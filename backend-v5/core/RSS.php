<?php

// Modul: core
// Klasa: RSS
// Opis: stvari vezane za RSS - za sada samo korisnikov ID


require_once(Config::$backend_path."core/DB.php");

class RSS {
	public $id, $personId;

	public static function fromId($id) {
		$q1 = DB::query("select auth from rss where id='$id'");
		if (mysql_num_rows($q1) < 1) {
			throw new Exception("Unknown RSS ID");
		}
		$r = new RSS;
		$r->id = $id;
		$r->personId = mysql_result($q1,0,0);
		return $r;
	}

	public static function fromPersonId($personId) {
		// person ID not checked for performance reasons
		$r = new RSS;
		$r->personId = $personId;
	
		$q200 = DB::query("select id from rss where auth=$personId");
		if (mysql_num_rows($q200) > 0) {
			$r->id = mysql_result($q200,0,0);
			return $r;
		}
			
		// Creating a new ID
		srand(time());
		do {
			$rssid="";
			for ($i=0; $i<10; $i++) {
				$slovo = rand()%62;
				if ($slovo<10) $sslovo=$slovo;
				else if ($slovo<36) $sslovo=chr(ord('a')+$slovo-10);
				else $sslovo=chr(ord('A')+$slovo-36);
				$rssid .= $sslovo;
			}
			$q210 = DB::query("select count(*) from rss where id='$rssid'");
		} while (mysql_result($q210,0,0)>0);
		$q220 = DB::query("insert into rss set id='$rssid', auth=$personId");
		
		$r->id = $rssid;
		return $r;
	}
	
	
	public function updateTimestamp() {
		$q2 = DB::query("update rss set access=NOW() where id='".$this->id."'");
	}
}

?>
