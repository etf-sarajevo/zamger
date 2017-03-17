<?

// IZVJESTAJ/ANKETA_SEMESTRALNI - stranica koja generiše izvjestaje: SEMESTRALNI i IZVJESTAJ PO SMJEROVIMA



function izvjestaj_anketa_semestralni() {
	
	require_once("lib/utility.php"); // procenat

	$ak_god = intval($_REQUEST['akademska_godina']);
	$studij = intval($_REQUEST['studij']);
	$semestar = intval($_REQUEST['semestar']);

	$dodaj="";
	if ($semestar%2==0) $dodaj="DESC";

	$q10 = db_query("select id from anketa_anketa where akademska_godina=$ak_god order by id $dodaj");
	if (db_num_rows($q10)==0){ // da li postoji anketa uopce
		biguglyerror("Ne postoji anketa za datu akademsku godinu!");
		return;
	}


	// -----------------------------------------  SEMESTRALNI IZVJEŠTAJ ---------------------------------------
	if ($_REQUEST['akcija']=="semestralni") {
	
		$q20 = db_query("select naziv from akademska_godina where id=$ak_god");
		$naziv_ak_god = db_result($q20,0,0);
		
		if ($studij==-1) {
			$naziv_studija = "Svi studiji";
		} else {
			$q30 = db_query("select naziv from studij where id=$studij");
			$naziv_studija = db_result($q30,0,0);
		}

		$id_ankete = db_result($q10,0,0);
		
		$q40 = db_query("SELECT count(*) FROM anketa_pitanje WHERE anketa=$id_ankete and tip_pitanja=1");
		$broj_rank_pitanja = db_result($q40,0,0);
	
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

		// Biramo pitanja za glavnu petlju
		$q50=db_query("SELECT p.id, p.tekst,t.tip FROM anketa_pitanje p,anketa_tip_pitanja t WHERE p.tip_pitanja = t.id and p.anketa=$id_ankete and p.tip_pitanja=1");
		
		$i=0;
		while ($r50 = db_fetch_row($q50)) {
			$i++;
			print "<tr><td align='center'>$i. $r50[1]</td><tr>";
			print "<td><img src='?sta=izvjestaj/chart_semestralni&pitanje=$r50[0]&semestar=$semestar&studij=$studij'><hr/></td></tr>";
		}
		?>
		
		</table>
		<?
	}


	// -----------------------------------------  SEMESTRALNI IZVJEŠTAJ TABELARNO ------------------------------------
	else if ($_REQUEST['akcija']=="semestralni_tab") {
	
		$q20 = db_query("select naziv from akademska_godina where id=$ak_god");
		$naziv_ak_god = db_result($q20,0,0);
		
		$tekst_pitanja = $_REQUEST['tekst_pitanja'];
		
		if ($studij==-1) {
			$naziv_studija = "Svi studiji";
		} else {
			$q30 = db_query("select naziv from studij where id=$studij");
			$naziv_studija = db_result($q30,0,0);
		}
		
		$id_ankete = db_result($q10,0,0);
		
		$q40 = db_query("SELECT count(*) FROM anketa_pitanje WHERE anketa=$id_ankete and tip_pitanja=1");
		$broj_rank_pitanja = db_result($q40,0,0);
		
		// Spisak predmeta
		$predmeti = array();
		
		// Ako je za studij odabrana Prva godina studija onda izbacujemo uslov
		// studij iz sljedeceg upita jer nakon zadnjih izmjena u Zamgeru ne postoji 
		// više studij PGS vec su studenti odmah razvrstani po smjerovima, na ovaj 
		// nacin objedinjujemo razultate svih ponuda kursa za isti predmet
		if ($studij == -1)
			$q50 = db_query("select distinct p.id, p.kratki_naziv, pk.obavezan from ponudakursa pk,predmet p, studij as s, tipstudija as ts where p.id=pk.predmet and pk.semestar=$semestar and pk.studij=s.id and s.tipstudija=2"); // tipstudija 2 = BSc... FIXME?
		else
			$q50 = db_query("select distinct p.id, p.kratki_naziv, pk.obavezan 
			from ponudakursa pk, predmet as p, institucija as i, studij as s, studij as s2
			where p.id=pk.predmet and pk.semestar=$semestar and s.id=$studij and s.institucija=p.institucija and pk.studij=s2.id and s2.tipstudija=s.tipstudija");

		while ($r50 = db_fetch_row($q50)) {
			// Da li je ovaj predmet imao ijednu anketu?
			$q55 = db_query("select count(*) from anketa_rezultat where anketa=$id_ankete and predmet=$r50[0] and zavrsena='Y'");
			if (db_result($q55,0,0)==0) continue;

			$predmeti[$r50[0]]=$r50[1];
			$obavezan[$r50[0]]=$r50[2];
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
		$q60 = db_query("SELECT p.id, p.tekst,t.tip FROM anketa_pitanje p,anketa_tip_pitanja t WHERE p.tip_pitanja = t.id and p.anketa=$id_ankete and p.tip_pitanja=1 order by p.id");
		
		$i=0;
		$maxpredmet=array();
		while ($r60 = db_fetch_row($q60)) {
			$pitanje = $r60[0];
			$i++;
			if (strstr($r60[1], "ocjena predmeta"))
				print "<tr bgcolor=\"#FFFF00\">\n";
			else
				print "<tr>\n";
			if ($tekst_pitanja === "da")
				print "<td width=\"400\">$i. $r60[1]</td>\n";
			else
				print "<td>$i</td>\n";
			$sumpitanje=0;
			foreach ($predmeti as $pid => $pnaziv) {
				$q6730 = db_query("SELECT avg( b.izbor_id ), STDDEV_POP(b.izbor_id), count(*) FROM anketa_rezultat a, anketa_odgovor_rank b WHERE a.id = b.rezultat AND b.pitanje=$pitanje AND a.predmet=$pid AND zavrsena='Y'");
				
				print "<td>".round(db_result($q6730,0,0),2)."</td>\n";
				$sumpitanje += db_result($q6730,0,0);
				if (db_result($q6730,0,2) > $maxpredmet[$pid]) $maxpredmet[$pid]=db_result($q6730,0,2);
			}
			if (count($predmeti)==0)
				print "<td>0</td>\n</tr>\n";
			else
				print "<td>".round($sumpitanje/count($predmeti),2)."</td>\n</tr>\n";
		}
		print "<tr>\n<td>Br.st</td>\n";
		$sumaobaveznih = $brobaveznih = 0;
		foreach ($predmeti as $pid => $pnaziv) {
			print "<td>".$maxpredmet[$pid]."</td>\n";
			if ($obavezan[$pid]) { $sumaobaveznih += $maxpredmet[$pid]; $brobaveznih++; }
		}
		if ($brobaveznih==0)
			print "<td>0</td></tr>\n";
		else
			print "<td>".round($sumaobaveznih/$brobaveznih,1)."</td></tr>\n";

		?>
			</tbody>
		</table> 
		<?
	
	}



	// -----------------------------------------  ECTS krediti ---------------------------------------
	else if ($_REQUEST['akcija']=="ects_krediti") {
		$pitanje = intval($_REQUEST['pitanje']);
	
		$q20 = db_query("select naziv from akademska_godina where id=$ak_god");
		$naziv_ak_god = db_result($q20,0,0);
		
		if ($studij==-1) {
			$naziv_studija = "Svi studiji";
		} else {
			$q30 = db_query("select naziv from studij where id=$studij");
			$naziv_studija = db_result($q30,0,0);
		}
		
		$id_ankete = db_result($q10,0,0);
		
		$q40 = db_query("SELECT count(*) FROM anketa_pitanje WHERE anketa=$id_ankete and tip_pitanja=1");
		$broj_rank_pitanja = db_result($q40,0,0);
		
		// Spisak predmeta
		$predmeti = array();
		
		// Ako je za studij odabrana Prva godina studija onda izbacujemo uslov
		// studij iz sljedeceg upita jer nakon zadnjih izmjena u Zamgeru ne postoji 
		// više studij PGS vec su studenti odmah razvrstani po smjerovima, na ovaj 
		// nacin objedinjujemo razultate svih ponuda kursa za isti predmet
		if ($studij == -1)
			$q50 = db_query("select distinct p.id, p.kratki_naziv, p.ects, (p.sati_predavanja+p.sati_vjezbi+p.sati_tutorijala) from ponudakursa pk,predmet p, studij as s, tipstudija as ts where p.id=pk.predmet and pk.semestar=$semestar and pk.studij=s.id and s.tipstudija=2"); // tipstudija 2 = BSc... FIXME?
		else
			$q50 = db_query("select distinct p.id, p.kratki_naziv, p.ects, (p.sati_predavanja+p.sati_vjezbi+p.sati_tutorijala) from ponudakursa pk,predmet p where p.id=pk.predmet and pk.studij=$studij and pk.semestar=$semestar");

		while ($r50 = db_fetch_row($q50)) {
			// Da li je ovaj predmet imao ijednu anketu?
			if ($studij==-1)
				$q55 = db_query("select count(*) from anketa_rezultat where anketa=$id_ankete and predmet=$r50[0] and zavrsena='Y'");
			else
				$q55 = db_query("select count(*) from anketa_rezultat where anketa=$id_ankete and predmet=$r50[0] and zavrsena='Y' and studij=$studij");
			if (db_result($q55,0,0)==0) continue;
			$predmeti[$r50[0]]=$r50[1];
			$predmet_ects[$r50[0]]=$r50[2];
			$predmet_sati[$r50[0]]=$r50[3];
		}
		
	
		?>
		<center>
			<h3>Usporedba broja ECTS kredita sa anketnim pitanjem o vremenu</h3>
			<h3><?=$naziv_studija?>, <?=$semestar?>. semestar, <?=$naziv_ak_god?></h3>
		</center>
		<table border="1" align="center" cellpadding="2" cellspacing="0">
			<thead>
			<tr>
				<th>1<br />Predmet</th>
				<th>2<br />ECTS</th>
				<th>3<br />Uk. sati<br/> (k2 * 25 sati)</th>
				<th>4<br />Nastave</th>
				<th>5<br />Samostalno<br/> (k3 - k4)</th>
				<th>6<br />Sedmično<br/> (k5 / 15 sedmica)</th>
				<th>7<br />Sedmično<br/> (anketa)</th>
				<th>8<br />Razlika<br/> (k7 - k6) / k7 </th>
				<th>9<br />Broj anketiranih<br />studenata</th>
				<th>10<br />Korekcija<br/> ECTS kredita</th>
			</tr>
			</thead>
			<tbody>
		<?
		
		foreach ($predmeti as $pid => $predmet_naziv) {
			$ects_sati = $predmet_ects[$pid]*25;
			$samostalno_sati = $ects_sati - $predmet_sati[$pid];
			$samostalno_sedmicno = $samostalno_sati / 15;
			$korekcija = "";
			
			// Odredjujemo broj sati po anketi
			$suma = $broj = 0;
			if ($studij==-1)
				$q0376 = db_query("select b.izbor_id from anketa_rezultat a, anketa_odgovor_rank b where a.id = b.rezultat and b.pitanje=$pitanje and a.predmet=$pid and zavrsena='Y'");
			else
				$q0376 = db_query("select b.izbor_id from anketa_rezultat a, anketa_odgovor_rank b where a.id = b.rezultat and b.pitanje=$pitanje and a.predmet=$pid and zavrsena='Y' AND a.studij=$studij");
			
			while ($r0376 = db_fetch_row($q0376)) {
				$suma += $r0376[0] * 2 - 1;
				$broj++;
			}
			if ($broj==0) continue; // Niko nije odgovorio na pitanje o opterećenju
			$anketa_sati = $suma/$broj;
			$razlika_sati = $anketa_sati - $samostalno_sedmicno;
			
			if ($razlika_sati > 0.5 || $razlika_sati < -0.5) {
				$novi_sati = $predmet_sati[$pid] + $anketa_sati*15;
				$korekcija = round($novi_sati/25,0);
				$kor_zarez = ($novi_sati/25) - $korekcija;
				if ($kor_zarez == -0.5) $korekcija -= 0.5;
				if ($kor_zarez > 0.3) $korekcija += 0.5;
				if ($korekcija == $predmet_ects[$pid]) $korekcija="";
			}
			
			$razlika_sati = procenat($razlika_sati, $samostalno_sedmicno);
			
			?>
			<tr>
				<td><?=$predmet_naziv?></td>
				<td><?=$predmet_ects[$pid]?></td>
				<td><?=$ects_sati?></td>
				<td><?=$predmet_sati[$pid]?></td>
				<td><?=$samostalno_sati?></td>
				<td><?=round($samostalno_sedmicno,2)?></td>
				<td><?=round($anketa_sati,2)?></td>
				<td><?=round($razlika_sati,0)?>%</td>
				<td><?=$broj?></td>
				<td><?=$korekcija?></td>
			</tr>
			<?
		}
		/*while ($r60 = db_fetch_row($q60)) {
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
					$q6730 = db_query("SELECT avg( b.izbor_id ), STDDEV_POP(b.izbor_id), count(*) FROM anketa_rezultat a, anketa_odgovor_rank b WHERE a.id = b.rezultat AND b.pitanje=$pitanje AND a.predmet=$pid AND zavrsena='Y'");
				else
					$q6730 = db_query("SELECT avg( b.izbor_id ), STDDEV_POP(b.izbor_id), count(*) FROM anketa_rezultat a, anketa_odgovor_rank b WHERE a.id = b.rezultat AND b.pitanje=$pitanje AND a.predmet=$pid AND zavrsena='Y' AND a.studij=$studij");
				
				print "<td>".round(db_result($q6730,0,0),2)."</td>\n";
				$sumpitanje += db_result($q6730,0,0);
				if (db_result($q6730,0,2) > $maxpredmet[$pid]) $maxpredmet[$pid]=db_result($q6730,0,2);
			}
			print "<td>".round($sumpitanje/count($predmeti),2)."</td>\n</tr>\n";
		}
		print "<tr>\n<td>Br.st</td>\n";
		foreach ($predmeti as $pid => $pnaziv) {
			print "<td>".$maxpredmet[$pid]."</td>\n";
		}
		print "<td>&nbsp;</td></tr>\n";*/

		?>
			</tbody>
		</table> 
		<?
			
	}

	// -----------------------------------------  IZVJEŠTAJ PO SMJEROVIMA ---------------------------------------
	else if ($_REQUEST['akcija']=="po_smjerovima") {
		biguglyerror("Nije u funkciji... žalimo");
		return;
		
		$q0111=db_query("select naziv from akademska_godina where id = $ak_god");
		$naziv_ak_god = db_result($q0111,0,0);
		
		//anketa za datu godinu:
		$q011 = db_query("select id from anketa_anketa where akademska_godina= $ak_god");	
		$anketa = db_result($q011,0,0);
		
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


	// -----------------------------------------  DISTRIBUCIJA OCJENA ---------------------------------------
	else if ($_REQUEST['akcija']=="distribucija") {
		$q0111=db_query("select naziv from akademska_godina where id = $ak_god");
		$naziv_ak_god = db_result($q0111,0,0);
		
		if ($studij==-1) {
			$naziv_studija = "Svi studiji";
		} else {
			$q30 = db_query("select naziv from studij where id=$studij");
			$naziv_studija = db_result($q30,0,0);
		}


	
		?>
		<center>
			<h3>Distribucija ocjena</h3>
			<h3><?=$naziv_studija?>, <?=$semestar?>. semestar, <?=$naziv_ak_god?></h3>
		</center>
		<table border="1" align="center" cellpadding="2" cellspacing="0">
			<thead>
			<tr>
				<th>Predmet</th>
				<th>Ocjena 6<br />(%)</th>
				<th>Ocjena 7<br />(%)</th>
				<th>Ocjena 8<br />(%)</th>
				<th>Ocjena 9<br />(%)</th>
				<th>Ocjena 10<br />(%)</th>
				<th>Odstupanje</th>
			</tr>
			</thead>
			<tbody>
		<?
		
		
		// Kalkulacija prosjeka
		$q100 = db_query("select ocjena, count(*) from konacna_ocjena where akademska_godina=$ak_god and ocjena>5 group by ocjena");
		$sumatotal=0;
		while ($r100 = db_fetch_row($q100)) {
			$suma_ocjena[$r100[0]] = $r100[1];
			$sumatotal += $r100[1];
		}
			
		?>
		<tr bgcolor="#cccccc">
			<td>ETF PROSJEK</td>
			<?
			for ($i=6; $i<=10; $i++) {
				$procenat[$i] = procenat($suma_ocjena[$i], $sumatotal); // trebace nam za odstupanje
				?>
				<td><?=$suma_ocjena[$i]?><br /><?=$procenat[$i]?></td>
				<?
			}

			?>
			<td>0</td>
		</tr>
		<?
		

		if ($studij == -1)
			$q110 = db_query("select distinct p.id, p.kratki_naziv from ponudakursa pk,predmet p, studij as s, tipstudija as ts where p.id=pk.predmet and pk.semestar=$semestar and pk.studij=s.id and s.tipstudija=2"); // tipstudija 2 = BSc... FIXME?
		else
			$q110 = db_query("select distinct p.id, p.kratki_naziv from ponudakursa pk,predmet p where p.id=pk.predmet and pk.studij=$studij and pk.semestar=$semestar");

		while ($r110 = db_fetch_row($q110)) {
			$q120 = db_query("select ocjena, count(*) from konacna_ocjena where akademska_godina=$ak_god and ocjena>5 and predmet=$r110[0] group by ocjena");
			if (db_num_rows($q120)==0) continue;
			$suma_ocjena_pr = array();
			$sumatotal_pr=0;
			$odstupanje=0;
			while ($r120 = db_fetch_row($q120)) {
				$suma_ocjena_pr[$r120[0]] = $r120[1];
				$sumatotal_pr += $r120[1];
			}
			
			?>
			<tr>
				<td><?=$r110[1]?></td>
				<?
				for ($i=6; $i<=10; $i++) {
					$procenat_pr = procenat($suma_ocjena_pr[$i], $sumatotal_pr); // trebace nam za odstupanje
					?>
					<td><?=intval($suma_ocjena_pr[$i])?><br /><?=$procenat_pr?></td>
					<?
					if (floatval($procenat_pr) > floatval($procenat[$i])) $odstupanje += ($procenat_pr - $procenat[$i]);
				}
				?>
				<td><?=$odstupanje?>%</td>
			</tr>
			<?
		}

		
		?>
			</tbody>
		</table>
		<?
	}
}
?>
