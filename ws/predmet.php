<?

// WS/PREDMET - web servis za predmet



function ws_predmet() {
	global $userid, $user_siteadmin, $user_studentska;

	$rezultat = array( 'success' => 'true', 'data' => array() );
	
	if (isset($_REQUEST['ag']))
		$ag = intval($_REQUEST['ag']);
	else {
		$q10 = myquery("select id from akademska_godina where aktuelna=1 order by id desc limit 1");
		$ag = mysql_result($q10,0,0);
	}
	
	// Podaci o predmetu
	if (isset($_REQUEST['id']) || isset($_REQUEST['predmet'])) {
		if (isset($_REQUEST['id'])) $predmet = intval($_REQUEST['id']);
		if (isset($_REQUEST['predmet'])) $predmet = intval($_REQUEST['predmet']);
		
		// Korisnik mora biti logiran
		if ($userid == 0) {
			print json_encode( array( 'success' => 'false', 'code' => 'ERR002', 'message' => 'Permission denied' ) );
			return;
		}

		$q100 = myquery("SELECT sifra, naziv, institucija, kratki_naziv, ects, sati_predavanja, sati_vjezbi, sati_tutorijala FROM predmet WHERE id=$predmet");
		if (mysql_num_rows($q100)==0) {
			header("HTTP/1.0 404 Not Found");
			print json_encode( array( 'success' => 'false', 'code' => 'ERR404', 'message' => 'Not found' ) );
			return;
		}
		$predmet_ar = array();
		$predmet_ar['id'] = $predmet;
		$predmet_ar['sifra'] = mysql_result($q100,0,0);
		$predmet_ar['naziv'] = mysql_result($q100,0,1);
		$q110 = myquery("SELECT naziv FROM institucija WHERE id=".mysql_result($q100,0,2));
		$predmet_ar['institucija'] = mysql_result($q110,0,0);
		$predmet_ar['kratki_naziv'] = mysql_result($q100,0,3);
		$predmet_ar['ects'] = mysql_result($q100,0,4);
		$predmet_ar['sati_predavanja'] = mysql_result($q100,0,5);
		$predmet_ar['sati_vjezbi'] = mysql_result($q100,0,6);
		$predmet_ar['sati_tutorijala'] = mysql_result($q100,0,7);
		
		$q120 = myquery("SELECT pk.id, s.id, s.naziv, s.kratkinaziv, ts.ciklus, pk.semestar, pk.obavezan 
		FROM ponudakursa pk, studij s, tipstudija ts 
		WHERE pk.predmet=$predmet AND pk.akademska_godina=$ag AND pk.studij=s.id AND s.tipstudija=ts.id");
		$predmet_ar['ponude_kursa'] = array();
		while ($r120 = mysql_fetch_row($q120)) {
			$pk['id'] = $r120[0];
			$pk['studij'] = array();
			$pk['studij']['id'] = $r120[1];
			$pk['studij']['naziv'] = $r120[2];
			$pk['studij']['kratki_naziv'] = $r120[3];
			$pk['studij']['ciklus'] = $r120[4];
			$pk['semestar'] = $r120[5];
			$pk['obavezan'] = ($r120[6] == 1 ? "true" : "false");
			$predmet_ar['ponude_kursa'][] = $pk;
		}
		
		// Nastavni ansambl
		$q130 = myquery("SELECT a.osoba, ans.id, ans.naziv FROM angazman a, angazman_status ans WHERE a.predmet=$predmet AND a.akademska_godina=$ag AND a.angazman_status=ans.id ORDER BY ans.id");
		$predmet_ar['nastavni_ansambl'] = array();
		while ($r130 = mysql_fetch_row($q130)) {
			$nas = array();
			$nas['id'] = $r130[0];
			$nas['ime'] = tituliraj($r130[0]);
			$nas['status'] = array('id' => $r130[1], 'opis' => $r130[2]);
			$predmet_ar['nastavni_ansambl'][] = $nas;
		}
		
		// Prava pristupa
		$q140 = myquery("SELECT nastavnik, nivo_pristupa FROM nastavnik_predmet WHERE predmet=$predmet AND akademska_godina=$ag");
		$predmet_ar['prava_pristupa'] = array();
		while ($r140 = mysql_fetch_row($q140)) {
			$nas = array();
			$nas['id'] = $r140[0];
			$nas['ime'] = tituliraj($r140[0]);
			$nas['nivo_pristupa'] = $r140[1];
			$predmet_ar['prava_pristupa'][] = $nas;
		}
		
		$rezultat['data'] = $predmet_ar;
	}

	// Default akcija - spisak predmeta na studiju i semestru
	if (isset($_REQUEST['studij'])) {
		$studij = intval($_REQUEST['studij']);
		$semestar = intval($_REQUEST['semestar']);

		$q10 = myquery("select p.id, p.naziv, pk.akademska_godina from predmet as p, ponudakursa as pk where pk.predmet=p.id and pk.akademska_godina=$ag and pk.studij=$studij and pk.semestar=$semestar order by p.naziv");
		while ($r10 = mysql_fetch_assoc($q10)) {
			array_push($rezultat['data'], $r10);
		}
	}


	print json_encode($rezultat);
}
