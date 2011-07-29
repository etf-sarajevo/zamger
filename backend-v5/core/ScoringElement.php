<?php

// Modul: core
// Klasa: ScoringElement
// Opis: jedan od elemenata koji ulaze u ukupan broj bodova na predmetu, npr: zadaća, ispit, prisustvo...


require_once(Config::$backend_path."core/DB.php");

class ScoringElement {
	public $id;
	public $name, $guiName, $shortGuiName, $type, $max, $pass, $option, $mandatory;
	
	public static function fromId($id) {
		$q10 = DB::query("select naziv, gui_naziv, kratki_gui_naziv, tipkomponente, maxbodova, prolaz, opcija, uslov from komponenta where id=$id");
		if (mysql_num_rows($q10)<1) {
			throw new Exception("nepostojeca komponenta");
		}
		$se = new ScoringElement;
		$se->id = $id;
		$se->name = mysql_result($q10,0,0);
		$se->guiName = mysql_result($q10,0,1);
		$se->shortGuiName = mysql_result($q10,0,2);
		$se->type = mysql_result($q10,0,3);
		$se->max = mysql_result($q10,0,4);
		$se->pass = mysql_result($q10,0,5);
		$se->option = mysql_result($q10,0,6);
		if (mysql_result($q10,0,7) == 1) $se->mandatory = true; else $se->mandatory = false;
		return $se;
	}
}

?>