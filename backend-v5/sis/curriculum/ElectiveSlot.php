<?php

// Modul: sis/curriculum
// Klasa: ElectiveSlot
// Opis: spisak predmeta koje je dopušteno birati na određenom mjestu za izborni predmet u npp-u


class ElectiveSlot {
	public $id;
	public $courseDescriptions;
	
	public static function fromId($id) {
		$es = new ElectiveSlot;
		$es->id = $id;
		$cds = DB::query_varray("SELECT pasos_predmeta FROM plan_izborni_slot WHERE id=$id");
		foreach($cds as &$cd)
			$cd = new UnresolvedClass("CourseDescription", $cd, $cd);
		$es->courseDescriptions = $cds;
		return $es;
	}
}


?>
