<?

// STUDENTSKA/PREDMETI - administracija predmeta, studentska služba

// v3.9.1.0 (2008/02/19) + Preimenovan bivsi admin_nihada
// v3.9.1.1 (2008/03/04) + Dodajemo upis svih studenata sa studija
// v3.9.1.2 (2008/03/25) + Nova auth tabela
// v3.9.1.3 (2008/04/09) + Nije radila izmjena imena predmeta
// v3.9.1.4 (2008/08/27) + Tabela auth zamijenjena sa osoba, centriran prikaz (radi novog menija), polje aktuelna u tabeli akademska_godina, izdvojen izvjestaj ukupne statistike, link na pretragu za sve ak. godine
// v3.9.1.5 (2008/09/08) + Dodavanje novog predmeta: popravljen neispravan upit, polje aktuelna, kratki naziv
// v3.9.1.6 (2008/10/03) + Popravljen link na detalje nastavnika; poostreni uslovi za subakcije na POST; iskomentirane neke subakcije koje se vise ne koriste; pretraga prebacena na GET radi lakseg back-a
// v3.9.1.7 (2008/12/23) + Link "Uredjivanje predmeta" sada vidljiv samo site adminu (ostali svakako ne mogu pristupiti); subakcija "izbaci" prebacena na POST radi zastite od CSRF
// v4.0.0.0 (2009/02/19) + Release
// v4.0.0.1 (2009/03/12) + Popravljen logging prilikom dodavanja predmeta - log ocekuje ID u tabeli ponudakursa a ne u tabeli predmet
// v4.0.9.1 (2009/03/24) + Prebacena polja ects i tippredmeta iz tabele ponudakursa u tabelu predmet, iskomentarisan dio koda koji se vise ne koristi (vezano za direktan upis studenata na predmet - sad se to radi kroz studentska/osobe gdje je puno prakticnije)
// v4.0.9.2 (2009/03/25) + nastavnik_predmet preusmjeren sa tabele ponudakursa na tabelu predmet
// v4.0.9.3 (2009/03/31) + Tabela ispit preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.4 (2009/04/23) + Popravljeni linkovi na izvjestaj/ispit; dodan ispis IDa predmeta (ID ponudekursa je u URLu)
// v4.0.9.5 (2009/04/29) + Kompletan modul sada radi sa predmetom i akademskom godinom; tabela labgrupa preusmjerena sa ponudekursa na predmet; nesto ciscenja i uredjivanja koda
// v4.0.9.6 (2009/05/06) + Kreiraj virtualnu grupu kod kreiranja predmeta; dodate nedostajuce akcije; malo kozmetike, mogucnost check/uncheck all kod ogranicenja


// TODO: Podatke o angazmanu prebaciti na novu tabelu angazman



function studentska_predmeti() {

global $userid,$user_siteadmin,$user_studentska;

global $_lv_; // Potrebno za genform() iz libvedran


require("lib/manip.php"); // radi ispisa studenata sa predmeta


// Provjera privilegija

if (!$user_studentska && !$user_siteadmin) {
	zamgerlog("nije studentska",3); // 3: error
	biguglyerror("Pristup nije dozvoljen.");
	return;
}



?>
<center>
<table border="0"><tr><td>

<?

$akcija = $_REQUEST['akcija'];


// AKCIJA: Ogranicenje nastavnika na odredjene grupe

if ($akcija == "ogranicenja") {
	$nastavnik = intval($_REQUEST['nastavnik']);
	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']); // akademska godina

	// Imena stvari
	$q370 = myquery("select ime,prezime from osoba where id=$nastavnik");
	if (mysql_num_rows($q370)<1) {
		zamgerlog("nepoznat nastavnik u$nastavnik",3);
		niceerror("Nepoznat nastavnik");
		return;
	}
	$ime = mysql_result($q370,0,0);
	$prezime = mysql_result($q370,0,1);
	$q371 = myquery("select naziv from predmet where id=$predmet");
	if (mysql_num_rows($q371)<1) {
		zamgerlog("nepoznat predmet p$predmet",3);
		niceerror("Nepoznat predmet");
		return;
	}
	$naziv_predmeta = mysql_result($q371,0,0);

	?><ul><p>
	<b>Ograničenja za nastavnika <?=$ime." ".$prezime?> na predmetu <?=$naziv_predmeta?></b></p><?

	// Subakcija
	if ($_POST['subakcija']=="izmjena" && check_csrf_token()) {
		// Provjera podataka...
		$q374 = myquery("select id from labgrupa where predmet=$predmet and akademska_godina=$ag");
		$izabrane=0; $nisuizabrane=0; $grupe=0; $upitdodaj=""; $upitbrisi="";
		while ($r374 = mysql_fetch_row($q374)) {
			$labgrupa = $r374[0];
			if ($_REQUEST['lg'.$labgrupa]) {
				$izabrane++;
				if ($upitdodaj) $upitdodaj .= ",";
				$upitdodaj .= "($nastavnik,$labgrupa)";
			} else {
				$nisuizabrane++;
				if ($upitbrisi) $upitbrisi .= " OR ";
				$upitbrisi .= "(nastavnik=$nastavnik and labgrupa=$labgrupa)";
			}
			$grupe++;
		}
		if ($upit == "") {
			zamgerlog("pokusao ograniciti sve grupe nastavniku u$nastavnik, predmet p$predmet, ag$ag",3);
			niceerror("Nastavnik mora imati pristup barem jednoj grupi");
			print "<br/>Ako ne želite da ima pristup, odjavite ga/je sa predmeta.";
		} else {
			if ($upitbrisi)
				$q375 = myquery("delete from ogranicenje where nastavnik=$nastavnik");
			if ($upitdodaj)
				$q376 = myquery("insert into ogranicenje values $upitdodaj");

			nicemessage ("Postavljena nova ograničenja.");
			zamgerlog("izmijenjena ogranicenja nastavniku u$nastavnik, predmet p$predmet, ag$ag",4);
		}
	}

	// Skripta za (de)selektovanje svih checkboxa

	?>
	<script language="JavaScript">
	function checkall(val) {
		var z;
		for(z=0; z<document.ogranicenjaform.length; z++)
			if (document.ogranicenjaform[z].type=='checkbox')
				document.ogranicenjaform[z].checked=val;
	}
	</script>
	<?


	?>
	<?=genform("POST", "ogranicenjaform")?>
	<input type="hidden" name="subakcija" value="izmjena">
	<p>
	<?
	
	$nema_ogranicenja=0;
	$q372 = myquery("select count(*) from ogranicenje as o, labgrupa as l where o.nastavnik=$nastavnik and o.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag");
	if (mysql_result($q372,0,0)<1) $nema_ogranicenja=1;

	$q373 = myquery("select id,naziv from labgrupa where predmet=$predmet and akademska_godina=$ag");
	while ($r373 = mysql_fetch_row($q373)) {
		$dodaj="CHECKED";
		if ($nema_ogranicenja==0) {
			$q374=myquery("select count(*) from ogranicenje where labgrupa=$r373[0] and nastavnik=$nastavnik");
			if (mysql_result($q374,0,0)==0) $dodaj="";
		}
		?><input type="checkbox" name="lg<?=$r373[0]?>" <?=$dodaj?>> <?=$r373[1]?><br/><?
	}
	?><br/><input type="submit" value=" Izmijeni "> &nbsp; <input type="button" value=" Označi sve " onclick="javascript:checkall(true);"> &nbsp; <input type="button" value=" Poništi sve " onclick="javascript:checkall(false);">
	&nbsp; <input type="button" value=" Nazad " onclick="location.href='?sta=studentska/predmeti&akcija=edit&predmet=<?=$predmet?>&ag=<?=$ag?>';"></form><?
	
}


// AKCIJA: Kreiranje novog predmeta

else if ($_POST['akcija'] == "novi" && check_csrf_token()) {
	// Naziv predmeta
	$naziv = substr(my_escape($_POST['naziv']), 0, 100);
	if (!preg_match("/\w/", $naziv)) {
		zamgerlog("naziv nije ispravan ($naziv)",3);
		niceerror("Naziv nije ispravan");
		return;
	}

	// Dodajemo ga u aktuelnu akademsku godinu
	$q390 = myquery("select id from akademska_godina where aktuelna=1");
	if (mysql_num_rows($q390)<1)
		$q390 = myquery("select id from akademska_godina order by id desc");
	if (mysql_num_rows($q390)<1) {
		niceerror("Nije definisana nijedna akademska godina. Molimo kontaktirajte administratora sajta.");
		zamgerlog("ne postoji nijedna akademska godina",3);
		return;
	}
	$ak_god = mysql_result($q390,0,0);

	// Da li vec postoji?
	$q391 = myquery("select id from predmet where naziv='$naziv'");
	if (mysql_num_rows($q391)>0) {
		$predmet = mysql_result($q391,0,0);
		$q392 = myquery("select id from ponudakursa where predmet=$predmet and akademska_godina=$ak_god");
		if (mysql_num_rows($q392)>0) {
			zamgerlog("predmet vec postoji u ovoj ak.god ($naziv)",3);
			niceerror("Predmet već postoji");
			return;
		}
	} else {
		// Odredjujemo kratki naziv
		$dijelovi = explode(" ",$naziv);
		$kratki_naziv = "";
		foreach ($dijelovi as $dio)
			$kratki_naziv .= strtoupper(substr($dio,0,1));

		$q393 = myquery("insert into predmet set naziv='$naziv', kratki_naziv='$kratki_naziv', tippredmeta=1"); // tippredmeta mora biti definisan
		$q391 = myquery("select id from predmet where naziv='$naziv'");
		$predmet = mysql_result($q391,0,0);
	}

	// Kreiram virtualnu labgrupu "Svi studenti"
	$q393 = myquery("insert into labgrupa set naziv='(Svi studenti)', predmet=$predmet, akademska_godina=$ak_god, virtualna=1");

	// Kreiram jednu ponudukursa sa default vrijednostima (sto ce se moci editovati odmah)
	$q395 = myquery("insert into ponudakursa set predmet=$predmet, akademska_godina=$ak_god, studij=1, semestar=1");

	// Logging
	if (mysql_num_rows($q391)>0) {
		print "Predmet već postoji - dodajem ga u aktuelnu akademsku godinu.<br/><br/>";
		zamgerlog("kreiram ponudu kursa pp$predmet za akademsku godinu ag$ak_god",4);
	} else {
		print "Novi predmet.<br/><br/>";
		zamgerlog("potpuno novi predmet pp$predmet, akademska godina ag$ak_god",4);
	}


	?>
	<script language="JavaScript">
	location.href='<?=genuri()?>&akcija=edit&predmet=<?=$predmet?>&ag=<?=$ak_god?>';
	</script>
	<?
}



// AKCIJA: Izmjena podataka o predmetu

else if ($akcija == "realedit") {
	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']); // akademska godina

	print "<h1>Izmjena podataka o predmetu</h1>";
	if ($_REQUEST['_lv_action']=="edit") {
		nicemessage("Podaci o predmetu izmijenjeni");
		zamgerlog("izmijenjeni podaci o predmetu pp$predmet",4);
	}

	$_lv_['where:id']=$predmet;
	$_lv_['forceedit']=1;
	print db_form("predmet");

	?>
	<p><a href="?sta=studentska/predmeti&akcija=edit&predmet=<?=$predmet?>&ag=<?=$ag?>">Nazad</a></p>
	<?
}



// AKCIJA: Dodavanje ponude kursa

else if ($akcija == "dodaj_pk") {
	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']); // akademska godina

	$q400 = myquery("select naziv from predmet where id=$predmet");
	$q410 = myquery("select naziv from akademska_godina where id=$ag");

	print "<h3>Nova ponuda kursa za predmet ".mysql_result($q400,0,0).",<br/> akademska godina ".mysql_result($q410,0,0)."</h3>";

	$_lv_['where:predmet']=$predmet;
	$_lv_['where:akademska_godina']=$ag;
	$forma=db_form("ponudakursa");

	if ($_REQUEST['_lv_action']=="add") {
		nicemessage("Dodana nova ponuda kursa");
		zamgerlog("dodana ponuda kursa na predmet pp$predmet",4);

	} else {
		print $forma;
	}

	?>
	<p><a href="?sta=studentska/predmeti&akcija=edit&predmet=<?=$predmet?>&ag=<?=$ag?>">Nazad</a></p>
	<?

}



// AKCIJA: Prikaz predmeta

else if ($akcija == "edit") {
	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']); // akademska godina

	$old_search = $_REQUEST['search']; // Za link ispod

	print "<a href=\"?sta=studentska/predmeti&ag=$ag&search=$old_search&offset=".intval($_REQUEST['offset'])."\">Nazad na rezultate pretrage</a><br/><br/>";



	// Izvjestaji

	?>
	<center>
	<table width="700" border="0" cellspacing="0" cellpadding="0"><tr><td width="100" valign="top">
		<table width="100%" border="1" cellspacing="0" cellpadding="0">
			<tr><td bgcolor="#777777" align="center">
				<font color="white"><b>IZVJEŠTAJI:</b></font>
			</td></tr>
			<tr><td align="center"><a href="?sta=izvjestaj/grupe&predmet=<?=$predmet?>&ag=<?=$ag?>">
			<img src="images/32x32/izvjestaj.png" border="0"><br/>Spisak grupa</a></td></tr>
			<tr><td align="center"><a href="?sta=izvjestaj/predmet&predmet=<?=$predmet?>&ag=<?=$ag?>">
			<img src="images/32x32/izvjestaj.png" border="0"><br/>Puni izvještaj</a></td></tr><?
			$q359 = myquery("select i.id,UNIX_TIMESTAMP(i.datum), k.gui_naziv from ispit as i, komponenta as k where i.predmet=$predmet and i.akademska_godina=$ag and i.komponenta=k.id order by i.datum,i.komponenta");
			if (mysql_num_rows($q359)>0) {
				?><tr><td align="center"><a href="?sta=izvjestaj/statistika_predmeta&predmet=<?=$predmet?>&ag=<?=$ag?>">
				<img src="images/32x32/izvjestaj.png" border="0"><br/>Statistika predmeta</a></td></tr><?
			}
			?><tr><td align="left">Ispiti:<br/><?
			while ($r359 = mysql_fetch_row($q359)) {
				$ispit=$r359[0];
				$datum = date("d. m. Y.",$r359[1]);
				$nazivispita=$r359[2];
				?>
				* <a href="?sta=izvjestaj/ispit&ispit=<?=$ispit?>"><?=$nazivispita?><br/>
				(<?=$datum?>)</a><br/>
				<?
			}

			?></td></tr>
		</table>
	</td><td width="10" valign="top">&nbsp;
	</td><td width="590" valign="top">
	<?


	// Submit akcije

	// Angazman nastavnika na predmetu
	if ($_POST['subakcija'] == "dodaj_nastavnika" && check_csrf_token()) {
		$nastavnik = intval($_POST['nastavnik']);
		if ($nastavnik>0) {
			$q360 = myquery("select count(*) from nastavnik_predmet where nastavnik=$nastavnik and predmet=$predmet and akademska_godina=$ag");
			if (mysql_result($q360,0,0) < 1) {
				$q361 = myquery("insert into nastavnik_predmet set nastavnik=$nastavnik, predmet=$predmet, akademska_godina=$ag");
			}
			zamgerlog("nastavnik u$nastavnik dodan na predmet pp$predmet",4);
		}
	}

	// Admin privilegije
	else if ($_GET['subakcija'] == "proglasi_za_admina") {
		$nastavnik = intval($_GET['nastavnik']);

		$yesno = intval($_GET['yesno']);
		$q362 = myquery("update nastavnik_predmet set admin=$yesno where nastavnik=$nastavnik and predmet=$predmet and akademska_godina=$ag");
		zamgerlog("nastavnik u$nastavnik proglasen za admina predmeta pp$predmet ($yesno)",4);
	}

	// De-angazman nastavnika sa predmeta
	else if ($_POST['subakcija'] == "izbaci_nastavnika" && check_csrf_token()) {
		$nastavnik = intval($_POST['nastavnik']);
		$q363 = myquery("delete from nastavnik_predmet where nastavnik=$nastavnik and predmet=$predmet and akademska_godina=$ag");
		zamgerlog("nastavnik u$nastavnik izbacen sa predmeta pp$predmet",4);
	}




	// Osnovni podaci o predmetu

	$q350 = myquery("select id, sifra, naziv, kratki_naziv, institucija, tippredmeta, ects from predmet where id=$predmet");
	if (!($r350 = mysql_fetch_row($q350))) {
		zamgerlog("nepostojeci predmet $predmet",3);
		niceerror("Nepostojeći predmet!");
		return;
	}

	// Oznacicemo neispravne podatke
	$greska=0;
	$naziv=$r350[2]; if (!preg_match("/\w/",$naziv)) { $naziv="<font color=\"red\">Bez naziva!</font>"; $greska=1; }
	$sifra=$r350[1]; if ($sifra=="") { $sifra="<font color=\"red\">(?)</font>"; $greska=1; }
	$kratkinaziv=$r350[3]; if ($kratkinaziv=="") { $kratkinaziv="<font color=\"red\">(?)</font>"; $greska=1; }
	$ects=floatval($r350[6]); if ($ects==0) { $ects="<font color=\"red\">(?)</font>"; $greska=1; }

	// Institucija
	$q352 = myquery("select naziv from institucija where id=$r350[4]");
	if (mysql_num_rows($q352)<1) {
		$institucija="<font color=\"red\">(?)</font>"; $greska=1; 
	} else {
		$institucija = mysql_result($q352,0,0);
	}

	// Tip predmeta
	$q354 = myquery("select naziv from tippredmeta where id=$r350[5]");
	if (mysql_num_rows($q354)<1) {
		$tippredmeta="<font color=\"red\">(?)</font>"; $greska=1; 
	} else {
		$tippredmeta= mysql_result($q354,0,0);
	}

	?>
	<h3><?=$naziv?></h3>
	<p>Šifra predmeta: <b><?=$sifra?></b><br />
	Skraćeni naziv predmeta: <b><?=$kratkinaziv?></b><br />
	Institucija: <b><?=$institucija?></b><br />
	Tip predmeta: <b><?=$tippredmeta?></b><br />
	ECTS: <b><?=$ects?> bodova</b><br />
	ID: <?=$predmet?></p>

	<?
	if ($greska==1) print "<font color=\"red\">Imate grešaka u definiciji predmeta. Kliknite na dugme <b>Izmijeni</b>.</font>\n";

	unset($_REQUEST['akcija']);
	print "\n\n<p>\n".genform("GET");
	?>
	<input type="hidden" name="akcija" value="realedit">
	<input type="submit" value=" Izmijeni "></form></p>

	<hr>
	<?



	// Ponude kursa

	?><h3>Ponude kursa:</h3>
	<?

	// Forma za upozorenje prilikom brisanja ponudekursa
	?>
	<script language="JavaScript">
	function upozorenje2(grupa,broj) {
		var a = confirm("Ovim će sa predmeta biti ispisano "+broj+" studenata, te pobrisani svi ostvareni bodovi i ocjene. Da li ste sigurni?");
		if (a) {
			document.brisanjepkform.pk.value=grupa;
			document.brisanjepkform.submit();
		}
	}
	</script>
	<?=genform("POST", "brisanjepkform")?>
	<input type="hidden" name="subakcija" value="obrisi_pk">
	<input type="hidden" name="pk" value=""></form>
	<?

	// Evt ispis akademske godine
	$q359 = myquery("select naziv, aktuelna from akademska_godina where id=$ag");
	if (mysql_num_rows($q359)<1) {
		zamgerlog("nepostojeca akademska godina $ag",3);
		niceerror("Nepostojeći predmet!");
		return;
	}
	$agnaziv = mysql_result($q359,0,0);
	if (mysql_result($q359,0,1)!=1)
		print "<p>Akademska godina: <b>$agnaziv</b></p>";

	$q360 = myquery("select pk.id, s.naziv, pk.semestar, pk.obavezan from ponudakursa as pk, studij as s where pk.predmet=$predmet and pk.akademska_godina=$ag and pk.studij=s.id");
	if (mysql_num_rows($q360)<1) {
		print "<p>Ovaj predmet se trenutno ne nudi nigdje! Dodajte ponudu kursa ispod.</p>\n";
	} else print "<ul>\n";
	while ($r360 = mysql_fetch_row($q360)) {
		// Broj studenata
		$q365 = myquery("select count(*) from student_predmet where predmet=$r360[0]");
		$brstud = mysql_result($q365,0,0);
		?>
		<li><?=$r360[1]?>, <?=$r360[2]?>. semestar <? if ($r360[3]<1) print "(izborni)"?> (<a href="javascript:onclick=upozorenje2('<?=$r360[0]?>','<?=$brstud?>')">obriši ponudu kursa</a>)</li>
		<?
	}
	if (mysql_num_rows($q360)>0) print "</ul>\n";

	?><a href="?sta=studentska/predmeti&predmet=<?=$predmet?>&ag=<?=$ag?>&akcija=dodaj_pk">Dodaj ponudu kursa</a><?


	// Ranije akademske godine
	$q370 = myquery("select ag.id, ag.naziv from akademska_godina as ag, ponudakursa as pk where pk.predmet=$predmet and pk.akademska_godina=ag.id and ag.id!=$ag group by ag.id order by ag.id");
	if (mysql_num_rows($q370)>0) {
		?>
		<p>Ovaj predmet se držao i sljedećih godina:
		<?
	}
	while ($r370 = mysql_fetch_row($q370)) {
		?><a href="?sta=studentska/predmeti&akcija=edit&predmet=<?=$predmet?>&ag=<?=$r370[0]?>"><?=$r370[1]?></a> <?
	}
	if (mysql_num_rows($q370)>0) print "</p>\n";



	// Nastavnici na predmetu

	?>
	<hr>
	<p>Nastavnici angažovani na predmetu (<?=$agnaziv?>):</p>
	<?
	$q351 = myquery("select np.nastavnik,np.admin,o.ime,o.prezime from osoba as o, nastavnik_predmet as np where np.nastavnik=o.id and np.predmet=$predmet and np.akademska_godina=$ag");
	if (mysql_num_rows($q351) < 1) {
		print "<ul><li>Na predmetu nije angažovan nijedan nastavnik</li></ul>\n";
	} else {
		?>
		<script language="JavaScript">
		function upozorenje(nastavnik) {
			document.izbaciform.nastavnik.value=nastavnik;
			document.izbaciform.submit();
		}
		</script>
		<?=genform("POST", "izbaciform")?>
		<input type="hidden" name="subakcija" value="izbaci_nastavnika">
		<input type="hidden" name="nastavnik" value=""></form>

		<table width="100%" border="1" cellspacing="0"><tr><td>Ime i prezime</td><td>Administrator predmeta</td><td>Ograničenja</td><td>&nbsp;</td></tr><?
	}
	while ($r351 = mysql_fetch_row($q351)) {
		$nastavnik = $r351[0];
		$imeprezime = "$r351[2] $r351[3]";

		if ($r351[1]==1) {
			$alterlink="0";
			$cbstanje="CHECKED";
		} else {
			$alterlink="1";
			$cbstanje="";
		}

		?>
		<tr>
			<td><a href="?sta=studentska/osobe&akcija=edit&osoba=<?=$nastavnik?>"><?=$imeprezime?></td>
			<td>
			<input type="checkbox" onchange="javascript:location.href=\'<?=genuri()?>&subakcija=proglasi_za_admina&nastavnik=<?=$nastavnik?>&yesno=<?=$alterlink?>" <?=$cbstanje?>></td>
			<td><a href="<?=genuri()?>&akcija=ogranicenja&nastavnik=<?=$nastavnik?>"><?

		// Spisak grupa na koje ima ogranicenje
		$q352 = myquery("select l.naziv from ogranicenje as o, labgrupa as l where o.nastavnik=$nastavnik and o.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag");
		if (mysql_num_rows($q352)<1)
			print "Nema";
		while ($r352 = mysql_fetch_row($q352)) {
			// Ljudi daju glupa imena grupama...
			if (!preg_match("/\w/",$r352[0])) 
				$imegrupe = "[Nema imena]";
			else
				$imegrupe = substr($r352[0],0,15);
			print "$imegrupe, ";
		}

			?></a></td>
			<td><a href="javascript:onclick=upozorenje('<?=$nastavnik?>')">Izbaci</a></td>
		</tr>
		<?
	}
	if (mysql_num_rows($q351) > 0) {
		print "</table>\n";
	}


	// Dodaj nove nastavnike

	?><p>Angažman nastavnika na predmetu:
	<?=genform("POST")?>
	<input type="hidden" name="subakcija" value="dodaj_nastavnika">
	<select name="nastavnik" class="default">'<?
	$q360 = myquery("select o.id, o.prezime, o.ime from osoba as o, privilegije as p where p.osoba=o.id and p.privilegija='nastavnik' order by o.prezime, o.ime");
	while ($r360 = mysql_fetch_row($q360)) {
		print "<option value=\"$r360[0]\">$r360[1] $r360[2]</option>\n";
	}
	?></select>&nbsp;&nbsp; <input type="submit" value=" Dodaj "></form></p><?


	?></td></tr></table></center><? // Vanjska tabela

}


// Glavni ekran - pretraga

else {
	$src = my_escape($_REQUEST["search"]);
	$limit = 20;
	$offset = intval($_REQUEST["offset"]);
	$ak_god = intval($_REQUEST["ag"]);
	if ($ak_god == 0) {
		$q299 = myquery("select id from akademska_godina where aktuelna=1 order by naziv desc limit 1");
		$ak_god = mysql_result($q299,0,0);
	}

	?>
	<table width="100%" border="0"><tr><td align="left">
		<p><b>Pretraga</b><br/>
		Za prikaz svih predmeta na akademskoj godini, ostavite polje za pretragu prazno.</br>
		<?=genform("GET")?>
		<input type="hidden" name="offset" value="0"> <?/*resetujem offset*/?>
		<select name="akademska_godina">
			<option value="-1">Sve akademske godine</option>
		<?
		$q295 = myquery("select id,naziv, aktuelna from akademska_godina order by naziv");
		while ($r295=mysql_fetch_row($q295)) {
?>
			<option value="<?=$r295[0]?>"<? if($r295[0]==$ak_god) print " selected"; ?>><?=$r295[1]?></option>
<?
		}
		?></select><br/>
		<input type="text" size="50" name="search" value="<? if ($src!="") print $src?>"> <input type="Submit" value=" Pretraži "></form>
		<br/>
	<?
	if ($ak_god>0 && $src != "") {
		$q300 = myquery("select count(*) from ponudakursa as pk, predmet as p where pk.akademska_godina=$ak_god and p.naziv like '%$src%' and pk.predmet=p.id");
	} else if ($ak_god>0) {
		$q300 = myquery("select count(*) from ponudakursa where akademska_godina=$ak_god");
	} else if ($src != "") {
		$q300 = myquery("select count(*) from ponudakursa as pk, predmet as p where pk.predmet=p.id and p.naziv like '%$src%'");
	} else {
		$q300 = myquery("select count(*) from ponudakursa");
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
					print "<a href=\"".genuri()."&offset=$i&_lv_column_akademska_godina=$ak_god\">$br</a> ";
			}
			print "<br/>";
		}
		print "<br/>";

		if ($ak_god>0 && $src != "") {
			$q301 = myquery("select p.id, p.naziv, ag.naziv, s.kratkinaziv, ag.id from predmet as p, ponudakursa as pk, akademska_godina as ag, studij as s where pk.akademska_godina=ag.id and ag.id=$ak_god and p.naziv like '%$src%' and pk.predmet=p.id and pk.studij=s.id order by ag.naziv desc, p.naziv limit $offset,$limit");
		} else if ($ak_god>0) {
			$q301 = myquery("select p.id, p.naziv, ag.naziv, s.kratkinaziv, ag.id from predmet as p, ponudakursa as pk, akademska_godina as ag, studij as s where pk.akademska_godina=ag.id and ag.id=$ak_god and pk.predmet=p.id and pk.studij=s.id order by ag.naziv desc, p.naziv limit $offset,$limit");
		} else if ($src != "") {
			$q301 = myquery("select p.id, p.naziv, ag.naziv, s.kratkinaziv, ag.id from predmet as p, ponudakursa as pk, akademska_godina as ag, studij as s where pk.akademska_godina=ag.id and p.naziv like '%$src%' and pk.predmet=p.id and pk.studij=s.id order by ag.naziv desc, p.naziv limit $offset,$limit");
		} else {
			$q301 = myquery("select p.id, p.naziv, ag.naziv, s.kratkinaziv, ag.id from predmet as p, ponudakursa as pk, akademska_godina as ag, studij as s where pk.akademska_godina=ag.id and pk.predmet=p.id and pk.studij=s.id order by ag.naziv desc,p.naziv limit $offset,$limit");
		}

		print '<table width="100%" border="0">';
		$i=$offset+1;
		while ($r301 = mysql_fetch_row($q301)) {
			if ($ak_god>0)
				print "<tr><td>$i. $r301[1] ($r301[3])</td>\n";
			else
				print "<tr><td>$i. $r301[1] ($r301[3]) - $r301[2]</td>\n";
			print "<td><a href=\"".genuri()."&akcija=edit&predmet=$r301[0]&ag=$r301[4]\">Detalji</a></td>\n";
			if ($user_siteadmin) print "<td><a href=\"?sta=nastavnik/predmet&predmet=$r301[0]&ag=$r301[4]\">Uređivanje predmeta</a></td></tr>";
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
	</table>
	<?

}


?>
</td></tr></table></center>
<?


}

?>
