<?

// ADMIN/MISC - sta god mi padne na pamet da kodiram





function admin_misc() {


require_once("lib/utility.php"); // testjmbg
require_once("lib/zamgerui.php"); // mass_input
require_once("lib/formgen.php"); // db_dropdown
require_once("lib/student_predmet.php"); // update_komponente, upisi_na_predmet, ispisi_sa_predmeta
require_once("lib/student_studij.php"); // ima_li_uslov


global $mass_rezultat, $conf_ldap_server,$conf_ldap_domain; // za masovni unos studenata u grupe
global $conf_files_path; // za migriranje zadaća
	global $_api_http_code;


?>
<p>&nbsp;</p>
<h3>Ostalo</h3>
<p>Ovdje možete dodati svoj kod:</p>
<?


if (param('akcija') == "broken_integral") {
	$predmeti = db_query_varray("SELECT DISTINCT predmet FROM ponudakursa WHERE akademska_godina=16");
	foreach($predmeti as $predmet) {
		$ime = db_get("SELECT naziv FROM predmet WHERE id=$predmet");
		print "Predmet: $ime ($predmet)<br><br>";
		$ispiti = db_query_table("SELECT akp.id, akp.opcije FROM aktivnost_predmet akp, aktivnost_agp aagp WHERE aagp.predmet=$predmet AND aagp.akademska_godina=16 AND aagp.aktivnost_predmet=akp.id AND aagp.predmet=$predmet AND akp.aktivnost=8");
		foreach ($ispiti as $ispit) {
			if (strstr($ispit['opcije'], "Integral")) {
				print "Integralni: " . $ispit['id'] . " - " . $ispit['opcije'] . "<br>";
				$options = explode(",", $ispit['opcije']);
				$integralopt = "";
				foreach($options as $option)
					if (strstr($option, "Integral"))
						$integralopt = $option;
				$parts = explode(":", $integralopt);
				$partials = explode("+", $parts[1]);
				foreach($partials as $partial) {
					$found = false;
					foreach ($ispiti as $ispit2) {
						if ($ispit2['id'] == $partial) $found = true;
					}
					if (!$found) print "Not found $partial<br>";
				}
			}
		}
		print "<br><br>";
	}
	return;
}



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


// Ovo je retroaktivni upis u predmete svih studenata viših godina 
// Koristi se samo ako sam opet bio kreten i nisam na vrijeme koristio admin/novagodina

if ($_REQUEST['akcija'] == "upis_vise") {
	$ag = intval($_REQUEST['ag']);

	if ($_REQUEST['fakatradi'] != 1) $ispis=1; else $ispis=0;
	$q10 = db_query("select ss.student, ss.studij, s.kratkinaziv, ss.ponovac, ss.semestar from student_studij as ss, studij as s, tipstudija as ts where ss.akademska_godina=$ag and ss.studij=s.id and s.tipstudija=ts.id and ts.ciklus=1");
	while (db_fetch5($q10, $student, $studij, $studij_kratki, $ponovac, $semestar)) {
		$godina = ($semestar + 1) / 2;
		if ($ispis) {
			$pstudent = db_query_assoc("SELECT ime, prezime, brindexa FROM osoba WHERE id=$student");
			print "Student " . $pstudent['ime'] . " " . $pstudent['prezime'] . " (" . $pstudent['brindexa'] . ") - $studij_kratki $godina";
		}
		if ($ponovac > 0) {
			if ($ispis) 
				print " - ponovac<br>\n";
			global $zamger_predmeti_pao, $zamger_pao_ects;
			$uslov = ima_li_uslov($student, $ag);
			foreach ($zamger_predmeti_pao as $predmet => $naziv_predmeta) {
				if ($ispis)
					print "-- Pao predmet $naziv_predmeta<br>\n";
				
				$q15 = db_query("SELECT id, semestar FROM ponudakursa WHERE akademska_godina=$ag AND predmet=$predmet AND studij=$studij");
				if (db_num_rows($q15) == 0) {
					if ($ispis) 
						print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; -- Nepoznata ponudakursa (studij $studij semestar $semestar)<br>\n";
					else
						print""; // Kreiraj ponudu kursa?
				} else {
					db_fetch2($q15, $pk, $semestar);
					if ($semestar % 2 == 0) {
						if ($ispis)
							print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; -- Predmet u parnom semestru, preskačem<br>\n";
						continue;
					}
				}
				$vecupisan = db_get("SELECT COUNT(*) FROM student_predmet WHERE student=$student AND predmet=$pk");
				if ($vecupisan) {
					if ($ispis)
						print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; -- Već upisan na predmet<br>\n";
				}
				else if (!$ispis)
					upis_studenta_na_predmet($student, $pk);
			}
			
		} else {
			if ($ispis) print "<br>\n";
			$q20 = db_query("select pk.id, p.naziv from ponudakursa as pk, predmet as p where pk.semestar=$semestar and pk.akademska_godina=$ag and pk.studij=$studij and pk.predmet=p.id AND pk.obavezan=1");
			while (db_fetch2($q20, $pk, $naziv_predmeta)) {
				$vecupisan = db_get("SELECT COUNT(*) FROM student_predmet WHERE student=$student AND predmet=$pk");
				if ($vecupisan) {
					if ($ispis)
						print "-- Već upisan na obavezan predmet $naziv_predmeta<br>\n";
				} else if ($ispis)
					print "-- Upisujem na obavezan predmet $naziv_predmeta<br>\n";
				else
					upis_studenta_na_predmet($student, $pk);
			}
			$uou_id = db_get("SELECT id FROM ugovoroucenju WHERE student=$student AND akademska_godina=$ag AND semestar=$semestar");
			if (!$uou_id) {
				if ($ispis)
					print "-- Student nije popunio ugovor o učenju<br>\n";
			} else {
				$predmeti_uou = db_query_varray("SELECT predmet FROM ugovoroucenju_izborni WHERE ugovoroucenju=$uou_id");
				foreach($predmeti_uou as $predmet) {
					$q30 = db_query("select pk.id, p.naziv from ponudakursa as pk, predmet as p where pk.semestar=$semestar and pk.akademska_godina=$ag and pk.studij=$studij and pk.predmet=p.id AND pk.obavezan=0 AND p.id=$predmet");
					if (db_num_rows($q30) == 0) {
						if ($ispis) {
							$naziv_predmeta = db_get("SELECT naziv FROM predmet WHERE id=$predmet");
							print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; -- Ne postoji ponuda kursa za predmet $naziv_predmeta iz ugovora<br>\n";
						} else
							print ""; // Kreirati ponudu za izborni sa drugog odsjeka
					} else {
						db_fetch2($q30, $pk, $naziv_predmeta);
						$vecupisan = db_get("SELECT COUNT(*) FROM student_predmet WHERE student=$student AND predmet=$pk");
						if ($vecupisan) {
							if ($ispis)
								print "-- Već upisan na izborni predmet $naziv_predmeta (uou)<br>\n";
						}
						else if ($ispis)
							print "-- Upisujem na izborni predmet $naziv_predmeta (uou)<br>\n";
						else
							upis_studenta_na_predmet($student, $pk);
					}
				}
			}
		}
	
// 		$q5 = db_query("select pk.id, p.naziv from ponudakursa as pk, predmet as p where pk.semestar=1 and pk.akademska_godina=$ag and pk.studij=$r10[1] and pk.predmet=p.id");
// 		while ($r5 = db_fetch_row($q5)) {
// 			$q15 = db_query("select count(*) from student_predmet where student=$r10[0] and predmet=$r5[0]");
// 			if (db_result($q15,0,0)>0) {
// 				if ($ispis) {
// 					$q20 = db_query("select ime, prezime, brindexa from osoba where id=$r10[0]");
// 					print "Student ".db_result($q20,0,0)." ".db_result($q20,0,1)." ".db_result($q20,0,2)." već upisan na ponudukursa $r5[1]<br>";
// 				}
// 			} else {
// 				if ($ispis) {
// 					$q20 = db_query("select ime, prezime, brindexa from osoba where id=$r10[0]");
// 					print "Upisujem studenta ".db_result($q20,0,0)." ".db_result($q20,0,1)." ".db_result($q20,0,2)." na ponudukursa $r5[1] ($r10[2] - $r5[0])<br>";
// 				} else
// 					upis_studenta_na_predmet($r10[0], $r5[0]);
// 			}
// 		}
	}
}



//----------------------------------------
// Masovni unos jmbg
//----------------------------------------

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
		<?=genform("POST")?>
		<input type="hidden" name="fakatradi" value="1">
		<input type="hidden" name="akcija" value="logini">
		<input type="submit" value=" Fakat radi ">
		</form>
		<?
	}

} else {
	
	
	?>
	<?=genform("POST")?>
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



if ($_POST['akcija']=="zamjena_grupa") {
	$s1 = int_param('s1');
	$s2 = int_param('s2');
	$f = intval($_POST['fakatradi']);
	$ag = db_get("SELECT id FROM akademska_godina WHERE aktuelna=1");
	
	if (param('automatski')) {
		if ($f == 0) {
			$ime_s1 = db_get("SELECT CONCAT(prezime,CONCAT(' ',ime)) FROM osoba WHERE id=$s1");
			$ime_s2 = db_get("SELECT CONCAT(prezime,CONCAT(' ',ime)) FROM osoba WHERE id=$s2");
		}
	
	
		$naziv_grupe_1 = db_get("SELECT l.naziv FROM labgrupa l, student_labgrupa sl WHERE l.akademska_godina=$ag AND sl.labgrupa=l.id AND sl.student=$s1 AND virtualna=0");
		$naziv_grupe_2 = db_get("SELECT l.naziv FROM labgrupa l, student_labgrupa sl WHERE l.akademska_godina=$ag AND sl.labgrupa=l.id AND sl.student=$s2 AND virtualna=0");
		
		if ($f == 0) print "Prebacujem studenta <b>$ime_s1</b> iz grupe <b>$naziv_grupe_1</b> u grupu <b>$naziv_grupe_2</b><br>";
		
		$grupe1 = db_query_vassoc("SELECT l.id, l.predmet FROM labgrupa l, student_labgrupa sl WHERE l.akademska_godina=$ag AND sl.labgrupa=l.id AND sl.student=$s1 AND virtualna=0");
		$grupe2 = db_query_vassoc("SELECT l.id, l.predmet FROM labgrupa l, student_labgrupa sl WHERE l.akademska_godina=$ag AND sl.labgrupa=l.id AND sl.student=$s2 AND virtualna=0");
		
		foreach($grupe1 as $id1=>$predmet1) {
			$nova = db_get("SELECT id FROM labgrupa WHERE predmet=$predmet1 AND akademska_godina=$ag AND naziv='$naziv_grupe_2'");
			$naziv_predmeta = db_get("SELECT naziv FROM predmet WHERE id=$predmet1");
			
			if (!$nova) {
				niceerror("-- Greška predmet $naziv_predmeta");
			} else {
				if ($f == 0) {
					print "-- Predmet $naziv_predmeta<br>";
				} else {
					ispis_studenta_sa_labgrupe($s1, $id1);
					upis_studenta_na_labgrupu($s1, $nova);
				}
			}
		}
		
		if ($f == 0) print "<br><br>Prebacujem studenta <b>$ime_s2</b> iz grupe <b>$naziv_grupe_2</b> u grupu <b>$naziv_grupe_1</b><br>";
		
		
		foreach($grupe2 as $id2=>$predmet2) {
			$nova = db_get("SELECT id FROM labgrupa WHERE predmet=$predmet2 AND akademska_godina=$ag AND naziv='$naziv_grupe_1'");
			$naziv_predmeta = db_get("SELECT naziv FROM predmet WHERE id=$predmet2");
			
			if (!$nova) {
				niceerror("-- Greška predmet $naziv_predmeta");
			} else {
				if ($f == 0) {
					print "-- Predmet $naziv_predmeta<br>";
				} else {
					ispis_studenta_sa_labgrupe($s2, $id2);
					upis_studenta_na_labgrupu($s2, $nova);
				}
			}
		}
		
		
		if ($f==0) {
			?>
			<?=genform("POST")?>
			<input type="hidden" name="fakatradi" value="1">
			<input type="hidden" name="akcija" value="zamjena_grupa">
			<input type="submit" value=" Fakat radi ">
			</form>
			<?
		} else {
			nicemessage("Zamijenjene grupe");
		}

	} else {
		$grupe1 = db_query_vassoc("SELECT l.id, l.predmet FROM labgrupa l, student_labgrupa sl WHERE l.akademska_godina=$ag AND sl.labgrupa=l.id AND sl.student=$s1 AND virtualna=0");
		$grupe2 = db_query_vassoc("SELECT l.id, l.predmet FROM labgrupa l, student_labgrupa sl WHERE l.akademska_godina=$ag AND sl.labgrupa=l.id AND sl.student=$s2 AND virtualna=0");
		
		foreach($grupe1 as $id1=>$predmet1) {
			foreach($grupe2 as $id2=>$predmet2) {
				if ($predmet1==$predmet2) {
					ispis_studenta_sa_labgrupe($s1, $id1);
					ispis_studenta_sa_labgrupe($s2, $id2);
					upis_studenta_na_labgrupu($s1, $id2);
					upis_studenta_na_labgrupu($s2, $id1);
					break;
				}
			}
		}
	
		nicemessage("Zamijenjene grupe");
	}
	
} else {
	
	
	?>
	<p><b>Zamjena grupa</b></p>
	<?=genform("POST")?>
	<input type="hidden" name="fakatradi" value="0">
	<input type="hidden" name="akcija" value="zamjena_grupa">
	Student 1: <input type="text" name="s1"><br>
	Student 2: <input type="text" name="s2"><br>
	<input type="checkbox" name="automatski" CHECKED> Automatski kreirane grupe<br>
	
	<input type="submit" value=" Zamijeni grupe ">
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
	<?=genform("POST")?>
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

if ($_POST['akcija']=="update_komponenti" && intval($_POST['stage']) == 2) {

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
		<?=genform("POST")?>
		<input type="hidden" name="fakatradi" value="1">
		<input type="hidden" name="akcija" value="update_komponenti">
		<input type="submit" value=" Fakat radi ">
		</form>
		<?
	}
	
} else if ($_POST['akcija']=="update_komponenti") {
	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);
	
	$q110 = db_query("SELECT pk.id, s.naziv, pk.semestar FROM ponudakursa pk, studij s WHERE pk.predmet=$predmet AND pk.akademska_godina=$ag AND pk.studij=s.id");
	$q120 = db_query("SELECT k.id, k.gui_naziv FROM komponenta k, tippredmeta_komponenta tpk, akademska_godina_predmet agp WHERE agp.predmet=$predmet AND agp.akademska_godina=$ag AND agp.tippredmeta=tpk.tippredmeta AND tpk.komponenta=k.id");
	
	if (db_num_rows($q110)==0 || db_num_rows($q120)==0) {
		niceerror("Predmet se nije izvodio u toj godini");
	} else {
		
		?>
		<?=genform("POST")?>
		<input type="hidden" name="akcija" value="update_komponenti">
		<input type="hidden" name="stage" value="2">
		Ponudakursa: <select name="pk"><?
		while (db_fetch3($q110, $pk, $studij, $semestar))
			print "<option value='$pk'>$studij, $semestar. semestar</option>\n";
		?></select><br>
		Komponenta: <select name="komponenta"><?
		while (db_fetch2($q120, $komponenta, $naziv_komponente))
			print "<option value='$komponenta'>$naziv_komponente</option>\n";
		?></select><br>
		<input type="submit" value=" Update komponenti ">
		</form>
		<?
	}
	

} else {
	
	$q130 = db_query("SELECT id, naziv FROM predmet ORDER BY naziv");
	$q140 = db_query("SELECT id, naziv, aktuelna FROM akademska_godina ORDER BY id");
	
	?>
	<?=genform("POST")?>
	<input type="hidden" name="akcija" value="update_komponenti">
	Predmet: <select name="predmet"><?
	while (db_fetch2($q130, $predmet, $naziv_predmeta))
		print "<option value='$predmet'>$naziv_predmeta</option>\n";
	?></select><br>
	Akademska godina: <select name="ag"><?
	while (db_fetch3($q140, $ag, $naziv_ag, $aktuelna))
		print "<option value='$ag'" . ($aktuelna ? " SELECTED" : "") . ">$naziv_ag</option>\n";
	?></select><br>
	<input type="submit" value=" Izbor komponente ">
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
	if (db_num_rows($q105)>0) {
		if ($f)
			$q105a = db_query("delete from auth where id=$osoba");
		else
			print "Login ".db_result($q105,0,0)."<br>\n";
	}

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
		
	$q145 = db_query("SELECT akademska_godina FROM izvoz_upis_prva WHERE student=$osoba");
	if ($f)
		$q145a = db_query("DELETE FROM izvoz_upis_prva WHERE student=$osoba");
	else 
		while ($r145 = db_fetch_row($q145))
			print "Izvoz upis prva: " . db_get("SELECT naziv FROM akademska_godina WHERE id=$r145[0]") . "<br>";

//izbor
//kolizija
//komentar
//komponentebodovi
//konacna_ocjena
//kviz_student

	/*$q150 = db_query("select vrijeme, dogadjaj, nivo from log where userid=$osoba");
	if ($f)
		$q150a = db_query("delete from log where userid=$osoba");
	else
		while ($r150 = db_fetch_row($q150)) {
			print "Log: $r150[0] $r150[1] $r150[2]<br>";
		}*/
	
	$q160 = db_query("select kod from kod_za_izvjestaj where osoba=$osoba");
	if ($f)
		$q160a = db_query("delete from kod_za_izvjestaj where osoba=$osoba");
	else
		while ($r160 = db_fetch_row($q160)) {
			print "Kod za izvjestaj: $r160[0]<br>";
		}

	$q160 = db_query("select vrijeme, modul, dogadjaj from log2 where userid=$osoba");
	if ($f)
		$q160a = db_query("delete from log2 where userid=$osoba");
	else
		while ($r160 = db_fetch_row($q160)) {
			print "Log2: $r160[0] $r160[1] $r160[2]<br>";
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
		<?=genform("POST")?>
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
	<?=genform("POST")?>
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

	$q145 = db_query("select labgrupa, komentar from komentar where student=$osoba_B");
	if ($f)
		$q145a = db_query("UPDATE komentar SET student=$osoba_A where student=$osoba_B");
	else
		while ($r145 = db_fetch_row($q145)) {
			$q146 = db_query("select p.naziv from predmet p, labgrupa l where l.id=$r145[0] and l.predmet=p.id");
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

// MYSQL query:
// delete from osoba where id=6800
// 
// MYSQL error:
// Cannot delete or update a parent row: a foreign key constraint fails (`zamger`.`izvoz_upis_semestar`, CONSTRAINT `izvoz_upis_semestar_ibfk_1` FOREIGN KEY (`student`) REFERENCES `osoba` (`id`))


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
		<?=genform("POST")?>
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
	<?=genform("POST")?>
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
				print "!! $r30[1] bodova na ispitu $r30[0] - nisam pronašao odgovarajući ispit (datum $r30[2], komponenta $r30[3]), promijeniti ispit manuelno<br>";
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



//----------------------------------------
// Generiši izvještaj
//----------------------------------------

?>
<p><hr/></p>

<p><b>Generiši izvještaj</b></p>

<?

if ($_POST['akcija']=="generisi_izvjestaj" && check_csrf_token()) {
	// Generisem statičku verziju izvještaja predmet
	generisi_izvjestaj_predmet( $_POST['predmet'], $_POST['ag'], array("skrati" => "da", "sakrij_imena" => "da", "razdvoji_ispite" => "da") );
	
	nicemessage("Izvještaj generisan");

} else {
	
	?>
	<?=genform("POST")?>
	<input type="hidden" name="akcija" value="generisi_izvjestaj">
	Unesite ID predmeta: <input type="text" name="predmet" value=""><br>
	Akademska godina: <input type="text" name="ag" value=""><br>
	<input type="submit" value=" Generiši izvještaj ">
	</form>
	<?
}



//----------------------------------------
// Import rasporeda
//----------------------------------------

?>
<p><hr/></p>

<p><b>Import raspored</b></p>

<?

if ($_POST['akcija']=="import_raspored" && check_csrf_token()) {
	$raspored = int_param('raspored');
	if ($raspored == 0) {
		?>
		<?=genform("POST")?></p>
		<input type="hidden" name="akcija" value="import_raspored">
		<input type="hidden" name="raspored" value="-1">
		Akademska godina: <select name="akademska_godina">
		<?
		$q20 = db_query("SELECT id, naziv, aktuelna FROM akademska_godina");
		while (db_fetch3($q20, $id, $naziv, $aktuelna)) {
			print "<option value=\"$id\"";
			if ($aktuelna == 1) print " selected";
			print ">$naziv</option>\n";
		}
		?>
		</select><br>
		Studij: <select name="studij"><option value="0">(Svi studiji)</option>
		<?
		$q30 = db_query("SELECT id, naziv FROM studij WHERE moguc_upis=1");
		while (db_fetch2($q30, $id, $naziv)) {
			print "<option value=\"$id\">$naziv</option>";
		}
		?>
		</select><br>
		Semestar: <select name="semestar"><option value="0">(Svi semestri)</option>
		<?
		for ($i=1; $i<6; $i++)
			print "<option value=\"$i\">$i</option>\n";
		?>
		</select><br>
		<input type="submit" value=" Kreiraj ">
		</form>
		<?
		
		return;
	}
	
	if ($raspored == -1) {
		$ag = int_param('akademska_godina');
		$studij = int_param('studij');
		$semestar = int_param('semestar');
		
		db_query("INSERT INTO raspored SET studij=$studij, akademska_godina=$ag, semestar=$semestar, privatno=0, aktivan=1");
		$raspored = db_insert_id();
	} else {
		$ag = db_get("SELECT akademska_godina FROM raspored WHERE id=$raspored");
		if ($ag === false) {
			niceerror("Nepoznat raspored");
			return;
		}
	}
	
	$tr_dani = array("PO" => 1, "UT" => 2, "SR" => 3, "CE" => 4, "PE" => 5);
	$zamjene = array(
		// Naša slova
		"Inzenj" => "Inženj", "Racun" => "Račun", "Masin" => "Mašin", "Dinamick" => "Dinamičk", "Elektricn" => "Električn", "Logick" => "Logičk", "Rjesenj" => "Rješenj", "Zastit" => "Zaštit", "Mrez" => "Mrež", "Numerick" => "Numeričk", "Istrazi" => "Istraži", "Menadzm" => "Menadžm", "Cvorist" => "Čvorišt", "Optick" => "Optičk", 
		// Specifičnosti predmeta
		"Računari Arhitektura" => "Računari, Arhitektura", "Upravljanje E E S" => "Upravljanje elektroenergetskih sistema", "Pouzdanost El Elemenata" => "Pouzdanost električnih elemenata", "Dinamika El Ma" => "Dinamika električnih ma", "Uupravljanje" => "Upravljanje", "Vjestacke" => "Vještačke", "Inovacije U Projektiranju" => "Inovacije u projektovanju", "lektronika Za Telekomunikacije " => "lektronika TK", "hC" => "h Č", "U T K Mre" => "u Telekomunikacijskim mre", "U T K Kana" => "u Telekomunikacijskom kana", "Softver Inzinjering" => "softver inženjering", "ki T K Sis" => "ki Telekomunikacijski sis", "Telek Softver Inzen" => "Telekomunikacijski Softver Inženjering", "Ttelekomu" => "Telekomu");
	$zamjene_sale = array("EE-1" => "EE1", "EE-2" => "EE2");
	
	$ocekivani = $ocekivano_ime = "";
	foreach(explode("\n", $_REQUEST['import']) as $linija) {
		//print "Linija: $linija<br>\n";
		//print "Ocekivani: $ocekivani $ocekivano_ime<br>\n";
		
		$dijelovi = explode(",", trim($linija));
		$novi_dan = $tr_dani[substr($dijelovi[0], 0, 2)];
		$polusat = (substr($dijelovi[0], strlen($dijelovi[0])-1) == "A");
		$vrijeme = intval(substr($dijelovi[0], 2)) - 1;
		
		$dijelovi_imena = substr($dijelovi[1], 0, strrpos($dijelovi[1], "-"));
		
		if ($dijelovi[0] == $ocekivani && $dijelovi_imena == $ocekivano_ime) { 
			$vrijeme_kraj = $vrijeme;
			
			if ($polusat) $fini_kraj = "00:00:00";
			else $fini_kraj = sprintf("00:%02d:30", $vrijeme+8);
			
			if ($polusat) $ocekivani = substr($dijelovi[0], 0, 2) . ($vrijeme_kraj+2);
			else $ocekivani = substr($dijelovi[0], 0, 2) . ($vrijeme+1) . "A";
			continue;
		}
		
		if ($ocekivani != "" && $id_predmeta !== false) {
			if (empty($labgrupe) && $tip == "P")
				db_query("INSERT INTO raspored_stavka VALUES (0, $raspored, $dan, $id_predmeta, 0, $vrijeme_pocetak, $vrijeme_kraj, $id_sale, '$tip', 0, 0, '$fini_pocetak', '$fini_kraj')");
			else foreach($labgrupe as $lg)
				db_query("INSERT INTO raspored_stavka VALUES (0, $raspored, $dan, $id_predmeta, $lg, $vrijeme_pocetak, $vrijeme_kraj, $id_sale, '$tip', 0, 0, '$fini_pocetak', '$fini_kraj')");
		}
		
		$dan = $novi_dan;
		$vrijeme_pocetak = $vrijeme;
		$vrijeme_kraj = $vrijeme;
		if ($polusat) {
			$fini_pocetak = sprintf("00:%02d:30", $vrijeme+8);
			$fini_kraj = "00:00:00";
		}
		else {
			$fini_pocetak = "00:00:00";
			$fini_kraj = sprintf("00:%02d:30", $vrijeme+8);
		}
		
		$ocekivano_ime = substr($dijelovi[1], 0, strrpos($dijelovi[1], "-"));
		$tip = strtoupper($ocekivano_ime[0]);
		
		$tmpime = substr($dijelovi[1], 1, strpos($dijelovi[1], "-")-1);
		$ime_predmeta = "";
		for ($i=0; $i<strlen($tmpime); $i++) {
			if ($i>0 && (($tmpime[$i] >= "A" && $tmpime[$i] <= "Z") || ($tmpime[$i] >= "0" && $tmpime[$i] <= "9")))
				$ime_predmeta .= " ";
			$ime_predmeta .= $tmpime[$i];
		}
		
		foreach($zamjene as $dio => $zamjena)
			$ime_predmeta = str_replace($dio, $zamjena, $ime_predmeta);
		
		$id_predmeta = db_get("SELECT id FROM predmet WHERE naziv LIKE '$ime_predmeta'");
		if ($id_predmeta === false)
			print "-- Nije pronađen predmet $ime_predmeta<br>\n";
		if ($id_predmeta == 20) $id_predmeta = 2093;
		
		$sala = $dijelovi[count($dijelovi)-1];
		if ($sala[0] == "R") $sala = substr($sala,1);
		foreach($zamjene_sale as $dio => $zamjena)
			$sala = str_replace($dio, $zamjena, $sala);
		
		$id_sale = db_get("SELECT id FROM raspored_sala WHERE naziv LIKE '$sala'");
		if ($id_sale === false)
			print "-- Nije pronađena sala $sala<br>\n";
		
		if ($polusat) $ocekivani = substr($dijelovi[0], 0, 2) . ($vrijeme_kraj+1);
		else $ocekivani = substr($dijelovi[0], 0, 2) . ($vrijeme+1) . "A";
		
		$labgrupe = array();
		if ($id_predmeta !== false)
			for ($i=2; $i<count($dijelovi)-1; $i++) {
				$id_labgrupe = db_get("SELECT id FROM labgrupa WHERE naziv LIKE '".$dijelovi[$i]."' AND predmet=$id_predmeta AND akademska_godina=$ag");
				if ($id_labgrupe !== false) $labgrupe[] = $id_labgrupe;
				//print "Naziv ".$dijelovi[$i]." id $id_labgrupe<br>\n";
			}
	}
	
	if ($ocekivani != "" && $id_predmeta !== false)  {
		if (empty($labgrupe) && $tip == "P")
			db_query("INSERT INTO raspored_stavka VALUES (0, $raspored, $dan, $id_predmeta, 0, $vrijeme_pocetak, $vrijeme_kraj, $id_sale, '$tip', 0, 0, '$fini_pocetak', '$fini_kraj')");
		else foreach($labgrupe as $lg)
			db_query("INSERT INTO raspored_stavka VALUES (0, $raspored, $dan, $id_predmeta, $lg, $vrijeme_pocetak, $vrijeme_kraj, $id_sale, '$tip', 0, 0, '$fini_pocetak', '$fini_kraj')");
	}
	

	nicemessage("Raspored importovan");

} else {
	
	$q10 = db_query("SELECT id, studij, akademska_godina, semestar FROM raspored ORDER BY akademska_godina DESC");
	$lista = "";
	while(db_fetch4($q10, $id, $studij, $akademska_godina, $semestar)) {
		if ($studij == 0) $tekst = "Svi studiji";
		else $tekst = db_get("SELECT naziv FROM studij WHERE id=$studij");
		
		$tekst .= " (" . db_get("SELECT naziv FROM akademska_godina WHERE id=$akademska_godina");
		
		if ($semestar > 0) $tekst .= ", $semestar. semestar";
		$tekst .= ")";
		
		$lista .= "<option value=\"$id\">$tekst</option>\n";
	}
	
	?>
	<?=genform("POST")?>
	<input type="hidden" name="akcija" value="import_raspored">
	Ažuriraj raspored: <select name="raspored"><option value="0">(Kreiraj novi)</option><?=$lista?></select><br>
	<textarea name="import" rows="6" cols="60"></textarea><br>
	<input type="submit" value=" Uvezi raspored ">
	</form>
	<?
}






//----------------------------------------
// Upis na završni rad
//----------------------------------------

?>
<p><hr/></p>

<p><b>Masovni upis na predmet &quot;Završni rad&quot;</b></p>

<?



if ($_POST['akcija']=="mass_zavrsni" && check_csrf_token()) {
	if ($_REQUEST['fakatradi'] != 1) $ispis=1; else $ispis=0;
	
	$ag = db_get("SELECT id FROM akademska_godina WHERE aktuelna=1");
	
	// Kod je obsolete zbog kolone tippredmeta
	$pk_zavrsni = db_query_vassoc("SELECT pk.studij, pk.id FROM ponudakursa pk, akademska_godina_predmet agp 
	WHERE agp.akademska_godina=$ag AND (agp.tippredmeta=1000 OR agp.tippredmeta=1001) AND agp.predmet=pk.predmet AND pk.akademska_godina=$ag"); // 1000 = Završni rad


	$q10 = db_query("SELECT o.id, o.ime, o.prezime, s.id FROM osoba o, student_studij ss, studij s, tipstudija ts
	WHERE ss.akademska_godina=$ag AND ss.student=o.id AND ss.studij=s.id AND s.tipstudija=ts.id AND ts.ciklus=1 AND ss.semestar=5
	ORDER BY o.prezime, o.ime");
	while (db_fetch4($q10, $student, $ime, $prezime, $studij)) {
		$pk = $pk_zavrsni[$studij];
		if (!isset($pk)) {
			print "--- Greška: Nepoznat predmet za studij $studij!!!<br>\n";
			continue;
		}
		$vec_upisan = db_get("SELECT COUNT(*) FROM student_predmet WHERE student=$student AND predmet=$pk");
		if ($vec_upisan) continue;
		if ($ispis) print "Upisujem $prezime $ime<br>\n";
		else {
			upis_studenta_na_predmet($student, $pk);
		}
	}
	
	
	// Sada isto to za drugi ciklus
	$q10 = db_query("SELECT o.id, o.ime, o.prezime, s.id FROM osoba o, student_studij ss, studij s, tipstudija ts
	WHERE ss.akademska_godina=$ag AND ss.student=o.id AND ss.studij=s.id AND s.tipstudija=ts.id AND ts.ciklus=2 AND ss.semestar=3
	ORDER BY o.prezime, o.ime");
	while (db_fetch4($q10, $student, $ime, $prezime, $studij)) {
		if ($studij >= 18 && $studij <= 21) continue; // Ekvivalencija
		$pk = $pk_zavrsni[$studij];
		if (!isset($pk)) print "--- Greška: Nepoznat predmet za studij $studij!!!<br>\n";
		$vec_upisan = db_get("SELECT COUNT(*) FROM student_predmet WHERE student=$student AND predmet=$pk");
		if ($vec_upisan) continue;
		if ($ispis) print "Upisujem $prezime $ime<br>\n";
		else {
			upis_studenta_na_predmet($student, $pk);
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
		Svi studenti upisani na Završni rad.
		<?
	}

} else {
	?>
	<?=genform("POST")?>
	<input type="hidden" name="fakatradi" value="0">
	<input type="hidden" name="akcija" value="mass_zavrsni">
	<input type="submit" value=" Upiši sve studente 5. semestra BSc / 3. semestra MSc na predmet Završni rad ">
	</form>
	<?
}




//----------------------------------------
// Provjera uslova
//----------------------------------------

?>
<p><hr/></p>

<p><b>Ima li uslov?</b></p>

<?



if ($_POST['akcija']=="ima_li_uslov" && check_csrf_token()) {
	global $zamger_predmeti_pao, $zamger_pao_ects;
	$uslov = ima_li_uslov_plan(int_param('student'), int_param('ag'), int_param('studij'), int_param('semestar'), int_param('studij_trajanje'), int_param('plan_studija'));
	if ($uslov) print "Ima uslov!<br><br>"; else print "Nema uslov!<br><br>";
	print_r($zamger_predmeti_pao);
	print "<br><br>ZPE: " . $zamger_pao_ects;
	return;

} else {
	?>
	<?=genform("POST")?>
	<input type="hidden" name="akcija" value="ima_li_uslov">
	Student: <input type="text" name="student"><br>
	A.g.: <input type="text" name="ag"><br>
	Studij: <input type="text" name="studij"><br>
	Semestar: <input type="text" name="semestar"><br>
	Trajanje: <input type="text" name="studij_trajanje"><br>
	Plan studija: <input type="text" name="plan_studija"><br>
	<input type="submit" value=" Provjeri uslov ">
	</form>
	<?
}




//----------------------------------------
// Novi messaging
//----------------------------------------

?>
<p><hr/></p>

<p><b>Pošalji poruku</b></p>

<?



if ($_POST['akcija']=="posalji_poruku" && check_csrf_token()) {
	require_once ("lib/messaging.php");
	$poruka = array();
	$poruka['tip'] = intval($_POST['tip']);
	$poruka['opseg'] = intval($_POST['opseg']);
	$poruka['ref'] = intval($_POST['ref']);
	$poruka['primalac'] = intval($_POST['primalac']);
	$poruka['naslov'] = $_POST['naslov'];
	$poruka['tekst'] = $_POST['tekst'];
	
	$poruka = posalji_poruku($poruka);
	nicemessage("Poruka uspješno poslana ".$poruka['id']);
	return;

} else if ($_POST['akcija']=="konverzija_poruka" && check_csrf_token()) {
	require_once ("lib/messaging.php");
	konverzija();
	nicemessage("Poruka uspješno poslana ".$poruka['id']);
	return;

} else if ($_POST['akcija']=="inbox" && check_csrf_token()) {
	require_once ("lib/messaging.php");
	$korisnik = intval($_POST['korisnik']);
	
	print "Nepročitane: <br><ul>\n";
	$count = 0;
	foreach(daj_inbox($korisnik, true) as $poruka) {
		print "<li>".$poruka['naslov']." (".date("d.m.Y H:i:s", $poruka['vrijeme']).") od: ".$poruka['posiljalac']."</li>";
		if ($count++ > 40) break;
	}

	print "</ul>Sve: <br><ul>\n";
	$count = 0;
	foreach(daj_inbox($korisnik, false) as $poruka) {
		print "<li>".$poruka['naslov']." (".date("d.m.Y H:i:s", $poruka['vrijeme']).") od: ".$poruka['posiljalac']."</li>";
		if ($count++ > 40) break;
	}
	print "</ul>";

	return;
	
} else {
	?>
	<?=genform("POST")?>
	<input type="hidden" name="akcija" value="posalji_poruku">
	Tip: <input type="text" name="tip"><br>
	Opseg: <input type="text" name="opseg"><br>
	Ref: <input type="text" name="ref"><br>
	Primalac: <input type="text" name="primalac"><br>
	Naslov: <input type="text" name="naslov"><br>
	Tekst: <input type="text" name="tekst"><br>
	<input type="submit" value=" Pošalji poruku ">
	</form>
	<?
	?>
	<?=genform("POST")?>
	<input type="hidden" name="akcija" value="konverzija_poruka">
	<input type="submit" value=" Konverzija poruka ">
	</form>
	<?
	?>
	<?=genform("POST")?>
	<input type="hidden" name="akcija" value="inbox">
	Korisnik: <input type="text" name="korisnik"><br>
	<input type="submit" value=" Daj inbox ">
	</form>
	<?
}




//----------------------------------------
// Apsolventi
//----------------------------------------

if (param('akcija') == "upisi_apsolvente" && check_csrf_token()) {
	$ispis = (int_param('fakatradi') != 1);
	
	$q10 = db_query("SELECT student, studij, semestar, ss.akademska_godina, nacin_studiranja, plan_studija FROM student_studij ss, akademska_godina ag WHERE ss.akademska_godina=ag.id AND ag.aktuelna=1 AND ss.semestar MOD 2 = 1 AND status_studenta=1");
	print "<ul>";
	while (db_fetch6($q10, $student, $studij, $semestar, $godina, $nacin_studiranja, $plan_studija)) {
		$ponovac = true;
		$status = 1;
		$semestar++;
		$enrollment = array_to_object([ "student" => [ "id" => $student], "Programme" => [ "id" => $studij], "semester" => $semestar, "AcademicYear" => [ "id" => $godina], "repeat" => $ponovac, "status" => $status ]);
		$enrollment->EnrollmentType = [ "id" => $nacin_studiranja ];
		$enrollment->Curriculum = [ "id" => $plan_studija ];
		$enrollment->dryRun = $ispis;
		
		$newEnrollment = api_call("enrollment/$student", $enrollment, "POST");
		if ($_api_http_code == "201") {
			$studentFullname = $newEnrollment['student']['surname'] . " " . $newEnrollment['student']['name'] . " (" . $newEnrollment['student']['studentIdNr'] . ")";
			
			nicemessage("Student $studentFullname ($student) je upisan na studij " . $newEnrollment['Programme']['name'] . ", $semestar. semestar u akademskoj " . $newEnrollment['AcademicYear']['name'] . " godini");
			
			// Enroll into courses
			foreach($newEnrollment['enrollCourses'] as $cuy) {
				if ($ispis) { print "* Student upisan na predmet " . $cuy['courseName'] . "<br>"; continue; }
				$result = api_call("course/" . $cuy['CourseUnit']['id'] . "/" . $cuy['AcademicYear']['id'] . "/enroll/$student", [], "POST");
				if ($_api_http_code == "201") {
					print "* Student upisan na predmet " . $cuy['courseName'] . "<br>";
				} else if ($_api_http_code == "403") {
					print "* Student je već od ranije upisan na predmet " . $cuy['courseName'] . "<br>";
				} else {
					niceerror("Neuspješan upis studenta na predmet: " . $result['message']);
				}
			}
			foreach($newEnrollment['failedCourses'] as $cuy) {
				if ($ispis) { print "* Student upisan na preneseni predmet " . $cuy['courseName'] . "<br>"; continue; }
				$result = api_call("course/" . $cuy['CourseUnit']['id'] . "/" . $cuy['AcademicYear']['id'] . "/enroll/$student", [], "POST");
				if ($_api_http_code == "201") {
					print "* Student upisan na preneseni predmet " . $cuy['courseName'] . "<br>";
				} else if ($_api_http_code == "403") {
					print "* Student je već od ranije upisan na preneseni predmet " . $cuy['courseName'] . "<br>";
				} else {
					niceerror("Neuspješan upis studenta na predmet: " . $result['message']);
				}
			}
			
			
		} else if($_api_http_code == "400" && starts_with($newEnrollment['message'], "Already enrolled")) {
			if ($semestar % 2 == 0) $rijec = "ljetnji"; else $rijec = "zimski";
			niceerror("Student $student je već upisan u $rijec semestar tekuće akademske godine!");
		} else {
			niceerror("Neuspješan upis studenta na studij: " . $newEnrollment['message']);
		}
	}
	print "</ul>";
	
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
		Svi studenti upisani u parni semestar.
		<?
	}
	return;
}

?>
<p><hr/></p>

<p><?=genform("POST");?>
	<input type="hidden" name="akcija" value="upisi_apsolvente">
	<input type="hidden" name="fakatradi" value="0">
<input type="submit" value="Upiši sve studente u parni semestar">
</form></p>

<?


if (param('akcija') == "promijeni_kodove_finaliziraj" && check_csrf_token()) {
	print "Finaliziram<br>\n";
	db_query("UPDATE kod_za_izvjestaj SET promjena=0");
	return;
}

if (param('akcija') == "promijeni_kodove_predmet" && check_csrf_token()) {
	$parovi = explode(",", param('predmeti'));
	$par = array_shift($parovi);
	print "Generišem $par<br>\n";
	list($predmet,$ag) = explode("-", $par);
	generisi_izvjestaj_predmet($predmet, $ag, array("skrati" => "da", "sakrij_imena" => "da", "razdvoji_ispite" => "da") );

	if (!empty($parovi)) {
		$predmeti = join(",", $parovi);
		?>

		<p><?=genform("POST", "dalje");?>
		<input type="hidden" name="akcija" value="promijeni_kodove_predmet">
		<input type="hidden" name="predmeti" value="<?=$predmeti?>">
		<input type="submit" value="Step 2">
		</form></p>
		<?
	} else {
		?>

		<p><?=genform("POST", "dalje");?>
		<input type="hidden" name="akcija" value="promijeni_kodove_finaliziraj">
		<input type="submit" value="Step 3">
		</form></p>
		<?
	}
	?>
		<script>setTimeout(function() {
			document.getElementById('dalje').submit();
		}, 1000);
		</script>
	<?
	return;
}


if (param('akcija') == "promijeni_kodove" && check_csrf_token()) {
	global $conf_files_path;

	$q12 = db_query("SELECT osoba FROM kod_za_izvjestaj WHERE promjena=1");
	while (db_fetch1($q12, $osoba)) {
		do {
			$consonants = 'BCDFGHJKLMNPQRSTVWXYZ';
			$wovels = 'AEIOU';
			$code = array(); //remember to declare $pass as an array
			for ($i = 0; $i < 10 / 2; $i++) {
				$n = rand(0, strlen($consonants)-1);
				$code[] = $consonants[$n];
				$n = rand(0, 4);
				$code[] = $wovels[$n];
			}
			$string = implode($code); //turn the array into a string
			$exists = db_get("SELECT COUNT(*) FROM kod_za_izvjestaj WHERE kod='$string'");
		} while ($exists > 0);
		db_query("DELETE FROM kod_za_izvjestaj WHERE osoba=" . $osoba);
		db_query("INSERT INTO kod_za_izvjestaj VALUES(" . $osoba . ", '$string', 1)");
		print "Novi kod za osobu $osoba je $string<br>\n";
	}

	$predmeti = "";
	$q11 = db_query("SELECT DISTINCT pk.predmet, pk.akademska_godina FROM ponudakursa pk, student_predmet sp, kod_za_izvjestaj kzi WHERE pk.id=sp.predmet AND sp.student=kzi.osoba AND kzi.promjena=1");
	while (db_fetch2($q11, $predmet, $ag)) {
		if (file_exists("$conf_files_path/cache/izvjestaj_predmet/$predmet-$ag")) {
			$predmeti .= "$predmet-$ag,";
		}
	}

	if ($predmeti != "") {
		?>

		<p><?=genform("POST", "dalje");?>
		<input type="hidden" name="akcija" value="promijeni_kodove_predmet">
		<input type="hidden" name="predmeti" value="<?=$predmeti?>">
		<input type="submit" value="Step 2">
		</form></p>
		<script>setTimeout(function() {
			document.getElementById('dalje').submit();
		}, 1000);
		</script>
		<?
	} else {
		print "Nema ništa";
	}

	return;
}


?>
<p><hr/></p>

<p><?=genform("POST");?>
	<input type="hidden" name="akcija" value="promijeni_kodove">
	<input type="hidden" name="fakatradi" value="0">
<input type="submit" value="Ažuriraj kodove za izvještaje">
</form></p>
	<p><hr/></p>
<?


// Import studenata iz eUpis aplikacije


function fix_ime($string) {
	$result = mb_substr($string,0,1);
	$string = strtolower($string);
	$string = strtr($string, ["Š" => "š"]);
	$result .= mb_substr($string, 1);
	return $result;
}

function upitnik_like($s1, $s2) {
	$s1 = strtr($s1, ["Č" => "?", "Ć" => "?", "č" => "?", "ć" => "?", "đ" => "?"]);
	$s2 = strtr($s2, ["Č" => "?", "Ć" => "?", "č" => "?", "ć" => "?", "đ" => "?"]);
	return strtolower($s1) == strtolower($s2);
}


if ($_POST['akcija']=="import_eunsa" && check_csrf_token()) {
	if ($_POST['fakatradi'] == 1) $f=true; else $f=false;
	
	$id = 7795;
	$prethodna_godina = 16;
	$godina = 17;
	
	$pocni_od = int_param('pocni_od');
	
	$svi_logini = db_query_varray("SELECT login FROM auth");
	
	if (!$f) print "<table><tr><td><b>Zamger ID</b></td><td><b>Ime</b></td><td><b>Prezime</b></td><td><b>Ime oca</b></td><td><b>Novi login</b></td><td><b>Broj indexa</b></td><td><b>Stari login</b></td><td><b>JMBG</b></td><td><b>OID</b></td></tr>\n";
	
	foreach(explode("\n", $_POST['csv']) as $line) {
		if (empty(trim($line))) continue;
		$i++;
		if ($i==1) continue;
		$parts = explode(",", trim($line));
		
		if ($id < $pocni_od) {
			$id++; continue;
		}
		
		$prezime = $parts[1];
		$ime = $parts[2];
		
		print "$id. $prezime $ime<br>\n";
		
		$jmbg = $parts[4];
		if ($parts[5] == "Muški") $spol='M'; else $spol='Z';
		$oid = $parts[6];
		$imeoca = $parts[7];
		$datum_rodjenja = substr($parts[9],0,10);
		if ($parts[10] == "Sarajevo" && $parts[12] == "Centar" || $parts[10] == "Centar Sarajevo")
			$mjesto = 139;
		else {
			$mjesto = db_get("SELECT id FROM mjesto WHERE naziv='" . $parts[10] . "'");
			if (!$mjesto)
				print "GRESKA: Nepoznato mjesto " . $parts[10] . "<br>\n";
		}
		$adresa = str_replace("\\\\", ",", $parts[16]);
		if ($parts[18] == "Sarajevo" && $parts[20] == "Centar" || $parts[18] == "Sarajevo" && $parts[20] == "Centar Sarajevo" ||  $parts[18] == "Centar Sarajevo")
			$adresa_mjesto = 139;
		else if ($parts[18] == "Sarajevo" && $parts[20] == "Novo Sarajevo" || $parts[18] == "Novo Sarajevo")
			$adresa_mjesto = 199;
		else if ($parts[18] == "Sarajevo" && $parts[20] == "Novi Grad" || $parts[18] == "Sarajevo" && $parts[20] == "Novi Grad Sarajevo" || $parts[18] == "Novi Grad")
			$adresa_mjesto = 206;
		else if ($parts[18] == "Sarajevo" && $parts[20] == "Stari Grad" || $parts[18] == "Sarajevo" && $parts[20] == "Stari Grad Sarajevo" || $parts[18] == "Stari Grad")
			$adresa_mjesto = 215;
		else if ($parts[18] == "Sarajevo" && $parts[20] == "Vogošća")
			$adresa_mjesto = 32;
		else if ($parts[18] == "Sarajevo" && $parts[20] == "Ilidža")
			$adresa_mjesto = 13;
		else if ($parts[18] == "Sarajevo") {
			print "GRESKA: Nepoznato Sarajevo " . $parts[20] . "<br>\n";
		}
		else {
			$adresa_mjesto = db_get("SELECT id FROM mjesto WHERE naziv='" . $parts[18] . "'");
			if (!$adresa_mjesto)
				print "GRESKA: Nepoznato adresa mjesto " . $parts[18] . "<br>\n";
		}
		$drzavljanstvo = db_get("SELECT id FROM drzava WHERE naziv LIKE '" . $parts[23] . "'");
		if (!$drzavljanstvo)
			print "GRESKA: Nepoznato drzavljanstvo " . $parts[23] . "<br>\n";
		$telefon = $parts[24];
		
		$brindexa = $parts[29];
		
		$zanimanje_oca = $parts[33];
		
		if ($parts[34] == "bez zaposlenja") {
			$status_aktivnosti_roditelja = 2;
			$status_zaposlenosti_roditelja = 0;
		}
		else if ($parts[34] == "Penzioner") {
			$status_aktivnosti_roditelja = 3;
			$status_zaposlenosti_roditelja = 0;
		}
		else if (!empty(trim($parts[34]))) {
			$status_aktivnosti_roditelja = 1;
			$status_zaposlenosti_roditelja = 2;
		}
		else {
			$status_aktivnosti_roditelja = 0;
			$status_zaposlenosti_roditelja = 0;
		}
		$ime_majke = fix_ime($parts[35]);
		if (upitnik_like($parts[0], $parts[36]))
			$prezime_majke = $prezime;
		else
			$prezime_majke = fix_ime($parts[36]);
		if ($parts[48] == "Bošnjaci")
			$nacionalnost = 1;
		else if ($parts[48] == "Srbi")
			$nacionalnost = 2;
		else if ($parts[48] == "Hrvat")
			$nacionalnost = 3;
		else if ($parts[48] == "Ostali - Bosanci")
			$nacionalnost = 9;
		else
			$nacionalnost = 6;
		
		$sql = "INSERT INTO osoba SET id=$id, prezime='$prezime', ime='$ime', jmbg='$jmbg', spol='$spol', imeoca='$imeoca', prezimeoca='$prezime', imemajke='$ime_majke', prezimemajke='$prezime_majke', datum_rodjenja='$datum_rodjenja', mjesto_rodjenja='$mjesto', adresa='$adresa', adresa_mjesto='$adresa_mjesto', drzavljanstvo=$drzavljanstvo, telefon='$telefon', brindexa='$brindexa', zanimanje_roditelja='$zanimanje_oca', status_aktivnosti_roditelja=$status_aktivnosti_roditelja, status_zaposlenosti_roditelja=$status_zaposlenosti_roditelja, nacionalnost=$nacionalnost, oid='$oid'";
		
		if ($f) db_query($sql);
		
		// Škola
		
		$ime_skole = str_replace("?", "%", $parts[51]);
		$ime_skole = str_replace("\"", "", $ime_skole);
		$skola = false;
		if ($ime_skole == "Srednja medicinska škola - Jezero Sarajevo")
			$skola = 920;
		else if ($ime_skole == "Franjevačka klasična gimnazija Visoko")
			$skola = 579;
		else
			$skola = db_get("SELECT id FROM srednja_skola WHERE naziv LIKE '$ime_skole';");
		if (!$skola)
			print "Greška: Nepoznata škola $ime_skole<br>\n";
		
		if ($skola) {
			$sql = "INSERT INTO uspjeh_u_srednjoj SET osoba=$id, srednja_skola=$skola, godina=$prethodna_godina;";
			
			if ($f) db_query($sql);
		}
		
		
		// Login
		
		$trans = array("č"=>"c", "ć"=>"c", "đ"=>"d", "š"=>"s", "ž"=>"z", "Č"=>"C", "Ć"=>"C", "Đ"=>"D", "Š"=>"S", "Ž"=>"Z");
		$ime_login = preg_replace("/\W/", "", strtolower(strtr($ime, $trans)));
		$prezime_login = preg_replace("/\W/", "", strtolower(strtr($prezime, $trans)));
		$br = 1;
		do {
			$login = substr($ime_login,0,1).substr($prezime_login,0,9) . $br;
			$br++;
		} while(in_array($login, $svi_logini));
		
		$sql = "INSERT INTO auth SET id=$id, login='$login', aktivan=1;";
		
		$stari_login = substr($ime_login,0,1).substr($prezime_login,0,1) . $brindexa;
		
		if ($f) db_query($sql);
		if ($f) db_query("INSERT INTO privilegije SET osoba=$id, privilegija='student'");
		
		
		// Email
		
		$email = $login . "@etf.unsa.ba";
		$sql = "INSERT INTO email SET osoba=$id, adresa='$email', sistemska=1;";
		if ($f) db_query($sql);
		$email2 = $parts[25];
		if (!empty(trim($email2))) {
			$sql = "INSERT INTO email SET osoba=$id, adresa='$email2', sistemska=0;";
			if ($f) db_query($sql);
		}
		
		// Upis u prvu
		
		if ($parts[26] == "Redovni samofinansirajući studij")
			$nacin_studiranja = 3;
		else if ($parts[26] == "Redovni studij")
			$nacin_studiranja = 1;
		else {
			$nacin_studiranja = 4;
			print "GRESKA: Nepoznat način studiranja " . $parts[26] . "<br>\n";
		}
		
		if ($parts[27] == "Računarstvo i informatika")
			$studij = 2;
		else if ($parts[27] == "Elektroenergetika")
			$studij = 4;
		else if ($parts[27] == "Automatika i elektronika")
			$studij = 3;
		else if ($parts[27] == "Telekomunikacije")
			$studij = 5;
		else if ($parts[27] == "Razvoj softvera")
			$studij = 22;
		else {
			$studij = 22;
			print "GRESKA: Nepoznat studij " . $parts[27] . "<br>\n";
		}
		
		if (!$f) print "<tr><td>$id</td><td>$ime</td><td>$prezime</td><td>$imeoca</td><td>$login</td><td>$brindexa</td><td>$stari_login</td><td>$jmbg</td><td>$oid</td></tr>\n";
		
		if ($f) {
			$enrollment = array_to_object(["student" => ["id" => $id], "Programme" => ["id" => $studij], "semester" => 1, "AcademicYear" => ["id" => $godina ], "EnrollmentType" => ["id" => $nacin_studiranja], "repeat" => false, "status" => 0 /* Normal student */, "whichTime" => 1, "dryRun" => false]);
			$newEnrollment = api_call("enrollment/$id", $enrollment, "POST");
			if ($_api_http_code == "201") {
				foreach ($newEnrollment['enrollCourses'] as $cuy) {
					$result = api_call("course/" . $cuy['CourseUnit']['id'] . "/" . $cuy['AcademicYear']['id'] . "/enroll/$id", [], "POST");
					if ($_api_http_code != "201") {
						print "GRESKA: Neuspješan upis studenta $ime $prezime na predmet " . $cuy['courseName'] . "<br>\n";
						print_r($result);
					}
				}
			} else {
				print "GRESKA: Neuspješan upis studenta $ime $prezime na studij $studij<br>\n";
				print_r($newEnrollment);
			}
		}
		
		$id++;
		
		if ($f && $i % 50 == 0) {
			?>
			<?=genform("POST")?>
			<input type="hidden" name="pocni_od" value="<?=$id?>">
			<input type="hidden" name="fakatradi" value="1">
			<input type="submit" value=" Fakat radi ">
			</form>
			<?
			break;
		}
	}
	
	if (!$f) {
		?>
		</table>
		<?=genform("POST")?>
		<input type="hidden" name="fakatradi" value="1">
		<input type="submit" value=" Fakat radi ">
		</form>
		<?
	}
} else {
	?>
	<?=genform("POST")?>
	<input type="hidden" name="akcija" value="import_eunsa">
	<input type="hidden" name="fakatradi" value="0">
	CSV:<br>
	<textarea name="csv"></textarea>
	<input type="submit" value=" Uvezi podatke iz eUpis aplikacije ">
	</form>
	<?
}

// Kraj ADMIN/MISC
print "<hr/>\n";

}

?>
