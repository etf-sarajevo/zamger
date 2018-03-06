<?

// IZVJESTAJ/PRIJEMNI_BRZI_UNOS - štampanje obrasca sa kodom kandidata na prijemnom prilikom brzog unosa



function izvjestaj_prijemni_brzi_unos() {


require_once('lib/tcpdf/tcpdf.php');

$termin = intval($_REQUEST['termin']);
$osoba = intval($_REQUEST['osoba']);


$q10 = db_query("select ime, prezime, imeoca, jmbg from osoba where id=$osoba");
if (db_num_rows($q10)<1) {
	biguglyerror("Nepostojeća osoba");
	zamgerlog("nepostojeca osoba $osoba", 3);
	zamgerlog2("nepostojeca osoba", $osoba);
	return;
}
$ime = db_result($q10,0,0);
$prezime = db_result($q10,0,1);
$imeoca = db_result($q10,0,2);
$jmbg = db_result($q10,0,3);

$q20 = db_query("select sifra, jezik from prijemni_obrazac where osoba=$osoba and prijemni_termin=$termin");
if (db_num_rows($q20)<1) {
	biguglyerror("Ne postoji obrazac za ovu osobu");
	zamgerlog("za osobu u$osoba ne postoji obrazac na terminu $termin", 3);
	zamgerlog2("ne postoji obrazac za osobu", $osoba, $termin);
	return;
}
$sifra = db_result($q20,0,0);
$jezik = db_result($q20,0,1);

$q30 = db_query("SELECT ag.naziv, pt.ciklus_studija FROM prijemni_termin pt, akademska_godina ag WHERE pt.id=$termin AND pt.akademska_godina=ag.id");
$naziv_ag = db_result($q30,0,0);
$ciklus = db_result($q30,0,1);

$datum = date("d. m. Y.");
$vrijeme = date("h:i");

if ($jezik == "en") {
	if ($ciklus == 1) $tekst_ciklus = "for admission to first year of first study cycle";
	else if ($ciklus == 2) $tekst_ciklus = "for admission to first year of second study cycle";
	else if ($ciklus == 3) $tekst_ciklus = "for admission to first year of third study cycle";
	$tekst_ak_godina = "Academic year $naziv_ag.";
} else {
	if ($ciklus == 1) $tekst_ciklus = "ZA UPIS U PRVI CIKLUS STUDIJA (3 GODINE)";
	else if ($ciklus == 2) $tekst_ciklus = "ZA UPIS U DRUGI CIKLUS STUDIJA (2 GODINE)";
	else if ($ciklus == 3) $tekst_ciklus = "ZA UPIS U TREĆI CIKLUS STUDIJA (3 GODINE)";
	$tekst_ak_godina = "Akademska $naziv_ag. godina";
}

$q40 = db_query("SELECT id_datuma, UNIX_TIMESTAMP(datum) FROM prijemni_vazni_datumi WHERE prijemni_termin=$termin ORDER BY id_datuma");
$vazni_datumi = array();
while ($r40 = db_fetch_row($q40))
	$vazni_datumi[$r40[0]] = $r40[1];

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
$pdf->SetFont('freesans', '', 36);

$pdf->SetHeaderData("",0,"","");
$pdf->SetPrintHeader(false);
$pdf->SetPrintFooter(false);

// add a page
$pdf->AddPage();


//	$pdf->Image("static/images/content/150dpi/ETF-Domestic-contract-PGS-ALL-0.png",210,297,0,0,'','','',true,150);
if ($jezik=="en") {
	$pdf->Image("static/images/content/150dpi/obrazac_sa_sifrom_en.png",0,0,210,0,'','','',true,150); 
	$en_offset = 7;
} else {
	$pdf->Image("static/images/content/150dpi/obrazac_sa_sifrom.png",0,0,210,0,'','','',true,150); 
	$en_offset = 0;
}
	
	$pdf->SetXY(130, 15);
	$pdf->Cell(23, 0, $sifra, 0, 0, 'C');

	$pdf->SetFont('freesans', '', 12);
	$pdf->SetXY(132, 34);
	$pdf->Cell(23, 0, $tekst_ciklus, 0, 0, 'C');
	$pdf->SetXY(132, 40);
	$pdf->Cell(23, 0, $tekst_ak_godina, 0, 0, 'C');

	$pdf->SetFont('freesans', '', 16);
	$pdf->SetXY(80, 62+$en_offset);
	$pdf->Cell(23, 0, "$ime ($imeoca) $prezime");

	$pdf->SetXY(80, 73+$en_offset);
	$pdf->Cell(23, 0, $jmbg);

	$pdf->SetFont('freesans', '', 12);

	$pdf->SetXY(40, 113+$en_offset);
	$pdf->Cell(23, 0, $datum);

	$pdf->SetXY(130, 113+$en_offset);
	$pdf->Cell(23, 0, $vrijeme);

	$pdf->SetXY(40, 141+$en_offset);
	$pdf->Cell(23, 0, date("d. m. Y.", $vazni_datumi[1]));

	$pdf->SetXY(130, 141+$en_offset);
	$pdf->Cell(23, 0, date("H:i", $vazni_datumi[1]));

	$pdf->SetXY(40, 164+$en_offset);
	$pdf->Cell(23, 0, date("d. m. Y.", $vazni_datumi[2]));

	$pdf->SetXY(130, 164+$en_offset);
	$pdf->Cell(23, 0, date("H:i", $vazni_datumi[2]));

	$pdf->SetXY(40, 188+$en_offset);
	$pdf->Cell(23, 0, date("d. m. Y.", $vazni_datumi[3])." - ".date("d. m. Y.", $vazni_datumi[4]));

	$pdf->SetXY(130, 188+$en_offset);
	$pdf->Cell(23, 0, date("H:i", $vazni_datumi[3])." - ".date("H:i", $vazni_datumi[4]));

	$pdf->SetXY(40, 211+$en_offset);
	$pdf->Cell(23, 0, date("d. m. Y.", $vazni_datumi[5]));

	$pdf->SetXY(130, 211+$en_offset);
	$pdf->Cell(23, 0, date("H:i", $vazni_datumi[5]));

	$pdf->SetXY(40, 235+$en_offset);
	$pdf->Cell(23, 0, date("d. m. Y.", $vazni_datumi[6]));

	$pdf->SetXY(130, 235+$en_offset);
	$pdf->Cell(23, 0, date("H:i", $vazni_datumi[6]));

	$pdf->SetXY(40, 259+$en_offset);
	$pdf->Cell(23, 0, date("d. m. Y.", $vazni_datumi[7])." - ".date("d. m. Y.", $vazni_datumi[8]));

	$pdf->SetXY(130, 259+$en_offset);
	$pdf->Cell(23, 0, date("H:i", $vazni_datumi[7])." - ".date("H:i", $vazni_datumi[8]));

	if (isset($vazni_datumi[9])) {
		$pdf->SetXY(40, 265+$en_offset);
		$pdf->Cell(23, 0, date("d. m. Y.", $vazni_datumi[9])." - ".date("d. m. Y.", $vazni_datumi[10]));

		$pdf->SetXY(130, 265+$en_offset);
		$pdf->Cell(23, 0, date("H:i", $vazni_datumi[9])." - ".date("H:i", $vazni_datumi[10]));
	}


// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output('obrazac_sa_sifrom.pdf', 'I');

//============================================================+
// END OF FILE                                                 
//============================================================+




}
