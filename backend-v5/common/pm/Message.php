<?php

// Modul: common/pm
// Klasa: Message
// Opis: privatne poruke


require_once(Config::$backend_path."core/DB.php");
require_once(Config::$backend_path."core/CourseUnit.php");
require_once(Config::$backend_path."core/AcademicYear.php");
require_once(Config::$backend_path."core/Enrollment.php");
require_once(Config::$backend_path."core/Portfolio.php");

class Message {
	public $id;
	public $userRange, $to, $fromId, $time, $ref, $subject, $text;
	
	public static function fromId($id) {
		$q10 = DB::query("select opseg, primalac, posiljalac, UNIX_TIMESTAMP(vrijeme), ref, naslov, tekst from poruka where id=$id and tip=2");
		if (mysql_num_rows($q10)<1) {
			throw new Exception("nepoznato obavjestenje");
		}
		$a = new Announcement;
		$a->id = $id;
		$a->userRange = mysql_result($q10,0,0);
		$a->to = mysql_result($q10,0,1);
		$a->fromId = mysql_result($q10,0,2);
		$a->time = mysql_result($q10,0,3);
		$a->ref = mysql_result($q10,0,4);
		$a->subject = mysql_result($q10,0,5);
		$a->text = mysql_result($q10,0,6);
		
		return $a;
	}
	
	// Gets no more than $limit announcements, ordered by time descending
	public static function getLatestForPerson($person, $limit, $isStudent = true) {
		$currentYear = AcademicYear::getCurrent();

		$currentEnrollment = 0;
		try {
			$currentEnrollment = Enrollment::getCurrentForStudent($person);
			$currentYear = ($currentEnrollment->semester + 1) % 2; // current year of study
		} catch(Exception $e) {
			// do nothing -- we'll check for 0 later
		}

		$q40 = DB::query("
SELECT id, opseg, primalac, posiljalac, UNIX_TIMESTAMP(vrijeme), ref, naslov, tekst
FROM poruka 
WHERE tip=2
ORDER BY vrijeme DESC");
		$messages = array();
		$count = 0;
		while ($r40 = mysql_fetch_row($q40)) {
			$scope = $r40[1];
			$to = $r40[2];
			$toText = "";
			$msgTime = $r40[4];
			
			// Skipping messages in wrong scope
			// scope == 0 -- all users
			if ($scope == 1 && !$isStudent) continue; // for students only
			if ($scope == 2 && $isStudent) continue; // for teachers only
			if ($scope == 3 && ( $currentEnrollment == 0 || $to != $currentEnrollment->programmeId ) )
				continue; // for students enrolled into given programme
			if ($scope == 4 && ( $currentEnrollment == 0 || $to != $currentYear ) )
				continue; // for students in the given year of study
				
			if ($scope == 5) { // for students enrolled in a course unit
				// TODO get AcademicYear from $msgTime
				
				// We care only for current academic year
				$yearStart = mktime(0,0,0,9,1,intval($currentYear->name)); // 1. september - name usually starts with year integer value
				if  ($msgTime<$yearStart) continue;
				
				// Is student enrolled in courseunit in current year?
				try {
					Portfolio::fromCourseUnit ($person, $to /* course unit id */, $currentYear->id);
				} catch(Exception $e) {
					// student not enrolled this year
					continue;
				}
				
				// Replace $to with course unit name
				// OPTIMIZE: merge this query with above?
				$cu = CourseUnit::fromId($to);
				$toText = $cu->name;
			}
			
			if ($scope == 6) { // for students members of group
				// If this scope is set, it's assummed that lms/attendance module is installed
				require_once(Config::$backend_path."lms/attendance/Group.php");
				
				try {
					$group = Group::fromId($to);
				} catch (Exception $e) {
					// not a group, bad message
					continue;  // just skip it to avoid unhandled exception
				}
				if (!($group->isMember($person))) continue;
				// OPTIMIZE: merge these two queries into one?
				
				// Replace $to with class name
				$toText = $group->name;
			}
			
			if ($scope == 7 && $to != $person)
				continue; // personal message

			// FIXME Skip making toText for performance reasons 
			if ($toText == "") $toText = "Administrator";
			
			$m = new Message;
			$m->id = $r40[0];
			$m->userRanme = $scope;
			$m->to = $toText;
			$m->fromId = $r40[3];
			$m->time = $msgTime;
			$m->ref = $r40[5];
			$m->subject = $r40[6];
			$m->text = $r40[7];
			
			array_push ($messages, $m);
			
			$count++;
			if ($count == $limit) break;
		}
		
		return $messages;
	}
}

?>
