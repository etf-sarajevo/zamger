<?php



// Upis studenta na semestar

function studentska_osobe_upis() {
	global $_api_http_code, $conf_files_path, $user_siteadmin;
	
	
	// Get a list of all programmes from api - we will need it later
	$allProgrammes = api_call("programme/all", [ "resolve" => [ "ProgrammeType" ]]);
	$allProgrammes = $allProgrammes['results'];
	
	
	// Parameters, if passed
	
	$student = int_param('osoba');
	$studij = int_param('studij');
	if ($studij == 0) {
		// Passing zero means that programme should be selected manually
		foreach ($allProgrammes as $programme) {
			if ($programme['acceptsStudents']) {
				$studij = $programme['id'];
				break;
			}
		}
		$_REQUEST['change'] = true;
	}
	$semestar = int_param('semestar');
	$godina = int_param('godina');
	if (param('ponovac')) $ponovac = true; else $ponovac = false;
	if (param('status')) $status = int_param('status'); else $status = 0; /* Default status: Normal student */
	$put = int_param('put');
	$enrollment = array_to_object([ "student" => [ "id" => $student ], "Programme" => [ "id" => $studij ], "semester" => $semestar, "AcademicYear" => [ "id" => $godina ], "repeat" => $ponovac, "status" => $status, "whichTime" => $put ]);
	
	// Optional fields
	if (param('nacin_studiranja')) {
		$nacin_studiranja = int_param('nacin_studiranja');
		$enrollment->EnrollmentType = [ "id" => $nacin_studiranja ];
	} else $nacin_studiranja = false;
	if (param('plan_studija')) {
		$plan_studija = int_param('plan_studija');
		$enrollment->Curriculum = [ "id" => $plan_studija ];
	} else $plan_studija = false;
	if (param('odluka')) {
		$odluka = int_param('odluka');
		$enrollment->Decision = [ "id" => $odluka ];
	} else $odluka = false;
	
	
	// Zaduženja
	$balance = api_call("balance/$student");
	// API expects negative value for debt
	$newAmount = -floatval(param('zaduzenje'));
	if ($newAmount != floatval($balance['amount'])) {
		print "Old amount " . $balance['amount'] . " new amount $newAmount<br>\n";
		$newBalance = array_to_object( [ "Person" => [ "id" => $student], "amount" => $newAmount ] );
		$result = api_call("balance/$student", $newBalance, "PUT");
		if ($_api_http_code != "201") {
			niceerror("Neuspješna izmjena zaduženja studenta");
			api_report_bug($result, []);
			return;
		} else {
			nicemessage("Izmijenjeno zaduženje studenta");
		}
		// Show updated amount on UI
		$balance['amount'] = $newAmount;
	}
	
	// Kada je specificirana subakcija uvijek omogući upis, čak i ako nema uslov (forsiranje)
	if (param('finish')) {
		$enrollment->dryRun = false;
		$newEnrollment = api_call("enrollment/$student", $enrollment, "POST");
		if ($_api_http_code == "201") {
			$studentFullname = $newEnrollment['student']['surname'] . " " . $newEnrollment['student']['name'] . " (" . $newEnrollment['student']['studentIdNr'] . ")";
			
			nicemessage("Student $studentFullname je upisan na studij " . $newEnrollment['Programme']['name'] . ", $semestar. semestar u akademskoj " . $newEnrollment['AcademicYear']['name'] . " godini");
			
			// Enroll into courses
			foreach($newEnrollment['enrollCourses'] as $cuy) {
				$result = api_call("course/" . $cuy['CourseUnit']['id'] . "/" . $cuy['AcademicYear']['id'] . "/enroll/$student", [], "POST");
				if ($_api_http_code == "201") {
					print "* Student upisan na predmet " . $cuy['courseName'] . "<br>";
				} else if ($_api_http_code == "403") {
					print "* Student je već od ranije upisan na predmet " . $cuy['courseName'] . "<br>";
				} else {
					niceerror("Neuspješan upis studenta na predmet");
					api_report_bug($result, []);
				}
			}
			foreach($newEnrollment['failedCourses'] as $co) {
				$co['AcademicYear'] = $newEnrollment['AcademicYear'];
				$co = array_to_object($co);
				$result = api_call("course/offering/enroll/$student", $co, "PUT");
				if ($_api_http_code == "201") {
					print "* Student upisan na preneseni predmet " . $co->CourseDescription->name . "<br>";
				} else if ($_api_http_code == "403") {
					print "* Student je već od ranije upisan na preneseni predmet " . $co->CourseDescription->name . "<br>";
				} else {
					niceerror("Neuspješan upis studenta na predmet");
					api_report_bug($result, $co);
				}
			}
			
			print "<p>Završene su sve operacije u vezi upisa.</p>";
			
		} else if($_api_http_code == "400" && starts_with($newEnrollment['message'], "Already enrolled")) {
			if ($semestar % 2 == 0) $rijec = "ljetnji"; else $rijec = "zimski";
			niceerror("Student je već upisan u $rijec semestar tekuće akademske godine!");
			?>
			<p>Vratite se na <a href="?sta=studentska%2Fosobe&akcija=edit&osoba=<?=$student?>">pregled podataka o studentu</a> da saznate više informacija.</p>
			<?
			opcije_za_retroaktivni_upis($godina, $semestar);
			return;
		} else {
			niceerror("Neuspješan upis studenta na studij");
			api_report_bug($newEnrollment, $enrollment);
		}
		return;
	}
	
	// Use "dry run" enrollment to find out which options need to be changed
	$enrollment->dryRun = true;
	// If everything is ok, we allow user to finish enrollment
	$finish = true;
	// Otherwise, some options will be shown in UI
	$show = [];
	
	$newEnrollment = api_call("enrollment/$student", $enrollment, "POST");
	
	// Handle errors
	if ($_api_http_code == "400" && $newEnrollment['message'] === "Enrollment type is required") {
		// API failed to automatically detect enrollment type, we will provide a random one
		$enrollment->EnrollmentType = [ "id" => 1 ];
		$newEnrollment = api_call("enrollment/$student", $enrollment, "POST");
	}
	
	if ($_api_http_code == "400" && starts_with($newEnrollment['message'], "Already enrolled")) {
		if ($semestar % 2 == 0) $rijec = "ljetnji"; else $rijec = "zimski";
		niceerror("Student je već upisan u $rijec semestar odabrane akademske godine!");
		?>
		<p>Vratite se na <a href="?sta=studentska%2Fosobe&akcija=edit&osoba=<?=$student?>">pregled podataka o studentu</a> da saznate više informacija.</p>
		<?
		opcije_za_retroaktivni_upis($godina, $semestar);
		return;
	}
	
	else if ($_api_http_code != "201" && !strstr($newEnrollment['message'], "semesters, but")) {
		niceerror("Neuspješan upis studenta na studij");
		api_report_bug($newEnrollment, $enrollment);
		return;
	}
	
	// Programme, cycle or curriculum has changed
	if (param('change') || $studij == -1 || ($_api_http_code == "400" && strstr($newEnrollment['message'], "semesters, but")) || $newEnrollment['Programme']['id'] != $studij) {
		$show['programme'] = $show['curriculum'] = true;
		$finish = false;
		
		// Determine programme type
		$programmeTypes = [];
		foreach($allProgrammes as $prog) {
			$ptId = $prog['ProgrammeType']['id']; // shortcut
			if (!array_key_exists($ptId, $programmeTypes))
				$programmeTypes[$ptId] = $prog['ProgrammeType'];
		}
		
		
		// If semester is invalid, we will try enrollment into the next cycle
		if (strstr($newEnrollment['message'], "semesters, but")) {
			// First find old programme, cause we lost it >D
			foreach($allProgrammes as $prog) {
				if ($prog['id'] == $studij)
					$oldProgramme = $prog;
			}
			$newProgramme = false;
			foreach($allProgrammes as $prog) {
				if ($prog['Institution']['id'] == $oldProgramme['Institution']['id'] && $prog['ProgrammeType']['cycle'] == $oldProgramme['ProgrammeType']['cycle'] + 1)
					$newProgramme = $prog;
			}
			if (!$newProgramme) {
				niceerror("Neispravan broj semestra");
				opcije_za_retroaktivni_upis($godina, $semestar);
				return;
			}
			$enrollment->Programme = [ "id" => $newProgramme['id']];
			$enrollment->semester = 1;
			
			// Retry enrollment with new cycle
			$newEnrollment = api_call("enrollment/$student", $enrollment, "PUT");
			
			if ($_api_http_code == "400") {
				niceerror("Neispravan broj semestra");
				opcije_za_retroaktivni_upis($godina, $semestar);
				return; // We don't know how to continue after this
			}
		}
		
		$currentCycle = 0;
		
		?>
		<script>
			function changeCycle() {
				var cycle = document.getElementById('cycle').value;
				var progEl  = document.getElementById('programme');
				progEl.options.length = 0;
				<?
				foreach ($programmeTypes as $id => $programmeType) {
					print "if (cycle == $id) {\n";
					foreach($allProgrammes as $programme)
						if ($programme['ProgrammeType']['id'] == $id) {
							print "option = document.createElement(\"option\");\n";
							print "option.text = '" . $programme['name'] . "';\n";
							print "option.value = " . $programme['id'] . ";\n";
							if ($programme['id'] == $studij) {
								print "option.selected = true;\n";
								$currentCycle = $id;
							}
							print "progEl.add(option);";
						}
					print "}\n";
				}
				?>
			}
			function changeProgramme() {
				var curriculumEl = document.getElementById('curriculum');
				curriculumEl.options.length = 0;
				var option = document.createElement("option");
				option.text = '(odredi automatski)';
				option.value = 0;
				curriculumEl.add(option);
			}
			window.addEventListener("load", function() {
				document.getElementById('cycle').value = <?=$currentCycle?>;
				changeCycle();
			});
		</script>
		<?
	} else
		$show['programme'] = $show['curriculum'] = false;
	
	// Check if other data has changed, make further API queries if neccessary
	if (param('change') || $newEnrollment['semester'] != $semestar) {
		$show['semester'] = true;
		$finish = false;
	} else
		$show['semester'] = false;
	
	if (param('change') || $newEnrollment['repeat'] != $ponovac) {
		$show['repeat'] = true;
		$finish = false;
	} else
		$show['repeat'] = false;
	
	if (param('change') || $newEnrollment['status'] != $status) {
		if (param('forceStatus')) {
			$newEnrollment['status'] = $status;
			$show['status'] = false;
		} else {
			$show['status'] = true;
			$finish = false;
		}
	} else
		$show['status'] = false;
	
	if (param('change')) {
		$years = api_call("zamger/year")["results"];
		$show['year'] = true;
		$finish = false;
	} else
		$show['year'] = false;
	
	if (param('change')) {
		$curricula = api_call("curriculum/programme/" . $newEnrollment['Programme']['id'] . "/all", [ "resolve" => ["AcademicYear"]])["results"];
		$show['year'] = true;
		$finish = false;
	} else
		$show['year'] = false;
	
	if (param('change') || param('nacin_studiranja') && $newEnrollment['EnrollmentType']['id'] != $nacin_studiranja) {
		$enrollmentTypes = api_call("enrollment/type")["results"];
		$show['nacin_studiranja'] = true;
		$finish = false;
	} else
		$show['nacin_studiranja'] = false;
	
	if (param('change')) {
		$show['whichTime'] = true;
		$finish = false;
	} else
		$show['whichTime'] = false;
	
	
	
	// Find uplatnitza
	
	if (!param('finish')) {
		$dir = "$conf_files_path/uplatnice/" . int_param('osoba');
		$dozvoljene_ekstenzije = ["png", "jpg", "pdf"];
		
		$found = false;
		foreach($dozvoljene_ekstenzije as $ext) {
			$filename = $dir . "/uplatnica-" . int_param('godina') . ".$ext";
			if (file_exists($filename)) $found = $filename;
		}
		if ($found) {
			?>
			<p>Uplatnica:</p>
			<?
			if (ends_with($found, ".pdf")) {
				?>
				<div>
					<object
						data='?sta=common/attachment&tip=uplatnica&ag=<?=int_param('godina')?>&student=<?=int_param('osoba')?>'
						type="application/pdf"
						width="400"
						height="300">
						
						<iframe
							src='?sta=common/attachment&tip=uplatnica&ag=<?=int_param('godina')?>&student=<?=int_param('osoba')?>'
							width="400"
							height="300">
							<p>This browser does not support PDF!</p>
						</iframe>
					
					</object>
				</div>
				<?
			} else {
				?>
				<img src="?sta=common/attachment&tip=uplatnica&ag=<?=int_param('godina')?>&student=<?=int_param('osoba')?>" style="width: 100%; max-width: 400px; height: 100%; max-height: 300px">
				<?
			}
		} else {
			?>
			<p>Student nije uploadovao uplatnicu.</p>
			<?
		}
	}
	
	// Show main form
	unset($_POST['change']); unset($_REQUEST['change']); unset($_POST['ponovac']); unset($_REQUEST['ponovac']);
	?>
	<?=genform("POST");?>
		<h2>Podaci o upisu</h2>
		<style>
			table.podaci { border: 1px solid #808080; border-collapse: collapse; width: 800px }
			.podaci tr td { border: 1px solid #808080; border-collapse: collapse; border-spacing: 10px; padding: 10px }
		</style>
		<table class="podaci" id="podaciid">
			<tr>
				<td><b>Student</b></td>
				<td><?=$newEnrollment['student']['surname'] . " " . $newEnrollment['student']['name'] . " (" . $newEnrollment['student']['studentIdNr'] . ")";
					?></td>
			</tr>
			<tr>
				<td><b>Akademska godina</b></td>
				<? if ($show['year']) {
					?>
					<td bgcolor="#ffeeee">
						<select name="godina">
							<?
							foreach ($years as $id => $academicYear) {
								print "<option value=\"$id\" ";
								if ($id == $newEnrollment['AcademicYear']['id']) print "SELECTED";
								print ">" . $academicYear['name'] . "</option>";
							}
							?>
						</select>
					</td>
					<?
				} else {
					?><td><?=$newEnrollment['AcademicYear']['name']?> <?
					if (!$newEnrollment['AcademicYear']['isCurrent'])
						print "- <font color='red'>Godina nije aktuelna</font>";
					?></td><?
				}?>
			</tr>
			<tr>
				<td><b>Ciklus studija</b></td>
				<? if ($show['programme']) {
					?>
					<td bgcolor="#ffeeee">
						<select name="ciklus" id="cycle" onchange="changeCycle();">
							<?
							foreach ($programmeTypes as $id => $programmeType)
								print "<option value=$id>" . $programmeType['name'] . "</option>";
							?>
						</select>
					</td>
					<?
				} else {
					?>
					<td>
					<?=$newEnrollment['Programme']['ProgrammeType']['name']?>
					<input type="hidden" name="ciklus" value="<?=$newEnrollment['Programme']['ProgrammeType']['id']?>">
					</td><?
				}?>
			</tr>
			<tr>
				<td><b>Studij</b></td>
				<? if ($show['programme']) {
					?>
					<td bgcolor="#ffeeee">
						<select name="studij" id="programme" onchange="changeProgramme();">
						</select>
					</td>
					<?
				} else {
					?>
					<td>
					<?=$newEnrollment['Programme']['name']?>
					<input type="hidden" name="studij" value="<?=$newEnrollment['Programme']['id']?>">
					</td><?
				}?>
			</tr>
			<tr>
				<td><b>Plan studija</b></td>
				<? if ($show['curriculum']) {
					?>
					<td bgcolor="#ffeeee">
					<select name="plan_studija" id="curriculum">
						<option value="0" selected>(odredi automatski)</option>
						<?
						// Not implemented currently
						foreach($curricula as $cur) {
							print "<option value=\"" . $cur['id'] . "\">" . $cur['startingYear']['name'] . "</option>";
						}
						?>
					</select></td><?
				} else {
					?>
					<td>
					<?=$newEnrollment['Curriculum']['startingYear']['name'];?>
					<input type="hidden" name="plan_studija" value="<?=$newEnrollment['Curriculum']['id']?>">
					</td><?
				}?>
			</tr>
			<tr>
				<td><b>Semestar</b></td>
				<? if ($show['semester']) {
					?>
					<td bgcolor="#ffeeee">
						<select name="semestar">
							<?
							for ($i=1; $i<=6; $i++) {
								print "<option value=$i";
								if ($i == $newEnrollment['semester']) print " selected";
								print ">$i</option>\n";
							}
							?>
						</select>
					</td>
					<?
				} else {
					?>
					<td>
					<?=$newEnrollment['semester'];?>
					<input type="hidden" name="semestar" value="<?=$newEnrollment['semester']?>">
					</td><?
				}?>
			</tr>
			<tr>
				<td><b>Koji put upisuje</b></td>
				<? if ($show['whichTime']) {
					?>
					<td bgcolor="#ffeeee">
						<select name="put">
							<?
							for ($i=1; $i<=20; $i++) {
								print "<option value=$i";
								if ($i == $newEnrollment['whichTime']) print " selected";
								print ">$i</option>\n";
							}
							?>
						</select>
					</td>
					<?
				} else {
					?>
					<td>
					<?=$newEnrollment['whichTime'];?>
					<input type="hidden" name="put" value="<?=$newEnrollment['whichTime']?>">
					</td><?
				}?>
			</tr>
			<tr>
				<td><b>Ponovac</b></td>
				<? if ($show['repeat']) {
					?>
					<td bgcolor="#ffeeee">
						<input type="checkbox" name="ponovac" <? if ($newEnrollment['repeat']) print "checked";?>>
					</td>
					<?
				} else {
					if ($newEnrollment['repeat']) {
						print "<td>DA";
						?>
						<input type="hidden" name="ponovac" value="1"></td><?
					} else
						print "<td>NE</td>";
				}?>
			</tr>
			<tr>
				<td><b>Apsolvent</b></td>
				<? if ($show['status']) {
					?>
					<td bgcolor="#ffeeee">
						<input type="checkbox" name="status" value="1">
						<input type="hidden" name="forceStatus" value="1">
					</td>
					<?
				} else {
					?>
					<td><?
					if ($newEnrollment['status'] == 1) print "DA"; else print "NE";
					?>
					<input type="hidden" name="status" value="<?=$newEnrollment['status']?>">
					</td><?
				}?>
			</tr>
			<tr>
				<td><b>Način studiranja</b></td>
				<? if ($show['nacin_studiranja']) {
					?>
					<td bgcolor="#ffeeee">
						<select name="nacin_studiranja">
							<?
							foreach ($enrollmentTypes as $type) {
								print "<option value=\"" . $type['id'] . "\"";
								if ($type['id'] == $newEnrollment['EnrollmentType']['id']) print "SELECTED";
								print ">" . $type['name'] . "</option>";
							}
							?>
						</select>
					</td>
					<?
				} else {
					?>
					<td>
					<?=$newEnrollment['EnrollmentType']['name'];?>
					<input type="hidden" name="nacin_studiranja" value="<?=$newEnrollment['EnrollmentType']['id']?>">
					</td><?
				}?>
			</tr>
			<tr>
				<td><b>Zaduženje</b></td>
				<td><input type="text" name="zaduzenje" value="<?=-$balance['amount']?>"> KM</td>
			</tr>
			<?
			if (!$newEnrollment['canEnroll']) {
				?>
				<tr>
					<td>&nbsp;</td>
					<td bgcolor="#ffeeee">
						<p><b>Student ne ispunjava uslove za upis</b></p>
						<p>Nepoloženi predmeti:</p>
						<ul>
							<?
							$totalECTS = 0;
							if (is_array($newEnrollment['failedCourses'])) foreach ($newEnrollment['failedCourses'] as $co) {
								print "<li>" . $co['CourseDescription']['name'] . " (" . $co['CourseDescription']['ects'] . " ECTS)</li>\n";
								$totalECTS += $co['CourseDescription']['ects'];
							}
							?>
						</ul>
						<p>Ukupno <?=count($newEnrollment['failedCourses'])?> predmeta (<?=$totalECTS?> ECTS)</p>
					</td>
				</tr>
				<?
			}
			?>
			<tr>
				<td>&nbsp;</td>
				<td>
					<input type="submit" name="change" value="Promijeni sve podatke">
					<?
					if (!$finish) {
						?><input type="submit" name="confirm" value="Potvrdi izmjene"><?
						//print_r($show);
					}
					if ($finish || $user_siteadmin) {
						?><input type="submit" name="finish" value="Upiši studenta"><?
					}
					?>
				</td>
			</tr>
		</table>
		</form>
	<?
}

function opcije_za_retroaktivni_upis($academicYearId, $semester) {
	?>
	<p>Upišite studenta retroaktivno na:</p>
	<?=genform("POST");?>
	Akademska godina: <select name="godina"><?
		$years = api_call("zamger/year")["results"];
		foreach ($years as $id => $academicYear) {
			print "<option value=\"$id\" ";
			if ($id == $academicYearId) print "SELECTED";
			print ">" . $academicYear['name'] . "</option>";
		}
		?></select><br>
	Semestar: <select name="semestar">
		<?
		for ($i=1; $i<=8; $i++) {
			print "<option value=$i";
			if ($i == $semester) print " selected";
			print ">$i</option>\n";
		}
		?>
	</select><br>
	<input type="submit" value="Kreni"></form>
	<?
}
