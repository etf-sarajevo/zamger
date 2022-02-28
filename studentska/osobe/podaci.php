<?php


// Izmjena ličnih podataka osobe

function studentska_osobe_podaci() {
	global $conf_files_path;
	
	$osoba = int_param('osoba');
	
	if ($_POST['subakcija']=="potvrda" && check_csrf_token()) {
		
		$ime = db_escape($_REQUEST['ime']);
		$prezime = db_escape($_REQUEST['prezime']);
		$spol = $_REQUEST['spol']; if ($spol!="M" && $spol!="Z") $spol="";
		$jmbg = db_escape($_REQUEST['jmbg']);
		$nacionalnost = intval($_REQUEST['nacionalnost']); if ($nacionalnost==0) $nacionalnost = "NULL";
		$brindexa = db_escape($_REQUEST['brindexa']);
		
		$imeoca = db_escape($_REQUEST['imeoca']);
		$prezimeoca = db_escape($_REQUEST['prezimeoca']);
		$imemajke = db_escape($_REQUEST['imemajke']);
		$prezimemajke = db_escape($_REQUEST['prezimemajke']);
		
		$mjesto_rodjenja = db_escape($_REQUEST['mjesto_rodjenja']);
		$opcina_rodjenja = intval($_REQUEST['opcina_rodjenja']);
		$drzava_rodjenja = intval($_REQUEST['drzava_rodjenja']);
		$drzavljanstvo = intval($_REQUEST['drzavljanstvo']); if ($drzavljanstvo==0) $drzavljanstvo = "NULL";
		
		// Posebne kategorije
		$q391 = db_query("SELECT id FROM posebne_kategorije");
		while ($r391 = db_fetch_row($q391)) {
			$q393 = db_query("DELETE FROM osoba_posebne_kategorije WHERE osoba=$osoba AND posebne_kategorije=$r391[0]");
			if ($_REQUEST['posebne_kategorije_'.$r391[0]])
				$q392 = db_query("INSERT INTO osoba_posebne_kategorije SET osoba=$osoba, posebne_kategorije=$r391[0]");
		}
		
		$adresa = db_escape($_REQUEST['adresa']);
		$adresa_mjesto = db_escape($_REQUEST['adresa_mjesto']);
		$kanton = intval($_REQUEST['_lv_column_kanton']); if ($kanton==-1) $kanton = "NULL";
		$telefon = db_escape($_REQUEST['telefon']);
		$email = db_escape($_REQUEST['email']);
		
		$strucni_stepen = intval($_REQUEST['_lv_column_strucni_stepen']); if ($strucni_stepen==-1) $strucni_stepen = "NULL";
		$naucni_stepen = intval($_REQUEST['_lv_column_naucni_stepen']); if ($naucni_stepen==-1) $naucni_stepen = "NULL";
		
		// Sredjujem datum
		$datum_rodjenja = "NULL";
		if (preg_match("/(\d+).*?(\d+).*?(\d+)/", $_REQUEST['datum_rodjenja'], $matches)) {
			$dan=$matches[1]; $mjesec=$matches[2]; $godina=$matches[3];
			if ($godina<100)
				if ($godina<50) $godina+=2000; else $godina+=1900;
			if ($godina<1000)
				if ($godina<900) $godina+=2000; else $godina+=1000;
			$datum_rodjenja="'$godina-$mjesec-$dan'";
		}
		
		// Mjesto rođenja
		$mjrid="NULL";
		if ($mjesto_rodjenja != "") {
			$q1 = db_query("select id from mjesto where naziv='$mjesto_rodjenja' and opcina=$opcina_rodjenja and drzava=$drzava_rodjenja");
			if (db_num_rows($q1)<1) {
				$q2 = db_query("select id from mjesto where naziv='$mjesto_rodjenja'");
				if (db_num_rows($q2)<1) {
					nicemessage("Dodajem novo mjesto '$mjesto_rodjenja'");
					$q2 = db_query("insert into mjesto set naziv='$mjesto_rodjenja', opcina=$opcina_rodjenja, drzava=$drzava_rodjenja");
					$q1 = db_query("select id from mjesto where naziv='$mjesto_rodjenja'");
				} else {
					nicemessage("Promjena općine/države za mjesto '$mjesto_rodjenja'");
					$q2 = db_query("insert into mjesto set naziv='$mjesto_rodjenja', opcina=$opcina_rodjenja, drzava=$drzava_rodjenja");
					$q1 = db_query("select id from mjesto where naziv='$mjesto_rodjenja' and opcina=$opcina_rodjenja and drzava=$drzava_rodjenja");
				}
			}
			$mjrid = db_result($q1,0,0);
		}
		
		// Mjesto adresa
		$admid="NULL";
		if ($adresa_mjesto != "") {
			$q3 = db_query("select id from mjesto where naziv='$adresa_mjesto'");
			if (db_num_rows($q3)<1) {
				$q4 = db_query("insert into mjesto set naziv='$adresa_mjesto', opcina=$opcina_rodjenja, drzava=1");
				$q3 = db_query("select id from mjesto where naziv='$adresa_mjesto'");
			}
			$admid = db_result($q3,0,0);
		}
		
		$q395 = db_query("update osoba set ime='$ime', prezime='$prezime', imeoca='$imeoca', prezimeoca='$prezimeoca', imemajke='$imemajke', prezimemajke='$prezimemajke', spol='$spol', brindexa='$brindexa', datum_rodjenja=$datum_rodjenja, mjesto_rodjenja=$mjrid, nacionalnost=$nacionalnost, drzavljanstvo=$drzavljanstvo, jmbg='$jmbg', adresa='$adresa', adresa_mjesto=$admid, telefon='$telefon', kanton=$kanton, strucni_stepen=$strucni_stepen, naucni_stepen=$naucni_stepen where id=$osoba");
		
		zamgerlog("promijenjeni licni podaci korisnika u$osoba",4); // nivo 4 - audit
		zamgerlog2("promijenjeni licni podaci korisnika", $osoba);
		?>
		<script language="JavaScript">
            location.href='?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=edit';
		</script>
		<?
		return;
	}
	
	
	// Postavljanje ili promjena slike
	
	if ($_POST['subakcija']=="postavisliku" && check_csrf_token()) {
		$slika = $_FILES['slika']['tmp_name'];
		if ($slika && (file_exists($slika))) {
			// Provjeravamo ispravnost slike
			$podaci = getimagesize($slika);
			$koef = $podaci[0]/$podaci[1];
			if ($koef < 0.5 || $koef > 2) {
				niceerror("Omjer širine i visine slike nije povoljan.");
				print "<p>Slika bi trebala biti uobičajenog formata slike za lične dokumente. Ova je formata $podaci[0]x$podaci[1].</p>\n";
				return;
			}
			
			if ($podaci[2] != IMAGETYPE_GIF && $podaci[2] != IMAGETYPE_JPEG && $podaci[2] != IMAGETYPE_PNG && $podaci[2] != IMAGETYPE_TIFF_II) {
				niceerror("Nepoznat tip slike.");
				print "<p>Podržane su samo slike tipa GIF, JPEG ili PNG.</p>";
				return;
			}
			
			// Brisemo evt. postojecu sliku
			$q498 = db_query("select slika from osoba where id=$osoba");
			if (db_result($q498,0,0)!="")
				unlink ("$conf_files_path/slike/".db_result($q498,0,0));
			
			// Kopiramo novu sliku
			$novavisina = 150;
			$novasirina = $novavisina * $koef;
			$filename = "$conf_files_path/slike/$osoba";
			if (!file_exists("$conf_files_path/slike"))
				mkdir ("$conf_files_path/slike", 0777, true);
			
			$dest = imagecreatetruecolor($novasirina, $novavisina);
			switch ($podaci[2]) {
				case IMAGETYPE_GIF:
					$source = imagecreatefromgif($slika);
					imagecopyresampled($dest, $source, 0, 0, 0, 0, $novasirina, $novavisina, $podaci[0], $podaci[1]);
					imagegif($dest, $filename.".gif");
					$slikabaza = "$osoba.gif";
					break;
				case IMAGETYPE_JPEG:
					$source = imagecreatefromjpeg($slika);
					imagecopyresampled($dest, $source, 0, 0, 0, 0, $novasirina, $novavisina, $podaci[0], $podaci[1]);
					imagejpeg($dest, $filename.".jpg");
					$slikabaza = "$osoba.jpg";
					break;
				case IMAGETYPE_PNG:
					$source = imagecreatefrompng($slika);
					imagecopyresampled($dest, $source, 0, 0, 0, 0, $novasirina, $novavisina, $podaci[0], $podaci[1]);
					imagepng($dest, $filename.".png");
					$slikabaza = "$osoba.png";
					break;
				case IMAGETYPE_TIFF_II:
					nicemessage("Nije moguća promjena dimenzija slike tipa TIFF... Ostavljam zadate dimenzije.");
					rename ($slika, $filename.".tiff");
					$slikabaza = "$osoba.tiff";
					break;
			}
			
			$q310 = db_query("update osoba set slika='$slikabaza' where id=$osoba");
			
			zamgerlog("postavljena slika za korisnika u$osoba", 2);
			zamgerlog2("postavljena slika za korisnika", $osoba);
			?>
			<script language="JavaScript">
                location.href='?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=edit';
			</script>
			<?
			return;
		} else {
			nicemessage("Greška pri slanju slike");
		}
	}
	
	
	// Brisanje slike
	
	if ($_POST['subakcija']=="obrisisliku" && check_csrf_token()) {
		// Brisemo evt. postojecu sliku
		$q496 = db_query("select slika from osoba where id=$osoba");
		if (db_result($q496,0,0)!="")
			unlink ("$conf_files_path/slike/".db_result($q496,0,0));
		
		$q497 = db_query("update osoba set slika='' where id=$osoba");
		
		zamgerlog("obrisana slika za korisnika u$osoba", 2);
		zamgerlog2("obrisana slika za korisnika", $osoba);
		?>
		<script language="JavaScript">
            location.href='?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=edit';
		</script>
		<?
		return;
	}
	
	
	// Mailovi
	
	if ($_GET['subakcija']=="obrisimail") {
		$mailid = intval($_GET['mailid']);
		$q497a = db_query("select adresa from email where id=$mailid and osoba=$osoba");
		if (db_num_rows($q497a)<1) {
			niceerror("Greška");
			return;
		}
		$staraadresa = db_result($q497a,0,0);
		
		$q498 = db_query("delete from email where osoba=$osoba and id=$mailid");
		
		zamgerlog("obrisana email adresa za u$osoba", 2);
		zamgerlog2("email adresa obrisana", $osoba, $mailid, 0, $staraadresa);
		nicemessage("Obrisana email adresa $staraadresa");
	}
	
	if ($_GET['subakcija']=="izmijenimail") {
		$mailid = intval($_GET['mailid']);
		$adresa = db_escape($_GET['adresa']);
		$q497a = db_query("select adresa from email where id=$mailid and osoba=$osoba");
		if (db_num_rows($q497a)<1) {
			niceerror("Greška");
			return;
		}
		$staraadresa = db_result($q497a,0,0);
		
		$q498 = db_query("update email set adresa='$adresa' where osoba=$osoba and id=$mailid");
		
		zamgerlog("promijenjena email adresa za u$osoba", 2);
		zamgerlog2("email adresa promijenjena", $osoba, $mailid, 0, "$staraadresa -> $adresa");
		nicemessage("Promijenjena email adresa $staraadresa u $adresa");
	}
	
	if ($_GET['subakcija']=="dodajmail") {
		$adresa = db_escape($_GET['adresa']);
		$q498 = db_query("insert into email set adresa='$adresa', osoba=$osoba, sistemska=0");
		
		zamgerlog("dodana email adresa za u$osoba", 2);
		zamgerlog2("email adresa dodana", $osoba, intval(db_insert_id()), 0, "$adresa");
		nicemessage("Dodana email adresa $adresa");
	}
	
	
	// Prikaz podataka
	
	$q400 = db_query("select ime, prezime, imeoca, prezimeoca, imemajke, prezimemajke, spol, 1, brindexa, UNIX_TIMESTAMP(datum_rodjenja), mjesto_rodjenja, jmbg, nacionalnost, drzavljanstvo, adresa, adresa_mjesto, telefon, kanton, strucni_stepen, naucni_stepen, boracke_kategorije, slika from osoba where id=$osoba");
	if (!($r400 = db_fetch_row($q400))) {
		zamgerlog("nepostojeca osoba u$osoba",3);
		zamgerlog2("nepostojeca osoba", $osoba);
		niceerror("Nepostojeća osoba!");
		return;
	}
	$ime = db_result($q400,0,0);
	$prezime = db_result($q400,0,1);
	$muski=$zenski=$boracke_kategorije="";
	if (db_result($q400,0,6)=="M") $muski=" CHECKED";
	if (db_result($q400,0,6)=="Z") $zenski=" CHECKED";
	if (db_result($q400,0,20) == 1) $boracke_kategorije = " CHECKED";
	
	
	// Spisak gradova
	$q410 = db_query("select id,naziv,opcina,drzava from mjesto order by naziv");
	$gradovir="<option></option>";
	$gradovia="<option></option>";
	$gradovilist = array();
	while ($r410 = db_fetch_row($q410)) {
		if ($r410[0]==db_result($q400,0,10)) {
			$mjestorvalue = $r410[1];
			$eopcinarodjenja = $r410[2];
			$edrzavarodjenja = $r410[3];
		}
		if ($r410[0]==db_result($q400,0,15)) {
			$mjestoavalue = $r410[1];
		}
		$gradovilist[] = $r410[1];
	}
	
	
	// Spisak država
	
	$q257 = db_query("select id, naziv from drzava order by naziv");
	$drzaverodjr="<option></option>";
	$drzavljanstvor="<option></option>";
	while ($r257 = db_fetch_row($q257)) {
		$drzaverodjr .= "<option value=\"$r257[0]\"";
		if ($r257[0]==$edrzavarodjenja) { $drzaverodjr  .= " SELECTED"; }
		$drzaverodjr .= ">$r257[1]</option>\n";
		$drzavljanstvor .= "<option value=\"$r257[0]\"";
		if ($r257[0]==db_result($q400,0,13)) { $drzavljanstvor .= " SELECTED"; }
		$drzavljanstvor .= ">$r257[1]</option>\n";
	}
	
	// Spisak nacionalnosti
	
	$q258 = db_query("select id, naziv from nacionalnost order by naziv");
	$nacionalnostr="<option></option>";
	while ($r258 = db_fetch_row($q258)) {
		$nacionalnostr .= "<option value=\"$r258[0]\"";
		if ($r258[0]==db_result($q400,0,12)) { $nacionalnostr .= " SELECTED"; }
		$nacionalnostr .= ">$r258[1]</option>\n";
	}
	
	// Spisak opičina
	
	$q259 = db_query("select id, naziv from opcina order by naziv");
	$opcinar="";
	while ($r259 = db_fetch_row($q259)) {
		$opcinar .= "<option value=\"$r259[0]\"";
		if ($r259[0]==$eopcinarodjenja) { $opcinar .= " SELECTED"; }
		$opcinar .= ">$r259[1]</option>\n";
	}
	
	// Spisak mailova
	
	$q260 = db_query("select id, adresa from email where osoba=$osoba");
	$email_adrese = "";
	while ($r260 = db_fetch_row($q260)) {
		$email_adrese .= "<input type=\"text\" class=\"default\" name=\"email\" id=\"email$r260[0]\" value=\"$r260[1]\"> <input type=\"button\" class=\"default\" value=\"Izmijeni\" onclick=\"javascript:location.href='?sta=studentska/osobe&osoba=$osoba&akcija=podaci&subakcija=izmijenimail&mailid=$r260[0]&adresa='+document.getElementById('email$r260[0]').value;\"> <input type=\"button\" class=\"default\" value=\"Obriši\" onclick=\"javascript:location.href='?sta=studentska/osobe&osoba=$osoba&akcija=podaci&subakcija=obrisimail&mailid=$r260[0]';\"><br>\n";
	}
	
	// Posebne kategorije
	
	$q262 = db_query("SELECT id, naziv FROM posebne_kategorije");
	$posebne_ispis = "";
	while ($r262 = db_fetch_row($q262)) {
		$q264 = db_query("SELECT COUNT(*) FROM osoba_posebne_kategorije WHERE osoba=$osoba AND posebne_kategorije=$r262[0]");
		if (db_result($q264,0,0) > 0)
			$dodaj = "CHECKED";
		else
			$dodaj = "";
		$posebne_ispis .= "<input type=\"checkbox\" name=\"posebne_kategorije_$r262[0]\" $dodaj>$r262[1]<br>";
	}
	
	
	?>
	
	<script type="text/javascript" src="static/js/mycombobox.js"></script>
	<h2><?=$ime?> <?=$prezime?> - izmjena ličnih podataka</h2>
	<p>ID: <b><?=$osoba?></b></p>
	<?
	if (db_result($q400,0,21)=="") {
		print genform("POST", "\"  enctype=\"multipart/form-data");
		?>
		<input type="hidden" name="subakcija" value="postavisliku">
		<p>Dodaj sliku:<br /><input type="file" name="slika"> <input type="submit" value="Dodaj"></p>
		</form>
		<?
	} else {
		?>
		<?=genform("POST")?>
		<input type="hidden" name="subakcija" value="obrisisliku">
		<p>Slika:<br />
			<img src="?sta=common/slika&osoba=<?=$osoba?>"><br/>
			<input type="submit" value="Obriši sliku"><br></form>
			<?
			print genform("POST", "\"  enctype=\"multipart/form-data");
			?>
			<input type="hidden" name="subakcija" value="postavisliku">
			<input type="file" name="slika"> <input type="submit" value="Promijeni sliku"></p>
		</form>
		<?
	}
	?>
	<?=genform("POST")?>
	<input type="hidden" name="subakcija" value="potvrda">
	<table border="0" width="600"><tr>
			<td>Ime:</td>
			<td><input type="text" name="ime" value="<?=$ime?>" class="default"></td>
		</tr><td>
		<td>Prezime:</td>
		<td><input type="text" name="prezime" value="<?=$prezime?>" class="default"></td>
		</tr><tr>
			<td>Spol:</td>
			<td><input type="radio" name="spol" value="M" <?=$muski?>> Muški &nbsp; <input type="radio" name="spol" value="Z" <?=$zenski?>> Ženski</td>
		</tr><tr>
			<td>JMBG:</td>
			<td><input type="text" name="jmbg" value="<?=db_result($q400,0,11)?>" class="default"></td>
		</tr><tr>
			<td>Nacionalnost:</td>
			<td><select name="nacionalnost" class="default"><?=$nacionalnostr?></select></td>
		</tr><tr>
			<td>Broj indexa<br>(za studente):</td>
			<td><input type="text" name="brindexa" value="<?=db_result($q400,0,8)?>" class="default"></td>
		</tr><tr><td colspan="2">&nbsp;</td>
		</tr><tr>
			<td>Ime oca:</td>
			<td><input type="text" name="imeoca" value="<?=db_result($q400,0,2)?>" class="default"></td>
		</tr><td>
		<td>Prezime oca:</td>
		<td><input type="text" name="prezimeoca" value="<?=db_result($q400,0,3)?>" class="default"></td>
		</tr><tr>
			<td>Ime majke:</td>
			<td><input type="text" name="imemajke" value="<?=db_result($q400,0,4)?>" class="default"></td>
		</tr><tr>
			<td>Prezime majke:</td>
			<td><input type="text" name="prezimemajke" value="<?=db_result($q400,0,5)?>" class="default"></td>
		</tr><tr><td colspan="2">&nbsp;</td>
		</tr><tr>
			<td>Datum rođenja:</td>
			<td><input type="text" name="datum_rodjenja" value="<?
				if (db_result($q400,0,4)) print date("d. m. Y.", db_result($q400,0,9))?>" class="default"></td>
		</tr><tr>
			<td>Mjesto rođenja:</td>
			<td><?=mycombobox("mjesto_rodjenja", $mjestorvalue, $gradovilist)?></td>
		</tr><tr>
			<td>Općina rođenja:</td>
			<td><select name="opcina_rodjenja" class="default"><?=$opcinar?></select></td>
		</tr><tr>
			<td>Država rođenja:</td>
			<td><select name="drzava_rodjenja" class="default"><?=$drzaverodjr?></select></td>
		</tr><tr>
			<td>Državljanstvo:</td>
			<td><select name="drzavljanstvo" class="default"><?=$drzavljanstvor?></select></td>
		</tr><tr>
			<td>Posebne kategorije:</td>
			<td><?=$posebne_ispis?></td>
		</tr><tr><td colspan="2">&nbsp;</td>
		</tr><tr>
			<td>Adresa:</td>
			<td><input type="text" name="adresa" value="<?=db_result($q400,0,14)?>" class="default"><br>
				<?=mycombobox("adresa_mjesto", $mjestoavalue, $gradovilist)?></td>
		</tr><tr>
			<td>Kanton:</td>
			<td><?=db_dropdown("kanton",db_result($q400,0,17), "--Izaberite kanton--") ?></td>
		</tr><tr>
			<td>Telefon:</td>
			<td><input type="text" name="telefon" value="<?=db_result($q400,0,16)?>" class="default"></td>
		</tr><tr>
			<td valign="top">Kontakt e-mail:</td>
			<td><?=$email_adrese?>
				<input type="text" name="emailnovi" id="emailnovi" class="default"> <input type="button" class="default" value="Dodaj" onclick="javascript:location.href='?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=podaci&subakcija=dodajmail&adresa='+document.getElementById('emailnovi').value;"></td>
		</tr><tr><td colspan="2">&nbsp;</td>
		</tr><tr>
			<td>Stručni stepen:</td>
			<td><?=db_dropdown("strucni_stepen",db_result($q400,0,18), " ") ?></td>
		</tr><tr>
			<td>Naučni stepen:</td>
			<td><?=db_dropdown("naucni_stepen",db_result($q400,0,19), " ") ?></td>
		</tr></table>
	
	<p>
		<input type="Submit" value=" Izmijeni "></form>
		<a href="?sta=studentska/osobe&akcija=edit&osoba=<?=$osoba?>">Povratak nazad</a>
	</p>
	<?
	
}
