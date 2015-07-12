<?

// IZVJESTAJ/ZAVRSNI_SPISAK - Spisak završenih studenata na ciklusu studija (za promociju)


function izvjestaj_zavrsni_spisak() {

?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>
<p>Datum i vrijeme izvještaja: <?=date("d. m. Y. H:i");?></p>
<?


$ciklus = intval($_REQUEST['ciklus']);
if ($ciklus == 1) {
	$ciklus_genitiv = "prvog";
	
} else if ($ciklus == 2) {
	$ciklus_genitiv = "drugog";
} else {
	// Za sada samo 1. i 2. ciklus
	biguglyerror("Neispravan ciklus");
	return;
}

$ag = intval($_REQUEST['ag']);
$q10 = myquery("SELECT naziv FROM akademska_godina WHERE id=$ag");
if (mysql_num_rows($q10) != 1) {
	biguglyerror("Neispravna akademska godina");
	return;
}
$ag_naziv = mysql_result($q10,0,0);

?>
<h2>Spisak završenih studenata <?=$ciklus_genitiv?> ciklusa studija u akademskoj <?=$ag_naziv?> godini</h2>
<p><b>Naziv VŠU: Elektrotehnički fakultet Sarajevo</b></p>

<table border="1" cellspacing="0" cellpadding="2">
<tr><th>Red. br</th><th>Prezime (ime roditelja) i ime</th><th>Datum stjecanja diplome</th><th>Stručni naziv</th><th>Broj diplome<br>Izdata prijevremeno</th></tr>
<?


$q15 = myquery("SELECT naziv FROM strucni_stepen WHERE id=".(3-$ciklus));
$strucni_naziv = mysql_result($q15,0,0);


$q20 = myquery("SELECT o.ime, o.imeoca, o.imemajke, o.prezime, UNIX_TIMESTAMP(ko.datum_u_indeksu), z.broj_diplome 
FROM osoba as o, konacna_ocjena as ko, predmet as p, student_studij as ss, studij as s, tipstudija as ts, zavrsni as z, akademska_godina_predmet as agp
WHERE o.id=ko.student AND ko.predmet=p.id AND ko.akademska_godina=$ag AND ko.ocjena>5 AND o.id=ss.student AND ss.studij=s.id AND ss.akademska_godina=$ag AND ss.semestar mod 2=0 AND s.tipstudija=ts.id AND ts.ciklus=$ciklus AND z.student=o.id AND z.predmet=p.id AND z.akademska_godina=$ag AND agp.akademska_godina=$ag AND agp.predmet=p.id AND agp.tippredmeta=1000
ORDER BY o.prezime, o.ime"); // 1000 = tip predmeta "Završni rad"

$rbr=0;
while ($r20 = mysql_fetch_row($q20)) {
	$ime_roditelja = $r20[1];
	if ($r20[1]=="" || $r20[1]=="nepoznato" || $r20[1]=="Nepoznato")
		$ime_roditelja=$r20[2];
	$ime = "$r20[3] ($ime_roditelja) $r20[0]";
	$datum = date("d. m. Y", $r20[4]);
	$rbr++;
	$broj_diplome = $r20[5];
	if ($broj_diplome == "") $broj_diplome = "&nbsp;";

	?>
	<tr><td><?=$rbr?>.</td><td><?=$ime?></td><td><?=$datum?></td><td><?=$strucni_naziv?></td><td><?=$broj_diplome?></td></tr>
	<?
}


?>
</table>
<?

return;

}
