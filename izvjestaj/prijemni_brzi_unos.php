<?



function izvjestaj_prijemni_brzi_unos() {


require_once('lib/tcpdf/tcpdf.php');

$termin = intval($_REQUEST['termin']);
$osoba = intval($_REQUEST['osoba']);


$q10 = myquery("select ime, prezime, imeoca, jmbg from osoba where id=$osoba");
if (mysql_num_rows($q10)<1) {
	biguglyerror("Nepostojeća osoba");
	zamgerlog("nepostojeca osoba $osoba", 3);
	return;
}
$ime = mysql_result($q10,0,0);
$prezime = mysql_result($q10,0,1);
$imeoca = mysql_result($q10,0,2);
$jmbg = mysql_result($q10,0,3);

$q20 = myquery("select sifra, jezik from prijemni_obrazac where osoba=$osoba and prijemni_termin=$termin");
if (mysql_num_rows($q20)<1) {
	biguglyerror("Ne postoji obrazac za ovu osobu");
	zamgerlog("za osobu u$osoba ne postoji obrazac na terminu $termin", 3);
	return;
}
$sifra = mysql_result($q20,0,0);
$jezik = mysql_result($q20,0,1);

$datum = date("d. m. Y.");
$vrijeme = date("h:i");



// ----- Pravljenje PDF dokumenta


$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); 

// set document information
$pdf->SetCreator("Zamger");
$pdf->SetTitle('Sifra kandidata i pregled vaznijih datuma');

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins
$pdf->SetMargins(0,0,0);

//set auto page breaks
$pdf->SetAutoPageBreak(false);

//set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 
//$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO*2.083333); 
$pdf->setJPEGQuality(100); 

//set some language-dependent strings
$pdf->setLanguageArray($l); 

// ---------------------------------------------------------

// set font
$pdf->SetFont('freesans', '', 48);

$pdf->SetHeaderData("",0,"","");
$pdf->SetPrintHeader(false);
$pdf->SetPrintFooter(false);

// add a page
$pdf->AddPage();


//	$pdf->Image("images/content/150dpi/ETF-Domestic-contract-PGS-ALL-0.png",210,297,0,0,'','','',true,150);
if ($jezik=="en") {
	$pdf->Image("images/content/150dpi/obrazac_sa_sifrom_en.png",0,0,210,0,'','','',true,150); 
	$en_offset = 7;
} else {
	$pdf->Image("images/content/150dpi/obrazac_sa_sifrom.png",0,0,210,0,'','','',true,150); 
	$en_offset = 0;
}
	
	$pdf->SetXY(130, 15);
	$pdf->Cell(23, 0, $sifra, 0, 0, 'C');

	$pdf->SetFont('freesans', '', 16);
	$pdf->SetXY(80, 62+$en_offset);
	$pdf->Cell(23, 0, "$ime ($imeoca) $prezime");

	$pdf->SetXY(80, 73+$en_offset);
	$pdf->Cell(23, 0, $jmbg);

	$pdf->SetFont('freesans', '', 14);

	$pdf->SetXY(40, 113+$en_offset);
	$pdf->Cell(23, 0, $datum);

	$pdf->SetXY(130, 113+$en_offset);
	$pdf->Cell(23, 0, $vrijeme);


// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output('obrazac_sa_sifrom.pdf', 'I');

//============================================================+
// END OF FILE                                                 
//============================================================+




}
