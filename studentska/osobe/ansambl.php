<?php


// Funkcije vezane za nastavni ansambl

function studentska_osobe_applet_ansambl($osoba) {
	$trenutna_godina = db_get("SELECT id FROM akademska_godina WHERE aktuelna=1");
	$naziv_ak_god = db_get("SELECT naziv FROM akademska_godina WHERE aktuelna=1");
	
	// Angažman nastavnika na predmetu
	if (param('subakcija') == "angazuj" && check_csrf_token()) {
		
		$predmet = intval($_POST['predmet']);
		$status = intval($_POST['_lv_column_angazman_status']);
		$angazman_ak_god = intval($_POST['_lv_column_akademska_godina']);
		
		$q115 = db_query("select naziv from predmet where id=$predmet");
		$naziv_predmeta = db_result($q115,0,0);
		
		$q130 = db_query("replace angazman set osoba=$osoba, predmet=$predmet, akademska_godina=$angazman_ak_god, angazman_status=$status");
		
		zamgerlog("nastavnik u$osoba angazovan na predmetu pp$predmet (status: $status, akademska godina: $trenutna_godina)",4);
		zamgerlog2("nastavnik angazovan na predmetu", $osoba, $predmet, intval($trenutna_godina));
		nicemessage("Nastavnik angažovan na predmetu $naziv_predmeta.");
		print "<p>Kliknite na naziv predmeta na spisku ispod kako biste detaljnije podesili privilegije.</p>";
	}
	
	
	?>
	<p><b>Angažman u nastavi (akademska godina <?=$naziv_ak_god?>)</b></p>
	<ul>
		<?
		
		$q430 = db_query("select p.id, p.naziv, angs.naziv, i.kratki_naziv from angazman as a, angazman_status as angs, predmet as p, institucija as i where a.osoba=$osoba and a.akademska_godina=$trenutna_godina and a.predmet=p.id and a.angazman_status=angs.id and p.institucija=i.id order by angs.id, p.naziv");
		if (db_num_rows($q430) < 1)
			print "<li>Uposlenik nije angažovan niti na jednom predmetu u ovoj godini.</li>\n";
		while ($r430 = db_fetch_row($q430)) {
			print "<li><a href=\"?sta=studentska/predmeti&akcija=edit&predmet=$r430[0]&ag=$trenutna_godina\">$r430[1] ($r430[3])</a> - $r430[2]</li>\n";
		}
		
		
		// Angažman
		
		?></ul>
	<p>Angažuj nastavnika na predmetu:
		<?=genform("POST")?>
		<input type="hidden" name="subakcija" value="angazuj">
		<select name="predmet" class="default"><?
			$q190 = db_query("select p.id, p.naziv, i.kratki_naziv from predmet as p, ponudakursa as pk, institucija as i where pk.predmet=p.id and pk.akademska_godina=$trenutna_godina and p.institucija=i.id group by p.id,p.naziv order by p.naziv");
			while ($r190 = db_fetch_row($q190)) {
				print "<option value=\"$r190[0]\">$r190[1] ($r190[2])</a>\n";
			}
			?></select><br/>
		<?=db_dropdown("angazman_status")?>
		<?=db_dropdown("akademska_godina", $trenutna_godina)?>
		<input type="submit" value=" Dodaj "></form></p>
	<?
}
