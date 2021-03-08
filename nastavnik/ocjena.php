<?

// NASTAVNIK/OCJENA - masovni unos konacnih ocjena



function nastavnik_ocjena() {
	
	global $_api_http_code;
	
	require("lib/manip.php");
	global $mass_rezultat; // za masovni unos studenata u grupe
	
	
	
	// Parametri
	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);
	
	$course = api_call("course/$predmet/$ag");
	
	// Naziv predmeta
	$predmet_naziv = $course['courseName'];
	if (!$course['AcademicYear']['isCurrent']) $predmet_naziv .= " (" . $course['AcademicYear']['name'] . ")";
	
	// Da li korisnik ima pravo ući u modul?
	
	if ($_api_http_code == "403") {
		zamgerlog("nastavnik/ocjena privilegije (predmet pp$predmet)",3);
		zamgerlog2("nije nastavnik na predmetu", $predmet, $ag);
		biguglyerror("Nemate pravo pristupa ovoj opciji");
		return;
	}
	
	
	?>
	
	<p>&nbsp;</p>
	
	<p><h3><?=$predmet_naziv?> - Konačna ocjena</h3></p>
	
	<?
	
	if (!$course['AcademicYear']['isCurrent']) {
		?>
		<hr>
		<p><font color="red">Odabrana akademska godina nije aktivna u Zamgeru.</font> Sve promjene koje vršite primjenjuju se retroaktivno na akademsku <?=$course['AcademicYear']['name'] ?>!</p>
		<hr>
		<?
	}
	
	?>
	
	<p><a href="?sta=nastavnik/unos_ocjene&predmet=<?=$predmet?>&ag=<?=$ag?>">Pojedinačni unos konačnih ocjena</a></p>
	
	<?
	
	// Da li je prošao ispitni rok?
	$examTerms = api_call("exam/terms", [ "year" => $ag ] )["results"];
	if (!empty($examTerms)) {
		$hasTerm = false;
		foreach($examTerms as $examTerm) {
			if (db_timestamp($examTerm['dateStart']) <= time() && db_timestamp($examTerm['dateEnd']) >= time())
				$hasTerm = true;
		}
		if (!$hasTerm) {
			niceerror("Nije u toku ispitni rok");
			?>
			<p>Unos konačne ocjene je moguć samo dok traju ispitni rokovi. Ako imate potrebu da evidentirate ocjenu izvan ovog perioda, molimo Vas da kontaktirate studentsku službu fakulteta.</p>
			<?
			return;
		}
	}
	
	
	# Masovni unos konačnih ocjena
	
	if ($_POST['akcija'] == "massinput" && strlen($_POST['nazad'])<1 && check_csrf_token()) {
	
		if ($_POST['fakatradi'] != 1) $ispis=1; else $ispis=0; // fakatradi=0 --> ispis=1
	
		if ($_REQUEST['datum']) {
			$uneseni_datumi=true;
			$_REQUEST['brpodataka'] = 2;
		} else {
			$uneseni_datumi=false;
			$_REQUEST['brpodataka'] = 1;
		}
	
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
				<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;color:white;">Ocjena / Komentar</font></td>
				<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;color:white;">Datum</font></td>
			</tr>
			</thead>
			<tbody>
			<?
		}
		
		// We will use allStudents group to see which students already have a grade
		$virtualGroup = api_call("group/course/$predmet/allStudents", [ "year" => $ag, "names" => true ]);
	
		$greska=mass_input($ispis, $virtualGroup); // Funkcija koja parsira podatke
	
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
			$ocjena = $mass_rezultat['podatak1'][$student];
	
			// Student neocijenjen (prazno mjesto za ocjenu)
			if (intval($ocjena)==0 && strpos($ocjena,"0")===FALSE) {
				if ($ispis) {
					?>
					<tr bgcolor="<?=$boja?>">
						<td><?=$prezime?></td><td><?=$ime?></td><td><?=$brindexa?></td>
						<td colspan="2">nije ocijenjen/a (unesena je ocjena: <?=$ocjena?>)</td>
					</tr>
					<?
					if ($boja==$boja1) $boja=$boja2; else $boja=$boja1;
				}
				continue;
			}
	
			// Ocjena mora biti u opsegu 6-10
			$ocjena = intval($ocjena);
			if ($ocjena<6 || $ocjena>10) {
				if ($ispis) {
					?>
					<tr bgcolor="<?=$bojae?>">
						<td><?=$prezime?></td><td><?=$ime?></td><td><?=$brindexa?></td>
						<td colspan="2">ocjena nije u opsegu 6-10 (ocjena: <?=$ocjena?>)</td>
					</tr>
					<?
					$greska=1;
					continue;
				}
			}
	
			// Da li vec ima ocjena u bazi?
			$hasGrade = false;
			foreach($virtualGroup['members'] as $member) {
				if ($member['student']['id'] == $student && $member['grade'] != null)
					$hasGrade = $member['grade'];
			}
			if ($hasGrade) {
				if ($ispis) {
					?>
					<tr bgcolor="<?=$bojae?>">
						<td><?=$prezime?></td><td><?=$ime?></td><td><?=$brindexa?></td>
						<td colspan="2">već ima ocjenu <?=$hasGrade?>; koristite pogled grupe za izmjenu</td>
					</tr>
					<?
					$greska=1;
					continue;
				}
			}
	
			// Ako je unesen datum, taj datum postaje datum_u_indeksu i provjeren je
			if ($uneseni_datumi) {
				$datum_ulaz = str_replace("/", ".", $mass_rezultat['podatak2'][$student]);
				$datum_ulaz = str_replace(". ", ".", $datum_ulaz);
				$matches = array();
				if (preg_match("/^(\d\d)\.(\d\d)\.(\d\d)\.?$/", $datum_ulaz, $matches)) {
					if ($matches[3] < 20) $godina = "20".$matches[3]; else $godina = "19".$matches[3];
					$datum_ulaz = $matches[1].".".$matches[2].".".$godina;
				}
				
				if (preg_match("/^(\d{2})\.(\d{2})\.(\d{4})\.?$/", $datum_ulaz, $matches)) {
					$datum_u_indeksu = $matches[3] . "-" . $matches[2] . "-" . $matches[1];
					$datum_ispis = $matches[1] . ". " . $matches[2] . ". " . $matches[3];
				} else {
					$datum_u_indeksu = null;
					$datum_ispis = "Neispravan format datuma: " . $mass_rezultat['podatak2'][$student] . " (treba biti: dan.mjesec.godina) - zanemarujem";
				}
				
			} else {
				// Ostavićemo null da se odredi na backendu
				$datum_u_indeksu = null;
				$datum_ispis = "Nije unesen datum";
			}
	
			if ($ispis) {
				?>
				<tr bgcolor="<?=$boja?>">
					<td><?=$prezime?></td><td><?=$ime?></td><td><?=$brindexa?></td>
					<td>ocjena: <?=$ocjena?></td>
					<td><?=$datum_ispis?></td>
				</tr>
				<?
				if ($boja==$boja1) $boja=$boja2; else $boja=$boja1;
			} else {
				$portfolio = array_to_object( [ "grade" => $ocjena, "gradeDate" => $datum_u_indeksu ] );
				$result = api_call("course/$predmet/$ag/student/$student/grade", $portfolio, "PUT");
				if ($_api_http_code == "201") {
					zamgerlog("masovno dodana ocjena $ocjena (predmet pp$predmet, student u$student)", 4);
					zamgerlog2("dodana ocjena", $student, $predmet, $ag, $ocjena);
					nicemessage("Dodajem ocjenu za studenta $prezime $ime ($brindexa)");
				} else {
					niceerror("Neuspješno dodavanje ocjene za studenta $prezime $ime ($brindexa)");
					api_report_bug($result, $portfolio);
					$greska = 1;
				}
			}
		}
	
		if ($ispis) {
			if ($greska == 0) {
				?>
				</tbody></table>
				<p>Potvrdite upis ocjena ili se vratite na prethodni ekran.</p>
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
				// Generisem statičku verziju izvještaja predmet
				generisi_izvjestaj_predmet( $predmet, $ag, array("skrati" => "da", "sakrij_imena" => "da", "razdvoji_ispite" => "da") );
		
				zamgerlog("masovno upisane ocjene na predmet pp$predmet",4);
				
				?>
				Ocjene su upisane.
				<script language="JavaScript">
				setTimeout(function() { location.href='?sta=nastavnik/ocjena&predmet=<?=$predmet?>&ag=<?=$ag?>'; }, 1000);
				</script>
				<?
				return;
			}
		}
	}
	
	
	
	
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
			$format = $preferences['mass-input-separator'];
		else //default vrijednost
			$format=0;
	}
	
	
	?><p><b>Masovni unos konačnih ocjena</b><br/>
	<?=genform("POST")?>
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
	<option value="4" <? if($format==4) print "SELECTED";?>>Broj indeksa</option></select>&nbsp;
	Separator: <select name="separator" class="default">
	<option value="0" <? if($separator==0) print "SELECTED";?>>Tab</option>
	<option value="1" <? if($separator==1) print "SELECTED";?>>Zarez</option></select><br/><br/>
	
	<input type="checkbox" name="datum"> Treća kolona: datum u formatu D. M. G.<br/><br/>
	
	<input type="submit" value="  Dodaj  ">
	</form></p>
	<?



}

?>
