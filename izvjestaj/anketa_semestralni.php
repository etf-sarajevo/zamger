<?

// IZVJESTAJ/ANKETA_SEMESTRALNI - stranica koja generiše izvjestaje: SEMESTRALNI i IZVJESTAJ PO SMJEROVIMA

function izvjestaj_anketa_semestralni() {
	
	$ak_god = intval($_REQUEST['akademska_godina']);
	$studij = intval($_REQUEST['studij']);
	$semestar = intval($_REQUEST['semestar']);

	$dodaj="";
	if ($semestar%2==0) $dodaj="DESC";

	$q011 = myquery("select id from anketa_anketa where akademska_godina=$ak_god order by id $dodaj");
	if (mysql_num_rows($q011)==0){ // da li postoji anketa uopce
		biguglyerror("Ne postoji anketa za datu akademsku godinu!");
		return;
	}


	// -----------------------------------------  SEMESTRALNI IZVJEŠTAJ ---------------------------------------
	if ($_REQUEST['akcija']=="semestralni") {
	
		$q0111 = myquery("select naziv from akademska_godina where id=$ak_god");
		$naziv_ak_god = mysql_result($q0111,0,0);
		
		if ($studij==-1) {
			$naziv_studija = "Svi studiji";
		} else {
			$q0112 = myquery("select naziv from studij where id=$studij");
			$naziv_studija = mysql_result($q0112,0,0);
		}

		$id_ankete = mysql_result($q011,0,0);
		
		$result203 = myquery("SELECT count(*) FROM anketa_pitanje WHERE anketa=$id_ankete and tip_pitanja=1");
		$broj_rank_pitanja = mysql_result($result203,0,0);
	
		?>
		<center>
			<h3>Sumarna statistika za rank pitanja za akademsku godinu <?=$naziv_ak_god?></h3>
			<h3><?=$naziv_studija?></h3>
			<h3><? if ($semestar%2==1) print "Zimski semestar"; else print "Ljetni semestar";?></h3>
		</center>
		<table border="0" align="center">
			<tr>
				<td bgcolor="#6699CC" width='350px'>Prikaz prosjeka odgovora po pitanjima za sve predmete</td>
			</tr>
			<tr>
				<td><hr/></td>
			</tr>
		<?

		// Biramo pitanja za glavnu petlju
		$result2077=myquery("SELECT p.id, p.tekst,t.tip FROM anketa_pitanje p,anketa_tip_pitanja t WHERE p.tip_pitanja = t.id and p.anketa =$id_ankete and p.tip_pitanja=1");
		
		$i=0;
		while ($r202 = mysql_fetch_row($result2077)) {
			print "<tr><td align='center'> $r202[1] </td> <tr>";
			print "<td><img src='izvjestaj/chart_semestralni.php?pitanje=$r202[0]&semestar=$semestar&studij=$studij'><hr/></td></tr>";
		}
		?>
		
		</table>
		<?
	}


	// -----------------------------------------  IZVJEŠTAJ PO SMJEROVIMA ---------------------------------------
	else if ($_REQUEST['akcija']=="po_smjerovima") {
		biguglyerror("Nije u funkciji... žalimo");
		return;
		
		$q0111=myquery("select naziv from akademska_godina where id = $ak_god");
		$naziv_ak_god = mysql_result($q0111,0,0);
		
		//anketa za datu godinu:
		$q011 = myquery("select id from anketa_anketa where akademska_godina= $ak_god");	
		$anketa = mysql_result($q011,0,0);
		
		?>
		<center>
			<h3>Sumarna statistika za rank pitanja za akademsku godinu <?=$naziv_ak_god?>  po smjerovima</h3>
			<h3><?
			if ($semestar==1) print "Zimski semestar";
			else if ($semestar==2) print "Ljetni semestar";
			else print "Cijela godina";?></h3>
		</center>
		
		<table align="center">
			<tr>
			<!-- FIXME povući studije iz baze -->
			<td align="center" bgcolor='#00FF00' height="20" width="150">PGS</td>
			<td align="center" bgcolor='#FF0000' width="150">RI</td>
			<td align="center" bgcolor='#0000FF' width="150">AE</td>
			<td align="center" bgcolor='#00FFFF' width="150">EE</td>
			<td align="center" bgcolor='#FFFF00' width="150">TK</td>
			</tr>
			<tr>
			<td colspan="5">
				<img src='izvjestaj/po_smjerovima_linijski.php?anketa=<?=$anketa?>&semestar=<?=$semestar?>'>
			</td>
			</tr>
		</table>
		<?
	}
}
?>