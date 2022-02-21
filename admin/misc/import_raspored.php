<?php



//----------------------------------------
// Import rasporeda
//----------------------------------------

// Ovo je teški očaj koliko je "lagan" Ribićev format za import :( lakše je bilo dok sam parsirao Milišićev word fajl

function admin_misc_import_raspored() {

	if ($_POST['akcija']=="import_raspored" && check_csrf_token()) {
		$raspored = int_param('raspored');
		if ($raspored == 0) {
			?>
			<?=genform("POST")?></p>
			<input type="hidden" name="akcija" value="import_raspored">
			<input type="hidden" name="raspored" value="-1">
			Akademska godina: <select name="akademska_godina">
				<?
				$q20 = db_query("SELECT id, naziv, aktuelna FROM akademska_godina");
				while (db_fetch3($q20, $id, $naziv, $aktuelna)) {
					print "<option value=\"$id\"";
					if ($aktuelna == 1) print " selected";
					print ">$naziv</option>\n";
				}
				?>
			</select><br>
			Studij: <select name="studij"><option value="0">(Svi studiji)</option>
				<?
				$q30 = db_query("SELECT id, naziv FROM studij WHERE moguc_upis=1");
				while (db_fetch2($q30, $id, $naziv)) {
					print "<option value=\"$id\">$naziv</option>";
				}
				?>
			</select><br>
			Semestar: <select name="semestar"><option value="0">(Svi semestri)</option>
				<?
				for ($i=1; $i<6; $i++)
					print "<option value=\"$i\">$i</option>\n";
				?>
			</select><br>
			<input type="submit" value=" Kreiraj ">
			</form>
			<?
			
			return;
		}
		
		if ($raspored == -1) {
			$ag = int_param('akademska_godina');
			$studij = int_param('studij');
			$semestar = int_param('semestar');
			
			db_query("INSERT INTO raspored SET studij=$studij, akademska_godina=$ag, semestar=$semestar, privatno=0, aktivan=1");
			$raspored = db_insert_id();
		} else {
			$ag = db_get("SELECT akademska_godina FROM raspored WHERE id=$raspored");
			if ($ag === false) {
				niceerror("Nepoznat raspored");
				return;
			}
		}
		
		$tr_dani = array("PO" => 1, "UT" => 2, "SR" => 3, "CE" => 4, "PE" => 5);
		$zamjene = array(
			// Naša slova
			"Inzenj" => "Inženj", "Racun" => "Račun", "Masin" => "Mašin", "Dinamick" => "Dinamičk", "Elektricn" => "Električn", "Logick" => "Logičk", "Rjesenj" => "Rješenj", "Zastit" => "Zaštit", "Mrez" => "Mrež", "Numerick" => "Numeričk", "Istrazi" => "Istraži", "Menadzm" => "Menadžm", "Cvorist" => "Čvorišt", "Optick" => "Optičk",
			// Specifičnosti predmeta
			"Računari Arhitektura" => "Računari, Arhitektura", "Upravljanje E E S" => "Upravljanje elektroenergetskih sistema", "Pouzdanost El Elemenata" => "Pouzdanost električnih elemenata", "Dinamika El Ma" => "Dinamika električnih ma", "Uupravljanje" => "Upravljanje", "Vjestacke" => "Vještačke", "Inovacije U Projektiranju" => "Inovacije u projektovanju", "lektronika Za Telekomunikacije " => "lektronika TK", "hC" => "h Č", "U T K Mre" => "u Telekomunikacijskim mre", "U T K Kana" => "u Telekomunikacijskom kana", "Softver Inzinjering" => "softver inženjering", "ki T K Sis" => "ki Telekomunikacijski sis", "Telek Softver Inzen" => "Telekomunikacijski Softver Inženjering", "Ttelekomu" => "Telekomu");
		$zamjene_sale = array("EE-1" => "EE1", "EE-2" => "EE2");
		
		$ocekivani = $ocekivano_ime = "";
		foreach(explode("\n", $_REQUEST['import']) as $linija) {
			//print "Linija: $linija<br>\n";
			//print "Ocekivani: $ocekivani $ocekivano_ime<br>\n";
			
			$dijelovi = explode(",", trim($linija));
			$novi_dan = $tr_dani[substr($dijelovi[0], 0, 2)];
			$polusat = (substr($dijelovi[0], strlen($dijelovi[0])-1) == "A");
			$vrijeme = intval(substr($dijelovi[0], 2)) - 1;
			
			$dijelovi_imena = substr($dijelovi[1], 0, strrpos($dijelovi[1], "-"));
			
			if ($dijelovi[0] == $ocekivani && $dijelovi_imena == $ocekivano_ime) {
				$vrijeme_kraj = $vrijeme;
				
				if ($polusat) $fini_kraj = "00:00:00";
				else $fini_kraj = sprintf("00:%02d:30", $vrijeme+8);
				
				if ($polusat) $ocekivani = substr($dijelovi[0], 0, 2) . ($vrijeme_kraj+2);
				else $ocekivani = substr($dijelovi[0], 0, 2) . ($vrijeme+1) . "A";
				continue;
			}
			
			if ($ocekivani != "" && $id_predmeta !== false) {
				if (empty($labgrupe) && $tip == "P")
					db_query("INSERT INTO raspored_stavka VALUES (0, $raspored, $dan, $id_predmeta, 0, $vrijeme_pocetak, $vrijeme_kraj, $id_sale, '$tip', 0, 0, '$fini_pocetak', '$fini_kraj')");
				else foreach($labgrupe as $lg)
					db_query("INSERT INTO raspored_stavka VALUES (0, $raspored, $dan, $id_predmeta, $lg, $vrijeme_pocetak, $vrijeme_kraj, $id_sale, '$tip', 0, 0, '$fini_pocetak', '$fini_kraj')");
			}
			
			$dan = $novi_dan;
			$vrijeme_pocetak = $vrijeme;
			$vrijeme_kraj = $vrijeme;
			if ($polusat) {
				$fini_pocetak = sprintf("00:%02d:30", $vrijeme+8);
				$fini_kraj = "00:00:00";
			}
			else {
				$fini_pocetak = "00:00:00";
				$fini_kraj = sprintf("00:%02d:30", $vrijeme+8);
			}
			
			$ocekivano_ime = substr($dijelovi[1], 0, strrpos($dijelovi[1], "-"));
			$tip = strtoupper($ocekivano_ime[0]);
			
			$tmpime = substr($dijelovi[1], 1, strpos($dijelovi[1], "-")-1);
			$ime_predmeta = "";
			for ($i=0; $i<strlen($tmpime); $i++) {
				if ($i>0 && (($tmpime[$i] >= "A" && $tmpime[$i] <= "Z") || ($tmpime[$i] >= "0" && $tmpime[$i] <= "9")))
					$ime_predmeta .= " ";
				$ime_predmeta .= $tmpime[$i];
			}
			
			foreach($zamjene as $dio => $zamjena)
				$ime_predmeta = str_replace($dio, $zamjena, $ime_predmeta);
			
			$id_predmeta = db_get("SELECT id FROM predmet WHERE naziv LIKE '$ime_predmeta'");
			if ($id_predmeta === false)
				print "-- Nije pronađen predmet $ime_predmeta<br>\n";
			if ($id_predmeta == 20) $id_predmeta = 2093;
			
			$sala = $dijelovi[count($dijelovi)-1];
			if ($sala[0] == "R") $sala = substr($sala,1);
			foreach($zamjene_sale as $dio => $zamjena)
				$sala = str_replace($dio, $zamjena, $sala);
			
			$id_sale = db_get("SELECT id FROM raspored_sala WHERE naziv LIKE '$sala'");
			if ($id_sale === false)
				print "-- Nije pronađena sala $sala<br>\n";
			
			if ($polusat) $ocekivani = substr($dijelovi[0], 0, 2) . ($vrijeme_kraj+1);
			else $ocekivani = substr($dijelovi[0], 0, 2) . ($vrijeme+1) . "A";
			
			$labgrupe = array();
			if ($id_predmeta !== false)
				for ($i=2; $i<count($dijelovi)-1; $i++) {
					$id_labgrupe = db_get("SELECT id FROM labgrupa WHERE naziv LIKE '".$dijelovi[$i]."' AND predmet=$id_predmeta AND akademska_godina=$ag");
					if ($id_labgrupe !== false) $labgrupe[] = $id_labgrupe;
					//print "Naziv ".$dijelovi[$i]." id $id_labgrupe<br>\n";
				}
		}
		
		if ($ocekivani != "" && $id_predmeta !== false)  {
			if (empty($labgrupe) && $tip == "P")
				db_query("INSERT INTO raspored_stavka VALUES (0, $raspored, $dan, $id_predmeta, 0, $vrijeme_pocetak, $vrijeme_kraj, $id_sale, '$tip', 0, 0, '$fini_pocetak', '$fini_kraj')");
			else foreach($labgrupe as $lg)
				db_query("INSERT INTO raspored_stavka VALUES (0, $raspored, $dan, $id_predmeta, $lg, $vrijeme_pocetak, $vrijeme_kraj, $id_sale, '$tip', 0, 0, '$fini_pocetak', '$fini_kraj')");
		}
		
		
		nicemessage("Raspored importovan");
		
	} else {
		
		$q10 = db_query("SELECT id, studij, akademska_godina, semestar FROM raspored ORDER BY akademska_godina DESC");
		$lista = "";
		while(db_fetch4($q10, $id, $studij, $akademska_godina, $semestar)) {
			if ($studij == 0) $tekst = "Svi studiji";
			else $tekst = db_get("SELECT naziv FROM studij WHERE id=$studij");
			
			$tekst .= " (" . db_get("SELECT naziv FROM akademska_godina WHERE id=$akademska_godina");
			
			if ($semestar > 0) $tekst .= ", $semestar. semestar";
			$tekst .= ")";
			
			$lista .= "<option value=\"$id\">$tekst</option>\n";
		}
		
		?>
		<?=genform("POST")?>
		<input type="hidden" name="akcija" value="import_raspored">
		Ažuriraj raspored: <select name="raspored"><option value="0">(Kreiraj novi)</option><?=$lista?></select><br>
		<textarea name="import" rows="6" cols="60"></textarea><br>
		<input type="submit" value=" Uvezi raspored ">
		</form>
		<?
	}
}