<?php


// Prikaz informativne sekcije za nastavnike u kontekstu modula za osobe

function studentska_osobe_nastavnik() {
	require_once("lib/formgen.php"); // db_dropdown
	
	$osoba = int_param('osoba');
	$trenutna_godina = db_get("SELECT id FROM akademska_godina WHERE aktuelna=1");
	$naziv_ak_god = db_get("SELECT naziv FROM akademska_godina WHERE aktuelna=1");
	
	
	?>
	<br/><hr>
	<h3>NASTAVNIK</h3>
	<p><b>Podaci o izboru</b></p>
	<?
	
	
	// Izbori
	
	require_once("studentska/osobe/izbori.php");
	studentska_osobe_applet_izbori($osoba);
	
	
	
	// Angažman
	
	require_once("studentska/osobe/ansambl.php");
	studentska_osobe_applet_ansambl($osoba);
	
	
	
	
	// Dodjela prava nastavniku na predmetu
	if (param('subakcija') == "daj_prava" && check_csrf_token()) {
		
		$predmet = intval($_POST['predmet']);
		
		$q115 = db_query("select naziv from predmet where id=$predmet");
		$naziv_predmeta = db_result($q115,0,0);
		
		$q130 = db_query("replace nastavnik_predmet set nastavnik=$osoba, predmet=$predmet, akademska_godina=$trenutna_godina, nivo_pristupa='asistent'");
		
		zamgerlog("nastavniku u$osoba data prava na predmetu pp$predmet (admin: asistent, akademska godina: $trenutna_godina)",4);
		zamgerlog2("nastavniku data prava na predmetu", $osoba, $predmet, intval($trenutna_godina));
		nicemessage("Nastavniku su dodijeljena prava na predmetu $naziv_predmeta.");
		print "<p>Kliknite na naziv predmeta na spisku ispod kako biste detaljnije podesili privilegije.</p>";
	}
	
	// Prava pristupa
	
	?>
	<p><b>Prava pristupa (akademska godina <?=$naziv_ak_god?>)</b></p>
	<ul>
		<?
		$q180 = db_query("select p.id, p.naziv, np.nivo_pristupa, i.kratki_naziv from nastavnik_predmet as np, predmet as p, institucija as i where np.nastavnik=$osoba and np.predmet=p.id and np.akademska_godina=$trenutna_godina and p.institucija=i.id order by np.nivo_pristupa, p.naziv"); // FIXME: moze li se ovdje izbaciti tabela ponudakursa? studij ili institucija?
		if (db_num_rows($q180) < 1)
			print "<li>Nijedan</li>\n";
		while ($r180 = db_fetch_row($q180)) {
			print "<li><a href=\"?sta=studentska/predmeti&akcija=edit&predmet=$r180[0]&ag=$trenutna_godina\">$r180[1] ($r180[3])</a>";
			if ($r180[2] == "nastavnik") print " (Nastavnik)";
			else if ($r180[2] == "super_asistent") print " (Super asistent)";
			print "</li>\n";
		}
		?></ul>
	<p>Za prava pristupa na prethodnim akademskim godinama, koristite pretragu na kartici &quot;Predmeti&quot;<br/></p>
	<?
	
	
	// Dodjela prava pristupa
	
	?><p>Dodijeli prava za predmet:
	<?=genform("POST")?>
	<input type="hidden" name="subakcija" value="daj_prava">
	<select name="predmet" class="default"><?
		$q190 = db_query("select p.id, p.naziv, i.kratki_naziv from predmet as p, ponudakursa as pk, institucija as i where pk.predmet=p.id and pk.akademska_godina=$trenutna_godina and p.institucija=i.id group by p.id, p.naziv order by p.naziv");
		while ($r190 = db_fetch_row($q190)) {
			print "<option value=\"$r190[0]\">$r190[1] ($r190[2])</a>\n";
		}
		?></select>&nbsp;
	<input type="submit" value=" Dodaj "></form></p>
	<?
}
