<?

// WS/AUTOTEST - web servis za autotestove



function ws_autotest() {
	global $userid, $user_student, $user_nastavnik, $user_studentska, $user_siteadmin;

	$rezultat = array( 'success' => 'true', 'data' => array() );
	
	if (isset($_REQUEST['student']))
		$student = intval($_REQUEST['student']);
	else
		$student = $userid;

	// Vraća zamjene za dati autotest
	if ($_REQUEST['akcija'] == "zamjene") {
		if ($_SERVER['REQUEST_METHOD'] == "GET") {
			$zadaca = intval($_REQUEST['zadaca']);
			$zadatak = intval($_REQUEST['zadatak']);
			if (!$user_siteadmin && !pravo_pristupa($zadaca,0)) {
				print json_encode( array( 'success' => 'false', 'code' => 'ERR002', 'message' => 'Permission denied' ) );
				return;
			} else {
				$q10 = myquery("select zadaca, zadatak, tip, specifikacija, zamijeni from autotest_replace where zadaca=$zadaca and zadatak=$zadatak");
				while ($dbrow = mysql_fetch_assoc($q10)) {
					array_push($rezultat['data'], $dbrow);
				}
			}
		}
	}

	// Rezultat testiranja za dati autotest
	else if ($_REQUEST['akcija'] == "rezultat") {
		$autotest = intval($_REQUEST['autotest']);
		$student = intval($_REQUEST['student']);
		if ($_SERVER['REQUEST_METHOD'] == "POST") {
			$status = my_escape($_REQUEST['status']);
			$nalaz = my_escape($_REQUEST['nalaz']);
			$izlaz_programa = my_escape($_REQUEST['izlaz_programa']);
			$trajanje = intval($_REQUEST['trajanje']);
			
			// Student ne može postavljati status autotestova za vlastitu zadaću
			if (!$user_siteadmin && !nastavnik_pravo_pristupa($zadaca)) {
				print json_encode( array( 'success' => 'false', 'code' => 'ERR002', 'message' => 'Permission denied' ) );
				return;
			} else {
				myquery("DELETE FROM autotest_rezultat WHERE autotest=$autotest AND student=$student");
				myquery("INSERT INTO autotest_rezultat set autotest=$autotest, student=$student, status='$status', nalaz='$nalaz', izlaz_programa='$izlaz_programa', trajanje=$trajanje");
				$rezultat['message'] = "Postavljen rezultat";
			}
		}
		if ($_SERVER['REQUEST_METHOD'] == "GET") {
			$q100 = myquery("SELECT autotest, student, status, nalaz, izlaz_programa, trajanje FROM autotest_rezultat WHERE autotest=$autotest AND student=$student");
			while ($dbrow = mysql_fetch_assoc($q10)) {
				array_push($rezultat['data'], $dbrow);
			}
		}
	}

	// Vraća sve testcaseove za zadaću i zadatak
	else if ($_SERVER['REQUEST_METHOD'] == "GET") {
		$zadaca = intval($_REQUEST['zadaca']);
		$zadatak = intval($_REQUEST['zadatak']);
		if (!$user_siteadmin && !pravo_pristupa($zadaca,0)) {
			print json_encode( array( 'success' => 'false', 'code' => 'ERR002', 'message' => 'Permission denied' ) );
			return;
		} else {
			$q10 = myquery("select id, kod, rezultat, alt_rezultat, fuzzy, global_scope, pozicija_globala, stdin, partial_match from autotest where zadaca=$zadaca and zadatak=$zadatak");
			while ($dbrow = mysql_fetch_assoc($q10)) {
				array_push($rezultat['data'], $dbrow);
			}
		}
	}


	print json_encode($rezultat);
}


// Ovdje nažalost kopiram kod iz ws/zadaca za provjeru prava pristupa...

// Da li korisnik $userid ima pravo pristupa zadaći $zadaca za studenta $student
// Ako je $student==0 onda se podatak odnosi na sve studente (read-only pristup)
function pravo_pristupa($zadaca, $student=0) {
	global $userid;
	
	// Korisnik ima pravo pristupa svojim zadaćama
	// Student ima pravo pristupa podacima zadaće na predmetima koje sluša
	if (($student == $userid || $student == 0) && student_pravo_pristupa($zadaca)) return true;

	// Nastavnici i super-asistenti mogu pristupati svemu
	// Asistent može pristupiti postavci zadaće
	$privilegija = nastavnik_pravo_pristupa($zadaca);
	if ($privilegija === false) return false;
	if ($student==0 || $privilegija != "asistent") return true;
	
	// Za asistente provjeravamo ograničenja na labgrupe
	return nastavnik_ogranicenje($zadaca, $student);
}


function student_pravo_pristupa($zadaca) {
	global $userid;

	$q20 = myquery("SELECT COUNT(*) FROM student_predmet as sp, zadaca as z, ponudakursa as pk WHERE sp.student=$userid AND sp.predmet=pk.id AND pk.predmet=z.predmet AND pk.akademska_godina=z.akademska_godina AND z.id=$zadaca");
	if (mysql_result($q20,0,0) > 0) return true;
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
		if (mysql_num_rows($q50)>0) {
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
