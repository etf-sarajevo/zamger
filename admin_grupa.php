<?


function admin_grupa() {

global $login;

$grupa_id = $_GET['id']; if ($grupa_id<1) { $grupa_id = $_POST['id']; }
//logthis("Admin grupa $grupa_id (login $login)");


# Dodavanje casa

$akcija = $_GET['akcija']; if (!$akcija) { $akcija = $_POST['akcija']; }
if ($akcija == 'dodajcas') {
	$datum = $_POST['godina']."-".$_POST['mjesec']."-".$_POST['dan'];
	$vrijeme = $_POST['vrijeme'];

	$q200 = myquery("insert into casovi set datum='$datum', vrijeme='$vrijeme', grupa=$grupa_id, demonstrator='$login'");
	$q201 = myquery("select id from casovi where datum='$datum' and vrijeme='$vrijeme' and grupa=$grupa_id");
	$cas_id = mysql_result($q201,0,0);

	# unos prisustva i ocjena

	$q202 = myquery("select id from studenti where grupa=$grupa_id");
	while ($r202 = mysql_fetch_row($q202)) {
		$stud_id = $r202[0];
		$q203 = mysql_query("insert into prisustvo set student=$stud_id, cas=$cas_id, prisutan=1");
	}
}


# Brisanje casa

if ($akcija == 'brisicas') {
	$cas_id = $_GET['cas']; if ($cas_id<1) { $cas_id = $_POST['cas']; }
	$q204 = myquery("delete from prisustvo where cas=$cas_id");
	$q204 = myquery("delete from casovi where id=$cas_id");
}




# Naslov

$q1 = myquery("select naziv from grupe where id=$grupa_id");
if (mysql_num_rows($q1)<1) { niceerror("Izabrana je nepostojeća grupa"); return; }
$naziv = mysql_result($q1,0,0);
print "<center><h1>$naziv</h1></center>";



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
</script>
<?

/*$q100 = myquery("select zadace.zadaca,zadace.zadatak,zadace.student,studenti.ime,studenti.prezime,zadace.id from zadace,studenti where zadace.status=4 and zadace.student=studenti.id and studenti.grupa=$grupa_id order by zadace.id");
if (mysql_num_rows($q100)>0) {
	print "<h2>Nove zadaće za pregled:</h2>\n<ul>";
	while ($r100 = mysql_fetch_row($q100)) {
		print '<li><a href="#" onclick="javascript:openzadaca(\''.$r100[2].'\',\''.$r100[0].'\',\''.$r100[1].'\')">'.$r100[3]." ".$r100[4]." - Zadaća ".$r100[0].", zadatak ".$r100[1]."</a></li>";
	}
	print "</ul>\n";
}*/

$print = "";
$q100 = myquery("select zadace.zadaca,zadace.zadatak,zadace.student,studenti.ime,studenti.prezime,zadace.status from zadace,studenti where zadace.student=studenti.id and studenti.grupa=$grupa_id order by zadace.zadaca,zadace.student,zadace.zadatak,zadace.id desc");
$zadaca=0; $zadatak=0; $student=0;
while ($r100 = mysql_fetch_row($q100)) {
	if ($r100[0]==$zadaca && $r100[1]==$zadatak && $r100[2]==$student) continue;
	$zadaca=$r100[0]; $zadatak=$r100[1]; $student=$r100[2];
	if ($r100[5]!=4) continue;
	$print .= '<li><a href="#" onclick="javascript:openzadaca(\''.$r100[2].'\',\''.$r100[0].'\',\''.$r100[1].'\')">'.$r100[3]." ".$r100[4]." - Zadaća ".$r100[0].", zadatak ".$r100[1]."</a></li>";
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
	frames['razmjena'].location.href = "qwerty.php?sta=ajah&stasta=prisustvo&student="+student+"&cas="+cas+"&prisutan="+prisutan;
}
</script>

<?



# --------------------
# Tablica grupe...



$q10 = myquery("select id,datum,vrijeme from casovi where grupa=$grupa_id order by datum");
$casova = 0;
$casovi_zaglavlje = "";
$casovi_zaglavlje = $ocjene_zaglavlje = $parc_zaglavlje = "";
$cas_id_array = $zad_id_array = $par_id_array = array();
while ($r10 = mysql_fetch_row($q10)) {
	$cas_id = $r10[0];
	list ($cas_godina,$cas_mjesec,$cas_dan) = explode("-",$r10[1]);
	list ($cas_sat,$cas_minuta,$cas_sekunda) = explode(":",$r10[2]);
	$casovi_zaglavlje .= "<td align=\"center\">$cas_dan.$cas_mjesec<br/>$cas_sat:$cas_minuta";
	$casovi_zaglavlje .= '<br/><a href="qwerty.php?sta=grupa&id='.$grupa_id.'&akcija=brisicas&cas='.$cas_id.'"><img src="images/b_drop.png" border="0"></a>';
	$casovi_zaglavlje .= "</td>\n";
	array_push($cas_id_array,$cas_id);
	$casova++;
}

if ($casovi_zaglavlje == "") $casovi_zaglavlje = "<td>&nbsp;</td>";


$q11 = myquery("select id from zadace_objavljene order by id");
$brzadaca = mysql_num_rows($q11);
if ($brzadaca == 0) { $brzadaca=1; $zadace_zaglavlje = "<td>&nbsp;</td>"; }
else {
	while ($r11 = mysql_fetch_row($q11)) {
		$vjezba = $r11[0];
		$zadace_zaglavlje .= "<td>$vjezba.</td>\n";
		array_push($zad_id_array,$vjezba);
	}
}

$q12 = myquery("select parcijale.id from parcijale,studenti where parcijale.student=studenti.id and studenti.grupa=$grupa_id group by parcijale.id order by parcijale.id");
$brparc = mysql_num_rows($q12);
if ($brparc == 0) {
	$parc_zaglavlje = "<td>&nbsp;</td>";
	$brparc=1;
} else {
	while ($r12 = mysql_fetch_row($q12)) {
		$parc = $r12[0];
		$parc_zaglavlje .= "<td>$parc.</td>\n";
		array_push($par_id_array,$parc);
	}
}
if ($casova==0) $casova=1;


?>
<table cellspacing="0" cellpadding="2" border="1">
<tr>
	<td rowspan="2" align="center" valign="center">Ime i prezime</td>
	<td rowspan="2" align="center" valign="center">Broj indexa</td>
	<td align="center" colspan="<?=($casova+1)?>">Prisustvo</td>
	<td align="center" colspan="<?=$brzadaca?>">Ocjene iz zadaća</td>
	<td align="center" colspan="<?=$brparc?>">Parcijalni ispiti</td>
	<td align="center" valign="center" rowspan="2" colspan="2">&nbsp;&nbsp;<b>UKUPNO</b>&nbsp;&nbsp;</td>
</tr>
<tr>
	<?=$casovi_zaglavlje?><td>BOD.</td>
	<?=$zadace_zaglavlje?>
	<?=$parc_zaglavlje?>
</tr>
<?

$q13 = myquery("select id,ime,prezime,brindexa from studenti where grupa=$grupa_id");
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
foreach ($imeprezime as $stud_id => $stud_imepr) {

?>
<tr>
	<td><a href="#" onclick="javascript:window.open('qwerty.php?sta=student-izmjena&student=<?=$stud_id?>','Podaci o studentu','width=300,height=200');"><?=$stud_imepr?></a></td>
	<td><?=$brind[$stud_id]?></td>
<?

	$prisustvo_ispis=$zadace_ispis=$parc_ispis="";
	$bodova=0;
	$mogucih=0;

	if (count($cas_id_array)==0) $prisustvo_ispis = "<td>&nbsp;</td>";
	$odsustvo=0;
	foreach ($cas_id_array as $cid) {
		$q14 = myquery("select prisutan,ocjena from prisustvo where student=$stud_id and cas=$cid");
		if (mysql_num_rows($q14)>0) {
			if (mysql_result($q14,0,0) == 1) { 
				$prisustvo_ispis .= "<td bgcolor=\"#CCFFCC\" align=\"center\" id=\"dane-".$stud_id."-".$cid."\" onclick=\"javascript:prisustvo(".$stud_id.",".$cid.")\"><div id=\"danetekst-".$stud_id."-".$cid."\">DA</div></td>";
			} else { 
				$prisustvo_ispis .= "<td bgcolor=\"#FFCCCC\" align=\"center\" id=\"dane-".$stud_id."-".$cid."\" onclick=\"javascript:prisustvo(".$stud_id.",".$cid.")\"><div id=\"danetekst-".$stud_id."-".$cid."\">NE</div></td>";
				$odsustvo++;
			}
			$ocj = mysql_result($q14,0,1);
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

	if (count($zad_id_array)==0) $zadace_ispis = "<td>&nbsp;</td>";
	foreach ($zad_id_array as $zid) {
		$zadace_ispis .= "<td>\n";
		$q15 = myquery("select zadatak,status,bodova from zadace where zadaca=$zid and student=$stud_id order by zadatak,id desc");
		if (mysql_num_rows($q15)<1) { $zadace_ispis .= "&nbsp;"; }
		$prevzadatak=0;
		while ($r15 = mysql_fetch_row($q15)) {
			if ($r15[0]==$prevzadatak) continue;
			if ($r15[1] == 1) {
				$icon = "zad_cekaj";
				$title = "Automatsko testiranje u toku";
			} else if ($r15[1] == 4) {
				$icon = "zad_preg";
				$title = "Potrebno pregledati";
			} else if ($r15[1] == 2) { 
				$icon = "zad_copy";
				$title = "Zadaća prepisana";
			} else if ($r15[1] == 5) { 
				$icon = "zad_ok";
				$title = "OK";
			} else  { // BUG - 3, 0, 6...
				$icon = "zad_bug";
				$title = "Bug u programu";
			}
			$zadace_ispis .= "<a href=\"javascript:openzadaca('".$stud_id."', '".$zid."', '".$r15[0]."')\"><img src=\"images/".$icon.".png\" width=\"16\" height=\"16\" border=\"0\" align=\"center\" title=\"".$title."\" alt=\"".$title."\"> ".$r15[2]."</a>";
			$bodova += $r15[2];
			$prevzadatak=$r15[0];
		}
		$zadace_ispis .= "</td>\n";
		$mogucih += 2;
	}

	if (count($par_id_array)==0) $parc_ispis = "<td>&nbsp;</td>";
	$i=$pao1=$pao2=0;
	foreach ($par_id_array as $pid) {
		$i++;
		$q16 = myquery("select ocjena from parcijale where student=$stud_id and id=$pid");
		if (mysql_num_rows($q16)>0) {
			if (($ocjena = mysql_result($q16,0,0)) == -1) {
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




}

?>
