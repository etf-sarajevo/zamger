<?php

//novi modul student/prijava_ispita


function student_prijava_ispita() {

global $userid;



//dio koda koji se izvrsava kad se klikne na "odjavi"
if ($_GET["akcija"]=="odjavi")
{
	$termin = intval($_GET['termin']);
	if ($termin) {
	$sqlDelete="DELETE FROM student_ispit_termin WHERE student=" .$userid . " AND ispit_termin=" . $termin . ";";
	myquery($sqlDelete);
	}
?>
	<script language="JavaScript">
		window.location="?sta=student/prijava_ispita";
	</script>
<?php
}

//dio koda koji se izvrsava kad se klikne na "prijavi"
if ($_GET["akcija"]=="prijavi")
{
	$termin = intval($_GET['termin']);
    if ($termin) {
	//sljedeci dio koda ispituje da li je popunjen ispitni termin
	$s0 = "SELECT it.id FROM ispit_termin as it, osoba as o, ispit as i, ponudakursa as pk, student_predmet as sp WHERE it.id=$termin AND it.ispit=i.id AND i.predmet=pk.id AND pk.id=sp.predmet AND sp.student=o.id AND o.id=$userid";
	$s1 = "SELECT count(*) FROM student_ispit_termin WHERE ispit_termin=$termin";
	$s2 = "SELECT maxstudenata FROM ispit_termin WHERE id=$termin";
	$q0 = myquery ($s0);
	$q1 = myquery ($s1);
	$q2 = myquery ($s2);
	$temp0 = mysql_fetch_row($q0);
	$temp1 = mysql_fetch_row($q1);
	$temp2 = mysql_fetch_row($q2);
	if(!$temp0[0])
	{
		niceerror("Niste upisani na taj predmet!");
		zamgerlog("nije upisan na predmet", 3);
		return 0;
	} 
	if($temp1[0]<$temp2[0])
		{	
		$sqlInsert="INSERT INTO student_ispit_termin (student,ispit_termin) VALUES (" .
		$userid . "," . $termin . ");";
		myquery($sqlInsert);
		}
    }
	else 
	{
		niceerror("Popunjen ispitni termin!");
		zamgerlog("popunjen termin", 3);
		return 0;
	}
?>
	<script language="JavaScript">
		window.location="?sta=student/prijava_ispita";
	</script>
<?php
}




$s3="SELECT DISTINCT p.naziv, UNIX_TIMESTAMP(it.datumvrijeme), UNIX_TIMESTAMP(it.deadline), k.gui_naziv, it.id, it.maxstudenata, k.id
             FROM ispit_termin as it, ispit as i, predmet as p, komponenta as k, osoba as o, student_predmet as sp, ponudakursa as pk, akademska_godina as ag
		 WHERE it.ispit=i.id AND it.komponenta=k.id AND i.predmet=pk.id AND p.id=pk.predmet AND o.id=$userid AND o.id=sp.student AND sp.predmet=pk.id AND pk.akademska_godina=ag.id AND ag.aktuelna=1
                   AND it.id NOT IN (SELECT ispit_termin FROM student_ispit_termin WHERE student=$userid)";
$s4="SELECT io.ocjena, i.komponenta FROM ispit as i, ispit_termin as it, ispitocjene as io WHERE io.student=$userid AND io.ispit=i.id AND it.ispit=i.id";

$q3 = myquery($s3);
$q4 = myquery($s4);


?>
<br><br>
<b>Ispiti otvoreni za prijavu:</b>
<br><br>
<table width="750" border="1" cellpadding="1" cellspacing="1" bordercolor="#000000">
	<tr>
	<td width=20><b>R.br.</b></td>
	<td width=300><b>Predmet</b></td>
     	<td align="center" width=110><b>Vrijeme ispita</b></td>
      <td align="center" width=110><b>Rok za prijavu</b></td>
	<td align="center" width=80><b>Tip ispita</b></td>
	<td align="center"><b>Opcije</b></td>
	</tr>

<?php
$brojac=1;

while ($ispit=mysql_fetch_row($q3)) {

//sljedeci dio koda izbacuje iz tabele polozene ispite
    $br=0;
	while ($a=mysql_fetch_row($q4))
	{
		if($ispit[6]==$a[1] && $a[0]>=10 && $a[1]==1) $br++;
		if($ispit[6]==$a[1] && $a[0]>=10 && $a[1]==2) $br++;
		if($ispit[6]==$a[1] && $a[0]>=20 && $a[1]==3) $br++;
		if($ispit[6]==$a[1] && $a[0]>=20 && $a[1]==4) $br++;
	}
	if($br!=0) continue;
//sljedeci dio koda ispituje da li je popunjen ispitni termin
	$s5 = "SELECT count(*) FROM student_ispit_termin WHERE ispit_termin=" . $ispit[4];
	$q5 = myquery ($s5);
	$temp = mysql_fetch_row($q5);
	if($temp[0]==$ispit[5]) continue;

//naredne 2 linije provjeravaju da li je istekao rok za prijavu ispita
	$mytime = time(); // Set time to now
      if($mytime>$ispit[2]) continue;

//naredne 2 linije provjeravaju da li je ispit zavrsen (ako je slucajno neko postavio veci 'deadline' od 'datumvrijeme' ispita)
	if($mytime>$ispit[1]) continue;


?>
	<tr>
	<td><? echo "$brojac"; ?></td>
	<td><?=$ispit[0]; ?></td>
	<td align="center"><?=date("d.m.Y. H:i",date($ispit[1]));?></td>
	<td align="center"><font color="#FF0000"><?=date("d.m.Y. H:i",date($ispit[2]));?></font></td>

	<td align="center"><?=$ispit[3];?></td>

	<td align="center"><a href="?sta=student/prijava_ispita&akcija=prijavi&termin=<?php echo $ispit[4];?> ">Prijavi</a></td>
	</tr>

<?php
$brojac++;
}
echo "</table>";

if($brojac==1) echo "<p><font color=\"#FF0000\">Trenutno nemate ispitnih termina koji su otvoreni za prijavu.</font></p>";

echo "<br><br><br>";
echo "<b>Prijavljeni ispiti:</b>";






//slijedeci dio koda sluzi za tabelarni prikaz prijavljenih predmeta


$s6="SELECT DISTINCT p.naziv, UNIX_TIMESTAMP(it.datumvrijeme), k.gui_naziv, it.id
             FROM ispit_termin as it, ispit as i, predmet as p, osoba as o, student_predmet as sp, komponenta k, ponudakursa as pk, student_ispit_termin as si
             WHERE it.ispit=i.id AND pk.id=i.predmet  AND p.id=pk.predmet AND it.komponenta=k.id AND o.id=$userid AND o.id=sp.student AND sp.predmet=pk.id AND si.student=$userid AND si.student=o.id AND si.ispit_termin=it.id";

$q6 = myquery($s6);

?>
<br><br>
<table width="630" border="1" cellpadding="1" cellspacing="1" bordercolor="#000000">
	<tr>
	<td width=20><b>R.br.</b></td>
	<td width=300><b>Predmet</b></td>
     	<td align="center" width=110><b>Vrijeme ispita</b></td>
	<td align="center" width=80><b>Tip ispita</b></td>
	<td align="center"><b>Opcije</b></td>
	</tr>

<?php
$brojac=1;

while ($ispit=mysql_fetch_row($q6)) {
	$mytime = time(); // Postavi vrijeme na trenutno
      if($mytime>$ispit[1]) continue;

?>
	<tr>
	<td><? echo "$brojac"; ?></td>
	<td><?=$ispit[0]; ?></td>
	<td align="center"><?=date("d.m.Y. H:i",date($ispit[1]));?></td>

	<td align="center"><?=$ispit[2];?></td>
	</td>
	<td align="center"><a href="?sta=student/prijava_ispita&akcija=odjavi&termin=<?php echo $ispit[3];?> ">Odjavi</a></td>
	</tr>

<?php
$brojac++;
}

echo "</table>";

if($brojac==1) echo "<p><font color=\"#FF0000\">Trenutno nemate prijavljenih ispita.</font></p>";




}
?>