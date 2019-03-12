<?

// IZVJESTAJ/PONOVCI - Koliko ima studenata da su pali X predmeta



function izvjestaj_ponovci() {


?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>

<p>Datum i vrijeme izvještaja: <?=date("d. m. Y. H:i");?></p>
<?


// Akademska godina

$ag = intval($_REQUEST['ag']);
if ($ag==0)
	$ag = db_get("SELECT id FROM akademska_godina WHERE aktuelna=1");

$ponovci = db_query_table("SELECT student, studij, semestar FROM student_studij WHERE akademska_godina=$ag AND ponovac=1 AND semestar MOD 2 = 1");

$studenata = array();

$ciklusi = array();

foreach($ponovci as $p) {
	$broj_predmeta = intval(db_get("SELECT COUNT(*) FROM student_predmet sp, ponudakursa pk WHERE student=".$p['student']." AND sp.predmet=pk.id AND pk.akademska_godina=$ag AND pk.semestar<=".$p['semestar']));
	$broj_predmeta += db_get("SELECT COUNT(*) FROM student_predmet sp, ponudakursa pk WHERE sp.student=".$p['student']." AND sp.predmet=pk.id AND pk.akademska_godina=".($ag-1)." AND pk.semestar=1+".$p['semestar']." AND (SELECT COUNT(*) FROM konacna_ocjena ko WHERE ko.ocjena>5 AND ko.student=sp.student AND ko.predmet=pk.predmet)=0");
	
	$godina_studija = intval(($p['semestar']+1)/2);
	if (!array_key_exists($p['studij'], $ciklusi))
		$ciklusi[$p['studij']] = db_get("SELECT ts.ciklus FROM tipstudija ts, studij s WHERE s.id=".$p['studij']." AND s.tipstudija=ts.id");
	if ($ciklusi[$p['studij']] == 2) $godina_studija += 3;
	if ($ciklusi[$p['studij']] == 3) $godina_studija += 5;
	//print "Student ".$p['student']." predmeta $broj_predmeta gs $godina_studija<br>\n";
	
	if (!array_key_exists($godina_studija, $studenata)) $studenata[$godina_studija] = array();
	if (!array_key_exists($broj_predmeta, $studenata[$godina_studija])) $studenata[$godina_studija][$broj_predmeta] = 0;
	$studenata[$godina_studija][$broj_predmeta]++;
}

ksort($studenata);

?>
<table border="1" cellpadding="1" cellspacing="0">
<thead><tr><th rowspan="2">Godina<br>studija</th><th colspan="14" style="text-align: center;">Broj predmeta</th><th rowspan="2">UKUPNO<br>PREDMETA</th></tr>
<tr><?
for ($i=0; $i<=13; $i++) print "<th>$i</th>";
?>
</tr>
</thead>
<tbody>
<?

$totali = array();
$sum_total = 0;

foreach($studenata as $godina_studija => $brp) {
	print "<tr>";
	if ($godina_studija < 4) print "<td>$godina_studija. godina 1. ciklusa</td>";
	else if ($godina_studija < 6) print "<td>".($godina_studija-3).". godina 2. ciklusa</td>";
	else print "<td>".($godina_studija-5).". godina 3. ciklusa</td>";
	
	$uk_predmeta = 0;
	for ($i=0; $i<=13; $i++) {
		if (!array_key_exists($i, $brp)) $brp[$i]=0;
		print "<td>".$brp[$i]."</td>";
		$uk_predmeta += $brp[$i] * $i;
		$totali[$i] += $brp[$i];
		$sum_total += $brp[$i] * $i;
	}
	print "<td>$uk_predmeta</td></tr>\n";
}



?>
<tr><th>UKUPNO:</th>
<?
for ($i=0; $i<=13; $i++) print "<th>".$totali[$i]."</th>";
?>
<th><?=$sum_total?></th></tr>
</tbody></table>
<?


}

