<?

// WS/LABGRUPA - spisak grupa na predmetu ili studenata u grupi



function ws_labgrupa() {
	global $userid, $user_nastavnik, $user_siteadmin;

	require_once("lib/permisije.php");

	// Listanje grupa i studenata u grupama mogu raditi osobe u statusu nastavnika na predmetu
	if (!$user_siteadmin && !$user_nastavnik) {
		print json_encode( array( 'success' => 'false', 'code' => 'ERR002', 'message' => 'Permission denied' ) );
		return;
	} 
	
	$rezultat = array( 'success' => 'true', 'data' => array() );
	
	if (isset($_REQUEST['id']) && $_REQUEST['id'] > 0) {
		$grupa = intval($_REQUEST['id']);
		
		$q20 = db_query("SELECT naziv, predmet, akademska_godina FROM labgrupa WHERE id=$grupa");
		if (db_num_rows($q20) == 0) {
			header("HTTP/1.0 404 Not Found");
			print json_encode( array( 'success' => 'false', 'code' => 'ERR404', 'message' => 'Not found' ) );
			return;
		}
		
		$predmet = db_result($q20,0,1);
		$ag = db_result($q20,0,2);
		if (!$user_siteadmin && !nastavnik_pravo_pristupa($predmet, $ag, 0)) {
			print json_encode( array( 'success' => 'false', 'code' => 'ERR002', 'message' => 'Permission denied' ) );
			return;
		}
		
		$rezultat['data']['naziv'] = db_result($q20,0,0);
		
		$q10 = db_query("SELECT o.ime, o.prezime, o.brindexa, a.login, o.id FROM osoba o, student_labgrupa as sl, auth a WHERE sl.labgrupa=$grupa AND sl.student=o.id AND o.id=a.id ORDER BY o.prezime, o.ime");
		$studenti = array();
		while ($r10 = db_fetch_row($q10))
			$studenti[$r10[4]] = array( 'ime' => $r10[0], 'prezime' => $r10[1], 'brindexa' => $r10[2], 'login' => $r10[3] );
		$rezultat['data']['studenti'] = $studenti;
		
		print json_encode($rezultat); 
		return; 
	}
	
	if (isset($_REQUEST['predmet'])) {
		$predmet = intval($_REQUEST['predmet']);
		$ag = intval($_REQUEST['ag']);
		if ($ag == 0) { // ag nije zadana, uzimamo aktuelnu
			$q10 = db_query("SELECT id FROM akademska_godina WHERE aktuelna=1");
			$ag = db_result($q10,0,0);
		}
		
		if (!$user_siteadmin && !nastavnik_pravo_pristupa($predmet, $ag, 0)) {
			print json_encode( array( 'success' => 'false', 'code' => 'ERR002', 'message' => 'Permission denied' ) );
			return;
		}
	
		$q100 = db_query("SELECT lg.id, lg.naziv FROM labgrupa lg WHERE lg.predmet=$predmet AND lg.akademska_godina=$ag");
		while ($r100 = db_fetch_row($q100))
			$rezultat['data'][$r100[0]] = $r100[1];
		
		print json_encode($rezultat); 
		return; 
	}
	
	print json_encode(array( 'success' => 'false', 'code' => 'ERR006', 'message' => 'Not implemented yet' ) );

}


?>
