<?

// WS/EXPORT - web servis za izvoz podataka u aplikaciju trećih lica



function ws_export() {
	require("lib/ws.php");

	global $userid, $user_siteadmin, $user_studentska;
	global $conf_export_format, $conf_export_isss_url, $conf_export_isss_id_fakulteta;

	if (!$user_siteadmin && !$user_studentska) {
		print json_encode( array( 'success' => 'false', 'code' => 'ERR002', 'message' => 'Permission denied' ) );
		return;
	}
	
	if ($conf_export_format == "isss-newws") {
		$rezultat = array( 'success' => 'true', 'data' => array() );
		
		$tip = param('tip');
		$akcija = param('akcija');
		
		if ($tip == "ocjene") {
			$url = $conf_export_isss_url . "dodajOcjenu.php";
			
			$id_studenta = int_param('student');
			$id_predmeta = int_param('predmet');
			
			// Određujemo podatke studenta
			$student = db_query_assoc("SELECT ime, prezime, brindexa FROM osoba WHERE id=$id_studenta");
			if (!$student) { 
				print json_encode( array( 'success' => 'false', 'code' => 'ERR004', 'message' => 'Nepoznat student ', 'student' => $id_studenta ) );
				return; 
			}
			
			// Određujemo naziv predmeta
			$podaci_ocjene = db_query_assoc("SELECT ko.ocjena, ag.naziv godina, pp.naziv predmet, UNIX_TIMESTAMP(ko.datum_u_indeksu) datum
				FROM pasos_predmeta pp, konacna_ocjena ko, akademska_godina ag 
				WHERE ko.student=$id_studenta AND ko.predmet=$id_predmeta AND ko.pasos_predmeta=pp.id AND ko.akademska_godina=ag.id");
			if (!$podaci_ocjene) { 
				print json_encode( array( 'success' => 'false', 'code' => 'ERR004', 'message' => 'Nije evidentirana ocjena za predmet' ) );
				return; 
			}
			
			$datum_isss_format = date("Y-m-d", $podaci_ocjene['datum']);
			
			$isss_data = array ( 
				"predmet" => $podaci_ocjene['predmet'], 
				"id_fakulteta" => $conf_export_isss_id_fakulteta, 
				"akademska_godina" => $podaci_ocjene['godina'],
				"ocjene" => array(
					array( 
						"ime" => $student['ime'],
						"prezime" => $student['prezime'],
						"brindexa" => $student['brindexa'],
						"ocjena" => $podaci_ocjene['ocjena'],
						"datum" => $datum_isss_format
					)
				)
			);
			if ($akcija == "provjera") $isss_data['test'] = "true";
			
			$isss_msg = array();
			$isss_msg['data'] = json_encode($isss_data);
			
			//print_r($isss_data);
			
			$isss_result = json_request($url, $isss_msg, "POST");
			
			if (!$isss_result || $isss_result['success'] == "false") {
				print json_encode( array( 'success' => 'false', 'code' => 'ERR005', 'message' => 'ISSS servis vratio grešku', 'data' => $isss_result ) );
				return; 
			}
			
			// Ako upstream vrati warning ovo smatramo za uspješno izvršenu provjeru koja je pokazala da su podaci neispravni
			$odgovor = array();
			foreach($isss_result['warnings'] as $warning) {
				if ($warning['code'] == 'unknown_student') {
					$odgovor['status'] = 'nepoznat student';
					break;
				}
				if ($warning['code'] == 'grade_exists') {
					if ($warning['mark'] == $podaci_ocjene['ocjena']) {
						$odgovor['status'] = "ocjena unesena";
					} else {
						$odgovor['status'] = "ocjena razlicita";
						$odgovor['ocjena'] = $warning['mark'];
					}
					break;
				}
				if ($warning['code'] == 'student_not_enrolled') {
					$odgovor['status'] = "student nije na predmetu";
					break;
				}
				if ($warning['code'] == 'course_not_offered') {
					$odgovor['status'] = "predmet nije u ponudi";
					break;
				}
				if ($warning['code'] == 'no_exam_open') {
					$odgovor['status'] = "nije otvoren ispit";
					break;
				}
			}
			if (empty($odgovor)) {
				if ($akcija == "provjera") $odgovor['status'] = "moguce upisati ocjenu";
				else $odgovor['status'] = "ocjena upisana";
			}
			$rezultat['data'] = $odgovor;
		}
		
		
		print json_encode($rezultat);
	}
}


?>
