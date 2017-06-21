<?php

// Modul: core
// Klasa: Programme
// Opis: studijski program


class Programme {
	public $id;
	public $name, $nameEn, $abbrev, $Institution, $acceptsStudents, $ProgrammeType;
	
	public static function fromId($id) {
		$prog = DB::query_assoc("select s.id id, s.naziv name, s.naziv_en nameEn, s.kratkinaziv abbrev, s.institucija Institution, s.moguc_upis acceptsStudents, s.tipstudija ProgrammeType from studij as s where s.id=$id");
		if (!$prog) throw new Exception("Unknown programme $id", "404");
		$prog = Util::array_to_class($prog, "Programme", array("Institution", "ProgrammeType"));
		if ($prog->acceptsStudents == 1) $prog->acceptsStudents=true; else $prog->acceptsStudents=false; // FIXME use boolean in database
		return $prog;
	}
}
