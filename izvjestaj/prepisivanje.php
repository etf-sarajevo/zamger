<?

// IZVJESTAJ/PREPISIVANJE



function izvjestaj_prepisivanje() {

$predmet = int_param('predmet');
$ag = int_param('ag');

$make_gt = true;
$abbrev = "ASP2017";

$preskakanje = array("Tutorijal", "Bonus", "Prigovor", "Dopuna");

?>
<h2>Izvještaj o prepisivanju</h2>
<h3>Predmet: <?=db_get("SELECT naziv FROM predmet WHERE id=$predmet");?><br>
Akademska godina: <?=db_get("SELECT naziv FROM akademska_godina WHERE id=$ag");?></h3>
<?

$zadace = db_query_table("SELECT id, naziv, zadataka FROM zadaca WHERE predmet=$predmet AND akademska_godina=$ag");

$max_zadataka = 0;
foreach($zadace as $id => $zadaca) {
	$preskoci = false;
	foreach($preskakanje as $p)
		if (strstr($zadaca['naziv'], $p)) $preskoci = true;
	if ($preskoci) unset($zadace[$id]);
	else if ($zadaca['zadataka'] > $max_zadataka) $max_zadataka = $zadaca['zadataka'];
}
?>

<table>
<thead>
<tr><th rowspan="2">ZADAĆA</th>
<?
for ($i = 1; $i <= $max_zadataka; $i++)
	print "<th colspan=\"5\">Zadatak $i</th>\n";
?>
</tr>
<tr>
<?
for ($i = 1; $i <= $max_zadataka; $i++)
	print "<th>Total</th><th>Prep.</th><th>100%</th><th>Odb.</th><th>Fin.prep.</th>";
?>
</tr></thead>
<?

$ord = 0;
foreach($zadace as $zadaca) {
	$ord++;
	?>
	<tr><td><?=$zadaca['naziv']?></td>
	<?
	for ($i=1; $i<=$zadaca['zadataka']; $i++) {
if ($make_gt) print "- $abbrev/Z$ord/Z$i<br>\n";
$gt_output = "";
		$total = db_get("SELECT COUNT(DISTINCT student) FROM zadatak WHERE zadaca=" . $zadaca['id'] . " AND redni_broj=$i");
		$studenti = db_query_varray("SELECT DISTINCT student FROM zadatak WHERE zadaca=" . $zadaca['id'] . " AND redni_broj=$i AND status=2");
		print "<td>$total</td><td>".count($studenti)."</td>\n";
		$stoposto = $odbranilo = 0;
		foreach ($studenti as $student) {
//print "SELECT status, komentar FROM zadatak WHERE zadaca=" . $zadaca['id'] . " AND redni_broj=$i AND student=$student ORDER BY id DESC LIMIT 1<br>";
			$q10 = db_query("SELECT status, komentar FROM zadatak WHERE zadaca=" . $zadaca['id'] . " AND redni_broj=$i AND student=$student ORDER BY id DESC LIMIT 1");
			db_fetch2($q10, $status, $komentar);
//print "status $status komentar $komentar<br>\n";
			if ($make_gt) {
				//$gt_output .= db_get("SELECT login FROM auth WHERE id=$student") . " ($status) $komentar<br>\n";
				$gt_output .= db_get("SELECT login FROM auth WHERE id=$student") . " $komentar";
				if (intval($status) != 2) $gt_output .= " - ODBR!";
				$gt_output .= "<br>\n";
			}
			if (intval($status) != 2) { $odbranilo++; }
			else if (strstr($komentar, "100%") || ($predmet==2 && $ag>8)) $stoposto++;
		}
		print "<td>$stoposto</td><td>$odbranilo</td>";
		print "<td>". round(100*(count($studenti)-$odbranilo)/$total, 2) . "%</td>\n";
if ($make_gt) print $gt_output;
	}
	print "</tr>\n";
}

?>
</table>

<p>Legenda:<br>
- <b>Total:</b> Ukupno poslalo zadaću<br>
- <b>Prep:</b> Prvobitno označeni kao prepisani<br>
- <b>100%:</b> Nisu imali mogućnost odbrane<br>
- <b>Odb.:</b> Odbranili zadaću<br>
- <b>Fin.prep.:</b> Procenat prepisivanja - finalno stanje</p>

<?




} // function izvjestaj_prepisivanje()

?>
