<?

// ADMIN/PRINTAJ_TOKENE - Modul za generisanje PDF dokumenta sa tokenima za anketu spremnog za sječenje



function admin_printaj_tokene() {


require_once('lib/tcpdf/tcpdf.php');

$anketa = int_param('anketa');
$ag = int_param('ag');
$studij = int_param('studij'); // svi predmeti na studiju
$semestar = int_param('semestar'); // svi predmeti na semestru
$semestar_parni = int_param('semestar_parni'); // svi predmeti u zimskom/ljetnjem semestru
$predmet_id = int_param('predmet'); // pojedinačni predmet

$html = param('html'); // izlaz u vidu HTMLa (ako nije setovano, PDF)


// Provjera parametara
if ($predmet != 0 && ($studij != 0 || $semestar != 0 || $semestar_parni != 0)) {
	niceerror("Ne možete istovremeno zadati ID predmeta i neki od ostalih parametara");
	return;
}
if ($semestar != 0 && $semestar_parni != 0) {
	niceerror("Ne možete istovremeno zadati parametre semestar i semestar_parni");
	return;
}
if ($studij == 0 && $predmet_id == 0) {
	niceerror("Morate zadati ili ID studija ili ID predmeta");
	return;
}

// Dodaci za SQL upite
$dodaj_studij = $dodaj_semestar = "";
if ($studij != 0) {
	$dodaj_studij = "AND studij=$studij";
} else {
	// Studij mora biti zadat... 
}
if ($semestar > 0) {
	$dodaj_semestar = "AND semestar=$semestar";
} else {
	// Ako nije zadan parametar "semestar" koristimo "semestar_parni" da printamo tokene
	// za sve predmete u parnom/neparnom semestru
	$dodaj_semestar = "AND semestar mod 2 = $semestar_parni";
}



// Kreiram nizove sa predmetima i tokenima
$predmeti = array();
$predmet_tokeni = array();
$broj_studenata = array();
$maxtokena = 0;

if ($predmet_id == 0) {
	// Najnoviji plan studija
	$plan_studija = db_get("SELECT id FROM plan_studija WHERE 1=1 $dodaj_studij ORDER BY godina_vazenja DESC LIMIT 1");

	// Svi predmeti na studiju/semestru
	$q30 = db_query("SELECT pasos_predmeta, plan_izborni_slot, obavezan FROM plan_studija_predmet WHERE plan_studija=$plan_studija");
	while (db_fetch3($q30, $pasos_predmeta, $plan_izborni_slot, $obavezan)) {
		if ($obavezan == 1) {
			$predmet = db_get("SELECT predmet FROM pasos_predmeta WHERE id=$pasos_predmeta");
			$predmeti[$predmet] = "";
		} else { // izborni
			// uzimamo sve predmete u slotu $plan_izborni_slot
			$q70 = db_query("select pp.predmet from pasos_predmeta as pp, plan_izborni_slot as pis where pis.id=$plan_izborni_slot and pis.pasos_predmeta=pp.id");
			while (db_fetch1($q70, $predmet)) {
				// Nećemo više puta dodati kreirati isti predmet
				if (array_key_exists($predmet, $predmeti) continue;
				$predmeti[$predmet] = "";
			}
		}
	}
}

else { // $predmet_id != 0
	$predmeti[$predmet_id] = "";
}

// Određujemo ostale parametre predmeta
foreach($predmeti as $predmet => $naziv) {
	$predmeti[$predmet] = db_get("SELECT naziv FROM predmet WHERE id=$predmet");
	$predmet_tokeni[$predmet] = db_get_varray("SELECT unique_id FROM anketa_rezultat WHERE anketa=$anketa AND predmet=$predmet AND akademska_godina=$ag");
	if (count($predmet_tokeni[$predmet]) > $maxtokena) $maxtokena = count($predmet_tokeni[$predmet]);

	$broj_studenata[$r30[0]] = db_get("SELECT count(*) FROM student_predmet as sp, ponudakursa as pk WHERE sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
}


if ($html) {
	foreach ($predmeti as $predmet => $naziv) {
		print "<p><b>$naziv</b><br>";
		$a = min( $broj_studenata[$predmet], count($predmet_tokeni[$predmet]) );
		if ($a==0) continue;
		for ($i=0; $i<$a; $i++)
			print $predmet_tokeni[$predmet][$i]."<br>\n";
	}
	return;
}



// Priprema PDFa

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); 

// set document information
$pdf->SetCreator("Zamger");
$pdf->SetTitle('Tokeni za popunjavanje ankete');

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
$pdf->SetFont('freesans', '', 9);

$pdf->SetHeaderData("",0,"","");
$pdf->SetPrintHeader(false);
$pdf->SetPrintFooter(false);

// add a page
$pdf->AddPage();

// Parametri
$sirina_kocke=95;
$visina_kocke=45;
$hpredmeti = 7;
$htokeni = 25;
$vpredmeti = 32;
$vjedanpredmet = 5;


// Petlja
$vpos=10;
$hpos=10;

foreach ($predmeti as $predmet => $naziv) {
//	$a = count($predmet_tokeni[$predmet]);
	$a = min( $broj_studenata[$predmet], count($predmet_tokeni[$predmet]) );
	if ($a==0) continue;
	for ($i=0; $i<$a; $i++) {
		$pdf->SetFont('freesans', '', 9);
		$pdf->writeHTMLCell($sirina_kocke, $visina_kocke, $hpos, $vpos, "&nbsp;<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Anketa za predmet: ".strtoupper($naziv)."<br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;* Otvorite web stranicu Zamgera<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;* Ako ste već prijavljeni, odjavite se<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;* Kliknite na ikonu Anketa<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;* Unesite kod naveden ispod", 1);
		$pdf->SetFont('freemono', '', 18);
		
		$pdf->SetXY($hpos+$htokeni, $vpos+$vpredmeti);
		$pdf->Cell($sirina_kocke-$htokeni, 0, $predmet_tokeni[$predmet][$i]);

		// Lijeva/desna kocka?
		if ($hpos==10) {
			$hpos+=$sirina_kocke;
		} else {
			$hpos=10;
			$vpos+=$visina_kocke;
			if ($vpos+$visina_kocke > 297) {
				$pdf->AddPage();
				$vpos=10;
			}
		}
	} // Dvije kocke u redu
	
	// Svaki predmet na svojoj stranici:
	if ($predmet_id == 0) {
		$pdf->AddPage();
		$hpos=10;
		$vpos=10;
	}
}

/*for ($i=0; $i<$maxtokena; $i++) {
//	$pdf->SetXY($hpos, $vpos);
	$pdf->SetFont('freesans', '', 9);
	$pdf->writeHTMLCell($sirina_kocke, $visina_kocke, $hpos, $vpos, "* Otvorite web stranicu Zamgera<br>* Ako ste već prijavljeni, odjavite se<br>* Kliknite na ikonu Anketa<br>* Unesite kod naveden ispod", 1);
	$vposadd = $vpos+$vpredmeti;
	foreach ($predmeti as $predmet=>$naziv) {
		if ($i>=count($predmet_tokeni[$predmet])) continue; // Nema više tokena za ovaj predmet
		$pdf->SetFont('freesans', '', 9);
		$pdf->SetXY($hpos+$hpredmeti, $vposadd);
		if (strlen($naziv)>40) $naziv=substr($naziv,0,35)."...";
		$pdf->Cell($sirina_kocke-$hpredmeti, 0, $naziv);
		$pdf->SetFont('freemono', 'B', 9);
		$pdf->SetXY($hpos+$htokeni, $vposadd);
		$pdf->Cell($sirina_kocke-$htokeni, 0, $predmet_tokeni[$predmet][$i]);
		$vposadd += $vjedanpredmet;
	}

	// Lijeva/desna kocka?
	if ($hpos==10) {
		$hpos+=$sirina_kocke;
	} else {
		$hpos=10;
		$vpos+=$visina_kocke;
		if ($vpos+$visina_kocke > 297) {
			$pdf->AddPage();
			$vpos=10;
		}
	} // Dvije kocke u redu
}*/

//Close and output PDF document
$pdf->Output('tokeni.pdf', 'I');


}

?>
