<?

// COMMON/ATTACHMENT - download bilo čega


function common_attachment() {

global $userid,$conf_files_path,$user_student,$user_nastavnik,$user_siteadmin,$_api_http_code,$user_studentska;


// Kakav fajl se downloaduje?
$tip = $_REQUEST['tip'];
if ($tip == "") $tip = "zadaca"; // privremeno

$student = $userid; // Inicijalizacija je potrebna zbog logginga

// PROVJERA PRIVILEGIJA I ODREĐIVANJE LOKACIJE FAJLA NA SERVERU

// Tip: zadaća

if ($tip == "zadaca") {
	// Poslani parametri
	$zadaca = intval($_REQUEST['zadaca']);
	$zadatak = intval($_REQUEST['zadatak']);
	$student = intval($_REQUEST['student']);

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
		// Provjera da li je nastavnik na predmetu će biti urađena na backendu
	}
	
	$assignment = api_call("homework/$zadaca/$zadatak/student/$student", []);
	if ($_api_http_code == "404") {
		zamgerlog("student nije upisan na predmet (student u$student zadaca z$zadaca)",3);
		zamgerlog2("student ne slusa predmet za zadacu", $zadaca);
		niceerror("Nemate pravo pregleda ove zadaće");
		return;
	}
	
	$filename = $assignment['filename'];
	
	$content = api_call("homework/$zadaca/$zadatak/student/$student/file", [], "GET", false, false);
	
	// Kreiramo privremenu datoteku u koju ćemo upisati sadržinu fajla
	$dir = "$conf_files_path/zadacetmp/$userid/";
	if (!file_exists($dir))
		mkdir ($dir,0777, true);
	
	$filepath = $dir . $filename;
	if (file_exists($filepath))
		unlink($filepath);
	
	$f = fopen($filepath,'w');
	if (!$f) {
		zamgerlog("greska pri pisanju zadace z$zadaca zadatak $zadatak",3); // nivo 3 - greska
		zamgerlog2("greska pri pisanju zadace", $zadaca, $zadatak); // nivo 3 - greska
		return;
	}
	fwrite($f, $content);
	fclose($f);
}



// Tip: postavka zadaće

if ($tip == "dodatne") {
	$zadaca = intval($_REQUEST['zadaca']);
	$file=intval($_REQUEST['file']);
	
	$homeworkFiles = api_call("homework/$zadaca/files")["results"];
	if ($_api_http_code != "200") {
		zamgerlog("nema pravo pristupa postavci zadace (z$zadaca)",3);
		zamgerlog2("nema pravo pristupa postavci zadace", $zadaca);
		niceerror("Nemate pravo pristupa ovoj postavci");
		return;
	}
	
	$homeworkFile = false;
	foreach($homeworkFiles as $hwf)
		if ($hwf['id'] == $file)
			$homeworkFile = $hwf;
	if ($homeworkFile == false) {
		niceerror("Postavka ne postoji");
		zamgerlog("postavka ne postoji z$zadaca", 3);
		zamgerlog2("postavka ne postoji", $zadaca);
		return;
	}
	
	$filename = $homeworkFile['filename'];
	
	$content = api_call("homework/files/" . $file, [], "GET", false, false);
	
	// Kreiramo privremenu datoteku u koju ćemo upisati sadržinu fajla
	$dir = "$conf_files_path/zadacetmp/$userid/";
	if (!file_exists($dir))
		mkdir ($dir,0777, true);
	
	$filepath = $dir . $filename;
	if (file_exists($filepath))
		unlink($filepath);
	
	$f = fopen($filepath,'w');
	if (!$f) {
		zamgerlog("greska pri pisanju zadace z$zadaca zadatak $zadatak",3); // nivo 3 - greska
		zamgerlog2("greska pri pisanju zadace", $zadaca, $zadatak); // nivo 3 - greska
		return;
	}
	fwrite($f, $content);
	fclose($f);

}


// Tip: projektni fajl

if ($tip == "projekat") {
	$projekat = intval($_REQUEST['projekat']);
	$id = intval($_REQUEST['id']); //file ID

	$q200 = db_query("select predmet, akademska_godina from projekat where id=$projekat");
	if (db_num_rows($q200)<1) {
		zamgerlog("nepostojeci projekat $projekat",3);
		zamgerlog2("nepostojeci projekat", $projekat);
		niceerror("Nepostojeći projekat");
		return;
	}
	$predmet = db_result($q200,0,0);
	$ag = db_result($q200,0,1);

	$ok = false;

	if ($user_siteadmin) $ok = true;
	if ($user_nastavnik && !$ok) {
		$q210 = db_query("select nivo_pristupa from nastavnik_predmet where predmet=$predmet and akademska_godina=$ag and nastavnik=$userid");
		if (db_num_rows($q210)>0 && db_result($q210,0,0)!="asistent") $ok = true;
	}
	if ($user_student && !$ok) {
		$q220 = db_query("SELECT count(*) FROM student_projekat WHERE student=$userid and projekat=$projekat");
		if (db_result($q220,0,0)>0) $ok = true;
	}

	if (!$ok) {
		zamgerlog("nema pravo pristupa projektu $projekat",3);
		zamgerlog2("nije na projektu", $projekat);
		niceerror("Nemate pravo pristupa ovom projektu.");
		return;
	}
	
	$q230 = db_query("select osoba, revizija, filename from projekat_file where id=$id");
	if (db_num_rows($q230)<1) {
		zamgerlog("nepostojeci file $id na projektu $projekat", 3);
		zamgerlog2("nepostojeci file na projektu", $projekat, $id);
		niceerror("Nepoznat ID $id");
		return;
	}
	
	$fileosoba = db_result($q230,0,0);
	$revizija = db_result($q230,0,1);
	$filename = db_result($q230,0,2);

	$filepath = "$conf_files_path/projekti/fajlovi/$projekat/$fileosoba/$filename/v$revizija/$filename";
}


// Tip: završni rad

if ($tip == "zavrsni") {
	$zavrsni = intval($_REQUEST['zavrsni']);
	$id = intval($_REQUEST['id']); //file ID

	$q300 = db_query("select predmet, akademska_godina from zavrsni where id=$zavrsni");
	if (db_num_rows($q300)<1) {
		zamgerlog("nepostojeca tema zavrsnog rada $zavrsni",3);
		zamgerlog2("nepostojeca tema zavrsnog rada", $zavrsni);
		niceerror("Nepostojeća tema završnog rada.");
		return;
	}
	$predmet = db_result($q300,0,0);
	$ag = db_result($q300,0,1);

	$ok = false;

	if ($user_siteadmin) $ok = true;
	if ($user_nastavnik && !$ok) {
		$q310 = db_query("select nivo_pristupa from nastavnik_predmet where predmet=$predmet and akademska_godina=$ag and nastavnik=$userid");
		if (db_num_rows($q310)>0 && db_result($q310,0,0)!="asistent") $ok = true;
	}
	if ($user_student && !$ok) {
		$q320 = db_query("SELECT count(*) FROM zavrsni WHERE student=$userid and id=$zavrsni");
		if (db_result($q320,0,0)>0) $ok = true;
	}

	if (!$ok) {
		zamgerlog("nema pravo pristupa zavrsnom radu $zavrsni",3);
		zamgerlog2("nema pravo pristupa zavrsnom radu", $zavrsni);
		niceerror("Nemate pravo pristupa ovom završnom radu.");
		return;
	}
	
	$q330 = db_query("select revizija, filename from zavrsni_file where id=$id");
	if (db_num_rows($q330)<1) {
		zamgerlog("nepostojeci file $id na zavrsnom radu $zavrsni", 3);
		zamgerlog2("nepostojeci file na zavrsnom radu", $zavrsni, $id);
		niceerror("Nepoznat ID $id");
		return;
	}
	
	$revizija = db_result($q330,0,0);
	$filename = db_result($q330,0,1);

	$filepath = "$conf_files_path/zavrsni/fajlovi/$zavrsni/$filename/v$revizija/$filename";
}



// Tip: uplatnica

if ($tip == "uplatnica") {
	$student = $userid;
	if ($user_studentska && int_param('student'))
		$student = int_param('student');
	$ag = int_param('ag');
	$dir = "$conf_files_path/uplatnice/$student/";
	$filepath = $dir . "uplatnica-$ag.jpg";
	if (!file_exists($filepath))
		$filepath = $dir . "uplatnica-$ag.png";
	if (!file_exists($filepath))
		$filepath = $dir . "uplatnica-$ag.pdf]";
	if (!file_exists($filepath))
		exit;
	
	$filename = basename($filepath);
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
	zamgerlog("citanje fajla za attachment nije uspjelo (z$zadaca zadatak $zadatak student $student)", 3);
	if ($tip == "zadaca") zamgerlog2("citanje fajla za attachment nije uspjelo - zadaca", $zadaca, $zadatak);
	if ($tip == "postavka") zamgerlog2("citanje fajla za attachment nije uspjelo - postavka", $zadaca);
	if ($tip == "projekat") zamgerlog2("citanje fajla za attachment nije uspjelo - projekat", $projekat, $id);
	if ($tip == "zavrsni") zamgerlog2("citanje fajla za attachment nije uspjelo - zavrsni", $zavrsni, $id);
}
exit;

}

?>
