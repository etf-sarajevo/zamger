<?

// IZVJESTAJ/ISPIT - statistika pojedinacnog ispita

// v3.9.1.0 (2008/04/11) + Izvjestaj izdvojen iz bivseg admin_izvjestaj.php
// v3.9.1.1 (2008/08/28) + Dodana provjera da li postoji predmet
// v4.0.0.0 (2009/02/19) + Release
// v4.0.9.1 (2009/03/24) + Prebacena polja ects i tippredmeta iz tabele ponudakursa u tabelu predmet
// v4.0.9.2 (2009/03/25) + nastavnik_predmet preusmjeren sa tabele ponudakursa na tabelu predmet
// v4.0.9.3 (2009/03/31) + Tabela ispit preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.4 (2009/03/31) + Tabela konacna_ocjena preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.5 (2009/04/16) + Popravljen logging
// v4.0.9.6 (2009/04/22) + Izbacujem predmet kao parametar (nepotrebno, predmet je sadrzan u ispitu), a ispit=svi prebacujem u drugi izvjestaj pod imenom statistika_predmeta
// v4.0.9.7 (2009/04/27) + Parametar "predmet" je ustvari ponudakursa, pa treba dodati upit koji saznaje predmet i akademsku godinu za izvjestaj/statistika_predmeta
// v4.0.9.8 (2009/04/29) + Preusmjeravam tabelu labgrupa sa tabele ponudakursa na tabelu predmet
// v4.0.9.9 (2009/05/05) + Ne prikazuj virtualne grupe posto je statistika za sve studente vec data


// Provjeriti ispravnost dijela sa grupama



function izvjestaj_ispit() {

global $userid,$user_nastavnik,$user_studentska,$user_siteadmin;



?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>
<p>Datum i vrijeme izvještaja: <?=date("d. m. Y. H:i");?></p>
<?



// Parametar
$ispit = intval($_REQUEST['ispit']);

// Upit za ispit

$q10 = myquery("select UNIX_TIMESTAMP(i.datum), k.gui_naziv, k.maxbodova, k.prolaz, i.predmet, i.akademska_godina from ispit as i, komponenta as k where i.id=$ispit and i.komponenta=k.id");
if (mysql_num_rows($q10)<1) {
	biguglyerror("Nepoznat ispit!");
	zamgerlog ("nepoznat ispit $ispit",3);
	return;
}

$finidatum = date("d. m. Y.", mysql_result($q10,0,0));
$naziv = mysql_result($q10,0,1);
$maxbodova = mysql_result($q10,0,2);
$prolaz = mysql_result($q10,0,3);
$predmet = mysql_result($q10,0,4);
$ag = mysql_result($q10,0,5);


// Dodatna provjera privilegija
if (!$user_studentska && !$user_siteadmin) {
	$q20 = myquery("select nivo_pristupa from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
	if (mysql_num_rows($q20) < 1) {
		biguglyerror("Nemate permisije za pristup ovom izvještaju");
		zamgerlog ("nije admin predmeta pp$predmet godina ag$ag",3); // 3 = error
		return;
	}
}



// Naziv predmeta, akademska godina
$q21 = myquery("select naziv from predmet where id=$predmet");
$q22 = myquery("select naziv from akademska_godina where id=$ag");

?>
	<p>&nbsp;</p>
	<h1><?=mysql_result($q21,0,0)?> <?=mysql_result($q22,0,0)?></h1>
	<h3><?=$naziv?>, <?=$finidatum?></h3>
<?


// Opste statistike - pojedinacni ispit

$q200 = myquery("select count(*) from ispitocjene where ispit=$ispit");
$ukupno_izaslo = mysql_result($q200,0,0);

$q210 = myquery("select count(*) from ispitocjene where ispit=$ispit and ocjena>=$prolaz");
$polozilo = mysql_result($q210,0,0);

$q220 = myquery("select count(*) from student_predmet as sp, ponudakursa as pk where sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
$slusa_predmet = mysql_result($q220,0,0);

?>
<p>Ukupno izašlo studenata: <b><?=$ukupno_izaslo?></b><br/>
Položilo: <b><?=$polozilo?></b><br/>
Prolaznost: <b><?=procenat($polozilo,$ukupno_izaslo)?></b></p>

<p>Od studenata koji slušaju predmet, nije izašlo: <b><?=($slusa_predmet-$ukupno_izaslo)?></b></p>

<?
$imeprezime = $brindexa = array();

$qtermini = myquery("SELECT it.id,UNIX_TIMESTAMP(it.datumvrijeme)
				     FROM ispit_termin it
					 INNER JOIN ispit i ON i.id = it.ispit
					 WHERE i.id=$ispit
					 ORDER BY it.datumvrijeme
					");

$broj_termina =0;
while ($rtermini = mysql_fetch_row($qtermini)) {
	
	$broj_termina ++;
	$id_termina = $rtermini[0];
	$datum_termina= date("H:i",$rtermini[1]);
	$ispit = intval($_REQUEST['ispit']);
	print "<h3>Termin $broj_termina : $datum_termina</h3>";
	$q10 = myquery("select o.id, o.prezime, o.ime, o.brindexa 
					from osoba as o, student_predmet as sp, ponudakursa as pk, student_ispit_termin sit, ispit_termin it, ispit i
					where 
						sp.predmet=pk.id 
						and sp.student=o.id
						and sit.student=o.id
						and sit.ispit_termin=it.id
						and it.ispit = i.id
						and pk.predmet=$predmet 
						and pk.akademska_godina=$ag
						and i.id=$ispit
						and it.id = $id_termina
						");
	if (mysql_num_rows($q10)<1) {
		print "<p>------------------------------------------------------</p>";
		print "<p>Nijedan student nije prijavljen na ovaj termin.</p>";
		print "<p>------------------------------------------------------</p>";
	}
	
	while ($r10 = mysql_fetch_row($q10)) {
		$imeprezime[$r10[0]] = "$r10[1] $r10[2]"; 
		$brindexa[$r10[0]] = "$r10[3]";
	}
	uasort($imeprezime,"bssort"); // bssort - bosanski jezik
	
	$q25 = myquery("select id from labgrupa where predmet=$predmet and akademska_godina=$ag and virtualna=1");
	$id_virtualne_grupe = mysql_result($q25,0,0);
	
	$spisak_grupa[0] = "[Bez grupe]"; // Dodajemo "nultu grupu" kojoj svi pripadaju
	$broj_ispita=0;
	$ispit_zaglavlje="";
	$oldkomponenta=0;
	
	$q30 = myquery("select i.id, UNIX_TIMESTAMP(i.datum), k.id, k.kratki_gui_naziv, k.tipkomponente, k.maxbodova, k.prolaz, k.opcija from ispit as i, komponenta as k where i.predmet=$predmet and i.akademska_godina=$ag and i.komponenta=k.id order by i.komponenta,i.datum");
	$imaintegralni=0;
	while ($r30 = mysql_fetch_row($q30)) {
		$komponenta = $r30[2];
		$imeispita = $r30[3];
		$tipkomponente = $r30[4];	
		if ($komponenta != $oldkomponenta && $tipkomponente != 2) { // 2 = integralni
			$oldkomponenta=$komponenta;
			$ispit_zaglavlje .= "<td align=\"center\">$imeispita</td>\n";
			$broj_ispita++;
		} else if ($tipkomponente == 2) {
			$imaintegralni=1;
		}
	
		$ispit_id_array[] = $r30[0];
		$ispit_komponenta[$r30[0]] = $r30[2];
	
		// Pripremamo podatke o komponentama
		$komponenta_tip[$r30[2]] = $r30[4];
		$komponenta_maxb[$r30[2]] = $r30[5];
		$komponenta_prolaz[$r30[2]] = $r30[6];
		$komponenta_opcija[$r30[2]] = "$r30[7]";
	}
	
	// Racunamo koliko je bilo moguce ostvariti bodova na predmetu (radi racunanja procenta)
	$mogucih_bodova=0; 
	foreach($komponenta_maxb as $kid => $kmb) 
		if ($komponenta_tip[$kid] != 2 || // 2 = integralni ne racunamo
			($imaintegralni == 1 && $broj_ispita < 2)) // osim ako je to jedini ispit
			$mogucih_bodova += $kmb;
	// Ostale komponente cemo sabrati nesto kasnije...
	
	// Za slucaj da prof odrzi integralni bez parcijalnih
	if ($imaintegralni==1 && $broj_ispita < 2) {
		// $razvdoji_ispite=1; goto // Zaglavlje tabele ispita
		// no php ne podržava goto :(
		$broj_ispita=2;
		// Ovo ce i dalje biti deformisano, ali nesto manje deformisano nego ranije
	}
	
	
	
	// SPISAK KOMPONENTI KOJE NISU ISPITI
	
	$ostale_komponente = array();
	
	// 1 = parcijalni ispit, 2 = integralni ispit
	$q40 = myquery("select k.id, k.kratki_gui_naziv, k.tipkomponente, k.maxbodova from komponenta as k, akademska_godina_predmet as agp, tippredmeta_komponenta as tpk where agp.predmet=$predmet and agp.tippredmeta=tpk.tippredmeta and tpk.komponenta=k.id and k.tipkomponente!=1 and k.tipkomponente!=2 and agp.akademska_godina=$ag");
	while ($r40 = mysql_fetch_row($q40)) {
		$mogucih_bodova += $r40[3];
	
		$ostale_komponente[$r40[0]]=$r40[1];
	}
	
	
	// GLAVNA PETLJA ZA GRUPE
	
	foreach ($spisak_grupa as $grupa_id => $grupa_naziv) {

	
		$zaglavlje1=$zaglavlje2=""; // Dva reda zaglavlja tabele
	
	
		// Ostale komponente
		foreach ($ostale_komponente as $kid => $knaziv)
			$zaglavlje1 .= "<td rowspan=\"2\" align=\"center\">$knaziv</td>\n";
	
	
	
		?>
	<table border="1" cellspacing="0" cellpadding="2">
		<tr><td rowspan="2" align="center">R.br.</td>
			<td rowspan="2" align="center">Prezime i ime</td>
			<td rowspan="2" align="center">Br. indexa</td>
			<?=$zaglavlje1?>
			<td align="center" <? if ($broj_ispita==0) { ?> rowspan="2" <? } else { ?> colspan="<?=$broj_ispita?>" <? } ?>>Ispiti</td>
			<td rowspan="2" align="center"><b>UKUPNO</b></td>
			<td rowspan="2" align="center">Konačna<br/>ocjena</td>
		</tr>
		<tr>
			<?=$zaglavlje2?>
			<?=$ispit_zaglavlje?>
		</tr>
		<?
	
	
	
	
		// ------ SPISAK STUDENATA ------
	
		$idovi = array();
		if ($grupa_id==0) {
			$idovi = array_keys($imeprezime);
		} else {
			$q190 = myquery("select student from student_labgrupa where labgrupa=$grupa_id");
			while ($r190 = mysql_fetch_row($q190)) $idovi[] = $r190[0];
		}
	
	
		// Petlja za ispis studenata
		$redni_broj=0;
		foreach ($imeprezime as $stud_id => $stud_imepr) {
			if (!in_array($stud_id, $idovi)) continue;
			unset ($imeprezime[$stud_id]); // Vise se nece javljati
	
			$redni_broj++;
			?>
		<tr>
			<td><?=$redni_broj?>.</td>
			<td><?=$stud_imepr?></td>
			<td><?=$brindexa[$stud_id]?></td>
			<?
	
			$ispis="";
			$bodova=0; // Zbir bodova koje je student ostvario
	
			// OSTALE KOMPONENTE
	
			foreach ($ostale_komponente as $kid => $knaziv) {
				$q230 = myquery("select kb.bodovi from komponentebodovi as kb, ponudakursa as pk where kb.student=$stud_id and kb.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag and kb.komponenta=$kid");
				$obodova=0; 
				if (mysql_num_rows($q230)>0) {
					$obodova = mysql_result($q230,0,0);
				}
				$ispis .= "<td>$obodova</td>";
				$bodova += $obodova;
			}
	
	
	
			// ISPITI
	
			if ($broj_ispita==0) {
				$ispis .= "<td>&nbsp;</td>";
			}
			$komponente=$kmax=$kispis=array();
			foreach ($ispit_id_array as $ispit) {
				$k = $ispit_komponenta[$ispit];
		
				$q230 = myquery("select ocjena from ispitocjene where ispit=$ispit and student=$stud_id");
				if (mysql_num_rows($q230)>0) {
					$ocjena = mysql_result($q230,0,0);
					if ($razdvoji_ispite==1) $ispis .= "<td align=\"center\">$ocjena</td>\n";
					if (!in_array($k,$komponente) || $ocjena>$kmax[$k]) {
						$kmax[$k]=$ocjena;
						$kispis[$k] = "<td align=\"center\">$ocjena</td>\n";
					}
				} else {
					if ($razdvoji_ispite==1) $ispis .= "<td align=\"center\">/</td>\n";
					if ($kispis[$k] == "") $kispis[$k] = "<td align=\"center\">/</td>\n";
				}
				if (!in_array($k,$komponente)) $komponente[]=$k;
			}
		
			// Prvo trazimo integralne ispite
			foreach ($komponente as $k) {
				if ($komponenta_tip[$k] == 2) {
					// Koje parcijalne ispite obuhvata integralni
					$dijelovi = explode("+", $komponenta_opcija[$k]);
		
					// Racunamo zbir
					$zbir=0;
					$pao=0;
					foreach ($dijelovi as $dio) {
						$zbir += $kmax[$dio];
						if ($kmax[$dio]<$komponenta_prolaz[$dio]) $pao=1;
					}
		
					// Eliminisemo parcijalne obuhvacene integralnim
					if ($kmax[$k]>$zbir || $pao==1 && $kmax[$k]>=$komponenta_prolaz[$k]) {
						$bodova += $kmax[$k];
						foreach ($dijelovi as $dio) {
							$kmax[$dio]=0;
							$kispis[$dio]="";
						}
						$kispis[$k] = "<td align=\"center\" colspan=\"".count($dijelovi)."\">".$kmax[$k]."</td>\n";
					}
					else $kispis[$k]="";
				}
			}
		
			// Sabiremo preostale parcijalne ispite na sumu bodova
			foreach ($komponente as $k) {
				if ($komponenta_tip[$k] != 2) {
					$bodova += $kmax[$k];
				}
				if ($razdvoji_ispite!=1) $ispis .= $kispis[$k];
			}
	
	
			// STATISTIKE
			$topscore[$stud_id]=$bodova;
	
			print $ispis;
	
			print "<td align=\"center\">$bodova (".procenat($bodova,$mogucih_bodova).")</td>\n";
	
	
			// Konacna ocjena
			$q508 = myquery("select ocjena from konacna_ocjena where student=$stud_id and predmet=$predmet and akademska_godina=$ag");
			if (mysql_num_rows($q508)>0) {
				print "<td>".mysql_result($q508,0,0)."</td>\n";
			} else {
				print "<td>/</td>\n";
			}
	
			print "</tr>\n";
		}
		print "</table><p>&nbsp;</p>";
	
	} // while ($r40...
}
?>
<?

// Po broju bodova

if ($maxbodova==20) { $rezolucija="0.5"; } else { $rezolucija="1"; }
print "<p>Distribucija po broju bodova:<br/>(Svaki stupac predstavlja broj studenata sa odre�enim brojem bodova. Rezolucija je $rezolucija bodova)</p>";

// Odredjivanje max. broja studenata po koloni radi skaliranja grafa
$max = 0;
for ($i=0; $i<=$maxbodova; $i+=$rezolucija) {
	$q300 = myquery("select COUNT( * ) FROM ispitocjene WHERE ispit=$ispit and ocjena>=$i and ocjena<".($i+$rezolucija));
	$studenata = mysql_result($q300,0,0);
	if ($studenata>$max) $max=$studenata;
}
$koef = 80/$max;

?><table border="0" cellspacing="0" cellpadding="0"><tr><?
for ($i=0; $i<=$maxbodova; $i+=$rezolucija) {
	$q310 = myquery("select COUNT( * ) FROM ispitocjene WHERE ispit=$ispit and ocjena>=$i and ocjena<".($i+$rezolucija));
	$height = intval(mysql_result($q310,0,0) * $koef);
	?><td width="10">
		<table width="10" border="0" cellspacing="0" cellpadding="0">
			<tr><td>
				<img src="images/fnord.gif" width="1" height="<?=(100-$height)?>">
			</td></tr><tr><td bgcolor="#FF0000">
				<img src="images/fnord.gif" width="1" height="<?=$height?>">
			</td></tr>
		</table>
	</td><td>&nbsp;</td><?
}
?>
</tr></table>
<?


// Prolaznost po grupama

$q315 = myquery("select count(*) from labgrupa where predmet=$predmet and akademska_godina=$ag and virtualna=0");
if (mysql_result($q315,0,0)<2) {
	// Nema grupa, preskacemo ostatak izvjestaja
	return;
}

$ukupno = array(); $polozilo = array(); $prosjek = array(); $grupe = array();
$maxprol = 0; $maxprosj = 0;

$q320 = myquery("select l.id,io.ocjena,l.naziv FROM ispitocjene as io, student_labgrupa as sl, labgrupa as l, ispit as i WHERE io.ispit=$ispit and io.student=sl.student and sl.labgrupa=l.id and i.id=io.ispit and l.predmet=i.predmet and l.akademska_godina=i.akademska_godina and l.virtualna=0 order by l.id");
while ($r320 = mysql_fetch_row($q320)) {
	$grupe[$r320[0]] = $r320[2]; // Nazivi grupa

	$ukupno[$r320[0]]++;
	if ($r320[1]>=$prolaz) $polozilo[$r320[0]]++;

	$prosjek[$r320[0]] = ($prosjek[$r320[0]]*($ukupno[$r320[0]]-1) + $r320[1]) / $ukupno[$r320[0]];
	if ($prosjek[$r320[0]]>$maxprosj) $maxprosj=$prosjek[$r320[0]];

	$prolaznost = $polozilo[$r320[0]]/$ukupno[$r320[0]];
	if ($prolaznost>$maxprol) $maxprol=$prolaznost;
}

print "<p>Prolaznost po grupama:</p>";
$koef = 80/$maxprol;
?><table border="0" cellspacing="0" cellpadding="0"><tr><?
foreach ($grupe as $id => $naziv) {
	$height = intval($polozilo[$id]/$ukupno[$id] * $koef);
	$label = intval($polozilo[$id]/$ukupno[$id] * 100) . "%";
	?><td width="50" valign="top">
		<table width="50" border="0" cellspacing="0" cellpadding="0">
			<tr><td align="center"><?=$label?></td></tr>
			<tr><td>
				<img src="images/fnord.gif" width="1" height="<?=(100-$height)?>">
			</td></tr><tr><td bgcolor="#FF0000">
				<img src="images/fnord.gif" width="1" height="<?=$height?>">
			</td></tr>
			<tr><td align="center"><?=$naziv?></td></tr>
		</table>
	</td><td width="10">&nbsp;</td><?
}
print "</tr></table>\n";


// Broj bodova po grupama

print "<p>Prosje�an broj bodova po grupama:</p>";
$koef = 80/$maxprosj;
?><table border="0" cellspacing="0" cellpadding="0"><tr><?
foreach ($grupe as $id => $naziv) {
	$height = intval($prosjek[$id] * $koef);
	$label = intval($prosjek[$id]*10) / 10;
	?><td width="50" valign="top">
		<table width="50" border="0" cellspacing="0" cellpadding="0">
			<tr><td align="center"><?=$label?></td></tr>
			<tr><td>
				<img src="images/fnord.gif" width="1" height="<?=(100-$height)?>">
			</td></tr><tr><td bgcolor="#FF0000">
				<img src="images/fnord.gif" width="1" height="<?=$height?>">
			</td></tr>
			<tr><td align="center"><?=$naziv?></td></tr>
		</table>
	</td><td width="10">&nbsp;</td><?
}
print "</tr></table>\n";

}

?>

