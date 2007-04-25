<?

// v2.9.3.1 (2007/03/17) + napravio da radi
// v2.9.3.2 (2007/03/19) + ispravan prikaz imena zadaće
// v2.9.3.3 (2007/04/08) + optimizacije, code cleanup, komentari
// v3.0.0.0 (2007/04/09) + Release
// v3.0.0.1 (2007/04/25) + Release

?>
<html>
<head>
<title>Prisustvo na vježbama</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="css/style.css" rel="stylesheet" type="text/css" />
</head>
<body>

<h1>Pregled prisustva na vježbama</h1>

<!--p>Legenda:<br/>
<table border="1" cellspacing="0" cellpadding="2">
<tr><td bgcolor="#CCFFCC" width="50">U</td><td>Usmeni</td></tr>
<tr><td bgcolor="#FFFFCC">P1</td><td>Popravni - 1. parcijala</td></tr>
<tr><td bgcolor="#FFFFCC">P2</td><td>Popravni - 2. parcijala</td></tr>
<tr><td bgcolor="#FFEECC">PO</td><td>Popravni - integralno</td></tr>
<tr><td bgcolor="#FFCCFF">??</td><td>Nije skupio/la 40 bodova - nedefinisano (bicete obavijesteni)</td></tr>
<tr><td bgcolor="#FFCCCC">/</td><td>Pao/Pala</td></tr>

</table-->

<?

include("libvedran.php");
dbconnect();


// Provjerimo sesiju radi prikazivanja imena
session_start();
$login = my_escape($_SESSION['login']);
if (!preg_match("/[a-zA-Z0-9]/",$login)) $login="";



// Ako nije izabran predmet, prikaži spisak predmeta
$predmet = intval($_GET['predmet']);
if ($predmet == 0) {
	$q1 = myquery("select p.id,p.naziv,ag.naziv from predmet as p, akademska_godina as ag where ag.id=p.akademska_godina order by ag.naziv,p.naziv");
	print "<p>Izaberite predmet:</p>\n<ul>";
	while ($r1 = mysql_fetch_row($q1)) {
		print "<li><a href=\"pregled-public.php?predmet=$r1[0]\">$r1[1] ($r1[2])</a></li>";
	}

} else {

$q2 = myquery("select p.naziv,ag.naziv from predmet as p, akademska_godina as ag where p.id=$predmet");
print "<p>Predmet: <b>".mysql_result($q2,0,0)." (".mysql_result($q2,0,1).")</b></p>\n";

// Imena prikazujemo samo ako je korisnik profesor/asistent/demonstrator
$imenaopt = 0;
if ($login != "") {
	$q10 = myquery("select admin from auth where login='$login'");
	if (mysql_num_rows($q10)>0 && mysql_result($q10,0,0)>0) 
		$imenaopt=1;
}

if ($imenaopt==0)
	print "<p><b>Napomena:</b> Radi zaštite privatnosti studenata, imena će biti prikazana samo ako ste prijavljeni kao nastavnik/tutor.</p>\n";
 

	$imena=array();
	$topscore=array();


$q10 = myquery("select id,naziv from labgrupa where predmet=$predmet order by id");

while ($r10 = mysql_fetch_row($q10)) {
	$grupa_id = $r10[0];
	$grupa_naziv = $r10[1];
	print "<center><h2>Grupa: $grupa_naziv</h2></center>\n";


	// Plan je sljedeći:
	// Učitamo sve podatke iz tabele u nizove i onda ih samo prikažemo
	// Trebalo bi biti brže od komplikovanih ifova i for petlji a opet raditi
	// sa starim mysql-om :(

	// Rezultati zadaca
	$zadace=array();
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

		// Dodajemo 1 kako bismo kasnije mogli znati da li je vrijednost
		// niza definisana ili ne.
		// undef ne radi :(

		// Slog sa najnovijim IDom se smatra mjerodavnim
		// Ostali su u bazi radi historije
		$zadace[$r100[0]][$r100[1]][$r100[2]]=$bodova;
	}


	// ZAGLAVLJE - PRISUSTVO
	$q101 = mysql_query("select id,datum,vrijeme from cas where labgrupa=$grupa_id order by datum");
	$casova = 0;
	$casovi_zaglavlje = "";
	$cas_id_array = array();
	while ($r101 = mysql_fetch_row($q101)) {
		$cas_id = $r101[0];
		list ($cas_godina,$cas_mjesec,$cas_dan) = explode("-",$r101[1]);
		list ($cas_sat,$cas_minuta,$cas_sekunda) = explode(":",$r101[2]);
		$casovi_zaglavlje .= "<td>$cas_dan.$cas_mjesec<br>$cas_sat:$cas_minuta</td>\n";
		array_push($cas_id_array,$cas_id);
		$casova++;
	}
	if ($casovi_zaglavlje == "") $casovi_zaglavlje = "<td>&nbsp;</td>";
	
	// ZAGLAVLJE - ZADACE
	$vj_id_array = $vj_br_zad = array(); 
	$ocjene_zaglavlje = "";
	$q102 = myquery("select id,naziv,zadataka from zadaca where predmet=$predmet order by id");
	$brzadaca = mysql_num_rows($q102);
	if ($brzadaca == 0) { $brzadaca=1; $ocjene_zaglavlje = "<td>&nbsp;</td>"; }
	else {
		while ($r102 = mysql_fetch_row($q102)) {
			$zad_id = $r102[0];
			$zad_naziv = $r102[1];
			$ocjene_zaglavlje .= "<td>$zad_naziv</td>\n";
			array_push($vj_id_array,$zad_id);
			array_push($vj_br_zad,$r102[2]);
		}
	}

	// ZAGLAVLJE - PARCIJALE
	$q103 = mysql_query("select id,naziv from ispit where predmet=$predmet");
	$par_id_array = array();
	$parc_zaglavlje = "";
	$brparc = mysql_num_rows($q103);
	if ($brparc == 0) {
		$parc_zaglavlje = "<td>&nbsp;</td>";
		$brparc=1;
	} else {
		while ($r103 = mysql_fetch_row($q103)) {
			$parc = $r103[0];
			$parc_zaglavlje .= "<td>$r103[1].</td>\n";
			array_push($par_id_array,$parc);
		}
	}
	if ($casova==0) $casova=1;


	?>
<table cellspacing="0" cellpadding="2" border="1">
<tr>
	<? if ($imenaopt) { ?><td rowspan="2" align="center" valign="center">Ime i prezime</td><? } ?>
	<td rowspan="2" align="center" valign="center">Broj indexa</td>
	<td align="center" colspan="<?=($casova+1)?>">Prisustvo</td>
	<td align="center" colspan="<?=$brzadaca?>">Ocjene iz zadaća</td>
	<td align="center" colspan="<?=$brparc?>">Parcijalni ispiti</td>
	<td align="center" valign="center" rowspan="2" colspan="2">&nbsp;&nbsp;<b>UKUPNO</b>&nbsp;&nbsp;</td>
</tr>
<tr>
	<?=$casovi_zaglavlje?><td>BOD.</td>
	<?=$ocjene_zaglavlje?>
	<?=$parc_zaglavlje?>
</tr>
<?

	// Spisak studenata

	$q200 = mysql_query("select s.id,s.ime,s.prezime,s.brindexa from student as s, student_labgrupa as sl where s.id=sl.student and sl.labgrupa=$grupa_id");
	$imeprezime = array();
	$brind = array();
	while ($r200 = mysql_fetch_row($q200)) {
		$stud_id = $r200[0];
		$stud_ime = $r200[1];
		$stud_prezime = $r200[2];
		$stud_brind = $r200[3];
		$imeprezime[$stud_id] = "$stud_prezime $stud_ime";
		$brind[$stud_id] = $stud_brind;
	}
	uasort($imeprezime,"bssort");
	foreach ($imeprezime as $stud_id => $stud_imepr) {
	
?>
<tr>
	<? if ($imenaopt) print "<td>$stud_imepr</td>"; ?>
	<td><?=$brind[$stud_id]?></td>
<?
		$prisustvo_ispis=$ocjene_ispis=$parc_ispis="";
		$bodova=0;
		$mogucih=0;

		// PRISUSTVO
		if (count($cas_id_array)==0) $prisustvo_ispis = "<td>&nbsp;</td>";
		$odsustvo=0;
		foreach ($cas_id_array as $cid) {
			$q201 = mysql_query("select prisutan,plus_minus from prisustvo where student=$stud_id and cas=$cid");
			if (mysql_num_rows($q201)>0) {
				if (mysql_result($q201,0,0) == 1) { 
					$prisustvo_ispis .= "<td bgcolor=\"#CCFFCC\" align=\"center\">DA</td>";
				} else { 
					$prisustvo_ispis .= "<td bgcolor=\"#FFCCCC\" align=\"center\">NE</td>";
					$odsustvo++;
				}
				//$ocj = mysql_result($r4,0,1);
			} else {
				$prisustvo_ispis .= "<td bgcolor=\"#FFFFCC\"> / </td>";
			}
		}
		if ($odsustvo<=3) {
			$prisustvo_ispis .= "<td>10</td>";
			$bodova+=10;
		} else {
			$prisustvo_ispis .= "<td>0</td>";
		}
		$mogucih+=10;

		// ZADACE
		foreach ($vj_id_array as $n => $vid) {
			$ocjena=0;
			$ima=0; // Da li je poslao ijedan zadatak?
			$ispis=1; // Da li ima nepregledanih zadataka?
			for ($i=1; $i<=$vj_br_zad[$n]; $i++) {
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
				$ocjene_ispis .= "<td> / </td>";
			} else {
				$ocjene_ispis .= "<td> $ocjena </td>";
				$bodova = $bodova + $ocjena;
			}
			$mogucih+=2;
		}

		// PARCIJALE
		if (count($par_id_array)==0) $parc_ispis = "<td>&nbsp;</td>";
		$i=$pao1=$pao2=0;
		foreach ($par_id_array as $pid) {
			$i++;
			$q202 = mysql_query("select ocjena from ispitocjena where student=$stud_id and ispit=$pid");
			if (mysql_num_rows($q202)>0) {
				if (($ocjena = mysql_result($q202,0,0)) == -1) {
					$parc_ispis .= "<td> / </td>";
					if ($i==1) { $pao1 = 1; } else { $pao2 = 1; }
				} else {
					$parc_ispis .= "<td> $ocjena </td>";
					if ($ocjena<10) {
						if ($i==1) { $pao1 = 1; } else { $pao2 = 1; }
					}	
					$bodova = $bodova + $ocjena;
				}
			} else {
				$parc_ispis .= "<td> / </td>";
				if ($i==1) { $pao1 = 1; } else { $pao2 = 1; }
			}
			$mogucih+=20;
		}

		if ($mogucih>0) $procent = round(($bodova/$mogucih)*100); else $procent=0;
		
		$imena[$stud_id]=$stud_imepr;
		$topscore[$stud_id]=$bodova;
		if ($pao1 == 0 && $pao2 == 0) {
			if ($bodova>=40) {
				$thecolor="#CCFFCC";
				$theletter="U";
			} else {
				$thecolor="#FFCCFF";
				$theletter="??";
			}
		} elseif ($bodova<20) {
			$thecolor="#FFCCCC";
			$theletter="/";
		} elseif ($pao1 == 0 && $pao2 == 1) {
			$thecolor="#FFFFCC";
			$theletter="P2";
		} elseif ($pao2 == 0 && $pao1 == 1) {
			$thecolor="#FFFFCC";
			$theletter="P1";
		} else {
			$thecolor="#FFEECC";
			$theletter="P0";
		}

?>
	<?=$prisustvo_ispis?>
	<?=$ocjene_ispis?>
	<?=$parc_ispis?>
	<td align="center"><?=$bodova?> (<?=$procent?>%)</td>
	<td bgcolor="<?=$thecolor?>"><?=$theletter?></td>
<?
	}
?>
</tr>
</table>
<p>&nbsp;</p>
<?

} // while ($r10...

mysql_close();

?>


<!-- TOP LISTA  - ukloniti komentar za ispis

<?
asort($topscore);
$i=0;
$ispis_tl = $ispis_bl = "";
foreach ($topscore as $id => $bodova) {
	if ($i<=15) {
		$ispis_bl .= "<tr><td>".(count($topscore)-$i)."</td><td>".$imena[$id]."</td><td>".$bodova."</td></tr>";
	}
	if ($i>=count($topscore)-15) {
		$ispis_tl .= "<tr><td>".(count($topscore)-$i)."</td><td>".$imena[$id]."</td><td>".$bodova."</td></tr>";
	}
	$i++;
}


# select id,naziv from grupe
?>

<table border="0" cellspacing="0" cellpadding="0">
<tr><td>
	<table border="2" cellspacing="0" cellpadding="3">
	<tr><td colspan="2"><center><h2>Top-Lista</h2></center></td></tr>
	<tr><td>&nbsp;</td><td>IME</td><td>BODOVA</td></tr>
	<?=$ispis_tl;?>
	</table>
</td>
<!--td width="150">&nbsp;</td>
<td>
	<table border="2" cellspacing="0" cellpadding="3">
	<tr><td colspan="2"><center><h2>Bottom-Lista</h2></center></td></tr>
	<tr><td>&nbsp;</td><td>IME</td><td>BODOVA</td></tr>
	<!--?=$ispis_bl;?>
	</table>

</td></tr--></table>
<!-- KRAJ TOP LISTE -->
<?



} // if ($predmet==0) ... else


?>



</body>
</html>