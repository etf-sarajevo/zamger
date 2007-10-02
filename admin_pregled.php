<?

// v2.9.3.1 (2007/03/12) + XSS fixes
// v2.9.3.2 (2007/03/22) + Izbacio htmlspecialchars za diff pošto je on već
// escapovan prilikom ubacivanja u bazu
// v2.9.3.3 (2007/03/23) + Ispis imena zadaće
// v2.9.3.4 (2007/03/28) + Riješen potencijalni SQL injection kod ocjenjivanja zadaće
// v3.0.0.0 (2007/04/09) + Release
// v3.0.0.1 (2007/04/12) + Izvršavanje programa na serveru, textarea za komentar polje, generalno čišćenje koda, komentari
// v3.0.0.2 (2007/05/03) + Nova combo-box kontrola za parametre programa
// v3.0.0.3 (2007/05/04) + Dodajem blank polje u combo-box kako bi bilo moguće kucati nešto
// v3.0.1.0 (2007/06/12) + Release
// v3.0.1.1 (2007/10/02) + Dodan logging


function admin_pregled() {

global $system_path;


?>
<html>
<head>
	<title>Pregled</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="css/style.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="js/combo-box.js"></script>
</head>
<body topmargin="0" leftmargin="0" bottommargin="0" rightmargin="0" bgcolor="#FFFFFF">

<?

// --------------------
// Standardni ulazni podaci i potrebne varijable

$stud_id=intval($_GET['student']);
$zadaca=intval($_GET['zadaca']);
$zadatak=intval($_GET['zadatak']);


// Provjera spoofinga
$q10 = myquery("select p.geshi,p.ekstenzija,zadaca.attachment,zadaca.predmet,zadaca.naziv from zadaca,programskijezik as p where zadaca.id=$zadaca and zadaca.programskijezik=p.id");
if (mysql_num_rows($q10)<1) {
	niceerror("Neispravna zadaća.");
	exit;
}

$q11 = myquery("select ime,prezime from student where id=$stud_id");
if (mysql_num_rows($q11)<1) {
	niceerror("Neispravan student.");
	exit;
}


$jezik = mysql_result($q10,0,0);
$ekst = mysql_result($q10,0,1);
$attach = mysql_result($q10,0,2);
$predmet_id = mysql_result($q10,0,3);

$lokacijazadaca="$system_path/zadace/$predmet_id/$stud_id/";



// --------------------
// AKCIJE


// Akcija: Ispis diffa

if ($_GET['akcija'] == "diff") {
	$diff_id=intval($_GET['diff_id']);
	$q2 = myquery("select diff from zadatakdiff where zadatak=$diff_id");
	$diff = mysql_result($q2,0,0);

	// Ovo ispod nema potrebe jer je diff već escapovan prilikom 
	// inserta u bazu (stud_zadaca.php)
	// $diff = str_replace("\n\n","\n",$diff);
	// $diff = htmlspecialchars($diff);

	print "<pre>$diff</pre>";
	print "</body></html>";
	return;
}


// Akcija: Izvršenje programa

if ($_GET['akcija'] == "izvrsi") {
	logthis("Izvrsena zadaca ($zadaca,$zadatak,$stud_id)");

	// čuvamo poslane podatke u bazi (ako ih nema)
	$stdin = $_GET['stdin'];
	$mstdin = my_escape($stdin);
	$q5 = myquery("select id from stdin where ulaz='$mstdin' and zadaca=$zadaca and redni_broj=$zadatak");
	if (mysql_num_rows($q5)==0) 
		$q6 = myquery("insert into stdin set ulaz='$mstdin', zadaca=$zadaca, redni_broj=$zadatak");


	// priprema fajlova
	$tstdin = str_replace('\\\n',"\n",$stdin);
	$tstdin .= "\n";
	$result = file_put_contents("/tmp/zamger-gdb.txt","run\nbt\n");
	if ($result) file_put_contents("/tmp/zamger-input.txt",$tstdin);
	if (!$result) {
		niceerror("Ne mogu kreirati potrebne datoteke u direktoriju /tmp");
		return;
	}
	
	// kompajliranje - FIXME: nema podrške za jezike?
	if (mysql_result($q10,0,0) == "C++")
		$kompajler = "g++";
	else
		$kompajler = "gcc";
	$the_file = "$lokacijazadaca$zadaca/$zadatak$ekst";
	$stdout = array();
	exec("$kompajler -ggdb $the_file -o /tmp/zamger.out 2>&1", $stdout, $retvar);
	if ($retvar != 0) {
		niceerror("Kompajliranje nije uspjelo! Slijedi ispis");
		print "<pre>".join("\n",$stdout)."</pre>";
		print "</body></html>";
		// čišćenje
		unlink("/tmp/zamger-gdb.txt");
		unlink("/tmp/zamger-input.txt");
		unlink("/tmp/zamger.out");
		return;
	}

	// izvršenje
	unset($stdout);
	chmod("/tmp/zamger.out", 0755);
	exec("gdb --batch --command=/tmp/zamger-gdb.txt /tmp/zamger.out </tmp/zamger-input.txt 2>&1", $stdout, $retvar);

	// Čistimo viškove iz stdout-a
	$ispis = join("\n",$stdout);
	$ispis = preg_replace("/^Using .*? library .*?\n/", "", $ispis);
	$ok = strpos($ispis,"\nProgram exited normally.\n");
	if ($ok)
		$ispis = substr($ispis,0,$ok);
	else {
		$greska = strpos($ispis,"\nProgram received signal SIGABRT, Aborted.\n");
		$backtrace = substr($ispis,$greska+42);
		$ispis = substr($ispis,0,$greska);
	}
	?>
	<h1>Rezultat izvršenja:</h1>
	<center><table width="95%" style="border:1px solid silver;" bgcolor="#FFF3F3"><tr><td>
	<pre><?=$ispis?></pre>
	</td></tr></table></center><br/><?
	if ($ok) {
		?><p><img src="images/zad_ok.png" width="16" height="16"> 
		Program se izvršio bez problema.</p><?
	} else {
		?><p><img src="images/zad_bug.png" width="16" height="16">
		Program se krahirao. Backtrace (obratiti pažnju na zadnje linije):</p>
		<pre><?=$backtrace?></pre>
		<?
	}
	?><p><a href="javascript:history.go(-1)">Nazad</a></p></body></html><?

	// čišćenje
	unlink("/tmp/zamger-gdb.txt");
	unlink("/tmp/zamger-input.txt");
	unlink("/tmp/zamger.out");
	return;
}


// Akcija: Izmjena statusa

if ($_GET['akcija'] == "slanje") {
	logthis("Izmjena statusa zadace ($zadaca,$zadatak,$stud_id)");
	$komentar = my_escape($_GET['komentar']);
	$status = intval($_GET['status']);
	$bodova = floatval($_GET['bodova']);
	$q1 = myquery("insert into zadatak set zadaca=$zadaca, redni_broj=$zadatak, student=$stud_id, status=$status, bodova=$bodova, vrijeme=now(), komentar='$komentar' ");
	// Nakon izmjene ispisujemo zadatak normalno
}




// --------------------
// PRIKAZ ZADATKA


// Header

print "<h1>".mysql_result($q11,0,0)." ".mysql_result($q11,0,1).", ".mysql_result($q10,0,4)."., Zadatak $zadatak.</h1>";


// Da li ispisati zadatak ili dugme za download attachmenta?

if ($attach == 0) {
	// Nije attachment

	$the_file = "$lokacijazadaca$zadaca/$zadatak$ekst";
	#print "The_file: $the_file<br>\n";
	if (file_exists($the_file)) { 
		$src = file_get_contents($the_file);  
	}
	
	// textarea.... mozda jednog dana
	/*?>
	<textarea rows="20" cols="80" name="program">
	</textarea><br/>
	<?*/

	
	// geshi - biblioteka za syntax highlighting
	
	include_once('geshi/geshi.php');
	$geshi =& new GeSHi($src, $jezik);
	?>
	<center><table width="95%" style="border:1px solid silver;" bgcolor="#F3F3F3"><tr><td>
	<?
	print $geshi->parse_code();
	// print join("",file($the_file));
	?></td></tr></table></center><br/><?


	// Formular za izvršavanje programa

	?><center><table style="border:1px solid silver;" cellspacing="0" cellpadding="6"><tr><td>
	Izvrši program sa sljedećim parametrima (kucajte \n za tipku enter):<br/>
	<form action="qwerty.php" method="GET">
	<input type="hidden" name="sta" value="pregled">
	<input type="hidden" name="akcija" value="izvrsi">
	<input type="hidden" name="student" value="<?=$stud_id?>">
	<input type="hidden" name="zadaca" value="<?=$zadaca?>">
	<input type="hidden" name="zadatak" value="<?=$zadatak?>">
	<select name="stdin" onKeyPress="edit(event)" onBlur="this.editing = false;">
	<?

	// Zadnje korišteni stdin se čuva u bazi
	$q15 = myquery("select ulaz from stdin where zadaca=$zadaca and redni_broj=$zadatak order by id desc");
	if (mysql_num_rows($q15)<1)
		print "<option></option>"; // bez ovoga nije moguće upisati novi tekst
	while ($r15 = mysql_fetch_row($q15)) {
		print "<option value=\"$r15[0]\">$r15[0]</option>\n";
	}
	?>
	</select><br/>

	<b>Pažnja!</b> Prije pokretanja provjerite da li program sadrži opasne naredbe.<br/>
	<input type="submit" value=" Izvrši program ">
	</form></table></center><br/>&nbsp;<br/>
	<?


} else {
	// Attachment

	$q20 = myquery("select filename,UNIX_TIMESTAMP(vrijeme) from zadatak where zadaca=$zadaca and redni_broj=$zadatak and student=$stud_id and status=1 order by id desc limit 1");
	$filename = mysql_result($q20,0,0);
	$the_file = "$lokacijazadaca$zadaca/$filename";

	if (file_exists($the_file)) {
		$vrijeme = date("d. m. Y. h:i:s", mysql_result($q20,0,1));
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



// Prikaz statusa sa log-om i izmjena

?>
<form action="qwerty.php" method="GET">
<input type="hidden" name="sta" value="pregled">
<input type="hidden" name="akcija" value="slanje">
<input type="hidden" name="student" value="<?=$stud_id?>">
<input type="hidden" name="zadaca" value="<?=$zadaca?>">
<input type="hidden" name="zadatak" value="<?=$zadatak?>">

<? 


$q21 = myquery("select status,bodova,izvjestaj_skripte,komentar from zadatak where zadaca=$zadaca and redni_broj=$zadatak and student=$stud_id order by id desc limit 1");

$status = mysql_result($q21,0,0);
$bodova = mysql_result($q21,0,1);
$izvjestaj_skripte = str_replace("\n","<br/>",mysql_result($q21,0,2));
$komentar = mysql_result($q21,0,3);
$komentar = str_replace("\"","&quot;",$komentar);

$q22 = myquery("select UNIX_TIMESTAMP(vrijeme) from zadatak where zadaca=$zadaca and redni_broj=$zadatak and student=$stud_id order by id limit 1");
$vrijeme_slanja = date("d. m. Y. H:i:s",mysql_result($q22,0,0));


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

// tabela status kodova

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
	<td valign="top">Komentar:</td>
	<td><textarea cols="50" rows="5" name="komentar"><?=$komentar?></textarea></td>
</tr>
<tr>
	<td colspan="2" align="Center"><input type="submit" value="Izmijeni vrijednosti"></td>
</tr>
</table>
</form>



<?

##### HISTORIJA IZMJENA ######


$q31 = myquery("select id,UNIX_TIMESTAMP(vrijeme),status,bodova,komentar from zadatak where zadaca=$zadaca and redni_broj=$zadatak and student=$stud_id order by vrijeme");
if (mysql_num_rows($q31)>1) {

?>

<p>Historija izmjena:</p>
<ul><?
	while ($r31 = mysql_fetch_row($q31)) {
		$vrijeme_slanja = date("d. m. Y. H:i:s",$r31[1]);
		print "<li><b>$vrijeme_slanja:</b> ".$statusi_array[$r31[2]];
		if ($r31[3]>0) print " (".$r31[3]." bodova)";
		if (strlen($r31[4])>0) print " - &quot;".$r31[4]."&quot;";
		$q32 = myquery("select count(zadatak) from zadatakdiff where zadatak=$r31[0]");
		if (mysql_result($q32,0,0)>0)
			print " (<a href=\"qwerty.php?sta=pregled&akcija=diff&zadaca=$zadaca&zadatak=$zadatak&student=$stud_id&diff_id=$r31[0]\">diff</a>)";
		print "</li>"; 
	}

?></ul><?


} 

// Kraj historije izmjena



} // function admin_pregled()



// Vrati odgovarajuću ikonu za fajl
// (Kandidat za prebacivanje u libvedran)

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

// ggrrrrrrrrrrrrr!!!
function file_put_contents($file,$tekst) {
	if (!($file = fopen($file,"w"))) return false;
	$bytes = fwrite($file,$tekst);
	fclose($file);
	return $bytes;
}

?>
