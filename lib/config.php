<?

// LIB/CONFIG - default konfiguracija za zamger

// v3.9.1.0 (2008/02/12) + Pocetak
// v3.9.1.1 (2008/09/05) + Dodane opcije: $conf_naziv_intitucije + skraceni oblik, $conf_site_url, $conf_promjena_sifre

$conf_naziv_institucije = "Elektrotehnički fakultet Sarajevo";
$conf_skr_naziv_institucije = "ETF";
$conf_skr_naziv_institucije_genitiv = "ETFa";

$conf_dbhost = "195.130.59.135";
$conf_dbuser = "zamgerdemo";
$conf_dbpass = "zamgerdemo";
$conf_dbdb = "zamgerdemo";

$conf_dbdb_moodle = "arnes_moodle";

$conf_site_url = "http://195.130.59.135/zamger-demo/";
$conf_dbdb_moodle_url= "http://arnes.inashost.biz/moodle/";

$conf_files_path = "/var/www/zamger";
$conf_script_path = "/var/www/html/zamger-demo";

$conf_appname = "ZAMGER";
$conf_appversion = "4.1.1";

//$conf_system_auth = "ldap";
$conf_system_auth = "table";

// LDAP stuff:
$conf_ldap_server = "localhost";
$conf_ldap_domain = "@moja.domena.ba"; // string koji se dodaje na uid da bi se dobila email adresa
// Vidjeti funkciju gen_ldap_uid() u lib/zamger.php!!!

// URL za promjenu sifre (u slucaju LDAPa) - ne koristi se trenutno
$conf_promjena_sifre = "<a href=\"promjena-sifre.php\" target=\"_blank\">promjena šifre</a>";

$conf_use_mysql_utf8 = true; // potreban mysql 5.0+

$conf_debug=1; // razne debug poruke
$_lv_["debug"]=1; // ovo je za libvedran



?>
