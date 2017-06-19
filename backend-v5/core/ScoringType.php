<?php

// Modul: core
// Klasa: ScoringType
// Opis: opisuje tip komponente bodovanja, sa lms klasama koje im odgovaraju


class ScoringType {
	public $id;
	public $name;
	
	public static function fromId($id) {
		$st = DB::query_assoc("SELECT id, naziv name FROM tipkomponente WHERE id=$id");
		if (!$st) throw new Exception("Unknown scoring type $id", "404");
		return Util::array_to_class($st, "ScoringType");
	}
}

?>
