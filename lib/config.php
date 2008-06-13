<?

// LIB/CONFIG - default konfiguracija za zamger

// v3.9.1.0 (2008/02/12) + Pocetak

$conf_dbhost = "localhost";
$conf_dbuser = "root";
$conf_dbpass = "";
$conf_dbdb = "zamger";

$conf_site_url = "https://zamger.etf.unsa.ba";

$conf_files_path = "/srv/httpd/zamger";
$conf_script_path = "/srv/httpd/zamger/www";

$conf_appname = "ZAMGER";
$conf_appversion = "4.0 beta2";

//$conf_system_auth = "ldap";
$conf_system_auth = "table";

// LDAP stuff:
$conf_ldap_server = "localhost";
$conf_ldap_domain = "@moja.domena.ba"; // string koji se dodaje na uid da bi se dobila email adresa
// Vidjeti funkciju gen_ldap_uid() u lib/zamger.php!!!

$conf_use_mysql_utf8 = true; // potreban mysql 5.0+

$conf_debug=1; // razne debug poruke
$_lv_["debug"]=1; // ovo je za libvedran



?>
