<?php

// Modul: core
// Klasa: Portfolio
// Opis: sadrži sve podatke o uspjehu studenta na ponudi kursa - bodovi i konačna ocjena


require_once(Config::$backend_path."core/DB.php");
require_once(Config::$backend_path."core/Logging.php");
require_once(Config::$backend_path."core/CourseOffering.php");
require_once(Config::$backend_path."core/ScoringElement.php");

class Portfolio {
	public $studentId, $courseOfferingId, $courseUnitId, $academicYearId;
	public $courseUnit;
	public $grade, $gradeDate; /* although these are public, please use getGrade() to update */
	
	// Da li je $student upisan na $courseOffering
	public static function fromCourseOffering($studentId, $courseOfferingId) {
		$q45 = DB::query ("select count(*) from student_predmet where student=$studentId and predmet=$courseOfferingId");
		if (mysql_result($q45,0,0)<1) {
			throw new Exception("student u$studentId ne slusa predmet $courseOfferingId");
		}
		$p = new Portfolio;
		$p->studentId = $studentId;
		$p->courseOfferingId = $courseOfferingId;
		
		// To be determined later, if needed:
		$p->courseUnitId = 0;
		$p->courseUnit = 0;
		$p->academicYearId = 0;
		$p->grade = 0;
		$p->gradeDate = 0;
		return $p;
	}

	// Studenta postavljamo manuelno a $courseOffering se dobije iz $course
	// Ujedno provjerava da li je student upisan na predmet
	public static function fromCourseUnit($studentId, $courseUnitId, $academicYearId) {
		$q45 = DB::query ("select pk.id from student_predmet as sp, ponudakursa as pk where sp.student=$studentId and sp.predmet=pk.id and pk.predmet=$courseUnitId and pk.akademska_godina=$academicYearId");
		if (mysql_num_rows($q45)<1) {
			throw new Exception("student $studentId ne slusa predmet $courseUnitId ($academicYearId)");
		}
		$p = new Portfolio;
		$p->studentId = $studentId;
		$p->courseOfferingId = mysql_result($q45,0,0);
		$p->courseUnitId = $courseUnitId;
		$p->academicYearId = $academicYearId;

		// To be determined later, if needed:
		$p->grade = 0;
		$p->gradeDate = 0;
		$p->courseUnit = 0;
		return $p;
	}

	public function getGrade() {
		if ($this->grade == 0) { // 0 means unknown, we need to check database
			if ($this->courseUnitId==0) {
				// Set course from courseOffering
				$co = CourseOffering::fromId($this->courseOfferingId);
				$this->courseUnitId = $co->courseUnitId;
				$this->academicYearId = $co->academicYearId;
			}

			$this->grade = -1; // -1 means not graded
			$q10 = DB::query("select ocjena, UNIX_TIMESTAMP(datum) from konacna_ocjena where predmet=".$this->courseUnitId." and student=".$this->studentId);
			if (mysql_num_rows($q10) > 0) {
				$this->grade = mysql_result($q10, 0, 0);
				$this->gradeDate = mysql_result($q10, 0, 1);
			}
		}
		
		return $this->grade;
	}

	public function setGrade($grade) {
		// FIXME ocjene trebaju biti A-F a ne 6-10
		if ($this->courseUnitId==0) {
			// Set course from courseOffering
			$co = CourseOffering::fromId($this->courseOfferingId);
			$this->courseUnitId = $co->courseUnitId;
			$this->academicYearId = $co->academicYearId;
		}
	
		// Upper and lower bounds for grade
		if ($grade > 10) {
			Logging::log("AJAH ispit - vrijednost $vrijednost > max $max",3);
			throw new Exception("stavili ste ocjenu veću od 10");
		}
		if ($grade<6) {
			Logging::log("AJAH ispit - konacna ocjena manja od 6 ($vrijednost)",3);
			throw new Exception("stavili ste ocjenu manju od 6");
		}

		// Ne koristimo REPLACE i slično zbog logginga
		$q70 = DB::query("select ocjena from konacna_ocjena where predmet=".$this->courseUnitId." and student=".$this->studentId);
		if (mysql_num_rows($q70)==0) {
			$q80 = DB::query("insert into konacna_ocjena set predmet=".$this->courseUnitId.", akademska_godina=".$this->academicYearId.", student=".$this->studentId.", ocjena=$grade, datum=NOW()");
			Logging::log("AJAH ko - dodana ocjena $grade (predmet pp".$this->courseUnitId.", student u".$this->studentId.")",4); // nivo 4: audit
		} else {
			$staraocjena = mysql_result($q70,0,0);
			$q80 = DB::query("update konacna_ocjena set ocjena=$grade, izvezena=2, datum=NOW() where predmet=".$this->courseUnitId." and student=".$this->studentId);
			Logging::log("AJAH ko - izmjena ocjene $staraocjena u $grade (predmet pp".$this->courseUnitId.", student u".$this->studentId.")",4); // nivo 4: audit
		}
		
		$this->grade = $grade;
	}
	
	public function deleteGrade() {
		if ($this->courseUnitId==0) {
			// Set course from courseOffering
			$co = CourseOffering::fromId($this->courseOfferingId);
			$this->courseUnitId = $co->courseUnitId;
		}

		$q70 = myquery("select ocjena from konacna_ocjena where predmet=".$this->courseUnitId." and student=".$this->studentId);
		if (mysql_num_rows($q70)>0) {
			$staraocjena = mysql_result($q70,0,0);
			$q80 = DB::query("delete from konacna_ocjena where predmet=".$this->courseUnitId." and student=".$this->studentId);
			//$q80 = myquery("update konacna_ocjena set ocjena=5, izvezena=3, datum=NOW() where predmet=".$this->course." and student=$stud_id");
			Logging::log("AJAH ko - obrisana ocjena $staraocjena (predmet pp".$this->courseUnitId.", student u".$this->studentId.")",4); // nivo 4: audit
		}
		
		$this->grade = -1; // -1 means no grade
	}
	
	public function getScore($scoringElementId) {
		$q10 = DB::query("select bodovi from komponentebodovi where student=".$this->studentId." and predmet=".$this->courseOfferingId." and komponenta=$scoringElementId");
		if (mysql_num_rows($q10)<1) return 0; // Entries in this table are created as needed
		return mysql_result($q10,0,0);
	}
	
	public function setScore($scoringElementId, $score) {
		$se = ScoringElement::fromId($scoringElementId);
		
		// Maksimalan broj bodova
		if ($score > $se->max) {
			zamgerlog("AJAH ispit - vrijednost $score > max ".$se->max,3);
			return "maksimalan broj bodova je ".$se->max.", a unijeli ste $score";
		}
		
		$q90 = myquery("lock tables komponentebodovi write");
		$q10 = DB::query("delete from komponentebodovi where student=".$this->studentId." and predmet=".$this->courseOfferingId." and komponenta=$scoringElementId");
		$q20 = DB::query("insert into komponentebodovi set student=".$this->studentId.", predmet=".$this->courseOfferingId.", komponenta=$scoringElementId, bodovi=$score");
		$q93 = myquery("unlock tables");
		Logging::log("AJAH fiksna - upisani bodovi $vrijednost za fiksnu komponentu $scoringElementId (predmet pp".$this->courseOfferingId.", student u".$this->studentId.")",4);
	}
	
	public function deleteScore($scoringElementId) {
		$q10 = DB::query("delete from komponentebodovi where student=".$this->studentId." and predmet=".$this->courseOfferingId." and komponenta=$scoringElementId");
	}

	public function getTotalScore() {
		$q10 = DB::query("select SUM(bodovi) from komponentebodovi where student=".$this->studentId." and predmet=".$this->courseOfferingId);
		return mysql_result($q10,0,0);
	}

	// Maximum total score that student could've made on this course so far
	// We use only those elements that currently have some kind of score, so as to give accurate information
	// of the course progress. Final total score is always 100 points (in theory)
	public function getMaxScore() {
		$q30 = DB::query("select k.id, k.maxbodova, k.tipkomponente, k.naziv from komponenta as k, komponentebodovi as kb where kb.student=".$this->studentId." and kb.predmet=".$this->courseOfferingId." and kb.komponenta=k.id");
		$maxscore=0;
		while ($r30 = mysql_fetch_row($q30)) {
//print $r30[3]." - ".$r30[1]."<br />";
			if ($r30[2] == 4) { // Tip komponente: zadaće
				$q35 = myquery("select sum(z.bodova) from zadaca as z, ponudakursa as pk where z.predmet=pk.predmet and z.akademska_godina=pk.akademska_godina and pk.id=".$this->courseOfferingId." and z.komponenta=$r30[0]");
				$maxscore += round(mysql_result($q35,0,0), 2);
			} else
				$maxscore += $r30[1];
		}
		return $maxscore;
	}
	
	// List of portfolios where student has recently received a final grade, ordered by grade timestamp
	public static function getLatestGradesForStudent($student, $limit) {
		$q17 = DB::query("
SELECT ko.ocjena, UNIX_TIMESTAMP(ko.datum), ko.predmet, ko.akademska_godina, p.naziv
FROM konacna_ocjena as ko, predmet as p 
WHERE ko.student=$student AND ko.predmet=p.id AND ko.ocjena>5 AND ko.datum > SUBDATE(NOW(), INTERVAL 1 MONTH)
ORDER BY ko.datum DESC
LIMIT $limit");
		$grades = array();
		while ($r17 = mysql_fetch_row($q17)) {
			$p = new Portfolio;
			$p->grade = $r17[0];
			$p->gradeDate = $r17[1];
			$p->courseUnitId = $r17[2];
			$p->academicYearId = $r17[3];
			// TODO dodati ostalo
			
			$p->courseUnit = new CourseUnit;
			$p->courseUnit->id = $r17[2];
			$p->courseUnit->name = $r17[4];
			// TODO dodati ostalo
			
			array_push($grades, $p);
		}
		return $grades;
	}
	
	// List of portfolios for courses currently attended by student, ordered alphabetically
	// "Currently" is defined as in current academic year, latest semester
	public static function getCurrentForStudent($studentId) {
		$q10 = DB::query("select pk.semestar, pk.id, p.id, ag.id, p.naziv, p.kratki_naziv from student_predmet as sp, ponudakursa as pk, akademska_godina as ag, predmet as p where sp.student=$studentId and sp.predmet=pk.id and pk.predmet=p.id and pk.akademska_godina=ag.id and ag.aktuelna=1 order by pk.semestar desc, p.naziv");
		$portfolios = array();
		$semester = 0;
		while ($r10 = mysql_fetch_row($q10)) {
			if ($semester > 0 && $semester != $r10[0]) break; // semester changed, but we will use only last one
			$semester = $r10[0];

			$p = new Portfolio;
			$p->grade = 0;
			$p->gradeDate = 0;
			$p->courseOfferingId = $r10[1];
			$p->courseUnitId = $r10[2];
			$p->academicYearId = $r10[3];
			// TODO dodati ostalo
			
			$p->courseUnit = new CourseUnit;
			$p->courseUnit->id = $r10[2];
			$p->courseUnit->name = $r10[4];
			$p->courseUnit->shortName = $r10[5];
			// TODO dodati ostalo
			
			array_push($portfolios, $p);
		}
		return $portfolios;
	}
}

?>