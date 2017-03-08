<?

// IZVJESTAJ/PDF_CONVERTER - konvertuje bilo koji HTML izvještaj u PDF format



function ob_file_callback($buffer)
{
	global $sadrzaj_bafera_za_pdf;
	$sadrzaj_bafera_za_pdf=$buffer;
}


function izvjestaj_pdf_converter() {
	global $string_pdf,$string,$sadrzaj_bafera_za_pdf,$registry;
	global $user_student, $user_nastavnik, $user_studentska, $user_siteadmin;

	ob_start('ob_file_callback');
	$koji = db_escape($_REQUEST['koji_izvjestaj']);
	$staf = str_replace("/","_",$koji);
	if ($_REQUEST['o'] == "l") $orientation="L";
	else $orientation="P";

	$found=false;
	foreach ($registry as $r) {
		if ($r[0] == $koji) {
			if (strstr($r[3],"P") || (strstr($r[3],"S") && $user_student) || (strstr($r[3],"N") && $user_nastavnik) || (strstr($r[3],"B") && $user_studentska) || (strstr($r[3],"A") && $user_siteadmin)) {
				$found=true;
			} else {
				zamgerlog ("pdf_converter pristup nedozvoljenom modulu $koji", 3);
				zamgerlog2 ("pristup nedozvoljenom modulu", 0, 0, 0, $koji);
				niceerror("Pristup nedozvoljenom modulu");
				return;
			}
			break;
		}
	}
	if ($found===false) {
		zamgerlog ("pdf_converter nepostojeći modul $koji", 3);
		zamgerlog2 ("nepostojeći modul", 0, 0, 0, $koji);
		niceerror("Pristup nepostojećem modulu");
		return;
	}

	include("$koji.php");//ovdje ga ukljucujem
	eval("$staf();");
	ob_end_clean();

	require_once('lib/tcpdf/tcpdf.php');

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

	$pdf->Output("$staf.pdf", 'I');
}

?>
