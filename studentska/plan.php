<?

// STUDENTSKA/PLAN - Nastavni plan studija

// v4.0.9.1 (2009/05/20) + Novi modul


function studentska_plan() {


$godina=intval($_REQUEST['godina']);
$studij=intval($_REQUEST['studij']);


?><h2>Nastavni plan studija</h2><?

// Spisak planova
if ($godina==0 || $studij==0) {

?>
<p>Planovi, po godinama usvajanja (početka važenja):<br/>
<?

$q10 = myquery("select distinct ag.id, ag.naziv, s.id, s.naziv from plan_studija as ps, akademska_godina as ag, studij as s where ps.godina_vazenja=ag.id and ps.studij=s.id order by ps.godina_vazenja, s.naziv");
while ($r10 = mysql_fetch_row($q10)) {
	?>- <a href="?sta=studentska/plan&godina=<?=$r10[0]?>&studij=<?=$r10[2]?>"><?=$r10[3]?> (<?=$r10[1]?>)</a><br/><?
}

print "</p>\n";
return;
}

// Nazivi
$q15=myquery("select naziv from studij where id=$studij");
$q20=myquery("select naziv from akademska_godina where id=$godina");

print "<h3>".mysql_result($q15,0,0)." (".mysql_result($q20,0,0).")</h3>\n";



$q20 = myquery("select distinct semestar from plan_studija where godina_vazenja=$godina and studij=$studij order by semestar");
$space = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
while ($r20 = mysql_fetch_row($q20)) {
	$semestar=$r20[0];
	print $space."<b>".$semestar.". semestar</b><br/>\n";

	$q30 = myquery("select p.id, p.naziv, p.sifra, p.ects from predmet as p, plan_studija as ps where ps.godina_vazenja=$godina and ps.studij=$studij and ps.semestar=$semestar and ps.obavezan=1 and ps.predmet=p.id order by p.naziv");
	while ($r30 = mysql_fetch_row($q30)) {
		print $space.$space."<a href=\"?sta=studentska/predmeti&akcija=edit&predmet=$r30[0]\">$r30[1]</a> ($r30[2]), $r30[3] ECTS<br/>\n";
	}

	$q40 = myquery("select predmet, count(predmet) from plan_studija where godina_vazenja=$godina and studij=$studij and semestar=$semestar and obavezan=0 group by predmet order by predmet");
	$count=1;
	while ($r40 = mysql_fetch_row($q40)) {
		print $space.$space."Izborni predmet $count";
		if ($r40[1]>1) {
			for ($i=2; $i<=$r40[1]; $i++) {
				print " i ".($count+$i-1);
			}
			$count = $count+$r40[1]-1;
		} else $count++;
		print "<br/>\n";

		$q50 = myquery("select p.id, p.naziv, p.sifra, p.ects from predmet as p, izborni_slot as iz where iz.id=$r40[0] and iz.predmet=p.id order by p.naziv");
		while ($r50 = mysql_fetch_row($q50)) {
			print $space.$space.$space."<a href=\"?sta=studentska/predmeti&akcija=edit&predmet=$r50[0]\">$r50[1]</a> ($r50[2]), $r50[3] ECTS<br/>\n";
		}
	}
}


}

?>
