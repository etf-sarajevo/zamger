<?php

// Modul: lms/homework
// Klasa: Homework
// Opis: jedna zadaća


require_once(Config::$backend_path."core/DB.php");
require_once(Config::$backend_path."core/CourseUnit.php");
require_once(Config::$backend_path."core/AcademicYear.php");
require_once(Config::$backend_path."core/Portfolio.php");

class Homework {
	public $id;
	public $name, $courseUnitId, $academicYearId, $nrAssignments, $scoringElementId, $deadline, $active, $programmingLanguageId, $attachment, $allowedExtensions, $publishedDateTime, $text /* postavka */;
	public $courseUnit, $academicYear;
	
	public static function fromId($id) {
		$q10 = DB::query("select z.naziv, z.predmet, z.akademska_godina, z.zadataka, z.komponenta, UNIX_TIMESTAMP(z.rok), z.aktivna, z.programskijezik, z.attachment, z.dozvoljene_ekstenzije, UNIX_TIMESTAMP(z.vrijemeobjave), z.postavka_zadace, p.naziv from zadaca as z, predmet as p where z.id=$id and z.predmet=p.id");
		if (mysql_num_rows($q10)<1) {
			throw new Exception("nepoznata zadaca");
		}
		$h = new Homework;
		$h->id = $id;
		$h->name = mysql_result($q10,0,0);
		$h->courseUnitId = mysql_result($q10,0,1);
		$h->academicYearId = mysql_result($q10,0,2);
		$h->nrAssignments = mysql_result($q10,0,3);
		$h->scoringElementId = mysql_result($q10,0,4);
		$h->deadline = mysql_result($q10,0,5);
		if (mysql_result($q10,0,6) == 1) $h->active=true; else $h->active=false;
		$h->programmingLanguageId = mysql_result($q10,0,7);
		if (mysql_result($q10,0,8) == 1) $h->attachment=true; else $h->attachment=false;
		$h->allowedExtensions = mysql_result($q10,0,9);
		$h->publishedDateTime = mysql_result($q10,0,10);
		$h->text = mysql_result($q10,0,11);

		$h->courseUnit = new CourseUnit;
		$h->courseUnit->id = $h->courseUnitId;
		$h->courseUnit->name = mysql_result($q10,0,12);
		
		// Refs to other objects to be instantiated as needed
		$h->academicYear = 0;
		return $h;
	}
	
	// List of homeworks that are due for submitting (deadline is near)
	// Gets no more than $limit homeworks, ordered by deadline descending
	public static function getLatestForStudent($student, $limit) {
		// FIXME u ovaj upit je ugrađena i provjera da li je aktivan studentski modul
		$q10 = DB::query("
SELECT z.id, z.naziv, z.predmet, z.akademska_godina, z.zadataka, z.komponenta, UNIX_TIMESTAMP(z.rok), z.aktivna, z.programskijezik, z.attachment, z.dozvoljene_ekstenzije, UNIX_TIMESTAMP(z.vrijemeobjave), z.postavka_zadace, p.naziv
FROM zadaca as z, student_predmet as sp, ponudakursa as pk, predmet as p
WHERE z.predmet=pk.predmet AND z.akademska_godina=pk.akademska_godina AND sp.student=$student AND sp.predmet=pk.id AND pk.predmet=p.id AND z.rok>curdate() AND z.aktivna=1
ORDER BY rok DESC
LIMIT $limit");
		$homeworks = array();
		while ($r10 = mysql_fetch_row($q10)) {
			// Skip if student has passing grade (optimize?)
			$q15a = DB::query("select count(*) from konacna_ocjena where predmet=$r10[2] and ocjena>=6");
			if (mysql_result($q15a,0,0)>0) continue;

			$h = new Homework;
			$h->id = $r10[0];
			$h->name = $r10[1];
			$h->courseUnitId = $r10[2];
			$h->academicYearId = $r10[3];
			$h->nrAssignments = $r10[4];
			$h->scoringElementId = $r10[5];
			$h->deadline = $r10[6];
			if ($r10[7] == 1) $h->active=true; else $h->active=false;
			$h->programmingLanguageId = $r10[8];
			if ($r10[9] == 1) $h->attachment=true; else $h->attachment=false;
			$h->allowedExtensions = $r10[10];
			$h->publishedDateTime = $r10[11];
			$h->text = $r10[12];
			
			$h->courseUnit = new CourseUnit;
			$h->courseUnit->id = $r10[2];
			$h->courseUnit->name = $r10[13];
			// TODO dodati ostalo
			
			$h->academicYear = new AcademicYear;
			$h->academicYear->id = $r10[3];
			// TODO dodati ostalo
			
			array_push($homeworks, $h);
		}
		
		return $homeworks;
	}
	
	// List of homeworks that student submitted and were recently reviewed
	// No more than $limit homeworks, ordered by time of review descending
	public static function getReviewedForStudent($student, $limit) {
		$q10 = DB::query("
SELECT z.id, z.naziv, z.predmet, z.akademska_godina, z.zadataka, z.komponenta, UNIX_TIMESTAMP(z.rok), z.aktivna, z.programskijezik, z.attachment, z.dozvoljene_ekstenzije, UNIX_TIMESTAMP(z.vrijemeobjave), z.postavka_zadace, p.naziv 
FROM zadatak as zk, zadaca as z, predmet as p 
WHERE zk.student=$student and zk.status!=1 and zk.status!=4 and zk.zadaca=z.id and z.predmet=p.id and zk.vrijeme < SUBDATE(NOW(), INTERVAL 1 MONTH)
ORDER BY zk.id DESC 
LIMIT $limit");
		$zadaca_bila = array();
		$homeworks = array();
		while ($r10 = mysql_fetch_row($q10)) {
			if ( in_array($r10[0], $zadaca_bila) ) continue; // ne prijavljujemo vise puta istu zadacu

			$h = new Homework;
			$h->id = $r10[0];
			$h->name = $r10[1];
			$h->courseUnitId = $r10[2];
			$h->academicYearId = $r10[3];
			$h->nrAssignments = $r10[4];
			$h->scoringElementId = $r10[5];
			$h->deadline = $r10[6];
			if ($r10[7] == 1) $h->active=true; else $h->active=false;
			$h->programmingLanguageId = $r10[8];
			if ($r10[9] == 1) $h->attachment=true; else $h->attachment=false;
			$h->allowedExtensions = $r10[10];
			$h->publishedDateTime = $r10[11];
			$h->text = $r10[12];
			
			$h->courseUnit = new CourseUnit;
			$h->courseUnit->id = $r10[2];
			$h->courseUnit->name = $r10[13];
			// TODO dodati ostalo
			
			array_push($homeworks, $h);
			array_push($zadaca_bila,$r10[0]);
		}
		
		return $homeworks;
	}
	
	// List of homeworks published for course
	public static function fromCourse($courseUnitId, $academicYearId) {
		$q10 = DB::query("SELECT id, naziv, zadataka, komponenta, UNIX_TIMESTAMP(rok), aktivna, programskijezik, attachment, dozvoljene_ekstenzije, UNIX_TIMESTAMP(vrijemeobjave), postavka_zadace FROM zadaca WHERE predmet=$courseUnitId and akademska_godina=$academicYearId ORDER BY komponenta, naziv");
		$homeworks = array();
		while ($r10 = mysql_fetch_row($q10)) {
			$h = new Homework;
			$h->id = $r10[0];
			$h->name = $r10[1];
			$h->courseUnitId = $courseUnitId;
			$h->academicYearId = $academicYearId;
			$h->nrAssignments = $r10[2];
			$h->scoringElementId = $r10[3];
			$h->deadline = $r10[4];
			if ($r10[5] == 1) $h->active=true; else $h->active=false;
			$h->programmingLanguageId = $r10[6];
			if ($r10[7] == 1) $h->attachment=true; else $h->attachment=false;
			$h->allowedExtensions = $r10[8];
			$h->publishedDateTime = $r10[9];
			$h->text = $r10[10];
			
			array_push($homeworks, $h);
		}
		
		return $homeworks;
	}

	// Since Assignment holds just one assignment, and Homework is not specific to student, here is
	// where we update the score with studentid as param
	public function updateScoreForStudent($studentId) {
		$pf = Portfolio::fromCourseUnit($studentId, $this->courseUnitId, $this->academicYearId);
		$allhomeworks = Homework::fromCourse($this->courseUnitId, $this->academicYearId);

		$totalScore = array();
		foreach ($allhomeworks as $h) {
			for ($i=1; $i <= $h->nrAssignments; $i++) {
				try {
					$a = Assignment::fromStudentHomeworkNumber($studentId, $h->id, $i);
					if ($a->status == 5) // 5 = reviewed
						$totalScore[$h->scoringElementId] += $a->score;
				} catch(Exception $e) {
					// Student didn't submit a homework, do nothing
				}
			}
		}

		foreach ($totalScore as $scoringElementId => $score)
			$pf->setScore($scoringElementId, $score);
	}

}

?>
