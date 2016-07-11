<?

// WS/OSOBA - svi podaci o osobi



function ws_osoba() {
	global $userid, $user_siteadmin, $user_studentska;

	$rezultat = array( 'success' => 'true', 'data' => array() );
	
	if (isset($_REQUEST['akcija']) && $_REQUEST['akcija'] == "pretraga") {
		// Svi prijavljeni korisnici mogu vršiti pretragu
		
		$ime = my_escape($_REQUEST['ime']);
		if (!preg_match("/\w/",$ime)) { 
			print json_encode($rezultat); 
			return; 
		}
		$ime = str_replace("(","",$ime);
		$ime = str_replace(")","",$ime);
		$imena = explode(" ",$ime);
		$upit = "";
		foreach($imena as $dio) {
			if ($upit != "") $upit .= " and ";
			$upit .= "(o.ime like '%$dio%' or o.prezime like '%$dio%' or a.login like '%$dio%' or o.brindexa like '%$dio%')";
		}
		$q10 = myquery("select o.ime, o.prezime, o.brindexa, a.login, o.id from auth as a, osoba as o where a.id=o.id and $upit order by o.prezime, o.ime");
		$redova=0;
		while ($r10 = mysql_fetch_row($q10)) {
			if (strlen($r10[3])<2) continue; // ?? Preskačemo sistemske korisnike koji nemaju login?
			$rezultat['data'][$r10[4]] = array( 'ime' => $r10[0], 'prezime' => $r10[1], 'brindexa' => $r10[2], 'login' => $r10[3] );
			$redova++;
			if ($redova>10) break;
		}
		
		print json_encode($rezultat); 
		return; 
	}

	if (isset($_REQUEST['id']))
		$korisnik = intval($_REQUEST['id']);
	else if (isset($_REQUEST['login'])) {
		$korisnik = -1;
		$q5 = myquery("SELECT id FROM auth WHERE login='$param_login'");
		if (mysql_num_rows($q5) > 0) 
			$korisnik = mysql_result($q5,0,0);
	}
	else
		$korisnik = $userid;

	if (!$user_siteadmin && !$user_studentska && $korisnik != $userid) {
		$rezultat = array( 'success' => 'false', 'code' => 'ERR002', 'message' => 'Permission denied' );
	} else {
		$q10 = myquery("SELECT ime, prezime FROM osoba WHERE id=$korisnik");
		if (mysql_num_rows($q10) < 1) {
			$rezultat = array( 'success' => 'false', 'code' => 'ERR005', 'message' => 'Unknown user' );
		} else {
			$podaci['id'] = $korisnik;
			$podaci['ime'] = mysql_result($q10,0,0);
			$podaci['prezime'] = mysql_result($q10,0,1);

			$podaci['logini'] = array();
			$q20 = myquery("SELECT login FROM auth WHERE id=$korisnik");
			while($r20 = mysql_fetch_row($q20))
				$podaci['logini'][] = $r20[0];
				
			// Određujemo RSS ID
			$q200 = myquery("select id from rss where auth=$korisnik");
			if (mysql_num_rows($q200)<1) {
				// kreiramo novi ID
				do {
					$rssid="";
					for ($i=0; $i<10; $i++) {
						$slovo = rand()%62;
						if ($slovo<10) $sslovo=$slovo;
						else if ($slovo<36) $sslovo=chr(ord('a')+$slovo-10);
						else $sslovo=chr(ord('A')+$slovo-36);
						$rssid .= $sslovo;
					}
					$q210 = myquery("select count(*) from rss where id='$rssid'");
				} while (mysql_result($q210,0,0)>0);
				$q220 = myquery("insert into rss set id='$rssid', auth=$korisnik");
			} else {
				$rssid = mysql_result($q200,0,0);
			}
			$podaci['rssid'] = $rssid;

			$rezultat['data'] = $podaci;
		}
	}
	
	echo json_encode($rezultat);
}


?>