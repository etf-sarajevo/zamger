<?php



//----------------------------------------
// Zamijeni ponudu kursa
//----------------------------------------

// Ovaj modul se koristi kada se student želi prebaciti sa jedne na drugu ponudu kursa unutar istog predmeta (npr.
// student je greškom upisan na pk za odsjek RI a ustvari je student TK) ili ako se želi prebaciti sa jednog na drugi
// predmet koji je ustvari isti predmet ali se iz nekog razloga vodi odvojeno u bazi

function admin_misc_zamijeni_pk() {
	global $conf_files_path;

	if ($_POST['akcija']=="zamijeni_pk" && check_csrf_token()) {
		$osoba = intval($_REQUEST['osoba']);
		$stari_pk = intval($_REQUEST['old_pk']);
		$novi_pk = intval($_REQUEST['new_pk']);
		if ($_REQUEST['fakatradi'] != 1) $ispis=1; else $ispis=0;
		
		$osobe = array();
		if ($osoba==0) {
			$osobe = db_query_varray("SELECT student FROM student_predmet WHERE predmet=$stari_pk");
		} else {
			$osobe[] = $osoba;
		}
		
		
		foreach($osobe as $osoba) {
			// Podaci za lijepi ispis
			$q1 = db_query("SELECT ime, prezime, brindexa FROM osoba WHERE id=$osoba");
			$ime = db_result($q1,0,0);
			$prezime = db_result($q1,0,1);
			$brindexa = db_result($q1,0,2);
			if ($ispis)
				print "-- Student $prezime $ime ($brindexa)<br>";
			
			$q2 = db_query("SELECT p.naziv, ag.id, ag.naziv, pk.semestar, p.id FROM ponudakursa as pk, predmet as p, akademska_godina as ag WHERE pk.id=$stari_pk AND pk.predmet=p.id AND pk.akademska_godina=ag.id");
			$stari_predmet = db_result($q2,0,0);
			$stari_ag = db_result($q2,0,1);
			$stari_ag_naziv = db_result($q2,0,2);
			$semestar = db_result($q2,0,3);
			$stari_pid = db_result($q2,0,4);
			if ($ispis)
				print "-- Stari predmet &quot;$stari_predmet&quot;, ag. $stari_ag_naziv, semestar $semestar<br>";
			
			$q3 = db_query("SELECT p.naziv, ag.id, ag.naziv, pk.semestar, p.id, pk.studij FROM ponudakursa as pk, predmet as p, akademska_godina as ag WHERE pk.id=$novi_pk AND pk.predmet=p.id AND pk.akademska_godina=ag.id");
			$novi_predmet = db_result($q3,0,0);
			$novi_ag = db_result($q3,0,1);
			$novi_pid = db_result($q3,0,4);
			$novi_studij = db_result($q3,0,5);
			if ($ispis)
				print "-- Novi predmet &quot;$novi_predmet&quot;<br>";
			if ($stari_ag != $novi_ag && $ispis)
				print "!! Predmeti nisu sa iste ag! Novi predmet je ".db_result($q3,0,2)."<br>";
			if ($semestar != db_result($q3,0,3) && $ispis)
				print "!! Predmeti nisu sa istog semestra! Novi predmet je ".db_result($q3,0,3)."<br>";
			
			
			// Provjera validnosti podataka
			$q10 = db_query("SELECT count(*) FROM student_predmet WHERE student=$osoba AND predmet=$stari_pk");
			$q20 = db_query("SELECT count(*) FROM student_predmet WHERE student=$osoba AND predmet=$novi_pk");
			if (db_result($q10,0,0) != 1) {
				niceerror("Osoba ne sluša ponudukursa $stari_predmet ($stari_pk)");
				return;
			}
			
			$vec_slusa = false;
			if (db_result($q20,0,0) > 0) {
				if ($ispis)
					print("-- Osoba već sluša ponudukursa $novi_predmet ($novi_pk) - samo ćemo ispisati iz stare ($stari_pk)");
				$vec_slusa = true;
			}
			
			print "<br><br>AKCIJE:<br>\n";
			
			// ISPITI
			if ($stari_pid != $novi_pid) {
				$q30 = db_query("SELECT i.id, io.ocjena, i.datum, i.komponenta FROM ispitocjene as io, ispit as i WHERE io.student=$osoba AND io.ispit=i.id AND i.predmet=$stari_pid and i.akademska_godina=$stari_ag");
				while ($r30 = db_fetch_row($q30)) {
					$q35 = db_query("SELECT id FROM ispit WHERE predmet=$novi_pid AND akademska_godina=$novi_ag AND datum='$r30[2]' AND komponenta=$r30[3]");
					if (db_num_rows($q35) == 1) {
						$novi_ispit = db_result($q35,0,0);
						if ($ispis)
							print "-- $r30[1] bodova na ispitu $r30[0] prelazi na ispit $novi_ispit<br>";
						else
							$q38 = db_query("UPDATE ispitocjene SET ispit=$novi_ispit WHERE student=$osoba AND ispit=$r30[0]");
					} else {
						print "!! $r30[1] bodova na ispitu $r30[0] - nisam pronašao odgovarajući ispit (datum $r30[2], aktivnost $r30[3]), promijeniti ispit manuelno<br>";
					}
				}
				
				
				// LABGRUPE (CASOVI I KOMENTARI - samo ih brišemo :( )
				$q40 = db_query("SELECT l.id, l.virtualna, l.naziv FROM student_labgrupa as sl, labgrupa as l WHERE sl.student=$osoba AND sl.labgrupa=l.id AND l.predmet=$stari_pid AND l.akademska_godina=$stari_ag");
				while (db_fetch3($q40, $labgrupa, $virtuelna, $naziv_labgrupe)) {
					$prisustvo = array();
					$q41 = db_query("SELECT c.datum, p.prisutan FROM prisustvo p, cas c WHERE p.cas=c.id AND p.student=$osoba AND c.labgrupa=$labgrupa");
					while (db_fetch2($q41, $datum, $prisutan))
						$prisustvo[$datum] = $prisutan;
					
					if ($virtuelna == 1) {
						$q45 = db_query("SELECT id FROM labgrupa WHERE predmet=$novi_pid AND akademska_godina=$novi_ag AND virtualna=1");
						if (db_num_rows($q45)>0) {
							$nova_lg = db_result($q45,0,0);
							if ($ispis)
								print "-- Ispisujem studenta sa virtuelne labgrupe $naziv_labgrupe ($labgrupa) i upisujem u istoimenu v.lg. $nova_lg<br>\n";
							else {
								ispis_studenta_sa_labgrupe($osoba, $labgrupa);
								$q47 = db_query("INSERT INTO student_labgrupa SET student=$osoba, labgrupa=$nova_lg");
							}
						} else {
							if ($ispis)
								print "!! Predmet $novi_predmet nema virtuelne labgrupe! Student će biti ispisan iz v. lg. $naziv_labgrupe<br>";
							else
								ispis_studenta_sa_labgrupe($osoba, $labgrupa);
						}
					} else {
						$novi_naziv = db_escape_string($naziv_labgrupe);
						$nova_lg = db_get("SELECT id FROM labgrupa WHERE predmet=$novi_pid AND akademska_godina=$novi_ag AND naziv='$novi_naziv'");
						if ($ispis)
							print "-- Ispisujem studenta sa labgrupe $naziv_labgrupe ";
						else
							ispis_studenta_sa_labgrupe($osoba, $labgrupa);
						if ($nova_lg) {
							if ($ispis)
								print "i upisujem u istoimenu lg. ($nova_lg)<br>\n";
							else
								$q47 = db_query("INSERT INTO student_labgrupa SET student=$osoba, labgrupa=$nova_lg");
						} else {
							if ($ispis)
								print "-- nisam pronašao istoimenu lg. (dodajte ručno)<br>\n";
						}
					}
					
					if ($nova_lg) {
						if ($ispis) print "-- migriram prisustvo: ";
						foreach($prisustvo as $datum => $prisutan) {
							$cas = db_get("SELECT id FROM cas WHERE labgrupa=$nova_lg AND datum='$datum'");
							if ($cas) {
								if ($ispis) print "$datum [+] ";
								else db_query("INSERT INTO prisustvo SET student=$osoba, cas=$cas, prisutan=$prisutan");
							} else
								if ($ispis) print "$datum [-] ";
						}
						print "<br>\n";
					}
				}
			} else {
				if ($ispis) print "Isti ID predmeta, ne mijenjam ispite, labgrupe i prisustvo.<br>\n";
			}
			
			
			// KOMPONENTEBODOVI
			$q50 = db_query("SELECT kb.komponenta, kb.bodovi, akp.naziv FROM komponentebodovi as kb, aktivnost_predmet as akp WHERE kb.student=$osoba AND kb.predmet=$stari_pk AND kb.komponenta=akp.id");
			while ($r50 = db_fetch_row($q50)) {
				$q55 = db_query("SELECT COUNT(*) FROM aktivnost_agp WHERE akademska_godina=$novi_ag AND predmet=$novi_pid AND aktivnost_predmet=$r50[0]");
				if (db_result($q55,0,0) == 0) {
					print "!! Predmet $novi_predmet nema aktivnost $r50[2] ($r50[0])! Manuelno editujte bazu<br>\n";
				} else {
					if ($ispis)
						print "-- Prenosim bodove za aktivnost $r50[2] ($r50[0])<br>\n";
					else
						$q57 = db_query("UPDATE komponentebodovi SET predmet=$novi_pk WHERE student=$osoba AND predmet=$stari_pk AND komponenta=$r50[0]");
				}
			}
			
			
			// KONACNA_OCJENA
			if ($stari_pid != $novi_pid) {
				$q60 = db_query("SELECT ocjena FROM konacna_ocjena WHERE student=$osoba AND predmet=$stari_pid AND akademska_godina=$stari_ag");
				if (db_num_rows($q60)>0) {
					$ocjena = db_result($q60,0,0);
					if ($ispis) print "-- Prebacujem ocjenu $ocjena ";
					
					// Određujem pasoš predmeta
					$pasos_predmeta = false;
					$plan_studija = db_get("SELECT id FROM plan_studija WHERE studij=$novi_studij AND godina_vazenja<=$novi_ag order by godina_vazenja desc limit 1");
					if ($plan_studija) {
						$pasos_predmeta = db_get("SELECT pp.id FROM plan_studija_predmet psp, pasos_predmeta pp WHERE psp.plan_studija=$plan_studija AND psp.pasos_predmeta=pp.id AND pp.predmet=$novi_pid");
						if (!$pasos_predmeta)
							$pasos_predmeta = db_get("SELECT pp.id FROM plan_studija_predmet psp, plan_izborni_slot pis, pasos_predmeta pp WHERE psp.plan_studija=$plan_studija AND psp.plan_izborni_slot=pis.id AND pis.pasos_predmeta=pp.id AND pp.predmet=$novi_pid");
					}
					
					if ($pasos_predmeta == false) {
						if ($ispis)
							print " - nepoznat pasoš predmeta! ažurirajte bazu ručno<br>\n";
						else
							$q65 = db_query("UPDATE konacna_ocjena SET predmet=$novi_pid, akademska_godina=$novi_ag, pasos_predmeta=NULL WHERE student=$osoba AND predmet=$stari_pid AND akademska_godina=$stari_ag");
					} else {
						if ($ispis)
							print " (pasoš predmeta $pasos_predmeta)<br>\n";
						else
							$q65 = db_query("UPDATE konacna_ocjena SET predmet=$novi_pid, akademska_godina=$novi_ag, pasos_predmeta=$pasos_predmeta WHERE student=$osoba AND predmet=$stari_pid AND akademska_godina=$stari_ag");
					}
					if ($novi_pid != $stari_pid)
						$q67 = db_query("UPDATE izvoz_ocjena SET predmet=$novi_pid WHERE predmet=$stari_pid AND student=$osoba");
				}
			} else {
				if ($ispis) print "Isti ID predmeta, ne mijenjam konačnu ocjenu.<br>\n";
			}
			
			
			// KVIZ - nije implementirano
			
			// PROJEKAT, STUDENT_PROJEKAT - nije implementirano
			
			// STUDENT_ISPIT_TERMIN - nije implementirano
			
			// ZADACA
			if ($stari_pid != $novi_pid) {
				$q70 = db_query("SELECT DISTINCT z.id, z.naziv FROM zadaca as z, zadatak as zk WHERE zk.student=$osoba and zk.zadaca=z.id AND z.predmet=$stari_pid AND z.akademska_godina=$stari_ag");
				while (db_fetch2($q70, $stara_zadaca, $stari_naziv)) {
					$q75 = db_query("SELECT id FROM zadaca WHERE naziv='$stari_naziv' AND predmet=$novi_pid AND akademska_godina=$novi_ag");
					if (db_num_rows($q75)==0)
						print "!! Nisam pronašao odgovarajuću zadaću za &quot;$stari_naziv&quot; ($stara_zadaca). Migrirajte ručno<br>\n";
					else {
						$nova_zadaca = db_result($q75,0,0);
						if ($ispis)
							print "-- Migriram sve zadatke za zadaću $stari_naziv ($stara_zadaca) na $nova_zadaca<br>\n";
						else {
							$q78 = db_query("UPDATE zadatak SET zadaca=$nova_zadaca WHERE student=$osoba AND zadaca=$stara_zadaca");
							$stara_zadaca_path = "$conf_files_path/zadace/$stari_pid-$stari_ag/$osoba";
							if (file_exists($stara_zadaca_path)) {
								$zadaca_path = "$conf_files_path/zadace/$novi_pid-$novi_ag/$osoba";
								if (!file_exists($zadaca_path))
									mkdir ("$zadaca_path", 0777, true);
								rename("$stara_zadaca_path/$stara_zadaca", "$zadaca_path/$nova_zadaca");
							}
						}
					}
				}
			} else {
				if ($ispis) print "Isti ID predmeta, ne mijenjam zadaće.<br>\n";
			}
			
			// Konačno: STUDENT_PREDMET
			if ($ispis)
				print "-- Prepisujem u novu ponudukursa<br>\n<hr><br>\n";
			else {
				if ($vec_slusa)
					$q100a = db_query("DELETE FROM student_predmet WHERE student=$osoba AND predmet=$stari_pk");
				else
					$q100 = db_query("UPDATE student_predmet SET predmet=$novi_pk WHERE student=$osoba AND predmet=$stari_pk");
				print "Migriran student $ime $prezime.<br>\n";
			}
			
		}
		
		
		
		// Potvrda i Nazad
		if ($ispis) {
			?>
			<?=genform("POST")?>
			<input type="hidden" name="fakatradi" value="1">
			<?
			print '<input type="submit" name="nazad" value=" Nazad "> ';
			if ($greska==0) print '<input type="submit" value=" Potvrda ">';
			print "</form>";
			return;
		} else {
			?>
			Migrirani podaci.
			<?
		}
		
		
	} else {
		
		?>
		<?=genform("POST")?>
		<input type="hidden" name="akcija" value="zamijeni_pk">
		Unesite ID osobe: <input type="text" name="osoba" value=""><br>
		Stara ponudakursa: <input type="text" name="old_pk" value=""><br>
		Nova ponudakursa: <input type="text" name="new_pk" value=""><br>
		<input type="submit" value=" Migriraj podatke na drugu ponudu kursa ">
		</form>
		<?
	}
}