<?

// SARADNIK/SVEZADACE - download svih zadaca u jednoj grupi



function saradnik_svezadace() {
	// This approach can be slow for very large files
	// TODO: create downloadable URL on backend and redirect there
	// (This URL must be without authentication, because headers can't be send through URLs)
	
	global $userid, $user_siteadmin, $user_nastavnik, $conf_files_path;

	// Pretvorba naših slova u nenaša slova
	$trans = array("č"=>"c", "ć"=>"c", "đ"=>"d", "š"=>"s", "ž"=>"z", "Č"=>"C", "Ć"=>"C", "Đ"=>"D", "Š"=>"S", "Ž"=>"Z");

	// Parametri
	$zadaca = intval($_REQUEST['zadaca']);
	$labgrupa = intval($_REQUEST['grupa']);

	// Da li korisnik ima pravo ući u grupu?
	if (!$user_nastavnik && !$user_siteadmin) {
		zamgerlog("attachment: nije nastavnik (student g$labgrupa zadaca z$zadaca)", 3);
		zamgerlog2("nije nastavnik");
		niceerror("Nemate pravo pregleda ove zadaće");
		return;
		// Provjera da li je nastavnik na predmetu će biti urađena na backendu
	}

	$group = api_call("group/$labgrupa", [ "resolve" => ["CourseUnit"] ] );
	$homework = api_call("homework/$zadaca");
	
	// Naziv za ZIP fajl...
	$filename = $group['CourseUnit']['abbrev'] . " " . $group['name'] . " " . $homework['name'];
	$filename = preg_replace("/\W/", "", str_replace(" ", "_", strtr($filename, $trans)));
	$filename = "$filename.zip";

	
	// Ekran za čekanje
	if ($_REQUEST['potvrda']!="ok") {
		?>
		<h3><?=$group['CourseUnit']['name']?>, <?=$group['name']?>, <?=$homework['name']?></h3>
		<h2>Download svih zadaća u grupi</h2>
		<? nicemessage ("Molimo sačekajte dok se kreira arhiva.");
		?>
		<!--script language="JavaScript">document.location.replace('index.php?sta=saradnik/svezadace&grupa=<?=$labgrupa?>&zadaca=<?=$zadaca?>&potvrda=ok');</script-->
		<?
	
		return;
	}
	
	
	// Kreiramo privremenu datoteku u koju ćemo upisati sadržinu fajla
	$dir = "$conf_files_path/zadacetmp/$userid/";
	if (!file_exists($dir))
		mkdir ($dir,0777, true);
	
	$filepath = $dir . $filename;
	if (file_exists($filepath))
		unlink($filepath);
	
	$content = api_call("homework/$zadaca/getAll", [ "filenames" => "fullname", "group" => $labgrupa ], "GET", false, false);
	
	$type = "application/zip; charset=binary";
	header("Content-Type: $type");
	header('Content-Disposition: attachment; filename="' . $filename.'"', false);
	header("Content-Length: ".strlen($content));
	
	print $content;

	exit;
}


?>
