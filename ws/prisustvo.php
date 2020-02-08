<?

// WS/PRISUSTVO - podaci o prisustvu



function ws_prisustvo() {
	global $userid, $user_nastavnik, $user_student, $user_siteadmin, $user_studentska;

	require_once("lib/permisije.php");
	require_once("lib/student_predmet.php"); // update_komponente
	
	$rezultat = array( 'success' => 'true', 'data' => array() );
	
	if (isset($_REQUEST['ag']))
		$ag = intval($_REQUEST['ag']);
	else {
		$q10 = db_query("select id from akademska_godina where aktuelna=1 order by id desc limit 1");
		$ag = db_result($q10,0,0);
	}
	if (isset($_REQUEST['student']))
		$student = intval($_REQUEST['student']);
	else
		$student = $userid;
	
	
	// ID je id časa
	if (isset($_REQUEST['id']) || isset($_REQUEST['cas'])) {
		if (isset($_REQUEST['id'])) $cas = intval($_REQUEST['id']);
		else $cas = intval($_REQUEST['cas']);
		$q10 = db_query("SELECT l.id, l.predmet, l.akademska_godina, c.komponenta FROM labgrupa l, cas c WHERE c.id=$cas AND c.labgrupa=l.id");
		if (db_num_rows($q10)<1) {
			header("HTTP/1.0 404 Not Found");
			print json_encode( array( 'success' => 'false', 'code' => 'ERR404', 'message' => 'Not found' ) );
			return;
		}
		$labgrupa = db_result($q10,0,0);
		$predmet = db_result($q10,0,1);
		$ag = db_result($q10,0,2);
		$komponenta = db_result($q10,0,3);
		
		// Provjeravamo prava pristupa
		$ok = false;
		if ($user_siteadmin || $user_studentska) $ok = true;
		if (!$ok && $user_nastavnik) $ok = nastavnik_pravo_pristupa($predmet, $ag, $student);
		if ($user_student && $student == $userid) 
			foreach(student_labgrupe($student, $predmet, $ag) as $lg)
				if ($lg == $labgrupa) $ok = true;
		
		if (!$ok) {
			print json_encode( array( 'success' => 'false', 'code' => 'ERR002', 'message' => 'Permission denied' ) );
			return;
		}
		
		if ($_SERVER['REQUEST_METHOD'] == "POST") {
			require("lib/manip.php");
			$prisutan=intval($_POST['prisutan']);
			if ($prisutan == 3) { // Postavljanje u neutralno stanje
				$q0 = db_query("delete from prisustvo where student=$student and cas=$cas");
			} else {
				$prisutan--;
				$q1 = db_query("select prisutan from prisustvo where student=$student and cas=$cas");
				if (db_num_rows($q1)<1) 
					$q2 = db_query("insert into prisustvo set prisutan=$prisutan, student=$student, cas=$cas");
				else
					$q3 = db_query("update prisustvo set prisutan=$prisutan where student=$student and cas=$cas");
			}

			update_komponente(
				$student, 
				daj_ponudu_kursa($student, $predmet, $ag), 
				$komponenta
			);
			zamgerlog2("prisustvo azurirano", $student, $cas, $prisutan);
			$rezultat['message'] = "Ažurirano prisustvo";
		}
		if ($_SERVER['REQUEST_METHOD'] == "GET") {
			$q15 = db_query("select prisutan from prisustvo where student=$student and cas=$cas");
			if (db_num_rows($q15)<1) 
				$rezultat['data']['status'] = "nepoznato";
			else if (db_result($q15,0,0)==1) 
				$rezultat['data']['status'] = "prisutan";
			else
				$rezultat['data']['status'] = "odsutan";
		}
	}

	
	// Default akcija: prikazujemo prisustvo za studenta na predmetu
	if (isset($_REQUEST['predmet'])) {
		$predmet = intval($_REQUEST['predmet']);
		
		// Provjeravamo prava pristupa
		$ok = false;
		if ($user_siteadmin || $user_studentska) $ok = true;
		if (!$ok && $user_nastavnik) $ok = nastavnik_pravo_pristupa($predmet, $ag, $student);
		if ($user_student && $student == $userid) $ok = true; // Kasnije ćemo provjeriti da li student sluša predmet
		
		if (!$ok) {
			print json_encode( array( 'success' => 'false', 'code' => 'ERR002', 'message' => 'Permission denied' ) );
			return;
		}
		
		if (!daj_ponudu_kursa($student, $predmet, $ag)) {
			print json_encode( array( 'success' => 'false', 'code' => 'ERR003', 'message' => 'Student ne sluša predmet' ) );
			return;
		}

		$komponente_rezultat = array();
		$komponente = db_query_varray("SELECT k.id
		FROM komponenta as k, tippredmeta_komponenta as tpk, akademska_godina_predmet as agp
		WHERE agp.predmet=$predmet and agp.akademska_godina=$ag and agp.tippredmeta=tpk.tippredmeta and tpk.komponenta=k.id and k.tipkomponente=3"); // 3 = prisustvo
		foreach($komponente as $id_komponente) {
			$grupe_rezultat = array();
			
			$grupe = db_query_vassoc("select l.id,l.naziv from labgrupa as l, student_labgrupa as sl where l.predmet=$predmet and l.akademska_godina=$ag and l.id=sl.labgrupa and sl.student=$student");
			foreach($grupe as $id_grupe => $naziv_grupe) {
				$grupa = array();
				$grupa['id'] = $id_grupe;
				$grupa['naziv'] = $naziv_grupe;
				if (!preg_match("/\w/", $naziv_grupe)) $grupa['naziv'] = "[Bez naziva]";
				
				$q40 = db_query("select id, UNIX_TIMESTAMP(datum), vrijeme from cas where labgrupa=$id_grupe and komponenta=$id_komponente order by datum, vrijeme");
				if (db_num_rows($q40)<1) continue; // Preskače grupe u kojima nema registrovanih časova
				
				$grupa_odsustva = 0;
				$casovi = array();
				while (db_fetch3($q40, $id_casa, $datum_casa, $vrijeme_casa)) {
					$timestamp_casa = $datum_casa;
					if (preg_match("/^(\d\d)\:(\d\d)\:(\d\d)$/", $vrijeme_casa, $matches))
						$timestamp_casa += $matches[1]*3600 + $matches[2]*60 + $matches[3];
						
					$cas = array();
					$cas['id'] = $id_casa;
					$cas['vrijeme'] = $timestamp_casa;
					
					$prisutan = db_get("select prisutan from prisustvo where student=$student and cas=$id_casa");
					if ($prisutan === false) 
						$cas['status'] = "nepoznato";
					else if ($prisutan == 1) 
						$cas['status'] = "prisutan";
					else {
						$cas['status'] = "odsutan";
					}
					$casovi[] = $cas;
				}
				
				$grupa['casovi'] = $casovi;
				$grupe_rezultat[] = $grupa;
			}
				
			$komponenta = array();
			$komponenta['id'] = $id_komponente;
			$komponenta['grupe'] = $grupe_rezultat;
			$komponente_rezultat[] = $komponenta;
		}
		$rezultat['data']['komponente'] = $komponente_rezultat;
	}
	
	echo json_encode($rezultat);
	return;
}


?>
