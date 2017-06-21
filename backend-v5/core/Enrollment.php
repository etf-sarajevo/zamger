<?php

// Modul: core
// Klasa: Enrollment
// Opis: podaci o statusu studenta na nekom studiju


require_once(Config::$backend_path."core/EnrollmentType.php");
require_once(Config::$backend_path."core/Programme.php");

class Enrollment {
	public $student, $Programme, $semester, $AcademicYear, $EnrollmentType /* provjeriti terminologiju */, $repeat, $Curriculum, $Decision;
	
	// The programme that given student is enrolled in in current academic year
	// If both even and odd semester exist for current year, even (bigger) semester will be returned
	// If student is not enrolled in current year, returns false
	public static function getCurrentForStudent($studentId) {
		$enr = DB::query_assoc("SELECT ss.student student, ss.studij Programme, ss.semestar semester, ss.akademska_godina AcademicYear, ss.nacin_studiranja EnrollmentType, ss.ponovac _repeat, ss.plan_studija Curriculum, ss.odluka Decision FROM student_studij as ss, akademska_godina as ag WHERE ss.student=$studentId AND ss.akademska_godina=ag.id AND ag.aktuelna=1 ORDER BY semestar DESC LIMIT 1");
		if (!$enr) return false;
		
		$enr['repeat'] = $enr['repeat']; unset($enr['repeat']); // reserved word in SQL
		$enr = Util::array_to_class($enr, "Enrollment", array("Programme", "AcademicYear", "EnrollmentType", "Curriculum", "Decision"));
		$enr->student = new UnresolvedClass("Person", $studentId, $enr->student);
		if ($enr->repeat == 1) $enr->repeat=true; else $enr->repeat=false; // FIXME use boolean in database
		return $enr;
	}
	
	// History of all enrollments of given student, sorted by academic year and semester
	public static function getAllForStudent($studentId) {
		$enrolls = DB::query_table("select student, studij Programme, semestar semester, akademska_godina AcademicYear, nacin_studiranja EnrollmentType, ponovac _repeat, plan_studija Curriculum, odluka Decision from student_studij where student=$studentId order by akademska_godina, semestar");

		foreach($enrolls as &$enr) {
			$enr['repeat'] = $enr['repeat']; unset($enr['repeat']); // reserved word in SQL
			$enr = Util::array_to_class($enr, "Enrollment", array("Programme", "AcademicYear", "EnrollmentType", "Curriculum", "Decision"));
			$enr->student = new UnresolvedClass("Person", $studentId, $enr->student);
			if ($enr->repeat == 1) $enr->repeat=true; else $enr->repeat=false; // FIXME use boolean in database
			return $enr;
		}
		return $enrolls;
	}
}
