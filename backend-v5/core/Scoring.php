<?php

// Modul: core
// Klasa: Scoring
// Opis: sistem bodovanja na predmetu, u biti kolekcija scoring elemenata


require_once(Config::$backend_path."core/ScoringElement.php");

class Scoring {
	public $id, $name, $elements;
	
	public static function fromId($id) {
		$scoring = DB::query_assoc("select id, naziv name from tippredmeta where id=$id");
		if (!$scoring) throw new Exception("Unknown scoring system $id", "404");
		$scoring = Util::array_to_class($scoring, "Scoring");
		$scoring->elements = ScoringElement::fromScoring($id);
		return $scoring;
	}
}

?>
