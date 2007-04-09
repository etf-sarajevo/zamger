<?

function dbconnect() {
	global $connection;

	$dbhost = "localhost";
	$dbuser = "vedran_studenti";
	$dbpass = "itneduts";
	$dbdb = "vedran_studenti_tp";
	
	mysql_connect($dbhost, $dbuser, $dbpass) or die(mysql_error());
	mysql_select_db($dbdb) or die(mysql_error());
}

function dbdisconnect() {
	global $connection;
	
	mysql_close($connection);
}

function myquery($query) {
	if ($r = @mysql_query($query)) {
		return $r;
	}
	
	# Error handling
	print "<br/><hr/><br/>MYSQL query:<br/><pre>".$query."</pre><br/>MYSQL error:<br/><pre>".mysql_error()."</pre>";
	exit;
}

function niceerror($error) {
	print "<p><font color='red'><b>GREŠKA: $error</b></font></p>";
}

function biguglyerror($error) {
	print "<center><h2><font color='red'><b>GREŠKA: $error</b></font></h2></center>";
}

function time2mysql($timestamp) { return date("YmdHis",$timestamp); }
function mysql2time($v) { 
	$g = substr($v,0,4); $mj=substr($v,4,2); $d=substr($v,6,2); 
	$h=substr($v,8,2); $mi=substr($v,10,2); $s=substr($v,12,2);
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

$system_path = "/srv/www/web2/user/vedran.ljubovic/web/tng";

?>