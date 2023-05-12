<?php

// NASTAVNIK/ZAVRSNI - nastavnički interfejs za definisanje tema završnih radova



function nastavnik_zavrsni() {

	global $userid, $_api_http_code;

	require_once("lib/legacy.php"); // mb_substr

	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);

	// Da li korisnik ima pravo ući u modul?
	$course = api_call("course/$predmet/$ag");
	if ($_api_http_code == "403") {
		zamgerlog("nastavnik/zadace privilegije (predmet pp$predmet)",3);
		zamgerlog2("nije nastavnik na predmetu", $predmet, $ag);
		biguglyerror("Nemate pravo pristupa ovoj opciji");
		return;
	}

	$linkPrefix = "?sta=nastavnik/zavrsni&predmet=$predmet&ag=$ag";
	$akcija = $_REQUEST['akcija'];
	$id = intval($_REQUEST['id']);
	
	$predmet_naziv = "Završni rad";
	if (!$course['AcademicYear']['isCurrent']) $predmet_naziv .= " (" . $course['AcademicYear']['name'] . ")";
	
	?>
	<h2><?=$predmet_naziv?></h2>
	<?

	
	// Default akcija - LISTA ZAVRSNIH RADOVA
	if (!isset($akcija)) {
		?>
		<h3>Lista tema završnih radova</h3>

		<p>Teme koje ste ponudili ili ste imenovani za mentora:</p>
		<?

		$theses = api_call("thesis/forMenthor/$userid", [ "course" => $predmet, "year" => $ag, "resolve" => [ "Person" ] ])["results"];
		if (count($theses) == 0) {
			?>
			<span class="notice">Nije definisana niti jedna tema.</span>	
			<?
		} else {

			?>
			<table border="1" cellspacing="0" cellpadding="4">
				<tr bgcolor="#CCCCCC">
					<td>R.br.</td>
					<td>Naslov</td>
					<td>Mentor</td>
					<td>Student</td>
					<td>Predsjednik komisije</td>
					<td>Član komisije</td>
					<td>Termin odbrane</td>
					<td>Konačna ocjena</td>
					<td>Akcije</td>
				</tr>
			<?

			$rbr = 0;
			$nema = "<font color=\"gray\">(nije definisan)</font>";
			foreach($theses as $thesis) {
				$rbr++;
				$id_zavrsni = $thesis['id'];
				$naslov_teme = $thesis['title'];

				$kratki_pregled = $thesis['description'];
				if ($kratki_pregled == "")
					$kratki_pregled = $nema;
				else
					$kratki_pregled = substr($kratki_pregled, 0, 200)."...";
				
				$mentori = "";
				foreach($thesis['menthors'] as $menthor) {
					if ($mentori != "") $mentori .= "<br>\n";
					$mentori .= tituliraj_api($menthor, false);
				}
				if ($mentori=="") $mentori = "<font color=\"red\">(nije definisan)</font>";

				$student_id = $thesis['candidate']['id'];
				if ($student_id > 0) {
					$student = tituliraj_api($thesis['candidate'], false);
					if (!$thesis['candidateApproved']) // Kandidat nije potvrđen
						$student .= "<br>(<a href=\"$linkPrefix&akcija=potvrdi_kandidata&id=$id_zavrsni\">potvrdi kandidata</a>)";
				} else {
					$student = "<font color=\"gray\">niko nije izabrao temu</font>";
				}

				if ($thesis['committeeChair']['id'] > 0)
					$predsjednik_komisije = tituliraj_api($thesis['committeeChair'], false);
				else
					$predsjednik_komisije = $nema;
				
				$clan_komisije = "";
				foreach($thesis['committeeMembers'] as $member) {
					if ($clan_komisije != "") $clan_komisije .= "<br>\n";
					$clan_komisije .= tituliraj_api($member, false);
				}
				if ($clan_komisije=="") $clan_komisije = $nema;

				$termin_odbrane = date("d.m.Y h:i", db_timestamp($thesis['presentationDateTime']));
				if (!$thesis['presentationDateTime']) $termin_odbrane = $nema;

				$konacna_ocjena = "<font color=\"gray\">(nije ocijenjen)</font>";
				if ($student_id > 0) {
					$pf = api_call("course/$predmet/student/$student_id", [ "year" => $ag, "score" => true ] );
					$ocjena = $pf['grade'];
					if ($ocjena > 5) {
						$konacna_ocjena = $ocjena;
						if ($konacna_ocjena == 12)
							$konacna_ocjena = "Uspješno odbranio";
					}
				}

				?>
				<tr>
					<td><?=$rbr?>.</td>
					<td><?=$naslov_teme?></td>
					<td><?=$mentori?></td>
					<td><?=$student?></td>
					<td><?=$predsjednik_komisije?></td>
					<td><?=$clan_komisije?></td>
					<td><?=$termin_odbrane?></td>
					<td><?=$konacna_ocjena?></td>
					<td><a href="?sta=nastavnik/zavrsni&akcija=izmjena&id=<?=$id_zavrsni?>&predmet=<?=$predmet?>&ag=<?=$ag?>">izmijeni</a> *
					<a href="?sta=nastavnik/zavrsni&akcija=zavrsni_stranica&zavrsni=<?=$id_zavrsni?>&predmet=<?=$predmet?>&ag=<?=$ag?>">stranica</a> *
					<a href="?sta=nastavnik/zavrsni&akcija=dodaj_biljesku&id=<?=$id_zavrsni?>&predmet=<?=$predmet?>&ag=<?=$ag?>">bilješka</a>
					</td>
				</tr>
				<?
			} // while

			?>
			</table>
			<?
		} // else

		?>
		<p><a href="?sta=nastavnik/zavrsni&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>&amp;akcija=izmjena">Predložite novu temu</a></p>
		
		<?
		
		// Ponavljanje tema od prošle godine
		
		$theses = api_call("thesis/forMenthor/$userid", [ "course" => $predmet, "year" => $ag-1, "resolve" => [ "Person" ] ])["results"];
		for ($i=0; $i<count($theses); $i++)
			if (!$theses[$i]['candidate']['id'])
				unset($theses[$i]);
		if (count($theses) > 0) {
			?>
			<p><b>Ponavljanje teme od prošle godine:</b></p>
			<?=genform("POST")?>
			<input type="hidden" name="akcija" value="ponovi_temu">
			<select name="id_teme">
			<?
			foreach($theses as $thesis) {
				$naslov = $thesis['title'];
				if (strlen($naslov)>50) $naslov = mb_substr($naslov, 0, 40) . "...";
				?>
				<option value="<?=$thesis['id']?>">(<?=$thesis['candidate']['surname']?> <?=$thesis['candidate']['name']?>) <?=$naslov?></option>
				<?
			}
			?>
			</select>
			<input type="submit" value=" Ponovi temu ">
			</form>
			<?
		}
	} // if (!isset($akcija) - lista završnih radova
	
	//Otvaranje stranica zavrsnih radova
	elseif ($akcija == 'zavrsni_stranica') {
		?> <p><a href="<?=$linkPrefix?>">Nazad na spisak tema</a></p> <?
		require_once ('common/zavrsniStrane.php');
		common_zavrsniStrane();
	} //akcija == zavrsni_stranica

	elseif ($akcija == 'ponovi_temu') {
		$zavrsni = intval($_REQUEST['id_teme']);
		$thesis = api_call("thesis/$zavrsni", [ "resolve" => [ "Person" ]]);
		if ($_api_http_code != "200") {
			niceerror("Završni rad nije sa ovog predmeta");
			zamgerlog("spoofing zavrsnog rada $zavrsni", 3);
			api_report_bug($thesis, []);
			return;
		}
		
		$newthesis = array_to_object($thesis);
		$newthesis->menthors = $newthesis->committeeMembers = [];
		foreach($thesis['menthors'] as $menthor)
			$newthesis->menthors[] = array_to_object($menthor);
		foreach($thesis['committeeMembers'] as $menthor)
			$newthesis->committeeMembers[] = array_to_object($menthor);
		
		$result = api_call("thesis/course/$predmet/$ag", $newthesis, "POST");
		if ($_api_http_code == "201") {
			nicemessage('Kopirana tema od prošle godine');
		} else {
			niceerror("Kopiranje teme nije uspjelo");
			api_report_bug($result, $thesis);
		}
		nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');

		return;
	}
	
	//akcija IZMJENA TEME ZAVRŠNOG RADA
	elseif ($akcija == 'izmjena') {
		if ($_REQUEST['subakcija'] == "potvrda" && check_csrf_token()) {
			// Construct thesis object
			$thesis = array_to_object([
				"CourseUnit" => [ "id" => $predmet ],
				"AcademicYear" => [ "id" => $ag ],
				"title" => $_REQUEST['naslov'],
				"titleEn" => $_REQUEST['naslov_en'],
				"subtitle" => $_REQUEST['podnaslov'],
				"description" => $_REQUEST['kratki_pregled'],
				"literature" => $_REQUEST['literatura'],
				"candidate" => [ "id" => $_REQUEST['kandidat'] ],
				"thesisCourseUnit" => [ "id" => $_REQUEST['na_predmetu'] ],
				"committeeChair" => [ "id" => $_REQUEST['predkom'] ],
				"thesisApproved" => false,
				"candidateApproved" => false
			]);
			
			
			// Construct decision objects (if neccessary)
			
			$thesisDecision = [ "id" => 0 ];
			if ($_REQUEST['datum_odluke_tema'] != "") {
				if (preg_match("/(\d+).*?(\d+).*?(\d+)/", $_REQUEST['datum_odluke_tema'], $matches)) {
					$dan=$matches[1]; $mjesec=$matches[2]; $godina=$matches[3];
					if (!checkdate($mjesec,$dan,$godina)) {
						niceerror("Datum za odluku o odobrenju teme je kalendarski nemoguć ($dan. $mjesec. $godina)");
						nicemessage('<a href="javascript:history.back();">Povratak.</a>');
						return;
					}
					$thesisDecision['date'] = "$godina-$mjesec-$dan";
				} else {
					niceerror("Datum za odluku o odobrenju teme nije u ispravnom formatu.");
					print "Potrebno je koristiti format: DD. MM. GGGG.<br>";
					nicemessage('<a href="javascript:history.back();">Povratak.</a>');
					return;
				}
				if ($_REQUEST['broj_odluke_tema'] == "") {
					niceerror("Unijeli ste datum odluke o odobrenju teme a niste unijeli broj odluke!");
					nicemessage('<a href="javascript:history.back();">Povratak.</a>');
					return;
				}
				$thesisDecision['protocolNumber'] = $_REQUEST['broj_odluke_tema'];
				$thesisDecision['Institution'] = [ "id" => 1 ]; // FIXME
				$thesisDecision['Person'] = [ "id" => $_REQUEST['kandidat'] ];
				$result = api_call("zamger/decision", array_to_object($thesisDecision), "POST");
				if ($_api_http_code != "201") {
					niceerror("Neuspješno dodavanje odluke o odobrenju teme");
					api_report_bug($result, $thesisDecision);
					nicemessage('<a href="javascript:history.back();">Povratak.</a>');
					return;
				}
				
				$thesisDecision = array_to_object($result);
				
				// Decision on thesis means thesis is approved
				$thesis->thesisApproved = true;
			} else $thesisDecision = array_to_object($thesisDecision);
			
			
			$committeeDecision = [ "id" => 0 ];
			if ($_REQUEST['datum_odluke_komisija'] != "") {
				if (preg_match("/(\d+).*?(\d+).*?(\d+)/", $_REQUEST['datum_odluke_komisija'], $matches)) {
					$dan=$matches[1]; $mjesec=$matches[2]; $godina=$matches[3];
					if (!checkdate($mjesec,$dan,$godina)) {
						niceerror("Datum za odluku o imenovanju komisije je kalendarski nemoguć ($dan. $mjesec. $godina)");
						nicemessage('<a href="javascript:history.back();">Povratak.</a>');
						return;
					}
					$committeeDecision['date'] = "$godina-$mjesec-$dan";
				} else {
					niceerror("Datum za odluku o imenovanju komisije nije u ispravnom formatu.");
					print "Potrebno je koristiti format: DD. MM. GGGG.<br>";
					nicemessage('<a href="javascript:history.back();">Povratak.</a>');
					return;
				}
				if ($_REQUEST['broj_odluke_komisija'] == "") {
					niceerror("Unijeli ste datum odluke o imenovanju komisije a niste unijeli broj odluke!");
					nicemessage('<a href="javascript:history.back();">Povratak.</a>');
					return;
				}
				$committeeDecision['protocolNumber'] = $_REQUEST['broj_odluke_komisija'];
				$committeeDecision['Institution'] = [ "id" => 1 ]; // FIXME
				$committeeDecision['Person'] = [ "id" => $_REQUEST['kandidat'] ];
				$result = api_call("zamger/decision", array_to_object($committeeDecision), "POST");
				if ($_api_http_code != "201") {
					niceerror("Neuspješno dodavanje odluke o imenovanju komisije");
					api_report_bug($result, $committeeDecision);
					nicemessage('<a href="javascript:history.back();">Povratak.</a>');
					return;
				}
				
				$committeeDecision = array_to_object($result);
				
				// Decision on committee means candidate is approved
				$thesis->candidateApproved = true;
				
			} else $committeeDecision = array_to_object($committeeDecision);
			
			$thesis->committeeDecision = $committeeDecision;
			$thesis->thesisDecision = $thesisDecision;
			$thesis->committeeMembers = [];
			$id_clankom = intval($_REQUEST['clankom']);
			if ($id_clankom > 0)
				$thesis->committeeMembers[] = array_to_object([ "id" => $id_clankom]);
			$thesis->menthors = [];
			
			if (empty($_REQUEST['naslov']) || empty($_REQUEST['naslov_en'])) {
				niceerror('Unesite sva obavezna polja.');
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}

			if ($id > 0) {
				// Copy menthors from old thesis object
				$oldThesis = api_call("thesis/$id");
				if ($_api_http_code != 200) {
					niceerror("Pogrešan ID završnog rada");
					api_report_bug($oldThesis, []);
					nicemessage('<a href="javascript:history.back();">Povratak.</a>');
					return;
				}
				foreach($oldThesis['menthors'] as $menthor)
					$thesis->menthors[] = array_to_object($menthor);
				
				if ($thesis->candidate->id == 0)
					$thesis->candidateApproved = false;
				else if ($thesis->candidate->id != $oldThesis['candidate']['id'])
					$thesis->candidateApproved = true; // If menthor changed the candidate, then he approved the candidate
				else
					$thesis->candidateApproved = $oldThesis['candidateApproved'];
				$thesis->thesisApproved = $oldThesis['thesisApproved'];
					
				$result = api_call("thesis/$id", $thesis, "PUT");
				if ($_api_http_code == "201") {
					nicemessage('Podaci o završnom radu uspješno izmijenjeni.');
					zamgerlog("izmijenjena tema zavrsnog rada $id na predmetu pp$predmet", 2);
					zamgerlog2("izmijenio temu zavrsnog rada", $id);
				} else {
					niceerror("Greška prilikom izmjene završnog rada");
					api_report_bug($result, $thesis);
				}
			} else {
				$thesis->menthors[] = array_to_object( [ "id" => $userid ] );
				
				$result = api_call("thesis/course/$predmet/$ag", $thesis, "POST");
				if ($_api_http_code == "201") {
					$id = $result['id'];
					nicemessage('Uspješno kreirana nova tema završnog rada.');
					zamgerlog("kreirana tema zavrsnog rada $id na predmetu pp$predmet", 2);
					zamgerlog2("dodao temu zavrsnog rada", $id);
				} else {
					niceerror("Greška prilikom dodavanja završnog rada");
					api_report_bug($result, $thesis);
				}
			}
			nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');

			return;
		}
		
		
		if ($id > 0) {
			$thesis = api_call("thesis/$id", [ "resolve" => [ "Decision" ] ] );
			if ($_api_http_code == "404") {
				niceerror("Nepostojeći završni rad");
				zamgerlog("spoofing zavrsnog rada $id kod izmjene teme", 3);
				zamgerlog2("id zavrsnog rada i predmeta se ne poklapaju", $id, $predmet, $ag);
				return;
			} else if ($_api_http_code != "200") {
				niceerror("Greška prilikom preuzimanja teme završnog rada");
				api_report_bug($thesis, []);
				return;
			}

			?>	
			<h3>Izmjena teme završnog rada</h3>
			<?
		} else {
			$thesis = [ "title" => "", "titleEn" => "", "subtitle" => "", "description" => "", "literature" => "", "thesisDecision" => [ "id" => 0 ], "committeeDecision" => [ "id" => 0 ] ];
			?>	
			<h3>Nova tema završnog rada</h3>
			<?
		}

		// Spisak predmeta na kojima je osoba odg. nastavnik iz kojih može biti predmet
		$courses = api_call("course/teacher/$userid", [ "resolve" => ["CourseUnit", "Institution" ] ])["results"];
		$pronadjen = false;
		$prof_predmeti = "<option value=0>(nije definisan)</option>\n";
		foreach($courses as $course) {
			if (substr($course['courseName'], 0, 12) == "Završni rad") continue;
			if ($course['CourseUnit']['id'] == $thesis['thesisCourseUnit']['id']) {
				$prof_predmeti .= "<option value=\"" . $course['CourseUnit']['id'] . "\" selected>";
				$pronadjen = true;
			} else
				$prof_predmeti .= "<option value=\"" . $course['CourseUnit']['id'] . "\">";
			$prof_predmeti .= $course['courseName'] . " (" . $course['CourseUnit']['Institution']['abbrev'] . ")</option>\n";
		}
		if ($thesis['thesisCourseUnit']['id'] != 0 && $pronadjen == false) {
			// Ako je ranije već izabran predmet kojeg nema na spisku, dodaćemo ga na spisak
			$course = api_call("course/$na_predmetu", [ "resolve" => ["CourseUnit", "Institution" ] ]);
			$prof_predmeti .= "<option value=\"$na_predmetu\" selected>" . $course['courseName'] . " (" . $course['CourseUnit']['Institution']['abbrev'] . ")</option>\n";
		}
		
		// Spisak studenata na predmetu Završni rad
		$allStudents = api_call("group/course/$predmet/allStudentsSimple", [ "year" => $ag ] )["results"];
		usort($allStudents, function($s1, $s2) {
			if ($s1['surname'] == $s2['surname'])
				return strcasecmp($s1['name'], $s2['name']);
			return strcasecmp($s1['surname'], $s2['surname']);
		});
		
		$studenti_ispis = "<option value=0>(nije definisan)</option>\n";
		$cnt5 = 0;
		foreach($allStudents as $student) {
			$cnt5 = $cnt5 + 1;
			if ($student['id'] == $id_studenta) $opcija = " SELECTED";
			else $opcija = "";
			
			$studenti_ispis .= "<option value=\"" . $student['id'] . "\" $opcija>" . $student['surname'] . " " . $student['name'] . " (" . $student['studentIdNr'] . ")</option>\n";
		}
		
		// Spisak potencijalnih članova komisije
		$profesori = array();
		$course = api_call("course/$predmet", [ "year" => $ag ]);
		foreach($course['staff'] as $staff) {
			if ($staff['status_id'] == 1 || $staff['status_id'] == 2) {
				$profesori[$staff['Person']['id']] = $staff['Person']['surname'] . " " . $staff['Person']['name'];
			}
		}
		
		$broj_odluke_tema = $datum_odluke_tema = "";
		if ($thesis['thesisDecision']['id'] > 0) {
			$broj_odluke_tema = $thesis['thesisDecision']['protocolNumber'];
			$datum_odluke_tema = date("d. m. Y", db_timestamp($thesis['thesisDecision']['date']));
		}
		
		$broj_odluke_komisija = $datum_odluke_komisija = "";
		if ($thesis['committeeDecision']['id'] > 0) {
			$broj_odluke_komisija = $thesis['committeeDecision']['protocolNumber'];
			$datum_odluke_komisija = date("d. m. Y", db_timestamp($thesis['committeeDecision']['date']));
		}
		
		// TODO jedan član komisije!
		if (count($thesis['committeeMembers']) > 0)
			$id_clankom = $thesis['committeeMembers'][0]['id'];

		?>
		<p><a href="<?=$linkPrefix?>">Nazad na spisak tema</a></p>

		<style>
		label{
			display: block;
			margin: 0px 0px 15px 0px;
		}
		label > span {
			width: 150px;
			font-weight: bold;
			float: left;
			padding-top: 8px;
			padding-right: 5px;
		}
		</style>

		<?=genform("POST", "addForm");?>
			<input type="hidden" name="subakcija" value="potvrda">
			<label for="naslov"><span>Naslov teme: <font color="red">*</font></span> <input name="naslov" type="text" id="naslov" size="70" value="<?=$thesis['title']?>"></label>
			<label for="naslov_en"><span>Naslov teme (engleski): <font color="red">*</font></span> <input name="naslov_en" type="text" id="naslov_en" size="70" value="<?=$thesis['titleEn']?>"></label>
			<label for="podnaslov"><span>Podnaslov:</span> <input name="podnaslov" type="text" id="podnaslov" size="70" value="<?=$thesis['subtitle']?>"></label>
			<label for="predmet"><span>Predmet:</span> <select name="na_predmetu"><?=$prof_predmeti?></select></label>  
			<label for="kandidat"><span>Kandidat:</span> <select name="kandidat"><?=$studenti_ispis?></select></label>  
			<label for="predkom"><span>Predsjednik komisije:</span> <select name="predkom"><option value=0>(nije definisan)</option><?php 
				foreach($profesori as $idprof => $imeprof) {
					if ($idprof == $userid || $idprof == $id_clankom) continue;
					print "<option value=\"$idprof\"";
					if ($idprof == $thesis['committeeChair']['id']) print " SELECTED";
					print ">$imeprof</option>\n";
				}
			?></select></label>  
			<label for="clankom"><span>Član komisije:</span> <select name="clankom"><option value=0>(nije definisan)</option><?php 
				foreach($profesori as $idprof => $imeprof) {
					if ($idprof == $userid || $idprof == $thesis['committeeChair']['id']) continue;
					print "<option value=\"$idprof\"";
					if ($idprof == $id_clankom) print " SELECTED";
					print ">$imeprof</option>\n";
				}
			?></select></label>  
			<label for="broj_odluke_tema"><span>Tema odobrena odlukom broj</span> <input name="broj_odluke_tema" type="text" id="broj_odluke" size="20" value="<?=$broj_odluke_tema?>">
						datum Vijeća
						<input name="datum_odluke_tema" type="text" id="datum_odluke" size="20" value="<?=$datum_odluke_tema?>"></label>  
			<label for="broj_odluke_komisija"><span>Komisija imenovana odlukom broj</span> <input name="broj_odluke_komisija" type="text" id="broj_odluke_komisija" size="20" value="<?=$broj_odluke_komisija?>">
						od datuma
						<input name="datum_odluke_komisija" type="text" id="datum_odluke_komisija" size="20" value="<?=$datum_odluke_komisija?>"></label>  
			<label for="kratki_pregled"><span>Kratki pregled:</span>
			<textarea name="kratki_pregled" cols="60" rows="10" id="kratki_pregled"><?=htmlentities($thesis['description'])?></textarea></label>
			<label for="literatura"><span>Preporučena literatura:</span>
			<textarea name="literatura" cols="60" rows="15" id="literatura"><?=htmlentities($thesis['literature'])?></textarea></label>
			<label><span>&nbsp;</span> <input type="submit" id="submit" value="Potvrdi"> <input type="button" id="nazad" value="Nazad" onclick="javascript:history.go(-1)"></label>
		</form>
		
		<p><font color="red">*</font> Polja su obavezna</p>
		<?
	} //akcija == izmjena_zavrsni
	
	// Akcija DODAJ BILJEŠKU
	elseif ($akcija == 'dodaj_biljesku') {
		$thesis = api_call("thesis/$id" );
		if ($_api_http_code == "404") {
			niceerror("Nepostojeći završni rad");
			zamgerlog("spoofing zavrsnog rada $id kod izmjene teme", 3);
			zamgerlog2("id zavrsnog rada i predmeta se ne poklapaju", $id, $predmet, $ag);
			return;
		} else if ($_api_http_code != "200") {
			niceerror("Greška prilikom preuzimanja teme završnog rada");
			api_report_bug($thesis, []);
			return;
		}

		if ($_REQUEST['subakcija'] == "potvrda" && check_csrf_token()) {
			// Poslana forma za dodavanje bilješke
			$thesis['note'] = $_REQUEST['biljeska'];
			
			$newthesis = array_to_object($thesis);
			$newthesis->menthors = $newthesis->committeeMembers = [];
			foreach($thesis['menthors'] as $menthor)
				$newthesis->menthors[] = array_to_object($menthor);
			foreach($thesis['committeeMembers'] as $menthor)
				$newthesis->committeeMembers[] = array_to_object($menthor);
			
			$result = api_call("thesis/$id", $newthesis, "PUT");
			if ($_api_http_code == "201") {
				nicemessage('Uspješno ste dodali bilješku.');
				zamgerlog("dodao biljesku na zavrsni rad $id", 2);
				zamgerlog2("dodao biljesku na zavrsni rad", $id);
			} else {
				niceerror("Greška prilikom izmjene završnog rada");
				api_report_bug($result, $thesis);
			}
			nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');
			return; 
		}

		// Forma za izmjenu/dodavanje bilješke
		?>
		<h3>Bilješka na završni rad: <?=htmlentities($thesis['title'])?></h3>
		<p>Ovdje možete ostaviti bilješku koja je samo vama vidljiva.</p>
		<p><a href="<?=$linkPrefix?>">Nazad na spisak tema</a></p>
		<?=genform('POST','addNote'); ?>
		<input type="hidden" name="subakcija" value="potvrda">
			<div class="row">
				<span class="label">Bilješka:</span>
				<span class="formw"><textarea name="biljeska" cols="60" rows="15" id="opis"><?=htmlentities($thesis['note'])?></textarea></span>
			</div> 
					
			<div class="row">	
				<span class="formw" style="margin-left:150px;"><input type="submit" id="submit" value="Potvrdi"/></span>
			</div>
		</form>
		<?
	} //akcija == dodaj biljesku

	elseif ($akcija == 'potvrdi_kandidata') {
		$thesis = api_call("thesis/$id" );
		if ($_api_http_code == "404") {
			niceerror("Nepostojeći završni rad");
			zamgerlog("spoofing zavrsnog rada $id kod izmjene teme", 3);
			zamgerlog2("id zavrsnog rada i predmeta se ne poklapaju", $id, $predmet, $ag);
			return;
		} else if ($_api_http_code != "200") {
			niceerror("Greška prilikom preuzimanja teme završnog rada");
			api_report_bug($thesis, []);
			return;
		}
		
		if ($thesis['candidate']['id'] == 0) {
			niceerror("Nije definisan kandidat za ovaj rad");
			zamgerlog("spoofing zavrsnog rada $id kod potvrde kandidata", 3);
			zamgerlog2("id zavrsnog rada i predmeta se ne poklapaju", $id, $predmet, $ag);
			return;
		} else {
			$thesis['candidateApproved'] = true;
			$newthesis = array_to_object($thesis);
			$newthesis->menthors = $newthesis->committeeMembers = [];
			foreach($thesis['menthors'] as $menthor)
				$newthesis->menthors[] = array_to_object($menthor);
			foreach($thesis['committeeMembers'] as $menthor)
				$newthesis->committeeMembers[] = array_to_object($menthor);
			$result = api_call("thesis/$id", $newthesis, "PUT");
			if ($_api_http_code == "201") {
				?>
				<script>window.location = '<?=$linkPrefix?>';</script>
				<?
			} else {
				niceerror("Greška prilikom izmjene završnog rada");
				api_report_bug($result, $thesis);
			}
		}
	} //akcija == potvrdi_kandidata
} // function
?>
