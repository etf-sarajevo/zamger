<?php

// Modul: lms/moodle
// Klasa: MoodleItem
// Opis: generalne vijesti o novim resursima i obavještenjima na Moodlu


// Za sada podržavamo samo module 9 (labele) i 13 (resursi)

require_once(Config::$backend_path."lms/moodle/MoodleConfig.php");
require_once(Config::$backend_path."lms/moodle/MoodleDB.php");

class MoodleItem {
	public $id;
	public $type, $text, $url, $timeModified;

	public static function getLatestForCourse($moodleCourseId) {
		$twoWeeksAgo = time()-(14*24*60*60); // we use this because Moodle uses regular int to keep timestamps

		// List modules active for course
		$q61 = MoodleDB::query("select module, instance, visible, id, added from ".MoodleConfig::$db.".".MoodleConfig::$db_prefix."course_modules where course=$moodleCourseId");
		$items = array();
	
		while ($r61 = mysql_fetch_array($q61)) {
			// Modul 9 je zaduzen za cuvanje informacija o obavijesti koje se postavljaju u labelu na moodle stranici
			// Ako visible != 1 instanca je sakrivena i ne treba je prikazati u Zamgeru
			if ($r61[0] == 9 && $r61[2] == 1) {
				$q62 = MoodleDB::query("select name, timemodified from ".MoodleConfig::$db.".".MoodleConfig::$db_prefix."label where course=$moodleCourseId and id=$r61[1] and timemodified>$twoWeeksAgo order by timemodified desc");
				
				while ($r62 = mysql_fetch_array($q62)) {
					$i = new MoodleItem;
					$i->type = "label";
					$i->text = $r62[0];
					$i->url = "";
					$i->timeModified = ($r61[4]>$r62[1])?$r61[4]:$r62[1];
					array_push ($items, $i);
				}
			}
			
			// Modul 13 je zaduzen za cuvanje informacija o dodatom resursu na moodle stranici
			if ($r61[0] == 13 && $r61[2] == 1) {
				$q64 = MoodleDB::query("select name, timemodified, id from ".MoodleConfig::$db.".".MoodleConfig::$db_prefix."resource where course=$moodleCourseId and id=$r61[1] and timemodified>$twoWeeksAgo order by timemodified desc");
				
				while ($r64 = mysql_fetch_array($q64)) {
					$i = new MoodleItem;
					$i->type = "resource";
					$i->text = $r64[0];
					$i->url = MoodleConfig::$url."mod/resource/view.php?id=$r61[3]";
					$i->timeModified = ($r61[4]>$r64[1])?$r61[4]:$r64[1];
					array_push ($items, $i);
				}
			}
		}
		return $items;
	}

}

?>
