<?

$podnaslov = "Unos radnog mjesta";
$operacija = "dodavanje";

// Dodavanje

if (($_REQUEST['operacija'] == "dodavanje" || $_REQUEST['operacija'] == "izmjena_potvrda") && check_csrf_token()) {
	$id = intval($_REQUEST['id']);
	$pocetak_dan = intval($_REQUEST['pocetakday']);
	$pocetak_mjesec = intval($_REQUEST['pocetakmonth']);
	$pocetak_godina = intval($_REQUEST['pocetakyear']);
	$kraj_dan = intval($_REQUEST['krajday']);
	$kraj_mjesec = intval($_REQUEST['krajmonth']);
	$kraj_godina = intval($_REQUEST['krajyear']);
	$poslodavac = my_escape($_REQUEST['poslodavac']);
	$adresa_poslodavca = my_escape($_REQUEST['adresa_poslodavca']);
	$radno_mjesto = my_escape($_REQUEST['radno_mjesto']);
	$radno_mjesto_en = my_escape($_REQUEST['radno_mjesto_en']);
	$opis_radnog_mjesta = my_escape($_REQUEST['opis_radnog_mjesta']);

	$pocetak = mktime(0, 0, 0, $pocetak_mjesec, $pocetak_dan, $pocetak_godina);
	$kraj = mktime(0, 0, 0, $kraj_mjesec, $kraj_dan, $kraj_godina);

	// Validacija
	if ($kraj<=$pocetak) {
		niceerror("Datum kraja je prije datuma početka");
	} else if (!preg_match("/\w/", $poslodavac) || !preg_match("/\w/", $radno_mjesto) || !preg_match("/\w/", $radno_mjesto_en)) {
		niceerror("Nisu unesena sva obavezna polja");
	} else {
		if ($_REQUEST['operacija'] == "dodavanje") {
			$q10 = myquery("INSERT INTO hr_radno_iskustvo SET osoba=$osoba, datum_pocetka=FROM_UNIXTIME($pocetak), datum_kraja=FROM_UNIXTIME($kraj), poslodavac='$poslodavac', adresa_poslodavca='$adresa_poslodavca', radno_mjesto='$radno_mjesto', radno_mjesto_en='$radno_mjesto_en', opis_radnog_mjesta='$opis_radnog_mjesta'");
			zamgerlog("dodano radno iskustvo", 2);
		} else {
			$q10 = myquery("UPDATE hr_radno_iskustvo SET osoba=$osoba, datum_pocetka=FROM_UNIXTIME($pocetak), datum_kraja=FROM_UNIXTIME($kraj), poslodavac='$poslodavac', adresa_poslodavca='$adresa_poslodavca', radno_mjesto='$radno_mjesto', radno_mjesto_en='$radno_mjesto_en', opis_radnog_mjesta='$opis_radnog_mjesta' WHERE id=$id");
			zamgerlog("izmijenjeno radno iskustvo", 2);
		}
		// Default vrijednosti
		$pocetak_dan = $pocetak_mjesec = $pocetak_godina = $kraj_dan = $kraj_mjesec = $kraj_godina = 1;
		$poslodavac = $adresa_poslodavca = $radno_mjesto = $radno_mjesto_en = $opis_radnog_mjesta = "";
	}
}

// Izmjena

else if ($_REQUEST['operacija'] == "izmjena") {
	$id = intval($_REQUEST['id']);
	$q20 = myquery("SELECT UNIX_TIMESTAMP(datum_pocetka) as pocetak, UNIX_TIMESTAMP(datum_kraja) as kraj, poslodavac, adresa_poslodavca, radno_mjesto, radno_mjesto_en, opis_radnog_mjesta from hr_radno_iskustvo where id=$id and osoba=$osoba");
	if (mysql_num_rows($q20)>0) {
		$r20 = mysql_fetch_assoc($q20);
		$pocetak_dan = date("d", $r20['pocetak']);
		$pocetak_mjesec = date("m", $r20['pocetak']);
		$pocetak_godina = date("Y", $r20['pocetak']);
		$kraj_dan = date("d", $r20['kraj']);
		$kraj_mjesec = date("m", $r20['kraj']);
		$kraj_godina = date("Y", $r20['kraj']);
		$poslodavac = $r20['poslodavac'];
		$adresa_poslodavca = $r20['adresa_poslodavca'];
		$radno_mjesto = $r20['radno_mjesto'];
		$radno_mjesto_en = $r20['radno_mjesto_en'];
		$opis_radnog_mjesta = $r20['opis_radnog_mjesta'];
	}
	$podnaslov = "Izmjena radnog mjesta";
	$operacija = "izmjena_potvrda";
}

// Izmjena

else if ($_REQUEST['operacija'] == "brisanje") {
	$id = intval($_REQUEST['id']);
	$q20 = myquery("SELECT id from hr_radno_iskustvo where id=$id and osoba=$osoba");
	if (mysql_num_rows($q20)>0) {
		$q30 = myquery("DELETE FROM hr_radno_iskustvo WHERE id=$id");
	}
}

else {
	// Default vrijednosti
	$pocetak_dan = $pocetak_mjesec = $pocetak_godina = $kraj_dan = $kraj_mjesec = $kraj_godina = 1;
	$poslodavac = $adresa_poslodavca = $radno_mjesto = $opis_radnog_mjesta = "";
}

?>
	<h2>Radno iskustvo</h2>

	<table border="0" width="800" id="tusavrsavanje">
	<tr>
		<td colspan="2" bgcolor="#999999">
		<font color="#FFFFFF"><b>Evidentirano radno iskustvo:</b></font>
		</td>
	</tr>
	<tr bgcolor="#84A6C6"  class="tdheader">
		<td>Datum početka</td>
		<td>Datum kraja</td>
		<td>Poslodavac</td>
		<td>Adresa poslodavca</td>
		<td>Radno mjesto</td>
		<td>Opis radnog mjesta</td>
		<td>&nbsp;</td>
	</tr>
	      
	      
	<?
	$starikraj = -1;
	$greska = false;
	$q420 = myquery("select id, UNIX_TIMESTAMP(datum_pocetka) as pocetak, UNIX_TIMESTAMP(datum_kraja) as kraj, poslodavac, adresa_poslodavca, radno_mjesto, radno_mjesto_en, opis_radnog_mjesta from hr_radno_iskustvo where osoba=$osoba order by datum_pocetka");
	while ($r420 = mysql_fetch_assoc($q420)) {
		$pocetak = date("d. m. Y", $r420['pocetak']);
		$kraj = date("d. m. Y", $r420['kraj']);

		if ($starikraj > $r420['pocetak']) $greska = true;
		$starikraj = $r420['kraj'];

		?>
		<tr>
			<td><?=$pocetak ?></td>
			<td><?=$kraj ?></td>
			<td><?=$r420['poslodavac'] ?></td>
			<td><?=$r420['adresa_poslodavca'] ?></td>
			<td><?=$r420['radno_mjesto'] ?><br><?=$r420['radno_mjesto_en'] ?></td>
			<td><?=$r420['opis_radnog_mjesta'] ?></td>
			<td style="text-align:center;" >
				<a href="?sta=common/profil&akcija=ljudskiresursi&subakcija=radnoiskustvo&operacija=izmjena&id=<?=$r420['id']?>"><img src="images/16x16/log_edit.png" /></a>
				<a href="?sta=common/profil&akcija=ljudskiresursi&subakcija=radnoiskustvo&operacija=brisanje&id=<?=$r420['id']?>"><img src="images/16x16/brisanje.png" /></a>
			</td>
		</tr>
		<?
	}

	?>

	</table>
	<?

		if ($greska) { niceerror("Datumi vam se preklapaju. Molimo provjerite."); }

	?>
	<p>&nbsp;</p>
	<?=genform("POST")?>
	<input type="hidden" name="operacija" value="<?=$operacija?>">
	<input type="hidden" name="id" value="<?=$id?>">
	     <table border="0" width="600">
	      <tr>
	        <td colspan="2" bgcolor="#999999">
	          <font color="#FFFFFF"><b><?=$podnaslov?>:</b></font>
	        </td>
	      </tr>
	      
	      <tr>
	        <td>Početak zaposlenja:</td>
	        <td>
	          <?=datectrl($pocetak_dan, $pocetak_mjesec, $pocetak_godina, "pocetak")?>
	        </td>
	      </tr>
	      
	      <tr>
	        <td>Kraj zaposlenja:</td>
	        <td>
	          <?=datectrl($pocetak_dan, $pocetak_mjesec, $pocetak_godina, "kraj")?>
	        </td>
	      </tr>

	      <tr>
	        <td>Poslodavac: <b><font color=red>*</font></b></td>
	        <td>
	          	<input class="default" type="text" id="poslodavac" name="poslodavac" value="<?=$poslodavac?>" />
	        </td>
	      </tr>

	      <tr>
	        <td>Adresa poslodavca:</td>
	        <td>
	          	<input class="default" type="text" id="adresa_poslodavca" name="adresa_poslodavca" value="<?=$adresa_poslodavca?>" />
	        </td>
	      </tr>
	      
	      <tr>
	        <td>Naziv radnog mjesta: <b><font color=red>*</font></b></td>
	        <td>
	          	<input class="default" type="text" id="radno_mjesto" name="radno_mjesto" value="<?=$radno_mjesto?>" />
	        </td>
	      </tr>
	      
	      <tr>
	        <td>Naziv radnog mjesta<br>(engleski jezik): <b><font color=red>*</font></b></td>
	        <td>
	          	<input class="default" type="text" id="radno_mjesto_en" name="radno_mjesto_en" value="<?=$radno_mjesto_en?>" />
	        </td>
	      </tr>
	      
	       <tr>
	        <td>Opis radnog mjesta:</td>
	        <td>
	          <textarea name="opis_radnog_mjesta" rows="3" cols="20" class="default"><?=$opis_radnog_mjesta?></textarea>
	        </td>
	      </tr>

	       <tr colspan="2">
	        <td><input type="submit" class="evidentiraj_usavrsavanje" value="Evidentiraj" \></td>
	        <td>
	        </td>
	      </tr>
	      
	    </table>  
	</form>
		<p>&nbsp;</p>
