<?


require("../www/lib/libvedran.php");
require("../www/lib/zamger.php");
require("../www/lib/config.php");


dbconnect();


$zadnje_vrijeme = 0;
$q10 = myquery("select vrijednost, UNIX_TIMESTAMP(NOW()) from preference where korisnik=0 and preferenca='cron-vrijeme-izvrsenja'");
if (mysql_num_rows($q10)>0) {
	$staro_vrijeme = mysql_result($q10,0,0);
	$novo_vrijeme = mysql_result($q10,0,1);
}



// Zadace za koje je definisan programski jezik
$q20 = myquery("select z.id, pj.naziv, z.predmet, ag.id, pj.ekstenzija from zadaca as z, akademska_godina as ag, programskijezik as pj where z.akademska_godina=ag.id and ag.aktuelna=1 and z.programskijezik!=0 and z.programskijezik=pj.id");
while ($r20 = mysql_fetch_row($q20)) {
	$zadaca = $r20[0];
	$programski_jezik = $r20[1];
	$predmet = $r20[2];
	$ag = $r20[3];
	$ekstenzija = $r20[4];

	if (!($programski_jezik == "C" || $programski_jezik == "C++" || $programski_jezik == "Java")) {
		print "Programski jezik $programski_jezik trenutno nije podržan.\n";
		continue;
	}
//$zadaca = 1220;
//$programski_jezik = "C++";
//$predmet = 2;
//$ag = 6;
//$ekstenzija = ".cpp";

	// Zadaci poslani u medjuvremenu
	$q30 = myquery("select distinct redni_broj, student, filename from zadatak where zadaca=$zadaca and status=1 and UNIX_TIMESTAMP(vrijeme)>$staro_vrijeme");
	while ($r30 = mysql_fetch_row($q30)) {
		$zadatak = $r30[0];
		$student = $r30[1];
		$filename = $r30[2];
//$zadatak=1;
//$student=3057;
//$filename = "1.cpp";

print "Zadaca $zadaca zadatak $zadatak student $student\n";

		// Preskačemo zadaće za koje je u međuvremenu asistent napravio izmjene
		$q40 = myquery("select status from zadatak where zadaca=$zadaca and redni_broj=$zadatak and student=$student order by id desc limit 1");
		if (mysql_result($q40,0,0) != 1) continue;

		$filepath = "$conf_files_path/zadace/$predmet-$ag/$student/$zadaca/$filename";

		// Unzip faza
		if (substr($filename, strlen($filename)-4) == ".zip") {
			print "Unzipujem...\n";
			system ("rm -fr /tmp/buildtest");
			system ("mkdir /tmp/buildtest");
			system ("unzip \"$filepath\" -d /tmp/buildtest");
			$blah = "";
			$k = exec("find /tmp/buildtest -name \\*$ekstenzija -printf \\%h\\\\n", $blah, $return);
			if ($blah==array()) {
				$q3 = myquery("insert into zadatak set status=3, izvjestaj_skripte='Nisu pronadjeni fajlovi sa ekstenzijom $ekstenzija', zadaca=$zadaca, redni_broj=$zadatak, student=$student, vrijeme=FROM_UNIXTIME($novo_vrijeme), filename='$filename'");
				print " -- Greska!\n";
				print "Nema fajlova sa ekstenzijom $ekstenzija!\n";
				continue;
			}
			$filepath = $blah[0]."/*$ekstenzija";
			$filepath = str_replace(" ", "\ ", $filepath);
		}

		// Test kompajliranja
		if (!kompajliraj()) continue;

		// Autotest, beta
		autotest();
//break;
	}
//break;
}

$q40 = myquery("update preference set vrijednost='$novo_vrijeme' where korisnik=0 and preferenca='cron-vrijeme-izvrsenja'");



function kompajliraj() {
	global $programski_jezik,$filename, $filepath;
	global $student,$zadaca,$zadatak,$novo_vrijeme;

	print "Kompajliram ($student,$zadaca,$zadatak)...\n";
	$blah = array();
	if ($programski_jezik == "C") {
		$k = exec("/usr/bin/gcc -O1 -Wall -Wuninitialized -Winit-self -pedantic -o /tmp/a.out -lm -pass-exit-codes $filepath 2>&1", $blah, $return);
	} elseif ($programski_jezik == "C++") {
		$k = exec("/usr/bin/g++ -O1 -Wall -Wuninitialized -Winit-self -pedantic -o /tmp/a.out -lm -pass-exit-codes $filepath 2>&1", $blah, $return);
	} else { // DEFAULT JEZIK: C++
		$k = exec("/usr/bin/g++ -O1 -Wall -Wuninitialized -Winit-self -pedantic -o /tmp/a.out -lm -pass-exit-codes $filepath 2>&1", $blah, $return);
	}
	$k = my_escape(join("\n",$blah));

	// Izbacujem poruku koja ništa ne znači
	$k = preg_replace("|$filepath:\d+:\d+:\s*warning:\s+no\s+newline\s+at\s+end\s+of\s+file|", "", $k);

	// Izbaci put i ime fajla iz ispisa
	$k = preg_replace("|$filepath:(\d+):(\d+):|", "red $1, kolona $2: ", $k);
	$k = preg_replace("|$filepath:(\d+):|", "red $1: ", $k);
	$k = preg_replace("|$filepath:|", "", $k);
print "K: $k\n";
	if ($return == 0) {
		// Status: 4 - "prošla test"
		$q3 = myquery("insert into zadatak set status=1, izvjestaj_skripte='$k', zadaca=$zadaca, redni_broj=$zadatak, student=$student, vrijeme=FROM_UNIXTIME($novo_vrijeme), filename='$filename'");
		return true;
	} else {
		// Status: 3 - "ne može se kompajlirati"
		$q3 = myquery("insert into zadatak set status=3, izvjestaj_skripte='$k', zadaca=$zadaca, redni_broj=$zadatak, student=$student, vrijeme=FROM_UNIXTIME($novo_vrijeme), filename='$filename'");
		print " -- Greska!\n";
		return false;
	}
}



function autotest() {
	global $programski_jezik,$ekstenzija,$filename, $filepath;
	global $student,$zadaca,$zadatak,$novo_vrijeme;

	print "Autotestiram ($student,$zadaca,$zadatak)...\n";

	$kod = file_get_contents($filepath);

	// Popravke u kodu koje su potrebne da bi testcase-ovi radili
	$q100 = myquery("select tip, specifikacija, zamijeni from autotest_replace where zadaca=$zadaca and zadatak=$zadatak");
	$fali_funkcija = false;
	$nalaz = "";
	while ($r100 = mysql_fetch_row($q100)) {
		$spec = $r100[1];
		$zamjena = $r100[2];

		// Tip zamjene: funkcija
		if ($r100[0] == "funkcija" && ($programski_jezik == "C" || $programski_jezik == "C++" || $programski_jezik == "Java")) {

			// Pretvaramo spec u validan regex
			$spec = str_replace(" ", "\\s+", $spec);
			$spec = str_replace("(", "\s*\\(\s*", $spec);
			$spec = str_replace(")", ".*?\\)", $spec);
			$spec = str_replace("TIP,", ".*?,", $spec);
			$spec = str_replace("TIP", ".*?", $spec);
			$spec = str_replace(",", ".*?,\s*", $spec);
			$spec = str_replace("FUNKCIJA", "(\\w+)", $spec);
			$results = preg_match("/$spec/", $kod, $matches);
			if ($results == 0) {
				print "Autotest: nije pronađena funkcija sa prototipom $r100[1]\n";
				$nalaz .= "Autotest: nije pronađena funkcija sa prototipom $r100[1]\n";
				$fali_funkcija = true;
			} else if ($results == 2) {
				print "Autotest: pronađene dvije funkcije sa prototipom $r100[1] - koristim prvu\n";
			}
			
			// Ako se u specifikaciji ne nalazi ključna riječ "FUNKCIJA", ovo je samo assert da funkcija postoji
			if (strstr($r100[1], "FUNKCIJA") && $zamjena != $matches[1]) { 
				$kod = "#define $zamjena $matches[1]\n" . $kod;
			}
		}
	}
	
	if ($fali_funkcija) {
		$q105 = myquery("insert into zadatak set status=1, izvjestaj_skripte='$nalaz', zadaca=$zadaca, redni_broj=$zadatak, student=$student, vrijeme=FROM_UNIXTIME($novo_vrijeme), filename='$filename'");
		return false; // Nema smisla da nastavimo dalje
	}

	// Kreiramo testcase-ove
	$q110 = myquery("select kod, rezultat, alt_rezultat, fuzzy, global_scope from autotest where zadaca=$zadaca and zadatak=$zadatak");
	if (mysql_num_rows($q110)<1) return;
	$rbr = 0;
	$orig_kod = $kod;
	$nalaz = "";
	while ($r110 = mysql_fetch_row($q110)) {
		$rbr++;
		// Kreiram kod testa
		$testni_kod = "";
		$test = $r110[0];
		if ($programski_jezik == "C") {
			$testni_kod .= "printf(\"====TEST$rbr====\");\n $test\n printf(\"====KRAJ$rbr====\");\n";
		} else if ($programski_jezik == "C++") {
			$testni_kod .= "try {\n std::cout<<\"====TEST$rbr====\";\n $test\n std::cout<<\"====KRAJ$rbr====\";\n } catch (...) {\n cout<<\"====IZUZETAK$rbr====\";\n }\n";
		}
		
		$global="\n".$r110[4]."\n";

		// Ubacujem u aplikaciju
		// U slučaju C i C++ dodaćemo naš kod na početak main-a i završiti returnom
		if ($programski_jezik == "C" || $programski_jezik == "C++") {
			$testni_kod .= "\nreturn 0;\n";
//			$kod = preg_replace("/\n([^\n]*?main\s?\(.*?\)\s?\n?\s?\n?\s?{)/s", "$global$1$testni_kod", $orig_kod);
			$kod = preg_replace("/main\s?\(/", "_main(", $orig_kod);
			$kod .= "\n$global\n"."int main() {\n$testni_kod\n}\n";
		}

		// Kompajliranje
		$source_file = "/tmp/autotest$ekstenzija";
		$exe_file = "/tmp/autotest_exec";
		$log_file = "/tmp/autotest_log";
		file_put_contents($source_file, $kod);
		$return=0;
	
		if ($programski_jezik == "C") {
			$k = exec("/usr/bin/gcc -ggdb -o $exe_file -lm -pass-exit-codes $source_file 2>&1", $blah, $return);
		} elseif ($programski_jezik == "C++") {
			$k = exec("/usr/bin/g++ -ggdb -o $exe_file -lm -pass-exit-codes $source_file 2>&1", $blah, $return);
		} else { // DEFAULT JEZIK: C++
			$k = exec("/usr/bin/g++ -ggdb -o $exe_filet -lm -pass-exit-codes $source_file 2>&1", $blah, $return);
		}

		// Došlo je do greške prilikom kompajliranja
		if ($return != 0) {
			$blah = join("\n", $blah);
			//print "---GRESKA:\n$blah\n";
			$blah .= "\n";
			if (preg_match("/error: (.*?)\\n/", $blah, $matches)) {
				print "Autotest $rbr: greška prilikom kompajliranja autotesta: $matches[1]\nKod je glasio: $test\n";
				$nalaz .= "Autotest $rbr: greška prilikom kompajliranja autotesta:\n".my_escape($matches[1])."\nKod je glasio: ".my_escape($test)."\n";
			} else {
				print "Autotest $rbr: nepoznata greška prilikom kompajliranja. Kod je glasio: $test\n";
				$nalaz .= "Autotest $rbr: nepoznata greška prilikom kompajliranja\nKod je glasio: ".my_escape($test)."\n";
			}
			continue; // sljedeći test
		}

		// Izvršenje testa
		$timeout = 10; // 10 sekundi bi trebalo biti dovoljno
		$sleep = 5; // probaj svakih 5 sekundi
		chmod($exe_file, 0755);
		$blah = "";
		exec("$exe_file &> $log_file & echo $!", $blah);
		$pid = (int)$blah[0];
		if ($pid == "") {
			print "Autotest $rbr: nisam uspio pokrenuti test. Kod je glasio: $test\n";
			$nalaz .= "Autotest $rbr: nisam uspio pokrenuti test\nKod je glasio: ".my_escape($test)."\n";
			//unlink($exe_file); // moramo saznati zašto! mada se ovo ne bi trebalo dešavati
			unlink($log_file);
			continue; // sljedeći test
		}
		$trajanje = 1;
		sleep(1); // 1 sekunda je realno da bude gotov
		while ($trajanje < $timeout) {
			$trajanje += $sleep;
			$found = false;
			$blah = "";
			exec ("ps ax | grep $pid 2>&1", $blah);
			while (list(,$row) = each($blah)) {
				$row = ltrim($row);
				$ps_stavke = explode(" ", $row);
				if ($pid == $ps_stavke[0]) $found=true;
			}
			if (!$found) break;
			sleep($sleep);
			print "čekam $trajanje\n";
		}

		if ($trajanje >= $timeout) { // Nije se završio prije $timeout
			exec("kill -9 $pid");
			print "Autotest $rbr: izgleda da je program upao u beskonačnu petlju za kod: $test\n";
			$nalaz .= "Autotest $rbr: izgleda da je program upao u beskonačnu petlju za kod: ".my_escape($test)."\n";
			unlink($exe_file);
			unlink($log_file);
			continue; // sljedeći test
		}

		$izlaz = file_get_contents($log_file);
		unlink($log_file);

		// Tražim rezultate u izlazu
		$rezultat = str_replace("\r\n", "\n", $r110[1]);
		$alt_rezultat = $r110[2];
		// Dozvoljavamo zadavanje novog reda preko \n
		$rezultat = str_replace("\\n", "\n", $rezultat);
		$alt_rezultat = str_replace("\\n", "\n", $alt_rezultat);

		$fuzzy = $r110[3];
		$nalaz_testa = ""; // Gomilamo rezultate u jedan string
		if (preg_match("/====TEST$rbr====(.*?)====KRAJ$rbr====/s", $izlaz, $matches)) {
			// fuzzy pretraga samo traži da li se rezultat nalazi bile gdje u stringu, uključujući i regex
			if ($fuzzy==1 && preg_match("/$rezultat/", $matches[1])) { 
				print "Autotest $rbr: rezultat ok!\n";

			// u suprotnom, exact match se traži
			} else if ($matches[1] == $rezultat) {
				print "Autotest $rbr: rezultat ok!\n";

			// alternativni rezultat
			} else if ($alt_rezultat != "" && $matches[1] == $alt_rezultat) {
				print "Autotest $rbr: rezultat ok (alt)!\n";

			// rezultat ne odgovara datom
			} else {
				if ($rezultat == "===IZUZETAK===") $rezultat = "izuzetak";
//				print "Autotest $rbr: nije uspio, za kod $test očekivan rezultat $rezultat, a dobio $matches[1]!\n";
//				$nalaz .= "Autotest $rbr: nije uspio, za kod ".my_escape($test)." očekivan rezultat $rezultat, a dobio $matches[1]!\n";
				$nalaz_testa .= "Očekivan rezultat $rezultat, a dobio $matches[1].\n";
			}

		// Ako je bačen izuzetak
		} else if ($programski_jezik == "C++" && preg_match("/====TEST$rbr====(.*?)====IZUZETAK$rbr====/s", $izlaz, $matches)) {
			if ($rezultat == "===IZUZETAK===") {
				print "Autotest $rbr: izuzetak ok!\n";
			} else {
//				print "Autotest $rbr: nije uspio, za kod $test očekivan rezultat $rezultat, a dobio izuzetak!\n";
//				$nalaz .= "Autotest $rbr: nije uspio, za kod ".my_escape($test)." očekivan rezultat $rezultat, a dobio izuzetak!\n";
				$nalaz_testa .= "Očekivan rezultat $rezultat, a dobio izuzetak.\n";
			}

		// Ni jedno ni drugo, izlaz nije parsabilan
		} else {
			// Pokušavamo dobiti core dump
			$pronadjen_krah = false;
			$blah = "";
			exec("ulimit -c 1000000; $exe_file &> $log_file & echo $!", $blah);
			$pid = (int)$blah[0];
			if ($pid != "") {
				sleep(1); // 1 sekunda je realno da bude gotov
				if (file_exists("core.$pid")) {
					$blah = $ispis = "";
					exec("gdb --batch -ex \"bt 100\" --core=core.$pid $exe_file", $blah);
//					for ($i=count($blah)-1; $i>=0; $i--) {
					$pocela_linija = false;
					for ($i=0; $i<count($blah); $i++) {
						if (preg_match("/\#\d\s+/", $blah[$i])) {
							$pocela_linija = true;
							$ispis = "";
						}
						if ($pocela_linija) {
							$ispis .= $blah[$i];
							if (strstr($blah[$i], $source_file)) break;
						}
					}
//					print "Autotest $rbr $source_file: program se krahirao za kod $test Detalji:\n$ispis\n";
//					$nalaz .= "Autotest $rbr: program se krahirao za kod ".my_escape($test)." Detalji:\n".my_escape($ispis)."\n";
//exit(1);
					// utf8_encode osigurava da se ilegalni karakteri (kojih nekad ima u debugger izlazu) zamijene upitnicima
					$nalaz_testa .= "Program se krahirao. Detalji:\n" . my_escape(utf8_encode($ispis))."\n";
					unlink("core.$pid");
					//unlink($exe_file);
					unlink($log_file);
					$pronadjen_krah = true;
				}
			}

			if (!$pronadjen_krah) {
//				print "Autotest $rbr: za kod $test rezultat nije pronadjen u ispisu\n";
//				$nalaz .= "Autotest $rbr: za kod ".my_escape($test)." rezultat nije pronadjen u ispisu\nMogući razlozi: program se krahira, funkcija poziva exit()\n";
				$nalaz_testa .= "Rezultat nije pronadjen u ispisu.\nMogući razlozi: program se krahira, funkcija poziva exit()\n";
			}
		}
		
		// Valgrind
		exec("valgrind --leak-check=full --log-file-exactly=/tmp/valgrind.out $exe_file", $blah);
		$valgrind = file("/tmp/valgrind.out");
		$greska=0;
		foreach ($valgrind as $valgrind_linija) {
			if (strstr($valgrind_linija, "Invalid read of size")) $greska=1;
			if (strstr($valgrind_linija, "Use of uninitialised value")) $greska=2;
			if (strstr($valgrind_linija, "are definitely lost")) $greska=3;
			if (strstr($valgrind_linija, "cannot throw exceptions and so is aborting")) 
				if (strstr($nalaz, "očekivan rezultat izuzetak")) $nalaz="";
			if (strstr($valgrind_linija, "Invalid free")) $greska=4;
			if (strstr($valgrind_linija, "Mismatched free")) $greska=5;
			if ($greska==1) {
				if (preg_match("/autotest$ekstenzija:(\d+)/", $valgrind_linija, $matches)) {
					$greska=0;
//					$nalaz .= "Autotest $rbr: pristup izvan opsega niza u liniji $matches[1] za kod ".my_escape($test)."\n";
//					print "Autotest $rbr: pristup izvan opsega niza u liniji $matches[1] za kod $test\n";
					$nalaz_testa .= "Pristup izvan opsega niza u liniji $matches[1].\n";
				}
			}
			if ($greska==2) {
				if (preg_match("/autotest$ekstenzija:(\d+)/", $valgrind_linija, $matches)) {
					$greska=0;
//					$nalaz .= "Autotest $rbr: pristup varijabli koja nije inicijalizovana u liniji $matches[1] za kod ".my_escape($test)."\n";
//					print "Autotest $rbr: pristup varijabli koja nije inicijalizovana u liniji $matches[1] za kod $test\n";
					$nalaz_testa .= "Pristup varijabli koja nije inicijalizovana u liniji $matches[1].\n";
				}
			}
			if ($greska==3) {
				if (preg_match("/autotest$ekstenzija:(\d+)/", $valgrind_linija, $matches)) {
					$greska=0;
//					$nalaz .= "Autotest $rbr: curenje memorije alocirane u liniji $matches[1] za kod ".my_escape($test)."\n";
//					print "Autotest $rbr: curenje memorije alocirane u liniji $matches[1] za kod $test\n";
					$nalaz_testa .= "Curenje memorije alocirane u liniji $matches[1].\n";
				}
			}
			if ($greska==4) {
				if (preg_match("/autotest$ekstenzija:(\d+)/", $valgrind_linija, $matches)) {
					$greska=0;
//					$nalaz .= "Autotest $rbr: dealokacija visećeg pokazivača u liniji $matches[1] za kod ".my_escape($test)."\n";
//					print "Autotest $rbr: dealokacija visećeg pokazivača u liniji $matches[1] za kod $test\n";
					$nalaz_testa .= "Dealokacija visećeg pokazivača u liniji $matches[1].\n";
				}
			}
			if ($greska==5) {
				if (preg_match("/autotest$ekstenzija:(\d+)/", $valgrind_linija, $matches)) {
					$greska=0;
//					$nalaz .= "Autotest $rbr: nije korišten odgovarajući dealokator u liniji $matches[1] za kod ".my_escape($test)."\n";
//					print "Autotest $rbr: nije korišten odgovarajući dealokator u liniji $matches[1] za kod $test\n";
					$nalaz_testa .= "Nije korišten odgovarajući dealokator (delete[] vs. delete) u liniji $matches[1].\n";
				}
			}
		}
		
		if ($nalaz_testa != "") {
			print "Autotest $rbr: \n$nalaz_testa\nTestni kod je glasio:\n$test\n\n";
			$nalaz .= "Autotest $rbr: $nalaz_testa\nTestni kod je glasio:\n".my_escape($test)."\n\n";
		}
		
		unlink($exe_file);
	}


	if ($nalaz != "") {
		$nalaz = iconv("UTF-8", "UTF-8//IGNORE", $nalaz);
		$q120 = myquery("insert into zadatak set status=1, izvjestaj_skripte='$nalaz', zadaca=$zadaca, redni_broj=$zadatak, student=$student, vrijeme=FROM_UNIXTIME($novo_vrijeme), filename='$filename'");
		return false;
	} else return true;
}

?>

Kraj.
