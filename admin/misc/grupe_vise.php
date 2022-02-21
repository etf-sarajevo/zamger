<?php


//----------------------------------------
// Masovno kreiranje grupa
//----------------------------------------

// - bitno drugačiji kod prilagođen za više godine studija (prva godina ima puno specifičnosti)

function admin_misc_grupe_vise() {

	if ($_POST['akcija']=="grupe-ri2") {
		$f = intval($_POST['fakatradi']);
		
		$studenti=$studenti_id=array();
		
		$broj_grupa=intval($_REQUEST['brojgrupa']);
		$ag = intval($_REQUEST['_lv_column_akademska_godina']);
		$godina_studija = intval($_REQUEST['godina_studija']);
		$ciklus = intval($_REQUEST['ciklus']);
		
		$dodaj = $dodaj_pk = "";
		foreach($_REQUEST['studij'] as $studij) {
			if ($dodaj != "") { $dodaj .= "or "; $dodaj_pk .= "or "; }
			$dodaj .= "ss.studij=$studij ";
			$dodaj_pk .= "pk.studij=$studij ";
		}
		if ($dodaj != "") $dodaj = "AND ($dodaj)";
		if ($dodaj_pk != "") $dodaj_pk = "AND ($dodaj_pk)";
		
		$semestar = $godina_studija*2 - 1;
		if (isset($_REQUEST['parni']) && $_REQUEST['parni'] == 1) $semestar = $godina_studija*2;
		
		// Spisak studenata na godini
		$q10 = db_query("select o.id, o.ime, o.prezime, o.brindexa from osoba as o, student_studij as ss where ss.student=o.id and ss.akademska_godina=$ag and ss.semestar=$semestar and ss.ponovac=0 $dodaj order by o.prezime, o.ime");
		$broj_studenata=db_num_rows($q10);
		$broj_studenata_po_grupi = intval($broj_studenata/$broj_grupa);
		$broj_ekstra_grupa = $broj_studenata%$broj_grupa;
		
		while ($r10 = db_fetch_row($q10)) {
			$studenti[$r10[0]]="$r10[2] $r10[1] ($r10[3])";
		}
		uasort($studenti,"bssort");
		
		
		if ($f==0) {
			print "Ukupno studenata: $broj_studenata<br/>\nBroj grupa: $broj_grupa<br/>\nStudenata po grupi: $broj_studenata_po_grupi (+$broj_ekstra_grupa)<br/><br/>\n";
		}
		
		
		// Spisak obaveznih i izbornih predmeta i studenata na njima
		$izborni_table = db_query_table("SELECT pk.id, pk.predmet, p.kratki_naziv FROM ponudakursa pk, predmet p WHERE pk.akademska_godina=$ag $dodaj_pk and pk.semestar=$semestar AND pk.obavezan=0 AND pk.predmet=p.id ORDER BY p.kratki_naziv");
		$izborni = $studenti_izborni = array();
		foreach($izborni_table as $predmet) {
			$izborni[$predmet['predmet']] = $predmet['kratki_naziv'];
			$studenti_izborni[$predmet['predmet']] = db_query_varray("SELECT student FROM student_predmet WHERE predmet=".$predmet['id']);
		}
		
		$obavezni_table = db_query_table("SELECT pk.id, pk.predmet, p.kratki_naziv FROM ponudakursa pk, predmet p WHERE pk.akademska_godina=$ag $dodaj_pk and pk.semestar=$semestar AND pk.obavezan=1 AND pk.predmet=p.id ORDER BY p.kratki_naziv");
		$obavezni = $studenti_obavezni = array();
		foreach($obavezni_table as $predmet) {
			$obavezni[$predmet['predmet']] = $predmet['kratki_naziv'];
			$studenti_obavezni[$predmet['predmet']] = db_query_varray("SELECT student FROM student_predmet WHERE predmet=".$predmet['id']);
		}
		
		
		// Spisak studenata koji nisu ni na jednom predmetu i spisak kombinacija za formiranje grupa
		$kombinacije = $student_predmeti = $student_izborni = array();
		$velicina_kombinacija = 0;
		foreach($studenti as $student => $ime) {
			$pronadjen = $imena = array();
			foreach($izborni as $predmet => $kratkinaziv) {
				if (in_array($student, $studenti_izborni[$predmet])) {
					$pronadjen[] = $predmet;
					$imena[] = $kratkinaziv;
				}
			}
			
			if ($student == 3955) { $pronadjen=array(2231,116); $imena=array("PJIP", "RMIS"); }
			$student_izborni[$student] = $pronadjen;
			
			if (!empty($pronadjen) && count($pronadjen) >= $velicina_kombinacija) {
				if (count($pronadjen) > $velicina_kombinacija) {
					$velicina_kombinacija = count($pronadjen);
					$kombinacije = array();
				}
				$kombinacija = join("-", $pronadjen);
				$ime_kombinacije = join("-", $imena);
				//print "Student $ime ($student) kombinacija $ime_kombinacije<br>";
				if (!array_key_exists($kombinacija, $kombinacije))
					$kombinacije[$kombinacija] = $ime_kombinacije;
			}
			
			foreach($obavezni as $predmet => $kratkinaziv) {
				if (in_array($student, $studenti_obavezni[$predmet])) {
					$pronadjen[] = $predmet;
				}
			}
			
			$student_predmeti[$student] = $pronadjen;
		}
		arsort($kombinacije); // zbog SP/NA - staviti asort
		
		
		// Studenti u datoj kombinaciji
		$kombinacija_studenti = array();
		$total_studenata_kombinacije = 0;
		foreach($kombinacije as $id_kombinacije => $ime_kombinacije) {
			$kpredmeti = explode("-", $id_kombinacije);
			$kombinacija_studenti[$id_kombinacije] = array();
			foreach($studenti as $student => $ime) {
				$svi = true;
				foreach($kpredmeti as $predmet) {
					if (!in_array($predmet, $student_predmeti[$student])) {
						$svi = false;
						break;
					}
				}
				if ($svi) {
					$kombinacija_studenti[$id_kombinacije][$student] = $ime;
					unset($studenti[$student]);
				}
			}
			/*if (count($kombinacija_studenti[$id_kombinacije])==0)
				unset($kombinacije[$id_kombinacije]); // Niko nema ovu kombinaciju!*/
			$total_studenata_kombinacije += count($kombinacija_studenti[$id_kombinacije]);
		}
		
		// Formiramo grupe po kombinacijama
		$grupa = 1;
		$kombinacija_grupe = array();
		foreach($kombinacije as $id_kombinacije => $ime_kombinacije) {
			$k_studenata = count($kombinacija_studenti[$id_kombinacije]);
			$k_ratio = $k_studenata / $total_studenata_kombinacije;
			$k_br_grupa = intval($broj_grupa * $k_ratio + 0.5);
			if ($k_br_grupa == 0) $k_br_grupa=1;
			$k_spg = intval ( $k_studenata / $k_br_grupa);
			$k_spg_extra = $k_studenata % $k_spg;
			
			if ($f==0) {
				print "Kombinacija: <b>$ime_kombinacije</b><ul>\n";
				print "<li>Studenata: $k_studenata</li>\n";
				print "<li>Omjer: $k_ratio</li>\n";
				print "<li>Grupa: $k_br_grupa</li>\n";
				print "<li>Studenata po grupi: $k_spg (+$k_spg_extra)</li>\n";
				print "</ul>\n";
			}
			
			$kombinacija_grupe[$id_kombinacije] = array();
			$count = 0;
			foreach($kombinacija_studenti[$id_kombinacije] as $student => $ime) {
				$kombinacija_grupe[$id_kombinacije]["Grupa $grupa"][$student] = $ime;
				$count++;
				if ($count == $k_spg+1) {
					$grupa++;
					$count=0;
					$k_spg_extra--;
				}
				else if ($count == $k_spg && $k_spg_extra == 0) {
					$grupa++;
					$count = 0;
				}
			}
			if ($count > 0) $grupa++;
			
		}
		
		// Raspoređujemo preostale neraspoređene studente u grupe
		foreach($studenti as $student => $ime) {
			foreach($kombinacije as $id_kombinacije => $ime_kombinacije) {
				// Može li student na ovu kombinaciju ikako?
				$kpredmeti = explode("-", $id_kombinacije);
				$moze = true;
				foreach($student_izborni[$student] as $sp) {
					$ima = false;
					foreach($kpredmeti as $predmet)
						if ($predmet == $sp) $ima = true;
					if (!$ima) { $moze=false; break; }
				}
				//print "Student $student moze $moze komb $ime_kombinacije k_spg $broj_studenata_po_grupi<br>";
				
				if ($moze) {
					$dodan = false;
					// Round robin dodajemo u grupe
					foreach($kombinacija_grupe[$id_kombinacije] as $grupa => $studenti) {
						//print "Grupa $grupa count " .count($kombinacija_grupe[$id_kombinacije][$grupa])."<br>";
						if (count($kombinacija_grupe[$id_kombinacije][$grupa]) < $broj_studenata_po_grupi) {
							$kombinacija_grupe[$id_kombinacije][$grupa][$student] = $ime;
							$dodan = true;
							break;
						}
					}
					if ($dodan) break; // foreach ($kombinacije)
				}
				
				// Trebalo bi biti nemoguće da student ne može nigdje?
			}
			
			// Nije nigdje dodan jer sve grupe imaju max. studenata
			if (!$dodan) {
				// Povećavamo globalni broj studenata u grupi
				$broj_studenata_po_grupi++;
				// Dodajemo u neku od grupa na posljednjoj kombinaciji koja može
				foreach($kombinacija_grupe[$id_kombinacije] as $grupa => $studenti) {
					if (count($kombinacija_grupe[$id_kombinacije][$grupa]) < $broj_studenata_po_grupi) {
						$kombinacija_grupe[$id_kombinacije][$grupa][$student] = $ime;
						break;
					}
				}
			}
		}
		
		// Ispis grupa
		foreach($kombinacije as $id_kombinacije => $ime_kombinacije) {
			foreach($kombinacija_grupe[$id_kombinacije] as $grupa => $studenti) {
				if ($f == 0) {
					print "<b>$grupa</b> ($ime_kombinacije):<br>\n<ol>\n";
					foreach($studenti as $id_studenta => $ime_studenta) {
						$predmeti_join = join("-", $student_predmeti[$id_studenta]);
						print "<li>$ime_studenta</li>\n";
					}
					print "</ol>\n";
					
				} else {
					// Kreiramo grupe
					$labgrupe = array();
					foreach ($obavezni as $predmet => $naziv) {
						$lg = db_get("SELECT id FROM labgrupa WHERE naziv='$grupa' AND predmet=$predmet AND akademska_godina=$ag AND virtualna=0");
						if ($lg) {
							print "Grupa $grupa na predmetu $naziv već kreirana<br>";
						} else {
							$q30 = db_query("insert into labgrupa set naziv='$grupa', predmet=$predmet, akademska_godina=$ag, virtualna=0");
							$labgrupe[$predmet] = db_insert_id();
							print "Kreiram grupu $grupa na predmetu $naziv<br>";
						}
					}
					
					$kpredmeti = explode("-", $id_kombinacije);
					foreach($kpredmeti as $predmet) {
						$lg = db_get("SELECT id FROM labgrupa WHERE naziv='$grupa' AND predmet=$predmet AND akademska_godina=$ag AND virtualna=0");
						if ($lg) {
							print "Grupa $grupa na predmetu $predmet već kreirana<br>";
						} else {
							$q30 = db_query("insert into labgrupa set naziv='$grupa', predmet=$predmet, akademska_godina=$ag, virtualna=0");
							$labgrupe[$predmet] = db_insert_id();
							print "Kreiram grupu $grupa na predmetu $predmet<br>";
						}
					}
					
					foreach($studenti as $id_studenta => $ime_studenta) {
						foreach ($labgrupe as $predmet => $lg) {
							if (in_array($predmet, $student_predmeti[$id_studenta]))
								$q50 = db_query("insert into student_labgrupa set student=$id_studenta, labgrupa=$lg");
						}
					}
				}
			}
		}
		
		
		if ($f==0) {
			?>
			<?=genform("POST")?>
			<input type="hidden" name="fakatradi" value="1">
			<input type="hidden" name="akcija" value="grupe-ri2">
			<input type="hidden" name="_lv_column_akademska_godina" value="<?=$ag?>">
			<input type="submit" value=" Fakat radi ">
			</form>
			<?
		} else {
			nicemessage("Grupe kreirane, studenti upisani.");
		}
		
	} else {
		
		
		?>
		<?=genform("POST")?>
		<input type="hidden" name="fakatradi" value="0">
		<input type="hidden" name="akcija" value="grupe-ri2">
		Akademska godina: <?=db_dropdown('akademska_godina')?><br/>
		Godina studija: <select name="godina_studija"><option value="1">Prva</option><option value="2">Druga</option><option value="3">Treća</option></select><br/>
		Ciklus: <select name="ciklus"><option value="1">Prvi</option><option value="2">Drugi</option><option value="3">Treći</option></select><br/>
		Broj grupa: <input type="text" name="brojgrupa" size="5" value="10"><br/>
		Studiji: <?
		
		$q10 = db_query("select s.id, s.kratkinaziv from studij as s, tipstudija as ts where s.tipstudija=ts.id and ts.ciklus=1 and s.moguc_upis=1");
		while ($r10 = db_fetch_row($q10)) {
			?>
			<input type="checkbox" name="studij[]" value="<?=$r10[0]?>"><?=$r10[1]?>
			<?
		}
		
		?><br/>
		Parni semestar: <input type="checkbox" name="parni" value="1"><br>
		<input type="submit" value=" Kreiraj grupe na godini ">
		</form>
		<?
	}
}