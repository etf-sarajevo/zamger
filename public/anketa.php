<?

// PUBLIC/ANKETA - stranica za ispunjavanje ankete



function public_anketa() {

	global $userid, $_api_http_code;
	
	$id_ankete = intval($_REQUEST['anketa']);
	
	// If token is defined, we will just take poll information from API
	if (isset($_POST['token']) && $_POST['token'] != "") {
		// CSRF zaštita
		if (!check_csrf_token()) {
			?>
			<center>
				<p>Vaša sesija je istekla. Molimo da ponovo popunite anketu. Izvinjavamo se zbog neugodnosti.</p>
				<a href="index.php">Nazad na početnu stranicu</a>
			</center>
			<?
			zamgerlog2("ilegalan CSRF token");
			return;
		}
		$token = $_POST['token'];

		// Get poll as object
		$poll = api_call("poll/$id_ankete", ["questions" => true ], "GET", false, true, false);
	}
	
	// Action for previewing poll (for teachers)
	else if ($_GET['akcija'] == "preview") {
		$token = "";
		$predmet = intval($_REQUEST['predmet']);
		$ag = intval($_REQUEST['ag']);
		$poll = api_call("poll/$id_ankete", ["questions" => true ], "GET", false, true, false);
	}
	
	// Otherwise course and year are mandatory parameters so we can call "take" endpoint
	else if ($userid > 0) {
		$predmet = intval($_REQUEST['predmet']);
		$ag = intval($_REQUEST['ag']);
		$poll = api_call("poll/$id_ankete/course/$predmet/take", ["year" => $ag ], "GET", false, true, false );
		
		// Token will be embedded in the Poll object
		$token = $poll->result->token;
	}
	
	// If user is not logged in, show screen for entering token
	else {
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
					<input type="text" id="token" name="token"  size="60">
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

	
	if ($_api_http_code == "404") {
		biguglyerror("Nepoznata anketa $id_ankete");
		api_report_bug($poll, []);
		return;
	}
	else if ($_api_http_code == "403") {
		biguglyerror("Nemate pravo popunjavanja ankete $id_ankete");
		return;
	}
	else if ($_api_http_code != "200") {
		biguglyerror("Greška prilikom pristupanja anketi");
		api_report_bug($poll, []);
		return;
	}
	if ($_GET['akcija'] != "preview" && !$poll->active) {
		biguglyerror("Anketa trenutno nije aktivna.");
		api_report_bug($poll, []);
		return;
	}

	// Metapodaci o anketi
	$naziv_ankete = $poll->name;
	$otvaranje = db_timestamp($poll->openDateTime);
	$zatvaranje = db_timestamp($poll->closeDateTime);
	$opis_ankete = nl2br($poll->description);
	

	// Da li je istekao rok?
	if ($_GET['akcija'] != "preview" && (time () > $zatvaranje || time () < $otvaranje)) {
		if (time() < $otvaranje)
			print "<center><h1><font color=\"#00AA00\">Anketa će postati aktivna ".date("d. m. Y. \u H:i", $otvaranje)."</font></h1></center>";
		else
			biguglyerror("Anketa trenutno nije aktivna B");
		return;
	}


	// Završetak i prijem ankete
	if ($_POST['akcija'] == "finish" && check_csrf_token()) {
		
		if ($_POST['odbija'] != "da") {
			foreach($poll->questions as &$question) {
				$pitanje = $question->id;
				$tip = $question->type;

				if ($tip == 1) { // Rank pitanje
					$izbor = intval($_POST['izbor'.$pitanje]);
					if ($izbor > 0) { // Odgovor N/A ima vrijednost 0
						$question->answered = true;
						$question->answer = $izbor;
					} else
						$question->answered = false;
				}
				
				if ($tip == 2) { // Esejsko pitanje
					$komentar = $_POST['komentar'.$pitanje];
					if (preg_match("/\w/", $komentar)) { // Ima li slova u komentaru?
						$question->answered = true;
						$question->answer = $komentar;
					} else
						$question->answered = false;
				}
				
				if ($tip == 3) { // MCSA
					$izbor = intval($_POST['izbor'.$pitanje]);
					foreach($question->answers as &$answer) {
						$answer->selected = ($answer->id == $izbor);
						if ($answer->selected && $answer->allowsWritein)
							$answer->writeInReponse = $_POST["dopis$pitanje-$izbor"];
						else
							$answer->writeInReponse = ""; // Initialize field
					}
				}
				
				if ($tip == 4) { // MCMA
					foreach($question['answers'] as &$answer) {
						$odgovor = $answer->id;
						$answer->selected = isset($_POST["izbor$pitanje-$odgovor"]);
						if ($answer->selected && $answer->allowsWritein)
							$answer->writeInReponse = $_POST["dopis$pitanje-$odgovor"];
						else
							$answer->writeInReponse = ""; // Initialize field
					}
				}

				// Za naslov i podnaslov ne radimo ništa ;)
			}

		} else { // odbija učestvovati u anketi
			foreach($poll->questions as &$question)
				$question->answered = false;
		}
		
		// We must manually add token to poll object
		if (!property_exists($poll, "result"))
			$poll->result = new stdClass();
		$poll->result->token = $token;
		
		$result = api_call("poll/$id_ankete/submit", $poll, "POST");
		if ($_api_http_code == "201") {
			zamgerlog("popunjena anketa", 2);
			zamgerlog2("uspjesno popunjena anketa (token)", $id_ankete);

			?>
			<center>
				<p> Hvala na ispunjavanju ankete. </p>
				<a href="index.php">Nazad na početnu stranicu</a>
			</center>
			<?
		} else if ($_api_http_code == "403" && $result['message'] == "Student already completed this poll") {
			?>
			<center>
				<? 	niceerror("Već ste jednom popunili ovu anketu"); ?>
				<p> Ne možete dva puta popuniti anketu za isti predmet. Ako je ovo prvi put da popunjavate anketu za ovaj predmet, moguće da ovu grešku vidite zato što ste dvaput kliknuli na dugme za slanje ili zato što ste koristili dugme Back Vašeg web preglednika. </p>
				<a href="index.php">Nazad na početnu stranicu</a>
			</center>
			<?
		} else {
			?>
			<center>
				<? 	niceerror("Neuspješno ispunjavanje ankete: " . $result['message']); ?>
				<p> Predlažemo da pokušate ponovo. </p>
				<a href="index.php">Nazad na početnu stranicu</a>
			</center>
			<?
			zamgerlog2("neuspješno popunjavanje ankete", $id_ankete);
			api_report_bug($result, $poll);
		}
		return;
	}


	//  Prikaz forme za anketu
	else {
		if ($predmet==0) { // ovo se može desiti samo ako je preview!
			$naziv_predmeta = "NAZIV PREDMETA";
		} else {
			$naziv_predmeta = getCourseName($predmet);
		}
		
		?>
		<center>
			<h2><?=$naziv_ankete?> (<?=$naziv_predmeta?>)</h2>
		</center>
		<?=genform("POST")?>
		<input type="hidden" name="akcija" value="finish">
		<input type="hidden" name="token" value="<?=$token?>">
		<table align="center" cellpadding="4" border="0" >
			<tr>
				<td colspan = '7'>
					<?=$opis_ankete?>
					<br />&nbsp;<br />
				</td>
			</tr>
			<tr>
				<td colspan = '7' align="center">
					<input type="checkbox" name="odbija" value="da"> Odbijam da učestvujem u anketi za ovaj predmet.
					<br />
					&nbsp;<br />
					<i><font color="#999999">U slučaju da izaberete ovu opciju, biće evidentirano da je X studenata odbilo da učestvuje u anketi,<br />
					vaš kod za anketu će biti poništen a odgovori dati ispod biće zanemareni.<br />
					U polju za komentar možete napisati zašto ste odbili učestvovati u anketi.</font></i><br />
					&nbsp;<br />
				</td>
			</tr>

			<?

			foreach($poll->questions as $q) {
				$ans = [];
				if ($q->type == 3 || $q->type == 4) $ans = $q->answers;
				ubaci_pitanje($q->id, $q->type, $q->text, $boja, $ans);
				if ($boja == "#FFFFFF") $boja = "#CCCCFF"; else $boja = "#FFFFFF";
				// Resetujemo boju poslije naslova
				if ($q->type==5 || $q->type==6) $boja = "#FFFFFF";
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
	}
	
}//  ----------------- KRAJ FUNKCIJE PUBLIC_ANKETA -------------


// Nova pomoćna funkcija, samo ubacuje pitanje :)
function ubaci_pitanje($id, $type, $text, $bgcolor, $answers) {
	//print "id $id type $type text $text bgcolor $bgcolor answers $answers<br>\n";
	$text = nl2br($text);

	// Tip 1: rank pitanje
	if ($type==1) {
		?>
		<tr bgcolor="<?=$bgcolor?>">
			<td><?=$text?></td>
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
	if ($type==2) {
		?>
		<tr bgcolor="<?=$bgcolor?>">
			<td colspan='7' align='center'>
				<p><?=$text?></p>
				<textarea name="komentar<?=$id?>" rows="7" cols="40"></textarea>
			</td>
		</tr>
		<?
	}

	// Tip 3: MCSA
	// Tip 4: MCMA
	if ($type==3 || $type==4) {
		?>
		<tr bgcolor="<?=$bgcolor?>">
			<td colspan="7">
				<p><?=$text?></p>
				<?

				// Spisak izbora
				foreach ($answers as $answer) {
					$aid = $answer->id;
					$atext = $answer->text;
					if ($answer->allowsWriteIn)
						$dopis = " (dopisati): <input type=\"text\" size=\"30\" name=\"dopis$id-$aid\">";
					else $dopis="";
	
					if ($type==3) { // radio button
						print "<input type='radio' name='izbor$id' value='$aid'> $atext$dopis<br>\n";
					} else {
						print "<input type='checkbox' name='izbor$id-$aid'> $atext$dopis<br>\n";
					}
				}
				?>
			</td>
		</tr>
		<?
	}

	// Tip 5: Naslov
	if ($type==5) {
		$text = strtoupper($text);
		$text = str_replace(array("č","ć","š","đ","ž"), array("Č","Ć","Š","Đ","Ž"), $text);
		?>
		<tr><td>&nbsp;</td></tr>
		<tr bgcolor="#CCCCCC">
			<td colspan="7"><?=$text?></td>
		</tr>
		<?
	}

	// Tip 6: Podnaslov
	if ($type==6) {
		?>
		<tr>
			<td colspan="7"><i><font color="#999999"><?=$text?></font></i></td>
		</tr>
		<?
	}
}


?>
