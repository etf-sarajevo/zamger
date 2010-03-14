<?


function izvjestaj_ugovoroucenju() {


?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>
<h2>Broj studenata po izbornom predmetu<h2>
<?

//Aktuelna godina
$q5 = myquery("select id from akademska_godina where aktuelna=1");
$ag = mysql_result($q5,0,0);

$q10 = myquery("select distinct ag.id, s.id, s.naziv from plan_studija as ps, akademska_godina as ag, studij as s where ps.godina_vazenja=ag.id and ps.studij=s.id order by ps.godina_vazenja, s.naziv");
while ($r10 = mysql_fetch_row($q10)) {
	$godina = $r10[0];
	$studij = $r10[1];
	print "<h3>$r10[2]</h3>\n";

	$q20 = myquery("select distinct semestar from plan_studija where godina_vazenja=$godina and studij=$studij order by semestar");
	while ($r20 = mysql_fetch_row($q20)) {
		// Preskacemo neparne semestre
		// if ($semestar%2!=0) continue;

		$semestar = $r20[0];
		print "<p><b>$semestar. semestar</b>\n";

		// Ugovori o ucenju
		$q30 = myquery("select MAX(id), student from ugovoroucenju WHERE akademska_godina=$ag and studij=$studij and semestar=$semestar group by student");
		print " - ukupno ugovor popunilo ".mysql_num_rows($q30)." studenata";

		// Od koliko mogućih?
		if ($semestar%2==1) $newsem=$semestar; else $newsem=$semestar-1;
		$q35 = myquery("select count(*) from student_studij where studij=$studij and semestar=$newsem and akademska_godina=$ag");
		print " - od ".mysql_result($q35,0,0)."<br/>\n";

		$predmeti=array();
		while ($r30 = mysql_fetch_row($q30)) {
			$uou = $r30[0];
			$q40 = myquery("select predmet from ugovoroucenju_izborni where ugovoroucenju=$uou");
			while ($r40 = mysql_fetch_row($q40)) {
				$predmeti[$r40[0]]++;
			}
		}
		if (count($predmeti)==0) {
			print "  -- Nema izbornih predmeta.</p>\n";
		} else {
			print '<table border="0" width="400">
			<tr bgcolor="#CCCCCC"><td>Naziv predmeta</td><td>Studenata</td></tr>';
		}
		foreach ($predmeti as $pid => $broj) {
			$q50 = myquery("select naziv from predmet where id=$pid");
			print "<tr><td>".mysql_result($q50,0,0)."</td><td>$broj</td></tr>\n";
		}
		if (count($predmeti)>0)
			print "</table>\n";
	}

	print "</p>\n";
}

	

}

?>