<?php


// Prikaz podataka o uspjehu kandidata na prijemnom ispitu u kontekstu studentska/osobe

function studentska_osobe_prijemni() {
	$osoba = int_param('osoba');
	
	?>
	<br/><hr>
	<h3>KANDIDAT NA PRIJEMNOM ISPITU</h3>
	<?
	
	$q600 = db_query("select prijemni_termin, broj_dosjea, nacin_studiranja, studij_prvi, studij_drugi, studij_treci, studij_cetvrti, izasao, rezultat from prijemni_prijava where osoba=$osoba");
	while ($r600 = db_fetch_row($q600)) {
		$q610 = db_query("select ag.id, ag.naziv, UNIX_TIMESTAMP(pt.datum), pt.ciklus_studija from prijemni_termin as pt, akademska_godina as ag where pt.id=$r600[0] and pt.akademska_godina=ag.id");
		?>
		<b>Za akademsku <?=db_result($q610,0,1)?> godinu (<?=db_result($q610,0,3)?>. ciklus studija), održan <?=date("d. m. Y", db_result($q610,0,2))?></b>
		<ul><li><?
			if ($r600[7]>0) print "$r600[8] bodova"; else print "(nije izašao/la)";
			?></li>
		<li>Broj dosjea: <?=$r600[1]?>, <?
			$q615 = db_query("select naziv from nacin_studiranja where id=$r600[2]");
			if (db_num_rows($q615)>0)
				print db_result($q615,0,0);
			else
				print "nepoznato";
			for ($i=3; $i<=6; $i++) {
				if ($r600[$i]>0) {
					$q620 = db_query("select kratkinaziv from studij where id=".$r600[$i]);
					print ", ".db_result($q620,0,0);
				}
			}
			?></li>
		<?
		
		// Link na upis prikazujemo samo za ovogodišnji prijemni
		$godina_prijemnog = db_result($q610,0,0);
		//			$q630 = db_query("select id from akademska_godina where aktuelna=1");
		//			$nova_ak_god = db_result($q630,0,0)+1;
		
		//			if ($godina_prijemnog==$nova_ak_god) {
		
		// Moguće je da se asistent upisuje na 3. ciklus pa je $korisnik_student==false
		// U tom slučaju neće uopšte biti prikazan dio za studente i neće biti opcije za upis
		$korisnik_student = db_get("select privilegija from privilegije where osoba=$osoba AND privilegija='student'");
		if (!$korisnik_student) {
			?>
			<li><a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=upis&studij=<?=$r600[3]?>&semestar=1&godina=<?=$godina_prijemnog?>">Upiši kandidata na <?
					$q630 = db_query("select naziv from studij where id=$r600[3]");
					if (db_num_rows($q630) > 0)
						print "&quot;".db_result($q630,0,0)."&quot;";
					else
						print "prvu godinu studija";
					?>, 1. semestar, u akademskoj <?=db_result($q610,0,1)?> godini</a></li>
			<?
		}
		?>
		</ul><?
	}
	
	$q640 = db_query("select ss.naziv, us.opci_uspjeh, us.kljucni_predmeti, us.dodatni_bodovi, us.ucenik_generacije from srednja_skola as ss, uspjeh_u_srednjoj as us where us.srednja_skola=ss.id and us.osoba=$osoba");
	
	if (db_num_rows($q640)>0) {
		?>
		<b>Uspjeh u srednjoj školi:</b>
		<ul>
			<li>Škola: <?=db_result($q640,0,0)?></li>
			<li>Opći uspjeh: <?=db_result($q640,0,1)?>. Ključni predmeti: <?=db_result($q640,0,2)?>. Dodatni bodovi: <?=db_result($q640,0,3)?>. <?
				if (db_result($q640,0,4)>0) print "Učenik generacije.";
				?></li>
		</ul>
		<?
	}
}
