<?

// WS/STUDENT_PREDMET - podaci o predmetu iz perspektive studenta



function ws_student_predmet() {
	global $userid, $user_nastavnik, $user_student, $user_siteadmin, $user_studentska;

	require_once("lib/permisije.php");
	
	$rezultat = array( 'success' => 'true', 'data' => array() );
	
	if (isset($_REQUEST['ag']))
		$ag = intval($_REQUEST['ag']);
	else {
		$q10 = db_query("select id from akademska_godina where aktuelna=1 order by id desc limit 1");
		$ag = db_result($q10,0,0);
	}
	
	// Ako je definisan predmet, vraćamo detaljnije informacije o tom predmetu
	if (isset($_REQUEST['predmet'])) {
		$predmet = intval($_REQUEST['predmet']);
		if (isset($_REQUEST['student']))
			$student = intval($_REQUEST['student']);
		else
			$student = $userid;
		
		// Provjeravamo prava pristupa
		$ok = false;
		if ($user_siteadmin || $user_studentska) $ok = true;
		if ($user_nastavnik) $ok = nastavnik_pravo_pristupa($predmet, $ag, $student);
		if ($user_student && $student == $userid) $ok = true;
		
		if (!$ok) {
			print json_encode( array( 'success' => 'false', 'code' => 'ERR002', 'message' => 'Permission denied' ) );
			return;
		}
		
		// Da li student slusa predmet?
		$ponudakursa = daj_ponudu_kursa($student, $predmet, $ag);
		if ($ponudakursa === false) {
			print json_encode( array( 'success' => 'false', 'code' => 'ERR003', 'message' => 'Student ne sluša predmet' ) );
			return;
		}
		
		$q1 = db_query("select naziv from predmet where id=$predmet");
		$rezultat['data']['naziv_predmeta'] = db_result($q1,0,0);
		
		// Traženje odgovornog nastavnika 
		$q3 = db_query("select o.id, ast.naziv from angazman as a, angazman_status as ast, osoba as o where a.predmet=$predmet and a.akademska_godina=$ag and a.angazman_status=ast.id and a.osoba=o.id order by ast.id");
		$r4 = db_fetch_row($q3);
		$rezultat['data']['odgovorni_nastavnik'] = tituliraj($r4[0]);
		
		// Traženje bodova po komponentama
		$q5 = db_query("select kb.bodovi, k.maxbodova, k.gui_naziv, k.id from komponentebodovi as kb, komponenta as k where kb.student=$student and kb.predmet=$ponudakursa and kb.komponenta=k.id");
		$komponente = array();
		while($r5 = db_fetch_row($q5)) {
			$komponente[$r5[3]] = array("bodovi" => $r5[0], "max_broj_bodova" => $r5[1], "naziv" => $r5[2]);
		}
		
		$rezultat['data']['komponente'] = $komponente;
		
		$q6 = db_query("select ocjena from konacna_ocjena where student=$student and predmet=$predmet and ocjena>=6");
		
		// Provjeramo da li je student vec upisao ocjenu
		while ($r6 = db_fetch_row($q6)) 
			$rezultat['data']['konacna_ocjena'] = $r6[0];
		
		echo json_encode($rezultat);
		return;
	}
	
	// Nije naveden predmet, uzimamo spisak predmeta koje student trenutno sluša
	if (($user_siteadmin || $user_studentska) && isset($_REQUEST['student']))
		$student = intval($_REQUEST['student']);
	else
		$student = $userid;
	$q100 = db_query("select p.id, p.naziv, p.kratki_naziv from student_predmet as sp, ponudakursa as pk, predmet as p where sp.student=$student and sp.predmet=pk.id and pk.akademska_godina=$ag and pk.predmet=p.id");
	if (db_num_rows($q100)<1) {
		print json_encode( array( 'success' => 'false', 'code' => 'ERR004', 'message' => 'Student ne sluša niti jedan predmet' ) );
		return;
	}
	
	//Kako bi spremili sve predmete u jedan niz, pa taj niz proslijedili u rezultat, pravimo pomoćni niz
	$temp = array();
	while($r100 = db_fetch_row($q100))
		array_push($temp, array("id" => $r100[0], "naziv" => $r100[1], "kratki_naziv" => $r100[2]));
	$rezultat['data']['predmeti'] = $temp;
	
	echo json_encode($rezultat);
}

?>

