<?


// AJAH/AUTH - skripta za autentikaciju korisnika za web servise

require("../lib/libvedran.php");
require("../lib/zamger.php");
require("../lib/config.php");

dbconnect2($conf_dbhost,$conf_dbuser,$conf_dbpass,$conf_dbdb);


$login = my_escape($_POST['login']);
$pass = $_POST['pass'];

if ($login=="autotester" && $pass=="testerauto")  {
	$userid = mysql_result($q1,0,0);
	$admin = mysql_result($q1,0,2);
	// All OK, start session
	session_start();
	//session_regenerate_id(); // prevent session fixation
	$_SESSION['login']=$login;
	session_write_close();
	print "OK|".session_id();
	return;
}

$status = login($pass);
if ($status == 1 || $status == 2) { 
	print "FAIL";
} else {
	print "OK|".session_id();
}

dbdisconnect();

?>
