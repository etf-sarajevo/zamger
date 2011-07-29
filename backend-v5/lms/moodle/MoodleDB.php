<?php

// Modul: lms/moodle
// Klasa: MoodleDB
// Opis: konekcija na Moodle bazu


require_once(Config::$backend_path."core/DB.php");
require_once(Config::$backend_path."lms/moodle/MoodleConfig.php");

class MoodleDB {
	public $connection;
	public static $the_connection;

	function connect() {
		$this->connection = DB::$the_connection;
		MoodleDB::$the_connection = DB::$the_connection;
		if (!MoodleConfig::$reuse_connection) {
			// Pravimo novu konekciju za moodle, kod iz dbconnect2() u libvedran
			if (!($this->connection = mysql_connect(MoodleConfig::$dbhost, MoodleConfig::$dbuser, MoodleConfig::$dbpass))) {
				biguglyerror(mysql_error());
				exit;
			}
			MoodleDB::$the_connection = $this->connection;
			if (!mysql_select_db(MoodleConfig::$db, $this->connection)) {
				biguglyerror(mysql_error());
				exit;
			}
			if (Config::$use_mysql_utf8) {
				mysql_set_charset("utf8", $this->connection);
			}
		}
	}
	
	function disconnect() {
		if (!MoodleConfig::$reuse_connection) mysql_close($this->connection);
	}
	
	public static function query($query) {
		if ($r = mysql_query($query, MoodleDB::$the_connection)) {
			return $r;
		}
		
		# Error handling
		if (Config::$debug) {
			print "<br /><hr /><br />MYSQL query:<br /><pre>".$query."</pre><br />MYSQL error:<br /><pre>".mysql_error(MoodleDB::$the_connection)."</pre>\n\n Backtrace:<br /><ul>\n";
			$count=1;
			foreach (debug_backtrace() as $bt) {
				print $count++." : ";
				$file = $bt["file"];
				if (substr($file, 0, strlen(Config::$backend_path)) == Config::$backend_path) $file = "BACKEND: ".substr($file, strlen(Config::$backend_path));
				if (substr($file, 0, strlen(Config::$frontend_path)) == Config::$frontend_path) $file = "FRONTEND: ".substr($file, strlen(Config::$frontend_path));
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
}


?>