<?

// STUDENT/PROJEKTI - studenski modul za prijavu na projekte i ulazak u projektnu stranu projekta



function student_projekti() {

	require_once("lib/projekti.php");

	//debug mod aktivan
	global $userid, $_api_http_code;

	$predmet = int_param('predmet');
	$ag = int_param('ag');	

	$linkprefix = "?sta=student/projekti&predmet=$predmet&ag=$ag";
	$akcija = param('akcija');
	$id = int_param('id');



	// KORISNI UPITI
	
	$allProjects = api_call("project/course/$predmet/$ag", [ "members" => true ])["results"];
	usort($allProjects, function ($p1, $p2) {
		return strnatcasecmp($p1['name'], $p2['name']);
	});
	
	// Da li je student upisan u neki projekat?
	$clan_projekta = $brtimova = 0;
	foreach($allProjects as $project) {
		if (count($project['members']) > 0)
			$brtimova++;
		foreach ($project['members'] as $member)
			if ($member['id'] == $userid) {
				$clan_projekta = $project['id'];
				
				// No ako je student trenutno član projekta sa samo jednim članom,
				// istupanjem iz tima otvoriće se mogućnost kreiranja novog tima
				if (count($project['members']) == 1)
					$brtimova--;
			}
	}
	
	$params = api_call("project/params/$predmet/$ag");
	if ($_api_http_code != "200") {
		niceerror("Predmetni nastavnik nije podesio parametre projekata.");
		print "Prijavljivanje na projekte za sada nije moguće. Obratite se predmetnom nastavniku ili asistentu za dodatne informacije.";
		return;
	}


	// Da li je dostignut limit broja timova?
	$limit_timova=false;
	if ($brtimova >= $params['maxTeams'])
		$limit_timova=true;


	// Stylesheet... čemu?
	?>
	<LINK href="static/css/projekti.css" rel="stylesheet" type="text/css">
	<?


	// Akcije

	if ($akcija == 'prijava') {
		$projekat = intval($_REQUEST['projekat']);

		// Da li je projekat sa ovog predmeta?
		$foundProject = false;
		foreach ($allProjects as $project) {
			if ($project['id']==$projekat) { $foundProject = $project; break; }
		}

		if ($foundProject==false) {
			niceerror("Nepoznat projekat!");
			zamgerlog("prijava na projekat $projekat koji nije sa predmeta pp$predmet", 3);
			zamgerlog2("projekat i predmet ne odgovaraju", $projekat, $predmet);
		}

		else if ($params['locked']) {
			niceerror("Zaključane su prijave na projekte.");
			zamgerlog("prijava na projekat $projekat koji je zaključan na predmetu pp$predmet", 3);
			zamgerlog2("projekat zakljucan", $projekat);
		}

		else if (count($foundProject['members']) >= $params['maxTeamMembers']) {
			niceerror("Dosegnut je limit broja članova po projektu.");
			zamgerlog("prijava na projekat $projekat koji je popunjen", 3);
			zamgerlog2("projekat popunjen", $projekat);
		}

		else if (count($foundProject['members']) == 0 && $limit_timova) {
			niceerror("Dosegnut je maksimalan broj timova. Ne možete kreirati novi tim.");
			zamgerlog("dosegnut limit broja timova na predmetu pp$predmet", 3);
			zamgerlog2("dosegnut limit broja timova", $predmet);
		}

		else {
			$result = api_call("project/$projekat/student/$userid", [], "PUT");
			if ($_api_http_code == "201") {
				nicemessage("Uspješno ste prijavljeni na projekat");
				zamgerlog("student upisan na projekat $projekat (predmet pp$predmet)", 2);
				zamgerlog2("prijavljen na projekat", $projekat);
				// Ispisujemo studenta sa postojećih projekata
				if ($clan_projekta>0) {
					nicemessage("Odjavljeni ste sa starog projekta");
					zamgerlog("student ispisan sa projekta $projekat (predmet pp$predmet)", 2);
					zamgerlog2("odjavljen sa starog projekta", $projekat);
				}
				?>
				<script language="JavaScript">
                    setTimeout(function() { location.href='?sta=student/projekti&predmet=<?=$predmet?>&ag=<?=$ag?>'; }, 1000);
				</script>
				<?
			} else {
				niceerror("Neuspješno prijavljivanje na projekat ($_api_http_code): " . $result['message']);
			}
		}

		print '<a href="'.$linkprefix.'">Povratak.</a>';
		return;
	} // akcija == prijava


	if ($akcija == 'odjava') {
		$projekat = intval($_REQUEST['projekat']);
		
		// Da li je projekat sa ovog predmeta?
		$foundProject = false;
		foreach ($allProjects as $project) {
			if ($project['id']==$projekat) { $foundProject = $project; break; }
		}

		if ($foundProject == false) {
			niceerror("Nepoznat projekat!");
			zamgerlog("odjava sa projekta $projekat koji nije sa predmeta pp$predmet", 3);
			zamgerlog2("projekat i predmet ne odgovaraju (odjava)", $projekat, $predmet);
		}

		else if ($params['locked']) {
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
			$result = api_call("project/$projekat/student/$userid", [], "DELETE");
			if ($_api_http_code == "204") {
				nicemessage("Uspješno ste odjavljeni sa projekta");
				zamgerlog("student ispisan sa projekta $projekat (predmet pp$predmet)", 2);
				zamgerlog2("odjavljen sa projekta", $projekat);
				?>
				<script language="JavaScript">
                    setTimeout(function() { location.href='?sta=student/projekti&predmet=<?=$predmet?>&ag=<?=$ag?>'; }, 1000);
				</script>
				<?
			} else {
				niceerror("Neuspješno odjavljivanje sa projekta ($_api_http_code): " . $result['message']);
			}
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
			if ($params['minTeams'] == $params['maxTeams']) print "tačno " . $params['maxTeams'];
			else print "od " . $params['minTeams'] . " do " . $params['maxTeams'];
		?></li>
		<li>Broj članova tima: <?
			if ($params['minTeamMembers'] == $params['maxTeamMembers']) print "tačno " . $params['maxTeamMembers'];
			else print "od " . $params['minTeamMembers'] . " do " . $params['maxTeamMembers'];
		?></li>
	</ul>
	Prijavite se na projekat i automatski se učlanjujete u projektni tim ili kreirate novi tim. Da biste promijenili tim, prijavite se u drugi tim.
	</span><br /><?


	// Ispis - zakljucani projekti

	if ($params['locked']) {
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
	if ($params['locked'] && $clan_projekta>0) {
		foreach ($allProjects as $project) {
			if ($project['id']==$clan_projekta)
				$projekti_za_ispis[] = $project;
		}
	} else 
		$projekti_za_ispis = $allProjects;


	// Nema projekata
	if (count($allProjects)==0) {
		nicemessage("Predmetni nastavnik još uvijek nije definisao projekte na ovom predmetu. Imajte strpljenja.");
	}


	// Ispis projektnih kocki
	foreach ($allProjects as $project) {

		?>
		<h3><?=$project['name']?></h3>
		<div class="links">
			<ul class="clearfix">
		<?

		if (!$params['locked']) {
			if ($project['id']==$clan_projekta) {
				?>
				<li class="last"><a href="<?=$linkprefix."&projekat=".$project['id']."&akcija=odjava"?>">Odustani od prijave na ovom projektu</a></li>
				<?

			} else if (count($project['members']) >= $params['maxTeamMembers']) {
				?>
				<li style="color:red" class="last">Projekat je popunjen i ne prima prijave.</li>
				<?

			} else if (count($project['member']) == 0 && $limit_timova) {
				?>
				<div style="color:red; margin-top: 10px;">Limit za broj timova dostignut. Ne možete kreirati novi tim. Prijavite se na projekte u kojima ima mjesta.</div>	
				<?

			} else if ($clan_projekta==0) {
				?>
				<li class="last"><a href="<?=$linkprefix."&projekat=".$project['id']."&akcija=prijava"?>">Prijavi se na ovaj projekat</a></li>
				<?

			} else {
				?>	
				<li class="last"><a href="<?=$linkprefix."&projekat=".$project['id']."&akcija=prijava"?>">Prijavi se na ovaj projekat / Promijeni članstvo</a></li>
				<?
			}

		} else { // Projekti su zaključani
			?>
			<li class="last"><a href="<?=$linkprefix."&projekat=".$project['id']."&akcija=projektnastranica"?>">Projektna stranica</a></li>
			<?
		}

		// Ispis ostalih podataka o projektu
		?>
			</ul>
		</div>	
		<table class="projekti" border="0" cellspacing="0" cellpadding="2">
			<tr>
				<th width="200" align="left" valign="top" scope="row">Naziv</th>
				<td width="490" align="left" valign="top"><?=$project['name']?></td>
			</tr>
			<tr>
				<th width="200" align="left" valign="top" scope="row">Prijavljeni tim / student</th>
				<td width="490" align="left" valign="top">
					<?
					// Spisak članova projekta
					if (count($project['members']) < 1)
						print 'Nema prijavljenih studenata.';
					else
						print "<ul>\n";
					
					foreach($project['members'] as $member) {
						?>
						<li><?=$member['surname'].' '.$member['name'].', '.$member['studentIdNr']?></li>
						<?
					}
					if (count($project['members']) > 0) print "</ul>\n";
					?>
				</td>
			</tr>
			<tr>
				<th width="200" align="left" valign="top" scope="row">Opis</th>
				<td width="490" align="left" valign="top"><?=nl2br($project['description'])?></td>
			</tr>
		</table>
		<?
	} // foreach ($projekti_za_ispis...

} //function


?>
