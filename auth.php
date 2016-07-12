<?


// AUTH.PHP - web servis za autentikaciju korisnika bez HTMLa

require("lib/libvedran.php");
require("lib/zamger.php");
require("lib/config.php");

dbconnect2($conf_dbhost,$conf_dbuser,$conf_dbpass,$conf_dbdb);
//$conf_system_auth = "table"; // prihvatamo autotestera

$login = my_escape($_POST['login']);
$pass = $_POST['pass'];
$result = array();

$status = login($pass);
if ($status == 1 || $status == 2) { 
	$result['success'] = "false";
	$result['code'] = $status;
	$result['message'] = "Unknown user or wrong password";
} else {
	$result['success'] = "true";
	$result['sid'] = session_id();
	$result['userid'] = $userid;
}

print json_encode($result);

dbdisconnect();

?>
