<?

function dbconnect() {
	global $connection;

	$dbhost = "localhost";
	$dbuser = "root";
	$dbpass = "";
	$dbdb = "zamger";
	
	mysql_connect($dbhost, $dbuser, $dbpass) or die(mysql_error());
	mysql_select_db($dbdb) or die(mysql_error());
}

function dbdisconnect() {
	global $connection;
	
	mysql_close($connection);
}

?>
