<?

// STUDENTSKA/INTRO - uvodna stranica za studentsku



function studentska_intro() {

	global $userid, $user_siteadmin, $user_studentska, $conf_files_path, $person, $_api_http_code;

	require_once("lib/utility.php"); // spol, vokativ
	require_once("lib/formgen.php"); // datectl


// Provjera privilegija

if (!$user_studentska && !$user_siteadmin) {
	zamgerlog("nije studentska",3); // 3: error
	zamgerlog2("nije studentska"); // 3: error
	biguglyerror("Pristup nije dozvoljen.");
	return;
}


	// Akcije
	
	function promjena($nominativ, $u, $iz) {
		if ($iz===$u) return;
		if ($iz=="" || $iz=="0" || $iz=="01. 01. 1970." || !preg_match("/\w/", $iz)) {
			print "<li>Upis novog podatka <b>$nominativ</b> (vrijednost: <b>$u</b>)</li>\n";
		} else if ($u=="" || $u=="0" || !preg_match("/\w/", $u)) {
			print "<li>Brisanje podatka <b>$nominativ</b> (stara vrijednost: <b>$iz</b>)</li>\n";
		} else {
			print "<li>Promjena podatka <b>$nominativ</b> iz vrijednosti <b>'$iz'</b> u vrijednost <b>'$u'</b></li>\n";
		}
	}

	function try_resolve($change, $newData, $oldData) {
		if (array_key_exists('name', $newData['ExtendedPerson'][$change['field']]))
			$change['newValue'] = $newData['ExtendedPerson'][$change['field']]['name'];
		if (array_key_exists('name', $oldData['ExtendedPerson'][$change['field']]))
			$change['oldValue'] = $oldData['ExtendedPerson'][$change['field']]['name'];
		return $change;
	}

	if (param('akcija') === "Prihvati zahtjev" && check_csrf_token()) {
		$id = intval($_REQUEST['id']);
		$changeRequest = api_call("person/changeRequest/$id", [ "resolve" => [ "Place", "Country" ] ]);
		if ($_api_http_code == "404") {
			niceerror("Nepoznat ID zahtjeva $id.");
			?>
			<p>Moguće da je zahtjev potvrđen ili obrisan u međuvremenu ili da ste do ove stranice došli koristeći dugme Back.</p>
			<?php
			
			return;
		} else if ($_api_http_code != "200") {
			niceerror("Neuspješan pristup zahtjevu za promjenu podataka");
			api_report_bug($changeRequest, []);
			return;
		}
		
		$osoba = $changeRequest['Person']['id'];
		$vrijeme_zahtjeva=db_timestamp($changeRequest['requestDateTime']);
		
		/* TODO: promjena slike
			// Provjera izmjene slike
			$q115 = db_query("select slika from osoba where id=$r100[0]");
			$staraslika = db_result($q115,0,0);
			if ($staraslika != $slikapromjena) {
				$novaslika = $slikapromjena;
				$novaslika = str_replace("-promjena", "", $novaslika);
				$prefiks = "$conf_files_path/slike/";
				if (file_exists($prefiks.$staraslika))
					unlink($prefiks.$staraslika);
				if ($slikapromjena != "")
					rename($prefiks.$slikapromjena, $prefiks.$novaslika);
				$q117 = db_query("update osoba set slika='$novaslika' where id=$r100[0]");
			}
		}
		*/
		$result = api_call("person/changeRequest/$id", [], "POST");
		if ($_api_http_code != "201") {
			niceerror("Neuspješno prihvatanje zahtjeva");
			api_report_bug($result, []);
			return;
		}
		zamgerlog("prihvacen zahtjev za promjenu podataka korisnika u$osoba", 4);
		zamgerlog2("prihvacen zahtjev za promjenu podataka", $osoba);
		print "Zahtjev je prihvaćen";
		
		// Poruka korisniku
		$tekst_poruke = "Na dan ".date("d. m. Y.", $vrijeme_zahtjeva).", u ".date("H:i:s", $vrijeme_zahtjeva)." poslali ste zahtjev za promjenu ličnih podataka. Vaš zahtjev je prihvaćen. Klikom na link Profil možete vidjeti vaše nove podatke.";
		if (strlen($_REQUEST['komentar'])>2)
			$tekst_poruke .= "\n\nPovodom Vašeg zahtjeva, Studentska služba vam je uputila sljedeći komentar:\n\t".$_REQUEST['komentar'];
		$message = array_to_object( [ "id" => 0, "type" => 2, "scope" => 7, "receiver" => $osoba, "sender" => [ "id" => $userid ], "ref" => 0, "subject" => 'Vaš zahtjev za promjenu podataka je prihvaćen!', "text" => $tekst_poruke ] );
		$result = api_call("inbox", $message, "POST");
		if ($_api_http_code != "201") {
			niceerror("Neuspješno slanje poruke: " . $result['message']);
			api_report_bug($result, $message);
			return;
		}
		?>
		<script language="JavaScript">
            setTimeout(function() {
                location.href='?sta=studentska/intro';
            }, 1000);
		</script>
		<?
	
		return;
	}

	if (param('akcija') == "Odbij zahtjev" && check_csrf_token()) {
		$id = intval($_REQUEST['id']);
		$changeRequest = api_call("person/changeRequest/$id", [ "resolve" => [ "Place", "Country" ] ]);
		if ($_api_http_code == "404") {
			niceerror("Nepoznat ID zahtjeva $id.");
			?>
			<p>Moguće da je zahtjev potvrđen ili obrisan u međuvremenu ili da ste do ove stranice došli koristeći dugme Back.</p>
			<?php
			
			return;
		} else if ($_api_http_code != "200") {
			niceerror("Neuspješan pristup zahtjevu za promjenu podataka");
			api_report_bug($changeRequest, []);
			return;
		}
		
		$osoba = $changeRequest['Person']['id'];
		$vrijeme_zahtjeva=db_timestamp($changeRequest['requestDateTime']);
		
		/* TODO: brisanje promjene slike
		$osoba = intval($_REQUEST['osoba']);
		$slikapromjena=db_result($q195,0,1);
	
		// Treba li obrisati viška sliku?
		$q197 = db_query("select slika from osoba where id=$osoba");
		if ($slikapromjena != "" && db_result($q197,0,0) != $slikapromjena)
			unlink ("$conf_files_path/slike/$slikapromjena");*/
		
		$result = api_call("person/changeRequest/$id", [], "DELETE");
		if ($_api_http_code != "204") {
			niceerror("Neuspješno brisanje zahtjeva");
			api_report_bug($result, []);
			return;
		}
		zamgerlog("odbijen zahtjev za promjenu podataka korisnika u$osoba", 2);
		zamgerlog2("odbijen zahtjev za promjenu podataka", $osoba);
		print "Zahtjev je odbijen";
	
		// Poruka korisniku
		$tekst_poruke = "Na dan ".date("d. m. Y.", $vrijeme_zahtjeva).", u ".date("H:i:s", $vrijeme_zahtjeva)." poslali ste zahtjev za promjenu ličnih podataka. Vaš zahtjev je odbijen.";
		if (strlen($_REQUEST['komentar'])>2)
			$tekst_poruke .= "\n\nRazlog odbijanja zahtjeva je:\n\t".$_REQUEST['komentar'];
		$message = array_to_object( [ "id" => 0, "type" => 2, "scope" => 7, "receiver" => $osoba, "sender" => [ "id" => $userid ], "ref" => 0, "subject" => 'Vaš zahtjev za promjenu podataka je odbijen!', "text" => $tekst_poruke ] );
		$result = api_call("inbox", $message, "POST");
		if ($_api_http_code != "201") {
			niceerror("Neuspješno slanje poruke: " . $result['message']);
			api_report_bug($result, $message);
			return;
		}
		
		?>
		<script language="JavaScript">
            setTimeout(function() {
                location.href='?sta=studentska/intro';
            }, 1000);
		</script>
		<?
		
		return;
	}


	if (param('akcija') == "zahtjev") {
		$id = intval($_REQUEST['id']);
		$changeRequest = api_call("person/changeRequest/$id", [ "resolve" => [ "Place", "Country" ] ]);
		if ($_api_http_code == "404") {
			niceerror("Nepoznat ID zahtjeva $id.");
			?>
			<p>Moguće da je zahtjev potvrđen ili obrisan u međuvremenu ili da ste do ove stranice došli koristeći dugme Back.</p>
			<?php
			
			return;
		} else if ($_api_http_code != "200") {
			niceerror("Neuspješan pristup zahtjevu za promjenu podataka");
			api_report_bug($changeRequest, []);
			return;
		}
		
		$personId = $changeRequest['Person']['id'];
		$oldPerson = api_call("person/$personId", [ "resolve" => [ "ExtendedPerson", "Place", "Country" ] ] );
	
		?>
		<p>Korisnik <b><?=$oldPerson['name']?> <?=$oldPerson['surname']?></b> zatražio/la je sljedeće izmjene
			svojih ličnih podataka:
		<ul>
		<?
		
		$translations = array(
			"name" => "ime", "surname" => "prezime", "sex" => "spol", "fathersSurname" => "prezime oca", "fathersName" => "ime oca",
			"mothersName" => "ime majke", "mothersSurname" => "prezime majke", "ethnicity" => "nacionalnost", "placeOfBirth" => "mjesto rođenja",
			"addressStreetNo" => "adresa", "addressPlace" => "adresa (mjesto)", "nationality" => "državljanstvo",
			"phone" => "telefon", "previousEducation" => "završena škola", "residenceAddress" => "adresa prebivališta",
			"residencePlace" => "mjesto prebivališta", "sourceOfFunding" => "izvor finasiranja",
			"activityStatusParent" => "status aktivnosti roditelja", "activityStatusStudent" => "status aktivnosti studenta",
			"occupationParent" => "zanimanje roditelja", "occupationStudent" => "zanimanje studenta",
			"employmentStatusParent" => "status zaposlenosti roditelja", "employmentStatusStudent" => "status zaposlenosti studenta",
		);
		$sexKeys = array("M" => "muški", "Z" => "ženski");
		$nacionalnost = [
			"1" => "Bošnjak/Bošnjakinja",
			"2" => "Srbin/Srpkinja",
			"3" => "Hrvat/Hrvatica",
			"4" => "Rom/Romkinja",
			"5" => "Ostalo",
			"6" => "Nepoznato / Nije se izjasnio/la",
			"9" => "Bosanac/Bosanka",
			"10" => "BiH",
			"11" => "Musliman/Muslimanka"
		];
		$izvoriFinansiranja = [ '1' => 'Roditelji', '2' => 'Primate plaću iz radnog odnosa', '3' => 'Primate stipendiju', '4' => 'Kredit', '5' => 'Ostalo' ];
		$statusAktivnosti   = [ '1' => 'Zaposlen', '2' => 'Nezaposlen', '3' => 'Neaktivan'];
		$statusZaposlenosti = [ '1' => 'Poslodavac / Samozaposlenik', '2' => 'Zaposlenik', '3' => 'Pomažući član porodice'];
		$drzava = $skola = [];

		foreach($changeRequest['changes'] as $change) {
			if ($change['field'] == 'sex') {
				$change['newValue'] = $sexKeys[$change['newValue']];
				$change['oldValue'] = $sexKeys[$change['oldValue']];
			}
			if ($change['field'] == 'ethnicity') {
				$change['newValue'] = $nacionalnost[$change['newValue']];
				$change['oldValue'] = $nacionalnost[$change['oldValue']];
			}
			if ($change['field'] == 'sourceOfFunding') {
				$change['newValue'] = $izvoriFinansiranja[$change['newValue']];
				$change['oldValue'] = $izvoriFinansiranja[$change['oldValue']];
			}
			if ($change['field'] == 'activityStatusParent' || $change['field'] == 'activityStatusStudent') {
				$change['newValue'] = $statusAktivnosti[$change['newValue']];
				$change['oldValue'] = $statusAktivnosti[$change['oldValue']];
			}
			if ($change['field'] == 'employmentStatusParent' || $change['field'] == 'employmentStatusStudent') {
				$change['newValue'] = $statusZaposlenosti[$change['newValue']];
				$change['oldValue'] = $statusZaposlenosti[$change['oldValue']];
			}
			if ($change['field'] == 'nationality') {
				if (empty($drzava))
					foreach (api_call("person/country/search", [ "query" => "" ] )["results"] as $result)
						$drzava[$result['id']] = $result['name'];
				$change['newValue'] = $drzava[$change['newValue']];
				$change['oldValue'] = $drzava[$change['oldValue']];
			}
			if ($change['field'] == 'previousEducation') {
				if (empty($skola))
					foreach (api_call("person/school/search", [ "query" => "" ] )["results"] as $result)
						$skola[$result['id']] = $result['name'];
				$change['newValue'] = $skola[$change['newValue']];
				$change['oldValue'] = $skola[$change['oldValue']];
			}
			if ($change['field'] == 'placeOfBirth' || $change['field'] == 'residencePlace' || $change['field'] == 'addressPlace') {
				$change = try_resolve($change, $changeRequest, $oldPerson);
			}
			
			$name = $change['field'];
			if (array_key_exists($name, $translations))
				$name = $translations[$name];
			promjena($name, $change['newValue'], $change['oldValue']);
		}
		
		?>
		</ul><p>&nbsp;</p>
		<?
	
		/* TODO: promjena slike
		$staraslika = db_result($q100,0,36); $novaslika = db_result($q100,0,35);
		if ($staraslika != $novaslika) {
			if ($staraslika=="") {
				?>
				<p>Dodavanje slike:<br><img src="?sta=common/slika&osoba=<?=$osoba?>&promjena=1"></p>
				<?
			}
			else if ($novaslika=="") {
				?>
				<p>Brisanje slike (stara slika):<br><img src="?sta=common/slika&osoba=<?=$osoba?>"></p>
				<?
			}
			else {
				?>
				<p>Promjena slike</p>
				<table border="0"><tr><td valign="top">Iz:<br><img src="?sta=common/slika&osoba=<?=$osoba?>"></td>
				<td valign="top">U<br><img src="?sta=common/slika&osoba=<?=$osoba?>&promjena=1"></td></tr></table>
				<?
			}
			print "<p>&nbsp;</p>\n";
		}*/
	
		?>
		<?=genform("POST")?>
		<input type="hidden" name="osoba" value="<?=$personId?>">
		<input type="submit" name="akcija" value="Prihvati zahtjev">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="submit" name="akcija" value="Odbij zahtjev"><br><br>
		Eventualno upišite komentar koji želite poslati studentu:<br>
		<input type="text" size="50" name="komentar">
		</form>
		<?
		
		return;
	}
	
	
	
	// -----------------------------------------
	//
	// POČETNA STRANICA
	//
	// -----------------------------------------
	
	
	// Dobrodošlica
	if ($person['ExtendedPerson']['sex'] == 'F' || ($person['ExtendedPerson']['sex'] == '' && spol($person['name'])=="Z"))
		print "<h1>Dobro došla, ".vokativ($person['name'],"Z")."</h1>";
	else
		print "<h1>Dobro došao, ".vokativ($person['name'],"M")."</h1>";
	
	
	// Zahtjevi za promjenu ličnih podataka
	
	$changeRequests = api_call("person/changeRequest")['results'];
	if (count($changeRequests) < 1) {
	?>
		<p>Nema novih zahtjeva za promjenu ličnih podataka.</p>
	<?
	} else {
		?>
		<p><b>Zahtjevi za promjenu ličnih podataka:</b>
		<ul>
	<?
	}
	//$q10 = db_query("select pp.id, pp.osoba, UNIX_TIMESTAMP(pp.vrijeme_zahtjeva), o.ime, o.prezime from promjena_podataka as pp, osoba as o where o.id=pp.osoba order by pp.vrijeme_zahtjeva");
	
	foreach ($changeRequests as $cr) {
		?>
		<li><a href="?sta=studentska/intro&akcija=zahtjev&id=<?=$cr['id']?>"><?=$cr['name']?> <?=$cr['surname']?></a> (<?=date("d. m. Y. H:i", db_timestamp($cr['requestDateTime']))?>)</li>
		<?
	}
	
	if (count($changeRequests) > 0) {
		?>
		</ul>
		Kliknite na zahtjev da biste ga prihvatili ili odbili.
		</p>
	
	<?
	
	}
	
	
	// Zahtjevi za dokumenta
	
	$q40 = db_query("SELECT count(*) FROM zahtjev_za_potvrdu WHERE status=1");
	$br_zahtjeva = db_result($q40, 0, 0);
	if ($br_zahtjeva > 0)
		print "<p><a href=\"?sta=studentska/potvrde&akcija=potvrda\">Imate $br_zahtjeva neobrađenih zahtjeva za dokumenta.</a></p>";
	else
		print "<p>Nema neobrađenih zahtjeva za dokumenta.</p>";
	

}

function dajplus($layerid,$layername) {
	return "<img src=\"static/images/plus.png\" width=\"13\" height=\"13\" id=\"img-$layerid\" onclick=\"daj_stablo('$layerid')\"> $layername <div id=\"$layerid\" style=\"display:none\">";
}

?>
