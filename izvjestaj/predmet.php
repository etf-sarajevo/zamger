<?

// IZVJESTAJ/PREDMET - statistika predmeta, pregled prisustva itd.

// v3.9.1.0 (2008/02/11) + Izvjestaj izdvojen iz bivseg admin_izvjestaj.php



function izvjestaj_predmet() {

global $userid,$user_nastavnik,$user_studentska,$user_siteadmin;



?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>
<?


// Parametri upita

$predmet_id = intval($_REQUEST['predmet']);

if ($_REQUEST['skrati']=="da") $skrati=1; else $skrati=0;
if ($_REQUEST['razdvoji_ispite']=="da") $razdvoji_ispite=1; else $razdvoji_ispite=0;

$grupa = intval($_REQUEST['grupa']);



// Naslov

$q10 = myquery("select p.naziv,ag.naziv from predmet as p, ponudakursa as pk, akademska_godina as ag where pk.id=$predmet_id and ag.id=pk.akademska_godina and pk.predmet=p.id");
if (mysql_num_rows($q10)<1) {
	biguglyerror("Nepoznat predmet");
	exit;
}

print "<p>&nbsp;</p><p>Predmet: <b>".mysql_result($q10,0,0)."</b><br/>Akademska godina: <b>".mysql_result($q10,0,1)."</b></p>\n<h1>Izvještaj o predmetu</h1>\n";


// Koristimo ulogu iz /index.php da odredimo da li će se prikazati imena...
$imenaopt=1;
if (!$user_nastavnik && !$user_studentska && !$user_siteadmin) {
	$imenaopt=0;
	print "<p><b>Napomena:</b> Radi zaštite privatnosti studenata, imena će biti prikazana samo ako ste prijavljeni kao nastavnik/saradnik.</p>\n";
}



// Zaglavlje tabele ispita

$broj_ispita=0;
$ispit_zaglavlje="";
$oldkomponenta=0;
if ($razdvoji_ispite==1) 
	$orderby="i.datum,i.komponenta";
else
	$orderby="i.komponenta,i.datum";


$q20 = myquery("select i.id, UNIX_TIMESTAMP(i.datum), k.id, k.kratki_gui_naziv, k.tipkomponente, k.maxbodova, k.prolaz, k.opcija from ispit as i, komponenta as k where i.predmet=$predmet_id and i.komponenta=k.id order by $orderby");
while ($r20 = mysql_fetch_row($q20)) {
	if ($razdvoji_ispite==1) {
		if ($r20[4]==5)
			$ispit_zaglavlje .= "<td align=\"center\">$r20[3]</td>\n";
		else
			$ispit_zaglavlje .= "<td align=\"center\">$r20[3]<br/> ".date("d.m.",$r20[1])."</td>\n";
		$broj_ispita++;
	} else if ($r20[2] != $oldkomponenta && $r20[4] != 2) { // 2 = integralni
		$oldkomponenta=$r20[2];
		$ispit_zaglavlje .= "<td align=\"center\">$r20[3]</td>\n";
		$broj_ispita++;
	}

	$ispit_id_array[] = $r20[0];
	$ispit_komponenta[$r20[0]] = $r20[2];

	// Pripremamo podatke o komponentama
	$komponenta_tip[$r20[2]] = $r20[4];
	$komponenta_maxb[$r20[2]] = $r20[5];
	$komponenta_prolaz[$r20[2]] = $r20[6];
	$komponenta_opcija[$r20[2]] = "$r20[7]";

}


// Upit za grupe

if ($grupa>0)
	$q40 = myquery("select id,naziv from labgrupa where predmet=$predmet_id and id=$grupa");
else
	$q40 = myquery("select id,naziv from labgrupa where predmet=$predmet_id order by id");


while ($r40 = mysql_fetch_row($q40)) {
	$grupa_id = $r40[0];
	$grupa_naziv = $r40[1];

	// Plan je sljedeći:
	// Učitamo sve podatke iz tabele u nizove i onda ih samo prikažemo
	// Trebalo bi biti brže od komplikovanih ifova i for petlji a opet raditi
	// sa starim mysql-om :(

	$zaglavlje1=$zaglavlje2="";

	// CACHE REZULTATA ZADAĆA
	$zadace = array();
	$q100 = myquery("SELECT z.zadaca,z.redni_broj,z.student,z.status,z.bodova
	FROM zadatak as z,student_labgrupa as sl 
	WHERE z.student=sl.student and sl.labgrupa=$grupa_id
	ORDER BY id");
	while ($r100 = mysql_fetch_row($q100)) {
		// Ne brojimo zadatke sa statusima 1 ("Ceka na pregled") i 
		// 4 ("Potrebno pregledati")
		if ($r100[3]!=1 && $r100[3]!=4) 
			$bodova=$r100[4]+1;
		else $bodova=-1;

		// Dodajemo 1 na status kako bismo kasnije mogli znati da li 
		// je vrijednost niza definisana ili ne.
		// undef ne radi :(

		// Slog sa najnovijim IDom se smatra mjerodavnim
		// Ostali su u bazi radi historije
		$zadace[$r100[0]][$r100[1]][$r100[2]]=$bodova;
	}


	// ZAGLAVLJE - PRISUSTVO
	$prisustvo_id_array = array();
	$prisustvo_casovi = array();
	$prisustvo_mogucih = array();

	$q105 = myquery("SELECT k.id, k.gui_naziv, k.maxbodova FROM ponudakursa as pk, tippredmeta_komponenta as tpk, komponenta as k WHERE pk.id=$predmet_id and pk.tippredmeta=tpk.tippredmeta and tpk.komponenta=k.id and k.tipkomponente=3 ORDER BY k.id");
	while ($r105 = mysql_fetch_row($q105)) {
		$prisustvo_id_array[] = $r105[0];
		$prisustvo_mogucih[$r105[0]] =  $r105[2];

		if ($skrati != 1) {
			$cas_id_array = array();
			$casova = 0;
			$prisustvo_zaglavlje = "";
		
			$q110 = myquery("SELECT id,datum,vrijeme FROM cas where labgrupa=$grupa_id and predmet=$predmet_id and komponenta=$r105[0] ORDER BY datum");
			while ($r110 = mysql_fetch_row($q110)) {
				$cas_id = $r110[0];
				list ($cas_godina,$cas_mjesec,$cas_dan) = explode("-",$r110[1]);
				list ($cas_sat,$cas_minuta,$cas_sekunda) = explode(":",$r110[2]);
				$prisustvo_zaglavlje .= "<td align=\"center\">$cas_dan.$cas_mjesec<br/>$cas_sat:$cas_minuta";
				$prisustvo_zaglavlje .= "</td>\n";
				$cas_id_array[] = $cas_id;
				$casova++;
				$minw += 40;
			}
			$prisustvo_casovi[$r105[0]] = $cas_id_array;
		//	$prisustvo_maxbodova[$r195[0]] = $r195[2];
		//	$prisustvo_maxizostanaka[$r195[0]] = $r195[3];
		//	$prisustvo_minbodova[$r195[0]] = $r195[4];
		
			if ($prisustvo_zaglavlje == "") { 
				$prisustvo_zaglavlje = "<td>&nbsp;</td>"; 
				$minw += 40; 
				$casova=1;
			}
	
			$zaglavlje1 .= "<td align=\"center\" colspan=\"".($casova+1)."\">$r105[1]</td>\n";
			$zaglavlje2 .= $prisustvo_zaglavlje;
			$zaglavlje2 .= "<td>BOD.</td>\n";
		}
	}


	// ZAGLAVLJE - ZADACE
	$komponente_zadace = $zadace_maxbodova = array();
	$zad_id_array = $zad_brz_array = $zad_mogucih = array();
	$zadace = array();

	$q115 = myquery("SELECT k.id, k.gui_naziv, k.maxbodova FROM ponudakursa as pk, tippredmeta_komponenta as tpk, komponenta as k 
	WHERE pk.id=$predmet_id and pk.tippredmeta=tpk.tippredmeta and tpk.komponenta=k.id and k.tipkomponente=4 ORDER BY k.id");
	while ($r115 = mysql_fetch_row($q115)) {
		$komponente_zadace[] = $r115[0];
		$zadace_maxbodova[$r115[0]] = $r115[2];

		if ($skrati!=1) {
			$brzadaca = 0;
			$zadace_zaglavlje = "";
			
			// U koju "komponentu zadaća" spadaju zadaće, nije nam toliko bitno
			$q120 = myquery("select id,naziv,zadataka,bodova from zadaca where predmet=$predmet_id order by id");
			while ($r120 = mysql_fetch_row($q120)) {
				$zadace_zaglavlje .= "<td width=\"60\">$r120[1]</td>\n";
				$zad_id_array[] = $r120[0];
				$zad_brz_array[$r120[0]] = $r120[2];
				$zad_mogucih[$r120[0]] = $r120[3];
				$brzadaca++;
				$minw += 60;

				// Cacheing bodova na zadaćama
				for ($i=1; $i<=$r120[2]; $i++) {
					$q125 = myquery("select student,bodova,status from zadatak where zadaca=$r120[0] and redni_broj=$i order by id");
					while ($r125=mysql_fetch_row($q125)) {
						if ($r125[2]==5 || $r125[2]==3 || $r125[2]==2) {
							// 5=pregledana, 3=bug, 2=prepisana
							$zadace[$r120[0]][$i][$r125[0]]=$r125[1]+1; // Nema drugog nacina da se utvrdi da li zadaca postoji
						} else {
							$zadace[$r120[0]][$i][$r125[0]]=-1;
						}
//print "$r120[0] $i $r125[0] ".$zadace[$r120[0]][$i][$r125[0]]."<br/>";
					}
				}
			}
		
			if ($brzadaca>0) {
				$zaglavlje1 .= "<td align=\"center\" colspan=\"$brzadaca\">$r115[1]</td>\n";
				$zaglavlje2 .= $zadace_zaglavlje;
			} else {
				$zaglavlje1 .= "<td align=\"center\" rowspan=\"2\">$r115[1]</td>\n";
			}
		}
	}



	?>
<center><h2><?=$r40[1]?></h2></center>
<table border="1" cellspacing="0" cellpadding="2">
	<tr><td rowspan="2" align="center">R.br.</td>
		<? if ($imenaopt) { ?><td rowspan="2" align="center">Prezime i ime</td><? } ?>
		<td rowspan="2" align="center">Br. indexa</td>
		<? if ($skrati!=1) { 
			print $zaglavlje1;
		} else { ?>
		<td rowspan="2" align="center">Prisustvo</td>
		<td rowspan="2" align="center">Zadaće</td>
		<? } ?>
		<td align="center" <? if ($broj_ispita==0) { ?> rowspan="2" <? } else { ?> colspan="<?=$broj_ispita?>" <? } ?>>Ispiti</td>
		<td rowspan="2" align="center"><b>UKUPNO</b></td>
		<td rowspan="2" align="center">Konačna<br/>ocjena</td>
	</tr>
	<tr>
		<? if ($skrati!=1) { 
			print $zaglavlje2;
		} ?>
		<?=$ispit_zaglavlje?>
	</tr>
	<?

	// Ucitavamo studente u array radi sortiranja
	$imeprezime=array();
	$brindexa=array();
	$q130 = myquery("select a.id, a.prezime, a.ime, a.brindexa from auth as a, student_labgrupa as sl where sl.labgrupa=$grupa_id and sl.student=a.id");
	while ($r130 = mysql_fetch_row($q130)) {
		$imeprezime[$r130[0]] = "$r130[1] $r130[2]";
		$brindexa[$r130[0]] = $r130[3];
	}
	uasort($imeprezime,"bssort"); // bssort - bosanski jezik

	$redni_broj=0;

	foreach ($imeprezime as $stud_id => $stud_imepr) {
		$redni_broj++;
		?>
	<tr>
		<td><?=$redni_broj?>.</td>
		<? if ($imenaopt) { ?><td><?=$stud_imepr?></td><? } ?>
		<td><?=$brindexa[$stud_id]?></td>
		<?

		$prisustvo_ispis=$zadace_ispis="";
		$bodova=0;
		$mogucih=0;


		// PRISUSTVO

		foreach($prisustvo_id_array as $pid) {
	
		$cas_id_array = $prisustvo_casovi[$pid];

		if ($skrati!=1) {
			if (count($cas_id_array)==0) $prisustvo_ispis = "<td>&nbsp;</td>\n";
			$odsustvo=0;
			foreach ($cas_id_array as $cid) {
				$q200 = mysql_query("select prisutan,plus_minus from prisustvo where student=$stud_id and cas=$cid");
				if (mysql_num_rows($q200)>0) {
					if (mysql_result($q200,0,0) == 1) { 
						$prisustvo_ispis .= "<td bgcolor=\"#CCFFCC\" align=\"center\">DA</td>\n";
					} else { 
						$prisustvo_ispis .= "<td bgcolor=\"#FFCCCC\" align=\"center\">NE</td>\n";
						$odsustvo++;
					}
					//$ocj = mysql_result($r4,0,1);
				} else {
					$prisustvo_ispis .= "<td bgcolor=\"#FFFFCC\"> / </td>\n";
				}
			}
		}

		$q210 = myquery("select bodovi from komponentebodovi where student=$stud_id and predmet=$predmet_id and komponenta=$pid");
		if (mysql_num_rows($q210)==0) 
			$pbodovi=0;
		else
			$pbodovi=mysql_result($q210,0,0);
		$prisustvo_total_ispis = "<td>$pbodovi</td>\n";
		$bodova += $pbodovi;
		$mogucih+=$prisustvo_mogucih[$pid];

		}


		// ZADACE

		if ($skrati != 1) {
			$zbodova = 0;
			foreach ($zad_id_array as $n => $vid) {
//print "VID: $vid ".$zad_brz_array[$vid]."</br>";
				$ocjena=0;
				$ima=0; // Da li je poslao ijedan zadatak?
				$ispis=1; // Da li ima nepregledanih zadataka?
				for ($i=1; $i<=$zad_brz_array[$vid]; $i++) {
//print "OUT: $vid $i $stud_id ".$zadace[$vid][$i][$stud_id]."<br/>";
					$bzad = $zadace[$vid][$i][$stud_id];
					if ($bzad > 0) {
						// Svi bodovi su uvećani za 1
						$ocjena+=($bzad-1);
						$ima=1;
					} 
					// Ispisujemo samo ako su svi zadaci pregledani
					if ($bzad == -1) $ispis=0;
				}
	
				if ($ima == 0 || $ispis==0) {
					$zadace_ispis .= "<td> / </td>\n";
				} else {
					$zadace_ispis .= "<td> $ocjena </td>\n";
					$zbodova = $zbodova + $ocjena;
				}
			}
			if (count($zad_id_array)==0) $zadace_ispis .= "<td>&nbsp;</td>";
		}

		foreach($komponente_zadace as $kz) {
			$q220 = myquery("select bodovi from komponentebodovi where student=$stud_id and predmet=$predmet_id and komponenta=$kz");
			$zbodova=0;
			while ($r220 = mysql_fetch_row($q220)) {
				$zbodova += $r220[0];
			}
			$bodova += $zbodova;
			$zadace_total_ispis = "<td>$zbodova</td>";
			$mogucih += $zadace_maxbodova[$kz];
		}


		// ISPITI

		$razdvojeni_ispis = $spojeni_ispis = "";
		if ($broj_ispita==0) {
			$spojeni_ispis=$razdvojeni_ispis="<td>&nbsp;</td>";
		}
		$komponente=$kmax=$kispis=array();
		foreach ($ispit_id_array as $ispit) {
			$k = $ispit_komponenta[$ispit];
	
			$q230 = myquery("select ocjena from ispitocjene where ispit=$ispit and student=$stud_id");
			if (mysql_num_rows($q230)>0) {
				$ocjena = mysql_result($q230,0,0);
				$tip = mysql_result($q230,0,1);
				$razdvojeni_ispis .= "<td align=\"center\">$ocjena</td>\n";
				if (!in_array($k,$komponente) || $ocjena>$kmax[$k]) {
					$kmax[$k]=$ocjena;
					$kispis[$k] = "<td align=\"center\">$ocjena</td>\n";
				}
			} else {
				$razdvojeni_ispis .= "<td align=\"center\">/</td>\n";
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
				$mogucih += $komponenta_maxb[$k];
			}
			$spojeni_ispis .= $kispis[$k];
		}


		// STATISTIKE

		$imena[$stud_id]=$stud_imepr;
		$topscore[$stud_id]=$bodova;

		if ($skrati != 1)
			print $prisustvo_ispis;
		print $prisustvo_total_ispis;
		if ($skrati!=1) print $zadace_ispis;
		else print $zadace_total_ispis;
		if ($razdvoji_ispite==1) print $razdvojeni_ispis;
		else print $spojeni_ispis;
		print "<td align=\"center\">$bodova (".procenat($bodova,$mogucih).")</td>\n";


		// Konacna ocjena
		$q508 = myquery("select ocjena from konacna_ocjena where student=$stud_id and predmet=$predmet_id");
		if (mysql_num_rows($q508)>0) {
			print "<td>".mysql_result($q508,0,0)."</td>\n";
		} else {
			print "<td>/</td>\n";
		}

		print "</tr>\n";
	}
	print "</table><p>&nbsp;</p>";

} // while ($r40...

} // function izvjestaj_predmet()

?>
