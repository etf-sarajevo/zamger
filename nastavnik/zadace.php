<?

// NASTAVNIK/ZADACE - kreiranje zadaća i masovni unos



function nastavnik_zadace() {

global $userid,$user_siteadmin,$conf_files_path;

require("lib/manip.php");
require("lib/autotest.php");

global $mass_rezultat; // za masovni unos studenata u grupe
global $_lv_; // radi autogenerisanih formi

// Parametri potrebni za Moodle integraciju
global $conf_moodle, $conf_moodle_url, $conf_moodle_db, $conf_moodle_prefix, $conf_moodle_reuse_connection, $conf_moodle_dbhost, $conf_moodle_dbuser, $conf_moodle_dbpass;
global $__lv_connection, $conf_use_mysql_utf8;



// Parametri
$predmet = intval($_REQUEST['predmet']);
$ag = intval($_REQUEST['ag']);

// Naziv predmeta
$q5 = myquery("select naziv from predmet where id=$predmet");
if (mysql_num_rows($q5)<1) {
	biguglyerror("Nepoznat predmet");
	zamgerlog("ilegalan predmet $predmet",3); //nivo 3: greska
	zamgerlog2("nepoznat predmet", $predmet);
	return;
}
$predmet_naziv = mysql_result($q5,0,0);


// Da li korisnik ima pravo ući u modul?

if (!$user_siteadmin) {
	$q10 = myquery("select nivo_pristupa from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
	if (mysql_num_rows($q10)<1 || mysql_result($q10,0,0)=="asistent") {
		zamgerlog("nastavnik/ispiti privilegije (predmet pp$predmet)",3);
		zamgerlog2("nije nastavnik na predmetu", $predmet, $ag);
		biguglyerror("Nemate pravo pristupa ovoj opciji");
		return;
	} 
}



// Dozvoljene ekstenzije

$q13 = myquery("select naziv from ekstenzije");
$dozvoljene_ekstenzije = array();
while ($r13 = mysql_fetch_row($q13)) {
	array_push($dozvoljene_ekstenzije, $r13[0]);
}

// Da li predmet posjeduje komponente za zadaće?
$q15 = myquery("select k.id, k.naziv from komponenta as k, tippredmeta_komponenta as tpk, akademska_godina_predmet as agp where agp.akademska_godina=$ag and agp.predmet=$predmet and agp.tippredmeta=tpk.tippredmeta and tpk.komponenta=k.id and k.tipkomponente=4");
if (mysql_num_rows($q15)<1) {
	zamgerlog("ne postoji komponenta za zadace na predmetu pp$predmet ag$ag", 3);
	zamgerlog2("ne postoji komponenta za zadace", $predmet, $ag);
	niceerror("U sistemu bodovanja za ovaj predmet nije definisana nijedna komponenta zadaće.");
	print "<p>Da biste nastavili, promijenite <a href=\"?sta=nastavnik/tip?predmet=$predmet&ag=$ag\">sistem bodovanja</a> za ovaj predmet.</p>\n";
	return;
}
if (mysql_num_rows($q15)>1) {
	niceerror("U sistemu bodovanja za ovaj predmet je definisano više od jedne komponente za zadaće.");
	print "<p>Ovaj modul trenutno podržava samo jednu komponentu zadaća. Ako imate potrebu za rad sa više od jedne komponente zadaća istovremeno, kontaktirajte administratora Zamgera. U suprotnom, provjerite <a href=\"?sta=nastavnik/tip?predmet=$predmet&ag=$ag\">sistem bodovanja</a> za ovaj predmet za slučaj da je ova situacija posljedica greške.</p>\n";
	print "<p>Koristićemo komponentu označenu nazivom: <b>".mysql_result($q15,0,1)."</b></p>";
}
$komponenta_za_zadace = mysql_result($q15,0,0);

?>

<p>&nbsp;</p>

<p><h3><?=$predmet_naziv?> - Zadaće</h3></p>

<?

// Prijem podataka o prepisivanju

if ($_POST['akcija'] == "prepisivanje" && check_csrf_token()) {
	$doit = isset($_POST['fakatradi']);
	$ulaz = $_POST['rezultati'];
	
	if (substr($ulaz,0,8) == "===c9===") {
		foreach(explode ("\n", $ulaz) as $red) {
			$red = trim($red);
			if ($red == "===c9===") continue;
			if (empty($red)) continue;
			list($username,$zadaca,$zadatak,$stuff) = explode("||", $red);

			$username = my_escape($username);
			$zadaca=intval($zadaca);
			$zadatak=intval($zadatak);

			$comments = explode(",", $stuff);

			$q10 = myquery("SELECT id FROM auth WHERE login='$username'");
			if (mysql_num_rows($q10) < 1) {
				print "--- Nepoznat username $username<br>";
				continue;
			}
			$student = mysql_result($q10,0,0);
			
			$q5 = myquery("SELECT COUNT(*) FROM student_predmet sp, ponudakursa pk WHERE sp.student=$student AND sp.predmet=pk.id AND pk.predmet=$predmet AND pk.akademska_godina=$ag");
			if (mysql_result($q5,0,0) == 0) continue;
			
			$q20 = myquery("SELECT izvjestaj_skripte, komentar, filename, status FROM zadatak WHERE zadaca=$zadaca AND redni_broj=$zadatak AND student=$student ORDER BY id DESC LIMIT 1");
			if (mysql_num_rows($q20) < 1) {
				print "--- Username $username nije poslao zadaću $zadaca / $zadatak<br>\n";
				continue;
			}
			$status = mysql_result($q20,0,3);
			if ($status == 4) {
				$status = 2;
			} else {
				print "--- Username $username status $status zadaća $zadaca / $zadatak<br>\n";
			}
			$izvjestaj_skripte = mysql_real_escape_string(mysql_result($q20,0,0));
			$komentar = mysql_real_escape_string(mysql_result($q20,0,1));
			if (preg_match("/testova \([\d\.]+ bodova/", $komentar)) $komentar="";
			$filename = mysql_real_escape_string(mysql_result($q20,0,2));
			if (in_array("ignore", $comments)) {
				if ($doit) $q30 = myquery("INSERT INTO zadatak SET zadaca=$zadaca, redni_broj=$zadatak, student=$student, izvjestaj_skripte='$izvjestaj_skripte', filename='$filename', status=5, komentar='c9 ignore', vrijeme=NOW()");
				else print "$username: $zadatak, c9 ignore<br>";
			} else if (in_array("nema", $comments)) {
				if ($doit) $q30 = myquery("INSERT INTO zadatak SET zadaca=$zadaca, redni_broj=$zadatak, student=$student, izvjestaj_skripte='$izvjestaj_skripte', filename='$filename', status=5, komentar='nema na c9', vrijeme=NOW()");
				else print "$username: $zadatak, c9 NEMA!<br>";
			} else {
				foreach ($comments as $comment)
					if (!empty($comment)) $komentar = $komentar . "\nc9 $comment";
				if ($doit) $q30 = myquery("INSERT INTO zadatak SET zadaca=$zadaca, redni_broj=$zadatak, student=$student, izvjestaj_skripte='$izvjestaj_skripte', filename='$filename', status=2, komentar='$komentar', vrijeme=NOW()");
				else print "$username: $zadatak, $komentar<br>";
			}
		}
		if (!$doit) {
			print genform("POST");
			?>
			<input type="submit" name="fakatradi" value="Kreni">
			</form>
			<?
		}
		return;
	}
	
	
	function dajIme($zadatak, $predmet, $ag) {
		$q20 = myquery("SELECT o.id, o.ime, o.prezime FROM osoba o, zadatak z WHERE z.id=$zadatak AND z.student=o.id");
		$student = mysql_result($q20,0,0);
		$imestudenta = mysql_result($q20,0,2) . " " . mysql_result($q20,0,1);
		//print "Zadatak: $zadatak<br>\n";
		$q30 = myquery("SELECT l.naziv FROM labgrupa l, student_labgrupa sl WHERE sl.student=$student AND sl.labgrupa=l.id AND l.predmet=$predmet AND l.akademska_godina=$ag AND l.virtualna=0");
		if (mysql_num_rows($q30) > 0) {
			list($ime, $broj) = explode(" ", mysql_result($q30,0,0));
			if ($ime == "Grupa" && $broj === intval($broj))
				$grupa = "G$broj";
			else if ($ime == "Ponovci" && $broj === intval($broj))
				$grupa = "P$broj";
			else if ($ime == "Grupa" || $ime == "Ponovci" || substr($broj,0,1) == "P")
				$grupa = $broj;
			else
				$grupa = "$ime $broj";
			$imestudenta .= " " .$grupa;
		} else
			$imestudenta .= " P"; // Ponovci bez grupe
		return array($student, $imestudenta);
	}
	
//	print "Ulaz: $ulaz<br>";
	$prepisivanje = array();
	$vremena = array();
	$imena = array();
	$procenti = array();
	foreach(explode ("\n", $ulaz) as $red) {
		$red = trim($red);
		list($procenat, $lijevi, $kk, $desni) = explode(" ", $red);
		$procenat = intval($procenat);
		$lijevi = str_replace(array("(",")",".zip"), "", $lijevi);
		$desni = str_replace(array("(",")",".zip"), "", $desni);
		if (intval($lijevi) == 0 || intval($desni) == 0) {
			print $procenat . " (";
			if (intval($lijevi) > 0) {
				list($student, $nekiime) = dajIme(intval($lijevi), $predmet, $ag);
				print $nekiime;
			}
			else print $lijevi;
			print ") <=> (";
			if (intval($desni) > 0) {
				list($student, $nekiime) = dajIme(intval($desni), $predmet, $ag);
				print $nekiime;
			}
			else print $desni;
			print ")<br>";
			continue;
		}
		$procenti["$lijevi-$desni"] = $procenat;

		$q10 = myquery("SELECT UNIX_TIMESTAMP(vrijeme),zadaca,redni_broj FROM zadatak WHERE id=$lijevi");
		$lijevovrijeme = mysql_result($q10,0,0);
		$zadaca = mysql_result($q10,0,1);
		$zadatak = mysql_result($q10,0,2);
		$q10 = myquery("SELECT UNIX_TIMESTAMP(vrijeme) FROM zadatak WHERE id=$desni");
		$desnovrijeme = mysql_result($q10,0,0);
		
		if ($lijevovrijeme < $desnovrijeme) { $manji=$lijevi; $manjevrijeme=$lijevovrijeme; $veci=$desni; }
		else { $manji=$desni; $manjevrijeme=$desnovrijeme; $veci=$lijevi; }
		
		if (array_key_exists($manji, $prepisivanje)) {
			if (!in_array($veci, $prepisivanje[$manji]))
				array_push($prepisivanje[$manji], $veci);
				
		} else if (array_key_exists($veci, $prepisivanje)) {
			$prepisivanje[$manji] = $prepisivanje[$veci];
			unset($prepisivanje[$veci]);
			array_unshift($prepisivanje[$manji], $veci);
			$vremena[$manji] = $manjevrijeme;

		} else {
			$dodano = false;
			foreach($prepisivanje as $k => $v) {
				foreach ($v as $broj) {
					if ($broj == $manji) {
						if (!in_array($veci, $prepisivanje[$k]))
							array_push($prepisivanje[$k], $veci);
						$dodano = true;
						break;
					}
				}
				if (!$dodano) {
					$nasao = false;
					foreach ($v as $broj) {
						if ($broj == $veci) {
							if ($vremena[$k] <= $manjevrijeme) {
								if (!in_array($manji, $prepisivanje[$k]))
									array_push($prepisivanje[$k], $manji);
								$dodano = true;
								break;
							} else {
								$prepisivanje[$manji] = $prepisivanje[$k];
								unset($prepisivanje[$k]);
								array_unshift($prepisivanje[$manji], $k);
								$vremena[$manji] = $manjevrijeme;
								$dodano = true;
								break;
							}
						}
					}
				}
				if ($dodano) break;
			}
			if (!$dodano) {
				$prepisivanje[$manji] = array( $veci );
				$vremena[$manji] = $manjevrijeme;
			}
		}
	}
	
	foreach ($prepisivanje as $manji => $ostali) {
		list($student, $imestudenta) = dajIme($manji, $predmet, $ag);
		print "<a href=\"?sta=saradnik/zadaca&amp;student=$student&amp;zadaca=$zadaca&amp;zadatak=$zadatak\" target=\"_new\">$imestudenta</a> => {";
		//print " => { ";
		$prvi = true;
		foreach($ostali as $veci) {
			list($student, $nekiime) = dajIme($veci, $predmet, $ag); 
			print "<a href=\"?sta=saradnik/zadaca&amp;student=$student&amp;zadaca=$zadaca&amp;zadatak=$zadatak\" target=\"_new\">$nekiime</a>";
			if (array_key_exists("$manji-$veci", $procenti)) print " (".$procenti["$manji-$veci"]."%)";
			if (array_key_exists("$veci-$manji", $procenti)) print " (".$procenti["$veci-$manji"]."%)";
			print ", ";
			if ($prvi) { $imeveci = $nekiime; $prvi = false; }
//			else print "$veci, ";
			if ($doit) {
				$q40 = myquery("SELECT zadaca, redni_broj, student, izvjestaj_skripte, filename, status FROM zadatak WHERE id=$veci");
				if (mysql_result($q40,0,5) == 4)
					$q50 = myquery("INSERT INTO zadatak SET zadaca=".mysql_result($q40,0,0).", redni_broj=".mysql_result($q40,0,1).", student=".mysql_result($q40,0,2).", status=2, bodova=0, izvjestaj_skripte='".mysql_real_escape_string(mysql_result($q40,0,3))."', vrijeme=NOW(), komentar='".$imestudenta."', filename='".mysql_real_escape_string(mysql_result($q40,0,4))."', userid=0");
				else
					print "(preskačem), ";
			}
		}
		if ($doit) {
			$q40 = myquery("SELECT zadaca, redni_broj, student, izvjestaj_skripte, filename, status FROM zadatak WHERE id=$manji");
			if (mysql_result($q40,0,5) == 4)
				$q50 = myquery("INSERT INTO zadatak SET zadaca=".mysql_result($q40,0,0).", redni_broj=".mysql_result($q40,0,1).", student=".mysql_result($q40,0,2).", status=2, bodova=0, izvjestaj_skripte='".mysql_real_escape_string(mysql_result($q40,0,3))."', vrijeme=NOW(), komentar='".$imeveci."', filename='".mysql_real_escape_string(mysql_result($q40,0,4))."', userid=0");
			else
				print "(preskačem), ";
		}
		
		print "}<br>";
	}
	if (!$doit) {
		print genform("POST");
		?>
		<input type="submit" name="fakatradi" value="Kreni">
		</form>
		<?
	}
	return;
}

if ($_GET['akcija'] == "prepisivanje") {
	print genform("POST");
	?>
<textarea name="rezultati" cols="50" rows="10"></textarea><br>
<input type="submit" value=" Šalji ">
</form>
	<?
	return 0;
}

# Masovni unos zadaća

if ($_POST['akcija'] == "massinput" && strlen($_POST['nazad'])<1 && check_csrf_token()) {

	if ($_POST['fakatradi'] != 1) $ispis=1; else $ispis=0;

	// Provjera ostalih parametara
	$zadaca = intval($_REQUEST['_lv_column_zadaca']);
	$zadatak = intval($_REQUEST['zadatak']);

	$q20 = myquery("select naziv,zadataka,bodova,komponenta,predmet,akademska_godina from zadaca where id=$zadaca");
	if (mysql_num_rows($q20)<1) {
		zamgerlog("nepostojeca zadaca $zadaca",3); // 3 = greška
		zamgerlog2("nepostojeca zadaca", $zadaca);
		niceerror("Morate najprije kreirati zadaću");
		print "\n<p>Koristite formular &quot;Kreiranje zadaće&quot; koji se nalazi na prethodnoj stranici. Ukoliko ne vidite nijednu zadaću na spisku &quot;Postojeće zadaće&quot;, koristite dugme Refresh vašeg web preglednika.</p>\n";
		return;
	}
	if (mysql_result($q20,0,1)<$zadatak) {
		zamgerlog("zadaca $zadaca nema $zadatak zadataka",3);
		zamgerlog2("zadaca nema toliko zadataka", $zadaca, $zadatak);
		niceerror("Zadaća \"".mysql_result($q20,0,0)."\" nema $zadatak zadataka.");
		return;
	}
	$maxbodova=mysql_result($q20,0,2);
	$komponenta=mysql_result($q20,0,3);

	// Provjera spoofanja zadaće
	if ($predmet != mysql_result($q20,0,4) || $ag != mysql_result($q20,0,5)) {
		zamgerlog("zadaca z$zadaca nije u predmetu pp$predmet",3);
		zamgerlog2("id zadace i predmeta se ne poklapaju", $zadaca, $predmet, $ag);
		niceerror("Pogresan ID zadace!");
		return;
	}

	if ($ispis) {
		?>Akcije koje će biti urađene:<br/><br/>
		<?=genform("POST")?>
		<input type="hidden" name="fakatradi" value="1">
		<input type="hidden" name="_lv_column_zadaca" value="<?=$zadaca?>">
		<table border="0" cellspacing="1" cellpadding="2">
		<!-- FIXME: prebaciti stilove u CSS? -->
		<thead>
		<tr bgcolor="#999999">
			<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;color:white;">Prezime</font></td>
			<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;color:white;">Ime</font></td>
			<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;color:white;">Bodovi / Komentar</font></td>
		</tr>
		</thead>
		<tbody>
		<?
	}

	$greska=mass_input($ispis); // Funkcija koja parsira podatke

	if (count($mass_rezultat)==0) {
		niceerror("Niste unijeli ništa.");
		return;
	}

	foreach ($mass_rezultat['ime'] as $student=>$ime) {
		$prezime = $mass_rezultat['prezime'][$student];
		$bodova = $mass_rezultat['podatak1'][$student];
		$bodova = str_replace(",",".",$bodova);

		// Student neocijenjen (prazno mjesto za ocjenu)
		if (floatval($bodova)==0 && strpos($bodova,"0")===FALSE) {
			if ($ispis)
				print "Student '$prezime $ime' - nema zadaću (nije unesen broj bodova $bodova)<br/>";
			continue;
		}

		// Bodovi moraju biti manji od maximalnih borova
		$bodova = floatval($bodova);
		if ($bodova>$maxbodova) {
			if ($ispis) {
				print "-- Studenta '$prezime $ime' ima $bodova bodova što je više od maksimalnih $maxbodova<br/>";
				//$greska=1;
				continue;
			}
		}

		// Zaključak
		if ($ispis) {
			print "Student '$prezime $ime' - zadaća $zadaca, bodova $bodova<br/>";
		} else {
			// Odredjujemo zadnji filename
			$q25 = myquery("select filename from zadatak where zadaca=$zadaca and redni_broj=$zadatak and student=$student order by id desc limit 1");
			if (mysql_num_rows($q25)>0) {
				$filename=mysql_result($q25,0,0);
			} else $filename='';

			$status_pregledana = 5; // status 5: pregledana
			$q30 = myquery("insert into zadatak set zadaca=$zadaca, redni_broj=$zadatak, student=$student, status=$status_pregledana, bodova=$bodova, vrijeme=NOW(), filename='$filename', userid=$userid"); 
			zamgerlog2("bodovanje zadace", $student, $zadaca, $zadatak, $bodova);

			// Treba nam ponudakursa za update komponente
			$q35 = myquery("select sp.predmet from student_predmet as sp, ponudakursa as pk where sp.student=$student and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
			$ponudakursa = mysql_result($q35,0,0);

			update_komponente($student,$ponudakursa,$komponenta); // update statistike
		}
	}

	if ($ispis) {
		if ($greska == 0) {
			?>
			</tbody></table>
			<p>Potvrdite upis ispita i bodova ili se vratite na prethodni ekran.</p>
			<p><input type="submit" name="nazad" value=" Nazad "> <input type="submit" value=" Potvrda"></p>
			</form>
			<? 
		} else {
			?>
			</tbody></table>
			<p>U unesenim podacima ima grešaka. Da li ste izabrali ispravan format ("Prezime[TAB]Ime" vs. "Prezime Ime")? Vratite se nazad kako biste ovo popravili.</p>
			<p><input type="submit" name="nazad" value=" Nazad "></p>
			</form>
			<? 
		}
		return;
	} else {
		zamgerlog("masovno upisane zadaće na predmet pp$predmet, zadaća z$zadaca, zadatak $zadatak",2); // 2 = edit
		?>
		Bodovi iz zadaća su upisani.
		<script language="JavaScript">
		location.href='?sta=nastavnik/zadace&predmet=<?=$predmet?>&ag=<?=$ag?>';
		</script>
		<?
	}
}



// Akcija za kreiranje nove, promjenu postojeće ili brisanje zadaće

if ($_POST['akcija']=="edit" && $_POST['potvrdabrisanja'] != " Nazad " && check_csrf_token()) {
	$edit_zadaca = intval($_POST['zadaca']);
	
	// Prava pristupa
	if ($edit_zadaca>0) {
		$q86 = myquery("select predmet, akademska_godina from zadaca where id=$edit_zadaca");
		if (mysql_num_rows($q86)<1) {
			niceerror("Nepostojeća zadaća sa IDom $edit_zadaca");
			zamgerlog("promjena nepostojece zadace $edit_zadaca", 3);
			zamgerlog2("nepostojeca zadaca", $edit_zadaca);
			return 0;
		}
		if (mysql_result($q86,0,0)!=$predmet || mysql_result($q86,0,1)!=$ag) {
			niceerror("Zadaća nije sa izabranog predmeta");
			zamgerlog("promjena zadace: zadaca $edit_zadaca nije sa predmeta pp$predmet", 3);
			zamgerlog2("id zadace i predmeta se ne poklapaju", $edit_zadaca, $predmet, $ag);
			return 0;
		}
	}

	// Brisanje postavke zadaće (a ne čitave zadaće!)
	if ($_POST['dugmeobrisi'] == "Obriši") {
		$q100 = myquery("select postavka_zadace from zadaca where id=$edit_zadaca");
		$filepath = "$conf_files_path/zadace/$predmet-$ag/postavke/".mysql_result($q100,0,0);
		unlink ($filepath);
		$q110 = myquery("update zadaca set postavka_zadace='' where id=$edit_zadaca");
		nicemessage ("Postavka zadaće obrisana");
		print "<a href=\"?sta=nastavnik/zadace&predmet=$predmet&ag=$ag&_lv_nav_id=$edit_zadaca\">Nazad</a>\n";
		zamgerlog("obrisana postavka zadace z$edit_zadaca",2);
		zamgerlog2("obrisana postavka zadace", $edit_zadaca);
		return;
	}

	// Brisanje zadaće
	if ($_POST['brisanje'] == " Obriši ") {
		if ($edit_zadaca <= 0) return; // Ne bi se smjelo desiti
		$q86 = myquery("select predmet, akademska_godina, komponenta from zadaca where id=$edit_zadaca");
		if (mysql_num_rows($q86)<1) {
			niceerror("Nepostojeća zadaća sa IDom $edit_zadaca");
			zamgerlog("brisanje nepostojece zadace $edit_zadaca", 3);
			zamgerlog2("nepostojeca zadaca", $edit_zadaca);
			return 0;
		}
		if (mysql_result($q86,0,0)!=$predmet || mysql_result($q86,0,1)!=$ag) {
			niceerror("Zadaća nije sa izabranog predmeta");
			zamgerlog("brisanje zadace: zadaca $edit_zadaca nije sa predmeta pp$predmet", 3);
			zamgerlog2("id zadace i predmeta se ne poklapaju", $edit_zadaca, $predmet, $ag);
			return 0;
		}
		$komponenta = mysql_result($q86,0,2);
	
		if ($_POST['potvrdabrisanja']==" Briši ") {
			// Brišemo srodne testove
			$q84 = myquery("delete from autotest_replace where zadaca=$edit_zadaca");
			$q85 = myquery("delete from autotest_rezultat where autotest in (select id from autotest where zadaca=$edit_zadaca)");
			$q86 = myquery("delete from autotest where zadaca=$edit_zadaca");
			
			// Update komponente za sve studente koji imaju unesene bodove za zadaću
			$q86a = myquery("select distinct zk.student, pk.id from zadatak as zk, student_predmet as sp, ponudakursa as pk where zk.zadaca=$edit_zadaca and zk.student=sp.student and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
			$broj_studenata = mysql_num_rows($q86a);
			$brojac=1;
			while ($r86a = mysql_fetch_row($q86a)) {
				$student = $r86a[0];
				$ponudakursa = $r86a[1];
				print "Ažuriram bodove za studenta $brojac od $brojstudenata<br />\n\n";

				update_komponente($student,$ponudakursa,$komponenta);
			}
			
			// Brišemo zadaću
			$q87 = myquery("delete from zadatak where zadaca=$edit_zadaca");
			$q88 = myquery("delete from zadaca where id=$edit_zadaca");
			
			zamgerlog("obrisana zadaca $edit_zadaca sa predmeta pp$predmet", 4);
			zamgerlog2("obrisana zadaca", $edit_zadaca);
			nicemessage ("Zadaća uspješno obrisana");
			?>
			<script language="JavaScript">
			location.href='?sta=nastavnik/zadace&predmet=<?=$predmet?>&ag=<?=$ag?>';
			</script>
			<?
			return;
		} else {
			$q96 = myquery("select count(*) from zadatak where zadaca=$edit_zadaca");
			$broj_zadataka = mysql_result($q96,0,0);
			$q97 = myquery("select count(*) from autotest where zadaca=$edit_zadaca");
			$broj_testova = mysql_result($q97,0,0);
			print genform("POST");
			?>
			Brisanjem zadaće obrisaćete i sve do sada unesene ocjene i poslane zadatke! Da li ste sigurni da to želite?<br>
			U pitanju je <b><?=$broj_zadataka?></b> jedinstvenih slogova u bazi!<br><br>
			<?
			
			if ($broj_testova > 0) {
				?>
				Također ćete obrisati i <b><?=$broj_testova?></b> testova.<br><br>
				<?
			}
			
			?>
			<input type="submit" name="potvrdabrisanja" value=" Briši ">
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input type="submit" name="potvrdabrisanja" value=" Nazad ">
			<?
			return;
		}
	}


	// Kreiranje ili izmjena zadaće

	$naziv = trim(my_escape($_POST['naziv']));
	$zadataka = intval($_POST['zadataka']);
	$bodova = floatval(str_replace(",",".",$_POST['bodova']));
	$dan = intval($_POST['day']);
	$mjesec = intval($_POST['month']);
	$godina = intval($_POST['year']);
	$sat = intval($_POST['sat']);
	$minuta = intval($_POST['minuta']);
	$sekunda = intval($_POST['sekunda']);
	if ($_POST['aktivna']) $aktivna=1; else $aktivna=0;
	if ($_POST['attachment']) $attachment=1; else $attachment=0;
	$programskijezik = intval($_POST['_lv_column_programskijezik']);
	if ($_POST['automatsko_testiranje']) $automatsko_testiranje=1; else $automatsko_testiranje=0;
	if ($_POST['readonly']) $readonly=1; else $readonly=0;

	$postavka_file = $_FILES['postavka_zadace_file']['name'];
	if ($postavka_file != "") {
		if (!file_exists("$conf_files_path/zadace/$predmet-$ag/postavke")) {
			mkdir("$conf_files_path/zadace/$predmet-$ag/postavke", 0755, true);
		}
		copy ($_FILES['postavka_zadace_file']['tmp_name'], "$conf_files_path/zadace/$predmet-$ag/postavke/$postavka_file");
		$sql_add_postavka_file = ", postavka_zadace = '$postavka_file'";
	} else
		$sql_add_postavka_file = "";

	if (intval($_POST['attachment']) == 1 && isset($_POST['dozvoljene_eks'])) {
		$ekstenzije = array_unique($_POST['dozvoljene_eks']);
		$dozvoljene_ekstenzije_selected = implode(',',$ekstenzije);
	} else {
		$dozvoljene_ekstenzije_selected = null;
	}

	// Provjera ispravnosti
	if (!preg_match("/\w/",$naziv)) {
		niceerror("Naziv zadaće nije dobar.");
		return 0;
	}
	if ($zadataka<=0 || $bodova<0 || $zadataka>100 || $bodova>100) {
		niceerror("Broj zadataka ili broj bodova nije dobar");
		return 0;
	}
	if (!checkdate($mjesec,$dan,$godina)) {
		niceerror("Odabrani datum je nemoguć");
		return 0;
	}
	if ($sat<0 || $sat>24 || $minuta<0 || $minuta>60 || $sekunda<0 || $sekunda>60) {
		niceerror("Vrijeme nije dobro");
		return 0;
	}
	$mysqlvrijeme = time2mysql(mktime($sat,$minuta,$sekunda,$mjesec,$dan,$godina));

	// Provjera duplog imena zadace
	$q90 = myquery("select count(*) from zadaca where naziv like '$naziv' and predmet=$predmet and akademska_godina=$ag and id!=$edit_zadaca");
	if (mysql_result($q90,0,0)>0) {
		niceerror("Zadaća pod imenom '$naziv' već postoji! Izaberite neko drugo ime.");
		zamgerlog("zadaca sa nazivom '$naziv' vec postoji", 3);
		return 0;
	}

	// Kreiranje nove
	if ($edit_zadaca==0) {
		// $komponenta_za_zadace određena na početku fajla
		$q92 = myquery("insert into zadaca set predmet=$predmet, akademska_godina=$ag, naziv='$naziv', zadataka=$zadataka, bodova=$bodova, rok='$mysqlvrijeme', aktivna=$aktivna, attachment=$attachment, programskijezik=$programskijezik, automatsko_testiranje=$automatsko_testiranje, dozvoljene_ekstenzije = '$dozvoljene_ekstenzije_selected', komponenta=$komponenta_za_zadace, readonly=$readonly $sql_add_postavka_file");
		$edit_zadaca = mysql_insert_id();
		if ($edit_zadaca == 0) {
			niceerror("Dodavanje zadaće nije uspjelo");
			zamgerlog("dodavanje zadace nije uspjelo pp$predmet, naziv '$naziv'",3);
			zamgerlog2("dodavanje zadace nije uspjelo", $predmet, $zadataka, $bodova, $naziv);
		} else {
			nicemessage("Kreirana nova zadaća '$naziv'");
			zamgerlog("kreirana nova zadaca z$edit_zadaca", 2);
			zamgerlog2("kreirana nova zadaca", $edit_zadaca);
		}

	// Izmjena postojece zadace
	} else {
		// Ako se smanjuje broj zadataka, moraju se obrisati bodovi
		$q94 = myquery("select zadataka, komponenta from zadaca where id=$edit_zadaca");
		$oldzadataka = mysql_result($q94,0,0);
		if ($zadataka<$oldzadataka) {
			// Prilikom brisanja svakog zadatka updatujemo komponentu studenta
			$komponenta = mysql_result($q94,0,1);
			$q96 = myquery("select id,student from zadatak where zadaca=$edit_zadaca and redni_broj>$zadataka and redni_broj<=$oldzadataka order by student");
			$oldstudent=0;
			while ($r96 = mysql_fetch_row($q96)) {
				$q97 = myquery("delete from zadatak where id=$r96[0]");
				if ($oldstudent!=0 && $oldstudent!=$r96[1])
					update_komponente($oldstudent,$predmet,$komponenta);
				$oldstudent=$r96[1];
			}
			if ($oldstudent!=0) { // log samo ako je bilo nesto
				zamgerlog("Smanjen broj zadataka u zadaci z$edit_zadaca", 4);
				zamgerlog2("smanjen broj zadataka u zadaci", $edit_zadaca);
			}
				
			// Brišemo i relevantne testove
			$q84 = myquery("delete from autotest_replace where zadaca=$edit_zadaca and zadatak>$zadataka");
			$q85 = myquery("delete from autotest_rezultat where autotest in (select id from autotest where zadaca=$edit_zadaca and zadatak>$zadataka)");
			$q86 = myquery("delete from autotest where zadaca=$edit_zadaca and zadatak>$zadataka");
		}

		$q94 = myquery("update zadaca set naziv='$naziv', zadataka=$zadataka, bodova=$bodova, rok='$mysqlvrijeme', aktivna=$aktivna, attachment=$attachment, programskijezik=$programskijezik, automatsko_testiranje=$automatsko_testiranje, dozvoljene_ekstenzije='$dozvoljene_ekstenzije_selected', readonly=$readonly $sql_add_postavka_file where id=$edit_zadaca");
		nicemessage("Ažurirana zadaća '$naziv'");
		zamgerlog("azurirana zadaca z$edit_zadaca", 2);
		zamgerlog2("azurirana zadaca", $edit_zadaca);
	}
}


// Akcija: AUTOTESTOVI

if ($_REQUEST['akcija'] == "autotestovi") {
	$zadaca = intval($_REQUEST['zadaca']);
	$backLink = "?sta=nastavnik/zadace&amp;predmet=$predmet&amp;ag=$ag";
	$linkPrefix = "$backLink&amp;zadaca=$zadaca&amp;akcija=autotestovi";
	$backLink = "<a href=\"?$backLink&amp;_lv_nav_id=$zadaca\">Nazad na popis zadaća</a>";

	// Provjera spoofinga zadaće
	$q10 = myquery("SELECT COUNT(*) FROM zadaca WHERE id=$zadaca AND predmet=$predmet AND akademska_godina=$ag");
	if (mysql_result($q10,0,0) == 0) {
		nicemessage("Nepoznat ID zadaće $zadaca.");
		return;
	}

	autotest_admin($zadaca, $linkPrefix, $backLink);

	return;
}



// Spisak postojećih zadaća

$_lv_["where:predmet"] = $predmet;
$_lv_["where:akademska_godina"] = $ag;
$_lv_["where:komponenta"] = $komponenta_za_zadace; // određena na početku fajla

print "Postojeće zadaće:<br/>\n";
print db_list("zadaca");


// Kreiranje nove zadace ili izmjena postojeće

$izabrana = intval($_REQUEST['_lv_nav_id']);
if ($izabrana==0) $izabrana=intval($edit_zadaca);
if ($izabrana==0) {
	?><p><hr/></p>
	<p><b>Kreiranje zadaće</b><br/>
	<?
	$znaziv=$zaktivna=$zattachment=$zjezik=$zreadonly="";
	$zzadataka=0; $zbodova=0;
	$tmpvrijeme=time();
} else {
	?><p><hr/></p>
	<p><b>Izmjena zadaće</b></p>
	<?
	$q100 = myquery("select predmet, akademska_godina, naziv, zadataka, bodova, rok, aktivna, programskijezik, attachment, dozvoljene_ekstenzije, postavka_zadace, automatsko_testiranje, readonly from zadaca where id=$izabrana");
	if ($predmet != mysql_result($q100,0,0) || $ag != mysql_result($q100,0,1)) {
		niceerror("Zadaća ne pripada vašem predmetu");
		zamgerlog("zadaca $izabrana ne pripada predmetu pp$predmet",3);
		zamgerlog2("id zadace i predmeta se ne poklapaju", $izabrana, $predmet, $ag);
		return;
	}

	$znaziv = mysql_result($q100,0,2);
	$zzadataka = intval(mysql_result($q100,0,3));
	$zbodova = floatval(mysql_result($q100,0,4));
	$tmpvrijeme = mysql2time(mysql_result($q100,0,5));
	if (mysql_result($q100,0,6)==1) $zaktivna="CHECKED"; else $zaktivna="";
	$zjezik = mysql_result($q100,0,7);
	if (mysql_result($q100,0,8)==1) $zattachment="CHECKED"; else $zattachment="";
	$dozvoljene_ekstenzije_selected = mysql_result($q100,0,9);
	$postavka_zadace = mysql_result($q100,0,10);
	$automatsko_testiranje = mysql_result($q100,0,11);
	if (mysql_result($q100,0,12)==1) $zreadonly="CHECKED"; else $zreadonly="";
}

$zdan = date('d',$tmpvrijeme);
$zmjesec = date('m',$tmpvrijeme);
$zgodina = date('Y',$tmpvrijeme);
$zsat = date('H',$tmpvrijeme);
$zminuta = date('i',$tmpvrijeme);
$zsekunda = date('s',$tmpvrijeme);



// JavaScript za provjeru validnosti forme
?>
<script language="JavaScript">
function IsNumeric(sText) {
   var ValidChars = "0123456789.";
   var IsNumber=true;
   var Char;

 
   for (i = 0; i < sText.length && IsNumber == true; i++) 
      { 
      Char = sText.charAt(i); 
      if (ValidChars.indexOf(Char) == -1) 
         {
         IsNumber = false;
         }
      }
   return IsNumber;0
   
}

function provjera() {
//	var forma=document.getElementById("kreiranje_zadace");
	var naziv=document.getElementById("naziv");
	if (parseInt(naziv.value.length)<1) {
		alert("Niste unijeli naziv");
		naziv.style.border=1;
		naziv.style.backgroundColor="#FF9999";
		naziv.focus();
		return false;
	}
	var zadataka=document.getElementById("zadataka");
	if (!IsNumeric(zadataka.value)) {
		alert("Neispravan broj zadataka!");
		zadataka.style.border=1;
		zadataka.style.backgroundColor="#FF9999";
		zadataka.focus();
		return false;
	}
	if (parseInt(zadataka.value)<=0) {
		alert("Broj zadataka u zadaći mora biti veći od nule, npr. 1");
		zadataka.style.border=1;
		zadataka.style.backgroundColor="#FF9999";
		zadataka.focus();
		return false;
	}
	var bodova=document.getElementById("bodova");
	if (!IsNumeric(bodova.value)) {
		alert("Neispravan broj bodova!");
		bodova.style.border=1;
		bodova.style.backgroundColor="#FF9999";
		bodova.focus();
		return false;
	}
	if (parseFloat(bodova.value)<0) {
		alert("Broj bodova koje nosi zadaća mora biti veći ili jednak nuli, npr. 2 boda");
		bodova.style.border=1;
		bodova.style.backgroundColor="#FF9999";
		bodova.focus();
		return false;
	}
	
	return true;
}

function onemoguci_ekstenzije(chk) {
	var attachment = document.getElementById("attachment");
	var dozvoljene_ekstenzije = document.getElementById("dozvoljene_ekstenzije");
	var jezik = document.getElementById("_lv_column_programskijezik");

	if (attachment.checked) {
		dozvoljene_ekstenzije.style.display = '';
	} else {
		dozvoljene_ekstenzije.style.display = 'none';
		for (i = 0; i < chk.length; i++) chk[i].checked = false;
	}
}
</script>
<?



// Forma za kreiranje zadaće

unset($_REQUEST['aktivna']);
unset($_REQUEST['attachment']);
unset($_REQUEST['automatsko_testiranje']);

print genform("POST", "kreiranje_zadace\" enctype=\"multipart/form-data\" onsubmit=\"return provjera();");

?>
<input type="hidden" name="akcija" value="edit">
<input type="hidden" name="zadaca" value="<?=$izabrana?>">
Naziv: <input type="text" name="naziv" id="naziv" size="30" value="<?=$znaziv?>"><br><br>

Broj zadataka: <input type="text" name="zadataka" id="zadataka" size="4" value="<?=$zzadataka?>">
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Max. broj bodova: <input type="text" name="bodova" id="bodova" size="3" value="<?=$zbodova?>"><br><br>

Rok za slanje: <?=datectrl($zdan,$zmjesec,$zgodina)?>
&nbsp;&nbsp; <input type="text" name="sat" size="1" value="<?=$zsat?>"> <b>:</b> <input type="text" name="minuta" size="1" value="<?=$zminuta?>"> <b>:</b> <input type="text" name="sekunda" size="1" value="<?=$zsekunda?>"> <br><br>

<input type="checkbox" name="aktivna" <?=$zaktivna?>> Aktivna
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
<input type="checkbox" name="readonly" <?=$zreadonly?>> Read-only
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
<input type="checkbox" value="1" id="attachment" onclick="onemoguci_ekstenzije(this.form.dozvoljene_eks)" name="attachment" <?=$zattachment?>> Slanje zadatka u formi attachmenta<br><br>

<span id="dozvoljene_ekstenzije" style="display:none" title="Oznacite željene ekstenzije">
Dozvoljene ekstenzije (Napomena: Ukoliko ne odaberete nijednu ekstenziju sve ekstenzije postaju dozvoljene): 
<? $dozvoljene_ekstenzije_selected=explode(',',$dozvoljene_ekstenzije_selected);
foreach($dozvoljene_ekstenzije as $doz_ext) { ?>
<input type="checkbox" name="dozvoljene_eks[]" <? if(in_array($doz_ext,$dozvoljene_ekstenzije_selected)) echo 'checked="checked"'?> value="<? echo $doz_ext; ?>" /> <? echo $doz_ext; ?>
<? } ?>
<br><br>
</span>

Programski jezik: <?=db_dropdown("programskijezik", $zjezik)?><br><br>

<?

if ($zjezik != 0) {// Ako nije definisan programski jezik, nećemo ni nuditi automatsko testiranje... ?
	if ($automatsko_testiranje == 1) $add_testiranje = "CHECKED"; else $add_testiranje = "";
	?>
	<input type="checkbox" name="automatsko_testiranje" <?=$add_testiranje?>> Automatsko testiranje<br>
	<a href="?sta=nastavnik/zadace&predmet=<?=$predmet?>&ag=<?=$ag?>&zadaca=<?=$izabrana?>&akcija=autotestovi">Kliknite ovdje da definišete testove</a><br><br>
	<?
}

?>

Postavka zadaće: 
<?
if ($postavka_zadace == "") {
	?><input type="file" name="postavka_zadace_file" size="45"><?
} else {
	?><a href="?sta=common/attachment&zadaca=<?=$izabrana?>&tip=postavka"><img src="images/16x16/preuzmi.png" width="16" height="16" border="0"> <?=$postavka_zadace?></a>
	<input type="submit" name="dugmeobrisi" value="Obriši">
	<?
}
?>
<br><br>

<input type="submit" value=" Pošalji "> <input type="reset" value=" Poništi ">
<?
if ($izabrana>0) {
	?><input type="submit" name="brisanje" value=" Obriši "><?
}
echo "<script> onemoguci_ekstenzije('');</script>";
?>
</form>
<?



/*
$_lv_["label:programskijezik"] = "Programski jezik";
$_lv_["label:zadataka"] = "Broj zadataka";
$_lv_["label:bodova"] = "Max. broj bodova";
$_lv_["label:attachment"] = "Slanje zadatka u formi attachmenta";
$_lv_["label:rok"] = "Rok za slanje";
$_lv_["hidden:vrijemeobjave"] = 1;
print db_form("zadaca");*/



// Formular za masovni unos zadaća

$format = intval($_POST['format']);
if (!$_POST['format']) {
	$q110 = myquery("select vrijednost from preference where korisnik=$userid and preferenca='mass-input-format'");
	if (mysql_num_rows($q110)>0) $format = mysql_result($q110,0,0);
	else //default vrijednost
		$format=0;
}

$separator = intval($_POST['separator']);
if (!$_POST['separator']) {
	$q120 = myquery("select vrijednost from preference where korisnik=$userid and preferenca='mass-input-separator'");
	if (mysql_num_rows($q120)>0) $separator = mysql_result($q120,0,0);
	else //default vrijednost
		$separator=0;
}

$q130 = myquery("select count(*) from zadaca where predmet=$predmet and akademska_godina=$ag");
if (mysql_result($q130,0,0)>0) {

?><p><hr/></p>
<p><b>Masovni unos zadaća</b><br/>
<?

print genform("POST");
if (strlen($_POST['nazad'])>1) $izabrana = $_POST['_lv_column_zadaca']; else $izabrana = -1;
?><input type="hidden" name="fakatradi" value="0">
<input type="hidden" name="akcija" value="massinput">
<input type="hidden" name="nazad" value="">
<input type="hidden" name="brpodataka" value="1">
<input type="hidden" name="duplikati" value="0">

Izaberite zadaću: <?=db_dropdown("zadaca", $izabrana);?>
Izaberite zadatak: <select name="zadatak"><?
$q112 = myquery("select zadataka from zadaca where predmet=$predmet and akademska_godina=$ag order by zadataka desc limit 1");
for ($i=1; $i<=mysql_result($q112,0,0); $i++) {
	print "<option value=\"$i\">$i</option>\n";
}
?>
</select><br/><br/>

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
<input type="submit" value="  Dodaj  ">
</form></p>
<?


} else {

	?><p><hr/></p>
	<p><b>Masovni unos zadaća NIJE MOGUĆ</b><br/>
	Najprije kreirajte zadaću koristeći formular iznad</p>
	<?
}






/*
// IMPORT ZADAĆA IZ MOODLA

//Prikupljanje id-a moodle predmeta iz zamger baze radi poredjenja
$q200 = myquery("SELECT moodle_id FROM moodle_predmet_id WHERE predmet='$predmet'");

if ($conf_moodle && mysql_num_rows($q200)>0) {

$id_predmeta_value = mysql_result($q200,0,0);


// Ima li zadaća u Moodlu?

$moodle_con = $__lv_connection;
if (!$conf_moodle_reuse_connection) {
	// Pravimo novu konekciju za moodle, kod iz dbconnect2() u libvedran
	if (!($moodle_con = mysql_connect($conf_moodle_dbhost, $conf_moodle_dbuser, $conf_moodle_dbpass))) {
		biguglyerror(mysql_error());
		exit;
	}
	if (!mysql_select_db($conf_moodle_db, $moodle_con)) {
		biguglyerror(mysql_error());
		exit;
	}
	if ($conf_use_mysql_utf8) {
		mysql_set_charset("utf8",$moodle_con);
	}
}
$q300 = mysql_query("SELECT itemname
	FROM $conf_moodle_db.$conf_moodle_prefix"."grade_items
	WHERE itemmodule='assignment' AND itemtype='mod'", $moodle_con) or die ("Greska u upitu 300: " .mysql_error());


// Ako nema, ne ispisujemo ništa
if (mysql_num_rows($q300)<1) 
	return;

$za_value = mysql_fetch_array($q300);



print genform("POST");
?>
<p><hr/></p>
<h4>Import svih zadaća iz Moodle-a</h4>
<p>Klikom na import importuju se sve zadaće za sve studente</p>
<p><br/><b>Napomena:</b> Sve zadaće moraju imati ista imena kao u Moodle-u!</p>
<input type="hidden" name="akcija" value="import_svih">

<?

//Import svih zadaca
if ($_POST['akcija'] == "import_svih" && check_csrf_token()) {
	//Prikupljanje imena zadaca iz Zamger baze
	$q210 = myquery("SELECT naziv
		FROM zadaca
		WHERE predmet='$predmet' AND akademska_godina='$ag'");
	if (mysql_num_rows($q210)<1) {
		niceerror("Nema zadaća u zamgeru");
		zamgerlog("predmet pp$predmet ne sadrzi niti jednu zadacu u zamgeru",3);
		return;
	}

	while ($r210 = mysql_fetch_array($q210)) {
		//Prikupljanje podataka iz Moodle tabele
		//Prikupljaju se id predmeta, ime zadace i JMBG svih studenata
		//Posto se pri prikupljanju zadace porede po imenu trebaju imati isti naziv u Moodle-u kao i u Zamgeru
		$q220 = mysql_query("SELECT c.id, gi.itemname, u.firstname, u.lastname
			FROM $conf_moodle_db.$conf_moodle_prefix"."grade_grades gg, $conf_moodle_db.$conf_moodle_prefix"."user u, $conf_moodle_db.$conf_moodle_prefix"."grade_items gi, $conf_moodle_db.$conf_moodle_prefix"."course c
			WHERE gi.itemname = '$r210[0]' AND c.id = '$id_predmeta_value' AND
			gg.userid=u.id AND gg.itemid=gi.id AND gi.courseid=c.id", $moodle_con) or die ("Greska u upitu 220: " .mysql_error());
		if (mysql_num_rows($q220)<1) {
			niceerror("Nema podataka u Moodle-u");
			zamgerlog("Nema podataka u Moodle-u za zadacu $r210[0]",3);
			return;
		}
		//Ubacivanje podataka u zamger tabelu
		while ($r220 = mysql_fetch_array($q220)) {
			//$bodovi sadrzi vrijednost zadace iz $row1 za date vrijednosti (trenutni student, trenutna zadaca i trenutni predmet)
			$q230 = mysql_query("SELECT gg.finalgrade
				FROM $conf_moodle_db.$conf_moodle_prefix"."grade_grades gg, $conf_moodle_db.$conf_moodle_prefix"."user u, $conf_moodle_db.$conf_moodle_prefix"."grade_items gi, $conf_moodle_db.$conf_moodle_prefix"."course c
				WHERE gi.itemname='$r220[1]' AND c.id='$r220[0]' AND u.firstname='$r220[2]' AND u.lastname='$r220[3]' AND
				gg.userid=u.id AND gg.itemid=gi.id AND gi.courseid=c.id", $moodle_con) or die ("Greska u upitu 230: " .mysql_error());
			if (mysql_num_rows($q230)<1) {
				niceerror("Zadaća nema bodova u Moodle-u");
				zamgerlog("Zadaca: $r210[0] nema bodova",3);
				return;
			}
			$bodovi_value = mysql_fetch_array($q230);
		
			//zadaca_id sadrzi id zadace trenutne vrijednosti u $row1
			$q240 = myquery("SELECT z.id
				FROM zadaca z, moodle_predmet_id p
				WHERE z.naziv='$r220[1]' AND p.moodle_id='$r220[0]' AND p.predmet=z.predmet");
			if (mysql_num_rows($q240)<1) {
				niceerror("Nema zadaća u zamgeru");
				zamgerlog("Predmet $predmet ne sadrzi niti jednu zadacu u zamgeru",3);
				return;
			}
			$zadaca_id_value = mysql_fetch_array($q240);
		
			//$student_id vraca id studenta koji se trenutno cita iz $row1
			$q250 = myquery("SELECT id
				FROM osoba
				WHERE ime='$r220[2]' AND prezime='$r220[3]'");
			if (mysql_num_rows($q250)<1) {
				niceerror("Student ne postoji zamgeru");
				zamgerlog("Student $r220[2] $r220[3] ne postoji u zamgeru",3);
				return;
			}
			$student_id_value = mysql_fetch_array($q250);
		
			$q260 = "INSERT INTO zadatak (zadaca, redni_broj, student, status, bodova, vrijeme, userid)
				VALUES ('$zadaca_id_value[0]', '1', '$student_id_value[0]', '5', '$bodovi_value[0]', 'SYSDATE()', '$userid')";
		
			myquery($q260);
			//upit za dobijanje komponente za zadace
			$q270 = myquery ("SELECT komponenta FROM zadaca WHERE id=$zadaca_id_value[0]");
			if (mysql_num_rows($q270)<1) {
				niceerror("Nema komponente");
				zamgerlog("Nema komponenti u zamgeru",3);
				return;
			}
			$komponenta_value = mysql_fetch_array($q270);
			// Treba nam ponudakursa za update komponente
			$q280 = myquery("SELECT sp.predmet
				FROM student_predmet as sp, ponudakursa as pk
				WHERE sp.student='$student_id_value[0]' and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina='$ag'");
			$pk_value = mysql_result($q280,0,0);
			update_komponente($student_id_value[0],$pk_value,$komponenta_value[0]);
		}
	}

	nicemessage("Import uspješan");
	zamgerlog("zadace su importovane iz Moodle-a", 2);
}
?>
<table>
<tr>
	<td><input type="submit" name="sve_zadace" value="Import"><br/></td>
</tr>
</table>
</form>

<?


// Import pojedinačnih zadaća iz Moodla

print genform("POST");
?>
<h4></br>Import zadaća iz Moodle-a sa advanced upload-a</h4>
<input type="hidden" name="akcija" value="import_selected">
<input type="hidden" name="moodle_zadace" value="<?=$za_value?>">

<?
if ($_POST['akcija'] == "import_selected" && check_csrf_token()) {
	$q310 = mysql_query("SELECT u.firstname, u.lastname, gi.itemname, gi.grademax
		FROM $conf_moodle_db.$conf_moodle_prefix"."grade_grades gg, $conf_moodle_db.$conf_moodle_prefix"."user u, $conf_moodle_db.$conf_moodle_prefix"."grade_items gi, $conf_moodle_db.$conf_moodle_prefix"."course c
		WHERE gi.itemmodule='assignment' AND gi.itemtype='mod' AND c.id = '$id_predmeta_value' AND
		gg.userid=u.id AND gg.itemid=gi.id AND gi.courseid=c.id", $moodle_con) or die ("Greska u upitu 310: " .mysql_error());
	while ($r310 = mysql_fetch_array($q310)) {
		
		$q320 = mysql_query("SELECT gg.finalgrade
			FROM $conf_moodle_db.$conf_moodle_prefix"."grade_grades gg, $conf_moodle_db.$conf_moodle_prefix"."user u, $conf_moodle_db.$conf_moodle_prefix"."grade_items gi, $conf_moodle_db.$conf_moodle_prefix"."course c
			WHERE gi.itemmodule='assignment' AND c.id='$id_predmeta_value' AND u.firstname='$r310[0]' AND u.lastname='$r310[1]' AND
			gg.userid=u.id AND gg.itemid=gi.id AND gi.courseid=c.id", $moodle_con) or die ("Greska u upitu 320: " .mysql_error());
		if (mysql_num_rows($q320)<1) {
			niceerror("Zadaća nema bodova u Moodle-u");
			zamgerlog("Zadaca: $r310[2] nema bodova",3);
			return;
		}
		$bodovi_value = mysql_fetch_array($q320);
		
		$q330 = myquery ("SELECT id FROM komponenta WHERE naziv='Zadace (ETF BSc)'");
		if (mysql_num_rows($q330)<1) {
			niceerror("Nema komponente");
			zamgerlog("Nema komponenti u zamgeru",3);
			return;
		}
		$komponenta_value = mysql_fetch_array($q330);
		
		$q340 = myquery("SELECT z.id
			FROM zadaca z, moodle_predmet_id p
			WHERE z.naziv='$za_value' AND p.moodle_id='$id_predmeta_value' AND p.predmet=z.predmet");
		if (mysql_num_rows($q340)<1) {
			$q350 = myquery ("INSERT INTO zadaca (naziv, predmet, akademska_godina, zadataka, bodova, rok, aktivna, programskijezik, attachment, komponenta, vrijemeobjave)
				VALUES ('$row1[2]', '$predmet', '$ag', 1, '$r310[3]', 'SYSDATE()', 1, 0, 0, '$komponenta_value[0]', 'SYSDATE()')");
			nicemessage("Kreirana nova zadaća '$naziv'");
			zamgerlog("kreirana nova zadaca z$edit_zadaca prilikom importa iz Moodla", 2);

			$q340 = myquery("SELECT z.id
				FROM zadaca z, moodle_predmet_id p
				WHERE z.naziv='$za_value' AND p.moodle_id='$id_predmeta_value' AND p.predmet=z.predmet");
		}
		$zadaca_id_value = mysql_fetch_array($q340);
	
		$q360 = myquery("SELECT id
			FROM osoba
			WHERE ime='$row1[0]' AND prezime='$row1[1]'");
		if (mysql_num_rows($q360)<1) {
			niceerror("Student ne postoji zamgeru");
			zamgerlog("Student $row1[2] $row1[3] ne postoji u zamgeru",3);
			return;
		}
		$student_id_value = mysql_fetch_array($q360);
		
		$q370 = "INSERT INTO zadatak (zadaca, redni_broj, student, status, bodova, vrijeme, userid)
			VALUES ('$zadaca_id_value[0]', '1', '$student_id_value[0]', '5', '$bodovi_value[0]', 'SYSDATE()', '$userid')";
	
		myquery($q370);
			
			
		$q380 = myquery("SELECT sp.predmet
			FROM student_predmet as sp, ponudakursa as pk
			WHERE sp.student='$student_id_value[0]' and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina='$ag'");
		$pk_value = mysql_result($q380,0,0);
		update_komponente($student_id_value[0],$pk_value,$komponenta_value[0]);
	}
	nicemessage("Import uspješan");
	zamgerlog("Zadace su importovane iz Moodle-a", 2);
}
?>
<table>
<tr>
	<td>Izaberite zadaću: <select name="moodle_zadaca"><?
foreach ($za_value as $zaneki) {
	print "<option value=\"$zaneki\">$zaneki</option>\n";
}
?>
</select></td></tr>
<tr>
	<td><input type="submit" name="advanced_zadace" value="Import"><br/></td>
</tr>
</table>
</form>

<?


// Diskonektujemo moodle
if (!$conf_moodle_reuse_connection) {
	mysql_close($moodle_con);
}


}*/

}

?>
