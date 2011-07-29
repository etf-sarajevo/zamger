<?

// STUDENT/PREDMET - statusna stranica predmeta


function student_predmet() {

global $userid, $__lv_connection;


require_once("Config.php");

// Backend stuff
require_once(Config::$backend_path."core/DB.php");
require_once(Config::$backend_path."core/CourseUnit.php");
require_once(Config::$backend_path."core/AcademicYear.php");
require_once(Config::$backend_path."core/CourseUnitYear.php");
require_once(Config::$backend_path."core/Portfolio.php");
require_once(Config::$backend_path."core/Scoring.php");

// FIXME sve ispod mora biti opcionalno
require_once(Config::$backend_path."lms/CourseOptions.php");

require_once(Config::$backend_path."lms/attendance/Group.php");
require_once(Config::$backend_path."lms/attendance/ZClass.php");
require_once(Config::$backend_path."lms/attendance/Attendance.php");

require_once(Config::$backend_path."lms/homework/Homework.php");
require_once(Config::$backend_path."lms/homework/Assignment.php");

require_once(Config::$backend_path."lms/exam/Exam.php");
require_once(Config::$backend_path."lms/exam/ExamResult.php");

require_once(Config::$backend_path."lms/moodle/MoodleID.php");
require_once(Config::$backend_path."lms/moodle/MoodleDB.php");
require_once(Config::$backend_path."lms/moodle/MoodleItem.php");
require_once(Config::$backend_path."lms/moodle/MoodleConfig.php");

require_once(Config::$backend_path."hrm/ensemble/Engagement.php");

DB::$the_connection = $__lv_connection; // FIXME ovo je zbog toga što se nismo konektovali kroz DB klasu nego kroz libvedran
// a potrebno je za MoodleDB klasu


$predmet = intval($_REQUEST['predmet']);
$ag = intval($_REQUEST['ag']); // akademska godina


// Podaci za zaglavlje
$cu = CourseUnit::fromId($predmet);
$ay = AcademicYear::fromId($ag);
$pf = Portfolio::fromCourseUnit($userid, $predmet, $ag);


?>
<br />
<p style="font-size: small;">Predmet: <b><?=$cu->name?> (<?=$ay->name?>)</b><br />
<?

// Određivanje labgrupe
$grupe = Group::fromStudentAndCourse($userid, $predmet, $ag);

foreach ($grupe as $g) {
	if ($g->virtual) continue;
	?>Grupa: <b><?=$g->name?></b><br /><?
}

print "</p><br />\n";



// Nastavni ansambl
$nastavnici = Engagement::getTeachersOnCourse($predmet, $ag);
foreach ($nastavnici as $n)
	print "<b>".ucfirst($n->status)."</b>: ".$n->person->titlesPre." ".$n->person->name." ".$n->person->surname." ".$n->person->titlesPost."<br />\n";
print "<br />\n";


// PROGRESS BAR
// FIXME zamijeniti skalom u skladu sa pravilima fakulteta o bodovanju

$bodova = $pf->getTotalScore();
$mogucih = $pf->getMaxScore();

// boja označava napredak studenta
if ($mogucih==0) $procent=0;
else $procent = intval(($bodova/$mogucih)*100);
if ($procent>=75) 
	$color="#00FF00";
else if ($procent>=50)
	$color="#FFFF00";
else
	$color="#FF0000";


$tabela1=$procent*2;
$tabela2=200-$tabela1;

$ispis1 = "<img src=\"images/fnord.gif\" width=\"$tabela1\" height=\"10\">";
$ispis2 = "<img src=\"images/fnord.gif\" width=\"$tabela2\" height=\"1\"><br/> $bodova bodova";

if ($tabela1>$tabela2) { 
	$ispis1="<img src=\"images/fnord.gif\" width=\"$tabela1\" height=\"1\"><br/> $bodova bodova";
	$ispis2="<img src=\"images/fnord.gif\" width=\"$tabela2\" height=\"10\">";
}

?>


<!-- progress bar -->

<center><table border="0"><tr><td align="left">
<p>Osvojili ste....<br/>
<table style="border:1px;border-style:solid" width="206" cellpadding="0" cellspacing="2"><tr>
<td width="<?=$tabela1?>" bgcolor="<?=$color?>"><?=$ispis1?></td>
<td width="<?=$tabela2?>" bgcolor="#FFFFFF"><?=$ispis2?></td></tr></table>

<table width="208" border="0" cellspacing="0" cellpadding="0"><tr>
<td width="68">0</td>
<td align="center" width="68">50</td>
<td align="right" width="69">100</td></tr></table>
što je <?=$procent?>% od trenutno mogućih <?=round($mogucih,2) /* Rješavamo nepreciznost floata */ ?> bodova.</p>
</td></tr></table></center>


<!-- end progress bar -->
<?




// PRIKAZ NOVOSTI SA MOODLE-a (by fzilic)

function moodle_novosti($predmet, $ag) {
	// Parametri potrebni za Moodle integraciju
	global $userid;
	
	if (!MoodleConfig::$moodle) return;

	// Potrebno je pronaci u tabeli moodle_predmet_id koji je id kursa koristen na Moodle stranici za odredjeni predmet sa Zamger-a..tacno jedan id kursa iz moodle baze odgovara jednom predmetu u zamger bazi
	try {
		$course_id = MoodleID::getMoodleID($predmet,$ag);
	} catch(Exception $e) {
		// Predmet nema moodle id
		return;
	}

	$vrijeme_posljednjeg_logina = time(); // FIXME

	$moodledb = new MoodleDB;
	$moodledb->connect();

	// TODO: ovo se sigurno može svesti na jednu petlju nekako
	$moodlestuff = MoodleItem::getLatestForCourse($course_id);
	foreach ($moodlestuff as $mdl) {
		if ($mdl->type == "label") {
			$code_poruke["o".$mdl->id] = $mdl->text;
			$vrijeme_poruke_obavijest["o".$mdl->id] = $mdl->timeModified;
		}
		if ($mdl->type == "resource") {
			$code_poruke["r".$mdl->id] = '<a href="'.$mdl->url.'">'.$mdl->text.'</a>';
			$vrijeme_poruke_resurs["r".$mdl->id] = $mdl->timeModified;
		}
	}
	
	$moodledb->disconnect();

	if (count($vrijeme_poruke_obavijest)>0) {
		?><h3>Obavještenja</h3>
		<ul><?
		arsort($vrijeme_poruke_obavijest);
		$count=0;
		foreach ($vrijeme_poruke_obavijest as $id=>$vrijeme) {
			$code = $code_poruke[$id];
			if ($vrijeme>$vrijeme_posljednjeg_logina) $code = "<b>$code</b>";
			print "<li>(".date("d.m. H:i:s", $vrijeme).") $code</li>\n";
			$count++;
			if ($count==5) break; // prikazujemo 5 poruka
		}
		print "<li><a href=\"$conf_moodle_url"."course/view.php?id=$course_id\">Opširnije...</a></li></ul>\n";
	}

	if (count($vrijeme_poruke_resurs)>0) {
		?><h3>Resursi</h3>
		<ul><?
		arsort($vrijeme_poruke_resurs);
		$count=0;
		foreach ($vrijeme_poruke_resurs as $id=>$vrijeme) {
			$code = $code_poruke[$id];
			if ($vrijeme>$vrijeme_posljednjeg_logina) $code = "<b>$code</b>";
			print "<li>(".date("d.m. H:i:s", $vrijeme).") $code</li>\n";
			$count++;
			if ($count==5) break; // prikazujemo 5 poruka
		}
		print "</ul>\n<br>\n";
	}

} // function moodle_novosti()


moodle_novosti($predmet, $ag);



//  PRISUSTVO NA VJEŽBAMA


function prisustvo_ispis($idgrupe, $imegrupe, $komponenta, $imekomponente) {
	global $userid;

	if (!preg_match("/\w/",$imegrupe)) $imegrupe = "[Bez naziva]";

	$casovi = ZClass::fromGroupAndScoringElement($idgrupe, $komponenta);
	if (count($casovi) == 0) return; // Ne ispisuj grupe u kojima nema registrovanih časova

	$odsustva=0;
	$datumi = $vremena = $statusi = "";
	$a = new Attendance;
	$a->studentId = $userid;

	foreach ($casovi as $c) {
		$datumi .= "<td>".date("d.m",$c->datetime)."</td>\n";
		$vremena .= "<td>".date("h",$c->datetime)."<sup>".date("i",$c->datetime)."</sup></td>\n";
		$a->classId = $c->id;
		$p = $a->getPresence();
		if ($p == -1)
			$statusi .= "<td bgcolor=\"#FFFFCC\" align=\"center\">/</td>\n";
		else if ($p == 1)
			$statusi .= "<td bgcolor=\"#CCFFCC\" align=\"center\">DA</td>\n";
		else {
			$statusi .= "<td bgcolor=\"#FFCCCC\" align=\"center\">NE</td>\n";
			$odsustva++;
		}
	}

	
	?>

	<b><?=$imekomponente?> (<?=$imegrupe?>):</b><br/>
	<table cellspacing="0" cellpadding="2" border="0" id="prisustvo">
	<tr>
		<th>Datum</th>
	<?=$datumi?>
	</tr>
	<tr>
		<th>Vrijeme</th>
	<?=$vremena?>
	</tr>
	<tr>
		<th>Prisutan</th>
	<?=$statusi?>
	</tr>
	</table>
	</p>
	
	<?
	return $odsustva;
}


// FIXME dva upita umjesto jednog?

$cuy = CourseUnitYear::fromCourseAndYear($predmet, $ag);
$komponente = $cuy->scoring->getScoringElements( 3 /*prisustvo*/ );

foreach ($komponente as $k) {
	$odsustva = 0;
	foreach ($grupe as $g)
		$odsustvo += prisustvo_ispis($g->id, $g->name, $k->id, $k->guiName);

	?><p>Ukupno imate <b><?=$odsustvo?></b> izostanaka odnosno <b><?=$pf->getScore($k->id)?></b> bodova.</p>
	<?
}



//  ZADAĆE


// Statusne ikone:
$stat_icon = array("zad_bug", "zad_preg", "zad_copy", "zad_bug", "zad_preg", "zad_ok");
$stat_tekst = array("Bug u programu", "Pregled u toku", "Zadaća prepisana", "Bug u programu", "Pregled u toku", "Zadaća OK");


?>


<!-- zadace -->

<b>Zadaće:</b><br/>
<table cellspacing="0" cellpadding="2" border="0" id="zadace">
	<thead>
		<tr>
<?

// Spisak zadaća
$zadace = Homework::fromCourse($predmet, $ag);

// Prikaz za predmete kod kojih nije aktivno slanje zadaća
if (!CourseOptions::isModuleActiveForCourse("student/zadaca", $predmet, $ag)) {

	// Nazivi zadaća
	foreach ($zadace as $z) {
		$naziv = $z->name;
		if (!preg_match("/\w/",$naziv)) $naziv = "[Bez naziva]";
		?><td><?=$naziv?></td><?
	}

	// U pravilu ovdje ima samo jedan zadatak, pa ćemo sumirati bodove
?>
		<td><b>Ukupno bodova</b></td>
		</tr>
	</thead>
<tbody>
<?
	$uk_bodova=0;
	foreach ($zadace as $z) {
		$bodova=0;
		$status=-1;
		for ($zadatak=1; $zadatak<=$z->nrAssignments; $zadatak++) {
			try {
				$a = Assignment::fromStudentHomeworkNumber($userid, $z->id, $zadatak);
				$status = $a->status; // uzimamo status zadnjeg zadatka
				$bodova += $a->score;
			} catch(Exception $e) {
				// student nema ništa uneseno za ovaj zadatak, ne radimo ništa 
			}
		}

		if ($status==-1) { // nema ništa uneseno niti za jedan zadatak u ovoj zadaći
			?>
			<td>&nbsp;</td>
			<?
		} else {
			?>
			<td><img src="images/16x16/<?=$stat_icon[$status]?>.png" width="16" height="16" border="0" align="center" title="<?=$stat_tekst[$status]?>" alt="<?=$stat_tekst[$status]?>"> <?=$bodova?></td>
			<?
		}
		$uk_bodova += $bodova;
	}
	?>
	<td><?=$uk_bodova?></td></tr>
</tbody>
</table>

&nbsp;<br/>

	<?


// Prikaz za predmete kod kojih jeste aktivno slanje zadaća
} else { // if (!StudentModule::isModuleActiveForCourse(

?>
	<td>&nbsp;</td>
<?

// Zaglavlje tabele - potreban nam je max. broj zadataka u zadaći

$max_broj_zadataka = 0;
$ima_postavka = false;
foreach ($zadace as $z) {
	if ($z->nrAssignments > $max_broj_zadataka) $max_broj_zadataka = $z->nrAssignments;
	if (preg_match("/\w/", $z->text)) $ima_postavka=true;
}

for ($i=1; $i<=$max_broj_zadataka; $i++) {
	?><td>Zadatak <?=$i?>.</td><?
}

?>
		<td><b>Ukupno bodova</b></td>
		<? if ($ima_postavka) { ?><td><b>Postavka zadaća</b></td><? } ?>
		<td><b>PDF</b></td>
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

foreach ($zadace as $z) {
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

		try {
			$a = Assignment::fromStudentHomeworkNumber($userid, $z->id, $zadatak);

			$slao_zadacu = true;
			$bodova_zadaca += $a->score;

			$ikona_komentar = "";
			if (strlen($a->comment) > 2)
				$ikona_komentar = "<img src=\"images/16x16/komentar.png\"  width=\"15\" height=\"14\" border=\"0\" title=\"Ima komentar\" alt=\"Ima komentar\" align=\"center\">";

			?>
			<td>
				<a href="?sta=student/zadaca&predmet=<?=$predmet?>&ag=<?=$ag?>&zadaca=<?=$z->id?>&zadatak=<?=$zadatak?>"><img src="images/16x16/<?=$stat_icon[$a->status]?>.png" width="16" height="16" border="0" align="center" title="<?=$stat_tekst[$a->status]?>" alt="<?=$stat_tekst[$a->status]?>"> <?=$a->score?> <?=$ikona_komentar?></a>
			</td>
			<?

		} catch(Exception $e) {
			// student nije slao ovaj zadatak
			?>
			<td>
				<a href="?sta=student/zadaca&predmet=<?=$predmet?>&ag=<?=$ag?>&zadaca=<?=$z->id?>&zadatak=<?=$zadatak?>"><img src="images/16x16/zad_novi.png" width="16" height="16" border="0" align="center" title="Novi zadatak" alt="Novi zadatak"></a>
			</td>
			<?
		}
	} // for ($zadatak=1...
	
	// Ukupan broj bodova za zadaću
	?>
	<td><?=$bodova_zadaca?></td><td>
	<?
	
	// Link za download postavke zadaće
	if ($ima_postavka) {
		if ($z->text != "") {
			?><a href="?sta=common/attachment&zadaca=<?=$z->id?>&tip=postavka"><img src="images/16x16/preuzmi.png" width="16" height="16" border="0"></a><?
		} else { print "&nbsp;"; }
		print "</td><td>\n";
	}

	// Download zadaće u PDF formatu - sada je moguć i za attachmente
	if ($slao_zadacu) {
		?><a href="?sta=student/zadacapdf&zadaca=<?=$z->id?>" target="_new"><img src="images/16x16/pdf.png" width="16" height="16" border="0"></a><?
	} else { print "&nbsp;"; }
	?>
	</td></tr>
	<?
	
	$bodova_sve_zadace += $bodova_zadaca;
}


// Ukupno bodova za studenta
 
$bodova += $bodova_sve_zadace;

?>
	<tr><td colspan="<?=$max_broj_zadataka+1?>" align="right">UKUPNO: </td>
	<td><?=$bodova_sve_zadace?></td><td>&nbsp;</td>
	<? if ($ima_postavka) { ?><td>&nbsp;</td><? } ?></tr>
</tbody>
</table>

<p>Za ponovno slanje zadatka, kliknite na sličicu u tabeli iznad. <a href="#" onclick="javascript:window.open('legenda-zadace.html','blah6','width=320,height=130');">Legenda simbola</a></p>
<br/>

<!-- end zadace -->

<?

} // else




// FIKSNE KOMPONENTE


$komponente = $cuy->scoring->getScoringElements(5);
if (count($komponente) > 0) {
	?>
	
	<!-- fiksne komponente -->
	
	<b>Bodovi po ostalim osnovama:</b><br/>
	
	<?

	foreach ($komponente as $k) {
		?><p><?=$k->guiName?>: <b><?=$pf->getScore($k->id)?> bodova</b></p><?
	}
	?><p>&nbsp;</p><?
}



//  ISPITI

?>

<!-- ispiti -->

<b>Ispiti:</b><br/>

<?

$ispiti = Exam::fromCourse($predmet, $ag);
if (count($ispiti) == 0) {
	print "<p>Nije bilo parcijalnih ispita.</p>";
}

foreach($ispiti as $i) {
	$er = ExamResult::fromStudentAndExam($userid, $i->id);
	if ($er->exists) {
		?><p><?=$i->scoringElement->guiName?> (<?=date("d. m. Y",$i->date)?>): <b><?=$er->result?> bodova</b></p><?
	}
}



// KONAČNA OCJENA
$ocjena = $pf->getGrade();
if ($ocjena>0) {
	?>
	<center>
		<table width="100px" style="border-width: 3px; border-style: solid; border-color: silver">
			<tr><td align="center">
				KONAČNA OCJENA<br/>
				<font size="6"><b><?=$ocjena?></b></font>
			</td></tr>
		</table>
	</center>
	<?
}


}

?>
