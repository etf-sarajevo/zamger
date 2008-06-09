<?

# Biblioteka korisnih funkcija koje koristim u svojim skriptama
# ---- Copyleft (c) Vedran Ljubović 
# v0.0.2 (2006/09/30) + dodana funkcija nicesize()
# v0.0.3 (2006/10/03) + bssort() za sortiranje bs jezika, nicemessage(), globalna varijabla $debug

# + (ZADACHA-MGR) Korisničko ime je fixirano za broj indexa - checkcookie()



// ----------- GLOBALNE VARIJABLE

$lv_debug=1;
//builtincss();

$system_path = "/srv/httpd/zamger";
$file_path = "/var/www/folder";


// ------------ FUNKCIJE


function dbconnect() {
	// Default database
	dbconnect2("localhost","root","","zamger2");
}

function dbconnect2($dbhost,$dbuser,$dbpass,$dbdb) {
	global $connection,$lv_debug;

	if (!($connection = mysql_connect($dbhost, $dbuser, $dbpass))) {
		if ($lv_debug) biguglyerror(mysql_error());
		exit;
	}
	if (!mysql_select_db($dbdb)) {
		if ($lv_debug) biguglyerror(mysql_error());
		exit;
	}
}

function dbdisconnect() {
	global $connection;
	
	mysql_close($connection);
}

function myquery($query) {
	global $lv_debug;

	if ($r = @mysql_query($query)) {
		return $r;
	}
	
	# Error handling
	if ($lv_debug)
		print "<br/><hr/><br/>MYSQL query:<br/><pre>".$query."</pre><br/>MYSQL error:<br/><pre>".mysql_error()."</pre>";
	exit;
}

function niceerror($error) {
	print "<p><font color='red'><b>GREŠKA: $error</b></font></p>";
}

function biguglyerror($error) {
	print "<center><h2><font color='red'><b>GREŠKA: $error</b></font></h2></center>";
}

function nicemessage($error) {
	print "<p><font color='green'><b>$error</b></font></p>";
}

function time2mysql($timestamp) { return date("Y-m-d H:i:s",$timestamp); }
function mysql2time($v) { 
	$g = substr($v,0,4); $mj=substr($v,5,2); $d=substr($v,8,2); 
	$h=substr($v,11,2); $mi=substr($v,14,2); $s=substr($v,17,2);
	return mktime($h,$mi,$s,$mj,$d,$g);
}

// Escape stringova radi koristenja u mysql upitima - kopirao sa php.net
function my_escape($value) {
	// Stripslashes
	if (get_magic_quotes_gpc()) {
		$value = stripslashes($value);
	}
	// Quote if not a number or a numeric string
	if (!is_numeric($value)) {
		$value = mysql_real_escape_string($value);	// Detecting quotes later is a pain
	}
	return $value;
}

function check_cookie() {
	global $stud_id,$student,$admin,$db,$login;

	session_start();
	$db = my_escape($_SESSION['db']);
	$db = str_replace("'","",$db);
	mysql_select_db("vedran_".$db);
	$login = my_escape($_SESSION['login']);
	if (!preg_match("/[a-zA-Z0-9]/",$login)) header("Location: index.php?greska=1");

	$q1 = myquery("select id from studenti where brindexa='$login'");
	if (mysql_num_rows($q1)>0) {
		$stud_id = mysql_result($q1,0,0);
		$student=1;
	} else {
		$q2 = myquery("select password from admin_login where login='$login'");
		if (mysql_num_rows($q2)>0) {
			$admin = 1;
		} else {
			header("Location: index.php?greska=1");
		}
	}
}

function logout() {
	$_SESSION = array();
	if (isset($_COOKIE[session_name()])) {
		setcookie(session_name(), '', time()-42000, '/');
	}
	session_destroy();

	?><center><h1>Bye-bye</h1></center>
	<script language="JavaScript">
		window.location = "index.php";
	</script>
	<?

}

# String sa lijepim ispisom veličine u Kibibajtima
function nicesize($size) {
	if ($size>1024*1024*1024) {
		return intval($size/(1024*1024*1024/10))/10 . " GB";
	} else if ($size>1024*1024*10) {
		return intval($size/(1024*1024)) . " MB";
	} else if ($size>1024*1024) {
		return intval($size/(1024*1024/10))/10 . " MB";
	} else if ($size>1024*10) {
		return intval($size/1024) . " kB";
	} else if ($size>1024) {
		return intval($size / (1024/10))/10 . " kB";
	} else {
		return $size . " B";
	}
}

# Sortiranje za bosanski jezik
function bssort($a, $b) {
	$a=strtolower($a); $b=strtolower($b);
	static $abeceda = array("a","A","b","B","c","C","č","Č","ć","Ć","d","đ","Đ","e","f","g","h","i","j","k","l","m","n","o","p", "q","r","s","š","Š","t","u","v", "w","x","y","z","ž","Ž");
	$min = (strlen($a)<strlen($b)) ? strlen($a) : strlen($b);
	for ($i=0; $i<$min; $i++) {
		$ca = substr($a,$i,1); if (ord($ca)>128) $ca = substr($a,$i,2);
		$cb = substr($b,$i,1); if (ord($cb)>128) $cb = substr($b,$i,2);
		$k=array_search($ca,$abeceda); $l=array_search($cb,$abeceda);
		//print "K: $k L: $l ZLJ: ".$ca. "       ";
		if ($k<$l) return -1; if ($k>$l) return 1;
	}
	if (strlen($a)<strlen($b)) return -1;
	return 1;
}


# Logiranje
function logthis($event) {
	global $lv_debug,$system_path;

/*	if (!$lv_debug) return;
	$lv_logfile = fopen($system_path."/debug12874.log",'a');
	fwrite($lv_logfile, date("[Y-m-d H:i:s]")." - $event\n");
	fclose($lv_logfile);
	return;*/

	// Database logging
	myquery("insert into log set dogadjaj='".my_escape($event)."'");
}



?>
