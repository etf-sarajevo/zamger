<?

// STUDENTSKA/PRODSJEKA - odobrenja za promjenu odsjeka

// v3.9.1.0 (2008/09/11) + Novi modul studentska/prodsjeka
// v3.9.1.1 (2008/09/24) + Dodan link na detalje o studentu
// v4.0.0.0 (2009/02/19) + Release
// v4.0.9.1 (2009/03/31) + Tabela konacna_ocjena preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.2 (2009/09/16) + Dodajem polje akademska_godina u tabelu promjena_odsjeka; sprijeceno dupliciranje zahtjeva
// v4.0.9.3 (2009/09/25) + Implementirana promjena odsjeka (upis u novi odsjek, eventualni ispis iz starog), posto modul studentska/osobe to ne dozvoljava direktno; prikaz ogranicen na studije prvog ciklusa
// v4.0.9.4 (2009/10/03) + Premjestam ispis studenta sa studija u studentska/osobe, gdje i pripada


function studentska_prodsjeka() {

global $userid,$user_siteadmin,$user_studentska;


// Provjera privilegija

if (!$user_studentska && !$user_siteadmin) {
	zamgerlog("nije studentska",3); // 3: error
	zamgerlog2("nije studentska"); // 3: error
	biguglyerror("Pristup nije dozvoljen.");
	return;
}


// Odredjujemo akademsku godinu
$ak_god = intval($_REQUEST['ak_god']);
if ($ak_god==0) {
	// Aktuelna
	$q1 = myquery("select id, naziv from akademska_godina where aktuelna=1");
	$ak_god = mysql_result($q1,0,0);
	$ak_god_naziv = mysql_result($q1,0,1);
	
	// Da li postoji godina iza aktuelne?
	$q2 = myquery("select id, naziv from akademska_godina where id>$ak_god order by id limit 1");
	if (mysql_num_rows($q2)>0) {
		$ak_god=mysql_result($q2,0,0);
		$ak_god_naziv = mysql_result($q2,0,1);
	}
} else {
	$q3 = myquery("select naziv from akademska_godina where id=$ak_god");
	$ak_god_naziv = mysql_result($q3,0,0);
}



?>
<h3>Studentska služba - Zahtjevi za promjenu odsjeka</h3>
<?


// Akcija: brisanje zahtjeva
if ($_REQUEST['akcija']=="obrisi") {
	$id = intval($_REQUEST['id']);
	$q5 = myquery("delete from promjena_odsjeka where id=$id");
	zamgerlog("obrisan zahtjev za promjenu odsjeka sa IDom $id", 2); // 2 = edit
	zamgerlog2("obrisan zahtjev za promjenu odsjeka", $id);
}

// Akcija: dodavanje zahtjeva
if ($_POST['akcija']=="dodaj" && check_csrf_token()) {
	$prezime = trim(malaslova(my_escape($_REQUEST['prezime'])));
	$ime = trim(malaslova(my_escape($_REQUEST['ime'])));

	$iz_odsjeka=intval($_REQUEST['iz_odsjeka']);
	$u_odsjek=intval($_REQUEST['u_odsjek']);

	$q100 = myquery("select id from osoba where ime='$ime' and prezime='$prezime'");

	if (mysql_num_rows($q100)<1) {
		niceerror("Nepoznat student pod imenom: \"$ime $prezime\"");
	} else if ($iz_odsjeka==0 || $u_odsjek==0) {
		niceerror("Niste odabrali odsjek");
	} else {
		$osoba = mysql_result($q100,0,0);
		$q105 = myquery("select count(*) from promjena_odsjeka where osoba=$osoba and akademska_godina=$ak_god");
		if (mysql_result($q105,0,0)>0) {
			niceerror("Već postoji zahtjev za promjenu odsjeka za studenta \"$ime $prezime\"");
		} else {
			$q110 = myquery("insert into promjena_odsjeka set osoba=$osoba, iz_odsjeka=$iz_odsjeka, u_odsjek=$u_odsjek, akademska_godina=$ak_god");
			$q115 = myquery("select id from promjena_odsjeka where osoba=$osoba and iz_odsjeka=$iz_odsjeka and u_odsjek=$u_odsjek and akademska_godina=$ak_god");
			zamgerlog("dodan zahtjev za promjenu odsjeka za osobu u$osoba (iz $iz_odsjeka u $u_odsjek)", 2);
			zamgerlog2("dodan zahtjev za promjenu odsjeka", intval($osoba), $iz_odsjeka, $u_odsjek);
		}
	}
}


// Akcija: prihvatanje zahtjeva
// Ustvari ćemo samo dati linkove na modul studentska/osobe
if ($_REQUEST['akcija']=="prihvati") {
	$id = intval($_REQUEST['id']);
	$potvrda = intval($_REQUEST['potvrda']);

	$q500 = myquery("select osoba, iz_odsjeka, u_odsjek from promjena_odsjeka where id=$id and akademska_godina=$ak_god");
	if (mysql_num_rows($q500)<1) {
		niceerror("Nepoznat zahtjev ID");
		return;
	}
	$osoba = mysql_result($q500,0,0);
	$iz_odsjeka = mysql_result($q500,0,1);
	$u_odsjek = mysql_result($q500,0,2);

	// Da li trenutno studira
	$q510 = myquery("select s.id, s.naziv, ss.semestar from studij as s, student_studij as ss where ss.student=$osoba and ss.studij=s.id and ss.akademska_godina=$ak_god order by ss.semestar desc");
	if (mysql_num_rows($q510)>0) {
		$studij=mysql_result($q510,0,0);
		$naziv_studija=mysql_result($q510,0,1);
		$semestar=mysql_result($q510,0,2);

		if ($studij==$u_odsjek) {
			nicemessage("Student je već upisan na studij $naziv_studija");
			return;
		}
		if ($studij!=$iz_odsjeka) {
			niceerror("Student je trenutno upisan na studij $naziv_studija a ne na izabrani studij!");
			print "Vaš zahtjev nije ispravan. Obrišite ga i napravite novi.";
			return;
		}

		// Ispis sa studija
		?>
		<p>Najprije morate ispisati studenta sa studija <?=$naziv_studija?>. <a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=ispis&studij=<?=$iz_odsjeka?>&semestar=<?=$semestar?>&godina=<?=$ak_god?>">Kliknite ovdje da ispišete studenta sa studija,</a> a zatim se vratite na stranicu &quot;Promjena odsjeka&quot; kako biste ga/je upisali na novi studij.</p>
		<?
		return;
	}

	// Koji je zadnji semestar slušao?
	$q560 = myquery("select ss.studij, ss.semestar, s.naziv from student_studij as ss, studij as s where ss.student=$osoba and ss.studij=s.id order by ss.akademska_godina desc, ss.semestar desc");
	$studij = mysql_result($q560,0,0);
	$zadnji_semestar = mysql_result($q560,0,1);
	$naziv_studija = mysql_result($q560,0,2);
	if ($studij != $iz_odsjeka) {
		niceerror("Student je prošle godine bio upisan na studij $naziv_studija, a ne na odabrani studij!");
		print "Vaš zahtjev nije ispravan. Obrišite ga i napravite novi.";
		return;
	}

	$q570 = myquery("select naziv from studij where id=$u_odsjek");
	$naziv_ciljnog = mysql_result($q570,0,0);

	print "<p>Provjerite da li student ima uslove za upis u viši semestar ili nema!!!</p>\n";
	if ($zadnji_semestar%2==1) {
		$manji=$zadnji_semestar-1;
		$veci=$zadnji_semestar+1;
		?>
		<p><a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=upis&studij=<?=$u_odsjek?>&semestar=<?=$manji?>&godina=<?=$ak_god?>">Ponovo upiši studenta na <?=$naziv_ciljnog?>, <?=$manji?>. semestar.</a></p>
		<p><a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=upis&studij=<?=$u_odsjek?>&semestar=<?=$veci?>&godina=<?=$ak_god?>">Upiši studenta na <?=$naziv_ciljnog?>, <?=$veci?>. semestar.</a></p>
		<?
	}
	else {
		?>
		<p><a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=upis&studij=<?=$u_odsjek?>&semestar=<?=$zadnji_semestar?>&godina=<?=$ak_god?>">Ponovo upiši studenta na <?=$naziv_ciljnog?>, <?=$zadnji_semestar?>. semestar.</a></p>
		<?
	}
	return;
}


// Akcija: kratki izvjestaj
if ($_REQUEST['akcija']=="kratkiizvj") {
	$q220 = myquery("select s.id, s.naziv from studij as s, tipstudija as ts where s.tipstudija=ts.id and ts.moguc_upis=1 and ts.ciklus=1");
	while ($r220 = mysql_fetch_row($q220)) {
		$ime_odsjeka[$r220[0]] = $r220[1];
	}

	$q400 = myquery("select iz_odsjeka,u_odsjek from promjena_odsjeka where akademska_godina=$ak_god");
	$total=0;
	while ($r400 = mysql_fetch_row($q400)) {
		$iz[$r400[0]]++;
		$u[$r400[1]]++;
		$total++;
	}

	?>
	<table border="1"><tr bgcolor="#CCCCCC"><td><b>Studij</b></td> <td><b>Sa studija</b></td> <td><b>Na studij</b></td> <td><b>Razlika</b></td></tr>
	<?
	foreach ($ime_odsjeka as $id => $ime) {
		?><tr><td><?=$ime?></td><td><?=$iz[$id]?></td> <td><?=$u[$id]?></td> <td bgcolor="#EEEEEE"><?=($u[$id]-$iz[$id])?></td></tr>
		<?
	}

	?>
	<tr><td>UKUPNO</td><td colspan="3" align="right"><?=$total?> zahtjeva</td></tr></table>
	<?

	return;
}

// Akcija: izvjestaj
if ($_REQUEST['akcija']=="izvjestaj") {
	$suma = 0;
	foreach ($_REQUEST as $key=>$value) {
		if (substr($key,0,6) == "limit-") {
			$studij=intval(substr($key,6));
			$limit[$studij]=intval($value);
			$suma += intval($value);
		}
	}

	if ($suma != 0) {
		niceerror("Suma svih limita mora biti nula!");
		return;
	}


	// Zahtjeve sortiramo po broju polozenih predmeta i prosjecnoj ocjeni
	$zahtjevi=array();
	global $brojpredmeta, $prosjek; // zbog usort() :(

	$q200 = myquery("select po.osoba, po.iz_odsjeka, po.u_odsjek, o.ime, o.prezime from promjena_odsjeka as po, osoba as o where po.osoba=o.id and po.akademska_godina=$ak_god");
	while ($r200 = mysql_fetch_row($q200)) {
		$zahtjevi[] = $r200[0];
		$imeiprezime[$r200[0]] = "$r200[3] $r200[4]";
		$izodsjeka[$r200[0]] = $r200[1];
		$uodsjek[$r200[0]] = $r200[2];

		$uk_izodsjeka[$r200[1]]++;
		$uk_uodsjek[$r200[2]]++;

		// Prosjek
		$q210 = myquery("select ko.ocjena from konacna_ocjena as ko, ponudakursa as pk where ko.student=$r200[0] and ko.predmet=pk.predmet and pk.semestar<3");
		while ($r210 = mysql_fetch_row($q210)) {
			$brojpredmeta[$r200[0]]++;
			$prosjek[$r200[0]] += $r210[0];
		}
		$prosjek[$r200[0]] = $prosjek[$r200[0]] / $brojpredmeta[$r200[0]];
	}

	// Sort preko korisnicke funkcije
	function po_broju_i_prosjeku($s1, $s2) {
		global $brojpredmeta, $prosjek;
		if ($brojpredmeta[$s1]<$brojpredmeta[$s2]) {
			return 1;
		} else if ($brojpredmeta[$s1]>$brojpredmeta[$s2]) {
			return -1;
		} else if ($prosjek[$s1]<$prosjek[$s2]) {
			return 1;
		} else if ($prosjek[$s1]>$prosjek[$s2]) {
			return -1;
		}
		return 0;
	}

	usort($zahtjevi, po_broju_i_prosjeku);

	// Da li je zahtjev prihvacen ili odbijen?
	$odbijen = array();

	// Status -1 znaci da nije jos uvijek razmatran
	for ($i=0; $i<count($zahtjevi); $i++) $odbijen[$zahtjevi[$i]]=-1;

	for ($i=0; $i<count($zahtjevi); $i++)
		if ($brojpredmeta[$zahtjevi[$i]]<9) // Ponovci svakako ne mogu mijenjati odsjek
			$odbijen[$zahtjevi[$i]]=1;

	for ($i=0; $i<count($zahtjevi); $i++) {
		$osoba = $zahtjevi[$i];

		if ($odbijen[$osoba]!=-1) continue; // Vec obradjen

		if ($limit[$uodsjek[$osoba]]>0 && $limit[$izodsjeka[$osoba]]<0) {
			// Ulazi u limit
			$odbijen[$osoba]=0;
			$limit[$izodsjeka[$osoba]]++;
			$limit[$uodsjek[$osoba]]--;
			continue;
		}

		// Trazimo osobu s kojom ce se mijenjati
		for ($j=$i+1; $j<count($zahtjevi); $j++) {
			$osoba2 = $zahtjevi[$j];

			if ($odbijen[$osoba2]!=-1) continue; // Vec obradjen

			if ($izodsjeka[$osoba]==$uodsjek[$osoba2] && $uodsjek[$osoba]==$izodsjeka[$osoba2]) {
				// Klasicna zamjena
				$odbijen[$osoba]=0;
				$odbijen[$osoba2]=0;
				break;
			}

			if ($izodsjeka[$osoba]==$uodsjek[$osoba2]) {

				if ($limit[$uodsjek[$osoba]]>0 && $limit[$izodsjeka[$osoba2]]<0) {
					// Zamjena ulazi u limit
					$odbijen[$osoba]=0;
					$odbijen[$osoba2]=0;
					$limit[$izodsjeka[$osoba2]]++;
					$limit[$uodsjek[$osoba]]--;
					break;
				}

				// Trazimo trecu osobu za 1->2->3->1 zamjenu
				for ($k=$j+1; $k<count($zahtjevi); $k++) {
					$osoba3 = $zahtjevi[$k];

					if ($odbijen[$osoba3]!=-1) continue;

					if ($izodsjeka[$osoba2]==$uodsjek[$osoba3] && $izodsjeka[$osoba3]==$uodsjek[$osoba]) {
						$odbijen[$osoba]=0;
						$odbijen[$osoba2]=0;
						$odbijen[$osoba3]=0;
						break;
					}
				}
			}
			if ($odbijen[$osoba]==0) break; // Zbog $k petlje

			// Da li u limit ulazi kontra-zamjena?
			if ($izodsjeka[$osoba2]==$uodsjek[$osoba]) {
				if ($limit[$uodsjek[$osoba2]]>0 && $limit[$izodsjeka[$osoba1]]<0) {
					$odbijen[$osoba]=0;
					$odbijen[$osoba2]=0;
					$limit[$izodsjeka[$osoba]]++;
					$limit[$uodsjek[$osoba2]]--;
					break;
				}
			}

		}

		// Nista nismo nasli, ovaj je odbijen
		if ($odbijen[$osoba]==-1) $odbijen[$osoba]=1;
	}

	// Ispis
	$q220 = myquery("select id,kratkinaziv from studij where moguc_upis=1");
	while ($r220 = mysql_fetch_row($q220)) {
		$ime_odsjeka[$r220[0]] = $r220[1];
	}

	?>
	<b>Prihvaćeni zahtjevi:</b>
	<table border="1"><tr><td>R. br.</td><td>Ime i 
prezime</td><td>Iz 
odsjeka</td><td>U odsjek</td><td>Broj pol.</td><td>Prosjek</td></tr>
	<?
	$rbr=1;
	foreach ($zahtjevi as $osoba) {
		if ($odbijen[$osoba]==1) continue;
		print "<tr> 
<td>".$rbr++."</td> <td>".$imeiprezime[$osoba]."</td> 
<td>".$ime_odsjeka[$izodsjeka[$osoba]]."</td> <td>".$ime_odsjeka[$uodsjek[$osoba]]."</td> <td>".$brojpredmeta[$osoba]."</td> <td>".round($prosjek[$osoba],2)."</td></tr>\n";
	}

	?>
	</table>
	<p>&nbsp;</p>
	<b>Odbijeni zahtjevi:</b>
	<table border="1"><tr><td>R. br.</td><td>Ime i 
prezime</td><td>Iz 
odsjeka</td><td>U odsjek</td><td>Broj pol.</td><td>Prosjek</td></tr>
	<?
	$rbr=1;
	foreach ($zahtjevi as $osoba) {
		if ($odbijen[$osoba]==0) continue;
		print "<tr> 
<td>".$rbr++."</td> <td>".$imeiprezime[$osoba]."</td> 
<td>".$ime_odsjeka[$izodsjeka[$osoba]]."</td> <td>".$ime_odsjeka[$uodsjek[$osoba]]."</td> <td>".$brojpredmeta[$osoba]."</td> <td>".round($prosjek[$osoba],2)."</td></tr>\n";
	}

	print "</table>\n";

/*	foreach ($delta as $odsjek=>$broj) {
		print "Delta ".$ime_odsjeka[$odsjek]." je $broj<br/>";
		print "Odobreno ".$ime_odsjeka[$odsjek]." je ".$odobreno[$odsjek]."<br/>";
	}*/

	return;
}



// Spisak zahtjeva

?>
<b>Aktuelni zahtjevi (akademska <?=$ak_god_naziv?>):</b>
<ul>
<?

$q10 = myquery("select po.id, o.ime, o.prezime, s.naziv, po.u_odsjek, o.id from promjena_odsjeka as po, osoba as o, studij as s where po.osoba=o.id and po.iz_odsjeka=s.id and po.akademska_godina=$ak_god");
if (mysql_num_rows($q10)<1) 
	print "<li>Nema zahtjeva</li\n";
$total=0;
while ($r10 = mysql_fetch_row($q10)) {
	$q20 = myquery("select naziv from studij where id=$r10[4]");
	?>
	<li><a href="?sta=studentska/osobe&akcija=edit&osoba=<?=$r10[5]?>"><?=$r10[1]?> <?=$r10[2]?></a> - sa "<?=$r10[3]?>" na "<?=mysql_result($q20,0,0)?>" (<a href="?sta=studentska/prodsjeka&akcija=obrisi&id=<?=$r10[0]?>&ak_god=<?=$ak_god?>">obriši zahtjev</a>) (<a href="?sta=studentska/prodsjeka&akcija=prihvati&id=<?=$r10[0]?>&ak_god=<?=$ak_god?>">prihvati zahtjev</a>)</li>
	<?
	$total++;
}

print "</ul><p>Ukupno: $total zahtjeva * <a href=\"?sta=studentska/prodsjeka&akcija=kratkiizvj\">Statistički pregled</a></p>\n\n";



// Novi zahtjev

// Upit za spisak odsjeka
$spisak_odsjeka = "<option></option>\n";
$q30 = myquery("select s.id, s.kratkinaziv from studij as s, tipstudija as ts where s.tipstudija=ts.id and ts.moguc_upis=1 and ts.ciklus=1 order by s.kratkinaziv"); // Promjena odsjeka ima smisla samo na prvom ciklusu
while ($r30 = mysql_fetch_row($q30)) {
	$spisak_odsjeka .= "<option value=\"$r30[0]\">$r30[1]</option>\n";
}

?>
<hr><br/>
<b>Upišite novi zahtjev za promjenu odsjeka:</b><br/><br/>
<?=genform("POST")?>
<input type="hidden" name="akcija" value="dodaj">
Ime: <input type="text" name="ime" size="10">
Prezime: <input type="text" name="prezime" size="10"><br/>
Želi preći sa odsjeka: 
<select name="iz_odsjeka">
<?=$spisak_odsjeka?>
</select>
Na odsjek:
<select name="u_odsjek">
<?=$spisak_odsjeka?>
</select>
<br/>
<input type="submit" value=" Dodaj "></form>
<?




// Izvjestaji

?>
<hr><br/>
<b>Spisak prihvaćenih i odbijenih zahtjeva:</b><br/><br/>
<?=genform("POST")?>
<input type="hidden" name="akcija" value="izvjestaj">
Iznos maksimalne promjene broja studenata po odsjeku:<br/>
<?

$q40 = myquery("select s.id, s.kratkinaziv from studij as s, tipstudija as ts where s.tipstudija=ts.id and ts.moguc_upis=1 and ts.ciklus=1 order by s.kratkinaziv"); // hardkodirano samo za prvi ciklus
// zato što promjena studija na drugom ciklusu (koji traje 1-2 godine) baš i nema smisla
while ($r40 = mysql_fetch_row($q40)) {
	print "$r40[1]: <input type=\"text\" name=\"limit-$r40[0]\" value=\"0\" size=\"3\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ";
}
?>
<input type="submit" value=" Prikaži ">
</form>
<br/>


<?


}

?>
