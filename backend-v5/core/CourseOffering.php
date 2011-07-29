<?php

// Modul: core
// Klasa: CourseOffering
// Opis: ponuda kursa na datom studiju i semestru u datoj ak. godini


require_once(Config::$backend_path."core/DB.php");
require_once(Config::$backend_path."core/CourseUnit.php");

class CourseOffering {
	public $id;
	public $courseUnitId, $academicYearId, $programmeId, $semester, $mandatory;
	public $courseUnit;
	
	public static function fromId($id) {
		$q10 = DB::query("select predmet, akademska_godina, studij, semestar, obavezan from ponudakursa where id=$id");
		if (mysql_num_rows($q10)<1) {
			throw new Exception("nepostojeca ponudakursa");
		}
		$co = new CourseOffering;
		$co->id = $id;
		$co->courseUnitId = mysql_result($q10,0,0);
		$co->academicYearId = mysql_result($q10,0,1);
		$co->programmeId = mysql_result($q10,0,2);
		$co->semesterId = mysql_result($q10,0,3);
		if (mysql_result($q10,0,4) == 1) $co->mandatory = true; else $co->mandatory = false;

		$co->courseUnit = 0;

		return $co;
	}

	// List all courses offered at specified academic year, programme and semester
	// ordered by name alphabetically
	// 0 means all
	public static function getCoursesOffered($academicYearId = 0, $programmeId = 0, $semestar = 0) {
		$sql = "";
		if ($academicYearId > 0) $sql .= " and pk.akademska_godina=$academicYearId";
		if ($programmeId > 0) $sql .= " and pk.studij=$programmeId";
		if ($semestar > 0) $sql .= " and pk.semestar=$semestar";

		$q1 = myquery("select pk.id, pk.predmet, pk.akademska_godina, pk.studij, pk.semestar, pk.obavezan, p.naziv, p.sifra from predmet as p, ponudakursa as pk where pk.predmet=p.id $sql order by p.naziv");
		$courses = array();
		while ($r1 = mysql_fetch_row($q1)) {
			$co = new CourseOffering;
			$co->id = $r1[0];
			$co->courseUnitId = $r1[1];
			$co->academicYearId = $r1[2];
			$co->programmeId = $r1[3];
			$co->semester = $r1[4];
			if ($r1[5] == 1) $co->mandatory = true; else $co->mandatory = false;

			$co->courseUnit = new CourseUnit;
			$co->courseUnit->id = $r1[1];
			$co->courseUnit->name = $r1[6];
			$co->courseUnit->code = $r1[7];

			array_push($courses, $co);
		}
		return $courses;
	}

}