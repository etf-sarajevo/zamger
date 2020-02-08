<?

// IZVJESTAJ/HISTORIJA - historija jednog studenta



function izvjestaj_historija() {

require_once("lib/utility.php"); // spol


// Ulazni parametar
$student = intval($_REQUEST['student']);


?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>
<p>Datum i vrijeme izvještaja: <?=date("d. m. Y. H:i");?></p>
<?

// Podaci o studentu
$q100 = db_query("select ime,prezime,brindexa from osoba where id=$student");
if (!($r100 = db_fetch_row($q100))) {
	biguglyerror("Student se ne nalazi u bazi podataka.");
	zamgerlog("nepoznat ID $student",3); // 3 = greska
	zamgerlog2("nepoznat id korisnika", $student); // 3 = greska
	return;
}
/*if ($r100[3] != 1) {
	biguglyerror("Nepoznat student");
	zamgerlog("korisnik u$student nema status studenta",3);
	return;
}*/


?>
<p>&nbsp;</br>
Student:</br>
<h1><?=$r100[0]." ".$r100[1]?></h1>
Broj indeksa: <?=$r100[2]?><br/></p>

<h2>Historija studenta</h2>
<?

// Spol
if (spol($r100[0])=="Z") {
	$upisa="Upisala";
	$polozi="položila";
	$ppolozi="Položila";
	$izasa="Izašla";
} else {
	$upisa="Upisao";
	$polozi="položio";
	$ppolozi="Položio";
	$izasa="Izašao";
}


// Glavni upit su akademske godine
$q10 = db_query("select id,naziv from akademska_godina order by id");
while ($r10 = db_fetch_row($q10)) {
	$ag = $r10[0];
	$agnaziv = $r10[1];

	// Prijemni ispit
	$q15 = db_query("SELECT s.naziv, uus.opci_uspjeh, uus.kljucni_predmeti, uus.dodatni_bodovi, pp.rezultat, UNIX_TIMESTAMP(pt.datum)
	FROM prijemni_prijava as pp, uspjeh_u_srednjoj as uus, studij as s, prijemni_termin as pt 
	WHERE pp.osoba=$student and uus.osoba=$student and pp.studij_prvi=s.id AND pp.prijemni_termin=pt.id AND pt.akademska_godina=$ag
	ORDER BY pp.prijemni_termin");
	while ($r15 = db_fetch_row($q15)) {
		$total = $r15[1]+$r15[2]+$r15[3]+$r15[4];
		$datum = date("d. m. Y.", $r15[5]);
		print "<p><b>$agnaziv</b>: $izasa na prijemni ispit $datum (za $r15[0]): ukupno $total bodova ($r15[4] bodova na prijemnom ispitu)</p>";
	}

	// Upisi u studije
	$q20 = db_query("select s.naziv, ss.semestar, ns.naziv, ss.ponovac, ss.odluka from studij as s, student_studij as ss, nacin_studiranja as ns where s.id=ss.studij and ns.id=ss.nacin_studiranja and ss.student=$student and ss.akademska_godina=$ag order by ss.akademska_godina,ss.semestar");
	while ($r20 = db_fetch_row($q20)) {
		$semestar = $r20[1];
		$parni = $semestar%2;
		print "<p><b>$agnaziv</b>: $upisa studij \"$r20[0]\", $semestar. semestar, kao $r20[2] student";
		if ($r20[3]>0) print " (ponovac)";
		if ($r20[4]>0) {
			$q25 = db_query("select UNIX_TIMESTAMP(datum), broj_protokola from odluka where id=$r20[4]");
			print " na osnovu odluke ".db_result($q25,0,1)." od ".date("d. m. Y", db_result($q25,0,0));
		}
		print ".<br />\n";
		$q30 = db_query("select p.id, p.naziv from student_predmet as sp, ponudakursa as pk, predmet as p where sp.student=$student and sp.predmet=pk.id and pk.akademska_godina=$ag and pk.semestar mod 2 = $parni and pk.predmet=p.id order by p.naziv");
		if (db_num_rows($q30)>0) print "<ul>\n";
		while ($r30 = db_fetch_row($q30)) {
			$q40 = db_query("select ocjena from konacna_ocjena where student=$student and predmet=$r30[0] and akademska_godina=$ag");
			if (db_num_rows($q40)<1) {
				print "NIJE $polozi predmet $r30[1]<br />\n";
			} else {
				$ocjena = db_result($q40,0,0);
				if ($ocjena == 11) $ocjena = "ispunio/la obaveze"; else if ($ocjena == 12) $ocjena = "uspješno odbranio"; else $ocjena = "ocjena $ocjena";
				print "$ppolozi predmet $r30[1], $ocjena<br />\n";
			}
		}
		if (db_num_rows($q30)>0) print "</ul></p>\n";
	}
}


}

?>
