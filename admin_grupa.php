<?

// v2.9.3.1 (2007/03/11) + popravka interakcije u FF, redni brojevi studenata, input validation
// v2.9.3.2 (2007/03/19) + zabrana ulaska u grupe za koje nema dozvolu
// v2.9.3.3 (2007/04/08) + polje ocjena je izbaceno iz tabele prisustvo, pa da usutkamo warningse u logu
// v3.0.0.0 (2007/04/09) + Release
// v3.0.0.1 (2007/04/27) + Kreiranje zadataka iz admina
// v3.0.0.2 (2007/05/04) + Optimizacija prikaza, čišćenje komentara i sl.
// v3.0.1.0 (2007/06/12) + Release
// v3.0.1.1 (2007/09/20) + Resize popup prozora za nove sadrzaje, dodatni parametri za FF 2.0
// v3.0.1.2 (2007/09/25) + Prelazak na novu schemu tabele ispita (za sada su moguca samo 2 parcijalna), horizontalni scroll po potrebi, ukinuta polja sa statusom/legendom


function admin_grupa() {

global $userid;



// ------- ULAZNI PARAMETRI

$grupa_id = intval($_GET['id']); if ($grupa_id<1) { $grupa_id = intval($_POST['id']); }
logthis("Admin grupa $grupa_id (login $userid)");

$akcija = $_GET['akcija']; if (!$akcija) { $akcija = $_POST['akcija']; }

$kreiranje = intval($_GET['kreiranje']);



// Određujemo predmet za labgrupu

$q500 = myquery("select predmet from labgrupa where id=$grupa_id");
if (mysql_num_rows($q500)<1) {
	niceerror("Nemate pravo ulaska u ovu grupu!");
	return;
} 
$predmet_id = mysql_result($q500,0,0);


// Da li korisnik ima pravo ući u grupu?

$q501 = myquery("select siteadmin from nastavnik where id=$userid");
if (mysql_num_rows($q501)<1) {
	niceerror("Nemate pravo ulaska u ovu grupu!");
	return;
} 
if (mysql_result($q501,0,0) < 1) {
	$q502 = myquery("select np.admin from nastavnik_predmet as np,labgrupa where np.nastavnik=$userid and np.predmet=labgrupa.predmet and labgrupa.id=$grupa_id");
	if (mysql_num_rows($q502)<1) {
		niceerror("Nemate pravo ulaska u ovu grupu!");
		return;
	} 
	$q503 = myquery("select ogranicenje.labgrupa from ogranicenje, labgrupa where ogranicenje.nastavnik=$userid and ogranicenje.labgrupa=labgrupa.id and labgrupa.predmet=$predmet_id");
	if (mysql_num_rows($q503)>0) {
		$nasao=0;
		while ($r503 = mysql_fetch_row($q503)) {
			if ($r503[0] == $grupa_id) { $nasao=1; break; }
		}
		if ($nasao == 0) {
			niceerror("Nemate pravo ulaska u ovu grupu!");
			return;
		}
	}
}



// ------- AKCIJE

// Dodavanje casa

if ($akcija == 'dodajcas') {
	$datum = intval($_POST['godina'])."-".intval($_POST['mjesec'])."-".intval($_POST['dan']);
	$vrijeme = my_escape($_POST['vrijeme']);
	$predavanje = intval($_POST['predavanje']);

	$q200 = myquery("insert into cas set datum='$datum', vrijeme='$vrijeme', labgrupa=$grupa_id, nastavnik=$userid, predavanje=$predavanje");
	$q201 = myquery("select id from cas where datum='$datum' and vrijeme='$vrijeme' and labgrupa=$grupa_id");
	$cas_id = mysql_result($q201,0,0);

	// dodajemo u bazu default podatke za prisustvo i ocjene

	$q202 = myquery("select student from student_labgrupa where labgrupa=$grupa_id");
	while ($r202 = mysql_fetch_row($q202)) {
		$stud_id = $r202[0];
		$prisustvo = intval($_POST['prisustvo']);
		$q203 = mysql_query("insert into prisustvo set student=$stud_id, cas=$cas_id, prisutan=$prisustvo");
	}
}


// Brisanje casa

if ($akcija == 'brisicas') {
	$cas_id = intval($_GET['cas']); if ($cas_id<1) { $cas_id = intval($_POST['cas']); }
	$q204 = myquery("delete from prisustvo where cas=$cas_id");
	$q204 = myquery("delete from cas where id=$cas_id");
}




// ------- ZAGLAVLJE


print '<p><a href="qwerty.php">Nazad na početnu stranu</a></p>'."\n";


// Naslov

$q1 = myquery("select naziv,predmet from labgrupa where id=$grupa_id");
if (mysql_num_rows($q1)<1) { niceerror("Izabrana je nepostojeća grupa"); return; }
$naziv = mysql_result($q1,0,0);
$predmet = mysql_result($q1,0,1);

$q2 = myquery("select naziv from predmet where id=$predmet");
$pime = mysql_result($q2,0,0);

print "<center><h1>$pime - $naziv</h1></center>";



// Ima li ikoga u grupi?

$q9 = myquery("select count(student) from student_labgrupa where labgrupa=$grupa_id");
if (mysql_result($q9,0,0)<1) {
	print "<p>Nijedan student nije u grupi</p>\n";
	return;
} 



// JavaScript za prikaz zadaće

?>
<script language="JavaScript">
function openzadaca(student,zadaca,zadatak) {
	var url='qwerty.php?sta=pregled&student='+student+'&zadaca='+zadaca+'&zadatak='+zadatak;
//	alert(url);
	window.open(url,'Ispravak zadace','width=600,height=600,scrollbars=yes');
}

// FF ne podrzava direktan poziv window.open() iz eventa 
function firefoxopen(p1,p2,p3) { 
	window.open(p1,p2,p3);
}
</script>
<?



// ------- SPISAK NEPREGLEDANIH ZADAĆA

// FIXME: subqueries
// Zahtijeva MySQL 4.1 ili noviji (dakle, ne radi na Debian Sarge :( )

/* $q100 = myquery(
"SELECT zadatak.zadaca, zadatak.redni_broj, zadatak.student, student.ime, student.prezime, zadaca.naziv
FROM zadatak, student, student_labgrupa, zadaca
WHERE zadatak.student=student.id AND zadatak.student=student_labgrupa.student 
AND zadatak.status=4 AND student_labgrupa.labgrupa=$grupa_id AND zadatak.zadaca=zadaca.id");
if (mysql_num_rows($q100)>0) {
	print "<h2>Nove zadaće za pregled:</h2>\n<ul>";
	while ($r100 = mysql_fetch_row($q100)) {
		print '<li><a href="#" onclick="javascript:openzadaca(\''.$r100[2].'\',\''.$r100[0].'\',\''.$r100[1].'\')">'.$r100[3]." ".$r100[4]." - Zadaća ".$r100[0].", zadatak ".$r100[1]."</a></li>";
	}
	print "</ul>\n";
}*/



$q100 = myquery(
"SELECT zadatak.zadaca, zadatak.redni_broj, zadatak.student, student.ime, student.prezime, zadatak.status, zadaca.naziv
FROM zadatak, student, student_labgrupa, zadaca
WHERE zadatak.student=student.id AND zadatak.student=student_labgrupa.student 
AND student_labgrupa.labgrupa=$grupa_id AND zadatak.zadaca=zadaca.id AND zadaca.predmet=$predmet
ORDER BY zadatak.zadaca, zadatak.student, zadatak.redni_broj, zadatak.id DESC");
$mzadaca=0; $mzadatak=0; $mstudent=0; $print="";
while ($r100 = mysql_fetch_row($q100)) {
	if ($r100[0]==$mzadaca && $r100[1]==$mzadatak && $r100[2]==$mstudent) continue;
	$mzadaca=$r100[0]; $mzadatak=$r100[1]; $mstudent=$r100[2];
	if ($r100[5]!=4) continue;
	$print .= '<li><a href="#" onclick="javascript:openzadaca(\''.$r100[2].'\',\''.$r100[0].'\',\''.$r100[1].'\')">'.$r100[3]." ".$r100[4]." - ".$r100[6].", zadatak ".$r100[1]."</a></li>";
}
if ($print != "") print "<h2>Nove zadaće za pregled:</h2>\n<ul>$print</ul>";





// ------- FORMA ZA NOVI ČAS


$dan=date("d"); $mjesec=date("m"); $godina=date("Y"); 
$vrijeme=date("H:i");


?>
<table border="0" width="100%"><tr><td valign="top" width="50%">&nbsp;</td>
<td valign="top" width="50%">
	Registrujte novi čas:<br/>
	<form action="qwerty.php" method="POST">
	<input type="hidden" name="sta" value="grupa">
	<input type="hidden" name="akcija" value="dodajcas">
	<input type="hidden" name="id" value="<?=$grupa_id?>">

	Datum:
	<select name="dan"><?
	for ($i=1; $i<=31; $i++) {
		print "<option value=\"$i\"";
		if ($i==$dan) print " selected";
		print ">$i</option>";
	}
	?></select>&nbsp;&nbsp;
	<select name="mjesec"><?
	for ($i=1; $i<=12; $i++) {
		print "<option value=\"$i\"";
		if ($i==$mjesec) print " selected";
		print ">$i</option>";
	}
	?></select>&nbsp;&nbsp;
	<select name="godina"><?
	for ($i=2005; $i<=2010; $i++) {
		print "<option value=\"$i\"";
		if ($i==$godina) print " selected";
		print ">$i</option>";
	}
	?></select><br/>
	Vrijeme: <input type="text" size="10" name="vrijeme" value="<?=$vrijeme?>">
	<input type="submit" value="Registruj"><br/><br/>

	<input type="radio" name="prisustvo" value="1" CHECKED>Svi prisutni
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<input type="radio" name="prisustvo" value="0">Svi odustni

</form>
</td></tr></table>
<?




// ------- TABLICA GRUPE


// JavaScript koji zamjenjuje AJAX koristeći hidden IFRAME 
// (tzv. AJAH)

?>
<font color="#FF0000"><b><div id="razmjena-info">&nbsp;</div></b></font>
<iframe height="0" width="0" frameborder="0" name="razmjena" id="razmjena"></iframe>
<script language="JavaScript">
var sending=false;

function stoploading() {
	// Dodati error checking
	sending=false;
	document.getElementById("razmjena-info").innerHTML="&nbsp;";
}

function prisustvo(student,cas) {
	var val = document.getElementById("danetekst-"+student+"-"+cas).innerHTML;
	if (sending) return; // semawhore
	sending=true;
	if (val == "DA") {
		document.getElementById("dane-"+student+"-"+cas).style.background = "#FFCCCC";
		document.getElementById("danetekst-"+student+"-"+cas).innerHTML = "NE";
		var prisutan=1;
	} else {
		document.getElementById("dane-"+student+"-"+cas).style.background="#CCFFCC";
		document.getElementById("danetekst-"+student+"-"+cas).innerHTML = "DA";
		var prisutan=2;
	}
	
	document.getElementById("razmjena-info").innerHTML="Šaljem podatke na server...";
	frames['razmjena'].location.replace ( "qwerty.php?sta=ajah&stasta=prisustvo&student="+student+"&cas="+cas+"&prisutan="+prisutan);
}
</script>

<?


$minw = 0; // minimalna sirina tabele


// Zaglavlje prisustvo

$q10 = myquery("select id,datum,vrijeme from cas where labgrupa=$grupa_id order by datum");
$casova = 0;
$prisustvo_zaglavlje = "";

while ($r10 = mysql_fetch_row($q10)) {
	$cas_id = $r10[0];
	list ($cas_godina,$cas_mjesec,$cas_dan) = explode("-",$r10[1]);
	list ($cas_sat,$cas_minuta,$cas_sekunda) = explode(":",$r10[2]);
	$prisustvo_zaglavlje .= "<td align=\"center\">$cas_dan.$cas_mjesec<br/>$cas_sat:$cas_minuta";
	$prisustvo_zaglavlje .= '<br/><a href="qwerty.php?sta=grupa&id='.$grupa_id.'&akcija=brisicas&cas='.$cas_id.'"><img src="images/b_drop.png" border="0"></a>';
	$prisustvo_zaglavlje .= "</td>\n";
	$cas_id_array[] = $cas_id;
	$casova++;
	$minw += 40;
}

if ($prisustvo_zaglavlje == "") $prisustvo_zaglavlje = "<td>&nbsp;</td>";


// Zaglavlje zadaće

$zadace_zaglavlje = "";

$q11 = myquery("select id,naziv,zadataka from zadaca where predmet=$predmet order by id");
$brzadaca = mysql_num_rows($q11);
if ($brzadaca > 0) { 
	while ($r11 = mysql_fetch_row($q11)) {
		$zadace_zaglavlje .= "<td>$r11[1]</td>\n";
		$zad_id_array[] = $r11[0];
		$zad_brz_array[$r11[0]] = $r11[2];

		$minw += 80;
	}
}


// Zaglavlje ispiti

/*$ispit_zaglavlje = "";

$q12 = myquery("SELECT ispit.id,ispit.naziv 
FROM ispitocjene, student_labgrupa, ispit 
WHERE ispitocjene.student=student_labgrupa.student AND student_labgrupa.labgrupa=$grupa_id AND ispitocjene.ispit=ispit.id AND ispit.predmet=$predmet 
GROUP BY ispitocjene.ispit ORDER BY ispitocjene.ispit");
$brispita = mysql_num_rows($q12);
if ($brispita > 0) {
	while ($r12 = mysql_fetch_row($q12)) {
		$ispit_zaglavlje .= "<td>$r12[1]</td>\n";
		$ispit_id_array[] = $r12[0];
	}
}*/
if ($casova==0) $casova=1;

$minw += (2*40); // parcijalni ispiti
$minw += 70; // ukupno
$minw += 45; // broj indexa
$minw += 100; // ime i prezime

?>
<table cellspacing="0" cellpadding="2" border="1" <? if ($minw>800) print "width=\"$minw\""; ?>>
<tr>
	<td rowspan="2" align="center" valign="center">Ime i prezime</td>
	<td rowspan="2" align="center" valign="center">Broj indexa</td>
	<td align="center" colspan="<?=($casova+1)?>">Prisustvo</td>
	<? if ($brzadaca > 0) { 
?><td align="center" colspan="<?=$brzadaca?>">Ocjene iz zadaća</td>
	<? } ?>
	<td align="center" colspan="2">Ispiti</td>
	<td align="center" valign="center" rowspan="2">&nbsp;&nbsp;<b>UKUPNO</b>&nbsp;&nbsp;</td>
</tr>
<tr>
	<?=$prisustvo_zaglavlje?><td>BOD.</td>
	<?=$zadace_zaglavlje?>
	<td>I parc.</td><td>II parc.</td>
</tr>
<?


// CACHE REZULTATA ZADAĆA
$zadace_statusi=array();
$zadace_bodovi=array();
$q12a = myquery("SELECT z.zadaca,z.redni_broj,z.student,z.status,z.bodova
FROM zadatak as z,student_labgrupa as sl 
WHERE z.student=sl.student and sl.labgrupa=$grupa_id
ORDER BY id");
while ($r100 = mysql_fetch_row($q12a)) {
	// Slog sa najnovijim IDom se smatra mjerodavnim
	// Ostali su u bazi radi historije
	$zadace_bodovi[$r100[0]][$r100[1]][$r100[2]]=$r100[4];
	$zadace_statusi[$r100[0]][$r100[1]][$r100[2]]=$r100[3]+1;
	// Dodajemo 1 na status kako bismo kasnije mogli znati da li 
	// je vrijednost niza definisana ili ne.
	// undef ne radi :(
}



// Ikone i statusi za zadaće
$stat_icon = array("zad_bug", "zad_cekaj", "zad_copy", "zad_bug", "zad_preg", "zad_ok");
$stat_tekst = array("Bug u programu", "Automatsko testiranje u toku", "Zadaća prepisana", "Bug u programu", "Potrebno pregledati", "Zadaća OK");



// Glavna petlja - studenti

$q13 = myquery("select student.id,student.ime,student.prezime,student.brindexa from student,student_labgrupa where student.id=student_labgrupa.student and student_labgrupa.labgrupa=$grupa_id");


$imeprezime = array();
$brind = array();
while ($r13 = mysql_fetch_row($q13)) {
	$stud_id = $r13[0];
	$stud_ime = $r13[1];
	$stud_prezime = $r13[2];
	$stud_brind = $r13[3];
	$imeprezime[$stud_id] = "$stud_prezime $stud_ime";
	$brind[$stud_id] = $stud_brind;
}
uasort($imeprezime,"bssort"); // bssort - bosanski jezik
$redni_broj=0;
foreach ($imeprezime as $stud_id => $stud_imepr) {

	$rednibroj++;
?>
<tr>
	<td><?=$rednibroj?>. <a href="javascript:firefoxopen('qwerty.php?sta=student-izmjena&student=<?=$stud_id?>&predmet=<?=$predmet?>','Podaci o studentu','width=320,height=320,status=0,toolbar=0,resizable=1,location=0,menubar=0,scrollbars=0');"><?=$stud_imepr?></a></td>
	<td><?=$brind[$stud_id]?></td>
<?

	$prisustvo_ispis=$zadace_ispis=$ispiti_ispis="";
	$bodova=0;
	$mogucih=0;

	// Ispis prisustvo

	if (count($cas_id_array)==0) $prisustvo_ispis = "<td>&nbsp;</td>";
	$odsustvo=0;
	foreach ($cas_id_array as $cid) {
		$q14 = myquery("select prisutan from prisustvo where student=$stud_id and cas=$cid");
		if (mysql_num_rows($q14)>0) {
			if (mysql_result($q14,0,0) == 1) { 
				$prisustvo_ispis .= "<td bgcolor=\"#CCFFCC\" align=\"center\" id=\"dane-".$stud_id."-".$cid."\" onclick=\"javascript:prisustvo(".$stud_id.",".$cid.")\"><div id=\"danetekst-".$stud_id."-".$cid."\">DA</div></td>";
			} else { 
				$prisustvo_ispis .= "<td bgcolor=\"#FFCCCC\" align=\"center\" id=\"dane-".$stud_id."-".$cid."\" onclick=\"javascript:prisustvo(".$stud_id.",".$cid.")\"><div id=\"danetekst-".$stud_id."-".$cid."\">NE</div></td>";
				$odsustvo++;
			}
			//$ocj = mysql_result($q14,0,1);
		} else {
			$prisustvo_ispis .= "<td bgcolor=\"#FFFFCC\" align=\"center\" id=\"dane-".$stud_id."-".$cid."\" onclick=\"javascript:prisustvo(".$stud_id.",".$cid.")\"><div id=\"danetekst-".$stud_id."-".$cid."\"> / </div></td>";
		}
	}
	if ($odsustvo<=3) {
		$prisustvo_ispis .= "<td>10</td>";
		$bodova+=10;
	} else {
		$prisustvo_ispis .= "<td>0</td>";
	}
	$mogucih+=10;

	foreach ($zad_id_array as $zid) {
		$zadace_ispis .= "<td>\n";
		// FIXME: subqueries
		//$q15a = myquery ("select redni_broj from zadatak where zadaca=$zid and student=$stud_id order by redni_broj group by redni_broj");

		for ($i=1; $i<=$zad_brz_array[$zid]; $i++) {
			$status = $zadace_statusi[$zid][$i][$stud_id];
			if ($status == 0) { // Zadatak nije poslan
				if ($kreiranje>0) {
					$zadace_ispis .= "<a href=\"javascript:openzadaca('".$stud_id."', '".$zid."', '".$i."')\"><img src=\"images/idea.png\" width=\"16\" height=\"16\" border=\"0\" align=\"center\" title=\"".$title."\" alt=\"".$title."\"></a>&nbsp;";
				}
			} else {
				$status--; // Bio uvećan za 1 
				$icon = $stat_icon[$status];
				$title = $stat_tekst[$status];
				$zb = $zadace_bodovi[$zid][$i][$stud_id];
				$zadace_ispis .= "<a href=\"javascript:openzadaca('".$stud_id."', '".$zid."', '".$i."')\"><img src=\"images/".$icon.".png\" width=\"16\" height=\"16\" border=\"0\" align=\"center\" title=\"".$title."\" alt=\"".$title."\"> ".$zb."</a>";
				$bodova += $zb;
			}
		}
		$zadace_ispis .= "&nbsp;</td>\n";
		$mogucih += 2;
	}

/*	$i=$pao1=$pao2=0;
	foreach ($ispit_id_array as $pid) {
		$i++;
		$q16 = myquery("select ocjena,ocjena2 from ispitocjene where student=$stud_id and ispit=$pid");
		if (mysql_num_rows($q16)>0) {
			if (($ocjena = mysql_result($q16,0,0)) == -1) {
				$ispiti_ispis .= "<td> / </td>";
				if ($i==1) { $pao1 = 1; } else { $pao2 = 1; }
			} else {
				$ispiti_ispis .= "<td> $ocjena </td>";
				if ($ocjena<10) {
					if ($i==1) { $pao1 = 1; } else { $pao2 = 1; }
				}	
				$bodova = $bodova + $ocjena;
			}
		} else {
			$ispiti_ispis .= "<td> / </td>";
			if ($i==1) { $pao1 = 1; } else { $pao2 = 1; }
		}		if ($r16[0] != -1 && $r16[0]>$max1) $max1=$r16[0];

		$mogucih+=20;
	}*/
	$max1 = $max2 = "/";
	$q16 = myquery("select io.ocjena,io.ocjena2 from ispitocjene as io, ispit as i where io.student=$stud_id and io.ispit=i.id and i.predmet=$predmet order by i.id");
	while ($r16 = mysql_fetch_row($q16)) {
		if ($r16[0] != -1 && $r16[0]>=$max1) $max1=$r16[0];
		if ($r16[1] != -1 && $r16[1]>=$max2) $max2=$r16[1];
	}
	$bodova += ($max1+$max2);
	$mogucih += 40;
	$ispiti_ispis = "<td>$max1</td><td>$max2</td>";

	if ($mogucih>0) $procent = round(($bodova/$mogucih)*100); else $procent=0;
	
	$imena[$stud_id]="$stud_prezime $stud_ime";
	$topscore[$stud_id]=$bodova;
/*	if ($pao1 == 0 && $pao2 == 0) {
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
	}*/

?>
	<?=$prisustvo_ispis?>
	<?=$zadace_ispis?>
	<?=$ispiti_ispis?>
	<td align="center"><?=$bodova?> (<?=$procent?>%)</td>
	<? /*<td bgcolor="<?=$thecolor?>"><?=$theletter?></td>*/ ?>
<?

}


?>
</tr>
</table>
<p><?
	if ($kreiranje>0) {
		$k=str_replace("&kreiranje=1","",genuri());
?><a href="<?=$k?>">Sakrij dugmad za kreiranje zadataka</a><?
	} else {
?><a href="<?=genuri()?>&kreiranje=1">Prikaži dugmad za kreiranje zadataka</a><?
	}
?></p>


<p>&nbsp;</p>
<?




}

?>
