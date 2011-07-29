<?php

// Modul: sis/announcement
// Klasa: Announcement
// Opis: obavještenja


require_once(Config::$backend_path."core/DB.php");
require_once(Config::$backend_path."core/CourseUnit.php");
require_once(Config::$backend_path."core/AcademicYear.php");
require_once(Config::$backend_path."core/Enrollment.php");
require_once(Config::$backend_path."core/Portfolio.php");
// posjeduje implicitan dependency na common/pm/* pošto koriste istu tabelu (popraviti?)

class Announcement {
	public $id;
	public $userRange, $to, $fromId, $time, $ref, $shortText, $longerText;
	
	public static function fromId($id) {
		$q10 = DB::query("select opseg, primalac, posiljalac, UNIX_TIMESTAMP(vrijeme), ref, naslov, tekst from poruka where id=$id and tip=1");
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
		$a->shortText = mysql_result($q10,0,5);
		$a->longerText = mysql_result($q10,0,6);
		
		return $a;
	}
	
	// Gets no more than $limit announcements, ordered by time descending
	public static function getLatestForPerson($person, $limit, $isStudent = true) {
		$currentYear = AcademicYear::getCurrent();

		$currentEnrollment = 0;
		try {
			$currentEnrollment = Enrollment::getCurrentForStudent($person);
			$currentYearOfStudy = ($currentEnrollment->semester + 1) % 2;
		} catch(Exception $e) {
			// do nothing -- we'll check for 0 later
		}

		$q40 = DB::query("
SELECT id, opseg, primalac, posiljalac, UNIX_TIMESTAMP(vrijeme), ref, naslov, tekst
FROM poruka 
WHERE tip=1 and vrijeme > SUBDATE(NOW(), INTERVAL 1 YEAR)
ORDER BY vrijeme DESC");
		$announcements = array();
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
			if ($scope == 4 && ( $currentEnrollment == 0 || $to != $currentYearOfStudy ) )
				continue; // for students in the given year of study
				
			if ($scope == 5) { // for students enrolled in a course unit
				// We care only for current academic year
				// FIXME get start date from AcademicYear class
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
			
			$a = new Announcement;
			$a->id = $r40[0];
			$a->userRanme = $scope;
			$a->to = $toText;
			$a->fromId = $r40[3];
			$a->time = $msgTime;
			$a->ref = $r40[5];
			$a->shortText = $r40[6];
			$a->longerText = $r40[7];
			
			array_push ($announcements, $a);
			
			$count++;
			if ($count == $limit) break;
		}
		
		return $announcements;
	}
}

?>
