<?

// NASTAVNIK/FIKSNE - masovni unos bodova za fiksnu komponentu



function nastavnik_fiksne() {
	
	global $userid, $_api_http_code, $conf_files_path, $conf_backend_url_client;
	
	require_once("lib/zamgerui.php"); // mass_input
	
	global $mass_rezultat; // za masovni unos studenata u grupe
	global $_lv_; // radi autogenerisanih formi
	
	
	// Parametri
	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);
	
	$course = api_call("course/$predmet/$ag");
	
	// Naziv predmeta
	$predmet_naziv = $course['courseName'];
	if (!$course['AcademicYear']['isCurrent']) $predmet_naziv .= " (" . $course['AcademicYear']['name'] . ")";
	
	// Da li korisnik ima pravo ući u modul?
	
	if ($_api_http_code == "403") {
		zamgerlog("nastavnik/fiksne privilegije (predmet pp$predmet)",3);
		zamgerlog2("nije nastavnik na predmetu", $predmet, $ag);
		biguglyerror("Nemate pravo pristupa ovoj opciji");
		return;
	}
	
	// Da li predmet posjeduje fiksne komponente ili projekte (koje za sada posmatramo isto)?
	$fixedCacts = [];
	foreach($course['activities'] as $cact) {
		if ($cact['Activity']['id'] == null || $cact['Activity']['id'] == 4) { // 0 = Fixed score
			$fixedCacts[] = $cact;
		}
	}
	if (empty($fixedCacts)) {
		niceerror("Na predmetu nije kreirana nijedna aktivnost sa fiksnim bodovanjem niti projekat");
		print "<p>Da biste nastavili, podesite <a href=\"?sta=nastavnik/tip&amp;predmet=$predmet&amp;ag=$ag\">aktivnosti</a> za ovaj predmet.</p>\n";
		return;
	}
	
	
	?>
	
	<p>&nbsp;</p>
	
	<p><h3><?=$predmet_naziv?> - Fiksni bodovi</h3></p>
	
	<?
	
	if (!$course['AcademicYear']['isCurrent']) {
		?>
		<hr>
		<p><font color="red">Odabrana akademska godina nije aktivna u Zamgeru.</font> Sve promjene koje vršite primjenjuju se retroaktivno na akademsku <?=$course['AcademicYear']['name'] ?>!</p>
		<hr>
		<?
	}
	
	// Potvrda masovnog unosa
	
	if ($_POST['akcija'] == "massinput" && strlen($_POST['nazad'])<1 && check_csrf_token()) {
		
		if ($_POST['fakatradi'] != 1) $ispis = 1; else $ispis = 0;
		
		// Provjera ostalih parametara
		$cactId = intval($_REQUEST['cact']);
		$foundCact = false;
		foreach($fixedCacts as $cact) {
			if ($cactId == $cact['id'])
				$foundCact = $cact;
		}
		if (!$foundCact) {
			niceerror("Nepoznata aktivnost $cactId");
			return;
		}
		
		
		$maxbodova = $foundCact['points'];
		
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
		
		
		// Get all students to find out course offerings
		$allStudents = api_call("group/course/$predmet/allStudents", [ "year" => $ag ]);
		$studentCOs = [];
		foreach($allStudents['members'] as $member) {
			$studentCOs[$member['student']['id']] = $member['CourseOffering']['id'];
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
			$bodova = str_replace(",", ".", $bodova);
			
			// Student neocijenjen (prazno mjesto za ocjenu)
			if (floatval($bodova)==0 && strpos($bodova,"0")===FALSE) {
				if ($ispis) {
					?>
					<tr bgcolor="<?=$bojae?>">
						<td><?=$prezime?></td><td><?=$ime?></td><td><?=$brindexa?></td>
						<td colspan="2">nije unesen broj bodova <?=$bodova?></td>
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
					<td colspan="2"><?=$bodova?> bodova</td>
				</tr>
				<?
				if ($boja==$boja1) $boja=$boja2; else $boja=$boja1;
			} else {
				$studentScore = array_to_object( [ "student" => [ "id" => $student ], "CourseActivity" => [ "id" => $cactId ], "CourseOffering" => [ "id" => $studentCOs[$student] ], "score" => $bodova ] );
				
				$result = api_call("course/$predmet/$ag/student/$student/score", $studentScore, "PUT");
				if ($_api_http_code == "201") {
					zamgerlog2("izmjena bodova za fiksnu komponentu", intval($student), intval($studentCOs[$student]), intval($cactId), $bodova);
				} else {
					niceerror("Neuspješna promjena bodova");
					api_report_bug($result, $bodova);
					$greska = 1;
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
			if ($greska) {
				?>
				<p>Došlo je do greške. Pregledajte pažljivo poruke iznad ove.</p>
				<?
			} else {
				zamgerlog("masovno upisani bodovi za fiksnu komponentu pp$predmet, komponenta $cactId",2); // 2 = edit
				?>
				Bodovi za aktivnost su upisani.
				<script language="JavaScript">
					setTimeout(function() { location.href='?sta=nastavnik/fiksne&predmet=<?=$predmet?>&ag=<?=$ag?>'; }, 1000);
				</script>
				<?
			}
			return;
		}
	}
	
	
	
	// Forma za masovni unos fiksnih komponenti
	
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
	<p><b>Masovni unos bodova</b><br/>
		<?
		
		print genform("POST");
		?><input type="hidden" name="fakatradi" value="0">
		<input type="hidden" name="akcija" value="massinput">
		<input type="hidden" name="nazad" value="">
		<input type="hidden" name="brpodataka" value="1">
		<input type="hidden" name="duplikati" value="0">
		
		Izaberite aktivnost tipa &quot;Fiksni bodovi&quot; ili &quot;Projekat&quot;: <select name="cact">
			<?
			foreach($fixedCacts as $cact) {
				?>
				<option value="<?=$cact['id']?>"><?=$cact['name']?></option>
				<?
			}
			?>
		</select><br/><br/>
		
		Unesite bodove studente i bodove u formatu definisanom kroz padajuće liste ispod:<br><br>
		
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
	
}

?>