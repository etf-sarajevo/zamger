<?php


use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\TemplateProcessor;

function izvjestaj_upisni_list() {
	global $userid, $person, $conf_files_path, $_api_http_code;
	
	$student = $userid;
	if (int_param('student') > 0) {
		$student = int_param('student');
		$person = api_call("person/$student", ["resolve" => ["ExtendedPerson", "Place", "Country"]]);
	}
	
	$exp = $person['ExtendedPerson'];
	
	$opcina = [];
	foreach (api_call("person/municipality/search", [ "query" => "" ] )["results"] as $result)
		$opcina[$result['id']] = $result['name'];
	$drzava = [];
	foreach (api_call("person/country/search", [ "query" => "" ] )["results"] as $result)
		$drzava[$result['id']] = $result['name'];
	$allYears = [];
	foreach (api_call("zamger/year")["results"] as $result)
		$allYears[$result['id']] = $result['name'];
	
	
	$nacionalnost = [
		"1" => "Bošnjak/Bošnjakinja",
		"2" => "Srbin/Srpkinja",
		"3" => "Hrvat/Hrvatica",
		"4" => "Rom/Romkinja",
		"5" => "Ostalo",
		"6" => "Nepoznato / Nije se izjasnio/la",
		"9" => "Bosanac/Bosanka",
		"10" => "BiH",
		"11" => "Musliman/Muslimanka"
	];
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
	$fullName = $person['name'] . " ($parentName) " . $person['surname'];
	$semestar = $rimski_brojevi[$enrollment['semester']];
	$department = $enrollment['Programme']['name'];
	
	$jmb = $exp['jmbg'];
	$birthDate = intval(substr($exp['dateOfBirth'], 8)) . ". " . intval(substr($exp['dateOfBirth'], 5, 2)) . ". " . intval(substr($exp['dateOfBirth'], 0, 4));
	
	$birthPlace = $exp['placeOfBirth']['name'] . ", " . $exp['placeOfBirth']['Municipality']['name']; // Ovdje ide Mjesto i općina rođenja
	$birthCountry = "";
	if ($exp['placeOfBirth']['Country']['id'] == 1)
		//$birthCountry = $kanton[$exp['placeOfBirth']['Municipality']['Canton']] . ", "; // Ovdje ide kanton i država
		$birthCountry = $kanton[$exp['placeOfBirth']['Municipality']['Canton']] . ", "; // Ovdje ide kanton i država
	$birthCountry .= $drzava[$exp['placeOfBirth']['Country']['id']];
	
	$citizenship  = $drzava[$exp['nationality']];
	$ethnicity  = $nacionalnost[$exp['ethnicity']];

	$highSchool = $opcina[ $exp['previousEducation'][0]['School']['Municipality']['id'] ] . ", ";
	list($nesto, $godina) = explode("/", $allYears[ $exp['previousEducation'][0]['yearCompleted']['id'] ]);
	$highSchool .= $godina;
	
	$father = $exp['fathersName'] . " " . $exp['fathersSurname'];
	$mother = $exp['mothersName'] . " " . $exp['mothersSurname'];
	$fatherOccupation = $exp['occupationParent'];
	
	$residenceAddress = $exp['residenceAddress'];
	$residencePlace = $exp['residencePlace']['name'] . ", " . $exp['residencePlace']['Municipality']['name']; // Ovdje ide Mjesto i općina rođenja
	if ($exp['residencePlace']['Country']['id'] == 1)
		$residencePlace = ", " . $kanton[$exp['residencePlace']['Municipality']['Canton']];
	$postalAddress = $exp['addressStreetNo'] . ", " . $exp['addressPlace']['name'];
	$phone = $exp['phone'];
	$email = $person['email'][0]['address'];

	$date = date('d.m.Y');
	$year = date('Y');
	
	// Kako se student upisuje
	// 1 - redovan, 2 - redovan samofinansirajući, 3 - Vanredan, 4 - DL, 5 - gost
	
	$status = $enrollment['EnrollmentType']['id']; // Redovan
	if ($status>1) $status--; // Preskačemo Paralelan
	$cycle  = $enrollment['Programme']['ProgrammeType']['cycle']; // 1 - Prvi ciklus, 2 - Drugi ciklus, 3 - Treći ciklus || Prvog - Drugog - Trećeg
	
	
	/*
	 * 	Path to file
	 */
	$path = $conf_files_path . "/tmp";
	if (!file_exists($path))
		mkdir($path);
	$fileName = $path . "/" . md5(time()) . '.docx';
	
	$templateProcessor = new TemplateProcessor('static/files/upisni_list.docx');
	$underline = array('underline' => 'single', 'name' => 'Arial');
	$arial = array('name' => 'Arial');
	$strikethrough = array('strikethrough' => true, 'name' => 'Arial');
	$strikethrough10 = array('strikethrough' => true, 'name' => 'Arial', 'size' => 10);
	
	/*
	 * 	Header
	 */
	$templateProcessor->setValue('faculty', $faculty);
	$templateProcessor->setValue('place', $place);
	$templateProcessor->setValue('index', $index);
	$templateProcessor->setValue('fullName', $fullName);
	$templateProcessor->setValue('semestar', $semestar);
	$templateProcessor->setValue('department', $department);
	
	/*
	 * 	Student
	 */
	
	$templateProcessor->setValue('jmb', $jmb);
	$templateProcessor->setValue('birthDate', $birthDate);
	$templateProcessor->setValue('birthPlace', $birthPlace);
	$templateProcessor->setValue('birthCountry', $birthCountry);
	
	$templateProcessor->setValue('citizenship', $citizenship);
	$templateProcessor->setValue('nationality', $ethnicity);
	$templateProcessor->setValue('highSchool', $highSchool);
	$templateProcessor->setValue('father', $father);
	$templateProcessor->setValue('mother', $mother);
	$templateProcessor->setValue('fatherOccupation', $fatherOccupation);
	$templateProcessor->setValue('motherOccupation', $motherOccupation);
	$templateProcessor->setValue('residenceAddress', $residenceAddress);
	$templateProcessor->setValue('residencePlace', $residencePlace);
	$templateProcessor->setValue('postalAddress', $postalAddress);
	$templateProcessor->setValue('phone', $phone);
	$templateProcessor->setValue('email', $email);
	
	$templateProcessor->setValue('date', $date);
	$templateProcessor->setValue('year', $year);
	
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
	header('Content-Disposition: attachment; filename="upisni_list.docx"', false);
	
	echo file_get_contents($fileName);
}