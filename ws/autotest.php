<?

// WS/AUTOTEST - web servis za autotestove



function ws_autotest() {
	global $userid, $user_student, $user_nastavnik, $user_studentska, $user_siteadmin;

	require_once("lib/permisije.php");

	$rezultat = array( 'success' => 'true', 'data' => array() );
	
	if (isset($_REQUEST['student']))
		$student = intval($_REQUEST['student']);
	else
		$student = $userid;

	$autotest = $zadaca = $predmet = $ag = 0;
	if (isset($_REQUEST['autotest'])) {
		$autotest = intval($_REQUEST['autotest']);
		$q5 = db_query("SELECT zadaca FROM autotest WHERE id=$autotest");
		if (db_num_rows($q5) > 0)
			$_REQUEST['zadaca'] = db_result($q5,0,0); // Sada će se kaskadno izvršiti sljedeći blok
	}
	if (isset($_REQUEST['zadaca'])) {
		if (isset($_REQUEST['zadaca'])) $zadaca = intval($_REQUEST['zadaca']);
		
		$q10 = db_query("SELECT predmet, akademska_godina FROM zadaca WHERE id=$zadaca");
		if (db_num_rows($q10) > 0) {
			$predmet = db_result($q10,0,0);
			$ag = db_result($q10,0,1);
		}
	}

	// Vraća zamjene za dati autotest
	if ($_REQUEST['akcija'] == "zamjene") {
		if ($_SERVER['REQUEST_METHOD'] == "GET") {
			// Student ima pravo pristupa postavci zadaća na predmetima koje sluša
			// Nastavnik može pristupiti postavci zadaća za svoje predmete
			if (!$user_siteadmin && !nastavnik_pravo_pristupa($predmet, $ag, 0) && !daj_ponudu_kursa($student, $predmet, $ag)) {
				print json_encode( array( 'success' => 'false', 'code' => 'ERR002', 'message' => 'Permission denied' ) );
				return;
			} else {
				$zadatak = intval($_REQUEST['zadatak']);
				$q10 = db_query("select zadaca, zadatak, tip, specifikacija, zamijeni from autotest_replace where zadaca=$zadaca and zadatak=$zadatak");
				while ($dbrow = db_fetch_assoc($q10)) {
					array_push($rezultat['data'], $dbrow);
				}
			}
		}
	}

	// Rezultat testiranja za dati autotest
	else if ($_REQUEST['akcija'] == "rezultat") {
		if ($_SERVER['REQUEST_METHOD'] == "POST") {
			$status = db_escape($_REQUEST['status']);
			$nalaz = db_escape($_REQUEST['nalaz']);
			$izlaz_programa = db_escape($_REQUEST['izlaz_programa']);
			$trajanje = intval($_REQUEST['trajanje']);
			
			// Student ne može postavljati status autotestova za vlastitu zadaću
			if (!$user_siteadmin && !nastavnik_pravo_pristupa($predmet, $ag, $student)) {
				print json_encode( array( 'success' => 'false', 'code' => 'ERR002', 'message' => 'Permission denied' ) );
				return;
			} else {
				db_query("DELETE FROM autotest_rezultat WHERE autotest=$autotest AND student=$student");
				db_query("INSERT INTO autotest_rezultat set autotest=$autotest, student=$student, status='$status', nalaz='$nalaz', izlaz_programa='$izlaz_programa', trajanje=$trajanje");
				$rezultat['message'] = "Postavljen rezultat";
			}
		}
		if ($_SERVER['REQUEST_METHOD'] == "GET") {
			//$rezultat['data']['autotest'] = $autotest;
			//$rezultat['data']['zadaca'] = $zadaca;
			if (!$user_siteadmin && !nastavnik_pravo_pristupa($predmet, $ag, $student) && $student != $userid) {
				print json_encode( array( 'success' => 'false', 'code' => 'ERR002', 'message' => 'Permission denied' ) );
				return;
			} else {
				//$rezultat['data']['upit'] = "SELECT autotest, student, status, nalaz, izlaz_programa, trajanje FROM autotest_rezultat WHERE autotest=$autotest AND student=$student";
				$q100 = db_query("SELECT autotest, student, status, nalaz, izlaz_programa, trajanje FROM autotest_rezultat WHERE autotest=$autotest AND student=$student");
				while ($dbrow = db_fetch_assoc($q100)) {
					array_push($rezultat['data'], $dbrow);
				}
			}
		}
	}

	// Vraća sve testcaseove za zadaću i zadatak
	else if ($_SERVER['REQUEST_METHOD'] == "GET") {
		$zadatak = intval($_REQUEST['zadatak']);
		if (!$user_siteadmin && !nastavnik_pravo_pristupa($predmet, $ag, 0) && !daj_ponudu_kursa($student, $predmet, $ag)) {
			print json_encode( array( 'success' => 'false', 'code' => 'ERR002', 'message' => 'Permission denied' ) );
			return;
		} else {
			$q10 = db_query("select id, kod, rezultat, alt_rezultat, fuzzy, global_scope, pozicija_globala, stdin, partial_match from autotest where zadaca=$zadaca and zadatak=$zadatak");
			while ($dbrow = db_fetch_assoc($q10)) {
				array_push($rezultat['data'], $dbrow);
			}
		}
	}


	print json_encode($rezultat);
}


?>
