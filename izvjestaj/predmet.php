<?

// IZVJESTAJ/PREDMET - statistika predmeta, pregled prisustva itd.

// v3.9.1.0 (2008/02/11) + Izvjestaj izdvojen iz bivseg admin_izvjestaj.php
// v3.9.1.1 (2008/08/28) + Tabela osoba umjesto auth
// v3.9.1.2 (2009/02/02) + Dodana podrska za studente koji nisu niti u jednoj grupi; ovo sada ukljucuje i jedan strahovito spor upit sa podupitom :(
// v3.9.1.3 (2009/02/07) + Ubrzano generisanje izvjestaja (ukinut ranije spomenuti podupit)
// v4.0.0.0 (2009/02/19) + Release
// v4.0.9.1 (2009/03/24) + Prebacena polja ects i tippredmeta iz tabele ponudakursa u tabelu predmet
// v4.0.9.2 (2009/03/31) + Tabela ispit preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.3 (2009/03/31) + Tabela konacna_ocjena preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.4 (2009/04/01) + Tabela zadaca preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.5 (2009/04/16) + Popravljen logging
// v4.0.9.6 (2009/04/29) + Prebacujem tabelu labgrupa i parametre izvjestaja sa ponudekursa na predmet i ag
// v4.0.9.7 (2009/05/02) + Ciscenje koda i optimizacija: izbaceni neki viska dijelovi, neke stvari izvucene iz petlje, upiti koji nisu potrebni za skraceni ispis stavljeni pod uslove; sada se skraceni izvjestaj prikazuje prakticno trenutno
// v4.0.9.8 (2009/05/05) + Ne prikazuj virtualne grupe (postoji zaseban upit za sve studente)
// v4.0.9.9 (2009/05/20) + Polja predmet i akademska_godina izbacena iz tabele cas


function izvjestaj_predmet() {

global $userid,$user_nastavnik,$user_studentska,$user_siteadmin;



// Parametri upita

$predmet = intval($_REQUEST['predmet']);
$ag = intval($_REQUEST['ag']);

// sumiraj kolone za zadace i prisustvo
if ($_REQUEST['skrati']=="da") $skrati=1; else $skrati=0; 
// ako ova opcija nije "da", prikazuje se samo zadnji rezultat na svakom parcijalnom, ili samo integralni ispit (ako je bolji)
if ($_REQUEST['razdvoji_ispite']=="da") $razdvoji_ispite=1; else $razdvoji_ispite=0; 
// nemoj razdvajati studente po grupama (neki su trazili ovu opciju)
if ($_REQUEST['sastavi_grupe']=="da") $sastavi_grupe=1; else $sastavi_grupe=0; 
// tabela za samo jednu grupu
$grupa = intval($_REQUEST['grupa']); 



// Naziv predmeta - ovo ujedno provjerava da li predmet postoji

$q10 = myquery("select naziv from predmet where id=$predmet");
if (mysql_num_rows($q10)<1) {
	zamgerlog("nepoznat predmet $predmet",3); // nivo 3: greska
	biguglyerror("Traženi predmet ne postoji");
	return;
}
$q15 = myquery("select naziv from akademska_godina where id=$ag");
if (mysql_num_rows($q15)<1) {
	zamgerlog("nepoznata akademska godina $ag",3); // nivo 3: greska
	biguglyerror("Tražena godina ne postoji");
	return;
}


?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>

<h1><?=mysql_result($q10,0,0)?></h1>
<h3>Akademska <?=mysql_result($q15,0,0)?> godina - Izvještaj o predmetu</h3>
<?


// Koristimo ulogu iz /index.php da odredimo da li će se prikazati imena...
$imenaopt=1;
if (!$user_nastavnik && !$user_studentska && !$user_siteadmin) {
	$imenaopt=0;
	print "<p><b>Napomena:</b> Radi zaštite privatnosti studenata, imena će biti prikazana samo ako ste prijavljeni kao nastavnik/saradnik.</p>\n";
}



// SPISAK SVIH STUDENATA NA PREDMETU

// Razlog za generisanje ovog spiska je sporost podupita koji vraca studente
// koji nisu ni u jednoj grupi
// Umjesto toga cemo napraviti spisak studenata na predmetu, a zatim izbacivati
// iz njega elemente po grupama, tako da ce na kraju ostati samo oni koji nisu
// u grupi
$imeprezime = $brindexa = array();

$q10 = myquery("select o.id, o.prezime, o.ime, o.brindexa from osoba as o, student_predmet as sp, ponudakursa as pk where sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag and sp.student=o.id");

while ($r10 = mysql_fetch_row($q10)) {
	$imeprezime[$r10[0]] = "$r10[1] $r10[2]";
	$brindexa[$r10[0]] = "$r10[3]";
}
uasort($imeprezime,"bssort"); // bssort - bosanski jezik



// SPISAK GRUPA

$spisak_grupa = array();

if ($sastavi_grupe==0) {
	if ($grupa>0) {
		// Samo odabrana grupa
		$q20 = myquery("select id,naziv from labgrupa where predmet=$predmet and akademska_godina=$ag and id=$grupa");
		$spisak_grupa[mysql_result($q40,0,0)] = mysql_result($q40,0,1);
	} else {
		// Spisak grupa moramo sortirati
		$q20 = myquery("select id,naziv from labgrupa where predmet=$predmet and akademska_godina=$ag and virtualna=0");
		while ($r20 = mysql_fetch_row($q20))
			$spisak_grupa[$r20[0]]=$r20[1];
		natsort($spisak_grupa); // "natural sort" - npr. "Grupa 10" dodje iza "Grupa 9"
	}
}

// ID grupe "[Svi studenti]" trebamo saznati iz baze
$q25 = myquery("select id from labgrupa where predmet=$predmet and akademska_godina=$ag and virtualna=1");
$id_virtualne_grupe = mysql_result($q25,0,0);

$spisak_grupa[0] = "[Bez grupe]"; // Dodajemo "nultu grupu" kojoj svi pripadaju



// SPISAK ISPITA
// Ujedno generisemo dio zaglavlja tabele koji se tice ispita

$broj_ispita=0;
$ispit_zaglavlje="";
$oldkomponenta=0;
if ($razdvoji_ispite==1) 
	$orderby="i.datum,i.komponenta"; // Prikazujemo ispite hronoloski
else
	$orderby="i.komponenta,i.datum"; // Prikazujemo I parc, pa II parc, pa Integralni, pa Usmeni (jer tim redom idu IDovi komponenti)


$q30 = myquery("select i.id, UNIX_TIMESTAMP(i.datum), k.id, k.kratki_gui_naziv, k.tipkomponente, k.maxbodova, k.prolaz, k.opcija from ispit as i, komponenta as k where i.predmet=$predmet and i.akademska_godina=$ag and i.komponenta=k.id order by $orderby");
$imaintegralni=0;
while ($r30 = mysql_fetch_row($q30)) {
	$komponenta = $r30[2];
	$imeispita = $r30[3];
	$tipkomponente = $r30[4];
	if ($razdvoji_ispite==1) {
		$ispit_zaglavlje .= "<td align=\"center\">$imeispita<br/> ".date("d.m.",$r30[1])."</td>\n";
		$broj_ispita++;
	} else if ($komponenta != $oldkomponenta && $tipkomponente != 2) { // 2 = integralni
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

	// Ako ispis nije skraceni, u ovu kategoriju stavljamo samo fiksne komponente
	if ($skrati!=1 && $r40[2]!=5) continue; // 5 = fiksna komponenta

	$ostale_komponente[$r40[0]]=$r40[1];
}




// SPISAK ZADACA
// Generise se dio zaglavlja za zadace i jos neki korisni podaci

$zadaca_zaglavlje1=$zadaca_zaglavlje2="";

if ($skrati!=1) {
	$komponente_zadace = $zadace_maxbodova = array();
	$zad_id_array = $zad_brz_array = $zad_mogucih = array();

	$q115 = myquery("SELECT k.id, k.gui_naziv, k.maxbodova FROM tippredmeta_komponenta as tpk, komponenta as k, akademska_godina_predmet as p
	WHERE p.predmet=$predmet and p.tippredmeta=tpk.tippredmeta and tpk.komponenta=k.id and k.tipkomponente=4 and p.akademska_godina=$ag ORDER BY k.id");
	while ($r115 = mysql_fetch_row($q115)) {
		$komponente_zadace[] = $r115[0];
		$zadace_maxbodova[$r115[0]] = $r115[2];

		$brzadaca = 0;
		$zadace_zaglavlje = "";

		// U koju "komponentu zadaća" spadaju zadaće, nije nam toliko bitno
		$q120 = myquery("select id,naziv,zadataka,bodova from zadaca where predmet=$predmet and akademska_godina=$ag order by id");
		while ($r120 = mysql_fetch_row($q120)) {
			$zadace_zaglavlje .= "<td width=\"60\">$r120[1]</td>\n";
			$zad_id_array[] = $r120[0];
			$zad_brz_array[$r120[0]] = $r120[2];
			$zad_mogucih[$r120[0]] = $r120[3];
			$brzadaca++;
			$minw += 60;
		}
		
		if ($brzadaca>0) {
			$zadaca_zaglavlje1 .= "<td align=\"center\" colspan=\"$brzadaca\">$r115[1]</td>\n";
			$zadaca_zaglavlje2 .= $zadace_zaglavlje;
		} else {
			$zadaca_zaglavlje1 .= "<td align=\"center\" rowspan=\"2\">$r115[1]</td>\n";
		}
	}
}



// CACHE REZULTATA ZADAĆA

// Plan je sljedeći:
// Učitamo sve podatke iz tabele u nizove i onda ih samo prikažemo
// Trebalo bi biti brže od komplikovanih ifova i for petlji 
// kao i od subqueries koji su očajno spori

if ($skrati!=1) { // Ako je skracen ispis, samo cemo koristiti komponentu
	$zadace = array();
	if ($grupa>0)
		$q50 = myquery("SELECT z.zadaca,z.redni_broj,z.student,z.status,z.bodova
		FROM zadatak as z,student_labgrupa as sl 
		WHERE z.student=sl.student and sl.labgrupa=$grupa
		ORDER BY id"); // Ovo je sumnjivo - vraca zadace koje je student poslao na drugim predmetima?
	else
		$q50 = myquery("SELECT z.zadaca,z.redni_broj,z.student,z.status,z.bodova
		FROM zadatak as z,student_predmet as sp, ponudakursa as pk
		WHERE z.student=sp.student and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag
		ORDER BY z.id");
	
	while ($r50 = mysql_fetch_row($q50)) {
		// Ne brojimo zadatke sa statusima 1 ("Ceka na pregled") i 
		// 4 ("Potrebno pregledati")
		if ($r50[3]!=1 && $r50[3]!=4) 
			$bodova=$r50[4]+1;
		else $bodova=-1;
	
		// Dodajemo 1 na status kako bismo kasnije mogli znati da li 
		// je vrijednost niza definisana ili ne.
		// undef ne radi :(
	
		// Slog sa najnovijim IDom se smatra mjerodavnim
		// Ostali su u bazi radi historije
		$zadace[$r50[0]][$r50[1]][$r50[2]]=$bodova;
	}
}




// -------------------------------

// GLAVNA PETLJA ZA GRUPE

foreach ($spisak_grupa as $grupa_id => $grupa_naziv) {
/*	if ($j<$br_grupa) {
		$r40 = mysql_fetch_row($q40);
		$grupa_id = $r40[0];
		$grupa_naziv = $r40[1];
	} else {
		$grupa_id = 0;
		$grupa_naziv = "[Bez grupe]";
	}*/

	// Ako je nulta grupa prazna (svi studenti rasporedjeni u grupe), preskacemo je
	if ($grupa_id==0 && count($imeprezime)==0) continue;


	// ----- GENERISANJE ZAGLAVLJA -----

	$zaglavlje1=$zaglavlje2=""; // Dva reda zaglavlja tabele



	// ZAGLAVLJE ZA PUNI ISPIS KOMPONENTI

	if ($skrati!=1) {

		// Ovdje dodati zaglavlje za eventualno nove komponente ...

	// Zaglavlje za prisustvo i spisak casova u ovoj grupi

	$prisustvo_id_array = array();
	$prisustvo_casovi = array();
	$prisustvo_mogucih = array();

	$q105 = myquery("SELECT k.id, k.gui_naziv, k.maxbodova FROM tippredmeta_komponenta as tpk, komponenta as k, akademska_godina_predmet as p WHERE p.predmet=$predmet and p.tippredmeta=tpk.tippredmeta and tpk.komponenta=k.id and k.tipkomponente=3 and p.akademska_godina=$ag ORDER BY k.id");
	while ($r105 = mysql_fetch_row($q105)) {
		$prisustvo_id_array[] = $r105[0];
		$prisustvo_mogucih[$r105[0]] =  $r105[2];

		if ($skrati != 1) {
			$cas_id_array = array();
			$casova = 0;
			$prisustvo_zaglavlje = "";

			if ($grupa_id!=0) 
				$q110 = myquery("SELECT id,datum,vrijeme FROM cas where labgrupa=$grupa_id and komponenta=$r105[0] ORDER BY datum");
			else if ($id_virtualne_grupe>0)
				$q110 = myquery("SELECT id,datum,vrijeme FROM cas where labgrupa=$id_virtualne_grupe and komponenta=$r105[0] ORDER BY datum");
			else continue; // ako nema virtualne grupe - preskacemo

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

	$zaglavlje1 .= $zadaca_zaglavlje1;
	$zaglavlje2 .= $zadaca_zaglavlje2;

	} // if ($skrati != 1)


	// Ostale komponente
	foreach ($ostale_komponente as $kid => $knaziv)
		$zaglavlje1 .= "<td rowspan=\"2\" align=\"center\">$knaziv</td>\n";



	?>
<center><h2><?=$grupa_naziv?></h2></center>
<table border="1" cellspacing="0" cellpadding="2">
	<tr><td rowspan="2" align="center">R.br.</td>
		<? if ($imenaopt) { ?><td rowspan="2" align="center">Prezime i ime</td><? } ?>
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
		<? if ($imenaopt) { ?><td><?=$stud_imepr?></td><? } ?>
		<td><?=$brindexa[$stud_id]?></td>
		<?

		$ispis="";
		$bodova=0; // Zbir bodova koje je student ostvario


		// PUNI ISPIS MODULA PRISUSTVO

		if ($skrati!=1) {
			foreach($prisustvo_id_array as $pid) {
	
				$cas_id_array = $prisustvo_casovi[$pid];

				if (count($cas_id_array)==0) $ispis .= "<td>&nbsp;</td>\n";
				$odsustvo=0;
				foreach ($cas_id_array as $cid) {
					$q200 = mysql_query("select prisutan,plus_minus from prisustvo where student=$stud_id and cas=$cid");
					if (mysql_num_rows($q200)>0) {
						if (mysql_result($q200,0,0) == 2) { 
							$ispis .= "<td bgcolor=\"#FFE303\" align=\"center\">NP</td>\n";
						} else if(mysql_result($q200,0,0) == 0){ 
							$ispis .= "<td bgcolor=\"#FFCCCC\" align=\"center\">NE</td>\n";
							$odsustvo++;
						} else if(mysql_result($q200,0,0) == 1){
							$ispis .= "<td bgcolor=\"#CCFFCC\" align=\"center\">DA</td>\n";
						}
						//$ocj = mysql_result($r4,0,1);
					} else {
						$ispis .= "<td bgcolor=\"#FFFFCC\"> / </td>\n";
					}
				}

				$q210 = myquery("select kb.bodovi from komponentebodovi as kb, ponudakursa as pk where kb.student=$stud_id and kb.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag and kb.komponenta=$pid");
				if (mysql_num_rows($q210)==0) 
					$pbodovi=0;
				else
					$pbodovi=mysql_result($q210,0,0);
				$ispis .= "<td>$pbodovi</td>\n";
				$bodova += $pbodovi;
			}
		}


		// PUNI ISPIS MODULA ZADACE

		if ($skrati != 1) {
			$zbodova = 0;
			foreach ($zad_id_array as $n => $vid) {
//print "VID: $vid ".$zad_brz_array[$vid]."</br>";
				$ocjena=0;
				$ima=0; // Da li je poslao ijedan zadatak?
				$ispisati=1; // Da li ima nepregledanih zadataka?
				for ($i=1; $i<=$zad_brz_array[$vid]; $i++) {
//print "OUT: $vid $i $stud_id ".$zadace[$vid][$i][$stud_id]."<br/>";
					$bzad = $zadace[$vid][$i][$stud_id];
					if ($bzad > 0) {
						// Svi bodovi su uvećani za 1
						$ocjena+=($bzad-1);
						$ima=1;
					} 
					// Ispisujemo samo ako su svi zadaci pregledani
					if ($bzad == -1) $ispisati=0;
				}
	
				if ($ima == 0 || $ispisati==0) {
					$ispis .= "<td> / </td>\n";
				} else {
					$ispis .= "<td> $ocjena </td>\n";
					$zbodova = $zbodova + $ocjena;
				}
			}
			if (count($zad_id_array)==0) $ispis .= "<td>&nbsp;</td>";

			foreach($komponente_zadace as $kz) {
				$q220 = myquery("select kb.bodovi from komponentebodovi as kb, ponudakursa as pk where kb.student=$stud_id and kb.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag and kb.komponenta=$kz");
				$zbodova=0;
				while ($r220 = mysql_fetch_row($q220)) {
					$zbodova += $r220[0];
				}
				$bodova += $zbodova;
			}
		}


		// Ovdje dodati puni ispis neke eventualno nove komponente


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

} // function izvjestaj_predmet()

?>
