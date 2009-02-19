<?

# Standardna login stranica za libvedran
# ---- Copyleft (c) Vedran Ljubović 
# v0.!? (ZAMGER v0.4)

# + (ZAMGER v0.3) provjera IDa u tabelama student i nastavnik
# + (ZAMGER v0.4) prešao na login() funkciju



require("libvedran.php");
dbconnect();
$admin=-1;

$login = my_escape($_POST['login']);
$pass = my_escape($_POST['pass']);

if (!preg_match("/[\w\d]/",$login)) {
	header("Location: index.php?greska=1");
	exit;
}

# STATUS:
#  0 - OK
#  1 - nepoznat login
#  2 - password ne odgovara 
$status = login($pass);

if ($status == 1) { 
	print '<script language="JavaScript">window.location="index.php?greska=1"</script>';
	exit;
} else if ($status == 2) {
	print '<script language="JavaScript">window.location="index.php?greska=2"</script>';
	exit;
}


// Provjera IDa u tabelama
if ($admin == 0) {
	$q2 = myquery("select count(*) from student where id=$userid");
} else {
	$q2 = myquery("select count(*) from nastavnik where id=$userid");
}
if (mysql_num_rows($q2)<1) {
	logout();
	// ID nije pronadjen u odgovarajucoj tabeli
	print '<script language="JavaScript">window.location="index.php?greska=1"</script>';
	exit;
}



# Log posjeta
logthis("Login '$login'");




if ($admin == 0) {
	header("Location: student.php");
} elseif ($admin == 1) {
	header("Location: qwerty.php");
// } elseif ($nastavnik == 2) { // admin predmeta
//	header("Location: uiop.php");
// } elseif ($nastavnik == 3) { // website admin
//	header("Location: asdfg.php");
} else {
	header("Location: index.php");
}


?>