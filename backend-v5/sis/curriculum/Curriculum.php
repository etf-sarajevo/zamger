<?php

// Modul: sis/curriculum
// Klasa: Curriculum
// Opis: nastavni plan i program studija


require_once(Config::$backend_path."sis/curriculum/CurriculumCourse.php");

class Curriculum {
	public $id;
	public $Programme, $startingYear, $accepted;
	
	public static function fromId($id) {
		$cur = DB::query_assoc("SELECT id, studij Programme, godina_vazenja startingYear, usvojen accepted FROM plan_studija WHERE id=$id");
		if (!$cur) throw new Exception("Unknown curriculum $id", "404");
		
		$cur = Util::array_to_class($cur, "Curriculum", array("Programme"));
		$cur->startingYear = new UnresolvedClass("AcademicYear", $cur->startingYear, $cur->startingYear);
		if ($cur->accepted == 1) $cur->accepted=true; else $cur->accepted=false; // FIXME use boolean in database
		return $cur;
	}
}


?>
