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
	$nize_godine = $obaveznih_pao = $zamger_pao_ects = $izbornih_pao = 0;
	$nepoznat_predmet_id = -1;
	
	// Svi predmeti koje je student slušao - zatrebaće nam kasnije
	if (!isset($cache_planova_studija)) $cache_planova_studija = array();
	if (!array_key_exists($plan_studija, $cache_planova_studija)) 
		$cache_planova_studija[$plan_studija] = predmeti_na_planu($plan_studija);
	
	// Svi predmeti koje je student položio
	$student_polozio = db_query_vassoc("SELECT ko.predmet, ko.ocjena FROM konacna_ocjena ko WHERE ko.student=$student AND ko.ocjena>5");
	
	$student_slusao = db_query_varray("SELECT DISTINCT pk.predmet FROM ponudakursa pk, student_predmet sp WHERE sp.student=$student AND sp.predmet=pk.id");
	$student_pao = array();
	foreach($student_slusao as $predmet)
		if (!array_key_exists($predmet, $student_polozio)) $student_pao[] = $predmet;
	
	// Pokušavam filtrirati predmete sa ranijih ciklusa studija
	$godina_upisa = db_get("SELECT ss.akademska_godina FROM student_studij ss WHERE ss.student=$student AND ss.studij=$studij ORDER BY ss.akademska_godina LIMIT 1");
	
	// Predmeti koje je student položio s drugih odsjeka
	$drugi_odsjek = array();
	foreach($student_slusao as $predmet) {
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
				WHERE sp.student=$student AND sp.predmet=pk.id AND pk.predmet=$predmet AND pk.predmet=pp.predmet AND pk.akademska_godina>=$godina_upisa");
			if ($drugi_odsjek[$predmet] === false) unset($drugi_odsjek[$predmet]);
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
				$obaveznih_pao++;
				$zamger_pao_ects += $slog['predmet']['ects'];
			}
		} else {
			// Kod izbornih predmeta moramo računati da se isti slot može ponavljati N puta, 
			// što znači da student mora položiti N predmeta iz tog slota
			$polozio_izbornih_slot = $pao_izbornih_slot = 0;
			$izborni_predmeti_pao = array(); // IDovi izbornih predmeta koje je student nekada slušao a nije položio

			foreach($slog['predmet'] as $slog_predmet) {
				$predmet = $slog_predmet['id'];
				$polozio = (array_key_exists($predmet, $student_polozio) && $student_polozio[$predmet]);
				if ($polozio)
					$polozio_izbornih_slot++;
				else if (in_array($predmet, $student_pao))
					$izborni_predmeti_pao[] = $slog_predmet;
			}
			
			// Nije položio dovoljno predmeta iz ovog slota
			if ($polozio_izbornih_slot < $slog['ponavljanja']) {
				// Tražimo da li je slušao predmete sa drugog odsjeka u istom semestru
				foreach ($drugi_odsjek as $predmet => $podaci) {
					if ($podaci['semestar'] == $slog['semestar']) {
						$polozio = (array_key_exists($predmet, $student_polozio) && $student_polozio[$predmet]);
						if (!$polozio) {
							// Da li je student slušao i nešto sa matičnog odsjeka?
							if (count($izborni_predmeti_pao) > 0) {
								// Koji je posljednji slušao, predmet sa matičnog ili sa drugog odsjeka?
								$ag_drugi_odsjek = db_get("SELECT pk.akademska_godina FROM ponudakursa pk, student_predmet sp WHERE sp.student=$student AND sp.predmet=pk.id AND pk.predmet=$predmet");
								foreach($izborni_predmeti_pao as &$slog_izborni) {
									$ag_izborni = db_get("SELECT pk.akademska_godina FROM ponudakursa pk, student_predmet sp WHERE sp.student=$student AND sp.predmet=pk.id AND pk.predmet=" . $slog_izborni['id']);
									if ($ag_izborni > $ag_drugi_odsjek) {
										// Sa matičnog je noviji pa ćemo koristiti njega
										$zamger_predmeti_pao[$slog_izborni['id']] = $slog_izborni['naziv'];
										$zamger_pao_ects += $slog_izborni['ects'];
										
										unset($slog_izborni);
										
										$pao_izbornih_slot++;
										if ($polozio_izbornih_slot + $pao_izbornih_slot == $slog['ponavljanja']) break;
									}
								}
								// Napunili smo potreban broj predmeta sa matičnog odsjeka
								if ($polozio_izbornih_slot + $pao_izbornih_slot == $slog['ponavljanja']) break;
							}
							
							// Student nije slušao predmet sa matičnog odsjeka ili je ovaj noviji
							$zamger_predmeti_pao[$predmet] = $podaci['naziv'];
							$zamger_pao_ects += $podaci['ects'];
							$pao_izbornih_slot++;
							if ($slog['semestar'] < $semestar-1) $nize_godine++;
						} else
							$polozio_izbornih_slot++;
						
						unset($drugi_odsjek[$predmet]);
						
						if ($polozio_izbornih_slot + $pao_izbornih_slot == $slog['ponavljanja']) break;
					}
				}
			}
			
			// Popunićemo ostatak spiska izbornim predmetima iz NPP za koje je evidentirano da je slušao a nije položio
			if ($polozio_izbornih_slot + $pao_izbornih_slot < $slog['ponavljanja']) {
				foreach($izborni_predmeti_pao as &$slog_predmet) {
					$zamger_predmeti_pao[$slog_predmet['id']] = $slog_predmet['naziv'];
					$zamger_pao_ects += $slog_predmet['ects'];
					if ($slog['semestar'] < $semestar-1) $nize_godine++;
					
					unset($slog_predmet);
					
					$pao_izbornih_slot++;
					if ($polozio_izbornih_slot + $pao_izbornih_slot == $slog['ponavljanja']) break;
				}
			}
			
			// Za preostali broj ponavljanja nemamo pojma pa ćemo ih dodati u petlji
			while ($polozio_izbornih_slot + $pao_izbornih_slot < $slog['ponavljanja']) {
				// Koristimo negativan ID za oznaku nepoznatog predmeta
				$zamger_predmeti_pao[$nepoznat_predmet_id--] = "(Nepoznat izborni predmet)";
				$pao_izbornih_slot++;
			}
			$izbornih_pao += $pao_izbornih_slot;
		}
	}
	
	
	// Predmet se ne može prenositi sa prve na treću godinu
	if ($nize_godine>0) return false;
	
	// Ako je završni semestar ne može se ništa prenijeti
	if ($semestar == $studij_trajanje && ($obaveznih_pao > 0 || $izbornih_pao > 0 || $zamger_pao_ects > 0)) return false;
	
	// Student ima uslov ako je pao <= $conf_uslov_predmeta ili ako svi nepoloženi 
	// krediti zajedno nose <= $conf_uslov_ects_kredita
	if ($obaveznih_pao + $izbornih_pao > $conf_uslov_predmeta && $zamger_pao_ects > $conf_uslov_ects_kredita) return false;
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
//  - kapacitet_izborni (dozvoljeni broj preko onih kojima je predmet obavezan)
//  - kapacitet_kolizija (dozvoljeni broj studenata koji mogu uzeti predmet na koliziju)
//  - kapacitet_drugi_odsjek (dozvoljeni broj studenata sa drugog odsjeka koji mogu uzeti predmet kao izborni)
//  - drugi_odsjek_zabrane (studiji kojima je zabranjeno da biraju ovaj predmet kao izborni sa drugog odsjeka)
//
// Za sva polja vrijednost 0 znači da niko ne može izabrati, a -1 da nema ograničenja

// Parametri:
//  - $student - ID studenta
//  - $predmet - ID predmeta koji student zeli izabrati
//  - $ag - ID akademske godine
//  - $studij - ID studija na koji je student upisan / želi upisati
//  - $kolizija - student želi uzeti predmet na koliziju
//  - $debug - ako je true, ispisuje se razlog

// Povratna vrijednost: 0 - nema vise mjesta, 1 - ima jos mjesta

// TODO: studenti sa maticnog odsjeka koji biraju predmet kao izborni trebaju imati prednost u odnosu 
// na koliziju i drugi odsjek, ali trenutno ne vidim kako to izvesti a da nekome ne postane invalidan odabir predmeta

function provjeri_kapacitet($student, $predmet, $ag, $studij, $kolizija = false, $debug = false) {
	// Provjera kapaciteta
	$q100 = db_query("SELECT kapacitet, kapacitet_izborni, kapacitet_kolizija, kapacitet_drugi_odsjek, drugi_odsjek_zabrane FROM ugovoroucenju_kapacitet WHERE predmet=$predmet AND akademska_godina=$ag");
	if (db_fetch5($q100, $kapacitet, $kapacitet_izborni, $kapacitet_kolizija, $kapacitet_drugi_odsjek, $drugi_odsjek_zabrane)) {
		// Ako je student već položio predmet ne provjeravamo kapacitet
		$polozio = db_get("SELECT COUNT(*) FROM konacna_ocjena WHERE student=$student AND predmet=$predmet AND ocjena>5");
		if ($polozio) return 1;
		
		// Predmet ne ide
		if ($kapacitet == 0) {
			if ($debug) print "Predmet ne ide.<br>\n";
			return 0;
		}
		if ($kolizija && $kapacitet_kolizija == 0) {
			if ($debug) print "Predmet ne ide u koliziji.<br>\n";
			return 0;
		}
	
		$broj_obavezni = $broj_izborni_maticno = $broj_kolizija = $broj_drugi_odsjek = 0;
	
		// Za koji studij je predmet obavezan (ako ijedan?)
		$maticni_studij = db_get("SELECT ps.studij FROM plan_studija ps, plan_studija_predmet psp, pasos_predmeta pp WHERE psp.plan_studija=ps.id AND psp.pasos_predmeta=pp.id AND pp.predmet=$predmet ORDER BY ps.godina_vazenja DESC LIMIT 1");
		if (!$maticni_studij) {
			// Na kojem studiju je izborni?
			$maticni_studij = db_get("SELECT ps.studij FROM plan_studija ps, plan_studija_predmet psp, plan_izborni_slot pis, pasos_predmeta pp WHERE psp.plan_studija=ps.id AND psp.plan_izborni_slot=pis.id AND pis.pasos_predmeta=pp.id AND pp.predmet=$predmet ORDER BY ps.godina_vazenja DESC LIMIT 1");
			
			// Ima li kapaciteta kao izborni
			if ($kapacitet_izborni == 0) {
				if ($debug) print "Predmet ne ide kao izborni.<br>\n";
				return 0;
			}
		} else
			// Broj studenata koji slušaju predmet na matičnom odsjeku kao obavezni
			$broj_obavezni = db_get("SELECT COUNT(*) FROM ugovoroucenju WHERE studij=$maticni_studij AND akademska_godina=$ag AND student!=$student");
		
		
		// Da li je zabranjeno za druge odsjeke?
		if ($maticni_studij != $studij) {
			if ($kapacitet_drugi_odsjek == 0)  {
				if ($debug) print "Predmet ne ide za druge studije.<br>\n";
				return 0;
			}
			// Da li je na spisku zabrana
			$zabrane = explode(",", $drugi_odsjek_zabrane);
			if (in_array($studij, $zabrane)) {
				if ($debug) print "Studij $studij je na spisku zabrana.<br>\n";
				return 0;
			}
		}
		
		// Povremeno provjeravamo da li je kapacitet već prekoračen da uštedimo upite
		if ($kapacitet != -1 && $broj_obavezni >= $kapacitet) {
			if ($debug) print "Popunjen opšti kapacitet.<br>\n";
			return 0;
		}
		
		// Koliko je studenata izabralo predmet kao izborni na matičnom odsjeku?
		$broj_izborni_maticno = db_get("SELECT COUNT(*) FROM ugovoroucenju as uou, ugovoroucenju_izborni as uoi WHERE uou.akademska_godina=$ag AND uou.studij=$maticni_studij AND uou.student!=$student AND uoi.ugovoroucenju=uou.id AND uoi.predmet=$predmet AND (SELECT COUNT(*) FROM konacna_ocjena AS ko WHERE ko.predmet=$predmet AND ko.ocjena>5 AND ko.student=uou.student)=0");
		
		// Povremeno provjeravamo da li je kapacitet već prekoračen da uštedimo upite
		if ($kapacitet != -1 && $broj_obavezni+$broj_izborni_maticno >= $kapacitet) {
			if ($debug) print "Popunjen opšti kapacitet.<br>\n";
			return 0;
		}
		if ($kapacitet_izborni != -1 && $broj_izborni_maticno >= $kapacitet_izborni) {
			if ($debug) print "Popunjen izborni kapacitet.<br>\n";
			return 0;
		}
		
		
		// Koliko sluša na koliziju?
		$broj_kolizija = db_get("SELECT COUNT(*) FROM kolizija WHERE akademska_godina=$ag AND predmet=$predmet AND student!=$student");
		
		// Povremeno provjeravamo da li je kapacitet već prekoračen da uštedimo upite
		if ($kapacitet != -1 && $broj_obavezni+$broj_izborni_maticno+$broj_kolizija >= $kapacitet) {
			if ($debug) print "Popunjen opšti kapacitet.<br>\n";
			return 0;
		}
		if ($kapacitet_izborni != -1 && $broj_izborni_maticno+$broj_kolizija >= $kapacitet_izborni) {
			if ($debug) print "Popunjen izborni kapacitet.<br>\n";
			return 0;
		}
		if ($kolizija && $kapacitet_kolizija != -1 && $broj_kolizija >= $kapacitet_kolizija) {
			if ($debug) print "Popunjen kapacitet za koliziju.<br>\n";
			return 0;
		}
		
		
		
		// Koliko je studenata izabralo predmet kao izborni na tuđem odsjeku?
		$broj_drugi_odsjek = db_get("SELECT COUNT(*) FROM ugovoroucenju as uou, ugovoroucenju_izborni as uoi WHERE uou.akademska_godina=$ag AND uou.studij!=$maticni_studij AND uou.student!=$student AND uoi.ugovoroucenju=uou.id AND uoi.predmet=$predmet AND (SELECT COUNT(*) FROM konacna_ocjena AS ko WHERE ko.predmet=$predmet AND ko.ocjena>5 AND ko.student=uou.student)=0");
		
		// Konačna provjera
		if ($kapacitet != -1 && $broj_obavezni+$broj_izborni_maticno+$broj_kolizija+$broj_drugi_odsjek >= $kapacitet) {
			if ($debug) print "Popunjen opšti kapacitet.<br>\n";
			return 0;
		}
		if ($kapacitet_izborni != -1 && $broj_izborni_maticno+$broj_kolizija+$broj_drugi_odsjek >= $kapacitet_izborni) {
			if ($debug) print "Popunjen izborni kapacitet.<br>\n";
			return 0;
		}
		if ($maticni_studij != $studij && $kapacitet_drugi_odsjek != -1 && $broj_drugi_odsjek >= $kapacitet) {
			if ($debug) print "Popunjen kapacitet za drugi odsjek.<br>\n";
			return 0;
		}
	}
	// Ako nema sloga u bazi znači da ništa nije ograničeno
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
			$semestar = db_get("SELECT psp.semestar FROM plan_studija_predmet psp, plan_izborni_slot pis, pasos_predmeta pp WHERE psp.plan_studija=$najnoviji_plan AND psp.obavezan=0 AND psp.plan_izborni_slot=pis.id AND pis.pasos_predmeta=pp.id AND pp.predmet=$predmet");
		if ($semestar === false) { niceerror("Predmet nije pronađen u planu i programu"); return; }
		$godina_predmeta = ($semestar+1)/2;

		$semestar = db_get("SELECT psp.semestar FROM plan_studija_predmet psp, pasos_predmeta pp WHERE psp.plan_studija=$najnoviji_plan AND psp.pasos_predmeta=pp.id AND pp.predmet=$preduvjet AND psp.obavezan=1");
		if ($semestar === false) 
			$semestar = db_get("SELECT psp.semestar FROM plan_studija_predmet psp, plan_izborni_slot pis, pasos_predmeta pp WHERE psp.plan_studija=$najnoviji_plan AND psp.obavezan=0 AND psp.plan_izborni_slot=pis.id AND pis.pasos_predmeta=pp.id AND pp.predmet=$preduvjet");
		if ($semestar === false) { niceerror("Preduvjet $preduvjet za predmet $predmet nije pronađen u planu i programu"); return; }
		$godina_preduvjeta = ($semestar+1)/2;

		if ($godina_preduvjeta >= $godina_predmeta) continue;

		// Da li je položio?
		$br_ocjena = db_get("SELECT COUNT(*) FROM konacna_ocjena WHERE student=$student AND predmet=$preduvjet AND ocjena>5");
		if ($br_ocjena == 0) array_push($rezultat, $preduvjet);
	}
	return $rezultat;
}

?>
