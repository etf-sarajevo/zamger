<?php

// STUDENT/PRIJAVA_ISPITA - stranica pomoću koje se studenti prijavljuju za termine ispita



function student_prijava_ispita() {

	global $userid, $_api_http_code;
	
	
	?>
	<h3>Prijava ispita</h3>
	<?
	
	// Spisak ispita koji se mogu prijaviti
	$upcoming_events = api_call("event/upcoming/$userid")['results'];
	
	// Spisak ispita na koje je student već prijavljen
	$registered_events = api_call("event/registered/$userid", [ "resolve" => ["CourseActivity"]] )['results'];
	
	
	// Odjavljivanje sa prijavljenog ispita
	
	if ($_GET["akcija"]=="odjavi") {
		$termin = intval($_GET['termin']);
		$foundEvent = [];
		foreach($registered_events as $event) {
			if ($event['id'] == $termin)
				$foundEvent = $event;
		}
		
		if (empty($foundEvent)) {
			niceerror("Već ste ispisani sa termina.");
			?>
			<script language="JavaScript">
			location.href='?sta=student/prijava_ispita';
			</script>
			<?
			return;
		}
		
		if (db_timestamp($foundEvent['deadline']) < time() && $_GET['potvrda_odjave'] != "da") {
			niceerror("Rok za prijavljivanje na ovaj ispit je istekao!");
			?>
			<p>Ako se sada odjavite, više se nećete moći ponovo prijaviti za ovaj isti termin! Da li ste sigurni da želite da se odjavite?</p>
			<?=genform("GET");?>
			<input type="hidden" name="potvrda_odjave" value="da">
			<input type="submit" value="Da, odjavi me!">
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="button" value="Nazad" onclick="javascript:location.href='?sta=student/prijava_ispita'">
			</form>
			<?
			return;
		}
		
		api_call("event/$termin/register/$userid", [], "DELETE");
		if ($_api_http_code == "204") {
			$predmet = $foundEvent['CourseUnit']['id'];
			nicemessage("Uspješno ste odjavljeni sa ispita.");
			zamgerlog("odjavljen sa ispita (pp$predmet)", 2);
			zamgerlog2("odjavljen sa termina", $termin);
			
			foreach($registered_events as $i => $event)
				if ($event['id'] == $foundEvent['id'])
					unset($registered_events[$i]);
		} else {
			niceerror("Odjavljivanje sa ispita nije uspjelo");
		}
	}
	
	
	// Prijava na ispit
	
	if ($_GET["akcija"]=="prijavi") {
		$termin = intval($_REQUEST['termin']);
		$foundEvent = [];
		foreach($upcoming_events as $event) {
			if ($event['id'] == $termin)
				$foundEvent = $event;
		}
		if (empty($foundEvent)) {
			niceerror("Neispravan termin.");
			return;
		}
	
		// Da li je popunjen termin?
		if ($foundEvent['registered'] >= $foundEvent['maxStudents']) {
			niceerror("Ispitni termin je popunjen.");
		} else {
			// Da li je već prijavio termin na istom ispitu?
			$same = false;
			foreach($registered_events as $event) {
				if ($foundEvent['CourseUnit']['id'] == $event['CourseUnit']['id'] && $foundEvent['CourseActivity']['id'] == $event['CourseActivity']['id']) {
					$same = true;
				}
			}
			if ($same) {
				niceerror("Već ste prijavljeni na neki termin za ovaj ispit.");
			} else {
				api_call("event/$termin/register/$userid", [], "POST");
				if ($_api_http_code == "201") {
					$predmet = $foundEvent['CourseUnit']['id'];
					nicemessage("Uspješno ste prijavljeni na termin ispita");
					zamgerlog("prijavljen na termin za ispit (pp$predmet)", 2);
					zamgerlog2("prijavljen na termin", $termin);
					
					$registered_events[] = $foundEvent;
				} else {
					niceerror("Prijavljivanje na ispit nije uspjelo");
				}
			}
		}
	}
	
	
	
	// GLAVNI EKRAN
	

	?>
	<br><br>
	<b>Ispiti otvoreni za prijavu:</b>
	<br><br>
	<table border="0" cellspacing="1" cellpadding="5">
	<thead>
	<tr bgcolor="#999999">
		<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">R.br.</font></td>
		<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Predmet</font></td>
		<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Rok za prijavu</font></td>
		<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Vrijeme ispita</font></td>
		<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Tip ispita</font></td>
		<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Opcije</font></td>
	</tr>
	</thead>
	<tbody>
	
	<?
	
	$brojac=1;
	$ispiti_u_ponudi = [];
	
	foreach ($upcoming_events as $event) {
		$id_termina = $event['id'];
		$id_predmeta = $event['CourseUnit']['id'];
		$id_komponente = $event['CourseActivity']['id'];
	
		$naziv_predmeta = getCourseName($id_predmeta);
		$vrijeme_ispita = date("d.m.Y. H:i", db_timestamp($event['dateTime']));
		$rok_za_prijavu = date("d.m.Y. H:i", db_timestamp($event['deadline']));
		$tip_ispita = $event['CourseActivity']['name'];
		
		// If event is exam, try to see if this is absolvent exam
		if ($event['CourseActivity']['Activity']['className'] == "ExamActivity") {
			$ispiti_u_ponudi[] = $event['options'];
			$exam = api_call("exam/" . $event['options']);
			if ($exam['absolvent'])
				$tip_ispita .= " - apsolventski rok";
		}
		$max_studenata = $event['maxStudents'];
		$prijavljenih = $event['registered'];
		
	
		$greska = $greska_long = "";
		
		// Is there MinScore option or conditional activities
		if (array_key_exists("MinScore", $event['CourseActivity']['options']) || !empty($event['CourseActivity']['conditionalActivities'])) {
			// Get student portfolio with score
			$portfolio = api_call("course/$id_predmeta/student/$userid", ["totalScore" => true]);
			if (array_key_exists("MinScore", $event['CourseActivity']['options']) && $event['CourseActivity']['options']['MinScore'] > $portfolio['totalScore']) {
				$greska = "U";
				$greska_long = "Nemate dovoljno bodova";
			}
			else if (!empty($event['CourseActivity']['conditionalActivities'])) {
				foreach ($event['CourseActivity']['conditionalActivities'] as $cact) {
					foreach($portfolio['score'] as $score) {
						if ($cact['id'] == $score['CourseActivity']['id']) {
							if ($score['score'] < $score['CourseActivity']['pass']) {
								$greska = "U";
								$greska_long = "Niste položili " . $score['CourseActivity']['name'];
							}
						}
					}
				}
			}
		}
	
		// Da li je već prijavio ovaj ispit u nekom od termina?
		foreach ($registered_events as $registered_event) {
			if ($registered_event['id'] == $event['id']) {
				$greska .= "O";
				$greska_long .= "Već ste prijavljeni za ovaj termin. ";
				break;
			}
		}
		if ($greska == "") foreach ($registered_events as $registered_event) {
			if ($registered_event['CourseUnit']['id'] == $event['CourseUnit']['id'] && $registered_event['CourseActivity']['id'] == $event['CourseActivity']['id']) {
				$greska .= "D";
				$greska_long .= "Prijavljeni ste za drugi termin ovog ispita. ";
				break;
			}
		}
		
		// Da li je termin popunjen?
		if ($prijavljenih >= $max_studenata) {
			$greska .= "P"; $greska_long = "Termin popunjen. ";
		}
	
		// Da li je istekao rok za prijavu?
		$color = "";
		if (db_timestamp($event['deadline']) < time()) {
			$color = " style=\"color: #999\"";
		}
	
		?>
		<tr<?=$color?>>
			<td<?=$color?>><?=$brojac?></td>
			<td<?=$color?>><?=$naziv_predmeta?></td>
			<td align="center"<?=$color?>><?=$rok_za_prijavu?></td>
			<td align="center"<?=$color?>><?=$vrijeme_ispita?></td>
			<td align="center"<?=$color?>><?=$tip_ispita?></td>
			<td align="center"<?=$color?> title="<?=$greska_long?>"><?
	
		if (db_timestamp($event['deadline']) < time()) {
			?>Rok za prijavu je istekao<?
		} else if ($greska === "") {
			?><a href="?sta=student/prijava_ispita&akcija=prijavi&termin=<?=$id_termina?>">Prijavi</a><?
		} else {
			?><font color="#FF0000">Prijava nije moguća (<?=$greska?>)</font><?
		} ?></td>
		</tr>
		<?
		$brojac++;
	}

	?>
	</table>
	<? if($brojac==1) {
		?><p>Trenutno nema termina na koje se možete prijaviti.</p><?
	} else {
		?><p><b>LEGENDA GREŠAKA:</b><br>
		<b>P</b> - termin je popunjen (ako nema ove oznake, postoji još slobodnih mjesta na ovom terminu)<br>
		<b>O</b> - već ste prijavljeni za ovaj termin<br>
		<b>D</b> - prijavljeni ste za drugi termin istog ispita; potrebno je da se odjavite sa tog termina da biste se mogli prijaviti za ovaj termin<br>
		<b>U</b> - ne ispunjavate uslove za ovaj termin</p>
		<?
	}
	
	?>
	<br><br><br>
	
	<b>Prijavljeni ispiti:</b>
	
	<?
	
	
	//slijedeci dio koda sluzi za tabelarni prikaz prijavljenih predmeta
	
	
	?>
	<br><br>
	<table border="0" cellspacing="1" cellpadding="5">
	<thead>
	<tr bgcolor="#999999">
		<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">R.br.</font></td>
		<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Predmet</font></td>
		<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Vrijeme ispita</font></td>
		<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Tip ispita</font></td>
		<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Opcije</font></td>
	</tr>
	</thead>
	<tbody>
	<?
	$brojac=1;
	
	foreach ($registered_events as $event) {
	
		// Ako je ispit u prošlosti, nije dozvoljeno odjavljivanje
		if (db_timestamp($event['dateTime']) < time() && !in_array($event['options'], $ispiti_u_ponudi)) continue;
		
		// Takođe ne dozvoljavamo da se student odjavi sa ispita za koje ima ocjenu jer bi to moglo pobrkati izvoz ocjena
		$courseDetails = getCourseDetails($event['CourseUnit']['id']);
		if ($courseDetails['grade'] >= 6) continue;
	
		?>
		<tr>
			<td><?=$brojac?></td>
			<td><?=$courseDetails['courseName']?></td>
			<td align="center"><?=date("d.m.Y. H:i", db_timestamp($event['dateTime']));?></td>
			<td align="center"><?=$event['CourseActivity']['name'];?></td>
			<td align="center"><?
			if (db_timestamp($event['dateTime']) < time()) {
				?>
				<span style="color: #999">Odjava nije moguća jer je ispit prošao</span></td>
				<?
			} else {
				?>
				<a href="?sta=student/prijava_ispita&amp;akcija=odjavi&amp;termin=<?=$event['id'];?> ">Odjavi</a></td>
				<?
			}
			?>
		</tr>
		<?
		$brojac++;
	}

?>
</table>
<?

if($brojac==1) print "<p>Niste prijavljeni niti na jedan ispit</p>";




}
?>
