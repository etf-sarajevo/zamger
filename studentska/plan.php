<?php

function studentska_plan(){
	global $userid,$user_siteadmin,$user_studentska;

	// Provjera privilegija
	if (!$user_studentska && !$user_siteadmin) {
		zamgerlog("nije studentska",3); // 3: error
		biguglyerror("Pristup nije dozvoljen.");
		return;
	}

	if (isset($_REQUEST['studij'])) 
		$studij = intval($_REQUEST['studij']);
	else
		$studij = 0;
	if (isset($_REQUEST['plan'])) 
		$plan = intval($_REQUEST['plan']);
	else
		$plan = 0;
	if (isset($_REQUEST['predmet'])) 
		$predmet = intval($_REQUEST['predmet']);
	else
		$predmet = 0;
	if (isset($_REQUEST['akcija'])) 
		$akcija = $_REQUEST['akcija'];
	else
		$akcija = "";

	?>
	<h1>Uređivanje plana i programa</h1>
	<h3>Odaberite studij: 
	<SELECT name="studij" id="studij" onchange="promijeniStudij()">
	<OPTION value="0">--- Odaberite studij ---</OPTION>
	<?
	$q10 = db_query("SELECT id, naziv FROM studij WHERE moguc_upis=1 ORDER BY tipstudija, naziv");
	while ($r10 = db_fetch_row($q10)) {
		?>
		<OPTION value="<?=$r10[0]?>" <? if ($r10[0]==$studij) print "SELECTED"; ?>><?=$r10[1]?></OPTION>
		<?
		if ($r10[0]==$studij) $studij_naziv = $r10[1];
	}
	?>
	</SELECT>
	</h3>

	<SCRIPT>function promijeniStudij() {
		studij = document.getElementById('studij').value;
		if (studij != 0) location.replace("?sta=studentska/plan&studij="+studij);
	}
	</SCRIPT>
	<?


	// Prikaz planova za odabrani studij

	if ($studij > 0 && $plan == 0) {
		if ($akcija === "novi") {
			$q15 = db_query("INSERT INTO plan_studija SET studij=$studij");
			zamgerlog2("kreiran plan studija", db_insert_id());
			?>
			Kreiran je novi plan studija.
			<script language="JavaScript">
			location.href='?sta=studentska/plan&studij=<?=$studij?>';
			</script>
			<?
			return;
		}

	
		?>
		<h3>Planovi i programi studija <?=$studij_naziv?></h3>
		<?
		$q20 = db_query("SELECT id, godina_vazenja, usvojen FROM plan_studija WHERE studij=$studij");
		if (db_num_rows($q20) > 0) {
			?>
			<table><tr><th>Godina važenja</th><th>Status</th><th>Akcije</th></tr>
			<?
		} else {
			?>
			<p>Nije definisan niti jedan plan studija za studij <?=$studij_naziv?></p>
			<?
		}

		while ($r20 = db_fetch_row($q20)) {
			$id_plana = $r20[0];
			?><tr>
				<td>
				<?
			if ($r20[1] > 0) {
				$q30 = db_query("SELECT naziv FROM akademska_godina WHERE id=".$r20[1]);
				print db_result($q30,0,0);
			} else
				print "u budućnosti";
			?>
				</td>
				<td><? 
				if ($r20[2]==1) {
					$q40 = db_query("SELECT COUNT(*) FROM plan_studija WHERE studij=$studij AND id>$id_plana AND usvojen=1");
					if (db_result($q40,0,0)==0) print "Važeći";
					else print "Raniji plan";
					$akcija_brisanje = "";
				} else {
					print "Predložen";
					$akcija_brisanje = " - <a href=\"?sta=studentska/plan&amp;studij=$studij&amp;plan=$id_plana&amp;akcija=brisanje\">obriši</a>";
				}
				?></td>
				<td><a href="?sta=studentska/plan&amp;studij=<?=$studij?>&amp;plan=<?=$id_plana?>">uređivanje</a><?=$akcija_brisanje?></td>
			</tr>
			<?
		}
		if (db_num_rows($q20) > 0)  print "\n</table>\n";
		
		?>
		<p><a href="?sta=studentska/plan&amp;studij=<?=$studij?>&amp;akcija=novi">Kreiraj novi plan i program</a></p>
		<?
	}
	
	
	// Uređivanje plana ili detalji o planu
	if ($studij == 0 || $plan == 0) return;

	$q100 = db_query("SELECT godina_vazenja, usvojen FROM plan_studija WHERE studij=$studij AND id=$plan");
	if (db_num_rows($q100) == 0) {
		biguglyerror("Nepostojeći plan studija");
		return;
	}
	$godina_vazenja = db_result($q100,0,0);
	$usvojen = db_result($q100,0,1);
	
		
	
	// OSNOVNE AKCIJE NAD PLANOM
	
	// Brisanje plana studija
	if ($akcija === "brisanje") {
		$q15 = db_query("DELETE FROM plan_studija WHERE id=$plan");
		zamgerlog("obrisan plan studija $plan", 4);
		zamgerlog2("obrisan plan studija", $plan);
		?>
		Obrisan je plan studija.
		<script language="JavaScript">
		setTimeout(function() {
			location.href='?sta=studentska/plan&studij=<?=$studij?>';
		}, 500);
		</script>
		<?
		return;
	}

	// Potvrda važenja plana studija
	if ($akcija === "potvrda") {
		$aktuelna_ag = db_get("SELECT id FROM akademska_godina WHERE aktuelna=1");
		
		print genform("POST");
		?>
		<h3>Prihvaćanje predloženog plana i programa za studij &quot;<?=$studij_naziv?>&quot;</h3>
		<input type="hidden" name="akcija" value="potvrda_submit">
		Odaberite godinu početka važenja prihvaćenog plana: 
		<?=db_dropdown("akademska_godina", $aktuelna_ag)?><br><br>
		<input type="submit" value="  Potvrdi!  ">
		</form>
		<?
		return;
	}
	
	if ($akcija === "potvrda_submit") {
		$godina_vazenja = int_param("_lv_column_akademska_godina");
		
		db_query("UPDATE plan_studija SET usvojen=1, godina_vazenja=$godina_vazenja WHERE id=$plan");
		zamgerlog("plan studija proglasen za vazeci $plan ag$godina_vazenja", 4);
		zamgerlog2("plan studija proglasen za vazeci", $plan);
		
		?>
		Plan studija je proglašen za važeći.
		<script language="JavaScript">
		setTimeout(function() {
			location.href='?sta=studentska/plan&studij=<?=$studij?>&plan=<?=$plan?>';
		}, 500);
		</script>
		<?
		return;
	}

	
	if ($godina_vazenja) {
		$q110 = db_query("SELECT naziv FROM akademska_godina WHERE id=$godina_vazenja");
		$ispis_vazenja = db_result($q110,0,0);
	} else {
		$ispis_vazenja = "";
	}

	if ($usvojen == 1) {
		$q120 = db_query("SELECT godina_vazenja FROM plan_studija WHERE studij=$studij AND id>$plan AND usvojen=1 ORDER by id");
		if (db_num_rows($q120)==0) $ispis_vazenja = "važeći ($ispis_vazenja)";
		else {
			$q110 = db_query("SELECT naziv FROM akademska_godina WHERE id=".db_result($q120,0,0));
			$ispis_vazenja = "(".$ispis_vazenja." - ".db_result($q110,0,0).")";
		}
	} else {
		if ($ispis_vazenja !== "") $ispis_vazenja = "prijedlog (".$ispis_vazenja.")";
		else $ispis_vazenja = "prijedlog - <a href=\"".genuri()."&amp;akcija=potvrda\">prihvati prijedlog</a>";
	}

	?>
	<h3>Studij <?=$studij_naziv?> - <?=$ispis_vazenja?></h3>
	<p><a href="?sta=studentska/plan&amp;studij=<?=$studij?>">Nazad na odabir plana</a></p>

	<?
	
	
	// EDITOVANJE PASOŠA
	if (isset($_REQUEST['pregled_pasosa'])) {
		$id_pasosa = intval($_REQUEST['pregled_pasosa']);

		// AKCIJE:

		if (isset($_REQUEST['akcija']) && $_REQUEST['akcija'] == "izmjena_pasosa") {
			$naziv = db_escape($_REQUEST['naziv']);
			$naziv_en = db_escape($_REQUEST['naziv_en']);
			$sifra = db_escape($_REQUEST['sifra']);
			$ects = _floatval($_REQUEST['ects']);

			$sati_predavanja = intval($_REQUEST['sati_predavanja']);
			$sati_vjezbi = intval($_REQUEST['sati_vjezbi']);
			$sati_tutorijala = intval($_REQUEST['sati_tutorijala']);

			$cilj_kursa = db_escape($_REQUEST['cilj_kursa']);
			$cilj_kursa_en = db_escape($_REQUEST['cilj_kursa_en']);
			$program = db_escape($_REQUEST['program']);
			$program_en = db_escape($_REQUEST['program_en']);
			$obavezna_literatura = db_escape($_REQUEST['obavezna_literatura']);
			$dopunska_literatura = db_escape($_REQUEST['dopunska_literatura']);

			$didakticke_metode = db_escape($_REQUEST['didakticke_metode']);
			$didakticke_metode_en = db_escape($_REQUEST['didakticke_metode_en']);
			$nacin_provjere_znanja = db_escape($_REQUEST['nacin_provjere_znanja']);
			$nacin_provjere_znanja_en = db_escape($_REQUEST['nacin_provjere_znanja_en']);
			$napomene = db_escape($_REQUEST['napomene']);
			$napomene_en = db_escape($_REQUEST['napomene_en']);

			$komentar_prijedloga = db_escape($_REQUEST['komentar_prijedloga']);

			$q2100 = db_query("INSERT INTO pasos_predmeta SET predmet=$predmet, usvojen=0, predlozio=$userid, vrijeme_prijedloga=NOW(), komentar_prijedloga='$komentar_prijedloga', sifra='$sifra', naziv='$naziv', naziv_en='$naziv_en', ects='$ects', sati_predavanja='$sati_predavanja', sati_vjezbi='$sati_vjezbi', sati_tutorijala='$sati_tutorijala', cilj_kursa='$cilj_kursa', cilj_kursa_en='$cilj_kursa_en', program='$program', program_en='$program_en', obavezna_literatura='$obavezna_literatura', dopunska_literatura='$dopunska_literatura', didakticke_metode='$didakticke_metode', didakticke_metode_en='$didakticke_metode_en', nacin_provjere_znanja='$nacin_provjere_znanja', nacin_provjere_znanja_en='$nacin_provjere_znanja_en', napomene='$napomene', napomene_en='$napomene_en'");
			$id_pasosa = db_insert_id();

			nicemessage("Ažuriran pasoš predmeta");
			zamgerlog2("azuriran pasos predmeta");
			?>
			<p><a href="?sta=studentska/plan&amp;studij=<?=$studij?>&amp;plan=<?=$plan?>&amp;predmet=<?=$predmet?>&amp;pregled_pasosa=<?=$id_pasosa?>">Nastavak</a></p>
			<?
			return;
		}
		

		if (isset($_REQUEST['akcija']) && $_REQUEST['akcija'] == "potvrdi_pasos") {
			if (isset($_REQUEST['fakat_potvrdi'])) {
				$q2020 = db_query("SELECT pp.id FROM plan_studija_predmet psp, pasos_predmeta pp WHERE psp.plan_studija=$plan AND psp.pasos_predmeta=pp.id AND pp.predmet=$predmet");
				if (db_num_rows($q2020) > 0) {
					$id_aktuelne_verzije = db_result($q2020,0,0);
					$q2200 = db_query("UPDATE plan_studija_predmet SET pasos_predmeta=$id_pasosa WHERE plan_studija=$plan AND pasos_predmeta=$id_aktuelne_verzije");
				} else {
					$q2210 = db_query("SELECT pp.id, pis.id FROM plan_studija_predmet psp, pasos_predmeta pp, plan_izborni_slot pis WHERE psp.plan_studija=$plan AND psp.plan_izborni_slot=pis.id AND pis.pasos_predmeta=pp.id AND pp.predmet=$predmet");
					$id_aktuelne_verzije = db_result($q2210,0,0);
					$id_izbornog_slota = db_result($q2210,0,0);
					$q2220 = db_query("UPDATE plan_izborni_slot SET pasos_predmeta=$id_pasosa WHERE id=$id_izbornog_slota AND pasos_predmeta=$id_aktuelne_verzije");
				}

				nicemessage("Pasoš predmeta postavljen za aktuelni");
				zamgerlog2("pasos predmeta postavljen za aktuelni");
				?>
				<p><a href="?sta=studentska/plan&amp;studij=<?=$studij?>&amp;plan=<?=$plan?>&amp;predmet=<?=$predmet?>&amp;pregled_pasosa=<?=$id_pasosa?>">Nastavak</a></p>
				<?
				return;
			}

			?>
			<?=genform("POST");?>
			<input type="hidden" name="fakat_potvrdi" value="da">
			<p>Da li ste sigurni da želite ovu verziju pasoša proglasiti za važeću na studiju <?=$studij_naziv?>  - <?=$ispis_vazenja?> ??</p>
			<input type="submit" value="Potvrda">
			&nbsp;<a href="?sta=studentska/plan&amp;studij=<?=$studij?>&amp;plan=<?=$plan?>&amp;predmet=<?=$predmet?>&amp;pregled_pasosa=<?=$id_pasosa?>">Nazad</a></p>
			</form>
			<?
			return;
		}


		// PRIKAZ PASOŠA

		?>
		<p><a href="?sta=studentska/plan&amp;studij=<?=$studij?>&amp;plan=<?=$plan?>&amp;predmet=<?=$predmet?>">Nazad na uređivanje plana i programa</a></p>
		<?

		$greska = array();
		
		// Podaci o pasošu za prikaz
		
		$q2000 = db_query("SELECT *, UNIX_TIMESTAMP(vrijeme_prijedloga) vrijeme FROM pasos_predmeta WHERE id=$id_pasosa");
		$pasos = db_fetch_assoc($q2000);
		$vrijeme_prijedloga = date("d.m.Y. h:i", $pasos['vrijeme']);
		
		$q2010 = db_query("SELECT ime, prezime FROM osoba WHERE id=".$pasos['predlozio']);
		$predlozio = db_result($q2010,0,1)." ".db_result($q2010,0,0);
		
		// Koja je aktuelna verzija pasoša?
		$q2020 = db_query("SELECT pp.id FROM plan_studija_predmet psp, pasos_predmeta pp WHERE psp.plan_studija=$plan AND psp.pasos_predmeta=pp.id AND pp.predmet=$predmet");
		if (db_num_rows($q2020) == 0) {
			$q2020 = db_query("SELECT pp.id FROM plan_studija_predmet psp, pasos_predmeta pp, plan_izborni_slot pis WHERE psp.plan_studija=$plan AND psp.plan_izborni_slot=pis.id AND pis.pasos_predmeta=pp.id AND pp.predmet=$predmet");
		}
		$id_aktuelne_verzije = db_result($q2020,0,0);
		if ($id_aktuelne_verzije == $id_pasosa) $text_aktuelna = "- <b><i>aktuelna verzija</i></b>"; else $text_aktuelna = " - <a href=\"?sta=studentska/plan&amp;studij=$studij&amp;plan=$plan&amp;predmet=$predmet&amp;uporedi_pasos=$id_pasosa\">uporedi sa aktuelnom verzijom</a>";
		
		?>
		<h2>Predmet: <b><?=$pasos['naziv']?></b></h2>
		
		<p>Verzija pasoša: <?=$pasos['komentar_prijedloga']?> (<?=$predlozio?>, <?=$vrijeme_prijedloga?>) <?=$text_aktuelna?></p>
		
		<?
		if ($id_aktuelne_verzije != $id_pasosa || $pasos['usvojen'] == 0) {
			?>
			<?=genform("POST");?>
			<input type="hidden" name="akcija" value="potvrdi_pasos">
			<input type="submit" value=" Potvrdi pasoš predmeta ">
			</form>
			<?
		}
		?>

		<STYLE>
			.mylabel { display: inline-block; width: 200px; }
		</STYLE>
		<?=genform("POST");?>
		<input type="hidden" name="akcija" value="izmjena_pasosa">
		<?
		
		_daj_textbox("Naziv predmeta", "naziv", $pasos, $greska, 40);
		_daj_textbox("Naziv predmeta (en)", "naziv_en", $pasos, $greska, 40);
		_daj_textbox("Šifra predmeta", "sifra", $pasos, $greska);
		_daj_textbox("Broj ECTS kredita", "ects", $pasos, $greska);
		print "<p>&nbsp;</p>\n";
		
		_daj_textbox("Broj sati predavanja", "sati_predavanja", $pasos, $greska);
		_daj_textbox("Broj sati vježbi", "sati_vjezbi", $pasos, $greska);
		_daj_textbox("Broj sati tutorijala", "sati_tutorijala", $pasos, $greska);
		print "<p><b>Ukupno:</b> ".($pasos['sati_predavanja']+$pasos['sati_vjezbi']+$pasos['sati_tutorijala'])." sati</p>\n";
		print "<p>&nbsp;</p>\n";

		_daj_textarea("Cilj kursa", "cilj_kursa", $pasos, $greska);
		_daj_textarea("Cilj kursa (en)", "cilj_kursa_en", $pasos, $greska);
		_daj_textarea("Program predmeta", "program", $pasos, $greska);
		_daj_textarea("Program predmeta (en)", "program_en", $pasos, $greska);
		print "<p>&nbsp;</p>\n";

		_daj_textarea("Obavezna literatura", "obavezna_literatura", $pasos, $greska);
		_daj_textarea("Dopunska literatura", "dopunska_literatura", $pasos, $greska);
		_daj_textarea("Didaktičke metode", "didakticke_metode", $pasos, $greska);
		_daj_textarea("Didaktičke metode (en)", "didakticke_metode_en", $pasos, $greska);
		_daj_textarea("Načini provjere znanja", "nacin_provjere_znanja", $pasos, $greska);
		_daj_textarea("Načini provjere znanja (en)", "nacin_provjere_znanja_en", $pasos, $greska);
		_daj_textarea("Napomene", "napomene", $pasos, $greska);
		_daj_textarea("Napomene (en)", "napomene_en", $pasos, $greska);
		print "<p>&nbsp;</p>\n";
		
		?>
		<p><input type="submit" value=" Ažuriraj pasoš "> Napišite komentar vaše izmjene: <input type="text" name="komentar_prijedloga" size="50"></p>
		<?
		
		print "</form>";
		
		return;
	}

	// UPOREĐIVANJE VERZIJA PASOŠA
	if (isset($_REQUEST['uporedi_pasos'])) {
		$id_pasosa = intval($_REQUEST['uporedi_pasos']);

		// Koja je aktuelna verzija pasoša?
		$q2020 = db_query("SELECT pp.id FROM plan_studija_predmet psp, pasos_predmeta pp WHERE psp.plan_studija=$plan AND psp.pasos_predmeta=pp.id AND pp.predmet=$predmet");
		if (db_num_rows($q2020) == 0) {
			$q2020 = db_query("SELECT pp.id FROM plan_studija_predmet psp, pasos_predmeta pp, plan_izborni_slot pis WHERE psp.plan_studija=$plan AND psp.plan_izborni_slot=pis.id AND pis.pasos_predmeta=pp.id AND pp.predmet=$predmet");
		}
		$id_aktuelne_verzije = db_result($q2020,0,0);

		if ($id_aktuelne_verzije == $id_pasosa) {
			nicemessage("Ovo je aktuelna verzija pasoša.");
			?>
			<p>Možete upoređivati druge verzije sa aktuelnom.</p>
			<p><a href="?sta=studentska/plan&amp;studij=<?=$studij?>&amp;plan=<?=$plan?>&amp;predmet=<?=$predmet?>">Nazad</a></p>
			<?
		}

		$q2300 = db_query("SELECT * FROM pasos_predmeta WHERE id=$id_pasosa");
		$odabrani_pasos = db_fetch_assoc($q2300);
		$q2310 = db_query("SELECT * FROM pasos_predmeta WHERE id=$id_aktuelne_verzije");
		$aktuelni_pasos = db_fetch_assoc($q2310);

		?>
		<table border="0"><tr><th>Polje</th><th>Odabrani pasoš</th><th>Aktuelna verzija</th></tr>
		<?
		foreach ($odabrani_pasos as $polje => $odabrani) {
			$aktuelni = $aktuelni_pasos[$polje];
			if ($odabrani == $aktuelni_pasos[$polje]) continue;
			if ($polje === intval($polje)) continue;
			if ($polje == "id" || $polje == "vrijeme_prijedloga" || $polje == "komentar_prijedloga") continue;

			$odabrani = str_replace("\n", "<br>", $odabrani);
			$aktuelni = str_replace("\n", "<br>", $aktuelni);

			print "<tr><td>$polje</td><td style=\"word-wrap: break-word\">$odabrani</td><td style=\"word-wrap: break-word\">$aktuelni</td></tr>\n";
		}
		?></table>

		<p><a href="?sta=studentska/plan&amp;studij=<?=$studij?>&amp;plan=<?=$plan?>&amp;predmet=<?=$predmet?>">Podešavanje plana i programa</a></p>
		<p><a href="?sta=studentska/plan&amp;studij=<?=$studij?>&amp;plan=<?=$plan?>&amp;predmet=<?=$predmet?>&amp;pregled_pasosa=<?=$id_pasosa?>">Pregled i izmjena pasoša</a></p>

		<?
		return;
	}


	// Upit za sve predmete na studiju
/*	$q130 = db_query("	(SELECT pp.predmet, pp.naziv, pp.sifra, pp.ects, psp.semestar sem, psp.obavezan obvz, NULL
	FROM plan_studija_predmet as psp, pasos_predmeta as pp
	WHERE psp.plan_studija=1 AND psp.obavezan=1 and psp.pasos_predmeta=pp.id)
        UNION ALL
	(SELECT pp.predmet, pp.naziv, pp.sifra, pp.ects, psp.semestar sem, psp.obavezan obvz, pis.id
	FROM plan_studija_predmet as psp, pasos_predmeta as pp, plan_izborni_slot as pis
	WHERE psp.plan_studija=1 AND psp.obavezan=0 and psp.plan_izborni_slot=pis.id and pis.pasos_predmeta=pp.id)
	ORDER by sem, obvz DESC");*/
	
	?>
	<table border="0"><tr><td width="40%" valign="top">
	<table><tr><th>Predmet</th><th>Šifra</th><th>ECTS</th></tr>
	<?

	// Tabela predmeta
	$q20 = db_query("SELECT ts.trajanje FROM tipstudija ts, studij s
	WHERE s.id=$studij AND s.tipstudija=ts.id");
	$trajanje = db_result($q20,0,0);
	$space = "&nbsp;&nbsp;";
	for ($semestar=1; $semestar<=$trajanje; $semestar++) {
		$semestar_ispis = "";
		$total_ects_low = $total_ects_high = 0;

		$parnepar = "#fff";
		$q30 = db_query("select pp.predmet, pp.naziv, pp.sifra, pp.ects from pasos_predmeta pp, plan_studija_predmet as psp where psp.plan_studija=$plan and psp.semestar=$semestar and psp.obavezan=1 and psp.pasos_predmeta=pp.id order by pp.naziv");
		while ($r30 = db_fetch_row($q30)) {
			$semestar_ispis .= "<tr bgcolor=\"$parnepar\">\n";
			if ($predmet == $r30[0])
				$semestar_ispis .= "<td>$r30[1]</td>\n";
			else
				$semestar_ispis .= "<td><a href=\"?sta=studentska/plan&studij=$studij&plan=$plan&predmet=$r30[0]\">$r30[1]</a></td>\n";
			$semestar_ispis .= "<td>$r30[2]</td><td>$r30[3]</td></tr>\n";
			if ($parnepar == "#fff") $parnepar = "#eee"; else $parnepar="#fff";
			$total_ects_low += $r30[3];
			$total_ects_high += $r30[3];
		}

		$q40 = db_query("select plan_izborni_slot, count(plan_izborni_slot) from plan_studija_predmet where plan_studija=$plan and semestar=$semestar and obavezan=0 group by plan_izborni_slot order by plan_izborni_slot");
		$count=1;
		while ($r40 = db_fetch_row($q40)) {
			$oldparnepar = $parnepar;
			if ($parnepar == "#fff") $parnepar = "#eee"; else $parnepar="#fff";

			// Kreiramo ispis za predmete u izbornom slotu ($is_ispis) i određujemo ECTS kredite za slot
			$is_ects_low = 0; $is_ects_high = 0;
			$is_ispis = "";
			$q50 = db_query("select pp.predmet, pp.naziv, pp.sifra, pp.ects from pasos_predmeta as pp, plan_izborni_slot as pis where pis.id=".$r40[0]." and pis.pasos_predmeta=pp.id order by pp.naziv");
			while ($r50 = db_fetch_row($q50)) {
				$is_ispis .= "<tr bgcolor=\"$parnepar\">\n";
				if ($predmet == $r50[0])
					$is_ispis .= "<td>$space$space$r50[1]</td>\n";
				else
					$is_ispis .= "<td>$space$space<a href=\"?sta=studentska/plan&studij=$studij&plan=$plan&predmet=$r50[0]\">$r50[1]</a></td>\n";
				$is_ispis .= "<td>$r50[2]</td><td>$r50[3]</td></tr>\n";
				if ($parnepar == "#fff") $parnepar = "#eee"; else $parnepar="#fff";
				if ($is_ects_low > $r50[3] || $is_ects_low == 0) $is_ects_low = $r50[3];
				if ($is_ects_high < $r50[3]) $is_ects_high = $r50[3];
			}
			
			// Dodajemo izborni slot na ispis za semestar
			$semestar_ispis .= "<tr bgcolor=\"$oldparnepar\"><td colspan=\"2\"><b>Izborni predmet $count ";
			if ($r40[1]>1) {
				for ($i=2; $i<=$r40[1]; $i++) {
					$semestar_ispis .= "i ".($count+$i-1)." ";
				}
				$count = $count+$r40[1]-1;
			} else $count++;
			$semestar_ispis .= "<a href=\"?sta=studentska/plan&amp;akcija=prosiri_izborni_slot&amp;studij=$studij&amp;plan=$plan&amp;semestar=$semestar&amp;pis=" . $r40[0]."\">[+]</a>";
			$semestar_ispis .= "</b></td>\n";
			if ($is_ects_low == $is_ects_high) 
				$semestar_ispis .= "<td>$is_ects_low</td>";
			else
				$semestar_ispis .= "<td>$is_ects_low - $is_ects_high</td>";
			$semestar_ispis .= "</tr>\n";
			$semestar_ispis .= $is_ispis;

			if ($is_ects_low != 0) { // Ako ima predmeta u slotu?
				$total_ects_low += $is_ects_low * $r40[1];
				$total_ects_high += $is_ects_high * $r40[1];
			}
		}
		
		// Ispis
		?>
		<tr bgcolor="#ddd"><td colspan="2"><b><?=$semestar?>. semestar</b></td>
		<?
		if ($total_ects_high < 30)
			print "<td style=\"color:#f00\">";
		else
			print "<td>";
		if ($total_ects_low == $total_ects_high) 
			print "<b>$total_ects_low</b></td>";
		else
			print "<b>$total_ects_low - $total_ects_high</b></td>";
		print "</tr>\n";
		
		print $semestar_ispis;
	}
	
	print "</table>";
	
	
	?>
	</td><td width="60%" valign="top">
	<?
	
	
	// PROŠIRENJE IZBORNOG SLOTA
	if (isset($_REQUEST['akcija']) && $_REQUEST['akcija'] == "prosiri_izborni_slot") {
		$pis = intval($_REQUEST['pis']);
		$semestar = intval($_REQUEST['semestar']);
		
		$q40 = db_query("select plan_izborni_slot, count(plan_izborni_slot) from plan_studija_predmet where plan_studija=$plan and semestar=$semestar and obavezan=0 group by plan_izborni_slot order by plan_izborni_slot");
		$count=1;
		while ($r40 = db_fetch_row($q40)) {
			$broj_slotova = $r40[1];
			if ($r40[0] == $pis) break;
			if ($broj_slotova>1) 
				$count = $count+$broj_slotova-1;
			else $count++;
		}
		
		if (isset($_REQUEST['fakatradi']) && $_REQUEST['fakatradi'] == "1") {
			$novi_broj_slotova = intval($_REQUEST['broj_slotova']);
			while ($novi_broj_slotova > $broj_slotova) {
				$q38 = db_query("INSERT INTO plan_studija_predmet SET plan_studija=$plan, plan_izborni_slot=$pis, semestar=$semestar, obavezan=0, potvrdjen=1");
				$broj_slotova++;
			}
			while ($novi_broj_slotova < $broj_slotova) {
				$q39 = db_query("DELETE FROM plan_studija_predmet WHERE plan_studija=$plan AND plan_izborni_slot=$pis AND semestar=$semestar AND obavezan=0 LIMIT 1");
				$broj_slotova--;
			}
			?>
			Promijenjen broj izbornih slotova.
			<script language="JavaScript">
			location.href='?sta=studentska/plan&studij=<?=$studij?>&plan=<?=$plan?>';
			</script>
			<?
			return;
		}
		
		
		$semestar_ispis = $count;
		for ($i=2; $i<=$broj_slotova; $i++) {
			$semestar_ispis .= " i ".($count+$i-1);
		}

		
		?>
		<?=genform("POST")?>
		<input type="hidden" name="fakatradi" value="1">
		<h3>Broj izbornih slotova za Izborni predmet <?=$semestar_ispis?> (semestar <?=$semestar?>)</h3>
		<p>Broj slotova: <input type="text" name="broj_slotova" value="<?=$broj_slotova?>"> <input type="submit" value="Promijeni"></p>
		</form>
		<?
	}

	else if ($predmet == 0) {
		// Dodavanje novog predmeta u NPP
		?>
		<STYLE>
			.mylabel { display: inline-block; width: 150px; }
		</STYLE>
		
		<h3>Dodavanje novog predmeta</h3>
		<?=genform("POST");?>
		<input type="hidden" name="akcija" value="novi_predmet">
		<?
		
		$greska_naziv = ""; $greska_sifra = ""; $greska_ects = "";
		if (isset($_REQUEST['akcija']) && $_REQUEST['akcija'] == "novi_predmet") {
			$naziv = trim(db_escape($_REQUEST['naziv']));
			$sifra = trim(db_escape($_REQUEST['sifra']));
			$ects = _floatval($_REQUEST['ects']);
			$semestar = intval($_REQUEST['semestar']);
			$izborni_slot = 0;
			if (isset($_REQUEST['izborni_slot'])) 
				$izborni_slot = intval($_REQUEST['izborni_slot']);
			
			// Provjera podataka
			if (!isset($_REQUEST['potvrda'])) {
				// Validacija
				if (strlen($naziv) < 3)
					$greska_naziv = "Naziv predmeta je prekratak";
				if (strlen($sifra) < 3)
					$greska_sifra = "Šifra predmeta je prekratka";
				if ($ects <= 0)
					$greska_ects = "Vrijednost ECTS kredita je neispravna ili 0";
				
				// Da li već postoji predmet sa sličnim imenom ili šifrom
				if ($greska_naziv == "" && $greska_sifra == "") {
					$dijelovi = explode(" ",$naziv);
					$kratki_naziv = ""; $dio_upita = "";
					foreach ($dijelovi as $dio) {
						$kratki_naziv .= strtoupper(substr($dio,0,1));
						$dio_upita .= "OR naziv LIKE '%$dio%' ";
					}
					$q500 = db_query("SELECT id, naziv, sifra FROM predmet WHERE naziv LIKE '%$naziv%' OR sifra LIKE '%$sifra%'");
					if (db_num_rows($q500) == 0)
						$q500 = db_query("SELECT id, naziv, sifra FROM predmet WHERE kratki_naziv='$kratki_naziv' $dio_upita");
					if (db_num_rows($q500) > 0) {
						?>
						<p><b>Pronađeni su sljedeći slični predmeti.</b> Da li želite da dodate postojeći predmet u plan i program ili da kreirate novi?<br>
						<?
						while ($r500 = db_fetch_row($q500)) {
							?>
							<input type="radio" name="postojeci_predmet" value="<?=$r500[0]?>"> <?=$r500[1]?> (<?=$r500[2]?>) <br>
							<?
						}
						?>
						<input type="radio" name="postojeci_predmet" value="0"> Kreiranje novog predmeta</p>
						<p>&nbsp;</p>
						<?
					}
				}
			
				// Ako je izborni, pitamo da li će u postojeći slot ili kreiranje novog slota
				// Ako nema izbornih slotova, uzimamo $izborni_slot=0 što znači kreiranje novog
				if (isset($_REQUEST['izborni'])) {
					if (!isset($_REQUEST['izborni_slot'])) {
						$q400 = db_query("SELECT plan_izborni_slot, COUNT(*) FROM plan_studija_predmet WHERE plan_studija=$plan AND semestar=$semestar AND plan_izborni_slot IS NOT NULL GROUP BY plan_izborni_slot ORDER BY plan_izborni_slot");
						if (db_num_rows($q400) > 0) {
							?>
							<h3>Dodavanje novog predmeta - odabir izbornog slota</h3>
							<p>Da li želite dodati novi predmet u postojeći slot (skupinu izbornih predmeta) ili kreirati novu skupinu izbornih predmeta? Studenti moraju izabrati određeni broj predmeta iz svakog slota.</p>
							<?=genform("POST");?>
							<?
							$broj_predmeta = 1;
							while ($r400 = db_fetch_row($q400)) {
								?>
								<input type="radio" name="izborni_slot" value="<?=$r400[0]?>">Izborni predmet <?=$broj_predmeta?>
								<?
								for ($i=2; $i<=$r400[1]; $i++)
									print "i $i ";
								print "<br>";
								$broj_predmeta += $r400[1];
							}
							
							?>
							<input type="radio" name="izborni_slot" value="0">Kreiraj novi slot<br>
							<input type="submit" value=" Šalji ">
							</form>
							<?
							return;
						}
						// Ne postoji ni jedan izborni slot, nastavljamo sa kreiranjem novog.
					}
				}
				
				// Ako nema grešaka završavamo formu, u suprotnom pustićemo da se ponovo popune polja
				if ($greska_naziv == "" && $greska_sifra == "" && $greska_ects == "") {
					?>
					<input type="hidden" name="potvrda" value="true">
					<p>Provjerite još jednom podatke i kliknite na dugme &quot;Potvrda&quot;. Ako ste pogriješili u podacima koristite opciju &quot;Nazad&quot; vašeg web preglednika.</p>
					<table border="0">
					<tr><td>Naziv</td><td><b><?=$naziv?></b></td></tr>
					<tr><td>Šifra</td><td><b><?=$sifra?></b></td></tr>
					<tr><td>ECTS </td><td><b><?=$ects?></b></td></tr>
					<tr><td>Semestar</td><td><b><?=$semestar?>. semestar</b></td></tr>
					<tr><td>Izborni?</td><td><b><? if (isset($_REQUEST['izborni'])) print "DA"; else print "NE"; ?></b></td></tr>
					</table>
					<input type="submit" value="Potvrda">
					</form>
					<?
					return;
				}
			}
			
			// Kreiranje novog predmeta
			if (isset($_REQUEST['potvrda'])) {
				// Da li dodajemo postojeći predmet u NPP ili kreiramo novi?
				if (isset($_REQUEST['postojeci_predmet']) && intval($_REQUEST['postojeci_predmet'])>0) {
					$id_predmeta = intval($_REQUEST['postojeci_predmet']);
					
					// Uzećemo najnoviji pasoš za ovaj predmet
					// Po mogućnosti usvojen
					$q405 = db_query("SELECT id FROM pasos_predmeta WHERE predmet=$id_predmeta ORDER BY usvojen DESC, id DESC LIMIT 1");
					$id_pasosa = db_result($q405,0,0);

				} else {
					// Institucija potrebna za tabelu predmet
					$q410 = db_query("SELECT institucija FROM studij WHERE id=$studij");
					$institucija = db_result($q410,0,0);
					
					// Određujemo kratki naziv
					$dijelovi = explode(" ",$naziv);
					$kratki_naziv = "";
					foreach ($dijelovi as $dio)
						$kratki_naziv .= strtoupper(substr($dio,0,1));

					$q420 = db_query("INSERT INTO predmet SET sifra='$sifra', naziv='$naziv', institucija=$institucija, ects=$ects, kratki_naziv='$kratki_naziv'");
					$id_predmeta = db_insert_id();
					
					// Kreiramo default pasoš predmeta
					$q430 = db_query("INSERT INTO pasos_predmeta SET predmet=$id_predmeta, usvojen=0, predlozio=$userid, vrijeme_prijedloga=NOW(), komentar_prijedloga='Kreiran novi predmet', sifra='$sifra', naziv='$naziv', ects=$ects");
					$id_pasosa = db_insert_id();
					zamgerlog2("kreiran novi predmet", $id_predmeta);
				}
				
				// Dodajemo pasoš u plan studija
				if (isset($_REQUEST['izborni'])) {
					// Kreiramo novi izborni slot
					if ($izborni_slot == 0) {
						$q440 = db_query("SELECT MAX(id)+1 FROM plan_izborni_slot");
						$izborni_slot = db_result($q440,0,0);
						if (intval($izborni_slot) == 0) $izborni_slot=1;
						$q450 = db_query("INSERT INTO plan_studija_predmet SET plan_studija=$plan, pasos_predmeta=NULL, plan_izborni_slot=$izborni_slot, semestar=$semestar, obavezan=0, potvrdjen=0");
					}
					$q460 = db_query("INSERT INTO plan_izborni_slot SET id=$izborni_slot, pasos_predmeta=$id_pasosa");
				} else {
					$q470 = db_query("INSERT INTO plan_studija_predmet SET plan_studija=$plan, pasos_predmeta=$id_pasosa, plan_izborni_slot=NULL, semestar=$semestar, obavezan=1, potvrdjen=0");
				}
				zamgerlog2("pasos predmeta ubacen u plan studija", $plan, intval($id_pasosa));
				
				?>
				Predmet je dodan u plan studija.
				<script language="JavaScript">
				location.href='?sta=studentska/plan&studij=<?=$studij?>&plan=<?=$plan?>&predmet=<?=$id_predmeta?>';
				</script>
				<?
				return;
			}
		} else { $naziv = $sifra = $ects = ""; $semestar = 0; }
		
		$semestar_padajuca = "";
		for ($i=1; $i<=$trajanje; $i++) {
			if ($i == $semestar) $dodaj = " SELECTED"; else $dodaj = "";
			$semestar_padajuca .= "<option value=\"$i\"$dodaj>$i</option>\n";
		}

		if (!($greska_naziv == "" && $greska_sifra == "" && $greska_ects == "")) {
			?>
			<p><font color="red">U vašim podacima postoji greška. Molimo popravite ispod.</font></p>
			<?
		}
		
		if (isset($_REQUEST['izborni'])) { $dodaj_izborni = "CHECKED"; } else { $dodaj_izborni = ""; }
		
		?>
		<label for="naziv" class="mylabel">Naziv predmeta:</label> <input type="text" name="naziv" size="20" value="<?=$naziv?>"> <? _print_greska($greska_naziv); ?><br>
		<label for="sifra" class="mylabel">Šifra predmeta:</label> <input type="text" name="sifra" size="20" value="<?=$sifra?>"> <? _print_greska($greska_sifra);  ?><br>
		<label for="ects" class="mylabel">ECTS krediti:</label> <input type="text" name="ects" size="20" value="<?=$ects?>"> <? _print_greska($greska_ects);  ?><br>
		<label for="semestar" class="mylabel">Semestar:</label> <select name="semestar"><?=$semestar_padajuca?></select><br>
		<label for="izborni" class="mylabel">Izborni predmet:</label> <input type="checkbox" name="izborni" <?=$dodaj_izborni?>><br>
		<input type="submit" value=" Šalji ">
		</form>
		<?
	}
	
	// Prikaz podataka o jednom predmetu: verzije pasoša, prava pristupa
	else {
		// Akcije - dodavanje i oduzimanje prava
		$dodaj_prava = $oduzmi_prava = $prihvati_pasos = 0;
		if (isset($_REQUEST['dodaj_prava'])) $dodaj_prava = intval($_REQUEST['dodaj_prava']);
		if (isset($_REQUEST['oduzmi_prava'])) $oduzmi_prava = intval($_REQUEST['oduzmi_prava']);
		if (isset($_REQUEST['prihvati_pasos'])) $prihvati_pasos = intval($_REQUEST['prihvati_pasos']);
		
		if ($dodaj_prava > 0) {
			$q300 = db_query("INSERT INTO plan_studija_permisije SET plan_studija=$plan, predmet=$predmet, osoba=$dodaj_prava");
			zamgerlog2("date permisije za izmjenu pasoša predmeta", $dodaj_prava, $predmet);
			?>
			Date su permisije.
			<script language="JavaScript">
			location.href='?sta=studentska/plan&studij=<?=$studij?>&plan=<?=$plan?>&predmet=<?=$predmet?>';
			</script>
			<?
			return;
		}
		
		if ($oduzmi_prava > 0) {
			$q300 = db_query("DELETE FROM plan_studija_permisije WHERE plan_studija=$plan AND predmet=$predmet AND osoba=$oduzmi_prava");
			zamgerlog2("oduzete permisije za izmjenu pasoša predmeta", $oduzmi_prava, $predmet);
			?>
			Oduzete su permisije.
			<script language="JavaScript">
			location.href='?sta=studentska/plan&studij=<?=$studij?>&plan=<?=$plan?>&predmet=<?=$predmet?>';
			</script>
			<?
			return;
		}
		
		if ($prihvati_pasos > 0) {
			$q400 = db_query("SELECT pp.id FROM pasos_predmeta pp, plan_studija_predmet psp WHERE pp.predmet=$predmet AND pp.id=psp.pasos_predmeta AND psp.plan_studija=$plan");
			if (db_num_rows($q400)>0) {
				$trenutni_pasos = db_result($q400,0,0);
				$q410 = db_query("UPDATE plan_studija_predmet SET pasos_predmeta=$prihvati_pasos WHERE pasos_predmeta=$trenutni_pasos AND plan_studija=$plan");
			} else {
				$q420 = db_query("SELECT pp.id, pis.id FROM pasos_predmeta pp, plan_studija_predmet psp, plan_izborni_slot pis WHERE pp.predmet=$predmet AND pp.id=pis.pasos_predmeta AND pis.id=psp.plan_izborni_slot AND psp.plan_studija=$plan");
				if (db_num_rows($q420) == 0) {
					niceerror("Nije pronađen važeći pasoš u odabranom NPPu.");
					return;
				}
				$trenutni_pasos = db_result($q420,0,0);
				$izborni_slot = db_result($q420,0,1);
				$q430 = db_query("SELECT COUNT(*) FROM plan_studija_predmet WHERE plan_izborni_slot=$izborni_slot AND plan_studija!=$plan");
				if (db_result($q430) == 0) {
					$q440 = db_query("UPDATE plan_izborni_slot SET pasos_predmeta=$prihvati_pasos WHERE pasos_predmeta=$trenutni_pasos AND id=$izborni_slot");
				} else {
					$q450 = db_query("SELECT id FROM plan_izborni_slot ORDER BY id DESC LIMIT 1");
					$novi_izborni_slot = db_result($q450,0,0) + 1;
					$q460 = db_query("INSERT INTO plan_izborni_slot SELECT $novi_izborni_slot, pasos_predmeta FROM plan_izborni_slot WHERE id=$izborni_slot");
					$q470 = db_query("UPDATE plan_izborni_slot SET pasos_predmeta=$prihvati_pasos WHERE pasos_predmeta=$trenutni_pasos AND id=$novi_izborni_slot");
					$q480 = db_query("UPDATE plan_studija_predmet SET plan_izborni_slot=$novi_izborni_slot WHERE plan_izborni_slot=$izborni_slot AND plan_studija=$plan");
				}
				
			}
			?>
			Prihvaćen pasoš predmeta.
			<script language="JavaScript">
			location.href='?sta=studentska/plan&studij=<?=$studij?>&plan=<?=$plan?>&predmet=<?=$predmet?>';
			</script>
			<?
			return;
		}
		
		// Različite verzije pasoša predmeta
		$verzije_pasosa = "";
		$id_aktuelne_verzije = 0;
		$ima_na_drugim_npp = false;
		$aktuelna_ima_na_drugim_npp = false;
		$q200 = db_query("SELECT pp.id, pp.usvojen, o.ime, o.prezime, UNIX_TIMESTAMP(pp.vrijeme_prijedloga), pp.komentar_prijedloga FROM pasos_predmeta pp, osoba o WHERE pp.predmet=$predmet AND pp.predlozio=o.id ORDER BY pp.vrijeme_prijedloga");
		while ($r200 = db_fetch_row($q200)) {
			$pp = $r200[0];
			$predlozio = $r200[3]." ".$r200[2];
			$vrijeme_prijedloga = date("d.m.Y. h:i", $r200[4]);
			$komentar_prijedloga = $r200[5];
			
			$verzije_pasosa .= "<li>".$komentar_prijedloga." (".$predlozio.", ".$vrijeme_prijedloga.")";
			
			// U kojim studijima se nalazi ovaj pasoš
			$q210 = db_query("(SELECT ps.id, s.naziv naz, ag.naziv, ps.godina_vazenja gv
			FROM plan_studija_predmet psp, plan_studija ps, studij s, akademska_godina ag 
			WHERE psp.pasos_predmeta=$pp AND psp.plan_studija=ps.id AND ps.studij=s.id AND ps.godina_vazenja=ag.id)
			UNION
			(SELECT ps.id, s.naziv naz, ag.naziv, ps.godina_vazenja gv
			FROM plan_studija_predmet psp, plan_studija ps, studij s, akademska_godina ag, plan_izborni_slot pis
			WHERE pis.pasos_predmeta=$pp AND pis.id=psp.plan_izborni_slot AND psp.plan_studija=ps.id AND ps.studij=s.id AND ps.godina_vazenja=ag.id)
			ORDER BY gv, naz");
			
			$planovi_studija = ""; $aktuelna = false; $ispis = false;
			while ($r210 = db_fetch_row($q210)) {
				if ($r210[0] == $plan) $aktuelna=true; else $ispis=true;
				$planovi_studija .= "<li>".$r210[1]." (".$r210[2].")</li>\n";
			}
			if ($aktuelna) { 
				$verzije_pasosa .= " - <b><i>aktuelna verzija</i></b> - <a href=\"?sta=studentska/plan&studij=$studij&plan=$plan&predmet=$predmet&pregled_pasosa=$pp\">izmijeni</a>"; 
				$id_aktuelne_verzije = $pp; 
			} else
				$verzije_pasosa .= " - <a href=\"?sta=studentska/plan&studij=$studij&plan=$plan&predmet=$predmet&uporedi_pasos=$pp\">uporedi</a> - <a href=\"?sta=studentska/plan&studij=$studij&plan=$plan&predmet=$predmet&prihvati_pasos=$pp\">prihvati</a>";
			
			if ($ispis) {
				$verzije_pasosa .= "<br>\nOvaj pasoš je važeći za planove i programe:\n<ul>\n".$planovi_studija."</ul>\n";
				$ima_na_drugim_npp = true;
				if ($aktuelna) $aktuelna_ima_na_drugim_npp = true;
			}
			
			$verzije_pasosa .= "</li>\n";
		}
		
		// Osnovni podaci
		$q240 = db_query("SELECT sifra, naziv, ects FROM pasos_predmeta WHERE id=$id_aktuelne_verzije");
		$podaci_o_predmetu = db_fetch_assoc($q240);
		
		// Akcija: Brisanje predmeta iz NPP
		if ($_REQUEST['akcija'] == "brisi_predmet") {
			if ($_REQUEST['potvrda']) {
				// Uklanjamo predmet iz NPPa - obavezan
				$q1000 = db_query("DELETE FROM plan_studija_predmet WHERE plan_studija=$plan AND pasos_predmeta=$id_aktuelne_verzije AND obavezan=1");
				// Uklanjamo predmet iz NPPa - izborni
				$q1010 = db_query("SELECT pis.id FROM plan_izborni_slot pis, plan_studija_predmet psp WHERE psp.plan_studija=$plan and psp.plan_izborni_slot=pis.id AND pis.pasos_predmeta=$id_aktuelne_verzije");
				while ($r1010 = db_fetch_row($q1010)) {
					$q1020 = db_query("DELETE FROM plan_izborni_slot WHERE id=$r1010[0] and pasos_predmeta=$id_aktuelne_verzije");
					// Da li je sada prazan izborni slot?
					$q1022 = db_query("SELECT COUNT(*) FROM plan_izborni_slot WHERE id=$r1010[0]");
					if (db_result($q1022,0,0) == 0)
						$q1000 = db_query("DELETE FROM plan_studija_predmet WHERE plan_studija=$plan AND plan_izborni_slot=$r1010[0] AND obavezan=0");
				}
				zamgerlog2("predmet uklonjen iz npp-a", $predmet, $plan);
					
				// Ako je korisnik tražio, skroz brišemo predmet i sve njegove pasoše
				// Ako su bilo kakvi podaci uneseni (ocjene, ponudekursa i sl) brisanje će pasti zbog referencijalnog integriteta kako i treba
				if ($_REQUEST['skroz_brisi']) {
					$q1030 = db_query("DELETE FROM predmet WHERE id=$predmet");
					$q1040 = db_query("DELETE FROM pasos_predmeta WHERE predmet=$predmet");
					zamgerlog2("predmet obrisan", $predmet);
				}

				// Ako je korisnik tražio, brišemo aktuelnu verziju pasoša
				if ($_REQUEST['brisi_pasos']) {
					$q1040 = db_query("DELETE FROM pasos_predmeta WHERE id=$id_aktuelne_verzije");
					zamgerlog2("obrisana aktuelna verzija pasoša predmeta", $predmet);
				}
				?>
				Predmet izbačen iz plana studija.
				<script language="JavaScript">
				location.href='?sta=studentska/plan&studij=<?=$studij?>&plan=<?=$plan?>';
				</script>
				<?
				return;
			}
			
			?><h2>Uklanjanje predmeta iz programa: <?=$podaci_o_predmetu['naziv']?></h2>
			<?
			
			if ($ima_na_drugim_studijima == false) {
				print genform("POST");
				?>
				<input type="hidden" name="potvrda" value="da">
				<p>Ovaj predmet se javlja isključivo u planu i programu koji editujete. Da li ga želite potpuno brisati sa spiska predmeta?</p>
				<p><input type="submit" value=" Samo ukloni predmet iz NPPa "></p>
				<p><input type="submit" name="skroz_brisi" value=" Briši predmet - pažnja! "></p>
				
				<p><a href="?sta=studentska/plan&amp;studij=<?=$studij?>&amp;plan=<?=$plan?>&amp;predmet=<?=$predmet?>">Povratak nazad</a></p>
				</form>
				<?
				return;
			}
			if ($aktuelna_ima_na_drugim_npp == false) {
				print genform("POST");
				?>
				<input type="hidden" name="potvrda" value="da">
				<p>Aktuelna verzija pasoša predmeta se javlja isključivo u planu i programu koji editujete. Da li želite obrisati i odgovarajući pasoš?</p>
				<p><input type="submit" name="brisi_pasos" value=" Briši pasoš predmeta vezan uz tekući plan i program "><br>
				<input type="submit" value=" Samo ukloni predmet iz NPPa "><br>
				<p><a href="?sta=studentska/plan&amp;studij=<?=$studij?>&amp;plan=<?=$plan?>&amp;predmet=<?=$predmet?>">Povratak nazad</a></p>
				</form>
				<?
				return;
			}
		}
		
		// PRIKAZ INFORMACIJA O PREDMETU
		
		// Ko ima prava?
		$prava_pristupa = "";
		$vec_imaju_pravo = array();
		$q220 = db_query("SELECT o.id, o.prezime, o.ime FROM plan_studija_permisije psp, osoba o WHERE psp.plan_studija=$plan AND psp.predmet=$predmet AND psp.osoba=o.id ORDER BY o.prezime, o.ime");
		if (db_num_rows($q220) == 0)
			$prava_pristupa = "<li>Niko</li>";
		while ($r220 = db_fetch_row($q220)) {
			$prava_pristupa .= "<li>$r220[1] $r220[2] - <a href=\"?sta=studentska/plan&studij=$studij&plan=$plan&predmet=$predmet&oduzmi_prava=$r220[0]\">Oduzmi prava</a>";
			array_push($vec_imaju_pravo, $r220[0]);
		}
		
		// Ko sve može dobiti prava?
		$dodaj_prava = "";
		$q360 = db_query("select o.id, o.prezime, o.ime from osoba as o, privilegije as p where p.osoba=o.id and p.privilegija='nastavnik' order by o.prezime, o.ime");
		while ($r360 = db_fetch_row($q360)) {
			if (in_array($r360[0], $vec_imaju_pravo)) continue;
			$dodaj_prava .= "<option value=\"$r360[0]\">$r360[1] $r360[2]</option>\n";
		}
		
		?>
		<h2>Predmet: <b><?=$podaci_o_predmetu['naziv']?></b></h2>
		<h2>Šifra: <b><?=$podaci_o_predmetu['sifra']?></b></h2>
		<p><a href="?sta=studentska/plan&amp;studij=<?=$studij?>&amp;plan=<?=$plan?>&amp;predmet=<?=$predmet?>&amp;pregled_pasosa=<?=$id_aktuelne_verzije?>"><img src="static/images/16x16/view.png" width="16" height="16" border="0" align="center"> Pasoš predmeta</a></p>
		<p>Verzije pasoša predmeta:</p>
		<ul><?=$verzije_pasosa?></ul>
		
		<hr>
		
		<p>Pravo predlaganja pasoša predmeta imaju sljedeći korisnici:</p>
		<ul><?=$prava_pristupa?></ul>
		<?=genform("POST")?>
		Dodaj:
		<select name="dodaj_prava"><?=$dodaj_prava?></select>
		<input type="submit" value=" Dodaj "></form>
		
		<hr>
		
		<p><a href="<?=genuri()?>&amp;akcija=brisi_predmet"><img src="static/images/16x16/not_ok.png" width="16" height="16" border="0" align="center"> Ukloni predmet iz programa</a></p>
		<p><a href="?sta=studentska/plan&amp;studij=<?=$studij?>&amp;plan=<?=$plan?>"><img src="static/images/16x16/ok.png" width="16" height="16" border="0" align="center"> Dodaj novi predmet</a></p>
		<?
	}
	
	?>
	</td></tr></table>
	<?

}

function _print_greska($greska) {
	if ($greska != "") {
		?><img src="static/images/16x16/not_ok.png" width="16" height="16"> <font color="red"><?=$greska?></font><?
	}
}


function _daj_textbox($tekst, $id, $vrijednost, $greska, $size=20) {
	print "<label for=\"$id\" class=\"mylabel\">$tekst:</label> 
	<input type=\"text\" name=\"$id\" size=\"$size\" value=\"".$vrijednost[$id]."\">";
	_print_greska($greska[$id]);
	print "<br>\n";
}

function _daj_textarea($tekst, $id, $vrijednost, $greska) {
	?>
	<p style="vertical-align: middle;">
		<label for="<?=$id?>" class="mylabel"  style="vertical-align: middle;"><?=$tekst?>:</label> 
		<textarea rows="7" cols="100" name="<?=$id?>"  style="vertical-align: middle;"><?=$vrijednost[$id]?></textarea>
		<? _print_greska($greska[$id]); ?>
	</p>
	<?
}

function _floatval($tekst) {
	$tekst = str_replace(",", ".", $tekst);
	return floatval($tekst);
}

?>
