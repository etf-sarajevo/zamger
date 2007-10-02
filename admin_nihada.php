<?

// v3.0.1.1 (2007/09/11) + Novi modul "Nihada" za unos i pristup podataka o studentima, nastavnicima, loginima itd. Trenutno implementirana samo pretraga studenata i neki izvještaji
// v3.0.1.2 (2007/09/20) + Izdvojeni izvjestaji u modul "izvjestaj"
// v3.0.1.3 (2007/09/25) + Dodana kartica Predmeti, izbacena kartica Korisnici (to je integrisano sa Studenti odnosno Nastavnici), razmaci u broju rezultata na kartici Studenti
// v3.0.1.4 (2007/09/26) + Dodana kartica Nastavnici; dodavanje novog studenta, sortiraj studente i po imenu
// v3.0.1.5 (2007/09/28) + Ispravka buga: nastavnici su dodavani u auth tabelu kao studenti
// v3.0.1.6 (2007/10/02) + Dodan logging; dodana LDAP podrska - kod studenata, polja za login i password se zamjenjuju checkbox-om (koji ce usput povuci i e-mail adresu sa LDAPa); kod nastavnika, polja za login i password su ukinuta a auth tabela se automatski popunjava pri kreiranju nastavnika; moguce dodati nastavnika kucanjem UIDa u polje za ime


function admin_nihada() {

global $userid, $system_auth, $ldap_server, $ldap_domain;

global $_lv_; // We use form generators


// Provjera privilegija
$q1 = myquery("select siteadmin from nastavnik where id=$userid");

if (mysql_num_rows($q1) < 1 || mysql_result($q1,0,0) < 1) {
	niceerror("Pristup nije dozvoljen.");
	return;
}

$tab=$_REQUEST['tab'];
if ($tab=="") $tab="Studenti";

$akcija=$_REQUEST['akcija'];

logthis("Admin Nihada - tab $tab");



function printtab($ime,$tab) {
	if ($ime==$tab) 
		print '<td bgcolor="#EEEEEE" width="50">'.$ime.'</td>'."\n";
	else
		print '<td bgcolor="#BBBBBB" width="50"><a href="qwerty.php?sta=nihada&tab='.$ime.'">'.$ime.'</a></td>'."\n";
}



?>
<p><h3>Administracija</h3></p>

<table border="0" cellspacing="1" cellpadding="5" width="800">
<tr>
<td width="50">&nbsp;</td>
<?
printtab("Studenti", $tab);
printtab("Nastavnici", $tab);
printtab("Predmeti", $tab);
?>
<td bgcolor="#BBBBBB" width="50"><a href="qwerty.php">Nazad</a></td>
<td width="350">&nbsp;</td>
</tr>
<tr>
<td width="50">&nbsp;</td>
<td colspan="7" bgcolor="#EEEEEE" width="750">
<?


//------------------------------
//   STUDENTI
//------------------------------

if ($tab == "Studenti" && $akcija == "novi") {
	$ime = substr(my_escape($_POST['ime']), 0, 100);
	if (!preg_match("/\w/", $ime)) {
		niceerror("Ime nije ispravno");
		return;
	}
	$prezime = substr(my_escape($_POST['prezime']), 0, 100);
	if (!preg_match("/\w/", $prezime)) {
		niceerror("Prezime nije ispravno");
		return;
	}
	$brindexa = intval($_POST['brindexa']);
	if ($brindexa<1 || $brindexa>100000) {
		niceerror("Broj indexa nije ispravan");
		return;
	}

	logthis("Dodan novi student: '$ime' '$prezime' '$brindexa'");

	$q180 = myquery("select id,ime,prezime,brindexa from student where ime like '$ime' and prezime like '$prezime'");
	if ($r180 = mysql_fetch_row($q180)) {
		niceerror("Student već postoji u bazi:");
		print "<br><a href=\"".genuri()."&akcija=edit&student=$r180[0]\">$r180[1] $r180[2] ($r180[3])</a>";
		return;
	}

	$q181 = myquery("select id,ime,prezime,brindexa from student where brindexa='$brindexa'");
	if ($r181 = mysql_fetch_row($q181)) {
		niceerror("Dvostruki broj indeksa:");
		print "<br><a href=\"".genuri()."&akcija=edit&student=$r181[0]\">$r181[1] $r181[2] ($r181[3])</a>";
		return;
	}

	$q182 = myquery("insert into student set ime='$ime', prezime='$prezime', brindexa='$brindexa'");
	$q183 = myquery("select id from student where ime='$ime' and prezime='$prezime' and brindexa='$brindexa'");
	$student = mysql_result($q183,0,0);


	?>
	<script language="JavaScript">
	location.href='<?=genuri()?>&akcija=edit&student=<?=$student?>';
	</script>
	<?
}


else if ($tab == "Studenti" && $akcija == "edit") {
	$student = intval($_REQUEST['student']);

	print "<a href=\"".genuri()."&akcija=\">Nazad na rezultate pretrage</a><br/><br/>";
	

	// Submit akcije

	if ($_POST['subakcija'] == "podaci") {
		logthis("Izmjena podataka studenta (Nihada): $student");

		$ime = my_escape($_POST['ime']);
		$prezime = my_escape($_POST['prezime']);
		$email = my_escape($_POST['email']);
		$brindexa = my_escape($_POST['brindexa']);

		$q190 = myquery("update student set ime='$ime', prezime='$prezime', email='$email', brindexa='$brindexa' where id=$student");
	}
	if ($_REQUEST['subakcija'] == "auth") {
		logthis("Dodan/izmijenjen login za studenta: $student");

		$login = my_escape($_REQUEST['login']);
		$password = my_escape($_REQUEST['password']);

		$q191 = myquery("select count(*) from auth where id=$student and admin=0");
		if (mysql_result($q191,0,0) < 1) {
			// Provjeri predlozeni login na LDAPu
			if ($login == "" && $system_auth == "ldap") {
				$suggest_login = my_escape($_REQUEST['suggest_login']);
				$ds = ldap_connect($ldap_server);
				ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
				if (ldap_bind($ds)) {
					$sr = ldap_search($ds, "", "uid=$suggest_login", array() /* just dn */ );
					if (!$sr) {
						niceerror("ldap_search() failed.");
						exit;
					}
					$results = ldap_get_entries($ds, $sr);
					if ($results['count'] < 1) 
						niceerror("Predloženi login ($suggest_login) nije pronađen na LDAP serveru! Vjerovatno je pogrešan broj indeksa, ime ili prezime.");
						
					else {
						$login = $suggest_login;

						// Updateuj email adresu, ako nije setovana
						$q191a = myquery("select email from student where id=$student");
						if (mysql_result($q191a,0,0) == "") {
							$email = $login.$ldap_domain;
							$q191b = myquery("update student set email='$email' where id=$student");
						}
					}
				}
			}

			if ($login != "")
				$q192 = myquery("insert into auth set id=$student, login='$login', password='$password', admin=0");

		} else {
			// U slucaju nestandardne autentikacije, ukini login
			if ($login == "" && $system_auth != "table")
				$q193 = myquery("delete from auth where id=$student and admin=0");
			else
				$q193 = myquery("update auth set login='$login', password='$password' where id=$student and admin=0");
		}

		// Izbjegni višestruko kreiranje korisnika
		$_REQUEST['login'] = $_POST['login'] = $_GET['login'] = "";
		$_REQUEST['password'] = $_POST['password'] = $_GET['password'] = "";
		$_REQUEST['subakcija'] = $_POST['subakcija'] = $_GET['subakcija'] = "";
	}
	if ($_POST['subakcija'] == "upisi") {
		$predmet = intval($_POST['_lv_column_predmet']);
		$q193 = myquery("select id from labgrupa where predmet=$predmet order by id limit 1");
		if (mysql_num_rows($q193) < 1) {
			$q194 = myquery("insert into labgrupa set predmet=$predmet, naziv='Default grupa'");
			$q193 = myquery("select id from labgrupa where predmet=$predmet order by id limit 1");
		}
		logthis("Student $student upisan na predmet $predmet (Nihada)");
		$labgrupa = mysql_result($q193,0,0);
		$q195 = myquery("insert into student_labgrupa set student=$student, labgrupa=$labgrupa");
	}

	// Izvjestaji

	?>
	<center>
	<table width="700" border="0" cellspacing="0" cellpadding="0"><tr><td width="100" valign="top">
		<table width="100%" border="1" cellspacing="0" cellpadding="0">
			<tr><td bgcolor="#777777" align="center">
				<font color="white"><b>IZVJEŠTAJI:</b></font>
			</td></tr>
			<tr><td align="center"><a href="qwerty.php?sta=izvjestaj&tip=index&student=<?=$student?>">
			<img src="images/kontact_journal.png" border="0"><br/>Indeks</a></td></tr>
			<tr><td align="center"><a href="qwerty.php?sta=izvjestaj&tip=progress&student=<?=$student?>&razdvoji_ispite=0">
			<img src="images/kontact_journal.png" border="0"><br/>Bodovi</a></td></tr>
			<tr><td align="center"><a href="qwerty.php?sta=izvjestaj&tip=progress&student=<?=$student?>&razdvoji_ispite=1">
			<img src="images/kontact_journal.png" border="0"><br/>Bodovi + nepoloženi ispiti</a></td></tr>
		</table>
	</td><td width="10" valign="top">&nbsp;
	</td><td width="590" valign="top">
	<?


	// Osnovni podaci

	$q200 = myquery("select ime,prezime,email,brindexa from student where id=$student");
	if (!($r200 = mysql_fetch_row($q200))) {
		niceerror("Nepostojeći student!");
		return;
	}
	?>
	<?=genform("POST")?>
	<input type="hidden" name="subakcija" value="podaci">
	<table width="100%" border="0"><tr>
		<td>Ime:<br/> <input type="text" size="10" name="ime" value="<?=$r200[0]?>"></td>
		<td>Prezime:<br/> <input type="text" size="10" name="prezime" value="<?=$r200[1]?>"></td>
		<td>E-mail:<br/> <input type="text" size="10" name="email" value="<?=$r200[2]?>"></td>
		<td>Broj indexa:<br/> <input type="text" size="10" name="brindexa" value="<?=$r200[3]?>"></td>
		<td><input type="Submit" value=" Izmijeni "></td>
	</tr></form>
	<tr><td colspan="5"><br/></td></tr>
	<?


	// Login&password
	if ($system_auth == "table") {
		$q201 = myquery("select login,password from auth where id=$student and admin=0");
		if (!($r201 = mysql_fetch_row($q201))) $auth=0; else $auth=1;
	
		?>
		<?=genform("POST")?>
		<input type="hidden" name="subakcija" value="auth">
		<tr>
			<td colspan="2">Korisnički pristup: <? if(!$auth) print '<font color="red">NEMA</font>'; ?></td>
			<td>Korisničko ime:<br/> <input type="text" size="10" name="login" value="<?=$r201[0]?>"></td>
			<td>Šifra:<br/> <input type="password" size="10" name="password" value="<?=$r201[1]?>"></td>
			<td><input type="Submit" value="<? if($auth) print ' Izmijeni '; else print ' Dodaj '?>"></td>
		</tr></form>
		<?
	}
	else {
		$q201 = myquery("select login from auth where id=$student and admin=0");
		if (!($r201 = mysql_fetch_row($q201))) $auth=0; else $auth=1;

		// generisanje logina za studenta
		$suggest_login = strtolower(substr($r200[0],0,1)).strtolower(substr($r200[1],0,1)).$r200[3];
		?>
		<tr>
			<td colspan="5">Korisnički pristup: <input type="checkbox" name="ima_auth" onchange="javascript:location.href='<?=genuri()?>&subakcija=auth&suggest_login=<?=$suggest_login?>';" <? if ($auth==1) print "CHECKED"; ?>></td>
		</tr></form>
		<?
	}

	print "</table>\n";

	// Trenutno sluša

	$q202 = myquery("select id,naziv from akademska_godina order by naziv desc");
	$r202 = mysql_fetch_row($q202);
	
	$q203 = myquery("select p.id,p.naziv from predmet as p, labgrupa as l, student_labgrupa as sl where sl.student=$student and sl.labgrupa=l.id and l.predmet=p.id and p.akademska_godina=$r202[0]");
	if (mysql_num_rows($q203)>0)
		print "<b>Trenutno sluša ($r202[1]):</b>\n<ul>\n";
	while ($r203 = mysql_fetch_row($q203))
		print "<li><a href=\"".genuri()."&tab=Predmeti&akcija=edit&predmet=$r203[0]\">$r203[1]</a></li>\n";
	print "</ul>\n";


	// Upis na predmet

	print "<p>Upis studenta na predmet:\n";
	print genform("POST");
	print '<input type="hidden" name="subakcija" value="upisi">';
	$_lv_["where:akademska_godina"] = $r202[0];
	print db_dropdown("predmet");
	print '<input type="submit" value=" Upiši "></form></p>';


	// Ranije slušao

	print "<b>Odslušao/la:</b>\n<ul>\n";
	while ($r202 = mysql_fetch_row($q202)) {
		$q204 = myquery("select p.id,p.naziv from predmet as p, labgrupa as l, student_labgrupa as sl where sl.student=$student and sl.labgrupa=l.id and l.predmet=p.id and p.akademska_godina=$r202[0]");
		while ($r204 = mysql_fetch_row($q204)) {
			print "<li><a href=\"".genuri()."&tab=Predmeti&akcija=edit&predmet=$r204[0]\">$r204[1] ($r202[1])</a> ";
			$q205 = myquery("select ocjena from konacna_ocjena where student=$student and predmet=$r204[0]");
			if ($r205 = mysql_fetch_row($q205)) 
				if ($r205[0]>5)
					print "(Ocjena: $r205[0])";
				else
					print "(Nije položio/la)";
			else
				print "(Nije položio/la)";
		}
	}


	?></td></tr></table></center><? // Vanjska tabela

}


else if ($tab == "Studenti") {
	$src = my_escape($_REQUEST["search"]);
	$limit = 20;
	$offset = intval($_REQUEST["offset"]);

	?>
	<center>
	<table width="500" border="0"><tr><td align="left">
		<p><b>Pretraži studente</b><br/>
		Unesite dio imena i prezimena ili broj indeksa:<br/>
		<?=genform("POST")?>
		<input type="hidden" name="offset" value="0"> <?/*resetujem offset*/?>
		<input type="text" size="50" name="search" value="<? if ($src!="sve") print $src?>"> <input type="Submit" value=" Pretraži "></form>
		<a href="<?=genuri()?>&search=sve">Prikaži sve studente</a><br/><br/>
	<?
	if ($src) {
		if ($src == "sve") {
			$q100 = myquery("select count(*) from student");
			$q101 = myquery("select id,ime,prezime,brindexa from student order by prezime,ime limit $offset,$limit");
		} else {
			$dijelovi = explode(" ", $src);
			$query = "";
			foreach($dijelovi as $dio) {
				if ($query != "") $query .= "or ";
				$query .= "ime like '%$dio%' or prezime like '%$dio%' or brindexa like '%$dio%' ";
			}
			$q100 = myquery("select count(*) from student where $query");
			$q101 = myquery("select id,ime,prezime,brindexa from student where $query order by prezime,ime limit $offset,$limit");
		}
		$rezultata = mysql_result($q100,0,0);
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
		while ($r101 = mysql_fetch_row($q101)) {
			print "<tr><td>$i. $r101[2] $r101[1] ($r101[3])</td><td><a href=\"".genuri()."&akcija=edit&student=$r101[0]\">Detalji</a></td></tr>";
			$i++;
		}
		print "</table>";
	}
	?>
		<br/>
		<?=genform("POST")?>
		<input type="hidden" name="akcija" value="novi">
		<b>Unesite novog studenta:</b><br/>
		<table border="0" cellspacing="0" cellpadding="0" width="100%">
		<tr><td>Ime:</td><td>Prezime:</td><td>Broj indexa:</td><td>&nbsp;</td></tr>
		<tr>
			<td><input type="text" name="ime" size="15"></td>
			<td><input type="text" name="prezime" size="15"></td>
			<td><input type="text" name="brindexa" size="15"></td>
			<td><input type="submit" value=" Dodaj "></td>
		</tr></table>
		</form>
	</table></center>
	<?
}


//------------------------------
//   PREDMETI
//------------------------------

else if ($tab == "Predmeti" && $akcija == "novi") {
	$naziv = substr(my_escape($_POST['naziv']), 0, 100);
	if (!preg_match("/\w/", $naziv)) {
		niceerror("Naziv nije ispravan");
		return;
	}

	$q390 = myquery("select id from akademska_godina order by naziv desc limit 1");
	$ak_god = mysql_result($q390,0,0);
	$q391 = myquery("select id from predmet where naziv='$naziv' and akademska_godina=$ak_god");
	if (mysql_num_rows($q391)>0) {
		niceerror("Predmet već postoji");
		return;
	}
	$q392 = myquery("insert into predmet set naziv='$naziv', akademska_godina=$ak_god");
	$q393 = myquery("select id from predmet where naziv='$naziv' and akademska_godina=$ak_god");
	$predmet = mysql_result($q393,0,0);

	logthis("Dodan novi predmet '$naziv' (ID: $predmet)");

	?>
	<script language="JavaScript">
	location.href='<?=genuri()?>&akcija=edit&predmet=<?=$predmet?>';
	</script>
	<?
}

else if ($tab == "Predmeti" && $akcija == "edit") {
	$predmet = intval($_REQUEST['predmet']);

	$oag = intval($_REQUEST['old_akademska_godina']);
	print "<a href=\"".genuri()."&akcija=&_lv_column_akademska_godina=$oag\">Nazad na rezultate pretrage</a><br/><br/>";

	// Izvjestaji

	?>
	<center>
	<table width="700" border="0" cellspacing="0" cellpadding="0"><tr><td width="100" valign="top">
		<table width="100%" border="1" cellspacing="0" cellpadding="0">
			<tr><td bgcolor="#777777" align="center">
				<font color="white"><b>IZVJEŠTAJI:</b></font>
			</td></tr>
			<tr><td align="center"><a href="qwerty.php?sta=izvjestaj&tip=grupe&predmet=<?=$predmet?>">
			<img src="images/kontact_journal.png" border="0"><br/>Spisak grupa</a></td></tr>
			<tr><td align="center"><a href="qwerty.php?sta=izvjestaj&tip=predmet_full&predmet=<?=$predmet?>">
			<img src="images/kontact_journal.png" border="0"><br/>Puni izvještaj</a></td></tr>
		</table>
	</td><td width="10" valign="top">&nbsp;
	</td><td width="590" valign="top">
	<?
	

	// Submit akcije

	if ($_POST['subakcija'] == "dodaj") {
		$nastavnik = intval($_POST['_lv_column_nastavnik']);
		if ($nastavnik>0) {
			logthis("Nastavnik $nastavnik dodan na predmet $predmet");

			$q360 = myquery("select count(*) from nastavnik_predmet where nastavnik=$nastavnik and predmet=$predmet");
			if (mysql_result($q360,0,0) < 1) {
				$q361 = myquery("insert into nastavnik_predmet set nastavnik=$nastavnik, predmet=$predmet");
			}
		}
	}
	else if ($_GET['subakcija'] == "set_admin") {
		$nastavnik = intval($_GET['nastavnik']);
		logthis("Nastavnik $nastavnik proglasen za admina predmeta $predmet");

		$yesno = intval($_GET['yesno']);
		$q362 = myquery("update nastavnik_predmet set admin=$yesno where nastavnik=$nastavnik and predmet=$predmet");
	}
	else if ($_GET['subakcija'] == "izbaci") {
		$nastavnik = intval($_GET['nastavnik']);
		logthis("Nastavnik $nastavnik izbacen sa predmeta $predmet");
		$q363 = myquery("delete from nastavnik_predmet where nastavnik=$nastavnik and predmet=$predmet");
	}
	else if($_POST['subakcija'] == "podaci") {
		logthis("Izmijenjeni podaci nastavnika $nastavnik");
		$naziv = my_escape($_POST['naziv']);
		$ak_god = intval($_POST['_lv_column_akademska_godina']);
		$q364 = myquery("update predmet set naziv='$naziv', akademska_godina=$ak_god where id=$predmet");
	}


	// Osnovni podaci

	$q350 = myquery("select naziv,akademska_godina from predmet where id=$predmet");
	if (!($r350 = mysql_fetch_row($q350))) {
		niceerror("Nepostojeći predmet!");
		return;
	}
	?>
	<?=genform("POST")?>
	<input type="hidden" name="subakcija" value="podaci">
	<table width="100%" border="0"><tr>
		<td>Naziv predmeta<br/> <input type="text" size="40" name="naziv" value="<?=$r350[0]?>"></td>
		<td>Akademska godina:<br/> <?=db_dropdown("akademska_godina", $r350[1])?></td>
		<td><input type="Submit" value=" Izmijeni "></td>
	</tr></table></form>
	<?


	// Nastavnici na predmetu

	print "<p>Nastavnici angažovani na predmetu:</p>\n";
	$q351 = myquery("select np.nastavnik,np.admin,n.ime,n.prezime from nastavnik as n, nastavnik_predmet as np where np.nastavnik=n.id and np.predmet=$predmet");
	if (mysql_num_rows($q351) < 1) {
		print "<ul><li>Nema nastavnika</li></ul>\n";
	} else {
		?>
		<table width="100%" border="1" cellspacing="0"><tr><td>Ime i prezime</td><td>Administrator predmeta</td><td>Ograničenja</td><td>&nbsp;</td></tr><?
	}
	while ($r351 = mysql_fetch_row($q351)) {
		print '<tr><td><a href="qwerty.php?sta=nihada&tab=Nastavnici&akcija=edit&nastavnik='.$r351[0].'">'.$r351[2].' '.$r351[3].'</td>'."\n";

		print '<td><input type="checkbox" onchange="javascript:location.href=\'';
		print genuri()."&subakcija=set_admin&nastavnik=$r351[0]&yesno=";
		if ($r351[1]==1) 
			print "0'\" CHECKED></td>\n"; 
		else 
			print "1'\"></td>\n";

		print '<td><a href="'.genuri().'&subakcija=ogranicenja&nastavnik='.$r351[0].'">';
		$q352 = myquery("select l.naziv from ogranicenje as o, labgrupa as l where o.nastavnik=$r351[0] and o.labgrupa=l.id and l.predmet=$predmet");
		if (mysql_num_rows($q352)<1)
			print "Nema";
		while ($r352 = mysql_fetch_row($q352)) {
			print substr($r352[0],0,15).", ";
		}
		print "</a></td>"."\n";

		print "<td><a href=\"".genuri()."&subakcija=izbaci&nastavnik=$r351[0]\">Izbaci</a></td></tr>"."\n";
	}
	if (mysql_num_rows($q351) > 0) {
		print "</table>\n";
	}


	// Dodaj nove nastavnike

	print "<p>Angažman nastavnika na predmetu:\n";
	print genform("POST");
	print '<input type="hidden" name="subakcija" value="dodaj">';
	print db_dropdown("nastavnik");
	print '<input type="submit" value=" Dodaj "></form></p>';

	?></td></tr></table></center><? // Vanjska tabela

}


else if ($tab == "Predmeti") {
	$src = my_escape($_REQUEST["search"]);
	$limit = 20;
	$offset = intval($_REQUEST["offset"]);
	$ak_god = intval($_REQUEST["_lv_column_akademska_godina"]);
	if ($ak_god == 0) {
		$q299 = myquery("select id from akademska_godina order by naziv desc limit 1");
		$ak_god = mysql_result($q299,0,0);
	}

	?>
	<center>
	<table width="500" border="0"><tr><td align="left">
		<p><b>Pretraga</b><br/>
		Za prikaz svih predmeta na akademskoj godini, ostavite polje za pretragu prazno.</br>
		<?=genform("POST")?>
		<input type="hidden" name="offset" value="0"> <?/*resetujem offset*/?>
		<?=db_dropdown("akademska_godina",$ak_god, "Sve akademske godine");?>
		<input type="text" size="50" name="search" value="<? if ($src!="") print $src?>"> <input type="Submit" value=" Pretraži "></form>
		<br/>
	<?
	if ($ak_god>0 && $src != "") {
		$q300 = myquery("select count(*) from predmet where akademska_godina=$ak_god and naziv like '%$src%'");
	} else if ($ak_god>0) {
		$q300 = myquery("select count(*) from predmet where akademska_godina=$ak_god");
	} else if ($src != "") {
		$q300 = myquery("select count(*) from predmet where naziv like '%$src%'");
	} else {
		$q300 = myquery("select count(*) from predmet");
	}
	$rezultata = mysql_result($q300,0,0);

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

		if ($ak_god>0 && $src != "") {
			$q301 = myquery("select p.id, p.naziv, ag.naziv from predmet as p, akademska_godina as ag where p.akademska_godina=ag.id and ag.id=$ak_god and p.naziv like '%$src%' order by ag.naziv desc, p.naziv");
		} else if ($ak_god>0) {
			$q301 = myquery("select p.id, p.naziv, ag.naziv from predmet as p, akademska_godina as ag where p.akademska_godina=ag.id and ag.id=$ak_god order by ag.naziv desc, p.naziv");
		} else if ($src != "") {
			$q301 = myquery("select p.id, p.naziv, ag.naziv from predmet as p, akademska_godina as ag where p.akademska_godina=ag.id and p.naziv like '%$src%' order by ag.naziv desc, p.naziv");
		} else {
			$q301 = myquery("select p.id, p.naziv, ag.naziv from predmet as p, akademska_godina as ag where p.akademska_godina=ag.id order by ag.naziv desc,p.naziv");
		}

		print '<table width="100%" border="0">';
		$i=$offset+1;
		while ($r301 = mysql_fetch_row($q301)) {
			print "<tr><td>$i. $r301[1] ($r301[2])</td>\n";
			print "<td><a href=\"".genuri()."&old_akademska_godina=$ak_god&akcija=edit&predmet=$r301[0]\">Detalji</a></td>\n";
			print "<td><a href=\"qwerty.php?sta=predmet&predmet=$r301[0]\">Uređivanje predmeta</a></td></tr>";
			$i++;
		}
		print "</table>";
	}
	?>
		<br/>
		<?=genform("POST")?>
		<input type="hidden" name="akcija" value="novi">
		<b>Novi predmet:</b><br/>
		<input type="text" name="naziv" size="50"> <input type="submit" value=" Dodaj ">
		</form>
	</table></center>
	<?

}





//------------------------------
//   NASTAVNICI
//------------------------------


else if ($tab == "Nastavnici" && $akcija == "novi") {
	$ime = substr(my_escape($_POST['ime']), 0, 100);
	if (!preg_match("/\w/", $ime)) {
		niceerror("Ime nije ispravno");
		return;
	}

	$prezime = substr(my_escape($_POST['prezime']), 0, 100);

	// Probamo tretirati ime kao LDAP UID
	if ($system_auth == "ldap") {
		$uid = $ime;
		$ds = ldap_connect($ldap_server);
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
			}
		} else {
			niceerror("Ne mogu kontaktirati LDAP server... pravim se da ga nema :(");
		}
	}

	if (!preg_match("/\w/", $prezime)) {
		niceerror("Prezime nije ispravno");
		return;
	}

	$q480 = myquery("select id,ime,prezime from nastavnik where ime like '$ime' and prezime like '$prezime'");
	if ($r480 = mysql_fetch_row($q480)) {
		niceerror("Nastavnik već postoji u bazi:");
		print "<br><a href=\"".genuri()."&akcija=edit&nastavnik=$r480[0]\">$r480[1] $r480[2]</a>";
		return;
	}

	// Ako je LDAP onda imamo email adresu
	if ($system_auth == "ldap") {
		$email = $uid.$ldap_domain;
		$q481 = myquery("insert into nastavnik set ime='$ime', prezime='$prezime', email='$email'");
	} else {
		$q481 = myquery("insert into nastavnik set ime='$ime', prezime='$prezime'");
	}
	$q482 = myquery("select id from nastavnik where ime='$ime' and prezime='$prezime'");
	$nastavnik = mysql_result($q482,0,0);

	// Ubacujemo dummy podatke u auth tabelu za slučaj eksterne autentikacije
	if ($system_auth == "ldap") {
		$q483 = myquery("insert into auth set id=$nastavnik, login='$uid', admin=1");
	}


	logthis("Dodan novi nastavnik '$ime' '$prezime' (ID: $nastavnik)");

	?>
	<script language="JavaScript">
	location.href='<?=genuri()?>&akcija=edit&nastavnik=<?=$nastavnik?>';
	</script>
	<?
}


else if ($tab == "Nastavnici" && $akcija == "edit") {
	$nastavnik = intval($_REQUEST['nastavnik']);

	print "<a href=\"".genuri()."&akcija=\">Nazad na rezultate pretrage</a><br/><br/>";
	

	// Submit akcije

	if ($_POST['subakcija'] == "podaci") {
		logthis("Izmjena osnovnih podataka nastavnika $nastavnik");
		$ime = my_escape($_POST['ime']);
		$prezime = my_escape($_POST['prezime']);
		$email = my_escape($_POST['email']);

		$q490 = myquery("update nastavnik set ime='$ime', prezime='$prezime', email='$email' where id=$nastavnik");
	}
	if ($_POST['subakcija'] == "auth") {
		logthis("Dodan/izmijenjen login za nastavnika $nastavnik");

		$login = my_escape($_POST['login']);
		$password = my_escape($_POST['password']);

		$q491 = myquery("select count(*) from auth where id=$nastavnik");
		if (mysql_result($q491,0,0) < 1) {
			$q492 = myquery("insert into auth set id=$nastavnik, login='$login', password='$password', admin=1");
		} else {
			$q493 = myquery("update auth set login='$login', password='$password' where id=$nastavnik");
		}
	}
	if ($_POST['subakcija'] == "upisi") {
		$predmet = intval($_POST['_lv_column_predmet']);
		$admin_predmeta = intval($_POST['admin_predmeta']);
		$q494 = myquery("insert into nastavnik_predmet set nastavnik=$nastavnik, predmet=$predmet, admin=$admin_predmeta");

		logthis("Nastavnik $nastavnik prijavljen na predmet $predmet (admin: $admin_predmeta)");
	}

	// Izvjestaji

	?>
	<center>
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

	$q480 = myquery("select ime,prezime,email from nastavnik where id=$nastavnik");
	if (!($r480 = mysql_fetch_row($q480))) {
		niceerror("Nepostojeći nastavnik!");
		return;
	}
	?>
	<?=genform("POST")?>
	<input type="hidden" name="subakcija" value="podaci">
	<table width="100%" border="0"><tr>
		<td>Ime:<br/> <input type="text" size="10" name="ime" value="<?=$r480[0]?>"></td>
		<td>Prezime:<br/> <input type="text" size="10" name="prezime" value="<?=$r480[1]?>"></td>
		<td>E-mail:<br/> <input type="text" size="10" name="email" value="<?=$r480[2]?>"></td>
		<td><input type="Submit" value=" Izmijeni "></td>
	</tr></form>
	<tr><td colspan="5"><br/></td></tr>
	<?


	// Login&password
	if ($system_auth == "table") {
		$q481 = myquery("select login,password from auth where id=$nastavnik");
		if (!($r481 = mysql_fetch_row($q481))) $auth=0; else $auth=1;
	
		?>
		<?=genform("POST")?>
		<input type="hidden" name="subakcija" value="auth">
		<tr>
			<td colspan="2">Korisnički pristup: <? if(!$auth) print '<font color="red">NEMA</font>'; ?></td>
			<td>Korisničko ime:<br/> <input type="text" size="10" name="login" value="<?=$r481[0]?>"></td>
			<td>Šifra:<br/> <input type="password" size="10" name="password" value="<?=$r481[1]?>"></td>
			<td><input type="Submit" value="<? if($auth) print ' Izmijeni '; else print ' Dodaj '?>"></td>
		</tr></form>
		<?
	}
	print "</table>\n";

	// Angazovan na predmetima

	print "<p>Angažovan na predmetima:</p>\n<ul>";
	$q482 = myquery("select id from akademska_godina order by naziv desc limit 1");
	$tekuca_ag = mysql_result($q482,0,0);

	$q483 = myquery("select p.id, p.naziv, ag.naziv, np.admin from nastavnik_predmet as np, predmet as p, akademska_godina as ag where np.nastavnik=$nastavnik and np.predmet=p.id and p.akademska_godina=ag.id and ag.id=$tekuca_ag");
	if (mysql_num_rows($q483) < 1)
		print "<li>Nijedan</li>\n";
	while ($r483 = mysql_fetch_row($q483)) {
		print "<li><a href=\"qwerty.php?sta=nihada&tab=Predmeti&akcija=edit&predmet=$r483[0]\">$r483[1] ($r483[2])</a>";
		if ($r483[3] == 1) print " (Administrator predmeta)";
		print "</li>\n";
	}
	print "</ul>\n";
	print "<p>Svi predmeti su u tekućoj akademskoj godini. Za prethodne akademske godine, koristite pretragu na kartici &quot;Predmeti&quot;<br/></p>";


	// Upis na predmet

	print "<p>Angažuj nastavnika na:\n";
	print genform("POST");
	print '<input type="hidden" name="subakcija" value="upisi">';
	$_lv_["where:akademska_godina"] = $tekuca_ag;
	print db_dropdown("predmet");
	print '<input type="submit" value=" Upiši "></form></p>';


	?></td></tr></table></center><? // Vanjska tabela
}


else if ($tab == "Nastavnici") {
	$src = my_escape($_REQUEST["search"]);
	$limit = 20;
	$offset = intval($_REQUEST["offset"]);

	?>
	<center>
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
		$q400 = myquery("select count(*) from nastavnik where $query");
		$q401 = myquery("select id,ime,prezime from nastavnik where $query order by prezime,ime limit $offset,$limit");
	} else {
		$q400 = myquery("select count(*) from nastavnik");
		$q401 = myquery("select id,ime,prezime from nastavnik order by prezime,ime limit $offset,$limit");
	}
	$rezultata = mysql_result($q400,0,0);

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
		while ($r401 = mysql_fetch_row($q401)) {
			print "<tr><td>$i. $r401[2] $r401[1]</td>\n";
			print "<td><a href=\"".genuri()."&akcija=edit&nastavnik=$r401[0]\">Detalji</a></td>\n";
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
	</table></center>
	<?

}




?>
</td>
</tr>
</table>
<?




}

?>
