<?

// WS/PRISUSTVO - podaci o prisustvu



function ws_prisustvo()
{	
	global $userid, $user_nastavnik, $user_student, $user_siteadmin, $user_studentska;
	
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
		if ($user_nastavnik) $ok = nastavnik_pravo_pristupa($predmet, $ag, $student);
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
		if ($user_nastavnik) $ok = nastavnik_pravo_pristupa($predmet, $ag, $student);
		if ($user_student && $student == $userid) $ok = true; // Kasnije ćemo provjeriti da li student sluša predmet
		
		if (!$ok) {
			print json_encode( array( 'success' => 'false', 'code' => 'ERR002', 'message' => 'Permission denied' ) );
			return;
		}
		
		if (!daj_ponudu_kursa($student, $predmet, $ag)) {
			print json_encode( array( 'success' => 'false', 'code' => 'ERR003', 'message' => 'Student ne sluša predmet' ) );
			return;
		}

		$q20 = db_query("SELECT k.id, k.maxbodova, k.prolaz, k.opcija 
		FROM komponenta as k, tippredmeta_komponenta as tpk, akademska_godina_predmet as agp
		WHERE agp.predmet=$predmet and agp.akademska_godina=$ag and agp.tippredmeta=tpk.tippredmeta and tpk.komponenta=k.id and k.tipkomponente=3"); // 3 = prisustvo
		
		while ($r20 = db_fetch_row($q20)) {
			$id_komponente = $r20[0];
			$max_bodova = $r20[1];
			$min_bodova = $r20[2];
			$max_izostanaka = $r20[3];
			
			$odsustva = $casova = 0;
			$q30 = db_query("select l.id,l.naziv from labgrupa as l, student_labgrupa as sl where l.predmet=$predmet and l.akademska_godina=$ag and l.id=sl.labgrupa and sl.student=$student");
			
			$grupe = array();
			
			while ($r30 = db_fetch_row($q30)) {
				$grupa = array();
				$grupa['id'] = $r30[0];
				$grupa['naziv'] = $r30[1];
				if (!preg_match("/\w/", $r30[1])) $grupa['naziv'] = "[Bez naziva]";
				
				$q40 = db_query("select id, UNIX_TIMESTAMP(datum), vrijeme from cas where labgrupa=$r30[0] and komponenta=$r20[0] order by datum, vrijeme");
				if (db_num_rows($q40)<1) continue; // Preskace u kojima nema registrovanih časova
				
				$grupa_odsustva = 0;
				$casovi = array();
				while ($r40 = db_fetch_row($q40)) {
					$vrijeme_casa = $r40[1];
					if (preg_match("/^(\d\d)\:(\d\d)\:(\d\d)$/", $r40[2], $matches))
						$vrijeme_casa += $matches[1]*3600 + $matches[2]*60 + $matches[3];
						
					$cas = array();
					$cas['id'] = $r40[0];
					$cas['vrijeme'] = $vrijeme_casa;
					
					$q15 = db_query("select prisutan from prisustvo where student=$student and cas=$r40[0]");
					if (db_num_rows($q15)<1) 
						$cas['status'] = "nepoznato";
					else if (db_result($q15,0,0)==1) 
						$cas['status'] = "prisutan";
					else {
						$cas['status'] = "odsutan";
						$grupa_odsustva++;
					}
					$casovi[] = $cas;
				}
				
				$grupa['casovi'] = $casovi;
				$grupe[] = $grupa;
				
				$odsustva += $grupa_odsustva;
				$casova += count($casovi);
			}
			
			if ($max_izostanaka == -1) {
				if ($casova == 0) 
					$bodovi = 10;
				else
					$bodovi = $min_bodova + round(($max_bodova - $min_bodova) * (($casova - $odsustva) / $casova), 2 ); 
			} 
			else if ($max_izostanaka == -2) { // Paraproporcionalni sistem TP
				if ($odsustva <= 2)
					$bodovi = $max_bodova;
				else if ($odsustva <= 2 + ($max_bodova - $min_bodova)/2)
					$bodovi = $max_bodova - ($odsustva-2)*2;
				else
					$bodovi = $min_bodova;
			} else if ($odsustva<=$max_izostanaka)
				$bodovi = $max_bodova;
			else
				$bodovi = $min_bodova;
				
			$komponenta = array();
			$komponenta['id'] = $r20[0];
			$komponenta['grupe'] = $grupe;
			$rezultat['data'][] = $komponenta;
		}
	}
	
	echo json_encode($rezultat);
	return;
}


?>