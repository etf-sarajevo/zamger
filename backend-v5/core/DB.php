<?php

// Modul: core
// Klasa: DB
// Opis: Baza podataka


require_once(Config::$backend_path."core/Logging.php");

class DB {
	public $connection;
	public static $the_connection;

	function connect() {
		if (!($this->connection = mysql_connect(Config::$dbhost, Config::$dbuser, Config::$dbpass))) {
			if (Config::$debug) biguglyerror(mysql_error());
			exit;
		}
		DB::$the_connection = $this->connection;
		if (!mysql_select_db(Config::$dbdb)) {
			if (Config::$debug) biguglyerror(mysql_error());
			exit;
		}
		if (Config::$use_mysql_utf8) {
			mysql_set_charset("utf8");
		}
	}
	
	function disconnect() {
		mysql_close($this->connection);
	}
	
	public static function query($query) {
		if ($r = @mysql_query($query)) {
			return $r;
		}
		
		# Error handling
		if (Config::$debug) {
			print "<br /><hr /><br />MYSQL query:<br /><pre>".$query."</pre><br />MYSQL error:<br /><pre>".mysql_error()."</pre>\n\n Backtrace:<br /><ul>\n";
			$count=1;
			foreach (debug_backtrace() as $bt) {
				print $count++." : ";
				$file = $bt["file"];
				if (substr($file, 0, strlen(Config::$backend_path)) == $backend_path) $file = "BACKEND: ".substr($file, strlen(Config::$backend_path));
				if (substr($file, 0, strlen(Config::$frontend_path)) == $frontend_path) $file = "FRONTEND: ".substr($file, strlen(Config::$frontend_path));
				print "$file::".$bt["line"]." => ".$bt["class"].$bt["type"].$bt["function"]."(";
				$k=1;
				foreach ($bt["args"] as $arg) { 
					if ($k!=1) print ",";
					$k=0;
					print "\"$arg\"";
				}
				print ")<br />\n";
			}
			print "</ul>\n";
		}
		$backtrace = debug_backtrace();
		$file = substr($backtrace[0]['file'], strlen($backtrace[0]['file'])-20);
		$line = intval($backtrace[0]['line']);
		Logging::log("SQL greska ($file : $line):".mysql_error(), 3);
		exit;
	}

	// Escape stringova radi koristenja u mysql upitima - kopirao sa php.net
	public static function my_escape($value) {
		// Convert special HTML chars to protect against XSS
		// If chars are needed for something, escape manually
		$value = htmlspecialchars($value);
	
		// If magic quotes is on, stuff would be double-escaped here
		if (get_magic_quotes_gpc()) {
			$value = stripslashes($value);
		}
	
		// Quote if not a number or a numeric string
		if (!is_numeric($value)) {
			$value = mysql_real_escape_string($value); // Detecting quotes later is a pain
		}
		return $value;
	}
}



// From php.net
if (function_exists('mysql_set_charset') === false) {
     /**
      * Sets the client character set.
      *
      * Note: This function requires MySQL 5.0.7 or later.
      *
      * @see http://www.php.net/mysql-set-charset
      * @param string $charset A valid character set name
      * @param resource $link_identifier The MySQL connection
      * @return TRUE on success or FALSE on failure
      */
     function mysql_set_charset($charset, $link_identifier = null)
     {
         if ($link_identifier == null) {
             return mysql_query('SET NAMES "'.$charset.'"');
//             return mysql_query('SET CHARACTER SET "'.$charset.'"');
         } else {
             return mysql_query('SET NAMES "'.$charset.'"', $link_identifier);
//             return mysql_query('SET CHARACTER SET "'.$charset.'"', $link_identifier);
         }
     }
 }


?>