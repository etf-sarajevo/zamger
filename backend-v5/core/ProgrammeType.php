<?php

// Modul: core
// Klasa: ProgrammeType
// Opis: tip studijskog programa


require_once(Config::$backend_path."core/DB.php");


class ProgrammeType {
	public $id;
	public $name, $cycle, $duration;
	
	public static function fromId($id) {
		$q10 = DB::query("select naziv, ciklus, trajanje from tipstudija where id=$id");
		if (mysql_num_rows($q10)<1) {
			throw new Exception("unknown programme type");
		}

		$pt = new ProgrammeType;
		$pt->id = $id;
		$pt->name = mysql_result($q10,0,0);
		$pt->cycle = mysql_result($q10,0,1);
		$pt->duration = mysql_result($q10,0,2);

		return $p;
	}
}