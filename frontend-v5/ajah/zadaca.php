<?


// AJAH/ZADACA - skripta za rad sa zadaćom

require("../lib/libvedran.php");
require("../lib/zamger.php");
require("../lib/config.php");
require("../lib/manip.php"); // zbog update_komponente

dbconnect2($conf_dbhost,$conf_dbuser,$conf_dbpass,$conf_dbdb);

// Provjera sesije
check_cookie();
if ($userid==0) {
	echo "FAIL|login nije uspio";
	dbdisconnect();
	return;
}

$result = odredi_privilegije_korisnika();
if ($result === false) {
	dbdisconnect();
	return;
}

if ($_REQUEST['akcija'] == "dajZadacu") {
	$id = intval($_REQUEST['id']);
	if (!$user_siteadmin && !pravo_pristupa($id,0)) {
		dbdisconnect();
		return;
	}
	echo json_iz_upita("select * from zadaca where id=$id");
}

if ($_REQUEST['akcija'] == "dajJezik") {
	$id = intval($_REQUEST['id']);
	echo json_iz_upita("select * from programskijezik where id=$id");
}

if ($_REQUEST['akcija'] == "dajZadatakIzFajla") {
	$zadaca = intval($_REQUEST['zadaca']);
	$student = intval($_REQUEST['student']);
	$filename = my_escape($_REQUEST['filename']);
	if (!$user_siteadmin && !prava_pristupa($zadaca,$student)) {
		dbdisconnect();
		return;
	}
	echo json_iz_upita("select redni_broj from zadatak where zadaca=$zadaca and student=$student and filename='$filename' limit 1");
}

if ($_REQUEST['akcija'] == "dajAutotestZamjene") {
	$zadaca = intval($_REQUEST['zadaca']);
	$zadatak = intval($_REQUEST['zadatak']);
	if (!$user_siteadmin && !prava_pristupa($zadaca,0)) {
		dbdisconnect();
		return;
	}
	echo json_iz_upita("select tip, specifikacija, zamijeni from autotest_replace where zadaca=$zadaca and zadatak=$zadatak");
}

if ($_REQUEST['akcija'] == "dajAutotestCaseove") {
	$zadaca = intval($_REQUEST['zadaca']);
	$zadatak = intval($_REQUEST['zadatak']);
	if (!$user_siteadmin && !prava_pristupa($zadaca,0)) {
		dbdisconnect();
		return;
	}
	echo json_iz_upita("select kod, rezultat, alt_rezultat, fuzzy, global_scope from autotest where zadaca=$zadaca and zadatak=$zadatak");
}


if ($_REQUEST['akcija'] == "oznaciPaketKaoObradjen") {
	$paket = intval($_REQUEST['id']);
	if (!$user_siteadmin) {
		echo "FAIL|niste admin";
		dbdisconnect();
		return;
	}
	$q80 = myquery("update workpackage set zavrsen=1 where id=$paket");
	echo serialize(array("success" => "true", "data" => true));
}

if ($_REQUEST['akcija'] == "postaviStatusZadace") {
	$zadaca = intval($_REQUEST['zadaca']);
	$zadatak = intval($_REQUEST['zadatak']);
	$student = intval($_REQUEST['student']);
	if (!$user_siteadmin && !prava_pristupa($zadaca,$student)) {
		dbdisconnect();
		return;
	}
	
	$komentar = my_escape($_REQUEST['komentar']);
	$izvjestaj_skripte = my_escape($_REQUEST['izvjestaj_skripte']);
	$status = intval($_REQUEST['status']);
	$bodova = floatval(str_replace(",",".",$_REQUEST['bodova']));
	$vrijeme = intval($_REQUEST['vrijeme']);
	
	// Filename
	$q90 = myquery("select filename from zadatak where zadaca=$zadaca and redni_broj=$zadatak and student=$student  order by id desc limit 1");
	$filename = mysql_result($q90,0,0);

	if ($vrijeme==0)
		$q100 = myquery("insert into zadatak set zadaca=$zadaca, redni_broj=$zadatak, student=$student, status=$status, bodova=$bodova, vrijeme=now(), komentar='$komentar', izvjestaj_skripte='$izvjestaj_skripte', filename='$filename', userid=$userid");
	else
		$q100 = myquery("insert into zadatak set zadaca=$zadaca, redni_broj=$zadatak, student=$student, status=$status, bodova=$bodova, vrijeme=FROM_UNIXTIME($vrijeme), komentar='$komentar', izvjestaj_skripte='$izvjestaj_skripte', filename='$filename', userid=$userid");

	// Odredjujemo ponudu kursa (za update komponente)
	$q110 = myquery("select pk.id from student_predmet as sp, ponudakursa as pk, zadaca as z where sp.student=$student and sp.predmet=pk.id and pk.predmet=z.predmet and pk.akademska_godina=z.akademska_godina and z.id=$zadaca");

	update_komponente($student, mysql_result($q110,0,0), $komponenta);

	zamgerlog("izmjena zadace (student u$student zadaca z$zadaca zadatak $zadatak)",2);
	echo serialize(array("success" => "true", "data" => true));
}

dbdisconnect();


function json_iz_upita($upit) {
	$q10 = myquery($upit);
	$rows = mysql_num_rows($q10);
	if ($rows < 1) {
		$result = array("success" => "true", "rows" => 0, "data" => "empty");
	} else if ($rows == 1) {
		$dbrow = mysql_fetch_array($q10, MYSQL_ASSOC);
		$result = array("success" => "true", "rows" => 1, "data" => $dbrow);
	} else {
		$dbrows = array();
		while ($dbrow = mysql_fetch_row($q10)) {
			array_push($dbrows, $dbrow);
		}
		$result = array("success" => "true", "rows" => $rows, "data" => $dbrows);
	}
//	return json_encode($result);
	return serialize($result);
}

function odredi_privilegije_korisnika() {
	global $userid, $user_student, $user_nastavnik, $user_studentska, $user_siteadmin;
	$user_student=$user_nastavnik=$user_studentska=$user_siteadmin=false;
	if ($userid>0) {
		$q10 = myquery("select privilegija from privilegije where osoba=$userid");
		while ($r10=mysql_fetch_row($q10)) {
			if ($r10[0]=="student") $user_student=true; 
			if ($r10[0]=="nastavnik") $user_nastavnik=true;
			if ($r10[0]=="studentska") $user_studentska=true;
			if ($r10[0]=="siteadmin") $user_siteadmin=true;
			//if ($r10[0]=="prijemni")  -- ovi nemaju pristup zamgeru
			// ovdje dodati ostale vrste korisnika koje imaju pristup
		}

		// Korisnik nije ništa!?
		if (!$user_student && !$user_nastavnik && !$user_studentska && !$user_siteadmin) {
			echo "FAIL|Vaše korisničko ime je ispravno, ali nemate nikakve privilegije na sistemu! Kontaktirajte administratora.";
			return false;
		}
		return true;
	}
}

function pravo_pristupa($zadaca, $student=0) {
	global $userid;
	echo "blah";
	
	// Da li korisnik ima pravo ući u grupu?
	$q40 = myquery("select np.nivo_pristupa from nastavnik_predmet as np, zadaca as z where np.nastavnik=$userid and np.predmet=z.predmet and np.akademska_godina=z.akademska_godina and z.id=$zadaca");
	if (mysql_num_rows($q40)<1) {
		echo "FAIL|nastavnik nije na predmetu";
		return false;
	}
	
	$privilegija = mysql_result($q40,0,0);
	if ($student==0 || $privilegija != "asistent") return true;
	
	$q45 = myquery("select l.id from student_labgrupa as sl, labgrupa as l where sl.student=$student and sl.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag and l.virtualna=0");
	$q50 = myquery("select o.labgrupa from ogranicenje as o, labgrupa as l where o.nastavnik=$userid and o.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag");
	if (mysql_num_rows($q45)<1) {
		if (mysql_num_rows($q50)<1) {
			echo "FAIL|imate ogranicenja a student nije u grupi";
			return false;
		}
		return true;
	}
	$labgrupa = mysql_result($q45,0,0);

	if (mysql_num_rows($q50)>0) {
		$nasao=0;
		while ($r50 = mysql_fetch_row($q50)) {
			if ($r50[0] == $labgrupa) { $nasao=1; break; }
		}
		if ($nasao == 0) {
			echo "FAIL|ogranicenje na labgrupu $labgrupa";
			return false;
		}
	}
	return true;
}

?>
