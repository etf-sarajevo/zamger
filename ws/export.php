<?

// WS/EXPORT - web servis za izvoz podataka u aplikaciju trećih lica



function ws_export() {
	require("lib/ws.php");

	global $userid, $user_siteadmin, $user_studentska;
	global $conf_export_format, $conf_export_isss_url, $conf_export_isss_id_fakulteta, $conf_export_isss_kreiraj_ispite;

	if (!$user_siteadmin && !$user_studentska) {
		print json_encode( array( 'success' => 'false', 'code' => 'ERR002', 'message' => 'Permission denied' ) );
		return;
	}
	
	if ($conf_export_format == "isss-newws") {
		$rezultat = array( 'success' => 'true', 'data' => array() );
		
		$tip = param('tip');
		$akcija = param('akcija');
		
		if ($tip == "ocjene" || $tip == "popravi_datum_isss") {
			$url = $conf_export_isss_url . "dodajOcjenu.php";
			
			$id_studenta = int_param('student');
			$id_predmeta = int_param('predmet');
			
			$odgovor = array();

			// Određujemo podatke studenta
			$student = db_query_assoc("SELECT ime, prezime, brindexa FROM osoba WHERE id=$id_studenta");
			if (!$student) { 
				print json_encode( array( 'success' => 'false', 'code' => 'ERR004', 'message' => 'Nepoznat student ', 'student' => $id_studenta ) );
				return; 
			}
			
			// Određujemo naziv predmeta
			$podaci_ocjene = db_query_assoc("SELECT ko.ocjena, ag.naziv godina, pp.naziv predmet, UNIX_TIMESTAMP(ko.datum_u_indeksu) datum, ss.studij studij
				FROM pasos_predmeta pp, konacna_ocjena ko, akademska_godina ag, student_studij ss
				WHERE ko.student=$id_studenta AND ko.predmet=$id_predmeta AND ko.pasos_predmeta=pp.id AND ko.akademska_godina=ag.id AND ss.student=$id_studenta AND ss.akademska_godina=ag.id
				ORDER BY ss.semestar LIMIT 1");
			if (!$podaci_ocjene) { 
				print json_encode( array( 'success' => 'false', 'code' => 'ERR004', 'message' => 'Nije evidentirana ocjena za predmet' ) );
				return; 
			}
			
			$podaci_ocjene['datum'] = date("Y-m-d", $podaci_ocjene['datum']);
			
			// Koristimo broj 11 kao oznaku za ocjenu IO
			if ($podaci_ocjene['ocjena'] == 11) $podaci_ocjene['ocjena'] = "IO";
			
			$isss_data = array ( 
				"predmet" => $podaci_ocjene['predmet'], 
				"id_fakulteta" => $conf_export_isss_id_fakulteta, 
				"akademska_godina" => $podaci_ocjene['godina'],
				"kreiraj_ispite" => $conf_export_isss_kreiraj_ispite,
				"ocjene" => array(
					array( 
						"ime" => $student['ime'],
						"prezime" => $student['prezime'],
						"brindexa" => $student['brindexa'],
						"ocjena" => $podaci_ocjene['ocjena'],
						"datum" => $podaci_ocjene['datum']
					)
				)
			);
			
			if ($tip == "popravi_datum_isss") {
				unset($isss_data['ocjene'][0]['ocjena']); // Ne želimo da slučajno promijenimo ocjenu
				$isss_data['akcija'] = "promijeni";
				
				$isss_msg = array();
				$isss_msg['data'] = json_encode($isss_data);
				
				//print_r($isss_data);
				
				$isss_result = json_request($url, $isss_msg, "POST");
				
				if ($isss_result === FALSE || $isss_result['success'] === "false") {
					print json_encode( array( 'success' => 'false', 'code' => 'ERR005', 'message' => 'ISSS servis vratio grešku', 'data' => $isss_result ) );
					return; 
				}
				
				foreach($isss_result['warnings'] as $warning) {
					if ($warning['code'] == 'unknown_subject') {
						$odgovor['tekst'] = 'Nepoznat predmet';
						$odgovor['status'] = 'greska';
						break;
					}
					else if ($warning['code'] == 'unknown_student') {
						$odgovor['tekst'] = 'Nepoznat student';
						$odgovor['status'] = 'greska';
						break;
					}
					else {
						$odgovor['tekst'] = 'Nepoznato upozorenje '.$warning['code'];
						$odgovor['status'] = 'greska';
						break;
					}
				}
			}
			
			if ($akcija == "provjera") {
				$isss_data['test'] = "true";
			
				// Najprije preuzimamo ocjenu iz ISSSa da vidimo da li već postoji - da li je netačna
				$isss_data['akcija'] = "vrati";
				
				$isss_msg = array();
				$isss_msg['data'] = json_encode($isss_data);
				
				//print_r($isss_data);
				
				$isss_result = json_request($url, $isss_msg, "POST");
				
				if ($isss_result === FALSE || $isss_result['success'] === "false") {
					print json_encode( array( 'success' => 'false', 'code' => 'ERR005', 'message' => 'ISSS servis vratio grešku', 'data' => $isss_result ) );
					return; 
				}
				
				foreach($isss_result['warnings'] as $warning) {
					if ($warning['code'] == 'unknown_subject') {
						$odgovor['tekst'] = 'Nepoznat predmet';
						$odgovor['status'] = 'greska';
						break;
					}
					else if ($warning['code'] == 'unknown_student') {
						$odgovor['tekst'] = 'Nepoznat student';
						$odgovor['status'] = 'greska';
						break;
					}
					else {
						$odgovor['tekst'] = 'Nepoznato upozorenje '.$warning['code'];
						$odgovor['status'] = 'greska';
						break;
					}
				}
				
				// Provjeravamo da li ocjena postoji i da li su podaci jednaki
				// Necemo nastavljati u suprotnom jer bi nova ocjena mogla "pregaziti" staru
				if (empty($odgovor)) {
					if(!array_key_exists('data', $isss_result) || empty($isss_result['data'])) {
						$odgovor['tekst'] = 'Servis nije vratio ocjenu';
						$odgovor['status'] = 'greska';
					} else {
						$ocjena = $isss_result['data'][0];
						// Ako ocjene nema servis može vratiti / ili NO
						if ($ocjena['ocjena'] != "/" && $ocjena['ocjena'] != "NO") {
							if ($podaci_ocjene['ocjena'] != $ocjena['ocjena']) {
								$odgovor['tekst'] = "Unesena je različita ocjena - ". $ocjena['ocjena'];
								$odgovor['status'] = 'greska';
							}
							else if ($podaci_ocjene['datum'] != $ocjena['datum']) {
								$odgovor['tekst'] = "Unesen je različit datum - ". $ocjena['datum'] . " - ".$podaci_ocjene['datum'];
								$odgovor['status'] = 'greska';
							}
							else {
								$odgovor['tekst'] = "Ocjena već unesena";
								$odgovor['status'] = 'ok';
							}
						}
					}
				}
			}
			
			// Ocjena nije ranije unesena, možemo je unijeti
			// (da bih smanjio indentaciju, pretpostavljam da je $odgovor prazan ako ocjena nije unesena)
			if (empty($odgovor) && $tip != "popravi_datum_isss") {
				$isss_data['akcija'] = "upisi";
				
				$isss_msg = array();
				$isss_msg['data'] = json_encode($isss_data);
				
				//print_r($isss_data);
				
				$isss_result = json_request($url, $isss_msg, "POST");
				
				if ($isss_result === FALSE || $isss_result['success'] === "false") {
					print json_encode( array( 'success' => 'false', 'code' => 'ERR005', 'message' => 'ISSS servis vratio grešku', 'data' => $isss_result ) );
					return; 
				}
				
				$odgovor = array();
				foreach($isss_result['warnings'] as $warning) {
					// Ovo je moralo već biti provjereno, ali zbog race conditiona provjeravamo i to
					if ($warning['code'] == 'grade_exists') {
						if ($warning['mark'] == $podaci_ocjene['ocjena']) {
							$odgovor['tekst'] = "Ocjena već unesena";
							$odgovor['status'] = 'ok';
						} else {
							$odgovor['tekst'] = "Unesena je različita ocjena - ". $warning['mark'];
							$odgovor['status'] = 'greska';
						}
						break;
					}
					else if ($warning['code'] == 'student_not_enrolled') {
						$odgovor['tekst'] = "Student nije upisan na studij";
						$odgovor['status'] = 'greska';
						break;
					}
					else if ($warning['code'] == 'course_not_offered') {
						$odgovor['tekst'] = "Predmet nije u ponudi";
						$odgovor['status'] = 'greska';
						break;
					}
					else if ($warning['code'] == 'no_exam_open') {
						$odgovor['tekst'] = "Nije otvoren ispit za predmet";
						$odgovor['status'] = 'greska';
						break;
					} 
					else {
						$odgovor['tekst'] = 'Nepoznato upozorenje '.$warning['code'];
						$odgovor['status'] = 'greska';
						break;
					}
				}
			}
			
			if (empty($odgovor)) {
				if ($akcija == "provjera") {
					$odgovor['tekst'] = "Moguće upisati ocjenu";
					$odgovor['status'] = 'nastaviti';
				} else if ($tip == "popravi_datum_isss") {
					$odgovor['tekst'] = "Datum popravljen";
					$odgovor['status'] = 'ok';
				} else {
					$odgovor['tekst'] = "Ocjena upisana";
					$odgovor['status'] = 'ok';
				}
			}
			$rezultat['data'] = $odgovor;
		}
		
		
		if ($tip == "popravi_datum_isss") {
		
		}
		
		
		if ($tip == "upis_prva") {
			$url = $conf_export_isss_url . "upisiPrva.php";
			
			$id_studenta = int_param('student');
			$id_studija = int_param('studij');
			$id_godine = int_param('godina');
			
			// Naziv studija i akademske godine
			$podaci_studija = db_query_assoc("SELECT s.id id_studija, ag.naziv godina, ss.nacin_studiranja nacin 
				FROM student_studij ss, studij s, akademska_godina ag, nacin_studiranja ns 
				WHERE ss.student=$id_studenta AND ss.studij=s.id AND s.id=$id_studija and ss.akademska_godina=ag.id AND ag.id=$id_godine");
			if (!$podaci_studija) { 
				print json_encode( array( 'success' => 'false', 'code' => 'ERR006', 'message' => 'Student nije upisan u toj godini', 'student' => $id_studenta ) );
				return; 
			}
			
			$isss_data = array ( 
				"id_fakulteta" => $conf_export_isss_id_fakulteta, 
				"akademska_godina" => $podaci_studija['godina'],
				"upisi" => array()
			);
			if ($akcija == "provjera") $isss_data['test'] = "true";
			
			$podaci_studenta = daj_podatke_studenta($id_studenta);
			if ($podaci_studenta === false) return;
			
			// Način studiranja iz šifrarnika
			$podaci_studenta['nacin_studiranja'] = zamger2isss('nacin_studiranja', $podaci_studija['nacin']);
			
			// Studij
			$podaci_studenta['studij'] = 0;
			if ($id_godine<10)
				$podaci_studenta['studij'] = zamger2isss("studij_stari", $podaci_studija['id_studija']);
			else
				$podaci_studenta['studij'] = zamger2isss("studij_novi", $podaci_studija['id_studija']);
			
			if ($podaci_studenta['studij'] == 0) {
				$odgovor['tekst'] = 'Studij nije podržan za upis na ISSS';
				$odgovor['status'] = 'greska';
				// Prekidamo sve
				$rezultat['data'] = $odgovor;
				print json_encode($rezultat);
				return;
			}
			
			// Pripremamo podatke za web servis
			$isss_data['upisi'][] = $podaci_studenta;
			
			$isss_msg = array();
			$isss_msg['data'] = json_encode($isss_data);
			
			//print_r($isss_data);
			
			$isss_result = json_request($url, $isss_msg, "POST");
			
			if ($isss_result === FALSE || $isss_result['success'] === "false" || $isss_result['success'] === false) {
				print json_encode( array( 'success' => 'false', 'code' => 'ERR005', 'message' => 'ISSS servis vratio grešku', 'data' => $isss_result ) );
				return; 
			}
			
			// Ako upstream vrati warning ovo smatramo za uspješno izvršenu provjeru koja je pokazala da su podaci neispravni
			$odgovor = array();
			foreach($isss_result['warnings'] as $warning) {
				if ($warning['code'] == 'studentno_taken') {
					$data = isss_daj_razlike($podaci_studenta);
					if ($data === false) return;
					if (empty($data['razlike'])) {
						$odgovor['tekst'] = 'Student već unesen';
						$odgovor['status'] = 'ok';
					} else {
						$odgovor['tekst'] = 'Student u ISSSu se razlikuje';
						$odgovor['status'] = 'greska';
						$odgovor['isss_id_studenta'] = $data['isss_id_studenta'];
						$odgovor['razlike'] = $data['razlike'];
					}
					break;
				}
				else if ($warning['code'] == 'unknown_state') {
					$odgovor['tekst'] = 'Nepoznata država';
					$odgovor['status'] = 'greska';
					break;
				}
				else if ($warning['code'] == 'unknown_region') {
					$odgovor['tekst'] = 'Nepoznat kanton';
					$odgovor['status'] = 'greska';
					break;
				}
				else if ($warning['code'] == 'unknown_municipality') {
					$odgovor['tekst'] = 'Nepoznata općina';
					$odgovor['status'] = 'greska';
					break;
				} else {
					$odgovor['tekst'] = 'Nepoznato upozorenje '.$warning['code'];
					$odgovor['status'] = 'greska';
					break;
				}
			}
			if (empty($odgovor)) {
				if ($akcija == "provjera") {
					$odgovor['tekst'] = "Moguće upisati studenta";
					$odgovor['status'] = 'nastaviti';
				} else {
					$odgovor['tekst'] = "Student upisan";
					$odgovor['status'] = 'ok';
				}
			}
			$odgovor['isss'] = $isss_result;
			$rezultat['data'] = $odgovor;
			
		}
		
		if ($tip == "daj_razlike") {
			$id_studenta = int_param('student');
			
			$podaci_studenta = daj_podatke_studenta($id_studenta);
			if ($podaci_studenta === false) return; // Funkcija je već ispisala grešku
			
			$data = isss_daj_razlike($podaci_studenta);
			if ($data === false) return;
			
			if (empty($data['razlike'])) {
				$odgovor['tekst'] = "Student u ISSSu je identičan";
				$odgovor['status'] = 'ok';
			} else {
				$odgovor['tekst'] = "Student u ISSSu se razlikuje";
				$odgovor['status'] = 'nastaviti';
				$odgovor['isss_id_studenta'] = $data['isss_id_studenta'];
				$odgovor['razlike'] = $data['razlike'];
			}
			$rezultat['data'] = $odgovor;
		}
		
		if ($tip == "popravi_studenta_isss") {
			$url = $conf_export_isss_url . "promijeniStudenta.php";
			
			$id_studenta = int_param('student');
			$isss_id_studenta = int_param('isss_id_studenta');
			$razlike = explode(" ", param('razlike'));
			
			$podaci_studenta = daj_podatke_studenta($id_studenta);
			if ($podaci_studenta === false) return;
			
			$isss_data = array ( 
				"id_fakulteta" => $conf_export_isss_id_fakulteta, 
				"id_studenta" => $isss_id_studenta,
				"studenti" => array()
			);
			if ($akcija == "provjera") $isss_data['test'] = "true";
			
			$student = array();
			foreach($razlike as $razlika) {
				if ($razlika == "studij" || $razlika == "nacin_studiranja" || empty($razlika)) continue;
				$student[$razlika] = $podaci_studenta[$razlika];
			}
			if (empty($student)) {
				print json_encode( array( 'success' => 'true', 'data' => array( "tekst" => "Nije moguće promijeniti studij i način studiranja", "status" => "greska" ) ) );
				return; 
			}
			
			$isss_data['studenti'][] = $student;
			
			$isss_msg = array();
			$isss_msg['data'] = json_encode($isss_data);
			
			//print_r($isss_msg);
			
			$isss_result = json_request($url, $isss_msg, "POST");
			
			if ($isss_result === FALSE || $isss_result['success'] === "false") {
				print json_encode( array( 'success' => 'false', 'code' => 'ERR005', 'message' => 'ISSS servis vratio grešku', 'data' => $isss_result ) );
				return; 
			}
			
			// Ako upstream vrati warning ovo smatramo za uspješno izvršenu provjeru koja je pokazala da su podaci neispravni
			$odgovor = array();
			foreach($isss_result['warnings'] as $warning) {
				if ($warning['code'] == 'unknown_student') {
					$odgovor['tekst'] = 'Nepoznat student';
					$odgovor['status'] = 'greska';
					break;
				}
				else if ($warning['code'] == 'unknown_state') {
					$odgovor['tekst'] = 'Nepoznata država';
					$odgovor['status'] = 'greska';
					break;
				}
				else if ($warning['code'] == 'unknown_region') {
					$odgovor['tekst'] = 'Nepoznat kanton';
					$odgovor['status'] = 'greska';
					break;
				}
				else if ($warning['code'] == 'unknown_municipality') {
					$odgovor['tekst'] = 'Nepoznata općina';
					$odgovor['status'] = 'greska';
					break;
				} else {
					$odgovor['tekst'] = 'Nepoznato upozorenje '.$warning['code'];
					$odgovor['status'] = 'greska';
					break;
				}
			}
			if (empty($odgovor)) {
				if ($akcija == "provjera") {
					$odgovor['tekst'] = "Moguće promijeniti podatke studenta";
					$odgovor['status'] = 'nastaviti';
				} else {
					$odgovor['tekst'] = "Student ažuriran";
					$odgovor['status'] = 'ok';
				}
			}
			$odgovor['isss'] = $isss_result;
			$rezultat['data'] = $odgovor;
		}
		
		if ($tip == "upis_vise") {
			$url = $conf_export_isss_url . "upisiSemestar.php";
			
			$id_studenta = int_param('student');
			$id_studija = int_param('studij');
			$id_godine = int_param('godina');
			$semestar = int_param('semestar');
			
			// Naziv studija i akademske godine
			$podaci_studija = db_query_assoc("SELECT s.id id_studija, ag.naziv godina, ss.nacin_studiranja nacin, ss.ponovac
				FROM student_studij ss, studij s, akademska_godina ag, nacin_studiranja ns 
				WHERE ss.student=$id_studenta AND ss.studij=s.id AND s.id=$id_studija and ss.akademska_godina=ag.id AND ag.id=$id_godine");
			if (!$podaci_studija) { 
				print json_encode( array( 'success' => 'false', 'code' => 'ERR006', 'message' => 'Student nije upisan u toj godini', 'student' => $id_studenta ) );
				return; 
			}
			
			// Studij
			$isss_id_studija = 0;
			if ($id_godine<10)
				$isss_id_studija = zamger2isss("studij_stari", $podaci_studija['id_studija']);
			else
				$isss_id_studija = zamger2isss("studij_novi", $podaci_studija['id_studija']);
			
			if ($isss_id_studija == 0) {
				$odgovor['tekst'] = 'Studij nije podržan za upis na ISSS';
				$odgovor['status'] = 'greska';
				// Prekidamo sve
				$rezultat['data'] = $odgovor;
				print json_encode($rezultat);
				return;
			}
			
			$isss_data = array ( 
				"id_fakulteta" => $conf_export_isss_id_fakulteta, 
				"akademska_godina" => $podaci_studija['godina'],
				"studij" => $isss_id_studija,
				"upisi" => array()
			);
			if ($akcija == "provjera") $isss_data['test'] = "true";
			
			// Upit sa podacima studenta stavljamo direktno u $isss_data
			$podaci_studenta = db_query_assoc("SELECT ime, prezime, brindexa, jmbg, imeoca, prezimeoca, imemajke, prezimemajke, adresa, adresa_mjesto, telefon, spol, nacionalnost, UNIX_TIMESTAMP(datum_rodjenja) dr, mjesto_rodjenja, drzavljanstvo, kanton FROM osoba WHERE id=$id_studenta");
			if (!$podaci_studenta) { 
				print json_encode( array( 'success' => 'false', 'code' => 'ERR004', 'message' => 'Nepoznat student ', 'student' => $id_studenta ) );
				return; 
			}
			
			// Popravke formata za eksport:
			
			$podaci_upis = array();
			$podaci_upis['ime'] = $podaci_studenta['ime'];
			$podaci_upis['prezime'] = $podaci_studenta['prezime'];
			$podaci_upis['brindexa'] = $podaci_studenta['brindexa'];
			$podaci_upis['semestar'] = $semestar;
			$podaci_upis['ponovac'] = $podaci_studija['ponovac'];
						
			// Način studiranja iz šifrarnika
			$podaci_upis['nacin_studiranja'] = zamger2isss('nacin_studiranja', $podaci_studija['nacin']);
			
			$isss_data['upisi'][] = $podaci_upis;
			
			$isss_msg = array();
			$isss_msg['data'] = json_encode($isss_data);
			
			//print_r($isss_data);
			
			$isss_result = json_request($url, $isss_msg, "POST");
			
			if ($isss_result === FALSE || $isss_result['success'] === "false" || $isss_result['success'] === false) {
				print json_encode( array( 'success' => 'false', 'code' => 'ERR005', 'message' => 'ISSS servis vratio grešku', 'data' => $isss_result ) );
				return; 
			}
			
			// Ako upstream vrati warning ovo smatramo za uspješno izvršenu provjeru koja je pokazala da su podaci neispravni
			$odgovor = array();
			foreach($isss_result['warnings'] as $warning) {
				if ($warning['code'] == 'unknown_student') {
					$odgovor['tekst'] = 'Nepoznat student';
					$odgovor['status'] = 'greska';
					break;
				}
				else if ($warning['code'] == 'no_major') {
					$odgovor['tekst'] = 'Nepoznat studij';
					$odgovor['status'] = 'greska';
					break;
				}
				else if ($warning['code'] == 'student_already_enrolled') {
					if ($warning['semester'] == $semestar) {
						$odgovor['tekst'] = "Student već upisan u $semestar. semestar";
						$odgovor['status'] = 'ok';
					} else {
						$odgovor['tekst'] = 'Student upisan u pogrešan semestar '.$warning['semester'];
						$odgovor['status'] = 'greska';
					}
					break;
				}
				else if ($warning['code'] == 'no_schoolyear_id') {
					$odgovor['tekst'] = 'Nepoznata akademska godina';
					$odgovor['status'] = 'greska';
					break;
				}
				else if ($warning['code'] == 'cant_enroll') {
					$odgovor['tekst'] = 'Upis nije moguć';
					$odgovor['status'] = 'greska';
					break;
				}
				else if ($warning['code'] == 'never_enrolled') {
					$odgovor['tekst'] = 'Student nikada nije bio upisan';
					$odgovor['status'] = 'greska';
					break;
				} else {
					$odgovor['tekst'] = 'Nepoznato upozorenje '.$warning['code'];
					$odgovor['status'] = 'greska';
					break;
				}
			}
			if (empty($odgovor)) {
				if ($akcija == "provjera") {
					$odgovor['tekst'] = "Moguće upisati studenta";
					$odgovor['status'] = 'nastaviti';
				} else {
					$odgovor['tekst'] = "Student upisan";
					$odgovor['status'] = 'ok';
				}
			}
			$odgovor['isss'] = $isss_result;
			$rezultat['data'] = $odgovor;
			
		}
		
		
		if ($tip == "ciscenje_upis_prva") {
			$id_studenta = int_param('student');
			$id_godine = int_param('godina');
			
			db_query("DELETE FROM izvoz_upis_prva WHERE student=$id_studenta AND akademska_godina=$id_godine");
			$odgovor = array("tekst" => "Očišćen", "status" => "ociscen");
			$rezultat['data'] = $odgovor;
		}
		if ($tip == "ciscenje_upis_vise") {
			$id_studenta = int_param('student');
			$id_godine = int_param('godina');
			$semestar = int_param('semestar');
			
			db_query("DELETE FROM izvoz_upis_semestar WHERE student=$id_studenta AND akademska_godina=$id_godine AND semestar=$semestar");
			$odgovor = array("tekst" => "Očišćen", "status" => "ociscen");
			$rezultat['data'] = $odgovor;
		}
		if ($tip == "ciscenje_ocjene") {
			$id_studenta = int_param('student');
			$id_predmeta = int_param('predmet');
			
			db_query("DELETE FROM izvoz_ocjena WHERE student=$id_studenta AND predmet=$id_predmeta");
			$odgovor = array("tekst" => "Očišćen", "status" => "ociscen");
			$rezultat['data'] = $odgovor;
		}
		if ($tip == "ciscenje_promjena_podataka") {
			$id_studenta = int_param('student');
			
			db_query("DELETE FROM izvoz_promjena_podataka WHERE student=$id_studenta");
			$odgovor = array("tekst" => "Očišćen", "status" => "ociscen");
			$rezultat['data'] = $odgovor;
		}
		
		
		print json_encode($rezultat);
	}
}



// Pomoćna funkcija koja kreira asoc. niz sa podacima jednog studenta u ISSS formatu

function daj_podatke_studenta($id_studenta) {
	// Upit sa podacima studenta stavljamo direktno u $isss_data
	$podaci_studenta = db_query_assoc("SELECT ime, prezime, brindexa, jmbg, imeoca, prezimeoca, imemajke, prezimemajke, adresa, adresa_mjesto, telefon, spol, nacionalnost, UNIX_TIMESTAMP(datum_rodjenja) dr, mjesto_rodjenja, drzavljanstvo, kanton FROM osoba WHERE id=$id_studenta");
	if (!$podaci_studenta) { 
		print json_encode( array( 'success' => 'false', 'code' => 'ERR004', 'message' => 'Nepoznat student ', 'student' => $id_studenta ) );
		return false; 
	}
	
	// Popravke formata za eksport:
	
	// Iz adrese određujemo mjesto, općinu i državu, a mjesto dodajemo na kraj adrese
	$adresa_mjesto = db_query_assoc("SELECT naziv, opcina, drzava FROM mjesto WHERE id=".intval($podaci_studenta['adresa_mjesto']));
	if ($adresa_mjesto) {
		$podaci_studenta['adresa'] .= ", " . $adresa_mjesto['naziv'];
		$podaci_studenta['mjesto'] = $adresa_mjesto['naziv'];
		$podaci_studenta['opcina'] = db_get("SELECT naziv FROM opcina WHERE id=".$adresa_mjesto['opcina']);
		if ($podaci_studenta['opcina'] === false && $adresa_mjesto['naziv'] == "Sarajevo") 
			$podaci_studenta['opcina'] = "Centar"; // Ako je mjesto Sarajevo i općina nije navedena
		$podaci_studenta['opcina'] = zamger2isss("opcina_popravke", $podaci_studenta['opcina']);
		$podaci_studenta['drzava'] = db_get("SELECT naziv FROM drzava WHERE id=".$adresa_mjesto['drzava']);
	} else {
		// Ako nije setovano mjesto, uzećemo da je to Sarajevo-Centar
		$podaci_studenta['mjesto'] = "Sarajevo";
		$podaci_studenta['opcina'] = zamger2isss("opcina_popravke", "Centar");
		$podaci_studenta['drzava'] = "Bosna i Hercegovina";
	}
	
	unset($podaci_studenta['adresa_mjesto']);
	
	// Uzimamo najbolju adresu iz tabele email
	$email = db_get("SELECT adresa FROM email WHERE osoba=$id_studenta ORDER BY sistemska DESC, id");
	if ($email) $podaci_studenta['email'] = $email; else $podaci_studenta['email'] = "";
	
	// Uzimamo nacionalnost iz šifrarnika
	$podaci_studenta['nacionalnost'] = zamger2isss('nacionalnost' , $podaci_studenta['nacionalnost']);
	
	// Datum rođenja u ISSS formatu
	$podaci_studenta['datum_rod'] = date('Y-m-d', $podaci_studenta['dr']);
	unset($podaci_studenta['dr']);
	
	// Podaci o mjestu rođenja
	$mjesto_rodjenja = db_query_assoc("SELECT naziv, opcina, drzava FROM mjesto WHERE id=".$podaci_studenta['mjesto_rodjenja']);
	if ($mjesto_rodjenja) {
		$podaci_studenta['mjesto_rod'] = $mjesto_rodjenja['naziv'];
		$podaci_studenta['opcina_rod'] = db_get("SELECT naziv FROM opcina WHERE id=".$mjesto_rodjenja['opcina']);
		if ($podaci_studenta['opcina_rod'] === false && $mjesto_rodjenja['naziv'] == "Sarajevo") 
			$podaci_studenta['opcina_rod'] = "Centar"; // Ako je mjesto Sarajevo i općina nije navedena
		$podaci_studenta['opcina_rod'] = zamger2isss("opcina_popravke", $podaci_studenta['opcina_rod']);
		$podaci_studenta['drzava_rod'] = db_get("SELECT naziv FROM drzava WHERE id=".$mjesto_rodjenja['drzava']);
	} else {
		// Ako nije setovano mjesto, uzećemo da je to Sarajevo-Centar
		$podaci_studenta['mjesto_rod'] = "Sarajevo";
		$podaci_studenta['opcina_rod'] = zamger2isss("opcina_popravke", "Centar");
		$podaci_studenta['drzava_rod'] = "Bosna i Hercegovina";
	}
	
	unset($podaci_studenta['mjesto_rodjenja']);
	
	// Srednja škola
	$srednja_skola = db_query_assoc("SELECT ss.naziv naziv, ag.naziv godina, ss.opcina, ss.domaca FROM srednja_skola ss, uspjeh_u_srednjoj uus, akademska_godina ag WHERE uus.osoba=$id_studenta AND uus.srednja_skola=ss.id AND uus.godina=ag.id");
	if ($srednja_skola) {
		$podaci_studenta['srednja_skola'] = ukini_viskove($srednja_skola['naziv']);
		$podaci_studenta['godina_zavrsetka'] = substr($srednja_skola['godina'], 5);
		$podaci_studenta['opcina_skole'] = db_get("SELECT naziv FROM opcina WHERE id=".$srednja_skola['opcina']);
		$podaci_studenta['opcina_skole'] = zamger2isss("opcina_popravke", $podaci_studenta['opcina_skole']);
		$podaci_studenta['domaca_skola'] = $srednja_skola['domaca'];
	} else {
		$podaci_studenta['srednja_skola'] = $podaci_studenta['godina_zavrsetka'] = $podaci_studenta['opcina_skole'] = "";
		$podaci_studenta['domaca_skola'] = 0;
	}
	
	// Boračke (posebne) kategorije
	$boracke_kategorije = db_get("SELECT COUNT(*) FROM osoba_posebne_kategorije WHERE osoba=$id_studenta");
	if ($boracke_kategorije>0) $boracke_kategorije=1;
	$podaci_studenta['boracke_kategorije'] = $boracke_kategorije;

	// Spol iz šifrarnika
	$podaci_studenta['spol'] = zamger2isss('spol', $podaci_studenta['spol']);
	
	// Državljanstvo
	$drzavljanstvo = db_get("SELECT naziv FROM drzava WHERE id=".$podaci_studenta['drzavljanstvo']);
	if ($drzavljanstvo) $podaci_studenta['drzavljanstvo'] = $drzavljanstvo; else $podaci_studenta['drzavljanstvo'] = "";
	
	// Kanton NE određujemo iz adresa-mjesto nego iz polja kanton (zbog razlike prebivalište/boravište)
	$kanton = db_get("SELECT naziv FROM kanton WHERE id=".$podaci_studenta['kanton']);
	if ($kanton) $podaci_studenta['kanton'] = zamger2isss('kanton_popravke', $kanton); else $podaci_studenta['kanton'] = "";

	// Način studiranja iz šifrarnika
	$podaci_studenta['nacin_studiranja'] = zamger2isss('nacin_studiranja', $podaci_studija['nacin']);
	
	return $podaci_studenta;
}



// Funkcija koja preuzima podatke o studentu sa ISSSa i vraća niz sa poljima koja se razlikuju u
// odnosu na $podaci_studenta

function isss_daj_razlike($podaci_studenta) {
	global $conf_export_isss_url, $conf_export_isss_id_fakulteta;
	
	$url = $conf_export_isss_url . "dajStudente.php";
	$isss_compare_data = array ( 
		"id_fakulteta" => $conf_export_isss_id_fakulteta, 
		"brindexa" => array( $podaci_studenta['brindexa'] )
	);
	$isss_compare_msg = array();
	$isss_compare_msg['data'] = json_encode($isss_compare_data);
	
	$isss_compare_result = json_request($url, $isss_compare_msg, "POST");

	if ($isss_compare_result === FALSE || $isss_compare_result['success'] === "false") {
		print json_encode( array( 'success' => 'false', 'code' => 'ERR005', 'message' => 'ISSS servis vratio grešku', 'data' => $isss_compare_result ) );
		return false; 
	}
	
	$razlike = array();
	foreach($podaci_studenta as $ime => $vrijednost) {
		$isss_vrijednost = $isss_compare_result['data'][0][$ime];
		if ($ime == "kanton" && $vrijednost == "Strani državljanin") continue;
		if ($ime == "opcina" && $vrijednost == "(nije u BiH)") continue;
		if ($ime == "opcina_rod" && $vrijednost == "(nije u BiH)") continue;
		if ($ime == "srednja_skola") { 
			$isss_vrijednost = ukini_viskove($isss_vrijednost);
			// Ime srednje škole u ISSSu ograničeno na 100 karaktera
			$vrijednost = substr($vrijednost, 0, 100);
		}
		
		if (strtoupper(trim($isss_vrijednost)) != strtoupper(trim($vrijednost))) {
			$razlika = array();
			$razlika['podatak'] = $ime;
			$razlika['zamger'] = $vrijednost;
			$razlika['isss'] = $isss_vrijednost;
			$razlike[] = $razlika;
		}
	}
	$data['razlike'] = $razlike;
	$data['isss_id_studenta'] = $isss_compare_result['data'][0]['id'];
	return $data;
}



// Konverzija šifrarnika između Zamgera i ISSSa

$isss_sifrarnik_nacionalnost = array( 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6 );
$isss_sifrarnik_nacionalnost_ostalo = 5;

$isss_sifrarnik_spol = array( 1 => "M", 0 => "Z" );

$isss_sifrarnik_studij_stari = array( 130 => 2, 80 => 3, 125 => 4, 132 => 5, 789 => 7, 787 => 8, 788 => 9, 790 => 10, 1426 => 14, 1424 => 15, 1425 => 16, 1427 => 17,  100789 => 18, 100787 => 19, 100788 => 20, 100790 => 21);
$isss_sifrarnik_studij_novi  = array( 1757 => 2, 1755 => 3, 1756 => 4, 1758 => 5, 789 => 7, 787 => 8, 788 => 9, 790 => 10, 1426 => 14, 1424 => 15, 1425 => 16, 1427 => 17,  100789 => 18, 100787 => 19, 100788 => 20, 100790 => 21);

$isss_sifrarnik_opcina_popravke = array( 
	"Sarajevo - Centar" => "Centar", 
	"Sarajevo - Novo Sarajevo" => "Novo Sarajevo",
	"Sarajevo Novi Grad" => "Novi Grad",
	"Novi Grad" => "Novi Grad (RS)",
	"Sarajevo - Stari Grad" => "Stari Grad",
	"Prozor / Prozor-Rama" => "Prozor-Rama",
	"Doboj - Istok" => "Doboj Istok",
	"Doboj - Jug" => "Doboj Jug",
	"Gornji Vakuf" => "Gornji Vakuf-Uskoplje"
);

$isss_sifrarnik_nacin_studiranja = array( 1 => 1, 3 => 4, 6 => 3 );
$isss_sifrarnik_nacin_studiranja_nepoznat = 1;

$isss_sifrarnik_kanton_popravke = array( 
	"Kanton Sarajevo" => "Sarajevski kanton",
	"Bosansko Podrinjski kanton" => "Bosansko-Podrinjski kanton",
	"Zeničko Dobojski kanton" => "Zeničko-Dobojski kanton",
	"RS" => "Republika Srpska",
	"Hercegovačko Neretvanski kanton" => "Hercegovačko-Neretvanski kanton",
	"Zapadno Hercegovački kanton" => "Zapadno-Hercegovački kanton",
	"Srednjebosanski kanton" => "Srednjobosanski kanton"
);


function isss2zamger($polje, $vrijednost) {
	eval("global \$isss_sifrarnik_".$polje."; \$niz = \$isss_sifrarnik_".$polje.";");
	
	if (array_key_exists($vrijednost, $niz)) return $niz[$vrijednost];
	if ($polje == "opcina_popravke") return $vrijednost; // Nema popravke
	if ($polje == "kanton_popravke") return $vrijednost; // Nema popravke
	return false;
}

function zamger2isss($polje, $vrijednost) {
	global $isss_sifrarnik_nacionalnost_ostalo, $isss_sifrarnik_nacin_studiranja_nepoznat;
	eval("global \$isss_sifrarnik_".$polje."; \$niz = \$isss_sifrarnik_".$polje.";");
	$niz = array_flip($niz);
	
	if (array_key_exists($vrijednost, $niz) && ($polje == "studij_stari" || $polje == "studij_novi")) return $niz[$vrijednost] % 10000;
	if (array_key_exists($vrijednost, $niz)) return $niz[$vrijednost];
	if ($polje == "nacionalnost") return $isss_sifrarnik_nacionalnost_ostalo;
	if ($polje == "nacin_studiranja") return $isss_sifrarnik_nacin_studiranja_nepoznat;
	if ($polje == "spol") return 1; // Spol mora biti 1 ili 2
	if ($polje == "opcina_popravke") return $vrijednost; // Nema popravke
	if ($polje == "kanton_popravke") return $vrijednost; // Nema popravke
	return 0;
}

// Ukida karaktere koji nisu relevantni za sistem iz stringa
function ukini_viskove($string) {
	$string = str_replace("&quot;", "", $string);
	$string = str_replace("'", "", $string);
	$string = preg_replace("/\s+/", " ", $string);
	return trim($string);
}

?>
