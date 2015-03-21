<?


// AJAH/AUTH - skripta za autentikaciju korisnika za web servise

require("../lib/libvedran.php");
require("../lib/zamger.php");
require("../lib/config.php");

dbconnect2($conf_dbhost,$conf_dbuser,$conf_dbpass,$conf_dbdb);
//$conf_system_auth = "table";

$login = my_escape($_POST['login']);
$pass = $_POST['pass'];
$result = array();

$status = login($pass);
if ($login == "autotester" && $pass == "testerauto") { // bypass ldap
	session_start();
	//session_regenerate_id(); // prevent session fixation
	$_SESSION['login']=$login;
	session_write_close();
	$result['success'] = "true";
	$result['sid'] = session_id();
	$result['server_message'] = "Hello autotester";
} else if ($status == 1 || $status == 2) { 
	$result['success'] = "false";
	$result['code'] = $status;
	$result['server_message'] = "Unknown user or wrong password";
} else {
	$result['success'] = "true";
	$result['sid'] = session_id();
	$result['server_message'] = "Welcome to $conf_appname $conf_appversion!";
}

print json_encode($result);

dbdisconnect();

?>
