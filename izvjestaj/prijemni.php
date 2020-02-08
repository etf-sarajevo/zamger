<?

// IZVJESTAJ/PRIJEMNI - rang liste kandidata sa prijemnog ispita



function izvjestaj_prijemni() {


// Spisak kandidata za prijemni
// Parametar IZ definiše da li su strani, domaci ili svi

if ($_REQUEST['akcija']=="kandidati") {

	$studij = intval($_REQUEST['studij']);
	$termin=intval($_REQUEST['termin']);

	$uslov="";
	if ($_REQUEST['iz']=='bih') {
		$uslov="AND o.kanton!=13"; 
		$naslov1="(iz BiH)";
	}
	else if ($_REQUEST['iz']=='strani') {
		$uslov="AND o.kanton=13"; 
		$naslov1="(strani državljani)";
	}

	if ($_REQUEST['sort']=="abecedno") {
		$orderby="ORDER BY o.prezime,o.ime";
	} else if ($_REQUEST['sort']=="kodovi") {
		$orderby="ORDER BY po.sifra";
	} else {
		$orderby="ORDER BY ukupno DESC";
	}

	if ($_REQUEST['sakrij_bodove']) $sakrij_bodove = true;

	if ($_REQUEST['borci']) {
		$borci = "AND o.boracke_kategorije=1";
		$borci_naslov = " (samo boračke kategorije)";
	} else {
		$borci = "";
		$borci_naslov = "";
	}

	if ($_REQUEST['kodovi']) { $kodovi = true; } else { $kodovi = false; }


	// Naslov

	if ($studij != 0) {
		$q10 = db_query("SELECT naziv FROM studij WHERE id=$studij");
		$naziv_studija = db_result($q10,0,0);
		$uslov .= "  AND pp.studij_prvi=$studij";
	}

	$q10 = db_query("select ag.naziv, UNIX_TIMESTAMP(pt.datum), pt.ciklus_studija, pt.akademska_godina, pt.datum, pt.predsjednik_komisije from prijemni_termin as pt, akademska_godina as ag where pt.id=$termin and pt.akademska_godina=ag.id");
	if (db_num_rows($q10)<1) {
		niceerror("Nepostojeći termin prijemnog ispita");
		zamgerlog("nepostojeci termin prijemnog $termin", 3);
		zamgerlog2("nepostojeci termin prijemnog", $termin);
		return;
	}
	$ag = db_result($q10,0,0);
	$datum = date("d. m. Y.", db_result($q10,0,1));
	$ciklus = db_result($q10,0,2);
	$predsjednik_komisije = db_result($q10,0,5);

	$naslov3 = "";
	if ($ciklus>1) {
		$naslov3 = " na $ciklus. ciklus studija";
	}

	// Koji po redu termin?
	$q20 = db_query("select count(*)+1 from prijemni_termin where ciklus_studija=$ciklus and akademska_godina=".db_result($q10,0,3)." and datum='".db_result($q10,0,4)."' and id<$termin");
	$rednibroj = db_result($q20,0,0);
	if ($rednibroj==1) { 
		$naslov2 = "kvalifikacioni ispit"; 
	} else { 
		$naslov2 = "$rednibroj. termin kvalifikacionog ispita"; 
	}


	?>
	<h4>Univerzitet u Sarajevu<br />
	Elektrotehnički fakultet Sarajevo</h4>

	<h3>Spisak kandidata <?=$naslov1?> za <?=$naslov2?> <?=$datum?> godine za upis kandidata<?=$naslov3?> u akademsku <?=$ag?> godinu<?=$borci_naslov?></h3>
	<? if ($studij!=0) { ?><h2 align="left">Studij: <?=$naziv_studija?></h2><? } ?>
	<br /><?


	// Glavni upit

	if ($ciklus==1)
		$q = db_query("SELECT o.ime, o.prezime, us.opci_uspjeh, us.kljucni_predmeti, us.dodatni_bodovi, us.opci_uspjeh+us.kljucni_predmeti+us.dodatni_bodovi AS ukupno, po.sifra, o.jmbg
		FROM prijemni_prijava as pp, osoba as o, uspjeh_u_srednjoj as us, prijemni_obrazac as po
		WHERE pp.prijemni_termin=$termin AND pp.osoba=o.id AND o.id=us.osoba AND po.prijemni_termin=$termin AND po.osoba=pp.osoba $uslov $borci $orderby");
	else
		$q = db_query("SELECT o.ime, o.prezime, pcu.opci_uspjeh, pcu.dodatni_bodovi, pcu.opci_uspjeh+pcu.dodatni_bodovi AS ukupno, po.sifra, o.jmbg
		FROM prijemni_prijava as pp, osoba as o, prosliciklus_uspjeh as pcu, prijemni_obrazac as po
		WHERE pp.prijemni_termin=$termin AND pp.osoba=o.id AND o.id=pcu.osoba AND po.prijemni_termin=$termin AND po.osoba=pp.osoba $uslov $borci $orderby");

	
	?>
	<table width="" align="center" border="1" cellpadding="1" cellspacing="0" bordercolor="#000000">
	<tr>
	<td width="10"><b>R.br.</b></td>
	<td><b>Prezime i ime</b></td><? 
	if ($kodovi) { 
		?><td width="100"><b>Kod</b></td>
		<td width="100"><b>JMBG</b></td><? 
	}
	if (!$sakrij_bodove) { 
		?>
		<td width="100"><b>Opći uspjeh</b></td>
		<? if ($ciklus==1) { ?><td width="110"><b>Ključni predmeti</b></td><? } ?>
		<td width="105"><b>Dodatni bodovi</b></td>
		<td width="105"><b>Ukupno bodova</b></td>
		<? 
	}
	?></tr>
	<?
	$brojac = 1;
	while ($kandidat=db_fetch_row($q))
	{
		?>
		<tr>
		<td align="center"><?=$brojac?></td>
		<td><?=$kandidat[1]?> <?=$kandidat[0]?></td><? 
		if ($kodovi) { ?><td><?=$kandidat[6]?></td><td><?=$kandidat[7]?></td><? }
		if (!$sakrij_bodove) { 
			?>
			<td align="center"><? vprintf("%3.2f",$kandidat[2])?></td>
			<td align="center"><? vprintf("%3.2f",$kandidat[3])?></td>
			<td align="center"><? vprintf("%3.2f",$kandidat[4])?></td>
			<? if ($ciklus==1) { ?><td align="center"><? vprintf("%3.2f",$kandidat[5])?></td><? }
		} 
		?></tr>
		<?
		$brojac++;
	}
	?>
	</table>
	<?


// Konačne rang-liste

} else {



	$studij = intval($_REQUEST['studij']);
	$termin = intval($_REQUEST['termin']);

	$upit_dodaj = $naslov_dodaj = "";

	if ($_REQUEST['nacin'] == "o_trosku_kantona") {
		$upit_dodaj .= " AND pp.nacin_studiranja=1";
		$naslov_dodaj .= " prijavljenih za redovan studij (o trošku kantona)";
	} else if ($_REQUEST['nacin'] == "samofinansirajuci") {
		$upit_dodaj .= " AND pp.nacin_studiranja=3";
		$naslov_dodaj .= " prijavljenih za redovan studij (samofinansirajući)";
	} else if ($_REQUEST['nacin'] == "redovni") {
		$upit_dodaj .= " AND (pp.nacin_studiranja=1 OR pp.nacin_studiranja=3)";
		$naslov_dodaj .= " prijavljenih za redovan studij (o trošku kantona ili samofinansirajući)";
	} else if ($_REQUEST['nacin'] == "vanredni") {
		$upit_dodaj .= " AND pp.nacin_studiranja=4";
		$naslov_dodaj .= " prijavljenih za vanredni studij";
	}

	if ($_REQUEST['iz'] == "bih") {
		$upit_dodaj .= " AND o.drzavljanstvo=1";
	} else if ($_REQUEST['iz'] == "strani") {
		$upit_dodaj .= " AND o.drzavljanstvo!=1";
		$naslov_dodaj = " stranih državljana";
	}

	if ($_REQUEST['vrsta'] == "preliminarni") {
		$vrsta_rang_liste = "Preliminarna";
	} else {
		$vrsta_rang_liste = "Konačna";
	}

	if (isset($_REQUEST['anonimno'])) {
		$kolona_ime = "Šifra kandidata";
	} else {
		$kolona_ime = "Prezime i ime";
	}


	// Naslov

	$q10 = db_query("SELECT naziv FROM studij WHERE id=$studij");
	$naziv_studija = db_result($q10,0,0);

	$q10 = db_query("select ag.naziv, UNIX_TIMESTAMP(pt.datum), pt.ciklus_studija, pt.akademska_godina, pt.datum, pt.predsjednik_komisije from prijemni_termin as pt, akademska_godina as ag where pt.id=$termin and pt.akademska_godina=ag.id");
	if (db_num_rows($q10)<1) {
		niceerror("Nepostojeći termin prijemnog ispita");
		zamgerlog("nepostojeci termin prijemnog $termin", 3);
		zamgerlog2("nepostojeci termin prijemnog", $termin);
		return;
	}
	$ag = db_result($q10,0,0);
	$datum = date("d. m. Y.", db_result($q10,0,1));
	$ciklus = db_result($q10,0,2);
	$predsjednik_komisije = db_result($q10,0,5);

	$naslov3 = " u prvu godinu $ciklus. ciklusa studija";

	// Koji po redu termin?
	$q20 = db_query("select count(*)+1 from prijemni_termin where ciklus_studija=$ciklus and akademska_godina=".db_result($q10,0,3)." and datum='".db_result($q10,0,4)."' and id<$termin");
	$rednibroj = db_result($q20,0,0);
	if ($rednibroj==1) { 
		$naslov2 = "prijemnog ispita"; 
	} else { 
		$naslov2 = "$rednibroj. termina prijemnog ispita"; 
	}

	
	?>
	<h4>Univerzitet u Sarajevu<br />
	Elektrotehnički fakultet Sarajevo</h4>

	<h3 align="left"><?=$vrsta_rang_liste?> rang lista kandidata <?=$naslov_dodaj?> nakon <?=$naslov2?> održanog dana <?=$datum?> godine za upis kandidata<?=$naslov3?> u akademsku <?=$ag?> godinu</h3>
	<h2 align="left">Studij: <?=$naziv_studija?></h2>
	<br/>
	<?

	// Kriteriji za upis
	$quk = db_query ("SELECT donja_granica, gornja_granica, kandidati_strani, kandidati_sami_placaju, kandidati_kanton_placa, kandidati_vanredni, prijemni_max
	FROM upis_kriterij WHERE studij=$studij AND prijemni_termin=$termin");
	if (db_num_rows($quk) < 1) {
		niceerror("Nisu definisani kriteriji za upis na studij");
		print "<p>Ne možemo napraviti rang listu ako ne znamo koliko studenata se prima na studij. Idite na link \"Kriteriji za upis\" i podesite parametre.</p>";
		print "<a href=\"javascript:history.go(-1);\">Nazad</a>";
		zamgerlog("nisu definisani kriteriji za upis $studij, $termin", 3);
		zamgerlog2("nisu definisani kriteriji za upis", $studij, $termin);
		return;
	}
	
	$bodovihard = floatval(db_result($quk, 0, 0));
	$bodovisoft = floatval(db_result($quk, 0, 1));
	$kandidatisd = intval(db_result($quk,0,2));
	$kandidatisp = intval(db_result($quk,0,3));
	$kandidatikp = intval(db_result($quk,0,4));
	$kandidativan = intval(db_result($quk,0,5));
	$prijemnimax = floatval(db_result($quk,0,6));

	// Kantoni
	$qkanton = db_query("select id, kratki_naziv from kanton");
	$kantoni = array();
	while ($rkanton = db_fetch_row($qkanton))
		$kantoni[$rkanton[0]] = $rkanton[1];

	// Spisak svih kandidata se učitava u niz
	if ($ciklus==1)
		$qispis = db_query ("SELECT pp.broj_dosjea, CONCAT(o.prezime, ' ', o.ime) 'Prezime i ime', us.opci_uspjeh, o.kanton, us.kljucni_predmeti, us.dodatni_bodovi, pp.rezultat, us.opci_uspjeh+us.kljucni_predmeti+us.dodatni_bodovi+pp.rezultat ukupno, pp.nacin_studiranja, po.sifra
		FROM prijemni_prijava as pp, osoba as o, uspjeh_u_srednjoj as us, prijemni_obrazac as po
		WHERE pp.osoba=o.id AND pp.osoba=us.osoba AND pp.prijemni_termin=$termin AND pp.studij_prvi=$studij AND po.osoba=us.osoba AND po.prijemni_termin=$termin $upit_dodaj
		ORDER BY ukupno DESC");
	else
		$qispis = db_query ("SELECT pp.broj_dosjea, CONCAT(o.prezime, ' ', o.ime) 'Prezime i ime', pcu.opci_uspjeh, o.kanton, 0, pcu.dodatni_bodovi, pp.rezultat, pcu.opci_uspjeh+pcu.dodatni_bodovi+pp.rezultat ukupno, pp.nacin_studiranja, po.sifra
		FROM prijemni_prijava as pp, osoba as o, prosliciklus_uspjeh as pcu, prijemni_obrazac as po
		WHERE pp.osoba=o.id AND pp.osoba=pcu.osoba AND pp.prijemni_termin=$termin AND pp.studij_prvi=$studij AND po.osoba=pcu.osoba AND po.prijemni_termin=$termin $upit_dodaj
		ORDER BY ukupno DESC");
	
	$kandidati = array();
	while($rezultat = db_fetch_row($qispis)) {
		$id = $rezultat[0];
		$kandidati[$id] = array('prezime_ime'=>$rezultat[1], 'kanton'=>$kantoni[$rezultat[3]], 'opci_uspjeh'=>$rezultat[2], 'kanton_id'=>$rezultat[3], 'kljucni_predmeti'=>$rezultat[4], 'dodatni_bodovi'=>$rezultat[5], 'prijemni_ispit'=>$rezultat[6], 'ukupno'=>$rezultat[7], 'nacin_studiranja'=>$rezultat[8]);

		// Iako u konkursu ne piše da se uspjeh zaokružuje, radi ljepšeg ispisa zaokružićemo na dvije decimale
		if ($ciklus!=1) {
			$kandidati[$id]['opci_uspjeh'] = round($kandidati[$id]['opci_uspjeh'], 2);
			$kandidati[$id]['ukupno'] = round($kandidati[$id]['ukupno'], 2);
		}
		
		if (isset($_REQUEST['anonimno'])) $kandidati[$id]['prezime_ime'] = $rezultat[9];
	}

	// Zaglavlje tabele
	?>
	<table align="center" border="1" cellspacing="0" cellpadding="1" bordercolor="#000000">
		<tr>
			<td align="center" width="5%"><b>R.br.</b></td>
			<td align="left"><b><?=$kolona_ime?></b></td>
			<td align="center" width="6%"><b>Kanton</b></td>
			<td align="center" width="10%"><b>Opći uspjeh</b></td>
			<? if ($ciklus==1) { ?><td align="center" width="10%"><b>Ključni predmeti</b></td><? } ?>
			<td align="center" width="10%"><b>Dodatni bodovi</b></td>
			<td align="center" width="10%"><b>Rezultat ispita</b></td>
			<td align="center" width="10%"><b>Ukupno</b></td>
		</tr>
		<?php


	// Troskove studija snosi Kanton
	$i = 1;
	foreach($kandidati as $id => $kandidat) {
		if ($i > $kandidatikp) break;
		if($kandidat['prijemni_ispit'] >= $bodovisoft && $kandidat['nacin_studiranja'] == 1) { // 1 = redovan
			if ($i == 1) {
				?>
				<tr>
					<td colspan="8"><b>Troškove studija snosi Kanton Sarajevo</b></td>
				</tr>
				<?
			}
			?>
			<tr>
				<td align="center"><?=$i?></td>
				<td><?=$kandidat['prezime_ime']?></td>
				<td align="center"><?=$kandidat['kanton']?></td>
				<td align="center"><?=$kandidat['opci_uspjeh']?></td>
				<? if ($ciklus==1) { ?><td align="center"><?=$kandidat['kljucni_predmeti']?></td><? } ?>
				<td align="center"><?=$kandidat['dodatni_bodovi']?></td>
				<td align="center"><?=$kandidat['prijemni_ispit']?></td>
				<td align="center"><b><?=$kandidat['ukupno']?></b></td>
			</tr>
			<?php
			unset($kandidati[$id]);
			$i++;
		}
	}
	
	// Troskove studija snose studenti (polozili prijemni)
	$j = 1;
	foreach($kandidati as $id => $kandidat) {
		if ($j > $kandidatisp) break;
		// 1 = redovan, 3 = samofinansirajući
		if ($kandidat['prijemni_ispit'] >= $bodovisoft && $i >= $kandidatikp && ($kandidat['nacin_studiranja'] == 1 || $kandidat['nacin_studiranja'] == 3)) {
			if ($j == 1) {
				?>
				<tr>
					<td colspan="8"><b>Troškove studija snose sami studenti</b></td>
				</tr>
				<?
			}
			?>
			<tr>
				<td align="center"><?=$i++?></td>
				<td><?=$kandidat['prezime_ime']?></td>
				<td align="center"><?=$kandidat['kanton']?></td>
				<td align="center"><?=$kandidat['opci_uspjeh']?></td>
				<? if ($ciklus==1) { ?><td align="center"><?=$kandidat['kljucni_predmeti']?></td><? } ?>
				<td align="center"><?=$kandidat['dodatni_bodovi']?></td>
				<td align="center"><?=$kandidat['prijemni_ispit']?></td>
				<td align="center"><b><?=$kandidat['ukupno']?></b></td>
			</tr>
			<?
			unset($kandidati[$id]);
			$j++;
		}
	}
	
	// Troskove studija snose studenti (nisu polozili ali su iznad soft limita i ima mjesta)
	$iznadsoftlimita=0;
	foreach($kandidati as $id => $kandidat) {
		if ($j > $kandidatisp) break;
		if ($kandidat['prijemni_ispit'] >= $bodovihard && $kandidat['prijemni_ispit'] <= $bodovisoft && $j<$kandidatisp && ($kandidat['nacin_studiranja'] == 1 || $kandidat['nacin_studiranja'] == 3)) {
			if ($j == 1) {
				?>
				<tr>
					<td colspan="8"><b>Troškove studija snose sami studenti</b></td>
				</tr>
				<?
			}
			?>
			<tr>
				<td align="center"><?=$i++?></td>
				<td><?=$kandidat['prezime_ime']?></td>
				<td align="center"><?=$kandidat['kanton']?></td>
				<td align="center"><?=$kandidat['opci_uspjeh']?></td>
				<? if ($ciklus==1) { ?><td align="center"><?=$kandidat['kljucni_predmeti']?></td><? } ?>
				<td align="center"><?=$kandidat['dodatni_bodovi']?></td>
				<td align="center"><?=$kandidat['prijemni_ispit']?></td>
				<td align="center"><b><?=$kandidat['ukupno']?></b></td>
				<td><font color="red">*</font></td>
			</tr>
			<?
			unset($kandidati[$id]);
			$iznadsoftlimita++;
			$j++;
		}
	}


	// Strani drzavljani
	$k = 1;
	$stranidrzavljani=0;
	foreach($kandidati as $id => $kandidat){
		if ($k > $kandidatisd) break;
		if ($kandidat['prijemni_ispit'] >= $bodovihard && $kandidat['kanton_id'] == 13 && ($kandidat['nacin_studiranja'] == 1 || $kandidat['nacin_studiranja'] == 3))  {
			if ($j == 1 && $k == 1) {
				?>
				<tr>
					<td colspan="8"><b>Troškove studija snose sami studenti</b></td>
				</tr>
				<?
			}
			?>
			<tr>
				<td align="center"><?=$i++?></td>
				<td><?=$kandidat['prezime_ime']?></td>
				<td align="center"><?=$kandidat['kanton']?></td>
				<td align="center"><?=$kandidat['opci_uspjeh']?></td>
				<? if ($ciklus==1) { ?><td align="center"><?=$kandidat['kljucni_predmeti']?></td><? } ?>
				<td align="center"><?=$kandidat['dodatni_bodovi']?></td>
				<td align="center"><?=$kandidat['prijemni_ispit']?></td>
				<td align="center"><b><?=$kandidat['ukupno']?></b></td>
				<td><font color="red">**</font></td>
			</tr>
			<?php
			unset($kandidati[$id]);
			$stranidrzavljani++;
			$k++;
		}
	}
	
/*	// Studenti upadaju na mjesta za strane drzavljane
	$mjestazasd=0;
	if($k <= $kandidatisd) {
		foreach($kandidati as $id => $kandidat) {
			if($kandidat['prijemni_ispit'] >= $bodovihard && $kandidat['kanton_id'] != 13 && ($kandidat['nacin_studiranja'] == 1 || $kandidat['nacin_studiranja'] == 3)) {
				if ($j == 1 && $k == 1) {
					?>
					<tr>
						<td colspan="8"><b>REDOVNI STUDIJ - Troškove studija snose sami studenti</b></td>
					</tr>
					<?
				}
				?>
				<tr>
					<td align="center"><?=$i++?></td>
					<td><?=$kandidat['prezime_ime']?></td>
					<td align="center"><?=$kandidat['kanton']?></td>
					<td align="center"><?=$kandidat['opci_uspjeh']?></td>
					<? if ($ciklus==1) { ?><td align="center"><?=$kandidat['kljucni_predmeti']?></td><? } ?>
					<td align="center"><?=$kandidat['dodatni_bodovi']?></td>
					<td align="center"><?=$kandidat['prijemni_ispit']?></td>
					<td align="center"><b><?=$kandidat['ukupno']?></b></td>
					<td><font color="red">***</font></td>
				</tr>
				<?
				unset($kandidati[$id]);
				$mjestazasd++;
				if ($k++ >= $kandidatisd) break;
			}
		}
	}*/

	
	// Vanredni studenti
	$l = 1;
	if ($l <= $kandidativan) {
		foreach($kandidati as $id => $kandidat) {
			if($kandidat['prijemni_ispit'] >= $bodovihard && $kandidat['nacin_studiranja'] == 4) {
				if ($j == 1 && $k == 1 && $l == 1) {
					?>
					<tr>
						<td colspan="8"><b>Kandidati koji su stekli uvjete za upis</b></td>
					</tr>
					<?
				}
				?>
				<tr>
					<td align="center"><?=$i++?></td>
					<td><?=$kandidat['prezime_ime']?></td>
					<td align="center"><?=$kandidat['kanton']?></td>
					<td align="center"><?=$kandidat['opci_uspjeh']?></td>
					<? if ($ciklus==1) { ?><td align="center"><?=$kandidat['kljucni_predmeti']?></td><? } ?>
					<td align="center"><?=$kandidat['dodatni_bodovi']?></td>
					<td align="center"><?=$kandidat['prijemni_ispit']?></td>
					<td align="center"><b><?=$kandidat['ukupno']?></b></td>
				</tr>
				<?
				unset($kandidati[$id]);
				if ($l++ >= $kandidativan) break;
			}
		}
	}


	// Položili prijemni ali nisu stekli uvjete
	/*$j = 1;
	$palo=0;
	$drugiodsjek=0;
	foreach ($kandidati as $id => $kandidat) {
		if ($kandidat['prijemni_ispit'] < $bodovihard) continue;
		if ($j == 1) {
			$palo=1;
			?>
			<tr>
				<td colspan="8"><b>Kandidati koji su položili prijemni ispit i za koje će se tražiti saglasnost za upis</b></td>
			</tr>
			<?
		}
		?>
		<tr>
				<td align="center"><?=$i++?></td>
				<td><?=$kandidat['prezime_ime']?></td>
				<td align="center"><?=$kandidat['kanton']?></td>
				<td align="center"><?=$kandidat['opci_uspjeh']?></td>
				<? if ($ciklus==1) { ?><td align="center"><?=$kandidat['kljucni_predmeti']?></td><? } ?>
				<td align="center"><?=$kandidat['dodatni_bodovi']?></td>
				<td align="center"><?=$kandidat['prijemni_ispit']?></td>
				<td align="center"><b><?=$kandidat['ukupno']?></b></td>
			</tr>
		<?
		$j++;
		unset($kandidati[$id]);
	}*/

	// Nisu se upisali
	$k = 1;
	$palo=0;
	$drugiodsjek=0;
	foreach ($kandidati as $id => $kandidat) {
		if ($k == 1) {
			$palo=1;
			?>
			<tr>
				<td colspan="8"><b>Kandidati koji nisu stekli uvjete za upis</b></td>
			</tr>
			<?
		}
		?>
		<tr>
				<td align="center"><?=$i++?></td>
				<td><?=$kandidat['prezime_ime']?></td>
				<td align="center"><?=$kandidat['kanton']?></td>
				<td align="center"><?=$kandidat['opci_uspjeh']?></td>
				<? if ($ciklus==1) { ?><td align="center"><?=$kandidat['kljucni_predmeti']?></td><? } ?>
				<td align="center"><?=$kandidat['dodatni_bodovi']?></td>
				<td align="center"><?=$kandidat['prijemni_ispit']?></td>
				<td align="center"><b><?=$kandidat['ukupno']?></b></td>
			</tr>
		<?
		$k++;
	}

	// Legenda

	?>
	</table>
	<br /><?

	if ($iznadsoftlimita>0 || $stranidrzavljani>0 || $mjestazasd>0 || $drugiodsjek>0) {
		?><br />LEGENDA:<br /><?
	}

	if ($iznadsoftlimita>0) {
		?>* - Kandidati nisu položili prijemni ispit, ali se upisuju na upražnjena mjesta za samofinansirajuće studente<br /><?
	}
	if ($stranidrzavljani>0) {
		?>** - Kandidati su strani državljani<br /><?
	}
	if ($mjestazasd>0) {
		?>*** - Kandidati se upisuju kao samofinansirajući studenti na mjesta konkursom predviđena za strane državljane<br /><?
	}
	if ($drugiodsjek>0) {
		?>**** - Kandidati su položili prijemni ispit ali nisu ušli u kvotu predviđenu konkursom za ovaj odsjek. Kandidati se pozivaju na razgovor radi eventualnog izbora drugog odsjeka.<br /><?
	}


	?>
	<p>&nbsp;</p>

	<table border="0" width="100%">
	<tr>
	<td>
	Sarajevo, <?=date("d. m. Y.")?> godine.</td>
	<td align="center">Predsjednik komisije:<br>
	<br>
	<br>
	<?=tituliraj($predsjednik_komisije)?></td>
	</tr></table>
	<?

}

}

?>
