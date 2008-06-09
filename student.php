<?


# Prijava

require("libvedran.php");
dbconnect();
$student=$admin=0;

check_cookie();

if (!$student || !$stud_id) { 
	print '<script language="JavaScript">window.location="index.php?greska=1"</script>';
	exit;
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

<?


function menuimage($img,$tekst,$link) {
	?>
		<table width="200" border="0" cellspacing="0" cellpadding="10">
			<tr><td valign="center" onmouseover="javascript:this.bgimage = 'images/button.gif';" onmouseout="javascript:this.bgimage = '';">
				<table border="0" cellspacing="0" cellpadding="0"><tr><td>
					<a href="<?=$link?>"><img src="images/<?=$img?>" align="center" border="0"></a>
				</td><td width="15">&nbsp;
				</td><td class="meni">
					<a href="<?=$link?>"><?=$tekst?></a>
				</td></tr></table>
			</td></tr>
		</table>
	<?
}


?>

	<table width="100%" height="450" border="0" cellspacing="0" cellpadding="0">
		<tr><td width="250" valign="top" background="images/fadetogray.gif">
			<br/>
			<?=menuimage("artsfftscope.png","Status","student.php?sta=status");?>
			<?=menuimage("source.png","Pošalji zadaću","student.php?sta=zadaca")?>
			<?=menuimage("kontact_contacts.png","Promijeni lične podatke","student.php?sta=profil")?>
			<?=menuimage("kontact_todo.png","Pregled ocjena","pregled-public.php?predmet=$db")?>
			<?=menuimage("exit.png","Izlaz","student.php?sta=logout")?>
		</td>
		<td bgcolor="#FFFFFF" valign="top">
			<table width="100%" border="0" cellspacing="0" cellpadding="10"><tr><td>

<? 
$sta=$_GET['sta']; if ($sta=="") $sta=$_POST['sta'];
if ($sta == "zadaca") {
	include("stud_zadaca.php"); stud_zadaca(); 
} elseif ($sta == "profil") {
	include("stud_profil.php"); stud_profil(); 
} elseif ($sta == "logout") {
	logout();
} else {
	include("stud_status.php"); stud_status(); 
}
?>

			</td></tr></table>
		</td></tr>
	</table>



		</td>
	</tr>
</table>

</body>
</html>
