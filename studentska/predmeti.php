<?

// STUDENTSKA/PREDMETI - administracija predmeta, studentska služba



function studentska_predmeti() {

global $userid,$user_siteadmin,$user_studentska, $_api_http_code;

global $_lv_; // Potrebno za genform() iz libvedran


require_once("lib/formgen.php"); // db_dropdown
require_once("lib/predmet.php"); 
require_once("lib/student_predmet.php"); 


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

$akcija = param('akcija');


// AKCIJA: Ogranicenje nastavnika na odredjene grupe

if ($akcija == "ogranicenja") {
	$nastavnik = intval($_REQUEST['nastavnik']);
	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']); // akademska godina

	// Imena stvari
	$q370 = db_query("select ime,prezime from osoba where id=$nastavnik");
	if (db_num_rows($q370)<1) {
		zamgerlog("nepoznat nastavnik u$nastavnik",3);
		zamgerlog2("nepoznat nastavnik", $nastavnik);
		niceerror("Nepoznat nastavnik");
		return;
	}
	$ime = db_result($q370,0,0);
	$prezime = db_result($q370,0,1);
	$q371 = db_query("select naziv from predmet where id=$predmet");
	if (db_num_rows($q371)<1) {
		zamgerlog("nepoznat predmet pp$predmet",3);
		zamgerlog2("nepoznat predmet", $predmet);
		niceerror("Nepoznat predmet");
		return;
	}
	$naziv_predmeta = db_result($q371,0,0);

	?><ul><p>
		<b>Ograničenja za nastavnika <?=$ime." ".$prezime?> na predmetu <?=$naziv_predmeta?></b></p></ul><?

	// Subakcija
	if ($_POST['subakcija']=="izmjena" && check_csrf_token()) {
		// Provjera podataka...
		$q374 = db_query("select id from labgrupa where predmet=$predmet and akademska_godina=$ag");
		$izabrane=0; $grupe=0; $upitdodaj=$upitbrisi=$upitbrisisve="";
		while ($r374 = db_fetch_row($q374)) {
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
				$q375 = db_query("delete from ogranicenje where $upitbrisisve");
			} else {
				$q376 = db_query("delete from ogranicenje where $upitbrisisve");
				$q377 = db_query("insert into ogranicenje values $upitdodaj");
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
	$q372 = db_query("select count(*) from ogranicenje as o, labgrupa as l where o.nastavnik=$nastavnik and o.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag");
	if (db_result($q372,0,0)<1) $nema_ogranicenja=1;

	$q373 = db_query("select id,naziv from labgrupa where predmet=$predmet and akademska_godina=$ag");
	while ($r373 = db_fetch_row($q373)) {
		$dodaj="CHECKED";
		if ($nema_ogranicenja==0) {
			$q374=db_query("select count(*) from ogranicenje where labgrupa=$r373[0] and nastavnik=$nastavnik");
			if (db_result($q374,0,0)==0) $dodaj="";
		}
		?><input type="checkbox" name="lg<?=$r373[0]?>" <?=$dodaj?>> <?=$r373[1]?><br/><?
	}
	?><br/><input type="submit" value=" Izmijeni "> &nbsp; <input type="button" value=" Označi sve " onclick="javascript:checkall(true);"> &nbsp; <input type="button" value=" Poništi sve " onclick="javascript:checkall(false);">
	&nbsp; <input type="button" value=" Nazad " onclick="location.href='?sta=studentska/predmeti&akcija=edit&predmet=<?=$predmet?>&ag=<?=$ag?>';">
	</p></form><?
	
}


// AKCIJA: Kreiranje novog predmeta

else if ($akcija == "novi" && check_csrf_token()) {
	// Naziv predmeta
	$naziv = substr(db_escape($_POST['naziv']), 0, 100);
	if (!preg_match("/\w/", $naziv)) {
		zamgerlog("naziv nije ispravan ($naziv)",3);
		zamgerlog2("naziv nije ispravan", 0, 0, 0, $naziv);
		niceerror("Naziv nije ispravan");
		return;
	}

	// Dodajemo ga u aktuelnu akademsku godinu
	$q200 = db_query("select id from akademska_godina where aktuelna=1");
	if (db_num_rows($q200)<1)
		$q200 = db_query("select id from akademska_godina order by id desc");
	if (db_num_rows($q200)<1) {
		niceerror("Nije definisana nijedna akademska godina. Molimo kontaktirajte administratora sajta.");
		zamgerlog("ne postoji nijedna akademska godina",3);
		zamgerlog2("ne postoji nijedna akademska godina");
		return;
	}
	$ak_god = db_result($q200,0,0);

	// Da li već postoji?
	$q210 = db_query("select id from predmet where naziv='$naziv'");
	if (db_num_rows($q210)>0) {
		$predmet = db_result($q210,0,0);

		// Da li se drži u tekućoj akademskoj godini?
		$q220 = db_query("select count(*) from ponudakursa where predmet=$predmet and akademska_godina=$ak_god");
		if (db_result($q220,0,0)>0) {
			zamgerlog("predmet vec postoji u ovoj ak.god (pp$predmet)",3);
			zamgerlog2("predmet vec postoji u ovoj ak.god", $predmet, $ak_god, 0, $naziv);
			niceerror("Predmet već postoji");
			?><a href="?sta=studentska/predmeti&akcija=edit&predmet=<?=$predmet?>&ag=<?=$ak_god?>">Editovanje predmeta &quot;<?=$naziv?>&quot;</a><?
			return;
		} else {
			// Određujemo najnoviji plan studija 
			// FIXME ovo je naopako jer određujemo studij iz ID-a predmeta na kojem se nudi
			$q225 = db_query("SELECT ps.studij, psp.semestar, psp.obavezan FROM plan_studija ps, plan_studija_predmet psp, pasos_predmeta pp WHERE pp.predmet=$predmet AND pp.id=psp.pasos_predmeta AND psp.plan_studija=ps.id ORDER BY ps.godina_vazenja DESC LIMIT 1");
			if (db_num_rows($q225) > 0) {
				$pstudij = db_result($q225,0,0);
				$psemestar = db_result($q225,0,1);
				$pobavezan = db_result($q225,0,2);
			} else {
				// Izborni predmet
				$q230 = db_query("SELECT ps.studij, psp.semestar, psp.obavezan FROM plan_studija ps, plan_studija_predmet psp, plan_izborni_slot pis, pasos_predmeta pp WHERE pp.predmet=$predmet AND pp.id=pis.pasos_predmeta AND pis.id=psp.plan_izborni_slot AND psp.plan_studija=ps.id ORDER BY ps.godina_vazenja DESC LIMIT 1");
				if (db_num_rows($q230) > 0) {
					$pstudij = db_result($q230,0,0);
					$psemestar = db_result($q230,0,1);
					$pobavezan = db_result($q230,0,2);
				} else {
					// Nema ga nikako u planu studija! Uzimamo podatke iz ponude kursa
					$q235 = db_query("select studij, semestar, obavezan from ponudakursa where predmet=$predmet and akademska_godina=".($ak_god-1));
					if (db_num_rows($q235) > 0) {
						$pstudij = db_result($q235,0,0);
						$psemestar = db_result($q235,0,1);
						$pobavezan = db_result($q235,0,2);
					} else {
						zamgerlog("predmet vec postoji, ali nije se drzao (pp$predmet)",3);
						zamgerlog2("predmet vec postoji, ali nije se drzao", $predmet);
						niceerror("Predmet već postoji, ali nije se držao ni ove ni prošle akademske godine.");
						?><p>Takođe nije definisan ni plan studija. Iz ovih razloga ne možemo automatski kreirati ponude kursa. Koristite editovanje da biste ručno dodali ponude kursa.</p><br/><a href="?sta=studentska/predmeti&amp;akcija=edit&amp;predmet=<?=$predmet?>&ag=<?=$ak_god?>">Editovanje predmeta &quot;<?=$naziv?>&quot;</a><?
						return;
					}
				}
			}
			
			$q240 = db_query("insert into ponudakursa set predmet=$predmet, studij=$pstudij, semestar=$psemestar, obavezan=$pobavezan, akademska_godina=$ak_god");
			$pk = db_insert_id();

			// Ispis i logging
			$naziv_studija = db_get("select naziv from studij where id=$pstudij");
			$ispis = "Kreiram ponudu kursa za predmet $naziv (studij $naziv_studija, semestar $psemestar";
			if ($pobavezan != 1) $ispis .= ", izborni";
			$ispis .= ")";
			nicemessage($ispis);
			zamgerlog ("kreirana ponudakursa za pp$predmet");
			zamgerlog2 ("kreirana ponudakursa", $pk);

			// Kreiram virtualnu labgrupu "Svi studenti"
			$q250 = db_query("insert into labgrupa set naziv='(Svi studenti)', predmet=$predmet, akademska_godina=$ak_god, virtualna=1");

			?><a href="?sta=studentska/predmeti&amp;akcija=edit&amp;predmet=<?=$predmet?>&amp;ag=<?=$ak_god?>">Editovanje predmeta &quot;<?=$naziv?>&quot;</a><?
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
	$q260 = db_query("select id from institucija order by id limit 1");
	$institucija = db_result($q260,0,0);

	// Dodajem predmet u bazu
	$q270 = db_query("insert into predmet set naziv='$naziv', kratki_naziv='$kratki_naziv', institucija=$institucija"); 

	// Koji id predmeta smo dobili?
	$q280 = db_query("select id from predmet where naziv='$naziv'");
	$predmet = db_result($q280,0,0);

	// Potrebno je definisati zapis u tabeli akademska_godina_predmet. Biramo 
	// default tip predmeta (ETF Bologna standard) a korisnik ga može promijeniti kasnije
	$q285 = db_query("select id from tippredmeta order by id limit 1");
	$tippredmeta = db_result($q285,0,0);
	$q287 = db_query("insert into akademska_godina_predmet set akademska_godina=$ak_god, predmet=$predmet, tippredmeta=$tippredmeta");

	// Kreiramo virtualnu labgrupu "Svi studenti"
	$q290 = db_query("insert into labgrupa set naziv='(Svi studenti)', predmet=$predmet, akademska_godina=$ak_god, virtualna=1");

	// Logging
	zamgerlog("potpuno novi predmet pp$predmet, akademska godina ag$ak_god",4);
	zamgerlog2("kreiran novi predmet", intval($predmet), intval($ak_god));

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
		kreiraj_ponudu_kursa($predmet, $studij, $semestar, $ag, $obavezan, $ispis=0, "NULL");
		nicemessage("Ponuda kursa uspješno kreirana");
	}
	$q400 = db_query("select naziv from predmet where id=$predmet");
	$q410 = db_query("select naziv from akademska_godina where id=$ag");
	print "<h3>Nova ponuda kursa za predmet ".db_result($q400,0,0).",<br/> akademska godina ".db_result($q410,0,0)."</h3>";
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
		$q358 = db_query("select id from akademska_godina where aktuelna=1 limit 1");
		$ag = db_result($q358,0,0);
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
			<tr><td align="center"><a href="?sta=izvjestaj/grupe&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>">
			<img src="static/images/32x32/report.png" border="0"><br/>Spisak grupa</a></td></tr>
			<tr><td align="center"><a href="?sta=izvjestaj/predmet&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>&amp;skrati=da">
			<img src="static/images/32x32/report.png" border="0"><br/>Puni izvještaj</a></td></tr><?
			$q359 = db_query("select i.id,UNIX_TIMESTAMP(i.datum), k.gui_naziv from ispit as i, komponenta as k where i.predmet=$predmet and i.akademska_godina=$ag and i.komponenta=k.id order by i.datum,i.komponenta");
			if (db_num_rows($q359)>0) {
				?><tr><td align="center"><a href="?sta=izvjestaj/statistika_predmeta&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>">
				<img src="static/images/32x32/report.png" border="0"><br/>Statistika predmeta</a></td></tr><?
			}
			?>
			<tr><td align="center"><a href="?sta=studentska/prijave&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>">
			<img src="static/images/32x32/report.png" border="0"><br/>Štampanje prijava</a></td></tr>
			<tr><td align="center"><a href="?sta=nastavnik/ispiti&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>">
			<img src="static/images/32x32/report.png" border="0"><br/>Ispiti i prijave</a></td></tr>
			<tr><td align="center"><a href="?sta=nastavnik/unos_ocjene&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>">
			<img src="static/images/32x32/report.png" border="0"><br/>Unos ocjena</a></td></tr>
			
			<tr><td align="center"><a href="?sta=studentska/ogranicenja&amp;predmet=<?=$predmet?>&amp;prikazi=sve">
			<img src="static/images/32x32/report.png" border="0"><br/>Ograničenja</a></td></tr>

			<tr><td align="left">Ispiti:<br/><?
			while ($r359 = db_fetch_row($q359)) {
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
			$q360 = db_query("select count(*) from nastavnik_predmet where nastavnik=$nastavnik and predmet=$predmet and akademska_godina=$ag");
			if (db_result($q360,0,0) < 1) {
				$q361 = db_query("insert into nastavnik_predmet set nastavnik=$nastavnik, predmet=$predmet, akademska_godina=$ag");
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
			zamgerlog("nepoznat nivo pristupa ".db_escape($nivo_pristupa), 3);
			zamgerlog2("nepoznat nivo pristupa", 0, 0, 0, $nivo_pristupa);
			return;
		}

		$q362a = db_query("update nastavnik_predmet set nivo_pristupa='$nivo_pristupa' where nastavnik=$nastavnik and predmet=$predmet and akademska_godina=$ag");
		nicemessage("Promijenjeni nivoi pristupa korisnika na predmetu");
		zamgerlog("nastavnik u$nastavnik dat nivo '$nivo_pristupa' na predmetu pp$predmet",4);
		zamgerlog2("nastavniku data prava na predmetu", $nastavnik, $predmet, $ag, $nivo_pristupa);
	}

	// De-angazman nastavnika sa predmeta
	else if ($_POST['subakcija'] == "izbaci_nastavnika" && check_csrf_token()) {
		$nastavnik = intval($_POST['nastavnik']);
		$q363 = db_query("delete from nastavnik_predmet where nastavnik=$nastavnik and predmet=$predmet and akademska_godina=$ag");
		nicemessage ("Nastavnik više nema pravo pristupa predmetu");
		zamgerlog("nastavnik u$nastavnik izbacen sa predmeta pp$predmet",4);
		zamgerlog2("nastavniku oduzeta prava na predmetu", $nastavnik, $predmet, $ag);
	}

	// Obrisi ponudukursa
	else if ($_POST['subakcija'] == "obrisi_pk" && check_csrf_token()) {
		$ponudakursa = intval($_POST['pk']);

		// Ispisujemo studente sa predmeta radi ispravnog brisanja podataka
		$q364 = db_query("select sp.student, pk.predmet, pk.akademska_godina, o.ime, o.prezime, p.naziv from student_predmet as sp, ponudakursa as pk, osoba as o, predmet as p where pk.id=$ponudakursa and sp.predmet=pk.id and sp.student=o.id and pk.predmet=p.id");
		while ($r364 = db_fetch_row($q364)) {
			$predmet=$r364[1]; $ag=$r364[2]; // za kasnije...
			nicemessage ("Ispisujem studenta $r364[3] $r364[4] sa predmeta $r364[5]");
			// Ova funkcija briše ispite, zadaće, prisustvo i konačnu ocjenu te ispisuje studenta iz labgrupe
			ispis_studenta_sa_predmeta($r364[0], $r364[1], $r364[2]);
		}
		
		// zaostatak u bazi... fixme?
		db_query("delete from komponentebodovi where predmet=$ponudakursa");
		
		// Brišemo ponudu kursa
		$q365 = db_query("delete from ponudakursa where id=$ponudakursa");
		nicemessage ("Ponuda kursa je obrisana");
		zamgerlog("obrisana ponudakursa $ponudakursa (predmet pp$predmet, godina ag$ag)",4);
		zamgerlog2("obrisana ponudakursa", $ponudakursa);
	}

	else if ($_GET['subakcija'] == "deangazuj") {
		$osoba = intval($_GET['osoba']);
		$q367 = db_query("delete from angazman where osoba=$osoba and predmet=$predmet and akademska_godina=$ag");
		nicemessage ("Nastavnik više nije angažovan na predmetu");
		zamgerlog("osoba u$osoba deangazovana sa predmeta pp$predmet, godina $ag", 4);
		zamgerlog2("nastavnik deangazovan sa predmeta", $osoba, $predmet, $ag);
	}



	// Osnovni podaci o predmetu
	$course = api_call("course/$predmet/$ag", [ "resolve" => [ 'CourseUnit', "Institution" ] ] );
	if ($_api_http_code != "200") {
		niceerror("Nepostojeći predmet");
		api_report_bug($course, []);
		return;
	}
	$naziv = $course['courseName'];
	$institucija = $course['CourseUnit']['Institution']['name'];


	?>
	<h3><?=$naziv?></h3>
	<p>Institucija: <b><?=$institucija?></b><br />
	ID: <?=$predmet?></p>

	<?
	
	// Omogućujemo popravku ako ne postoji labgrupa "svi studenti"
	$q356 = db_query("SELECT COUNT(*) FROM labgrupa WHERE predmet=$predmet AND akademska_godina=$ag AND virtualna=1");
	if (db_result($q356,0,0) == 0) niceerror("Ne postoji virtualna labgrupa.");

	?>
	<hr>
	<?



	// Nastavni ansambl

	?><h3>Nastavni ansambl:</h3>
	<ul>
	<?
	if ( count($course['staff']) < 1 )
		print "<li>Niko nije angažovan na ovom predmetu</li>\n";
	foreach($course['staff'] as $staff) {
		if (strstr($staff['Person']['titlesPre'], " dr")) $titlesIn = "dr"; else $titlesIn = "";
		?>
		<li>
			<a href="?sta=studentska/osobe&akcija=edit&osoba=<?=$staff['Person']['id']?>">
				<?=$staff['Person']['surname'] . " $titlesIn " . $staff['Person']['name'] ?>
			</a> - <?=$staff['status']?> (<a href="?sta=studentska/predmeti&akcija=edit&predmet=<?=$predmet?>&ag=<?=$ag?>&subakcija=deangazuj&osoba=<?=$staff['Person']['id']?>">deangažuj</a>)
		</li>
		<?
	}
	
	?>
	</ul>
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
	<input type="hidden" name="akcija" value="edit">
	<input type="hidden" name="subakcija" value="obrisi_pk">
	<input type="hidden" name="pk" value=""></form>
	<?


	if (count($course['courseOfferings']) < 1) {
		?><p><font color="red">Ovaj predmet se trenutno ne nudi nigdje!</font><br/>
		Dodajte ponudu kursa ispod. Dok to ne uradite, predmet neće biti vidljiv, osim kod pretrage ako je izabrana opcija &quot;Sve akademske godine&quot;</p>
		<?
	} else print "<ul>\n";
	foreach($course['courseOfferings'] as $co) {
		// Broj studenata
		$q365 = db_query("select count(*) from student_predmet where predmet=" . $co['id']);
		$brstud = db_result($q365,0,0);
		?>
		<li><?=$co['CourseDescription']['name']?> (<?=$co['CourseDescription']['code']?>, <?=$co['CourseDescription']['ects']?> ECTS)<br> <?=$co['Programme']['name']?>, <?=$co['semester']?>. semestar <? if (!$co['mandatory']) print "(izborni)"?><br><?=$brstud?> studenata<br> (<a href="javascript:onclick=upozorenje2('<?=$co['id']?>','<?=$brstud?>')">obriši ponudu kursa</a>)</li>
		<?
	}
	if (count($course['courseOfferings']) > 0) print "</ul>\n";

	?><a href="?sta=studentska/predmeti&predmet=<?=$predmet?>&ag=<?=$ag?>&akcija=dodaj_pk">Dodaj ponudu kursa</a><?


	// Ranije akademske godine
	$q370 = db_query("select ag.id, ag.naziv from akademska_godina as ag, ponudakursa as pk where pk.predmet=$predmet and pk.akademska_godina=ag.id and ag.id!=$ag group by ag.id order by ag.id");
	if (db_num_rows($q370)>0) {
		?>
		<p>Ovaj predmet se držao i sljedećih godina:
		<?
	}
	while ($r370 = db_fetch_row($q370)) {
		?><a href="?sta=studentska/predmeti&akcija=edit&predmet=<?=$predmet?>&ag=<?=$r370[0]?>"><?=$r370[1]?></a> <?
	}
	if (db_num_rows($q370)>0) { ?></p><? }



	// Prava pristupa na predmetu

	?>
	<hr>
	<p>Osobe sa pravima pristupa na predmetu (<?=$agnaziv?>):</p>
	<?
	$q351 = db_query("select np.nastavnik,np.nivo_pristupa,o.ime,o.prezime from osoba as o, nastavnik_predmet as np where np.nastavnik=o.id and np.predmet=$predmet and np.akademska_godina=$ag order by np.nivo_pristupa, o.prezime, o.ime");
	if (db_num_rows($q351) < 1) {
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
	while ($r351 = db_fetch_row($q351)) {
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
		$q352 = db_query("select l.naziv from ogranicenje as o, labgrupa as l where o.nastavnik=$nastavnik and o.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag");
		if (db_num_rows($q352)<1)
			print "Nema";
		while ($r352 = db_fetch_row($q352)) {
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
	if (db_num_rows($q351) > 0) {
		?>
		</table>
		<?
	}


	// Dodaj nove nastavnike

	?><p>Angažman nastavnika na predmetu:
	<?=genform("POST")?>
	<input type="hidden" name="akcija" value="edit">
	<input type="hidden" name="subakcija" value="dodaj_nastavnika">
	<select name="nastavnik" class="default">'<?
	$q360 = db_query("select o.id, o.prezime, o.ime from osoba as o, privilegije as p where p.osoba=o.id and p.privilegija='nastavnik' order by o.prezime, o.ime");
	while ($r360 = db_fetch_row($q360)) {
		print "<option value=\"$r360[0]\">$r360[1] $r360[2]</option>\n";
	}
	?></select>&nbsp;&nbsp; <input type="submit" value=" Dodaj "></form></p><?


	?></td></tr></table></center><? // Vanjska tabela

}


// Glavni ekran - pretraga

else {
	$years = api_call("zamger/year")['results'];
	$ak_god = int_param('ag');
	if ($ak_god == 0) {
		foreach($years as $year)
			if ($year['isCurrent'])
				$ak_god = $year['id'];
	}
	
	$src = db_escape(param('search'));
	$limit = 20;
	$page = int_param("page");
	if ($page == 0) $page = 1;

	?>
	<table width="100%" border="0"><tr><td align="left">
		<p><b>Pretraga</b><br/>
		Za prikaz svih predmeta na akademskoj godini, ostavite polje za pretragu prazno.</br>
		<?=genform("GET")?>
		<input type="hidden" name="offset" value="0"> <?/*resetujem offset*/?>
		<select name="ag">
			<option value="-1">Sve akademske godine</option>
		<?
		foreach ($years as $year) {
?>
			<option value="<?=$year['id']?>"<? if($year['id'] == $ak_god) print " selected"; ?>><?=$year['name']?></option>
<?
		}
		?></select><br/>
		<input type="text" size="50" name="search" value="<? if ($src!="") print $src?>"> <input type="Submit" value=" Pretraži "></form>
		</p>
	<?
	
	$courses = api_call("course/search", [ "query" => param('search'), "page" => $page, "year" => $ak_god, "resolve" => [ "CourseUnit", "Institution" ] ]);
	if ($_api_http_code != "200") {
		niceerror("Neuspješna pretraga predmeta");
		api_report_bug($courses, []);
		return;
	}

	if ($courses['totalResults'] == 0)
		print "Nema rezultata!";
	else {
		$page = $courses['page']; // If page is changed on backend for some reason
		$kraj = $page * $limit;
		$poc = $kraj - $limit + 1;
		
		if ($courses['totalPages'] > 1) {
			print "Prikazujem rezultate $poc-$kraj od " . $courses['totalResults'] . ". Stranica: ";
	
			for ($i=1; $i <= $courses['totalPages']; $i++) {
				if ($i == $page)
					print "<b>$i</b> ";
				else
					print "<a href=\"".genuri()."&page=$i&_lv_column_akademska_godina=$ak_god\">$i</a> ";
			}
			print "<br/>";
		}
		print "<br/>";

		?>
		<table width="100%" border="0"><?
		$i=$poc;
		foreach ($courses['results'] as $course) {
			?>
			<tr>
				<td><?=$i?>. <?=$course['courseName']?> (<?=$course['CourseUnit']['Institution']['abbrev']?>)</td>
				<td><a href="<?=genuri()?>&amp;akcija=edit&amp;predmet=<?=$course['CourseUnit']['id']?>&amp;ag=<?=$course['AcademicYear']['id']?>">Detalji</a></td>
				<?
			if ($user_siteadmin) {
				?>
				<td><a href="?sta=nastavnik/predmet&amp;predmet=<?=$course['CourseUnit']['id']?>&amp;ag=<?=$course['AcademicYear']['id']?>">Uređivanje predmeta</a></td>
				<?
			}
			?>
			</tr>
			<?
			$i++;
		}
		?>
		</table>
		<?
	}
	?>
		<br/>
		<?=genform("POST")?>
		<input type="hidden" name="akcija" value="novi">
		<b>Novi predmet:</b><br/>
		<input type="text" name="naziv" size="50"> <input type="submit" value=" Dodaj ">
		</form>
		</td></tr>
	</table>
	<?

}


?>
</td></tr></table></center>
<?


}

?>
