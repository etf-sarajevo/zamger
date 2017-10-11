<?

// STUDENT/ANKETA - stranica za dobijanje anketnog koda / prikaz rezultata ankete



function student_anketa() {

	global $userid;
	
	// Kod koji je naveden ispod omogućuje studentu da pristupi rezultatima ankete
	// Ranije je tu bila i mogućnost za preuzimanje "koda" za popunjavanje ankete od koje se odustalo
	// TODO ovo treba biti konfigurabilno na način da se prilikom kreiranja ankete bira tip ankete:
	// anonimni kodovi, popunjavanje pod loginom, moguće preuzimanje koda kroz Zamger itd.
	
	$anketa = intval($_REQUEST['anketa']);
	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);


	// Podaci za zaglavlje
	$q1 = db_query("select naziv from predmet where id=$predmet");
	if (db_num_rows($q1)<1) {
		zamgerlog("nepoznat predmet $predmet",3); // nivo 3: greska
		zamgerlog2("nepoznat predmet", $predmet); // nivo 3: greska
		biguglyerror("Nepoznat predmet");
		return;
	}

	$q2 = db_query("select naziv from akademska_godina where id=$ag");
	if (db_num_rows($q2)<1) {
		zamgerlog("nepoznata akademska godina $ag",3); // nivo 3: greska
		zamgerlog2("nepoznata akademska godina", $ag); // nivo 3: greska
		biguglyerror("Nepoznata akademska godina");
		return;
	}


	// Da li student slusa predmet?
	$q5 = db_query("select sp.predmet from student_predmet as sp, ponudakursa as pk where sp.student=$userid and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
	if (db_num_rows($q5)<1) {
		zamgerlog("student ne slusa predmet pp$predmet (ag$ag)", 3);
		zamgerlog2("student ne slusa predmet", $predmet, $ag);
		biguglyerror("Niste upisani na ovaj predmet");
		return;
	}


	$q10 = db_query("select naziv, UNIX_TIMESTAMP(datum_otvaranja), UNIX_TIMESTAMP(datum_zatvaranja), akademska_godina from anketa_anketa where id=$anketa");
	if (db_num_rows($q10)<1) {
		biguglyerror("Nepostojeća anketa");
		zamgerlog("student/anketa nepostojeca anketa", 3);
		zamgerlog2("nepostojeca anketa", $anketa);
		return;
	}
	
	if (db_result($q10,0,3) != $ag) {
		biguglyerror("U datoj akademskoj godini nije bila raspisana anketa za ovaj predmet");
		zamgerlog("student/anketa pogresna ag", 3);
		zamgerlog2("id ankete i godine ne odgovaraju", $anketa, $ag);
		return;
	}
	
	
	// Naslov
	
	?>
	<h2><?=db_result($q10,0,0)?></h2>
	<?
	
	if (db_result($q10,0,1) > time()) {
		nicemessage("Anketa još uvijek nije otvorena za popunjavanje.");
		return;
	}
	
	if (db_result($q10,0,2) > time()) {
		nicemessage("Anketa je otvorena za popunjavanje.");
		?>
		<p><a href="?sta=public/anketa&amp;anketa=<?=$anketa?>&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>"><b>Kliknite ovdje za popunjavanje ankete</b></a></p>
		<p>Ne možete vidjeti rezultate ankete dok se popunjavanje ne završi.</p>
		<!--p>Za ovu anketu je predviđeno anonimno popunjavanje. Molimo da se odjavite da biste popunili anketu koristeći kod koji ste dobili.</p-->
		<?
		return;
	}
	
	
	$q20 = db_query("select predmet, aktivna from anketa_predmet where anketa=$anketa");
	if (db_num_rows($q20)<1) {
		biguglyerror("Greška");
		zamgerlog("student/anketa ne postoji zapis u tabeli anketa_predmet", 3);
		zamgerlog2("ne postoji zapis u tabeli anketa_predmet", $anketa);
		return;
	}
	
	if (db_result($q20,0,0) != $predmet && db_result($q20,0,0) != 0) {
		biguglyerror("U datoj akademskoj godini nije bila raspisana anketa za ovaj predmet");
		zamgerlog("student/anketa pogresan predmet", 3);
		zamgerlog2("id ankete i predmeta ne odgovaraju", $anketa, $predmet);
		return;
	}
	
	if (db_result($q20,0,1) != 0) {
		?>
		<h2>Pristup rezultatima ankete nije moguć</h2>
		<p><?=$pristup_student?> <?=$userid?> <?=$anketa?> Rezultatima ankete se može pristupiti tek nakon isteka određenog roka. Za dodatne informacije predlažemo da kontaktirate službe <?=$conf_skr_naziv_institucije_genitiv?></p>
		<?
		return;
	}
	
	?>
	<a href="?sta=izvjestaj/anketa&predmet=<?=$predmet?>&ag=<?=$ag?>&anketa=<?=$anketa?>&rank=da">Rezultati ankete za predmet <?=db_result($q1,0,0)?>, akademska <?=db_result($q2,0,0)?></a>
	<?
	
	
	
	return;
}

?>
