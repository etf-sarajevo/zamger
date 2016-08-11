<?

// COMMON/SLIKA - vrlo jednostavan modul za prikaz slike osobe iz sakrivenog foldera

// Pristup svim slikama ima svaki logirani korisnik
// Ne vidim zašto tako ne bi bilo



function common_slika() {

global $conf_files_path, $user_nastavnik, $user_studentska, $user_siteadmin, $userid;

// Poslani parametar

$osoba=intval($_REQUEST['osoba']);
$promjena=intval($_REQUEST['promjena']);


// Studenti mogu vidjeti samo svoju sliku

if (!$user_nastavnik && !$user_studentska && !$user_siteadmin && $osoba != $userid) {
	niceerror("Možete vidjeti samo svoju sliku");
	zamgerlog("pristupa slici za osobu $osoba a student je", 3);
	zamgerlog2("pristupa tudjoj slici a student je", $osoba);
	return;
}


if ($promjena==1)
	$q = db_query("select slika from promjena_podataka where osoba=$osoba");
else
	$q = db_query("select slika from osoba where id=$osoba");
if (db_num_rows($q)<1) {
	// Ova poruka se neće vidjeti iz <img> taga, ali neko može otvoriti sliku u posebnom prozoru/tabu
	niceerror("Nepostojeća osoba $osoba");
	zamgerlog("slika: nepostojeca osoba $osoba",3);
	zamgerlog2("nepostojeca osoba", $osoba);
	return;
}

$slika = db_result($q,0,0);
if ($slika=="") {
	niceerror("Osoba $osoba nema sliku");
	zamgerlog("osoba u$osoba nema sliku",3);
	zamgerlog2("osoba nema sliku", $osoba);
	return;
}

$lokacija_slike = "$conf_files_path/slike/$slika";
if (!file_exists($lokacija_slike)) {
	niceerror("Slika za osobu $osoba je definisana, ali datoteka ne postoji");
	zamgerlog("nema datoteke za sliku osobe u$osoba",3);
	zamgerlog2("nema datoteke za sliku", $osoba);
	return;
}

// Odredjujemo mimetype
$podaci = getimagesize($lokacija_slike);
$mimetype = image_type_to_mime_type($podaci[2]);
if ($mimetype=="") {
	niceerror("Nepoznat tip slike za osobu $osoba");
	zamgerlog("nepoznat tip slike za osobu u$osoba",3);
	zamgerlog2("nepoznat tip slike", $osoba);
	return;
}


header("Content-Type: $mimetype");

$k = readfile($lokacija_slike,false);
if ($k == false) {
	//print "Otvaranje slike nije uspjelo! Kontaktirajte administratora";
	// Pošto je header već poslan, nema smisla ispisivati grešku
	zamgerlog("citanje fajla za sliku nije uspjelo u$osoba", 3);
	zamgerlog2("citanje fajla za sliku nije uspjelo", $osoba);
}
exit;

}

?>
