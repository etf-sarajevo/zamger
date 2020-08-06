<?php if (ini_get("short_open_tag") != 1) exit; ?><?

// LIB/CONFIG - default konfiguracija za zamger




// Opšte informacije o instituciji na kojoj se koristi Zamger
$conf_naziv_institucije = "Elektrotehnički fakultet Sarajevo";
$conf_naziv_institucije_genitiv = "Elektrotehničkog fakulteta Sarajevo";
$conf_skr_naziv_institucije = "ETF";
$conf_skr_naziv_institucije_genitiv = "ETFa";
$conf_logo_institucije = "etf-50x50.png";

// Pristupni podaci za bazu podataka
$conf_dbhost = "localhost";
$conf_dbuser = "root";
$conf_dbpass = "";
$conf_dbdb = "zamger";

// Ovaj dio je potreban za generisanje linkova, mada su u principu linkovi relativni
$conf_site_url = "http://localhost/zamger";

// Lokacija na disku gdje je Zamger instaliran
$conf_script_path = "/var/www/html/zamger";

// Lokacija gdje Zamger drži privremene datoteke
// PAZITE da web server korisnik (npr. apache, nobody i slični) ima pravo pisanja
// u ovaj direktorij, te da se direktorij ne može "nasurfati" (pristupiti mu kroz
// web preglednik)
$conf_files_path = "/home/zamger";

// Podaci koji se ispisuju u gornjem desnom uglu svake stranice :)
$conf_appname = "ZAMGER";
$conf_appversion = "4.3";


// Uslov za upis u narednu godinu (iz zakona)
$conf_uslov_predmeta = 2;
$conf_uslov_ects_kredita = 12;
$conf_uslov_kolizija = 5;


// Broj statusnih uvjerenja i prepisa ocjena koje studenti mogu dobiti besplatno
// Ako je nula, neće se prikazivati nikakve informacije o plaćanju, informišite studente
// na neki drugi način (ili je sve besplatno)
$conf_broj_besplatnih_potvrda = 5;

// Cijena svakog narednog uvjerenja (u KM)
$conf_cijena_potvrde = 2;

// Ako je MySQL verzija < 5.0 postavite ovo na false
$conf_use_mysql_utf8 = true;

// Da li ispisivati debugging poruke?
$conf_debug = true;

// Postavite parametar ispod na true ukoliko želite omogućiti slanje maila, koji sadrzi spisak konacnih ocjena upisanih u posljednja 24h
// U suprotnom, postavite vrijednost na false 
$conf_email = true;

// Mailovi koje šalje zamger će imati ovu vrijednost u From: polju
$conf_admin_email = "vljubovic@etf.unsa.ba";

// Preglednik za sourcecode (moguće vrijednosti: ace, geshi)
$conf_code_viewer = "ace";
//$conf_code_viewer = "geshi";

// Database backend
$conf_dblayer = "mysqli";
//$conf_dblayer = "mysql_";

// Prikazati dnevnik na login stranici (javno)
$conf_javni_dnevnik = true;

// IP adrese banovane od pristupa Zamgeru
// $conf_banned_ips = array("5.43.71.81");
$conf_banned_ips = array();


// =====================================
// AUTENTIKACIJSKI PODSISTEM ZAMGERA
// =====================================

// Gdje su smještene lozinke korisnika?
// "table" - u tabeli 'auth' zamgerove baze podataka
// "ldap" - na LDAP serveru (postaviti $conf_ldap na true)
// Ako koristite login ekran nekog SSO servisa, teoretski ne morate držati lozinke nigdje, ali to nije
// podržano u trenutnoj verziji Zamgera
$conf_passwords = "table";

// Ako je $conf_passwords postavljeno na "table", lozinka se mijenja kroz profil, u suprotnom promjena
// lozinke nije moguća kroz Zamger
// Ovdje postavite URL na kojem se može promijeniti lozinka
$conf_promjena_sifre = "<a href=\"promjena-sifre.php\" target=\"_blank\">promjena šifre</a>";

// Korišteni login ekran
// Interni login ekran Zamgera je uvijek moguće koristiti putem URLa ?sta=public/intro
// ali prijava putem ovog ekrana neće aktivirati SSO sesiju nego običnu internu Zamger sesiju
// Ova opcija određuje gdje će korisnik biti redirektovan u slučaju nevalidne sesije ili otvaranja home stranice
// "internal" - interni Zamger login ekran
// "cas" - CAS server (postaviti $conf_cas na true)
// "keycloak" - KeyCloak server (postaviti $conf_keycloak na true)
$conf_login_screen = "internal";



// =====================================
// LDAP podrška
// =====================================

$conf_ldap = false;

// Domensko ime LDAP servera
$conf_ldap_server = "localhost";

// DN za pretragu (možete probati ostaviti prazan string)
$conf_ldap_dn="cn=users,cn=accounts,dc=moja,dc=domena,dc=ba";

// string koji se dodaje na uid da bi se dobila email adresa
// Vidjeti funkciju gen_ldap_uid() u lib/zamger.php!!!
$conf_ldap_domain = "@moja.domena.ba"; 

// Ako je postavljeno na true, moguća je pretraga LDAP servera prilikom dodavanja novog korisnika
// Pod ime se unosi UID
$conf_ldap_search = false;



// =====================================
// KeyCloak single sign-on server
// =====================================

$conf_keycloak = false;

// URL KeyCloak servera
$conf_keycloak_url = "https://keycloak.moja.domena.ba/auth";

// KeyCloak realm (vidjeti KC dokumentaciju)
$conf_keycloak_realm = "myrealm";

// KeyCloak Client ID (vidjeti KC dokumentaciju)
$conf_keycloak_client_id = "zamger";

// KeyCloak Client secret (po defaultu prazan string, vidjeti KC dokumentaciju)
$conf_keycloak_client_secret = "";



// =====================================
// JasperReports server
// =====================================

$conf_jasper = false;

// Bazni URL na koji se nadovezuje /flow.html?....
$conf_jasper_url = "https://jasper.moja.domena.ba";



// =====================================
// CAS single sign-on server
// =====================================

$conf_cas = false;

$conf_cas_server = ""; // hostname CAS servera, ne možete koristiti localhost (mora biti FQDN)
$conf_cas_port = 443; // CAS uvijek koristi HTTPS
$conf_cas_context = "cas"; // dio url-a iza hostname


// =====================================
// Moodle integracija
// =====================================


// Postavite parametar ispod na true ako želite integraciju sa Moodle serverom

// Ako je vrijednost false, ostale parametre u ovoj sekciji možete zanemariti

$conf_moodle = false;


// URL do početne Moodle stranice (bez index.php i slično, samo direktorij,
// obavezno kosa crta na kraju)
$conf_moodle_url = "http://localhost/moodle/";

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


// =====================================
// ISSS integracija (NewWS web servis)
// =====================================

// Izvozni format: izvoz na ISSS novi web servis (NewWS)
//$conf_export_format = "isss-newws";
$conf_export_format = "";
$conf_export_isss_url = "https://isss.ba/NewWS/";
$conf_export_isss_id_fakulteta = 3;

// Ko otvara termine za prijavu ispita u ISSSu?
//   true = export operacija automatski kreira termine
//   false = studentska služba ručno kreira termine
$conf_export_isss_kreiraj_ispite = false;


// =====================================
// Knjigovodstveni web servis 
// (integracija sa eksternom aplikacijom za knjigovodstvo)
// =====================================

$conf_knjigovodstveni_servis = false;

// URLovi web servisa za plaćanje
// $conf_url_daj_karticu = "http://80.65.65.68:8080/WebService1.asmx/dajKarticuStudenta";
// $conf_url_upisi_zaduzenje = "http://80.65.65.68:8080/WebService1.asmx/UpisiZaduzenje";
$conf_url_daj_karticu = "";
$conf_url_upisi_zaduzenje = "";



?>
