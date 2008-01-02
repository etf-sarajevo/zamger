<?


// v3.0.0.0 (2007/04/09) + Release
// v3.0.1.0 (2007/06/12) + Release
// v3.0.1.1 (2007/09/11) + Novi modul "Nihada" za unos i pristup podataka o studentima, nastavnicima, loginima itd.
// v3.0.1.2 (2007/09/20) + Modul "Izvjestaj" izdvojen iz modula "Nihada"
// v3.0.1.3 (2007/09/26) + Novi modul "Komentar" za komentare na rad studenta
// v3.0.1.4 (2007/12/03) + Novi modul "download" (moracemo vidjeti oko ovih zajednickih modula...)
// v3.0.1.5 (2007/12/25) + Dodan logout

# Prijava

require("libvedran.php");
dbconnect();
$admin=0;

check_cookie();

if (!$admin) { 
	print '<script language="JavaScript">window.location="index.php?greska=1"</script>';
	exit;
}

$sta=$_REQUEST['sta'];
$akcija=$_REQUEST['akcija'];

# Moduli bez template-a
if ($sta == "student-izmjena") {
	include("admin_student_izmjena.php"); admin_student_izmjena(); exit;
} elseif ($sta == "ajah") {
	include("admin_ajah.php"); admin_ajah(); exit;
} elseif ($sta == "zadaca") {
	include("admin_zadaca.php"); admin_zadaca(); exit;
} elseif ($sta == "pregled") {
	include("admin_pregled.php"); admin_pregled(); exit;
} elseif ($sta == "download") {
	include("admin_pregled.php"); admin_download(); exit;
} elseif ($sta == "unos") {
	include("admin_unos.php"); admin_unos(); exit;
} elseif ($sta == "izvjestaj") {
	include("admin_izvjestaj.php"); admin_izvjestaj(); exit;
} elseif ($sta == "komentar") {
	include("admin_komentar.php"); admin_komentar(); exit;
} elseif ($sta == "logout") {
	logout();
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
			<td width="100%"><font color="#FFFFFF"><center><h1>ZAMGER v3.5 beta</h1>by <a href="http://www.linux.org.ba/?c=contact&koga=vedran"><font color="#FFFFFF">Vedran Ljubović</font></a> (c) 2006,2007</center></font></td>
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
} elseif ($sta == "nihada") {
	include("admin_nihada.php"); admin_nihada(); 
} elseif ($sta == "siteadmin") {
	include("admin_site.php"); admin_site(); 
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
