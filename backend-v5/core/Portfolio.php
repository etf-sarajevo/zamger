<?php

// Modul: core
// Klasa: Portfolio
// Opis: sadrži sve podatke o uspjehu studenta na ponudi kursa - bodovi i konačna ocjena


require_once(Config::$backend_path."core/AcademicYear.php");
require_once(Config::$backend_path."core/CourseOffering.php");
require_once(Config::$backend_path."core/ScoringElement.php");

class Portfolio {
	public $Person, $CourseOffering;
	public $scoringElements;
	public $grade, $gradeDate; /* although these are public, please use getGrade() to update */
	
	// This method doesn't check if student is enrolled! FIXME?
	public static function fromCourseOffering($studentId, $courseOfferingId) {
		$p = array("Person" => $studentId, "CourseOffering" => $courseOfferingId, "scoringElements" => false);
		$p = Util::array_to_class($p, "Portfolio", array("Person", "CourseOffering"));
		return $p;
	}

	// Get CourseOffering from CourseUnit (and Year), also check if student is enrolled
	public static function fromCourseUnit($studentId, $courseUnitId, $academicYearId = 0) {
		if ($academicYearId == 0)
			$academicYearId = AcademicYear::getCurrent()->id;
		
		// Get CO and also check if student is enrolled
		$p = new Portfolio;
		$p->CourseOffering = CourseOffering::forStudent($studentId, $courseUnitId, $academicYearId);
		if (!$p->CourseOffering) 
			throw new Exception("Student $studentId not enrolled to course $courseUnitId, year $academicYearId", "700");
		$p->Person = new UnresolvedClass("Person", $studentId, $p->Person);
		$p->scoringElements = false;
		return $p;
	}

	public function getGrade() {
		if (!$this->grade) { // undefined, load from database
			if (get_class($this->CourseOffering) == "UnresolvedClass")
				$this->CourseOffering->resolve();
			$q10 = DB::query_assoc("select ocjena, UNIX_TIMESTAMP(datum) date from konacna_ocjena where predmet=" . $this->CourseOffering->CourseUnit->id . " and student=" . $this->Person->id);
			if ($q10) {
				$this->grade = $q10['ocjena'];
				$this->gradeDate = $q10['date'];
			}
		}
		
		return $this->grade;
	}
	
	// Use this method to get scoring elements for lasy loading
	public function getScore() {
		if (!$this->scoringElements)
			$this->scoringElements = ScoringElement::forStudent($this->Person->id, $this->CourseOffering->id);
		return $this->scoringElements;
	}
	
	// NOT FIXED BELOW THIS LINE

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
		// This could be further optimized...
		$pfs = DB::query_varray("select pk.id from student_predmet as sp, ponudakursa as pk, akademska_godina as ag, predmet as p where sp.student=$studentId and sp.predmet=pk.id and pk.predmet=p.id and pk.akademska_godina=ag.id and ag.aktuelna=1 order by pk.semestar desc, p.naziv");
		foreach($pfs as &$pf) {
			$pf = Portfolio::fromCourseOffering($studentId, $pf);
		}
		return $pfs;
	}
	
	// List of all portfolios for courses ever attended by student, ordered by academic year, semester, then alphabetically
	public static function getAllForStudent($studentId) {
		if ($winterSemester) $modulo=0; else $modulo=1;
		$q10 = DB::query("select pk.id, p.id, pk.akademska_godina, p.naziv, p.kratki_naziv, pk.studij, pk.semestar, pk.obavezan from student_predmet as sp, ponudakursa as pk, predmet as p where sp.student=$studentId and sp.predmet=pk.id and pk.predmet=p.id order by pk.akademska_godina, pk.semestar, p.naziv");
		$portfolios = array();
		$semester = 0;
		while ($r10 = mysql_fetch_row($q10)) {

			$p = new Portfolio;
			$p->studentId = $studentId;
			$p->grade = 0;
			$p->gradeDate = 0;
			$p->courseOfferingId = $r10[0];
			$p->courseUnitId = $r10[1];
			$p->academicYearId = $r10[2];
			// TODO dodati ostalo
			
			$p->courseUnit = new CourseUnit;
			$p->courseUnit->id = $r10[1];
			$p->courseUnit->name = $r10[3];
			$p->courseUnit->shortName = $r10[4];
			// TODO dodati ostalo
			
			$p->courseOffering = new CourseOffering;
			$p->courseOffering->id = $r10[0];
			$p->courseOffering->courseUnitId = $r10[1];
			$p->courseOffering->academicYearId = $r10[2];
			$p->courseOffering->programmeId = $r10[5];
			$p->courseOffering->semester = $r10[6];
			if ($r10[7]==1) $p->courseOffering->mandatory = true; else $p->courseOffering->mandatory = false;

			array_push($portfolios, $p);
		}
		return $portfolios;
	}
}

?>
