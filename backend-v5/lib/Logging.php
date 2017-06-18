<?php

// Modul: lib
// Klasa: Logging
// Opis: Rad sa logom


// Klasa je defacto singleton (u PHPu je besmisleno praviti singletone:
// https://stackoverflow.com/questions/4595964/is-there-a-use-case-for-singletons-with-database-access-in-php/4596323#4596323 )

require_once(Config::$backend_path."lib/DB.php");
require_once(Config::$backend_path."lib/Util.php");

abstract class LogLevel {
	const Access = 1;
	const Edit = 2;
	const Error = 3;
	const Audit = 4;
}

class Logging {
	
	public static function log($event,$level) {
		// Brisemo gluposti iz eventa
		if (($k=strpos($event,"sta="))>0) $event=substr($event,$k+4);
		if (strstr($event,"MOODLEID_=")) $event=preg_replace("/MOODLEID_=([^&]*)/","",$event);
		$event = str_replace("&amp;"," ",$event);
		$event = str_replace("&"," ",$event);
		// sakrij sifru!
		$event=preg_replace("/pass=([^&]*)/","",$event);
		// brisemo PHPSESSID
		$event=preg_replace("/PHPSESSID=([^&]*)/","",$event);
		// brisemo tekstove poruka i sl.
		$event=preg_replace("/tekst=([^&]*)/","",$event);
		// brisemo suvisan tekst koji ubacuje mysql
		$event=str_replace("You have an error in your SQL syntax;","",$event);
		$event=str_replace("check the manual that corresponds to your MySQL server version for the right syntax to use","",$event);

		// Username
		$userid = intval(Session::$userid);
		if ($userid == 0) 
			$userdata = "(0)"; 
		else
			$userdata = trim(Session::$username)." ($userid)";
			
		if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && $_SERVER['HTTP_X_FORWARDED_FOR'])
			$ip_adresa = DB::escape($_SERVER['HTTP_X_FORWARDED_FOR']); 
		else
			$ip_adresa = DB::escape($_SERVER['REMOTE_ADDR']);
	
		$nivostr_ar = array( "", "---", "CCC", "EEE", "AAA");
		$event = str_replace("\r", "", $event);
		$event = str_replace("\n", "", $event);
		$logline = "[" . $nivostr_ar[$level]. "] $ip_adresa - $userdata - [".date("Y-m-d H:i:s")."] \"$event\"\n";

		$godina = date("Y");
		$mjesec = date("m");
		$path = Config::$backend_file_path . "/log/$godina";
		if (!file_exists($path)) mkdir($path, 0777, true);
		$logfile = "$path/$godina-$mjesec.log";
		
		file_put_contents($logfile, $logline, FILE_APPEND);
	}
	
	public static function log2($tekst, $objekat1 = 0, $objekat2 = 0, $objekat3 = 0, $blob = "") {
		global $sta; // FIXME koristiti backtrace

		$tekst = DB::escape($tekst);
		$blob = DB::escape($blob);
		if ($sta=="logout") $sta="";

		$ip_adresa = DB::escape(Util::getip());
		
		$userid = intval(Session::$userid);

		// Parametri objekat* moraju biti tipa int, pratimo sve drugačije pozive kako bismo ih mogli popraviti
		if ($objekat1 !== intval($objekat1) || $objekat2 !== intval($objekat2) || $objekat3 !== intval($objekat3)) {
			$q5 = DB::query("INSERT INTO log2 SELECT 0,NOW(), $userid, m.id, d.id, 0, 0, 0, '$ip_adresa' FROM log2_modul AS m, log2_dogadjaj AS d WHERE m.naziv='$sta' AND d.opis='poziv zamgerlog2 funkcije nije ispravan'");
			// Dodajemo blob
			$id = DB::insert_id(); // Zašto se dešava da $id bude nula???
			$tekst_bloba = "";
			if ($objekat1 !== intval($objekat1)) $tekst_bloba .= "objekat1: $objekat1 ";
			if ($objekat2 !== intval($objekat2)) $tekst_bloba .= "objekat2: $objekat2 ";
			if ($objekat3 !== intval($objekat3)) $tekst_bloba .= "objekat3: $objekat3 ";

			$q7 = DB::query("INSERT INTO log2_blob SET log2=$id, tekst='$tekst_bloba'");
			$objekat1 = intval($objekat1); $objekat2 = intval($objekat2); $objekat3 = intval($objekat3);
		}
		
		// $userid izgleda nekada može biti i prazan string?
		$q5 = DB::query("INSERT INTO log2 SELECT 0,NOW(), $userid, m.id, d.id, $objekat1, $objekat2, $objekat3, '$ip_adresa' FROM log2_modul AS m, log2_dogadjaj AS d WHERE m.naziv='$sta' AND d.opis='$tekst'");
		if (DB::affected_rows() == 0) {
			// Nije ništa ubačeno, vjerovatno fale polja u tabelama
			$ubaceno = DB::get("SELECT COUNT(*) FROM log2_modul WHERE naziv='$sta'");
			if ($ubaceno == 0)
				// U ovim slučajevima će se pozvati zamgerlog2 sa invalidnim modulom
				if ($tekst == "login" || $tekst == "sesija istekla" || $tekst == "nepoznat korisnik")
					$sta == "";
				else
					$q20 = DB::query("INSERT INTO log2_modul SET naziv='$sta'");

			$ubaceno = DB::get("SELECT COUNT(*) FROM log2_dogadjaj WHERE opis='$tekst'");
			if ($ubaceno == 0)
				// Neka admin manuelno u bazi definiše ako je događaj različitog nivoa od 2
				$q40 = DB::query("INSERT INTO log2_dogadjaj SET opis='$tekst', nivo=2"); 

			$q50 = DB::query("INSERT INTO log2 SELECT 0,NOW(), $userid, m.id, d.id, $objekat1, $objekat2, $objekat3, '$ip_adresa' FROM log2_modul AS m, log2_dogadjaj AS d WHERE m.naziv='$sta' AND d.opis='$tekst'");
			// Ako sada nije uspjelo ubacivanje, nije nas briga :)
		}

		if ($blob !== "") {
			// Dodajemo blob
			$id = DB::insert_id();
			$q60 = DB::query("INSERT INTO log2_blob SET log2=$id, tekst='$blob'");
		}
	}

}

?>
