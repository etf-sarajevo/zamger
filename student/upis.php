<?

// STUDENT/UPIS - svi podaci vezani za upis na sljedeću godinu/semestar

// TODO: Ovdje treba prebaciti sve što je trenutno u ugovoroucenju i kolizija


$dozvoljene_ekstenzije = ["png", "jpg", "pdf"];

// Pomoćna funkcija koja pronalazi sliku sa dozvoljenim ekstenzijama
function nadji_filename_slike($ag) {
	global $userid, $conf_files_path, $dozvoljene_ekstenzije;
	$dir = "$conf_files_path/uplatnice/$userid";
	
	foreach($dozvoljene_ekstenzije as $ext) {
		$filename = $dir . "/uplatnica-$ag.$ext";
		if (file_exists($filename)) return $filename;
	}
	return false;
}


function student_upis() {
	global $userid, $person, $conf_files_path, $dozvoljene_ekstenzije;
	
	$allYears = api_call("zamger/year")["results"];
	$trenutna = $sljedeca = false;
	foreach($allYears as $year) {
		if ($year['isCurrent'])
			$trenutna = $year;
		else if ($trenutna)
			$sljedeca = $year;
	}
	
	if (!$sljedeca) $sljedeca = $trenutna;
	
	?>
	<h1>Upis u studijsku <?=$sljedeca['name']?> godinu</h1>
	<?php
	
	
	if (param('akcija') == "brisanje" && check_csrf_token()) {
		// Brisemo sve vrste uplatnica
		$oldFilePath = nadji_filename_slike($sljedeca['id']);
		if ($oldFilePath) {
			unlink($oldFilePath);
			nicemessage("Uplatnica obrisana");
			zamgerlog("obrisana uplatnica", 2);
			zamgerlog2("obrisana uplatnica");
		} else {
			niceerror("Uplatnica ne postoji na serveru!");
		}
		?>
		<p><a href="?sta=student/upis">Nazad</a></p>
		<?php
		return;
	}
	
	if (param('akcija') == "slanje") {
		?>
		<h3>Slanje skenirane uplatnice</h3>
		<?php
		
		$dir = "$conf_files_path/uplatnice/$userid";
		if (!file_exists($dir))
			mkdir($dir, 0777, true);
		$uplatnica = $_FILES['attachment']['tmp_name'];
		if ($uplatnica && (file_exists($uplatnica)) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
			$filename = strip_tags(basename($_FILES['attachment']['name']));
			$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
			if (!in_array($ext, $dozvoljene_ekstenzije)) {
				niceerror("Dozvoljene su datoteke tipa: PNG, JPG ili PDF.");
				print "<p>Poslali ste datoteku tipa " . strtoupper($ext) . ". Molimo konvertujte sliku u odgovarajući tip i pošaljite ponovo.</p>";
				return;
			}
			
			// Brisemo sve vrste uplatnica
			$oldFilePath = nadji_filename_slike($sljedeca['id']);
			if ($oldFilePath) unlink($oldFilePath);
			
			// Pravimo rename fajla na željenu lokaciju
			$filepath = $dir . "/uplatnica-" . $sljedeca['id'] . ".$ext";
			rename($uplatnica, $filepath);
			chmod($filepath, 0640);
			nicemessage("Uplatnica uspješno uploadovana");
			zamgerlog("uploadovana uplatnica", 2);
			zamgerlog2("uploadovana uplatnica");
			?>
			<p><a href="?sta=student/upis">Nazad</a></p>
			<?php
			return;
		} else {
			switch ($_FILES['attachment']['error']) {
				case UPLOAD_ERR_OK:
					$greska = "Poslali ste praznu ili nepostojeću datoteku.";
					break;
				case UPLOAD_ERR_INI_SIZE:
					$greska = "Poslana datoteka je veća od dozvoljene. Trenutno maksimalna dozvoljena veličina je " . ini_get('upload_max_filesize');
					break;
				case UPLOAD_ERR_FORM_SIZE:
					$greska = "Poslana datoteka je veća od dozvoljene."; // jednom ćemo omogućiti nastavniku da ograniči veličinu kroz formu
					break;
				case UPLOAD_ERR_PARTIAL:
					$greska = "Slanje datoteke je prekinuto, vjerovatno zbog problema sa vašom konekcijom. Molimo pokušajte ponovo.";
					break;
				case UPLOAD_ERR_NO_FILE:
					$greska = "Poslali ste praznu ili nepostojeću datoteku.";
					break;
				case UPLOAD_ERR_NO_TMP_DIR:
					$greska = "1 Greška u konfiguraciji Zamgera: nepostojeći TMP direktorij.";
					break;
				case UPLOAD_ERR_CANT_WRITE:
					$greska = "2 Greška u konfiguraciji Zamgera: nemoguće pisati u TMP direktorij.";
					break;
				case UPLOAD_ERR_EXTENSION:
					$greska = "3 Greška u konfiguraciji Zamgera: neka ekstenzija sprječava upload.";
					break;
				default:
					$greska = "Nepoznata greška u slanju datoteke. Kod: " . $_FILES['attachment']['error'];
			}
			niceerror("$greska");
			return;
		}
	}
	
	if ($sljedeca['id'] == $trenutna['id'])
		print "<p>Ova studijska godina je već u toku... Ili kasnite sa upisom ili još uvijek nije aktiviran upis u narednu studijsku godinu.</p>";

	$enrollmentContract = api_call("enrollment/contract/$userid");
	if ($enrollmentContract['AcademicYear']['id'] != $sljedeca['id']) {
		niceerror("Niste popunili Ugovor o učenju za sljedeću godinu");
		print "<p>Popunite <a href=\"?sta=student/ugovoroucenju\">Ugovor o učenju</a> pa se vratite na ovu stranicu</p>\n";
	}
	
	$exp = $person['ExtendedPerson'];
	$missing = [];
	$required = [ 'sex', 'placeOfBirth', 'dateOfBirth', 'nationality', 'residenceAddress', 'residencePlace', 'addressStreetNo', 'addressPlace', 'phone', 'previousEducation', 'sourceOfFunding', 'activityStatusParent', 'activityStatusStudent', 'occupationParent', 'employmentStatusParent'];
	$names = [ 'spol', 'mjesto rođenja', 'datum rođenja', 'drzavljanstvo', 'adresa prebivališta', 'mjesto prebivališta', 'adresa boravišta', 'mjesto boravišta', 'broj telefona', 'srednja škola', 'izvor finansiranja', 'status aktivnosti roditelja', 'status aktivnosti studenta', 'zanimanje roditelja', 'status zaposlenosti roditelja'];
	for ($i=0; $i<count($required); $i++) {
		$data = $exp[$required[$i]];
		if ($required[$i] == 'previousEducation')
			$data = $data[0]['School']['id'];
		else if (is_object($data) || is_array($data))
			$data = $data['id'];
		if ($data === '' || $data === 0 || $data === '0' || $data === false || $data === null || $data === '1970-01-01')
			$missing[] = $names[$i];
	}
	if (!empty($missing)) {
		?><p><b>Nedostaju sljedeći podaci u vašem profilu:</b></p>
		<ul><li><?
		foreach($missing as $miss) print $miss . ", ";
		?></li></ul>
		<p>Molimo da popunite nedostajuće podatke u <a href="?sta=common/profil">Vašem profilu</a>. Navedeni podaci su potrebni za ispravno popunjavanje upisnih materijala i bez njih biste morali ručno popuniti te podatke.</p>
		<?php
	} else {
		?>
		<p>Molimo da još jednom prođete kroz podatke u <a href="?sta=common/profil">Vašem profilu</a> za slučaj da je došlo do promjena u odnosu na upis / prethodnu godinu.</p>
		<?php
	}
	
	
	?>

	<h3>Uplatnica</h3>
	<?php
	
	// Tražimo uplatnicu
	$filepath = nadji_filename_slike($sljedeca['id']);
	if ($filepath) {
		?>
		<p>Postavili ste fotografiju uplatnice za ovu akademsku godinu</p>
		<p><a href="?sta=common/attachment&tip=uplatnica&ag=<?=$sljedeca['id']?>">Pogledajte fotografiju</a></p>
		<?=genform("POST")?>
			<input type="hidden" name="akcija" value="brisanje">
			<input type="submit" value="Obriši fotografiju">
		</form>
		<?php
	}
	
	?>
	<p>Postavite skeniranu fotografiju popunjene uplatnice sa ispravnim iznosom uplaćene školarine</p>
	<form action="index.php" method="POST" enctype="multipart/form-data">
		<input type="hidden" name="sta" value="student/upis">
		<input type="hidden" name="akcija" value="slanje">
		<input type="file" name="attachment" size="50"><br>
		<input type="submit" value=" Pošalji! ">
	</form>
	
	
	<h3>Dokumenti</h3>
	<ul>
		<li><a href="?sta=student/ugovoroucenjupdf">Ugovor o učenju</a></li>
		<li><a href="?sta=izvjestaj/sv20">ŠV-20 obrazac</a></li>
		<li>Upisni list (u pripremi)</li>
		<li>Semestralni list (u pripremi)</li>
	</ul>
	<?php
}
