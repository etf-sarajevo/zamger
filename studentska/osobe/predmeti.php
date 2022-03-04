<?php

// Manuelni upis/ispis na predmete

function studentska_osobe_predmeti() {
	require_once("lib/predmet.php"); // kreiraj_ponudu_kursa
	require_once("lib/formgen.php"); // db_dropdown
	
	$osoba = int_param('osoba');
	
	// Parametar "spisak" određuje koji predmeti će biti prikazani
	$spisak = intval($_REQUEST['spisak']);
	
	$q2000 = db_query("select ime, prezime from osoba where id=$osoba");
	if (db_num_rows($q2000)<1) {
		niceerror("Nepoznata osoba $osoba");
		return;
	}
	$ime = db_result($q2000,0,0);
	$prezime = db_result($q2000,0,1);
	
	?>
	<h2><?=$ime?> <?=$prezime?> - upis/ispis na predmete</h2>
	<?
	
	
	// Subakcije: upis i ispis sa predmeta
	
	if ($_REQUEST['subakcija']=="upisi") {
		$ponudakursa = intval($_REQUEST['ponudakursa']);
		upis_studenta_na_predmet($osoba, $ponudakursa);
		
		$q2200 = db_query("select p.naziv from ponudakursa as pk, predmet as p where pk.id=$ponudakursa and pk.predmet=p.id");
		$naziv_predmeta = db_result($q2200,0,0);
		
		nicemessage("Student upisan na predmet $naziv_predmeta");
		zamgerlog("student u$osoba manuelno upisan na predmet p$ponudakursa", 4); // 4 - audit
		zamgerlog2("student upisan na predmet (manuelno)", $osoba, $ponudakursa);
	}
	
	if ($_REQUEST['subakcija']=="ispisi") {
		$ponudakursa = intval($_REQUEST['ponudakursa']);
		$q2200 = db_query("select p.id, p.naziv, pk.akademska_godina from ponudakursa as pk, predmet as p where pk.id=$ponudakursa and pk.predmet=p.id");
		$predmet = db_result($q2200,0,0);
		$naziv_predmeta = db_result($q2200,0,1);
		$ag = db_result($q2200,0,2);
		
		// Upozorenje ako ima neke bodove?
		$q2210 = db_query("select sum(bodovi) from komponentebodovi where student=$osoba and predmet=$ponudakursa");
		$bodovi = db_result($q2210,0,0);
		if ($bodovi!=0 && $bodovi!=10 && $_REQUEST['siguran']!="da") { // 10 bodova je default za prisustvo
			nicemessage("Upozorenje! Student je osvojio $bodovi bodova na predmetu $naziv_predmeta.");
			?>Da li ste sigurni da ga želite ispisati?<br/>
			<?=genform("POST");?>
			<input type="hidden" name="siguran" value="da">
			<input type="submit" value=" Potvrda ">
			</form>
			<?
			return;
		}
		
		ispis_studenta_sa_predmeta($osoba, $predmet, $ag);
		
		nicemessage("Student ispisan sa predmeta $naziv_predmeta");
		zamgerlog("student u$osoba manuelno ispisan sa predmeta p$ponudakursa", 4); // 4 - audit
		zamgerlog2("student ispisan sa predmeta (manuelno)", $osoba, intval($predmet), intval($ag));
	}
	
	
	
	// Aktuelna akademska godina
	
	if ($_REQUEST['ag'] || $_REQUEST['_lv_column_akademska_godina']) {
		$ak_god = intval($_REQUEST['ag']);
		if ($_REQUEST['_lv_column_akademska_godina']) $ak_god = intval($_REQUEST['_lv_column_akademska_godina']);
		$q2005 = db_query("select naziv from akademska_godina where id=$ak_god");
		if (db_num_rows($q2005)<1) {
			biguglyerror("Nepoznata akademska godina");
			return;
		}
		$naziv_ag = db_result($q2005,0,0);
	} else {
		$q2010 = db_query("select id, naziv from akademska_godina where aktuelna=1");
		$ak_god = db_result($q2010,0,0);
		$naziv_ag = db_result($q2010,0,1);
	}
	
	$q2020 = db_query("select studij, semestar, plan_studija from student_studij where student=$osoba and akademska_godina=$ak_god order by semestar desc");
	if (db_num_rows($q2020)>0) {
		$studij = db_result($q2020,0,0);
		$semestar = db_result($q2020,0,1);
		
		$q2025 = db_query("select naziv from studij where id=$studij");
		$naziv_studija = db_result($q2025,0,0);
		
		print "<p>Student trenutno ($naziv_ag) upisan na $naziv_studija, $semestar. semestar.</p>\n";
		
		// Upozorenje!
		if (db_result($q2020,0,2)>0) {
			print "<p><b>Napomena:</b> Student je već upisan na sve predmete koje je trebao slušati po odabranom planu studija!<br/> Koristite ovu opciju samo za izuzetke / odstupanja od plana ili u slučaju grešaka u radu Zamgera.<br/>U suprotnom, može se desiti da student nema adekvatan broj ECTS kredita ili da sluša izborni predmet<br/>koji ne bi smio slušati.</p>\n";
		}
		
	} else {
		// Student trenutno nije upisan nigdje... biramo zadnji studij koji je slušao
		if ($spisak==0) $spisak=1;
		$q2030 = db_query("select studij, semestar, akademska_godina from student_studij where student=$osoba order by akademska_godina desc limit 1");
		if (db_num_rows($q2030)>0) {
			$studij = db_result($q2030,0,0);
			$ag_studija = db_result($q2030,0,2);
			
			$q2040 = db_query("select naziv from studij where id=$studij");
			$naziv_studija = db_result($q2040,0,0);
			
			$q2050 = db_query("select naziv from akademska_godina where id=$ag_studija");
			
			if ($ag_studija > $ak_god) {
				print "<p>Student nije bio upisan u odabranoj akademskoj godini ($naziv_ag), ali je upisan na studij $naziv_studija, ".db_result($q2030,0,0).". semestar, akademske ".db_result($q2050,0,0)." godine.</p>\n";
			} else {
				print "<p>Student trenutno ($naziv_ag) nije upisan na fakultet! Posljednji put slušao $naziv_studija, ".db_result($q2030,0,0).". semestar, akademske ".db_result($q2050,0,0)." godine.</p>\n";
			}
		} else {
			// Nikada nije bio student?
			$studij=0;
			if ($spisak<2) $spisak=2;
			print "<p>Osoba nikada nije bila naš student!</p>\n";
		}
	}
	
	// Opcije za spisak predmeta
	$s0 = ($spisak==0) ? "CHECKED" : "";
	$s1 = ($spisak==1) ? "CHECKED" : "";
	$s2 = ($spisak==2) ? "CHECKED" : "";
	
	unset($_REQUEST['subakcija']); // da se ne bi ponovila
	
	?>
	<?=genform("GET");?>
	Akademska godina: <?=db_dropdown("akademska_godina", $ak_god);?><br>
	<input type="radio" name="spisak" value="0" <?=$s0?>> Prikaži predmete sa izabranog studija i semestra<br/>
	<input type="radio" name="spisak" value="1" <?=$s1?>> Prikaži predmete sa svih semestara<br/>
	<input type="radio" name="spisak" value="2" <?=$s2?>> Prikaži predmete sa drugih studija<br/>
	<input type="submit" value=" Kreni "></form><br><br>
	<?
	
	
	// Ispis predmeta
	
	if ($spisak==0) {
		print "<b>$naziv_studija, $semestar. semestar</b>\n<ul>\n";
		dajpredmete($studij, $semestar, $osoba, $ak_god, $spisak);
		print "</ul>\n";
	}
	
	else if ($spisak==1) {
		// Broj semestara?
		$q2060 = db_query("select ts.trajanje from studij as s, tipstudija as ts where s.id=$studij and s.tipstudija=ts.id");
		for ($s=1; $s<=db_result($q2060,0,0); $s++) {
			if ($s==$semestar) print "<b>$naziv_studija, $s. semestar</b>\n<ul>\n";
			else print "$naziv_studija, $s. semestar\n<ul>\n";
			dajpredmete($studij, $s, $osoba, $ak_god, $spisak);
			print "</ul>\n";
		}
	}
	
	else if ($spisak==2) {
		// Svi studiji
		$q2070 = db_query("select s.id, s.naziv, ts.trajanje from studij as s, tipstudija as ts where s.tipstudija=ts.id and s.moguc_upis=1 order by ts.ciklus, s.naziv");
		while ($r2070=db_fetch_row($q2070)) {
			$stud=$r2070[0];
			$stud_naziv=$r2070[1];
			$stud_trajanje=$r2070[2];
			
			if ($stud==$studij) print "<b>$stud_naziv</b>\n<ul>\n";
			else print "$stud_naziv\n<ul>\n";
			
			for ($s=1; $s<=$stud_trajanje; $s++) {
				if ($stud==$studij && $s==$semestar) print "<b>$s. semestar</b>\n<ul>\n";
				else print "$s. semestar\n<ul>\n";
				dajpredmete($stud, $s, $osoba, $ak_god, $spisak);
				print "</ul>\n";
			}
			print "</ul>\n";
		}
	}
}


// Ispis svih predmeta na studiju semestru je funkcija, pošto pozivanje unutar petlje ovisi o nivou spiska

function dajpredmete($studij, $semestar, $student, $ag, $spisak) {
	$q2100 = db_query("SELECT pk.id, p.id, p.naziv, pk.obavezan, pp.naziv, pk.pasos_predmeta
			FROM ponudakursa AS pk, predmet AS p, akademska_godina_predmet agp
			LEFT JOIN pasos_predmeta as pp ON pp.id=agp.pasos_predmeta
			WHERE pk.studij=$studij AND pk.semestar=$semestar AND pk.akademska_godina=$ag AND pk.predmet=p.id AND agp.predmet=p.id AND agp.akademska_godina=$ag
			ORDER BY p.naziv");
	while ($r2100 = db_fetch_row($q2100)) {
		$ponudakursa = $r2100[0];
		$predmet = $r2100[1];
		$predmet_naziv = $r2100[2];
		if ($r2100[5])
			$predmet_naziv = db_get("SELECT naziv FROM pasos_predmeta WHERE id=" . $r2100[5]);
		else if ($r2100[4]) $predmet_naziv = $r2100[4];
		print "<li>$predmet_naziv";
		if ($r2100[3]!=1) print " (izborni)";
		
		// Da li je upisan?
		// Zbog mogućih bugova, prvo gledamo da li je upisan...
		$q2120 = db_query("select count(*) from student_predmet where student=$student and predmet=$ponudakursa");
		if (db_result($q2120,0,0)>0) {
			print " - <a href=\"?sta=studentska/osobe&akcija=predmeti&osoba=$student&subakcija=ispisi&ponudakursa=$ponudakursa&spisak=$spisak&ag=$ag\">ispiši</a></li>\n";
			
		} else {
			// Da li je položen?
			$q2110 = db_query("select count(*) from konacna_ocjena where student=$student and predmet=$predmet and ocjena>5");
			if (db_result($q2110,0,0)>0) {
				print " - položen</li>\n";
				
			} else {
				print " - <a href=\"?sta=studentska/osobe&akcija=predmeti&osoba=$student&subakcija=upisi&ponudakursa=$ponudakursa&spisak=$spisak&ag=$ag\">upiši</a></li>\n";
			}
		}
	}
} // function dajpredmete
