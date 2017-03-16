<?

// IZVJESTAJ/PREGLED_NACIN - Skraceni tabelarni pregled upisanih studenata po tipu i načinu studiranja



function izvjestaj_pregled_nacin() {


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


// Kreiranje niza studija za bsc i msc
$studiji_bsc = $studiji_msc = array();
$trajanje_bsc = $trajanje_msc = 0;
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



// Sumarni izvještaj za studije

// Da li su isti studiji za bsc i msc?
$istisu=1;
foreach ($studiji_bsc as $naziv) {
	if (!in_array($naziv, $studiji_msc)) $istisu=0;
}
// TODO napisati kod
if ($istisu==0) {
	niceerror("Ovaj izvještaj za sada podržava samo isti set studija na oba ciklusa.");
	return;
}


?>
<h2>Pregled upisanih studenata u akademsku <?=$ak_god_naziv?> godinu</h2>



<center>
<table border="1" cellspacing="0" cellpadding="4">
	<tr>
		<td bgcolor="#EEEEEE" align="left" valign="center"><b>Godina</b></td>
		<td bgcolor="#EEEEEE" align="left" valign="center"><b>Status</b></td>
		<td bgcolor="#EEEEEE" align="left" valign="center"><b>Način studiranja</b></td>
		<td bgcolor="#EEEEEE" align="center" valign="center" width="100"><b>UKUPNO</b></td>
		<?

		foreach ($studiji_bsc as $id=>$ime) {
		?>
		<td bgcolor="#EEEEEE" align="center" valign="center" width="100"><b><?=$ime?></b></td>
		<? } ?>
	</tr>
	<tr>
		<? for ($i=0; $i<count($studiji_bsc)+4; $i++) print "<td></td>"; ?>
	</tr>
<?



// Računanje suma
$suma_svega = 0;

$suma_nacin = $suma_tip = $suma_nacin_tip = $suma_godina = $suma_godina_nacin = $suma_godina_tip = $suma_godina_nacin_tip = array();
$suma_studij = $suma_studij_nacin = $suma_studij_tip = $suma_studij_nacin_tip = $suma_godina_studij = $suma_godina_studij_nacin = $suma_godina_studij_tip = $suma_godina_studij_nacin_tip = array();

for ($godina=1; $godina<=$trajanje_bsc+$trajanje_msc; $godina++) {

	if ($godina>$trajanje_bsc) {
		$studiji = $studiji_msc;
		$godina_real = $godina-$trajanje_bsc;
		$dodatak = "MSc";
	} else {
		$studiji = $studiji_bsc;
		$godina_real = $godina;
		$dodatak = "BSc";
	}

	$semestar = $godina_real*2-1;

/*	$q20 = db_query("select count(*) from student_studij where akademska_godina=$ak_god and semestar=$semestar");
	$ukupno = db_result($q20,0,0);

	$q30 = db_query("select count(*) from student_studij as ss where ss.akademska_godina=$ak_god and ss.semestar=$semestar and (select count(*) from student_studij as ss2 where ss.student=ss2.student and ss2.semestar=$semestar and ss2.akademska_godina<$ak_god)=0");
	$redovnih = db_result($q30,0,0);
	$ponovaca = $ukupno-$redovnih;*/
	$ukupno_godina = $redovnih_godina = $ponovaca_godina = 0;

	$ukupno_studij_godina = $redovnih_studij_godina = $ponovaca_studij_godina = array();

	foreach ($studiji as $studij => $ime) {
		for ($ponovac=0; $ponovac<=1; $ponovac++) {
			for ($nacin=1; $nacin<=4; $nacin++) {
				if ($nacin == 2) continue; // Zanemarujemo paralelne

				$q40 = db_query("select count(*) from student_studij where akademska_godina=$ak_god and semestar=$semestar and studij=$studij and ponovac=$ponovac and nacin_studiranja=$nacin");
				$x = db_result($q40,0,0);
				$suma_svega += $x; $suma_nacin[$nacin] += $x; $suma_tip[$ponovac] += $x; $suma_nacin_tip[$nacin][$ponovac] += $x; 
				$suma_godina[$godina] += $x; $suma_godina_nacin[$godina][$nacin] += $x; $suma_godina_tip[$godina][$ponovac] += $x;
				$suma_godina_nacin_tip[$godina][$nacin][$ponovac] += $x;
				$suma_studij[$institucije[$studij]] += $x; $suma_studij_nacin[$institucije[$studij]][$nacin] += $x;
				$suma_studij_tip[$institucije[$studij]][$ponovac] += $x; $suma_studij_nacin_tip[$institucije[$studij]][$nacin][$ponovac] += $x;
				$suma_godina_studij[$godina][$studij] += $x;
				$suma_godina_studij_nacin[$godina][$studij][$nacin] += $x; $suma_godina_studij_tip[$godina][$studij][$ponovac] += $x;
				$suma_godina_studij_nacin_tip[$godina][$studij][$nacin][$ponovac] += $x;
			}
		}
	}
}


// ISPIS
for ($godina=1; $godina<=$trajanje_bsc+$trajanje_msc+1; $godina++) {
	if ($godina>$trajanje_bsc) {
		$studiji = $studiji_msc;
		$godina_real = $godina-$trajanje_bsc;
		$dodatak = "MSc";
	} else {
		$studiji = $studiji_bsc;
		$godina_real = $godina;
		$dodatak = "BSc";
	}

	if ($godina <= $trajanje_bsc+$trajanje_msc) {
	?>
	<tr>
		<td bgcolor="#EEEEEE" align="left" valign="center" rowspan="12"><b><?=$godina_real?>. g. <?=$dodatak?></b></td>
	<?
	} else {
	?>
	<tr>
		<? for ($i=0; $i<count($studiji)+4; $i++) print "<td></td>"; ?>
	</tr>
	<tr>
		<td bgcolor="#EEEEEE" align="left" valign="center" rowspan="12"><b>UKUPNO</b></td>
	<?
	}

	for ($ponovac=0; $ponovac<=2; $ponovac++) {
		$bgcolor = "#FFFFFF";
		if ($ponovac == 0) $ispis="redovan";
		else if ($ponovac == 1) $ispis="ponovac";
		else {
			$ispis="<b>ukupno</b>";
			$bgcolor="#EEEEEE";
		}
		
		?>
		<td align="left" valign="center" rowspan="4" bgcolor="<?=$bgcolor?>"><?=$ispis?></td>
		<?
		
		for ($nacin=1; $nacin<=5; $nacin++) {
			if ($nacin == 2) continue; // Zanemarujemo paralelne

			$bgcolor = "#FFFFFF";
			if ($nacin == 1) $ispis="redovan";
			else if ($nacin == 3) $ispis="samofinansirajući";
			else if ($nacin == 4) $ispis="vanredni";
			else {
				$ispis="<b>ukupno</b>";
				if ($ponovac == 2 || $godina == $trajanje_bsc+$trajanje_msc+1) $bgcolor="#EEEEEE";
			}

			?>
			<td align="left" valign="center" bgcolor="<?=$bgcolor?>"><?=$ispis?></td>
			<td align="center" valign="center" bgcolor="<?=$bgcolor?>">
			<?

			if (($ponovac==2 || $godina == $trajanje_bsc+$trajanje_msc+1) && $nacin==5) print "<b>";

			// Ispis sumarno za studije
			if ($godina == $trajanje_bsc+$trajanje_msc+1)
				if ($ponovac == 2)
					if ($nacin == 5)
						print $suma_svega;
					else
						print $suma_nacin[$nacin];
				else
					if ($nacin == 5)
						print $suma_tip[$ponovac];
					else
						print $suma_nacin_tip[$nacin][$ponovac];
			else
				if ($ponovac == 2)
					if ($nacin == 5)
						print $suma_godina[$godina];
					else
						print $suma_godina_nacin[$godina][$nacin];
				else
					if ($nacin == 5)
						print $suma_godina_tip[$godina][$ponovac];
					else
						print $suma_godina_nacin_tip[$godina][$nacin][$ponovac];
			if (($ponovac==2 || $godina == $trajanje_bsc+$trajanje_msc+1) && $nacin==5) print "</b>";
			?>
			</td>
			<?

			foreach ($studiji as $studij => $ime) {
				?>
				<td align="center" valign="center" bgcolor="<?=$bgcolor?>">
				<?
				if (($ponovac==2 || $godina == $trajanje_bsc+$trajanje_msc+1) && $nacin==5) print "<b>";

				if ($godina == $trajanje_bsc+$trajanje_msc+1)
					if ($ponovac == 2)
						if ($nacin == 5)
							print $suma_studij[$institucije[$studij]];
						else
							print $suma_studij_nacin[$institucije[$studij]][$nacin];
					else
						if ($nacin == 5)
							print $suma_studij_tip[$institucije[$studij]][$ponovac];
						else
							print $suma_studij_nacin_tip[$institucije[$studij]][$nacin][$ponovac];
				else
					if ($ponovac == 2)
						if ($nacin == 5)
							print $suma_godina_studij[$godina][$studij];
						else
							print $suma_godina_studij_nacin[$godina][$studij][$nacin];
					else
						if ($nacin == 5)
							print $suma_godina_studij_tip[$godina][$studij][$ponovac];
						else
							print $suma_godina_studij_nacin_tip[$godina][$studij][$nacin][$ponovac];

				if (($ponovac==2 || $godina == $trajanje_bsc+$trajanje_msc+1) && $nacin==5) print "</b>";
				?>
				</td>
				<?
			}

			?>
			</tr>
			<tr>
			<?
		}
	}
	?>
	</tr>
	<?
}

?>

</table>
</center>
<?


}
