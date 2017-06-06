<?php

// Modul: core
// Klasa: AccessControl
// Opis: Prava pristupa

// Klasa implementira niz boolean funkcija kojom utvrđujemo da li trenutno prijavljeni korisnik ima 
// određeni nivo pristupa

require_once(Config::$backend_path."core/CourseOffering.php");
require_once(Config::$backend_path."core/CourseUnitYear.php");

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
		$cuy = new CourseUnitYear;
		$cuy->CourseUnit = $course;
		$cuy->AcademicYear = $year;
		return $cuy->teacherAccessLevel(Session::$userid);
	}
	
	public static function isStudent($course, $year) {
		if (CourseOffering::forStudent(Session::$userid, $course, $year))
			return true;
		return false;
	}
}




?>
