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
