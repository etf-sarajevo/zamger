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
// v4.0.9.7 (2009/05/08) + Popravljen typo, popravljeno prosljedjivanje akcija=edit parametra, dodano malo feedback poruka
// v4.0.9.8 (2009/05/20) + Dodajem akciju brisanje ponude kursa; ne prikazuj svaku ponudu kursa zasebno kod pretrage, prikazi predmete bez ponude kursa; akcija novi je pogresno prijavljivala da predmet vec postoji (ali je inace sve radilo ispravno); izbacio sam kreiranje default ponudekursa (svakako je viska), kreiranje virtualne labgrupe se radi samo ako ne postoji u aktuelnoj godini; renumerisem upite
// v4.0.9.9 (2009/09/27) + Popravljena provjera da li je ista izabrano u akciji ogranicenja; popravljeno prosljedjivanje IDa nastavnika kod izbacivanja nastavnika sa predmeta
// v4.0.9.10 (2009/10/20) + Prethodna izmjena je brisala podatke o ogranicenjima na drugim predmetima


// TODO: Podatke o angazmanu prebaciti na novu tabelu angazman



function studentska_predmeti() {

global $userid,$user_siteadmin,$user_studentska;

global $_lv_; // Potrebno za genform() iz libvedran


require("lib/manip.php"); // radi ispisa studenata sa predmeta


// Provjera privilegija

if (!$user_studentska && !$user_siteadmin) {
	zamgerlog("nije studentska",3); // 3: error
	zamgerlog2("nije studentska");
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
		zamgerlog2("nepoznat nastavnik", $nastavnik);
		niceerror("Nepoznat nastavnik");
		return;
	}
	$ime = mysql_result($q370,0,0);
	$prezime = mysql_result($q370,0,1);
	$q371 = myquery("select naziv from predmet where id=$predmet");
	if (mysql_num_rows($q371)<1) {
		zamgerlog("nepoznat predmet pp$predmet",3);
		zamgerlog2("nepoznat predmet", $predmet);
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
		$izabrane=0; $grupe=0; $upitdodaj=$upitbrisi=$upitbrisisve="";
		while ($r374 = mysql_fetch_row($q374)) {
			$labgrupa = $r374[0];
			if ($_REQUEST['lg'.$labgrupa]) {
				$izabrane++;
				if ($upitdodaj) $upitdodaj .= ",";
				$upitdodaj .= "($nastavnik,$labgrupa)";
			} else {
				if ($upitbrisi) $upitbrisi .= " OR ";
				$upitbrisi .= "(nastavnik=$nastavnik AND labgrupa=$labgrupa)";
			}
			if ($upitbrisisve) $upitbrisisve .= " OR ";
			$upitbrisisve .= "(nastavnik=$nastavnik AND labgrupa=$labgrupa)";
			$grupe++;
		}
		if ($upitdodaj == "") {
			zamgerlog("pokusao ograniciti sve grupe nastavniku u$nastavnik, predmet pp$predmet, ag$ag",3);
			zamgerlog2("pokusao ograniciti sve grupe nastavniku", $nastavnik, $predmet, $ag);
			niceerror("Nastavnik mora imati pristup barem jednoj grupi");
			print "<br/>Ako ne želite da ima pristup, odjavite ga/je sa predmeta.";
		} else {
			if ($grupe==$izabrane) { // Sve izabrano
				$q375 = myquery("delete from ogranicenje where $upitbrisisve");
			} else {
				$q376 = myquery("delete from ogranicenje where $upitbrisisve");
				$q377 = myquery("insert into ogranicenje values $upitdodaj");
			}
			nicemessage ("Postavljena nova ograničenja.");
			zamgerlog("izmijenjena ogranicenja nastavniku u$nastavnik, predmet pp$predmet, ag$ag",4);
			zamgerlog2("izmijenjena ogranicenja nastavniku", $nastavnik, $predmet, $ag);
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
		zamgerlog2("naziv nije ispravan", 0, 0, 0, $naziv);
		niceerror("Naziv nije ispravan");
		return;
	}

	// Dodajemo ga u aktuelnu akademsku godinu
	$q200 = myquery("select id from akademska_godina where aktuelna=1");
	if (mysql_num_rows($q200)<1)
		$q200 = myquery("select id from akademska_godina order by id desc");
	if (mysql_num_rows($q200)<1) {
		niceerror("Nije definisana nijedna akademska godina. Molimo kontaktirajte administratora sajta.");
		zamgerlog("ne postoji nijedna akademska godina",3);
		zamgerlog2("ne postoji nijedna akademska godina");
		return;
	}
	$ak_god = mysql_result($q200,0,0);

	// Da li već postoji?
	$q210 = myquery("select id from predmet where naziv='$naziv'");
	if (mysql_num_rows($q210)>0) {
		$predmet = mysql_result($q210,0,0);

		// Da li se drži u tekućoj akademskoj godini?
		$q220 = myquery("select count(*) from ponudakursa where predmet=$predmet and akademska_godina=$ak_god");
		if (mysql_result($q220,0,0)>0) {
			zamgerlog("predmet vec postoji u ovoj ak.god (pp$predmet)",3);
			zamgerlog2("predmet vec postoji u ovoj ak.god", $predmet, $ag, 0, $naziv);
			niceerror("Predmet već postoji");
			?><a href="?sta=studentska/predmeti&akcija=edit&predmet=<?=$predmet?>&ag=<?=$ak_god?>">Editovanje predmeta &quot;<?=$naziv?>&quot;</a><?
			return;
		} else {
			// Određujemo najnoviji plan studija
			$q225 = myquery("select godina_vazenja from plan_studija where predmet=$predmet order by godina_vazenja desc limit 1");
			if (mysql_num_rows($q225)>0) {
				// Biramo ponude kursa iz najnovijeg plana studija
				$q230 = myquery("select studij, semestar, obavezan from plan_studija where predmet=$predmet and godina_vazenja=".mysql_result($q225,0,0));
			} else {
				// Ne postoji plan studija
				// Kopiramo ponude kursa iz prošle godine u ovu
				$q230 = myquery("select studij, semestar, obavezan from ponudakursa where predmet=$predmet and akademska_godina=".($ak_god-1));
			}
			if (mysql_num_rows($q230)<1) {
				zamgerlog("predmet vec postoji, ali nije se drzao (pp$predmet)",3);
				zamgerlog2("predmet vec postoji, ali nije se drzao", $predmet);
				niceerror("Predmet već postoji, ali nije se držao ni ove ni prošle akademske godine.");
				?><p>Takođe nije definisan ni plan studija. Iz ovih razloga ne možemo automatski kreirati ponude kursa. Koristite editovanje da biste ručno dodali ponude kursa.</p><br/><a href="?sta=studentska/predmeti&akcija=edit&predmet=<?=$predmet?>&ag=<?=$ak_god?>">Editovanje predmeta &quot;<?=$naziv?>&quot;</a><?
				return;
			}
			while ($r230 = mysql_fetch_row($q230)) {
				$q240 = myquery("insert into ponudakursa set predmet=$predmet, studij=$r230[0], semestar=$r230[1], obavezan=$r230[2], akademska_godina=$ak_god");
				$pk = mysql_insert_id();

				// Ispis i logging
				$q231 = myquery("select naziv from studij where id=$r230[0]");
				$ispis = "Kreiram ponudu kursa za predmet $naziv (studij ".mysql_result($q231,0,0).", semestar $r230[1]";
				if ($r230[2]!=1) $ispis.=", izborni";
				$ispis.=")";
				nicemessage($ispis);
				zamgerlog ("kreirana ponudakursa za pp$predmet");
				zamgerlog2 ("kreirana ponudakursa", $pk);
			}

			// Kreiram virtualnu labgrupu "Svi studenti"
			$q250 = myquery("insert into labgrupa set naziv='(Svi studenti)', predmet=$predmet, akademska_godina=$ak_god, virtualna=1");

			?><a href="?sta=studentska/predmeti&akcija=edit&predmet=<?=$predmet?>&ag=<?=$ak_god?>">Editovanje predmeta &quot;<?=$naziv?>&quot;</a><?
			return;
		}
	}


	// Kreiranje potpuno novog predmeta

	// Određujemo kratki naziv
	$dijelovi = explode(" ",$naziv);
	$kratki_naziv = "";
	foreach ($dijelovi as $dio)
		$kratki_naziv .= strtoupper(substr($dio,0,1));

	// Polje institucija u tabeli predmet mora biti definisano! 
	// Korisnik ga može promijeniti kasnije
	$q260 = myquery("select id from institucija order by id limit 1");
	$institucija = mysql_result($q260,0,0);

	// Dodajem predmet u bazu
	$q270 = myquery("insert into predmet set naziv='$naziv', kratki_naziv='$kratki_naziv', institucija=$institucija"); 

	// Koji id predmeta smo dobili?
	$q280 = myquery("select id from predmet where naziv='$naziv'");
	$predmet = mysql_result($q280,0,0);

	// Potrebno je definisati zapis u tabeli akademska_godina_predmet. Biramo 
	// default tip predmeta (ETF Bologna standard) a korisnik ga može promijeniti kasnije
	$q285 = myquery("select id from tippredmeta order by id limit 1");
	$tippredmeta = mysql_result($q285,0,0);
	$q287 = myquery("insert into akademska_godina_predmet set akademska_godina=$ak_god, predmet=$predmet, tippredmeta=$tippredmeta");

	// Kreiramo virtualnu labgrupu "Svi studenti"
	$q290 = myquery("insert into labgrupa set naziv='(Svi studenti)', predmet=$predmet, akademska_godina=$ak_god, virtualna=1");

	// Logging
	zamgerlog("potpuno novi predmet pp$predmet, akademska godina ag$ak_god",4);
	zamgerlog2("kreiran novi predmet", $predmet, $ak_god);

	?>
	<p>Kreiran novi predmet pod nazivom <?=$naziv?> sa uobičajenim parametrima. Koristite polja za izmjenu da ih podesite.</p>
	<p>Obavezno definišite barem jednu ponudu kursa, u suprotnom studenti neće moći biti upisani na predmet.</p>
	<a href="?sta=studentska/predmeti&akcija=edit&predmet=<?=$predmet?>&ag=<?=$ak_god?>">Editovanje predmeta &quot;<?=$naziv?>&quot;</a>
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
		zamgerlog2("izmijenjeni podaci o predmetu", $predmet);
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
	if ($_REQUEST['subakcija'] == "potvrda" && check_csrf_token()) {
		$studij = intval($_REQUEST['_lv_column_studij']);
		$semestar = intval($_REQUEST['semestar']);
		if ($_REQUEST['obavezan']) $obavezan=true; else $obavezan=false;
		kreiraj_ponudu_kursa($predmet, $studij, $semestar, $ag, $obavezan, $ispis=0);
		nicemessage("Ponuda kursa uspješno kreirana");
	}
	$q400 = myquery("select naziv from predmet where id=$predmet");
	$q410 = myquery("select naziv from akademska_godina where id=$ag");
	print "<h3>Nova ponuda kursa za predmet ".mysql_result($q400,0,0).",<br/> akademska godina ".mysql_result($q410,0,0)."</h3>";
	unset($_REQUEST['obavezan']);
	print genform("POST");
	?>
	<input type="hidden" name="subakcija" value="potvrda">
	Studij: <?=db_dropdown("studij");?><br><br>
	Semestar: <input type="text" name="semestar" size="5"><br><br>
	<input type="checkbox" name="obavezan"> Obavezan<br><br>
	<input type="submit" value=" Pošalji "> <input type="reset" value=" Poništi "></form>

	<p><a href="?sta=studentska/predmeti&akcija=edit&predmet=<?=$predmet?>&ag=<?=$ag?>">Nazad</a></p>
	<?
}



// AKCIJA: Prikaz predmeta

else if ($akcija == "edit") {
	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']); // akademska godina
	if ($ag==0) {
		// Izaberi aktuelnu akademsku godinu
		$q358 = myquery("select id from akademska_godina where aktuelna=1 limit 1");
		$ag = mysql_result($q358,0,0);
	}

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
			<tr><td align="center"><a href="?sta=izvjestaj/predmet&predmet=<?=$predmet?>&ag=<?=$ag?>&skrati=da">
			<img src="images/32x32/izvjestaj.png" border="0"><br/>Puni izvještaj</a></td></tr><?
			$q359 = myquery("select i.id,UNIX_TIMESTAMP(i.datum), k.gui_naziv from ispit as i, komponenta as k where i.predmet=$predmet and i.akademska_godina=$ag and i.komponenta=k.id order by i.datum,i.komponenta");
			if (mysql_num_rows($q359)>0) {
				?><tr><td align="center"><a href="?sta=izvjestaj/statistika_predmeta&predmet=<?=$predmet?>&ag=<?=$ag?>">
				<img src="images/32x32/izvjestaj.png" border="0"><br/>Statistika predmeta</a></td></tr><?
			}
			?>
			<tr><td align="center"><a href="?sta=studentska/prijave&predmet=<?=$predmet?>&ag=<?=$ag?>">
			<img src="images/32x32/izvjestaj.png" border="0"><br/>Štampanje prijava</a></td></tr>
			<tr><td align="center"><a href="?sta=nastavnik/ispiti&predmet=<?=$predmet?>&ag=<?=$ag?>">
			<img src="images/32x32/izvjestaj.png" border="0"><br/>Ispiti i prijave</a></td></tr>
			<tr><td align="center"><a href="?sta=nastavnik/unos_ocjene&predmet=<?=$predmet?>&ag=<?=$ag?>">
			<img src="images/32x32/izvjestaj.png" border="0"><br/>Unos ocjena</a></td></tr>


			<tr><td align="left">Ispiti:<br/><?
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
			nicemessage ("Nastavniku dato pravo pristupa predmetu");
			zamgerlog("nastavnik u$nastavnik dodan na predmet pp$predmet",4);
			zamgerlog2("nastavniku data prava na predmetu", $nastavnik, $predmet, $ag);
		}
	}

	// Podešavanje privilegija na predmetu
	else if ($_POST['subakcija'] == "postavi_nivo_pristupa" && check_csrf_token()) {
		$nastavnik = intval($_POST['nastavnik']);
		$nivo_pristupa = $_POST['nivo_pristupa'];

		if ($nivo_pristupa != 'nastavnik' && $nivo_pristupa != 'super_asistent' && $nivo_pristupa != 'asistent') {
			niceerror("Nepoznat nivo pristupa");
			zamgerlog("nepoznat nivo pristupa ".my_escape($nivo_pristupa), 3);
			zamgerlog2("nepoznat nivo pristupa", 0, 0, 0, $nivo_pristupa);
			return;
		}

		$q362a = myquery("update nastavnik_predmet set nivo_pristupa='$nivo_pristupa' where nastavnik=$nastavnik and predmet=$predmet and akademska_godina=$ag");
		nicemessage("Promijenjeni nivoi pristupa korisnika na predmetu");
		zamgerlog("nastavnik u$nastavnik dat nivo '$nivo_pristupa' na predmetu pp$predmet",4);
		zamgerlog2("nastavniku data prava na predmetu", $nastavnik, $predmet, $ag, $nivo_pristupa);
	}

	// De-angazman nastavnika sa predmeta
	else if ($_POST['subakcija'] == "izbaci_nastavnika" && check_csrf_token()) {
		$nastavnik = intval($_POST['nastavnik']);
		$q363 = myquery("delete from nastavnik_predmet where nastavnik=$nastavnik and predmet=$predmet and akademska_godina=$ag");
		nicemessage ("Nastavnik više nema pravo pristupa predmetu");
		zamgerlog("nastavnik u$nastavnik izbacen sa predmeta pp$predmet",4);
		zamgerlog2("nastavniku oduzeta prava na predmetu", $nastavnik, $predmet, $ag);
	}

	// Obrisi ponudukursa
	else if ($_POST['subakcija'] == "obrisi_pk" && check_csrf_token()) {
		$ponudakursa = intval($_POST['pk']);

		// Ispisujemo studente sa predmeta radi ispravnog brisanja podataka
		$q364 = myquery("select sp.student, pk.predmet, pk.akademska_godina, o.ime, o.prezime, p.naziv from student_predmet as sp, ponudakursa as pk, osoba as o, predmet as p where pk.id=$ponudakursa and sp.predmet=pk.id and sp.student=o.id and pk.predmet=p.id");
		while ($r364 = mysql_fetch_row($q364)) {
			$predmet=$r364[1]; $ag=$r364[2]; // za kasnije...
			nicemessage ("Ispisujem studenta $r364[3] $r364[4] sa predmeta $r365");
			// Ova funkcija briše ispite, zadaće, prisustvo i konačnu ocjenu te ispisuje studenta iz labgrupe
			ispis_studenta_sa_predmeta($r364[0], $r364[1], $r364[2]);
		}


		// Brišemo ponudu kursa
		$q365 = myquery("delete from ponudakursa where id=$ponudakursa");
		nicemessage ("Ponuda kursa je obrisana");
		zamgerlog("obrisana ponudakursa $ponudakursa (predmet pp$predmet, godina ag$ag)",4);
		zamgerlog2("obrisana ponudakursa", $ponudakursa);
	}

	else if ($_GET['subakcija'] == "deangazuj") {
		$osoba = intval($_GET['osoba']);
		$q367 = myquery("delete from angazman where osoba=$osoba and predmet=$predmet and akademska_godina=$ag");
		nicemessage ("Nastavnik više nije angažovan na predmetu");
		zamgerlog("osoba u$osoba deangazovana sa predmeta pp$predmet, godina $ag", 4);
		zamgerlog2("nastavnik deangazovan sa predmeta", $osoba, $predmet, $ag);
	}



	// Osnovni podaci o predmetu

	$q350 = myquery("SELECT p.id, p.sifra, p.naziv, p.kratki_naziv, p.institucija, agp.tippredmeta, p.ects, p.sati_predavanja, p.sati_vjezbi, p.sati_tutorijala 
	FROM predmet as p, akademska_godina_predmet as agp 
	WHERE p.id=$predmet AND agp.akademska_godina=$ag AND p.id=agp.predmet");
	if (!($r350 = mysql_fetch_row($q350))) {
		$q351 = myquery("SELECT COUNT(*) FROM predmet WHERE id=$predmet");
		if (mysql_result($q351,0,0) > 0) {
			zamgerlog("nedostaje slog u tabeli akademska_godina_predmet $predmet $ag",3);
			zamgerlog2("nedostaje slog u tabeli akademska_godina_predmet", $predmet, $ag);
			niceerror("Nepostojeći predmet (nedostaje agp)!");
		} else {
			zamgerlog("nepostojeci predmet $predmet",3);
			zamgerlog2("nepostojeci predmet", $predmet);
			niceerror("Nepostojeći predmet!");
		}
		return;
	}

	// Oznacicemo neispravne podatke
	$greska=0;
	$naziv=$r350[2]; if (!preg_match("/\w/",$naziv)) { $naziv="<font color=\"red\">Bez naziva!</font>"; $greska=1; }
	$sifra=$r350[1]; if ($sifra=="") { $sifra="<font color=\"red\">(?)</font>"; $greska=1; }
	$kratkinaziv=$r350[3]; if ($kratkinaziv=="") { $kratkinaziv="<font color=\"red\">(?)</font>"; $greska=1; }
	$ects=floatval($r350[6]); if ($ects==0) { $ects="<font color=\"red\">(?)</font>"; $greska=1; }
	// Zašto ne bi bilo nula sati?
	$sati_predavanja=floatval($r350[7]); // if ($sati_predavanja==0) { $sati_predavanja="<font color=\"red\">(?)</font>"; $greska=1; }
	$sati_vjezbi=floatval($r350[8]); // if ($sati_vjezbi==0) { $sati_vjezbi="<font color=\"red\">(?)</font>"; $greska=1; }
	$sati_tutorijala=floatval($r350[9]); // if ($sati_tutorijala==0) { $sati_tutorijala="<font color=\"red\">(?)</font>"; $greska=1; }*/

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
	Sati predavanja: <b><?=$sati_predavanja?> </b><br />
	Sati vježbi: <b><?=$sati_vjezbi?> </b><br />
	Sati tutorijala: <b><?=$sati_tutorijala?> </b><br />
	ID: <?=$predmet?></p>

	<?
	if ($greska==1) print "<font color=\"red\">Imate grešaka u definiciji predmeta. Kliknite na dugme <b>Izmijeni</b>.</font>\n";

	unset($_REQUEST['akcija']);
	print "\n\n<p>\n".genform("GET");
	?>
	<input type="hidden" name="akcija" value="realedit">
	<input type="submit" value=" Izmijeni "></form></p>
	<?
	
	// Omogućujemo popravku ako ne postoji labgrupa "svi studenti"
	$q356 = myquery("SELECT COUNT(*) FROM labgrupa WHERE predmet=$predmet AND akademska_godina=$ag AND virtualna=1");
	if (mysql_result($q356,0,0) == 0) niceerror("Ne postoji virtualna labgrupa.");

	?>
	<hr>
	<?



	// Nastavni ansambl

	?><h3>Nastavni ansambl:</h3>
	<ul>
	<?

	$q355 = myquery("select o.id, angs.naziv from angazman as a, osoba as o, angazman_status as angs where a.predmet=$predmet and a.akademska_godina=$ag and a.osoba=o.id and a.angazman_status=angs.id order by angs.id, o.prezime");
	if (mysql_num_rows($q355)<1) print "<li>Niko nije angažovan na ovom predmetu</li>\n";
	while ($r355 = mysql_fetch_row($q355)) {
		print "<li><a href=\"?sta=studentska/osobe&akcija=edit&osoba=$r355[0]\">".tituliraj($r355[0], false, false, true)."</a> - $r355[1] (<a href=\"?sta=studentska/predmeti&akcija=edit&predmet=$predmet&ag=$ag&subakcija=deangazuj&osoba=$r355[0]\">deangažuj</a>)</li>\n";
	}
	print "</ul>\n";



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
	<input type="hidden" name="akcija" value="edit">
	<input type="hidden" name="subakcija" value="obrisi_pk">
	<input type="hidden" name="pk" value=""></form>
	<?

	// Evt ispis akademske godine
	$q359 = myquery("select naziv, aktuelna from akademska_godina where id=$ag");
	if (mysql_num_rows($q359)<1) {
		zamgerlog("nepostojeca akademska godina $ag",3);
		zamgerlog2("nepostojeca akademska godina", $ag);
		niceerror("Nepostojeća akademska godina!");
		return;
	}
	$agnaziv = mysql_result($q359,0,0);
	if (mysql_result($q359,0,1)!=1)
		print "<p>Akademska godina: <b>$agnaziv</b></p>";

	$q360 = myquery("select pk.id, s.naziv, pk.semestar, pk.obavezan from ponudakursa as pk, studij as s where pk.predmet=$predmet and pk.akademska_godina=$ag and pk.studij=s.id");
	if (mysql_num_rows($q360)<1) {
		?><p><font color="red">Ovaj predmet se trenutno ne nudi nigdje!</font><br/>
		Dodajte ponudu kursa ispod. Dok to ne uradite, predmet neće biti vidljiv, osim kod pretrage ako je izabrana opcija &quot;Sve akademske godine&quot;</p>
		<?
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



	// Prava pristupa na predmetu

	?>
	<hr>
	<p>Osobe sa pravima pristupa na predmetu (<?=$agnaziv?>):</p>
	<?
	$q351 = myquery("select np.nastavnik,np.nivo_pristupa,o.ime,o.prezime from osoba as o, nastavnik_predmet as np where np.nastavnik=o.id and np.predmet=$predmet and np.akademska_godina=$ag order by np.nivo_pristupa, o.prezime, o.ime");
	if (mysql_num_rows($q351) < 1) {
		print "<ul><li>Nijedan nastavnik nema pravo pristupa predmetu.</li></ul>\n";
	} else {
		?>
		<script language="JavaScript">
		function upozorenje(nastavnik) {
			document.izbaciform.nastavnik.value=nastavnik;
			document.izbaciform.submit();
		}
		</script>
		<?=genform("POST", "izbaciform")?>
		<input type="hidden" name="akcija" value="edit">
		<input type="hidden" name="subakcija" value="izbaci_nastavnika">
		<input type="hidden" name="nastavnik" id="nastavnik" value=""></form>

		<table width="100%" border="1" cellspacing="0"><tr><td>Ime i prezime</td><td>Nivo pristupa</td><td>Ograničenja</td><td>&nbsp;</td></tr><?
	}
	while ($r351 = mysql_fetch_row($q351)) {
		$nastavnik = $r351[0];
		$imeprezime = "$r351[2] $r351[3]";
		$nivo_pristupa = $r351[1];

		if ($nivo_pristupa=='nastavnik') {
			$option_nastavnik="SELECTED";
			$option_sa=$option_asistent="";
		} else if ($nivo_pristupa=='super_asistent') {
			$option_sa="SELECTED";
			$option_nastavnik=$option_asistent="";
		} else if ($nivo_pristupa=='asistent') {
			$option_asistent="SELECTED";
			$option_nastavnik=$option_sa="";
		}

		?>
		<tr>
			<td><a href="?sta=studentska/osobe&akcija=edit&osoba=<?=$nastavnik?>"><?=$imeprezime?></td>
			<td><?=genform("POST")?>
				<input type="hidden" name="akcija" value="edit">
				<input type="hidden" name="nastavnik" value="<?=$nastavnik?>">
				<input type="hidden" name="subakcija" value="postavi_nivo_pristupa">
				<select name="nivo_pristupa" class="default">
					<option value="nastavnik" <?=$option_nastavnik?>>Nastavnik</option>
					<option value="super_asistent" <?=$option_sa?>>Super-asistent</option>
					<option value="asistent" <?=$option_asistent?>>Asistent</option>
				</select>
				<input type="submit" class="default" value=" Postavi ">
				</form>
			</td>
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
	<input type="hidden" name="akcija" value="edit">
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
		<select name="ag">
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
	if ($ak_god>=0 && $src != "") {
		$q300 = myquery("select count(distinct pk.predmet) from ponudakursa as pk, predmet as p where pk.akademska_godina=$ak_god and (p.naziv like '%$src%' or p.kratki_naziv like '%$src%') and pk.predmet=p.id");
	} else if ($ak_god>=0) {
		$q300 = myquery("select count(distinct pk.predmet) from ponudakursa as pk where pk.akademska_godina=$ak_god");
	} else if ($src != "") {
		$q300 = myquery("select count(*) from predmet as p where (p.naziv like '%$src%' or p.kratki_naziv like '%$src%')");
	} else {
		$q300 = myquery("select count(*) from predmet as p");
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

		if ($ak_god>=0 && $src != "") {
			$q301 = myquery("select distinct p.id, p.naziv, i.kratki_naziv, ag.id, ag.naziv from predmet as p, ponudakursa as pk, akademska_godina as ag, institucija as i where pk.akademska_godina=ag.id and ag.id=$ak_god and (p.naziv like '%$src%' or p.kratki_naziv like '%$src%') and pk.predmet=p.id and p.institucija=i.id order by ag.naziv desc, p.naziv limit $offset,$limit");
		} else if ($ak_god>=0) {
			$q301 = myquery("select distinct p.id, p.naziv, i.kratki_naziv, ag.id, ag.naziv from predmet as p, ponudakursa as pk, akademska_godina as ag, institucija as i where pk.akademska_godina=ag.id and ag.id=$ak_god and pk.predmet=p.id and p.institucija=i.id order by ag.naziv desc, p.naziv limit $offset,$limit");
		} else if ($src != "") {
			$q301 = myquery("select distinct p.id, p.naziv, i.kratki_naziv, 1 from predmet as p, institucija as i where (p.naziv like '%$src%' or p.kratki_naziv like '%$src%') and p.institucija=i.id order by p.naziv limit $offset,$limit");
		} else {
			$q301 = myquery("select distinct p.id, p.naziv, i.kratki_naziv, 1 from predmet as p, institucija as i where p.institucija=i.id order by p.naziv limit $offset,$limit");
		}

		print '<table width="100%" border="0">';
		$i=$offset+1;
		while ($r301 = mysql_fetch_row($q301)) {
			print "<tr><td>$i. $r301[1] ($r301[2])</td>\n";
			print "<td><a href=\"".genuri()."&akcija=edit&predmet=$r301[0]&ag=$r301[3]\">Detalji</a></td>\n";
			if ($user_siteadmin) print "<td><a href=\"?sta=nastavnik/predmet&predmet=$r301[0]&ag=$r301[3]\">Uređivanje predmeta</a></td></tr>";
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
