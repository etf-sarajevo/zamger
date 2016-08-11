<?

// COMMON/CRON - periodicno izvrsavanje skripti


// Pomoćna funkcija za parsiranje crontaba
function cron_fill_array($niz, $opseg) {
	$rezultat = $niz;
	if (strstr($opseg,","))
		foreach (explode(",",$opseg) as $element)
			$rezultat = fill_array($rezultat, $element);
			
	else if (strstr($opseg,"-")) {
		list($a,$b) = explode("-", $opseg);
		for ($i=$a; $i<=$b; $i++)
			$rezultat[] = $i;
	}
	else $rezultat[] = $opseg;
	return $rezultat;
}

	
// Nadji prvu sljedecu vrijednost u opsegu
function cron_find($localtime, $idx, $opseg) {
	if ($opseg == "*") return $localtime;
	$dozvoljene = cron_fill_array($dozvoljene, $opseg);
	$stara = $localtime[$idx];
	do {
		$localtime[$idx]++;
		$localtime = localtime(mktime($localtime[2], $localtime[1], $localtime[0], $localtime[4]+1, $localtime[3], $localtime[5]+1900));
		if ($localtime[$idx]==$stara) break;
	} while (!in_array($localtime[$idx], $dozvoljene));
	
	return $localtime;
}


function common_cron() {
	global $conf_files_path, $user_siteadmin;
	
	$force = int_param('force');
	if ($force>0) {
		if (!$user_siteadmin) {
			niceerror("Nemate dozvolu da ovo izvršite.");
			zamgerlog("forsira cron a nije admin", 3);
			zamgerlog2("forsira cron a nije admin");
			return;
		}
		$upit = "id=$force";
	} else
		$upit = "aktivan=1 AND sljedece_izvrsenje<NOW()";

	$q10 = db_query("SELECT id, path, UNIX_TIMESTAMP(zadnje_izvrsenje), godina, mjesec, dan, sat, minuta, sekunda FROM cron WHERE $upit");
	
	if (db_num_rows($q10)==0 && $force>0) {
		niceerror("Nije pronađen zadatak koji odgovara upitu.");
		return;
	}
	
	while ($r10 = db_fetch_row($q10)) {
		// Određujemo sljedeće vrijeme izvršenja
		$localtime = localtime();
		$localtime = cron_find($localtime, 0, $r10[8]);
		$localtime = cron_find($localtime, 1, $r10[7]);
		$localtime = cron_find($localtime, 2, $r10[6]);
		$localtime = cron_find($localtime, 3, $r10[5]);
		$localtime = cron_find($localtime, 4, $r10[4]);
		$localtime = cron_find($localtime, 5, $r10[3]);
		$nexttime = mktime($localtime[2], $localtime[1], $localtime[0], $localtime[4]+1, $localtime[3], $localtime[5]+1900);

		// Ažuriramo bazu
		$q20 = db_query("UPDATE cron SET zadnje_izvrsenje=NOW(), sljedece_izvrsenje=FROM_UNIXTIME($nexttime) WHERE id=$r10[0]");
		$q30 = db_query("INSERT INTO cron_rezultat SET cron=$r10[0], izlaz='(Nije završeno)', return_value=0, vrijeme=NOW()");
		$id = db_insert_id();

		// Pripremamo za izvršenje
		$exec = str_replace("---LASTTIME---", $r10[2], $r10[1]);
		$exec = "php $conf_files_path/$exec";
		$return = 0;
		$blah = array();
		
		// Izvršavamo skriptu
		$k = exec($exec, $blah, $return);
		
		// Stavljamo izlaz u bazu
		$izlaz = db_escape(iconv("UTF-8","UTF-8//IGNORE", join("\n",$blah)));
		$q40 = db_query("UPDATE cron_rezultat SET return_value=$return, izlaz='$izlaz' WHERE id=$id");
		
		if ($force>0) {
			nicemessage("Uspješno izvršena skripta.");
			$izlaz = str_replace("\\n", "\n", $izlaz);
			print "<p>Izlaz:</p>\n<pre>$izlaz</pre>\n";
		}
	}
}

?>
