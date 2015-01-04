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
	$prijemni_termin = intval($_REQUEST['prijemni_termin']);
	$fakatradi = $_REQUEST['fakatradi'];
	if ($do<$od) {
		zamgerlog("nije dobar opseg $od-$do",3);
		niceerror("Nije dobar opseg $od-$do");
		return;
	}
	$greska=0;
	
	$q10 = myquery("SELECT ciklus_studija FROM prijemni_termin WHERE id=$prijemni_termin");
	$q20 = myquery("SELECT osoba, broj_dosjea FROM prijemni_prijava WHERE prijemni_termin=$prijemni_termin AND broj_dosjea>=$od AND broj_dosjea<=$do");
	if (mysql_result($q10,0,0) == 1) {
		// Popraviti!
		print "ne ovdje!!!";
		/*for ($i=$od; $i<=$do; $i++) {
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
		}*/
	} else {
		while ($r20 = mysql_fetch_row($q20)) {
			$osoba = $r20[0];
			$q30 = myquery("SELECT ocjena FROM prosliciklus_ocjene WHERE osoba=$osoba");
			$sumaocjena = $brojocjena = 0;
			while ($r30 = mysql_fetch_row($q30)) {
				$sumaocjena += $r30[0];
				$brojocjena++;
			}
			$bodovi = round(($sumaocjena / $brojocjena) * 100) / 10;
			if ($fakatradi!="da") {
				$q40 = myquery("SELECT o.ime, o.prezime, pcu.opci_uspjeh FROM osoba as o, prosliciklus_uspjeh as pcu WHERE o.id=$osoba AND pcu.osoba=$osoba");
				$imepr = mysql_result($q40,0,1)." ".mysql_result($q40,0,0);
				$stari_bodovi = mysql_result($q40,0,2);
				$bd = $r20[1];
				if ($stari_bodovi != $bodovi) {
					print "$bd. $imepr ($stari_bodovi -> $bodovi)<br>";
				}
			} else {
				$q50 = myquery("UPDATE prosliciklus_uspjeh SET opci_uspjeh=$bodovi WHERE osoba=$osoba");
			}
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


} else if ($_GET["akcija"] == "spisak") {


$termin = intval($_REQUEST['termin']);

$q = myquery("SELECT o.id, o.ime, o.prezime, s.kratkinaziv, po.sifra FROM `prijemni_obrazac` as po, osoba as o, prijemni_prijava as pp, studij as s WHERE po.osoba=o.id and po.prijemni_termin=$termin and pp.osoba=o.id and pp.prijemni_termin=$termin and pp.studij_prvi=s.id order by o.prezime, o.ime");
?>
<table border="1" cellspacing="0">
<tr><th>Zamger ID</th><th>Ime</th><th>Prezime</th><th>Studij</th><th>Šifra</th></tr>
<?
while ($r = mysql_fetch_row($q)) {
	?>
	<tr><td><?=$r[0]?></td><td><?=$r[1]?></td><td><?=$r[2]?></td><td><?=$r[3]?></td><td><?=$r[4]?></td>
	</tr>
	<?
}


print "</table>";

} else {


?>
<p>&nbsp;</p>
<h3>Prijemni</h3>

<ul><li>Rekalkulacija bodova:<br/>
<?=genform("POST")?>
<input type="hidden" name="akcija" value="recalc">
<select name="prijemni_termin">
<?
$q100 = myquery("SELECT pt.id, ag.naziv, UNIX_TIMESTAMP(pt.datum), pt.ciklus_studija FROM prijemni_termin as pt, akademska_godina as ag WHERE pt.akademska_godina=ag.id ORDER BY ag.id DESC, pt.datum DESC");
while ($r100 = mysql_fetch_row($q100)) {
	print "<option value=\"$r100[0]\">$r100[3]. ciklus, ".date("d.m.Y", $r100[2])." ($r100[1])</option>\n";
}
?></select><br>
Od: <input type="text" size="3" name="od">
Do: <input type="text" size="3" name="do">
<input type="submit" value=" Kreni! ">
</form>
</ul>

<hr>

<?=genform("POST")?>
<h3>Spisak imena sa šiframa</h3>

<a href="?sta=admin/prijemni&akcija=spisak&termin=25">Spisak za 2012. 2 ciklus</a>
</form>
<?


}

}

?>