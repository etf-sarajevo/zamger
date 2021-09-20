<?

use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\TemplateProcessor;

/*
 * 	Return strikethrough data for special cases
 */
function strikeThrough($val, $bold = false){
	$elem = new \PhpOffice\PhpWord\Element\Text();
	$fontStyle = array('size' => 11, 'bold' => $bold, 'strikethrough' => true);
	$elem->setText($val)->setFontStyle($fontStyle);
	
	return $elem;
}

function izvjestaj_sv20() {
	global $userid, $person, $conf_files_path, $_api_http_code;
	
	$student = $userid;
	if (int_param('student') > 0) {
		$student = int_param('student');
		$person = api_call("person/$student", [ "resolve" => [ "ExtendedPerson", "Place", "Country" ] ] );
	}
	
	$exp = $person['ExtendedPerson'];
	
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
	$imena_ciklusa = [ "", "Prvi ciklus", "Drugi ciklus", "Treći ciklus" ];
	$rimski_brojevi = [ "", "I", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX", "X" ];
	$nacini_studija = [ "", "redovan", "paralelan", "samofinansirajući", "vanredan", "dl" ];
	$izvoriFinansiranja = [ "", "roditelji", "primate plaću iz radnog odnosa", "primate stipendiju", "kredit", "ostalo" ];
	$statusAktivnosti = [ "", "zaposlen", "nezaposlen", "neaktivan" ];
	$statusZaposlenosti = [ "", "poslodavac/samozaposlenik", "zaposlenik", "pomažući član porodice" ] ;
	
	if (param('ugovor')) {
		$enrollment = api_call("enrollment/contract/$student", ["resolve" => ["AcademicYear", "Programme", "Institution", "ProgrammeType", "EnrollmentType"]]);
		if ($_api_http_code != "200")
			$enrollment = api_call("enrollment/current/$student", [ "resolve" => ["AcademicYear", "Programme", "Institution", "ProgrammeType", "EnrollmentType"]]);
	} else
		$enrollment = api_call("enrollment/current/$student", [ "resolve" => ["AcademicYear", "Programme", "Institution", "ProgrammeType", "EnrollmentType"]]);
	
	list($yearFrom, $yearTo) = explode("/", $enrollment['AcademicYear']['name']);
		
	$faculty       = 'Elektrotehnički fakultet Sarajevo';
	$department    = $enrollment['Programme']['name'];
	$canton        = 'Kanton Sarajevo';
	$address       = 'Zmaja od Bosne bb, 71000 Sarajevo';
	$municipality  = 'Novo Sarajevo';
	
	$fullName   = $person['name'] . " " . $person['surname'];
	$gender     = $exp['sex'];
	$birthPlace = $exp['placeOfBirth']['name'] . ", " . $exp['placeOfBirth']['Municipality']['name'];
	
	$birthDay   = substr($exp['dateOfBirth'], 8);
	$birthMonth = substr($exp['dateOfBirth'], 6, 2);
	$birthYear  = substr($exp['dateOfBirth'], 0, 4);
	
	if ($exp['nationality'] == 1) { // Bosna i Hercegovina
		$citizenship = 'BiH';
		$country     = 'Bosna i Hercegovina'; // If it is not BiH, then what it is (name of country)
		$ethnicity   = $nacionalnost[$exp['ethnicity']];
	} else {
		$citizenship = 'Strano';
		$country     = $drzava[$exp['nationality']];
		$ethnicity   = '';
	}
	
	$res_country      = $drzava[$exp['residencePlace']['Country']['id']];
	$res_canton       = $kanton[$exp['residencePlace']['Municipality']['Canton']];
	$res_municipality = $exp['residencePlace']['Municipality']['name'];
	$res_address      = $exp['residenceAddress'] . ", " . $exp['residencePlace']['name'];
	
	$address_place    = $exp['addressStreetNo'] . ", " . $exp['addressPlace']['name'];
	$phone            = $exp['phone'];
	$email            = $person['email'][0]['address'];
		
	$cycle            = $imena_ciklusa[$enrollment['Programme']['ProgrammeType']['cycle']];
	$godina           = floor(($enrollment['semester'] + 1) / 2);
	$studyYear        = $rimski_brojevi[$godina];
	$again            = $enrollment['repeat'] ? 'Da' : 'Ne';
	
	$allEnrollments = api_call("enrollment/all/$userid")["results"];
	$lastPrevEd = $exp['previousEducation'][ count($exp['previousEducation']) - 1 ];
	
	$studyType        = $nacini_studija[$enrollment['EnrollmentType']['id']];
	$enrollmentYear   = explode("/", $allYears[ $allEnrollments[0]['AcademicYear']['id'] ] )[0];
	$last_education   = $lastPrevEd['School']['name'];
	$last_education_y = explode("/", $allYears[ $lastPrevEd['yearCompleted']['id'] ])[1];
		
	$sourceOfFunding  = $izvoriFinansiranja[$exp['sourceOfFunding']];
	$activityStatusParent  = $statusAktivnosti[$exp['activityStatusParent']];
	$activityStatusStudent = $statusAktivnosti[$exp['activityStatusStudent']];
		
	$occupationParent  = $exp['occupationParent'];
	$occupationStudent = $exp['occupationStudent'];
		
	$employmentStatusParent  = $statusZaposlenosti[$exp['employmentStatusParent']];
	$employmentStatusStudent = $statusZaposlenosti[$exp['employmentStatusStudent']];
	
	/*
	 * 	Path to file
	 */
	$path = $conf_files_path . "/sv20";
	if (!file_exists($path))
		mkdir($path);
	$fileName = $path . "/" . md5(time()) . '.docx';
	
	try {
		/*
		 * Init template processor
		 */
		$templateProcessor = new TemplateProcessor('static/files/sv-20.docx');
		$underline = array('underline' => 'single', 'name' => 'Arial');
		$arial     = array('name' => 'Arial');
		$strikethrough   =  array('strikethrough' => true, 'name' => 'Arial');
		$strikethrough9  =  array('strikethrough' => true, 'name' => 'Arial', 'size' => 9);
		$strikethrough10 =  array('strikethrough' => true, 'name' => 'Arial', 'size' => 10);
		$strikethroughb  =  array('strikethrough' => true, 'name' => 'Arial', 'bold' => true);
		$bold            =  array('bold' => true, 'name' => 'Arial');
		
		/*
		 * 	Faculty (school) informations
		 */
		$templateProcessor->setComplexValue('y_f', (new TextRun())->addText($yearFrom, $underline));
		$templateProcessor->setComplexValue('y_t', (new TextRun())->addText($yearTo, $underline));
		
		$templateProcessor->setValue('faculty', $faculty);
		$templateProcessor->setValue('department', $department);
		$templateProcessor->setValue('canton', $canton);
		$templateProcessor->setValue('address', $address);
		$templateProcessor->setValue('municipality', $municipality);
		
		/*
		 * 	Student informations
		 */
		
		// Student name and surname
		$templateProcessor->setValue('full_name', $fullName);
		// Student gender : Male or Female
		if($gender == 'M'){
			$templateProcessor->setComplexValue('male', (new TextRun())->addText('1. Muški', $strikethrough));
			$templateProcessor->setValue('female', '2. Ženski');
		}else{
			$templateProcessor->setComplexValue('female', (new TextRun())->addText('2. Ženski', $strikethrough));
			$templateProcessor->setValue('male', '1. Muški');
		}
		// Birth place and date
		$templateProcessor->setValue('birth_place', $birthPlace);
		$templateProcessor->setComplexValue('b_d', (new TextRun())->addText('__'.$birthDay  .'__', $underline));
		$templateProcessor->setComplexValue('b_m', (new TextRun())->addText('__'.$birthMonth.'__', $underline));
		$templateProcessor->setComplexValue('b_y', (new TextRun())->addText('_' .$birthYear .'_', $underline));
		
		if($citizenship == 'BiH'){
			$templateProcessor->setComplexValue('cit_f', (new TextRun())->addText('1. BiH', $strikethrough));
			$templateProcessor->setValue('cit_s', '2. BiH i drugo');
			$templateProcessor->setValue('cit_t', '3 - Strano');
			$templateProcessor->setValue('citizenship', '');
		}else if($citizenship == 'BiH i drugo'){
			$templateProcessor->setValue('cit_f', '1 - BiH');
			$templateProcessor->setComplexValue('cit_s', (new TextRun())->addText('2. BiH i drugo', $strikethrough));
			$templateProcessor->setValue('cit_t', '3 - Strano');
			// Name of country
			$templateProcessor->setValue('citizenship', $country);
		}else{
			$templateProcessor->setValue('cit_f', '1 - BiH');
			$templateProcessor->setValue('cit_s', '2. BiH i drugo');
			$templateProcessor->setComplexValue('cit_t', (new TextRun())->addText('3 - Strano', $strikethrough));
			// Name of country
			$templateProcessor->setValue('citizenship', $country);
		}
		// Ethnicality - Bošnjak, Srbin, Hrvat, etc
		$templateProcessor->setValue('ethnicity', $ethnicity);
		// Residence place
		$templateProcessor->setValue('res_country', $res_country);
		$templateProcessor->setValue('res_canton', $res_canton);
		$templateProcessor->setValue('res_municipality', $res_municipality);
		$templateProcessor->setValue('res_address', $res_address);
		
		$templateProcessor->setValue('address_place', $address_place);
		$templateProcessor->setValue('phone', $phone);
		$templateProcessor->setValue('email', $email);
		
		/*
		 * 	Enrollment data
		 */
		// Study cycle
		if($cycle == 'Prvi ciklus'){
			$templateProcessor->setComplexValue('cyc_f', (new TextRun())->addText('Prvi ciklus', $strikethrough9));
			$templateProcessor->setValue('cyc_s', 'Drugi ciklus');
			$templateProcessor->setValue('cyc_t', 'Treći ciklus');
		}else if($citizenship == 'Drugi ciklus '){
			$templateProcessor->setValue('cyc_f', 'Prvi ciklus');
			$templateProcessor->setComplexValue('cyc_s', (new TextRun())->addText('Drugi ciklus', $strikethrough9));
			$templateProcessor->setValue('cyc_t', 'Treći ciklus');
		}else{
			$templateProcessor->setValue('cyc_f', 'Prvi ciklus');
			$templateProcessor->setValue('cyc_s', 'Drugi ciklus');
			$templateProcessor->setComplexValue('cyc_t', (new TextRun())->addText('Treći ciklus', $strikethrough9));
		}
		// Study year
		
		if($studyYear == 'I'){ $templateProcessor->setComplexValue('f',  (new TextRun())->addText('I', $strikethroughb)); } // TODO - check
		else $templateProcessor->setComplexValue('f',  (new TextRun())->addText('I',  $bold));
		if($studyYear == 'II'){ $templateProcessor->setComplexValue('s',  (new TextRun())->addText('II', $strikethroughb)); }
		else $templateProcessor->setComplexValue('s',  (new TextRun())->addText('II',  $bold));
		if($studyYear == 'III'){ $templateProcessor->setComplexValue('t',  (new TextRun())->addText('III', $strikethroughb)); }
		else $templateProcessor->setComplexValue('t',  (new TextRun())->addText('III',  $bold));
		if($studyYear == 'IV'){ $templateProcessor->setComplexValue('ft',  (new TextRun())->addText('IV', $strikethroughb)); }
		else $templateProcessor->setComplexValue('ft',  (new TextRun())->addText('IV',  $bold));
		if($studyYear == 'V'){ $templateProcessor->setComplexValue('ff',  (new TextRun())->addText('V', $strikethroughb)); }
		else $templateProcessor->setComplexValue('ff',  (new TextRun())->addText('V',  $bold));
		if($studyYear == 'VI'){ $templateProcessor->setComplexValue('sx',  (new TextRun())->addText('VI', $strikethroughb)); }
		else $templateProcessor->setComplexValue('sx',  (new TextRun())->addText('VI',  $bold));
		
		if($again == 'Da'){ $templateProcessor->setComplexValue('ay', (new TextRun())->addText('Da', $strikethrough10)); } // TODO - check
		else $templateProcessor->setValue('ay', 'Da');
		if($again == 'Ne'){ $templateProcessor->setComplexValue('an', (new TextRun())->addText('Ne', $strikethrough10)); }
		else $templateProcessor->setValue('an', 'Ne');
		
		if($studyType == 'redovan'){ $templateProcessor->setComplexValue('st_f', (new TextRun())->addText('redovan', $strikethrough9)); } // TODO - check
		else $templateProcessor->setValue('st_f', 'redovan');
		if($studyType == 'samofinancirajući'){ $templateProcessor->setComplexValue('st_s', (new TextRun())->addText('samofinansirajući', $strikethrough9)); }
		else $templateProcessor->setValue('st_s', 'samofinansirajući');
		if($studyType == 'vanredan'){ $templateProcessor->setComplexValue('st_t', (new TextRun())->addText('vanredan', $strikethrough9)); }
		else $templateProcessor->setValue('st_t', 'vanredan');
		
		// Last education
		$templateProcessor->setValue('e_y', $enrollmentYear);
		$templateProcessor->setValue('last_education', $last_education);
		$templateProcessor->setValue('last_education_y', $last_education_y);
		
		
		if($sourceOfFunding == 'roditelji') { $templateProcessor->setComplexValue('sf_parent', (new TextRun())->addText('roditelji', $strikethrough9)); } // TODO - check
		else $templateProcessor->setValue('sf_parent', 'roditelji');
		if($sourceOfFunding == 'plata') { $templateProcessor->setComplexValue('sf_payment', (new TextRun())->addText('primate plaću iz radnog odnosa', $strikethrough9)); } // TODO - check
		else $templateProcessor->setValue('sf_payment', 'primate plaću iz radnog odnosa');
		if($sourceOfFunding == 'stipendija') { $templateProcessor->setComplexValue('sf_st', (new TextRun())->addText('primate stipendiju', $strikethrough9)); } // TODO - check
		else $templateProcessor->setValue('sf_st', 'primate stipendiju');
		if($sourceOfFunding == 'kredit') { $templateProcessor->setComplexValue('sf_loan', (new TextRun())->addText('kredit', $strikethrough9)); } // TODO - check
		else $templateProcessor->setValue('sf_loan', 'kredit');
		if($sourceOfFunding == 'ostalo') { $templateProcessor->setComplexValue('sf_r', (new TextRun())->addText('ostalo', $strikethrough9)); } // TODO - check
		else $templateProcessor->setValue('sf_r', 'ostalo');
		
		if($activityStatusParent == 'zaposlen') { $templateProcessor->setComplexValue('asp_f', (new TextRun())->addText('zaposlen', $strikethrough9)); } // TODO - check
		else $templateProcessor->setValue('asp_f', 'zaposlen');
		if($activityStatusParent == 'nezaposlen') { $templateProcessor->setComplexValue('asp_s', (new TextRun())->addText('nezaposlen', $strikethrough9)); } // TODO - check
		else $templateProcessor->setValue('asp_s', 'nezaposlen');
		if($activityStatusParent == 'neaktivan') { $templateProcessor->setComplexValue('asp_t', (new TextRun())->addText('neaktivan', $strikethrough9)); } // TODO - check
		else $templateProcessor->setValue('asp_t', 'neaktivan');
		
		if($activityStatusStudent == 'zaposlen') { $templateProcessor->setComplexValue('ass_f', (new TextRun())->addText('zaposlen', $strikethrough9)); } // TODO - check
		else $templateProcessor->setValue('ass_f', 'zaposlen');
		if($activityStatusStudent == 'nezaposlen') { $templateProcessor->setComplexValue('ass_s', (new TextRun())->addText('nezaposlen', $strikethrough9)); } // TODO - check
		else $templateProcessor->setValue('ass_s', 'nezaposlen');
		if($activityStatusStudent == 'neaktivan') { $templateProcessor->setComplexValue('ass_t', (new TextRun())->addText('neaktivan', $strikethrough9)); } // TODO - check
		else $templateProcessor->setValue('ass_t', 'neaktivan');
		
		$templateProcessor->setValue('occupationParent', $occupationParent);
		$templateProcessor->setValue('occupationStudent', $occupationStudent);
		
		
		if($activityStatusParent == 'zaposlen'){
			if($employmentStatusParent == 'poslodavac/samozaposlenik') { $templateProcessor->setComplexValue('emp_f', (new TextRun())->addText('poslodavac/samozaposlenik', $strikethrough9)); } // TODO - check
			else $templateProcessor->setValue('emp_f', 'poslodavac/samozaposlenik');
			if($employmentStatusParent == 'zaposlenik') { $templateProcessor->setComplexValue('emp_s', (new TextRun())->addText('zaposlenik', $strikethrough9)); } // TODO - check
			else $templateProcessor->setValue('emp_s', 'zaposlenik');
			if($employmentStatusParent == 'pomažući član porodice') { $templateProcessor->setComplexValue('emp_t', (new TextRun())->addText('pomažući član porodice', $strikethrough9)); } // TODO - check
			else $templateProcessor->setValue('emp_t', 'pomažući član porodice');
		}else{
			$templateProcessor->setValue('emp_f', 'poslodavac/samozaposlenik');
			$templateProcessor->setValue('emp_s', 'zaposlenik');
			$templateProcessor->setValue('emp_t', 'pomažući član porodice');
		}
		
		if($activityStatusStudent == 'zaposlen'){
			if($employmentStatusStudent == 'poslodavac/samozaposlenik') { $templateProcessor->setComplexValue('ems_f', (new TextRun())->addText('poslodavac/samozaposlenik', $strikethrough9)); } // TODO - check
			else $templateProcessor->setValue('ems_f', 'poslodavac/samozaposlenik');
			if($employmentStatusStudent == 'zaposlenik') { $templateProcessor->setComplexValue('ems_s', (new TextRun())->addText('zaposlenik', $strikethrough9)); } // TODO - check
			else $templateProcessor->setValue('ems_s', 'zaposlenik');
			if($employmentStatusStudent == 'pomažući član porodice') { $templateProcessor->setComplexValue('ems_t', (new TextRun())->addText('pomažući član porodice', $strikethrough9)); } // TODO - check
			else $templateProcessor->setValue('ems_t', 'pomažući član porodice');
		}else{
			$templateProcessor->setValue('ems_t', 'pomažući član porodice');
			$templateProcessor->setValue('ems_s', 'zaposlenik');
			$templateProcessor->setValue('ems_f', 'poslodavac/samozaposlenik');
		}
		
		
		$templateProcessor->setValue('day',  date('d').'.'.date('m'));
		$templateProcessor->setValue('year', date('Y'));
		/*
		 * 	Save file in static/files/sv-20/files/hashed_timestamp.docx
		 * 	After that, return filename for download
		 */
		$templateProcessor->saveAs($fileName);
		
		
		header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
		header('Content-Length: ' . filesize($fileName));
		header('Content-Disposition: attachment; filename="sv-20.docx"', false);
		
		echo file_get_contents($fileName);
		
		echo json_encode([
			'code' => '0000',
			'file' => $fileName
		]);
	} catch (\PhpOffice\PhpWord\Exception\CopyFileException $e) {
	} catch (\PhpOffice\PhpWord\Exception\CreateTemporaryFileException $e) {
	}
	
}
