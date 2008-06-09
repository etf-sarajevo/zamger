<html>
<head>
<title>Prisustvo na vježbama</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="style.css" rel="stylesheet" type="text/css" />
</head>
<body>

<h1>Prisustvo na vježbama</h1>

<? 

include("mysql.php");
dbconnect();

if (!$_POST['stage'] || $_POST['stage']==0) {
	$stage=1;

	$dan=date("d"); $mjesec=date("m"); $godina=date("Y"); 
	$vrijeme=date("H:i");
	$asistent=$demonstrator="";
	$grupa=-1;
} elseif($_POST['stage']>0) {
	$stage=2;
	
	$dan=$_POST['dan'];
	$mjesec=$_POST['mjesec'];
	$godina=$_POST['godina'];
	$vrijeme=$_POST['vrijeme'];
	$asistent=$_POST['asistent'];
	$demonstrator=$_POST['demonstrator'];
	$grupa=$_POST['grupa'];
}

if (($_POST['stage']==2) && ($_POST['slanje'])) {

	# unos casa
	$datum = $_POST['godina']."-".$_POST['mjesec']."-".$_POST['dan'];

	$result = mysql_query("insert into casovi set datum='$datum', vrijeme='$vrijeme', grupa=$grupa, asistent='$asistent', demonstrator='$demonstrator'");
	$result = mysql_query("select id from casovi where datum='$godina-$mjesec-$dan' and vrijeme='$vrijeme' and grupa=$grupa");
	$cas_id = mysql_result($result,0,0);

	# unos prisustva i ocjena

	$result = mysql_query("select id from studenti where grupa=$grupa");
	while ($drugired = mysql_fetch_row($result)) {
		$stud_id = $drugired[0];
		$checkid = "check$stud_id";
		if ($_POST[$checkid]) { $check = 0; } else { $check = 1; }
		$bodovaid = "bodova$stud_id";
		$bodova = $_POST[$bodovaid]*2;
		$result2 = mysql_query("insert into prisustvo set student=$stud_id, cas=$cas_id, prisutan=$check, ocjena=$bodova");
	}
	
	print "<p><span class=\"msg\">Čas je unesen.</span> <a href=\"index.php\">Povratak na početnu stranicu</a></p>";
	exit;
}

?>

<form action="prisustvo.php" method="POST" name="forma">
<input type="hidden" name="stage" value="<?=$stage?>">
<table width="100%" border="0">
	<tr>
		<td>&nbsp;</td>
		<td>Datum:</td>
		<td>
			<select name="dan"><?
			for ($i=1; $i<=31; $i++) {
				print "<option value=\"$i\"";
				if ($i==$dan) print " selected";
				print ">$i</option>";
			}
			?></select>&nbsp;&nbsp;
			<select name="mjesec"><?
			for ($i=1; $i<=12; $i++) {
				print "<option value=\"$i\"";
				if ($i==$mjesec) print " selected";
				print ">$i</option>";
			}
			?></select>&nbsp;&nbsp;
			<select name="godina"><?
			for ($i=2005; $i<=2010; $i++) {
				print "<option value=\"$i\"";
				if ($i==$godina) print " selected";
				print ">$i</option>";
			}
			?></select>
		</td>
		<td>Vrijeme: <input type="text" size="10" name="vrijeme" value="<?=$vrijeme?>"></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td colspan="2">Asistent: <input type="text" size="20" name="asistent" value="<?=$asistent?>"></td>
		<td>Demonstrator: <input type="text" size="20" name="demonstrator" value="<?=$demonstrator?>"></td>
		<td>&nbsp;</td>
	</tr>
	<tr><td colspan="5">&nbsp;</td></tr>
	<td><td colspan="5">Grupa:&nbsp;&nbsp; <select name="grupa" onchange="javascript:forma.submit()">
	<option>----</option><?
	$result = mysql_query("select * from grupe order by naziv");
	while ($jedanred = mysql_fetch_row($result)) {
		$grupa_id = $jedanred[0];
		$grupa_naziv = $jedanred[1];
		if ($grupa_id == $grupa) { $addtext=" selected"; } else { $addtext = ""; }
		print "<option value=\"$grupa_id\"$addtext>$grupa_naziv</option>";
	}
	?></select></td></tr>
</table>
<?
if ($stage>1) {
?>
<p>&nbsp;</p>
<table width="100%" border="0">
<tr bgcolor="#CCCCCC">
<td width="50"></td>
<td><b>Ime i prezime</b></td>
<td width="100"><b>Odsutan</b></td>
<!--td width="100"><b>Bodova za zadaću</b></td-->
</tr>

<?
$result2 = mysql_query("select id,ime,prezime from studenti where grupa=$grupa");
$imeprezime=array();
while ($drugired = mysql_fetch_row($result2)) {
	$stud_id = $drugired[0];
	$stud_ime = $drugired[1];
	$stud_prez = $drugired[2];
	$imeprezime[$stud_id]="$stud_prez $stud_ime";
}
uasort($imeprezime,"vsortcmp");
$i=0;
foreach ($imeprezime as $stud_id => $stud_imepr) {
	if ($i==1) { $i=0; $color="#DDDDDD"; } else { $i=1; $color="#EEEEEE"; }
	?>
	<tr bgcolor="<?=$color;?>">
		<td width="50"></td>
		<td><?=$stud_imepr?></a></td>
		<td width="100"><input type="checkbox" name="check<?=$stud_id?>"></td>
		<!--td width="100"><select name="bodova<?=$stud_id?>"><option value="0">----</option>
		<option value="0">0</option>
		<option value="0.5">0,5</option>
		<option value="1">1</option>
		<option value="1.5">1,5</option>
		<option value="2">2</option>
		</select></td-->
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


function vsortcmp($a, $b) {
	$a=strtolower($a); $b=strtolower($b);
	$abeceda = array("a","A","b","B","c","C","č","Č","ć","Ć","d","đ","Đ","e","f","g","h","i","j","k","l","m","n","o","p", "q","r","s","š","Š","t","u","v", "w","x","y","z","ž","Ž");
	$min = (strlen($a)<strlen($b)) ? strlen($a) : strlen($b);
	for ($i=0; $i<$min; $i++) {
		$ca = substr($a,$i,1); if (ord($ca)>128) $ca = substr($a,$i,2);
		$cb = substr($b,$i,1); if (ord($cb)>128) $cb = substr($b,$i,2);
		$k=array_search($ca,$abeceda); $l=array_search($cb,$abeceda);
//		print "K: $k L: $l ZLJ: ".$ca. "       ";
		if ($k<$l) return -1; if ($k>$l) return 1;
	}
	if (strlen($a)<strlen($b)) return -1;
	return 1;
}


?>
</form>

</body>
</html>