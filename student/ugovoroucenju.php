<?

// STUDENT/UGOVOROUCENJU - generisanje ugovora o ucenju

// TODO: Ovdje se neće moći koristiti tabela plan_studija jer treba omogućiti maskiranje nekih predmeta, kao i odstupanja od plana (grrr)



function student_ugovoroucenju() {

	global $userid;
	
	
	// Studiji za koje je trenutno isključeno popunjavanje uou
	$zabrana_studij = array();
	// Studiji/godine za koje je trenutno isključeno popunjavanje uou (navesti string oblika "$studij-$godina")
	$zabrana_studij_godina = array();
	
	// Studiji i studiji/godine koji ne mogu birati predmete sa drugog odsjeka
	$nema_drugi_odsjek_studij = array();
	$nema_drugi_odsjek_studij_godina = array();
	
	
	require_once("lib/student_studij.php");
	require_once("lib/plan_studija.php");

	// Naslov
	?>
	<h3>Ugovor o učenju</h3>

	<?
	
	// Za koju godinu se prijavljuje?
	// Promjenljiva $akademska_godina sadrži id godine u koju se student želi upisati
	$q1 = db_query("SELECT id, naziv FROM akademska_godina WHERE aktuelna=1");
	db_fetch2($q1, $aktuelna_godina, $aktuelna_godina_naziv);
	
	$q2 = db_query("SELECT id, naziv FROM akademska_godina WHERE id>$aktuelna_godina ORDER BY id DESC LIMIT 1");
	if (!db_fetch2($q2, $akademska_godina, $akademska_godina_naziv)) {
		// Nije kreirana godina poslije aktuelne
		// Pretpostavljamo da se student upisuje u aktuelnu godinu
		$akademska_godina = $aktuelna_godina;
		$akademska_godina_naziv = $aktuelna_godina_naziv;
	}
	
	?>
	<p>Za akademsku <?=$akademska_godina_naziv?> godinu.</p>
	<?


	// Ulazni podaci

	$studij = intval($_REQUEST['studij']);
	$godina_studija = intval($_REQUEST['godina']);

	// Provjera ispravnosti podataka
	if ($studij != 0) {
		$trajanje_studija = db_get("SELECT ts.trajanje FROM studij s, tipstudija ts WHERE s.id=$studij AND s.tipstudija=ts.id");
		if ($trajanje_studija === false) {
			niceerror("Neispravan studij");
			$studij = 0;
			unset($_POST['akcija']);
		}
		else if ($godina_studija < 1 || $godina_studija > $trajanje_studija/2) {
			$godina_studija = 1;
		}
	} else {
		unset($_POST['akcija']);
	}


	// Odabir plana studija
	$plan_studija = $novi_studij = 0;
	if ($studij > 0) {
		$q5a = db_query("SELECT studij, plan_studija FROM student_studij WHERE student=$userid AND akademska_godina<=$akademska_godina ORDER BY akademska_godina DESC LIMIT 1");
		if (!db_fetch2($q5a, $stari_studij, $plan_studija) || $studij != $stari_studij) {
			// Student nije prethodno studirao na istom studiju ili plan studija nije bio definisan
 			// Uzimamo najnoviji plan za odabrani studij
			$plan_studija = db_get("SELECT id FROM plan_studija WHERE studij=$studij ORDER BY godina_vazenja DESC LIMIT 1");
			if (!$plan_studija) { 
				niceerror("Nije definisan plan i program za studij $studij");
				return;
			}
		}
	}

	
	// Akcija - kreiranje ugovora

	if ($_POST['akcija']=="kreiraj_ugovor") {
		for ($semestar = $godina_studija * 2 - 1; $semestar <= $godina_studija * 2; $semestar++) {
			$izabrani_predmeti = array();
			$plan_studija_sadrzaj = predmeti_na_planu($plan_studija, $semestar);
			$semestar_ects = 0;
			
			foreach($plan_studija_sadrzaj as $slog) {
				if ($slog['obavezan'] == 1) $semestar_ects += $slog['predmet']['ects'];
				else if ($slog['ponavljanja'] == 1) {
					$pis = $slog['plan_izborni_slot'];
					$izabran_text = $_REQUEST["is$pis"];
					
					if ($izabran_text == "odsjek$semestar") {
						$izabran_predmet = intval($_REQUEST["odsjek-$pis"]);
						// Izborni predmet sa drugog odsjeka
						$q120 = db_query("select pp.ects, pp.naziv from pasos_predmeta pp where pp.predmet=$izabran_predmet order by pp.id desc limit 1");
						if (!db_fetch2($q120, $ects, $predmet_naziv)) {
							niceerror("Ilegalan izborni predmet A $izabran_predmet");
							return;
						}
					} else {
						$izabran_predmet = intval($izabran_text);
						$predmet_naziv = "";
						foreach($slog['predmet'] as $slog_predmet) {
							if ($slog_predmet['id'] == $izabran_predmet) {
								$predmet_naziv = $slog_predmet['naziv'];
								$ects = $slog_predmet['ects'];
								break;
							}
						}
						if ($predmet_naziv === "") {
							niceerror("Ilegalan izborni predmet A1 $izabran_predmet $pis $semestar");
							return;
						}
					}
					
					if (provjeri_kapacitet($userid, $izabran_predmet, $akademska_godina, $studij) == 0) {
						niceerror("Predmet $predmet_naziv se ne može izabrati jer su dostupni kapaciteti za taj predmet popunjeni");
						zamgerlog2("popunjen kapacitet za predmet", $izabran_predmet);
						return;
					}

					$semestar_ects += $ects;
					$izabrani_predmeti[] = $izabran_predmet;
					
				} else {
					$pis = $slog['plan_izborni_slot'];
					$izabrani_predmeti_slot = array();
					
					foreach ($_REQUEST as $ime => $vrijednost) {
						$komad = "iz$pis-";
						if (substr($ime,0,strlen($komad)) != $komad) continue;
						
						// izborni predmet sa drugog odsjeka
						if (substr($ime, strlen($komad)) == "odsjek") {
							$izabran_predmet = intval($_REQUEST["odsjek-$pis"]);
							// Izborni predmet sa drugog odsjeka
							$q120 = db_query("select pp.ects, pp.naziv from pasos_predmeta pp where pp.predmet=$izabran_predmet order by pp.id desc limit 1");
							if (!db_fetch2($q120, $ects, $predmet_naziv)) {
								niceerror("Ilegalan izborni predmet B $izabran_predmet");
								return;
							}
						}
						else {
							$izabran_predmet = intval($vrijednost);
							$predmet_naziv = "";
							foreach($slog['predmet'] as $slog_predmet) {
								if ($slog_predmet['id'] == $izabran_predmet) {
									$predmet_naziv = $slog_predmet['naziv'];
									$ects = $slog_predmet['ects'];
									break;
								}
							}
							if ($predmet_naziv === "") {
								niceerror("Ilegalan izborni predmet B1 $izabran_predmet");
								return;
							}
						}
						
						if (provjeri_kapacitet($userid, $izabran_predmet, $akademska_godina, $studij) == 0) {
							niceerror("Predmet $predmet_naziv se ne može izabrati jer su dostupni kapaciteti za taj predmet popunjeni");
							zamgerlog2("popunjen kapacitet za predmet", $izabran_predmet);
							return;
						}
						
						$semestar_ects += $ects;
						$izabrani_predmeti_slot[] = $izabran_predmet;
					}
					
					if (count($izabrani_predmeti_slot) < $slog['ponavljanja']) {
						niceerror("Nije izabrano dovoljno predmeta u izbornom slotu $pis");
						return;
					}
					$izabrani_predmeti = array_merge($izabrani_predmeti, $izabrani_predmeti_slot);
				}
			}
			
			if ($semestar_ects < 30) {
				niceerror("Niste izabrali dovoljno izbornih predmeta u $semestar. semestru (ukupno $semestar_ects ECTS kredita, a potrebno je 30)");
				return;
			}
			
			if ($semestar % 2 == 1) 
				$izabrani_predmeti_neparni = $izabrani_predmeti;
			else
				$izabrani_predmeti_parni = $izabrani_predmeti;
		}

		// Sve ok, brišemo stari ugovor iz baze
		$q140 = db_query("select id from ugovoroucenju where student=$userid and akademska_godina=$akademska_godina");
		while ($r140 = db_fetch_row($q140)) {
			$q145 = db_query("delete from ugovoroucenju where id=$r140[0]");
			$q145 = db_query("delete from ugovoroucenju_izborni where ugovoroucenju=$r140[0]");
		}
		
		// Generišemo novi kod
		do {
			$kod = "";
			for ($i=0; $i<10; $i++)
				$kod .= chr(ord("0") + rand(0,9));
			$ima_li = db_get("SELECT COUNT(*) FROM ugovoroucenju WHERE kod='$kod'");
		} while($ima_li > 0);

		// Ubacujemo novi ugovor u bazu
		$q150 = db_query("insert into ugovoroucenju set student=$userid, akademska_godina=$akademska_godina, studij=$studij, semestar=" . ($godina_studija*2-1) . ", kod='$kod'");
		// Uzimamo ID ugovora
		$id1 = db_get("select id from ugovoroucenju where student=$userid and akademska_godina=$akademska_godina and studij=$studij and semestar=".($godina_studija*2-1));
		foreach ($izabrani_predmeti_neparni as $predmet) {
			$q170 = db_query("insert into ugovoroucenju_izborni set ugovoroucenju=$id1, predmet=$predmet");
		}

		// Isto za parni semestar
		$q180 = db_query("insert into ugovoroucenju set student=$userid, akademska_godina=$akademska_godina, studij=$studij, semestar=" . ($godina_studija*2) . ", kod='$kod'");
		$id2 = db_get("select id from ugovoroucenju where student=$userid and akademska_godina=$akademska_godina and studij=$studij and semestar=".($godina_studija*2) );
		foreach ($izabrani_predmeti_parni as $predmet) {
			$q200 = db_query("insert into ugovoroucenju_izborni set ugovoroucenju=$id2, predmet=$predmet");
		}

		zamgerlog("student u$userid kreirao ugovor o ucenju (ID: $id1 i $id2)",2); // 2 - edit
		zamgerlog2("kreirao ugovor o ucenju", intval($id1), intval($id2));
		nicemessage("Kreirali ste Ugovor o učenju!");
		?>
		<p><a href="?sta=student/ugovoroucenjupdf">Kliknite ovdje da biste ga isprintali.</a></p>
		<?
		return;
	}


	// Da li student već ima kreiran ugovor o učenju za sljedeću godinu?
	$q9 = db_query("select count(*) from ugovoroucenju where student=$userid and akademska_godina=$akademska_godina");
	if (db_result($q9,0,0)>0) {
		?>
		<p>Već imate kreiran Ugovor o učenju.<br />Možete ga preuzeti <a href="?sta=student/ugovoroucenjupdf">klikom ovdje</a>, ili možete kreirati novi ugovor ispod (pri čemu će stari biti pobrisan).</p>
		<p>&nbsp;</p>
		<?
	}


	// Studij nije odabran, biramo onaj koji student trenutno sluša
	if ($studij==0) {
		$q10 = db_query("SELECT ss.studij, ss.semestar, ts.trajanje, s.institucija, s.tipstudija, ss.plan_studija, ss.akademska_godina 
			FROM student_studij ss, studij s, tipstudija ts 
			WHERE ss.student=$userid AND ss.studij=s.id AND s.tipstudija=ts.id
			ORDER BY ss.akademska_godina DESC, ss.semestar DESC LIMIT 1");
		if (db_fetch7($q10, $studij, $trenutni_semestar, $trajanje, $institucija, $tipstudija, $plan_studija, $trenutni_studij_ag)) {
			//print "studij $studij trenutni_semestar $trenutni_semestar plan_studija $plan_studija<br>";
			
			// Određujemo godinu studija u koju se student vjerovatno želi upisati
			if ($trenutni_studij_ag == $akademska_godina)
				$godina_studija = intval(($trenutni_semestar+1)/2);
			else
				$godina_studija = intval(($trenutni_semestar+1)/2 + 1);
			
			if ($trenutni_semestar >= $trajanje) {
				$studij = db_get("select id from studij where moguc_upis=1 and institucija=$institucija and tipstudija>$tipstudija"); // FIXME pretpostavka je da su tipovi studija poredani po ciklusima
				if ($studij) {
					$godina_studija = 1;
					$plan_studija = 0; // Uzećemo najnoviji plan za odabrani studij

				} else {
					// Nema gdje dalje... postavljamo sve na nulu
					$studij = 0;
					$godina_studija = 1;
				}
			}

			if ($plan_studija == 0) {
				// Određujemo najnoviji plan za novi studij
				$plan_studija = db_get("select id from plan_studija where studij=$studij order by godina_vazenja desc limit 1");
				if (!$plan_studija) { 
					niceerror("Nepostojeći studij");
					return;
				}
			}

		} else {
			niceerror("Niste nikada bili naš student!");
			// Radi testiranja dozvolićemo nestudentima da uđu
			$godina_studija = 1;
		}
	}
	
	// Godina važenja za plan studija
	$godina_vazenja = db_get("SELECT godina_vazenja FROM plan_studija WHERE id=$plan_studija");
	
	
		
	// =======================================
	//  Prikaz formulara za kreiranje ugovora
	// =======================================
	
	// Od ovog trenutka smatra se da promjenljive $studij, $godina_studija, $plan_studija imaju fiksne vrijednosti
	
	// Zabrane
	$studij_godina = $studij . "-" . $godina_studija;
	if (in_array($studij, $zabrana_studij) || in_array($studij_godina, $zabrana_studij_godina)) {
		nicemessage("Popunjavanje Ugovora o učenju je trenutno onemogućeno za vaš studij / godinu studija.");
		print "<p>Kontaktirajte službe Fakulteta za više informacija.</p>";
		return;
	}
	
	

	?>
	<SCRIPT language="JavaScript">
	// Refresh stranice sa novim izbornim predmetima
	function refresh() {
		var studij = parseInt(document.getElementById('studij').value);
		var godina = parseInt(document.getElementById('godina').value);
		location.replace("index.php?sta=student/ugovoroucenju&studij="+studij+"&godina="+godina);
	}

	// Da bismo spriječili da pritisak na Enter submituje formu
	function noenter() {
		if (window.event && window.event.keyCode == 13) {
			refresh();
			return false;
		}
	}

	// Funckija koja ne dozvoljava da se selektuje različit broj polja od navedenog
	var globalna;
	function jedanod(slot, clicked) {
		var template = "iz"+slot;
		var found=false;
		for (var i=0; i<document.mojaforma.length; i++) {
			var el = document.mojaforma.elements[i];

			if (el.type != "checkbox") continue;
			if (el==clicked) continue;
			if (template == el.name.substr(0,template.length)) {
				if (clicked.checked==true && el.checked==true) {
					el.checked=false; found=true; break; 
				}
				if (clicked.checked==false && el.checked==false) {
					el.checked=true; found=true; break;
				}
			}
		}
		if (!found) {
			globalna=clicked;
			setTimeout("revertuj()", 100);
		}
		return found;
	}
	function drugiodsjek(slot, semestar, enable) {
		var template = "iz"+slot;
		for (var i=0; i<document.mojaforma.length; i++) {
			var el = document.mojaforma.elements[i];
			if (el.type != "checkbox" && el.type != "radio") continue;
			if (el.name.substr(0,template.length) == template) continue;
			if (el.name.substr(0,template.length) == "is"+slot) continue;
			if (el.value == "odsjek"+semestar) el.disabled=enable;
		}
	}
	function revertuj() {
			if (globalna.checked) globalna.checked=false;
			else globalna.checked=true;
	}
	</SCRIPT>

	<form action="index.php" method="POST" name="mojaforma">
	<input type="hidden" name="sta" value="student/ugovoroucenju">
	<input type="hidden" name="akcija" value="kreiraj_ugovor">

	<p>Bjanko Ugovor o učenju: <a href="static/images/content/150dpi/domestic-contract-0.png">stranica 1</a>, <a href="static/images/content/150dpi/domestic-contract-1.png">stranica 2</a>!</p>

	<p>Studij: <select name="studij" id="studij" onchange="javascript:refresh()"><option></option>
	<?

	// Spisak studija
	$q30 = db_query("select id, naziv, institucija from studij where moguc_upis=1 order by tipstudija, naziv");
	while (db_fetch3($q30, $qstudij, $qnaziv_studija, $qinstitucija)) {
		print "<option value=\"$qstudij\"";
		if ($qstudij==$studij) {
			print " selected";
			$studij_institucija = $qinstitucija;
		}
		print ">$qnaziv_studija</option>\n";
	}


	
	?>
	</select></p>
	<p>Godina studija: <input type="text" name="godina" id="godina" value="<?=$godina_studija?>" onchange="javascript:refresh()" onkeypress="javascript:return noenter()"></p>
	<p>&nbsp;</p>

	<p><b>Izborni predmeti:</b></p><?
	
	// Preuzimamo plan studija
	$plan_studija_sadrzaj = predmeti_na_planu($plan_studija);
	
	for ($semestar = $godina_studija*2-1; $semestar <= $godina_studija*2; $semestar++) {
		?>
		<p><?=$semestar?>. semestar:</p>
		<?

		// Generišemo spisak predmeta sa drugog odsjeka za dati semestar
		$izborni_drugi_odsjek = array();
		if (!in_array($studij, $nema_drugi_odsjek_studij) && !in_array($studij_godina, $nema_drugi_odsjek_studij_godina)) {
			// Spisak planova studija sa drugih odsjeka
			$q100 = db_query("SELECT ps.id, s.kratkinaziv FROM plan_studija ps, studij as s WHERE ps.godina_vazenja=$godina_vazenja AND ps.studij!=$studij AND ps.studij=s.id");
			while(db_fetch2($q100, $m_plan, $kratki_naziv_studija)) {
				// Obavezni predmeti
				$obavezni_predmeti = db_query_table("SELECT pp.predmet id, pp.naziv FROM pasos_predmeta pp, plan_studija_predmet psp WHERE psp.plan_studija=$m_plan AND psp.pasos_predmeta=pp.id AND psp.semestar=$semestar");
				$izborni_predmeti = db_query_table("SELECT pp.predmet id, pp.naziv FROM pasos_predmeta pp, plan_studija_predmet psp, plan_izborni_slot pis WHERE psp.plan_studija=$m_plan AND psp.plan_izborni_slot=pis.id AND pis.pasos_predmeta=pp.id AND psp.semestar=$semestar");
				foreach(array_merge($obavezni_predmeti, $izborni_predmeti) as $predmet) {
					// Kapacitet
					$q110 = db_query("SELECT kapacitet, kapacitet_izborni, kapacitet_drugi_odsjek, drugi_odsjek_zabrane FROM ugovoroucenju_kapacitet WHERE predmet=" . $predmet['id'] . " AND akademska_godina=$akademska_godina");
					if (db_fetch4($q110, $kapacitet, $kapacitet_izborni, $kapacitet_drugi_odsjek, $drugi_odsjek_zabrane)) {
						// Ako je student već položio predmet ipak treba imati mogućnost da ga izabere
						$polozio = db_get("SELECT COUNT(*) FROM konacna_ocjena WHERE student=$userid AND predmet=" . $predmet['id'] . " AND ocjena>5");
						if (!$polozio) {
							if ($kapacitet == 0 || $kapacitet_izborni == 0 || $kapacitet_drugi_odsjek == 0) continue;
							$zabrane = explode(",", $drugi_odsjek_zabrane);
							if (in_array($studij, $zabrane)) continue;
						}
					}
					
					// Da li postoji u matičnom planu
					$postoji_maticni = false;
					foreach($plan_studija_sadrzaj as $slog) {
						if ($slog['obavezan'] == 1 && $slog['predmet']['id'] == $predmet['id']) {
							$postoji_maticni = true;
							break;
						}
						if ($slog['obavezan'] == 0) {
							foreach($slog['predmet'] as $slog_predmet) {
								if ($slog_predmet['id'] == $predmet['id']) {
									$postoji_maticni = true;
									break;
								}
							}
							if ($postoji_maticni) break;
						}
					}
					if ($postoji_maticni) continue;
					
					// Nećemo dvaput isti
					if (array_key_exists($predmet['id'], $izborni_drugi_odsjek)) continue;
					
					$izborni_drugi_odsjek[$predmet['id']] = $predmet['naziv'] . " ($kratki_naziv_studija)";
				}
			}
		}

		// Spisak izbornih predmeta za dati semestar
		foreach($plan_studija_sadrzaj as $slog) {
			if ($slog['obavezan'] == 1) continue;
			if ($slog['semestar'] != $semestar) continue;
			
			// Da li je student već položio nešto iz slota?
			$polozeni = array();
			foreach($slog['predmet'] as $slog_predmet) {
				$polozio = db_get("SELECT COUNT(*) FROM konacna_ocjena WHERE student=$userid AND predmet=" . $slog_predmet['id'] . " AND ocjena>5");
				if ($polozio) $polozeni[] = $slog_predmet['id'];
			}
			
			// Ako je već ispolagao dovoljno predmeta
			$disabled = "";
			if (count($polozeni) == $slog['ponavljanja']) $disabled = " DISABLED";
			$odaberi_jos = $slog['ponavljanja'] - count($polozeni);
			
			$pis = $slog['plan_izborni_slot'];
			if ($slog['ponavljanja'] == 1) {
				print "<p>\n";
				foreach($slog['predmet'] as $slog_predmet) {
					// Kapacitet
					$q120 = db_query("SELECT kapacitet, kapacitet_izborni FROM ugovoroucenju_kapacitet WHERE predmet=" . $slog_predmet['id'] . " AND akademska_godina=$akademska_godina");
					if (db_fetch2($q120, $kapacitet, $kapacitet_izborni)) {
						if ($kapacitet == 0 || $kapacitet_izborni == 0) continue;
					}
					
					?><input type="radio" name="is<?=$pis?>" value="<?=$slog_predmet['id']?>" onchange="drugiodsjek('<?=$pis?>',<?=$semestar?>,false);" <?
					if (in_array($slog_predmet['id'], $polozeni)) {
						print " CHECKED"; 
					} else if ($odaberi_jos > 0) { 
						print " CHECKED"; $odaberi_jos--;
					}
					
					print $disabled;
					?>><?=$slog_predmet['naziv']?></input><br>
					<?
				}
				if ($disabled) {
					// Disabled radio button neće biti poslan sa formom
					?>
					<input type="hidden" name="is<?=$pis?>" value="<?=$polozeni[0]?>">
					<?
				}
				
				?>
				<input type="radio" name="is<?=$pis?>" value="odsjek<?=$semestar?>" onchange="drugiodsjek('<?=$pis?>',<?=$semestar?>,true);" <?=$disabled?>>Predmet sa drugog odsjeka</input><br>
				<select name="odsjek-<?=$pis?>">
				<?
				foreach($izborni_drugi_odsjek as $predmet_id => $predmet_naziv)
					print "<option value=\"$predmet_id\">$predmet_naziv</option>\n";
				print "</select>\n";
				print "</p>\n";
			} else {
				
				print "<p>\n";
				foreach($slog['predmet'] as $slog_predmet) {
					// Kapacitet
					$q120 = db_query("SELECT kapacitet, kapacitet_izborni FROM ugovoroucenju_kapacitet WHERE predmet=" . $slog_predmet['id'] . " AND akademska_godina=$akademska_godina");
					if (db_fetch2($q120, $kapacitet, $kapacitet_izborni)) {
						if ($kapacitet == 0 || $kapacitet_izborni == 0) continue;
					}
					
					?><input type="checkbox" name="iz<?=$pis?>-<?=$slog_predmet['id']?>" value="<?=$slog_predmet['id']?>" onchange="javascript:jedanod('<?=$pis?>',this)" <?
					if (in_array($slog_predmet['id'], $polozeni)) {
						print " CHECKED"; 
					} else if ($odaberi_jos > 0) { 
						print " CHECKED"; $odaberi_jos--;
					} 
					print $disabled;
					?>><?=$slog_predmet['naziv']?></input><br>
					<?
				}
				if ($disabled) {
					// Također ni disabled checkbox neće biti poslan sa formom
					foreach($polozeni as $predmet) {
						?>
						<input type="hidden" name="iz<?=$pis?>-<?=$predmet?>" value="<?=$predmet?>">
						<?
					}
				}
				
				?>
				<input type="checkbox" name="iz<?=$pis?>-odsjek" value="odsjek<?=$semestar?>" onchange="jedanod('<?=$pis?>', this); ('<?=$pis?>',<?=$semestar?>,this.checked);" <?=$disabled?>>Predmet sa drugog odsjeka</input><br>
				<select name="odsjek-<?=$pis?>">
				<?
				foreach($izborni_drugi_odsjek as $predmet_id => $predmet_naziv)
					print "<option value=\"$predmet_id\">$predmet_naziv</option>\n";
				print "</select>\n";
				print "</p>\n";
			}
		}
	}

	?>
	<p>&nbsp;</p>
	<input type="button" value="Osvježi spisak predmeta" onclick="javascript:refresh()"><br /><br />

	<input type="submit" value="Kreiraj ugovor"></form>


	<p><b>Napomene:</b><br>
	* Ukoliko obnavljate godinu, trebate ponovo izabrati one predmete koje ste već položili.<br>
	* Možete izabrati najviše jedan predmet s drugog odsjeka po semestru, a u zbiru trebate imati najmanje 30 ECTS kredita po semestru odnosno 60 ECTS kredita po godini.<br>
	* Ako želite slušati izborni predmet sa drugog fakulteta, sada ovdje izaberite neki predmet sa našeg fakulteta a ujedno pokrenite proceduru (koja podrazumijeva odobrenje oba fakulteta).</p>
	<?

}

?>
