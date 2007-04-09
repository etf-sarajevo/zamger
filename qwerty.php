<?


# Prijava

require("libvedran.php");
dbconnect();
$admin=0;

check_cookie();

if (!$admin) { 
	print '<script language="JavaScript">window.location="index.php?greska=1"</script>';
	exit;
}

$sta=$_GET['sta']; if ($sta=="") $sta=$_POST['sta'];

# Moduli bez template-a
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
	<title>ETF Bolognaware</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="css/style.css" rel="stylesheet" type="text/css" />
</head>
<body topmargin="0" leftmargin="0" bottommargin="0" rightmargin="0" bgcolor="#FFFFFF">

<!-- The big table - cause we like it!!!!! web 2.0 is teh sux00rz -->
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr bgcolor="#BBBBFF">
		<td><table width="100%" border="0" cellspacing="0" cellpadding="0"><tr>
			<td width="25">&nbsp;</td>
			<td width="110" height="110" align="center" valign="center"><a href="http://www.etf.unsa.ba"><img src="images/etf.gif" border="0"></a></td>
			<td width="100%"><font color="#FFFFFF"><center><h1>ZAMGER v3.0</h1>by <a href="http://www.linux.org.ba/?c=contact&koga=vedran"><font color="#FFFFFF">Vedran Ljubović</font></a> (c) 2006,2007</center></font></td>
			<td width="135">&nbsp;</td> <!-- Centriranje-->
		</tr></table></td>
	</tr>
	<tr bgcolor="#777777"><td><img src="images/fnord.gif" width="1" height="1"></td></tr>
	<tr>
		<td>

<p>&nbsp;</p>



<? 

$q1 = myquery("select ime,prezime from nastavnik where id=$userid");
$ime = mysql_result($q1,0,0);
$prezime = mysql_result($q1,0,1);

print "<p>Trenutno prijavljen: $ime $prezime</p>";


if ($sta == "grupa") {
	include("admin_grupa.php"); admin_grupa(); 
#} elseif ($sta == "profil") {
#	include("stud_profil.php"); stud_profil(); 
} elseif ($sta == "predmet") {
	include("admin_predmet.php"); admin_predmet(); 
} elseif ($sta == "statistika") {
	print "<h1>Statistika ispita</h1><p>Nije još implementirano... Sačekajte sljedeću verziju :)</p>";
} elseif ($sta == "sifra") {
	include("admin_sifra.php"); admin_sifra(); 
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
