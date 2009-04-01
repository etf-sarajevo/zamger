<?

// STUDENTSKA/PRODSJEKA - odobrenja za promjenu odsjeka

// v3.9.1.0 (2008/09/11) + Novi modul studentska/prodsjeka
// v3.9.1.1 (2008/09/24) + Dodan link na detalje o studentu
// v4.0.0.0 (2009/02/19) + Release
// v4.0.9.1 (2009/03/31) + Tabela konacna_ocjena preusmjerena sa ponudakursa na tabelu predmet


function studentska_prodsjeka() {

global $userid,$user_siteadmin,$user_studentska;


// Provjera privilegija

if (!$user_studentska && !$user_siteadmin) {
	zamgerlog("nije studentska",3); // 3: error
	biguglyerror("Pristup nije dozvoljen.");
	return;
}


?>
<h3>Studentska služba - Zahtjevi za promjenu odsjeka</h3>
<?


// Akcija: brisanje zahtjeva
if ($_REQUEST['akcija']=="obrisi") {
	$id = intval($_REQUEST['id']);
	$q5 = myquery("delete from promjena_odsjeka where id=$id");
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
		$q110 = myquery("insert into promjena_odsjeka set osoba=$osoba, iz_odsjeka=$iz_odsjeka, u_odsjek=$u_odsjek");
	}
}


// Akcija: kratki izvjestaj
if ($_REQUEST['akcija']=="kratkiizvj") {
	$q220 = myquery("select id, naziv from studij where moguc_upis=1");
	while ($r220 = mysql_fetch_row($q220)) {
		$ime_odsjeka[$r220[0]] = $r220[1];
	}

	$q400 = myquery("select iz_odsjeka,u_odsjek from promjena_odsjeka");
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

	$q200 = myquery("select po.osoba, po.iz_odsjeka, po.u_odsjek, o.ime, o.prezime from promjena_odsjeka as po, osoba as o where po.osoba=o.id");
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
<b>Aktuelni zahtjevi:</b>
<ul>
<?

$q10 = myquery("select po.id, o.ime, o.prezime, s.naziv, po.u_odsjek, o.id from promjena_odsjeka as po, osoba as o, studij as s where po.osoba=o.id and po.iz_odsjeka=s.id");
if (mysql_num_rows($q10)<1) 
	print "<li>Nema zahtjeva</li\n";
$total=0;
while ($r10 = mysql_fetch_row($q10)) {
	$q20 = myquery("select naziv from studij where id=$r10[4]");
	?>
	<li><a href="?sta=studentska/osobe&akcija=edit&osoba=<?=$r10[5]?>"><?=$r10[1]?> <?=$r10[2]?></a> - sa "<?=$r10[3]?>" na "<?=mysql_result($q20,0,0)?>" (<a href="?sta=studentska/prodsjeka&akcija=obrisi&id=<?=$r10[0]?>">obriši zahtjev</a>)</li>
	<?
	$total++;
}

print "</ul><p>Ukupno: $total zahtjeva * <a href=\"?sta=studentska/prodsjeka&akcija=kratkiizvj\">Statistički pregled</a></p>\n\n";



// Novi zahtjev

// Upit za spisak odsjeka
$spisak_odsjeka = "<option></option>\n";
$q30 = myquery("select id,kratkinaziv from studij where moguc_upis=1 order by kratkinaziv");
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

$q40 = myquery("select id,kratkinaziv from studij where moguc_upis=1 order by kratkinaziv");
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
