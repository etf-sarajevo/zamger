<?php

// Modul: core
// Klasa: EnrollmentType
// Opis: način studiranja (šifrarnik)


class EnrollmentType {
	public $id, $name, $enrollmentPossible;
	
	public static function fromId($id) {
		$et = DB::query_assoc("SELECT id, naziv name, moguc_upis enrollmentPossible FROM nacin_studiranja WHERE id=$id");
		
		if (!$et) throw new Exception("Unknown enrollment type $id", "404");
		$et = Util::array_to_class($et, "EnrollmentType", array());
		if ($et->enrollmentPossible == 1) $et->enrollmentPossible=true; else $et->enrollmentPossible=false; // FIXME use boolean in database
		return $et;
	}
}
