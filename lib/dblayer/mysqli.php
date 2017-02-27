<?

// LIB/DBLAYER - Sloj apstrakcije nad bazom - implementacija sa mysqli modulom



function db_connect($dbhost,$dbuser,$dbpass,$dbdb) {
	global $__db_connection, $conf_debug, $conf_use_mysql_utf8;
	
	$__db_connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbdb);
	if (!$__db_connection) {
		if ($conf_debug) biguglyerror(mysqli_connect_error());
		exit;
	}

	if ($conf_use_mysql_utf8) {
		mysqli_set_charset($__db_connection, "utf8");
	}
}

function db_disconnect() {
	global $__db_connection;
	
	mysqli_close($__db_connection);
}


// Stari API - vraća resurs (čija semantika zavisi od db layera)
function db_query($query) {
	global $__db_connection, $conf_debug, $conf_script_path;

	if ($resource = @mysqli_query($__db_connection, $query)) {
		return $resource;
	}
	
	# Error handling
	$error = mysqli_error($__db_connection);
	if ($conf_debug)
		print "<br/><hr/><br/>MYSQL query:<br/><pre>".$query."</pre><br/>MYSQL error:<br/><pre>".$error."</pre>";
	$backtrace = debug_backtrace();
	$file = $backtrace[0]['file'];
	$file = str_replace($conf_script_path."/", "", $file);
	$line = intval($backtrace[0]['line']);

	$error = str_replace("You have an error in your SQL syntax;", "", $error); 
	$error = str_replace("check the manual that corresponds to your MySQL server version for the right syntax to use", "", $error);
	zamgerlog("SQL greska ($file : $line): $error", 3);
	zamgerlog2("SQL greska", 0, 0, 0, "$file:$line: $error");
	exit;
}


// Reimplementacija mysql_ funkcija
function db_fetch_row($res) { return mysqli_fetch_row($res); }
function db_fetch_assoc($res) { return mysqli_fetch_assoc($res); }
function db_num_rows($res) { return mysqli_num_rows($res); }
function db_insert_id() { global $__db_connection; return mysqli_insert_id($__db_connection); }
function db_affected_rows() { global $__db_connection; return mysqli_affected_rows($__db_connection); }
function db_escape_string($s) { global $__db_connection; return mysqli_real_escape_string($__db_connection, $s); }
function db_free_result($res) { return mysqli_free_result($res); }

// Reimplementacija mysql_result sa mysqli (bez provjere ispravnosti parametara)
function db_result($res, $row, $col) { 
	mysqli_data_seek($res, $row);
	$resrow = mysqli_fetch_row($res);
	return $resrow[$col];
}




// Novi API koji implementira neke česte patterne u kodu Zamgera

// db_get: Vrši SQL upit i vraća vrijednost u prvom redu i prvoj koloni (ili false)
// Primjer: $broj_studenata = db_get("SELECT COUNT(*) FROM studenti");
function db_get($query) {
	$res = db_query($query);
	if (db_num_rows($res) == 0) return false;
	$row = mysqli_fetch_row($res); // Najbrži način da se vrati prvi rezultat sa mysqli ekstenzijom
	return $row[0];
}

// db_query_assoc: Vrši SQL upit i vraća prvi red kao asocijativni niz (ili false)
// Primjer: $student = db_query_assoc("SELECT ime, prezime FROM studenti WHERE id=1");
function db_query_assoc($query) {
	$res = db_query($query);
	if (db_num_rows($res) == 0) return false;
	return db_fetch_assoc($res);
}

// db_query_varray: Vrši SQL upit i vraća prvu kolonu rezultata kao niz
// Primjer: $imena = db_query_varray("SELECT ime FROM studenti WHERE godina=2016");
function db_query_varray($query) { 
	$result = array();
	$res = db_query($query);
	while($r = db_fetch_row($res)) $result[] = $r[0];
	return $result;
}

// db_query_vassoc: Vraća asocijativni niz gdje je prva kolona ključ a druga vrijednost
// Primjer: $imena = db_query_vassoc("SELECT id, ime FROM studenti WHERE godina=2016");
function db_query_vassoc($query) { 
	$result = array();
	$res = db_query($query);
	while($r = db_fetch_row($res)) $result[$r[0]] = $r[1];
	return $result;
}

// db_query_table: Vrši upit i raća sve rezultate upita kao niz asocijativnih nizova
// Primjer: $tabela = db_query_table("SELECT id, ime, prezime, godina FROM studenti ORDER BY prezime, ime");
function db_query_table($query) {
	$result = array();
	$res = db_query($query);
	while($r = db_fetch_assoc($res)) $result[] = $r;
	return $result;
}


// db_fetch1 (,2,3,4...): Vraća rezultate u varijable preko referenci
// Primjer: while(db_fetch2($r, $ime, $prezime)) { print "Ime: $ime Prezime: $prezime<br>"; }

// Razlog je što se asocijativni nizovi ne interpoliraju fino u stringove, a obični nizovi su nečitljivi
// Primjeri: 
//   while($row = db_fetch_assoc($r)) { print "Ime: ".$row['ime']." Prezime: ".$row['prezime']."<br>"; } // Ružno
//   while($row = db_fetch_row($r)) { print "Ime: $row[0] Prezime: $row[1]<br>"; } // Još ružnije

function db_fetch1($res, &$a) { $r = db_fetch_row($res); if ($r) $a=$r[0]; return $r; }
function db_fetch2($res, &$a, &$b) { $r = db_fetch_row($res); if ($r) { $a=$r[0]; $b=$r[1]; } return $r; }
function db_fetch3($res, &$a, &$b, &$c) { $r = db_fetch_row($res); if ($r) { $a=$r[0]; $b=$r[1]; $c=$r[2]; } return $r; }
function db_fetch4($res, &$a, &$b, &$c, &$d) { $r = db_fetch_row($res); if ($r) { $a=$r[0]; $b=$r[1]; $c=$r[2]; $d=$r[3]; } return $r; }
function db_fetch5($res, &$a, &$b, &$c, &$d, &$e) { $r = db_fetch_row($res); if ($r) { $a=$r[0]; $b=$r[1]; $c=$r[2]; $d=$r[3]; $e=$r[4]; } return $r; }
function db_fetch6($res, &$a, &$b, &$c, &$d, &$e, &$f) { $r = db_fetch_row($res); if ($r) { $a=$r[0]; $b=$r[1]; $c=$r[2]; $d=$r[3]; $e=$r[4]; $f=$r[5]; } return $r; }
function db_fetch7($res, &$a, &$b, &$c, &$d, &$e, &$f, &$g) { $r = db_fetch_row($res); if ($r) { $a=$r[0]; $b=$r[1]; $c=$r[2]; $d=$r[3]; $e=$r[4]; $f=$r[5]; $g=$r[6]; } return $r; }



// Pomoćne funkcije

// Konvertuje timestamp u nativni format baze
function db_time($timestamp) { return date("Y-m-d H:i:s",$timestamp); }
// Konvertuje datum u nativnom formatu baze u timestamp
function db_timestamp($v) { 
	$g = substr($v,0,4); $mj=substr($v,5,2); $d=substr($v,8,2); 
	$h=substr($v,11,2); $mi=substr($v,14,2); $s=substr($v,17,2);
	return mktime($h,$mi,$s,$mj,$d,$g);
}


// Escape stringova radi koristenja u mysql upitima
// Zamger slijedi filozofiju da se podaci escapuju samo jednom, prilikom dodavanja u bazu,
// a sve što je u bazi je sigurno za direktan prikaz na stranici
// Samim time postoji samo jedna "idealna" escape funkcija
function db_escape($value) {
	global $__db_connection;

	// Convert special HTML chars to protect against XSS
	// If chars are needed for something, escape manually
	$value = htmlspecialchars($value);

	// If magic quotes is on, stuff would be double-escaped here
	if (get_magic_quotes_gpc()) {
		$value = stripslashes($value);
	}

	// Quote if not a number or a numeric string
	if (!is_numeric($value)) {
		$value = mysqli_real_escape_string($__db_connection, $value); // Detecting quotes later is a pain
	}
	return $value;
}






// ----------------------------
// INTERNO KORIŠTENE FUNKCIJE
// ----------------------------


?>