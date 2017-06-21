<?php

// Modul: lms/homework
// Klasa: Homework
// Opis: jedna zadaća


require_once(Config::$backend_path."lms/homework/ProgrammingLanguage.php");

class Homework {
	public $id;
	public $name, $CourseUnit, $AcademicYear, $nrAssignments, $maxScore, $ScoringElement, $deadline, $active, $ProgrammingLanguage, $automatedTesting, $attachment, $allowedExtensions, $publishedDateTime, $text /* postavka */;
	
	public static function fromId($id) {
		$hw = DB::query_assoc("SELECT id, naziv name, predmet CourseUnit, akademska_godina AcademicYear, zadataka nrAssignments, bodova maxScore, komponenta ScoringElement, UNIX_TIMESTAMP(rok) deadline, aktivna active, programskijezik ProgrammingLanguage, automatsko_testiranje automatedTesting, attachment, dozvoljene_ekstenzije allowedExtensions, UNIX_TIMESTAMP(vrijemeobjave) publishedTime, postavka_zadace text FROM zadaca WHERE id=$id");
		if (!$hw) throw new Exception("Unknown homework $id", "404");
		
		$hw = Util::array_to_class($hw, "Homework", array("CourseUnit", "AcademicYear", "ScoringElement", "ProgrammingLanguage"));
		if ($hw->active == 1) $hw->active=true; else $hw->active=false; // FIXME use boolean in database
		if ($hw->attachment == 1) $hw->attachment=true; else $hw->attachment=false;
		if ($hw->automatedTesting == 1) $hw->automatedTesting=true; else $hw->automatedTesting=false;
		return $hw;
	}
	
	// List of homeworks that are due for submitting (deadline is near)
	// Gets no more than $limit homeworks, ordered by deadline descending
	// Doesn't return homeworks on inactive student modules
	public static function getLatestForStudent($student, $limit) {
		// FIXME u ovaj upit je ugrađena i provjera da li je aktivan studentski modul
		$results = DB::query_table("SELECT z.id id, z.naziv name, z.predmet CourseUnit, z.akademska_godina AcademicYear, z.zadataka nrAssignments, z.bodova maxScore, z.komponenta ScoringElement, UNIX_TIMESTAMP(z.rok) deadline, z.aktivna active, z.programskijezik ProgrammingLanguage, z.automatsko_testiranje automatedTesting, z.attachment, z.dozvoljene_ekstenzije allowedExtensions, UNIX_TIMESTAMP(z.vrijemeobjave) publishedTime, z.postavka_zadace text 
		FROM zadaca as z, student_predmet as sp, ponudakursa as pk, predmet as p
		WHERE z.predmet=pk.predmet AND z.akademska_godina=pk.akademska_godina AND sp.student=$student AND sp.predmet=pk.id AND pk.predmet=p.id AND z.rok>curdate() AND z.aktivna=1
		ORDER BY rok DESC
		LIMIT $limit");
		$homeworks = array();
		foreach ($results as $hw) {
			// Skip if student has passing grade (optimize?)
			$grade = DB::get("SELECT COUNT(*) FROM konacna_ocjena WHERE predmet=" . $hw['CourseUnit'] . " AND ocjena>=6");
			if ($grade > 0) continue;

			$hw = Util::array_to_class($hw, "Homework", array("CourseUnit", "AcademicYear", "ScoringElement", "ProgrammingLanguage"));
			if ($hw->active == 1) $hw->active=true; else $hw->active=false; // FIXME use boolean in database
			if ($hw->attachment == 1) $hw->attachment=true; else $hw->attachment=false;
			if ($hw->automatedTesting == 1) $hw->automatedTesting=true; else $hw->automatedTesting=false;
			
			$homeworks[] = $hw;
		}
		
		return $homeworks;
	}
	
	// List of homeworks that student submitted and were recently reviewed
	// No more than $limit homeworks, ordered by time of review descending
	public static function getReviewedForStudent($student, $limit) {
		// Not very optimal...
		$results = DB::query_table("SELECT z.id id, z.naziv name, z.predmet CourseUnit, z.akademska_godina AcademicYear, z.zadataka nrAssignments, z.bodova maxScore, z.komponenta ScoringElement, UNIX_TIMESTAMP(z.rok) deadline, z.aktivna active, z.programskijezik ProgrammingLanguage, z.automatsko_testiranje automatedTesting, z.attachment, z.dozvoljene_ekstenzije allowedExtensions, UNIX_TIMESTAMP(z.vrijemeobjave) publishedTime, z.postavka_zadace text 
		FROM zadatak as zk, zadaca as z, predmet as p 
		WHERE zk.student=$student and zk.status!=1 and zk.status!=4 and zk.zadaca=z.id and z.predmet=p.id and zk.vrijeme < SUBDATE(NOW(), INTERVAL 1 MONTH)
		ORDER BY zk.id DESC 
		LIMIT $limit");
		$repeated = array();
		$homeworks = array();
		foreach ($results as $hw) {
			if ( in_array($hw['id'], $repeated) ) continue; // ne prijavljujemo vise puta istu zadacu
			$repeated[] = $hw['id'];
			
			$hw = Util::array_to_class($hw, "Homework", array("CourseUnit", "AcademicYear", "ScoringElement", "ProgrammingLanguage"));
			if ($hw->active == 1) $hw->active=true; else $hw->active=false; // FIXME use boolean in database
			if ($hw->attachment == 1) $hw->attachment=true; else $hw->attachment=false;
			if ($hw->automatedTesting == 1) $hw->automatedTesting=true; else $hw->automatedTesting=false;
			
			$homeworks[] = $hw;
		}
		return $homeworks;
	}
	
	// List of homeworks published for course
	public static function fromCourse($courseUnitId, $academicYearId) {
		$homeworks = DB::query_table("SELECT id, naziv name, predmet CourseUnit, akademska_godina AcademicYear, zadataka nrAssignments, bodova maxScore, komponenta ScoringElement, UNIX_TIMESTAMP(rok) deadline, aktivna active, programskijezik ProgrammingLanguage, automatsko_testiranje automatedTesting, attachment, dozvoljene_ekstenzije allowedExtensions, UNIX_TIMESTAMP(vrijemeobjave) publishedTime, postavka_zadace text FROM zadaca WHERE predmet=$courseUnitId and akademska_godina=$academicYearId ORDER BY komponenta, naziv");
		foreach ($homeworks as &$hw) {
			$hw = Util::array_to_class($hw, "Homework", array("CourseUnit", "AcademicYear", "ScoringElement", "ProgrammingLanguage"));
			if ($hw->active == 1) $hw->active=true; else $hw->active=false; // FIXME use boolean in database
			if ($hw->attachment == 1) $hw->attachment=true; else $hw->attachment=false;
			if ($hw->automatedTesting == 1) $hw->automatedTesting=true; else $hw->automatedTesting=false;
		}
		return $homeworks;
	}
	
	// List of homeworks published for course
	public static function fromCourseOffering($courseOfferingId) {
		$homeworks = DB::query_table("SELECT z.id id, z.naziv name, z.predmet CourseUnit, z.akademska_godina AcademicYear, z.zadataka nrAssignments, z.bodova maxScore, z.komponenta ScoringElement, UNIX_TIMESTAMP(z.rok) deadline, z.aktivna active, z.programskijezik ProgrammingLanguage, z.automatsko_testiranje automatedTesting, z.attachment, z.dozvoljene_ekstenzije allowedExtensions, UNIX_TIMESTAMP(z.vrijemeobjave) publishedTime, z.postavka_zadace text FROM zadaca z, ponudakursa pk WHERE z.predmet=pk.predmet and z.akademska_godina=pk.akademska_godina AND pk.id=$courseOfferingId ORDER BY komponenta, naziv");
		foreach ($homeworks as &$hw) {
			$hw = Util::array_to_class($hw, "Homework", array("CourseUnit", "AcademicYear", "ScoringElement", "ProgrammingLanguage"));
			if ($hw->active == 1) $hw->active=true; else $hw->active=false; // FIXME use boolean in database
			if ($hw->attachment == 1) $hw->attachment=true; else $hw->attachment=false;
			if ($hw->automatedTesting == 1) $hw->automatedTesting=true; else $hw->automatedTesting=false;
		}
		return $homeworks;
	}
}

?>
