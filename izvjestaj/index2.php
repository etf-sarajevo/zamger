<?

// IZVJESTAJ/INDEX - spisak ocjena studenta

// v3.9.1.0 (2008/04/11) + Izvjestaj izdvojen iz bivseg admin_izvjestaj.php
// v3.9.1.1 (2008/08/28) + Tabela osoba umjesto auth
// v4.0.0.0 (2009/02/19) + Release
// v4.0.9.1 (2009/03/31) + Tabela konacna_ocjena preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.2 (2009/09/15) + Ocjene po odluci



function izvjestaj_index2() {


global $userid, $user_studentska, $user_siteadmin;

// Ulazni parametri
$student      = intval($_REQUEST['student']);
$param_ciklus = intval($_REQUEST['ciklus']);


// Prava pristupa
if (!$user_studentska && !$user_siteadmin && $userid!=$student) {
	biguglyerror("Nemate pravo pristupa ovom izvještaju");
	zamgerlog("nije studentska, a pristupa tudjem izvjestaju ($student)", 3);
	zamgerlog2("nije studentska, a pristupa tudjem izvjestaju", $student);
	return;
}


// Deklaracije nizova
$imena_semestara = array("", "prvi", "drugi", "treći", "četvrti", "peti", "šesti");
$rimski_brojevi = array("", "I", "II", "III", "IV", "V", "VI");
$imena_ocjena = array("", "", "", "", "", "5 (pet)", "6 (šest)", "7 (sedam)", "8 (osam)", "9 (devet)", "10 (deset)", "ispunio/la obaveze");
$ects_ocjene = array("", "", "", "", "", "F", "E", "D", "C", "B", "A", "IO");

// Podaci o studentu
$q100 = myquery("select ime, prezime, brindexa, jmbg, spol from osoba where id=$student");
if (!($r100 = mysql_fetch_row($q100))) {
	biguglyerror("Student se ne nalazi u bazi podataka.");
	zamgerlog("nepoznat ID $student",3); // 3 = greska
	zamgerlog2("nepoznat id korisnika", $student); // 3 = greska
	return;
}

$ime_prezime = "$r100[0] $r100[1]";
$brindexa    = $r100[2];
$jmbg        = $r100[3];


if ($param_ciklus != 0) $upit_dodaj = " AND ts.ciklus=$param_ciklus";

$q110 = myquery("SELECT s.naziv, ag.naziv, ss.semestar, ns.naziv, ss.ponovac, s.id, ts.ciklus, s.institucija, ts.trajanje, ts.ects 
FROM student_studij as ss, studij as s, nacin_studiranja as ns, akademska_godina as ag, tipstudija as ts 
WHERE ss.student=$student and ss.studij=s.id and ss.akademska_godina=ag.id and ss.nacin_studiranja=ns.id and s.tipstudija=ts.id $upit_dodaj
ORDER BY ag.id desc, ss.semestar DESC LIMIT 1");
if (!($r110 = mysql_fetch_row($q110))) {
	niceerror("Nemamo podataka o studiju za studenta ".$r100[0]." ".$r100[1]);
	zamgerlog("student u$student nikada nije studirao", 3);
	zamgerlog2("korisnik nikada nije studirao", $student);
	return;
}

$naziv_studija     = $r110[0];
$naziv_ag          = $r110[1];
$trenutno_semestar = $r110[2];
$nacin_studiranja  = $r110[3];
$ponovac           = $r110[4];
$studij_ciklus     = $r110[6];
$studij_trajanje   = $r110[8];
$studij_ects       = $r110[9];

if ($ponovac == 1) {
	$q120 = myquery("select count(*) from student_studij where student=$student and studij=$r110[5] and semestar=$r110[2]");
	$koji_put = mysql_result($q120,0,0);
} else $koji_put = "1";

// Kod izvještaja za sve cikluse sumiramo ECTS bodove na svim studijima koje je student slušao
if ($studij_ciklus == 2 /* zašto samo 2? */ && $param_ciklus == 0) {
	$q115 = myquery("select ts.ects from student_studij as ss, studij as s, tipstudija as ts where ss.student=$student and ss.studij=s.id and s.tipstudija=ts.id and ts.ciklus=1 and ss.semestar=ts.trajanje order by ss.akademska_godina desc limit 1");
	if (mysql_num_rows($q115) > 0) {
		$studij_ects += mysql_result($q115,0,0);
	}
}


?>
<img src="images/content/ETF-memorandum.png">
<p>&nbsp;</p>
<p>Na osnovu člana 169. Zakona o upravnom postupku FBiH (Službene novine FBiH, broj 2/98, 48/99), člana 147. (4) Zakona o visokom 
obrazovanju Kantona Sarajevo (Službene novine Kantona Sarajevo, broj 22/10, 15/13) i člana 198. stav (1) Statuta 
Univerziteta u Sarajevu, Elektrotehnički fakultet u Sarajevu izdaje</p>

<h2>Uvjerenje o prepisu ocjena</h2>
<p>&nbsp;<br />
<table border="0">
<tr>
	<td>Ime i prezime studenta:</td>
	<td><b><?=$ime_prezime?></b></td>
</tr>
<tr>
	<td>Broj dosijea:</td>
	<td><b><?=$brindexa?></b></td>
</tr>
<tr>
	<td>JMBG:</td>
	<td><b><?=$jmbg?></b></td>
</tr>
</table>

<?

$spol = $r100[4];
if ($spol == "") $spol = spol($r100[0]);


// Da li je student završio/la studij?
$q88 = myquery("SELECT COUNT(*), SUM(p.ects) 
FROM konacna_ocjena as ko, ponudakursa as pk, predmet as p, student_predmet as sp, studij as s, tipstudija as ts
WHERE ko.student=$student and ko.predmet=p.id and ko.predmet=pk.predmet and ko.akademska_godina=pk.akademska_godina and pk.id=sp.predmet 
and sp.student=$student and pk.studij=s.id and s.tipstudija=ts.id and ko.ocjena>5 $upit_dodaj");
$broj_polozenih_predmeta = mysql_result($q88,0,0);
$suma_ects = mysql_result($q88,0,1);

// Određujemo na osnovu sume ECTS kredita
if ($suma_ects >= $studij_ects && $trenutno_semestar == $studij_trajanje) {
	$q89 = myquery("SELECT UNIX_TIMESTAMP(ko.datum_u_indeksu) 
	FROM konacna_ocjena as ko, predmet as p, ponudakursa as pk, student_predmet as sp, studij as s, tipstudija as ts, akademska_godina_predmet as agp
	WHERE ko.student=$student and ko.predmet=p.id and ko.predmet=pk.predmet and ko.akademska_godina=pk.akademska_godina and pk.id=sp.predmet and sp.student=$student and pk.studij=s.id and s.tipstudija=ts.id and agp.predmet=p.id and agp.akademska_godina=pk.akademska_godina and agp.tippredmeta=1000 $upit_dodaj
	ORDER BY ko.datum_u_indeksu desc"); // 1000 = tip predmeta "Završni rad"
	if (mysql_num_rows($q89) == 0) {
		niceerror("Greška! Ne može se generisati izvještaj.");
		print "Student je završio studij jer trenutno nije upisan, ima sve potrebne ECTS kredite, ali nije unesena ocjena za Završni rad tako da se ne može odrediti datum diplomiranja. Nešto nije u redu sa podacima vezanim za ovog studenta (suma ECTS kredita ne bi trebala biti ispravna) STUDIJ: $studij_ects SUMA $suma_ects.";
		return;
	}
	$datum_diplomiranja = date("d. m. Y.", mysql_result($q89,0,0));

	if ($spol == "Z") {
		?>
		<p>Studentica <?=$ime_prezime?> je završila <?=$studij_ciklus?>. ciklus studija dana <?=$datum_diplomiranja?> kao <?=$nacin_studiranja?> student, studij "<?=$naziv_studija?>" , pri čemu je položila sljedeće predmete:</p>
		<?
	} else {
		?>
		<p>Student <?=$ime_prezime?> je završio <?=$studij_ciklus?>. ciklus studija dana <?=$datum_diplomiranja?> kao <?=$nacin_studiranja?> student, studij "<?=$naziv_studija?>", pri čemu je položio sljedeće predmete:</p>
		<?
	}

} else {
	if ($spol == "Z") {
		?>
		<p>Studentica <?=$ime_prezime?> je upisana u akademskoj <?=$naziv_ag?>. godini u <?=$rimski_brojevi[$trenutno_semestar]?> (<?=$imena_semestara[$trenutno_semestar]?>) semestar <?=$koji_put?>. put kao <?=$nacin_studiranja?> student, studij "<?=$naziv_studija?>" (<?=$studij_ciklus?>. ciklus), pri čemu je položila sljedeće predmete:</p>
		<?
	} else {
		?>
		<p>Student <?=$ime_prezime?> je upisan u akademskoj <?=$naziv_ag?>. godini u <?=$rimski_brojevi[$trenutno_semestar]?> (<?=$imena_semestara[$trenutno_semestar]?>) semestar <?=$koji_put?>. put kao <?=$nacin_studiranja?> student, studij "<?=$naziv_studija?>" (<?=$studij_ciklus?>. ciklus), pri čemu je položio sljedeće predmete:</p>
		<?
	}
}



$sumagodine = $brojgodine = $sumauk = $brojuk = $sumaects = 0;



// Ocjene po odluci:

$q105 = myquery("select ko.ocjena, p.naziv, UNIX_TIMESTAMP(o.datum), o.broj_protokola, p.ects from konacna_ocjena as ko, odluka as o, predmet as p where ko.odluka=o.id and ko.predmet=p.id and ko.student=$student");
if (mysql_num_rows($q105)>0) {
	?>
	<p><b>Ocjene donesene odlukom (nostrifikacija, promjena studija itd.):</b><br/><ul>
	<?
}
while ($r105 = mysql_fetch_row($q105)) {
	print "<li><b>$r105[1]</b> - ocjena: ".$imena_ocjena[$r105[0]]."<br/>(odluka br. $r105[3] od ".date("d. m. Y.", $r105[2]).")</li>\n";
	$sumauk += $r105[0];
	$brojuk++;
	$sumaects += $r105[4];
}
if (mysql_num_rows($q105)>0) print "</ul></p><p>&nbsp;</p>\n";



// Ocjene priznavanje

if ($param_ciklus != 0) $dod_priznavanje = " and ciklus=$param_ciklus"; else $dod_priznavanje = "";
$q125 = myquery("select naziv_predmeta, sifra_predmeta, ects, ocjena, odluka, akademska_godina, strana_institucija from priznavanje where student=$student $dod_priznavanje order by odluka, akademska_godina, naziv_predmeta");
if (mysql_num_rows($q125)>0) {
	?>
	<p><b>Priznavanje ocjena ostvarenih na drugoj instituciji po osnovu mobilnosti studenata:</b></p>
	<table width="700" border="1" cellspacing="0" cellpadding="3"><tr bgcolor="#AAAAAA">
		<td width="20"><b>R.br.</b></td>
		<td width="60"><b>Šifra</b></td>
		<td width="280"><b>Naziv predmeta</b></td>
		<td width="30"><b>ECTS bodovi</b></td>
		<td width="60"><b>Konačna ocjena</b></td>
		<td width="40"><b>ECTS ocjena</b></td>
	</tr>
	<?
}
$i = 1; $stara_odluka = $stara_ag = $stara_inst = 0;
while ($r125 = mysql_fetch_row($q125)) {
	if ($r125[4] != $stara_odluka || $r125[5] != $stara_ag || $r125[6] != $stara_inst) {
		$stara_odluka = $r125[4];
		$stara_ag = $r125[5];
		$stara_inst = $r125[6];
		$q115 = myquery("select UNIX_TIMESTAMP(datum), broj_protokola from odluka where id=$stara_odluka");
		if (mysql_num_rows($q115) > 0)
			$odluka_ispis = " (odluka br. ".mysql_result($q115,0,1)." od ".date("d. m. Y.", mysql_result($q115,0,0)).")";
		$q127 = myquery("SELECT naziv FROM akademska_godina WHERE id=$stara_ag");
		?>
		<tr bgcolor="#CCCCCC">
			<td colspan="6"><b><?=$stara_inst?>, akademska <?=mysql_result($q127,0,0)?>. godina <?=$odluka_ispis?>:</b></td>
		</tr>
		<?
	}
	?>
	<tr>
		<td><?=$i++?></td><td><?=$r125[1]?></td><td><?=$r125[0]?></td>
		<td><?=$r125[2]?></td>
		<td><?=$imena_ocjena[$r125[3]]?></td>
		<td align="center"><?=$ects_ocjene[$r125[3]]?></td>
	</tr>
	<?
	$sumauk += $r125[3];
	$brojuk++;
	$sumaects += $r125[2];
}
if (mysql_num_rows($q125)>0) print "</table><p>&nbsp;</p><p><b>Ocjene ostvarene na matičnoj instituciji:</b></p>\n";


?>

<table width="700" border="1" cellspacing="0" cellpadding="3"><tr bgcolor="#AAAAAA">
	<td width="20"><b>R.br.</b></td>
	<td width="60"><b>Šifra</b></td>
	<td width="280"><b>Naziv predmeta</b></td>
	<td width="30"><b>ECTS bodovi</b></td>
	<td width="60"><b>Konačna ocjena</b></td>
	<td width="40"><b>ECTS ocjena</b></td>
	<td width="80"><b>Datum polaganja</b></td>
</tr>
<?

function nuliraj($broj) {
	if ($broj == round($broj))
		$broj .= ",0";
	else
		$broj = str_replace(".", ",", $broj);
	return $broj;
}

$upisanagodina = round($r110[2]/2);

$oldgodina = 0;
$i=1;
$q130 = myquery("SELECT p.sifra, p.naziv, p.ects, ko.ocjena, UNIX_TIMESTAMP(ko.datum_u_indeksu), UNIX_TIMESTAMP(ko.datum), pk.semestar, ts.ciklus
FROM konacna_ocjena as ko, ponudakursa as pk, predmet as p, student_predmet as sp, studij as s, tipstudija as ts
WHERE ko.student=$student and ko.predmet=p.id and ko.predmet=pk.predmet and ko.akademska_godina=pk.akademska_godina and pk.id=sp.predmet 
and sp.student=$student and pk.studij=s.id and s.tipstudija=ts.id and ko.ocjena>5 $upit_dodaj
ORDER BY ts.ciklus, pk.semestar, p.naziv");
while ($r130 = mysql_fetch_row($q130)) {
	$godina = round($r130[6]/2);

	if ($oldgodina != $godina) {
		// Koliziju preskačemo
		if ($godina > $upisanagodina && $r130[7]==$r110[6]) break;

		if ($oldgodina != 0 && $brojgodine != 0) {
			?>
			<tr bgcolor="#DDDDDD">
			<td colspan="7"><b>Prosjek godine: <?=nuliraj(round($sumagodine/$brojgodine, 2))?></b></td>
			</tr>
			<?
			$sumagodine = $brojgodine = 0;
		}
		?>
		<tr bgcolor="#CCCCCC">
		<td colspan="7"><b><?=$godina?>. GODINA <?=$r130[7]?>. CIKLUSA STUDIJA</b></td>
		</tr>
		<?
		$oldgodina = $godina;
	}

	$datum = $r130[4];
	if ($datum == 0) $datum = $r130[5];
	
	?>
	<tr>
		<td><?=($i++)?>.</td>
		<td><?=$r130[0]?></td>
		<td><?=$r130[1]?></td>
		<td align="center"><?=nuliraj($r130[2])?></td>
		<td align="center"><?=$imena_ocjena[$r130[3]]?></td>
		<td align="center"><?=$ects_ocjene[$r130[3]]?></td>
		<td align="center"><?=date("d. m. Y", $datum)?></td>
	</tr>
	<?

	$sumagodine += $r130[3];
	$sumauk += $r130[3];
	$brojgodine++;
	$brojuk++;
	$sumaects += $r130[2];
}

if ($oldgodina != 0 && $brojgodine != 0) {
	?>
	<tr bgcolor="#DDDDDD">
	<td colspan="7"><b>Prosjek godine: <?=nuliraj(round($sumagodine/$brojgodine, 2))?></b></td>
	</tr>
	<?
}

if ($brojuk == 0) $prosjek = 0; else $prosjek = $sumauk/$brojuk;

?>
</table>

<p>&nbsp;</p>

<table border="1" cellspacing="0" cellpadding="3">
<tr>
	<td>Ukupan broj položenih predmeta:</td>
	<td><b><?=$brojuk?></b></td>
</tr>
<tr>
	<td>Prosječna ocjena položenih predmeta:</td>
	<td><b><?=nuliraj(round($prosjek, 2))?></b></td>
</tr>
<tr>
	<td>Ukupan broj ECTS bodova:</td>
	<td><b><?=nuliraj($sumaects)?></b></td>
</tr>
</table>

<?

// Određivanje dekana i broja protokola
$institucija = $r110[7];
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



?>

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


// Označi izvještaj kao obrađen - FIXME: ovo treba biti event na klik u studentska/intro
if ($user_studentska) {
	$q200 = myquery("SELECT id, status FROM zahtjev_za_potvrdu WHERE student=$student AND svrha_potvrde=1");
	while ($r200 = mysql_fetch_row($q200)) {
		if ($r200[1] == 1)
			$q210 = myquery("UPDATE zahtjev_za_potvrdu SET status=2 WHERE id=$r200[0]");
	}
}


}

?>