<?php

// Modul: lms/homework
// Klasa: Diff
// Opis: čuva promjene napravljene u zadaći


require_once(Config::$backend_path."core/DB.php");

class Diff {
	public static function add($assignmentId, $diff) {
		$q250 = myquery("insert into zadatakdiff set zadatak=$assignmentId, diff='$diff'");
	}
}

?>
