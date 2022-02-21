<?php


//----------------------------------------
// Upis na završni rad
//----------------------------------------

// Ovaj modul se koristi kada počnu da zanovijetaju zašto studenti ne vide završni rad a još nije bila ni nova godina

function admin_misc_mass_zavrsni() {
	// TODO prebaciti na api
	
	if ($_POST['akcija']=="mass_zavrsni" && check_csrf_token()) {
		if ($_REQUEST['fakatradi'] != 1) $ispis=1; else $ispis=0;
		
		$greska = 0;
		
		$ag = db_get("SELECT id FROM akademska_godina WHERE aktuelna=1");
		
		// Kod je obsolete zbog kolone tippredmeta, mada za sada radi
		$pk_zavrsni = db_query_vassoc("SELECT pk.studij, pk.id FROM ponudakursa pk, akademska_godina_predmet agp
		WHERE agp.akademska_godina=$ag AND (agp.tippredmeta=1000 OR agp.tippredmeta=1001) AND agp.predmet=pk.predmet AND pk.akademska_godina=$ag"); // 1000 = Završni rad
		
		
		$q10 = db_query("SELECT o.id, o.ime, o.prezime, s.id FROM osoba o, student_studij ss, studij s, tipstudija ts
		WHERE ss.akademska_godina=$ag AND ss.student=o.id AND ss.studij=s.id AND s.tipstudija=ts.id AND ts.ciklus=1 AND ss.semestar=5
		ORDER BY o.prezime, o.ime");
		while (db_fetch4($q10, $student, $ime, $prezime, $studij)) {
			$pk = $pk_zavrsni[$studij];
			if (!isset($pk)) {
				print "--- Greška: Nepoznat predmet za studij $studij!!!<br>\n";
				continue;
			}
			$vec_upisan = db_get("SELECT COUNT(*) FROM student_predmet WHERE student=$student AND predmet=$pk");
			if ($vec_upisan) continue;
			if ($ispis) print "Upisujem $prezime $ime<br>\n";
			else {
				upis_studenta_na_predmet($student, $pk);
			}
		}
		
		
		// Sada isto to za drugi ciklus
		$q10 = db_query("SELECT o.id, o.ime, o.prezime, s.id FROM osoba o, student_studij ss, studij s, tipstudija ts
		WHERE ss.akademska_godina=$ag AND ss.student=o.id AND ss.studij=s.id AND s.tipstudija=ts.id AND ts.ciklus=2 AND ss.semestar=3
		ORDER BY o.prezime, o.ime");
		while (db_fetch4($q10, $student, $ime, $prezime, $studij)) {
			if ($studij >= 18 && $studij <= 21) continue; // Ekvivalencija
			$pk = $pk_zavrsni[$studij];
			if (!isset($pk)) print "--- Greška: Nepoznat predmet za studij $studij!!!<br>\n";
			$vec_upisan = db_get("SELECT COUNT(*) FROM student_predmet WHERE student=$student AND predmet=$pk");
			if ($vec_upisan) continue;
			if ($ispis) print "Upisujem $prezime $ime<br>\n";
			else {
				upis_studenta_na_predmet($student, $pk);
			}
		}
		
		// Potvrda i Nazad
		if ($ispis) {
			?>
			<?=genform("POST")?>
			<input type="hidden" name="fakatradi" value="1">
			<?
			print '<input type="submit" name="nazad" value=" Nazad "> ';
			if ($greska==0) print '<input type="submit" value=" Potvrda ">';
			print "</form>";
			return;
		} else {
			?>
			Svi studenti upisani na Završni rad.
			<?
		}
		
	} else {
		?>
		<?=genform("POST")?>
		<input type="hidden" name="fakatradi" value="0">
		<input type="hidden" name="akcija" value="mass_zavrsni">
		<input type="submit" value=" Upiši sve studente 5. semestra BSc / 3. semestra MSc na predmet Završni rad ">
		</form>
		<?
	}
}
