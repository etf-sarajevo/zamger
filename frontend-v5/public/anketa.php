<?

// PUBLIC/ANKETA - stranica za ispunjavanje ankete

function public_anketa() {

	global $userid,$user_siteadmin,$user_studentska,$user_nastavnik;
	global $_lv_; 


	require_once("Config.php");

	// Backend stuff
	require_once(Config::$backend_path."core/CourseUnit.php");
	require_once(Config::$backend_path."core/CourseUnitYear.php");
	require_once(Config::$backend_path."core/Portfolio.php");

	// Pošto je ova skripta ustvari dio lms/poll modula, ovo ispod ne treba biti opcionalno
	require_once(Config::$backend_path."lms/poll/Poll.php");
	require_once(Config::$backend_path."lms/poll/PollResult.php");
	require_once(Config::$backend_path."lms/poll/PollQuestion.php");
	require_once(Config::$backend_path."lms/poll/PollAnswer.php");


	// Predmet i a.g. mogu biti parametri, a ako nisu zadati koristi se aktuelna anketa za sve predmete
	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);


	// Akcija za preview ankete, provjeravamo privilegije
	if ($_GET['akcija'] == "preview") {
		$ok = false;
		if ($user_studentska || $user_siteadmin) $ok = true;

		$cuy = new CourseUnitYear;
		$cuy->courseUnitId = $predmet;
		$cuy->academicYearId = $ag;
		
		if ($predmet>0 && $ag>0 && $user_nastavnik) {
			$pristup = $cuy->teacherAccess($userid);
			if ($pristup != "nema" && $pristup != "asistent") $ok = true;
		}

		if (!$ok) {
			zamgerlog("preview ankete privilegije",3); // 3: error
			biguglyerror("Pristup nije dozvoljen.");
			return;
		}
	}

	// Da li je dat ID ankete kao parametar?
	if (isset($_REQUEST['anketa'])) {
		$poll = Poll::fromId(intval($_REQUEST['anketa']));

	} else {
		try {
			if ($predmet>0) 
				$poll = Poll::getActiveForCourse($predmet, $ag);
			else
				$poll = Poll::getActiveForAllCourses();
				
		} catch(Exception $e) {
			biguglyerror("Anketa trenutno nije aktivna.");
			return;
		}
	}


	// Da li je istekao rok?
	if ($_GET['akcija'] != "preview" && (time () > $poll->closeDate || time () < $poll->openDate)) {
		biguglyerror("Anketa trenutno nije aktivna");
		return;
	}


	// U slučaju preview, ne radimo ništa
	if ($_GET['akcija'] == "preview") {
		$zavrsena = 'N';
	

	// Ako je student unio hash code, koristimo ga radi anonimnosti rezultata
	} else if (isset($_POST['hash_code'])) {
		// CSRF zaštita
		if (!check_csrf_token()) {
			?>
			<center>
				<p>Vaša sesija je istekla. Molimo da ponovo popunite anketu. Izvinjavamo se zbog neugodnosti.</p>
				<a href="index.php">Nazad na početnu stranicu</a>
			</center>
			<?
			return;
		}

		$hash_code = my_escape($_POST['hash_code']);

		// Provjeravamo da li kod postoji i da li je već iskorišten (polje zavrsena)
		try {
			$pr = PollResult::fromHash($hash_code);
		} catch (Exception $e) {
			// Dati hash ne postoji u bazi tj. student pokušava da izmisli hash :P
			?>
			<center>
				<p>Greška: neispravan kod '<?=$hash_code?>'.</p>
				<a href="index.php">Nazad na početnu stranicu</a>
			</center>
			<?
			return;
		}
		
		if ($pr->pollId != $poll->id) {
			// Kod je za drugu anketu
			// Dati hash ne postoji u bazi tj. student pokušava da izmisli hash :P
			?>
			<center>
				<p>Greška: neispravan kod '<?=$hash_code?>'.</p>
				<a href="index.php">Nazad na početnu stranicu</a>
			</center>
			<?
			return;
		}

		// Postavljamo predmet i ag iz PollResult
		$predmet = $pr->courseUnitId;
		$ag = $pr->academicYearId;
/*$q50 = myquery("SELECT id, predmet, zavrsena FROM anketa_rezultat WHERE unique_id='$hash_code' and anketa=$id_ankete");
	
		$id_rezultata = mysql_result($q50,0,0);
		$predmet = mysql_result($q50,0,1);
		$zavrsena = mysql_result($q50,0,2);*/


	// Student je logiran, pa ćemo pokušati iskoristiti njegove kredencijale
	} else if ($userid>0) {
		// Korisnik nije izabrao predmet, pa je najvjerovatnije pokušao ući na anketu bez logouta
		if ($predmet==0 || $ag==0) {
			biguglyerror("Niste napravili logout!");
			return;
		}
		
		// Da li student sluša predmet?
		$pf = Portfolio::fromCourseUnit($userid, $predmet, $ag); // Ako ne, baciće izuzetak

		try {
			$pr = PollResult::fromStudentAndPoll($userid, $poll->id);
		} catch(Exception $e) {
			// Aktuelni studij i semestar za studenta
			$en = Enrollment::getCurrentForStudent($userid);

			// Ne postoji rezultat za ovog studenta, kreiramo novi
			$pr = new PollResult;
			$pr->pollID = $poll->id;
			$pr->finished = false;
			$pr->courseUnitId = $predmet;
			$pr->academicYearID = $ag;
			$pr->programmeId = $en->programmeId;
			$pr->semester = $en->semester;
			$pr->studentId = $userid;
			
			$pr->add(); // Dodavanje u bazu
		}
		
		$_POST['akcija'] = "prikazi"; // Možemo odmah prikazati anketu


	// Ništa od navedenog, prikazujemo ekran za unos koda
	} else {
		?>
		<?=genform("POST")?>
		<table align="center" cellpadding="0">
			<tr>
				<td>
					<br/>
					Unesite kod koji ste dobili za ispunjavanje ankete: &nbsp;
				</td>
				<td>
					<br/>
					<input type="hidden" id="akcija" name="akcija" value="prikazi">
					<input type="text" id="kod" name="hash_code"  size="60">	
				</td>
			</tr>
			<tr>
				<td colspan="2" align="center">
					<br/>
					 <input type="submit" value="Pošalji">
				</td>
			</tr>
		</table>
		</form>
		<?
		return;
	}


	// Da li je anketa već popunjena?
	if ($zavrsena != 'N') {
		?>
		<center>
			<p>Već ste jednom popunili anketu. Nema mogućnosti izmjene jednom popunjene ankete.</p>
			<a href="index.php">Nazad na početnu stranicu</a>
		</center>
		<?
		return;
	}


	// Završetak i prijem ankete
	if ($_POST['akcija'] == "finish" && check_csrf_token()) {
		
		if ($_POST['odbija'] != "da") { // Regularno popunjena anketa
			$pitanja = PollQuestion::getAllForPoll($poll->id);
			foreach ($pitanja as $pi) {
				switch ($pi->typeId) {
					case 1: // Rank pitanje
						$pi->setAnswerRank($pr->id, intval($_POST['izbor'.$pi->id]))
						break;
					case 2: // Esejsko pitanje
						$pi->setAnswerEssay($pr->id, my_escape($_POST['komentar'.$pi->id]))
						break;
					case 3: // MCSA
						$izbor = intval($_POST['izbor'.$pi->id]);
						$pi->setAnswerChoice($pr->id, intval($_POST['izbor'.$pi->id]), my_escape($_POST['dopis'.$pi->id."-$izbor"]))
						break;
					case 4: // MCMA
						// Moramo testirati request varijable za sve moguće odgovore
						// Zato prvo skidamo listu odgovora
						$odgovori = PollAnswer::forQuestion($pi->id);
						foreach ($odgovori as $o) {
							if ($_POST["izbor".$pi->id."-".$o->id])
								$pi->setAnswerChoice($pr->id, $o->id, my_escape($_POST["dopis".$pi->id."-".$o->id]));
						}
					break;
				}
			}
		}
		
		// nakon uspjesnog ispunjenja ankete postaviti i polje zavrsena na true u tabeli razultati
		$pr->finished = true;
		$pr->update();
		
		zamgerlog("popunjena anketa za predmet pp$predmet", 2);

		?>
		<center>
			<p> Hvala na ispunjavanju ankete. </p>
			<a href="index.php">Nazad na početnu stranicu</a>
		</center>
		<?
		return;
	}


	//  Prikaz forme za anketu
	else if ($_POST['akcija'] == "prikazi" || $_GET['akcija'] == "preview") {
		if ($predmet==0) { // ovo se može desiti samo ako je preview!
			$naziv_predmeta = "NAZIV PREDMETA";
		} else {
			$cu = CourseUnit::fromId($predmet);
			$naziv_predmeta = $cu->name;
		}
		
		?>
		<center>
			<h2><?=$poll->name?> (<?=$naziv_predmeta?>)</h2>
		</center>
		<?=genform("POST")?>
		<input type="hidden" name="akcija" value="finish">
		<input type="hidden" name="hash_code" value="<?=$hash_code?>">
		<table align="center" cellpadding="4" border="0" >
			<tr>
				<td colspan = '7'>
					<?=$poll->description?>
					<br />&nbsp;<br />
				</td>
			</tr>
			<tr>
				<td colspan = '7' align="center">
					<input type="checkbox" name="odbija" value="da"> Odbijam da učestvujem u anketi za ovaj predmet.
					<br />&nbsp;<br />
				</td>
			</tr>

			<?


			$pitanja = PollQuestion::getAllForPoll($poll->id);
			$boja = "#FFFFFF";
			foreach ($pitanja as $pi) {
				ubaci_pitanje($pi, $boja);
				if ($boja == "#FFFFFF") $boja = "#CCCCFF"; else $boja = "#FFFFFF";
				// Resetujemo boju poslije naslova
				if ($pi->typeId == 5 || $pi->typeId == 6) $boja = "#FFFFFF";
			}

			/*
			?>
			<tr><td colspan='6'><hr/></td></tr>
			<? $broj_pitanja = Ubaci_pitanje(1,$anketa,1);?>
			<tr><td colspan='6'><hr/></td></tr>
			<? $broj_pitanja = Ubaci_pitanje(2,$anketa,$broj_pitanja);?>
			<tr><td colspan='6'><hr/></td></tr><?
			*/
			?>

		</table>
		<br />
		<table align="center">
			<tr>
				<td>
					<input align="middle"  type="submit" value="Pošalji" <?
					if ($_GET['akcija'] == "preview") print "disabled"; 
					?>/>
				</td>
			</tr>
		</table>
		</form>

		<?
	} // ---------------  KRAJ AKCIJA PRIKAZI  --------------------------

	else	{
		?>
		<?=genform("POST")?>
		<table align="center" cellpadding="0">
			<tr>
				<td>
					<br/>
					Unesite kod koji ste dobili za ispunjavanje ankete: &nbsp;
				</td>
				<td>
					<br/>
					<input type="hidden" id="akcija" name="akcija" value="prikazi">
					<input type="text" id="kod" name="hash_code"  size="60">	
				</td>
			</tr>
			<tr>
				<td colspan="2" align="center">
					<br/>
					 <input type="submit" value="Pošalji">
				</td>
			</tr>
		</table>
		</form>

		<?
	}
}//  ----------------- KRAJ FUNKCIJE PUBLIC_ANKETA -------------


// Pomoćna funkcija, samo ubacuje pitanje :)
function ubaci_pitanje($pitanje, $bgcolor) {
	$tekst = nl2br($pitanje->text);

	// Tip 1: rank pitanje
	if ($pitanje->typeId == 1) {
		?>
		<tr bgcolor="<?=$bgcolor?>">
			<td><?=$tekst?></td>
			<td><input type="radio" name="izbor<?=$id?>" value=1 /> 1</td>
			<td><input type="radio" name="izbor<?=$id?>" value=2 /> 2</td>
			<td><input type="radio" name="izbor<?=$id?>" value=3 /> 3</td>
			<td><input type="radio" name="izbor<?=$id?>" value=4 /> 4</td>
			<td><input type="radio" name="izbor<?=$id?>" value=5 /> 5</td>
			<td><input type="radio" name="izbor<?=$id?>" value=0 checked/> N/A</td>
		</tr>
		<?
	}

	// Tip 2: esejsko pitanje
	if ($pitanje->typeId == 2) {
		?>
		<tr bgcolor="<?=$bgcolor?>">
			<td colspan='7' align='center'>
				<p><?=$tekst?></p>
				<textarea name="komentar<?=$id?>" rows="7" cols="40"></textarea>
			</td>
		</tr>
		<?
	}

	// Tip 3: MCSA
	// Tip 4: MCMA
	if ($pitanje->typeId == 3 || $pitanje->typeId == 4) {
		?>
		<tr bgcolor="<?=$bgcolor?>">
			<td colspan="7">
				<p><?=$tekst?></p>
				<?

				// Spisak izbora
				$odgovori = PollAnswer::forQuestion($pitanje->id);
				foreach ($odgovori as $o) {
					if ($o->allowsWritein)
						$dopis = " (dopisati): <input type=\"text\" size=\"30\" name=\"dopis$id-$r300[0]\">";
					else $dopis="";
	
					if ($pi->typeId == 3) // MCSA - radio button
						print "<input type='radio' name='izbor$id' value='$r300[0]'> $r300[1]$dopis<br>\n";
					else
						print "<input type='checkbox' name='izbor$id-$r300[0]'> $r300[1]$dopis<br>\n";
				}
				?>
			</td>
		</tr>
		<?
	}

	// Tip 5: Naslov
	if ($pitanje->typeId == 5) {
		$tekst = strtoupper($tekst);
		$tekst = str_replace(array("č","ć","š","đ","ž"), array("Č","Ć","Š","Đ","Ž"), $tekst);
		?>
		<tr><td>&nbsp;</td></tr>
		<tr bgcolor="#CCCCCC">
			<td colspan="7"><?=$tekst?></td>
		</tr>
		<?
	}

	// Tip 6: Podnaslov
	if ($pitanje->typeId == 6) {
		?>
		<tr>
			<td colspan="7"><?=$tekst?></td>
		</tr>
		<?
	}
}


?>
