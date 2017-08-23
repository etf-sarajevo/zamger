<?

// LIB/PREDMET - funkcije za upravljanje predmetima



// Funkcija koja kreira jednu ponudu kursa i dodaje sve ostalo što treba
// Ako je parametar $ispis == true, ne radi ništa (vraća id ponudekursa ako ista već postoji,
// u suprotnom vraća false)
function kreiraj_ponudu_kursa($predmet, $studij, $semestar, $ag, $obavezan, $ispis) {
	// Naziv predmeta nam treba za poruke
	if ($obavezan === true || $obavezan === 1 || $obavezan === "1") $obavezan=1; else $obavezan=0;
	$q60 = db_query("select naziv from predmet where id=$predmet");
	$naziv_predmeta = db_result($q60,0,0);
	$pkid = false;

	// Da li već postoji slog u tabeli ponudakursa
	$q61 = db_query("select id from ponudakursa where predmet=$predmet and akademska_godina=$ag and studij=$studij and semestar=$semestar");
	if (db_num_rows($q61)>0) {
		if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Već postoji slog u tabeli ponudakursa za $naziv_predmeta<br/>\n";
		$pkid = db_result($q61,0,0);

	} else {
		if ($obavezan==1) $tekst = "obavezan"; else $tekst = "izborni";
		if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Dodajem predmet $naziv_predmeta ($tekst)<br/>\n";
		else {
			$q63 = db_query("insert into ponudakursa set predmet=$predmet, studij=$studij, semestar=$semestar, obavezan=$obavezan, akademska_godina=$ag");
			$pkid = db_insert_id();

			// Kreiranje labgrupe "svi studenti"
			$q65 = db_query("select count(*) from labgrupa where predmet=$predmet and akademska_godina=$ag and virtualna=1");
			if (db_result($q65,0,0)==0)
				$q67 = db_query("insert into labgrupa set naziv='(Svi studenti)', predmet=$predmet, akademska_godina=$ag, virtualna=1");
		}
	}

	// Dodajem slog u akademska_godina_predmet
	// Uzimamo tip predmeta od prethodne godine
	$q80 = db_query("select akademska_godina, tippredmeta from akademska_godina_predmet where predmet=$predmet and akademska_godina<=$ag order by akademska_godina desc limit 1");
	if (db_num_rows($q80)==0) {
		$tippredmeta = 1; // 1 = Bologna Standard - mora postojati
		if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Dodajem slog u akademska_godina_predmet<br>\n";
		else $q90 = db_query("insert into akademska_godina_predmet set akademska_godina=$ag, predmet=$predmet, tippredmeta=$tippredmeta");
	} else if (db_result($q80,0,0) == $ag) { // Već postoji
		if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Već postoji slog u akademska_godina_predmet<br>\n";
	} else {
		$tippredmeta = db_result($q80,0,1);
		if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Dodajem slog u akademska_godina_predmet<br>\n";
		else $q90 = db_query("insert into akademska_godina_predmet set akademska_godina=$ag, predmet=$predmet, tippredmeta=$tippredmeta");
	}

	// Kopiram podatak od prošle godine za moodle predmet id, ako ga ima
	$q100 = db_query("select akademska_godina, moodle_id from moodle_predmet_id where predmet=$predmet and akademska_godina<=$ag order by akademska_godina desc limit 1");
	// Ako ga nema, ne radimo ništa
	if (db_num_rows($q100)>0) {
		if (db_result($q100,0,0) == $ag) { // Već postoji
			if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Već postoji slog u moodle_predmet_id<br>\n";
		} else {
			$moodle_id = db_result($q100,0,1);
			if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Dodajem slog u moodle_predmet_id<br>\n";
			else $q110 = db_query("insert into moodle_predmet_id set akademska_godina=$ag, predmet=$predmet, moodle_id=$moodle_id");
		}
	} else {
		if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- E: Nema podatka od prošle godine za moodle_predmet_id<br>\n";
	}

	// Kopiram podatak od prošle godine za angažman
	$q120 = db_query("select count(*) from angazman where predmet=$predmet and akademska_godina=$ag");
	if (db_result($q120,0,0) > 0) {
		if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Već postoje slogovi u tabeli angazman<br>\n";
	} else {
		if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Kopiram angažman od prošle godine<br>\n";
		else {
			$q130 = db_query("select osoba, angazman_status from angazman where predmet=$predmet and akademska_godina=".($ag-1));
			while ($r130 = db_fetch_row($q130))
				$q140 = db_query("insert into angazman set osoba=$r130[0], angazman_status=$r130[1], predmet=$predmet, akademska_godina=$ag");
		}
	}

	// Kopiram podatak od prošle godine za prava pristupa
	$q150 = db_query("select count(*) from nastavnik_predmet where predmet=$predmet and akademska_godina=$ag");
	if (db_result($q150,0,0) > 0) {
		if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Već postoje slogovi u tabeli nastavnik_predmet<br>\n";
	} else {
		if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Kopiram prava pristupa od prošle godine<br>\n";
		else {
			$q160 = db_query("select nastavnik, nivo_pristupa from nastavnik_predmet where predmet=$predmet and akademska_godina=".($ag-1));
			while ($r160 = db_fetch_row($q160))
				$q170 = db_query("insert into nastavnik_predmet set nastavnik=$r160[0], nivo_pristupa='$r160[1]', predmet=$predmet, akademska_godina=$ag");
		}
	}

	return $pkid;
}

?>



