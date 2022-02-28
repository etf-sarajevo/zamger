<?php


// Analitička kartica studenta

function studentska_osobe_kartica() {
	global $conf_url_daj_karticu;
	
	require_once("lib/ws.php"); // Web service za parsiranje XMLa
	
	$osoba = int_param('osoba');
	
	$q2000 = db_query("select ime, prezime, jmbg from osoba where id=$osoba");
	if (db_num_rows($q2000)<1) {
		niceerror("Nepoznata osoba $osoba");
		return;
	}
	$ime = db_result($q2000,0,0);
	$prezime = db_result($q2000,0,1);
	$jmbg = db_result($q2000,0,2);
	
	?>
	<h2><?=$ime?> <?=$prezime?> - analitička kartica studenta</h2>
	<?
	
	$kartice = parsiraj_kartice(xml_request($conf_url_daj_karticu, array("jmbg" => $jmbg), "POST"));
	$saldo = 0;
	if ($kartice === FALSE || count($kartice) == 0) niceerror("Nema podataka o uplatama");
	else {
		?>
		<table><tr><th>R. br.</th><th>Datum</th><th>Vrsta zaduženja</th><th>Zaduženje</th><th>Razduženje</th></tr>
			<?
			$rbr=0;
			foreach($kartice as $kartica) {
				$rbr++;
				?>
				<tr><td><?=$rbr?></td><td><?=$kartica['datum']?></td><td><?=$kartica['vrsta_zaduzenja']?></td>
					<td><?=number_format($kartica['zaduzenje'], 2, ",", "")?> KM</td>
					<td><?=number_format($kartica['razduzenje'], 2, ",", "")?> KM</td>
				</tr>
				<?
			}
			?>
		</table>
		<?
	}
}


function studentska_osobe_applet_kartica($osoba,$jmbg) {
	global $conf_url_daj_karticu;
	
	require_once("lib/ws.php"); // Web service za parsiranje XMLa
	
	$kartice = parsiraj_kartice(xml_request($conf_url_daj_karticu, array("jmbg" => $jmbg), "POST"));
	$saldo = 0;
	if ($kartice === FALSE || count($kartice) == 0) {
		?>
		<p><font color="red">Nema podataka o uplatama</font></p>
		<?
	} else {
		foreach($kartice as $kartica) $saldo += $kartica['razduzenje'] - $kartica['zaduzenje'];
		if ($saldo>=0) $boja="green"; else $boja="red";
		?>
		<p><font color="<?=$boja?>">Student na računu ima: <?=number_format($saldo, 2, ",", "")?> KM</font> - <a href="?sta=studentska/osobe&amp;osoba=<?=$osoba?>&amp;akcija=kartica">Analitička kartica studenta</a></p>
		<?
	}
}


function parsiraj_kartice($xml_data) {
	$result = array();
	if ($xml_data === FALSE) return FALSE;
	
	$u_kartici = false;
	$tekuca_kartica = array();
	foreach ($xml_data as $node) {
		if ($node['tag'] == "KARTICA") {
			if ($node['type'] == "open") {
				if ($u_kartici) $result[] = $tekuca_kartica;
				$u_kartici=true;
				$tekuca_kartica = array();
			}
			if ($node['type'] == "closed") {
				$u_kartici=false;
				$result[] = $tekuca_kartica;
			}
			continue;
		}
		if (!$u_kartici) continue;
		if ($node['tag'] == "DATUM") $tekuca_kartica['datum'] = $node['value'];
		if ($node['tag'] == "VRSTAZADUZENJA") $tekuca_kartica['vrsta_zaduzenja'] = $node['value'];
		if ($node['tag'] == "ZADUZENJE") $tekuca_kartica['zaduzenje'] = bhfloat($node['value']);
		if ($node['tag'] == "RAZDUZENJE") $tekuca_kartica['razduzenje'] = bhfloat($node['value']);
	}
	if ($u_kartici) $result[] = $tekuca_kartica;
	
	return $result;
}