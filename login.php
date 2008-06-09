<?

error_reporting(E_ALL);


require("libvedran.php");
dbconnect();
$student=$admin=0;


$login = my_escape($_POST['brind']);
$pass = $_POST['pass'];
$db = my_escape($_POST['predmet']);

mysql_select_db("vedran_".$db);


if (!preg_match("/[\w\d]/",$login)) {
	header("Location: index.php");
	exit;
}


$q1 = myquery("select id from studenti where brindexa='$login'");
if (mysql_num_rows($q1)>0) {
	$stud_id = mysql_result($q1,0,0);
	$q2 = myquery("select password from login where id=$stud_id");
	if (mysql_num_rows($q2)>0) {
		$pass2 = mysql_result($q2,0,0);
		if ($pass2 == $pass) {
			$student=1;
		} else {
			print '<script language="JavaScript">window.location="index.php?greska=2"</script>';
			exit;
		}
	} else { # $q2
		print '<script language="JavaScript">window.location="index.php?greska=1"</script>';
		exit;
	}
} else {
	$q3 = myquery("select password from admin_login where login='$login'");
	if (mysql_num_rows($q3)>0) {
		$pass2 = mysql_result($q3,0,0);
		if ($pass2 == $pass) {
			$admin = 1;
		} else {
#			header("Location: index.php?greska=1"); # Lafo
			print '<script language="JavaScript">window.location="index.php?greska=1"</script>';
			exit;
		}
	} else { # $q3
#		header("Location: index.php?greska=1");
		print '<script language="JavaScript">window.location="index.php?greska=1"</script>';
		exit;
	}
}


# Log posjeta
logthis("Login '$login' (db: $db)");
//myquery("insert into log set dogadjaj='".my_escape($event)."'");

/*$q4 = myquery("insert into log set id=$id, dogadjaj='login'");

$prijesatvremena = time2mysql(time()-3600);

$q6 = myquery("select vrijeme from log where id=$id and dogadjaj='login' and vrijeme<'$prijesatvremena'");
if (mysql_num_rows($q6)<1) {
	print "<p><i>Ovo je vaš prvi posjet ovoj stranici...</i></p>";
} else {
	$vrijeme = mysql2time(mysql_result($q6,0,0));
	$vrijeme = date("j. n. Y. h:i:s",$vrijeme);
	print "<p><i>Zadnji posjet: $vrijeme</i></p>";
}*/



session_start();
$_SESSION['login']=$login;
$_SESSION['db']=$db;
session_write_close();

if ($admin == 1) {
	header("Location: qwerty.php");
} elseif ($student == 1) {
	header("Location: student.php");
} else {
	header("Location: index.php");
}


?>