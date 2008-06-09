<html>
<head>
<title>Komentari na aktivnost studenata</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="style.css" rel="stylesheet" type="text/css" />
</head>
<body>

<h2>Komentari na aktivnost studenata</h2>

<form action="komentar.php" method="POST" name="forma">

<?

include("mysql.php");
dbconnect();

$stage = $_POST['stage'];
if ($stage<1) { 
	$stage=1; 
} elseif ($stage<2) { 
	$stage=2; 
} elseif ($stage<3) { 
	$stage=3; 
} else { 
	$stage=4; 
}
$grupa = $_POST['grupa'];
$student = $_POST['student'];

?>
<input type="hidden" name="stage" value="<?=$stage?>">
<?
if ($stage==1) {

?>
Izaberite grupu:
<select name="grupa" onchange="javascript:forma.submit()"><option>----</option>
<?
$r1 = mysql_query("select id,naziv from grupe order by id");
while ($jr = mysql_fetch_row($r1)) {
	$grupa_id = $jr[0];
	$grupa_ime = $jr[1];
	print "<option value=\"$grupa_id\">$grupa_ime</option>\n";
}
?>
</select>
<?
} # if ($stage==1)

if ($stage==2) { 
	$ra = mysql_query("select naziv from grupe where id=$grupa");
	if (mysql_num_rows($ra)<0) {
		print "<h1>Greška!!!!!</h1>";
	} else {
		print "<h3>Grupa: ".mysql_result($ra,0,0)."</h3>\n";
	}

?>
<p>Izaberite studenta:</p>
<select name="student" onchange="javascript:forma.submit()"><option>----</option>
<?
$r2 = mysql_query("select id,ime,prezime from studenti where grupa=$grupa order by prezime");
while ($dr = mysql_fetch_row($r2)) {
	$stud_id = $dr[0];
	$stud_ime = $dr[1];
	$stud_prez = $dr[2];
	print "<option value=\"$stud_id\">$stud_prez $stud_ime</option>";
}
?>
</select>
<?
} # if ($stage==2)



if (($stage==4) && ($_POST['posalji'])) {
	$komentar = $_POST['komentar']."\n  -- ".$_POST['asistent']." (".$_POST['datum'].")";
	$r3 = mysql_query("select count(*) from komentari where student=$student");
	if (mysql_result($r3,0,0)>0) {
		$r4 = mysql_query("update komentari set komentar='$komentar' where student=$student");
	} else {
		$r4 = mysql_query("insert into komentari set komentar='$komentar', student=$student");
	}
} # if ($stage==4)



if (($stage==3) || ($stage==4)) {
	$ra = mysql_query("select ime,prezime from studenti where id=$student");
	if (mysql_num_rows($ra)<0) {
		print "<h1>Greška!!!!!</h1>";
	} else {
		print "<h3>Student: ".mysql_result($ra,0,0)." ".mysql_result($ra,0,1)."</h3>\n";
	}

?>
<input type="hidden" name="student" value="<?=$student?>">
<p>Komentari za studenta - dopišite svoj komentar ispod:  (<a href="index.php">Nazad na početnu stranicu</a>)<br><br>
<textarea rows="20" cols="50" name="komentar"><?
$r5 = mysql_query("select komentar from komentari where student=$student");
if (mysql_num_rows($r5)>0) { print mysql_result($r5,0,0); }
?></textarea>
<br><br>
Profesor/asistent/demonstrator: <input type="text" name="asistent" value=""><br><br>
Datum: <input type="text" name="datum" value="<?=date("d. m. Y.");?>"><br><br>
<input type="submit" name="posalji" value="Pošalji komentar">
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="reset" value="Poništi komentar">
<?

} # if ($stage==3)

?>
</form>
<p>&nbsp;</p>
<p>(c) Vedran Ljubović, 2005.</p>
</body>
</html>