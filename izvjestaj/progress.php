<?

// IZVJESTAJ/PROGRESS - pregled svih kurseva koje je slusao student sa ostvarenim bodovima (eventualno sa razdvojenim ispitima)


// TODO: spojiti sa izvjestaj/index???


function izvjestaj_progress() {

global $userid, $user_studentska, $user_siteadmin;


// Ulazni parametar
$student = intval($_REQUEST['student']);
$razdvoji = intval($_REQUEST['razdvoji_ispite']); // da li prikazivati nepoložene pokušaje ispita


// Prava pristupa
if (!$user_studentska && !$user_siteadmin && $userid!=$student) {
	biguglyerror("Nemate pravo pristupa ovom izvještaju");
	zamgerlog("nije studentska, a pristupa tudjem izvjestaju ($student)", 3);
	zamgerlog2("nije studentska, a pristupa tudjem izvjestaju", $student);
	return;
}


?>
<p>Univerzitet u Sarajevu<br>
Elektrotehnički fakultet Sarajevo</p>
<p>Datum i vrijeme izvještaja: <?=date("d. m. Y. H:i");?></p>
<?

// Podaci o studentu
$q100 = db_query("select ime,prezime,brindexa from osoba where id=$student");
if (!($r100 = db_fetch_row($q100))) {
	biguglyerror("Student se ne nalazi u bazi podataka.");
	zamgerlog("nepoznat ID $student",3); // 3 = greska
	zamgerlog2("nepoznat id korisnika", $student); // 3 = greska
	return;
}


?>
<h2>Pregled ostvarenih rezultata na predmetima</h2>
<p>&nbsp;<br>
<big>Student:
<b><?=$r100[0]." ".$r100[1]?></b></big><br>
Broj indeksa: <?=$r100[2]?><br><br><br>

<?

$imena_ocjena = array("", "", "", "", "", "5 (pet)", "6 (šest)", "7 (sedam)", "8 (osam)", "9 (devet)", "10 (deset)", "ispunio/la obaveze");


// Ocjene po odluci:

$q105 = db_query("select ko.ocjena, p.naziv, UNIX_TIMESTAMP(o.datum), o.broj_protokola from konacna_ocjena as ko, odluka as o, predmet as p where ko.odluka=o.id and ko.predmet=p.id and ko.student=$student");
if (db_num_rows($q105)>0) {
	?>
	<p><b>Ocjene donesene odlukom (nostrifikacija, promjena studija itd.):</b><br><ul>
	<?
}
while ($r105 = db_fetch_row($q105)) {
	print "<li><b>$r105[1]</b> - ocjena: ".$imena_ocjena[$r105[0]]."<br>(odluka br. $r105[3] od ".date("d. m. Y.", $r105[2]).")</li>\n";
}
if (db_num_rows($q105)>0) print "</ul></p><p>&nbsp;</p>\n";



// Ocjene priznavanje

$q125 = db_query("select naziv_predmeta, ocjena, odluka, akademska_godina, strana_institucija from priznavanje where student=$student order by odluka, akademska_godina, naziv_predmeta");
if (db_num_rows($q125)>0) {
	?>
	<p><b>Priznavanje ocjena ostvarenih na drugoj instituciji po osnovu mobilnosti studenata:</b></p>
	<?
}
$i = 1; $stara_odluka = $stara_ag = $stara_inst = 0;
while ($r125 = db_fetch_row($q125)) {
	if ($r125[2] != $stara_odluka || $r125[3] != $stara_ag || $r125[4] != $stara_inst) {
		if ($stara_odluka != 0) print "</ul>\n";
		$stara_odluka = $r125[2];
		$stara_ag = $r125[3];
		$stara_inst = $r125[4];
		$q115 = db_query("select UNIX_TIMESTAMP(datum), broj_protokola from odluka where id=$stara_odluka");
		if (db_num_rows($q115) > 0)
			$odluka_ispis = " (odluka br. ".db_result($q115,0,1)." od ".date("d. m. Y.", db_result($q115,0,0)).")";
		$q127 = db_query("SELECT naziv FROM akademska_godina WHERE id=$stara_ag");
		?>
		<p>Institucija: &quot;<?=$stara_inst?>&quot;, akademska <?=db_result($q127,0,0)?>. godina<?=$odluka_ispis?>:</p><ul>
		<?
	}
	print "<li><b>$r125[0]</b> - ocjena: ".$imena_ocjena[$r125[1]]."</li>\n";
}
print "</ul>";


// Ocjene po akademskoj godini

$rbr=1;
$q110 = db_query("select id,naziv from akademska_godina order by naziv");
while ($r110 = db_fetch_row($q110)) {
	for ($sem=1; $sem>=0; $sem--) {
		if ($sem==1) $naziv_sem="Zimski semestar"; else $naziv_sem="Ljetnji semestar";

		$q120 = db_query("select pk.id, p.naziv, p.id from predmet as p, ponudakursa as pk, student_predmet as sp where sp.student=$student and sp.predmet=pk.id and pk.akademska_godina=$r110[0] and pk.predmet=p.id and pk.semestar%2=$sem order by p.naziv");
		if (db_num_rows($q120)>0) {
			// Zaglavlje tabele
			?>
			<p><b>Akademska godina: <?=$r110[1]?>, <?=$naziv_sem?></b></p>
			<table width="850" border="1" cellspacing="0" cellpadding="3"><tr>
				<th width="20">&nbsp;</th>
				<th width="155">Predmet</th>
				<th width="75">Ak. godina</th>
				<th width="75">Prisustvo</th>
				<th width="75">Zadaće i seminarski</th>
				<th width="75">I parcijalni</th>
				<th width="75">II parcijalni</th>
				<th width="75">Integralni</th>
				<th width="75">Završni</th>
				<th width="75">UKUPNO</th>
				<th width="75">Ocjena</th>
			</tr>
			<?

		}

		while ($r120 = db_fetch_row($q120)) {
			print "<tr><td>".($rbr++)."</td><td>".$r120[1]."</td><td>".$r110[1]."</td>";
			$ukupno=0;

			// Od kojih komponenti se sastoji ispit?
			$prisustvo = $zadace = $parc1 = $parc2 = $int = $zavrsni = $ukupno = "&nbsp;";
			$kp1 = $kp2 = 0; // Čuvamo id-ove komponenti za 1 i 2 parcijalni, radi kasnijeg ispisa
			
			$q130 = db_query("select k.id, k.gui_naziv, k.tipkomponente, kb.bodovi from komponenta as k, tippredmeta_komponenta as tpk, akademska_godina_predmet as agp, komponentebodovi as kb where agp.predmet=$r120[2] and agp.akademska_godina=$r110[0] and agp.tippredmeta=tpk.tippredmeta and tpk.komponenta=k.id and kb.komponenta=k.id and kb.student=$student and kb.predmet=$r120[0]");
			while ($r130 = db_fetch_row($q130)) {
				$bodovi = $r130[3];
				if ($r130[2] == 1) { // tip komponente = ispit
					if ($r130[1] == "I parcijalni" || $r130[1] == "I parc" || $r130[1] == "Parcijalni" || $r130[1] == "1 parcijalni" || $r130[1] == "1. parcijalni") {
						$kp1 = $r130[0];
						$parc1 += $bodovi;
					} else if ($r130[1] == "II parcijalni" || $r130[1] == "II parc" || $r130[1] == "2 parcijalni" || $r130[1] == "2. parcijalni") {
						$kp2 = $r130[0];
						$parc2 += $bodovi;
					} else if ($r130[1] == "Usmeni" || $r130[1] == "Završni" || $r130[1] == "Usmeni ispit" || $r130[1] == "Završni ispit")
						$zavrsni += $bodovi;
					else // Ako je nepoznat tip ispita, pribrajamo prvom parcijalnom
						$parc1 += $bodovi;
				}
				else if ($r130[2] == 2) // tip komponente = integralni
					$int += $bodovi;
				else if ($r130[2] == 3) // prisustvo
					$prisustvo += $bodovi;
				else if ($r130[2] == 4) // zadace
					$zadace += $bodovi;
				else if ($r130[2] == 5) { // fiksna komponenta
					if ($r130[1] == "Prisustvo")
						$prisustvo += $bodovi;
					else if ($r130[1] == "Zadaće" || $r130[1] == "Zadace")
						$zadace += $bodovi;
					else if ($r130[1] == "I parcijalni")
						$parc1 += $bodovi;
					else if ($r130[1] == "II parcijalni")
						$parc2 += $bodovi;
					else if ($r130[1] == "Integralni")
						$int += $bodovi;
					else if ($r130[1] == "Parcijalni" || $r130[1] == "Ispit")
						// Nepoznat tip ispita, pribrajamo 1. parc
						$parc1 += $bodovi;
					else // Pretpostavka da je neki seminarski ili projekat
						$zadace += $bodovi;
				}
				$ukupno += $bodovi;
			}

			print "<td>$prisustvo</td><td>$zadace</td>";
			if ($razdvoji==0) {
				print "<td>$parc1</td><td>$parc2</td><td>$int</td>";
			} else {
				// Treba razdvojiti ispite... gledamo tabelu ispiti
				$q140 = db_query("select io.ocjena, i.komponenta, i.datum, k.tipkomponente from ispitocjene as io, ispit as i, ponudakursa as pk, komponenta as k where io.student=$student and io.ispit=i.id  and i.predmet=pk.predmet and i.akademska_godina=pk.akademska_godina and pk.id=$r120[0] and i.komponenta=k.id order by i.datum");

				$ispis = array();
				$ispis1p = $ispis2p = $ispisint = "";
				while ($r140 = db_fetch_row($q140)) {
					if ($r140[0] == -1) continue; // skip
					list ($g,$m,$d) = explode("-",$r140[2]);
					if ($r140[3] == 2) { // tipkomponente 2 = integralni
						$ispisint .= "$r140[0] ($d.$m.)<br>";
					} else {
						if ($r140[1]==$kp1)
							$ispis1p .= "$r140[0] ($d.$m.)<br>";
						else if ($r140[1]==$kp2)
							$ispis2p .= "$r140[0] ($d.$m.)<br>";
						//else
						// Ostale komponente ispita nećemo ni uzimati u obzir
						// To može biti usmeni, a može biti i neprepoznata komponenta
					}
				}
				if ($ispis1p=="") $ispis1p="&nbsp;";
				if ($ispis2p=="") $ispis2p="&nbsp;";
				if ($ispisint=="") $ispisint="&nbsp;";
	
				print "<td>$ispis1p</td><td>$ispis2p</td><td>$ispisint</td>";
			}
	
			print "<td>$zavrsni</td><td>$ukupno</td>\n";
			
			// Konacna ocjena
			$q150 = db_query("select ocjena from konacna_ocjena where student=$student and predmet=$r120[2] and akademska_godina=$r110[0]");
			if ($r150 = db_fetch_row($q150))
				if ($r150[0] > 5)
					print "<td>".$imena_ocjena[$r150[0]]."</td>";
				else
					print "<td>Nije ocijenjen</td>";
			else
				print "<td>Nije ocijenjen</td>";
	
	
			print "</tr>";
		} // while ($r120...
		if (db_num_rows($q120)>0)
			print "</table>\n\n";
	} // for ($i=0...

}

}

?>
