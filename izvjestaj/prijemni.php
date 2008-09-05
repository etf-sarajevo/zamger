<?

// IZVJESTAJ/PRIJEMNI - rang liste kandidata sa prijemnog ispita

// v3.9.1.0 (2008/07/04) + Kod prebacen iz studentska/prijemni da bi se rasteretio modul


function izvjestaj_prijemni() {


// Spisak kandidata za prijemni
// Parametar IZ definiše da li su strani, domaci ili svi

if ($_REQUEST['akcija']=="kandidati") {

	if ($_REQUEST['iz']=='bih') {
		$uslov="WHERE kanton!=13"; $naslov="(BiH)";
	}
	else if ($_REQUEST['iz']=='strani') {
		$uslov="WHERE kanton=13"; $naslov="(Strani državljani)";
	}

	if ($_REQUEST['termin']=="1") {
		if (!$uslov) $uslov="WHERE "; else $uslov.=" AND ";
		$uslov .= "prijavio_drugi=0 OR prijavio_drugi=2";
		$naslov = "kvalifikacioni ispit $naslov 7. jula";
	} else if ($_REQUEST['termin']=="2") {
		if (!$uslov) $uslov="WHERE "; else $uslov.=" AND ";
		$uslov .= "prijavio_drugi=1 OR prijavio_drugi=2";
		$naslov = "drugi termin kvalifikacionog ispita $naslov 1. septembra";
	}

	if ($_REQUEST['sort']=="abecedno") {
		$orderby="ORDER BY prezime,ime";
	} else {
		$orderby="ORDER BY ukupno DESC";
	}

	if ($_REQUEST['sakrij_bodove']) $sakrij_bodove = true;


	?>
	<p>Univerzitet u Sarajevu<br />
	Elektrotehnički fakultet Sarajevo</p>

	<h3>Spisak kandidata za <?=$naslov?> 2008. godine za upis kandidata u akademsku 2008/2009 godinu</h3>
	<br /><?

	$sqlSelect="SELECT id, ime, prezime, kanton, opci_uspjeh, kljucni_predmeti, dodatni_bodovi, opci_uspjeh+kljucni_predmeti+dodatni_bodovi AS ukupno FROM prijemni $uslov $orderby";
	
	$q = myquery($sqlSelect);
	
	?>
	<table width="" align="center" border="1" cellpadding="1" cellspacing="1" bordercolor="#000000">
	<tr>
	<td width="10"><b>R.br.</b></td>
	<td><b>Prezime i ime</b></td>
	<? if (!$sakrij_bodove) { ?>
	<td width="100"><b>Opći uspjeh</b></td>
	<td width="110"><b>Ključni predmeti</b></td>
	<td width="105"><b>Dodatni bodovi</b></td>
	<td width="105"><b>Ukupno bodova</b></td>
	<? } ?>
	</tr>
	<?
	$brojac = 1;
	while ($kandidat=mysql_fetch_array($q))
	{
		$array = array ($kandidat["prezime"], $kandidat["ime"]);
		$prezimeIme = join (" ", $array);
		?>
		<tr>
		<td align="center"><?=$brojac?></td>
		<td><?=$prezimeIme;?></td>
		<? if (!$sakrij_bodove) { ?>
		<td align="center"><?=$kandidat["opci_uspjeh"];?></td>
		<td align="center"><?=$kandidat["kljucni_predmeti"];?></td>
		<td align="center"><?=$kandidat["dodatni_bodovi"];?></td>
		<td align="center"><?=($kandidat["opci_uspjeh"]+$kandidat["kljucni_predmeti"]+$kandidat["dodatni_bodovi"]);?></td>
		<? } ?>
		</tr>
		<?
		$brojac++;
	}
	?>
	</table>


	<!-- Potpis dekana -->
	<table border="0" width="100%">
		<tr>
			<td>&nbsp;</td>
			<td align="right" width="300"><br /><br /><br /><br /><hr></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td align="center" width="300">Predsjednik Komisije<br />R. prof. dr Kemo Sokolija</td>
		</tr>
	</table>
	<?



// Konačne rang-liste

} else {


	$studij = intval($_REQUEST['_lv_column_studij']);

	$q10 = myquery("SELECT naziv FROM studij WHERE id=$studij");
	$naslov = mysql_result($q10,0,0);
	
	?>
	<p>Univerzitet u Sarajevu<br />
	Elektrotehnički fakultet Sarajevo</p>

	<h3 align="left">Preliminarna rang lista kandidata nakon drugog termina kvalifikacionog ispita održanog dana 1. septembra 2008. godine za upis kandidata u akademsku 2008/2009 godinu</h3>
	<h2 align="left">Studij: <?=$naslov?></h2>
	<br/>
	<?

	// Kriteriji za upis
	$quk = myquery ("SELECT donja_granica, gornja_granica, kandidati_strani, kandidati_sami_placaju, kandidati_kanton_placa, prijemni_max
	FROM upis_kriterij WHERE studij=$studij");
	
	$bodovihard = floatval(mysql_result($quk, 0, 0));
	$bodovisoft = floatval(mysql_result($quk, 0, 1));
	$kandidatisd = intval(mysql_result($quk,0,2));
	$kandidatisp = intval(mysql_result($quk,0,3));
	$kandidatikp = intval(mysql_result($quk,0,4));
	$prijemnimax = floatval(mysql_result($quk,0,5));

	$prijemni_polje = "prijemni_ispit";
	if ($_REQUEST['termin']=="1") {
		$where_dodaj = "AND (prijavio_drugi=0 OR prijavio_drugi=2)";
	} else if ($_REQUEST['termin']=="2") {
		$where_dodaj = "AND (prijavio_drugi=1 OR prijavio_drugi=2)";
		$prijemni_polje .= "_dva";
	}
	
	// Spisak svih kandidata se učitava u niz
	$qispis = myquery ("SELECT p.id, CONCAT(p.prezime, ' ', p.ime) 'Prezime i ime', k.kratki_naziv, p.opci_uspjeh, p.kanton, p.kljucni_predmeti, p.dodatni_bodovi, p.$prijemni_polje, p.opci_uspjeh+p.kljucni_predmeti+p.dodatni_bodovi+p.$prijemni_polje ukupno
	FROM prijemni p, kanton k, studij s
	WHERE  p.kanton = k.id AND p.odsjek_prvi=s.kratkinaziv AND s.id=$studij $where_dodaj
	ORDER BY ukupno DESC");
	
	$kandidati = array();
	while($rezultat = mysql_fetch_row($qispis)) {
		$id = $rezultat[0];
		$kandidati[$id] = array('prezime_ime'=>$rezultat[1], 'kanton'=>$rezultat[2], 'opci_uspjeh'=>$rezultat[3], 'kanton_id'=>$rezultat[4], 'kljucni_predmeti'=>$rezultat[5], 'dodatni_bodovi'=>$rezultat[6], 'prijemni_ispit'=>$rezultat[7], 'ukupno'=>$rezultat[8]);
	}

	// Zaglavlje tabele
	?>
	<table align="center" border="1" cellspacing="1" cellpadding="1" bordercolor="#000000">
		<tr>
			<td align="center" width="5%"><b>R.br.</b></td>
			<td align="left"><b>Prezime i ime</b></td>
			<td align="center" width="6%"><b>Kanton</b></td>
			<td align="center" width="10%"><b>Opći uspjeh</b></td>
			<td align="center" width="10%"><b>Ključni predmeti</b></td>
			<td align="center" width="10%"><b>Dodatni bodovi</b></td>
			<td align="center" width="10%"><b>Rezultat ispita</b></td>
			<td align="center" width="10%"><b>Ukupno</b></td>
		</tr>
		<?php


	// Troskove studija snosi Kanton
	$i = 1;
	foreach($kandidati as $id => $kandidat) {
		if($kandidat['prijemni_ispit'] >= $bodovisoft) {
			if ($i == 1) {
				?>
				<tr>
					<td colspan="8"><b>REDOVNI 
STUDIJ - Troškove studija snose sami studenti</b></td>
				</tr>
				<?
			}
			?>
			<tr>
				<td align="center"><?=$i?></td>
				<td><?=$kandidat['prezime_ime']?></td>
				<td align="center"><?=$kandidat['kanton']?></td>
				<td align="center"><?=$kandidat['opci_uspjeh']?></td>
				<td align="center"><?=$kandidat['kljucni_predmeti']?></td>
				<td align="center"><?=$kandidat['dodatni_bodovi']?></td>
				<td align="center"><?=$kandidat['prijemni_ispit']?></td>
				<td align="center"><b><?=$kandidat['ukupno']?></b></td>
			</tr>
			<?php
			unset($kandidati[$id]);
			if ($i++ >= $kandidatikp) break;
		}
	}
	
	// Troskove studija snose studenti (polozili prijemni)
	$j = 1;
	foreach($kandidati as $id => $kandidat) {
		if(($kandidat['prijemni_ispit'] >= $bodovisoft && $i >= $kandidatikp)) {
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
				<td align="center"><?=$kandidat['kljucni_predmeti']?></td>
				<td align="center"><?=$kandidat['dodatni_bodovi']?></td>
				<td align="center"><?=$kandidat['prijemni_ispit']?></td>
				<td align="center"><b><?=$kandidat['ukupno']?></b></td>
			</tr>
			<?
			unset($kandidati[$id]);
			if ($j++ >= $kandidatisp) break;
		}
	}
	
	// Troskove studija snose studenti (nisu polozili ali su iznad soft limita i ima mjesta)
	foreach($kandidati as $id => $kandidat) {
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
				<td align="center"><?=$kandidat['kljucni_predmeti']?></td>
				<td align="center"><?=$kandidat['dodatni_bodovi']?></td>
				<td align="center"><?=$kandidat['prijemni_ispit']?></td>
				<td align="center"><b><?=$kandidat['ukupno']?></b></td>
				<td><font color="red">**</font></td>
			</tr>
			<?
			unset($kandidati[$id]);
			if ($j++ >= $kandidatisp) break;
		}
	}

	/*	
	// Strani drzavljani
	$k = 1;
	foreach($kandidati as $id => $kandidat){
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
				<td align="center"><?=$kandidat['kljucni_predmeti']?></td>
				<td align="center"><?=$kandidat['dodatni_bodovi']?></td>
				<td align="center"><?=$kandidat['prijemni_ispit']?></td>
				<td align="center"><b><?=$kandidat['ukupno']?></b></td>
				<td><font color="red">****</font></td>
			</tr>
			<?php
			unset($kandidati[$id]);
			if ($k++ >= $kandidatisd) break;
		}
	}
	
	// Studenti upadaju na mjesta za strane drzavljane
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
					<td align="center"><?=$kandidat['kljucni_predmeti']?></td>
					<td align="center"><?=$kandidat['dodatni_bodovi']?></td>
					<td align="center"><?=$kandidat['prijemni_ispit']?></td>
					<td align="center"><b><?=$kandidat['ukupno']?></b></td>
					<td><font color="red">***</font></td>
				</tr>
				<?
				unset($kandidati[$id]);
				if ($k++ >= $kandidatisd) break;
			}
		}
	}
*/
	// Nisu se upisali
	$j = 1;
	$palo=0;
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
				<td><?=$kandidat['prezime_ime']?> *</td>
				<td align="center"><?=$kandidat['kanton']?></td>
				<td align="center"><?=$kandidat['opci_uspjeh']?></td>
				<td align="center"><?=$kandidat['kljucni_predmeti']?></td>
				<td align="center"><?=$kandidat['dodatni_bodovi']?></td>
				<td align="center"><?=$kandidat['prijemni_ispit']?></td>
				<td align="center"><b><?=$kandidat['ukupno']?></b></td>
			</tr>
		<?
		$j++;
	}

	// Legenda

	?>
	</table>
	</br>
	<?
	if ($palo==1) {
		?>
		<p>* nisu dostigli potrebni broj bodova te se pozivaju u 
petak 5. 9. 2008. u 8 sati ujutro na dodatni razgovor sa Komisijom.</p>
		<?
	}


	// Potpis dekana
	?>
	<table border="0" width="100%">
		<tr>
			<td>&nbsp;</td>
			<td align="right" width="300"><br /><br /><br /><br /><hr></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td align="center" width="300">Predsjednik Komisije<br />R. prof. dr Kemo Sokolija</td>
		</tr>
	</table>
	<?

}

}

?>
