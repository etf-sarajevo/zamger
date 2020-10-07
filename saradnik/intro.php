<?

// SARADNIK/INTRO - spisak predmeta i grupa



function saradnik_intro() {
	
	global $userid,$user_siteadmin,$registry,$posljednji_pristup,$person;
	
	require_once ("lib/utility.php"); // spol, vokativ
	
	// Dobrodošlica
	if ($person['ExtendedPerson']['sex'] == 'F' || ($person['ExtendedPerson']['sex'] == '' && spol($person['name'])=="Z"))
		print "<h1>Dobro došla, ".vokativ($person['name'],"Z")."</h1>";
	else
		print "<h1>Dobro došao, ".vokativ($person['name'],"M")."</h1>";

	
	print "<p>Poštovani nastavnici, ako smatrate da je neko neovlašteno pristupao vašem Zamger nalogu, možete pogledati historiju pristupa u Vašem profilu ili koristeći sljedeći direktni link:<br><a href=\"https://zamger.etf.unsa.ba/index.php?sta=common/profil&akcija=log&nivo=1\">Historija pristupa</a></p>";


	// Sakrij raspored ako ga nema u registry-ju
	$nasao = false;
	foreach ($registry as $r) {
		if ($r[0]=="common/raspored1") { $nasao = true; break; }
	}
	if ($nasao) {
		require "common/raspored1.php";
		common_raspored1("nastavnik");
	}


	// Prikaz obavještenja za saradnike
	$announcements = api_call("inbox/announcements")['results'];
	$obavjestenje = false;
	$prikaz_sekundi = 600; // Koliko dugo se prikazuje obavještenje
	$vrijeme = $posljednji_pristup - $prikaz_sekundi; // globalna
	foreach($announcements as $ann) {
		if (db_timestamp($ann['time']) > $vrijeme && ($ann['scope'] == 0 || $ann['scope'] == 2))
			$obavjestenje = $ann['id'];
	}
	if ($obavjestenje != false) {
		?><p><a href="?sta=common/inbox&poruka=<?=$obavjestenje?>"><div style="color:red; text-decoration: underline">Imate novo sistemsko obavještenje. Kliknite ovdje.</div></a></p><?
	}

	
	
	
	// Spisak grupa po predmetima, predmeti po akademskoj godini
	?><table border="0" cellspacing="5"><tr>
	<?

	$courses = api_call("course/teacher/$userid",
		[
			"all" => (int_param('sve') == 1),
			"resolve" => ["CourseUnit", "AcademicYear", "Institution"]
		]
	)['results'];
	$oldag = 0;
	foreach($courses as $course) {
		$ag = $course['AcademicYear']['id'];
		if ($ag != $oldag) {
			// Count courses in this year
			$nr = 0;
			foreach($courses as $c) {
				if ($c['AcademicYear']['id'] == $ag)
					$nr++;
			}
			
			$cols = $nr*2;
			if ($cols > 12) $cols = 12;
			?>
			<td colspan="<?=$cols?>" align="center" bgcolor="#88BB99">Predmeti (<?=$course['AcademicYear']['name']?>)</td>
		</tr>
		<tr><?
			$br = 0;
			$oldag = $ag;
		}
		
		// Spacer
		if ($br % 6 > 0) {
			?><td bgcolor="#666666" width="1"></td><?
		}

		?>
			<td valign="top">
		<?
		
		$predmet = $course['CourseUnit']['id'];
		$naziv_predmeta = $course['courseName'];
		$studij = $course['CourseUnit']['Institution']['abbrev'];
	
		?>
				<b><?=$naziv_predmeta?> (<?=$studij?>)</b>
				<?
	
		// Edit link
		if ($user_siteadmin || $course['accessLevel']=="nastavnik" || $course['accessLevel']=="super_asistent") {
			?>
				[<b><a href="?sta=nastavnik/predmet&predmet=<?=$predmet?>&ag=<?=$ag?>"><font color="red">EDIT</font></a></b>]
			<?
		}
		
		// Get a list of groups
		$groups = api_call("group/course/$predmet", [ "year" => $ag, "includeVirtual" => true ])['results'];
		?><ul><?
		foreach($groups as $group) {
			if (!preg_match("/\w/", $group['name']))
				$group['name'] = "[Nema imena]";
			?>
					<li><a href="?sta=saradnik/grupa&id=<?=$group['id']?>"><?=$group['name']?></a></li>
					<?
		}
		
		// Kraj
		?>
				</ul>
			</td>
		<?
	
		$br++;
		if ($br % 6 == 0 || $br == $nr) {
			?>
		</tr>
		<tr>
			<?
		}
	}

	?>
	</tr></table>
	<?php
	
	if (int_param('sve') !== 1) {
		?>
		<p><a href="<?=genuri()?>&sve=1">Prikaži ranije akademske godine</a></p>
		<?
	}



}

?>
