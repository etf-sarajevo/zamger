<?php


// Dodavanje nove osobe u bazu

function studentska_osobe_nova() {
	global $conf_ldap_search, $conf_ldap_server, $conf_ldap_dn, $conf_ldap_domain;
	
	$ime = substr(db_escape($_POST['ime']), 0, 100);
	if (!preg_match("/\w/", $ime)) {
		niceerror("Ime nije ispravno");
		return;
	}
	
	$prezime = substr(db_escape($_POST['prezime']), 0, 100);
	
	// Probamo tretirati ime kao LDAP UID
	if ($conf_ldap_search) {
		// TODO: smanjiti duplikaciju koda za LDAP!
		$uid = $ime;
		$ds = ldap_connect($conf_ldap_server);
		ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
		if ($ds && ldap_bind($ds)) {
			$sr = ldap_search($ds, $conf_ldap_dn, "uid=$uid", array("givenname","sn") );
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
				zamgerlog2("korisnik nije pronadjen na LDAPu", 0, 0, 0, $uid);
				$uid = "";
				niceerror("Korisnik nije pronadjen na LDAPu... dodajem novog!");
			}
		} else {
			zamgerlog("ne mogu kontaktirati LDAP server",3);
			zamgerlog2("ne mogu kontaktirati LDAP server");
			niceerror("Ne mogu kontaktirati LDAP server... pravim se da ga nema :(");
		}
	}
	
	if (!preg_match("/\w/", $prezime)) {
		niceerror("Prezime nije ispravno");
		return;
	}
	
	// Da li ovaj korisnik već postoji u osoba tabeli?
	$q10 = db_query("select id, ime, prezime from osoba where ime like '$ime' and prezime like '$prezime'");
	if ($r10 = db_fetch_row($q10)) {
		zamgerlog("korisnik vec postoji u bazi ('$ime' '$prezime' - ID: $r10[0])",3);
		zamgerlog2("korisnik vec postoji u bazi", intval($r10[0]), 0, 0, "'$ime' '$prezime'");
		niceerror("Korisnik već postoji u bazi:");
		print "<br><a href=\"?sta=studentska/osobe&akcija=edit&osoba=$r10[0]\">$r10[1] $r10[2]</a>";
		return;
		
	} else {
		// Nije u tabeli, dodajemo ga...
		$q30 = db_query("select id from osoba order by id desc limit 1");
		$osoba = db_result($q30,0,0)+1;
		
		$q40 = db_query("insert into osoba set id=$osoba, ime='$ime', prezime='$prezime', naucni_stepen=6, strucni_stepen=5");
		// 6 = bez naučnog stepena, 5 = srednja stručna sprema
		
		if ($conf_ldap_search && $uid != "") {
			// Ako je LDAP onda imamo email adresu
			$email = $uid.$conf_ldap_domain;
			$q33 = db_query("INSERT INTO email SET osoba=$osoba, adresa='$email', sistemska=1");
			// Adresu podešavamo kao sistemsku što znači da je korisnik ne može mijenjati niti brisati
			
			// Mozemo ga dodati i u auth tabelu
			$q35 = db_query("select count(*) from auth where id=$osoba");
			if (db_result($q35,0,0)==0) {
				$q37 = db_query("insert into auth set id=$osoba, login='$uid', admin=1, aktivan=1");
			}
		}
		
		nicemessage("Novi korisnik je dodan.");
		zamgerlog("dodan novi korisnik u$osoba (ID: $osoba)",4); // nivo 4: audit
		zamgerlog2("dodan novi korisnik", $osoba);
		print "<br><a href=\"?sta=studentska/osobe&akcija=edit&osoba=$osoba\">$ime $prezime</a>";
		return;
	}
}
