<?

// STUDENT/POPUNI_KVIZ - popunjavanje kviza

function student_popuni_kviz() {
	global $userid;

	require_once("Config.php");

	// Backend stuff
	require_once(Config::$backend_path."core/CourseUnit.php");
	require_once(Config::$backend_path."core/AcademicYear.php");
	require_once(Config::$backend_path."core/Portfolio.php");
	require_once(Config::$backend_path."core/Util.php");

	// Ova skripta je dio modula lms/quiz tako da ovo ispod ne mora biti opcionalno
	require_once(Config::$backend_path."lms/quiz/Quiz.php");
	require_once(Config::$backend_path."lms/quiz/QuizResult.php");
	require_once(Config::$backend_path."lms/quiz/QuizQuestion.php");
	require_once(Config::$backend_path."lms/quiz/QuizAnswer.php");
	
	$kviz = intval($_REQUEST['kviz']);
	if ($_REQUEST['akcija']=="salji") {
		try {
			$res = QuizResult::fromStudentAndQuiz($userid, $kviz);
		} catch (Exception $e) {
			niceerror("Molimo ponovite kviz");
			zamgerlog("poslao popunjen kviz $kviz a nema stavke u student_kviz", 3);
			return;
		}
		$vrijeme_kraja = "FROM_UNIXTIME(".$res->timeActivated.") + INTERVAL (trajanje_kviza+60) SECOND";
		// Dodajemo 60 sekundi na trajanje, zbog evt. problema sa konekcijom
	} else
		$vrijeme_kraja = "vrijeme_kraj";

	$quiz = Quiz::fromId($kviz);
	if ( ! $quiz->active) {
		niceerror("Kviz nije aktivan");
		zamgerlog("kviz nije aktivan $kviz", 3);
		return;
	}

	if ( $quiz->timeBegin > time() || $quiz->timeEnd < time() ) {
		niceerror("Vrijeme za ovaj kviz je isteklo");
		zamgerlog("vrijeme isteklo za kviz $kviz", 3);
		return;
	}


/*	$naziv_ankete = mysql_result($q10,0,0);
	$predmet = mysql_result($q10,0,1);
	$ag = mysql_result($q10,0,2);
	$broj_pitanja = mysql_result($q10,0,8);
	$trajanje_kviza = mysql_result($q10,0,9); // u sekundama
	$prolaz_bodova = mysql_result($q10,0,10);*/
	
	// Određujemo naziv predmeta
	$cu = CourseUnit::fromID($quiz->courseUnitId);
	
	// Da li student sluša predmet?
	$pf = Portfolio::fromCourseUnit($userid, $quiz->courseUnitId, $quiz->academicYearId);
	

	// Da li je u labgrupi?
	if ( $quiz->groupId != 0 ) {
		// Ako je ova vrijednost definisana, možemo pretpostaviti da je instaliran modul lms/attendance
		require_once(Config::$backend_path."lms/attendance/Group.php");
		$g = new Group;
		$g->id = $quiz->groupId;
		if (! $g->isMember($userid) ) {
			niceerror("Nemate pristup ovom kvizu");
			zamgerlog("student nije u labgrupi $labgrupa za kviz $kviz", 3);
			return;
		}
	}

	if ( $quiz->ipAddressRanges != "" && ! Quiz::isIpInRange( Util::getip(), $quiz->ipAddressRanges ) ) {
		niceerror("Nemate pristup ovom kvizu");
		zamgerlog("losa ip adresa za kviz $kviz", 3);
		return;
	}


	// AKCIJA šalji
	// Sve ove provjere smo iskoristili da ih ne bismo ponovo kucali
	if ($_REQUEST['akcija'] == "salji" && check_csrf_token()) {
		$uk_bodova = 0;
		$rbr=1;
		for ($i=1; $i<=$quiz->nrQuestions; $i++) {
			// MCSA - ako je dato više tačnih odgovora na pitanje, uvažavamo bilo koji
			$id_pitanja = $_REQUEST["rbrpitanje$i"];
			
			$pitanje = QuizQuestion::fromId($id_pitanja);
			$odgovori = QuizAnswer::getAllForQuestion($id_pitanja);

			$tacan_odgovor = false;
			foreach ($odgovori as $o) {
				if ($o->correct && $_REQUEST["odgovor"][$id_pitanja] == $o->id) 
					$tacan_odgovor=false;
			}
			
			$ispis_rezultata .= "<tr><td>$rbr.</td><td>".substr($pitanje->text,0,20)."...</td><td>";
			$rbr++;
			
			if ($tacan_odgovor) {
				$uk_bodova += $pitanje->score;
				$ispis_rezultata .= '<img src="images/16x16/zad_ok.png" width="16" height="16">'."</td><td>".$pitanje->score."</td></tr>";
			} else
				$ispis_rezultata .= '<img src="images/16x16/brisanje.png" width="16" height="16">'."</td><td>0</td></tr>";
		}
		
		// Varijabla $res je trebala dobiti vrijednost u liniji 24
		$res->finished = true;
		$res->score = $uk_bodova;
		$res->update();
		
		print "<center><h1>Kviz završen</h1></center>\n";
		nicemessage("Osvojili ste $uk_bodova bodova.");
		if ( $uk_bodova >= $quiz->passPoints ) nicemessage("Čestitamo");
		?>
		<p><b>Tabela odgovora</b></p>
		<table border="1" cellspacing="0" cellpadding="2">
			<tr><td>R.br.</td><td>Pitanje</td><td>Tačno?</td><td>Bodova</td></tr>
		<?
		print $ispis_rezultata;
		print "</table>\n<br><br>\n";
		
		?><p><a href="#" onclick="window.close();">Zatvorite ovaj prozor</a></p><?
		zamgerlog("uradio kviz $kviz", 2);
		return;		
	}
	
	
	// Da li je već ranije popunjavao kviz?
	try {
		$res = QuizResult::fromStudentAndQuiz($userid, $kviz);
		niceerror("Već ste popunjavali ovaj kviz");
		zamgerlog("vec popunjavan kviz $kviz", 3);
		return;
	} catch (Exception $e) {
		// Nema QuizResult, nije popunjavao kviz
	}
	
	// Ubacujemo da je započeo kviz
	$res = new QuizResult;
	$res->studentId = $userid;
	$res->quizId = $kviz;
	$res->finished = false;
	$res->score = 0;
	$res->add();

		
	// Student može sudjelovati u kvizu pa šaljemo HTML

	?>
	<html>
	<head>
	<title>Kviz</title>
	<script>
	var Tpocetak=new Date();
	var Tkraj=new Date();
	var active_element;

	function onBlur() {
		if (/*@cc_on!@*/false) { // check for Internet Explorer
			if (active_element != document.activeElement) {
				active_element = document.activeElement;
				return;
			}
		}

		alert('Vaš kviz je obustavljen jer ste pokušali raditi nešto što nije popunjavanje kviza!\nIzgubili ste bodove.');
		window.close();
	}

	function ucitavanje() {
		Tkraj.setTime((new Date()).getTime()+<?=$trajanje_kviza?>*1000); // vrijeme je u milisekundama
		var t = setTimeout("provjeriVrijeme()",1000);
		if (/*@cc_on!@*/false) { // check for Internet Explorer
			active_element = document.activeElement;
			document.onfocusout = onBlur;
		} else {
			window.onblur = onBlur;
		}
	}
	
	function provjeriVrijeme() {
		var diff=new Date();
		diff.setTime(Tkraj-(new Date()));
		var vrijeme=document.getElementById('vrijeme');

		if (Tkraj<=(new Date())) {
			var forma=document.getElementsByName('slanje');
			forma[0].submit();
			return;
		}

		if (diff.getMinutes()==0 && diff.getSeconds()<30) {
			vrijeme.style.color='#FF0000';
		}
		var s = diff.getSeconds();
		if (s<10) s = "0"+s;
		
		vrijeme.innerHTML = diff.getMinutes()+":"+s;
		setTimeout("provjeriVrijeme()", 1000);
	}
	</script>
	</head>
	<body onload="ucitavanje()">
	<center><h2><?=$cu->name?></h2>
	<h2><?=$quiz->name?></h2></center>
	<div id="vrijemeinfo" style="width:150px; position:fixed; right:10px; top:20px; background-color: #303030; color:white;">Preostalo vrijeme: <span id="vrijeme"></span></div>
	<?


	// Ispisujemo pitanja kviza

	?>
	<br>
	<?=genform("POST", "slanje")?>
	<input type="hidden" name="akcija" value="salji">
	<table width=600px align=center>
	<?
		// ISPISI PITANJA
		$i=0;
		$pitanja = QuizQuestion::getAllForQuiz($quiz->id, $quiz->nrQuestions, true);
		foreach ($pitanja as $pitanje) {
			// TODO: za sada su samo MCSA pitanja podržana
			$i++;
			?>
			<tr>
				<td valign=top><font size="5" face="serif"><?=$i ?>.</font></td>
				<td><font size="5" face="serif"><?=$pitanje->text ?></font>
				<input type="hidden" name="rbrpitanje<?=$i?>" value="<?=$pitanje->id?>">
					<br>
					<table>
					<?
						$odgovori = QuizAnswer::getAllForQuestion($pitanje->id, true);
						foreach ($odgovori as $odgovor) {
							// FIXME: moze mapipulirati id odgovora i pitanja kada salje...
							?>
							<tr>
								<td><font size="5" face="serif">
								&nbsp;&nbsp; <input name="odgovor[<?=$pitanje->id ?>]" type="radio" value=<?=$odgovor->id ?>>&nbsp;&nbsp;<?=$odgovor->text; ?>
								</font></td>
							</tr>
							<?
						} // kraj ispisa odgovora 
					?>
					</table>
				</td>
			</tr>
			<tr><td colspan="2"><font size="5" face="serif">&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;</font></td></tr>
			<?
		}  // Kraj ispisa pitanja
		?>
	</table>	
		
	<br><br>
		<input type="submit" value="Predaj">
	</form>
<!--
	 <div id=navbox style="width:150px; position:fixed; right:10px; top:120px">
		<b><div id=showTime2 style="width:100%; background-color: #303030; color:white;" ></div></b>
		<?PHP for ($j=1; $j<$i+1; $j++) { ?>
			<a href="#pitanje<?PHP echo $j ?>" style="text-decoration: none; color:white;"><div style="width:100%; background-color: darkgray;" ><b>Pitanje <?PHP echo $j ?></b></div>
		<?PHP } ?>
	 </div>
-->

	</body>
	</html>
	<?

}


?>
