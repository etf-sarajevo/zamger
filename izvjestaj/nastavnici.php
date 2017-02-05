<?


function izvjestaj_nastavnici() {

	// Parametar: akademska godina (koristiti aktuelnu ako nije data)
	$ag = $_REQUEST['ag'];
	if ($ag==0) {
		$q5 = myquery("select id from akademska_godina where aktuelna=1");
		$ag = mysql_result($q5,0,0);
	}
	
	$angazman = $predmeti = array();

	// Određivanje kojem studiju pripadaju predmeti
	$predmeti_studij = array();
	$q7 = myquery("select predmet, studij, id from ponudakursa where akademska_godina=$ag");
	while ($r7 = mysql_fetch_row($q7)) {
		$predmet = $r7[0];
		$studij = $r7[1];
		$q9 = myquery("select count(*) from student_predmet where predmet=$r7[2]");
		if (mysql_result($q9,0,0)<1) continue;
		if (empty($predmeti_studij[$studij])) $predmeti_studij[$studij]=array();
		array_push($predmeti_studij[$studij], $predmet);
		//$predmeti_studij[$predmet]=$studij;
	}
	
	$angazmani = array();
	$bilo = array();

	// Spisak angazmana
	$q10 = myquery("select o.id, o.prezime, o.ime, a.predmet from angazman as a, osoba as o where a.akademska_godina=$ag and a.osoba=o.id order by o.prezime, o.ime");
	while ($r10 = mysql_fetch_row($q10)) {
		$osoba = $r10[0];
		
		// Da li je radni odnos osobe stalni ili dopunski?
		$q20 = myquery("select dopunski, zvanje from izbor where osoba=$osoba order by datum_izbora desc");
		if (mysql_num_rows($q20)<1) {
			$dopunski = 2;
			$radni_odnos = "nepoznato";
			$idzvanja = 7;
			$zvanje = "nepoznato";
		} else {
			$dopunski = mysql_result($q20,0,0);
			if ($dopunski==0) $radni_odnos = "stalni"; else $radni_odnos="dopunski";
			$idzvanja = mysql_result($q20,0,1);
			$q30 = myquery("select naziv from zvanje where id=$idzvanja");
			if (mysql_num_rows($q30)<1) {
				$zvanje = "nepoznato";
			} else {
				$zvanje = mysql_result($q30,0,0);
			}
		}
		//print "osoba $osoba predmet $r10[3] zvanje $zvanje radni odnos $radni_odnos<br>\n";
		
		foreach ($predmeti_studij as $studij => $predmeti) {
			foreach ($predmeti as $predmet) {
				if ($predmet == $r10[3]) {
					if (empty($angazmani[$studij])) $angazmani[$studij]=array();
					if (empty($angazmani[$studij][$dopunski])) $angazmani[$studij][$dopunski] = array();
					if ($bilo["$studij-$dopunski-$idzvanja-$osoba"]) continue;
					$bilo["$studij-$dopunski-$idzvanja-$osoba"] = true;
					$angazmani[$studij][$dopunski][$idzvanja] = $angazmani[$studij][$dopunski][$idzvanja] . "<li>$r10[1] $r10[2] - $zvanje</li>\n";
				}
			}
		}
	}
	
	// Ispis
	foreach ($angazmani as $studij => $angazmani_studij) {
		$q40 = myquery("select naziv from studij where id=$studij");
		$naziv_studija = mysql_result($q40,0,0);
		print "<h3>$naziv_studija</h3>\n";

		for ($i=0; $i<=2; $i++) {
			if ($i==0) print "<b>Stalni radni odnos</b>\n<ul>\n";
			else if ($i==1) print "<b>Dopunski radni odnos</b>\n<ul>\n";
			else print "<b>Nepoznat radni odnos</b>\n<ul>\n";
			for ($j=1; $j<=7; $j++)
				print $angazmani_studij[$i][$j];
			print "</ul>\n";
		}
	}	
}
?>
