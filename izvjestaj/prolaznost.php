<?

// IZVJESTAJ/PROLAZNOST - Pregled prolaznosti i ocjena po godini, odsjeku...



function izvjestaj_prolaznost() {

require_once("lib/utility.php"); // procenat, bssort


?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>
<p>Datum i vrijeme izvještaja: <?=date("d. m. Y. H:i");?></p>
<?


// parametri izvjestaja
$akgod = intval($_REQUEST['_lv_column_akademska_godina']);
$studij = intval($_REQUEST['_lv_column_studij']);
$period = intval($_REQUEST['period']); // 0 = semestar, 1 = godina
$semestar = intval($_REQUEST['semestar']);
$godina = intval($_REQUEST['godina']);
$ispit = intval($_REQUEST['ispit']); // 1 = prvi parc., 2 = drugi p., 3 = broj bodova, 4 = konacna ocjena, 5 = uslov za usmeni
$cista_gen = intval($_REQUEST['cista_gen']); // 0 = svi, 1 = bez kolizije i prenesenih, 2 = redovni, 3 = cista gen. 
$studenti = intval($_REQUEST['studenti']); // 1 = prikazi pojedinacne studente
$sortiranje = intval($_REQUEST['sortiranje']); // 0 = po prezimenu, 1 = po broju bodova, 2 = po broju indexa
$oboji = $_REQUEST['oboji']; // "tajna" opcija za bojenje studija
$tipstudija = intval($_REQUEST['tipstudija']); // "tajna" opcija koja se koristi u kombinaciji sa $studij = -1 (svi studiji)

if ($tipstudija==0) $tipstudija=2; // FIXME


// Naslov
$q20 = db_query("select naziv from akademska_godina where id=$akgod");

?>
<h2>Prolaznost</h2>
<p>Studij: <b><?

if ($studij==-1)
	$q10 = db_query("select naziv from tipstudija where id=$tipstudija");
else 
	$q10 = db_query("select naziv from studij where id=$studij");

if (db_num_rows($q10)<1) {
	niceerror("Nepoznat studij / tipstudija");
	return;
}

if ($studij==-1) print "Svi studenti (".db_result($q10,0,0).")";
else print db_result($q10,0,0);

?></b><br/>
Akademska godina: <b><?=db_result($q20,0,0)?></b><br/>
Godina/semestar studija: <b><?
if ($period==0) {
	if ($semestar==0) $semestar=1;
	print "$semestar. semestar";
} else {
	if ($godina==0) $godina=1;
	print "$godina. godina, ";
}
?></b><br/>
Obuhvaćeni studenti: <b><?
if ($cista_gen==0) print "Redovni, Ponovci, Preneseni predmeti i kolizija";
elseif ($cista_gen==1) print "Redovni, Ponovci";
elseif ($cista_gen==2) print "Redovni studenti";
elseif ($cista_gen==3) print "Čista generacija";
elseif ($cista_gen==4) print "Ponovci";?></b><br/><br/>
Vrsta izvještaja: <b><?
if ($ispit==1) print "I parcijalni ispit";
elseif ($ispit==2) print "II parcijalni ispit";
elseif ($ispit==3) print "Ukupni bodovi";
elseif ($ispit==4) print "Konačna ocjena";
elseif ($ispit==5) print "Uslovi za usmeni ispit";
?></b><br/>
</p><?


// Razni dodaci na upite ovisno o primljenim parametrima

if ($period==0) { // Semestar ili godina?
	$semestar_upit = "pk.semestar=$semestar";
	$sem_stud_upit = "semestar=$semestar";
} else {
	$semestar_upit = "(pk.semestar=".($godina*2-1)." or pk.semestar=".($godina*2).")";
	$sem_stud_upit = "semestar=".($godina*2-1); // blazi kriterij za studente koji slusaju
}

$studij_upit_pk = "";
$studij_upit_ss = "";
$studij_upit_ss2 = "";
if ($studij>-1) { // Izbor studija
	$studij_upit_pk = "and pk.studij=$studij";
	$studij_upit_ss = "and ss.studij=$studij";
	$studij_upit_ss2 = "and ss2.studij=$studij";
} else {
	$q25 = db_query("select id from studij where tipstudija=$tipstudija");
	while ($r25 = db_fetch_row($q25)) {
		if ($studij_upit_pk=="") {
			$studij_upit_pk = "and (pk.studij=$r25[0]";
			$studij_upit_ss = "and (ss.studij=$r25[0]";
			$studij_upit_ss2 = "and (ss2.studij=$r25[0]";
		} else {
			$studij_upit_pk .= " or pk.studij=$r25[0]";
			$studij_upit_ss .= " or ss.studij=$r25[0]";
			$studij_upit_ss2 .= " or ss2.studij=$r25[0]";
		}
	}
	if (db_num_rows($q25)>0) {
		$studij_upit_pk .= ")";
		$studij_upit_ss .= ")";
		$studij_upit_ss2 .= ")";
	}
}


// ($q30) Spisak predmeta na studij-semestru
if ($studij==-1) 
	$q30 = db_query("select distinct p.id, p.naziv, 1 from predmet as p, ponudakursa as pk where pk.predmet=p.id and pk.akademska_godina=$akgod $studij_upit_pk and $semestar_upit order by pk.obavezan desc, p.naziv");
else
	$q30 = db_query("select p.id, p.naziv, pk.obavezan from predmet as p, ponudakursa as pk where pk.predmet=p.id and pk.akademska_godina=$akgod $studij_upit_pk and $semestar_upit order by pk.obavezan desc, p.naziv");

// Dodatak upitu za studente
$upit_studenti="";
if ($cista_gen>=1) {
	// Student trenutno upisan na dati studij/semestar
	$upit_studenti = "$studij_upit_ss and ss.$sem_stud_upit and ss.akademska_godina=$akgod";
}
if ($cista_gen==2) {
	// Student nije nikada prije slusao dati studij/semestar
	// FIXME: pretpostavka je da IDovi akademskih godina idu redom
	$upit_studenti .= " and (select count(*) from student_studij as ss2 where ss2.student=io.student $studij_upit_ss2 and ss2.$sem_stud_upit and ss2.akademska_godina<$akgod)=0";
}
if ($cista_gen==3) {
	// Student nije nikada ponavljao godinu (nema zapisa o upisu u studij prije datog broja godina)
	// FIXME: pretpostavka je da IDovi akademskih godina idu redom
	$upisao_godine = $akgod;
	if ($period==0) {
		$upisao_godine -= intval(($semestar+1)/2);
	} else {
		$upisao_godine -= $godina;
	}

	$upit_studenti .= " and (select count(*) from student_studij as ss2 where ss2.student=io.student and ss2.akademska_godina<=$upisao_godine)=0";
}
if ($cista_gen==4) {
	// Samo ponovci
	$upit_studenti .= " and (select count(*) from student_studij as ss2 where ss2.student=io.student $studij_upit_ss2 and ss2.$sem_stud_upit and ss2.akademska_godina<$akgod)>0";
}


// PODIZVJESTAJ 1
// 1 = I parc., 2 = II parc., 4 = Konacna ocjena
if ($ispit == 1 || $ispit == 2 || $ispit==3 || $ispit == 4 || $ispit == 5) {
	global $polozio;
	$polozio = array(); // ne znam kako bez global :(
	global $suma_bodova;
	$suma_bodova = array();
	global $brindexa;
	$brindexa = array();

	// Zaglavlja tabela, ovisno o tome da li su navedeni pojedinacni studenti ili ne
	if ($studenti==1) {
		print "<p>Pregled po studentima.";
		if ($sortiranje==1 && $ispit==4) 
			print " Spisak je sortiran po broju položenih predmeta i ocjenama.</p>\n";
		else if ($sortiranje==1) 
			print " Spisak je sortiran po broju položenih ispita i bodovima.</p>\n";
		else print " Spisak je sortiran po prezimenu.</p>\n";

		if ($oboji=="odsjek") {
			?>
			<table width="100%" border="0" cellpadding="4" cellspacing="4"><tr>
				<td align="left">
					<table border="1" bgcolor="#FF9999" width="100"><tr><td>&nbsp;</td></tr></table>
					Računarstvo i informatika
				</td>
				<td align="left">
					<table border="1" bgcolor="#99FF99" width="100"><tr><td>&nbsp;</td></tr></table>
					Automatika i elektronika
				</td>
				<td align="left">
					<table border="1" bgcolor="#9999FF" width="100"><tr><td>&nbsp;</td></tr></table>
					Elektroenergetika
				</td>
				<td align="left">
					<table border="1" bgcolor="#FF99FF" width="100"><tr><td>&nbsp;</td></tr></table>
					Telekomunikacije
				</td>
			</tr></table>
			<?
		}
	}


	if ($studenti==0 && $ispit==4) { // $studenti = prikaz individualnih studenata
		?><table border="1" cellspacing="0" cellpadding="2">
			<tr><th>Predmet</th>
			<th>Upisalo</th>
			<th>Položilo</th>
			<th>%</th>
		</tr><?
	} else if ($studenti==0 && ($ispit==5 || $ispit==3)) {
		?><table border="1" cellspacing="0" cellpadding="2">
			<tr><th>Predmet</th>
			<th>Upisalo</th>
			<th>Ima uslove</th>
			<th>%</th>
		</tr><?
	} else if ($studenti==0) {
		?><table border="1" cellspacing="0" cellpadding="2">
			<tr><th><b>Predmet</b></th>
			<th><b>Izašlo</b></th>
			<th><b>Položilo</b></th>
			<th><b>%</b></th>
		</tr><?
	} else {
		?>
		<table  border="1" cellspacing="0" cellpadding="2">
		<tr>
			<th>R. br.</th>
			<th>Student</th>
			<th>Br. indeksa</th>
		<?
		if ($studij==-1) {
			print "<th>Studij</th>\n";
		}
		while ($r30 = db_fetch_row($q30)) {
			$kursevi[$r30[0]] = $r30[1];
			$naziv = $r30[1];
			if ($r30[2]==0) $naziv .= " *";
			print "<th>$naziv</th>\n";
		}
		print "<th>UKUPNO:</th></tr>\n";
	}


	// ($q40) Upit za spisak studenata

	if ($cista_gen==0) {
		// Redovni studenti + ponovci + preneseni studenti
		// (svi upisani na predmete sa studija/semestra)

		$q40 = db_query("select distinct sp.student from student_predmet as sp, ponudakursa as pk where sp.predmet=pk.id and pk.akademska_godina=$akgod $studij_upit_pk and $semestar_upit");
		$uk_studenata=db_num_rows($q40);

		// Statisticki podaci o generaciji

		// Redovni studenti
		//$q50 = db_query("select count(*) from student_studij as ss where ss.akademska_godina=$akgod $studij_upit_ss and ss.$sem_stud_upit and (select count(*) from student_studij as ss2 where ss2.student=ss.student $studij_upit_ss2 and ss2.$sem_stud_upit and ss2.akademska_godina<$akgod)=0");
		$q50 = db_query("select count(*) from student_studij as ss where ss.akademska_godina=$akgod $studij_upit_ss and ss.$sem_stud_upit and ss.ponovac=0");
		$redovnih = db_result($q50,0,0);

		// Ukupan broj studenata na studiju
		$q60 = db_query("select count(ss.student) from student_studij as ss where ss.akademska_godina=$akgod $studij_upit_ss and ss.$sem_stud_upit");

		// Posto su neki ponovci polozili sve iz ovog semestra, sljedeci upit vraca samo prenesene predmete
		// i kolizije kako bi ukupna statistika bila tacna, cak iako se suma ne poklapa
		if ($period==0) {
			$prenesenoupit = "ss.semestar>$semestar"; // Pretpostavljamo da student ne može biti istovremeno upisan na drugi studij
			$kolizijaupit = "ss.semestar<$semestar";
		} else {
			$prenesenoupit = "ss.semestar>".($godina*2); 
			$kolizijaupit = "ss.semestar<".($godina*2-1); 
		}
		$q65 = db_query("SELECT count(distinct sp.student) FROM student_predmet as sp, ponudakursa as pk, student_studij as ss WHERE sp.predmet=pk.id $studij_upit_pk and $semestar_upit and pk.akademska_godina=$akgod and ss.student=sp.student and $prenesenoupit and ss.akademska_godina=$akgod");
		$q67 = db_query("SELECT count(distinct sp.student) FROM student_predmet as sp, ponudakursa as pk, student_studij as ss WHERE sp.predmet=pk.id $studij_upit_pk and $semestar_upit and pk.akademska_godina=$akgod and ss.student=sp.student and $kolizijaupit and ss.akademska_godina=$akgod");

		$ukupno_na_godini = db_result($q60,0,0);
		$ponovaca = $ukupno_na_godini - $redovnih;
		$prenesenih = db_result($q65,0,0);
		$kolizije = db_result($q67,0,0);

		$ispis_br_studenata = "Ukupno studenata:<br />&nbsp;&nbsp;&nbsp;&nbsp;<b>$redovnih</b> studenata redovno upisalo godinu<br />&nbsp;&nbsp;&nbsp;&nbsp;<b>$ponovaca</b> ponavlja godinu<br />&nbsp;&nbsp;&nbsp;&nbsp;<b>$prenesenih</b> prenijelo predmet na iduću godinu<br />&nbsp;&nbsp;&nbsp;&nbsp;<b>$kolizije</b> sluša predmete sa ove godine u koliziji";

		// Ova statistika se izvrsava presporo:
		
		/*
		$q604a = db_query("select count(*) from student_studij as ss where ss.studij=$studij and ss.akademska_godina=$akgod and ss.$sem_stud_upit");
		$q604 = db_query("select count(*) from student_studij as ss where ss.studij=$studij and ss.akademska_godina=$akgod and ss.$sem_stud_upit and (select count(*) from student_studij as ss2 where ss2.student=ss.student and ss2.studij=$studij and ss2.$sem_stud_upit and ss2.akademska_godina<$akgod)=0");
		$q604b = db_query("select count(*) from student_labgrupa as sl, labgrupa as l, ponudakursa as pk where sl.labgrupa=l.id and l.predmet=pk.id and pk.akademska_godina=$akgod and pk.studij=$studij and $semestar_upit and (select count(*) from student_studij as ss where ss.student=sl.student and ss.studij=$studij and ss.akademska_godina=$akgod and ss.$sem_stud_upit)=0");

		$redovnih = db_result($q604,0,0);
		$ponovaca = db_result($q604a,0,0) - $redovnih;
		$prenesenih = db_result($q604b,0,0);
		$ispis_br_studenata = "Predmete slušalo: <b>$redovnih</b> redovnih studenata + <b>$ponovaca</b> ponovaca + <b>$prenesenih</b> prenesenih predmeta";
		*/

	} else if ($cista_gen==1) {
		// Redovni studenti i ponovci

		$q40 = db_query("select ss.student from student_studij as ss where ss.akademska_godina=$akgod $studij_upit_ss and ss.$sem_stud_upit");
		$q50 = db_query("select count(*) from student_studij as ss where ss.akademska_godina=$akgod $studij_upit_ss and ss.$sem_stud_upit and (select count(*) from student_studij as ss2 where ss2.student=ss.student $studij_upit_ss2 and ss2.$sem_stud_upit and ss2.akademska_godina<$akgod)=0");

		$uk_studenata = db_num_rows($q40);
		$redovnih = db_result($q50,0,0);
		$ponovaca = $uk_studenata-$redovnih;
		$ispis_br_studenata = "Semestar upisalo: <b>$redovnih</b> redovnih studenata + <b>$ponovaca</b> ponovaca";

	} else if ($cista_gen==2) {
		// Samo redovni, bez ponovaca (nisu nikada slusali istu ak. godinu)

		$q40 = db_query("select ss.student from student_studij as ss where ss.akademska_godina=$akgod $studij_upit_ss and ss.$sem_stud_upit and ss.ponovac=0 and (select count(*) from student_studij as ss2 where ss2.student=ss.student $studij_upit_ss2 and ss2.$sem_stud_upit and ss2.akademska_godina<$akgod)=0");

		$uk_studenata = db_num_rows($q40);
		$ispis_br_studenata = "Semestar upisalo: <b>$uk_studenata</b> redovnih studenata";

	} else if ($cista_gen==3) {
		// Studenti koji nisu nikada nista ponavljali (upisali fakultet prije semestar/2 godina)
		// FIXME: Pretpostavka je da IDovi akademskih godina idu redom
		$upisao_godine = $akgod;
		if ($period==0) {
			$upisao_godine -= intval(($semestar+1)/2);
		} else {
			$upisao_godine -= $godina;
		}

		$q40 = db_query("select ss.student from student_studij as ss where ss.akademska_godina=$akgod $studij_upit_ss and ss.$sem_stud_upit and ss.ponovac=0 and (select count(*) from student_studij as ss2 where ss2.student=ss.student and ss2.akademska_godina<=$upisao_godine)=0");
		$uk_studenata = db_num_rows($q40);
		$ispis_br_studenata = "Semestar upisalo: <b>$uk_studenata</b> studenata &quot;čiste generacije&quot;";

	} else if ($cista_gen==4) {
		// Samo ponovci

		$q40 = db_query("select ss.student from student_studij as ss where ss.akademska_godina=$akgod $studij_upit_ss and ss.$sem_stud_upit and ss.ponovac=1");

		$uk_studenata = db_num_rows($q40);
		$ispis_br_studenata = "Semestar upisalo: <b>$uk_studenata</b> ponovaca";
	}


	// Cache ispita za I i II parcijalni ispit
	// Gledamo samo redovni rok a.k.a. prvi ispit datog tipa
	$cache_ispiti = $cache_predmeti = array();
	if ($ispit==1 || $ispit==2) {
		$q90 = db_query("select i.id, p.id from ispit as i, ponudakursa as pk, predmet as p where i.predmet=p.id and i.akademska_godina=pk.akademska_godina and pk.predmet=p.id and pk.akademska_godina=$akgod $studij_upit_pk and $semestar_upit and i.komponenta=$ispit group by i.predmet,i.komponenta");
		while ($r90 = db_fetch_row($q90)) {
			array_push($cache_ispiti,$r90[0]);
			array_push($cache_predmeti,$r90[1]);
		}
	}

	// Cache komponenti
	if ($ispit==5) {
		$cache_komponente = array();
		if ($studij==-1) 
			$q31 = db_query("select distinct p.id, p.naziv, 1 from predmet as p, ponudakursa as pk where pk.predmet=p.id and pk.akademska_godina=$akgod $studij_upit_pk and $semestar_upit order by pk.obavezan desc, p.naziv");
		else
			$q31 = db_query("select p.id, p.naziv, pk.obavezan from predmet as p, ponudakursa as pk where pk.predmet=p.id and pk.akademska_godina=$akgod $studij_upit_pk and $semestar_upit order by pk.obavezan desc, p.naziv");
		while ($r31 = db_fetch_row($q31)) {
			$predmet = $r31[0];
			$cache_komponente[$predmet] = array();
			$q95 = db_query("select k.id, k.prolaz, k.gui_naziv from komponenta as k, tippredmeta_komponenta as tpk, akademska_godina_predmet as agp where agp.predmet=$predmet and agp.akademska_godina=$akgod and agp.tippredmeta=tpk.tippredmeta and tpk.komponenta=k.id and k.tipkomponente!=2 and k.gui_naziv != 'Usmeni' and k.gui_naziv != 'Završni ispit'");
			while ($r95 = db_fetch_row($q95)) {
				$cache_komponente[$predmet][$r95[0]] = $r95[1];
			}
		}
	}


	// GLAVNA PETLJA
	// Izracunavanje statistickih podataka

	$max_broj_polozenih=0;
	while ($r40 = db_fetch_row($q40)) {
		$stud_id = $r40[0];

		// Zaglavlje za poimenicni spisak studenata
		if ($studenti==1) {
			$q100 = db_query("select ime, prezime, brindexa from osoba where id=$stud_id");
			$imeprezime[$stud_id] = db_result($q100,0,1)." ".db_result($q100,0,0);
			$brindexa[$stud_id] = db_result($q100,0,2);
			/* Korisna informacija - kako je upotrijebiti?
			$q105 = db_query("select studij from student_studij where student=$stud_id");
			$st_studij[$stud_id] = db_result($q105,0,0);*/

			if ($oboji=="odsjek" || $studij==-1) {
				$q105 = db_query("select ss.studij, s.kratkinaziv from student_studij as ss, studij as s where ss.student=$stud_id and ss.studij!=1 and ss.studij=s.id limit 1");
				$student_studij[$stud_id] = db_result($q105,0,0);
				$student_studij_naziv[$stud_id] = db_result($q105,0,1);
			}
		}

		// Upit za I i II parcijalni ispit
		if ($ispit==1 || $ispit==2) {
			$broj_polozenih=0;
			foreach ($cache_ispiti as $redni_broj=>$id_ispita) {
				$id_predmeta=$cache_predmeti[$redni_broj];

				$q100 = db_query("select ocjena from ispitocjene where ispit=$id_ispita and student=$stud_id");
				if (db_num_rows($q100)>0) {
					$ocjena = db_result($q100,0,0);
					$izaslo[$id_predmeta]++;
					if ($ocjena>=10) {
						$polozilo[$id_predmeta]++;
						$broj_polozenih++;
					}
					if ($studenti==1) {
						$ispitocjena[$stud_id][$id_predmeta] = $ocjena;
						$suma_bodova[$stud_id] += $ocjena;
					}
				} else {
					if ($studenti==1) $ispitocjena[$stud_id][$id_predmeta] = "/";
				}
			}
			$ispita_polozenih[$broj_polozenih]++;
			if ($broj_polozenih>$max_broj_polozenih) 
				$max_broj_polozenih = $broj_polozenih;
			if ($studenti==1) 
				$polozio[$stud_id] = $broj_polozenih;


		// Po ukupnom broju bodova
		} else if ($ispit==3) {
//			$stud_predmeti_ar=array();
			$broj_polozenih=0;
			if ($studij==-1)
				$q200 = db_query("select pk.predmet, kb.bodovi from komponentebodovi as kb, ponudakursa as pk where kb.student=$stud_id and kb.predmet=pk.id $studij_upit_pk and pk.akademska_godina=$akgod and $semestar_upit");
			else
				$q200 = db_query("select pk.predmet, kb.bodovi from komponentebodovi as kb, ponudakursa as pk where kb.student=$stud_id and kb.predmet=pk.id $studij_upit_pk and pk.akademska_godina=$akgod and $semestar_upit");
			while ($r200 = db_fetch_row($q200)) {
				$suma_bodova[$stud_id] += $r200[1];
				$ispitocjena[$stud_id][$r200[0]] += $r200[1];
//				array_push($stud_predmeti_ar,$r200[0]);
			}
			foreach ($ispitocjena[$stud_id] as $id_predmeta => $m_bodova) {
				if ($m_bodova>=40) {
					$polozilo[$id_predmeta]++;
					$broj_polozenih++;
				}
				$izaslo[$id_predmeta]++;
			}
			$ispita_polozenih[$broj_polozenih]++;
			if ($broj_polozenih>$max_broj_polozenih) 
				$max_broj_polozenih = $broj_polozenih;
			if ($studenti==1) 
				$polozio[$stud_id] = $broj_polozenih;

		// Konacna ocjena
		} else if ($ispit==4) {
			if ($studij==-1) 
				$q110 = db_query("select pk.predmet,ko.ocjena from konacna_ocjena as ko, ponudakursa as pk, student_predmet as sp where ko.student=$stud_id and ko.predmet=pk.predmet and ko.akademska_godina=$akgod $studij_upit_pk and pk.akademska_godina=$akgod and $semestar_upit and sp.student=$stud_id and sp.predmet=pk.id and ko.odluka IS NULL"); // Eliminisemo ocjene po odluci
			else
				$q110 = db_query("select pk.predmet,ko.ocjena from konacna_ocjena as ko, ponudakursa as pk where ko.student=$stud_id and ko.predmet=pk.predmet and ko.akademska_godina=$akgod $studij_upit_pk and pk.akademska_godina=$akgod and $semestar_upit and ko.odluka IS NULL");
			$broj_polozenih=0;
			while ($r110 = db_fetch_row($q110)) {
				if ($r110[1] >= 6 ) {
					$polozilo[$r110[0]]++;
					$broj_polozenih++;
				}
				if ($studenti==1) {
					$ispitocjena[$stud_id][$r110[0]] = $r110[1];
					$suma_bodova[$stud_id] += $r110[1];
				}
			}
			$ispita_polozenih[$broj_polozenih]++;
			if ($broj_polozenih>$max_broj_polozenih) $max_broj_polozenih=$broj_polozenih;
			if ($studenti==1) $polozio[$stud_id] = $broj_polozenih;

			// Niz $izaslo punimo brojem studenata upisanih na predmet
			if ($studij==-1)
				$q120 = db_query("select pk.predmet from student_predmet as sp, ponudakursa as pk where sp.student=$stud_id and sp.predmet=pk.id and pk.akademska_godina=$akgod $studij_upit_pk and $semestar_upit");
			else
				$q120 = db_query("select pk.predmet from student_predmet as sp, ponudakursa as pk where sp.student=$stud_id and sp.predmet=pk.id and pk.akademska_godina=$akgod $studij_upit_pk and $semestar_upit");
			while ($r120 = db_fetch_row($q120)) {
				$izaslo[$r120[0]]++;
				if ($studenti==1 && $ispitocjena[$stud_id][$r120[0]]<6) {
					// Ako student sluša predmet, a nije ga položio, stavljamo minus
					$ispitocjena[$stud_id][$r120[0]]="-";
				}
			}

		// Ima li uslove za usmeni?
		} else if ($ispit==5) {
			$broj_polozenih=0;
			if ($studij==-1)
				$q120 = db_query("select pk.predmet, pk.id from student_predmet as sp, ponudakursa as pk where sp.student=$stud_id and sp.predmet=pk.id and pk.akademska_godina=$akgod $studij_upit_pk and $semestar_upit");
			else
				$q120 = db_query("select pk.predmet, pk.id from student_predmet as sp, ponudakursa as pk where sp.student=$stud_id and sp.predmet=pk.id and pk.akademska_godina=$akgod $studij_upit_pk and $semestar_upit");
			while ($r120 = db_fetch_row($q120)) {
				$predmet = $r120[0];
				$ponudakursa = $r120[1];
				$izaslo[$predmet]++;
				$polozenih_komponenti = 0;
				foreach ($cache_komponente[$predmet] as $komponenta_id => $komponenta_prolaz) {
					$q250 = db_query("select bodovi from komponentebodovi where student=$stud_id and predmet=$ponudakursa and komponenta=$komponenta_id");
					if (db_num_rows($q250)>0) {
						$bodovi = db_result($q250,0,0);
						$ispitocjena[$stud_id][$predmet] += $bodovi;
						if ($bodovi >= $komponenta_prolaz) $polozenih_komponenti++;
					}
					// Ako je prolaz=0 priznajemo čak i ako student nije izašao na ispit
					else if ($komponenta_prolaz==0) $polozenih_komponenti++;
				}
				if ($polozenih_komponenti == count($cache_komponente[$predmet])) {
					$polozilo[$predmet]++;
					$broj_polozenih++;
				}
			}
			$ispita_polozenih[$broj_polozenih]++;
			if ($broj_polozenih>$max_broj_polozenih) 
				$max_broj_polozenih = $broj_polozenih;
			if ($studenti==1) 
				$polozio[$stud_id] = $broj_polozenih;

		}
	}

	// Ispis podataka
	if ($studenti==0) {
		// Ispisujemo samo sumarne podatke
		while ($r30 = db_fetch_row($q30)) {
			if ($ispit==4 && $izaslo[$r30[0]] == 0) continue;
			$naziv = $r30[1];
			if ($r30[2]==0) $naziv .= " *";
			?><tr><td><?=$naziv?></td>
			<td><?=intval($izaslo[$r30[0]])?></td>
			<td><?=intval($polozilo[$r30[0]])?></td>
			<td><?=procenat($polozilo[$r30[0]],$izaslo[$r30[0]])?></td></tr><?
		}

	} else {
		// Sortiranje niza studenata
		if ($sortiranje==0) {
			// po prezimenu i imenu
			uasort($imeprezime,"bssort"); // bssort - bosanski jezik

		} else if ($sortiranje==1) {
			// po broju bodova i polozenih ispita
			function tablica_sort($a, $b) {
				global $polozio,$suma_bodova;
				if ($polozio[$a]>$polozio[$b]) return -1;
				else if ($polozio[$a]<$polozio[$b]) return 1;
				else if ($suma_bodova[$a]>$suma_bodova[$b]) return -1;
				return 1;
			}
			uksort($imeprezime,"tablica_sort");

		} else if ($sortiranje==2) {
			// po broju indeksa
			function indeks_sort($a, $b) {
				global $brindexa;
				if (intval($brindexa[$a])<intval($brindexa[$b])) return -1;
				if (intval($brindexa[$a])>intval($brindexa[$b])) return 1;
				return 0;
			}
			uksort($imeprezime,"indeks_sort");
		}
		
		// Ispis redova za studente
		$rbr=0;
		$oldsuma=-1; $oldpolozio=-1;
		foreach ($imeprezime as $stud_id => $imepr) {
			$rbr++;
			// Kod sortiranja po broju bodova, 
			// redni broj se ne uvecava ako je broj bodova jednak
			if ($sortiranje==0 || $oldsuma != $suma_bodova[$stud_id] || $oldpolozio != $polozio[$stud_id]) {
				$rrbr=$rbr;
			}

			$bgcolor="#FFFFFF";
			if ($oboji=="odsjek") {
				if ($student_studij[$stud_id]==2) $bgcolor="#FFCCCC";
				else if ($student_studij[$stud_id]==3) $bgcolor="#CCFFCC";
				else if ($student_studij[$stud_id]==4) $bgcolor="#CCCCFF";
				else if ($student_studij[$stud_id]==5) $bgcolor="#FFCCFF";
			}

			?><tr bgcolor="<?=$bgcolor?>">
				<td><?=$rrbr?></td>
				<td><?=$imepr?></td>
				<td><?=$brindexa[$stud_id]?></td><?
			if ($studij==-1) {
				print "<td>".$student_studij_naziv[$stud_id]."</td>\n";
			}
			foreach ($kursevi as $kurs_id => $kurs) {
				if ($ispitocjena[$stud_id][$kurs_id]===NULL) $ispitocjena[$stud_id][$kurs_id]="/";
				print "<td>".$ispitocjena[$stud_id][$kurs_id]."</td>\n";
			}
			print "<td>".$polozio[$stud_id]."</td></tr>\n";
			$oldsuma = $suma_bodova[$stud_id];
			$oldpolozio = $polozio[$stud_id];
		}

		// Sumarni podaci na kraju tabele
		print '<tr><td colspan="3" align="right">';
		if ($ispit==1 || $ispit==2) 
			print 'PRISTUPILO ISPITU:&nbsp; </td>';
		else
			print 'UPISALO PREDMET:&nbsp; </td>';
		if ($studij==-1) print "<td>&nbsp;</td>";
		foreach ($kursevi as $kurs_id => $kurs) {
			print "<td>".intval($izaslo[$kurs_id])."</td>\n";
		}
		print "<td>&nbsp;</td></tr>\n";

		print '<tr><td colspan="3" align="right">POLOŽILO:&nbsp; </td>';
		if ($studij==-1) print "<td>&nbsp;</td>";
		foreach ($kursevi as $kurs_id => $kurs) {
			print "<td>".intval($polozilo[$kurs_id])."</td>\n";
		}
		print "<td>&nbsp;</td></tr>\n";

		print '<tr><td colspan="3" align="right">PROCENAT:&nbsp; </td>';
		if ($studij==-1) print "<td>&nbsp;</td>";
		foreach ($kursevi as $kurs_id => $kurs) {
			print "<td>".procenat($polozilo[$kurs_id],$izaslo[$kurs_id])."</td>\n";
		}
		print "<td>&nbsp;</td></tr>\n";
	}

	// Statistika broja studenata
	print "</table>\n* Predmet je izborni\n\n<br/><br/>$ispis_br_studenata<br/><br/>\n";
	
	// Suma po broju polozenih ispita/predmeta
	if ($ispit==4) $tekst="predmeta"; else $tekst="ispita";
	for ($i=$max_broj_polozenih; $i>=0; $i--) {
		print "Položilo $i $tekst: <b>".$ispita_polozenih[$i]."</b> (".procenat($ispita_polozenih[$i],$uk_studenata).")<br/>\n";
	}
}

// PODIZVJESTAJ 2: Ukupan zbir bodova, bez pojedinacnih studenata
else if ($studenti==0 && $ispit == 3) {
	// Ovo će biti komplikovano....
}



// PODIZVJESTAJ 5: Ukupan broj bodova, pojedinacni studenti
// ****   NEOPTIMIZOVANO
else if ($studenti==1 && $ispit==3) {


	// tabela kurseva i studenata
	$kursevi = array();
	$imeprezime = array();
	$brind = array();
	$sirina = 200;
	while ($r30 = db_fetch_row($q30)) {
		$kursevi[$r30[0]] = $r30[1];

		$q601 = db_query("select s.id, s.ime, s.prezime, s.brindexa from student as s, student_labgrupa as sl, labgrupa as l where sl.student=s.id and sl.labgrupa=l.id and l.predmet=$r30[0]");
		while ($r601 = db_fetch_row($q601)) {
			$imeprezime[$r601[0]] = "$r601[2] $r601[1]";
			$brind[$r601[0]] = $r601[3];
		}
		$sirina += 200;
	}

	uasort($imeprezime,"bssort"); // bssort - bosanski jezik

	// array zadaća - optimizacija
	$kzadace = array();
	foreach ($kursevi as $kurs_id => $kurs) {
		$q600a = db_query("select z.id, z.zadataka from zadaca as z, ponudakursa as pk where pk.id=$kurs_id and pk.predmet=z.predmet and pk.akademska_godina=z.akademska_godina");
		$tmpzadaca = array();
		while ($r600a = db_fetch_row($q600a)) {
			$tmpzadaca[$r600a[0]] = $r600a[1];
		}
		$kzadace[$kurs_id] = $tmpzadaca;
	}

	?>
	<table width="<?=$sirina?>" border="1" cellspacing="0" cellpadding="2">
	<tr>
		<td rowspan="2" valign="center">R. br.</td>
		<td rowspan="2" valign="center">Broj indeksa</td>
		<td rowspan="2" valign="center">Prezime i ime</td>
	<?
	foreach ($kursevi as $kurs) {
		print '<td colspan="6" align="center">'.$kurs."</td>\n";
	}
	?>
		<td rowspan="2" valign="center" align="center">UKUPNO</td>
	</tr>
	<tr>
	<?
	for ($i=0; $i<count($kursevi); $i++) {
		?>
		<td align="center">I</td>
		<td align="center">II</td>
		<td align="center">Int</td>
		<td align="center">P</td>
		<td align="center">Z</td>
		<td align="center">Ocjena</td>
		<?
	}
	print "</tr>\n";
	$rbr=1;

	// Slušalo / položilo predmet
	$slusalo = array();
	$polozilo = array();

	foreach ($imeprezime as $stud_id => $stud_imepr) {
		?>
		<tr>
			<td><?=$rbr++?></td>
			<td><?=$brind[$stud_id]?></td>
			<td><?=$stud_imepr?></td>
		<?
		$polozio = 0;
		foreach ($kursevi as $kurs_id => $kurs) {
			$slusalo[$kurs_id]++;
			$q602 = db_query("select io.ocjena,i.komponenta from ispit as i, ispitocjene as io, ponudakursa as pk where io.ispit=i.id and io.student=$stud_id and i.predmet=pk.predmet and i.akademska_godina=pk.akademska_godina and pk.id=$kurs_id");
			$ispit = array();
			$ispit[1] = $ispit[2] = $ispit[3] = "/";
			while ($r602 = db_fetch_row($q602)) {
				if ($r602[0] > $ispit[$r602[1]] || $ispit[$r602[1]] == "/") 
					$ispit[$r602[1]] = $r602[0];
			}
			for ($i=1; $i<4; $i++) {
				if ($ispit[$i] >= 0)
					print "<td>$ispit[$i]</td>\n";
				else
					print "<td>&nbsp;</td>\n";
			}

			$q603 = db_query("select count(*) from prisustvo as p,cas as c, labgrupa as l where p.student=$stud_id and p.cas=c.id and c.labgrupa=l.id and l.predmet=$kurs_id and p.prisutan=0");
			if (db_result($q603,0,0)<=3) {
				print "<td>10</td>\n";
				$ukupno += 10;
			} else
				print "<td>0</td>\n";

			$zadaca = 0;
			foreach ($kzadace[$kurs_id] as $zid => $zadataka) {
				for ($i=1; $i<=$zadataka; $i++) {
					$q605 = db_query("select status,bodova from zadatak where zadaca=$zid and redni_broj=$i and student=$stud_id order by id desc limit 1");
					if ($r605 = db_fetch_row($q605))
						if ($r605[0] == 5)
							$zadaca += $r605[1];
//					$zadaca .= $i." ";
				}
			}
			print "<td>$zadaca</td>\n";

			$q606 = db_query("select ko.ocjena from konacna_ocjena as ko, ponudakursa as pk where ko.student=$stud_id and ko.predmet=pk.predmet and ko.akademska_godina=pk.akademska_godina and pk.id=$kurs_id");
			if (db_num_rows($q606)>0) {
				$ocj = db_result($q606,0,0);
				print "<td>$ocj</td>\n";
				if ($ocj >= 6) $polozio++;
				$polozilo[$kurs_id]++;
			} else
				print "<td>&nbsp;</td>\n";
		}
		print "<td>$polozio</td></tr>\n";
		$i++;
	}
	print '<tr><td colspan="3" align="right">SLUŠALO</td>';
	foreach ($kursevi as $kurs_id => $kurs) {
		print '<td colspan="5">'.$slusalo[$kurs_id]."</td>\n";
	}
	print '<td>&nbsp;</td></tr><tr><td colspan="3" align="right">POLOŽILO</td>';
	foreach ($kursevi as $kurs_id => $kurs) {
		if (intval($polozilo[$kurs_id])==0) $polozilo[$kurs_id]="0";
		print '<td colspan="5">'.$polozilo[$kurs_id]."</td>\n";
	}
	print '<td>&nbsp;</td></tr><tr><td colspan="3" align="right">PROCENAT</td>';
	foreach ($kursevi as $kurs_id => $kurs) {
		$proc = intval(($polozilo[$kurs_id]/$slusalo[$kurs_id])*100)/100;
		print '<td colspan="5">'.$proc."%</td>\n";
	}
	print '<td>&nbsp;</td></tr></table>';
}

}
