<?

require("../lib/libvedran.php");
require("../lib/zamger.php");
require("../lib/config.php");

dbconnect2($conf_dbhost,$conf_dbuser,$conf_dbpass,$conf_dbdb);

if ($_POST['loginforma'] == "1") {
	$login = my_escape($_POST['login']);
	$pass = my_escape($_POST['pass']);
    $regkey = my_escape($_POST['regkey']);
	
    // Registration key not supplied
    if(empty($regkey)) {
        print -3;
        return;
    }
    
	if (!preg_match("/[\w\d]/",$login)) {
		print -1;
		return;
	} else {
		$status = login($pass);
		if ($status == 1) { 
			print -1;
			return;
		} else if ($status == 2) {
			print -2;
			return;
		} else if ($userid>0) {
			// Store reg key for user into database
            $q0 = myquery("SELECT ID FROM notifikacije_gcm WHERE auth=$userid");
            if(mysql_num_rows($q0)<1) {
                myquery("INSERT INTO notifikacije_gcm(auth, reg_key) VALUES($userid, '$regkey')");
            } else {
                myquery("UPDATE notifikacije_gcm SET reg_key = '$regkey' WHERE auth=$userid LIMIT 1");
            }
            echo 0;
			return;
		}
	}

}
else if($_POST['loginforma'] == "2"){
	$login = my_escape($_POST['login']);
	$pass = my_escape($_POST['pass']);
	if (!preg_match("/[\w\d]/",$login)) {
		print -1;
		return;
	} else {
		$status = login($pass);
		if ($status == 1) { 
			print -1;
			return;
		} else if ($status == 2) {
			print -2;
			return;
		} else if ($userid>0) {
			// Remove reg key from database
            $q0 = myquery("DELETE FROM notifikacije_gcm WHERE auth=$userid");
            if($q0>0)
				echo 5;
			else
				echo -4;
			return;
		}
	}
}
?>
	<form action="<?=$uri?>" method="POST">
	<input type="hidden" name="loginforma" value="2">
	<table border="0">
        <tr>
            <td>Korisnicko ime (UID):</td>
            <td>
                <input type="text" name="login" size="15">
            </td>
        </tr>
        <tr>
            <td>Sifra:</td>
            <td>
                <input type="password" name="pass" size="15">
            </td>
        </tr>
        <tr>
            <td>Registracijski kljuc:</td>
            <td>
                <input type="text" name="regkey" size="15">
            </td>
        </tr>
        <tr>
            <td colspan="2" align="center">
                <input type="submit" value="Kreni">
            </td>
        </tr>
    </table>
	</form>
<?

dbdisconnect();
?>
