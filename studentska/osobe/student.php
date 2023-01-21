<?php


// Prikaz informativne sekcije za studente u kontekstu modula za osobe

function studentska_osobe_student() {
	global $registry, $conf_knjigovodstveni_servis;
	
	require_once("lib/student_studij.php"); // Za ima_li_uslov
	
	$osoba = int_param('osoba');
	$trenutna_godina = db_get("SELECT id FROM akademska_godina WHERE aktuelna=1");
	$naziv_ak_god = db_get("SELECT naziv FROM akademska_godina WHERE aktuelna=1");
	
	?>
	<hr>
	<h3>STUDENT</h3>
	<?
	
	// Trenutno upisan na semestar:
	$q220 = db_query("SELECT s.naziv, ss.semestar, ss.akademska_godina, ag.naziv, s.id, ts.trajanje, ns.naziv, ts.ciklus, status.naziv, ss.put
			FROM student_studij as ss, studij as s, akademska_godina as ag, tipstudija as ts, nacin_studiranja as ns, status_studenta status
			WHERE ss.student=$osoba and ss.studij=s.id and ag.id=ss.akademska_godina and s.tipstudija=ts.id and ss.nacin_studiranja=ns.id AND ss.status_studenta=status.id
			ORDER BY ag.naziv DESC");
	$studij="0";
	$studij_id=$semestar=0;
	$puta=1;
	$status_studenta = "";
	
	// Da li je ikada slušao nešto?
	$ikad_studij=$ikad_studij_id=$ikad_semestar=$ikad_ak_god=$studij_ciklus=-1;
	$ikad_ciklusi = $ikad_puta = array();
	
	while ($r220=db_fetch_row($q220)) {
		if ($r220[2]==$trenutna_godina && $r220[1]>$semestar) { //trenutna akademska godina
			$studij=$r220[0];
			$semestar = $r220[1];
			$studij_id=$r220[4];
			$studij_trajanje=$r220[5];
			$nacin_studiranja="kao $r220[6]";
			$studij_ciklus=$r220[7];
			if ($r220[8] != "Student") $status_studenta = " - " .$r220[8];
			$puta = $r220[9];
		} else if ($r220[2]>$ikad_ak_god || ($r220[2]==$ikad_ak_god && $r220[1]>$ikad_semestar)) {
			$ikad_studij=$r220[0];
			$ikad_semestar=$r220[1];
			$ikad_ak_god=$r220[2];
			$ikad_ak_god_naziv=$r220[3];
			$ikad_studij_id=$r220[4];
			$ikad_studij_trajanje=$r220[5];
			$ikad_puta["$ikad_studij_id-$ikad_semestar"] = $r220[9];
		}
		if (!in_array($r220[7], $ikad_ciklusi)) $ikad_ciklusi[] = $r220[7];
	}
	
	$prepisi_ocjena = "";
	if (count($ikad_ciklusi) > 1) {
		$ikad_ciklusi = array_reverse($ikad_ciklusi);
		foreach ($ikad_ciklusi as $i)
			if ($i == 99)
				$prepisi_ocjena .= "<br><a href=\"?sta=izvjestaj/index2&student=$osoba&ciklus=$i\">Samo stručni studij</a>";
			else
				$prepisi_ocjena .= "<br><a href=\"?sta=izvjestaj/index2&student=$osoba&ciklus=$i\">Samo $i. ciklus</a>";
	}
	
	
	// Izvjestaji
	
	?>
	<div style="float:left; margin-right:10px">
		<table width="100" border="1" cellspacing="0" cellpadding="0">
			<tr><td bgcolor="#777777" align="center">
					<font color="white"><b>IZVJEŠTAJI:</b></font>
				</td></tr>
			<tr><td align="center"><a href="?sta=izvjestaj/historija&student=<?=$osoba?>">
						<img src="static/images/32x32/report.png" border="0"><br/>Historija</a></td></tr>
			<tr><td align="center"><a href="?sta=izvjestaj/index2&student=<?=$osoba?>">
						<img src="static/images/32x32/report.png" border="0"><br/>Prepis ocjena</a> <?=$prepisi_ocjena?></td></tr>
			<tr><td align="center"><a href="?sta=izvjestaj/progress&student=<?=$osoba?>&razdvoji_ispite=0">
						<img src="static/images/32x32/report.png" border="0"><br/>Bodovi</a></td></tr>
			<tr><td align="center"><a href="?sta=izvjestaj/progress&student=<?=$osoba?>&razdvoji_ispite=1">
						<img src="static/images/32x32/report.png" border="0"><br/>Bodovi + nepoloženi ispiti</a></td></tr>
		</table>
	</div>
	<?
	
	// Aktivni moduli
	$modul_uou=$modul_kolizija=0;
	foreach ($registry as $r) {
		if (count($r) == 0) continue;
		if ($r[0]=="student/ugovoroucenju") $modul_uou=1;
		if ($r[0]=="student/kolizija") $modul_kolizija=1;
	}
	
	// Trenutno slusa studij
	
	$nova_ak_god=0;
	
	?>
	<p align="left">Trenutno (<b><?=$naziv_ak_god?></b>) upisan/a na:<br/>
	<?
	if ($studij=="0") {
		?>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nije upisan/a niti u jedan semestar!</p>
		<?
		
		// Proglasavamo zadnju akademsku godinu koju je slusao za tekucu
		// a tekucu za novu
		$nova_ak_god = $trenutna_godina;
		$naziv_nove_ak_god = $naziv_ak_god;
		if ($ikad_semestar != 0) {
			// Ako je covjek upisan u buducu godinu, onda je u toku upis
			if ($ikad_ak_god>$trenutna_godina) {
				$nova_ak_god=$ikad_ak_god;
				$naziv_nove_ak_god=$ikad_ak_god_naziv;
				$semestar=$ikad_semestar-1; // da se ne bi ispisivalo da drugi put sluša
			} else {
				$trenutna_godina = $ikad_ak_god;
				$naziv_ak_god = $ikad_ak_god_naziv;
				$semestar = $ikad_semestar;
				if ($semestar % 2 != 0) $semestar++; // Da ga ne bi pokušavalo upisati u parni semestar
			}
			// Zelimo da se provjeri ECTS:
			$studij = $ikad_studij;
			$studij_id = $ikad_studij_id;
			$studij_trajanje = $ikad_studij_trajanje;
			
		} else {
			// Nikada nije slušao ništa - ima li podataka o prijemnom ispitu?
			$q225 = db_query("select pt.akademska_godina, ag.naziv, s.id, s.naziv from prijemni_termin as pt, prijemni_prijava as pp, akademska_godina as ag, studij as s where pp.osoba=$osoba and pp.prijemni_termin=pt.id and pt.akademska_godina=ag.id and pp.studij_prvi=s.id order by ag.id desc, pt.id desc limit 1");
			if (db_num_rows($q225)>0) {
				$nova_ak_god = db_result($q225,0,0);
				$naziv_nove_ak_god = db_result($q225,0,1);
				$novi_studij = db_result($q225,0,3);
				$novi_studij_id = db_result($q225,0,2);
			}
		}
		
	} else {
		?>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>&quot;<?=$studij?>&quot;</b>, <?=$semestar?>. semestar (<?=$puta?>. put) <?=$nacin_studiranja?> <?=$status_studenta?> (<a href="?sta=studentska/osobe&amp;osoba=<?=$osoba?>&amp;akcija=ispis&amp;studij=<?=$studij_id?>&amp;semestar=<?=$semestar?>&amp;godina=<?=$trenutna_godina?>">ispiši sa studija</a>) (<a href="?sta=studentska/osobe&osoba=<?=$osoba?>&amp;akcija=promijeni_nacin&amp;studij=<?=$studij_id?>&amp;semestar=<?=$semestar?>&amp;godina=<?=$trenutna_godina?>">promijeni način studiranja</a>)</p>
		<?
		$q230 = db_query("select id, naziv from akademska_godina where id=" . ($trenutna_godina+1));
		if (db_num_rows($q230)>0) {
			$nova_ak_god = db_result($q230,0,0);
			$naziv_nove_ak_god = db_result($q230,0,1);
		}
	}
	
	require_once("studentska/osobe/zaduzenje.php");
	studentska_osobe_applet_zaduzenje($osoba);
	
	if ($nova_ak_god==0) { // Upis u tekućoj godini (ako nije kreirana nova)
		?>
		<a href="?sta=studentska/osobe&amp;osoba=<?=$osoba?>&amp;akcija=upis&amp;studij=<?=$studij_id?>&amp;semestar=<?=($semestar+1)?>&amp;godina=<?=$trenutna_godina?>">Upiši na <?=($semestar+1)?>. semestar</a>
		<?
		
		// Ispisujemo podatke o ugovoru o učenju
		if ($modul_uou==1) {
			$q270 = db_query("select s.naziv, u.semestar, u.kod from ugovoroucenju as u, studij as s where u.student=$osoba and u.akademska_godina=$trenutna_godina and u.studij=s.id order by u.semestar");
			if (db_fetch3($q270, $naziv_studija_ugovor, $semestar_ugovor, $kod_ugovora)) {
				// Uvijek se popunjava za neparni i parni semestar!
				$semestar_ugovor .= ". i " . ($semestar_ugovor+1) . ".";
				?>
				<p>Student je popunio/la <b>Ugovor o učenju</b> za <?=$naziv_studija_ugovor?>, <?=$semestar_ugovor?> semestar:<br>Kod: <b><?=$kod_ugovora?></b></p>
				<?
			} else {
				?>
				<p>Student NIJE popunio/la <b>Ugovor o učenju</b> za sljedeću akademsku godinu.</p>
				<?
			}
		}
	}
	
	
	// Pristup web servisu za uplate
	if ($conf_knjigovodstveni_servis) {
		require_once("studentska/osobe/kartica.php");
		studentska_osobe_applet_kartica($osoba, $jmbg);
	}
	
	
	// UPIS U SLJEDEĆU AK. GODINU
	
	if ($nova_ak_god!=0) { // Ne prikazuj podatke o upisu dok se ne kreira nova ak. godina
		
		
		?>
		<p>Upis u akademsku <b><?=$naziv_nove_ak_god?></b> godinu:<br />
		<?
		
		
		// Da li je vec upisan?
		$novi_studij_id = 0;
		$q235 = db_query("select s.naziv, ss.semestar, s.id, ss.put from student_studij as ss, studij as s where ss.student=$osoba and ss.studij=s.id and ss.akademska_godina=$nova_ak_god order by ss.semestar desc");
		if (db_num_rows($q235)>0) {
			$novi_studij=db_result($q235,0,0);
			$novi_semestar=db_result($q235,0,1);
			$novi_studij_id=db_result($q235,0,2);
			$nputa=db_result($q235,0,3);
			?>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Student je upisan na studij: <b><?=$novi_studij?></b>, <?=$novi_semestar?>. semestar (<?=$nputa?>. put). (<a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=ispis&studij=<?=$novi_studij_id?>&semestar=<?=$novi_semestar?>&godina=<?=$nova_ak_god?>">ispiši sa studija</a>)</p><?
			
		} else {
			
			// Ima li uslove za upis
			if ($semestar==0 && $ikad_semestar==-1) {
				// Upis na prvu godinu
				
				?>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nemamo podataka da je ovaj student ikada bio upisan na fakultet.</p><?
				if ($novi_studij_id) { // Podatak sa prijemnog
					?>
					<p><a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=upis&studij=<?=$novi_studij_id?>&semestar=1&godina=<?=$nova_ak_god?>">Upiši studenta na <?=$novi_studij?>, 1. semestar.</a></p>
					<?
				} else {
					?>
					<p><a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=upis&studij=<?=$novi_studij_id?>&semestar=1&godina=<?=$nova_ak_god?>">Upiši studenta na Prvu godinu studija, 1. semestar.</a></p>
					<?
				}
				
			} else if ($studij=="0") {
				if ($ikad_semestar%2==0) $ikad_semestar--;
				// Trenutno nije upisan na fakultet, ali upisacemo ga
				?>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=upis&studij=<?=$ikad_studij_id?>&semestar=<?=$ikad_semestar?>&godina=<?=$nova_ak_god?>">Ponovo upiši studenta na <?=$ikad_studij?>, <?=$ikad_semestar?>. semestar.</a></p>
				<?
				
			} else if ($semestar%2!=0) {
				// S neparnog na parni ide automatski
				?>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Student je stekao uslove za upis na &quot;<?=$studij?>&quot;, <?=($semestar+1)?> semestar</p>
				<p><a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=upis&studij=<?=$studij_id?>&semestar=<?=($semestar+1)?>&godina=<?=$trenutna_godina?>">Upiši studenta na &quot;<?=$studij?>&quot;, <?=($semestar+1)?> semestar.</a></p>
				<?
				
			} else {
				// Upis na neparni semestar - da li je student dao uslov?
				
				// Pokusacemo odrediti uslov na osnovu polozenih predmeta...
				global $zamger_predmeti_pao, $zamger_pao_ects, $uslov_debug;
				$ima_uslov = ima_li_uslov($osoba, $trenutna_godina);
				if (!$ima_uslov && $zamger_pao_ects == 0 && count($zamger_predmeti_pao) == 1 && $zamger_predmeti_pao[-1] == "(Nepoznat izborni predmet)")
					$ima_uslov = true;
				
				if ($ima_uslov) {
					if ($semestar == $studij_trajanje) {
						?>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Student je stekao/la uslove za upis na sljedeći ciklus studija</p>
						<p><a href="?sta=studentska/osobe&amp;osoba=<?=$osoba?>&amp;akcija=upis&amp;studij=0&amp;semestar=1&amp;godina=<?=$nova_ak_god?>">Upiši studenta na sljedeći ciklus studija.</a></p>
						<?
					} else {
						?>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Student je stekao/la uslove za upis na &quot;<?=$studij?>&quot;, <?=($semestar+1)?>. semestar</p>
						<p><a href="?sta=studentska/osobe&amp;osoba=<?=$osoba?>&amp;akcija=upis&amp;studij=<?=$studij_id?>&amp;semestar=<?=($semestar+1)?>&amp;godina=<?=$nova_ak_god?>">Upiši studenta na &quot;<?=$studij?>&quot;, <?=($semestar+1)?>. semestar.</a></p>
						<?
					}
				} else {
					if ($semestar == $studij_trajanje) {
						?>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Student <b>NIJE</b> stekao/la uslove za upis na sljedeći ciklus studija<br/>
						<?
						
					} else {
						?>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Student <b>NIJE</b> stekao/la uslove za &quot;<?=$studij?>&quot;, <?=($semestar+1)?>. semestar<br/>
						<?
					}
					
					?>
					(<?=count($zamger_predmeti_pao)?> nepoloženih predmeta, <?=$zamger_pao_ects?> ECTS kredita)
					</p>
					<p><a href="?sta=studentska/osobe&amp;osoba=<?=$osoba?>&amp;akcija=upis&amp;studij=<?=$studij_id?>&amp;semestar=<?=($semestar-1)?>&amp;godina=<?=$nova_ak_god?>">Ponovo upiši studenta na <?=$studij?>, <?=($semestar-1)?>. semestar (<?=($ikad_puta["$studij_id-".($semestar-1)]+1)?>. put).</a></p>
					<!--p><a href="?sta=studentska/osobe&amp;osoba=<?=$osoba?>&amp;akcija=upis&amp;studij=<?=$studij_id?>&amp;semestar=<?=($semestar+1)?>&amp;godina=<?=$nova_ak_god?>">Upiši studenta na <?=$sta?>.</a></p-->
					<?
				}
			}
			
		} // if ($q235... else ... -- nije vec upisan nigdje
		
		// Ugovor o učenju
		if ($modul_uou==1) {
			$q270 = db_query("select s.naziv, u.semestar, u.kod from ugovoroucenju as u, studij as s where u.student=$osoba and u.akademska_godina=$nova_ak_god and u.studij=s.id order by u.semestar");
			if (db_fetch3($q270, $naziv_studija_ugovor, $semestar_ugovor, $kod_ugovora)) {
				// Uvijek se popunjava za neparni i parni semestar!
				$semestar_ugovor .= ". i " . ($semestar_ugovor+1) . ".";
				?>
				<p>Student je popunio/la <b>Ugovor o učenju</b> za <?=$naziv_studija_ugovor?>, <?=$semestar_ugovor?> semestar:<br>Kod: <b><?=$kod_ugovora?></b></p>
				<?
			} else {
				?>
				<p>Student NIJE popunio/la <b>Ugovor o učenju</b> za sljedeću akademsku godinu.</p>
				<?
			}
		}
		
	} // if (db_num_rows($q230  -- da li postoji ak. god. iza aktuelne?
	
	
	// Kolizija
	if ($modul_kolizija==1) {
		$q280 = db_query("select count(*) from kolizija where student=$osoba and akademska_godina=$nova_ak_god");
		$ima_koliziju=0;
		if (db_result($q280,0,0)>0) {
			$ima_koliziju=$nova_ak_god;
		} else {
			// Probavamo i za trenutnu
			$q280 = db_query("select count(*) from kolizija where student=$osoba and akademska_godina=$trenutna_godina");
			if (db_result($q280,0,0)>0) {
				$ima_koliziju=$trenutna_godina;
			}
		}
		
		if ($ima_koliziju) { // provjeravamo septembar
			$kolizija_ok = true;
			$qc = db_query("select distinct predmet from septembar where student=$osoba and akademska_godina=$ima_koliziju");
			while ($rc = db_fetch_row($qc)) {
				$predmet = $rc[0];
				
				// Da li ima ocjenu?
				$qd = db_query("select count(*) from konacna_ocjena where student=$osoba and predmet=$predmet and ocjena>=6");
				if (db_result($qd,0,0)>0) continue;
				
				// Da li ima septembarskog roka?
				$qe = db_query("select i.id, k.prolaz from ispit as i, komponenta as k where i.akademska_godina=".($ima_koliziju-1)." and (MONTH(i.datum)=8 or MONTH(i.datum)=9) and (select count(*) from ispitocjene as io where io.ispit=i.id)>0 and i.predmet=$predmet and i.komponenta=k.id and k.naziv NOT LIKE 'Usmeni%'");
				if (db_num_rows($qe)==0) continue; // nema
				
				$polozio=false;
				$septembar_razlog = "";
				while ($re = db_fetch_row($qe)) {
					$qf = db_query("select ocjena from ispitocjene where ispit=$re[0] and student=$osoba");
					if (db_num_rows($qf)>0 && db_result($qf,0,0)>=$re[1]) {
						$polozio=true;
						break;
					}
				}
				if (!$polozio) {
					$kolizija_ok=false;
					$qg = db_query("select naziv from predmet where id=$predmet");
					$paopredmet=db_result($qg,0,0);
					break;
				}
			}
			
			if ($kolizija_ok) {
				?>
				<p>Student je popunio/la <b>Zahtjev za koliziju</b>. <a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=kolizija&godina=<?=$ima_koliziju?>">Kliknite ovdje da potvrdite upis na kolizione predmete.</a></p>
				<?
			} else {
				?>
				<p>Student je popunio/la <b>Zahtjev za koliziju</b> koji je neispravan (nije položio/la <?=$paopredmet?>). Potrebno ga je ponovo popuniti.</p>
				<?
			}
		}
	}
	
	
	// Upis studenta na pojedinačne predmete
	?>
	<p><a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=predmeti">Manuelni upis studenta na predmete / ispis sa predmeta.</a></p>
	<p><a href="?sta=izvjestaj/sv20&student=<?=$osoba?>&ugovor=da">ŠV-20 obrazac</a> * <a href="?sta=izvjestaj/upisni_list&student=<?=$osoba?>&ugovor=da">Upisni list</a> * <a href="?sta=izvjestaj/prijava_semestra&student=<?=$osoba?>&ugovor=da">List o prijavi semestra</a></p>
	<?
	
	print "\n<div style=\"clear:both\"></div>\n";
}
