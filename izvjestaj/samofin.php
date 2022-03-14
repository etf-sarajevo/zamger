<?

// IZVJESTAJ/SAMOFIN - Pregled po broju ponavljanja godine


function izvjestaj_samofin() {

	
	?>
	<p>Univerzitet u Sarajevu<br/>
	Elektrotehnički fakultet Sarajevo</p>
	
	<p>Datum i vrijeme izvještaja: <?=date("d. m. Y. H:i");?></p>
	<?
	
	// Akademska godina
	
	$ak_god = int_param('akademska_godina');
	if ($ak_god==0) {
		// Aktuelna godina
		$q10 = db_query("select id, naziv from akademska_godina where aktuelna=1");
		$ak_god = db_result($q10,0,0);
		$ak_god_naziv = db_result($q10,0,1);
	} else {
		$q10 = db_query("select naziv from akademska_godina where id=$ak_god");
		$ak_god_naziv = db_result($q10,0,0);
	}
	
	$sql_add = "";
	$limit = int_param('limit');
	$ukljuci_samofin = int_param('ukljuci_samofin');
	if ($ukljuci_samofin == 0)
		$sql_add = " AND nacin_studiranja=1 ";
	$ukljuci_apsolvente = int_param('ukljuci_apsolvente');
	if ($ukljuci_samofin == 0)
		$sql_add .= " AND status_studenta=0 ";
	
	
	// Studiji semestara
	//$trajanja = db_query_varray("SELECT s.id, ts.trajanje FROM studij s, tipstudija ts WHERE s.tipstudija=ts.id");
	
	?>
	<h2>Pregled studenata po broju ponavljanja godine</h2>
	<p>Akademska <?=$ak_god_naziv?> godina.</p>
	<p>Pored imena studenta je navedeno koliko puta je student sluša istu godinu studija<? if ($ukljuci_samofin == 0) { ?> (ne računajući apsolventski staž)<? } ?>.</p>
	<? if ($ukljuci_samofin == 0) { ?><p>Navedeni su samo redovni studenti (bez samofinansirajućih).</p><? } ?>
	<p>Limit: minimalno <?=$limit?>. put sluša godinu</p>
	<?
	
	//$rezultati = array();
	
	$q10 = db_query("SELECT student, studij, semestar, put FROM student_studij WHERE akademska_godina=$ak_god AND semestar MOD 2 = 1 $sql_add");
	while (db_fetch4($q10, $student, $studij, $semestar, $puta)) {
		$broj_ponavljanja = db_get("SELECT COUNT(*) FROM student_studij WHERE student=$student AND studij=$studij AND semestar=$semestar AND status_studenta=0");
		if ($broj_ponavljanja < $limit) continue;
		
		$q30 = db_query("SELECT ime, prezime, brindexa FROM osoba WHERE id=$student");
		db_fetch3($q30, $ime, $prezime, $brindexa);
		$ime_ispis = "$prezime $ime ($brindexa)";
		$rezultati[$ime_ispis] = $broj_ponavljanja;
	}
	
	ksort($rezultati);
	
	print "<ul>\n";
	
	foreach($rezultati as $ime => $broj) {
		print "<li>$ime - $broj. put</li>\n";
	}
	
	print "</ul>\n";
}
