<?

// STUDENT/PREDMET - statusna stranica predmeta



function student_predmet() {

	global $userid, $courseDetails;
	
	
	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']); // akademska godina
	
	$course = api_call("course/$predmet/student/$userid", ["resolve" => ["Group", "ZClass", "Homework", "Exam"], "year" => $ag, "score" => "true", "activities" => "true", "totalScore" => "true", "courseInformation" => "true", "details" => "true"]);
	if (empty($course)) {
		zamgerlog("nepoznat predmet $predmet",3); // nivo 3: greska
		zamgerlog2("nepoznat predmet", $predmet);
		biguglyerror("Nepoznat predmet");
		return;
	}

	$ponudakursa = $course['CourseOffering']['id'];


	?>
	<br/>
	<p style="font-size: small;">Predmet: <b><?=$course['courseName']?> (<?=$course['courseYear']?>)</b><br/>
	<?

	// Određivanje labgrupe
	if (array_key_exists('group', $course) && $course['group']) {
		?>Grupa: <b><?=$course['group']?></b><?
	}
	
	print "</p><br/>\n";

	foreach($course['staff'] as $staff) {
		$name = $staff['Person']['name'] . " " . $staff['Person']['surname'];
		if ($staff['Person']['titlesPre']) $name = $staff['Person']['titlesPre'] . " $name";
		if ($staff['Person']['titlesPost']) $name .= " " . $staff['Person']['titlesPost'];
		$email = $staff['Person']['email'];
		
		print "<b>".ucfirst($staff['status'])."</b>: <a href=\"mailto:$email\">$name</a><br/>";
	}

	
	// PROGRESS BAR
	
	// Sumiramo bodove po komponentama i računamo koliko je bilo moguće ostvariti
	$ukupno_bodova = $course['totalScore'];
	$ukupno_mogucih = $course['possibleScore'];
	$procenat = $course['percent'];
	
	// boja označava napredak studenta
	if ($procenat>=75)
		$boja = "#00FF00";
	else if ($procenat>=50)
		$boja = "#FFFF00";
	else
		$boja = "#FF0000";
	
	// Crtamo tabelu koristeći dvije preskalirane slike
	$ukupna_sirina = 200;
	
	$tabela1 = $procenat * 2;
	$tabela2 = $ukupna_sirina - $tabela1;
	
	// Tekst "X bodova" ćemo upisati u onu stranu tabele koja je manja
	if ($tabela1 <= $tabela2) {
		$ispis1 = "<img src=\"static/images/fnord.gif\" width=\"$tabela1\" height=\"10\">";
		$ispis2 = "<img src=\"static/images/fnord.gif\" width=\"$tabela2\" height=\"1\"><br> $ukupno_bodova bodova";
	} else {
		$ispis1="<img src=\"static/images/fnord.gif\" width=\"$tabela1\" height=\"1\"><br> $ukupno_bodova bodova";
		$ispis2="<img src=\"static/images/fnord.gif\" width=\"$tabela2\" height=\"10\">";
	}


	?>
	
	
	<!-- progress bar -->
	
	<center><table border="0"><tr><td align="left">
	<p>Osvojili ste....<br/>
	<table style="border:1px;border-style:solid" width="206" cellpadding="0" cellspacing="2"><tr>
	<td width="<?=$tabela1?>" bgcolor="<?=$boja?>"><?=$ispis1?></td>
	<td width="<?=$tabela2?>" bgcolor="#FFFFFF"><?=$ispis2?></td></tr></table>
	
	<table width="208" border="0" cellspacing="0" cellpadding="0"><tr>
	<td width="68">0</td>
	<td align="center" width="68">50</td>
	<td align="right" width="69">100</td></tr></table>
	što je <?=$procenat?>% od trenutno mogućih <?=round($ukupno_mogucih,2) ?> bodova.</p>
	</td></tr></table></center>

	
	<!-- end progress bar -->
	<?




	
	//  PRISUSTVO NA VJEŽBAMA
	
	function prisustvo_ispis($AttendanceDetails) {
		// Don't print groups without attendance detail
		if (!array_key_exists('attendance', $AttendanceDetails) || empty($AttendanceDetails['attendance']))
			return;
		
		$imegrupe = "[Bez naziva]";
		if (array_key_exists("Group", $AttendanceDetails))
			$imegrupe = $AttendanceDetails['Group']['name'];
		
		$odsustva=0;
		$datumi = $vremena = $statusi = "";
		foreach($AttendanceDetails['attendance'] as $Attendance) {
			$time = db_timestamp($Attendance['ZClass']['datetime']);
			$datumi .= "<td>" . date("d.m" , $time) . "</td>\n";
			$vremena .= "<td>" . date("h" , $time) . "<sup>" . date("i" , $time) . "</sup></td>\n";
		
			if ($Attendance['presence'] == 0) {
				$statusi .= "<td bgcolor=\"#FFCCCC\" align=\"center\">NE</td>\n";
				$odsustva++;
			} else if ($Attendance['presence'] == 1) {
				$statusi .= "<td bgcolor=\"#CCFFCC\" align=\"center\">DA</td>\n";
			} else {
				$statusi .= "<td bgcolor=\"#FFFFCC\" align=\"center\">/</td>\n";
			}
			
		}
		
		?>
	
		<b>Prisustvo (<?=$imegrupe?>):</b><br/>
		<table cellspacing="0" cellpadding="2" border="0" id="prisustvo" class="prisustvo">
			<tr>
				<th>Datum</th>
				<?=$datumi?>
			</tr>
			<tr>
				<th>Vrijeme</th>
				<?=$vremena?>
			</tr>
			<tr>
				<th>Prisutan</th>
				<?=$statusi?>
			</tr>
		</table>
		</p>
		
		<?
	}
	
	$bodovi = 0; $found = false;
	foreach($course['score'] as $StudentScore) {
		if ($StudentScore['CourseActivity']['Activity']['id'] != 9) // 9 Prisustvo
			continue;
		$found = true;
		$bodovi += $StudentScore['score'];
		foreach ($StudentScore['details'] as $AttendanceDetails)
			prisustvo_ispis($AttendanceDetails);
	}
	
	if ($found) {
		?><p>Ukupno na prisustvo imate <b><?=$bodovi?></b> bodova.</p>
		<?
	}




	//  ZADAĆE
	
	
	// Statusne ikone:
	$stat_icon = array("bug", "view", "copy", "bug", "view", "ok");
	$stat_tekst = array("Bug u programu", "Pregled u toku", "Zadaća prepisana", "Bug u programu", "Pregled u toku", "Zadaća OK");
	
	foreach($course['score'] as $StudentScore) {
		$cact = $StudentScore['CourseActivity']; // shortcut
		if ($cact['Activity']['id'] != 2) // 2 = Zadaće
			continue;
		
		// Transform homework details into a 2D matrix which is much easier to work with
		$homeworks = [];
		$assignmentHomeworks = [];
		foreach($StudentScore['details'] as $Assignment) {
			if (!array_key_exists($Assignment['Homework']['id'], $assignmentHomeworks)) {
				$homeworks[] = $Assignment['Homework'];
				$assignmentHomeworks[$Assignment['Homework']['id']] = [];
			}
			$assignmentHomeworks[$Assignment['Homework']['id']][] = $Assignment;
		}
		
		$totalSumScore = $totalMaxScore = 0;
		
		?>

		<!-- zadace -->

		<b><?=$cact['name']?>:</b><br/>
		<table cellspacing="0" cellpadding="2" border="0" id="zadace<?=$cact['id']?>" class="zadace">
			<thead>
				<tr>
		<?
		
		// If students are not allowed to submit homework, we show a simplified table
		if (!array_key_exists('StudentSubmit', $cact['options']) || !$cact['options']['StudentSubmit']) {
			// Summarize individual assignments for each homework
			foreach ($homeworks as $homework) {
				$name = $homework['name'];
				if (!preg_match("/\w/",$name)) $name = "[Bez naziva]";
				?><td><?=$name?></td><?
			}
			
			?>
					<td><b>Ukupno bodova</b></td>
				</tr>
			</thead>
			<tbody>
			<?
			
			foreach ($homeworks as $homework) {
				$sumScore = 0;
				$status = 0;
				foreach ($assignmentHomeworks[$homework['id']] as $Assignment) {
					$sumScore += $Assignment['score'];
					if ($status == 0) $status = $Assignment['status'];
				}
				
				// Status 0 means not submitted/graded, show empty cell
				if ($status == 0) {
					?>
					<td>&nbsp;</td>
					<?
					$sumScore = 0; // Shouldn't happen
				} else {
					?>
					<td><img src="static/images/16x16/<?=$stat_icon[$status]?>.png" width="16" height="16" border="0" align="center" title="<?=$stat_tekst[$status]?>" alt="<?=$stat_tekst[$status]?>"> <?=$sumScore?> / <?=$homework['maxScore']?></td>
					<?
				}
				$totalSumScore += $sumScore;
				$totalMaxScore += $homework['maxScore'];
			}
			
			// Show totals
			?>
					<td><?=$totalSumScore?> / <?=$totalMaxScore?></td></tr>
				</tbody>
			</table>

			&nbsp;<br/>
			
			<?

		// Otherwise, we show a rich table with interactive controls for each assignment
		} else {
			// We need a maximum number of assignments per homework for table heading
			// Also if there are homework texts
			$maxAssignments = 0;
			$hasText = false;
			foreach ($homeworks as $homework) {
				if ($homework['nrAssignments'] > $maxAssignments)
					$maxAssignments = $homework['nrAssignments'];
				if ($homework['text']) $hasText = true;
			}
			
			?>
			<td>&nbsp;</td>
			<?
		
			for ($i=1; $i<=$maxAssignments; $i++) {
				?><td>Zadatak <?=$i?>.</td><?
			}
			
			?>
					<td><b>Ukupno bodova</b></td>
					<td><b>Mogućih</b></td>
					<? if ($hasText) { ?><td><b>Postavka zadaća</b></td><? } ?>
					<td><b>PDF</b></td>
				</tr>
			</thead>
			<tbody>
			<?
			
			// Print homework details
			foreach ($homeworks as $homework) {
				?>
				<tr>
					<th><?=$homework['name']?></th>
				<?
				
				$sumScore = 0;
				$sentAnything = false;
				for($asgn=1; $asgn<=$maxAssignments; $asgn++) {
					// If this homework has less than maxAssignments, print empty cells
					if ($asgn > $homework['nrAssignments']) {
						?><td>&nbsp;</td><?
						continue;
					}
					
					// Get assignment from 2D array
					$Assignment = $assignmentHomeworks[$homework['id']][$asgn-1];
					
					// id=null means user did not submit homework
					if (!$Assignment['id']) {
						?><td><a href="?sta=student/zadaca&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>&amp;zadaca=<?=$homework['id']?>&amp;zadatak=<?=$asgn?>"><img src="static/images/16x16/create_new.png" width="16" height="16" border="0" align="center" title="Novi zadatak" alt="Novi zadatak"></a></td><?
						
					} else {
						if (!empty(trim($Assignment['comment'])))
							$hasComment = "<img src=\"static/images/16x16/comment_yellow.png\"  width=\"15\" height=\"14\" border=\"0\" title=\"Ima komentar\" alt=\"Ima komentar\" align=\"center\">";
						else
							$hasComment = "";
						?><td><a href="?sta=student/zadaca&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>&amp;zadaca=<?=$homework['id']?>&amp;zadatak=<?=$asgn?>"><img src="static/images/16x16/<?=$stat_icon[$Assignment['status']]?>.png" width="16" height="16" border="0" align="center" title="<?=$stat_tekst[$Assignment['status']]?>" alt="<?=$stat_tekst[$Assignment['status']]?>"> <?=$Assignment['score']?> <?=$hasComment?></a></td>
						<?
						
						$sumScore += $Assignment['score'];
						$sentAnything = true;
					}
				}
					
				?>
					<td><?=$sumScore?></td>
					<td><?=$homework['maxScore']?></td>
				<?
					
				// Show homework text link
				if ($hasText) {
					if ($homework['text']) {
						?>
						<td>
						<a href="?sta=common/attachment&amp;zadaca=<?=$homework['id']?>&amp;tip=postavka"><img src="static/images/16x16/download.png" width="16" height="16" border="0"></a>
						</td><?
					} else {
						?>
						<td>&nbsp;</td>
						<?
					}
				}
					
				// Show PDF link
				if ($sentAnything) {
					?>
					<td>
						<a href="?sta=student/zadacapdf&amp;zadaca=<?=$homework['id']?>" target="_new"><img src="static/images/16x16/pdf.png" width="16" height="16" border="0"></a>
					</td>
					<?
				} else {
					?>
					<td>&nbsp;</td>
					<?
				}
				$totalSumScore += $sumScore;
				$totalMaxScore += $homework['maxScore'];
					
				?>
				</tr>
				<?
				
			}

			?>
				<tr>
					<td colspan="<?=$maxAssignments+1?>" align="right">UKUPNO: </td>
					<td><?=$totalSumScore?></td>
					<td><?=$totalMaxScore?></td>
					<td>&nbsp;</td>
					<? if ($hasText) { ?><td>&nbsp;</td><? } ?>
				</tr>
			</tbody>
			</table>

			<p>Za ponovno slanje zadatka, kliknite na sličicu u tabeli iznad. <a href="#" onclick="javascript:window.open('legenda-zadace.html','blah6','width=320,height=130');">Legenda simbola</a></p>
			<br/>
			
			<!-- end zadace -->
			
			<?

		} // else


	} // foreach($course['score'] as $StudentScore) {



	//  ISPITI
	
	?>
	
	<!-- ispiti -->
	
	<b>Ispiti:</b><br/>
	
	<?
	
	// Sort exam results by date
	$examResults = [];
	foreach($course['score'] as $StudentScore) {
		if ($StudentScore['CourseActivity']['Activity']['id'] == 8) {
			foreach ($StudentScore['details'] as $ExamResult) {
				$time = db_timestamp($ExamResult['Exam']['date']);
				$ExamResult['name'] = $StudentScore['CourseActivity']['name'];
				$ExamResult['date'] = date("d. m. Y", $time);
				$examResults[$time . $StudentScore['CourseActivity']['id']] = $ExamResult;
			}
		}
	}
	
	if (count($examResults) == 0) {
		?>
		<p>Nije bilo parcijalnih ispita.</p>
		<?
	}
	ksort($examResults);
	foreach($examResults as $ExamResult) {
		?>
		<p><?=$ExamResult['name']?> (<?=$ExamResult['date']?>): <b><?=$ExamResult['result']?> bodova</b></p>
		<?
	}
	

	//  FIKSNE KOMPONENTE
	
	$printedHeadline = false;
	foreach($course['score'] as $StudentScore) {
		if ($StudentScore['CourseActivity']['Activity']['id'] == null) {
			if (!$printedHeadline) {
				$printedHeadline = true;
				?>

				<!-- ostalo -->

				<b>Ostalo:</b><br/>
				
				<?
			}
			?><p><?=$StudentScore['CourseActivity']['name']?>: <b><?=$StudentScore['score']?> bodova</b></p><?
		}
	}


	// KONAČNA OCJENA

	if ($course['grade'] != null) {
		if ($course['grade'] == 11) $course['grade'] = "Ispunio/la obaveze";
		if ($course['grade'] == 12) $course['grade'] = "Uspješno odbranio/la";
		?>
		<center>
			<table width="100px" style="border-width: 3px; border-style: solid; border-color: silver">
				<tr><td align="center">
						KONAČNA OCJENA<br/>
						<font size="6"><b><?=$course['grade']?></b></font>
					</td></tr>
			</table>
		</center>
		<?
	}

}

?>
