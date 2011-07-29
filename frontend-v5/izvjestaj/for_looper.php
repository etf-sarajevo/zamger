<?

function izvjestaj_for_looper() {
	global $sadrzaj_bafera_za_csv,$conf_files_path, $registry;
	global $userid, $user_student, $user_nastavnik, $user_studentska, $user_siteadmin;

	$koji = my_escape($_REQUEST['koji_izvjestaj']);
	$staf = str_replace("/","_",$koji);

	$found=false;
	foreach ($registry as $r) {
		if ($r[0] == $koji) {
			if (strstr($r[3],"P") || (strstr($r[3],"S") && $user_student) || (strstr($r[3],"N") && $user_nastavnik) || (strstr($r[3],"B") && $user_studentska) || (strstr($r[3],"A") && $user_siteadmin)) {
				$found=true;
			} else {
				zamgerlog ("for_looper pristup nedozvoljenom modulu $koji", 3);
				niceerror("Pristup nedozvoljenom modulu");
				return;
			}
			break;
		}
	}
	if ($found===false) {
		zamgerlog ("for_looper nepostojeći modul $koji", 3);
		niceerror("Pristup nepostojećem modulu");
		return;
	}

	include("$koji.php");//ovdje ga ukljucujem

	$for_loop_vars = array();

	// Čitanje for_loop varijabli iz upita
	foreach ($_REQUEST as $key => $value) {
		if (substr($key,0,8) == "for_loop") {
			list ($var, $range) = explode(" ", $value);
			$for_loop_vars[$var] = $range;
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
