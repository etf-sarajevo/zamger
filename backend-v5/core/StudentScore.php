<?php

// Modul: core
// Klasa: StudentScore
// Opis: sadrži ostvarene bodove studenta na predmetu iz neke specifične komponente


class StudentScore {
	public $student, $ScoringElement, $CourseOffering, $score, $details;

	// Constructor that takes student id, ScoringElement id and CourseOffering id
	// This will not set $score and $details attributes, you need to call getScore and getDetails respectively
	public static function fromStudentSEandCO($studentId, $scoringElementId, $courseOfferingId) {
		$ss = array("student" => $studentId, "ScoringElement" => $scoringElementId, "CourseOffering" => $courseOfferingId);
		// score and details will remain null
		$ss = Util::array_to_class($ss, "StudentScore", array("ScoringElement", "CourseOffering"));
		$ss->student = new UnresolvedClass("Person", $ss->student, $ss->student);
		$ss->score = null;
		$ss->details = null;
		return $ss;
	}
	
	// Here $id isn't actually id, but a string consisting of student id, ScoringElement id and CourseOffering id
	// It's a trick so that UnresolvedClass would call this constructor and populate the corresponding
	// info
	public static function fromId($id) {
		list($student, $se, $co) = explode("-", $id);
		return fromStudentSEandCO($student, $se, $co);
	}

	// This method gets a list of StudentScores for a given student on given course
	// Score will also be determined but not details
	public static function fromStudentAndCO($studentId, $courseOfferingId) {
		$scores = DB::query_table("SELECT student, komponenta ScoringElement, predmet CourseOffering, bodovi score FROM komponentebodovi WHERE student=$studentId AND predmet=$courseOfferingId");
		foreach($scores as &$ss) {
			$ss = Util::array_to_class($ss, "StudentScore", array("ScoringElement", "CourseOffering"));
			$ss->student = new UnresolvedClass("Person", $ss->student, $ss->student);
			$ss->details = null;
		}
		return $scores;
	}
	
	// Update score for all scoring elements of given type on given course for given student
	public static function updateAllOfType($studentId, $courseOfferingId, $seType) {
		$scores = DB::query_table("SELECT kb.student student, kb.komponenta ScoringElement, kb.predmet CourseOffering, kb.bodovi score, k.tipkomponente ScoringType FROM komponentebodovi kb, komponenta k WHERE kb.student=$studentId AND kb.predmet=$courseOfferingId AND kb.komponenta=k.id AND k.tipkomponente=$seType");
		foreach($scores as &$ss) {
			$ss = Util::array_to_class($ss, "StudentScore", array("CourseOffering"));
			$seid = $ss->ScoringElement;
			$ss->ScoringElement = new ScoringElement;
			$ss->ScoringElement->id = $seid;
			$ss->ScoringElement->ScoringType = new UnresolvedClass("ScoringType", $ss->ScoringType, $ss->ScoringElement->ScoringType);
			$ss->student = new UnresolvedClass("Person", $ss->student, $ss->student);
			$ss->details = null;
			
			$ss->updateScore();
		}
	}
	
	// Populate the score field
	public function getScore() {
		if ($this->score !== null) return $this->score;
		
		// Get score
		$this->score = DB::get("SELECT bodovi FROM komponentebodovi WHERE student=" . $this->student->id . " AND predmet=" . $this->CourseOffering->id . " AND
			komponenta=" . $this->ScoringElement->id);
		return $this->score;
	}
	
	// This method should not be called directly unless ScoringType is 5
	// Call updateScore() instead to recalculate using corresponding ScoringType
	public function setScore($score) {
		if (get_class($this->ScoringElement) == "UnresolvedClass")
			$this->ScoringElement->resolve();
		
		if ($score > $this->ScoringElement->max) {
			Logging::log("AJAH ispit - vrijednost $score > max ".$se->max,3);
			throw new Exception("Score $score greater then allowed " . $this->ScoringElement->max, "702");
		}

		$oldScore = $this->getScore();
		DB::query("LOCK TABLES komponentebodovi WRITE");
		if ($oldScore === false)
			DB::query("INSERT INTO komponentebodovi SET student=" . $this->student->id . ", predmet=" . $this->CourseOffering->id . ", komponenta=" . $this->ScoringElement->id . ", bodovi=$score");
		else 
			DB::query("UPDATE komponentebodovi SET bodovi=$score WHERE student=" . $this->student->id . " AND predmet=" . $this->CourseOffering->id . " AND
			komponenta=" . $this->ScoringElement->id);
		DB::query("UNLOCK TABLES");
		$this->score = $score;
	}
	
	public function updateScore() {
		if (get_class($this->ScoringElement) == "UnresolvedClass")
			$this->ScoringElement->resolve();
		
		$score = 0;
		
		if ($this->ScoringElement->ScoringType->id == 1 || $this->ScoringElement->ScoringType->id == 2) {
			require_once(Config::$backend_path."lms/exam/ExamResult.php");
			// For exams the updateAllScores function does everything
			ExamResult::updateAllScores($this->student->id, $this->CourseOffering->id, $this->ScoringElement);
		}
		if ($this->ScoringElement->ScoringType->id == 3) {
			require_once(Config::$backend_path."lms/attendance/Attendance.php");
			$score = Attendance::calculateScore($this->student->id, $this->CourseOffering->id, $this->ScoringElement);
			$this->setScore($score);
		}
		if ($this->ScoringElement->ScoringType->id == 4) {
			require_once(Config::$backend_path."lms/homework/Assignment.php");
			$score = Assignment::calculateScore($this->student->id, $this->CourseOffering->id, $this->ScoringElement);
			$this->setScore($score);
		}
	}
	
	public function deleteScore() {
		DB::query("DELETE FROM komponentebodovi WHERE student=" . $this->student->id . " AND predmet=" . $this->CourseOffering->id . " and komponenta=" . $this->ScoringElement->id);
	}

	
	// Populate the details field
	public function getDetails() {
		if ($this->details) return $this->details;
		
		// We need to resolve ScoringElement to get type
		if (get_class($this->ScoringElement) == "UnresolvedClass")
			$this->ScoringElement->resolve();
			
		// Do not assume that lms/* components exist
		if ($this->ScoringElement->ScoringType->id == 1 || $this->ScoringElement->ScoringType->id == 2) {
			require_once(Config::$backend_path."lms/exam/ExamResult.php");
			$this->details = ExamResult::forStudentOnCourse($this->student->id, $this->CourseOffering->id, $this->ScoringElement->id);
		}
		if ($this->ScoringElement->ScoringType->id == 3) {
			require_once(Config::$backend_path."lms/attendance/Attendance.php");
			$this->details = Attendance::forStudentOnCourse($this->student->id, $this->CourseOffering->id, $this->ScoringElement->id);
		}
		if ($this->ScoringElement->ScoringType->id == 4) {
			require_once(Config::$backend_path."lms/homework/Assignment.php");
			$this->details = Assignment::forStudentOnCourse($this->student->id, $this->CourseOffering->id, $this->ScoringElement->id);
		}
		if ($this->ScoringElement->ScoringType->id == 5) {
			// No details for type 5
		}
		return $this->details;
	}
	
}

?>
