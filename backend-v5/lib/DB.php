<?php

// Modul: lib
// Klasa: DB
// Opis: Baza podataka


// Klasa je defacto singleton (u PHPu je besmisleno praviti singletone:
// https://stackoverflow.com/questions/4595964/is-there-a-use-case-for-singletons-with-database-access-in-php/4596323#4596323 )

require_once(Config::$backend_path."lib/Logging.php");

class DB {
	public static $the_connection;
	public static $error;

	public static function connect() {
		if (!(DB::$the_connection = mysqli_connect(Config::$dbhost, Config::$dbuser, Config::$dbpass, Config::$dbdb))) {
			throw new Exception("Database connection error", "801");
		}
		DB::$the_connection = DB::$the_connection;
		if (Config::$use_mysql_utf8) {
			mysqli_set_charset(DB::$the_connection, "utf8");
		}
	}
	
	public static function disconnect() {
		mysqli_close(DB::$the_connection);
	}
	
	public static function query($query) {
		$error = array();
		if ($r = @mysqli_query(DB::$the_connection, $query)) {
			return $r;
		}
		
		$the_error = mysqli_error(DB::$the_connection);
		$backtrace = debug_backtrace();

		DB::$error = array();
		DB::$error['query'] = $query;
		DB::$error['error'] = $the_error;
		DB::$error['backtrace'] = $backtrace;
		
		$file = substr($backtrace[0]['file'], strlen($backtrace[0]['file'])-20);
		$line = intval($backtrace[0]['line']);
		Logging::log("SQL greska ($file : $line): $the_error", 3);
		Logging::log2("SQL greska", 0, 0, 0, "$file:$line: $the_error");
		throw new Exception("Error in SQL query", "800");
	}


	// Escape stringova radi koristenja u mysql upitima
	// Zamger slijedi filozofiju da se podaci escapuju samo jednom, prilikom dodavanja u bazu,
	// a sve što je u bazi je sigurno za direktan prikaz na stranici
	// Samim time postoji samo jedna "idealna" escape funkcija
	public static function escape($value) {
		// Convert special HTML chars to protect against XSS
		// If chars are needed for something, escape manually
		$value = htmlspecialchars($value);
	
		// If magic quotes is on, stuff would be double-escaped here
		if (get_magic_quotes_gpc()) {
			$value = stripslashes($value);
		}
	
		// Quote if not a number or a numeric string
		if (!is_numeric($value)) {
			$value = mysqli_real_escape_string(DB::$the_connection, $value); // Detecting quotes later is a pain
		}
		return $value;
	}


	// Reimplementacija mysql_ funkcija
	public static function fetch_row($res) { return mysqli_fetch_row($res); }
	public static function fetch_assoc($res) { return mysqli_fetch_assoc($res); }
	public static function num_rows($res) { return mysqli_num_rows($res); }
	public static function insert_id() { return mysqli_insert_id(DB::$the_connection); }
	public static function affected_rows() { return mysqli_affected_rows(DB::$the_connection); }
	public static function escape_string($s) { return mysqli_real_escape_string(DB::$the_connection, $s); }
	public static function free_result($res) { return mysqli_free_result($res); }

	// Reimplementacija mysql_result sa mysqli (bez provjere ispravnosti parametara)
	public static function result($res, $row, $col) { 
		mysqli_data_seek($res, $row);
		$resrow = mysqli_fetch_row($res);
		return $resrow[$col];
	}




	// Novi API koji implementira neke česte patterne u kodu Zamgera

	// db_get: Vrši SQL upit i vraća vrijednost u prvom redu i prvoj koloni (ili false)
	// Primjer: $broj_studenata = db_get("SELECT COUNT(*) FROM studenti");
	public static function get($query) {
		$res = DB::query($query);
		if (DB::num_rows($res) == 0) return false;
		$row = mysqli_fetch_row($res); // Najbrži način da se vrati prvi rezultat sa mysqli ekstenzijom
		return $row[0];
	}

	// db_query_assoc: Vrši SQL upit i vraća prvi red kao asocijativni niz (ili false)
	// Primjer: $student = db_query_assoc("SELECT ime, prezime FROM studenti WHERE id=1");
	public static function query_assoc($query) {
		$res = DB::query($query);
		if (DB::num_rows($res) == 0) return false;
		return DB::fetch_assoc($res);
	}

	// db_query_varray: Vrši SQL upit i vraća prvu kolonu rezultata kao niz
	// Primjer: $imena = db_query_varray("SELECT ime FROM studenti WHERE godina=2016");
	public static function query_varray($query) { 
		$result = array();
		$res = DB::query($query);
		while($r = DB::fetch_row($res)) $result[] = $r[0];
		return $result;
	}

	// db_query_vassoc: Vraća asocijativni niz gdje je prva kolona ključ a druga vrijednost
	// Primjer: $imena = db_query_vassoc("SELECT id, ime FROM studenti WHERE godina=2016");
	public static function query_vassoc($query) { 
		$result = array();
		$res = DB::query($query);
		while($r = DB::fetch_row($res)) $result[$r[0]] = $r[1];
		return $result;
	}

	// db_query_table: Vrši upit i raća sve rezultate upita kao niz asocijativnih nizova
	// Primjer: $tabela = db_query_table("SELECT id, ime, prezime, godina FROM studenti ORDER BY prezime, ime");
	public static function query_table($query) {
		$result = array();
		$res = DB::query($query);
		while($r = DB::fetch_assoc($res)) $result[] = $r;
		return $result;
	}


	// db_fetch1 (,2,3,4...): Vraća rezultate u varijable preko referenci
	// Primjer: while(db_fetch2($r, $ime, $prezime)) { print "Ime: $ime Prezime: $prezime<br>"; }

	// Razlog je što se asocijativni nizovi ne interpoliraju fino u stringove, a obični nizovi su nečitljivi
	// Primjeri: 
	//   while($row = db_fetch_assoc($r)) { print "Ime: ".$row['ime']." Prezime: ".$row['prezime']."<br>"; } // Ružno
	//   while($row = db_fetch_row($r)) { print "Ime: $row[0] Prezime: $row[1]<br>"; } // Još ružnije

	public static function fetch1($res, &$a) { $r = DB::fetch_row($res); if ($r) $a=$r[0]; return $r; }
	public static function fetch2($res, &$a, &$b) { $r = DB::fetch_row($res); if ($r) { $a=$r[0]; $b=$r[1]; } return $r; }
	public static function fetch3($res, &$a, &$b, &$c) { $r = DB::fetch_row($res); if ($r) { $a=$r[0]; $b=$r[1]; $c=$r[2]; } return $r; }
	public static function fetch4($res, &$a, &$b, &$c, &$d) { $r = DB::fetch_row($res); if ($r) { $a=$r[0]; $b=$r[1]; $c=$r[2]; $d=$r[3]; } return $r; }
	public static function fetch5($res, &$a, &$b, &$c, &$d, &$e) { $r = DB::fetch_row($res); if ($r) { $a=$r[0]; $b=$r[1]; $c=$r[2]; $d=$r[3]; $e=$r[4]; } return $r; }
	public static function fetch6($res, &$a, &$b, &$c, &$d, &$e, &$f) { $r = DB::fetch_row($res); if ($r) { $a=$r[0]; $b=$r[1]; $c=$r[2]; $d=$r[3]; $e=$r[4]; $f=$r[5]; } return $r; }
	public static function fetch7($res, &$a, &$b, &$c, &$d, &$e, &$f, &$g) { $r = DB::fetch_row($res); if ($r) { $a=$r[0]; $b=$r[1]; $c=$r[2]; $d=$r[3]; $e=$r[4]; $f=$r[5]; $g=$r[6]; } return $r; }



	// Pomoćne funkcije

	// Konvertuje timestamp u nativni format baze
	public static function time($timestamp) { return date("Y-m-d H:i:s",$timestamp); }
	// Konvertuje datum u nativnom formatu baze u timestamp
	public static function timestamp($v) { 
		$g = substr($v,0,4); $mj=substr($v,5,2); $d=substr($v,8,2); 
		$h=substr($v,11,2); $mi=substr($v,14,2); $s=substr($v,17,2);
		return mktime($h,$mi,$s,$mj,$d,$g);
	}
}




?>
