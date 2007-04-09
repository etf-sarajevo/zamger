<?

// v2.9.3.1 (2007/03/12) + XSS fixes
// v2.9.3.2 (2007/03/22) + Izbacio htmlspecialchars za diff pošto je on već
// escapovan prilikom ubacivanja u bazu
// v2.9.3.3 (2007/03/23) + Ispis imena zadaće
// v2.9.3.4 (2007/03/28) + Riješen potencijalni SQL injection kod ocjenjivanja zadaće


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
	$zadaca = intval($_GET['zadaca']);
	$q2 = myquery("select diff from zadatakdiff where zadatak=$zadaca");
	$diff = mysql_result($q2,0,0);
//	$diff = str_replace("\n\n","\n",$diff);
	//$diff = htmlspecialchars($diff);
	print "<pre>$diff</pre>";
	return;
}



# Glavni program

$stud_id=intval($_GET['student']);
$zadaca=intval($_GET['zadaca']);
$zadatak=intval($_GET['zadatak']);



# Header i provjera spoofinga
$q10 = myquery("select p.geshi,p.ekstenzija,zadaca.attachment,zadaca.predmet,zadaca.naziv from zadaca,programskijezik as p where zadaca.id=$zadaca and zadaca.programskijezik=p.id");
if (mysql_num_rows($q10)<1)
	niceerror("Neispravna zadaća.");

$q0 = myquery("select ime,prezime from student where id=$stud_id");
if (mysql_num_rows($q0)<1)
	niceerror("Neispravan student.");

print "<h1>".mysql_result($q0,0,0)." ".mysql_result($q0,0,1).", ".mysql_result($q10,0,4)."., Zadatak $zadatak.</h1>";



# --------------------
# Izmjena podataka

if ($_GET['akcija'] == "slanje") {
	$komentar = my_escape($_GET['komentar']);
	$status = intval($_GET['status']);
	$bodova = floatval($_GET['bodova']);
	$q1 = myquery("insert into zadatak set zadaca=$zadaca, redni_broj=$zadatak, student=$stud_id, status=$status, bodova=$bodova, vrijeme=now(), komentar='$komentar' ");
}



?>
<form action="qwerty.php" method="GET">
<input type="hidden" name="sta" value="pregled">
<input type="hidden" name="akcija" value="slanje">
<input type="hidden" name="student" value="<?=$stud_id?>">
<input type="hidden" name="zadaca" value="<?=$zadaca?>">
<input type="hidden" name="zadatak" value="<?=$zadatak?>">

<? 

$jezik = mysql_result($q10,0,0);
$ekst = mysql_result($q10,0,1);
$attach = mysql_result($q10,0,2);
$predmet_id = mysql_result($q10,0,3);

$lokacijazadaca="$system_path/zadace/$predmet_id/$stud_id/";


if ($attach == 0) {
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

} else {
	$q10a = myquery("select filename,vrijeme from zadatak where zadaca=$zadaca and redni_broj=$zadatak and student=$stud_id and status=1 order by id desc limit 1");
	$filename = mysql_result($q10a,0,0);
	$the_file = "$lokacijazadaca$zadaca/$filename";

	if (file_exists($the_file)) {
		$vrijeme = mysql_result($q10a,0,01);
		$vrijeme = date("d. m. Y. h:i:s",mysql2time($vrijeme));
		$velicina = nicesize(filesize($the_file));
		$icon = "images/mimetypes/" . getmimeicon($the_file);
		$dllink = "qwerty.php?sta=download&zadaca=$zadaca&zadatak=$zadatak";
		?>
		<center><table width="75%" border="1" cellpadding="6" cellspacing="0" bgcolor="#CCCCCC"><tr><td>
		<a href="<?=$dllink?>"><img src="<?=$icon?>" border="0"></a>
		</td><td>
		<p>Poslani fajl: <b><a href="<?=$dllink?>"><?=$filename?></a></b><br/>
		Veličina: <b><?=$velicina?></b></p>
		</td></tr></table></center><br/>
		<?
	}

}


$q11 = myquery("select status,bodova,izvjestaj_skripte,komentar from zadatak where zadaca=$zadaca and redni_broj=$zadatak and student=$stud_id order by id desc limit 1");

$status = mysql_result($q11,0,0);
$bodova = mysql_result($q11,0,1);
$izvjestaj_skripte = str_replace("\n","<br/>",mysql_result($q11,0,2));
$komentar = mysql_result($q11,0,3);
$komentar = str_replace("\"","&quot;",$komentar);

$q12 = myquery("select vrijeme from zadatak where zadaca=$zadaca and redni_broj=$zadatak and student=$stud_id order by id limit 1");
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
	<td><input type="text" size="50" name="komentar" value="<?=$komentar?>"></td>
</tr>
<tr>
	<td colspan="2" align="Center"><input type="submit" value="Izmijeni vrijednosti"></td>
</tr>
</table>
</form>



<?

##### HISTORIJA IZMJENA ######


$q21 = myquery("select id,vrijeme,status,bodova,komentar from zadatak where zadaca=$zadaca and redni_broj=$zadatak and student=$stud_id order by vrijeme");
if (mysql_num_rows($q21)>1) {

?>

<p>Historija izmjena:</p>
<ul><?
	while ($r21 = mysql_fetch_row($q21)) {
		$vrijeme_slanja = date("d. m. Y. H:i:s",mysql2time($r21[1]));
		print "<li><b>$vrijeme_slanja:</b> ".$statusi_array[$r21[2]];
		if ($r21[3]>0) print " (".$r21[3]." bodova)";
		if (strlen($r21[4])>0) print " - &quot;".$r21[4]."&quot;";
		$q22 = myquery("select count(zadatak) from zadatakdiff where zadatak=$r21[0]");
		if (mysql_result($q22,0,0)>0)
			print " (<a href=\"qwerty.php?sta=pregled&akcija=diff&zadaca=$r21[0]\">diff</a>)";
		print "</li>"; 
	}

?></ul><?


} 

# Kraj historije izmjena



} # function admin_pregled()



function getmimeicon($file) {
	$mimetypes = array(
		"text/x-c" => "source_c.png",
		"audio/mpeg" => "sound.png",
		"application/msword" => "document.png",
		"application/x-rar" => "zip.png",
		"application/x-tar" => "tar.png",
		"application/x-gzip" => "tar.png",
		"application/x-rpm" => "rpm.png",
		"text/plain" => "txt.png",
		"image/png" => "image.png",
		"image/gif" => "image.png",
		"image/jpeg" => "image.png",
		"text/plain" => "txt.png",
		"text/html" => "html.png",
		"application/pdf" => "pdf.png",
		"application/postscript" => "postscript.png",
		"video/quicktime" => "quicktime.png",
		"video/mp2p" => "video.png",
		"video/mpv" => "video.png",
		"application/x-zip" => "zip.png"
	);

	$mtekst = array(
		"text/x-c.cpp" => "source_cpp.png",
		"application/x-zip.odt" => "document.png",
		"application/x-zip.ods" => "spreadsheet.png",
		"application/x-zip.odg" => "vectorgfx.png",
		".svg" => "vectorgfx.png",
		".xls" => "spreadsheet.png",
		".html" => "html.png"
	);


	$file_output = `file -bi $file`;
	if (strstr($file_output, ";"))
		$file_output = substr($file_output, 0, strpos($file_output, ";"));
	if (strstr($file_output, ","))
		$file_output = substr($file_output, 0, strpos($file_output, ","));
	$ekst = $file_output . strrchr($file, ".");

	if ($mtekst[$ekst]) return $mtekst[$ekst];
	if ($mimetypes[$file_output]) return $mimetypes[$file_output];

	return "misc.png";
}


?>
