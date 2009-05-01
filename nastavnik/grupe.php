<?

// NASTAVNIK/GRUPE - administracija grupa

// v3.9.1.0 (2008/02/18) + Preimenovan bivsi admin_predmet
// v3.9.1.1 (2008/02/28) + Koristimo lib/manip, student moze biti u vise grupa
// v3.9.1.2 (2008/08/18) + Popravljen logging predmeta, popravljen ispis u mass_inputu, informativna poruka kada parsiranje ne vrati nista, dodana greska za brisanje nepostojece grupe, dodana zastita od visestrukog slanja kod masovnog unosa
// v3.9.1.3 (2008/08/28) + Tabela osoba umjesto auth
// v3.9.1.4 (2008/09/17) + Konacno azuriran kod za kopiranje grupa
// v3.9.1.5 (2008/09/24) + Popravljen bug u massinput-u kada opcija Naziv grupe nije ukljucena
// v3.9.1.6 (2008/10/03) + Iskomentarisan dio koda koji se vec odavno ne koristi
// v3.9.1.7 (2008/10/07) + Malo doradjen logging
// v3.9.1.8 (2008/12/23) + Dodana zastita od CSRF, brisanje grupe prebaceno na POST jer je destruktivna operacija (bug 51)
// v4.0.0.0 (2009/02/19) + Release
// v4.0.0.1 (2009/02/25) + Popravljen ispis imena i prezimena studenta koji ne slusa predmet prilikom kopiranja grupa
// v4.0.9.1 (2009/03/25) + nastavnik_predmet preusmjeren sa tabele ponudakursa na tabelu predmet
// v4.0.9.2 (2009/04/23) + Preusmjeravam tabelu labgrupa sa tabele ponudakursa na tabelu predmet; nastavnicki moduli sada primaju predmet i akademsku godinu (ag) umjesto ponudekursa; dodana provjera predmeta za akcije; kod brisanja grupe dodano brisanje registrovanih casova i prisustva


function nastavnik_grupe() {

global $userid,$user_siteadmin;

require("lib/manip.php");
global $mass_rezultat; // za masovni unos studenata u grupe

// Parametri
$predmet = intval($_REQUEST['predmet']);
$ag = intval($_REQUEST['ag']);

// Naziv predmeta
$q10 = myquery("select naziv from predmet where id=$predmet");
if (mysql_num_rows($q10)<1) {
	biguglyerror("Nepoznat predmet");
	zamgerlog("ilegalan predmet $predmet",3); //nivo 3: greska
	return;
}
$predmet_naziv = mysql_result($q10,0,0);



// Da li korisnik ima pravo ući u modul?

if (!$user_siteadmin) { // 3 = site admin
	$q10 = myquery("select admin from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
	if (mysql_num_rows($q10)<1 || mysql_result($q10,0,0)<1) {
		zamgerlog("nastavnik/ispiti privilegije (predmet pp$predmet)",3);
		biguglyerror("Nemate pravo ulaska u ovu grupu!");
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
	$ime = my_escape($_POST['ime']);
	$q2 = myquery("insert into labgrupa set naziv='$ime', predmet=$predmet, akademska_godina=$ag");
	zamgerlog("dodana nova labgrupa '$ime' (predmet pp$predmet godina ag$ag)",4); // nivo 4: audit
}


// Obrisi grupu

if ($_POST['akcija'] == "obrisi_grupu" && check_csrf_token()) {
	$grupaid = intval($_POST['grupaid']);

	// Provjera ispravnosti podataka
	$q29 = myquery("select predmet, akademska_godina from labgrupa where id=$grupaid");
	if (mysql_num_rows($q29)<1) {
		zamgerlog("nepostojeca labgrupa $grupaid",3);
		niceerror("Pokušavate obrisati labgrupu koja ne postoji");
		return;
	}
	if (mysql_result($q29,0,0) != $predmet || mysql_result($q29,0,1) != $ag) {
		zamgerlog("labgrupa g$grupaid nije sa predmeta pp$predmet (ag$ag)",3);
		niceerror("Predmet se ne poklapa");
		return;
	}

	// ispis svih studenata iz labgrupe
	$q30 = myquery("select student from student_labgrupa where labgrupa=$grupaid");
	while ($r30 = mysql_fetch_row($q30)) {
		ispis_studenta_sa_labgrupe($r30[0],$predmet,$grupaid);
	}

	// Sada mozemo obrisati casove jer je funkcija ispis_studenta... obrisala prisustvo
	$q35 = myquery("delete from cas where labgrupa=$grupaid");

	// Konacno brišem grupu
	$q40 = myquery("delete from labgrupa where id=$grupaid");
	zamgerlog("obrisana labgrupa $grupaid (predmet pp$predmet)",4); // nivo 4: audit
}


// Promjena imena grupe

if ($_POST['akcija'] == "preimenuj_grupu" && check_csrf_token()) {
	$grupaid = intval($_POST['grupaid']);
	$ime = my_escape($_POST['ime']);

	// Provjera ispravnosti podataka
	$q29 = myquery("select predmet, akademska_godina from labgrupa where id=$grupaid");
	if (mysql_num_rows($q29)<1) {
		zamgerlog("nepostojeca labgrupa $grupaid",3);
		niceerror("Pokušavate obrisati labgrupu koja ne postoji");
		return;
	}
	if (mysql_result($q29,0,0) != $predmet || mysql_result($q29,0,1) != $ag) {
		zamgerlog("labgrupa g$grupaid nije sa predmeta pp$predmet (ag$ag)",3);
		niceerror("Predmet se ne poklapa");
		return;
	}

	$q50 = myquery("update labgrupa set naziv='$ime' where id=$grupaid");

	// Grupa treba ostati otvorena:
	$_GET['akcija']="studenti_grupa";
	$_GET['grupaid']=$grupaid;

	zamgerlog("preimenovana labgrupa $grupaid u '$ime' (predmet pp$predmet godina ag$ag)",2); // nivo 2: edit
}


// Kopiraj grupe

if ($_POST['akcija'] == "kopiraj_grupe" && check_csrf_token()) {
	$kopiraj = intval($_POST['kopiraj']);
	if ($kopiraj == $predmet) {
		zamgerlog("kopiranje sa istog predmeta pp$predmet",3);
		niceerror("Ne možete kopirati grupe sa istog predmeta.");
		return;
	}

	// Spisak labgrupa na odabranom predmetu
	$q60 = myquery("select id,naziv from labgrupa where predmet=$kopiraj and akademska_godina=$ag");
	if (mysql_num_rows($q60) == 0) {
		zamgerlog("kopiranje sa predmeta pp$kopiraj na kojem nema grupa",3);
		niceerror("Na odabranom predmetu nije definisana nijedna grupa.");
	}

	while ($r60 = mysql_fetch_row($q60)) {
		$staragrupa = $r60[0];
		$imegrupe = $r60[1];

		// Da li već postoji grupa sa tim imenom?
		$q70 = myquery("select id from labgrupa where predmet=$predmet and naziv='$imegrupe' and akademska_godina=$ag");
		if (mysql_num_rows($q70) == 0) {
			$q80 = myquery("insert into labgrupa set naziv='$imegrupe', predmet=$predmet, akademska_godina=$ag");
			$q70 = myquery("select id from labgrupa where predmet=$predmet and naziv='$imegrupe' and akademska_godina=$ag");
		}
		$novagrupa = mysql_result($q70,0,0);

		// Spisak studenata u grupi koja se kopira
		$q100 = myquery("select student from student_labgrupa as sl where labgrupa=$staragrupa");
		while ($r100 = mysql_fetch_row($q100)) {
			$student = $r100[0];

			// Da li student uopste slusa ovaj predmet?
			$q90 = myquery("select o.ime, o.prezime from student_predmet as sp, osoba as o, ponudakursa as pk where sp.student=$student and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag and o.id=$student");
			if (mysql_num_rows($q90)<1) {
				// Pošto upit nije vratio ništa, moramo nekako saznati ime i prezime
				$q100 = myquery("select ime, prezime from osoba where id=$student");
				print "-- Student ".mysql_result($q100,0,0)." ".mysql_result($q100,0,1)." ne sluša ovaj predmet, pa ćemo ga preskočiti.<br/>";
				continue;
			}

			// Ispis studenta sa svih grupa u kojima je trenutno
			$q110 = myquery("select sl.labgrupa from student_labgrupa as sl, labgrupa as l where sl.student=$student and sl.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag and sl.labgrupa!=$novagrupa");
			while ($r110 = mysql_fetch_row($q110)) {
				ispis_studenta_sa_labgrupe($student,$predmet,$r110[0]);
			}

			// Upis u novu grupu
			$q120 = myquery("select count(*) from student_labgrupa where student=$student and labgrupa=$novagrupa");
			if (mysql_result($q120,0,0)<1) {
				$q130 = myquery("insert into student_labgrupa set labgrupa=$novagrupa, student=$student");
//				print "Upisujem studenta $r23[0] u grupu $novagrupa<br/>";
			}
		}
	}

	zamgerlog("prekopirane labgrupe sa predmeta pp$kopiraj u pp$predmet",4);
}


// Masovni unos studenata u grupe
if ($_POST['akcija'] == "massinput" && strlen($_POST['nazad'])<1 && check_csrf_token()) {
	if ($_POST['fakatradi'] != 1) $ispis=1; else $ispis=0;

	// Unos moze imati jedan parametar (ime grupe) ili nula (prva grupa)
	$brpodataka = intval($_REQUEST['brpodataka']);
	if ($_REQUEST['brpodataka']=='on') $brpodataka=1; //checkbox

	if ($brpodataka==0) {
		$q200 = myquery("select id,naziv from labgrupa where predmet=$predmet and akademska_godina=$ag order by id limit 1");
		if (mysql_num_rows($q200)<1) {
			// Ovo je fatalna greska...
			zamgerlog("nije kreirana nijedna grupa za masovni upis (predmet pp$predmet)",3);
			niceerror("Niste kreirali niti jednu grupu.");
			print "<br/>Ili izaberite opciju &quot;Naziv grupe&quot; (s kojom će automatski biti kreirane grupe pod imenima koje navedete u drugoj koloni), ili ručno kreirajte barem jednu grupu.";
			return;
		}
		$labgrupa = mysql_result($q200,0,0);
		$imegrupe = mysql_result($q200,0,1);
	}

	$greska=mass_input($ispis); // Funkcija koja parsira podatke
	if ($greska != 0) {
		print "<p>NAPOMENA: U novoj verziji ZAMGERa upis studenata na predmet može vršiti samo studentska služba. Spiskovi studenata na predmetima su dostavljeni iz studentske službe, tako da ako su ti spiskovi netačni molimo da kontaktirate njih.</p>\n";
	}

	if (count($mass_rezultat)==0) {
		zamgerlog("parsiranje kod masovnog upisa nije vratilo ništa (predmet pp$predmet)",3);
		niceerror("Niste unijeli nijedan koristan podatak.");
	//	return;
	}

	if ($ispis) {
		?>Akcije koje će biti urađene:<br/><br/>
		<?=genform("POST")?>
		<input type="hidden" name="fakatradi" value="1">
		<?
	}

	$idovi_grupa=array();
	if ($brpodataka==0) $idovi_grupa[$imegrupe]=$labgrupa;

	// Spisak studenata
	foreach ($mass_rezultat['ime'] as $student=>$ime) {
		$prezime = $mass_rezultat['prezime'][$student];

		// Ispis studenta iz svih grupa
		$q230 = myquery("select l.id,l.naziv from labgrupa as l, student_labgrupa as sl where sl.student=$student and sl.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag");
		while ($r230 = mysql_fetch_row($q230)) {
			if ($ispis) {
				print "Ispis studenta '$prezime $ime' iz grupe '$r230[1]'<br/>\n";
			} else {
				ispis_studenta_sa_labgrupe($student,$predmet,$r230[0]);
			}
		}

		// Kod upisa sviju u prvu grupu, ovo ispod znatno pojednostavljuje k^od
		if ($brpodataka==0) $mass_rezultat['podatak1'][$student][]=$imegrupe;

		// spisak grupa u koje treba upisati studenta
		foreach ($mass_rezultat['podatak1'][$student] as $imegrupe) {
			if (array_key_exists($imegrupe,$idovi_grupa)) {
				$labgrupa=$idovi_grupa[$imegrupe];
			} else {

				// Da li je ime ispravno?
				if (!preg_match("/\w/", $imegrupe)) {
					print "--GREŠKA: Neispravno ime grupe '$imegrupe'<br/>\n";
					$greska=1;
					continue;
				}

				// Određujemo ID grupe
				$q210 = myquery("select id from labgrupa where naziv='$imegrupe' and predmet=$predmet and akademska_godina=$ag");
				if (mysql_num_rows($q210)<1) {
					// Grupa ne postoji - kreiramo je
					if ($ispis) {
						print "Kreiranje nove grupe '$imegrupe' <br/>\n";
					} else {
						$q220 = myquery("insert into labgrupa set naziv='$imegrupe', predmet=$predmet, akademska_godina=$ag");
						$q210 = myquery("select id from labgrupa where naziv like '$imegrupe' and predmet=$predmet and akademska_godina=$ag");
						$labgrupa = mysql_result($q210,0,0);
					}
				} else {
					$labgrupa = mysql_result($q210,0,0);
				}

				$idovi_grupa[$imegrupe]=$labgrupa;
			}

			// Upis u novu grupu
			if ($ispis) {
				print "Upis studenta '$prezime $ime' u grupu '$imegrupe'<br/>\n";
			} else {
				$q240 = myquery("insert into student_labgrupa set student=$student, labgrupa=$labgrupa");
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
		zamgerlog("masovan upis grupa za predmet pp$predmet",4);
		?>
		Grupe su kreirane.
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
		document.brisanjegrupe.grupaid.value=grupa;
		document.brisanjegrupe.submit();
	}
}
</script>
<?=genform("POST", "brisanjegrupe")?>
<input type="hidden" name="akcija" value="obrisi_grupu">
<input type="hidden" name="grupaid" value=""></form>

Spisak grupa:<br/>
<?

$q100 = myquery("select id,naziv from labgrupa where predmet=$predmet and akademska_godina=$ag order by id");

print "<ul>\n";
if (mysql_num_rows($q100) == 0)
	print "<li>Nema definisanih grupa</li>\n";
while ($r100 = mysql_fetch_row($q100)) {
	$grupa = $r100[0];
	$naziv = $r100[1];

	if (!preg_match("/\w/",$naziv)) 
		print "<li>[Nema imena] - ";
	else
		print "<li>$naziv - ";

	$q110 = myquery("select count(*) from student_labgrupa where labgrupa=$grupa");
	$brstud = mysql_result($q110,0,0);
	print "(<a href=\"?sta=nastavnik/grupe&predmet=$predmet&ag=$ag&akcija=studenti_grupa&grupaid=$grupa\">$brstud studenata</a>) - ";

	print "<a href=\"javascript:onclick=upozorenje('$grupa')\">Obriši grupu</a></li>";

	//print "</li>\n";
	if ($_GET['akcija']=="studenti_grupa" && $_GET['grupaid']==$grupa) {
		print "<ul>\n";
		$q102 = myquery("select osoba.id,osoba.prezime,osoba.ime from student_labgrupa,osoba where student_labgrupa.student=osoba.id and student_labgrupa.labgrupa=$grupa order by osoba.prezime,osoba.ime");
		while ($r102 = mysql_fetch_row($q102)) {
			?><li><a href="#" onclick="javascript:window.open('?sta=saradnik/izmjena_studenta&student=<?=$r102[0]?>&predmet=<?=$predmet?>','blah6','width=320,height=320');"><? print $r102[1]." ".$r102[2]."</a></li>\n";
		}
		print "</ul>";
		$zapamti_grupu=$naziv;
	}
}
print "</ul>\n";

# Editovanje grupe
if ($_GET['akcija']=="studenti_grupa") {
	$gg = intval($_GET['grupaid']);
	# Dodavanje grupe
	print "<p>\n";
	print genform("POST");
	print '<input type="hidden" name="akcija" value="preimenuj_grupu">'."\n";
	print '<input type="hidden" name="grupaid" value="'.$gg.'">'."\n";
	print 'Promijenite naziv grupe: <input type="text" name="ime" size="20" value="'.$zapamti_grupu.'"> <input type="submit" value="Izmijeni"></form></p>'."\n";
}


// Dodavanje grupe

?>

<p>
<?=genform("POST")?>
<input type="hidden" name="akcija" value="nova_grupa">
Dodaj grupu: <input type="text" name="ime" size="20"> <input type="submit" value="Dodaj"></form></p>
<?


// Kopiranje grupa sa predmeta
?>

<p>
<?=genform("POST")?>
<input type="hidden" name="akcija" value="kopiraj_grupe">
Prekopiraj grupe sa predmeta: <select name="kopiraj">
<?
$q103a = myquery("select p.id, p.naziv from predmet as p, ponudakursa as pk where pk.predmet=p.id and pk.akademska_godina=$ag order by p.naziv"); // TODO: Sortirati po semestru i studiju
while ($r103a = mysql_fetch_row($q103a)) {
	print "<option value=\"$r103a[0]\">$r103a[1]</a>\n";
}
?></select>
<input type="submit" value="Dodaj">
</form></p><?


// Masovni unos

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

?>

<p><hr/></p><p><b>Masovni unos studenata</b><br/>
<?=genform("POST")?>
<input type="hidden" name="fakatradi" value="0">
<input type="hidden" name="akcija" value="massinput">
<input type="hidden" name="nazad" value="">
<input type="hidden" name="visestruki" value="1">
<input type="hidden" name="duplikati" value="1">

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
<input type="checkbox" name="brpodataka" CHECKED> Naziv grupe (treća kolona)<br/><br/>

<input type="submit" value="  Dodaj  ">
</form></p><?


} // function nastavnik_grupa()

?>