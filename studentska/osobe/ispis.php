<?php

// Ispis sa studija

function studentska_osobe_ispis() {
	$osoba = int_param('osoba');
	
	// Svi parametri su obavezni!
	$studij = $_REQUEST['studij'];
	$semestar = $_REQUEST['semestar'];
	$ak_god = $_REQUEST['godina'];
	
	$q2500 = db_query("select ime, prezime from osoba where id=$osoba");
	$ime = db_result($q2500,0,0);
	$prezime = db_result($q2500,0,1);
	
	$q2510 = db_query("select naziv from akademska_godina where id=$ak_god");
	$naziv_ak_god = db_result($q2510,0,0);
	
	?>
	<h2><?=$ime?> <?=$prezime?> - ispis sa studija</h2>
	<?
	
	// Gdje je trenutno upisan?
	$q2520 = db_query("select s.id, s.naziv, ss.semestar from studij as s, student_studij as ss where ss.student=$osoba and ss.studij=s.id and ss.akademska_godina=$ak_god and ss.semestar=$semestar");
	if (db_num_rows($q2520)<1) {
		niceerror("Student nije upisan na fakultet u izabranoj akademskoj godini!");
		zamgerlog("pokusao ispisati studenta u$osoba koji nije upisan u ag$ak_god", 3);
		zamgerlog2("pokusao ispisati studenta koji nije upisan", $osoba, intval($ak_god));
		return;
	}
	if (db_result($q2520,0,0)!=$studij) {
		niceerror("Student nije upisan na izabrani studij u izabranoj akademskoj godini!");
		zamgerlog("pokusao ispisati studenta u$osoba sa studija $studij koji ne slusa u ag$ak_god", 3);
		zamgerlog2("pokusao ispisati studenta sa studija koji ne slusa", $osoba, intval($studij), intval($ak_god));
		return;
	}
	if (db_result($q2520,0,2)!=$semestar) {
		niceerror("Student nije upisan na izabrani semestar u izabranoj akademskoj godini!");
		zamgerlog("pokusao ispisati studenta u$osoba sa semestra $semestar koji ne slusa u ag$ak_god", 3);
		zamgerlog2("pokusao ispisati studenta sa semestra koji ne slusa", $osoba, intval($semestar), intval($ak_god));
		return;
	}
	$naziv_studija = db_result($q2520,0,1);
	
	$zimski_ljetnji = $semestar%2;
	
	?>
	<h3>Studij: <?=$naziv_studija?>, <?=$semestar?>. semestar, <?=$naziv_ak_god?> godina</h3>
	<?
	
	// Ispis sa studija
	if ($_REQUEST['potvrda']=="1") {
		$q530 = db_query("select pk.predmet from student_predmet as sp, ponudakursa as pk where sp.student=$osoba and sp.predmet=pk.id and pk.akademska_godina=$ak_god and pk.semestar mod 2=$zimski_ljetnji");
		while ($r530 = db_fetch_row($q530)) {
			$predmet = $r530[0];
			ispis_studenta_sa_predmeta($osoba, $predmet, $ak_god);
			zamgerlog("ispisujem studenta u$osoba sa predmeta pp$predmet (ispis sa studija)",4); // 4 - audit
			zamgerlog2("student ispisan sa predmeta (ispis sa studija)", $osoba, intval($predmet), intval($ak_god));
		}
		$q550 = db_query("delete from student_studij where student=$osoba and akademska_godina=$ak_god and semestar=$semestar");
		nicemessage("Ispisujem studenta sa studija $naziv_studija i svih predmeta koje trenutno sluša.");
		zamgerlog("ispisujem studenta u$osoba sa studija $naziv_studija (ag$ak_god)", 4); // 4 - audit
		zamgerlog2("student ispisan sa studija", $osoba, intval($ak_god));
	} else {
		?>
		<p>Student će biti ispisan sa sljedećih predmeta:<ul>
			<?
			$q520 = db_query("select p.naziv from predmet as p, ponudakursa as pk, student_predmet as sp where sp.student=$osoba and sp.predmet=pk.id and pk.akademska_godina=$ak_god and pk.predmet=p.id and pk.semestar mod 2=$zimski_ljetnji");
			while ($r520 = db_fetch_row($q520)) {
				print "<li>$r520[0]</li>\n";
			}
			?>
		</ul></p>
		<p>NAPOMENA: Svi bodovi ostvareni na ovim predmetima će biti izgubljeni! Trenutno nema drugog načina da se student ispiše sa studija.</p>
		<p>Kliknite na dugme "Potvrda" da potvrdite ispis.</p>
		<?=genform("POST");?>
		<input type="hidden" name="potvrda" value="1">
		<input type="submit" value=" Potvrda ">
		</form>
		<?
	}
}