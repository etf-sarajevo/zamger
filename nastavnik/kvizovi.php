<?

// NASTAVNIK/KVIZOVI - kreiranje i administracija kvizova



function nastavnik_kvizovi() {

	global $_api_http_code;
	
	require_once("lib/formgen.php"); // db_dropdown, db_form, db_list
	require_once("lib/utility.php"); // procenat
	require_once("lib/legacy.php"); // mb_substr
	
	
	// Parametri
	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);
	
	$course = api_call("course/$predmet/$ag");
	
	// Naziv predmeta
	$predmet_naziv = $course['courseName'];
	
	// Da li korisnik ima pravo ući u modul?
	
	if ($_api_http_code == "403") {
		zamgerlog("nastavnik/kvizovi privilegije (predmet pp$predmet)",3);
		zamgerlog2("nije nastavnik na predmetu", $predmet, $ag);
		biguglyerror("Nemate pravo pristupa ovoj opciji");
		return;
	}
	
	
	
	?>
	
	<p>&nbsp;</p>
	
	<p><h3><?=$predmet_naziv?> - Kvizovi</h3></p>
	
	<?
	
	$allQuizzes = api_call("quiz/course/$predmet/$ag")["results"];
	usort($allQuizzes, function($q1, $q2) {
		return strnatcasecmp($q1['name'], $q2['name']);
	} );
	
	
	
	// Akcija - editovanje pitanja
	
	if ($_REQUEST['akcija'] == "pitanja") {
		$quizId = intval($_REQUEST['kviz']);
		$quiz = api_call("quiz/$quizId", [ "questions" => true ], "GET", false, true, false );
		
		// Fix answers: If answer text is "true" or "false", API will return boolean true and false (due to Util::fix_data_types),
		// which PHP will convert into strings "1" and "" respectively
		foreach($quiz->questions as &$question) {
			foreach($question->answers as &$answer) {
				if ($answer->text === true) $answer->text = "true";
				if ($answer->text === false) $answer->text = "false";
			}
		}
		
		if ($_api_http_code != "200") {
			niceerror("Nepostojeći kviz $quizId ($_api_http_code): " . $quiz['message']);
			zamgerlog("editovanje pitanja: nepostojeci kviz $quizId", 3);
			zamgerlog2("nepostojeci kviz (editovanje pitanja)", $quizId);
			return;
		}
		$naziv_kviza = $quiz->name;
	
		// Subakcije
		if ($_REQUEST['subakcija'] == "potvrda_novo" && check_csrf_token()) {
			$tekst = $_REQUEST['tekst'];
			$bodova = floatval(str_replace(',', '.', $_REQUEST['bodova']));
			if ($_REQUEST['vidljivo']) $vidljivo=true; else $vidljivo=false;
			$tip = $_REQUEST['tip'];
	
			$question = array_to_object( [ "id" => 0, "Quiz" => [ "id" => $quizId ], "type" => $tip, "text" => $tekst, "score" => $bodova, "visible" => $vidljivo ] );
			$quiz->questions[] = $question;
			
			$result = api_call("quiz/$quizId", $quiz, "PUT");
			
			if ($_api_http_code == "201") {
				foreach($result['questions'] as $question) {
					if ($question['text'] == $tekst)
						$pitanje = $question['id'];
				}
				nicemessage("Pitanje uspješno dodano");
				zamgerlog2("dodano pitanje na kviz", $pitanje);
				?>
				<script language="JavaScript">
				setTimeout(function() {
				    location.href='?sta=nastavnik/kvizovi&predmet=<?=$predmet?>&ag=<?=$ag?>&kviz=<?=$quizId?>&akcija=pitanja&subakcija=izmijeni&pitanje=<?=$pitanje?>';}, 1000);
				</script>
				<?
			} else {
				niceerror("Neuspješno dodavanje pitanja ($_api_http_code): " . $result['message']);
			}
			return;
		}
	
		if ($_REQUEST['subakcija'] == "potvrda_izmjene" && check_csrf_token()) {
			$pitanje = intval($_REQUEST['pitanje']);
			$tekst = $_REQUEST['tekst'];
			$bodova = floatval(str_replace(',', '.', $_REQUEST['bodova']));
			if ($_REQUEST['vidljivo']) $vidljivo=true; else $vidljivo=false;
			$tip = $_REQUEST['tip'];
			
			foreach($quiz->questions as &$question) {
				if ($question->id == $pitanje) {
					$question->text = $tekst;
					$question->score = $bodova;
					$question->visible = $vidljivo;
					$question->type = $tip;
				}
			}
			
			$result = api_call("quiz/$quizId", $quiz, "PUT");
			
			if ($_api_http_code == "201") {
				nicemessage("Pitanje uspješno izmijenjeno");
				zamgerlog2("izmijenjeno pitanje na kvizu", $pitanje);
				?>
				<script language="JavaScript">
                    setTimeout(function() {
                        location.href='?sta=nastavnik/kvizovi&predmet=<?=$predmet?>&ag=<?=$ag?>&kviz=<?=$quizId?>&akcija=pitanja&subakcija=izmijeni&pitanje=<?=$pitanje?>';
						}, 1000);
				</script>
				<?
			} else {
				niceerror("Neuspješno izmjena pitanja ($_api_http_code): " . $result['message']);
			}
			
			return;
		}
		
		if ($_REQUEST['subakcija'] == "obrisi") { // brisanje pitanja - ovdje ce nam trebati potvrda!
			$pitanje = intval($_REQUEST['pitanje']);
			
			$found = false;
			foreach($quiz->questions as $i => $question) {
				if ($question->id == $pitanje) {
					$found = $i;
				}
			}
			unset($quiz->questions[$found]);
			
			$result = api_call("quiz/$quizId", $quiz, "PUT");
			
			if ($_api_http_code == "201") {
				nicemessage("Pitanje uspješno obrisano");
				zamgerlog2("obrisano pitanje sa kviza", $quizId, $pitanje);
				?>
				<script language="JavaScript">
                    setTimeout(function() {
                        location.href='?sta=nastavnik/kvizovi&predmet=<?=$predmet?>&ag=<?=$ag?>&kviz=<?=$quizId?>&akcija=pitanja&subakcija=izmijeni&pitanje=<?=$pitanje?>';
                    }, 1000);
				</script>
				<?
			} else {
				niceerror("Neuspješno brisanje pitanja ($_api_http_code): " . $result['message']);
			}
			return;
		}
	
		if ($_REQUEST['subakcija'] == "dodaj_odgovor" && check_csrf_token()) {
			$pitanje = intval($_REQUEST['pitanje']);
			$tekst = $_REQUEST['tekst'];
			if ($_REQUEST['tacan']) $tacan=true; else $tacan=false;
			
			// Add "visible" to form?
			$answer = array_to_object( [ "id" => 0, "QuizQuestion" => [ "id" => $pitanje ], "text" => $tekst, "correct" => $tacan, "visible" => true ] );
			
			foreach($quiz->questions as &$question) {
				if ($question->id == $pitanje)
					$question->answers[] = $answer;
			}
			
			print "<textarea>" . json_encode($quiz, JSON_PRETTY_PRINT);
			$result = api_call("quiz/$quizId", $quiz, "PUT");
			print "</textarea>";
			
			if ($_api_http_code == "201") {
				nicemessage("Odgovor uspješno dodan");
				zamgerlog2("dodan odgovor na pitanje", db_insert_id());
				?>
				<script language="JavaScript">
                    setTimeout(function() {
                        location.href='?sta=nastavnik/kvizovi&predmet=<?=$predmet?>&ag=<?=$ag?>&kviz=<?=$quizId?>&akcija=pitanja&subakcija=izmijeni&pitanje=<?=$pitanje?>';}, 1000);
				</script>
				<?
			} else {
				niceerror("Neuspješno dodavanje odgovora ($_api_http_code): " . $result['message']);
			}
			return;
		}
	
		if ($_REQUEST['subakcija'] == "obrisi_odgovor") { // && check_csrf_token()) {
			$odgovor = intval($_REQUEST['odgovor']);
			$pitanje = 0;
			
			foreach($quiz->questions as &$question) {
				$found = false;
				foreach($question->answers as $i => $answer) {
					if ($answer->id == $odgovor)
						$found = $i;
				}
				if ($found) {
					$pitanje = $question->id;
					unset($question->answers[$found]);
				}
			}
			
			$result = api_call("quiz/$quizId", $quiz, "PUT");
			
			if ($_api_http_code == "201") {
				nicemessage("Odgovor uspješno obrisan");
				?>
				<script language="JavaScript">
                    setTimeout(function() {
                        location.href='?sta=nastavnik/kvizovi&predmet=<?=$predmet?>&ag=<?=$ag?>&kviz=<?=$quizId?>&akcija=pitanja&subakcija=izmijeni&pitanje=<?=$pitanje?>';}, 1000);
				</script>
				<?
			} else {
				niceerror("Neuspješno brisanje odgovora ($_api_http_code): " . $result['message']);
			}
			return;
		}
		
		if ($_REQUEST['subakcija'] == "toggle_tacnost") { // && check_csrf_token()) {
			$odgovor = intval($_REQUEST['odgovor']);
			
			foreach($quiz->questions as &$question) {
				foreach($question->answers as &$answer) {
					if ($answer->id == $odgovor) {
						$pitanje = $question->id;
						$answer->correct = !$answer->correct;
					}
				}
			}
			
			$result = api_call("quiz/$quizId", $quiz, "PUT");
			
			if ($_api_http_code == "201") {
				nicemessage("Odgovor proglašen za (ne)tačan");
				zamgerlog2("odgovor proglasen za (ne)tacan", $odgovor, $tacan);
				?>
				<script language="JavaScript">
                    setTimeout(function() {
                        location.href='?sta=nastavnik/kvizovi&predmet=<?=$predmet?>&ag=<?=$ag?>&kviz=<?=$quizId?>&akcija=pitanja&subakcija=izmijeni&pitanje=<?=$pitanje?>';}, 1000);
				</script>
				<?
			} else {
				niceerror("Neuspješan toggle odgovora ($_api_http_code): " . $result['message']);
			}
			return;
		}
	
		if ($_REQUEST['subakcija'] == "kopiraj_pitanja" && check_csrf_token()) {
			$drugi_kviz = intval($_REQUEST['drugi_kviz']);
			
			$result = api_call("quiz/$quizId/copy", [ "quizId" => $drugi_kviz ], "POST");
			
			if ($_api_http_code == "201") {
				nicemessage("Prekopirana pitanja sa kviza");
				zamgerlog2("prekopirana pitanja sa kviza", $quizId, $drugi_kviz);
				?>
				<script language="JavaScript">
                    setTimeout(function() {
                        location.href='?sta=nastavnik/kvizovi&predmet=<?=$predmet?>&ag=<?=$ag?>&kviz=<?=$quizId?>&akcija=pitanja';}, 1000);
				</script>
				<?
			} else {
				niceerror("Neuspješno kopiranje pitanja sa kviza $drugi_kviz ($_api_http_code): " . $result['message']);
			}
			return;
		}
	
		?>
		<h3>Izmjena pitanja za kviz "<?=$naziv_kviza?>"</h3>
		<a href="?sta=nastavnik/kvizovi&predmet=<?=$predmet?>&ag=<?=$ag?>&_lv_nav_id=<?=$quizId?>">Nazad na podešavanje parametara kviza</a><br><br>
		<table border="0" cellspacing="1" cellpadding="2">
		<tr bgcolor="#999999">
			<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;color:white;">R.br.</font></td>
			<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;color:white;">Tekst pitanja</font></td>
			<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;color:white;">Odgovori</font></td>
			<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;color:white;">Bodova</font></td>
			<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;color:white;">Vidljivo?</font></td>
			<td>&nbsp;</td>
		</tr>
		<?
		
		foreach($quiz->questions as $question) {
			$odgovori = "";
			if (empty($question->answers))
				$odgovori = "<font color=\"red\">Nema ponuđenih odgovora</font>";
			$broj_tacnih = 0;
			foreach($question->answers as $answer) {
				$odgovori .= "'" . $answer->text . "'";
				if ($answer->correct) { $odgovori .= " (*)"; $broj_tacnih++; }
				$odgovori .= ", ";
			}
			if (!empty($question->answers) && $broj_tacnih==0) {
				$odgovori = "<font color=\"red\">Nije ponuđen tačan odgovor</font><br>\n".$odgovori;
			}
			else if (!empty($question->answers) && $question->type=='mcma' && $broj_tacnih==1) {
				$odgovori = "<font color=\"red\">Ponuđen je samo jedan tačan odgovor</font><br>\n".$odgovori;
			}
			
			$vidljivo = "NE";
			if ($question->visible) $vidljivo = "DA";
			
			$rbr++;
			?>
			<tr>
				<td><?=$rbr?></td>
				<td><?=$question->text?></td>
				<td><?=$odgovori?></td>
				<td><?=$question->score?></td>
				<td><?=$vidljivo?></td>
				<td><a href="?sta=nastavnik/kvizovi&predmet=<?=$predmet?>&ag=<?=$ag?>&kviz=<?=$quizId?>&akcija=pitanja&subakcija=obrisi&pitanje=<?=$question->id?>">Obriši</a> *
					<a href="?sta=nastavnik/kvizovi&predmet=<?=$predmet?>&ag=<?=$ag?>&kviz=<?=$quizId?>&akcija=pitanja&subakcija=izmijeni&pitanje=<?=$question->id?>">Izmijeni</a></td>
			</tr>
			<?
		}
		
		?>
		</table>
		<br><br>
		<?
		if (empty($quiz->questions)) {
			print genform("POST");
			?>
			<input type="hidden" name="subakcija" value="kopiraj_pitanja">
			<p>Kopiraj pitanja sa kviza: <select name="drugi_kviz"><?
				foreach($allQuizzes as $_quiz) {
					?><option value="<?=$_quiz['id']?>"><?=$_quiz['name']?></option><?
				}
			?>
					</select>
			<input type="submit" value=" Kreni ">
			</p></form><?
		}
		
		$found = false;
		if ($_REQUEST['subakcija']=="izmijeni") {
			$pitanje = intval($_REQUEST['pitanje']);
			foreach ($quiz->questions as $question) {
				if ($question->id == $pitanje) {
					$tip = $question->type;
					$tekst = $question->text;
					$bodova = $question->score;
					if ($question->visible) $vidljivo = "CHECKED"; else $vidljivo = "";
					$answers = $question->answers;
					$found = true;
				}
			}
		}
		if ($found) {
			?>
			<a href="?sta=nastavnik/kvizovi&predmet=<?=$predmet?>&ag=<?=$ag?>&kviz=<?=$quizId?>&akcija=pitanja">Dodaj novo pitanje</a><br><br>
			
			<a name="izmjena"></a>
			<b>Izmjena pitanja</b><br>
			<?
	
			$subakcija="potvrda_izmjene";
		} else {
			?>
			<b>Dodajte novo pitanje</b><br>
			<?
			
			$tekst = $vidljiv = "";
			$bodova = $pitanje = 0;
			$tip = "mcsa";
			$answers = [];
			$subakcija="potvrda_novo";
		}
		unset($_REQUEST['subakcija']);
		unset($_GET['subakcija']);
		
		?>
		<?=genform("POST");?>
		<input type="hidden" name="subakcija" value="<?=$subakcija?>">
		<input type="hidden" name="pitanje" value="<?=$pitanje?>">
		<table border="0">
			<tr><td>Tekst pitanja:</td><td><input type="text" size="50" name="tekst" value="<?=$tekst?>"></td></tr>
			<tr><td>Bodova:</td><td><input type="text" size="5" name="bodova" value="<?=$bodova?>"></td></tr>
			<tr><td>Tip pitanja:</td><td>
				<select name="tip">
					<option value="mcsa" <? if ($tip=="mcsa") print "SELECTED" ?>>MCSA</option>
					<option value="mcma" <? if ($tip=="mcma") print "SELECTED" ?>>MCMA</option>
					<option value="tekstualno" <? if ($tip=="tekstualno") print "SELECTED" ?>>Tekstualno</option>
				</select>
				<a href="#" onclick="javascript:window.open('legenda-pitanja.html','blah6','width=320,height=300');">Legenda tipova pitanja</a>
			</td></tr>
			<tr><td align="right"><input type="checkbox" name="vidljivo" value="1" <?=$vidljivo?>></td><td>Pitanje vidljivo</td></tr>
		</table>
		<br>Ponuđeni odgovori:<br>
		<ul>
		<?
		
		if (empty($answers)) {
			?><li>Do sada nije unesen nijedan odgovor</li><?
		}
		
		foreach($answers as $answer) {
			?>
				<li>
					<? if (!$answer->visible) print "<font color=\"#AAAAAA\">"; ?>
					<?=$answer->text?>
					<? if ($answer->correct) { print " (TAČAN)"; $toggle_link = "Proglasi za netačan"; }
					else { $toggle_link = "Proglasi za tačan"; } ?>
					<? if (!$answer->visible) print "</font> - nevidljiv"; ?>
					- <a href="?sta=nastavnik/kvizovi&predmet=<?=$predmet?>&ag=<?=$ag?>&kviz=<?=$quizId?>&akcija=pitanja&subakcija=obrisi_odgovor&odgovor=<?=$answer->id?>">Obriši</a>
					- <a href="?sta=nastavnik/kvizovi&predmet=<?=$predmet?>&ag=<?=$ag?>&kviz=<?=$quizId?>&akcija=pitanja&subakcija=toggle_tacnost&odgovor=<?=$answer->id?>"><?=$toggle_link?></a>
				</li>
			<?
		}
		?>
		</ul>
		<input type="submit" value="Promjena pitanja"><br>
		</form>
		<br>
		Dodajte odgovor na ovo pitanje:<br>
		<?=genform("POST");?>
		<input type="hidden" name="subakcija" value="dodaj_odgovor">
		<input type="hidden" name="pitanje" value="<?=$pitanje?>">
		Tekst odgovora: <input type="text" name="tekst" size="50"><br>
		<input type="checkbox" name="tacan" value="1"> Tačan<br>
		<input type="submit" value="Dodaj"><br>
		</form>
		<?
	
		return;
	}
	
	
	
	// Akcija - statistički pregled rezultata kviza
	
	if ($_REQUEST['akcija'] == "rezultati") {
		$quizId = intval($_REQUEST['kviz']);
		$quiz = api_call("quiz/$quizId", [ "stats" => true, "questions" => true ]);
		if ($_api_http_code != "200") {
			zamgerlog("editovanje pitanja: nepostojeci kviz $quizId", 3);
			zamgerlog2("nepostojeci kviz (editovanje pitanja)", $quizId);
			niceerror("Neuspješan pristup kvizu ($_api_http_code): " . $quiz['message']);
		}
	
		?>
		<p>Popunilo kviz: <b><?=$quiz['stats']['finished']?></b> studenata<br />
		Nisu dovršili popunjavanje kviza: <b><?=$quiz['stats']['unfinished']?></b> studenata<br />
		Ostvarilo prolazne bodove: <b><?=$quiz['stats']['passed']?></b> studenata (<?=procenat($quiz['stats']['passed'], $quiz['stats']['finished'])?>)</p>
		
		<h3><?=$quiz['name']?></h3>
		<h4>Distribucija bodova</h4>
		<div id="grafik">
			<div style="width:300px;height:200px;margin:5px;">
				<?
				ksort($quiz['stats']['points']);
				$max_broj = 0;
				foreach ($quiz['stats']['points'] as $broj)
					if ($broj > $max_broj)
						$max_broj = $broj;
				foreach ($quiz['stats']['points'] as $bod => $broj) {
					if($broj==0) $broj_pixela_print =170;
					else {
						$broj_pixela = ($broj/$max_broj)*200;
						$broj_pixela_print = intval(200-$broj_pixela);
					}
					if ($bod < $quiz['passPoints']) $boja="red"; else $boja="green";
					?>
					<div style="width:45px; height:200px; background:<?=$boja?>;margin-left:5px;float:left;">
						<div style="width:45px;height:<?=$broj_pixela_print?>px;background:white;">&nbsp;</div>
						<span style="color:white;font-size: 25px; text-align: center; ">&nbsp;<?=$bod?></span>
					</div>
					<?
				}
			?>
			</div>
			<div style="width:300px;height:50px;margin:5px;">
				<?
				foreach ($quiz['stats']['points'] as $bod => $broj) {
					?>
					<div style="width:45px; margin-left:5px; text-align: center; float:left; ">
						<?=$broj?> (<?=procenat($broj, $quiz['stats']['finished'])?>)
					</div>
					<?
				}
				?>
			</div>
		</div>
		<?
		
		// Statistika pitanja
		
		?>
		<h3>Statistika pitanja</h3>
		<table border="1" style="border-collapse:collapse">
		<tr><th>Pitanje</th><th>Uk. odgovora</th><th>Tačnih</th></tr>
		<?
		
		usort($quiz['questions'], function($qq1, $qq2) { return $qq1['id'] > $qq2['id']; } );
		
		foreach($quiz['questions'] as $question) {
			if (strlen($question['text']) > 60)
				$skr_pitanje = mb_substr($question['text'],0,50)."...";
			else
				$skr_pitanje = $question['text'];
			$odgovora = $r640[2];
			$tacnih = $r640[3];
			?>
			<tr>
				<td title="<?=$question['text']?>">
					<a href="?sta=nastavnik/kvizovi&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>&amp;kviz=<?=$quizId?>&amp;akcija=pitanja&amp;subakcija=izmijeni&amp;pitanje=<?=$question['id']?>#izmjena"><?=$skr_pitanje?></a></td>
				<td><?=$question['totalTakes']?></td>
				<td><?=$question['correctTakes']?> (<?=procenat($question['correctTakes'], $question['totalTakes'])?>)</td>
			</tr>
			<?
			
		}
	
		?>
		</table>
		<?
	
		return;
	}
	
	
	// Kopiranje kvizova sa prošlogodišnjeg predmeta
	
	if ($_REQUEST['akcija'] === "prosla_godina" && strlen($_POST['nazad'])<1) {
		$old_ag = $ag-1; // Ovo je po definiciji prošla godina
		$greska = false;
		
		if (!$greska && $_REQUEST['potvrda'] === "potvrdjeno" && check_csrf_token()) {
			$result = api_call("quiz/course/$predmet/$ag/copy", [ "srcCourse" => $predmet, "srcYear" => $old_ag], "POST");
			if ($_api_http_code == "201") {
				nicemessage("Kopiranje završeno!");
			} else {
				niceerror("Neuspješno kopiranje kvizova ($_api_http_code): " . $result['message']);
			}
			?>
			<a href="?sta=nastavnik/kvizovi&predmet=<?=$predmet?>&ag=<?=$ag?>">Povratak na stranicu kvizova</a>
			<?
			return;
		}
		
		else if (!$greska) {
			$oldQuizzes = api_call("quiz/course/$predmet/$old_ag", [ "resolve" => "AcademicYear" ])["results"];
			usort($oldQuizzes, function($q1, $q2) {
				return strnatcasecmp($q1['name'], $q2['name']);
			});
			
			$oldYear = $oldQuizzes[0]['AcademicYear']['name'];
			
			nicemessage("Kopiram sljedeće kvizove iz akademske $oldYear. godine.");
			print "\n<ul>\n";
			foreach($oldQuizzes as $_quiz) {
				print "<li>" . $_quiz['name'] . "</li>\n";
			}
			print "</ul>\n";
			print genform("POST");
			?>
			<input type="hidden" name="potvrda" value="potvrdjeno">
			<p>Da li ste sigurni?</p>
			<p><input type="submit" name="nazad" value=" Nazad "> <input type="submit" value=" Potvrda"></p>
			</form>
			<?
		}
		return;
	}
	
	
	// Korektno brisanje kviza
	
	if ($_REQUEST['delete']) {
		$quizId = intval($_REQUEST['column_id']);
		$result = api_call("quiz/$quizId", [], "DELETE");
		if ($_api_http_code == "204") {
			zamgerlog2("obrisan kviz", $predmet, $ag, $quizId);
			$allQuizzes = api_call("quiz/course/$predmet/$ag")["results"];
			usort($allQuizzes, function($q1, $q2) {
				return strnatcasecmp($q1['name'], $q2['name']);
			} );
			
		} else {
			niceerror("Neuspješno brisanje kviza ($_api_http_code): " . $result['message']);
		}
	}
	
	
	// Provjeravamo da li je raspon dobro unesen
	
	if (($_REQUEST['action'] == "edit" || $_REQUEST['action'] == "add") && !$_REQUEST['delete']) {
		$ip_adresa_losa = false;
		
		$active = false;
		if ($_POST['active']) $active = true;
		$quiz = array_to_object( [ "id" => 0, "name" => $_POST['naziv'], "CourseUnit" => [ "id" => $predmet ], "AcademicYear" => [ "id" => $ag ], "Group" => [ "id" => intval($_POST['labgrupa']) ], "timeBegin" => $_POST['pocDate'] . " " . $_POST['pocTime'], "timeEnd" => $_POST['krajDate'] . " " . $_POST['krajTime'], "active" => $active, "ipAddressRanges" => $_POST['ip_adrese'], "passPoints" => floatval($_POST['prolaz_bodova']), "nrQuestions" => intval($_POST['broj_pitanja']), "duration" => intval($_POST['trajanje_kviza']) ] );
		
		// Add/edit akcije su implementirane u db_form
		if ($_REQUEST['action'] == "edit") {
			$quizId = intval($_REQUEST['column_id']);
			$quiz->id = $quizId;
			
			$result = api_call("quiz/$quizId", $quiz, "PUT");
			if ($_api_http_code == "201") {
				zamgerlog("izmijenjen kviz $quizId (pp$predmet)", 2);
				zamgerlog2("izmijenjen kviz", $quizId);
				$allQuizzes = api_call("quiz/course/$predmet/$ag")["results"];
				usort($allQuizzes, function($q1, $q2) {
					return strnatcasecmp($q1['name'], $q2['name']);
				} );
			} else {
				niceerror("Neuspješna izmjena kviza ($_api_http_code): " . $result['message']);
			}
		} else {
			$quiz = api_call("quiz/course/$predmet/$ag", $quiz, "POST");
			$quizId = $quiz->id;
			if ($_api_http_code == "201") {
				zamgerlog("dodan novi kviz $quizId (pp$predmet)", 2);
				zamgerlog2("dodan kviz", $quizId);
				$allQuizzes = api_call("quiz/course/$predmet/$ag")["results"];
				usort($allQuizzes, function($q1, $q2) {
					return strnatcasecmp($q1['name'], $q2['name']);
				} );
			} else {
				niceerror("Neuspješno dodavnaje kviza ($_api_http_code): " . $quiz['message']);
			}
			
		}
	
		$ip_adrese = trim($_REQUEST['_lv_column_ip_adrese']);
	
		if (!empty($ip_adrese)) {
			foreach (explode(",", $ip_adrese) as $blok) {
				if (strstr($blok, "/")) { // blok adresa u CIDR formatu
					list ($baza, $maska) = explode("/", $blok);
					if ($baza != long2ip(ip2long($baza))) { $ip_adresa_losa = true; break; }
					if ($maska != intval($maska)) { $ip_adresa_losa = true; break; }
					if ($maska<1 || $maska>32) { $ip_adresa_losa = true; break; }
				}
				else if (strstr($blok, "-")) { // raspon adresa sa crticom
					list ($pocetak, $kraj) = explode("-", $blok);
					if ($pocetak != long2ip(ip2long($pocetak))) { $ip_adresa_losa = true; break; }
					if ($kraj != long2ip(ip2long($kraj))) { $ip_adresa_losa = true; break; }
				}
				else { // pojedinačna adresa
					if ($blok != long2ip(ip2long($blok))) { $ip_adresa_losa = true; break; }
				}
			}
	
			// Vraćamo se na editovanje lošeg kviza
			if ($ip_adresa_losa) {
				$_REQUEST['_lv_nav_id'] = $quizId;
				$_GET['_lv_nav_id'] = $quizId;
				$_POST['_lv_nav_id'] = $quizId;
	
				niceerror("Neispravan format IP adrese");
				?>
				<p>Raspon IP adresa treba biti u jednom od formata:<br>
				- CIDR format (npr. 123.45.67.89/24)<br>
				- raspon početak-kraj sa crticom (npr. 123.45.67.89-123.45.67.98)<br>
				- pojedinačna adresa<br>
				Takođe možete navesti više raspona ili pojedinačnih adresa razdvojenih zarezom.</p>
				<?
			}
		}
	}
	
	
	// Spisak postojećih kvizova
	
	?>
	Odaberite neki od postojećih kvizova koji želite administrirati:<br/>
	<ul>
		<?
		if (count($allQuizzes) == 0) {
			?>
		<li>Nema</li>
		<?
		} else foreach($allQuizzes as $quiz) {
			?>
			<li><a href="?sta=nastavnik/kvizovi&predmet=<?=$predmet?>&ag=<?=$ag?>&_lv_nav_id=<?=$quiz['id']?>"><?=$quiz['name']?></a></li>
		<?
		}
		?>
	</ul>
	<?
	
	if (count($allQuizzes) == 0) {
		?>
		<p><a href="?sta=nastavnik/kvizovi&predmet=<?=$predmet?>&ag=<?=$ag?>&akcija=prosla_godina">Prekopiraj kvizove sa prošle akademske godine</a></p>
		<?
	}
	
	
	// Form to add/edit quiz
	
	$groups = api_call("group/course/$predmet", [ "year" => $ag, "includeVirtual" => true ] )["results"];
	
	$quizId = intval($_REQUEST['_lv_nav_id']);
	$foundQuiz = false;
	foreach($allQuizzes as $quiz)
		if ($quiz['id'] == $quizId)
			$foundQuiz = $quiz;
	
	?>
	<hr>
	<?
	if ($foundQuiz) {
		?>
		<h3>Izmjena kviza</h3>
		<ul>
			<li><a href="?sta=nastavnik/kvizovi&predmet=<?=$predmet?>&ag=<?=$ag?>&kviz=<?=$quizId?>&akcija=pitanja">Izmijenite pitanja na kvizu</a></li>
			<li><a href="?sta=nastavnik/kvizovi&predmet=<?=$predmet?>&ag=<?=$ag?>&kviz=<?=$quizId?>&akcija=rezultati">Rezultati kviza (do sada poslani odgovori)</a></li>
		</ul>
		<?
		$action = "edit";
		list ($pocDate, $pocTime) = explode(" ", $foundQuiz['timeBegin']);
		list ($krajDate, $krajTime) = explode(" ", $foundQuiz['timeEnd']);
	} else {
		?>
		<h3>Kreiranje novog kviza</h3>
		<p>Unesite podatke o novom kvizu koji želite kreirati:</p><br>
		<?
		$action = "add";
		$foundQuiz = [ "name" => "", "Group" => [ "id" => 0 ], "ipAddressRanges" => "", "passPoints" => "", "nrQuestions" => "", "duration" => "", "active" => false ];
		$pocDate = $pocTime = $krajDate = $krajTime = "";
	}
	
	?>
	<form name="" action="/index.php" method="POST">
		<input type="hidden" name="sta" value="nastavnik/kvizovi">
		<input type="hidden" name="predmet" value="<?=$predmet?>">
		<input type="hidden" name="ag" value="<?=$ag?>">
		<input type="hidden" name="action" value="<?=$action?>">
		<input type="hidden" name="column_id" value="<?=$quizId?>">
		Naziv: <input type="text" name="naziv" size="30" value="<?=$foundQuiz['name']?>"><br/><br/>
		Početak: <input type="date" name="pocDate" value="<?=$pocDate?>"> <input type="time" name="pocTime" value="<?=$pocTime?>"> <br/><br/>
		Kraj: <input type="date" name="krajDate" value="<?=$krajDate?>"> <input type="time" name="krajTime" value="<?=$krajTime?>"><br/><br/>
		Samo za studente iz grupe: <select name="labgrupa">
			<?
			foreach($groups as $group) {
				if ($group['id'] == $foundQuiz['Group']['id']) $sel = "SELECTED"; else $sel = "";
				?>
				<option value="<?=$group['id']?>" <?=$sel?>><?=$group['name']?></option>
				<?
			}
			?>
		</select><br/><br/>
		Ograniči na IP adrese:<br/><textarea name="ip_adrese" rows="10" cols="50"><?=$foundQuiz['ipAddressRanges']?></textarea><br/><br/>
		Minimum bodova za prolaz: <input type="text" name="prolaz_bodova" size="3" value="<?=$foundQuiz['passPoints']?>"><br/><br/>
		Broj pitanja: <input type="text" name="broj_pitanja" size="3" value="<?=$foundQuiz['nrQuestions']?>"><br/><br/>
		Trajanje kviza (u sekundama): <input type="text" name="trajanje_kviza" size="3" value="<?=$foundQuiz['duration']?>"><br/><br/>
		<input type="checkbox" name="aktivan" <? if ($foundQuiz['active']) print "CHECKED"; ?>> Aktivan<br/><br/>
		<input type="submit" value=" Pošalji "> <input type="reset" value=" Poništi "> <?
		if ($action == "edit") {
			?>
			<input type="submit" name="delete" value=" Obriši ">
			<?
		}
		?>
	</form>
	<?
	
	// Markiramo loše polje
	if ($ip_adresa_losa) {
		?>
		<script>
		var element = document.getElementsByName('ip_adrese');
		element[0].style.backgroundColor = "#FF9999";
		element[0].focus();
		element[0].select();
		</script>
		<?
	}

}

?>
