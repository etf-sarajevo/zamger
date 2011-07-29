<?php

// Modul: core
// Klasa: CourseUnit
// Opis: predmet


require_once(Config::$backend_path."core/DB.php");

class CourseUnit {
	public $id;
	public $code, $name, $shortName;
	
	public static function fromId($id) {
		$q10 = DB::query("select sifra, naziv, kratki_naziv from predmet where id=$id");
		if (mysql_num_rows($q10)<1) {
			throw new Exception("nepostojeci predmet");
		}
		$c = new CourseUnit;
		$c->id = $id;
		$c->code = mysql_result($q10,0,0);
		$c->name = mysql_result($q10,0,1);
		$c->shortName = mysql_result($q10,0,2);
		// TODO dodati ostalo
		return $c;
	}
}