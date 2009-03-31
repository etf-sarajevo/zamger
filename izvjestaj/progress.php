<?

// IZVJESTAJ/PROGRESS - pregled svih kurseva koje je slusao student sa ostvarenim bodovima (eventualno sa razdvojenim ispitima)

// v3.9.1.0 (2008/04/22) + Izvjestaj izdvojen iz bivseg admin_izvjestaj.php, prebaceno na komponente i student_predmet; razdvojen po godinama i semestrima
// v4.0.0.0 (2009/02/19) + Release
// v4.0.9.1 (2009/03/31) + Tabela ispit preusmjerena sa ponudakursa na tabelu predmet


// TODO: spojiti sa izvjestaj/index???

// NAPOMENA: ovaj kod radi samo sa ETF standard predmetima!


function izvjestaj_progress() {


// Ulazni parametar
$student = intval($_REQUEST['student']);
$razdvoji = intval($_REQUEST['razdvoji_ispite']); // da li prikazivati nepoložene pokušaje ispita


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
<p>&nbsp;</br>
Student:</br>
<h1><?=$r100[0]." ".$r100[1]?></h1><br/>
Broj indeksa: <?=$r100[2]?><br/><br/><br/>

<?

$imena_ocjena = array("Nije položio/la", "Šest","Sedam","Osam","Devet","Deset");

$rbr=1;
$q110 = myquery("select id,naziv from akademska_godina order by naziv");
while ($r110 = mysql_fetch_row($q110)) {
	for ($sem=1; $sem>=0; $sem--) {
		if ($sem==1) $naziv_sem="Zimski semestar"; else $naziv_sem="Ljetnji semestar";

		$q120 = myquery("select pk.id, p.naziv from predmet as p, ponudakursa as pk, student_predmet as sp where sp.student=$student and sp.predmet=pk.id and pk.akademska_godina=$r110[0] and pk.predmet=p.id and pk.semestar%2=$sem order by p.naziv");
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
			$q150 = myquery("select ocjena from konacna_ocjena where student=$student and predmet=$r120[0]");
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