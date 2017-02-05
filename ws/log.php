<?

// WS/LOG - upiti na log sistemskih zamger događaja



function ws_log() {
	global $userid, $user_siteadmin, $user_studentska;

	// Podaci za pasoš studenta
	if (param("tip_loga") == "student") {
		$student = int_param('student');
		$predmet = int_param('predmet');
		$ag = int_param('ag');

		// Provjera permisija
		$ponudakursa = daj_ponudu_kursa($student, $predmet, $ag);
		if (!$user_siteadmin && !nastavnik_pravo_pristupa($predmet, $ag, 0)) { // Ne bi trebalo da može ni asistent
			print json_encode( array( 'success' => 'false', 'code' => 'ERR002', 'message' => 'Permission denied' ) );
			return;
		} else if (!$ponudakursa) {
			print json_encode( array( 'success' => 'false', 'code' => 'ERR404', 'message' => 'Not found' ) );
			return;
		}
		
		$rezultat = array( 'success' => 'true', 'data' => array() );
		
		// Spisak ispita na predmetu, za filtriranje loga
		$ispiti = db_query_varray("select id from ispit where predmet=$predmet and akademska_godina=$ag");
		
		// Događaji u logu koji su nam interesantni
		$txt_dogadjaji = array("dodana ocjena", "obrisana ocjena", "izmjena ocjene", "upisan rezultat ispita", "izbrisan rezultat ispita", "izmjenjen rezultat ispita",
		"izmjena bodova za fiksnu komponentu", "promijenjen datum ocjene",
		// Interesuje nas upis na predmet kao završetak loga
		"student upisan na predmet%"
		);
		$dogadjaji = array();
		$upit_txt = "";

		foreach ($txt_dogadjaji as $txt_dogadjaj) {
			$idovi = db_query_varray("SELECT id FROM log2_dogadjaj WHERE opis LIKE '$txt_dogadjaj'");
			foreach ($idovi as $id) {
				$dogadjaji[$id] = $txt_dogadjaj;
				if ($upit_txt !== "") $upit_txt .= " OR ";
				$upit_txt .= "dogadjaj=$id";
			}
		}

		// Upit nad logom
		$limit_upita = 50;
		$q100 = db_query("SELECT id, dogadjaj, UNIX_TIMESTAMP(vrijeme), userid, objekat2, objekat3, tekst FROM log2, log2_blob WHERE objekat1=$student AND ($upit_txt) AND log2.id=log2_blob.log2 ORDER BY id DESC LIMIT $limit_upita");
		$count = 0;
		while (db_fetch7($q100, $id_dogadjaja, $dogadjaj, $vrijeme, $id_korisnika, $objekat2, $objekat3, $blob)) {
			$count++;
			$fino_vrijeme = date("d.m.Y. H:i:s", $vrijeme);
			
			$q120 = db_query("select ime, prezime from osoba where id=$id_korisnika");
			if (db_num_rows($q120)>0) {
				$korisnik = db_result($q120,0,0)." ".db_result($q120,0,1);
			} else {
				$korisnik = "/nepoznat korisnik $id_korisnika/";
			}
			
			$log_stavka = array( 
				'id' => $id_dogadjaja, 
				'dogadjaj' => $dogadjaj, 
				'opis_dogadjaja' => $dogadjaji[$dogadjaj],
				'vrijeme' => $fino_vrijeme,
				'id_korisnika' => $id_korisnika,
				'korisnik' => $korisnik
			);
			
			if ($dogadjaji[$dogadjaj] == "dodana ocjena") {
				if ($objekat2 != $predmet || $objekat3 != $ag) continue;
				$log_stavka['ocjena'] = $blob;
				
			} else if ($dogadjaji[$dogadjaj] == "obrisana ocjena") {
				if ($objekat2 != $predmet || $objekat3 != $ag) continue;
				$log_stavka['stara_ocjena'] = $blob;
				
			} else if ($dogadjaji[$dogadjaj] == "izmjena ocjene") {
				if ($objekat2 != $predmet || $objekat3 != $ag) continue;
				
				$stari_bodovi = $bodovi = false;
				list($stari_bodovi, $bodovi) = explode(" -&gt; ", $blob);
				if (!$bodovi) { $bodovi=$stari_bodovi; $stari_bodovi=false; }
				$log_stavka['ocjena'] = $bodovi;
				$log_stavka['stara_ocjena'] = $stari_bodovi;
				
			} else if ($dogadjaji[$dogadjaj] == "promijenjen datum ocjene") {
				if ($objekat2 != $predmet || $objekat3 != $ag) continue;
				$log_stavka['datum_ocjene'] = $blob;
				
			} else if ($dogadjaji[$dogadjaj] == "upisan rezultat ispita") {
				$ispit = $objekat2;
				if (!in_array($ispit, $ispiti)) continue;
				
				$log_stavka['bodovi'] = $blob;
				$log_stavka['ispit'] = $ispit;
				
			} else if ($dogadjaji[$dogadjaj] == "izbrisan rezultat ispita") {
				$ispit = $objekat2;
				if (!in_array($ispit, $ispiti)) continue;
				
				$log_stavka['stari_bodovi'] = $blob;
				$log_stavka['ispit'] = $ispit;
				
			} else if ($dogadjaji[$dogadjaj] == "izmjenjen rezultat ispita") {
				$ispit = $objekat2;
				if (!in_array($ispit, $ispiti)) continue;
				
				$stari_bodovi = $bodovi = false;
				list($stari_bodovi, $bodovi) = explode(" -&gt; ", $blob);
				if (!$bodovi) { $bodovi=$stari_bodovi; $stari_bodovi=false; }
				$log_stavka['bodovi'] = $bodovi;
				$log_stavka['stari_bodovi'] = $stari_bodovi;
				$log_stavka['ispit'] = $ispit;
				
			} else if ($dogadjaji[$dogadjaj] == "izmjena bodova za fiksnu komponentu") {
				if ($objekat2 != $ponudakursa) continue;
				
				$komponenta = $objekat3;
				$stari_bodovi = $bodovi = false;
				list($stari_bodovi, $bodovi) = explode(" -&gt; ", $blob);
				if (!$bodovi) { $bodovi=$stari_bodovi; $stari_bodovi="/"; }
				$log_stavka['bodovi'] = $bodovi;
				$log_stavka['stari_bodovi'] = $stari_bodovi;
				$log_stavka['komponenta'] = $komponenta;
				

			} else if ($dogadjaji[$dogadjaj] == "student upisan na predmet%") {
				if ($objekat2 != $ponudakursa) continue;
				// Nema smisla da dalje idemo
				break;
			}
			
			$rezultat['data'][] = $log_stavka;
			
			// Ako smo potrošili sve podatke a nismo došli do upisa studenta, ponavljamo upit
			if ($count == $limit_upita) {
				$q100 = db_query("SELECT id, dogadjaj, UNIX_TIMESTAMP(vrijeme), userid, objekat2, objekat3 FROM log2 WHERE id<$id_dogadjaja AND objekat1=$student AND ($upit_txt) ORDER BY id DESC LIMIT $limit_upita");
				$count = 0;
			}
		}
		
		print json_encode($rezultat);
	}
}


?>
