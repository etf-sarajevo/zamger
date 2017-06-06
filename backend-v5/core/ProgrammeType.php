<?php

// Modul: core
// Klasa: ProgrammeType
// Opis: tip studijskog programa


class ProgrammeType {
	public $id;
	public $name, $cycle, $duration;
	
	public static function fromId($id) {
		$pt = DB::query_assoc("select id, naziv name, ciklus cycle, trajanje duration from tipstudija where id=$id");
		if (!$pt) throw new Exception("Unknown programme type $id", "404");
		$pt = Util::array_to_class($pt, "ProgrammeType");
		return $pt;
	}
}
