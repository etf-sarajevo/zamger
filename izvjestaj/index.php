<?

// IZVJESTAJ/INDEX - spisak ocjena studenta

// v3.9.1.0 (2008/04/11) + Izvjestaj izdvojen iz bivseg admin_izvjestaj.php
// v3.9.1.1 (2008/08/28) + Tabela osoba umjesto auth



function izvjestaj_index() {


// Ulazni parametar
$student = intval($_REQUEST['student']);


?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>
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
<p>&nbsp;</br>
Student:</br>
<h1><?=$r100[0]." ".$r100[1]?></h1><br/>
Broj indeksa: <?=$r100[2]?><br/><br/><br/>

<p><b>Pregled položenih predmeta sa ocjenama</b></p>
<table width="700" border="1" cellspacing="0" cellpadding="3"><tr bgcolor="#AAAAAA">
	<td width="20">&nbsp;</td>
	<td width="330">Naziv predmeta</td>
	<td width="200">Akademska godina</td>
	<td width="150">Konačna ocjena</td>
</tr>
<?

$imena_ocjena = array("Nije položio/la", "Šest","Sedam","Osam","Devet","Deset");

$i=1;
$q110 = myquery("select id,naziv from akademska_godina order by id");
while ($r110 = mysql_fetch_row($q110)) {
	$q120 = myquery("SELECT p.naziv,ko.ocjena,pk.semestar FROM `konacna_ocjena` as ko, ponudakursa as pk, predmet as p WHERE ko.student=$student and ko.predmet=pk.id and pk.predmet=p.id and pk.akademska_godina=$r110[0] order by pk.semestar,p.naziv");
	while ($r120 = mysql_fetch_row($q120)) {
		print "<tr><td>".($i++)."</td><td>".$r120[0]."</td><td>".$r110[1]."</td><td>".$r120[1]." (".$imena_ocjena[$r120[1]-5].")</td></tr>";
	}
}
print "</table>";

}

?>