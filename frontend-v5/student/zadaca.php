<?

// STUDENT/ZADACA - slanje zadace za studente



function student_zadaca() {

global $userid,$conf_files_path;

require_once("Config.php");

// Backend stuff
require_once(Config::$backend_path."core/CourseUnit.php");
require_once(Config::$backend_path."core/AcademicYear.php");
require_once(Config::$backend_path."core/Portfolio.php");
require_once(Config::$backend_path."core/Person.php");

// Pošto je ovo ustvari dio lms/homework modula, ovo ispod ne treba biti opcionalno
require_once(Config::$backend_path."lms/homework/Homework.php");
require_once(Config::$backend_path."lms/homework/Assignment.php");
require_once(Config::$backend_path."lms/homework/ProgrammingLanguage.php");
require_once(Config::$backend_path."lms/homework/Diff.php");


// Akcije
if ($_REQUEST['akcija'] == "slanje") {
	akcijaslanje();
}


// Poslani parametri

$predmet = intval($_REQUEST['predmet']);
$ag = intval($_REQUEST['ag']);

$id_zadace = intval($_REQUEST['zadaca']);
$trenutni_zadatak = intval($_REQUEST['zadatak']);


// Podaci za zaglavlje
$cu = CourseUnit::fromId($predmet);
$ay = AcademicYear::fromId($ag);
$pf = Portfolio::fromCourseUnit($userid, $predmet, $ag); // ovo će provjeriti spoofing predmeta


// Odabir zadaće

$lista_zadaca = Homework::fromCourse($predmet, $ag);
$ima_aktivnih = false;
foreach ($lista_zadaca as $z) {
	if ($z->active) $ima_aktivnih=true;
}
if (!$ima_aktivnih) {
	zamgerlog("nijedna zadaća nije aktivna (pp$predmet ag$ag)", 3);
	niceerror("Trenutno nije aktivna nijedna zadaća.");
	return;
}


$zadaca = 0;

if ($id_zadace != 0) {
	// Izabrana je konkretna zadaća
	foreach ($lista_zadaca as $z) {
		if ($z->id == $id_zadace) {
			$zadaca = $z;
			break;
		}
	}
	if ($zadaca == 0) {
		zamgerlog("nepostojeca zadaca ili nije sa predmeta (z$id_zadace)", 3);
		biguglyerror("Ova zadaća nije iz vašeg predmeta");
		return;
	}
	if (!$zadaca->active) {
		zamgerlog("zadaća nije aktivna (z$id_zadace)", 3);
		niceerror("Zadaća (više) nije aktivna");
		return;
	}
}

else { // Korisnik nije zadao zadaću
	// Uzećemo zadaću kojoj prvoj ističe rok
	$zadace_po_roku = Homework::getLatestForStudent($userid, 1);
	if (count($zadace_po_roku) == 0) {
		// Svim zadaćama je istekao rok, uzimamo zadnju
		$zadaca = $lista_zadaca[count($lista_zadaca) - 1];
	} else {
		$zadaca = $zadace_po_roku[0];
	}
}

if ($trenutni_zadatak == 0) { // Korisnik nije odabrao zadatak, uzimamo prvi
	$trenutni_zadatak = 1;
}


// Standardna lokacija zadaca:

$lokacijazadaca = "$conf_files_path/zadace/$predmet-$ag/$userid/";



// Ove vrijednosti će nam trebati kasnije
/*$q60 = myquery("select naziv,zadataka,UNIX_TIMESTAMP(rok),programskijezik,attachment,dozvoljene_ekstenzije from zadaca where id=$zadaca");
$naziv = mysql_result($q60,0,0);
$brojzad = mysql_result($q60,0,1);
$rok = mysql_result($q60,0,2);
$jezik = mysql_result($q60,0,3);
$attachment = mysql_result($q60,0,4);
$zadaca_dozvoljene_ekstenzije = mysql_result($q60,0,5);*/




//  TABELA ZA NAVIGACIJU

print "<br/><br/><center><h1>".$zadaca->name.", Zadatak: $trenutni_zadatak</h1></center>\n";


// Statusne ikone:
$stat_icon = array("zad_bug", "zad_preg", "zad_copy", "zad_bug", "zad_preg", "zad_ok");
$stat_tekst = array("Bug u programu", "Pregled u toku", "Zadaća prepisana", "Bug u programu", "Pregled u toku", "Zadaća OK");


?>


<!-- zadace -->
<center>
<table cellspacing="0" cellpadding="2" border="0" id="zadace">
	<thead>
		<tr>
<?



?>
	<td>&nbsp;</td>
<?


// Zaglavlje tabele - potreban nam je max. broj zadataka u zadaći

$max_broj_zadataka = 0;
foreach ($lista_zadaca as $z) {
	if ($z->nrAssignments > $max_broj_zadataka) $max_broj_zadataka = $z->nrAssignments;
}

for ($i=1; $i<=$max_broj_zadataka; $i++) {
	?><td>Zadatak <?=$i?>.</td><?
}

?>
		</tr>
	</thead>
<tbody>
<?


// Tijelo tabele

// LEGENDA STATUS POLJA:
// 0 - nepoznat status
// 1 - nova zadaća
// 2 - prepisana
// 3 - ne može se kompajlirati
// 4 - prošla test, predstoji kontrola
// 5 - pregledana


/* Ovo se sve moglo kroz SQL rijesiti, ali necu iz razloga:
1. PHP je citljiviji
2. MySQL <4.1 ne podrzava subqueries */


$bodova_sve_zadace=0;

foreach ($lista_zadaca as $z) {
	?><tr>
	<th><?=$z->name?></th>
	<?

	$bodova_zadaca = 0;
	$slao_zadacu = false;


	for ($zadatak=1; $zadatak <= $max_broj_zadataka; $zadatak++) {
		// Ako tekuća zadaća nema toliko zadataka, ispisujemo blank polje
		if ($zadatak > $z->nrAssignments) {
			?><td>&nbsp;</td><?
			continue;
		}

		if ($z->id == $id_zadace && $zadatak == $trenutni_zadatak)
			$bgcolor = ' bgcolor="#DDDDFF"'; 
		else 	$bgcolor = "";

		try {
			$a = Assignment::fromStudentHomeworkNumber($userid, $z->id, $zadatak);

			$bodova_zadaca += $a->score;

			$ikona_komentar = "";
			if (strlen($a->comment) > 2)
				$ikona_komentar = "<img src=\"images/16x16/komentar.png\"  width=\"15\" height=\"14\" border=\"0\" title=\"Ima komentar\" alt=\"Ima komentar\" align=\"center\">";

			?>
			<td <?=$bgcolor?>>
				<a href="?sta=student/zadaca&predmet=<?=$predmet?>&ag=<?=$ag?>&zadaca=<?=$z->id?>&zadatak=<?=$zadatak?>"><img src="images/16x16/<?=$stat_icon[$a->status]?>.png" width="16" height="16" border="0" align="center" title="<?=$stat_tekst[$a->status]?>" alt="<?=$stat_tekst[$a->status]?>"> <?=$a->score?> <?=$ikona_komentar?></a>
			</td>
			<?

		} catch(Exception $e) {
			// student nije slao ovaj zadatak
			?>
			<td <?=$bgcolor?>>
				<a href="?sta=student/zadaca&predmet=<?=$predmet?>&ag=<?=$ag?>&zadaca=<?=$z->id?>&zadatak=<?=$zadatak?>"><img src="images/16x16/zad_novi.png" width="16" height="16" border="0" align="center" title="Novi zadatak" alt="Novi zadatak"></a>
			</td>
			<?
		}
	} // for ($zadatak=1...
	?>
	</tr>
	<?
}


?>
</tbody>
</table>
</center>
<?






//  PORUKE I KOMENTARI


// Upit za izvjestaj skripte i komentar tutora

?>
<br/><br/>
<center>
<table width="600" border="0"><tr><td>
<?

$a = 0;
try {
	$a = Assignment::fromStudentHomeworkNumber($userid, $id_zadace, $trenutni_zadatak);

	if (preg_match("/\w/", $a->compileReport)) {
		$poruka = str_replace("\n","<br/>\n", $a->compileReport);
		?><p>Poruka kod kompajliranja:<br/><b><?=$poruka?></b></p><?
	}

	if (preg_match("/\w/", $a->comment)) {
		// Link za odgovor na komentar
		$link="";
		if ($a->authorId > 0) {
			$tutor = Person::fromId($a->authorId);

			$naslov = urlencode("Odgovor na komentar (".$zadaca->name.", Zadatak $trenutni_zadatak)");
			$tekst = urlencode("> ".$a->comment);
			$primalac = urlencode($tutor->login." (".$tutor->name." ".$tutor->surname.")");

			$link = " (<a href=\"?sta=common/inbox&akcija=compose&naslov=$naslov&tekst=$tekst&primalac=$primalac\">odgovor</a>)";
		}
		?><p>Komentar tutora: <b><?=$a->comment?></b><?=$link?><?
	}

} catch(Exception $e) {
	// Student nije slao zadatak, ne radimo ništa
}

// Kraj tabele za komentar tutora
print "</td></tr></table>\n<br />\n";




//  FORMA ZA SLANJE


if ($zadaca->attachment) {
	// Attachment

	if ($a == 0) { // Student nije slao datoteku
		print "<p>Izaberite datoteku koju želite poslati i kliknite na dugme za slanje.";
		if ($zadaca->allowedExtensions != "")
			// FIXME: malo procesiranja?
			print " Dozvoljeni su sljedeći tipovi datoteka: <b>".$zadaca->allowedExtensions."</b>.";
		print "</p>\n";

	} else {
		$the_file = "$lokacijazadaca/$id_zadace/".$a->filename;
		if ($a->filename && file_exists("$conf_files_path/zadace/$predmet-$ag") && file_exists($the_file)) {
			$vrijeme = date("d. m. Y. h:i:s", $a->time);
			$velicina = nicesize(filesize($the_file));
			// Funkciju getmimeicon iz libvedran prebaciti u neku od backend klasa
			$icon = "images/mimetypes/" . getmimeicon($the_file);
			$dllink = "index.php?sta=common/attachment&zadaca=$id_zadace&zadatak=$trenutni_zadatak";

			?>
			
			<center><table width="75%" border="1" cellpadding="6" cellspacing="0" bgcolor="#CCCCCC"><tr><td>
			<a href="<?=$dllink?>"><img src="<?=$icon?>" border="0"></a>
			</td><td>
			<p>Poslani fajl: <b><a href="<?=$dllink?>"><?=$a->filename?></a></b><br/>
			Datum slanja: <b><?=$vrijeme?></b><br/>
			Veličina: <b><?=$velicina?></b></p>
			</td></tr></table></center>
			<?
			print "<p>Ako želite promijeniti datoteku iznad, izaberite novu i kliknite na dugme za slanje:</p>";
		}
	}

	if ($zadaca->deadline <= time()) {
		print "<p><b>Vrijeme za slanje ove zadaće je isteklo.</b></p>";
		// Ovo je onemogućavalo copy&paste u Firefoxu :(
		//$readonly = "DISABLED";
	} else {
		$readonly = "";
	}

	?>

	<form action="index.php" method="POST" enctype="multipart/form-data">
	<input type="hidden" name="sta" value="student/zadaca">
	<input type="hidden" name="akcija" value="slanje">
	<input type="hidden" name="predmet" value="<?=$predmet?>">
	<input type="hidden" name="ag" value="<?=$ag?>">
	<input type="hidden" name="zadaca" value="<?=$id_zadace?>">
	<input type="hidden" name="zadatak" value="<?=$trenutni_zadatak?>">
	<input type="file" name="attachment" size="50" <?=$readonly?>>
	</center>
	<p>&nbsp;</p>
	<?

} else {

	try {
		// Dodati da Homework::fromId očita i ProgrammingLanguage instancu
		$pl = ProgrammingLanguage::fromId($zadaca->programmingLanguageId);
		$ekst = $pl->extension;
	} catch(Exception $e) {
		// Nije zadan programski jezik, ostavljamo ekstenziju praznom
		$ekst = "";
	}

	if ($zadaca->deadline <= time()) {
		?>
		<p><b>Vrijeme za slanje ove zadaće je isteklo.</b></p>

		<?

		// Ovo je onemogućavalo copy&paste u Firefoxu :(
		//$readonly = "DISABLED";
	} else {
		?><p>Kopirajte vaš zadatak u tekstualno polje ispod:</p>

		<?
		$readonly = "";
	}
	
	?>
	
	<center>
	<?=genform("POST")?>
	<input type="hidden" name="zadaca" value="<?=$id_zadace?>">
	<input type="hidden" name="zadatak" value="<?=$zadatak?>">
	<input type="hidden" name="akcija" value="slanje">
	
	<textarea rows="20" cols="80" name="program" <?=$readonly?> wrap="off"><? 
	$the_file = "$lokacijazadaca$id_zadace/$trenutni_zadatak$ekst";
	if (file_exists("$conf_files_path/zadace/$predmet-$ag") && file_exists($the_file)) print join("",file($the_file)); 
	?></textarea>
	</center>	

	<?
}

?>

<center>
<table width="600" border="0">
<tr><td align="center"><input type="reset" value=" Poništi izmjene "></td>
<td align="center"><input type="submit" value=" Pošalji zadatak! "></td></tr>
</table>
</center>
</form>
<?


} // function student_zadaca()



function akcijaslanje() {

	global $userid,$conf_files_path;

	// FIXME Sve ovo bi u biti trebalo da odrađuje backend, a da frontend samo pošalje fajl backendu i dobije neki status.
	// Frontend ne bi trebalo da pristupa filesystemu uopšte (conf_files_path ukinuti).
	// Plan je da se uvede novi lms/file modul koji handluje generalni file upload i download

	// Parametri
	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);
	$id_zadace = intval($_POST['zadaca']); 
	$zadatak = intval($_POST['zadatak']);
	$program = $_POST['program'];

	$pf = Portfolio::fromCourseUnit($userid, $predmet, $ag); // ovo će provjeriti spoofing predmeta

	// Standardna lokacija zadaca
	$lokacijazadaca="$conf_files_path/zadace/$predmet-$ag/$userid/";
	if (!file_exists("$conf_files_path/zadace/$predmet-$ag")) {
		mkdir ("$conf_files_path/zadace/$predmet-$ag",0777, true);
	}

	try {
		$zadaca = Homework::fromId($id_zadace);
	} catch(Exception $e) {
		// Nepostojeca zadaća, main će ispisati grešku
		return;
	}

	if ($zadaca->courseUnitId != $predmet || $zadaca->academicYearId != $ag)
		// Zadaća nije na predmetu, main će ispisati grešku
		return;

	// Ovo je potrebno radi pravljenja diff-a
	if (get_magic_quotes_gpc()) {
		$program = stripslashes($program);
	}

	// Ako nije zadat jezik, postavi status na 4 (ceka pregled), inace na 1 (automatska kontrola)
	if ($zadaca->programmingLanguageId == 0) $prvi_status=4; else $prvi_status=1;

	// Provjera roka
	if ($zadaca->deadline <= time()) {
		niceerror("Vrijeme za slanje zadaće je isteklo!");
		zamgerlog("isteklo vrijeme za slanje zadaće z$zadaca",3); // nivo 3 - greska
		return; 
	}

	// Pravimo potrebne puteve
	if (!file_exists($lokacijazadaca)) mkdir ($lokacijazadaca,0777);
	if ($id_zadace>0 && !file_exists("$lokacijazadaca$id_zadace")) 
		mkdir ("$lokacijazadaca$id_zadace",0777);

	// Vrsta zadaće: textarea ili attachment
	if ($attach == 0) { // textarea
		if (!check_csrf_token()) return; // CSRF zaštita

		// Određivanje ekstenzije iz jezika
		try {
			$pl = ProgrammingLanguage::fromId($zadaca->programmingLanguageId);
			$ekst = $pl->extension;
		} catch(Exception $e) {
			// Nije zadan programski jezik, ostavljamo ekstenziju praznom
			$ekst = "";
		}

		$filename = "$lokacijazadaca$id_zadace/$zadatak$ekst";

		// Temp fajl radi određivanja diff-a 
		$diffing=0;
		if (file_exists($filename)) {
			if (file_exists("$lokacijazadaca$id_zadace/difftemp")) 
				unlink ("$lokacijazadaca$id_zadace/difftemp");
			rename ($filename, "$lokacijazadaca$id_zadace/difftemp"); 
			$diffing=1;
		}

		// Kreiranje datoteke
		if (strlen($program)<=10) {
			niceerror("Niste kopirali zadaću!");
			zamgerlog("poslao praznu zadacu z$id_zadace zadatak $zadatak",3); // nivo 3 - greska

		} else if ($id_zadace>0 && $zadatak>0 && ($f = fopen($filename,'w'))) {
			fwrite($f,$program);
			fclose($f);


			// Tabela "zadatak" funkcioniše kao log događaja u
			// koji se stvari samo dodaju
			$a = new Assignment;
			$a->homeworkId = $id_zadace;
			$a->assignNo = $zadatak;
			$a->studentId = $userid;
			$a->status = $prvi_status;
			$a->filename = "$zadatak$ekst";
			$a->authorId = $userid;
			$a->addAssignment();


			// Pravljenje diffa
			if ($diffing==1) {
				// Ovo ispod staviti u Diff klasu
				$diff = `/usr/bin/diff -u $lokacijazadaca$id_zadace/difftemp $filename`;
				$diff = my_escape($diff);
				if (strlen($diff)>1) {
					Diff::add($a->id, $diff);
				}
				unlink ("$lokacijazadaca$id_zadace/difftemp");
			}

			nicemessage($zadaca->name."/Zadatak ".$zadatak." uspješno poslan!");
			$zadaca->updateScore($userid);
			zamgerlog("poslana zadaca z$id_zadace zadatak $zadatak",2); // nivo 2 - edit

		} else {
			zamgerlog("greska pri slanju zadace (zadaca z$id_zadace zadatak $zadatak filename $filename)",3);
			niceerror("Greška pri slanju zadaće. Kontaktirajte tutora.");
		}

	} else { // if ($attach==0)...
		$program = $_FILES['attachment']['tmp_name'];
		if ($program && (file_exists($program)) && $_FILES['attachment']['error']===UPLOAD_ERR_OK) {
			// Nećemo pokušavati praviti diff
			$ime_fajla = strip_tags(basename($_FILES['attachment']['name']));

			// Ukidam HTML znakove radi potencijalnog XSSa
			$ime_fajla = str_replace("&", "", $ime_fajla);
			$ime_fajla = str_replace("\"", "", $ime_fajla);
			$ime_fajla = str_replace("<", "", $ime_fajla);
			$puni_put = "$lokacijazadaca$id_zadace/$ime_fajla";

			// Provjeravamo da li je ekstenzija na spisku dozvoljenih
			$ext = ".".pathinfo($ime_fajla, PATHINFO_EXTENSION); // FIXME: postojeći kod očekuje da ekstenzije počinju tačkom...
			$db_doz_eks = explode(',', $zadaca->allowedExtensions);
			if ($zadaca->allowedExtensions != "" && !in_array($ext, $db_doz_eks)) {
				niceerror("Tip datoteke koju ste poslali nije dozvoljen.");
				print "<p>Na ovoj zadaći dozvoljeno je slati samo datoteke jednog od sljedećih tipova: <b>".$zadaca->allowedExtensions."</b>.<br>
				Vi ste poslali datoteku tipa: <b>$ext</b>.</p>";
				zamgerlog("pogresan tip datoteke (z$id_zadace)", 3);
				return;
			}

			unlink ($puni_put);
			rename($program, $puni_put);

			// Escaping za SQL
			$ime_fajla = my_escape($ime_fajla);

			// Tabela "zadatak" funkcioniše kao log događaja u
			// koji se stvari samo dodaju
			$a = new Assignment;
			$a->homeworkId = $id_zadace;
			$a->assignNo = $zadatak;
			$a->studentId = $userid;
			$a->status = $prvi_status;
			$a->filename = "$ime_fajla";
			$a->authorId = $userid;
			$a->addAssignment();

			nicemessage($zadaca->name."/".$zadatak." uspješno poslan!");
			$zadaca->updateScore($userid);
			zamgerlog("poslana zadaca z$id_zadace zadatak $zadatak (attachment)",2); // nivo 2 - edit

		} else {
			switch ($_FILES['attachment']['error']) { 
				case UPLOAD_ERR_OK:
					$greska="Poslali ste praznu ili nepostojeću datoteku.";
					break;
				case UPLOAD_ERR_INI_SIZE: 
					$greska="Poslana datoteka je veća od dozvoljene. Trenutno maksimalna dozvoljena veličina je ".ini_get('upload_max_filesize'); 
					break;
				case UPLOAD_ERR_FORM_SIZE: 
					$greska="Poslana datoteka je veća od dozvoljene."; // jednom ćemo omogućiti nastavniku da ograniči veličinu kroz formu
					break;
				case UPLOAD_ERR_PARTIAL: 
					$greska="Slanje datoteke je prekinuto, vjerovatno zbog problema sa vašom konekcijom. Molimo pokušajte ponovo."; 
					break;
				case UPLOAD_ERR_NO_FILE: 
					$greska="Poslali ste praznu ili nepostojeću datoteku.";
					break;
				case UPLOAD_ERR_NO_TMP_DIR: 
					$greska="1 Greška u konfiguraciji Zamgera: nepostojeći TMP direktorij.";
					break;
				case UPLOAD_ERR_CANT_WRITE: 
					$greska="2 Greška u konfiguraciji Zamgera: nemoguće pisati u TMP direktorij.";
					break;
				case UPLOAD_ERR_EXTENSION: 
					$greska="3 Greška u konfiguraciji Zamgera: neka ekstenzija sprječava upload.";
					break;
				default: 
					$greska="Nepoznata greška u slanju datoteke. Kod: ".$_FILES['attachment']['error'];
			} 
			zamgerlog("greska kod attachmenta (z$id_zadace): $greska",3);
			niceerror("$greska");
		}
	}
}

?>
