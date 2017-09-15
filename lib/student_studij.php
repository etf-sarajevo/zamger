<?

// LIB/STUDENT_STUDIJ - funkcije vezane za studij (upis na studij, ispis, uslov itd.)


require_once("lib/plan_studija.php");

// Funkcija koja provjerava da li je student dao uslov za upis na sljedecu godinu studija, odnosno koliko predmeta nije položeno
// - Parametar $ag omogućuje da se uslov posmatra u nekom trenutku iz prošlosti. Vrijednost je godina u kojoj je student bio upisan,
// provjerava se da li je student imao uslov po završetku te godine
// - Funkcija vraća boolean vrijednost
// Globalni niz $zamger_predmeti_pao sadrži id-eve predmeta koji nisu položeni
function ima_li_uslov($student, $ag=0) {
	$ima_uslov=false;

	// Odredjujemo studij i semestar
	if ($ag==0) {
		$q10 = db_query("select ss.studij, ss.semestar, ts.trajanje, ss.plan_studija from student_studij as ss, studij as s, tipstudija as ts where ss.student=$student and ss.studij=s.id and s.tipstudija=ts.id order by ss.akademska_godina desc, ss.semestar desc limit 1");
		if (db_num_rows($q10)<1) 
			return true; // Nikad nije bio student, ima uslov za prvu godinu ;)
	} else {
		$q10 = db_query("select ss.studij, ss.semestar, ts.trajanje, ss.plan_studija from student_studij as ss, studij as s, tipstudija as ts where ss.student=$student and ss.studij=s.id and s.tipstudija=ts.id and ss.akademska_godina=$ag order by ss.semestar desc limit 1");
		if (db_num_rows($q10)<1) 
			return false; // Nije bio student u datoj akademskoj godini
	}

	db_fetch4($q10, $studij, $semestar, $studij_trajanje, $plan_studija);
	if ($semestar%2==1) $semestar++; // zaokružujemo na parni semestar
	
	// Ako je definisan plan studija, koristimo ga
	if ($plan_studija > 0)
		$ima_uslov = ima_li_uslov_plan($student, $ag, $studij, $semestar, $studij_trajanje, $plan_studija);
	
	// U suprotnom, uslov se određuje na osnovu predmeta koje je student ranije slušao
	// Uz pokušaj da se predvidi i mogućnost promjene izbornog predmeta
	else
		$ima_uslov = ima_li_uslov_predmeti($student, $ag, $studij, $semestar, $studij_trajanje);
	
	return $ima_uslov;
}


// Određivanje uslova na osnovu plana studija
function ima_li_uslov_plan($student, $ag, $studij, $semestar, $studij_trajanje, $plan_studija) {
	global $zamger_predmeti_pao, $zamger_pao_ects, $conf_uslov_predmeta, $conf_uslov_ects_kredita;
	global $cache_planova_studija;
	
	$zamger_predmeti_pao = $pis_bio = $nepoznat_izborni = array();
	$nize_godine = $obavezni_pao = $zamger_pao_ects = $izborni_pao = 0;
	$nepoznat_predmet_id = -1;
	
	// Svi predmeti koje je student slušao - zatrebaće nam kasnije
	if (!isset($cache_planova_studija)) $cache_planova_studija = array();
	if (!array_key_exists($plan_studija, $cache_planova_studija)) 
		$cache_planova_studija[$plan_studija] = predmeti_na_planu($plan_studija);
	
	// Svi predmeti koje je student položio
	$student_polozio = db_query_vassoc("SELECT pk.predmet, ko.ocjena
	FROM student_predmet sp, ponudakursa pk
	LEFT JOIN konacna_ocjena ko ON ko.predmet=pk.predmet AND ko.student=$student
	WHERE sp.student=$student AND sp.predmet=pk.id");
	
	// Predmeti koje je student slušao s drugih odsjeka
	$drugi_odsjek = array();
	foreach($student_polozio as $predmet => $ocjena) {
		$pronasao = false;
		foreach($cache_planova_studija[$plan_studija] as $slog) {
			if ($slog['obavezan'] == 1 && $slog['predmet']['id'] == $predmet) {
				$pronasao = true;
				break;
			}
			if ($slog['obavezan'] == 0) {
				foreach($slog['predmet'] as $slog_predmet) {
					if ($slog_predmet['id'] == $predmet) {
						$pronasao = true;
						break;
					}
				}
				if ($pronasao) break;
			}
		}
		if (!$pronasao) {
			$drugi_odsjek[$predmet] = db_query_assoc("SELECT pk.semestar semestar, pp.ects ects, pp.naziv naziv
				FROM student_predmet sp, ponudakursa pk, pasos_predmeta pp 
				WHERE sp.student=$student AND sp.predmet=pk.id AND pk.predmet=$predmet AND pk.predmet=pp.predmet");
		}
	}
	
	// Sada prolazimo kroz plan studija i provjeravamo šta je položeno
	foreach($cache_planova_studija[$plan_studija] as $slog) {
		if ($slog['semestar'] > $semestar) continue;
		if ($slog['obavezan'] == 1) {
			$predmet = $slog['predmet']['id'];
			$polozio = (array_key_exists($predmet, $student_polozio) && $student_polozio[$predmet]);
			
			if (!$polozio) {
				$zamger_predmeti_pao[$predmet] = $slog['predmet']['naziv'];

				// Predmet se ne može prenijeti preko dvije godine
				if ($slog['semestar'] < $semestar-1) $nize_godine++;

				// Ako je obavezan, situacija je jasna
				$obavezni_pao++;
				$zamger_pao_ects += $slog['predmet']['ects'];
			}
		} else {
			// Kod izbornih predmeta moramo računati da se isti slot može ponavljati N puta, 
			// što znači da student mora položiti N predmeta iz tog slota
			$polozio_izbornih = 0;
			$izborni_predmeti_pao = array();
			foreach($slog['predmet'] as $slog_predmet) {
				$predmet = $slog_predmet['id'];
				$polozio = (array_key_exists($predmet, $student_polozio) && $student_polozio[$predmet]);
				if ($polozio)
					$polozio_izbornih++;
				else if (array_key_exists($predmet, $student_polozio))
					$izborni_predmeti_pao[] = $slog_predmet;
			}
			
			// Nije položio dovoljno predmeta iz ovog slota
			if ($polozio_izbornih < $slog['ponavljanja']) {
				foreach($izborni_predmeti_pao as $slog_predmet) {
					$zamger_predmeti_pao[$predmet] = $slog_predmet['naziv'];
					$zamger_pao_ects += $slog_predmet['ects'];
					if ($slog['semestar'] < $semestar-1) $nize_godine++;
					
					$polozio_izbornih++;
					if ($polozio_izbornih == $slog['ponavljanja']) break;
				}
			}
			
			// Još uvijek nije dovoljno... tražimo predmete sa drugog odsjeka
			if ($polozio_izbornih < $slog['ponavljanja']) {
				foreach ($drugi_odsjek as $predmet => $podaci) {
					if ($podaci['semestar'] == $slog['semestar']) {
						$polozio = (array_key_exists($predmet, $student_polozio) && $student_polozio[$predmet]);
						if (!$polozio) {
							$zamger_predmeti_pao[$predmet] = $podaci['naziv'];
							$zamger_pao_ects += $podaci['ects'];
							if ($slog['semestar'] < $semestar-1) $nize_godine++;
						}
						
						$polozio_izbornih++;
						if ($polozio_izbornih == $slog['ponavljanja']) break;
					}
				}
			}
			
			if ($polozio_izbornih < $slog['ponavljanja']) {
				// Koristimo negativan ID za oznaku nepoznatog predmeta
				$zamger_predmeti_pao[$nepoznat_predmet_id--] = "(Nepoznat izborni predmet)";
			}
		}
	}
	
	
	// Predmet se ne može prenositi sa prve na treću godinu
	if ($nize_godine>0) return false;
	
	// Ako je završni semestar ne može se ništa prenijeti
	if ($semestar == $studij_trajanje && ($obavezni_pao > 0 || $izborni_pao > 0 || $zamger_pao_ects > 0)) return false;
	
	// Student ima uslov ako je pao <= $conf_uslov_predmeta ili ako svi nepoloženi 
	// krediti zajedno nose <= $conf_uslov_ects_kredita
	if ($obavezni_pao + $izborni_pao > $conf_uslov_predmeta && $zamger_pao_ects > $conf_uslov_ects_kredita) return false;
	return true;
}


// Određivanje uslova na osnovu predmeta koje je student slušao
function ima_li_uslov_predmeti($student, $ag, $studij, $semestar, $studij_trajanje) {
	global $zamger_predmeti_pao, $zamger_pao_ects, $conf_uslov_predmeta;

	$zamger_predmeti_pao = array();
	$obavezni_pao_ects = $obavezni_pao = $nize_godine = $ects_polozio = 0;
	
	// Od predmeta koje je slušao, koliko je pao?
	$q20 = db_query("select distinct pk.predmet, p.ects, pk.semestar, pk.obavezan, p.naziv from ponudakursa as pk, student_predmet as sp, predmet as p where sp.student=$student and sp.predmet=pk.id and pk.semestar<=$semestar and pk.studij=$studij and pk.predmet=p.id order by pk.semestar");
	while (db_fetch5($q20, $predmet, $ects, $predmet_semestar, $obavezan, $naziv)) {
		$polozio = db_get("select count(*) from konacna_ocjena where student=$student and predmet=$predmet and ocjena>5");
		if ($polozio) {
			$ects_polozio += $ects;
		} else {
			$zamger_predmeti_pao[$predmet] = $naziv;

			// Predmet se ne može prenijeti preko dvije godine
			if ($predmet_semestar < $semestar-1) $nize_godine++;

			// Ako je obavezan, situacija je jasna
			if ($obavezan) { 
				$obavezni_pao_ects += $ects;
				$zamger_pao_ects += $ects;
				$obavezni_pao++;

			// Za izborne možemo odrediti uslov samo preko ECTSa
			// pošto je tokom godina student mogao pokušavati razne izborne
			// predmete
			}
		}
	}

	// USLOV ZA UPIS
	// Prema aktuelnom zakonu može se prenijeti tačno $conf_uslov_predmeta predmeta, bez obzira na ECTS
	// No, na sljedeći ciklus studija se ne može prenijeti ništa
	$ects_ukupno = $semestar*30;

	// 1. Završni semestar, mora očistiti sve
	if ($semestar==$studij_trajanje && $obavezni_pao==0 && $ects_polozio>=$ects_ukupno) {
		// Jedan semestar nosi 30 ECTSova
		$ima_uslov=true;

	// 2. Nije završni semestar, nedostaje jedan ili nijedan predmet (ali samo sa zadnje odslušane godine studija)
	} else if ($semestar<$studij_trajanje && $obavezni_pao<=$conf_uslov_predmeta && $nize_godine==0) {

		// 2A. Položeni svi obavezni predmeti. 
		// Da li nedostaje više od jednog izbornog? Izborni slotovi nose 4-6 ECTS
		$izborni_ects_pao_max = ($conf_uslov_predmeta-$obavezni_pao) * 7; // maksimalno 7 kredita po predmetu
		
		if ($ects_polozio + $obavezni_pao_ects + $izborni_ects_pao_max >= $ects_ukupno) {
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
