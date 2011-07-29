<?php

// Modul: lms/moodle
// Klasa: MoodleConfig
// Opis: konfiguracija Moodle servera


require_once(Config::$backend_path."core/DB.php");

class MoodleConfig {
	public static $moodle = true;
	
	// URL do početne Moodle stranice (bez index.php i slično, samo direktorij,
	// obavezno kosa crta na kraju)
	public static $url = "http://c2.etf.unsa.ba/";
	
	// MySQL baza u kojoj se nalaze moodle tabele
	public static $db = "moodle2";
	
	// Prefiks moodle tabela. U default Moodle instalaciji to je "mdl_"
	public static $db_prefix = "mdl_";
	
	// Ako se Moodle baza nalazi na istom MySQL serveru kao i Zamger i isti korisnik
	// ima SELECT privilegije nad tim tabelama, postavite vrijednost ispod na true
	// U suprotnom koristite false
	public static $reuse_connection = true;
	
	// Ako je gornja vrijednost bila false, podesite ostale parametre pristupa
	// Moodle bazi (naziv baze je $conf_moodle_db iznad)
	public static $dbhost = "localhost";
	public static $dbuser = "zamgerdemo";
	public static $dbpass = "zamgerdemo";
}

?>
