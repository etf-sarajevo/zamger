<?

// STUDENT/POTVRDA - zahtjev za izdavanje ovjerene potvrde



function student_potvrda() {
	function db_fetch8($res, &$a, &$b, &$c, &$d, &$e, &$f, &$g, &$h) { $r = db_fetch_row($res); if ($r) { $a=$r[0]; $b=$r[1]; $c=$r[2]; $d=$r[3]; $e=$r[4]; $f=$r[5]; $g=$r[6]; $h=$r[7]; } return $r; }

	global $userid;
	global $conf_broj_besplatnih_potvrda, $conf_cijena_potvrde;
	
	$aktuelna_godina = db_get("SELECT id FROM akademska_godina WHERE aktuelna=1");
	
	$broj_potvrda = db_get("SELECT COUNT(*) FROM zahtjev_za_potvrdu WHERE student=$userid AND akademska_godina=$aktuelna_godina");

	$akcija = param('akcija');
	if ($akcija == "odustani") {
		$id = intval($_REQUEST['id']);
		$status = db_get("SELECT status FROM zahtjev_za_potvrdu WHERE id=$id AND student=$userid");
		if ($status === false) {
			niceerror("Neispravan zahtjev");
			return;
		}
		if ($status == 2) {
			niceerror("Ne možete odustati od zahtjeva koji je obrađen");
			return;
		}
		
		$q310 = db_query("DELETE FROM zahtjev_za_potvrdu WHERE id=$id");
		nicemessage("Odustali ste od zahtjeva");
		zamgerlog("odustao od zahtjeva za potvrdu $id", 2);
		zamgerlog2("odustao od zahtjeva za potvrdu", $id);
	}

	if ($akcija == "novi") {
		if ($conf_broj_besplatnih_potvrda > 0 && $broj_potvrda >= $conf_broj_besplatnih_potvrda && !isset($_REQUEST['potvrda'])) {
			nicemessage("Ova potvrda se plaća!");
			?>
			<p>U ovoj akademskoj godini ste iskoristili <b><?=$broj_potvrda?></b> od besplatnih <b><?=$conf_broj_besplatnih_potvrda?></b> potvrda na koje imate pravo.</p>
			<p>Svaka naredna potvrda košta <?=sprintf("%.2f", $conf_cijena_potvrde)?> KM, te prilikom preuzimanja trebate pokazati uplatnicu. Možete odustati od potvrda koje još uvijek nisu obrađene.</p>
			<?=genform("POST")?>
			<input type="hidden" name="potvrda" value="DA">
			<p>Da li ste sigurni?</p>
			<input type="submit" value="Siguran/na sam!">
			<input type="button" onclick="javascript:history.go(-1);" value="Vratite me nazad">
			</form>
			<?
			return;
		}
		
		if ($conf_broj_besplatnih_potvrda == 0 || $broj_potvrda < $conf_broj_besplatnih_potvrda)
			$besplatna = 1;
		else
			$besplatna = 0;
		$tip_potvrde = intval($_REQUEST['tip_potvrde']);
		$svrha_potvrde = intval($_REQUEST['svrha_potvrde']);
		$q320 = db_query("INSERT INTO zahtjev_za_potvrdu SET student=$userid, tip_potvrde=$tip_potvrde, svrha_potvrde=$svrha_potvrde, datum_zahtjeva=NOW(), status=1, akademska_godina=$aktuelna_godina, besplatna=$besplatna");

		$id = intval(db_insert_id());
		nicemessage("Zahtjev prihvaćen i čeka na obradu");
		zamgerlog("uputio novi zahtjev za potvrdu $id", 2);
		zamgerlog2("uputio novi zahtjev za potvrdu", $id);
		?>
		<script language="JavaScript">
		location.href='?sta=student/potvrda';
		</script>
		<?
		return;
	}


	// Naslov
	?>
	<h3>Zahtjev za izdavanje ovjerenog uvjerenja</h3>
	
	<p>Vaši aktuelni zahtjevi:</p>
	<?

	$q100 = db_query("SELECT zzp.id, tp.id, tp.naziv, sp.id, sp.naziv, UNIX_TIMESTAMP(zzp.datum_zahtjeva), zzp.status, zzp.besplatna FROM zahtjev_za_potvrdu as zzp, tip_potvrde as tp, svrha_potvrde sp WHERE zzp.student=$userid and zzp.tip_potvrde=tp.id AND zzp.akademska_godina=$aktuelna_godina AND zzp.svrha_potvrde=sp.id");
	if (db_num_rows($q100) == 0)
		print "<p>Nema otvorenih zahtjeva</p>\n";
	else
		print "<ul>";
	while (db_fetch8($q100, $id_zahtjeva, $id_tipa_potvrde, $tip_potvrde, $id_svrhe_potvrde, $svrha_potvrde, $datum_zahtjeva, $status_zahtjeva, $besplatna)) {
		$fini_datum = date("d.m.Y. H:i:s", $datum_zahtjeva);
	
		print "<li>$tip_potvrde<br>\n";
		if ($id_tipa_potvrde == 1) {
			print "(u svrhu: $svrha_potvrde)<br>\n";
		}
		print "Datum i vrijeme zahtjeva: $fini_datum<br>\n";
		if ($status_zahtjeva == 1)
			print "Status zahtjeva: <font color=\"red\"><b>čeka na obradu</b></font>\n";
		else
			print "Status zahtjeva: <font color=\"green\"><b>obrađen</b></font>\n";
		
		if ($besplatna == 0) print " - potrebno platiti ".sprintf("%.2f", $conf_cijena_potvrde)." KM";
		print "<br>\n";
		
		if ($id_tipa_potvrde == 1) {
			print "<a href=\"?sta=izvjestaj/potvrda&student=$userid&svrha=$id_svrhe_potvrde\">pogledaj uvjerenje</a> ";
		} else {
			print "<a href=\"?sta=izvjestaj/index2&student=$userid\">pogledaj uvjerenje</a> ";
		}
		if ($status_zahtjeva == 1)
			print "* <a href=\"?sta=student/potvrda&akcija=odustani&id=$id_zahtjeva\">odustani od zahtjeva</a>\n";

		print "</li>\n";
	}
	if (db_num_rows($q100) > 0)
		print "</ul>\n";

	?>

	<p>&nbsp;</p>

	<p><b>Novi zahtjev:</b></p>
	<?=genform("POST")?>
	<input type="hidden" name="akcija" value="novi">
	Tip uvjerenja/potvrde: <select name="tip_potvrde">
	<?
	$q200 = db_query("SELECT id, naziv FROM tip_potvrde");
	while ($r200 = db_fetch_row($q200))
		print "<option value=\"$r200[0]\">$r200[1]</option>\n";
	?>
	</select><br>

	Izdaje se u svrhu: <select name="svrha_potvrde">
	<?
	$q210 = db_query("SELECT id, naziv FROM svrha_potvrde");
	while ($r210 = db_fetch_row($q210))
		print "<option value=\"$r210[0]\">$r210[1]</option>\n";
	?>
	</select><br><br>

	<input type="submit" value=" Pošalji zahtjev ">
	</form>
	
	<?
	if ($conf_broj_besplatnih_potvrda > 0) {
		?>	
		<p>U ovoj akademskoj godini ste iskoristili <b><?=$broj_potvrda?></b> od besplatnih <b><?=$conf_broj_besplatnih_potvrda?></b> potvrda na koje imate pravo. (Možete odustati od potvrda koje još uvijek nisu obrađene.)</p>
		<?
	}
}

?>
