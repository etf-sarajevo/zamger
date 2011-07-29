<?php

// Modul: core
// Klasa: CourseUnitYear
// Opis: ova klasa sadrži podatke koji su karakteristični za SVE ponude kursa 
// (CourseOffering) u datoj godini, ali ne i za ponude kursa iz ranijih godina!
// Razbijanje ovih podataka po CourseOffering instancama ne bi bilo 
// normalizovano.


require_once(Config::$backend_path."core/DB.php");
require_once(Config::$backend_path."core/Scoring.php");

class CourseUnitYear {
	public $courseUnitId, $academicYearId, $scoringId;
	public $scoring;

	public static function fromCourseAndYear($courseUnitId, $academicYearId) {
		$q1 = DB::query("select agp.tippredmeta, tp.naziv from akademska_godina_predmet as agp, tippredmeta as tp where agp.akademska_godina=$academicYearId and agp.predmet=$courseUnitId and agp.tippredmeta=tp.id");
		if (mysql_num_rows($q1) < 1) {
			throw new Exception("predmet se nije izvodio u datoj godini");
		}
		
		$cuy = new CourseUnitYear;
		$cuy->courseUnitId = $courseUnitId;
		$cuy->academicYearId = $academicYearId;
		$cuy->scoringId = mysql_result($q1,0,0);

		$cuy->scoring = new Scoring;
		$cuy->scoring->id = $cuy->scoringId;
		$cuy->scoring->name = mysql_result($q1,0,1);
		
		return $cuy;
	}

	// Nivo pristupa nastavnika na predmet
	// TODO ova funkcija bi trebala u budućnosti biti zamijenjena ACLovima
	public function teacherAccessLevel($teacher) {
		$q1 = DB::query("select nivo_pristupa from nastavnik_predmet where nastavnik=$teacher and predmet=".$this->courseUnitId." and akademska_godina=".$this->academicYearId);
		if (mysql_num_rows($q1) < 1) {
			return "nema";
		}
		return mysql_result($q1,0,0);
	}
}

?>
