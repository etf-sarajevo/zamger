<?php

// Modul: core
// Klasa: Scoring
// Opis: sistem bodovanja na predmetu, u biti kolekcija scoring elemenata


require_once(Config::$backend_path."core/DB.php");

class Scoring {
	public $id, $name;
	
	public static function fromId($id) {
		$q10 = DB::query("select naziv from tippredmeta where id=$id");
		if (mysql_num_rows($q10)<1) {
			throw new Exception("unknown scoring");
		}
		$s = new Scoring;
		$s->id = $id;
		$s->name = mysql_result($q10,0,0);

		return $s;
	}

	// Get list of scoring elements of given type (see ScoringElement::$type), if type is 0 get all
	public function getScoringElements($seType = 0) {
		$sql = "";
		if ($seType != 0) $sql = " and k.tipkomponente=".intval($seType);

		$q10 = myquery("select k.id, k.naziv, k.gui_naziv, k.kratki_gui_naziv, k.tipkomponente, k.maxbodova, k.prolaz, k.opcija, k.uslov from komponenta as k, tippredmeta_komponenta as tpk where tpk.komponenta=k.id and tpk.tippredmeta=".$this->id.$sql);
		$ses = array();
		while ($r10 = mysql_fetch_row($q10)) {
			$se = new ScoringElement;
			$se->id = $r10[0];
			$se->name = $r10[1];
			$se->guiName = $r10[2];
			$se->shortGuiName = $r10[3];
			$se->type = $r10[4];
			$se->max = $r10[5];
			$se->pass = $r10[6];
			$se->option = $r10[7];
			if ($r10[8] == 1) $se->mandatory = true; else $se->mandatory = false;

			array_push($ses, $se);
		}
		return $ses;
	}
}

?>