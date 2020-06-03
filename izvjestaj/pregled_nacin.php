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

if (param('po_semestrima')) $po_semestrima=true; else $po_semestrima=false;


// Kreiranje niza studija za bsc i msc
$studiji_bsc = $studiji_msc = $studiji_phd = $studij_trajanje = $studiji_svi = array();
$trajanje_bsc = $trajanje_msc = $trajanje_phd = 0;

// Upit za prvi ciklus - tu ćemo spojiti i stručni studij
$q20 = db_query("select s.id, s.kratkinaziv, ts.trajanje, s.institucija from studij as s, tipstudija as ts where s.tipstudija=ts.id and (ts.ciklus=1 or ts.ciklus=99) and s.moguc_upis=1 order by s.kratkinaziv");
while ($r20 = db_fetch_row($q20)) {
	$studiji_bsc[$r20[0]]=$r20[1];
	$studij_trajanje[$r20[0]] = $r20[2];
	if ($r20[2] > $trajanje_bsc) $trajanje_bsc=$r20[2];
	$institucije[$r20[0]]=$r20[3];
	$studiji_svi[] = $r20[1];
}
$trajanje_bsc /= 2; // broj godina umjesto broj semestara

$q30 = db_query("select s.id, s.kratkinaziv, ts.trajanje, s.institucija from studij as s, tipstudija as ts where s.tipstudija=ts.id and ts.ciklus=2 and s.moguc_upis=1 order by s.kratkinaziv");
while ($r30 = db_fetch_row($q30)) {
	$studiji_msc[$r30[0]]=$r30[1];
	$studij_trajanje[$r30[0]] = $r30[2];
	if ($r30[2]>$trajanje_msc) $trajanje_msc=$r30[2];
	$institucije[$r30[0]]=$r30[3];
	$studiji_svi[] = $r30[1];
}
$trajanje_msc /= 2; // broj godina umjesto broj semestara

$q40 = db_query("select s.id, s.kratkinaziv, ts.trajanje, s.institucija from studij as s, tipstudija as ts where s.tipstudija=ts.id and ts.ciklus=3 and s.moguc_upis=1 order by s.kratkinaziv");
while ($r40 = db_fetch_row($q40)) {
	$studiji_phd[$r40[1]] = $r40[0];
	$studij_trajanje[$r40[0]] = $r40[2];
	if ($r40[2] > $trajanje_phd) $trajanje_phd = $r40[2];
	$institucije[$r40[1]] = $r40[3];
	$studiji_svi[] = $r40[1];
}
$trajanje_phd /= 2; // broj godina umjesto broj semestara

// Sumarni izvještaj za studije

$studiji_svi = array_unique($studiji_svi);
sort($studiji_svi);


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
	
	foreach ($studiji_svi as $ime) {
		?>
		<td bgcolor="#EEEEEE" align="center" valign="center" width="100"><b><?=$ime?></b></td>
	<? } ?>
	</tr>
	<tr>
		<? for ($i=0; $i<count($studiji_svi)+4; $i++) print "<td></td>"; ?>
	</tr>
<?



// Računanje suma
$suma_svega = 0;

$suma_nacin = $suma_tip = $suma_nacin_tip = $suma_godina = $suma_godina_nacin = $suma_godina_tip = $suma_godina_nacin_tip = array();
$suma_studij = $suma_studij_nacin = $suma_studij_tip = $suma_studij_nacin_tip = $suma_godina_studij = $suma_godina_studij_nacin = $suma_godina_studij_tip = $suma_godina_studij_nacin_tip = array();
$semestar = 1;

for ($godina=1; $godina<=$trajanje_bsc+$trajanje_msc+$trajanje_phd; $godina++) {
	
	if ($godina>$trajanje_bsc+$trajanje_msc) {
		$studiji = $studiji_phd;
		$godina_real = $godina - ($trajanje_bsc + $trajanje_msc);
		$semestar_real = $semestar - ($trajanje_bsc + $trajanje_msc) * 2;
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
	$ukupno_godina = $redovnih_godina = $ponovaca_godina = $apsolvenata_godina = 0;

	$ukupno_studij_godina = $redovnih_studij_godina = $ponovaca_studij_godina = $apsolvenata_studij_godina = array();

	foreach ($studiji as $studij => $ime) {
		if ($semestar_real <= $studij_trajanje[$studij]) {
			// Ukinuti sljedeće godine (ovo je hack jer u zimskom semestru nisu bili evidentirani apsolventi)
			if ($semestar_real == $studij_trajanje[$studij]-1) $semestar_real++;
			for ($ponovac = 0; $ponovac <= 2; $ponovac++) {
				if ($ponovac == 2)
					$dodaj_upit = "status_studenta=1";
				else
					$dodaj_upit = "ponovac=$ponovac and status_studenta!=1";
				for ($nacin = 1; $nacin <= 4; $nacin++) {
					if ($nacin == 2) continue; // Zanemarujemo paralelne
					
					$q40 = db_query("select count(*) from student_studij where akademska_godina=$ak_god and semestar=$semestar_real and studij=$studij and $dodaj_upit and nacin_studiranja=$nacin");
					$x = db_result($q40, 0, 0);
					$suma_svega += $x;
					$suma_nacin[$nacin] += $x;
					$suma_tip[$ponovac] += $x;
					$suma_nacin_tip[$nacin][$ponovac] += $x;
					$suma_godina[$godina] += $x;
					$suma_godina_nacin[$godina][$nacin] += $x;
					$suma_godina_tip[$godina][$ponovac] += $x;
					$suma_godina_nacin_tip[$godina][$nacin][$ponovac] += $x;
					$suma_studij[$institucije[$studij]] += $x;
					$suma_studij_nacin[$institucije[$studij]][$nacin] += $x;
					$suma_studij_tip[$institucije[$studij]][$ponovac] += $x;
					$suma_studij_nacin_tip[$institucije[$studij]][$nacin][$ponovac] += $x;
					$suma_godina_studij[$godina][$studij] += $x;
					$suma_godina_studij_nacin[$godina][$studij][$nacin] += $x;
					$suma_godina_studij_tip[$godina][$studij][$ponovac] += $x;
					$suma_godina_studij_nacin_tip[$godina][$studij][$nacin][$ponovac] += $x;
				}
			}
		}
	}
	$semestar++;
	if ($po_semestrima && $semestar%2 == 0) { $godina--;}
	else if (!$po_semestrima) $semestar++;
}


// ISPIS
for ($godina=1; $godina<=$trajanje_bsc+$trajanje_msc+$trajanje_phd+1; $godina++) {
	if ($godina>$trajanje_bsc+$trajanje_msc) {
		$studiji = $studiji_phd;
		$godina_real = $godina - $trajanje_bsc - $trajanje_msc;
		$dodatak = "PhD";
	} else if ($godina>$trajanje_bsc) {
		$studiji = $studiji_msc;
		$godina_real = $godina-$trajanje_bsc;
		$dodatak = "MSc";
	} else {
		$studiji = $studiji_bsc;
		$godina_real = $godina;
		$dodatak = "BSc";
	}

	if ($godina <= $trajanje_bsc+$trajanje_msc+$trajanje_phd) {
	?>
	<tr>
		<td bgcolor="#EEEEEE" align="left" valign="center" rowspan="16"><b><?=$godina_real?>. g. <?=$dodatak?></b></td>
	<?
	} else {
	?>
	<tr>
		<? for ($i=0; $i<count($studiji_svi)+4; $i++) print "<td></td>"; ?>
	</tr>
	<tr>
		<td bgcolor="#EEEEEE" align="left" valign="center" rowspan="16"><b>UKUPNO</b></td>
	<?
	}

	for ($ponovac=0; $ponovac<=3; $ponovac++) {
		$bgcolor = "#FFFFFF";
		if ($ponovac == 0) $ispis="redovan";
		else if ($ponovac == 1) $ispis="ponovac";
		else if ($ponovac == 2) $ispis="apsolvent";
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
				if ($ponovac == 3 || $godina == $trajanje_bsc+$trajanje_msc+$trajanje_phd+1) $bgcolor="#EEEEEE";
			}

			?>
			<td align="left" valign="center" bgcolor="<?=$bgcolor?>"><?=$ispis?></td>
			<td align="center" valign="center" bgcolor="<?=$bgcolor?>">
			<?

			if (($ponovac==3 || $godina == $trajanje_bsc+$trajanje_msc+$trajanje_phd+1) && $nacin==5) print "<b>";

			// Ispis sumarno za studije
			if ($godina == $trajanje_bsc+$trajanje_msc+$trajanje_phd+1)
				if ($ponovac == 3)
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
				if ($ponovac == 3)
					if ($nacin == 5)
						print $suma_godina[$godina];
					else
						print $suma_godina_nacin[$godina][$nacin];
				else
					if ($nacin == 5)
						print $suma_godina_tip[$godina][$ponovac];
					else
						print $suma_godina_nacin_tip[$godina][$nacin][$ponovac];
			if (($ponovac==3 || $godina == $trajanje_bsc+$trajanje_msc+$trajanje_phd+1) && $nacin==5) print "</b>";
			?>
			</td>
			<?

			foreach ($studiji_svi as $ime) {
				$studij = 0;
				foreach($studiji as $id => $sime) if ($sime == $ime) $studij = $id;
				?>
				<td align="center" valign="center" bgcolor="<?=$bgcolor?>">
				<?
				if (($ponovac==3 || $godina == $trajanje_bsc+$trajanje_msc+$trajanje_phd+1) && $nacin==5) print "<b>";

				if ($godina == $trajanje_bsc+$trajanje_msc+$trajanje_phd+1)
					if ($ponovac == 3)
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
					if ($ponovac == 3)
						if ($nacin == 5)
							print $suma_godina_studij[$godina][$studij];
						else
							print $suma_godina_studij_nacin[$godina][$studij][$nacin];
					else
						if ($nacin == 5)
							print $suma_godina_studij_tip[$godina][$studij][$ponovac];
						else
							print $suma_godina_studij_nacin_tip[$godina][$studij][$nacin][$ponovac];

				if (($ponovac==3 || $godina == $trajanje_bsc+$trajanje_msc+$trajanje_phd+1) && $nacin==5) print "</b>";
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
	$semestar++;
	if ($po_semestrima && $semestar%2 == 0) { $godina--;}
	else if (!$po_semestrima) $semestar++;
}

?>

</table>
</center>
<?


}
