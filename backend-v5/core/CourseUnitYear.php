<?php

// Modul: core
// Klasa: CourseUnitYear
// Opis: ova klasa sadrži podatke koji su karakteristični za SVE ponude kursa 
// (CourseOffering) u datoj godini, ali ne i za ponude kursa iz ranijih godina!
// Razbijanje ovih podataka po CourseOffering instancama ne bi bilo 
// normalizovano.


require_once(Config::$backend_path."core/AcademicYear.php");
require_once(Config::$backend_path."core/CourseOffering.php");
require_once(Config::$backend_path."core/CourseUnit.php");
require_once(Config::$backend_path."core/Scoring.php");
require_once(Config::$backend_path."core/Person.php");
require_once(Config::$backend_path."core/Portfolio.php");

class CourseUnitYear {
	public $CourseUnit, $AcademicYear, $Scoring;

	public static function fromCourseAndYear($courseUnitId, $academicYearId) {
		$cuy = DB::query_assoc("SELECT agp.predmet CourseUnit, agp.akademska_godina AcademicYear, agp.tippredmeta Scoring FROM akademska_godina_predmet as agp, tippredmeta as tp where agp.akademska_godina=$academicYearId and agp.predmet=$courseUnitId and agp.tippredmeta=tp.id");
		if (!$cuy) throw new Exception("Unknown course unit / year", "404");
		
		$cuy = Util::array_to_class($cuy, "CourseUnitYear", array("CourseUnit", "AcademicYear", "Scoring"));
		$cuy->courseOfferings = CourseOffering::fromCourseAndYear($courseUnitId, $academicYearId);
		$cuy->staff = $cuy->getTeachingStaff();
		
		return $cuy;
	}

	// Teachers access level on course
	public function teacherAccessLevel($teacher) {
		return DB::get("select nivo_pristupa from nastavnik_predmet where nastavnik=$teacher and predmet=".$this->CourseUnit." and akademska_godina=".$this->AcademicYear);
	}
	
	// Get teaching staff for course unit / year
	public function getTeachingStaff() {
		if (is_object($this->CourseUnit)) $cu = $this->CourseUnit->id; else $cu = $this->CourseUnit;	
		if (is_object($this->AcademicYear)) $ay = $this->AcademicYear->id; else $ay = $this->AcademicYear;
		
		$staff = DB::query_table("SELECT a.osoba Person, ans.id status_id, ans.naziv status FROM angazman a, angazman_status ans WHERE a.predmet=$cu AND a.akademska_godina=$ay AND a.angazman_status=ans.id ORDER BY ans.id");
		foreach($staff as &$member) {
			$member['Person'] = Person::fromId($member['Person']);
			$member['Person']->getTitles();
		}
		return $staff;
	}

	// List of courses that given person has access privileges for
	public static function forTeacher($teacherId, $academicYearId = 0) {
		if ($academicYearId == 0)
			$academicYearId = AcademicYear::getCurrent()->id;
		$cuys = DB::query_table("SELECT p.id CourseUnit, np.akademska_godina AcademicYear, agp.tippredmeta Scoring FROM predmet p, nastavnik_predmet np, akademska_godina_predmet agp WHERE np.nastavnik=$teacherId AND np.akademska_godina=$academicYearId AND np.predmet=p.id and agp.akademska_godina=$academicYearId and agp.predmet=np.predmet ORDER BY p.naziv");
		foreach($cuys as &$cuy) {
			$cuy = Util::array_to_class($cuy, "CourseUnitYear", array("AcademicYear", "Scoring"));
			$cuy->CourseUnit = CourseUnit::fromId($cuy->CourseUnit);
		}
		return $cuys;
	}
	
}

?>
