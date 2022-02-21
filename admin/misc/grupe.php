<?php




//----------------------------------------
// Masovno kreiranje grupa na prvoj godini
//----------------------------------------

function admin_misc_grupe() {

	if ($_POST['akcija']=="grupe") {
		$f = intval($_POST['fakatradi']);
		
		$studenti=$studenti_id=array();
		
		$broj_grupa=intval($_REQUEST['brojgrupa']);
		$ag = intval($_REQUEST['_lv_column_akademska_godina']);
		
		$dodaj = $dodaj2 = "";
		foreach($_REQUEST['studij'] as $studij) {
			if ($dodaj != "") { $dodaj .= "or "; $dodaj2 .= "or "; }
			$dodaj .= "ss.studij=$studij ";
			$dodaj2 .= "s.id=$studij ";
		}
		if ($dodaj != "") { $dodaj = "and ($dodaj)"; $dodaj2 = "and ($dodaj2)"; }
		
		$semestar=1;
		if (isset($_REQUEST['parni']) && $_REQUEST['parni'] == 1) $semestar=2;
		
		//print "select o.id, o.ime, o.prezime, o.brindexa from osoba as o, student_studij as ss where ss.student=o.id and ss.akademska_godina=$ag and ss.semestar=$semestar and ss.ponovac=0 $dodaj order by o.prezime, o.ime";
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
			print "Prijedlog spiska grupa :<br/><br/>\n";
		}
		
		
		// Spisak predmeta
		$q20 = db_query("select distinct pk.predmet from ponudakursa as pk, studij as s where pk.semestar=$semestar and pk.obavezan=1 and pk.studij=s.id and pk.akademska_godina=$ag $dodaj2");
		$predmeti=array();
		while ($r20 = db_fetch_row($q20)) array_push($predmeti, $r20[0]);
		if ($f == 0) { print_r($predmeti); print "<br><br>\n"; }
		
		$count=$grupa=1;
		$grupa_naziv = $grupa;
		// Hack za RI i AET
		if ($dodaj == "and (ss.studij=2 )") {
			if ($grupa % 2 == 0) $slovo='b'; else $slovo='a';
			$grupa_naziv = "RI1-" . intval(($grupa+1)/2) . $slovo;
		}
		if ($dodaj == "and (ss.studij=3 or ss.studij=4 or ss.studij=5 )") {
			if ($grupa % 2 == 0) $slovo='b'; else $slovo='a';
			$grupa_naziv = "ATE1-" . intval(($grupa+1)/2) . $slovo;
		}
		if ($f==0) {
			print "<b>Grupa $grupa_naziv</b>:<br/>\n<ol>\n";
		} else {
			$labgrupe=array();
			$labgrupa_predmet = array();
			foreach ($predmeti as $predmet) {
				$q30 = db_query("insert into labgrupa set naziv='Grupa $grupa_naziv', predmet=$predmet, akademska_godina=$ag, virtualna=0");
				$labgrupa = db_get("select id from labgrupa where naziv='Grupa $grupa_naziv' and predmet=$predmet and akademska_godina=$ag and virtualna=0");
				array_push($labgrupe, $labgrupa);
				$labgrupa_predmet[$labgrupa] = $predmet;
			}
		}
		print_r($labgrupa_predmet);
		foreach($studenti as $stud_id=>$stud_ispis) {
			if ($count>$broj_studenata_po_grupi) {
				$count=1;
				if ($broj_ekstra_grupa>0) {
					if ($f==0) {
						print "<li>$stud_ispis</li>\n";
						
					} else {
						foreach ($labgrupe as $lg) {
							$slusa_li = db_get("SELECT COUNT(*) FROM student_predmet sp, ponudakursa pk WHERE sp.student=$stud_id AND sp.predmet=pk.id AND pk.predmet=" . $labgrupa_predmet[$lg] . " AND pk.akademska_godina=$ag");
							if ($slusa_li)
								$q50 = db_query("insert into student_labgrupa set student=$stud_id, labgrupa=$lg");
							else
								print "<li>Student $stud_ispis ne sluša predmet ". db_get("SELECT naziv FROM predmet WHERE id=" . $labgrupa_predmet[$lg]) . "</li>\n";
						}
					}
					
					$broj_ekstra_grupa--;
					$ispiso=1;
					$count=0;
				}
				$grupa++;
				$grupa_naziv = $grupa;
				if ($dodaj == "and (ss.studij=2 )") {
					if ($grupa % 2 == 0) $slovo='b'; else $slovo='a';
					$grupa_naziv = "RI1-" . intval(($grupa+1)/2) . $slovo;
				}
				if ($dodaj == "and (ss.studij=3 or ss.studij=4 or ss.studij=5 )") {
					if ($grupa % 2 == 0) $slovo='b'; else $slovo='a';
					$grupa_naziv = "ATE1-" . intval(($grupa+1)/2) . $slovo;
				}
				if ($f==0) {
					print "</ol>\n";
					print "<b>Grupa $grupa_naziv</b>:<br/>\n<ol>\n";
				} else {
					$labgrupe=array();
					foreach ($predmeti as $predmet) {
						$q30 = db_query("insert into labgrupa set naziv='Grupa $grupa_naziv', predmet=$predmet, akademska_godina=$ag, virtualna=0");
						$q40 = db_query("select id from labgrupa where naziv='Grupa $grupa_naziv' and predmet=$predmet and akademska_godina=$ag and virtualna=0");
						array_push($labgrupe, db_result($q40,0,0));
					}
				}
			}
			if ($ispiso != 1) {
				if ($f==0) { print "<li>$stud_ispis</li>\n";
				} else {
					foreach ($labgrupe as $lg) {
						$q50 = db_query("insert into student_labgrupa set student=$stud_id, labgrupa=$lg");
					}
				}
			}
			$ispiso=0;
			$count++;
		}
		
		/*	for ($i=1; $i<=10; $i++) {
				if ($f==0) {
					print "<b>Grupa $i</b>:<br/>\n<ol>\n";
				}
		
				$start=0;
				if ($broj_ekstra_grupa>0) { $start--; $broj_ekstra_grupa--; }
				for ($j=$start; $j<$broj_studenata_po_grupi; $j++) {
					$astudent = array_pop($studenti)
					$astudentid = array_pop($studenti_id)
		//			$r10 = db_fetch_row($q10);
					if ($f==0) {
						print "<li>$astudent</li>\n";
					}
				}
				print "</ol>\n";
			}*/
		
		
		if ($f==0) {
			?>
			<?=genform("POST")?>
			<input type="hidden" name="fakatradi" value="1">
			<input type="hidden" name="akcija" value="grupe">
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
		<input type="hidden" name="akcija" value="grupe">
		Akademska godina: <?=db_dropdown('akademska_godina')?><br/>
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
		<input type="submit" value=" Kreiraj grupe na prvoj godini ">
		</form>
		<?
	}
}