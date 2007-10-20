<?

// v2.9.3.1 (2007/04/05) + Prosljeđivanje labgrupe u modul zadaca
// v2.9.3.2 (2007/04/06) + "odbrana" vs. "slanje"
// v3.0.0.0 (2007/04/09) + Release
// v3.0.0.1 (2007/04/16) + Design komentari by Teo 
// v3.0.0.2 (2007/05/30) + Generisanje zadaća u PDF formatu 
// v3.0.1.0 (2007/06/12) + Release
// v3.0.1.1 (2007/10/10) + Nova struktura baze za predmete; ukinuta oznaka konačnog statusa
// v3.0.1.2 (2007/10/20) + Nova schema tabele ispita; sredjeno forwardovanje force_userid (potreban za admin pristup studentskom interfejsu)


function stud_status() {

global $userid,$labgrupa,$predmet_id,$force_userid;



////////////////////////////
//  ZAGLAVLJE
////////////////////////////


// Dobrodošlica

$q1 = myquery("select ime,prezime from student where id=$userid");
$ime = mysql_result($q1,0,0);
$prezime = mysql_result($q1,0,1);
$stud_spol = substr($ime,strlen($ime)-1);
if ($stud_spol == "a" && $ime != "Vanja" && $ime != "Peđa" && $ime != "Mirza" && $ime != "Feđa" && $ime != "Saša" && $ime != "Alija" && $ime != "Mustafa") {
	print "<h1>Dobro došla, $ime $prezime!<h1>";
} else {
	print "<h1>Dobro došao, $ime $prezime!</h1>";
}



// Određivanje predmeta iz labgrupe

$q1a = myquery("select predmet from labgrupa where id=$labgrupa");
if (mysql_num_rows($q1a)<1) {
	biguglyerror("Nema labgrupe $labgrupa");
	return;
}
$predmet_id = mysql_result($q1a,0,0);


$q1b = myquery("select p.naziv,pk.akademska_godina,pk.aktivan,pk.motd from predmet as p, ponudakursa as pk where pk.id=$predmet_id and pk.predmet=p.id");
if (mysql_num_rows($q1b)<1) {
	biguglyerror("Nema predmeta $predmet_id");
	return;
}
if (mysql_result($q1b,0,2) == 0) {
	niceerror("Predmet nije aktivan. Molimo izaberite neki drugi predmet / lab grupu.");
	return;
}
$ak_god_id = mysql_result($q1b,0,1);
$motd = mysql_result($q1b,0,3);

$q1c = myquery("select naziv from akademska_godina where id=$ak_god_id");
if (mysql_num_rows($q1c)<1) {
	biguglyerror("Nema akademske godine $ak_god_id");
	return;
}

print "<p>Predmet: <b>".mysql_result($q1b,0,0)." (".mysql_result($q1c,0,0).")</b></p>\n";



# MOTD

$q2 = myquery("select naziv,rok from zadaca where predmet=$predmet_id and rok>curdate() and aktivna=1");
while ($r2 = mysql_fetch_row($q2)) {
	print "<center><h2><font color=\"#00AA00\">Rok za slanje zadaće ".$r2[0]." je ".date("d. m. Y.",mysql2time($r2[1]))."</font></h2></center>";
}

print "<h3>$motd</h3>";

?>
<br/>
<?

# Koliko bodova je student ukupno osvojio?
$bodova = 0;

# Koliko bodova je student teoretski mogao osvojiti?
$mogucih = 0;





////////////////////////////
//  PRISUSTVO NA VJEŽBAMA
////////////////////////////


?><center><table width="90%" cellspacing="0" cellpadding="0"><tr bgcolor="#999999"><td><img src="images/fnord.gif" width="1" height="1"></td></tr></table></center>

<br/>
<?

$q10 = myquery("select id,datum,vrijeme from cas where labgrupa=$labgrupa order by datum");
$casova = 0;
$casovi_zaglavlje = "";
$cas_id_array = array();
while ($r10 = mysql_fetch_row($q10)) {
	$cas_id = $r10[0];
	list ($cas_godina,$cas_mjesec,$cas_dan) = explode("-",$r10[1]);
	list ($cas_sat,$cas_minuta,$cas_sekunda) = explode(":",$r10[2]);
	$casovi_zaglavlje .= "<td>$cas_dan.$cas_mjesec<br>$cas_sat:$cas_minuta</td>\n";
	array_push($cas_id_array,$cas_id);
	$casova++;
}

?><h3>Prisustvo:</h3>

<center><table cellspacing="0" cellpadding="2" border="1">
<tr>
	<?=$casovi_zaglavlje?>
	<td>Bodova</td>
</tr>
<tr><?

	$odsustvo=0;
	foreach ($cas_id_array as $cid) {
		$q11 = myquery("select prisutan from prisustvo where student=$userid and cas=$cid");
		if (mysql_num_rows($q11)>0) {
			if (mysql_result($q11,0,0) == 1) { 
				$prisustvo_ispis .= "<td bgcolor=\"#CCFFCC\" align=\"center\">DA</td>";
			} else { 
				$prisustvo_ispis .= "<td bgcolor=\"#FFCCCC\" align=\"center\">NE</td>";
				$odsustvo++;
			}
		} else {
			$prisustvo_ispis .= "<td bgcolor=\"#FFFFCC\"> / </td>";
		}
	}
	print $prisustvo_ispis;

	if ($odsustvo<=3) $bodova=10; else $bodova=0;
	$mogucih += 10;

?>
	<td><?=$bodova ?></td>
</tr>
</table></center>

<br/>

<center><table width="90%" cellspacing="0" cellpadding="0"><tr bgcolor="#999999"><td><img src="images/fnord.gif" width="1" height="1"></td></tr></table></center>

<br/>
<?






////////////////////////////
//  ZADAĆE
////////////////////////////



?><table width="100%" border="0"><tr><td valign="top">
<h3>Zadaće:</h3>

<p>Za ponovno slanje zadatka, kliknite na sličicu u tabeli dolje.</p>
</td>
<td valign="top" align="center">
<table width="350" border="1" cellspacing="0" cellpadding="5" bgcolor="#EEEEEE"><tr><td>
<table border="0" cellspacing="2" cellpadding="0"><tr><td>
<b>LEGENDA:</b></td></tr>
<tr><td><img src="images/zad_preg.png" width="16" height="16" border="0" align="center"> Pregled u toku</td></tr>
<tr><td><img src="images/zad_bug.png" width="16" height="16" border="0" align="center"> Bug u programu, pošaljite ponovo</td></tr>
<tr><td><img src="images/zad_copy.png" width="16" height="16" border="0" align="center"> Zadatak prepisan, pošaljite ponovo</td></tr>
<tr><td><img src="images/zad_ok.png" width="16" height="16" border="0" align="center"> Zadatak pregledan, bodovi su navedeni pored</td></tr></table>

</td></tr></table>

</td></tr></table>

<br/><br/>

<center><table cellspacing="0" cellpadding="2" border="1">
<tr><td>&nbsp;</td>

<?


# Zaglavlje tabele - potreban nam je max. broj zadataka u zadaci

$q20 = myquery("select zadataka from zadaca where predmet=$predmet_id order by zadataka desc limit 1");
$broj_zadataka = mysql_result($q20,0,0);
for ($i=1;$i<=$broj_zadataka;$i++) {
	?><td>Zadatak <?=$i?>.</td><?
}

?>
<td><b>Ukupno bodova</b></td>
<td>&nbsp;</td>
</tr>
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


// Status ikone:
$stat_icon = array("zad_bug", "zad_preg", "zad_copy", "zad_bug", "zad_preg", "zad_ok");
$stat_tekst = array("Bug u programu", "Pregled u toku", "Zadaća prepisana", "Bug u programu", "Pregled u toku", "Zadaća OK");


$bodova_sve_zadace=0;

$q21 = myquery("select id,naziv,bodova from zadaca where predmet=$predmet_id");
while ($r21 = mysql_fetch_row($q21)) {
	$zadaca = $r21[0];
	$mogucih += $r21[2];
	?><tr>
	<td><?=$r21[1]?></td><?
	$bodova_zadaca = 0;

	for ($zadatak=1;$zadatak<=$broj_zadataka;$zadatak++) {
		// Uzmi samo rjesenje sa zadnjim IDom
		$q22 = myquery("select status,bodova from zadatak where student=$userid and zadaca=$zadaca and redni_broj=$zadatak order by id desc limit 1");
		if (mysql_num_rows($q22)<1) {
			?><td>&nbsp;</td><?
		} else {
			$status = mysql_result($q22,0,0);
			$bodova_zadatak = mysql_result($q22,0,1);
			$bodova_zadaca += $bodova_zadatak;
			?><td><a href="student.php?sta=zadaca&zadaca=<?=$zadaca?>&zadatak=<?=$zadatak?>&labgrupa=<?=$labgrupa?>&force_userid=<?=$force_userid?>"><img src="images/<?=$stat_icon[$status]?>.png" width="16" height="16" border="0" align="center" title="<?=$stat_tekst[$status]?>" alt="<?=$stat_tekst[$status]?>"> <?=$bodova_zadatak?></a></td><?
		}
	}
	?><td><?=$bodova_zadaca?></td><td><a href="student.php?sta=pdf&zadaca=<?=$zadaca?>&labgrupa=<?=$labgrupa?>&force_userid=<?=$force_userid?>"><img src="images/acroread.png" border="0"></a></td></tr><?
	$bodova_sve_zadace += $bodova_zadaca;
}


// Ukupno bodova za studenta
 
$bodova += $bodova_sve_zadace;

?>
<tr><td colspan="<?=$broj_zadataka+1?>" align="right">UKUPNO: </td>
<td><?=$bodova_sve_zadace?></td><td>&nbsp;</td></tr>
</table></center>

<br/>

<center><table width="90%" cellspacing="0" cellpadding="0"><tr bgcolor="#999999"><td><img src="images/fnord.gif" width="1" height="1"></td></tr></table></center>

<br/>
<?






////////////////////////////
//  ISPITI
////////////////////////////


?><h3>Ispiti:</h3>


<?
	

$q30 = myquery("select i.id,i.naziv,UNIX_TIMESTAMP(i.datum),t.naziv,t.id from ispit as i, tipispita as t where i.predmet=$predmet_id and i.tipispita=t.id order by i.datum,i.tipispita");
if (mysql_num_rows($q30) == 0) {
	print "<p>Nije bilo parcijalnih ispita.</p>";
}

$brispita=$polozenih=0; 
$ispit_ocjene = array();
$ispit_ocjene[1] = $ispit_ocjene[2] = $ispit_ocjene[3] = -1;

while ($r30 = mysql_fetch_row($q30)) {
	$brispita++;

	$q31 = myquery("select ocjena from ispitocjene where ispit=$r30[0] and student=$userid");
	if (mysql_num_rows($q31)>0) {
		if (($ocjena = mysql_result($q31,0,0)) == -1) {
//			if ($stud_spol=="a") print "Nije izašla"; else print "Nije izašao";
		} else {
			?><p><?=$r30[3]?> (<?=date("d. m. Y.",$r30[2])?>): <b><?
			print "$ocjena bodova";
			if ($ocjena>$ispit_ocjene[$r30[4]]) $ispit_ocjene[$r30[4]]=$ocjena;
		}
//	} else {
//		if ($stud_spol=="a") print "Nije izašla"; else print "Nije izašao";
//		$mogucih+=20;
	}
//	$mogucih+=20;
	print "</b></p>";
}

if ($ispit_ocjene[3]!=-1 && $ispit_ocjene[3] > ($ispit_ocjene[1]+$ispit_ocjene[2])) {
	$bodova += $ispit_ocjene[3];
	$mogucih += 40;
} else {
	if ($ispit_ocjene[1]!=-1) { 
		$bodova += $ispit_ocjene[1];
		$mogucih += 20;
	}
	if ($ispit_ocjene[2]!=-1) { 
		$bodova += $ispit_ocjene[2];
		$mogucih += 20;
	}
}



////////////////////////////
//  KONAČNI STATUS
////////////////////////////


?>

<br/>

<center><table width="90%" cellspacing="0" cellpadding="0"><tr bgcolor="#999999"><td><img src="images/fnord.gif" width="1" height="1"></td></tr></table></center>

<br/>

<? 

if ($mogucih>0) $procent = round(($bodova/$mogucih)*100); else $procent=0; 

if ($pao1 == 0 && $pao2 == 0) {
	if ($bodova>=40) {
		$thecolor="#CCFFCC";
		$theletter="U";
		$thetext="Student se poziva na usmeni ispit";
	} else {
		$thecolor="#FFCCFF";
		$theletter="??";
		$thetext="Student može ostvariti pravo na usmeni ispit";
	}
} elseif ($bodova<20) {
	$thecolor="#FFCCCC";
	$theletter="/";
	$thetext="Student još uvijek nije skupio 20 bodova potrebnih za popravni ispit";
} elseif ($pao1 == 0 && $pao2 == 1) {
	$thecolor="#FFFFCC";
	$theletter="P2";
	$thetext="Popravni ispit (I parcijalni)";
} elseif ($pao2 == 0 && $pao1 == 1) {
	$thecolor="#FFFFCC";
	$theletter="P1";
	$thetext="Popravni ispit (II parcijalni)";
} else {
	$thecolor="#FFEECC";
	$theletter="P0";
	$thetext="Popravni ispit (integralno)";
}

?>

<h3>Osvojeno <?=$bodova?> bodova od <?=$mogucih?> mogućih (<?=$procent?> %)</h3>

<!--center><table cellspacing="0" cellpadding="0">
<tr>
	<td>
	<table width="50" height="50" cellspacing="0" cellpadding="0" border="1"><tr>
	<td align="center" valign="center" bgcolor="<?=$thecolor?>"><?=$theletter?></td>
	</tr></table>
	</td>
	<td>&nbsp;&nbsp;&nbsp;</td>
	<td valign="center"><?=$thetext?></td>
</tr></table></center-->


<?

}

?>
