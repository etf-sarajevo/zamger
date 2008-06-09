<html>
<head>
<title>Ocjene iz domaćih zadaća</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="style.css" rel="stylesheet" type="text/css" />
</head>
<body>

<h1>Ocjene iz domaćih zadaća</h1>

<? 

include("mysql.php");
dbconnect();

if (!$_POST['stage'] || $_POST['stage']==0) {
	$stage=1;

} elseif($_POST['stage']>0) {
	$stage=2;
	
	$brzadace = $_POST['brzadace'];
	$brzadace=$brzadace+1;
	$brzadace=$brzadace-1;
	$grupa=$_POST['grupa'];
}

if (($_POST['stage']==2) && ($_POST['slanje'])) {

	# unos zadace
	$brzadace = $_POST['brzadace'];
	$brzadace=$brzadace+1;
	$brzadace=$brzadace-1;

	$result = mysql_query("select id from studenti where grupa=$grupa");
	while ($drugired = mysql_fetch_row($result)) {
		$stud_id = $drugired[0];
		$bodovaid = "bodova$stud_id";
		$bodova = $_POST[$bodovaid];
		$result2 = mysql_query("insert into ocjene set vjezba=$brzadace, student=$stud_id, ocjena=$bodova");
	}
	
	print "<p><span class=\"msg\">Zadaća je unesena.</span> <a href=\"index.php\">Povratak na početnu stranicu</a></p>";
	exit;
}

?>

<form action="zadace.php" method="POST" name="forma">
<input type="hidden" name="stage" value="<?=$stage?>">
<table border="0">
	<tr>
		<td>&nbsp;</td>
		<td>Zadaća broj: <input type="text" name="brzadace" value="<?=$brzadace;?>"></td>
		<td>&nbsp;</td>
		<td>Grupa:&nbsp;&nbsp; <select name="grupa" onchange="javascript:forma.submit()">
		<option>----</option><?
		$result = mysql_query("select * from grupe order by naziv");
		while ($jedanred = mysql_fetch_row($result)) {
			$grupa_id = $jedanred[0];
			$grupa_naziv = $jedanred[1];
			if ($grupa_id == $grupa) { $addtext=" selected"; } else { $addtext = ""; }
			print "<option value=\"$grupa_id\"$addtext>$grupa_naziv</option>";
		}
		?></select></td>
	</tr>
</table>
<?
if ($stage>1) {
?>
<p>&nbsp;</p>
<table width="100%" border="0">
<tr bgcolor="#CCCCCC">
<td width="50"></td>
<td><b>Ime i prezime</b></td>
<td width="100"><b>Bodova za zadaću</b></td>
</tr>

<?
$result2 = mysql_query("select id,ime,prezime from studenti where grupa=$grupa order by prezime,ime");
$i=0;
while ($drugired = mysql_fetch_row($result2)) {
	$stud_id = $drugired[0];
	$stud_ime = $drugired[1];
	$stud_prez = $drugired[2];
	if ($i==1) { $i=0; $color="#DDDDDD"; } else { $i=1; $color="#EEEEEE"; }
	?>
	<tr bgcolor="<?=$color;?>">
		<td width="50"></td>
		<td><?=$stud_prez?> <?=$stud_ime?></a></td>
		<td width="100"><select name="bodova<?=$stud_id?>"><option value="0">----</option>
		<option value="-1">Nema</option>
		<option value="0">0</option>
		<option value="0.5">0,5</option>
		<option value="1">1</option>
		<option value="1.5">1,5</option>
		<option value="2">2</option>
		<option value="2.5">2,5</option>
		<option value="3">3</option>
		<option value="3.5">3,5</option>
		<option value="4">4</option>
		<option value="4.5">4,5</option>
		<option value="5">5</option>
		<option value="5.5">5,5</option>
		<option value="6">6</option>
		</select></td>
	</tr>
	<?
}

?>

</table>

<center><input type="submit" name="slanje" value=" Pošalji formular ">
&nbsp;&nbsp;&nbsp;&nbsp;
<input type="reset" value=" Poništi "></center>

<? 
}  # if ($stage>1)
?>
</form>

</body>
</html>