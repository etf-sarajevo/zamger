<?

// IZVJESTAJ/FOR_LOOPER - spajanje više izvještaja po opsegu nekog parametra



function izvjestaj_for_looper() {
	global $sadrzaj_bafera_za_csv,$conf_files_path, $registry;
	global $userid, $user_student, $user_nastavnik, $user_studentska, $user_siteadmin;

	$koji = db_escape($_REQUEST['koji_izvjestaj']);
	$staf = str_replace("/","_",$koji);

	$found=false;
	foreach ($registry as $r) {
		if ($r[0] == $koji) {
			if (strstr($r[3],"P") || (strstr($r[3],"S") && $user_student) || (strstr($r[3],"N") && $user_nastavnik) || (strstr($r[3],"B") && $user_studentska) || (strstr($r[3],"A") && $user_siteadmin)) {
				$found=true;
			} else {
				zamgerlog ("for_looper pristup nedozvoljenom modulu $koji", 3);
				zamgerlog2 ("pristup nedozvoljenom modulu", 0, 0, 0, $koji);
				niceerror("Pristup nedozvoljenom modulu");
				return;
			}
			break;
		}
	}
	if ($found===false) {
		zamgerlog ("for_looper nepostojeći modul $koji", 3);
		zamgerlog2 ("nepostojeci modul", 0, 0, 0, $koji);
		niceerror("Pristup nepostojećem modulu");
		return;
	}

	include_once("$koji.php");//ovdje ga ukljucujem

	$for_loop_vars = array();

	// Čitanje for_loop varijabli iz upita
	foreach ($_REQUEST as $key => $value) {
		if (substr($key,0,8) == "for_loop") {
			list ($var, $range) = explode(" ", $value);
			$for_loop_vars[$var] = $range;
		}
	}

	// Čitanje for_upit varijabli iz upita
	foreach ($_REQUEST as $key => $value) {
		if ($key == "for_studij") {
			$studij = intval($value);
			$last_semester = db_get("SELECT ts.trajanje FROM tipstudija ts, studij s WHERE s.id=$studij AND s.tipstudija=ts.id");
			$semestri = "";
			$start = 2 + intval($_REQUEST['for_semestar']);
			for ($i=$start; $i<=$last_semester; $i++) {
				if ($semestri != "") $semestri .= " or ";
				$semestri .= "pk.semestar=$i";
			}
			if ($_REQUEST['for_semestar'] == 0)
				$start = 2;
				
			$q10 = db_query("SELECT DISTINCT pk.predmet
				FROM ponudakursa as pk, akademska_godina as ag, predmet as p, studij as s
				WHERE pk.studij=".intval($value)." and pk.akademska_godina=ag.id AND ag.aktuelna=1 and ($semestri) and pk.predmet=p.id and pk.studij=s.id and
					p.institucija=s.institucija
				ORDER BY pk.semestar, p.naziv");
			$range = "";
			while ($r10 = db_fetch_row($q10)) {
				if ($range != "") $range .= ",";
				$range .= $r10[0];
			}			
			$for_loop_vars["predmet"] = $range;
		}
		if ($key == "for_studij_student") {
			$studij = intval($value);
			$upit = "SELECT ss.student FROM student_studij ss WHERE ss.studij=$studij";
			if (array_key_exists("for_studij_student_ag", $_REQUEST))
				$upit .= " AND ss.akademska_godina=".intval($_REQUEST["for_studij_student_ag"]);
			if (array_key_exists("for_studij_student_ponovac", $_REQUEST))
				$upit .= " AND ss.ponovac=".intval($_REQUEST["for_studij_student_ponovac"]);
			if (array_key_exists("for_studij_student_semestar", $_REQUEST))
				$upit .= " AND ss.semestar=".intval($_REQUEST["for_studij_student_semestar"]);
			$q10 = db_query($upit);
			$range = "";
			while ($r10 = db_fetch_row($q10)) {
				if ($range != "") $range .= ",";
				$range .= $r10[0];
			}			
			//$for_loop_vars["osoba"] = $range;
			$for_loop_vars["student"] = $range;
		}
		if ($key == "for_pgs") {
			if ($_REQUEST['for_semestar'] == 0) $semestar = 2; else $semestar = 1;
			$q10 = db_query("SELECT DISTINCT pk.predmet
				FROM ponudakursa as pk, predmet as p, studij as s, akademska_godina ag
				WHERE pk.akademska_godina=ag.id AND ag.aktuelna=1 and pk.semestar=$semestar and pk.predmet=p.id and pk.studij=s.id AND s.tipstudija=2
				ORDER BY pk.semestar, p.naziv");
			$range = "";
			while ($r10 = db_fetch_row($q10)) {
				if ($range != "") $range .= ",";
				$range .= $r10[0];
			}			
			$for_loop_vars["predmet"] = $range;
		}
	}
	

	$kombinacije = array();

	foreach ($for_loop_vars as $var => $range) {
		$values = array();
		if (strstr($range,",")) {
			$values = explode(",", $range);
		} else if (strstr($range,"-")) {
			list ($begin, $end) = explode("-", $range);
			for ($i=$begin; $i<=$end; $i++)
				$values[]=$i;
		}

		$tmp_kombinacije = array();
		if (empty($kombinacije)) {
			foreach ($values as $value) {
				$tmp = array();
				$tmp[] = "$var=$value";
				array_push ($tmp_kombinacije, $tmp);
			}
		} else {
			foreach ($kombinacije as $komb) {
				foreach ($values as $value) {
					$tmp = $komb;
					$tmp[] = "$var=$value";
					array_push ($tmp_kombinacije, $tmp);
				}
			}
		}
		$kombinacije = $tmp_kombinacije;
	}
	

	if ($_REQUEST['fltip'] == "spiskovi") {
		$ag = intval($_REQUEST['ag']);
		if ($_REQUEST['flpodtip'] == "ljeto")
			$q10 = db_query("select DISTINCT pk.predmet, l.id from ponudakursa as pk, labgrupa as l where pk.semestar mod 2=0 and pk.akademska_godina=$ag and pk.predmet=l.predmet and l.akademska_godina=$ag and l.virtualna=1");
		else
			$q10 = db_query("select DISTINCT pk.predmet, l.id from ponudakursa as pk, labgrupa as l where pk.semestar mod 2=1 and pk.akademska_godina=$ag and pk.predmet=l.predmet and l.akademska_godina=$ag and l.virtualna=1");

		while ($r10 = db_fetch_row($q10)) {
			$komb = array();
			$komb[] = "predmet=$r10[0]";
			$komb[] = "grupa=$r10[1]";
			$kombinacije[] = $komb;
		}
	}

	$i=1;
	foreach ($kombinacije as $komb) {
//print "Komb ".($i++)."<br>\n";
		foreach ($komb as $dijelovi) {
			list($key, $value) = explode("=", $dijelovi);
			$_REQUEST[$key]=$value;
//print "Key: $key Value: $value<br>\n";
		}
		eval("$staf();");
		print "\n<DIV style=\"page-break-after:always\"></DIV>\n";
	}
//	print "Radi!\n";
//	eval("$staf();");

}

?>
