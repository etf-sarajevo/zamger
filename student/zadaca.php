<?

// STUDENT/ZADACA - slanje zadace za studente


function student_zadaca() {

	global $userid,$conf_files_path;
	
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
	
	$course = getCourseDetails($predmet, $ag);
	if (empty($course)) {
		zamgerlog("nepoznat predmet $predmet",3); // nivo 3: greska
		zamgerlog2("nepoznat predmet", $predmet);
		biguglyerror("Nepoznat predmet");
		return;
	}
	$ponudakursa = $course['CourseOffering']['id'];

	$assignments = api_call("homework/course/$predmet/student/$userid", ["resolve" => ["Homework"], "year" => $ag, "submittedTime" => true ])['results'];
	
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
	$attachment = $currentAssignment['Homework']['attachment'];
	$zadaca_dozvoljene_ekstenzije = $currentAssignment['Homework']['allowedExtensions'];
	$readonly_zadaca = $currentAssignment['Homework']['readonly'];
	$filename = $currentAssignment['filename'];


	
	// Akcije vezane za autotest
	// FIXME kada pređemo na autotester v2
	
	if ($_REQUEST['akcija'] == "test_detalji") {
		$test = intval($_REQUEST['test']);
	
		// Provjera spoofinga testa
		$q10 = db_query("SELECT COUNT(*) FROM autotest WHERE id=$test AND zadaca=$zadaca AND zadatak=$zadatak");
		if (db_result($q10,0,0) == 0) {
			niceerror("Odabrani test nije sa odabrane zadaće.");
			return;
		}
	
		autotest_detalji($test, $userid, /* $param_nastavnik = */ false);
		return;
	}


	if ($_REQUEST['akcija'] == "test_sa_kodom") {
		if ($attachment) {
			niceerror("Download zadaće poslane kao attachment sa ugrađenim testnim kodom trenutno nije podržano.");
			return;
		}
		$test = intval($_REQUEST['test']);
	
		// Provjera spoofinga testa
		$q10 = db_query("SELECT COUNT(*) FROM autotest WHERE id=$test AND zadaca=$zadaca AND zadatak=$zadatak");
		if (db_result($q10,0,0) == 0) {
			niceerror("Odabrani test nije sa odabrane zadaće.");
			return;
		}
	
		$kod = autotest_sa_kodom($test, $userid, /* $param_nastavnik = */ false);
	
		?>
		<textarea rows="20" cols="80" name="program" wrap="off"><?=$kod?></textarea>
		<?
	
		return;
	}



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
		$bodova = $currentAssignment['score'];
		$vrijemeSlanja = $currentAssignment['submittedTime'];

		// Statusni ekran
		// FIXME kada pređemo na autotester v2
		autotest_status_display($userid, $zadaca, $zadatak, /*$nastavnik = */false);

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
		// FIXME kada pređemo na autotester v2
		$nalaz_autotesta = autotest_tabela($userid, $zadaca, $zadatak, /*$nastavnik =*/ false);
		if ($nalaz_autotesta != "") {
			print "<p>Rezultati testiranja:</p>\n$nalaz_autotesta\n";
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
			Veličina: <b><?=$velicina?></b></p>
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
	
		?>
		
			</td></tr></table>
		<center>
		<?=genform("POST")?>
		<input type="hidden" name="zadaca" value="<?=$zadaca?>">
		<input type="hidden" name="zadatak" value="<?=$zadatak?>">
		<input type="hidden" name="akcija" value="slanje">
		
		<textarea rows="20" cols="80" name="program" <?=$readonly?> wrap="off"><?
		$tekst_zadace = api_call("homework/$zadaca/$zadatak/student/$userid/file", [], "GET", false, false);
		$tekst_zadace = htmlspecialchars($tekst_zadace);
		print $tekst_zadace;
		?></textarea>
		</center>
	
		<?
	}

	?>
	
	<center><input type="submit" value=" Pošalji zadatak! "></center>
	</form>
	<?


} // function student_zadaca()



function akcijaslanje() {

	global $userid,$conf_files_path;
	require_once("lib/student_predmet.php"); // update_komponente nakon slanja

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
	$q195 = db_query("select sp.predmet from student_predmet as sp, ponudakursa as pk where sp.student=$userid and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
	if (db_num_rows($q195)<1) {
		biguglyeerror("Ova zadaća nije iz vašeg predmeta");
		return;
	}
	$ponudakursa = db_result($q195,0,0);	


	// Standardna lokacija zadaca
	$lokacijazadaca="$conf_files_path/zadace/$predmet-$ag/$userid/";
	if (!file_exists("$conf_files_path/zadace/$predmet-$ag")) {
		mkdir ("$conf_files_path/zadace/$predmet-$ag",0777, true);
	}


	// Da li neko pokušava da spoofa zadaću?
	$q200 = db_query("SELECT count(*) FROM zadaca as z, student_predmet as sp, ponudakursa as pk
	WHERE sp.student=$userid and sp.predmet=pk.id and pk.predmet=z.predmet and pk.akademska_godina=z.akademska_godina and z.id=$zadaca");
	if (db_result($q200,0,0)==0) {
		biguglyeerror("Ova zadaća nije iz vašeg predmeta");
		return;
	}

	// Ovo je potrebno radi pravljenja diff-a
	if (get_magic_quotes_gpc()) {
		$program = stripslashes($program);
	}

	// Podaci o zadaći
	$q210 = db_query("select programskijezik, UNIX_TIMESTAMP(rok), attachment, naziv, komponenta, dozvoljene_ekstenzije, automatsko_testiranje, readonly from zadaca where id=$zadaca");
	$jezik = db_result($q210,0,0);
	$rok = db_result($q210,0,1);
	$attach = db_result($q210,0,2);
	$naziv_zadace = db_result($q210,0,3);
	$komponenta = db_result($q210,0,4);
	$zadaca_dozvoljene_ekstenzije = db_result($q210,0,5);
	$automatsko_testiranje = db_result($q210,0,6);
	if (db_result($q210,0,7) == 1) {
		niceerror("Slanje ove zadaće kroz Zamger nije moguće");
		return;
	}

	// Ako je aktivno automatsko testiranje, postavi status na 1 (automatska kontrola), inace na 4 (ceka pregled)
	if ($automatsko_testiranje==1) $prvi_status=1; else $prvi_status=4;

	// Provjera roka
	if ($rok <= time()) {
		niceerror("Vrijeme za slanje zadaće je isteklo!");
		zamgerlog("isteklo vrijeme za slanje zadaće z$zadaca",3); // nivo 3 - greska
		zamgerlog2("isteklo vrijeme za slanje zadace",$zadaca); // nivo 3 - greska
		print $povratak_html;
		return; 
	}

	// Prepisane zadaće se ne mogu ponovo slati
	$q240 = db_query("select status from zadatak where zadaca=$zadaca and redni_broj=$zadatak and student=$userid order by id desc limit 1");
	if (db_num_rows($q240) > 0 && db_result($q240,0,0) == 2) { // status = 2 - prepisana zadaća
		niceerror("Zadaća se ne može ponovo poslati jer je predviđena odbrana.");
		print $povratak_html;
		return; 
	}

	// Pravimo potrebne puteve
	if (!file_exists($lokacijazadaca)) mkdir ($lokacijazadaca,0777);
	if ($zadaca>0 && !file_exists("$lokacijazadaca$zadaca")) 
		mkdir ("$lokacijazadaca$zadaca",0777);
	
	// Temp fajl radi određivanja diff-a 
	if (file_exists("$lokacijazadaca$zadaca/difftemp")) 
		unlink ("$lokacijazadaca$zadaca/difftemp");
	
	// Vrsta zadaće: textarea ili attachment
	if ($attach == 0) { // textarea
		if (!check_csrf_token()) {
			niceerror("Forma za slanje zadaće je istekla.");
			print "<p>Kada otvorite prozor za unos zadaće, imate određeno vrijeme (npr. 15 minuta) da pošaljete zadaću, u suprotnom zahtjev neće biti prihvaćen iz sigurnosnih razloga. Preporučujemo da zadaću ne radite direktno u prozoru za slanje zadaće nego u nekom drugom programu (npr. CodeBlocks) iz kojeg kopirate u Zamger.</p>";
			print $povratak_html;
			return;
		}

		// Određivanje ekstenzije iz jezika
		$q220 = db_query("select ekstenzija from programskijezik where id=$jezik");
		$ekst = db_result($q220,0,0);

		$filename = "$lokacijazadaca$zadaca/$zadatak$ekst";

		// Kreiranje datoteke
		if (strlen($program)<=10) {
			niceerror("Pokušali ste poslati praznu zadaću!");
			print "<p>Vjerovatno ste zaboravili kopirati kod u prozor za slanje.</p>";
			zamgerlog("poslao praznu zadacu z$zadaca zadatak $zadatak",3); // nivo 3 - greska
			zamgerlog2("poslao praznu zadacu", $zadaca, $zadatak); // nivo 3 - greska
			print $povratak_html;
			return;
		} else if ($zadaca>0 && $zadatak>0) {
			// Pravimo backup fajla za potrebe računanja diff-a
			$postoji_prosla_verzija = false;
			if (file_exists($filename)) {
				rename ($filename, "$lokacijazadaca$zadaca/difftemp"); 
				$postoji_prosla_verzija = true;
			}
			
			$f = fopen($filename,'w');
			if (!$f) {
				niceerror("Greška pri pisanju fajla za zadaću.");
				zamgerlog("greska pri pisanju zadace z$zadaca zadatak $zadatak",3); // nivo 3 - greska
				zamgerlog2("greska pri pisanju zadace", $zadaca, $zadatak); // nivo 3 - greska
				if ($postoji_prosla_verzija)
					rename ("$lokacijazadaca$zadaca/difftemp", $filename);
				print $povratak_html;
				return;
			}
			fwrite($f,$program);
			fclose($f);

			// Tabela "zadatak" funkcioniše kao log događaja u
			// koji se stvari samo dodaju
			$q230 = db_query("insert into zadatak set zadaca=$zadaca, redni_broj=$zadatak, student=$userid, status=$prvi_status, vrijeme=now(), filename='$zadatak$ekst', userid=$userid");
			$id_zadatka = db_insert_id();

			// Pravljenje diffa
			if ($postoji_prosla_verzija) {
				$diff = `/usr/bin/diff -u $lokacijazadaca$zadaca/difftemp $filename`;
				$diff = db_escape($diff);
				if (strlen($diff)>1) {
					if (strlen($diff) > 65535) $diff = substr($diff, 0, 65534);
					$q250 = db_query("insert into zadatakdiff set zadatak=$id_zadatka, diff='$diff'");
				}
				unlink ("$lokacijazadaca$zadaca/difftemp");
			}

			nicemessage($naziv_zadace."/Zadatak ".$zadatak." uspješno poslan!");
			update_komponente($userid,$ponudakursa);
			zamgerlog("poslana zadaca z$zadaca zadatak $zadatak",2); // nivo 2 - edit
			zamgerlog2("poslana zadaca (textarea)", $zadaca, $zadatak); // nivo 2 - edit
			print $povratak_html;
			print $povratak_js;
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
			$ime_fajla = strip_tags(basename($_FILES['attachment']['name']));

			// Ukidam HTML znakove radi potencijalnog XSSa
			$ime_fajla = str_replace("&", "", $ime_fajla);
			$ime_fajla = str_replace("\"", "", $ime_fajla);
			$puni_put = "$lokacijazadaca$zadaca/$ime_fajla";

			// Provjeravamo da li je ekstenzija na spisku dozvoljenih
			$ext = ".".pathinfo($ime_fajla, PATHINFO_EXTENSION); // FIXME: postojeći kod očekuje da ekstenzije počinju tačkom...
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
			
			// Diffing
			$diff = "";
			$q255 = db_query("SELECT filename FROM zadatak WHERE zadaca=$zadaca AND redni_broj=$zadatak AND student=$userid ORDER BY id DESC LIMIT 1");
			if (db_num_rows($q255) > 0) {
				$stari_filename = "$lokacijazadaca$zadaca/".db_result($q255, 0, 0);

				// Podržavamo diffing ako je i stara i nova ekstenzija ZIP (TODO ostale vrste arhiva)
				if (ends_with($stari_filename, ".zip") && ends_with($puni_put, ".zip")) {
				
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
				}
			}

			if (file_exists($puni_put)) unlink ($puni_put);
			rename($program, $puni_put);
			chmod($puni_put, 0640);

			// Escaping za SQL
			$ime_fajla = db_escape($ime_fajla);

			$q260 = db_query("insert into zadatak set zadaca=$zadaca, redni_broj=$zadatak, student=$userid, status=$prvi_status, vrijeme=now(), filename='$ime_fajla', userid=$userid");
			$id_zadatka = db_insert_id();

			if (strlen($diff)>1) {
				if (strlen($diff) > 65535) $diff = substr($diff, 0, 65534);
				$q270 = db_query("insert into zadatakdiff set zadatak=$id_zadatka, diff='$diff'");
			}

			nicemessage("Z".$naziv_zadace."/".$zadatak." uspješno poslan!");
			update_komponente($userid,$ponudakursa,$komponenta);
			zamgerlog("poslana zadaca z$zadaca zadatak $zadatak (attachment)",2); // nivo 2 - edit
			zamgerlog2("poslana zadaca (attachment)", $zadaca, $zadatak);
			print $povratak_html;
			print $povratak_js;
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
