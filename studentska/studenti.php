<?

// STUDENTSKA/STUDENTI - administracija studenata, studentska služba

// v3.9.1.0 (2008/02/19) + Preimenovan bivsi admin_nihada
// v3.9.1.1 (2008/03/21) + Nova auth tabela, popravka upisa na predmet, pojednostavljenje i čišćenje koda
// v3.9.1.2 (2008/04/23) + Trimovanje whitespace-a kod pretrage



function studentska_studenti() {

global $userid,$user_siteadmin,$user_studentska;
global $conf_system_auth,$conf_ldap_server,$conf_ldap_domain;

global $_lv_; // Potrebno za genform() iz libvedran


// Provjera privilegija
if (!$user_siteadmin && !$user_studentska) { // 2 = studentska, 3 = admin
	zamgerlog("korisnik nije studentska (admin $admin)",3);
	biguglyerror("Pristup nije dozvoljen.");
	return;
}




?>
<p><h3>Studentska služba - Studenti</h3></p>

<?

$akcija = $_REQUEST['akcija'];
$student = intval($_REQUEST['student']);


// Dodavanje novog studenta u bazu korisnika

if ($akcija == "novi") {
	$ime = substr(my_escape($_POST['ime']), 0, 100);
	if (!preg_match("/\w/", $ime)) {
		zamgerlog("ime nije ispravno ($ime)",3);
		niceerror("Ime nije ispravno");
		return;
	}
	$prezime = substr(my_escape($_POST['prezime']), 0, 100);
	if (!preg_match("/\w/", $prezime)) {
		zamgerlog("prezime nije ispravno ($prezime)",3);
		niceerror("Prezime nije ispravno");
		return;
	}
	$brindexa = intval($_POST['brindexa']);
	if ($brindexa<1 || $brindexa>100000) {
		zamgerlog("broj indexa nije ispravan ($brindexa)",3);
		niceerror("Broj indexa nije ispravan");
		return;
	}

	// Tražimo korisnika sa datim imenom i prezimenom
	$q10 = myquery("select id,ime,prezime,brindexa,student from auth where ime like '$ime' and prezime like '$prezime'");
	if ($r10 = mysql_fetch_row($q10)) {
		if ($r10[4]==0) {
			// Pronađen korisnik, proglašavamo ga za studenta
			$q15 = myquery("update auth set student=1 where id=$r10[0]");
			nicemessage("Korisnik je proglašen za studenta.");
			zamgerlog("korisnik u$r10[0] proglašen za studenta",4); // nivo 4 - audit
			$akcija="edit";
			$student=$r10[0];
		} else {
			// Korisnik već postoji i student je! Ovo je greška
			zamgerlog("student vec postoji u bazi ('$ime' '$prezime' - ID: $r10[0])",3);
			niceerror("Student već postoji u bazi:");
			print "<br><a href=\"sta=studentska/studenti&akcija=edit&student=$r10[0]\">$r10[1] $r10[2] ($r10[3])</a>";
			return;
		}
	} else {

		// Provjera duplog broja indeksa
		$q20 = myquery("select id,ime,prezime,brindexa from auth where brindexa='$brindexa'");
		if ($r20 = mysql_fetch_row($q20)) {
			zamgerlog("dvostruki broj indeksa $brindexa (u$r20[0])",3);
			niceerror("Dvostruki broj indeksa:");
			print "<br><a href=\"sta=studentska/studenti&akcija=edit&student=$r20[0]\">$r20[1] $r20[2] ($r20[3])</a>";
			return;
		}
	
		// Sve ok, dodajemo studenta
		$q40 = myquery("select id from auth order by id desc limit 1");
		$student = mysql_result($q40,0,0)+1;
	
		$q30 = myquery("insert into auth set id=$student, ime='$ime', prezime='$prezime', brindexa='$brindexa', student=1");
	
		zamgerlog("dodan novi student u$student (ID: $student)",4); // nivo 4 - audit
		nicemessage("Novi korisnik je dodan.");
		$akcija="edit";
	}
}


// Izmjena podataka o studentu

if ($akcija == "edit") {
	?><a href="?sta=studentska/studenti&search=<?=$_REQUEST['search']?>&offset=<?=$_REQUEST['offset']?>">Nazad na rezultate pretrage</a><br/><br/><?
	

	// Submit akcije

	// Promjena podataka studenta
	if ($_POST['subakcija'] == "podaci") {
		$ime = my_escape($_POST['ime']);
		$prezime = my_escape($_POST['prezime']);
		$email = my_escape($_POST['email']);
		$brindexa = my_escape($_POST['brindexa']);

		$q100 = myquery("update auth set ime='$ime', prezime='$prezime', email='$email', brindexa='$brindexa' where id=$student");

		zamgerlog("izmjena podataka studenta u$student",2); // 2 - edit
	}

	// Promjena korisničkog pristupa i pristupnih podataka
	if ($_REQUEST['subakcija'] == "auth") {

		// LDAP
		if ($conf_system_auth == "ldap") {

			// Ako isključujemo pristup, samo treba pobrisati polje login iz auth tabele
			$pristup = intval($_REQUEST['pristup']);
			if ($pristup!=0) {
				$q105 = myquery("update auth set login='', password='' where id=$student");
				zamgerlog("ukinut login za studenta u$student (ldap)",4);
			} else {

			// predloženi login
			$suggest_login = gen_ldap_uid($student);

			// Tražimo ovaj login na LDAPu...
			$ds = ldap_connect($conf_ldap_server);
			ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
			if (!ldap_bind($ds)) {
				zamgerlog("Ne mogu se spojiti na LDAP server",3); // 3 - greska
				niceerror("Ne mogu se spojiti na LDAP server");
				return;
			}

			$sr = ldap_search($ds, "", "uid=$suggest_login", array() /* just dn */ );
			if (!$sr) {
				zamgerlog("ldap_search() nije uspio.",3);
				niceerror("ldap_search() nije uspio.");
				return;
			}
			$results = ldap_get_entries($ds, $sr);
			if ($results['count'] < 1) {
				zamgerlog("login ne postoji na LDAPu ($suggest_login)",3);
				niceerror("Predloženi login ($suggest_login) nije pronađen na LDAP serveru!");
				print "<p>Da li ste uspravno unijeli broj indeksa, ime i prezime? Ako jeste, kontaktirajte administratora!</p>";

				// Nastavljamo dalje sa edit akcijom kako bi studentska mogla popraviti podatke

			} else {
				// Dodajemo login, ako nije podešen
				$q110 = myquery("select login,email from auth where id=$student");
				if (mysql_result($q110,0,0) == "") {
					$q112 = myquery("update auth set login='$suggest_login' where id=$student");
					zamgerlog("kreiran login za studenta u$student (ldap)",4);
				}
				// Generišemo email adresu ako nije podešena
				if (mysql_result($q110,0,1) == "") {
					$email = $suggest_login.$conf_ldap_domain;
					$q114 = myquery("update auth set email='$email' where id=$student");
					zamgerlog("promijenjen email za studenta u$student",2); // nivo 2 - edit
				}
			}

			} // if ($auth!=0) ... else ...
		} // if ($conf_system_auth == "ldap")


		// Lokalna tabela sa šiframa
		else if ($conf_system_auth == "table") {

			$login = my_escape($_REQUEST['login']);
			$password = my_escape($_REQUEST['password']);

			$q120 = myquery("update auth set login='$login', password='$password' where id=$student");
			zamgerlog("dodan/izmijenjen login za studenta u$student (table)",4);

		}
	} // if ($_REQUEST['subakcija'] == "auth")


	// Upis studenta na predmet
	if ($_POST['subakcija'] == "upisi") {
		$predmet = intval($_POST['predmet']);
		$q130 = myquery("select count(*) from student_predmet where student=$student and predmet=$predmet");
		if (mysql_result($q130,0,0)<1) {
			$q135 = myquery("insert into student_predmet set student=$student, predmet=$predmet");
			zamgerlog("student u$student upisan na predmet p$predmet",4);
		}
	}


	// Izvjestaji

	?>
	<center>
	<table width="700" border="0" cellspacing="0" cellpadding="0"><tr><td width="100" valign="top">
		<table width="100%" border="1" cellspacing="0" cellpadding="0">
			<tr><td bgcolor="#777777" align="center">
				<font color="white"><b>IZVJEŠTAJI:</b></font>
			</td></tr>
			<tr><td align="center"><a href="?sta=izvjestaj/index&student=<?=$student?>">
			<img src="images/32x32/izvjestaj.png" border="0"><br/>Indeks</a></td></tr>
			<tr><td align="center"><a href="?sta=izvjestaj/progress&student=<?=$student?>&razdvoji_ispite=0">
			<img src="images/32x32/izvjestaj.png" border="0"><br/>Bodovi</a></td></tr>
			<tr><td align="center"><a href="?sta=izvjestaj/progress&student=<?=$student?>&razdvoji_ispite=1">
			<img src="images/32x32/izvjestaj.png" border="0"><br/>Bodovi + nepoloženi ispiti</a></td></tr>
		</table>
	</td><td width="10" valign="top">&nbsp;
	</td><td width="590" valign="top">
	<?


	// Osnovni podaci

	$q200 = myquery("select ime,prezime,email,brindexa,login,password from auth where id=$student");
	if (!($r200 = mysql_fetch_row($q200))) {
		zamgerlog("nepostojeci student u$student",3);
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

	if ($conf_system_auth == "table") {
		if ($r200[4]=="") $pristup=0; else $pristup=1;
	
		?>
		<?=genform("POST")?>
		<input type="hidden" name="subakcija" value="auth">
		<tr>
			<td colspan="2">Korisnički pristup: <? if(!$pristup) print '<font color="red">NEMA</font>'; ?></td>
			<td>Korisničko ime:<br/> <input type="text" size="10" name="login" value="<?=$r200[4]?>"></td>
			<td>Šifra:<br/> <input type="password" size="10" name="password" value="<?=$r200[5]?>"></td>
			<td><input type="Submit" value="<? if($pristup) print ' Izmijeni '; else print ' Dodaj '?>"></td>
		</tr></form>
		<?
	}

	else if ($conf_system_auth == "ldap") {
		if ($r200[4]=="") $pristup=0; else $pristup=1;

		?>
		<tr>
			<td colspan="5">Korisnički pristup: <input type="checkbox" name="ima_auth" onchange="javascript:location.href='<?=genuri()?>&subakcija=auth&pristup=<?=$pristup?>';" <? if ($pristup==1) print "CHECKED"; ?>></td>
		</tr></form>
		<?
	}


	// Trenutno upisan na semestar:

	// Prvo odredjujemo aktuelnu akademsku godinu - ovaj upit se dosta koristi kasnije
	$q210 = myquery("select id,naziv from akademska_godina order by naziv desc");
	$id_ak_god = mysql_result($q210,0,0);
	$naziv_ak_god = mysql_result($q210,0,1);

	$q220 = myquery("select s.naziv,ss.semestar,ss.akademska_godina,ag.naziv from student_studij as ss, studij as s, akademska_godina as ag where ss.student=$student and ss.studij=s.id and ag.id=ss.akademska_godina order by ag.naziv desc");
	$studij="0";
	$puta=1;

	while ($r220=mysql_fetch_row($q220)) {
		if ($r220[2]==$id_ak_god) { //trenutna akademska godina
			$studij=$r220[0];
			$semestar = $r220[1];
		}
		else if ($r220[0]==$studij && $r220[1]==$semestar) { // ponovljeni semestri
			$puta++;
		}
	}

	print "</table><br/>\n";
	print "<p>Trenutno (<b>$naziv_ak_god</b>) upisan na:<br/>\n";

	if ($studij=="0") {
		print "Nije upisan niti u jedan semestar!</p>";
	} else {
		print "<b>&quot;$studij&quot;</b>, $semestar. semestar ($puta. put)</p>";
	}


	// Predmeti koje sluša
	
	$q230 = myquery("select pk.id,p.naziv from predmet as p, ponudakursa as pk, student_predmet as sp where sp.student=$student and sp.predmet=pk.id and pk.akademska_godina=$id_ak_god and pk.predmet=p.id");
	if (mysql_num_rows($q230)>0)
		print "Trenutno (<b>$naziv_ak_god</b>) sluša predmete:\n<ul>\n";
	while ($r230 = mysql_fetch_row($q230))
		print "<li><a href=\"?sta=studentska/predmeti&akcija=edit&predmet=$r230[0]\">$r230[1]</a></li>\n";
	print "</ul>\n";


	// Upis na predmet

	print "<p>Upis studenta na predmet:\n";
	print genform("POST");
	print '<input type="hidden" name="subakcija" value="upisi">';
	print '<select name="predmet">';
	// TODO: prikaži samo predmete sa studija koji je upisao
	// Eventualno samo predmete koje može slušati?
	$q240 = myquery("select pk.id, p.naziv from predmet as p, ponudakursa as pk where pk.predmet=p.id and pk.akademska_godina=$id_ak_god order by p.naziv");
	while ($r240 = mysql_fetch_row($q240)) {
		print "<option value=\"$r240[0]\">$r240[1]</a>\n";
	}
	print '</select><input type="submit" value=" Upiši "></form></p>';


	// Ranije slušao

	print "<b>Odslušao/la:</b>\n<ul>\n";
	while ($r210 = mysql_fetch_row($q210)) {
		$q250 = myquery("select pk.id,p.naziv from predmet as p, ponudakursa as pk, labgrupa as l, student_labgrupa as sl where sl.student=$student and sl.labgrupa=l.id and l.predmet=pk.id and pk.akademska_godina=$r210[0] and pk.predmet=p.id");
		while ($r250 = mysql_fetch_row($q250)) {
			print "<li><a href=\"".genuri()."&akcija=edit&predmet=$r250[0]\">$r250[1] ($r210[1])</a> ";
			$q260 = myquery("select ocjena from konacna_ocjena where student=$student and predmet=$r250[0]");
			if ($r260 = mysql_fetch_row($q260)) 
				if ($r260[0]>5)
					print "(Ocjena: $r260[0])";
				else
					print "(Nije položio/la)";
			else
				print "(Nije položio/la)";
		}
	}


	?></td></tr></table></center><? // Vanjska tabela

}


// Spisak studenata

else {
	$src = my_escape($_REQUEST["search"]);
	$limit = 20;
	$offset = intval($_REQUEST["offset"]);

	?>

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
			$q100 = myquery("select count(*) from auth where student=1");
			$q101 = myquery("select id,ime,prezime,brindexa from auth where student=1 order by prezime,ime limit $offset,$limit");
		} else {
			$src = preg_replace("/\s+/"," ",$src);
			$src=trim($src);
			$dijelovi = explode(" ", $src);
			$query = "";
			foreach($dijelovi as $dio) {
				if ($query != "") $query .= "or ";
				$query .= "ime like '%$dio%' or prezime like '%$dio%' or brindexa like '%$dio%' ";
				if (intval($dio)>0) $query .= "or id=".intval($dio)." ";
			}
			$q100 = myquery("select count(*) from auth where ($query) and student=1");
			$q101 = myquery("select id,ime,prezime,brindexa from auth where ($query) and student=1 order by prezime,ime limit $offset,$limit");
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
	</table>
	<?
}





}

?>
