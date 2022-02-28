<?

// STUDENTSKA/OSOBE - administracija studenata, studentska služba



function studentska_osobe() {

	global $user_siteadmin, $user_studentska;
	global $conf_system_auth, $conf_ldap_search, $conf_ldap_server, $conf_ldap_dn, $conf_ldap_domain;
	global $conf_knjigovodstveni_servis;
	global $registry; // šta je od modula aktivno
	
	global $_lv_; // Potrebno za genform()
	
	require_once("lib/student_predmet.php");
	require_once("lib/student_studij.php"); // Za ima_li_uslov
	require_once("lib/formgen.php"); // datectrl, db_dropdown
	
	
	// Provjera privilegija
	if (!$user_siteadmin && !$user_studentska) { // 2 = studentska, 3 = admin
		zamgerlog("korisnik nije studentska",3);
		zamgerlog2("nije studentska");
		biguglyerror("Pristup nije dozvoljen.");
		return;
	}
	
	
	
	?>
	
	<center>
	<table border="0"><tr><td>
	
	<?
	
	$akcija = param('akcija');
	$osoba = int_param('osoba');
	
	
	// Dodavanje nove osobe u bazu
	
	if ($akcija == "nova" && check_csrf_token()) {
		require_once("studentska/osobe/nova.php");
		studentska_osobe_nova();
	}


	// Izmjena ličnih podataka osobe
	
	if ($akcija == "podaci") {
		?>
		<p><a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=edit">Nazad na podatke o osobi</a></p>
		<?
		require_once("studentska/osobe/podaci.php");
		studentska_osobe_podaci();
	}


	// Upis studenta na semestar
	
	else if ($akcija == "upis") {
		?>
		<p><a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=edit">Nazad na podatke o studentu</a></p>
		<?
		require_once("studentska/osobe/upis.php");
		studentska_osobe_upis();
	}

	
	// Ispis sa studija
	
	else if ($akcija == "ispis") {
		require_once("studentska/osobe/ispis.php");
		studentska_osobe_ispis();
	}


	// Promjena načina studiranja
	
	else if ($akcija == "promijeni_nacin") {
		?>
		<a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=edit">Nazad na podatke o studentu</a><br/><br/>
		<?

		require_once("studentska/osobe/promijeni_nacin.php");
		studentska_osobe_promijeni_nacin();
	}

	
	// Pregled predmeta za koliziju i potvrda
	
	else if ($akcija == "kolizija") {
		?>
		<a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=edit">Nazad na podatke o studentu</a><br/><br/>
		<?
		
		require_once("studentska/osobe/kolizija.php");
		studentska_osobe_kolizija();
	}


	
	// Manuelni upis/ispis na predmete
	
	else if ($akcija == "predmeti") {
		?>
		<a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=edit">Nazad na podatke o studentu</a><br/><br/>
		<?

		require_once("studentska/osobe/predmeti.php");
		studentska_osobe_predmeti();
	}

	
	// Izbori za nastavnike
	
	else if ($akcija == "izbori") {
		?>
		<p><a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=edit">Nazad na podatke o osobi</a></p>
		<?
		require_once("studentska/osobe/izbori.php");
		studentska_osobe_izbori();
	}

	
	// Analitička kartica studenta
	
	else if ($akcija == "kartica") {
		?>
		<a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=edit">Nazad na podatke o studentu</a><br/><br/>
		<?
		require_once("studentska/osobe/kartica.php");
		studentska_osobe_kartica();
	}


	// Ažuriranje dugovanja studenta
	
	else if ($akcija == "izmijeni_zaduzenje") {
		?>
		<a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=edit">Nazad na podatke o studentu</a><br/><br/>
		<?
		require_once("studentska/osobe/zaduzenje.php");
		studentska_osobe_zaduzenje();
		return;
	}

	
	// Pregled informacija o osobi
	
	else if ($akcija == "edit") {
		$pretraga = db_escape(param('search'));
		$ofset = int_param('offset');
	
		?><a href="?sta=studentska/osobe&search=<?=$pretraga?>&offset=<?=$ofset?>">Nazad na rezultate pretrage</a><br/><br/><?
		
	
		// Prvo odredjujemo aktuelnu akademsku godinu - ovaj upit se dosta koristi kasnije
		$q210 = db_query("select id,naziv from akademska_godina where aktuelna=1 order by id desc");
		if (db_num_rows($q210)<1) {
			// Nijedna godina nije aktuelna - ali mora postojati barem jedna u bazi
			$q210 = db_query("select id,naziv from akademska_godina order by id desc");
		}
		$id_ak_god = db_result($q210,0,0);
		$naziv_ak_god = db_result($q210,0,1);
		// Posto se id_ak_god moze promijeniti.... CLEANUP!!!
		$orig_iag = $id_ak_god;
	
	
	
		// ======= SUBMIT AKCIJE =========
	
	
		// Promjena korisničkog pristupa i pristupnih podataka
		if (param('subakcija') == "auth" && check_csrf_token()) {
			$login = db_escape(trim($_REQUEST['login']));
			$login_ldap = zamger_ldap_escape(trim($_REQUEST['login']));
			$stari_login = db_escape($_REQUEST['stari_login']);
			$password = db_escape($_REQUEST['password']);
			$aktivan = intval($_REQUEST['aktivan']);
	
			if ($login=="") {
				niceerror("Ne možete postaviti prazan login");
			}
			else if ($stari_login=="") {
				// Provjeravamo LDAP?
				if ($conf_ldap_search) do { // Simuliramo GOTO...
					// Tražimo ovaj login na LDAPu...
					$ds = ldap_connect($conf_ldap_server);
					ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
					if (!ldap_bind($ds)) {
						zamgerlog("Ne mogu se spojiti na LDAP server",3); // 3 - greska
						zamgerlog2("ne mogu se spojiti na LDAP server");
						niceerror("Ne mogu se spojiti na LDAP server - nastavljam dalje bez provjere");
						break;
					}
	
					$sr = ldap_search($ds, $conf_ldap_dn, "uid=$login_ldap", array() /* just dn */ );
					if (!$sr) {
						zamgerlog("ldap_search() nije uspio.",3);
						zamgerlog2("ldap_search() nije uspio.");
						niceerror("ldap_search() nije uspio - nastavljam dalje bez provjere");
						break;
					}
					$results = ldap_get_entries($ds, $sr);
					if ($results['count'] > 0) {
						nicemessage("Login '$login' pronađen na LDAP serveru");
						break;
					}
	
					// Pokušavamo mail alias
					$sr = ldap_search($ds, $conf_ldap_dn, "mail=$login_ldap$conf_ldap_domain", array() );
					if (!$sr) {
						zamgerlog("ldap_search() 2 nije uspio.",3);
						zamgerlog2("ldap_search() nije uspio.");
						niceerror("ldap_search() nije uspio - nastavljam dalje bez provjere");
						break;
					}
					$results = ldap_get_entries($ds, $sr); // pretpostavka je da će druga pretraga raditi
					if ($results['count'] > 0) {
						nicemessage("Email '$login$conf_ldap_domain' pronađen na LDAP serveru");
					} else {
						zamgerlog("login ne postoji na LDAPu ($login)",3);
						zamgerlog2("login ne postoji na LDAPu", 0, 0, 0, $login);
						niceerror("Predloženi login ($login) nije pronađen na LDAP serveru!");
						print "<p>Nastaviću dalje sa dodavanjem logina, ali korisnik vjerovatno neće moći pristupiti Zamgeru.</p>";
					}
				} while (false);
	
				// Dodavanje novog logina
				$q120 = db_query("insert into auth set id=$osoba, login='$login', password='$password', aktivan=$aktivan");
				nicemessage("Uspješno kreiran novi login za korisnika");
				zamgerlog("dodan novi login '$login' za korisnika u$osoba", 4);
				zamgerlog2("dodan novi login za korisnika", $osoba, 0, 0, $login);
	
			} else {
				// Izmjena starog logina
				$q123 = db_query("select count(*) from auth where id=$osoba and login='$stari_login'");
				if (db_result($q123,0,0)<1) {
					niceerror("Nije pronađen login... molimo pokušajte ponovo");
					zamgerlog("nije pronadjen stari login '$stari_login' za korisnika u$osoba", 3);
					zamgerlog2("nije pronadjen stari login za korisnika", $osoba);
				} else {
					if ($_REQUEST['brisanje']==" Obriši ") {
						$q125 = db_query("delete from auth where id=$osoba and login='$stari_login'");
						nicemessage("Uspješno obrisan login '$stari_login'");
						zamgerlog("obrisan login '$stari_login' za korisnika u$osoba", 4);
						zamgerlog2("obrisan login za korisnika", $osoba, 0, 0, $stari_login);
	
					} else {
						$q127 = db_query("update auth set login='$login', password='$password', aktivan=$aktivan where id=$osoba and login='$stari_login'");
						nicemessage("Uspješno izmijenjen login '$login'");
						zamgerlog("izmijenjen login '$stari_login' u '$login' za korisnika u$osoba", 4);
						zamgerlog2("izmijenjen login za korisnika", $osoba, 0, 0, $login);
					}
				}
			}
	
	
		} // if ($_REQUEST['subakcija'] == "auth")
	
	
		// Pojednostavljena promjena podataka za studentsku službu u slučaju korištenja LDAPa
		if (param('subakcija') == "auth_ldap" && check_csrf_token()) {
			$aktivan = intval($_REQUEST['aktivan']);
	
			// Postoji li zapis u tabeli auth?
			$q103 = db_query("select count(*) from auth where id=$osoba");
			if (db_result($q103,0,0)>0) { // Da!
				// Ako isključujemo pristup, stavljamo aktivan na 0
				if ($aktivan!=0) {
					$q105 = db_query("update auth set aktivan=0 where id=$osoba");
					zamgerlog("ukinut login za korisnika u$osoba (ldap)",4);
					zamgerlog2("ukinut login za korisnika (ldap)", $osoba );
				} else {
					$q105 = db_query("update auth set aktivan=1 where id=$osoba");
					zamgerlog("aktiviran login za korisnika u$osoba (ldap)",4);
					zamgerlog2("aktiviran login za korisnika (ldap)", $osoba );
				}
	
			} else if ($aktivan!=0) { // Nema zapisa u tabeli auth
				// TODO: smanjiti duplikaciju koda za LDAP!
				
				// Ako je izabrano isključenje pristupa, ne radimo nista
				// (ne bi se smjelo desiti)
				// U suprotnom kreiramo login
	
				// predloženi login
				$suggest_login = gen_ldap_uid($osoba);
		
				// Tražimo ovaj login na LDAPu...
				$ds = ldap_connect($conf_ldap_server);
				ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
				if (!ldap_bind($ds)) {
					zamgerlog("Ne mogu se spojiti na LDAP server",3); // 3 - greska
					zamgerlog2("ne mogu se spojiti na LDAP server");
					niceerror("Ne mogu se spojiti na LDAP server");
					return;
				}
		
				$sr = ldap_search($ds, $conf_ldap_dn, "uid=$suggest_login", array() /* just dn */ );
				if (!$sr) {
					zamgerlog("ldap_search() nije uspio.",3);
					zamgerlog2("ldap_search() nije uspio.");
					niceerror("ldap_search() nije uspio.");
					return;
				}
				$results = ldap_get_entries($ds, $sr);
				if ($results['count'] < 1) {
					zamgerlog("login ne postoji na LDAPu ($suggest_login)",3);
					zamgerlog2("login ne postoji na LDAPu", 0, 0, 0, $suggest_login);
					niceerror("Predloženi login ($suggest_login) nije pronađen na LDAP serveru!");
					print "<p>Da li ste uspravno unijeli broj indeksa, ime i prezime? Ako jeste, kontaktirajte administratora!</p>";
		
					// Nastavljamo dalje sa edit akcijom kako bi studentska mogla popraviti podatke
		
				} else {
					// Dodajemo login, ako nije podešen
					$q110 = db_query("select login, aktivan from auth where id=$osoba");
					if (db_num_rows($q110)==0) {
						$q111 = db_query("insert into auth set id=$osoba, login='$suggest_login', aktivan=1");
						zamgerlog("kreiran login za korisnika u$osoba (ldap - upis u tabelu)",4);
						zamgerlog2("kreiran login za korisnika (ldap - upis u tabelu)", $osoba);
					}
					else {
						if (db_result($q110,0,0) == "") {
							$q112 = db_query("update auth set login='$suggest_login' where id=$osoba");
							zamgerlog("kreiran login za korisnika u$osoba (ldap - postavljeno polje login)",4);
							zamgerlog2("kreiran login za korisnika (ldap - postavljeno polje login)", $osoba );
						}
						if (db_result($q110,0,1)==0) {
							$q113 = db_query("update auth set aktivan=1 where id=$osoba");
							zamgerlog("kreiran login za korisnika u$osoba (ldap - aktivan=1)",4);
							zamgerlog2("kreiran login za korisnika (ldap - aktivan=1)", $osoba);
						}
					}
		
					// Generišemo email adresu ako nije podešena
					$email_adresa = $suggest_login.$conf_ldap_domain;
					$q115 = db_query("select sistemska from email where osoba=$osoba and adresa='$email_adresa'");
					if (db_num_rows($q115) < 1) {
						$q114 = db_query("insert into email set osoba=$osoba, adresa='$email_adresa', sistemska=1");
						zamgerlog("dodana sistemska email adresa za u$osoba", 2);
						zamgerlog2("sistemska email adresa dodana", $osoba, intval(db_insert_id()), 0, "$email_adresa");
					}
					else if (db_result($q115,0,0) == 0) {
						$q114 = db_query("update email set sistemska=1 where adresa='$email_adresa' and osoba=$osoba");
						zamgerlog("email adresa proglasena za sistemsku za u$osoba", 2);
						zamgerlog2("email adresa proglasena za sistemsku", $osoba, 0, 0, "$email_adresa");
					}
				}
			} // else if ($pristup!=0)
	
		} // if ($_REQUEST['subakcija'] == "auth")
	
	
		// Upis studenta na predmet
		if (param('subakcija') == "upisi" && check_csrf_token()) {
	
			$predmet = intval($_POST['predmet']);
			if ($predmet==0) {
				nicemessage("Niste izabrali predmet");
			} else {
				$q130 = db_query("select count(*) from student_predmet where student=$osoba and predmet=$predmet");
				if (db_result($q130,0,0)<1) {
					upis_studenta_na_predmet($osoba, $predmet);
					zamgerlog("student u$osoba upisan na predmet p$predmet",4);
					zamgerlog2("student upisan na predmet (manuelno 2)", $osoba, $predmet);
					$q136 = db_query("select p.naziv from predmet as p, ponudakursa as pk where pk.id=$predmet and pk.predmet=p.id");
					$naziv_predmeta = db_result($q136,0,0);
					nicemessage("Student upisan na predmet $naziv_predmeta.");
				}
			}
		}
	
	
		// Dodjela prava nastavniku na predmetu
		if (param('subakcija') == "daj_prava" && check_csrf_token()) {
	
			$predmet = intval($_POST['predmet']);
	
			$q115 = db_query("select naziv from predmet where id=$predmet");
			$naziv_predmeta = db_result($q115,0,0);
	
			$q130 = db_query("replace nastavnik_predmet set nastavnik=$osoba, predmet=$predmet, akademska_godina=$id_ak_god, nivo_pristupa='asistent'");
	
			zamgerlog("nastavniku u$osoba data prava na predmetu pp$predmet (admin: asistent, akademska godina: $id_ak_god)",4);
			zamgerlog2("nastavniku data prava na predmetu", $osoba, $predmet, intval($id_ak_god));
			nicemessage("Nastavniku su dodijeljena prava na predmetu $naziv_predmeta.");
			print "<p>Kliknite na naziv predmeta na spisku ispod kako biste detaljnije podesili privilegije.</p>";
		}
	
	
		// Angažman nastavnika na predmetu
		if (param('subakcija') == "angazuj" && check_csrf_token()) {
	
			$predmet = intval($_POST['predmet']);
			$status = intval($_POST['_lv_column_angazman_status']);
			$angazman_ak_god = intval($_POST['_lv_column_akademska_godina']);
	
			$q115 = db_query("select naziv from predmet where id=$predmet");
			$naziv_predmeta = db_result($q115,0,0);
	
			$q130 = db_query("replace angazman set osoba=$osoba, predmet=$predmet, akademska_godina=$angazman_ak_god, angazman_status=$status");
	
			zamgerlog("nastavnik u$osoba angazovan na predmetu pp$predmet (status: $status, akademska godina: $id_ak_god)",4);
			zamgerlog2("nastavnik angazovan na predmetu", $osoba, $predmet, intval($id_ak_god));
			nicemessage("Nastavnik angažovan na predmetu $naziv_predmeta.");
			print "<p>Kliknite na naziv predmeta na spisku ispod kako biste detaljnije podesili privilegije.</p>";
		}
	
	
		// Promjena uloga korisnika
		if (param('subakcija') == "uloga" && check_csrf_token()) {
			$korisnik['student']=$korisnik['nastavnik']=$korisnik['prijemni']=$korisnik['studentska']=$korisnik['siteadmin']=$korisnik['sefodsjeka']=$korisnik['uprava']=0;
			$q150 = db_query("select privilegija from privilegije where osoba=$osoba");
			while($r150 = db_fetch_row($q150)) {
				if ($r150[0]=="student") $korisnik['student']=1;
				if ($r150[0]=="nastavnik") $korisnik['nastavnik']=1;
				if ($r150[0]=="prijemni") $korisnik['prijemni']=1;
				if ($r150[0]=="studentska") $korisnik['studentska']=1;
				if ($r150[0]=="siteadmin") $korisnik['siteadmin']=1;
				if ($r150[0]=="sefodsjeka") $korisnik['sefodsjeka']=1;
				if ($r150[0]=="uprava") $korisnik['uprava']=1;
			}
	
			foreach ($korisnik as $privilegija => $vrijednost) {
				if ($user_studentska && !$user_siteadmin && $privilegija !== 'nastavnik') continue;
			
				if ($_POST[$privilegija]=="1" && $vrijednost==0) {
					$q151 = db_query("insert into privilegije set osoba=$osoba, privilegija='$privilegija'");
					zamgerlog("osobi u$osoba data privilegija $privilegija",4);
					zamgerlog2("osobi data privilegija", $osoba, 0, 0, $privilegija);
					nicemessage("Data privilegija $privilegija");
				}
				if ($_POST[$privilegija]!="1" && $vrijednost==1) {
					$q151 = db_query("delete from privilegije where osoba=$osoba and privilegija='$privilegija'");
					zamgerlog("osobi u$osoba oduzeta privilegija $privilegija",4);
					zamgerlog2("osobi oduzeta privilegija", $osoba, 0, 0, $privilegija);
					nicemessage("Oduzeta privilegija $privilegija");
				}
			}
		}
	
	
		//$person = api_call("person/$osoba", [ "resolve" => [ "ExtendedPerson" ]]);
		//print_r($person);
	
		// Osnovni podaci
	
		$q200 = db_query("select ime, prezime, 1, brindexa, UNIX_TIMESTAMP(datum_rodjenja), mjesto_rodjenja, jmbg, drzavljanstvo, adresa, adresa_mjesto, telefon, kanton, strucni_stepen, naucni_stepen, slika from osoba where id=$osoba");
		if (!($r200 = db_fetch_row($q200))) {
			zamgerlog("nepostojeca osoba u$osoba",3);
			zamgerlog2("nepostojeca osoba", $osoba);
			niceerror("Nepostojeća osoba!");
			return;
		}
		$ime = db_result($q200,0,0);
		$prezime = db_result($q200,0,1);
		$jmbg = db_result($q200,0,6);
		$slika = db_result($q200,0,14);
	
		// Pripremam neke podatke za ispis
		// Ovo nije u istom upitu jer nije pravi FK, podaci ne moraju biti definisani
		// TODO dodati polje "nedefinisano" u sve tabele, po mogućnosti sa IDom nula
		$mjesto_rodj = "";
		if (db_result($q200,0,5)!=0) {
			$q201 = db_query("select naziv from mjesto where id=".db_result($q200,0,5));
			$mjesto_rodj = db_result($q201,0,0);
		}
	
		$drzavljanstvo = "";
		if (db_result($q200,0,7)!=0) {
			$q202 = db_query("select naziv from drzava where id=".db_result($q200,0,7));
			$drzavljanstvo = db_result($q202,0,0);
		}
	
		$adresa = db_result($q200,0,8);
		if (db_result($q200,0,9)!=0) {
			$q203 = db_query("select naziv from mjesto where id=".db_result($q200,0,9));
			$adresa .= ", ".db_result($q203,0,0);
		}
	
		$kanton = "";
		if (db_result($q200,0,11)>0) {
			$q205 = db_query("select naziv from kanton where id=".db_result($q200,0,11));
			$kanton = db_result($q205,0,0);
		}
	
		if (db_result($q200,0,12)!=0) {
			$q206 = db_query("select naziv from strucni_stepen where id=".db_result($q200,0,12));
			$strucni_stepen = db_result($q206,0,0);
		}
		if (db_result($q200,0,13)!=0) {
			$q207 = db_query("select naziv from naucni_stepen where id=".db_result($q200,0,13));
			$naucni_stepen = db_result($q207,0,0);
		}
	
		// Spisak mailova
		
		$q260 = db_query("select adresa from email where osoba=$osoba");
		$email_adrese = "";
		while ($r260 = db_fetch_row($q260)) {
			if ($email_adrese !== "") $email_adrese .= ", ";
			$email_adrese .= $r260[0];
		}
	
		?>
	
		<h2><?=$ime?> <?=$prezime?></h2>
		<?
		if ($slika!="") {
			?>
			<img src="?sta=common/slika&osoba=<?=$osoba?>"><br/>
			<?
		}
		?>
		<table border="0" width="600"><tr><td valign="top">
			Ime: <b><?=$ime?></b><br/>
			Prezime: <b><?=$prezime?></b><br/>
			Broj indexa (za studente): <b><?=db_result($q200,0,3)?></b><br/>
			JMBG: <b><?=$jmbg?></b><br/>
			<br/>
			Datum rođenja: <b><?
			if (db_result($q200,0,4)) print date("d. m. Y.", db_result($q200,0,4))?></b><br/>
			Mjesto rođenja: <b><?=$mjesto_rodj?></b><br/>
			Državljanstvo: <b><?=$drzavljanstvo?></b><br/>
			</td><td valign="top">
			Adresa: <b><?=$adresa?></b><br/>
			Kanton: <b><?=$kanton?></b><br/>
			Telefon: <b><?=db_result($q200,0,10)?></b><br/>
			Kontakt e-mail: <b><?=$email_adrese?></b><br/>
			<br/>
			Stručni stepen: <b><?=$strucni_stepen?></b><br/>
			Naučni stepen: <b><?=$naucni_stepen?></b><br/>
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
	
		// Promjena lozinke na ovom mjestu je moguća samo ako je autentikacija "table"
		// Site admin će vidjeti detaljnije informacije
		if ($conf_system_auth == "table" || $user_siteadmin) {
			// Kapitalizovan naziv autentikacijskog modula
			$auth_name = $conf_system_auth;
			if ($conf_system_auth == "ldap") $auth_name = "LDAP";
			if ($conf_system_auth == "cas") $auth_name = "CAS";
			if ($conf_system_auth == "keycloak") $auth_name = "KeyCloak";
			
			print "<p>Korisnički pristup:\n";
			$q201 = db_query("select aktivan from auth where id=$osoba and aktivan=1");
			if (db_num_rows($q201)<1) print "<font color=\"red\">NEMA</font>";
			?></p>
				<table border="0">
				<tr>
					<td>Korisničko ime:</td>
					<td width="80">Šifra:</td>
					<td>Aktivan:</td>
					<td>&nbsp;</td>
				</tr>
			<?
	
			$q201 = db_query("select login,password,aktivan from auth where id=$osoba");
			while ($r201 = db_fetch_row($q201)) {
				$login=$r201[0];
				$password=$r201[1];
				$pristup=$r201[2];
				?>
				<?=genform("POST")?>
				<input type="hidden" name="subakcija" value="auth">
				<input type="hidden" name="stari_login" value="<?=$login?>">
				<tr>
					<td><input type="text" size="10" name="login" value="<?=$login?>"></td>
					<td valign="center"><? if ($conf_system_auth=="table") {
						?><input type="password" size="10" name="password" value="<?=$password?>"><?
					} else {
						?><b><?=$auth_name?></b><?
					}?></td>
					<td><input type="checkbox" size="10" name="aktivan" value="1" <? if ($pristup==1) print "CHECKED"; ?>></td>
					<td><input type="Submit" value=" Izmijeni "> <input type="Submit" name="brisanje" value=" Obriši "></td>
				</tr></form>
				<?
			}
	
			?>
			<?=genform("POST")?>
			<input type="hidden" name="subakcija" value="auth">
			<input type="hidden" name="stari_login" value="">
			<tr>
				<td><input type="text" size="10" name="login" value=""></td>
				<td><? if ($conf_system_auth=="table") {
						?><input type="password" size="10" name="password" value=""><?
					} else {
						?><b><?=$auth_name?></b><?
					}?></td>
				<td><input type="checkbox" size="10" name="aktivan" value="1"></td>
				<td><input type="Submit" value=" Dodaj novi "></td>
			</tr></form></table>
			<?
		}
	
		// U slučaju LDAPa studentskoj službi dajemo mogućnost da (de)aktivira pristup korisniku
		else if ($conf_system_auth == "ldap") {
			$q201 = db_query("select aktivan from auth where id=$osoba and aktivan=1");
			if (db_num_rows($q201)>0) $pristup=1; else $pristup=0;
			?>
			
			<script language="JavaScript">
			function upozorenje(pristup) {
				document.authforma.pristup.value=pristup;
				document.authforma.submit();
			}
			</script>
			<?=genform("POST", "authforma")?>
			<input type="hidden" name="subakcija" value="auth_ldap">
			<input type="hidden" name="pristup" value="">
			</form>
	
			<table border="0">
			<tr>
				<td colspan="5">Korisnički pristup: <input type="checkbox" name="aktivan" onchange="javascript:upozorenje('<?=$pristup?>');" <? if ($pristup==1) print "CHECKED"; ?>></td>
			</tr></table></form>
			<?
		}
	
	
		// Uloge korisnika
		$korisnik_student=$korisnik_nastavnik=$korisnik_prijemni=$korisnik_studentska=$korisnik_siteadmin=$korisnik_sefodsjeka=$korisnik_uprava=0;
		print "<p>Tip korisnika: ";
		$q209 = db_query("select privilegija from privilegije where osoba=$osoba");
	
		while ($r209 = db_fetch_row($q209)) {
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
			if ($r209[0]=="sefodsjeka") {
				print "<b>šef odsjeka,</b> ";
				$korisnik_sefodsjeka=1;
			}
			if ($r209[0]=="uprava") {
				print "<b>dekan/prodekan,</b> ";
				$korisnik_uprava=1;
			}
		}
		print "</p>\n";
	
	
		// Admin dio
	
		if ($user_siteadmin) {
			unset( $_REQUEST['student'], $_REQUEST['nastavnik'], $_REQUEST['prijemni'], $_REQUEST['studentska'], $_REQUEST['siteadmin'], $_REQUEST['sefodsjeka'], $_REQUEST['uprava'] );
			?>
			<?=genform("POST")?>
			<input type="hidden" name="subakcija" value="uloga">
			<input type="checkbox" name="student" value="1" <?if($korisnik_student==1) print "CHECKED";?>> Student&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="checkbox" name="nastavnik" value="1" <?if($korisnik_nastavnik==1) print "CHECKED";?>> nastavnik&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="checkbox" name="prijemni" value="1" <?if($korisnik_prijemni==1) print "CHECKED";?>> prijemni&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="checkbox" name="studentska" value="1" <?if($korisnik_studentska==1) print "CHECKED";?>> studentska&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="checkbox" name="siteadmin" value="1" <?if($korisnik_siteadmin==1) print "CHECKED";?>> siteadmin&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="checkbox" name="sefodsjeka" value="1" <?if($korisnik_sefodsjeka==1) print "CHECKED";?>> šef odsjeka&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="checkbox" name="uprava" value="1" <?if($korisnik_uprava==1) print "CHECKED";?>> dekan/prodekan<br/> <br/>
			<input type="submit" value=" Izmijeni ">
			</form>
			<?
		} else if($korisnik_student==1 && $korisnik_nastavnik!=1) {
			?>
			<?=genform("POST")?>
			<input type="hidden" name="subakcija" value="uloga">
			<input type="checkbox" name="nastavnik" value="1"> Proglasi korisnika za nastavnika&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="submit" value=" Promijeni ">
			</form>
			<?
		}
	
		// Link za uređivanje historije studenta TODO - obratiti pozornost na to "ko ima pristup"
		if($korisnik_student==1 && ($user_studentska || $user_siteadmin)) {
			// Uređivanje historije studenta
			print "<p> Za uređivanje historije studenta, kliknite <a href='index.php?sta=studentska/uredi_historiju_studenta&student=".$osoba."'>ovdje</a>. </p>";
			// Unos konačne ocjene studenta
			print "<p> Za unos, pregled i izmjene konačnih ocjena po odluci, kliknite <a href='index.php?sta=studentska/konacna_ocjena&student=".$osoba."&akcija=pregled'>ovdje</a>. </p>";
		}
	
		// STUDENT
	
		if ($korisnik_student) {
			?>
			<hr>
			<h3>STUDENT</h3>
			<?
	
			// Trenutno upisan na semestar:
			$q220 = db_query("SELECT s.naziv, ss.semestar, ss.akademska_godina, ag.naziv, s.id, ts.trajanje, ns.naziv, ts.ciklus, status.naziv, ss.put
			FROM student_studij as ss, studij as s, akademska_godina as ag, tipstudija as ts, nacin_studiranja as ns, status_studenta status
			WHERE ss.student=$osoba and ss.studij=s.id and ag.id=ss.akademska_godina and s.tipstudija=ts.id and ss.nacin_studiranja=ns.id AND ss.status_studenta=status.id
			ORDER BY ag.naziv DESC");
			$studij="0";
			$studij_id=$semestar=0;
			$puta=1;
			$status_studenta = "";
	
			// Da li je ikada slušao nešto?
			$ikad_studij=$ikad_studij_id=$ikad_semestar=$ikad_ak_god=$studij_ciklus=-1;
			$ikad_ciklusi = $ikad_puta = array();
		
			while ($r220=db_fetch_row($q220)) {
				if ($r220[2]==$id_ak_god && $r220[1]>$semestar) { //trenutna akademska godina
					$studij=$r220[0];
					$semestar = $r220[1];
					$studij_id=$r220[4];
					$studij_trajanje=$r220[5];
					$nacin_studiranja="kao $r220[6]";
					$studij_ciklus=$r220[7];
					if ($r220[8] != "Student") $status_studenta = " - " .$r220[8];
					$puta = $r220[9];
				} else if ($r220[2]>$ikad_ak_god || ($r220[2]==$ikad_ak_god && $r220[1]>$ikad_semestar)) {
					$ikad_studij=$r220[0];
					$ikad_semestar=$r220[1];
					$ikad_ak_god=$r220[2];
					$ikad_ak_god_naziv=$r220[3];
					$ikad_studij_id=$r220[4];
					$ikad_studij_trajanje=$r220[5];
					$ikad_puta["$ikad_studij_id-$ikad_semestar"] = $r220[9];
				}
				if (!in_array($r220[7], $ikad_ciklusi)) $ikad_ciklusi[] = $r220[7];
			}
	
			$prepisi_ocjena = "";
			if (count($ikad_ciklusi) > 1) {
				$ikad_ciklusi = array_reverse($ikad_ciklusi);
				foreach ($ikad_ciklusi as $i)
					if ($i == 99)
						$prepisi_ocjena .= "<br><a href=\"?sta=izvjestaj/index2&student=$osoba&ciklus=$i\">Samo stručni studij</a>";
					else
						$prepisi_ocjena .= "<br><a href=\"?sta=izvjestaj/index2&student=$osoba&ciklus=$i\">Samo $i. ciklus</a>";
			}
	
	
			// Izvjestaji
			
			?>
			<div style="float:left; margin-right:10px">
				<table width="100" border="1" cellspacing="0" cellpadding="0">
					<tr><td bgcolor="#777777" align="center">
						<font color="white"><b>IZVJEŠTAJI:</b></font>
					</td></tr>
					<tr><td align="center"><a href="?sta=izvjestaj/historija&student=<?=$osoba?>">
					<img src="static/images/32x32/report.png" border="0"><br/>Historija</a></td></tr>
					<tr><td align="center"><a href="?sta=izvjestaj/index2&student=<?=$osoba?>">
					<img src="static/images/32x32/report.png" border="0"><br/>Prepis ocjena</a> <?=$prepisi_ocjena?></td></tr>
					<tr><td align="center"><a href="?sta=izvjestaj/progress&student=<?=$osoba?>&razdvoji_ispite=0">
					<img src="static/images/32x32/report.png" border="0"><br/>Bodovi</a></td></tr>
					<tr><td align="center"><a href="?sta=izvjestaj/progress&student=<?=$osoba?>&razdvoji_ispite=1">
					<img src="static/images/32x32/report.png" border="0"><br/>Bodovi + nepoloženi ispiti</a></td></tr>
				</table>
			</div>
			<?
	
			// Aktivni moduli
			$modul_uou=$modul_kolizija=0;
			foreach ($registry as $r) {
				if (count($r) == 0) continue;
				if ($r[0]=="student/ugovoroucenju") $modul_uou=1;
				if ($r[0]=="student/kolizija") $modul_kolizija=1;
			}
	
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
						if ($semestar % 2 != 0) $semestar++; // Da ga ne bi pokušavalo upisati u parni semestar
					}
					// Zelimo da se provjeri ECTS:
					$studij = $ikad_studij;
					$studij_id = $ikad_studij_id;
					$studij_trajanje = $ikad_studij_trajanje;
	
				} else {
					// Nikada nije slušao ništa - ima li podataka o prijemnom ispitu?
					$q225 = db_query("select pt.akademska_godina, ag.naziv, s.id, s.naziv from prijemni_termin as pt, prijemni_prijava as pp, akademska_godina as ag, studij as s where pp.osoba=$osoba and pp.prijemni_termin=pt.id and pt.akademska_godina=ag.id and pp.studij_prvi=s.id order by ag.id desc, pt.id desc limit 1");
					if (db_num_rows($q225)>0) {
						$nova_ak_god = db_result($q225,0,0);
						$naziv_nove_ak_god = db_result($q225,0,1);
						$novi_studij = db_result($q225,0,3);
						$novi_studij_id = db_result($q225,0,2);
					}
				}
	
			} else {
				?>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>&quot;<?=$studij?>&quot;</b>, <?=$semestar?>. semestar (<?=$puta?>. put) <?=$nacin_studiranja?> <?=$status_studenta?> (<a href="?sta=studentska/osobe&amp;osoba=<?=$osoba?>&amp;akcija=ispis&amp;studij=<?=$studij_id?>&amp;semestar=<?=$semestar?>&amp;godina=<?=$id_ak_god?>">ispiši sa studija</a>) (<a href="?sta=studentska/osobe&osoba=<?=$osoba?>&amp;akcija=promijeni_nacin&amp;studij=<?=$studij_id?>&amp;semestar=<?=$semestar?>&amp;godina=<?=$id_ak_god?>">promijeni način studiranja</a>)</p>
				<?
				$q230 = db_query("select id, naziv from akademska_godina where id=$id_ak_god+1");
				if (db_num_rows($q230)>0) {
					$nova_ak_god = db_result($q230,0,0);
					$naziv_nove_ak_god = db_result($q230,0,1);
				}
			}
	
			$zaduzenje = db_get("SELECT zaduzenje FROM student_zaduzenje WHERE student=$osoba");
			if ($zaduzenje > 0) {
				print "<p style=\"color: red\">Evidentirano je zaduženje: <b>" . sprintf("%.2f", $zaduzenje) . " KM</b> - ";
			}
			print "<a href=\"?sta=studentska/osobe&osoba=$osoba&akcija=izmijeni_zaduzenje\">Izmijeni zaduženje</a></p>\n";
	
			if ($nova_ak_god==0) { // Upis u tekućoj godini (ako nije kreirana nova)
				?>
				<a href="?sta=studentska/osobe&amp;osoba=<?=$osoba?>&amp;akcija=upis&amp;studij=<?=$studij_id?>&amp;semestar=<?=($semestar+1)?>&amp;godina=<?=$id_ak_god?>">Upiši na <?=($semestar+1)?>. semestar</a>
				<?
				
				// Ispisujemo podatke o ugovoru o učenju
				if ($modul_uou==1) {
					$q270 = db_query("select s.naziv, u.semestar, u.kod from ugovoroucenju as u, studij as s where u.student=$osoba and u.akademska_godina=$id_ak_god and u.studij=s.id order by u.semestar");
					if (db_fetch3($q270, $naziv_studija_ugovor, $semestar_ugovor, $kod_ugovora)) {
						// Uvijek se popunjava za neparni i parni semestar!
						$semestar_ugovor .= ". i " . ($semestar_ugovor+1) . ".";
						?>
						<p>Student je popunio/la <b>Ugovor o učenju</b> za <?=$naziv_studija_ugovor?>, <?=$semestar_ugovor?> semestar:<br>Kod: <b><?=$kod_ugovora?></b></p>
						<?
					} else {
						?>
						<p>Student NIJE popunio/la <b>Ugovor o učenju</b> za sljedeću akademsku godinu.</p>
						<?
					}
				}
			}
	
	
			// Pristup web servisu za uplate
			if ($conf_knjigovodstveni_servis) {
				global $conf_url_daj_karticu;
				$kartice = parsiraj_kartice(xml_request($conf_url_daj_karticu, array("jmbg" => $jmbg), "POST"));
				$saldo = 0;
				if ($kartice === FALSE || count($kartice) == 0) {
					?>
					<p><font color="red">Nema podataka o uplatama</font></p>
					<?
				} else {
					foreach($kartice as $kartica) $saldo += $kartica['razduzenje'] - $kartica['zaduzenje'];
					if ($saldo>=0) $boja="green"; else $boja="red";
					?>
					<p><font color="<?=$boja?>">Student na računu ima: <?=number_format($saldo, 2, ",", "")?> KM</font> - <a href="?sta=studentska/osobe&amp;osoba=<?=$osoba?>&amp;akcija=kartica">Analitička kartica studenta</a></p>
					<?
				}
			}
	
	
			// UPIS U SLJEDEĆU AK. GODINU
	
			if ($nova_ak_god!=0) { // Ne prikazuj podatke o upisu dok se ne kreira nova ak. godina
	
	
			?>
			<p>Upis u akademsku <b><?=$naziv_nove_ak_god?></b> godinu:<br />
			<?
	
	
			// Da li je vec upisan?
			$novi_studij_id = 0;
			$q235 = db_query("select s.naziv, ss.semestar, s.id, ss.put from student_studij as ss, studij as s where ss.student=$osoba and ss.studij=s.id and ss.akademska_godina=$nova_ak_god order by ss.semestar desc");
			if (db_num_rows($q235)>0) {
				$novi_studij=db_result($q235,0,0);
				$novi_semestar=db_result($q235,0,1);
				$novi_studij_id=db_result($q235,0,2);
				$nputa=db_result($q235,0,3);
				?>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Student je upisan na studij: <b><?=$novi_studij?></b>, <?=$novi_semestar?>. semestar (<?=$nputa?>. put). (<a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=ispis&studij=<?=$novi_studij_id?>&semestar=<?=$novi_semestar?>&godina=<?=$nova_ak_god?>">ispiši sa studija</a>)</p><?
	
			} else {
	
			// Ima li uslove za upis
			if ($semestar==0 && $ikad_semestar==-1) {
				// Upis na prvu godinu
	
				?>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nemamo podataka da je ovaj student ikada bio upisan na fakultet.</p><?
				if ($novi_studij_id) { // Podatak sa prijemnog
					?>
					<p><a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=upis&studij=<?=$novi_studij_id?>&semestar=1&godina=<?=$nova_ak_god?>">Upiši studenta na <?=$novi_studij?>, 1. semestar.</a></p>
					<?
				} else {
					?>
					<p><a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=upis&studij=<?=$novi_studij_id?>&semestar=1&godina=<?=$nova_ak_god?>">Upiši studenta na Prvu godinu studija, 1. semestar.</a></p>
					<?
				}
	
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
				<p><a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=upis&studij=<?=$studij_id?>&semestar=<?=($semestar+1)?>&godina=<?=$id_ak_god?>">Upiši studenta na &quot;<?=$studij?>&quot;, <?=($semestar+1)?> semestar.</a></p>
				<?
	
			} else {
				// Upis na neparni semestar - da li je student dao uslov?
	
				// Pokusacemo odrediti uslov na osnovu polozenih predmeta...
				global $zamger_predmeti_pao, $zamger_pao_ects;
				$ima_uslov = ima_li_uslov($osoba, $id_ak_god);
				
				if ($ima_uslov) {
					if ($semestar == $studij_trajanje) {
						?>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Student je stekao/la uslove za upis na sljedeći ciklus studija</p>
						<p><a href="?sta=studentska/osobe&amp;osoba=<?=$osoba?>&amp;akcija=upis&amp;studij=0&amp;semestar=1&amp;godina=<?=$nova_ak_god?>">Upiši studenta na sljedeći ciklus studija.</a></p>
						<?
					} else {
						?>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Student je stekao/la uslove za upis na &quot;<?=$studij?>&quot;, <?=($semestar+1)?>. semestar</p>
						<p><a href="?sta=studentska/osobe&amp;osoba=<?=$osoba?>&amp;akcija=upis&amp;studij=<?=$studij_id?>&amp;semestar=<?=($semestar+1)?>&amp;godina=<?=$nova_ak_god?>">Upiši studenta na &quot;<?=$studij?>&quot;, <?=($semestar+1)?>. semestar.</a></p>
						<?
					}
				} else {
					if ($semestar == $studij_trajanje) {
						?>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Student <b>NIJE</b> stekao/la uslove za upis na sljedeći ciklus studija<br/>
						<?
						
					} else {
						?>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Student <b>NIJE</b> stekao/la uslove za &quot;<?=$studij?>&quot;, <?=($semestar+1)?>. semestar<br/>
						<?
					}
					
					?>
					(<?=count($zamger_predmeti_pao)?> nepoloženih predmeta, <?=$zamger_pao_ects?> ECTS kredita)
					</p>
					<p><a href="?sta=studentska/osobe&amp;osoba=<?=$osoba?>&amp;akcija=upis&amp;studij=<?=$studij_id?>&amp;semestar=<?=($semestar-1)?>&amp;godina=<?=$nova_ak_god?>">Ponovo upiši studenta na <?=$studij?>, <?=($semestar-1)?>. semestar (<?=($ikad_puta["$studij_id-".($semestar-1)]+1)?>. put).</a></p>
					<!--p><a href="?sta=studentska/osobe&amp;osoba=<?=$osoba?>&amp;akcija=upis&amp;studij=<?=$studij_id?>&amp;semestar=<?=($semestar+1)?>&amp;godina=<?=$nova_ak_god?>">Upiši studenta na <?=$sta?>.</a></p-->
					<?
				}
			}
	
			} // if ($q235... else ... -- nije vec upisan nigdje
	
				// Ugovor o učenju
				if ($modul_uou==1) {
					$q270 = db_query("select s.naziv, u.semestar, u.kod from ugovoroucenju as u, studij as s where u.student=$osoba and u.akademska_godina=$nova_ak_god and u.studij=s.id order by u.semestar");
					if (db_fetch3($q270, $naziv_studija_ugovor, $semestar_ugovor, $kod_ugovora)) {
						// Uvijek se popunjava za neparni i parni semestar!
						$semestar_ugovor .= ". i " . ($semestar_ugovor+1) . ".";
						?>
						<p>Student je popunio/la <b>Ugovor o učenju</b> za <?=$naziv_studija_ugovor?>, <?=$semestar_ugovor?> semestar:<br>Kod: <b><?=$kod_ugovora?></b></p>
						<?
					} else {
						?>
						<p>Student NIJE popunio/la <b>Ugovor o učenju</b> za sljedeću akademsku godinu.</p>
						<?
					}
				}
	
			} // if (db_num_rows($q230  -- da li postoji ak. god. iza aktuelne?
	
	
			// Kolizija
			if ($modul_kolizija==1) {
				$q280 = db_query("select count(*) from kolizija where student=$osoba and akademska_godina=$nova_ak_god");
				$ima_koliziju=0;
				if (db_result($q280,0,0)>0) {
					$ima_koliziju=$nova_ak_god;
				} else {
					// Probavamo i za trenutnu
					$q280 = db_query("select count(*) from kolizija where student=$osoba and akademska_godina=$id_ak_god");
					if (db_result($q280,0,0)>0) {
						$ima_koliziju=$id_ak_god;
					}
				}
	
				if ($ima_koliziju) { // provjeravamo septembar
					$kolizija_ok = true;
					$qc = db_query("select distinct predmet from septembar where student=$osoba and akademska_godina=$ima_koliziju");
					while ($rc = db_fetch_row($qc)) {
						$predmet = $rc[0];
				
						// Da li ima ocjenu?
						$qd = db_query("select count(*) from konacna_ocjena where student=$osoba and predmet=$predmet and ocjena>=6");
						if (db_result($qd,0,0)>0) continue;
				
						// Da li ima septembarskog roka?
						$qe = db_query("select i.id, k.prolaz from ispit as i, komponenta as k where i.akademska_godina=".($ima_koliziju-1)." and (MONTH(i.datum)=8 or MONTH(i.datum)=9) and (select count(*) from ispitocjene as io where io.ispit=i.id)>0 and i.predmet=$predmet and i.komponenta=k.id and k.naziv NOT LIKE 'Usmeni%'");
						if (db_num_rows($qe)==0) continue; // nema
				
						$polozio=false;
						$septembar_razlog = "";
						while ($re = db_fetch_row($qe)) {
							$qf = db_query("select ocjena from ispitocjene where ispit=$re[0] and student=$osoba");
							if (db_num_rows($qf)>0 && db_result($qf,0,0)>=$re[1]) {
								$polozio=true;
								break;
							}
						}
						if (!$polozio) {
							$kolizija_ok=false;
							$qg = db_query("select naziv from predmet where id=$predmet");
							$paopredmet=db_result($qg,0,0);
							break;
						}
					}
	
					if ($kolizija_ok) {
						?>
						<p>Student je popunio/la <b>Zahtjev za koliziju</b>. <a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=kolizija&godina=<?=$ima_koliziju?>">Kliknite ovdje da potvrdite upis na kolizione predmete.</a></p>
						<?
					} else {
						?>
						<p>Student je popunio/la <b>Zahtjev za koliziju</b> koji je neispravan (nije položio/la <?=$paopredmet?>). Potrebno ga je ponovo popuniti.</p>
						<?
					}
				}
			}
	
	
			// Upis studenta na pojedinačne predmete
			?>
			<p><a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=predmeti">Manuelni upis studenta na predmete / ispis sa predmeta.</a></p>
			<p><a href="?sta=izvjestaj/sv20&student=<?=$osoba?>&ugovor=da">ŠV-20 obrazac</a> * <a href="?sta=izvjestaj/upisni_list&student=<?=$osoba?>&ugovor=da">Upisni list</a> * <a href="?sta=izvjestaj/prijava_semestra&student=<?=$osoba?>&ugovor=da">List o prijavi semestra</a></p>
			<?
	
			print "\n<div style=\"clear:both\"></div>\n";
		} // STUDENT
	
	
	
		// NASTAVNIK
	
		// Akademska godina se promijenila :( CLEANUP
		$id_ak_god = $orig_iag;
		$q399 = db_query("select naziv from akademska_godina where id=$id_ak_god");
		$naziv_ak_god=db_result($q399,0,0);
	
	
		if ($korisnik_nastavnik) {
			?>
			<br/><hr>
			<h3>NASTAVNIK</h3>
			<p><b>Podaci o izboru</b></p>
			<?
	
	
			// Izbori
	
			$q400 = db_query("select z.naziv, UNIX_TIMESTAMP(i.datum_izbora), UNIX_TIMESTAMP(i.datum_isteka), i.oblast, i.podoblast, i.dopunski, i.druga_institucija from izbor as i, zvanje as z WHERE i.osoba=$osoba and i.zvanje=z.id order by i.datum_isteka DESC, i.datum_izbora DESC");
			if (db_num_rows($q400)==0) {
				print "<p>Nema podataka o izboru.</p>\n";
			} else {
				$datum_izbora = date("d. m. Y", db_result($q400,0,1));
				if (db_result($q400,0,1)==0)
					$datum_izbora = "<font color=\"red\">(nepoznato)</font>";
				$datum_isteka = date("d. m. Y", db_result($q400,0,2));
				if (db_result($q400,0,2)==0)
					$datum_isteka = "Neodređeno";
				$oblast = db_result($q400,0,3);
				if ($oblast<1)
					$oblast = "<font color=\"red\">(nepoznato)</font>";
				else {
					$q410 = db_query("select naziv from oblast where id=$oblast");
					if (db_num_rows($q410)<1)
						$oblast = "<font color=\"red\">GREŠKA</font>";
					else
						$oblast = db_result($q410,0,0);
				}
				$podoblast = db_result($q400,0,4);
				if ($podoblast<1)
					$podoblast = "<font color=\"red\">(nepoznato)</font>";
				else {
					$q420 = db_query("select naziv from podoblast where id=$podoblast");
					if (db_num_rows($q420)<1)
						$podoblast = "<font color=\"red\">GREŠKA</font>";
					else
						$podoblast = db_result($q420,0,0);
				}
				if (db_result($q400,0,5)==0) $radniodnos = "Stalni";
				else $radniodnos = "Dopunski";
				
				?>
				<table border="0">
				<tr><td>Zvanje:</td><td><?=db_result($q400,0,0)?></td></tr>
				<tr><td>Datum izbora:</td><td><?=$datum_izbora?></td></tr>
				<tr><td>Datum isteka:</td><td><?=$datum_isteka?></td></tr>
				<tr><td>Oblast:</td><td><?=$oblast?></td></tr>
				<tr><td>Podoblast:</td><td><?=$podoblast?></td></tr>
				<tr><td>Radni odnos:</td><td><?=$radniodnos?></td></tr>
				<?
				if (db_result($q400,0,6)==1) print "<tr><td colspan=\"2\">Biran/a na drugoj VŠO</td></tr>\n";
				?>
				</table>
				<?
			}
	
			?>
			<p><a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=izbori">Izmijenite podatke o izboru ili pogledajte historijske podatke</a></p>
			<?
	
	
			// Angažman
	
			?>
			<p><b>Angažman u nastavi (akademska godina <?=$naziv_ak_god?>)</b></p>
			<ul>
			<?
			
			$q430 = db_query("select p.id, p.naziv, angs.naziv, i.kratki_naziv from angazman as a, angazman_status as angs, predmet as p, institucija as i where a.osoba=$osoba and a.akademska_godina=$id_ak_god and a.predmet=p.id and a.angazman_status=angs.id and p.institucija=i.id order by angs.id, p.naziv");
			if (db_num_rows($q430) < 1)
				print "<li>Uposlenik nije angažovan niti na jednom predmetu u ovoj godini.</li>\n";
			while ($r430 = db_fetch_row($q430)) {
				print "<li><a href=\"?sta=studentska/predmeti&akcija=edit&predmet=$r430[0]&ag=$id_ak_god\">$r430[1] ($r430[3])</a> - $r430[2]</li>\n";
			}
	
	
			// Angažman
		
			?></ul>
			<p>Angažuj nastavnika na predmetu:
			<?=genform("POST")?>
			<input type="hidden" name="subakcija" value="angazuj">
			<select name="predmet" class="default"><?
			$q190 = db_query("select p.id, p.naziv, i.kratki_naziv from predmet as p, ponudakursa as pk, institucija as i where pk.predmet=p.id and pk.akademska_godina=$id_ak_god and p.institucija=i.id group by p.id,p.naziv order by p.naziv");
			while ($r190 = db_fetch_row($q190)) {
				print "<option value=\"$r190[0]\">$r190[1] ($r190[2])</a>\n";
			}
			?></select><br/>
			<?=db_dropdown("angazman_status")?>
			<?=db_dropdown("akademska_godina", $id_ak_god)?>
			<input type="submit" value=" Dodaj "></form></p>
			<?
	
	
			// Prava pristupa
	
			?>
			<p><b>Prava pristupa (akademska godina <?=$naziv_ak_god?>)</b></p>
			<ul>
			<?
			$q180 = db_query("select p.id, p.naziv, np.nivo_pristupa, i.kratki_naziv from nastavnik_predmet as np, predmet as p, institucija as i where np.nastavnik=$osoba and np.predmet=p.id and np.akademska_godina=$id_ak_god and p.institucija=i.id order by np.nivo_pristupa, p.naziv"); // FIXME: moze li se ovdje izbaciti tabela ponudakursa? studij ili institucija?
			if (db_num_rows($q180) < 1)
				print "<li>Nijedan</li>\n";
			while ($r180 = db_fetch_row($q180)) {
				print "<li><a href=\"?sta=studentska/predmeti&akcija=edit&predmet=$r180[0]&ag=$id_ak_god\">$r180[1] ($r180[3])</a>";
				if ($r180[2] == "nastavnik") print " (Nastavnik)";
				else if ($r180[2] == "super_asistent") print " (Super asistent)";
				print "</li>\n";
			}
			?></ul>
			<p>Za prava pristupa na prethodnim akademskim godinama, koristite pretragu na kartici &quot;Predmeti&quot;<br/></p>
			<?
	
	
			// Dodjela prava pristupa
		
			?><p>Dodijeli prava za predmet:
			<?=genform("POST")?>
			<input type="hidden" name="subakcija" value="daj_prava">
			<select name="predmet" class="default"><?
			$q190 = db_query("select p.id, p.naziv, i.kratki_naziv from predmet as p, ponudakursa as pk, institucija as i where pk.predmet=p.id and pk.akademska_godina=$id_ak_god and p.institucija=i.id group by p.id, p.naziv order by p.naziv");
			while ($r190 = db_fetch_row($q190)) {
				print "<option value=\"$r190[0]\">$r190[1] ($r190[2])</a>\n";
			}
			?></select>&nbsp;
			<input type="submit" value=" Dodaj "></form></p>
			<?
		}
	
	
	
	
	
		// PRIJEMNI
	
		$q600 = db_query("select prijemni_termin, broj_dosjea, nacin_studiranja, studij_prvi, studij_drugi, studij_treci, studij_cetvrti, izasao, rezultat from prijemni_prijava where osoba=$osoba");
		if (db_num_rows($q600)>0) {
			?>
			<br/><hr>
			<h3>KANDIDAT NA PRIJEMNOM ISPITU</h3>
			<?
			while ($r600 = db_fetch_row($q600)) {
				$q610 = db_query("select ag.id, ag.naziv, UNIX_TIMESTAMP(pt.datum), pt.ciklus_studija from prijemni_termin as pt, akademska_godina as ag where pt.id=$r600[0] and pt.akademska_godina=ag.id");
				?>
				<b>Za akademsku <?=db_result($q610,0,1)?> godinu (<?=db_result($q610,0,3)?>. ciklus studija), održan <?=date("d. m. Y", db_result($q610,0,2))?></b>
				<ul><li><?
					if ($r600[7]>0) print "$r600[8] bodova"; else print "(nije izašao/la)";
				?></li>
				<li>Broj dosjea: <?=$r600[1]?>, <?
				$q615 = db_query("select naziv from nacin_studiranja where id=$r600[2]");
				if (db_num_rows($q615)>0)
					print db_result($q615,0,0);
				else
					print "nepoznato";
				for ($i=3; $i<=6; $i++) {
					if ($r600[$i]>0) {
						$q620 = db_query("select kratkinaziv from studij where id=".$r600[$i]);
						print ", ".db_result($q620,0,0);
					}
				}
				?></li>
				<?
	
				// Link na upis prikazujemo samo za ovogodišnji prijemni
				$godina_prijemnog = db_result($q610,0,0);
	//			$q630 = db_query("select id from akademska_godina where aktuelna=1");
	//			$nova_ak_god = db_result($q630,0,0)+1;
	
	//			if ($godina_prijemnog==$nova_ak_god) {
				// Moguće je da se asistent upisuje na 3. ciklus pa je $korisnik_nastavnik==true
				if (!$korisnik_student) {
					?>
					<li><a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=upis&studij=<?=$r600[3]?>&semestar=1&godina=<?=$godina_prijemnog?>">Upiši kandidata na <?
					$q630 = db_query("select naziv from studij where id=$r600[3]");
					if (db_num_rows($q630) > 0)
						print "&quot;".db_result($q630,0,0)."&quot;";
					else
						print "prvu godinu studija";
					?>, 1. semestar, u akademskoj <?=db_result($q610,0,1)?> godini</a></li>
				<?
				}
				?>
				</ul><?
			}
	
			$q640 = db_query("select ss.naziv, us.opci_uspjeh, us.kljucni_predmeti, us.dodatni_bodovi, us.ucenik_generacije from srednja_skola as ss, uspjeh_u_srednjoj as us where us.srednja_skola=ss.id and us.osoba=$osoba");
	
			if (db_num_rows($q640)>0) {
				?>
				<b>Uspjeh u srednjoj školi:</b>
				<ul>
				<li>Škola: <?=db_result($q640,0,0)?></li>
				<li>Opći uspjeh: <?=db_result($q640,0,1)?>. Ključni predmeti: <?=db_result($q640,0,2)?>. Dodatni bodovi: <?=db_result($q640,0,3)?>. <?
				if (db_result($q640,0,4)>0) print "Učenik generacije.";
				?></li>
				</ul>
				<?
			}
		}
	
		?></td></tr></table></center><? // Vanjska tabela
	
	}
	
	
	
	// Spisak osoba
	
	else {
		$src = db_escape(param("search"));
		$limit = 20;
		$offset = int_param("offset");
	
		// Naucni stepeni
		$naucni_stepen = array();
		$q99 = db_query("select id, titula from naucni_stepen");
		while ($r99 = db_fetch_row($q99))
			$naucni_stepen[$r99[0]]=$r99[1];
	
		?>
		<p><h3>Studentska služba - Studenti i nastavnici</h3></p>
	
		<table width="500" border="0"><tr><td align="left">
			<p><b>Pretraži osobe:</b><br/>
			Unesite dio imena i prezimena ili broj indeksa<br/>
			<?=genform("GET")?>
			<input type="hidden" name="offset" value="0"> <?/*resetujem offset*/?>
			<input type="text" size="50" name="search" value="<? if ($src!="sve") print $src?>"> <input type="Submit" value=" Pretraži "></form>
			<a href="<?=genuri()?>&search=sve">Prikaži sve osobe</a></p>
		<?
		if ($src) {
			$rezultata=0;
			if ($src == "sve") {
				$q100 = db_query("select count(*) from osoba");
				$q101 = db_query("select id, ime, prezime, brindexa, naucni_stepen from osoba order by prezime,ime limit $offset,$limit");
				$rezultata = db_result($q100,0,0);
			} else {
				$src = preg_replace("/\s+/"," ",$src);
				$src=trim($src);
				$dijelovi = explode(" ", $src);
				$query = "";
	
				// Probavamo traziti ime i prezime istovremeno
				if (count($dijelovi)==2) {
					$q100 = db_query("select count(*) from osoba where ime like '%$dijelovi[0]%' and prezime like '%$dijelovi[1]%'");
					$q101 = db_query("select id,ime,prezime,brindexa,naucni_stepen from osoba where ime like '%$dijelovi[0]%' and prezime like '%$dijelovi[1]%' order by prezime,ime limit $offset,$limit");
					if (db_result($q100,0,0)==0) {
						$q100 = db_query("select count(*) from osoba where ime like '%$dijelovi[1]%' and prezime like '%$dijelovi[0]%'");
						$q101 = db_query("select id,ime,prezime,brindexa,naucni_stepen from osoba where ime like '%$dijelovi[1]%' and prezime like '%$dijelovi[0]%' order by prezime,ime limit $offset,$limit");
					}
					$rezultata = db_result($q100,0,0);
				}
	
				// Nismo nasli ime i prezime, pokusavamo bilo koji dio
				if ($rezultata==0) {
					foreach($dijelovi as $dio) {
						if ($query != "") $query .= "or ";
						$query .= "ime like '%$dio%' or prezime like '%$dio%' or brindexa like '%$dio%' ";
						if (intval($dio)>0) $query .= "or id=".intval($dio)." ";
					}
					$q100 = db_query("select count(*) from osoba where ($query)");
					$q101 = db_query("select id,ime,prezime,brindexa,naucni_stepen from osoba where ($query) order by prezime,ime limit $offset,$limit");
					$rezultata = db_result($q100,0,0);
				}
	
				// Nismo nasli nista, pokusavamo login
				if ($rezultata==0) {
					$query="";
					foreach($dijelovi as $dio) {
						if ($query != "") $query .= "or ";
						$query .= "a.login like '%$dio%' ";
					}
					$q100 = db_query("select count(*) from osoba as o, auth as a where ($query) and a.id=o.id");
					$q101 = db_query("select o.id,o.ime,o.prezime,o.brindexa,o.naucni_stepen from osoba as o, auth as a where ($query) and a.id=o.id order by o.prezime,o.ime limit $offset,$limit");
					$rezultata = db_result($q100,0,0);
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
			while ($r101 = db_fetch_row($q101)) {
				print "<tr ";
				if ($i%2==0) print "bgcolor=\"#EEEEEE\"";
				print "><td>$i. $r101[2] ";
				if ($r101[4]>0) print $naucni_stepen[$r101[4]]." ";
				print $r101[1];
				if (intval($r101[3])>0) print " ($r101[3])";
				print "</td><td><a href=\"".genuri()."&akcija=edit&osoba=$r101[0]\">Detalji</a></td></tr>";
				$i++;
			}
			print "</table>";
		}
	
		?>
			<br/>
			<?=genform("POST")?>
			<input type="hidden" name="akcija" value="nova">
			<b>Unesite novu osobu:</b><br/>
			<table border="0" cellspacing="0" cellpadding="0" width="100%">
			<tr><td>Ime<? if ($conf_ldap_search) print " ili login"?>:</td><td>Prezime:</td><td>&nbsp;</td></tr>
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

