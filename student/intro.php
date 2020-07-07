<?

// STUDENT/INTRO - uvodna stranica za studente



function student_intro() {

	global $userid, $registry, $person, $courseDetails;

	require_once("lib/utility.php"); // spol, vokativ
	require_once("lib/ws.php"); // spol, vokativ

	// Dobrodošlica
	if ($person['ExtendedPerson']['sex'] == 'F' || ($person['ExtendedPerson']['sex'] == '' && spol($person['name'])=="Z"))
		print "<h1>Dobro došla, ".vokativ($person['name'],"Z")."</h1>";
	else
		print "<h1>Dobro došao, ".vokativ($person['name'],"M")."</h1>";

	// Zaduženje FIXME
	$zaduzenje = db_get("SELECT zaduzenje FROM student_zaduzenje WHERE student=$userid");
	if ($zaduzenje > 0) {
		?>
		<p>Prema trenutnoj evidenciji dugujete <b style="color: red"><?=sprintf("%.2f", $zaduzenje)?> KM</b> za školarinu.<br>
			Ako ovo nije tačno, molimo da se javite studentskoj službi.</p>
		<?
	}

	// Sakrij module ako ih nema u registry-ju
	$modul_raspored=$modul_anketa=0;
	foreach ($registry as $r) {
		if (count($r) == 0) continue;
		if ($r[0]=="common/raspored1") $modul_raspored=1;
		if ($r[0]=="student/anketa") $modul_anketa=1;
	}
	
	// Prikazujem raspored
	if ($modul_raspored==1) {
		require "common/raspored1.php";
		common_raspored1("student");
	}


	// AKTUELNO
	
	?>
	
	<table border="0" width="100%"><tr>
		<td width="30%" valign="top" style="padding: 10px; padding-right:30px;">
			<h2><img src="static/images/32x32/latest.png" align="absmiddle"> <font color="#666699">AKTUELNO</font></h2>
	<?
	
	$courseNames = [];
	foreach($courseDetails as $course)
		$courseNames[$course['CourseOffering']['CourseUnit']['id']] = $course['courseName'];
	
	$vrijeme_poruke = array();
	$code_poruke = array();


	// Rokovi za slanje zadaća
	$homeworks = api_call("homework/latest/$userid", ["resolve" => ["CourseActivity"]])['results'];
	foreach($homeworks as $hw) {
		if (array_key_exists("StudentSubmit", $hw['CourseActivity']['options']) && $hw['CourseActivity']['options']["StudentSubmit"]) {
			$id = $hw['id'];
			$idPredmeta = $hw['CourseUnit']['id'];
			$nazivPredmeta = $courseNames[$idPredmeta];
			$ag = $hw['AcademicYear']['id'];
			$naziv = $hw['name'];
			$datum = date("d. m. Y. \u H:i", db_timestamp($hw['deadline']));
			
			$code_poruke["z$id"] = "<b>$nazivPredmeta:</b> Rok za slanje <a href=\"?sta=student/zadaca&zadaca=$id&predmet=$idPredmeta&ag=$ag\">zadaće $naziv</a> je $datum.<br/><br/>\n";
			$vrijeme_poruke["z$id"] = db_timestamp($hw['publishedDateTime']);
		}
	}
	
	
	// Objavljeni rezultati ispita
	$exams = api_call("exam/latest/$userid", ["resolve" => ["CourseActivity"]])['results'];
	foreach($exams as $exam) {
		$bodova = $exam['result'];
		$prolaz = $exam['Exam']['CourseActivity']['pass'];
		if ($bodova >= $prolaz && $prolaz > 0) $cestitka=" Čestitamo!"; else $cestitka="";
		$id = $exam['Exam']['id'];
		$idPredmeta = $exam['Exam']['CourseUnit']['id'];
		$nazivPredmeta = $courseNames[$idPredmeta];
		$ag = $exam['Exam']['AcademicYear']['id'];
		$nazivIspita = $exam['Exam']['CourseActivity']['name'];
		$datumIspita = date("d. m. Y", db_timestamp($exam['Exam']['date']));
		
		$code_poruke["i$id"] = "<b>$nazivPredmeta:</b> Objavljeni rezultati ispita: <a href=\"?sta=student/predmet&predmet=$idPredmeta&ag=$ag\">$nazivIspita ($datumIspita)</a>. Dobili ste $bodova bodova.$cestitka<br /><br />\n";
		$vrijeme_poruke["i$id"] = db_timestamp($exam['Exam']['publishedDateTime']);
	}
	
	
	// Termini ispita (događaji)
	$events = api_call("event/upcoming/$userid", ["resolve" => ["CourseActivity"]])['results'];
	foreach($events as $event) {
		$idPredmeta = $event['CourseUnit']['id'];
		$nazivPredmeta = $courseNames[$idPredmeta];
		$ag = $event['AcademicYear']['id'];
		$idAktivnosti = $event['CourseActivity']['id'];
		$nazivIspita = $event['CourseActivity']['name'];
		
		$code_poruke["d$idPredmeta-$idAktivnosti"] = "<b>$nazivPredmeta:</b> Objavljeni termini za $nazivIspita: <a href=\"?sta=student/prijava_ispita&predmet=$idPredmeta&ag=$ag\">Prijavite se!</a><br /><br />\n";
		$vrijeme_poruke["d$idPredmeta-$idAktivnosti"] = db_timestamp($event['dateTimePublished']);
	}
	
	
	// Konačne ocjene
	$grades = api_call("course/latestGrades/$userid")['results'];
	foreach($grades as $grade) {
		$idPredmeta = $grade['CourseOffering']['CourseUnit']['id'];
		$nazivPredmeta = $courseNames[$idPredmeta];
		$ag = $grade['CourseOffering']['AcademicYear']['id'];
		$the_ocjena = $grade['grade'];
		
		$code_poruke["k$idPredmeta"] = "<b>$nazivPredmeta:</b> Čestitamo! <a href=\"?sta=student/predmet&predmet=$idPredmeta&ag=$ag\">Dobili ste $the_ocjena</a><br /><br />\n";
		$vrijeme_poruke["k$idPredmeta"] = db_timestamp($grade['gradeDate']);
	}
	
	
	// Objavljeni kvizovi
	$quizzes = api_call("quiz/latest/$userid")['results'];
	foreach($quizzes as $quiz) {
		$id = $quiz['id'];
		$idPredmeta = $quiz['CourseUnit']['id'];
		$nazivPredmeta = $courseNames[$idPredmeta];
		$ag = $quiz['AcademicYear']['id'];
		$naziv = $quiz['name'];
		
		$code_poruke["kv$id"] = "<b>$nazivPredmeta:</b> Otvoren je kviz <a href=\"?sta=student/kviz&predmet=$idPredmeta&ag=$ag\">$naziv</a><br/><br/>\n";
		$vrijeme_poruke["kv$id"] = db_timestamp($quiz['timeBegin']);
	}
	
	
	// Sortiramo po vremenu
	arsort($vrijeme_poruke);
	$count=0;
	foreach ($vrijeme_poruke as $id=>$vrijeme) {
		print $code_poruke[$id];
		$count++;
		if ($count==5) break; // prikazujemo 5 poruka
	}
	if ($count==0) {
		print "Nema aktuelnih informacija.";
	}



	
	
	// OBAVJEŠTENJA
	
	?>
	</td>
	
	<td width="30%" valign="top" style="padding: 10px; padding-right:30px;" bgcolor="#f2f2f2">
					<h2><img src="static/images/32x32/info.png" align="absmiddle"> <font color="#666699">OBAVJEŠTENJA</font></h2>
	<?
	
	
	// Obavještenja
	$announcements = api_call("inbox/announcements")['results'];
	$printed = 0;
	foreach($announcements as $ann) {
		if ($ann['scope'] == 5) // scope 5 = course
			$sender = $courseNames[$ann['receiver']];
		else if ($ann['scope'] == 6) { // scope 6 = group
			$group = api_call("group/" . $ann['receiver']);
			$sender = $courseNames[$group['CourseUnit']['id']] . ", " . $group['name'];
		}
		else // For other cases, sender is administrator
			$sender = "Administrator";
		
		// Further shorten the announcement text
		$text = $ann['shortText'];
		$shortened = false;
		if (strlen($text) > 250) {
			$pos = strpos($text," ",200);
			if ($pos>220) $pos=220;
			$text = substr($text,0,$pos)."...";
			$shortened=true;
		}
		
		
		?>
		<b><?=$sender?></b> (<?=date("d.m", db_timestamp($ann['time']))?>)<br/>
		<?=$text?><?
		if (strlen($ann['longerText'])>0 || $shortened) print " (<a href=\"?sta=common/inbox&poruka=" . $ann['id'] . "\">Dalje...</a>)";
		?><br/><br/>
		<?
		$printed++;
		// Maksimalno 5 obavjestenja
		if ($printed>=5) break;
	}
	if ($printed==0)
		print "Nema novih obavještenja.";




	// PORUKE (izvadak iz inboxa)
	
	?></td>
	
	<td width="30%" valign="top" style="padding: 10px;">
	<h2><img src="static/images/32x32/messages.png" align="absmiddle"> <font color="#666699">PORUKE</font></h2><?
	
	$messages = api_call("inbox")['results'];
	$vrijeme_poruke = array();
	$code_poruke = array();
	foreach($messages as $message) {
		$id = $message['id'];
		$vrijeme_poruke[$id] = db_timestamp($message['time']);
		
		// Fino vrijeme
		$vr = db_timestamp($message['time']);
		$vrijeme="";
		if (date("d.m.Y",$vr)==date("d.m.Y")) $vrijeme = "<i>danas</i> ";
		else if (date("d.m.Y",$vr+3600*24)==date("d.m.Y")) $vrijeme = "<i>juče</i> ";
		else $vrijeme .= date("d.m. ",$vr);
		$vrijeme .= date("H:i",$vr);
		
		$naslov = $message['subject'];
		if (strlen($naslov)>30) $naslov = substr($naslov,0,28)."...";
		if (!preg_match("/\S/",$naslov)) $naslov = "[Bez naslova]";
		
		$code_poruke[$id]="<li><a href=\"?sta=common/inbox&poruka=$id\">$naslov</a><br/>($vrijeme)</li>\n";
	}
	
	// Sortiramo po vremenu
	arsort($vrijeme_poruke);
	$count=0;
	foreach ($vrijeme_poruke as $id=>$vrijeme) {
		print $code_poruke[$id];
		$count++;
		if ($count==5) break; // prikazujemo 5 poruka
	}
	if ($count==0) {
		print "<li>Nemate nijednu poruku.</li>\n";
	}

	
	?>
	</td>
	
	</tr>
	</table>
	
	<br/><br/>
	
	
	
	<?

// RSS ID

	global $conf_site_url;
	
	?>
	<a href="<?=$conf_site_url?>/rss.php?id=<?=$person['RSS']['id']?>"><img src="static/images/32x32/rss.png" width="32" height="32" border="0" align="center"> <big>RSS Feed - automatsko obavještenje o novostima!</big></a>
	<a href="http://feedvalidator.org/check.cgi?url=http%3A//zamger.etf.unsa.ba/rss.php%3Fid%3D<?=$person['RSS']['id']?>"><img src="static/images/valid-rss-rogers.png" width="88" height="31" border="0" align="center"></a>
	
	<?
}

?>
