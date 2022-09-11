<?

// NASTAVNIK/ISPITI - kreiranje i unos rezultata ispita



function nastavnik_ispiti() {

	global $userid, $_api_http_code;
	
	require_once("lib/formgen.php"); // datectrl, genform
	require_once("lib/zamgerui.php"); // za masovni unos studenata u grupe (mass_input)
	
	global $mass_rezultat;
	
	// Parametri
	$predmet = int_param('predmet');
	$ag = int_param('ag');
	
	
	$course = api_call("course/$predmet/$ag");
	
	// Naziv predmeta
	$predmet_naziv = $course['courseName'];
	if (!$course['AcademicYear']['isCurrent']) $predmet_naziv .= " (" . $course['AcademicYear']['name'] . ")";
	
	// Da li korisnik ima pravo ući u modul?
	
	if ($_api_http_code == "403") {
		zamgerlog("nastavnik/ispiti privilegije (predmet pp$predmet)",3);
		zamgerlog2("nije nastavnik na predmetu", $predmet, $ag);
		biguglyerror("Nemate pravo pristupa ovoj opciji");
		return;
	}

	
	// Neki korisni podaci o ispitima i izabranom ispitu
	$allExams = api_call("exam/course/$predmet/$ag", [ "resolve" => ["CourseActivity"] ] )["results"];
	
	$ispit = int_param('ispit');
	if ($ispit>0) {
		$exam = api_call("exam/$ispit", [ "resolve" => ["CourseActivity"], "withResults" => true ] );
		if ($_api_http_code != "200") {
			niceerror("Nepostojeći ispit");
			print "Moguće je da ste ga već obrisali? Ako ste koristili dugme Back vašeg browsera da biste došli na ovu stranicu, predlažemo da kliknete na link Ispiti sa lijeve strane kako biste dobili ažurnu informaciju.";
			zamgerlog("nepostojeci ispit $ispit ili nije sa predmeta (pp$predmet, ag$ag)", 3);
			zamgerlog2("nepostojeci ispit ili nije sa predmeta", $predmet, $ag, $ispit);
			return;
		}
		
		$finidatum = date("d. m. Y", db_timestamp($exam['date']));
		$tipispita = $exam['CourseActivity']['id'];
		$fini_naziv_ispita = $exam['CourseActivity']['name'];
	}
	
	
	?>
	
	<p>&nbsp;</p>
	
	<p><h3><?=$predmet_naziv?> - Ispiti</h3></p>
	
	<?
	
	if (!$course['AcademicYear']['isCurrent']) {
		?>
		<hr>
		<p><font color="red">Odabrana akademska godina nije aktivna u Zamgeru.</font> Sve promjene koje vršite primjenjuju se retroaktivno na akademsku <?=$course['AcademicYear']['name'] ?>!</p>
		<hr>
		<?
	}
	
	
	// Masovni unos rezultata ispita
	
	if ($_POST['akcija'] == "massinput" && strlen($_POST['nazad'])<1 && check_csrf_token()) {
		//$q30 = db_query("select UNIX_TIMESTAMP(i.datum), k.id, k.gui_naziv, k.maxbodova, i.apsolventski_rok from ispit as i, komponenta as k where i.id=$ispit and i.predmet=$predmet and i.akademska_godina=$ag and i.komponenta=k.id");
	
		if ($ispit>0) {
			print "<p><b>Masovni unos ocjena za ispit $fini_naziv_ispita, održan $finidatum</b></p>";
			$maxbodova = $exam['CourseActivity']['points'];
		}
	
	
		if ($_POST['fakatradi'] != 1) $ispis=1; else $ispis=0;
	
		if ($ispis) {
			?>Akcije koje će biti urađene:<br/><br/>
			<?=genform("POST")?>
			<input type="hidden" name="fakatradi" value="1">
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
	
		// Dozvoljavamo kreiranje blank ispita
		// if (count($mass_rezultat)==0) { ...
	
	
		// Registrovati ispit u bazi - ovaj kod se trenutno ne koristi, ali ga neću još izbacivati
		if ($ispit==0) {
			$naziv = db_escape($_POST['naziv']);
			$dan = intval($_POST['day']);
			$mjesec = intval($_POST['month']);
			$godina = intval($_POST['year']);
			$db_date = "$godina-$mjesec-$dan";
			$mdat = mktime(0,0,0,$mjesec,$dan,$godina);
		
			$tipispita = intval($_POST['tipispita']);
		
			// Da li je ispit vec registrovan?
			$exam = false;
			foreach($allExams as $_exam) {
				if ($_exam['date'] == $db_date && $_exam['CourseActivity']['id'] == $tipispita)
					$exam = $_exam;
			}
			if ($exam != false) {
				if ($ispis) {
					print "Dodati rezultate na postojeći ispit (ID: " . $exam['id'] . "):<br/>";
				}
				$dodavanje=1;
			} else if (!$ispis) {
				$addExam = array_to_object(["id" => 0, "CourseUnit" => [ "id" => $predmet], "AcademicYear" => [ "id" => $ag ], "date" => $db_date, "absolvent" => false, "CourseActivity" => ["id" => $tipispita ] ] );
				$exam = api_call("exam/course/$predmet/$ag", $addExam, "POST");
				$dodavanje=0;
			}
			$ispit = $exam['id'];
		} else $dodavanje=1; // Uvijek je dodavanje
	
	
		// Obrada rezultata
	
		$boja1 = "#EEEEEE";
		$boja2 = "#DDDDDD";
		$boja=$boja1;
		$bojae = "#FFE3DD";
	
		foreach ($mass_rezultat['ime'] as $student=>$ime) {
			$prezime = $mass_rezultat['prezime'][$student];
			$brindexa = $mass_rezultat['brindexa'][$student];
			$bodova = $mass_rezultat['podatak1'][$student];
	
			// pretvori bodove u float uz obradu decimalnog zareza
			$fbodova = floatval(str_replace(",",".",$bodova));
			// samo 0 priznajemo za nula bodova, inace student nije izasao na ispit
			if ($fbodova==0 && strpos($bodova,"0")===FALSE) {
				if ($ispis) {
					?>
					<tr bgcolor="<?=$boja?>">
						<td><?=$prezime?></td><td><?=$ime?></td><td><?=$brindexa?></td>
						<td>nije izašao/la na ispit (unesena je ocjena: <?=$bodova?>)</td>
					</tr>
					<?
					if ($boja==$boja1) $boja=$boja2; else $boja=$boja1;
				}
				continue;
			}
			$bodova = $fbodova;
	
			// Da li je broj bodova veći od maksimalno dozvoljenog?
			if ($bodova > $maxbodova) {
				if ($ispis) {
					?>
					<tr bgcolor="<?=$bojae?>">
						<td><?=$prezime?></td><td><?=$ime?></td><td><?=$brindexa?></td>
						<td>broj bodova <?=$bodova?> je veći od maksimalnih <?=$maxbodova?></td>
					</tr>
					<?
				}
				$greska=1;
				continue;
			}
	
			// Da li je ocjena za studenta vec ranije unesena?
			if ($dodavanje == 1) {
				$found = false;
				foreach($exam['results'] as $result) {
					if ($result['student']['id'] == $student) {
						$found = true; break;
					}
				}
				if ($found) {
					if ($ispis) {
						?>
						<tr bgcolor="<?=$bojae?>">
							<td><?=$prezime?></td><td><?=$ime?></td><td><?=$brindexa?></td>
							<td>već ima rezultat <?=$oc2?>; koristite pogled grupe za izmjenu</td>
						</tr>
						<?
					}
					$greska=1;
					continue; // Ne smijemo dozvoliti dvostruke ocjene u bazi
				}
			}
	
			// Zakljucak
			if ($ispis) {
				?>
				<tr bgcolor="<?=$boja?>">
					<td><?=$prezime?></td><td><?=$ime?></td><td><?=$brindexa?></td>
					<td><?=$bodova?> bodova</td>
				</tr>
				<?
				if ($boja==$boja1) $boja=$boja2; else $boja=$boja1;
			} else {
				$er = array_to_object( [ "result" => $bodova ] );
				$result = api_call("exam/$ispit/student/$student", $er, "PUT");
				if ($_api_http_code == "201")
					zamgerlog2("upisan rezultat ispita", $student, $ispit, 0, $bodova);
				else
					niceerror("Neuspješan upis rezultata za studenta $ime $prezime: " . $result['message']);
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
			// Generisem statičku verziju izvještaja predmet
			generisi_izvjestaj_predmet( $predmet, $ag );
	
			zamgerlog("masovni rezultati ispita za predmet pp$predmet",4);
			?>
			Rezultati ispita su upisani.
			<script language="JavaScript">
			location.href='?sta=nastavnik/ispiti&predmet=<?=$predmet?>&ag=<?=$ag?>';
			</script>
			<?
		}
	}
	
	if ($_POST['akcija'] == "massinput" && $_POST['nazad']==" Nazad ") {
		// Redirektujemo na akciju masovni_unos
		$_REQUEST['akcija']='masovni_unos';
	}
	
	
	
	// Brisanje ispita
	
	if ($_REQUEST['akcija']=="brisanje" && $ispit > 0 && $_REQUEST['potvrdabrisanja'] != " Nazad ") {
		$brojstudenata = count($exam['results']);
	
		if ($_REQUEST['potvrdabrisanja'] == " Briši " && check_csrf_token()) {
			print "<p>Brisanje u toku. Molimo budite strpljivi, ova akcija može potrajati nekoliko minuta.</p>\n\n\n\n";
			
			$result = api_call("exam/$ispit", [], "DELETE");
			if ($_api_http_code == "204") {
				zamgerlog("obrisan ispit $ispit (pp$predmet, ag$ag)", 4); // 4 - audit
				zamgerlog2("obrisan ispit", $predmet, $ag, $ispit);
				nicemessage("Svi podaci su ažurirani.");
			} else {
				niceerror("Greška prilikom brisanja ispita");
				api_report_bug($result, []);
			}
			
			// Generisem statičku verziju izvještaja predmet
			generisi_izvjestaj_predmet( $predmet, $ag );
			
			print "<a href=\"?sta=nastavnik/ispiti&predmet=$predmet&ag=$ag\">Nazad</a>\n";
			return;
	
		} else {
			$events = api_call("event/exam/$ispit")["results"];
			$brojprijava = 0;
			foreach($events as $event)
				$brojprijava += $event['registered'];
	
			print genform("POST");
			?>
			<h2>Zatražili ste brisanje ispita &quot;<?=$fini_naziv_ispita?>&quot; održanog <?=$finidatum?></h2>
			<p><font color="red"><b>Brisanje ispita je vrlo destruktivna akcija!</b></font></p>
			<p>Brisanjem ispita potpuno ćete promijeniti bodovanje svih studenata na predmetu. Ova operacija se ne može vratiti! Da li ste sigurni da to želite?<br /><br />
			Na odabranom ispitu su registrovani rezultati za <b><?=$brojstudenata?> studenata</b>.<br /><br />
			<? if ($brojprijava>0) { ?>Za polaganje ovog ispita je prijavljeno <b><?=$brojprijava?> studenata</b>.<br /><br /><? } ?>
			<input type="submit" name="potvrdabrisanja" value=" Briši ">
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input type="submit" name="potvrdabrisanja" value=" Nazad ">
			<?
			return;
		}
	}
	
	
	
	// Promjena ispita
	
	if ($_REQUEST['akcija']=="promjena" && $ispit > 0 && $_REQUEST['potvrdapromjene'] != " Nazad ") {
		$brojstudenata = count($exam['results']);
	
		if ($_REQUEST['potvrdapromjene'] == " Promijeni " && check_csrf_token()) {
			$dan = nuliraj_broj(int_param('day'));
			$mjesec = nuliraj_broj(int_param('month'));
			$godina = nuliraj_broj(int_param('year'));
			$db_date = "$godina-$mjesec-$dan";
			
			$nap = int_param('apsolventski_rok');
			$nova_komponenta = intval($_POST['tipispita']);
			
			if ($nap > 0 && !$exam['absolvent'] || $nap == 0 && $exam['absolvent']) {
				if ($nap) {
					zamgerlog("ispit $ispit proglasen za apsolventski", 4);
					print "<p>Označavam ispit kao apsolventski rok</p>";
				} else {
					zamgerlog("ispit $ispit proglasen za ne-apsolventski", 4);
					print "<p>Označavam da ispit nije apsolventski rok</p>";
				}
			}
			if ($komponenta != $nova_komponenta) {
				zamgerlog ("promijenjen tip ispita $ispit (pp$predmet, ag$ag)", 4); // 4 - audit
				zamgerlog2 ("promijenjen tip ispita", $ispit);
				print "<p>Ažuriranje u toku. Molimo budite strpljivi, ova akcija može potrajati nekoliko minuta.</p>\n\n\n\n";
			}
	
			if ($db_date != $exam['date']) {
				zamgerlog ("promijenjen datum ispita $ispit (pp$predmet, ag$ag)", 4); // 4 - audit
				zamgerlog2("promijenjen datum ispita", $ispit);
				print "<p>Ažuriram datum ispita.</p>\n";
			}
			
			$changedExam = array_to_object([ "id" => $ispit, "CourseUnit" => [ "id" => $predmet ], "AcademicYear" => [ "id" => $ag ], "date" => $db_date, "absolvent" => ($nap>0), "CourseActivity" => [ "id" => $nova_komponenta ] ] );
			$result = api_call("exam/$ispit", $changedExam, "PUT");
			if ($_api_http_code == "201") {
				nicemessage("Svi podaci su ažurirani.");
			} else {
				niceerror("Neuspješno ažuriranje ispita");
				api_report_bug($result, $changedExam);
			}
			print "<a href=\"?sta=nastavnik/ispiti&predmet=$predmet&ag=$ag\">Nazad</a>\n";
			return;
	
		} else {
			$dan = date("d", db_timestamp($exam['date']));
			$mjesec = date("m", db_timestamp($exam['date']));
			$godina = date("Y", db_timestamp($exam['date']));
			$apsolventski_rok = $exam['absolvent'];
	
			print genform("POST");
			?>
			<h2>Zatražili ste promjenu podataka ispita &quot;<?=$fini_naziv_ispita?>&quot; održanog <?=$finidatum?></h2>
			<p>Na odabranom ispitu su registrovani rezultati za <b><?=$brojstudenata?> studenata</b>.<br><br>
			<p>Datum ispita: <?=datectrl($dan, $mjesec, $godina)?></p>
			<p>Apsolventski rok: <select name="apsolventski_rok" id="">
				<option value="0">Ne</option>
				<option value="1" <? if ($apsolventski_rok) print "SELECTED"; ?>>Da</option>
			</select></p>
			<p>Tip ispita: <select name="tipispita" class="default"><?
			
			foreach($course['activities'] as $cact) {
				if ($cact['Activity']['id'] == 8) { // 8 = Exam
					print '<option value="'.$cact['id'].'"';
					if ($tipispita==$cact['id']) print ' SELECTED';
					print '>'.$cact['name'].'</option>'."\n";
				}
			}
			?></select><br />
			<font color="red">Promjenom tipa ispita mijenjate bodovanje za sve studente! Ova operacija može potrajati malo duže.</font></p>
			
			<input type="submit" name="potvrdapromjene" value=" Promijeni ">
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input type="submit" name="potvrdapromjene" value=" Nazad ">
			<?
			return;
		}
	}
	
	
	
	// Masovni unos rezultata ispita
	
	if ($_REQUEST['akcija']=="masovni_unos") {
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
	
		?>
		<h4>Masovni unos ocjena za ispit <?=$fini_naziv_ispita?>, održan <?=$finidatum?></h4>
	
		<?=genform("POST");?>
		<input type="hidden" name="fakatradi" value="0">
		<input type="hidden" name="akcija" value="massinput">
		<input type="hidden" name="nazad" value="">
		<input type="hidden" name="brpodataka" value="1">
		<input type="hidden" name="duplikati" value="0">
	
		<textarea name="massinput" cols="50" rows="10"><?
		if (strlen($_POST['nazad'])>1) print $_POST['massinput'];
		?></textarea><br/>
		<br/>Format imena i prezimena: <select name="format" class="default">
		<option value="0" <? if($format==0) print "SELECTED";?>>Prezime[TAB]Ime</option>
		<option value="1" <? if($format==1) print "SELECTED";?>>Ime[TAB]Prezime</option>
		<option value="2" <? if($format==2) print "SELECTED";?>>Prezime Ime</option>
		<option value="3" <? if($format==3) print "SELECTED";?>>Ime Prezime</option>
		<option value="4" <? if($format==4) print "SELECTED";?>>Broj indeksa</option>
		<option value="5" <? if($format==5) print "SELECTED";?>>Username</option></select>&nbsp;
		Separator: <select name="separator" class="default">
		<option value="0" <? if($separator==0) print "SELECTED";?>>Tab</option>
		<option value="1" <? if($separator==1) print "SELECTED";?>>Zarez</option></select><br/><br>
		<input type="submit" value="  Dodaj  ">
		</form>
		<p><a href="?sta=nastavnik/ispiti&predmet=<?=$predmet?>&ag=<?=$ag?>">Nazad na tabelu ispita</a></p>
		<?
		return;
	}
	
	
	// Kreiranje novog ispita
	
	if ($_REQUEST['akcija']=="novi_ispit") {
		$dan = nuliraj_broj(int_param('day'));
		$mjesec = nuliraj_broj(int_param('month'));
		$godina = nuliraj_broj(int_param('year'));
		$db_date = "$godina-$mjesec-$dan";
	
	
		$tipispita = intval($_POST['tipispita']);
		$apsolventski_rok = intval($_POST['apsolventski_rok']);
	
		// Da li je ispit vec registrovan?
		$found = false;
		foreach($allExams as $_exam) {
			if ($_exam['date'] == $db_date && $_exam['CourseActivity']['id'] == $tipispita)
				$found = true;
		}
		if ($found) {
			nicemessage("Ispit već postoji.");
		} else {
			$addExam = array_to_object(["id" => 0, "CourseUnit" => [ "id" => $predmet ], "AcademicYear" => [ "id" => $ag ], "date" => $db_date, "absolvent" => ($apsolventski_rok > 0), "CourseActivity" => ["id" => $tipispita ] ] );
			$exam = api_call("exam/course/$predmet/$ag", $addExam, "POST");
			if ($_api_http_code == "201") {
				$ispit = $exam['id'];
				nicemessage("Ispit uspješno kreiran.");
				zamgerlog("kreiran novi ispit (predmet pp$predmet, ag$ag)", 4); // 4 - audit
				zamgerlog2("kreiran novi ispit", $ispit, $predmet, $ag);
			} else if ($_api_http_code == "400" && $exam['message'] == "Invalid date") {
				niceerror("Neispravan datum ispita");
				print "<p>Unijeli ste datum $dan. $mjesec. $godina. koji je nemoguć.</p>";
			} else {
				niceerror("Neuspješno kreiranje ispita");
				api_report_bug($exam, $addExam);
				print "<br><br>";
			}
			print "<a href=\"?sta=nastavnik/ispiti&predmet=$predmet&ag=$ag\">Nazad</a>\n";
			return;
		}
	}
	
	
	// Izvještaj rezultati ispita
	
	if ($_REQUEST['akcija']=="rezultati_ispita") {
		$ispit = intval($_REQUEST['ispit']);
		?>
		<form action="index.php" method="POST">
		<input type="hidden" name="sta" value="izvjestaj/rezultati_ispita">
		<input type="hidden" name="ispit" value="<?=$ispit;?>">
		<input type="hidden" name="predmet" value="<?=$predmet ?>">
		<input type="hidden" name="ag" value="<?=$ag ?>">
		<h3>Rezultati ispita</h3>
		<p>Molimo da u polje ispod unesete obaviještenje o terminu uvida u radove koje će biti dodato na dno izvještaja.</p>
		<textarea name="obavijest_uvid" rows="10" cols="60"></textarea>
		<p>U koliko kolona želite ispis: <select name="kolone"><option value="1">Jedna kolona</option><option value="2">Dvije kolone</option></select></p>
		<input type="submit" value=" Kreiraj izvještaj "></form>
		<?
	
		return;
	}
	
	
	
	
	// GLAVNI EKRAN
	
	// Tabela unesenih ispita
	
	?>
	<br>
	<table border="0" cellspacing="1" cellpadding="2">
	<thead>
	<tr bgcolor="#999999">
		<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Tip ispita</font></td>
		<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Datum ispita</font></td>
		<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Opcije</font></td>
	</tr>
	</thead>
	<tbody>
	<?
	
	$brojac=1;
	
	if (count($allExams) == 0)
		print "Nije unesen nijedan ispit.";
	
	foreach($allExams as $_exam) {
		$id = $_exam['id'];
		?>
		<tr>
			<td align="left"><?=$_exam['CourseActivity']['name']?> <? if ($_exam['absolvent']) print " - apsolventski rok"; ?></td>
			<td align="left"><?=date("d.m.Y.", db_timestamp($_exam['date']));?></td>
			<td align="left">
				<a href="?sta=nastavnik/ispiti&amp;akcija=masovni_unos&ispit=<?=$id;?>&amp;predmet=<?=$predmet ?>&amp;ag=<?=$ag ?>">Masovni unos rezultata</a>
				*
				<a href="?sta=nastavnik/ispiti&amp;akcija=promjena&ispit=<?=$id;?>&amp;predmet=<?=$predmet ?>&amp;ag=<?=$ag ?>">Promjena</a>
				*
				<a href="?sta=nastavnik/ispiti&amp;akcija=brisanje&ispit=<?=$id;?>&amp;predmet=<?=$predmet ?>&amp;ag=<?=$ag ?>">Brisanje</a>
				*
				<a href="?sta=nastavnik/prijava_ispita&amp;ispit=<?=$id;?>&amp;predmet=<?=$predmet ?>&amp;ag=<?=$ag ?>">Termini</a>
				*
				<a href="?sta=izvjestaj/ispit&amp;ispit=<?=$id;?>&amp;predmet=<?=$predmet ?>">Statistika</a>
				*
				<a href="?sta=nastavnik/ispiti&amp;akcija=rezultati_ispita&amp;ispit=<?=$id;?>&amp;predmet=<?=$predmet ?>&amp;ag=<?=$ag ?>">Rezultati ispita</a>
			</td>
		</tr>
		<?
	
		$brojac++;
	}
	?>
	</tbody></table><br>
	
	<p>Ako želite da unosite rezultate ispita jedan po jedan u tabelu studenata, koristite <a href="?sta=saradnik/intro">Spisak predmeta i grupa</a></p>
	<?
	
	
	
	
	// Forma za kreiranje ispita
	
	?>
	<p>&nbsp;</p>
	<?=genform("POST")?>
	<input type="hidden" name="akcija" value="novi_ispit">
	
	<p><b>Dodaj novi ispit</b></p>
	
	<!--br/>Naziv ispita: <input type="text" name="naziv" size="20">&nbsp;-->
	<p>Tip ispita: <select name="tipispita" class="default"><?
		$tipispita = intval($_POST['tipispita']);
		
		foreach($course['activities'] as $cact) {
			if ($cact['Activity']['id'] == 8) { // 8 = Exam
				print '<option value="'.$cact['id'].'"';
				if ($tipispita == $cact['id']) print ' SELECTED';
				print '>'.$cact['name'].'</option>'."\n";
			}
		}
	?></select>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	Datum: <?
	$day=intval($_POST['day']); $month=intval($_POST['month']); $year=intval($_POST['year']);
	if ($day>0) print datectrl($day,$month,$year);
	else print datectrl(date('d'),date('m'),date('Y'));
	?>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	Apsolventski rok:
	<select name="apsolventski_rok" id="">
		<option value="0">Ne</option>
		<option value="1">Da</option>
	</select><br/><br/>
	
	<input type="submit" value="  Dodaj  ">
	<br/><br/><br/>
	
	</form></p>
	<?


}

?>
