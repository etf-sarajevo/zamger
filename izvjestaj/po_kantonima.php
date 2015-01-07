<?

// IZVJESTAJ/PO_KANTONIMA



function izvjestaj_po_kantonima() {


?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>

<p>Datum i vrijeme izvještaja: <?=date("d. m. Y. H:i");?></p>
<?


// Akademska godina

$ak_god = intval($_REQUEST['akademska_godina']);
if ($ak_god==0) {
	// Aktuelna godina
	$q10 = myquery("select id, naziv from akademska_godina where aktuelna=1");
	$ak_god = mysql_result($q10,0,0);
	$ak_god_naziv = mysql_result($q10,0,1);
} else {
	$q10 = myquery("select naziv from akademska_godina where id=$ak_god");
	$ak_god_naziv = mysql_result($q10,0,0);
}


$kanton=array();
$q20 = myquery("select id, naziv from kanton order by id");
while ($r20 = mysql_fetch_row($q20))
	$kanton[$r20[0]] = $r20[1];


?>

<h2>Spisak studenata po kantonima</h2>
<h3>Za akademsku <?=$ak_god_naziv?>. godinu</h3>

<table border="1" cellspacing="0">
<tr>
<td rowspan="2">&nbsp;</td>
<td rowspan="2" align="center" valign="center">KANTON - ŽUPANIJA<br>
ENTITET - STRANI DRŽ.</td>
<td rowspan="2">&nbsp;</td>
<td colspan="6" align="center">Studenti koji se finansiraju iz budžeta</td>
<td rowspan="2" width="2"></td>
<td colspan="6" align="center">Studenti koji sami plaćaju troškove studija</td>
<td rowspan="2" width="2"></td>
<td colspan="6" align="center">Vanredni studenti</td>
</tr>
<tr>
<td>I god.<br>I cik.</td><td>II god.<br>I cik.</td><td>III god.<br>I cik.</td>
<td>I god.<br>II cik.</td><td>II god.<br>II cik.</td><td>Svega</td>
<td>I god.<br>I cik.</td><td>II god.<br>I cik.</td><td>III god.<br>I cik.</td>
<td>I god.<br>II cik.</td><td>II god.<br>II cik.</td><td>Svega</td>
<td>I god.<br>I cik.</td><td>II god.<br>I cik.</td><td>III god.<br>I cik.</td>
<td>I god.<br>II cik.</td><td>II god.<br>II cik.</td><td>Svega</td>
</tr>
<?

// Kantoni
$rbr=1;
$kanton[1000] = "suma";
$summa_summarum = array();
foreach ($kanton as $id_kantona => $naziv_kantona) {
	if ($id_kantona==1000) {
		?>
		<tr><td rowspan="2" valign="center">&nbsp;</td>
		<td rowspan="2" valign="center"><b><i>UKUPNO</i></b></td>
		<?
	} else {
		?>
		<tr><td rowspan="2" valign="center"><?=$rbr++?>.</td>
		<td rowspan="2" valign="center"><?=$naziv_kantona?></td>
		<?
	}
	
	for ($ponovac=0; $ponovac<=1; $ponovac++) {
		if ($ponovac==0) {
			?>
			<td>Prvi put<br> upisani</td>
			<?
		} else {
			?>
			</tr>
			<tr>
			<td>Obnavljaju<br> godinu</td>
			<?
		}

		for ($nacin=1; $nacin<=4; $nacin++) {
			if ($nacin == 2) continue; // Zanemarujemo paralelne
			$suma=0;
			for ($ciklus=1; $ciklus<=2; $ciklus++) {
				if ($ciklus==1) $maxgodina=3; else $maxgodina=2; // Koristiti studij.trajanje?
				for ($godina=1; $godina<=$maxgodina; $godina++) {
					$semestar = $godina*2-1;
					if ($id_kantona==1000) {
						$broj = $summa_summarum[$ponovac][$nacin][$ciklus][$godina];
					} else {
						$q30 = myquery("select count(*) from student_studij as ss, studij as s, tipstudija as ts, osoba as o where ss.semestar=$semestar and ss.akademska_godina=$ak_god and ss.nacin_studiranja=$nacin and ss.ponovac=$ponovac and ss.student=o.id and o.kanton=$id_kantona and ss.studij=s.id and s.tipstudija=ts.id and ts.ciklus=$ciklus");
						$broj = mysql_result($q30,0,0);
						$summa_summarum[$ponovac][$nacin][$ciklus][$godina] += $broj;
					}

					print "<td>$broj</td>\n";
					$suma += $broj;
				}
			}
			print "<td>$suma</td>"; // kolona "Svega"
			if ($nacin < 4) print "<td width=\"2\"></td>\n"; // dvostruka linija
		}
	}

	print "</tr>\n";

}

print "</table>";

$q40 = myquery("select o.ime, o.prezime, o.id from osoba as o, student_studij as ss where ss.student=o.id and ss.akademska_godina=$ak_god and ss.semestar mod 2 = 1 and (o.kanton=0 or o.kanton=-1) order by o.prezime, o.id");
if (mysql_num_rows($q40)>0) {
	?>
	<p><font color="red"><?=mysql_num_rows($q40)?> studenata nema unesen validan kanton!</font></p>
	<p><a href="#" onclick="javascript:document.getElementById('nema_kanton').style.display='inline'; return false;">Prikaži</a></p>
	<div id="nema_kanton" style="display:none"><?
	while ($r40 = mysql_fetch_row($q40)) {
		print "<a href=\"?sta=studentska/osobe&akcija=edit&osoba=$r40[2]\">$r40[1] $r40[0]</a><br>";
	}
	print "</div>\n";
}


}
