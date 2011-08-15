<?

// STUDENT/PROJEKTI - studenski modul za prijavu na projekte i ulazak u projektnu stranu projekta


function student_projekti() {

	require_once("Config.php");
	
	// Backend stuff
	require_once(Config::$backend_path."core/Portfolio.php");
	
	// Pošto je ovaj kod dio lms/projects modula, dio ispod može biti obavezan
	require_once(Config::$backend_path."lms/projects/Project.php");
	require_once(Config::$backend_path."lms/projects/ProjectParams.php");


	//debug mod aktivan
	global $userid, $user_student;

	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);	

	$linkprefix = "?sta=student/projekti&predmet=$predmet&ag=$ag";
	$akcija = $_REQUEST['akcija'];
	$id = intval($_REQUEST['id']);
	
	// Da li student slusa predmet?
	$pf = Portfolio::fromCourseUnit($userid, $predmet, $ag);
	
	// Spisak projekata i druge korisne informacije
	$projekti = Project::getAllForCourse($predmet, $ag);
	$broj_studenata = array();
	$clan_projekta = 0; // projekat čiji je član aktuelni korisnik
	$broj_timova = 0; // broj nepraznih projekata
	foreach ($projekti as $p) {
		$clanovi = $p->getMembers();
		$broj_studenata[$p->id] = count($clanovi);
		foreach ($clanovi as $c)
			if ($c->id == $userid) $clan_projekta = $p;
		if (count($clanovi)>0) $broj_timova++;
	}

	// Parametri projekata na predmetu
	try {
		$par = ProjectParams::fromCourse($predmet, $ag);
	} catch(Exception $e) {
		niceerror("Predmetni nastavnik nije podesio parametre projekata.");
		print "Prijavljivanje na projekte za sada nije moguće. Obratite se predmetnom nastavniku ili asistentu za dodatne informacije.";
		return;
	}

	// Da li je dostignut limit broja timova?
	if ($broj_timova >= $par->maxTeams) {
		$limit_timova=true;

		// No ako je student trenutno član projekta sa samo jednim članom,
		// istupanjem iz tima otvoriće se mogućnost kreiranja novog tima
		if ($broj_timova == $par->maxTeams && $clan_projekta != 0 && $broj_studenata[$clan_projekta->id]==1) 
			$limit_timova=false;
	}

	// Stylesheet... čemu?
	?>
	<LINK href="css/projekti.css" rel="stylesheet" type="text/css">
	<?


	// Akcije

	if ($akcija == 'prijava') {
		$id_projekta = intval($_REQUEST['projekat']);
		$projekat = Project::fromId($id_projekta);

		// Da li je projekat sa ovog predmeta?
		if ($projekat->courseUnitId != $predmet || $projekat->academicYearId != $ag) {
			niceerror("Nepoznat projekat!");
			zamgerlog("prijava na projekat $id_projekta koji nije sa predmeta pp$predmet", 3);
		}

		else if ($par->locked) {
			niceerror("Zaključane su prijave na projekte.");
			zamgerlog("prijava na projekat $id_projekta koji je zaključan na predmetu pp$predmet", 3);
		}

		else if ($broj_studenata[$id_projekta] >= $par->maxTeamMembers) {
			niceerror("Dosegnut je limit broja članova po projektu.");
			zamgerlog("prijava na projekat $id_projekta koji je popunjen", 3);
		}

		else if ($broj_studenata[$id_projekta]==0 && $limit_timova) {
			niceerror("Dosegnut je maksimalan broj timova. Ne možete kreirati novi tim.");
			zamgerlog("dosegnut limit broja timova na predmetu pp$predmet", 3);
		}

		else {
			// Upisujemo u novi projekat
			$projekat->addMember($userid);

			nicemessage("Uspješno ste prijavljeni na projekat");
			zamgerlog("student upisan na projekat $id_projekta (predmet pp$predmet)", 2);

			// Ispisujemo studenta sa postojećih projekata
			if ($clan_projekta != 0) {
				$clan_projekta->removeMember($userid);
				nicemessage("Odjavljeni ste sa starog projekta");
				zamgerlog("student ispisan sa projekta $projekat (predmet pp$predmet)", 2);
			}
		}

		print '<a href="'.$linkprefix.'">Povratak.</a>';
		return;
	} // akcija == prijava


	if ($akcija == 'odjava') {
		$id_projekta = intval($_REQUEST['projekat']);

		if ($id_projekta != $clan_projekta->id) {
			niceerror("Niste prijavljeni na ovaj projekat");
			zamgerlog("odjava sa projekta $projekat na koji nije prijavljen", 3);
		}

		else if ($par->locked) {
			niceerror("Zaključane su liste timova za projekte. Odustajanja nisu dozvoljena.");
			zamgerlog("odjava sa projekta $projekat koji je zakljucan na predmetu pp$predmet", 3);
		}

		else {
			$clan_projekta->removeMember($userid);
			nicemessage("Uspješno ste odjavljeni sa projekta");
			zamgerlog("student ispisan sa projekta $projekat (predmet pp$predmet)", 2);
		}

		print '<a href="'.$linkprefix.'">Povratak.</a>';
		return;
	} // akcija == odjava


	if ($akcija == 'projektna_stranica') {
		// Svi projekti koriste portal (FIXME)
		$_REQUEST['portalId'] = $projekat;
		require_once('common/portal.php');
		common_portal();
		return;
	} //akcija == projektnastranica


	// Glavni ekran
	?>
	<h2>Projekti</h2>
	<span class="notice">
	Nastavnik je definisao sljedeće parametre svih projekata na ovom predmetu:
	<ul>
		<li>Broj timova: <?
			if ($par->minTeams == $par->maxTeams) print "tačno ".$par->maxTeams;
			else print "od ".$par->minTeams." do ".$par->maxTeams;
		?></li>
		<li>Broj članova tima: <?
			if ($par->minTeamMembers == $par->maxTeamMembers) print "tačno ".$par->minTeamMembers;
			else print "od ".$par->minTeamMembers." do ".$par->maxTeamMembers;
		?></li>
	</ul>
	Prijavite se na projekat i automatski se učlanjujete u projektni tim ili kreirate novi tim. Da biste promijenili tim, prijavite se u drugi tim.
	</span><br /><?


	// Ispis - zakljucani projekti

	if ($par->locked == 1) {
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
	if ($par->locked && $clan_projekta != 0)
		$projekti_za_ispis[] = $clan_projekta;
	else if ($par->locked) // $clan_projekta == 0 
		nicemessage("Niste se učlanili niti u jedan projekat, a vrijeme za prijave je isteklo! Kontaktirajte predmetnog nastavnika.");
	else if (count($projekti) == 0)
		nicemessage("Predmetni nastavnik još uvijek nije definisao projekte na ovom predmetu. Imajte strpljenja.");
	else
		$projekti_za_ispis = $projekti;


	// Ispis projektnih kocki
	foreach ($projekti_za_ispis as $projekat) {

		?>
		<h3><?=$projekat->name?></h3>
		<div class="links">
			<ul class="clearfix">
		<?

		if ( !$par->locked ) {
			if ($projekat->id == $clan_projekta->id) {
				?>
				<li class="last"><a href="<?=$linkprefix."&projekat=".$projekat->id."&akcija=odjava"?>">Odustani od prijave na ovom projektu</a></li>	
				<?

			} else if ($broj_studenata[$projekat->id] >= $par->maxTeamMembers) {
				?>
				<li style="color:red" class="last">Projekat je popunjen i ne prima prijave.</li>
				<?

			} else if ($broj_studenata[$projekat->id]==0 && $limit_timova) {
				?>
				<div style="color:red; margin-top: 10px;">Limit za broj timova dostignut. Ne možete kreirati novi tim. Prijavite se na projekte u kojima ima mjesta.</div>	
				<?

			} else if ($clan_projekta==0) {
				?>
				<li class="last"><a href="<?=$linkprefix."&projekat=".$projekat->id."&akcija=prijava"?>">Prijavi se na ovaj projekat</a></li>
				<?

			} else {
				?>	
				<li class="last"><a href="<?=$linkprefix."&projekat=".$projekat->id."&akcija=prijava"?>">Prijavi se na ovaj projekat / Promijeni članstvo</a></li>
				<?
			}

		} else { // Projekti su zaključani
			?>
			<li class="last"><a href="<?=$linkprefix."&projekat=".$projekat->id."&akcija=projektna_stranica"?>">Projektna stranica</a></li>
			<?
		}

		// Ispis ostalih podataka o projektu
		?>
			</ul>
		</div>	
		<table class="projekti" border="0" cellspacing="0" cellpadding="2">
			<tr>
				<th width="200" align="left" valign="top" scope="row">Naziv</th>
				<td width="490" align="left" valign="top"><?=$projekat->name?></td>
			</tr>
			<tr>
				<th width="200" align="left" valign="top" scope="row">Prijavljeni tim / student</th>
				<td width="490" align="left" valign="top">
					<?

					// Spisak članova projekta
					$clanovi = $projekat->getMembers();
					if (count($clanovi) == 0)
						print 'Nema prijavljenih studenata.';
					else
						print "<ul>\n";
					
					foreach ($clanovi as $clan) {
						?>
						<li><?=$clan->name.' '.$clan->surname.', '.$clan->studentIdNr?></li>
						<?
					}
					if (count($clanovi) > 0) print "</ul>\n";

					?>
				</td>
			</tr>
			<tr>
				<th width="200" align="left" valign="top" scope="row">Opis</th>
				<td width="490" align="left" valign="top"><?=nl2br($projekat->description)?></td>
			</tr>
		</table>
		<?
	} // foreach ($projekti_za_ispis...

} //function


?>
