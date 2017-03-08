<?

// LIB/AUTOTEST - Funkcije vezane za autotestove



// autotest_detalji: Funkcija za prikaz detaljnih informacija o rezultatima testiranja zadatka
//   $test    - ID testa (što definiše zadaću i zadatak, treba biti provjeren spoofing ovog IDa)
//   $student - ID studenta
//   $nastavnik - ako je true, moguć je pristup neaktivnim i sakrivenim testovima

function autotest_detalji($test, $student, $nastavnik) {
	// Glavni upit
	$dodaj = "";
	if (!$nastavnik)
		$dodaj = "AND a.aktivan=1 AND (a.sakriven=0 OR z.rok<NOW())";
	else
		$dodaj = "AND a.aktivan=1";
	$q1000 = db_query("SELECT a.kod, a.global_scope, a.rezultat, a.alt_rezultat, a.fuzzy, ar.nalaz, ar.izlaz_programa, ar.status, ar.trajanje, a.stdin, a.partial_match, ar.testni_sistem, a.sakriven, a.zadaca, a.zadatak FROM autotest AS a, autotest_rezultat AS ar, zadaca as z WHERE a.id=$test AND ar.autotest=$test AND ar.student=$student AND a.zadaca=z.id $dodaj");
	if (db_num_rows($q1000)==0) {
		print "Nije testirano.";
		return;
	}
	
	$r1000 = db_fetch_row($q1000);
	
	// Escaping (u bazi se nalaze neescapovani podaci o kodu, ulazu i izlazu programa)
	$kod      = htmlspecialchars($r1000[0]);
	$global   = htmlspecialchars($r1000[1]);
	$ulaz     = htmlspecialchars($r1000[2]);
	$alt_ulaz = htmlspecialchars($r1000[3]);
	$izlaz    = htmlspecialchars($r1000[6]);
	$stdin    = htmlspecialchars($r1000[9]);

	// Ostali podaci su već HTML-escapovani od strane buildservice/buildservice.php skripte
	$nalaz         = $r1000[5];
	$status        = $r1000[7];
	$testni_sistem = $r1000[11];

	// Zamjenjujemo nove redove sa <br> tagom
	$novi_redovi = array( "\r\n", "\n", "\\n" );
	$ulaz     = str_replace($novi_redovi, "<br>", $ulaz);
	$alt_ulaz = str_replace($novi_redovi, "<br>", $alt_ulaz);
	$izlaz    = str_replace($novi_redovi, "<br>", $izlaz);
	$stdin    = str_replace($novi_redovi, "<br>", $stdin);
	$nalaz    = str_replace($novi_redovi, "<br>", $nalaz);

	// Na ulazu i izlazu mogu biti višestruki razmaci koje također treba prikazati
	$ulaz     = str_replace(" ", "&nbsp;", $ulaz);
	$alt_ulaz = str_replace(" ", "&nbsp;", $alt_ulaz);
	$izlaz    = str_replace(" ", "&nbsp;", $izlaz);
	$stdin    = str_replace(" ", "&nbsp;", $stdin);

	// Diff skripta traži ulaz i izlaz escapovane za JavaScript, što je malo drugačije
	$diffulaz  = str_replace($novi_redovi, "\\n", $r1000[2]);
	$diffulaz  = str_replace("'", "\'", $diffulaz);
	$diffizlaz = str_replace($novi_redovi, "\\n", $r1000[6]);
	$diffizlaz = str_replace("'", "\'", $diffizlaz);
	
	// Sljedeći parametri su int
	$vrijeme_izvrsenja = $r1000[8];
	$fuzzy_level       = $r1000[4];
	$substring         = $r1000[10];
	$sakriven          = $r1000[12];

	// Boje
	$ulaz_boja  = "#fcc";
	$izlaz_boja = "#cfc";
	
	// Određivanje rednog broja testa unutar zadaće
	$id_zadace = $r1000[13];
	$zadatak = $r1000[14];
	$q1010 = db_query("SELECT COUNT(*) FROM autotest WHERE zadaca=$id_zadace AND zadatak=$zadatak AND id<$test AND aktivan=1");
	$testbr = db_result($q1010,0,0) + 1;
	
	?>
		<script src="static/js/jsdiff/diff.js"></script>
		<script> var razlike=false;
		function dajDiff(link) {
			if (razlike) {
				document.getElementById('izlazniCode').innerHTML = "<span style=\"background: <?=$izlaz_boja?>\"><code><?=$izlaz?></code></span>";
				link.textContent = "Prikaži razlike";
				razlike=false;
				return false;
			}
			var izlaz='<?=$diffizlaz?>';
			var ulaz='<?=$diffulaz?>';
			var diff = JsDiff.diffChars(izlaz, ulaz);
			document.getElementById('izlazniCode').innerHTML = "";
			diff.forEach(function(part) {
				var color = part.added ? '<?=$ulaz_boja?>' :
				part.removed ? '<?=$izlaz_boja?>' : 'white';
				var span = document.createElement('span');
				//span.style.color = 'grey';
				span.style.backgroundColor = color;
				span.appendChild(document.createTextNode(part.value));
				document.getElementById('izlazniCode').appendChild(span);
			});
			document.getElementById('izlazniCode').innerHTML = "<pre>" + document.getElementById('izlazniCode').innerHTML + "</pre>";
			link.textContent = "Sakrij razlike";
			razlike = true;
			return false;
		}
		function testhost() {
			var me = document.getElementById('testni_sistem');
			if (me.style.display=="none"){
				me.style.display="inline";
			}
			else {
				me.style.display="none";
			}
			return false;
		}
		</script>
		<h2>Detaljnije informacije o testu - Test <?=$testbr?></h2>
		<?
	if (!empty($testni_sistem)) {
		?>
		<p><a href="#" onclick="return testhost();">Prikaži informacije o testnoj platformi</a></p>
		<div id="testni_sistem" style="display:none"><?=$testni_sistem?></div>
		<?
	}
		?>
		<h3>Kod testa:</h3>
		<pre><?=$kod?></pre>
		<?
	if (!empty($global)) {
		?>
		<p>U globalnom opsegu:</p>
		<pre><?=$global?></pre>
		<?
	}
		
	if ($status != "no_func") {
		?>
		<p><a href="<?=genuri()?>&amp;akcija=test_sa_kodom">Prikaži kod testa unutar zadaće</a></p>
		<?
	}

	if ($nastavnik)
		if ($sakriven == 1) print "<p>Test je sakriven (nije vidljiv studentima)</p>\n"; else print "<p>Test je javan (vidljiv studentima)</p>\n";

		?>
		<hr>
		<h3>Ulaz/izlaz programa</h3>
		<table border="0" cellspacing="5">
		<?

	if (strlen($stdin)>0) {
			?>
			<tr><td>Standardni ulaz:</td>
			<td><code><?=$stdin?></code></td></tr>
			<?
	}
			?>
			<tr><td>Očekivan je izlaz:</td>
			<td><span style="background: <?=$ulaz_boja?>"><code><?=$ulaz?></code></span></td></tr>
			<?

	if ($alt_ulaz != "") {
			?>
			<tr><td>Alternativni izlaz:</td>
			<td><span style="background: <?=$ulaz_boja?>"><code><?=$alt_ulaz?></code></span></td></tr>
			<?
	}

			?>
			<tr><td>Vaš program je ispisao:</td>
			<td id="izlazniCode"><span style="background: <?=$izlaz_boja?>"><code><?=$izlaz?></code></span></td></tr>
			<tr><td>&nbsp;</td><td><a href="#" onclick="return dajDiff(this);">Prikaži razlike</a></td></tr>
			<tr><td>Vrijeme izvršenja (zaokruženo):</td>
			<td><?=$vrijeme_izvrsenja?> sekundi</td></tr>
		</table>
	<?

	if ($fuzzy_level != 0) 
		  print "<p><i>Fuzzy matching level $fuzzy_level</i></p>\n";
	if ($substring != 0)
		  print "<p><i>Poklapanje podstringa</i></p>\n";

		?>
		<hr>
		<h3>Nalaz testa:</h3>
		<code><?=$nalaz?></code>
		<?

	return;
}



// autotest_sa_kodom: Prikaz zadaće sa ugrađenim kodom autotesta (popraviti!)
//   $test    - ID testa (što definiše zadaću i zadatak, treba biti provjeren spoofing ovog IDa)
//   $student - ID studenta
//   $nastavnik - ako je true, moguć je pristup neaktivnim i sakrivenim testovima
// Vraća programski kod zadatka sa integrisanim testom
// Ova funkcija trenutno ne radi sa zadaćama tipa attachment
function autotest_sa_kodom($test, $student, $nastavnik) {
	global $conf_files_path;

	// Određivanje parametara zadaće iz IDa testa...
	$q10 = db_query("SELECT at.zadaca, at.zadatak, z.predmet, z.akademska_godina, pj.naziv, pj.ekstenzija FROM zadaca as z, autotest as at, programskijezik as pj WHERE at.id=$test AND at.zadaca=z.id AND z.programskijezik=pj.id");
	if (db_num_rows($q10) != 1) {
		niceerror("Greška. Nepoznat test.");
		return;
	}

	$r10 = db_fetch_row($q10);
	$zadaca  = $r10[0];
	$zadatak = $r10[1];
	$predmet = $r10[2];
	$ag      = $r10[3];
	$programski_jezik = $r10[4];
	$ekst    = $r10[5];

	$lokacijazadaca="$conf_files_path/zadace/$predmet-$ag/$student/";
	$the_file = "$lokacijazadaca$zadaca/$zadatak$ekst";
	$kod = "";
	if (file_exists("$conf_files_path/zadace/$predmet-$ag") && file_exists($the_file)) $kod = join("",file($the_file)); 

	// Popravke u kodu koje su potrebne da bi testcase-ovi radili
	$q100 = db_query("select tip, specifikacija, zamijeni from autotest_replace where zadaca=$zadaca and zadatak=$zadatak");
	while ($r100 = db_fetch_row($q100)) {
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
				niceerror("Nije pronađena funkcija sa očekivanim prototipom $r100[1]");
				return;
			}
			
			// Ako se u specifikaciji ne nalazi ključna riječ "FUNKCIJA", ovo je samo assert da funkcija postoji
			if (strstr($r100[1], "FUNKCIJA") && $zamjena != $matches[1]) { 
				$kod = "#define $zamjena $matches[1]\n" . $kod;
			}
		}
	}

	// Uzimamo odabrani test
	$dodaj = "";
	if (!$nastavnik)
		$dodaj = "AND a.aktivan=1 AND (a.sakriven=0 OR z.rok<NOW())";
	$q110 = db_query("SELECT a.kod, a.rezultat, a.alt_rezultat, a.fuzzy, a.global_scope, a.pozicija_globala FROM autotest as a, zadaca as z WHERE a.zadaca=$zadaca AND a.zadatak=$zadatak AND a.id=$test AND a.zadaca=z.id $dodaj");
	$r110 = db_fetch_row($q110);

	$testni_kod = "";
	$test = $r110[0];
	$global="\n".$r110[4]."\n";
	$pozicija_globala=$r110[5];

	// Sadržaj maina
	if ($programski_jezik == "C") {
		$testni_kod .= "printf(\"====TEST$rbr====\");\n $test\n printf(\"====KRAJ$rbr====\");\n";
	} else if ($programski_jezik == "C++" || $programski_jezik == "C++11") {
		// Hvatanje izuzetaka
		$testni_kod .= "try {\n std::cout<<\"====TEST$rbr====\";\n $test\n std::cout<<\"====KRAJ$rbr====\";\n } catch (...) {\n cout<<\"====IZUZETAK$rbr====\";\n }\n";
	}

	// Ubacujem u kod zadaće
	// U slučaju C i C++ dodaćemo naš kod na početak main-a i završiti returnom
	if ($programski_jezik == "C" || $programski_jezik == "C++" || $programski_jezik == "C++11") {
		$testni_kod .= "\nreturn 0;\n";
		$kod = preg_replace("/main\s?\(/", "_main(", $kod);
		if ($pozicija_globala == "prije_maina")
			$kod .= "\n$global\n"."int main() {\n$testni_kod\n}\n";
		else if ($pozicija_globala == "prije_svega")
			$kod = "$global\n\n$kod\n\nint main() {\n$testni_kod\n}\n";
	}

	return $kod;
}



// autotest_admin: Korisnički interfejs za administriranje testova
//   $zadaca     - ID zadaće
//   $linkPrefix - bazni URL za sve akcije unutar ovog modula
//   $backLink   - HTML kod sa linkom za povratak na baznu stranicu
function autotest_admin($zadaca, $linkPrefix, $backLink) {

	if ($_REQUEST['subakcija'] == "promijeni_uslov") {
		$id = intval($_REQUEST['id']);
		$zadatak = intval($_REQUEST['zadatak']);
		$q300 = db_query("SELECT specifikacija FROM autotest_replace WHERE zadaca=$zadaca AND zadatak=$zadatak AND tip='funkcija' AND zamijeni='' AND id=$id");
		if (db_num_rows($q300)<1) {
			niceerror("Nepostojeći uslov");
			zamgerlog("spoofing uslovne funkcije $id", 3);
			zamgerlog2("spoofing uslovne funkcije", $id);
			return 0;
		}

		if ($_POST['subakcija'] == "promijeni_uslov" && check_csrf_token()) {
			$specifikacija = db_escape($_REQUEST['specifikacija']);
			$q310 = db_query("UPDATE autotest_replace SET specifikacija='$specifikacija' WHERE id=$id");
			nicemessage("Izmijenjen uslov za autotest");
			zamgerlog("izmijenjen uslov $id za autotest (zadaca z$zadaca)", 2);
			zamgerlog2("izmijenjen uslov za autotest", $id);
			print "<a href=\"$linkPrefix\">Nazad</a>\n";
			return 0;
		}

		print genform("POST");
		?>
		<b>Izmjena prototipa funkcije koja mora postojati u zadatku:</b><br>
		<input type="text" name="specifikacija" size="50" value="<?=db_result($q300,0,0)?>"><br>
		<input type="submit" value="Izmijeni">
		<input type="button" value="Nazad" onclick="javascript:history.go(-1);">
		</form>
		<?
		return 0;
	}

	if ($_REQUEST['subakcija'] == "obrisi_uslov") {
		$id = intval($_REQUEST['id']);
		$zadatak = intval($_REQUEST['zadatak']);
		$q300 = db_query("SELECT specifikacija FROM autotest_replace WHERE zadaca=$zadaca AND zadatak=$zadatak AND tip='funkcija' AND zamijeni='' AND id=$id");
		if (db_num_rows($q300)<1) {
			niceerror("Nepostojeći uslov");
			zamgerlog("spoofing uslovne funkcije $id", 3);
			zamgerlog2("spoofing uslovne funkcije", $id);
			return 0;
		}

		if ($_POST['subakcija'] == "obrisi_uslov" && check_csrf_token()) {
			$q320 = db_query("DELETE FROM autotest_replace WHERE id=$id");
			nicemessage("Obrisan uslov za autotest");
			zamgerlog("obrisan uslov $id za autotest (zadaca z$zadaca)", 2);
			zamgerlog2("obrisan uslov za autotest", $id);
			print "<a href=\"$linkPrefix\">Nazad</a>\n";
			return 0;
		}

		print genform("POST");
		?>
		<b>Da li ste sigurni da želite obrisati obavezni prototip &quot;<?=db_result($q300,0,0)?>&quot; ?</b><br>
		<input type="submit" value="Da">
		<input type="button" value="Ne" onclick="javascript:history.go(-1);">
		</form>
		<?
		return 0;
	}

	if ($_REQUEST['subakcija'] == "dodaj_uslov") {
		$zadatak = intval($_REQUEST['zadatak']);
		if ($_POST['subakcija'] == "dodaj_uslov" && check_csrf_token()) {
			$specifikacija = db_escape($_REQUEST['specifikacija']);
			$q330 = db_query("INSERT INTO autotest_replace SET zadaca=$zadaca, zadatak=$zadatak, tip='funkcija', zamijeni='', specifikacija='$specifikacija'");
			nicemessage("Dodan uslov za autotest");
			$id = db_insert_id();
			zamgerlog("dodan uslov $id za autotest (zadaca z$zadaca)", 2);
			zamgerlog2("dodan uslov za autotest", $id);
			print "<a href=\"$linkPrefix\">Nazad</a>\n";
			return 0;
		}

		print genform("POST");
		?>
		<b>Unesite prototip funkcije koja mora postojati u zadatku:</b><br>
		<input type="text" name="specifikacija" size="50" value=""><br>
		<input type="submit" value="Dodaj">
		<input type="button" value="Nazad" onclick="javascript:history.go(-1);">
		</form>
		<?
		return 0;
	}

	if ($_REQUEST['subakcija'] == "promijeni_at") {
		$id = intval($_REQUEST['id']);
		$zadatak = intval($_REQUEST['zadatak']);
		$q340 = db_query("SELECT kod, rezultat, alt_rezultat, fuzzy, global_scope, stdin, partial_match, aktivan, sakriven FROM autotest WHERE zadaca=$zadaca AND zadatak=$zadatak AND id=$id");
		if (db_num_rows($q340)<1) {
			niceerror("Nepostojeći autotest");
			zamgerlog("spoofing autotesta $id", 3);
			zamgerlog2("spoofing autotesta", $id);
			return 0;
		}

		if ($_POST['subakcija'] == "promijeni_at" && check_csrf_token()) {
			// Ne možemo koristiti db_escape jer htmlspecialchars zezne znakove < > (između ostalog)
			$kod = db_escape_string($_REQUEST['kod']);
			$rezultat = db_escape_string($_REQUEST['rezultat']);
			$alt_rezultat = db_escape_string($_REQUEST['alt_rezultat']);
			$fuzzy = intval($_REQUEST['fuzzy']);
			$global_scope = db_escape_string($_REQUEST['global_scope']);
			$stdin = db_escape_string($_REQUEST['stdin']);
			$stdin = str_replace("\\n", "\n", $stdin);
			$partial_match = intval($_REQUEST['partial_match']);
			$aktivan = intval($_REQUEST['aktivan']);
			$sakriven = intval($_REQUEST['sakriven']);
			if ($sakriven==0) $sakriven=1; else $sakriven=0;

			$q350 = db_query("UPDATE autotest SET kod='$kod', rezultat='$rezultat', alt_rezultat='$alt_rezultat', fuzzy=$fuzzy, global_scope='$global_scope', stdin='$stdin', partial_match='$partial_match', aktivan=$aktivan, sakriven=$sakriven WHERE id=$id");
			nicemessage("Izmijenjen autotest");
			zamgerlog("izmijenjen autotest $id (zadaca z$zadaca)", 2);
			zamgerlog2("izmijenjen autotest", $id);
			print "<a href=\"$linkPrefix\">Nazad</a>\n";
			return 0;
		}

		print genform("POST");
		// Pošto nije pozvan htmlspecialchars prilikom ubacivanja u bazu, moramo ga pozvati sada (prilikom prikaza)

		$stdin = htmlspecialchars(db_result($q340,0,5));
		$stdin = str_replace("\n", "\\n", $stdin);
		
		?>
		<b>Izmjena autotesta:</b><br><br>
		Kod testa:<br><textarea name="kod" rows="10" cols="50"><?=htmlspecialchars(db_result($q340,0,0))?></textarea><br>
		Standardni ulaz: <input type="text" name="stdin" size="50" value="<?=$stdin?>"><br><br>
		(Koristite \n da označite ENTER tipku)<br><br>
		Rezultat: <input type="text" name="rezultat" size="50" value="<?=htmlspecialchars(db_result($q340,0,1))?>"><br><br>
		Alt. rezultat: <input type="text" name="alt_rezultat" size="50" value="<?=htmlspecialchars(db_result($q340,0,2))?>"><br>
		(Ostaviti prazno ako ne želite da ponudite dvije varijante rezultata)<br><br>
		Fuzzy matching rezultata: <input type="checkbox" name="fuzzy" value="1" <? if (db_result($q340,0,3)==1) print "CHECKED"; ?>><br><br>
		Traženje podstringa u rezultatu: <input type="checkbox" name="partial_match" value="1" <? if (db_result($q340,0,6)==1) print "CHECKED"; ?>><br><br>
		Kod koji treba ubaciti u globalni opseg:<br><textarea name="global_scope" rows="10" cols="50"><?=htmlspecialchars(db_result($q340,0,4))?></textarea><br>
		Aktivan: <input type="checkbox" name="aktivan" value="1" <? if (db_result($q340,0,7)==1) print "CHECKED"; ?>><br><br>
		Javni test (vidljiv studentima): <input type="checkbox" name="sakriven" value="1" <? if (db_result($q340,0,8)==0) print "CHECKED"; ?>><br><br>
		<input type="submit" value="Izmijeni">
		<input type="button" value="Nazad" onclick="javascript:history.go(-1);">
		</form>
		<?
		return 0;
	}

	if ($_REQUEST['subakcija'] == "obrisi_at") {
		$id = intval($_REQUEST['id']);
		$zadatak = intval($_REQUEST['zadatak']);
		$q340 = db_query("SELECT kod FROM autotest WHERE zadaca=$zadaca AND zadatak=$zadatak AND id=$id");
		if (db_num_rows($q340)<1) {
			niceerror("Nepostojeći autotest");
			zamgerlog("spoofing autotesta $id", 3);
			zamgerlog2("spoofing autotesta", $id);
			return 0;
		}

		if ($_POST['subakcija'] == "obrisi_at" && check_csrf_token()) {
			$q345 = db_query("DELETE FROM autotest_rezultat WHERE autotest=$id");
			$q350 = db_query("DELETE FROM autotest WHERE id=$id");
			nicemessage("Obrisan autotest");
			zamgerlog("obrisan autotest $id (zadaca z$zadaca)", 2);
			zamgerlog2("obrisan autotest", $id);
			print "<a href=\"$linkPrefix\">Nazad</a>\n";
			return 0;
		}

		print genform("POST");
		?>
		<b>Brisanje autotesta:</b><br><br>
		Da li ste sigurni da želite obrisati autotest sa sljedećim kodom:<br>
		<textarea name="kod" rows="10" cols="50"><?=db_result($q340,0,0)?></textarea><br>
		<input type="submit" value="Da">
		<input type="button" value="Ne" onclick="javascript:history.go(-1);">
		</form>
		<?
		return 0;
	}

	if ($_REQUEST['subakcija'] == "dodaj_at") {
		$zadatak = intval($_REQUEST['zadatak']);

		if ($_POST['subakcija'] == "dodaj_at" && check_csrf_token()) {
			// Ne možemo koristiti db_escape jer htmlspecialchars zezne znakove < > (između ostalog)
			$kod = db_escape_string($_REQUEST['kod']);
			$rezultat = db_escape_string($_REQUEST['rezultat']);
			$alt_rezultat = db_escape_string($_REQUEST['alt_rezultat']);
			$fuzzy = intval($_REQUEST['fuzzy']);
			$global_scope = db_escape_string($_REQUEST['global_scope']);
			$stdin = db_escape_string($_REQUEST['stdin']);
			$stdin = str_replace("\\n", "\n", $stdin);
			$partial_match = intval($_REQUEST['partial_match']);
			$aktivan = intval($_REQUEST['aktivan']);
			$sakriven = intval($_REQUEST['sakriven']);
			if ($sakriven==0) $sakriven=1; else $sakriven=0;
			
			$q350 = db_query("INSERT INTO autotest SET zadaca=$zadaca, zadatak=$zadatak, kod='$kod', rezultat='$rezultat', alt_rezultat='$alt_rezultat', fuzzy=$fuzzy, global_scope='$global_scope', stdin='$stdin', partial_match='$partial_match', aktivan=$aktivan, sakriven=$sakriven");
			nicemessage("Dodan novi autotest");
			$id = db_insert_id();
			zamgerlog("dodan novi autotest $id (zadaca z$zadaca)", 2);
			zamgerlog2("dodan novi autotest", $id);
			print "<a href=\"$linkPrefix\">Nazad</a>\n";
			return 0;
		}

		print genform("POST");
		?>
		<b>Novi autotest:</b><br><br>
		Kod testa:<br><textarea name="kod" rows="10" cols="50"></textarea><br>
		Standardni ulaz: <input type="text" name="stdin" size="50" value=""><br><br>
		(Koristite \n da označite ENTER tipku)<br><br>
		Rezultat: <input type="text" name="rezultat" size="50" value=""><br><br>
		Alt. rezultat: <input type="text" name="alt_rezultat" size="50" value=""><br>
		(Ostaviti prazno ako ne želite da ponudite dvije varijante rezultata)<br><br>
		Fuzzy matching rezultata: <input type="checkbox" name="fuzzy" value="1"><br><br>
		Traženje podstringa u rezultatu: <input type="checkbox" name="partial_match" value="1"><br><br>
		Kod koji treba ubaciti u globalni opseg:<br><textarea name="global_scope" rows="10" cols="50"></textarea><br>
		Aktivan: <input type="checkbox" name="aktivan" value="1" CHECKED><br><br>
		Javni test (vidljiv studentima): <input type="checkbox" name="sakriven" value="1" CHECKED><br><br>
		<input type="submit" value="Dodaj">
		<input type="button" value="Nazad" onclick="javascript:history.go(-1);">
		</form>
		<?
		return 0;
	}
	
	if ($_POST['subakcija'] == "kopiraj_at" && check_csrf_token()) {
		$stara_zadaca = intval($_REQUEST['stara_zadaca']);
		$q190 = db_query("SELECT zadatak, kod, rezultat, alt_rezultat, fuzzy, global_scope, pozicija_globala, stdin, partial_match FROM autotest WHERE zadaca=$stara_zadaca");
		while ($r190 = db_fetch_row($q190)) {
			$q195 = db_query("INSERT INTO autotest SET zadaca=$zadaca, zadatak=$r190[0], kod='".db_escape_string($r190[1])."', rezultat='".db_escape_string($r190[2])."', alt_rezultat='".db_escape_string($r190[3])."', fuzzy=$r190[4], global_scope='".db_escape_string($r190[5])."', pozicija_globala='$r190[6]', stdin='".db_escape_string($r190[7])."', partial_match=$r190[8]");
		}
		nicemessage("Iskopirani testovi sa stare zadaće.");
		zamgerlog("iskopirani autotestovi sa zadace z$stara_zadaca na zadacu z$zadaca)", 2);
		zamgerlog2("iskopirani autotestovi", $stara_zadaca, $zadaca);
	}
	
	if ($_POST['subakcija'] == "import_at" && check_csrf_token()) {
		$zadatak = intval($_REQUEST['zadatak']);
		if (isset($_REQUEST['at_textarea']))
			$autotest = json_decode($_REQUEST['at_textarea'], true);
		else {
			$at_file = $_FILES['autotest_file']['tmp_name'];
			$autotest = json_decode(file_get_contents($at_file), true);
		}
		foreach($autotest['test_specifications'] as $test) {
			if (count($test['expected'])>1) $alt_rezultat=$test['expected'][1]; else $alt_rezultat="";
			if (!empty($test['global_top'])) {
				$global = $test['global_top'];
				$pozicija_globala = 'prije_svega';
			} else if (!empty($test['global_above_main'])) {
				$global = $test['global_above_main'];
				$pozicija_globala = 'prije_maina';
			} else {
				$global = "";
				$pozicija_globala = 'prije_svega';
			}
			if ($test['substring'] == 'true')
				$partial = 1;
			else
				$partial = 0;
			if ($test['regex'] == 'true')
				$fuzzy = 2;
			else if ($test['ignore_whitespace'] == 'true')
				$fuzzy = 1;
			else
				$fuzzy = 0;
			$q195 = db_query("INSERT INTO autotest SET zadaca=$zadaca, zadatak=$zadatak, kod='".db_escape_string($test['code'])."', rezultat='".db_escape_string($test['expected'][0])."', alt_rezultat='".db_escape_string($alt_rezultat)."', fuzzy=$fuzzy, global_scope='".db_escape_string($global)."', pozicija_globala='$pozicija_globala', stdin='".db_escape_string($test['running_params']['stdin'])."', partial_match=$partial");
		}
		nicemessage("Importovan .autotest fajl.");
		zamgerlog2("importovan .autotest fajl", $zadaca, $zadatak);
	}
	
	$q200 = db_query("SELECT naziv, zadataka, predmet, akademska_godina FROM zadaca WHERE id=$zadaca");
	if (db_num_rows($q200) < 1) {
		niceerror("Nepoznata zadaća.");
		return;
	}
	$naziv_zadace  = db_result($q200,0,0);
	$broj_zadataka = db_result($q200,0,1);
	$predmet       = db_result($q200,0,2);
	$ag            = db_result($q200,0,3);
	
	?>
	<h2>Autotestovi, <?=$naziv_zadace?></h2>
	<p><?=$backLink?></p>
	<?
	
	$q205 = db_query("SELECT distinct z.id, z.naziv, ag.naziv FROM zadaca as z, autotest as a, akademska_godina as ag WHERE z.predmet=$predmet AND z.akademska_godina<$ag AND z.akademska_godina=ag.id AND a.zadaca=z.id ORDER BY ag.id desc, z.id");
	if (db_num_rows($q205) > 0) {
		?>
		<h3>Kopiraj testove sa ranijih godina</h3>
		<?=genform("POST")?>
		<input type="hidden" name="subakcija" value="kopiraj_at">
		<select name="stara_zadaca">
		<?
	}
	$found = 0;
	while ($r205 = db_fetch_row($q205)) {
		print "<option value=\"$r205[0]\"";
		if ($r205[1] == $naziv_zadace && $found == 0) {
			$found=1;
			print " CHECKED";
		}
		print ">$r205[1] ($r205[2])</option>\n";
	}
	if (db_num_rows($q205) > 0) {
		print "</select> <input type=\"submit\" value=\" Kreni \"></form>\n";
	}

	for ($zadatak=1; $zadatak<=$broj_zadataka; $zadatak++) {
		?>
		<h3>Zadatak <?=$zadatak?></h3>
		
		<?=genform("POST", "\"  enctype=\"multipart/form-data")?>
		<input type="hidden" name="subakcija" value="import_at">
		<input type="hidden" name="zadatak" value="<?=$zadatak?>"><?
		if (isset($_REQUEST['prikazi_import_textarea']))
			print "<textarea name=\"at_textarea\" cols=\"60\" rows=\"20\"></textarea>";
		else 
			print "Import .autotest fajl: <input type=\"file\" name=\"autotest_file\">";
		?> <input type="submit" value="Kreni">
		</form>

		<p>Prototipovi funkcija koje moraju postojati u zadatku:
		
		<?

		$q210 = db_query("SELECT id,specifikacija FROM autotest_replace WHERE zadaca=$zadaca AND zadatak=$zadatak AND tip='funkcija' AND zamijeni=''");
		while ($r210 = db_fetch_row($q210)) {
			print "<br>\n* $r210[1] (<a href=\"$linkPrefix&subakcija=promijeni_uslov&id=$r210[0]&zadatak=$zadatak\">promijeni</a>) (<a href=\"$linkPrefix&subakcija=obrisi_uslov&id=$r210[0]&zadatak=$zadatak\">obriši</a>)";
		}
		if (db_num_rows($q210)<1)
			print "<br>\n* nema";
		print "</p>\n<p><a href=\"$linkPrefix&subakcija=dodaj_uslov&id=$r210[0]&zadatak=$zadatak\">Dodaj zahtijevanu funkciju</a></p>\n";


		?><p><b>Autotestovi:</b></p>
		<table border="1" cellspacing="0" cellpadding="5">
		<tr bgcolor="#CCCCCC"><td>R.br.</td><td>Kod</td><td>Ulaz</td><td>Rezultat</td><td>Alternativni rezultat</td><td>Fuzzy matching?</td><td>Naredbe u globalnom opsegu</td><td>Akcije</td></tr>
		<?

		$q220 = db_query("SELECT id, kod, rezultat, alt_rezultat, fuzzy, global_scope, stdin, partial_match FROM autotest WHERE zadaca=$zadaca AND zadatak=$zadatak");
		if (db_num_rows($q220)<1) {
			?>
			<tr><td colspan="8">nije definisan nijedan autotest</td></tr>
			<?
		}
		$rbr=1;
		while ($r220 = db_fetch_row($q220)) {
			$kod = htmlentities($r220[1]);
			$altrez = $r220[3];
			if ($altrez=="") $altrez="&nbsp;";
			$global_kod = htmlentities($r220[5]);
			if ($r220[4]==0) $fuzzy="NE"; else $fuzzy="L".$r220[4];
			if ($r220[7]==1)
				if ($fuzzy=="NE") $fuzzy="substr";
				else $fuzzy .= " substr";
			$stdin = htmlentities($r220[6]);

			?>
			<tr>
				<td><?=$rbr++?></td>
				<td><pre><?=$kod?></pre></td>
				<td><pre><?=$stdin?></pre></td>
				<td><?=$r220[2]?></td>
				<td><?=$altrez?></td>
				<td><?=$fuzzy?></td>
				<td><pre><?=$global_kod?></pre></td>
				<td>(<a href="<?=$linkPrefix?>&subakcija=promijeni_at&id=<?=$r220[0]?>&zadatak=<?=$zadatak?>">promijeni</a>) (<a href="<?=$linkPrefix?>&subakcija=obrisi_at&id=<?=$r220[0]?>&zadatak=<?=$zadatak?>">obriši</a>)</td>
			</tr>
			<?
		}
		print "</table>\n";
		print "</p>\n<p><a href=\"$linkPrefix&subakcija=dodaj_at&id=$r210[0]&zadatak=$zadatak\">Dodaj autotest</a></p>\n";
	}
}




// autotest_tabela: Tabelarni pregled rezultata svih testova za korisnika
//   $student - ID studenta
//   $zadaca  - ID zadace
//   $zadatak - redni broj zadatka
//   $nastavnik - ako je true, moguć je pristup neaktivnim i sakrivenim testovima
// Vraća HTML kod tabele

function autotest_tabela($student, $zadaca, $zadatak, $nastavnik) {
	// Labela za status autotesta
	$stat_autotest = array(
		"ok"              => "OK", 
		"wrong"           => "Pogrešan rezultat", 
		"error"           => "Ne može se kompajlirati", 
		"no_func"         => "Ne postoji funkcija", 
		"exec_fail"       => "Ne može se izvršiti", 
		"too_long"        => "Predugo izvršavanje", 
		"crash"           => "Testni program se krahira", 
		"find_fail"       => "Nije pronađen rezultat", 
		"oob"             => "Memorijska greška", 
		"uninit"          => "Nije inicijalizovano", 
		"memleak"         => "Curenje memorije", 
		"invalid_free"    => "Loša dealokacija", 
		"mismatched_free" => "Pogrešan dealokator"
	);


	$rezultat = "";

	$q115 = db_query("SELECT a.id, ar.status, UNIX_TIMESTAMP(ar.vrijeme), a.sakriven, UNIX_TIMESTAMP(z.rok) FROM autotest AS a, autotest_rezultat AS ar, zadaca as z WHERE a.zadaca=$zadaca AND a.zadatak=$zadatak AND a.id=ar.autotest AND ar.student=$student AND a.zadaca=z.id AND a.aktivan=1");
	if (db_num_rows($q115)>0) {
		$rezultat = <<<HTML
		<table border="1" cellspacing="0" cellpadding="2">
			<thead><tr>
				<th>Test</th>
				<th>Rezultat</th>
				<th>Vrijeme testiranja</th>
				<th>&nbsp;</th>
			</tr></thead>
HTML;
	}

	$rbr=1;
	while ($r115 = db_fetch_row($q115)) {
		$test_id      = $r115[0];
		$status       = $r115[1];
		$fino_vrijeme = date("d. m. y. H:i:s", $r115[2]);
		$sakriven     = $r115[3];

		if ($status == "ok") $ikona = "ok"; else $ikona = "not_ok";
		if ($sakriven == 1) {
			if (!$nastavnik) {
				$rok_za_slanje = $r115[4];
				if ($rok_za_slanje > time())
					continue;
			}
			$boja = "style=\"color: #777\"";
		}  else $boja = "";
		$uri = genuri();

		$rezultat .= <<<HTML
		<tr>
			<td $boja>$rbr</td>
			<td $boja><img src="static/images/16x16/$ikona.png" width="8" height="8"> $stat_autotest[$status]</td>
			<td $boja>$fino_vrijeme</td>
			<td>
				<a href="$uri&amp;test=$r115[0]&amp;akcija=test_detalji">Detalji</a>
			</td>
		</tr>
HTML;
		$rbr++;
	}
	
	if (db_num_rows($q115)>0) {
		$rezultat .= <<<HTML

		</table>
HTML;
	}

	return $rezultat;
}


// autotest_brisi_rezultate: Briše rezultate svih testova za datog studenta, zadaću i zadatak
//   $student - ID studenta
//   $zadaca  - ID zadace
//   $zadatak - redni broj zadatka

function autotest_brisi_rezultate($student, $zadaca, $zadatak) {
	$q115 = db_query("SELECT a.id FROM autotest AS a WHERE a.zadaca=$zadaca AND a.zadatak=$zadatak");
	while ($r115 = db_fetch_row($q115)) {
		$q120 = db_query("DELETE FROM autotest_rezultat WHERE autotest=$r115[0] AND student=$student");
	}
}


// autotest_status_display: Na ekranu ispisuje plutajući layer sa statusom na zadaći
//   $student - ID studenta
//   $zadaca  - ID zadace
//   $zadatak - redni broj zadatka
//   $nastavnik - ako je true, moguć je pristup neaktivnim i sakrivenim testovima

function autotest_status_display($student, $zadaca, $zadatak, $nastavnik) {
	$stat_tekst = array("Bug u programu", "Pregled u toku", "Zadaća prepisana", "Bug u programu", "Pregled u toku", "Zadaća OK");

	$q10 = db_query("select status, bodova from zadatak where student=$student and zadaca=$zadaca and redni_broj=$zadatak order by id desc limit 1");
	$status_zadace = db_result($q10,0,0);
	if ($status_zadace == 3) {
		$bgcolor = "#fcc";
		$status_duzi_tekst = "<b>Ne može se kompajlirati</b>";
		$status_ikona = "bug";
	}
	else if ($status_zadace == 2) {
		$bgcolor = "#fcc";
		$status_duzi_tekst = "<b>Zadaća prepisana</b>";
		$status_ikona = "copy";
	}
	else if ($status_zadace == 1 || $status_zadace == 4) {
		$bgcolor = "#ffc";
		$status_duzi_tekst = "<b>Pregled u toku</b>";
		$status_ikona = "view";
	}
	else if ($status_zadace == 5) {
		$bodova = db_result($q10,0,1);
		$status_duzi_tekst = "<b>Zadaća pregledana: $bodova bodova</b>";
		$status_ikona = "ok";
	}

	// Status testova
	$dodaj = "";
	if (!$nastavnik)
		$dodaj = "AND a.aktivan=1";
		
	if ($status_zadace == 1 || $status_zadace == 4 || $status_zadace == 5) {
		$q111 = db_query("SELECT COUNT(*) FROM autotest AS a, autotest_rezultat AS ar WHERE a.zadaca=$zadaca AND a.zadatak=$zadatak AND a.id=ar.autotest AND ar.student=$student $dodaj");
		$ukupno_testova = db_result($q111,0,0);
	}
	if ($status_zadace == 4 || $status_zadace == 5) {
		$q112 = db_query("SELECT COUNT(*) FROM autotest AS a, autotest_rezultat AS ar WHERE a.zadaca=$zadaca AND a.zadatak=$zadatak AND a.id=ar.autotest AND ar.student=$student AND ar.status='ok' $dodaj");
		$proslo_testova = db_result($q112,0,0);
	}

	if ($status_zadace == 1 || $status_zadace == 3) {
		if ($ukupno_testova > 0)
			$status_duzi_tekst .= "<br>Ispod su stari rezultati testova za prošlu verziju zadaće";
	}
	else if ($status_zadace == 4 || $status_zadace == 5) {
		if ($ukupno_testova > 0 && $ukupno_testova > $proslo_testova) {
			$bgcolor = "#ffc";
			$status_duzi_tekst .= ". <b>".($ukupno_testova-$proslo_testova)." od $ukupno_testova testova nije prošlo</b>";
		}
		else if ($ukupno_testova > 0) {
			$bgcolor = "#cfc";
			$status_duzi_tekst .= ". <b>Prošli svi testovi</b>";
		} else if ($status_zadace == 5) {
			$bgcolor = "#cfc";
		} else {
			$bgcolor = "#ffc";
		}
	}

	?>
	<table width="95%" style="border: 1px solid silver; background-color: <?=$bgcolor?>" cellpadding="5">
	<tr><td align="center">
		<p>Status zadaće:<br>
		<img src="static/images/16x16/<?=$status_ikona?>.png" width="16" height="16" border="0" align="center" title="<?=$stat_tekst[$status_zadace]?>" alt="<?=$stat_tekst[$status_zadace]?>"> <?=$status_duzi_tekst?></p>
	</td></tr>
	</table>
	<?
}

?>
