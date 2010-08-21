<?

// STUDENT/PDF - ispis zadace u PDF formatu

// v3.9.1.0 (2008/02/19) + Kopiran raniji stud_pdf
// v3.9.1.1 (2008/03/28) + Nova auth tabela
// v3.9.1.2 (2008/03/30) + Popravljen put za zadaæe
// v3.9.1.3 (2008/08/28) + Tabela osoba umjesto auth
// v4.0.0.0 (2009/02/19) + Release
// v4.0.9.1 (2009/04/01) + Tabela zadaca preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.2 (2009/04/29) + Preusmjeravam tabelu labgrupa sa tabele ponudakursa na tabelu predmet
// v4.0.9.3 (2009/05/25) + Direktorij za zadace je sada predmet-ag umjesto ponudekursa
// v4.0.9.4 (2009/05/26) + Varijabla $predmet je koristena u dva znacenja :)

// TODO: koristiti tcpdf

//mb_internal_encoding('UTF-8');

//mysql_query('SET NAMES UTF8');


function nastavnik_pdf_converter() {

global $userid,$conf_files_path;



# Poslani parametar:
$predmet = intval($_GET['predmet']);
$ag = intval($_GET['ag']);
$grupa = intval($_GET['grupa']);

require_once('lib\tcpdf\config\lang\eng.php');
require_once('lib\tcpdf\tcpdf.php');



// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

	//Page header
	public function Header() {
	
	$this->SetMargins(10,35,25,true);	
	$this->Image("images/etf-100x100.png",10,8,20);
	$this->Image("images/unsa.png",180,8,20);
	
        $this->SetFont("DejaVu Sans",'',10);
	$this->SetY(15);
	$this->SetX(80);
	$this->Cell(50,5,'UNIVERZITET U SARAJEVU',0,0,'C');
	$this->Ln();
	$this->SetX(80);
	$this->Cell(50,5,iconv('utf-8','iso-8859-2','ELEKTROTEHNIÈKI FAKULTET'),0,0,'C');
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




		
//Kupimo sadrzaj html-a u jednu varijablu("ne moguce pristupiti linku zbog sigurnosti, najvjerovatnije dodati id sesije na link")
	$txt = file_get_contents("http://localhost/zamger41/index.php?sta=izvjestaj/grupe&predmet=1&ag=1&grupa=2");
	
	$s = utf8_encode($txt);
    $p = utf8_decode($s);
	     
		//$pdf->Ln();
		//$pdf->Ln();
		//pozivamo metodu za konvertovanje html u pdf
		$pdf->writeHTMLCell($w=0, $h=0, $x='', $y='', $s, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);

		//$pdf->MultiCell(0,4,$txt);
	


$pdf->Output('Izvjestaj.pdf', 'I');

}

?>