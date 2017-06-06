<?php

// Modul: core
// Klasa: Institution
// Opis: institucija


class Institution {
	public $id;
	public $name, $abbrev;
	
	public static function fromId($id) {
		$inst = DB::query_assoc("select id, naziv name, kratki_naziv abbrev from institucija where id=$id");
		if (!$inst) throw new Exception("Unknown institution $id", "404");
		return Util::array_to_class($inst, "Institution");
	}
}

?>
