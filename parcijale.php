<html>
<head>
<title>Parcijale</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="style.css" rel="stylesheet" type="text/css" />
</head>
<body>

<form action="parcijale.php" method="POST" name="forma">
<?

if ($_POST['action'] == " Dodaj ") {

	include("mysql.php");
	dbconnect();
	$p = $_POST['parcijala'];

	$i=1; $id = $_POST["stud$i"];
	while ($id != "") {
		$ocjena = chop($_POST["ocj$i"]);
		$ocjena = str_replace(",",".",$ocjena);
		if ($ocjena == 0) $ocjena="0";
		if ($id<=0) {
			print "GREŠKA. ID=$id.<br>";
		} else {
			$result = mysql_query("insert into parcijale set id=$p, student=$id, ocjena=$ocjena") or die ("Greska: ".mysql_error()."<br>insert into parcijale set id=$p, student=$id, ocjena=$ocjena");
		}
		$i++;
		$id = $_POST["stud$i"];
	}

	print "Podaci ubačeni. <a href=\"index.php\">Nazad na početnu stranicu</a>";

} else if ($_POST['action'] == " Ažuriraj ") {

?>
<input type="hidden" name="parcijala" value="<?=$_POST['parcijala']?>">
Parcijala: <b><?=$_POST['parcijala']?></b><br><br>
<table border="1" cellspacing="0" cellpadding="3">
<tr bgcolor="#CCCCCC"><td>Student ID</td><td>Prezime</td><td>Ime</td><td>Ocjena</td></tr>
<?
	include("mysql.php");
	dbconnect();

	$i=1; $id = $_POST["stud$i"];
	while ($id != "") {
		if ($id>0) {
			$result = mysql_query("select prezime,ime from studenti where id=$id");
			if (mysql_num_rows($result)>0) { 
				$prezime = mysql_result($result,0,0);
				$ime = mysql_result($result,0,1);
			} else {
				$prezime = $ime = "?";
			}
		} else {
			$prezime = $ime = "GREŠKA!!! Idi nazad";
		}
		$ocjena = chop($_POST["ocj$i"]);
		$ocjena = str_replace(",",".",$ocjena);
		if ($ocjena == 0) $ocjena="0";
		print "<tr><td><input type=\"text\" name=\"stud$i\" value=\"$id\" size=\"3\"></td>";
		print "<td>$prezime</td><td>$ime</td><td>$ocjena</td>";
		print "<input type=\"hidden\" name=\"ocj$i\" value=\"$ocjena\"></tr>";
		$i++;
		$id = $_POST["stud$i"];
	}

?>
</table>
<input type="submit" name="action" value=" Dodaj ">
<?

} else if ($_POST['action'] == " Pošalji ") {
?>
<input type="hidden" name="parcijala" value="<?=$_POST['parcijala']?>">
Parcijala: <b><?=$_POST['parcijala']?></b><br><br>
<table border="1" cellspacing="0" cellpadding="3">
<tr bgcolor="#CCCCCC"><td>Student ID</td><td>Prezime</td><td>Ime</td><td>Ocjena</td></tr>
<?
	include("mysql.php");
	dbconnect();

	$imput = $_POST['imput'];
	$redovi = explode("\n",$imput);
	$tempid=1;

	foreach ($redovi as $red) {	
		if (strlen($red)>1) {
			list($imepr,$ocjena) = explode("\t",$red);
			list($prezime,$ime) = explode(" ",$imepr);
			$result = mysql_query("select id from studenti where prezime='$prezime' and ime='$ime'");
			if (mysql_num_rows($result)>0) { $id = mysql_result($result,0,0); } else { $id = "?"; }
			print "<tr><td><input type=\"text\" name=\"stud$tempid\" value=\"$id\" size=\"3\"></td>";
			print "<td>$prezime</td><td>$ime</td><td>$ocjena</td>";
			print "<input type=\"hidden\" name=\"ocj$tempid\" value=\"$ocjena\"></tr>";
			$tempid++;
		}
	}
?>
</table>
<input type="submit" name="action" value=" Ažuriraj ">
<?

} else {

?>

Redni broj parcijalnog ispita: <input type="text" name="parcijala"><br><br>
<textarea rows="25" cols="70" name="imput"></textarea><br><br>
<input type="submit" name="action" value=" Pošalji ">

<? } ?>

</form>

</body>
</html>