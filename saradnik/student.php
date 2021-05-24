<?

// SARADNIK/STUDENT - prikaz svih detalja o studentu na predmetu, sa mogucnoscu editovanja



// TODO: dodati:
// - fiksne komponente, sa AJAHom



function saradnik_student() {

	global $userid, $user_siteadmin, $_api_http_code;
	
	
	require("lib/student_predmet.php"); // upis_studenta*, ispis_studenta*, update_komponente
	
	
	print '<p><a href="index.php?sta=saradnik/intro">Spisak predmeta i grupa</a></p>'."\n";
	
	// Ulazni parametri
	$student = int_param('student');
	$predmet = int_param('predmet');
	$ag = int_param('ag');

	
	$course = api_call("course/$predmet/student/$student", ["resolve" => ["Group", "ZClass", "Homework", "Exam", "Person"], "year" => $ag, "score" => "true", "activities" => "true", "totalScore" => "true", "courseInformation" => "true", "details" => "true"]);
	if ($_api_http_code == "403") {
		biguglyerror("Nemate pravo pristupa ovom studentu");
		zamgerlog ("nastavnik nije na predmetu (pp$predmet ag$ag)", 3);
		zamgerlog2("nije saradnik na predmetu", $predmet, $ag);
		return;
	}
	if ($_api_http_code != "200") {
		niceerror("Neuspješan pristup podacima studenta");
		api_report_bug($course, []);
		return;
	}


	// Provjera ulaznih podataka i podaci za naslov
	
	$ime = $course['student']['name'];
	$prezime = $course['student']['surname'];
	$brindexa = $course['student']['studentIdNr'];
	$privilegija = $course['accessLevel'];
	
	// TODO više mailova
	$mailprint = "";
	if ($course['student']['email'] != "")
		$mailprint .= "<a href=\"mailto:" . $course['student']['email'] . "\">" . $course['student']['email'] . "</a>";
	
	$nazivpredmeta = $course['courseName'];
	
	// TODO završni rad prebaciti na aktivnosti
	/*$q15 = db_query("SELECT tippredmeta FROM akademska_godina_predmet WHERE akademska_godina=$ag AND predmet=$predmet");
	$tippredmeta = db_result($q15,0,0);
	if ($tippredmeta == 1000 || $tippredmeta == 1001) {
		$q4 = db_query("SELECT id FROM zavrsni WHERE student=$student AND predmet=$predmet AND akademska_godina=$ag");
		if (db_num_rows($q4)>0) {
			$zavrsni = db_result($q4,0,0);
			?>
			<script language="JavaScript">
				location.href='?sta=nastavnik/zavrsni&akcija=zavrsni_stranica&zavrsni=<?=$zavrsni?>&predmet=<?=$predmet?>&ag=<?=$ag?>';
			</script>
			<?
			return;
		}
	}*/
	
	$nazivag = $course['courseYear'];
	
	$semestar = $course['CourseOffering']['semester'];
	$godinaStudija = round($semestar / 2);
	
	// Koji studij student sluša, koji put
	$enrollment = api_call("enrollment/current/$student", [ "resolve" => [ "Programme" ] ] );
	if ($_api_http_code == "404") {
		$nazivstudija = "Nije upisan na studij!";
		$kolpren=$ponovac=$nacin_studiranja="";
	} else {
		$nazivstudija = $enrollment['Programme']['name'];
		$enrollmentYear = round($enrollment['semester'] / 2);
		if ($enrollmentYear < $godinaStudija)
			$kolpren = ", kolizija";
		else if ($enrollmentYear > $godinaStudija)
			$kolpren = ", prenio predmet";
		else
			$kolpren = "";
		$semestar = $enrollment['semester'];
		if ($enrollment['status'] == 1)
			$ponovac = ", apsolvent";
		else if ($enrollment['repeat'])
			$ponovac=", ponovac";
		else $ponovac = "";
	}
	
	// Koliko puta i kada je student slušao ovaj predmet?
	$allCourses = api_call("course/student/$student", [ "all" => true, "resolve" => ["CourseOffering", "AcademicYear"] ] )["results"];
	$count = 0;
	$years = [];
	foreach($allCourses as $_course) {
		if ($_course['CourseOffering']['CourseUnit']['id'] == $predmet) {
			if ($ag == $_course['CourseOffering']['AcademicYear']['id'])
				break;
			$count++;
			$years[$_course['CourseOffering']['AcademicYear']['id']] = $_course['CourseOffering']['AcademicYear']['name'];
		}
	}
	$kojiput = $dosjei = "";
	if ($count > 0) {
		$count++;
		$kojiput = "($count. put sluša predmet)";
		$dosjei = "&nbsp;&nbsp;&nbsp;&nbsp;Pogledajte dosje za: ";
		$zarez = false;
		foreach($years as $yearId => $yearName) {
			if (!$zarez) $zarez=true; else $dosjei .= ", ";
			$dosjei .= "<a href=\"?sta=saradnik/student&student=$student&predmet=$predmet&ag=$yearId\">$yearName</a>";
		}
		$dosjei .= "<br>\n";
	}
	
	$labgrupa = $staragrupa = 0;
	$lgnaziv = $naziv_stare_grupe = "";
	if (array_key_exists('Group', $course) && $course['Group'] != null) {
		$labgrupa = $course['Group']['id'];
		$lgnaziv = $course['Group']['name'];
		if (!$course['Group']['virtual']) {
			$staragrupa = $labgrupa;
			$naziv_stare_grupe = $lgnaziv;
		}
	}
	
	
	// Provjera prava pristupa je na backendu


	// ----  AKCIJE

	// Akcija: ispis studenta sa predmeta
	if (param('akcija') == "ispis" && $user_siteadmin) {
		$result = api_call("course/$predmet/$ag/enroll/$student", [], "DELETE");
		if ($_api_http_code == "204") {
			zamgerlog("student ispisan sa predmeta (student u$student predmet pp$predmet)",4); // nivo 4: audit
			zamgerlog2("student ispisan sa predmeta", $student, $predmet, $ag);
			nicemessage("Student ispisan sa predmeta.");
		} else {
			niceerror("Ispisivanje studenta sa predmeta nije uspjelo");
			api_report_bug($result, []);
		}
		return;
	}

	$groups = api_call("group/course/$predmet", [ "year" => $ag] )["results"];

	if (param('akcija') == "promjena_grupe" && check_csrf_token()) {
		$novagrupa = intval($_POST['grupa']);
		if ($novagrupa==$staragrupa) {
			nicemessage("Student se već nalazi u grupi $naziv_stare_grupe!");
			print '<a href="?sta=saradnik/student&student='.$student.'&predmet='.$predmet.'&ag='.$ag.'">Nazad</a>'."\n";
			return;
		}
		
		$naziv_nove_grupe = "";
		foreach($groups as $_group) {
			if ($_group['id'] == $novagrupa)
				$naziv_nove_grupe = $_group['name'];
		}

		if ($novagrupa == 0)
			$result = api_call("group/$staragrupa/student/$student", [], "DELETE");
		else
			$result = api_call("group/$novagrupa/student/$student", [], "PUT");
		if ($_api_http_code != "201" && $_api_http_code != "204") {
			niceerror("Promjena grupe nije uspjela");
			api_report_bug($result, []);
			return;
		}
		
		// Pametni logging
		else if ($staragrupa>0 && $novagrupa>0) {
			zamgerlog("student u$student prebacen iz grupe g$staragrupa u g$novagrupa", 2); // 2 = edit
			zamgerlog2("promijenjena grupa studenta", $student, $novagrupa, 0, $staragrupa);
		} else if ($staragrupa>0) {
			zamgerlog("student u$student ispisan iz grupe g$staragrupa", 2);
			zamgerlog2("student ispisan sa grupe", $student, intval($staragrupa));
		} else {
			zamgerlog("student u$student upisan u grupu g$novagrupa", 2);
			zamgerlog2("student upisan u grupu", $student, $novagrupa);
		}
		
		// Linkovi za dalje
		print "<p>Gdje želite sada ići?:<br />\n";
		if ($staragrupa>0)
			print '- <a href="?sta=saradnik/grupa&id='.$staragrupa.'">Spisak studenata u grupi ' . $naziv_stare_grupe . '</a><br />'."\n";
		else
			print '- <a href="?sta=saradnik/grupa&predmet='.$predmet.'&ag='.$ag.'">Spisak svih studenata na predmetu</a><br/>'."\n"; // Ovo je jedini slučaj kad $staragrupa može biti nula
		if ($novagrupa>0)
			print '- <a href="?sta=saradnik/grupa&id='.$novagrupa.'">Spisak studenata u grupi ' . $naziv_nove_grupe . '</a><br />'."\n";
		print '- <a href="?sta=saradnik/student&student='.$student.'&predmet='.$predmet.'&ag='.$ag.'">Nazad na detalje studenta '.$ime.' '.$prezime.'</a>'."\n";
		return;
	}
	
	if (param('akcija') == "ponisti_kviz") {
		$kviz = intval($_REQUEST['kviz']);
		api_call("quiz/$kviz/student/$student", [], "DELETE");
		if ($_api_http_code != "204") {
			niceerror("Poništavanje kviza nije uspjelo");
			api_report_bug($result, []);
			return;
		}
		zamgerlog("ponisten kviz u$student $kviz", 2);
		zamgerlog2("ponisten kviz", $student, $kviz);
		
		nicemessage("Poništen kviz");
		
		?>
		<script language="JavaScript">
		setTimeout(function() {
			location.href='?sta=saradnik/student&student=<?=$student?>&predmet=<?=$predmet?>&ag=<?=$ag?>';
		}, 500);
		</script>
		<?
		return;
	}




	// --- ISPIS
	
	if ($course['student']['hasPhoto']) {
		?>
		<img src="?sta=common/slika&osoba=<?=$student?>" align="left" style="margin: 10px">
		<?
	}
	
	
	// Određujemo username radi slanja poruke
	$poruka_link = "<br><a href=\"?sta=common/inbox&akcija=compose&primalac=" . $course['student']['login'] . "\">Pošaljite Zamger poruku</a>";
	
	
	// Naslov
	?>
	<h1><?=$ime?> <?=$prezime?> (<?=$brindexa?>)</h1>
	<p>Upisan na (<?=$nazivag?>): <b><?=$nazivstudija?>, <?=$semestar?>. semestar <?=$ponovac?> <?=$kolpren?> <?=$kojiput?></b>
	<br />
	<?=$dosjei?>
	<b>Email: <?=$mailprint?><?=$poruka_link?></b></p>
	<h3>Predmet: <?=$nazivpredmeta?> <br />
	<?
	if ($labgrupa>0) print "Grupa: <a href=\"?sta=saradnik/grupa&id=$labgrupa\">$lgnaziv</a>";
	else print "(nije ni u jednoj grupi)";
	?>
	</h3>
	<?
	
	
	// Projekat
	// TODO prebaciti na aktivnosti
	
	/*$projekat = db_get("select distinct p.id from student_projekat as sp, projekat as p where sp.projekat=p.id and p.predmet=$predmet and p.akademska_godina=$ag and sp.student=$student");
	if ($projekat) {
		?>
		<h2><a href="?sta=nastavnik/projekti&predmet=<?=$predmet?>&ag=<?=$ag?>&akcija=projektna_stranica&projekat=<?=$projekat?>">Projekat</a></h2>
		<?
	}*/
	
	
	// Promjena grupe
	
	$nonVirtuals = 0;
	foreach($groups as $_group)
		if (!$_group['virtual']) $nonVirtuals++;
	if ($nonVirtuals > 0) {
		if ($labgrupa == 0) $nijedna = " SELECTED"; else $nijedna = "";
		?>
		<?=genform("POST");?>
		<input type="hidden" name="akcija" value="promjena_grupe">
		<p>Promijenite grupu:
		<select name="grupa" class="default"><option value="0"<?=$nijedna?>>-- Nije ni u jednoj grupi --</option>
		<?
		foreach($groups as $_group) {
			if ($_group['virtual']) continue;
			if ($_group['id'] == $labgrupa) $value="SELECTED"; else $value="";
			?>
			<option value="<?=$_group['id']?>" <?=$value?>><?=$_group['name']?></option>
			<?
		}
		?>
		</select>
		<input type="submit" value=" Promijeni grupu " class="default"></p>
		</form>
		<?
	}
	
	
	
	// PROGRESS BAR
	// Kod kopiran iz student/predmet - trebalo bi izdvojiti u lib
	
	// Sumiramo bodove po komponentama i računamo koliko je bilo moguće ostvariti
	$ukupno_bodova = $course['totalScore'];
	$ukupno_mogucih = $course['possibleScore'];
	$procenat = $course['percent'];
	
	// boja označava napredak studenta
	if ($procenat>=75)
		$boja = "#00FF00";
	else if ($procenat>=50)
		$boja = "#FFFF00";
	else
		$boja = "#FF0000";
	
	// Crtamo tabelu koristeći dvije preskalirane slike
	$ukupna_sirina = 200;
	
	$tabela1 = $procenat * 2;
	$tabela2 = $ukupna_sirina - $tabela1;
	
	// Tekst "X bodova" ćemo upisati u onu stranu tabele koja je manja
	if ($tabela1 <= $tabela2) {
		$ispis1 = "<img src=\"static/images/fnord.gif\" width=\"$tabela1\" height=\"10\">";
		$ispis2 = "<img src=\"static/images/fnord.gif\" width=\"$tabela2\" height=\"1\"><br> $ukupno_bodova bodova";
	} else {
		$ispis1="<img src=\"static/images/fnord.gif\" width=\"$tabela1\" height=\"1\"><br> $ukupno_bodova bodova";
		$ispis2="<img src=\"static/images/fnord.gif\" width=\"$tabela2\" height=\"10\">";
	}
	
	?>
	
	
	<!-- progress bar -->
	
	<table border="0"><tr><td align="left">
	<p>
	<table style="border:1px;border-style:solid" width="206" cellpadding="0" cellspacing="2"><tr>
	<td width="<?=$tabela1?>" bgcolor="<?=$boja?>"><?=$ispis1?></td>
	<td width="<?=$tabela2?>" bgcolor="#FFFFFF"><?=$ispis2?></td></tr></table>
	
	<table width="208" border="0" cellspacing="0" cellpadding="0"><tr>
	<td width="68">0</td>
	<td align="center" width="68">50</td>
	<td align="right" width="69">100</td></tr></table>
	što je <?=$procenat?>% od trenutno mogućih <?=round($ukupno_mogucih,2) ?> bodova.</p>
	</td></tr></table>
	
	
	<!-- end progress bar -->
	<?
	
	
	// Nekoliko korisnih operacija za site admina
	
	if ($user_siteadmin) {
		?>
		<p><a href="index.php?sta=saradnik/student&amp;student=<?=$student?>&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>&amp;akcija=ispis">Ispiši studenta sa predmeta</a> * <a href="index.php?sta=studentska/osobe&amp;akcija=edit&amp;osoba=<?=$student?>">Detaljnije o studentu</a> * <a href="index.php?su=<?=$student?>">Prijavi se kao student</a></p>
		<?
	}
	
	
	
	
	// PRISUSTVO:
	
	print ajah_box();
	
	?>
	<script language="JavaScript">
	// Funkcija koja se poziva klikom na polje u tabeli
	function prisustvo(student,cas) {
	if (zamger_ajah_sending) {
		alert("Slanje u toku. Sačekajte malo.");
		return false;
	}
	var prisutan = invert(student,cas);
	ajah_start("index.php?c=N&sta=common/ajah&akcija=prisustvo&student="+student+"&cas="+cas+"&prisutan="+prisutan, "invert("+student+","+cas+")");
	// U slucaju da ajah ne uspije, ponovo se poziva funkcija invert
	}
	// Switchuje DA i NE
	function invert(student,cas) {
	var val = document.getElementById("danetekst-"+student+"-"+cas).innerHTML;
	if (val == "DA") {
		document.getElementById("dane-"+student+"-"+cas).style.background = "#FFCCCC";
		document.getElementById("danetekst-"+student+"-"+cas).innerHTML = "NE";
		return 1;
	} else {
		document.getElementById("dane-"+student+"-"+cas).style.background="#CCFFCC";
		document.getElementById("danetekst-"+student+"-"+cas).innerHTML = "DA";
		return 2;
	}
	}
	function openzadaca(student,zadaca,zadatak) {
	var url='index.php?sta=saradnik/zadaca&student='+student+'&zadaca='+zadaca+'&zadatak='+zadatak;
	window.open(url,'blah','width=600,height=600,scrollbars=yes');
	}
	function toggleVisibilityObj(ime){
	var me = document.getElementById(ime);
	if (me.style.display=="none"){
		me.style.display="inline";
	}
	else {
		me.style.display="none";
	}
	return false; // da ne bi radio link
	}
	</script>
	<?
	
	
	// Ispis tablice prisustva za jednu od grupa u kojima je student
	
	function prisustvo_ispis($AttendanceDetails, $cactName) {
		// Don't print groups without attendance detail
		if (!array_key_exists('attendance', $AttendanceDetails) || empty($AttendanceDetails['attendance']))
			return;
		
		$imegrupe = "";
		if (array_key_exists("Group", $AttendanceDetails) && !$AttendanceDetails['Group']['virtual'])
			$imegrupe = " (" . $AttendanceDetails['Group']['name'] . ")";
		
		$odsustva=0;
		$datumi = $vremena = $statusi = "";
		foreach($AttendanceDetails['attendance'] as $Attendance) {
			$time = db_timestamp($Attendance['ZClass']['dateTime']);
			$datumi .= "<td>" . date("d.m" , $time) . "</td>\n";
			$vremena .= "<td>" . date("H" , $time) . "<sup>" . date("i" , $time) . "</sup></td>\n";
			$student = $Attendance['student']['id'];
			$class = $Attendance['ZClass']['id'];
		
			if ($Attendance['presence'] == 0) {
				$statusi .= "<td bgcolor=\"#FFCCCC\" align=\"center\" id=\"dane-".$student."-".$class."\" onclick=\"javascript:prisustvo(".$student.",".$class.")\"><div id=\"danetekst-".$student."-".$class."\">NE</div></td>\n";
				$odsustva++;
			} else if ($Attendance['presence'] == 1) {
				$statusi .= "<td bgcolor=\"#CCFFCC\" align=\"center\" id=\"dane-".$student."-".$class."\" onclick=\"javascript:prisustvo(".$student.",".$class.")\"><div id=\"danetekst-".$student."-".$class."\">DA</div></td>\n";
			} else {
				$statusi .= "<td bgcolor=\"#FFFFCC\" align=\"center\" id=\"dane-".$student."-".$class."\" onclick=\"javascript:prisustvo(".$student.",".$class.")\"><div id=\"danetekst-".$student."-".$class."\"> / </div></td>\n";
			}
			
		}
		
		?>
	
		<b><?=$cactName?><?=$imegrupe?>:</b><br/>
		<table cellspacing="0" cellpadding="2" border="0" id="prisustvo" class="prisustvo">
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
	}
	
	
	$bodovi = 0; $found = false;
	foreach($course['score'] as $StudentScore) {
		if ($StudentScore['CourseActivity']['Activity']['id'] != 9) // 9 Prisustvo
			continue;
		$found = true;
		$bodovi += $StudentScore['score'];
		foreach ($StudentScore['details'] as $AttendanceDetails)
			prisustvo_ispis($AttendanceDetails, $StudentScore['CourseActivity']['name']);
	}
	
	if ($found) {
		?><p>Ukupno na prisustvo: <b><?=$bodovi?></b> bodova.</p>
		<?
	}

	
	
	
	// KVIZOVI
	
	$quizResults = api_call("quiz/course/$predmet/student/$student", ["year" => $ag, "resolve" => [ "Quiz" ]])["results"];
	if (count($quizResults) > 0) {
		?>
		
		<b>Kvizovi:</b><br/>
		<table cellspacing="0" cellpadding="2" border="0" id="kvizovi">
		<thead>
		<tr>
			<th>Naziv kviza</th>
			<th>Rezultat</th>
			<th>Akcije</th>
		</tr>
		</thead>
		<?
		
		foreach($quizResults as $qr) {
			$tekst = "";
		
			if ($qr['student']['id'] != null) {
				if (!$qr['finished']) {
					$tekst = "<img src=\"static/images/16x16/wait_icon.png\" width=\"8\" height=\"8\"> Nije završio/la";
				} else if ($qr['score'] < $qr['Quiz']['passPoints']) {
					$tekst = "<img src=\"static/images/16x16/not_ok.png\" width=\"8\" height=\"8\"> ".$qr['score']." bodova";
				} else {
					$tekst = "<img src=\"static/images/16x16/ok.png\" width=\"8\" height=\"8\"> ".$qr['score']." bodova";
				}
			}
		
			?>
			<tr>
				<td><?=$qr['Quiz']['name']?></td>
				<td><?=$tekst?></td>
				<td><? if ($tekst !== "") { ?><a href="?sta=saradnik/student&student=<?=$student?>&predmet=<?=$predmet?>&ag=<?=$ag?>&akcija=ponisti_kviz&kviz=<?=$qr['Quiz']['id']?>">Poništi kviz</a><? } ?></td>
			</tr>
			<?
		}
		
		?>
		</table>
		</p>
		<?
	}
	
	
	
	
	//  ZADAĆE
	
	// Statusne ikone:
	$stat_icon = array("bug", "view", "copy", "bug", "view", "ok");
	$stat_tekst = array("Bug u programu", "Pregled u toku", "Zadaća prepisana", "Bug u programu", "Pregled u toku", "Zadaća OK");
	
	foreach($course['score'] as $StudentScore) {
		$cact = $StudentScore['CourseActivity']; // shortcut
		if ($cact['Activity']['id'] != 2) // 2 = Zadaće
			continue;
		
		// Transform homework details into a 2D matrix which is much easier to work with
		$homeworks = [];
		$assignmentHomeworks = [];
		foreach($StudentScore['details'] as $Assignment) {
			if (!array_key_exists($Assignment['Homework']['id'], $assignmentHomeworks)) {
				$homeworks[] = $Assignment['Homework'];
				$assignmentHomeworks[$Assignment['Homework']['id']] = [];
			}
			$assignmentHomeworks[$Assignment['Homework']['id']][] = $Assignment;
		}
		
		$totalSumScore = $totalMaxScore = 0;
		
		?>

		<!-- zadace -->
		
		<b><?=$cact['name']?>:</b><br/>
		<table cellspacing="0" cellpadding="2" border="0" id="zadace<?=$cact['id']?>" class="zadace">
			<thead>
			<tr>
				<td>&nbsp;</td>
				<?
		
		// We need a maximum number of assignments per homework for table heading
		$maxAssignments = 0;
		foreach ($homeworks as $homework) {
			if ($homework['nrAssignments'] > $maxAssignments)
				$maxAssignments = $homework['nrAssignments'];
		}
		for ($i=1;$i<=$maxAssignments;$i++) {
				?>
				<td>Zadatak <?=$i?>.</td>
				<?
		}
		
			?>
				<td><b>Ukupno bodova</b></td>
				</tr>
			</thead>
			<tbody>
			<?
			
			
		// Print homework details
		foreach ($homeworks as $homework) {
			?>
			<tr>
				<th><?=$homework['name']?></th>
				<?
				
			$sumScore = 0;
			for($asgn=1; $asgn<=$maxAssignments; $asgn++) {
				// If this homework has less than maxAssignments, print empty cells
				if ($asgn > $homework['nrAssignments']) {
					?><td>&nbsp;</td><?
					continue;
				}
				
				// Get assignment from 2D array
				$Assignment = $assignmentHomeworks[$homework['id']][$asgn-1];
				
				// id=null means user did not submit homework
				if (!$Assignment['id']) {
					?>
					<td>&nbsp;</td><?
					
				} else {
					if (!empty(trim($Assignment['comment'])))
						$hasComment = "<img src=\"static/images/16x16/comment_yellow.png\"  width=\"15\" height=\"14\" border=\"0\" title=\"Ima komentar\" alt=\"Ima komentar\" align=\"center\">";
					else
						$hasComment = "";
				
					?>
					<td><a href="javascript:openzadaca('<?=$student?>', '<?=$homework['id']?>', '<?=$asgn?>')"><img src="static/images/16x16/<?=$stat_icon[$Assignment['status']]?>.png" width="16" height="16" border="0" align="center" title="<?=$stat_tekst[$Assignment['status']]?>" alt="<?=$stat_tekst[$Assignment['status']]?>"> <?=$Assignment['score']?> <?=$hasComment?></a></td>
					<?
			
					$sumScore += $Assignment['score'];
				}
			}
				?>
				<td><?=$sumScore?></td>
			</tr>
			<?
			$totalSumScore += $sumScore;
		}
		
		
		?>
		<tr><td colspan="<?=$maxAssignments+1?>" align="right">UKUPNO: </td>
		<td><?=$totalSumScore?></td></tr>
		</tbody>
		</table>
		
		<p>Za historiju izmjena kliknite na željeni zadatak. <a href="#" onclick="javascript:window.open('legenda-zadace.html','blah6','width=320,height=130'); return false;">Legenda simbola</a></p>
		<br/>
		
		<!-- end zadace -->
		
		<?
	
	
	} // foreach($course['score']...
	
	
	
	// Importujemo kod za coolbox
	cool_box('ajah_start("index.php?c=N&sta=common/ajah&akcija=izmjena_ispita&idpolja="+zamger_coolbox_origcaller.id+"&vrijednost="+coolboxedit.value+"&staravrijednost="+zamger_coolbox_origvalue, "undo_coolbox()", "zamger_coolbox_origcaller=false");');
	?>
	<script language="JavaScript">
	function undo_coolbox() {
	var greska = document.getElementById("zamger_ajah-info").innerText || document.getElementById("zamger_ajah-info").textContent;
	if (!greska.match(/\S/)) greska = "Došlo je do greške. Molimo kontaktirajte administratora.";
	alert(greska);
	zamger_coolbox_origcaller.innerHTML = zamger_coolbox_origvalue;
	zamger_coolbox_origcaller=false;
	}
	</script>
	<?
	
	
	
	
	// FIKSNE KOMPONENTE
	
	$printedHeadline = false;
	foreach($course['score'] as $StudentScore) {
		if ($StudentScore['CourseActivity']['Activity']['id'] == null) {
			if ($StudentScore['score'] == null)
				$ocjenaedit = "/";
			else
				$ocjenaedit = $StudentScore['score'];
			$komponenta = $StudentScore['CourseActivity']['id'];
			if (!$printedHeadline) {
				$printedHeadline = true;
				?>
				
				<!-- fiksne komponente -->
				
				<table cellspacing="0" cellpadding="2" border="0" id="fiksne" class="zadace">
					<thead>
					<tr>
						<td><b>Komponenta ocjene</b></td>
						<td><b>Bodovi</b></td>
						<td><b>Dnevnik izmjena</b></td>
					</tr>
					</thead>
					<tbody>
					<?
			}
			?>
			<tr>
				<td><?=$StudentScore['CourseActivity']['name']?></td>
				<td id="fiksna-<?=$student?>-<?=$predmet?>-<?=$komponenta?>-<?=$ag?>" ondblclick="coolboxopen(this)"><?=$ocjenaedit?></td>
				<td><?
					if ($privilegija=="nastavnik" || $privilegija=="super_asistent" || $user_siteadmin) {
						?><div id="fiksnalog<?=$komponenta?>"></div><?
					} else print "/";
					?></td>
			</tr>
			<?
		}
	}
	
	
	
	//  ISPITI
	
	// Trebaju nam svi ispiti
	$exams = api_call("exam/course/$predmet/$ag", [ "resolve" => ["CourseActivity"] ] )["results"];
	
	?>
	
	<!-- ispiti -->
	
	<b>Ispiti:</b><br/>
	
	<?
	
	// Sort exam results by date
	$rezultati_ispita = $examResults = [];
	foreach($course['score'] as $StudentScore) {
		if ($StudentScore['CourseActivity']['Activity']['id'] == 8) {
			foreach ($StudentScore['details'] as $ExamResult) {
				$time = db_timestamp($ExamResult['Exam']['date']);
				$ExamResult['name'] = $StudentScore['CourseActivity']['name'];
				$ExamResult['date'] = date("d. m. Y", $time);
				$ExamResult['pass'] = ($ExamResult['result'] >= $ExamResult['Exam']['passPoints']);
				$examResults[$time . $StudentScore['CourseActivity']['id']] = $ExamResult;
			}
		}
	}
	
	if (count($exams) == 0) {
		?>
		<p>Nije bilo parcijalnih ispita.</p>
		<?
	} else {
		?>
		<table cellspacing="0" cellpadding="2" border="0" id="ispiti" class="zadace">
		<thead>
		<tr>
			<td><b>Tip ispita</b></td>
			<td><b>Datum ispita</b></td>
			<td><b>Položio/la?</b></td>
			<td><b>Bodovi</b></td>
			<td><b>Dnevnik izmjena</b></td>
		</tr>
		</thead>
		<tbody>
		<?
	}
	
	//ksort($examResults);
	foreach($exams as $exam) {
		$result = "/";
		foreach($course['score'] as $StudentScore) {
			if ($StudentScore['CourseActivity']['Activity']['id'] != 8) continue;
			foreach($StudentScore['details'] as $detail) {
				if ($detail['Exam']['id'] == $exam['id']) {
					$result = $detail['result'];
					break;
				}
			}
		}
		$ispit = $exam['id'];
		$rezultati_ispita[$ispit] = $result;
		$datum = date("d. m. Y", db_timestamp($exam['date']));
		
		?>
		<tr>
			<td><?=$exam['CourseActivity']['name']?></td>
			<td><?=$datum?></td>
			<td><?
				if ($result == "/")
					print "&nbsp;";
				else if ($result >= $exam['CourseActivity']['pass'])
					print "<img src=\"static/images/16x16/ok.png\" width=\"16\" height=\"16\">";
				else
					print "<img src=\"static/images/16x16/not_ok.png\" width=\"16\" height=\"16\">"; // najljepše slike
				?></td>
			<td id="ispit-<?=$student?>-<?=$ispit?>" ondblclick="coolboxopen(this)"><?=$result?></td>
			<td><?
				if ($privilegija=="nastavnik" || $privilegija=="super_asistent" || $user_siteadmin) {
					?><div id="ispitlog<?=$ispit?>"><img src="static/images/busy-light-25x25.gif" width="16" height="16"></div><?
				} else print "/";
				?></td>
		</tr>
		<?
	}
	
	if (count($exams) > 0) {
	?>
	</tbody></table>
	<p>Dvokliknite na bodove da promijenite podatak ili upišete novi. Za brisanje rezultata, pobrišite postojeći podatak i pritisnite Enter.</p>
	<?
	}
	
	
	
	
	// KONAČNA OCJENA
	
	if ($course['grade'] != null) {
		$konacnaocjena = $course['grade'];
		$datum_u_indeksu = date("d. m. Y", db_timestamp($course['gradeDate']));
	} else {
		$konacnaocjena = "/";
		$datum_u_indeksu = "";
	}
	
	?>
	<p>&nbsp;</p>
	<table cellspacing="0" cellpadding="2" border="0" id="konacna_ocjena" class="zadace">
	<tr>
	<td>&nbsp;</td>
	<td>Ocjena:</td>
	<td>Datum u indeksu:</td>
	<? if ($privilegija=="nastavnik" || $user_siteadmin) { ?>
	<td>Dnevnik izmjena:</td>
	<? } ?>
	</tr>
	<tr>
	<td><b>Konačna ocjena:</b></td>
	<?
	
	if ($privilegija=="nastavnik" || $user_siteadmin) {
		?>
		<td id="ko-<?=$student?>-<?=$predmet?>-<?=$ag?>" ondblclick="coolboxopen(this)"><?=$konacnaocjena?></td>
		<td id="kodatum-<?=$student?>-<?=$predmet?>-<?=$ag?>" ondblclick="coolboxopen(this)"><?=$datum_u_indeksu?></td>
		<td><div id="kolog"><img src="static/images/busy-light-25x25.gif" width="16" height="16"></div></td>
		<?
	} else {
		?>
		<td><?=$konacnaocjena?></td>
		<td><?=$datum_u_indeksu?></td>
		<?
	}
	
	?>
	</tr></table>
	<?
	
	
	
	// **************************************
	// POPUNA LOGOVA TODO
	// **************************************
	
	// Ne radimo ništa ako korisnik nema privilegije
	
	if ($privilegija != "nastavnik" && $privilegija != "super_asistent" && !$user_siteadmin) return;
	
	?>
	
	
	<SCRIPT language="JavaScript">
	
	setTimeout(function() { ucitajLogove(<?=$student?>, <?=$predmet?>, <?=$ag?>); }, 100);
	var konacnaocjena = '<?=$konacnaocjena?>';
	var rezultati_ispita = {};
	<?
	foreach($rezultati_ispita as $ispit => $bodovi)
		print "rezultati_ispita['".$ispit."'] = '$bodovi';\n";
	
	?>
	
	function ucitajLogove(student, predmet, ag) {
		var xmlhttp = new XMLHttpRequest();
		var url = "index.php?sta=ws/log&tip_loga=student&student=" + student + "&predmet=" + predmet + "&ag=" + ag;
		xmlhttp.onreadystatechange = function() {
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
				result = JSON.parse(xmlhttp.responseText);
				if (result.success == "true") {
					parsirajLogove(result.data);
				} else {
					console.log("Web servis za logove vratio success=false");
					console.log(result);
				}
				return false;
			}
			if (xmlhttp.readyState == 4 && xmlhttp.status == 500) {
				console.log("Serverska greška kod pozivanja web servisa za logove.");
				console.log("readyState "+xmlhttp.readyState+" status "+xmlhttp.status);
			}
		}
		xmlhttp.open("GET", url, true);
		xmlhttp.send();
	
	}
	
	function parsirajLogove(log) {
		if (document.getElementById('kolog')) document.getElementById('kolog').innerHTML = "";
		for (var ispit in rezultati_ispita) {
			if (rezultati_ispita.hasOwnProperty(ispit)) {
				document.getElementById('ispitlog' + ispit).innerHTML = "";
			}
		}
		for (i=0; i<log.length; i++) {
			var stavka = log[i];
			
			if (stavka.opis_dogadjaja == "dodana ocjena" && document.getElementById('kolog')) {
				if (stavka.ocjena != konacnaocjena) stavka.ocjena += " ?";
				konacnaocjena = "/";
				
				document.getElementById('kolog').innerHTML = '<img src="static/images/16x16/edit_red.png" width="16" height="16" align="center"> dodana ocjena <b>' + stavka.ocjena + '</b> (' + stavka.korisnik + ', ' + stavka.vrijeme + ')<br />' + document.getElementById('kolog').innerHTML;
				
			} else if (stavka.opis_dogadjaja == "obrisana ocjena" && document.getElementById('kolog')) {
				if (konacnaocjena != "/")
					stavka.ocjena += " ?";
				else
					konacnaocjena=stavka.ocjena;
				
				document.getElementById('kolog').innerHTML = '<img src="static/images/16x16/edit_red.png" width="16" height="16" align="center"> obrisana ocjena (' + stavka.korisnik + ', ' + stavka.vrijeme + ')<br />' + document.getElementById('kolog').innerHTML;
				
			} else if (stavka.opis_dogadjaja == "izmjena ocjene" && document.getElementById('kolog')) {
				if (stavka.ocjena != konacnaocjena) stavka.ocjena += " ?";
				konacnaocjena = stavka.stara_ocjena;
				
				document.getElementById('kolog').innerHTML = '<img src="static/images/16x16/edit_red.png" width="16" height="16" align="center"> promijenjena ocjena u <b>' + stavka.ocjena + '</b> (' + stavka.korisnik + ', ' + stavka.vrijeme + ')<br />' + document.getElementById('kolog').innerHTML;
				
			} else if (stavka.opis_dogadjaja == "promijenjen datum ocjene" && document.getElementById('kolog')) {
				document.getElementById('kolog').innerHTML = '<img src="static/images/16x16/edit_red.png" width="16" height="16" align="center"> promijenjena datum ocjene u <b>' + stavka.datum_ocjene + '</b> (' + stavka.korisnik + ', ' + stavka.vrijeme + ')<br />' + document.getElementById('kolog').innerHTML;
				
			} else if (stavka.opis_dogadjaja == "upisan rezultat ispita" && document.getElementById('ispitlog' + stavka.ispit)) {
				if (stavka.bodovi != rezultati_ispita[stavka.ispit])
					stavka.bodovi += " ?";
				rezultati_ispita[stavka.ispit] = "/";
				
				document.getElementById('ispitlog' + stavka.ispit).innerHTML = '<img src="static/images/16x16/edit_red.png" width="16" height="16" align="center"> upisan rezultat <b>' + stavka.bodovi + '</b> (' + stavka.korisnik + ', ' + stavka.vrijeme + ')<br />' + document.getElementById('ispitlog' + stavka.ispit).innerHTML;
				
			} else if (stavka.opis_dogadjaja == "izbrisan rezultat ispita" && document.getElementById('ispitlog' + stavka.ispit)) {
				if (rezultati_ispita[stavka.ispit] != "/")
					stavka.stari_bodovi += " ?";
				else
					rezultati_ispita[stavka.ispit] = stavka.stari_bodovi;
				
				document.getElementById('ispitlog' + stavka.ispit).innerHTML = '<img src="static/images/16x16/edit_red.png" width="16" height="16" align="center"> izbrisan rezultat (' + stavka.korisnik + ', ' + stavka.vrijeme + ')<br />' + document.getElementById('ispitlog' + stavka.ispit).innerHTML;
				
			} else if (stavka.opis_dogadjaja == "izmjenjen rezultat ispita" && document.getElementById('ispitlog' + stavka.ispit)) {
				if (stavka.bodovi != rezultati_ispita[stavka.ispit])
					stavka.bodovi += " ?";
				rezultati_ispita[stavka.ispit] = stavka.stari_bodovi;
				
				document.getElementById('ispitlog' + stavka.ispit).innerHTML = '<img src="static/images/16x16/edit_red.png" width="16" height="16" align="center"> promijenjen rezultat u <b>' + stavka.bodovi + '</b> (' + stavka.korisnik + ', ' + stavka.vrijeme + ')<br />' + document.getElementById('ispitlog' + stavka.ispit).innerHTML;
				
			} else if (stavka.opis_dogadjaja == "izmjena bodova za fiksnu komponentu" && document.getElementById('fiksnalog' + stavka.komponenta)) {
				document.getElementById('fiksnalog' + stavka.komponenta).innerHTML = '<img src="static/images/16x16/edit_red.png" width="16" height="16" align="center"> promijenjeni bodovi u <b>' + stavka.bodovi + '</b> (' + stavka.korisnik + ', ' + stavka.vrijeme + ')<br />' + document.getElementById('fiksnalog' + stavka.komponenta).innerHTML;
			}
		}
	}
	
	</SCRIPT>
	<?
}


?>
