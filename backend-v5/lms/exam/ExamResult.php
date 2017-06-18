<?php

// Modul: lms/exam
// Klasa: ExamResult
// Opis: drži rezultat jednog studenta na ispitu


require_once(Config::$backend_path."lms/exam/Exam.php");
require_once(Config::$backend_path."core/CourseOffering.php");
require_once(Config::$backend_path."core/StudentScore.php");

class ExamResult {
	// Value "false" for result means that student didn't attend the exam
	public $student, $Exam, $result;
	
	public static function fromStudentAndExam($studentId, $examId) {
		$er = new ExamResult;
		$er->student = new UnresolvedClass("Person", $studentId, $er->student);
		$er->Exam = new UnresolvedClass("Exam", $examId, $er->Exam);
		$er->result = DB::get("SELECT ocjena FROM ispitocjene WHERE ispit=$examId AND student=$studentId");
		return $er;
	}


	public function setExamResult($result) {
		// Test if result is allowed
		if (get_class($this->Exam) == "UnresolvedClass")
			$this->Exam->resolve();
		if (get_class($this->Exam->ScoringElement) == "UnresolvedClass")
			$this->Exam->ScoringElement->resolve();
			
		$max = $this->Exam->ScoringElement->max;
		if ($result > $max) {
			Logging::log("AJAH ispit - vrijednost $result > max $max", LogLevel::Error);
			Logging::log2("ispit - vrijednost > max", $this->student->id, $this->Exam->id, 0, "$result > $max");
			throw new Exception("Exam result too big ($result > $max)", "703");
		}
	
		if ($this->result === false) {
			DB::query("INSERT INTO ispitocjene SET ocjena=$result, ispit=" . $this->Exam->id . ", student=" . $this->student->id);
			Logging::log("AJAH ispit - upisan novi rezultat $result (ispit i".$this->Exam->id.", student u".$this->student->id.")", LogLevel::Audit);
			Logging::log2("upisan rezultat ispita", $this->student->id, $this->Exam->id, 0, $result);
		} else {
			DB::query("UPDATE ispitocjene SET ocjena=$result WHERE ispit=" . $this->Exam->id . " AND student=" . $this->student->id);
			Logging::log("AJAH ispit - izmjena rezultata ".$this->result." u $result (ispit i".$this->Exam->id.", student u".$this->student->id.")", LogLevel::Audit);
			Logging::log2("izmjenjen rezultat ispita", $this->student->id, $this->Exam->id, 0, $this->result . " -> $result");
		}
		
		$this->result = $result;

		// Check integral exam
		$this->updateScore();
	}

	public function deleteExamResult() {
		if ($this->result !== false) {
			DB::query("DELETE FROM ispitocjene WHERE ispit=" . $this->Exam->id . " AND student=" . $this->student->id);
			Logging::log("AJAH ispit - izbrisan rezultat " . $this->result . "(ispit i".$this->Exam->id.", student u".$this->student->id.")", LogLevel::Audit);
			Logging::log2("izbrisan rezultat ispita", $this->student->id, $this->Exam->id, 0, $this->result);
		}
		
		$this->result = false;
		
		// Check integral exam
		$this->updateScore();
	}

	// Update score data related to exams
	public function updateScore() {
		// Resolve exam so we could get CourseUnit and AcademicYear
		if (get_class($this->Exam) == "UnresolvedClass")
			$this->Exam->resolve();
		if (get_class($this->Exam->ScoringElement) == "UnresolvedClass")
			$this->Exam->ScoringElement->resolve();
		
		// Get CoureOffering for student
		$co = CourseOffering::forStudent($this->student->id, $this->Exam->CourseUnit->id, $this->Exam->AcademicYear->id);
		Exam::updateAllScores($this->student->id, $co->id, $this->ZClass->ScoringElement);
	}
	
	// Calculate score that a student would have for exams
	public static function calculateScore($studentId, $courseOfferingId, $scoringElementId) {
		// If course option AnnullExams is set, every time student retakes an exam the previous score is annulled
		// (last one is used)
		// Otherwise, the best score is used
		$options = CourseOffering::getCourseOptions($courseOfferingId);
		if (in_array("PonistavanjeIspita", $options))
			return DB::get("SELECT io.ocjena FROM ispitocjene io, ispit i, ponudakursa pk WHERE pk.id=$courseOfferingId AND i.predmet=pk.predmet AND i.akademska_godina=pk.akademska_godina AND i.komponenta=$scoringElementId AND io.ispit=i.id AND io.student=$studentId ORDER BY i.datum DESC LIMIT 1");
		return DB::get("SELECT MAX(io.ocjena) FROM ispitocjene io, ispit i, ponudakursa pk WHERE pk.id=$courseOfferingId AND i.predmet=pk.predmet AND i.akademska_godina=pk.akademska_godina AND i.komponenta=$scoringElementId AND io.ispit=i.id AND io.student=$studentId");
	}
	
	// Calculate score that a student would have for exams
	public static function updateAllScores($studentId, $courseOfferingId, $ScoringElement) {
	
		// If this is partial exam, find integrals
		if ($ScoringElement->ScoringType->id == 1) {
			$se = $ScoringElement->id;
			$integral = DB::get("SELECT k.id FROM komponenta k, tippredmeta_komponenta tpk, akademska_godina_predmet agp, ponudakursa pk WHERE pk.id=$courseOfferingId AND agp.predmet=pk.predmet AND agp.akademska_godina=pk.akademska_godina AND agp.tippredmeta=tpk.tippredmeta AND tpk.komponenta=k.id AND k.tipkomponente=2 AND (k.opcija='$se' OR k.opcija LIKE '%$se+%' OR k.opcija LIKE '%+$se')");
			if ($integral) {
				// Recurse this function for integral exam
				$ScoringElement->id = $integral;
				$ScoringElement->ScoringType->id = 2;
				return updateScore($studentId, $courseOfferingId, $ScoringElement);
			}
			
			// No integrals, just calculate score and update
			$score = calculateScore($studentId, $courseOfferingId, $ScoringElement->id);
			$ss = StudentScore::fromStudentSEandCO($studentId, $ScoringElement->id, $courseOfferingId);
			if ($score === false)
				$ss->deleteScore();
			else
				$ss->setScore($score);
			return $score;
		}

		// This is an integral exam
		$score = calculateScore($studentId, $courseOfferingId, $ScoringElement->id);
		
		// Is integral exam passed?
		$passed = ($score >= $ScoringElement->pass);
		
		// Find partials for this integral exam
		$partials = explode("+", $ScoringElement->option);
		$partial_scores = array();
		$all_partials_passed = true;
		foreach($partials as $partial) {
			$partial_scores[$partial] = calculateScore($studentId, $courseOfferingId, $partial);
			
			// Is this partial exam passed?
			if ($all_partials_passed) {
				$se = ScoringElement::fromId($partial);
				if ($partial_scores[$partial] < $se->pass) $all_partials_passed = false;
			}
		}
		
		// Use integral score and delete partial scores if integral score is greater then sum of partials,
		// or if integral is passed and some of partials are failed
		if ($score > array_sum($partial_scores) || ($passed && !$all_partials_passed) ) {
			$ss = StudentScore::fromStudentSEandCO($studentId, $ScoringElement->id, $courseOfferingId);
			$ss->setScore($score);
			foreach($partials as $partial) {
				$ss = StudentScore::fromStudentSEandCO($studentId, $partial, $courseOfferingId);
				$ss->deleteScore();
			}
			
		// Otherwise use partial scores and delete integral score
		} else {
			$ss = StudentScore::fromStudentSEandCO($studentId, $ScoringElement->id, $courseOfferingId);
			if ($score > 0 || count($partials) > 0 || $score == false) $ss->deleteScore();
			foreach($partials as $partial) {
				$ss = StudentScore::fromStudentSEandCO($studentId, $partial, $courseOfferingId);
				$ss->setScore($partial_scores[$partial]);
			}
		}
		return $score;
	}
	
	// List of exam results for student on course
	public static function forStudentOnCourse($studentId, $courseOfferingId, $scoringElementId) {
		$query = DB::query_table("SELECT i.id id, io.ocjena result
			FROM ispitocjene as io, ispit as i, ponudakursa as pk
			WHERE io.student=$studentId AND io.ispit=i.id AND i.predmet=pk.predmet AND i.akademska_godina=pk.akademska_godina
			AND pk.id=$courseOfferingId AND i.komponenta=$scoringElementId
			ORDER BY i.vrijemeobjave");
		$examresults = array();
		foreach ($query as $item) {
			$er = new ExamResult;
			$er->student = new UnresolvedClass("Person", $studentId, $er->student);
			$er->Exam = new UnresolvedClass("Exam", $item['id'], $er->Exam);
			$er->result = $item['result'];

			array_push($examresults, $er);
		}
		return $examresults;
	}

	
	// List of latest results on exams attended by student newer than one month 
	// Skip exams that student didn't attend to and exams on courses where passing grade is already entered
	public static function getLatestForStudent($studentId, $limit) {
		$interval = "1 MONTH"; // MySQL interval
	
		$query = DB::query_table("SELECT io.ocjena result, i.id id, i.predmet CourseUnit, i.akademska_godina AcademicYear, UNIX_TIMESTAMP(i.datum) date, UNIX_TIMESTAMP(i.vrijemeobjave) publishedDateTime, i.komponenta ScoringElement
			FROM ispitocjene as io, ispit as i, predmet as p
			WHERE io.student=$studentId AND io.ispit=i.id AND i.predmet=p.id AND i.vrijemeobjave > SUBDATE(NOW(), INTERVAL $interval)
			AND (SELECT COUNT(*) FROM konacna_ocjena ko WHERE ko.student=$studentId and ko.predmet=i.predmet and ko.ocjena>=6)=0
			ORDER BY i.vrijemeobjave DESC
			LIMIT $limit");
		
		$examresults = array();
		foreach($query as $item) {
			$er = new ExamResult;
			$er->student = new UnresolvedClass("Person", $studentId, $er->student);
			$er->result = $item['result'];
			unset($item['result']);
			$er->Exam = Util::array_to_class($item, "Exam", array("CourseUnit", "AcademicYear", "ScoringElement"));

			array_push($examresults, $er);
		}
		return $examresults;
	}
}

?>
