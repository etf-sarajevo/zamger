<?


// BUILDSERVICE.PHP - serverska strana skripte za buildservice

require("../lib/libvedran.php");
require("../lib/zamger.php");
require("../lib/config.php");
require("../lib/manip.php"); // zbog update_komponente

dbconnect2($conf_dbhost,$conf_dbuser,$conf_dbpass,$conf_dbdb);



// Čekamo 15 minuta na buildhost
$conf_buildhost_timeout = 60*15;

$result = array();

$result['success'] = "true";
$result['data'] = array();

$autotest_basepath = "/tmp/autotest";


// Lists of source filename extensions per language
$conf_extensions = array(
	"C"    => array( ".c", ".h" ),
	"C++"  => array( ".cpp", ".h", ".cxx", ".hxx" ),
	"C++11"  => array( ".cpp", ".h", ".cxx", ".hxx" ),
	"Java" => array( ".java" ),
);



check_cookie();

if ($userid!=0)
	$priv = odredi_privilegije_korisnika();

if ($userid==0) {
	$result['success'] = "false";
	$result['code'] = "ERR001";
	$result['message'] = "User session expired";
}

else if ($priv === false) {
	$result['success'] = "false";
	$result['code'] = "ERR002";
	$result["message"] = "User has no privileges";
}


else if ($_REQUEST['action'] == "nextTask") {
	if (!$user_autotester) {
		$result['success'] = "false";
		$result['code'] = "ERR004";
		$result["message"] = "Only autotester has access to nextTask";
	} else {
		$task = false;
		$q = myquery("SELECT id, zadataka FROM zadaca WHERE automatsko_testiranje=1 ORDER BY id");
		while ($r = mysql_fetch_row($q)) {
			for ($i=1; $i<=$r[1]; $i++)
				if (dajZadatak($r[0], $i)) {
					$task = $r[0] * 100 + $i;
					break;
				}
		}
		if ($task == false)
			$result['data']['id'] = "false";
		else
			$result['data']['id'] = $task;
	}
}


else if ($_REQUEST['action'] == "assignProgram") {
	if (!$user_autotester && !$user_siteadmin) {
		$result['success'] = "false";
		$result['code'] = "ERR004";
		$result["message"] = "Only autotester has access to assignProgram";
	} else {
		$task = intval($_REQUEST['task']);
		$zadaca = intval($task/100);
		$zadatak = $task % 100;
		$buildhost_id = my_escape($_REQUEST['buildhost']);
		$zadatak = dajZadatak($zadaca, $zadatak);
		if ($zadatak) {
			$q100 = myquery("DELETE FROM buildservice_tracking WHERE zadatak=$zadatak");
			$q110 = myquery("INSERT INTO buildservice_tracking SET zadatak=$zadatak, buildhost='$buildhost_id'");
			$result['data']['id'] = $zadatak;
		} else
			$result['data']['id'] = "false";
	}
}

else if ($_REQUEST['action'] == "getTaskData") {
	$task = intval($_REQUEST['task']);
	$zadaca = intval($task/100);
	$zadatak = $task % 100;
	
	if (!$user_siteadmin && !$user_autotester && !pravo_pristupa($zadaca,0)) {
		$result['success'] = "false";
		$result['code'] = "ERR003";
		$result["message"] = "Access denied";

	} else {
		$q = myquery("SELECT z.naziv, p.naziv, ag.naziv, pj.naziv, pj.kompajler, pj.opcije_kompajlera, pj.opcije_kompajlera_debug, z.zadataka FROM zadaca as z, predmet as p, akademska_godina as ag, programskijezik as pj WHERE z.id=$zadaca AND z.predmet=p.id AND z.akademska_godina=ag.id AND z.programskijezik=pj.id");
		if (mysql_num_rows($q) == 0) {
			$result['success'] = "false";
			$result['code'] = "ERR005";
			$result["message"] = "Unknown task ID $id";
		}
		
		$r = mysql_fetch_row($q);
		$result['data']['name'] = $r[1]." (".$r[2]."), ".$r[0];
		if ($r[7] > 1) $result['data']['name'] .= ", zadatak $zadatak";
		$result['data']['language'] = $r[3];

		// FIXME:
		if ($r[3] == "C++11") $result['data']['language'] = "C++";

		// FIXME:
		$result['data']['required_compiler']  = $r[4];
		$result['data']['preferred_compiler'] = $r[4];
		$result['data']['compiler_features']  = array();
		$result['data']['compiler_options']   = $r[5];
		$result['data']['compiler_options_debug'] = $r[6];

		// Šta sve treba raditi sa zadaćom
		$result['data']['compile'] = "true";
		$result['data']['run']     = "false";
		$result['data']['test']    = "true";
		if ($r[3] == "Python") {
			$result['data']['debug']   = "false";
			$result['data']['profile'] = "false";
		} else {
			$result['data']['debug']   = "true";
			$result['data']['profile'] = "true";
		}
		if ($zadaca == 2890) $result['data']['profile'] = "false";

		// Tests
		$result['data']['test_specifications'] = array();
		
		// require symbols HACK
		$q3 = myquery("SELECT tip, specifikacija, zamijeni FROM autotest_replace WHERE zadaca=$zadaca AND zadatak=$zadatak");
		$replace_symbols = array();
		$require_symbols = array();
		while ($r3 = mysql_fetch_row($q3)) {
			if ($r3[2] === "")
				array_push($require_symbols, $r3[1]);
			else {
				$replace = array();
				$replace['type'] = $r3[0];
				$replace['match'] = $r3[1];
				$replace['replace'] = $r3[2];
			}
		}
		
		$q2 = myquery("SELECT id, kod, rezultat, alt_rezultat, fuzzy, global_scope, pozicija_globala, stdin, partial_match FROM autotest WHERE zadaca=$zadaca AND zadatak=$zadatak AND aktivan=1");
		while ($r2 = mysql_fetch_row($q2)) {
			$test = array();
			$test['id'] = $r2[0];
			$test['require_symbols'] = $require_symbols;
			$test['replace_symbols'] = $replace_symbols;
			$test['code'] = $r2[1];
			
			if ($r2[6] === 'prije_maina') {
				$test['global_above_main'] = $r2[5];
				$test['global_top'] = "";
			} else {
				$test['global_top'] = $r2[5];
				$test['global_above_main'] = "";
			}
			
			$test['running_params'] = array();
			$test['running_params']['timeout'] = 10; // TODO hardcodirano 10 sekundi
			$test['running_params']['vmem'] = 1000000; // TODO hardcodirano ~200 MB
			$test['running_params']['stdin'] = $r2[7];
			
			$test['expected'] = array();
			if ($r2[2] === "===IZUZETAK===") // TODO dodati switch u GUI, tip izuzetka
				$test['expected_exception'] = "true";
			else {
				$test['expected_exception'] = "false";
				array_push($test['expected'], $r2[2]);
				if ($r2[3] !== "") array_push($test['expected'], $r2[3]);
			}

			$test['expected_crash'] = "false"; // TODO implementirati
			
			// TODO Napraviti sve kao jedan dropdown
			$test['ignore_whitespace'] = "false";
			$test['regex'] = "false";
			if ($r2[4] === "1")
				$test['ignore_whitespace'] = "true";
			else if ($r2[4] === "2")
				$test['regex'] = "true";
			
			if ($r2[8] === "1")
				$test['substring'] = "true";
			else
				$test['substring'] = "false";
				
			array_push($result['data']['test_specifications'], $test);
		}
	}
}


else if ($_REQUEST['action'] == "getProgramData") {
	$program = intval($_REQUEST['program']);
	$q = myquery("SELECT zk.zadaca, zk.redni_broj, zk.student, o.ime, o.prezime, o.brindexa, zk.status FROM zadatak as zk, osoba as o WHERE zk.id=$program AND zk.student=o.id");
	if (mysql_num_rows($q)<1) {
		$result['success'] = "false";
		$result['code'] = "ERR007";
		$result["message"] = "Unknown program id $program";
	} else {
		$r = mysql_fetch_row($q);
		$zadaca = $r[0];
		$zadatak = $r[1];
		$student = $r[2];
		$status = $r[6];
		if (!$user_siteadmin && !$user_autotester && !pravo_pristupa($zadaca,$student)) {
			$result['success'] = "false";
			$result['code'] = "ERR003";
			$result["message"] = "Access denied";
		} else {
			$result['data']['id'] = $program;
			$result['data']['name'] = $r[4]." ".$r[3]." (".$r[5].")";
			$result['data']['task'] = $zadaca * 100 + $zadatak;
			$result['data']['status'] = $r[6];
		}
	}
}


else if ($_REQUEST['action'] == "getFile") {
	global $conf_files_path;

	$id = intval($_REQUEST['program']);

	$q = myquery("SELECT zk.zadaca, zk.student, zk.filename, z.predmet, z.akademska_godina FROM zadatak as zk, zadaca as z WHERE zk.id=$id AND zk.zadaca=z.id");
	$r = mysql_fetch_row($q);
	if (mysql_num_rows($q)<1) {
		$result['success'] = "false";
		$result['code'] = "ERR007";
		$result["message"] = "Unknown program id $program";
		print json_encode($result);
		dbdisconnect();
		exit;
	}
	$zadaca = $r[0];
	$student = $r[1];
	$filename = $r[2];
	$predmet = $r[3];
	$ag = $r[4];

	$filepath="$conf_files_path/zadace/$predmet-$ag/$student/$zadaca/$filename";

	if (!$user_siteadmin && !$user_autotester && !pravo_pristupa($zadaca,$student)) {
		$result['success'] = "false";
		$result['code'] = "ERR003";
		$result["message"] = "Access denied";

	} else {
		if (substr($filename, strlen($filename)-4) !== ".zip") {
			$tmp = "$conf_files_path/zadace/temporary$id.zip";
			`zip -j $tmp $filepath`;
			$filepath = $tmp;
			$filename = "temporary.zip";
		}

		$type = `file -bi '$filepath'`;
		header("Content-Type: $type");
		header('Content-Disposition: attachment; filename="' . $filename.'"', false);
		header("Content-Length: ".(string)(filesize($filepath)));

		// workaround za http://support.microsoft.com/kb/316431 (zamger bug 94)
		header("Pragma: dummy=bogus"); 
		header("Cache-Control: private");

		$k = readfile($filepath,false);

		unlink("$conf_files_path/zadace/temporary$id.zip");
	}
}



else if ($_REQUEST['action'] == "setCompileResult") {
	$id = intval($_REQUEST['program']);

	$q = myquery("SELECT zadaca, redni_broj, student, status, bodova, komentar, filename FROM zadatak WHERE id=$id");
	if (mysql_num_rows($q)<1) {
		$result['success'] = "false";
		$result['code'] = "ERR007";
		$result["message"] = "Unknown program id $id";
	} else {
		$r = mysql_fetch_row($q);
		$zadaca = $r[0];
		$student = $r[1];
		
		$privilegija = nastavnik_pravo_pristupa($zadaca);
		
		if (!$user_siteadmin && !$user_autotester && ($privilegija === false || $privilegija == "asistent" && !nastavnik_ogranicenje($zadaca, $student))) {
			$result['success'] = "false";
			$result['code'] = "ERR003";
			$result["message"] = "Access denied";
		} else {
			$cr = json_decode($_REQUEST['result'], true);
			if (array_key_exists("parsed_output", $cr))
				$output = join ("\n", $cr['parsed_output'] );
			else
				$output = $cr['output'];
			$output = my_escape($output);
			
			$q2 = myquery("INSERT INTO zadatak SET zadaca=$r[0], redni_broj=$r[1], student=$r[2], status=$r[3], bodova=$r[4], izvjestaj_skripte='$output', vrijeme=NOW(), komentar='".mysql_real_escape_string($r[5])."', filename='".mysql_real_escape_string($r[6])."', userid=$userid");
		}
	}
}



else if ($_REQUEST['action'] == "setProgramStatus") {
	$id = intval($_REQUEST['program']);
	$q = myquery("SELECT zadaca, redni_broj, student, bodova, izvjestaj_skripte, komentar, filename FROM zadatak WHERE id=$id");
	if (mysql_num_rows($q)<1) {
		$result['success'] = "false";
		$result['code'] = "ERR007";
		$result["message"] = "Unknown program id $id";
	} else {
		$r = mysql_fetch_row($q);
		$zadaca = $r[0];
		$zadatak = $r[1];
		$student = $r[2];
		
		$privilegija = nastavnik_pravo_pristupa($zadaca);
		zamgerlog("autotestiran student u$student z$zadaca zadatak $zadatak", 2);
		zamgerlog2("autotestiran student", intval($student), intval($zadaca), intval($zadatak));
		
		if (!$user_siteadmin && !$user_autotester && ($privilegija === false || $privilegija == "asistent" && !nastavnik_ogranicenje($zadaca, $student))) {
			$result['success'] = "false";
			$result['code'] = "ERR003";
			$result["message"] = "Access denied";
		} else {
			$q = myquery("SELECT zadaca, redni_broj, student, bodova, izvjestaj_skripte, komentar, filename FROM zadatak WHERE zadaca=$zadaca AND redni_broj=$zadatak AND student=$student ORDER BY id DESC LIMIT 1");
			$r = mysql_fetch_row($q);
			$status = intval($_REQUEST['status']);
			$izvjestaj_skripte = mysql_real_escape_string($r[4]);
			if ($status == 6) { $status=3; $izvjestaj_skripte .= "\nNije pronađena nijedna datoteka sa kodom u odabranom programskom jeziku."; }
			
			$q2 = myquery("INSERT INTO zadatak SET zadaca=$zadaca, redni_broj=$zadatak, student=$student, status=$status, bodova=$r[3], izvjestaj_skripte='$izvjestaj_skripte', vrijeme=NOW(), komentar='".mysql_real_escape_string($r[5])."', filename='".mysql_real_escape_string($r[6])."', userid=$userid");
			
			$q3 = myquery("DELETE FROM buildservice_tracking WHERE zadatak=$id");
		}
	}
	
}



else if ($_REQUEST['action'] == "setTestResult") {
	$program = intval($_REQUEST['program']);
	$test = intval($_REQUEST['test']);

	$q = myquery("SELECT zk.student, zk.filename, zk.zadaca, pj.naziv, z.predmet, z.akademska_godina, pj.opcije_kompajlera, pj.opcije_kompajlera_debug FROM zadatak as zk, zadaca as z, programskijezik as pj WHERE zk.id=$program and zk.zadaca=z.id and z.programskijezik=pj.id");
	if (mysql_num_rows($q)<1) {
		$result['success'] = "false";
		$result['code'] = "ERR007";
		$result["message"] = "Unknown program id $id";
	} else {
	$r = mysql_fetch_row($q);
	$zadaca = $r[2];
	$student = $r[0];
		
	$privilegija = nastavnik_pravo_pristupa($zadaca);
	
	if (!$user_siteadmin && !$user_autotester && ($privilegija === false || $privilegija == "asistent" && !nastavnik_ogranicenje($zadaca, $student))) {
		$result['success'] = "false";
		$result['code'] = "ERR003";
		$result["message"] = "Access denied";

	} else {
		$filename = $r[1];
		$jezik = $r[3];
		$predmet = $r[4];
		$ag = $r[5];
		$komp_opc = $r[6];
		$komp_opc_debug = $r[7];

		$filepath="$conf_files_path/zadace/$predmet-$ag/$student/$zadaca/$filename";

		// Učitavamo sve fajlove radi ispisa linije u kojoj je greška
		$nalaz = "";
		$sources = array();
		if (ends_with($filename, ".zip")) {
			directoryCleanup ($autotest_basepath);
			$output = `unzip "$filepath" -d $autotest_basepath`;
			$filelist = array();
			foreach ($conf_extensions[$jezik] as $ekst) {
				$k = exec("find $autotest_basepath -name \\*$ekst", $blah, $return);
				$filelist = array_unique(array_merge($filelist, $blah));
			}
			if ($filelist === array()) {
				$result['success'] = "false";
				$result['code'] = "ERR006";
				$result["message"] = "Failed to unzip";
				print json_encode($result);
				dbdisconnect();
				exit();
			}

			foreach($filelist as $filename) {
				$sources[$filename] = file($filename);
			}

		} else
			$sources[$filepath] = file($filepath);

		$tr = json_decode($_REQUEST['result'], true);
		$izlaz_programa = mysql_real_escape_string($tr['run_result']['output']);


		switch($tr['status']) {
			case 1: $status="ok"; break;
			case 2: $status="no_func"; $nalaz .= "Nije pronađena funkcija: ".$tr['status_object']; break;

			// Compiler error
			case 3: {
				$status="error"; 
				$nalaz .= "Greška prilikom kompajliranja autotesta.\n";
				if (array_key_exists('parsed_output', $tr['compile_result'])) 
					// FIXME!
					$nalaz .= join ("\n", $tr['compile_result']['parsed_output']) . "\n";
				if (strlen($tr['compile_result']['output']) > 0) 
					$nalaz .= "\nIZLAZ KOMPAJLERA:\n".$tr['compile_result']['output'] . "\n\n";
				break;
			}
			case 4: $status="too_long"; $nalaz .= "Izvršavanje programa je trajalo " . $tr['run_result']['duration'] . " sekundi, što je predugo.\n\n"; break;
			case 5: $status="crash"; break;
			case 6: $status="wrong"; $nalaz .= "Izlaz programa ne odgovara očekivanom.\n\n"; break;
			case 7:
			switch($tr['profile_result']['status']) {
				case 1: $status="ok"; break;
				case 2: $status="oob"; break;
				case 3: $status="uninit"; break;
				case 4: $status="memleak"; break;
				case 5: $status="invalid_free"; break;
				case 6: $status="mismatched_free"; break;
			}
			break;
			case 8: $status="find_fail"; $nalaz .= "Program nije ispisao ono što se očekuje. Mogući razlozi: program se krahirao, pozvana je funkcija exit().\n\n"; break;
			case 9: $status="wrong"; $izlaz_programa .= "===IZUZETAK==="; $nalaz .= "Izlaz programa ne odgovara očekivanom.\n\n"; break;
			break;
		}
		
		// Izlaze debuggera i profilera dajemo uvijek kada status nije OK

		// Propasirani izlaz gdba
		if ( $status != "ok" && (array_key_exists("parsed_output", $tr['debug_result'])) ) 
			foreach ($tr['debug_result']['parsed_output'] as $msg) {
				$nalaz .= "\nProgram se krahirao.\nLokacija greške: ";
				$nalaz .= lociraj_gresku($msg['file'], $msg['line'], $test, $sources) . "\n\n";
			}
		
		// Propasirani izlaz valgrinda
		$nepasirani_valgrind = "";
		if ( $status != "ok" && (array_key_exists("parsed_output", $tr['profile_result'])) ) {
			foreach ($tr['profile_result']['parsed_output'] as $msg) {
				switch($msg['type']) {
					case 2: $nalaz .= "Izlazak van opsega niza/vektora ili pristup ilegalnom pokazivaču"; break;
					case 3: $nalaz .= "Pristup vrijednosti koja nije inicijalizovana"; break;
					case 4: $nalaz .= "Curenje memorije"; break;
					case 5: $nalaz .= "Dealokacija visećeg pokazivača"; break;
					case 6: $nalaz .= "Nije korišten odgovarajući dealokator (delete[] vs. delete)"; break;
				}
				$nalaz .= "\nLokacija greške: ";
				$nalaz .= lociraj_gresku($msg['file'], $msg['line'], $test, $sources);
				if (array_key_exists("file_alloced", $msg)) {
					$nalaz .= "Memorija (de)alocirana u: ";
					$nalaz .= lociraj_gresku($msg['file_alloced'], $msg['line_alloced'], $test, $sources);
				}
				$nalaz .= "\n\n";
				// Ovo je ljepši ispis od pravog nepasiranog valgrinda jer ne sadrži viškove
				$nepasirani_valgrind .= $msg['output'] . "\n\n";
			}
		}
		
		// Nepropasirani izlaz
		if ($status != "ok" && strlen($tr['debug_result']['output']) > 0)
			$nalaz .= "IZLAZ DEBUGGERA:\n" . $tr['debug_result']['output'] . "\n\n";
			
		if ($status != "ok" && strlen($tr['profile_result']['output']) > 0) {
			// Koristimo ljepsi izlaz za nepasirani valgrind ako postoji
			if ($nepasirani_valgrind === "") $nepasirani_valgrind = $tr['profile_result']['output'];
			$nalaz .= "IZLAZ PROFILERA:\n$nepasirani_valgrind";
		}
			
		$nalaz = my_escape($nalaz);
		
		$trajanje = intval($tr['run_result']['duration']);
		
		$bh = json_decode($_REQUEST['buildhost'], true);
		$bhos = str_replace("\n","<br>",$bh['os']);
		$bhos = my_escape($bhos);
		$bhos = str_replace("&lt;br&gt;","<br>",$bhos);

		$specifikacija_hosta = "<b>Testni sistem:</b><br>".my_escape($bh['id'])."<br><br><b>OS:</b><br>".$bhos."<br><br><b>Verzija kompajlera:</b><br>".my_escape($bh['compiler_version'])."<br><br><b>Opcije kompajlera:</b><br>".my_escape($komp_opc)."<br><br><b>Verzija debuggera</b><br>".my_escape($bh['debugger_version'])."<br><br><b>Verzija profilera:</b><br>".my_escape($bh['profiler_version']);
		
		$q2 = mysql_query("DELETE FROM autotest_rezultat WHERE autotest=$test AND student=$r[0]");
		$q2 = myquery("INSERT INTO autotest_rezultat SET autotest=$test, student=$r[0], izlaz_programa='$izlaz_programa', status='$status', nalaz='$nalaz', vrijeme=NOW(), trajanje=$trajanje, testni_sistem='$specifikacija_hosta'");
	}
	}
}


else if ($_REQUEST['action'] == "getTaskList") {
	if ($user_siteadmin || $user_autotester) {
		$q = myquery("SELECT z.id, z.naziv, p.naziv, ag.naziv, pj.naziv, z.zadataka FROM zadaca as z, predmet as p, akademska_godina as ag, programskijezik as pj WHERE z.predmet=p.id AND z.akademska_godina=ag.id AND z.programskijezik=pj.id AND pj.id>0 ORDER BY z.id DESC LIMIT 100"); // Nećemo da pretjeramo
	} else {
		// Upit za zadaće nastavnika
		$q = myquery("SELECT z.id, z.naziv, p.naziv, ag.naziv, pj.naziv, z.zadataka FROM zadaca as z, predmet as p, akademska_godina as ag, programskijezik as pj, nastavnik_predmet as np WHERE z.predmet=p.id AND z.akademska_godina=ag.id AND z.programskijezik=pj.id AND pj.id>0 AND np.nastavnik=$userid AND np.predmet=z.predmet AND np.akademska_godina=z.akademska_godina ORDER BY z.id DESC LIMIT 100");
		if (mysql_num_rows($q) == 0)
			// Ako je upit prazan probavamo zadaće studenta
			$q = myquery("SELECT z.id, z.naziv, p.naziv, ag.naziv, pj.naziv, z.zadataka FROM zadaca as z, predmet as p, akademska_godina as ag, programskijezik as pj, student_predmet as sp, ponudakursa as pk WHERE z.predmet=p.id AND z.akademska_godina=ag.id AND z.programskijezik=pj.id AND pj.id>0 AND sp.student=$userid AND sp.predmet=pk.id AND pk.predmet=z.predmet AND pk.akademska_godina=z.akademska_godina ORDER BY z.id DESC LIMIT 100");
	}
	$result['data'] = array();
	while ($r = mysql_fetch_row($q)) {
		for ($i=1; $i<=$r[5]; $i++) {
			$task = array();
			$task['id'] = $r[0] * 100 + $i;
			$task['name'] = $r[2]." (".$r[3]."), ".$r[1];
			if ($r[5] > 1) $task['name'] .= ", zadatak $i";
			$task['language'] = $r[4];
			array_push($result['data'], $task);
		}
	}
}


else if ($_REQUEST['action'] == "getProgList") {
	$task = intval($_REQUEST['task']);
	$zadaca = intval($task/100);
	$zadatak = $task % 100;
	
	if (!$user_siteadmin && !$user_autotester && !pravo_pristupa($zadaca,0)) {
		$result['success'] = "false";
		$result['code'] = "ERR003";
		$result["message"] = "Access denied";

	} else {
		$result['data'] = array();
		$q30 = myquery("select distinct student from zadatak where zadaca=$zadaca and redni_broj=$zadatak AND status=1");
		while ($r30 = mysql_fetch_row($q30)) {
			$student = $r30[0];

			// Zadnja instanca zadatka
			$q40 = myquery("select id from zadatak where zadaca=$zadaca and redni_broj=$zadatak and student=$student order by id desc limit 1");

			// Podaci studenta
			$q50 = myquery("SELECT ime, prezime, brindexa FROM osoba WHERE id=$student");
			$r50 = mysql_fetch_row($q50);
			
			$program=array();
			$program['id'] = mysql_result($q40,0,0);
			$program['name'] = $r50[1]." ".$r50[0]." (".$r50[2].")";
			array_push($result['data'], $program);
		}
	}
}

else {
	$result['success'] = "false";
	$result['code'] = "ERR999";
	$result['message'] = "Unknown action ".$_REQUEST['action'];
}

print json_encode($result);

dbdisconnect();


function odredi_privilegije_korisnika() {
	global $userid, $user_student, $user_nastavnik, $user_studentska, $user_siteadmin, $user_autotester;

	// FIXME
	if ($userid == 3376) $user_autotester=true;

	$user_student=$user_nastavnik=$user_studentska=$user_siteadmin=false;
	if ($userid>0) {
		$q10 = myquery("select privilegija from privilegije where osoba=$userid");
		while ($r10=mysql_fetch_row($q10)) {
			if ($r10[0]=="student") $user_student=true; 
			if ($r10[0]=="nastavnik") $user_nastavnik=true;
			if ($r10[0]=="studentska") $user_studentska=true;
			if ($r10[0]=="siteadmin") $user_siteadmin=true;
			//if ($r10[0]=="prijemni")  -- ovi nemaju pristup zamgeru
			// ovdje dodati ostale vrste korisnika koje imaju pristup
		}

		// Korisnik nije ništa!?
		if (!$user_student && !$user_nastavnik && !$user_studentska && !$user_siteadmin) {
			//echo "FAIL|Vaše korisničko ime je ispravno, ali nemate nikakve privilegije na sistemu! Kontaktirajte administratora.";
			return false;
		}
		return true;
	}
}

function student_pravo_pristupa($zadaca) {
	global $userid;

	$q20 = myquery("SELECT COUNT(*) FROM student_predmet as sp, zadaca as z, ponudakursa as pk WHERE sp.student=$userid AND sp.predmet=pk.id AND pk.predmet=z.predmet AND pk.akademska_godina=z.akademska_godina AND z.id=$zadaca");
	if (mysql_result($q20,0,0) == 1) return true;
	return false;
}

function nastavnik_pravo_pristupa($zadaca) {
	global $userid;

	// Da li korisnik ima pravo ući u grupu?
	$q40 = myquery("select np.nivo_pristupa from nastavnik_predmet as np, zadaca as z where np.nastavnik=$userid and np.predmet=z.predmet and np.akademska_godina=z.akademska_godina and z.id=$zadaca");
	if (mysql_num_rows($q40)<1) {
		// Nastavnik nije angažovan na predmetu
		return false;
	}
	return mysql_result($q40,0,0);
}

function nastavnik_ogranicenje($zadaca, $student) {
	global $userid;

	$q45 = myquery("select l.id from student_labgrupa as sl, labgrupa as l, zadaca as z where sl.student=$student and sl.labgrupa=l.id and l.predmet=z.predmet and l.akademska_godina=z.akademska_godina and l.virtualna=0 and z.id=$zadaca");
	$q50 = myquery("select o.labgrupa from ogranicenje as o, labgrupa as l, zadaca as z where o.nastavnik=$userid and o.labgrupa=l.id and l.predmet=z.predmet and l.akademska_godina=z.akademska_godina and l.virtualna=0 and z.id=$zadaca");
	if (mysql_num_rows($q45)<1) {
		if (mysql_num_rows($q50)<1) {
			// imate ogranicenja a student nije u grupi
			return false;
		}
		return true;
	}
	$labgrupa = mysql_result($q45,0,0);

	if (mysql_num_rows($q50)>0) {
		$nasao=0;
		while ($r50 = mysql_fetch_row($q50)) {
			if ($r50[0] == $labgrupa) { $nasao=1; break; }
		}
		if ($nasao == 0) {
			// echo "FAIL|ogranicenje na labgrupu $labgrupa";
			return false;
		}
	}
	return true;
}

function pravo_pristupa($zadaca, $student=0) {
	global $userid;
	
	// Ima pravo pristupa svojim zadaćama
	if ($userid == $student) return true;

	// Student ima pravo pristupa podacima zadaće na predmetima koje sluša
	if (student_pravo_pristupa($zadaca)) return true;

	// Nastavnici i super-asistenti mogu pristupati svemu
	// Asistent može pristupiti postavci zadaće
	$privilegija = nastavnik_pravo_pristupa($zadaca);
	if ($privilegija === false) return false;
	if ($student==0 || $privilegija != "asistent") return true;
	
	// Za asistente provjeravamo ograničenja na labgrupe
	return nastavnik_ogranicenje($zadaca, $student);
}


// Vraća sljedeći neprocesirani zadatak u zadaći ili false ako nema
function dajZadatak($zadaca, $zadatak) {
	global $conf_buildhost_timeout;

	$q30 = myquery("select distinct student from zadatak where zadaca=$zadaca and redni_broj=$zadatak AND status=1");
	while ($r30 = mysql_fetch_row($q30)) {
		$student = $r30[0];

		// Zadnja instanca zadatka
		$q40 = myquery("select id, status, userid from zadatak where zadaca=$zadaca and redni_broj=$zadatak and student=$student order by id desc limit 1");
		$id = mysql_result($q40,0,0);
		$status = mysql_result($q40,0,1);
		$zadnji_izmijenio = mysql_result($q40,0,2);

		// Preskačemo zadaće za koje status nije 1 (u međuvremenu asistent napravio izmjene)
		if ($status != 1) continue;

		// Preskačemo nalaze autotesta
		//if ($zadnji_izmijenio== 3376) { print"Imamo nalaz autotesta<br>\n"; continue; } // FIXME ovo ne treba biti hardcodirano

		// Preskačemo zadatke koje neko već radi
		$q50 = myquery("SELECT UNIX_TIMESTAMP(vrijeme) FROM buildservice_tracking WHERE zadatak=$id");
		if (mysql_num_rows($q50) > 0) {
		//print "Vrijeme ".mysql_result($q50,0,0)." time ".time()." razlika ".(mysql_result($q50,0,0) - time())." timeout $conf_buildhost_timeout<br>\n";
			if (time() - mysql_result($q50,0,0) < $conf_buildhost_timeout)
				continue;
		}

		return $id;
	}
	return false;
}


// Ensure that $path exists and is an empty directory
function directoryCleanup($path) 
{
	if (!file_exists($path)) {
		mkdir($path, 0777, true);
	} else if (!is_dir($path)) {
		unlink($path);
		mkdir($path);
	} else {
		rmMinusR($path);
	}
}

function lociraj_gresku($file, $line, $test, $sources)
{
	$nalaz = "";
	if (strlen($file)>8 && substr($file, 0, 9) === "TEST_CODE") {
		$nalaz .= "Unutar testnog koda";
		$q5 = myquery("SELECT kod, global_scope FROM autotest WHERE id=$test");
		if ($file === "TEST_CODE")
			$kod = explode("\n", mysql_result($q5,0,0));
		else {
			$nalaz .= " (globalni opseg)";
			$kod = explode("\n", mysql_result($q5,0,1));
		}
		$nalaz .= ",";
	}
	else if (count($sources)>1) {
		foreach ($sources as $sourcefile => $sourcecode) {
			if (basename($sourcefile) === basename($file)) {
				$nalaz .= "Datoteka ".basename($file).",";
				$kod = $sourcecode;
				break;
			}
		}
	} else $kod = array_shift(array_values($sources));
	if ($line-1 >= count($kod))
		$nalaz .= " kraj koda (pozivi destruktora)\n";
	else {
		$nalaz .= " Linija $line:\n";
		$nalaz .= $kod[$line-1]; /* Numeracija linija počinje od 1 */
		if (strlen($file)>8 && substr($file, 0, 9) === "TEST_CODE") $nalaz .= "\n";
	}
	return $nalaz;
}

?>
