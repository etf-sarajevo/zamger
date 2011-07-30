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
	public $type, $scope, $toId, $fromId, $time, $ref, $subject, $text;

	private static $cachePerson, $cacheYear, $cacheEnrollment, $cacheYearOfStudy;
	
	public static function fromId($id) {
		$q10 = DB::query("select tip, opseg, primalac, posiljalac, UNIX_TIMESTAMP(vrijeme), ref, naslov, tekst from poruka where id=$id and tip=2");
		if (mysql_num_rows($q10)<1) {
			throw new Exception("nepoznato obavjestenje");
		}
		$a = new Message;
		$a->id = $id;
		$a->type = mysql_result($q10,0,0);
		$a->scope = mysql_result($q10,0,1);
		$a->toId = mysql_result($q10,0,2);
		$a->fromId = mysql_result($q10,0,3);
		$a->time = mysql_result($q10,0,4);
		$a->ref = mysql_result($q10,0,5);
		$a->subject = mysql_result($q10,0,6);
		$a->text = mysql_result($q10,0,7);
		
		return $a;
	}

	// Tests if this message is for specified person
	public function forPerson($person, $isStudent = true) {
		// Cache some variables to optimise getLatestForPerson
		if (Message::$cachePerson != $person) {
			Message::$cachePerson = $person;
			Message::$cacheYear = AcademicYear::getCurrent();
			Message::$cacheEnrollment = 0;

			try {
				Message::$cacheEnrollment = Enrollment::getCurrentForStudent($person);
				Message::$cacheYearOfStudy = (Message::$cacheEnrollment->semester + 1) % 2; // current year of study
			} catch(Exception $e) {
				// do nothing -- we'll check for 0 later
			}
		}

		switch ($this->scope) {
			case 0: // all users 
				return true;

			// FIXME šta ako je korisnik i student i nastavnik? možda bi bolje bilo da ova metoda to utvrdi
			case 1: // for students only
				return $isStudent;
			case 2: // for teachers only
				return !$isStudent;
			case 3: // for students enrolled in specified programme
				if (Message::$cacheEnrollment == 0) return false;
				return $this->toId == $cacheEnrollment->programmeId;
			case 4: // for students in the given year of study
				// FIXME ovo će naći npr. 2. godinu i bachelora i mastera
				// Treba ukinuti ovaj nivo i koristiti opseg godina+studij
				if (Message::$cacheEnrollment == 0) return false;
				return $this->toId == Message::$cacheYearOfStudy;

			case 5: // for students enrolled in the given course unit
				// FIXME get AcademicYear from $msgTime
				
				// We care only for current academic year
				$yearStart = mktime(0,0,0,9,1,intval(Message::$cacheYear->name)); // 1. september - name usually starts with year integer value
				if  ($msgTime < $yearStart) continue;
				
				// Is student enrolled in courseunit in current year?
				try {
					Portfolio::fromCourseUnit ($person, $this->toId /* course unit id */, Message::$cacheYear->id);
				} catch(Exception $e) {
					return false;
				}
				return true;

			case 6: // for students that are members of a group
				// If this scope is set, it's assummed that lms/attendance module is installed
				require_once(Config::$backend_path."lms/attendance/Group.php");
			
				try {
					$group = Group::fromId($this->toId);
				} catch (Exception $e) {
					// not a group, bad message
					return false;  // just skip it to avoid unhandled exception
				}
				if (!($group->isMember($person))) return false;
				// OPTIMIZE: merge above two queries into one?

				return true;

			case 7: // personal message
				return $this->toId == $person;

			default:
				return false; // unknown scope
		}
	}

	// Send constructed message
	public function send() {
		$this->type = 2; // only messages allowed
		$this->scope = 7; // only personal messages allowed
		$q10 = DB::query("insert into poruka set tip=2, opseg=7, posiljalac=".$this->fromId.", primalac=".$this->toId.", vrijeme=NOW(), ref=".$this->ref.", naslov='".$this->subject."', tekst='".$this->text."'");
		$this->id = mysql_insert_id();
		$q20 = DB::query("select UNIX_TIMESTAMP(vrijeme) from poruka where id=".$this->id);
		$this->time = mysql_result($q10,0,0);
	}

	// Gets no more than $limit messages starting from $startFrom, ordered by time descending
	public static function getLatestForPerson($person, $limit = 0, $isStudent = true, $startFrom = 0) {
		$q40 = DB::query("SELECT id, opseg, primalac, posiljalac, UNIX_TIMESTAMP(vrijeme), ref, naslov, tekst FROM poruka WHERE tip=2 ORDER BY vrijeme DESC");
		$messages = array();
		$count = 0;
		while ($r40 = mysql_fetch_row($q40)) {
			$m = new Message;
			$m->id = $r40[0];
			$m->type = 2;
			$m->scope = $r40[1];
			$m->toId = $r40[2];
			$m->fromId = $r40[3];
			$m->time = $r40[4];
			$m->ref = $r40[5];
			$m->subject = $r40[6];
			$m->text = $r40[7];

			if (!$m->forPerson($person)) continue;

			if ($count >= $startFrom) array_push ($messages, $m);
			
			// We must count messages manually because we don't know how many are for this person
			$count++;
			if ($limit > 0 && $count == $startFrom + $limit) break;
		}
		
		return $messages;
	}

	
	// Outbox is easier because we just check for sender in query
	public static function getOutboxForPerson($person, $limit = 0, $startFrom = 0) {
		if ($limit > 0) $sqladd = " limit $startFrom,$limit";
		else $sqladd = "";
		$q10 = DB::query("select id, opseg, primalac, UNIX_TIMESTAMP(vrijeme), ref, naslov, tekst from poruka where tip=2 and posiljalac=$person order by vrijeme desc $sqladd ");
		$messages = array();
		while ($r10 = mysql_fetch_row($q10)) {
			$m = new Message;
			$m->id = $r10[0];
			$m->type = 2;
			$m->userRange = $r10[1];
			$m->fromId = $person;
			$m->toId = $r10[2];
			$m->time = $r10[3];
			$m->ref = $r10[4];
			$m->subject = $r10[5];
			$m->text = $r10[6];

			array_push ($messages, $m);
		}
		return $messages;
	}

}

?>
