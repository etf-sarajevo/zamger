<?php

// Modul: core
// Klasa: AcademicYear
// Opis: akademska godina


class AcademicYear {
	public $id;
	public $name, $isCurrent;
	
	public static function fromId($id) {
		$ay = DB::query_assoc("select id id, naziv name, aktuelna isCurrent from akademska_godina where id=$id");
		if (!$ay) throw new Exception("Unknown academic year", "404");
		$ay = Util::array_to_class($ay, "AcademicYear");
		if ($ay->isCurrent == 1) $ay->isCurrent=true; else $ay->isCurrent=false;  // FIXME use boolean in database
		return $ay;
	}

	// Returns AcademicYear object for current year
	public static function getCurrent() {
		// ASSUMPTION: There should always be exactly one academic year marked current!
		$ay = DB::query_assoc("select id id, naziv name, aktuelna isCurrent from akademska_godina where aktuelna=1");
		if (!$ay) throw new Exception("No academic year is current", "404");
		$ay = Util::array_to_class($ay, "AcademicYear");
		$ay->isCurrent=true; // FIXME use boolean in database
		return $ay;
	}
}

?>
