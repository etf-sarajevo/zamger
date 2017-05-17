<?

// LIB/STUDENT_STUDIJ - funkcije vezane za studij (upis na studij, ispis, uslov itd.)


// Funkcija koja provjerava da li je student dao uslov za upis na sljedecu godinu studija, odnosno koliko predmeta nije položeno
// Vraća boolean vrijednost
// Globalni niz $zamger_predmeti_pao sadrži id-eve predmeta koji nisu položeni
function ima_li_uslov($student, $ag=0) {
	global $zamger_predmeti_pao;
	$ima_uslov=false;

	// Odredjujemo studij i semestar
	if ($ag==0) {
		$q10 = db_query("select ss.studij, ss.semestar, ts.trajanje from student_studij as ss, studij as s, tipstudija as ts where ss.student=$student and ss.studij=s.id and s.tipstudija=ts.id order by ss.akademska_godina desc, ss.semestar desc limit 1");
		if (db_num_rows($q10)<1) 
			return true; // Nikad nije bio student, ima uslov za prvu godinu ;)
	} else {
		$q10 = db_query("select ss.studij, ss.semestar, ts.trajanje from student_studij as ss, studij as s, tipstudija as ts where ss.student=$student and ss.studij=s.id and s.tipstudija=ts.id and ss.akademska_godina=$ag order by ss.semestar desc limit 1");
		if (db_num_rows($q10)<1) 
			return false; // Nije bio student u datoj akademskoj godini
	}

	$studij = db_result($q10,0,0);
	$semestar = db_result($q10,0,1);
	if ($semestar%2==1) $semestar++; // zaokružujemo na parni semestar
	$studij_trajanje = db_result($q10,0,2);

	// Od predmeta koje je slušao, koliko je pao?
	$q20 = db_query("select distinct pk.predmet, p.ects, pk.semestar, pk.obavezan from ponudakursa as pk, student_predmet as sp, predmet as p where sp.student=$student and sp.predmet=pk.id and pk.semestar<=$semestar and pk.studij=$studij and pk.predmet=p.id order by pk.semestar");
	$obavezni_pao_ects=$obavezni_pao=$nize_godine=$ects_polozio=0;
	$zamger_predmeti_pao=array();
	while ($r20 = db_fetch_row($q20)) {
		$predmet = $r20[0];

		$ects = $r20[1];
		$predmet_semestar = $r20[2];
		$obavezan = $r20[3];

		$q30 = db_query("select count(*) from konacna_ocjena where student=$student and predmet=$predmet and ocjena>5");
		if (db_result($q30,0,0)<1) {
			array_push($zamger_predmeti_pao, $predmet);

			// Predmet se ne može prenijeti preko dvije godine
			if ($predmet_semestar<$semestar-1) $nize_godine++;

			// Ako je obavezan, situacija je jasna
			if ($obavezan) { 
				$obavezni_pao_ects+=$ects;
				$obavezni_pao++;

			// Za izborne možemo odrediti uslov samo preko ECTSa
			// pošto je tokom godina student mogao pokušavati razne izborne
			// predmete
			}
		} else
			$ects_polozio += $ects;
	}

	// USLOV ZA UPIS
	// Prema aktuelnom zakonu može se prenijeti tačno jedan predmet, bez obzira na ECTS
	// No, na sljedeći ciklus studija se ne može prenijeti ništa
	$ects_ukupno = $semestar*30;

	// 1. Završni semestar, mora očistiti sve
	if ($semestar==$studij_trajanje && $obavezni_pao==0 && $ects_polozio>=$ects_ukupno) {
		// Jedan semestar nosi 30 ECTSova
		$ima_uslov=true;

	// 2. Nije završni semestar, nedostaje jedan ili nijedan predmet (ali samo sa zadnje odslušane godine studija)
	} else if ($semestar<$studij_trajanje && $obavezni_pao<=1 && $nize_godine==0) {

		// 2A. Položeni svi obavezni predmeti. 
		// Da li nedostaje više od jednog izbornog? Izborni slotovi nose 4-6 ECTS
		if ($obavezni_pao==0 && $ects_polozio>$ects_ukupno-8) {
			$ima_uslov=true;

		// 2B. Nedostaje jedan obavezan predmet. Izbornih treba biti nula
		} else if ($obavezni_pao==1 && $ects_polozio+$obavezni_pao_ects>=$ects_ukupno) {
			$ima_uslov=true;
		}

	}

	return $ima_uslov;
}



// Funkcija provjerava da li ima slobodnog mjesta na predmetu kao izbornom ili u koliziji

// Tabela ugovoroucenju_kapacitet sadrzi polja:
//  - kapacitet (ukupan dozvoljeni broj studenata) 
//  - kapacitet_ekstra (dozvoljeni broj preko onih kojima je predmet obavezan)
//   TODO: kapacitet_drugi_odsjek (maksimalan broj studenata sa drugog odsjeka)

// Parametri:
//  - $predmet - ID predmeta koji student zeli izabrati
//  - $zagodinu - ID akademske godine
//  - $najnoviji_plan - ID NPP za koji gledamo da li je predmet obavezan 
//    (to se da zakljuciti iz parametra $zagodinu, ali bi potencijalno usporilo upite?)

// Povratna vrijednost: 0 - nema vise mjesta, 1 - ima jos mjesta

// TODO: studenti sa maticnog odsjeka koji biraju predmet kao izborni trebaju imati prednost u odnosu 
// na koliziju, ali trenutno ne vidim kako to izvesti a da nekome ne postane invalidan odabir predmeta

function provjeri_kapacitet($predmet, $zagodinu, $najnoviji_plan) {
	global $userid; // TODO ovo treba biti parametar $student...
//	print "Provjeravam kapacitet $predmet za godinu $zagodinu<br>";
	// Provjera kapaciteta
	$q112 = db_query("SELECT kapacitet, kapacitet_ekstra FROM ugovoroucenju_kapacitet WHERE predmet=$predmet AND akademska_godina=$zagodinu");
	if (db_num_rows($q112)>0) {
		$kapacitet = db_result($q112,0,0);
		$kapacitet_ekstra = db_result($q112,0,0);
		
		// Koliko je studenata izabralo predmet kao izborni?
		$q113 = db_query("SELECT COUNT(*) FROM ugovoroucenju as uou, ugovoroucenju_izborni as uoi WHERE uou.akademska_godina=$zagodinu AND uou.student!=$userid AND uoi.ugovoroucenju=uou.id AND uoi.predmet=$predmet AND (SELECT COUNT(*) FROM konacna_ocjena AS ko WHERE ko.predmet=$predmet AND ko.ocjena>5 AND ko.student=uou.student)=0");
		$popunjeno = db_result($q113,0,0);
		
		// Koliko sluša na koliziju?
		$q114 = db_query("SELECT COUNT(*) FROM kolizija WHERE akademska_godina=$zagodinu AND predmet=$predmet AND student!=$userid");
		$popunjeno += db_result($q114,0,0);
		
		if ($kapacitet_ekstra != 0 && $popunjeno >= $kapacitet_ekstra)
			return 0;
		
		// Koliko studenata slusa predmet kao obavezan na svom studiju?
		$q115 = db_query("SELECT ps.studij, psp.semestar FROM plan_studija ps, plan_studija_predmet psp, pasos_predmeta pp WHERE ps.id=$najnoviji_plan AND psp.plan_studija=$najnoviji_plan AND psp.pasos_predmeta=pp.id AND pp.predmet=$predmet AND psp.obavezan=1");
		if (db_fetch2($q115, $studij, $semestar)) {
			$q116 = db_query("SELECT COUNT(*) FROM ugovoroucenju WHERE akademska_godina=$zagodinu AND studij=$studij AND semestar=$semestar");
			$popunjeno += db_result($q116,0,0);
		}
//		print "popunjeno $popunjeno<br>";
		
		if ($kapacitet != 0 && $popunjeno >= $kapacitet) 
			return 0;
	}
	return 1;
}


// Da li je student ostvario preduvjete za dati predmet?
// Povratna vrijednost: niz IDova predmeta koji su preduvjet a nisu položeni

function provjeri_preduvjete($predmet, $student, $najnoviji_plan) {
	$rezultat = array();

	$q100 = db_query("SELECT preduvjet FROM preduvjeti WHERE predmet=$predmet");
	while (db_fetch1($q100, $preduvjet)) {
		// Da li je preduvjet po najnovijem planu na istoj ili višoj godini kao predmet?
		$semestar = db_get("SELECT psp.semestar FROM plan_studija_predmet psp, pasos_predmeta pp WHERE psp.plan_studija=$najnoviji_plan AND psp.pasos_predmeta=pp.id AND pp.predmet=$predmet AND psp.obavezan=1");
		if ($semestar === false) 
			$semestar = db_get("SELECT psp.semestar FROM plan_studija_predmet psp, plan_izborni_slot pis, pasos_predmeta pp WHERE psp.plan_studija=$najnoviji_plan AND psp.obavezan=0 AND psp.plan_izborni_slot=pis.id AND pis.predmet=$predmet");
		if ($semestar === false) { niceerror("Predmet nije pronađen u planu i programu"); return; }
		$godina_predmeta = ($semestar+1)/2;

		$semestar = db_get("SELECT psp.semestar FROM plan_studija_predmet psp, pasos_predmeta pp WHERE psp.plan_studija=$najnoviji_plan AND psp.pasos_predmeta=pp.id AND pp.predmet=$preduvjet AND psp.obavezan=1");
		if ($semestar === false) 
			$semestar = db_get("SELECT psp.semestar FROM plan_studija_predmet psp, plan_izborni_slot pis, pasos_predmeta pp WHERE psp.plan_studija=$najnoviji_plan AND psp.obavezan=0 AND psp.plan_izborni_slot=pis.id AND pis.predmet=$preduvjet");
		if ($semestar === false) { niceerror("Preduvjet nije pronađen u planu i programu"); return; }
		$godina_preduvjeta = ($semestar+1)/2;

		if ($godina_preduvjeta >= $godina_predmeta) continue;

		// Da li je položio?
		$br_ocjena = db_get("SELECT COUNT(*) FROM konacna_ocjena WHERE student=$student AND predmet=$preduvjet AND ocjena>5");
		if ($br_ocjena == 0) array_push($rezultat, $preduvjet);
	}
	return $rezultat;
}

?>
