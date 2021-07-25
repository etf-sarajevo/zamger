<?

// IZVJESTAJ/SAMOFIN - Pregled studenata po broju ponavljanja godine (studenti koji gube status studenta na budžetu)


function izvjestaj_samofin() {


?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>

<p>Datum i vrijeme izvještaja: <?=date("d. m. Y. H:i");?></p>
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

$limit = intval($_REQUEST['limit']);


?>
<h2>Pregled studenata po broju ponavljanja godine</h2>

<ul>
<?

$rezultati = array();

$q10 = db_query("SELECT student, studij, semestar FROM student_studij WHERE akademska_godina=$ak_god AND semestar MOD 2 = 1 AND nacin_studiranja=1");
while ($r10 = db_fetch_row($q10)) {
	$student = $r10[0];
	$studij = $r10[1];
	$semestar = $r10[2];
	$q20 = db_query("SELECT COUNT(*) FROM student_studij WHERE student=$student AND studij=$studij AND semestar=$semestar");
	$broj_ponavljanja = db_result($q20,0,0);
	if ($broj_ponavljanja < $limit) continue;
	
	$q30 = db_query("SELECT ime, prezime, brindexa FROM osoba WHERE id=$student");
	$ime_ispis = db_result($q30,0,1)." ".db_result($q30,0,0)." (".db_result($q30,0,2).") - $semestar. semestar";
	$rezultati[$ime_ispis] = $broj_ponavljanja;
}

ksort($rezultati);

print "<ul>\n";

foreach($rezultati as $ime => $broj) {
	print "<li>$ime - $broj</li>\n";
}

print "</ul>\n";

}
