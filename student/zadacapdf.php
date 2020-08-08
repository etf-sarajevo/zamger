<?

// STUDENT/ZADACAPDF - ispis zadace u PDF formatu
// Preraditi kod!


function student_zadacapdf() {

	global $userid, $person, $_api_http_code, $conf_files_path;
	
	require("vendor/autoload.php"); // Koristimo: TCPDF, Pclzip, Geshi
	
	
	# Poslani parametar:
	$zadaca = intval($_GET['zadaca']);
	$predmet = intval($_GET['predmet']);
	$ag = intval($_GET['ag']);
	
	if ($zadaca == 0 || $predmet == 0 || $ag == 0) {
		biguglyerror("Ova zadaća nije na odabranom predmetu.");
		return;
	}
	
	// Prikupljamo sve zadatke iz ove zadaće koje je student poslao
	$assignments = api_call("homework/course/$predmet/student/$userid", ["resolve" => ["Homework"], "year" => $ag, "submittedTime" => true ])['results'];
	if ($_api_http_code == "404") {
		biguglyerror("Ova zadaća nije na odabranom predmetu.");
		return;
	}
	
	$homework = [];
	$homeworkAssignments = [];
	$bodova_zadaca = 0;
	foreach($assignments as $assignment) {
		if($assignment['Homework']['id'] == $zadaca) {
			$homework = $assignment['Homework'];
			$homeworkAssignments[$assignment['assignNo']] = $assignment;
			if ($assignment['status'] == 5) // status 5 - ocijenjeno
				$bodova_zadaca += $assignment['score'];
		}
	}
	
	// Ako je niz prazan, znaći da zadaća nije na predmetu
	if (empty($homeworkAssignments)) {
		biguglyerror("Ova zadaća nije iz vašeg predmeta!?");
		return;
	}
	
	// Podaci o zadaći
	$imepredmeta = getCourseName($predmet, $ag); // Ovo bi moralo raditi jer smo već provjerili da predmet postoji i student je upisan na njega
	$imezad = $homework['name'];

	//$lokacijazadaca="$conf_files_path/zadace/$predmet-$ag/$userid/$zadaca/";
	
	// Podaci o studentu
	$ime = $person['name'];
	$prezime = $person['surname'];
	$brindexa = $person['studentIdNr'];

	// Određujemo naziv labgrupe
	$groups = api_call("group/course/$predmet/student/$userid", ["year" => $ag ])['results'];
	$labgrupa = "";
	foreach($groups as $group)
		if (!$group['virtual'])
			$labgrupa = $group['name'];

	
	// Extend the TCPDF class to create custom Header and Footer
	class MYPDF extends TCPDF {
	
		//Page header
		public function Header() {
			$this->SetMargins(10,35,25,true);
			$this->Image("static/images/etf-100x100.png",10,8,20);
			$this->Image("static/images/unsa.png",180,8,20);
				$this->SetFont("DejaVu Sans",'',10);
			$this->SetY(15);
			$this->SetX(80);
			$this->Cell(50,5,'UNIVERZITET U SARAJEVU',0,0,'C');
			$this->Ln();
			$this->SetX(80);
			$this->Cell(50,5,'ELEKTROTEHNIČKI FAKULTET',0,0,'C');
			$this->Ln();
			$this->Cell(190,5,'','B',0,'C');
			$this->Ln();
			$this->Ln();
		}
		
		// Page footer
		public function Footer() {
			if ($this->PageNo() > 1) {
				//Position at 1.5 cm from bottom
				$this->SetY(-15);
				//Arial italic 8
				$this->SetFont('DejaVu Sans B','I',8);
				//Text color in gray
				$this->SetTextColor(128);
				//Page number
				$this->Cell(0,10,'Stranica '.$this->PageNo(),0,0,'C');
			}
		}
	}
	
	// Prva stranica
	$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	
	$pdf->AddFont("DejaVuSans","","DejaVuSans.php");
	$pdf->AddFont("DejaVuSans","","DejaVuSans-Bold.php");
	$pdf->AddPage();
	
	$pdf->SetY(100);
	$pdf->SetFont('DejaVuSans','',30);
	$pdf->Cell(190,10,$imezad,0,0,'C');
	$pdf->Ln();
	$pdf->SetFont('DejaVuSans','',16);
	$pdf->Cell(190,10,$imepredmeta,0,0,'C');
	
	$pdf->SetY(-90);
	$pdf->SetFont('DejaVu Sans','',12);
	$pdf->Cell(40,10,'Student:');
	$pdf->SetFont('DejaVu Sans B','',12);
	$pdf->Cell(24,10,$ime.' '.$prezime,0,0,'C');
	$pdf->Ln();
	
	if ($labgrupa != "") {
		$pdf->SetFont('DejaVu Sans','',12);
		$pdf->Cell(40,10,'Grupa:');
		$pdf->SetFont('DejaVu Sans','',12);
		$pdf->Cell(19,10,$labgrupa,0,0,'C');
		$pdf->Ln();
	}
	
	$pdf->SetFont('DejaVu Sans','',12);
	$pdf->Cell(40,10,'Broj indeksa:');
	$pdf->SetFont('DejaVu Sans','',12);
	$pdf->Cell(38,10,$brindexa);
	$pdf->Ln();
	
	$pdf->Ln();
	
	$pdf->SetFont('DejaVu Sans','',12);
	$pdf->Cell(60,10,'Potpis:',0,0,'C');
	$pdf->Ln();
	$pdf->Cell(60,10,'','B',0,'C');
	
	
	$pdf->SetY(-90);
	$pdf->SetX(-80);
	
	$pdf->SetFont('DejaVu Sans','',12);
	$pdf->Cell(40,10,'Ocjena:');
	$pdf->SetFont('DejaVu Sans','',16);
	$pdf->Cell(40,10,$bodova_zadaca);
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	
	$pdf->SetX(-80);
	$pdf->SetFont('DejaVu Sans','',12);
	$pdf->Cell(60,10,'Potpis tutora:',0,0,'C');
	$pdf->Ln();
	$pdf->SetX(-80);
	$pdf->Cell(60,10,'','B',0,'C');
	
	// Funkcija koja kupi putanje rekurzivno za sve fajlove u direktoriju (prebaciti u utility.php?)
	function getFilesRecursive($path) {
		$files = array();
		if(!($handle=opendir($path))) {
			die("Greska kod otvaranja dirketorija $path");
		}
	
		while ($file = readdir($handle)) {
			if ($file != "." && $file != "..") {
				$filepath = $path . "/" . $file;
				if (is_dir($filepath))
					$files = array_merge($files, getFilesRecursive($filepath));
				else
					$files[] = $filepath;
			}
		}
	
		return $files;
	}
	
	/*
	//Funkcija koja brise cijeli direktorij
	function delete_directory($dirname) {
		if (is_dir($dirname))
			$dir_handle = opendir($dirname);
			
		if (!$dir_handle)
			return false;
		
		while($file = readdir($dir_handle)) {
			if ($file != "." && $file != "..") {
				if (!is_dir($dirname."/".$file))
					 unlink($dirname."/".$file);
				 else
					delete_directory($dirname.'/'.$file);
			 }
		}
		closedir($dir_handle);
		rmdir($dirname);
		return true;
	}*/

	// Zadaci
	
	// Fajlovi tipa cpp,c se mogu slati i u formi attachmenta i vrsi se bojenje sintakse
	
	foreach($homeworkAssignments as $zadatak => $assignment) {
		if ($assignment['status'] == 0 || empty($assignment['filename'])) // status = 0 : not submitted
			continue;
		$ekstenzija = pathinfo($assignment['filename'], PATHINFO_EXTENSION);
		
		// Preuzimamo sadržaj zadaće
		$content = api_call("homework/$zadaca/$zadatak/student/$userid/file", [], "GET", false, false);
		if ($_api_http_code == "404") continue;
		
		// Extract zip fajlova
		if ($ekstenzija == "zip") {
			// Kreiramo privremenu datoteku u koju ćemo upisati ZIP
			$dir = "$conf_files_path/zadacetmp/$userid/";
			if (!file_exists($dir))
				mkdir ($dir,0777, true);
			
			$target_path = $dir . "extracted_files";
			if (is_dir($target_path))
				rm_minus_r($target_path);
			if (file_exists($target_path))
				unlink($target_path);
			
			$zip_path = $dir . "tmp.zip";
			$f = fopen($zip_path,'w');
			if (!$f) {
				// Nema smisla printati grešku jer smo u PDF generatoru
				zamgerlog("greska pri pisanju zadace z$zadaca zadatak $zadatak",3); // nivo 3 - greska
				zamgerlog2("greska pri pisanju zadace", $zadaca, $zadatak); // nivo 3 - greska
				return;
			}
			fwrite($f, $content);
			fclose($f);
			
			$archive = new PclZip($zip_path);
			if ($archive->extract(PCLZIP_OPT_ADD_PATH, $target_path) == 0) {
				die("Error : ".$archive->errorInfo(true));
			}
			
			$files = getFilesRecursive($target_path);
			
			//------------------------------------------------------------------------------
	
			$txt = "";
			foreach ($files as $file_path)
			{
				$filename = substr($file_path, strlen($target_path) + 1);
				$naslov = "<html><p><font size=\"14\" color=\"black\">$filename</font></p></html>";
				$txt = file_get_contents($file_path);
				$ekstenzija = pathinfo($file_path, PATHINFO_EXTENSION);
				$geshi = new GeSHi($txt, $ekstenzija);
				$html = $geshi->parse_code();
			
				if ($html == false)
					$html = "<tt>$text</tt>";
				else
					// Zamijeni tabove sa po 8 razmaka, jer pravi probleme u TCPDF
					$html = str_replace("\t","        ", $html);
				
				$pdf->SetAutoPageBreak(1,15);
				$pdf->AddPage();
				$pdf->SetX(15);
				$pdf->SetFont('DejaVu Sans','',16);
				$pdf->Cell(40,10,'Zadatak '.$i.'.');
				$pdf->writeHTMLCell($w=0, $h=0, $x='', $y='', $naslov, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);
				$pdf->Ln();
				$pdf->SetX(15);
				$pdf->SetFont('DejaVu Sans','',10);
				$pdf->writeHTMLCell($w=0, $h=0, $x='', $y='', $html, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);
			}
		}
		
		else {
			$naslov='<html><p><font size="14" color="black">'.$assignment['filename'].'</font></p></html>';
			
			if ($ekstenzija == "cpp") $ekstenzija = "c++";
			$geshi = new GeSHi($content, $ekstenzija);
			$html = $geshi->parse_code();
			
			if ($html == false)
				$html = "<tt>$content</tt>";
			else
				// Zamijeni tabove sa po 8 razmaka, jer pravi probleme u TCPDF
				$html = str_replace("\t","        ", $html);
				
			$pdf->SetAutoPageBreak(1,15);
			$pdf->AddPage();
			$pdf->SetX(15);
			
			$pdf->Cell(40,10,'Zadatak '.$i.'.');
			$pdf->SetFont('DejaVu Sans','',16);
			
			$pdf->writeHTMLCell($w=0, $h=0, $x='', $y='', $naslov, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);
			$pdf->SetX(15);
			$pdf->SetFont('DejaVu Sans','',10);
			$pdf->writeHTMLCell($w=0, $h=0, $x='', $y='', $html, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);
			
		}
	}
	
	$pdf->Output($ime.'_'.$prezime.'_'.$imezad.'.pdf', 'I');

}

?>
