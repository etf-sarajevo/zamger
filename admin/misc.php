<?

// ADMIN/MISC - sta god mi padne na pamet da kodiram





function admin_misc() {


require("lib/manip.php");
global $mass_rezultat, $conf_ldap_server,$conf_ldap_domain; // za masovni unos studenata u grupe


?>
<p>&nbsp;</p>
<h3>Ostalo</h3>
<p>Ovdje možete dodati svoj kod:</p>
<?


// Spisak sa linkovima na studentska/osobe radi lakšeg upisa

if ($_REQUEST['akcija'] == "upis_linkovi") {
	$nova_ag = intval($_REQUEST['ag']);
	$godina = intval($_REQUEST['godina']);
	$parni = intval($_REQUEST['parni']);
	$neparni = 1-$parni;
	if (isset($_REQUEST['direktno'])) $direktno = true; else $direktno = false;
	if (isset($_REQUEST['kolizija'])) $kolizija = true; else $kolizija = false;
	
	$stari_semestar = $godina*2 - $parni;
	$novi_semestar = $stari_semestar+1;
	$stara_ag = $nova_ag - $neparni;

	$q10 = db_query("select o.id, o.ime, o.prezime, o.brindexa, ss.studij from osoba as o, student_studij as ss where ss.akademska_godina=$stara_ag and ss.semestar=$stari_semestar and 
ss.student=o.id order by o.prezime, o.ime");
	while ($r10 = db_fetch_row($q10)) {
		if ($kolizija) {
			$q20 = db_query("SELECT count(*) FROM kolizija WHERE student=$r10[0] AND akademska_godina=$nova_ag");
			if (db_result($q20,0,0)==0) continue;
		} else {
			$q20 = db_query("SELECT count(*) FROM student_studij WHERE student=$r10[0] AND akademska_godina=$nova_ag AND semestar mod 2=$neparni");
			if (db_result($q20,0,0)>0) continue;
		}
		if ($direktno)
			print "<a href=\"?sta=studentska/osobe&osoba=$r10[0]&akcija=upis&studij=$r10[4]&semestar=$novi_semestar&godina=$nova_ag\">$r10[2] $r10[1] ($r10[3])</a><br>\n";
		else
			print "<a href=\"?sta=studentska/osobe&osoba=$r10[0]&akcija=edit\">$r10[2] $r10[1] ($r10[3])</a><br>\n";
	}
	
}




// Upis brucoša u predmete na prvoj godini, ako to nije obavila studentska služba

if ($_REQUEST['akcija'] == "upis_prva") {
	$ag = intval($_REQUEST['ag']);

	if ($_REQUEST['fakatradi'] != 1) $ispis=1; else $ispis=0;
	$q10 = db_query("select ss.student, ss.studij, s.kratkinaziv from student_studij as ss, studij as s, tipstudija as ts where ss.akademska_godina=$ag and ss.studij=s.id and s.tipstudija=ts.id and ts.ciklus=1");
	while ($r10 = db_fetch_row($q10)) {
		$q5 = db_query("select pk.id, p.naziv from ponudakursa as pk, predmet as p where pk.semestar=1 and pk.akademska_godina=$ag and pk.studij=$r10[1] and pk.predmet=p.id");
		while ($r5 = db_fetch_row($q5)) {
			$q15 = db_query("select count(*) from student_predmet where student=$r10[0] and predmet=$r5[0]");
			if (db_result($q15,0,0)>0) {
				if ($ispis) {
					$q20 = db_query("select ime, prezime, brindexa from osoba where id=$r10[0]");
					print "Student ".db_result($q20,0,0)." ".db_result($q20,0,1)." ".db_result($q20,0,2)." već upisan na ponudukursa $r5[1]<br>";
				}
			} else {
				if ($ispis) {
					$q20 = db_query("select ime, prezime, brindexa from osoba where id=$r10[0]");
					print "Upisujem studenta ".db_result($q20,0,0)." ".db_result($q20,0,1)." ".db_result($q20,0,2)." na ponudukursa $r5[1] ($r10[2] - $r5[0])<br>";
				} else
					upis_studenta_na_predmet($r10[0], $r5[0]);
			}
		}
	}
}



//----------------------------------------
// Masovni unos jmbg
//----------------------------------------

function testjmbg($jmbg) {
	if (strlen($jmbg)!=13) return "JMBG nema tačno 13 cifara";
	for ($i=0; $i<13; $i++) {
		$slovo = substr($jmbg,$i,1);
		if ($slovo<'0' || $slovo>'9') return "Neki od znakova nisu cifre";
		$cifre[$i] = $slovo-'0';
	}
	// Datum
	if (!checkdate($cifre[2]*10+$cifre[3], $cifre[0]*10+$cifre[1], $cifre[4]*10+$cifre[5]))
		return "Datum rođenja je kalendarski nemoguć";
	// Checksum
	$k = 11 - (( 7*($cifre[0]+$cifre[6]) + 6*($cifre[1]+$cifre[7]) + 5*($cifre[2]+$cifre[8]) + 4*($cifre[3]+$cifre[9]) + 3*($cifre[4]+$cifre[10]) + 2*($cifre[5]+$cifre[11]) ) % 11);
	if ($k==11) $k=0;
	if ($k!=$cifre[12]) return "Checksum ne valja ($cifre[12] a trebao bi biti $k)";
	return "";
}

if ($_POST['akcija'] == "massjmbg" && strlen($_POST['nazad'])<1) {

	if ($_POST['fakatradi'] != 1) $ispis=1; else $ispis=0;

	$greska=mass_input($ispis); // Funkcija koja parsira podatke
	if (count($mass_rezultat)==0) {
		niceerror("Niste unijeli nijedan koristan podatak.");
		return;
	}

	if ($ispis) {
		?>Akcije koje će biti urađene:<br/><br/>
		<?=genform("POST")?>
		<input type="hidden" name="fakatradi" value="1">
		<?
	} else {
}

	// Spisak studenata
	foreach ($mass_rezultat['ime'] as $student=>$ime) {
		$prezime = $mass_rezultat['prezime'][$student];

		$jmbg = trim($mass_rezultat['podatak1'][$student]);
		$t = testjmbg($jmbg);
		if ($t != "")
			if ($ispis==1) print "++ Student '$ime $prezime' JMBG '$jmbg' -- $t<br/>\n";
			else print "$ime $prezime - $jmbg<br/>\n";
		else if ($ispis==1)
			print "Student '$ime $prezime' ispravan JMBG $jmbg<br/>\n";
		
		if ($ispis==0)
			$q10 = db_query("update osoba set jmbg='$jmbg' where id=$student");
	}

	// Potvrda i Nazad
	if ($ispis) {

		print '<input type="submit" name="nazad" value=" Nazad "> ';
		if ($greska==0) print '<input type="submit" value=" Potvrda ">';
		print "</form>";
		return;
	} else {
		?>
		Upisani JMBGovi.
		<?
	}


}



?>

<p><hr/></p><p><b>Masovni unos JMBGova</b><br/>
<?=genform("POST")?>
<input type="hidden" name="fakatradi" value="0">
<input type="hidden" name="akcija" value="massjmbg">
<input type="hidden" name="nazad" value="">
<input type="hidden" name="visestruki" value="1">
<input type="hidden" name="duplikati" value="0">
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
</form></p><?





//----------------------------------------
// Masovni unos broja indexa
//----------------------------------------


if ($_POST['akcija'] == "massindex" && strlen($_POST['nazad'])<1) {

	if ($_POST['fakatradi'] != 1) $ispis=1; else $ispis=0;

	$greska=mass_input($ispis); // Funkcija koja parsira podatke
	if (count($mass_rezultat)==0) {
		niceerror("Niste unijeli nijedan koristan podatak.");
		return;
	}

	if ($ispis) {
		?>Akcije koje će biti urađene:<br/><br/>
		<?=genform("POST")?>
		<input type="hidden" name="fakatradi" value="1">
		<?
	}

	// Spisak studenata
	foreach ($mass_rezultat['ime'] as $student=>$ime) {
		$prezime = $mass_rezultat['prezime'][$student];

		$brindexa = intval($mass_rezultat['podatak1'][$student]);
		if ($brindexa < 15000 || $brindexa>16000)
			if ($ispis==1) print "++ Student '$ime $prezime' brindexa '$brindexa'<br/>\n";
			else print "$ime $prezime - $brindexa<br/>\n";
		else if ($ispis==1)
			print "Student '$ime $prezime' ispravan broj indexa $brindexa<br/>\n";
		
		if ($ispis==0) {
			$q5 = db_query("update osoba set brindexa='$brindexa' where id=$student");

			$q10 = db_query("update privilegije set privilegija='student' where osoba=$student");
			$q20 = db_query("insert into student_studij set student=$student, studij=1, semestar=1, akademska_godina=4");
			$q30 = db_query("select id from ponudakursa where studij=1 and semestar=1 and akademska_godina=4");
			while ($r30 = db_fetch_row($q30)) {
				upis_studenta_na_predmet($student, $r30[0]);
			}
		}
	}

	// Potvrda i Nazad
	if ($ispis) {

		print '<input type="submit" name="nazad" value=" Nazad "> ';
		if ($greska==0) print '<input type="submit" value=" Potvrda ">';
		print "</form>";
		return;
	} else {
		?>
		Upisani brojevi indexa.
		<?
	}


}




?>

<p><hr/></p><p><b>Masovni unos broja indexa</b><br/>
<?=genform("POST")?>
<input type="hidden" name="fakatradi" value="0">
<input type="hidden" name="akcija" value="massindex">
<input type="hidden" name="nazad" value="">
<input type="hidden" name="visestruki" value="1">
<input type="hidden" name="duplikati" value="0">
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
</form></p><?









//----------------------------------------
// Masovno kreiranje logina
//----------------------------------------

?>
<p><hr/></p>

<?


if ($_POST['akcija']=="logini") {
	$f = intval($_POST['fakatradi']);

/*	// Tražimo ovaj login na LDAPu...
	$ds = ldap_connect($conf_ldap_server);
	ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
	if (!ldap_bind($ds)) {
		zamgerlog("Ne mogu se spojiti na LDAP server",3); // 3 - greska
		niceerror("Ne mogu se spojiti na LDAP server");
		return;
	}


	print "Spisak studenata kojima fale logini:<br/>\n<ul>";

	$q10 = db_query("select o.id, o.ime, o.prezime, o.brindexa from osoba as o, student_studij as ss, akademska_godina as ag where ss.student=o.id and ss.akademska_godina=ag.id and ag.aktuelna=1 and ss.semestar=1 and (select count(*) from auth as a where a.id=o.id)=0 order by o.prezime, o.ime");
	while ($r10 = db_fetch_row($q10)) {
		print "<li>$r10[2] $r10[1] $r10[3] - ";


		// predloženi login
		$suggest_login = gen_ldap_uid($r10[0]);
		print "login <b>$suggest_login</b> - ";


		$sr = ldap_search($ds, "", "uid=$suggest_login", array() /* just dn *//* );
		if (!$sr) {
			print "ldap_search() nije uspio.";
		}
		$results = ldap_get_entries($ds, $sr);
		if ($results['count'] < 1) {
			print "<font color=\"red\">nema na LDAP serveru</font></li>";
			// Nastavljamo dalje sa edit akcijom kako bi studentska mogla popraviti podatke

		} else if ($f==1) {
			print "dodan u bazu<br/>";
			// Dodajemo login, ako nije podešen
			$q111 = db_query("insert into auth set id=$r10[0], login='$suggest_login', aktivan=1");

			// Generišemo email adresu ako nije podešena
			$q115 = db_query("select email from osoba where id=$r10[0]");
			if (db_result($q115,0,0) == "") {
				$email = $suggest_login.$conf_ldap_domain;
				$q114 = db_query("update osoba set email='$email' where id=$r10[0]");
			}
		} else {
			print "ok<br/>";
		}

	}
	print "</ul>\n";*/

	// Za koju akademsku godinu?
	$q5 = db_query("select id from akademska_godina order by id desc limit 1");
	$ag = db_result($q5,0,0);

	$bilo=array();
	$count=array();
	$trans = array("č"=>"c", "ć"=>"c", "đ"=>"d", "š"=>"s", "ž"=>"z", "Č"=>"C", "Ć"=>"C", "Đ"=>"D", "Š"=>"S", "Ž"=>"Z");
	$q10 = db_query("select o.id, o.ime, o.prezime, o.brindexa, o.jmbg, ss.akademska_godina, o.imeoca from osoba as o, student_studij as ss where ss.student=o.id order by ss.akademska_godina, o.prezime, o.ime");
	print "<table><tr><td><b>Zamger ID</b></td><td><b>Ime</b></td><td><b>Prezime</b></td><td><b>Ime oca</b></td><td><b>Novi login</b></td><td><b>Broj indexa</b></td><td><b>Stari login</b></td><td><b>JMBG</b></td></tr>\n";
	while ($r10 = db_fetch_row($q10)) {
		if ($bilo[$r10[0]]) continue;
		$bilo[$r10[0]]=1;
		$ime = preg_replace("/\W/", "", strtolower(strtr($r10[1], $trans)));
		$prezime = preg_replace("/\W/", "", strtolower(strtr($r10[2], $trans)));
		$login = substr($ime,0,1).substr($prezime,0,9);
		$count[$login]++;
		if ($count[$login]>9) {
			$login = substr($login,0,9).$count[$login];
		} else {
			$login = $login.$count[$login];
		}
//		$count[$login] = "0".$count[$login];
		$q15 = db_query("select login from auth where id=$r10[0]");
		if (db_num_rows($q15) > 0) { 
			if (db_result($q15,0,0) != $login)
				$count[db_result($q15,0,0)]++;
			continue;
		}
		if ($r10[5]==$ag) {
			if ($f==1) {
/*				$q30 = db_query("select email from osoba where id=$r10[0]");
				if (db_result($q30,0,0)=="") {
					$adresa = $login.$conf_ldap_domain;
					$q40 = db_query("update osoba set email='$adresa' where id=$r10[0]");
					print "update osoba set email='$adresa' where id=$r10[0]";
				}*/
				$q30 = db_query("select count(*) from email where osoba=$r10[0]");
				if (db_result($q30,0,0)==0) {
					$adresa = $login.$conf_ldap_domain;
					$q40 = db_query("insert into email set osoba=$r10[0], adresa='$adresa', sistemska=1");
				}
				$q19 = db_query("delete from auth where id=$r10[0] and login='$login'");
 				$q20 = db_query("insert into auth set id=$r10[0], login='$login', password='', admin=0, aktivan=1");
				//print "insert into auth set id=$r10[0], login='$login', password='', admin=0, aktivan=1<br />\n";

			} else {
				print "<tr><td>$r10[0]</td><td>$r10[1]</td><td>$r10[2]</td><td>$r10[6]</td><td>$login</td><td>$r10[3]</td><td>".gen_ldap_uid($r10[0])."<td>$r10[4]</td></tr>\n";
			}
		}
	}
	print "</table>\n";


	if ($f==0) {
		?>
		<p><?=genform("POST")?>
		<input type="hidden" name="fakatradi" value="1">
		<input type="hidden" name="akcija" value="logini">
		<input type="submit" value=" Fakat radi ">
		</form>
		<?
	}

} else {
	
	
	?>
	<p><?=genform("POST")?>
	<input type="hidden" name="fakatradi" value="0">
	<input type="hidden" name="akcija" value="logini">
	<input type="submit" value=" Kreiraj logine svim studentima prve godine ">
	</form>
	<?
}




//----------------------------------------
// Masovno kreiranje grupa na prvoj godini
//----------------------------------------

?>
<p><hr/></p>

<?

if ($_POST['akcija']=="grupe") {
	$f = intval($_POST['fakatradi']);

	$studenti=$studenti_id=array();

	$broj_grupa=intval($_REQUEST['brojgrupa']);
	$ag = intval($_REQUEST['_lv_column_akademska_godina']);

	$dodaj = "";
	foreach($_REQUEST['studij'] as $studij) {
		if ($dodaj != "") $dodaj .= "or ";
		$dodaj .= "ss.studij=$studij ";
	}
	
	$semestar=1;
	if (isset($_REQUEST['parni']) && $_REQUEST['parni'] == 1) $semestar=2;

		print "select o.id, o.ime, o.prezime, o.brindexa from osoba as o, student_studij as ss where ss.student=o.id and ss.akademska_godina=$ag and ss.semestar=$semestar and ss.ponovac=0 and ($dodaj) order by o.prezime, o.ime";
	$q10 = db_query("select o.id, o.ime, o.prezime, o.brindexa from osoba as o, student_studij as ss where ss.student=o.id and ss.akademska_godina=$ag and ss.semestar=$semestar and ss.ponovac=0 and ($dodaj) order by o.prezime, o.ime");
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
	$q20 = db_query("select distinct pk.predmet from ponudakursa as pk, studij as s, tipstudija as ts where pk.semestar=1 and pk.obavezan=1 and pk.studij=s.id and s.tipstudija=ts.id and ts.ciklus=1 and pk.akademska_godina=$ag");
	$predmeti=array();
	while ($r20 = db_fetch_row($q20)) array_push($predmeti, $r20[0]);

	$count=$grupa=1;
	if ($f==0) {
		print "<b>Grupa $grupa</b>:<br/>\n<ol>\n";
	} else {
		$labgrupe=array();
		foreach ($predmeti as $predmet) {
			$q30 = db_query("insert into labgrupa set naziv='Grupa $grupa', predmet=$predmet, akademska_godina=$ag, virtualna=0");
			$q40 = db_query("select id from labgrupa where naziv='Grupa $grupa' and predmet=$predmet and akademska_godina=$ag and virtualna=0");
			array_push($labgrupe, db_result($q40,0,0));
		}
	}
	foreach($studenti as $stud_id=>$stud_ispis) {
		if ($count>$broj_studenata_po_grupi) {
			$count=1;
			if ($broj_ekstra_grupa>0) {
				if ($f==0) {
					print "<li>$stud_ispis</li>\n";
				} else {
					foreach ($labgrupe as $lg) {
						$q50 = db_query("insert into student_labgrupa set student=$stud_id, labgrupa=$lg");
					}
				}
				$broj_ekstra_grupa--;
				$ispiso=1;
				$count=0;
			}
			$grupa++;
			if ($f==0) {
				print "</ol>\n";
				print "<b>Grupa $grupa</b>:<br/>\n<ol>\n";
			} else {
				$labgrupe=array();
				foreach ($predmeti as $predmet) {
					$q30 = db_query("insert into labgrupa set naziv='Grupa $grupa', predmet=$predmet, akademska_godina=$ag, virtualna=0");
					$q40 = db_query("select id from labgrupa where naziv='Grupa $grupa' and predmet=$predmet and akademska_godina=$ag and virtualna=0");
					array_push($labgrupe, db_result($q40,0,0));
				}
			}
		}
		if ($ispiso!=1) {
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
		<p><?=genform("POST")?>
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
	<p><?=genform("POST")?>
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





//----------------------------------------
// Masovno kreiranje tokena za ankete
//----------------------------------------

?>
<p><hr/></p>

<p><b>Masovno kreiranje tokena za ankete</b></p>

<?

if ($_POST['akcija']=="anketa_tokeni") {

	$broj_tokena = intval($_REQUEST['broj']);
	$anketa = intval($_REQUEST['anketa']);

	$studij = intval($_REQUEST['studij']);
	if ($studij!=0) {
		$godina = intval($_REQUEST['godina']);
		$dodaj_studij = "AND s.id=$studij";
	} else {
		$godina = 1;
		$dodaj_studij = "AND s.tipstudija=2"; // ETF specifično
	}
	$semestar = $godina*2 - intval($_REQUEST['zimaljeto']);

	// Aktuelna akademska godina
	$q20 = db_query("select id from akademska_godina where aktuelna=1");
	$ag = db_result($q20,0,0);

	// Subakcija za printanje tokena - redirektujem na poseban modul
	if ($_REQUEST['printaj'] === " Printaj tokene ") {
		?>
		<script language="JavaScript">
			location.href='?sta=admin/printaj_tokene&anketa=<?=$anketa?>&ag=<?=$ag?>&studij=<?=$studij?>&semestar=<?=$semestar?>';
		</script>
		<?
		return;
	}

	
	function suglasnik() {
//		$suglasnici=array('b','c','č','ć','d','dž','f','g','h','j','k','l','lj','m','n','nj','p','q','r','s','š','t','v','w','x','y','z','ž');
		$suglasnici=array('b','c','d','f','g','h','j','k','l','lj','m','n','nj','p','q','r','s','t','v','w','x','y','z');
		return $suglasnici[rand(0,count($suglasnici)-1)]; 
	}
	function samoglasnik() { 
		$samoglasnici=array('a','e','i','o','u','y','r');
		return $samoglasnici[rand(0,count($samoglasnici)-1)]; 
	}

	// Upit za predmete
	$q30 = db_query("select distinct p.id, p.naziv, pk.id from predmet as p, ponudakursa as pk, studij as s where pk.predmet=p.id and pk.studij=s.id and pk.semestar=$semestar and pk.akademska_godina=$ag $dodaj_studij");
	while ($r30 = db_fetch_row($q30)) {
		$predmet = $r30[0];
		$naziv_predmeta = $r30[1];
		$ponudakursa = $r30[2];
		print "Predmet: <b>$naziv_predmeta</b><br />\n";
		if (isset($_REQUEST['massinput'])) {
			$k = strpos($_REQUEST['massinput'], $naziv_predmeta);
			if ($k !== false) {
				$broj_tokena = intval(substr($_REQUEST['massinput'], $k + strlen($naziv_predmeta) + 1, 10));
			} else $broj_tokena = 0;
			print "-Broj studenata na predmetu: <b>$broj_tokena</b><br />\n";
		} else if (intval($_REQUEST['broj']) == 0) {
			$q35 = db_query("select count(*) from student_predmet where predmet=$ponudakursa");
			$broj_tokena = db_result($q35,0,0);
			print "Broj studenata na predmetu: <b>$broj_tokena</b><br />\n";
		}
		
		for ($i=0; $i<$broj_tokena; $i++) {
			// Generator tokena
			$token = suglasnik().samoglasnik().suglasnik().samoglasnik().suglasnik().samoglasnik().suglasnik().samoglasnik();
			// Cenzura
			if (strstr($token, "jebe")) { $i--; continue; }

			// Da li već postoji?
			$q40 = db_query("select count(*) from anketa_rezultat where unique_id='$token'");
			if (db_result($q40,0,0)>0) {
				print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$token - duplikat!!<br/>\n";
				$i--;
				continue;
			}

			// Ubacujemo u tabelu
			$q50 = db_query("insert into anketa_rezultat set anketa=$anketa, zavrsena='N', predmet=$predmet, unique_id='$token', akademska_godina=$ag, studij=$studij, semestar=$semestar");
			print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$token<br/>\n";
		}
	}

} else {
	
	
	?>
	<p><?=genform("POST")?>
	<input type="hidden" name="akcija" value="anketa_tokeni">
	Anketa: <select name="anketa">
	<?
	$q5 = db_query("select aa.id, aa.naziv, ag.naziv from anketa_anketa as aa, akademska_godina as ag where aa.akademska_godina=ag.id order by aa.id desc"); // Daj ankete počevši od posljenje kreirane
	while ($r5 = db_fetch_row($q5)) {
		?>
		<option value="<?=$r5[0]?>"><?=$r5[1]?> (<?=$r5[2]?>)</option>
		<?
	}
	?>
	</select><br />

	Godina studija: <input type="text" name="godina" size="5"><br/>
	Studij: <select name="studij"><option value="0">Svi (PGS)</option>
	<?
	$q10 = db_query("select id, naziv from studij where moguc_upis=1 order by tipstudija,naziv");
	while ($r10 = db_fetch_row($q10)) {
		?>
		<option value="<?=$r10[0]?>"><?=$r10[1]?></option>
		<?
	}
	?>
	</select><br />
	Semestar: <select name="zimaljeto"><option value="1">Zimski</option><option value="0">Ljetnji</option></select><br />
	Broj tokena: <input type="text" name="broj" size="5"><br/>
	Broj studenata po predmetu:<br>
	<textarea name="massinput" cols="50" rows="10"><?
	if (strlen($_POST['nazad'])>1) print $_POST['massinput'];
	?></textarea><br/>
	
	<input type="submit" value=" Generiši tokene za popunjavanje ankete ">
	<input type="submit" name="printaj" value=" Printaj tokene ">
	</form>
	<?

}










//----------------------------------------
// Masovni update komponenti
//----------------------------------------

?>
<p><hr/></p>

<p><b>Masovni update komponenti</b></p>

<?

if ($_POST['akcija']=="update_komponenti") {

	$pk = intval($_REQUEST['pk']);
	$komponenta = intval($_REQUEST['komponenta']);
	$f = intval($_POST['fakatradi']);

	$q10 = db_query("select p.naziv, s.naziv, pk.semestar, pk.obavezan, ag.naziv, agp.tippredmeta, tp.naziv from ponudakursa as pk, predmet as p, studij as s, akademska_godina as ag, akademska_godina_predmet as agp, tippredmeta as tp where pk.id=$pk and pk.predmet=p.id and pk.studij=s.id and pk.akademska_godina=ag.id and agp.predmet=p.id and agp.akademska_godina=ag.id and agp.tippredmeta=tp.id");
	if (db_num_rows($q10)<1) {
		niceerror("Nepostojeća ponudakursa");
		return;
	}
	print "Predmet: ".db_result($q10,0,0)."<br>Studij: ".db_result($q10,0,1)."<br>Semestar: ".db_result($q10,0,2)."<br>Obavezan: ".db_result($q10,0,3)."<br>Akademska godina: ".db_result($q10,0,4)."<br>Tip predmeta: ".db_result($q10,0,6)."<br><br>\n";
	$tippredmeta = db_result($q10,0,5);

	if ($komponenta != 0) {
		$q20 = db_query("select naziv from komponenta where id=$komponenta");
		if (db_num_rows($q20)<1) {
			niceerror("Nepostojeća komponenta");
			return;
		}
		print "<b>Komponenta:  ".db_result($q20,0,0)."</b><br><br>\n";
		
		$q30 = db_query("select count(*) from tippredmeta_komponenta where tippredmeta=$tippredmeta and komponenta=$komponenta");
		if (db_result($q30,0,0)<1) {
			niceerror("Komponenta nije pridružena ovom tipu predmeta");
			return;
		}
	} else {
		print "<b>Komponenta:  SVE</b><br><br>\n";
	}


	$q40 = db_query("select o.id, o.ime, o.prezime, o.brindexa from student_predmet as sp, osoba as o where sp.predmet=$pk and sp.student=o.id");
	if (db_num_rows($q40)<1) {
		niceerror("Nema studenata na ovoj ponudi kursa");
		return;
	}
	if ($f) print "Ažuriram ".db_num_rows($q40)." studenata<br><br>";
	while ($r40 = db_fetch_row($q40)) {
		if ($f) {
			update_komponente($r40[0], $pk, $komponenta);
		} else {
			print "-- $r40[1] $r40[2] ($r40[3])<br>\n";
		}
	}

	if (!$f) {
		?>
		<p><?=genform("POST")?>
		<input type="hidden" name="fakatradi" value="1">
		<input type="hidden" name="akcija" value="update_komponenti">
		<input type="submit" value=" Fakat radi ">
		</form>
		<?
	}

} else {
	
	
	?>
	<p><?=genform("POST")?>
	<input type="hidden" name="akcija" value="update_komponenti">
	Ponudakursa: <input type="text" name="pk" value=""><br>
	Komponenta: <input type="text" name="komponenta" value=""><br>
	<input type="submit" value=" Update komponenti ">
	</form>
	<?

}










//----------------------------------------
// Brisanje osobe
//----------------------------------------

?>
<p><hr/></p>

<p><b>Brisanje osobe</b></p>

<?

if ($_POST['akcija']=="brisanje_osobe" && check_csrf_token()) {
	$osoba = intval($_REQUEST['osoba']);
	$f = intval($_REQUEST['fakatradi']);

	$q100 = db_query("select predmet, akademska_godina, angazman_status from angazman where osoba=$osoba");
	if ($f)
		$q100a = db_query("delete from angazman where osoba=$osoba");
	else
		while ($r100 = db_fetch_row($q100)) {
			print "Angazman: ";
			$q101 = db_query("select naziv from predmet where id=$r100[0]");
			if (db_num_rows($q101)>0)
				print db_result($q101,0,0)." ";
			else
				print "Predmet: $r100[0] ";
			$q102 = db_query("select naziv from akademska_godina where id=$r100[1]");
			if (db_num_rows($q102)>0)
				print db_result($q102,0,0)." ";
			else
				print "Ag: $r100[1] ";
			$q103 = db_query("select naziv from angazman_status where id=$r100[2]");
			if (db_num_rows($q103)>0)
				print db_result($q103,0,0)." ";
			else
				print "Status: $r100[2] ";
			print "<br>";
		}

	$q105 = db_query("select login from auth where id=$osoba");
	if ($f)
		$q105a = db_query("delete from auth where id=$osoba");
	else
		print "Login ".db_result($q105,0,0)."<br>\n";

	$q120 = db_query("select vrijeme, prvi_post from bb_tema where osoba=$osoba");
	if ($f)
		$q120a = db_query("delete from bb_tema where osoba=$osoba");
	else
		while ($r120 = db_fetch_row($q120)) {
			print "BB tema: $r120[0] ";
			$q121 = db_query("select naslov from bb_post where id=$r120[1]");
			if (db_num_rows($q121)>0)
				print db_result($q121,0,0)." ";
			else
				print "Post: $r120[1] ";
			print "<br>";
		}

	$q110 = db_query("select naslov, vrijeme, tema from bb_post where osoba=$osoba");
	if ($f)
		$q110a = db_query("delete from bb_post where osoba=$osoba");
	else
		while ($r110 = db_fetch_row($q110)) {
			print "BB post: '$r110[0]' $r110[1] $r110[2]<br>";
		}
//bl_clanak

	$q125 = db_query("select datum, vrijeme, labgrupa from cas where nastavnik=$osoba");
	if ($f)
		$q125a = db_query("UPDATE cas SET nastavnik=0 where nastavnik=$osoba");
	else
		while ($r125 = db_fetch_row($q125)) {
			print "Čas ($r125[0] $r125[1]), grupa $r125[2]<br>";
		}

	$q130 = db_query("select adresa from email where osoba=$osoba");
	if ($f)
		$q130a = db_query("delete from email where osoba=$osoba");
	else
		while ($r130 = db_fetch_row($q130)) {
			print "Email: '$r130[0]'<br>";
		}

	$q140 = db_query("select ispit, ocjena from ispitocjene where student=$osoba");
	if ($f)
		$q140a = db_query("delete from ispitocjene where student=$osoba");
	else
		while ($r140 = db_fetch_row($q140)) {
			print "Ispitocjene: ";
			$q141 = db_query("select p.naziv, ag.naziv, k.gui_naziv from predmet as p, akademska_godina as ag, komponenta as k, ispit as i where i.id=$r140[0] and i.predmet=p.id and i.akademska_godina=ag.id and i.komponenta=k.id");
			if (db_num_rows($q141)>0)
				print db_result($q141,0,0)." ".db_result($q141,0,1)." ".db_result($q141,0,2)." ";
			else
				print "Ispit: $r140[0] ";
			print "Ocjena: $r140[1]<br>";
		}
//izbor
//kolizija
//komentar
//komponentebodovi
//konacna_ocjena
//kviz_student

	$q150 = db_query("select vrijeme, dogadjaj, nivo from log where userid=$osoba");
	if ($f)
		$q150a = db_query("delete from log where userid=$osoba");
	else
		while ($r150 = db_fetch_row($q150)) {
			print "Log: $r150[0] $r150[1] $r150[2]<br>";
		}

	$q160 = db_query("select vrijeme, modul, dogadjaj from log2 where userid=$osoba");
	if ($f)
		$q160a = db_query("delete from log2 where userid=$osoba");
	else
		while ($r160 = db_fetch_row($q160)) {
			print "Log2: $r150[0] $r150[1] $r150[2]<br>";
		}
//nastavnik_predmet
//odluka
//ogranicenje
//poruka
//preference
	$q170 = db_query("select prijemni_termin, sifra, jezik from prijemni_obrazac where osoba=$osoba");
	if ($f)
		$q170a = db_query("delete from prijemni_obrazac where osoba=$osoba");
	else
		while ($r170 = db_fetch_row($q170)) {
			print "Prijemni_obrazac: ";
			$q171 = db_query("select ag.naziv, pt.datum, pt.ciklus_studija from akademska_godina as ag, prijemni_termin as pt where pt.id=$r170[0] and pt.akademska_godina=ag.id");
			if (db_num_rows($q171)>0)
				print db_result($q171,0,0)." ".db_result($q171,0,1)." ".db_result($q171,0,2)." ";
			else
				print "Prijemni termin: $r170[0] ";
			print "$r170[1] $r170[2]<br>";
		}

	$q180 = db_query("select prijemni_termin, broj_dosjea, izasao, rezultat from prijemni_prijava where osoba=$osoba");
	if ($f)
		$q180a = db_query("delete from prijemni_prijava where osoba=$osoba");
	else
		while ($r180 = db_fetch_row($q180)) {
			print "Prijemni_prijava: ";
			$q181 = db_query("select ag.naziv, pt.datum, pt.ciklus_studija from akademska_godina as ag, prijemni_termin as pt where pt.id=$r180[0] and pt.akademska_godina=ag.id");
			if (db_num_rows($q181)>0)
				print db_result($q181,0,0)." ".db_result($q181,0,1)." ".db_result($q181,0,2)." ";
			else
				print "Prijemni termin: $r180[0] ";
			print "$r180[1] $r180[2] $r180[3]<br>";
		}
//prisustvo

	$q190 = db_query("select privilegija from privilegije where osoba=$osoba");
	if ($f)
		$q190a = db_query("delete from privilegije where osoba=$osoba");
	else
		while ($r190 = db_fetch_row($q190)) {
			print "Privilegije: $r190[0]<br>";
		}
//projekat_file
//projekat_link
//projekat_rss
//promjena_odsjeka
//promjena_podataka
//prosliciklus_ocjene
//prosliciklus_uspjeh
//rss
//septembar

	$q200 = db_query("select razred, redni_broj, ocjena from srednja_ocjene where osoba=$osoba");
	if ($f)
		$q200a = db_query("delete from srednja_ocjene where osoba=$osoba");
	else
		while ($r200 = db_fetch_row($q200)) {
			print "Srednja_ocjene: $r200[0] $r200[1] $r200[2]<br>";
		}

//student_ispit_termin
//student_labgrupa

	$q210 = db_query("select predmet from student_predmet where student=$osoba");
	if ($f)
		$q210a = db_query("delete from student_predmet where student=$osoba");
	else
		while ($r210 = db_fetch_row($q210)) {
			print "Student_predmet: ";
			$q211 = db_query("select p.naziv, ag.naziv, s.naziv, pk.semestar, pk.obavezan from akademska_godina as ag, predmet as p, studij as s, ponudakursa as pk where pk.id=$r210[0] and pk.akademska_godina=ag.id and pk.predmet=p.id and pk.studij=s.id");
			if (db_num_rows($q211)>0)
				print db_result($q211,0,0)." ".db_result($q211,0,1)." ".db_result($q211,0,2)." "." ".db_result($q211,0,3)." "." ".db_result($q211,0,4)." ";
			else
				print "Ponudakursa: $r210[0] ";
			print "<br>";
		}
//student_projekat

	$q220 = db_query("select studij, semestar, akademska_godina from student_studij where student=$osoba");
	if ($f)
		$q220a = db_query("delete from student_studij where student=$osoba");
	else
		while ($r220 = db_fetch_row($q220)) {
			print "Student_studij: ";
			$q221 = db_query("select naziv from studij where id=$r220[0]");
			if (db_num_rows($q221)>0)
				print db_result($q221,0,0)." ";
			else
				print "Studij: $r220[0] ";
			print "$r220[1] ";
			$q222 = db_query("select naziv from akademska_godina where id=$r220[2]");
			if (db_num_rows($q222)>0)
				print db_result($q222,0,0)." ";
			else
				print "A.g.: $r220[2] ";
			print "<br>";
		}

//ugovoroucenju

	$q230 = db_query("select srednja_skola, godina from uspjeh_u_srednjoj where osoba=$osoba");
	if ($f)
		$q230a = db_query("delete from uspjeh_u_srednjoj where osoba=$osoba");
	else
		while ($r230 = db_fetch_row($q230)) {
			print "Uspjeh_u_srednjoj: ";
			$q231 = db_query("select naziv from srednja_skola where id=$r230[0]");
			if (db_num_rows($q231)>0)
				print db_result($q231,0,0)." ";
			else
				print "Srednja skola: $r230[0] ";
			$q232 = db_query("select naziv from akademska_godina where id=$r230[1]");
			if (db_num_rows($q232)>0)
				print db_result($q232,0,0)." ";
			else
				print "a.g.: $r230[1] ";
			print "<br>";
		}
//zadatak
//zavrsni_*


	if ($f)
		$q500 = db_query("delete from osoba where id=$osoba");


	if (!$f) {
		?>
		<p><?=genform("POST")?>
		<input type="hidden" name="fakatradi" value="1">
		<input type="hidden" name="akcija" value="brisanje_osobe">
		<input type="submit" value=" Fakat radi ">
		</form>
		<?
	}
	else {
		nicemessage("Osoba sa IDom $osoba obrisana.");
		print "<a href=\"?sta=admin/misc\">Nazad</a>";
	}

} else {
	
	
	?>
	<p><?=genform("POST")?>
	<input type="hidden" name="akcija" value="brisanje_osobe">
	Unesite ID osobe: <input type="text" name="osoba" value=""><br>
	<input type="submit" value=" Brisanje osobe ">
	</form>
	<?

}












//----------------------------------------
// Spajanje osoba
//----------------------------------------

?>
<p><hr/></p>

<p><b>Spajanje osoba</b></p>

<?

if ($_POST['akcija']=="spajanje_osoba" && check_csrf_token()) {
	$osoba_A = intval($_REQUEST['osoba_A']);
	$osoba_B = intval($_REQUEST['osoba_B']);
	$f = intval($_REQUEST['fakatradi']);

	if (!$f) {
		// Da ispišemo šta će se raditi:
		$q90 = db_query("select ime, prezime from osoba where id=$osoba_A");
		$r90 = db_fetch_row($q90);
		$q91 = db_query("select ime, prezime from osoba where id=$osoba_B");
		$r91 = db_fetch_row($q91);

		print "<p>Podaci osobe: $r91[0] $r91[1] (ID: $osoba_B) će biti spojeni na osobu $r90[0] $r90[1] (ID: $osoba_A)</p>";
	}

	$q100 = db_query("select predmet, akademska_godina, angazman_status from angazman where osoba=$osoba_B");
	if ($f)
		$q100a = db_query("UPDATE angazman SET osoba=$osoba_A where osoba=$osoba_B");
	else
		while ($r100 = db_fetch_row($q100)) {
			print "Angazman: ";
			$q101 = db_query("select naziv from predmet where id=$r100[0]");
			if (db_num_rows($q101)>0)
				print db_result($q101,0,0)." ";
			else
				print "Predmet: $r100[0] ";
			$q102 = db_query("select naziv from akademska_godina where id=$r100[1]");
			if (db_num_rows($q102)>0)
				print db_result($q102,0,0)." ";
			else
				print "Ag: $r100[1] ";
			$q103 = db_query("select naziv from angazman_status where id=$r100[2]");
			if (db_num_rows($q103)>0)
				print db_result($q103,0,0)." ";
			else
				print "Status: $r100[2] ";
			print "<br>";
		}



	$stari_logini = $logini_dodati = array();
	$q105 = db_query("select login from auth where id=$osoba_A");
	while($r105 = db_fetch_row($q105)) array_push($stari_logini, $r105[0]);

	$q106 = db_query("select login from auth where id=$osoba_B");
	while ($r106 = db_fetch_row($q106)) {
		if (!in_array($r106[0], $stari_logini))
			array_push($logini_dodati, $r131[0]);
	}
	
	if ($f) {
		$q130a = db_query("delete from auth where id=$osoba_B");
	} else
		foreach ($logini_dodati as $login) 
			print "Dodati login: $login<br>";


	$q120 = db_query("select vrijeme, prvi_post from bb_tema where osoba=$osoba_B");
	if ($f)
		$q120a = db_query("UPDATE bb_tema  SET osoba=$osoba_A where osoba=$osoba_B");
	else
		while ($r120 = db_fetch_row($q120)) {
			print "BB tema: $r120[0] ";
			$q121 = db_query("select naslov from bb_post where id=$r120[1]");
			if (db_num_rows($q121)>0)
				print db_result($q121,0,0)." ";
			else
				print "Post: $r120[1] ";
			print "<br>";
		}

	$q110 = db_query("select naslov, vrijeme, tema from bb_post where osoba=$osoba_B");
	if ($f)
		$q110a = db_query("UPDATE bb_post  SET osoba=$osoba_A where osoba=$osoba_B");
	else
		while ($r110 = db_fetch_row($q110)) {
			print "BB post: '$r110[0]' $r110[1] $r110[2]<br>";
		}

//bl_clanak


	$q125 = db_query("select datum, vrijeme, labgrupa from cas where nastavnik=$osoba_B");
	if ($f)
		$q125a = db_query("UPDATE cas SET nastavnik=$osoba_A where nastavnik=$osoba_B");
	else
		while ($r125 = db_fetch_row($q125)) {
			print "Čas ($r125[0] $r125[1]), grupa $r125[2]<br>";
		}

	$stari_mailovi = $mailovi_dodati = array();
	$q130 = db_query("select adresa from email where osoba=$osoba_A");
	while($r130 = db_fetch_row($q130)) array_push($stari_mailovi, $r130[0]);

	$q131 = db_query("select adresa from email where osoba=$osoba_B");
	while ($r131 = db_fetch_row($q131)) {
		if (!in_array($r131[0], $stari_mailovi))
			array_push($mailovi_dodati, $r131[0]);
	}
	
	if ($f) {
		foreach ($mailovi_dodati as $mail) 
			$q130b = db_query("INSERT INTO email SET osoba=$osoba_A, adresa='$mail', sistemska=0");
		$q130a = db_query("delete from email where osoba=$osoba_B");
	} else
		foreach ($mailovi_dodati as $mail) 
			print "Dodati mail: $mail<br>";


	$q140 = db_query("select ispit, ocjena from ispitocjene where student=$osoba_B");
	if ($f)
		$q140a = db_query("UPDATE ispitocjene SET student=$osoba_A where student=$osoba_B");
	else
		while ($r140 = db_fetch_row($q140)) {
			print "Ispitocjene: ";
			$q141 = db_query("select p.naziv, ag.naziv, k.gui_naziv from predmet as p, akademska_godina as ag, komponenta as k, ispit as i where i.id=$r140[0] and i.predmet=p.id and i.akademska_godina=ag.id and i.komponenta=k.id");
			if (db_num_rows($q141)>0)
				print db_result($q141,0,0)." ".db_result($q141,0,1)." ".db_result($q141,0,2)." ";
			else
				print "Ispit: $r140[0] ";
			print "Ocjena: $r140[1]<br>";
		}
//izbor
//kolizija

	$q145 = db_query("select predmet, komentar from komentar where student=$osoba_B");
	if ($f)
		$q145a = db_query("UPDATE komentar SET student=$osoba_A where student=$osoba_B");
	else
		while ($r145 = db_fetch_row($q145)) {
			$q146 = db_query("select naziv from predmet where id=$r145[0]");
			if (db_num_rows($q146)>0)
				print "Komentar na predmetu ".db_result($q146,0,0).": $r145[1]<br>";
			else
				print "Komentar na predmetu ".$r145[0].": $r145[1]<br>";
		}


	$q147 = db_query("select predmet, komponenta, bodovi from komponentebodovi where student=$osoba_B");
	if ($f)
		$q147a = db_query("UPDATE komponentebodovi SET student=$osoba_A where student=$osoba_B");
	else
		while ($r147 = db_fetch_row($q147)) {
			$q148 = db_query("select naziv from predmet where id=$r147[0]");
			if (db_num_rows($q148)>0)
				print "Komponentebodovi: Predmet ".db_result($q148,0,0).", komponenta $r147[1], bodovi $r147[2]<br>";
			else
				print "Komponentebodovi: Predmet $r147[0], komponenta $r147[1], bodovi $r147[2]<br>";
		}


	$q149 = db_query("select predmet, akademska_godina, ocjena from konacna_ocjena where student=$osoba_B");
	if ($f)
		$q149a = db_query("UPDATE konacna_ocjena SET student=$osoba_A where student=$osoba_B");
	else
		while ($r149 = db_fetch_row($q149)) {
			$q150 = db_query("select naziv from predmet where id=$r149[0]");
			if (db_num_rows($q150)>0)
				print "Konačna ocjena $r149[2]: Predmet ".db_result($q150,0,0).", a.g. $r149[1]<br>";
			else
				print "Konačna ocjena $r149[2]: Predmet $r149[0], a.g. $r149[1]<br>";
		}

//kviz_student
/*
	$q150 = db_query("select vrijeme, dogadjaj, nivo from log where userid=$osoba_B");
	if ($f)
		$q150a = db_query("UPDATE log SET userid=$osoba_A where userid=$osoba_B");
	else
		while ($r150 = db_fetch_row($q150)) {
			print "Log: $r150[0] $r150[1] $r150[2]<br>";
		}
*/
	$q160 = db_query("select vrijeme, modul, dogadjaj from log2 where userid=$osoba_B");
	if ($f)
		$q160a = db_query("UPDATE log2 SET userid=$osoba_A where userid=$osoba_B");
	else
		while ($r160 = db_fetch_row($q160)) {
			print "Log2: $r150[0] $r150[1] $r150[2]<br>";
		}

//nastavnik_predmet
//odluka
//ogranicenje
//poruka
//preference

	$q170 = db_query("select prijemni_termin, sifra, jezik from prijemni_obrazac where osoba=$osoba_B");
	if ($f)
		$q170a = db_query("UPDATE prijemni_obrazac SET osoba=$osoba_A where osoba=$osoba_B");
	else
		while ($r170 = db_fetch_row($q170)) {
			print "Prijemni_obrazac: ";
			$q171 = db_query("select ag.naziv, pt.datum, pt.ciklus_studija from akademska_godina as ag, prijemni_termin as pt where pt.id=$r170[0] and pt.akademska_godina=ag.id");
			if (db_num_rows($q171)>0)
				print db_result($q171,0,0)." ".db_result($q171,0,1)." ".db_result($q171,0,2)." ";
			else
				print "Prijemni termin: $r170[0] ";
			print "$r170[1] $r170[2]<br>";
		}

	$q180 = db_query("select prijemni_termin, broj_dosjea, izasao, rezultat from prijemni_prijava where osoba=$osoba_B");
	if ($f)
		$q180a = db_query("UPDATE prijemni_prijava SET osoba=$osoba_A where osoba=$osoba_B");
	else
		while ($r180 = db_fetch_row($q180)) {
			print "Prijemni_prijava: ";
			$q181 = db_query("select ag.naziv, pt.datum, pt.ciklus_studija from akademska_godina as ag, prijemni_termin as pt where pt.id=$r180[0] and pt.akademska_godina=ag.id");
			if (db_num_rows($q181)>0)
				print db_result($q181,0,0)." ".db_result($q181,0,1)." ".db_result($q181,0,2)." ";
			else
				print "Prijemni termin: $r180[0] ";
			print "$r180[1] $r180[2] $r180[3]<br>";
		}
//prisustvo

	$stare_priv = $priv_dodate = array();
	$q190 = db_query("select privilegija from privilegije where osoba=$osoba_A");
	while($r190 = db_fetch_row($q190)) array_push($stare_priv, $r190[0]);

	$q191 = db_query("select privilegija from privilegije where osoba=$osoba_B");
	while ($r191 = db_fetch_row($q191)) {
		if (!in_array($r191[0], $stare_priv))
			array_push($priv_dodate, $r191[0]);
	}
	
	if ($f) {
		foreach ($priv_dodate as $priv) 
			$q130b = db_query("INSERT INTO privilegije SET osoba=$osoba_A, privilegija='$priv'");
		$q130a = db_query("DELETE FROM privilegije where osoba=$osoba_B");
	} else
		foreach ($priv_dodate as $priv) 
			print "Dodata privilegija: $priv<br>";

//projekat_file
//projekat_link
//projekat_rss
//promjena_odsjeka
//promjena_podataka


	$q195 = db_query("select ocjena from prosliciklus_ocjene where osoba=$osoba_B");
	if ($f)
		$q195a = db_query("UPDATE prosliciklus_ocjene SET osoba=$osoba_A where osoba=$osoba_B");
	else {
		print "Ocjene sa prošlog ciklusa: ";
		while ($r195 = db_fetch_row($q195)) {
			print $r195[0]. ", ";
		}
		print "<br>\n";
	}


	$q197 = db_query("select count(*) from prosliciklus_uspjeh where osoba=$osoba_B");
	if ($f)
		$q195a = db_query("UPDATE prosliciklus_uspjeh SET osoba=$osoba_A where osoba=$osoba_B");
	else {
		if (db_result($q197,0,0)) {
			print "Podaci o uspjehu na prošlom ciklusu.<br>\n";
		}
	}

//rss
//septembar

	$q200 = db_query("select razred, redni_broj, ocjena from srednja_ocjene where osoba=$osoba_B");
	if ($f)
		$q200a = db_query("UPDATE srednja_ocjene SET osoba=$osoba_A where osoba=$osoba_B");
	else
		while ($r200 = db_fetch_row($q200)) {
			print "Srednja_ocjene: $r200[0] $r200[1] $r200[2]<br>";
		}

//student_ispit_termin

	$stare_grupe = $grupe_dodate = array();
	$q205 = db_query("select labgrupa from student_labgrupa where student=$osoba_A");
	while($r205 = db_fetch_row($q205)) array_push($stare_grupe, $r205[0]);

	$q206 = db_query("select labgrupa from student_labgrupa where student=$osoba_B");
	while ($r206 = db_fetch_row($q206)) {
		if (!in_array($r206[0], $stare_grupe))
			array_push($grupe_dodate, $r206[0]);
	}
	
	if ($f) {
		foreach ($grupe_dodate as $grupa) 
			$q130b = db_query("INSERT INTO student_labgrupa SET student=$osoba_A, labgrupa=$grupa");
		$q130a = db_query("delete from student_labgrupa where student=$osoba_B");
	} else
		foreach ($grupe_dodate as $grupa) 
			print "Dodata labgrupa: $grupa<br>";


	$q210 = db_query("select predmet from student_predmet where student=$osoba_B");
	if ($f)
		$q210a = db_query("UPDATE student_predmet SET student=$osoba_A where student=$osoba_B");
	else
		while ($r210 = db_fetch_row($q210)) {
			print "Student_predmet: ";
			$q211 = db_query("select p.naziv, ag.naziv, s.naziv, pk.semestar, pk.obavezan from akademska_godina as ag, predmet as p, studij as s, ponudakursa as pk where pk.id=$r210[0] and pk.akademska_godina=ag.id and pk.predmet=p.id and pk.studij=s.id");
			if (db_num_rows($q211)>0)
				print db_result($q211,0,0)." ".db_result($q211,0,1)." ".db_result($q211,0,2)." "." ".db_result($q211,0,3)." "." ".db_result($q211,0,4)." ";
			else
				print "Ponudakursa: $r210[0] ";
			print "<br>";
		}

//student_projekat

	$q220 = db_query("select studij, semestar, akademska_godina from student_studij where student=$osoba_B");
	if ($f)
		$q220a = db_query("UPDATE student_studij SET student=$osoba_A where student=$osoba_B");
	else
		while ($r220 = db_fetch_row($q220)) {
			print "Student_studij: ";
			$q221 = db_query("select naziv from studij where id=$r220[0]");
			if (db_num_rows($q221)>0)
				print db_result($q221,0,0)." ";
			else
				print "Studij: $r220[0] ";
			print "$r220[1] ";
			$q222 = db_query("select naziv from akademska_godina where id=$r220[2]");
			if (db_num_rows($q222)>0)
				print db_result($q222,0,0)." ";
			else
				print "A.g.: $r220[2] ";
			print "<br>";
		}

//ugovoroucenju

	$q230 = db_query("select srednja_skola, godina from uspjeh_u_srednjoj where osoba=$osoba_B");
	if ($f)
		$q230a = db_query("UPDATE uspjeh_u_srednjoj SET osoba=$osoba_A where osoba=$osoba_B");
	else
		while ($r230 = db_fetch_row($q230)) {
			print "Uspjeh_u_srednjoj: ";
			$q231 = db_query("select naziv from srednja_skola where id=$r230[0]");
			if (db_num_rows($q231)>0)
				print db_result($q231,0,0)." ";
			else
				print "Srednja skola: $r230[0] ";
			$q232 = db_query("select naziv from akademska_godina where id=$r230[1]");
			if (db_num_rows($q232)>0)
				print db_result($q232,0,0)." ";
			else
				print "a.g.: $r230[1] ";
			print "<br>";
		}
//zadatak
//zavrsni_*


// Lični podaci

	print "<br><b>Lični podaci:</b><br>\n";
	$q300 = db_query("SELECT ime, prezime, imeoca, prezimeoca, imemajke, prezimemajke, spol, brindexa, datum_rodjenja, mjesto_rodjenja, nacionalnost, drzavljanstvo, boracke_kategorije, jmbg, adresa, adresa_mjesto, telefon, kanton, strucni_stepen, naucni_stepen FROM osoba where id=$osoba_A");
	$r300 = db_fetch_assoc($q300);
	$q310 = db_query("SELECT ime, prezime, imeoca, prezimeoca, imemajke, prezimemajke, spol, brindexa, datum_rodjenja, mjesto_rodjenja, nacionalnost, drzavljanstvo, boracke_kategorije, jmbg, adresa, adresa_mjesto, telefon, kanton, strucni_stepen, naucni_stepen FROM osoba where id=$osoba_B");
	$r310 = db_fetch_assoc($q310);
	$sql = "";
	foreach ($r300 as $key=>$value) {
		if ($r310[$key] !== "" && $r310[$key] !== 0 && $r310[$key] != $value) {
			if ($value === "" || $value === 0 || $value === "0") {
				if (!$f)
					print "Ključ $key dodati ".$r310[$key]."<br>\n";
			} else {
				if (!$f)
					print "Ključ $key bio $value sada ".$r310[$key]."<br>\n";
			}
			if ($sql != "") $sql .= ", ";
			$sql .= "$key='".$r310[$key]."'";
		}
	}
	if ($f && $sql != "") {
		$q320 = db_query("UPDATE osoba SET $sql WHERE id=$osoba_A");
	}
	if (!$f && $sql == "") {
		print "sve ok.<br>\n";
	}


	if ($f)
		$q500 = db_query("delete from osoba where id=$osoba_B");


	if (!$f) {
		?>
		<p><?=genform("POST")?>
		<input type="hidden" name="fakatradi" value="1">
		<input type="hidden" name="akcija" value="spajanje_osoba">
		<input type="submit" value=" Fakat radi ">
		</form>
		<?
	}
	else {
		nicemessage("Spojene osobe sa IDom $osoba_A i $osoba_B obrisana.");
		print "<a href=\"?sta=admin/misc\">Nazad</a>";
	}

} else {
	
	?>
	<p><?=genform("POST")?>
	<input type="hidden" name="akcija" value="spajanje_osoba">
	Unesite ID osobe A: <input type="text" name="osoba_A" value=""><br>
	Unesite ID osobe B: <input type="text" name="osoba_B" value=""><br>
	<input type="submit" value=" Spajanje osoba ">
	</form>
	<?

}












//----------------------------------------
// Zamijeni ponudu kursa
//----------------------------------------

?>
<p><hr/></p>

<p><b>Zamijeni ponudukursa</b></p>

<?


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
		niceerror("Osoba ne sluša ponudukursa $stari_predmet");
		return;
	}
	if (db_result($q20,0,0) > 0) {
		niceerror("Osoba već sluša ponudukursa $novi_predmet");
		return;
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
				print "!! $r30[1] bodova na ispitu $r30[0] - nisam pronašao odgovarajući ispit (datum $r30[2], komponenta $r30[3]), promijeniti ispit manuelno<br>";
			}
		}


		// LABGRUPE (CASOVI I KOMENTARI - samo ih brišemo :( )
		$q40 = db_query("SELECT l.id, l.virtualna, l.naziv FROM student_labgrupa as sl, labgrupa as l WHERE sl.student=$osoba AND sl.labgrupa=l.id AND l.predmet=$stari_pid AND l.akademska_godina=$stari_ag");
		while ($r40 = db_fetch_row($q40)) {
			if ($r40[1] == 1) {
				$q45 = db_query("SELECT id FROM labgrupa WHERE predmet=$novi_pid AND akademska_godina=$novi_ag AND virtualna=1");
				if (db_num_rows($q45)>0) {
					$nova_lg = db_result($q45,0,0);
					if ($ispis)
						print "-- Ispisujem studenta sa virtuelne labgrupe $r40[2] ($r40[0]) i upisujem u istoimenu v.lg. $nova_lg<br>\n";
					else {
						ispis_studenta_sa_labgrupe($osoba,$r40[0]);
						$q47 = db_query("INSERT INTO student_labgrupa SET student=$osoba, labgrupa=$nova_lg");
					}
				} else {
					if ($ispis)
						print "!! Predmet $novi_predmet nema virtuelne labgrupe! Student će biti ispisan iz v. lg. $r40[2]<br>";
					else
						ispis_studenta_sa_labgrupe($osoba,$r40[0]);
				}
			} else {
				$novi_naziv = db_escape_string($r40[2]);
				$nova_lg = db_get("SELECT id FROM labgrupa WHERE predmet=$novi_pid AND akademska_godina=$novi_ag AND naziv='$novi_naziv'");
				if ($ispis)
					print "-- Ispisujem studenta sa labgrupe $r40[2] ";
				else
					ispis_studenta_sa_labgrupe($osoba,$r40[0]);
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
		}
	} else {
		if ($ispis) print "Isti ID predmeta, ne mijenjam ispite, labgrupe i prisustvo.<br>\n";
	}


	// KOMPONENTEBODOVI
	$q50 = db_query("SELECT kb.komponenta, kb.bodovi, k.naziv FROM komponentebodovi as kb, komponenta as k WHERE kb.student=$osoba AND kb.predmet=$stari_pk AND kb.komponenta=k.id");
	while ($r50 = db_fetch_row($q50)) {
		$q55 = db_query("SELECT COUNT(*) FROM akademska_godina_predmet as agp, tippredmeta_komponenta as tpk WHERE agp.akademska_godina=$novi_ag AND agp.predmet=$novi_pid AND agp.tippredmeta=tpk.tippredmeta AND tpk.komponenta=$r50[0]");
		if (db_result($q55,0,0) == 0) {
			print "!! Predmet $novi_predmet nema komponentu $r50[2] ($r50[0])! Manuelno editujte bazu<br>\n";
		} else {
			if ($ispis)
				print "-- Prenosim bodove za komponentu $r50[2] ($r50[0])<br>\n";
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
		}
	} else {
		if ($ispis) print "Isti ID predmeta, ne mijenjam konačnu ocjenu.<br>\n";
	}


	// KVIZ - nije implementirano

	// PROJEKAT, STUDENT_PROJEKAT - nije implementirano

	// STUDENT_ISPIT_TERMIN - nije implementirano

	// ZADACA
	if ($stari_pid != $novi_pid) {
		$q70 = db_query("SELECT z.id, z.naziv FROM zadaca as z, zadatak as zk WHERE zk.student=$osoba and zk.zadaca=z.id AND z.predmet=$stari_pid AND z.akademska_godina=$stari_ag");
		while ($r70 = db_fetch_row($q70)) {
			$q75 = db_query("SELECT id FROM zadaca WHERE naziv='$r70[1]' AND predmet=$novi_pid AND akademska_godina=$novi_ag");
			if (db_num_rows($q75)==0)
				print "!! Nisam pronašao odgovarajuću zadaću za &quot;$r70[1]&quot; ($r70[0]). Migrirajte ručno<br>\n";
			else {
				$nova_zadaca = db_result($q75,0,0);
				if ($ispis)
					print "-- Migriram sve zadatke za zadaću $r70[1] ($r70[0]) na $nova_zadaca<br>\n";
				else
					$q78 = db_query("UPDATE zadatak SET zadaca=$nova_zadaca WHERE student=$osoba AND zadaca=$r70[0]");
			}
		}
	} else {
		if ($ispis) print "Isti ID predmeta, ne mijenjam zadaće.<br>\n";
	}

	// Konačno: STUDENT_PREDMET
	if ($ispis)
		print "-- Prepisujem u novu ponudukursa<br>\n<hr><br>\n";
	else {
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
	<p><?=genform("POST")?>
	<input type="hidden" name="akcija" value="zamijeni_pk">
	Unesite ID osobe: <input type="text" name="osoba" value=""><br>
	Stara ponudakursa: <input type="text" name="old_pk" value=""><br>
	Nova ponudakursa: <input type="text" name="new_pk" value=""><br>
	<input type="submit" value=" Migriraj podatke na drugu ponudu kursa ">
	</form>
	<?
}


// Kraj ADMIN/MISC
print "<hr/>\n";

}

?>
