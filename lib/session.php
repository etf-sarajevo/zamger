<?

// LIB/SESSION - podrška za login/logout na Zamgeru


// Funkcija koja prihvata podatke sa "internal" login stranice, neće uopšte biti pozivana za
// različite vrijednosti $conf_login_screen
// Funkcija je pisana pod pretpostavkom da je $login globalna varijabla (iz više razloga)
// Parametri:
//    password - lozinka korisnika
//    type     - gdje se lozinka nalazi (table, ldap)
// Vraća:
//    0 - OK
//    1 - nepoznat korisnik
//    2 - pogrešan password
// Globalne varijable:
//    $login  - korisničko ime (mora biti setovana prije poziva funkcije)
//    $admin  - korisnik je administrator
//    $userid - interni ID korisnika (prirodan broj)

require("lib/oauth2_client.php");

function login($pass, $type = "") {
	global $userid,$admin,$login,$conf_passwords,$conf_ldap_server,$conf_ldap_domain,$conf_ldap_dn,$posljednji_pristup;
	if ($type === "") $type = $conf_passwords;
	
	if ($type == "backend") {
		require_once("lib/ws.php");
		global $conf_backend_url;
		$result = api_call("auth", ["login" => $login, "pass" => $pass], "POST");
		if ($result['success'] == "false") {
			print_r($result);
			return 2;
		}
		session_start();
		//session_regenerate_id(); // prevent session fixation
		$_SESSION['login']=$login;
		$_SESSION['api_session'] = $result['sid'];
		check_cookie();
		session_write_close();
		return 0;
	}

	$q1 = db_query("select id, password, admin, UNIX_TIMESTAMP(posljednji_pristup) from auth where login='$login' and aktivan=1");
	if (db_num_rows($q1)<=0)
		return 1;

	if ($type == "ldap") {
		$ds = ldap_connect($conf_ldap_server);
		ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
		if ($ds) {
			if (ldap_bind($ds)) {
				$i=0;

				// Probavamo UID
				$login = zamger_ldap_escape($login);
				$sr = ldap_search($ds, $conf_ldap_dn, "uid=$login", array() /* just dn */ );
				if (!$sr) {
					niceerror("ldap_search() failed.");
					exit;
				}
				$results = ldap_get_entries($ds, $sr);
				// Ako ldap_get_entries vrati false, pretpostavićemo da nema rezultata
				// To se dešava rijetko ali se dešava i nije mi jasno zašto

				// Ovaj upit ce vratiti i aliase, koje moramo profiltrirati
				while ($results && is_alias($results[$i]) && $i<$results['count']) $i++;

				// Probavamo email adresu
				if (!$results || $i == $results['count']) {
					$sr = ldap_search($ds, $conf_ldap_dn, "mail=$login", array() );
					if (!$sr) {
						niceerror("ldap_search() 1 failed.");
						exit;
					}
					$results = ldap_get_entries($ds, $sr);

					$i=0;
					while ($results && is_alias($results[$i]) && $i<$results['count']) $i++;
				}

				// Probavamo email adresu + domena
				if (!$results || $i == $results['count']) {
					$sr = ldap_search($ds, $conf_ldap_dn, "mail=$login$conf_ldap_domain", array() );
					if (!$sr) {
						niceerror("ldap_search() 2 failed.");
						exit;
					}
					$results = ldap_get_entries($ds, $sr);

					$i=0;
					while ($results && is_alias($results[$i]) && $i<$results['count']) $i++;
				}

				if (!$results || $i == $results['count']) // return 1;
					// Ako nema na LDAPu probavamo "table" autentikaciju
					return login($pass, "table");
				$dn = $results[$i]['dn'];
				
				if (!@ldap_bind($ds, $dn, $pass)) {
					// ldap_bind generiše warning svaki put kad je pogrešna šifra :(
					//return 2;
					return login($pass, "table");
				}
				// ldap_bind succeeded, user is authenticated
			} else {
				niceerror("LDAP anonymous bind failed.");
				exit;
			}
		} else {
			niceerror("Can't contact LDAP server.");
			exit;
		}
	} else if ($type == "table") {
		$result = db_result($q1,0,1);
		if ($pass != $result || $result === "") return 2;
	}
	
	$userid = db_result($q1,0,0);
	$admin = db_result($q1,0,2);
	$posljednji_pristup = db_result($q1,0,3);
	$q2 = db_query("update auth set posljednji_pristup=NOW() where id=$userid");

	// All OK, start session
	session_start();
	//session_regenerate_id(); // prevent session fixation
	$_SESSION['login']=$login;
	$_SESSION['api_session'] = $result['sid'];
	session_write_close();
	return 0;
}


// Redirekcija na CAS login screen
function cas_login_screen() {
	// TODO: FIXME
	global $conf_cas_server;
	header('Location: ' . $conf_cas_server);
}

// Redirektuj na odgovarajuću login stranicu
function session_login_screen() {
	global $conf_login_screen;
	if ($conf_login_screen == "cas")
		cas_login_screen();
	if ($conf_login_screen == "keycloak")
		OAuth2Helper::loginScreen();
}

// Provjera da li trenutni korisnik ima važeću sesiju
function check_cookie() {
	global $userid,$admin,$login,$conf_cas,$conf_keycloak,$posljednji_pristup,$conf_script_path,$conf_passwords,$person,$privilegije,$su,$uspjeh;
	
	require "$conf_script_path/vendor/autoload.php"; // phpcas, keycloak
	
	$userid = 0;
	$admin = 0;
	$login = "";
	
	// Ovo je potrebno u nekim slučajevima kod pristupa servisima
	if (isset($_REQUEST['PHPSESSID'])) session_id($_REQUEST['PHPSESSID']);
	session_start();
	
	// Provjeravamo CAS sesiju
	if ($conf_cas) {
		global $conf_cas_server, $conf_cas_port, $conf_cas_context;
		phpCAS::setDebug();
		phpCAS::client(CAS_VERSION_2_0, $conf_cas_server, $conf_cas_port, $conf_cas_context);
		phpCAS::setNoCasServerValidation();
		phpCAS::forceAuthentication();
		$login = phpCAS::getUser(); // TODO: šta se desi ako sesija nije validna? testirati
	}
	

	// Provjera OAuth2 single sign-on sesije
	if ($login == "" && $conf_keycloak) {
		$login = OAuth2Helper::checkSession();
	}
	
	// Koristimo backend za sesiju
	if ($conf_passwords == "backend" && (!$conf_keycloak || $login != "")) {
		// Ako se ne koristi OAuth2, sa backenda ćemo dobiti login
		// Ako se koristi OAuth2, moramo imati login jer na osnovu logina dobijamo token
		require_once("lib/ws.php");
		$person = api_call("person", ["resolve[]" => "ExtendedPerson"]);
		
		if ($person['code'] == "401") {
			if ($conf_keycloak) {
				// Token expired - this happens when users use "back" button to reach login page
				zamgerlog("person vratio " . $person['code'] == "490",3);
				?>
				<p>Vaša sesija je istekla. <a href="?logout">Prijavite se ponovo.</a></p>
				<?php
				$uspjeh = 2; // Don't display an error message
				exit(0);
			}
			
			// We use backend for session, so return to login page
			$userid = 0;
			return;
		}
		
		if ($person['code'] != "200") {
			// Other type of exception - can happen if database is inconsistent
			niceerror("Došlo je do greške u pristupu podacima Vaše osobe");
			print "<p>Molimo da pošaljete sljedeće podatke nadležnima. Detalji:</p>";
			api_debug($person, true);
			if ($conf_keycloak) {
				print "<p>Možete probati logout preko <a href='$conf_keycloak_url/realms/$conf_keycloak_realm/account/'>Keycloak stranice</a> mada ne vjerujemo da će pomoći.</p>";
			}
			$uspjeh = 2; // Don't display an error message
			exit(0);
		}
		
		$privilegije = $person['privileges'];
		$userid = $person['id'];
		if ($login == "") $login = $person['login'][0];
		$posljednji_pristup = db_timestamp($person['lastAccess']);
		foreach($privilegije as $p)
			if ($p != "student")
				$admin=1;
		
		// SU - switch user
		$su = int_param('su');
		if ($su==0 && isset($_SESSION['su'])) $su = $_SESSION['su'];
		$unsu = int_param('unsu');
		if ($unsu==1 && $su!=0) $su=0;
		if ($su>0) {
			if (in_array("siteadmin", $privilegije)) {
				$userid=$su;
				$_SESSION['su']=$su;
				$person = api_call("person/$su", ["resolve[]" => "ExtendedPerson"]);
				$privilegije = $person['privileges'];
			}
		} else {
			$_SESSION['su']="";
		}
		if ($unsu == 1) {
			// Moramo ponoviti person, sada bez impersonate atributa
			$person = api_call("person", ["resolve[]" => "ExtendedPerson"]);
			$privilegije = $person['privileges'];
		}
		
		// Ne smijemo dalje nastaviti, jer ostatak koda pretpostavlja bazu
		return;
	}
	
	
	// Zamger sesija
	if ($login == "") {
		if (isset($_SESSION['login'])) $login = db_escape($_SESSION['login']);
	}
	
	// Ako je $login prazno, znači da autentikacija nije uspjela
	// $userid neće biti setovano, pa će index.php prikazati grešku
	if (!preg_match("/[a-zA-Z0-9]/", $login)) return;

	$q1 = db_query("select id, admin, UNIX_TIMESTAMP(posljednji_pristup) from auth where login='$login'");
	if (db_num_rows($q1)>0) {
		$userid = db_result($q1,0,0);
		$admin = db_result($q1,0,1);
		$posljednji_pristup = db_result($q1,0,2);
		$q2 = db_query("update auth set posljednji_pristup=NOW() where id=$userid");
		
		$privilegije = db_query_varray("select privilegija from privilegije where osoba=$userid");
		
		if ($conf_keycloak && isset($_GET['code'])) {
			// Nećemo nigdje drugo znati da je ovo login event
			zamgerlog("login",1); // nivo 1 = posjeta stranici
			zamgerlog2("login");
		}
	}
}


// Prekid sesije (logout)
function logout() {
	global $conf_cas, $conf_keycloak, $conf_script_path;
	if ($conf_cas) {
		require "$conf_script_path/vendor/autoload.php"; // phpcas
		phpCAS::logout();
	}
	
	$_SESSION = array();
	if (isset($_COOKIE[session_name()])) {
		setcookie(session_name(), '', time()-42000, '/');
	}
	session_destroy();
	
	if ($conf_keycloak)
		OAuth2Helper::logOut();
}


// ----------------------------
// INTERNO KORIŠTENE FUNKCIJE
// ----------------------------

// LDAP ne trpi određene karaktere u loginu
function zamger_ldap_escape($str){
	$metaChars = array('\\', '(', ')', '#', '*');
	$quotedMetaChars = array();
	foreach ($metaChars as $key => $value) $quotedMetaChars[$key] = '\\'.dechex(ord($value));
	$str=str_replace($metaChars,$quotedMetaChars,$str); //replace them
	return ($str);
}


// Provjera da li je korisničko ime alias
// FIXME samo za zimbru
function is_alias($results) {
	if (is_array($results))	foreach ($results as $k1=>$v1) {
		if ($k1 === "objectclass") foreach ($v1 as $k2=>$v2) {
			if ($v2 === "zimbraAlias") return true;
		}
	}
	return false;
}

?>
