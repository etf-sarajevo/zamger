<?php

// Modul: core
// Klasa: Logging
// Opis: Rad sa logom


require_once(Config::$backend_path."core/DB.php");

class Logging {
	
	public static function log($event,$level) {
		global $userid;

		// Brisemo gluposti iz eventa
		if (($k=strpos($event,"sta="))>0) $event=substr($event,$k+4);
		if (strstr($event,"MOODLEID_=")) $event=preg_replace("/MOODLEID_=([^&]*)/","",$event);
		$event = str_replace("&"," ",$event);
		// sakrij sifru!
		$event=preg_replace("/pass=([^&]*)/","",$event);
		// brisemo tekstove poruka i sl.
		$event=preg_replace("/tekst=([^&]*)/","",$event);
		// brisemo suvisan tekst koji ubacuje mysql
		$event=str_replace("You have an error in your SQL syntax;","",$event);
		$event=str_replace("check the manual that corresponds to your MySQL server version for the right syntax to use","",$event);

		if (intval($userid)==0) $userid=0;

		DB::query("insert into log set dogadjaj='".DB::my_escape($event)."', userid=$userid, nivo=$level");
	}

}

?>