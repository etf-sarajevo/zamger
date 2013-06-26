<?

// IZVJESTAJ/INDEX - spisak ocjena studenta

// v3.9.1.0 (2008/04/11) + Izvjestaj izdvojen iz bivseg admin_izvjestaj.php
// v3.9.1.1 (2008/08/28) + Tabela osoba umjesto auth
// v4.0.0.0 (2009/02/19) + Release
// v4.0.9.1 (2009/03/31) + Tabela konacna_ocjena preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.2 (2009/09/15) + Ocjene po odluci



function izvjestaj_index2() {


global $userid, $user_studentska, $user_siteadmin;

// Ulazni parametar
$student = intval($_REQUEST['student']);


// Prava pristupa
if (!$user_studentska && !$user_siteadmin && $userid!=$student) {
	biguglyerror("Nemate pravo pristupa ovom izvještaju");
	zamgerlog("nije studentska, a pristupa tudjem izvjestaju ($student)", 3);
	return;
}


// Deklaracije nizova
$imena_semestara = array("", "prvi", "drugi", "treći", "četvrti", "peti", "šesti");
$rimski_brojevi = array("", "I", "II", "III", "IV", "V", "VI");
$imena_ocjena = array("", "", "", "", "", "pet", "šest", "sedam", "osam", "devet", "deset");
$ects_ocjene = array("", "", "", "", "", "F", "E", "D", "C", "B", "A");

// Podaci o studentu
$q100 = myquery("select ime, prezime, brindexa, jmbg, spol from osoba where id=$student");
if (!($r100 = mysql_fetch_row($q100))) {
	biguglyerror("Student se ne nalazi u bazi podataka.");
	zamgerlog("nepoznat ID $student",3); // 3 = greska
	return;
}


$q110 = myquery("select s.naziv, ag.naziv, ss.semestar, ns.naziv, ss.ponovac, s.id, ts.ciklus, s.institucija from student_studij as ss, studij as s, nacin_studiranja as ns, akademska_godina as ag, tipstudija as ts where ss.student=$student and ss.studij=s.id and ss.akademska_godina=ag.id and ss.nacin_studiranja=ns.id and s.tipstudija=ts.id order by ag.id desc, ss.semestar desc limit 1");
if (!($r110 = mysql_fetch_row($q110))) {
	niceerror("Nemamo podataka o studiju za studenta ".$r100[0]." ".$r100[1]);
	zamgerlog("student u$student nikada nije studirao", 3);
	return;
}
if ($r110[4] == 1) {
	$q120 = myquery("select count(*) from student_studij where student=$student and studij=$r110[5] and semestar=$r110[2]");
	$koji_put = mysql_result($q120,0,0);
} else $koji_put = "1";

?>
<img src="images/content/ETF-memorandum.png">
<p>&nbsp;</p>
<p>Na osnovu člana 169. Zakona o upravnom postupku FBiH (Službene novine FBiH, broj 2/98, 48/99) i člana 147. (4) Zakona o visokom obrazovanju Kantona Sarajevo - prečišćeni tekst (Službene novine Kantona Sarajevo, broj 22/10) izdaje se</p>

<h2>Uvjerenje o prepisu ocjena</h2>
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
	<td>JMBG:</td>
	<td><b><?=$r100[3]?></b></td>
</tr>
</table>

<?

$spol = $r100[4];
if ($spol == "") $spol = spol($r100[0]);

if ($spol == "Z") {
	?>
	<p>Studentica <?=$r100[0]." ".$r100[1]?> je upisana u akademskoj <?=$r110[1]?>. godini u <?=$rimski_brojevi[$r110[2]]?> (<?=$imena_semestara[$r110[2]]?>) semestar <?=$koji_put?>. put kao <?=$r110[3]?> student, studij "<?=$r110[0]?>" (<?=$r110[6]?>. ciklus), pri čemu je položila sljedeće predmete:</p>
	<?
} else {
	?>
	<p>Student <?=$r100[0]." ".$r100[1]?> je upisan u akademskoj <?=$r110[1]?>. godini u <?=$rimski_brojevi[$r110[2]]?> (<?=$imena_semestara[$r110[2]]?>) semestar <?=$koji_put?>. put kao <?=$r110[3]?> student, studij "<?=$r110[0]?>" (<?=$r110[6]?>. ciklus), pri čemu je položio sljedeće predmete:</p>
	<?
}



// Ocjene po odluci:

$q105 = myquery("select ko.ocjena, p.naziv, UNIX_TIMESTAMP(o.datum), o.broj_protokola from konacna_ocjena as ko, odluka as o, predmet as p where ko.odluka=o.id and ko.predmet=p.id and ko.student=$student");
if (mysql_num_rows($q105)>0) {
	?>
	<p><b>Ocjene donesene odlukom (nostrifikacija, promjena studija itd.):</b><br/><ul>
	<?
}
while ($r105 = mysql_fetch_row($q105)) {
	print "<li><b>$r105[1]</b> - ocjena: $r105[0] (".$imena_ocjena[$r105[0]-5].")<br/>(odluka br. $r105[3] od ".date("d. m. Y.", $r105[2]).")</li>\n";
}
if (mysql_num_rows($q105)>0) print "</ul></p><p>&nbsp;</p>\n";


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
$sumagodine = $brojgodine = $sumauk = $brojuk = $sumaects = 0;
$i=1;
$q130 = myquery("SELECT p.sifra, p.naziv, p.ects, ko.ocjena, UNIX_TIMESTAMP(ko.datum_u_indeksu), UNIX_TIMESTAMP(ko.datum), pk.semestar, ts.ciklus
FROM konacna_ocjena as ko, ponudakursa as pk, predmet as p, student_predmet as sp, studij as s, tipstudija as ts
WHERE ko.student=$student and ko.predmet=p.id and ko.predmet=pk.predmet and ko.akademska_godina=pk.akademska_godina and pk.id=sp.predmet 
and sp.student=$student and pk.studij=s.id and s.tipstudija=ts.id and ko.ocjena>5
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
		<td align="center"><?=$r130[3]?> (<?=$imena_ocjena[$r130[3]]?>)</td>
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
	<td><b><?=nuliraj(round($sumauk/$brojuk, 2))?></b></td>
</tr>
<tr>
	<td>Ukupan broj ECTS bodova:</td>
	<td><b><?=nuliraj($sumaects)?></b></td>
</tr>
</table>

<?

// Određivanje dekana
$institucija = $r110[7];
do {
	$q140 = myquery("select tipinstitucije, roditelj, dekan from institucija where id=$institucija");
	if (!($r140 = mysql_fetch_row($q140))) {
		return;
	}
	if ($r140[0] == 1 && $r140[2] != 0) {
		$dekan = $r140[2];
		break;
	}
	$institucija = $r140[1];
} while(true);



?>

<p>&nbsp;</p>

<p>Sarajevo, <?=date("d. m. Y.")?> godine</p>

<table border="0" width="100%">
<tr>
	<td width="60%">&nbsp;</td>
	<td width="40%" align="center"><p>DEKAN<br /><br /><br /><?=tituliraj($dekan)?></p></td>
</tr>
</table>

<?


}

?>