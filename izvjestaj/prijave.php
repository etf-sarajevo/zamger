<?

// IZVJESTAJ/PRIJAVE - generisanje PDFa sa prijavom



function izvjestaj_prijave() {

require_once('lib/tcpdf/tcpdf.php');

global $userid,$conf_files_path;

// Poslani parametar
$ispit_termin=intval($_GET['ispit_termin']);
$predmet=intval($_GET['predmet']);
$ag=intval($_GET['ag']);
$student=intval($_GET['student']);

$nasa_slova = array("č"=>"c", "ć" => "c", "đ" => "d", "š" => "s", "ž" => "z", "Č" => "C", "Ć" => "C", "Đ" => "D", "Š" => "S", "Ž" => "Z");

// Odredjujemo filename
if ($ispit_termin>0) {
	$q5 = db_query("select p.id, p.naziv, UNIX_TIMESTAMP(it.datumvrijeme), i.akademska_godina from predmet as p, ispit as i, ispit_termin as it where it.id=$ispit_termin and it.ispit=i.id and i.predmet=p.id");

	$predmet = db_result($q5,0,0);
	$ag = db_result($q5,0,3);
	$filename = "prijave-".strtr(db_result($q5,0,1), $nasa_slova)."-".date("d-m-Y", db_result($q5,0,2)).".pdf";

} else if ($predmet>0) {
	$q5 = db_query("select naziv from predmet where id=$predmet");
	$filename = "prijave-".strtr(db_result($q5,0,0), $nasa_slova).".pdf";

} else {
	$filename = "prijave.pdf";
}



$upit = "SELECT o.id, o.ime, o.prezime, o.brindexa, pk.semestar, s.naziv, p.naziv, ag.naziv, "; // slijedi datum


// Stampaj sve studente na terminu
if ($ispit_termin>0) {
	// Uzimamo datum termina
	$upit .= "UNIX_TIMESTAMP(it.datumvrijeme) from osoba as o, ispit_termin as it, student_ispit_termin as sit, student_predmet as sp, ponudakursa as pk, ispit as i, studij as s, predmet as p, akademska_godina as ag where sit.ispit_termin=it.id and sit.student=o.id and it.id=$ispit_termin and o.id=sp.student and sp.predmet=pk.id and it.ispit=i.id and i.predmet=pk.predmet and i.akademska_godina=pk.akademska_godina and pk.studij=s.id and pk.predmet=p.id and pk.akademska_godina=ag.id order by o.prezime, o.ime";

} else if ($predmet<=0 || $ag<=0) {
	biguglyerror("Neispravni parametri");
	print "Da li je moguće da ste odabrali neispravan ili nepostojeći predmet?";
	return;

// Stampaj jednog studenta
} else if ($student>0) {
	// Uzecemo danasnji datum
	$upit .= "UNIX_TIMESTAMP(NOW()) from osoba as o, ponudakursa as pk, studij as s, predmet as p, akademska_godina as ag, student_predmet as sp where o.id=$student and sp.student=$student and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag and p.id=$predmet and ag.id=$ag and pk.studij=s.id";

// Sve studente koji nemaju ocjenu
} else if ($_GET['tip'] == "bez_ocjene" || $_GET['tip'] == "uslov") { // Naknadno provjeravamo da li ima uslov
	// Uzecemo danasnji datum
	$upit .= "UNIX_TIMESTAMP(NOW()) from osoba as o, ponudakursa as pk, studij as s, predmet as p, akademska_godina as ag, student_predmet as sp where o.id=sp.student and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag and p.id=$predmet and ag.id=$ag and pk.studij=s.id and (select count(*) from konacna_ocjena as ko where ko.student=o.id and ko.predmet=$predmet)=0 order by o.prezime, o.ime";

// Sve studente koji imaju ocjenu
} else if ($_GET['tip'] == "sa_ocjenom") {
	// Uzecemo danasnji datum
	$upit .= "UNIX_TIMESTAMP(NOW()) from osoba as o, ponudakursa as pk, studij as s, predmet as p, akademska_godina as ag, student_predmet as sp where o.id=sp.student and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag and p.id=$predmet and ag.id=$ag and pk.studij=s.id and (select count(*) from konacna_ocjena as ko where ko.student=o.id and ko.predmet=$predmet and ko.ocjena>5)>0 order by o.prezime, o.ime";

// Sve studente na predmetu
} else if ($_GET['tip'] == "sve") {
	// Uzecemo danasnji datum
	$upit .= "UNIX_TIMESTAMP(NOW()) from osoba as o, ponudakursa as pk, studij as s, predmet as p, akademska_godina as ag, student_predmet as sp where o.id=sp.student and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag and p.id=$predmet and ag.id=$ag and pk.studij=s.id order by o.prezime, o.ime";

// Sve studente na terminima koji padaju u određeni datum
} else if ($_GET['tip'] == "na_datum") {
	// Provjera da li je datum ispravan i konverzija u mysql format
	$matches = array();
	if (!preg_match("/^(\d+)\.(\d+)\.(\d+)$/", $_GET['datum'], $matches)) {
		biguglyerror("Datum nije u ispravnom formatu");
		return;
	}
	$mysql_datum = $matches[3] . "-" . $matches[2] . "-" . $matches[1];
	
	// Uzecemo danasnji datum
	$upit .= "UNIX_TIMESTAMP(it.datumvrijeme) FROM osoba as o, ispit_termin as it, student_ispit_termin as sit, student_predmet as sp, ponudakursa as pk, ispit as i, studij as s, predmet as p, akademska_godina as ag 
	WHERE sit.ispit_termin=it.id and sit.student=o.id and DATE(it.datumvrijeme)='$mysql_datum' and o.id=sp.student and sp.predmet=pk.id and it.ispit=i.id and i.predmet=pk.predmet and i.akademska_godina=pk.akademska_godina and pk.studij=s.id and pk.predmet=p.id and pk.akademska_godina=ag.id and pk.akademska_godina=$ag and p.id=$predmet
	ORDER BY o.prezime, o.ime";

// Ovo se može desiti ako se klikne na prikaz pojedinačnog studenta, a nijedan student nije izabran
// (npr. ako nijedan student ne sluša predmet)
} else {
	biguglyerror("Neispravni parametri");
	print "Da li je moguće da ovaj predmet ne sluša niti jedan student?";
	return;
}


// PDF inicijalizacija
$pdf = new TCPDF('P', 'mm', 'a5', true, 'UTF-8', false);

$pdf->SetCreator("Zamger");
$pdf->SetTitle('Printanje prijava');
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(0,0,0);
$pdf->SetAutoPageBreak(false);
$pdf->setLanguageArray($l); 
$pdf->SetFont('freesans', 'B', 9);
$pdf->SetHeaderData("",0,"","");
$pdf->SetPrintHeader(false);
$pdf->setFooterMargin($fm=0);
$pdf->SetPrintFooter(false);

//set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 
//$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO*2.083333); 
$pdf->setJPEGQuality(100); 


// Izvršenje upita

$q10 = db_query($upit);

while ($r10 = db_fetch_row($q10)) {
	$student=$r10[0];
	$imeprezime=$r10[1]." ".$r10[2];
	$brind=$r10[3];
	$godStudija=intval(($r10[4]+1)/2);
	$odsjek=$r10[5];
	$nazivPr=$r10[6];
	$skolskaGod=$r10[7];
//	$NastavnikSl=$r10[9];
	$datumIspita=date("d. m. Y.", $r10[8]);
//	$NastavnikPr=$r10[8];
//	$datumPrijave=$r10[12]; 
	$datumPrijave=$datumIspita;
//	$datumPolaganja=$r10[10];
	$datumPolaganja=$datumIspita;
//	$datumUsmenog=$r10[13];
	$datumUsmenog=$datumIspita;
//	$datumDrPar=$r10[14];

	// Ispis nastavnika
	$q33 = db_query("select osoba from angazman where predmet=$predmet and akademska_godina=$ag and angazman_status=1");
	if (db_num_rows($q33)==1) { // Ako imaju dva odgovorna nastavnika, ne znam kojeg da stavim
		$id_nastavnika = db_result($q33,0,0);
		$nastavnik = tituliraj($id_nastavnika, $sa_akademskim_zvanjem=false);
	} else {
		$nastavnik="";
	}

	// Da li ima uslov?
	if ($_GET['tip']=="uslov") { 
		// Dva parcijalna ispita
		$q35 = db_query("select count(*) from ispitocjene as io, ispit as i, komponenta as k where io.student=$student and io.ispit=i.id and i.predmet=$predmet and i.akademska_godina=$ag and i.komponenta=k.id and k.tipkomponente=1 and io.ocjena>=k.prolaz");
		$parcijalnih = db_result($q35,0,0);
		// Integralni ispiti
		$q37 = db_query("select count(*) from ispitocjene as io, ispit as i, komponenta as k where io.student=$student and io.ispit=i.id and i.predmet=$predmet and i.akademska_godina=$ag and i.komponenta=k.id and k.tipkomponente=2 and io.ocjena>=k.prolaz");
		$integralnih = db_result($q37,0,0);
		if ($integralnih==1 || $parcijalnih==2) // FIXME: ovo radi samo za ETF Bologna standard
			kreirajPrijavu($pdf, $imeprezime, $brind, $godStudija, $odsjek, $nazivPr, $skolskaGod, $datumIspita, $nastavnik);

	} else {
		// Da li je student polozio predmet?
		$q40 = db_query("select ko.ocjena, UNIX_TIMESTAMP(ko.datum_u_indeksu), ko.datum_provjeren, pp.naziv from konacna_ocjena ko, pasos_predmeta pp where ko.student=$student and ko.predmet=$predmet and ko.pasos_predmeta=pp.id");
		if (db_num_rows($q40)>0) {
			$ocjena = db_result($q40,0,0);
			$datum_provjeren = db_result($q40,0,2);
			if ($datum_provjeren) $datumIspita=$datumPrijave=$datumPolaganja=$datumUsmenog=date("d. m. Y.", db_result($q40,0,1));
			$nazivPr = db_result($q40,0,3);
		} else $ocjena=0;

		kreirajPrijavu($pdf, $imeprezime, $brind, $godStudija, $odsjek, $nazivPr, $skolskaGod, $datumIspita, $ocjena, $nastavnik);
//		print "$pdf, $imeprezime, $brind, $godStudija, $odsjek, $nazivPr, $skolskaGod, $datumIspita ($r10[8]), $ocjena, $nastavnik<br>\n";
	}
}

$pdf->Output($filename, 'I');

} // function izvjestaj_prijave()



function kreirajPrijavu($pdf, $imeprezime, $brind, $godStudija, $odsjek, $nazivPr, $skolskaGod, $datumIspita, $ocjena, $nastavnik) {
	$datumPrijave=$datumIspita;
	$datumPolaganja=$datumIspita;
	$datumUsmenog=$datumIspita;

	$NastavnikSl = $NastavnikPr = $nastavnik;

	$imeocjene = array("", "", "", "", "", "pet", "šest", "sedam", "osam", "devet", "deset");

	$fontzapredmet=12;
	if (strlen($nazivPr)>40) $fontzapredmet=10;

	$pdf->AddPage();
	
	$pdf->Image("static/images/content/150dpi/prijava-blanko.png",0,0,148,0,'','','',true,150);
	
	// broj indexa
	$pdf->SetY(20);
	$pdf->SetX(108);
	$pdf->SetFont('freesans','',12);
	$pdf->Cell(30,-136,$brind, 0, 0, 'C');
	
	// naziv ustanove
	$pdf->SetY(30);
	$pdf->SetX(12);
	$pdf->SetFont('freesans','',11);
	$pdf->Cell(90,-136,'ELEKTROTEHNIČKI FAKULTET SARAJEVO', 0, 0, 'C');
	
	/*// redovan1
	$pdf->SetY(32.5);
	$pdf->SetX(101.5);
	$pdf->SetFont('freesans','',12);
	$pdf->Cell(160,-136,"|_____|");
	
	// redovan2
	$pdf->SetY(28.1);
	$pdf->SetX(101.7);
	$pdf->SetFont('freesans','',14);
	$pdf->Cell(160,-136,"_____");
	*/
	
	// ime i prezime studenta
	$pdf->SetY(50);
	$pdf->SetX(12);
	$pdf->SetFont('freesans','',12);
	$pdf->Cell(90,-136,$imeprezime, 0, 0, 'C');
	
	// godina studija
	$pdf->SetY(50);
	$pdf->SetX(108);
	$pdf->SetFont('freesans','',12);
	$pdf->Cell(30,-136,$godStudija, 0, 0, 'C');
	
	// odsjek
	$pdf->SetY(60);
	$pdf->SetX(12);
	$pdf->SetFont('freesans','',12);
	$pdf->Cell(126,-136,$odsjek, 0, 0, 'C');
	
	// predmet
	$pdf->SetY(70);
	$pdf->SetX(12);
	$pdf->SetFont('freesans','',$fontzapredmet);
	$pdf->Cell(80,-136,$nazivPr, 0, 0, 'C');
	
	// koji put izlazite
	$pdf->SetY(70);
	$pdf->SetX(95);
	$pdf->SetFont('freesans','',12);
	$pdf->Cell(44,-136,'1. (prvi)', 0, 0, 'C');
	
	// skolska godina
	$pdf->SetY(80);
	$pdf->SetX(12);
	$pdf->SetFont('freesans','',12);
	$pdf->Cell(42,-136,$skolskaGod, 0, 0, 'C');
	
	// nastavnik kod kojeg se slusa predmet
	$pdf->SetY(80);
	$pdf->SetX(59);
	$pdf->SetFont('freesans','',12);
	$pdf->Cell(80,-136,$NastavnikSl, 0, 0, 'C');
	
	// datum ispita
	$pdf->SetY(91);
	$pdf->SetX(12);
	$pdf->SetFont('freesans','',12);
	$pdf->Cell(30,-136,$datumIspita, 0, 0, 'C');
	
	// nastavnik kod kojeg se polaze predmet
	$pdf->SetY(91);
	$pdf->SetX(47);
	$pdf->SetFont('freesans','',12);
	$pdf->Cell(92,-136,$NastavnikPr, 0, 0, 'C');
	
	// datum prijave ispita
	$pdf->SetY(101);
	$pdf->SetX(12);
	$pdf->SetFont('freesans','',12);
	$pdf->Cell(30,-136,$datumPrijave, 0, 0, 'C');
	
	// datum polaganja ispita
	$pdf->SetY(113);
	$pdf->SetX(12);
	$pdf->SetFont('freesans','',12);
	$pdf->Cell(47,-136,$datumIspita, 0, 0, 'C');
	
	/*// ocjena pismenog dijela
	$pdf->SetY(120);
	$pdf->SetX(14);
	$pdf->SetFont('freesans','',12);
	$pdf->Cell(160,-136,$kon);
	*/
	// datum usmenog
	$pdf->SetY(125);
	$pdf->SetX(63);
	$pdf->SetFont('freesans','',12);
	$pdf->Cell(160,-136,$datumUsmenog);

	if ($ocjena>0) {
		// ocjena usmenog dijela
		$pdf->SetY(132);
		$pdf->SetX(63);
		$pdf->SetFont('freesans','',12);
		$pdf->Cell(160,-136,$imeocjene[$ocjena]." ($ocjena)");

		// konacna ocjena
		$pdf->SetY(130);
		$pdf->SetX(108);
		$pdf->SetFont('freesans','',12);
		$pdf->Cell(160,-136,$imeocjene[$ocjena]." ($ocjena)");
	}

	/*
	// datum drugog parcijalnog
	$pdf->SetY(150);
	$pdf->SetX(22);
	$pdf->SetFont('Helvetica','',14);
	$pdf->Cell(160,-136,$datumDrPar);
	
	*/
	//EVIDENCIJA
	
	// ime i prezime studenta
	$pdf->SetY(175);
	$pdf->SetX(12);
	$pdf->SetFont('freesans','',12);
	$pdf->Cell(72,-136,$imeprezime, 0, 0, 'C');
	
	// godina studija
	$pdf->SetY(175);
	$pdf->SetX(89);
	$pdf->SetFont('freesans','',12);
	$pdf->Cell(23,-136,$godStudija, 0, 0, 'C');
	
	// broj indexa
	$pdf->SetY(175);
	$pdf->SetX(115);
	$pdf->SetFont('freesans','',12);
	$pdf->Cell(24,-136,$brind, 0, 0, 'C');
	
	// predmet
	$pdf->SetY(185);
	$pdf->SetX(12);
	$pdf->SetFont('freesans','',$fontzapredmet);
	$pdf->Cell(85,-136,$nazivPr, 0, 0, 'C');
	
	// koji put izlazite
	$pdf->SetY(185);
	$pdf->SetX(100);
	$pdf->SetFont('freesans','',12);
	$pdf->Cell(39,-136,'1. (prvi)', 0, 0, 'C');
	
	// datum usmenog
	$pdf->SetY(194);
	$pdf->SetX(10);
	$pdf->SetFont('freesans','',12);
	$pdf->Cell(160,-136,$datumUsmenog);

	// Konacna ocjena
	if ($ocjena>0) {
		$pdf->SetY(194);
		$pdf->SetX(40);
		$pdf->SetFont('freesans','',12);
		$pdf->Cell(160,-136,$imeocjene[$ocjena]." ($ocjena)");
	}
}

?>
