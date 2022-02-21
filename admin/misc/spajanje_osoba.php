<?php


//----------------------------------------
// Spajanje osoba
//----------------------------------------

// Koristi se ako je dvaput unesena ista osoba, što se dešava iznenađujuće često (npr. ako je student bio kandidat
// na prijemnom, nije primljen, pa se opet prijavio sljedeće godine)

function admin_misc_spajanje_osoba() {
	
	if ($_POST['akcija']=="spajanje_osoba" && check_csrf_token()) {
		$osoba_A = intval($_REQUEST['osoba_A']);
		$osoba_B = intval($_REQUEST['osoba_B']);
		$f = intval($_REQUEST['fakatradi']);
		
		if (!$f) {
			// Da ispišemo šta će se raditi:
			$q90 = db_query("select ime, prezime from osoba where id=$osoba_A");
			$r90 = db_fetch_row($q90);
			$q91 = db_query("select ime, prezime from osoba where id=$osoba_B");
			$r91 = db_fetch_row($q91);
			
			print "<p>Podaci osobe: $r91[0] $r91[1] (ID: $osoba_B) će biti spojeni na osobu $r90[0] $r90[1] (ID: $osoba_A)</p>";
		}
		
		$q100 = db_query("select predmet, akademska_godina, angazman_status from angazman where osoba=$osoba_B");
		if ($f)
			$q100a = db_query("UPDATE angazman SET osoba=$osoba_A where osoba=$osoba_B");
		else
			while ($r100 = db_fetch_row($q100)) {
				print "Angazman: ";
				$q101 = db_query("select naziv from predmet where id=$r100[0]");
				if (db_num_rows($q101)>0)
					print db_result($q101,0,0)." ";
				else
					print "Predmet: $r100[0] ";
				$q102 = db_query("select naziv from akademska_godina where id=$r100[1]");
				if (db_num_rows($q102)>0)
					print db_result($q102,0,0)." ";
				else
					print "Ag: $r100[1] ";
				$q103 = db_query("select naziv from angazman_status where id=$r100[2]");
				if (db_num_rows($q103)>0)
					print db_result($q103,0,0)." ";
				else
					print "Status: $r100[2] ";
				print "<br>";
			}
		
		
		
		$stari_logini = $logini_dodati = array();
		$q105 = db_query("select login from auth where id=$osoba_A");
		while($r105 = db_fetch_row($q105)) array_push($stari_logini, $r105[0]);
		
		$q106 = db_query("select login from auth where id=$osoba_B");
		while ($r106 = db_fetch_row($q106)) {
			if (!in_array($r106[0], $stari_logini))
				array_push($logini_dodati, $r106[0]);
		}
		
		if ($f) {
			$q130a = db_query("delete from auth where id=$osoba_B");
		} else
			foreach ($logini_dodati as $login)
				print "Dodati login: $login<br>";
		
		
		$q120 = db_query("select vrijeme, prvi_post from bb_tema where osoba=$osoba_B");
		if ($f)
			$q120a = db_query("UPDATE bb_tema  SET osoba=$osoba_A where osoba=$osoba_B");
		else
			while ($r120 = db_fetch_row($q120)) {
				print "BB tema: $r120[0] ";
				$q121 = db_query("select naslov from bb_post where id=$r120[1]");
				if (db_num_rows($q121)>0)
					print db_result($q121,0,0)." ";
				else
					print "Post: $r120[1] ";
				print "<br>";
			}
		
		$q110 = db_query("select naslov, vrijeme, tema from bb_post where osoba=$osoba_B");
		if ($f)
			$q110a = db_query("UPDATE bb_post  SET osoba=$osoba_A where osoba=$osoba_B");
		else
			while ($r110 = db_fetch_row($q110)) {
				print "BB post: '$r110[0]' $r110[1] $r110[2]<br>";
			}
	
	//bl_clanak
		
		
		$q125 = db_query("select datum, vrijeme, labgrupa from cas where nastavnik=$osoba_B");
		if ($f)
			$q125a = db_query("UPDATE cas SET nastavnik=$osoba_A where nastavnik=$osoba_B");
		else
			while ($r125 = db_fetch_row($q125)) {
				print "Čas ($r125[0] $r125[1]), grupa $r125[2]<br>";
			}
		
		$stari_mailovi = $mailovi_dodati = array();
		$q130 = db_query("select adresa from email where osoba=$osoba_A");
		while($r130 = db_fetch_row($q130)) array_push($stari_mailovi, $r130[0]);
		
		$q131 = db_query("select adresa from email where osoba=$osoba_B");
		while ($r131 = db_fetch_row($q131)) {
			if (!in_array($r131[0], $stari_mailovi))
				array_push($mailovi_dodati, $r131[0]);
		}
		
		if ($f) {
			foreach ($mailovi_dodati as $mail)
				$q130b = db_query("INSERT INTO email SET osoba=$osoba_A, adresa='$mail', sistemska=0");
			$q130a = db_query("delete from email where osoba=$osoba_B");
		} else
			foreach ($mailovi_dodati as $mail)
				print "Dodati mail: $mail<br>";
		
		
		$q140 = db_query("select ispit, ocjena from ispitocjene where student=$osoba_B");
		if ($f)
			$q140a = db_query("UPDATE ispitocjene SET student=$osoba_A where student=$osoba_B");
		else
			while ($r140 = db_fetch_row($q140)) {
				print "Ispitocjene: ";
				$q141 = db_query("select p.naziv, ag.naziv, k.gui_naziv from predmet as p, akademska_godina as ag, komponenta as k, ispit as i where i.id=$r140[0] and i.predmet=p.id and i.akademska_godina=ag.id and i.komponenta=k.id");
				if (db_num_rows($q141)>0)
					print db_result($q141,0,0)." ".db_result($q141,0,1)." ".db_result($q141,0,2)." ";
				else
					print "Ispit: $r140[0] ";
				print "Ocjena: $r140[1]<br>";
			}
	//izbor
	//kolizija
		
		$q145 = db_query("select labgrupa, komentar from komentar where student=$osoba_B");
		if ($f)
			$q145a = db_query("UPDATE komentar SET student=$osoba_A where student=$osoba_B");
		else
			while ($r145 = db_fetch_row($q145)) {
				$q146 = db_query("select p.naziv from predmet p, labgrupa l where l.id=$r145[0] and l.predmet=p.id");
				if (db_num_rows($q146)>0)
					print "Komentar na predmetu ".db_result($q146,0,0).": $r145[1]<br>";
				else
					print "Komentar na predmetu ".$r145[0].": $r145[1]<br>";
			}
		
		
		$q147 = db_query("select predmet, komponenta, bodovi from komponentebodovi where student=$osoba_B");
		if ($f)
			$q147a = db_query("UPDATE komponentebodovi SET student=$osoba_A where student=$osoba_B");
		else
			while ($r147 = db_fetch_row($q147)) {
				$q148 = db_query("select naziv from predmet where id=$r147[0]");
				if (db_num_rows($q148)>0)
					print "Komponentebodovi: Predmet ".db_result($q148,0,0).", komponenta $r147[1], bodovi $r147[2]<br>";
				else
					print "Komponentebodovi: Predmet $r147[0], komponenta $r147[1], bodovi $r147[2]<br>";
			}
		
		
		$q149 = db_query("select predmet, akademska_godina, ocjena from konacna_ocjena where student=$osoba_B");
		if ($f)
			$q149a = db_query("UPDATE konacna_ocjena SET student=$osoba_A where student=$osoba_B");
		else
			while ($r149 = db_fetch_row($q149)) {
				$q150 = db_query("select naziv from predmet where id=$r149[0]");
				if (db_num_rows($q150)>0)
					print "Konačna ocjena $r149[2]: Predmet ".db_result($q150,0,0).", a.g. $r149[1]<br>";
				else
					print "Konačna ocjena $r149[2]: Predmet $r149[0], a.g. $r149[1]<br>";
			}
	
	//kviz_student
		/*
			$q150 = db_query("select vrijeme, dogadjaj, nivo from log where userid=$osoba_B");
			if ($f)
				$q150a = db_query("UPDATE log SET userid=$osoba_A where userid=$osoba_B");
			else
				while ($r150 = db_fetch_row($q150)) {
					print "Log: $r150[0] $r150[1] $r150[2]<br>";
				}
		*/
		$q160 = db_query("select vrijeme, modul, dogadjaj from log2 where userid=$osoba_B");
		if ($f)
			$q160a = db_query("UPDATE log2 SET userid=$osoba_A where userid=$osoba_B");
		else
			while ($r160 = db_fetch_row($q160)) {
				print "Log2: $r160[0] $r160[1] $r160[2]<br>";
			}
	
	//nastavnik_predmet
	//odluka
	//ogranicenje
	//poruka
	//preference
		
		$q170 = db_query("select prijemni_termin, sifra, jezik from prijemni_obrazac where osoba=$osoba_B");
		if ($f)
			$q170a = db_query("UPDATE prijemni_obrazac SET osoba=$osoba_A where osoba=$osoba_B");
		else
			while ($r170 = db_fetch_row($q170)) {
				print "Prijemni_obrazac: ";
				$q171 = db_query("select ag.naziv, pt.datum, pt.ciklus_studija from akademska_godina as ag, prijemni_termin as pt where pt.id=$r170[0] and pt.akademska_godina=ag.id");
				if (db_num_rows($q171)>0)
					print db_result($q171,0,0)." ".db_result($q171,0,1)." ".db_result($q171,0,2)." ";
				else
					print "Prijemni termin: $r170[0] ";
				print "$r170[1] $r170[2]<br>";
			}
		
		$q180 = db_query("select prijemni_termin, broj_dosjea, izasao, rezultat from prijemni_prijava where osoba=$osoba_B");
		if ($f)
			$q180a = db_query("UPDATE prijemni_prijava SET osoba=$osoba_A where osoba=$osoba_B");
		else
			while ($r180 = db_fetch_row($q180)) {
				print "Prijemni_prijava: ";
				$q181 = db_query("select ag.naziv, pt.datum, pt.ciklus_studija from akademska_godina as ag, prijemni_termin as pt where pt.id=$r180[0] and pt.akademska_godina=ag.id");
				if (db_num_rows($q181)>0)
					print db_result($q181,0,0)." ".db_result($q181,0,1)." ".db_result($q181,0,2)." ";
				else
					print "Prijemni termin: $r180[0] ";
				print "$r180[1] $r180[2] $r180[3]<br>";
			}
	//prisustvo
		
		$stare_priv = $priv_dodate = array();
		$q190 = db_query("select privilegija from privilegije where osoba=$osoba_A");
		while($r190 = db_fetch_row($q190)) array_push($stare_priv, $r190[0]);
		
		$q191 = db_query("select privilegija from privilegije where osoba=$osoba_B");
		while ($r191 = db_fetch_row($q191)) {
			if (!in_array($r191[0], $stare_priv))
				array_push($priv_dodate, $r191[0]);
		}
		
		if ($f) {
			foreach ($priv_dodate as $priv)
				$q130b = db_query("INSERT INTO privilegije SET osoba=$osoba_A, privilegija='$priv'");
			$q130a = db_query("DELETE FROM privilegije where osoba=$osoba_B");
		} else
			foreach ($priv_dodate as $priv)
				print "Dodata privilegija: $priv<br>";
	
	//projekat_file
	//projekat_link
	//projekat_rss
	//promjena_odsjeka
	//promjena_podataka
		
		
		$q195 = db_query("select ocjena from prosliciklus_ocjene where osoba=$osoba_B");
		if ($f)
			$q195a = db_query("UPDATE prosliciklus_ocjene SET osoba=$osoba_A where osoba=$osoba_B");
		else {
			print "Ocjene sa prošlog ciklusa: ";
			while ($r195 = db_fetch_row($q195)) {
				print $r195[0]. ", ";
			}
			print "<br>\n";
		}
		
		
		$q197 = db_query("select count(*) from prosliciklus_uspjeh where osoba=$osoba_B");
		if ($f)
			$q195a = db_query("UPDATE prosliciklus_uspjeh SET osoba=$osoba_A where osoba=$osoba_B");
		else {
			if (db_result($q197,0,0)) {
				print "Podaci o uspjehu na prošlom ciklusu.<br>\n";
			}
		}
	
	//rss
	//septembar
		
		$q200 = db_query("select razred, redni_broj, ocjena from srednja_ocjene where osoba=$osoba_B");
		if ($f)
			$q200a = db_query("UPDATE srednja_ocjene SET osoba=$osoba_A where osoba=$osoba_B");
		else
			while ($r200 = db_fetch_row($q200)) {
				print "Srednja_ocjene: $r200[0] $r200[1] $r200[2]<br>";
			}
	
	//student_ispit_termin
		
		$stare_grupe = $grupe_dodate = array();
		$q205 = db_query("select labgrupa from student_labgrupa where student=$osoba_A");
		while($r205 = db_fetch_row($q205)) array_push($stare_grupe, $r205[0]);
		
		$q206 = db_query("select labgrupa from student_labgrupa where student=$osoba_B");
		while ($r206 = db_fetch_row($q206)) {
			if (!in_array($r206[0], $stare_grupe))
				array_push($grupe_dodate, $r206[0]);
		}
		
		if ($f) {
			foreach ($grupe_dodate as $grupa)
				$q130b = db_query("INSERT INTO student_labgrupa SET student=$osoba_A, labgrupa=$grupa");
			$q130a = db_query("delete from student_labgrupa where student=$osoba_B");
		} else
			foreach ($grupe_dodate as $grupa)
				print "Dodata labgrupa: $grupa<br>";
		
		
		$q210 = db_query("select predmet from student_predmet where student=$osoba_B");
		if ($f)
			$q210a = db_query("UPDATE student_predmet SET student=$osoba_A where student=$osoba_B");
		else
			while ($r210 = db_fetch_row($q210)) {
				print "Student_predmet: ";
				$q211 = db_query("select p.naziv, ag.naziv, s.naziv, pk.semestar, pk.obavezan from akademska_godina as ag, predmet as p, studij as s, ponudakursa as pk where pk.id=$r210[0] and pk.akademska_godina=ag.id and pk.predmet=p.id and pk.studij=s.id");
				if (db_num_rows($q211)>0)
					print db_result($q211,0,0)." ".db_result($q211,0,1)." ".db_result($q211,0,2)." "." ".db_result($q211,0,3)." "." ".db_result($q211,0,4)." ";
				else
					print "Ponudakursa: $r210[0] ";
				print "<br>";
			}
	
	//student_projekat
		
		$q220 = db_query("select studij, semestar, akademska_godina from student_studij where student=$osoba_B");
		if ($f)
			$q220a = db_query("UPDATE student_studij SET student=$osoba_A where student=$osoba_B");
		else
			while ($r220 = db_fetch_row($q220)) {
				print "Student_studij: ";
				$q221 = db_query("select naziv from studij where id=$r220[0]");
				if (db_num_rows($q221)>0)
					print db_result($q221,0,0)." ";
				else
					print "Studij: $r220[0] ";
				print "$r220[1] ";
				$q222 = db_query("select naziv from akademska_godina where id=$r220[2]");
				if (db_num_rows($q222)>0)
					print db_result($q222,0,0)." ";
				else
					print "A.g.: $r220[2] ";
				print "<br>";
			}
	
	//ugovoroucenju
		
		$q230 = db_query("select srednja_skola, godina from uspjeh_u_srednjoj where osoba=$osoba_B");
		if ($f)
			$q230a = db_query("UPDATE uspjeh_u_srednjoj SET osoba=$osoba_A where osoba=$osoba_B");
		else
			while ($r230 = db_fetch_row($q230)) {
				print "Uspjeh_u_srednjoj: ";
				$q231 = db_query("select naziv from srednja_skola where id=$r230[0]");
				if (db_num_rows($q231)>0)
					print db_result($q231,0,0)." ";
				else
					print "Srednja skola: $r230[0] ";
				$q232 = db_query("select naziv from akademska_godina where id=$r230[1]");
				if (db_num_rows($q232)>0)
					print db_result($q232,0,0)." ";
				else
					print "a.g.: $r230[1] ";
				print "<br>";
			}
	//zadatak
	//zavrsni_*
	
	// MYSQL query:
	// delete from osoba where id=6800
	//
	// MYSQL error:
	// Cannot delete or update a parent row: a foreign key constraint fails (`zamger`.`izvoz_upis_semestar`, CONSTRAINT `izvoz_upis_semestar_ibfk_1` FOREIGN KEY (`student`) REFERENCES `osoba` (`id`))
	
	
	// Lični podaci
		
		print "<br><b>Lični podaci:</b><br>\n";
		$q300 = db_query("SELECT ime, prezime, imeoca, prezimeoca, imemajke, prezimemajke, spol, brindexa, datum_rodjenja, mjesto_rodjenja, nacionalnost, drzavljanstvo, boracke_kategorije, jmbg, adresa, adresa_mjesto, telefon, kanton, strucni_stepen, naucni_stepen FROM osoba where id=$osoba_A");
		$r300 = db_fetch_assoc($q300);
		$q310 = db_query("SELECT ime, prezime, imeoca, prezimeoca, imemajke, prezimemajke, spol, brindexa, datum_rodjenja, mjesto_rodjenja, nacionalnost, drzavljanstvo, boracke_kategorije, jmbg, adresa, adresa_mjesto, telefon, kanton, strucni_stepen, naucni_stepen FROM osoba where id=$osoba_B");
		$r310 = db_fetch_assoc($q310);
		$sql = "";
		foreach ($r300 as $key=>$value) {
			if ($r310[$key] !== "" && $r310[$key] !== 0 && $r310[$key] != $value) {
				if ($value === "" || $value === 0 || $value === "0") {
					if (!$f)
						print "Ključ $key dodati ".$r310[$key]."<br>\n";
				} else {
					if (!$f)
						print "Ključ $key bio $value sada ".$r310[$key]."<br>\n";
				}
				if ($sql != "") $sql .= ", ";
				$sql .= "$key='".$r310[$key]."'";
			}
		}
		if ($f && $sql != "") {
			$q320 = db_query("UPDATE osoba SET $sql WHERE id=$osoba_A");
		}
		if (!$f && $sql == "") {
			print "sve ok.<br>\n";
		}
		
		
		if ($f)
			$q500 = db_query("delete from osoba where id=$osoba_B");
		
		
		if (!$f) {
			?>
			<?=genform("POST")?>
			<input type="hidden" name="fakatradi" value="1">
			<input type="hidden" name="akcija" value="spajanje_osoba">
			<input type="submit" value=" Fakat radi ">
			</form>
			<?
		}
		else {
			nicemessage("Spojene osobe sa IDom $osoba_A i $osoba_B obrisana.");
			print "<a href=\"?sta=admin/misc\">Nazad</a>";
		}
		
	} else {
		
		?>
		<?=genform("POST")?>
		<input type="hidden" name="akcija" value="spajanje_osoba">
		Unesite ID osobe A: <input type="text" name="osoba_A" value=""><br>
		Unesite ID osobe B: <input type="text" name="osoba_B" value=""><br>
		<input type="submit" value=" Spajanje osoba ">
		</form>
		<?
		
	}
}