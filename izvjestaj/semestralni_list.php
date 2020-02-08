<?

// IZVJESTAJ/SEMESTRALNI_LIST - popunjavanje semestralnog lista



function izvjestaj_semestralni_list() {

global $userid;

require_once('lib/tcpdf/tcpdf.php');
require_once("lib/utility.php"); // ends_with


$imena_semestara = array("", "prvi", "drugi", "treći", "četvrti", "peti", "šesti");
$imena_semestara_dativ = array("", "prvom", "drugom", "trećem", "četvrtom", "petom", "šestom");


if (isset($_REQUEST['upisni'])) $upisni=true; else $upisni=false;

// Prikupljam podatke iz baze

// Za koju godinu se prijavljuje?
$q1 = db_query("select id, naziv from akademska_godina where aktuelna=1");
// Da li postoji godina poslije aktuelne?
$q2 = db_query("select id, naziv from akademska_godina where id>".db_result($q1,0,0)." order by id limit 1");
if (db_num_rows($q2)<1) {
	// Ne postoji - pretpostavljamo da se upisuje u aktuelnu
	$zagodinu  = db_result($q1,0,0);
	$agnaziv  = db_result($q1,0,1);
	$q3 = db_query("select id from akademska_godina where id<$zagodinu order by id desc limit 1");
	$proslagodina = db_result($q3,0,0);
} else {
	$proslagodina = db_result($q1,0,0);
	$zagodinu = db_result($q2,0,0);
	$agnaziv = db_result($q2,0,1);
}


// Ostali podaci o osobi
$osoba = db_query_assoc("select ime, prezime, brindexa, jmbg, UNIX_TIMESTAMP(datum_rodjenja) ut_rodjenje, mjesto_rodjenja, drzavljanstvo, imeoca, imemajke, prezimeoca, prezimemajke, adresa, adresa_mjesto, telefon, kanton, spol, nacionalnost from osoba where id=$userid");
if ($userid == 0 || $osoba === false) {
	niceerror("Nepoznat korisnik");
	return;
}

$ime_prezime       = $osoba['ime']." ".$osoba['prezime'];
$ime_prezime_oca   = $osoba['imeoca']." ".$osoba['prezimeoca'];
$ime_prezime_majke = $osoba['imemajke']." ".$osoba['prezimemajke'];
$ime_roditelj_prezime = $ime_prezime;
if (!empty($osoba['imeoca'])) 
	$ime_roditelj_prezime = $osoba['ime']." (".$osoba['imeoca'].") ".$osoba['prezime'];
else if (!empty($osoba['imemajke']))
	$ime_roditelj_prezime = $osoba['ime']." (".$osoba['imemajke'].") ".$osoba['prezime'];
$datum_rodjenja = date ("d. m. Y.", $osoba['ut_rodjenje']);

$mr = false;
if (!empty($osoba['mjesto_rodjenja'])) {
	$q20 = db_query("SELECT m.naziv naziv, m.opcina opcina, m.opcina_van_bih ovb, d.naziv drzava FROM mjesto as m, drzava as d WHERE m.id=".$osoba['mjesto_rodjenja']." AND m.drzava=d.id");
	$mr = db_fetch_assoc($q20);
}
if ($mr) {
	if ($mr['drzava'] == "Bosna i Hercegovina") {
		$q30 = db_query("SELECT naziv FROM opcina WHERE id=".$mr['opcina']);
		$q40 = db_query("SELECT naziv FROM kanton WHERE id=".$osoba['kanton']);
		$mjesto_rodjenja_1 = $mr['naziv'] . ", " . db_result($q30,0,0) . ",";
		$mjesto_rodjenja_2 = db_result($q40,0,0) . ", Bosna i Hercegovina";
	} else {
		$mjesto_rodjenja_1 = $mr['naziv'] . ", ";
		if (!empty($mr['ovb'])) $mjesto_rodjenja_1 .= $mr['ovb'] . ",";
		else $mjesto_rodjenja_1 .= "(nije u BiH),";
		$mjesto_rodjenja_2 = $mr['drzava'];
	}
} else {
	$mjesto_rodjenja_1 = "Nepoznato mjesto rođenja"; $mjesto_rodjenja_2 = "";
}

$q30 = db_query("SELECT naziv FROM drzava WHERE id=".$osoba['drzavljanstvo']);
if (db_num_rows($q30) > 0)
	$drzavljanstvo = db_result($q30,0,0);
else
	$drzavljanstvo = "Nepoznato državljanstvo";

$am = false;
if (!empty($osoba['adresa_mjesto'])) {
	$q40 = db_query("SELECT m.naziv, m.opcina, m.opcina_van_bih ovb, d.naziv drzava FROM mjesto as m, drzava as d WHERE m.id=".$osoba['adresa_mjesto']." AND m.drzava=d.id");
	$am = db_fetch_assoc($q40);
}
if ($am) {
	if ($am['drzava'] == "Bosna i Hercegovina") {
		$q30 = db_query("SELECT naziv FROM opcina WHERE id=".$am['opcina']);
		$q40 = db_query("SELECT naziv FROM kanton WHERE id=".$osoba['kanton']);
		$opcina = db_result($q30,0,0);
		if (empty($opcina)) $opcina = "Centar";
		$adresa_mjesto_1 = $am['naziv'] . ", " . $opcina . ", " . db_result($q40,0,0);
		$adresa_mjesto_2 = $osoba['adresa'];
	} else {
		$adresa_mjesto_1 = $am['naziv'] . ", ";
		if (!empty($mr['ovb'])) $adresa_mjesto_1 .= $am['ovb'] . ",";
		else $adresa_mjesto_1 .= "(nije u BiH),";
		$adresa_mjesto_2 = $am['drzava'] . ", " . $osoba['adresa'];
	}
} else {
	$adresa_mjesto_1 = "Nepoznata adresa prebivališta"; $adresa_mjesto_2 = "";
}

$q50 = db_query("SELECT studij, semestar, nacin_studiranja, plan_studija, ponovac FROM student_studij WHERE student=$userid AND akademska_godina=$zagodinu ORDER BY semestar");
$akademska_godina = db_get("SELECT naziv FROM akademska_godina WHERE id=$zagodinu");
if (db_num_rows($q50) == 0) {
	$q50 = db_query("SELECT studij, semestar, nacin_studiranja, plan_studija, ponovac FROM student_studij WHERE student=$userid AND akademska_godina=$proslagodina ORDER BY semestar");
	$akademska_godina = db_get("SELECT naziv FROM akademska_godina WHERE id=$proslagodina");
}
if (db_num_rows($q50) == 0) {
	niceerror("Student trenutno nije upisan na fakultet!");
	return 0;
}
$ss = db_fetch_assoc($q50);
$studij = $ss['studij'];
$sem1 = $ss['semestar'];
$sem2 = $ss['semestar']+1;
$semestar = $imena_semestara[$sem1];
$semestar_dativ = $imena_semestara_dativ[$sem1];
$semestar_parni = $imena_semestara[$sem2];
$semestar_parni_dativ = $imena_semestara_dativ[$sem2];

$plan_studija = $ss['plan_studija'];
if ($plan_studija == 0) {
	// Student nije prethodno studirao na istom studiju ili plan studija nije bio definisan
	// Uzimamo najnoviji plan za odabrani studij
	$plan_studija = db_get("select id from plan_studija where studij=$studij order by godina_vazenja desc limit 1");
	if ($plan_studija === false) { 
		niceerror("Nepostojeći studij");
		return;
	}
}

$q60 = db_query("SELECT s.naziv, ts.ciklus, s.institucija FROM studij as s, tipstudija as ts WHERE s.id=$studij AND s.tipstudija=ts.id");
$studij_podaci = db_fetch_assoc($q60);

$institucija = $studij_podaci['institucija'];
do {
	$q140 = db_query("select tipinstitucije, roditelj, naziv from institucija where id=$institucija");
	if (!($r140 = db_fetch_row($q140))) {
		return;
	}
	if ($r140[0] == 1) {
		$naziv_vsu = $r140[2];
		break;
	}
	$institucija = $r140[1];
} while(true);

if (ends_with($naziv_vsu, "Sarajevo")) {
	$naziv_vsu = substr($naziv_vsu, 0, strlen($naziv_vsu) - strlen("Sarajevo") - 1);
	$naziv_vsu_grad = "Sarajevu";
} else $naziv_vsu_grad = "";


$q70 = db_query("SELECT adresa FROM email WHERE osoba=$userid ORDER BY sistemska DESC");
if (db_num_rows($q70) > 0) $email = db_result($q70,0,0);
else $email = "";


if ($studij_podaci['ciklus'] == 1) {
	$q80 = db_query("SELECT ss.naziv, ag.naziv, ss.domaca FROM srednja_skola as ss, uspjeh_u_srednjoj as uus, akademska_godina as ag WHERE uus.osoba=$userid AND uus.srednja_skola=ss.id AND uus.godina=ag.id");
	$prethodno_obrazovanje = $godina_prethodnog = $drzava_prethodnog = "";
	if (db_num_rows($q80) > 0) {
		$prethodno_obrazovanje = db_result($q80,0,0);
		$godina_prethodnog = db_result($q80,0,1);
		if (db_result($q80,0,2) == 1) 
			$drzava_prethodnog = "Bosna i Hercegovina";
		else
			$drzava_prethodnog = ""; // FIXME nemamo taj podatak
	}
} else {
	// FIXME ovi podaci se nisu do sada unosili!?
	$prethodno_obrazovanje = "Elektrotehnički fakultet Sarajevo";
	$godina_prethodnog = "";
	$drzava_prethodnog = "Bosna i Hercegovina";
}





// ----- Pravljenje PDF dokumenta


$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); 

// set document information
$pdf->SetCreator("Zamger");
if ($upisni)
	$pdf->SetTitle('Upisni list');
else 
	$pdf->SetTitle('List o prijavi semestra');

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

	// NEPARNI SEMESTAR

	// Prednja stranica
	$pdf->SetXY(10, 22);
	$pdf->Cell(100, 0, $naziv_vsu, 0, 0);
	$pdf->SetXY(20, 29);
	$pdf->Cell(100, 0, $naziv_vsu_grad, 0, 0);
	$pdf->SetXY(182, 22);
	$pdf->Cell(30, 0, $osoba['brindexa'], 0, 0);

	$pdf->SetXY(10, 82);
	$pdf->Cell(100, 0, $ime_roditelj_prezime, 0, 0);
	$pdf->SetXY(70, 92);
	$pdf->Cell(30, 0, $semestar, 0, 0);
	$pdf->SetXY(10, 99);
	$pdf->Cell(100, 0, $studij_podaci['naziv'], 0, 0);

	// Zaokružujemo način studija
	$style5 = array('width' => 0.25, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0);
	$pdf->SetLineStyle($style5);
	if ($ss['nacin_studiranja'] == 1) // redovan
		$pdf->Ellipse(140,85,10,3);
	if ($ss['nacin_studiranja'] == 3) // samofin
		$pdf->Ellipse(170,85,22,3);
	if ($ss['nacin_studiranja'] == 4) // vanredan
		$pdf->Ellipse(15,94,10,3);
	if ($ss['nacin_studiranja'] == 5) // dl
		$pdf->Ellipse(28,94,3,3);
	// Način studiranja "gost" ne postoji u zamgeru (treba dodati!)

	// Zaokružujemo ciklus studija
	if ($studij_podaci['ciklus'] == 1)
		$pdf->Ellipse(137,94,8,3);
	if ($studij_podaci['ciklus'] == 2)
		$pdf->Ellipse(151,94,8,3);
	if ($studij_podaci['ciklus'] == 3)
		$pdf->Ellipse(180,94,20,3);

	$pdf->SetXY(30, 136);
	$pdf->Cell(100, 0, $osoba['jmbg'], 0, 0);
	$pdf->SetXY(70, 145);
	$pdf->Cell(100, 0, $datum_rodjenja, 0, 0);
	$pdf->SetXY(80, 154);
	$pdf->Cell(100, 0, $mjesto_rodjenja_1, 0, 0);
	$pdf->SetXY(10, 163);
	$pdf->Cell(100, 0, $mjesto_rodjenja_2, 0, 0);
	$pdf->SetXY(40, 171);
	$pdf->Cell(100, 0, $drzavljanstvo, 0, 0);
	if ($upisni) {
		$pdf->SetXY(80, 181);
		$pdf->Cell(65, 0, $prethodno_obrazovanje . ", " . $godina_prethodnog, 0, 0);
		$pdf->SetXY(50, 189);
		$pdf->Cell(65, 0, $ime_prezime_oca, 0, 0);
		$pdf->SetXY(140, 189);
		$pdf->Cell(65, 0, $ime_prezime_majke, 0, 0);
		//$pdf->SetXY(50, 199);
		//$pdf->Cell(65, 0, $zanimanje_oca, 0, 0); // Nema u Zamgeru!
		//$pdf->SetXY(140, 199);
		//$pdf->Cell(65, 0, $zanimanje_majke, 0, 0); // Nema u Zamgeru!
		$pdf->SetXY(80, 209);
		$pdf->Cell(100, 0, $adresa_mjesto_1, 0, 0);
		$pdf->SetXY(10, 219);
		$pdf->Cell(100, 0, $adresa_mjesto_2, 0, 0);
		$pdf->SetXY(80, 227);
		$pdf->Cell(100, 0, $adresa_mjesto_2, 0, 0);
		$pdf->SetXY(30, 236);
		$pdf->Cell(100, 0, $osoba['telefon'], 0, 0);
		$pdf->SetXY(110, 236);
		$pdf->Cell(100, 0, $email, 0, 0);
	} else {
		$pdf->SetXY(50, 181);
		$pdf->Cell(65, 0, $ime_prezime_oca, 0, 0);
		$pdf->SetXY(140, 181);
		$pdf->Cell(65, 0, $ime_prezime_majke, 0, 0);
		$pdf->SetXY(80, 191);
		$pdf->Cell(100, 0, $adresa_mjesto_1, 0, 0);
		$pdf->SetXY(10, 201);
		$pdf->Cell(100, 0, $adresa_mjesto_2, 0, 0);
		$pdf->SetXY(80, 209);
		$pdf->Cell(100, 0, $adresa_mjesto_2, 0, 0);
		$pdf->SetXY(30, 218);
		$pdf->Cell(100, 0, $osoba['telefon'], 0, 0);
		$pdf->SetXY(110, 218);
		$pdf->Cell(100, 0, $email, 0, 0);
	}

	// Stražnja stranica
	$pdf->AddPage();
	$pdf->SetXY(13, 20);
	$pdf->Cell(100, 0, $naziv_vsu, 0, 0);
	$pdf->SetXY(140, 20);
	$pdf->Cell(65, 0, $semestar, 0, 0);
	$pdf->SetXY(13, 33);
	$pdf->Cell(100, 0, $studij_podaci['naziv'], 0, 0);

	$pdf->SetXY(50, 52);
	$pdf->Cell(100, 0, $ime_prezime, 0, 0);
	$pdf->SetXY(40, 65);
	$pdf->Cell(100, 0, $semestar_dativ, 0, 0);
	$pdf->SetXY(95, 63);
	$pdf->Cell(100, 0, $akademska_godina, 0, 0);


// testiram
//$plan_studija=1;
//$studij=2;

	// Spisak predmeta na semestru
	if ($ponovac==1) 
		$q100 = db_query("SELECT p.sifra, p.naziv, p.ects, p.id from predmet as p, pasos_predmeta pp, plan_studija_predmet as psp where psp.plan_studija=$plan_studija and psp.semestar=$sem1 and psp.obavezan=1 and psp.pasos_predmeta=pp.id AND pp.predmet=p.id and (select count(*) from konacna_ocjena as ko where ko.student=$userid and ko.predmet=p.id)=0");
	else
	// Ako nije, trebamo prikazati one koje je položio u koliziji
		$q100 = db_query("SELECT p.sifra, p.naziv, p.ects, p.id from predmet as p, pasos_predmeta pp, plan_studija_predmet as psp where psp.plan_studija=$plan_studija and psp.semestar=$sem1 and psp.obavezan=1 and psp.pasos_predmeta=pp.id AND pp.predmet=p.id");
	$ykoord = 110;
	$ects = 0;
	while ($r100 = db_fetch_row($q100)) {
		// Tražimo odgovornog nastavnika za predmet
		$q110 = db_query("SELECT osoba FROM angazman WHERE predmet=$r100[3] AND akademska_godina=$proslagodina AND angazman_status=1");
		if (db_num_rows($q110)>0) $nastavnik = tituliraj(db_result($q110,0,0));

		// Prelamanje naziva predmeta
		$naziv_predmeta = $r100[1];
		if (strlen($naziv_predmeta) <= 35) {
			$pdf->SetXY(13, $ykoord);
			$pdf->Cell(100, 0, $r100[1]);
		} else {
			for ($i=35; $i>10; $i++) if ($naziv_predmeta[$i] == " ") break;
			
			$pdf->SetXY(13, $ykoord-2);
			$pdf->Cell(100, 0, substr($naziv_predmeta, 0, $i));
			$pdf->SetXY(13, $ykoord+2);
			$pdf->Cell(100, 0, substr($naziv_predmeta, $i+1));
		}

		$pdf->SetXY(70, $ykoord);
		$pdf->Cell(100, 0, $nastavnik);
		$e = "$r100[2]";
		if (!strchr($e,".")) $e .= ".0";
		$pdf->SetXY(190, $ykoord);
		$pdf->Cell(100, 0, $e);
		$ykoord+=9;
		$ects += $r100[2];
	}


	// PARNI SEMESTAR

	// Prednja stranica
	if (!$upisni) {
	$pdf->AddPage();
	$pdf->SetXY(10, 22);
	$pdf->Cell(100, 0, $naziv_vsu, 0, 0);
	$pdf->SetXY(20, 29);
	$pdf->Cell(100, 0, $naziv_vsu_grad, 0, 0);
	$pdf->SetXY(182, 22);
	$pdf->Cell(30, 0, $osoba['brindexa'], 0, 0);

	$pdf->SetXY(10, 82);
	$pdf->Cell(100, 0, $ime_roditelj_prezime, 0, 0);
	$pdf->SetXY(70, 92);
	$pdf->Cell(30, 0, $semestar_parni, 0, 0);
	$pdf->SetXY(10, 99);
	$pdf->Cell(100, 0, $studij_podaci['naziv'], 0, 0);

	// Zaokružujemo način studija
	$style5 = array('width' => 0.25, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0);
	$pdf->SetLineStyle($style5);
	if ($ss['nacin_studiranja'] == 1) // redovan
		$pdf->Ellipse(140,85,10,3);
	if ($ss['nacin_studiranja'] == 3) // samofin
		$pdf->Ellipse(170,85,22,3);
	if ($ss['nacin_studiranja'] == 4) // vanredan
		$pdf->Ellipse(15,94,10,3);
	if ($ss['nacin_studiranja'] == 5) // dl
		$pdf->Ellipse(28,94,3,3);
	// Način studiranja "gost" ne postoji u zamgeru (treba dodati!)

	// Zaokružujemo ciklus studija
	if ($studij_podaci['ciklus'] == 1)
		$pdf->Ellipse(137,94,8,3);
	if ($studij_podaci['ciklus'] == 2)
		$pdf->Ellipse(151,94,8,3);
	if ($studij_podaci['ciklus'] == 3)
		$pdf->Ellipse(180,94,20,3);

	$pdf->SetXY(30, 136);
	$pdf->Cell(100, 0, $osoba['jmbg'], 0, 0);
	$pdf->SetXY(70, 145);
	$pdf->Cell(100, 0, $datum_rodjenja, 0, 0);
	$pdf->SetXY(80, 154);
	$pdf->Cell(100, 0, $mjesto_rodjenja_1, 0, 0);
	$pdf->SetXY(10, 163);
	$pdf->Cell(100, 0, $mjesto_rodjenja_2, 0, 0);
	$pdf->SetXY(40, 171);
	$pdf->Cell(100, 0, $drzavljanstvo, 0, 0);
	$pdf->SetXY(50, 181);
	$pdf->Cell(65, 0, $ime_prezime_oca, 0, 0);
	$pdf->SetXY(140, 181);
	$pdf->Cell(65, 0, $ime_prezime_majke, 0, 0);
	$pdf->SetXY(85, 191);
	$pdf->Cell(100, 0, $adresa_mjesto_1, 0, 0);
	$pdf->SetXY(10, 201);
	$pdf->Cell(100, 0, $adresa_mjesto_2, 0, 0);
	$pdf->SetXY(80, 209);
	$pdf->Cell(100, 0, $adresa_mjesto_2, 0, 0);
	$pdf->SetXY(30, 218);
	$pdf->Cell(100, 0, $osoba['telefon'], 0, 0);
	$pdf->SetXY(110, 218);
	$pdf->Cell(100, 0, $email, 0, 0);


	// Stražnja stranica
	$pdf->AddPage();
	$pdf->SetXY(13, 20);
	$pdf->Cell(100, 0, $naziv_vsu, 0, 0);
	$pdf->SetXY(140, 20);
	$pdf->Cell(65, 0, $semestar_parni, 0, 0);
	$pdf->SetXY(13, 33);
	$pdf->Cell(100, 0, $studij_podaci['naziv'], 0, 0);

	$pdf->SetXY(50, 52);
	$pdf->Cell(100, 0, $ime_prezime, 0, 0);
	$pdf->SetXY(40, 65);
	$pdf->Cell(100, 0, $semestar_parni_dativ, 0, 0);
	$pdf->SetXY(95, 63);
	$pdf->Cell(100, 0, $akademska_godina, 0, 0);


// testiram
//$plan_studija=1;
//$studij=2;

	// Spisak predmeta na semestru
	if ($ponovac==1) 
		$q100 = db_query("SELECT p.sifra, p.naziv, p.ects, p.id from predmet as p, pasos_predmeta pp, plan_studija_predmet as psp where psp.plan_studija=$plan_studija and psp.semestar=$sem2 and psp.obavezan=1 and psp.pasos_predmeta=pp.id AND pp.predmet=p.idd and (select count(*) from konacna_ocjena as ko where ko.student=$userid and ko.predmet=p.id)=0");
	else
	// Ako nije, trebamo prikazati one koje je položio u koliziji
		$q100 = db_query("SELECT p.sifra, p.naziv, p.ects, p.id from predmet as p, pasos_predmeta pp, plan_studija_predmet as psp where psp.plan_studija=$plan_studija and psp.semestar=$sem2 and psp.obavezan=1 and psp.pasos_predmeta=pp.id AND pp.predmet=p.id");
	$ykoord = 110;
	$ects = 0;
	while ($r100 = db_fetch_row($q100)) {
		// Tražimo odgovornog nastavnika za predmet
		$q110 = db_query("SELECT osoba FROM angazman WHERE predmet=$r100[3] AND akademska_godina=$proslagodina AND angazman_status=1");
		if (db_num_rows($q110)>0) $nastavnik = tituliraj(db_result($q110,0,0));

		// Prelamanje naziva predmeta
		$naziv_predmeta = $r100[1];
		if (strlen($naziv_predmeta) <= 35) {
			$pdf->SetXY(13, $ykoord);
			$pdf->Cell(100, 0, $r100[1]);
		} else {
			for ($i=35; $i>10; $i++) if ($naziv_predmeta[$i] == " ") break;
			
			$pdf->SetXY(13, $ykoord-2);
			$pdf->Cell(100, 0, substr($naziv_predmeta, 0, $i));
			$pdf->SetXY(13, $ykoord+2);
			$pdf->Cell(100, 0, substr($naziv_predmeta, $i+1));
		}

		$pdf->SetXY(70, $ykoord);
		$pdf->Cell(100, 0, $nastavnik);
		$e = "$r100[2]";
		if (!strchr($e,".")) $e .= ".0";
		$pdf->SetXY(190, $ykoord);
		$pdf->Cell(100, 0, $e);
		$ykoord+=9;
		$ects += $r100[2];
	}

	}

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output('semestralni_list.pdf', 'I');

//============================================================+
// END OF FILE                                                 
//============================================================+




}

?>
