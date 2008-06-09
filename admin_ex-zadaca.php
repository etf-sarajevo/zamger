<?

function admin_zadaca() {

?>
<html>
<head>
	<title>Zadaća</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="css/style.css" rel="stylesheet" type="text/css" />
</head>
<body topmargin="0" leftmargin="0" bottommargin="0" rightmargin="0" bgcolor="#FFFFFF">



<center><table cellspacing="0" cellpadding="2" border="1">
<tr><td>&nbsp;</td>

<?

$stud_id = $_GET['student'];


$q20 = myquery("select zadatak from zadace where student=$stud_id group by zadatak order by zadatak");
$broj_zadataka = mysql_num_rows($q20);
$i=0;
while ($r20 = mysql_fetch_row($q20)) {
	?><td>Zadatak <?=$r20[0];?></td><?
	$zadarr[$i++]=$r20[0];
}

?>
<td><b>Ukupno bodova</b></td>
<?



// LEGENDA STATUS POLJA:
// 0 - nepoznat status
// 1 - nova zadaća
// 2 - prepisana
// 3 - ne može se kompajlirati
// 4 - prošla test, predstoji kontrola
// 5 - pregledana


$q21 = myquery("select zadaca,zadatak,status,bodova from zadace where student=$stud_id order by zadaca,zadatak");

$zdc=-1;
$totzb=0;


while ($r21 = mysql_fetch_row($q21)) {
	if ($zdc != $r21[0]) {
		?>&nbsp;</td><?
		if ($zdc != -1) print "<td><?=$totb?></td>";
		?></tr><tr><td>Zadaća <?=$r21[0]?><?
		$zdc=$r21[0];
		$zdtk=0;
		$totzb += $totb;
		$mogucih += 2;
		$totb=0;
	}
	while ($zdtk < $r21[1]) {
		?>&nbsp;</td><td align="center"><?
		$zdtk++;
	}
	if ($r21[2] == 1 || $r21[2] == 4) { // Looking
		$icon = "zad_preg";
		$title = "Pregled u toku";
	} else if ($r21[2] == 2) { // Looking
		$icon = "zad_copy";
		$title = "Zadaća prepisana";
	} else if ($r21[2] == 5) { // Looking
		$icon = "zad_ok";
		$title = "OK";
	} else  { // BUG - 3, 0, 6...
		$icon = "zad_bug";
		$title = "Bug u programu";
	}
	?><a href="javascript:window.opener.openzadaca(<?=$stud_id?>,<?=$r21[0]?>,<?=$r21[1]?>)"><img src="images/<?=$icon?>.png" width="16" height="16" border="0" align="center" title="<?=$title?>" alt="<?=$title?>"> <?=$r21[3]?></a><?
	$totb += $r21[3];
}

$mogucih += 2;
$totzb += $totb;
$bodova += $totzb;

?>
&nbsp;</td><td><?=$totb?></td></tr>
<tr><td colspan="<?=$broj_zadataka+1?>" align="right">UKUPNO: </td>
<td><?=$totzb?></td></tr>
</table></center>
<?


}

?>
