<?

// IZVJESTAJ/PROGRESS - pregled svih kurseva koje je slusao student sa ostvarenim bodovima (eventualno sa razdvojenim ispitima)

// v3.9.1.0 (2008/04/22) + Izvjestaj izdvojen iz bivseg admin_izvjestaj.php, prebaceno na komponente i student_predmet; razdvojen po godinama i semestrima
// v4.0.0.0 (2009/02/19) + Release
// v4.0.9.1 (2009/03/31) + Tabela ispit preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.2 (2009/03/31) + Tabela konacna_ocjena preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.3 (2009/09/15) + Ocjene po odluci


// TODO: spojiti sa izvjestaj/index???

// NAPOMENA: ovaj kod radi samo sa ETF standard predmetima!


function izvjestaj_progress() {

global $userid, $user_studentska, $user_siteadmin;


// Ulazni parametar
$student = intval($_REQUEST['student']);
$razdvoji = intval($_REQUEST['razdvoji_ispite']); // da li prikazivati nepoložene pokušaje ispita


// Prava pristupa
if (!$user_studentska && !$user_siteadmin && $userid!=$student) {
	biguglyerror("Nemate pravo pristupa ovom izvještaju");
	zamgerlog("nije studentska, a pristupa tudjem izvjestaju ($student)", 3);
	return;
}


?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>
<p><b>Pregled ostvarenog rezultata na predmetima</b></p>
<?

// Podaci o studentu
$q100 = myquery("select ime,prezime,brindexa from osoba where id=$student");
if (!($r100 = mysql_fetch_row($q100))) {
	biguglyerror("Student se ne nalazi u bazi podataka.");
	zamgerlog("nepoznat ID $student",3); // 3 = greska
	return;
}
/*if ($r100[3] != 1) {
	biguglyerror("Nepoznat student");
	zamgerlog("korisnik u$student nema status studenta",3);
	return;
}*/


?>
<h2>Pregled ostvarenih rezultata na predmetima</h2>
<p>&nbsp;</br>
<big>Student:
<b><?=$r100[0]." ".$r100[1]?></b></big><br />
Broj indeksa: <?=$r100[2]?><br/><br/><br/>

<?

$imena_ocjena = array("Nije položio/la", "Šest","Sedam","Osam","Devet","Deset");


// Ocjene po odluci:

$q105 = myquery("select ko.ocjena, p.naziv, UNIX_TIMESTAMP(o.datum), o.broj_protokola from konacna_ocjena as ko, odluka as o, predmet as p where ko.odluka=o.id and ko.predmet=p.id and ko.student=$student");
if (mysql_num_rows($q105)>0) {
	?>
	<p><b>Ocjene donesene odlukom (nostrifikacija, promjena studija itd.):</b><br/><ul>
	<?
}
while ($r105 = mysql_fetch_row($q105)) {
	print "<li><b>$r105[1]</b> - ocjena: $r105[0] (".$imena_ocjena[$r105[0]-5].")<br/>(odluka br. $r105[3] od ".date("d. m. Y.", $r105[2]).")</li>\n";
}
if (mysql_num_rows($q105)>0) print "</ul></p><p>&nbsp;</p>\n";



// Ocjene po akademskoj godini

$rbr=1;
$q110 = myquery("select id,naziv from akademska_godina order by naziv");
while ($r110 = mysql_fetch_row($q110)) {
	for ($sem=1; $sem>=0; $sem--) {
		if ($sem==1) $naziv_sem="Zimski semestar"; else $naziv_sem="Ljetnji semestar";

		$q120 = myquery("select pk.id, p.naziv, p.id from predmet as p, ponudakursa as pk, student_predmet as sp where sp.student=$student and sp.predmet=pk.id and pk.akademska_godina=$r110[0] and pk.predmet=p.id and pk.semestar%2=$sem order by p.naziv");
		if (mysql_num_rows($q120)>0) {
			// Zaglavlje tabele
			?>
			<p><b>Akademska godina: <?=$r110[1]?>, <?=$naziv_sem?></b></p>
			<table width="775" border="1" cellspacing="0" cellpadding="3"><tr bgcolor="#AAAAAA">
				<td width="20">&nbsp;</td>
				<td width="155">Predmet</td>
				<td width="75">Ak. godina</td>
				<td width="75">Prisustvo</td>
				<td width="75">Zadaće</td>
				<td width="75">I parcijalni</td>
				<td width="75">II parcijalni</td>
				<td width="75">Integralni</td>
				<td width="75">UKUPNO</td>
				<td width="75">Ocjena</td>
			</tr>
			<?
		}

		while ($r120 = mysql_fetch_row($q120)) {
			print "<tr><td>".($rbr++)."</td><td>".$r120[1]."</td><td>".$r110[1]."</td>";
			$ukupno=0;
	
			// Komponente
			$kb = array();
			for ($i=1; $i<=7; $i++) $kb[$i]="&nbsp;"; // radi ispisa tabele
			$suma=0;
			$q130 = myquery("select komponenta,bodovi from komponentebodovi where student=$student and predmet=$r120[0]");
			while ($r130 = mysql_fetch_row($q130)) {
				$kb[$r130[0]] = $r130[1];
				$suma += $r130[1];
			}
	
			print "<td>".$kb[5]."</td><td>".$kb[6]."</td>";
			if ($razdvoji==0) {
				print "<td>".$kb[1]."</td><td>".$kb[2]."</td><td>".$kb[3]."</td>";
			} else {
				// Treba razdvojiti ispite... gledamo tabelu ispiti
				$q140 = myquery("select io.ocjena,i.komponenta,i.datum from ispitocjene as io, ispit as i, ponudakursa as pk where io.student=$student and io.ispit=i.id  and i.predmet=pk.predmet and i.akademska_godina=pk.akademska_godina and pk.id=$r120[0] order by i.datum");

				$ispis = array();
				while ($r140 = mysql_fetch_row($q140)) {
					if ($r140[0] == -1) continue; // skip
					list ($g,$m,$d) = explode("-",$r140[2]);
					$ispis[$r140[1]] .= "$r140[0] ($d.$m.)<br/>";
				}
	
				for ($i=1; $i<4; $i++)
					if ($ispis[$i] == "")
						print "<td>&nbsp;</td>\n";
					else
						print "<td>".$ispis[$i]."</td>\n";
			}
	
			// Ukupan broj bodova.
	/*		$total = $kb[4] + $kb[5] + $kb[6];
			if ($kb[3]>$kb[1]+$kb[2]) 
				$total += $kb[3];
			else 
				$total += $kb[1]+$kb[2];
			print "<td>$total</td>\n";*/
			print "<td>$suma</td>\n";
			
			// Konacna ocjena
			$q150 = myquery("select ocjena from konacna_ocjena where student=$student and predmet=$r120[2] and akademska_godina=$r110[0]");
			if ($r150 = mysql_fetch_row($q150))
				if ($r150[0] > 5)
					print "<td>$r150[0] (".$imena_ocjena[$r150[0]-5].")</td>";
				else
					print "<td>5 (".$imena_ocjena(0).")</td>";
			else
				print "<td>Nije ocijenjen</td>";
	
	
			print "</tr>";
		} // while ($r120...
		print "</table>\n\n";
	} // for ($i=0...

}

}

?>