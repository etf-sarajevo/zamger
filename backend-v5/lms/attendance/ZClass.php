<?php

// Modul: lms/attendance
// Klasa: ZClass
// Opis: jedan čas

// Z u imenu znači "Zamger" i dodato je jer je class rezervisana riječ u PHPu
// (i mnogim drugim jezicima)


require_once(Config::$backend_path."core/DB.php");
require_once(Config::$backend_path."lms/attendance/Group.php");

class ZClass {
	public $id;
	public $datetime, $teacherId, $groupId, $scoringElementId;
	public $group;
	
	public static function fromId($id) {
		// TODO kombinovati datum i vrijeme u bazi u jedan timestamp kao što je na svim drugim mjestima
		$q10 = DB::query("select UNIX_TIMESTAMP(c.datum+c.vrijeme), c.nastavnik, c.labgrupa, c.komponenta, l.naziv, l.predmet, l.akademska_godina from cas as c, labgrupa as l where c.id=$id and c.labgrupa=l.id");
		if (mysql_num_rows($q10)<1) {
			throw new Exception("nepostojeci cas");
		}
		$c = new ZClass;
		$c->id = $id;
		$c->datetime = mysql_result($q10,0,1);
		$c->teacherId = mysql_result($q10,0,2);
		$c->groupId = mysql_result($q10,0,3);
		$c->scoringElementId = mysql_result($q10,0,4);
		
		// U pravilu je potrebna i grupa
		$c->group = new Group;
		$c->group->id = $c->groupId;
		$c->group->name = mysql_result($q10,0,5);
		$c->group->courseUnitId = mysql_result($q10,0,6);
		$c->group->academicYearId = mysql_result($q10,0,7);
		
		return $c;
	}

	public static function fromGroupAndScoringElement($groupId, $scoringElementId) {
		$q10 = myquery("select id, UNIX_TIMESTAMP(datum+vrijeme), nastavnik from cas where labgrupa=$groupId and komponenta=$scoringElementId order by vrijeme");
		$classes = array();
		while ($r10 = mysql_fetch_row($q10)) {
			$c = new ZClass;
			$c->id = $r10[0];
			$c->datetime = $r10[1];
			$c->teacherId = $r10[2];
			$c->groupId = $groupId;
			$c->scoringElementId = $scoringElementId;
			$c->group = 0;

			array_push($classes, $c);
		}
		return $classes;
	}
}

?>
