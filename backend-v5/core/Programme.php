<?php

// Modul: core
// Klasa: Programme
// Opis: studijski program


require_once(Config::$backend_path."core/DB.php");
// require_once($backend_path."core/Institution.php"); -- čim bude napravljen
// require_once($backend_path."core/ProgrammeType.php"); -- čim bude napravljen


class Programme {
	public $id;
	public $name, $nameEn, $shortName, $institutionId, $acceptsStudents, $typeId;
	
	public $institution, $type;
	
	public static function fromId($id) {
		$q10 = DB::query("select naziv, naziv_en, kratki_naziv, institucija, moguc_upis, tipstudija from studij where id=$id");
		if (mysql_num_rows($q10)<1) {
			throw new Exception("nepostojeci studij");
		}

		$p = new Programme;
		$p->id = $id;
		$p->name = mysql_result($q10,0,0);
		$p->nameEn = mysql_result($q10,0,1);
		$p->shortName = mysql_result($q10,0,2);
		$p->institutionId = mysql_result($q10,0,3);
		if (mysql_result($q10,0,4)==1) $p->acceptsStudents=true; else $p->acceptsStudents=false;
		$p->typeId = mysql_result($q10,0,5);

		// To be initialized as neccessary
		$p->institution = 0;
		$p->type = 0;

		return $p;
	}
}