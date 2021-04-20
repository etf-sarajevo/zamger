<?

// IZVJESTAJ/TERMINI_ISPITA - spiskovi studenata za termine ispita sa bodovima i ostalim podacima



function izvjestaj_termini_ispita() {

	global $_api_http_code;
	
	require_once("lib/utility.php"); // procenat, bssort
	
	
	?>
	
	<p>Univerzitet u Sarajevu<br/>
	Elektrotehnički fakultet Sarajevo</p>
	<p>Datum i vrijeme izvještaja: <?=date("d. m. Y. H:i");?></p>
	<?

	// Parameters
	if (isset($_REQUEST['termin'])) {
		$termin_id = int_param('termin');
		$event = api_call("event/$termin_id", [ "resolve" => ["CourseActivity", "AcademicYear", "Group" ], "portfolios" => true ] );
		$events = [ $event ];
		$ispit = $event['options'];
		$this_url = "?sta=izvjestaj/termini_ispita&termin=$termin_id&predmet_naziv=" . urlencode($predmet_naziv);
	} else if (isset($_REQUEST['ispit'])) {
		$ispit = int_param('ispit');
		$events = api_call("event/exam/$ispit", [ "resolve" => ["CourseActivity", "AcademicYear", "Group" ], "portfolios" => true ] )["results"];
		$this_url = "?sta=izvjestaj/termini_ispita&ispit=$ispit&predmet_naziv=" . urlencode($predmet_naziv);
	} else {
		biguglyerror("Neispravni parametri izvještaja");
		return;
	}
	
	if ($_api_http_code == "403") {
		biguglyerror("Nemate permisije za pristup ovom izvještaju");
		zamgerlog ("nije admin predmeta pp$predmet godina ag$ag",3); // 3 = error
		return;
	} else if ($_api_http_code != "200") {
		niceerror("Greška prilikom pristupa ispitnom terminu");
		if (isset($_REQUEST['termin']))
			api_report_bug($event, []);
		else
			api_report_bug($events, []);
		return;
	}
	
	// General information
	$predmet = $events[0]['CourseUnit']['id'];
	$ag = $events[0]['AcademicYear']['id'];
	$ag_naziv = $events[0]['AcademicYear']['name'];
	
	if ($ispit > 0) {
		$exam = api_call("exam/$ispit", [ "resolve" => ["CourseActivity"] ] );
		$naziv = $exam['CourseActivity']['name'];;
		$finidatum = date ("d. m. Y.", db_timestamp($exam['date']));
	} else {
		// Use details from first event
		$naziv = $events[0]['CourseActivity']['name'];
		$finidatum = date ("d. m. Y.", db_timestamp($events[0]['dateTime']));
	}
	
	// Get course data (needed for table)
	$course = api_call("course/$predmet/$ag");
	
	// Get exam list from api, since details will not include exams that noone took
	$exams = api_call("exam/course/$predmet/$ag", [ "resolve" => ["CourseActivity"] ] )["results"];
	
	// Cool editing box
	cool_box('ajah_start("index.php?c=N&sta=common/ajah&akcija=izmjena_ispita&idpolja="+zamger_coolbox_origcaller.id+"&vrijednost="+coolboxedit.value, "undo_coolbox()", "zamger_coolbox_origcaller=false");');
	?>
	<script language="JavaScript">
	function undo_coolbox() {
		var greska = document.getElementById("zamger_ajah-info").innerText || document.getElementById("zamger_ajah-info").textContent;
		if (greska.includes("Exam result too large")) {
			alert ("Unijeli ste rezultat ispita izvan dozvoljenog opsega");
			document.getElementById("zamger_ajah-info").innerText = "";
			document.getElementById("zamger_ajah-info").textContent = "";
		} else
			alert(greska);
		zamger_coolbox_origcaller.innerHTML = zamger_coolbox_origvalue;
		zamger_coolbox_origcaller=false;
	}
	</script>
	<?
	
	?>
		<p>&nbsp;</p>
		<h3><?=$naziv?>, <?=$finidatum?></h3>
		<p><?=$course['courseName']?> (<?=$ag_naziv?>)</p>
		<p><a href="<?=$this_url?>>">Refresh</a></p>
	<?

	print ajah_box();

	$broj_termina =0;
	
	foreach($events as $event) {
		$broj_termina ++;
		$datum_termina = date("d. m. Y. ( H:i )", db_timestamp($event['dateTime']));
		$groups_exist = false;
		$imeprezime = $logini = $brindexa = $grupe = [];
		
		?>
		<p>Termin <?=$broj_termina?> : <b> <?=$datum_termina?></b></p>
		<?
	
		if (count($event['details']) < 1) {
			?>
			<p>------------------------------------------------------</p>
			<p>Nijedan student nije prijavljen na ovaj termin.</p>
			<p>------------------------------------------------------</p>
			<?
			continue;
		}
	
		foreach($event['details'] as $detail) {
			$student = $detail['student'];
			$imeprezime[$student['id']] = $student['surname'] . " " . $student['name'];
			if (param('logini')) $logini[$student['id']] = $student['login'];
			$brindexa[$student['id']] = $student['studentIdNr'];
			
			// Are there non virtual groups on course
			foreach($detail['score'] as $score) {
				if ($score['CourseActivity']['Activity']['id'] == 9) {
					foreach($score['details'] as $scoreDetail) {
						if (!$scoreDetail['Group']['virtual']) {
							$groups_exist = true;
							$grupe[$student['id']] = $scoreDetail['Group']['name'];
						}
					}
				}
			}
		}
		uasort($imeprezime,"bssort"); // bssort - bosanski jezik
	
	
		// ------- PIVOT DETAILS DATA, FOR FASTER TABLE RENDERING
		
		$cactScores = [];
		$cactClasses = $cactHomeworks = $fixedCacts = [];
		$examResults = [];
		$grades = [];
		$totalScore = $percent = [];
		foreach($event['details'] as $member) {
			$studentId = $member['student']['id'];
			$totalScore[$studentId] = $member['totalScore'];
			$percent[$studentId] = $member['percent'];
			$grades[$studentId] = $member['grade'];
			foreach($member['score'] as $score) {
				$activityType = $score['CourseActivity']['Activity']['id'];
				$cactId = $score['CourseActivity']['id'];
				$cactScores[$cactId][$studentId] = $score['score'];
				
				if ($activityType == 8) { // 8 = Exam
					foreach($score['details'] as $detail) {
						$examId = $detail['Exam']['id'];
						$examResults[$examId][$studentId] = $detail['result'];
					}
				}
			}
		}
		
		// Table heading
		foreach($course['activities'] as $cact) {
			if ($cact['Activity']['id'] == null || $cact['Activity']['id'] == 4) // 4 = Projects
				$fixedCacts[$cact['id']] = $cact;
			if ($cact['Activity']['id'] == 9)
				$cactClasses[$cact['id']] = $cact;
			if ($cact['Activity']['id'] == 2)
				$cactHomeworks[$cact['id']] = $cact;
		}
	
		$zaglavlje1 = ""; // Prvi red zaglavlja
		$zaglavlje2 = ""; // Drugi red zaglavlja
		
		foreach($cactClasses as $cact) {
			$zaglavlje1 .= "<td align=\"center\" rowspan=\"2\">" . $cact['name'] . "</td>\n";
		}
		foreach($cactHomeworks as $cact) {
			$zaglavlje1 .= "<td align=\"center\" rowspan=\"2\">" . $cact['name'] . "</td>\n";
		}
		foreach($fixedCacts as $cact) {
			$zaglavlje1 .= "<td align=\"center\" rowspan=\"2\">" . $cact['name'] . "</td>";
		}
	
		// Zaglavlje ispiti
		if (count($exams) > 0) {
			foreach ($exams as $exam) {
				$zaglavlje2 .= "<td align=\"center\">" . $exam['CourseActivity']['abbrev'] . "<br/> " . date("d.m.", db_timestamp($exam['date'])) . "</td>\n";
			}
			$zaglavlje1 .= "<td align=\"center\" colspan=\"" . count($exams) . "\">Ispiti</td>\n";
		}
	
	?>
		<table border="1" cellspacing="0" cellpadding="2">
			<tr><td rowspan="2" align="center">R.br.</td>
				<td rowspan="2" align="center">Prezime i ime</td>
				<td rowspan="2" align="center">Br. indexa</td>
				<? if ($groups_exist) { ?><td rowspan="2" align="center">Grupa</td><? } ?>
				<?=$zaglavlje1?>
				<td rowspan="2" align="center"><b>UKUPNO</b></td>
				<td rowspan="2" align="center">Konačna<br/>ocjena</td>
			</tr>
			<tr>
				<?=$zaglavlje2?>
			</tr>
		<?
	
		
		// ------ SPISAK STUDENATA ------
	
		$idovi = array_keys($imeprezime);
		
		// Petlja za ispis studenata
		$redni_broj=0;
		$zebra_bg = $zebra_siva = "#f0f0f0";
		$zebra_bijela = "#ffffff";
		foreach ($imeprezime as $studentId => $stud_imepr) {
			if (!in_array($studentId, $idovi)) continue;
			unset ($imeprezime[$studentId]); // Vise se nece javljati
	
			if ($zebra_bg == $zebra_siva) $zebra_bg=$zebra_bijela; else $zebra_bg=$zebra_siva;
			if (param('logini')) $stud_imepr = $logini[$studentId];
			
			$redni_broj++;
				?>
			<tr bgcolor="<?=$zebra_bg?>">
				<td><?=$redni_broj?>.</td>
				<td><?=$stud_imepr?></td>
				<td><?=$brindexa[$studentId]?></td>
				<?
	
			if ($groups_exist) {
				?>
				<td><?=$grupe[$studentId]?></td>
				<?
			}
	
			// OSTALE KOMPONENTE
			foreach($cactClasses as $cactId => $cact) {
				?><td><?=$cactScores[$cactId][$studentId]?></td><?
			}
			foreach($cactHomeworks as $cactId => $cact) {
				?><td><?=$cactScores[$cactId][$studentId]?></td><?
			}
			foreach($fixedCacts as $cactId => $cact) {
				?><td><?=$cactScores[$cactId][$studentId]?></td><?
			}
		
			// ISPITI
	
			if (count($exams) == 0) {
				?><td>&nbsp;</td><?
			}
			foreach ($exams as $exam) {
				$examId = $exam['id'];
				if (array_key_exists($examId, $examResults) && array_key_exists($studentId, $examResults[$examId]))
					$result = $examResults[$examId][$studentId];
				else
					$result = "/";
				$cellId = "ispit-$studentId-$examId";
				
				?><td align="center" id="<?=$cellId?>" ondblclick="coolboxopen(this)"><?=$result?></td><?
			}
			
			$grade = "/";
			if ($grades[$studentId])
				$grade = $grades[$studentId];
			$cellId = "ko-$studentId-$predmet-$ag";
			
			?>
				<td align="center"><?=$totalScore[$studentId]?> (<?=$percent[$studentId]?>%) </td>
				<td id="<?=$cellId?>" ondblclick="coolboxopen(this)"><?=$grade?></td>
			</tr>
			<?
		}
		?>
		</table>
		<p>&nbsp;</p>
		<?
}
?>

<?}?>
