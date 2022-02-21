<?php


//----------------------------------------
// Brisanje osobe
//----------------------------------------

function admin_misc_brisanje_osobe() {

	if ($_POST['akcija']=="brisanje_osobe" && check_csrf_token()) {
		$osoba = intval($_REQUEST['osoba']);
		$f = intval($_REQUEST['fakatradi']);
		
		$q100 = db_query("select predmet, akademska_godina, angazman_status from angazman where osoba=$osoba");
		if ($f)
			$q100a = db_query("delete from angazman where osoba=$osoba");
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
		
		$q105 = db_query("select login from auth where id=$osoba");
		if (db_num_rows($q105)>0) {
			if ($f)
				$q105a = db_query("delete from auth where id=$osoba");
			else
				print "Login ".db_result($q105,0,0)."<br>\n";
		}
		
		$q120 = db_query("select vrijeme, prvi_post from bb_tema where osoba=$osoba");
		if ($f)
			$q120a = db_query("delete from bb_tema where osoba=$osoba");
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
		
		$q110 = db_query("select naslov, vrijeme, tema from bb_post where osoba=$osoba");
		if ($f)
			$q110a = db_query("delete from bb_post where osoba=$osoba");
		else
			while ($r110 = db_fetch_row($q110)) {
				print "BB post: '$r110[0]' $r110[1] $r110[2]<br>";
			}
	//bl_clanak
		
		$q125 = db_query("select datum, vrijeme, labgrupa from cas where nastavnik=$osoba");
		if ($f)
			$q125a = db_query("UPDATE cas SET nastavnik=0 where nastavnik=$osoba");
		else
			while ($r125 = db_fetch_row($q125)) {
				print "Čas ($r125[0] $r125[1]), grupa $r125[2]<br>";
			}
		
		$q130 = db_query("select adresa from email where osoba=$osoba");
		if ($f)
			$q130a = db_query("delete from email where osoba=$osoba");
		else
			while ($r130 = db_fetch_row($q130)) {
				print "Email: '$r130[0]'<br>";
			}
		
		$q140 = db_query("select ispit, ocjena from ispitocjene where student=$osoba");
		if ($f)
			$q140a = db_query("delete from ispitocjene where student=$osoba");
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
		
		$q145 = db_query("SELECT akademska_godina FROM izvoz_upis_prva WHERE student=$osoba");
		if ($f)
			$q145a = db_query("DELETE FROM izvoz_upis_prva WHERE student=$osoba");
		else
			while ($r145 = db_fetch_row($q145))
				print "Izvoz upis prva: " . db_get("SELECT naziv FROM akademska_godina WHERE id=$r145[0]") . "<br>";
	
	//izbor
	//kolizija
	//komentar
	//komponentebodovi
	//konacna_ocjena
	//kviz_student
		
		/*$q150 = db_query("select vrijeme, dogadjaj, nivo from log where userid=$osoba");
		if ($f)
			$q150a = db_query("delete from log where userid=$osoba");
		else
			while ($r150 = db_fetch_row($q150)) {
				print "Log: $r150[0] $r150[1] $r150[2]<br>";
			}*/
		
		$q160 = db_query("select kod from kod_za_izvjestaj where osoba=$osoba");
		if ($f)
			$q160a = db_query("delete from kod_za_izvjestaj where osoba=$osoba");
		else
			while ($r160 = db_fetch_row($q160)) {
				print "Kod za izvjestaj: $r160[0]<br>";
			}
		
		$q160 = db_query("select vrijeme, modul, dogadjaj from log2 where userid=$osoba");
		if ($f)
			$q160a = db_query("delete from log2 where userid=$osoba");
		else
			while ($r160 = db_fetch_row($q160)) {
				print "Log2: $r160[0] $r160[1] $r160[2]<br>";
			}
	//nastavnik_predmet
	//odluka
	//ogranicenje
	//poruka
	//preference
		$q170 = db_query("select prijemni_termin, sifra, jezik from prijemni_obrazac where osoba=$osoba");
		if ($f)
			$q170a = db_query("delete from prijemni_obrazac where osoba=$osoba");
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
		
		$q180 = db_query("select prijemni_termin, broj_dosjea, izasao, rezultat from prijemni_prijava where osoba=$osoba");
		if ($f)
			$q180a = db_query("delete from prijemni_prijava where osoba=$osoba");
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
		
		$q190 = db_query("select privilegija from privilegije where osoba=$osoba");
		if ($f)
			$q190a = db_query("delete from privilegije where osoba=$osoba");
		else
			while ($r190 = db_fetch_row($q190)) {
				print "Privilegije: $r190[0]<br>";
			}
	//projekat_file
	//projekat_link
	//projekat_rss
	//promjena_odsjeka
	//promjena_podataka
	//prosliciklus_ocjene
	//prosliciklus_uspjeh
	//rss
	//septembar
		
		$q200 = db_query("select razred, redni_broj, ocjena from srednja_ocjene where osoba=$osoba");
		if ($f)
			$q200a = db_query("delete from srednja_ocjene where osoba=$osoba");
		else
			while ($r200 = db_fetch_row($q200)) {
				print "Srednja_ocjene: $r200[0] $r200[1] $r200[2]<br>";
			}
	
	//student_ispit_termin
	//student_labgrupa
		
		$q210 = db_query("select predmet from student_predmet where student=$osoba");
		if ($f)
			$q210a = db_query("delete from student_predmet where student=$osoba");
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
		
		$q220 = db_query("select studij, semestar, akademska_godina from student_studij where student=$osoba");
		if ($f)
			$q220a = db_query("delete from student_studij where student=$osoba");
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
		
		$q230 = db_query("select srednja_skola, godina from uspjeh_u_srednjoj where osoba=$osoba");
		if ($f)
			$q230a = db_query("delete from uspjeh_u_srednjoj where osoba=$osoba");
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
		
		
		if ($f)
			$q500 = db_query("delete from osoba where id=$osoba");
		
		
		if (!$f) {
			?>
			<?=genform("POST")?>
			<input type="hidden" name="fakatradi" value="1">
			<input type="hidden" name="akcija" value="brisanje_osobe">
			<input type="submit" value=" Fakat radi ">
			</form>
			<?
		}
		else {
			nicemessage("Osoba sa IDom $osoba obrisana.");
			print "<a href=\"?sta=admin/misc\">Nazad</a>";
		}
		
	} else {
		
		
		?>
		<?=genform("POST")?>
		<input type="hidden" name="akcija" value="brisanje_osobe">
		Unesite ID osobe: <input type="text" name="osoba" value=""><br>
		<input type="submit" value=" Brisanje osobe ">
		</form>
		<?
		
	}
}