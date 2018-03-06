<?

// STUDENT/UGOVOROUCENJUPDF - generisanje PDFa na osnovu ugovora o ucenju

// Modul koristi biblioteku TCPDF



function student_ugovoroucenjupdf() {

global $userid;

require_once('lib/tcpdf/tcpdf.php');
require_once('lib/student_studij.php'); // zbog ima_li_uslov

// Prikupljam podatke iz baze

// Za koju godinu se prijavljuje?
$q1 = db_query("select id, naziv from akademska_godina where aktuelna=1");
$q2 = db_query("select id, naziv from akademska_godina where id>".db_result($q1,0,0)." order by id limit 1");
if (db_num_rows($q2)<1) {
//	nicemessage("U ovom trenutku nije aktiviran upis u sljedeću akademsku godinu.");
//	return;
	// Pretpostavljamo da se upisuje u aktuelnu?
	$zagodinu  = db_result($q1,0,0);
	$agnaziv  = db_result($q1,0,1);
	$q3 = db_query("select id from akademska_godina where id<$zagodinu order by id desc limit 1");
	$proslagodina = db_result($q3,0,0);
} else {
	$proslagodina = db_result($q1,0,0);
	$zagodinu = db_result($q2,0,0);
	$agnaziv = db_result($q2,0,1);
}


// Zapis u tabeli ugovoroucenju
$q5 = db_query("select uu.id, s.id, s.naziv, s.naziv_en, uu.semestar, s.tipstudija, uu.kod from ugovoroucenju as uu, studij as s where uu.student=$userid and uu.akademska_godina=$zagodinu and uu.studij=s.id order by semestar desc limit 1");
if (!db_fetch7($q5, $ugovorid, $studij, $studijbos, $studijeng, $sem2, $tipstudija, $kod_ugovora)) {
	niceerror("Nije kreiran ugovor o učenju za studenta.");
	return;
}

$studijbos = substr($studijbos, 0, strpos($studijbos, "(")-1);

$sem1 = $sem2-1;
$godina = $sem2/2;


// Ostali podaci o osobi
$q10 = db_query("select ime, prezime, brindexa from osoba where id=$userid");
$imeprezime = db_result($q10,0,0)." ".db_result($q10,0,1);
$brindexa = db_result($q10,0,2);


// Odabir plana studija
$plan_studija = 0;
$q5a = db_query("SELECT studij, plan_studija FROM student_studij WHERE student=$userid AND akademska_godina<=$zagodinu ORDER BY akademska_godina DESC LIMIT 1");
if (db_num_rows($q5a)>0 && $studij ==  db_result($q5a,0,0))
	$plan_studija = db_result($q5a,0,1);

if ($plan_studija == 0) {
	// Student nije prethodno studirao na istom studiju ili plan studija nije bio definisan
	// Uzimamo najnoviji plan za odabrani studij
	$q6 = db_query("select id from plan_studija where studij=$studij order by godina_vazenja desc limit 1");
	if (db_num_rows($q6)<1) { 
		niceerror("Nepostojeći studij");
		return;
	}
	$plan_studija = db_result($q6,0,0);
}

// Godina važenja plana studija (za predmete sa drugog odsjeka - FIXME)
$q5n = db_query("SELECT godina_vazenja FROM plan_studija WHERE id=$plan_studija");
$godina_vazenja = db_result($q5n,0,0);


// Da li je ponovac (ikada slušao isti tip studija)?
$q20 = db_query("select ss.semestar from student_studij as ss, studij as s, tipstudija as ts where ss.student=$userid and ss.akademska_godina<=$proslagodina and ss.studij=s.id and s.tipstudija=$tipstudija order by semestar desc limit 1");
if (db_num_rows($q20)<1) { 
	/*niceerror("Ne možete popunjavati ugovor o učenju ako prvi put slušate prvu godinu studija.");
	return;*/
	// Zašto ne bismo dozvolili?
	$ponovac = 0;
} else {
	if ($sem1>db_result($q20,0,0)) 
		$ponovac=0;
	else
		$ponovac=1;
}

global $zamger_predmeti_pao;
$uslov = ima_li_uslov($userid, $proslagodina);
if ($ponovac == 0 && !$uslov) {
	niceerror("Nemate uslove za upis $godina. godine studija");
	print "Sačekajte da prikupite uslov ili popunite Ugovor za prethodnu godinu studija.";
	return;
}

// Kreiramo spiskove predmeta za prikaz na stranicama
$neparni_obavezni = $neparni_izborni = $parni_obavezni = $parni_izborni = array();
if ($ponovac==0) {
	// Ako student nije ponovac, *trebamo* prikazati predmete koje je položio u koliziji (jer ih prošle godine nije imao na Ugovoru)
	// TODO: Uprava se nije izjasnila da li je ovo ispravno ili nije
	$neparni_obavezni = db_query_table("select pp.sifra, pp.naziv, pp.ects from pasos_predmeta pp, plan_studija_predmet psp where psp.plan_studija=$plan_studija and psp.semestar=$sem1 and psp.obavezan=1 and psp.pasos_predmeta=pp.id");
	$parni_obavezni = db_query_table("select pp.sifra, pp.naziv, pp.ects from pasos_predmeta pp, plan_studija_predmet psp where psp.plan_studija=$plan_studija and psp.semestar=$sem2 and psp.obavezan=1 and psp.pasos_predmeta=pp.id");
}

// Spisak izbornih predmeta
for ($sem=$sem1; $sem<=$sem2; $sem++) {
	$izborni = array();
	$q110 = db_query("SELECT uoui.predmet FROM ugovoroucenju_izborni uoui, ugovoroucenju uou WHERE uoui.ugovoroucenju=uou.id AND uou.student=$userid and uou.akademska_godina=$zagodinu AND uou.semestar=$sem");
	while(db_fetch1($q110, $predmet)) {
		// Preskačemo predmete koje je student već položio
		if ($ponovac == 1) {
			$polozio = db_get("SELECT COUNT(*) FROM konacna_ocjena WHERE student=$userid AND predmet=$predmet AND ocjena>5");
			if ($polozio) continue;
		}
	
		// Uzimamo pasoš koji je važeći u tekućem NPPu
		$podaci = db_query_assoc("SELECT pp.sifra, pp.naziv, pp.ects FROM pasos_predmeta as pp, plan_studija_predmet psp, plan_izborni_slot pis WHERE psp.plan_studija=$plan_studija AND psp.plan_izborni_slot=pis.id AND pis.pasos_predmeta=pp.id AND pp.predmet=$predmet");
		
		// Nema ga - možda je izborni sa drugog odsjeka?
		// Uzimamo drugi studij sa istom godinom usvajanja
		if (!$podaci) 
			$podaci = db_query_assoc("SELECT pp.sifra, pp.naziv, pp.ects FROM pasos_predmeta as pp, plan_studija_predmet psp, plan_studija ps WHERE ps.godina_vazenja=$godina_vazenja AND psp.plan_studija=ps.id AND psp.pasos_predmeta=pp.id AND pp.predmet=$predmet");
		
		// Nije među obaveznim - možda je izborni na drugom odsjeku?
		if (!$podaci) 
			$podaci = db_query_assoc("SELECT pp.sifra, pp.naziv, pp.ects FROM pasos_predmeta as pp, plan_studija_predmet psp, plan_izborni_slot pis, plan_studija ps WHERE ps.godina_vazenja=$godina_vazenja AND psp.plan_studija=ps.id AND psp.plan_izborni_slot=pis.id AND pis.pasos_predmeta=pp.id AND pp.predmet=$predmet");

		if (!$podaci) 
			// E ne znam... preskačemo
			continue;
			
		$izborni[] = $podaci;
	}
	if ($sem == $sem1) $neparni_izborni = $izborni; else $parni_izborni = $izborni;
}

// Dodajemo predmete koje je student prenio sa prošle godine
foreach($zamger_predmeti_pao as $predmet => $naziv_predmeta) {
	// Uzimamo pasoš koji je važeći u tekućem NPPu
	$podaci = db_query_assoc("SELECT pp.sifra, pp.naziv, pp.ects, psp.semestar FROM pasos_predmeta as pp, plan_studija_predmet psp WHERE psp.plan_studija=$plan_studija AND psp.pasos_predmeta=pp.id AND pp.predmet=$predmet");
	
	if ($podaci) {
		if ($podaci['semestar'] % 2 == 0)
			$parni_obavezni[] = $podaci;
		else
			$neparni_obavezni[] = $podaci;
		continue;
	}
	
	// Nema ga - možda je izborni sa drugog odsjeka?
	// Uzimamo drugi studij sa istom godinom usvajanja
	if (!$podaci) 
		$podaci = db_query_assoc("SELECT pp.sifra, pp.naziv, pp.ects, psp.semestar FROM pasos_predmeta as pp, plan_studija_predmet psp, plan_studija ps WHERE ps.godina_vazenja=$godina_vazenja AND psp.plan_studija=ps.id AND psp.pasos_predmeta=pp.id AND pp.predmet=$predmet");
	
	// Sada gledamo izborne predmete
	if (!$podaci) 
		$podaci = db_query_assoc("SELECT pp.sifra, pp.naziv, pp.ects, psp.semestar FROM pasos_predmeta as pp, plan_studija_predmet psp, plan_izborni_slot pis WHERE psp.plan_studija=$plan_studija AND psp.plan_izborni_slot=pis.id AND pis.pasos_predmeta=pp.id AND pp.predmet=$predmet");
	
	// Drugi studij sa istom godinom usvajanja
	if (!$podaci) 
		$podaci = db_query_assoc("SELECT pp.sifra, pp.naziv, pp.ects, psp.semestar FROM pasos_predmeta as pp, plan_studija_predmet psp, plan_izborni_slot pis, plan_studija ps WHERE ps.godina_vazenja=$godina_vazenja AND psp.plan_studija=ps.id AND psp.plan_izborni_slot=pis.id AND pis.pasos_predmeta=pp.id AND pp.predmet=$predmet");
	
	if ($podaci) {
		// Preskačemo izborne predmete iz trenutnog semestra jer su oni već dodati kroz Ugovor ili se student predomislio
		if ($podaci['semestar'] == $sem1 || $podaci['semestar'] == $sem2) continue;
		if ($podaci['semestar'] % 2 == 0)
			$parni_izborni[] = $podaci;
		else
			$neparni_izborni[] = $podaci;
	}
	
	// if (!$podaci)
		// E ne znam... preskačemo
}


// Privremeni hack za master
if ($tipstudija==3) {
	$mscfile="-msc";
} else if ($tipstudija==2) {
	$mscfile="";
}


// Ako čovjek upisuje prvu godinu mastera, broj indexa je netačan!
if ($godina==1 && $tipstudija==3) $brindexa="";



// ----- Pravljenje PDF dokumenta


$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); 

// set document information
$pdf->SetCreator("Zamger");
$pdf->SetTitle('Domestic Learning Agreement / Ugovor o ucenju');

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
$pdf->SetFont('freesans', 'B', 9);

$pdf->SetHeaderData("",0,"","");
$pdf->SetPrintHeader(false);
$pdf->SetPrintFooter(false);

// add a page
$pdf->AddPage();


//	$pdf->Image("static/images/content/150dpi/ETF-Domestic-contract-PGS-ALL-0.png",210,297,0,0,'','','',true,150);
	$pdf->Image("static/images/content/150dpi/domestic-contract$mscfile-0.png",0,0,210,0,'','','',true,150); 
	$pdf->SetXY(175, 34);
	$pdf->Cell(23, 0, $agnaziv, 0, 0, 'C');
	$pdf->SetXY(175, 42);
	$pdf->Cell(23, 0, $godina.".", 0, 0, 'C');
	$pdf->SetXY(175, 50);
	$pdf->Cell(23, 0, $sem1.". & ".$sem2, 0, 0, 'C');
	$pdf->SetXY(70, 48);
	$pdf->Cell(100, 0, $studijeng, 0, 0);
	$pdf->SetXY(70, 52);
	$pdf->Cell(100, 0, $studijbos, 0, 0);
	
	$pdf->SetXY(70, 62);
	$pdf->Cell(100, 0, $imeprezime);
	$pdf->SetXY(70, 69);
	$pdf->Cell(100, 0, $brindexa);


	// PRVI SEMESTAR
	$pdf->AddPage();
	$pdf->Image("static/images/content/150dpi/domestic-contract$mscfile-1.png",0,0,210); 

	$pdf->SetXY(175, 34);
	$pdf->Cell(23, 0, $agnaziv, 0, 0, 'C');
	$pdf->SetXY(175, 42);
	$pdf->Cell(23, 0, $godina.".", 0, 0, 'C');
	$pdf->SetXY(175, 50);
	$pdf->Cell(23, 0, $sem1.".", 0, 0, 'C');
	$pdf->SetXY(70, 48);
	$pdf->Cell(100, 0, $studijeng, 0, 0);
	$pdf->SetXY(70, 52);
	$pdf->Cell(100, 0, $studijbos, 0, 0);
	
	$pdf->SetXY(70, 62);
	$pdf->Cell(100, 0, $imeprezime);
	$pdf->SetXY(70, 69);
	$pdf->Cell(100, 0, $brindexa);
	
	// Spisak obaveznih predmeta na neparnom semestru
	// Ako je ponovac, ne prikazujemo predmete koje je polozio

	$ykoord = 95;
	$ects = 0;
	foreach($neparni_obavezni as $predmet) {
		$pdf->SetXY(13, $ykoord);
		$pdf->Cell(100, 0, $predmet['sifra']);
		$pdf->SetXY(50, $ykoord);
		$pdf->Cell(100, 0, $predmet['naziv']);
		$e = "".$predmet['ects'];
		if (!strchr($e,".")) $e .= ".0";
		$pdf->SetXY(170, $ykoord);
		$pdf->Cell(100, 0, $e);
		$ykoord+=4;
		$ects += $predmet['ects'];
	}

	// Spisak izbornih predmeta
	$ykoord = 127;
	foreach($neparni_izborni as $predmet) {
		$pdf->SetXY(13, $ykoord);
		$pdf->Cell(100, 0, $predmet['sifra']);
		$pdf->SetXY(50, $ykoord);
		$pdf->Cell(100, 0, $predmet['naziv']);
		$e = "".$predmet['ects'];
		if (!strchr($e,".")) $e .= ".0";
		$pdf->SetXY(170, $ykoord);
		$pdf->Cell(100, 0, $e);
		$ykoord+=4;
		$ects += $predmet['ects'];
	}

	// Suma ects
	if (!strchr($ects,".")) $ects .= ".0";
	$pdf->SetXY(170, 139);
	$pdf->Cell(100, 0, $ects);

	// Kod
	$pdf->SetXY(90, 265);
	$pdf->Cell(100, 0, "Ugovor br. $kod_ugovora");

	
	// DRUGI SEMESTAR
	$pdf->AddPage();
	$pdf->Image("static/images/content/150dpi/domestic-contract$mscfile-2.png",0,0,210); 

	$pdf->SetXY(175, 34);
	$pdf->Cell(23, 0, $agnaziv, 0, 0, 'C');
	$pdf->SetXY(175, 42);
	$pdf->Cell(23, 0, $godina.".", 0, 0, 'C');
	$pdf->SetXY(175, 50);
	$pdf->Cell(23, 0, $sem2.".", 0, 0, 'C');
	$pdf->SetXY(70, 48);
	$pdf->Cell(100, 0, $studijeng, 0, 0);
	$pdf->SetXY(70, 52);
	$pdf->Cell(100, 0, $studijbos, 0, 0);
	
	$pdf->SetXY(70, 62);
	$pdf->Cell(100, 0, $imeprezime);
	$pdf->SetXY(70, 69);
	$pdf->Cell(100, 0, $brindexa);
	
	// Spisak obaveznih predmeta na parnom semestru
	$ykoord = 95;
	$ects = 0;
	foreach($parni_obavezni as $predmet) {
		$pdf->SetXY(13, $ykoord);
		$pdf->Cell(100, 0, $predmet['sifra']);
		$pdf->SetXY(50, $ykoord);
		$pdf->Cell(100, 0, $predmet['naziv']);
		$e = "".$predmet['ects'];
		if (!strchr($e,".")) $e .= ".0";
		$pdf->SetXY(170, $ykoord);
		$pdf->Cell(100, 0, $e);
		$ykoord+=4;
		$ects += $predmet['ects'];
	}

	// Spisak izbornih predmeta
	$ykoord = 127;
	foreach($parni_izborni as $predmet) {
		$pdf->SetXY(13, $ykoord);
		$pdf->Cell(100, 0, $predmet['sifra']);
		$pdf->SetXY(50, $ykoord);
		$pdf->Cell(100, 0, $predmet['naziv']);
		$e = "".$predmet['ects'];
		if (!strchr($e,".")) $e .= ".0";
		$pdf->SetXY(170, $ykoord);
		$pdf->Cell(100, 0, $e);
		$ykoord+=4;
		$ects += $predmet['ects'];
	}

	// Suma ects
	if (!strchr($ects,".")) $ects .= ".0";
	$pdf->SetXY(170, 139);
	$pdf->Cell(100, 0, $ects);

	// Kod
	$pdf->SetXY(90, 265);
	$pdf->Cell(100, 0, "Ugovor br. $kod_ugovora");

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output('ugovor_o_ucenju.pdf', 'I');

//============================================================+
// END OF FILE                                                 
//============================================================+




}
