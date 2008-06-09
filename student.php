<?


# Prijava

require("libvedran.php");
dbconnect();
$student=$admin=$predmet_id=0;


check_cookie();

if (!$userid) { 
	print '<script language="JavaScript">window.location="index.php?greska=1"</script>';
	exit;
}

$labgrupa=intval($_GET['labgrupa']); if ($labgrupa==0) $labgrupa=intval($_POST['labgrupa']);


// Da li neko pokušava da spoofa labgrupu?
if ($labgrupa != 0) {
	$q01 = myquery("select count(*) from student_labgrupa where student=$userid and labgrupa=$labgrupa");
	if (mysql_result($q01,0,0) == 0) {
		$greska = "Niste upisani u ovu labgrupu.";
	}
}


// Labgrupa nije zadana, ali moguće da je student u samo jednoj labgrupi
// pa ćemo izabrati nju
if ($labgrupa == 0) {
	$q02 = myquery("SELECT sl.labgrupa, predmet.id 
	FROM student_labgrupa as sl, labgrupa, predmet 
	WHERE sl.student=$userid and sl.labgrupa=labgrupa.id and labgrupa.predmet=predmet.id and predmet.aktivan=1");
	if (mysql_num_rows($q02)<1) {
		$greska = "Niste trenutno upisani ni u jedan aktivan predmet. Kontaktirajte tutora!";
	}
	if (mysql_num_rows($q02)==1) {
		$labgrupa = mysql_result($q02,0,0);
		$predmet_id = mysql_result($q02,0,1);
	}
}

// Predmet često zatreba...
if ($predmet_id == 0) {
	$q03 = myquery("select predmet from labgrupa where id=$labgrupa"); 
	$predmet_id = mysql_result($q03,0,0);
}



// Moduli bez templatea

$sta=$_GET['sta']; if ($sta=="") $sta=$_POST['sta'];

if ($sta == "download") {
	include ("stud_download.php"); stud_download(); exit;
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
			<td width="110" height="110" align="center" valign="center"><img src="images/etf.gif"></td>
			<td width="100%"><font color="#FFFFFF"><center><h1>ZAMGER v3.0 RC1</h1>by <a href="http://people.etf.unsa.ba/~vljubovic/contact.php"><font color="#FFFFFF">Vedran Ljubović</font></a> (c) 2006,2007</center></font></td>
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
			<?=menuimage("artsfftscope.png","Status","student.php?sta=status&labgrupa=$labgrupa");?>
			<?=menuimage("source.png","Pošalji zadaću","student.php?sta=zadaca&labgrupa=$labgrupa")?>
			<?=menuimage("kontact_contacts.png","Promijeni lične podatke","student.php?sta=profil&labgrupa=$labgrupa")?>
			<?=menuimage("kontact_todo.png","Pregled ocjena","pregled-public.php?predmet=$predmet_id&toplista=1")?>
			<?=menuimage("exit.png","Izlaz","student.php?sta=logout&labgrupa=$labgrupa")?>
		</td>
		<td bgcolor="#FFFFFF" valign="top">
			<table width="100%" border="0" cellspacing="0" cellpadding="10"><tr><td>

<? 

if ($greska) {
	niceerror($greska);
} elseif ($sta == "zadaca") {
	include("stud_zadaca.php"); stud_zadaca(); 
} elseif ($sta == "profil") {
	include("stud_profil.php"); stud_profil(); 
} elseif ($sta == "logout") {
	logout();
} else {
	if ($labgrupa == 0) {
		include("stud_labgrupa.php"); stud_labgrupa();
	}
	else { 
		include("stud_status.php"); stud_status();
	} 
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
