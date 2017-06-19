<?php

// Modul: core
// Klasa: AccessControl
// Opis: Prava pristupa

// Klasa implementira niz boolean funkcija kojom utvrđujemo da li trenutno prijavljeni korisnik ima 
// određeni nivo pristupa

require_once(Config::$backend_path."core/CourseOffering.php");
require_once(Config::$backend_path."core/CourseUnitYear.php");
require_once(Config::$backend_path."lms/attendance/Group.php");

class AccessControl {

	public static function all() { return true; }

	public static function loggedIn() {
		if (Session::$userid == 0) return false;
		return true;
	}

	public static function self($id) {
		if (Session::$userid == $id) return true;
		return false;
	}
	
	public static function privilege($name) {
		return in_array($name, Session::$privileges);
	}
	
	public static function teacherLevel($course, $year) {
		// Avoid unneccessary queryies
		$cuy = CourseUnitYear::fromCourseAndYearQuick($course, $year);
		return $cuy->teacherAccessLevel(Session::$userid);
	}
	
	// Teacher access level for given group
	public static function teacherLevelGroup($group) {
		// Can't avoid a query :(
		try {
			$grp = Group::fromId($group);
		} catch(Exception $e) {
			return false; // Unknown group
		}
		$lvl = AccessControl::teacherLevel($grp->CourseUnit->id, $grp->AcademicYear->id);
		if ($lvl == "asistent") {
			// If the result of query below is empty, all groups are allowed
			// Otherwise, only listed groups are allowed
			$allowed_groups = DB::query_varray("SELECT o.labgrupa FROM ogranicenje as o, labgrupa as l WHERE o.nastavnik=" . Session::$userid." AND o.labgrupa=l.id AND l.predmet=" . $grp->CourseUnit->id . " AND l.akademska_godina=" . $grp->AcademicYear->id);
			if (!empty($allowed_groups) && !in_array($group, $allowed_groups))
				return "";
		}
		return $lvl;
	}
	
	// Does teacher have access to group where student is in
	public static function teacherLevelStudent($course, $year, $student) {
		$lvl = AccessControl::teacherLevel($course, $year);
		if ($lvl != "asistent") return $lvl;
		
		$allowed_groups = DB::query_varray("SELECT o.labgrupa FROM ogranicenje as o, labgrupa as l WHERE o.nastavnik=" . Session::$userid." AND o.labgrupa=l.id AND l.predmet=" . $grp->CourseUnit->id . " AND l.akademska_godina=" . $grp->AcademicYear->id);
		if (empty($allowed_groups)) return $lvl;
		
		$student_groups = Group::fromStudentAndCourse($student, $course, $year);
		foreach ($student_groups as $grp)
			if (!$grp->virtual && in_array($grp->id, $allowed_groups)) return $lvl;
		return "";
	}
	
	public static function isStudent($course, $year) {
		if (CourseOffering::forStudent(Session::$userid, $course, $year))
			return true;
		return false;
	}
}




?>
