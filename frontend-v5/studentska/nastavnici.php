<?

// STUDENTSKA/NASTAVNICI - administracija nastavnika, studentska služba

// v3.9.1.0 (2008/02/19) + Preimenovan bivsi admin_nihada
// v3.9.1.1 (2008/03/04) + Sprijecen konflikt IDa nastavnika i studenta, omogucen unos studenta kao nastavnika
// v3.9.1.2 (2008/03/24) + Nova auth tabela
// v3.9.1.3 (2008/04/12) + Popravljen typo i redirekcija u proceduri za dodavanje nastavnika; popravljeno proglasavanje korisnika za nastavnika kada se koristi LDAP
// v3.9.1.4 (2008/06/10) + Popravljena redirekcija kod izmjene podataka upravo dodanog nastavnika

function studentska_nastavnici() {

global $userid, $user_siteadmin, $user_studentska, $conf_system_auth, $conf_ldap_server, $conf_ldap_domain;

global $_lv_; // Potrebno za genform() iz libvedran




// Provjera privilegija

if (!$user_studentska && !$user_siteadmin) {
	zamgerlog("nije studentska",3); // 3: error
	biguglyerror("Pristup nije dozvoljen.");
	return;
}



?>
<p><h3>Studentska služba - Nastavnici</h3></p>

<?

$akcija = $_REQUEST['akcija'];
$nastavnik = intval($_REQUEST['nastavnik']);


if ($akcija == "novi") {
	$ime = substr(my_escape($_POST['ime']), 0, 100);
	if (!preg_match("/\w/", $ime)) {
		zamgerlog("ime nije ispravno ($ime)",3);
		niceerror("Ime nije ispravno");
		return;
	}

	$prezime = substr(my_escape($_POST['prezime']), 0, 100);

	// Probamo tretirati ime kao LDAP UID
	if ($conf_system_auth == "ldap") {
		$uid = $ime;
		$ds = ldap_connect($conf_ldap_server);
		ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
		if ($ds && ldap_bind($ds)) {
			$sr = ldap_search($ds, "", "uid=$uid", array("givenname","sn") );
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
				$uid = "";
				niceerror("Korisnik nije pronadjen na LDAPu... dodajem novog!");
			}
		} else {
			zamgerlog("ne mogu kontaktirati LDAP server",3);
			niceerror("Ne mogu kontaktirati LDAP server... pravim se da ga nema :(");
		}
	}

	if (!preg_match("/\w/", $prezime)) {
		zamgerlog("prezime nije ispravno ($prezime)",3);
		niceerror("Prezime nije ispravno");
		return;
	}

	// Da li ovaj korisnik već postoji u auth tabeli?
	if ($conf_system_auth == "ldap")
		$q10 = myquery("select id,ime,prezime,nastavnik from auth where login='$uid'");
	else
		$q10 = myquery("select id,ime,prezime,nastavnik from auth where ime like '$ime' and prezime like '$prezime'");

	if ($r10 = mysql_fetch_row($q10)) {
		if ($r10[3]==0) {
			// Pronađen korisnik, proglašavamo ga za studenta
			$q20 = myquery("update auth set nastavnik=1 where id=$r10[0]");
			nicemessage("Korisnik je proglašen za nastavnika.");
			zamgerlog("korisnik u$r10[0] proglašen za nastavnika",4); // nivo 4 - audit
			$akcija="edit";
			$_POST['subakcija']="";
			$nastavnik=$r10[0];
		} else {
			// Korisnik već postoji i nastavnik je! Ovo je greška
			zamgerlog("nastavnik vec postoji u bazi ('$ime' '$prezime' - ID: $r10[0])",3);
			niceerror("Nastavnik već postoji u bazi:");
			print "<br><a href=\"".genuri()."&akcija=edit&nastavnik=$r10[0]\">$r10[1] $r10[2]</a>";
			return;
		}

	} else {
		// Nije u tabeli, dodajemo ga...
		$q30 = myquery("select id from auth order by id desc limit 1");
		$nastavnik = mysql_result($q30,0,0)+1;

		if ($conf_system_auth == "ldap" && $uid != "") {
			// Ako je LDAP onda imamo email adresu
			$email = $uid.$conf_ldap_domain;
			$q40 = myquery("insert into auth set id=$nastavnik, login='$uid', ime='$ime', prezime='$prezime', email='$email', nastavnik=1");
		} else {
			$q40 = myquery("insert into auth set id=$nastavnik, login='$uid', ime='$ime', prezime='$prezime', nastavnik=1");
		}
		nicemessage("Novi korisnik je dodan.");
		zamgerlog("dodan novi nastavnik u$nastavnik (ID: $nastavnik)",4); // nivo 4: audit
		$akcija="edit";
		$_POST['subakcija']="";
	}
}


if ($akcija == "edit") {

	print "<a href=\"".genuri()."&akcija=\">Nazad na rezultate pretrage</a><br/><br/>";
	

	// Submit akcije

	// Promjena osnovnih podataka
	if ($_POST['subakcija'] == "podaci") {
		$ime = my_escape($_POST['ime']);
		$prezime = my_escape($_POST['prezime']);
		$email = my_escape($_POST['email']);

		$q100 = myquery("update auth set ime='$ime', prezime='$prezime', email='$email' where id=$nastavnik");
		zamgerlog("izmjena osnovnih podataka nastavnika u$nastavnik",4);
	}

	// Promjena autentikacije (tabela)
	if ($_POST['subakcija'] == "auth") {
		$login = my_escape($_POST['login']);
		$password = my_escape($_POST['password']);

		$q110 = myquery("update auth set login='$login', password='$password' where id=$nastavnik");
		zamgerlog("izmijenjen login za nastavnika u$nastavnik",4);
	}

	// Prijava nastavnika na predmet
	if ($_POST['subakcija'] == "upisi") {
		$predmet = intval($_POST['predmet']);
		$admin_predmeta = intval($_POST['admin_predmeta']);

		$q120 = myquery("select count(*) from nastavnik_predmet where nastavnik=$nastavnik and predmet=$predmet");
		if (mysql_result($q120)>0) {
			$q130 = myquery("update nastavnik_predmet set admin=$admin_predmeta where nastavnik=$nastavnik, predmet=$predmet");
		} else {
			$q140 = myquery("insert into nastavnik_predmet set nastavnik=$nastavnik, predmet=$predmet, admin=$admin_predmeta");
		}

		zamgerlog("nastavnik u$nastavnik prijavljen na predmet p$predmet (admin: $admin_predmeta)",4);
	}

	// Izvjestaji

	?>
	<table width="700" border="0" cellspacing="0" cellpadding="0"><tr><td width="100" valign="top">
		<table width="100%" border="1" cellspacing="0" cellpadding="0">
			<tr><td bgcolor="#777777" align="center">
				<font color="white"><b>IZVJEŠTAJI:</b></font>
			</td></tr>
			<tr><td align="center">(Ništa za sada)</td></tr>
		</table>
	</td><td width="10" valign="top">&nbsp;
	</td><td width="590" valign="top">
	<?


	// Osnovni podaci

	$q150 = myquery("select ime,prezime,email from auth where id=$nastavnik");
	if (!($r150 = mysql_fetch_row($q150))) {
		zamgerlog("nepostojeci nastavnik $nastavnik",3);
		niceerror("Nepostojeći nastavnik!");
		return;
	}
	?>
	<?=genform("POST")?>
	<input type="hidden" name="akcija" value="edit">
	<input type="hidden" name="nastavnik" value="<?=$nastavnik?>">
	<input type="hidden" name="subakcija" value="podaci">
	<table width="100%" border="0"><tr>
		<td>Ime:<br/> <input type="text" size="10" name="ime" value="<?=$r150[0]?>"></td>
		<td>Prezime:<br/> <input type="text" size="10" name="prezime" value="<?=$r150[1]?>"></td>
		<td>E-mail:<br/> <input type="text" size="10" name="email" value="<?=$r150[2]?>"></td>
		<td><input type="Submit" value=" Izmijeni "></td>
	</tr></form>
	<tr><td colspan="5"><br/></td></tr>
	<?


	// Login&password
	if ($conf_system_auth == "table") {
		$q160 = myquery("select login,password from auth where id=$nastavnik");
		if (!($r160 = mysql_fetch_row($q160))) $auth=0; else $auth=1;

		?>
		<?=genform("POST")?>
		<input type="hidden" name="subakcija" value="auth">
		<tr>
			<td colspan="2">Korisnički pristup: <? if(!$auth) print '<font color="red">NEMA</font>'; ?></td>
			<td>Korisničko ime:<br/> <input type="text" size="10" name="login" value="<?=$r160[0]?>"></td>
			<td>Šifra:<br/> <input type="password" size="10" name="password" value="<?=$r160[1]?>"></td>
			<td><input type="Submit" value="<? if($auth) print ' Izmijeni '; else print ' Dodaj '?>"></td>
		</tr></form>
		<?
	}
	print "</table>\n";

	// Angazovan na predmetima

	print "<p>Angažovan na predmetima:</p>\n<ul>";
	$q170 = myquery("select id from akademska_godina order by naziv desc limit 1");
	$tekuca_ag = mysql_result($q170,0,0);

	$q180 = myquery("select pk.id, p.naziv, ag.naziv, np.admin from nastavnik_predmet as np, predmet as p, ponudakursa as pk, akademska_godina as ag where np.nastavnik=$nastavnik and np.predmet=pk.id and pk.akademska_godina=ag.id and ag.id=$tekuca_ag and pk.predmet=p.id");
	if (mysql_num_rows($q180) < 1)
		print "<li>Nijedan</li>\n";
	while ($r180 = mysql_fetch_row($q180)) {
		print "<li><a href=\"?sta=studentska/predmeti&akcija=edit&predmet=$r180[0]\">$r180[1] ($r180[2])</a>";
		if ($r180[3] == 1) print " (Administrator predmeta)";
		print "</li>\n";
	}
	print "</ul>\n";
	print "<p>Svi predmeti su u tekućoj akademskoj godini. Za prethodne akademske godine, koristite pretragu na kartici &quot;Predmeti&quot;<br/></p>";


	// Angažman na predmetu

	print "<p>Angažuj nastavnika na:\n";
	print genform("POST");
	print '<input type="hidden" name="subakcija" value="upisi">';
	print '<select name="predmet">';
	$q190 = myquery("select pk.id, p.naziv from predmet as p, ponudakursa as pk where pk.predmet=p.id and pk.akademska_godina=$tekuca_ag order by p.naziv");
	while ($r190 = mysql_fetch_row($q190)) {
		print "<option value=\"$r190[0]\">$r190[1]</a>\n";
	}
	print '</select><input type="submit" value=" Upiši "></form></p>';


	?></td></tr></table><? // Vanjska tabela
}


else {
	$src = my_escape($_REQUEST["search"]);
	$limit = 20;
	$offset = intval($_REQUEST["offset"]);

	?>
	<table width="500" border="0"><tr><td align="left">
		<p><b>Pretraga</b><br/>
		Za prikaz svih nastavnika, ostavite polje za pretragu prazno.</br>
		<?=genform("POST")?>
		<input type="hidden" name="offset" value="0"> <?/*resetujem offset*/?>
		<input type="text" size="50" name="search" value="<? if ($src!="") print $src?>"> <input type="Submit" value=" Pretraži "></form>
		<br/>
	<?
	if ($src != "") {
		$dijelovi = explode(" ", $src);
		$query = "";
		foreach($dijelovi as $dio) {
			if ($query != "") $query .= "or ";
			$query .= "ime like '%$dio%' or prezime like '%$dio%' ";
		}
		$q200 = myquery("select count(*) from auth where ($query) and nastavnik=1");
		$q210 = myquery("select id,ime,prezime from auth where ($query) and nastavnik=1 order by prezime,ime limit $offset,$limit");
	} else {
		$q200 = myquery("select count(*) from auth where nastavnik=1");
		$q210 = myquery("select id,ime,prezime from auth where nastavnik=1 order by prezime,ime limit $offset,$limit");
	}
	$rezultata = mysql_result($q200,0,0);

	if ($rezultata == 0)
		print "Nema rezultata!";
	else {
		if ($rezultata>$limit) {
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
		print "<br/>";

		print '<table width="100%" border="0">';
		$i=$offset+1;
		while ($r210 = mysql_fetch_row($q210)) {
			print "<tr><td>$i. $r210[2] $r210[1]</td>\n";
			print "<td><a href=\"".genuri()."&akcija=edit&nastavnik=$r210[0]\">Detalji</a></td>\n";
			$i++;
		}
		print "</table>";
	}
	?>
		<br/>
		<?=genform("POST")?>
		<input type="hidden" name="akcija" value="novi">
		<b>Novi nastavnik:</b><br/>
		<table border="0" cellspacing="0" cellpadding="0" width="100%">
		<tr><td>Ime:</td><td>Prezime:</td><td>&nbsp;</td></tr>
		<tr>
			<td><input type="text" name="ime" size="15"></td>
			<td><input type="text" name="prezime" size="15"></td>
			<td><input type="submit" value=" Dodaj "></td>
		</tr></table>
		</form>
	</table>
	<?

}




}

?>
