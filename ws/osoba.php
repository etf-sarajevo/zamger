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
		
	if (isset($_REQUEST['akcija']) && $_REQUEST['akcija'] == "detalji") {
		if (!$user_siteadmin && !$user_studentska && $korisnik != $userid) {
			$rezultat = array( 'success' => 'false', 'code' => 'ERR002', 'message' => 'Permission denied' );
			echo json_encode($rezultat);
			return;
		}
		$detalji = true;
	} else
		$detalji = false;
		
			
	if (isset($_REQUEST['akcija']) && $_REQUEST['akcija'] == "tituliraj") {
		$rezultat['data'] = tituliraj($korisnik);
		echo json_encode($rezultat);
		return;
	}

	// Upit za podatke o korisniku
	if ($detalji) $upit = "ime, prezime, imeoca, prezimeoca, imemajke, prezimemajke, spol, brindexa, datum_rodjenja, mjesto_rodjenja, nacionalnost, drzavljanstvo, jmbg, adresa, adresa_mjesto, telefon, kanton, naucni_stepen, strucni_stepen";
	else $upit = "ime, prezime";
	
	$q10 = myquery("SELECT $upit FROM osoba WHERE id=$korisnik");
	if (mysql_num_rows($q10) < 1) {
		header("HTTP/1.0 404 Not Found");
		$rezultat = array( 'success' => 'false', 'code' => 'ERR404', 'message' => 'Not found' );
	} else {
		$podaci['id'] = $korisnik;
		$podaci['ime'] = mysql_result($q10,0,0);
		$podaci['prezime'] = mysql_result($q10,0,1);
		if ($detalji) {
			$podaci['imeoca'] = mysql_result($q10,0,2);
			$podaci['prezimeoca'] = mysql_result($q10,0,3);
			$podaci['imemajke'] = mysql_result($q10,0,4);
			$podaci['prezimemajke'] = mysql_result($q10,0,5);
			$podaci['spol'] = mysql_result($q10,0,6);
			$podaci['brindexa'] = mysql_result($q10,0,7);
			$podaci['datum_rodjenja'] = mysql_result($q10,0,8);
			
			$q20 = myquery("SELECT m.naziv, o.naziv, d.naziv, m.opcina_van_bih FROM mjesto m, opcina o, drzava d WHERE m.opcina=o.id AND m.drzava=d.id AND m.id=".mysql_result($q10,0,9));
			$podaci['mjesto'] = array();
			$podaci['mjesto']['naziv'] = mysql_result($q20,0,0);
			$podaci['mjesto']['drzava'] = mysql_result($q20,0,2);
			if ($podaci['mjesto']['drzava'] == "Bosna i Hercegovina")
				$podaci['mjesto']['opcina'] = mysql_result($q20,0,1);
			else
				$podaci['mjesto']['opcina'] = mysql_result($q20,0,3);
			
			$q30 = myquery("SELECT naziv FROM nacionalnost WHERE id=".mysql_result($q10,0,10));
			$podaci['nacionalnost'] = mysql_result($q30,0,0);
			
			$q40 = myquery("SELECT naziv FROM drzava WHERE id=".mysql_result($q10,0,11));
			$podaci['drzavljanstvo'] = mysql_result($q40,0,0);
			
			$podaci['datum_rodjenja'] = mysql_result($q10,0,12);
			
			$podaci['adresa'] = array();
			$podaci['adresa']['ulica_i_broj'] = mysql_result($q10,0,13);
			$q50 = myquery("SELECT m.naziv, o.naziv, d.naziv, m.opcina_van_bih FROM mjesto m, opcina o, drzava d WHERE m.opcina=o.id AND m.drzava=d.id AND m.id=".mysql_result($q10,0,14));
			$podaci['adresa']['mjesto'] = mysql_result($q50,0,0);
			$podaci['adresa']['drzava'] = mysql_result($q50,0,2);
			if ($podaci['adresa']['drzava'] == "Bosna i Hercegovina")
				$podaci['adresa']['opcina'] = mysql_result($q50,0,1);
			else
				$podaci['adresa']['opcina'] = mysql_result($q50,0,3);
			$q60 = myquery("SELECT naziv FROM kanton WHERE id=".mysql_result($q10,0,16));
			$podaci['adresa']['kanton'] = mysql_result($q60,0,0);
			
			$podaci['telefon'] = mysql_result($q10,0,15);
			
			$q70 = myquery("SELECT naziv FROM naucni_stepen WHERE id=".mysql_result($q10,0,17));
			$podaci['naucni_stepen'] = mysql_result($q70,0,0);
			
			$q80 = myquery("SELECT naziv FROM strucni_stepen WHERE id=".mysql_result($q10,0,18));
			$podaci['strucni_stepen'] = mysql_result($q80,0,0);
		}

		$podaci['logini'] = array();
		$q200 = myquery("SELECT login FROM auth WHERE id=$korisnik");
		while($r200 = mysql_fetch_row($q200))
			$podaci['logini'][] = $r200[0];
			
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
	
	echo json_encode($rezultat);
}


?>