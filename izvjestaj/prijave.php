<?

// IZVJESTAJ/PRIJAVE - generisanje PDFa sa prijavom



function izvjestaj_prijave() {

	require("vendor/autoload.php"); // Koristimo TCPDF
	
	global $userid,$conf_files_path;
	
	// Poslani parametar
	$ispit_termin=intval($_GET['ispit_termin']);
	$predmet=intval($_GET['predmet']);
	$ag=intval($_GET['ag']);
	$student=intval($_GET['student']);

	$nasa_slova = array("č"=>"c", "ć" => "c", "đ" => "d", "š" => "s", "ž" => "z", "Č" => "C", "Ć" => "C", "Đ" => "D", "Š" => "S", "Ž" => "Z");
	
	// Odredjujemo filename
	if ($ispit_termin > 0) {
		$event = api_call("event/$ispit_termin", [ "resolve" => [ "CourseUnit"] ] );
		
		$predmet = $event['CourseUnit']['id'];
		$ag = $event['AcademicYear']['id'];
		$filename = "prijave-".strtr($event['CourseUnit']['name'], $nasa_slova)."-".date("d-m-Y", db_timestamp($event['dateTime'])).".pdf";
		
		// Neefikasno rješenje: povlačimo spisak svih studenata na predmetu
	}
	
	if ($predmet > 0) {
		$course = api_call("course/$predmet/$ag");
		if ($ispit_termin == 0) $filename = "prijave-".strtr($course['courseName'], $nasa_slova).".pdf";
		
		$foundTeacher = "";
		$teachers = 0;
		foreach($course['staff'] as $teacher) {
			if ($teacher['status_id'] == 1) { // professor
				if ($teachers == 1) $foundTeacher .= " / ";
				$foundTeacher .= $teacher['Person']['titlesPre'] . " " . $teacher['Person']['name'] . " " . $teacher['Person']['surname'];
				$teachers++;
			}
		}
		
		$teacherFont = 12;
		if ($teachers == 2) {
			$teacherFont = 10;
		} else if ($teachers != 1) {
			$foundTeacher = "";
		}
		
		$group = api_call("group/course/$predmet/allStudents",
			[ "names" => true, "year" => $ag, "resolve" => [ "CourseOffering", "CourseDescription" ] ]
		);
		usort($group['members'], function($s1, $s2) {
			$s1name = $s1['student']['surname'].$s1['student']['name'];
			$s2name = $s2['student']['surname'].$s2['student']['name'];
			return bssort($s1name,$s2name);
		});
	
	} else {
		biguglyerror("Neispravni parametri");
		print "Da li je moguće da ste odabrali neispravan ili nepostojeći predmet?";
		return;
	}
	
	
	if ($_GET['tip'] == "na_datum" || $_GET['tip'] == "na_datum_sa_ocjenom") {
		// List of events on given date
		$events = api_call("event/course/$predmet/$ag/date/" . $_GET['datum'])["results"];
		$event_students = [];
		foreach($events as $event) {
			$event_students = array_merge($event_students, $event['students']);
		}
	}
	

	// PDF inicijalizacija
	$pdf = new TCPDF('P', 'mm', 'A5', true, 'UTF-8', false);
	
	$pdf->SetCreator("Zamger");
	$pdf->SetTitle('Printanje prijava');
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
	$pdf->SetMargins(0,0,0);
	$pdf->SetAutoPageBreak(false);
	//$pdf->setLanguageArray($l);
	$pdf->SetFont('freesans', 'B', 9);
	$pdf->SetHeaderData("",0,"","");
	$pdf->SetPrintHeader(false);
	$pdf->setFooterMargin($fm=0);
	$pdf->SetPrintFooter(false);
	
	//set image scale factor
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	//$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO*2.083333);
	$pdf->setJPEGQuality(100);
	

	foreach($group['members'] as $member) {
		$foundMember = false;
		
		if ($ispit_termin > 0) {
			foreach($event['students'] as $student) {
				if ($student['id'] == $member['student']['id']) {
					if ($_GET['tip'] != "sa_ocjenom" || $member['grade'])
						$foundMember = $member;
					break;
				}
			}
		}
		else if ($student > 0 && $member['student']['id'] == $student) {
			$foundMember = $member;
			//print "Pronađen student " . $member['student']['name'] . " " . $member['student']['surname'] . "<br>\n";
		}
		else if ($_GET['tip'] == "na_datum" || $_GET['tip'] == "na_datum_sa_ocjenom") {
			foreach($event_students as $student) {
				if ($student['id'] == $member['student']['id']) {
					if ($_GET['tip'] != "na_datum_sa_ocjenom" || $member['grade'])
						$foundMember = $member;
					break;
				}
			}
		}
		else if ($_GET['tip'] == "bez_ocjene" || $_GET['tip'] == "uslov") {
			if (!$member['grade']) {
				$uslov = true;
				if ($_GET['tip'] == "uslov") {
					// All activities are not neccessarily in student score
					// So we use the list from course
					foreach ($course['activities'] as $activity) {
						if ($activity['mandatory']) {
							$hasActivity = false;
							foreach ($member['score'] as $studentScore) {
								if ($studentScore['CourseActivity']['id'] == $activity['id'] && $studentScore['score'] >= $activity['pass']) {
									$hasActivity = true;
									break;
								}
							}
							if (!$hasActivity) {
								$uslov = false;
								break;
							}
						}
					}
				}
				if ($uslov) $foundMember = $member;
			}
		}
		else if ($_GET['tip'] == "sa_ocjenom") {
			if ($member['grade']) $foundMember = $member;
		}
		else if ($_GET['tip'] == "sve") {
			$foundMember = $member;
		}
		
		if (!$foundMember) continue;
		
		$student = $member['student']['id'];
		
		$enrollment = api_call("enrollment/current/$student", [ "resolve" => [ "Programme" ] ]); // Printanje prijava od prošle godine?
		
		$imeprezime = $member['student']['name'] . " " . $member['student']['surname'];
		$brind = $member['student']['studentIdNr'];
		$godStudija = intval(($member['CourseOffering']['semester'] + 1) / 2);
		$odsjek = $enrollment['Programme']['name'];
		$nazivPr = $member['CourseOffering']['CourseDescription']['name'];
		$skolskaGod = $course['AcademicYear']['name'];;
		if ($member['grade'])
			$datumIspita = date("d. m. Y.", db_timestamp($member['gradeDate']));
		else if ($ispit_termin > 0)
			$datumIspita = date("d. m. Y.", db_timestamp($event['dateTime']));
		else if ($_GET['tip'] == "na_datum" || $_GET['tip'] == "na_datum_sa_ocjenom")
			$datumIspita = date("d. m. Y.", db_timestamp($_GET['datum']));
		else {
			$datumIspita = date("d. m. Y.");
			// Get events that student is registered for
			$registeredEvents = api_call("event/registered/$student")["results"];
			foreach($registeredEvents as $evt) {
				if ($evt['CourseUnit']['id'] == $predmet && $evt['AcademicYear']['id'] == $ag)
					$datumIspita = date("d. m. Y.", db_timestamp($evt['dateTime']));
			}
		}
		
		kreirajPrijavu($pdf, $imeprezime, $brind, $godStudija, $odsjek, $nazivPr, $skolskaGod, $datumIspita, $member['grade'], $foundTeacher, $teacherFont);
	}

	$pdf->Output($filename, 'I');

} // function izvjestaj_prijave()



function kreirajPrijavu($pdf, $imeprezime, $brind, $godStudija, $odsjek, $nazivPr, $skolskaGod, $datumIspita, $ocjena, $nastavnik, $nastavnikFont) {
	$datumPrijave=$datumIspita;
	$datumPolaganja=$datumIspita;
	$datumUsmenog=$datumIspita;

	$NastavnikSl = $NastavnikPr = $nastavnik;

	$imeocjene = array("", "", "", "", "", "pet", "šest", "sedam", "osam", "devet", "deset");

	$fontzapredmet=12;
	if (strlen($nazivPr)>40) $fontzapredmet=10;
	if (strlen($nazivPr)>55) $fontzapredmet=8;

	$pdf->AddPage();
	
	$pdf->Image("static/images/content/150dpi/prijava-blanko.png",0,0,148,0,'','','',true,150);
	
	// broj indexa
	$pdf->SetY(20);
	$pdf->SetX(108);
	$pdf->SetFont('freesans','',12);
	$pdf->Cell(30,-136,$brind, 0, 0, 'C');
	
	// naziv ustanove
	$pdf->SetY(30);
	$pdf->SetX(12);
	$pdf->SetFont('freesans','',11);
	$pdf->Cell(90,-136,'ELEKTROTEHNIČKI FAKULTET SARAJEVO', 0, 0, 'C');
	
	/*// redovan1
	$pdf->SetY(32.5);
	$pdf->SetX(101.5);
	$pdf->SetFont('freesans','',12);
	$pdf->Cell(160,-136,"|_____|");
	
	// redovan2
	$pdf->SetY(28.1);
	$pdf->SetX(101.7);
	$pdf->SetFont('freesans','',14);
	$pdf->Cell(160,-136,"_____");
	*/
	
	// ime i prezime studenta
	$pdf->SetY(50);
	$pdf->SetX(12);
	$pdf->SetFont('freesans','',12);
	$pdf->Cell(90,-136,$imeprezime, 0, 0, 'C');
	
	// godina studija
	$pdf->SetY(50);
	$pdf->SetX(108);
	$pdf->SetFont('freesans','',12);
	$pdf->Cell(30,-136,$godStudija, 0, 0, 'C');
	
	// odsjek
	$pdf->SetY(60);
	$pdf->SetX(12);
	$pdf->SetFont('freesans','',12);
	$pdf->Cell(126,-136,$odsjek, 0, 0, 'C');
	
	// predmet
	$pdf->SetY(70);
	$pdf->SetX(12);
	$pdf->SetFont('freesans','',$fontzapredmet);
	$pdf->Cell(80,-136,$nazivPr, 0, 0, 'C');
	
	// koji put izlazite
	$pdf->SetY(70);
	$pdf->SetX(95);
	$pdf->SetFont('freesans','',12);
	$pdf->Cell(44,-136,'1. (prvi)', 0, 0, 'C');
	
	// skolska godina
	$pdf->SetY(80);
	$pdf->SetX(12);
	$pdf->SetFont('freesans','',12);
	$pdf->Cell(42,-136,$skolskaGod, 0, 0, 'C');
	
	// nastavnik kod kojeg se slusa predmet
	$pdf->SetY(80);
	$pdf->SetX(59);
	$pdf->SetFont('freesans','',$nastavnikFont);
	$pdf->Cell(80,-136,$NastavnikSl, 0, 0, 'C');
	
	// datum ispita
	$pdf->SetY(91);
	$pdf->SetX(12);
	$pdf->SetFont('freesans','',12);
	$pdf->Cell(30,-136,$datumIspita, 0, 0, 'C');
	
	// nastavnik kod kojeg se polaze predmet
	$pdf->SetY(91);
	$pdf->SetX(47);
	$pdf->SetFont('freesans','',$nastavnikFont);
	$pdf->Cell(92,-136,$NastavnikPr, 0, 0, 'C');
	
	// datum prijave ispita
	$pdf->SetY(101);
	$pdf->SetX(12);
	$pdf->SetFont('freesans','',12);
	$pdf->Cell(30,-136,$datumPrijave, 0, 0, 'C');
	
	// datum polaganja ispita
	$pdf->SetY(113);
	$pdf->SetX(12);
	$pdf->SetFont('freesans','',12);
	$pdf->Cell(47,-136,$datumIspita, 0, 0, 'C');
	
	/*// ocjena pismenog dijela
	$pdf->SetY(120);
	$pdf->SetX(14);
	$pdf->SetFont('freesans','',12);
	$pdf->Cell(160,-136,$kon);
	*/
	// datum usmenog
	$pdf->SetY(125);
	$pdf->SetX(63);
	$pdf->SetFont('freesans','',12);
	$pdf->Cell(160,-136,$datumUsmenog);

	if ($ocjena>0) {
		// ocjena usmenog dijela
		$pdf->SetY(132);
		$pdf->SetX(63);
		$pdf->SetFont('freesans','',12);
		$pdf->Cell(160,-136,$imeocjene[$ocjena]." ($ocjena)");

		// konacna ocjena
		$pdf->SetY(130);
		$pdf->SetX(108);
		$pdf->SetFont('freesans','',12);
		$pdf->Cell(160,-136,$imeocjene[$ocjena]." ($ocjena)");
	}

	/*
	// datum drugog parcijalnog
	$pdf->SetY(150);
	$pdf->SetX(22);
	$pdf->SetFont('Helvetica','',14);
	$pdf->Cell(160,-136,$datumDrPar);
	
	*/
	//EVIDENCIJA
	
	// ime i prezime studenta
	$pdf->SetY(175);
	$pdf->SetX(12);
	$pdf->SetFont('freesans','',12);
	$pdf->Cell(72,-136,$imeprezime, 0, 0, 'C');
	
	// godina studija
	$pdf->SetY(175);
	$pdf->SetX(89);
	$pdf->SetFont('freesans','',12);
	$pdf->Cell(23,-136,$godStudija, 0, 0, 'C');
	
	// broj indexa
	$pdf->SetY(175);
	$pdf->SetX(115);
	$pdf->SetFont('freesans','',12);
	$pdf->Cell(24,-136,$brind, 0, 0, 'C');
	
	// predmet
	$pdf->SetY(185);
	$pdf->SetX(12);
	$pdf->SetFont('freesans','',$fontzapredmet);
	$pdf->Cell(85,-136,$nazivPr, 0, 0, 'C');
	
	// koji put izlazite
	$pdf->SetY(185);
	$pdf->SetX(100);
	$pdf->SetFont('freesans','',12);
	$pdf->Cell(39,-136,'1. (prvi)', 0, 0, 'C');
	
	// datum usmenog
	$pdf->SetY(194);
	$pdf->SetX(10);
	$pdf->SetFont('freesans','',12);
	$pdf->Cell(160,-136,$datumUsmenog);

	// Konacna ocjena
	if ($ocjena>0) {
		$pdf->SetY(194);
		$pdf->SetX(40);
		$pdf->SetFont('freesans','',12);
		$pdf->Cell(160,-136,$imeocjene[$ocjena]." ($ocjena)");
	}
}

?>
