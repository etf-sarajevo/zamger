<?

function admin_pregled() {

global $system_path;


?>
<html>
<head>
	<title>Pregled</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="css/style.css" rel="stylesheet" type="text/css" />
</head>
<body topmargin="0" leftmargin="0" bottommargin="0" rightmargin="0" bgcolor="#FFFFFF">

<?


# Ispis diffa!

if ($_GET['akcija'] == "diff") {
	$zadaca = my_escape($_GET['zadaca']);
	$q2 = myquery("select diff from zadace_diff where zadaca=$zadaca");
	$diff = mysql_result($q2,0,0);
//	$diff = str_replace("\n\n","\n",$diff);
	$diff = str_replace("<","&lt;",$diff);
	$diff = str_replace(">","&gt;",$diff);
	print "<pre>$diff</pre>";
	return;
}



# Glavni program

$stud_id=$_GET['student'];
$zadaca=$_GET['zadaca'];
$zadatak=$_GET['zadatak'];


# Standardna lokacija zadaca:

$db = $_SESSION['db'];
if (!get_magic_quotes_gpc()) {
	$db = addslashes($db);
}

$lokacijazadaca="$system_path/zadace/$db/$stud_id/";


# Header
$q0 = myquery("select ime,prezime from studenti where id=$stud_id");
print "<h1>".mysql_result($q0,0,0)." ".mysql_result($q0,0,1).", Zadaca $zadaca., Zadatak $zadatak.</h1>";



# --------------------
# Izmjena podataka

if ($_GET['akcija'] == "slanje") {
	$komentar = my_escape($_GET['komentar']);
	$q1 = myquery("insert into zadace set zadaca=$zadaca, zadatak=$zadatak, student=$stud_id, status=".$_GET['status'].", bodova=".$_GET['bodova'].", vrijeme_slanja=now(), komentar='$komentar' ");
}



?>
<form action="qwerty.php" method="GET">
<input type="hidden" name="sta" value="pregled">
<input type="hidden" name="akcija" value="slanje">
<input type="hidden" name="student" value="<?=$stud_id?>">
<input type="hidden" name="zadaca" value="<?=$zadaca?>">
<input type="hidden" name="zadatak" value="<?=$zadatak?>">

<? 

$q10 = myquery("select jezik from zadace_objavljene where id=$zadaca");
$jezik = mysql_result($q10,0,0);

// JEZIK
if ($jezik == "C") { $ekst = ".c"; }
elseif ($jezik == "C++") { $ekst = ".cpp"; }
else { $ekst = ".cpp"; }	// DEFAULT EKSTENZIJA

$the_file = "$lokacijazadaca$zadaca/$zadatak$ekst";
#print "The_file: $the_file<br>\n";
if (file_exists($the_file)) { 
$src = join("",file($the_file));  
}

# textarea.... mozda jednog dana
/*?>
<textarea rows="20" cols="80" name="program">
</textarea><br/>
<?*/

# geshi


include_once('geshi/geshi.php');
$geshi =& new GeSHi($src, $jezik);
?>
<center><table width="95%" style="border:1px solid silver;" bgcolor="#F3F3F3"><tr><td>
<?
print $geshi->parse_code();
# print join("",file($the_file)); ?></td></tr></table></center><br/><?



$q11 = myquery("select status,bodova,izvjestaj_skripte,komentar from zadace where zadaca=$zadaca and zadatak=$zadatak and student=$stud_id order by id desc limit 1");

$status = mysql_result($q11,0,0);
$bodova = mysql_result($q11,0,1);
$izvjestaj_skripte = str_replace("\n","<br/>",mysql_result($q11,0,2));
$komentar = mysql_result($q11,0,3);
$komentar = str_replace("\"","&quot;",$komentar);

$q12 = myquery("select vrijeme_slanja from zadace where zadaca=$zadaca and zadatak=$zadatak and student=$stud_id order by id limit 1");
$vrijeme_slanja = date("d. m. Y. H:i:s",mysql2time(mysql_result($q12,0,0)));


?>
<table border="0">
<tr>
	<td>Vrijeme slanja:</td>
	<td><b><?=$vrijeme_slanja?></b></td>
</tr>
<tr>
	<td>Izvještaj skripte:</td>
	<td><i><?=$izvjestaj_skripte?></i></td>
</tr>
<tr>
	<td>Status</td>
	<td><select name="status"><?

function myoption($nr,$tx,$sel) {
print "$sel";
print "<option value=$nr";
if ($nr==$sel) print " selected";
print ">$tx</option>";
}

# statusi
$statusi_array = array("nepoznat status","nova zadaća","prepisana","ne može se kompajlirati","prošla test, predstoji kontrola","pregledana");
$brstatusa = 6;

for ($i=0;$i<$brstatusa;$i++)
	myoption($i,$statusi_array[$i],$status);



?></select></td>
</tr>
<tr>
	<td>Bodova:</td>
	<td><input type="text" size="20" name="bodova" value="<?=$bodova?>"></td>
</tr>
<tr>
	<td>Komentar:</td>
	<td><textarea cols="50" rows="5" name="komentar"><?=$komentar?></textarea></td>
</tr>
<tr>
	<td colspan="2" align="Center"><input type="submit" value="Izmijeni vrijednosti"></td>
</tr>
</table>
</form>



<?

##### HISTORIJA IZMJENA ######


$q21 = myquery("select id,vrijeme_slanja,status,bodova,komentar,izvjestaj_skripte from zadace where zadaca=$zadaca and zadatak=$zadatak and student=$stud_id order by vrijeme_slanja");
if (mysql_num_rows($q21)>1) {

?>

<p>Historija izmjena:</p>
<ul><?
	while ($r21 = mysql_fetch_row($q21)) {
		$vrijeme_slanja = date("d. m. Y. H:i:s",mysql2time($r21[1]));
		print "<li><b>$vrijeme_slanja:</b> ".$statusi_array[$r21[2]];
		if ($r21[3]>0) print " (".$r21[3]." bodova)";
		if (strlen($r21[4])>0) print " - &quot;".$r21[4]."&quot;";
		if (strlen($r21[5])>0) print " - &quot;".$r21[5]."&quot;";
		$q22 = myquery("select count(zadaca) from zadace_diff where zadaca=$r21[0]");
		if (mysql_result($q22,0,0)>0)
			print " (<a href=\"qwerty.php?sta=pregled&akcija=diff&zadaca=$r21[0]\">diff</a>)";
		print "</li>"; 
	}

?></ul><?


} 

# Kraj historije izmjena



} # function admin_pregled()

?>
