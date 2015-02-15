<?

// IZVJESTAJ/POTVRDA - potvrda o redovnom studiju



function izvjestaj_potvrda() {


global $userid, $user_studentska, $user_siteadmin;

$imena_semestara = array("", "prvi", "drugi", "treći", "četvrti", "peti", "šesti");
$rimski_brojevi = array("", "I", "II", "III", "IV", "V", "VI");


// Ulazni parametar
$student = intval($_REQUEST['student']);
$svrha = intval($_REQUEST['svrha']);


// Prava pristupa
if (!$user_studentska && !$user_siteadmin && $userid!=$student) {
	biguglyerror("Nemate pravo pristupa ovom izvještaju");
	zamgerlog("nije studentska, a pristupa tudjem izvjestaju ($student)", 3);
	zamgerlog2("nije studentska, a pristupa tudjem izvjestaju", $student);
	return;
}

$q100 = myquery("SELECT ime, prezime, brindexa, jmbg, UNIX_TIMESTAMP(datum_rodjenja), mjesto_rodjenja, spol FROM osoba WHERE id=$student");
if (mysql_num_rows($q100) < 1) {
	biguglyerror("Nepoznat student");
	return;
}
$r100 = mysql_fetch_row($q100);

$q110 = myquery("SELECT m.naziv, o.naziv, d.naziv FROM mjesto as m, opcina as o, drzava as d WHERE m.id=$r100[5] AND m.opcina=o.id and m.drzava=d.id");
$r110 = mysql_fetch_row($q110);

if ($r100[5] == 1) { // Sarajevo
	$r110[0] = "Sarajevo";
	$r110[1] = "Centar Sarajevo";
	$r110[2] = "Bosna i Hercegovina";
}

$q120 = myquery("SELECT naziv FROM svrha_potvrde WHERE id=$svrha");
if (mysql_num_rows($q120) < 1) {
	biguglyerror("Nepoznata svrha");
	return;
}
$r120 = mysql_fetch_row($q120);

// Treba nam ID aktuelne godine
$q200 = myquery("SELECT id, naziv FROM akademska_godina WHERE aktuelna=1");
$id_ak_god = mysql_result($q200, 0, 0);
$naziv_ak_god = mysql_result($q200, 0, 1);

// Trenutno upisan na semestar:
$q220 = myquery("SELECT s.naziv, ss.semestar, ss.akademska_godina, ag.naziv, s.id, ts.trajanje, ns.naziv, ts.ciklus, s.institucija from student_studij as ss, studij as s, akademska_godina as ag, tipstudija as ts, nacin_studiranja as ns where ss.student=$student and ss.studij=s.id and ag.id=ss.akademska_godina and s.tipstudija=ts.id and ss.nacin_studiranja=ns.id order by ag.naziv desc");
$studij="0";
$studij_id=$semestar=0;
$puta=1;

// Da li je ikada slusao nesto?
$ikad_studij=$ikad_studij_id=$ikad_semestar=$ikad_ak_god=$institucija=0;

while ($r220=mysql_fetch_row($q220)) {
	if ($r220[2]==$id_ak_god && $r220[1]>$semestar) { //trenutna akademska godina
		$studij = $r220[0];
		$semestar = $r220[1];
		$studij_id = $r220[4];
		$studij_trajanje = $r220[5];
		$nacin_studiranja = "kao $r220[6]";
		$ciklus = $r220[7];
		$institucija = $r220[8];
	}
	else if ($r220[0]==$studij && $r220[1]==$semestar) { // ponovljeni semestri
		$puta++;
	} else if ($r220[2]>$ikad_ak_god || ($r220[2]==$ikad_ak_god && $r220[1]>$ikad_semestar)) {
		$ikad_studij=$r220[0];
		$ikad_semestar=$r220[1];
		$ikad_ak_god=$r220[2];
		$ikad_ak_god_naziv=$r220[3];
		$ikad_studij_id=$r220[4];
		$ikad_studij_trajanje=$r220[5];
	}
}

if ($institucija == 0) {
	niceerror("Trenutno niste upisani na studij.");
	print "Ako je ovo greška, hitno kontaktirajte Studentsku službu.";
	return 0;
}

// Određivanje institucije
do {
	$q140 = myquery("select tipinstitucije, roditelj, dekan, broj_protokola from institucija where id=$institucija");
	if (!($r140 = mysql_fetch_row($q140))) {
		return;
	}
	if ($r140[0] == 1 && $r140[2] != 0) {
		$dekan = $r140[2];
		if ($r140[3] !== "")
			$dodaj_broj_protokola = "<p>Broj protokola: $r140[3]</p>";
		else
			$dodaj_broj_protokola = "";
		break;
	}
	$institucija = $r140[1];
} while(true);


$spol = $r100[6];
if ($spol == "") {
	$spol = spol($r100[0]);
}
if ($spol == "Z") {
	$upisana = "upisana";	
} else {
	$upisana = "upisan";
}

?>
<img src="images/content/ETF-memorandum.png">
<p>&nbsp;</p>
<p>Na osnovu člana 169. Zakona o upravnom postupku FBiH (Službene novine FBiH, broj 2/98, 48/99) izdaje se:</p>

<center><h2>P O T V R D A</h2></center>
<p>&nbsp;<br />
<table border="0">
<tr>
	<td>Ime i prezime studenta:</td>
	<td><b><?=$r100[0]." ".$r100[1]?></b></td>
</tr>
<tr>
	<td>Broj dosijea:</td>
	<td><b><?=$r100[2]?></b></td>
</tr>
<tr>
	<td>Datum rođenja:</td>
	<td><b><?=date("d. m. Y", $r100[4]) ?></b></td>
</tr>
<tr>
	<td>Mjesto rođenja:</td>
	<td><b><?=$r110[0]?></b></td>
</tr>
<tr>
	<td>Općina rođenja:</td>
	<td><b><?=$r110[1]?></b></td>
</tr>
<tr>
	<td>Država rođenja:</td>
	<td><b><?=$r110[2]?></b></td>
</tr>
</table>

<p>&nbsp;</p>

<p>Potvrđuje se da je <?=$r100[0]." ".$r100[1]?> <?=$upisana?> <?=$puta?>. put u akademskoj <?=$naziv_ak_god?> godini u <?=$rimski_brojevi[$semestar]?> (<?=$imena_semestara[$semestar]?>) semestar - <?=$imena_semestara[$ciklus]?> ciklus <?=$nacin_studiranja?> student, na studiju <?=$studij?>.</p>

<p>Ova potvrda se izdaje u svrhu <b><?=$r120[0]?></b>, te se u druge svrhe ne može koristiti.</p>

<p>&nbsp;</p>

<p>Sarajevo, <?=date("d. m. Y.")?> godine</p>

<?=$dodaj_broj_protokola?>

<table border="0" width="100%">
<tr>
	<td width="60%">&nbsp;</td>
	<td width="40%" align="center"><p>DEKAN<br /><br /><br /><?=tituliraj($dekan)?></p></td>
</tr>
</table>

<?

// Označi izvještaj kao obrađen
if ($user_studentska) {
	require("gcm/push_message.php");

	$q200 = myquery("SELECT id, status FROM zahtjev_za_potvrdu WHERE student=$student AND svrha_potvrde=$svrha");
	while ($r200 = mysql_fetch_row($q200)) {
		if ($r200[1] == 1) {
			$q210 = myquery("UPDATE zahtjev_za_potvrdu SET status=2 WHERE id=$r200[0]");
			
			// Slanje GCM poruke
			push_message(array($student), "Potvrde", "Vaša potvrda/uvjerenje je spremno");
		}
	}
}


}

?>
