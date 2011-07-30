<?

// ------------------------------------------------
// rss-v5 - RSS feed za studente
// ------------------------------------------------


require_once("Config.php");

// Backend stuff
require_once(Config::$backend_path."core/DB.php");
require_once(Config::$backend_path."core/Person.php");
require_once(Config::$backend_path."core/Portfolio.php");
require_once(Config::$backend_path."core/RSS.php");
require_once(Config::$backend_path."core/Util.php");

// FIXME sve ispod mora biti opcionalno
require_once(Config::$backend_path."lms/CourseOptions.php");
require_once(Config::$backend_path."lms/homework/Homework.php");
require_once(Config::$backend_path."lms/exam/ExamResult.php");

require_once(Config::$backend_path."common/pm/Message.php");

require_once(Config::$backend_path."sis/announcement/Announcement.php");

require_once(Config::$backend_path."lms/moodle/MoodleID.php");
require_once(Config::$backend_path."lms/moodle/MoodleDB.php");
require_once(Config::$backend_path."lms/moodle/MoodleItem.php");
require_once(Config::$backend_path."lms/moodle/MoodleConfig.php");



$db = new DB;
$db->connect();

$broj_poruka = 10; // Koliko poruka će biti ispisano u RSSu
$id = DB::my_escape($_REQUEST['id']); // ID feed-a


// Pretvaramo rss id u userid
try {
	$rss = RSS::fromId($id);
} catch(Exception $e) {
	print "Nepoznat RSS ID";
	return;
}

$rss->updateTimestamp();


// Ime studenta
$osoba = Person::fromId($rss->personId);

$userid = $rss->personId;
$ime = $osoba->name; 
$prezime = $osoba->surname;


// Header

header("Content-type: application/rss+xml");

?>
<<?='?'?>xml version="1.0" encoding="utf-8"?>
<!DOCTYPE rss PUBLIC "-//Netscape Communications//DTD RSS 0.91//EN" "http://www.rssboard.org/rss-0.91.dtd">
<rss version="0.91">
<channel>
        <title>Zamger RSS</title>
        <link><?=Config::$frontend_url?></link>
        <description>Aktuelne informacije za studenta <?=$ime?> <?=$prezime?></description>
        <language>bs-ba</language>
<?


// Pomoćna funkcija generiše validan RSS kod za date podatke

function genRssItem($title, $link, $description) {

$code = "
<item>
	<title>$title</title>
	<link>$link</link>
	<description><![CDATA[$description]]></description>
</item>
";
return $code;

}



// Trpamo sve RSS items u nizove koje ćemo sortirati kasnije

$vrijeme_poruke = array();
$code_poruke = array();


// Cache rezultata provjere da li je aktivno slanje zadaća
$aktivno_slanje = array();


// Rokovi za slanje zadaća

try {
	$zadace = Homework::getLatestForStudent($userid, $broj_poruka);
} catch (Exception $e) {
	print "e: ".$e->getMessage()."<br>\n"; break;
}

foreach ($zadace as $z) {
	// Pošto zadaće nisu složene po predmetu, keširamo provjeru da li je aktivan modul u jednom nizu
	if ($aktivno_slanje[$z->courseUnitId] == 0) { // 0 = nepoznato stanje
		if (CourseOptions::isModuleActiveForCourse("student/zadaca", $z->courseUnitId, $z->academicYearId))
			$aktivno_slanje[$z->courseUnitId] = 2;
		else
			$aktivno_slanje[$z->courseUnitId] = 1;
	}
	if ($aktivno_slanje[$z->courseUnitId] == 1) continue;

	$code_poruke["z".$z->id] = genRssItem(
		$title = "Objavljena zadaća ".$z->name.", predmet ".$z->courseUnit->name,
		$link = Config::$frontend_url."/index.php?sta=student/zadaca&amp;zadaca=". $z->id. "&amp;predmet=". $z->courseUnitId. "&amp;ag=". $z->academicYearId,
		$description = "Rok za slanje je ".date("d. m. Y", $z->deadline)
	);
	$vrijeme_poruke["z".$z->id] = $z->publishedDateTime;
}


// Pregledane zadaće

try {
	$zadace = Homework::getReviewedForStudent($userid, $broj_poruka);
} catch (Exception $e) {
	print "e: ".$e->getMessage()."<br>\n"; break;
}

foreach ($zadace as $z) {
	// Pošto zadaće nisu složene po predmetu, keširamo provjeru da li je aktivan modul u jednom nizu
	if ($aktivno_slanje[$z->courseUnitId] == 0) { // 0 = nepoznato stanje
		if (CourseOptions::isModuleActiveForCourse("student/zadaca", $z->courseUnitId, $z->academicYearId))
			$aktivno_slanje[$z->courseUnitId] = 2;
		else
			$aktivno_slanje[$z->courseUnitId] = 1;
	}
	if ($aktivno_slanje[$z->courseUnitId] == 1) continue;

	$code_poruke["zp".$z->id] = genRssItem(
		$title = "Pregledana zadaća ".$z->name.", predmet ".$z->courseUnit->name,
		$link = Config::$frontend_url."/index.php?sta=student/zadaca&amp;zadaca=".$z->id."&amp;predmet=".$z->courseUnitId."&amp;ag=".$z->academicYearId,
		$description = "Rok za slanje je ".date("d. m. Y", $z->deadline)
	);
	$vrijeme_poruke["zp".$z->id] = $z->publishedDateTime;
}



// Objavljeni rezultati ispita

try {
	$rezultati = ExamResult::getLatestForStudent($userid, $broj_poruka);
} catch (Exception $e) {
	print "e: ".$e->getMessage()."<br>\n";
}

foreach ($rezultati as $r) {
	if ($r->result >= $r->exam->scoringElement->pass) $cestitka=" Čestitamo!"; else $cestitka="";

	$code_poruke["i".$r->exam->id] = genRssItem(
		$title = "Objavljeni rezultati ispita ".$r->exam->scoringElement->guiName." (".date("d. m. Y",$r->exam->date).") - predmet ".$r->exam->courseUnit->name,
		$link = Config::$frontend_url."/index.php?sta=student/predmet&amp;predmet=".$r->exam->courseUnitId."&amp;ag=".$r->exam->academicYearId,
		$description = ""
	);
	$vrijeme_poruke["i".$r->exam->id] = $r->exam->publishedDateTime;
}

// Dodati informaciju o terminima za prijavljivanje ispita
// Potrebno kreirati modul za opšte prijavljivanje


// Konačne ocjene

try {
	$ocjene = Portfolio::getLatestGradesForStudent($userid, $broj_poruka);
} catch (Exception $e) {
	print "e: ".$e->getMessage()."<br>\n";
}

foreach ($ocjene as $o) {
	$code_poruke["k".$o->courseUnitId] = genRssItem(
		$title = "Čestitamo! Dobili ste ".$o->grade." -- predmet ".$o->courseUnit->name,
		$link = Config::$frontend_url."/index.php?sta=student/predmet&amp;predmet=".$o->courseUnitId."&amp;ag=".$o->academicYearId,
		$description = ""
	);
	$vrijeme_poruke["k".$o->courseUnitId] = $o->gradeDate;
}


// Dodati kvizove?


// PORUKE (izvadak iz inboxa)


try {
	$poruke = Message::getLatestForPerson($userid, $broj_poruka, true); // FIXME možda je nastavnik?
} catch (Exception $e) {
	print "e: ".$e->getMessage()."<br>\n";
}

foreach ($poruke as $p) {
	// Fino vrijeme
	$vr = $p->time;
	$vrijeme = date("d.m. H:i",$vr); // Format vremena se ne smije mijenjati jer to zbunjuje neke readere npr. Google Reader

	// Skraćujemo naslov ako treba
	$naslov = $p->subject;
	if (strlen($naslov)>30) $naslov = Util::substr_utf8($naslov,0,28)."...";
	if (!preg_match("/\S/",$naslov)) $naslov = "[Bez naslova]";
	
	// Ukidam nove redove u potpunosti
	$naslov = str_replace("\n", " ", $naslov);
	// RSS ne podržava &quot; entitet!?
	$naslov = str_replace("&quot;", '"', $naslov);
	
	$posiljalac = Person::fromId($p->fromId);
	$posiljalacTxt = $posiljalac->name." ".$posiljalac->surname;

	$code_poruke["pm".$p>id] = genRssItem(
		$title = "Poruka: $naslov ($vrijeme)",
		$link = Config::$frontend_url."/index.php?sta=common%2Finbox&amp;poruka=".$p->id,
		$description = "Poslao: $posiljalacTxt"
	);
	$vrijeme_poruke["pm".$p>id]=$p->time;
}


// Obavijesti

try {
	$obavijesti = Announcement::getLatestForPerson($userid, $broj_poruka, true); // FIXME možda je nastavnik?
} catch (Exception $e) {
	print "e: ".$e->getMessage()."<br>\n";
}

foreach ($obavijesti as $o) {
	// Fino vrijeme
	$vr = $o->time;
	$vrijeme="";
	if (date("d.m.Y",$vr)==date("d.m.Y")) $vrijeme = "<i>danas</i>";
	else if (date("d.m.Y",$vr+3600*24)==date("d.m.Y")) $vrijeme = "<i>juče</i>";
	else {
		$vrijeme .= date("d.m.",$vr);
		if (date("Y", $vr) != date("Y")) $vrijeme .= date("Y.", $vr);
	}
	$vrijeme .= date(" H:i",$vr);

	// Koristimo prvih 30 znakova teksta kao naslov
	$naslov = $o->shortText;
	if ($naslov == "") $naslov = $o->longerText;
	if (strlen($naslov)>30) $naslov = Util::substr_utf8($naslov,0,28)."...";
	if (!preg_match("/\S/",$naslov)) $naslov = "[Bez naslova]";
	
	// Ukidam nove redove u potpunosti
	$naslov = str_replace("\n", " ", $naslov);
	// RSS ne podržava &quot; entitet!?
	$naslov = str_replace("&quot;", '"', $naslov);

	$code_poruke["ob".$o->id] = genRssItem(
		$title = "Obavijest: $naslov ($vrijeme)",
		$link = Config::$frontend_url."/index.php?sta=common%2Finbox&amp;poruka=".$p->id,
		$description = $o->to
	);
	$vrijeme_poruke["ob".$o->id]=$o->time;
}


// Moodle obavijesti
if (MoodleConfig::$moodle) {
$moodledb = new MoodleDB;
$moodledb->connect();

$predmeti = Portfolio::getCurrentForStudent($userid);
foreach ($predmeti as $p) {
	try {
		$course_id = MoodleID::getMoodleID($p->courseUnitId, $p->academicYearId);
	} catch(Exception $e) {
		// Predmet nema moodle id
		continue;
	}

	// TODO: ovo se sigurno može svesti na jednu petlju nekako
	$moodlestuff = MoodleItem::getLatestForCourse($course_id);
	foreach ($moodlestuff as $mdl) {
		$vrijeme = date("d.m. H:i", $mdl->timeModified);

		// Skraćeni naslov
		$naslov = $mdl->text;
		if (strlen($naslov)>30) 
			$naslov = Util::substr_utf8($naslov,0,28)."...";

		if ($mdl->type == "label") {
			$code_poruke["mo".$mdl->id] = genRssItem(
				$title = "Obavijest (".$p->courseUnit->shortName."): $naslov ($vrijeme)",
				$link = MoodleConfig::$url."course/view.php?id=$course_id",
				$description = "Detaljnije na Moodle stranici predmeta ".$p->courseUnit->name
			);
			$vrijeme_poruke["mo".$mdl->id] = $mdl->timeModified;
		}
		if ($mdl->type == "resource") {
			$code_poruke["mr".$mdl->id] = genRssItem(
				$title = "Resurs (".$p->courseUnit->shortName."): $naslov ($vrijeme)",
				$link = $mdl->url,
				$description = "Detaljnije na Moodle stranici predmeta ".$p->courseUnit->name
			);
			$vrijeme_poruke["mr".$mdl->id] = $mdl->timeModified;
		}
	}
}

$moodledb->disconnect();


} // MoodleConfig::$moodle


// Sortiramo po vremenu

arsort($vrijeme_poruke);
$count=0;


foreach ($vrijeme_poruke as $id=>$vrijeme) {
	if ($count==0) {
		// Polje pubDate u zaglavlju sadrži vrijeme zadnje izmjene tj. najnovije poruke

		//print "        <pubDate>".date(DATE_RSS, $vrijeme)."</pubDate>\n";
		// U verziji PHP 5.1.6 (i vjerovatno starijim) DATE_RSS je nekorektno 
		// izjednačeno sa "D, j M Y H:i:s T" -- obratiti pažnju na T
		print "        <pubDate>".date("D, j M Y H:i:s O", $vrijeme)."</pubDate>\n";
	}

	print $code_poruke[$id];
	$count++;
	if ($count == $broj_poruka) break;
}




?>
</channel>
</rss>
