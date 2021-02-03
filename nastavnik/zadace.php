<?

// NASTAVNIK/ZADACE - kreiranje zadaća i masovni unos



function nastavnik_zadace() {

	global $userid, $_api_http_code, $conf_files_path, $conf_backend_url_client;
	
	require_once("lib/autotest.php");
	require_once("lib/zamgerui.php"); // mass_input
	require_once("lib/formgen.php"); // datectrl, db_form, db_dropdown, db_list
	require_once("lib/utility.php"); // procenat
	
	
	global $mass_rezultat; // za masovni unos studenata u grupe
	global $_lv_; // radi autogenerisanih formi
	
	
	
	// Parametri
	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);
	
	$course = api_call("course/$predmet/$ag");
	
	// Naziv predmeta
	$predmet_naziv = $course['courseName'];
	
	// Da li korisnik ima pravo ući u modul?
	
	if ($_api_http_code == "403") {
		zamgerlog("nastavnik/zadace privilegije (predmet pp$predmet)",3);
		zamgerlog2("nije nastavnik na predmetu", $predmet, $ag);
		biguglyerror("Nemate pravo pristupa ovoj opciji");
		return;
	}
	
	
	// Dozvoljene ekstenzije
	$dozvoljene_ekstenzije = api_call("homework/extensions")["results"];
	
	// Da li predmet posjeduje komponente za zadaće?
	$foundHomeworkCact = false;
	foreach($course['activities'] as $cact) {
		if ($cact['Activity']['id'] == 2) { // 2 = Homework
			$foundHomeworkCact = true;
			if (!isset($_REQUEST['komponenta']))
				$_REQUEST['komponenta'] = $cact['id'];
		}
	}
	
	if (!$foundHomeworkCact) {
		zamgerlog("ne postoji komponenta za zadace na predmetu pp$predmet ag$ag", 3);
		zamgerlog2("ne postoji komponenta za zadace", $predmet, $ag);
		niceerror("U postavkama predmeta nije dodata nijedna aktivnost tipa &quot;Zadaće&quot;.");
		print "<p>Da biste nastavili, podesite <a href=\"?sta=nastavnik/tip&amp;predmet=$predmet&amp;ag=$ag\">aktivnosti</a> za ovaj predmet.</p>\n";
		return;
	}
	
	$allHomeworks = api_call("homework/course/$predmet/$ag")["results"];
	
	
	?>
	
	<p>&nbsp;</p>
	
	<p><h3><?=$predmet_naziv?> - Zadaće</h3></p>
	
	<?
	
	
	// Masovni unos zadaća
	
	if ($_POST['akcija'] == "massinput" && strlen($_POST['nazad'])<1 && check_csrf_token()) {
		
		if ($_POST['fakatradi'] != 1) $ispis = 1; else $ispis = 0;
		
		// Provjera ostalih parametara
		$zadaca = intval($_REQUEST['zadaca']);
		$zadatak = intval($_REQUEST['zadatak']);
		
		$homework = api_call("homework/$zadaca");
		if ($_api_http_code != "200") {
			zamgerlog("nepostojeca zadaca $zadaca",3); // 3 = greška
			zamgerlog2("nepostojeca zadaca", $zadaca);
			niceerror("Morate najprije kreirati zadaću $zadaca");
			print "\n<p>Koristite formular &quot;Kreiranje zadaće&quot; koji se nalazi na prethodnoj stranici. Ukoliko ne vidite nijednu zadaću na spisku &quot;Postojeće zadaće&quot;, koristite dugme Refresh vašeg web preglednika.</p>\n";
			return;
		} else {
			if ($zadatak < 1 || $zadatak > $homework['nrAssignments']) {
				zamgerlog("zadaca $zadaca nema $zadatak zadataka",3);
				zamgerlog2("zadaca nema toliko zadataka", $zadaca, $zadatak);
				niceerror("Zadaća " . $homework['name'] . " nema $zadatak zadataka");
				return;
			}
		}
		
		$maxbodova = $homework['maxScore'];
	
		if ($ispis) {
			?>Akcije koje će biti urađene:<br/><br/>
			<?=genform("POST")?>
			<input type="hidden" name="fakatradi" value="1">
			<input type="hidden" name="_lv_column_zadaca" value="<?=$zadaca?>">
			<table border="0" cellspacing="1" cellpadding="2">
			<!-- FIXME: prebaciti stilove u CSS? -->
			<thead>
			<tr bgcolor="#999999">
				<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;color:white;">Prezime</font></td>
				<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;color:white;">Ime</font></td>
				<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;color:white;">Broj indeksa</font></td>
				<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;color:white;">Bodovi / Komentar</font></td>
			</tr>
			</thead>
			<tbody>
			<?
		}
	
		$greska=mass_input($ispis); // Funkcija koja parsira podatke
	
		if (count($mass_rezultat)==0) {
			print "Niste unijeli nijedan upotrebljiv podatak<br/><br/>\n";
			$greska=1;
		}
		
		// Obrada rezultata
		
		$boja1 = "#EEEEEE";
		$boja2 = "#DDDDDD";
		$boja=$boja1;
		$bojae = "#FFE3DD";
	
		foreach ($mass_rezultat['ime'] as $student=>$ime) {
			$prezime = $mass_rezultat['prezime'][$student];
			$brindexa = $mass_rezultat['brindexa'][$student];
			$bodova = $mass_rezultat['podatak1'][$student];
			$bodova = str_replace(",",".",$bodova);
	
			// Student neocijenjen (prazno mjesto za ocjenu)
			if (floatval($bodova)==0 && strpos($bodova,"0")===FALSE) {
				if ($ispis) {
					?>
					<tr bgcolor="<?=$bojae?>">
						<td><?=$prezime?></td><td><?=$ime?></td><td><?=$brindexa?></td>
						<td colspan="2">nema zadaću (nije unesen broj bodova <?=$bodova?>)</td>
					</tr>
					<?
					//$greska=1;
				}
				continue;
			}
	
			// Bodovi moraju biti manji od maximalnih borova
			$bodova = floatval($bodova);
			if ($bodova>$maxbodova) {
				if ($ispis) {
					?>
					<tr bgcolor="<?=$bojae?>">
						<td><?=$prezime?></td><td><?=$ime?></td><td><?=$brindexa?></td>
						<td colspan="2">ima <?=$bodova?> bodova što je više od maksimalnih <?=$maxbodova?></td>
					</tr>
					<?
					//$greska=1;
					continue;
				}
			}
	
			// Zaključak
			if ($ispis) {
				?>
				<tr bgcolor="<?=$boja?>">
					<td><?=$prezime?></td><td><?=$ime?></td><td><?=$brindexa?></td>
					<td colspan="2">zadaca <?=$zadaca?>, bodova <?=$bodova?></td>
				</tr>
				<?
				if ($boja==$boja1) $boja=$boja2; else $boja=$boja1;
			} else {
				// Odredjujemo zadnji filename
				$filename = "";
				$assignment = api_call("homework/$zadaca/$zadatak/student/$student");
				if ($_api_http_code == "200")
					$filename = $assignment['filename'];
				
				$status_pregledana = 5; // status 5: pregledana
				$newAssignment = array_to_object( [ "id" => 0, "Homework" => [ "id" => $zadaca ], "assignNo" => $zadatak, "student" => [ "id" => $student ], "status" => $status_pregledana, "score" => $bodova, "time" => 0, "comment" => "", "compileReport" => "", "filename" => $filename, "author" => [ "id" => $userid ] ] );
				$result = api_call("homework/$zadaca/$zadatak/student/$student", $newAssignment, "PUT");
				if ($_api_http_code == "201") {
					zamgerlog2("bodovanje zadace", $student, $zadaca, $zadatak, $bodova);
				} else {
					niceerror("Neuspješno bodovanje zadaće");
					api_report_bug($result, $newAssignment);
				}
			}
		}
	
		if ($ispis) {
			if ($greska == 0) {
				?>
				</tbody></table>
				<p>Potvrdite upis ispita i bodova ili se vratite na prethodni ekran.</p>
				<p><input type="submit" name="nazad" value=" Nazad "> <input type="submit" value=" Potvrda"></p>
				</form>
				<?
			} else {
				?>
				</tbody></table>
				<p>U unesenim podacima ima grešaka. Da li ste izabrali ispravan format ("Prezime[TAB]Ime" vs. "Prezime Ime")? Vratite se nazad kako biste ovo popravili.</p>
				<p><input type="submit" name="nazad" value=" Nazad "></p>
				</form>
				<?
			}
			return;
		} else {
			zamgerlog("masovno upisane zadaće na predmet pp$predmet, zadaća z$zadaca, zadatak $zadatak",2); // 2 = edit
			?>
			Bodovi iz zadaća su upisani.
			<script language="JavaScript">
                setTimeout(function() { location.href='?sta=nastavnik/zadace&predmet=<?=$predmet?>&ag=<?=$ag?>'; }, 1000);
			</script>
			<?
			return;
		}
	}
	
	
	
	
	// Dodavanje prateće datoteke
	
	if ($_POST['akcija']=="dodaj_datoteku") {
		$edit_zadaca = intval($_POST['zadaca']);
		$type = $_POST['type'];
		$assignNo = intval($_POST['assignNo']);
		if ($_POST['display']) $display = true; else $display = false;
		
		$tmpfile = $_FILES['dodatna_datoteka']['tmp_name'];
		$filename = strip_tags(basename($_FILES['dodatna_datoteka']['name']));
		
		$dir = "$conf_files_path/zadacetmp/$userid/";
		if (!file_exists($dir))
			mkdir ($dir,0777, true);
		
		// Ukidam HTML znakove radi potencijalnog XSSa
		$filename = str_replace("&", "", $filename);
		$filename = str_replace("\"", "", $filename);
		if (empty($filename)) {
			niceerror("Ime datoteke je prazno");
			return;
		}
		$filepath = $dir . $filename;
		if (file_exists($filepath))
			unlink($filepath);
		rename($tmpfile, $filepath);
		chmod($filepath, 0644);
		
		$id = 0;
		if ($type == "autotest") {
			// If type is autotest, file already exists and we are updating it
			$homeworkFiles = api_call("homework/$edit_zadaca/files")["results"];
			foreach ($homeworkFiles as $homeworkFile) {
				if ($homeworkFile['type'] == $type && $homeworkFile['assignNo'] == $assignNo)
					$id = $homeworkFile['id'];
			}
		}
		
		if ($id == 0) {
			$homeworkFile = array_to_object(["id" => 0, "Homework" => ["id" => $edit_zadaca], "assignNo" => $assignNo, "type" => $type, "description" => "", "display" => $display]);
			$result = api_call("homework/$edit_zadaca/files", $homeworkFile, "POST");
			if ($_api_http_code == "201") {
				$id = $result['id'];
			} else {
				niceerror("Neuspješno dodavanje prateće datoteke");
				api_report_bug($result, $homeworkFile);
				return;
			}
		}
		
		$result = api_file_upload("homework/files/$id/upload", "homeworkFileUpload", $filepath);
		if ($_api_http_code == "201") {
			nicemessage("Postavljena dodatna datoteka $filename");
		} else {
			niceerror("Neuspješno slanje prateće datoteke");
			api_report_bug($result, []);
		}
	}
	
	
	// Brisanje postavke zadaće (a ne čitave zadaće!)
	if ($_POST['akcija']=="obrisi_datoteku") {
		$edit_zadaca = intval($_POST['zadaca']);
		$homeworkFile = intval($_POST['homeworkFile']);
		$result = api_call("homework/files/$homeworkFile", [], "DELETE");
		if ($_api_http_code == "204") {
			nicemessage("Postavka zadaće obrisana");
			zamgerlog("obrisana postavka zadace z$edit_zadaca", 2);
			zamgerlog2("obrisana postavka zadace", $edit_zadaca);
			?>
			<script language="JavaScript">
                setTimeout(function() { location.href='?sta=nastavnik/zadace&predmet=<?=$predmet?>&ag=<?=$ag?>&_lv_nav_id=<?=$edit_zadaca?>'; }, 1000);
			</script>
			<?
		} else {
			niceerror("Neuspješno brisanje prateće datoteke");
			api_report_bug($result, []);
		}
		return;
	}
	
	
	// Akcija za kreiranje nove, promjenu postojeće ili brisanje zadaće
	
	if ($_POST['akcija']=="edit" && $_POST['potvrdabrisanja'] != " Nazad " && check_csrf_token()) {
		$edit_zadaca = intval($_POST['zadaca']);
		
		// Prava pristupa
		if ($edit_zadaca>0) {
			$homework = api_call("homework/$edit_zadaca", ["stats" => true]);
			if ($_api_http_code != "200") {
				niceerror("Nepostojeća zadaća sa IDom $edit_zadaca");
				zamgerlog("promjena nepostojece zadace $edit_zadaca", 3);
				zamgerlog2("nepostojeca zadaca", $edit_zadaca);
				api_report_bug($homework, []);
				return 0;
			}
		}
	
		// Brisanje zadaće
		if ($_POST['brisanje'] == " Obriši ") {
			if ($_POST['potvrdabrisanja']==" Briši ") {
				$result = api_call("homework/$edit_zadaca", [], "DELETE");
				
				if ($_api_http_code == "204") {
					zamgerlog("obrisana zadaca $edit_zadaca sa predmeta pp$predmet", 4);
					zamgerlog2("obrisana zadaca", $edit_zadaca);
					nicemessage ("Zadaća uspješno obrisana");
					?>
					<script language="JavaScript">
                    setTimeout(function() { location.href='?sta=nastavnik/zadace&predmet=<?=$predmet?>&ag=<?=$ag?>'; }, 1000);
					</script>
					<?
				} else {
					niceerror("Neuspješno brisanje zadaće");
					api_report_bug($result, []);
				}
				return;
				
			} else {
				print genform("POST");
				
				?>
				Brisanjem zadaće obrisaćete i sve do sada unesene ocjene i poslane zadatke! Da li ste sigurni da to želite?<br>
				U pitanju je <b><?=$homework['submissions']?></b> jedinstvenih slogova u bazi!<br><br>
				<?
				
				if ($homework['autotests'] > 0) {
					?>
					Također ćete obrisati i <b><?=$homework['autotests']?></b> testova.<br><br>
					<?
				}
				
				?>
				<input type="submit" name="potvrdabrisanja" value=" Briši ">
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input type="submit" name="potvrdabrisanja" value=" Nazad ">
				<?
				return;
			}
		}
	
	
		// Kreiranje ili izmjena zadaće
	
		$naziv = trim($_POST['naziv']);
		$zadataka = intval($_POST['zadataka']);
		$bodova = floatval(str_replace(",",".",$_POST['bodova']));
		$dan = nuliraj_broj(intval($_POST['day']));
		$mjesec = nuliraj_broj(intval($_POST['month']));
		$godina = intval($_POST['year']);
		$sat = nuliraj_broj(intval($_POST['sat']));
		$minuta = nuliraj_broj(intval($_POST['minuta']));
		$sekunda = nuliraj_broj(intval($_POST['sekunda']));
		if ($_POST['aktivna']) $aktivna=true; else $aktivna=false;
		if ($_POST['attachment']) $attachment=true; else $attachment=false;
		$programskijezik = intval($_POST['_lv_column_programskijezik']);
		if ($_POST['automatsko_testiranje']) $automatsko_testiranje=true; else $automatsko_testiranje=false;
		if ($_POST['readonly']) $readonly=true; else $readonly=false;
	
		if (intval($_POST['attachment']) == 1 && isset($_POST['dozvoljene_eks'])) {
			$ekstenzije = array_unique($_POST['dozvoljene_eks']);
			$dozvoljene_ekstenzije_selected = implode(',',$ekstenzije);
		} else {
			$dozvoljene_ekstenzije_selected = "";
		}
	
		// Provjera ispravnosti
		if (!preg_match("/\w/",$naziv)) {
			niceerror("Naziv zadaće nije dobar.");
			return 0;
		}
		if ($zadataka<=0 || $bodova<0 || $zadataka>100 || $bodova>100) {
			niceerror("Broj zadataka ili broj bodova nije dobar");
			return 0;
		}
		if (!checkdate($mjesec,$dan,$godina)) {
			niceerror("Odabrani datum je nemoguć");
			return 0;
		}
		if ($sat<0 || $sat>24 || $minuta<0 || $minuta>60 || $sekunda<0 || $sekunda>60) {
			niceerror("Vrijeme nije dobro");
			return 0;
		}
		
		// Parametar "komponenta" bi trebao sadržavati odredišnu komponentu za ovu zadaću
		$komponenta_za_zadace = int_param('komponenta');
		$db_date = "$godina-$mjesec-$dan $sat:$minuta:$sekunda";
		$newHomework = array_to_object( [ "id" => 0, "name" => $naziv, "CourseUnit" => [ "id" => $predmet ], "AcademicYear" => [ "id" => $ag ], "nrAssignments" => $zadataka, "maxScore" => $bodova, "CourseActivity" => [ "id" => $komponenta_za_zadace ], "deadline" => $db_date, "active" => $aktivna, "ProgrammingLanguage" => [ "id" => $programskijezik ], "automatedTesting" => $automatsko_testiranje, "attachment" => $attachment, "allowedExtensions" => $dozvoljene_ekstenzije_selected, "text" => $postavka_file, "readonly" => $readonly ] );
		
		// Kreiranje nove
		if ($edit_zadaca==0) {
			// Test if homework with given name already exists
			$found = false;
			foreach ($allHomeworks as $_hw) {
				if ($_hw['name'] == $naziv)
					$found = true;
			}
			if ($found) {
				niceerror("Zadaća pod imenom '$naziv' već postoji! Izaberite neko drugo ime.");
				zamgerlog("zadaca sa nazivom '$naziv' vec postoji", 3);
				return 0;
			}
			
			$result = api_call("homework/course/$predmet/$ag", $newHomework, "POST");
			if ($_api_http_code != "201") {
				niceerror("Dodavanje zadaće nije uspjelo ($_api_http_code): " . $result['message']);
				zamgerlog("dodavanje zadace nije uspjelo pp$predmet, naziv '$naziv'",3);
				zamgerlog2("dodavanje zadace nije uspjelo", $predmet, $zadataka, $bodova, $naziv);
			} else {
				nicemessage("Kreirana nova zadaća '$naziv'");
				zamgerlog("kreirana nova zadaca z$edit_zadaca", 2);
				zamgerlog2("kreirana nova zadaca", $edit_zadaca);
				$allHomeworks = api_call("homework/course/$predmet/$ag")["results"];
			}
	
		// Izmjena postojece zadace
		} else {
			$result = api_call("homework/$edit_zadaca", $newHomework, "PUT");
			if ($_api_http_code != "201") {
				niceerror("Izmjena zadaće nije uspjela");
				api_report_bug($result, $newHomework);
			} else {
				nicemessage("Ažurirana zadaća '$naziv'");
				zamgerlog("azurirana zadaca z$edit_zadaca", 2);
				zamgerlog2("azurirana zadaca", $edit_zadaca);
				$allHomeworks = api_call("homework/course/$predmet/$ag")["results"];
			}
		}
	}
	
	
	if ($_GET['akcija'] == "resetStatus") {
		$zadaca = intval($_GET['zadaca']);
		$zadatak = intval($_GET['zadatak']);
		$status = 1;
		
		$result = api_call("homework/$zadaca/$zadatak/resetStatus", ["status" => $status], "PUT");
		if ($_api_http_code == "201") {
			nicemessage("Zadaća je označena za ponovno testiranje svim studentima");
			print "Ponovno testiranje će se desiti u terminu kada je to podešeno platformom za automatsko testiranje zadaća (sutra ujutro).";
			zamgerlog("resetovan status zadace z$zadaca zadatak $zadatak", 2);
			zamgerlog2("resetovan status zadace", $zadaca, $zadatak);
			?>
			<script language="JavaScript">
                setTimeout(function() { location.href='?sta=nastavnik/zadace&predmet=<?=$predmet?>&ag=<?=$ag?>&_lv_nav_id=<?=$zadaca?>'; }, 5000);
			</script>
			<?
			
		} else {
			niceerror("Neuspješno resetovanje statusa");
			api_report_bug($result, ["status" => $status] );
		}
		return;
	}
	
	
	if (param('akcija') == "bodujSve") {
		$zadaca = int_param('zadaca');
		$zadatak = int_param('zadatak');
		
		if ((param('subakcija') == "fakatBoduj" || param('subakcija') == "dryRun") && check_csrf_token()) {
			if (param('subakcija') == "dryRun") $dryRun = true; else $dryRun = false;
			$maxBodova = floatval($_REQUEST['maxBodova']);
			$autoGrade = api_call("homework/$zadaca/$zadatak/autoGrade", [ "maxScore" => $maxBodova, "dryRun" => $dryRun], "PUT");
			if ($_api_http_code == "201") {
				if (param('subakcija') == "dryRun") {
					foreach($autoGrade['results'] as $student => $result) {
						print "$result<br>";
					}
					print "<p>&nbsp;</p>";
					?>
					<?=genform("POST");?>
					<input type="hidden" name="subakcija" value="fakatBoduj">
					<input type="submit" value=" Potvrdi bodove ">
					</form>
					<?php
				} else {
					foreach($autoGrade['results'] as $student => $result) {
						zamgerlog2("automatsko bodovanje zadace", $student, $zadaca, $zadatak);
						print "$result<br>";
					}
					nicemessage("Automatsko bodovanje zadaće uspješno završeno");
				}
			} else {
				niceerror("Neuspješno bodovanje zadaće");
				api_report_bug($result, $newAssignment);
			}
			
			return;
		}
		
		$homework = api_call("homework/$zadaca");
		$defaultScore = round($homework['maxScore'] / $homework['nrAssignments'], 2);
		
		?>
		<h3>Automatsko bodovanje zadaće proporcionalno rezultatima testiranja</h3>
		<p>Zadaća <b><?=$homework['name']?></b>, Zadatak <b><?=$zadatak?></b></p>
		<?=genform("POST")?>
		<input type="hidden" name="subakcija" value="dryRun">
		Maksimalan broj bodova: <input type="text" name="maxBodova" value="<?=$defaultScore?>">
		<input type="submit" value=" Kreni ">
		</form>
		<?
		
		return;
	}
	
	
	// Spisak postojećih zadaća
	$izabrana = intval($_REQUEST['_lv_nav_id']);
	if ($izabrana==0) $izabrana=intval($edit_zadaca);
	
	$_lv_["where:predmet"] = $predmet;
	$_lv_["where:akademska_godina"] = $ag;
	$izabrana_komponenta = int_param('komponenta');
	
	
	$homeworkActivities = [];
	foreach ($course['activities'] as $cact) {
		if ($cact['Activity']['id'] == 2) { // 2 = Homework
			$homeworkActivities[] = $cact;
			$_lv_["where:komponenta"] = $cact['id']; // određena na početku fajla
			
			// FIXME Hack kojim ćemo postići da link "Unesi novu" ispravno prosljeđuje komponentu
			$_REQUEST['komponenta'] = $cact['id'];
			
			?>
			<b><?=$cact['name']?>:</b><br/>
			<ul>
			<?
			foreach ($allHomeworks as $_hw) {
				if ($_hw['CourseActivity']['id'] == $cact['id']) {
					if ($_hw['id'] == $izabrana) {
						?>
						<li><?=$_hw['name']?></li>
						<?
					} else {
					?>
					<li><a href="?sta=nastavnik/zadace&predmet=<?=$predmet?>&ag=<?=$ag?>&komponenta=<?=$cact['id']?>&_lv_nav_id=<?=$_hw['id']?>"><?=$_hw['name']?></a></li>
					<?
					}
				}
			}
			?>
				</ul>
			<?
		}
	}
	
	//if ($izabrana_komponenta!=0)
	//	$_REQUEST['komponenta'] = $izabrana_komponenta; // Potrebno nam je radi genform za kreiranje zadaće
	
	
	// Kreiranje nove zadace ili izmjena postojeće
	
	if ($izabrana==0) {
		?><p><hr/></p>
		<p><b>Kreiranje zadaće</b><br/>
		<?
		$znaziv=$zaktivna=$zattachment=$zjezik=$zreadonly="";
		$zzadataka=0; $zbodova=0;
		$tmpvrijeme=time();
		$zkomponenta = $izabrana_komponenta;
	} else {
		$homework = api_call("homework/$izabrana");
		
		?><p><hr/></p>
		<p><b>Izmjena zadaće</b></p>
		<?
	
		$znaziv = $homework['name'];
		$zzadataka = $homework['nrAssignments'];
		$zbodova = $homework['maxScore'];
		$tmpvrijeme = db_timestamp($homework['deadline']);
		if ($homework['active']) $zaktivna="CHECKED"; else $zaktivna="";
		$zjezik = $homework['ProgrammingLanguage']['id'];
		if ($homework['attachment']) $zattachment="CHECKED"; else $zattachment="";
		$dozvoljene_ekstenzije_selected = $homework['allowedExtensions'];
		$automatsko_testiranje = $homework['automatedTesting'];
		if ($homework['readonly']) $zreadonly="CHECKED"; else $zreadonly="";
		$zkomponenta = $homework['CourseActivity']['id'];
	}
	
	$zdan = date('d',$tmpvrijeme);
	$zmjesec = date('m',$tmpvrijeme);
	$zgodina = date('Y',$tmpvrijeme);
	$zsat = date('H',$tmpvrijeme);
	$zminuta = date('i',$tmpvrijeme);
	$zsekunda = date('s',$tmpvrijeme);
	
	
	
	// JavaScript za provjeru validnosti forme
	?>
	<script language="JavaScript">
	function IsNumeric(sText) {
	   var ValidChars = "0123456789.";
	   var IsNumber=true;
	   var Char;
	
	 
	   for (i = 0; i < sText.length && IsNumber == true; i++)
		  {
		  Char = sText.charAt(i);
		  if (ValidChars.indexOf(Char) == -1)
			 {
			 IsNumber = false;
			 }
		  }
	   return IsNumber;0
	   
	}
	
	function provjera() {
	//	var forma=document.getElementById("kreiranje_zadace");
		var naziv=document.getElementById("naziv");
		if (parseInt(naziv.value.length)<1) {
			alert("Niste unijeli naziv");
			naziv.style.border=1;
			naziv.style.backgroundColor="#FF9999";
			naziv.focus();
			return false;
		}
		var zadataka=document.getElementById("zadataka");
		if (!IsNumeric(zadataka.value)) {
			alert("Neispravan broj zadataka!");
			zadataka.style.border=1;
			zadataka.style.backgroundColor="#FF9999";
			zadataka.focus();
			return false;
		}
		if (parseInt(zadataka.value)<=0) {
			alert("Broj zadataka u zadaći mora biti veći od nule, npr. 1");
			zadataka.style.border=1;
			zadataka.style.backgroundColor="#FF9999";
			zadataka.focus();
			return false;
		}
		var bodova=document.getElementById("bodova");
		if (!IsNumeric(bodova.value)) {
			alert("Neispravan broj bodova!");
			bodova.style.border=1;
			bodova.style.backgroundColor="#FF9999";
			bodova.focus();
			return false;
		}
		if (parseFloat(bodova.value)<0) {
			alert("Broj bodova koje nosi zadaća mora biti veći ili jednak nuli, npr. 2 boda");
			bodova.style.border=1;
			bodova.style.backgroundColor="#FF9999";
			bodova.focus();
			return false;
		}
		
		return true;
	}
	
	function onemoguci_ekstenzije(chk) {
		var attachment = document.getElementById("attachment");
		var dozvoljene_ekstenzije = document.getElementById("dozvoljene_ekstenzije");
		var jezik = document.getElementById("_lv_column_programskijezik");
	
		if (attachment.checked) {
			dozvoljene_ekstenzije.style.display = '';
		} else {
			dozvoljene_ekstenzije.style.display = 'none';
			for (i = 0; i < chk.length; i++) chk[i].checked = false;
		}
	}
	</script>
	<?
	
	
	
	// Forma za kreiranje zadaće
	
	unset($_REQUEST['aktivna']);
	unset($_REQUEST['attachment']);
	unset($_REQUEST['automatsko_testiranje']);
	
	print genform("POST", "kreiranje_zadace\" enctype=\"multipart/form-data\" onsubmit=\"return provjera();");
	
	?>
	<input type="hidden" name="akcija" value="edit">
	<input type="hidden" name="zadaca" value="<?=$izabrana?>">
	Naziv: <input type="text" name="naziv" id="naziv" size="30" value="<?=$znaziv?>"><br><br>
	
	<?
	if (count($homeworkActivities) == 1) {
		?>
		<input type="hidden" name="komponenta" value="<?=$homeworkActivities[0]['id']?>">
		<?
	} else {
		?>
		Aktivnost: <select name="komponenta"><?
		foreach($homeworkActivities as $cact) {
			?><option value="<?=$cact['id']?>" <? if ($cact['id'] == $zkomponenta) print "SELECTED"; ?>><?=$cact['name']?></option><?
		}
		?></select><br><br>
		<?
	}
	?>
	
	Broj zadataka: <input type="text" name="zadataka" id="zadataka" size="4" value="<?=$zzadataka?>">
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Max. broj bodova: <input type="text" name="bodova" id="bodova" size="3" value="<?=$zbodova?>"><br><br>
	
	Rok za slanje: <?=datectrl($zdan,$zmjesec,$zgodina)?>
	&nbsp;&nbsp; <input type="text" name="sat" size="1" value="<?=$zsat?>"> <b>:</b> <input type="text" name="minuta" size="1" value="<?=$zminuta?>"> <b>:</b> <input type="text" name="sekunda" size="1" value="<?=$zsekunda?>"> <br><br>
	
	<input type="checkbox" name="aktivna" <?=$zaktivna?>> Aktivna
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<input type="checkbox" name="readonly" <?=$zreadonly?>> Read-only
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<input type="checkbox" value="1" id="attachment" onclick="onemoguci_ekstenzije(this.form.dozvoljene_eks)" name="attachment" <?=$zattachment?>> Slanje zadatka u formi attachmenta<br><br>
	
	<span id="dozvoljene_ekstenzije" style="display:none" title="Oznacite željene ekstenzije">
	Dozvoljene ekstenzije (Napomena: Ukoliko ne odaberete nijednu ekstenziju sve ekstenzije postaju dozvoljene):
	<? $dozvoljene_ekstenzije_selected=explode(',',$dozvoljene_ekstenzije_selected);
	foreach($dozvoljene_ekstenzije as $doz_ext) { ?>
	<input type="checkbox" name="dozvoljene_eks[]" <? if(in_array($doz_ext,$dozvoljene_ekstenzije_selected)) echo 'checked="checked"'?> value="<? echo $doz_ext; ?>" /> <? echo $doz_ext; ?>
	<? } ?>
	<br><br>
	</span>
	
	Programski jezik: <?=db_dropdown("programskijezik", $zjezik)?><br><br>
	
	<?
	
	if ($izabrana != 0) {
		// We will need homework files for autotest editor buttons and later for listing other files
		$homeworkFiles = api_call("homework/$izabrana/files")["results"];
	}
	
	if ($zjezik != 0) {// Ako nije definisan programski jezik, nećemo ni nuditi automatsko testiranje... ?
		if ($automatsko_testiranje) $add_testiranje = "CHECKED"; else $add_testiranje = "";
		?>
		<input type="checkbox" name="automatsko_testiranje" <?=$add_testiranje?>> Automatsko testiranje<br>
		<?php
		
		// Show buttons to define tests
		if ($izabrana != 0 && $automatsko_testiranje) {
			$wsurl = "dummy,neradi";
			global $conf_site_url, $conf_backend_url_client, $conf_keycloak;
			ajax_box();
			?>
			<script src="lib/autotest-genv2/scripts/helpers.js"></script>
			<script>
				function updateAutotestFile(fileID, assignNo) {
				    const data = window.localStorage.getItem('.autotest-content');
				    
				    // Requires fairly recent browser, doesn't work in IE
                    const formData = new FormData();
                    formData.append("sta", "nastavnik/zadace");
                    formData.append("predmet", "<?=$predmet?>");
                    formData.append("ag", "<?=$ag?>");
                    formData.append("zadaca", "<?=$izabrana?>");
                    formData.append("akcija", "dodaj_datoteku");
                    formData.append("type", "autotest");
                    formData.append("assignNo", assignNo);
                    formData.append("display", "0");

                    formData.append("dodatna_datoteka", new File([new Blob([data])], "autotest"));
                    var url='<?=$conf_site_url?>/index.php';
                    fetch(url, {
						method: 'POST',
						body: formData,
					}).then((response) => {
						console.log(response)
					});
				}
				
				function doOpenAutotestGenerator(fileID, assignNo) {
					ajax_api_start( "homework/files/"+fileID, "GET", [], (obj) => {
						window.localStorage.setItem('.autotest-content', JSON.stringify(obj));
						const newWindow = Helpers.openGenerator('lib/autotest-genv2/html/index.html','<?=$wsurl?>', true);
						
						newWindow.addEventListener('load', () => {
							const button = newWindow.document.getElementById('export-button');
							button.addEventListener("click", () => {
								updateAutotestFile(fileID, assignNo);
							});
						}, false);
					}, (json, status, url) => {
						alert("Greška prilikom preuzimanja autotest fajla sa servera: " + status);
						console.log(json);
					})
				}
			</script>
			<br><b>Opcije automatskog testiranja:</b><br>
			<ul>
			<?
			
			for ($i = 1; $i <= $zzadataka; $i++) {
				?>
				<li>Zadatak <?=$i?>:
				<?
				$fileID = false;
				foreach($homeworkFiles as $hwf) {
					if ($hwf['type'] == "autotest" && $hwf['assignNo'] == $i) $fileID = $hwf['id'];
				}
				if (!$fileID) {
					niceerror("Nije kreiran default autotest fajl. Kontaktirajte administratora");
				} else {
					?>
					<button onclick="doOpenAutotestGenerator(<?=$fileID?>, <?=$i?>) ;" type="button" class="btn btn-info btn-sm mr-2 waves-effect">Definiši testove</button>&nbsp;
					<button onclick="location.href='?sta=nastavnik/zadace&predmet=<?=$predmet?>&ag=<?=$ag?>&zadaca=<?=$izabrana?>&zadatak=<?=$i?>&akcija=resetStatus';" type="button" class="btn btn-info btn-sm mr-2 waves-effect">Zatraži retestiranje</button>&nbsp;
					<button onclick="location.href='?sta=nastavnik/zadace&predmet=<?=$predmet?>&ag=<?=$ag?>&zadaca=<?=$izabrana?>&zadatak=<?=$i?>&akcija=bodujSve';" type="button" class="btn btn-info btn-sm mr-2 waves-effect">Automatsko bodovanje</button>&nbsp;
					</li>
					<?
				}
			}
			
			?>
			</ul>
			<br><br><br>
			<?
		}
	}
	
	
	// Prateće datoteke
	
	?>
	
	<input type="submit" value=" Pošalji "> <input type="reset" value=" Poništi ">
	<?
	if ($izabrana>0) {
		?><input type="submit" name="brisanje" value=" Obriši "><?
	}
	echo "<script> onemoguci_ekstenzije('');</script>";
	?>
	</form>

	<br><br>
	
	Prateće datoteke:<br>
	<?
	
	if ($izabrana != 0) {
		foreach($homeworkFiles as $homeworkFile) {
			if ($homeworkFile['type'] == "autotest") continue; // Don't show autotest files
			
			?>
			<form action="index.php" method="POST">
				<input type="hidden" name="sta" value="nastavnik/zadace">
				<input type="hidden" name="predmet" value="<?=$predmet?>">
				<input type="hidden" name="ag" value="<?=$ag?>">
				<input type="hidden" name="zadaca" value="<?=$izabrana?>">
				<input type="hidden" name="akcija" value="obrisi_datoteku">
				<a href="?sta=common/attachment&amp;zadaca=<?=$izabrana?>&amp;file=<?=$homeworkFile['id']?>&amp;tip=dodatne"><img src="static/images/16x16/download.png" width="16" height="16" border="0"> <?=$homeworkFile['filename']?></a>
			<?
			if ($homeworkFile['type'] == "postavka") print "- postavka zadaće";
			if ($homeworkFile['type'] == "autotest") print "- autotest";
			if ($homeworkFile['assignNo'] != 0)
				print " (" . $homeworkFile['assignNo'] . ". zadatak)";
			?>
				<input type="hidden" name="homeworkFile" value="<?=$homeworkFile['id']?>"> <input type="submit" name="dugmeobrisi" value="Obriši">
			</form>
			<br>
			<?
		}
		?>
		<ul>
		Dodajte prateću datoteku:
			<form action="index.php" method="POST" enctype="multipart/form-data">
			<input type="hidden" name="sta" value="nastavnik/zadace">
			<input type="hidden" name="predmet" value="<?=$predmet?>">
			<input type="hidden" name="ag" value="<?=$ag?>">
			<input type="hidden" name="zadaca" value="<?=$izabrana?>">
			<input type="hidden" name="akcija" value="dodaj_datoteku">
			<input type="file" name="dodatna_datoteka" size="45"><br>
		Tip datoteke: <select name="type"><option value="postavka">Postavka zadaće</option><option value="autotest">Autotest</option></select>
		Uz zadatak: <select name="assignNo"><option value="0">Svi zadaci (čitava zadaća)</option>
			<?
			for ($i=1; $i<=$homework['nrAssignments']; $i++)
				print "<option value=\"$i\">$i. zadatak</option>\n";
			?>
		</select><br>
			Prikaži studentima: <input type="checkbox" name="display" value="1"><br>
		<input type="submit" value="Dodaj"></form>
		</ul>
		<?
	}
	?>
	<br><br>
	<?
	
	
	/*
	$_lv_["label:programskijezik"] = "Programski jezik";
	$_lv_["label:zadataka"] = "Broj zadataka";
	$_lv_["label:bodova"] = "Max. broj bodova";
	$_lv_["label:attachment"] = "Slanje zadatka u formi attachmenta";
	$_lv_["label:rok"] = "Rok za slanje";
	$_lv_["hidden:vrijemeobjave"] = 1;
	print db_form("zadaca");*/
	
	
	
	// Masovni unos konačnih ocjena
	
	$preferences = api_call("person/preferences")["results"];
	$format = intval($_POST['format']);
	if (!$_POST['format']) {
		if (array_key_exists('mass-input-format', $preferences))
			$format = $preferences['mass-input-format'];
		else //default vrijednost
			$format=0;
	}
	
	$separator = intval($_POST['separator']);
	if (!$_POST['separator']) {
		if (array_key_exists('mass-input-separator', $preferences))
			$separator = $preferences['mass-input-separator'];
		else //default vrijednost
			$separator=0;
	}
	
	if (count($allHomeworks) > 0) {
	
	?><p><hr/></p>
	<p><b>Masovni unos zadaća</b><br/>
	<?
	
	print genform("POST");
	if (strlen($_POST['nazad'])>1) $izabrana = $_POST['_lv_column_zadaca']; else $izabrana = -1;
	?><input type="hidden" name="fakatradi" value="0">
	<input type="hidden" name="akcija" value="massinput">
	<input type="hidden" name="nazad" value="">
	<input type="hidden" name="brpodataka" value="1">
	<input type="hidden" name="duplikati" value="0">
	
	Izaberite zadaću: <select name="zadaca">
	<?
	$maxAssignments = 0;
	foreach($allHomeworks as $_hw) {
		if ($_hw['id'] == $izabrana) $sel = " SELECTED"; else $sel = "";
		?>
		<option value="<?=$_hw['id']?>"><?=$_hw['name']?></option>
		<?
		if ($_hw['nrAssignments'] > $maxAssignments) $maxAssignments = $_hw['nrAssignments'];
	}
	?>
		</select>
	Izaberite zadatak: <select name="zadatak"><?
	
	for ($i=1; $i<=$maxAssignments; $i++) {
		?><option value="<?=$i?>"><?=$i?></option><?
	}
	?>
	</select><br/><br/>
	
	<textarea name="massinput" cols="50" rows="10"><?
	if (strlen($_POST['nazad'])>1) print $_POST['massinput'];
	?></textarea><br/>
	<br/>Format imena i prezimena: <select name="format" class="default">
	<option value="0" <? if($format==0) print "SELECTED";?>>Prezime[TAB]Ime</option>
	<option value="1" <? if($format==1) print "SELECTED";?>>Ime[TAB]Prezime</option>
	<option value="2" <? if($format==2) print "SELECTED";?>>Prezime Ime</option>
	<option value="3" <? if($format==3) print "SELECTED";?>>Ime Prezime</option>&nbsp;
		<option value="4" <? if($format==4) print "SELECTED";?>>Broj indeksa</option></select>&nbsp;
	Separator: <select name="separator" class="default">
	<option value="0" <? if($separator==0) print "SELECTED";?>>Tab</option>
	<option value="1" <? if($separator==1) print "SELECTED";?>>Zarez</option></select><br/><br/>
	<input type="submit" value="  Dodaj  ">
	</form></p>
	<?
	
	
	} else {
	
		?><p><hr/></p>
		<p><b>Masovni unos zadaća NIJE MOGUĆ</b><br/>
		Najprije kreirajte zadaću koristeći formular iznad</p>
		<?
	}


}

?>
