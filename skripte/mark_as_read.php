<?

// MARK_AS_READ.PHP - istovremena promjena statusa velikog broja zadaća

require("../www/lib/config.php");
require("../www/lib/dblayer.php");
require("../www/lib/libvedran.php");
require("../www/lib/zamger.php");

$aktuelna_zadaca = 3755;
$predmet = "1-12";
$aktuelni_zadatak = 1;

// 1 -sacekati, 2 - prepisana, 3 - bug, 4 - nova, 5 - pregledana

$fromstatus = 4;
$tostatus = 1;

db_connect($conf_dbhost,$conf_dbuser,$conf_dbpass,$conf_dbdb);


// Jezik zadace
$q2 = db_query("select pj.naziv,pj.ekstenzija from programskijezik as pj,zadaca where zadaca.id=$aktuelna_zadaca and zadaca.programskijezik=pj.id");
if (db_num_rows($q2)>0) {
	$jezik = db_result($q2,0,0);
	$ekst = db_result($q2,0,1);
} else {
	// Default jezik: C++
	$jezik = "C++";
	$ekst = ".cpp";
}



$kk=0;
$fcn = 0;
$dir1 = opendir("$conf_files_path/zadace/$predmet");
$maxzad=0;
while ($student = readdir($dir1)) {
	if ($student == "." || $student == "..") continue;
	$student = trim($student);
	if (!is_numeric($student)) { print "Nije ID studenta? $student\n"; continue; }
	$q10 = db_query("SELECT status, izvjestaj_skripte, komentar, filename, bodova FROM zadatak WHERE zadaca=$aktuelna_zadaca AND redni_broj=$aktuelni_zadatak AND student=$student ORDER BY id DESC LIMIT 1");
	$zadatak = db_fetch_assoc($q10);
	if ($zadatak['status'] == $fromstatus) {
		$q20 = db_query("INSERT INTO zadatak SET zadaca=$aktuelna_zadaca, redni_broj=$aktuelni_zadatak, student=$student, status=$tostatus, bodova=".$zadatak['bodova'].", izvjestaj_skripte='".db_escape_string($zadatak['izvjestaj_skripte'])."', komentar='".db_escape_string($zadatak['komentar'])."', filename='".db_escape_string($zadatak['filename'])."', vrijeme=NOW(), userid=0");
		print "Markiram ($student,$aktuelna_zadaca,$aktuelni_zadatak)\n";
	}
}

?>

Kraj.
