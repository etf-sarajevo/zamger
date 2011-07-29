<?php

// Modul: core
// Klasa: AcademicYear
// Opis: akademska godina


require_once(Config::$backend_path."core/DB.php");

class AcademicYear {
	public $id;
	public $name, $current;
	
	public static function fromId($id) {
		$q10 = DB::query("select naziv, aktuelna from akademska_godina where id=$id");
		if (mysql_num_rows($q10)<1) {
			throw new Exception("nepostojeca akademska godina");
		}
		$ay = new AcademicYear;
		$ay->id = $id;
		$ay->name = mysql_result($q10,0,0);
		if (mysql_result($q10,0,1) == 1) $ay->current=true; else $ay->current=false;
		// TODO dodati ostalo
		
		return $ay;
	}

	// Returns AcademicYear object for current year
	public static function getCurrent() {
		$q10 = DB::query("select id, naziv from akademska_godina where aktuelna=1");
		if (mysql_num_rows($q10)<1) {
			// There should always be at least one academic year marked current!
			throw new Exception("ne postoji aktuelna godina");
		}

		$ay = new AcademicYear;
		$ay->id = mysql_result($q10,0,0);
		$ay->name = mysql_result($q10,0,1);
		$ay->current = true;
		return $ay;
	}
}