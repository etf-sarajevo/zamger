<?


// IZVJESTAJ/DODATNI_PODACI - Dodatni podaci o studentima na predmetu

function izvjestaj_dodatni_podaci() {

global $userid,$user_nastavnik,$user_studentska,$user_siteadmin;


?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>
<p>Datum i vrijeme izvještaja: <?=date("d. m. Y. H:i");?></p>
<?

$predmet = int_param('predmet');
$ag = int_param('ag');

if (!$user_studentska && !$user_siteadmin) {
	$q2 = db_query("select nivo_pristupa from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
	if (db_num_rows($q2) < 1) {
		biguglyerror("Nemate permisije za pristup ovom izvještaju");
		zamgerlog ("nije admin predmeta pp$predmet, godina ag$ag",3); // 3 = error
		zamgerlog2 ("nije saradnik na predmetu", $predmet, $ag); // 3 = error
		return;
	}
}

$naziv_predmeta = db_get("SELECT naziv FROM predmet WHERE id=$predmet");
if (!$naziv_predmeta) {
	biguglyerror("Nepoznat predmet");
	zamgerlog ("nepoznat predmet $predmet", 3);
	zamgerlog2 ("nepoznat predmet", $predmet);
	return;
}

$pasos_predmeta = db_get("SELECT pasos_predmeta FROM akademska_godina_predmet WHERE akademska_godina=$ag AND predmet=$predmet");
if ($pasos_predmeta) {
	$naziv_predmeta = db_get("SELECT naziv FROM pasos_predmeta WHERE id=$pasos_predmeta");
}

$naziv_ag = db_get("SELECT naziv FROM akademska_godina WHERE id=$ag");
if (!$naziv_ag) {
	biguglyerror("Nepoznata akademska godina");
	zamgerlog ("nepoznat akademska godina $ag", 3);
	zamgerlog2 ("nepoznat akademska godina", $ag);
	return;
}

?>
	<p>&nbsp;</p>
	<h1><?=$naziv_predmeta?> <?=$naziv_ag?></h1>
	<h3>Dodatni podaci o upisanim studentima</h3>
	<p>Da biste filtrirali ili sortirali ovaj izvještaj po raznim kriterijima, predlažemo da ga importujete u Excel.</p>
	
<table>
<thead>
	<tr>
		<th>R.br</th><th>Prezime i ime</th><th>Broj indeksa</th><th>Studij</th><th>Semestar</th><th>Koji put</th><th>Status</th>
	</tr>
</thead>
<tbody>
<?

$q10 = db_query("SELECT sp.student, o.ime, o.prezime, o.brindexa, s.kratkinaziv, ss.semestar, ss.ponovac, ss.status_studenta
	FROM student_predmet sp, ponudakursa pk, student_studij ss, osoba o, studij s
	WHERE sp.student=o.id AND sp.predmet=pk.id AND pk.predmet=$predmet AND pk.akademska_godina=$ag AND ss.student=sp.student AND ss.akademska_godina=$ag AND ss.semestar MOD 2 = pk.semestar MOD 2 AND ss.studij=s.id
	ORDER BY o.prezime, o.ime");
$rbr = 1;
while (db_fetch8($q10, $student, $ime, $prezime, $brindexa, $studij, $semestar, $ponovac, $apsolvent)) {
	
	if ($apsolvent) $status="Apsolvent";
	else if($ponovac) $status="Ponovac";
	else $status="Redovan";
	
	$put = db_get("SELECT COUNT(*) FROM student_predmet sp, ponudakursa pk WHERE sp.student=$student AND sp.predmet=pk.id AND pk.predmet=$predmet AND pk.akademska_godina<=$ag");
	
	?>
	<tr>
		<td><?=$rbr?></td><td><?=$prezime?> <?=$ime?></td><td><?=$brindexa?></td><td><?=$studij?></td><td><?=$semestar?></td><td><?=$put?></td><td><?=$status?></td>
	</tr>
	<?
	$rbr++;
}

?>
</table>
<?

}

function db_fetch8($res, &$a, &$b, &$c, &$d, &$e, &$f, &$g, &$h) { $r = db_fetch_row($res); if ($r) { $a=$r[0]; $b=$r[1]; $c=$r[2]; $d=$r[3]; $e=$r[4]; $f=$r[5]; $g=$r[6]; $h=$r[7]; } return $r; }