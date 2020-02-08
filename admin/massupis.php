<?

// ADMIN/MASSUPIS - masovni upis studenata na semestar

// Ovaj kod je u biti funkcionalan samo za upis na parne semestre
// U slučaju da nije definisan NPP može se koristiti za specificiranje izbornih predmeta koristeći uobičajeni modul za masovni unos


function admin_massupis() {

require("lib/manip.php");
global $mass_rezultat, $conf_ldap_server,$conf_ldap_domain; // za masovni unos studenata u grupe


if ($_POST['akcija']=="massupis" && strlen($_POST['nazad'])<1) {

	if ($_POST['fakatradi'] != 1) $ispis=1; else $ispis=0;

	$studij = intval($_POST['_lv_column_studij']);
	if ($studij==0) $studij=intval($_POST['studij']);
	$semestar = intval($_POST['semestar']);

	if ($ispis) {
		?>Akcije koje će biti urađene:<br/><br/>
		<?=genform("POST")?>
		<input type="hidden" name="fakatradi" value="1">
		<input type="hidden" name="studij" value="<?=$studij /* posto genform ukida _lv_* */ ?>">
		<?
	}


	$greska=mass_input($ispis); // Funkcija koja parsira podatke


	// Stara godina
	$q90 = myquery("select id, naziv from akademska_godina where aktuelna=1");
	if (mysql_num_rows($q90)<1) { niceerror("Nema aktuelne akademske godine. Kreirajte jednu"); return; }
	$staraag = mysql_result($q90,0,0);

	// Promjena akademske godine
	$starinaziv = mysql_result($q90,0,1);
	if ($semestar%2==1) {
		$novinaziv = (intval($starinaziv)+1)."/".(intval($starinaziv)+2);

		$q100 = myquery("select id from akademska_godina where naziv='$novinaziv'");
		if (mysql_num_rows($q100)>0) {
			$novaag = mysql_result($q100,0,0);
			if ($ispis) print "Proglašavam akademsku godinu $novinaziv za aktuelnu<br/>";
			else {
				$q110 = myquery("update akademska_godina set aktuelna=0");
				$q120 = myquery("update akademska_godina set aktuelna=1 where id=$novaag");
			}
		} else {
			if ($ispis) print "Dodajem akademsku godinu $novinaziv i proglašavam za aktuelnu";
			else {
				$q130 = myquery("update akademska_godina set aktuelna=0");
				$q140 = myquery("select id from akademska_godina order by id desc limit 1");
				$novaag = mysql_result($q140,0,0)+1;
				$q150 = myquery("insert into akademska_godina set id=$novaag, naziv='$novinaziv', aktuelna=1");
			}
		}
	} else {
		$novaag=$staraag;
		$novinaziv=$starinaziv;
	}

	// Uzimam spisak predmeta
	$q10 = myquery("select pk.id, p.naziv, pk.obavezan from ponudakursa as pk, predmet as p where pk.studij=$studij and pk.semestar=$semestar and pk.akademska_godina=$novaag and pk.predmet=p.id");
	$predmeti=$nazivipredmeta=$obaveznipredmeti=array();
	while ($r10 = mysql_fetch_row($q10)) {
		array_push($predmeti,$r10[0]);
		array_push($nazivipredmeta,$r10[1]);
//print "O: $r5[1]<br>";
		array_push($obaveznipredmeti,$r10[2]);
	}

	// Upis u izborne predmete putem masovnog unosa
	$brpredmeta=0;
	if ($greska==0 && count($mass_rezultat)>0) {
		foreach ($mass_rezultat['ime'] as $student=>$ime) {
			$prezime = $mass_rezultat['prezime'][$student];

			// Preskacemo studente koji nisu na studiju
			$q20 = myquery("select count(*) from student_studij where student=$student and studij=$studij and semestar=".($semestar-1)." and akademska_godina=$staraag");
			/*if (mysql_result($q20,0,0)<1) {
				if ($ispis) print "(Preskačem studenta '$ime $prezime' jer nije na ovom studiju)<br>\n";
//				$greska=1;
				continue;
			}*/

			foreach ($mass_rezultat['podatak1'][$student] as $predmet) {
				// Pronalazimo predmet
				$found=-1;
				foreach ($nazivipredmeta as $id=>$naziv) {
					if ($predmet==$naziv && $obaveznipredmeti[$id]==0) { $found=$id; break; }
				}
				if ($found==-1) {
					if ($ispis) print "-- Nepoznat predmet '$predmet' (student '$ime $prezime')<br>\n";
					$greska=1;
					continue;
				}
	
				// Da li je student vec upisan
				$q10 = myquery("select count(*) from student_predmet where student=$student and predmet=".$predmeti[$found]);
				if (mysql_result($q10,0,0)>0) {
					if ($ispis) print "-- Student '$ime $prezime' je vec upisan na IZBORNI predmet '$predmet'<br>\n";
//					$greska=1;
					continue;
				}
	
				// Da li je polozio nekad ranije
				$q20 = myquery("select count(*) from konacna_ocjena as ko, predmet as p where ko.student=$student and ko.predmet=p.id and p.naziv='".$nazivipredmeta[$found]."'");
				if (mysql_result($q20,0,0)>0) {
					if ($ispis) print "-- Student '$ime $prezime' je nekad ranije položio IZBORNI predmet '$predmet'<br>\n";
//					$greska=1;
					continue;
				}
	
				// Sve ok, upisuj
				if ($ispis) {
					print "Upisujem studenta '$ime $prezime' na IZBORNI predmet '$predmet'<br>\n";
				} else {
					//$q30 = myquery("insert into student_predmet set student=$student, predmet=".$predmeti[$found]);
					upis_studenta_na_predmet($student, $predmeti[$found]);
					$brpredmeta++;
				}
			}
		}

	}

	// Da li student ostaje na istom studiju ili upisuje novi?
	$upisano=0;
	$q160 = myquery("select ts.trajanje, s.naziv from studij s, tipstudija ts where s.id=$studij and s.tipstudija=ts.id");
	$nazivstudija = mysql_result($q160,0,1);
	if (mysql_result($q160,0,0)>=$semestar-1) { // Izabrani semestar je manji od broja semestara

		// Isti studij!
		// Uzimam spisak studenata
		$q180 = myquery("select o.id, o.ime, o.prezime, o.brindexa from student_studij as ss, osoba as o where ss.student=o.id and ss.studij=$studij and ss.semestar=".($semestar-1)." and ss.akademska_godina=$staraag order by o.prezime, o.ime");
		while ($r180 = mysql_fetch_row($q180)) {
			$student = $r180[0];
			$imeprezime = "$r180[1] $r180[2] ($r180[3])";

			// Odredjujem parametre
			$q182 = myquery("select nacin_studiranja, ponovac, plan_studija from student_studij where student=$student and studij=$studij and akademska_godina=$staraag and semestar=".($semestar-1));
			$nacin_studiranja = mysql_result($q182,0,0);
			$ponovac = mysql_result($q182,0,1);
			$plan_studija = mysql_result($q182,0,2);

			// Upis na semestar (eventualno)
			$parni = $semestar%2;
			$q184 = myquery("select studij, semestar, nacin_studiranja, ponovac from student_studij where student=$student and semestar MOD 2=$parni and akademska_godina=$novaag");
			if (mysql_num_rows($q184)>0) {
				$pogresan_studij = mysql_result($q184,0,0);
				$pogresan_semestar = mysql_result($q184,0,1);
				if ($ispis) {
					print "SEMESTAR: <b>Ne upisujem</b> studenta $imeprezime na studij $nazivstudija jer je već upisan na studij $pogresan_studij, semestar $pogresan_semestar<br>\n";
				}
				if ($pogresan_studij != $studij) continue;
			} else {
				if ($ispis) print "SEMESTAR: Upisujem studenta <a href=\"?sta=studentska/osobe&amp;akcija=edit&amp;osoba=$student\">$imeprezime</a> na studij $nazivstudija, semestar $semestar., godina $novinaziv (ns: $nacin_studiranja, p: $ponovac, plan: $plan_studija)<br>\n";
				else {
					$q183 = myquery("insert into student_studij set student=$student, studij=$studij, semestar=$semestar, akademska_godina=$novaag, nacin_studiranja=$nacin_studiranja, ponovac=$ponovac, plan_studija=$plan_studija");
					$upisano++;
				}
			}

			// Spisak predmeta
			foreach ($predmeti as $id=>$pk) { // $id je redni broj u nizu koji ne znači ništa
				if ($obaveznipredmeti[$id]==1) {
					// Obavezni predmeti
					$q185 = myquery("select count(*) from konacna_ocjena as ko, predmet as p where ko.student=$student and ko.predmet=p.id and p.naziv='".$nazivipredmeta[$id]."'");
					if (mysql_result($q185,0,0)==0) {
						$q186 = myquery("select count(*) from student_predmet where student=$student and predmet=$pk");
						if (mysql_result($q186,0,0)==0) {
							if ($ispis) print "Upisujem studenta $imeprezime na obavezan predmet ".$nazivipredmeta[$id]." $pk<br>\n";
							else {
								// Brisanje iz labgrupa
								$q187 = myquery("select sl.labgrupa from 
student_labgrupa as sl, labgrupa as l, ponudakursa as pk where sl.student=$student and sl.labgrupa=l.id 
and l.predmet=pk.predmet and l.akademska_godina=pk.akademska_godina and pk.id=$pk");
								while ($r187 = mysql_fetch_row($q187)) {
									//$q188 = myquery("delete from student_labgrupa where student=$student and labgrupa=".$r187[0]);
									ispis_studenta_sa_labgrupe($student, $r187[0]);
								}

								//$q190 = myquery("insert into student_predmet set student=$student, predmet=$pid");
								upis_studenta_na_predmet($student, $pk);
								$brpredmeta++;
							}
						} else if ($ispis)
						print "-- NE upisujem studenta $imeprezime na obavezan predmet ".$nazivipredmeta[$id]." jer je već upisan/a<br>\n";
					} else if ($ispis)
						print "-- NE upisujem studenta $imeprezime na obavezan predmet ".$nazivipredmeta[$id]." jer ga je položio/la<br>\n";
				}
			}

			// Izborni predmeti preko Ugovora o ucenju
			if (count($mass_rezultat['podatak1'][$student])>0) {
				if ($ispis) print "-- Ignorišem Ugovor o učenju studenta $imeprezime jer ste unijeli izborne predmete.<br>\n";
				continue;
			}
			$q190 = myquery("select id from ugovoroucenju where student=$student and akademska_godina=$novaag and studij=$studij and semestar=$semestar order by id desc limit 1");
			if (mysql_num_rows($q190)<1) {
				if ($ispis) print "-- Student $imeprezime nije popunio Ugovor o učenju!<br>\n";
				continue;
			}
			$ugovor = mysql_result($q190,0,0);
			$q192 = myquery("select predmet from ugovoroucenju_izborni where ugovoroucenju=$ugovor");
			while ($r192 = mysql_fetch_row($q192)) { // pretpostavljamo da je ugovor ok
				$pid = $r192[0];
				
				$q193 = myquery("select naziv from predmet where id=$pid");
				if (mysql_num_rows($q193) < 1) {
					if ($ispis) print "--- Preskačem predmet iz ugovora $pid - nepostojeći ID!<br>\n";
					continue;
				}
				$predmetnaziv = mysql_result($q193,0,0);
				
				$q194 = myquery("select id from ponudakursa where predmet=$pid and studij=$studij and semestar=$semestar and akademska_godina=$novaag");
				if (mysql_num_rows($q194) < 1) {
					if ($ispis) print "Kreiram ponudu kursa za predmet $predmetnaziv, studij $nazivstudija, semestar $semestar, godina $novinaziv<br>\n";
					else {
						$q194a = myquery("insert into ponudakursa set predmet=$pid, studij=$studij, semestar=$semestar, obavezan=0, akademska_godina=$novaag");
						$pk = mysql_insert_id();
					}
				} else
					$pk = mysql_result($q194,0,0);

				$q195 = myquery("select count(*) from konacna_ocjena where student=$student and predmet=$pid");
				if (mysql_result($q195,0,0)==0) {
					$q196 = myquery("select count(*) from student_predmet where student=$student and predmet=$pk");
					if (mysql_result($q196,0,0)==0) {
						if ($ispis) print "Upisujem studenta $imeprezime na IZBORNI predmet $predmetnaziv prema Ugovoru<br>\n";
						else {
								// Brisanje iz labgrupa
								$q187 = myquery("select sl.labgrupa from 
student_labgrupa as sl, labgrupa as l, ponudakursa as pk where sl.student=$student and sl.labgrupa=l.id 
and l.predmet=pk.predmet and l.akademska_godina=pk.akademska_godina and pk.id=$pk");
								while ($r187 = mysql_fetch_row($q187)) {
									//$q188 = myquery("delete from student_labgrupa where student=$student and labgrupa=".$r187[0]);
									
ispis_studenta_sa_labgrupe($student, $r187[0]);
								}
							//$q197 = myquery("insert into student_predmet set student=$student, predmet=$r190[0]");
							upis_studenta_na_predmet($student, $pk);
							$brpredmeta++;
						}
					} else if ($ispis)
						print "-- NE upisujem studenta $imeprezime na IZBORNI predmet $predmetnaziv (Ugovor) jer je već upisan/a<br>\n";
				} else if ($ispis)
					print "-- NE upisujem studenta $imeprezime na IZBORNI predmet $predmetnaziv (Ugovor) jer ga je položio/la<br>\n";
			}
		}

	} else {
		// Promjena studija
		print "Greska: Ne znam kako da predjem na sljedeci studij :(";
		$greska=1; // TODO
	}

	// Potvrda i Nazad
	if ($ispis) {

		print '<input type="submit" name="nazad" value=" Nazad "> ';
		if ($greska==0) print '<input type="submit" value=" Potvrda ">';
		print "</form>";
		return;
	} else {
		?>
		Masovno upisani studenti (upisano <?=$upisano?> studenata na studij, <?=$brpredmeta?> studenata na predmete).
		<?
	}
	return; 

}



?>

<b>Masovni upis studenata na semestar</b>

<?=genform("POST")?>
<p>Izaberite studij: <?=db_dropdown("studij")?></p>
<p>Izaberite semestar u koji upisujete studente: <input type="text" name="semestar" value="0"></p>
<p>Masovni unos izbornih predmeta (format student[TAB]predmet):</p>

<input type="hidden" name="fakatradi" value="0">
<input type="hidden" name="akcija" value="massupis">
<input type="hidden" name="nazad" value="">
<input type="hidden" name="visestruki" value="1">
<input type="hidden" name="duplikati" value="1">
<input type="hidden" name="brpodataka" value="1">

<textarea name="massinput" cols="50" rows="10"><?
if (strlen($_POST['nazad'])>1) print $_POST['massinput'];
?></textarea><br/>
<br/>Format imena i prezimena: <select name="format" class="default">
<option value="0" <? if($format==0) print "SELECTED";?>>Prezime[TAB]Ime</option>
<option value="1" <? if($format==1) print "SELECTED";?>>Ime[TAB]Prezime</option>
<option value="2" <? if($format==2) print "SELECTED";?>>Prezime Ime</option>
<option value="3" <? if($format==3) print "SELECTED";?>>Ime Prezime</option></select>&nbsp;
Separator: <select name="separator" class="default">
<option value="0" <? if($separator==0) print "SELECTED";?>>Tab</option>
<option value="1" <? if($separator==1) print "SELECTED";?>>Zarez</option></select><br/><br/>
<br/><br/>

<input type="submit" value="  Dodaj  ">
</form></p>


<?

}

?>
