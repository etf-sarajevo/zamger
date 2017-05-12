<?

// NASTAVNIK/GRUPE - administracija grupa



// FIXME: moguce kreirati vise grupa sa istim imenom


function nastavnik_grupe() {

global $userid,$user_siteadmin;

require("lib/student_predmet.php"); // ispis_studenta_sa_labgrupe
global $mass_rezultat; // za masovni unos studenata u grupe

// Parametri
$predmet = intval($_REQUEST['predmet']);
$ag = intval($_REQUEST['ag']);

// Naziv predmeta
$q10 = db_query("select naziv from predmet where id=$predmet");
if (db_num_rows($q10)<1) {
	biguglyerror("Nepoznat predmet");
	zamgerlog("ilegalan predmet $predmet",3); //nivo 3: greska
	zamgerlog2("nepoznat predmet", $predmet);
	return;
}
$predmet_naziv = db_result($q10,0,0);



// Da li korisnik ima pravo ući u modul?

if (!$user_siteadmin) {
	$q10 = db_query("select nivo_pristupa from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
	if (db_num_rows($q10)<1 || db_result($q10,0,0)=="asistent") {
		zamgerlog("nastavnik/ispiti privilegije (predmet pp$predmet)",3);
		zamgerlog2("nije nastavnik na predmetu", $predmet, $ag);
		biguglyerror("Nemate pravo pristupa ovoj opciji");
		return;
	} 
}



?>

<p>&nbsp;</p>

<p><h3><?=$predmet_naziv?> - Grupe</h3></p>

<?


###############
# Akcije
###############


// Dodaj grupu

if ($_POST['akcija'] == "nova_grupa" && check_csrf_token()) {
	$ime = db_escape($_POST['ime']);
	$tip = db_escape($_POST['tip']);
	$q2 = db_query("insert into labgrupa set naziv='$ime', predmet=$predmet, tip='$tip', akademska_godina=$ag, virtualna=0");
	zamgerlog2("kreirana labgrupa", db_insert_id(), $predmet, $ag, $ime);
	zamgerlog("dodana nova labgrupa '$ime' (predmet pp$predmet godina ag$ag)",4); // nivo 4: audit
}


// Obrisi grupu

if ($_POST['akcija'] == "obrisi_grupu" && check_csrf_token()) {
	$grupaid = intval($_POST['grupaid']);

	// Provjera ispravnosti podataka
	$q29 = db_query("select predmet, akademska_godina from labgrupa where id=$grupaid");
	if (db_num_rows($q29)<1) {
		zamgerlog("nepostojeca labgrupa $grupaid",3);
		zamgerlog2("nepostojeca labgrupa (brisanje)", $grupaid);
		niceerror("Pokušavate obrisati labgrupu koja ne postoji");
		return;
	}
	if (db_result($q29,0,0) != $predmet || db_result($q29,0,1) != $ag) {
		zamgerlog("labgrupa g$grupaid nije sa predmeta pp$predmet (ag$ag)",3);
		zamgerlog2("predmet i labgrupa se ne poklapaju (brisanje)", $predmet, $ag, $grupaid);
		niceerror("Predmet se ne poklapa");
		return;
	}

	// ispis svih studenata iz labgrupe
	$q30 = db_query("select student from student_labgrupa where labgrupa=$grupaid");
	while ($r30 = db_fetch_row($q30)) {
		ispis_studenta_sa_labgrupe($r30[0],$grupaid);
		zamgerlog2("student ispisan sa grupe (brisanje)", intval($r30[0]), $grupaid);
	}

	// Sada mozemo obrisati casove jer je funkcija ispis_studenta... obrisala prisustvo
	$q35 = db_query("delete from cas where labgrupa=$grupaid");

	// Konacno brišem grupu
	$q40 = db_query("delete from labgrupa where id=$grupaid");
	zamgerlog("obrisana labgrupa $grupaid (predmet pp$predmet)",4); // nivo 4: audit
	zamgerlog2("obrisana labgrupa", intval($predmet), $ag, $grupaid);
}


// Promjena imena grupe

if ($_POST['akcija'] == "preimenuj_grupu" && check_csrf_token()) {
	$grupaid = intval($_POST['grupaid']);
	$ime = db_escape($_POST['ime']);
	$tip = db_escape($_POST['tip']);

	// Provjera ispravnosti podataka
	$q29 = db_query("select predmet, akademska_godina from labgrupa where id=$grupaid");
	if (db_num_rows($q29)<1) {
		zamgerlog("nepostojeca labgrupa $grupaid",3);
		zamgerlog2("nepostojeca labgrupa (preimenovanje)", $grupaid);
		niceerror("Pokušavate obrisati labgrupu koja ne postoji");
		return;
	}
	if (db_result($q29,0,0) != $predmet || db_result($q29,0,1) != $ag) {
		zamgerlog("labgrupa g$grupaid nije sa predmeta pp$predmet (ag$ag)",3);
		zamgerlog2("predmet i labgrupa se ne poklapaju (preimenovanje)", $predmet, $ag, $grupaid);
		niceerror("Predmet se ne poklapa");
		return;
	}

	$q50 = db_query("update labgrupa set naziv='$ime', tip='$tip' where id=$grupaid");

	// Grupa treba ostati otvorena:
	$_GET['akcija']="studenti_grupa";
	$_GET['grupaid']=$grupaid;

	zamgerlog("preimenovana labgrupa $grupaid u '$ime' (predmet pp$predmet godina ag$ag)",2); // nivo 2: edit
	zamgerlog2("preimenovana labgrupa", $grupaid, 0, 0, $ime);
}


// Kopiraj grupe

if ($_POST['akcija'] == "kopiraj_grupe" && check_csrf_token()) {
	$kopiraj = intval($_POST['kopiraj']);
	if ($kopiraj == $predmet) {
		zamgerlog("kopiranje sa istog predmeta pp$predmet",3);
		zamgerlog2("kopiranje grupa sa istog predmeta", $predmet, $ag);
		niceerror("Ne možete kopirati grupe sa istog predmeta.");
		return;
	}

	// Spisak labgrupa na odabranom predmetu
	$q60 = db_query("select id, naziv, tip from labgrupa where predmet=$kopiraj and akademska_godina=$ag and virtualna=0");
	if (db_num_rows($q60) == 0) {
		zamgerlog("kopiranje sa predmeta pp$kopiraj na kojem nema grupa",3);
		zamgerlog2("kopiranje sa predmeta na kojem nema grupa", $kopiraj, $ag, $predmet);
		niceerror("Na odabranom predmetu nije definisana nijedna grupa.");
	}

	while ($r60 = db_fetch_row($q60)) {
		$staragrupa = $r60[0];
		$imegrupe = $r60[1];
		$tipgrupe = $r60[2];

		// Da li već postoji grupa sa tim imenom?
		$q70 = db_query("select id from labgrupa where predmet=$predmet and naziv='$imegrupe' and akademska_godina=$ag");
		if (db_num_rows($q70) == 0) {
			$q80 = db_query("insert into labgrupa set naziv='$imegrupe', predmet=$predmet, akademska_godina=$ag, tip='$tipgrupe'");
			$q70 = db_query("select id from labgrupa where predmet=$predmet and naziv='$imegrupe' and akademska_godina=$ag");
			zamgerlog2("kreirana labgrupa (kopiranje)", intval(db_result($q70,0,0)), $predmet, $ag, $imegrupe);
		}
		$novagrupa = db_result($q70,0,0);

		// Spisak studenata u grupi koja se kopira
		$q100 = db_query("select student from student_labgrupa where labgrupa=$staragrupa");
		while ($r100 = db_fetch_row($q100)) {
			$student = $r100[0];

			// Da li student uopste slusa ovaj predmet?
			$q110 = db_query("select o.ime, o.prezime from student_predmet as sp, osoba as o, ponudakursa as pk where sp.student=$student and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag and o.id=$student");
			if (db_num_rows($q110)<1) {
				// Pošto upit nije vratio ništa, moramo nekako saznati ime i prezime
				$q120 = db_query("select ime, prezime from osoba where id=$student");
				print "-- Student ".db_result($q120,0,0)." ".db_result($q120,0,1)." ne sluša ovaj predmet, pa ćemo ga preskočiti.<br/>";
				continue;
			}

			// Ispis studenta sa svih grupa u kojima je trenutno
			$q130 = db_query("select sl.labgrupa from student_labgrupa as sl, labgrupa as l where sl.student=$student and sl.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag and sl.labgrupa!=$novagrupa and l.virtualna=0");
			while ($r130 = db_fetch_row($q130)) {
				ispis_studenta_sa_labgrupe($student,$r130[0]);
				zamgerlog2("student ispisan sa grupe (kopiranje)", $student, $r130[0]);
			}

			// Upis u novu grupu
			$q140 = db_query("select count(*) from student_labgrupa where student=$student and labgrupa=$novagrupa");
			if (db_result($q140,0,0)<1) {
				upis_studenta_na_labgrupu($student, $novagrupa);
				zamgerlog2("student upisan u grupu (kopiranje)", intval($student), intval($novagrupa));
				//print "Upisujem studenta $student u grupu $novagrupa<br/>";
			}
		}
	}

	zamgerlog("prekopirane labgrupe sa predmeta pp$kopiraj u pp$predmet",4);
	zamgerlog2("prekopirane labgrupe", $kopiraj, $ag);
}


// Masovni unos studenata u grupe
if ($_POST['akcija'] == "massinput" && strlen($_POST['nazad'])<1 && check_csrf_token()) {

	if ($_POST['fakatradi'] != 1) $ispis=1; else $ispis=0;

	if ($ispis) {
		?>Akcije koje će biti urađene:<br/><br/>
		<?=genform("POST")?>
		<input type="hidden" name="fakatradi" value="1">
		<table border="0" cellspacing="1" cellpadding="2">
		<!-- FIXME: prebaciti stilove u CSS? -->
		<thead>
		<tr bgcolor="#999999">
			<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;color:white;">Prezime</font></td>
			<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;color:white;">Ime</font></td>
			<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;color:white;">Akcije</font></td>
		</tr>
		</thead>
		<tbody>
		<?
	}

	$greska=mass_input($ispis); // Funkcija koja parsira podatke

	// Cache IDova grupa prema imenu
	$idovi_grupa=array();


	// Spisak studenata

	$boja1 = "#EEEEEE";
	$boja2 = "#DDDDDD";
	$boja=$boja1;
	$bojae = "#FFE3DD";

	foreach ($mass_rezultat['ime'] as $student=>$ime) {
		$prezime = $mass_rezultat['prezime'][$student];

		// Ispis studenta iz svih grupa
		$ispisispis = "";
		$ispis_grupe=$upis_grupe=array();
		$q230 = db_query("select l.id,l.naziv from labgrupa as l, student_labgrupa as sl where sl.student=$student and sl.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag and l.virtualna=0");
		$found=1;
		while ($r230 = db_fetch_row($q230)) {
			$ispis_grupe[$r230[0]] = $r230[1];
			if (!in_array($r230[1], $mass_rezultat['podatak1'][$student])) $found=0;
		}

		if ($found==1 && db_num_rows($q230)>0) {
			if ($ispis) {
				?>
				<tr bgcolor="<?=$boja?>">
					<td><?=$prezime?></td><td><?=$ime?></td>
					<td>Već upisan u grupu <?
					foreach ($ispis_grupe as $gid => $gime) print "'$gime' ";
					?> - preskačem</td>
				</tr>
				<?
				if ($boja==$boja1) $boja=$boja2; else $boja=$boja1;
			}
			continue;
		}

		// spisak grupa u koje treba upisati studenta
		foreach ($mass_rezultat['podatak1'][$student] as $imegrupe) {
			$imegrupe = trim($imegrupe);
			if ($imegrupe == "") continue;

			// Da li grupa postoji u cache-u ?
			if (array_key_exists($imegrupe,$idovi_grupa)) {
				$labgrupa=$idovi_grupa[$imegrupe];

			// Ne postoji, tražimo u bazi
			} else {
				// Da li je ime ispravno?
				if (!preg_match("/\w/", $imegrupe)) {
					?>
					<tr bgcolor="<?=$bojae?>">
						<td><?=$prezime?></td><td><?=$ime?></td>
						<td>neispravno ime grupe '<?=$imegrupe?>'</td>
					</tr>
					<?
					$greska=1;
					continue;
				}

				// Određujemo ID grupe
				$q210 = db_query("select id from labgrupa where naziv='$imegrupe' and predmet=$predmet and akademska_godina=$ag");
				if (db_num_rows($q210)<1) {
					// Grupa ne postoji - kreiramo je
					if ($ispis) {
						?>
						<tr bgcolor="<?=$boja?>">
							<td colspan="3">Kreiranje nove grupe '<?=$imegrupe?>'</td>
						</tr>
						<?
						if ($boja==$boja1) $boja=$boja2; else $boja=$boja1;
					} else {
						$q220 = db_query("insert into labgrupa set naziv='$imegrupe', predmet=$predmet, akademska_godina=$ag, tip='vjezbe+tutorijali', virtualna=0");
						$labgrupa = db_insert_id();
						zamgerlog2("kreirana labgrupa (masovni unos)", intval($labgrupa), 0, 0, $imegrupe);
					}
				} else {
					$labgrupa = db_result($q210,0,0);
				}

				$idovi_grupa[$imegrupe]=$labgrupa;
			}

			// Da li je grupa već jednom spomenuta?
			foreach ($upis_grupe as $gid => $gime) {
				if ($gid==$labgrupa) {
					if ($ispis) {
						?>
						<tr bgcolor="<?=$bojae?>">
							<td><?=$prezime?></td><td><?=$ime?></td>
							<td>Grupa '<?=$gime?>' je navedena dvaput - greška?</td>
						</tr>
						<?
					}
					continue;
				}
			}

			$upis_grupe[$labgrupa]=$imegrupe;
		}

		// Obavljam ispisivanje i upisivanje u grupe
		if ($ispis) { // na ekran
			?>
			<tr bgcolor="<?=$boja?>">
				<td><?=$prezime?></td><td><?=$ime?></td>
				<td>
			<?
			foreach ($ispis_grupe as $gid => $gime) {
				print "Ispis iz grupe '$gime'<br />\n";
			}
			foreach ($upis_grupe as $gid => $gime) {
				print "Upis u grupu '$gime'<br />\n";
			}
			print "</td></tr>\n";
			if ($boja==$boja1) $boja=$boja2; else $boja=$boja1;
		} else {
			foreach ($ispis_grupe as $gid => $gime) {
				ispis_studenta_sa_labgrupe($student,$gid);
				zamgerlog2("student ispisan sa grupe (masovni unos)", $student, $gid);
			}
			foreach ($upis_grupe as $gid => $gime) {
				$q240 = db_query("insert into student_labgrupa set student=$student, labgrupa=$gid");
				zamgerlog2("student upisan u grupu (masovni unos)", $student, $gid);
			}
		}
	}

	// Potvrda i Nazad
	if ($ispis) {
		if ($greska != 0) {
			?>
			</tbody></table>
			<p>U unesenim podacima ima grešaka. Da li ste izabrali ispravan format ("Prezime[TAB]Ime" vs. "Prezime Ime")?Vratite se nazad kako biste ovo popravili.</p>
			<p>NAPOMENA: Upis studenata na predmet može vršiti samo studentska služba. Ukoliko na spisku nedostaje neki student koji sluša vaš predmet, kontaktirajte službu radi razjašnjenja nesporazuma.</p>
			<p><input type="submit" name="nazad" value=" Nazad "></p>
			</form>
			<? 

		} else if (count($mass_rezultat)==0) {
			?>
			</tbody></table>
			<p>Niste unijeli nijedan koristan podatak.</p>
			<p><input type="submit" name="nazad" value=" Nazad "></p>
			</form>
			<?

		} else {
			?>
			</tbody></table>
			<p>Potvrdite kreiranje grupa i upis studenata u grupe ili se vratite na prethodni ekran.</p>
			<p><input type="submit" name="nazad" value=" Nazad "> <input type="submit" value=" Potvrda"></p>
			</form>
			<? 
		}
		return;

	} else {
		zamgerlog("masovan upis grupa za predmet pp$predmet",4);
		?>
		Masovan upis studenata u grupe je uspješno obavljen.
		<script language="JavaScript">
		location.href='?sta=nastavnik/grupe&predmet=<?=$predmet?>&ag=<?=$ag?>';
		</script>
		<?
	}
}



###############
# Prikaz grupa
###############

?>
<script language="JavaScript">
function upozorenje(grupa) {
	var a = confirm("Svi studenti će biti ispisani iz ove grupe.");
	if (a) {
		document.getElementById('grupaid').value=grupa;
		document.brisanjegrupe.submit();
	}
}
</script>
<?=genform("POST", "brisanjegrupe")?>
<input type="hidden" name="akcija" value="obrisi_grupu">
<input type="hidden" name="grupaid" id="grupaid" value=""></form>

Spisak grupa:<br/>
<?

$q100 = db_query("select id, naziv, tip from labgrupa where predmet=$predmet and akademska_godina=$ag and virtualna=0 order by id");

// TODO: koristiti natsort za sortiranje grupa

print "<ul>\n";
if (db_num_rows($q100) == 0)
	print "<li>Nema definisanih grupa</li>\n";
$tip_selektovan = array();
while ($r100 = db_fetch_row($q100)) {
	$grupa = $r100[0];
	$naziv = $r100[1];

	if (!preg_match("/\w/",$naziv)) 
		print "<li>[Nema imena] - ";
	else
		print "<li>$naziv - ";

	$q110 = db_query("select count(*) from student_labgrupa where labgrupa=$grupa");
	$brstud = db_result($q110,0,0);
	print "(<a href=\"?sta=nastavnik/grupe&predmet=$predmet&ag=$ag&akcija=studenti_grupa&grupaid=$grupa\">$brstud studenata</a>) - ";

	print "<a href=\"javascript:onclick=upozorenje('$grupa')\">Obriši grupu</a></li>";

	//print "</li>\n";
	if ($_GET['akcija']=="studenti_grupa" && $_GET['grupaid']==$grupa) {
		print "<ul>\n";
		$q102 = db_query("select osoba.id,osoba.prezime,osoba.ime from student_labgrupa,osoba where student_labgrupa.student=osoba.id and student_labgrupa.labgrupa=$grupa order by osoba.prezime,osoba.ime");
		while ($r102 = db_fetch_row($q102)) {
			?><li><a href="#" onclick="javascript:window.open('?sta=saradnik/izmjena_studenta&student=<?=$r102[0]?>&predmet=<?=$predmet?>&ag=<?=$ag?>','blah6','width=320,height=320');"><? print $r102[1]." ".$r102[2]."</a></li>\n";
		}
		print "</ul>";
		$zapamti_grupu = $naziv;
		$tip_selektovan[$r100[2]] = " SELECTED";
	}
}
print "</ul>\n";

# Editovanje grupe
if ($_GET['akcija']=="studenti_grupa") {
	$gg = intval($_GET['grupaid']);
	?><p>
	<?=genform("POST")?>
	<input type="hidden" name="akcija" value="preimenuj_grupu">
	<input type="hidden" name="grupaid" value="<?=$gg?>">
	Promijenite naziv grupe: <input type="text" name="ime" size="20" value="<?=$zapamti_grupu?>">
	Promijenite tip grupe: <select name="tip">
	<option value="predavanja" <?=$tip_selektovan['predavanja']?>>Grupa za predavanja</a>
	<option value="vjezbe" <?=$tip_selektovan['vjezbe']?>>Grupa za vježbe</a>
	<option value="tutorijali" <?=$tip_selektovan['tutorijali']?>>Grupa za tutorijale</a>
	<option value="vjezbe+tutorijali" <?=$tip_selektovan['vjezbe+tutorijali']?>>Grupa za vježbe i tutorijale</a>
	</select>
	<input type="submit" value="Izmijeni"></form></p>
	<?
}


// Dodavanje grupe

?>

<p>
<?=genform("POST")?>
<input type="hidden" name="akcija" value="nova_grupa">
Dodaj grupu: <input type="text" name="ime" size="20">
Tip grupe:<select name="tip">
	<option value="predavanja">Grupa za predavanja</a>
	<option value="vjezbe">Grupa za vježbe</a>
	<option value="tutorijali">Grupa za tutorijale</a>
	<option value="vjezbe+tutorijali" SELECTED>Grupa za vježbe i tutorijale</a>
</select>
<input type="submit" value="Dodaj"></form></p>
<?


// Kopiranje grupa sa predmeta
?>

<p>
<?=genform("POST")?>
<input type="hidden" name="akcija" value="kopiraj_grupe">
Prekopiraj grupe sa predmeta: <select name="kopiraj">
<?
$q103a = db_query("select p.id, p.naziv from predmet as p, ponudakursa as pk where pk.predmet=p.id and pk.akademska_godina=$ag order by p.naziv"); // TODO: Sortirati po semestru i studiju
while ($r103a = db_fetch_row($q103a)) {
	print "<option value=\"$r103a[0]\">$r103a[1]</a>\n";
}
?></select>
<input type="submit" value="Dodaj">
</form></p><?


// Masovni unos

$format = intval($_POST['format']);
if (!$_POST['format']) {
	$q110 = db_query("select vrijednost from preference where korisnik=$userid and preferenca='mass-input-format'");
	if (db_num_rows($q110)>0) $format = db_result($q110,0,0);
	else //default vrijednost
		$format=0;
}

$separator = intval($_POST['separator']);
if (!$_POST['separator']) {
	$q120 = db_query("select vrijednost from preference where korisnik=$userid and preferenca='mass-input-separator'");
	if (db_num_rows($q120)>0) $separator = db_result($q120,0,0);
	else //default vrijednost
		$separator=0;
}

?>

<p><hr/></p><p><b>Masovni upis studenata u grupe</b><br/>
U prozoru ispod navedite ime i prezime studenta, znak za separator i naziv grupe u koju želite da ga/je upišete.<br/>
<?=genform("POST")?>
<input type="hidden" name="fakatradi" value="0">
<input type="hidden" name="akcija" value="massinput">
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

<input type="submit" value="  Dodaj  ">
</form></p><?


} // function nastavnik_grupa()

?>
