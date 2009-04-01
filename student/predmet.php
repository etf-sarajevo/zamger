<?

// STUDENT/PREDMET - statusna stranica predmeta

// v3.9.1.0 (2008/02/19) + Kopiran raniji stud_status, uz novi dizajn
// v3.9.1.1 (2008/03/28) + Dodana ikona za slanje novog zadatka (zad_novi.png)
// v3.9.1.2 (2008/04/09) + Dodan prikaz akademske godine uz ime predmeta; zadace bez imena; navigacija za zadace je prikazivala visak zadataka; otvori PDF u novom prozoru
// v3.9.1.3 (2008/10/02) + Dodana provjera da li student slusa predmet
// v4.0.0.0 (2009/02/19) + Release
// v4.0.9.1 (2009/03/24) + Prebacena polja ects i tippredmeta iz tabele ponudakursa u tabelu predmet
// v4.0.9.2 (2009/03/31) + Tabela ispit preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.3 (2009/03/31) + Tabela konacna_ocjena preusmjerena sa ponudakursa na tabelu predmet


function student_predmet() {

global $userid;


// Određivanje predmeta iz labgrupe
$ponudakursa = intval($_REQUEST['predmet']);

$q10 = myquery("select p.naziv,ag.naziv,p.id,ag.id from predmet as p, ponudakursa as pk, akademska_godina as ag where pk.id=$ponudakursa and pk.predmet=p.id and pk.akademska_godina=ag.id");
if (mysql_num_rows($q10)<1) {
	zamgerlog("nepoznat predmet $ponudakursa",3); // nivo 3: greska
	biguglyerror("Nepoznat predmet $ponudakursa");
	return;
}

// Da li student slusa predmet?
$q15 = myquery("select count(*) from student_predmet where student=$userid and predmet=$ponudakursa");
if (mysql_result($q15,0,0)==0) {
	zamgerlog("student ne slusa predmet p$ponudakursa", 3);
	biguglyerror("Niste upisani na ovaj predmet");
	return;
}

?>
<br/>
<p style="font-size: small;">Predmet: <b><?=mysql_result($q10,0,0)?> (<?=mysql_result($q10,0,1)?>)</b><br/>
<?

$predmet = mysql_result($q10,0,2);
$ag = mysql_result($q10,0,3);

// Određivanje labgrupe
$q20 = myquery("select l.naziv from labgrupa as l, student_labgrupa as sl where l.predmet=$ponudakursa and l.id=sl.labgrupa and sl.student=$userid limit 1");
if (mysql_num_rows($q20)>0) {
	?>Grupa: <b><?=mysql_result($q20,0,0)?></b></p><?
}

print "<br/>\n";



// PROGRESS BAR

$q30 = myquery("select kb.bodovi,k.maxbodova from komponentebodovi as kb, komponenta as k where kb.student=$userid and kb.predmet=$ponudakursa and kb.komponenta=k.id");

$bodova=$mogucih=0;
while ($r30 = mysql_fetch_row($q30)) {
	$bodova += $r30[0];
	$mogucih += $r30[1];
}
if ($bodova>$mogucih) $bodova=$mogucih; //ne bi se trebalo desiti


// boja označava napredak studenta
$procent = intval(($bodova/$mogucih)*100);
if ($procent>=75) 
	$color="#00FF00";
else if ($procent>=50)
	$color="#FFFF00";
else
	$color="#FF0000";


$tabela1=$procent*2;
$tabela2=200-$tabela1;

$ispis1 = "<img src=\"images/fnord.gif\" width=\"$tabela1\" height=\"10\">";
$ispis2 = "<img src=\"images/fnord.gif\" width=\"$tabela2\" height=\"1\"><br/> $bodova bodova";

if ($tabela1>$tabela2) { 
	$ispis1="<img src=\"images/fnord.gif\" width=\"$tabela1\" height=\"1\"><br/> $bodova bodova";
	$ispis2="<img src=\"images/fnord.gif\" width=\"$tabela2\" height=\"10\">";
}

?>


<!-- progress bar -->

<center><table border="0"><tr><td align="left">
<p>Osvojili ste....<br/>
<table style="border:1px;border-style:solid" width="206" cellpadding="0" cellspacing="2"><tr>
<td width="<?=$tabela1?>" bgcolor="<?=$color?>"><?=$ispis1?></td>
<td width="<?=$tabela2?>" bgcolor="#FFFFFF"><?=$ispis2?></td></tr></table>

<table width="208" border="0" cellspacing="0" cellpadding="0"><tr>
<td width="68">0</td>
<td align="center" width="68">50</td>
<td align="right" width="69">100</td></tr></table>
što je <?=$procent?>% od trenutno mogućih <?=$mogucih?> bodova.</p>
</td></tr></table></center>


<!-- end progress bar -->
<?




//  PRISUSTVO NA VJEŽBAMA


function prisustvo_ispis($idgrupe,$imegrupe,$ponudakursa,$komponenta) {
	global $userid;

	if (!preg_match("/\w/",$imegrupe)) $imegrupe = "[Bez naziva]";

	$odsustva=0;
	$q70 = myquery("select id,UNIX_TIMESTAMP(datum), vrijeme from cas where labgrupa=$idgrupe and predmet=$ponudakursa and komponenta=$komponenta");
	if (mysql_num_rows($q70)<1) return;

	$datumi = $vremena = $statusi = "";
	while ($r70 = mysql_fetch_row($q70)) {
		$datumi .= "<td>".date("d.m",$r70[1])."</td>\n";
		list($sati,$minute,$sekunde) = explode(":", $r70[2]);
		$vremena .= "<td>$sati<sup>$minute</sup></td>\n";
		$q80 = myquery("select prisutan from prisustvo where student=$userid and cas=$r70[0]");
		if (mysql_num_rows($q80)<1) {
			$statusi .= "<td bgcolor=\"#FFFFCC\" align=\"center\">/</td>\n";
		} else if (mysql_result($q80,0,0)==1) {
			$statusi .= "<td bgcolor=\"#CCFFCC\" align=\"center\">DA</td>\n";
		} else {
			$statusi .= "<td bgcolor=\"#FFCCCC\" align=\"center\">NE</td>\n";
			$odsustva++;
		}
	}

	
	?>

	<b>Prisustvo (<?=$imegrupe?>):</b><br/>
	<table cellspacing="0" cellpadding="2" border="0" id="prisustvo">
	<tr>
		<th>Datum</th>
	<?=$datumi?>
	</tr>
	<tr>
		<th>Vrijeme</th>
	<?=$vremena?>
	</tr>
	<tr>
		<th>Prisutan</th>
	<?=$statusi?>
	</tr>
	</table>
	</p>
	
	<?
	return $odsustva;
}

$q40 = myquery("select k.id,k.maxbodova,k.prolaz,k.opcija from komponenta as k, tippredmeta_komponenta as tpk, predmet as p
where p.id=$predmet and p.tippredmeta=tpk.tippredmeta and tpk.komponenta=k.id and k.tipkomponente=3"); // 3 = prisustvo

while ($r40 = mysql_fetch_row($q40)) {
	$id_komponente = $r40[0];
	$max_bodova = $r40[1];
	$min_bodova = $r40[2];
	$max_izostanaka = $r40[3];

	$odsustva = 0;
	$q60 = myquery("select l.id,l.naziv from labgrupa as l, student_labgrupa as sl where l.predmet=$ponudakursa and l.id=sl.labgrupa and sl.student=$userid");
	
	while ($r60 = mysql_fetch_row($q60)) {
		$odsustva += prisustvo_ispis($r60[0],$r60[1],$ponudakursa,$id_komponente);
	}
	
	$odsustva += prisustvo_ispis(0,"sve grupe",$ponudakursa,$id_komponente);
	
	if ($odsustva<=$max_izostanaka) {
		?><p>Ukupno na prisustvo imate <b><?=$max_bodova?></b> bodova.</p>
		<?
	} else {
		?><p>Ukupno na prisustvo imate <b><?=$min_bodova?></b> bodova.</p>
		<?
	}
}






//  ZADAĆE


// Statusne ikone:
$stat_icon = array("zad_bug", "zad_preg", "zad_copy", "zad_bug", "zad_preg", "zad_ok");
$stat_tekst = array("Bug u programu", "Pregled u toku", "Zadaća prepisana", "Bug u programu", "Pregled u toku", "Zadaća OK");


?>


<!-- zadace -->

<b>Zadaće:</b><br/>
<table cellspacing="0" cellpadding="2" border="0" id="zadace">
	<thead>
		<tr>
<?


$q100 = myquery("select count(*) from studentski_moduli where predmet=$ponudakursa and url like '%student/zadaca%' and aktivan=1");

// Prikaz sa predmete kod kojih nije aktivno slanje zadaća
if (mysql_result($q100,0,0)==0) {
	// U pravilu ovdje ima samo jedan zadatak, pa ćemo sumirati
	$q110 = myquery("select id,naziv,zadataka from zadaca where predmet=$ponudakursa order by komponenta,naziv");
	while ($r110 = mysql_fetch_row($q110)) {
		$idovi_zadaca[] = $r110[0];
		$brzad[$r110[0]] = $r110[2];
		$naziv_zadace = $r110[1];
		if (!preg_match("/\w/",$naziv_zadace)) $naziv_zadace = "[Bez naziva]";
		?><td><?=$naziv_zadace?></td><?
	}
?>
		<td><b>Ukupno bodova</b></td>
		</tr>
	</thead>
<tbody>
<?
	$uk_bodova=0;
	foreach ($idovi_zadaca as $zadaca) {
		$bodova=0;
		$status=-1;
		for ($zadatak=1; $zadatak<=$brzad[$zadaca]; $zadatak++) {
			$q120 = myquery("select status,bodova from zadatak where student=$userid and zadaca=$zadaca and redni_broj=$zadatak order by id desc limit 1");
			if (mysql_num_rows($q120)>0) {
				$status = mysql_result($q120,0,0);
				$bodova += mysql_result($q120,0,1);
			}
		}
		if ($status==-1) {
		?>
		<td>&nbsp;</td>
		<?
		} else {
		?>
		<td><img src="images/16x16/<?=$stat_icon[$status]?>.png" width="16" height="16" border="0" align="center" title="<?=$stat_tekst[$status]?>" alt="<?=$stat_tekst[$status]?>"> <?=$bodova?></td>
		<?
		}
		$uk_bodova+=$bodova;
	}
	?>
	<td><?=$uk_bodova?></td></tr>
</tbody>
</table>

&nbsp;<br/>

	<?


// Prikaz sa aktivnim slanjem
} else { // if (mysql_result($q100...

?>
	<td>&nbsp;</td>
<?

// Zaglavlje tabele - potreban nam je max. broj zadataka u zadaci

$q20 = myquery("select zadataka from zadaca where predmet=$ponudakursa order by zadataka desc limit 1");
$broj_zadataka = mysql_result($q20,0,0);
for ($i=1;$i<=$broj_zadataka;$i++) {
	?><td>Zadatak <?=$i?>.</td><?
}

?>
		<td><b>Ukupno bodova</b></td>
		<td>&nbsp;</td>
		</tr>
	</thead>
<tbody>
<?


// Tijelo tabele

// LEGENDA STATUS POLJA:
// 0 - nepoznat status
// 1 - nova zadaća
// 2 - prepisana
// 3 - ne može se kompajlirati
// 4 - prošla test, predstoji kontrola
// 5 - pregledana


/* Ovo se sve moglo kroz SQL rijesiti, ali necu iz razloga:
1. PHP je citljiviji
2. MySQL <4.1 ne podrzava subqueries */


$bodova_sve_zadace=0;

$q21 = myquery("select id,naziv,bodova,zadataka from zadaca where predmet=$ponudakursa order by komponenta,id");
while ($r21 = mysql_fetch_row($q21)) {
	$zadaca = $r21[0];
	$mogucih += $r21[2];
	$zzadataka = $r21[3];
	?><tr>
	<th><?=$r21[1]?></th>
	<?
	$bodova_zadaca = 0;

	for ($zadatak=1;$zadatak<=$broj_zadataka;$zadatak++) {
		// Ako tekuća zadaća nema toliko zadataka, ispisujemo blank polje
		if ($zadatak>$zzadataka) {
			?><td>&nbsp;</td><?
			continue;
		}

		// Uzmi samo rjesenje sa zadnjim IDom
		$q22 = myquery("select status,bodova,komentar from zadatak where student=$userid and zadaca=$zadaca and redni_broj=$zadatak order by id desc limit 1");
		if (mysql_num_rows($q22)<1) {
			?><td><a href="?sta=student/zadaca&predmet=<?=$ponudakursa?>&zadaca=<?=$zadaca?>&zadatak=<?=$zadatak?>"><img src="images/16x16/zad_novi.png" width="16" height="16" border="0" align="center" title="Novi zadatak" alt="Novi zadatak"></a></td><?
		} else {
			$status = mysql_result($q22,0,0);
			$bodova_zadatak = mysql_result($q22,0,1);
			$bodova_zadaca += $bodova_zadatak;
			if (strlen(mysql_result($q22,0,2))>2)
				$imakomentar = "<img src=\"images/16x16/komentar.png\"  width=\"15\" height=\"14\" border=\"0\" title=\"Ima komentar\" alt=\"Ima komentar\" align=\"center\">";
			else
				$imakomentar = "";
			?><td><a href="?sta=student/zadaca&predmet=<?=$ponudakursa?>&zadaca=<?=$zadaca?>&zadatak=<?=$zadatak?>"><img src="images/16x16/<?=$stat_icon[$status]?>.png" width="16" height="16" border="0" align="center" title="<?=$stat_tekst[$status]?>" alt="<?=$stat_tekst[$status]?>"> <?=$bodova_zadatak?> <?=$imakomentar?></a></td>
	<?
		}
	}
	?>
	<td><?=$bodova_zadaca?></td><td><a href="?sta=student/pdf&zadaca=<?=$zadaca?>" target="_new"><img src="images/16x16/pdf.png" width="16" height="16" border="0"></a></td></tr>
	<?
	$bodova_sve_zadace += $bodova_zadaca;
}


// Ukupno bodova za studenta
 
$bodova += $bodova_sve_zadace;

?>
	<tr><td colspan="<?=$broj_zadataka+1?>" align="right">UKUPNO: </td>
	<td><?=$bodova_sve_zadace?></td><td>&nbsp;</td></tr>
</tbody>
</table>

<p>Za ponovno slanje zadatka, kliknite na sličicu u tabeli iznad. <a href="#" onclick="javascript:window.open('legenda-zadace.html','blah6','width=320,height=130');">Legenda simbola</a></p>
<br/>

<!-- end zadace -->

<?

} // else




//  ISPITI

?>

<!-- ispiti -->

<b>Ispiti:</b><br/>

<?
	

$q30 = myquery("select i.id,UNIX_TIMESTAMP(i.datum),k.gui_naziv,k.id from ispit as i, komponenta as k where i.predmet=$predmet and i.akademska_godina=$ag and i.komponenta=k.id order by i.datum,i.komponenta");
if (mysql_num_rows($q30) == 0) {
	print "<p>Nije bilo parcijalnih ispita.</p>";
}

while ($r30 = mysql_fetch_row($q30)) {
	$q40 = myquery("select ocjena from ispitocjene where ispit=$r30[0] and student=$userid");
	if (mysql_num_rows($q40)<1) {
//		print "Nije izašao/izašla";
	} else {
		?><p><?=$r30[2]?> (<?=date("d. m. Y",$r30[1])?>): <b><?=mysql_result($q40,0,0)?> bodova</b></p><?
	}
}



// KONAČNA OCJENA

$q50 = myquery("select ocjena from konacna_ocjena where student=$userid and predmet=$predmet and akademska_godina=$ag");
if (mysql_num_rows($q50)>0) {
	?>
	<center>
		<table width="100px" style="border-width: 3px; border-style: solid; border-color: silver">
			<tr><td align="center">
				KONAČNA OCJENA<br/>
				<font size="6"><b><?=mysql_result($q50,0,0)?></b></font>
			</td></tr>
		</table>
	</center>
	<?
}


}

?>