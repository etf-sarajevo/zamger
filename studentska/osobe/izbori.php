<?php


// Podaci o izboru u zvanja

function studentska_osobe_izbori() {
	$osoba = int_param('osoba');
	
	if (param('subakcija') == "novi" && check_csrf_token()) {
		$zvanje = intval($_POST['_lv_column_zvanje']);
		$datum_izbora = mktime(0,0,0, intval($_POST['izbormonth']), intval($_POST['izborday']), intval($_POST['izboryear']));
		$datum_isteka = mktime(0,0,0, intval($_POST['istekmonth']), intval($_POST['istekday']), intval($_POST['istekyear']));
		// Ove vrijednosti moraju biti ovakve
		if ($datum_izbora == mktime(0,0,0,1,1,1990)) $datum_izbora=0;
		if ($datum_isteka == mktime(0,0,0,1,1,1990)) $datum_isteka=0;
		
		$oblast = intval($_POST['_lv_column_oblast']);
		if ($oblast == -1) $oblast="NULL";
		$podoblast = intval($_POST['_lv_column_podoblast']);
		if ($podoblast == -1) $podoblast="NULL";
		if ($_POST['dopunski']) $dopunski=1; else $dopunski=0;
		if ($_POST['druga_institucija']) $drugainst=1; else $drugainst=0;
		if ($_POST['neodredjeno'])
			$sqlisteka = "'2999-01-01'";
		else
			$sqlisteka = "FROM_UNIXTIME($datum_isteka)";
		
		$q3030 = db_query("insert into izbor set osoba=$osoba, zvanje=$zvanje, datum_izbora=FROM_UNIXTIME($datum_izbora), datum_isteka=$sqlisteka, oblast=$oblast, podoblast=$podoblast, dopunski=$dopunski, druga_institucija=$drugainst");
		zamgerlog("dodani podaci o izboru za u$osoba", 2);
		zamgerlog2("dodani podaci o izboru", $osoba);
		
		?>
		<script language="JavaScript">
            setTimeout(function() { location.href='?sta=studentska/osobe&akcija=izbori&osoba=<?=$osoba?>'; }, 1000);
		</script>
		<?
		return;
	}
	
	if (param('subakcija') == "izmjena" && check_csrf_token()) {
		$izvanje = intval($_POST['_lv_column_zvanje']);
		$idatum_izbora = mktime(0,0,0, intval($_POST['izbormonth']), intval($_POST['izborday']), intval($_POST['izboryear']));
		$idatum_isteka = mktime(0,0,0, intval($_POST['istekmonth']), intval($_POST['istekday']), intval($_POST['istekyear']));
		// Ove vrijednosti moraju biti ovakve
		if ($idatum_izbora == mktime(0,0,0,1,1,1990)) $idatum_izbora=0;
		if ($idatum_isteka == mktime(0,0,0,1,1,1990)) $idatum_isteka=0;
		
		$ioblast = intval($_POST['_lv_column_oblast']);
		if ($ioblast == -1) $ioblast="NULL";
		$ipodoblast = intval($_POST['_lv_column_podoblast']);
		if ($ipodoblast == -1) $ipodoblast="NULL";
		if ($_POST['dopunski']) $idopunski=1; else $idopunski=0;
		if ($_POST['druga_institucija']) $idrugainst=1; else $idrugainst=0;
		if ($_POST['neodredjeno'])
			$isqlisteka = "'2999-01-01'";
		else
			$isqlisteka = "FROM_UNIXTIME($idatum_isteka)";
		
		// Bice azurirano prilikom ispisa...
	}
	
	$broj_izbora = int_param('broj_izbora');
	$q3000 = db_query("select ime, prezime from osoba where id=$osoba");
	$imeprezime = db_result($q3000,0,0)." ".db_result($q3000,0,1);
	
	?>
	<h3>Izbor nastavnika u zvanja</h3>
	<p><a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=edit">Nazad na podatke o osobi <?=$imeprezime?></a></p>
	<p>&nbsp;</p>
	<?
	
	$t_zvanje=$t_datumiz=$t_datumis=$t_oblast=$t_podoblast=$t_dopunski=$t_neodredjeno=$t_drugainst=0;
	$ispis="";
	
	$q3010 = db_query("select zvanje, UNIX_TIMESTAMP(datum_izbora), UNIX_TIMESTAMP(datum_isteka), oblast, podoblast, dopunski, druga_institucija from izbor WHERE osoba=$osoba order by datum_isteka, datum_izbora");
	if (db_num_rows($q3010)==1 && $broj_izbora!=-1)
		$broj_izbora=1; // Ako postoji samo jedan izbor, editujemo ga; -1 znači ipak dodavanje novog
	for ($i=1; $i<=db_num_rows($q3010); $i++) {
		$zvanje=db_result($q3010,$i-1,0);
		$datumiz=db_result($q3010,$i-1,1);
		$datumis=db_result($q3010,$i-1,2);
		$oblast=db_result($q3010,$i-1,3);
		$podoblast=db_result($q3010,$i-1,4);
		$dopunski=db_result($q3010,$i-1,5);
		$drugainst=db_result($q3010,$i-1,6);
		
		$q3020 = db_query("select naziv from zvanje where id=$zvanje");
		$nzvanje = db_result($q3020,0,0);
		
		$ndatumiz = date("d. m. Y", $datumiz);
		if ($datumiz==0)
			$ndatumiz = "nepoznato";
		$ndatumis = date("d. m. Y", $datumis);
		if ($datumis==0) // UNIX_TIMESTAMP će vratiti 0 za '2999-01-01'
			$ndatumis = "neodređeno";
		
		if ($i==$broj_izbora) {
			$t_zvanje=$zvanje; $t_datumiz=$datumiz; $t_datumis=$datumis; $t_oblast=$oblast; $t_podoblast=$podoblast; $t_dopunski=$dopunski; $t_drugainst=$drugainst;
			if ($datumis==0) $t_neodredjeno=1; else $t_neodredjeno=0;
			if (param('subakcija') == "izmjena" && check_csrf_token()) {
				if (param('brisanje')) {
					$q3035 = db_query("DELETE FROM izbor WHERE osoba=$osoba and zvanje=$zvanje and UNIX_TIMESTAMP(datum_izbora)=$datumiz and UNIX_TIMESTAMP(datum_isteka)=$datumis and dopunski=$dopunski and druga_institucija=$drugainst");
					zamgerlog("obrisani podaci o izboru za u$osoba", 2);
					zamgerlog2("obrisani podaci o izboru", $osoba);
					nicemessage("Obrisani podaci o izboru");
					?>
					<p><a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=izbori">Nazad</a></p>
					<?php
					return;
				} else {
					$q3040 = db_query("update izbor set zvanje=$izvanje, datum_izbora=FROM_UNIXTIME($idatum_izbora), datum_isteka=$isqlisteka, oblast=$ioblast, podoblast=$ipodoblast, dopunski=$idopunski, druga_institucija=$idrugainst WHERE osoba=$osoba and zvanje=$zvanje and UNIX_TIMESTAMP(datum_izbora)=$datumiz and UNIX_TIMESTAMP(datum_isteka)=$datumis and dopunski=$dopunski and druga_institucija=$drugainst");
					zamgerlog("azurirani podaci o izboru za u$osoba", 2);
					zamgerlog2("azurirani podaci o izboru", $osoba);
				}
				$t_zvanje=$izvanje; $t_datumiz=$idatum_izbora; $t_datumis=$idatum_isteka; $t_oblast=$ioblast; $t_podoblast=$ipodoblast; $t_dopunski=$idopunski; $t_drugainst=$idrugainst;
				$q3020 = db_query("select naziv from zvanje where id=$izvanje");
				$nzvanje = db_result($q3020,0,0);
				
				$ndatumiz = date("d. m. Y", $t_datumiz);
				if ($t_datumiz==0)
					$ndatumiz = "nepoznato";
				$ndatumis = date("d. m. Y", $t_datumis);
				if ($t_datumis==0)
					$ndatumis = "neodređeno";
			}
			$ispis .= "<br/>* $nzvanje ($ndatumiz - $ndatumis)\n";
			if ($t_oblast == "NULL") $t_oblast = -1;
			if ($t_podoblast == "NULL") $t_podoblast = -1;
		} else {
			$ispis .= "<br/>* <a href=\"?sta=studentska/osobe&osoba=$osoba&akcija=izbori&broj_izbora=$i\">$nzvanje ($ndatumiz - $ndatumis)</a>\n";
		}
	}
	if (db_num_rows($q3010)>0) {
		?>
		<p><b>Historija izbora:</b>
			<?=$ispis?></p>
		<?
	}
	
	if ($broj_izbora<1) {
		?>
		<p><b>Unos novog izbora:</b></p>
		<?=genform("POST")?>
		<input type="hidden" name="subakcija" value="novi">
		<?
	} else {
		?>
		<p><b>Izmjena podataka o izboru:</b></p>
		<?=genform("POST")?>
		<input type="hidden" name="subakcija" value="izmjena">
		<?
	}
	?>
	<table border="0"><tr>
			<td>Zvanje:</td>
			<td><?=db_dropdown("zvanje", $t_zvanje)?></td>
		</tr><tr>
			<td>Datum izbora:</td>
			<td><?=datectrl(date("d",$t_datumiz), date("m",$t_datumiz), date("Y",$t_datumiz), "izbor")?></td>
		</tr><tr>
			<td valign="top">Datum isteka:</td>
			<td><input type="checkbox" name="neodredjeno" <? if ($t_neodredjeno==1) print "CHECKED"; ?>> Neodređeno<br/>
				<?=datectrl(date("d",$t_datumis), date("m",$t_datumis), date("Y",$t_datumis), "istek")?></td>
		</tr><tr>
			<td>Oblast:</td>
			<td><?=db_dropdown("oblast", $t_oblast, "--Nepoznato--")?></td>
		</tr><tr>
			<td>Podoblast:</td>
			<td><?=db_dropdown("podoblast", $t_podoblast, "--Nepoznato--")?></td>
		</tr><tr>
			<td colspan="2"><input type="checkbox" name="dopunski" <? if ($t_dopunski==1) print "CHECKED"; ?>> Dopunski radni odnos</td>
		</tr><tr>
			<td colspan="2"><input type="checkbox" name="druga_institucija" <? if ($t_drugainst==1) print "CHECKED"; ?>> Biran/a na drugoj VŠO</td>
		</tr>
	</table>
	<input type="submit" value=" Pošalji "> <input type="submit" name="brisanje" value=" Obriši ">
	</form>
	<?
	if ($broj_izbora>0) {
		?>
		<p><a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=izbori&broj_izbora=-1">Kliknite ovdje za unos novog izbora</a></p>
		<?
	}
}

