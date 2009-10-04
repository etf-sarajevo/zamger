<?

// STUDENTSKA/OSOBE - administracija studenata, studentska služba

// v3.9.1.0 (2008/02/19) + Preimenovan bivsi admin_nihada
// v3.9.1.1 (2008/03/21) + Nova auth tabela, popravka upisa na predmet, pojednostavljenje i čišćenje koda
// v3.9.1.2 (2008/04/23) + Trimovanje whitespace-a kod pretrage
// v3.9.1.3 (2008/08/27) + Pretvaram studentska/studenti u studentska/osobe; izbjegnut XSS u linku 'nazad na rezultate pretrage'
// v3.9.1.4 (2008/09/03) + Napravljena akcija 'upis', dodani linkovi na sve vrste upisa u sljedeci semestar; dodano polje aktivan u tabeli auth
// v3.9.1.5 (2008/09/05) + Ispravke bugova; prikazi podatke i za godinu u koju pokusavas upisati studenta
// v3.9.1.6 (2008/09/13) + Upisi studenta u predmete koje je prenio prilikom upisa novog semestra; 
// v3.9.1.7 (2008/09/17) + Dodan debugging ispis; dodaj nastavnike u auth tabelu prilikom kreiranja iz LDAPa; omogucen upis studenta u aktuelnu akademsku godinu ako postoje podaci iz ranijih godina
// v3.9.1.8 (2008/09/19) + Nemoj upisivati studenta u predmete koje je vec polozio
// v3.9.1.9 (2008/10/02) + Ozivljavam dio koda za direktan upis studenta na predmet, radi "kolizije"
// v3.9.1.10 (2008/10/03) + Pretraga prebacena na GET radi lakseg vracanja na back; za sve vrste izmjena poostren uslov na POST
// v3.9.1.11 (2008/10/08) + Dodana mogucnost siteadminu da promijeni tip korisnika; popravljen logging na par mjesta; pretraga pokusava naci EXACT match, provjerava i login; prikaz vise logina za istu osobu (ako postoje); ne moze se upisati student na predmet 0
// v3.9.1.12 (2008/10/16) + Popravljen bug kod spiska predmeta u koje se moze direktno upisati
// v3.9.1.13 (2008/10/31) + Ukinut autocomplete kod unosa logina i sifre; ukinuta redirekcija kod dodavanja novog korisnika, zbog toga sto je $_REQUEST niz zagadjen podacima; dodana mogucnost upisa na prvu godinu za studente koji nikad nista nisu slusali; dodani tagovi u logging kod upisa
// v3.9.1.14 (2008/12/23) + Checkbox za korisnicki pristup kod LDAPa prebacen na POST radi zastite od CSRF (bug 59)
// v3.9.1.15 (2009/02/01) + Popravljena dva linka na osobu
// v3.9.1.16 (2009/02/10) + Dodan prikaz ECTS bodova na izbornim predmetima i kontrola sume ECTSa prilikom upisa na semestar
// v4.0.0.0 (2009/02/19) + Release
// v4.0.0.1 (2009/02/24) + Kod direktnog upisa na predmet, sakrij predmete sa drugih studija (osim prve godine) i predmete koje je student vec polozio
// v4.0.0.2 (2009/03/19) + Sakrij direktni upis na predmet ako student nije upisan na fakultet!
// v4.0.9.1 (2009/03/19) + Novi izvjestaj "Historija"
// v4.0.9.2 (2009/03/24) + Prebacena polja ects i tippredmeta iz tabele ponudakursa u tabelu predmet
// v4.0.9.3 (2009/03/25) + nastavnik_predmet preusmjeren sa tabele ponudakursa na tabelu predmet -- FIXME provjeriti mogucnosti optimizacije
// v4.0.9.4 (2009/03/31) + Tabela konacna_ocjena preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.5 (2009/04/19) + U r335 nije ustvari napravljena izmjena koja pise u komentaru; subakcija "angazuj" nije definisala polje akademska_godina koje je u tabelu nastavnik_predmet dodano u r365; upit za aktuelnu ak. godinu pomjeren naprijed
// v4.0.9.6 (2009/04/27) + Popravljen typo u dijelu za promjenu privilegija; typo u upitu r566
// v4.0.9.7 (2009/05/06) + Koristim funkciju upis_studenta_na_predmet radi upisa na virtualnu labgrupu
// v4.0.9.8 (2009/06/16) + Popravljen link na studentska/predmeti
// v4.0.9.9 (2009/06/19) + Tabela osoba: ukinuto polje srednja_skola (to ce biti rijeseno na drugi nacin); polje mjesto_rodjenja prebaceno na sifrarnik; dodano polje adresa_mjesto kao FK na isti sifrarnik
// v4.0.9.10 (2009/08/28) + Dodajem podrsku za ugovor o ucenju, popravljena provjera uslova za upis na osnovu plana studija, ukinut hardkodirani studij "Prva godina studija" iz upisa i provjere uslova
// v4.0.9.11 (2009/09/12) + Dodajem podrsku za koliziju; oba dijela se prikazuju samo ako su moduli aktivni
// v4.0.9.12 (2009/09/15) + Redizajniran kod za akciju upis, uz podrsku za plan studija i nova polja u tabelama studij i tipstudija; u akciji "edit", sekcija "Prijemni", daj link za upis samo ako je student polagao prijemni za godinu iza aktuelne; popravljen prikaz upisa u sljedecu godinu za studente upravo upisane na prijemnom
// v4.0.9.13 (2009/09/23) + Manuelni upis na predmete / ispis sa predmeta napravljen kao zasebna akcija
// v4.0.9.14 (2009/10/03) + Dodajem kod za ispis studenta sa studija (prethodno u modulu prodsjeka)



function studentska_osobe() {

global $userid,$user_siteadmin,$user_studentska;
global $conf_system_auth,$conf_ldap_server,$conf_ldap_domain;
global $registry; // šta je od modula aktivno

global $_lv_; // Potrebno za genform() iz libvedran

require ("lib/manip.php"); // Radi upisa studenta na predmet


// Provjera privilegija
if (!$user_siteadmin && !$user_studentska) { // 2 = studentska, 3 = admin
	zamgerlog("korisnik nije studentska (admin $admin)",3);
	biguglyerror("Pristup nije dozvoljen.");
	return;
}



?>

<center>
<table border="0"><tr><td>

<?

$akcija = $_REQUEST['akcija'];
$osoba = intval($_REQUEST['osoba']);



// Dodavanje novog korisnika u bazu

if ($_POST['akcija'] == "novi" && check_csrf_token()) {

	$ime = substr(my_escape($_POST['ime']), 0, 100);
	if (!preg_match("/\w/", $ime)) {
		zamgerlog("ime nije ispravno ($ime)",3);
		niceerror("Ime nije ispravno");
		return;
	}

	$prezime = substr(my_escape($_POST['prezime']), 0, 100);

	// Probamo tretirati ime kao LDAP UID
	if ($conf_system_auth == "ldap") {
		$uid = $ime;
		$ds = ldap_connect($conf_ldap_server);
		ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
		if ($ds && ldap_bind($ds)) {
			$sr = ldap_search($ds, "", "uid=$uid", array("givenname","sn") );
			$results = ldap_get_entries($ds, $sr);
			if ($results['count'] > 0) {
				$gn = $results[0]['givenname'];
				if (is_array($gn)) $gn = $results[0]['givenname'][0];
				if ($gn) $ime = $gn;

				$sn = $results[0]['sn'];
				if (is_array($sn)) $sn = $results[0]['sn'][0];
				if ($sn) $prezime = $sn;
			} else {
				zamgerlog("korisnik '$uid' nije pronadjen na LDAPu",3);
				$uid = "";
				niceerror("Korisnik nije pronadjen na LDAPu... dodajem novog!");
			}
		} else {
			zamgerlog("ne mogu kontaktirati LDAP server",3);
			niceerror("Ne mogu kontaktirati LDAP server... pravim se da ga nema :(");
		}
	}

	if (!preg_match("/\w/", $prezime)) {
		zamgerlog("prezime nije ispravno ($prezime)",3);
		niceerror("Prezime nije ispravno");
		return;
	}

	// Da li ovaj korisnik već postoji u osoba tabeli?
	$q10 = myquery("select id, ime, prezime from osoba where ime like '$ime' and prezime like '$prezime'");
	if ($r10 = mysql_fetch_row($q10)) {
		zamgerlog("korisnik vec postoji u bazi ('$ime' '$prezime' - ID: $r10[0])",3);
		niceerror("Korisnik već postoji u bazi:");
		print "<br><a href=\"?sta=studentska/osobe&akcija=edit&osoba=$r10[0]\">$r10[1] $r10[2]</a>";
		return;

	} else {
		// Nije u tabeli, dodajemo ga...
		$q30 = myquery("select id from osoba order by id desc limit 1");
		$osoba = mysql_result($q30,0,0)+1;

		$upit = "insert into osoba set id=$osoba, ime='$ime', prezime='$prezime'";

		if ($conf_system_auth == "ldap" && $uid != "") {
			// Ako je LDAP onda imamo email adresu
			$upit = $upit.", email='".$uid.$conf_ldap_domain."'";
			// a ako je student, imamo i brindexa
			if (preg_match("/\w\w(\d\d\d\d\d)/", $uid, $matches))
				$upit = $upit.", brindexa='".$matches[1]."'";

			// Mozemo ga dodati i u auth tabelu
			$q35 = myquery("select count(*) from auth where id=$osoba");
			if (mysql_result($q35,0,0)==0) {
				$q37 = myquery("insert into auth set id=$osoba, login='$uid', admin=1, aktivan=1");
			}
		}

		$q40 = myquery($upit);

		nicemessage("Novi korisnik je dodan.");
		zamgerlog("dodan novi korisnik u$osoba (ID: $osoba)",4); // nivo 4: audit
		print "<br><a href=\"?sta=studentska/osobe&akcija=edit&osoba=$osoba\">$ime $prezime</a>";
		return;
	}
}



// Izmjena licnih podataka osobe

if ($akcija == "podaci") {

	if ($_POST['subakcija']=="potvrda" && check_csrf_token()) {

		$ime = my_escape($_REQUEST['ime']);
		$prezime = my_escape($_REQUEST['prezime']);
		$email = my_escape($_REQUEST['email']);
		$brindexa = my_escape($_REQUEST['brindexa']);
		$mjesto_rodjenja = my_escape($_REQUEST['mjesto_rodjenja']);
		$jmbg = my_escape($_REQUEST['jmbg']);
		$drzavljanstvo = my_escape($_REQUEST['drzavljanstvo']);
		$adresa = my_escape($_REQUEST['adresa']);
		$adresa_mjesto = my_escape($_REQUEST['adresa_mjesto']);
		$telefon = my_escape($_REQUEST['telefon']);
		$kanton = intval($_REQUEST['_lv_column_kanton']);

		// Sredjujem datum
		if (preg_match("/(\d+).*?(\d+).*?(\d+)/", $_REQUEST['datum_rodjenja'], $matches)) {
			$dan=$matches[1]; $mjesec=$matches[2]; $godina=$matches[3];
			if ($godina<100)
				if ($godina<50) $godina+=2000; else $godina+=1900;
			if ($godina<1000)
				if ($godina<900) $godina+=2000; else $godina+=1000;
		}

		// Mjesto
		$mjrid=0;
		if ($mjesto_rodjenja != "") {
			$q1 = myquery("select id from mjesto where naziv='$mjesto_rodjenja'");
			if (mysql_num_rows($q1)<1) {
				$q2 = myquery("insert into mjesto set naziv='$mjesto_rodjenja'");
				$q1 = myquery("select id from mjesto where naziv='$mjesto_rodjenja'");
			}
			$mjrid = mysql_result($q1,0,0);
		}
	
		$admid=0;
		if ($adresa_mjesto != "") {
			$q3 = myquery("select id from mjesto where naziv='$adresa_mjesto'");
			if (mysql_num_rows($q3)<1) {
				$q4 = myquery("insert into mjesto set naziv='$adresa_mjesto'");
				$q3 = myquery("select id from mjesto where naziv='$adresa_mjesto'");
			}
			$admid = mysql_result($q3,0,0);
		}


		$q395 = myquery("update osoba set ime='$ime', prezime='$prezime', email='$email', brindexa='$brindexa', datum_rodjenja='$godina-$mjesec-$dan', mjesto_rodjenja=$mjrid, jmbg='$jmbg', drzavljanstvo='$drzavljanstvo', adresa='$adresa', adresa_mjesto=$admid, telefon='$telefon', kanton='$kanton' where id=$osoba");

		zamgerlog("promijenjeni licni podaci korisnika u$osoba",4); // nivo 4 - audit
		?>
		<script language="JavaScript">
		location.href='?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=edit';
		</script>
		<?
		return;
	}

	$q400 = myquery("select ime, prezime, email, brindexa, UNIX_TIMESTAMP(datum_rodjenja), mjesto_rodjenja, jmbg, drzavljanstvo, adresa, adresa_mjesto, telefon, kanton from osoba where id=$osoba");
	if (!($r400 = mysql_fetch_row($q400))) {
		zamgerlog("nepostojeca osoba u$osoba",3);
		niceerror("Nepostojeća osoba!");
		return;
	}
	$ime = mysql_result($q400,0,0);
	$prezime = mysql_result($q400,0,1);

	// Spisak gradova
	$q410 = myquery("select id,naziv from mjesto order by naziv");
	$gradovir="<option></option>";
	$gradovia="<option></option>";
	while ($r410 = mysql_fetch_row($q410)) { 
		$gradovir .= "<option"; $gradovia .= "<option";
		if ($r410[0]==mysql_result($q400,0,5)) { $gradovir  .= " SELECTED"; }
		if ($r410[0]==mysql_result($q400,0,9)) { $gradovia  .= " SELECTED"; }
		$gradovir .= ">$r410[1]</option>\n";
		$gradovia .= ">$r410[1]</option>\n";
	}

	?>

	<script type="text/javascript" src="js/combo-box.js"></script>
	<h2><?=$ime?> <?=$prezime?> - izmjena ličnih podataka</h2>
	<?=genform("POST")?>
	<input type="hidden" name="subakcija" value="potvrda">
	<table border="0" width="600"><tr><td valign="top">
		Ime: <input type="text" name="ime" value="<?=$ime?>" class="default"><br/>
		Prezime: <input type="text" name="prezime" value="<?=$prezime?>" class="default"><br/>
		Broj indexa (za studente): <input type="text" name="brindexa" value="<?=mysql_result($q400,0,3)?>" class="default"><br/>
		JMBG: <input type="text" name="jmbg" value="<?=mysql_result($q400,0,6)?>" class="default"><br/>
		<br/>
		Datum rođenja: <input type="text" name="datum_rodjenja" value="<?
		if (mysql_result($q400,0,4)) print date("d. m. Y.", mysql_result($q400,0,4))?>" class="default"><br/>
		Mjesto rođenja: <select name="mjesto_rodjenja" onKeyPress="edit(event)" onBlur="this.editing = false;" class="default"><?=$gradovir?></select><br/>
		Državljanstvo: <input type="text" name="drzavljanstvo" value="<?=mysql_result($q400,0,7)?>" class="default"><br/>
		</td><td valign="top">
		Adresa: <input type="text" name="adresa" value="<?=mysql_result($q400,0,8)?>" class="default"><br/>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<select name="adresa_mjesto" onKeyPress="edit(event)" onBlur="this.editing = false;" class="default"><?=$gradovia?></select><br />
		Kanton: <?=db_dropdown("kanton",mysql_result($q400,0,11), "--Izaberite kanton--") ?> <br/>
		Telefon: <input type="text" name="telefon" value="<?=mysql_result($q400,0,10)?>" class="default"><br/>
		Kontakt e-mail: <input type="text" name="email" value="<?=mysql_result($q400,0,2)?>" class="default"><br/>
		<br/>
		ID: <b><?=$osoba?></b></td>
	</tr></table>

	<p>
	<input type="Submit" value=" Izmijeni "></form>
	<a href="?sta=studentska/osobe&akcija=edit&osoba=<?=$osoba?>">Povratak nazad</a>
	</p>
	<?

} // if ($akcija == "podaci")




// Upis studenta na semestar

else if ($akcija == "upis") {

	$student = intval($_REQUEST['osoba']);
	$studij = intval($_REQUEST['studij']);
	$semestar = intval($_REQUEST['semestar']);
	$godina = intval($_REQUEST['godina']);

	// Neispravni parametri se ne bi trebali desiti, osim u slučaju hackovanja
	// a i tada je "šteta" samo nekonzistentnost baze

	$q500 = myquery("select ime, prezime, brindexa from osoba where id=$student");
	$ime = mysql_result($q500,0,0);
	$prezime = mysql_result($q500,0,1);
	$brindexa = mysql_result($q500,0,2);

	$q505 = myquery("select naziv from akademska_godina where id=$godina");
	$naziv_ak_god = mysql_result($q505,0,0);


	?>
	<a href="?sta=studentska/osobe&akcija=edit&osoba=<?=$student?>">Nazad na podatke o osobi</a><br/><br/>
	<h2><?=$ime?> <?=$prezime?> - upis</h2><?
	print genform("POST");
	?>
	<input type="hidden" name="subakcija" value="upis_potvrda">
	<?


	// Ako je subakcija, potvrdjujemo da se moze izvrsiti upis
	$ok_izvrsiti_upis=0;

	if ($_POST['subakcija']=="upis_potvrda" && check_csrf_token()) {

		$ok_izvrsiti_upis=1;

		// Potvrdjujemo promjenu studija napravljenu tokom rada
		$ns = intval($_REQUEST['novi_studij']);
		if ($ns>0) {
			$studij=$ns;
			$_REQUEST['novi_studij'] = 0;
			?>
	<input type="hidden" name="studij" value="<?=$studij?>">
	<input type="hidden" name="novi_studij" value="0">
			<?
			$ok_izvrsiti_upis=0; // Tražimo novu potvrdu jer od izbora studija ovisi previše stvari
			// npr. ugovor o učenju
		}
	}


	// Šta je student slušao i kako?
	$q510 = myquery("select studij, nacin_studiranja, plan_studija, semestar from student_studij where student=$student order by akademska_godina desc, semestar desc limit 1");
	$stari_studij=$nacin_studiranja=$plan_studija=$ponovac=0;
	if (mysql_num_rows($q510)>0) {
		$stari_studij=mysql_result($q510,0,0);
		$nacin_studiranja=mysql_result($q510,0,1);
		$plan_studija=mysql_result($q510,0,2);
		if (mysql_result($q510,0,3)>=$semestar) $ponovac=1;
	} else if (intval($_REQUEST['nacin_studiranja'])>0) {
		$nacin_studiranja=intval($_REQUEST['nacin_studiranja']);
	}

	// Ako je promijenjen studij, moramo odrediti i novi plan studija
	if ($stari_studij != $studij) {
		$ponovac=0;
		$q515 = myquery("select godina_vazenja from plan_studija where studij=$studij order by godina_vazenja desc limit 1");
		$plan_studija = mysql_result($q515,0,0);
	}


	// Novi student
	$mijenja_studij=0;
	if ($stari_studij==0 && $ns==0 && $ok_izvrsiti_upis==0) {
		// Šta je odabrao na prijemnom? (pretpostavljamo da godine idu hronološkim redom)
		$izabrani_studij=$studij;
		$q520 = myquery("select pp.studij_prvi, pt.ciklus_studija from prijemni_prijava as pp, prijemni_termin as pt where pp.osoba=$student and pp.prijemni_termin=pt.id and pt.akademska_godina=$godina order by pt.datum desc limit 1");
		if (mysql_num_rows($q520)>0) {
			$izabrani_studij=mysql_result($q520,0,0);
			$ciklus = mysql_result($q520,0,1);
		} else {
			// Iz parametra studij ćemo probati odrediti ciklus
			$q530 = myquery("select ts.ciklus from tipstudija as ts, studij as s where s.id=$studij and s.tipstudija=ts.id");
			if (mysql_num_rows($q530)>0)
				$ciklus = mysql_result($q530,0,0);
			else
				$ciklus=1; // nemamo pojma = prvi ciklus
		}

		// Lista studija
		$q550 = myquery("select s.id, s.naziv from studij as s, tipstudija as ts where s.tipstudija=ts.id and ts.ciklus=$ciklus and ts.moguc_upis=1 order by s.naziv");
		?>
		<p><b>Izaberite studij koji će student upisati:</b><br/>
		<?
		while ($r550 = mysql_fetch_row($q550)) {
			if ($r550[0]==$izabrani_studij) $dodaj=" CHECKED"; else $dodaj="";
			print '<input type="radio" name="novi_studij" value="'.$r550[0].'"'.$dodaj.'>'.$r550[1]."<br/>\n";
		}
		print "</p>\n\n";
		$mijenja_studij=1;
	}


	// Izbor studija kod zavrsetka prethodnog
	$q540 = myquery("select ts.trajanje, s.naziv, ts.ciklus, s.institucija from studij as s, tipstudija as ts where s.id=$studij and s.tipstudija=ts.id");
	if (mysql_num_rows($q540)>0) {
		$trajanje=mysql_result($q540,0,0);
		$naziv_studija=mysql_result($q540,0,1);
		$ciklus=mysql_result($q540,0,2);
		$institucija=mysql_result($q540,0,3);
	} else $ok_izvrsiti_upis=0; // nepoznat studij

	// Pošto se akcija "edit" ne bavi određivanjem sljedećeg ciklusa, ona će proslijediti 
	// prevelik broj semestra
	if ($semestar>$trajanje && $stari_studij!=0) {
		// Biramo sljedeći ciklus istog studija po tome što ga nudi ista institucija
		$ciklus++;
		$q545 = myquery("select s.id from studij as s, tipstudija as ts where s.institucija=$institucija and s.tipstudija=ts.id and ts.ciklus=$ciklus and ts.moguc_upis=1");
		if (mysql_num_rows($q545)>0) {
			$izabrani_studij=mysql_result($q545,0,0);
		}
	
		$q550 = myquery("select s.id, s.naziv from studij as s, tipstudija as ts where s.tipstudija=ts.id and ts.ciklus=$ciklus and ts.moguc_upis=1 order by s.naziv");
		?>
		<p><b>Izaberite studij koji će student upisati:</b><br/>
		<?
		while ($r550 = mysql_fetch_row($q550)) {
			if ($r550[0]==$izabrani_studij) $dodaj=" CHECKED"; else $dodaj="";
			print '<input type="radio" name="novi_studij" value="'.$r550[0].'"'.$dodaj.'>'.$r550[1]."<br/>\n";
		}
		print "</p>\n\n";

		// Postavljamo semestar na 1
		unset($_REQUEST['semestar']);
		print '<input type="hidden" name="semestar" value="1">'."\n";

		$prijedlog_nacin_studiranja=$nacin_studiranja;
		$nacin_studiranja=0; // Ponovo se mora izabrati način studiranja

		$ok_izvrsiti_upis=0;
		$mijenja_studij=1;
	} else if ($stari_studij!=0) {
		?>
		<p>Upis na studij <?=$naziv_studija?>, <?=$semestar?>. semestar:</p>
		<?
	}



	// Izbor načina studiranja
	if ($nacin_studiranja==0) {
		?>
		<p><b>Izaberite način studiranja studenta:</b><br/>
		<?
		$q560 = myquery("select id, naziv from nacin_studiranja where id!=0"); // 0 = nepoznat status
		while ($r560 = mysql_fetch_row($q560)) {
			if ($r560[0]==$prijedlog_nacin_studiranja) $dodaj=" CHECKED"; else $dodaj="";
			print '<input type="radio" name="nacin_studiranja" value="'.$r560[0].'"'.$dodaj.'>'.$r560[1]."<br/>\n";
		}
		$ok_izvrsiti_upis=0;
	}



	// Da li ima nepoložene predmete sa ranijih semestara?
	if ($semestar>1 && $semestar%2==1 && $stari_studij!=0) {

	// Uvodimo dva načina izbora predmeta - preko plana studija i preko odslušanih predmeta u prošloj godini
	// U slučaju da nije definisan plan studija, bira se ovaj drugi način, ali on nije pouzdan zbog komplikacije
	// oko izbornih predmeta i ECTSova

	if ($plan_studija>0) {
		// Prema novom zakonu, uslov za upis je jedan predmet iz prethodne godine
		$predmeti_pao=array();
		$stari_predmet=array();

		$q570 = myquery("select predmet, obavezan, semestar from plan_studija where godina_vazenja=$plan_studija and studij=$stari_studij and semestar<$semestar order by semestar");
		$slusao=array();
		while ($r570 = mysql_fetch_row($q570)) {
			$psemestar = $r570[2];
			if ($r570[1]==1) { // obavezan
				$predmet = $r570[0];

				$q580 = myquery("select count(*) from konacna_ocjena where student=$student and predmet=$predmet and ocjena>5");
				if (mysql_result($q580,0,0)<1) {
					$q590 = myquery("select ects, naziv from predmet where id=$predmet");
					$predmeti_pao[$predmet]=mysql_result($q590,0,1);
					if ($psemestar<$semestar-2) $stari_predmet[$predmet]=1;
				}
			} else { // izborni
				$is = $r570[0];
				$slusao_id=0;
				$polozio=0;
				$q600 = myquery("select predmet from izborni_slot where id=$is");
				while ($r600 = mysql_fetch_row($q600)) {
					$predmet=$r600[0];
					if ($slusao[$predmet]!="") continue; // kada je isti predmet u dva slota

					// Koji je od ovih slušao?
					$q610 = myquery("select count(*) from student_predmet as sp, ponudakursa as pk where sp.student=$student and sp.predmet=pk.id and pk.predmet=$predmet");
					if (mysql_result($q610,0,0)>0) {
						$slusao_id=$predmet;
						$q620 = myquery("select ects, naziv from predmet where id=$predmet");
						$slusao[$predmet]=mysql_result($q620,0,1);
					}

					// Da li je polozio?
					$q630 = myquery("select count(*) from konacna_ocjena where student=$student and predmet=$predmet and ocjena>5");
					if (mysql_result($q630,0,0)>0) {
						$polozio=1; break;
					}
				}
				if ($polozio==0) { // nije položio nijedan od mogućih predmeta u slotu
					if ($slusao_id>0) $predmeti_pao[$slusao_id]=$slusao[$slusao_id];
					else {
						// Ubacićemo nešto u niz $predmeti_pao da se zna da nema uslov
						// ali u biti ne znamo šta
						$predmeti_pao[0]="X";
					}
					if ($psemestar<$semestar-2) $stari_predmet[$slusao_id]=1;
				}
			}
		}

	} else { // if ($plan_studija>0)
		// Nemamo plana studija, pokušavamo odrediti šta je student slušao ranijih godina
		// Nepouzdano zbog kolizija, izbornih predmeta itd.

		$q640 = myquery("select pk.predmet, p.ects, pk.semestar, p.naziv from ponudakursa as pk, student_predmet as sp, predmet as p where sp.student=$student and sp.predmet=pk.id and pk.semestar<$semestar and pk.predmet=p.id");
		$predmeti_pao=array();
		while ($r650 = mysql_fetch_row($q650)) {
			$predmet = $r650[0];
			$psemestar = $r650[2];
			$pnaziv = $r650[3];
			$q660 = myquery("select count(*) from konacna_ocjena where student=$student and predmet=$predmet");
			if (mysql_result($q660,0,0)<1 && !$predmeti_pao[$predmet]) { 
				$predmeti_pao[$predmet]=$pnaziv;
				if ($psemestar<$semestar-2) $stari_predmet[$predmet]=1;
			}
		}
	}

	// Tabela za unos ocjena na predmetima koje je pao:
	if (count($predmeti_pao)>0 && $ok_izvrsiti_upis==0) {
		?>
		<p><b>Predmeti iz kojih je student ostao neocijenjen - upišite eventualne ocjene u polja lijevo:</b></p>
		<table border="0">
		<?
		foreach ($predmeti_pao as $id => $naziv) {
			if ($id==0) {
				// Ovo je jedini pametan razlog da se pojavi id nula
				?>
				<tr><td colspan="2">Student nije slušao nijedan od ponuđenih izbornih predmeta koje je po planu studija trebao slušati.<br/> Pošto ima dovoljan broj ostvarenih ECTS kredita pretpostavićemo da je sve u redu.</td></tr>
				<?
				continue;
			}
			?>
			<tr><td><input type="text" size="3" name="pao-<?=$id?>"></td>
			<td><?=$naziv?></td></tr>
			<?
		}
		?>
		</table>
		<?
	}

	} // if ($semestar%2 ==1)




	// IZBORNI PREDMETI

	// novi studij - određujemo najnoviji plan studija za taj studij
	if ($ns>0) { 
		$q670 = myquery("select godina_vazenja from plan_studija where studij=$studij order by godina_vazenja desc limit 1");
		if (mysql_num_rows($q670)>0)
			$plan_studija = mysql_result($q670,0,0);
	}

	// Nema potrebe gledati dalje ako treba tek izabrati studij
	if ($mijenja_studij==0) {
		// Da li je popunjen ugovor o učenju?
		$q680 = myquery("select id from ugovoroucenju where student=$student and akademska_godina=$godina and studij=$studij and semestar=$semestar");
		$uoupk = array();
		if (mysql_num_rows($q680)>0) {
			$uou=mysql_result($q680,0,0);
			if ($ok_izvrsiti_upis==0) print "<p>Popunjen Ugovor o učenju (ID: $uou).\n";
			$q690 = myquery("select p.id, p.naziv from ugovoroucenju_izborni as uoui, predmet as p where uoui.ugovoroucenju=$uou and uoui.predmet=p.id");
			if (mysql_num_rows($q690)>0 && $ok_izvrsiti_upis==0) print " Izabrani predmeti u semestru:";
			while ($r690 = mysql_fetch_row($q690)) {
				$predmet = $r690[0];

				if ($ok_izvrsiti_upis==0) print "<br/>* $r690[1]\n";

				// Da li je već položio predmet
				$q695 = myquery("select count(*) from konacna_ocjena where student=$student and predmet=$predmet");
				if (mysql_result($q695,0,0)>0) {
					if ($ok_izvrsiti_upis==0) print " - već položen! Preskačem";
				}
	
				// Tražimo ponudukursa
				$q700 = myquery("select id from ponudakursa where predmet=$predmet and studij=$studij and semestar=$semestar and akademska_godina=$godina");
				if (mysql_num_rows($q700)<1) {
					if ($ok_izvrsiti_upis==0) print " - nije pronađena ponuda kursa!!\n";
				} else $uoupk[$predmet] = mysql_result($q700,0,0);
			}
			if ($ok_izvrsiti_upis==0) print "</p>\n";
		} else {
			if ($ok_izvrsiti_upis==0) print "<p><b>Nije popunjen Ugovor o učenju!</b> Izaberite izborne predmete ručno.</p>\n";
		}


		// Nalazim izborne predmete 

		// Ako postoji plan studija, problem je jednostavan
		if ($plan_studija>0) {
			$bio_predmet=array();
			$q710 = myquery("select predmet from plan_studija where godina_vazenja=$plan_studija and studij=$studij and semestar=$semestar and obavezan=0");
			while ($r710 = mysql_fetch_row($q710)) {
				$izborni_slot = $r710[0];
				$q720 = myquery("select p.id, p.naziv, p.ects from izborni_slot as iz, predmet as p where iz.id=$izborni_slot and iz.predmet=p.id");

				// Prvi prolaz, za provjere
				$nastavak=0;
				$ispis_predmet=array();
				$ispis_predmet_ects = array();
				while ($r720 = mysql_fetch_row($q720)) {
					$predmet=$r720[0];
					if (in_array($predmet, $bio_predmet)) continue;
					array_push($bio_predmet, $predmet);

					// Da li je položen?
					$q730 = myquery("select count(*) from konacna_ocjena where student=$student and predmet=$predmet");
					if (mysql_result($q730,0,0)>0) {
						$nastavak=1; break;
					}

					// Da li je već izabran u Ugovoru o učenju?
					if ($uoupk[$predmet]>0) {
						if ($ok_izvrsiti_upis==0) print '<input type="hidden" name="izborni-'.$uoupk[$predmet].'" value="on">'."\n";
						$nastavak=1; break;
					}

					$ispis_predmet[$predmet]=$r720[1];
					$ispis_predmet_ects[$predmet]=$r720[2];
				}
				if ($nastavak==1) continue;

				if ($ok_izvrsiti_upis==1 && count($ispis_predmet)>0) {
					print "<p><b>Morate izabrati jedan od ovih predmeta.</b> Ako to znači da ste sada izabrali viška predmeta, koristite dugme za povratak nazad.</p>\n";
					$ok_izvrsiti_upis=0;
				}

				// Drugi prolaz
				foreach ($ispis_predmet as $predmet => $pnaziv) {
					// Odredjujemo ponudu kursa
					$q740 = myquery("select id from ponudakursa where predmet=$predmet and studij=$studij and semestar=$semestar and akademska_godina=$godina");
					if (mysql_num_rows($q740)<1) {
						niceerror("Kurs '$pnaziv' nije ponuđen za studij $naziv_studija, $semestar. semestar, godina $naziv_ak_god");
						zamgerlog("nije ponudjen predmet pp$predmet, studij s$studij, semestar $semestar, ag$godina", 3); // 3 - greska
					} else {
						?>
						<input type="checkbox" name="izborni-<?=mysql_result($q740,0,0)?>"> <?=$pnaziv?> (<?=$ispis_predmet_ects[$predmet]?> ECTS)<br/>
						<?
					}
				}
			}

		} else { // Nije definisan plan studija - deduciramo izborne predmete iz onoga što se držalo prošle godine

			// Da li je zbir ECTS bodova sa izbornim predmetima = 30?
			$q560 = myquery("select p.id, p.naziv, pk.id, p.ects from predmet as p, ponudakursa as pk where pk.akademska_godina=$godina and pk.studij=$studij and pk.semestar=$semestar and obavezan=0 and pk.predmet=p.id");
			if (mysql_num_rows($q560)>0 && $ok_izvrsiti_upis==1) {
				$q565 = myquery("select sum(p.ects) from ponudakursa as pk, predmet as p where pk.studij=$studij and pk.semestar=$semestar and pk.akademska_godina=$godina and pk.obavezan=1 and pk.predmet=p.id");
				$ects_suma = mysql_result($q565,0,0);
		
				// Upisujemo na izborne predmete koji su odabrani
				foreach($_REQUEST as $key=>$value) {
					if (substr($key,0,8) != "izborni-") continue;
					if ($value=="") continue;
					$predmet = intval(substr($key,8));
					$q566 = myquery("select p.ects from ponudakursa as pk, predmet as p where pk.id=$predmet and pk.predmet=p.id");
					$ects_suma += mysql_result($q566,0,0);
				}
		
				if ($ects_suma != 30) {
					$ok_izvrsiti_upis=0;
					niceerror("Izabrani izborni predmeti čine sumu $ects ECTS kredita, umjesto 30");
				}
			}
		
			if (mysql_num_rows($q560)>0 && $ok_izvrsiti_upis==0) {
				?>
				<p><b>Izaberite izborne predmete:</b><br/>
				<?
				while ($r560 = mysql_fetch_row($q560)) {
					$q570 = myquery("select count(*) from konacna_ocjena where student=$student and predmet=$r560[0]");
					if (mysql_result($q570,0,0)<1) {
						// Nije polozio/la - koristimo pk
						?>
						<input type="checkbox" name="izborni-<?=$r560[2]?>"> <?=$r560[1]?> (<?=$r560[3]?> ECTS)<br/>
						<?
					}
				}
			}

		}
	}  // if ($stari_studij!=0 && $semestar<=$trajanje)

	// Studentu nikada nije zadat broj indexa (npr. prvi put se upisuje)
	if (($brindexa==0 || $brindexa=="" || $mijenja_studij==1) && $ok_izvrsiti_upis==0) {
		if ($brindexa==0) $brindexa="";
		?>
		<p><b>Unesite broj indeksa za ovog studenta:</b><br/>
		<input type="text" name="novi_brindexa" size="10" value="<?=$brindexa?>"></p>
		<?
	}



	// ------ Izvrsenje upisa!

	if ($ok_izvrsiti_upis==1 && check_csrf_token()) {

		// Upis u prvi semestar - kandidat za prijemni postaje student!
		if ($stari_studij==0) {
			$q640 = myquery("insert into privilegije set privilegija='student', osoba=$student");
//			$q640 = myquery("update privilegije set privilegija='student' where osoba=$student and privilegija='prijemni'");

			// AUTH tabelu cemo srediti naknadno
			print "-- $prezime $ime proglašen za studenta<br/>\n";
		}

		// Novi broj indexa
		$nbri = my_escape($_REQUEST['novi_brindexa']);
		if ($nbri!="") {
			$q650 = myquery("update osoba set brindexa='$nbri' where id=$student");
			print "-- broj indeksa postavljen na $nbri<br/>\n";
		}

		// Upisujemo ocjene za predmete koje su dopisane
		foreach ($predmeti_pao as $predmet=>$naziv_predmeta) {
			$ocjena = intval($_REQUEST["pao-$predmet"]);
			if ($ocjena>5) {
				// Upisujem dopisanu ocjenu
				$q590 = myquery("insert into konacna_ocjena set student=$student, predmet=$predmet, ocjena=$ocjena, akademska_godina=$ag");
				zamgerlog("dopisana ocjena $ocjena prilikom upisa na studij (predmet pp$predmet, student u$student)", 4); // 4 = audit
				print "-- Dopisana ocjena $ocjena za predmet $naziv_predmeta<br/>\n";
			} else {
				// Student prenio predmet
				if ($predmet==0) continue; // nije slušao nijedan od mogućih izbornih predmeta
				// Provjera broja ECTS kredita je obavljena na početnoj strani (akcija "edit")
				// pa ćemo pretpostaviti sve najbolje :)

				// Moramo upisati studenta u istu ponudu kursa koju je ranije slušao
				$q592 = myquery("select pk.studij,pk.semestar from ponudakursa as pk, student_predmet as sp where sp.student=$student and sp.predmet=pk.id and pk.predmet=$predmet order by pk.akademska_godina desc limit 1");
				$q594 = myquery("select id from ponudakursa where predmet=$predmet and studij=".mysql_result($q592,0,0)." and semestar=".mysql_result($q592,0,1)." and akademska_godina=$godina");

				upis_studenta_na_predmet($student, mysql_result($q594,0,0));
				print "-- Upisan u predmet $naziv_predmeta koji je prenio s prethodne godine (ako je ovo greška, zapamtite da ga treba ispisati sa predmeta!)<br/>\n";
			}
		}


		// Upisujemo studenta na novi studij
		$q600 = myquery("insert into student_studij set student=$student, studij=$studij, semestar=$semestar, akademska_godina=$godina, nacin_studiranja=$nacin_studiranja, ponovac=$ponovac, odluka=0, plan_studija=$plan_studija");

		// Upisujemo na sve obavezne predmete na studiju
		$q610 = myquery("select pk.id, p.id, p.naziv from ponudakursa as pk, predmet as p where pk.studij=$studij and pk.semestar=$semestar and pk.akademska_godina=$godina and pk.obavezan=1 and pk.predmet=p.id");
		while ($r610 = mysql_fetch_row($q610)) {
			// Da li ga je vec polozio
			$q615 = myquery("select count(*) from konacna_ocjena where student=$student and predmet=$r610[1]");
			if (mysql_result($q615,0,0)<1) {
				upis_studenta_na_predmet($student, $r610[0]);
			} else {
				print "-- Student NIJE upisan u $r610[2] jer ga je već položio<br/>\n";
			}
		}

		// Upisujemo na izborne predmete koji su odabrani
		foreach($_REQUEST as $key=>$value) {
			if (substr($key,0,8) != "izborni-") continue;
			if ($value=="") continue;
			$ponudakursa = intval(substr($key,8)); // drugi dio ključa je ponudakursa
			upis_studenta_na_predmet($student, $ponudakursa);
			$q635 = myquery("select p.naziv from ponudakursa as pk, predmet as p where pk.id=$ponudakursa and pk.predmet=p.id");
			print "-- Student upisan u izborni predmet ".mysql_result($q635,0,0)."<br/>";
		}
		
		nicemessage("Student uspješno upisan na $naziv_studija, $semestar. semestar");
		zamgerlog("Student u$student upisan na studij s$studij, semestar $semestar, godina ag$godina", 4); // 4 - audit
		return;

	} else {
		?>
		<p>&nbsp;</p>
		<input type="submit" value=" Potvrda upisa ">
		</form>
		<?
	}
} // $akcija == "upis"



// Ispis sa studija

else if ($akcija == "ispis") {

	// Svi parametri su obavezni!
	$studij = $_REQUEST['studij'];
	$semestar = $_REQUEST['semestar'];
	$ak_god = $_REQUEST['godina'];

	$q2500 = myquery("select ime, prezime from osoba where id=$osoba");
	$ime = mysql_result($q2500,0,0);
	$prezime = mysql_result($q2500,0,1);

	$q2510 = myquery("select naziv from akademska_godina where id=$ak_god");
	$naziv_ak_god = mysql_result($q2510,0,0);

	?>
	<a href="?sta=studentska/osobe&akcija=edit&osoba=<?=$osoba?>">Nazad na podatke o osobi</a><br/><br/>
	<h2><?=$ime?> <?=$prezime?> - ispis sa studija</h2>
	<?

	// Gdje je trenutno upisan?
	$q2520 = myquery("select s.id, s.naziv, ss.semestar from studij as s, student_studij as ss where ss.student=$osoba and ss.studij=s.id and ss.akademska_godina=$ak_god");
	if (mysql_num_rows($q2520)<1) {
		niceerror("Student nije upisan na fakultet u izabranoj akademskoj godini!");
		zamgerlog("pokusao ispisati studenta u$osoba koji nije upisan u ag$ak_god", 3);
		return;
	}
	if (mysql_result($q2520,0,0)!=$studij) {
		niceerror("Student nije upisan na izabrani studij u izabranoj akademskoj godini!");
		zamgerlog("pokusao ispisati studenta u$osoba sa studija $studij koji ne slusa u ag$ak_god", 3);
		return;
	}
	if (mysql_result($q2520,0,2)!=$semestar) {
		niceerror("Student nije upisan na izabrani semestar u izabranoj akademskoj godini!");
		zamgerlog("pokusao ispisati studenta u$osoba sa semestra $semestar koji ne slusa u ag$ak_god", 3);
		return;
	}
	$naziv_studija = mysql_result($q2520,0,1);

	?>
	<h3>Studij: <?=$naziv_studija?>, <?=$semestar?>. semestar, <?=$naziv_ak_god?> godina</h3>
	<?

	// Ispis sa studija
	if ($_REQUEST['potvrda']=="1") {
		$q530 = myquery("select pk.predmet from student_predmet as sp, ponudakursa as pk where sp.student=$osoba and sp.predmet=pk.id and pk.akademska_godina=$ak_god");
		while ($r530 = mysql_fetch_row($q530)) {
			$predmet = $r530[0];
			ispis_studenta_sa_predmeta($osoba, $predmet, $ak_god);
			zamgerlog("ispisujem studenta u$osoba sa predmeta pp$predmet (promjena odsjeka)",4); // 4 - audit
		}
		$q550 = myquery("delete from student_studij where student=$osoba and akademska_godina=$ak_god");
		nicemessage("Ispisujem studenta sa studija $naziv_studija i svih predmeta koje trenutno sluša.");
		zamgerlog("ispisujem studenta u$osoba sa studija $naziv_studija (ag$ak_god) (promjena odsjeka)", 4);
	} else {
		?>
		<p>Student će biti ispisan sa sljedećih predmeta:<ul>
		<?
		$q520 = myquery("select p.naziv from predmet as p, ponudakursa as pk, student_predmet as sp where sp.student=$osoba and sp.predmet=pk.id and pk.akademska_godina=$ak_god and pk.predmet=p.id");
		while ($r520 = mysql_fetch_row($q520)) {
			print "<li>$r520[0]</li>\n";
		}
		?>
		</ul></p>
		<p>NAPOMENA: Svi bodovi ostvareni na ovim predmetima će biti izgubljeni! Trenutno nema drugog načina da se student ispiše sa studija.</p>
		<p>Kliknite na dugme "Potvrda" da potvrdite ispis.</p>
		<?=genform("POST");?>
		<input type="hidden" name="potvrda" value="1">
		<input type="submit" value=" Potvrda ">
		</form>
		<?
	}
}



// Pregled predmeta za koliziju i potvrda

else if ($akcija == "kolizija") {
	?>
	<a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=edit">Nazad na podatke o studentu</a><br/><br/>
	<?

	// Odredjujemo u koju akademsku godinu bi se trebao upisivati student
	$nova_ak_god=intval($_REQUEST['godina']);
	$q398 = myquery("select naziv from akademska_godina where id=$nova_ak_god");
	$naziv_godine=mysql_result($q398,0,0);

	// Koji studij student sluša? Treba nam radi ponudekursa
	$q399 = myquery("select s.id, s.naziv from student_studij as ss, studij as s where ss.student=$osoba and ss.studij=s.id order by ss.akademska_godina desc, ss.semestar desc");
	$studij = mysql_result($q399,0,0);
	$studij_naziv = mysql_result($q399,0,1);
	
	$q400 = myquery("select predmet from kolizija where student=$osoba and akademska_godina=$nova_ak_god");
	$predmeti=$ponudekursa=array();
	$greska=0;
	while ($r400 = mysql_fetch_row($q400)) {
		$predmet = $r400[0];

		// Eliminišemo predmete koje je položio u međuvremenu
		$q410 = myquery("select count(*) from konacna_ocjena where student=$osoba and predmet=$predmet and ocjena>5");
		if (mysql_result($q410,0,0)<1) {
			$q420 = myquery("select naziv from predmet where id=$predmet");
			$predmeti[$predmet] = "<b>".mysql_result($q420,0,0)."</b> ($studij_naziv, ";

			// Odredjujemo ponudu kursa koju bi student trebao slušati
			$q430 = myquery("select id, semestar, obavezan from ponudakursa where predmet=$predmet and studij=$studij and akademska_godina=$nova_ak_god");
			if (mysql_num_rows($q430)<1) {
				if ($greska==0) niceerror("Nije pronađena ponuda kursa");
				print "Predmet <b>".mysql_result($q420,0,0)."</b>, studij <b>$studij_naziv</b>, godina $naziv_godine<br/>";
				$greska=1;
			}
			$ponudekursa[$predmet] = mysql_result($q430,0,0);
			$predmeti[$predmet] .= mysql_result($q430,0,1).". semestar";
			if (mysql_result($q430,0,2)==0) $predmeti[$predmet] .= ", izborni";
			$predmeti[$predmet] .= ")";
		}
	}

	if ($greska==1) return; // ne idemo dalje

	if (count($predmeti)==0) { // nema ništa za koliziju!!!
		nicemessage ("Student je u međuvremenu položio/la sve predmete! Nema se ništa za upisati.");
		return;
	}
	

	if ($_REQUEST['subakcija'] == "potvrda") {
		foreach ($ponudekursa as $predmet => $pk) {
			upis_studenta_na_predmet($osoba, $pk);
			$q440 = myquery("delete from kolizija where student=$osoba and akademska_godina=$nova_ak_god and predmet=$predmet");
		}
		zamgerlog("prihvacen zahtjev za koliziju studenta u$osoba", 4); // 4 = audit
		print "<p>Upis je potvrđen.</p>\n";
	} else {
		?>
		<p>Student želi upisati sljedeće predmete:</p>
		<ul>
		<?
		foreach ($predmeti as $tekst) {
			print "<li>$tekst</li>\n";
		}
		?>
		</ul>
		<?=genform("POST");?>
		<input type="hidden" name="subakcija" value="potvrda">
		<input type="submit" value=" Potvrdi ">
		</form>
		<?
	}
}



// Manuelni upis/ispis na predmete

else if ($akcija == "predmeti") {
	?>
	<a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=edit">Nazad na podatke o studentu</a><br/><br/>
	<?

	// Parametar "spisak" određuje koji predmeti će biti prikazani
	$spisak = intval($_REQUEST['spisak']);

	$q2000 = myquery("select ime, prezime from osoba where id=$osoba");
	if (mysql_num_rows($q2000)<1) {
		niceerror("Nepoznata osoba $osoba");
		return;
	}
	$ime = mysql_result($q2000,0,0);
	$prezime = mysql_result($q2000,0,1);

	?>
	<h2><?=$ime?> <?=$prezime?> - upis/ispis na predmete</h2>
	<?


	// Subakcije: upis i ispis sa predmeta

	if ($_REQUEST['subakcija']=="upisi") {
		$ponudakursa = intval($_REQUEST['ponudakursa']);
		upis_studenta_na_predmet($osoba, $ponudakursa);

		$q2200 = myquery("select p.naziv from ponudakursa as pk, predmet as p where pk.id=$ponudakursa and pk.predmet=p.id");
		$naziv_predmeta = mysql_result($q2200,0,0);

		nicemessage("Student upisan na predmet $naziv_predmeta");
		zamgerlog("student u$osoba manuelno upisan na predmet p$ponudakursa", 4); // 4 - audit
	}

	if ($_REQUEST['subakcija']=="ispisi") {
		$ponudakursa = intval($_REQUEST['ponudakursa']);
		$q2200 = myquery("select p.id, p.naziv, pk.akademska_godina from ponudakursa as pk, predmet as p where pk.id=$ponudakursa and pk.predmet=p.id");
		$predmet = mysql_result($q2200,0,0);
		$naziv_predmeta = mysql_result($q2200,0,1);
		$ag = mysql_result($q2200,0,2);

		// Upozorenje ako ima neke bodove?
		$q2210 = myquery("select sum(bodovi) from komponentebodovi where student=$osoba and predmet=$ponudakursa");
		$bodovi = mysql_result($q2210,0,0);
		if ($bodovi!=0 && $bodovi!=10 && $_REQUEST['siguran']!="da") { // 10 bodova je default za prisustvo
			nicemessage("Upozorenje! Student je osvojio $bodovi bodova na predmetu $naziv_predmeta.");
			?>Da li ste sigurni da ga želite ispisati?<br/>
			<?=genform("POST");?>
			<input type="hidden" name="siguran" value="da">
			<input type="submit" value=" Potvrda ">
			</form>
			<?
			return;
		}

		ispis_studenta_sa_predmeta($osoba, $predmet, $ag);

		nicemessage("Student ispisan sa predmeta $naziv_predmeta");
		zamgerlog("student u$osoba manuelno ispisan sa predmeta p$ponudakursa", 4); // 4 - audit
	}



	// Aktuelna akademska godina

	$q2010 = myquery("select id, naziv from akademska_godina where aktuelna=1");
	$ak_god = mysql_result($q2010,0,0);
	$naziv_ag = mysql_result($q2010,0,1);

	$q2020 = myquery("select studij, semestar, plan_studija from student_studij where student=$osoba and akademska_godina=$ak_god");
	if (mysql_num_rows($q2020)>0) {
		$studij = mysql_result($q2020,0,0);
		$semestar = mysql_result($q2020,0,1);

		$q2025 = myquery("select naziv from studij where id=$studij");
		$naziv_studija = mysql_result($q2025,0,0);

		print "<p>Student trenutno ($naziv_ag) upisan na $naziv_studija, $semestar. semestar.</p>\n";

		// Upozorenje!
		if (mysql_result($q2020,0,2)>0) {
			print "<p><b>Napomena:</b> Student je već upisan na sve predmete koje je trebao slušati po odabranom planu studija!<br/> Koristite ovu opciju samo za izuzetke / odstupanja od plana ili u slučaju grešaka u radu Zamgera.<br/>U suprotnom, može se desiti da student nema adekvatan broj ECTS kredita ili da sluša izborni predmet<br/>koji ne bi smio slušati.</p>\n";
		}

	} else {
		// Student trenutno nije upisan nigdje... biramo zadnji studij koji je slušao
		if ($spisak==0) $spisak=1;
		$q2030 = myquery("select studij, semestar, akademska_godina from student_studij where student=$osoba order by akademska_godina desc limit 1");
		if (mysql_num_rows($q2030)>0) {
			$studij = mysql_result($q2030,0,0);

			$q2040 = myquery("select naziv from studij where id=$studij");
			$naziv_studija = mysql_result($q2040,0,0);

			$q2050 = myquery("select naziv from akademska_godina  where id=".mysql_result($q2030,0,2));

			print "<p>Student trenutno ($naziv_ag) nije upisan na fakultet! Posljednji put slušao $naziv_studija, ".mysql_result($q2030,0,0).". semestar, akademske ".mysql_result($q2050,0,0)." godine.</p>\n";
		} else {
			// Nikada nije bio student?
			$studij=0;
			if ($spisak<2) $spisak=2;
			print "<p>Osoba nikada nije bila naš student!</p>\n";
		}
	}

	// Opcije za spisak predmeta
	$s0 = ($spisak==0) ? "CHECKED" : "";
	$s1 = ($spisak==1) ? "CHECKED" : "";
	$s2 = ($spisak==2) ? "CHECKED" : "";

	unset($_REQUEST['subakcija']); // da se ne bi ponovila

	?>
	<?=genform("GET");?>
	<input type="radio" name="spisak" value="0" <?=$s0?>> Prikaži predmete sa izabranog studija i semestra<br/>
	<input type="radio" name="spisak" value="1" <?=$s1?>> Prikaži predmete sa svih semestara<br/>
	<input type="radio" name="spisak" value="2" <?=$s2?>> Prikaži predmete sa drugih studija<br/>
	<input type="submit" value=" Kreni "></form>
	<?


	// Ispis svih predmeta na studiju semestru je funkcija, pošto pozivanje unutar petlje ovisi o nivou spiska

	function dajpredmete($studij, $semestar, $student, $ag, $spisak) {
		$q2100 = myquery("select pk.id, p.id, p.naziv, pk.obavezan from ponudakursa as pk, predmet as p where pk.studij=$studij and pk.semestar=$semestar and pk.akademska_godina=$ag and pk.predmet=p.id order by p.naziv");
		while ($r2100 = mysql_fetch_row($q2100)) {
			$ponudakursa = $r2100[0];
			$predmet = $r2100[1];
			$predmet_naziv = $r2100[2];
			print "<li>$predmet_naziv";
			if ($r2100[3]!=1) print " (izborni)";

			// Da li je upisan?
			// Zbog mogućih bugova, prvo gledamo da li je upisan...
			$q2120 = myquery("select count(*) from student_predmet where student=$student and predmet=$ponudakursa");
			if (mysql_result($q2120,0,0)>0) {
				print " - <a href=\"?sta=studentska/osobe&akcija=predmeti&osoba=$student&subakcija=ispisi&ponudakursa=$ponudakursa&spisak=$spisak\">ispiši</a></li>\n";

			} else {
				// Da li je položen?
				$q2110 = myquery("select count(*) from konacna_ocjena where student=$student and predmet=$predmet");
				if (mysql_result($q2110,0,0)>0) {
					print " - položen</li>\n";

				} else {
					print " - <a href=\"?sta=studentska/osobe&akcija=predmeti&osoba=$student&subakcija=upisi&ponudakursa=$ponudakursa&spisak=$spisak\">upiši</a></li>\n";
				}
			}
		}
	} // function dajpredmete


	// Ispis predmeta

	if ($spisak==0) {
		print "<b>$naziv_studija, $semestar. semestar</b>\n<ul>\n";
		dajpredmete($studij, $semestar, $osoba, $ak_god, $spisak);
		print "</ul>\n";
	}

	else if ($spisak==1) {
		// Broj semestara?
		$q2060 = myquery("select ts.trajanje from studij as s, tipstudija as ts where s.id=$studij and s.tipstudija=ts.id");
		for ($s=1; $s<=mysql_result($q2060,0,0); $s++) {
			if ($s==$semestar) print "<b>$naziv_studija, $s. semestar</b>\n<ul>\n";
			else print "$naziv_studija, $s. semestar\n<ul>\n";
			dajpredmete($studij, $s, $osoba, $ak_god, $spisak);
			print "</ul>\n";
		}
	}

	else if ($spisak==2) {
		// Svi studiji
		$q2070 = myquery("select s.id, s.naziv, ts.trajanje from studij as s, tipstudija as ts where s.tipstudija=ts.id and ts.moguc_upis=1 order by ts.ciklus, s.naziv");
		while ($r2070=mysql_fetch_row($q2070)) {
			$stud=$r2070[0];
			$stud_naziv=$r2070[1];
			$stud_trajanje=$r2070[2];

			if ($stud==$studij) print "<b>$stud_naziv</b>\n<ul>\n";
			else print "$stud_naziv\n<ul>\n";

			for ($s=1; $s<=$stud_trajanje; $s++) {
				if ($stud==$studij && $s==$semestar) print "<b>$s. semestar</b>\n<ul>\n";
				else print "$s. semestar\n<ul>\n";
				dajpredmete($stud, $s, $osoba, $ak_god, $spisak);
				print "</ul>\n";
			}
			print "</ul>\n";
		}
	}

}



// Pregled informacija o osobi

else if ($akcija == "edit") {
	$pretraga = my_escape($_REQUEST['search']);
	$ofset = my_escape($_REQUEST['offset']);

	?><a href="?sta=studentska/osobe&search=<?=$pretraga?>&offset=<?=$ofset?>">Nazad na rezultate pretrage</a><br/><br/><?
	

	// Prvo odredjujemo aktuelnu akademsku godinu - ovaj upit se dosta koristi kasnije
	$q210 = myquery("select id,naziv from akademska_godina where aktuelna=1 order by id desc");
	if (mysql_num_rows($q210)<1) {
		// Nijedna godina nije aktuelna - ali mora postojati barem jedna u bazi
		$q210 = myquery("select id,naziv from akademska_godina order by id desc");
	}
	$id_ak_god = mysql_result($q210,0,0);
	$naziv_ak_god = mysql_result($q210,0,1);
	// Posto se id_ak_god moze promijeniti.... CLEANUP!!!
	$orig_iag = $id_ak_god;



	// ======= SUBMIT AKCIJE =========


	// Promjena korisničkog pristupa i pristupnih podataka
	if ($_POST['subakcija'] == "auth" && check_csrf_token()) {

		// LDAP
		if ($conf_system_auth == "ldap") {

			// Ako isključujemo pristup, stavljamo aktivan na 0
			$pristup = intval($_REQUEST['pristup']);
			if ($pristup!=0) {
				$q105 = myquery("update auth set aktivan=0 where id=$osoba");
				zamgerlog("ukinut login za korisnika u$osoba (ldap)",4);
			} else {

			$q107 = myquery("select count(*) from auth where id=$osoba");
			if (mysql_result($q107,0,0)>0) {
				$q105 = myquery("update auth set aktivan=1 where id=$osoba");
				zamgerlog("aktiviran login za korisnika u$osoba (ldap)",4);
			} else {


			// predloženi login
			$suggest_login = gen_ldap_uid($osoba);

			// Tražimo ovaj login na LDAPu...
			$ds = ldap_connect($conf_ldap_server);
			ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
			if (!ldap_bind($ds)) {
				zamgerlog("Ne mogu se spojiti na LDAP server",3); // 3 - greska
				niceerror("Ne mogu se spojiti na LDAP server");
				return;
			}

			$sr = ldap_search($ds, "", "uid=$suggest_login", array() /* just dn */ );
			if (!$sr) {
				zamgerlog("ldap_search() nije uspio.",3);
				niceerror("ldap_search() nije uspio.");
				return;
			}
			$results = ldap_get_entries($ds, $sr);
			if ($results['count'] < 1) {
				zamgerlog("login ne postoji na LDAPu ($suggest_login)",3);
				niceerror("Predloženi login ($suggest_login) nije pronađen na LDAP serveru!");
				print "<p>Da li ste uspravno unijeli broj indeksa, ime i prezime? Ako jeste, kontaktirajte administratora!</p>";

				// Nastavljamo dalje sa edit akcijom kako bi studentska mogla popraviti podatke

			} else {
				// Dodajemo login, ako nije podešen
				$q110 = myquery("select login, aktivan from auth where id=$osoba");
				if (mysql_num_rows($q110)==0) {
					$q111 = myquery("insert into auth set id=$osoba, login='$suggest_login', aktivan=1");
					zamgerlog("kreiran login za korisnika u$osoba (ldap - upis u tabelu)",4);
				}
				else {
					if (mysql_result($q110,0,0) == "") {
						$q112 = myquery("update auth set login='$suggest_login' where id=$osoba");
						zamgerlog("kreiran login za korisnika u$osoba (ldap - postavljeno polje login)",4);
					}
					if (mysql_result($q110,0,1)==0) {
						$q113 = myquery("update auth set aktivan=1 where id=$osoba");
						zamgerlog("kreiran login za korisnika u$osoba (ldap - aktivan=1)",4);
					}
				}

				// Generišemo email adresu ako nije podešena
				$q115 = myquery("select email from osoba where id=$osoba");
				if (mysql_result($q115,0,0) == "") {
					$email = $suggest_login.$conf_ldap_domain;
					$q114 = myquery("update osoba set email='$email' where id=$osoba");
					zamgerlog("promijenjen email za korisnika u$osoba",2); // nivo 2 - edit
				}
			}

			} // if ($q107...) ... else ...
			} // if ($auth!=0) ... else ...

		} // if ($conf_system_auth == "ldap")

		// Lokalna tabela sa šiframa
		else if ($conf_system_auth == "table") {

			$login = my_escape($_REQUEST['login']);
			$password = my_escape($_REQUEST['password']);

			$q120 = myquery("replace auth set id=$osoba, login='$login', password='$password', aktivan=1");
			zamgerlog("dodan/izmijenjen login za korisnika u$osoba (table)",4);

		}
	} // if ($_REQUEST['subakcija'] == "auth")


	// Upis studenta na predmet
	if ($_POST['subakcija'] == "upisi" && check_csrf_token()) {

		$predmet = intval($_POST['predmet']);
		if ($predmet==0) {
			nicemessage("Niste izabrali predmet");
		} else {
			$q130 = myquery("select count(*) from student_predmet where student=$osoba and predmet=$predmet");
			if (mysql_result($q130,0,0)<1) {
				upis_studenta_na_predmet($osoba, $predmet);
				zamgerlog("student u$osoba upisan na predmet p$predmet",4);
				$q136 = myquery("select p.naziv from predmet as p, ponudakursa as pk where pk.id=$predmet and pk.predmet=p.id");
				$naziv_predmeta = mysql_result($q136,0,0);
				nicemessage("Student upisan na predmet $naziv_predmeta.");
			}
		}
	}


	// Prijava nastavnika na predmet
	if ($_POST['subakcija'] == "angazuj" && check_csrf_token()) {

		$ponudakursa = intval($_POST['predmet']);
		$admin_predmeta = intval($_POST['admin_predmeta']);

		$q115 = myquery("select p.id, p.naziv from ponudakursa as pk, predmet as p where pk.id=$ponudakursa and pk.predmet=p.id");
		$predmet = mysql_result($q115,0,0);
		$naziv_predmeta = mysql_result($q115,0,1);

		$q130 = myquery("replace nastavnik_predmet set admin=$admin_predmeta, nastavnik=$osoba, predmet=$predmet, akademska_godina=$id_ak_god");

		zamgerlog("nastavnik u$osoba prijavljen na predmet p$ponudakursa (admin: $admin_predmeta, akademska godina: $id_ak_god)",4);
		nicemessage("Nastavnik prijavljen na predmet $naziv_predmeta.");
	}


	// Promjena uloga korisnika
	if ($_POST['subakcija'] == "uloga" && check_csrf_token()) {
		if (!$user_siteadmin) { niceerror("Nemate pravo na promjenu uloga!"); return; }

		$korisnik['student']=$korisnik['nastavnik']=$korisnik['prijemni']=$korisnik['studentska']=$korisnik['siteadmin']=0;
		$q150 = myquery("select privilegija from privilegije where osoba=$osoba");
		while($r150 = mysql_fetch_row($q150)) {
			if ($r150[0]=="student") $korisnik['student']=1;
			if ($r150[0]=="nastavnik") $korisnik['nastavnik']=1;
			if ($r150[0]=="prijemni") $korisnik['prijemni']=1;
			if ($r150[0]=="studentska") $korisnik['studentska']=1;
			if ($r150[0]=="siteadmin") $korisnik['siteadmin']=1;
		}

		foreach ($korisnik as $privilegija => $vrijednost) {
			if ($_POST[$privilegija]=="1" && $vrijednost==0) {
				$q151 = myquery("insert into privilegije set osoba=$osoba, privilegija='$privilegija'");
				zamgerlog("osobi u$osoba data privilegija $privilegija",4);
				nicemessage("Data privilegija $privilegija");
			}
			if ($_POST[$privilegija]!="1" && $vrijednost==1) {
				$q151 = myquery("delete from privilegije where osoba=$osoba and privilegija='$privilegija'");
				zamgerlog("osobi u$osoba oduzeta privilegija $privilegija",4);
				nicemessage("Oduzeta privilegija $privilegija");
			}
		}
	}


	// Osnovni podaci

	$q200 = myquery("select ime, prezime, email, brindexa, UNIX_TIMESTAMP(datum_rodjenja), mjesto_rodjenja, jmbg, drzavljanstvo, adresa, adresa_mjesto, telefon, kanton from osoba where id=$osoba");
	if (!($r200 = mysql_fetch_row($q200))) {
		zamgerlog("nepostojeca osoba u$osoba",3);
		niceerror("Nepostojeća osoba!");
		return;
	}
	$ime = mysql_result($q200,0,0);
	$prezime = mysql_result($q200,0,1);

	// Pripremam neke podatke za ispis
	$mjesto_rodj = "";
	if (mysql_result($q200,0,5)!=0) {
		$q201 = myquery("select naziv from mjesto where id=".mysql_result($q200,0,5));
		$mjesto_rodj = mysql_result($q201,0,0);
	}

	$adresa = mysql_result($q200,0,8);
	if (mysql_result($q200,0,9)!=0) {
		$q202 = myquery("select naziv from mjesto where id=".mysql_result($q200,0,9));
		$adresa .= ", ".mysql_result($q202,0,0);
	}

	$kanton = "";
	if (mysql_result($q200,0,11)>0) {
		$q205 = myquery("select naziv from kanton where id=".mysql_result($q200,0,11));
		$kanton = mysql_result($q205,0,0);
	}

	?>

	<h2><?=$ime?> <?=$prezime?></h2>
	<table border="0" width="600"><tr><td valign="top">
		Ime: <b><?=$ime?></b><br/>
		Prezime: <b><?=$prezime?></b><br/>
		Broj indexa (za studente): <b><?=mysql_result($q200,0,3)?></b><br/>
		JMBG: <b><?=mysql_result($q200,0,6)?></b><br/>
		<br/>
		Datum rođenja: <b><?
		if (mysql_result($q200,0,4)) print date("d. m. Y.", mysql_result($q200,0,4))?></b><br/>
		Mjesto rođenja: <b><?=$mjesto_rodj?></b><br/>
		Državljanstvo: <b><?=mysql_result($q200,0,7)?></b><br/>
		</td><td valign="top">
		Adresa: <b><?=$adresa?></b><br/>
		Kanton: <b><?=$kanton?></b><br/>
		Telefon: <b><?=mysql_result($q200,0,10)?></b><br/>
		Kontakt e-mail: <b><?=mysql_result($q200,0,2)?></b><br/>
		<br/>
		ID: <b><?=$osoba?></b><br/>
		<br/>
		</form>
		<?=genform("GET")?>
		<input type="hidden" name="akcija" value="podaci">
		<input type="Submit" value=" Izmijeni "></form></td>
	</tr></table>
	<?


	// Login&password

	$q201 = myquery("select login,password,aktivan from auth where id=$osoba");
	if (mysql_num_rows($q201)>0) {
		$login=mysql_result($q201,0,0);
		$password=mysql_result($q201,0,1);
		$pristup=mysql_result($q201,0,2);
	} else $pristup=0;

	if ($conf_system_auth == "table" || $user_siteadmin) {
		if ($pristup==0) {
			?>
			<?=genform("POST")?>
			<input type="hidden" name="subakcija" value="auth">
			<table border="0">
			<tr>
				<td colspan="2">Korisnički pristup:<br/> <font color="red">NEMA</font></td>
				<td>Korisničko ime:<br/> <input type="text" size="10" name="login" autocomplete="off"></td>
				<td>Šifra:<br/> <input type="password" size="10" name="password" autocomplete="off"></td>
				<td>Aktivan:<br/> <input type="checkbox" size="10" name="ima_auth" value="1"></td>
				<td><input type="Submit" value=" Dodaj "></td>
			</tr></table></form>
			<?
		}

		$q201 = myquery("select login,password,aktivan from auth where id=$osoba");
		while ($r201 = mysql_fetch_row($q201)) {
			$login=$r201[0];
			$password=$r201[1];
			$pristup=$r201[2];
			?>
			<?=genform("POST")?>
			<input type="hidden" name="subakcija" value="auth">
			<table border="0">
			<tr>
				<td colspan="2">Korisnički pristup:</td>
				<td>Korisničko ime:<br/> <input type="text" size="10" name="login" value="<?=$login?>"></td>
				<td>Šifra:<br/> <input type="password" size="10" name="password" value="<?=$password?>"></td>
				<td>Aktivan:<br/> <input type="checkbox" size="10" name="ima_auth" value="1" <? if ($pristup==1) print "CHECKED"; ?>></td>
				<td><input type="Submit" value=" Izmijeni "></td>
			</tr></table></form>
			<?
		}
	}

	else if ($conf_system_auth == "ldap") {
		?>
		
		<script language="JavaScript">
		function upozorenje(pristup) {
			document.authforma.pristup.value=pristup;
			document.authforma.submit();
		}
		</script>
		<?=genform("POST", "authforma")?>
		<input type="hidden" name="subakcija" value="auth">
		<input type="hidden" name="pristup" value="">
		</form>

		<table border="0">
		<tr>
			<td colspan="5">Korisnički pristup: <input type="checkbox" name="ima_auth" onchange="javascript:upozorenje('<?=$pristup?>');" <? if ($pristup==1) print "CHECKED"; ?>></td>
		</tr></table></form>
		<?
	}


	// Uloge korisnika
	$korisnik_student=$korisnik_nastavnik=$korisnik_prijemni=$korisnik_studentska=$korisnik_siteadmin=0;
	print "<p>Tip korisnika: ";
	$q209 = myquery("select privilegija from privilegije where osoba=$osoba");

	while ($r209 = mysql_fetch_row($q209)) {
		if ($r209[0]=="student") {
			print "<b>student,</b> ";
			$korisnik_student=1;
		}
		if ($r209[0]=="nastavnik") {
			print "<b>nastavnik,</b> ";
			$korisnik_nastavnik=1;
		}
		if ($r209[0]=="prijemni") {
			print "<b>kandidat na prijemnom ispitu,</b> ";
			$korisnik_prijemni=1;
		}
		if ($r209[0]=="studentska") {
			print "<b>uposlenik studentske službe,</b> ";
			$korisnik_studentska=1;
		}
		if ($r209[0]=="siteadmin") {
			print "<b>administrator,</b> ";
			$korisnik_siteadmin=1;
		}
	}
	print "</p>\n";


	// Admin dio

	if ($user_siteadmin) {
		?>
		<?=genform("POST")?>
		<input type="hidden" name="subakcija" value="uloga">
		<input type="checkbox" name="student" value="1" <?if($korisnik_student==1) print "CHECKED";?>> Student&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="checkbox" name="nastavnik" value="1" <?if($korisnik_nastavnik==1) print "CHECKED";?>> nastavnik&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="checkbox" name="prijemni" value="1" <?if($korisnik_prijemni==1) print "CHECKED";?>> prijemni&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="checkbox" name="studentska" value="1" <?if($korisnik_studentska==1) print "CHECKED";?>> studentska&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="checkbox" name="siteadmin" value="1" <?if($korisnik_siteadmin==1) print "CHECKED";?>> siteadmin<br/>
		<input type="submit" value=" Promijeni ">
		</form>
		<?
	}


	// STUDENT

	if ($korisnik_student) {
		?>
		<hr>
		<h3>STUDENT</h3>
		<?

		// Trenutno upisan na semestar:
		$q220 = myquery("select s.naziv,ss.semestar,ss.akademska_godina,ag.naziv, s.id, ts.trajanje from student_studij as ss, studij as s, akademska_godina as ag, tipstudija as ts where ss.student=$osoba and ss.studij=s.id and ag.id=ss.akademska_godina and s.tipstudija=ts.id order by ag.naziv desc");
		$studij="0";
		$studij_id=$semestar=0;
		$puta=1;

		// Da li je ikada slusao nesto?
		$ikad_studij=$ikad_studij_id=$ikad_semestar=$ikad_ak_god=0;
	
		while ($r220=mysql_fetch_row($q220)) {
			if ($r220[2]==$id_ak_god && $r220[1]>$semestar) { //trenutna akademska godina
				$studij=$r220[0];
				$semestar = $r220[1];
				$studij_id=$r220[4];
				$studij_trajanje=$r220[5];
			}
			else if ($r220[0]==$studij && $r220[1]==$semestar) { // ponovljeni semestri
				$puta++;
			} else if ($r220[2]>$ikad_ak_god || ($r220[2]==$ikad_ak_god && $r220[1]>$ikad_semestar)) {
				$ikad_studij=$r220[0];
				$ikad_semestar=$r220[1];
				$ikad_ak_god=$r220[2];
				$ikad_ak_god_naziv=$r220[3];
				$ikad_studij_id=$r220[4];
				$ikad_studij_trajanje=$r220[5];
			}
		}


		// Izvjestaji
		
		?>
		<div style="float:left; margin-right:10px">
			<table width="100" border="1" cellspacing="0" cellpadding="0">
				<tr><td bgcolor="#777777" align="center">
					<font color="white"><b>IZVJEŠTAJI:</b></font>
				</td></tr>
				<tr><td align="center"><a href="?sta=izvjestaj/historija&student=<?=$osoba?>">
				<img src="images/32x32/izvjestaj.png" border="0"><br/>Historija</a></td></tr>
				<tr><td align="center"><a href="?sta=izvjestaj/index&student=<?=$osoba?>">
				<img src="images/32x32/izvjestaj.png" border="0"><br/>Indeks</a></td></tr>
				<tr><td align="center"><a href="?sta=izvjestaj/progress&student=<?=$osoba?>&razdvoji_ispite=0">
				<img src="images/32x32/izvjestaj.png" border="0"><br/>Bodovi</a></td></tr>
				<tr><td align="center"><a href="?sta=izvjestaj/progress&student=<?=$osoba?>&razdvoji_ispite=1">
				<img src="images/32x32/izvjestaj.png" border="0"><br/>Bodovi + nepoloženi ispiti</a></td></tr>
			</table>
		</div>
		<?


		// Trenutno slusa studij 

		$nova_ak_god=0;

		?>
		<p align="left">Trenutno (<b><?=$naziv_ak_god?></b>) upisan/a na:<br/>
		<?
		if ($studij=="0") {
			?>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nije upisan/a niti u jedan semestar!</p>
			<?

			// Proglasavamo zadnju akademsku godinu koju je slusao za tekucu
			// a tekucu za novu
			$nova_ak_god = $id_ak_god;
			$naziv_nove_ak_god = $naziv_ak_god;
			if ($ikad_semestar != 0) {
				// Ako je covjek upisan u buducu godinu, onda je u toku upis
				if ($ikad_ak_god>$id_ak_god) {
					$nova_ak_god=$ikad_ak_god;
					$naziv_nove_ak_god=$ikad_ak_god_naziv;
					$semestar=$ikad_semestar-1; // da se ne bi ispisivalo da drugi put sluša
				} else {
					$id_ak_god = $ikad_ak_god;
					$naziv_ak_god = $ikad_ak_god_naziv;
					$semestar = $ikad_semestar;
				}
				// Zelimo da se provjeri ECTS:
				$studij = $ikad_studij;
				$studij_id = $ikad_studij_id;
				$studij_trajanje = $ikad_studij_trajanje;

			}

		} else {
			?>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>&quot;<?=$studij?>&quot;</b>, <?=$semestar?>. semestar (<?=$puta?>. put)  (<a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=ispis&studij=<?=$studij_id?>&semestar=<?=$semestar?>&godina=<?=$id_ak_god?>">ispiši sa studija</a>)</p>
			<?
			$q230 = myquery("select id, naziv from akademska_godina where id=$id_ak_god+1");
			if (mysql_num_rows($q230)>0) {
				$nova_ak_god = mysql_result($q230,0,0);
				$naziv_nove_ak_god = mysql_result($q230,0,1);
			}
		}



		// UPIS U SLJEDEĆU AK. GODINU

		// Aktivni moduli
		$modul_uou=$modul_kolizija=0;
		foreach ($registry as $r) {
			if ($r[0]=="student/ugovoroucenju") $modul_uou=1;
			if ($r[0]=="student/kolizija") $modul_kolizija=1;
		}


		if ($nova_ak_god!=0) { // Ne prikazuj podatke o upisu dok se ne kreira nova ak. godina


		?>
		<p>Upis u akademsku <b><?=$naziv_nove_ak_god?></b> godinu:<br />
		<?


		// Da li je vec upisan?
		$q235 = myquery("select s.naziv, ss.semestar, s.id from student_studij as ss, studij as s where ss.student=$osoba and ss.studij=s.id and ss.akademska_godina=$nova_ak_god order by ss.semestar desc");
		if (mysql_num_rows($q235)>0) {
			$novi_studij=mysql_result($q235,0,0);
			$novi_semestar=mysql_result($q235,0,1);
			$novi_studij_id=mysql_result($q235,0,2);
			if ($novi_semestar<=$semestar && $novi_studij==$studij) $nputa=$puta+1; else $nputa=1;
			?>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Student je upisan na studij: <b><?=$novi_studij?></b>, <?=$novi_semestar?>. semestar (<?=$nputa?>. put). (<a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=ispis&studij=<?=$novi_studij_id?>&semestar=<?=$novi_semestar?>&godina=<?=$nova_ak_god?>">ispiši sa studija</a>)</p><?

		} else {

		// Ima li uslove za upis
		if ($semestar==0 && $ikad_semestar==0) {
			// Upis na prvu godinu

			?>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nemamo podataka da je ovaj student ikada bio upisan na fakultet.</p>
			<p><a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=upis&studij=&semestar=1&godina=<?=$nova_ak_god?>">Upiši studenta na Prvu godinu studija, 1. semestar.</a></p>
			<?

		} else if ($studij=="0") {
			if ($ikad_semestar%2==0) $ikad_semestar--;
			// Trenutno nije upisan na fakultet, ali upisacemo ga
			?>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=upis&studij=<?=$ikad_studij_id?>&semestar=<?=$ikad_semestar?>&godina=<?=$nova_ak_god?>">Ponovo upiši studenta na <?=$ikad_studij?>, <?=$ikad_semestar?>. semestar.</a></p>
			<?

		} else if ($semestar%2!=0) {
			// S neparnog na parni ide automatski
			?>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Student je stekao uslove za upis na &quot;<?=$studij?>&quot;, <?=($semestar+1)?> semestar</p>
			<p><a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=upis&studij=<?=$studij_id?>&semestar=<?=($semestar+1)?>&godina=<?=$nova_ak_god?>">Upiši studenta na &quot;<?=$studij?>&quot;, <?=($semestar+1)?> semestar.</a></p>
			<?

		} else {
			// Upis na neparni semestar - da li je student dao uslov?
			$ima_uslov=0;
			
			// Tekst za ono što upisuje
			if ($semestar==$studij_trajanje) {
				$sta = "sljedeći ciklus studija";
			} else {
				$sta = "&quot;$studij&quot;, ".($semestar+1).". semestar";
			}


			// Pokusacemo odrediti uslov na osnovu polozenih predmeta...

			// Od predmeta koje je slušao, koliko je pao?
			$q250 = myquery("select distinct pk.predmet, p.ects, pk.semestar, pk.obavezan, p.naziv from ponudakursa as pk, student_predmet as sp, predmet as p where sp.student=$osoba and sp.predmet=pk.id and pk.semestar<=$semestar and pk.studij=$studij_id and pk.predmet=p.id order by pk.semestar");
			$ects_pao=$predmeti_pao=$izborni_pao=$nize_godine=$ects_polozio=0;
			while ($r250 = mysql_fetch_row($q250)) {
				$q260 = myquery("select count(*) from konacna_ocjena where student=$osoba and predmet=$r250[0]");
				if (mysql_result($q260,0,0)<1) { 
					if ($r250[3]==1) { // Obavezni predmeti se ne smiju pasti!
						$ects_pao+=$r250[1];

						$predmeti_pao++;
						if ($r250[2]<$semestar-1) $nize_godine++;
					} else {
						$izborni_pao++; // Za izborne cemo uporediti sumu ECTSova kasnije
					}
				} else
					$ects_polozio += $r250[1];
			}

			// USLOV ZA UPIS
			// Prema aktuelnom zakonu može se prenijeti tačno jedan predmet, bez obzira na ECTS
			// No za sljedeći ciklus studija se ne može prenijeti ništa
			if ($semestar==$studij_trajanje && $predmeti_pao==0 && ($ects_pao+$ects_polozio)%60==0) {
				// Ako je student pao izborni predmet pa polozio drugi umjesto njega, vazice
				// ($ects_pao+$ects_polozio)%60==0
				// Zato sto ects_pao = obavezni predmeti tako da ostaju samo izborni predmeti
				$ima_uslov=1;

			} else if ($semestar<$studij_trajanje && $predmeti_pao<=1) {
				// Provjeravamo broj nepolozenih izbornih predmeta i razliku ects-ova
				if ($predmeti_pao==0 && ($ects_pao+$ects_polozio)%60<7) { // nema izbornog predmeta sa 7 ili više kredita
					$ima_uslov=1;
				} else if ($predmeti_pao==1 && ($ects_pao+$ects_polozio)%60==0) {
					$ima_uslov=1;
				} else {
					if ($predmeti_pao==1) $objasnjenje="nepoložen jedan obavezan i jedan ili više izborni predmet";
					else $objasnjenje="nepoloženo dva ili više izbornih predmeta";
					$objasnjenje .= ", nedostaje ".(60-$ects_polozio%60)." ECTS kredita";
				}

			} else {
				$objasnjenje=($predmeti_pao+$izborni_pao)." nepoloženih predmeta, nedostaje ".(60-$ects_polozio%60)." ECTS kredita";
			}


			// Konačan ispis
			if ($ima_uslov) {
				?>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Student je stekao/la uslove za upis na <?=$sta?></p>
				<p><a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=upis&studij=<?=$studij_id?>&semestar=<?=($semestar+1)?>&godina=<?=$nova_ak_god?>">Upiši studenta na <?=$sta?>.</a></p>
				<?
			} else {
				?>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Student <b>NIJE</b> stekao/la uslove za <?=$sta?><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(<?=$objasnjenje?>)</p>
				<p><a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=upis&studij=<?=$studij_id?>&semestar=<?=($semestar-1)?>&godina=<?=$nova_ak_god?>">Ponovo upiši studenta na <?=$studij?>, <?=($semestar-1)?>. semestar (<?=($puta+1)?>. put).</a></p>
				<p><a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=upis&studij=<?=$studij_id?>&semestar=<?=($semestar+1)?>&godina=<?=$nova_ak_god?>">Upiši studenta na <?=$sta?>.</a></p>
				<?
			}
		}

		} // if ($q235... else ... -- nije vec upisan nigdje

			// Ugovor o učenju
			if ($modul_uou==1) {
				$q270 = myquery("select s.naziv, u.semestar from ugovoroucenju as u, studij as s where u.student=$osoba and u.akademska_godina=$nova_ak_god and u.studij=s.id order by u.semestar");
				if (mysql_num_rows($q270)>0) {
					$nazivstudijauu=$semestaruu="";
					while ($r270 = mysql_fetch_row($q270)) {
						$nazivstudijauu=$r270[0];
						$semestaruu.=$r270[1].". ";
					}
					?>
					<p>Student je popunio/la <b>Ugovor o učenju</b> za <?=$nazivstudijauu?>, <?=$semestaruu?>semestar</p>
					<?
				} else {
					?>
					<p>Student NIJE popunio/la <b>Ugovor o učenju</b> za sljedeću akademsku godinu.</p>
					<?
				}
			}

		} // if (mysql_num_rows($q230  -- da li postoji ak. god. iza aktuelne?


		// Kolizija
		if ($modul_kolizija==1) {
			$q280 = myquery("select count(*) from kolizija where student=$osoba and akademska_godina=$nova_ak_god");
			if (mysql_result($q280,0,0)>0) {
				?>
				<p>Student je popunio/la <b>Zahtjev za koliziju</b>. <a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=kolizija&godina=<?=$nova_ak_god?>">Kliknite ovdje da potvrdite upis na kolizione predmete.</a>
				<?
			} else {
				// Probavamo i za trenutnu
				$q280 = myquery("select count(*) from kolizija where student=$osoba and akademska_godina=$id_ak_god");
				if (mysql_result($q280,0,0)>0) {
					?>
					<p>Student je popunio/la <b>Zahtjev za koliziju</b>. <a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=kolizija&godina=<?=$id_ak_god?>">Kliknite ovdje da potvrdite upis na kolizione predmete.</a>
					<?
				}
			}
		}


		// Upis studenta na predmet
		if ($nova_ak_god==0) { 
			// Ovaj uslov će važiti samo ako je student trenutno upisan na fakultet, 
			// a novi upis nije trenutno u toku
			?>
			<p><a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=predmeti">Manuelni upis studenta na predmete / ispis sa predmeta.</a></p>
			<?
		}

		print "\n<div style=\"clear:both\"></div>\n";
	} // STUDENT



	// NASTAVNIK

	if ($korisnik_nastavnik) {
		?>
		<br/><hr>
		<h3>NASTAVNIK</h3>
		<p>Angažovan/a na predmetima (akademska godina <b><?=$naziv_ak_god?></b>):</p>
		<ul>
		<?
		$q180 = myquery("select p.id, p.naziv, np.admin, s.kratkinaziv from nastavnik_predmet as np, predmet as p, ponudakursa as pk, studij as s where np.nastavnik=$osoba and np.predmet=pk.predmet and np.akademska_godina=$id_ak_god and pk.akademska_godina=$id_ak_god and pk.predmet=p.id and pk.studij=s.id"); // FIXME: moze li se ovdje izbaciti tabela ponudakursa? studij ili institucija?
		if (mysql_num_rows($q180) < 1)
			print "<li>Nijedan</li>\n";
		while ($r180 = mysql_fetch_row($q180)) {
			print "<li><a href=\"?sta=studentska/predmeti&akcija=edit&predmet=$r180[0]\">$r180[1] ($r180[3])</a>";
			if ($r180[2] == 1) print " (Administrator predmeta)";
			print "</li>\n";
		}
		?></ul>
		<p>Za prethodne akademske godine, koristite pretragu na kartici &quot;Predmeti&quot;<br/></p>
	
		<?

		// Angažman na predmetu
	
		?><p>Angažuj nastavnika na:
		<?=genform("POST")?>
		<input type="hidden" name="subakcija" value="angazuj">
		<select name="predmet" class="default"><?
		$q190 = myquery("select pk.id, p.naziv, s.kratkinaziv from predmet as p, ponudakursa as pk, studij as s where pk.predmet=p.id and pk.akademska_godina=$id_ak_god and pk.studij=s.id order by p.naziv");
		while ($r190 = mysql_fetch_row($q190)) {
			print "<option value=\"$r190[0]\">$r190[1] ($r190[2])</a>\n";
		}
		?></select>&nbsp;
		<input type="submit" value=" Dodaj "></form></p>
		<?
	}





	// PRIJEMNI

	$q600 = myquery("select prijemni_termin, broj_dosjea, redovan, studij_prvi, studij_drugi, studij_treci, studij_cetvrti, izasao, rezultat from prijemni_prijava where osoba=$osoba");
	if (!$korisnik_student && !$korisnik_nastavnik && mysql_num_rows($q600)>0) {
		?>
		<br/><hr>
		<h3>KANDIDAT NA PRIJEMNOM ISPITU</h3>
		<?
		while ($r600 = mysql_fetch_row($q600)) {
			$q610 = myquery("select ag.id, ag.naziv, UNIX_TIMESTAMP(pt.datum), pt.ciklus_studija from prijemni_termin as pt, akademska_godina as ag where pt.id=$r600[0] and pt.akademska_godina=ag.id");
			?>
			<b>Za akademsku <?=mysql_result($q610,0,1)?> godinu (<?=mysql_result($q610,0,3)?>. ciklus studija), održan <?=date("d. m. Y", mysql_result($q610,0,2))?></b>
			<ul><li><?
				if ($r600[7]>0) print "$r600[8] bodova"; else print "(nije izašao/la)";
			?></li>
			<li>Broj dosjea: <?=$r600[1]?>, <?
			if ($r600[2]==1) print "redovan"; else print "paralelan";
			for ($i=3; $i<=6; $i++) {
				if ($r600[$i]>0) {
					$q620 = myquery("select kratkinaziv from studij where id=".$r600[$i]);
					print ", ".mysql_result($q620,0,0);
				}
			}
			?></li>
			<?

			// Link na upis prikazujemo samo za ovogodišnji prijemni
			$godina_prijemnog = mysql_result($q610,0,0);
//			$q630 = myquery("select id from akademska_godina where aktuelna=1");
//			$nova_ak_god = mysql_result($q630,0,0)+1;

//			if ($godina_prijemnog==$nova_ak_god) {
				?>
				<li><a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=upis&studij=<?=$r600[3]?>&semestar=1&godina=<?=$godina_prijemnog?>">Upiši kandidata na &quot;<?
				$q630 = myquery("select naziv from studij where id=$r600[3]");
				print mysql_result($q630,0,0);
				?>&quot;, 1. semestar, u akademskoj <?=mysql_result($q610,0,1)?> godini</a></li>
			<?
//			}
			?>
			</ul><?
		}

		$q640 = myquery("select ss.naziv, us.opci_uspjeh, us.kljucni_predmeti, us.dodatni_bodovi, us.ucenik_generacije from srednja_skola as ss, uspjeh_u_srednjoj as us where us.srednja_skola=ss.id and us.osoba=$osoba");

		?>
		<b>Uspjeh u srednjoj školi:</b>
		<ul>
		<li>Škola: <?=mysql_result($q640,0,0)?></li>
		<li>Opći uspjeh: <?=mysql_result($q640,0,1)?>. Ključni predmeti: <?=mysql_result($q640,0,2)?>. Dodatni bodovi: <?=mysql_result($q640,0,3)?>. <?
		if (mysql_result($q640,0,4)>0) print "Učenik generacije.";
		?></li>
		</ul>
		<?
		
	}

	?></td></tr></table></center><? // Vanjska tabela

}



// Spisak osoba

else {
	$src = my_escape($_REQUEST["search"]);
	$limit = 20;
	$offset = intval($_REQUEST["offset"]);

	?>
	<p><h3>Studentska služba - Studenti i nastavnici</h3></p>

	<table width="500" border="0"><tr><td align="left">
		<p><b>Pretraži osobe:</b><br/>
		Unesite dio imena i prezimena ili broj indeksa<br/>
		<?=genform("GET")?>
		<input type="hidden" name="offset" value="0"> <?/*resetujem offset*/?>
		<input type="text" size="50" name="search" value="<? if ($src!="sve") print $src?>"> <input type="Submit" value=" Pretraži "></form>
		<a href="<?=genuri()?>&search=sve">Prikaži sve osobe</a><br/><br/>
	<?
	if ($src) {
		$rezultata=0;
		if ($src == "sve") {
			$q100 = myquery("select count(*) from osoba");
			$q101 = myquery("select id,ime,prezime,brindexa from osoba order by prezime,ime limit $offset,$limit");
			$rezultata = mysql_result($q100,0,0);
		} else {
			$src = preg_replace("/\s+/"," ",$src);
			$src=trim($src);
			$dijelovi = explode(" ", $src);
			$query = "";

			// Probavamo traziti ime i prezime istovremeno
			if (count($dijelovi)==2) {
				$q100 = myquery("select count(*) from osoba where ime like '%$dijelovi[0]%' and prezime like '%$dijelovi[1]%'");
				$q101 = myquery("select id,ime,prezime,brindexa from osoba where ime like '%$dijelovi[0]%' and prezime like '%$dijelovi[1]%' order by prezime,ime limit $offset,$limit");
				if (mysql_result($q100,0,0)==0) {
					$q100 = myquery("select count(*) from osoba where ime like '%$dijelovi[1]%' and prezime like '%$dijelovi[0]%'");
					$q101 = myquery("select id,ime,prezime,brindexa from osoba where ime like '%$dijelovi[1]%' and prezime like '%$dijelovi[0]%' order by prezime,ime limit $offset,$limit");
				}
				$rezultata = mysql_result($q100,0,0);
			}

			// Nismo nasli ime i prezime, pokusavamo bilo koji dio
			if ($rezultata==0) {
				foreach($dijelovi as $dio) {
					if ($query != "") $query .= "or ";
					$query .= "ime like '%$dio%' or prezime like '%$dio%' or brindexa like '%$dio%' ";
					if (intval($dio)>0) $query .= "or id=".intval($dio)." ";
				}
				$q100 = myquery("select count(*) from osoba where ($query)");
				$q101 = myquery("select id,ime,prezime,brindexa from osoba where ($query) order by prezime,ime limit $offset,$limit");
				$rezultata = mysql_result($q100,0,0);
			}

			// Nismo nasli nista, pokusavamo login
			if ($rezultata==0) {
				$query="";
				foreach($dijelovi as $dio) {
					if ($query != "") $query .= "or ";
					$query .= "a.login like '%$dio%' ";
				}
				$q100 = myquery("select count(*) from osoba as o, auth as a where ($query) and a.id=o.id");
				$q101 = myquery("select o.id,o.ime,o.prezime,o.brindexa from osoba as o, auth as a where ($query) and a.id=o.id order by o.prezime,o.ime limit $offset,$limit");
				$rezultata = mysql_result($q100,0,0);
			}

		}

		if ($rezultata == 0)
			print "Nema rezultata!";
		else if ($rezultata>$limit) {
			print "Prikazujem rezultate ".($offset+1)."-".($offset+20)." od $rezultata. Stranica: ";

			for ($i=0; $i<$rezultata; $i+=$limit) {
				$br = intval($i/$limit)+1;
				if ($i==$offset)
					print "<b>$br</b> ";
				else
					print "<a href=\"".genuri()."&offset=$i\">$br</a> ";
			}
			print "<br/>";
		}
//		else
//			print "$rezultata rezultata:";

		print "<br/>";

		print '<table width="100%" border="0">';
		$i=$offset+1;
		while ($r101 = mysql_fetch_row($q101)) {
			print "<tr ";
			if ($i%2==0) print "bgcolor=\"#EEEEEE\"";
			print "><td>$i. $r101[2] $r101[1]";
			if (intval($r101[3])>0) print " ($r101[3])";
			print "</td><td><a href=\"".genuri()."&akcija=edit&osoba=$r101[0]\">Detalji</a></td></tr>";
			$i++;
		}
		print "</table>";
	}

	?>
		<br/>
		<?=genform("POST")?>
		<input type="hidden" name="akcija" value="novi">
		<b>Unesite novu osobu:</b><br/>
		<table border="0" cellspacing="0" cellpadding="0" width="100%">
		<tr><td>Ime<? if ($conf_system_auth == "ldap") print " ili login"?>:</td><td>Prezime:</td><td>&nbsp;</td></tr>
		<tr>
			<td><input type="text" name="ime" size="15"></td>
			<td><input type="text" name="prezime" size="15"></td>
			<td><input type="submit" value=" Dodaj "></td>
		</tr></table>
		</form>
	<?
	?>

	</td></tr></table>
	<?
}


?>
</td></tr></table></center>
<?


}

