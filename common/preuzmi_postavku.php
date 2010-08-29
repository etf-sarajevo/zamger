<?php
//ova skripta sluzi da se omoguci download postavke zadace
function common_preuzmi_postavku(){
global $conf_files_path;

$predmet = intval($_REQUEST['predmet']);
$ag = intval($_REQUEST['ag']); // akademska godina
$zadaca= intval($_REQUEST['zadaca']); 

// Preuzimanje postavke
//mjesto gdje se smjestaju postavke zadaća
$lokacijapostavke="$conf_files_path/zadace/$predmet-$ag/postavke/";
//trazimo ime postavke iz baze
$q40 = myquery("select postavka_zadace from zadaca where id=$zadaca and predmet=$predmet and akademska_godina=$ag order by id desc limit 1");
if (mysql_num_rows($q40) < 1) {
	zamgerlog("ne postoji attachment ()",3);
	niceerror("Ne postoji attachment");
	return;
}

$postavka_zadace = mysql_result($q40,0,0);
$filepath = $lokacijapostavke.$postavka_zadace;


$type = `file -bi '$filepath'`;
header("Content-Type: $type");
header('Content-Disposition: attachment; filename="' . $postavka_zadace.'"', false);


header("Pragma: dummy=bogus"); 
header("Cache-Control: private");

$k = readfile($filepath,false);
if ($k == false) {
	print "Otvaranje attachmenta nije uspjelo! Kontaktirajte administratora";
	zamgerlog("citanje fajla za attachment nije uspjelo ", 3);
}
exit;
}
?>