<?php

// Modul: lms/attendance
// Klasa: ZClass
// Opis: jedan čas

// Z u imenu znači "Zamger" i dodato je jer je class rezervisana riječ u PHPu
// (i mnogim drugim jezicima)


require_once(Config::$backend_path."lms/attendance/Group.php");

class ZClass {
	public $id;
	public $datetime, $teacher, $Group, $ScoringElement;
	
	public static function fromId($id) {
		// TODO combine date and time into a single timestamp db field as in the rest of db
		$zclass = DB::query_assoc("SELECT c.id id, UNIX_TIMESTAMP(c.datum+c.vrijeme) datetime, c.nastavnik teacher, c.labgrupa _Group, c.komponenta ScoringElement FROM cas as c WHERE c.id=$id");
		if (!$zclass) throw new Exception("Unknown class $id", "404");
		$zclass['Group'] = $zclass['_Group']; unset($zclass['_Group']); // reserved word in SQL
		$zclass = Util::array_to_class($zclass, "ZClass", array("Group", "ScoringElement"));
		
		// $teacher is a Person
		$zclass->teacher = new UnresolvedClass("Person", $zclass->teacher, $zclass->teacher);
		
		return $zclass;
	}

	public static function fromGroupAndScoringElement($groupId, $scoringElementId) {
		$classes = DB::query_table("SELECT id, UNIX_TIMESTAMP(datum+vrijeme) datetime, nastavnik teacher, labgrupa _Group, komponenta ScoringElement FROM cas WHERE labgrupa=$groupId AND komponenta=$scoringElementId ORDER BY datetime");
		foreach($classes as &$zclass) {
			$zclass['Group'] = $zclass['_Group']; unset($zclass['_Group']); // reserved word in SQL
			$zclass = Util::array_to_class($zclass, "ZClass", array("Group", "ScoringElement"));
			$zclass->teacher = new UnresolvedClass("Person", $zclass->teacher, $zclass->teacher);
		}
		return $classes;
	}

	public static function fromGroup($groupId) {
		$classes = DB::query_table("SELECT id, UNIX_TIMESTAMP(datum+vrijeme) datetime, nastavnik teacher, labgrupa _Group, komponenta ScoringElement FROM cas WHERE labgrupa=$groupId ORDER BY datetime");
		foreach($classes as &$zclass) {
			$zclass['Group'] = $zclass['_Group']; unset($zclass['_Group']); // reserved word in SQL
			$zclass = Util::array_to_class($zclass, "ZClass", array("Group", "ScoringElement"));
			$zclass->teacher = new UnresolvedClass("Person", $zclass->teacher, $zclass->teacher);
		}
		return $classes;
	}
}

?>
