<?



function stud_pdf() {

global $userid,$system_path,$predmet_id;



# Poslani parametar:
$zadaca = intval($_GET['zadaca']);

// Da li neko pokušava da spoofa zadaću?
if ($zadaca!=0) {
	$q01 = myquery("SELECT count(*) FROM zadaca, labgrupa, student_labgrupa as sl
	WHERE sl.student=$userid and sl.labgrupa=labgrupa.id and labgrupa.predmet=zadaca.predmet and zadaca.id=$zadaca");
	if (mysql_result($q01,0,0)==0) {
		print niceerror("Ova zadaća nije iz vašeg predmeta!?");
		return;
	}
}

if ($zadaca == 0) {
	print niceerror("Neispravan zadatak.");
	return;
}

$lokacijazadaca="$system_path/zadace/$predmet_id/$userid/$zadaca/";


// Podaci o zadaći

$q02 = myquery("select zadaca.zadataka,predmet.naziv,zadaca.naziv,programskijezik.ekstenzija from zadaca,predmet,programskijezik where zadaca.id=$zadaca and zadaca.predmet=predmet.id and zadaca.programskijezik=programskijezik.id");
if (mysql_num_rows($q02) < 1) {
	print niceerror("Ne mogu pronaći zadaću");
	return;
}
$brzad = mysql_result($q02,0,0);
$predmet = mysql_result($q02,0,1);
$imezad = mysql_result($q02,0,2);
$ekst = mysql_result($q02,0,3);


// Podaci o studentu

$q03 = myquery("select s.ime, s.prezime, s.brindexa, l.naziv from student as s, labgrupa as l, student_labgrupa as sl where s.id=$userid and sl.student=$userid and sl.labgrupa=l.id");
if (mysql_num_rows($q03) < 1) {
	print niceerror("Ne mogu pronaći zadaću");
	return;
}
$ime = mysql_result($q03,0,0);
$prezime = mysql_result($q03,0,1);
$brindexa = mysql_result($q03,0,2);
$labgrupa = mysql_result($q03,0,3);



// Računanje ocjene
$bodova_zadaca=0;
$filename=array();
for ($zadatak=1;$zadatak<=$brzad;$zadatak++) {
	// Uzmi samo rjesenje sa zadnjim IDom
	$q04 = myquery("select status,bodova,filename from zadatak where student=$userid and zadaca=$zadaca and redni_broj=$zadatak order by id desc limit 1");
	if (mysql_num_rows($q04)>0) {
		$status = mysql_result($q04,0,0);
		$bodova_zadatak = mysql_result($q04,0,1);
		$bodova_zadaca += $bodova_zadatak;
		$filename[$zadatak] = mysql_result($q04,0,2);
		if (strlen($filename[$zadatak])<2) {
			$filename[$zadatak]="$zadatak$ekst";
		} 
	}
}




// PDF rendering

require('fpdf153/fpdf.php');

class PDF extends FPDF
{

function Header()
{
	$this->Image("fpdf153/etf_logo_2.png",10,8,20);
	$this->Image("fpdf153/unsa.png",180,8,20);
	
	$this->SetFont("DejaVu Sans B",'',10);
	$this->SetY(15);
	$this->SetX(80);
	$this->Cell(50,5,'UNIVERZITET U SARAJEVU',0,0,'C');
	$this->Ln();
	$this->SetX(80);
	$this->Cell(50,5,iconv('utf-8','iso-8859-2','ELEKTROTEHNIČKI FAKULTET'),0,0,'C');
	$this->Ln();
	$this->Cell(190,5,'','B',0,'C');
	$this->Ln();
	$this->Ln();
}

function Footer()
{

	if ($this->PageNo() > 1) {
		//Position at 1.5 cm from bottom
		$this->SetY(-15);
		//Arial italic 8
		$this->SetFont('Arial','I',8);
		//Text color in gray
		$this->SetTextColor(128);
		//Page number
		$this->Cell(0,10,'Stranica '.$this->PageNo(),0,0,'C');
	}
}
}


// Prva stranica

//$pdf=new PDF("P","mm","A4");
$pdf=new PDF();
$pdf->AddFont("DejaVu Sans","","DejaVuSans.php");
$pdf->AddFont("DejaVu Sans B","","DejaVuSans-Bold.php");
$pdf->AddFont("Nimbus Mono","","n022003l.php");

$pdf->AddPage();

$pdf->SetY(100);
$pdf->SetFont('DejaVu Sans B','',30);
$pdf->Cell(190,10,iconv('utf-8','iso-8859-2',$imezad),0,0,'C');
$pdf->Ln();
$pdf->SetFont('DejaVu Sans B','',16);
$pdf->Cell(190,10,'- '.strtoupper(iconv("utf8","iso-8859-2",$predmet)).' -',0,0,'C');



$pdf->SetY(-90);

$pdf->SetFont('DejaVu Sans','',12);
$pdf->Cell(40,10,'Student:');
$pdf->SetFont('DejaVu Sans B','',12);
$pdf->Cell(40,10,iconv("utf8","iso-8859-2",$ime.' '.$prezime));
$pdf->Ln();

$pdf->SetFont('DejaVu Sans','',12);
$pdf->Cell(40,10,'Grupa:');
$pdf->SetFont('DejaVu Sans B','',12);
$pdf->Cell(40,10,iconv("utf8","iso-8859-2",$labgrupa));
$pdf->Ln();

$pdf->SetFont('DejaVu Sans','',12);
$pdf->Cell(40,10,'Broj indeksa:');
$pdf->SetFont('DejaVu Sans B','',12);
$pdf->Cell(40,10,$brindexa);
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
$pdf->SetFont('DejaVu Sans B','',16);
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


// Zadaci
for ($i=1; $i<=$brzad; $i++) {
	$txt = file_get_contents($lokacijazadaca.$filename[$i]);

	if ($txt != false) {
		// Zamijeni tabove sa po 8 razmaka
		$txt = str_replace("\t","        ",$txt);

		$pdf->AddPage();
		
		$pdf->SetX(15);
		$pdf->SetFont('DejaVu Sans B','',16);
		$pdf->Cell(40,10,'Zadatak '.$i.'.');
		$pdf->Ln();
		
		$pdf->SetX(15);
		$pdf->SetFont('Nimbus Mono','',10);
		$pdf->MultiCell(0,4,$txt);
	}
}

// Kraj
$pdf->Output();



}

?>