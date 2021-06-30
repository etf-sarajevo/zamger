<?

// NASTAVNIK/TIP - modul koji ce omogućiti definisanja sistema bodovanja na predmetu



function nastavnik_tip() {
	global $_api_http_code;
	
	// Parametri
	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);
	
	$course = api_call("course/$predmet/$ag", [ "resolve" => ["Activity", "CourseActivity"] ] );
	
	// Naziv predmeta
	$predmet_naziv = $course['courseName'];
	if (!$course['AcademicYear']['isCurrent']) $predmet_naziv .= " (" . $course['AcademicYear']['name'] . ")";
	
	// Da li korisnik ima pravo ući u modul?
	
	if ($_api_http_code == "403") {
		zamgerlog("nastavnik/kvizovi privilegije (predmet pp$predmet)",3);
		zamgerlog2("nije nastavnik na predmetu", $predmet, $ag);
		biguglyerror("Nemate pravo pristupa ovoj opciji");
		return;
	}
	
	// Useful codebook
	$activityTypes = api_call("zamger/activities")["results"];
	
	
	
	?>

	<p>&nbsp;</p>
	<style>
		label {
			width: 100px;
			display: inline-block;
			padding-top: 10px;
		}
		span.opis {
			margin-left: 100px;
			color: #aaa;
		}
	</style>

	<p><h3><?=$predmet_naziv?> - Aktivnosti na predmetu</h3></p>
	
	<?
	
	if (!$course['AcademicYear']['isCurrent']) {
		?>
		<hr>
		<p><font color="red">Odabrana akademska godina nije aktivna u Zamgeru.</font> Sve promjene koje vršite primjenjuju se retroaktivno na akademsku <?=$course['AcademicYear']['name'] ?>!</p>
		<hr>
		<?
	}
	
	$foundActivity = false;
	if (param('akcija') != "" && param('akcija') != "dodaj") {
		$aktivnost = int_param('aktivnost');
		foreach ($course['activities'] as $activity) {
			if ($activity['id'] == $aktivnost)
				$foundActivity = $activity;
		}
		
		if ($foundActivity == false) {
			niceerror("Nepoznata aktivnost $aktivnost");
			return;
		}
	}
	
	if (param('akcija') == "opcije") {
		if (param('subakcija') != "") {
			if ($foundActivity['forced']) {
				niceerror("Aktivnost je forsirana od administratora i ne može se mijenjati");
				return;
			}
			
			$cact = array_to_object( $foundActivity );
			// We add these afterwards because array_to_object would convert them to stdClass and they need to remain as arrays
			$cact->options = $foundActivity['options'];
			$cact->conditionalActivities = $foundActivity['conditionalActivities'];
		}
		
		if (param('subakcija') == "dodaj_uslovnu" && check_csrf_token()) {
			$found = false;
			$new_conditional_id = int_param("uslovna_aktivnost");
			foreach ($cact->conditionalActivities as $cond)
				if ($cond['id'] == $new_conditional_id)
					$found = true;
			if (!$found)
				$cact->conditionalActivities[] = [ "id" => $new_conditional_id];
		}
		
		if (param('subakcija') == "obrisi_uslovnu" /*&& check_csrf_token()*/) { // TODO: move to POST request
			$found = false;
			$conditional_id = int_param("uslovna_aktivnost");
			foreach ($cact->conditionalActivities as $key => $cond)
				if ($cond['id'] == $conditional_id)
					unset($cact->conditionalActivities[$key]);
		}
		
		if (param('subakcija') == "dodaj_min_bodove" && check_csrf_token()) {
			if (floatval(param('min_bodovi')) == 0)
				unset($cact->options['MinScore']);
			else
				$cact->options['MinScore'] = floatval(param('min_bodovi'));
		}
		
		if (param('subakcija') == "postavi_opcije" && check_csrf_token()) {
			if ($foundActivity['Activity']['id'] == 2) {
				if (isset($_REQUEST['StudentSubmit']))
					$cact->options['StudentSubmit'] = 1;
				else
					unset($cact->options['StudentSubmit']);
			}
			
			if ($foundActivity['Activity']['id'] == 8) {
				if (isset($_REQUEST['AnnulExam']))
					$cact->options['AnnulExam'] = true;
				else
					unset($cact->options['AnnulExam']);
				if (isset($_REQUEST['Integral'])) {
					$int = "";
					foreach ($_REQUEST as $key => $value) {
						if (starts_with($key, "integralni-")) {
							if ($int != "") $int .= "+";
							$int .= intval(substr($key, 11));
						}
					}
					if ($int == "") {
						niceerror("Ne možete postaviti Integralni ispit koji ne obuhvata niti jedan drugi ispit");
						?>
						<p>Integralni ispit je ispit koji objedinjuje rezultate sa nekoliko drugih ispita. Obavezno je da odmah odaberete koji su to ispiti.</p>
						<p><a href="javascript:window.history.back();">Nazad</a></p>
						<?
						return;
					}
					$cact->options['Integral'] = $int;
				} else
					unset($cact->options['Integral']);
			}
			
			if ($foundActivity['Activity']['id'] == 9) {
				if (param('prisustvo') == "binarno") {
					unset($cact->options['Proportional']);
					$cact->options['MinAbsence'] = int_param('max_izostanaka');
				}
				else if (param("prisustvo") == "proporcionalno") {
					$cact->options['Proportional'] = true;
					if (int_param('ukupno_casova') > 0)
						$cact->options['TotalClasses'] = int_param('ukupno_casova');
					else
						unset($cact->options['TotalClasses']);
					if (int_param('ukupno_casova') > 0)
						$cact->options['TotalClasses'] = int_param('ukupno_casova');
					else
						unset($cact->options['TotalClasses']);
					if (int_param('gornja_granica') > 0) {
						$cact->options['AbsenceMinScore'] = int_param('gornja_granica');
						$cact->options['AbsenceMaxScore'] = int_param('donja_granica');
					} else {
						unset($cact->options['AbsenceMinScore']);
						unset($cact->options['AbsenceMaxScore']);
					}
				}
			}
		}
		
		// Završne operacije u vezi primjene opcija
		if (param('subakcija') != "") {
			$result = api_call("course/$predmet/$ag/activity/$aktivnost", $cact, "PUT");
			if ($_api_http_code == "201") {
				nicemessage("Opcije aktivnosti su promijenjene");
				?>
				<script language="JavaScript">
                    setTimeout(function() { location.href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>'; }, 1000);
				</script>
				<?
			}
			else if ($_api_http_code == "401") {
				niceerror("Nemate permisije za izmjenu aktivnosti");
				?>
				<p>Samo korisnici sa nastavničkim nivoom pristupa na predmetu mogu mijenjati aktivnosti.</p>
				<?
			}
			else if ($_api_http_code == "400" && starts_with($result['message'], "Circular dependency")) {
				niceerror("Stvorili ste cirkularnu međuzavisnost");
				$act = substr($result['message'], strlen("Circular dependency with "));
				?>
				<p>Pokušali ste da dodate <?=$act?> kao uslov za aktivnost <?=$foundActivity['name']?>, međutim <?=$foundActivity['name']?> je već uslov za <?=$act?>!</p>
				<p><a href="?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>">Nazad</a></p>
				<?
			}
			else {
				niceerror("Neuspješna izmjena aktivnosti");
				api_report_bug($result, $cact);
			}
			return;
		}
		
		
		// Pronalazimo opciju bodovi
		$min_bodovi = 0;
		foreach($foundActivity['options'] as $key => $value) {
			if ($key == "MinScore") $min_bodovi = $value;
		}
		
		// Pronalazimo naziv tipa aktivnosti
		foreach($activityTypes as $type) {
			if ($type['id'] == $foundActivity['Activity']['id'])
				$typeName = $type['name'];
		}
		if ($foundActivity['Activity']['id'] == 0)
			$typeName = "Fiksni bodovi";
			
			?>
		<h4>Uslovi za pristupanje aktivnosti &quot;<?=$foundActivity['name']?>&quot;</h4>
		<p>Položene aktivnosti:</p>
		<ul>
		<?
		if (count($foundActivity['conditionalActivities']) == 0)
			print "<li>Nijedna</li>";
		foreach($foundActivity['conditionalActivities'] as $cond) {
			foreach ($course['activities'] as $activity) {
				if ($cond['id'] == $activity['id'])
					print "<li>" . $activity['name'] . " (<a href='" . genuri() . "&subakcija=obrisi_uslovnu&uslovna_aktivnost=" . $cond['id'] .  "'>obriši</a>)</li>\n";
			}
		}
		?>
		</ul>
		<?=genform("POST")?>
			<input type="hidden" name="subakcija" value="dodaj_uslovnu">
			<p>Dodaj uslovnu aktivnost: <select name="uslovna_aktivnost">
					<?
					foreach ($course['activities'] as $activity) {
						if ($activity['id'] != $foundActivity['id'])
							print "<option value='" . $activity['id'] . "'>" . $activity['name'] . "</option>\n";
					}
					?>
				</select> <input type="submit" value="Dodaj"></p>
		</form>
		
		<?=genform("POST")?>
		<input type="hidden" name="subakcija" value="dodaj_min_bodove">
		<p>Ostvaren minimalan broj bodova: <input type="text" name="min_bodovi" value="<?=$min_bodovi?>"> <input type="submit" value="Postavi"></p>
		</form>
		
		<p>&nbsp;</p>
		
		<h4>Ostale opcije specifične za tip aktivnosti: <?=$typeName?></h4>
		
		<?=genform("POST")?>
		<input type="hidden" name="subakcija" value="postavi_opcije">
		<?
		
		if ($foundActivity['Activity']['id'] == 2) {
			if (array_key_exists("StudentSubmit", $foundActivity['options']) && $foundActivity['options']['StudentSubmit'])
				$sel = "CHECKED";
			else
				$sel = "";
			?>
			<p><input type="checkbox" name="StudentSubmit" value="1" <?=$sel?>> Slanje zadaća</p>
			<span class="opis">Ako je uključena opcija &quot;Slanje zadaća&quot; studenti imaju mogućnost da pošalju zadaću kroz Zamger, u suprotnom studenti mogu samo vidjeti bodove a vi imate mogućnost da unosite zadaće i bodove na uobičajen način.</span>
			<p>&nbsp;</p>
			<input type="submit" value="Promijeni">
			<?
		}
		
		if ($foundActivity['Activity']['id'] == 8) {
			if (array_key_exists("AnnulExam", $foundActivity['options']) && $foundActivity['options']['AnnulExam'])
				$selPonistavanje = "CHECKED";
			else
				$selPonistavanje = "";
			$integralni = [];
			if (array_key_exists("Integral", $foundActivity['options'])) {
				$sel = "CHECKED";
				$integralni = explode("+", $foundActivity['options']['Integral']);
			} else
				$sel = "";
			?>
			<script>
				function updateIntegralni() {
				
				}
			</script>
			
			<p><input type="checkbox" name="AnnulExam" value="1" <?=$selPonistavanje?>> Poništavanje ispita</p>
			<span class="opis">Ako je ova opcija odabrana, u zbiru bodova studenta se računa posljednji ostvareni rezultat. Ako nije odabrana, uzima se najbolji rezultat koji je student ostvario tokom tekuće akademske godine.</span>
			<p><input type="checkbox" name="Integral" value="1" <?=$sel?> onchange="updateIntegralni()"> Integralni ispit</p>
			<span class="opis">Integralni ispit se u ukupnom zbiru bodova računa umjesto pojedinačnih (parcijalnih) ispita koje odaberete.</span>
			<ul>Odaberite koje postojeće aktivnosti tipa &quot;Ispit&quot; objedinjuje ovaj integralni ispit (integralni ispit ne može obuhvatati druge integralne ispite):<br>
				<?
				foreach ($course['activities'] as $activity) {
					if ($activity['id'] != $foundActivity['id'] && $activity['Activity']['id'] == 8 && !array_key_exists("Integral", $activity['options'])) {
						if (in_array($activity['id'], $integralni)) $sel = "CHECKED"; else $sel = "";
						?>
						<input type="checkbox" name="integralni-<?=$activity['id']?>" <?=$sel?>> <?=$activity['name']?><br>
						<?
					}
				}
				
				?>
			</ul>
			<p>&nbsp;</p>
			<input type="submit" value="Promijeni">
			<?
		}
		
		if ($foundActivity['Activity']['id'] == 9) {
			if (array_key_exists("Proportional", $foundActivity['options']) && $foundActivity['options']['Proportional'])
				$type = 1;
			else
				$type = 0;
			
			?>
			<script>
				function updatePrisustvo() {
				    console.log("updatePrisustvo");
				    console.log(document.getElementById('binarno').checked);
				    if (document.getElementById('binarno').checked) {
                        document.getElementById('max_izostanaka').disabled = false;
                        document.getElementById('ukupno_casova').disabled = true;
                        document.getElementById('donja_granica').disabled = true;
                        document.getElementById('gornja_granica').disabled = true;
                    }
				    else {
                        document.getElementById('max_izostanaka').disabled = true;
                        document.getElementById('ukupno_casova').disabled = false;
                        document.getElementById('donja_granica').disabled = false;
                        document.getElementById('gornja_granica').disabled = false;
                    }
				}
			</script>
			
			
			<p>Način bodovanja prisustva:<br>
				<input type="radio" name="prisustvo" id="binarno" value="binarno" <? if ($type == 0) print "CHECKED"?> onchange="updatePrisustvo();"> Binarno<br>
				<span class="opis">Student može dobiti ili sve bodove za prisustvo ili ništa, ovisno o tome koliko ima izostanaka.</span><br>
				<ul>Maksimalan dozvoljen broj izostanaka: <input type="text" name="max_izostanaka" id="max_izostanaka" value="<?=$foundActivity['options']['MinAbsence']?>" <? if ($type == 1) print "DISABLED"; ?>></ul>
				<input type="radio" name="prisustvo" value="proporcionalno" <? if ($type == 1) print "CHECKED"?> onchange="updatePrisustvo();"> Proporcionalno<br>
				<span class="opis">Student dobija broj bodova porporcionalan broju prisustva/izostanaka.</span>
				<ul>Ukupan broj časova: <input type="text" name="ukupno_casova" id="ukupno_casova" value="<?=$foundActivity['options']['TotalClasses']?>" <? if ($type == 0) print "DISABLED"; ?>><br>
					<span class="opis">Ako je definisan ukupan broj časova, student kreće sa maksimalnim brojem bodova za prisustvo i prilikom svakog izostanka gubi (Ukupno bodova/Ukupno časova) bodova. Ako se registruje veći broj časova od ovog, suvišni časovi će se tretirati kao nadoknada tj. student na njima može samo popraviti bodove. Ako polje "Ukupan broj časova" ostavite na nuli tj. prazno, svaki put kada bude registrovan novi čas vrši se rekalkulacija bodova prema trenutnom broju časova.</span><br>
					Donja granica broja izostanaka: <input type="text" name="donja_granica" id="donja_granica" value="<?=$foundActivity['options']['AbsenceMaxScore']?>" <? if ($type == 0) print "DISABLED"; ?>> Gornja granica: <input type="text" name="gornja_granica" id="gornja_granica" value="<?=$foundActivity['options']['AbsenceMinScore']?>" <? if ($type == 0) print "DISABLED"; ?>><br>
					<span class="opis">Ako su definisane donja i gornja granica broja izostanaka, student sa manje izostanaka od donje granice uvijek ima maksimalan broj bodova, a sa više izostanaka od gornje uvijek ima minimalan broj bodova, a linearno proporcionalan model se odnosi na broj izostanaka između ove dvije vrijednost. Ako ne želite da koristite ovu mogućnost, ostavite oba broja na nuli tj. praznom polju.</span><br>
				</ul>
			</p>
			<p>Obratite pažnju da ako je student istovremeno član više grupa (npr. grupa za vježbe i grupa "Svi studenti"), bodovi se odnose prema zbirnom stanju u svim grupama. Ako želite da odvojite bodove npr. za predavanja i vježbe, trebate kreirati dvije aktivnosti "Prisustvo - predavanja" i "Prisustvo - vježbe".</span>
			</p>
			<p>&nbsp;</p>
			<p><input type="checkbox" name="sa_kvizovima"> Poveži prisustvo sa kvizovima<br>
				<span class="opis">Ova opcija vam omogućuje da prilikom kreiranja časa aktivirate kviz koji student mora popuniti na početku časa. Vi na pogledu grupe možete vidjeti da li je student uspješno uradio kviz i na osnovu toga odlučiti da li računate da je student prisutan ili ne.
			</p>
			<p>&nbsp;</p>
			<input type="submit" value="Promijeni">
			<?
		}
		?>
		<button type="button" onclick="location.href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>';">Nazad</button>
		</form>
		<?
		return;
	}
	
	if (param('akcija') == "ukloni") {
		if (param('subakcija') == "potvrda" && check_csrf_token()) {
			$result = api_call("course/$predmet/$ag/activity/$aktivnost", [], "DELETE");
			if ($_api_http_code == "204") {
				nicemessage("Aktivnost " . $foundActivity['name'] . " je uklonjena sa predmeta");
				?>
				<script language="JavaScript">
                    setTimeout(function() { location.href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>'; }, 1000);
				</script>
				<?
			}
			else if ($_api_http_code == "401") {
				niceerror("Nemate permisije za brisanje aktivnosti");
				?>
				<p>Samo korisnici sa nastavničkim nivoom pristupa na predmetu mogu mijenjati aktivnosti.</p>
				<?
			}
			else if ($_api_http_code == "400" && starts_with($result['message'], "Activity not removable")) {
				if (starts_with($result['message'], "Activity not removable because it is conditional for activity")) {
					$act = substr($result['message'], strlen("Activity not removable because it is conditional for activity"));
					niceerror("Aktivnost se ne može ukloniti jer je uslovna za aktivnost $act");
					print "<p>Najprije izmijenite aktivnost $act tako što ćete ukloniti aktivnost " . $foundActivity['name'] . " iz spiska njenih uslova, zatim možete ponovo pokušati ukloniti aktivnost " . $foundActivity['name'] . ".</p>";
				} else {
					niceerror("Aktivnost se ne može ukloniti iz nekog razloga");
				}
				?>
				<p><a href="?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>">Nazad</a></p>
				<?
			}
			else if ($_api_http_code == "400" && starts_with($result['message'], "There are integral exams")) {
				niceerror("Postoje integralni ispiti koji obuhvataju ovaj ispit");
				?>
				<p>Najprije editujte aktivnosti tipa integralnog ispita koje obuhvataju <?=$foundActivity['name']?> kao parcijalni ispit, a zatim pokušajte ponovo ukloniti <?=$foundActivity['name']?>.</p>
				<p><a href="?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>">Nazad</a></p>
				<?
			}
			else {
				niceerror("Neuspješno uklanjanje aktivnosti");
				if ($result['message']) nicemessage("Razlog: " . $result['message']);
				api_report_bug($result, []);
			}
			return;
		}
		
		?>
			<?=genform("POST")?>
		<input type="hidden" name="subakcija" value="potvrda">
		<h3>Potvrdite brisanje</h3>
		<p>Da li zaista želite da uklonite aktivnost <b><?=$foundActivity['name']?></b> sa predmeta <?=$predmet_naziv?>?</p>
		<?
		$dodatak = "";
		if ($foundActivity['points'] > 0) {
			?>
			<p>Za ovu aktivnost su bili predviđeni i određeni bodovi. Svi bodovi koje su studenti do sada ostvarili kroz ovu aktivnost biće trajno izgubljeni i njihov ukupan broj bodova umanjen za taj iznos!</p>
			<?
			$dodatak = "i gubitak bodova";
		}
		?>
		<input type="submit" value=" Povrđujem uklanjanje aktivnosti <?=$dodatak?> ">
		<button type="button" onclick="location.href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&akcija=izmijeni&aktivnost=<?=$aktivnost?>';">Nazad</button>
		</form>
		<?
		return;
	}
	
	if (param('akcija') == "izmijeni_potvrda" || param('akcija') == "dodaj") {
		$naziv = param('naziv');
		$tip = int_param('tip_aktivnosti');
		$abbrev = param('abbrev');
		$poena = floatval(param('poena'));
		$prolaz = floatval(param('prolaz'));
		if ($_REQUEST['obavezna']) $obavezna=true; else $obavezna=false;
		
		if (empty(trim($naziv))) {
			foreach($activityTypes as $type) {
				if ($type['id'] == $tip)
					$naziv = $type['name'];
			}
		}
		
		foreach($course['activities'] as $activity) {
			if ($activity['name'] == $naziv && $activity['id'] != $aktivnost) {
				niceerror("Naziv aktivnosti $naziv je zauzet");
				return;
			}
		}
		
		if (empty(trim($abbrev)))
			$abbrev = $naziv;
		if ($prolaz > $poena) {
			niceerror("Broj poena za prolaz ne može biti veći od ukupnog broja poena");
			return;
		}
		
		if ($foundActivity && $foundActivity['forced']) {
			niceerror("Aktivnost je forsirana od administratora i ne može se mijenjati");
			return;
		}
		
		if ($foundActivity && $tip != $foundActivity['Activity']['id']) {
			if (param('fakat_potvrda') != 1) {
				?>
				<h2 style="color:red">Da li ste sigurni???</h2>
				<p>Izabrali ste promjenu tipa aktivnosti za aktivnost:<br><b><?=$naziv?></b><br>Promjenom tipa biće <i>trajno izgubljeni</i> svi podaci o rezultatu koji su studenti ostvarili na datoj aktivnosti.</p>
				<?
				$dodatak = "";
				if ($foundActivity['points'] > 0 && $tip != 0) {
					?><p>Postojeća aktivnost <?=$foundActivity['naziv']?> je imala predviđen određeni broj bodova. Bodovi koje su studenti do sada ostvarili će također biti izgubljeni! Predlažemo da sačuvate bodove koristeći opciju za izvoz izvještaja u Excel, a da zatim ih ponovo unesete. Razlog zašto se ovo dešava je što se način kako se bodovi računaju razlikuje od jednog do drugog tipa aktivnosti.</p>
					<?
					$dodatak = "i gubitak bodova";
				}
				
				?>
				<?=genform("POST")?>
				<input type="hidden" name="fakat_potvrda" value="1">
				<input type="submit" value=" Potvrđujem ovu izmjenu <?=$dodatak?> ">
				<button type="button" onclick="location.href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&akcija=izmijeni&aktivnost=<?=$aktivnost?>';">Nazad</button>
				</form>
				<?
				
				return;
			}
		}
		
		$cact = array_to_object( [ "name" => $naziv, "abbrev" => $abbrev, "Activity" => [ "id" => $tip ], "points" => $poena, "pass" => $prolaz, "mandatory" => $obavezna, "forced" => false ] );
		if ($foundActivity) {
			$cact->id = $aktivnost;
			// We add these afterwards because array_to_object would convert them to stdClass and they need to remain as arrays
			$cact->options = $foundActivity['options'];
			$cact->conditionalActivities = $foundActivity['conditionalActivities'];
		} else {
			$cact->options = [];
			$cact->conditionalActivities = [];
		}
		
		if (param('akcija') == "izmijeni_potvrda") {
			$result = api_call("course/$predmet/$ag/activity/$aktivnost", $cact, "PUT");
			if ($_api_http_code == "201") {
				nicemessage("Aktivnost je uspješno izmijenjena");
				?>
				<script language="JavaScript">
					setTimeout(function() { location.href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>'; }, 1000);
				</script>
				<?
			}
			else if ($_api_http_code == "401") {
				niceerror("Nemate permisije za izmjenu aktivnosti");
				?>
				<p>Samo korisnici sa nastavničkim nivoom pristupa na predmetu mogu mijenjati aktivnosti.</p>
				<?
			}
			else {
				niceerror("Neuspješna izmjena aktivnosti");
				api_report_bug($result, $cact);
			}
		} else {
			$result = api_call("course/$predmet/$ag/activity", $cact, "POST");
			if ($_api_http_code == "201") {
				nicemessage("Aktivnost je uspješno dodata");
				?>
				<script language="JavaScript">
                    setTimeout(function() { location.href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>'; }, 1000);
				</script>
				<?
			}
			else if ($_api_http_code == "401") {
				niceerror("Nemate permisije za dodavanje aktivnosti");
				?>
				<p>Samo korisnici sa nastavničkim nivoom pristupa na predmetu mogu mijenjati aktivnosti.</p>
				<?
			}
			else {
				niceerror("Neuspješno dodavanje aktivnosti");
				api_report_bug($result, $cact);
			}
		}
		return;
	}
	
	if (param('akcija') == "izmijeni") {
		?>
			<h3>Izmjena aktivnosti &quot;<?=$foundActivity['name']?>&quot;</h3>
	<form action="index.php" method="POST" class="aktivnosti">
		<input type="hidden" name="sta" value="nastavnik/tip">
		<input type="hidden" name="predmet" value="<?=$predmet?>">
		<input type="hidden" name="ag" value="<?=$ag?>">
		<input type="hidden" name="aktivnost" value="<?=$aktivnost?>">
		<input type="hidden" name="akcija" value="izmijeni_potvrda">
		<label for="naziv">Naziv aktivnosti:</label> <input type="text" name="naziv" id="naziv" value="<?=$foundActivity['name']?>"><br>
		<label for="tip_aktivnosti">Tip aktivnosti:</label> <select name="tip_aktivnosti" id="tip_aktivnosti">
			<option value="0">Fiksni bodovi</option>
			<?
			foreach($activityTypes as $type) {
				if ($foundActivity['Activity']['id'] == $type['id']) $sel = "SELECTED"; else $sel = "";
				?>
				<option value="<?=$type['id']?>" <?=$sel?>><?=$type['name']?></option>
				<?
			}
			?>
		</select> <a href="#" onclick="javascript:window.open('legenda-aktivnosti.html','blah6','width=520,height=500');">Legenda tipova aktivnosti</a><br>
		<label for="abbrev">Skraćeni naziv:</label> <input type="text" name="abbrev" id="abbrev" value="<?=$foundActivity['abbrev']?>"><br>
		<span class="opis">Skraćeni naziv se koristi u zaglavljima tabela i nekim menijima</span><br>
		<label for="poena">Bodova:</label> <input type="text" name="poena" id="poena" value="<?=$foundActivity['points']?>"><br>
		<span class="opis">Koliko poena nosi aktivnost. Ako se aktivnost ne boduje, stavite 0.</span><br>
		<label for="prolaz">Prolaz:</label> <input type="text" name="prolaz" id="prolaz" value="<?=$foundActivity['pass']?>"><br>
		<span class="opis">"Prolaz" je minimalan broj bodova potreban da bi se smatralo da je aktivnost "položena". Ako ne postoji takav minimalan broj bodova, unesite 0.</span><br>
		<label for="obavezna">Obavezna:</label> <input type="checkbox" name="obavezna" id="obavezna" <? if ($foundActivity['mandatory']) print "CHECKED"; ?>><br>
		<span class="opis">Ako je aktivna opcija "Obavezna", student ne može dobiti konačnu ocjenu dok ne položi ovu aktivnost. Ako je u "prolaz" uneseno 0, student mora pristupiti ovoj aktivnosti, čak i ako osvoji 0 bodova.</span><br><br>
		<input type="submit" value=" Izmijeni aktivnost "> <button type="button" onclick="location.href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>';">Nazad</button>
	</form>
		<?
		return;
	}
	
	
	?>
	
	<p>Na ovom modulu podešavate koje sve aktivnosti ćete ponuditi studentima kroz Zamger, što ujedno definiše i sistem bodovanja na predmetu. U slučaju da imate nekih nedoumica kontaktirajte administratora, ili možete koristiti neki od ponuđenih templejta.</p>
	
	<p><b>Trenutno podešene aktivnosti na predmetu:</b></p>
	
	<table>
		<thead>
		<tr>
			<th>&nbsp;</th>
			<th>Naziv</th>
			<th>Tip</th>
			<th>Bodova</th>
			<th>Prolaz</th>
			<th>Uslov</th>
			<th>Opcije</th>
		</tr>
		</thead>
		<tbody>
	<?
	
	$rbr = 1;
	$totalScore = 0;
	foreach($course['activities'] as $activity) {
		if ($activity['Activity']['id'] == null) $activity['Activity']['name'] = "Fiksni bodovi";
		if (!is_array($activity['options']) || !array_key_exists("Integral", $activity['options']) )
			$totalScore += $activity['points'];
		?>
		<tr>
			<td><?=$rbr++?></td>
			<td><?=$activity['name']?></td>
			<td><?=$activity['Activity']['name']?></td>
			<td><?=$activity['points']?></td>
			<td><?=$activity['pass']?></td>
			<td><?
				if ($activity['mandatory']) print "Obavezna, ";
				foreach($activity['conditionalActivities'] as $cond)
					print "uslov: " . $cond['name'] . ", ";
				if (is_array($activity['options']) && array_key_exists("MinScore", $activity['options']))
					print "min. " . $activity['options']['MinScore'] . " b.";
				?></td>
			<td><?
				if (!$activity['forced']) {
				?>
				<a href="?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&akcija=opcije&aktivnost=<?=$activity['id']?>">Opcije</a> *
				<a href="?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&akcija=izmijeni&aktivnost=<?=$activity['id']?>">Izmijeni</a> *
				<a href="?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&akcija=ukloni&aktivnost=<?=$activity['id']?>">Ukloni</a>
				<?
				} else {
					?>
					nije moguća izmjena
					<?
				}
				?>
			</td>
		</tr>
			<?
	}
	
	?>
		</tbody>
	</table>
	<?
	
	
	if ($totalScore != 100) {
		?>
		<p style="color:red">Trenutni zbir bodova je <?=$totalScore?>. Uobičajeno je da zbir bude jednak 100. Molimo da prođete kroz aktivnosti nabrojane iznad i uvjerite se da su bodovi onakvi kakvi želite.</p>
		<?
	}
	
	?>
	
	<p><b>Dodajte novu aktivnost na predmet</b></p>
	<form action="index.php" method="POST" class="aktivnosti">
		<input type="hidden" name="sta" value="nastavnik/tip">
		<input type="hidden" name="predmet" value="<?=$predmet?>">
		<input type="hidden" name="ag" value="<?=$ag?>">
		<input type="hidden" name="akcija" value="dodaj">
		<label for="naziv">Naziv aktivnosti:</label> <input type="text" name="naziv" id="naziv"><br>
		<label for="tip_aktivnosti">Tip aktivnosti:</label> <select name="tip_aktivnosti" id="tip_aktivnosti">
			<option value="0">Fiksni bodovi</option>
			<?
			foreach($activityTypes as $type) {
				?>
				<option value="<?=$type['id']?>"><?=$type['name']?></option>
				<?
			}
			?>
		</select> <a href="#" onclick="javascript:window.open('legenda-aktivnosti.html','blah6','width=520,height=500');">Legenda tipova aktivnosti</a><br>
		<label for="abbrev">Skraćeni naziv:</label> <input type="text" name="abbrev" id="abbrev"><br>
		<span class="opis">Skraćeni naziv se koristi u zaglavljima tabela i nekim menijima</span><br>
		<label for="poena">Bodova:</label> <input type="text" name="poena" id="poena" value="0"><br>
		<span class="opis">Koliko poena nosi aktivnost. Ako se aktivnost ne boduje, stavite 0.</span><br>
		<label for="prolaz">Prolaz:</label> <input type="text" name="prolaz" id="prolaz" value="0"><br>
		<span class="opis">"Prolaz" je minimalan broj bodova potreban da bi se smatralo da je aktivnost "položena". Ako ne postoji takav minimalan broj bodova, unesite 0.</span><br>
		<label for="obavezna">Obavezna:</label> <input type="checkbox" name="obavezna" id="obavezna"><br>
		<span class="opis">Ako je aktivna opcija "Obavezna", student ne može dobiti konačnu ocjenu dok ne položi ovu aktivnost. Ako je u "prolaz" uneseno 0, student mora pristupiti ovoj aktivnosti, čak i ako osvoji 0 bodova.</span><br>
		<p>Opcije specifične za tip aktivnosti te spisak uslovnih aktivnosti možete podesiti nakon što dodate aktivnost.</p>
		<input type="submit" value=" Dodaj aktivnost ">
	</form>
	<?
}

?>