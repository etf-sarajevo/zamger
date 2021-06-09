<?

// STUDENTSKA/OSOBE - administracija studenata, studentska služba



function studentska_osobe() {

global $userid, $user_siteadmin, $user_studentska;
global $conf_files_path, $conf_system_auth, $conf_ldap, $conf_ldap_search, $conf_ldap_server, $conf_ldap_dn, $conf_ldap_domain;
global $conf_knjigovodstveni_servis;
global $registry; // šta je od modula aktivno
	global $_api_http_code;

global $_lv_; // Potrebno za genform()

require_once("lib/student_predmet.php");
require_once("lib/student_studij.php"); // Za ima_li_uslov
require_once("lib/ws.php"); // Web service za parsiranje XMLa
require_once("lib/formgen.php"); // datectrl, db_dropdown
require_once("lib/predmet.php"); // kreiraj_ponudu_kursa


// Provjera privilegija
if (!$user_siteadmin && !$user_studentska) { // 2 = studentska, 3 = admin
	zamgerlog("korisnik nije studentska",3);
	zamgerlog2("nije studentska");
	biguglyerror("Pristup nije dozvoljen.");
	return;
}



?>

<center>
<table border="0"><tr><td>

<?

$akcija = param('akcija');
$osoba = int_param('osoba');


// Dodavanje novog korisnika u bazu

if ($akcija == "novi" && check_csrf_token()) {

	$ime = substr(db_escape($_POST['ime']), 0, 100);
	if (!preg_match("/\w/", $ime)) {
		niceerror("Ime nije ispravno");
		return;
	}

	$prezime = substr(db_escape($_POST['prezime']), 0, 100);

	// Probamo tretirati ime kao LDAP UID
	if ($conf_ldap_search) {
		// TODO: smanjiti duplikaciju koda za LDAP!
		$uid = $ime;
		$ds = ldap_connect($conf_ldap_server);
		ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
		if ($ds && ldap_bind($ds)) {
			$sr = ldap_search($ds, $conf_ldap_dn, "uid=$uid", array("givenname","sn") );
			$results = ldap_get_entries($ds, $sr);
			if ($results['count'] > 0) {
				$gn = $results[0]['givenname'];
				if (is_array($gn)) $gn = $results[0]['givenname'][0];
				if ($gn) $ime = $gn;

				$sn = $results[0]['sn'];
				if (is_array($sn)) $sn = $results[0]['sn'][0];
				if ($sn) $prezime = $sn;
			} else {
				zamgerlog("korisnik '$uid' nije pronadjen na LDAPu",3);
				zamgerlog2("korisnik nije pronadjen na LDAPu", 0, 0, 0, $uid);
				$uid = "";
				niceerror("Korisnik nije pronadjen na LDAPu... dodajem novog!");
			}
		} else {
			zamgerlog("ne mogu kontaktirati LDAP server",3);
			zamgerlog2("ne mogu kontaktirati LDAP server");
			niceerror("Ne mogu kontaktirati LDAP server... pravim se da ga nema :(");
		}
	}

	if (!preg_match("/\w/", $prezime)) {
		niceerror("Prezime nije ispravno");
		return;
	}

	// Da li ovaj korisnik već postoji u osoba tabeli?
	$q10 = db_query("select id, ime, prezime from osoba where ime like '$ime' and prezime like '$prezime'");
	if ($r10 = db_fetch_row($q10)) {
		zamgerlog("korisnik vec postoji u bazi ('$ime' '$prezime' - ID: $r10[0])",3);
		zamgerlog2("korisnik vec postoji u bazi", intval($r10[0]), 0, 0, "'$ime' '$prezime'");
		niceerror("Korisnik već postoji u bazi:");
		print "<br><a href=\"?sta=studentska/osobe&akcija=edit&osoba=$r10[0]\">$r10[1] $r10[2]</a>";
		return;

	} else {
		// Nije u tabeli, dodajemo ga...
		$q30 = db_query("select id from osoba order by id desc limit 1");
		$osoba = db_result($q30,0,0)+1;

		$q40 = db_query("insert into osoba set id=$osoba, ime='$ime', prezime='$prezime', naucni_stepen=6, strucni_stepen=5");
		// 6 = bez naučnog stepena, 5 = srednja stručna sprema

		if ($conf_ldap_search && $uid != "") {
			// Ako je LDAP onda imamo email adresu
			$email = $uid.$conf_ldap_domain;
			$q33 = db_query("INSERT INTO email SET osoba=$osoba, adresa='$email', sistemska=1");
			// Adresu podešavamo kao sistemsku što znači da je korisnik ne može mijenjati niti brisati

			// Mozemo ga dodati i u auth tabelu
			$q35 = db_query("select count(*) from auth where id=$osoba");
			if (db_result($q35,0,0)==0) {
				$q37 = db_query("insert into auth set id=$osoba, login='$uid', admin=1, aktivan=1");
			}
		}

		nicemessage("Novi korisnik je dodan.");
		zamgerlog("dodan novi korisnik u$osoba (ID: $osoba)",4); // nivo 4: audit
		zamgerlog2("dodan novi korisnik", $osoba);
		print "<br><a href=\"?sta=studentska/osobe&akcija=edit&osoba=$osoba\">$ime $prezime</a>";
		return;
	}
}



// Izmjena licnih podataka osobe

if ($akcija == "podaci") {

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

} // if ($akcija == "podaci")
	



	// Upis studenta na semestar
	
	else if ($akcija == "upis") {
		?>
		<p><a href="?sta=studentska/osobe&akcija=edit&osoba=<?=int_param('osoba')?>">Nazad na podatke o osobi</a></p>
		<?

		// Parameters, if passed
		
		$student = int_param('osoba');
		$studij = int_param('studij');
		$semestar = int_param('semestar');
		$godina = int_param('godina');
		if (param('ponovac')) $ponovac = true; else $ponovac = false;
		if (param('status')) $status = int_param('status'); else $status = 0; /* Default status: Normal student */
		$put = int_param('put');
		$enrollment = array_to_object([ "student" => [ "id" => $student], "Programme" => [ "id" => $studij], "semester" => $semestar, "AcademicYear" => [ "id" => $godina], "repeat" => $ponovac, "status" => $status, "whichTime" => $put ]);
		
		// Optional fields
		if (param('nacin_studiranja')) {
			$nacin_studiranja = int_param('nacin_studiranja');
			$enrollment->EnrollmentType = [ "id" => $nacin_studiranja ];
		} else $nacin_studiranja = false;
		if (param('plan_studija')) {
			$plan_studija = int_param('plan_studija');
			$enrollment->Curriculum = [ "id" => $plan_studija ];
		} else $plan_studija = false;
		if (param('odluka')) {
			$odluka = int_param('odluka');
			$enrollment->Decision = [ "id" => $odluka ];
		} else $odluka = false;
		
		
		// Zaduženja
		$balance = api_call("balance/$student");
		// API expects negative value for debt
		$newAmount = -floatval(param('zaduzenje'));
		if ($newAmount != floatval($balance['amount'])) {
			print "Old amount " . $balance['amount'] . " new amount $newAmount<br>\n";
			$newBalance = array_to_object( [ "Person" => [ "id" => $student], "amount" => $newAmount ] );
			$result = api_call("balance/$student", $newBalance, "PUT");
			if ($_api_http_code != "201") {
				niceerror("Neuspješna izmjena zaduženja studenta");
				api_report_bug($result, []);
				return;
			} else {
				nicemessage("Izmijenjeno zaduženje studenta");
			}
			// Show updated amount on UI
			$balance['amount'] = $newAmount;
		}
		
		// Kada je specificirana subakcija uvijek omogući upis, čak i ako nema uslov (forsiranje)
		if (param('finish')) {
			$enrollment->dryRun = false;
			$newEnrollment = api_call("enrollment/$student", $enrollment, "POST");
			if ($_api_http_code == "201") {
				$studentFullname = $newEnrollment['student']['surname'] . " " . $newEnrollment['student']['name'] . " (" . $newEnrollment['student']['studentIdNr'] . ")";
				
				nicemessage("Student $studentFullname je upisan na studij " . $newEnrollment['Programme']['name'] . ", $semestar. semestar u akademskoj " . $newEnrollment['AcademicYear']['name'] . " godini");
				
				// Enroll into courses
				foreach($newEnrollment['enrollCourses'] as $cuy) {
					$result = api_call("course/" . $cuy['CourseUnit']['id'] . "/" . $cuy['AcademicYear']['id'] . "/enroll/$student", [], "POST");
					if ($_api_http_code == "201") {
						print "* Student upisan na predmet " . $cuy['courseName'] . "<br>";
					} else if ($_api_http_code == "403") {
						print "* Student je već od ranije upisan na predmet " . $cuy['courseName'] . "<br>";
					} else {
						niceerror("Neuspješan upis studenta na predmet");
						api_report_bug($result, []);
					}
				}
				foreach($newEnrollment['failedCourses'] as $co) {
					$co['AcademicYear'] = $newEnrollment['AcademicYear'];
					$co = array_to_object($co);
					$result = api_call("course/offering/enroll/$student", $co, "PUT");
					if ($_api_http_code == "201") {
						print "* Student upisan na preneseni predmet " . $co->CourseDescription->name . "<br>";
					} else if ($_api_http_code == "403") {
						print "* Student je već od ranije upisan na preneseni predmet " . $co->CourseDescription->name . "<br>";
					} else {
						niceerror("Neuspješan upis studenta na predmet");
						api_report_bug($result, $co);
					}
				}
				
				print "<p>Završene su sve operacije u vezi upisa.</p>";
				
			} else if($_api_http_code == "400" && starts_with($newEnrollment['message'], "Already enrolled")) {
				if ($semestar % 2 == 0) $rijec = "ljetnji"; else $rijec = "zimski";
				niceerror("Student je već upisan u $rijec semestar tekuće akademske godine!");
				?>
				<p>Vratite se na <a href="?sta=studentska%2Fosobe&akcija=edit&osoba=<?=$student?>">pregled podataka o studentu</a> da saznate više informacija.</p>
				<?
				opcije_za_retroaktivni_upis($godina, $semestar);
				return;
			} else {
				niceerror("Neuspješan upis studenta na studij");
				api_report_bug($newEnrollment, $enrollment);
			}
			return;
		}
		
		// Use "dry run" enrollment to find out which options need to be changed
		$enrollment->dryRun = true;
		// If everything is ok, we allow user to finish enrollment
		$finish = true;
		// Otherwise, some options will be shown in UI
		$show = [];
		
		$newEnrollment = api_call("enrollment/$student", $enrollment, "POST");
		
		// Handle errors
		if ($_api_http_code == "400" && starts_with($newEnrollment['message'], "Already enrolled")) {
			if ($semestar % 2 == 0) $rijec = "ljetnji"; else $rijec = "zimski";
			niceerror("Student je već upisan u $rijec semestar odabrane akademske godine!");
			?>
			<p>Vratite se na <a href="?sta=studentska%2Fosobe&akcija=edit&osoba=<?=$student?>">pregled podataka o studentu</a> da saznate više informacija.</p>
			<?
			opcije_za_retroaktivni_upis($godina, $semestar);
			return;
		}
		
		else if ($_api_http_code != "201" && !strstr($newEnrollment['message'], "semesters, but")) {
			niceerror("Neuspješan upis studenta na studij");
			api_report_bug($newEnrollment, $enrollment);
			return;
		}
		
		// Programme, cycle or curriculum has changed
		if (param('change') || $studij == -1 || ($_api_http_code == "400" && strstr($newEnrollment['message'], "semesters, but")) || $newEnrollment['Programme']['id'] != $studij) {
			$show['programme'] = $show['curriculum'] = true;
			$finish = false;
			
			// Get a list of all programmes from api
			$allProgrammes = api_call("programme/all", [ "resolve" => [ "ProgrammeType" ]]);
			$allProgrammes = $allProgrammes['results'];
			$programmeTypes = [];
			foreach($allProgrammes as $prog) {
				$ptId = $prog['ProgrammeType']['id']; // shortcut
				if (!array_key_exists($ptId, $programmeTypes))
					$programmeTypes[$ptId] = $prog['ProgrammeType'];
			}
			
			
			// If semester is invalid, we will try enrollment into the next cycle
			if (strstr($newEnrollment['message'], "semesters, but")) {
				// First find old programme, cause we lost it >D
				foreach($allProgrammes as $prog) {
					if ($prog['id'] == $studij)
						$oldProgramme = $prog;
				}
				$newProgramme = false;
				foreach($allProgrammes as $prog) {
					if ($prog['Institution']['id'] == $oldProgramme['Institution']['id'] && $prog['ProgrammeType']['cycle'] == $oldProgramme['ProgrammeType']['cycle'] + 1)
						$newProgramme = $prog;
				}
				if (!$newProgramme) {
					niceerror("Neispravan broj semestra");
					opcije_za_retroaktivni_upis($godina, $semestar);
					return;
				}
				$enrollment->Programme = [ "id" => $newProgramme['id']];
				$enrollment->semester = 1;
				
				// Retry enrollment with new cycle
				$newEnrollment = api_call("enrollment/$student", $enrollment, "PUT");
				
				if ($_api_http_code == "400") {
					niceerror("Neispravan broj semestra");
					opcije_za_retroaktivni_upis($godina, $semestar);
					return; // We don't know how to continue after this
				}
			}
			
			$currentCycle = 0;
			
			?>
			<script>
				function changeCycle() {
				    var cycle = document.getElementById('cycle').value;
				    var progEl  = document.getElementById('programme');
				    progEl.options.length = 0;
				    <?
				    foreach ($programmeTypes as $id => $programmeType) {
				    	print "if (cycle == $id) {\n";
				    	foreach($allProgrammes as $programme)
				    		if ($programme['ProgrammeType']['id'] == $id) {
								print "option = document.createElement(\"option\");\n";
								print "option.text = '" . $programme['name'] . "';\n";
								print "option.value = " . $programme['id'] . ";\n";
								if ($programme['id'] == $studij) {
									print "option.selected = true;\n";
									$currentCycle = $id;
								}
								print "progEl.add(option);";
							}
				    	print "}\n";
					}
				    ?>
				}
                function changeProgramme() {
                    var curriculumEl = document.getElementById('curriculum');
                    curriculumEl.options.length = 0;
                    var option = document.createElement("option");
                    option.text = '(odredi automatski)';
                    option.value = 0;
                    curriculumEl.add(option);
                }
                window.addEventListener("load", function() {
                    document.getElementById('cycle').value = <?=$currentCycle?>;
                    changeCycle();
				});
			</script>
			<?
		} else
			$show['programme'] = $show['curriculum'] = false;
			
		// Check if other data has changed, make further API queries if neccessary
		if (param('change') || $newEnrollment['semester'] != $semestar) {
			$show['semester'] = true;
			$finish = false;
		} else
			$show['semester'] = false;
			
		if (param('change') || $newEnrollment['repeat'] != $ponovac) {
			$show['repeat'] = true;
			$finish = false;
		} else
			$show['repeat'] = false;
			
		if (param('change') || $newEnrollment['status'] != $status) {
			$show['status'] = true;
			$finish = false;
		} else
			$show['status'] = false;
		
		if (param('change')) {
			$years = api_call("zamger/year")["results"];
			$show['year'] = true;
			$finish = false;
		} else
			$show['year'] = false;
			
		if (param('change')) {
			$curricula = api_call("curriculum/programme/" . $newEnrollment['Programme']['id'] . "/all", [ "resolve" => ["AcademicYear"]])["results"];
			$show['year'] = true;
			$finish = false;
		} else
			$show['year'] = false;

		if (param('change') || param('nacin_studiranja') && $newEnrollment['EnrollmentType']['id'] != $nacin_studiranja) {
			$enrollmentTypes = api_call("enrollment/type")["results"];
			$show['nacin_studiranja'] = true;
			$finish = false;
		} else
			$show['nacin_studiranja'] = false;
			
		if (param('change')) {
			$show['whichTime'] = true;
			$finish = false;
		} else
			$show['whichTime'] = false;
			
			
			// Show main form
		unset($_POST['change']); unset($_REQUEST['change']); unset($_POST['ponovac']); unset($_REQUEST['ponovac']);
		?>
		<?=genform("POST");?>
		<h2>Podaci o upisu</h2>
		<style>
			  table.podaci { border: 1px solid #808080; border-collapse: collapse; width: 800px }
             .podaci tr td { border: 1px solid #808080; border-collapse: collapse; border-spacing: 10px; padding: 10px }
		</style>
		<table class="podaci" id="podaciid">
			<tr>
				<td><b>Student</b></td>
				<td><?=$newEnrollment['student']['surname'] . " " . $newEnrollment['student']['name'] . " (" . $newEnrollment['student']['studentIdNr'] . ")";
					?></td>
			</tr>
			<tr>
				<td><b>Akademska godina</b></td>
				<? if ($show['year']) {
					?>
					<td bgcolor="#ffeeee">
						<select name="godina">
							<?
							foreach ($years as $id => $academicYear) {
								print "<option value=\"$id\" ";
								if ($id == $newEnrollment['AcademicYear']['id']) print "SELECTED";
								print ">" . $academicYear['name'] . "</option>";
							}
							?>
						</select>
					</td>
					<?
				} else {
					?><td><?=$newEnrollment['AcademicYear']['name']?> <?
					if (!$newEnrollment['AcademicYear']['isCurrent'])
						print "- <font color='red'>Godina nije aktuelna</font>";
					?></td><?
				}?>
			</tr>
			<tr>
				<td><b>Ciklus studija</b></td>
				<? if ($show['programme']) {
					?>
					<td bgcolor="#ffeeee">
						<select name="ciklus" id="cycle" onchange="changeCycle();">
							<?
							foreach ($programmeTypes as $id => $programmeType)
								print "<option value=$id>" . $programmeType['name'] . "</option>";
							?>
						</select>
					</td>
					<?
				} else {
					?>
					<td>
						<?=$newEnrollment['Programme']['ProgrammeType']['name']?>
						<input type="hidden" name="ciklus" value="<?=$newEnrollment['Programme']['ProgrammeType']['id']?>">
					</td><?
				}?>
			</tr>
			<tr>
				<td><b>Studij</b></td>
				<? if ($show['programme']) {
					?>
				<td bgcolor="#ffeeee">
					<select name="studij" id="programme" onchange="changeProgramme();">
					</select>
				</td>
					<?
				} else {
				?>
				<td>
					<?=$newEnrollment['Programme']['name']?>
					<input type="hidden" name="studij" value="<?=$newEnrollment['Programme']['id']?>">
				</td><?
				}?>
			</tr>
			<tr>
				<td><b>Plan studija</b></td>
				<? if ($show['curriculum']) {
					?>
				<td bgcolor="#ffeeee">
					<select name="plan_studija" id="curriculum">
						<option value="0" selected>(odredi automatski)</option>
				<?
					// Not implemented currently
					foreach($curricula as $cur) {
						print "<option value=\"" . $cur['id'] . "\">" . $cur['startingYear']['name'] . "</option>";
					}
					?>
					</select></td><?
				} else {
					?>
				<td>
					<?=$newEnrollment['Curriculum']['startingYear']['name'];?>
					<input type="hidden" name="plan_studija" value="<?=$newEnrollment['Curriculum']['id']?>">
				</td><?
				}?>
			</tr>
			<tr>
				<td><b>Semestar</b></td>
				<? if ($show['semester']) {
					?>
					<td bgcolor="#ffeeee">
						<select name="semestar">
						<?
						for ($i=1; $i<=6; $i++) {
							print "<option value=$i";
							if ($i == $newEnrollment['semester']) print " selected";
							print ">$i</option>\n";
						}
						?>
						</select>
					</td>
					<?
				} else {
					?>
					<td>
						<?=$newEnrollment['semester'];?>
						<input type="hidden" name="semestar" value="<?=$newEnrollment['semester']?>">
					</td><?
				}?>
			</tr>
			<tr>
				<td><b>Koji put upisuje</b></td>
				<? if ($show['whichTime']) {
					?>
					<td bgcolor="#ffeeee">
						<select name="put">
							<?
							for ($i=1; $i<=20; $i++) {
								print "<option value=$i";
								if ($i == $newEnrollment['whichTime']) print " selected";
								print ">$i</option>\n";
							}
							?>
						</select>
					</td>
					<?
				} else {
					?>
					<td>
					<?=$newEnrollment['whichTime'];?>
					<input type="hidden" name="put" value="<?=$newEnrollment['whichTime']?>">
					</td><?
				}?>
			</tr>
			<tr>
				<td><b>Ponovac</b></td>
				<? if ($show['repeat']) {
						?>
					<td bgcolor="#ffeeee">
						<input type="checkbox" name="ponovac" <? if ($newEnrollment['repeat']) print "checked";?>>
					</td>
						<?
					} else {
						if ($newEnrollment['repeat']) {
							print "<td>DA";
							?>
				<input type="hidden" name="ponovac" value="1"></td><?
						} else
							print "<td>NE</td>";
					}?>
			</tr>
			<tr>
				<td><b>Apsolvent</b></td>
				<? if ($show['status']) {
					?>
					<td bgcolor="#ffeeee">
						<input type="checkbox" name="status" value="1">
					</td>
						<?
					} else {
					?>
					<td><?
						if ($newEnrollment['status'] == 1) print "DA"; else print "NE";
						?>
						<input type="hidden" name="status" value="<?=$newEnrollment['status']?>">
					</td><?
					}?>
			</tr>
			<tr>
				<td><b>Način studiranja</b></td>
				<? if ($show['nacin_studiranja']) {
					?>
					<td bgcolor="#ffeeee">
						<select name="nacin_studiranja">
							<?
							foreach ($enrollmentTypes as $type) {
								print "<option value=\"" . $type['id'] . "\"";
								if ($type['id'] == $newEnrollment['EnrollmentType']['id']) print "SELECTED";
								print ">" . $type['name'] . "</option>";
							}
							?>
						</select>
					</td>
					<?
				} else {
					?>
					<td>
						<?=$newEnrollment['EnrollmentType']['name'];?>
						<input type="hidden" name="nacin_studiranja" value="<?=$newEnrollment['EnrollmentType']['id']?>">
					</td><?
				}?>
			</tr>
			<tr>
				<td><b>Zaduženje</b></td>
				<td><input type="text" name="zaduzenje" value="<?=-$balance['amount']?>"> KM</td>
			</tr>
			<?
			if (!$newEnrollment['canEnroll']) {
				?>
				<tr>
					<td>&nbsp;</td>
					<td bgcolor="#ffeeee">
						<p><b>Student ne ispunjava uslove za upis</b></p>
						<p>Nepoloženi predmeti:</p>
						<ul>
							<?
							$totalECTS = 0;
							if (is_array($newEnrollment['failedCourses'])) foreach ($newEnrollment['failedCourses'] as $co) {
								print "<li>" . $co['CourseDescription']['name'] . " (" . $co['CourseDescription']['ects'] . " ECTS)</li>\n";
								$totalECTS += $co['CourseDescription']['ects'];
							}
							?>
						</ul>
						<p>Ukupno <?=count($newEnrollment['failedCourses'])?> predmeta (<?=$totalECTS?> ECTS)</p>
					</td>
				</tr>
			<?
			}
			?>
			<tr>
				<td>&nbsp;</td>
				<td>
					<input type="submit" name="change" value="Promijeni sve podatke">
					<?
					if (!$finish) {
						?><input type="submit" name="confirm" value="Potvrdi izmjene"><?
						//print_r($show);
					}
					if ($finish || $user_siteadmin) {
						?><input type="submit" name="finish" value="Upiši studenta"><?
					}
					?>
				</td>
			</tr>
		</table>
		</form>
		<?
	}


// Ispis sa studija

else if ($akcija == "ispis") {

	// Svi parametri su obavezni!
	$studij = $_REQUEST['studij'];
	$semestar = $_REQUEST['semestar'];
	$ak_god = $_REQUEST['godina'];

	$q2500 = db_query("select ime, prezime from osoba where id=$osoba");
	$ime = db_result($q2500,0,0);
	$prezime = db_result($q2500,0,1);

	$q2510 = db_query("select naziv from akademska_godina where id=$ak_god");
	$naziv_ak_god = db_result($q2510,0,0);

	?>
	<a href="?sta=studentska/osobe&akcija=edit&osoba=<?=$osoba?>">Nazad na podatke o osobi</a><br/><br/>
	<h2><?=$ime?> <?=$prezime?> - ispis sa studija</h2>
	<?

	// Gdje je trenutno upisan?
	$q2520 = db_query("select s.id, s.naziv, ss.semestar from studij as s, student_studij as ss where ss.student=$osoba and ss.studij=s.id and ss.akademska_godina=$ak_god and ss.semestar=$semestar");
	if (db_num_rows($q2520)<1) {
		niceerror("Student nije upisan na fakultet u izabranoj akademskoj godini!");
		zamgerlog("pokusao ispisati studenta u$osoba koji nije upisan u ag$ak_god", 3);
		zamgerlog2("pokusao ispisati studenta koji nije upisan", $osoba, intval($ak_god));
		return;
	}
	if (db_result($q2520,0,0)!=$studij) {
		niceerror("Student nije upisan na izabrani studij u izabranoj akademskoj godini!");
		zamgerlog("pokusao ispisati studenta u$osoba sa studija $studij koji ne slusa u ag$ak_god", 3);
		zamgerlog2("pokusao ispisati studenta sa studija koji ne slusa", $osoba, intval($studij), intval($ak_god));
		return;
	}
	if (db_result($q2520,0,2)!=$semestar) {
		niceerror("Student nije upisan na izabrani semestar u izabranoj akademskoj godini!");
		zamgerlog("pokusao ispisati studenta u$osoba sa semestra $semestar koji ne slusa u ag$ak_god", 3);
		zamgerlog2("pokusao ispisati studenta sa semestra koji ne slusa", $osoba, intval($semestar), intval($ak_god));
		return;
	}
	$naziv_studija = db_result($q2520,0,1);

	$zimski_ljetnji = $semestar%2;

	?>
	<h3>Studij: <?=$naziv_studija?>, <?=$semestar?>. semestar, <?=$naziv_ak_god?> godina</h3>
	<?

	// Ispis sa studija
	if ($_REQUEST['potvrda']=="1") {
		$q530 = db_query("select pk.predmet from student_predmet as sp, ponudakursa as pk where sp.student=$osoba and sp.predmet=pk.id and pk.akademska_godina=$ak_god and pk.semestar mod 2=$zimski_ljetnji");
		while ($r530 = db_fetch_row($q530)) {
			$predmet = $r530[0];
			ispis_studenta_sa_predmeta($osoba, $predmet, $ak_god);
			zamgerlog("ispisujem studenta u$osoba sa predmeta pp$predmet (ispis sa studija)",4); // 4 - audit
			zamgerlog2("student ispisan sa predmeta (ispis sa studija)", $osoba, intval($predmet), intval($ak_god));
		}
		$q550 = db_query("delete from student_studij where student=$osoba and akademska_godina=$ak_god and semestar=$semestar");
		nicemessage("Ispisujem studenta sa studija $naziv_studija i svih predmeta koje trenutno sluša.");
		zamgerlog("ispisujem studenta u$osoba sa studija $naziv_studija (ag$ak_god)", 4); // 4 - audit
		zamgerlog2("student ispisan sa studija", $osoba, intval($ak_god));
	} else {
		?>
		<p>Student će biti ispisan sa sljedećih predmeta:<ul>
		<?
		$q520 = db_query("select p.naziv from predmet as p, ponudakursa as pk, student_predmet as sp where sp.student=$osoba and sp.predmet=pk.id and pk.akademska_godina=$ak_god and pk.predmet=p.id and pk.semestar mod 2=$zimski_ljetnji");
		while ($r520 = db_fetch_row($q520)) {
			print "<li>$r520[0]</li>\n";
		}
		?>
		</ul></p>
		<p>NAPOMENA: Svi bodovi ostvareni na ovim predmetima će biti izgubljeni! Trenutno nema drugog načina da se student ispiše sa studija.</p>
		<p>Kliknite na dugme "Potvrda" da potvrdite ispis.</p>
		<?=genform("POST");?>
		<input type="hidden" name="potvrda" value="1">
		<input type="submit" value=" Potvrda ">
		</form>
		<?
	}
}


// Promjena načina studiranja

else if ($akcija == "promijeni_nacin") {
	$studij = intval($_REQUEST['studij']);
	$semestar = intval($_REQUEST['semestar']);
	$godina = intval($_REQUEST['godina']);

	$q10 = db_query("SELECT ime, prezime, brindexa FROM osoba WHERE id=$osoba");
	$ime = db_result($q10,0,0);
	$prezime = db_result($q10,0,1);
	$brindexa = db_result($q10,0,2);
	
	$q20 = db_query("SELECT nacin_studiranja FROM student_studij WHERE student=$osoba AND studij=$studij AND semestar=$semestar AND akademska_godina=$godina");
	if (db_num_rows($q20)<1) {
		niceerror("Greška");
		return;
	}
	$nacin = db_result($q20,0,0);

	if ($_REQUEST['subakcija'] == "mijenjaj") {
		$nacin = intval($_REQUEST['_lv_column_nacin_studiranja']);
		$q50 = db_query("UPDATE student_studij SET nacin_studiranja=$nacin WHERE student=$osoba AND studij=$studij AND semestar=$semestar AND akademska_godina=$godina");
		nicemessage("Promijenjen način studiranja za studenta $ime $prezime");
		print "<p><a href=\"?sta=studentska/osobe&amp;akcija=edit&amp;osoba=$osoba\">Nazad na podatke o studentu $ime $prezime</a></p>\n";
		zamgerlog("promijenjen nacin studiranja za u$osoba na $nacin", 4);
		zamgerlog2("promijenjen nacin studiranja", $osoba, $nacin);
		return;
	}

	// Podaci za ispis na ekran	
	$q30 = db_query("SELECT naziv FROM studij WHERE id=$studij");
	$naziv_studija = db_result($q30,0,0);
	$q40 = db_query("SELECT naziv FROM akademska_godina WHERE id=$godina");
	$naziv_godine = db_result($q40,0,0);
	
	
	?>
	<a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=edit">Nazad na podatke o studentu</a><br/><br/>
	
	<h3><?=$ime?> <?=$prezime?> (<?=$brindexa?>)</h3>
	
	<p>Način studiranja na: <b><?=$naziv_studija?>, <?=$naziv_godine?>, <?=$semestar?>. semestar</b></p>
	<?=genform("POST")?>
	<input type="hidden" name="subakcija" value="mijenjaj">
	<?=db_dropdown("nacin_studiranja",$nacin) ?><br>
	<input type="submit" value=" Promijeni ">
	</form>
	<?
}



// Pregled predmeta za koliziju i potvrda

else if ($akcija == "kolizija") {
	?>
	<a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=edit">Nazad na podatke o studentu</a><br/><br/>
	<?

	// Odredjujemo u koju akademsku godinu bi se trebao upisivati student
	$nova_ak_god=intval($_REQUEST['godina']);
	$q398 = db_query("select naziv from akademska_godina where id=$nova_ak_god");
	$naziv_godine=db_result($q398,0,0);

	// Koji studij student sluša? Treba nam radi ponudekursa
	$q399 = db_query("select s.id, s.naziv from student_studij as ss, studij as s where ss.student=$osoba and ss.studij=s.id order by ss.akademska_godina desc, ss.semestar desc");
	$studij = db_result($q399,0,0);
	$studij_naziv = db_result($q399,0,1);
	
	$q400 = db_query("select predmet from kolizija where student=$osoba and akademska_godina=$nova_ak_god");
	$predmeti=$ponudekursa=array();
	$greska=0;
	while ($r400 = db_fetch_row($q400)) {
		$predmet = $r400[0];

		// Eliminišemo predmete koje je položio u međuvremenu
		$q410 = db_query("select count(*) from konacna_ocjena where student=$osoba and predmet=$predmet and ocjena>5");
		if (db_result($q410,0,0)<1) {
			$q420 = db_query("select naziv from predmet where id=$predmet");
			$predmeti[$predmet] = "<b>".db_result($q420,0,0)."</b> ($studij_naziv, ";

			// Odredjujemo ponudu kursa koju bi student trebao slušati
			$q430 = db_query("select id, semestar, obavezan from ponudakursa where predmet=$predmet and studij=$studij and akademska_godina=$nova_ak_god");
			if (db_num_rows($q430)<1) {
				if ($greska==0) niceerror("Nije pronađena ponuda kursa");
				print "Predmet <b>".db_result($q420,0,0)."</b>, studij <b>$studij_naziv</b>, godina $naziv_godine<br/>";
				$greska=1;
			}
			$ponudekursa[$predmet] = db_result($q430,0,0);
			$predmeti[$predmet] .= db_result($q430,0,1).". semestar";
			if (db_result($q430,0,2)==0) $predmeti[$predmet] .= ", izborni";
			$predmeti[$predmet] .= ")";
		}
	}

	if ($greska==1) return; // ne idemo dalje

	if (count($predmeti)==0) { // nema ništa za koliziju!!!
		nicemessage ("Student je u međuvremenu položio/la sve predmete! Nema se ništa za upisati.");
		return;
	}
	

	if ($_REQUEST['subakcija'] == "potvrda") {
		foreach ($ponudekursa as $predmet => $pk) {
			upis_studenta_na_predmet($osoba, $pk);
			$q440 = db_query("delete from kolizija where student=$osoba and akademska_godina=$nova_ak_god and predmet=$predmet");
			zamgerlog2("student upisan na predmet (kolizija)", $osoba, intval($pk));
		}
		zamgerlog("prihvacen zahtjev za koliziju studenta u$osoba", 4); // 4 = audit
		zamgerlog2("prihvacen zahtjev za koliziju", $osoba);
		print "<p>Upis je potvrđen.</p>\n";
	} else {
		?>
		<p>Student želi upisati sljedeće predmete:</p>
		<ul>
		<?
		foreach ($predmeti as $tekst) {
			print "<li>$tekst</li>\n";
		}
		?>
		</ul>
		<?=genform("POST");?>
		<input type="hidden" name="subakcija" value="potvrda">
		<input type="submit" value=" Potvrdi ">
		</form>
		<?
	}
}



// Manuelni upis/ispis na predmete

else if ($akcija == "predmeti") {
	?>
	<a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=edit">Nazad na podatke o studentu</a><br/><br/>
	<?

	// Parametar "spisak" određuje koji predmeti će biti prikazani
	$spisak = intval($_REQUEST['spisak']);

	$q2000 = db_query("select ime, prezime from osoba where id=$osoba");
	if (db_num_rows($q2000)<1) {
		niceerror("Nepoznata osoba $osoba");
		return;
	}
	$ime = db_result($q2000,0,0);
	$prezime = db_result($q2000,0,1);

	?>
	<h2><?=$ime?> <?=$prezime?> - upis/ispis na predmete</h2>
	<?


	// Subakcije: upis i ispis sa predmeta

	if ($_REQUEST['subakcija']=="upisi") {
		$ponudakursa = intval($_REQUEST['ponudakursa']);
		upis_studenta_na_predmet($osoba, $ponudakursa);

		$q2200 = db_query("select p.naziv from ponudakursa as pk, predmet as p where pk.id=$ponudakursa and pk.predmet=p.id");
		$naziv_predmeta = db_result($q2200,0,0);

		nicemessage("Student upisan na predmet $naziv_predmeta");
		zamgerlog("student u$osoba manuelno upisan na predmet p$ponudakursa", 4); // 4 - audit
		zamgerlog2("student upisan na predmet (manuelno)", $osoba, $ponudakursa);
	}

	if ($_REQUEST['subakcija']=="ispisi") {
		$ponudakursa = intval($_REQUEST['ponudakursa']);
		$q2200 = db_query("select p.id, p.naziv, pk.akademska_godina from ponudakursa as pk, predmet as p where pk.id=$ponudakursa and pk.predmet=p.id");
		$predmet = db_result($q2200,0,0);
		$naziv_predmeta = db_result($q2200,0,1);
		$ag = db_result($q2200,0,2);

		// Upozorenje ako ima neke bodove?
		$q2210 = db_query("select sum(bodovi) from komponentebodovi where student=$osoba and predmet=$ponudakursa");
		$bodovi = db_result($q2210,0,0);
		if ($bodovi!=0 && $bodovi!=10 && $_REQUEST['siguran']!="da") { // 10 bodova je default za prisustvo
			nicemessage("Upozorenje! Student je osvojio $bodovi bodova na predmetu $naziv_predmeta.");
			?>Da li ste sigurni da ga želite ispisati?<br/>
			<?=genform("POST");?>
			<input type="hidden" name="siguran" value="da">
			<input type="submit" value=" Potvrda ">
			</form>
			<?
			return;
		}

		ispis_studenta_sa_predmeta($osoba, $predmet, $ag);

		nicemessage("Student ispisan sa predmeta $naziv_predmeta");
		zamgerlog("student u$osoba manuelno ispisan sa predmeta p$ponudakursa", 4); // 4 - audit
		zamgerlog2("student ispisan sa predmeta (manuelno)", $osoba, intval($predmet), intval($ag));
	}



	// Aktuelna akademska godina

	if ($_REQUEST['ag'] || $_REQUEST['_lv_column_akademska_godina']) {
		$ak_god = intval($_REQUEST['ag']);
		if ($_REQUEST['_lv_column_akademska_godina']) $ak_god = intval($_REQUEST['_lv_column_akademska_godina']);
		$q2005 = db_query("select naziv from akademska_godina where id=$ak_god");
		if (db_num_rows($q2005)<1) {
			biguglyerror("Nepoznata akademska godina");
			return;
		}
		$naziv_ag = db_result($q2005,0,0);
	} else {
		$q2010 = db_query("select id, naziv from akademska_godina where aktuelna=1");
		$ak_god = db_result($q2010,0,0);
		$naziv_ag = db_result($q2010,0,1);
	}

	$q2020 = db_query("select studij, semestar, plan_studija from student_studij where student=$osoba and akademska_godina=$ak_god order by semestar desc");
	if (db_num_rows($q2020)>0) {
		$studij = db_result($q2020,0,0);
		$semestar = db_result($q2020,0,1);

		$q2025 = db_query("select naziv from studij where id=$studij");
		$naziv_studija = db_result($q2025,0,0);

		print "<p>Student trenutno ($naziv_ag) upisan na $naziv_studija, $semestar. semestar.</p>\n";

		// Upozorenje!
		if (db_result($q2020,0,2)>0) {
			print "<p><b>Napomena:</b> Student je već upisan na sve predmete koje je trebao slušati po odabranom planu studija!<br/> Koristite ovu opciju samo za izuzetke / odstupanja od plana ili u slučaju grešaka u radu Zamgera.<br/>U suprotnom, može se desiti da student nema adekvatan broj ECTS kredita ili da sluša izborni predmet<br/>koji ne bi smio slušati.</p>\n";
		}

	} else {
		// Student trenutno nije upisan nigdje... biramo zadnji studij koji je slušao
		if ($spisak==0) $spisak=1;
		$q2030 = db_query("select studij, semestar, akademska_godina from student_studij where student=$osoba order by akademska_godina desc limit 1");
		if (db_num_rows($q2030)>0) {
			$studij = db_result($q2030,0,0);
			$ag_studija = db_result($q2030,0,2);

			$q2040 = db_query("select naziv from studij where id=$studij");
			$naziv_studija = db_result($q2040,0,0);

			$q2050 = db_query("select naziv from akademska_godina where id=$ag_studija");

			if ($ag_studija > $ak_god) {
				print "<p>Student nije bio upisan u odabranoj akademskoj godini ($naziv_ag), ali je upisan na studij $naziv_studija, ".db_result($q2030,0,0).". semestar, akademske ".db_result($q2050,0,0)." godine.</p>\n";
			} else {
				print "<p>Student trenutno ($naziv_ag) nije upisan na fakultet! Posljednji put slušao $naziv_studija, ".db_result($q2030,0,0).". semestar, akademske ".db_result($q2050,0,0)." godine.</p>\n";
			}
		} else {
			// Nikada nije bio student?
			$studij=0;
			if ($spisak<2) $spisak=2;
			print "<p>Osoba nikada nije bila naš student!</p>\n";
		}
	}

	// Opcije za spisak predmeta
	$s0 = ($spisak==0) ? "CHECKED" : "";
	$s1 = ($spisak==1) ? "CHECKED" : "";
	$s2 = ($spisak==2) ? "CHECKED" : "";

	unset($_REQUEST['subakcija']); // da se ne bi ponovila

	?>
	<?=genform("GET");?>
	Akademska godina: <?=db_dropdown("akademska_godina", $ak_god);?><br>
	<input type="radio" name="spisak" value="0" <?=$s0?>> Prikaži predmete sa izabranog studija i semestra<br/>
	<input type="radio" name="spisak" value="1" <?=$s1?>> Prikaži predmete sa svih semestara<br/>
	<input type="radio" name="spisak" value="2" <?=$s2?>> Prikaži predmete sa drugih studija<br/>
	<input type="submit" value=" Kreni "></form><br><br>
	<?


	// Ispis svih predmeta na studiju semestru je funkcija, pošto pozivanje unutar petlje ovisi o nivou spiska

	function dajpredmete($studij, $semestar, $student, $ag, $spisak) {
		$q2100 = db_query("SELECT pk.id, p.id, p.naziv, pk.obavezan, pp.naziv, pk.pasos_predmeta
			FROM ponudakursa AS pk, predmet AS p, akademska_godina_predmet agp
			LEFT JOIN pasos_predmeta as pp ON pp.id=agp.pasos_predmeta
			WHERE pk.studij=$studij AND pk.semestar=$semestar AND pk.akademska_godina=$ag AND pk.predmet=p.id AND agp.predmet=p.id AND agp.akademska_godina=$ag
			ORDER BY p.naziv");
		while ($r2100 = db_fetch_row($q2100)) {
			$ponudakursa = $r2100[0];
			$predmet = $r2100[1];
			$predmet_naziv = $r2100[2];
			if ($r2100[5])
				$predmet_naziv = db_get("SELECT naziv FROM pasos_predmeta WHERE id=" . $r2100[5]);
			else if ($r2100[4]) $predmet_naziv = $r2100[4];
			print "<li>$predmet_naziv";
			if ($r2100[3]!=1) print " (izborni)";

			// Da li je upisan?
			// Zbog mogućih bugova, prvo gledamo da li je upisan...
			$q2120 = db_query("select count(*) from student_predmet where student=$student and predmet=$ponudakursa");
			if (db_result($q2120,0,0)>0) {
				print " - <a href=\"?sta=studentska/osobe&akcija=predmeti&osoba=$student&subakcija=ispisi&ponudakursa=$ponudakursa&spisak=$spisak&ag=$ag\">ispiši</a></li>\n";

			} else {
				// Da li je položen?
				$q2110 = db_query("select count(*) from konacna_ocjena where student=$student and predmet=$predmet and ocjena>5");
				if (db_result($q2110,0,0)>0) {
					print " - položen</li>\n";

				} else {
					print " - <a href=\"?sta=studentska/osobe&akcija=predmeti&osoba=$student&subakcija=upisi&ponudakursa=$ponudakursa&spisak=$spisak&ag=$ag\">upiši</a></li>\n";
				}
			}
		}
	} // function dajpredmete


	// Ispis predmeta

	if ($spisak==0) {
		print "<b>$naziv_studija, $semestar. semestar</b>\n<ul>\n";
		dajpredmete($studij, $semestar, $osoba, $ak_god, $spisak);
		print "</ul>\n";
	}

	else if ($spisak==1) {
		// Broj semestara?
		$q2060 = db_query("select ts.trajanje from studij as s, tipstudija as ts where s.id=$studij and s.tipstudija=ts.id");
		for ($s=1; $s<=db_result($q2060,0,0); $s++) {
			if ($s==$semestar) print "<b>$naziv_studija, $s. semestar</b>\n<ul>\n";
			else print "$naziv_studija, $s. semestar\n<ul>\n";
			dajpredmete($studij, $s, $osoba, $ak_god, $spisak);
			print "</ul>\n";
		}
	}

	else if ($spisak==2) {
		// Svi studiji
		$q2070 = db_query("select s.id, s.naziv, ts.trajanje from studij as s, tipstudija as ts where s.tipstudija=ts.id and s.moguc_upis=1 order by ts.ciklus, s.naziv");
		while ($r2070=db_fetch_row($q2070)) {
			$stud=$r2070[0];
			$stud_naziv=$r2070[1];
			$stud_trajanje=$r2070[2];

			if ($stud==$studij) print "<b>$stud_naziv</b>\n<ul>\n";
			else print "$stud_naziv\n<ul>\n";

			for ($s=1; $s<=$stud_trajanje; $s++) {
				if ($stud==$studij && $s==$semestar) print "<b>$s. semestar</b>\n<ul>\n";
				else print "$s. semestar\n<ul>\n";
				dajpredmete($stud, $s, $osoba, $ak_god, $spisak);
				print "</ul>\n";
			}
			print "</ul>\n";
		}
	}
}


// Izbori za nastavnike

else if ($akcija == "izbori") {
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


// Analitička kartica studenta

else if ($akcija == "kartica") {
	?>
	<a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=edit">Nazad na podatke o studentu</a><br/><br/>
	<?

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
	
	global $conf_url_daj_karticu;
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


// Ažuriranje dugovanja studenta

else if ($akcija == "izmijeni_zaduzenje") {
	$zaduzenje = db_get("SELECT zaduzenje FROM student_zaduzenje WHERE student=$osoba");
	if (param('subakcija') == "potvrda" && check_csrf_token()) {
		$novo_zaduzenje = floatval($_REQUEST['zaduzenje']);
		if ($novo_zaduzenje == 0 && $zaduzenje != 0)
			db_query("DELETE FROM student_zaduzenje WHERE student=$osoba");
		else if ($zaduzenje === false && $novo_zaduzenje != 0)
			db_query("INSERT INTO student_zaduzenje SET student=$osoba, zaduzenje=$novo_zaduzenje");
		else
			db_query("UPDATE student_zaduzenje SET zaduzenje=$novo_zaduzenje WHERE student=$osoba");

		nicemessage("Ažurirano zaduženje za studenta " . db_get("SELECT CONCAT(ime, ' ', prezime) FROM osoba WHERE id=$osoba"));
		?>
		<a href="?sta=studentska/osobe&amp;akcija=edit&amp;osoba=<?=$osoba?>">Nazad na podatke o studentu</a>
		<?
		return;
	}

	?>
	<h2><?=db_get("SELECT CONCAT(ime, ' ', prezime) FROM osoba WHERE id=$osoba")?></h2>
	<?=genform("POST")?>
	<input type="hidden" name="subakcija" value="potvrda">
	Zaduženje: <input type="text" name="zaduzenje" value="<?=$zaduzenje?>">
	<input type="submit" value="Izmijeni">
	</form>
	<a href="?sta=studentska/osobe&amp;akcija=edit&amp;osoba=<?=$osoba?>">Nazad</a>
	<?
	return;
}


// Pregled informacija o osobi

else if ($akcija == "edit") {
	$pretraga = db_escape(param('search'));
	$ofset = int_param('offset');

	?><a href="?sta=studentska/osobe&search=<?=$pretraga?>&offset=<?=$ofset?>">Nazad na rezultate pretrage</a><br/><br/><?
	

	// Prvo odredjujemo aktuelnu akademsku godinu - ovaj upit se dosta koristi kasnije
	$q210 = db_query("select id,naziv from akademska_godina where aktuelna=1 order by id desc");
	if (db_num_rows($q210)<1) {
		// Nijedna godina nije aktuelna - ali mora postojati barem jedna u bazi
		$q210 = db_query("select id,naziv from akademska_godina order by id desc");
	}
	$id_ak_god = db_result($q210,0,0);
	$naziv_ak_god = db_result($q210,0,1);
	// Posto se id_ak_god moze promijeniti.... CLEANUP!!!
	$orig_iag = $id_ak_god;



	// ======= SUBMIT AKCIJE =========


	// Promjena korisničkog pristupa i pristupnih podataka
	if (param('subakcija') == "auth" && check_csrf_token()) {
		$login = db_escape(trim($_REQUEST['login']));
		$login_ldap = zamger_ldap_escape(trim($_REQUEST['login']));
		$stari_login = db_escape($_REQUEST['stari_login']);
		$password = db_escape($_REQUEST['password']);
		$aktivan = intval($_REQUEST['aktivan']);

		if ($login=="") {
			niceerror("Ne možete postaviti prazan login");
		}
		else if ($stari_login=="") {
			// Provjeravamo LDAP?
			if ($conf_ldap_search) do { // Simuliramo GOTO...
				// Tražimo ovaj login na LDAPu...
				$ds = ldap_connect($conf_ldap_server);
				ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
				if (!ldap_bind($ds)) {
					zamgerlog("Ne mogu se spojiti na LDAP server",3); // 3 - greska
					zamgerlog2("ne mogu se spojiti na LDAP server");
					niceerror("Ne mogu se spojiti na LDAP server - nastavljam dalje bez provjere");
					break;
				}

				$sr = ldap_search($ds, $conf_ldap_dn, "uid=$login_ldap", array() /* just dn */ );
				if (!$sr) {
					zamgerlog("ldap_search() nije uspio.",3);
					zamgerlog2("ldap_search() nije uspio.");
					niceerror("ldap_search() nije uspio - nastavljam dalje bez provjere");
					break;
				} 
				$results = ldap_get_entries($ds, $sr);
				if ($results['count'] > 0) {
					nicemessage("Login '$login' pronađen na LDAP serveru");
					break;
				}

				// Pokušavamo mail alias
				$sr = ldap_search($ds, $conf_ldap_dn, "mail=$login_ldap$conf_ldap_domain", array() );
				if (!$sr) {
					zamgerlog("ldap_search() 2 nije uspio.",3);
					zamgerlog2("ldap_search() nije uspio.");
					niceerror("ldap_search() nije uspio - nastavljam dalje bez provjere");
					break;
				} 
				$results = ldap_get_entries($ds, $sr); // pretpostavka je da će druga pretraga raditi
				if ($results['count'] > 0) {
					nicemessage("Email '$login$conf_ldap_domain' pronađen na LDAP serveru");
				} else {
					zamgerlog("login ne postoji na LDAPu ($login)",3);
					zamgerlog2("login ne postoji na LDAPu", 0, 0, 0, $login);
					niceerror("Predloženi login ($login) nije pronađen na LDAP serveru!");
					print "<p>Nastaviću dalje sa dodavanjem logina, ali korisnik vjerovatno neće moći pristupiti Zamgeru.</p>";
				}
			} while (false);

			// Dodavanje novog logina
			$q120 = db_query("insert into auth set id=$osoba, login='$login', password='$password', aktivan=$aktivan");
			nicemessage("Uspješno kreiran novi login za korisnika");
			zamgerlog("dodan novi login '$login' za korisnika u$osoba", 4);
			zamgerlog2("dodan novi login za korisnika", $osoba, 0, 0, $login);

		} else {
			// Izmjena starog logina
			$q123 = db_query("select count(*) from auth where id=$osoba and login='$stari_login'");
			if (db_result($q123,0,0)<1) {
				niceerror("Nije pronađen login... molimo pokušajte ponovo");
				zamgerlog("nije pronadjen stari login '$stari_login' za korisnika u$osoba", 3);
				zamgerlog2("nije pronadjen stari login za korisnika", $osoba);
			} else {
				if ($_REQUEST['brisanje']==" Obriši ") {
					$q125 = db_query("delete from auth where id=$osoba and login='$stari_login'");
					nicemessage("Uspješno obrisan login '$stari_login'");
					zamgerlog("obrisan login '$stari_login' za korisnika u$osoba", 4);
					zamgerlog2("obrisan login za korisnika", $osoba, 0, 0, $stari_login);

				} else {
					$q127 = db_query("update auth set login='$login', password='$password', aktivan=$aktivan where id=$osoba and login='$stari_login'");
					nicemessage("Uspješno izmijenjen login '$login'");
					zamgerlog("izmijenjen login '$stari_login' u '$login' za korisnika u$osoba", 4);
					zamgerlog2("izmijenjen login za korisnika", $osoba, 0, 0, $login);
				}
			}
		}


	} // if ($_REQUEST['subakcija'] == "auth")


	// Pojednostavljena promjena podataka za studentsku službu u slučaju korištenja LDAPa
	if (param('subakcija') == "auth_ldap" && check_csrf_token()) {
		$aktivan = intval($_REQUEST['aktivan']);

		// Postoji li zapis u tabeli auth?
		$q103 = db_query("select count(*) from auth where id=$osoba");
		if (db_result($q103,0,0)>0) { // Da!
			// Ako isključujemo pristup, stavljamo aktivan na 0
			if ($aktivan!=0) {
				$q105 = db_query("update auth set aktivan=0 where id=$osoba");
				zamgerlog("ukinut login za korisnika u$osoba (ldap)",4);
				zamgerlog2("ukinut login za korisnika (ldap)", $osoba );
			} else {
				$q105 = db_query("update auth set aktivan=1 where id=$osoba");
				zamgerlog("aktiviran login za korisnika u$osoba (ldap)",4);
				zamgerlog2("aktiviran login za korisnika (ldap)", $osoba );
			}

		} else if ($aktivan!=0) { // Nema zapisa u tabeli auth
			// TODO: smanjiti duplikaciju koda za LDAP!
			
			// Ako je izabrano isključenje pristupa, ne radimo nista
			// (ne bi se smjelo desiti)
			// U suprotnom kreiramo login

			// predloženi login
			$suggest_login = gen_ldap_uid($osoba);
	
			// Tražimo ovaj login na LDAPu...
			$ds = ldap_connect($conf_ldap_server);
			ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
			if (!ldap_bind($ds)) {
				zamgerlog("Ne mogu se spojiti na LDAP server",3); // 3 - greska
				zamgerlog2("ne mogu se spojiti na LDAP server");
				niceerror("Ne mogu se spojiti na LDAP server");
				return;
			}
	
			$sr = ldap_search($ds, $conf_ldap_dn, "uid=$suggest_login", array() /* just dn */ );
			if (!$sr) {
				zamgerlog("ldap_search() nije uspio.",3);
				zamgerlog2("ldap_search() nije uspio.");
				niceerror("ldap_search() nije uspio.");
				return;
			}
			$results = ldap_get_entries($ds, $sr);
			if ($results['count'] < 1) {
				zamgerlog("login ne postoji na LDAPu ($suggest_login)",3);
				zamgerlog2("login ne postoji na LDAPu", 0, 0, 0, $suggest_login);
				niceerror("Predloženi login ($suggest_login) nije pronađen na LDAP serveru!");
				print "<p>Da li ste uspravno unijeli broj indeksa, ime i prezime? Ako jeste, kontaktirajte administratora!</p>";
	
				// Nastavljamo dalje sa edit akcijom kako bi studentska mogla popraviti podatke
	
			} else {
				// Dodajemo login, ako nije podešen
				$q110 = db_query("select login, aktivan from auth where id=$osoba");
				if (db_num_rows($q110)==0) {
					$q111 = db_query("insert into auth set id=$osoba, login='$suggest_login', aktivan=1");
					zamgerlog("kreiran login za korisnika u$osoba (ldap - upis u tabelu)",4);
					zamgerlog2("kreiran login za korisnika (ldap - upis u tabelu)", $osoba);
				}
				else {
					if (db_result($q110,0,0) == "") {
						$q112 = db_query("update auth set login='$suggest_login' where id=$osoba");
						zamgerlog("kreiran login za korisnika u$osoba (ldap - postavljeno polje login)",4);
						zamgerlog2("kreiran login za korisnika (ldap - postavljeno polje login)", $osoba );
					}
					if (db_result($q110,0,1)==0) {
						$q113 = db_query("update auth set aktivan=1 where id=$osoba");
						zamgerlog("kreiran login za korisnika u$osoba (ldap - aktivan=1)",4);
						zamgerlog2("kreiran login za korisnika (ldap - aktivan=1)", $osoba);
					}
				}
	
				// Generišemo email adresu ako nije podešena
				$email_adresa = $suggest_login.$conf_ldap_domain;
				$q115 = db_query("select sistemska from email where osoba=$osoba and adresa='$email_adresa'");
				if (db_num_rows($q115) < 1) {
					$q114 = db_query("insert into email set osoba=$osoba, adresa='$email_adresa', sistemska=1");
					zamgerlog("dodana sistemska email adresa za u$osoba", 2);
					zamgerlog2("sistemska email adresa dodana", $osoba, intval(db_insert_id()), 0, "$email_adresa");
				}
				else if (db_result($q115,0,0) == 0) {
					$q114 = db_query("update email set sistemska=1 where adresa='$email_adresa' and osoba=$osoba");
					zamgerlog("email adresa proglasena za sistemsku za u$osoba", 2);
					zamgerlog2("email adresa proglasena za sistemsku", $osoba, 0, 0, "$email_adresa");
				}
			}
		} // else if ($pristup!=0)

	} // if ($_REQUEST['subakcija'] == "auth")


	// Upis studenta na predmet
	if (param('subakcija') == "upisi" && check_csrf_token()) {

		$predmet = intval($_POST['predmet']);
		if ($predmet==0) {
			nicemessage("Niste izabrali predmet");
		} else {
			$q130 = db_query("select count(*) from student_predmet where student=$osoba and predmet=$predmet");
			if (db_result($q130,0,0)<1) {
				upis_studenta_na_predmet($osoba, $predmet);
				zamgerlog("student u$osoba upisan na predmet p$predmet",4);
				zamgerlog2("student upisan na predmet (manuelno 2)", $osoba, $predmet);
				$q136 = db_query("select p.naziv from predmet as p, ponudakursa as pk where pk.id=$predmet and pk.predmet=p.id");
				$naziv_predmeta = db_result($q136,0,0);
				nicemessage("Student upisan na predmet $naziv_predmeta.");
			}
		}
	}


	// Dodjela prava nastavniku na predmetu
	if (param('subakcija') == "daj_prava" && check_csrf_token()) {

		$predmet = intval($_POST['predmet']);

		$q115 = db_query("select naziv from predmet where id=$predmet");
		$naziv_predmeta = db_result($q115,0,0);

		$q130 = db_query("replace nastavnik_predmet set nastavnik=$osoba, predmet=$predmet, akademska_godina=$id_ak_god, nivo_pristupa='asistent'");

		zamgerlog("nastavniku u$osoba data prava na predmetu pp$predmet (admin: asistent, akademska godina: $id_ak_god)",4);
		zamgerlog2("nastavniku data prava na predmetu", $osoba, $predmet, intval($id_ak_god));
		nicemessage("Nastavniku su dodijeljena prava na predmetu $naziv_predmeta.");
		print "<p>Kliknite na naziv predmeta na spisku ispod kako biste detaljnije podesili privilegije.</p>";
	}


	// Angažman nastavnika na predmetu
	if (param('subakcija') == "angazuj" && check_csrf_token()) {

		$predmet = intval($_POST['predmet']);
		$status = intval($_POST['_lv_column_angazman_status']);
		$angazman_ak_god = intval($_POST['_lv_column_akademska_godina']);

		$q115 = db_query("select naziv from predmet where id=$predmet");
		$naziv_predmeta = db_result($q115,0,0);

		$q130 = db_query("replace angazman set osoba=$osoba, predmet=$predmet, akademska_godina=$angazman_ak_god, angazman_status=$status");

		zamgerlog("nastavnik u$osoba angazovan na predmetu pp$predmet (status: $status, akademska godina: $id_ak_god)",4);
		zamgerlog2("nastavnik angazovan na predmetu", $osoba, $predmet, intval($id_ak_god));
		nicemessage("Nastavnik angažovan na predmetu $naziv_predmeta.");
		print "<p>Kliknite na naziv predmeta na spisku ispod kako biste detaljnije podesili privilegije.</p>";
	}


	// Promjena uloga korisnika
	if (param('subakcija') == "uloga" && check_csrf_token()) {
		$korisnik['student']=$korisnik['nastavnik']=$korisnik['prijemni']=$korisnik['studentska']=$korisnik['siteadmin']=$korisnik['sefodsjeka']=$korisnik['uprava']=0;
		$q150 = db_query("select privilegija from privilegije where osoba=$osoba");
		while($r150 = db_fetch_row($q150)) {
			if ($r150[0]=="student") $korisnik['student']=1;
			if ($r150[0]=="nastavnik") $korisnik['nastavnik']=1;
			if ($r150[0]=="prijemni") $korisnik['prijemni']=1;
			if ($r150[0]=="studentska") $korisnik['studentska']=1;
			if ($r150[0]=="siteadmin") $korisnik['siteadmin']=1;
			if ($r150[0]=="sefodsjeka") $korisnik['sefodsjeka']=1;
			if ($r150[0]=="uprava") $korisnik['uprava']=1;
		}

		foreach ($korisnik as $privilegija => $vrijednost) {
			if ($user_studentska && !$user_siteadmin && $privilegija !== 'nastavnik') continue;
		
			if ($_POST[$privilegija]=="1" && $vrijednost==0) {
				$q151 = db_query("insert into privilegije set osoba=$osoba, privilegija='$privilegija'");
				zamgerlog("osobi u$osoba data privilegija $privilegija",4);
				zamgerlog2("osobi data privilegija", $osoba, 0, 0, $privilegija);
				nicemessage("Data privilegija $privilegija");
			}
			if ($_POST[$privilegija]!="1" && $vrijednost==1) {
				$q151 = db_query("delete from privilegije where osoba=$osoba and privilegija='$privilegija'");
				zamgerlog("osobi u$osoba oduzeta privilegija $privilegija",4);
				zamgerlog2("osobi oduzeta privilegija", $osoba, 0, 0, $privilegija);
				nicemessage("Oduzeta privilegija $privilegija");
			}
		}
	}


	// Osnovni podaci

	$q200 = db_query("select ime, prezime, 1, brindexa, UNIX_TIMESTAMP(datum_rodjenja), mjesto_rodjenja, jmbg, drzavljanstvo, adresa, adresa_mjesto, telefon, kanton, strucni_stepen, naucni_stepen, slika from osoba where id=$osoba");
	if (!($r200 = db_fetch_row($q200))) {
		zamgerlog("nepostojeca osoba u$osoba",3);
		zamgerlog2("nepostojeca osoba", $osoba);
		niceerror("Nepostojeća osoba!");
		return;
	}
	$ime = db_result($q200,0,0);
	$prezime = db_result($q200,0,1);
	$jmbg = db_result($q200,0,6);
	$slika = db_result($q200,0,14);

	// Pripremam neke podatke za ispis
	// Ovo nije u istom upitu jer nije pravi FK, podaci ne moraju biti definisani
	// TODO dodati polje "nedefinisano" u sve tabele, po mogućnosti sa IDom nula
	$mjesto_rodj = "";
	if (db_result($q200,0,5)!=0) {
		$q201 = db_query("select naziv from mjesto where id=".db_result($q200,0,5));
		$mjesto_rodj = db_result($q201,0,0);
	}

	$drzavljanstvo = "";
	if (db_result($q200,0,7)!=0) {
		$q202 = db_query("select naziv from drzava where id=".db_result($q200,0,7));
		$drzavljanstvo = db_result($q202,0,0);
	}

	$adresa = db_result($q200,0,8);
	if (db_result($q200,0,9)!=0) {
		$q203 = db_query("select naziv from mjesto where id=".db_result($q200,0,9));
		$adresa .= ", ".db_result($q203,0,0);
	}

	$kanton = "";
	if (db_result($q200,0,11)>0) {
		$q205 = db_query("select naziv from kanton where id=".db_result($q200,0,11));
		$kanton = db_result($q205,0,0);
	}

	if (db_result($q200,0,12)!=0) {
		$q206 = db_query("select naziv from strucni_stepen where id=".db_result($q200,0,12));
		$strucni_stepen = db_result($q206,0,0);
	}
	if (db_result($q200,0,13)!=0) {
		$q207 = db_query("select naziv from naucni_stepen where id=".db_result($q200,0,13));
		$naucni_stepen = db_result($q207,0,0);
	}

	// Spisak mailova
	
	$q260 = db_query("select adresa from email where osoba=$osoba");
	$email_adrese = "";
	while ($r260 = db_fetch_row($q260)) {
		if ($email_adrese !== "") $email_adrese .= ", ";
		$email_adrese .= $r260[0];
	}

	?>

	<h2><?=$ime?> <?=$prezime?></h2>
	<?
	if ($slika!="") {
		?>
		<img src="?sta=common/slika&osoba=<?=$osoba?>"><br/>
		<?
	}
	?>
	<table border="0" width="600"><tr><td valign="top">
		Ime: <b><?=$ime?></b><br/>
		Prezime: <b><?=$prezime?></b><br/>
		Broj indexa (za studente): <b><?=db_result($q200,0,3)?></b><br/>
		JMBG: <b><?=$jmbg?></b><br/>
		<br/>
		Datum rođenja: <b><?
		if (db_result($q200,0,4)) print date("d. m. Y.", db_result($q200,0,4))?></b><br/>
		Mjesto rođenja: <b><?=$mjesto_rodj?></b><br/>
		Državljanstvo: <b><?=$drzavljanstvo?></b><br/>
		</td><td valign="top">
		Adresa: <b><?=$adresa?></b><br/>
		Kanton: <b><?=$kanton?></b><br/>
		Telefon: <b><?=db_result($q200,0,10)?></b><br/>
		Kontakt e-mail: <b><?=$email_adrese?></b><br/>
		<br/>
		Stručni stepen: <b><?=$strucni_stepen?></b><br/>
		Naučni stepen: <b><?=$naucni_stepen?></b><br/>
		<br/>
		ID: <b><?=$osoba?></b><br/>
		<br/>
		</form>
		<?=genform("GET")?>
		<input type="hidden" name="akcija" value="podaci">
		<input type="Submit" value=" Izmijeni "></form></td>
	</tr></table>
	<?


	// Login&password

	// Promjena lozinke na ovom mjestu je moguća samo ako je autentikacija "table"
	// Site admin će vidjeti detaljnije informacije
	if ($conf_system_auth == "table" || $user_siteadmin) {
		// Kapitalizovan naziv autentikacijskog modula
		$auth_name = $conf_system_auth;
		if ($conf_system_auth == "ldap") $auth_name = "LDAP";
		if ($conf_system_auth == "cas") $auth_name = "CAS";
		if ($conf_system_auth == "keycloak") $auth_name = "KeyCloak";
		
		print "<p>Korisnički pristup:\n";
		$q201 = db_query("select aktivan from auth where id=$osoba and aktivan=1");
		if (db_num_rows($q201)<1) print "<font color=\"red\">NEMA</font>";
		?></p>
			<table border="0">
			<tr>
				<td>Korisničko ime:</td>
				<td width="80">Šifra:</td>
				<td>Aktivan:</td>
				<td>&nbsp;</td>
			</tr>
		<?

		$q201 = db_query("select login,password,aktivan from auth where id=$osoba");
		while ($r201 = db_fetch_row($q201)) {
			$login=$r201[0];
			$password=$r201[1];
			$pristup=$r201[2];
			?>
			<?=genform("POST")?>
			<input type="hidden" name="subakcija" value="auth">
			<input type="hidden" name="stari_login" value="<?=$login?>">
			<tr>
				<td><input type="text" size="10" name="login" value="<?=$login?>"></td>
				<td valign="center"><? if ($conf_system_auth=="table") { 
					?><input type="password" size="10" name="password" value="<?=$password?>"><? 
				} else {
					?><b><?=$auth_name?></b><?
				}?></td>
				<td><input type="checkbox" size="10" name="aktivan" value="1" <? if ($pristup==1) print "CHECKED"; ?>></td>
				<td><input type="Submit" value=" Izmijeni "> <input type="Submit" name="brisanje" value=" Obriši "></td>
			</tr></form>
			<?
		}

		?>
		<?=genform("POST")?>
		<input type="hidden" name="subakcija" value="auth">
		<input type="hidden" name="stari_login" value="">
		<tr>
			<td><input type="text" size="10" name="login" value=""></td>
			<td><? if ($conf_system_auth=="table") { 
					?><input type="password" size="10" name="password" value=""><? 
				} else {
					?><b><?=$auth_name?></b><?
				}?></td>
			<td><input type="checkbox" size="10" name="aktivan" value="1"></td>
			<td><input type="Submit" value=" Dodaj novi "></td>
		</tr></form></table>
		<?
	}

	// U slučaju LDAPa studentskoj službi dajemo mogućnost da (de)aktivira pristup korisniku
	else if ($conf_system_auth == "ldap") {
		$q201 = db_query("select aktivan from auth where id=$osoba and aktivan=1");
		if (db_num_rows($q201)>0) $pristup=1; else $pristup=0;
		?>
		
		<script language="JavaScript">
		function upozorenje(pristup) {
			document.authforma.pristup.value=pristup;
			document.authforma.submit();
		}
		</script>
		<?=genform("POST", "authforma")?>
		<input type="hidden" name="subakcija" value="auth_ldap">
		<input type="hidden" name="pristup" value="">
		</form>

		<table border="0">
		<tr>
			<td colspan="5">Korisnički pristup: <input type="checkbox" name="aktivan" onchange="javascript:upozorenje('<?=$pristup?>');" <? if ($pristup==1) print "CHECKED"; ?>></td>
		</tr></table></form>
		<?
	}


	// Uloge korisnika
	$korisnik_student=$korisnik_nastavnik=$korisnik_prijemni=$korisnik_studentska=$korisnik_siteadmin=$korisnik_sefodsjeka=$korisnik_uprava=0;
	print "<p>Tip korisnika: ";
	$q209 = db_query("select privilegija from privilegije where osoba=$osoba");

	while ($r209 = db_fetch_row($q209)) {
		if ($r209[0]=="student") {
			print "<b>student,</b> ";
			$korisnik_student=1;
		}
		if ($r209[0]=="nastavnik") {
			print "<b>nastavnik,</b> ";
			$korisnik_nastavnik=1;
		}
		if ($r209[0]=="prijemni") {
			print "<b>kandidat na prijemnom ispitu,</b> ";
			$korisnik_prijemni=1;
		}
		if ($r209[0]=="studentska") {
			print "<b>uposlenik studentske službe,</b> ";
			$korisnik_studentska=1;
		}
		if ($r209[0]=="siteadmin") {
			print "<b>administrator,</b> ";
			$korisnik_siteadmin=1;
		}
		if ($r209[0]=="sefodsjeka") {
			print "<b>šef odsjeka,</b> ";
			$korisnik_sefodsjeka=1;
		}
		if ($r209[0]=="uprava") {
			print "<b>dekan/prodekan,</b> ";
			$korisnik_uprava=1;
		}
	}
	print "</p>\n";


	// Admin dio

	if ($user_siteadmin) {
		unset( $_REQUEST['student'], $_REQUEST['nastavnik'], $_REQUEST['prijemni'], $_REQUEST['studentska'], $_REQUEST['siteadmin'], $_REQUEST['sefodsjeka'], $_REQUEST['uprava'] );
		?>
		<?=genform("POST")?>
		<input type="hidden" name="subakcija" value="uloga">
		<input type="checkbox" name="student" value="1" <?if($korisnik_student==1) print "CHECKED";?>> Student&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="checkbox" name="nastavnik" value="1" <?if($korisnik_nastavnik==1) print "CHECKED";?>> nastavnik&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="checkbox" name="prijemni" value="1" <?if($korisnik_prijemni==1) print "CHECKED";?>> prijemni&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="checkbox" name="studentska" value="1" <?if($korisnik_studentska==1) print "CHECKED";?>> studentska&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="checkbox" name="siteadmin" value="1" <?if($korisnik_siteadmin==1) print "CHECKED";?>> siteadmin&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="checkbox" name="sefodsjeka" value="1" <?if($korisnik_sefodsjeka==1) print "CHECKED";?>> šef odsjeka&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="checkbox" name="uprava" value="1" <?if($korisnik_uprava==1) print "CHECKED";?>> dekan/prodekan<br/> <br/>
		<input type="submit" value=" Izmijeni ">
		</form>
		<?
	} else if($korisnik_student==1 && $korisnik_nastavnik!=1) {
		?>
		<?=genform("POST")?>
		<input type="hidden" name="subakcija" value="uloga">
		<input type="checkbox" name="nastavnik" value="1"> Proglasi korisnika za nastavnika&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="submit" value=" Promijeni ">
		</form>
		<?
	}

	// Link za uređivanje historije studenta TODO - obratiti pozornost na to "ko ima pristup"
	if($korisnik_student==1 && ($user_studentska || $user_siteadmin)) {
		// Uređivanje historije studenta
		print "<p> Za uređivanje historije studenta, kliknite <a href='index.php?sta=studentska/uredi_historiju_studenta&student=".$osoba."'>ovdje</a>. </p>";
		// Unos konačne ocjene studenta
		print "<p> Za unos, pregled i izmjene konačnih ocjena po odluci, kliknite <a href='index.php?sta=studentska/konacna_ocjena&student=".$osoba."&akcija=pregled'>ovdje</a>. </p>";
	}

	// STUDENT

	if ($korisnik_student) {
		?>
		<hr>
		<h3>STUDENT</h3>
		<?

		// Trenutno upisan na semestar:
		$q220 = db_query("SELECT s.naziv, ss.semestar, ss.akademska_godina, ag.naziv, s.id, ts.trajanje, ns.naziv, ts.ciklus, status.naziv, ss.put
		FROM student_studij as ss, studij as s, akademska_godina as ag, tipstudija as ts, nacin_studiranja as ns, status_studenta status
		WHERE ss.student=$osoba and ss.studij=s.id and ag.id=ss.akademska_godina and s.tipstudija=ts.id and ss.nacin_studiranja=ns.id AND ss.status_studenta=status.id
		ORDER BY ag.naziv DESC");
		$studij="0";
		$studij_id=$semestar=0;
		$puta=1;
		$status_studenta = "";

		// Da li je ikada slušao nešto?
		$ikad_studij=$ikad_studij_id=$ikad_semestar=$ikad_ak_god=$studij_ciklus=-1;
		$ikad_ciklusi = $ikad_puta = array();
	
		while ($r220=db_fetch_row($q220)) {
			if ($r220[2]==$id_ak_god && $r220[1]>$semestar) { //trenutna akademska godina
				$studij=$r220[0];
				$semestar = $r220[1];
				$studij_id=$r220[4];
				$studij_trajanje=$r220[5];
				$nacin_studiranja="kao $r220[6]";
				$studij_ciklus=$r220[7];
				if ($r220[8] != "Student") $status_studenta = " - " .$r220[8];
				$puta = $r220[9];
			} else if ($r220[2]>$ikad_ak_god || ($r220[2]==$ikad_ak_god && $r220[1]>$ikad_semestar)) {
				$ikad_studij=$r220[0];
				$ikad_semestar=$r220[1];
				$ikad_ak_god=$r220[2];
				$ikad_ak_god_naziv=$r220[3];
				$ikad_studij_id=$r220[4];
				$ikad_studij_trajanje=$r220[5];
				$ikad_puta["$ikad_studij_id-$ikad_semestar"] = $r220[9];
			}
			if (!in_array($r220[7], $ikad_ciklusi)) $ikad_ciklusi[] = $r220[7];
		}

		$prepisi_ocjena = "";
		if (count($ikad_ciklusi) > 1) {
			$ikad_ciklusi = array_reverse($ikad_ciklusi);
			foreach ($ikad_ciklusi as $i)
				if ($i == 99)
					$prepisi_ocjena .= "<br><a href=\"?sta=izvjestaj/index2&student=$osoba&ciklus=$i\">Samo stručni studij</a>";
				else
					$prepisi_ocjena .= "<br><a href=\"?sta=izvjestaj/index2&student=$osoba&ciklus=$i\">Samo $i. ciklus</a>";
		}


		// Izvjestaji
		
		?>
		<div style="float:left; margin-right:10px">
			<table width="100" border="1" cellspacing="0" cellpadding="0">
				<tr><td bgcolor="#777777" align="center">
					<font color="white"><b>IZVJEŠTAJI:</b></font>
				</td></tr>
				<tr><td align="center"><a href="?sta=izvjestaj/historija&student=<?=$osoba?>">
				<img src="static/images/32x32/report.png" border="0"><br/>Historija</a></td></tr>
				<tr><td align="center"><a href="?sta=izvjestaj/index2&student=<?=$osoba?>">
				<img src="static/images/32x32/report.png" border="0"><br/>Prepis ocjena</a> <?=$prepisi_ocjena?></td></tr>
				<tr><td align="center"><a href="?sta=izvjestaj/progress&student=<?=$osoba?>&razdvoji_ispite=0">
				<img src="static/images/32x32/report.png" border="0"><br/>Bodovi</a></td></tr>
				<tr><td align="center"><a href="?sta=izvjestaj/progress&student=<?=$osoba?>&razdvoji_ispite=1">
				<img src="static/images/32x32/report.png" border="0"><br/>Bodovi + nepoloženi ispiti</a></td></tr>
			</table>
		</div>
		<?

		// Aktivni moduli
		$modul_uou=$modul_kolizija=0;
		foreach ($registry as $r) {
			if (count($r) == 0) continue;
			if ($r[0]=="student/ugovoroucenju") $modul_uou=1;
			if ($r[0]=="student/kolizija") $modul_kolizija=1;
		}

		// Trenutno slusa studij 

		$nova_ak_god=0;

		?>
		<p align="left">Trenutno (<b><?=$naziv_ak_god?></b>) upisan/a na:<br/>
		<?
		if ($studij=="0") {
			?>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nije upisan/a niti u jedan semestar!</p>
			<?

			// Proglasavamo zadnju akademsku godinu koju je slusao za tekucu
			// a tekucu za novu
			$nova_ak_god = $id_ak_god;
			$naziv_nove_ak_god = $naziv_ak_god;
			if ($ikad_semestar != 0) {
				// Ako je covjek upisan u buducu godinu, onda je u toku upis
				if ($ikad_ak_god>$id_ak_god) {
					$nova_ak_god=$ikad_ak_god;
					$naziv_nove_ak_god=$ikad_ak_god_naziv;
					$semestar=$ikad_semestar-1; // da se ne bi ispisivalo da drugi put sluša
				} else {
					$id_ak_god = $ikad_ak_god;
					$naziv_ak_god = $ikad_ak_god_naziv;
					$semestar = $ikad_semestar;
					if ($semestar % 2 != 0) $semestar++; // Da ga ne bi pokušavalo upisati u parni semestar
				}
				// Zelimo da se provjeri ECTS:
				$studij = $ikad_studij;
				$studij_id = $ikad_studij_id;
				$studij_trajanje = $ikad_studij_trajanje;

			} else {
				// Nikada nije slušao ništa - ima li podataka o prijemnom ispitu?
				$q225 = db_query("select pt.akademska_godina, ag.naziv, s.id, s.naziv from prijemni_termin as pt, prijemni_prijava as pp, akademska_godina as ag, studij as s where pp.osoba=$osoba and pp.prijemni_termin=pt.id and pt.akademska_godina=ag.id and pp.studij_prvi=s.id order by ag.id desc, pt.id desc limit 1");
				if (db_num_rows($q225)>0) {
					$nova_ak_god = db_result($q225,0,0);
					$naziv_nove_ak_god = db_result($q225,0,1);
					$novi_studij = db_result($q225,0,3);
					$novi_studij_id = db_result($q225,0,2);
				}
			}

		} else {
			?>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>&quot;<?=$studij?>&quot;</b>, <?=$semestar?>. semestar (<?=$puta?>. put) <?=$nacin_studiranja?> <?=$status_studenta?> (<a href="?sta=studentska/osobe&amp;osoba=<?=$osoba?>&amp;akcija=ispis&amp;studij=<?=$studij_id?>&amp;semestar=<?=$semestar?>&amp;godina=<?=$id_ak_god?>">ispiši sa studija</a>) (<a href="?sta=studentska/osobe&osoba=<?=$osoba?>&amp;akcija=promijeni_nacin&amp;studij=<?=$studij_id?>&amp;semestar=<?=$semestar?>&amp;godina=<?=$id_ak_god?>">promijeni način studiranja</a>)</p>
			<?
			$q230 = db_query("select id, naziv from akademska_godina where id=$id_ak_god+1");
			if (db_num_rows($q230)>0) {
				$nova_ak_god = db_result($q230,0,0);
				$naziv_nove_ak_god = db_result($q230,0,1);
			}
		}

		$zaduzenje = db_get("SELECT zaduzenje FROM student_zaduzenje WHERE student=$osoba");
		if ($zaduzenje > 0) {
			print "<p style=\"color: red\">Evidentirano je zaduženje: <b>" . sprintf("%.2f", $zaduzenje) . " KM</b> - ";
		}
		print "<a href=\"?sta=studentska/osobe&osoba=$osoba&akcija=izmijeni_zaduzenje\">Izmijeni zaduženje</a></p>\n";

		if ($nova_ak_god==0) { // Upis u tekućoj godini (ako nije kreirana nova)
			?>
			<a href="?sta=studentska/osobe&amp;osoba=<?=$osoba?>&amp;akcija=upis&amp;studij=<?=$studij_id?>&amp;semestar=<?=($semestar+1)?>&amp;godina=<?=$id_ak_god?>">Upiši na <?=($semestar+1)?>. semestar</a>
			<?
			
			// Ispisujemo podatke o ugovoru o učenju
			if ($modul_uou==1) {
				$q270 = db_query("select s.naziv, u.semestar, u.kod from ugovoroucenju as u, studij as s where u.student=$osoba and u.akademska_godina=$id_ak_god and u.studij=s.id order by u.semestar");
				if (db_fetch3($q270, $naziv_studija_ugovor, $semestar_ugovor, $kod_ugovora)) {
					// Uvijek se popunjava za neparni i parni semestar!
					$semestar_ugovor .= ". i " . ($semestar_ugovor+1) . ".";
					?>
					<p>Student je popunio/la <b>Ugovor o učenju</b> za <?=$naziv_studija_ugovor?>, <?=$semestar_ugovor?> semestar:<br>Kod: <b><?=$kod_ugovora?></b></p>
					<?
				} else {
					?>
					<p>Student NIJE popunio/la <b>Ugovor o učenju</b> za sljedeću akademsku godinu.</p>
					<?
				}
			}
		}


		// Pristup web servisu za uplate
		if ($conf_knjigovodstveni_servis) {
			global $conf_url_daj_karticu;
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


		// UPIS U SLJEDEĆU AK. GODINU

		if ($nova_ak_god!=0) { // Ne prikazuj podatke o upisu dok se ne kreira nova ak. godina


		?>
		<p>Upis u akademsku <b><?=$naziv_nove_ak_god?></b> godinu:<br />
		<?


		// Da li je vec upisan?
		$novi_studij_id = 0;
		$q235 = db_query("select s.naziv, ss.semestar, s.id, ss.put from student_studij as ss, studij as s where ss.student=$osoba and ss.studij=s.id and ss.akademska_godina=$nova_ak_god order by ss.semestar desc");
		if (db_num_rows($q235)>0) {
			$novi_studij=db_result($q235,0,0);
			$novi_semestar=db_result($q235,0,1);
			$novi_studij_id=db_result($q235,0,2);
			$nputa=db_result($q235,0,3);
			?>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Student je upisan na studij: <b><?=$novi_studij?></b>, <?=$novi_semestar?>. semestar (<?=$nputa?>. put). (<a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=ispis&studij=<?=$novi_studij_id?>&semestar=<?=$novi_semestar?>&godina=<?=$nova_ak_god?>">ispiši sa studija</a>)</p><?

		} else {

		// Ima li uslove za upis
		if ($semestar==0 && $ikad_semestar==0) {
			// Upis na prvu godinu

			?>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nemamo podataka da je ovaj student ikada bio upisan na fakultet.</p><?
			if ($novi_studij_id) { // Podatak sa prijemnog
				?>
				<p><a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=upis&studij=<?=$novi_studij_id?>&semestar=1&godina=<?=$nova_ak_god?>">Upiši studenta na <?=$novi_studij?>, 1. semestar.</a></p>
				<?
			} else {
				?>
				<p><a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=upis&studij=<?=$novi_studij_id?>&semestar=1&godina=<?=$nova_ak_god?>">Upiši studenta na Prvu godinu studija, 1. semestar.</a></p>
				<?
			}

		} else if ($studij=="0") {
			if ($ikad_semestar%2==0) $ikad_semestar--;
			// Trenutno nije upisan na fakultet, ali upisacemo ga
			?>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=upis&studij=<?=$ikad_studij_id?>&semestar=<?=$ikad_semestar?>&godina=<?=$nova_ak_god?>">Ponovo upiši studenta na <?=$ikad_studij?>, <?=$ikad_semestar?>. semestar.</a></p>
			<?

		} else if ($semestar%2!=0) {
			// S neparnog na parni ide automatski
			?>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Student je stekao uslove za upis na &quot;<?=$studij?>&quot;, <?=($semestar+1)?> semestar</p>
			<p><a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=upis&studij=<?=$studij_id?>&semestar=<?=($semestar+1)?>&godina=<?=$id_ak_god?>">Upiši studenta na &quot;<?=$studij?>&quot;, <?=($semestar+1)?> semestar.</a></p>
			<?

		} else {
			// Upis na neparni semestar - da li je student dao uslov?
			$ima_uslov=0;
			
			// Tekst za ono što upisuje
			if ($semestar==$studij_trajanje) {
				$sta = "sljedeći ciklus studija";
			} else {
				$sta = "&quot;$studij&quot;, ".($semestar+1).". semestar";
			}


			// Pokusacemo odrediti uslov na osnovu polozenih predmeta...
			global $zamger_predmeti_pao, $zamger_pao_ects;
			$ima_uslov = ima_li_uslov($osoba);
			
			if ($ima_uslov) {
				?>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Student je stekao/la uslove za upis na <?=$sta?></p>
				<p><a href="?sta=studentska/osobe&amp;osoba=<?=$osoba?>&amp;akcija=upis&amp;studij=<?=$studij_id?>&amp;semestar=<?=($semestar+1)?>&amp;godina=<?=$nova_ak_god?>">Upiši studenta na <?=$sta?>.</a></p>
				<?
			} else {
				?>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Student <b>NIJE</b> stekao/la uslove za <?=$sta?><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?
				
				print "(".count($zamger_predmeti_pao)." nepoloženih predmeta, $zamger_pao_ects ECTS kredita)";
				
				?></p>
				<p><a href="?sta=studentska/osobe&amp;osoba=<?=$osoba?>&amp;akcija=upis&amp;studij=<?=$studij_id?>&amp;semestar=<?=($semestar-1)?>&amp;godina=<?=$nova_ak_god?>">Ponovo upiši studenta na <?=$studij?>, <?=($semestar-1)?>. semestar (<?=($ikad_puta["$studij_id-".($semestar-1)]+1)?>. put).</a></p>
				<!--p><a href="?sta=studentska/osobe&amp;osoba=<?=$osoba?>&amp;akcija=upis&amp;studij=<?=$studij_id?>&amp;semestar=<?=($semestar+1)?>&amp;godina=<?=$nova_ak_god?>">Upiši studenta na <?=$sta?>.</a></p-->
				<?
			}
		}

		} // if ($q235... else ... -- nije vec upisan nigdje

			// Ugovor o učenju
			if ($modul_uou==1) {
				$q270 = db_query("select s.naziv, u.semestar, u.kod from ugovoroucenju as u, studij as s where u.student=$osoba and u.akademska_godina=$nova_ak_god and u.studij=s.id order by u.semestar");
				if (db_fetch3($q270, $naziv_studija_ugovor, $semestar_ugovor, $kod_ugovora)) {
					// Uvijek se popunjava za neparni i parni semestar!
					$semestar_ugovor .= ". i " . ($semestar_ugovor+1) . ".";
					?>
					<p>Student je popunio/la <b>Ugovor o učenju</b> za <?=$naziv_studija_ugovor?>, <?=$semestar_ugovor?> semestar:<br>Kod: <b><?=$kod_ugovora?></b></p>
					<?
				} else {
					?>
					<p>Student NIJE popunio/la <b>Ugovor o učenju</b> za sljedeću akademsku godinu.</p>
					<?
				}
			}

		} // if (db_num_rows($q230  -- da li postoji ak. god. iza aktuelne?


		// Kolizija
		if ($modul_kolizija==1) {
			$q280 = db_query("select count(*) from kolizija where student=$osoba and akademska_godina=$nova_ak_god");
			$ima_koliziju=0;
			if (db_result($q280,0,0)>0) {
				$ima_koliziju=$nova_ak_god;
			} else {
				// Probavamo i za trenutnu
				$q280 = db_query("select count(*) from kolizija where student=$osoba and akademska_godina=$id_ak_god");
				if (db_result($q280,0,0)>0) {
					$ima_koliziju=$id_ak_god;
				}
			}

			if ($ima_koliziju) { // provjeravamo septembar
				$kolizija_ok = true;
				$qc = db_query("select distinct predmet from septembar where student=$osoba and akademska_godina=$ima_koliziju");
				while ($rc = db_fetch_row($qc)) {
					$predmet = $rc[0];
			
					// Da li ima ocjenu?
					$qd = db_query("select count(*) from konacna_ocjena where student=$osoba and predmet=$predmet and ocjena>=6");
					if (db_result($qd,0,0)>0) continue;
			
					// Da li ima septembarskog roka?
					$qe = db_query("select i.id, k.prolaz from ispit as i, komponenta as k where i.akademska_godina=".($ima_koliziju-1)." and (MONTH(i.datum)=8 or MONTH(i.datum)=9) and (select count(*) from ispitocjene as io where io.ispit=i.id)>0 and i.predmet=$predmet and i.komponenta=k.id and k.naziv NOT LIKE 'Usmeni%'");
					if (db_num_rows($qe)==0) continue; // nema
			
					$polozio=false;
					$septembar_razlog = "";
					while ($re = db_fetch_row($qe)) {
						$qf = db_query("select ocjena from ispitocjene where ispit=$re[0] and student=$osoba");
						if (db_num_rows($qf)>0 && db_result($qf,0,0)>=$re[1]) {
							$polozio=true;
							break;
						}
					}
					if (!$polozio) {
						$kolizija_ok=false; 
						$qg = db_query("select naziv from predmet where id=$predmet");
						$paopredmet=db_result($qg,0,0);
						break;
					}
				}

				if ($kolizija_ok) {
					?>
					<p>Student je popunio/la <b>Zahtjev za koliziju</b>. <a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=kolizija&godina=<?=$ima_koliziju?>">Kliknite ovdje da potvrdite upis na kolizione predmete.</a></p>
					<?
				} else {
					?>
					<p>Student je popunio/la <b>Zahtjev za koliziju</b> koji je neispravan (nije položio/la <?=$paopredmet?>). Potrebno ga je ponovo popuniti.</p>
					<?
				}
			}
		}


		// Upis studenta na pojedinačne predmete
		?>
		<p><a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=predmeti">Manuelni upis studenta na predmete / ispis sa predmeta.</a></p>
		<?

		print "\n<div style=\"clear:both\"></div>\n";
	} // STUDENT



	// NASTAVNIK

	// Akademska godina se promijenila :( CLEANUP
	$id_ak_god = $orig_iag;
	$q399 = db_query("select naziv from akademska_godina where id=$id_ak_god");
	$naziv_ak_god=db_result($q399,0,0);


	if ($korisnik_nastavnik) {
		?>
		<br/><hr>
		<h3>NASTAVNIK</h3>
		<p><b>Podaci o izboru</b></p>
		<?


		// Izbori

		$q400 = db_query("select z.naziv, UNIX_TIMESTAMP(i.datum_izbora), UNIX_TIMESTAMP(i.datum_isteka), i.oblast, i.podoblast, i.dopunski, i.druga_institucija from izbor as i, zvanje as z WHERE i.osoba=$osoba and i.zvanje=z.id order by i.datum_isteka DESC, i.datum_izbora DESC");
		if (db_num_rows($q400)==0) {
			print "<p>Nema podataka o izboru.</p>\n";
		} else {
			$datum_izbora = date("d. m. Y", db_result($q400,0,1));
			if (db_result($q400,0,1)==0)
				$datum_izbora = "<font color=\"red\">(nepoznato)</font>";
			$datum_isteka = date("d. m. Y", db_result($q400,0,2));
			if (db_result($q400,0,2)==0)
				$datum_isteka = "Neodređeno";
			$oblast = db_result($q400,0,3);
			if ($oblast<1)
				$oblast = "<font color=\"red\">(nepoznato)</font>";
			else {
				$q410 = db_query("select naziv from oblast where id=$oblast");
				if (db_num_rows($q410)<1)
					$oblast = "<font color=\"red\">GREŠKA</font>";
				else
					$oblast = db_result($q410,0,0);
			}
			$podoblast = db_result($q400,0,4);
			if ($podoblast<1)
				$podoblast = "<font color=\"red\">(nepoznato)</font>";
			else {
				$q420 = db_query("select naziv from podoblast where id=$podoblast");
				if (db_num_rows($q420)<1)
					$podoblast = "<font color=\"red\">GREŠKA</font>";
				else
					$podoblast = db_result($q420,0,0);
			}
			if (db_result($q400,0,5)==0) $radniodnos = "Stalni";
			else $radniodnos = "Dopunski";
			
			?>
			<table border="0">
			<tr><td>Zvanje:</td><td><?=db_result($q400,0,0)?></td></tr>
			<tr><td>Datum izbora:</td><td><?=$datum_izbora?></td></tr>
			<tr><td>Datum isteka:</td><td><?=$datum_isteka?></td></tr>
			<tr><td>Oblast:</td><td><?=$oblast?></td></tr>
			<tr><td>Podoblast:</td><td><?=$podoblast?></td></tr>
			<tr><td>Radni odnos:</td><td><?=$radniodnos?></td></tr>
			<?
			if (db_result($q400,0,6)==1) print "<tr><td colspan=\"2\">Biran/a na drugoj VŠO</td></tr>\n";
			?>
			</table>
			<?
		}

		?>
		<p><a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=izbori">Izmijenite podatke o izboru ili pogledajte historijske podatke</a></p>
		<?


		// Angažman

		?>
		<p><b>Angažman u nastavi (akademska godina <?=$naziv_ak_god?>)</b></p>
		<ul>
		<?
		
		$q430 = db_query("select p.id, p.naziv, angs.naziv, i.kratki_naziv from angazman as a, angazman_status as angs, predmet as p, institucija as i where a.osoba=$osoba and a.akademska_godina=$id_ak_god and a.predmet=p.id and a.angazman_status=angs.id and p.institucija=i.id order by angs.id, p.naziv");
		if (db_num_rows($q430) < 1)
			print "<li>Uposlenik nije angažovan niti na jednom predmetu u ovoj godini.</li>\n";
		while ($r430 = db_fetch_row($q430)) {
			print "<li><a href=\"?sta=studentska/predmeti&akcija=edit&predmet=$r430[0]&ag=$id_ak_god\">$r430[1] ($r430[3])</a> - $r430[2]</li>\n";
		}


		// Angažman
	
		?></ul>
		<p>Angažuj nastavnika na predmetu:
		<?=genform("POST")?>
		<input type="hidden" name="subakcija" value="angazuj">
		<select name="predmet" class="default"><?
		$q190 = db_query("select p.id, p.naziv, i.kratki_naziv from predmet as p, ponudakursa as pk, institucija as i where pk.predmet=p.id and pk.akademska_godina=$id_ak_god and p.institucija=i.id group by p.id,p.naziv order by p.naziv");
		while ($r190 = db_fetch_row($q190)) {
			print "<option value=\"$r190[0]\">$r190[1] ($r190[2])</a>\n";
		}
		?></select><br/>
		<?=db_dropdown("angazman_status")?>
		<?=db_dropdown("akademska_godina", $id_ak_god)?>
		<input type="submit" value=" Dodaj "></form></p>
		<?


		// Prava pristupa

		?>
		<p><b>Prava pristupa (akademska godina <?=$naziv_ak_god?>)</b></p>
		<ul>
		<?
		$q180 = db_query("select p.id, p.naziv, np.nivo_pristupa, i.kratki_naziv from nastavnik_predmet as np, predmet as p, institucija as i where np.nastavnik=$osoba and np.predmet=p.id and np.akademska_godina=$id_ak_god and p.institucija=i.id order by np.nivo_pristupa, p.naziv"); // FIXME: moze li se ovdje izbaciti tabela ponudakursa? studij ili institucija?
		if (db_num_rows($q180) < 1)
			print "<li>Nijedan</li>\n";
		while ($r180 = db_fetch_row($q180)) {
			print "<li><a href=\"?sta=studentska/predmeti&akcija=edit&predmet=$r180[0]&ag=$id_ak_god\">$r180[1] ($r180[3])</a>";
			if ($r180[2] == "nastavnik") print " (Nastavnik)";
			else if ($r180[2] == "super_asistent") print " (Super asistent)";
			print "</li>\n";
		}
		?></ul>
		<p>Za prava pristupa na prethodnim akademskim godinama, koristite pretragu na kartici &quot;Predmeti&quot;<br/></p>
		<?


		// Dodjela prava pristupa
	
		?><p>Dodijeli prava za predmet:
		<?=genform("POST")?>
		<input type="hidden" name="subakcija" value="daj_prava">
		<select name="predmet" class="default"><?
		$q190 = db_query("select p.id, p.naziv, i.kratki_naziv from predmet as p, ponudakursa as pk, institucija as i where pk.predmet=p.id and pk.akademska_godina=$id_ak_god and p.institucija=i.id group by p.id, p.naziv order by p.naziv");
		while ($r190 = db_fetch_row($q190)) {
			print "<option value=\"$r190[0]\">$r190[1] ($r190[2])</a>\n";
		}
		?></select>&nbsp;
		<input type="submit" value=" Dodaj "></form></p>
		<?
	}





	// PRIJEMNI

	$q600 = db_query("select prijemni_termin, broj_dosjea, nacin_studiranja, studij_prvi, studij_drugi, studij_treci, studij_cetvrti, izasao, rezultat from prijemni_prijava where osoba=$osoba");
	if (db_num_rows($q600)>0) {
		?>
		<br/><hr>
		<h3>KANDIDAT NA PRIJEMNOM ISPITU</h3>
		<?
		while ($r600 = db_fetch_row($q600)) {
			$q610 = db_query("select ag.id, ag.naziv, UNIX_TIMESTAMP(pt.datum), pt.ciklus_studija from prijemni_termin as pt, akademska_godina as ag where pt.id=$r600[0] and pt.akademska_godina=ag.id");
			?>
			<b>Za akademsku <?=db_result($q610,0,1)?> godinu (<?=db_result($q610,0,3)?>. ciklus studija), održan <?=date("d. m. Y", db_result($q610,0,2))?></b>
			<ul><li><?
				if ($r600[7]>0) print "$r600[8] bodova"; else print "(nije izašao/la)";
			?></li>
			<li>Broj dosjea: <?=$r600[1]?>, <?
			$q615 = db_query("select naziv from nacin_studiranja where id=$r600[2]");
			if (db_num_rows($q615)>0)
				print db_result($q615,0,0);
			else
				print "nepoznato";
			for ($i=3; $i<=6; $i++) {
				if ($r600[$i]>0) {
					$q620 = db_query("select kratkinaziv from studij where id=".$r600[$i]);
					print ", ".db_result($q620,0,0);
				}
			}
			?></li>
			<?

			// Link na upis prikazujemo samo za ovogodišnji prijemni
			$godina_prijemnog = db_result($q610,0,0);
//			$q630 = db_query("select id from akademska_godina where aktuelna=1");
//			$nova_ak_god = db_result($q630,0,0)+1;

//			if ($godina_prijemnog==$nova_ak_god) {
			// Moguće je da se asistent upisuje na 3. ciklus pa je $korisnik_nastavnik==true
			if (!$korisnik_student) {
				?>
				<li><a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=upis&studij=<?=$r600[3]?>&semestar=1&godina=<?=$godina_prijemnog?>">Upiši kandidata na <?
				$q630 = db_query("select naziv from studij where id=$r600[3]");
				if (db_num_rows($q630) > 0) 
					print "&quot;".db_result($q630,0,0)."&quot;";
				else
					print "prvu godinu studija";
				?>, 1. semestar, u akademskoj <?=db_result($q610,0,1)?> godini</a></li>
			<?
			}
			?>
			</ul><?
		}

		$q640 = db_query("select ss.naziv, us.opci_uspjeh, us.kljucni_predmeti, us.dodatni_bodovi, us.ucenik_generacije from srednja_skola as ss, uspjeh_u_srednjoj as us where us.srednja_skola=ss.id and us.osoba=$osoba");

		if (db_num_rows($q640)>0) {
			?>
			<b>Uspjeh u srednjoj školi:</b>
			<ul>
			<li>Škola: <?=db_result($q640,0,0)?></li>
			<li>Opći uspjeh: <?=db_result($q640,0,1)?>. Ključni predmeti: <?=db_result($q640,0,2)?>. Dodatni bodovi: <?=db_result($q640,0,3)?>. <?
			if (db_result($q640,0,4)>0) print "Učenik generacije.";
			?></li>
			</ul>
			<?
		}
	}

	?></td></tr></table></center><? // Vanjska tabela

}



// Spisak osoba

else {
	$src = db_escape(param("search"));
	$limit = 20;
	$offset = int_param("offset");

	// Naucni stepeni
	$naucni_stepen = array();
	$q99 = db_query("select id, titula from naucni_stepen");
	while ($r99 = db_fetch_row($q99))
		$naucni_stepen[$r99[0]]=$r99[1];

	?>
	<p><h3>Studentska služba - Studenti i nastavnici</h3></p>

	<table width="500" border="0"><tr><td align="left">
		<p><b>Pretraži osobe:</b><br/>
		Unesite dio imena i prezimena ili broj indeksa<br/>
		<?=genform("GET")?>
		<input type="hidden" name="offset" value="0"> <?/*resetujem offset*/?>
		<input type="text" size="50" name="search" value="<? if ($src!="sve") print $src?>"> <input type="Submit" value=" Pretraži "></form>
		<a href="<?=genuri()?>&search=sve">Prikaži sve osobe</a></p>
	<?
	if ($src) {
		$rezultata=0;
		if ($src == "sve") {
			$q100 = db_query("select count(*) from osoba");
			$q101 = db_query("select id, ime, prezime, brindexa, naucni_stepen from osoba order by prezime,ime limit $offset,$limit");
			$rezultata = db_result($q100,0,0);
		} else {
			$src = preg_replace("/\s+/"," ",$src);
			$src=trim($src);
			$dijelovi = explode(" ", $src);
			$query = "";

			// Probavamo traziti ime i prezime istovremeno
			if (count($dijelovi)==2) {
				$q100 = db_query("select count(*) from osoba where ime like '%$dijelovi[0]%' and prezime like '%$dijelovi[1]%'");
				$q101 = db_query("select id,ime,prezime,brindexa,naucni_stepen from osoba where ime like '%$dijelovi[0]%' and prezime like '%$dijelovi[1]%' order by prezime,ime limit $offset,$limit");
				if (db_result($q100,0,0)==0) {
					$q100 = db_query("select count(*) from osoba where ime like '%$dijelovi[1]%' and prezime like '%$dijelovi[0]%'");
					$q101 = db_query("select id,ime,prezime,brindexa,naucni_stepen from osoba where ime like '%$dijelovi[1]%' and prezime like '%$dijelovi[0]%' order by prezime,ime limit $offset,$limit");
				}
				$rezultata = db_result($q100,0,0);
			}

			// Nismo nasli ime i prezime, pokusavamo bilo koji dio
			if ($rezultata==0) {
				foreach($dijelovi as $dio) {
					if ($query != "") $query .= "or ";
					$query .= "ime like '%$dio%' or prezime like '%$dio%' or brindexa like '%$dio%' ";
					if (intval($dio)>0) $query .= "or id=".intval($dio)." ";
				}
				$q100 = db_query("select count(*) from osoba where ($query)");
				$q101 = db_query("select id,ime,prezime,brindexa,naucni_stepen from osoba where ($query) order by prezime,ime limit $offset,$limit");
				$rezultata = db_result($q100,0,0);
			}

			// Nismo nasli nista, pokusavamo login
			if ($rezultata==0) {
				$query="";
				foreach($dijelovi as $dio) {
					if ($query != "") $query .= "or ";
					$query .= "a.login like '%$dio%' ";
				}
				$q100 = db_query("select count(*) from osoba as o, auth as a where ($query) and a.id=o.id");
				$q101 = db_query("select o.id,o.ime,o.prezime,o.brindexa,o.naucni_stepen from osoba as o, auth as a where ($query) and a.id=o.id order by o.prezime,o.ime limit $offset,$limit");
				$rezultata = db_result($q100,0,0);
			}

		}

		if ($rezultata == 0)
			print "Nema rezultata!";
		else if ($rezultata>$limit) {
			print "Prikazujem rezultate ".($offset+1)."-".($offset+20)." od $rezultata. Stranica: ";

			for ($i=0; $i<$rezultata; $i+=$limit) {
				$br = intval($i/$limit)+1;
				if ($i==$offset)
					print "<b>$br</b> ";
				else
					print "<a href=\"".genuri()."&offset=$i\">$br</a> ";
			}
			print "<br/>";
		}
//		else
//			print "$rezultata rezultata:";

		print "<br/>";

		print '<table width="100%" border="0">';
		$i=$offset+1;
		while ($r101 = db_fetch_row($q101)) {
			print "<tr ";
			if ($i%2==0) print "bgcolor=\"#EEEEEE\"";
			print "><td>$i. $r101[2] ";
			if ($r101[4]>0) print $naucni_stepen[$r101[4]]." ";
			print $r101[1];
			if (intval($r101[3])>0) print " ($r101[3])";
			print "</td><td><a href=\"".genuri()."&akcija=edit&osoba=$r101[0]\">Detalji</a></td></tr>";
			$i++;
		}
		print "</table>";
	}

	?>
		<br/>
		<?=genform("POST")?>
		<input type="hidden" name="akcija" value="novi">
		<b>Unesite novu osobu:</b><br/>
		<table border="0" cellspacing="0" cellpadding="0" width="100%">
		<tr><td>Ime<? if ($conf_ldap_search) print " ili login"?>:</td><td>Prezime:</td><td>&nbsp;</td></tr>
		<tr>
			<td><input type="text" name="ime" size="15"></td>
			<td><input type="text" name="prezime" size="15"></td>
			<td><input type="submit" value=" Dodaj "></td>
		</tr></table>
		</form>
	<?
	?>

	</td></tr></table>
	<?
}


?>
</td></tr></table></center>
<?


}


function opcije_za_retroaktivni_upis($academicYearId, $semester) {
	?>
	<p>Upišite studenta retroaktivno na:</p>
	<?=genform("POST");?>
	Akademska godina: <select name="godina"><?
		$years = api_call("zamger/year")["results"];
		foreach ($years as $id => $academicYear) {
			print "<option value=\"$id\" ";
			if ($id == $academicYearId) print "SELECTED";
			print ">" . $academicYear['name'] . "</option>";
		}
	?></select><br>
	Semestar: <select name="semestar">
		<?
		for ($i=1; $i<=8; $i++) {
			print "<option value=$i";
			if ($i == $semester) print " selected";
			print ">$i</option>\n";
		}
		?>
	</select><br>
	<input type="submit" value="Kreni"></form>
	<?
}
