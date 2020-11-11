<?

// SARADNIK/GRUPA - administracija jedne grupe na predmetu



function saradnik_grupa() {

	global $userid,$user_siteadmin, $_api_http_code;
	
	require_once("lib/student_predmet.php"); // update_komponente
	require_once("lib/utility.php"); // procenat, bssort, array_to_object


	?>
	<p><a href="index.php?sta=saradnik/intro">Spisak predmeta i grupa</a></p>
	<?


	// ------- ULAZNI PARAMETRI
	
	$labgrupa = int_param('id');
	$kreiranje = int_param('kreiranje');
	
	
	// ------- AKCIJE
	
	// Dodavanje casa
	
	if (param('akcija') == 'dodajcas' && check_csrf_token()) {
		// KOMPONENTA
		// Ovaj kod radi samo sa jednom komponentom prisustva. U budućnosti to bi moglo biti popravljeno, ali realno nema prevelike potrebe
		
		$datum = intval($_POST['godina'])."-". intval($_POST['mjesec'])."-". intval($_POST['dan']);
		$vrijeme = $_POST['vrijeme'];
		if (!preg_match("/^\d?\d:\d\d$/", $vrijeme)) {
			niceerror("Vrijeme nije u ispravnom formatu!");
			print "<p>Vrijeme mora biti oblika HH:MM, a vi ste unijeli '$vrijeme'.</p>";
			print "<p><a href=\"?sta=saradnik/grupa&id=$labgrupa\">Nazad</a></p>";
			return;
		}
		$predavanje = intval($_POST['predavanje']); // Not used
		
		// Ako se klikne na refresh, datum moze biti 0-0-0...
		if ($datum != "0-0-0") {
			$komponenta = intval($_POST['komponenta']);
			
			$kviz = intval($_REQUEST['kviz']);
			if ($kviz == 0) $kviz = "null";
			
			$prisustvo = intval($_POST['prisustvo']);
			
			if ($labgrupa == 0) {
				// Moramo izvršiti jedan upit ranije da bismo dobili id grupe ako je zadat predmet i ag
				$predmet = intval($_REQUEST['predmet']);
				$ag = intval($_REQUEST['ag']);
				$group = api_call("group/course/$predmet/allStudents", [ "year" => $ag ] );
				$labgrupa = $group['id'];
			}
			
			// Create ZClass object
			$zclass = [
				"id" => 0,
				"dateTime" => "$datum $vrijeme",
				"teacher" => [ "id" => $userid ],
				"Group" => [ "id" => $labgrupa ],
				"CourseActivity" => [ "id" => $komponenta ],
				"Quiz" => [ "id" => $kviz ],
				"defaultPresence" => $prisustvo
			];
			$result = api_call("class/group/$labgrupa", array_to_object($zclass), "PUT");
			if ($_api_http_code == "201") {
				$cas_id = $result['id'];
				zamgerlog("registrovan cas c$cas_id", 2); // nivo 2: edit
				zamgerlog2("registrovan cas", $cas_id);
			} else {
				niceerror("Greška prilikom kreiranja časa: " . $result['message']);
				print_r($result);
			}
		}
	}
	
	// Brisanje casa
	if (param('akcija') == 'brisi_cas' && check_csrf_token()) {
		$cas_id = intval($_POST['_lv_casid']);
		$result = api_call("class/$cas_id", [], "DELETE");
		if ($_api_http_code == "204") {
			zamgerlog("obrisan cas $cas_id",2);
			zamgerlog2("obrisan cas", $cas_id);
		} else {
			niceerror("Greška prilikom brisanja časa (code $_api_http_code)"); // DELETE request can't return anything
			print_r($result);
		}
	}

	
	// ------- PREUZIMANJE PODATAKA
	
	if ($labgrupa>0) {
		$group = api_call("group/$labgrupa",
			[ "details" => true, "names" => true,
				"resolve" => ["Homework", "ZClass"] ]
		);
		if ($_api_http_code != "200") {
			biguglyerror("Nemate pravo ulaska u ovu grupu!");
			zamgerlog("nepostojeca labgrupa $labgrupa",3); // 3 = greska
			zamgerlog2("nepostojeca labgrupa", $labgrupa);
			return;
		}
		$naziv = $group['name'];
		$predmet = $group['CourseUnit']['id'];
		$ag = $group['AcademicYear']['id'];
		$grupa_virtualna = $group['virtual'];
	
	} else {
		// Ako nije definisana grupa, probacemo preko predmeta i ag uci u virtuelnu grupu
		$predmet = intval($_REQUEST['predmet']);
		$ag = intval($_REQUEST['ag']);
		$group = api_call("group/course/$predmet/allStudents",
			[ "details" => true, "names" => true, "year" => $ag,
				"resolve" => ["Homework", "ZClass"] ]
		);
		if ($_api_http_code != "200") {
			biguglyerror("Nemate pravo ulaska u ovu grupu!");
			zamgerlog("nepostojeca labgrupa $labgrupa",3); // 3 = greska
			zamgerlog2("nepostojeca labgrupa", $labgrupa);
			return;
		}
		$labgrupa = $group['id'];
		$naziv = $group['name'];
		$grupa_virtualna = true;
	}


	// Spisak komponenti koje su zastupljene na predmetu
	foreach($group['activities'] as $activity)
		$tipovi_komponenti[$activity['id']] = $activity['Activity']['id'];
	



	// ------- ZAGLAVLJE STRANICE (naslov i sl.)

	$course = api_call("course/$predmet/$ag");
	
	?>
	<br />
	<center><h1><?=$course['courseName']?> - <?=$naziv?></h1></center>
	<?



	// -------- CACHE: SPISAK STUDENATA U GRUPI
	
	$imeprezime = array();
	$brind = array();
	foreach($group['members'] as $member) {
		$studentId = $member['student']['id'];
		
		$imeprezime[$studentId] = $member['student']['surname'] . "&nbsp;" . $member['student']['name'];
		if ($grupa_virtualna && $member['Group'])
			$imeprezime[$studentId] .= " (" . $member['Group']['name'] . ")";
		$brind[$studentId] = $member['student']['studentIdNr'];
	}
	uasort($imeprezime,"bssort"); // bssort - bosanski jezik


	// Ako nema nikoga u grupi, prekidamo rad odmah
	
	if (count($imeprezime) == 0) {
		print "<p>Nijedan student nije u grupi</p>\n";
		return;
	}


	
	// JavaScript za prikaz popup prozora (trenutno se koristi samo za komentare)
	//  * FF ne podržava direktan poziv window.open() iz eventa
	
	?>
	<script language="JavaScript">
	function firefoxopen(p1,p2,p3) {
		window.open(p1,p2,p3);
	}
	</script>
	
	<?

	
	// Cool editing box
	if ($privilegija=="nastavnik" || $privilegija=="super_asistent" || $user_siteadmin) {
		if ($course['gradeType'] == 1 || $course['gradeType'] == 2) {
			?>
			<SCRIPT language="JavaScript">
			function ispunio_uslove(element, ocjena) {
				var id = element.id;
				var vrijednost = element.checked;
				if (vrijednost!=origval[id]) {
					var oc_vrijednost;
					if (!vrijednost) ocjena='/';
					var value = parseInt(element.id.substr(6));
					ajah_start("index.php?c=N&sta=common/ajah&akcija=izmjena_ispita&idpolja=ko-"+value+"-<?=$predmet?>-<?=$ag?>&vrijednost="+ocjena+"","document.getElementById('"+id+"').focus()");
				}
			}
			var origval=new Array();
			</SCRIPT>
			<?
	
		}
		else {
			cool_box('ajah_start("index.php?c=N&sta=common/ajah&akcija=izmjena_ispita&idpolja="+zamger_coolbox_origcaller.id+"&vrijednost="+coolboxedit.value, "undo_coolbox()", "zamger_coolbox_origcaller=false");');
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
		}
	}
	
	
	// ------- PIVOT DETAILS DATA, FOR FASTER TABLE RENDERING
	
	$cactTitles = $cactScores = [];
	$homeworks = $cactHomeworks = $homeworkStatus = $homeworkScore = [];
	$classes = $cactClasses = $presenceCache = [];
	$examResults = [];
	foreach($group['members'] as $member) {
		$studentId = $member['student']['id'];
		foreach($member['score'] as $score) {
			$activityType = $score['CourseActivity']['Activity']['id'];
			$cactId = $score['CourseActivity']['id'];
			$cactTitles[$cactId] = $score['CourseActivity']['name'];
			$cactScores[$cactId][$studentId] = $score['score'];
			
			if ($activityType == null) // null = Fixed component
				continue; // No details
			
			foreach($score['details'] as $detail) {
				if ($activityType == 2) { // 2 = Homework
					$homeworkId = $detail['Homework']['id'];
					$assignNo = $detail['assignNo'];
					$status = $detail['status'];
					$score = $detail['score'];
					
					if (!array_key_exists($homeworkId, $homeworks)) {
						$cactHomeworks[$cactId][$homeworkId] = $detail['Homework'];
						$homeworks[$homeworkId] = $detail['Homework'];
					}
					
					$homeworkStatus[$homeworkId][$assignNo][$studentId] = $status;
					$homeworkScore[$homeworkId][$assignNo][$studentId] = $score;
				}
				
				if ($activityType == 9) { // 9 = Attendance
					foreach($detail['attendance'] as $attendance) {
						if (!array_key_exists($cactId, $cactClasses))
							$cactClasses[$cactId] = [];
						// In virtual groups, we would receive attendance detail for all groups
						if ($attendance['ZClass']['Group']['id'] != $labgrupa) continue;
						
						$classId = $attendance['ZClass']['id'];
						if (!array_key_exists($classId, $classes)) {
							$cactClasses[$cactId][$classId] = $attendance['ZClass'];
							$classes[$classId] = $attendance['ZClass'];
						}
						$presenceCache[$classId][$studentId] = $attendance['presence'];
					}
				}
				
				if ($activityType == 8) { // 8 = Exam
					$examId = $detail['Exam']['id'];
					$examResults[$examId][$studentId] = $detail['result'];
				}
			}
		}
	}
	
	// Get exam list from api, since details will not include exams that noone took
	$exams = api_call("exam/course/$predmet/$ag", [ "resolve" => ["CourseActivity"] ] )["results"];
	
	// Get fixed cacts list
	$fixedCacts = [];
	foreach($course['activities'] as $cact)
		if ($cact['Activity']['id'] == null)
			$fixedCacts[$cact['id']] = $cact;
	
	// Get list of homeworks
	$allHomeworks = api_call("homework/course/$predmet/$ag", [ "resolve" => ["CourseActivity"] ] )["results"];
	foreach($allHomeworks as $hw) {
		$cactId = $hw['CourseActivity']['id'];
		if (!array_key_exists($cactId, $cactHomeworks))
			$cactHomeworks[$cactId] = [];
		$found = false;
		foreach($cactHomeworks[$cactId] as $hwk)
			if ($hwk['id'] == $hw['id'])
				$found = true;
		if (!$found)
			$cactHomeworks[$cactId][] = $hw;
		if (!array_key_exists($cactId, $cactTitles))
			$cactTitles[$cactId] = $hw['CourseActivity']['name'];
	}
	
	// Sort classes by dateTime within each component
	foreach($cactClasses as &$cc) {
		uasort($cc, function($c1, $c2) { return db_timestamp($c1['dateTime']) > db_timestamp($c2['dateTime']); });
	}
	// Sort exams by date
	uasort($exams, function($e1, $e2) { return db_timestamp($e1['date']) > db_timestamp($e2['date']); });

	$quizResultCache = []; // Cache quizzes
	$quizzes = api_call("quiz/course/$predmet/$ag")["results"];
	foreach($quizzes as $quiz) {
		$quizResults = api_call("quiz/" . $quiz['id'] . "/group/$labgrupa")["results"];
		foreach($quizResults as $qr) {
			$studentId = $qr['student']['id'];
			if ($qr['finished'] && $qr['score'] >= $quiz['passPoints'])
				$quizResultCache[$quiz['id']][$studentId] = true;
			else
				$quizResultCache[$quiz['id']][$studentId] = false;
		}
	}
	
	// ------- SPISAK NEPREGLEDANIH ZADAĆA
	
	if (in_array(2, $tipovi_komponenti)) { // 2 = zadaće
	
		// JavaScript za prikaz popup prozora sa zadaćom
		//  * Kod IE naslov prozora ('zadaca') ne smije sadržavati razmak i
		// ne smije biti prazan, a inače je nebitan
	
		?>
		<script language="JavaScript">
		function openzadaca(e, student,zadaca,zadatak) {
			var evt = e || window.event;
			var url='index.php?sta=saradnik/zadaca&student='+student+'&zadaca='+zadaca+'&zadatak='+zadatak;
			if (evt.shiftKey)
				window.open(url,'_blank','width=600,height=600,scrollbars=yes');
			else
				window.open(url,'zadaca','width=600,height=600,scrollbars=yes');
		}
		</script>
		
		<?
	
	
		$print="";
		foreach ($homeworkStatus as $zadaca => $data1) {
			foreach ($data1 as $zadatak => $data2) {
				foreach($data2 as $student => $status) {
					if ($status==4)
						$print .= '<li><a href="#" onclick="javascript:openzadaca(event, \''.$student.'\',\''.$zadaca.'\',\''.$zadatak.'\')">'.$imeprezime[$student]." - ".$homeworks[$zadaca]['name'].", zadatak ".$zadatak."</a></li>";
				}
			}
		}

		if ($print != "") print "<h2>Nove zadaće za pregled:</h2>\n<ul>$print</ul>";
	}





	// ------- FORMA ZA NOVI ČAS
	
	if (in_array(9, $tipovi_komponenti)) { // 9 = prisustvo
		$dan=date("d"); $mjesec=date("m"); $godina=date("Y");
		$vrijeme=date("H:i");
		
		// Tražimo komponentu prisustva i uzimamo prvu
		// FIXME: praktično je nemoguće registrovati čas za drugu komponentu
		$komponenta = 0;
		foreach ($tipovi_komponenti as $k_id => $tip) {
			if ($tip == 9) { // 9 = prisustvo
				$komponenta = $k_id;
				break;
			}
		}
	
		// Ujedno ćemo definisati i neke JavaScripte za prisustvo
	
		?>
		<table border="0" width="100%"><tr><td valign="top" width="50%">&nbsp;</td>
		<td valign="top" width="50%">
			Registrujte novi čas:<br/>
			<?=genform("POST")?>
			<input type="hidden" name="komponenta" value="<?=$komponenta?>">
			<input type="hidden" name="akcija" value="dodajcas">
		
			Datum:
			<select name="dan" class="default"><?
			for ($i=1; $i<=31; $i++) {
				print "<option value=\"$i\"";
				if ($i==$dan) print " selected";
				print ">$i</option>";
			}
			?></select>&nbsp;&nbsp;
			<select name="mjesec" class="default"><?
			for ($i=1; $i<=12; $i++) {
				print "<option value=\"$i\"";
				if ($i==$mjesec) print " selected";
				print ">$i</option>";
			}
			?></select>&nbsp;&nbsp;
			<select name="godina" class="default"><?
			for ($i=2005; $i<=2020; $i++) {
				print "<option value=\"$i\"";
				if ($i==$godina) print " selected";
				print ">$i</option>";
			}
			?></select><br/>
			Vrijeme: <input type="text" size="10" name="vrijeme" value="<?=$vrijeme?>"  class="default">
			<input type="submit" value="Registruj"  class="default"><br/><br/>
		
			<input type="radio" name="prisustvo" value="1" CHECKED>Svi prisutni
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="radio" name="prisustvo" value="0">Svi odsutni
			<?
			
			// Kreiraj čas sa kvizom
			if (in_array(5, $tipovi_komponenti)) { // 5 = kvizovi
				?>
				<br>
				Sa kvizom: <select name="kviz"><option value="0">/</option>
				<?
				
				$quizzes = api_call("quiz/course/$predmet/$ag")['results'];
				foreach($quizzes as $quiz) {
					?>
				<option value="<?=$quiz['id']?>"><?=$quiz['name']?></option>
				<?
				}
				
				?>
				</select>
				<?
			}
			
			
			?>
		
		</form>
		</td></tr></table>
		
		<script language="JavaScript">
		var prisutan;
		var oldState;
		var boje = [ "#FFCCCC", "#CCFFCC", "#FFFFCC" ];
		var tekstovi = [ "NE", "DA", "/" ];
		
		function azuriraj_polje(status, student, cas) {
			var celija = document.getElementById("dane-"+student+"-"+cas);
			var tekst = document.getElementById("danetekst-"+student+"-"+cas);
			if (status == -1) {
				celija.style.background = "#FFFFFF";
				celija.style.backgroundImage = "url(static/images/Animated-Hourglass.gif)";
				celija.style.backgroundRepeat = "no-repeat";
				celija.style.backgroundPosition = "center";
				tekst.innerHTML = "";
			} else {
				celija.style.backgroundImage = "";
				celija.style.background = boje[status];
				tekst.innerHTML = tekstovi[status];
			}
		}
	
		// Funkcija koja se poziva klikom na polje u tabeli
		function prisustvo(e, student, cas) {
			var val = document.getElementById("danetekst-"+student+"-"+cas).innerHTML;
			azuriraj_polje(-1, student, cas);
			for (i=0; i<=2; i++)
				if (val == tekstovi[i]) oldState = i;
			
			prisutan = 0;
			var evt = e || window.event;
			if (evt.shiftKey) {
				prisutan = 2;
			} else if (oldState == 1) {
				prisutan = 0;
			} else if (oldState == 0) {
				prisutan = 1;
			}
			
			var att = { "ZClass": { "id" : cas }, "student" : { "id" : student }, "presence" : prisutan };
			
			ajax_api_start(
				"class/"+cas+"/student/"+student,
				"POST",
                att,
				function(foo) {
					azuriraj_polje(prisutan, student, cas);
				},
				function(responseText, status, url) {
					azuriraj_polje(oldState, student, cas);
					var greska = "";
					if (status != 200)
						greska = "Došlo je do greške (status: "+status+"). Molimo kontaktirajte administratora";
					else try {
						var object = JSON.parse(responseText);
						greska = object['message'];
					} catch(e) {
						greska = "Došlo je do greške (nevalidan odgovor). Molimo kontaktirajte administratora";
						console.log("Web servis "+url+" nije vratio validan JSON: "+xhttp.responseText);
						console.log(e);
					}
					alert(greska);
				}
			);
		}
		
		function upozorenje(cas) {
			if (confirm("Da li ste sigurni da želite obrisati čas?")) {
				// _lv_casid osigurava da genform() neće dodati još jedno hidden polje
				document.brisanjecasa._lv_casid.value=cas;
				document.brisanjecasa.submit();
			}
			return false;
		}
		
		</script>
	
		<!-- Pomocna forma za POST brisanje casa -->
		
		<?=genform("POST", "brisanjecasa")?>
		<input type="hidden" name="akcija" value="brisi_cas">
		<input type="hidden" name="_lv_casid" value="">
		</form>
	
		<?
	
	
	} // if (in_array(3, $tipovi_komponenti))


	// Ispis AJAH box-a neposredno iznad tablice grupe
	
	print ajah_box();
	ajax_box();
	
	
	
	
	// ------- TABLICA GRUPE - ZAGLAVLJE
	
	$minw = 0; // minimalna sirina tabele
	$zaglavlje1 = ""; // Prvi red zaglavlja
	$zaglavlje2 = ""; // Drugi red zaglavlja
	
	// Zaglavlje prisustvo
	foreach($cactClasses as $cactId => $c) {
		$brcasova = count($c);
		if ($brcasova == 0) {
			$brcasova = 1;
			$zaglavlje2 .= "<td>&nbsp;</td>";
			$minw += 40;
		}
		
		$zaglavlje1 .= "<td align=\"center\" colspan=\"".($brcasova+1)."\">" . $cactTitles[$cactId] . "</td>\n";
		
		foreach($c as $class) {
			$cas_id = $class['id'];
			list($date, $time) = explode(" ", $class['dateTime']);
			list ($cas_godina,$cas_mjesec,$cas_dan) = explode("-", $date);
			list ($cas_sat,$cas_minuta,$cas_sekunda) = explode(":", $time);
			$zaglavlje2 .= "<td align=\"center\">$cas_dan.$cas_mjesec<br/>$cas_sat:$cas_minuta";
			$zaglavlje2 .= '<br/><a href="javascript:onclick=upozorenje('."'$cas_id'".');"><img src="static/images/16x16/not_ok.png" border="0"></a>';
			$zaglavlje2 .= "</td>\n";
			$minw += 40;
		}
		
		$zaglavlje2 .= "<td>BOD.</td>\n";
	}
	
	// Zaglavlje zadaće
	
	foreach($cactHomeworks as $cactId => $hw) {
		$brzadaca = count($hw);
		if ($brzadaca == 0) continue; // Skip component with no homeworks
		
		$zaglavlje1 .= "<td align=\"center\" colspan=\"$brzadaca\">" . $cactTitles[$cactId] . "</td>\n";
		
		foreach ($hw as $homework) {
			$zaglavlje2 .= "<td width=\"60\" align=\"center\">" . $homework['name'] . "<br /><a href=\"?sta=saradnik/svezadace&grupa=$labgrupa&zadaca=" . $homework['id'] . "\">Download</a></td>\n";
			$minw += 40;
		}
	}
	
	// Zaglavlje fiksne komponente
	foreach($fixedCacts as $cactId => $cact) {
		$zaglavlje1 .= "<td align=\"center\" rowspan=\"2\">" . $cact['name'] . "</td>";
		$minw += 60;
	}
	
	// Zaglavlje ispiti
	if (count($exams) > 0) {
		foreach ($exams as $exam) {
			$zaglavlje2 .= "<td align=\"center\">" . $exam['CourseActivity']['abbrev'] . "<br/> " . date("d.m.", db_timestamp($exam['date'])) . "</td>\n";
			$minw += 40;
		}
		$zaglavlje1 .= "<td align=\"center\" colspan=\"" . count($exams) . "\">Ispiti</td>\n";
	}
	
	$minw += 70; // ukupno
	$minw += 45; // broj indexa
	$minw += 100; // ime i prezime
	$minw += 40; // komentar
	$minw += 40; // bodovi prisustvo
	
	?>
	<table cellspacing="0" cellpadding="2" border="1" <? if ($minw>800) print "width=\"$minw\""; ?>>
		<tr>
			<td rowspan="2" align="center" valign="center">Ime i prezime</td>
			<td rowspan="2" align="center" valign="center">Broj indexa</td>
			<td rowspan="2" align="center" valign="center">Ko-<br/>men-<br/>tar</td>
			<?=$zaglavlje1?>
			<td align="center" valign="center" rowspan="2">&nbsp;&nbsp;<b>UKUPNO</b>&nbsp;&nbsp;</td>
			<td rowspan="2" align="center">Konačna<br/>ocjena</td>
		</tr>
		<tr>
			<?=$zaglavlje2?>
		</tr>
	<?
	
	
	// Ikone i statusi za zadaće
	$stat_icon = array("bug", "wait_icon", "copy", "bug", "view", "ok");
	$stat_tekst = array("Bug u programu", "Automatsko testiranje u toku", "Zadaća prepisana", "Bug u programu", "Potrebno pregledati", "Zadaća OK");
	
	
	
	// ------- GLAVNA PETLJA ZA ISPIS TABELE STUDENATA
	
	$redni_broj=0;
	foreach ($imeprezime as $studentId => $stud_imepr) {
		$redni_broj++;
		?>
		<tr>
			<td id="student_<?=$studentId?>"><?=$redni_broj?>.&nbsp;<a href="index.php?sta=saradnik/student&student=<?=$studentId?>&predmet=<?=$predmet?>&ag=<?=$ag?>"><?=$stud_imepr?></a></td>
			<td><?=$brind[$studentId]?></td>
			<td align="center"><a href="javascript:firefoxopen('index.php?sta=saradnik/komentar&student=<?=$studentId?>&labgrupa=<?=$labgrupa?>','blah3','width=350,height=320,status=0,toolbar=0,resizable=1,location=0,menubar=0,scrollbars=1');"><img src="static/images/16x16/comment_blue.png" border="0" width="16" height="16" alt="Komentar na rad studenta" title="Komentar na rad studenta"></a></td>
		<?

		$prisustvo_ispis=$zadace_ispis=$ispiti_ispis=$fiksne_ispis="";
		

		// PRISUSTVO - ISPIS
		
		foreach($cactClasses as $cactId => $_classes) {
			if (count($_classes) == 0)
				$prisustvo_ispis .= "<td>&nbsp;</td>";
			
			foreach($_classes as $classId => $class) {
				$uspjeh_na_kvizu = "";
				if ($class['Quiz']['id'] > 0) {
					$quizId = $class['Quiz']['id'];
					if (array_key_exists($studentId, $quizResultCache[$quizId])) {
						if ($quizResultCache[$quizId][$studentId])
							$uspjeh_na_kvizu = '<img src="static/images/16x16/ok.png" width="8" height="8">';
						else
							$uspjeh_na_kvizu = '<img src="static/images/16x16/not_ok.png" width="8" height="8">';
					}
				}
				
				if (!array_key_exists($studentId, $presenceCache[$classId]))
					$presence = 2;
				else
					$presence = $presenceCache[$classId][$studentId];
				
				if ($presence == 1) {
					$prisustvo_ispis .= "<td bgcolor=\"#CCFFCC\" align=\"center\" id=\"dane-" . $studentId . "-" . $classId . "\" onclick=\"javascript:prisustvo(event," . $studentId . "," . $classId . ")\"><span id=\"danetekst-" . $studentId . "-" . $classId . "\">DA</span> $uspjeh_na_kvizu</td>";
				} else if ($presence == 0) {
					$prisustvo_ispis .= "<td bgcolor=\"#FFCCCC\" align=\"center\" id=\"dane-" . $studentId . "-" . $classId . "\" onclick=\"javascript:prisustvo(event," . $studentId . "," . $classId . ")\"><span id=\"danetekst-" . $studentId . "-" . $classId . "\">NE</span> $uspjeh_na_kvizu</td>";
				} else {
					$prisustvo_ispis .= "<td bgcolor=\"#FFFFCC\" align=\"center\" id=\"dane-" . $studentId . "-" . $classId . "\" onclick=\"javascript:prisustvo(event," . $studentId . "," . $classId . ")\"><div id=\"danetekst-" . $studentId . "-" . $classId . "\"> / </div> $uspjeh_na_kvizu</td>";
				}
			}
			
			// Total score
			$prisustvo_ispis .= "<td>" . $cactScores[$cactId][$studentId] . "</td>\n";
		}


		// ZADACE - ISPIS
		
		foreach($cactHomeworks as $cactId => $_homeworks) {
			foreach($_homeworks as $homeworkId => $homework) {
				$zadace_ispis .= "<td> \n";
				for ($i=1; $i<=$homework['nrAssignments']; $i++) {
					$status = $homeworkStatus[$homeworkId][$i][$studentId];
					if ($status == 0) { // Zadatak nije poslan
						if ($kreiranje>0) {
							$zadace_ispis .= "<a href=\"#\" onclick=\"javascript:openzadaca(event, '".$studentId."', '".$homeworkId."', '".$i."'); return false;\"><img src=\"static/images/16x16/create_new.png\" width=\"16\" height=\"16\" border=\"0\" align=\"center\" title=\"".$studentId.",".$homeworkId.",".$i."\" alt=\"".$studentId.",".$homeworkId.",".$i."\"></a>&nbsp;";
							//if ($i<$zad_brz_array[$homeworkId]) $zadace_ispis .= "<br/>";
						}
					} else {
						$icon = $stat_icon[$status];
						$title = $stat_tekst[$status];
						$zb = $homeworkScore[$homeworkId][$i][$studentId];
						$zadace_ispis .= "<a href=\"#\" onclick=\"javascript:openzadaca(event, '".$studentId."', '".$homeworkId."', '".$i."'); return false;\"><img src=\"static/images/16x16/".$icon.".png\" width=\"16\" height=\"16\" border=\"0\" align=\"center\" title=\"".$studentId.",".$homeworkId.",".$i."\" alt=\"".$studentId.",".$homeworkId.",".$i."\">&nbsp;".$zb."</a>";
					}
				}
				$zadace_ispis .= "&nbsp;</td>\n";
			}
		}
		

		// FIKSNE KOMPONENTE - ISPIS
		foreach ($fixedCacts as $cactId => $cact) {
			if (array_key_exists($studentId, $cactScores[$cactId])) {
				$fiksne_ispis .= "<td id=\"fiksna-$studentId-$predmet-$cactId-$ag\" ondblclick=\"coolboxopen(this)\">" . $cactScores[$cactId][$studentId] . "</td>\n";
			} else {
				$fiksne_ispis .= "<td id=\"fiksna-$studentId-$predmet-$cactId-$ag\" ondblclick=\"coolboxopen(this)\">/</td>\n";
			}
		}


		// ISPITI - ISPIS

		foreach($exams as $exam) {
			$examId = $exam['id'];
			if (array_key_exists($exam['id'], $examResults) && array_key_exists($studentId, $examResults[$exam['id']])) {
				$ispiti_ispis .= "<td id=\"ispit-$studentId-$examId\" ondblclick=\"coolboxopen(this)\">" . $examResults[$examId][$studentId] . "</td>\n";
			} else {
				$ispiti_ispis .= "<td id=\"ispit-$studentId-$examId\" ondblclick=\"coolboxopen(this)\">/</td>\n";
			}
		}


		// KONACNA OCJENA - ISPIS
		$currentMember = [];
		foreach($group['members'] as $member) {
			if ($member['student']['id'] == $studentId) {
				$currentMember = $member;
				break;
			}
		}
		if ($course['gradeType'] == 1 || $course['gradeType'] == 2) {
			if ($course['gradeType'] == 1) {
				$ocjena_value = 11;
				$ocjena_text = "ispunio/la uslove";
			} else {
				$ocjena_value = 12;
				$ocjena_text = "uspješno odbranio";
			}
			if ($currentMember['grade']) $ispunio_uslove = "CHECKED"; else $ispunio_uslove = "";
			if ($privilegija != "super-asistent")
				$ko_ispis = "<td align=\"center\" id=\"ko-$studentId-$predmet-$ag\"><input type=\"checkbox\" id=\"ocjena$studentId\" onchange=\"ispunio_uslove(this,$ocjena_value)\" $ispunio_uslove></td>";
			else
				$ko_ispis = "<td align=\"center\" id=\"ko-$studentId-$predmet-$ag\">$ocjena_text</td>";
			$ko_ispis .= "\n<SCRIPT>origval['ko-$studentId-$predmet-$ag'] = \"" . $currentMember['grade'] . "\";</SCRIPT>\n";
		}
		
		else if ($privilegija == "super-asistent") {
			if ($currentMember['grade']) {
				$ko_ispis = "<td align=\"center\" id=\"ko-$studentId-$predmet-$ag\">" . $currentMember['grade'] . "</td>\n";
			} else {
				$ko_ispis = "<td align=\"center\" id=\"ko-$studentId-$predmet-$ag\">/</td>\n";
			}
		} else {
			if ($currentMember['grade']) {
				$ko_ispis = "<td align=\"center\" id=\"ko-$studentId-$predmet-$ag\" ondblclick=\"coolboxopen(this)\">" . $currentMember['grade'] . "</td>\n";
			} else {
				$ko_ispis = "<td align=\"center\" id=\"ko-$studentId-$predmet-$ag\" ondblclick=\"coolboxopen(this)\">/</td>\n";
			}
		}

		?>
			<?=$prisustvo_ispis?>
			<?=$zadace_ispis?>
			<?=$fiksne_ispis?>
			<?=$ispiti_ispis?>
			<td align="center"><? print $currentMember['totalScore'];
		/*	Procenat zauzima previše prostora po horizontali, a nije toliko interesantan
			if ($mogucih_bodova!=0 && $mogucih_bodova!=100) {
		//		?> (<?=procenat($bodova,$mogucih_bodova)?>)<?
			} */
			?></td>
			<?=$ko_ispis?>
		</tr><?
	}

	?>
	</table>
	
	<p><?
		if ($kreiranje>0) {
			$k=str_replace("&amp;kreiranje=1","",genuri());
	?><a href="<?=$k?>">Sakrij dugmad za kreiranje zadataka</a><?
		} else {
	?><a href="<?=genuri()?>&amp;kreiranje=1">Prikaži dugmad za kreiranje zadataka</a><?
		}
	?> * <a href="?sta=saradnik/grupa&amp;id=<?=$labgrupa?>">Refresh</a></p>
	
	<?
	if ($privilegija=="nastavnik") {
		?><p>Vi ste administrator ovog predmeta.</p><?
	} else if ($privilegija=="super_asistent") {
		?><p>Vi ste super-asistent ovog predmeta.</p><?
	}
	?>
	<p>&nbsp;</p>
	<?
	



}

?>
