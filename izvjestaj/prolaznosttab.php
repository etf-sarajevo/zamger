<?

// IZVJESTAJ/PROLAZNOSTTAB - Tabelarni pregled prolaznosti



function izvjestaj_prolaznosttab() {


?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>
<p>Datum i vrijeme izvještaja: <?=date("d. m. Y. H:i");?></p>

<h2>Tabelarni pregled prolaznosti</h2>
<?


if ($_REQUEST['sa_ponovcima'] == "da") {
	$sa_ponovcima = "";
} else {
	$sa_ponovcima = "and ponovac=0";
}

// FIXME: Ovaj izvještaj radi samo za dva ciklusa studija koji traju 6 odnosno 4 semestara


?>
<center>
<table border="1" cellspacing="0" cellpadding="4">
	<tr>
		<td rowspan="3" bgcolor="#EEEEEE" align="center" valign="center">Akademska<br>godina*</td>
		<td colspan="5" bgcolor="#EEEEEE" align="center" valign="center">Broj redovnih studenata<br>
		(svi studijski programi)</td>
		<td colspan="5" bgcolor="#EEEEEE" align="center" valign="center">Prolaznost studenata (%)**</td>
	</tr>
	<tr>
		<td align="center" valign="center" colspan="3">1. ciklus studija</td>
		<td align="center" valign="center" colspan="2">2. ciklus studija</td>
		<td align="center" valign="center" colspan="3">1. ciklus studija</td>
		<td align="center" valign="center" colspan="2">2. ciklus studija</td>
	<tr>
		<td align="center" valign="center">I godina</td>
		<td align="center" valign="center">II godina</td>
		<td align="center" valign="center">III godina</td>
		<td align="center" valign="center">I godina</td>
		<td align="center" valign="center">II godina</td>
		<td align="center" valign="center">I -> II</td>
		<td align="center" valign="center">II -> III</td>
		<td align="center" valign="center">Završen ciklus</td>
		<td align="center" valign="center">I -> II</td>
		<td align="center" valign="center">Završen ciklus</td>
	</tr>
<?



$varijable = array();
$rbr_ag=0;
$boja="DDDDDD";

$q10 = db_query("select id,naziv from akademska_godina order by id");
while ($r10 = db_fetch_row($q10)) {
	$ag = $r10[0];
	if ($ag==0) continue; // nebitna godina
	$rbr_ag++;
?>
	<tr>
		<td bgcolor="#EEEEEE" align="center" valign="center"><b><?=$r10[1]?></b></td>
<?
	for ($ciklus=1; $ciklus<=2; $ciklus++) {
		if ($ciklus==1) $maxsemestar=6; else $maxsemestar=4;
		for ($semestar=1; $semestar<$maxsemestar; $semestar+=2) {
			$q20 = db_query("select count(*) from student_studij as ss, studij as s, tipstudija as ts where ss.semestar=$semestar and ss.akademska_godina=$r10[0] and ss.studij=s.id and s.tipstudija=ts.id and ts.ciklus=$ciklus $sa_ponovcima");
			$broj = db_result($q20,0,0);

			// Redni broj generacije
			$gen_br = $rbr_ag - (($ciklus-1)*3+ceil($semestar/2)) + 1;

			// Određivanje boje po dijagonalama
			if ($boja=="CCCCCC") $boja="FFFFFF";
			else if ($boja=="DDDDDD") $boja="CCCCCC";
			else $boja="DDDDDD";

			if ($broj==0) {
				?>
				<td align="center" valign="center"><b>-</b></td>
				<?
				continue;
			}

			// Određivanje slova
			if ($semestar==1 && $ciklus==1) $ime="a";
			else if ($semestar==3 && $ciklus==1) $ime="b";
			else if ($semestar==5 && $ciklus==1) $ime="c";
			else if ($semestar==1 && $ciklus==2) $ime="d";
			else $ime="e";

			$ime .= $gen_br;

			$varijable[$ime]=$broj; // ovo cemo koristiti kasnije

			if ($ime == $_REQUEST["imena"]) {
				$q20 = db_query("select o.prezime, o.ime from student_studij as ss, studij as s, tipstudija as ts, osoba as o where ss.semestar=$semestar and ss.akademska_godina=$r10[0] and ss.studij=s.id and s.tipstudija=ts.id and ts.ciklus=$ciklus and ss.student=o.id $sa_ponovcima");
				while ($r20 = db_fetch_row($q20)) 
					print "$r20[0] $r20[1]<br>\n";
			}

			// Ispisujemo broj studenata na semestru
			?>
			<td bgcolor="#<?=$boja?>" align="center" valign="center"><b><?=$ime?><br><?=$broj?></b></td>
			<?
		}
	}

	// Procenti

	for ($i=0; $i<4; $i++) {
		if ($i==0) { $brojnik="b"; $nazivnik="a"; }
		else if ($i==1) { $brojnik="c"; $nazivnik="b"; }
		else if ($i==2) { $brojnik="d"; $nazivnik="c"; }
		else if ($i==3) { $brojnik="e"; $nazivnik="d"; }

		$k = $ag-2;
		if ($k<$i) {
			?>
			<td align="center" valign="center"><b>-</b></td>
			<?
			continue;
		}
		
		$gen_br = $ag - $i - 1;
		$brojnik .= $gen_br;
		$nazivnik .= $gen_br;

		$vrijednost = round( $varijable[$brojnik]/$varijable[$nazivnik] * 100, 2);
		?>
		<td bgcolor="#DDDDDD" align="center" valign="center"><b><?=$brojnik?>/<?=$nazivnik?> x 100%<br><?=$vrijednost?>%</b></td>
		<?
	}
	?>
		<td align="center" valign="center"><b>-</b></td>
	</tr><?
}


?>

</table>
</center>
<?


}
