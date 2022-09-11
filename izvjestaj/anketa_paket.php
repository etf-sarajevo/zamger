<?

// IZVJESTAJ/ANKETA_PAKET - informacioni paket za ankete


require("vendor/autoload.php"); // Koristimo TCPDF

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
			$this->SetFont('DejaVu Sans ','I',8);
			//Text color in gray
			$this->SetTextColor(128);
			//Page number
			$this->Cell(0,10,'Stranica '.$this->PageNo(),0,0,'C');
		}
	}
}


function ob_file_callback($buffer)
{
	global $sadrzaj_bafera_za_pdf;
	$sadrzaj_bafera_za_pdf=$buffer;
}

function izvjestaj_anketa_paket() {
	global $sadrzaj_bafera_za_pdf, $conf_files_path;
	
	$anketa = intval($_REQUEST['anketa']);
	$query = db_query("SELECT semestar, akademska_godina FROM anketa_predmet WHERE anketa=$anketa AND predmet is null");
	db_fetch2($query, $semestar, $ag);
	
	$dest_path = $conf_files_path . "/anketa_paket_$anketa";
	if (file_exists($dest_path)) rm_minus_r($dest_path);
	mkdir($dest_path);
	
	
	$studiji = [ 1 => "pgs", 2 => "ri1", 3 => "ae1", 4 => "ee1", 5 => "tk1", 7 => "ri2", 8 => "ae2", 9 => "ee2", 10 => "tk2", 22 => "rs" ];
	
	// Rank pitanja
	include("izvjestaj/for_looper.php");
	foreach ($studiji as $studij => $ime_studija) {
		if ($ime_studija == "pgs") {
			$_REQUEST['for_pgs'] = true;
			unset($_REQUEST['for_studij']);
		} else {
			$_REQUEST['for_studij'] = $studij;
			$_REQUEST['for_semestar'] = $semestar;
			unset($_REQUEST['for_pgs']);
		}
		$_REQUEST['koji_izvjestaj'] = 'izvjestaj/anketa';
		$_REQUEST['rank'] = 'da';
		$_REQUEST['ag'] = $ag;
		
		// Izlaznost
		ob_start('ob_file_callback');
		eval("izvjestaj_for_looper();");
		ob_end_clean();
		
		// Prva stranica
		$pdf = new MYPDF($orientation, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		
		$pdf->AddFont("DejaVuSans","","DejaVuSans.php");
		$pdf->AddFont("DejaVuSans","","DejaVuSans-Bold.php");
		$pdf->SetFont('DejaVuSans','',8);
		$pdf->AddPage();
		
		
		$sadrzaj_bafera_za_pdf = str_replace("\t","        ",$sadrzaj_bafera_za_pdf);
		// Ukidam JavaScript koji izgleda TCPDF ne ignoriše kako bi trebalo
		$sadrzaj_bafera_za_pdf = preg_replace("/\<script.*?\<\/script\>/is","",$sadrzaj_bafera_za_pdf);
		
		$pdf->writeHTMLCell($w=0, $h=0, $x='', $y='', $sadrzaj_bafera_za_pdf, $border=0, $ln=1, $fill=0, $reseth=true, $align='center', $autopadding=true);
		
		$pdf->Output("$dest_path/rank_$ime_studija.pdf", 'F');
	}
	
	// Komentari
	foreach ($studiji as $studij => $ime_studija) {
		if ($ime_studija == "pgs") {
			$_REQUEST['for_pgs'] = true;
			unset($_REQUEST['for_studij']);
		} else {
			$_REQUEST['for_studij'] = $studij;
			$_REQUEST['for_semestar'] = $semestar;
			unset($_REQUEST['for_pgs']);
		}
		$_REQUEST['koji_izvjestaj'] = 'izvjestaj/anketa';
		$_REQUEST['komentar'] = 'da';
		unset ($_REQUEST['rank']);
		$_REQUEST['limit'] = '100';
		$_REQUEST['ag'] = $ag;
		
		// Izlaznost
		ob_start('ob_file_callback');
		eval("izvjestaj_for_looper();");
		ob_end_clean();
		
		// Prva stranica
		$pdf = new MYPDF($orientation, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		
		$pdf->AddFont("DejaVuSans","","DejaVuSans.php");
		$pdf->AddFont("DejaVuSans","","DejaVuSans-Bold.php");
		$pdf->SetFont('DejaVuSans','',8);
		$pdf->AddPage();
		
		
		$sadrzaj_bafera_za_pdf = str_replace("\t","        ",$sadrzaj_bafera_za_pdf);
		// Ukidam JavaScript koji izgleda TCPDF ne ignoriše kako bi trebalo
		$sadrzaj_bafera_za_pdf = preg_replace("/\<script.*?\<\/script\>/is","",$sadrzaj_bafera_za_pdf);
		
		$pdf->writeHTMLCell($w=0, $h=0, $x='', $y='', $sadrzaj_bafera_za_pdf, $border=0, $ln=1, $fill=0, $reseth=true, $align='center', $autopadding=true);
		
		$pdf->Output("$dest_path/komentari_$ime_studija.pdf", 'F');
	}
	
	// Izlaznost
	ob_start('ob_file_callback');
	include("izvjestaj/anketa_sumarno.php");//ovdje ga ukljucujem
	eval("izvjestaj_anketa_sumarno();");
	ob_end_clean();
	
	// Prva stranica
	$pdf = new MYPDF($orientation, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	
	$pdf->AddFont("DejaVuSans","","DejaVuSans.php");
	$pdf->AddFont("DejaVuSans","","DejaVuSans-Bold.php");
	$pdf->SetFont('DejaVuSans','',8);
	$pdf->AddPage();
	
	
	$sadrzaj_bafera_za_pdf = str_replace("\t","        ",$sadrzaj_bafera_za_pdf);
	// Ukidam JavaScript koji izgleda TCPDF ne ignoriše kako bi trebalo
	$sadrzaj_bafera_za_pdf = preg_replace("/\<script.*?\<\/script\>/is","",$sadrzaj_bafera_za_pdf);
	
	$pdf->writeHTMLCell($w=0, $h=0, $x='', $y='', $sadrzaj_bafera_za_pdf, $border=0, $ln=1, $fill=0, $reseth=true, $align='center', $autopadding=true);
	
	$pdf->Output("$dest_path/izlaznost.pdf", 'F');
	
	
	// Sveukupna ocjena
	ob_start('ob_file_callback');
	$_REQUEST['tip'] = 'sveukupna';
	eval("izvjestaj_anketa_sumarno();");
	ob_end_clean();
	
	// Prva stranica
	$pdf = new MYPDF($orientation, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	
	$pdf->AddFont("DejaVuSans","","DejaVuSans.php");
	$pdf->AddFont("DejaVuSans","","DejaVuSans-Bold.php");
	$pdf->SetFont('DejaVuSans','',8);
	$pdf->AddPage();
	
	
	$sadrzaj_bafera_za_pdf = str_replace("\t","        ",$sadrzaj_bafera_za_pdf);
	// Ukidam JavaScript koji izgleda TCPDF ne ignoriše kako bi trebalo
	$sadrzaj_bafera_za_pdf = preg_replace("/\<script.*?\<\/script\>/is","",$sadrzaj_bafera_za_pdf);
	
	$pdf->writeHTMLCell($w=0, $h=0, $x='', $y='', $sadrzaj_bafera_za_pdf, $border=0, $ln=1, $fill=0, $reseth=true, $align='center', $autopadding=true);
	
	$pdf->Output("$dest_path/sveukupna_ocjena.pdf", 'F');
	
	`cd $dest_path; zip anketa_$anketa.zip *.pdf`;
	
	$filename = "anketa_$anketa.zip";
	$filepath = "$dest_path/anketa_$anketa.zip";
	
	$type = `file -bi '$filepath'`;
	header("Content-Type: $type");
	header('Content-Disposition: attachment; filename="' . $filename.'"', false);
	header("Content-Length: ".(string)(filesize($filepath)));

	// workaround za http://support.microsoft.com/kb/316431 (zamger bug 94)
	header("Pragma: dummy=bogus");
	header("Cache-Control: private");
	
	$k = readfile($filepath,false);
}
