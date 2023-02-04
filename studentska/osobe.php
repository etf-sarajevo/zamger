<?

// STUDENTSKA/OSOBE - administracija studenata, studentska služba



function studentska_osobe() {
	global $user_siteadmin, $user_studentska, $_api_http_code;
	global $conf_passwords, $conf_ldap_search, $conf_ldap_server, $conf_ldap_dn, $conf_ldap_domain, $conf_system_auth;

	require_once("lib/student_predmet.php");
	
	
	// Provjera privilegija
	if (!$user_siteadmin && !$user_studentska) { // 2 = studentska, 3 = admin
		zamgerlog("korisnik nije studentska",3);
		zamgerlog2("nije studentska");
		biguglyerror("Pristup nije dozvoljen.");
		return;
	}
	
	
	
	$akcija = param('akcija');
	
	
	// Dodavanje nove osobe u bazu
	
	if ($akcija == "nova" && check_csrf_token()) {
		require_once("studentska/osobe/nova.php");
		studentska_osobe_nova();
		return;
	}

	if ($akcija != "") {
		$osoba = int_param('osoba');
		$person = api_call("person/$osoba", [ "resolve" => [ "ExtendedPerson", "ProfessionalDegree", "ScientificDegree" ], "withInactiveLogins" => true ]);
		if ($_api_http_code == "404") {
			niceerror("Nepoznata osoba");
			return;
		} else if ($_api_http_code != "200") {
			niceerror("Neuspješan pristup podacima osobe");
			return;
		}
		
		if ($akcija != "edit") {
			?>
			<p><a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=edit">Nazad na podatke o osobi</a></p>
			<?
		}
	}

	// Izmjena ličnih podataka osobe
	
	if ($akcija == "podaci") {
		require_once "includes/profile/profile.php";
		$title = '<div class="col-md-6">
						<span class="navbar-brand color-logo" href="#"> <h2>Izmjena ličnih podataka - ' . $person['name'] . ' ' . $person['surname'] . '</h2> </span>
					</div>
					<div class="col-md-6 text-right pt-2 mt-2">
						&nbsp;
					</div>';
		includes_profile($title, $person, true);
	}


	// Upis studenta na semestar
	
	else if ($akcija == "upis") {
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
		require_once("studentska/osobe/promijeni_nacin.php");
		studentska_osobe_promijeni_nacin();
	}

	
	// Pregled predmeta za koliziju i potvrda
	
	else if ($akcija == "kolizija") {
		require_once("studentska/osobe/kolizija.php");
		studentska_osobe_kolizija();
	}


	
	// Manuelni upis/ispis na predmete
	
	else if ($akcija == "predmeti") {
		require_once("studentska/osobe/predmeti.php");
		studentska_osobe_predmeti();
	}

	
	// Izbori za nastavnike
	
	else if ($akcija == "izbori") {
		require_once("studentska/osobe/izbori.php");
		studentska_osobe_izbori();
	}

	
	// Analitička kartica studenta
	
	else if ($akcija == "kartica") {
		require_once("studentska/osobe/kartica.php");
		studentska_osobe_kartica();
	}


	// Ažuriranje dugovanja studenta
	
	else if ($akcija == "izmijeni_zaduzenje") {
		require_once("studentska/osobe/zaduzenje.php");
		studentska_osobe_zaduzenje();
	}
	
	
	// Uređivanje konačnih ocjena
	
	else if ($akcija == "konacna_ocjena") {
		require_once("studentska/osobe/konacna_ocjena.php");
		studentska_osobe_konacna_ocjena($person);
	}

	
	// Pregled informacija o osobi
	
	else if ($akcija == "edit") {
		$pretraga = db_escape(param('search'));
		$ofset = int_param('offset');
	
		?><a href="?sta=studentska/osobe&search=<?=$pretraga?>&offset=<?=$ofset?>">Nazad na rezultate pretrage</a><br/><br/><?
		
	
		// Promjena korisničkog pristupa i pristupnih podataka
		if (param('subakcija') == "auth" && check_csrf_token()) {
			$login = db_escape(trim($_REQUEST['login']));
			$login_ldap = zamger_ldap_escape(trim($_REQUEST['login']));
			$stari_login = db_escape($_REQUEST['stari_login']);
			$password = db_escape($_REQUEST['password']);
			$aktivan = int_param('aktivan');
	
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
		
		
		// JavaScript
		ajax_box();
		?>
		<SCRIPT language="JavaScript">
		var person = null, personId = <?=$osoba?>;
		window.onload = function() {
			ajax_api_start(
				"person/" + personId,
				"GET",
                { "resolve" : [ "ExtendedPerson", "ProfessionalDegree", "ScientificDegree" ] },
				function (foo) {
					person = foo;
				},
            	function (responseText, status, url) {}
			);
		}
		function addRemovePrivilege(privilege) {
            var found = -1;
            for (var i=0; i<person.privileges.length; i++) {
                if (person.privileges[i] === privilege)
                    found = i;
			}
            console.log("Found " + found);
			if (found === -1)
                person.privileges.push(privilege);
            else
                person.privileges.splice(found, 1);
		}
        function updatePrivileges(okFunc, errorFunc) {
            ajax_api_start(
                "person/" + personId + "/privileges",
                "PUT",
                person,
                okFunc,
                errorFunc
            );
		}
		</SCRIPT>
		<?php
	
		// Osnovni podaci
		$exp = $person['ExtendedPerson']; // shortcut
		
		
		// Small helper function
		function optionalField($field) {
			if (is_array($field) && array_key_exists('name', $field))
				return $field['name'];
			return "";
		}
		
		// Nationality
		$nationality = "";
		if (intval($exp['nationality']) > 0) {
			$country = api_call("person/country/" . $exp['nationality']);
			if ($_api_http_code == "200")
				$nationality = $country['name'];
		}
		
		// PoB
		$placeOfBirth = "";
		if (is_array($exp['placeOfBirth']) && array_key_exists('name', $exp['placeOfBirth'])) {
			$placeOfBirth = $exp['placeOfBirth']['name'];
			if ($exp['placeOfBirth']['Country']['id'] != 1) {
				$pobCountry = api_call("person/country/" . $exp['placeOfBirth']['Country']['id']);
				$placeOfBirth .= " (" . $pobCountry['name'] . ")";
			}
		}
		
		// Address
		$address = "";
		if ($exp['addressStreetNo']) {
			$address = $exp['addressStreetNo'];
			if (optionalField($exp['addressPlace'])) $address .= ", ";
		}
		$address .= optionalField($exp['addressPlace']);
		
		// E-mails
		$emails = "";
		foreach($person['email'] as $email) {
			if ($emails != "") $emails .= ", ";
			$emails .= $email['address'];
		}
		
		// Canton
		$cantons = [
			"1" => "Bosansko-Podrinjski kanton",
			"2" => "Hercegovačko-Neretvanski kanton",
			"3" => "Livanjski kanton",
			"4" => "Posavski kanton",
			"5" => "Sarajevski kanton",
			"6" => "Srednjobosanski kanton",
			"7" => "Tuzlanski kanton",
			"8" => "Unsko-Sanski kanton",
			"9" => "Zapadno-Hercegovački kanton",
			"10" =>"Zeničko-Dobojski kanton",
			"11" =>"Republika Srpska",
			"12" =>"Distrikt Brčko",
			"13" =>"Strani državljanin",
		];
		$canton = "";
		if (array_key_exists('Municipality', $exp['residencePlace']) && array_key_exists('Canton', $exp['residencePlace']['Municipality']))
			$canton = $cantons[ $exp['residencePlace']['Municipality']['Canton'] ];
		
		?>
	
		<h2><?=$person['name']?> <?=$person['surname']?></h2>
		<?
		if ($person['hasPhoto']!="") {
			?>
			<img src="?sta=common/slika&osoba=<?=$osoba?>"><br/>
			<?
		}
		
		?>
		<table border="0" width="600"><tr><td valign="top">
			Ime: <b><?=$person['name']?></b><br/>
			Prezime: <b><?=$person['surname']?></b><br/>
			Broj indexa (za studente): <b><?=$person['studentIdNr']?></b><br/>
			JMBG: <b><?=$exp['jmbg']?></b><br/>
			<br/>
			Datum rođenja: <b><?
			if ($exp['dateOfBirth']) print date("d. m. Y.", db_timestamp($exp['dateOfBirth']))?></b><br/>
			Mjesto rođenja: <b><?=$placeOfBirth?></b><br/>
			Državljanstvo: <b><?=$nationality?></b><br/>
			</td><td valign="top">
			Adresa: <b><?=$address?></b><br/>
			Kanton: <b><?=$canton?></b><br/>
			Telefon: <b><?=$exp['phone']?></b><br/>
			Kontakt e-mail: <b><?=$emails?></b><br/>
			<br/>
			Stručni stepen: <b><?=optionalField($exp['ProfessionalDegree'])?></b><br/>
			Naučni stepen: <b><?=optionalField($exp['ScientificDegree'])?></b><br/>
					<?
					if (!empty($exp['specialCategories'])) {
						?>
						Posebne kategorije: <b><?
						foreach ($exp['specialCategories'] as $cat) print $cat['SpecialCategory']['name'] . ", ";
						?></b><br/>
						<?
					}
					?>
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
		if ($conf_passwords == "table" || $user_siteadmin) {
			// Kapitalizovan naziv autentikacijskog modula
			$auth_name = $conf_passwords;
			if ($conf_passwords == "ldap") $auth_name = "LDAP";
			if ($conf_passwords == "backend") $auth_name = "Backend";
			
			// Da li ima aktivan login?
			$hasLogin = false;
			foreach($person['login'] as $login) {
				if ($login['active']) $hasLogin = true;
			}
			
			print "<p>Korisnički pristup:\n";
			if (!$hasLogin) print "<font color=\"red\">NEMA</font>";
			?>
				<table border="0">
				<tr>
					<td>Korisničko ime:</td>
					<td style="width: 80px">Šifra:</td>
					<td>Aktivan:</td>
					<td>&nbsp;</td>
				</tr>
			<?
			
			foreach($person['login'] as $login) {
				?>
				<?=genform("POST")?>
				<input type="hidden" name="subakcija" value="auth">
				<input type="hidden" name="stari_login" value="<?=$login['login']?>">
				<tr>
					<td><input type="text" size="10" name="login" value="<?=$login['login']?>"></td>
					<td valign="center"><? if ($conf_system_auth=="table" || $login['password'] !== '') {
						?><input type="password" size="10" name="password" value="<?=$login['password']?>"><?
					} else {
						?><b><?=$auth_name?></b><?
					}?></td>
					<td><input type="checkbox" size="10" name="aktivan" value="1" <? if ($login['active']) print "CHECKED"; ?>></td>
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
		else {
			
			$hasLogin = false;
			foreach($person['login'] as $login) {
				if ($login['active']) $hasLogin = true;
			}
			
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
				<td colspan="5">Korisnički pristup: <input type="checkbox" name="aktivan" onchange="javascript:upozorenje('<?=$hasLogin?>');" <? if ($hasLogin) print "CHECKED"; ?>></td>
			</tr></table></form>
			<?
		}
	
	
		// Uloge korisnika
		$allPrivileges = [ 'student', 'nastavnik', 'prijemni', 'studentska', 'siteadmin', 'sefodsjeka', 'uprava' ];
		print "<p>Tip korisnika: ";
		foreach($person['privileges'] as $privilege) {
			print "<b>$privilege,</b> ";
		}
		print "</p>\n";
	
	
		// Admin dio
	
		if ($user_siteadmin) {
			?>
			<?
			foreach ($allPrivileges as $privilege) {
				?>
				<input type="checkbox" <? if (in_array($privilege, $person['privileges'])) print "CHECKED";?> onchange="addRemovePrivilege('<?=$privilege?>')"> <?=$privilege?>&nbsp;&nbsp;&nbsp;&nbsp;
				<?
			}
		
			?><br/> <br/>
			<input type="submit" value=" Izmijeni " onclick="updatePrivileges(
                    function(foo) { document.getElementById('izmijeniPrivilegije').innerHTML = '<b>Izmijenjene privilegije.</b>'; },
                    function (responseText, status, url) { let response=JSON.parse(responseText); alert('Došlo je do greške ('+status+'): ' + response['message']); }
				)"> <div id="izmijeniPrivilegije" style="color:green"></div>
			<?
		} else if(in_array('student', $person['privileges']) && !in_array('nastavnik', $person['privileges'])) {
			?>
			<div id="proglasiZaNastavnika">
				<input type="checkbox" name="nastavnik" value="1" onchange="addRemovePrivilege('nastavnik');"> Proglasi korisnika za nastavnika&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="submit" value=" Promijeni " onclick="updatePrivileges(
                    function(foo) { document.getElementById('proglasiZaNastavnika').innerHTML = '<b>Korisnik proglašen za nastavnika.</b>'; },
                    function (responseText, status, url) { let response=JSON.parse(responseText); alert('Došlo je do greške ('+status+'): ' + response['message']); }
				)">
			</div>
			<?
		}
	
		// Link za uređivanje historije studenta TODO - obratiti pozornost na to "ko ima pristup"
		if (in_array('student', $person['privileges'])) {
			// Uređivanje historije studenta
			print "<p> Za uređivanje historije studenta, kliknite <a href='index.php?sta=studentska/uredi_historiju_studenta&student=".$osoba."'>ovdje</a>. </p>";
			// Unos konačne ocjene studenta
			print "<p> Za unos, pregled i izmjene konačnih ocjena po odluci, kliknite <a href='index.php?sta=studentska/osobe&osoba=".$osoba."&akcija=konacna_ocjena&subakcija=pregled'>ovdje</a>. </p>";
		}
	
		
		// STUDENT
	
		if (in_array('student', $person['privileges'])) {
			require_once("studentska/osobe/student.php");
			studentska_osobe_student();
		}
		
	
		// NASTAVNIK
		
		if (in_array('nastavnik', $person['privileges'])) {
			require_once("studentska/osobe/nastavnik.php");
			studentska_osobe_nastavnik();
		}
		
		
		// PRIJEMNI
		
		$kandidat = db_get("select COUNT(*) from prijemni_prijava where osoba=$osoba");
		if ($kandidat) {
			require_once("studentska/osobe/prijemni.php");
			studentska_osobe_prijemni();
		}
	
	}
	
	
	
	// Spisak osoba
	
	else {
		$src = db_escape(param("search"));
		$limit = 20;
		$page = int_param("page");
		if ($page == 0) $page = 1;
	
		?>
		<h3>Studentska služba - Studenti i nastavnici</h3>
	
			<p><b>Pretraži osobe:</b><br/>
			Unesite dio imena i prezimena ili broj indeksa<br/>
			<?=genform("GET")?>
			<input type="hidden" name="offset" value="0"> <?/*resetujem offset*/?>
			<input type="text" size="50" name="search" value="<? if ($src!="sve") print $src?>"> <input type="Submit" value=" Pretraži "></form>
			<a href="<?=genuri()?>&search=sve">Prikaži sve osobe</a></p>
		<?
		
		if ($src) {
			if ($src == "sve")
				$persons = api_call("person/all", [ "page" => $page ]);
			else
				$persons = api_call("person/search", [ "query" => param('search'), "page" => $page ]);
			
			$brojRezultata = $persons['totalResults'];
			$brojStranica = $persons['totalPages'];
			$page = $persons['page']; // If page is changed on backend for some reason
			if ($page > $brojStranica) $page = $brojStranica;
			
			$kraj = $page * $limit;
			$poc = $kraj - $limit + 1;
			
			if ($brojRezultata == 0)
				print "Nema rezultata!";
			else if ($brojRezultata>$limit) {
				print "Prikazujem rezultate $poc-$kraj od $brojRezultata. Stranica: ";
	
				for ($i=1; $i<=intval($brojStranica); $i++) {
					if ($i==$page)
						print "<b>$i</b> ";
					else
						print "<a href=\"".genuri()."&page=$i\">$i</a> ";
				}
				print "<br/>";
			}
	//		else
	//			print "$rezultata rezultata:";
	
			print "<br/>";
	
			?><table width="100%" border="0"><?
			$i=$poc;
			foreach ($persons['results'] as $person) {
				?>
				<tr <? if ($i%2==0) print "bgcolor=\"#EEEEEE\""; ?>>
					<td><?=$i?>. <?=$person['surname']?> <?
						//if ($r101[4]>0) print $naucni_stepen[$r101[4]]." ";
						?> <?=$person['name']?> <? if ($person['studentIdNr']) print " (" . $person['studentIdNr'] . ")"; ?>
					</td>
					<td><a href="<?=genuri()?>&akcija=edit&osoba=<?=$person['id']?>">Detalji</a></td>
				</tr>
				<?
				$i++;
			}
			?>
			</table>
			<?
		}
	
		?>
			<br/>
			<?=genform("POST")?>
			<input type="hidden" name="akcija" value="nova">
			<b>Unesite novu osobu:</b><br/>
			<table border="0" cellspacing="0" cellpadding="0" width="500">
			<tr><td>Ime<? if ($conf_ldap_search) print " ili login"?>:</td><td>Prezime:</td><td>&nbsp;</td></tr>
			<tr>
				<td><input type="text" name="ime" size="15"></td>
				<td><input type="text" name="prezime" size="15"></td>
				<td><input type="submit" value=" Dodaj "></td>
			</tr></table>
			</form>
		<?
	}
	
}

