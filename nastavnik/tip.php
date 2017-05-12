<?

// NASTAVNIK/TIP - modul koji ce omogućiti definisanja sistema bodovanja na predmetu



function nastavnik_tip() {

global $userid,$user_siteadmin;

require_once("lib/student_predmet.php"); // update_komponente


// Parametri

$predmet = intval($_REQUEST['predmet']);
$ag = intval($_REQUEST['ag']);
$brojK=intval($_REQUEST['komp']);
if(!isset($_REQUEST['komp'])) $brojK=1;
$brojIspita=intval($_REQUEST['brojIspita']);
if(!isset($_REQUEST['brojIspita'])){ $brojIspita=3;}
$brojPrisustva=intval($_REQUEST['brojPrisustva']);
if(!isset($_REQUEST['brojPrisustva'])) {$brojPrisustva=1;}
$brojZadaca=intval($_REQUEST['brojZadaca']);
if(!isset($_REQUEST['brojZadaca'])) {$brojZadaca=1;}

$korak = $_REQUEST['korak']; // Trenutno odabrana opcija u meniju
$akcija = $_REQUEST['akcija']; 


// Naziv predmeta
$q10 = db_query("select naziv from predmet where id=$predmet");
if (db_num_rows($q10)<1) {
	biguglyerror("Nepoznat predmet");
	zamgerlog("ilegalan predmet $predmet",3); //nivo 3: greška
	zamgerlog2("nepoznat predmet", $predmet);
	return;
}
$predmet_naziv = db_result($q10,0,0);

?>


<p>&nbsp;</p>

<p><h3>Sistem bodovanja na predmetu - <?=$predmet_naziv?></h3></p>

<?



// Da li korisnik ima pravo ući u modul?

if (!$user_siteadmin) { // 3 = site admin
	$q15 = db_query("select nivo_pristupa from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
	if (db_num_rows($q15)<1 || db_result($q15,0,0)!="nastavnik") {
		zamgerlog("nastavnik/tip privilegije (predmet pp$predmet)",3);
		zamgerlog2("nije nastavnik na predmetu", $predmet, $ag);
		biguglyerror("Nemate pravo pristupa ovoj opciji");
		return;
	} 
}



// Kreiranje novog tipa predmeta

if ($akcija == "potvrda" && check_csrf_token()) {

	// Odredjujemo stari tip predmeta
	$q20 = db_query("select t.id, t.naziv from akademska_godina_predmet as agp, tippredmeta as t where agp.akademska_godina=$ag and agp.predmet=$predmet and agp.tippredmeta=t.id");
	$stari_tip_predmeta=db_result($q20,0,0);

	// Kreiramo novi tip predmeta i uzimamo njegov id
	// Biramo naziv koji ne postoji već
	$naziv_tipa = substr($predmet_naziv,0,50);
	$q65 = db_query("select count(*) from tippredmeta where naziv='$naziv_tipa'");
	$broj=0;
	while (db_result($q65,0,0)>0) {
		$broj++;
		$naziv_tipa = "$predmet_naziv $broj";
		$q65 = db_query("select count(*) from tippredmeta where naziv='$naziv_tipa'");
	}
	
	$q70 = db_query("INSERT INTO tippredmeta set naziv='$naziv_tipa'");
	$q80 = db_query("select id from tippredmeta where naziv='$naziv_tipa'");
	if (db_num_rows($q80) != 1) { // Ovo se ne bi smjelo desiti!
		niceerror("Naziv predmeta je predugačak. Kontaktirajte administratora");
		zamgerlog("nije pronadjen tacno jedan tip predmeta", 3);
		zamgerlog2("nije pronadjen tacno jedan tip predmeta", $predmet, $ag, 0, $naziv_tipa);

		return;
	}
	$tip_predmeta = db_result($q80,0,0); // -- mora postojati tačno jedan

	// Spašavamo naše novodefinisane komponente
	// Podaci su sačuvani u cookie-jima i samim time im se ne može vjerovati!

	$TabelaKomponenti = $_SESSION['TabelaKomponenti'];
	$BrojKomponenti = count($TabelaKomponenti);

	$prvi_parcijalni_id = $drugi_parcijalni_id = 0;
	$idovi_komponenti = array();

	$potreban_update = false; // Da li je potrebno uopšte ažurirati bodove studentima?

	for ($i=0; $i<$BrojKomponenti; $i++) {
		if ($TabelaKomponenti[$i]['odabrana'] != 1) continue;

		$tipKomponente = intval($TabelaKomponenti[$i]['tip']);
		$guiNaziv = substr(db_escape($TabelaKomponenti[$i]['naziv']), 0, 20); // Dužina naziva je 20 slova
		$kratkiNaziv = substr(db_escape($TabelaKomponenti[$i]['kratkiNaziv']), 0, 20);
		$maxBodova = floatval($TabelaKomponenti[$i]['maxBodova']);
		$prolazBodova = floatval($TabelaKomponenti[$i]['prolazBodova']);
		$opcija = db_escape($TabelaKomponenti[$i]['opcija']);
		if ($TabelaKomponenti[$i]['uslov'] == 1) $uslov=1; else $uslov=0;

		// Kod drugog parcijalnog ispita, za polje opcija treba znati 
		// tačno koji ID su dobile komponente za prvi i drugi parcijalni
		if ($tipKomponente == 2) {
			$opcija="$prvi_parcijalni_id+$drugi_parcijalni_id"; 
			$prvi_parcijalni_id = $drugi_parcijalni_id = 0;
		}

		// Usmeni pretvaramo u običan ispit
		if ($tipKomponente == -1) $tipKomponente = 1;

		// Da li je predmet već imao ovakvu komponentu?
		// Koristimo istu kako bi bili sačuvani bodovi ako je moguće
		$q85 = db_query("select k.id from tippredmeta_komponenta as tpk, komponenta as k where tpk.tippredmeta=$stari_tip_predmeta and tpk.komponenta=k.id and k.gui_naziv='$guiNaziv' and k.kratki_gui_naziv='$kratkiNaziv' and k.tipkomponente=$tipKomponente and k.maxbodova=$maxBodova and k.prolaz=$prolazBodova and k.uslov=$uslov and k.opcija='$opcija'");
		if (db_num_rows($q85)>0) {
			$id_komponente = db_result($q85,0,0);
		} else {
			// Da li uopće postoji takva komponenta?
			$q90 = db_query("select k.id from komponenta as k where k.gui_naziv='$guiNaziv' and k.kratki_gui_naziv='$kratkiNaziv' and k.tipkomponente=$tipKomponente and k.maxbodova=$maxBodova and k.prolaz=$prolazBodova and k.uslov=$uslov and k.opcija='$opcija'");
			if (db_num_rows($q90)>0) {
				$id_komponente = db_result($q90,0,0);
			} else {
				// Kreiramo novu komponentu
				// Ovaj naziv komponente bi trebao biti unique
				// osim ako korisnik da npr. ispitu ime "Zadaća" ili tako nešto :s
				$naziv_komponente = $guiNaziv." (".$tip_predmeta.")";
				$q95 = db_query("INSERT INTO komponenta set naziv='$naziv_komponente', gui_naziv='$guiNaziv', kratki_gui_naziv='$kratkiNaziv', tipkomponente=$tipKomponente, maxbodova=$maxBodova, prolaz=$prolazBodova, uslov=$uslov, opcija='$opcija'");

				$q100 = db_query("select id from komponenta where naziv='$naziv_komponente'");
				$id_komponente = db_result($q100,0,0);
			}


			// Pokušavamo migrirati eventualne postojeće podatke
			// Posljedica će biti da su podaci izvan dozvoljenog opsega, što će korisnik morati popraviti ručno

			if ($tipKomponente == 1 || $tipKomponente == 2) { // ispit
				$q300 = db_query("select i.id, k.kratki_gui_naziv, k.tipkomponente from ispit as i, komponenta as k where i.predmet=$predmet and i.akademska_godina=$ag and i.komponenta=k.id");
				while ($r300 = db_fetch_row($q300)) {
					if ($r300[2]==2 && $tipKomponente==2) {
						$q310 = db_query("update ispit set komponenta=$id_komponente where id=$r300[0]");
						$potreban_update = true;
					}
					if ($r300[2]==1 && $tipKomponente==1 && $r300[1]==$kratkiNaziv) {
						$q310 = db_query("update ispit set komponenta=$id_komponente where id=$r300[0]");
						$potreban_update = true;
					}
				}
			}

			if ($tipKomponente == 3) { // prisustvo
				$q320 = db_query("select c.id from cas as c, labgrupa as l where c.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag");
				while ($r320 = db_fetch_row($q320)) {
					$q330 = db_query("update cas set komponenta=$id_komponente where id=$r320[0]");
					$potreban_update = true;
				}
			}

			if ($tipKomponente == 4) { // zadace
				$q320 = db_query("select count(*) from zadaca where predmet=$predmet and akademska_godina=$ag");
				if (db_num_rows($q320)>0) {
					$q330 = db_query("update zadaca set komponenta=$id_komponente where predmet=$predmet and akademska_godina=$ag");
					$potreban_update = true;
				}
			}

			// Fiksne komponente ignorišemo, ali ih nećemo brisati za slučaj da se korisnik predomisli

		}

		if ($tipKomponente == 1) {
			if ($prvi_parcijalni_id==0) $prvi_parcijalni_id = $id_komponente;
			else if ($drugi_parcijalni_id==0) $drugi_parcijalni_id = $id_komponente;
		}

		$q110 = db_query("INSERT INTO tippredmeta_komponenta set tippredmeta=$tip_predmeta, komponenta=$id_komponente");
		$idovi_komponenti[] = $id_komponente;
	}

	// Od sada ovaj predmet je novokreiranog tipa
	$q120 = db_query("UPDATE akademska_godina_predmet set tippredmeta=$tip_predmeta where akademska_godina=$ag and predmet=$predmet");
	
	// Moramo sada obrisati sve podatke koji nisu migrirani jer mogu praviti razne probleme
	$q400 = db_query("select id, komponenta from ispit where predmet=$predmet and akademska_godina=$ag");
	while (db_fetch2($q400, $ispit, $komponenta)) {
		if (!in_array($komponenta, $idovi_komponenti)) {
			$q410 = db_query("delete from ispitocjene where ispit=$ispit");
			$q420 = db_query("select id from ispit_termin where ispit=$ispit");
			while ($r420 = db_fetch_row($q420)) {
				$termin = $r420[0];
				$q430 = db_query("delete from student_ispit_termin where ispit_termin=$termin");
				$q440 = db_query("delete from ispit_termin where id=$termin");
			}

			zamgerlog2 ("obrisan ispit zbog promjene sistema bodovanja", $predmet, $ag, intval($ispit));
		}
	}
	// Prisustvo
	$q450 = db_query("select c.id, c.komponenta from cas c, labgrupa l where l.predmet=$predmet and l.akademska_godina=$ag and c.labgrupa=l.id");
	while (db_fetch2($q450, $cas, $komponenta)) {
		if (!in_array($komponenta, $idovi_komponenti)) {
			$q460 = db_query("delete from prisustvo where cas=$cas");
			$q470 = db_query("delete from cas where id=$cas");
			zamgerlog2 ("obrisan cas zbog promjene sistema bodovanja", $predmet, $ag, intval($cas));
		}
	}
	// .. tako i za zadaće...

	// Ako nijedan predmet više ne koristi stari tip predmeta, brišemo ga
	$q130 = db_query("select count(*) from akademska_godina_predmet where tippredmeta=$stari_tip_predmeta");
	if (db_result($q130,0,0)==0) {
		$q140 = db_query("delete from tippredmeta where id=$stari_tip_predmeta");

		// Brišemo veze iz tabele tippredmeta_komponenta
		$q150 = db_query("select komponenta from tippredmeta_komponenta where tippredmeta=$stari_tip_predmeta");
		while ($r150 = db_fetch_row($q150)) {
			$q160 = db_query("delete from tippredmeta_komponenta where tippredmeta=$stari_tip_predmeta and komponenta=$r150[0]");

			// Ako nijedan tip predmeta više ne koristi ovu komponentu, brišemo i nju
			$q170 = db_query("select count(*) from tippredmeta_komponenta where komponenta=$r150[0]");
			if (db_result($q170,0,0)==0) 
				$q180 = db_query("delete from komponenta where id=$r150[0]");
		}
	}


	// Updatujemo studentima bodove u tabeli komponentebodovi
	if ($potreban_update) {
		$q185 = db_query("select sp.student, sp.predmet from student_predmet as sp, ponudakursa as pk where sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
		while ($r185 = db_fetch_row($q185)) {
			update_komponente($r185[0], $r185[1], 0);
		}
	}


	zamgerlog("kreiran tip predmeta '$predmet_naziv"."$ag",4);
	zamgerlog2("kreiran tip predmeta", $predmet, $ag, 0, $naziv_tipa);
	nicemessage("Novi sistem bodovanja je potvrđen");
	print "<a href=\"?sta=nastavnik/tip&predmet=$predmet&ag=$ag\">Nazad na početnu stranicu</a>";
	return;
}



// Izbor jednog od postojećih tipova predmeta

if ($akcija == "postojeci_tip_potvrda" && check_csrf_token()) {
	// Odredjujemo stari tip predmeta
	$q20 = db_query("select t.id, t.naziv from akademska_godina_predmet as agp, tippredmeta as t where agp.akademska_godina=$ag and agp.predmet=$predmet and agp.tippredmeta=t.id");
	$stari_tip_predmeta=db_result($q20,0,0);

	$tip_predmeta = intval($_POST['tip_predmeta']); // novi tip predmeta
	$q190 = db_query("UPDATE akademska_godina_predmet SET tippredmeta=$tip_predmeta WHERE predmet=$predmet AND akademska_godina=$ag");

	// Ako nijedan predmet više ne koristi stari tip predmeta, brišemo ga
	$q130 = db_query("select count(*) from akademska_godina_predmet where tippredmeta=$stari_tip_predmeta");
	if (db_result($q130,0,0)==0) {
		$q140 = db_query("delete from tippredmeta where id=$stari_tip_predmeta");

		// Brišemo veze iz tabele tippredmeta_komponenta
		$q150 = db_query("select komponenta from tippredmeta_komponenta where tippredmeta=$stari_tip_predmeta");
		while ($r150 = db_fetch_row($q150)) {
			$q160 = db_query("delete from tippredmeta_komponenta where tippredmeta=$stari_tip_predmeta and komponenta=$r150[0]");

			// Ako nijedan tip predmeta više ne koristi ovu komponentu, brišemo i nju
			$q170 = db_query("select count(*) from tippredmeta_komponenta where komponenta=$r150[0]");
			if (db_result($q170,0,0)==0) 
				$q180 = db_query("delete from komponenta where id=$r150[0]");
		}
	}

	zamgerlog("promijenjen tip predmeta pp".$predmet." u $tip_predmeta",4);
	zamgerlog2("promijenjen tip predmeta", $predmet, $ag, 0, $tip_predmeta);
	nicemessage("Odabran je sistem bodovanja na predmetu");
	print "<a href=\"?sta=nastavnik/tip&predmet=$predmet&ag=$ag\">Nazad na početnu stranicu</a>";
	return;
}



// Izmjena podataka o komponentama smještenih u tabelu komponenti
if ($_POST['izmjena'] == "da" && check_csrf_token()) {
	// Izmjena se vrši na nivou stranice
	// Stranica u principu odgovara svim komponentama određenog tipa (osim završnog ispita)
	$tmpTabelaKomponenti = $_SESSION['TabelaKomponenti'];
	$BrojKomponenti = count($tmpTabelaKomponenti);
	$TabelaKomponenti = array();

	// Izbacujemo sve komponente datog tipa iz niza.
	// Ponovo ćemo ih dodati nakon validacije
	$j=0;
	for ($i=0; $i<$BrojKomponenti; $i++) {
		if ($korak=="fiksne" && $tmpTabelaKomponenti[$i]['tip'] == 5)
			continue;
		if ($korak=="prisustvo" && $tmpTabelaKomponenti[$i]['tip'] == 3)
			continue;
		if ($korak=="zadace" && $tmpTabelaKomponenti[$i]['tip'] == 4)
			continue;
		if ($korak=="ispiti" && ($tmpTabelaKomponenti[$i]['tip'] == 1 || $tmpTabelaKomponenti[$i]['tip'] == 2))
			continue;
		if ($korak=="usmeni" && $tmpTabelaKomponenti[$i]['tip'] == -1)
			continue;
		$TabelaKomponenti[$j++] = $tmpTabelaKomponenti[$i];
	}


	// Validacija podataka
	if ($korak=="fiksne") $broj=$brojK;
	else if ($korak=="ispiti") $broj=$brojIspita;
	else if ($korak=="zadace") $broj=$brojZadaca;
	else if ($korak=="prisustvo") $broj=$brojPrisustva;
	else if ($korak=="usmeni") $broj=1;

	$sveOk=true;
	for ($i=0;$i<$broj;$i++) {
		// Preuzimamo podatke sa forme
		$tipKomponente = intval($_POST['tipKomponente'.$i]);
		$naziv = db_escape($_POST['nazivKomponente'.$i]);
		$kratkiNaziv = db_escape($_POST['kratkiNaziv'.$i]);
		$maxBodova = floatval(str_replace(",",".",$_POST['maxBodova'.$i]));
		$prolazBodova = floatval(str_replace(",",".",$_POST['prolazBodova'.$i]));
		$opcija = db_escape($_POST['opcija'.$i]);
		if (isset($_POST['odabrana'.$i])) $odabrana=1; else $odabrana=0;
		if (isset($_POST['uslov'.$i])) $zauslov=1; else $zauslov=0;

		// Posebni uslovi za vrste komponenti
		if ($kratkiNaziv=="") $kratkiNaziv=$naziv;
		if ($tipKomponente==3) // prisustvo
			$opcija=intval($opcija); // ovdje je opcija broj dozvoljenih izostanaka

		if ($tipKomponente==1) {
			// Kod ispita je vrsta ispita ovisna o redoslijedu:
			// 0 - prvi parcijalni, 1 - drugi parcijalni, 2 - integralni, 
			// 3 i dalje - ostali ispiti (običnog tipa)
			if ($i==0) { 
				$prviParcMax = $maxBodova; $prviParcProlaz = $prolazBodova;
			}
			if ($i==1) { 
				$drugiParcMax = $maxBodova; $drugiParcProlaz = $prolazBodova;
			}
		}
		if ($tipKomponente==2) {
			$maxBodova = $prviParcMax+$drugiParcMax;
			$prolazBodova = $prviParcProlaz+$drugiParcProlaz;
		}


		// Bodovi za prolaz ne smiju biti veći od maksimalnog broja bodova.
		if ($maxBodova<$prolazBodova) {
			niceerror("Maksimalan broj bodova za komponentu mora biti veći od broja bodova potrebnih za prolaz!");
			$sveOk=false;
			break;
		}

		// Neki od parametara za komponentu je ostao nedefinisan
		if ($odabrana==1 && ($maxBodova==0 || $naziv=="")) {
			niceerror("Potrebno je definisati sve parametre kako biste dodali komponentu!");
			$sveOk=false;
			break;
		}
		
		// Da li se naziv ili kratki naziv ponavljaju?
		for ($k=0; $k<$j; $k++) {
			if ($naziv==$TabelaKomponenti[$k]['naziv']) {
				niceerror("Već postoji komponenta sa tim nazivom!");
				$sveOk=false;
				break;
			}
			if ($kratkiNaziv==$TabelaKomponenti[$k]['kratkiNaziv']) {
				niceerror("Već postoji komponenta sa tim kratkim nazivom!");
				$sveOk=false;
				break;
			}
		}
		if (!$sveOk) break;

		// Validacija završena, dodajemo slog u tabelu komponenti
		$TabelaKomponenti[$j]['tip'] = $tipKomponente;
		$TabelaKomponenti[$j]['naziv'] = $naziv;
		$TabelaKomponenti[$j]['kratkiNaziv'] = $kratkiNaziv;
		$TabelaKomponenti[$j]['maxBodova'] = $maxBodova;
		$TabelaKomponenti[$j]['prolazBodova'] = $prolazBodova;
		$TabelaKomponenti[$j]['opcija'] = $opcija;
		$TabelaKomponenti[$j]['uslov'] = $zauslov;
		$TabelaKomponenti[$j]['odabrana'] = $odabrana;
		$j++;
	}

	// Sve je ok, upisujemo komponente u sesiju (meni će ponovo pročitati iz sesije)
	if ($sveOk) {
		$_SESSION['TabelaKomponenti'] = $TabelaKomponenti;
	}
}



// Wizard za kreiranje novog tipa predmeta

if ($akcija == "wizard") {

	// Ispis menija na vrhu ekrana
	?>
	&nbsp;<br>
	<b>Definisanje vlastitog sistema bodovanja</b><br>
	<br><br>
	
	<table cellspacing="0" cellpadding="2" style="border:0px; border-style:solid; border-color:black; margin-left: 0px">
		<tr>
		<?

		if($korak==false) $korak="naziv";
		$registry = array(array("Naziv tipa-->","naziv"),array("Pismeni ispiti-->","ispiti"),array("Zadaće-->","zadace"),array("Prisustvo-->","prisustvo"),array("Završni ispit-->","usmeni"),array("Fiksne komponente-->","fiksne"), array("Pregled","pregled"));
		foreach ($registry as $r) { 
			if ($r[1]==$korak) $bgcolor="#eeeeee"; else $bgcolor="#cccccc";
			?><td height="20"  bgcolor="<?=$bgcolor?>" onmouseover="this.bgColor='#ffffff'" onmouseout="this.bgColor='<?=$bgcolor?>'">
				<a href="?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&akcija=wizard&korak=<?=$r[1]?>&komp=<?=$brojK?>&brojIspita=<?=$brojIspita; ?>&brojZadaca=<?=$brojZadaca?>&brojPrisustva=<?=$brojPrisustva?>" class="malimeni"><?=$r[0]?></a>
			</td>
			<?

			// Linkovi na prethodni i sljedeći korak
			if ($r[1] == $korak) $prethodni_korak = $k;
			if ($k == $korak) $sljedeci_korak = $r[1];
			$k = $r[1];
		}


	?>
	</tr></table>
	<p>&nbsp;</p>
	<?

	// Čitamo do sada definisane podatke iz sesije

	$tmpTabelaKomponenti = $_SESSION['TabelaKomponenti'];
	$BrojKomponenti = count($tmpTabelaKomponenti);
	$TabelaKomponenti = array();

	// Izdvajamo komponente željenog tipa na osnovu odabrane stranice
	$j=0;
	for ($i=0; $i<$BrojKomponenti; $i++) {
		if ($korak=="fiksne" && $tmpTabelaKomponenti[$i]['tip'] == 5)
			$TabelaKomponenti[$j++] = $tmpTabelaKomponenti[$i];
		if ($korak=="prisustvo" && $tmpTabelaKomponenti[$i]['tip'] == 3)
			$TabelaKomponenti[$j++] = $tmpTabelaKomponenti[$i];
		if ($korak=="zadace" && $tmpTabelaKomponenti[$i]['tip'] == 4)
			$TabelaKomponenti[$j++] = $tmpTabelaKomponenti[$i];
		if ($korak=="ispiti" && ($tmpTabelaKomponenti[$i]['tip'] == 1 || $tmpTabelaKomponenti[$i]['tip'] == 2))
			$TabelaKomponenti[$j++] = $tmpTabelaKomponenti[$i];
		if ($korak=="usmeni" && $tmpTabelaKomponenti[$i]['tip'] == -1)
			$TabelaKomponenti[$j++] = $tmpTabelaKomponenti[$i];
	}


	// Meni opcija za definisanje naziva novog tipa predmeta
	if ($korak == "naziv") {

		// Naziv NE može biti isti kao stari naziv pošto ne znamo da li još neki predmeti koriste isti tip
		$novi_naziv = $predmet_naziv;

		$q210 = db_query("select count(*) from tippredmeta where naziv='$novi_naziv'");
		$broj=0;
		while (db_result($q210,0,0)>0) {
			$broj++;
			$novi_naziv = "$predmet_naziv $broj";
			$q210 = db_query("select count(*) from tippredmeta where naziv='$novi_naziv'");
		}

		?>
		</br>
		<font size="2">Naziv vašeg novokreiranog tipa predmeta će biti <?=$novi_naziv?>.</font>
		<br><br><br>
		<?
	}


	// Meni opcija za Fiksne komponente
	else if ($korak == "fiksne") {
		// Definisanje fiksnih komponenti
			
		if ($j>$brojK) $brojK=$j;

		?>
		<div id="fiksne">Ovdje možete definisati vlastite komponente predmeta koje se boduju npr. seminarski rad i slično.</div>
		</br></br>
		<table>
		<tr>
			<td>
				<!-- <form method="post" action=""> -->   
				<? print genform_hani(); ?>
				<input type="hidden" name="izmjena" value="da">
				<input type="hidden" name="sta" value="nastavnik/tip">
				<table border="0">
				<tr bgcolor="#bbbbbb">
					<td>Naziv komponente</td><td>Max. bodova</td><td>Prolaz</td><td>Dodaj</td><td>Uslov</td>
				</tr>
				<?
				for ($i=0;$i<$brojK;$i++) {
					if ($bgcolor=="") $bgcolor="bgcolor=\"#efefef\""; else $bgcolor="";
					?>
					<tr <?=$bgcolor?>>
						<input type="hidden" name="opcija<?=$i?>" value="">
						<input type="hidden" name="tipKomponente<?=$i?>" value="5">
						<td width="30"><input style="text" name="nazivKomponente<?=$i?>" width="29" align="middle" value="<?=$TabelaKomponenti[$i]['naziv']?>"/></td>
						<td width="30"><input style="text" name="maxBodova<?=$i?>" width="29" align="middle" value="<?=$TabelaKomponenti[$i]['maxBodova']?>"/></td>
						<td width="30"><input style="text" name="prolazBodova<?=$i?>" width="29" align="middle" value="<?=$TabelaKomponenti[$i]['prolazBodova']?>"/></td>
						<td width="30"><input type="checkbox" name="odabrana<?=$i?>" value="Dodaj" onclick="this.form.submit();" <? if($TabelaKomponenti[$i]['odabrana']==1) print "checked=\"yes\""; ?> /></td>
						<td width="30"><input type="checkbox" name="uslov<?=$i?>" value="Uslov" onclick="this.form.submit();" <? if($TabelaKomponenti[$i]['uslov']==1) print "checked=\"yes\""; ?>/></td>
					</tr>
				<?
				} ?>
				</table>
				</form>
				</br>
				<a href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&akcija=wizard&korak=fiksne&komp=<?=($brojK+1)?>&brojIspita=<?=$brojIspita?>&brojZadaca=<?=$brojZadaca?>&brojPrisustva=<?=$brojPrisustva?>'><font size="1" color="#000066">Dodaj komponentu</font></a>
				</br></br>
			</td>
			<td width="20 px"></td>
			<td><? pregled_predmeta_bez_naziva($predmet); ?></td>
		</tr>
		</table>
		<?
	} // if ($korak=="fiksne")
	
	
	// Meni opcija za ispite
	else if ($korak == "ispiti") {

		if ($j>$brojIspita) $brojIspita=$j;

		?>
		<table>
		<tr>
			<td>
				<!-- <form method="post" action=""> -->   
				<? print genform_hani(); ?>
				<input type="hidden" name="izmjena" value="da">
				<input type="hidden" name="sta" value="nastavnik/tip">
				<table border="0">
				<tr bgcolor="#bbbbbb">
					<td>Naziv</td><td>Max. bodova</td><td>Prolaz</td><td>Dodaj</td><td>Uslov</td>
				</tr>
				<tr>
					<input type="hidden" name="opcija0" value="">
					<input type="hidden" name="nazivKomponente0" value="I parcijalni">
					<input type="hidden" name="kratkiNaziv0" value="I parc">
					<input type="hidden" name="tipKomponente0" value="1">
					<td width="150">Prvi parcijalni ispit</td>
					<td width="30"><input style="text" name="maxBodova0" width="29" align="middle" value="<?=$TabelaKomponenti[0]['maxBodova']?>" /></td>
					<td width="30"><input style="text" name="prolazBodova0" width="29" align="middle" value="<?=$TabelaKomponenti[0]['prolazBodova']?>" /></td>
					<td width="30"><input type="checkbox" name="odabrana0" value="Dodaj" onclick="this.form.submit();" <? if($TabelaKomponenti[0]['odabrana']==1) print "checked=\"yes\""; ?>/></td>
					<td width="30"><input type="checkbox" name="uslov0" value="Uslov" onclick="this.form.submit();" <? if($TabelaKomponenti[0]['uslov']==1) print "checked=\"yes\""; ?>/></td>
				</tr>
				<tr bgcolor="#efefef">
					<input type="hidden" name="opcija1" value="">
					<input type="hidden" name="nazivKomponente1" value="II parcijalni">
					<input type="hidden" name="kratkiNaziv1" value="II parc">
					<input type="hidden" name="tipKomponente1" value="1">
					<td width="150">Drugi parcijalni ispit</td>
					<td width="30"><input style="text" name="maxBodova1" width="29" align="middle" value="<?=$TabelaKomponenti[1]['maxBodova']?>" /></td>
					<td width="30"><input style="text" name="prolazBodova1" width="29" align="middle" value="<?=$TabelaKomponenti[1]['prolazBodova']?>" /></td>
					<td width="30"><input type="checkbox" name="odabrana1" value="Dodaj" onclick="this.form.submit();" <? if($TabelaKomponenti[1]['odabrana']==1) print "checked=\"yes\""; ?>/></td>
					<td width="30"><input type="checkbox" name="uslov1" value="Uslov" onclick="this.form.submit();" <? if($TabelaKomponenti[1]['uslov']==1) print "checked=\"yes\""; ?>/></td>
				</tr>
				<tr>
					<input type="hidden" name="opcija2" value="">
					<input type="hidden" name="nazivKomponente2" value="Integralni">
					<input type="hidden" name="kratkiNaziv2" value="Int">
					<input type="hidden" name="tipKomponente2" value="2">
					<!-- Biće izračunato automatski: -->
					<input type="hidden" name="maxBodova2" value="0">
					<input type="hidden" name="prolazBodova2" value="0">
					<td width="150">Integralni ispit</td>
					<td width="30"></td>
					<td width="30"></td>
					<td width="30"><input type="checkbox" name="odabrana2" value="Dodaj" onclick="this.form.submit();" <? if($TabelaKomponenti[2]['odabrana']==1) print "checked=\"yes\""; ?>/></td>
					<td width="30"><input type="checkbox" name="uslov2" value="Uslov" onclick="this.form.submit();" <? if($TabelaKomponenti[2]['uslov']==1) print "checked=\"yes\""; ?>/></td>
				</tr>
				<?

				$bgcolor=="";
				for ($i=3;$i<$brojIspita;$i++) {
					if ($bgcolor=="") $bgcolor="bgcolor=\"#efefef\""; else $bgcolor="";
					?>
					<tr <?=$bgcolor?>>
						<input type="hidden" name="opcija<?=$i?>" value="">
						<input type="hidden" name="tipKomponente<?=$i?>" value="1">
						<td width="30"><input style="text" name="nazivKomponente<?=$i?>" width="29" align="middle" value="<?=$TabelaKomponenti[$i]['naziv']?>"/></td>
						<td width="30"><input style="text" name="maxBodova<?=$i?>" width="29" align="middle" value="<?=$TabelaKomponenti[$i]['maxBodova']?>"/></td>
						<td width="30"><input style="text" name="prolazBodova<?=$i?>" width="29" align="middle" value="<?=$TabelaKomponenti[$i]['prolazBodova']?>"/></td>
						<td width="30"><input type="checkbox" name="odabrana<?=$i?>" value="Dodaj"  onclick="this.form.submit();" <? if($TabelaKomponenti[$i]['odabrana']==1) print "checked=\"yes\""; ?> /></td>
						<td width="30"><input type="checkbox" name="uslov<?=$i?>" value="Uslov" onclick="this.form.submit();" <? if($TabelaKomponenti[$i]['uslov']==1) print "checked=\"yes\""; ?> /></td>
					</tr>
					<?
				}
				?>
				</table>
				</form>
			</td>
			<td width="10px">
			</td>
			<td><? pregled_predmeta_bez_naziva($predmet); ?></td>
		</tr>
		<tr>
			<td>
				</br>
				<a href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&akcija=wizard&korak=ispiti&komp=<?=$brojK?>&brojIspita=<?=($brojIspita+1)?>&brojZadaca=<?=$brojZadaca?>&brojPrisustva=<?=$brojPrisustva?>'><font size="1" color="#000066">Dodaj ispit</font></a>
				</br></br>
			</td>
		</tr>
		</table>
		<?
	}
	
	
	// Meni opcija za zadaće
	else if ($korak == "zadace") {

		if ($j>$brojZadaca) $brojZadaca=$j;

		?>
		<table>
		<tr>
			<td>
				<? print genform_hani(); ?>
				<input type="hidden" name="izmjena" value="da">
				<input type="hidden" name="sta" value="nastavnik/tip">
				<table border="0">
				<tr bgcolor="#bbbbbb">
					<td>Naziv</td><td>Max. bodova</td><td>Dodaj</td><td>Uslov</td>
				</tr>
				<tr>
					<input type="hidden" name="opcija0" value="">
					<input type="hidden" name="nazivKomponente0" value="Zadaće">
					<input type="hidden" name="tipKomponente0" value="4">
					<input type="hidden" name="prolazBodova0" value="0">
					<td width="150">Zadaće</td>
					<td width="30"><input style="text" name="maxBodova0" width="29" align="middle" value="<?=$TabelaKomponenti[0]['maxBodova']?>"/></td>
					<td width="30"><input type="checkbox" name="odabrana0" value="Dodaj" onclick="this.form.submit();" <? if($TabelaKomponenti[0]['odabrana']==1) print "checked=\"yes\""; ?>/></td>
					<td width="30"><input type="checkbox" name="uslov0" value="Uslov" onclick="this.form.submit();" <? if($TabelaKomponenti[0]['uslov']==1) print "checked=\"yes\""; ?>/></td>
				</tr>
				<?
				$bgcolor=="bgcolor=\"#efefef\"";
				for ($i=1;$i<$brojZadaca;$i++) {
					if ($bgcolor=="") $bgcolor="bgcolor=\"#efefef\""; else $bgcolor="";
					?>
					<tr <?=$bgcolor?>>
						<input type="hidden" name="opcija<?=$i?>" value="">
						<input type="hidden" name="tipKomponente<?=$i?>" value="4">
						<input type="hidden" name="prolazBodova<?=$i?>" value="0">
						<td width="30"><input style="text" name="nazivKomponente<?=$i?>" width="29" align="middle" value="<?=$TabelaKomponenti[$i]['naziv']?>"/></td>
						<td width="30"><input style="text" name="maxBodova<?=$i?>" width="29" align="middle" value="<?=$TabelaKomponenti[$i]['maxBodova']?>"/></td>
						<td width="30"><input type="checkbox" name="odabrana<?=$i?>" value="Dodaj"  onclick="this.form.submit();" <? if($TabelaKomponenti[$i]['odabrana']==1) print "checked=\"yes\""; ?> /></td>
						<td width="30"><input type="checkbox" name="uslov<?=$i?>" value="Uslov" onclick="this.form.submit();" <? if($TabelaKomponenti[$i]['uslov']==1) print "checked=\"yes\""; ?> /></td>
					</tr>
					<?
				}
				?>
				</table>
				</form>
				</br></br>
			</td>
			<td width="20 px">
			</td>
			<td>
				<? pregled_predmeta_bez_naziva($predmet); ?>
			</td>
		</tr>
		<tr>
			<td>
				</br>
				<? 
				// Samo site admin može dodavati komponente zadaća
				if ($user_siteadmin) { 
					?>
					<a href="?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&akcija=wizard&korak=zadace&komp=<?=$brojK?>&brojIspita=<?=$brojIspita?>&brojZadaca=<?=($brojZadaca+1)?>&brojPrisustva=<?=$brojPrisustva?>"><font size="1" color="#000066">Dodaj komponentu zadaće</font></a>
					</br></br>
				<? } ?>
			</td>
		</tr>
		</table>
		<?
	}
	

	// Meni opcija za prisustvo

	else if ($korak == "prisustvo") {

		if ($j>$brojPrisustva) $brojPrisustva=$j;

		?>
		<table>
		<tr>
			<td>
				<? print genform_hani(); ?>
				<input type="hidden" name="izmjena" value="da">
				<input type="hidden" name="sta" value="nastavnik/tip">
				<table border="0">
				<tr bgcolor="#bbbbbb">
					<td>Naziv</td><td>Max. bodova</td><td>Dozvoljen broj izostanaka</td><td>Dodaj</td><td>Uslov</td>
				</tr>

				<tr>
					<input type="hidden" name="nazivKomponente0" value="Prisustvo">
					<input type="hidden" name="tipKomponente0" value="3">
					<input type="hidden" name="prolazBodova0" value="0">
					<td width="100">Prisustvo</td>
					<td width="30"><input style="text" name="maxBodova0" width="29" align="middle" value="<? print $TabelaKomponenti[0]['maxBodova']?>" /></td>
					<td width="100"><input style="text" name="opcija0" width="29" align="middle" value="<? print $TabelaKomponenti[0]['opcija']?>" /></td>
					<td width="30"><input type="checkbox" name="odabrana0" value="Dodaj" onclick="this.form.submit();" <? if ($TabelaKomponenti[0]['odabrana']==1) print "checked=\"yes\""; ?> /></td>
					<td width="30"><input type="checkbox" name="uslov0" value="Dodaj" onclick="this.form.submit();" <? if ($TabelaKomponenti[0]['uslov']==1) print "checked=\"yes\""; ?>/></td>
				</tr>
				<? 
				$bgcolor=="bgcolor=\"#efefef\"";
				for ($i=1;$i<$brojPrisustva;$i++) {
					if ($bgcolor=="") $bgcolor="bgcolor=\"#efefef\""; else $bgcolor="";
					?>
					<tr <?=$bgcolor?>>
						<input type="hidden" name="tipKomponente<?=$i?>" value="3">
						<input type="hidden" name="prolazBodova<?=$i?>" value="0">
						<td width="30"><input style="text" name="nazivKomponente<?=$i?>" width="29" align="middle" value="<?=$TabelaKomponenti[$i]['naziv']?>"/></td>
						<td width="30"><input style="text" name="maxBodova<?=$i?>" width="29" align="middle" value="<?=$TabelaKomponenti[$i]['maxBodova']?>"/></td>
						<td width="100"><input style="text" name="opcija<?=$i?>" width="29" align="middle" value="<?=$TabelaKomponenti[$i]['opcija']?>" /></td>
						<td width="30"><input type="checkbox" name="odabrana<?=$i?>" value="Dodaj"  onclick="this.form.submit();" <? if($TabelaKomponenti[$i]['odabrana']==1) print "checked=\"yes\""; ?> /></td>
						<td width="30"><input type="checkbox" name="uslov<?=$i?>" value="Uslov" onclick="this.form.submit();" <? if($TabelaKomponenti[$i]['uslov']==1) print "checked=\"yes\""; ?> /></td>
					</tr>
					<?
				}
				?>
				</tr>
				</table>
				</form>
				</br></br>
			</td>
			<td width="20px">
			</td>
			<td>
				<? pregled_predmeta_bez_naziva($predmet); ?>
			</td>
		</tr>
		<tr>
			<td>
				</br>
				<p>Da bi se bodovi za prisustvo skalirali sa brojem izostanaka, pod Dozvoljen broj izostanaka unesite -1.<p>
				</br></br>
				<? 
				// Samo site admin može dodavati komponente prisustva
				if ($user_siteadmin) { 
					?>
					<a href="?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&akcija=wizard&korak=prisustvo&komp=<?=$brojK?>&brojIspita=<?=$brojIspita?>&brojZadaca=<?=$brojZadaca?>&brojPrisustva=<?=($brojPrisustva+1)?>"><font size="1" color="#000066">Dodaj komponentu prisustva</font></a>
					<?
				}
				?>
				</br></br>
			</td>
		</tr>
		</table>
		<?
	}


	// Meni opcija za usmeni ispit
	else if ($korak == "usmeni") {

		// Postoji samo jedan usmeni
		// FIXME: usmeni je tipa 1 tako da se može desiti da bude izbrisan 
		// prilikom otvaranja taba "ispiti"

		?>
		<table>
		<tr>
			<td>
				<? print genform_hani(); ?>
				<input type="hidden" name="izmjena" value="da">
				<input type="hidden" name="sta" value="nastavnik/tip">
				<table border="0">
				<tr bgcolor="#bbbbbb">
					<td></td><td>Max. bodova</td><td>Prolaz</td><td>Dodaj</td>
				</tr>

				<tr>
					<input type="hidden" name="nazivKomponente0" value="Završni ispit">
					<input type="hidden" name="kratkiNaziv0" value="Završni">
					<input type="hidden" name="tipKomponente0" value="-1">
					<td width="150">Završni ispit </td>
					<td width="30"><input style="text" name="maxBodova0" width="29" align="middle" value="<?=$TabelaKomponenti[0]['maxBodova']?>" /></td>
					<td width="30"><input style="text" name="prolazBodova0" width="29" align="middle" value="<?=$TabelaKomponenti[0]['prolazBodova']?>" /></td>
					<td width="30"><input type="checkbox" name="odabrana0" value="Dodaj" onclick="this.form.submit();" <? if($TabelaKomponenti[0]['odabrana']==1) print "checked=\"yes\""; ?> /></td>
				</tr>
				</table>
				</form>
				</br></br>
			</td>
			<td width="10px">
			</td>
			<td>
				<? pregled_predmeta_bez_naziva($predmet); ?>
			</td>
		</tr>
		</table>
		<?
	}
	
	else if ($korak == "pregled") {
		// Posljednje upozorenje
		$q220 = db_query("select count(*) from cas as c, labgrupa as l where c.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag");
		$broj_casova = db_result($q220,0,0);
		$q230 = db_query("select count(*) from zadaca where predmet=$predmet and akademska_godina=$ag");
		$broj_zadaca = db_result($q230,0,0);
		$q240 = db_query("select count(*) from ispit where predmet=$predmet and akademska_godina=$ag");
		$broj_ispita = db_result($q240,0,0);

		if ($broj_casova>0)
			print "Na predmetu imate <b>$broj_casova</b> kreiranih časova sa prisustvom.<br>\n";
		if ($broj_zadaca>0)
			print "Na predmetu imate <b>$broj_zadaca</b> kreiranih zadaća sa ocjenama, poslanim datotekama itd.<br>\n";
		if ($broj_ispita>0)
			print "Na predmetu imate <b>$broj_ispita</b> kreiranih ispita.<br>\n";
		if ($broj_casova+$broj_zadaca+$broj_ispita>0)
			print "<br><font color=\"red\"><b>SVI OVI PODACI ĆE BITI IZGUBLJENI</b> ako kliknete na dugme Spasi!!!</font><br><br><br>\n";


		// Pregled definisanog tipa predmeta
		?>
		</br>
		<font size="2">Pregled definisanog tipa predmeta:</font>
		</br></br>
		<div style="margin-left: 250px">
			<?
			pregled_predmeta_bez_naziva($predmet);
			?>
		</div>
		</br></br>
		<table>
		<tr>
			<td><input type="button" class="default" onclick="location.href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&akcija=wizard&korak=fiksne&komp=<?=$brojK?>&brojIspita=<?=$brojIspita?>&brojZadaca=<?=$brojZadaca?>&brojPrisustva=<?=$brojPrisustva?>';" value="<< Nazad"></td>
			<td align="right" width="450 px">
				<?=genform_hani("POST")?>
				<input type="hidden" name="akcija" value="potvrda">
				<input type="submit" value="Spasi" />     </form>
			</td>
		</tr>
		</table>
		<?
	}

	// Završni linkovi Nazad i Dalje
	if ($korak != "pregled") {
		$nazadlink = "?sta=nastavnik/tip&predmet=$predmet&ag=$ag&akcija=wizard&korak=$prethodni_korak&komp=$brojK&brojIspita=$brojIspita&brojZadaca=$brojZadaca&brojPrisustva=$brojPrisustva";
		$daljelink = "?sta=nastavnik/tip&predmet=$predmet&ag=$ag&akcija=wizard&korak=$sljedeci_korak&komp=$brojK&brojIspita=$brojIspita&brojZadaca=$brojZadaca&brojPrisustva=$brojPrisustva";
		if ($korak == "naziv") $nazadlink = "?sta=nastavnik/tip&predmet=$predmet&ag=$ag";

		?>
		<table>
			<tr>
				<td><input type="button" class="default" onclick="location.href='<?=$nazadlink?>';" value="<< Nazad"></td>
				<td align="right" width="450 px"><input type="button" class="default" onclick="location.href='<?=$daljelink?>';" value="Dalje >>"></td>
			</tr>
		</table>
		<?
	}
}



// Odabir jednog od postojećih tipova predmeta

if ($akcija == "postojeci_tip") {
	$tip_predmeta = intval($_POST['tip_predmeta']);
	if (!$tip_predmeta) $tip_predmeta = 1;
	$_SESSION['spasi']=$pregled;
	
	$q10 = db_query("SELECT id,naziv FROM tippredmeta WHERE id>0 ORDER BY naziv");
	?>
	<? print genform_hani("POST", "zaPregled"); ?>
	<input type="hidden" name="akcija" value="postojeci_tip">
	<input type="hidden" name="sta" value="nastavnik/tip">
	<font size=2 >Izaberite tip predmeta:</font>
	<select name="tip_predmeta" onchange="submit()">
	<?
	while ($r10 = db_fetch_row($q10)) {
		if ($r10[0] == $tip_predmeta) $sel="selected"; else $sel="";
		print "<option $sel value='$r10[0]'>$r10[1]</option>\n";
	}
	?>
	</select>
	</form>
	</br></br>
	Pregled odabranog tipa predmeta:
	</br></br>
	<div style="margin-left: 100px">
	<?
		pregled_predmeta($tip_predmeta);
	?>
	</div>
	</br></br>
	<table>
	<tr>
		<td><input type="button" class="default" onclick="location.href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>';" value="<< Nazad"></td>
		<td align="right" width="450 px">
			<?=genform_hani("POST")?>
			<input type="hidden" name="tip_predmeta" value="<?=$tip_predmeta?>">
			<input type="hidden" name="akcija" value="postojeci_tip_potvrda">
			<input type="submit" value="Spasi" />
			</form>
		</td>
	</tr>
	</table>
	<?
}


// Početna stranica

if ($akcija == "") {
	?>
	<span id="opomena">
		Promjenom sistema bodovanja na predmetu gube se svi pohranjeni podaci o ispitima, zadaćama i sl.
	</span>

	<p><a href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&akcija=postojeci_tip'>->Odaberite postojeći tip predmeta</a><br>
	<a href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&akcija=wizard&korak=naziv'>->Definišite vlastiti sistem bodovanja</a><br>
	</br></br></br>
	
	Trenutno definisane komponente na predmetu:
	</br></br>
	<div style="margin-left: 150px">
	<?

	$q10 = db_query("select tippredmeta from akademska_godina_predmet where predmet=$predmet AND akademska_godina=$ag");
	pregled_predmeta(db_result($q10,0,0));
	
	unset($_SESSION['TabelaKomponenti']);
	?>
	</div>
	<?
}



}


// Funkcija koja daje tabelarni pregled postojećeg tipa predmeta

function pregled_predmeta($tippredmeta) {
	
	?>
	<table border="0">
	<tr bgcolor="#bbbbbb">
		<td>Naziv</td><td>Komponente</td><td>Max. bodova</td><td>Prolaz</td><td>Uslov</td>
	</tr>
	<?

	$q10 = db_query("select naziv from tippredmeta where id=$tippredmeta");
	$bgcolor="";

	?>
	<tr <?=$bgcolor?>>
		<td><input type="text" name="naziv" value="<?=db_result($q10,0,0)?>" readonly="readonly"></td>
		<?
		$q20 = db_query("select k.id, k.gui_naziv, k.maxbodova, k.prolaz, k.uslov, k.tipkomponente from komponenta as k, tippredmeta_komponenta as tpk where k.id=tpk.komponenta and tpk.tippredmeta=$tippredmeta");
		while ($r20 = db_fetch_row($q20)){
			if ($bgcolor=="") $bgcolor="bgcolor=\"#efefef\""; else $bgcolor="";
			?>
			<td><?=$r20[1]?></td>
			<td><?=$r20[2]?></td>
			<td><? if($r20[3]!=0) print $r20[3]; ?></td>
			<td><? if($r20[4]==1) print "Da"; else print "Ne"; ?></td>
			</tr><tr <?=$bgcolor?>><td></td>
			<?
			if ($r20[5] != 2) { // 2 = integralni ispit
				$suma += $r20[2];
				$suma_p += $r20[3];
			}
		}
		?>
	<td> Ukupno</td><td><? print $suma; ?></td><td><? print $suma_p; ?></td></tr>
	</tr>
	</table>
	<?
}



// Funkcija koja daje tabelarni pregled tipa koji se upravo definiše

function pregled_predmeta_bez_naziva() {

	$TabelaKomponenti = $_SESSION['TabelaKomponenti'];
	
	?>
	<table border="0">
	<tr bgcolor="#bbbbbb">
		<td>Komponente</td><td>Max. bodova</td><td>Prolaz</td><td>Uslov</td>
	</tr>
	<?

	$suma=$suma_p=0;
	for ($i=0; $i<count($TabelaKomponenti); $i++) {
		if ($TabelaKomponenti[$i]['odabrana'] != 1) continue;
		?>
		<tr <?=$bgcolor?>>
			<td><?=db_escape($TabelaKomponenti[$i]['naziv'])?></td>
			<td><?=floatval($TabelaKomponenti[$i]['maxBodova'])?></td>
			<td><?=floatval($TabelaKomponenti[$i]['prolazBodova'])?></td>
			<td><? 
			if ($TabelaKomponenti[$i]['tip'] == -1)
				print "&nbsp;"; // Završni ispit
			else if ($TabelaKomponenti[$i]['uslov'] == 1) 
				print "Da"; 
			else 
				print "Ne"; 
			?></td>
		</tr>
		<?
		if ($TabelaKomponenti[$i]['tip'] != 2) { // 2 = integralni ispit
			$suma += $TabelaKomponenti[$i]['maxBodova'];
			$suma_p += $TabelaKomponenti[$i]['prolazBodova'];
		}
	}
	?>
	<tr><td> Ukupno</td><td><?=$suma?></td><td>&nbsp;</td></tr>
	</table>
	<?
	
}


// Funkcija genform_hani uvedena da bi se izbjeglo automatsko popunjavanje hidden polja iz sesije
function genform_hani($method="POST", $name="") {
	global $login;

	if ($method != "GET" && $method != "POST") $method="POST";
	$result = '<form name="'.$name.'" action="" method="'.$method.'">'."\n";

	//   CSRF protection
	//   The generated token is a SHA1 sum of session ID, time()/1000 and userid (in the
	// highly unlikely case that two users get the same SID in a short timespan). The
	// second function checks this token and the second token which uses time()/1000+1.
	// This leaves a 1000-2000 second (cca. 16-33 minutes) window during which an 
	// attacker could potentially discover a users SID and then craft an attack targeting
	// that specific user.

	$result .= '<input type="hidden" name="_lv_csrf_protection_token1" value="'.sha1(session_id().(intval(time()/1000)).$login).'"><input type="hidden" name="_lv_csrf_protection_token2" value="'.sha1(session_id().(intval(time()/1000)+1).$login).'">';

	return $result;
}



?>
