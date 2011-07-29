<?php

// Modul: lms/attendance
// Klasa: Group
// Opis: nastavna grupa


require_once(Config::$backend_path."core/DB.php");

class Group {
	public $id;
	public $name, $courseUnitId, $academicYearId, $virtual;
	
	public static function fromId($id) {
		$q10 = DB::query("select naziv, predmet, akademska_godina, virtualna from labgrupa where id=$id");
		if (mysql_num_rows($q10)<1) {
			throw new Exception("nepostojeca labgrupa");
		}
		
		$g = new Group;
		$g->id = $id;
		$g->name = mysql_result($q10,0,0);
		$g->courseUnitId = mysql_result($q10,0,1);
		$g->academicYearId = mysql_result($q10,0,2);
		if ( mysql_result($q10,0,3) == 1) $g->virtual=true; else $g->virtual=false;

		return $g;
	}

	// Get groups that student is a member of for given course unit
	public static function fromStudentAndCourse($student, $courseUnitId, $academicYearId) {
		$q10 = DB::query("select l.id, l.naziv, l.virtualna from student_labgrupa as sl, labgrupa as l where l.predmet=$courseUnitId and l.akademska_godina=$academicYearId and l.id=sl.labgrupa and sl.student=$student");
		$groups = array();
		while ($r10 = mysql_fetch_row($q10)) {
			$g = new Group;
			$g->id = $r10[0];
			$g->name = $r10[1];
			$g->courseUnitId = $courseUnitId;
			$g->academicYearId = $academicYearId;
			if ($r10[2] == 1) $g->virtual=true; else $g->virtual=false;
			array_push($groups, $g);
		}
		return $groups;
	}
	
	// Test if student is a member of group
	public function isMember($student) {
		$q10 = DB::query("select count(*) from student_labgrupa where student=$student and labgrupa=".$this->id);
		if (mysql_result($q10,0,0) == 0) return false;
		return true;
	}

	// Test if teacher is granted access only to specific groups and this one is not among them (in that case return value is false)
	public function isTeacher($teacher) {
		$q20 = DB::query("select o.labgrupa from ogranicenje as o, labgrupa as l where o.nastavnik=$teacher and o.labgrupa=l.id and l.predmet=".$this->courseUnitId." and l.akademska_godina=".$this->academicYearId);
		if (mysql_num_rows($q20) == 0) return true;

		// Avoid second query
		$result = false;
		while ($r20 = mysql_fetch_row($q20)) {
			if ($r20[0] == $this->id) { $result = 1; break; }
		}
		return $result;
	}
}

?>
