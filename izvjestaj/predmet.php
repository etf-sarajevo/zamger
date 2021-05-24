<?

// IZVJESTAJ/PREDMET - statistika predmeta, pregled prisustva itd.



function izvjestaj_predmet() {

	global $userid,$user_nastavnik,$user_studentska,$user_siteadmin, $user_student, $user_sefodsjeka, $conf_files_path;
	
	require_once("lib/utility.php"); // procenat, bssort
	
	
	// Parametri upita
	
	$predmet = int_param('predmet');
	$ag = int_param('ag');
	$time = time();
	
	if (!$user_nastavnik && !$user_studentska && !$user_siteadmin && !$user_sefodsjeka) {
		$dan=0;
		do {
			$filename = $conf_files_path."/cache/izvjestaj_predmet/$predmet-$ag/$predmet-$ag-".date("dmY", $time).".html";
			$time -= 86400;
			$dan++;
			if ($dan == 3650) {
				niceerror("Izvještaj ne postoji");
				return;
			}
		} while (!file_exists($filename));
	
		readfile($filename);
		return;
	}
	
	if ($userid != 0 && !$user_nastavnik && !$user_studentska && !$user_siteadmin) {
		// Sprječavamo veliki broj uzastopnih otvaranja istog modula
		zamgerlog2("pristup");
		
		$limit_vrijeme = 5*60; // 5 minuta
		$limit_broj_posjeta = 5; // broj posjeta
	
		$q10 = db_query("select UNIX_TIMESTAMP(vrijeme) FROM log2 WHERE userid=$userid AND modul=15 ORDER BY id DESC LIMIT $limit_broj_posjeta");
		$count = 0;
		while($r10 = db_fetch_row($q10)) {
			if ($r10[0] > $time - $limit_vrijeme) $count++;
		}
		if ($count >= $limit_broj_posjeta) {
			//niceerror("Odmori malo, opusti se, oguli jednu jabuku.");
			print "<img src=\"static/images/oguljena_zelena_jabuka_kora.jpg\">";
			return;
		}
	}
	
	// sumiraj kolone za zadace i prisustvo
	if (param('skrati')=="da") $skrati=1; else $skrati=0;
	// ako ova opcija nije "da", prikazuje se samo zadnji rezultat na svakom parcijalnom, ili samo integralni ispit (ako je bolji)
	if (param('razdvoji_ispite')=="da") $razdvoji_ispite=1; else $razdvoji_ispite=0;
	// nemoj razdvajati studente po grupama (neki su trazili ovu opciju)
	if (param('sastavi_grupe')=="da" || param('sakrij_imena')=="da") $sastavi_grupe=1; else $sastavi_grupe=0;
	// tabela za samo jednu grupu
	$grupa = int_param('grupa');
	
	
	
	// Novi kod
	
	global $_api_http_code;
	
	// ------- ZAGLAVLJE STRANICE (naslov i sl.)
	
	$course = api_call("course/$predmet/$ag");
	if ($_api_http_code != "200") {
		niceerror("Neuspješno otvaranje predmeta");
		api_report_bug($course, []);
		return;
	}

	?>
	
	<p>Univerzitet u Sarajevu<br/>
		Elektrotehnički fakultet Sarajevo</p>
	<p>Datum i vrijeme izvještaja: <?=date("d. m. Y. H:i");?></p>
	
	<h1><?=$course['courseName']?></h1>
	<h3>Akademska <?=$course['AcademicYear']['name']?> godina - Izvještaj o predmetu</h3>
	<?
	
	// Koristimo ulogu iz /index.php da odredimo da li će se prikazati imena...
	$imenaopt = true;
	if ((!$user_nastavnik && !$user_studentska && !$user_siteadmin) || $_REQUEST['sakrij_imena']=="da") {
		$imenaopt = false;
		print "<p><b>Napomena:</b> Radi zaštite privatnosti studenata, imena će biti prikazana samo ako ste prijavljeni kao nastavnik/saradnik.</p>\n";
	}
	
	
	// Podaci o studentima
	
	if ($grupa > 0) {
		$group = api_call("group/$grupa",
			[ "details" => true, "names" => $imenaopt,
				"resolve" => ["Homework", "ZClass"] ]
		);
	} else {
		$group = api_call("group/course/$predmet/allStudents",
			[ "details" => true, "names" => $imenaopt, "year" => $ag,
				"resolve" => ["Homework", "ZClass"] ]
		);
	}
	
	if ($_api_http_code == "404") {
		biguglyerror("Nemate pravo ulaska u ovu grupu!");
		zamgerlog("nepostojeci predmet $predmet $ag",3); // 3 = greska
		zamgerlog2("nepostojeci predmet", $predmet, $ag);
		return;
	}
	else if ($_api_http_code == "401") {
		biguglyerror("Nemate pravo ulaska u ovu grupu!");
		zamgerlog("nema pravo pristupa labgrupi svi studenti $predmet $ag",3); // 3 = greska
		zamgerlog2("nema pravo pristupa predmetu", $predmet, $ag);
		return;
	} else if ($_api_http_code != "200") {
		niceerror("Greška prilikom pristupa grupi");
		api_report_bug($result, []);
		return;
	}
	
	$labgrupa = $group['id'];
	$naziv = $group['name'];
	$grupa_virtualna = true;
	
	// Spisak komponenti koje su zastupljene na predmetu
	$tipovi_komponenti = [];
	foreach($group['activities'] as $activity)
		$tipovi_komponenti[$activity['id']] = $activity['Activity']['id'];
	
	
	
	// -------- CACHE: SPISAK STUDENATA U GRUPI
	
	$imeprezime = array();
	$brind = array();
	$spisak_grupa = [ 0 => "[Bez grupe]" ];
	$clanovi_grupa = [ 0 => [] ];
	foreach($group['members'] as $member) {
		$studentId = $member['student']['id'];
		
		if ($imenaopt) {
			$imeprezime[$studentId] = $member['student']['surname'] . "&nbsp;" . $member['student']['name'];
			$brind[$studentId] = $member['student']['studentIdNr'];
		} else {
			$imeprezime[$studentId] = $member['reportCode'];
			$brind[$studentId] = "";
		}
		
		if (!$sastavi_grupe && $member['Group']) {
			//if (array_key_exists($member['Group']['id'], $spisak_grupa)) {
				$spisak_grupa[$member['Group']['id']] = $member['Group']['name'];
				//$clanovi_grupa[$member['Group']['id']] = [];
			//}
			$clanovi_grupa[$member['Group']['id']][] = $studentId;
		} else {
			$clanovi_grupa[0][] = $studentId;
		}
	}
	if ($imenaopt)
		uasort($imeprezime,"bssort"); // bssort - bosanski jezik
	else {
		// Shuffle array while preserving key association
		$keys = array_keys($imeprezime);
		shuffle($keys);
		$newArray = [];
		foreach($keys as $key)
			$newArray[$key] = $imeprezime[$key];
		$imeprezime = $newArray;
	}
	natsort($spisak_grupa); // Natural sort: Grupa 10 dolazi nakon grupe 9 a ne prije grupe 2
	
	// Ako nema nikoga u grupi, prekidamo rad odmah
	
	if (count($imeprezime) == 0) {
		print "<p>Nijedan student nije u grupi</p>\n";
		return;
	}
	
	
	
	// ------- PIVOT DETAILS DATA, FOR FASTER TABLE RENDERING
	
	$cactTitles = $cactScores = [];
	$homeworks = $cactHomeworks = $homeworkStatus = $homeworkScore = [];
	$classes = $cactClasses = $presenceCache = [];
	$examResults = [];
	$possibleScore = $percent = [];
	foreach($group['members'] as $member) {
		$studentId = $member['student']['id'];
		$possibleScore[$studentId] = $member['possibleScore'];
		$percent[$studentId] = $member['percent'];
		foreach($member['score'] as $score) {
			$activityType = $score['CourseActivity']['Activity']['id'];
			$cactId = $score['CourseActivity']['id'];
			if ($skrati == 1)
				$cactTitles[$cactId] = $score['CourseActivity']['abbrev'];
			else
				$cactTitles[$cactId] = $score['CourseActivity']['name'];
			$cactScores[$cactId][$studentId] = $score['score'];
			
			if ($activityType == null) // null = Fixed component
				continue; // No details
			
			foreach($score['details'] as $detail) {
				if ($activityType == 2) { // 2 = Homework
					$homeworkId = $detail['Homework']['id'];
					$assignNo = $detail['assignNo'];
					$status = $detail['status'];
					$score = $detail['score'];
					
					if (!array_key_exists($homeworkId, $homeworks)) {
						$cactHomeworks[$cactId][$homeworkId] = $detail['Homework'];
						$homeworks[$homeworkId] = $detail['Homework'];
					}
					
					$homeworkStatus[$homeworkId][$assignNo][$studentId] = $status;
					$homeworkScore[$homeworkId][$assignNo][$studentId] = $score;
				}
				
				if ($activityType == 9) { // 9 = Attendance
					foreach($detail['attendance'] as $attendance) {
						if (!array_key_exists($cactId, $cactClasses))
							$cactClasses[$cactId] = [];
						// In virtual groups, we would receive attendance detail for all groups
						if ($attendance['ZClass']['Group']['id'] != $labgrupa) continue;
						
						$classId = $attendance['ZClass']['id'];
						if (!array_key_exists($classId, $classes)) {
							$cactClasses[$cactId][$classId] = $attendance['ZClass'];
							$classes[$classId] = $attendance['ZClass'];
						}
						$presenceCache[$classId][$studentId] = $attendance['presence'];
					}
				}
				
				if ($activityType == 8) { // 8 = Exam
					$examId = $detail['Exam']['id'];
					$examResults[$examId][$studentId] = $detail['result'];
				}
			}
		}
	}
	
	// Get exam list from api, since details will not include exams that noone took
	$exams = api_call("exam/course/$predmet/$ag", [ "resolve" => ["CourseActivity"] ] )["results"];
	
	// Get fixed cacts list
	$fixedCacts = [];
	foreach($course['activities'] as $cact) {
		if ($cact['Activity']['id'] == null || $cact['Activity']['id'] == 4) // 4 = Projects
			$fixedCacts[$cact['id']] = $cact;
		// Fix attendance with no registered classes
		if ($cact['Activity']['id'] == 9)
			if (!array_key_exists($cact['id'], $cactClasses))
				$cactClasses[$cact['id']] = [];
	}
	
	// Get list of homeworks
	$allHomeworks = api_call("homework/course/$predmet/$ag", [ "resolve" => ["CourseActivity"] ] )["results"];
	foreach($allHomeworks as $hw) {
		$cactId = $hw['CourseActivity']['id'];
		if (!array_key_exists($cactId, $cactHomeworks))
			$cactHomeworks[$cactId] = [];
		$found = false;
		foreach($cactHomeworks[$cactId] as $hwk)
			if ($hwk['id'] == $hw['id'])
				$found = true;
		if (!$found)
			$cactHomeworks[$cactId][$hw['id']] = $hw;
		if (!array_key_exists($cactId, $cactTitles))
			$cactTitles[$cactId] = $hw['CourseActivity']['name'];
	}
	
	// Sort classes by dateTime within each component
	foreach($cactClasses as &$cc) {
		uasort($cc, function($c1, $c2) { return db_timestamp($c1['dateTime']) > db_timestamp($c2['dateTime']); });
	}
	// Sort exams by date
	uasort($exams, function($e1, $e2) { return db_timestamp($e1['date']) > db_timestamp($e2['date']); });
	
	$quizResultCache = []; // Cache quizzes
	$quizzes = api_call("quiz/course/$predmet/$ag")["results"];
	foreach($quizzes as $quiz) {
		$quizResults = api_call("quiz/" . $quiz['id'] . "/group/$labgrupa")["results"];
		foreach($quizResults as $qr) {
			$studentId = $qr['student']['id'];
			if ($qr['finished'] && $qr['score'] >= $quiz['passPoints'])
				$quizResultCache[$quiz['id']][$studentId] = true;
			else
				$quizResultCache[$quiz['id']][$studentId] = false;
		}
	}
	
	
	
	// ------- TABLICA GRUPE - ZAGLAVLJE
	
	$zaglavlje1 = ""; // Prvi red zaglavlja
	$zaglavlje2 = ""; // Drugi red zaglavlja
	
	// Zaglavlje prisustvo
	foreach($cactClasses as $cactId => $c) {
		if ($skrati == 1) {
			$zaglavlje1 .= "<td align=\"center\" rowspan=\"2\">" . $cactTitles[$cactId] . "</td>\n";
		} else {
			$brcasova = count($c);
			if ($brcasova == 0 || $skrati == 1) {
				$brcasova = 1;
				$zaglavlje2 .= "<td>&nbsp;</td>";
			}
			
			$zaglavlje1 .= "<td align=\"center\" colspan=\"" . ($brcasova + 1) . "\">" . $cactTitles[$cactId] . "</td>\n";
			
			foreach ($c as $class) {
				$cas_id = $class['id'];
				list($date, $time) = explode(" ", $class['dateTime']);
				list ($cas_godina, $cas_mjesec, $cas_dan) = explode("-", $date);
				list ($cas_sat, $cas_minuta, $cas_sekunda) = explode(":", $time);
				$zaglavlje2 .= "<td align=\"center\">$cas_dan.$cas_mjesec<br/>$cas_sat:$cas_minuta";
				$zaglavlje2 .= '<br/><a href="javascript:onclick=upozorenje(' . "'$cas_id'" . ');"><img src="static/images/16x16/not_ok.png" border="0"></a>';
				$zaglavlje2 .= "</td>\n";
			}
			
			$zaglavlje2 .= "<td>BOD.</td>\n";
		}
	}
	
	// Zaglavlje zadaće
	
	foreach($cactHomeworks as $cactId => $hw) {
		$brzadaca = count($hw);
		if ($brzadaca == 0) continue; // Skip component with no homeworks
		
		if ($skrati == 1) {
			$zaglavlje1 .= "<td align=\"center\" rowspan=\"2\">" . $cactTitles[$cactId] . "</td>\n";
		} else {
			$zaglavlje1 .= "<td align=\"center\" colspan=\"$brzadaca\">" . $cactTitles[$cactId] . "</td>\n";
			foreach ($hw as $homework) {
				$zaglavlje2 .= "<td width=\"60\" align=\"center\">" . $homework['name'] . "<br /><a href=\"?sta=saradnik/svezadace&grupa=$labgrupa&zadaca=" . $homework['id'] . "\">Download</a></td>\n";
			}
		}
	}
	
	// Zaglavlje fiksne komponente
	foreach($fixedCacts as $cactId => $cact) {
		$zaglavlje1 .= "<td align=\"center\" rowspan=\"2\">" . $cact['name'] . "</td>";
	}
	
	// Zaglavlje ispiti
	if (count($exams) > 0) {
		if ($razdvoji_ispite == 0) {
			$zaglavlje1 .= "<td align=\"center\" rowspan=\"2\">Ispiti</td>\n";
		} else {
			foreach ($exams as $exam) {
				$zaglavlje2 .= "<td align=\"center\">" . $exam['CourseActivity']['abbrev'] . "<br/> " . date("d.m.", db_timestamp($exam['date'])) . "</td>\n";
			}
			$zaglavlje1 .= "<td align=\"center\" colspan=\"" . count($exams) . "\">Ispiti</td>\n";
		}
	}
	

	
	// -------------------------------
	
	// GLAVNA PETLJA ZA GRUPE
	
	foreach ($spisak_grupa as $grupa_id => $grupa_naziv) {
		// Ako je nulta grupa prazna (svi studenti rasporedjeni u grupe), preskacemo je
		if ($grupa_id==0 && count($imeprezime)==0) continue;
	
		
		// ----- GENERISANJE ZAGLAVLJA -----
		
		
			?>
		<center><h2><?=$grupa_naziv?></h2></center>
		<table border="1" cellspacing="0" cellpadding="2">
			<tr><td rowspan="2" align="center">R.br.</td>
				<? if ($imenaopt) {
					?>
					<td rowspan="2" align="center">Prezime i ime</td>
					<td rowspan="2" align="center">Br. indexa</td>
				<? } else { ?>
					<td rowspan="2" align="center">Kod</td>
				<? } ?>
				<?=$zaglavlje1?>
				<td rowspan="2" align="center"><b>UKUPNO</b></td>
				<td rowspan="2" align="center">Konačna<br/>ocjena</td>
			</tr>
			<tr>
				<?=$zaglavlje2?>
			</tr>
			<?
			
			
		$redni_broj=0;
		foreach ($imeprezime as $studentId => $stud_imepr) {
			if (!in_array($studentId, $clanovi_grupa[$grupa_id]))
				continue;
			$redni_broj++;
			?>
			<tr>
			<td id="student_<?=$studentId?>"><?=$redni_broj?></td>
			<td><?=$imeprezime[$studentId]?></td>
			<? if ($imenaopt == 1) { ?><td><?=$brind[$studentId]?></td><? } ?>
			<?
			
			$prisustvo_ispis=$zadace_ispis=$ispiti_ispis=$fiksne_ispis="";
			
			
			// PRISUSTVO - ISPIS
			
			foreach($cactClasses as $cactId => $_classes) {
				if ($skrati == 0) {
					if (count($_classes) == 0)
						$prisustvo_ispis .= "<td>&nbsp;</td>";
					
					foreach ($_classes as $classId => $class) {
						$uspjeh_na_kvizu = "";
						if ($class['Quiz']['id'] > 0) {
							$quizId = $class['Quiz']['id'];
							if (array_key_exists($studentId, $quizResultCache[$quizId])) {
								if ($quizResultCache[$quizId][$studentId])
									$uspjeh_na_kvizu = '<img src="static/images/16x16/ok.png" width="8" height="8">';
								else
									$uspjeh_na_kvizu = '<img src="static/images/16x16/not_ok.png" width="8" height="8">';
							}
						}
						
						if (!array_key_exists($studentId, $presenceCache[$classId]))
							$presence = 2;
						else
							$presence = $presenceCache[$classId][$studentId];
						
						if ($presence == 1) {
							$prisustvo_ispis .= "<td bgcolor=\"#CCFFCC\" align=\"center\" id=\"dane-" . $studentId . "-" . $classId . "\" onclick=\"javascript:prisustvo(event," . $studentId . "," . $classId . ")\"><span id=\"danetekst-" . $studentId . "-" . $classId . "\">DA</span> $uspjeh_na_kvizu</td>";
						} else if ($presence == 0) {
							$prisustvo_ispis .= "<td bgcolor=\"#FFCCCC\" align=\"center\" id=\"dane-" . $studentId . "-" . $classId . "\" onclick=\"javascript:prisustvo(event," . $studentId . "," . $classId . ")\"><span id=\"danetekst-" . $studentId . "-" . $classId . "\">NE</span> $uspjeh_na_kvizu</td>";
						} else {
							$prisustvo_ispis .= "<td bgcolor=\"#FFFFCC\" align=\"center\" id=\"dane-" . $studentId . "-" . $classId . "\" onclick=\"javascript:prisustvo(event," . $studentId . "," . $classId . ")\"><div id=\"danetekst-" . $studentId . "-" . $classId . "\"> / </div> $uspjeh_na_kvizu</td>";
						}
					}
				}
				
				// Total score
				$prisustvo_ispis .= "<td align=\"center\">" . $cactScores[$cactId][$studentId] . "</td>\n";
			}
			
			
			// ZADACE - ISPIS
			
			foreach($cactHomeworks as $cactId => $_homeworks) {
				if ($skrati == 1) {
					$zadace_ispis .= "<td align=\"center\">" . $cactScores[$cactId][$studentId] . "</td>\n";
				} else {
					foreach ($_homeworks as $homeworkId => $homework) {
						$zadace_ispis .= "<td> \n";
						for ($i = 1; $i <= $homework['nrAssignments']; $i++) {
							$status = $homeworkStatus[$homeworkId][$i][$studentId];
							if ($status == 0) { // Zadatak nije poslan
							} else {
								$zadace_ispit .= $homeworkScore[$homeworkId][$i][$studentId];
							}
						}
						$zadace_ispis .= "&nbsp;</td>\n";
					}
				}
			}
			
			
			// FIKSNE KOMPONENTE - ISPIS
			foreach ($fixedCacts as $cactId => $cact) {
				if (array_key_exists($cactId, $cactScores) && array_key_exists($studentId, $cactScores[$cactId])) {
					$fiksne_ispis .= "<td align=\"center\">" . $cactScores[$cactId][$studentId] . "</td>\n";
				} else {
					$fiksne_ispis .= "<td align=\"center\">/</td>\n";
				}
			}
			
			
			// ISPITI - ISPIS
			
			foreach($exams as $exam) {
				$examId = $exam['id'];
				if (array_key_exists($exam['id'], $examResults) && array_key_exists($studentId, $examResults[$exam['id']])) {
					$ispiti_ispis .= "<td align=\"center\">" . $examResults[$examId][$studentId] . "</td>\n";
				} else {
					$ispiti_ispis .= "<td align=\"center\">/</td>\n";
				}
			}
			
			
			// KONACNA OCJENA - ISPIS
			$currentMember = [];
			foreach($group['members'] as $member) {
				if ($member['student']['id'] == $studentId) {
					$currentMember = $member;
					break;
				}
			}
			if ($course['gradeType'] == 1 || $course['gradeType'] == 2) {
				if ($course['gradeType'] == 1) {
					$ocjena_value = 11;
					$ocjena_text = "ispunio/la uslove";
				} else {
					$ocjena_value = 12;
					$ocjena_text = "uspješno odbranio";
				}
				if ($currentMember['grade']) $ispunio_uslove = "CHECKED"; else $ispunio_uslove = "";
				$ko_ispis = "<td align=\"center\" id=\"ko-$studentId-$predmet-$ag\">$ocjena_text</td>";
			}
			
			else {
				if ($currentMember['grade']) {
					$ko_ispis = "<td align=\"center\" id=\"ko-$studentId-$predmet-$ag\">" . $currentMember['grade'] . "</td>\n";
				} else {
					$ko_ispis = "<td align=\"center\" id=\"ko-$studentId-$predmet-$ag\">/</td>\n";
				}
			}
			
			?>
			<?=$prisustvo_ispis?>
			<?=$zadace_ispis?>
			<?=$fiksne_ispis?>
			<?=$ispiti_ispis?>
			<td align="center" id="total-<?=$studentId?>"><?=$currentMember['totalScore']?> (<?=$currentMember['percent']?> %)</td>
			<?=$ko_ispis?>
			</tr><?
		}
			
			?>
		</table>
		<?
	}

} // function izvjestaj_predmet()

?>
