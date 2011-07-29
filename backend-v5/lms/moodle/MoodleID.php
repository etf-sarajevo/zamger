<?php

// Modul: lms/moodle
// Klasa: MoodleID
// Opis: dobijanje Moodle IDa predmeta iz Zamger IDa


require_once(Config::$backend_path."core/DB.php");

class MoodleID {
	public static function getMoodleId($courseUnitId, $academicYearId) {
		$q10 = DB::query("select moodle_id from moodle_predmet_id where predmet=$courseUnitId and akademska_godina=$academicYearId");
		if (mysql_num_rows($q10)<1) {
			throw new Exception("predmet nema moodle id");
		}
		
		return mysql_result($q10,0,0);
	}
}

?>
