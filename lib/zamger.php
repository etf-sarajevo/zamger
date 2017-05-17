<?

// LIB/ZAMGER - core funkcije potrebne za rad Zamgera


// Logging
function zamgerlog($event,$nivo) {
	global $userid, $login, $conf_files_path;

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
	$userid = intval($userid);
	if ($userid == 0) 
		$userdata = "(0)"; 
	else
		$userdata = trim($login)." ($userid)";
		
	if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && $_SERVER['HTTP_X_FORWARDED_FOR'])
		$ip_adresa = db_escape($_SERVER['HTTP_X_FORWARDED_FOR']); 
	else
		$ip_adresa = db_escape($_SERVER['REMOTE_ADDR']);
	
	$nivostr_ar = array( "", "---", "CCC", "EEE", "AAA");
	$event = str_replace("\r", "", $event);
	$event = str_replace("\n", "", $event);
	$logline = "[" . $nivostr_ar[$nivo]. "] $ip_adresa - $userdata - [".date("Y-m-d H:i:s")."] \"$event\"\n";

	$godina = date("Y");
	$mjesec = date("m");
	$path = $conf_files_path . "/log/$godina";
	if (!file_exists($path)) mkdir($path, 0777, true);
	$logfile = "$path/$godina-$mjesec.log";
	
	file_put_contents($logfile, $logline, FILE_APPEND);
}


// Bilježenje poruke u log2 je nešto složenije
function zamgerlog2($tekst, $objekat1 = 0, $objekat2 = 0, $objekat3 = 0, $blob = "") {
	global $userid, $sta;

	$tekst = db_escape($tekst);
	$blob = db_escape($blob);
	if ($sta=="logout") $sta="";

	if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && $_SERVER['HTTP_X_FORWARDED_FOR'])
		$ip_adresa = db_escape($_SERVER['HTTP_X_FORWARDED_FOR']); 
	else
		$ip_adresa = db_escape($_SERVER['REMOTE_ADDR']);

	// Parametri objekat* moraju biti tipa int, pratimo sve drugačije pozive kako bismo ih mogli popraviti
	if ($objekat1 !== intval($objekat1) || $objekat2 !== intval($objekat2) || $objekat3 !== intval($objekat3)) {
		$q5 = db_query("INSERT INTO log2 SELECT 0,NOW(), ".intval($userid).", m.id, d.id, 0, 0, 0, '$ip_adresa' FROM log2_modul AS m, log2_dogadjaj AS d WHERE m.naziv='$sta' AND d.opis='poziv zamgerlog2 funkcije nije ispravan'");
		// Dodajemo blob
		$id = db_insert_id(); // Zašto se dešava da $id bude nula???
		$tekst_bloba = "";
		if ($objekat1 !== intval($objekat1)) $tekst_bloba .= "objekat1: $objekat1 ";
		if ($objekat2 !== intval($objekat2)) $tekst_bloba .= "objekat2: $objekat2 ";
		if ($objekat3 !== intval($objekat3)) $tekst_bloba .= "objekat3: $objekat3 ";

		$q7 = db_query("INSERT INTO log2_blob SET log2=$id, tekst='$tekst_bloba'");
		$objekat1 = intval($objekat1); $objekat2 = intval($objekat2); $objekat3 = intval($objekat3);
	}
	
	// $userid izgleda nekada može biti i prazan string?
	$q5 = db_query("INSERT INTO log2 SELECT 0,NOW(), ".intval($userid).", m.id, d.id, $objekat1, $objekat2, $objekat3, '$ip_adresa' FROM log2_modul AS m, log2_dogadjaj AS d WHERE m.naziv='$sta' AND d.opis='$tekst'");
	if (db_affected_rows() == 0) {
		// Nije ništa ubačeno, vjerovatno fale polja u tabelama
		$ubaceno = db_get("SELECT COUNT(*) FROM log2_modul WHERE naziv='$sta'");
		if ($ubaceno == 0)
			// U ovim slučajevima će se pozvati zamgerlog2 sa invalidnim modulom
			if ($tekst == "login" || $tekst == "sesija istekla" || $tekst == "nepoznat korisnik")
				$sta == "";
			else
				$q20 = db_query("INSERT INTO log2_modul SET naziv='$sta'");

		$ubaceno = db_get("SELECT COUNT(*) FROM log2_dogadjaj WHERE opis='$tekst'");
		if ($ubaceno == 0)
			// Neka admin manuelno u bazi definiše ako je događaj različitog nivoa od 2
			$q40 = db_query("INSERT INTO log2_dogadjaj SET opis='$tekst', nivo=2"); 

		$q50 = db_query("INSERT INTO log2 SELECT 0,NOW(), ".intval($userid).", m.id, d.id, $objekat1, $objekat2, $objekat3, '$ip_adresa' FROM log2_modul AS m, log2_dogadjaj AS d WHERE m.naziv='$sta' AND d.opis='$tekst'");
		// Ako sada nije uspjelo ubacivanje, nije nas briga :)
	}

	if ($blob !== "") {
		// Dodajemo blob
		$id = db_insert_id();
		$q60 = db_query("INSERT INTO log2_blob SET log2=$id, tekst='$blob'");
	}
}


// Ova funkcija definiše pravila za kreiranje UIDa za LDAP.
// Ispod je dato pravilo: prvo slovo imena + prvo slovo prezimena + broj indexa
function gen_ldap_uid($userid) {
	$q10 = db_query("select ime, prezime, brindexa from osoba where id=$userid");
	$ime = db_result($q10,0,0);
	$prezime = db_result($q10,0,1);
	$brindexa = db_result($q10,0,2);

	// Pretvorba naših slova Unicode -> ASCII (proširiti?)
	$debosn = array( 'Č'=>'c', 'č'=>'c', 'Ć'=>'c', 'ć'=>'c', 'Đ'=>'d', 'đ'=>'d', 'Š'=>'s', 'š'=>'s', 'Ž'=>'z', 'ž'=>'z');

	$sime = strtolower(substr($ime,0,1));
	if ($debosn[substr($ime,0,2)]) $sime=$debosn[substr($ime,0,2)];
	$sprezime = strtolower(substr($prezime,0,1));
	if ($debosn[substr($prezime,0,2)]) $sprezime=$debosn[substr($prezime,0,2)];

	if (strstr($brindexa,"/")) $brindexa = substr($brindexa,strpos($brindexa,"/")+1);

	return $sime.$sprezime.$brindexa;
}


// Vraća puni naziv osobe sa svim titulama
function tituliraj($osoba, $sa_akademskim_zvanjem = true, $sa_naucnonastavnim_zvanjem = true, $prezime_prvo = false) {
	if (intval($osoba) == 0) return "";
	$q10 = db_query("select ime, prezime, naucni_stepen, strucni_stepen from osoba where id=$osoba");
	if (!($r10 = db_fetch_row($q10))) {
		return "";
	}
	if ($prezime_prvo)
		$ime = $r10[1]." ".$r10[0];
	else
		$ime = $r10[0]." ".$r10[1];

	if ($r10[2]) {
		$q20 = db_query("select titula from naucni_stepen where id=$r10[2]");
		if ($r20 = db_fetch_row($q20))
			if ($prezime_prvo)
				$ime = $r10[1]." ".$r20[0]." ".$r10[0];
			else
				$ime = $r20[0]." ".$ime;
	}
	
	if ($sa_akademskim_zvanjem) {
		$q30 = db_query("select titula from strucni_stepen where id=$r10[3]");
		if ($r30 = db_fetch_row($q30))
			$ime = $ime.", ".$r30[0];
	}
	
	if ($sa_naucnonastavnim_zvanjem) {
		$q40 = db_query("select z.titula from izbor as i, zvanje as z where i.osoba=$osoba and i.zvanje=z.id and (i.datum_isteka>=NOW() or i.datum_isteka='0000-00-00')");
		if ($r40 = db_fetch_row($q40))
			$ime = $r40[0]." ".$ime;
	}

	return $ime;
}


// Generiše cachiranu verziju izvještaja izvjestaj/predmet
// Prije poziva treba u superglobalni niz $_REQUEST napuniti eventualne parametre izvještaja

function zamger_file_callback($buffer) {
	global $zamger_filecb_sadrzaj_buffera;
	$zamger_filecb_sadrzaj_buffera = $buffer;
}
function generisi_izvjestaj_predmet($predmet, $ag, $params = array()) {
	global $zamger_filecb_sadrzaj_buffera, $conf_files_path;

	// Punimo parametre u superglobalni niz $_REQUEST kako bi se proslijedili izvještaju
	foreach($params as $key => $value)
		$_REQUEST[$key] = $value;
	$_REQUEST['predmet'] = $predmet;
	$_REQUEST['ag'] = $ag;
	
	ob_start('zamger_file_callback');
	include("izvjestaj/predmet.php");
	eval("izvjestaj_predmet();");
	ob_end_clean();
	
	if (!file_exists("$conf_files_path/cache/izvjestaj_predmet/$predmet-$ag")) {
		mkdir ("$conf_files_path/cache/izvjestaj_predmet/$predmet-$ag",0755, true);
	}
	$filename = $conf_files_path."/cache/izvjestaj_predmet/$predmet-$ag/$predmet-$ag-".date("dmY").".html";
	file_put_contents($filename, $zamger_filecb_sadrzaj_buffera);
}


// Vraća vrijednost request parametra ili false
// Mada ove funkcije bi spadale u lib/utility, toliko često se koriste u Zamgeru da sam ih stavio ovdje
function param($name) {
	if (isset($_REQUEST[$name])) return $_REQUEST[$name];
	return false;
}

// Vraća integer vrijednost request parametra ili nulu
function int_param($name) {
	if (isset($_REQUEST[$name])) return intval($_REQUEST[$name]);
	return 0;
}



// ----------------------------
// GENERATORI FORMI
// ----------------------------

// Mada bi sljedeće funkcije pripadale u lib/formgen, toliko često se koriste u Zamgeru 
// (iz sigurnosnih razloga) da su stavljene u lib/zamger

// genform - pravi zaglavlje forme sa hidden poljima
function genform($method="POST", $name="") {
	global $login;

	if ($method != "GET" && $method != "POST") $method="POST";
	$result = '<form name="'.$name.'" action="'.$_SERVER['PHP_SELF'].'" method="'.$method.'">'."\n";
	foreach ($_REQUEST as $key=>$value) {
		if ($key=="pass" && $method=="GET") continue; // Ne pokazuj sifru u URLu!
		if ($key=="PHPSESSID") continue; // Ne pokazuj session id u URLu
		if ($key=="loginforma") continue; // Izbjegavamo logout
		$key = htmlspecialchars($key);
		if (substr($key,0,4) != "_lv_") {
			if (is_array($value)) {
				foreach ($value as $val) {
					$val = htmlspecialchars($val);
					$result .= '<input type="hidden" name="'.$key.'[]" value="'.$val.'">'."\n";
				}
			} else {
				$value = htmlspecialchars($value);
				$result .= '<input type="hidden" name="'.$key.'" value="'.$value.'">'."\n";
			}
		}
	}

	//   CSRF protection
	//   The generated token is a SHA1 sum of session ID, time()/1000 and userid (in the
	// highly unlikely case that two users get the same SID in a short timespan). The
	// second function checks this token and the second token which uses time()/1000+1.
	// This leaves a 1000-2000 second (cca. 16-33 minutes) window during which an 
	// attacker could potentially discover a users SID and then craft an attack targeting
	// that specific user.

	$result .= '<input type="hidden" name="_lv_csrf_protection_token1" value="'.sha1(session_id().(intval(time()/1000)).$login).'"><input type="hidden" name="_lv_csrf_protection_token2" value="'.sha1(session_id().(intval(time()/1000)+1).$login).'">';

	return $result;
}


// Funkcija koja provjerava da li je CSRF token (kojeg insertuje genform) validan
function check_csrf_token() {
	global $login;
	$token = sha1(session_id().intval(time()/1000).$login);
	if ($_POST['_lv_csrf_protection_token1']==$token || $_POST['_lv_csrf_protection_token2']==$token)
		return true;
	return false;
}


// genuri - pravi link na isti dokument sa ukodiranim varijablama
function genuri() {
	$result = $_SERVER['PHP_SELF']."?";
	foreach ($_REQUEST as $key=>$value) {
		// Prevent revealing session
		if (substr($key,0,4) == "_lv_" || $key == "PHPSESSID" || $key == "pass") continue;
		if ($key=="loginforma") continue; // Izbjegavamo logout
		if (is_array($value)) {
			foreach ($value as $val) 
				$result .= urlencode($key).'[]='.urlencode($val).'&amp;';
		} else
			$result .= urlencode($key).'='.urlencode($value).'&amp;';
	}
	if (substr($result,strlen($result)-5) == "&amp;") 
		$result = substr($result,0,strlen($result)-5); // drop last &
	return $result;
}


?>
