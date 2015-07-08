<?

// STUDENT/PROJEKTI - studenski modul za prijavu na projekte i ulazak u projektnu stranu projekta

// v4.0.9.0 (2009/07/08) - originalna verzija by Haris Agic
// v4.0.9.1 (2009/10/04) - Dodana provjera da li su definisani predmetni parametri; dodana poruka ako nema definisanih timova; uljepsan kod, izbaceno viska provjera primljenih varijabli, importovan kod iz lib/projekti

function student_projekti() {

	require_once("lib/projekti.php");

	//debug mod aktivan
	global $userid, $user_student;

	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);	
	
	// Da li student slusa predmet?
	$q10 = myquery("select sp.predmet from student_predmet as sp, ponudakursa as pk where sp.student=$userid and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
	if (mysql_num_rows($q10)<1) {
		zamgerlog("student ne slusa predmet pp$predmet", 3);
		zamgerlog2("student ne slusa predmet", $predmet, $ag);
		biguglyerror("Niste upisani na ovaj predmet");
		return;
	}
	
	$linkprefix = "?sta=student/projekti&predmet=$predmet&ag=$ag";
	$akcija = $_REQUEST['akcija'];
	$id = intval($_REQUEST['id']);



	// KORISNI UPITI

	// Spisak svih projekata
	$q20 = myquery("SELECT id, naziv, opis, vrijeme FROM projekat WHERE predmet=$predmet AND akademska_godina=$ag ORDER BY vrijeme DESC");
	$svi_projekti = array();
	while ($r20 = mysql_fetch_assoc($q20))
		$svi_projekti[] = $r20;

	// Broj članova po projektu
	$broj_studenata = array();
	$q30 = myquery("select p.id, count(sp.student) FROM projekat as p, student_projekat as sp WHERE p.id=sp.projekat AND p.predmet=$predmet AND p.akademska_godina=$ag GROUP BY sp.projekat");
	while ($r30 = mysql_fetch_row($q30))
		$broj_studenata[$r30[0]]=$r30[1];

	// Da li je student upisan u neki projekat?
	$clan_projekta = 0;
	$q40 = myquery("SELECT p.id FROM projekat as p, student_projekat as sp WHERE p.id=sp.projekat AND sp.student=$userid AND p.predmet=$predmet AND p.akademska_godina=$ag LIMIT 1");
	if (mysql_num_rows($q40)>0) 
		$clan_projekta = mysql_result($q40,0,0);

	// Parametri projekata na predmetu
	$q50 = myquery("SELECT min_timova, max_timova, min_clanova_tima, max_clanova_tima, zakljucani_projekti FROM predmet_projektni_parametri WHERE predmet='$predmet' AND akademska_godina='$ag' LIMIT 1");
	if (mysql_num_rows($q50)<1) {
		niceerror("Predmetni nastavnik nije podesio parametre projekata.");
		print "Prijavljivanje na projekte za sada nije moguće. Obratite se predmetnom nastavniku ili asistentu za dodatne informacije.";
		return;
	}

	$min_timova = mysql_result($q50,0,0);
	$max_timova = mysql_result($q50,0,1);
	$min_clanova_tima = mysql_result($q50,0,2);
	$max_clanova_tima = mysql_result($q50,0,3);
	$zakljucani_projekti = mysql_result($q50,0,4);


	// Da li je dostignut limit broja timova?
	$brtimova=0;
	foreach ($svi_projekti as $projekat) {
		if ($broj_studenata[$projekat[id]]>0) $brtimova++;
	}
	$limit_timova=false;
	if ($brtimova>=$max_timova) {
		$limit_timova=true;

		// No ako je student trenutno član projekta sa samo jednim članom,
		// istupanjem iz tima otvoriće se mogućnost kreiranja novog tima
		if ($clan_projekta>0 && $broj_studenata[$clan_projekta]==1) $limit_timova=false;
	}


	// Stylesheet... čemu?
	?>
	<LINK href="css/projekti.css" rel="stylesheet" type="text/css">
	<?


	// Akcije

	if ($akcija == 'prijava') {
		$projekat = intval($_REQUEST['projekat']);

		// Da li je projekat sa ovog predmeta?
		$nasao=false;
		foreach ($svi_projekti as $proj) {
			if ($proj[id]==$projekat) { $nasao=true; break; }
		}

		if ($nasao==false) {
			niceerror("Nepoznat projekat!");
			zamgerlog("prijava na projekat $projekat koji nije sa predmeta pp$predmet", 3);
			zamgerlog2("projekat i predmet ne odgovaraju", $projekat, $predmet);
		} 

		else if ($zakljucani_projekti) {
			niceerror("Zaključane su prijave na projekte.");
			zamgerlog("prijava na projekat $projekat koji je zaključan na predmetu pp$predmet", 3);
			zamgerlog2("projekat zakljucan", $projekat);
		}

		else if ($broj_studenata[$projekat]>=$max_clanova_tima) {
			niceerror("Dosegnut je limit broja članova po projektu.");
			zamgerlog("prijava na projekat $projekat koji je popunjen", 3);
			zamgerlog2("projekat popunjen", $projekat);
		}

		else if ($broj_studenata[$projekat]==0 && $limit_timova) {
			niceerror("Dosegnut je maksimalan broj timova. Ne možete kreirati novi tim.");
			zamgerlog("dosegnut limit broja timova na predmetu pp$predmet", 3);
			zamgerlog2("dosegnut limit broja timova", $predmet);
		}

		else {
			// Upisujemo u novi projekat
			$q110 = myquery("INSERT INTO student_projekat SET student=$userid, projekat=$projekat");
			nicemessage("Uspješno ste prijavljeni na projekat");
			zamgerlog("student upisan na projekat $projekat (predmet pp$predmet)", 2);
			zamgerlog2("prijavljen na projekat", $projekat);
			// Ispisujemo studenta sa postojećih projekata
			if ($clan_projekta>0) {
				$q100 = myquery("DELETE FROM student_projekat WHERE student=$userid AND projekat=$clan_projekta");
				nicemessage("Odjavljeni ste sa starog projekta");
				zamgerlog("student ispisan sa projekta $projekat (predmet pp$predmet)", 2);
				zamgerlog2("odjavljen sa starog projekta", $projekat);
			}
		}

		print '<a href="'.$linkprefix.'">Povratak.</a>';
		return;
	} // akcija == prijava


	if ($akcija == 'odjava') {
		$projekat = intval($_REQUEST['projekat']);
		
		// Da li je projekat sa ovog predmeta?
		$nasao=false;
		foreach ($svi_projekti as $proj) {
			if ($proj[id]==$projekat) { $nasao=true; break; }
		}

		if ($nasao==false) {
			niceerror("Nepoznat projekat!");
			zamgerlog("odjava sa projekta $projekat koji nije sa predmeta pp$predmet", 3);
			zamgerlog2("projekat i predmet ne odgovaraju (odjava)", $projekat, $predmet);
		}

		else if ($zakljucani_projekti) {
			niceerror("Zaključane su liste timova za projekte. Odustajanja nisu dozvoljena.");
			zamgerlog("odjava sa projekta $projekat koji je zakljucan na predmetu pp$predmet", 3);
			zamgerlog2("projekat zakljucan (odjava)", $projekat);
		}

		else if ($projekat != $clan_projekta) {
			niceerror("Niste prijavljeni na ovaj projekat");
			zamgerlog("odjava sa projekta $projekat na koji nije prijavljen", 3);
			zamgerlog2("odjava sa projekta na koji nije prijavljen", $projekat);
		}

		else {
			$q120 = myquery("DELETE FROM student_projekat WHERE student=$userid AND projekat=$projekat");
			nicemessage("Uspješno ste odjavljeni sa projekta");
			zamgerlog("student ispisan sa projekta $projekat (predmet pp$predmet)", 2);
			zamgerlog2("odjavljen sa projekta", $projekat);
		}

		print '<a href="'.$linkprefix.'">Povratak.</a>';
		return;
	} // akcija == odjava


	if ($akcija == 'projektnastranica') {
		require_once('common/projektneStrane.php');
		common_projektneStrane();
		return;
	} //akcija == projektnastranica


	// Glavni ekran
	?>
	<h2>Projekti</h2>
	<span class="notice">
	Nastavnik je definisao sljedeće parametre svih projekata na ovom predmetu:
	<ul>
		<li>Broj timova: <?
			if ($min_timova == $max_timova) print "tačno $max_timova";
			else print "od $min_timova do $max_timova";
		?></li>
		<li>Broj članova tima: <?
			if ($min_clanova_tima == $max_clanova_tima) print "tačno $max_clanova_tima";
			else print "od $min_clanova_tima do $max_clanova_tima";
		?></li>
	</ul>
	Prijavite se na projekat i automatski se učlanjujete u projektni tim ili kreirate novi tim. Da biste promijenili tim, prijavite se u drugi tim.
	</span><br /><?


	// Ispis - zakljucani projekti

	if ($zakljucani_projekti == 1) {
		?>
		<span class="notice">Onemogućene su prijave u projektne timove. Otvorene su projektne stranice.</span>	
		<?
	} else {
		?>
		<span class="noticeGreen">Moguće su prijave u projetne timove. Nastavnik još uvijek nije kompletirao prijave.</span>	
		<?
	}


	// Ako je upisivanje zaključano, ispisaćemo samo onaj projekat u koji je student upisan
	$projekti_za_ispis = array();
	if ($zakljucani_projekti==1 && $clan_projekta>0) {
		foreach ($svi_projekti as $projekat) {
			if ($projekat[id]==$clan_projekta)
				$projekti_za_ispis[] = $projekat;
		}
	} else 
		$projekti_za_ispis = $svi_projekti;


	// Nema projekata
	if (count($svi_projekti)==0) {
		nicemessage("Predmetni nastavnik još uvijek nije definisao projekte na ovom predmetu. Imajte strpljenja.");
	}


	// Ispis projektnih kocki
	foreach ($projekti_za_ispis as $projekat) {

		?>
		<h3><?=$projekat['naziv']?></h3>
		<div class="links">
			<ul class="clearfix">
		<?

		if ($zakljucani_projekti == 0) {
			if ($projekat[id]==$clan_projekta) {
				?>
				<li class="last"><a href="<?=$linkprefix."&projekat=".$projekat[id]."&akcija=odjava"?>">Odustani od prijave na ovom projektu</a></li>	
				<?

			} else if ($broj_studenata[$projekat[id]]>=$max_clanova_tima) {
				?>
				<li style="color:red" class="last">Projekat je popunjen i ne prima prijave.</li>
				<?

			} else if ($broj_studenata[$projekat[id]]==0 && $limit_timova) {
				?>
				<div style="color:red; margin-top: 10px;">Limit za broj timova dostignut. Ne možete kreirati novi tim. Prijavite se na projekte u kojima ima mjesta.</div>	
				<?

			} else if ($clan_projekta==0) {
				?>
				<li class="last"><a href="<?=$linkprefix."&projekat=".$projekat[id]."&akcija=prijava"?>">Prijavi se na ovaj projekat</a></li>
				<?

			} else {
				?>	
				<li class="last"><a href="<?=$linkprefix."&projekat=".$projekat[id]."&akcija=prijava"?>">Prijavi se na ovaj projekat / Promijeni članstvo</a></li>   	
				<?
			}

		} else { // Projekti su zaključani
			?>
			<li class="last"><a href="<?=$linkprefix."&projekat=".$projekat[id]."&akcija=projektnastranica"?>">Projektna stranica</a></li>
			<?
		}

		// Ispis ostalih podataka o projektu
		?>
			</ul>
		</div>	
		<table class="projekti" border="0" cellspacing="0" cellpadding="2">
			<tr>
				<th width="200" align="left" valign="top" scope="row">Naziv</th>
				<td width="490" align="left" valign="top"><?=$projekat['naziv']?></td>
			</tr>
			<tr>
				<th width="200" align="left" valign="top" scope="row">Prijavljeni tim / student</th>
				<td width="490" align="left" valign="top">
					<?
					// Spisak članova projekta
					$q200 = myquery("select o.ime, o.prezime, o.brindexa from osoba as o, student_projekat as sp where sp.student=o.id and sp.projekat=".$projekat[id]." order by o.prezime, o.ime");
					if (mysql_num_rows($q200)<1)
						print 'Nema prijavljenih studenata.';
					else
						print "<ul>\n";
					
					while ($r200 = mysql_fetch_row($q200)) {
						?>
						<li><?=$r200[1].' '.$r200[0].', '.$r200[2]?></li>
						<?
					}
					if (mysql_num_rows($q200)>0) print "</ul>\n";
					?>
				</td>
			</tr>
			<tr>
				<th width="200" align="left" valign="top" scope="row">Opis</th>
				<td width="490" align="left" valign="top"><?=nl2br($projekat['opis'])?></td>
			</tr>
		</table>
		<?
	} // foreach ($projekti_za_ispis...

} //function


?>
