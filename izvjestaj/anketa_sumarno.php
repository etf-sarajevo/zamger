<?

// IZVJESTAJ/ANKETA_SUMARNO - sumarni izvještaj za anketu

function izvjestaj_anketa_sumarno(){

	?><p>Univerzitet u Sarajevu<br/>
	Elektrotehnički fakultet Sarajevo</p>
	<p>Datum i vrijeme izvještaja: <?=date("d. m. Y. H:i");?></p>
	<?

	$anketa = intval($_REQUEST['anketa']);
	
	$q10 = myquery("SELECT UNIX_TIMESTAMP(aa.datum_otvaranja), UNIX_TIMESTAMP(aa.datum_zatvaranja), aa.naziv, ag.naziv, ag.id, ap.semestar, ap.predmet 
		FROM anketa_anketa as aa, akademska_godina as ag, anketa_predmet as ap 
		WHERE aa.id=$anketa and ap.anketa=$anketa and aa.akademska_godina=ag.id");
	if (mysql_num_rows($q10)<1) {
		biguglyerror("Nepostojeća anketa!");
		zamgerlog("Pristup nepostojećoj anketi $anketa",3);
		zamgerlog2("pristup nepostojećoj anketi", $anketa);
		return;
	}

	$datum_otvaranja = mysql_result($q10,0,0);
	$datum_zatvaranja = mysql_result($q10,0,1);
	$naziv_ankete = mysql_result($q10,0,2);
	$naziv_ag = mysql_result($q10,0,3);
	$ag = mysql_result($q10,0,4);
	$semestar = mysql_result($q10,0,5);
	$anketa_predmet = mysql_result($q10,0,6);


	// Ova vrsta izvještaja nema smisla za ankete koje su samo za jedan predmet
	// Stoga ćemo prikazati sve predmete u datoj akademskoj godini i semestru
	if ($anketa_predmet != 0) {
		niceerror("Ova anketa je vezana samo za jedan predmet");
		print "Nema smisla prikazivati sumarni izvještaj za takvu anketu.";
		return;
	}
	

	if (!isset($_REQUEST['tip']) || $_REQUEST['tip'] == "izlaznost") {
		?>
		<h2>Izlaznost na anketu <?=$naziv_ankete?> (godina <?=$naziv_ag?>)</h2>
		<?
	} else if ($_REQUEST['tip'] == "sveukupna") {
		?>
		<h2>Sveukupna ocjena predmeta, anketa <?=$naziv_ankete?> (godina <?=$naziv_ag?>)</h2>
		<?
	}
	
	if ($datum_otvaranja > time()) {
		print "<p><font color=\"red\">Anketa još uvijek nije održana! Datum otvaranja je u budućnosti.</font></p>\n";
	}
	else if ($datum_zatvaranja > time()) {
		print "<p><font color=\"red\">Anketa je još uvijek otvorena! Datum zatvaranja je u budućnosti.</font></p>\n";
	}
	

	// Cachiramo broj studenata po predmetu u nizove
	$broj_studenata = array();
	$q15 = myquery("SELECT pk.predmet, count(*) FROM student_predmet as sp, ponudakursa as pk WHERE sp.predmet=pk.id and pk.akademska_godina=$ag and pk.semestar mod 2=$semestar GROUP BY pk.id");
	while ($r15 = mysql_fetch_row($q15))
		$broj_studenata[$r15[0]] += $r15[1];



	if (!isset($_REQUEST['tip']) || $_REQUEST['tip'] == "izlaznost") {
	
	// Glavna tabela
	?>
	<table cellspacing="0" border="1">
	<tr><th>Predmet</th><th>Uk. studenata</th><th>Nije popunilo anketu</th><th>Poništilo anketu</th><th>Učestvovalo u anketi</th></tr>
	<?
	
	$predmet_bio = array();
	$stari_studij = $stari_semestar = 0;
	$q20 = myquery("SELECT p.id, p.naziv, pk.studij, pk.semestar, s.naziv, p.institucija, s.institucija
		FROM predmet as p, ponudakursa as pk, studij as s
		WHERE pk.akademska_godina=$ag and pk.semestar mod 2=$semestar and pk.predmet=p.id and pk.studij=s.id
		and s.id<=10 ". /* Izbjegavamo ekvivalenciju */ "
		ORDER BY s.tipstudija, s.naziv, pk.semestar, p.naziv");
	while ($r20 = mysql_fetch_row($q20)) {
		// Svrstavamo predmete pod njihov odsjek
		if ($r20[5] != $r20[6] && $r20[5] != 1) continue;

		// Da li je predmet bio?
		$predmet = $r20[0];
		if (in_array($predmet, $predmet_bio)) continue;
		array_push($predmet_bio, $predmet);

		// Preskačemo predmete bez studenata
		if ($broj_studenata[$predmet] == 0) continue;

		// Da li je novi studij
		$naziv_predmeta = $r20[1];
		$studij = $r20[2];
		$semestar = $r20[3];
		if ($studij != $stari_studij || $semestar != $stari_semestar) {
			$naziv_studija = $r20[4];
			print "<tr><td colspan='5'><b>$naziv_studija, $semestar semestar</b></td></tr>\n";
			$stari_studij=$studij;
			$stari_semestar=$semestar;
		}

		print "<tr><td>$naziv_predmeta</td>\n";
		
		/*$q30 = myquery("select count(*) from student_predmet as sp, ponudakursa as pk where sp.predmet=pk.id and pk.predmet=$r20[0] and pk.akademska_godina=$r20[4]");
		$broj_studenata = mysql_result($q30,0,0);*/
		$bs = $broj_studenata[$predmet]; // Kraće pisanje
		print "<td>$bs</td>\n";
		
		$q40 = myquery("select id from anketa_rezultat where anketa=$anketa and zavrsena='Y' and predmet=$r20[0]");
		$broj_neuradjenih = $bs - mysql_num_rows($q40);
		print "<td>$broj_neuradjenih (".procenat($broj_neuradjenih, $bs).")</td>\n";
		
		$ponistenih = $uradjenih = 0;
		while ($r40 = mysql_fetch_row($q40)) {
			$q50 = myquery("select count(*) from anketa_odgovor_rank where rezultat=$r40[0]"); // TODO: dodati i ostale tipove pitanja
			if (mysql_result($q50,0,0)==0) $ponistenih++;
			else $uradjenih++;
		}

		print "<td>$ponistenih (".procenat($ponistenih, $bs).")</td>\n";

		print "<td>$uradjenih (".procenat($uradjenih, $bs).")</td>\n";
		
		print "</tr>\n";

		$suma_bs += $bs;
		$suma_neuradjenih += $broj_neuradjenih;
		$suma_ponistenih += $ponistenih;
		$suma_uradjenih += $uradjenih;
	}

	print "<tr><td colspan='5'><b>UKUPNO:</b></td></tr>\n";
	print "<tr><td>&nbsp;</td><td>$suma_bs</td><td>$suma_neuradjenih (".procenat($suma_neuradjenih, $suma_bs).")</td><td>$suma_ponistenih (".procenat($suma_ponistenih, $suma_bs).")</td><td>$suma_uradjenih (".procenat($suma_uradjenih, $suma_bs).")</td></tr>\n";
	print "</table>\n";
	return;

	}


	if ($_REQUEST['tip'] == "sveukupna") {

	// Anketno pitanje "sveukupna ocjena predmeta"
	$q17 = myquery("SELECT p.id, p.tekst FROM anketa_pitanje as p,anketa_tip_pitanja as t WHERE p.tip_pitanja = t.id and p.anketa=$anketa and p.tip_pitanja=1 order by p.id");
	$the_pitanje = 0;
	while ($r17 = mysql_fetch_row($q17)) {
		if (strstr($r17[1], "ocjena predmeta"))
			$the_pitanje = $r17[0];
	}
	if ($the_pitanje == 0) {
		biguglyerror("Nije pronađeno anketno pitanje 'sveukupna ocjena predmeta'");
		return;
	}

	// Glavna tabela
	?>
	<table cellspacing="0" border="1">
	<tr><th>Predmet</th><th>Sveukupna ocjena</th><th>Odgovora</th></tr>
	<?

	$predmet_bio = array();
	$stari_studij = $stari_semestar = 0;
	$q20 = myquery("SELECT p.id, p.naziv, pk.studij, pk.semestar, s.naziv, p.institucija, s.institucija
		FROM predmet as p, ponudakursa as pk, studij as s
		WHERE pk.akademska_godina=$ag and pk.semestar mod 2=$semestar and pk.predmet=p.id and pk.studij=s.id
		and s.id<=10 ". /* Izbjegavamo ekvivalenciju */ "
		ORDER BY s.tipstudija, s.naziv, pk.semestar, p.naziv");
	while ($r20 = mysql_fetch_row($q20)) {
		// Svrstavamo predmete pod njihov odsjek
		if ($r20[5] != $r20[6] && $r20[5] != 1) continue;

		// Da li je predmet bio?
		$predmet = $r20[0];
		if (in_array($predmet, $predmet_bio)) continue;
		array_push($predmet_bio, $predmet);

		// Preskačemo predmete bez studenata
		if ($broj_studenata[$predmet] == 0) continue;

		// Da li je novi studij
		$naziv_predmeta = $r20[1];
		$studij = $r20[2];
		$semestar = $r20[3];
		if ($studij != $stari_studij || $semestar != $stari_semestar) {
			$naziv_studija = $r20[4];
			print "<tr><td colspan='3'><b>$naziv_studija, $semestar semestar</b></td></tr>\n";
			$stari_studij=$studij;
			$stari_semestar=$semestar;
		}

		print "<tr><td>$naziv_predmeta</td>\n";
		
		$bs = $broj_studenata[$predmet]; // Kraće pisanje
		
		$q40 = myquery("select id from anketa_rezultat where anketa=$anketa and zavrsena='Y' and predmet=$r20[0]");
		$suma_ocjena = $br_ocjena = 0;
		while ($r40 = mysql_fetch_row($q40)) {
			$q50 = myquery("select izbor_id from anketa_odgovor_rank where rezultat=$r40[0] and pitanje=$the_pitanje");
			if (mysql_num_rows($q50) > 0) {
				$suma_ocjena += mysql_result($q50, 0, 0);
				$br_ocjena++;
			}
		}

		if ($br_ocjena > 0) {
			$prosjek = round($suma_ocjena/$br_ocjena, 2);
			$suma_suma_ocjena += ($suma_ocjena/$br_ocjena);
		} else
			$prosjek = 0;
		print "<td>$prosjek</td><td>$br_ocjena (".procenat($br_ocjena, $bs).")</td>\n";

		print "</tr>\n";

		$suma_uradjenih += $br_ocjena;
		$suma_bs += $bs;
		$br_predmeta ++;
	}

	print "<tr><td colspan='3'><b>UKUPNO:</b></td></tr>\n";
	print "<tr><td>&nbsp;</td><td>".round($suma_suma_ocjena/$br_predmeta, 2)."</td><td>$suma_uradjenih (".procenat($suma_uradjenih, $suma_bs).")</td></tr>\n";
	print "</table>\n";
	return;

	} // if ($_REQUEST['tip'] == "sveukupna")


	// naziv predmeta
	$q10 = myquery("select p.naziv,pk.akademska_godina,p.id from predmet as p, ponudakursa as pk where pk.predmet=p.id and p.id=$predmet and pk.akademska_godina=$ag; ");
	$naziv_predmeta = mysql_result($q10,0,0);

	// provjera da li je dati profesor zadužen na predmetu za koji želi pogledat izvještaj
	if (!$user_siteadmin && !$user_studentska) {
		$q20 = myquery("select nivo_pristupa from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
		if (mysql_num_rows($q20)==0) {
			zamgerlog("nastavnik/izvjestaj_anketa privilegije",3);
			zamgerlog2("privilegije");
			biguglyerror("Nemate pravo pregledati ovaj izvještaj!");
			return;
		}
	}
	
	// naziv akademske godine
	$q30 = myquery("select naziv from akademska_godina where id=$ag");
	$naziv_ak_god = mysql_result($q30,0,0);
	
	// da li postoji anketa?
	if ($anketa>0) 
		$q40 = myquery("select id, aktivna from anketa_anketa where akademska_godina= $ag and id=$anketa");
	else {
		$q40 = myquery("select aa.id, aa.aktivna from anketa_anketa as aa where aa.akademska_godina=$ag and (select count(*) from anketa_rezultat as ar where ar.anketa=aa.id and ar.predmet=$predmet)>0 order by id desc"); // prikaži anketu koju je neko popunjavao
		if (mysql_num_rows($q40)<1)
			$q40 = myquery("select id, aktivna from anketa_anketa where akademska_godina=$ag");
	}

	if (mysql_num_rows($q40)==0){
		biguglyerror("Za datu akademsku godinu nije kreirana anketa!");
		return;
	}
	$anketa = mysql_result($q40,0,0);
	$aktivna = mysql_result($q40,0,1);

	if (!$user_siteadmin && !$user_studentska && $aktivna==1) {
		?>
		<h2>Pristup rezultatima ankete nije moguć</h2>
		<p>Odlukom uprave <?=$conf_skr_naziv_institucije_genitiv?>, nastavni ansambl ne može pristupiti rezultatima ankete do isteka određenog roka. Za dodatne informacije predlažemo da kontaktirate službe <?=$conf_skr_naziv_institucije_genitiv?></p>
		<?
		return;
	}

	if ($_REQUEST['komentar'] == "da") {
		// ---------------------------------------------   IZVJESTAJ ZA KOMENTARE ---------------------------------------------
		
		$limit = 5; // broj kometara prikazanih po stranici
		$offset = intval($_REQUEST["offset"]);

	 	$q50 = myquery("select count(*) from anketa_rezultat where predmet=$predmet and anketa = $anketa AND zavrsena='Y'");
		$broj_anketa = mysql_result($q50,0,0);

		?>
		<center>
		<h2>Prikaz svih komentara za predmet <?=$naziv_predmeta?> za akademsku godinu <?=$naziv_ak_god?></h2>
		
		<h3>Broj studenata koji su pristupili anketi je: <?=$broj_anketa?></h3>
		<?
		
		
		// pokupimo sve komentare za dati predmet
		$q60 = myquery("SELECT count(*) FROM anketa_odgovor_text WHERE odgovor<>'' and rezultat IN (SELECT id FROM anketa_rezultat WHERE predmet=$predmet and anketa=$anketa AND zavrsena='Y')");
		$broj_odgovora = mysql_result($q60,0,0);
		$q61 = myquery(" SELECT odgovor FROM anketa_odgovor_text WHERE odgovor<>'' and rezultat IN (SELECT id FROM anketa_rezultat WHERE predmet =$predmet and anketa=$anketa) limit $offset, $limit");
		
		if ($broj_odgovora == 0)
			print "Nema rezultata!";

		else if ($broj_odgovora > $limit) {
			$donja_granica=$offset+1;
			$gornja_granica=$offset+5;
			if ($gornja_granica>$broj_odgovora) $gornja_granica=$broj_odgovora;
				
			print "Prikazujem rezultate $donja_granica-$gornja_granica od $broj_odgovora. Stranica: ";
			for ($i=0; $i < $broj_odgovora; $i+=$limit) {
				$br = intval($i/$limit)+1;
				
				if ($i == $offset)
					print "<b>$br</b> ";
				else
					print "<a href=\"?sta=izvjestaj/anketa&predmet=$predmet&ag=$ag&komentar=da&offset=$i\">$br</a> ";
			}
			print "<br/>";
		}
	
		?>
		<table width="650px"  >
			 <tr>
				<td bgcolor="#6699CC" height="10">   </td>
			</tr>
		<?
		$i=0;
		while ($r61 = mysql_fetch_row($q61)) {
			$komentar = str_replace("\n", "<br/>\n", $r61[0]);
			?><tr>
				<td><hr/></td>
			</tr>
			<tr>
				<td><?=$komentar?></td>
			</tr>
			<?
			$i++;
		}	
		?>
		
		</table> 
		</center>
		<?
		
	}
	// -------------------------------------------------   KRAH IZVJEŠTAJ ZA KOMENTARE  ------------------------------------------------------------------------
	
	// -------------------------------------------------   IZVJEŠTAJ ZA RANK PITANJA  ------------------------------------------------------------------------
	else if ($_REQUEST['rank'] == "da") {

		print "<center>";
		print "<h2>Statistika za predmet $naziv_predmeta za akademsku godinu $naziv_ak_god</h2>\n";

		$q100 = myquery("select count(*) from anketa_rezultat where predmet=$predmet and anketa = $anketa AND zavrsena='Y'" );
		$broj_anketa = mysql_result($q100,0,0);
		print "<h3> Broj studenata koji su pristupili anketi je : $broj_anketa </h3>";
		
		// broj rank pitanja
		$q110 = myquery("SELECT id FROM anketa_pitanje WHERE anketa =$anketa and tip_pitanja =1");
		
		$i = 0;
		while ($r110 = mysql_fetch_row($q110)) {
			$j=$i+1;
			$q120 = myquery("SELECT avg(izbor_id), count(izbor_id) FROM anketa_odgovor_rank WHERE rezultat IN (SELECT id FROM anketa_rezultat WHERE predmet=$predmet and anketa=$anketa AND zavrsena='Y') AND pitanje = $r110[0]");
			$prosjek[$i]=mysql_result($q120,0,0);
			$broj_odgovora[$i]=mysql_result($q120,0,1);
			$i++;
		}
		
		// kupimo pitanja
		$q130 = myquery("SELECT p.id, p.tekst,t.tip FROM anketa_pitanje p,anketa_tip_pitanja t WHERE p.tip_pitanja = t.id and p.anketa =$anketa and p.tip_pitanja=1");

		?>
		
		<table width="800px">
			<tr> 
				<td bgcolor="#6699CC">&nbsp;&nbsp;Pitanje</td><td bgcolor="#6699CC" width='350px'>&nbsp;&nbsp;Prosjek odgovora</td>
			</tr>
		
			<tr>
				<td colspan="2"><hr/></td>
			</tr>
			<tr>
				 <td>&nbsp;</td><td bgcolor="#FF0000" width='350px'>&nbsp;MAX </td>
			</tr>
		<?

		$i=0;
		while ($r130 = mysql_fetch_row($q130)) {
			$tekst=$r130[1];
			$procenat=($prosjek[$i]/5)*100;

			?><tr height='35'>
				<td><?=($i+1)?>. <?=$tekst?><br><font color="#999999"><small>(<?=$broj_odgovora[$i]?> odgovora)</small></font></td>
				<td>
					<table border='0' width='350px'>
					<tr> 
	        				<td height='30' width='<?=$procenat?>%' bgcolor="#CCCCFF"> &nbsp;<?=round($prosjek[$i],2)?></td>
						<td width='<?=(100-$procenat)?>%'> </td>
        				</tr></table> 
				</td>
			</tr>
			<?
			
			$i++;
		}
		$prosjek = array_sum($prosjek)/count($prosjek);


		
		// PITANJA TIPA IZBOR
		
		//kupimo pitanja
		$q200 = myquery("SELECT p.id, p.tekst,t.tip FROM anketa_pitanje p,anketa_tip_pitanja t WHERE p.tip_pitanja = t.id and p.anketa =$anketa and (p.tip_pitanja=3 or p.tip_pitanja=4)");

		if (mysql_num_rows($q200)>0) {
		?>
		
		<table width="800px"  >
			<tr> 
				<td bgcolor="#6699CC"> Pitanje </td> <td bgcolor="#6699CC" width='350px'> Odgovori </td>
			</tr>
		   
			<tr> 
				<td colspan="2"> <hr/>  </td>
			</tr>
			<tr > 
				 <td  > </td> <td bgcolor="#FF0000" width='350px'> &nbsp;MAX </td>
			</tr>
		<?
		$i=0;
		while ($r200 = mysql_fetch_row($q200)) {
			$id_pitanja=$r200[0];
			$tekst=$r200[1];
			$ispis_odgovori = "";

			$q210 = myquery("select ip.id, ip.izbor, ip.dopisani_odgovor, count(oi.rezultat) from anketa_izbori_pitanja as ip, anketa_odgovor_izbori as oi where ip.pitanje=$id_pitanja and oi.pitanje=$id_pitanja and oi.izbor_id=ip.id group by ip.id");
			while ($r210 = mysql_fetch_row($q210)) {
				$ispis_odgovori .= $r210[1]." - ".$r210[3]." (".(round($r210[3]/$broj_anketa, 4)*100)."%)<br>\n";
				if ($r210[2]==1) {
					$q220 = myquery("select odgovor from anketa_odgovor_dopisani where pitanje=$id_pitanja");
					if (mysql_num_rows($q220)==0) continue;
					$ispis_odgovori .= "<font color=\"#BBBBBB\">";
					while ($r220 = mysql_fetch_row($q220)) {
						$ispis_odgovori .= "&quot;".$r220[0]."&quot; ";
					}
					$ispis_odgovori .= "</font><br>\n";
				}
			}

			$q230 = myquery("select count(distinct rezultat) from anketa_odgovor_izbori where pitanje=$id_pitanja");
			$q240 = myquery("select count(*) from anketa_odgovor_izbori where pitanje=$id_pitanja and izbor_id=0");
			$neodg = $broj_anketa - mysql_result($q230,0,0) + mysql_result($q240,0,0);
			$ispis_odgovori .= "<i>neodgovoreno: $neodg (".(round($neodg/$broj_anketa, 4)*100)."%)</i>";

			?><tr height='35'>
				<td><?=($i+1)?>. <?=$tekst?></td>
				<td width="100"><?=$ispis_odgovori?></td>
			</tr>
			<tr><td colspan="2"><hr></td></tr>
			<?
			
			$i++;
		}
		} // mysql_num_rows($result202)


		?>
		<tr> 
				<td colspan="2"> <hr/>  </td>
			</tr>
			  <!--tr > 
				 <td align="right"> Prosjek predmeta : </td> <td  width='350px'> &nbsp;<strong><?=round($prosjek,2)?> </strong> </td>
			 </tr-->
		</table> 
		</center>
		<?
	}
}
?>
