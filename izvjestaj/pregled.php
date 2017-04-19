<?

// IZVJESTAJ/PREGLED - Skraceni tabelarni pregled upisanih studenata



function izvjestaj_pregled() {


?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>

<p>Datum i vrijeme izvještaja: <?=date("d. m. Y. H:i");?></p>
<?


// Akademska godina

$ak_god = intval($_REQUEST['akademska_godina']);
if ($ak_god==0) {
	// Aktuelna godina
	$q10 = db_query("select id, naziv from akademska_godina where aktuelna=1");
	$ak_god = db_result($q10,0,0);
	$ak_god_naziv = db_result($q10,0,1);
} else {
	$q10 = db_query("select naziv from akademska_godina where id=$ak_god");
	$ak_god_naziv = db_result($q10,0,0);
}

if (param('po_semestrima')) $po_semestrima=true; else $po_semestrima=false;


// Kreiranje niza studija za bsc i msc
$studiji_bsc = $studiji_msc = $studiji_phd = array();
$trajanje_bsc = $trajanje_msc = $trajanje_phd = 0;
$q20 = db_query("select s.id, s.kratkinaziv, ts.trajanje, s.institucija from studij as s, tipstudija as ts where s.tipstudija=ts.id and ts.ciklus=1 and s.moguc_upis=1 order by s.kratkinaziv");
while ($r20 = db_fetch_row($q20)) {
	$studiji_bsc[$r20[0]]=$r20[1];
	if ($r20[2]>$trajanje_bsc) $trajanje_bsc=$r20[2];
	$institucije[$r20[0]]=$r20[3];
}
$trajanje_bsc /= 2; // broj godina umjesto broj semestara

$q30 = db_query("select s.id, s.kratkinaziv, ts.trajanje, s.institucija from studij as s, tipstudija as ts where s.tipstudija=ts.id and ts.ciklus=2 and s.moguc_upis=1 order by s.kratkinaziv");
while ($r30 = db_fetch_row($q30)) {
	$studiji_msc[$r30[0]]=$r30[1];
	if ($r30[2]>$trajanje_msc) $trajanje_msc=$r30[2];
	$institucije[$r30[0]]=$r30[3];
}
$trajanje_msc /= 2; // broj godina umjesto broj semestara

$q30 = db_query("select s.id, s.kratkinaziv, ts.trajanje, s.institucija from studij as s, tipstudija as ts where s.tipstudija=ts.id and ts.ciklus=3 and s.moguc_upis=1 order by s.kratkinaziv");
while ($r30 = db_fetch_row($q30)) {
	$studiji_phd[$r30[0]]=$r30[1];
	if ($r30[2]>$trajanje_phd) $trajanje_phd=$r30[2];
	$institucije[$r30[0]]=$r30[3];
}
$trajanje_phd /= 2; // broj godina umjesto broj semestara



// Sumarni izvještaj za studije

// Da li su isti studiji za bsc i msc?
$istisu=1;
foreach ($studiji_bsc as $naziv) {
	if (!in_array($naziv, $studiji_msc)) $istisu=0;
}
// TODO napisati kod
if ($istisu==0) {
	niceerror("Ovaj izvještaj za sada podržava samo isti set studija na svim ciklusima.");
	return;
}


?>
<h2>Pregled upisanih studenata u akademsku <?=$ak_god_naziv?> godinu</h2>



<center>
<table border="1" cellspacing="0" cellpadding="4">
	<tr>
		<td bgcolor="#EEEEEE" align="left" valign="center"><b>Godina</b></td>
		<td bgcolor="#EEEEEE" align="left" valign="center"><b>Status</b></td>
		<td bgcolor="#EEEEEE" align="center" valign="center" width="100"><b>UKUPNO</b></td>
<?

foreach ($studiji_bsc as $id=>$ime) {
?>
		<td bgcolor="#EEEEEE" align="center" valign="center" width="100"><b><?=$ime?></b></td>
<? } ?>
	</tr>
<?



// Centralna tabela
$ukupno_studij = $redovnih_studij = $ponovaca_studij = array();
$ukupno_total = $redovnih_total = $ponovaca_total = 0;
$semestar = 1;

for ($godina=1; $godina<=$trajanje_bsc+$trajanje_msc+$trajanje_phd; $godina++) {

	if ($godina>$trajanje_bsc+$trajanje_msc) {
		$studiji = $studiji_phd;
		$godina_real = $godina-($trajanje_bsc+$trajanje_msc);
		$semestar_real = $semestar-($trajanje_bsc+$trajanje_msc)*2;
		$dodatak = "PhD";
	} else if ($godina>$trajanje_bsc) {
		$studiji = $studiji_msc;
		$godina_real = $godina-$trajanje_bsc;
		$semestar_real = $semestar-$trajanje_bsc*2;
		$dodatak = "MSc";
	} else {
		$studiji = $studiji_bsc;
		$godina_real = $godina;
		$semestar_real = $semestar;
		$dodatak = "BSc";
	}

/*	$q20 = db_query("select count(*) from student_studij where akademska_godina=$ak_god and semestar=$semestar");
	$ukupno = db_result($q20,0,0);

	$q30 = db_query("select count(*) from student_studij as ss where ss.akademska_godina=$ak_god and ss.semestar=$semestar and (select count(*) from student_studij as ss2 where ss.student=ss2.student and ss2.semestar=$semestar and ss2.akademska_godina<$ak_god)=0");
	$redovnih = db_result($q30,0,0);
	$ponovaca = $ukupno-$redovnih;*/
	$ukupno_godina = $redovnih_godina = $ponovaca_godina = 0;

	$ukupno_studij_godina = $redovnih_studij_godina = $ponovaca_studij_godina = array();
	foreach ($studiji as $id=>$ime) {
		$q40 = db_query("select count(*) from student_studij where akademska_godina=$ak_god and semestar=$semestar_real and studij=$id");
		$ukupno_studij_godina[$id] = db_result($q40,0,0);
		$ukupno_godina += $ukupno_studij_godina[$id];

		$q50 = db_query("select count(*) from student_studij as ss where ss.akademska_godina=$ak_god and ss.semestar=$semestar_real and ss.studij=$id and ss.ponovac=0");
		$redovnih_studij_godina[$id] = db_result($q50,0,0);
		$ponovaca_studij_godina[$id] = $ukupno_studij_godina[$id]-$redovnih_studij_godina[$id];

		$redovnih_godina += $redovnih_studij_godina[$id];
		$ponovaca_godina += $ponovaca_studij_godina[$id];

		$redovnih_studij[$institucije[$id]] += $redovnih_studij_godina[$id];
		$ponovaca_studij[$institucije[$id]] += $ponovaca_studij_godina[$id];
		$ukupno_studij[$institucije[$id]] += $ukupno_studij_godina[$id];
	}


	// Totali
	$redovnih_total += $redovnih_godina;
	$ponovaca_total += $ponovaca_godina;
	$ukupno_total += $ukupno_godina;


	if ($po_semestrima) $naslov = "$semestar_real. sem. $dodatak"; else $naslov = "$godina_real. g. $dodatak";
	
	
	// Ispis
?>
	<tr>
		<? for ($i=0; $i<count($studiji)+3; $i++) print "<td></td>"; ?>
	</tr>
	<tr>
		<td bgcolor="#EEEEEE" align="left" valign="center"><b><?=$naslov?></b></td>
		<td align="left" valign="center">redovan</td>
		<td align="center" valign="center"><?=$redovnih_godina?></td>
<? foreach ($studiji as $id=>$ime) { ?>
		<td align="center" valign="center"><?=$redovnih_studij_godina[$id]?></td>
<? } ?>
	</tr>
	<tr>
		<td></td>
		<td align="left" valign="center">ponovac</td>
		<td align="center" valign="center"><?=$ponovaca_godina?></td>
<? foreach ($studiji as $id=>$ime) { ?>
		<td align="center" valign="center"><?=$ponovaca_studij_godina[$id]?></td>
<? } ?>
	</tr>
	<tr>
		<td></td>
		<td align="left" valign="center"><b>ukupno</b></td>
		<td align="center" valign="center"><b><?=$ukupno_godina?></b></td>
<? foreach ($studiji as $id=>$ime) { ?>
		<td align="center" valign="center"><b><?=$ukupno_studij_godina[$id]?></b></td>
<? } ?>
	</tr>
<?

	$semestar++;
	if ($po_semestrima && $semestar%2 == 0) { $godina--;}
	else if (!$po_semestrima) $semestar++;

}



// Ispis reda sa sumama.

?>
	<tr>
		<? for ($i=0; $i<count($studiji)+3; $i++) print "<td></td>"; ?>
	</tr>
	<tr>
		<td bgcolor="#EEEEEE" align="left" valign="center"><b>UKUPNO</b></td>
		<td bgcolor="#EEEEEE" align="left" valign="center"><b>redovan</b></td>
		<td bgcolor="#EEEEEE" align="center" valign="center"><b><?=$redovnih_total?></b></td>
<? foreach ($studiji as $id=>$ime) { ?>
		<td bgcolor="#EEEEEE" align="center" valign="center"><b><?=$redovnih_studij[$institucije[$id]]?></b></td>
<? } ?>
	</tr>
	<tr>
		<td></td>
		<td bgcolor="#EEEEEE" align="left" valign="center"><b>ponovac</b></td>
		<td bgcolor="#EEEEEE" align="center" valign="center"><b><?=$ponovaca_total?></b></td>
<? foreach ($studiji as $id=>$ime) { ?>
		<td bgcolor="#EEEEEE" align="center" valign="center"><b><?=$ponovaca_studij[$institucije[$id]]?></b></td>
<? } ?>
	</tr>
	<tr>
		<td></td>
		<td bgcolor="#EEEEEE" align="left" valign="center"><b>ukupno</b></td>
		<td bgcolor="#EEEEEE" align="center" valign="center"><b><?=$ukupno_total?></b></td>
<? foreach ($studiji as $id=>$ime) { ?>
		<td bgcolor="#EEEEEE" align="center" valign="center"><b><?=$ukupno_studij[$institucije[$id]]?></b></td>
<? } ?>
	</tr>
<?


?>

</table>
</center>
<?


}
