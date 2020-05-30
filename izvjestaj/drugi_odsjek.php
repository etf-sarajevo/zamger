<?

// IZVJESTAJ/DRUGI_ODSJEK - spisak izbornih predmeta sa drugog odsjeka koji su izabrani u tekućoj ak.god./semestru



function izvjestaj_drugi_odsjek() {

require_once("lib/plan_studija.php"); 

$warn_about_wrong_pk = false;

?>
	<p>Univerzitet u Sarajevu<br>
	Elektrotehnički fakultet Sarajevo</p>
<h2>Predmeti sa drugih odsjeka</h2>
<h3>koje su studenti slušali u akademskoj <?=db_get("SELECT naziv FROM akademska_godina WHERE aktuelna=1")?> godini</h3>
<p>Datum i vrijeme izvještaja: <?=date("d. m. Y. H:i");?></p>
<?

$ag = db_get("SELECT id FROM akademska_godina WHERE aktuelna=1");


$q100 = db_query("SELECT DISTINCT pk.id, ss.studij, pk.predmet, pk.studij, pk.semestar FROM ponudakursa pk, student_predmet sp, student_studij ss WHERE sp.predmet=pk.id AND pk.akademska_godina=$ag AND sp.student=ss.student AND ss.akademska_godina=$ag AND ss.semestar MOD 2 = pk.semestar MOD 2");
$pkovi = $predmeti = $semestri = array();
$planovi_studija = array();
while(db_fetch5($q100, $pk, $studij, $predmet, $pkstudij, $pksemestar)) {
	$pkovi[$studij][] = $pk;
	$predmeti[$studij][] = $predmet;
	$planovi_studija[$studij] = array();
	$semestri["$studij-$predmet"] = $pksemestar;
	
	if ($warn_about_wrong_pk && $pkstudij != $studij) {
		print "Warnung pk $pk predmet $predmet studij $studij pkstudij $pkstudij<br>";
		$studenti = db_query_table("SELECT o.id, o.ime, o.prezime FROM osoba o, student_predmet sp, student_studij ss WHERE o.id=sp.student AND sp.predmet=$pk and sp.student=ss.student AND ss.studij=$studij AND ss.akademska_godina=$ag");
		print_r($studenti);
	}
}

foreach($planovi_studija as $studij => $dummy) {
	$plan_studija = db_get("SELECT id FROM plan_studija WHERE studij=$studij AND godina_vazenja<$ag AND godina_vazenja IS NOT NULL ORDER BY godina_vazenja DESC");
	//print "Studij $studij Plan $plan_studija<br>";
	if ($plan_studija) $planovi_studija[$studij] = predmeti_na_planu($plan_studija);
}


foreach($predmeti as $studij => $predmetstudij) {
	$printed = false;
	//print_r($predmetstudij); print"<br><br>";
	foreach($predmetstudij as $predmet) {
		$found = false;
		foreach($planovi_studija[$studij] as $ps) {
			if ($ps['obavezan'] == 1 && $ps['predmet']['id'] == $predmet) $found = true; 
			else foreach($ps['predmet'] as $izborni)
				if ($izborni['id'] == $predmet) $found=true;
		}
		if (!$found && !$printed) {
			print "</p><p>Studenti na studiju: <b>" . db_get("SELECT naziv FROM studij WHERE id=$studij") . "</b> su slušali sljedeće predmete:<br><br>";
			$printed = true;
		}
		if (!$found) print db_get("SELECT naziv FROM predmet WHERE id=$predmet") . " (".$semestri["$studij-$predmet"].". semestar)<br>\n";
	}
}

/*
$studiji = db_query_varray("SELECT DISTINCT studij FROM student_studij WHERE akademska_godina=$ag");

$planovi_studija = array();
foreach($studiji as $studij) $planovi_studija[$studij] = predmeti_na_planu($studij);

foreach($pks as $pk) {
	foreach($planovi_studija as $predmet) {
		
	}
	print db_get("SELECT p.naziv FROM predmet p, ponudakursa pk WHERE pk.id=$pk AND pk.predmet=p.id") . "<br>";
}
*/


}

?>
