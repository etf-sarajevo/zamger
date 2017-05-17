<?

// IZVJESTAJ/ODRZANA_NASTAVA - izvještaj o održanoj nastavi nastavnika, asistenta ili demonstratora



function izvjestaj_odrzana_nastava() {

global $userid, $user_nastavnik;

require_once("lib/utility.php"); // rimski_broj


// Prava pristupa
if (!$user_nastavnik) {
	biguglyerror("Nemate pravo pristupa ovom izvještaju");
	return;
}



if ($_REQUEST['demonstratorski']) {
	$ag = intval($_REQUEST['ag']);
	if ($ag == 0) 
		$ag = intval($_REQUEST['_lv_column_akademska_godina']);
	$predmet = intval($_REQUEST['predmet']);
	if ($_REQUEST['semestar'] == "ljetnji") $semestar_genitiv="ljetnjem"; else $semestar_genitiv="zimskom";
	
	$q10 = db_query("SELECT naziv FROM akademska_godina WHERE id=$ag");
	$naziv_ag = db_result($q10,0,0);
	$q20 = db_query("SELECT naziv FROM predmet WHERE id=$predmet");
	$naziv_predmeta = db_result($q10,0,0);
	
	// Odgovorni nastavnik za predmet
	$q30 = db_query("SELECT osoba FROM angazman WHERE predmet=$predmet AND akademska_godina=$ag AND angazman_status=1"); // 1 = odgovorni nastavnik
	if (db_num_rows($q30)>0) {
		$ime_nastavnika = tituliraj(db_result($q30,0,0));
	} else {
		$ime_nastavnika = "";
	}
	
	// Ime osobe
	$q40 = db_query("SELECT ime, prezime FROM osoba WHERE id=$userid");
	$ime_demonstratora = db_result($q40,0,0)." ".db_result($q40,0,1);
	
	?>
	<p>Univerzitet u Sarajevu<br>
	Elektrotehnički fakultet Sarajevo<br>
	Br:<br>
	Sarajevo, <?=date("d. m. Y.");?></p>
	
	<p>Predmet: <b><?=$naziv_predmeta?></b></p>
	
	<p>Nastavnik: <b><?=$ime_nastavnika?></b></p>
	
	<p>Demonstrator: <b><?=$ime_demonstratora?></b></p>
	
	<h2><center>Izvještaj o nastavi u <?=$semestar_genitiv?> semestru <?=$naziv_ag?> god.<br>realiziranoj uz pomoć studenta-demonstratora</center></h2>
	
	<STYLE>
	table {
		border-collapse: collapse;
	}
	table, tr, td, th {
		border: 1px solid black;
		padding: 4px;
	}
	th {
		background: #fff;
	}
	</STYLE>

	<center><table><tr><th>R.br.</th><th>Datum<br>nastave</th><th>Broj<br>časova</th></tr>
	<?
	$q100 = db_query("SELECT c.id, UNIX_TIMESTAMP(c.datum), c.labgrupa from cas as c, labgrupa as l WHERE c.nastavnik=$userid AND c.labgrupa=l.id AND l.predmet=$predmet AND l.akademska_godina=$ag ORDER BY c.datum");
	$rbr = 0;
	while ($r100 = db_fetch_row($q100)) {
		$datum_casa = date("d. m. Y.", $r100[1]);

		$q110 = db_query("SELECT COUNT(*) FROM prisustvo WHERE cas=$r100[0] AND prisutan=1");
		$broj_studenata_casa = db_result($q110,0,0);
		
		$broj_sati = 2; // FIXME
		$rbr++;
		
		print "<tr><td>$rbr</td><td>$datum_casa</td><td>$broj_sati</td></tr>\n";
	}
	
	?>
	</table></center>
	
	<p>&nbsp;</p>
	<p>NASTAVNIK (potpis): ______________________________________</p>
	
	<p>&nbsp;</p>
	<p>ŠEF ODSJEKA (potpis): ______________________________________</p>
	
	<p>&nbsp;</p>
	<p>PRODEKAN ZA NASTAVU (potpis): ______________________________________</p>
	
	<?
	
	return 0;
}


// Ulazni parametar
$mjesec = intval($_REQUEST['mjesec']);
$ag = intval($_REQUEST['ag']);
if ($ag == 0) 
	$ag = intval($_REQUEST['_lv_column_akademska_godina']);
$odsjek = intval($_REQUEST['odsjek']);

$nazivi_mjeseci = array("", "Januar", "Februar", "Mart", "April", "Maj", "Juni", "Juli", "Avgust", "Septembar", "Oktobar", "Novembar", "Decembar");
$naziv_mjeseca = $nazivi_mjeseci[$mjesec];

$q10 = db_query("SELECT naziv FROM akademska_godina WHERE id=$ag");
if (db_num_rows($q10)<1) {
	biguglyerror("Nepoznata akademska godina");
	return;
}

$naziv_ag = db_result($q10, 0, 0);
if ($mjesec >= 9)
	$naziv_godine = substr($naziv_ag, 0, 4);
else
	$naziv_godine = substr($naziv_ag, 5);
	
$q20 = db_query("SELECT naziv FROM institucija WHERE id=$odsjek");
if (db_num_rows($q20)<1) {
	biguglyerror("Nepoznata akademska godina");
	return;
} 
$naziv_odsjeka = db_result($q20, 0, 0);

$naziv_osobe = tituliraj($userid);


?>
<p>Univerzitet u Sarajevu<br>
Elektrotehnički fakultet Sarajevo<br>
<?=$naziv_odsjeka?></p>
<p>Datum i vrijeme izvještaja: <?=date("d. m. Y. H:i");?></p>

<h2>MJESEČNI IZVJEŠTAJ O REALIZACIJI NASTAVNIH AKTIVNOSTI<br>
za mjesec: <?=strtoupper($naziv_mjeseca)?> <?=$naziv_godine?>. godine</h2>

<p>Nastavnik: <b><?=$naziv_osobe?></b></p>



<?

$poc_datum = mktime(0,0,0, $mjesec, 1, $naziv_godine);
$sljedeca_godina = $naziv_godine; $sljedeci_mjesec = $mjesec+1;
if ($mjesec == 12) { $sljedeca_godina++; $sljedeci_mjesec = 1; }
$kraj_datum = mktime(0,0,0, $sljedeci_mjesec, 1, $sljedeca_godina);

$tabela_celija = $predmeti = $ukupno_sati_predmet = array();
$ukupno_sati = 0;

$q100 = db_query("SELECT c.id, UNIX_TIMESTAMP(c.datum), c.labgrupa, p.id, p.naziv from cas as c, labgrupa as l, predmet as p WHERE c.nastavnik=$userid AND c.datum>=FROM_UNIXTIME($poc_datum) AND c.datum<FROM_UNIXTIME($kraj_datum) AND c.labgrupa=l.id AND l.predmet=p.id");
while ($r100 = db_fetch_row($q100)) {
	$datum_casa = date("d. m. Y.", $r100[1]);

	$q110 = db_query("SELECT COUNT(*) FROM prisustvo WHERE cas=$r100[0] AND prisutan=1");
	$broj_studenata_casa = db_result($q110,0,0);
	
	$broj_sati = 2;
	
	$celija = "<td>$datum_casa</td><td>$broj_studenata_casa</td><td>2</td>";

	$predmeti[$r100[3]] = $r100[4];
	if (!$tabela_celija[$r100[3]]) $tabela_celija[$r100[3]]=array();
	array_push($tabela_celija[$r100[3]], $celija);
	
	$ukupno_sati_predmet[$r100[3]] += $broj_sati;
	$ukupno_sati += $broj_sati;
}

asort($predmeti);
$broj_predmeta = count($predmeti);

?>
<table border="1" cellspacing="0" cellpadding="3">
<tr>
<?

$sumarni_tekst = "";
for ($i=1; $i<=$broj_predmeta; $i++) {
	print "	<td colspan=\"3\">".rimski_broj($i)." / PREDMET</td>\n";
	$sumarni_tekst .= " " . rimski_broj($i);
	if ($i < $broj_predmeta) $sumarni_tekst .= " +";
}
$sumarni_tekst .= " ";

print "</tr><tr>\n";

$visina_tabele = $sirina_tabele = 0;
foreach($predmeti as $id_predmeta => $naziv_predmeta) {
	print "	<td colspan=\"3\">".$naziv_predmeta."</td>\n";
	if (count($tabela_celija[$id_predmeta]) > $visina_tabele)
		$visina_tabele = count($tabela_celija[$id_predmeta]);
	$sirina_tabele += 3;
}

print "</tr><tr>\n";
foreach($predmeti as $id_predmeta => $naziv_predmeta) {
	print "<td>Datum</td><td><div class=\"rotate\">Prisutno</div></td><td><div class=\"rotate\">Br. sati</div></td>\n";
}

print "</tr>\n";

for ($i=0; $i<$visina_tabele; $i++) {
	print "<tr>\n";
	foreach($predmeti as $id_predmeta => $naziv_predmeta) {
		if ($tabela_celija[$id_predmeta][$i])
			print $tabela_celija[$id_predmeta][$i];
		else
			print "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
		print "\n";
	}
	print "</tr>\n";
}

print "<tr>\n";
foreach($predmeti as $id_predmeta => $naziv_predmeta) {
	print "<td colspan=\"2\">Ukupno sati:</td><td>".$ukupno_sati_predmet[$id_predmeta]."</td>\n";
}
print "</tr>\n";

print "<tr><td colspan=\"$sirina_tabele\">UKUPNO SATI NASTAVE   ($sumarni_tekst)  = $ukupno_sati</td></tr>\n";

print "</table>\n<p>&nbsp;</p>";

// ISPITI

$ispiti_celije = array();
foreach($predmeti as $id_predmeta => $naziv_predmeta) {
	$q200 = db_query("SELECT UNIX_TIMESTAMP(i.datum), k.gui_naziv FROM ispit as i, komponenta as k WHERE i.predmet=$id_predmeta AND i.datum>=FROM_UNIXTIME($poc_datum) AND i.datum<FROM_UNIXTIME($kraj_datum) AND i.komponenta=k.id");
	while ($r200 = db_fetch_row($q200)) 
		array_push($ispiti_celije, "<td>".date("d. m. Y.", $r200[0])."</td><td>$naziv_predmeta</td><td>$r200[1]</td>\n");
}

print "<table><tr><td rowspan=\"".count($ispiti_celije)."\">ISPITI:<br>(pismeni,<br>usmeni,<br>konsultacije)</td>";
foreach ($ispiti_celije as $celija)
	print $celija."</tr><tr>";

print "</tr></table>";

?>
<p>&nbsp;</p>
<table><tr><td>Laboratorijska nastava:</td><td>Sva laboratorijska nastava je održana u skladu sa planom i programom</td></tr>
<tr><td>Tutorijali:</td><td>Svi tutorijali su održani u skladu sa planom i programom</td></tr>
</table>


	<table border="0" width="100%">
	<tr>
	<td>
	Sarajevo, <?=date("d. m. Y.")?> godine.</td>
	<td align="center">Nastavnik:<br>
	<br>
	<br>
	<?=tituliraj($userid)?></td>
	<td align="center">Šef odsjeka:<br>
	<br>
	<br><img src="static/images/fnord.gif" height="1" width="200">
	</td>
	</tr></table>

<?




}

?>
