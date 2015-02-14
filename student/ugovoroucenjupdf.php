<?

// STUDENT/UGOVOROUCENJUPDF - generisanje PDFa na osnovu ugovora o ucenju

// v4.0.9.1 (2009/07/17) + Novi modul za ugovor o ucenju
// v4.0.9.2 (2009/08/25) + Razdvajam fajlove po ciklusu studija

// Modul koristi biblioteku TCPDF



function student_ugovoroucenjupdf() {

global $userid;

require_once('lib/tcpdf/tcpdf.php');

// Prikupljam podatke iz baze

// Za koju godinu se prijavljuje?
$q1 = myquery("select id, naziv from akademska_godina where aktuelna=1");
$q2 = myquery("select id, naziv from akademska_godina where id>".mysql_result($q1,0,0)." order by id limit 1");
if (mysql_num_rows($q2)<1) {
//	nicemessage("U ovom trenutku nije aktiviran upis u sljedeću akademsku godinu.");
//	return;
	// Pretpostavljamo da se upisuje u aktuelnu?
	$zagodinu  = mysql_result($q1,0,0);
	$agnaziv  = mysql_result($q1,0,1);
	$q3 = myquery("select id from akademska_godina where id<$zagodinu order by id desc limit 1");
	$proslagodina = mysql_result($q3,0,0);
} else {
	$proslagodina = mysql_result($q1,0,0);
	$zagodinu = mysql_result($q2,0,0);
	$agnaziv = mysql_result($q2,0,1);
}


// Zapis u tabeli ugovoroucenju
$q5 = myquery("select uu.id, s.id, s.naziv, s.naziv_en, uu.semestar, s.tipstudija from ugovoroucenju as uu, studij as s where uu.student=$userid and uu.akademska_godina=$zagodinu and uu.studij=s.id order by semestar desc limit 1");
if (mysql_num_rows($q5)<1) {
	niceerror("Nije kreiran ugovor o učenju za studenta.");
	return;
}

$ugovorid = mysql_result($q5,0,0);
$studij = mysql_result($q5,0,1);
$studijbos = mysql_result($q5,0,2);
$studijbos = substr($studijbos, 0, strpos($studijbos, "(")-1);
$studijeng = mysql_result($q5,0,3);
$sem2 = mysql_result($q5,0,4);
$tipstudija = mysql_result($q5,0,5);

$sem1 = $sem2-1;
$godina = $sem2/2;


// Ostali podaci o osobi
$q10 = myquery("select ime, prezime, brindexa from osoba where id=$userid");
$imeprezime = mysql_result($q10,0,0)." ".mysql_result($q10,0,1);
$brindexa = mysql_result($q10,0,2);


// Odabir plana studija
$plan_studija = 0;
$q5a = myquery("SELECT studij, plan_studija FROM student_studij WHERE student=$userid AND akademska_godina<=$zagodinu ORDER BY akademska_godina DESC LIMIT 1");
if (mysql_num_rows($q5a)>0 && $studij ==  mysql_result($q5a,0,0))
	$plan_studija = mysql_result($q5a,0,1);

if ($plan_studija == 0) {
	// Student nije prethodno studirao na istom studiju ili plan studija nije bio definisan
	// Uzimamo najnoviji plan za odabrani studij
	$q6 = myquery("select godina_vazenja from plan_studija where studij=$studij order by godina_vazenja desc limit 1");
	if (mysql_num_rows($q6)<1) { 
		niceerror("Nepostojeći studij");
		return;
	}
	$plan_studija = mysql_result($q6,0,0);
}


// Da li je ponovac (ikada slušao isti tip studija)?
$q20 = myquery("select ss.semestar from student_studij as ss, studij as s, tipstudija as ts where ss.student=$userid and ss.akademska_godina<=$proslagodina and ss.studij=s.id and s.tipstudija=$tipstudija order by semestar desc limit 1");
if (mysql_num_rows($q20)<1) { 
	/*niceerror("Ne možete popunjavati ugovor o učenju ako prvi put slušate prvu godinu studija.");
	return;*/
	// Zašto ne bismo dozvolili?
	$ponovac = 0;
} else {
	if ($sem1>mysql_result($q20,0,0)) 
		$ponovac=0;
	else
		$ponovac=1;
}

// Odredjujemo da li ima prenesenih predmeta
// TODO: ovo sada ne radi za izborne predmete
$q20 = myquery("select p.sifra, p.naziv, p.ects, ps.semestar from predmet as p, plan_studija as ps where ps.godina_vazenja=$plan_studija and ps.studij=$studij and (ps.semestar=".($sem1-1)." or ps.semestar=".($sem1-2).") and ps.obavezan=1 and ps.predmet=p.id and (select count(*) from konacna_ocjena as ko where ko.student=$userid and ko.predmet=p.id)=0");
if (mysql_num_rows($q20)>1) {
	niceerror("Nemate uslove za upis $godina. godine studija");
	print "Sačekajte da prikupite uslov ili popunite Ugovor za prethodnu godinu studija.";
	return;
}
if (mysql_num_rows($q20)==1) {
	$ima_preneseni=1;
	$preneseni_sifra=mysql_result($q20,0,0);
	$preneseni_naziv=mysql_result($q20,0,1);
	$preneseni_ects=mysql_result($q20,0,2);
	$preneseni_semestar=mysql_result($q20,0,3);
} else {
	$ima_preneseni=0;
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


//	$pdf->Image("images/content/150dpi/ETF-Domestic-contract-PGS-ALL-0.png",210,297,0,0,'','','',true,150);
	$pdf->Image("images/content/150dpi/domestic-contract$mscfile-0.png",0,0,210,0,'','','',true,150); 
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
	$pdf->Image("images/content/150dpi/domestic-contract$mscfile-1.png",0,0,210); 

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
	if ($ponovac==1) 
		$q100 = myquery("select p.sifra, p.naziv, p.ects from predmet as p, plan_studija as ps where ps.godina_vazenja=$plan_studija and ps.studij=$studij and ps.semestar=$sem1 and ps.obavezan=1 and ps.predmet=p.id and (select count(*) from konacna_ocjena as ko where ko.student=$userid and ko.predmet=p.id)=0");
	else
	// Ako nije, trebamo prikazati one koje je položio u koliziji
		$q100 = myquery("select p.sifra, p.naziv, p.ects from predmet as p, plan_studija as ps where ps.godina_vazenja=$plan_studija and ps.studij=$studij and ps.semestar=$sem1 and ps.obavezan=1 and ps.predmet=p.id");

	$ykoord = 95;
	$ects = 0;
	while ($r100 = mysql_fetch_row($q100)) {
		$pdf->SetXY(13, $ykoord);
		$pdf->Cell(100, 0, $r100[0]);
		$pdf->SetXY(50, $ykoord);
		$pdf->Cell(100, 0, $r100[1]);
		$e = "$r100[2]";
		if (!strchr($e,".")) $e .= ".0";
		$pdf->SetXY(170, $ykoord);
		$pdf->Cell(100, 0, $e);
		$ykoord+=4;
		$ects += $r100[2];
	}

	// Da li je prenesen predmet na neparnom semestru?
	if ($ima_preneseni && $preneseni_semestar%2==1) {
		$pdf->SetXY(13, $ykoord);
		$pdf->Cell(100, 0, $preneseni_sifra);
		$pdf->SetXY(50, $ykoord);
		$pdf->Cell(100, 0, $preneseni_naziv);
		$e = "$preneseni_ects";
		if (!strchr($e,".")) $e .= ".0";
		$pdf->SetXY(170, $ykoord);
		$pdf->Cell(100, 0, $e);
		$ykoord+=4;
		$ects += $preneseni_ects;
	}

	// Spisak izbornih predmeta
	if ($ponovac==1)
		$q110 = myquery("select p.sifra, p.naziv, p.ects from predmet as p, ugovoroucenju_izborni as uoui, ugovoroucenju as uu where uoui.ugovoroucenju=uu.id and uu.student=$userid and uu.akademska_godina=$zagodinu and uoui.predmet=p.id and uu.semestar=$sem1 and (select count(*) from konacna_ocjena as ko where ko.student=$userid and ko.predmet=p.id)=0");
	else
		$q110 = myquery("select p.sifra, p.naziv, p.ects from predmet as p, ugovoroucenju_izborni as uoui, ugovoroucenju as uu where uoui.ugovoroucenju=uu.id and uu.student=$userid and uu.akademska_godina=$zagodinu and uoui.predmet=p.id and uu.semestar=$sem1");

	$ykoord = 123;
	while ($r110 = mysql_fetch_row($q110)) {
		$pdf->SetXY(13, $ykoord);
		$pdf->Cell(100, 0, $r110[0]);
		$pdf->SetXY(50, $ykoord);
		$pdf->Cell(100, 0, $r110[1]);
		$e = "$r110[2]";
		if (!strchr($e,".")) $e .= ".0";
		$pdf->SetXY(170, $ykoord);
		$pdf->Cell(100, 0, $e);
		$ykoord+=4;
		$ects += $r110[2];
	}

	// Suma ects
	if (!strchr($ects,".")) $ects .= ".0";
	$pdf->SetXY(170, 135);
	$pdf->Cell(100, 0, $ects);


	// DRUGI SEMESTAR
	$pdf->AddPage();
	$pdf->Image("images/content/150dpi/domestic-contract$mscfile-2.png",0,0,210); 

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
	if ($ponovac==1)
		$q100 = myquery("select p.sifra, p.naziv, p.ects from predmet as p, plan_studija as ps where ps.godina_vazenja=$plan_studija and ps.studij=$studij and ps.semestar=$sem2 and ps.obavezan=1 and ps.predmet=p.id and (select count(*) from konacna_ocjena as ko where ko.student=$userid and ko.predmet=p.id)=0");
	else
		$q100 = myquery("select p.sifra, p.naziv, p.ects from predmet as p, plan_studija as ps where ps.godina_vazenja=$plan_studija and ps.studij=$studij and ps.semestar=$sem2 and ps.obavezan=1 and ps.predmet=p.id");
	$ykoord = 95;
	$ects = 0;
	while ($r100 = mysql_fetch_row($q100)) {
		$pdf->SetXY(13, $ykoord);
		$pdf->Cell(100, 0, $r100[0]);
		$pdf->SetXY(50, $ykoord);
		$pdf->Cell(100, 0, $r100[1]);
		$e = "$r100[2]";
		if (!strchr($e,".")) $e .= ".0";
		$pdf->SetXY(170, $ykoord);
		$pdf->Cell(100, 0, $e);
		$ykoord+=4;
		$ects += $r100[2];
	}

	// Da li je prenesen predmet na parnom semestru?
	if ($ima_preneseni && $preneseni_semestar%2==0) {
		$pdf->SetXY(13, $ykoord);
		$pdf->Cell(100, 0, $preneseni_sifra);
		$pdf->SetXY(50, $ykoord);
		$pdf->Cell(100, 0, $preneseni_naziv);
		$e = "$preneseni_ects";
		if (!strchr($e,".")) $e .= ".0";
		$pdf->SetXY(170, $ykoord);
		$pdf->Cell(100, 0, $e);
		$ykoord+=4;
		$ects += $preneseni_ects;
	}

	// Spisak izbornih predmeta
	if ($ponovac==1)
		$q110 = myquery("select p.sifra, p.naziv, p.ects from predmet as p, ugovoroucenju_izborni as uoui, ugovoroucenju as uu where uoui.ugovoroucenju=uu.id and uu.student=$userid and uu.akademska_godina=$zagodinu and uoui.predmet=p.id and uu.semestar=$sem2 and (select count(*) from konacna_ocjena as ko where ko.student=$userid and ko.predmet=p.id)=0");
	else
		$q110 = myquery("select p.sifra, p.naziv, p.ects from predmet as p, ugovoroucenju_izborni as uoui, ugovoroucenju as uu where uoui.ugovoroucenju=uu.id and uu.student=$userid and uu.akademska_godina=$zagodinu and uoui.predmet=p.id and uu.semestar=$sem2");
	$ykoord = 123;
	while ($r110 = mysql_fetch_row($q110)) {
		$pdf->SetXY(13, $ykoord);
		$pdf->Cell(100, 0, $r110[0]);
		$pdf->SetXY(50, $ykoord);
		$pdf->Cell(100, 0, $r110[1]);
		$e = "$r110[2]";
		if (!strchr($e,".")) $e .= ".0";
		$pdf->SetXY(170, $ykoord);
		$pdf->Cell(100, 0, $e);
		$ykoord+=4;
		$ects += $r110[2];
	}

	// Suma ects
	if (!strchr($ects,".")) $ects .= ".0";
	$pdf->SetXY(170, 135);
	$pdf->Cell(100, 0, $ects);

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output('ugovor_o_ucenju.pdf', 'I');

//============================================================+
// END OF FILE                                                 
//============================================================+




}
