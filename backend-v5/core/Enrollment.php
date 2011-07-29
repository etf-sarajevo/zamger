<?php

// Modul: core
// Klasa: Enrollment
// Opis: podaci o statusu studenta na nekom studiju


require_once(Config::$backend_path."core/DB.php");
require_once(Config::$backend_path."core/Programme.php");


class Enrollment {
	public $student, $programmeId, $semester, $academicYearId, $type /* provjeriti terminologiju */, $repeat, $curriculumId, $decisionId;
	
	public $programme;
	
	// The programme that given student is enrolled in in current academic year
	// If both even and odd semester exist for current year, even (bigger) semester will be returned
	public static function getCurrentForStudent($student) {
		$q10 = DB::query("select ss.studij, ss.semestar, ss.akademska_godina, ss.nacin_studiranja, ss.ponovac, ss.plan_studija, ss.odluka from student_studij as ss, akademska_godina as ag where ss.student=$student and ss.akademska_godina=ag.id and ag.aktuelna=1 order by semestar desc limit 1");
		if (mysql_num_rows($q10)<1) {
			throw new Exception("student trenutno ne studira");
		}

		$e = new Enrollment;
		$e->student = $student;
		$e->programmeId = mysql_result($q10,0,0);
		$e->semester = mysql_result($q10,0,1);
		$e->academicYearId = mysql_result($q10,0,2);
		$e->type = mysql_result($q10,0,3);
		if (mysql_result($q10,0,4)==1) $e->repeat=true; else $e->repeat=false;
		$e->curriculumId = mysql_result($q10,0,5);
		$e->decisionId = mysql_result($q10,0,6);
		// TODO dodati ostalo, posebno reference na druge klase

		// To be initialized as neccessary
		$e->programme = 0;

		return $e;
	}
}