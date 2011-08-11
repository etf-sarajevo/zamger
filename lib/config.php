<?php
	if (ini_get("short_open_tag") != 1) exit;
?>
<?

// LIB/CONFIG - default konfiguracija za zamger




// Opšte informacije o instituciji na kojoj se koristi Zamger
$conf_naziv_institucije = "Elektrotehnički fakultet Sarajevo";
$conf_skr_naziv_institucije = "ETF";
$conf_skr_naziv_institucije_genitiv = "ETFa";

// Pristupni podaci za bazu podataka
$conf_dbhost = "localhost";
$conf_dbuser = "zamgerdemo";
$conf_dbpass = "zamgerdemo";
$conf_dbdb = "zamgerdemo";

// Ovaj dio je potreban za generisanje linkova, mada su u principu linkovi relativni
$conf_site_url = "http://195.130.59.135/zamger-demo/";

// Lokacija na disku gdje je Zamger instaliran
$conf_script_path = "/var/www/html/zamger-demo";

// Lokacija gdje Zamger drži privremene datoteke
// PAZITE da web server korisnik (npr. apache, nobody i slični) ima pravo pisanja
// u ovaj direktorij, te da se direktorij ne može "nasurfati" (pristupiti mu kroz
// web preglednik)
$conf_files_path = "/var/www/zamger";

// Podaci koji se ispisuju u gornjem desnom uglu svake stranice :)
$conf_appname = "ZAMGER";
$conf_appversion = "4.1.1";

// Gdje su smještene šifre korisnika?
// "table" - u tabeli auth zamgerove baze podataka
// "ldap" - na LDAP serveru; ako izaberete ovu opciju, promjena šifre je onemogućena
//$conf_system_auth = "ldap";
$conf_system_auth = "table";

// Pristupni podaci za LDAP
// Zamger će koristiti anonimni pristup
$conf_ldap_server = "localhost";
// string koji se dodaje na uid da bi se dobila email adresa
// Vidjeti funkciju gen_ldap_uid() u lib/zamger.php!!!
$conf_ldap_domain = "@moja.domena.ba"; 

// Pošto se ne može šifra promijeniti kroz Zamger kada se koristi LDAP,
// ovdje postavite URL za promjenu šifre
$conf_promjena_sifre = "<a href=\"promjena-sifre.php\" target=\"_blank\">promjena šifre</a>";

// Ako je MySQL verzija < 5.0 postavite ovo na false
$conf_use_mysql_utf8 = true;

// Da li ispisivati debugging poruke?
$conf_debug = true;
$_lv_["debug"] = true; // libvedran debugging poruke


// ------ MOODLE INTEGRACIJA ---------

// Postavite parametar ispod na true ako želite integraciju sa Moodle serverom
// Ako je vrijednost false, ostale parametre u ovoj sekciji možete zanemariti
$conf_moodle = true;

// URL do početne Moodle stranice (bez index.php i slično, samo direktorij,
// obavezno kosa crta na kraju)
$conf_moodle_url = "http://195.130.59.135/moodle/";

// MySQL baza u kojoj se nalaze moodle tabele
$conf_moodle_db = "moodledemo";

// Prefiks moodle tabela. U default Moodle instalaciji to je "mdl_"
$conf_moodle_prefix = "mdl_";

// Ako se Moodle baza nalazi na istom MySQL serveru kao i Zamger i isti korisnik
// ima SELECT privilegije nad tim tabelama, postavite vrijednost ispod na true
// U suprotnom koristite false
$conf_moodle_reuse_connection = true;

// Ako je gornja vrijednost bila false, podesite ostale parametre pristupa
// Moodle bazi (naziv baze je $conf_moodle_db iznad)
$conf_moodle_dbhost = "localhost";
$conf_moodle_dbuser = "zamgerdemo";
$conf_moodle_dbpass = "zamgerdemo";



?>
