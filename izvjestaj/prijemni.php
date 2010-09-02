<?

// IZVJESTAJ/PRIJEMNI - rang liste kandidata sa prijemnog ispita

// v3.9.1.0 (2008/07/04) + Kod prebacen iz studentska/prijemni da bi se rasteretio modul
// v4.0.0.0 (2009/02/19) + Release
// v4.0.9.1 (2009/06/19) + Restruktuiranje i ciscenje baze: uvedeni sifrarnici mjesto i srednja_skola, za unos se koristi combo box; tabela prijemni_termin omogucuje definisanje termina prijemnog ispita, sto omogucuje i prijemni ispit za drugi ciklus; pa su dodate i odgovarajuce akcije za kreiranje i izbor termina; licni podaci se sada unose direktno u tabelu osoba, dodaje se privilegija "prijemni" u tabelu privilegija; razdvojene tabele: uspjeh_u_srednjoj (koja se vezuje na osoba i srednja_skola) i prijemni_prijava (koja se vezuje na osoba i prijemni_termin); polja za studij su FK umjesto tekstualnog polja; dodano polje prijemni_termin u upis_kriterij; tabela prijemniocjene preimenovana u srednja_ocjene; ostalo: dodan logging; jmbg proglasen obaveznim; vezujem ocjene iz srednje skole za redni broj, posto se do sada redoslijed ocjena oslanjao na ponasanje baze; nova combobox kontrola
// v4.0.9.2 (2009/09/04) + Popravljen bug, izvjestaj nije podrzavao 0 studenata u nekim kategorijama (npr. kategorija kanton finansira studij), nego je uvijek ubacivao jednog


function izvjestaj_prijemni() {


// Spisak kandidata za prijemni
// Parametar IZ definiše da li su strani, domaci ili svi

if ($_REQUEST['akcija']=="kandidati") {

	$uslov="";
	if ($_REQUEST['iz']=='bih') {
		$uslov="AND o.kanton!=13"; 
		$naslov1="(iz BiH)";
	}
	else if ($_REQUEST['iz']=='strani') {
		$uslov="AND o.kanton=13"; 
		$naslov1="(strani državljani)";
	}

	$termin=intval($_REQUEST['termin']);

	if ($_REQUEST['sort']=="abecedno") {
		$orderby="ORDER BY o.prezime,o.ime";
	} else {
		$orderby="ORDER BY ukupno DESC";
	}

	if ($_REQUEST['sakrij_bodove']) $sakrij_bodove = true;


	// Naslov

	$q10 = myquery("select ag.naziv, UNIX_TIMESTAMP(pt.datum), pt.ciklus_studija, pt.akademska_godina, pt.datum from prijemni_termin as pt, akademska_godina as ag where pt.id=$termin and pt.akademska_godina=ag.id");
	if (mysql_num_rows($q10)<1) {
		niceerror("Nepostojeći termin prijemnog ispita");
		zamgerlog("nepostojeci termin prijemnog $termin", 3);
		return;
	}
	$ag = mysql_result($q10,0,0);
	$datum = date("d. m. Y.", mysql_result($q10,0,1));
	$ciklus = mysql_result($q10,0,2);

	$naslov3 = "";
	if ($ciklus>1) {
		$naslov3 = " na $ciklus. ciklus studija";
	}

	// Koji po redu termin?
	$q20 = myquery("select count(*)+1 from prijemni_termin where ciklus_studija=$ciklus and akademska_godina=".mysql_result($q10,0,3)." and datum='".mysql_result($q10,0,4)."' and id<$termin");
	$rednibroj = mysql_result($q20,0,0);
	if ($rednibroj==1) { 
		$naslov2 = "kvalifikacioni ispit"; 
	} else { 
		$naslov2 = "$rednibroj. termin kvalifikacionog ispita"; 
	}


	?>
	<h4>Univerzitet u Sarajevu<br />
	Elektrotehnički fakultet Sarajevo</h4>

	<h3>Spisak kandidata <?=$naslov1?> za <?=$naslov2?> <?=$datum?> godine za upis kandidata<?=$naslov3?> u akademsku <?=$ag?> godinu</h3>
	<br /><?


	// Glavni upit

	if ($ciklus==1)
		$q = myquery("SELECT o.ime, o.prezime, us.opci_uspjeh, us.kljucni_predmeti, us.dodatni_bodovi, us.opci_uspjeh+us.kljucni_predmeti+us.dodatni_bodovi AS ukupno FROM prijemni_prijava as pp, osoba as o, uspjeh_u_srednjoj as us WHERE pp.prijemni_termin=$termin AND pp.osoba=o.id AND o.id=us.osoba $uslov $orderby");
	else
		$q = myquery("SELECT o.ime, o.prezime, pcu.opci_uspjeh, pcu.dodatni_bodovi, pcu.opci_uspjeh+pcu.dodatni_bodovi AS ukupno FROM prijemni_prijava as pp, osoba as o, prosliciklus_uspjeh as pcu WHERE pp.prijemni_termin=$termin AND pp.osoba=o.id AND o.id=pcu.osoba $uslov $orderby");

	
	?>
	<table width="" align="center" border="1" cellpadding="1" cellspacing="0" bordercolor="#000000">
	<tr>
	<td width="10"><b>R.br.</b></td>
	<td><b>Prezime i ime</b></td><? 
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
	while ($kandidat=mysql_fetch_row($q))
	{
		?>
		<tr>
		<td align="center"><?=$brojac?></td>
		<td><?=$kandidat[1]?> <?=$kandidat[0]?></td><? 
		if (!$sakrij_bodove) { 
			?>
			<td align="center"><?=$kandidat[2]?></td>
			<td align="center"><?=$kandidat[3]?></td>
			<td align="center"><?=$kandidat[4]?></td>
			<? if ($ciklus==1) { ?><td align="center"><?=$kandidat[5]?></td><? }
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



	$studij = intval($_REQUEST['_lv_column_studij']);
	$termin = intval($_REQUEST['termin']);


	// Naslov

	$q10 = myquery("SELECT naziv FROM studij WHERE id=$studij");
	$naziv_studija = mysql_result($q10,0,0);

	$q10 = myquery("select ag.naziv, UNIX_TIMESTAMP(pt.datum), pt.ciklus_studija, pt.akademska_godina, pt.datum from prijemni_termin as pt, akademska_godina as ag where pt.id=$termin and pt.akademska_godina=ag.id");
	if (mysql_num_rows($q10)<1) {
		niceerror("Nepostojeći termin prijemnog ispita");
		zamgerlog("nepostojeci termin prijemnog $termin", 3);
		return;
	}
	$ag = mysql_result($q10,0,0);
	$datum = date("d. m. Y.", mysql_result($q10,0,1));
	$ciklus = mysql_result($q10,0,2);

	$naslov3 = "";
	if ($ciklus>1) {
		$naslov3 = " na $ciklus. ciklus studija";
	}

	// Koji po redu termin?
	$q20 = myquery("select count(*)+1 from prijemni_termin where ciklus_studija=$ciklus and akademska_godina=".mysql_result($q10,0,3)." and datum='".mysql_result($q10,0,4)."' and id<$termin");
	$rednibroj = mysql_result($q20,0,0);
	if ($rednibroj==1) { 
		$naslov2 = "kvalifikacionog ispita"; 
	} else { 
		$naslov2 = "$rednibroj. termina kvalifikacionog ispita"; 
	}

	
	?>
	<h4>Univerzitet u Sarajevu<br />
	Elektrotehnički fakultet Sarajevo</h4>

	<h3 align="left">Preliminarna rang lista kandidata nakon <?=$naslov2?> održanog dana <?=$datum?> godine za upis kandidata<?=$naslov3?> u akademsku <?=$ag?> godinu</h3>
	<h2 align="left">Studij: <?=$naziv_studija?></h2>
	<br/>
	<?

	// Kriteriji za upis
	$quk = myquery ("SELECT donja_granica, gornja_granica, kandidati_strani, kandidati_sami_placaju, kandidati_kanton_placa, prijemni_max
	FROM upis_kriterij WHERE studij=$studij AND prijemni_termin=$termin");
	
	$bodovihard = floatval(mysql_result($quk, 0, 0));
	$bodovisoft = floatval(mysql_result($quk, 0, 1));
	$kandidatisd = intval(mysql_result($quk,0,2));
	$kandidatisp = intval(mysql_result($quk,0,3));
	$kandidatikp = intval(mysql_result($quk,0,4));
	$prijemnimax = floatval(mysql_result($quk,0,5));

	// Spisak svih kandidata se učitava u niz
	if ($ciklus==1)
		$qispis = myquery ("SELECT pp.broj_dosjea, CONCAT(o.prezime, ' ', o.ime) 'Prezime i ime', k.kratki_naziv, us.opci_uspjeh, o.kanton, us.kljucni_predmeti, us.dodatni_bodovi, pp.rezultat, us.opci_uspjeh+us.kljucni_predmeti+us.dodatni_bodovi+pp.rezultat ukupno
		FROM prijemni_prijava as pp, kanton as k, osoba as o, uspjeh_u_srednjoj as us
		WHERE pp.osoba=o.id AND pp.osoba=us.osoba AND pp.prijemni_termin=$termin AND pp.studij_prvi=$studij AND o.kanton=k.id
		ORDER BY ukupno DESC");
	else
		$qispis = myquery ("SELECT pp.broj_dosjea, CONCAT(o.prezime, ' ', o.ime) 'Prezime i ime', k.kratki_naziv, pcu.opci_uspjeh, o.kanton, 0, pcu.dodatni_bodovi, pp.rezultat, pcu.opci_uspjeh+pcu.dodatni_bodovi+pp.rezultat ukupno
		FROM prijemni_prijava as pp, kanton as k, osoba as o, prosliciklus_uspjeh as pcu
		WHERE pp.osoba=o.id AND pp.osoba=pcu.osoba AND pp.prijemni_termin=$termin AND pp.studij_prvi=$studij AND o.kanton=k.id
		ORDER BY ukupno DESC");
	
	$kandidati = array();
	while($rezultat = mysql_fetch_row($qispis)) {
		$id = $rezultat[0];
		$kandidati[$id] = array('prezime_ime'=>$rezultat[1], 'kanton'=>$rezultat[2], 'opci_uspjeh'=>$rezultat[3], 'kanton_id'=>$rezultat[4], 'kljucni_predmeti'=>$rezultat[5], 'dodatni_bodovi'=>$rezultat[6], 'prijemni_ispit'=>$rezultat[7], 'ukupno'=>$rezultat[8]);
	}

	// Zaglavlje tabele
	?>
	<table align="center" border="1" cellspacing="0" cellpadding="1" bordercolor="#000000">
		<tr>
			<td align="center" width="5%"><b>R.br.</b></td>
			<td align="left"><b>Prezime i ime</b></td>
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
		if ($i >= $kandidatikp) break;
		if($kandidat['prijemni_ispit'] >= $bodovisoft) {
			if ($i == 1) {
				?>
				<tr>
					<td colspan="8"><b>REDOVNI 
STUDIJ - Troškove studija snosi Kanton Sarajevo</b></td>
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
		if ($j >= $kandidatisp) break;
		if(($kandidat['prijemni_ispit'] >= $bodovisoft && $i >= $kandidatikp)) {
			if ($j == 1) {
				?>
				<tr>
					<td colspan="8"><b>REDOVNI 
STUDIJ - Troškove studija snose sami studenti</b></td>
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
		if ($j >= $kandidatisp) break;
		if(($kandidat['prijemni_ispit'] >= $bodovihard && $kandidat['prijemni_ispit'] <= $bodovisoft && $j<$kandidatisp)) {
			if ($j == 1) {
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
		if ($k >= $kandidatisd) break;
		if($kandidat['prijemni_ispit'] >= $bodovihard && $kandidat['kanton_id'] == 13)  {
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
				<td><font color="red">**</font></td>
			</tr>
			<?php
			unset($kandidati[$id]);
			$stranidrzavljani++;
			$k++;
		}
	}
	
	// Studenti upadaju na mjesta za strane drzavljane
	$mjestazasd=0;
	if($k <= $kandidatisd) {
		foreach($kandidati as $id => $kandidat) {
			if($kandidat['prijemni_ispit'] >= $bodovihard && $kandidat['kanton_id'] != 13) {
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
	}

	// Nisu se upisali
	$j = 1;
	$palo=0;
	$drugiodsjek=0;
	foreach ($kandidati as $id => $kandidat) {
		if ($j == 1) {
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
				<td align="center"><b><?=$kandidat['ukupno']?><? 
				if ($kandidat['prijemni_ispit']>=20) {
					?><td><font color="red">****</font></td><?
					$drugiodsjek++;
				}
				?></b></td>
			</tr>
		<?
		$j++;
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


}

}

?>
