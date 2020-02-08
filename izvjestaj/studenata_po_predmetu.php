<?

// IZVJESTAJ/STUDENATA_PO_PREDMETU - Broj studenata po predmetu (tabelarno)



function izvjestaj_studenata_po_predmetu() {


?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>

<p>Datum i vrijeme izvještaja: <?=date("d. m. Y. H:i");?></p>

<h2>Broj upisanih studenata po predmetu</h2>
<?


// Akademska godina

$ak_god = intval($_REQUEST['akademska_godina']);
if ($ak_god==0) {
	// Aktuelna godina
	$q10 = db_query("select id, naziv from akademska_godina where aktuelna=1");
	$ak_god = db_result($q10,0,0);
	$ak_god_naziv = db_result($q10,0,1);
} else {
	$q10 = db_query("select naziv from akademska_godina where id=$ak_god");
	$ak_god_naziv = db_result($q10,0,0);
}


$result = array();

// Spisak studija
$nazivi_studija = db_query_vassoc("SELECT id, naziv FROM studij");

// Spisak ponuda kursa u ag
$q5 = db_query("SELECT pk.id id, p.id predmet, p.naziv naziv, pk.studij, pk.semestar, pk.obavezan, p.institucija FROM ponudakursa pk, predmet p WHERE pk.akademska_godina=$ak_god AND pk.predmet=p.id ORDER BY pk.studij, pk.semestar");
while(db_fetch7($q5, $pk, $predmet, $naziv_predmeta, $studij, $semestar, $obavezan, $institucija)) {
	if ($semestar == 1 && $studij>=2 && $studij<=5) $studij=1; // Hack za zajedničku prvu godinu studija
	else if ($semestar == 2 && $studij>=2 && $studij<=5) $studij=1; // Hack za zajedničku prvu godinu studija
	else {
		if ($studij >= 2 && $studij <= 5) $studij = $institucija;
		else if ($studij >= 7 && $studij <= 10) $studij = $institucija + 5;
	}

	if (!array_key_exists($studij, $result)) 
		$result[$studij] = array();
	if (!array_key_exists($semestar, $result[$studij])) 
		$result[$studij][$semestar] = array();
	if (!array_key_exists($naziv_predmeta, $result[$studij][$semestar])) 
		$result[$studij][$semestar][$naziv_predmeta] = array("prvoupisanih" => 0, "ponovaca" => 0, "ukupno" => 0);
	
	$q10 = db_query("SELECT student FROM student_predmet WHERE predmet=$pk");
	while(db_fetch1($q10, $student)) {
		$ponovac = db_get("SELECT COUNT(*) FROM student_predmet sp, ponudakursa pk WHERE sp.student=$student AND sp.predmet=pk.id AND pk.akademska_godina<$ak_god AND pk.predmet=$predmet");
		if ($ponovac > 0)
			$result[$studij][$semestar][$naziv_predmeta]["ponovaca"]++;
		else
			$result[$studij][$semestar][$naziv_predmeta]["prvoupisanih"]++;
		$result[$studij][$semestar][$naziv_predmeta]["ukupno"]++;
	}
}

//print_r($result);


foreach($result as $studij => $r1) {
	ksort($r1);
	foreach($r1 as $semestar => $r2) {
		ksort($r2);
	
		?>
		<h3> <?=$nazivi_studija[$studij]?>, <?=$semestar?>. semestar</h3>
		<table><thead><tr><th>Naziv predmeta</th><th>Prvoupisanih</th><th>Ponovaca</th><th>Ukupno</th></tr></thead>
		<tbody>
		<?
		
		foreach($r2 as $predmet => $r3) {
			?>
			<tr><td><?=$predmet?></td><td><?=$r3['prvoupisanih']?></td><td><?=$r3['ponovaca']?></td><td><?=$r3['ukupno']?></td></tr>
			<?
		}
		
		?>
		</tbody>
		</table>
		<?
	}
}


}

