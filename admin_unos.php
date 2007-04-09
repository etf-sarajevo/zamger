<?

function admin_zadaca() {

?>
<html>
<head>
<title>Unos studenata</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="style.css" rel="stylesheet" type="text/css" />
</head>
<body leftmargin="0" topmargin="0" rightmargin="0">

<?

$message = "";

# Prijem poslanih podataka

if ($_POST['novagrupa']) {
	myquery("insert into grupe set naziv='".$_POST['imegrupe']."'");
	$message = "<span class=\"msg\">Nova grupa <b>".$_POST['imegrupe']."</b> je dodana.</span>";
}

if ($_POST['novistudent']) {
	myquery("insert into studenti set ime='".$_POST['ime']."', prezime='".$_POST['prezime']."', grupa=".$_POST['grupa'].", email='".$_POST['email']."', brindexa='".$_POST['brindexa']."'") or die(mysql_error());
	$message = "<span class=\"msg\">Student <b>".$_POST['ime']." ".$_POST['prezime']."</b> je dodan.</span>";
}

if ($_POST['izmstudent']) {
	myquery("update studenti set ime='".$_POST['ime']."', prezime='".$_POST['prezime']."', grupa=".$_POST['grupa'].", email='".$_POST['email']."', brindexa='".$_POST['brindexa']."' where id=".$_POST['idid']) or die(mysql_error());
	$message = "<span class=\"msg\">Student <b>".$_POST['ime']." ".$_POST['prezime']."</b> je izmijenjen.</span>";
}

?>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
<td width="200" bgcolor="#DDEEFF" align="center" valign="top"><table width="190" border="0"><tr><td>
<br />
<b><a href="?sta=novi">Novi student</a></b><br />
<br />
<?

# Lista studenata

$result = myquery("select * from grupe order by naziv");
while ($jedanred = mysql_fetch_row($result)) {
	$grupa_id = $jedanred[0];
	$grupa_naziv = $jedanred[1];
	?>
		<table border="0" cellspacing="0" cellpadding="0">
		<tr><td width="10"></td>
		<td colspan="3" class="grupa"><?=$grupa_naziv?></td>
		</tr>
	<?
	$result2 = myquery("select id,ime,prezime from studenti where grupa=$grupa_id order by prezime,ime");
	while ($drugired = mysql_fetch_row($result2)) {
		$stud_id = $drugired[0];
		$stud_ime = $drugired[1];
		$stud_prez = $drugired[2];
		?>
			<tr>
			<td width="10"></td>
			<td width="10"></td>
			<td width="20" background="dots.gif"></td>
			<td> <a href="?id=<?=$stud_id?>"><?=$stud_prez?> <?=$stud_ime?></a></td>
			</tr>
		<?
	}
	print "</table>";
}

?>
<br />
<b><a href="?sta=novagr">Nova grupa</a></b><br />
<br />
</td></tr></table></td>
<td width="1" bgcolor="#0000FF">
</td>
<td valign="top" align="center">
<table width="95%"><tr><td>
<?=$message?>
<form action="unos.php" method="POST">
<?

# Ispisi podatke studenta

if ($_GET['sta'] == "novagr") {
?>
	<br/>
	<h2>Nova grupa:</h2>
	Unesite ime nove grupe:<br/>
	<input type="text" size="20" name="imegrupe">
	&nbsp;&nbsp;&nbsp;
	<input type="submit" name="novagrupa" value=" Pošalji ">
<?
} elseif (($_GET['sta'] == "novi") || ($_GET['id'] != 0)) {

if ($_GET['id'] != 0) {
	$result = myquery("select grupa, ime, prezime, email, brindexa from studenti where id=".$_GET['id']);
	$idgrupa = mysql_result($result,0,0);
	$idime = mysql_result($result,0,1);
	$idprezime = mysql_result($result,0,2);
	$idemail = mysql_result($result,0,3);
	$idbrindexa = mysql_result($result,0,4);
?>
	<br/>
	<h2>Izmjena podataka studenta:</h2>
	<input type="hidden" name="idid" value="<?=$_GET['id']?>">
<?
	$submitid="izmstudent";
} else {
?>
	<br/>
	<h2>Novi student:</h2>
<?
	$submitid="novistudent";
}# if id != 0

?>
	Ime studenta:<br/>
	<input type="text" size="20" name="ime" value="<?=$idime?>"><br/>
	<br/>
	Prezime studenta:<br/>
	<input type="text" size="20" name="prezime" value="<?=$idprezime?>"><br/>
	<br/>
	Grupa:<br/>
	<select name="grupa">
<?
$result = myquery("select * from grupe order by naziv");
while ($jedanred = mysql_fetch_row($result)) {
	$grupa_id = $jedanred[0];
	$grupa_naziv = $jedanred[1];
	if ($grupa_id == $idgrupa) { $addtext=" selected"; } else { $addtext = ""; }
	print "<option value=\"$grupa_id\"$addtext>$grupa_naziv</option>";
}
?>
	</select><br/>
	<br/>
	Kontakt e-mail:<br/>
	<input type="text" size="20" name="email" value="<?=$idemail?>"><br/>
	<br/>
	Broj indexa:<br/>
	<input type="text" size="20" name="brindexa" value="<?=$idbrindexa?>"><br/>
	<br/>
	<input type="submit" name="<?=$submitid?>" value=" Pošalji podatke ">
	&nbsp;&nbsp;&nbsp;&nbsp;
	<input type="reset" value=" Poništi ">
<?

} else {
?>
<h1>Unos studenata / grupa</h1>
<ul><li>Da unesete novog studenta, kliknite na link "Novi student" na vrhu spiska lijevo</li>
<li>Da promijenite podatke studenta ili da ga premjestite u drugu grupu, kliknite na ime studenta na spisku lijevo. Spisak je sortiran po grupama, te abecedno po prezimenima studenata</li>
<li>Da dodate novu grupu, kliknite na link "Nova grupa" na dnu spiska lijevo</li></ul>
<?
}

?>
</form>
</td></tr></table>
</td>
</tr>
<tr bgcolor="#0000FF"><td colspan="3"><img src="fnord.gif" width="1" height="1"></td></tr>
</table>

<p>(c) Vedran Ljubović, 2005.</p>
</body>
</html>
<?
}
?>