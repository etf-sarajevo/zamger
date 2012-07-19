<?

// ADMIN/NOVAGODINA - kreiranje nove akademske godine





function admin_novagodina() {




// Funkcija koja kreira jednu ponudu kursa i dodaje sve ostalo što treba
function kreirajPonuduKursa($predmet, $studij, $semestar, $ag, $obavezan, $ispis) {
	// Naziv predmeta nam treba za poruke
	$q60 = myquery("select naziv from predmet where id=$predmet");
	$naziv_predmeta = mysql_result($q60,0,0);

	// Da li već postoji slog u tabeli ponudakursa
	$q61 = myquery("select count(*) from ponudakursa where predmet=$predmet and akademska_godina=$ag and studij=$studij and semestar=$semestar");
	if (mysql_result($q61,0,0)>0) {
		if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Već postoji slog u tabeli ponudakursa za $naziv_predmeta<br/>\n";

	} else {
		if ($obavezan==1) $tekst = "obavezan"; else $tekst = "izborni";
		if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Dodajem predmet $naziv_predmeta ($tekst)<br/>\n";
		else {
			$q63 = myquery("insert into ponudakursa set predmet=$predmet, studij=$studij, semestar=$semestar, obavezan=$obavezan, akademska_godina=$ag");

			// Kreiranje labgrupe "svi studenti"
			$q65 = myquery("select count(*) from labgrupa where predmet=$predmet and akademska_godina=$ag and virtualna=1");
			if (mysql_result($q65,0,0)==0)
				$q67 = myquery("insert into labgrupa set naziv='(Svi studenti)', predmet=$predmet, akademska_godina=$ag, virtualna=1");
		}
	}

	// Dodajem slog u akademska_godina_predmet
	// Uzimamo tip predmeta od prethodne godine
	$q80 = myquery("select akademska_godina, tippredmeta from akademska_godina_predmet where predmet=$predmet and akademska_godina<=$ag order by akademska_godina desc limit 1");
	if (mysql_num_rows($q80)==0) 
		$tippredmeta = 1; // 1 = ETF Bologna Standard - mora postojati
	else if (mysql_result($q80,0,0) == $ag) { // Već postoji
		if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Već postoji slog u akademska_godina_predmet<br>\n";
	} else {
		$tippredmeta = mysql_result($q80,0,1);
		if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Dodajem slog u akademska_godina_predmet<br>\n";
		else $q90 = myquery("insert into akademska_godina_predmet set akademska_godina=$ag, predmet=$predmet, tippredmeta=$tippredmeta");
	}

	// Kopiram podatak od prošle godine za moodle predmet id, ako ga ima
	$q100 = myquery("select akademska_godina, moodle_id from moodle_predmet_id where predmet=$predmet and akademska_godina<=$ag order by akademska_godina desc limit 1");
	// Ako ga nema, ne radimo ništa
	if (mysql_num_rows($q100)>0) {
		if (mysql_result($q100,0,0) == $ag) { // Već postoji
			if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Već postoji slog u moodle_predmet_id<br>\n";
		} else {
			$moodle_id = mysql_result($q100,0,1);
			if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Dodajem slog u moodle_predmet_id<br>\n";
			else $q110 = myquery("insert into moodle_predmet_id set akademska_godina=$ag, predmet=$predmet, moodle_id=$moodle_id");
		}
	}

	// Kopiram podatak od prošle godine za angažman
	$q120 = myquery("select count(*) from angazman where predmet=$predmet and akademska_godina=$ag");
	if (mysql_result($q120,0,0) > 0) {
		if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Već postoje slogovi u tabeli angazman<br>\n";
	} else {
		if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Kopiram angažman od prošle godine<br>\n";
		else {
			$q130 = myquery("select osoba, angazman_status from angazman where predmet=$predmet and akademska_godina=".($ag-1));
			while ($r130 = mysql_fetch_row($q130))
				$q140 = myquery("insert into angazman set osoba=$r130[0], angazman_status=$r130[1], predmet=$predmet, akademska_godina=$ag");
		}
	}

	// Kopiram podatak od prošle godine za prava pristupa
	$q150 = myquery("select count(*) from nastavnik_predmet where predmet=$predmet and akademska_godina=$ag");
	if (mysql_result($q150,0,0) > 0) {
		if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Već postoje slogovi u tabeli nastavnik_predmet<br>\n";
	} else {
		if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Kopiram prava pristupa od prošle godine<br>\n";
		else {
			$q160 = myquery("select nastavnik, nivo_pristupa from nastavnik_predmet where predmet=$predmet and akademska_godina=".($ag-1));
			while ($r160 = mysql_fetch_row($q160))
				$q170 = myquery("insert into nastavnik_predmet set nastavnik=$r160[0], nivo_pristupa='$r160[1]', predmet=$predmet, akademska_godina=$ag");
		}
	}

}



if ($_POST['akcija'] == "novagodina") {
	if ($_POST['fakatradi'] != 1) $ispis=1; else $ispis=0;

	$naziv = my_escape($_POST['godina']);
	$q10 = myquery("select id from akademska_godina where naziv like '$naziv'");
	if (mysql_num_rows($q10)<1) {
		$q20 = myquery("select id from akademska_godina order by id desc limit 1");
		$noviid = mysql_result($q20,0,0)+1;
		$q30 = myquery("insert into akademska_godina set id=$noviid, naziv='$naziv', aktuelna=0");
		$q10 = myquery("select id from akademska_godina where naziv like '$naziv'");
		$ag = mysql_result($q10,0,0);
		print "-- Kreirana nova akademska godina '$naziv' (ID: $ag). Koristite modul 'Parametri studija' da je proglasite za aktuelnu.<br/>\n";
	} else {
		$ag = mysql_result($q10,0,0);
		print "-- Pronađena postojeća akademska godina (ID: $ag) - neće biti kreirana nova godina.<br/>\n";
	}
	
	$q40 = myquery("select s.id, s.naziv, ts.trajanje, ts.moguc_upis from studij as s, tipstudija as ts where s.tipstudija=ts.id");
	while ($r40 = mysql_fetch_row($q40)) {
		$studij = $r40[0];
		if ($ispis) print "-- Studij $r40[1]<br/>\n";

		if ($r40[3]==0) {
			if ($ispis) print "&nbsp;&nbsp;&nbsp;!! Nije moguć upis na ovaj studij.<br/>";
			continue;
		}

		$bio=array();
		for ($sem=1; $sem<=$r40[2]; $sem++) {
			if ($ispis) print "&nbsp;&nbsp;&nbsp;-- Semestar $sem<br/>\n";
			$min_god_vazenja = $ag-intval(($sem-1)/2);
			$q50 = myquery("select predmet, godina_vazenja, obavezan from plan_studija where studij=$studij and semestar=$sem and godina_vazenja<=$min_god_vazenja");
			if (mysql_num_rows($q50)<1) {
				if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;!! Nije pronađen plan studija mlađi od godine sa IDom $min_god_vazenja<br/>\n";
			}
			while ($r50 = mysql_fetch_row($q50)) {
				if ($r50[2]==1) { // obavezan
					kreirajPonuduKursa ($predmet = $r50[0], $studij, $sem, $ag, $obavezan=1, $ispis);

				} else { // izborni
					$iz = $r50[0];
					// $iz je slot, uzimamo sve predmete u tom slotu
					$q70 = myquery("select p.id, p.naziv from predmet as p, izborni_slot as iz where iz.id=$iz and iz.predmet=p.id");
					while ($r70 = mysql_fetch_row($q70)) {
						$predmet = $r70[0];
						if (in_array($predmet, $bio)) continue;
						array_push($bio, $predmet);
						kreirajPonuduKursa ($predmet, $studij, $sem, $ag, $obavezan=0, $ispis);
					}
				}
			}
		}
	}

	if ($ispis) {
		?><?=genform("POST")?>
		<input type="submit" value="Potvrdi">
		<input type="hidden" name="fakatradi" value="1">
		</form>
		<?
	} else {
		print "Podaci su ubačeni.";
	}


} else {
	


	$q = myquery("select naziv from akademska_godina order by id desc limit 1");
	
	?>
	<h2>Nova akademska godina</h2>
	<p>Ovaj modul kreira novu akademsku godinu u bazi, a zatim za datu godinu kreira sve predmete koji su predviđeni aktuelnim planovima svih kreiranih studija.</p>
	<p>Klikom na dugme "Kreiraj" biće najprije ispisano šta će se sve uraditi, te ponuđeno dugme "Potvrda" nakon kojeg će akcije biti izvršene i baza izmijenjena.</p>
	<p><?=genform("POST")?>
	<input type="hidden" name="akcija" value="novagodina">
	<input type="text" name="godina" size="20" value="<?=mysql_result($q,0,0)?>">
	<input type="submit" value=" Kreiraj novu akademsku godinu ">
	</form>
	<hr>
	<?
}





}

?>