<?

function stud_status() {

global $stud_id;

#$motd = "NAPOMENA ZA ZADAĆU 1: Prvi zadatak trebate uraditi i predati samo na papiru. Zadatke 2, 3 i 4 trebate dostaviti i u digitalnoj formi i na papiru! Ovdje je Zadatak 2 označen kao Zadatak 1, 3 kao 2 itd.";
#$motd = "Podaci o prvom zadatku prve zadaće (brojevni sistemi) uneseni su u sistem. Zbog komplikacija sa naknadnim unosom ovaj prvi zadatak se prikazuje kao četvrti. Ako vam stoji da je 'pregled u toku', znači da niste još predali zadatak ili da tutor(ica) još nije pregledao/la.";
#$motd = "UPUTA ZA SLANJE ZADAĆE 3: U aplikaciji je otvoreno 5 zadataka. Zadatak broj 1 pod a) b) i c) šaljete kao prva tri zadatka, a zatim šaljete zadatke 2 i 3.";
$motd = "Na zahtjev studenata, rok za predaju zadaće 4 produžuje se do 24. 12.";
//$motd = "";



# Dobrodošlica

$q1 = myquery("select ime,prezime,grupa from studenti where id=$stud_id");
$ime = mysql_result($q1,0,0);
$prezime = mysql_result($q1,0,1);
$stud_spol = substr($ime,strlen($ime)-1);
if ($stud_spol == "a" && $ime != "Vanja" && $ime != "Peđa" && $ime != "Mirza" && $ime != "Feđa") {
	print "<h1>Dobro došla, $ime $prezime!<h1>";
} else {
	print "<h1>Dobro došao, $ime $prezime!</h1>";
}

$grupa_id = mysql_result($q1,0,2);


# MOTD

$q2 = myquery("select id,rok from zadace_objavljene where rok>curdate() and aktivna=1");
while ($r2 = mysql_fetch_row($q2)) {
	print "<center><h2><font color=\"#00AA00\">Rok za odbranu zadaće ".$r2[0]." je ".date("d. m. Y.",mysql2time($r2[1]))."</font></h2></center>";
}

print "<h3>$motd</h3>";

?>
<br/>


<center><table width="90%" cellspacing="0" cellpadding="0"><tr bgcolor="#999999"><td><img src="images/fnord.gif" width="1" height="1"></td></tr></table></center>

<br/>
<?


# Priprema za finalni zbir

$mogucih = 0;



# Prisustvo

$q10 = myquery("select id,datum,vrijeme from casovi where grupa=$grupa_id order by datum");
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
		$q11 = myquery("select prisutan,ocjena from prisustvo where student=$stud_id and cas=$cid");
		if (mysql_num_rows($q11)>0) {
			if (mysql_result($q11,0,0) == 1) { 
				$prisustvo_ispis .= "<td bgcolor=\"#CCFFCC\" align=\"center\">DA</td>";
			} else { 
				$prisustvo_ispis .= "<td bgcolor=\"#FFCCCC\" align=\"center\">NE</td>";
				$odsustvo++;
			}
			$ocj = mysql_result($q11,0,1);
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




<table width="100%" border="0"><tr><td valign="top">
<h3>Zadaće:</h3>

<p>Za ponovno slanje zadatka, kliknite na sličicu u tabeli dolje.</p>
</td>
<td valign="top">
<table width="100%" border="1" cellspacing="0" cellpadding="5" bgcolor="#EEEEEE"><tr><td>
<table border="0" cellspacing="2" cellpadding="0"><tr><td>
<b>LEGENDA:</b></td></tr>
<tr><td><img src="images/zad_preg.png" width="16" height="16" border="0" align="center"> Pregled u toku</td></tr>
<tr><td><img src="images/zad_bug.png" width="16" height="16" border="0" align="center"> Bug u programu, pošaljite ponovo</td></tr>
<tr><td><img src="images/zad_copy.png" width="16" height="16" border="0" align="center"> Zadatak prepisan, pošaljite ponovo</td></tr>
<tr><td><img src="images/zad_ok.png" width="16" height="16" border="0" align="center"> Zadatak pregledan, bodovi su navedeni pored</td></tr></table>

</td></tr></table>

</td></tr></table>


<center><table cellspacing="0" cellpadding="2" border="1">
<tr><td>&nbsp;</td>

<?


$q20 = myquery("select zadatak from zadace where student=$stud_id group by zadatak order by zadatak");
$broj_zadataka = mysql_num_rows($q20);
$i=0;
while ($r20 = mysql_fetch_row($q20)) {
	?><td>Zadatak <?=$r20[0];?></td><?
	$zadarr[$i++]=$r20[0];
}

?>
<td><b>Ukupno bodova</b></td>
<?



// LEGENDA STATUS POLJA:
// 0 - nepoznat status
// 1 - nova zadaća
// 2 - prepisana
// 3 - ne može se kompajlirati
// 4 - prošla test, predstoji kontrola
// 5 - pregledana


$q21 = myquery("select zadaca,zadatak,status,bodova from zadace where student=$stud_id order by zadaca,zadatak,id desc");

$zdc=-1;
$totzb=0;
$totb="0";


while ($r21 = mysql_fetch_row($q21)) {
	if ($zdc != $r21[0]) {
		?>&nbsp;</td><?
		if ($zdc != -1) {
			while ($zdtk<$broj_zadataka) {
				print "<td>&nbsp;</td>";
				$zdtk++;
			}
			print "<td>$totb</td>";
		}
		?></tr><tr><td>Zadaća <?=$r21[0]?><?
		$zdc=$r21[0];
		$zdtk=0;
		$totzb += $totb;
		$mogucih += 2;
		$totb=0;
	}
	if ($zdtk>=$r21[1]) continue;
	while ($zdtk < $r21[1]) {
		?>&nbsp;</td><td align="center"><?
		$zdtk++;
	}
	if ($r21[2] == 1 || $r21[2] == 4) { // Looking
		$icon = "zad_preg";
		$title = "Pregled u toku";
	} else if ($r21[2] == 2) { // Looking
		$icon = "zad_copy";
		$title = "Zadaća prepisana";
	} else if ($r21[2] == 5) { // Looking
		$icon = "zad_ok";
		$title = "OK";
	} else  { // BUG - 3, 0, 6...
		$icon = "zad_bug";
		$title = "Bug u programu";
	}

	# ispis reda
	?><a href="student.php?sta=zadaca&zadaca=<?=$r21[0]?>&zadatak=<?=$r21[1]?>"><img src="images/<?=$icon?>.png" width="16" height="16" border="0" align="center" title="<?=$title?>" alt="<?=$title?>"> <?=$r21[3]?></a><?

	$totb += $r21[3];
	//$zdtk++;
}

//$mogucih += 2;
$totzb += $totb;
$bodova += $totzb;
while ($zdtk<$broj_zadataka) {
				print "&nbsp;</td><td>";
				$zdtk++;
			}
?>
&nbsp;</td><td><?=$totb?></td></tr>
<tr><td colspan="<?=$broj_zadataka+1?>" align="right">UKUPNO: </td>
<td><?=$totzb?></td></tr>
</table></center>

<br/>

<center><table width="90%" cellspacing="0" cellpadding="0"><tr bgcolor="#999999"><td><img src="images/fnord.gif" width="1" height="1"></td></tr></table></center>

<br/>







<h3>Parcijale:</h3>


<?
	

$q30 = myquery("select id from parcijale group by id order by id");
$brparc = mysql_num_rows($q30);
if ($brparc == 0) {
	print "<p>Nije bilo parcijalnih ispita.</p>";
} else for ($i=0;$i<$brparc;$i++) {
	$pid = mysql_result($q30,$i,0);

	if ($i==0) print "<p>Prva parcijala: <b>";
	else if ($i==1) print "<p>Druga parcijala: <b>";
	else print "<p>Naka parcijala: <b>";

	$q31 = myquery("select ocjena from parcijale where student=$stud_id and id=$pid");
	if (mysql_num_rows($q31)>0) {
		if (($ocjena = mysql_result($q31,0,0)) == -1) {
			if ($stud_spol=="a") print "Nije izašla"; else print "Nije izašao";
			$pao1 = 1;
		} else {
			print "$ocjena bodova";
			if ($ocjena<10) $pao1 = 1;
			$bodova = $bodova + $ocjena;
		}
		$mogucih+=20;
	} else {
		if ($stud_spol=="a") print "Nije izašlaa"; else print "Nije izašaoa";
		if ($i==1) { $pao1 = 1; } else { $pao2 = 1; }
		$mogucih+=20;
	}
	print "</b></p>";
}



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

<h3>Osvojeno <?=$bodova?> od <?=$mogucih?> mogućih (<?=$procent?> %)</h3>

<center><table cellspacing="0" cellpadding="0">
<tr>
	<td>
	<table width="50" height="50" cellspacing="0" cellpadding="0" border="1"><tr>
	<td align="center" valign="center" bgcolor="<?=$thecolor?>"><?=$theletter?></td>
	</tr></table>
	</td>
	<td>&nbsp;&nbsp;&nbsp;</td>
	<td valign="center"><?=$thetext?></td>
</tr></table></center>


<?

}

?>
