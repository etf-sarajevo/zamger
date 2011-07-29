<?php

// Modul: lms/exam
// Klasa: ExamResult
// Opis: drži rezultat jednog studenta na ispitu


require_once(Config::$backend_path."core/DB.php");
require_once(Config::$backend_path."core/Logging.php");
require_once(Config::$backend_path."core/Portfolio.php");
require_once(Config::$backend_path."core/ScoringElement.php");
require_once(Config::$backend_path."core/CourseUnit.php");

require_once(Config::$backend_path."lms/exam/Exam.php");

class ExamResult {
	public $studentId, $examId, $result, $exists;
	public $exam;
	
	public static function fromStudentAndExam($studentId, $examId) {
		$er = new ExamResult;
		$er->studentId = $studentId;
		$er->examId = $examId;
		
		$er->exam = Exam::fromId($examId);

		$q10 = myquery("select ocjena from ispitocjene where ispit=$examId and student=$studentId");
		if (mysql_num_rows($q10)<1) {
			// Allow to set a new result
			$er->exists = false;
		} else {
			$er->exists = true;
			$er->result = mysql_result($q10,0,0);
		}
		
		return $er;
	}


	public function setExamResult($result) {
		$course = $this->exam->courseUnitId;
		$ay = $this->exam->academicYearId;
		$sei = $this->exam->scoringElementId;
		
		$p = Portfolio::fromCourseUnit($this->studentId, $course, $ay);
		
		$se = ScoringElement::fromId($sei);
		$max = $se->max;
		
		// Maksimalan i minimalan broj bodova
		if ($result>$max) {
			Logging::log("AJAH ispit - vrijednost $result > max $max",3);
			throw new Exception("maksimalan broj bodova je $max, a unijeli ste $result");
		}

		if ($this->exists) {
			$q60 = DB::query("update ispitocjene set ocjena=$result where ispit=".$this->examId." and student=".$this->studentId);
			Logging::log("AJAH ispit - izmjena rezultata ".$this->result." u $result (ispit i".$this->examId.", student u".$this->studentId.")",4); // nivo 4: audit
		} else {
			$q60 = DB::query("insert into ispitocjene set ispit=".$this->examId.", student=".$this->studentId.", ocjena=$result");
			Logging::log("AJAH ispit - upisan novi rezultat $result (ispit i".$this->examId.", student u".$this->studentId.")",4); // nivo 4: audit
			$this->exists = true;
		}
		
		$this->result = $result;

		// Check integral exam
		$this->updateScoring();
	}

	public function deleteExamResult() {
		$course = $this->exam->courseUnitId;
		$ay = $this->exam->academicYearId;
		$sei = $this->exam>scoringElementId;
		
		$p = Portfolio::fromCourseUnit($this->studentId, $course, $ay);
		
		$se = new ScoringElement($sei);
		$max = $se->max;

		if ($this->exists) {
			$q60 = DB::query("delete from ispitocjene where ispit=".$this->examId." and student=".$this->studentId);
			Logging::log("AJAH ispit - izbrisan rezultat ".$this->result." (ispit i".$this->examId.", student u".$this->studentId.")",4); // nivo 4: audit
			$this->exists = false;
		}
		
		// Check integral exam
		$this->updateScoring();
	}

	public function updateScoring() {
		$course = $this->exam->courseUnitId;
		$ay = $this->exam->academicYearId;
		$sei = $this->exam->scoringElementId;

		$p = Portfolio::fromCourseUnit($this->studentId, $course, $ay);

		// Find integral exam scoring element
		$q30 = DB::query("select k.id, k.opcija, k.prolaz from komponenta as k, tippredmeta_komponenta as tpk, akademska_godina_predmet as agp where tpk.komponenta=k.id and tpk.tippredmeta=agp.tippredmeta and agp.predmet=$course and agp.akademska_godina=$ay and k.tipkomponente=2 and k.opcija like '%$sei%'");
		if (mysql_num_rows($q30)<1) return; // No integral exam scoring element on this course

		$intk = mysql_result($q30,0,0);
		$intdijelovi = mysql_result($q30,0,1);
		$intprolaz = mysql_result($q30,0,2);

		// Koliko bodova je na integralnom?
		$q40 = DB::query("select io.ocjena from ispit as i, ispitocjene as io where i.predmet=$course and i.akademska_godina=$ay and i.komponenta=$intk and i.id=io.ispit and io.student=".$this->studentId." order by io.ocjena desc limit 1");
		if (mysql_num_rows($q40)<1) return; // No exams registered or this student didn't take one
		$intbodovi = mysql_result($q40,0,0);

		// Koliko bodova je osvojio na ostalim ispitima koji čine jedan 
		// integralni (npr. 1+2 znači da se integralni sastoji od 
		// parcijalnih ispita sa IDovima 1 i 2)
		$dijelovi = explode("+",$intdijelovi);
		$suma = 0;
		$polozio=1; // Da li je polozio sve parcijalne ispite?
		$diobodovi = array();
		foreach ($dijelovi as $dio) {
			$q45 = DB::query("select prolaz from komponenta where id=$dio");
			$dioprolaz = mysql_result($q45,0,0);

			$q50 = DB::query("select io.ocjena from ispit as i, ispitocjene as io where i.predmet=$course and i.akademska_godina=$ay and i.komponenta=$dio and i.id=io.ispit and io.student=".$this->studentId." order by io.ocjena desc limit 1");
			if (mysql_num_rows($q50)>0) {
				$diobodovi[$dio] = mysql_result($q50,0,0);
				if ($diobodovi[$dio]<$dioprolaz) $polozio=0;
				$suma += $diobodovi[$dio];
			} else $polozio=0;
		}

		// Integralni se uzima u obzir ako je osvojeno više bodova nego
		// suma svih parcijalnih, ili ako je položio integralni a pao
		// bilo koji od parcijalnih
		if ($suma<$intbodovi || ($polozio==0 && $intbodovi>$intprolaz)) {
			foreach ($dijelovi as $dio) {
				// Ovo ce ujedno obrisati upravo ubacenu komponentu
				// ali to vrijedi pojednostavljenja koda
				$p->deleteScore($dio);
			}
			$p->setScore($intk, $intbodovi);
		} else {
			foreach ($dijelovi as $dio) {
				// Ovo ce ujedno obrisati upravo ubacenu komponentu
				// ali to vrijedi pojednostavljenja koda
				$p->setScore($dio, $diobodovi[$dio]);
			}
			$p->deleteScore($intk);
		}
	}
	
	// List of latest results on exams attended by student (e.g. we only return results that exist)
	public static function getLatestForStudent($student, $limit) {
		$q15 = DB::query("
SELECT io.ocjena, 
i.id, i.predmet, i.akademska_godina, UNIX_TIMESTAMP(i.datum), UNIX_TIMESTAMP(i.vrijemeobjave), i.komponenta,
k.gui_naziv, k.prolaz, 
p.naziv
FROM ispitocjene as io, ispit as i, komponenta as k, predmet as p
WHERE io.student=$student AND io.ispit=i.id AND i.komponenta=k.id AND i.predmet=p.id AND i.vrijemeobjave > SUBDATE(NOW(), INTERVAL 1 MONTH)
ORDER BY i.vrijemeobjave DESC
LIMIT $limit"); // 
		$examresults = array();
		while ($r15 = mysql_fetch_row($q15)) {
			// Skip if student has passing grade (optimize?)
			$q15a = DB::query("select count(*) from konacna_ocjena where student=$student and predmet=$r15[2] and ocjena>=6");
			if (mysql_result($q15a,0,0)>0) continue;

			$er = new ExamResult;
			$er->studentId = $student;
			$er->examId = $r15[1];
			$er->result = $r15[0];
			$er->exists = true;
			
			$er->exam = new Exam;
			$er->exam->id = $r15[1];
			$er->exam->courseUnitId = $r15[2];
			$er->exam->academicYearId = $r15[3];
			$er->exam->date = $r15[4];
			$er->exam->publishedDateTime = $r15[5];
			$er->exam->scoringElementId = $r15[6];
			
			$er->exam->scoringElement = new ScoringElement;
			$er->exam->scoringElement->guiName = $r15[7];
			$er->exam->scoringElement->pass = $r15[8];
			// TODO dodati ostalo
			
			$er->exam->courseUnit = new CourseUnit;
			$er->exam->courseUnit->name = $r15[9];
			// TODO dodati ostalo		
			
			array_push($examresults, $er);
		}
		return $examresults;
	}
}

?>