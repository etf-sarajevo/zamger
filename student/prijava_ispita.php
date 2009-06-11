<?php

//novi modul student/prijava_ispita


function student_prijava_ispita() {

global $userid;

$s1="SELECT DISTINCT p.naziv, UNIX_TIMESTAMP(it.datumvrijeme), UNIX_TIMESTAMP(it.deadline), k.gui_naziv, it.id, it.maxstudenata
             FROM ispit_termin as it, ispit as i, predmet as p, komponenta as k, osoba as o, student_predmet as sp, ponudakursa as pk
		 WHERE it.ispit=i.id AND it.komponenta=k.id AND i.predmet=pk.id AND p.id=pk.predmet AND o.id=$userid AND o.id=sp.student AND sp.predmet=pk.id
                   AND it.id NOT IN (SELECT ispit_termin FROM student_ispit_termin WHERE student=$userid)";

$q1 = myquery($s1);

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

while ($ispit=mysql_fetch_row($q1)) {

//sljedeci dio koda ispituje da li je popunjen ispitni termin
	$s3 = "SELECT count(*) FROM student_ispit_termin WHERE ispit_termin=" . $ispit[4];
	$q3 = myquery ($s3);
	$temp = mysql_fetch_row($q3);
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

	<td align="center"><a href="?sta=student/prijava_ispita&akcija=prijavi&user_id=<?php echo
	$userid;?>&termin=<?php echo $ispit[4];?> ">Prijavi</a></td>
	</tr>

<?php
$brojac++;
}
echo "</table>";

//dio koda koji se izvrsava kad se klikne na "prijavi"
if ($_REQUEST["akcija"]=="prijavi")
{
	if ($_GET["user_id"] && $_GET["termin"]) {
	$sqlInsert="INSERT INTO student_ispit_termin (student,ispit_termin) VALUES (" .
	$_GET["user_id"] . "," . $_GET["termin"] . ");";
	myquery($sqlInsert);
	}
?>
	<script language="JavaScript">
		window.location="?sta=student/prijava_ispita";
	</script>
<?php
}


if($brojac==1) echo "<p><font color=\"#FF0000\">Trenutno nemate ispitnih termina koji su otvoreni za prijavu.</font></p>";

echo "<br><br><br>";
echo "<b>Prijavljeni ispiti:</b>";






//slijedeci dio koda sluzi za tabelarni prikaz prijavljenih predmeta


$s2="SELECT DISTINCT p.naziv, UNIX_TIMESTAMP(it.datumvrijeme), k.gui_naziv, it.id
             FROM ispit_termin as it, ispit as i, predmet as p, osoba as o, student_predmet as sp, komponenta k, ponudakursa as pk, student_ispit_termin as si
             WHERE it.ispit=i.id AND pk.id=i.predmet  AND p.id=pk.predmet AND it.komponenta=k.id AND o.id=$userid AND o.id=sp.student AND sp.predmet=pk.id AND si.student=$userid AND si.student=o.id AND si.ispit_termin=it.id";

$q2 = myquery($s2);

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

while ($ispit=mysql_fetch_row($q2)) {
	$mytime = time(); // Set time to now
      if($mytime>$ispit[1]) continue;

?>
	<tr>
	<td><? echo "$brojac"; ?></td>
	<td><?=$ispit[0]; ?></td>
	<td align="center"><?=date("d.m.Y. H:i",date($ispit[1]));?></td>

	<td align="center"><?=$ispit[2];?></td>
	</td>
	<td align="center"><a href="?sta=student/prijava_ispita&akcija=odjavi&user_id=<?php echo
	$userid;?>&termin=<?php echo $ispit[3];?> ">Odjavi</a></td>
	</tr>

<?php
$brojac++;
}

echo "</table>";

if($brojac==1) echo "<p><font color=\"#FF0000\">Trenutno nemate prijavljenih ispita.</font></p>";




//dio koda koji se izvrsava kad se klikne na "odjavi"
if ($_REQUEST["akcija"]=="odjavi")
{
	if ($_GET["user_id"] && $_GET["termin"]) {
	$sqlDelete="DELETE FROM student_ispit_termin WHERE student=" .
	$_GET["user_id"] . " AND ispit_termin=" . $_GET["termin"] . ";";
	myquery($sqlDelete);
	}
?>
	<script language="JavaScript">
		window.location="?sta=student/prijava_ispita";
	</script>
<?php
}


}
?>


