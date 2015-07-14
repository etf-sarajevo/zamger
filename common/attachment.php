<?

// COMMON/ATTACHMENT - download bilo čega


function common_attachment() {

global $userid,$conf_files_path,$user_student,$user_nastavnik,$user_siteadmin;


// Kakav fajl se downloaduje?
$tip = $_REQUEST['tip'];
if ($tip == "") $tip = "zadaca"; // privremeno


// PROVJERA PRIVILEGIJA I ODREĐIVANJE LOKACIJE FAJLA NA SERVERU

// Tip: zadaća

if ($tip == "zadaca") {
	// Poslani parametri
	$zadaca = intval($_REQUEST['zadaca']);
	$zadatak = intval($_REQUEST['zadatak']);
	$student = intval($_REQUEST['student']);

	$q5 = myquery("select predmet, akademska_godina from zadaca where id=$zadaca");
	if (mysql_num_rows($q5)<1) {
		zamgerlog("nepostojeca zadaca $zadaca",3);
		zamgerlog2("nepostojeca zadaca", $zadaca);
		niceerror("Nepostojeća zadaća");
		return;
	}
	$predmet = mysql_result($q5,0,0);
	$ag = mysql_result($q5,0,1);


	if ($student==0) { // student otvara vlastitu zadaću
		if ($user_student)
			$student=$userid;
		else {
			zamgerlog("pokusao otvoriti attachment bez ID studenta, a sam nije student",3);
			zamgerlog2("pokusao otvoriti attachment bez ID studenta, a sam nije student");
			niceerror("Čiju zadaću pokušavate otvoriti?");
			return;
		}
	
	} else { // student je odredjen kao parametar
		if (!$user_nastavnik && !$user_siteadmin) {
			zamgerlog("attachment: nije nastavnik (student u$student zadaca z$zadaca)",3);
			zamgerlog2("nije nastavnik");
			niceerror("Nemate pravo pregleda ove zadaće");
			return;
		}
		
		if (!$user_siteadmin) {
			$q10 = myquery("select count(*) from nastavnik_predmet where predmet=$predmet and akademska_godina=$ag and nastavnik=$userid");
			if (mysql_result($q10,0,0)<1) {
				zamgerlog("attachment: nije nastavnik na predmetu (student u$student zadaca z$zadaca)",3);
				zamgerlog2("nije nastavnik na predmetu za zadacu", $zadaca);
				niceerror("Nemate pravo pregleda ove zadaće");
				return;
			}
			
			// Provjera ograničenja
			$q20 = myquery("select o.labgrupa from ogranicenje as o, labgrupa as l where o.nastavnik=$userid and o.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag");
			if (mysql_num_rows($q20)>0) {
				// Ako ograničenja postoje, dozvoljavamo korisniku da otvori zadaće samo studenata u labgrupama kojima inače može pristupiti
				$nasao=0;
				while ($r20 = mysql_fetch_row($q20)) {
					$q25 = myquery("select count(*) from student_labgrupa where student=$student and labgrupa=$r20[0]");
					if (mysql_result($q25,0,0)>0) { $nasao=1; break; }
				}
				if ($nasao == 0) {
					zamgerlog("ogranicenje na predmet (student u$student predmet p$ponudakursa)",3);
					zamgerlog2("ogranicenje na predmet za zadacu", $zadaca);
					niceerror("Nemate pravo pregleda ove zadaće");
					return;
				}
			}
		}
	}


	// Da li neko pokušava da spoofa zadaću?
	
	$q30 = myquery("SELECT count(*) FROM student_predmet as sp, ponudakursa as pk WHERE sp.student=$student and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
	if (mysql_result($q30,0,0)<1) {
		zamgerlog("student nije upisan na predmet (student u$student zadaca z$zadaca)",3);
		zamgerlog2("student ne slusa predmet za zadacu", $zadaca);
		niceerror("Student nije upisan na predmet");
		return;
	}


	// Lokacija zadaće

	$lokacijazadaca="$conf_files_path/zadace/$predmet-$ag/$student/$zadaca/";
	
	$q40 = myquery("select filename from zadatak where zadaca=$zadaca and redni_broj=$zadatak and student=$student order by id desc limit 1");
	if (mysql_num_rows($q40) < 1) {
		zamgerlog("ne postoji attachment (zadaca $zadaca zadatak $zadatak student $student)",3);
		zamgerlog2("ne postoji attachment", intval($student), $zadaca, $zadatak);
		niceerror("Ne postoji attachment");
		return;
	}
	
	$filename = mysql_result($q40,0,0);
	$filepath = $lokacijazadaca.$filename;
}



// Tip: postavka zadaće

if ($tip == "postavka") {
	$zadaca=intval($_REQUEST['zadaca']);
	
	$q100 = myquery("select predmet, akademska_godina, postavka_zadace from zadaca where id=$zadaca");
	if (mysql_num_rows($q100)<1) {
		zamgerlog("nepostojeca zadaca $zadaca",3);
		zamgerlog2("nepostojeca zadaca", $zadaca);
		niceerror("Nepostojeća zadaća");
		return;
	}
	
	$predmet = mysql_result($q100,0,0);
	$ag = mysql_result($q100,0,1);
	$postavka_zadace = mysql_result($q100,0,2);

	if ($postavka_zadace == "") {
		niceerror("Postavka ne postoji");
		zamgerlog("postavka ne postoji z$zadaca", 3);
		zamgerlog2("postavka ne postoji", $zadaca);
		return;
	}


	$ok = false;

	if ($user_siteadmin) $ok = true;
	if ($user_nastavnik && !$ok) {
		$q110 = myquery("select count(*) from nastavnik_predmet where predmet=$predmet and akademska_godina=$ag and nastavnik=$userid");
		if (mysql_result($q110,0,0)>0) $ok = true;
	}
	if ($user_student && !$ok) {
		$q120 = myquery("SELECT count(*) FROM student_predmet as sp, ponudakursa as pk WHERE sp.student=$userid and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
		if (mysql_result($q120,0,0)>0) $ok = true;
	}

	if (!$ok) {
		zamgerlog("nema pravo pristupa postavci zadace (z$zadaca)",3);
		zamgerlog2("nema pravo pristupa postavci zadace", $zadaca);
		niceerror("Nemate pravo pristupa ovoj postavci");
		return;
	}


	$filename = $postavka_zadace;
	$filepath = "$conf_files_path/zadace/$predmet-$ag/postavke/$filename";
}


// Tip: projektni fajl

if ($tip == "projekat") {
	$projekat = intval($_REQUEST['projekat']);
	$id = intval($_REQUEST['id']); //file ID

	$q200 = myquery("select predmet, akademska_godina from projekat where id=$projekat");
	if (mysql_num_rows($q200)<1) {
		zamgerlog("nepostojeci projekat $projekat",3);
		zamgerlog2("nepostojeci projekat", $projekat);
		niceerror("Nepostojeći projekat");
		return;
	}
	$predmet = mysql_result($q200,0,0);
	$ag = mysql_result($q200,0,1);

	$ok = false;

	if ($user_siteadmin) $ok = true;
	if ($user_nastavnik && !$ok) {
		$q210 = myquery("select nivo_pristupa from nastavnik_predmet where predmet=$predmet and akademska_godina=$ag and nastavnik=$userid");
		if (mysql_num_rows($q210)>0 && mysql_result($q210,0,0)!="asistent") $ok = true;
	}
	if ($user_student && !$ok) {
		$q220 = myquery("SELECT count(*) FROM student_projekat WHERE student=$userid and projekat=$projekat");
		if (mysql_result($q220,0,0)>0) $ok = true;
	}

	if (!$ok) {
		zamgerlog("nema pravo pristupa projektu $projekat",3);
		zamgerlog2("nije na projektu", $projekat);
		niceerror("Nemate pravo pristupa ovom projektu.");
		return;
	}
	
	$q230 = myquery("select osoba, revizija, filename from projekat_file where id=$id");
	if (mysql_num_rows($q230)<1) {
		zamgerlog("nepostojeci file $id na projektu $projekat", 3);
		zamgerlog2("nepostojeci file na projektu", $projekat, $id);
		niceerror("Nepoznat ID $id");
		return;
	}
	
	$fileosoba = mysql_result($q230,0,0);
	$revizija = mysql_result($q230,0,1);
	$filename = mysql_result($q230,0,2);

	$filepath = "$conf_files_path/projekti/fajlovi/$projekat/$fileosoba/$filename/v$revizija/$filename";
}


// Tip: završni rad

if ($tip == "zavrsni") {
	$zavrsni = intval($_REQUEST['zavrsni']);
	$id = intval($_REQUEST['id']); //file ID

	$q300 = myquery("select predmet, akademska_godina from zavrsni where id=$zavrsni");
	if (mysql_num_rows($q300)<1) {
		zamgerlog("nepostojeca tema zavrsnog rada $zavrsni",3);
		zamgerlog2("nepostojeca tema zavrsnog rada", $zavrsni);
		niceerror("Nepostojeća tema završnog rada.");
		return;
	}
	$predmet = mysql_result($q300,0,0);
	$ag = mysql_result($q300,0,1);

	$ok = false;

	if ($user_siteadmin) $ok = true;
	if ($user_nastavnik && !$ok) {
		$q310 = myquery("select nivo_pristupa from nastavnik_predmet where predmet=$predmet and akademska_godina=$ag and nastavnik=$userid");
		if (mysql_num_rows($q310)>0 && mysql_result($q310,0,0)!="asistent") $ok = true;
	}
	if ($user_student && !$ok) {
		$q320 = myquery("SELECT count(*) FROM zavrsni WHERE student=$userid and id=$zavrsni");
		if (mysql_result($q320,0,0)>0) $ok = true;
	}

	if (!$ok) {
		zamgerlog("nema pravo pristupa zavrsnom radu $zavrsni",3);
		zamgerlog2("nema pravo pristupa zavrsnom radu", $zavrsni);
		niceerror("Nemate pravo pristupa ovom završnom radu.");
		return;
	}
	
	$q330 = myquery("select revizija, filename from zavrsni_file where id=$id");
	if (mysql_num_rows($q330)<1) {
		zamgerlog("nepostojeci file $id na zavrsnom radu $zavrsni", 3);
		zamgerlog2("nepostojeci file na zavrsnom radu", $zavrsni, $id);
		niceerror("Nepoznat ID $id");
		return;
	}
	
	$revizija = mysql_result($q330,0,0);
	$filename = mysql_result($q330,0,1);

	$filepath = "$conf_files_path/zavrsni/fajlovi/$zavrsni/$filename/v$revizija/$filename";
}

// DOWNLOAD

$type = `file -bi '$filepath'`;
header("Content-Type: $type");
header('Content-Disposition: attachment; filename="' . $filename.'"', false);
header("Content-Length: ".(string)(filesize($filepath)));

// workaround za http://support.microsoft.com/kb/316431 (zamger bug 94)
header("Pragma: dummy=bogus"); 
header("Cache-Control: private");

$k = readfile($filepath,false);
if ($k == false) {
	print "Otvaranje attachmenta nije uspjelo! Kontaktirajte administratora";
	zamgerlog("citanje fajla za attachment nije uspjelo (z$zadaca zadatak $zadatak student $stud_id)", 3);
	if ($tip == "zadaca") zamgerlog2("citanje fajla za attachment nije uspjelo - zadaca", $zadaca, $zadatak);
	if ($tip == "postavka") zamgerlog2("citanje fajla za attachment nije uspjelo - postavka", $zadaca);
	if ($tip == "projekat") zamgerlog2("citanje fajla za attachment nije uspjelo - projekat", $projekat, $id);
	if ($tip == "zavrsni") zamgerlog2("citanje fajla za attachment nije uspjelo - zavrsni", $zavrsni, $id);
}
exit;

}

?>
