<?php


// Ovo bi trebao biti automatski background proces u cron-u, ali moram smisliti kako da ne istekne sesija dok traje

function admin_misc_promijeni_kodove() {
	
	if (param('akcija') == "promijeni_kodove_finaliziraj" && check_csrf_token()) {
		print "Finaliziram<br>\n";
		db_query("UPDATE kod_za_izvjestaj SET promjena=0");
		return;
	}
	
	if (param('akcija') == "promijeni_kodove_predmet" && check_csrf_token()) {
		$parovi = explode(",", param('predmeti'));
		$par = array_shift($parovi);
		print "Generišem $par<br>\n";
		list($predmet,$ag) = explode("-", $par);
		generisi_izvjestaj_predmet($predmet, $ag);
		
		if (!empty($parovi)) {
			$predmeti = join(",", $parovi);
			?>
			
			<p><?=genform("POST", "dalje");?>
				<input type="hidden" name="akcija" value="promijeni_kodove_predmet">
				<input type="hidden" name="predmeti" value="<?=$predmeti?>">
				<input type="submit" value="Step 2">
				</form></p>
			<?
		} else {
			?>
			
			<p><?=genform("POST", "dalje");?>
				<input type="hidden" name="akcija" value="promijeni_kodove_finaliziraj">
				<input type="submit" value="Step 3">
				</form></p>
			<?
		}
		?>
		<script>setTimeout(function() {
				document.getElementById('dalje').submit();
			}, 1000);
		</script>
		<?
		return;
	}
	
	
	if (param('akcija') == "promijeni_kodove" && check_csrf_token()) {
		global $conf_files_path;
		
		$q12 = db_query("SELECT osoba FROM kod_za_izvjestaj WHERE promjena=1");
		while (db_fetch1($q12, $osoba)) {
			do {
				$consonants = 'BCDFGHJKLMNPQRSTVWXYZ';
				$wovels = 'AEIOU';
				$code = array(); //remember to declare $pass as an array
				for ($i = 0; $i < 10 / 2; $i++) {
					$n = rand(0, strlen($consonants)-1);
					$code[] = $consonants[$n];
					$n = rand(0, 4);
					$code[] = $wovels[$n];
				}
				$string = implode($code); //turn the array into a string
				$exists = db_get("SELECT COUNT(*) FROM kod_za_izvjestaj WHERE kod='$string'");
			} while ($exists > 0);
			db_query("DELETE FROM kod_za_izvjestaj WHERE osoba=" . $osoba);
			db_query("INSERT INTO kod_za_izvjestaj VALUES(" . $osoba . ", '$string', 1)");
			print "Novi kod za osobu $osoba je $string<br>\n";
		}
		
		$predmeti = "";
		$q11 = db_query("SELECT DISTINCT pk.predmet, pk.akademska_godina FROM ponudakursa pk, student_predmet sp, kod_za_izvjestaj kzi WHERE pk.id=sp.predmet AND sp.student=kzi.osoba AND kzi.promjena=1");
		while (db_fetch2($q11, $predmet, $ag)) {
			if (file_exists("$conf_files_path/cache/izvjestaj_predmet/$predmet-$ag")) {
				$predmeti .= "$predmet-$ag,";
			}
		}
		
		if ($predmeti != "") {
			?>
			
			<p><?=genform("POST", "dalje");?>
				<input type="hidden" name="akcija" value="promijeni_kodove_predmet">
				<input type="hidden" name="predmeti" value="<?=$predmeti?>">
				<input type="submit" value="Step 2">
				</form></p>
			<script>setTimeout(function() {
					document.getElementById('dalje').submit();
				}, 1000);
			</script>
			<?
		} else {
			print "Nema ništa";
			db_query("UPDATE kod_za_izvjestaj SET promjena=0");
		}
		
		return;
	}
	
	
	?>
		<p><hr/></p>
		
		<p><?=genform("POST");?>
			<input type="hidden" name="akcija" value="promijeni_kodove">
			<input type="hidden" name="fakatradi" value="0">
			<input type="submit" value="Ažuriraj kodove za izvještaje">
			</form></p>
		<p><hr/></p>
	<?
}
