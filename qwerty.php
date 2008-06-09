<?


# Prijava

require("libvedran.php");
dbconnect();
$student=$admin=0;

check_cookie();

if (!$admin) { 
	print '<script language="JavaScript">window.location="index.php?greska=1"</script>';
	exit;
}

$sta=$_GET['sta']; if ($sta=="") $sta=$_POST['sta'];
if ($sta == "student-izmjena") {
	include("admin_student_izmjena.php"); admin_student_izmjena(); exit;
} elseif ($sta == "ajah") {
	include("admin_ajah.php"); admin_ajah(); exit;
} elseif ($sta == "zadaca") {
	include("admin_zadaca.php"); admin_zadaca(); exit;
} elseif ($sta == "pregled") {
	include("admin_pregled.php"); admin_pregled(); exit;
} elseif ($sta == "unos") {
	include("admin_unos.php"); admin_unos(); exit;
}
?>
<html>
<head>
	<title>Osnove računarstva &amp; Tehnike programiranja</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="css/style.css" rel="stylesheet" type="text/css" />
</head>
<body topmargin="0" leftmargin="0" bottommargin="0" rightmargin="0" bgcolor="#FFFFFF">

<!-- The big table - cause we like it!!!!! web 2.0 is teh sux00rz -->
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr bgcolor="#BBBBFF">
		<td><table width="100%" border="0" cellspacing="0" cellpadding="0"><tr>
			<td width="25">&nbsp;</td>
			<td width="110" height="110" align="center" valign="center"><img src="images/etf.gif"></td>
			<td width="100%"><font color="#FFFFFF"><center><h1>Zadacha manager v2.0</h1>by <a href="http://people.etf.unsa.ba/~vljubovic/contact.php"><font color="#FFFFFF">Vedran Ljubović</font></a> (c) 2006</center></font></td>
			<td width="135">&nbsp;</td> <!-- Centriranje-->
		</tr></table></td>
	</tr>
	<tr bgcolor="#777777"><td><img src="images/fnord.gif" width="1" height="1"></td></tr>
	<tr>
		<td>

<p>&nbsp;</p>



<? 

$q1 = myquery("select ime,prezime from admin_login where login='$login'");
$ime = mysql_result($q1,0,0);
$prezime = mysql_result($q1,0,1);

print "<p>Trenutno prijavljen: $ime $prezime</p>";


if ($sta == "grupa") {
	include("admin_grupa.php"); admin_grupa(); 
#} elseif ($sta == "profil") {
#	include("stud_profil.php"); stud_profil(); 
} elseif ($sta == "logout") {
	logout();
} else {
	include("admin_intro.php"); admin_intro(); 
}
?>




		</td>
	</tr>
</table>

</body>
</html>
