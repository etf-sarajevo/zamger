<?

// STUDENT/ANKETA - stranica za dobijanje anketnog koda / prikaz rezultata ankete



function student_anketa() {

	global $userid, $conf_skr_naziv_institucije_genitiv;
	
	// Kod koji je naveden ispod omogućuje studentu da pristupi rezultatima ankete
	// Ranije je tu bila i mogućnost za preuzimanje "koda" za popunjavanje ankete od koje se odustalo
	// TODO ovo treba biti konfigurabilno na način da se prilikom kreiranja ankete bira tip ankete:
	// anonimni kodovi, popunjavanje pod loginom, moguće preuzimanje koda kroz Zamger itd.
	
	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);
	
	// Podaci za zaglavlje
	$course = getCourseDetails($predmet, $ag);
	$naziv_predmeta = $course['courseName'];
	$naziv_ag = $course['courseYear'];
	
	$polls = api_call("poll/course/$predmet", ["year" => $ag] )['results'];
	
	if (empty($polls)) {
		biguglyerror("U datoj akademskoj godini nije bila raspisana anketa za ovaj predmet");
		zamgerlog("student/anketa nepostojeca anketa", 3);
		zamgerlog2("nije bilo anketa za predmet", $predmet, $ag);
		return;
	}
	
	foreach($polls as $poll) {
		$anketa = $poll['id'];
		// Naslov
		?>
		<h2><?=$poll['name']?></h2>
		<?
		
		if (db_timestamp($poll['openDate']) > time()) {
			nicemessage("Anketa još uvijek nije otvorena za popunjavanje.");
		}
		
		else if (db_timestamp($poll['closeDate']) > time()) {
			nicemessage("Anketa je otvorena za popunjavanje.");
			?>
			<p><a href="?sta=public/anketa&amp;anketa=<?=$anketa?>&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>"><b>Kliknite ovdje za popunjavanje ankete</b></a></p>
			<p>Vrijeme za popunjavanje ankete ističe: <b><?=date("d. m. Y. H:i:s", db_timestamp($poll['closeDate']))?></b></p>
			<p>Ne možete vidjeti rezultate ankete dok se popunjavanje ne završi.</p>
			<!--p>Za ovu anketu je predviđeno anonimno popunjavanje. Molimo da se odjavite da biste popunili anketu koristeći kod koji ste dobili.</p-->
			<?
		}
		
		else {
			// Anketa je zatvorena
			if ($poll['active']) {
				?>
				<h2>Pristup rezultatima ankete nije moguć</h2>
				<p>Rezultatima ankete se može pristupiti tek nakon isteka određenog roka. Za dodatne informacije predlažemo da kontaktirate službe <?=$conf_skr_naziv_institucije_genitiv?></p>
				<?
				return;
			}
			else {
				?>
				<a href="?sta=izvjestaj/anketa&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>&amp;anketa=<?=$anketa?>&amp;rank=da">Rezultati ankete za predmet <?=$naziv_predmeta?>, akademska <?=$naziv_ag?></a>
				<?
			}
		}
		
	}
}
