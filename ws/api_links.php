<?php
require_once 'vendor/autoload.php';

use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\TemplateProcessor;

function returnData($data){	echo json_encode(['results' =>[['id' => '0', 'text' => $data]]]); }

/*
 * 	Return strikethrough data for special cases
 */
function strikeThrough($val, $bold = false){
	$elem = new \PhpOffice\PhpWord\Element\Text();
	$fontStyle = array('size' => 11, 'bold' => $bold, 'strikethrough' => true);
	$elem->setText($val)->setFontStyle($fontStyle);
	
	return $elem;
}

// ------------------------------ TODO - extra validation for SQL injection ----------------------------------------- //

function ws_api_links(){
	
	global $userid, $person;
	$response = array( 'success' => 'true', 'message' => '', 'data' => array() );
	
	/*
	 * 	Select - 2 AJAX requests
	 */
	if(isset($_REQUEST['type'])){
		/** @var $data - array with data */
		$data = [];
		/** @var  $term - keyword for searching */
		$term = $_REQUEST['term'];
		if(empty($term)) returnData("Nema podataka");
		
		else if($_REQUEST['type'] == 's2-place'){
			$query = db_query("select VAJ_SIFRA, SIFRA,NAZIV from adm_jedinice where VAJ_SIFRA = 'M' AND NAZIV LIKE '%$term%' order by naziv");
			while ($row = db_fetch_row($query)){
				$data[] = [
					'id' => $row[1],
					'text' => $row[2]
				];
			}
		}else if($_REQUEST['type'] == 's2-munic'){
			$query = db_query("select NAZIV from mjesta_mkr WHERE NAZIV LIKE '%$term%' order by naziv");
			while ($row = db_fetch_row($query)){
				$data[] = [
					'id' => $row[0],
					'text' => $row[0]
				];
			}
		}
		
		if(!count($data)) $data[] = ['id' => '0', 'text' => 'Nema rezultata'];
		/*
		 *  Todo - ovdje možemo ukoliko neko mjesto ne postoji, vratiti ga kao opciju, pa onda prilikom unosa podataka, ukoliko
		 *  nema u bazi podataka, da se unese novi uzorak, ili stavi na razmatranje ..
		 */
		
		echo json_encode([ 'results' => $data ]);
	}
	
	/*
	 * 	Download SV-20 document
	 */
	
	else if($_REQUEST['download_sv_20']){

		$yearFrom      = '2021';
		$yearTo        = '2022';
		
		$faculty       = 'Elektrotenički Fakultet';
		$department    = 'Automatika i elektronika hehe';
		$canton        = 'Kanton Sarajevo';
		$address       = 'Zmaja od Bosne bb';
		$municipality  = 'Novo Sarajevo';
		
		$fullName   = 'John Doe';
		$gender     = 'M';
		$birthPlace = 'Centar, Sarajevo';
		
		$birthDay   = '03';
		$birthMonth = '05';
		$birthYear  = '1994';
		
		$citizenship = 'BiH i drugo';
		$country     = 'Bosna i Hercegovina'; // If it is not BiH, then what it is (name of country)
		$ethnicity   = 'Bošnjo';
		
		$res_country      = 'Bosna i Hercegovina';
		$res_canton       = 'Kanton Sarajevo';
		$res_municipality = 'Elidža';
		$res_address      = 'Bregovi 8';
		
		$address_place    = 'Muhameda ef. Pandže 55, Općina Centar'; // Address and municipality
		$phone            = '38761683449';
		$email            = 'john.doe@hotmail.com';
		
		$cycle            = 'Prvi ciklus'; // Prvi ciklus, Drugi ciklus, Treći ciklus
		$studyYear        = 'IV'; // I, II, III, IV, V, VI
		$again            = 'Da'; // Da, Ne
		
		$studyType        = 'redovan'; // redovan, samofinancirajući, vanredan
		$enrollmentYear   = '2021';
		$last_education   = 'Gimnazija, Cazin';
		$last_education_y = '2018';
		
		$sourceOfFunding  = 'roditelji';    // roditelji, primate plaću iz radnog odnosa, primate stipendiju, kredit, ostalo
		$activityStatusParent  = 'zaposlen'; // zaposlen, nezaposlen, neaktivan
		$activityStatusStudent = 'zaposlen'; // zaposlen, nezaposlen, neaktivan
		
		$occupationParent  = 'Roditelj full time';
		$occupationStudent = 'Student full time';
		
		$employmentStatusParent  = 'zaposlenik'; // poslodavac/samozaposlenik, zaposlenik, pomažući član porodice
		$employmentStatusStudent = 'zaposlenik'; // poslodavac/samozaposlenik, zaposlenik, pomažući član porodice
		/*
		 * 	Path to file
		 */
		$fileName = 'static/files/sv-20/files/'.md5(time()).'.docx';
		
		try {
			/*
			 * Init template processor
			 */
			$templateProcessor = new TemplateProcessor('static/files/sv-20/sv-20.docx');
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
			if($studyType == 'samofinancirajući'){ $templateProcessor->setComplexValue('st_s', (new TextRun())->addText('samofnancirajući', $strikethrough9)); }
			else $templateProcessor->setValue('st_s', 'samofinancirajući');
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
			echo json_encode([
				'code' => '0000',
				'file' => $fileName
			]);
		} catch (\PhpOffice\PhpWord\Exception\CopyFileException $e) {
		} catch (\PhpOffice\PhpWord\Exception\CreateTemporaryFileException $e) {
		}
	}
	
	/*
	 * 	Upload image
	 */
	
	else if($_REQUEST['category']){
		
		$target_dir = $_REQUEST['path'];
		$target_file = $target_dir . basename($_FILES["photo-input"]["name"]);
		$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

		$name = md5(time()).'.'.$imageFileType;
		
		// Check if image file is a actual image or fake image
		$check = getimagesize($_FILES["photo-input"]["tmp_name"]);
		if($check !== false) {
			if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" ) {
				$data[] = ['code' => '4004', 'message' => 'Samo .jpg, .png i .jpeg formati slike su dozvoljeni!'];
			}else{
				if(move_uploaded_file($_FILES["photo-input"]["tmp_name"], $target_dir.$name)){
					$data = ['code' => '0000', 'photo' => $name];
				}else{
					$data[] = ['code' => '4004', 'message' => 'Desila se greška prilikom spremanja !'];
				}
			}
		} else {
			$data[] = ['code' => '4004', 'message' => 'Spremljeni dokument nema formu slike !'];
		}
		
		echo json_encode($data);
	}
	
	else if($_REQUEST['uploadImage']){
		$name = $_REQUEST['image'];
		$id   = $person['id'];
		
		$update = db_query("UPDATE osoba SET slika = '".$name."' where id = $id");
		
		echo json_encode([
			'code' => '0000',
			'message' => 'Slika profila uspješno ažurirana !'
		]);
	}
	
	/*
	 * 	Final result for select-2 AJAX requests -- if there is no data, or some kind of error occurs, it would
	 * 	return a json object with message "No data"
	 */
	
	else{
		returnData("Nema podataka !");
	}
}
