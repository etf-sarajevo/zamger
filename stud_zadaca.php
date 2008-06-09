<?



function stud_zadaca() {

global $stud_id,$system_path;


print "STUD_ID: $stud_id<br><br><br><br>";


# Standardna lokacija zadaca:

$db = $_SESSION['db'];
if (!get_magic_quotes_gpc()) {
	$db = addslashes($db);
}

$lokacijazadaca="$system_path/zadace/$db/$stud_id/";
# Create db dir
if (!file_exists("$system_path/zadace/$db")) mkdir ("zadace/$db",0777);



if ($_POST['akcija'] == "slanje") {
	akcijaslanje($lokacijazadaca);
}



# Utvrditi koja je zadnja...

if ($_GET['zadaca']>0) {
	$zadaca=$_GET['zadaca'];
	if ($_GET['zadatak']>0)
		$zadatak=$_GET['zadatak'];
	else
		$zadatak=dajzadatak($zadaca);
} else {

	$zadaca=$zadatak=0;

	$q1 = myquery("select zadaca from zadace where student=$stud_id group by zadaca order by zadaca desc limit 1");
	if (mysql_num_rows($q1)>0)
		$zadnja_koju_je_radio=mysql_result($q1,0,0);
	else
		$zadnja_koju_je_radio=1;

	$q2 = myquery("select id from zadace_objavljene where rok>curdate() and aktivna=1 order by id");
	$utvrdjeno=0;
	while (mysql_num_rows($q2)>0 && $r2 = mysql_fetch_row($q2)) {
		if ($r2[0] == $zadnja_koju_je_radio) {
			$zadaca = $r2[0];
			$zadatak = dajzadatak($zadaca);
			$utvrdjeno=1;
		} else if ($r2[0] > $zadnja_koju_je_radio) {
			$zadaca=$r2[0];
			$zadatak=1;
			$utvrdjeno=1;
		}
	}

	if ($utvrdjeno==0) {	
		$zadaca=$zadnja_koju_je_radio;
		$zadatak=dajzadatak($zadaca);
	}
}


// Provjera koja je zadnja aktivna zadaća i da li uopšte ima aktivnih

$q3 = myquery("select id,rok from zadace_objavljene where aktivna=1 order by id desc limit 1");
if (mysql_num_rows($q3) == 0) {
	?><center><h1>Slanje zadaća trenutno nije aktivno</h1></center>
	<p><a href="student.php?sta=status">Nazad na status</a></p><?
	return;
}
$zadnja_objavljena = mysql_result($q3,0,0);

// Ostali podaci o zadaci

$q4 = myquery("select zadataka,jezik,rok from zadace_objavljene where id=$zadaca");
$brojzad = mysql_result($q4,0,0);
$jezik = mysql_result($q4,0,1);
$rok = mysql_result($q4,0,2);

// JEZIK
if ($jezik == "C") { $ekst = ".c"; }
elseif ($jezik == "C++") { $ekst = ".cpp"; }
else { $ekst = ".cpp"; }	// DEFAULT EKSTENZIJA



// Generisanje dugmadi za lijevo/desno 

$k = 'onclick="self.location = \'student.php?sta=zadaca&';

if ($zadaca==1) $d1="disabled"; else $d1=$k.'zadaca='.($zadaca-1).'&zadatak=1\'"';

if ($zadaca==$zadnja_objavljena) $d4="disabled"; else $d4=$k.'zadaca='.($zadaca+1).'&zadatak=1\'"';

if ($zadatak==1 && $zadaca==1) $d2="disabled";
elseif ($zadatak==1) { 
	$q5 = myquery("select zadataka from zadace_objavljene where id=".($zadaca-1));
	$d2=$k.'zadaca='.($zadaca-1).'&zadatak='.mysql_result($q5,0,0).'\'"'; 
} else $d2=$k.'zadaca='.($zadaca).'&zadatak='.($zadatak-1).'\'"';

if ($zadatak==$brojzad && $zadaca==$zadnja_objavljena) $d3="disabled";
elseif ($zadatak==$brojzad) $d3=$k.'zadaca='.($zadaca+1).'&zadatak=1\'"';
else $d3=$k.'zadaca='.($zadaca).'&zadatak='.($zadatak+1).'\'"';



# Upit za izvjestaj skripte i komentar tutora

$q5 = myquery("select izvjestaj_skripte,komentar from zadace where student=$stud_id and zadaca=$zadaca and zadatak=$zadatak order by id desc limit 1");
$poruka = mysql_result($q5,0,0);
$komentar = mysql_result($q5,0,1);



# Ispis zaglavlja

?>
<table width="100%" border="0">
<tr>
<td width="10%" align="center" valign="center"><input type="submit" value=" &lt;&lt; " <?=$d1?>></td>
<td width="10%" align="center" valign="center"><input type="submit" value=" &lt; " <?=$d2?>></td>
<td align="center" valign="center">
<h1>Zadaća: <?=$zadaca?>, Zadatak: <?=$zadatak?></h1>
</td>
<td width="10%" align="center" valign="center"><input type="submit" value=" &gt; " <?=$d3?>></td>
<td width="10%" align="center" valign="center"><input type="submit" value=" &gt;&gt; " <?=$d4?>></td>
</tr></table>

<? 
if (preg_match("/\w/",$poruka)) {
	$poruka = str_replace("\n","<br/>\n",$poruka);
	print "<p>Poruka kod kompajliranja:<br/><b>$poruka</b></p>";
}
if (preg_match("/\w/",$komentar)) {
	print "<p>Komentar tutora: <b>$komentar</b></p>";
}
if (mysql2time($rok) <= time()) {
	print "<p><b>Vrijeme za slanje ove zadaće je isteklo.</b></p>";
	//$readonly = "DISABLED";
} else {
	$readonly = "";
}
?>

<p>Kopirajte vaš zadatak u tekstualno polje ispod:</p>

<form action="student.php" method="POST">
<input type="hidden" name="sta" value="zadaca">
<input type="hidden" name="akcija" value="slanje">
<input type="hidden" name="zadaca" value="<?=$zadaca?>">
<input type="hidden" name="zadatak" value="<?=$zadatak?>">

<textarea rows="20" cols="80" name="program" <?=$readonly?>>
<? 
$the_file = "$lokacijazadaca$zadaca/$zadatak$ekst";
if (file_exists($the_file)) print join("",file($the_file)); ?>
</textarea>


<table width="100%" border="0">
<tr><td align="center"><input type="reset" value=" Poništi izmjene "></td>
<td align="center"><input type="submit" value=" Pošalji zadatak! "></td></tr>
</table>
</form>

<?






}

function dajzadatak($zadaca) {
global $stud_id;

	$q100 = myquery("select zadataka from zadace_objavljene where id=$zadaca");
	$brojzad = mysql_result($q100,0,0);

	$q101 = myquery("select zadatak from zadace where student=$stud_id and zadaca=$zadaca order by zadatak");
	$prethodna=0;
	while ($r101 = mysql_fetch_row($q101)) {
		if ($r101[0]>$prethodna+1) { $zadatak=$prethodna+1; return $zadatak; }
		$prethodna=$r101[0];
	}
	if ($prethodna<$brojzad) { $zadatak=$prethodna+1; return $zadatak; }

	$q102 = myquery("select zadatak from zadace where student=$stud_id and zadaca=$zadaca order by vrijeme_slanja desc limit 1");
	$zadatak = mysql_result($q102,0,0);
	return $zadatak;
}


function akcijaslanje($path) {

	global $stud_id;

	$zadaca = intval($_POST['zadaca']); 
	$zadatak = intval($_POST['zadatak']);
	$program = $_POST['program'];
	if (get_magic_quotes_gpc()) {
		$program = stripslashes($program);
	}
logthis("Poslana zadaca $zadaca-$zadatak (student $stud_id)");

	if (!file_exists($path)) mkdir ($path,0777);
	if ($zadaca>0 && !file_exists("$path$zadaca")) mkdir ("$path$zadaca",0777);
	$q200 = myquery("select jezik,rok from zadace_objavljene where id=$zadaca");
	$j = mysql_result($q200,0,0);
	$rok = mysql_result($q200,0,1);

	// Provjera roka
	if (mysql2time($rok)<=time()) { niceerror("Vrijeme za slanje zadaće je isteklo!"); return; }

	// Jezik
	if ($j == "C") { $ekst = ".c"; }
	elseif ($j == "C++") { $ekst = ".cpp"; }
	else { $ekst = ".cpp"; }	// DEFAULT EKSTENZIJA
	$filename = "$path$zadaca/$zadatak$ekst";

	$diffing=0;
	if (file_exists($filename)) {
//		$oldprogarr = file($filename);
		unlink ("$path$zadaca/difftemp");
		rename ($filename, "$path$zadaca/difftemp"); 
		$diffing=1;
	}

	if ($zadaca>0 && $zadatak>0 && strlen($program)>10 && ($f = fopen($filename,'w'))) {
		fwrite($f,$program);
		fclose($f);

		# Status 1 = nova zadaća
		/*$q201 = myquery("select count(*) from zadace where zadaca=$zadaca and zadatak=$zadatak and student=$stud_id");
		if (mysql_result($q201,0,0)==0) {
			$q202 = myquery("insert into zadace set zadaca=$zadaca, zadatak=$zadatak, student=$stud_id, status=1, vrijeme_slanja=now()");
		} else {
			$q203 = myquery("update zadace set status=1, vrijeme_slanja=now(), izvjestaj_skripte='', komentar='', bodova=0 where zadaca=$zadaca and zadatak=$zadatak and student=$stud_id");
		}*/
		$q202 = myquery("insert into zadace set zadaca=$zadaca, zadatak=$zadatak, student=$stud_id, status=1, vrijeme_slanja=now()");

		# Pravljenje diffa
		if ($diffing==1) {
/*			$newprogarr = file($filename);
			$diffarr = array_diff($oldprogarr,$newprogarr);
			$diff = "";
			foreach ($diffarr as $key => $value) {
				if (!$diffarr[$key-1]) { $diff .= " ".$oldprogarr[$key-2]." ".$oldprogarr[$key-1]; }
				$diff .= "-".$oldprogarr[$key];
				$diff .= "+".$newprogarr[$key];
				if (!$diffarr[$key+1]) { $diff .= " ".$oldprogarr[$key+1]." ".$oldprogarr[$key+2]; }
			}*/
			$diff = `/usr/bin/diff -u $path$zadaca/difftemp $filename`;
			$diff = my_escape($diff);
			if (strlen($diff)>1) {
				$q203 = myquery("select id from zadace where zadaca=$zadaca and zadatak=$zadatak and student=$stud_id and status=1 order by id desc limit 1");
				$id = mysql_result($q203,0,0);
				$q204 = myquery("insert into zadace_diff set zadaca=$id, diff='$diff'");
			}
			unlink ("$path$zadaca/difftemp");
		}

		nicemessage("Z".$zadaca."/".$zadatak." uspješno poslan!");
	} else {
		biguglyerror("Greška pri slanju zadaće. Kontaktirajte tutora.");
	}
}

?>
