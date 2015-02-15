<?

// STUDENT/POTVRDA - zahtjev za izdavanje ovjerene potvrde



function student_potvrda() {

	global $userid;


	$akcija = $_REQUEST['akcija'];
	if ($akcija == "odustani") {
		$id = intval($_REQUEST['id']);
		$q300 = myquery("SELECT COUNT(*) FROM zahtjev_za_potvrdu WHERE id=$id AND student=$userid");
		if (mysql_num_rows($q300)<1) {
			niceerror("Neispravan zahtjev");
			return;
		}
		$q310 = myquery("DELETE FROM zahtjev_za_potvrdu WHERE id=$id");
		nicemessage("Odustali ste od zahtjeva");
		zamgerlog("odustao od zahtjeva za potvrdu $id", 2);
		zamgerlog2("odustao od zahtjeva za potvrdu", $id);
	}

	if ($akcija == "novi") {
		$tip_potvrde = intval($_REQUEST['tip_potvrde']);
		$svrha_potvrde = intval($_REQUEST['svrha_potvrde']);
		$q320 = myquery("INSERT INTO zahtjev_za_potvrdu SET student=$userid, tip_potvrde=$tip_potvrde, svrha_potvrde=$svrha_potvrde, datum_zahtjeva=NOW(), status=1");

		$id = intval(mysql_insert_id());
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

	$q100 = myquery("SELECT zzp.id, tp.id, tp.naziv, zzp.svrha_potvrde, UNIX_TIMESTAMP(zzp.datum_zahtjeva), zzp.status FROM zahtjev_za_potvrdu as zzp, tip_potvrde as tp WHERE zzp.student=$userid and zzp.tip_potvrde=tp.id");
	if (mysql_num_rows($q100) == 0)
		print "<p>Nema otvorenih zahtjeva</p>\n";
	else
		print "<ul>";
	while ($r100 = mysql_fetch_row($q100)) {
		print "<li>$r100[2]<br>\n";
		if ($r100[1] == 1) {
			$q110 = myquery("SELECT naziv FROM svrha_potvrde WHERE id=$r100[3]");
			print "(u svrhu: ".mysql_result($q110,0,0) . ")<br>\n";
		}
		print "Datum i vrijeme zahtjeva: ".date("d.m.Y. H:i:s", $r100[4])."<br>\n";
		if ($r100[5] == 1) {
			print "Status zahtjeva: <font color=\"red\"><b>čeka na obradu</b></font><br>\n";
		} else
			print "Status zahtjeva: <font color=\"green\"><b>obrađen</b></font>\n";

		if ($r100[1] == 1) {
			print "<a href=\"?sta=izvjestaj/potvrda&student=$userid&svrha=$r100[3]\">pogledaj uvjerenje</a> ";
		} else {
			print "<a href=\"?sta=izvjestaj/index2&student=$userid\">pogledaj uvjerenje</a> ";
		}
		if ($r100[5] == 1)
			print "* <a href=\"?sta=student/potvrda&akcija=odustani&id=$r100[0]\">odustani od zahtjeva</a>\n";

		print "</li>\n";
	}
	if (mysql_num_rows($q100) > 0)
		print "</ul>\n";

	?>

	<p>&nbsp;</p>

	<p><b>Novi zahtjev:</b></p>
	<?=genform("POST")?>
	<input type="hidden" name="akcija" value="novi">
	Tip uvjerenja/potvrde: <select name="tip_potvrde">
	<?
	$q200 = myquery("SELECT id, naziv FROM tip_potvrde");
	while ($r200 = mysql_fetch_row($q200))
		print "<option value=\"$r200[0]\">$r200[1]</option>\n";
	?>
	</select><br>

	Izdaje se u svrhu: <select name="svrha_potvrde">
	<?
	$q210 = myquery("SELECT id, naziv FROM svrha_potvrde");
	while ($r210 = mysql_fetch_row($q210))
		print "<option value=\"$r210[0]\">$r210[1]</option>\n";
	?>
	</select><br><br>

	<input type="submit" value=" Pošalji zahtjev ">
	</form>
	<?
}

?>
