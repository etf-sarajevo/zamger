<?

// STUDENT/INTRO - uvodna stranica za studente


function student_intro() {

global $userid, $registry;


require_once("Config.php"); // pomjeriti u index.php?

// Backend stvari koje se koriste

require_once(Config::$backend_path."core/Portfolio.php");
require_once(Config::$backend_path."core/RSS.php");

// FIXME sve ispod mora biti opcionalno
require_once(Config::$backend_path."lms/homework/Homework.php");
require_once(Config::$backend_path."lms/quiz/Quiz.php");
require_once(Config::$backend_path."lms/exam/ExamResult.php");

require_once(Config::$backend_path."sis/announcement/Announcement.php");

require_once(Config::$backend_path."common/pm/Message.php");



// Dobrodošlica

$q1 = myquery("select ime, spol from osoba where id=$userid");
$ime = mysql_result($q1,0,0);
$spol = mysql_result($q1,0,1);
if ($spol == 'Z' || ($spol == '' && spol($ime)=="Z"))
	print "<h1>Dobro došla, ".genitiv($ime,"Z")."</h1>";
else
	print "<h1>Dobro došao, ".genitiv($ime,"M")."</h1>";


// Sakrij module ako ih nema u registry-ju
$modul_raspored=$modul_anketa=0;
foreach ($registry as $r) {
	if ($r[0]=="common/raspored1") $modul_raspored=1;
	if ($r[0]=="student/anketa") $modul_anketa=1;
}



// Prikazujem raspored
if ($modul_raspored==1) {
	require "common/raspored1.php";
	//common_raspored1("student");
}



// AKTUELNO

// TODO: dodati prijave ispita i druge module...

?>

<table border="0" width="100%"><tr>
	<td width="30%" valign="top" style="padding: 10px; padding-right:30px;">
		<h2><img src="images/32x32/aktuelno.png" align="absmiddle"> <font color="#666699">AKTUELNO</font></h2>
<?

$vrijeme_poruke = array();
$code_poruke = array();


$broj_poruka = 5; // u rubrici aktuelno



// Rokovi za slanje zadaća

try {
	$zadace = Homework::getLatestForStudent($userid, $broj_poruka);
} catch (Exception $e) {
	print "e: ".$e->getMessage()."<br>\n";
}

foreach ($zadace as $z) {
	$code_poruke["z".$z->id] = "<b>".$z->courseUnit->name.":</b> Rok za slanje <a href=\"?sta=student/zadaca&zadaca=".$z->id."&predmet=".$z->courseUnitId."&ag=".$z->academicYearId."\">zadaće ".$z->name."</a> je ".date("d. m. Y. u H:i",$z->deadline).".<br/><br/>\n";
	$vrijeme_poruke["z".$z->id] = $z->publishedDateTime;
}



// Objavljeni rezultati ispita

try {
	$rezultati = ExamResult::getLatestForStudent($userid, $broj_poruka);
} catch (Exception $e) {
	print "e: ".$e->getMessage()."<br>\n";
}

foreach ($rezultati as $r) {
	if ($r->result >= $r->exam->scoringElement->pass) $cestitka=" Čestitamo!"; else $cestitka="";
	$code_poruke["i".$r->exam->id] = "<b>".$r->exam->courseUnit->name.":</b> Objavljeni rezultati ispita: <a href=\"?sta=student/predmet&predmet=".$r->exam->courseUnitId."&ag=".$r->exam->academicYearId."\">".$r->exam->scoringElement->guiName." (".date("d. m. Y",$r->exam->date).")</a>. Dobili ste ".$r->result." bodova.$cestitka<br /><br />\n";
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
	$code_poruke["k".$o->courseUnitId] = "<b>".$o->courseUnit->name.":</b> Čestitamo! <a href=\"?sta=student/predmet&predmet=".$o->courseUnitId."&ag=".$o->academicYearId."\">Dobili ste ".$o->grade."</a><br /><br />\n";
	$vrijeme_poruke["k".$o->courseUnitId] = $o->gradeDate;
}



// Novi kvizovi

try {
	$kvizovi = Quiz::getLatestForStudent($userid, $broj_poruka);
} catch (Exception $e) {
	print "e: ".$e->getMessage()."<br>\n";
}

foreach ($kvizovi as $k) {
	$code_poruke["kv".$k->id] = "<b>".$k->courseUnit->name.":</b> Otvoren je kviz <a href=\"?sta=student/kviz&predmet=".$k->courseUnitId."&ag=".$k->academicYearId."\">".$k->name."</a><br/><br/>\n";
	$vrijeme_poruke["kv".$k->id] = $k->timeBegin;
}


// Sortiramo po vremenu
arsort($vrijeme_poruke);
$count=0;
foreach ($vrijeme_poruke as $id=>$vrijeme) {
	print $code_poruke[$id];
	$count++;
	if ($count==$broj_poruka) break; // prikazujemo samo $broj_poruka
}
if ($count==0) {
	print "Nema aktuelnih informacija.";
}

print $vijesti;





// OBAVJEŠTENJA

?>
</td>

<td width="30%" valign="top" style="padding: 10px; padding-right:30px;" bgcolor="#f2f2f2">
				<h2><img src="images/32x32/info.png" align="absmiddle"> <font color="#666699">OBAVJEŠTENJA</font></h2>
<?

$broj_obavjestenja = 5;


try {
	$obavijesti = Announcement::getLatestForPerson($userid, $broj_obavjestenja, true);
} catch (Exception $e) {
	print "e: ".$e->getMessage()."<br>\n";
}

foreach ($obavijesti as $o) {
	// Ako je tekst obavještenja prevelik, skraćujemo
	$tekst = $o->shortText;
	if ($tekst == "") $tekst = $o->longerText;
	$skracen=false;
	if (strlen($tekst)>200) {
		$pos = strpos($tekst," ",200);
		if ($pos > 220) $pos = 220;
		if ($pos > 0) { // ako je 0 znači da nema razmaka poslije 200. znaka
			$tekst = substr($tekst,0,$pos)."...";
			$skracen = true;
		}
	}
	
	// Treba li dodati link na dalje?
	if ( $skracen || ($o->shortText != "" && $o->longerText != "") )
		$tekst .= " (<a href=\"?sta=common/inbox&poruka=".$o->id."\">Dalje...</a>)"

	?>
	<b><?=$o->to?></b> (<?=date("d.m",$o->time)?>)<br/>
	<?=$tekst?><br/><br/>
	<?
}

if (count($obavijesti) == 0)
	print "Nema novih obavještenja.";



// PORUKE (izvadak iz inboxa)

?></td>

<td width="30%" valign="top" style="padding: 10px;">
<h2><img src="images/32x32/poruke.png" align="absmiddle"> <font color="#666699">PORUKE</font></h2><?

$broj_poruka = 5;

try {
	$poruke = Message::getLatestForPerson($userid, $broj_poruka, true);
} catch (Exception $e) {
	print "e: ".$e->getMessage()."<br>\n";
}

foreach ($poruke as $p) {
	// Fino vrijeme
	$vr = $p->time;
	$vrijeme="";
	if (date("d.m.Y",$vr)==date("d.m.Y")) $vrijeme = "<i>danas</i>";
	else if (date("d.m.Y",$vr+3600*24)==date("d.m.Y")) $vrijeme = "<i>juče</i>";
	else {
		$vrijeme .= date("d.m.",$vr);
		if (date("Y", $vr) != date("Y")) $vrijeme .= date("Y.", $vr);
	}
	$vrijeme .= date(" H:i",$vr);

	// Skraćujemo naslov ako treba
	$naslov = $p->subject;
	if (strlen($naslov)>30) $naslov = substr($naslov,0,28)."...";
	if (!preg_match("/\S/",$naslov)) $naslov = "[Bez naslova]";

	print "<li><a href=\"?sta=common/inbox&poruka=".$p->id."\">$naslov</a><br/>($vrijeme)</li>\n";
}

if (count($poruke) == 0)
	print "<li>Nemate nijednu poruku.</li>\n";


?>
</td>

</tr>
</table>

<br/><br/>



<?

// RSS ID

$rss = RSS::fromPersonId($userid);

?>
<p>
<a href="http://feed1.w3.org/check.cgi?url=<?=urlencode(Config::$rss_url)?>"><img src="images/valid-rss-rogers.png" alt="[Valid RSS]" title="Validate my RSS feed" /></a> <a href="<?=Config::$rss_url?>?id=<?=$rss->id?>"><big>RSS Feed - automatsko obavještenje o novostima!</big></a></p>




<!--
<table border="0" bgcolor="#DDDDDD" width="100%">
<tr><td colspan="4" bgcolor="#CCCCCC" align="center" valign="center" style="font-size: medium"><b>Sa drugih sajtova...</b></td></tr>
<tr>

<td width="25%" valign="top" style="padding: 10px; padding-right:30px;" bgcolor="#FFFFFF">
<b>ETF.UNSA.BA:</b><br/>
* yadayada<br/>
* blah<br/>
* whocares<br/>
* Excel format
</td>

<td width="25%" valign="top" style="padding: 10px; padding-right:30px;" bgcolor="#FFFFFF">
<b>ra15070@etf.unsa.ba:</b><br/>
* prepisana zadaća<br/>
* još jedna prepisana zadaća<br/>
* pa radil iko išta sam<br/>
* Excel format
</td>


<td width="25%" valign="top" style="padding: 10px; padding-right:30px;" bgcolor="#FFFFFF">
<b>ETF.BA:</b><br/>
* Steleks se ugasio<br/>
* Teo proglašen za doživotnog počasnog studenta<br/>
* šta ja znam
</td>


<td width="25%" valign="top" style="padding: 10px; padding-right:30px;" bgcolor="#FFFFFF">
&nbsp; <!--FILLER--><!--
</td>
</tr></table>
-->
			
	<?
}

?>
