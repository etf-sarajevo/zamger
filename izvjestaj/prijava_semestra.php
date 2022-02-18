<?php

use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\TemplateProcessor;

function izvjestaj_prijava_semestra() {
	global $userid, $person, $conf_files_path, $_api_http_code;
	
	$student = $userid;
	if (int_param('student') > 0) {
		$student = int_param('student');
		$person = api_call("person/$student", ["resolve" => ["ExtendedPerson", "Place", "Country"]]);
	}
	
	$exp = $person['ExtendedPerson'];
	
	$drzava = [];
	foreach (api_call("person/country/search", [ "query" => "" ] )["results"] as $result)
		$drzava[$result['id']] = $result['name'];
	
	
	$kanton = [
		1 => 'Bosansko-Podrinjski kanton',
		2 => 'Hercegovačko-Neretvanski kanton',
		3 => 'Livanjski kanton',
		4 => 'Posavski kanton',
		5 => 'Sarajevski kanton',
		6 => 'Srednjobosanski kanton',
		7 => 'Tuzlanski kanton',
		8 => 'Unsko-Sanski kanton',
		9 => 'Zapadno-Hercegovački kanton',
		10 => 'Zeničko-Dobojski kanton',
		11 => 'Republika Srpska',
		12 => 'Distrikt Brčko',
		13 => 'Strani državljanin'
	];
	$rimski_brojevi = [ "", " I ", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX", "X" ];
	
	
	$enrollment = api_call("enrollment/current/$student", ["resolve" => ["AcademicYear", "Programme", "Institution", "ProgrammeType", "EnrollmentType"], "getCourses" => true]);
	$currentlyEnrolled = ($_api_http_code == "200");
	$enrollmentContract = api_call("enrollment/contract/$student", ["resolve" => ["AcademicYear", "Programme", "Institution", "ProgrammeType", "EnrollmentType"], "getCourses" => true]);
	$hasContract = ($_api_http_code == "200");
	
	if (param('ugovor') && !$hasContract) {
		niceerror("Student nije popunio Ugovor o učenju");
		?><p>Ako želite možete isprintati Prijavu semestra prema semestru koji je trenutno upisan.</p>
		<p><a href="?sta=izvjestaj/prijava_semestra&student=<?=$student?>">Prijava semestra za trenutno upisani semestar</a></p>
		<?php
		exit(0);
	}
	
	if (param('ugovor') || !$currentlyEnrolled)
		$enrollment = $enrollmentContract;
	
	if (!$currentlyEnrolled && !$hasContract) {
		niceerror("Student nije trenutno upisan niti ima generisan ugovor o učenju");
		print "<p>Da biste mogli preuzeti List o prijavi semestra potrebno je da je ispunjen jedan od ova dva uslova.</p>";
		return;
	}
	
	
	$faculty = 'Elektrotehnički fakultet Sarajevo';
	$place   = 'Sarajevu';
	$index   = $person['studentIdNr'];
	
	$parentName = $exp['fathersName'];
	if (empty($parentName)) $parentName = $exp['mothersName'];
	$fullNameParent = $person['name'] . " ($parentName) " . $person['surname'];
	$fullName = $person['name'] . " " . $person['surname'];
	$semestar = $rimski_brojevi[$enrollment['semester']];
	$department = $enrollment['Programme']['name'];
	
	$jmb = $exp['jmbg'];
	$birthDate = intval(substr($exp['dateOfBirth'], 8)) . ". " . intval(substr($exp['dateOfBirth'], 5, 2)) . ". " . intval(substr($exp['dateOfBirth'], 0, 4));
		
	$birthPlace = $exp['placeOfBirth']['name'] . ", " . $exp['placeOfBirth']['Municipality']['name']; // Ovdje ide Mjesto i općina rođenja
	$birthCountry = "";
	if ($exp['placeOfBirth']['Country']['id'] == 1)
		$birthCountry = $kanton[$exp['placeOfBirth']['Municipality']['Canton']] . ", "; // Ovdje ide kanton i država
	$birthCountry .= $drzava[$exp['placeOfBirth']['Country']['id']];
	
	$citizenship  = $drzava[$exp['nationality']];
	
	$father = $exp['fathersName'] . " " . $exp['fathersSurname'];
	$mother = $exp['mothersName'] . " " . $exp['mothersSurname'];
	
	$residenceAddress = $exp['residenceAddress'] . ", " . $exp['residencePlace']['name'];
	$residencePlace = $exp['residencePlace']['Municipality']['name']; // Ovdje ide Mjesto i općina rođenja
	if ($exp['residencePlace']['Country']['id'] == 1)
		$residencePlace .= ", " . $kanton[$exp['residencePlace']['Municipality']['Canton']];
	
	
	$postalAddress = $exp['addressStreetNo'] . ", " . $exp['addressPlace']['name'];
	$phone = $exp['phone'];
	$email = $person['email'][0]['address'];
	
	$schoolYear = $enrollment['AcademicYear']['name'];
	$date  = date('d.m.Y');
	$date2 = date('d.m');
	$year  = date('Y');
	
	// Kako se student upisuje
	// 1 - redovan, 2 - redovan samofinansirajući, 3 - Vanredan, 4 - DL, 5 - gost
	
	$status = $enrollment['EnrollmentType']['id']; // Redovan
	if ($status>1) $status--; // Preskačemo Paralelan
	$cycle  = $enrollment['Programme']['ProgrammeType']['cycle']; // 1 - Prvi ciklus, 2 - Drugi ciklus, 3 - Treći ciklus || Prvog - Drugog - Trećeg
	
	// Construct course data
	$data = [];
	foreach($enrollment['courses'] as $course) {
		$cd = $course['courseOfferings'][0]['CourseDescription'];
		$teacher = "";
		foreach($course['staff'] as $staffMember)
			if ($staffMember['status_id'] == 1) {
				if (strstr($teacher, "\n")) {
					$teacher = ""; break;
				}
				else if ($teacher != "")
					$teacher .= "</w:t><w:br/><w:t>\n" . $staffMember['Person']['titlesPre'] . " " . $staffMember['Person']['name'] . " " . $staffMember['Person']['surname'];
				else
					$teacher = $staffMember['Person']['titlesPre'] . " " . $staffMember['Person']['name'] . " " . $staffMember['Person']['surname'];
			}
		$entry = [ $course['courseName'], $teacher, ceil($cd['lectureHours'] / 14), ceil(($cd['tutorialHours']+$cd['practiceHours']) / 14), $cd['lectureHours'], $cd['tutorialHours']+$cd['practiceHours'], $cd['ects'] ];
		$data[] = $entry;
	}
	
/*	// TODO -- Napomena za predmete :: Pokušaj dobiti neki array, koji je array of arrays - example ispod
	$data = [
		[
			'Električni krugovi 1', // Predmet
			'prof. dr. Nastavnik',  // Profesor
			'4', // Sedmično predavanja
			'2', // Sedmično vježbi
			'36', // Ukupno predavanja
			'20', // Ukupno vježbi
			'6', // ECTS
		],
		[
			'Drugi',
			'profa',
			'5',
			'3',
			'40',
			'22',
			'3'
		]
	];*/
	
	
	
	/*
	 * 	Path to file
	 */
	$path = $conf_files_path . "/tmp";
	if (!file_exists($path))
		mkdir($path);
	$fileName = $path . "/" . md5(time()) . '.docx';
	
	try {
		/*
		 * Init template processor
		 */
		$templateProcessor = new TemplateProcessor('static/files/prijava_semestra.docx');
		$underline = array('underline' => 'single', 'name' => 'Arial');
		$arial = array('name' => 'Arial');
		$strikethrough = array('strikethrough' => true, 'name' => 'Arial');
		$strikethrough10 = array('strikethrough' => true, 'name' => 'Arial', 'size' => 10);
		
		/*
		 * 	Data
		 */
		for($i=0; $i<count($data); $i++){
			$templateProcessor->setValue('p'.$i,  $data[$i][0]); // Predmet
			$templateProcessor->setValue('n'.$i,  $data[$i][1]); // Nastavnik
			$templateProcessor->setValue('pr'.$i, $data[$i][2]); // Predavanja sedmično
			$templateProcessor->setValue('vj'.$i, $data[$i][3]); // Vježbi sedmično
			$templateProcessor->setValue('pt'.$i, $data[$i][4]); // Predavanja ukupno
			$templateProcessor->setValue('vt'.$i, $data[$i][5]); // Vježbi ukupno
			$templateProcessor->setValue('e'.$i,  $data[$i][6]); // ECTS
		}
		
		/*
		 * 	Now, set rest values to empty string
		 */
		
		for($i = count($data); $i<12; $i++){
			$templateProcessor->setValue('p'.$i, ''); // Predmet
			$templateProcessor->setValue('n'.$i, ''); // Nastavnik
			$templateProcessor->setValue('pr'.$i,''); // Predavanja sedmično
			$templateProcessor->setValue('vj'.$i,''); // Vježbi sedmično
			$templateProcessor->setValue('pt'.$i,''); // Predavanja ukupno
			$templateProcessor->setValue('vt'.$i,''); // Vježbi ukupno
			$templateProcessor->setValue('e'.$i, ''); // ECTS
		}
		
		/*
		 * 	Header
		 */
		$templateProcessor->setValue('faculty', $faculty);
		$templateProcessor->setValue('place', $place);
		$templateProcessor->setValue('index', $index);
		$templateProcessor->setValue('fullNameParent', $fullNameParent);
		$templateProcessor->setValue('fullName', $fullName);
		$templateProcessor->setValue('semestar', $semestar);
		$templateProcessor->setValue('department', $department);
		
		/*
		 * 	Student info
		 */
		
		$templateProcessor->setValue('jmb', $jmb);
		$templateProcessor->setValue('birthDate', $birthDate);
		$templateProcessor->setValue('birthPlace', $birthPlace);
		$templateProcessor->setValue('birthCountry', $birthCountry);
		
		$templateProcessor->setValue('citizenship', $citizenship);
		$templateProcessor->setValue('father', $father);
		$templateProcessor->setValue('mother', $mother);
		$templateProcessor->setValue('residenceAddress', $residenceAddress);
		$templateProcessor->setValue('residencePlace', $residencePlace);
		$templateProcessor->setValue('postalAddress', $postalAddress);
		$templateProcessor->setValue('phone', $phone);
		$templateProcessor->setValue('email', $email);
		
		$templateProcessor->setValue('date', $date);
		$templateProcessor->setValue('date2', $date2);
		$templateProcessor->setValue('year', $year);
		$templateProcessor->setValue('sYear', $schoolYear);
		
		/*
		 * 	Strike trough
		 */
		
		if($status == 1) {
			$templateProcessor->setComplexValue('st1', (new TextRun())->addText('redovan', $strikethrough10));
			$templateProcessor->setComplexValue('st11', (new TextRun())->addText('redovan', $strikethrough10));
		}
		else{
			$templateProcessor->setValue('st1', 'redovan');
			$templateProcessor->setValue('st11', 'redovan');
		}
		if($status == 2) {
			$templateProcessor->setComplexValue('st2', (new TextRun())->addText('redovan samofinansirajući', $strikethrough10));
			$templateProcessor->setComplexValue('st21', (new TextRun())->addText('redovan samofinansirajući', $strikethrough10));
		}
		else {
			$templateProcessor->setValue('st2', 'redovan samofinansirajući');
			$templateProcessor->setValue('st21', 'redovan samofinansirajući');
		}
		if($status == 3) {
			$templateProcessor->setComplexValue('st3', (new TextRun())->addText('Vanredan', $strikethrough10));
			$templateProcessor->setComplexValue('st31', (new TextRun())->addText('Vanredan', $strikethrough10));
		}
		else {
			$templateProcessor->setValue('st3', 'Vanredan');
			$templateProcessor->setValue('st31', 'Vanredan');
		}
		if($status == 4) {
			$templateProcessor->setComplexValue('st4', (new TextRun())->addText('DL', $strikethrough10));
			$templateProcessor->setComplexValue('st41', (new TextRun())->addText('DL', $strikethrough10));
		}
		else {
			$templateProcessor->setValue('st4', 'DL');
			$templateProcessor->setValue('st41', 'DL');
		}
		if($status == 5) { $templateProcessor->setComplexValue('st5', (new TextRun())->addText('gost', $strikethrough10)); }
		else $templateProcessor->setValue('st5', 'gost');
		
		if($cycle == 1) $templateProcessor->setComplexValue('c1', (new TextRun())->addText('Prvog', $strikethrough10));
		else $templateProcessor->setValue('c1', 'Prvog');
		if($cycle == 2) $templateProcessor->setComplexValue('c2', (new TextRun())->addText('Drugog', $strikethrough10));
		else $templateProcessor->setValue('c2', 'Drugog');
		if($cycle == 3) $templateProcessor->setComplexValue('c3', (new TextRun())->addText('Trećeg', $strikethrough10));
		else $templateProcessor->setValue('c3', 'Trećeg');
		
		$templateProcessor->saveAs($fileName);
		
		
		header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
		header('Content-Length: ' . filesize($fileName));
		header('Content-Disposition: attachment; filename="prijava_semestra.docx"', false);
		
		echo file_get_contents($fileName);
	} catch (\PhpOffice\PhpWord\Exception\CreateTemporaryFileException $e) { }
	
}