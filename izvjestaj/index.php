<?

// IZVJESTAJ/INDEX - spisak ocjena studenta



function izvjestaj_index() {


global $userid, $user_studentska, $user_siteadmin;

// Ulazni parametar
$student = intval($_REQUEST['student']);


// Prava pristupa
if (!$user_studentska && !$user_siteadmin && $userid!=$student) {
	biguglyerror("Nemate pravo pristupa ovom izvještaju");
	zamgerlog("nije studentska, a pristupa tudjem izvjestaju ($student)", 3);
	zamgerlog2("nije studentska, a pristupa tudjem izvjestaju", $student);
	return;
}

?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>
<p>Datum i vrijeme izvještaja: <?=date("d. m. Y. H:i");?></p>
<?

// Podaci o studentu
$q100 = db_query("select ime,prezime,brindexa from osoba where id=$student");
if (!($r100 = db_fetch_row($q100))) {
	biguglyerror("Student se ne nalazi u bazi podataka.");
	zamgerlog("nepoznat ID $student",3); // 3 = greska
	zamgerlog2("nepoznat id korisnika", $student); // 3 = greska
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

$imena_ocjena = array("", "", "", "", "", "5 (pet)", "6 (šest)", "7 (sedam)", "8 (osam)", "9 (devet)", "10 (deset)", "ispunio/la obaveze");


// Ocjene po odluci:

$q105 = db_query("select ko.ocjena, p.naziv, UNIX_TIMESTAMP(o.datum), o.broj_protokola from konacna_ocjena as ko, odluka as o, predmet as p where ko.odluka=o.id and ko.predmet=p.id and ko.student=$student");
if (db_num_rows($q105)>0) {
	?>
	<p><b>Ocjene donesene odlukom (nostrifikacija, promjena studija itd.):</b><br/><ul>
	<?
}
while ($r105 = db_fetch_row($q105)) {
	print "<li><b>$r105[1]</b> - ocjena: $r105[0] (".$imena_ocjena[$r105[0]-5].")<br/>(odluka br. $r105[3] od ".date("d. m. Y.", $r105[2]).")</li>\n";
}
if (db_num_rows($q105)>0) print "</ul></p><p>&nbsp;</p>\n";



// Ocjene priznavanje

$q125 = db_query("select naziv_predmeta, sifra_predmeta, ects, ocjena, odluka, akademska_godina, strana_institucija from priznavanje where student=$student order by odluka, akademska_godina, naziv_predmeta");
if (db_num_rows($q125)>0) {
	?>
	<p><b>Priznavanje ocjena ostvarenih na drugoj instituciji po osnovu mobilnosti studenata:</b></p>
	<?
}
$i = 1; $stara_odluka = $stara_ag = $stara_inst = 0;
while ($r125 = db_fetch_row($q125)) {
	if ($r125[4] != $stara_odluka || $r125[5] != $stara_ag || $r125[6] != $stara_inst) {
		if ($stara_odluka != 0) print "</ul>\n";
		$stara_odluka = $r125[4];
		$stara_ag = $r125[5];
		$stara_inst = $r125[6];
		$q115 = db_query("select UNIX_TIMESTAMP(datum), broj_protokola from odluka where id=$stara_odluka");
		if (db_num_rows($q115) > 0)
			$odluka_ispis = " (odluka br. ".db_result($q115,0,1)." od ".date("d. m. Y.", db_result($q115,0,0)).")";
		$q127 = db_query("SELECT naziv FROM akademska_godina WHERE id=$stara_ag");
		?>
		<p>Institucija <?=$stara_inst?>, akademska <?=db_result($q127,0,0)?>. godina<?=$odluka_ispis?>:</p><ul>
		<?
	}
	print "<li><b>$r125[0]</b> - ocjena: ".$imena_ocjena[$r125[1]]."</li>\n";
}
print "</ul>";


?>

<p><b>Pregled položenih predmeta sa ocjenama</b></p>
<table width="700" border="1" cellspacing="0" cellpadding="3"><tr bgcolor="#AAAAAA">
	<td width="20">&nbsp;</td>
	<td>Naziv predmeta</td>
	<td>Akademska godina</td>
	<td>Konačna ocjena</td>
</tr>
<?

$i=1;
$q110 = db_query("SELECT p.naziv, ko.ocjena, ag.naziv, pk.semestar 
FROM konacna_ocjena as ko, ponudakursa as pk, predmet as p, student_predmet as sp, akademska_godina as ag
WHERE ko.student=$student and ko.predmet=p.id and ko.akademska_godina=ag.id and ko.predmet=pk.predmet and pk.id=sp.predmet and sp.student=$student and pk.akademska_godina=ag.id and ko.ocjena>5 order by ag.id, pk.semestar, p.naziv");
while ($r110 = db_fetch_row($q110)) {
	print "<tr><td>".($i++).".</td><td>".$r110[0]."</td><td>".$r110[2]."</td><td>".$imena_ocjena[$r110[1]]."</td></tr>\n";
}
print "</table>";

}

?>
