<?

// WS/ZADACA - servisi za zadaću



function ws_zadaca() {
	global $userid, $user_student, $user_nastavnik, $user_studentska, $user_siteadmin, $conf_files_path;
	
	require_once("lib/permisije.php");
	require_once("lib/student_predmet.php"); // update_komponente
	require_once("lib/utility.php"); // ends_with, rm_minus_r, clear_unicode
	
	
	$rezultat = array( 'success' => 'true', 'data' => array() );
	
	if (isset($_REQUEST['student']))
		$student = intval($_REQUEST['student']);
	else
		$student = $userid;
		
	$predmet = $ag = $zadaca = 0;
	if (isset($_REQUEST['zadaca']) || isset($_REQUEST['id'])) {
		if (isset($_REQUEST['zadaca'])) $zadaca = intval($_REQUEST['zadaca']);
		if (isset($_REQUEST['id'])) $zadaca = intval($_REQUEST['id']);
		
		$q10 = db_query("SELECT predmet, akademska_godina FROM zadaca WHERE id=$zadaca");
		if (db_num_rows($q10) < 1) {
			header("HTTP/1.0 404 Not Found");
			$rezultat = array( 'success' => 'false', 'code' => 'ERR404', 'message' => 'Not found' );
			print json_encode($rezultat);
			return;
		}
		$predmet = db_result($q10,0,0);
		$ag = db_result($q10,0,1);
	}

	// Podaci o programskom jeziku
	if ($_REQUEST['akcija'] == "jezik") {
		$id = intval($_REQUEST['id']);
		$q10 = db_query("select * from programskijezik where id=$id");
		if (db_num_rows($q10) < 1) {
			header("HTTP/1.0 404 Not Found");
			$rezultat = array( 'success' => 'false', 'code' => 'ERR404', 'message' => 'Not found' );
			print json_encode($rezultat);
			return;
		}
		while ($dbrow = db_fetch_assoc($q10)) {
			array_push($rezultat['data'], $dbrow);
		}
	}
	
	// Podaci o jednoj zadaći
	else if ($_SERVER['REQUEST_METHOD'] == "GET" && isset($_GET['id'])) { // bilo = dajZadacu
		$id = intval($_GET['id']);
		
		// Student ima pravo pristupa postavci zadaća na predmetima koje sluša
		// Nastavnik može pristupiti postavci zadaća za svoje predmete
		if (!$user_siteadmin && !nastavnik_pravo_pristupa($predmet, $ag, 0) && !daj_ponudu_kursa($student, $predmet, $ag)) {
			print json_encode( array( 'success' => 'false', 'code' => 'ERR002', 'message' => 'Permission denied' ) );
			return;
		} else {
			$q10 = db_query("select * from zadaca where id=$id");
			while ($dbrow = db_fetch_assoc($q10)) {
				array_push($rezultat['data'], $dbrow);
			}
		}
	}

	// Vraća redni broj zadatka ako je dat filename
	else if ($_REQUEST['akcija'] == "dajZadatakIzFajla") {
		$filename = db_escape($_REQUEST['filename']);
		
		// Student može vidjeti svoje fajlove, nastavnik može vidjeti fajlove studenta
		if (!$user_siteadmin && !nastavnik_pravo_pristupa($predmet, $ag, $student) && $student != $userid) {
			print json_encode( array( 'success' => 'false', 'code' => 'ERR002', 'message' => 'Permission denied' ) );
			return;
		} else {
			$q10 = db_query("select redni_broj from zadatak where zadaca=$zadaca and student=$student and filename='$filename' limit 1");
			while ($dbrow = db_fetch_assoc($q10)) {
				array_push($rezultat['data'], $dbrow);
			}
		}
	}

	// Vraća redni broj zadatka ako je dat filename
	else if ($_REQUEST['akcija'] == "dajZadatak") {
		$zadatak = intval($_REQUEST['zadatak']);
		
		$q10 = db_query("SELECT status, bodova, izvjestaj_skripte, komentar, filename, vrijeme FROM zadatak WHERE zadaca=$zadaca AND redni_broj=$zadatak AND student=$student ORDER BY id DESC LIMIT 1");
		if (db_num_rows($q10) < 1) {
			header("HTTP/1.0 404 Not Found");
			$rezultat = array( 'success' => 'false', 'code' => 'ERR404', 'message' => 'Not found' );
			print json_encode($rezultat);
			return;
		}
		
		$rezultat['data'] = db_fetch_assoc($q10);
		$q20 = db_query("SELECT id FROM autotest WHERE zadaca=$zadaca AND zadatak=$zadatak");
		$testovi = array();
		while(db_fetch1($q20, $autotest)) {
			$q30 = db_query("SELECT izlaz_programa, status, nalaz, vrijeme, trajanje FROM autotest_rezultat WHERE autotest=$autotest AND student=$student");
			$at_rez = db_fetch_assoc($q30);
			$at_rez['id'] = $autotest;
			// Izlaz programa može sadržavati invalidne karaktere, ali je u pravilu ASCII
			$at_rez['izlaz_programa'] = clear_unicode($at_rez['izlaz_programa']);
			$testovi[] = $at_rez;
		}
		if (!empty($testovi)) $rezultat['data']['autotest_rezultat'] = $testovi;
		
		// Studenti ne vide log
		if ($user_siteadmin || nastavnik_pravo_pristupa($predmet, $ag, $student))
			$rezultat['data']['log'] = db_query_table("SELECT id, status, bodova, izvjestaj_skripte, komentar, filename, vrijeme, userid FROM zadatak WHERE zadaca=$zadaca AND redni_broj=$zadatak AND student=$student ORDER BY id");
	}

	// Vraća redni broj zadatka ako je dat filename
	else if ($_REQUEST['akcija'] == "dajFajl") {
		$zadatak = intval($_REQUEST['zadatak']);
		
		$filename = db_get("SELECT filename FROM zadatak WHERE zadaca=$zadaca AND redni_broj=$zadatak AND student=$student ORDER BY id DESC LIMIT 1");
		if ($filename === false) {
			header("HTTP/1.0 404 Not Found");
			$rezultat = array( 'success' => 'false', 'code' => 'ERR404', 'message' => 'Not found' );
			print json_encode($rezultat);
			return;
		}
		$lokacijazadaca="$conf_files_path/zadace/$predmet-$ag/$student/";
		$the_file = "$lokacijazadaca$zadaca/$filename";

		if (!file_exists($the_file)) {
			header("HTTP/1.0 404 Not Found");
			$rezultat = array( 'success' => 'false', 'code' => 'ERR404', 'message' => 'Not found' );
			print json_encode($rezultat);
			return;
		}
		readfile($the_file);
		return;
	}

	// Postavlja status zadaće
	else if ($_SERVER['REQUEST_METHOD'] == "POST" && $_REQUEST['akcija'] == "status") {
		$zadatak = intval($_REQUEST['zadatak']);
		
		// Student sam ne može mijenjati status svojih zadaća
		if (!$user_siteadmin && !nastavnik_pravo_pristupa($predmet, $ag, $student)) {
			print json_encode( array( 'success' => 'false', 'code' => 'ERR002', 'message' => 'Permission denied' ) );
			return;
		} else {
			// Odredjujemo ponudu kursa (za update komponente)
			$ponudakursa = db_get("select pk.id from student_predmet as sp, ponudakursa as pk, zadaca as z where sp.student=$student and sp.predmet=pk.id and pk.predmet=z.predmet and pk.akademska_godina=z.akademska_godina and z.id=$zadaca");
			if ($ponudakursa === false) {
				header("HTTP/1.0 404 Not Found");
				$rezultat = array( 'success' => 'false', 'code' => 'ERR404', 'message' => "Student $student nije upisan na predmet kojem pripada zadaca $zadaca" );
				print json_encode($rezultat);
				return;
			}
			
			require("lib/manip.php"); // zbog update komponente
			
			$komentar = db_escape($_REQUEST['komentar']);
			$izvjestaj_skripte = db_escape($_REQUEST['izvjestaj_skripte']);
			$status = intval($_REQUEST['status']);
			$bodova = floatval(str_replace(",",".",$_REQUEST['bodova']));
			$vrijeme = intval($_REQUEST['vrijeme']);
		
			// Filename
			$q90 = db_query("select filename from zadatak where zadaca=$zadaca and redni_broj=$zadatak and student=$student  order by id desc limit 1");
			$filename = db_result($q90,0,0);

			if ($vrijeme==0)
				$q100 = db_query("insert into zadatak set zadaca=$zadaca, redni_broj=$zadatak, student=$student, status=$status, bodova=$bodova, vrijeme=now(), komentar='$komentar', izvjestaj_skripte='$izvjestaj_skripte', filename='$filename', userid=$userid");
			else
				$q100 = db_query("insert into zadatak set zadaca=$zadaca, redni_broj=$zadatak, student=$student, status=$status, bodova=$bodova, vrijeme=FROM_UNIXTIME($vrijeme), komentar='$komentar', izvjestaj_skripte='$izvjestaj_skripte', filename='$filename', userid=$userid");


			update_komponente($student, $ponudakursa, $komponenta);

			zamgerlog("izmjena zadace (student u$student zadaca z$zadaca zadatak $zadatak)",2);
			$rezultat['message'] = "Ažuriran status zadaće";
		}
	}
	
	// Slanje zadaće
	else if ($_SERVER['REQUEST_METHOD'] == "POST") {
		$zadatak = intval($_REQUEST['zadatak']);
		
		// Nastavnik ima pravo poslati zadaću u ime studenta (u logu će ostati zabilježeno ko je poslao)
		// Student može poslati svoju zadaću
		if (!$user_siteadmin && !nastavnik_pravo_pristupa($predmet, $ag, $student) && $student != $userid) {
			print json_encode( array( 'success' => 'false', 'code' => 'ERR002', 'message' => 'Permission denied' ) );
			return;
		}
		
		// Podaci o zadaći
		$q210 = db_query("select programskijezik, UNIX_TIMESTAMP(rok), attachment, naziv, komponenta, dozvoljene_ekstenzije, automatsko_testiranje, predmet, akademska_godina, zadataka, aktivna from zadaca where id=$zadaca");
		if (db_num_rows($q210) < 1) {
			header("HTTP/1.0 404 Not Found");
			print json_encode( array( 'success' => 'false', 'code' => 'ERR404', 'message' => 'Not found' ) );
			return;
		}
		$jezik = db_result($q210,0,0);
		$rok = db_result($q210,0,1);
		$attach = db_result($q210,0,2);
		$naziv_zadace = db_result($q210,0,3);
		$komponenta = db_result($q210,0,4);
		$zadaca_dozvoljene_ekstenzije = db_result($q210,0,5);
		$automatsko_testiranje = db_result($q210,0,6);
		$predmet = db_result($q210,0,7);
		$ag = db_result($q210,0,8);
		$zadataka = db_result($q210,0,9);
		$aktivna = db_result($q210,0,10);
		
		if ($aktivna == 0 && !$user_siteadmin && !nastavnik_pravo_pristupa($predmet, $ag, $student)) {
			print json_encode( array( 'success' => 'false', 'code' => 'ERR912', 'message' => 'Zadaća nije aktivna' ) );
			return;
		}
		
		if ($zadatak < 1 || $zadatak > $zadataka) {
			print json_encode( array( 'success' => 'false', 'code' => 'ERR907', 'message' => 'Neispravan redni broj zadatka' ) );
			return;
		}

		// Provjera roka
		if ($rok <= time() && !$user_siteadmin && !nastavnik_pravo_pristupa($predmet, $ag, $student)) {
			print json_encode( array( 'success' => 'false', 'code' => 'ERR908', 'message' => 'Vrijeme za slanje zadaće je isteklo' ) );
			return;
		}
		
		$lokacijazadaca="$conf_files_path/zadace/$predmet-$ag/$student/";
		if (!file_exists("$conf_files_path/zadace/$predmet-$ag")) {
			mkdir ("$conf_files_path/zadace/$predmet-$ag",0777, true);
		}
		
		// Ako je aktivno automatsko testiranje, postavi status na 1 (automatska kontrola), inace na 4 (ceka pregled)
		if ($automatsko_testiranje==1) $prvi_status=1; else $prvi_status=4;

		// Prepisane zadaće se ne mogu ponovo slati
		$q240 = db_query("select status from zadatak where zadaca=$zadaca and redni_broj=$zadatak and student=$student order by id desc limit 1");
		if (db_num_rows($q240) > 0 && db_result($q240,0,0) == 2 && $userid == $student) { // status = 2 - prepisana zadaća
			print json_encode( array( 'success' => 'false', 'code' => 'ERR909', 'message' => 'Zadaća je prepisana i ne može se ponovo poslati' ) );
			return;
		}

		// Pravimo potrebne puteve
		if (!file_exists($lokacijazadaca)) mkdir ($lokacijazadaca,0777);
		if ($zadaca>0 && !file_exists("$lokacijazadaca$zadaca")) 
			mkdir ("$lokacijazadaca$zadaca",0777);
		
		// Temp fajl radi određivanja diff-a 
		if (file_exists("$lokacijazadaca$zadaca/difftemp")) 
			unlink ("$lokacijazadaca$zadaca/difftemp");

		$program = $_FILES['attachment']['tmp_name'];
		if ($program && (file_exists($program)) && $_FILES['attachment']['error']===UPLOAD_ERR_OK) {
			$ime_fajla = strip_tags(basename($_FILES['attachment']['name']));
			
			// Forsiramo ime fajla za non-attach
			if ($attach == 0) {
				$q220 = db_query("select ekstenzija from programskijezik where id=$jezik");
				$ekst = db_result($q220,0,0);
				$ime_fajla = $zadatak.$ekst;
			}

			// Ukidam HTML znakove radi potencijalnog XSSa
			$ime_fajla = str_replace("&", "", $ime_fajla);
			$ime_fajla = str_replace("\"", "", $ime_fajla);
			$puni_put = "$lokacijazadaca$zadaca/$ime_fajla";

			// Provjeravamo da li je ekstenzija na spisku dozvoljenih
			$ext = ".".pathinfo($ime_fajla, PATHINFO_EXTENSION); // FIXME: postojeći kod očekuje da ekstenzije počinju tačkom...
			$db_doz_eks = explode(',',$zadaca_dozvoljene_ekstenzije);
			if ($zadaca_dozvoljene_ekstenzije != "" && !in_array($ext, $db_doz_eks)) {
				print json_encode( array( 'success' => 'false', 'code' => 'ERR910', 'message' => 'Nedozvoljen tip datoteke $ext' ) );
				return;
			}
			
			// Diffing
			$diff = "";
			$q255 = db_query("SELECT filename FROM zadatak WHERE zadaca=$zadaca AND redni_broj=$zadatak AND student=$student ORDER BY id DESC LIMIT 1");
			if (db_num_rows($q255) > 0) {
				$stari_filename = "$lokacijazadaca$zadaca/".db_result($q255, 0, 0);

				// Podržavamo diffing ako je i stara i nova ekstenzija ZIP (TODO ostale vrste arhiva)
				if (ends_with($stari_filename, ".zip") && ends_with($program, ".zip")) {
				
					// Pripremamo temp dir
					$zippath = "/tmp/difftemp";
					if (!file_exists($zippath)) {
						mkdir($zippath, 0777, true);
					} else if (!is_dir($zippath)) {
						unlink($zippath);
						mkdir($zippath);
					} else {
						rm_minus_r($zippath);
					}
					$oldpath = "$zippath/old";
					$newpath = "$zippath/new";
					mkdir ($oldpath);
					mkdir ($newpath);
					`unzip -j "$stari_filename" -d $oldpath`;
					`unzip -j "$program" -d $newpath`;
					$diff = `/usr/bin/diff -ur $oldpath $newpath`;
					$diff = clear_unicode(db_escape($diff));
				} else {
					rename ($stari_filename, "$lokacijazadaca$zadaca/difftemp"); 
					$diff = `/usr/bin/diff -u $lokacijazadaca$zadaca/difftemp $program`;
					$diff = db_escape($diff);
					unlink ("$lokacijazadaca$zadaca/difftemp");
				}
			}
		
			if (file_exists($puni_put)) unlink ($puni_put);
			rename($program, $puni_put);
			chmod($puni_put, 0640);

			// Escaping za SQL
			$ime_fajla = db_escape($ime_fajla);

			$q260 = db_query("insert into zadatak set zadaca=$zadaca, redni_broj=$zadatak, student=$student, status=$prvi_status, vrijeme=now(), filename='$ime_fajla', userid=$userid");
			$id_zadatka = db_insert_id();

			if (strlen($diff)>1) {
				$q270 = db_query("insert into zadatakdiff set zadatak=$id_zadatka, diff='$diff'");
			}
			$rezultat['message'] = "Zadaća uspješno poslana";
			zamgerlog("poslana zadaca z$zadaca zadatak $zadatak (webservice)",2); // nivo 2 - edit
			zamgerlog2("poslana zadaca (webservice)", $zadaca, $zadatak);
		} else {
			zamgerlog("greska pri slanju zadace (zadaca z$zadaca zadatak $zadatak - webservice)",3);
			zamgerlog2("greska pri slanju zadace (webservice)", $zadaca, $zadatak);
			print json_encode( array( 'success' => 'false', 'code' => 'ERR911', 'message' => 'Slanje zadaće nije uspjelo. Molimo pokušajte ponovo' ) );
			return;
		}
	}

	
	// Default akcija: spisak zadaća koje su vidljive studentu u tekućoj akademskoj godini, sa bodovima
	else {
		if (isset($_REQUEST['ag']))
			$ag = intval($_REQUEST['ag']);
		else {
			$q10 = db_query("select id from akademska_godina where aktuelna=1 order by id desc limit 1");
			$ag = db_result($q10,0,0);
		}
		
		if (isset($_REQUEST['predmet']))
			$predmet = intval($_REQUEST['predmet']);
		
		// Nastavnik može vidjeti podatke studenata u svojim grupama
		// Student može vidjeti svoje podatke
		if (!$user_siteadmin && !nastavnik_pravo_pristupa($predmet, $ag, $student) && $student != $userid) {
			print json_encode( array( 'success' => 'false', 'code' => 'ERR002', 'message' => 'Permission denied' ) );
			return;
		} 
		
		$rezultat['data']['predmeti'] = array();
		$upit = "SELECT p.id, p.naziv, p.kratki_naziv 
		FROM student_predmet as sp, ponudakursa as pk, predmet as p 
		WHERE sp.student=$student and sp.predmet=pk.id and pk.akademska_godina=$ag and pk.predmet=p.id";
		if ($predmet > 0) $upit .= " AND p.id=$predmet";
		$q100 = db_query($upit);
		
		// Nastavnik može vidjeti spisak zadaća za svoj predmet
		if (db_num_rows($q100) == 0 && $predmet>0 && nastavnik_pravo_pristupa($predmet, $ag, 0)) {
			$upit = "SELECT p.id, p.naziv, p.kratki_naziv 
			FROM nastavnik_predmet as np, predmet as p
			WHERE np.nastavnik=$userid and np.akademska_godina=$ag and np.predmet=p.id and p.id=$predmet";
			$q100 = db_query($upit);
		}
		
		while (db_fetch3($q100, $id_predmeta, $naziv_predmeta, $kratki_naziv)) {
			$predmet = array();
			$predmet['id'] = $id_predmeta;
			$predmet['naziv'] = $naziv_predmeta;
			$predmet['kratki_naziv'] = $kratki_naziv;
			$predmet['zadace'] = array();
			
			$q110 = db_query("select id, naziv, bodova, zadataka, programskijezik, attachment, postavka_zadace, UNIX_TIMESTAMP(rok), aktivna from zadaca where predmet=$id_predmeta and akademska_godina=$ag order by komponenta,id");
			while ($r110 = db_fetch_row($q110)) {
				$zadaca = array();
				$zadaca['id'] = $r110[0];
				$zadaca['naziv'] = $r110[1];
				$zadaca['bodova'] = 0;
				$zadaca['moguce_bodova'] = $r110[2];
				$zadaca['broj_zadataka'] = $r110[3];
				$zadaca['programski_jezik'] = $r110[4];
				if ($r110[5]==1) $zadaca['attachment'] = 'true'; else $zadaca['attachment'] = 'false';
				$zadaca['rok'] = $r110[7];
				if ($r110[8]==1) $zadaca['aktivna'] = 'true'; else $zadaca['aktivna'] = 'false';
				
				$zadaca['zadaci'] = array();
				for ($zadatak=1;$zadatak<=$r110[3];$zadatak++) {
					$zad_ar = array();
					$zad_ar['redni_broj'] = $zadatak;
					// Uzmi samo rjesenje sa zadnjim IDom
					$q22 = db_query("select status, bodova, komentar, izvjestaj_skripte, vrijeme, filename from zadatak where student=$student and zadaca=$r110[0] and redni_broj=$zadatak order by id desc limit 1");
					if (db_num_rows($q22)<1) {
						$zad_ar['poslan'] = 'false';
					} else {
						$zad_ar['poslan'] = 'true';
						$zad_ar['status'] = db_result($q22,0,0);
						$zad_ar['bodova'] = db_result($q22,0,1);
						$zad_ar['komentar_tutora'] = db_result($q22,0,2);
						$zad_ar['izvjestaj_skripte'] = db_result($q22,0,3);
						$zad_ar['vrijeme_slanja'] = db_result($q22,0,4);
						$zad_ar['filename'] = db_result($q22,0,5);
						$zadaca['bodova'] += $zad_ar['bodova'];
					}
					$zadaca['zadaci'][] = $zad_ar;
				}
				
				$predmet['zadace'][] = $zadaca;
			}
			$rezultat['data']['predmeti'][] = $predmet;
		}
	}


	print json_encode($rezultat);
}


?>
