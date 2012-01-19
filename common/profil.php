<?

// COMMON/PROFIL + opcije korisnika

// v3.9.1.0 (2008/05/09) + Novi modul common/profil
// v3.9.1.1 (2008/08/28) + $conf_promjena_sifre, zahtjev za promjenu ostalih podataka
// v3.9.1.2 (2008/10/03) + Poostren zahtjev na POST
// v3.9.1.3 (2008/10/15) + Dodan format datuma
// v4.0.0.0 (2009/02/19) + Release
// v4.0.0.1 (2009/03/05) + Dodan logging; sakrij broj indexa korisnicima koji nisu studenti; prikazi informaciju ako je vec poslan zahtjev; ne radi nista ako korisnik nije napravio promjenu
// v4.0.9.1 (2009/06/19) + Tabela osoba: ukinuto polje srednja_skola (to ce biti rijeseno na drugi nacin); polje mjesto_rodjenja prebaceno na sifrarnik; dodano polje adresa_mjesto kao FK na isti sifrarnik
// v4.0.9.2 (2009/06/23) + Nova combobox kontrola koja se sasvim dobro pokazala kod studentska/prijemni


function common_profil() {

global $userid, $conf_system_auth, $conf_files_path, $conf_promjena_sifre, $conf_skr_naziv_institucije, $conf_skr_naziv_institucije_genitiv;
global $user_student, $user_nastavnik, $user_studentska, $user_siteadmin;


$akcija = $_REQUEST['akcija'];

// Ispis menija

$boja_licni = $boja_opcije = $boja_izbori = "#BBBBBB";
if ($akcija=="opcije") $boja_opcije="#DDDDDD";
else if ($akcija=="izbori") $boja_izbori="#DDDDDD";
else $boja_licni = "#DDDDDD";


// Za sada ne postoje dodatne mogućnosti ponuđene studentima

if ($user_nastavnik) {
	?>
	<br>
	
	<table border="0" cellspacing="0" cellpadding="0">
	<tr height="25">
		<td bgcolor="#FFFFFF" width="50">&nbsp;</td>
		<td bgcolor="<?=$boja_licni?>" width="200" align="center" onmouseover="this.style.backgroundColor='#FFFFFF';" onmouseout="this.style.backgroundColor='<?=$boja_licni?>';"><a href="?sta=common/profil&akcija=licni">Lični podaci</a></td>
		<td bgcolor="#FFFFFF" width="50">&nbsp;</td>
		<td bgcolor="<?=$boja_opcije?>" width="200" align="center" onmouseover="this.style.backgroundColor='#FFFFFF';" onmouseout="this.style.backgroundColor='<?=$boja_opcije?>';"><a href="?sta=common/profil&akcija=opcije">Zamger opcije</a></td>
		<td bgcolor="#FFFFFF" width="50">&nbsp;</td>
		<td bgcolor="<?=$boja_izbori?>" width="200" align="center" onmouseover="this.style.backgroundColor='#FFFFFF';" onmouseout="this.style.backgroundColor='<?=$boja_izbori?>';"><a href="?sta=common/profil&akcija=izbori">Izbori i nastavni ansambl</a></td>
		<td bgcolor="#FFFFFF" width="50">&nbsp;</td>
				<td bgcolor="<?=$boja_izbori?>" width="200" align="center" onmouseover="this.style.backgroundColor='#FFFFFF';" onmouseout="this.style.backgroundColor='<?=$boja_izbori?>';"><a href="?sta=common/profil&akcija=hr">UNSA HR</a></td>
		<td bgcolor="#FFFFFF" width="100">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="7" height="1" bgcolor="#000000" bgimage="images/fnord.gif">
	</tr>
	</table>
	<?
}



// Zamger opcije

if ($akcija=="opcije") {
	if ($_REQUEST['subakcija'] == "promjena" && check_csrf_token()) {
		$csv_separator = $_REQUEST['csv-separator'];
		if ($csv_separator != ";" && $csv_separator != ",") $csv_separator = my_escape($csv_separator);

		$q500 = myquery("delete from preference where korisnik=$userid and preferenca='csv-separator'");
		$q510 = myquery("insert into preference set korisnik=$userid, preferenca='csv-separator', vrijednost='$csv_separator'");
		
		$savjet_dana = intval($_REQUEST['savjet_dana']);

		$q520 = myquery("delete from preference where korisnik=$userid and preferenca='savjet_dana'");
		$q530 = myquery("insert into preference set korisnik=$userid, preferenca='savjet_dana', vrijednost=$savjet_dana");

		nicemessage("Zamger opcije uspješno promijenjene");
		zamgerlog("promijenjene zamger opcije", 2);
	}

	?>
	<h2>Opcije Zamgera</h2>
	<p>U ovom trenutku možete prilagoditi sljedeće opcije koje se odnose samo na vaš korisnički nalog:</p>

	<?=genform("POST")?>
	<input type="hidden" name="subakcija" value="promjena">
	<table border="0" cellspacing="0" cellpadding="0">

	<?

	// mass-input-format
	// mass-input-separator
	// - Pošto se ova dva jednostavno zapamte od zadnje primjene, ne vidim svrhu da ih dodajem ovdje

	// csv-separator

	$csv_separatori = array(";", ",");
	$csv_vrijednosti = array("SELECTED", ""); // default je tačka-zarez

	$q100 = myquery("select vrijednost from preference where korisnik=$userid and preferenca='csv-separator'");
	if (mysql_num_rows($q100)>0) {
		if (mysql_result($q100,0,0) == ",") {
			$csv_vrijednosti[0] = "";
			$csv_vrijednosti[1] = "SELECTED";
		} else if (mysql_result($q100,0,0) != ";") {
			$csv_vrijednosti[0] = "";
			array_push($csv_separatori, mysql_result($q100,0,0));
			array_push($csv_vrijednosti, "SELECTED");
		}
	}

	?>
	<tr>
		<td>Separator za izvoz u CSV format (Excel):</td>
		<td><select name="csv-separator">
		<?
		for ($i=0; $i<count($csv_separatori); $i++) 
			print "<option value=\"$csv_separatori[$i]\" $csv_vrijednosti[$i]\">$csv_separatori[$i]</option>\n";
		?>
		</select></td>
	</tr>
	<?

	// csv-encoding
	// - Treba uvijek biti Windows-1250

	// savjet_dana

	$savjet_dana = "CHECKED";
	$q110 = myquery("select vrijednost from preference where korisnik=$userid and preferenca='savjet_dana'");
	if (mysql_num_rows($q110)>0 && mysql_result($q110,0,0)==0)
		$savjet_dana = "";

	?>
	<tr>
		<td>Prikaži "Savjet dana":</td>
		<td><input type="checkbox" name="savjet_dana" value="1" <?=$savjet_dana?>></td>
	</tr>
	<?

	// Kraj tabele

	?>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr>
		<td>&nbsp;</td>
		<td><input type="submit" value="Promijeni"></td>
	</tr>

	</table>
	</form>
	<?

	return;
}



// Akcija: izbori i imenovanja

if ($akcija=="izbori") {

	?>
	<h2>Izbori, imenovanja, nastavni ansambl</h2>
	<p>Podaci u tabelama ispod za sada se ne mogu mijenjati! Molimo da sve greške i dopune prijavite službama <?=$conf_skr_naziv_institucije_genitiv?>.</p>

	<?


	if ($_REQUEST['subakcija'] == "arhiva_izbora") {
		?>
		<h3>Historijski pregled izbora u zvanja</h3>
		<table border="1" cellspacing="0" cellpadding="2">
		<tr>
			<th>Zvanje</th><th>Datum izbora</th><th>Datum isteka</th><th>Oblast</th><th>Podoblast</th><th>Radni odnos</th><th>Druga VŠO?</th>
		</tr>
		<?

		$q500 = myquery("select zvanje, UNIX_TIMESTAMP(datum_izbora), UNIX_TIMESTAMP(datum_isteka), oblast, podoblast, dopunski, druga_institucija from izbor WHERE osoba=$userid order by datum_isteka, datum_izbora");
		if (mysql_num_rows($q500) < 1) {
			?>
			<tr><td colspan="7">Nemamo nikakvih podataka o vašim izborima.</td></tr>
			<?
		}
		while ($r500 = mysql_fetch_row($q500)) {
			$q510 = myquery("select naziv from zvanje where id=$r500[0]");
			$nzvanje = mysql_result($q510,0,0);

			$datum_izbora = date("d. m. Y", $r500[1]);
			if ($r500[1] == 0)
				$datum_izbora = "<font color=\"red\">(nepoznato)</font>";
			$datum_isteka = date("d. m. Y", $r500[2]);
			if ($r500[2] == 0)
				$datum_isteka = "Neodređeno";
			$oblast = $r500[3];
			if ($oblast<1)
				$oblast = "<font color=\"red\">(nepoznato)</font>";
			else {
				$q520 = myquery("select naziv from oblast where id=$oblast");
				if (mysql_num_rows($q520)<1)
					$oblast = "<font color=\"red\">GREŠKA</font>";
				else
					$oblast = mysql_result($q520,0,0);
			}
			$podoblast = $r500[4];
			if ($podoblast<1)
				$podoblast = "<font color=\"red\">(nepoznato)</font>";
			else {
				$q530 = myquery("select naziv from podoblast where id=$podoblast");
				if (mysql_num_rows($q530)<1)
					$podoblast = "<font color=\"red\">GREŠKA</font>";
				else
					$podoblast = mysql_result($q530,0,0);
			}
			if ($r500[5]==0) $radniodnos = "Stalni";
			else $radniodnos = "Dopunski";

			if ($r500[6]==1) $druga_vso = "DA";

			?>
			<tr><td><?=$nzvanje?></td><td><?=$datum_izbora?></td><td><?=$datum_isteka?></td><td><?=$oblast?></td><td><?=$podoblast?></td><td><?=$radniodnos?></td><td><?=$druga_vso?></td></tr>
			<?
		}

		?>
		</table>
		<br>
		<a href="?sta=common/profil&akcija=izbori">&lt; &lt; Nazad</a>
		<?


		return;
	}



	if ($_REQUEST['subakcija'] == "arhiva_angazman") {
		?>
		<h3>Historijski pregled angažmana u nastavnom ansamblu</h3>
		<table border="1" cellspacing="0" cellpadding="2">
		<tr>
			<th>Akademska godina</th><th>Predmet</th><th>Status</th>
		</tr>
		<?

		$q540 = myquery("select p.id, p.naziv, angs.naziv, i.kratki_naziv, ag.naziv from angazman as a, angazman_status as angs, predmet as p, institucija as i, akademska_godina as ag where a.osoba=$userid and a.akademska_godina=ag.id and a.predmet=p.id and a.angazman_status=angs.id and p.institucija=i.id order by ag.naziv desc, angs.id, p.naziv");
		if (mysql_num_rows($q540) < 1) {
			?>
			<tr><td colspan="7">Nemamo nikakvih podataka o vašem angažmanu u nastavi.</td></tr>
			<?
		}
		while ($r540 = mysql_fetch_row($q540)) {
			?>
			<tr><td><?=$r540[4]?></td><td><?="$r540[1] ($r540[3])"?></td><td><?=$r540[2]?></td></tr>
			<?
		}

		?>
		</table>
		<br>
		<a href="?sta=common/profil&akcija=izbori">&lt; &lt; Nazad</a>
		<?


		return;
	}


	// Izbori u zvanja

	?>
	<table border="0" width="600">
	<tr><td colspan="2" bgcolor="#999999"><font color="#FFFFFF">IZBORI U ZVANJA:</font></td></tr>
	<tr>
	<?

	$q400 = myquery("select z.naziv, UNIX_TIMESTAMP(i.datum_izbora), UNIX_TIMESTAMP(i.datum_isteka), i.oblast, i.podoblast, i.dopunski, i.druga_institucija from izbor as i, zvanje as z WHERE i.osoba=$userid and i.zvanje=z.id order by i.datum_isteka DESC, i.datum_izbora DESC");
	if (mysql_num_rows($q400)==0) {
		?>
		<tr><td colspan="2">Nema podataka o izboru ili nikada niste bili izabrani u zvanje.</td></tr>
		<?
	} else {
		$datum_izbora = date("d. m. Y", mysql_result($q400,0,1));
		if (mysql_result($q400,0,1)==0)
			$datum_izbora = "<font color=\"red\">(nepoznato)</font>";
		$datum_isteka = date("d. m. Y", mysql_result($q400,0,2));
		if (mysql_result($q400,0,2)==0)
			$datum_isteka = "Neodređeno";
		$oblast = mysql_result($q400,0,3);
		if ($oblast<1)
			$oblast = "<font color=\"red\">(nepoznato)</font>";
		else {
			$q410 = myquery("select naziv from oblast where id=$oblast");
			if (mysql_num_rows($q410)<1)
				$oblast = "<font color=\"red\">GREŠKA</font>";
			else
				$oblast = mysql_result($q410,0,0);
		}
		$podoblast = mysql_result($q400,0,4);
		if ($podoblast<1)
			$podoblast = "<font color=\"red\">(nepoznato)</font>";
		else {
			$q420 = myquery("select naziv from podoblast where id=$podoblast");
			if (mysql_num_rows($q420)<1)
				$podoblast = "<font color=\"red\">GREŠKA</font>";
			else
				$podoblast = mysql_result($q420,0,0);
		}
		if (mysql_result($q400,0,5)==0) $radniodnos = "Stalni";
		else $radniodnos = "Dopunski";
		
		?>
		<tr><td>Zvanje:</td><td><b><?=mysql_result($q400,0,0)?></b></td></tr>
		<tr><td>Datum izbora:</td><td><b><?=$datum_izbora?></b></td></tr>
		<tr><td>Datum isteka:</td><td><b><?=$datum_isteka?></b></td></tr>
		<tr><td>Oblast:</td><td><b><?=$oblast?></b></td></tr>
		<tr><td>Podoblast:</td><td><b><?=$podoblast?></b></td></tr>
		<tr><td>Radni odnos:</td><td><b><?=$radniodnos?></b></td></tr>
		<?
		if (mysql_result($q400,0,6)==1) print "<tr><td colspan=\"2\"><b>Biran/a na drugoj VŠO</b></td></tr>\n";

		?>
		<tr><td colspan="2">&nbsp;</td></tr>
		<tr><td>&nbsp;</td><td><a href="?sta=common/profil&akcija=izbori&subakcija=arhiva_izbora">Historijski pregled izbora u zvanja</a></td></tr>
		<?
	}


	// Stručni i naučni stepen

	?>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td colspan="2" bgcolor="#999999"><font color="#FFFFFF">STRUČNI I NAUČNI STEPEN:</font></td></tr>
	<?

	$q430 = myquery("select strucni_stepen, naucni_stepen from osoba where id=$userid");
	$strucni_stepen = "Nepoznato / Bez stručnog stepena";
	$naucni_stepen = "Nepoznato / Bez naučnog stepena";
	if (mysql_result($q430,0,0)!=0) {
		$q440 = myquery("select naziv from strucni_stepen where id=".mysql_result($q430,0,0));
		$strucni_stepen = mysql_result($q440,0,0);
	}
	if (mysql_result($q430,0,1)!=0) {
		$q450 = myquery("select naziv from naucni_stepen where id=".mysql_result($q430,0,1));
		$naucni_stepen = mysql_result($q450,0,0);
	}

	?>
	<tr><td>Stručni stepen:</td><td><b><?=$strucni_stepen?></b></td></tr>
	<tr><td>Naučni stepen:</td><td><b><?=$naucni_stepen?></b></td></tr>
	<?


	// Nastavni ansambl

	?>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td colspan="2" bgcolor="#999999"><font color="#FFFFFF">UČEŠĆE U NASTAVNOM ANSAMBLU:</font></td></tr>
	<?


	$q460 = myquery("select p.id, p.naziv, angs.naziv, i.kratki_naziv from angazman as a, angazman_status as angs, predmet as p, institucija as i, akademska_godina as ag where a.osoba=$userid and a.akademska_godina=ag.id and ag.aktuelna=1 and a.predmet=p.id and a.angazman_status=angs.id and p.institucija=i.id order by angs.id, p.naziv");
	if (mysql_num_rows($q460) == 0) {
		?>
		<tr><td colspan="2">Niste angažovani niti na jednom predmetu u ovoj godini.</td></tr>
		<?
	}
	else {
		?>
		<tr><td valign="top">Predmeti:</td><td>
		<?
		while ($r460 = mysql_fetch_row($q460)) {
			print "$r460[1] ($r460[3]) - <b>$r460[2]</b><br>\n";
		}
		?>
		</td></tr>
		<?
	}
	?>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td>&nbsp;</td><td><a href="?sta=common/profil&akcija=izbori&subakcija=arhiva_angazman">Historijski pregled angažmana u nastavi</a></td></tr>
	<?

	return;
}


if ($akcija=="hr") {
	include("common/profil_hr.php");
	return;
}



// Akcija za lične podatke

?>
<h2>Zahtjev za promjenu ličnih podataka u Informacionom sistemu <?=$conf_skr_naziv_institucije_genitiv?></h2>
<?


if ($_POST['subakcija'] == "potvrda" && check_csrf_token()) {
	$ime = my_escape($_REQUEST['ime']);
	$prezime = my_escape($_REQUEST['prezime']);
	$spol = $_REQUEST['spol'];
	if ($spol != "M" && $spol != "Z") $spol="";
	$brindexa = my_escape($_REQUEST['brindexa']);
	$jmbg = my_escape($_REQUEST['jmbg']);

	$adresa = my_escape($_REQUEST['adresa']);
	$adresa_mjesto = my_escape($_REQUEST['adresa_mjesto']);
	$telefon = my_escape($_REQUEST['telefon']);
	$email = my_escape($_REQUEST['email']);

	$imeoca = my_escape($_REQUEST['imeoca']);
	$prezimeoca = my_escape($_REQUEST['prezimeoca']);
	$imemajke = my_escape($_REQUEST['imemajke']);
	$prezimemajke = my_escape($_REQUEST['prezimemajke']);
	$mjesto_rodjenja = my_escape($_REQUEST['mjesto_rodjenja']);
	$opcina_rodjenja = intval($_REQUEST['opcina_rodjenja']);
	$drzava_rodjenja = intval($_REQUEST['drzava_rodjenja']);
	$nacionalnost = intval($_REQUEST['nacionalnost']);
	$drzavljanstvo = intval($_REQUEST['drzavljanstvo']);
	$kanton = intval($_REQUEST['_lv_column_kanton']);
	if ($_REQUEST['borac']) $borac=1; else $borac=0;

	if (preg_match("/(\d+).*?(\d+).*?(\d+)/", $_REQUEST['datum_rodjenja'], $matches)) {
		$dan=$matches[1]; $mjesec=$matches[2]; $godina=$matches[3];
		if ($godina<100)
			if ($godina<50) $godina+=2000; else $godina+=1900;
		if ($godina<1000)
			if ($godina<900) $godina+=2000; else $godina+=1000;
	} else {
		$dan="00"; $mjesec="00"; $godina="0000";
	}

	// Mjesto
	$mjrid=0;
	if ($mjesto_rodjenja != "") {
		$q1 = myquery("select id from mjesto where naziv='$mjesto_rodjenja' and opcina=$opcina_rodjenja and drzava=$drzava_rodjenja");
		if (mysql_num_rows($q1)<1) {
			$q2 = myquery("insert into mjesto set naziv='$mjesto_rodjenja', opcina=$opcina_rodjenja, drzava=$drzava_rodjenja");
			$q1 = myquery("select id from mjesto where naziv='$mjesto_rodjenja' and opcina=$opcina_rodjenja and drzava=$drzava_rodjenja");
			zamgerlog("upisano novo mjesto rodjenja $mjesto_rodjenja", 2);
		}
		$mjrid = mysql_result($q1,0,0);
	}

	$admid=0;
	if ($adresa_mjesto != "") {
		$q3 = myquery("select id from mjesto where naziv='$adresa_mjesto'");
		if (mysql_num_rows($q3)<1) {
			$q4 = myquery("insert into mjesto set naziv='$adresa_mjesto'");
			$q3 = myquery("select id from mjesto where naziv='$adresa_mjesto'");
			zamgerlog("upisano novo mjesto (adresa) $adresa_mjesto", 2);
		}
		$admid = mysql_result($q3,0,0);
	}


	// Da li je uopste bilo promjene?
	$q05 = myquery("select ime, prezime, imeoca, prezimeoca, imemajke, prezimemajke, spol, email, brindexa, datum_rodjenja, mjesto_rodjenja, nacionalnost, drzavljanstvo, jmbg, adresa, adresa_mjesto, telefon, kanton, boracke_kategorije from osoba where id=$userid");
	if (mysql_result($q05,0,0)==$ime && mysql_result($q05,0,1)==$prezime && mysql_result($q05,0,2)==$imeoca && mysql_result($q05,0,3)==$prezimeoca && mysql_result($q05,0,4)==$imemajke && mysql_result($q05,0,5)==$prezimemajke && mysql_result($q05,0,6)==$spol && mysql_result($q05,0,7)==$email && mysql_result($q05,0,8)==$brindexa && mysql_result($q05,0,9)=="$godina-$mjesec-$dan" && mysql_result($q05,0,10)==$mjrid && mysql_result($q05,0,11)==$nacionalnost && mysql_result($q05,0,12)==$drzavljanstvo && mysql_result($q05,0,13)==$jmbg && mysql_result($q05,0,14)==$adresa && mysql_result($q05,0,15)==$admid && mysql_result($q05,0,16)==$telefon && mysql_result($q05,0,17)==$kanton && mysql_result($q05,0,18)==$borac) {
		?><p><b>Ništa nije promijenjeno?</b><br>
		Podaci koje ste unijeli ne razlikuju se od podataka koje već imamo u bazi. Zahtjev za promjenu neće biti poslan.</p><?
		return;
	}

	$q10 = myquery("select id from promjena_podataka where osoba=$userid");
	if (mysql_num_rows($q10)>0) {
		$id = mysql_result($q10,0,0);
		$upit = "osoba=$userid";
		if ($ime != "") $upit .= ", ime='$ime'";
		if ($prezime != "") $upit .= ", prezime='$prezime'";
		if ($imeoca != "") $upit .= ", imeoca='$imeoca'";
		if ($prezimeoca != "") $upit .= ", prezimeoca='$prezimeoca'";
		if ($imemajke != "") $upit .= ", imemajke='$imemajke'";
		if ($prezimemajke != "") $upit .= ", prezimemajke='$prezimemajke'";
		if ($spol != "") $upit .= ", spol='$spol'";
		if ($brindexa != "") $upit .= ", brindexa='$brindexa'";
		if ($jmbg != "") $upit .= ", jmbg='$jmbg'";
		if ($mjrid != 0) $upit .= ", mjesto_rodjenja=$mjrid";
		if ($nacionalnost != 0) $upit .= ", nacionalnost=$nacionalnost";
		if ($drzavljanstvo != 0) $upit .= ", drzavljanstvo=$drzavljanstvo";
		if ($adresa != "") $upit .= ", adresa='$adresa'";
		if ($admid != 0) $upit .= ", adresa_mjesto='$admid'";
		if ($telefon != "") $upit .= ", telefon='$telefon'";
		if ($email != "") $upit .= ", email='$email'";
		if ($kanton != 0) $upit .= ", kanton=$kanton";
		if ($godina!=1970) $upit .= ", datum_rodjenja='$godina-$mjesec-$dan'";
		if ($borac != 0) $upit .= ", boracke_kategorije=$borac";
		$q20 = myquery("update promjena_podataka set $upit where id=$id");
	} else {
		$q25 = myquery("select slika from osoba where id=$userid");
		$slika = mysql_result($q25,0,0);
		$q30 = myquery("insert into promjena_podataka set osoba=$userid, ime='$ime', prezime='$prezime', imeoca='$imeoca', prezimeoca='$prezimeoca', imemajke='$imemajke', prezimemajke='$prezimemajke', spol='$spol', brindexa='$brindexa', jmbg='$jmbg', mjesto_rodjenja=$mjrid, nacionalnost=$nacionalnost, drzavljanstvo=$drzavljanstvo, adresa='$adresa', adresa_mjesto=$admid, telefon='$telefon', email='$email', kanton=$kanton, datum_rodjenja='$godina-$mjesec-$dan', boracke_kategorije=$borac, slika='".my_escape($slika)."'");
	}
	zamgerlog("zatrazena promjena ličnih podataka",2); // 2 = edit

	?>
	<h2>Zahvaljujemo!</h2>

	<p>Zahtjev je poslan!</p>
	<p>Nakon što Studentska služba provjeri ispravnost podataka, oni će biti uneseni u Informacioni sistem. Molimo da budete dostupni za slučaj da je potrebna dokumentacija za neku od izmjena koje ste zatražili.</p>
	<?
	return;
}


// Postavljanje ili promjena slike

if ($_POST['subakcija']=="postavisliku" && check_csrf_token()) {
	$slika = $_FILES['slika']['tmp_name'];
	if ($slika && (file_exists($slika))) {
		// Kopiramo novu sliku na privremenu lokaciju
		$podaci = getimagesize($slika);
		$koef = $podaci[0]/$podaci[1];
		if ($koef < 0.5 || $koef > 2) {
			niceerror("Omjer širine i visine slike nije povoljan.");
			print "<p>Slika bi trebala biti uobičajenog formata slike za lične dokumente. Ova je formata $podaci[0]x$podaci[1].</p>\n";
			return;
		}

		$novavisina = 150;
		$novasirina = $novavisina * $koef;
		$filename = "$conf_files_path/slike/$userid-promjena";
		if (!file_exists("$conf_files_path/slike"))
			mkdir ("$conf_files_path/slike", 0777, true);

		$dest = imagecreatetruecolor($novasirina, $novavisina);
		switch ($podaci[2]) {
			case IMAGETYPE_GIF:
				$source = imagecreatefromgif($slika);
				imagecopyresampled($dest, $source, 0, 0, 0, 0, $novasirina, $novavisina, $podaci[0], $podaci[1]);
				imagegif($dest, $filename.".gif");
				$slikabaza = "$userid-promjena.gif";
				break;
			case IMAGETYPE_JPEG:
				$source = imagecreatefromjpeg($slika);
				imagecopyresampled($dest, $source, 0, 0, 0, 0, $novasirina, $novavisina, $podaci[0], $podaci[1]);
				imagejpeg($dest, $filename.".jpg");
				$slikabaza = "$userid-promjena.jpg";
				break;
			case IMAGETYPE_PNG:
				$source = imagecreatefrompng($slika);
				imagecopyresampled($dest, $source, 0, 0, 0, 0, $novasirina, $novavisina, $podaci[0], $podaci[1]);
				imagepng($dest, $filename.".png");
				$slikabaza = "$userid-promjena.png";
				break;
			case IMAGETYPE_TIFF_II:
				nicemessage("Nije moguća promjena dimenzija slike tipa TIFF... Ostavljam zadate dimenzije.");
				rename ($slika, $filename.".tiff");
				$slikabaza = "$userid-promjena.tiff";
				break;
			default:
				niceerror("Nepoznat tip slike.");
				print "<p>Za vašu profil sliku možete koristiti samo slike tipa GIF, JPEG ili PNG.</p>";
				return;
		}
	
		$q300 = myquery("select id from promjena_podataka where osoba=$userid");
		if (mysql_num_rows($q300)>0) {
			$q310 = myquery("update promjena_podataka set slika='$slikabaza' where osoba=$userid");
		} else {
			$q320 = myquery("insert into promjena_podataka select 0, $userid, ime, prezime, imeoca, prezimeoca, imemajke, prezimemajke, spol, email, brindexa, datum_rodjenja, mjesto_rodjenja, nacionalnost, drzavljanstvo, boracke_kategorije, jmbg, adresa, adresa_mjesto, telefon, kanton, '$slikabaza', NOW() from osoba where id=$userid");
		}
	
		zamgerlog("zatrazeno postavljanje/promjena slike", 2);
		?>
		<h2>Zahvaljujemo!</h2>
	
		<p>Zahtjev je poslan!</p>
		<p>Nakon što Studentska služba provjeri ispravnost podataka, oni će biti uneseni u Informacioni sistem. Molimo da budete dostupni za slučaj da je potrebna dokumentacija za neku od izmjena koje ste zatražili.</p>
		<?
		return;
	} else {
		nicemessage("Greška pri slanju slike");
	}
}


// Brisanje slike

if ($_POST['subakcija']=="obrisisliku" && check_csrf_token()) {
	$q300 = myquery("select id from promjena_podataka where osoba=$userid");
	if (mysql_num_rows($q300)>0) {
		$q310 = myquery("update promjena_podataka set slika='' where osoba=$userid");
	} else {
		$q320 = myquery("insert into promjena_podataka select 0, $userid, ime, prezime, imeoca, prezimeoca, imemajke, prezimemajke, spol, email, brindexa, datum_rodjenja, mjesto_rodjenja, nacionalnost, drzavljanstvo, boracke_kategorije, jmbg, adresa, adresa_mjesto, telefon, kanton, '', NOW() from osoba where id=$userid");
	}

	zamgerlog("zatrazeno brisanje slike", 2);
	?>
	<h2>Zahvaljujemo!</h2>

	<p>Zahtjev je poslan!</p>
	<p>Nakon što Službe <?=$conf_skr_naziv_institucije_genitiv?> provjere ispravnost podataka, oni će biti uneseni u Informacioni sistem. Molimo da budete dostupni za slučaj da je potrebna dokumentacija za neku od izmjena koje ste zatražili.</p>
	<?
	return;
}



if ($conf_system_auth == "ldap") {
?>
<h3><font color="red">NAPOMENA:</font> Pristupnu šifru možete promijeniti isključivo koristeći <?=$conf_promjena_sifre?></h3>
<?

} else {
	// TODO: napraviti promjenu sifre

}

$q390 = myquery("select UNIX_TIMESTAMP(vrijeme_zahtjeva) from promjena_podataka where osoba=$userid order by vrijeme_zahtjeva desc limit 1");

if (mysql_num_rows($q390)>0) {
	?><p><b>Već ste uputili zahtjev za promjenu ličnih podataka</b> (na dan <?=date("d. m. Y. u H:i:s", mysql_result($q390,0,0))?>). Vaš zahtjev se trenutno razmatra. U međuvremenu, ispod možete vidjeti stare podatke i eventualno ponovo poslati zahtjev (stari zahtjev će u tom slučaju biti zanemaren.</p><?
} else {
?>
	<p>Pozivamo Vas da podržite rad Studentske službe <?=$conf_skr_naziv_institucije_genitiv?> tako što ćete prijaviti sve eventualne greške u vašim ličnim podacima (datim ispod).</p><?
}

$q400 = myquery("select ime, prezime, email, brindexa, UNIX_TIMESTAMP(datum_rodjenja), mjesto_rodjenja, jmbg, drzavljanstvo, adresa, adresa_mjesto, telefon, kanton, spol, imeoca, prezimeoca, imemajke, prezimemajke, drzavljanstvo, nacionalnost, boracke_kategorije, slika from osoba where id=$userid");

// Spisak gradova
$q410 = myquery("select id,naziv, opcina, drzava from mjesto order by naziv");
$gradovir="<option></option>";
$gradovia="<option></option>";
while ($r410 = mysql_fetch_row($q410)) { 
	$gradovir .= "<option"; $gradovia .= "<option";
 	if ($r410[0]==mysql_result($q400,0,5)) { 
		$gradovir  .= " SELECTED"; 
		$mjestorvalue = $r410[1]; 
		$opcinar = $r410[2];
		$drzavar = $r410[3];
	}
 	if ($r410[0]==mysql_result($q400,0,9)) { $gradovia  .= " SELECTED"; $adresarvalue = $r410[1]; }
	$gradovir .= ">$r410[1]</option>\n";
	$gradovia .= ">$r410[1]</option>\n";
}


// Spisak opcina
$q420 = myquery("select id,naziv from opcina order by naziv");
$opciner="<option></option>";
while ($r420 = mysql_fetch_row($q420)) {
	$opciner .= "<option value=\"$r420[0]\"";
 	if ($r420[0]==$opcinar) { $opciner  .= " SELECTED";  }
	$opciner .= ">$r420[1]</option>\n";
}


// Spisak drzava
$q430 = myquery("select id,naziv from drzava order by naziv");
$drzaver="<option></option>";
$drzavlj="<option></option>";
while ($r430 = mysql_fetch_row($q430)) {
	$drzaver .= "<option value=\"$r430[0]\"";
 	if ($r430[0]==$drzavar) { $drzaver  .= " SELECTED";  }
	$drzaver .= ">$r430[1]</option>\n";
	$drzavlj .= "<option value=\"$r430[0]\"";
 	if ($r430[0]==mysql_result($q400,0,17)) { $drzavlj  .= " SELECTED";  }
	$drzavlj .= ">$r430[1]</option>\n";
}


// Spisak nacionalnosti
$q440 = myquery("select id,naziv from nacionalnost order by naziv");
$nacion="<option></option>";
while ($r440 = mysql_fetch_row($q440)) {
	$nacion .= "<option value=\"$r440[0]\"";
 	if ($r440[0]==mysql_result($q400,0,18)) { $nacion  .= " SELECTED";  }
	$nacion .= ">$r440[1]</option>\n";
}

// Spol
if (mysql_result($q400,0,12)=="M") $muskir = "CHECKED"; else $muskir="";
if (mysql_result($q400,0,12)=="Z") $zenskir = "CHECKED"; else $zenskir="";

// Pripadnik borackih kategorija
if (mysql_result($q400,0,19)==1) $boracke = "CHECKED"; else $boracke="";


?>
	<script type="text/javascript">
	function comboBoxEdit(evt, elname) {
		var ib = document.getElementById(elname);
		var list = document.getElementById("comboBoxDiv_"+elname);
		var listsel = document.getElementById("comboBoxMenu_"+elname);

		var key, keycode;
		if (evt) {
			key = evt.which;
			keycode = evt.keyCode;
		} else if (window.event) {
			key = window.event.keyCode;
			keycode = key; // wtf?
		} else return true;

		if (keycode==40) { // arrow down
			if (list.style.visibility == 'visible') {
				if (listsel.selectedIndex<listsel.length)
					listsel.selectedIndex = listsel.selectedIndex+1;
			} else {
				comboBoxShowHide(elname);
			}
			return false;

		} else if (keycode==38) { // arrow up
			if (list.style.visibility == 'visible' && listsel.selectedIndex>0) {
				listsel.selectedIndex = listsel.selectedIndex-1;
			}
			return false;

		} else if (keycode==13 && list.style.visibility == 'visible') { // Enter key - select option and hide
			comboBoxOptionSelected(elname);
			return false;

		} else if (key>31 && key<127) {
			// This executes before the letter is added to text
			// so we have to add it manually
			var ibtxt = ib.value.toLowerCase() + String.fromCharCode(key).toLowerCase();

			for (i=0; i<listsel.length; i++) {
				var listtxt = listsel.options[i].value.toLowerCase();
				if (ibtxt == listtxt.substr(0,ibtxt.length)) {
					listsel.selectedIndex=i;
					if (list.style.visibility == 'hidden') comboBoxShowHide(elname);
					return true;
				}
			}
			return true;
		}
		return true;
	}

	function comboBoxShowHide(elname) {
		var ib = document.getElementById(elname);
		var list = document.getElementById("comboBoxDiv_"+elname);
		var image = document.getElementById("comboBoxImg_"+elname);

		if (list.style.visibility == 'hidden') {
			// Nadji poziciju objekta
			var curleft = curtop = 0;
			var obj=ib;
			if (obj.offsetParent) {
				do {
					curleft += obj.offsetLeft;
					curtop += obj.offsetTop;
				} while (obj = obj.offsetParent);
			}
	
			list.style.visibility = 'visible';
			list.style.left=curleft;
			list.style.top=curtop+ib.offsetHeight;
			image.src = "images/cb_down.png";
		} else {
			list.style.visibility = 'hidden';
			image.src = "images/cb_up.png";
		}
	}
	function comboBoxHide(elname) {
		var list = document.getElementById("comboBoxDiv_"+elname);
		var listsel = document.getElementById("comboBoxMenu_"+elname);
		if (list.style.visibility == 'visible' && listsel.focused==false) {
			list.style.visibility = 'hidden';
			image.src = "images/cb_up.png";
		}
	}
	function comboBoxOptionSelected(elname) {
		var ib = document.getElementById(elname);
		var listsel = document.getElementById("comboBoxMenu_"+elname);
		
		ib.value = listsel.options[listsel.selectedIndex].value;
		comboBoxShowHide(elname);
	}
	</script>

	<!--script type="text/javascript" src="js/combo-box.js"></script-->


	<table border="0" width="600">
	<tr><td colspan="2" bgcolor="#999999"><font color="#FFFFFF">SLIKA:</font></td></tr>
	<tr><td colspan="2">Slika koju ovdje odaberete nije vaš "avatar" nego zvanična fotografija u formatu lične karte / pasoša koja ide u dokumentaciju fakulteta i vezuje se za vaše zvanične dokumente. Slika mora imati bijelu/svijetlu pozadinu. Molimo vas da pošaljete sliku zadovoljavajuće kvalitete radi lakšeg štampanja dokumenata.</td></tr>
	<?
	if (mysql_result($q400,0,20)=="") {
		print genform("POST", "a\"  enctype=\"multipart/form-data");
		?>
		<input type="hidden" name="subakcija" value="postavisliku">
		<tr><td colspan="2"><p><input type="file" name="slika"> <input type="submit" value="Dodaj sliku"></p></td></tr>
		</form>
		<?
	} else {
		?>
		<?=genform("POST")?>
		<input type="hidden" name="subakcija" value="obrisisliku">
		<tr><td colspan="2"><p>
		<img src="?sta=common/slika&osoba=<?=$userid?>"><br/>
		<input type="submit" value="Obriši sliku"><br></form>
		<?
		print genform("POST", "b\"  enctype=\"multipart/form-data");
		?>
		<input type="hidden" name="subakcija" value="postavisliku">
		<input type="file" name="slika"> <input type="submit" value="Promijeni sliku"></p></td></tr>
		</form>
		<?
	}
	?>
	<?=genform("POST")?>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td colspan="2" bgcolor="#999999"><font color="#FFFFFF">OSNOVNI PODACI:</font></td></tr>
	<tr><td>
		Ime:</td><td><input type="text" name="ime" value="<?=mysql_result($q400,0,0)?>" class="default">
	</td></tr>
	<tr><td>
		Prezime:</td><td><input type="text" name="prezime" value="<?=mysql_result($q400,0,1)?>" class="default">
	</td></tr>
	<tr><td>
		Spol:</td><td><input type="radio" name="spol" value="M" <?=$muskir?>> Muški &nbsp;&nbsp; <input type="radio" name="spol" value="Z" <?=$zenskir?>> Ženski
	</td></tr>

	<? if ($user_student) { ?>
	<tr><td>
		Broj indexa:</td><td><input type="text" name="brindexa" value="<?=mysql_result($q400,0,3)?>" class="default">
	</td></tr>
	<? } ?>
	<tr><td>
		JMBG:</td><td><input type="text" name="jmbg" value="<?=mysql_result($q400,0,6)?>" class="default">
	</td></tr>

	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td colspan="2" bgcolor="#999999"><font color="#FFFFFF">KONTAKT PODACI:</font></td></tr>
	<tr><td colspan="2">Ovi podaci će se koristiti kako bi Službe <?=$conf_skr_naziv_institucije_genitiv?> mogle lakše stupiti u kontakt s vama.</td></tr>

	</td></tr>
	<tr><td>
		Adresa (ulica i broj):</td><td><input type="text" name="adresa" value="<?=mysql_result($q400,0,8)?>" class="default">
	</td></tr>
	<tr><td>
		Adresa (mjesto):</td><td>
		<input type="text" name="adresa_mjesto" id="adresa_mjesto" value="<?=$adresarvalue?>" class="default" onKeyPress="comboBoxEdit(event, 'adresa_mjesto')" autocomplete="off" onBlur="comboBoxHide('adresa_mjesto')"><img src="images/cb_up.png" width="19" height="18" onClick="comboBoxShowHide('adresa_mjesto')" id="comboBoxImg_adresa_mjesto" valign="bottom"> <img src="images/cb_down.png" style="visibility:hidden">
		<!-- Rezultati pretrage primaoca -->
		<div id="comboBoxDiv_adresa_mjesto" style="position:absolute;visibility:hidden">
			<select name="comboBoxMenu_adresa_mjesto" id="comboBoxMenu_adresa_mjesto" size="10" onClick="comboBoxOptionSelected('adresa_mjesto')"><?=$gradovir?></select>
		</div>
	</td></tr>
	<tr><td>
		Kontakt telefon:</td><td><input type="text" name="telefon" value="<?=mysql_result($q400,0,10)?>" class="default">
	</td></tr>
	<tr><td>
		Kontakt e-mail:</td><td><input type="text" name="email" value="<?=mysql_result($q400,0,2)?>" class="default">
	</td></tr>
	<tr><td colspan="2">Ovim putem ne možete promijeniti vašu <?=$conf_skr_naziv_institucije?> e-mail adresu! Možete postaviti neku drugu adresu (Gmail, Hotmail...) na koju želite da primate obavještenja pored vaše <?=$conf_skr_naziv_institucije?> adrese.</td></tr>


	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td colspan="2" bgcolor="#999999"><font color="#FFFFFF">LIČNI PODACI:</font></td></tr>
	<tr><td colspan="2">Ovi podaci će se koristiti za automatsko popunjavanje formulara i obrazaca. Podaci su preuzeti iz formulara koje ste popunili prilikom upisa na fakultet. Ovim putem preuzimate punu odgovornost za ispravnost podataka koje navedete u formularu ispod.</td></tr>
	<tr><td>
		Ime oca:</td><td><input type="text" name="imeoca" value="<?=mysql_result($q400,0,13)?>" class="default">
	</td></tr>
	<tr><td>
		Prezime oca:</td><td><input type="text" name="prezimeoca" value="<?=mysql_result($q400,0,14)?>" class="default">
	</td></tr>
	<tr><td>
		Ime majke:</td><td><input type="text" name="imemajke" value="<?=mysql_result($q400,0,15)?>" class="default">
	</td></tr>
	<tr><td>
		Prezime majke:</td><td><input type="text" name="prezimemajke" value="<?=mysql_result($q400,0,16)?>" class="default">
	</td></tr>
	<tr><td>
		Datum rođenja:<br/>
		(D.M.G)</td><td><input type="text" name="datum_rodjenja" value="<?
		if (mysql_result($q400,0,4)) print date("d. m. Y.", mysql_result($q400,0,4))?>" class="default">
	</td></tr>
	<tr><td>
		Mjesto rođenja:</td><td>
		<input type="text" name="mjesto_rodjenja" id="mjesto_rodjenja" value="<?=$mjestorvalue?>" class="default" onKeyPress="return comboBoxEdit(event, 'mjesto_rodjenja')" autocomplete="off" onBlur="comboBoxHide('mjesto_rodjenja')"><img src="images/cb_up.png" width="19" height="18" onClick="comboBoxShowHide('mjesto_rodjenja')" id="comboBoxImg_mjesto_rodjenja" valign="bottom"> <img src="images/cb_down.png" style="visibility:hidden">
		<!-- Rezultati pretrage primaoca -->
		<div id="comboBoxDiv_mjesto_rodjenja" style="position:absolute;visibility:hidden">
			<select name="comboBoxMenu_mjesto_rodjenja" id="comboBoxMenu_mjesto_rodjenja" size="10" onClick="comboBoxOptionSelected('mjesto_rodjenja')" onFocus="this.focused=true;" onBlur="this.focused=false;"><?=$gradovir?></select>
		</div>
	</td></tr>
	<tr><td>
		Općina rođenja:</td><td><select name="opcina_rodjenja" class="default"><?=$opciner?></select>
	</td></tr>
	<tr><td>
		Država rođenja:</td><td><select name="drzava_rodjenja" class="default"><?=$drzaver?></select>
	</td></tr>
	<tr><td>
		Nacionalnost:</td><td><select name="nacionalnost" class="default"><?=$nacion?></select>
	</td></tr>
	<tr><td>
		Kanton / regija:</td><td><?=db_dropdown("kanton",mysql_result($q400,0,11), "--Izaberite kanton--") ?> <br/>
	</td></tr>
	<tr><td>
		Državljanstvo:</td><td><select name="drzavljanstvo" class="default"><?=$drzavlj?></select>
	</td></tr>
	<tr><td colspan="2">
		<input type="checkbox" name="borac" <?=$boracke?>> Dijete šehida / borca / pripadnik RVI
	</td></tr>


	<? if ($user_student && false) { ?>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td colspan="2" bgcolor="#999999"><font color="#FFFFFF">PODACI O PRETHODNOM CIKLUSU STUDIJA:</font></td></tr>
	<tr><td>
		Završena srednja škola:</td><td><input type="text" name="srednja_skola" value="<?=mysql_result($q400,0,13)?>" class="default">
	</td></tr>
	<? } ?>


	</table>

	<input type="hidden" name="subakcija" value="potvrda">
	<input type="Submit" value=" Pošalji zahtjev "></form>

	<p>&nbsp;</p>
	<p>Klikom na dugme iznad biće poslan zahtjev koji službe <?=$conf_skr_naziv_institucije_genitiv?> trebaju da provjere i potvrde. Ovo može potrajati nekoliko dana. Molimo da budete strpljivi.</p>

	<?



}


?>