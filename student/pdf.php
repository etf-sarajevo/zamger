<?

// STUDENT/PDF - ispis zadace u PDF formatu

// v3.9.1.0 (2008/02/19) + Kopiran raniji stud_pdf
// v3.9.1.1 (2008/03/28) + Nova auth tabela
// v3.9.1.2 (2008/03/30) + Popravljen put za zadaće
// v3.9.1.3 (2008/08/28) + Tabela osoba umjesto auth
// v4.0.0.0 (2009/02/19) + Release
// v4.0.9.1 (2009/04/01) + Tabela zadaca preusmjerena sa ponudakursa na tabelu predmet

// TODO: koristiti tcpdf


function student_pdf() {

global $userid,$conf_files_path;



# Poslani parametar:
$zadaca = intval($_GET['zadaca']);

if ($zadaca == 0) {
	biguglyerror("Neispravan zadatak.");
	return;
}

// Da li neko pokušava da spoofa zadaću?
$q10 = myquery("SELECT pk.id FROM zadaca as z, student_predmet as sp, ponudakursa as pk
WHERE sp.student=$userid and sp.predmet=pk.id and pk.predmet=z.predmet and pk.akademska_godina=z.akademska_godina and z.id=$zadaca");
if (mysql_num_rows($q10)<1) {
	biguglyerror("Ova zadaća nije iz vašeg predmeta!?");
	return;
}
$predmet_id = mysql_result($q10,0,0);

$lokacijazadaca="$conf_files_path/zadace/$predmet_id/$userid/$zadaca/";


// Podaci o zadaći

$q20 = myquery("select z.zadataka,p.naziv,z.naziv,pj.ekstenzija from zadaca as z,predmet as p,programskijezik as pj where z.id=$zadaca and z.predmet=p.id and z.programskijezik=pj.id");
if (mysql_num_rows($q20) < 1) {
	biguglyerror("Ne mogu pronaći zadaću");
	// .. može li se ukinuti ovo?
	return;
}
$brzad = mysql_result($q20,0,0);
$predmet = mysql_result($q20,0,1);
$imezad = mysql_result($q20,0,2);
$ekst = mysql_result($q20,0,3);


// Podaci o studentu

$q30 = myquery("select ime, prezime, brindexa from osoba where id=$userid");
if (mysql_num_rows($q30) < 1) {
	biguglyerror("Ne mogu pronaći studenta");
	// .. može li se ukinuti ovo?
	return;
}
$ime = mysql_result($q30,0,0);
$prezime = mysql_result($q30,0,1);
$brindexa = mysql_result($q30,0,2);

// Labgrupa

$q40 = myquery("select l.naziv from labgrupa as l, student_labgrupa as sl where sl.student=$userid and sl.labgrupa=l.id and l.predmet=$predmet_id limit 1");
if (mysql_num_rows($q40)>0)
	$labgrupa = mysql_result($q40,0,0);
else
	$labgrupa = ""; // nema grupe



// Računanje ocjene

$bodova_zadaca=0;
$filename=array();
for ($zadatak=1;$zadatak<=$brzad;$zadatak++) {
	// Uzmi samo rjesenje sa zadnjim IDom
	$q50 = myquery("select status,bodova,filename from zadatak where student=$userid and zadaca=$zadaca and redni_broj=$zadatak order by id desc limit 1");
	if (mysql_num_rows($q50)>0) {
		$status = mysql_result($q50,0,0);
		$bodova_zadatak = mysql_result($q50,0,1);
		if ($status==5) $bodova_zadaca += $bodova_zadatak;
		$filename[$zadatak] = mysql_result($q50,0,2);
		if (strlen($filename[$zadatak])<2) {
			$filename[$zadatak]="$zadatak$ekst";
		} 
	}
}




// PDF rendering

require('lib/fpdf153/fpdf.php');

class PDF extends FPDF
{

function Header()
{
	$this->Image("images/etf-100x100.png",10,8,20);
	$this->Image("images/unsa.png",180,8,20);
	
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

if ($labgrupa != "") {
	$pdf->SetFont('DejaVu Sans','',12);
	$pdf->Cell(40,10,'Grupa:');
	$pdf->SetFont('DejaVu Sans B','',12);
	$pdf->Cell(40,10,iconv("utf8","iso-8859-2",$labgrupa));
	$pdf->Ln();
}

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