<?

// WS/POTVRDA - spisak vrsta potvrda i zahtjeva



function ws_potvrda() {
	global $userid, $user_student;

	$rezultat = array( 'success' => 'true', 'data' => array() );
	
	if (isset($_REQUEST['akcija']) && $_REQUEST['akcija'] == "tipvrsta") {
		$q10 = myquery("SELECT id, naziv FROM tip_potvrde");
		$tip_array = array();
		while($r10 = mysql_fetch_row($q10))
			$tip_array[$r10[0]] = $r10[1];
		
		$q20 = myquery("SELECT id, naziv FROM svrha_potvrde");
		$svrha_array = array();
		while($r20 = mysql_fetch_row($q20))
			$svrha_array[$r20[0]] = $r20[1];
		
		$rezultat['data']['tipovi'] = $tip_array;
		$rezultat['data']['svrhe'] = $svrha_array;
		echo json_encode($rezultat);
		return;
	}
	
	if ($_SERVER['REQUEST_METHOD'] == "POST") {
		$tip_potvrde = intval($_REQUEST['tip_potvrde']);
		$svrha_potvrde = intval($_REQUEST['svrha_potvrde']);
		if ($tip_potvrde == 0 || $svrha_potvrde == 0) {
			$rezultat = array( 'success' => 'false', 'code' => 'ERR900', 'message' => 'Nedostaje tip ili svrha potvrde' );
			echo json_encode($rezultat);
			return;
		}
		$q0 = myquery("INSERT INTO zahtjev_za_potvrdu SET student=$userid, tip_potvrde=$tip_potvrde, svrha_potvrde=$svrha_potvrde, datum_zahtjeva=NOW(), status=1");
		$id = intval(mysql_insert_id());
		zamgerlog("uputio novi zahtjev za potvrdu $id", 2);
		zamgerlog2("uputio novi zahtjev za potvrdu", $id);
		
		echo json_encode($rezultat);
		return;
	}
	
	
	if ($_SERVER['REQUEST_METHOD'] == "DELETE") {
		$id = intval($_REQUEST['id']);
		$q300 = myquery("SELECT COUNT(*) FROM zahtjev_za_potvrdu WHERE id=$id AND student=$userid");
		if (mysql_num_rows($q300)<1) {
			$rezultat = array( 'success' => 'false', 'code' => 'ERR901', 'message' => 'Neispravan zahtjev' );
			echo json_encode($rezultat);
			return;
		}
		$q310 = myquery("DELETE FROM zahtjev_za_potvrdu WHERE id=$id");
		zamgerlog("odustao od zahtjeva za potvrdu $id", 2);
		zamgerlog2("odustao od zahtjeva za potvrdu", $id);
		
		echo json_encode($rezultat);
		return;
	}

	$q100 = myquery("SELECT zzp.id, tp.id, tp.naziv, zzp.svrha_potvrde, UNIX_TIMESTAMP(zzp.datum_zahtjeva), zzp.status FROM zahtjev_za_potvrdu as zzp, tip_potvrde as tp WHERE zzp.student=$userid and zzp.tip_potvrde=tp.id");
	while ($r100 = mysql_fetch_row($q100)) {
		$zahtjev['id'] = $r100[0];
		$zahtjev['tip_potvrde'] = $r100[1];
		$zahtjev['svrha_potvrde'] = $r100[3];
		$zahtjev['datum'] = $r100[4];
		$zahtjev['status'] = $r100[5];
		$rezultat['data'][] = $zahtjev;
	}
	
	echo json_encode($rezultat);
}


?>