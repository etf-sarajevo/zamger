<?

// v2.9.3.1 (2007/03/11) + popravka interakcije u FF, redni brojevi studenata, input validation
// v2.9.3.2 (2007/03/19) + zabrana ulaska u grupe za koje nema dozvolu
// v2.9.3.3 (2007/04/08) + polje ocjena je izbaceno iz tabele prisustvo, pa da usutkamo warningse u logu
// v3.0.0.0 (2007/04/09) + Release
// v3.0.0.1 (2007/04/27) + Kreiranje zadataka iz admina


function admin_grupa() {

global $userid;


print '<p><a href="qwerty.php">Nazad na početnu stranu</a></p>'."\n";


// Ulazni parametri

$grupa_id = intval($_GET['id']); if ($grupa_id<1) { $grupa_id = intval($_POST['id']); }
logthis("Admin grupa $grupa_id (login $userid)");

$akcija = $_GET['akcija']; if (!$akcija) { $akcija = $_POST['akcija']; }

$kreiranje = intval($_GET['kreiranje']);


// Predmet

$q500 = myquery("select predmet from labgrupa where id=$grupa_id");
if (mysql_num_rows($q500)<1) {
	niceerror("Nemate pravo ulaska u ovu grupu!");
	return;
} 
$predmet_id = mysql_result($q500,0,0);


// Ima li pravo ući u grupu?
$q501 = myquery("select siteadmin from nastavnik where id=$userid");
if (mysql_num_rows($q501)<1) {
	niceerror("Nemate pravo ulaska u ovu grupu!");
	return;
} 
if (mysql_result($q501,0,0) != 1) {
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

# Dodavanje casa

if ($akcija == 'dodajcas') {
	$datum = intval($_POST['godina'])."-".intval($_POST['mjesec'])."-".intval($_POST['dan']);
	$vrijeme = my_escape($_POST['vrijeme']);

	$q200 = myquery("insert into cas set datum='$datum', vrijeme='$vrijeme', labgrupa=$grupa_id, nastavnik=$userid");
	$q201 = myquery("select id from cas where datum='$datum' and vrijeme='$vrijeme' and labgrupa=$grupa_id");
	$cas_id = mysql_result($q201,0,0);

	# unos prisustva i ocjena

	$q202 = myquery("select student from student_labgrupa where labgrupa=$grupa_id");
	while ($r202 = mysql_fetch_row($q202)) {
		$stud_id = $r202[0];
		$q203 = mysql_query("insert into prisustvo set student=$stud_id, cas=$cas_id, prisutan=1");
	}
}


# Brisanje casa

if ($akcija == 'brisicas') {
	$cas_id = intval($_GET['cas']); if ($cas_id<1) { $cas_id = intval($_POST['cas']); }
	$q204 = myquery("delete from prisustvo where cas=$cas_id");
	$q204 = myquery("delete from cas where id=$cas_id");
}




# Naslov

$q1 = myquery("select naziv,predmet from labgrupa where id=$grupa_id");
if (mysql_num_rows($q1)<1) { niceerror("Izabrana je nepostojeća grupa"); return; }
$naziv = mysql_result($q1,0,0);
$predmet = mysql_result($q1,0,1);

$q2 = myquery("select naziv from predmet where id=$predmet");
$pime = mysql_result($q2,0,0);

print "<center><h1>$pime - $naziv</h1></center>";



# Ima li iko u grupi?

$q9 = myquery("select count(student) from student_labgrupa where labgrupa=$grupa_id");
if (mysql_result($q9,0,0)<1) {
	print "<p>Nijedan student nije u grupi</p>\n";
	return;
} 


# --------------------
# Zadace za pregled

?>
<table border="0" width="100%"><tr><td valign="top" width="50%">

<script language="JavaScript">
function openzadaca(student,zadaca,zadatak) {
	var url='qwerty.php?sta=pregled&student='+student+'&zadaca='+zadaca+'&zadatak='+zadatak;
//	alert(url);
	window.open(url,'Ispravak zadace','width=600,height=600,scrollbars=yes');
}
function firefoxopen(p1,p2,p3) {
	window.open(p1,p2,p3);
}
</script>
<?

// FIXME: subqueries

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


# --------------------
# Novi cas


$dan=date("d"); $mjesec=date("m"); $godina=date("Y"); 
$vrijeme=date("H:i");


?></td><td valign="top" width="50%">
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


<input type="submit" value="Registruj">


</form>
</td></tr></table>
<?



# --------------------
# JavaScript koji zamjenjuje AJAX

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



# --------------------
# Tablica grupe...


$q10 = myquery("select id,datum,vrijeme from cas where labgrupa=$grupa_id order by datum");
$casova = 0;
$casovi_zaglavlje = "";
$casovi_zaglavlje = $ocjene_zaglavlje = $ispit_zaglavlje = "";

while ($r10 = mysql_fetch_row($q10)) {
	$cas_id = $r10[0];
	list ($cas_godina,$cas_mjesec,$cas_dan) = explode("-",$r10[1]);
	list ($cas_sat,$cas_minuta,$cas_sekunda) = explode(":",$r10[2]);
	$casovi_zaglavlje .= "<td align=\"center\">$cas_dan.$cas_mjesec<br/>$cas_sat:$cas_minuta";
	$casovi_zaglavlje .= '<br/><a href="qwerty.php?sta=grupa&id='.$grupa_id.'&akcija=brisicas&cas='.$cas_id.'"><img src="images/b_drop.png" border="0"></a>';
	$casovi_zaglavlje .= "</td>\n";
	$cas_id_array[] = $cas_id;
	$casova++;
}

if ($casovi_zaglavlje == "") $casovi_zaglavlje = "<td>&nbsp;</td>";


$q11 = myquery("select id,naziv,zadataka from zadaca where predmet=$predmet order by id");
$brzadaca = mysql_num_rows($q11);
if ($brzadaca > 0) { 
	while ($r11 = mysql_fetch_row($q11)) {
		$zadace_zaglavlje .= "<td>$r11[1]</td>\n";
		$zad_id_array[] = $r11[0];
		$zad_brz_array[$r11[0]] = $r11[2];

	}
}

$q12 = myquery("SELECT ispit.id,ispit.naziv 
FROM ispitocjene, student_labgrupa, ispit 
WHERE ispitocjene.student=student_labgrupa.student AND student_labgrupa.labgrupa=$grupa_id AND ispitocjene.ispit=ispit.id AND ispit.predmet=$predmet 
GROUP BY ispitocjene.ispit order by ispitocjene.ispit");
$brispita = mysql_num_rows($q12);
if ($brispita > 0) {
	while ($r12 = mysql_fetch_row($q12)) {
		$ispit_zaglavlje .= "<td>$r12[1]</td>\n";
		$ispit_id_array[] = $r12[0];
	}
}
if ($casova==0) $casova=1;


?>
<table cellspacing="0" cellpadding="2" border="1">
<tr>
	<td rowspan="2" align="center" valign="center">Ime i prezime</td>
	<td rowspan="2" align="center" valign="center">Broj indexa</td>
	<td align="center" colspan="<?=($casova+1)?>">Prisustvo</td>
	<? if ($brzadaca > 0) { 
?><td align="center" colspan="<?=$brzadaca?>">Ocjene iz zadaća</td>
	<? } ?>
	<? if ($brispita > 0) {
?><td align="center" colspan="<?=$brispita?>">Ispiti</td>
	<? } ?>
	<td align="center" valign="center" rowspan="2" colspan="2">&nbsp;&nbsp;<b>UKUPNO</b>&nbsp;&nbsp;</td>
</tr>
<tr>
	<?=$casovi_zaglavlje?><td>BOD.</td>
	<?=$zadace_zaglavlje?>
	<?=$ispit_zaglavlje?>
</tr>
<?

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
uasort($imeprezime,"bssort");
$redni_broj=0;
foreach ($imeprezime as $stud_id => $stud_imepr) {

	$rednibroj++;
?>
<tr>
	<td><?=$rednibroj?>. <a href="javascript:firefoxopen('qwerty.php?sta=student-izmjena&student=<?=$stud_id?>&predmet=<?=$predmet?>','Podaci o studentu','width=300,height=200');"><?=$stud_imepr?></a></td>
	<td><?=$brind[$stud_id]?></td>
<?

	$prisustvo_ispis=$zadace_ispis=$ispiti_ispis="";
	$bodova=0;
	$mogucih=0;

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
			$q15 = myquery ("select status,bodova from zadatak where zadaca=$zid and student=$stud_id and redni_broj=$i order by id desc limit 1");
			if (mysql_num_rows($q15)<1) {
				if ($kreiranje>0) {
					$zadace_ispis .= "<a href=\"javascript:openzadaca('".$stud_id."', '".$zid."', '".$i."')\"><img src=\"images/idea.png\" width=\"16\" height=\"16\" border=\"0\" align=\"center\" title=\"".$title."\" alt=\"".$title."\"></a>&nbsp;";
				}
				//$zadace_ispis .= "&nbsp;";
				continue;
			}

			$status = mysql_result($q15,0,0);
			$zb = mysql_result($q15,0,1);
			
			if ($status == 1) {
				$icon = "zad_cekaj";
				$title = "Automatsko testiranje u toku";
			} else if ($status == 4) {
				$icon = "zad_preg";
				$title = "Potrebno pregledati";
			} else if ($status == 2) { 
				$icon = "zad_copy";
				$title = "Zadaća prepisana";
			} else if ($status == 5) { 
				$icon = "zad_ok";
				$title = "OK";
			} else  { // BUG - 3, 0, 6...
				$icon = "zad_bug";
				$title = "Bug u programu";
			}
			$zadace_ispis .= "<a href=\"javascript:openzadaca('".$stud_id."', '".$zid."', '".$i."')\"><img src=\"images/".$icon.".png\" width=\"16\" height=\"16\" border=\"0\" align=\"center\" title=\"".$title."\" alt=\"".$title."\"> ".$zb."</a>";
			$bodova += $zb;
		}
		$zadace_ispis .= "&nbsp;</td>\n";
		$mogucih += 2;
	}

	$i=$pao1=$pao2=0;
	foreach ($ispit_id_array as $pid) {
		$i++;
		$q16 = myquery("select ocjena from ispitocjene where student=$stud_id and ispit=$pid");
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
		}
		$mogucih+=20;
	}

	if ($mogucih>0) $procent = round(($bodova/$mogucih)*100); else $procent=0;
	
	$imena[$stud_id]="$stud_prezime $stud_ime";
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
	<?=$zadace_ispis?>
	<?=$ispiti_ispis?>
	<td align="center"><?=$bodova?> (<?=$procent?>%)</td>
	<td bgcolor="<?=$thecolor?>"><?=$theletter?></td>
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
