<?

// v2.9.3.1 (2007/03/12) + XSS fix
// v2.9.3.2 (2007/03/15) + uvijek vraćao zadnji zadatak u zadaći
// v2.9.3.3 (2007/03/22) + popravljene tabele za diff
// v2.9.3.4 (2007/04/05) + prosljeđivanje labgrupe kod slanja zadaće
// v2.9.3.5 (2007/04/06) + popravka fast forward dugmeta
// v3.0.0.0 (2007/04/09) + Release
// v3.0.0.1 (2007/04/28) + Novi sistem navigacije - tabela sa status stranice
// v3.0.0.2 (2007/05/03) + Popravka navigacije: broj zadataka u zadaći


function stud_zadaca() {

global $userid,$system_path,$predmet_id,$labgrupa;



# Standardna lokacija zadaca:

$lokacijazadaca="$system_path/zadace/$predmet_id/$userid/";
# Create db dir
if (!file_exists("$system_path/zadace/$predmet_id")) {
	mkdir ("$system_path/zadace/$predmet_id",0777);
}



if ($_POST['akcija'] == "slanje") {
	akcijaslanje($lokacijazadaca);
}





//////////////////////////
//  IMA LI AKTIVNIH?
//////////////////////////


$q01 = myquery("select count(*) from zadaca where predmet=$predmet_id and aktivna=1");
if (mysql_result($q01,0,0) == 0) {
	?><center><h1>Slanje zadaća trenutno nije aktivno</h1></center>
	<p><a href="<?=genuri()?>&sta=status">Nazad na status</a></p><?
	return;
}



//////////////////////////
//  ODREĐIVANJE ID ZADAĆE
//////////////////////////


# Poslani parametar:
$zadaca = intval($_GET['zadaca']);

// Da li neko pokušava da spoofa zadaću?
if ($zadaca!=0) {
	$q09 = myquery("SELECT count(*) FROM zadaca, labgrupa, student_labgrupa as sl
	WHERE sl.student=$userid and sl.labgrupa=labgrupa.id and labgrupa.predmet=zadaca.predmet and zadaca.id=$zadaca");
	if (mysql_result($q09,0,0)==0) {
		print niceerror("Ova zadaća nije iz vašeg predmeta!?");
		return;
	}
}


if ($zadaca==0) {
	// Zadnja zadaca na kojoj je radio
	$q10 = myquery("SELECT zadaca.id FROM zadatak, zadaca 
	WHERE zadaca.id=zadatak.zadaca and zadaca.aktivna=1 and zadaca.rok>curdate() and zadaca.predmet=$predmet_id and zadatak.student=$userid
	ORDER BY zadaca.id DESC LIMIT 1");

	if (mysql_num_rows($q10)>0)
		$zadaca = mysql_result($q10,0,0);
	else {
		// Nije radio ni na jednoj od aktivnih zadaca
		// Daj najstariju aktivnu zadacu
		$q11 = myquery("select id from zadaca where predmet=$predmet_id and rok>curdate() and aktivna=1 order by id limit 1");

		if (mysql_num_rows($q11)>0)
			$zadaca = mysql_result($q11,0,0);
		else {
			// Ako ni ovdje nema rezultata, znači da je svim 
			// zadaćama istekao rok. Daćemo zadnju zadaću.
			// Da li ima aktivnih provjerili smo u $q01
			$q12 = myquery("select id from zadaca where predmet=$predmet_id and aktivna=1 order by id desc limit 1");
			$zadaca = mysql_result($q12,0,0);
		}
	}
}


// Ove vrijednosti će nam trebati kasnije
$q13 = myquery("select naziv,zadataka,rok,programskijezik,attachment from zadaca where id=$zadaca");
$naziv = mysql_result($q13,0,0);
$brojzad = mysql_result($q13,0,1);
$rok = mysql_result($q13,0,2);
$jezik = mysql_result($q13,0,3);
$attachment = mysql_result($q13,0,4);



//////////////////////////
//  ODREĐIVANJE ZADATKA
//////////////////////////


// Poslani parametar:
$zadatak = intval($_GET['zadatak']);

if ($zadatak==0) { 
	// Prvi neurađeni zadatak u datoj zadaći
	// -- Zna li neko kako ovo izvesti kroz SQL?
	for ($i=1; $i<=$brojzad; $i++) {
		$q21 = myquery("select count(*) from zadatak where student=$userid and zadaca=$zadaca and redni_broj=$i");
		if (mysql_result($q21,0,0)<1) {
			$zadatak=$i;
			break;
		}
	}
	
	// Sve je uradio, daj zadnji
	if ($zadatak==0) $zadatak=$brojzad;
}



//////////////////////////
//  NAVIGACIJA
//////////////////////////

print "<center><h1>$naziv, Zadatak: $zadatak</h1></center>\n";


// Nova navigacija - kod kopiran iz stud_status

?>
<center><table cellspacing="0" cellpadding="2" border="1">
<tr><td>&nbsp;</td>

<?


// Zaglavlje tabele - potreban nam je max. broj zadataka u zadaci
$q30 = myquery("select zadataka from zadaca where predmet=$predmet_id order by zadataka desc limit 1");
if (mysql_num_rows($q30)<1) 
	$max_brzad=0;
else
	$max_brzad=mysql_result($q30,0,0);

for ($i=1;$i<=$max_brzad;$i++) {
	?><td>Zadatak <?=$i?>.</td><?
}

?>
</tr>
<?


/* Ovo se sve moglo kroz SQL rijesiti, ali necu iz razloga:
1. PHP je citljiviji
2. MySQL <4.1 ne podrzava subqueries */


// Status ikone:
$stat_icon = array("zad_bug", "zad_preg", "zad_copy", "zad_bug", "zad_preg", "zad_ok", "idea");
$stat_tekst = array("Bug u programu", "Pregled u toku", "Zadaća prepisana", "Bug u programu", "Pregled u toku", "Zadaća OK", "Novi zadatak");


$bodova_sve_zadace=0;

$q31 = myquery("select id,naziv,bodova,zadataka from zadaca where predmet=$predmet_id");
while ($r31 = mysql_fetch_row($q31)) {
	$m_zadaca = $r31[0];
	$m_mogucih += $r31[2];
	$m_brzad = $r31[3];
	?><tr>
	<td><?=$r31[1]?></td><?
	$m_bodova_zadaca = 0;

	for ($m_zadatak=1;$m_zadatak<=$m_brzad;$m_zadatak++) {
		// Uzmi samo rjesenje sa zadnjim IDom
		$q32 = myquery("select status,bodova from zadatak where student=$userid and zadaca=$m_zadaca and redni_broj=$m_zadatak order by id desc limit 1");
		if (mysql_num_rows($q32)>0) {
			$status = mysql_result($q32,0,0);
			$m_bodova_zadatak = mysql_result($q32,0,1);
			$m_bodova_zadaca += $m_bodova_zadatak;
		} else {
			$status = 6;
			$m_bodova_zadatak="";
		} 
		if ($m_zadaca==$zadaca && $m_zadatak==$zadatak)
			$bgcolor = ' bgcolor="#EEEEFF"'; 
		else 	$bgcolor = "";
		?><td <?=$bgcolor?>><a href="student.php?sta=zadaca&zadaca=<?=$m_zadaca?>&zadatak=<?=$m_zadatak?>&labgrupa=<?=$labgrupa?>"><img src="images/<?=$stat_icon[$status]?>.png" width="16" height="16" border="0" align="center" title="<?=$stat_tekst[$status]?>" alt="<?=$stat_tekst[$status]?>"> <?=$m_bodova_zadatak?></a></td><?
	}
	
	// Ispis praznih ćelija tabele za zadaće koje imaju manje od max. zadataka
	for ($i=$m_zadatak; $i<=$max_brzad; $i++) {
		print "<td>&nbsp;</td>\n";
	}

	?></tr><?
	$bodova_sve_zadace += $m_bodova_zadaca;
}


// Ukupno bodova za studenta
 
$bodova += $bodova_sve_zadace;

?>
</table></center>
<?

# Naslov i strelice lijevo-desno...


/*$onclick = 'onclick="self.location = \''.genuri();

# dugme "<<"
$q30 = myquery("select id from zadaca where id<$zadaca and predmet=$predmet_id order by id desc limit 1");
if (mysql_num_rows($q30)==0)
	$d1="disabled";
else
	$d1=$onclick.'&zadaca='.mysql_result($q30,0,0).'&zadatak=1\'"';

# dugme "<"
if ($zadatak==1)
	$d2="disabled";
else
	$d2=$onclick.'&zadaca='.$zadaca.'&zadatak='.($zadatak-1).'\'"';

# dugme "<"
if ($zadatak>=$brojzad) // $brzad određen kod Određivanja zadaće
	$d3="disabled";
else
	$d3=$onclick.'&zadaca='.$zadaca.'&zadatak='.($zadatak+1).'\'"';

# dugme ">>"
$q31 = myquery("select id from zadaca where id>$zadaca and predmet=$predmet_id order by id limit 1");
if (mysql_num_rows($q31)==0)
	$d4="disabled";
else
	$d4=$onclick.'&zadaca='.mysql_result($q31,0,0).'&zadatak=1\'"';



# Ispis zaglavlja

?>
<table width="100%" border="0">
<tr>
<td width="10%" align="center" valign="center"><input type="submit" value=" &lt;&lt; " <?=$d1?>></td>
<td width="10%" align="center" valign="center"><input type="submit" value=" &lt; " <?=$d2?>></td>
<td align="center" valign="center">
<h1>Zadaća: <?=$naziv?>, Zadatak: <?=$zadatak?></h1>
</td>
<td width="10%" align="center" valign="center"><input type="submit" value=" &gt; " <?=$d3?>></td>
<td width="10%" align="center" valign="center"><input type="submit" value=" &gt;&gt; " <?=$d4?>></td>
</tr></table>

<? 
*/



//////////////////////////
//  PORUKE I KOMENTARI
//////////////////////////


# Upit za izvjestaj skripte i komentar tutora

$q40 = myquery("select izvjestaj_skripte,komentar from zadatak where student=$userid and zadaca=$zadaca and redni_broj=$zadatak order by id desc limit 1");
if (mysql_num_rows($q40)>0) {
	$poruka = mysql_result($q40,0,0);
	$komentar = mysql_result($q40,0,1);

	if (preg_match("/\w/",$poruka)) {
		$poruka = str_replace("\n","<br/>\n",$poruka);
		?><p>Poruka kod kompajliranja:<br/><b><?=$poruka?></b></p><?
	}
	if (preg_match("/\w/",$komentar)) {
		?><p>Komentar tutora: <b><?=$komentar?></b></p><?
	# Poslani parametar:
	$zadaca = intval($_GET['zadaca']);
	
	// Da li neko pokušava da spoofa zadaću?
	if ($zadaca!=0) {
		$q09 = myquery("SELECT count(*) FROM zadaca, labgrupa, student_labgrupa as sl
		WHERE sl.student=$userid and sl.labgrupa=labgrupa.id and labgrupa.predmet=zadaca.predmet and zadaca.id=$zadaca");
		if (mysql_result($q09,0,0)==0) {
			print niceerror("Ova zadaća nije iz vašeg predmeta!?");
			return;
		}
	}
}
}


# Istek roka za slanje zadace

if (mysql2time($rok) <= time()) {
	print "<p><b>Vrijeme za slanje ove zadaće je isteklo.</b></p>";
	// Ovo je onemogućavalo copy&paste u Firefoxu :(
	//$readonly = "DISABLED";
} else {
	$readonly = "";
}




//////////////////////////
//  FORMA ZA SLANJE
//////////////////////////


if ($attachment) {

	# Attachment
	$q50 = myquery("select filename,vrijeme from zadatak where zadaca=$zadaca and redni_broj=$zadatak and student=$userid and status=1 order by id desc limit 1");
	if (mysql_num_rows($q50)>0) {
		$filename = mysql_result($q50,0,0);
		$the_file = "$lokacijazadaca/$zadaca/$filename";
		if (file_exists($the_file)) {
			$vrijeme = mysql_result($q50,0,01);
			$vrijeme = date("d. m. Y. h:i:s",mysql2time($vrijeme));
			$velicina = nicesize(filesize($the_file));
			$icon = "images/mimetypes/" . getmimeicon($the_file);
			$dllink = "student.php?sta=download&zadaca=$zadaca&zadatak=$zadatak";
			?>
			<center><table width="75%" border="1" cellpadding="6" cellspacing="0" bgcolor="#CCCCCC"><tr><td>
			<a href="<?=$dllink?>"><img src="<?=$icon?>" border="0"></a>
			</td><td>
			<p>Poslani fajl: <b><a href="<?=$dllink?>"><?=$filename?></a></b><br/>
			Datum slanja: <b><?=$vrijeme?></b><br/>
			Veličina: <b><?=$velicina?></b></p>
			</td></tr></table></center>
			<?
		}
		print "<p>Ako želite promijeniti datoteku iznad, izaberite novu i kliknite na dugme za slanje:</p>";
	} else {
		print "<p>Izaberite datoteku koju želite poslati i kliknite na dugme za slanje:</p>";
	}

	?>

	<form action="student.php" method="POST" enctype="multipart/form-data">
	<input type="hidden" name="sta" value="zadaca">
	<input type="hidden" name="akcija" value="slanje">
	<input type="hidden" name="zadaca" value="<?=$zadaca?>">
	<input type="hidden" name="zadatak" value="<?=$zadatak?>">
	<input type="hidden" name="labgrupa" value="<?=$labgrupa?>">
	<input type="file" name="attachment" size="50">
	</file>
	</center>
	<p>&nbsp;</p>
	<?

} else {

	# Forma
	$q60 = myquery("select ekstenzija from programskijezik where id=$jezik");
	$ekst = mysql_result($q60,0,0);

	?>
	
	<p>Kopirajte vaš zadatak u tekstualno polje ispod:</p>
	
	<form action="student.php" method="POST">
	<input type="hidden" name="sta" value="zadaca">
	<input type="hidden" name="akcija" value="slanje">
	<input type="hidden" name="zadaca" value="<?=$zadaca?>">
	<input type="hidden" name="zadatak" value="<?=$zadatak?>">
	<input type="hidden" name="labgrupa" value="<?=$labgrupa?>">
	
	<textarea rows="20" cols="80" name="program" <?=$readonly?>><? 
	$the_file = "$lokacijazadaca$zadaca/$zadatak$ekst";
	if (file_exists($the_file)) print join("",file($the_file)); 
	?></textarea>
	
	<?

}

?>

<table width="100%" border="0">
<tr><td align="center"><input type="reset" value=" Poništi izmjene "></td>
<td align="center"><input type="submit" value=" Pošalji zadatak! "></td></tr>
</table>
</form>
<?





}



function akcijaslanje($path) {

	global $userid;

	$zadaca = intval($_POST['zadaca']); 
	$zadatak = intval($_POST['zadatak']);
	$program = $_POST['program']; 

	// Da li to neko pokušava da spoofa zadaću?
	$q09 = myquery("SELECT count(*) FROM zadaca, labgrupa, student_labgrupa as sl
	WHERE sl.student=$userid and sl.labgrupa=labgrupa.id and labgrupa.predmet=zadaca.predmet and zadaca.id=$zadaca");
	if (mysql_result($q09,0,0)==0) {
		print niceerror("Ova zadaća nije iz vašeg predmeta!?");
		return;
	}

	logthis("Poslana zadaca $zadaca-$zadatak (student $userid)");

	// Ovo je potrebno radi pravljenja diff-a
	if (get_magic_quotes_gpc()) {
		$program = stripslashes($program);
	}

	// Podaci o zadaći
	$q200 = myquery("select programskijezik,rok,attachment,naziv from zadaca where id=$zadaca");
	$jezik = mysql_result($q200,0,0);
	$rok = mysql_result($q200,0,1);
	$attach = mysql_result($q200,0,2);
	$naziv_zadace = mysql_result($q200,0,3);

	// Provjera roka
	if (mysql2time($rok)<=time()) { 
		niceerror("Vrijeme za slanje zadaće je isteklo!"); 
		return; 
	}

	// Pravimo potrebne puteve
	if (!file_exists($path)) mkdir ($path,0777);
	if ($zadaca>0 && !file_exists("$path$zadaca")) mkdir ("$path$zadaca",0777);

	// Vrsta zadaće: textarea ili attachment
	if ($attach == 0) {
		// Određivanje ekstenzije iz jezika
		$q201 = myquery("select ekstenzija from programskijezik where id=$jezik");
		$ekst = mysql_result($q201,0,0);

		$filename = "$path$zadaca/$zadatak$ekst";

		// Temp fajl radi određivanja diff-a 
		$diffing=0;
		if (file_exists($filename)) {
			if (file_exists("$path$zadaca/difftemp")) 
				unlink ("$path$zadaca/difftemp");
			rename ($filename, "$path$zadaca/difftemp"); 
			$diffing=1;
		}

		// Kreiranje datoteke
		if (strlen($program)<=10) {
			biguglyerror("Niste kopirali zadaću!");
		} else if ($zadaca>0 && $zadatak>0 && ($f = fopen($filename,'w'))) {
			fwrite($f,$program);
			fclose($f);

			// Tabela "zadatak" funkcioniše kao log događaja u
			// koji se stvari samo dodaju
			$q202 = myquery("insert into zadatak set zadaca=$zadaca, redni_broj=$zadatak, student=$userid, status=1, vrijeme=now(), filename='$zadatak$ekst'");

			// Pravljenje diffa
			if ($diffing==1) {
				$diff = `/usr/bin/diff -u $path$zadaca/difftemp $filename`;
				$diff = my_escape($diff);
				if (strlen($diff)>1) {
					$q203 = myquery("select id from zadatak where zadaca=$zadaca and redni_broj=$zadatak and student=$userid and status=1 order by id desc limit 1");
					if (mysql_num_rows($q203) > 0) {
						$id = mysql_result($q203,0,0);
						$q204 = myquery("insert into zadatakdiff set zadatak=$id, diff='$diff'");
					}
				}
				unlink ("$path$zadaca/difftemp");
			}

			nicemessage($naziv_zadace."/Zadatak ".$zadatak." uspješno poslan!");
		} else {
			biguglyerror("Greška pri slanju zadaće. Kontaktirajte tutora.");
		}

	} else { // if ($attach==0)...
		$program = $_FILES['attachment']['tmp_name'];
		if ($program && (file_exists($program))) {
			// Nećemo pokušavati praviti diff
			$filename = "$path$zadaca/".$_FILES['attachment']['name'];
			unlink ($filename);
			rename($program, $filename);

			$q210 = myquery("insert into zadatak set zadaca=$zadaca, redni_broj=$zadatak, student=$userid, status=1, vrijeme=now(), filename='".$_FILES['attachment']['name']."'");

			nicemessage("Z".$naziv_zadace."/".$zadatak." uspješno poslan!");
		} else {
			biguglyerror("Greška pri slanju zadaće. Kontaktirajte tutora.");
		}
	}
}

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
