<?

// IZVJESTAJ/ANKETA_SEMESTRALNI - stranica koja generiše izvjestaje: SEMESTRALNI i IZVJESTAJ PO SMJEROVIMA

function izvjestaj_anketa_semestralni() {
	
	$ak_god = intval($_REQUEST['akademska_godina']);
	$studij = intval($_REQUEST['studij']);
	$semestar = intval($_REQUEST['semestar']);

	$dodaj="";
	if ($semestar%2==0) $dodaj="DESC";

	$q10 = myquery("select id from anketa_anketa where akademska_godina=$ak_god order by id $dodaj");
	if (mysql_num_rows($q10)==0){ // da li postoji anketa uopce
		biguglyerror("Ne postoji anketa za datu akademsku godinu!");
		return;
	}


	// -----------------------------------------  SEMESTRALNI IZVJEŠTAJ ---------------------------------------
	if ($_REQUEST['akcija']=="semestralni") {
	
		$q20 = myquery("select naziv from akademska_godina where id=$ak_god");
		$naziv_ak_god = mysql_result($q20,0,0);
		
		if ($studij==-1) {
			$naziv_studija = "Svi studiji";
		} else {
			$q30 = myquery("select naziv from studij where id=$studij");
			$naziv_studija = mysql_result($q30,0,0);
		}
		
		$id_ankete = mysql_result($q10,0,0);
		if ($id_ankete==9) $id_ankete=2;
		
		$q40 = myquery("SELECT count(*) FROM anketa_pitanje WHERE anketa=$id_ankete and tip_pitanja=1");
		$broj_rank_pitanja = mysql_result($q40,0,0);
	
		?>
		<center>
			<h3>Sumarna statistika za rank pitanja za akademsku godinu <?=$naziv_ak_god?></h3>
			<h3><?=$naziv_studija?>, <?=$semestar?>. semestar</h3>
		</center>
		<table border="0" align="center">
			<tr>
				<td width='350px'>Prikaz prosjeka odgovora po pitanjima za sve predmete</td>
			</tr>
			<tr>
				<td><hr/></td>
			</tr>
		<?
		// biramo pitanja za glavnu petlju
		$q50=myquery("SELECT p.id, p.tekst,t.tip FROM anketa_pitanje p,anketa_tip_pitanja t WHERE p.tip_pitanja = t.id and p.anketa=$id_ankete and p.tip_pitanja=1");
		
		$i=0;
		while ($r50 = mysql_fetch_row($q50)) {
			$i++;
			print "<tr><td align='center'>$i. $r50[1]</td><tr>";
			print "<td><img src='?sta=izvjestaj/chart_semestralni&pitanje=$r50[0]&semestar=$semestar&studij=$studij'><hr/></td></tr>";
		}
		?>
			
		</table> 
		<?
	
	}

	
	
	// -----------------------------------------  SEMESTRALNI IZVJEŠTAJ ---------------------------------------
	else if ($_REQUEST['akcija']=="semestralni_tab") {
	
		$q20 = myquery("select naziv from akademska_godina where id=$ak_god");
		$naziv_ak_god = mysql_result($q20,0,0);
		
		if ($studij==-1) {
			$naziv_studija = "Svi studiji";
		} else {
			$q30 = myquery("select naziv from studij where id=$studij");
			$naziv_studija = mysql_result($q30,0,0);
		}
		
		$id_ankete = mysql_result($q10,0,0);
		if ($id_ankete==9) $id_ankete=2;
		
		$q40 = myquery("SELECT count(*) FROM anketa_pitanje WHERE anketa=$id_ankete and tip_pitanja=1");
		$broj_rank_pitanja = mysql_result($q40,0,0);
		
		// Spisak predmeta
		$predmeti = array();
		
		// Ako je za studij odabrana Prva godina studija onda izbacujemo uslov
		// studij iz sljedeceg upita jer nakon zadnjih izmjena u Zamgeru ne postoji 
		// više studij PGS vec su studenti odmah razvrstani po smjerovima, na ovaj 
		// nacin objedinjujemo razultate svih ponuda kursa za isti predmet
		if ($studij == -1)
			$q50 = myquery("select distinct p.id, p.kratki_naziv from ponudakursa pk,predmet p, studij as s, tipstudija as ts where p.id=pk.predmet and pk.semestar=$semestar and pk.studij=s.id and s.tipstudija=2"); // tipstudija 2 = BSc... FIXME?
		else
			$q50 = myquery("select distinct p.id, p.kratki_naziv from ponudakursa pk,predmet p where p.id=pk.predmet and pk.studij=$studij and pk.semestar=$semestar");

		while ($r50 = mysql_fetch_row($q50)) {
			// Da li je ovaj predmet imao ijednu anketu?
			if ($studij==-1)
				$q55 = myquery("select count(*) from anketa_rezultat where anketa=$id_ankete and predmet=$r50[0] and zavrsena='Y'");
			else
				$q55 = myquery("select count(*) from anketa_rezultat where anketa=$id_ankete and predmet=$r50[0] and zavrsena='Y' and studij=$studij");
			if (mysql_result($q55,0,0)==0) continue;
			$predmeti[$r50[0]]=$r50[1];
		}
		
	
		?>
		<center>
			<h3>Sumarna statistika za rank pitanja za akademsku godinu <?=$naziv_ak_god?> - tabelarno</h3>
			<h3><?=$naziv_studija?>, <?=$semestar?>. semestar</h3>
		</center>
		<table border="1" align="center" cellpadding="2" cellspacing="0">
			<thead>
			<tr>
				<th>Pitanje</th>
				<?
				foreach ($predmeti as $naziv) { print "<th>$naziv</th>\n"; }
				?>
				<th>Prosjek</th>
			</tr>
			</thead>
			<tbody>
		<?
		// biramo pitanja za glavnu petlju
		$q60 = myquery("SELECT p.id, p.tekst,t.tip FROM anketa_pitanje p,anketa_tip_pitanja t WHERE p.tip_pitanja = t.id and p.anketa=$id_ankete and p.tip_pitanja=1");
		
		$i=0;
		$maxpredmet=array();
		while ($r60 = mysql_fetch_row($q60)) {
			$pitanje = $r60[0];
			$i++;
			if (strstr($r60[1], "ocjena predmeta"))
				print "<tr bgcolor=\"#FFFF00\">\n";
			else
				print "<tr>\n";
			print "<td>$i</td>\n";
			$sumpitanje=0;
			foreach ($predmeti as $pid => $pnaziv) {
				if ($studij==-1)
					$q6730 = myquery("SELECT avg( b.izbor_id ), STDDEV_POP(b.izbor_id), count(*) FROM anketa_rezultat a, anketa_odgovor_rank b WHERE a.id = b.rezultat AND b.pitanje=$pitanje AND a.predmet=$pid AND zavrsena='Y'");
				else
					$q6730 = myquery("SELECT avg( b.izbor_id ), STDDEV_POP(b.izbor_id), count(*) FROM anketa_rezultat a, anketa_odgovor_rank b WHERE a.id = b.rezultat AND b.pitanje=$pitanje AND a.predmet=$pid AND zavrsena='Y' AND a.studij=$studij");
				
				print "<td>".round(mysql_result($q6730,0,0),2)."</td>\n";
				$sumpitanje += mysql_result($q6730,0,0);
				if (mysql_result($q6730,0,2) > $maxpredmet[$pid]) $maxpredmet[$pid]=mysql_result($q6730,0,2);
			}
			print "<td>".round($sumpitanje/count($predmeti),2)."</td>\n</tr>\n";
		}
		print "<tr>\n<td>Br.st</td>\n";
		foreach ($predmeti as $pid => $pnaziv) {
			print "<td>".$maxpredmet[$pid]."</td>\n";
		}
		print "<td>&nbsp;</td></tr>\n";

		?>
			</tbody>
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