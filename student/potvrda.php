<?

// STUDENT/POTVRDA - zahtjev za izdavanje ovjerene potvrde



function student_potvrda() {
	function db_fetch8($res, &$a, &$b, &$c, &$d, &$e, &$f, &$g, &$h) { $r = db_fetch_row($res); if ($r) { $a=$r[0]; $b=$r[1]; $c=$r[2]; $d=$r[3]; $e=$r[4]; $f=$r[5]; $g=$r[6]; $h=$r[7]; } return $r; }

	global $userid;
	global $conf_broj_besplatnih_potvrda, $conf_cijena_potvrde, $_api_http_code;
	
	$certPurposesTypes = api_call("certificate/purposesTypes");
	$zahtjevi = api_call("certificate/student/$userid")['results'];

	$akcija = param('akcija');
	if ($akcija == "odustani") {
		$id = intval($_REQUEST['id']);
		$status = false;
		foreach($zahtjevi as $zahtjev)
			if ($zahtjev['id'] == $id)
				$status = $zahtjev['status'];
		if ($status === false) {
			niceerror("Neispravan zahtjev");
			return;
		}
		if ($status == 2) {
			niceerror("Ne možete odustati od zahtjeva koji je obrađen");
			return;
		}
		
		api_call("certificate/$id", [], "DELETE");
		if ($_api_http_code == "204") {
			nicemessage("Odustali ste od zahtjeva");
			for ($i=0; $i<count($zahtjevi); $i++)
				if ($zahtjevi[$i]['id'] == $id)
					unset($zahtjevi[$i]);
		}
		else
			niceerror("Došlo je do greške prilikom odustajanja od zahtjeva");
		
	}

	if ($akcija == "novi") {
		if ($conf_broj_besplatnih_potvrda > 0 && count($zahtjevi) >= $conf_broj_besplatnih_potvrda && !isset($_REQUEST['potvrda'])) {
			nicemessage("Ova potvrda se plaća!");
			?>
			<p>U ovoj akademskoj godini ste iskoristili <b><?=count($zahtjevi)?></b> od besplatnih <b><?=$conf_broj_besplatnih_potvrda?></b> potvrda na koje imate pravo.</p>
			<p>Svaka naredna potvrda košta <?=sprintf("%.2f", $conf_cijena_potvrde)?> KM, te prilikom preuzimanja trebate pokazati uplatnicu. Možete odustati od potvrda koje još uvijek nisu obrađene.</p>
			<?=genform("POST")?>
			<input type="hidden" name="potvrda" value="DA">
			<p>Da li ste sigurni?</p>
			<input type="submit" value="Siguran/na sam!">
			<input type="button" onclick="history.go(-1);" value="Vratite me nazad">
			</form>
			<?
			return;
		}
		
		$certificate = new stdClass;
		$certificate->CertificateType = intval($_REQUEST['tip_potvrde']);
		$certificate->CertificatePurpose = intval($_REQUEST['svrha_potvrde']);
		
		$result = api_call("certificate/student/$userid", $certificate, "POST");
		if ($result['code'] == "201") {
			nicemessage("Zahtjev prihvaćen i čeka na obradu");
			?>
			<script language="JavaScript">
			setTimeout(function() { location.href='?sta=student/potvrda' }, 1000 );
			</script>
			<?
		}
		else
			niceerror("Greška prilikom slanja zahtjeva: Error " . $result['code'] . ": " . $result['message']);
		return;
	}


	// Naslov
	?>
	<h3>Zahtjev za izdavanje ovjerenog uvjerenja</h3>
	
	<p>Vaši aktuelni zahtjevi:</p>
	<?

	if (count($zahtjevi) == 0)
		print "<p>Nema otvorenih zahtjeva</p>\n";
	else
		print "<ul>";
	
	foreach($zahtjevi as $zahtjev) {
		$fini_datum = date("d.m.Y. H:i:s", db_timestamp($zahtjev['datetime']));
	
		print "<li>" . $certPurposesTypes['types'][$zahtjev['CertificateType']] . "<br>\n";
		if ($zahtjev['CertificateType'] == 1)
			print "(u svrhu: " . $certPurposesTypes['purposes'][$zahtjev['CertificatePurpose']] . ")<br>\n";
		print "Datum i vrijeme zahtjeva: $fini_datum<br>\n";
		if ($zahtjev['status'] == 1)
			print "Status zahtjeva: <font color=\"red\"><b>čeka na obradu</b></font>\n";
		else
			print "Status zahtjeva: <font color=\"green\"><b>obrađen</b></font>\n";
		
		if (!$zahtjev['free']) print " - potrebno platiti ".sprintf("%.2f", $conf_cijena_potvrde)." KM";
		print "<br>\n";
		
		if ($zahtjev['CertificateType'] == 1) {
			print "<a href=\"?sta=izvjestaj/potvrda&student=$userid&svrha=" . $zahtjev['CertificatePurpose'] . "\">pogledaj uvjerenje</a> ";
		} else {
			print "<a href=\"?sta=izvjestaj/index2&student=$userid\">pogledaj uvjerenje</a> ";
		}
		if ($zahtjev['status'] == 1)
			print "* <a href=\"?sta=student/potvrda&akcija=odustani&id=" . $zahtjev['id'] . "\">odustani od zahtjeva</a>\n";

		print "</li>\n";
	}
	if (count($zahtjevi) > 0)
		print "</ul>\n";

	?>

	<p>&nbsp;</p>

	<p><b>Novi zahtjev:</b></p>
	<?=genform("POST")?>
	<input type="hidden" name="akcija" value="novi">
	Tip uvjerenja/potvrde: <select name="tip_potvrde">
	<?
	foreach($certPurposesTypes['types'] as $id => $description)
		print "<option value=\"$id\">$description</option>\n";
	?>
	</select><br>

	Izdaje se u svrhu: <select name="svrha_potvrde">
	<?
	foreach($certPurposesTypes['purposes'] as $id => $description)
		print "<option value=\"$id\">$description</option>\n";
	?>
	</select><br><br>

	<input type="submit" value=" Pošalji zahtjev ">
	</form>
	
	<?
	if ($conf_broj_besplatnih_potvrda > 0) {
		?>	
		<p>U ovoj akademskoj godini ste iskoristili <b><?=count($zahtjevi)?></b> od besplatnih <b><?=$conf_broj_besplatnih_potvrda?></b> potvrda na koje imate pravo. (Možete odustati od potvrda koje još uvijek nisu obrađene.)</p>
		<?
	}
}

?>
