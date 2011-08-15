<?php

// Modul: lms/projects
// Klasa: Project
// Opis: jedan projekat / seminarski rad

require_once(Config::$backend_path."core/DB.php");
require_once(Config::$backend_path."core/Person.php");

class Project {
	public $id;
	public $name, $courseUnitId, $academicYearId, $description, $time, $note;
	
	public static function fromId($id) {
		$q10 = DB::query("select naziv, predmet, akademska_godina, opis, UNIX_TIMESTAMP(vrijeme), biljeska from projekat where id=$id");
		if (mysql_num_rows($q10)<1) {
			throw new Exception("unknown project");
		}
		
		$p = new Project;
		$p->id = $id;
		$p->name = mysql_result($q10,0,0);
		$p->courseUnitId = mysql_result($q10,0,1);
		$p->academicYearId = mysql_result($q10,0,2);
		$p->description = mysql_result($q10,0,3);
		$p->time = mysql_result($q10,0,4);
		$p->note = mysql_result($q10,0,5);
		
		return $p;
	}
	
	public static function getAllForCourse($courseUnitId, $academicYearId) {
		$q10 = DB::query("select id, naziv, opis, UNIX_TIMESTAMP(vrijeme), biljeska from projekat where predmet=$courseUnitId and akademska_godina=$academicYearId order by naziv");
		$projects = array();
		while ($r10 = mysql_fetch_row($q10)) {
			$p = new Project;
			$p->id = $r10[0];
			$p->name = $r10[1];
			$p->courseUnitId = $courseUnitId;
			$p->academicYearId = $academicYearId;
			$p->description = $r10[2];
			$p->time = $r10[3];
			$p->note = $r10[4];

			array_push($projects, $p);
		}
		
		return $projects;
	}
	
	public static function fromMemberAndCourse($personId, $courseUnitId, $academicYearId) {
		$q10 = DB::query("select p.id, p.naziv, p.opis, UNIX_TIMESTAMP(p.vrijeme), p.biljeska from projekat as p, student_projekat as sp where p.predmet=$courseUnitId and p.akademska_godina=$academicYearId and p.id=sp.projekat and sp.student=$personId");
		if (mysql_num_rows($q10)<1) {
			throw new Exception("person not a member of any project in this course");
		}
		
		$p = new Project;
		$p->id = $id;
		$p->name = mysql_result($q10,0,0);
		$p->courseUnitId = $courseUnitId;
		$p->academicYearId = $academicYearId;
		$p->description = mysql_result($q10,0,1);
		$p->time = mysql_result($q10,0,2);
		$p->note = mysql_result($q10,0,3);
		
		return $p;
	}

	public function isMember($personId) {
		$q10 = DB::query("select count(*) from student_projekat where student=$personId and projekat=".$this->id);
		if (mysql_result($q10,0,0)>0) return true;
		return false;
	}

	public function getMembers() {
		$q10 = DB::query("select o.id, o.ime, o.prezime, o.brindexa from student_projekat as sp, osoba as o where sp.projekat=".$this->id." and sp.student=o.id");
		$members = array();
		while ($r10 = mysql_fetch_row($q10)) {
			$p = new Person;
			$p->id = $r10[0];
			$p->name = $r10[1];
			$p->surname = $r10[2];
			$p->studentIdNr = $r10[3];
			$p->login = ""; // FIXME: problem je što može postojati više slogova u tabeli auth za jedan login a hoću da izbjegnem podupite

			array_push($members, $p);
		}
		return $members;
	}

	public function addMember($personId) {
		$q10 = DB::query("INSERT INTO student_projekat SET student=$personId, projekat=".$this->id);
	}

	public function deleteMember($personId) {
		$q10 = DB::query("DELETE FROM student_projekat WHERE student=$personId AND projekat=".$this->id);
	}
}

?>