<?

// PUBLIC/ANKETA - stranica za ispunjavanje ankete

function public_anketa() {

	global $userid,$user_siteadmin,$user_studentska,$user_nastavnik;
	global $_lv_; 


	// Predmet i a.g. mogu biti opcionalno parametri
	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);


	// Akcija za preview ankete, provjeravamo privilegije
	if ($_GET['akcija'] == "preview") {
		$ok = false;
		if ($user_studentska || $user_siteadmin) $ok = true;

		if ($predmet>0 && $ag>0 && $user_nastavnik) {
			$q10 = myquery("select nivo_pristupa from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
			if (mysql_num_rows($q10)>0 && mysql_result($q10,0,0)!="asistent") $ok = true;
		}

		if (!$ok) {
			zamgerlog("preview ankete privilegije",3); // 3: error
			zamgerlog2("preview ankete privilegije");
			biguglyerror("Pristup nije dozvoljen.");
			return;
		}
	}

	// Da li je dat ID ankete kao parametar?
	if (isset($_REQUEST['anketa'])) {
		$id_ankete = intval($_REQUEST['anketa']);

	} else {
		// Nije, uzimamo trenutno aktivnu anketu
		if ($predmet>0) 
			// Možda je anketa samo za jedan predmet?
			$upit = "select aa.id from anketa_anketa as aa, anketa_predmet as ap where aa.id=ap.anketa and ap.aktivna=1 and (ap.predmet=$predmet or ap.predmet IS NULL) and ap.akademska_godina=$ag order by aa.id desc";
		else
			$upit = "select aa.id from anketa_anketa as aa, akademska_godina as ag, anketa_predmet as ap where aa.id=ap.anketa and ap.aktivna=1 and aa.akademska_godina=ag.id and ag.aktuelna=1 order by aa.id desc";

		$q20 = myquery($upit);
		if (mysql_num_rows($q20)==0) {
			biguglyerror("Anketa trenutno nije aktivna. A");
			return;
		}
		$id_ankete = mysql_result($q20,0,0);
	}

	// Metapodaci o anketi
	$q30 = myquery("select naziv, UNIX_TIMESTAMP(datum_otvaranja), UNIX_TIMESTAMP(datum_zatvaranja), opis from anketa_anketa where id=$id_ankete");
	if (mysql_num_rows($q30)==0) {
		biguglyerror("Ne postoji anketa sa tim IDom");
		zamgerlog2("nepostojeci ID ankete");
		return;
	}
	$naziv_ankete = mysql_result($q30,0,0);
	$otvaranje = mysql_result($q30,0,1);
	$zatvaranje = mysql_result($q30,0,2);
	$opis_ankete = nl2br(mysql_result($q30,0,3));
	

	// Da li je istekao rok?
	if ($_GET['akcija'] != "preview" && (time () > $zatvaranje || time () < $otvaranje)) {
		if (time() < $otvaranje)
			print "<center><h1><font color=\"#00AA00\">Anketa će postati aktivna ".date("d. m. Y. \u H:i", $otvaranje)."</font></h1></center>";
		else
			biguglyerror("Anketa trenutno nije aktivna B");
		return;
	}

	// Da li je anketa aktivna? 
	// Ako je aktivna samo za određeni predmet, on mora biti zadat kao parametar
	$q40 = myquery("select aktivna, semestar from anketa_predmet where anketa=$id_ankete and ((predmet=$predmet and akademska_godina=$ag) or predmet IS NULL)");
	$anketa_aktivna = mysql_result($q40,0,0);
	$anketa_semestar = mysql_result($q40,0,1);
	if ($_GET['akcija'] != "preview" && (mysql_num_rows($q40)<1 || $anketa_aktivna==0)) {
		biguglyerror("Anketa trenutno nije aktivna!");
		return;
	}


	// U slučaju preview, ne radimo ništa
	if ($_GET['akcija'] == "preview") {
		$zavrsena = 'N';
	

	// Ako je student unio hash code, koristimo ga radi anonimnosti rezultata
	} else if (isset($_POST['hash_code']) && $_POST['hash_code'] != "") {
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

		$hash_code = my_escape($_POST['hash_code']);

		// Provjeravamo da li kod postoji i da li je već iskorišten (polje zavrsena)
		$q50 = myquery("SELECT id, predmet, zavrsena FROM anketa_rezultat WHERE unique_id='$hash_code' and anketa=$id_ankete");
		if (mysql_num_rows($q50)==0) {
			// Dati hash ne postoji u bazi tj. student pokušava da izmisli hash :P
			?>
			<center>
				<p>Greška: neispravan kod '<?=$hash_code?>'.</p>
				<a href="index.php">Nazad na početnu stranicu</a>
			</center>
			<?
			zamgerlog2("ilegalan hash code", intval($id_ankete), 0, 0, $hash_code);
			return;
		}
		if (mysql_num_rows($q50)>1) {
			// Hash nije unique!?
			zamgerlog2("hash nije unique", $id_ankete, 0, 0, $hash_code);
			return;
		}
	
		$id_rezultata = mysql_result($q50,0,0);
		$predmet = mysql_result($q50,0,1);
		$zavrsena = mysql_result($q50,0,2);


	// Student je logiran, pa ćemo pokušati iskoristiti njegove kredencijale
	} else if ($userid>0) {
		// Korisnik nije izabrao predmet, pa je najvjerovatnije pokušao ući na anketu bez logouta
		if ($predmet==0 || $ag==0) {
			biguglyerror("Niste napravili logout niti izabrali predmet!");
			zamgerlog2("nije definisan predmet a korisnik je logiran");
			return;
		}
		
		// Da li student sluša predmet?
		$q60 = myquery("select pk.studij, pk.semestar from student_predmet as sp, ponudakursa as pk where sp.student=$userid and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
		if (mysql_num_rows($q60)<1) {
			zamgerlog("student ne slusa predmet pp$predmet", 3);
			zamgerlog2("student ne slusa predmet", $predmet, $ag);
			biguglyerror("Niste upisani na ovaj predmet");
			return;
		}

		// Određujemo studij i semestar radi insertovanja u tabelu anketa_rezultat
		$studij = mysql_result($q60,0,0);
		$semestar = mysql_result($q60,0,1);
		
		if ($semestar % 2 != $anketa_semestar) {
			biguglyerror("Predmet nije u odgovarajućem semestru");
			return;
		}

		$q70 = myquery("SELECT zavrsena, anketa_rezultat FROM anketa_student_zavrsio WHERE student=$userid AND predmet=$predmet AND akademska_godina=$ag AND anketa=$id_ankete");

		// Kreiramo zapise u tabelama anketa_rezultat i anketa_student_zavrsio
		if (mysql_num_rows($q70)==0) {
			$q75 = myquery("SELECT lg.id FROM student_labgrupa as sl, labgrupa as lg WHERE sl.student=$userid AND sl.labgrupa=lg.id AND lg.predmet=$predmet AND lg.akademska_godina=$ag AND lg.virtualna=0");
			if (mysql_num_rows($q75)==1) 
				$labgrupa = mysql_result($q75,0,0);
			else
				$labgrupa = 0;
			$q90 = myquery("INSERT INTO anketa_rezultat SET anketa=$id_ankete, zavrsena='N', predmet=$predmet, unique_id='', akademska_godina=$ag, studij=$studij, semestar=$semestar, student=NULL, labgrupa=$labgrupa");
			$id_rezultata = mysql_insert_id();

			$q80 = myquery("INSERT INTO anketa_student_zavrsio SET student=$userid, predmet=$predmet, akademska_godina=$ag, anketa=$id_ankete, zavrsena='N', anketa_rezultat=$id_rezultata");
			$q70 = myquery("SELECT zavrsena FROM anketa_student_zavrsio WHERE student=$userid AND predmet=$predmet AND akademska_godina=$ag AND anketa=$id_ankete");
			$zavrsena = 'N';

		} else {
			$zavrsena     = mysql_result($q70,0,0);
			$id_rezultata = mysql_result($q70,0,1);
		}

		if (!isset($_POST['akcija'])) $_POST['akcija'] = "prikazi"; // Možemo odmah prikazati anketu


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
		zamgerlog("anketa vec popunjena", 3);
		zamgerlog2("anketa vec popunjena", $predmet, $ag);
		return;
	}


	// Završetak i prijem ankete
	if ($_POST['akcija'] == "finish" && check_csrf_token()) {
		
		if ($_POST['odbija'] != "da") {
			$q300 = myquery("select id, tip_pitanja from anketa_pitanje where anketa=$id_ankete order by id");
			while ($r300 = mysql_fetch_row($q300)) {
				$pitanje = $r300[0];
				$tip = $r300[1];
				
				if ($tip == 1) { // Rank pitanje
					$izbor = intval($_POST['izbor'.$pitanje]);
					if ($izbor > 0) { // Odgovor N/A ima vrijednost 0
						$q310 = myquery("insert into anketa_odgovor_rank set rezultat=$id_rezultata, pitanje=$pitanje, izbor_id=$izbor");
					}
				}
				
				if ($tip == 2) { // Esejsko pitanje
					$komentar = my_escape($_POST['komentar'.$pitanje]);
					if (preg_match("/\w/", $_POST['komentar'.$pitanje]))  // Ima li slova u komentaru?
						$q320 = myquery("insert into anketa_odgovor_text set rezultat=$id_rezultata, pitanje=$pitanje, odgovor='$komentar'");
				}
				
				if ($tip == 3) { // MCSA
					$izbor = intval($_POST['izbor'.$pitanje]);
					$q330 = myquery("select dopisani_odgovor from anketa_izbori_pitanja where pitanje=$pitanje and id=$izbor");
					if (mysql_result($q330,0,0)==1) {
						$dopisani_odgovor = my_escape($_POST["dopis$pitanje-$odgovor"]);
						$q590 = myquery("insert into anketa_odgovor_dopisani set rezultat=$id_rezultata, pitanje=$pitanje, odgovor='$dopisani_odgovor'");
					}
					$q590 = myquery("insert into anketa_odgovor_izbori set rezultat=$id_rezultata, pitanje=$pitanje, izbor_id=$izbor");
				}
				
				if ($tip == 4) { // MCMA
					$izbor = intval($_POST['izbor'.$pitanje]);
					$q340 = myquery("select id, dopisani_odgovor from anketa_izbori_pitanja where pitanje=$pitanje");
					while ($r340 = mysql_fetch_row($q340)) {
						$odgovor = $r340[0];
						if ($_POST["izbor$pitanje-$odgovor"]) {
							if ($r340[1] == 1) { // Odgovor je dopisani
								$dopisani_odgovor = my_escape($_POST["dopis$pitanje-$odgovor"]);
								$q590 = myquery("insert into anketa_odgovor_dopisani set rezultat=$id_rezultata, pitanje=$pitanje, odgovor='$dopisani_odgovor'");
							}
							$q590 = myquery("insert into anketa_odgovor_izbori set rezultat=$id_rezultata, pitanje=$pitanje, izbor_id=$odgovor");
						}
					}
				}

				// Za naslov i podnaslov ne radimo ništa ;)
			}

		} else { // odbija učestvovati u anketi
			$q300 = myquery("select id, tip_pitanja from anketa_pitanje where anketa=$id_ankete order by id");
			while ($r300 = mysql_fetch_row($q300)) {
				$pitanje = $r300[0];
				$tip = $r300[1];
				if ($tip == 2) { // Esejsko pitanje
					$komentar = my_escape($_POST['komentar'.$pitanje]);
					if (preg_match("/\w/", $_POST['komentar'.$pitanje]))  // Ima li slova u komentaru?
						$q320 = myquery("insert into anketa_odgovor_text set rezultat=$id_rezultata, pitanje=$pitanje, odgovor='$komentar'");
				}
			}
		}
		
		// nakon uspjesnog ispunjenja ankete postaviti i polje zavrsena na true u tabeli razultati
		$q600 = myquery("update anketa_rezultat set zavrsena='Y' where id=$id_rezultata");
		
		// Za logirane studente moramo ažurirati i tabelu anketa_student_zavrsio
		if ($userid>0) {
			$q610 = myquery("UPDATE anketa_student_zavrsio SET zavrsena='Y', anketa_rezultat=0 WHERE student=$userid AND predmet=$predmet AND akademska_godina=$ag AND anketa=$id_ankete");
			// Brišemo vezu na tabelu anketa_rezultat da se ne bi znalo ko je šta popunio :)
		}

		zamgerlog("popunjena anketa za predmet pp$predmet", 2);
		zamgerlog2("uspjesno popunjena anketa", $predmet, $ag);

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
			$q190 = myquery("select naziv from predmet where id=$predmet");
			$naziv_predmeta = mysql_result($q190,0,0);
		}
		
		?>
		<center>
			<h2><?=$naziv_ankete?> (<?=$naziv_predmeta?>)</h2>
		</center>
		<?=genform("POST")?>
		<input type="hidden" name="akcija" value="finish">
		<input type="hidden" name="hash_code" value="<?=$hash_code?>">
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



			$q200 = myquery("select id, tip_pitanja, tekst from anketa_pitanje where anketa=$id_ankete order by id");
			$boja = "#FFFFFF";
			while ($r200 = mysql_fetch_row($q200)) {
				ubaci_pitanje($r200[0], $r200[1], $r200[2], $boja);
				if ($boja == "#FFFFFF") $boja = "#CCCCFF"; else $boja = "#FFFFFF";
				// Resetujemo boju poslije naslova
				if ($r200[1]==5 || $r200[1]==6) $boja = "#FFFFFF";
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


// Nova pomoćna funkcija, samo ubacuje pitanje :)
function ubaci_pitanje($id, $tip, $tekst, $bgcolor) {
	$tekst = nl2br($tekst);

	// Tip 1: rank pitanje
	if ($tip==1) {
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
	if ($tip==2) {
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
	if ($tip==3 || $tip==4) {
		?>
		<tr bgcolor="<?=$bgcolor?>">
			<td colspan="7">
				<p><?=$tekst?></p>
				<?

				// Spisak izbora
				$q300 = myquery("select id, izbor, dopisani_odgovor from anketa_izbori_pitanja where pitanje=$id");
				while ($r300 = mysql_fetch_row($q300)) {
					if ($r300[2]==1)
						$dopis = " (dopisati): <input type=\"text\" size=\"30\" name=\"dopis$id-$r300[0]\">";
					else $dopis="";
	
					if ($pitanje['tip_pitanja']==3) { // radio button
						print "<input type='radio' name='izbor$id' value='$r300[0]'> $r300[1]$dopis<br>\n";
					} else {
						print "<input type='checkbox' name='izbor$id-$r300[0]'> $r300[1]$dopis<br>\n";
					}
				}
				?>
			</td>
		</tr>
		<?
	}

	// Tip 5: Naslov
	if ($tip==5) {
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
	if ($tip==6) {
		?>
		<tr>
			<td colspan="7"><i><font color="#999999"><?=$tekst?></font></i></td>
		</tr>
		<?
	}
}


?>
