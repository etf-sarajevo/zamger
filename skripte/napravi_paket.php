<?


require("../www/lib/libvedran.php");
require("../www/lib/zamger.php");
require("../www/lib/config.php");

// KONFIGURACIJA - FIXME premjestiti u config.php
$conf_workpackage_vrsta = "zadnje_automatsko"; // status postavljen na "potrebna automatska kontrola" poslije zadnjeg pokretanja
$conf_workpackage_arhiver = "zip";
$conf_workpackage_folder = "/tmp/"; // U koji folder staviti workpackage?


dbconnect();


// Preuzimamo vrijeme kreiranja zadnjeg paketa

$q10 = myquery("select UNIX_TIMESTAMP(kreiran), UNIX_TIMESTAMP(NOW()) from workpackage order by kreiran desc");
if (mysql_num_rows($q10)>0) {
	$staro_vrijeme = mysql_result($q10,0,0);
	$novo_vrijeme = mysql_result($q10,0,1);
} else {
	$staro_vrijeme = 0;
	$q11 = myquery("select UNIX_TIMESTAMP(NOW())");
	$novo_vrijeme = mysql_result($q11,0,0);
}


// Pripremamo paket zadaća za pregledati
$lista = array();

// Zadaće za koje je definisan programski jezik
$q20 = myquery("select z.id, pj.naziv, z.predmet, ag.id, pj.ekstenzija from zadaca as z, akademska_godina as ag, programskijezik as pj where z.akademska_godina=ag.id and ag.aktuelna=1 and z.programskijezik!=0 and z.programskijezik=pj.id");
while ($r20 = mysql_fetch_row($q20)) {
	$zadaca = $r20[0];
	$programski_jezik = $r20[1];
	$predmet = $r20[2];
	$ag = $r20[3];
	$ekstenzija = $r20[4];

	// Zadaci poslani u međuvremenu od zadnjeg pokretanja
	$q30 = myquery("select distinct redni_broj, student, filename from zadatak where zadaca=$zadaca and status=1 and UNIX_TIMESTAMP(vrijeme)>$staro_vrijeme");
	if (mysql_num_rows($q30)<1) continue;
	
	while ($r30 = mysql_fetch_row($q30)) {
		$zadatak = $r30[0];
		$student = $r30[1];
		$filename = $r30[2];

		// Preskačemo zadaće za koje je u međuvremenu asistent napravio izmjene
		$q40 = myquery("select status from zadatak where zadaca=$zadaca and redni_broj=$zadatak and student=$student order by id desc limit 1");
		if (mysql_result($q40,0,0) != 1) continue;

		// Dodajemo na listu
		$filepath = "$predmet-$ag/$student/$zadaca/$filename";
		array_push($lista, $filepath);
	}
}

// Lista je prazna?
if (empty($lista)) {
	echo "Nije napravljen radni paket: nema novih zadaća.\n";
	exit(0);
}

// Ima zadaća u paketu - kreiramo novi paket
$q35 = myquery("insert into workpackage set kreiran=FROM_UNIXTIME($novo_vrijeme), zavrsen=0");
$q36 = myquery("select id from workpackage where kreiran=FROM_UNIXTIME($novo_vrijeme)");
$workpackage = mysql_result($q36,0,0);

$tmpfolder = "/tmp/";
if ($conf_workpackage_arhiver == "zip") {
	$arhiva = "workpackage$workpackage";
	$arhiva_real = "workpackage$workpackage.zip";
}

// Prelazimo u direktorij zadaća kako bi putevi bili relativni na taj folder
chdir ("$conf_files_path/zadace/");

// Kreiramo arhivu
if (file_exists("$tmpfolder$arhiva_real")) unlink("$tmpfolder$arhiva_real");
foreach($lista as $fajl) {
	exec("zip $tmpfolder$arhiva \"$fajl\"");
}

// Premještamo u potrebni folder
if ($conf_workpackage_folder != $tmpfolder)
	rename("$tmpfolder$arhiva_real", "$conf_workpackage_folder$arhiva_real");
	
echo "Pripremljen radni paket $workpackage ($arhiva_real).\n"

?>

