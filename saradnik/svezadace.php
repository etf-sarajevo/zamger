<?

// SARADNIK/SVEZADACE - download svih zadaca u jednoj grupi



function saradnik_svezadace() {

global $userid, $user_siteadmin, $tmpfolder, $conf_files_path;

// Parametri
$labgrupa = intval($_REQUEST['grupa']);
$zadaca = intval($_REQUEST['zadaca']);

// Pretvorba naših slova u nenaša slova
$trans = array("č"=>"c", "ć"=>"c", "đ"=>"d", "š"=>"s", "ž"=>"z", "Č"=>"C", "Ć"=>"C", "Đ"=>"D", "Š"=>"S", "Ž"=>"Z");


// Određujemo predmet i ag za labgrupu
$q30 = db_query("select naziv, predmet, akademska_godina from labgrupa where id=$labgrupa");
if (db_num_rows($q30)<1) {
	biguglyerror("Nemate pravo ulaska u ovu grupu!");
	zamgerlog("nepostojeca labgrupa $labgrupa",3); // 3 = greska
	zamgerlog2("nepostojeca labgrupa", $labgrupa);
	return;
}
$naziv_grupe = db_result($q30,0,0);
$predmet = db_result($q30,0,1);
$ag = db_result($q30,0,2);



// Da li korisnik ima pravo ući u grupu?
if (!$user_siteadmin) {
	$q40 = db_query("select nivo_pristupa from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
	if (db_num_rows($q40)<1) {
		biguglyerror("Nemate pravo ulaska u ovu grupu!");
		zamgerlog ("nastavnik nije na predmetu (labgrupa g$labgrupa)", 3);
		zamgerlog2("nije saradnik na predmetu", $predmet, $ag);
		return;
	}
	$privilegija = db_result($q40,0,0);

	$q50 = db_query("select o.labgrupa from ogranicenje as o, labgrupa as l where o.nastavnik=$userid and o.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag");
	if (db_num_rows($q50)>0) {
		$nasao=0;
		while ($r50 = db_fetch_row($q50)) {
			if ($r50[0] == $labgrupa) { $nasao=1; break; }
		}
		if ($nasao == 0) {
			biguglyerror("Nemate pravo ulaska u ovu grupu!");
			zamgerlog("ogranicenje na labgrupu g$labgrupa", 3); // 3 - greska
			zamgerlog2("ima ogranicenje na labgrupu", $labgrupa);
			return;
		}
	}
}

// Naziv predmeta i zadaće
$q60 = db_query("select naziv, kratki_naziv from predmet where id=$predmet");
$naziv_predmeta = db_result($q60,0,0);

$q70 = db_query("select naziv from zadaca where id=$zadaca");
if (db_num_rows($q70)<1) {
	niceerror("Nepostojeća zadaća!");
	zamgerlog("nepostojeca zadaca $zadaca", 3);
	zamgerlog2("nepostojeca zadaca", $zadaca);
	return;
}
$naziv_zadace = db_result($q70,0,0);

// Naziv za ZIP fajl...
$naziv_zip_fajla = db_result($q60,0,1)." ".$naziv_grupe." ".$naziv_zadace;
$naziv_zip_fajla = preg_replace("/\W/", "", str_replace(" ", "_", strtr($naziv_zip_fajla, $trans)));
$naziv_fajla_bez_puta = "$naziv_zip_fajla.zip";
$naziv_zip_fajla = "$conf_files_path/zadace/$naziv_zip_fajla.zip";


// Ekran za čekanje
if ($_REQUEST['potvrda']!="ok") {
	?>
	<h3><?=$naziv_predmeta?>, <?=$naziv_grupe?>, <?=$naziv_zadace?></h3>
	<h2>Download svih zadaća u grupi</h2>
	<? nicemessage ("Molimo sačekajte dok se kreira arhiva."); 
	?>
	<script language="JavaScript">document.location.replace('index.php?sta=saradnik/svezadace&grupa=<?=$labgrupa?>&zadaca=<?=$zadaca?>&potvrda=ok');</script>
	<?

	return;
}


// Pravim folder koji će biti zipovan
$lokacijazadaca="$conf_files_path/zadace/$predmet-$ag/";
$tmpfolder = "$conf_files_path/zadace/tmp/";
mkdir ($tmpfolder);

// Podaci o zadaći
$q100 = db_query("select zadataka, programskijezik, attachment from zadaca where id=$zadaca");
$brzadataka = db_result($q100,0,0);
$attach = db_result($q100,0,2);
if ($attach==0) {
	$q105 = db_query("select ekstenzija from programskijezik where id=".db_result($q100,0,1));
	$ekst = db_result($q105,0,0);
}

// Spisak studenata u grupi
$q110 = db_query("select o.id, o.ime, o.prezime, o.brindexa from osoba as o, student_labgrupa as sl where sl.labgrupa=$labgrupa and sl.student=o.id");
while ($r110 = db_fetch_row($q110)) {
	// Kreiram string pogodan da bude ime foldera
	$ime = preg_replace("/\W/", "", str_replace(" ", "_", strtr($r110[1], $trans)));
	$prezime = preg_replace("/\W/", "", str_replace(" ", "_", strtr($r110[2], $trans)));
	$brindexa = preg_replace("/\W/", "", str_replace("/", "_", $r110[3]));
	$studenti[$r110[0]] = $prezime."_".$ime."_".$brindexa;
}

// Petlja koja kopira fajlove u privremeni folder
$fajlova=0;
for ($zadatak=1; $zadatak<=$brzadataka; $zadatak++) {
	$zadatakfolder = $tmpfolder;
	// Ako je $brzadataka>1 pravimo folder za svaki zadatak
	if ($brzadataka>1) {
		$zadatakfolder = $tmpfolder . "zadatak_$zadatak/";
		mkdir($zadatakfolder);
	}
	
	// Petlja za studente
	foreach ($studenti as $student_id => $student_string) {
		// Odredjujemo lokaciju fajla na serveru
		if ($attach==0) {
			$oldfile = "$lokacijazadaca$student_id/$zadaca/$zadatak$ekst";
			if (!file_exists($oldfile)) continue; // Nije poslao zadaću...
			$fajlova++;

		} else {
			$q120 = db_query("select filename from zadatak where zadaca=$zadaca and redni_broj=$zadatak and student=$student_id order by id desc limit 1");
			if (db_num_rows($q120)<1) continue; // Nije poslao zadaću...
			$oldfile = "$lokacijazadaca$student_id/$zadaca/".db_result($q120,0,0);
			if (!file_exists($oldfile)) { // Konfliktna situacija na serveru?
				//print "<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;* Nisam uspio pronaći fajl '".db_result($q120,0,0)."' na serveru (student $student_id, zadatak $zadatak). Molimo prijavite ovo administratoru.</p>\n";
				continue;
			}
			$fajlova++;

			// Želimo da $newfile ima istu ekstenziju kao $oldfile
			// Specijalni slučajevi
			if (substr($oldfile, strlen($oldfile)-7) == ".tar.gz") {
				$ekst=".tar.gz";
			} else if (substr($oldfile, strlen($oldfile)-8) == ".tar.bz2") {
				$ekst=".tar.bz2";
			} else {
				$ekst = strrchr($oldfile, ".");
			}

		}
		$newfile=$zadatakfolder.$student_string.$ekst;

		copy($oldfile, $newfile);
	}
}


//Delete folder function 
function deleteDirectory($dir) { 
    if (!file_exists($dir)) return true; 
    if (!is_dir($dir) || is_link($dir)) return unlink($dir); 
        foreach (scandir($dir) as $item) { 
            if ($item == '.' || $item == '..') continue; 
            if (!deleteDirectory($dir . "/" . $item)) { 
                chmod($dir . "/" . $item, 0777); 
                if (!deleteDirectory($dir . "/" . $item)) return false; 
            }; 
        } 
        return rmdir($dir); 
    } 




if ($fajlova==0) {
	niceerror("Nijedan student nije poslao zadaću kroz Zamger.");
	print "<p>Ova funkcionalnost služi kako bi se odjednom mogle preuzeti sve zadaće poslane kroz Zamger. No u izabranoj grupi nijedan student nije poslao zadaću kroz Zamger!</p>\n<p>Da li su zadaće poslane na neki drugi način?</p>";
	deleteDirectory($tmpfolder);
	zamgerlog("niko nije poslao zadacu (z$zadaca, pp$predmet, g$labgrupa)", 3);
	zamgerlog2("niko nije poslao zadacu", $zadaca);
	return;
}


// Zipujemo folder
exec("cd $tmpfolder; zip -r $naziv_zip_fajla *"); // ZIP čuva kompletan put ako se ne nalazi u istom direktoriju

deleteDirectory($tmpfolder);


$type = `file -bi '$naziv_zip_fajla'`;
header("Content-Type: $type");
header('Content-Disposition: attachment; filename="' . $naziv_fajla_bez_puta.'"', false);
header("Content-Length: ".(string)(filesize($naziv_zip_fajla)));

$k = readfile($naziv_zip_fajla,false);
if ($k == false) {
	print "Kreiranje arhive nije uspjelo! Kontaktirajte administratora";
	zamgerlog("kreiranje arhive zadaca nije uspjelo (z$zadaca, pp$predmet, g$labgrupa)", 3);
	zamgerlog2("kreiranje arhive zadaca nije uspjelo", $zadaca);
}

unlink($naziv_zip_fajla);

exit;



}


?>
