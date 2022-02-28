<?php


// Pregled predmeta za koliziju i potvrda

function studentska_osobe_kolizija() {
	$osoba = int_param('osoba');
	
	// Odredjujemo u koju akademsku godinu bi se trebao upisivati student
	$nova_ak_god=intval($_REQUEST['godina']);
	$q398 = db_query("select naziv from akademska_godina where id=$nova_ak_god");
	$naziv_godine=db_result($q398,0,0);
	
	// Koji studij student sluša? Treba nam radi ponudekursa
	$q399 = db_query("select s.id, s.naziv from student_studij as ss, studij as s where ss.student=$osoba and ss.studij=s.id order by ss.akademska_godina desc, ss.semestar desc");
	$studij = db_result($q399,0,0);
	$studij_naziv = db_result($q399,0,1);
	
	$q400 = db_query("select predmet from kolizija where student=$osoba and akademska_godina=$nova_ak_god");
	$predmeti=$ponudekursa=array();
	$greska=0;
	while ($r400 = db_fetch_row($q400)) {
		$predmet = $r400[0];
		
		// Eliminišemo predmete koje je položio u međuvremenu
		$q410 = db_query("select count(*) from konacna_ocjena where student=$osoba and predmet=$predmet and ocjena>5");
		if (db_result($q410,0,0)<1) {
			$q420 = db_query("select naziv from predmet where id=$predmet");
			$predmeti[$predmet] = "<b>".db_result($q420,0,0)."</b> ($studij_naziv, ";
			
			// Odredjujemo ponudu kursa koju bi student trebao slušati
			$q430 = db_query("select id, semestar, obavezan from ponudakursa where predmet=$predmet and studij=$studij and akademska_godina=$nova_ak_god");
			if (db_num_rows($q430)<1) {
				if ($greska==0) niceerror("Nije pronađena ponuda kursa");
				print "Predmet <b>".db_result($q420,0,0)."</b>, studij <b>$studij_naziv</b>, godina $naziv_godine<br/>";
				$greska=1;
			}
			$ponudekursa[$predmet] = db_result($q430,0,0);
			$predmeti[$predmet] .= db_result($q430,0,1).". semestar";
			if (db_result($q430,0,2)==0) $predmeti[$predmet] .= ", izborni";
			$predmeti[$predmet] .= ")";
		}
	}
	
	if ($greska==1) return; // ne idemo dalje
	
	if (count($predmeti)==0) { // nema ništa za koliziju!!!
		nicemessage ("Student je u međuvremenu položio/la sve predmete! Nema se ništa za upisati.");
		return;
	}
	
	
	if ($_REQUEST['subakcija'] == "potvrda") {
		foreach ($ponudekursa as $predmet => $pk) {
			upis_studenta_na_predmet($osoba, $pk);
			$q440 = db_query("delete from kolizija where student=$osoba and akademska_godina=$nova_ak_god and predmet=$predmet");
			zamgerlog2("student upisan na predmet (kolizija)", $osoba, intval($pk));
		}
		zamgerlog("prihvacen zahtjev za koliziju studenta u$osoba", 4); // 4 = audit
		zamgerlog2("prihvacen zahtjev za koliziju", $osoba);
		print "<p>Upis je potvrđen.</p>\n";
	} else {
		?>
		<p>Student želi upisati sljedeće predmete:</p>
		<ul>
			<?
			foreach ($predmeti as $tekst) {
				print "<li>$tekst</li>\n";
			}
			?>
		</ul>
		<?=genform("POST");?>
		<input type="hidden" name="subakcija" value="potvrda">
		<input type="submit" value=" Potvrdi ">
		</form>
		<?
	}
}