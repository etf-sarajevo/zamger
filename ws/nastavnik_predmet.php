<?

// WS/NASTAVNIK_PREDMET - podaci o predmetu iz perspektive nastavnika



function ws_nastavnik_predmet()
{	
	global $userid, $user_nastavnik, $user_student, $user_siteadmin, $user_studentska;
	
	$rezultat = array( 'success' => 'true', 'data' => array() );
	
	if (isset($_REQUEST['ag']))
		$ag = intval($_REQUEST['ag']);
	else {
		$ag = db_get("select id from akademska_godina where aktuelna=1 order by id desc limit 1");
	}

	$q100 = db_query("SELECT p.id, p.naziv, p.kratki_naziv FROM predmet p, nastavnik_predmet np WHERE np.nastavnik=$userid AND np.akademska_godina=$ag AND np.predmet=p.id");
	if (db_num_rows($q100)<1) {
		print json_encode( array( 'success' => 'false', 'code' => 'ERR004', 'message' => 'Niste nastavnik niti na jednom predmetu' ) );
		return;
	}
	
	//Kako bi spremili sve predmete u jedan niz, pa taj niz proslijedili u rezultat, pravimo pomoćni niz
	$temp = array();
	while($r100 = db_fetch_row($q100))
		array_push($temp, array("id" => $r100[0], "naziv" => $r100[1], "kratki_naziv" => $r100[2]));
	$rezultat['data']['predmeti'] = $temp;
	
	echo json_encode($rezultat);
}