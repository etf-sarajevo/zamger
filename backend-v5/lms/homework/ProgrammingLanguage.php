<?php

// Modul: lms/homework
// Klasa: ProgrammingLanguage
// Opis: pomoćna klasa za zadatke koji predstavljaju programski kod


require_once(Config::$backend_path."core/DB.php");

class ProgrammingLanguage {
	public $id;
	public $name, $geshi, $extension;
	
	public static function fromId($id) {
		$q10 = DB::query("select naziv, geshi, ekstenzija from programskijezik where id=$id");
		if (mysql_num_rows($q10)<1) {
			throw new Exception("unknown programming language");
		}

		$pl = new ProgrammingLanguage;
		$pl->id = $id;
		$pl->name = mysql_result($q10,0,0);
		$pl->geshi = mysql_result($q10,0,1);
		$pl->extension = mysql_result($q10,0,2);

		return $pl;
	}
}

?>
