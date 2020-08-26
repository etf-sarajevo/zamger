<?php

// NASTAVNIK/PROJEKTI - nastavnicki modul za definisanje projekata, parametara



function nastavnik_projekti() {

	require_once ('lib/projekti.php');
	global $_api_http_code;
	global $conf_files_path;
	
	// Parametri
	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);
	
	$course = api_call("course/$predmet/$ag");
	
	// Naziv predmeta
	$predmet_naziv = $course['courseName'];
	
	// Da li korisnik ima pravo ući u modul?
	
	if ($_api_http_code == "403") {
		zamgerlog("nastavnik/unos_ocjene privilegije (predmet pp$predmet)",3);
		zamgerlog2("nije nastavnik na predmetu", $predmet, $ag);
		biguglyerror("Nemate pravo pristupa ovoj opciji");
		return;
	}

	$linkPrefix = "?sta=nastavnik/projekti&predmet=$predmet&ag=$ag";
	$akcija = $_REQUEST['akcija'];
	$id = intval($_REQUEST['id']);
	
	?>
	<LINK href="static/css/projekti.css" rel="stylesheet" type="text/css">
	<h2<?=$predmet_naziv?> - Projekti</h2>
	<?
	
	// Preuzimanje projektnih parametara
	$params = api_call("project/params/$predmet/$ag");
	$nema_parametara = ($_api_http_code != "200");
	$allProjects = api_call("project/course/$predmet/$ag", [ "members" => true ])["results"];
	usort($allProjects, function ($p1, $p2) {
		return strnatcasecmp($p1['name'], $p2['name']);
	});

	// Glavni meni
	if ($akcija != 'projektna_stranica') {
		?>
		<div class="links">
			<ul class="clearfix">
				<li><a href="<?=$linkPrefix."&akcija=param"?>">Parametri projekata</a></li>
				<li><a href="<?=$linkPrefix ?>">Lista projekata</a></li>
				<li><a href="<?=$linkPrefix."&akcija=dodaj_projekat" ?>">Novi projekat</a></li>
				<li class="last"><a href="<?=$linkPrefix."&akcija=dodjela_studenata"?>">Dodjela projekata studentima</a></li>
			</ul>
		</div>	
		<?
	}
	
	// Default akcija - LISTA PROJEKATA
	if (!isset($akcija)) {
		?>
		<h2>Lista projekata</h2>
		<?

		// Početne informacije
		$projectStats = api_call("project/stats/$predmet/$ag")["results"];
		$broj_projekata = count($allProjects);
		if ($broj_projekata > 0) {
			if ($params['locked']) {
				?>
				<span class="notice">Onemogućene su prijave u projektne timove. Otvorene su projektne stranice.</span>	
				<?
			} else {
				?>
				<span class="noticeGreen">Studenti se još uvijek mogu prijaviti u projektne timove. Niste zaključali spiskove u parametrima prijekata.</span>	
				<?
			}

			if ($projectStats['nonEmpty'] < $params['minTeams']) {
				?>
				<span class="notice">Trenutni broj timova sa barem jednim studentom (<?=$projectStats['nonEmpty']?>) je ispod minimalnog broj timova koji ste definisali za ovaj predmet (<?=$params['minTeams']?>).</span>
				<?
			}

		} else {
			?>
			<span class="notice">Nema kreiranih projekata na ovom predmetu.</span>	
			<?
		}
	
		foreach($allProjects as $project) {
			?>
			<h3><?=$project['name']?></h3>
			<div class="links">
				<ul class="clearfix" style="margin-bottom: 10px;">
					<li><a href="<?=$linkPrefix."&akcija=izmjena_projekta&id=" . $project['id'] ?>">Izmijeni projekat</a></li>
					<li><a href="<?=$linkPrefix."&akcija=dodaj_biljesku&id=" . $project['id'] ?>">Dodaj bilješku</a></li>
					<li <? if (!$params['locked']) { print 'class="last"'; } ?>><a href="<?=$linkPrefix."&akcija=obrisi_projekat&id=" . $project['id'] ?>">Obriši projekat</a></li>
					<?
					if ($params['locked']) {
						?>
						<li class="last"><a href="<?= $linkPrefix . "&akcija=projektna_stranica&projekat=" . $project['id'] ?>">Projektna stranica</a></li>
						<?
					}
					?>
				</ul> 
				<?

				if (count($project['members']) < $params['minTeamMembers']) {
					?>
					<span class="notice">Broj prijavljenih studenata (<?=count($project['members'])?>) je ispod minimuma koji ste definisali za ovaj predmet (<?=$params['minTeamMembers']?>).</span>
					<?
				}

				?>
			</div>

			<table class="projekti" border="0" cellspacing="0" cellpadding="2">
				<tr>
					<th width="200" align="left" valign="top" scope="row">Naziv</th>
					<td width="490" align="left" valign="top"><?=$project['name']?></td>
				</tr>
				<tr>
					<th width="200" align="left" valign="top" scope="row">Prijavljeni studenti</th>
					<td width="490" align="left" valign="top">
					<?

					// Spisak studenata
					if (count($project['members']) < 1)
						print 'Nema prijavljenih studenata.';
					else {
						print "<ul>\n";
						foreach($project['members'] as $member) {
							print "<li>" . $member['surname'] . " " . $member['name'] . " (" . $member['studentIdNr'] . ")";
							if (!$params['locked']) {
								print ' - (<a href="'.$linkPrefix."&akcija=izbaci_studenta&student=" . $member['id'] . "&projekat=" . $project['id'] . '">izbaci</a>)';
							}
							print "</li>\n";
						}
						print "</ul>\n";
					}
					?>
					</td>
				</tr>
				<tr>
					<th width="200" align="left" valign="top" scope="row">Opis</th>
					<td width="490" align="left" valign="top"><?=$project['description']?></td>
				</tr>
			</table>
			<?
		} // while ($r100...
	} // if (!isset($akcija)



	// Akcija PARAMETRI PROJEKATA

	if ($akcija == 'param') {

		if ($_REQUEST['subakcija'] == "potvrda" && check_csrf_token()) {
			// Poslana forma za promjenu
			$min_timova = intval($_REQUEST['min_timova']);
			$max_timova  = intval($_REQUEST['max_timova']);
			
			$min_clanova_tima = intval($_REQUEST['min_clanova_tima']);
			$max_clanova_tima = intval($_REQUEST['max_clanova_tima']);
			
			$zakljucani_projekti = false;
			if (isset($_REQUEST['lock'])) $zakljucani_projekti = true;
			
			if ($min_timova <= 0 || $max_timova <= 0 || $min_clanova_tima <= 0 || $max_clanova_tima <= 0) {
				niceerror("Morate unijeti ispravne vrijednosti u sva polja");
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}

			$params = array_to_object( [ "CourseUnit" => [ "id" => $predmet ], "AcademicYear" => [ "id" => $ag ], "minTeams" => $min_timova, "maxTeams" => $max_timova, "minTeamMembers" => $min_clanova_tima, "maxTeamMembers" => $max_clanova_tima] );
			$result = api_call("project/params/$predmet/$ag", $params, "PUT");
			if ($_api_http_code == "201") {
				nicemessage('Uspješno ste uredili parametre projekata.');
				zamgerlog("izmijenio parametre projekata na predmetu pp$_REQUEST[predmet]", 2);
				zamgerlog2("izmijenjeni parametri projekata na predmetu", $predmet, $ag);
			} else {
				niceerror("Neuspješna izmjena parametara ($_api_http_code): " . $result['message']);
			}
			nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');
			return;
		}

		?>
		<h2>Parametri projekata</h2>

		<?=genform("POST", "editForm"); ?>
		<input type="hidden" name="subakcija" value="potvrda">
		<div id="formDiv">
			Polja sa * su obavezna. <br />
			<div class="row">
				<span class="label">Zaključaj stanje projekata i timova</span>
				<span class="formw"><input name="lock" type="checkbox" id="lock" <? if ($params['locked']) print 'checked';?> /></span>
				<br /><br /> Ova opcija će onemogućiti prijavljivanje na projekte i pokrenuti projektne stranice.
			</div>
			<div class="row">
				<span class="label">MIN timova *</span>
				<span class="formw"><input name="min_timova" type="text" id="min_timova" size="10" value="<?=$params['minTeams'];?>" /></span>
			</div>
			<div class="row">
				<span class="label">MAX timova *</span>
				<span class="formw"><input name="max_timova" type="text" id="max_timova" size="10" value="<?=$params['maxTeams']?>" /></span>
			</div>
			<div class="row">
				<span class="label">MIN članova tima *</span>
				<span class="formw"><input name="min_clanova_tima" type="text" id="min_clanova_tima" size="10" value="<?=$params['minTeamMembers']?>" /></span>
			</div>
			<div class="row">
				<span class="label">MAX članova tima *</span>
				<span class="formw"><input name="max_clanova_tima" type="text" id="max_clanova_tima" size="10" value="<?=$params['maxTeamMembers']?>" /></span>
			</div>
			<div class="row">	
				<span class="formw" style="margin-left:150px;"><input type="submit" id="submit" value="Potvrdi"/></span>
			</div>
		</div><!--formDiv-->
		</form>
		<?
	}



	// Akcija DODAJ PROJEKAT

	elseif ($akcija == 'dodaj_projekat') {
		if ($nema_parametara && !isset($_REQUEST['submit'])) {
			nicemessage("Prvo podesite parametre projekata.");
			nicemessage('<a href="'. $linkPrefix .'&akcija=param">Parametri projekata</a>');
			return;
		}

		if ($params['locked']) {
			niceerror("Zaključali ste stanje projekata na ovom predmetu. Nije moguće napraviti novi projekat.");
			nicemessage('<a href="'. $linkPrefix .'&akcija=param">Parametri projekata</a>');
			return;
		}

		if ($_REQUEST['subakcija'] == "potvrda" && check_csrf_token()) {
			// Poslana forma za dodavanje projekta
			$naziv = trim($_REQUEST['naziv']);
			$opis  = trim($_REQUEST['opis']);
	
			if (empty($naziv) || empty($opis)) {
				niceerror('Unesite sva obavezna polja.');
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}

			$project = array_to_object( [ "id" => 0, "CourseUnit" => [ "id" => $predmet ], "AcademicYear" => [ "id" => $ag ], "name" => $naziv, "description" => $opis ] );
			$result = api_call("project/course/$predmet/$ag", $project, "POST");
			if ($_api_http_code == "201") {
				nicemessage('Novi projekat uspješno dodan.');
				zamgerlog("dodao novi projekat na predmetu pp$predmet", 2);
				zamgerlog2("dodao projekat", db_insert_id(), $predmet, $ag);
			} else {
				niceerror("Neuspješno dodavanje projekta ($_api_http_code): " . $result['message']);
			}

			nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');
			return;
		}
		
		?>	
		<h2>Novi projekat</h2>
		<?=genform("POST", "addForm");?>
		<input type="hidden" name="subakcija" value="potvrda">
			<div id="formDiv">
				Polja sa * su obavezna. <br />
			
				<div class="row">
					<span class="label">Naziv *</span>
					<span class="formw"><input name="naziv" type="text" id="naziv" size="70" /></span> 
				</div>
				<div class="row">
					<span class="label">Opis *</span>
					<span class="formw"><textarea name="opis" cols="60" rows="15" wrap="physical" id="opis"></textarea></span>
				</div> 
				<div class="row">	
					<span class="formw" style="margin-left:150px;"><input type="submit" id="submit" value="Potvrdi"/></span>
				</div>
			</div><!--formDiv-->
		</form>
		<?
	}



	// Akcija IZMJENA PROJEKTA

	elseif ($akcija == 'izmjena_projekta') {

		if ($_REQUEST['subakcija'] == "potvrda" && check_csrf_token()) {
			// Poslana forma za izmjenu projekta
			$naziv = trim($_REQUEST['naziv']);
			$opis  = trim($_REQUEST['opis']);
	
			if (empty($naziv) || empty($opis)) {
				niceerror('Unesite sva obavezna polja.');
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}
			
			$project = array_to_object( [ "id" => $id, "CourseUnit" => [ "id" => $predmet ], "AcademicYear" => [ "id" => $ag ], "name" => $naziv, "description" => $opis ] );
			$result = api_call("project/$id", $project, "PUT");
			if ($_api_http_code == "201") {
				nicemessage('Uspješno ste izmijenili projekat.');
				zamgerlog("izmijenio projekat $id na predmetu pp$predmet", 2);
				zamgerlog2("izmijenio projekat", $id);
			} else {
				niceerror("Neuspješna izmjena projekta ($_api_http_code): " . $result['message']);
			}
			nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');
			return;
		}

		// Prikaz forme
		$project = api_call("project/$id");

		?>
		<h1>Izmijeni projekat</h1>
		<?=genform("POST", "editForm");?>
		<input type="hidden" name="subakcija" value="potvrda">
			<div id="formDiv">
				Polja sa * su obavezna. <br />
			
				<div class="row">
					<span class="label">Naziv *</span>
					<span class="formw"><input name="naziv" type="text" id="naziv" size="70" value="<?=$project['name']?>" /></span>
				</div>
				<div class="row">
					<span class="label">Opis *</span>
					<span class="formw"><textarea name="opis" cols="60" rows="15" wrap="physical" id="opis"><?=$project['description']?></textarea></span>
				</div> 
				
				<div class="row">	
					<span class="formw" style="margin-left:150px;"><input type="submit" id="submit" value="Potvrdi"/></span>
				</div>
			
			</div><!--formDiv-->
		</form>
		<?
	} //akcija == izmjena_projekta



	// Akcija DODAJ BILJEŠKU

	elseif ($akcija == 'dodaj_biljesku') {
		$project = api_call("project/$id", [], "GET", false, true, false);

		if ($_REQUEST['subakcija'] == "potvrda" && check_csrf_token()) {
			// Poslana forma za dodavanje bilješke
			$project->note = $_REQUEST['biljeska'];
			$result = api_call("project/$id", $project, "PUT");
			if ($_api_http_code == "201") {
				nicemessage('Uspješno ste dodali bilješku.');
				zamgerlog("dodao biljesku na projekat $id na predmetu pp$predmet", 2);
				zamgerlog2("dodao biljesku na projekat", $id);
			} else {
				niceerror("Neuspješno dodavanje bilješke ($_api_http_code): " . $result['message']);
			}

			nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');
			return; 
		}

		// Forma za izmjenu/dodavanje bilješke
		?>
		<h3>Dodaj bilješku za projekat</h3>	
		<?=genform('POST','addNote'); ?>			
		<input type="hidden" name="subakcija" value="potvrda">
			<div class="row">
				<span class="label">Bilješka:</span>
				<span class="formw"><textarea name="biljeska" cols="60" rows="15" wrap="physical" id="opis"><?=$project->note?></textarea></span>
			</div> 
					
			<div class="row">	
				<span class="formw" style="margin-left:150px;"><input type="submit" id="submit" value="Potvrdi"/></span>
			</div>
		</form>
		<?
	} //akcija == dodaj biljesku


	// Akcija OBRIŠI PROJEKAT

	elseif ($akcija == 'obrisi_projekat') {
		if ($_REQUEST['subakcija'] == "potvrda" && check_csrf_token()) {
			$result = api_call("project/$id", [], "DELETE");
			if ($_api_http_code == "204") {
				nicemessage('Uspješno ste obrisali projekat.');
				zamgerlog("izbrisan projekat $id na predmetu pp$predmet", 4);
				zamgerlog2("izbrisan projekat", $id, $predmet, $ag);
			} else {
				niceerror("Neuspješno brisanje projekta ($_api_http_code): " . $result['message']);
			}
			nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');
			return;
		}

		?>
		Da li ste sigurni da želite obrisati ovaj projekat? Svi podaci vezani za aktivnosti na ovom projektu će biti nepovratno izgubljeni.<br />
		<?=genform('POST'); ?>
		<input type="hidden" name="subakcija" value="potvrda">
		<input type="submit" value="Potvrda brisanja">
		<input type="button" onclick="location.replace('<?=$linkPrefix?>');" value="Odustani">
		</form>
		<?
	
	} //akcija == obrisi_projekat


	// Akcija PROJEKTNA STRANICA

	elseif ($akcija == 'projektna_stranica') {
		require_once ('common/projektneStrane.php');
		common_projektneStrane();
	} //akcija == projektna_stranica



	// Akcija DODJELA STUDENATA NA PROJEKTE

	elseif ($akcija == 'dodjela_studenata') {
		if ($_REQUEST['subakcija'] == "dodaj" && check_csrf_token()) {
			// Dodavanje studenta na projekat

			$student = intval($_REQUEST['student']);
			$projekat = intval($_REQUEST['projekat']);

			if ($params['locked']) {
				// Ne bi se smjelo desiti
				niceerror("Zaključane su prijave na projekte.");
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}
			
			$broj_nepraznih = $stari_projekat = 0;
			foreach($allProjects as $project) {
				if ($project['id'] == $projekat && count($project['members']) > $params['maxTeamMembers']) {
					// Ne bi se smjelo desiti
					niceerror("Projekat je popunjen.");
					nicemessage('<a href="javascript:history.back();">Povratak.</a>');
					return;
				}
				foreach($project['members'] as $member) {
					if ($member['id'] == $student) {
						$stari_projekat = $project['id'];
						if (count($project['members']) == 1)
							$broj_nepraznih--;
					}
				}
				if (count($project['members']) > 0)
					$broj_nepraznih++;
			}
			
			if ($broj_nepraznih >= $params['maxTeams']) {
				niceerror("Ne mogu upisati studenta na ovaj projekat jer bi time bio prekoračen maksimalan broj timova. $broj_nepraznih");
				print "<p>Koristite <a href='$linkPrefix&akcija=param'>Parametre projekata</a> da biste povećali ograničenje broja timova.</p>";
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}
			
		
			$result = api_call("project/$projekat/student/$student", [], "PUT");
			if ($_api_http_code == "201") {
				nicemessage('Student je uspješno prijavljen na projekat!');
				if ($stari_projekat==0) {
					zamgerlog ("student u$student prijavljen na projekat $projekat (predmet pp$predmet", 2);
					zamgerlog2 ("student prijavljen na projekat", $student, $projekat);
				} else {
					zamgerlog ("student u$student prebacen sa projekta $stari_projekat na $projekat (predmet pp$predmet", 2);
					zamgerlog2 ("student prebacen na projekat", $student, $projekat, 0, $stari_projekat);
				}
			} else {
				nicemessage("Neuspješno prijavljivanje studenta na projekat ($_api_http_code): " . $result['message']);
			}
			nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');
			return;
		}


		$opcije = "";

		?>
		<!-- Ako je prvi put ucitano, dohvati predmete i dohvati sve studente na predmetu, prikazi formu. -->

		</br>
		<b>LISTA STUDENATA BEZ PROJEKTA:</b>
		<?
		
		$allStudents = api_call("group/course/$predmet/allStudentsSimple", [ "year" => $ag ])["results"];
		usort($allStudents, function ($s1, $s2) {
			if ($s1['surname'] == $s2['surname']) return bssort($s1['name'], $s2['name']);
			return bssort($s1['surname'], $s2['surname']);
		});
		
		foreach ($allStudents as $i => $student) {
			foreach ($allProjects as $project) {
				foreach($project['members'] as $member) {
					if ($member['id'] == $student['id']) {
						unset($allStudents[$i]);
						break;
					}
				}
			}
		}
		
		if (count($allStudents) == 0) {
			nicemessage('Svim studentima je dodijeljen projekat!');
		} else {
			$cnt = 0;
			
			foreach($allStudents as $student) {
				// Odmah kreiramo i opcije za selektovanje studenta
				$opcije .= "<option value='" . $student['id'] . "'>" . $student['surname'] . " " . $student['name'] . "</option>\n";
				$cnt = $cnt+1;
				print "</br>";
				print "<span id=\"noProjectStudent\">$cnt. " . $student['surname'] . " " . $student['name'] . "</span>";
			}
		}
		?>
		<br><br><br>
		<b>DODAVANJE STUDENTA NA PROJEKAT</b><br>
		<span class="napomena">*Uputa:</span> Izaberite studenta, a zatim projekat i konačno kliknite Upiši!<br>
		<?=genform("POST"); ?>
		<input type="hidden" name="subakcija" value="dodaj">
			Student: <select name="student"><?=$opcije?></select><br/>
			Projekat: <select name="projekat"><? 
			$cnt2 = 0;
			foreach($allProjects as $project) {
				$cnt2 = $cnt2 +1;
				?>
				<option value="<?=$project['id']?>"><?=$project['name']?></option>
				<?  
			}
			?></select>
			<br />
			<input name="dodaj" type="submit" value="Upiši"/>
		</form>

		<p>Za ispisivanje studenta sa projekta, koristite listu projekata.</p>
		<?

	} //akcija - dodjela_studenata



	// Akcija BRISANJE STUDENTA SA PROJEKTA

	elseif ($akcija == 'izbaci_studenta') {
		$student = intval($_REQUEST['student']);
		$projekat = intval($_REQUEST['projekat']);
		
		if ($params['locked']) {
			niceerror('Zaključane su prijave na projekte. Odjave nisu dozvoljene.');
			return;
		}
		
		if ($_REQUEST['subakcija'] == "potvrda" && check_csrf_token()) {
			$result = api_call("project/$projekat/student/$student", [], "DELETE");
			if ($_api_http_code == "204") {
				print "Student $imeprezime uspješno odjavljen sa projekta $naziv_projekta";
				zamgerlog("student u$student odjavljen sa projekta $projekat (pp$predmet)", 2);
				zamgerlog2("student odjavljen sa projekta", $student, $projekat);
			} else {
				niceerror("Neuspješno odjavljivanje studenta sa projekta ($_api_http_code): " . $result['message']);
			}
			nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');
			return;
		}
		
		foreach($allProjects as $project)
			if ($project['id'] == $projekat)
				$naziv_projekta = $project['name'];
		
		$student = api_call("person/$student");
		$imeprezime = $student['surname'] . " " . $student['name'];

		?>
		<?=genform("POST")?>
		<input type="hidden" name="subakcija" value="potvrda">
		Da li ste sigurni da želite ispisati studenta <?=$imeprezime?> sa projekta <?=$naziv_projekta?>?<br>
		<input type="submit" value="Potvrda ispisa">
		<input type="button" onclick="location.replace('<?=$linkPrefix?>');" value="Odustani">
		</form>
		<?
	}

} // function

?>