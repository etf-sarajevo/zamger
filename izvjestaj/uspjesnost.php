<?

// IZVJESTAJ/USPJESNOST - Uspješnost studenata i prosječno trajanje studija



function izvjestaj_uspjesnost() {

require_once("lib/utility.php"); // procenat


?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>
<p>Datum i vrijeme izvještaja: <?=date("d. m. Y. H:i");?></p>

<h2>Uspješnost studenata i prosječno trajanje studija</h2>
<?

// Parametar izvještaja
$studij = intval($_REQUEST['studij']);
if ($studij==0) $studij = intval($_REQUEST['_lv_column_studij']);

$q10 = db_query("SELECT s.naziv, ts.id, ts.trajanje, ts.ects FROM studij as s, tipstudija as ts WHERE s.id=$studij AND s.tipstudija=ts.id");
if (db_num_rows($q10)<1) {
	biguglyerror("Nepostojeći studij");
	return;
}

$tipstudija = db_result($q10,0,1);
$trajanje_studija = db_result($q10,0,2); // u semestrima
$ects_studija = db_result($q10,0,2);

?>
<h3><?=db_result($q10,0,0)?></h3>


<table>
<tr>
	<th>&nbsp;</th>
	<th>Upisalo<br> studij</th>
	<th>Završilo<br> studij</th>
	<th>Još uvijek<br> studira</th>
	<th>Odustalo od<br> studija</th>
	<th>Prosječno trajanje<br>(godina)</th>
</tr>
<?

$q20 = db_query("SELECT ss.student, ss.akademska_godina, ss.semestar, ss.studij FROM student_studij AS ss, studij AS s WHERE ss.studij=s.id AND s.tipstudija=$tipstudija AND ss.nacin_studiranja!=6 ORDER BY ss.akademska_godina, ss.semestar"); // nacin studiranja 6 = mobilnost
$student_pocetna_godina = $student_krajnja_godina = $student_krajnji_semestar = $student_studij = array();
$maxgodina = 0;
while ($r20 = db_fetch_row($q20)) {
	$student_krajnja_godina[$r20[0]] = $r20[1];
	$student_krajnji_semestar[$r20[0]] = $r20[2];
	if (!isset($student_pocetna_godina[$r20[0]])) $student_pocetna_godina[$r20[0]] = $r20[1];
	$maxgodina = $r20[1];
	$student_studij[$r20[0]] = $r20[3]; // Zbog promjene odsjeka bitno je u kojoj godini je završio
}

$godina_upisalo = $godina_zavrsilo = $godina_studira = $godina_odustalo = $godina_suma_trajanja = array();

foreach ($student_pocetna_godina as $student => $pocetna_godina) {
	if ($student_studij[$student] != $studij) continue;
	$godina_upisalo[$pocetna_godina]++;

	// Studenti koji su potencijalno završili studij
	if ($student_krajnji_semestar[$student] == $trajanje_studija) {
		// Da bismo odredili da li je student završio studij, provjerićemo da li ima min. 180 ECTS kredita u zbiru
		// Koristimo početnu i krajnju godinu da eliminišemo predmete sa drugog ciklusa
		$q30 = db_query("SELECT SUM(p.ects) FROM predmet AS p, konacna_ocjena AS ko WHERE ko.student=$student AND ko.predmet=p.id AND ko.akademska_godina>=$pocetna_godina AND ko.akademska_godina<=".$student_krajnja_godina[$student]); 
		if (db_result($q30,0,0) >= $ects_studija) {
			$godina_zavrsilo[$pocetna_godina]++;
			$godina_suma_trajanja[$pocetna_godina] += ($student_krajnja_godina[$student] - $pocetna_godina + 1);
		} else if ($student_krajnja_godina[$student] == $maxgodina) {
			$godina_studira[$pocetna_godina]++;
		} else {
			$godina_odustalo[$pocetna_godina]++;
		}
	}

	else if ($student_krajnja_godina[$student] == $maxgodina) {
		$godina_studira[$pocetna_godina]++;
	} else {
		$godina_odustalo[$pocetna_godina]++;
	}
}


// Ispisujemo tabelu samo za one akademske godine u kojima ima završenih studenata
$suma_upisalo = $suma_zavrsilo = $suma_studira = $suma_odustalo = $suma_trajanje = 0;
foreach ($godina_zavrsilo as $godina => $zavrsilo) {
	$prosjecno_trajanje = round($godina_suma_trajanja[$godina] / $zavrsilo, 2);
	$upisalo = $godina_upisalo[$godina]; // shortcut

	$q40 = db_query("SELECT naziv FROM akademska_godina WHERE id = $godina");
	?>
	<tr>
		<td><?=db_result($q40,0,0)?></td>
		<td><?=$upisalo?></td>
		<td><?=$zavrsilo?> (<?=procenat($zavrsilo, $upisalo)?>)</td>
		<td><?=$godina_studira[$godina]?> (<?=procenat($godina_studira[$godina], $upisalo)?>)</td>
		<td><?=$godina_odustalo[$godina]?> (<?=procenat($godina_odustalo[$godina], $upisalo)?>)</td>
		<td><?=$prosjecno_trajanje?></td>
	</tr>
	<?

	$suma_upisalo += $godina_upisalo[$godina];
	$suma_zavrsilo += $zavrsilo;
	$suma_studira += $godina_studira[$godina];
	$suma_odustalo += $godina_odustalo[$godina];
	$suma_trajanje += $godina_suma_trajanja[$godina];
}

$uk_prosjek_trajanja = round($suma_trajanje / $suma_zavrsilo, 2);

?>
<tr>
	<td>UKUPNO</td>
	<td><?=$suma_upisalo?></td>
	<td><?=$suma_zavrsilo?> (<?=procenat($suma_zavrsilo, $suma_upisalo)?>)</td>
	<td><?=$suma_studira?> (<?=procenat($suma_studira, $suma_upisalo)?>)</td>
	<td><?=$suma_odustalo?> (<?=procenat($suma_odustalo, $suma_upisalo)?>)</td>
	<td><?=$uk_prosjek_trajanja?></td>
</tr>
</table>
<?


}
