<?

// STUDENTSKA/PRIJAVE - štampanje prijava



function studentska_prijave() {

	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);
	
	// Naziv predmeta
	$course = api_call("course/$predmet/$ag");
	$naziv_predmeta = $course['courseName'];
	$naziv_ag = $course['AcademicYear']['name'];
	
	// Kreiramo spisak studenata i provjeravamo njihov status
	$group = api_call("group/course/$predmet/allStudents",
		[ "names" => true, "year" => $ag ]
	);

	$studenata_uslov = $studenata_bez_ocjene = $svih_studenata = 0;
	$studentList = [];
	foreach($group['members'] as $member) {
		$studentList[$member['student']['id']] = $member['student']['surname'] . " " . $member['student']['name'];
		$svih_studenata++;
	
		// Ima li ocjenu?
		if (!$member['grade']) {
			$studenata_bez_ocjene++;
	
			// Ima li uslov?
			$uslov = true;
			// All activities are not neccessarily in student score
			// So we use the list from course
			foreach($course['activities'] as $activity) {
				if ($activity['mandatory']) {
					$hasActivity = false;
					foreach($member['score'] as $studentScore) {
						if ($studentScore['CourseActivity']['id'] == $activity['id'] && $studentScore['score'] >= $activity['pass']) {
							$hasActivity = true;
							break;
						}
					}
					if (!$hasActivity) {
						$uslov = false;
						break;
					}
				}
			}
			if ($uslov) $studenata_uslov++;
		}
	}
	
	uasort($studentList, "bssort");
	
	$spisak = "";
	foreach($studentList as $id => $name)
		$spisak .= "<option value=\"$id\">" . $name . "</option>\n";
	
	$studenata_sa_ocjenom = $svih_studenata - $studenata_bez_ocjene;

	?>
	<p><h3>Studentska služba - Štampanje prijava</h3></p>
	
	<p><h3>Predmet: <?=$naziv_predmeta?> (<?=$naziv_ag?>)</h3></p>
	
	<p><a href="?sta=studentska/predmeti&akcija=edit&predmet=<?=$predmet?>&ag=<?=$ag?>">&gt; &gt; Povratak na stranicu predmeta</a></p>
	
	<p>Štampajte prijave za:
	
	<ul>
	<li>Sve studente koji su se prijavili za ispit:<br/>
		<ul>
	<?


	$events = api_call("event/course/$predmet/$ag", [ "resolve" => [ "CourseActivity" ] ])["results"];
	$prosli_datum = $prosla_komponenta = "";
	$broj_na_datum = $studenata_na_datum = 0;
	foreach($events as $event) {
		if (date("Y-m-d", db_timestamp($event['dateTime'])) != $prosli_datum || $prosla_komponenta != $event['CourseActivity']['id']) {
			if ($broj_na_datum > 1) {
				?>
				<li><a href="?sta=izvjestaj/prijave&amp;tip=na_datum&amp;datum=<?=$prosli_datum?>&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>">Svi studenti na datum <?="$prosli_datum, $prosla_komponenta</a> ($studenata_na_datum studenata)"?> - samo studenti <a href="?sta=izvjestaj/prijave&amp;tip=na_datum_sa_ocjenom&amp;datum=<?=$prosli_datum?>&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>">sa ocjenom</a></li>
				<?
			}
			$prosli_datum = date("Y-m-d", db_timestamp($event['dateTime']));
			$broj_na_datum = 1;
			$studenata_na_datum = $event['registered'];
			$prosla_komponenta = $event['CourseActivity']['id'];
		} else {
			$broj_na_datum++;
			$studenata_na_datum += $event['registered'];
		}
		?>
		<li><a href="?sta=izvjestaj/prijave&amp;ispit_termin=<?=$event['id']?>"><?=date("d.m.Y. h:i", db_timestamp($event['dateTime'])).", ".$event['CourseActivity']['name']."</a> (".$event['registered']." studenata)"?> - samo studenti <a href="?sta=izvjestaj/prijave&amp;ispit_termin=<?=$event['id']?>&amp;tip=sa_ocjenom">sa ocjenom</a></li>
		<?
	}
	if ($broj_na_datum > 1) {
		?>
		<li><a href="?sta=izvjestaj/prijave&amp;tip=na_datum&amp;datum=<?=$prosli_datum?>&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>">Svi studenti na datum <?="$prosli_datum, $prosla_komponenta</a> ($studenata_na_datum studenata)"?> - samo studenti <a href="?sta=izvjestaj/prijave&amp;tip=na_datum_sa_ocjenom&amp;datum=<?=$prosli_datum?>&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>">sa ocjenom</a></li>
		<?
	}

	?>
		<br/></ul>
	</li>
	<li><a href="?sta=izvjestaj/prijave&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>&amp;tip=uslov">Sve studente koji imaju uslove za usmeni (<?=$studenata_uslov?> studenata)</a><br/>&nbsp;</li>
	<li><a href="?sta=izvjestaj/prijave&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>&amp;tip=bez_ocjene">Sve studente koji nemaju upisanu ocjenu (<?=$studenata_bez_ocjene?> studenata)</a><br/>&nbsp;</li>
	<li><a href="?sta=izvjestaj/prijave&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>&amp;tip=sa_ocjenom">Sve studente koji imaju upisanu ocjenu (<?=$studenata_sa_ocjenom?> studenata)</a><br/>&nbsp;</li>
	<li><a href="?sta=izvjestaj/prijave&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>&amp;tip=sve">Sve studente (<?=$svih_studenata?> studenata)</a><br/>&nbsp;</li>
	<li>Pojedinačnog studenta:<br/>
	<form action="index.php" method="GET">
	<input type="hidden" name="sta" value="izvjestaj/prijave">
	<input type="hidden" name="predmet" value="<?=$predmet?>">
	<input type="hidden" name="ag" value="<?=$ag?>">
	<select name="student" class="default"><?=$spisak?></select> <input type="submit" value=" Odaberi " class="default"></form></li>
	</ul>
	<?

	$foundTeacher = "";
	$teachers = 0;
	foreach($course['staff'] as $teacher) {
		if ($teacher['status_id'] == 1) { // professor
			$foundTeacher = $teacher;
			$teachers++;
		}
	}
	if ($teachers == 0) {
		?><p><b>Napomena:</b> Za ovaj predmet nije podešen odgovorni nastavnik!</p><?
	} else if ($teachers>1) { // Ako imaju dva odgovorna nastavnika, ne znam kojeg da stavim
		?><p><b>Napomena:</b> Za ovaj predmet je podešen više od jednog odgovornog nastavnika! Polje za odgovornog nastavnika na prijavi neće biti popunjeno. Morate ga popuniti ručno.</p><?
	} else {
		if (empty(trim($teacher['titlesPre']))) {
			?><p><b>Napomena:</b> Predmetnom nastavniku je istekao izbor ili nisu popunjeni odgovarajući podaci. Bez podataka o izboru ne možemo ispravno popuniti titulu nastavnika. Polje za odgovornog nastavnika na prijavi neće biti popunjeno. Morate ga popuniti ručno.</p><?
		}
	}


}

?>
