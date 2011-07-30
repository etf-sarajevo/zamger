<?php

// Modul: core
// Klasa: Programme
// Opis: studijski program


require_once(Config::$backend_path."core/DB.php");
// require_once(Config::$backend_path."core/Institution.php"); -- čim bude napravljen
require_once(Config::$backend_path."core/ProgrammeType.php");


class Programme {
	public $id;
	public $name, $nameEn, $shortName, $institutionId, $acceptsStudents, $typeId;
	
	public $institution, $type;
	
	public static function fromId($id) {
		$q10 = DB::query("select s.naziv, s.naziv_en, s.kratkinaziv, s.institucija, s.moguc_upis, s.tipstudija, ts.naziv, ts.ciklus, ts.trajanje from studij as s, tipstudija as ts where s.id=$id and s.tipstudija=ts.id");
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

		$p->type = new ProgrammeType;
		$p->type->id = $p->typeId;
		$p->type->name = mysql_result($q10,0,6);
		$p->type->cycle = mysql_result($q10,0,7);
		$p->type->duration = mysql_result($q10,0,8);

		// To be initialized as neccessary
		$p->institution = 0;

		return $p;
	}
}