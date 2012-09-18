<?

// STUDENT/KOLIZIJAPDF - generisanje PDFa na osnovu zahtjeva za koliziju

// Modul koristi biblioteku TCPDF



function student_kolizijapdf() {

global $userid;

require_once('lib/tcpdf/tcpdf.php');

$semestar = intval($_REQUEST['semestar']);
if ($semestar==1) $tekst_semestar = "zimskom";
else $tekst_semestar = "ljetnjem";


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


// Koji je odsjek?

$q4 = myquery("select s.id, s.naziv, i.naziv, ss.semestar from studij as s, student_studij as ss, institucija as i where ss.student=$userid and ss.studij=s.id and s.institucija=i.id and ss.akademska_godina=$proslagodina order by semestar desc limit 1");
if (mysql_num_rows($q4)<1) {
	// Očito da su aktuelna i prošla godina loše određene
	$q4 = myquery("select s.id, s.naziv, i.naziv, ss.semestar, ss.akademska_godina from studij as s, student_studij as ss, institucija as i where ss.student=$userid and ss.studij=s.id and s.institucija=i.id order by akademska_godina desc, semestar desc limit 1");
	if (mysql_num_rows($q4)<1) {
		biguglyerror("Nikada niste bili nas student!");
		return;
	}
	$proslagodina = mysql_result($q4,0,4);
	if (mysql_num_rows($q2)<1) {
		biguglyerror("Nije kreirana nova akademska godina u koju se upisujete.");
		print "Kontaktirajte administratora.";
		return;
	}
	$q2 = myquery("select id, naziv from akademska_godina where id>$proslagodina order by id limit 1");
	$zagodinu  = mysql_result($q2,0,0);
	$agnaziv  = mysql_result($q2,0,1);
}
$studij = mysql_result($q4,0,0);
$studij_naziv = mysql_result($q4,0,1);
$institucija_naziv = mysql_result($q4,0,2);
$godina_studija = ceil(mysql_result($q4,0,3)/2);


// Da li je student popunio ugovor za drugi odsjek?
$tekst_mijenja = "";
$q7 = myquery("select s.id, s.naziv, i.naziv from studij as s, ugovoroucenju as uou, institucija as i where uou.student=$userid and uou.studij=s.id and s.institucija=i.id and uou.akademska_godina=$zagodinu");
if (mysql_num_rows($q7)>1 && $studij != mysql_result($q7,0,0)) {
	$institucija_naziv = mysql_result($q7,0,2);
	$tekst_mijenja = "predao sam zahtjev za promjenu studija na ".mysql_result($q7,0,1).". S tim u vezi, ";
}


// Zapis u tabeli kolizija
$predmeti_kolizija=$predmeti_ects=array();
$q10 = myquery("select p.id, p.naziv, p.ects from kolizija as k, predmet as p where k.student=$userid and k.akademska_godina=$zagodinu and k.semestar=$semestar and k.predmet=p.id");
if (mysql_num_rows($q10)<1) {
	biguglyerror("Vi niste popunili Zahtjev za koliziju za $agnaziv godinu!");
	print "Ako je ovo greska, kontaktirajte administratora.";
	return;
}
while ($r10 = mysql_fetch_row($q10)) {
	$predmeti_kolizija[$r10[0]]=$r10[1];
	$predmeti_ects[$r10[0]]=$r10[2];
}


if ($semestar==1) $s2=1; else $s2=0;

// Predmeti koje nije polozio
$predmeti_prenos=array();
$q20 = myquery("select p.id, p.naziv, p.ects from student_predmet as sp, ponudakursa as pk, predmet as p where sp.student=$userid and sp.predmet=pk.id and pk.predmet=p.id and pk.akademska_godina=$proslagodina and pk.semestar MOD 2=$s2 and pk.semestar<$godina_studija*2+1 and (select count(*) from konacna_ocjena as ko where ko.student=$userid and ko.predmet=p.id and ko.ocjena != 5)=0");
while ($r20 = mysql_fetch_row($q20)) {
	if (array_key_exists($r20[0], $predmeti_kolizija)) continue;
	$predmeti_prenos[$r20[0]]=$r20[1];
	$predmeti_ects[$r20[0]]=$r20[2];
}


// Podaci o studentu
$q30 = myquery("select ime, prezime, brindexa, spol from osoba where id=$userid");
$ime = mysql_result($q30,0,0);
$prezime = mysql_result($q30,0,1);
$brindexa = mysql_result($q30,0,2);
$spol = mysql_result($q30,0,3);
if ($spol=="") $spol=spol(mysql_result($q30,0,0));

if ($spol=="M") { $student="student"; $polozio="položio"; }
else { $student="studentica"; $polozio="položila"; }

?>
<html>
<head>
<title>Zahtjev za koliziju</title>
</head>
<body>
<p>Univerzitet u Sarajevu<br>
Elektrotehnički fakultet Sarajevo<br>
<?=$institucija_naziv?></p>

<p>&nbsp;</p>

<p>Student: <?="$ime $prezime ($brindexa)"?></p>

<p>&nbsp;</p>

<p><b>Predmet: ZAHTJEV ZA KOLIZIJU</b></p>

<p>&nbsp;</p>

<p>Ja, <?="$ime $prezime"?>, <?=$student?> studija <?=$studij_naziv?>, <?=$godina_studija?>. godina, broj indexa <?=$brindexa?>, <?=$tekst_mijenja?> molim Vas da mi u skladu sa Zakonom o visokom obrazovanju Kantona Sarajevo, u <?=$tekst_semestar?> semestru akademske <?=$agnaziv?> godine odobrite slušanje sljedećih predmeta sa <?=($godina_studija+1)?>. godine studija u koliziji:</p>

<ul>
<?
foreach ($predmeti_kolizija as $id=>$predmet)
	print "<li>$predmet (".$predmeti_ects[$id]." ECTS)</li>\n";
?>
</ul>

<p>&nbsp;</p>

<p>Obzirom da sa <?=$godina_studija?>. godine studija nisam <?=$polozio?> sljedeće predmete:</p>

<ul>
<?
foreach ($predmeti_prenos as $id=>$predmet)
	print "<li>$predmet (".$predmeti_ects[$id]." ECTS)</li>\n";
?>
</ul>

te da se jedan predmet prenosi, nije prekoračen maksimalan broj od 30 ECTS kredita po semestru.</p>

<p>&nbsp;</p>

<p>U nadi da ćete udovoljiti mom zahtjevu,</p>

<p>&nbsp;</p>

<table border="0"><tr><td width="100%">&nbsp;</td><td align="right"><p>&nbsp;</p><p>_____________________________________</p></td></tr>
<tr><td width="100%">&nbsp;</td><td align="center"><?="$ime $prezime"?></td></tr></table>
</body>
</html>
<?

return;



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


// Najnoviji plan za odabrani studij
$q6 = myquery("select godina_vazenja from plan_studija where studij=$studij order by godina_vazenja desc limit 1");
if (mysql_num_rows($q6)<1) { 
	niceerror("Nepostojeći studij");
	return;
}
$najnoviji_plan = mysql_result($q6,0,0);


// Da li je ponovac?
$q20 = myquery("select semestar from student_studij where student=$userid and studij=$studij and akademska_godina=$proslagodina order by semestar desc limit 1");
if ($sem1>mysql_result($q20,0,0)) 
	$ponovac=0;
else
	$ponovac=1;

// Odredjujemo da li ima prenesenih predmeta
// TODO: ovo sada ne radi za izborne predmete
$q20 = myquery("select p.sifra, p.naziv, p.ects, ps.semestar from predmet as p, plan_studija as ps where ps.godina_vazenja=$najnoviji_plan and ps.studij=$studij and (ps.semestar=".($sem1-1)." or ps.semestar=".($sem1-2).") and ps.obavezan=1 and ps.predmet=p.id and (select count(*) from konacna_ocjena as ko where ko.student=$userid and ko.predmet=p.id)=0");
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


// Ako čovjek upisuje prvu godinu nečeka (mastera), broj indexa je netačan!
if ($godina==1) $brindexa="";



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
		$q100 = myquery("select p.sifra, p.naziv, p.ects from predmet as p, plan_studija as ps where ps.godina_vazenja=$najnoviji_plan and ps.studij=$studij and ps.semestar=$sem1 and ps.obavezan=1 and ps.predmet=p.id and (select count(*) from konacna_ocjena as ko where ko.student=$userid and ko.predmet=p.id)=0");
	else
	// Ako nije, trebamo prikazati one koje je položio u koliziji
		$q100 = myquery("select p.sifra, p.naziv, p.ects from predmet as p, plan_studija as ps where ps.godina_vazenja=$najnoviji_plan and ps.studij=$studij and ps.semestar=$sem1 and ps.obavezan=1 and ps.predmet=p.id");

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
		$q100 = myquery("select p.sifra, p.naziv, p.ects from predmet as p, plan_studija as ps where ps.godina_vazenja=$najnoviji_plan and ps.studij=$studij and ps.semestar=$sem2 and ps.obavezan=1 and ps.predmet=p.id and (select count(*) from konacna_ocjena as ko where ko.student=$userid and ko.predmet=p.id)=0");
	else
		$q100 = myquery("select p.sifra, p.naziv, p.ects from predmet as p, plan_studija as ps where ps.godina_vazenja=$najnoviji_plan and ps.studij=$studij and ps.semestar=$sem2 and ps.obavezan=1 and ps.predmet=p.id");
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
