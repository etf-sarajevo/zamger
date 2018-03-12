<?php

// Modul: core
// Klasa: Message
// Opis: privatne poruke
// TODO: zamijeniti univerzalnim messaging sistemom


require_once(Config::$backend_path."core/AcademicYear.php");
require_once(Config::$backend_path."core/CourseOffering.php");
require_once(Config::$backend_path."core/Enrollment.php");
require_once(Config::$backend_path."core/Portfolio.php");
require_once(Config::$backend_path."core/Portfolio.php");

require_once(Config::$backend_path."lms/attendance/Group.php");

class Message {
	public $id;
	public $type, $range, $receiver, $sender, $time, $ref, $subject, $text, $unread;
	
	public static function fromId($id) {
		$msg = DB::query_assoc("SELECT id id, tip type, opseg range, primalac receiver, posiljalac sender, vrijeme time, ref, naslov subject, tekst text, procitana unread FROM poruka WHERE id=$id");
		if (!$msg) throw new Exception("Unknown message", "404");
		$msg = Util::array_to_class($msg, "Message");
		if ($msg->unread == 1) $msg->unread=false; else $msg->unread=true;  // inverted logic...
		$msg->sender = new UnresolvedClass("Person", $msg->sender, $msg->sender);
		if ($msg->range == 7)
			$msg->receiver = new UnresolvedClass("Person", $msg->receiver, $msg->receiver);
		
		// Does this user have access rights to this message?
		if ($msg->range == 1 && !in_array("student", Session::$privileges))
			throw new Exception("Permission denied", "403");
		if ($msg->range == 2 && !in_array("nastavnik", Session::$privileges))
			throw new Exception("Permission denied", "403");
		if ($msg->range == 4) {
			$cur_ay = AcademicYear::getCurrent();
			if ($msg->receiver != $cur_ay->id)
				throw new Exception("Permission denied", "403");
		}
		if ($msg->range == 7 && $msg->receiver != Session::$userid && $msg->sender->id != Session::$userid)
			throw new Exception("Permission denied", "403");
		
		if ($msg->range == 3 || $msg->range == 8) {
			$enr = Enrollment::getCurrentForStudent(Session::$userid);
			if ($msg->range == 3 && $msg->receiver != $enr->Programme->id)
				throw new Exception("Permission denied", "403");
			
			// Range 8 is a hack really...
			$study_year = intval(($enr->semester + 1) / 2);
			$rcv_code = $enr->Programme->id * 10 + $study_year;
			if ($msg->range == 8 && $msg->receiver != $rcv_code) {
				// Secondary code 
				$enr->Programme->resolve();
				$enr->Programme->ProgrammeType->resolve();
				$rcv_code = - $enr->Programme->ProgrammeType->cycle - $study_year;
				if ($msg->receiver != $rcv_code)
					throw new Exception("Permission denied", "403");
			}
		}
		
		if ($msg->range == 5) {
			$pfs = Portfolio::getAllForStudent(Session::$userid);
			$found = false;
			foreach($pfs as $pf) {
				if ($pf->courseOffering->courseUnitId == $msg->receiver)
					$found = true;
			}
			if (!$found)
				throw new Exception("Permission denied", "403");
		}
		if ($msg->range == 6) {
			// Use a fake class from id?
			$grp = Group::fromId($msg->receiver, false, false);
			if (!$grp->isMember(Session::$userid))
				throw new Exception("Permission denied", "403");
		}
		
		if ($msg->unread) {
			DB::query("UPDATE poruka SET procitana=1 WHERE id=$id");
		}
		
		return $msg;
	}
	
	public static function count() {
		$result['count'] = DB::get("SELECT COUNT(*) FROM poruka WHERE primalac=".Session::$userid." AND tip=2 and opseg=7");
		return $result;
	}

	// Inbox of latest messages for user
	public static function latest($count = 0, $start = 0) {
		$sql = "";
		if ($count > 0) {
			if ($start > 0) $sql .= "LIMIT $start,$count";
			else $sql = "LIMIT $count";
		}
		$msgs = DB::query_table("SELECT id id, tip type, opseg range, primalac receiver, posiljalac sender, vrijeme time, ref, naslov subject, tekst text, procitana unread FROM poruka WHERE primalac=".Session::$userid." AND tip=2 and opseg=7 ORDER BY id DESC $sql");
		foreach ($msgs as &$msg) {
			$msg = Util::array_to_class($msg, "Message");
			if ($msg->unread == 1) $msg->unread=false; else $msg->unread=true;  // inverted logic...
			$msg->sender = new UnresolvedClass("Person", $msg->sender, $msg->sender);
		}
		return $msgs;
	}

	// Inbox of latest messages for user
	public static function outbox($count = 0, $start = 0) {
		$sql = "";
		if ($count > 0) {
			if ($start > 0) $sql .= "LIMIT $start,$count";
			else $sql = "LIMIT $count";
		}
		$msgs = DB::query_table("SELECT id id, tip type, opseg range, primalac receiver, posiljalac sender, vrijeme time, ref, naslov subject, tekst text, procitana unread FROM poruka WHERE posiljalac=".Session::$userid." AND tip=2 and opseg=7 ORDER BY id DESC $sql");
		foreach ($msgs as &$msg) {
			$msg = Util::array_to_class($msg, "Message");
			if ($msg->unread == 1) $msg->unread=false; else $msg->unread=true;  // inverted logic...
			$msg->sender = new UnresolvedClass("Person", $msg->sender, $msg->sender);
			$msg->receiver = new UnresolvedClass("Person", $msg->receiver, $msg->receiver);
		}
		return $msgs;
	}
	
	// Inbox of unread messages for user
	public static function unread() {
		$msgs = DB::query_table("SELECT id id, tip type, opseg range, primalac receiver, posiljalac sender, vrijeme time, ref, naslov subject, tekst text, procitana unread FROM poruka WHERE primalac=".Session::$userid." AND tip=2 and opseg=7 AND procitana=0");
		foreach ($msgs as &$msg) {
			$msg = Util::array_to_class($msg, "Message");
			if ($msg->unread == 1) $msg->unread=false; else $msg->unread=true;  // inverted logic...
			$msg->sender = new UnresolvedClass("Person", $msg->sender, $msg->sender);
		}
		return $msgs;
	}

}

?>
