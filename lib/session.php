<?

// LIB/SESSION - podrška za login/logout na Zamgeru


// Funkcija login pisana je pod pretpostavkom da je $login globalna varijabla
// (iz više razloga)
// Parametri:
//    password - koji će biti provjeren ako se koristi password autentikacija.
//    type     - autentikacijski backend (cas, ldap, table...)
// Vraća:
//    0 - OK
//    1 - nepoznat korisnik
//    2 - pogrešan password
// Globalne varijable:
//    $login  - korisničko ime (mora biti setovana prije poziva funkcije)
//    $admin  - korisnik je administrator
//    $userid - interni ID korisnika (prirodan broj)

function login($pass, $type = "") {
	global $userid,$admin,$login,$conf_system_auth,$conf_ldap_server,$conf_ldap_domain,$conf_ldap_dn,$posljednji_pristup;
	if ($type === "") $type = $conf_system_auth;

	$q1 = db_query("select id, password, admin, UNIX_TIMESTAMP(posljednji_pristup) from auth where login='$login' and aktivan=1");
	if (db_num_rows($q1)<=0)
		return 1;
	
	if ($type == "cas") {
		// Do nothing
	}

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
					// Ako nema na LDAPu probavamo tabelu
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
	session_write_close();
}


// Provjera da li trenutni korisnik ima važeću sesiju
function check_cookie() {
	global $userid,$admin,$login,$conf_system_auth,$posljednji_pristup;

	$userid=0;
	$admin=0;

	if ($conf_system_auth == "cas") {
		global $conf_cas_server, $conf_cas_port, $conf_cas_context;
		require("lib/phpcas/CAS.php");
		phpCAS::setDebug();
		phpCAS::client(CAS_VERSION_2_0, $conf_cas_server, $conf_cas_port, $conf_cas_context);
		phpCAS::setNoCasServerValidation();
		phpCAS::forceAuthentication();
		$login = phpCAS::getUser();
	}
	

	// Single sign-on redirekcija na Keycloak
	else if ($conf_system_auth == "keycloak") {
		require 'vendor/autoload.php';
		global $conf_site_url, $conf_files_path, $conf_keycloak_url, $conf_keycloak_realm, $conf_keycloak_client_id, $conf_keycloak_client_secret;

		session_start();

		$provider = new Stevenmaguire\OAuth2\Client\Provider\Keycloak([
			'authServerUrl'             => $conf_keycloak_url,
			'realm'                     => $conf_keycloak_realm,
			'clientId'                  => $conf_keycloak_client_id,
			'clientSecret'              => $conf_keycloak_client_secret,
			'redirectUri'               => $conf_site_url . '/index.php',
			'encryptionAlgorithm'       => null,
			'encryptionKey'             => null,
			'encryptionKeyPath'         => null
		]);

		$token_file = $conf_files_path . "/keycloak_token/$login";
		$logout_url = $conf_keycloak_url . "/realms/$conf_keycloak_realm/protocol/openid-connect/logout?redirect_uri=" . urlencode($conf_site_url . '/index.php');
		
		if (isset($_SESSION['login']) && file_exists($token_file)) {
			$login = db_escape($_SESSION['login']);
			
			// Provjeravamo da li je sesija još uvijek validna
			$token = unserialize(file_get_contents($token_file));
			if ($token->hasExpired()) {
				// Token je istekao, preuzimamo novi sa servera i zapisujemo ga u fajl
				$newAccessToken = $provider->getAccessToken('refresh_token', [
					'refresh_token' => $token->getRefreshToken()
				]);
				file_put_contents($token_file, serialize($token));
			}
		}

		// Prvi pristup Zamgeru, redirektujemo na login stranicu
		else if (!isset($_GET['code']) ) {
			// Preuzimamo autentikacijski URL i state cookie
			$authUrl = $provider->getAuthorizationUrl();
			$_SESSION['oauth2state'] = $provider->getState();
			header('Location: '.$authUrl);
			exit;

		/*// Check given state against previously stored one to mitigate CSRF attack
		// TODO? Ovo je bitno samo prilikom logina
		} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
			unset($_SESSION['oauth2state']);
			$greska = 'Autentikacija na keycloak neuspjela, kontaktirajte administratora (1)';*/
			
		} else {
			if (isset($_GET['code']))
				$_SESSION['keycloak_code'] = $code = $_GET['code'];
			else
				$code = $_SESSION['keycloak_code'];
			// Try to get an access token (using the authorization coe grant)
			try {
				$token = $provider->getAccessToken('authorization_code', [
					'code' => $code
				]);
				if ($token->hasExpired()) {
					// Redirektujemo na logout url kako bi se korisnik opet prijavio
					header('Location: '.$logout_url);
					exit(0);
				}

				// Od KeyCloak servera dobijamo login
				$user = $provider->getResourceOwner($token);
				$login = $user->toArray()['preferred_username'];

				// Zapisujemo token
				if (!file_exists($conf_files_path . "/keycloak_token"))
					mkdir($conf_files_path . "/keycloak_token");

				file_put_contents($token_file, serialize($token));
				$_SESSION['login'] = $login;

				//print "Login: $login\n";
			} catch (Exception $e) {
				if ($e->getMessage() == "invalid_grant: Code not valid") {
					// Autorizacijski kod je invalidan, vjerovatno jer je sesija istekla
					// Redirektujemo na logout url kako bi se korisnik opet prijavio
					header('Location: '.$logout_url);
					exit(0);
				}
				
				print 'Autentikacija na keycloak neuspjela, kontaktirajte administratora: '.$e->getMessage();
				$login = '';
			}
		}
	}
	

	// Zamger sesija
	else {
		if (isset($_REQUEST['PHPSESSID'])) session_id($_REQUEST['PHPSESSID']);

		session_start();
		if (isset($_SESSION['login'])) $login = db_escape($_SESSION['login']);
	}
	
	if (!preg_match("/[a-zA-Z0-9]/",$login)) return;

	$q1 = db_query("select id, admin, UNIX_TIMESTAMP(posljednji_pristup) from auth where login='$login'");
	if (db_num_rows($q1)>0) {
		$userid = db_result($q1,0,0);
		$admin = db_result($q1,0,1);
		$posljednji_pristup = db_result($q1,0,2);
		$q2 = db_query("update auth set posljednji_pristup=NOW() where id=$userid");
	}
}


// Prekid sesije (logout)
function logout() {
	global $conf_system_auth;
	if ($conf_system_auth == "cas") {
		require("lib/phpcas/CAS.php");
		phpCAS::logout();
	} else {
		$_SESSION = array();
		if (isset($_COOKIE[session_name()])) {
			setcookie(session_name(), '', time()-42000, '/');
		}
		session_destroy();
		if ($conf_system_auth == "keycloak") {
			// Potrebno je da korisnika logoutujemo i na keycloaku
			global $conf_keycloak_url, $conf_keycloak_realm, $conf_site_url;
			$logout_url = $conf_keycloak_url . "/realms/$conf_keycloak_realm/protocol/openid-connect/logout?redirect_uri=" . urlencode($conf_site_url . '/index.php');
			header('Location: ' . $logout_url);
			exit(0);
		}
	}
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
