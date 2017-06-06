<?php

// Session klasa reguliše sve što se tiče logina
// Trenutno je ova klasa identična frontend-v5 implementaciji sesije, ali u budućnosti
// bi se trebao koristiti OAuth

// Klasa je defacto singleton (u PHPu je besmisleno praviti singletone:
// https://stackoverflow.com/questions/4595964/is-there-a-use-case-for-singletons-with-database-access-in-php/4596323#4596323 )


require_once(Config::$backend_path."lib/DB.php");

class Session {
	public static $userid; // Pozitivan integer unique ID korisnika
	public static $username;
	public static $admin; // Boolean
	public static $lastAccess; // Vrijeme posljednjeg pristupa
	public static $privileges; // Niz sa stringovima privilegija
	
	// Parametri:
	//    login
	//    password - koji će biti provjeren ako se koristi password autentikacija.
	//    type     - autentikacijski backend (cas, ldap, table...)
	// Vraća:
	//    0 - OK
	//    1 - nepoznat korisnik
	//    2 - pogrešan password

	public static function login($login, $pass, $type = "") {
		if ($type === "") $type = Config::$system_auth;

		$q1 = DB::query("select id, password, admin, UNIX_TIMESTAMP(posljednji_pristup) from auth where login='$login' and aktivan=1");
		if (DB::num_rows($q1)<=0)
			return 1;
		
		if ($type == "cas") {
			// Do nothing
		}

		if ($type == "ldap") {
			$ds = ldap_connect(Config::$ldap_server);
			ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
			if ($ds) {
				if (ldap_bind($ds)) {
					$i=0;

					// Probavamo UID
					$login = zamger_ldap_escape($login);
					$sr = ldap_search($ds, "", "uid=$login", array() /* just dn */ );
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
						$sr = ldap_search($ds, "", "mail=$login", array() );
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
						$sr = ldap_search($ds, "", "mail=$login$conf_ldap_domain", array() );
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
			$result = DB::result($q1,0,1);
			if ($pass != $result || $result === "") return 2;
		}

		Session::$username = $login;
		Session::$userid = intval(DB::result($q1,0,0));
		Session::$admin = DB::result($q1,0,2);
		Session::$lastAccess = DB::result($q1,0,3);
		DB::query("update auth set posljednji_pristup=NOW() where id=".Session::$userid);

		// All OK, start session
		session_start();
		//session_regenerate_id(); // prevent session fixation
		$_SESSION['login']=$login;
		session_write_close();
	}


	// Provjera da li trenutni korisnik ima važeću sesiju
	public static function verify() {
		Session::$username="";
		Session::$userid=0;
		Session::$admin=0;

		if (Config::$system_auth == "cas") {
			require("lib/phpcas/CAS.php");
			phpCAS::setDebug();
			phpCAS::client(CAS_VERSION_2_0, Config::$cas_server, Config::$cas_port, Config::$cas_context);
			phpCAS::setNoCasServerValidation();
			phpCAS::forceAuthentication();
			$login = phpCAS::getUser();
		} else {
			session_start();
			if (isset($_SESSION['login'])) $login = DB::escape($_SESSION['login']); else return;
		}
		
		if (!preg_match("/[a-zA-Z0-9]/",$login)) return;

		$q1 = DB::query("select id, admin, UNIX_TIMESTAMP(posljednji_pristup) from auth where login='$login'");
		if (DB::num_rows($q1)>0) {
			Session::$username = $login;
			Session::$userid = intval(DB::result($q1,0,0));
			if (DB::result($q1,0,1) == 1) Session::$admin = true; else Session::$admin = false;
			Session::$lastAccess = DB::result($q1,0,2);
			DB::query("update auth set posljednji_pristup=NOW() where id=".Session::$userid);
		}
	}


	// Prekid sesije (logout)
	public static function logout() {
		if (Config::$system_auth == "cas") {
			require("lib/phpcas/CAS.php");
			phpCAS::logout();
		} else {
			$_SESSION = array();
			if (isset($_COOKIE[session_name()])) {
				setcookie(session_name(), '', time()-42000, '/');
			}
			session_destroy();
		}
	}

	
	public static function getCoarsePrivileges() {
		Session::$privileges = array();
		if (Session::$userid>0)
			Session::$privileges = DB::query_varray("select privilegija from privilegije where osoba=".Session::$userid);
	}
	

	// ----------------------------
	// INTERNO KORIŠTENE FUNKCIJE
	// ----------------------------

	// LDAP ne trpi određene karaktere u loginu
	public static function zamger_ldap_escape($str){
		$metaChars = array('\\', '(', ')', '#', '*');
		$quotedMetaChars = array();
		foreach ($metaChars as $key => $value) $quotedMetaChars[$key] = '\\'.dechex(ord($value));
		$str=str_replace($metaChars,$quotedMetaChars,$str); //replace them
		return ($str);
	}


	// Provjera da li je korisničko ime alias
	// FIXME samo za zimbru
	public static function is_alias($results) {
		if (is_array($results))	foreach ($results as $k1=>$v1) {
			if ($k1 === "objectclass") foreach ($v1 as $k2=>$v2) {
				if ($v2 === "zimbraAlias") return true;
			}
		}
		return false;
	}
}

?>
