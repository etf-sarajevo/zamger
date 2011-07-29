<?

// IZVJESTAJ/INDEX - spisak ocjena studenta

// v3.9.1.0 (2008/04/11) + Izvjestaj izdvojen iz bivseg admin_izvjestaj.php
// v3.9.1.1 (2008/08/28) + Tabela osoba umjesto auth
// v4.0.0.0 (2009/02/19) + Release
// v4.0.9.1 (2009/03/31) + Tabela konacna_ocjena preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.2 (2009/09/15) + Ocjene po odluci



function izvjestaj_index() {


global $userid, $user_studentska, $user_siteadmin;

// Ulazni parametar
$student = intval($_REQUEST['student']);


// Prava pristupa
if (!$user_studentska && !$user_siteadmin && $userid!=$student) {
	biguglyerror("Nemate pravo pristupa ovom izvještaju");
	zamgerlog("nije studentska, a pristupa tudjem izvjestaju ($student)", 3);
	return;
}

?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>
<p>Datum i vrijeme izvještaja: <?=date("d. m. Y. H:i");?></p>
<?

// Podaci o studentu
$q100 = myquery("select ime,prezime,brindexa from osoba where id=$student");
if (!($r100 = mysql_fetch_row($q100))) {
	biguglyerror("Student se ne nalazi u bazi podataka.");
	zamgerlog("nepoznat ID $student",3); // 3 = greska
	return;
}
/*if ($r100[3] != 1) {
	biguglyerror("Nepoznat student");
	zamgerlog("korisnik u$student nema status studenta",3);
	return;
}*/


?>
<h2>Uvjerenje o položenim predmetima</h2>
<p>&nbsp;<br />
<big>Student:
<b><?=$r100[0]." ".$r100[1]?></b></big><br />
Broj indeksa: <?=$r100[2]?><br/><br/><br/>

<?

$imena_ocjena = array("Nije položio/la", "Šest","Sedam","Osam","Devet","Deset");


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

<p><b>Pregled položenih predmeta sa ocjenama</b></p>
<table width="700" border="1" cellspacing="0" cellpadding="3"><tr bgcolor="#AAAAAA">
	<td width="20">&nbsp;</td>
	<td width="330">Naziv predmeta</td>
	<td width="200">Akademska godina</td>
	<td width="150">Konačna ocjena</td>
</tr>
<?

$i=1;
$q110 = myquery("SELECT p.naziv, ko.ocjena, ag.naziv, pk.semestar 
FROM konacna_ocjena as ko, ponudakursa as pk, predmet as p, student_predmet as sp, akademska_godina as ag
WHERE ko.student=$student and ko.predmet=p.id and ko.akademska_godina=ag.id and ko.predmet=pk.predmet and pk.id=sp.predmet and sp.student=$student and pk.akademska_godina=ag.id order by ag.id, pk.semestar, p.naziv");
while ($r110 = mysql_fetch_row($q110)) {
	print "<tr><td>".($i++).".</td><td>".$r110[0]."</td><td>".$r110[2]."</td><td>".$r110[1]." (".$imena_ocjena[$r110[1]-5].")</td></tr>\n";
}
print "</table>";

}

?>