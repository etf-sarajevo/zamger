<?php

// Modul: core
// Klasa: Config
// Opis: ovdje ćemo za sada nabacati konfiguraciju svega i svačega... popraviti!

// Ovaj fajl treba biti iskopiran u sve frontende uz moguće neke modifikacije

// Osnovna ideja je sljedeća: ko god bude require-ovao ostale klase "zna" gdje se nalazi klasa Config
// i već je odradio require ovog fajla. Na taj način se rješava koka-jaje problem sa varijablom
// $backend_path

class Config {
	// Standardne stvari
	public static $debug = true;

	// Konfiguracija backenda
	public static $backend_path = "";
	public static $backend_file_path = "";
	public static $backend_url = ""; // API URL - nije još napravljeno

	public static $dbhost = "localhost";
	public static $dbuser = "root";
	public static $dbpass = "";
	public static $dbdb = "zamger";

	public static $use_mysql_utf8 = true;

	// Konfiguracija frontenda
	public static $frontend_path = "";
	public static $frontend_url = "https://zamger.etf.unsa.ba/v5/frontend-v5";

	// Konfiguracija RSS frontenda
	public static $rss_url = "https://zamger.etf.unsa.ba/v5/rss-v5/index.php";
}

?>
