<?

// STUDENT/ZADACA - slanje zadace za studente


function student_zadaca() {

	global $userid, $conf_code_viewer, $_api_http_code;
	
	require_once("lib/autotest.php");
	require_once("lib/utility.php"); // linkuj_urlove, nicesize, ends_with, rm_minus_r, clear_unicode
	
	
	// Akcije
	if ($_REQUEST['akcija'] == "slanje") {
		akcijaslanje();
		return;
	}
	
	
	// Poslani parametri
	$zadaca = intval($_REQUEST['zadaca']);
	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);
	$zadatak = intval($_REQUEST['zadatak']);
	
	// Test if student is enrolled on course
	$course = getCourseDetails($predmet, $ag);
	if (empty($course)) {
		zamgerlog("nepoznat predmet $predmet",3); // nivo 3: greska
		zamgerlog2("nepoznat predmet", $predmet);
		biguglyerror("Nepoznat predmet");
		return;
	}

	$assignments = api_call("homework/course/$predmet/student/$userid", ["resolve" => ["Homework", "ProgrammingLanguage"], "year" => $ag, "submittedTime" => true ]);
	if ($_api_http_code != 200) {
		niceerror("Neuspješno čitanje podataka o zadaćama");
		api_report_bug($assignments, []);
		return;
	}
	$assignments = $assignments['results'];
	
	//  IMA LI AKTIVNIH?
	// TODO: provjeriti da li je aktivan modul...
	//  ODREĐIVANJE ID ZADAĆE
	// Da li neko pokušava da spoofa zadaću?
	// Zaglavlje tabele - potreban nam je max. broj zadataka u zadaci
	$anyActive = false;
	$currentAssignment = [];
	$homeworks = $assignmentHomeworks = [];
	$maxAssignments = 0;
	
	foreach($assignments as $assignment) {
		$id = $assignment['Homework']['id'];
		$homeworks[$id] = $assignment['Homework'];
		if (!array_key_exists($id, $assignmentHomeworks)) $assignmentHomeworks[$id] = [];
		$assignmentHomeworks[$id][$assignment['assignNo']-1] = $assignment;
		
		if ($assignment['Homework']['active']) $anyActive = true;
		if ($assignment['Homework']['nrAssignments'] > $maxAssignments)
			$maxAssignments = $assignment['Homework']['nrAssignments'];
		
		if ($zadaca != 0 && $id == $zadaca) {
			// Homework is selected, use last worked assignment in that homework
			if ($assignment['assignNo'] == $zadatak)
				$currentAssignment = $assignment;
			else if (empty($currentAssignment))
				$currentAssignment = $assignment;
			else if ($zadatak == 0 && db_timestamp($assignment['time']) > db_timestamp($currentAssignment['time']))
				$currentAssignment = $assignment;
			
		} else if ($zadaca == 0) {
			// Homework not selected, if homework is active and not past deadline, use last worked assignment
			if ($assignment['Homework']['active'] && db_timestamp($assignment['Homework']['deadline']) < time() && $assignment['status'] != 0) {
				if (empty($currentAssignment))
					$currentAssignment = $assignment;
				else if (db_timestamp($assignment['time']) > db_timestamp($currentAssignment['time']))
					$currentAssignment = $assignment;
			}
		}
	}
	
	if (!$anyActive) {
		zamgerlog("nijedna zadaća nije aktivna, predmet pp$predmet", 3);
		zamgerlog2("nijedna zadaca nije aktivna", $predmet);
		niceerror("Nijedna zadaća nije aktivna");
		return;
	}

	if ($zadaca != 0 && empty($currentAssignment)) {
		// This can only happen if unknown homework was selected
		zamgerlog("student nije upisan na predmet (zadaca z$zadaca)",3);
		zamgerlog2("student ne slusa predmet za zadacu", $zadaca);
		biguglyerror("Ova zadaća nije iz vašeg predmeta");
		return;
	}
	
	if (empty($currentAssignment)) {
		// Try assignment no 1 in homework that is active and not past deadline
		foreach($assignments as $assignment) {
			if ($assignment['Homework']['active'] && db_timestamp($assignment['Homework']['deadline']) < time() && $assignment['assignNo'] == 1) {
				$currentAssignment = $assignment;
				break;
			}
		}
	}
	
	// This can happen if all assignments are past deadline, just give last worked assignment
	if (empty($currentAssignment)) {
		foreach($assignments as $assignment) {
			if ($assignment['status'] != 0) {
				if (empty($currentAssignment))
					$currentAssignment = $assignment;
				else if (db_timestamp($assignment['time']) > db_timestamp($currentAssignment['time']))
					$currentAssignment = $assignment;
			}
		}
	}
	
	// No worked assignments, give assignment no 1 from last homework
	if (empty($currentAssignment)) {
		foreach($assignments as $assignment) {
			if ($assignment['Homework']['active'] && $assignment['assignNo'] == 1) {
				if (empty($currentAssignment))
					$currentAssignment = $assignment;
				else if (db_timestamp($assignment['Homework']['deadline']) > db_timestamp($currentAssignment['Homework']['deadline']))
					$currentAssignment = $assignment;
			}
		}
		// Now current assignment must be set, otherwise there are no active homeworks (caught earlier)
	}
	
	// Shortcuts
	if ($zadaca == 0) $zadaca = $currentAssignment['Homework']['id'];
	if ($zadatak == 0) $zadatak = $currentAssignment['assignNo'];
	
	$naziv = $currentAssignment['Homework']['name'];
	$rok = db_timestamp($currentAssignment['Homework']['deadline']);
	$jezik = $currentAssignment['Homework']['ProgrammingLanguage']['id'];
	$ace_mode = $currentAssignment['Homework']['ProgrammingLanguage']['ace'];
	$attachment = $currentAssignment['Homework']['attachment'];
	$zadaca_dozvoljene_ekstenzije = $currentAssignment['Homework']['allowedExtensions'];
	$readonly_zadaca = $currentAssignment['Homework']['readonly'];
	$filename = $currentAssignment['filename'];


	//  NAVIGACIJA
	
	print "<br/><br/><center><h1>$naziv, Zadatak: $zadatak</h1></center>\n";
	
	
	// Statusne ikone:
	$stat_icon = array("bug", "view", "copy", "bug", "view", "ok");
	$stat_tekst = array("Bug u programu", "Pregled u toku", "Potrebna odbrana", "Bug u programu", "Pregled u toku", "Zadaća OK");


	?>
	
	
	<!-- zadace -->
	<center>
	<table cellspacing="0" cellpadding="2" border="0" id="zadace" class="zadace">
		<thead>
			<tr>
	<?



	?>
		<td>&nbsp;</td>
	<?

	for ($i=1; $i<=$maxAssignments; $i++) {
		?><td>Zadatak <?=$i?>.</td><?
	}

	?>
			<td>Rok za slanje</td>
			</tr>
		</thead>
	<tbody>
	<?


	// Tijelo tabele
	
	foreach ($homeworks as $homework) {
	?>
	<tr>
		<th><?=$homework['name']?></th>
		<?
		
		for($asgn=1; $asgn<=$maxAssignments; $asgn++) {
			// If this homework has less than maxAssignments, print empty cells
			if ($asgn > $homework['nrAssignments']) {
				?><td>&nbsp;</td><?
				continue;
			}
		
			// Get assignment from 2D array
			$Assignment = $assignmentHomeworks[$homework['id']][$asgn-1];
			
			// Background color for current assignment
			if ($homework['id'] == $zadaca && $asgn==$zadatak)
				$bgcolor = ' bgcolor="#DDDDFF"';
			else
				$bgcolor = "";
			
			// status=0 means user did not submit homework
			if ($Assignment['status'] == 0) {
				?><td <?=$bgcolor?>><a href="?sta=student/zadaca&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>&amp;zadaca=<?=$homework['id']?>&amp;zadatak=<?=$asgn?>"><img src="static/images/16x16/create_new.png" width="16" height="16" border="0" align="center" title="Novi zadatak" alt="Novi zadatak"></a></td><?
				
			} else {
				if (!empty(trim($Assignment['comment'])))
					$hasComment = "<img src=\"static/images/16x16/comment_yellow.png\"  width=\"15\" height=\"14\" border=\"0\" title=\"Ima komentar\" alt=\"Ima komentar\" align=\"center\">";
				else
					$hasComment = "";
				?><td <?=$bgcolor?>><a href="?sta=student/zadaca&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>&amp;zadaca=<?=$homework['id']?>&amp;zadatak=<?=$asgn?>"><img src="static/images/16x16/<?=$stat_icon[$Assignment['status']]?>.png" width="16" height="16" border="0" align="center" title="<?=$stat_tekst[$Assignment['status']]?>" alt="<?=$stat_tekst[$Assignment['status']]?>"> <?=$Assignment['score']?> <?=$hasComment?></a></td>
				<?
			}
		}
		?>
		<td><?
			$deadline = db_timestamp($homework['deadline']);
			if ($deadline < time()) print "<font color=\"red\">";
			print date("d. m. Y. H:i:s", $deadline);
			if ($deadline < time()) print "</font>";
			?></td>
	</tr>
	<?
	}

	?>
	</tbody>
	</table>
	</center>
	<?





	
	//  PORUKE I KOMENTARI
	
	
	// Upit za izvjestaj skripte i komentar tutora
	
	?>
	<br/><br/>
	<center>
	<table width="600" border="0"><tr><td>
	<?

	if ($currentAssignment['id'] > 0) {
		$poruka = $currentAssignment['compileReport'];
		$komentar = $currentAssignment['comment'];
		$tutor = $currentAssignment['author']['id'];
		$status_zadace = $currentAssignment['status'];
		$vrijemeSlanja = $currentAssignment['submittedTime'];

		// Statusni ekran
		autotest_status_display($userid, $zadaca, $zadatak, $currentAssignment, /*$nastavnik = */false);

		if ($vrijemeSlanja) {
			?>
			<p>Zadatak poslan: <?=date("d.m.Y. H:i:s", db_timestamp($vrijemeSlanja))?></p>
			<?
		} else {
			?>
			<p>Zadatak nije poslan (tutor upisao/la bodove)</p>
			<?
		}
	
		// Rezultati automatskog testiranja
		if ($currentAssignment['Homework']['automatedTesting']) {
			$nalaz_autotesta = autotest_tabela($userid, $zadaca, $zadatak, /*$nastavnik =*/ false, db_timestamp($currentAssignment['Homework']['deadline']));
			if ($nalaz_autotesta != "") {
				print "<p>Rezultati testiranja:</p>\n$nalaz_autotesta\n";
			}
		}
	
		// Poruke i komentari tutora
		if (preg_match("/\w/",$poruka)) {
			$poruka = str_replace("\n","<br/>\n",$poruka);
			?><p>Poruka kod kompajliranja:<br/><b><?=$poruka?></b></p><?
		}
		if (preg_match("/\w/",$komentar)) {
			$komentar = linkuj_urlove($komentar);
			$komentar = str_replace("\n","<br/>\n",$komentar);
		
			// Link za odgovor na komentar
			$link="";
			if ($tutor>0) {
				$tutor = api_call("person/$tutor");
	
				$naslov = urlencode("Odgovor na komentar ($naziv, Zadatak $zadatak)");
				$tekst = urlencode("> $komentar");
				$primalac = urlencode($tutor['login']." (".$tutor['name']." ".$tutor['surname'].")");
	
				$link = " (<a href=\"?sta=common/inbox&akcija=compose&naslov=$naslov&tekst=$tekst&primalac=$primalac\">odgovor</a>)";
			}
			?><p>Komentar tutora: <b><?=$komentar?></b><?=$link?><?
		}
	}


	// Istek roka za slanje zadace
	
	if ($rok <= time()) {
		print "<p><b>Vrijeme za slanje ove zadaće je isteklo.</b></p>";
		// Ovo je onemogućavalo copy&paste u Firefoxu :(
		//$readonly = "DISABLED";
	} else {
		$readonly = "";
	}
	
	if ($readonly_zadaca) $readonly = "DISABLED";



	//  FORMA ZA SLANJE
	
	
	if ($attachment) {
		print "</td></tr></table>\n";

		if ($currentAssignment['status'] != 0 && $currentAssignment['filename'] != "") {
			if ($currentAssignment['submittedTime'] > 0)
				$vrijeme = date("d. m. Y. H:i:s", db_timestamp($currentAssignment['submittedTime']));
			else
				$vrijeme = date("d. m. Y. H:i:s", db_timestamp($currentAssignment['time']));
			
			$velicina = $currentAssignment['filesize'];
			$icon = "static/images/mimetypes/" . getmimeicon($currentAssignment['filename'], $currentAssignment['filetype']);
			$dllink = "index.php?sta=common/attachment&zadaca=$zadaca&zadatak=$zadatak";
			?>
			<center><table width="75%" border="1" cellpadding="6" cellspacing="0" bgcolor="#CCCCCC"><tr><td>
			<a href="<?=$dllink?>"><img src="<?=$icon?>" border="0"></a>
			</td><td>
			<p>Poslani fajl: <b><a href="<?=$dllink?>"><?=$filename?></a></b><br/>
			Datum slanja: <b><?=$vrijeme?></b><br/>
			Veličina: <b><?=$velicina?></b> <? if ($velicina == "0 B") print "<font color=\"red\"><b>Datoteka je prazna! Možda je niste ispravno poslali?</b></font>"; ?></p>
			</td></tr></table></center>
			<?
			print "<p>Ako želite promijeniti datoteku iznad, izaberite novu i kliknite na dugme za slanje:</p>";
		
		} else if (db_timestamp($currentAssignment['Homework']['deadline']) > time()) {
			print "<p>Izaberite datoteku koju želite poslati i kliknite na dugme za slanje.";
			if ($zadaca_dozvoljene_ekstenzije != "")
				print " Dozvoljeni su sljedeći tipovi datoteka: <b>$zadaca_dozvoljene_ekstenzije</b>.";
			print "</p>\n";
		}
		
		?>

		<form action="index.php" method="POST" enctype="multipart/form-data">
		<input type="hidden" name="sta" value="student/zadaca">
		<input type="hidden" name="akcija" value="slanje">
		<input type="hidden" name="predmet" value="<?=$predmet?>">
		<input type="hidden" name="ag" value="<?=$ag?>">
		<input type="hidden" name="zadaca" value="<?=$zadaca?>">
		<input type="hidden" name="zadatak" value="<?=$zadatak?>">
		<input type="file" name="attachment" size="50" <?=$readonly?>>
		</center>
		<p>&nbsp;</p>
		<?
	
	} else { // if ($attachment)

		if ($status_zadace == 2) {
			?><p>Zadaća se ne može ponovo poslati jer je predviđena odbrana</p><?
		} else if ($rok > time()) {
			?><p>Kopirajte vaš zadatak u tekstualno polje ispod:</p>
			</td></tr></table>
	
			<?
		}
		
		$tekst_zadace = api_call("homework/$zadaca/$zadatak/student/$userid/file", [], "GET", false, false);
		if ($_api_http_code == "404") $tekst_zadace = "";
		$tekst_zadace = htmlspecialchars($tekst_zadace);
		
		?>
		
			</td></tr></table>
		<?=genform("POST")?>
		<input type="hidden" name="zadaca" value="<?=$zadaca?>">
		<input type="hidden" name="zadatak" value="<?=$zadatak?>">
		<input type="hidden" name="akcija" value="slanje">
		
	
		<?
		
		// ACE code editor
		if ($conf_code_viewer == "aceee" && $jezik > 0) {
			// Ako nije definisan programski jezik geshi je lakši
			?>
			<div id="editor" style="text-align: left"><?=$tekst_zadace?></div>
			<script src="static/js/ace/ace.js" type="text/javascript" charset="utf-8"></script>
			<script>
                var editor = ace.edit("editor");
                //editor.setTheme("ace/theme/monokai");
                editor.getSession().setMode("ace/mode/<?=$ace_mode?>");

                // Stavljamo visinu ACE editora na dužinu koda
                var newHeight =
                    editor.getSession().getScreenLength()
                    * editor.renderer.lineHeight
                    + editor.renderer.scrollBar.getWidth() + 20; // 20 = jedan prazan red na kraju
                /*$('#editor').height(newHeight.toString() + "px");
				$('#editor-section').height(newHeight.toString() + "px");
				editor.resize();*/
                document.getElementById('editor').style.height = newHeight.toString() + "px";
                document.getElementById('editor-section').style.height = newHeight.toString() + "px";
                editor.resize();

                // Not editable
                editor.setOptions({
                    readOnly: <? if ($readonly_zadaca) print "true"; else print "false"; ?>,
                    highlightActiveLine: false,
                    highlightGutterLine: false
                })
                editor.renderer.$cursorLayer.element.style.opacity=0
                editor.textInput.getElement().tabIndex=-1
                editor.commands.commmandKeyBinding={}
			</script>
			<?
			
		} else {
			?>
		<center>
			<textarea rows="20" cols="80" name="program" <?=$readonly?> wrap="off"><?
				print $tekst_zadace;
				?></textarea>
		</center>
			<?
		}
		
		?>
		<?
	}
	
	?>
	
	<center><input type="submit" value=" Pošalji zadatak! "></center>
	</form>
	<?


} // function student_zadaca()



function akcijaslanje() {

	global $userid, $conf_files_path, $_api_http_code;

	// Parametri
	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);
	$zadaca = intval($_POST['zadaca']); 
	$zadatak = intval($_POST['zadatak']);
	$program = $_POST['program'];
	
	$povratak_url = "?sta=student/zadaca&predmet=$predmet&ag=$ag&zadaca=$zadaca&zadatak=$zadatak";
	$povratak_html = "<a href=\"$povratak_url\">Nastavak</a>";
	$povratak_js = "<script>window.onload = function() { setTimeout('redirekcija()', 3000); }\nfunction redirekcija() { window.location='$povratak_url'; } </script>\n";

	// Da li student slusa predmet?
	$course = getCourseDetails($predmet, $ag);
	if (empty($course)) {
		biguglyeerror("Ova zadaća nije iz vašeg predmeta");
		return;
	}
	$ponudakursa = $course['CourseOffering']['id'];


	// Da li neko pokušava da spoofa zadaću?
	$homeworks = api_call("homework/course/$predmet/$ag", [ "resolve" => ["ProgrammingLanguage"] ] )['results'];
	$selectedHomework = [];
	foreach($homeworks as $homework)
		if ($homework['id'] == $zadaca && $zadatak >= 1 && $zadatak <= $homework['nrAssignments'])
			$selectedHomework = $homework;
	if (empty($selectedHomework)) {
		biguglyeerror("Ova zadaća nije iz vašeg predmeta");
		return;
	}

	// Podaci o zadaći
	$jezik = $selectedHomework['ProgrammingLanguage']['id'];
	$rok = db_timestamp($selectedHomework['deadline']);
	$attach = $selectedHomework['attachment'];
	$naziv_zadace = $selectedHomework['name'];
	$komponenta = $selectedHomework['CourseActivity']['id'];
	$zadaca_dozvoljene_ekstenzije = $selectedHomework['allowedExtensions'];
	$automatsko_testiranje = $selectedHomework['automatedTesting'];
	if ($selectedHomework['readonly']) {
		niceerror("Slanje ove zadaće kroz Zamger nije moguće");
		return;
	}

	// Provjera roka
	if ($rok <= time()) {
		niceerror("Vrijeme za slanje zadaće je isteklo!");
		zamgerlog("isteklo vrijeme za slanje zadaće z$zadaca",3); // nivo 3 - greska
		zamgerlog2("isteklo vrijeme za slanje zadace",$zadaca); // nivo 3 - greska
		print $povratak_html;
		return; 
	}
	
	// Lokacija na serveru za privremenu datoteku
	$dir = "$conf_files_path/zadacetmp/$userid/";
	if (!file_exists($dir))
		mkdir ($dir,0777, true);
	
	// Prepisane zadaće se ne mogu ponovo slati
	$previousSubmission = api_call("homework/$zadaca/$zadatak/student/$userid", []);
	if ($previousSubmission['status'] == 2) { // status = 2 - prepisana zadaća
		niceerror("Zadaća se ne može ponovo poslati jer je predviđena odbrana.");
		print $povratak_html;
		return; 
	}

	// Vrsta zadaće: textarea ili attachment
	if ($attach == 0) { // textarea
		if (!check_csrf_token()) {
			niceerror("Forma za slanje zadaće je istekla.");
			print "<p>Kada otvorite prozor za unos zadaće, imate određeno vrijeme (npr. 15 minuta) da pošaljete zadaću, u suprotnom zahtjev neće biti prihvaćen iz sigurnosnih razloga. Preporučujemo da zadaću ne radite direktno u prozoru za slanje zadaće nego u nekom drugom programu (npr. CodeBlocks) iz kojeg kopirate u Zamger.</p>";
			print $povratak_html;
			return;
		}

		// Određivanje ekstenzije iz jezika
		$ekst = $selectedHomework['ProgrammingLanguage']['extension'];
		$filename = "$zadatak$ekst";

		// Kreiranje datoteke
		if (strlen($program)<=10) {
			niceerror("Pokušali ste poslati praznu zadaću!");
			print "<p>Vjerovatno ste zaboravili kopirati kod u prozor za slanje.</p>";
			zamgerlog("poslao praznu zadacu z$zadaca zadatak $zadatak",3); // nivo 3 - greska
			zamgerlog2("poslao praznu zadacu", $zadaca, $zadatak); // nivo 3 - greska
			print $povratak_html;
			return;
			
		} else if ($zadaca>0 && $zadatak>0) {
			// Zapisujemo fajl na server radi slanja na backend (može li bez ovoga?)
			$filepath = $dir . $filename;
			
			// Pravimo backup fajla za potrebe računanja diff-a
			$f = fopen($filepath,'w');
			if (!$f) {
				niceerror("Greška pri pisanju fajla za zadaću.");
				zamgerlog("greska pri pisanju zadace z$zadaca zadatak $zadatak",3); // nivo 3 - greska
				zamgerlog2("greska pri pisanju zadace", $zadaca, $zadatak); // nivo 3 - greska
				print $povratak_html;
				return;
			}
			fwrite($f,$program);
			fclose($f);

			// Content-Type: text/plain je najsigurniji pošto je u pitanju sigurno tekstualni format
			$result = api_file_upload("homework/$zadaca/$zadatak/student/$userid", "homework", $filepath, "text/plain");
			
			// Očekivan je kod 201
			if ($_api_http_code == "201") {
				nicemessage($naziv_zadace . "/Zadatak " . $zadatak . " uspješno poslan!");
				zamgerlog("poslana zadaca z$zadaca zadatak $zadatak", 2); // nivo 2 - edit
				zamgerlog2("poslana zadaca (textarea)", $zadaca, $zadatak); // nivo 2 - edit
				print $povratak_html;
				print $povratak_js;
			} else {
				niceerror("Neuspješno slanje zadaće");
				api_report_bug($result, []);
			}
			return;
		} else {
			zamgerlog("greska pri slanju zadace (zadaca z$zadaca zadatak $zadatak filename $filename)",3);
			zamgerlog2("greska pri slanju zadace (textarea)", $zadaca, $zadatak); // nivo 2 - edit
			niceerror("Greška pri slanju zadaće. Kontaktirajte tutora.");
			print $povratak_html;
			return;
		}

	} else { // if ($attach==0)...
		$program = $_FILES['attachment']['tmp_name'];
		if ($program && (file_exists($program)) && $_FILES['attachment']['error']===UPLOAD_ERR_OK) {
			$filename = strip_tags(basename($_FILES['attachment']['name']));

			// Ukidam HTML znakove radi potencijalnog XSSa
			$filename = str_replace("&", "", $filename);
			$filename = str_replace("\"", "", $filename);

			// Provjeravamo da li je ekstenzija na spisku dozvoljenih
			$ext = ".".pathinfo($filename, PATHINFO_EXTENSION); // FIXME: postojeći kod očekuje da ekstenzije počinju tačkom...
			$db_doz_eks = explode(',',$zadaca_dozvoljene_ekstenzije);
			if ($zadaca_dozvoljene_ekstenzije != "" && !in_array($ext, $db_doz_eks)) {
				niceerror("Tip datoteke koju ste poslali nije dozvoljen.");
				print "<p>Na ovoj zadaći dozvoljeno je slati samo datoteke jednog od sljedećih tipova: <b>$zadaca_dozvoljene_ekstenzije</b>.<br>
				Vi ste poslali datoteku tipa: <b>$ext</b>.</p>";
				zamgerlog("pogresan tip datoteke (z$zadaca)", 3);
				zamgerlog2("pogresan tip datoteke", $zadaca);
				print $povratak_html;
				return;
			}
			
			// Pravimo rename fajla na željenu lokaciju
			$filepath = $dir . $filename;
			if (file_exists($filepath))
				unlink($filepath);
			rename($program, $filepath);
			chmod($filepath, 0640);
			
			$result = api_file_upload("homework/$zadaca/$zadatak/student/$userid", "homework", $filepath);
			
			if ($_api_http_code == "201") {
				nicemessage("Z" . $naziv_zadace . "/" . $zadatak . " uspješno poslan!");
				zamgerlog("poslana zadaca z$zadaca zadatak $zadatak (attachment)", 2); // nivo 2 - edit
				zamgerlog2("poslana zadaca (attachment)", $zadaca, $zadatak);
				print $povratak_html;
				print $povratak_js;
			} else if ($_api_http_code == "403" && ends_with($result['message'], "isn't active")) {
				niceerror("Zadaća nije aktivna");
				print "Molimo kontaktirajte tutora da saznate zašto zadaća nije u Zamgeru označena kao aktivna.";
				print $povratak_html;
				print $povratak_js;
			} else {
				niceerror("Neuspješno slanje zadaće");
				api_report_bug($result, []);
			}
			return;
		} else {
			switch ($_FILES['attachment']['error']) { 
				case UPLOAD_ERR_OK:
					$greska="Poslali ste praznu ili nepostojeću datoteku.";
					break;
				case UPLOAD_ERR_INI_SIZE: 
					$greska="Poslana datoteka je veća od dozvoljene. Trenutno maksimalna dozvoljena veličina je ".ini_get('upload_max_filesize'); 
					break;
				case UPLOAD_ERR_FORM_SIZE: 
					$greska="Poslana datoteka je veća od dozvoljene."; // jednom ćemo omogućiti nastavniku da ograniči veličinu kroz formu
					break;
				case UPLOAD_ERR_PARTIAL: 
					$greska="Slanje datoteke je prekinuto, vjerovatno zbog problema sa vašom konekcijom. Molimo pokušajte ponovo."; 
					break;
				case UPLOAD_ERR_NO_FILE: 
					$greska="Poslali ste praznu ili nepostojeću datoteku.";
					break;
				case UPLOAD_ERR_NO_TMP_DIR: 
					$greska="1 Greška u konfiguraciji Zamgera: nepostojeći TMP direktorij.";
					break;
				case UPLOAD_ERR_CANT_WRITE: 
					$greska="2 Greška u konfiguraciji Zamgera: nemoguće pisati u TMP direktorij.";
					break;
				case UPLOAD_ERR_EXTENSION: 
					$greska="3 Greška u konfiguraciji Zamgera: neka ekstenzija sprječava upload.";
					break;
				default: 
					$greska="Nepoznata greška u slanju datoteke. Kod: ".$_FILES['attachment']['error'];
			} 
			zamgerlog("greska kod attachmenta (z$zadaca): $greska",3);
			zamgerlog2("greska pri slanju zadace (attachment)", $zadaca, $zadatak, 0, $greska);
			niceerror("$greska");
			print $povratak_html;
			return;
		}
	}
}

?>
