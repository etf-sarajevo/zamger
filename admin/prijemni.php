<?

// ADMIN/PRIJEMNI - pomocne akcije za prijemni

// v3.9.1.0 (2008/07/03) + Novi modul admin/prijemni


// TODO: prebaciti na tabelu osoba



function admin_prijemni() {




###############
# Akcije
###############


if ($_POST['akcija'] == "recalc") {
	$od = intval($_POST['od']);
	$do = intval($_POST['do']);
	$fakatradi = $_REQUEST['fakatradi'];
	if ($do<$od) {
		zamgerlog("nije dobar opseg $od-$do",3);
		niceerror("Nije dobar opseg $od-$do");
		return;
	}
	$greska=0;
	for ($i=$od; $i<=$do; $i++) {
		if ($fakatradi!="da") {
			$q5 = myquery("select ime,prezime,opci_uspjeh,kljucni_predmeti from prijemni where id=$i");
			if (mysql_num_rows($q5)<1) {
				$greska=1;
				print "GRESKA!! Nepostojeci ID $i<br />";
				continue;
			} else {
				print "$i. ".mysql_result($q5,0,1)." ".mysql_result($q5,0,0)." - ";
				$stario=mysql_result($q5,0,2);
				$starik=mysql_result($q5,0,3);
			}
		}
		$q10 = myquery("select ocjena from prijemniocjene where prijemni=$i and tipocjene=0");
		$suma=0; $broj=0;
		while ($r10 = mysql_fetch_row($q10)) {
			$suma += $r10[0];
			$broj++;
		}
		$sv = $suma/$broj;
		$sv = round($sv*10)/10;
		$obodova = $sv*8;

		$ksuma=0;
		for ($j=1; $j<=3; $j++) {
			$suma=0; $broj=0;
			$q20 = myquery("select ocjena from prijemniocjene where prijemni=$i and tipocjene=$j");
			while ($r20 = mysql_fetch_row($q20)) {
				$suma += $r20[0];
				$broj++;
			}
			$sv = $suma/$broj;
			$ksuma += $sv;
		}
		$ksuma = $ksuma/3;
		$kbodova = $ksuma*4;
		$kbodova = round($kbodova*10)/10; // SIC!!! zaokruzivanje kljucnih bodova

		if ($fakatradi=="da") {
			if ($obodova != $stario || $kbodova != $starik)
				$q30 = myquery("update prijemni set opci_uspjeh=$obodova, kljucni_predmeti=$kbodova where id=$i");
		} else {
			print "opći uspjeh: $obodova (bilo $stario), ključni predmeti: $kbodova (bilo $starik)";
			if ($obodova != $stario || $kbodova != $starik) print " PAZI!!!";
			print "<br />\n";
		}
	}

	if ($fakatradi=="da") {
		?>
		Završeno!<br />
		<br />
		<a href="?sta=admin/prijemni">Nazad</a>
		<?
	} else {
		if ($greska==0) {
			?>
			<?=genform("POST")?>
			<input type="hidden" name="fakatradi" value="da">
			<input type="submit" value=" Kreni! ">
			</form>
			<?
		}
	}



} else {


?>
<p>&nbsp;</p>
<h3>Prijemni</h3>

<ul><li>Rekalkulacija bodova:<br/>
<?=genform("POST")?>
<input type="hidden" name="akcija" value="recalc">
Od: <input type="text" size="3" name="od">
Do: <input type="text" size="3" name="do">
<input type="submit" value=" Kreni! ">
</form>
<?


}

}

?>