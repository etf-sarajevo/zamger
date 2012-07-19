<?
	if ($_REQUEST['subakcija'] == "promjena" && check_csrf_token()) {
		$csv_separator = $_REQUEST['csv-separator'];
		if ($csv_separator != ";" && $csv_separator != ",") $csv_separator = my_escape($csv_separator);

		$q500 = myquery("delete from preference where korisnik=$osoba and preferenca='csv-separator'");
		$q510 = myquery("insert into preference set korisnik=$osoba, preferenca='csv-separator', vrijednost='$csv_separator'");
		
		$savjet_dana = intval($_REQUEST['savjet_dana']);

		$q520 = myquery("delete from preference where korisnik=$osoba and preferenca='savjet_dana'");
		$q530 = myquery("insert into preference set korisnik=$osoba, preferenca='savjet_dana', vrijednost=$savjet_dana");

		nicemessage("Zamger opcije uspješno promijenjene");
		zamgerlog("promijenjene zamger opcije", 2);
	}

	?>
	<h2>Opcije Zamgera</h2>
	<p>U ovom trenutku možete prilagoditi sljedeće opcije koje se odnose samo na vaš korisnički nalog:</p>

	<?=genform("POST")?>
	<input type="hidden" name="subakcija" value="promjena">
	<table border="0" cellspacing="0" cellpadding="0">

	<?

	// mass-input-format
	// mass-input-separator
	// - Pošto se ova dva jednostavno zapamte od zadnje primjene, ne vidim svrhu da ih dodajem ovdje

	// csv-separator

	$csv_separatori = array(";", ",");
	$csv_vrijednosti = array("SELECTED", ""); // default je tačka-zarez

	$q100 = myquery("select vrijednost from preference where korisnik=$osoba and preferenca='csv-separator'");
	if (mysql_num_rows($q100)>0) {
		if (mysql_result($q100,0,0) == ",") {
			$csv_vrijednosti[0] = "";
			$csv_vrijednosti[1] = "SELECTED";
		} else if (mysql_result($q100,0,0) != ";") {
			$csv_vrijednosti[0] = "";
			array_push($csv_separatori, mysql_result($q100,0,0));
			array_push($csv_vrijednosti, "SELECTED");
		}
	}

	?>
	<tr>
		<td>Separator za izvoz u CSV format (Excel):</td>
		<td><select name="csv-separator">
		<?
		for ($i=0; $i<count($csv_separatori); $i++) 
			print "<option value=\"$csv_separatori[$i]\" $csv_vrijednosti[$i]\">$csv_separatori[$i]</option>\n";
		?>
		</select></td>
	</tr>
	<?

	// csv-encoding
	// - Treba uvijek biti Windows-1250

	// savjet_dana

	$savjet_dana = "CHECKED";
	$q110 = myquery("select vrijednost from preference where korisnik=$osoba and preferenca='savjet_dana'");
	if (mysql_num_rows($q110)>0 && mysql_result($q110,0,0)==0)
		$savjet_dana = "";

	?>
	<tr>
		<td>Prikaži "Savjet dana":</td>
		<td><input type="checkbox" name="savjet_dana" value="1" <?=$savjet_dana?>></td>
	</tr>
	<?

	// Kraj tabele

	?>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr>
		<td>&nbsp;</td>
		<td><input type="submit" value="Promijeni"></td>
	</tr>

	</table>
	</form>