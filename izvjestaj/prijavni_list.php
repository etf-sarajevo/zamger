<?

// IZVJESTAJ/PRIJAVNI_LIST - popunjavanje prijavnog lista (obrazac ŠV-20)



function izvjestaj_prijavni_list() {

global $userid;

require_once('lib/tcpdf/tcpdf.php');

$imena_semestara = array("", "prvi", "drugi", "treći", "četvrti", "peti", "šesti");
$imena_semestara_dativ = array("", "prvom", "drugom", "trećem", "četvrtom", "petom", "šestom");


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
$datum_rodjenja_dan = date("d", $osoba['ut_rodjenje']);
$datum_rodjenja_mjesec = date("m", $osoba['ut_rodjenje']);
$datum_rodjenja_godina = date("Y", $osoba['ut_rodjenje']);

$osoba_kanton = db_get("SELECT naziv FROM kanton WHERE id=".$osoba['kanton']);

$mr = false;
if (!empty($osoba['mjesto_rodjenja'])) {
	$q20 = db_query("SELECT m.naziv naziv, m.opcina opcina, m.opcina_van_bih ovb, d.naziv drzava FROM mjesto as m, drzava as d WHERE m.id=".$osoba['mjesto_rodjenja']." AND m.drzava=d.id");
	$mr = db_fetch_assoc($q20);
}
if ($mr) {
	if ($mr['drzava'] == "Bosna i Hercegovina") {
		$opcina = db_get("SELECT naziv FROM opcina WHERE id=".$mr['opcina']);
		if (empty($opcina)) $opcina = "Centar";
		$mjesto_rodjenja_1 = $mr['naziv'] . ", " . $opcina . ",";
		$mjesto_rodjenja_2 = $osoba_kanton . ", Bosna i Hercegovina";
		$mjesto_rodjenja_sv = $mr['naziv'] . ", " . $opcina;
	} else {
		$mjesto_rodjenja_1 = $mr['naziv'] . ", ";
		if (!empty($mr['ovb'])) $mjesto_rodjenja_1 .= $mr['ovb'] . ",";
		else $mjesto_rodjenja_1 .= "(nije u BiH),";
		$mjesto_rodjenja_2 = $mr['drzava'];
		$mjesto_rodjenja_sv = $am['drzava'];
	}
} else {
	$mjesto_rodjenja_1 = "Nepoznato mjesto rođenja"; $mjesto_rodjenja_2 = "";
}

$drzavljanstvo = db_get("SELECT naziv FROM drzava WHERE id=".$osoba['drzavljanstvo']);
if ($drzavljanstvo === false)
	$drzavljanstvo = "";

$nacionalnost = db_get("SELECT naziv FROM nacionalnost WHERE id=".$osoba['nacionalnost']);
if ($nacionalnost === false)
	$nacionalnost = "";

$am = false;
if (!empty($osoba['adresa_mjesto'])) {
	$am = db_query_assoc("SELECT m.naziv, m.opcina, m.opcina_van_bih ovb, d.naziv drzava FROM mjesto as m, drzava as d WHERE m.id=".$osoba['adresa_mjesto']." AND m.drzava=d.id");
}
if ($am) {
	if ($am['drzava'] == "Bosna i Hercegovina") {
		$opcina = db_get("SELECT naziv FROM opcina WHERE id=".$am['opcina']);
		$kanton = db_get("SELECT naziv FROM kanton WHERE id=".$osoba['kanton']);
		if (empty($opcina)) $opcina = "Centar";
		$adresa_mjesto_1 = $am['naziv'] . ", " . $opcina . ", " . $kanton;
		$adresa_mjesto_2 = $osoba['adresa'];
		if ($am['opcina'] < 80) // FIXME
			$entitet = "FBiH";
		else if ($am['opcina'] < 142)
			$entitet = "Brčko distrikt";
		else
			$entitet = "RS";
	} else {
		if (!empty($mr['ovb']))
			$opcina = $mr['ovb'];
		else
			$opcina = "(nije u BiH)";

		$adresa_mjesto_1 = $am['naziv'] . ", " . $opcina . ",";
		$adresa_mjesto_2 = $am['drzava'] . ", " . $osoba['adresa'];
		$entitet = "";
	}
	$adresa_mjesto_sv = $am['naziv'] . ", " . $osoba['adresa'];
	$adresa_mjesto_sv2 = $opcina . ", " . $adresa_mjesto_sv; // FIXME treba adresa boravišta
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
$godina_studija = $sem2/2;
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

if ($ss['ponovac'] == 0 && $sem1 == 1)
	$godina_prvog_upisa = $akademska_godina;
else {
	$godina_prvog_upisa = db_get("SELECT ag.naziv FROM akademska_godina ag, student_studij ss WHERE ss.student=$userid AND ss.studij=$studij AND ss.akademska_godina=ag.id ORDER BY ag.id LIMIT 1");
}

$studij_podaci = db_query_assoc("SELECT s.naziv, ts.ciklus, s.institucija FROM studij as s, tipstudija as ts WHERE s.id=$studij AND s.tipstudija=ts.id");

$institucija = $studij_podaci['institucija'];
do {
	$q140 = db_query("select tipinstitucije, roditelj, naziv from institucija where id=$institucija");
	if (!($r140 = db_fetch_row($q140))) {
		return;
	}
	if ($r140[0] == 1) {
		$naziv_oj = $r140[2];
		break;
	} else
		$naziv_odsjeka = $r140[2];
	$institucija = $r140[1];
} while(true);

if (ends_with($naziv_oj, "Sarajevo")) {
	$naziv_oj = substr($naziv_oj, 0, strlen($naziv_oj) - strlen("Sarajevo") - 1);
	$naziv_oj_grad = "Sarajevu";
} else $naziv_oj_grad = "";

$naziv_vsu = "Univerzitet u Sarajevu";
$kanton_vsu = "Sarajevski kanton";
$opcina_oj = "Novo Sarajevo";
$adresa_oj = "Zmaja od Bosne bb, Sarajevo";
$telefon_oj = "033/250-700";

$email = db_get("SELECT adresa FROM email WHERE osoba=$userid ORDER BY sistemska DESC");
if ($email === false) 
	$email = "";

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
$pdf->SetTitle('Prijavni list');

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
$akademska_godina = "2014/2015"; // FIXME
	// NEPARNI SEMESTAR

	// Prednja stranica
	$pdf->SetXY(118, 36);
	$pdf->Cell(100, 0, $akademska_godina, 0, 0);

	$pdf->SetXY(70, 68);
	$pdf->Cell(100, 0, $naziv_vsu, 0, 0);
	$pdf->SetXY(70, 80);
	$pdf->Cell(100, 0, $naziv_oj, 0, 0);
	$pdf->SetXY(70, 93);
	$pdf->Cell(100, 0, $naziv_odsjeka, 0, 0);
	$pdf->SetXY(70, 102);
	$pdf->Cell(100, 0, $kanton_vsu, 0, 0);
	$pdf->SetXY(70, 108);
	$pdf->Cell(100, 0, $opcina_oj, 0, 0);
	$pdf->SetXY(70, 114);
	$pdf->Cell(100, 0, $adresa_oj, 0, 0);
	$pdf->SetXY(70, 119);
	$pdf->Cell(100, 0, $telefon_oj, 0, 0);

	$pdf->SetXY(110, 130);
	$pdf->Cell(100, 0, $osoba['jmbg'], 0, 0);
	$pdf->SetXY(70, 136);
	$pdf->Cell(100, 0, $ime_prezime, 0, 0);
	$pdf->SetXY(110, 146);
	$pdf->Cell(100, 0, $mjesto_rodjenja_sv, 0, 0);
	$pdf->SetXY(91, 152);
	$pdf->Cell(100, 0, $datum_rodjenja_dan, 0, 0);
	$pdf->SetXY(110, 152);
	$pdf->Cell(100, 0, $datum_rodjenja_mjesec, 0, 0);
	$pdf->SetXY(128, 152);
	$pdf->Cell(100, 0, $datum_rodjenja_godina, 0, 0);
	if ($drzavljanstvo != "Bosna i Hercegovina") {
		$pdf->SetXY(138, 167);
		$pdf->Cell(100, 0, $drzavljanstvo, 0, 0);
		$pdf->SetXY(138, 173);
		$pdf->Cell(100, 0, $nacionalnost, 0, 0);
	}

	// Zaokruživanja
	$style5 = array('width' => 0.25, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0);
	$pdf->SetLineStyle($style5);
	// Spol
	if ($osoba['spol'] == "M")
		$pdf->Ellipse(87,143,3,3);
	if ($osoba['spol'] == "Z")
		$pdf->Ellipse(114,143,3,3);
	// Državljanstvo
	if ($drzavljanstvo == "Bosna i Hercegovina")
		$pdf->Ellipse(81,160,3,3);

	$pdf->SetXY(90, 175);
	$pdf->Cell(100, 0, $am['drzava'], 0, 0);
	$pdf->SetXY(90, 181);
	$pdf->Cell(100, 0, $entitet, 0, 0);
	$pdf->SetXY(90, 187);
	$pdf->Cell(100, 0, $osoba_kanton, 0, 0);
	$pdf->SetXY(90, 193);
	$pdf->Cell(100, 0, $opcina, 0, 0);
	$pdf->SetXY(90, 199);
	$pdf->Cell(100, 0, $adresa_mjesto_sv, 0, 0);
	$pdf->SetXY(100, 214);
	$pdf->Cell(100, 0, $adresa_mjesto_sv2, 0, 0);
	$pdf->SetXY(75, 219);
	$pdf->Cell(100, 0, $osoba['telefon'], 0, 0);
	$pdf->SetXY(110, 219);
	$pdf->Cell(100, 0, $email, 0, 0);
	$pdf->SetXY(100, 228);
	$pdf->Cell(100, 0, $prethodno_obrazovanje, 0, 0);
	$pdf->SetXY(100, 240);
	$pdf->Cell(100, 0, $godina_prethodnog, 0, 0);
	$pdf->SetXY(100, 250);
	$pdf->Cell(100, 0, $drzava_prethodnog, 0, 0);


	// Stražnja stranica
	$pdf->AddPage();
	$pdf->SetXY(130, 77);
	$pdf->Cell(100, 0, $godina_prvog_upisa, 0, 0);
	$pdf->SetXY(20, 250);
	$pdf->Cell(100, 0, $ime_prezime, 0, 0);
	$pdf->SetXY(30, 258);
	$pdf->Cell(100, 0, $osoba['telefon'], 0, 0);
	$pdf->SetXY(30, 266);
	$pdf->Cell(100, 0, $email, 0, 0);

	// Zaokruživanja

	// Ciklus
	$style5 = array('width' => 0.25, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0);
	$pdf->SetLineStyle($style5);
	if ($studij_podaci['ciklus'] == 1)
		$pdf->Ellipse(27,50,3,3);
	if ($studij_podaci['ciklus'] == 2)
		$pdf->Ellipse(55,50,3,3);
	//if ($studij_podaci['ciklus'] == 3) // Zamger ne podržava integrirani I+II ciklus
	//	$pdf->Ellipse(90,50,3,3);
	if ($studij_podaci['ciklus'] == 3)
		$pdf->Ellipse(128,50,3,3);
	
	// Godina studija
	if ($godina_studija == 1)
		$poz = 99;
	else
		$poz = 90.5 + $godina_studija*6.5;
	$pdf->Ellipse($poz,57,3,3);
	/*for ($i=98; $i<140; $i+=6.5) {
		if ($i==98)
			$pdf->Ellipse(100,57,3,3);
		else $pdf->Ellipse($i,57,3,3);
	}*/

	// Ponovac?
	if ($ss['ponovac'] == 1)
		$pdf->Ellipse(126,62,3,3); // Ponovac = 1 - Da
	else
		$pdf->Ellipse(140,62,3,3); // Nije ponovac = 2 - Ne

	// Način studiranja
	if ($ss['nacin_studiranja'] == 1) // redovan
		$pdf->Ellipse(95,68,3,3);
	if ($ss['nacin_studiranja'] == 3) // samofin
		$pdf->Ellipse(124,68,3,3);
	if ($ss['nacin_studiranja'] == 4) // vanredan
		$pdf->Ellipse(95,72,3,3);
	if ($ss['nacin_studiranja'] == 5) // dl
		$pdf->Ellipse(124,72,3,3);

	// Izvor finansiranja - Nije implementirano u Zamgeru!
	// Stipendija - Nije implementirano u Zamgeru!
	// Zaposlenje roditelja/studenta - Nije implementirano u Zamgeru!


// testiram
//$plan_studija=1;
//$studij=2;
/*

	// PARNI SEMESTAR

	// Prednja stranica
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

	// Spisak predmeta na sermestru
	if ($ponovac==1) 
		$q100 = db_query("select p.sifra, p.naziv, p.ects, p.id from predmet as p, plan_studija as ps where ps.godina_vazenja=$plan_studija and ps.studij=$studij and ps.semestar=$sem2 and ps.obavezan=1 and ps.predmet=p.id and (select count(*) from konacna_ocjena as ko where ko.student=$userid and ko.predmet=p.id)=0");
	else
	// Ako nije, trebamo prikazati one koje je položio u koliziji
		$q100 = db_query("select p.sifra, p.naziv, p.ects, p.id from predmet as p, plan_studija as ps where ps.godina_vazenja=$plan_studija and ps.studij=$studij and ps.semestar=$sem2 and ps.obavezan=1 and ps.predmet=p.id");
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


/*

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
		$q100 = db_query("select p.sifra, p.naziv, p.ects from predmet as p, plan_studija as ps where ps.godina_vazenja=$plan_studija and ps.studij=$studij and ps.semestar=$sem1 and ps.obavezan=1 and ps.predmet=p.id and (select count(*) from konacna_ocjena as ko where ko.student=$userid and ko.predmet=p.id)=0");
	else
	// Ako nije, trebamo prikazati one koje je položio u koliziji
		$q100 = db_query("select p.sifra, p.naziv, p.ects from predmet as p, plan_studija as ps where ps.godina_vazenja=$plan_studija and ps.studij=$studij and ps.semestar=$sem1 and ps.obavezan=1 and ps.predmet=p.id");

	$ykoord = 95;
	$ects = 0;
	while ($r100 = db_fetch_row($q100)) {
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
		$q110 = db_query("select p.sifra, p.naziv, p.ects from predmet as p, ugovoroucenju_izborni as uoui, ugovoroucenju as uu where uoui.ugovoroucenju=uu.id and uu.student=$userid and uu.akademska_godina=$zagodinu and uoui.predmet=p.id and uu.semestar=$sem1 and (select count(*) from konacna_ocjena as ko where ko.student=$userid and ko.predmet=p.id)=0");
	else
		$q110 = db_query("select p.sifra, p.naziv, p.ects from predmet as p, ugovoroucenju_izborni as uoui, ugovoroucenju as uu where uoui.ugovoroucenju=uu.id and uu.student=$userid and uu.akademska_godina=$zagodinu and uoui.predmet=p.id and uu.semestar=$sem1");

	$ykoord = 123;
	while ($r110 = db_fetch_row($q110)) {
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
		$q100 = db_query("select p.sifra, p.naziv, p.ects from predmet as p, plan_studija as ps where ps.godina_vazenja=$plan_studija and ps.studij=$studij and ps.semestar=$sem2 and ps.obavezan=1 and ps.predmet=p.id and (select count(*) from konacna_ocjena as ko where ko.student=$userid and ko.predmet=p.id)=0");
	else
		$q100 = db_query("select p.sifra, p.naziv, p.ects from predmet as p, plan_studija as ps where ps.godina_vazenja=$plan_studija and ps.studij=$studij and ps.semestar=$sem2 and ps.obavezan=1 and ps.predmet=p.id");
	$ykoord = 95;
	$ects = 0;
	while ($r100 = db_fetch_row($q100)) {
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
		$q110 = db_query("select p.sifra, p.naziv, p.ects from predmet as p, ugovoroucenju_izborni as uoui, ugovoroucenju as uu where uoui.ugovoroucenju=uu.id and uu.student=$userid and uu.akademska_godina=$zagodinu and uoui.predmet=p.id and uu.semestar=$sem2 and (select count(*) from konacna_ocjena as ko where ko.student=$userid and ko.predmet=p.id)=0");
	else
		$q110 = db_query("select p.sifra, p.naziv, p.ects from predmet as p, ugovoroucenju_izborni as uoui, ugovoroucenju as uu where uoui.ugovoroucenju=uu.id and uu.student=$userid and uu.akademska_godina=$zagodinu and uoui.predmet=p.id and uu.semestar=$sem2");
	$ykoord = 123;
	while ($r110 = db_fetch_row($q110)) {
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
*/
// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output('prijavni_list.pdf', 'I');

//============================================================+
// END OF FILE                                                 
//============================================================+




}

?>
