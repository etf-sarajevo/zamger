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

global $userid,$conf_system_auth,$conf_promjena_sifre, $conf_skr_naziv_institucije_genitiv, $user_student;

?>
<h2>Zahtjev za promjenu ličnih podataka u Informacionom sistemu <?=$conf_skr_naziv_institucije_genitiv?></h2>
<?


if ($_POST['subakcija'] == "potvrda" && check_csrf_token()) {

	$ime = my_escape($_REQUEST['ime']);
	$prezime = my_escape($_REQUEST['prezime']);
	$brindexa = my_escape($_REQUEST['brindexa']);
	$jmbg = my_escape($_REQUEST['jmbg']);
	$mjesto_rodjenja = my_escape($_REQUEST['mjesto_rodjenja']);
	$drzavljanstvo = my_escape($_REQUEST['drzavljanstvo']);
	$adresa = my_escape($_REQUEST['adresa']);
	$adresa_mjesto = my_escape($_REQUEST['adresa_mjesto']);
	$telefon = my_escape($_REQUEST['telefon']);
	$email = my_escape($_REQUEST['email']);
	$kanton = intval($_REQUEST['_lv_column_kanton']);

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
		$q1 = myquery("select id from mjesto where naziv='$mjesto_rodjenja'");
		if (mysql_num_rows($q1)<1) {
			$q2 = myquery("insert into mjesto set naziv='$mjesto_rodjenja'");
			$q1 = myquery("select id from mjesto where naziv='$mjesto_rodjenja'");
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
	$q05 = myquery("select ime, prezime, email, brindexa, datum_rodjenja, mjesto_rodjenja, drzavljanstvo, jmbg, adresa, adresa_mjesto, telefon, kanton from osoba where id=$userid");
	if (mysql_result($q05,0,0)==$ime && mysql_result($q05,0,1)==$prezime && mysql_result($q05,0,2)==$email && mysql_result($q05,0,3)==$brindexa && mysql_result($q05,0,4)=="$godina-$mjesec-$dan" && mysql_result($q05,0,5)==$mjrid && mysql_result($q05,0,6)==$drzavljanstvo && mysql_result($q05,0,7)==$jmbg && mysql_result($q05,0,8)==$adresa && mysql_result($q05,0,9)==$admid && mysql_result($q05,0,10)==$telefon && mysql_result($q05,0,11)==$kanton) {
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
		if ($brindexa != "") $upit .= ", brindexa='$brindexa'";
		if ($jmbg != "") $upit .= ", jmbg='$jmbg'";
		if ($mjrid != 0) $upit .= ", mjesto_rodjenja=$mjrid";
		if ($drzavljanstvo != "") $upit .= ", drzavljanstvo='$drzavljanstvo'";
		if ($adresa != "") $upit .= ", adresa='$adresa'";
		if ($admid != 0) $upit .= ", adresa_mjesto='$admid'";
		if ($telefon != "") $upit .= ", telefon='$telefon'";
		if ($email != "") $upit .= ", email='$email'";
		if ($kanton != 0) $upit .= ", kanton=$kanton";
		if ($godina!=1970) $upit .= ", datum_rodjenja='$godina-$mjesec-$dan'";
		$q20 = myquery("update promjena_podataka set $upit where id=$id");
	} else {
		$q30 = myquery("insert into promjena_podataka set osoba=$userid, ime='$ime', prezime='$prezime', brindexa='$brindexa', jmbg='$jmbg', mjesto_rodjenja=$mjrid, drzavljanstvo='$drzavljanstvo', adresa='$adresa', adresa_mjesto=$admid, telefon='$telefon', email='$email', kanton=$kanton, datum_rodjenja='$godina-$mjesec-$dan'");
	}
	zamgerlog("zatrazena promjena ličnih podataka",2); // 2 = edit

	?>
	<h2>Zahvaljujemo!</h2>

	<p>Zahtjev je poslan!</p>
	<p>Nakon što Studentska služba provjeri ispravnost podataka, oni će biti uneseni u Informacioni sistem. Molimo da budete dostupni za slučaj da je potrebna dokumentacija za neku od izmjena koje ste zatražili.</p>
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
	?><p><b>Već ste uputili zahtjev za promjenu ličnih podataka</b> (na dan <?=date("d. m. Y. u H:i:s", mysql_result($q390,0,0))?>). Vaš zahtjev se trenutno razmatra. U međuvremenu, ispod možete vidjeti stare podatke ili eventualno ponovo poslati zahtjev (stari zahtjev će u tom slučaju biti zanemaren.</p><?
} else {
?>
	<p>Pozivamo Vas da podržite rad Studentske službe <?=$conf_skr_naziv_institucije_genitiv?> tako što ćete prijaviti sve eventualne greške u vašim ličnim podacima (datim ispod).</p><?
}

$q400 = myquery("select ime, prezime, email, brindexa, UNIX_TIMESTAMP(datum_rodjenja), mjesto_rodjenja, jmbg, drzavljanstvo, adresa, adresa_mjesto, telefon, kanton from osoba where id=$userid");

// Spisak gradova
$q410 = myquery("select id,naziv from mjesto order by naziv");
$gradovir="<option></option>";
$gradovia="<option></option>";
while ($r410 = mysql_fetch_row($q410)) { 
	$gradovir .= "<option"; $gradovia .= "<option";
 	if ($r410[0]==mysql_result($q400,0,5)) { $gradovir  .= " SELECTED"; $mjestorvalue = $r410[1]; }
 	if ($r410[0]==mysql_result($q400,0,9)) { $gradovia  .= " SELECTED"; $adresarvalue = $r410[1]; }
	$gradovir .= ">$r410[1]</option>\n";
	$gradovia .= ">$r410[1]</option>\n";
}


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

	<?=genform("POST")?>
	<table border="0" width="600">
	<tr><td valign="top">
		Ime:</td><td><input type="text" name="ime" value="<?=mysql_result($q400,0,0)?>" class="default">
	</td></tr><tr><td valign="top">
		Prezime:</td><td><input type="text" name="prezime" value="<?=mysql_result($q400,0,1)?>" class="default">
	</td></tr>
	<? if ($user_student) { ?>
	<tr><td valign="top">
		Broj indexa:</td><td><input type="text" name="brindexa" value="<?=mysql_result($q400,0,3)?>" class="default">
	</td></tr>
	<? } ?>
	<tr><td valign="top">
		JMBG:</td><td><input type="text" name="jmbg" value="<?=mysql_result($q400,0,6)?>" class="default">
	</td></tr><tr><td valign="top">
		Datum rođenja:<br/>
		(D.M.G)</td><td><input type="text" name="datum_rodjenja" value="<?
		if (mysql_result($q400,0,4)) print date("d. m. Y.", mysql_result($q400,0,4))?>" class="default">
	</td></tr><tr><td valign="top">
		Mjesto rođenja:</td><td>
		<input type="text" name="mjesto_rodjenja" id="mjesto_rodjenja" value="<?=$mjestorvalue?>" class="default" onKeyPress="return comboBoxEdit(event, 'mjesto_rodjenja')" autocomplete="off" onBlur="comboBoxHide('mjesto_rodjenja')"><img src="images/cb_up.png" width="19" height="18" onClick="comboBoxShowHide('mjesto_rodjenja')" id="comboBoxImg_mjesto_rodjenja" valign="bottom"> <img src="images/cb_down.png" style="visibility:hidden">
		<!-- Rezultati pretrage primaoca -->
		<div id="comboBoxDiv_mjesto_rodjenja" style="position:absolute;visibility:hidden">
			<select name="comboBoxMenu_mjesto_rodjenja" id="comboBoxMenu_mjesto_rodjenja" size="10" onClick="comboBoxOptionSelected('mjesto_rodjenja')" onFocus="this.focused=true;" onBlur="this.focused=false;"><?=$gradovir?></select>
		</div>
	</td></tr><tr><td valign="top">
		Državljanstvo:</td><td><input type="text" name="drzavljanstvo" value="<?=mysql_result($q400,0,7)?>" class="default">
	</td></tr><tr><td valign="top">
		Adresa:</td><td><input type="text" name="adresa" value="<?=mysql_result($q400,0,8)?>" class="default">
	</td></tr><tr><td valign="top">
		&nbsp;</td><td>
		<input type="text" name="adresa_mjesto" id="adresa_mjesto" value="<?=$adresarvalue?>" class="default" onKeyPress="comboBoxEdit(event, 'adresa_mjesto')" autocomplete="off" onBlur="comboBoxHide('adresa_mjesto')"><img src="images/cb_up.png" width="19" height="18" onClick="comboBoxShowHide('adresa_mjesto')" id="comboBoxImg_adresa_mjesto" valign="bottom"> <img src="images/cb_down.png" style="visibility:hidden">
		<!-- Rezultati pretrage primaoca -->
		<div id="comboBoxDiv_adresa_mjesto" style="position:absolute;visibility:hidden">
			<select name="comboBoxMenu_adresa_mjesto" id="comboBoxMenu_adresa_mjesto" size="10" onClick="comboBoxOptionSelected('adresa_mjesto')"><?=$gradovir?></select>
		</div>
	</td></tr><tr><td valign="top">
		Kanton / regija:</td><td><?=db_dropdown("kanton",mysql_result($q400,0,11), "--Izaberite kanton--") ?> <br/>
	</td></tr><tr><td valign="top">
		Kontakt telefon:</td><td><input type="text" name="telefon" value="<?=mysql_result($q400,0,10)?>" class="default">
	</td></tr><tr><td valign="top">
		Kontakt e-mail:</td><td><input type="text" name="email" value="<?=mysql_result($q400,0,2)?>" class="default">
	</td></tr></table>

	<input type="hidden" name="subakcija" value="potvrda">
	<input type="Submit" value=" Pošalji zahtjev "></form>

<?



}


?>