<?php

//novi modul student/prijava_ispita


function nastavnik_prijava_ispita() {

require("lib/manip.php");


	

$predmet=intval($_REQUEST['predmet']);
if ($predmet==0) { 
	zamgerlog("ilegalan predmet $predmet",3); //nivo 3: greska
	biguglyerror("Nije izabran predmet."); 
	return; 
}


$q1 = myquery("select p.naziv from predmet as p, ponudakursa as pk where pk.id=$predmet and pk.predmet=p.id");
$predmet_naziv = mysql_result($q1,0,0);

$dan=date("d"); $mjesec=date("m"); $godina=date("Y"); 
$sat=date("H"); $minuta=date("i"); $sekunda=date("s");
$dan1=date("d"); $mjesec1=date("m"); $godina1=date("Y"); 
$sat1=date("H"); $minuta1=date("i"); $sekunda1=date("s");
$limit=0;
?>

<br/>
<p><h3><?=$predmet_naziv?> - Prijava ispita</h3></p>

<?php



//akcija koja brise ispitni termin

if ($_REQUEST["akcija"]=="obrisi")
{
	if ($_GET["termin"]) {
	
	$delete1="DELETE FROM ispit_termin WHERE id=" . $_GET["termin"];
	$delete2="DELETE FROM student_ispit_termin WHERE ispit_termin=" . $_GET["termin"];
	myquery($delete1);
	myquery($delete2);
	
	}
	
?>
	<script language="JavaScript">
		window.location="?sta=nastavnik/prijava_ispita&predmet=<?php echo $predmet; ?>";
	</script>
<?php
	$terminid=$_GET["termin"];
	zamgerlog("Izbrisan ispitni termin id=$terminid", 2);
}






if ($_REQUEST["akcija"]=="izmijeni")
{
	if ($_GET["termin"]) {
	
	$s2="SELECT it.datumvrijeme, it.deadline, k.id, it.maxstudenata
             FROM ispit_termin as it, komponenta as k, ispit as i WHERE i.id=it.ispit AND k.id=it.komponenta AND i.predmet=$predmet AND it.id=" . $_GET["termin"];

	$q2 = myquery($s2);
	
	$limit = intval(mysql_result($q2,0,3));
	$vrijeme = mysql2time(mysql_result($q2,0,0));
	$vrijeme1 = mysql2time(mysql_result($q2,0,1));
	$tip = mysql_result($q2,0,2);
	
	}

	if($vrijeme){
	$dan = date('d',$vrijeme);
	$mjesec = date('m',$vrijeme);
	$godina = date('Y',$vrijeme);
	$sat = date('H',$vrijeme);
	$minuta = date('i',$vrijeme);
	$sekunda = date('s',$vrijeme);
	}
	if($vrijeme1){
	$dan1 = date('d',$vrijeme1);
	$mjesec1 = date('m',$vrijeme1);
	$godina1 = date('Y',$vrijeme1);
	$sat1 = date('H',$vrijeme1);
	$minuta1 = date('i',$vrijeme1);
	$sekunda1 = date('s',$vrijeme1);
	}
	
	$terminid=$_GET["termin"];

}





	




//forma za unos novog ispitnog termina

?>

	<? if($terminid<=0) print 'Registruj novi ispitni termin za prijavu:';
         else print 'Izmijeni termin:'; ?>
	<?=genform("POST")?>
	<input type="hidden" name="termin_id" value="<?=$terminid?>">
	<? if($terminid<=0) print '<input type="hidden" name="akcija" value="dodajtermin">';
	   else print '<input type="hidden" name="akcija" value="izmijenitermin">';
	?>
	<br/>
	Datum i vrijeme ispita:<br/>
	<select name="dan" class="default"><?
	for ($i=1; $i<=31; $i++) {
		print "<option value=\"$i\"";
		if ($i==$dan) print " selected";
		print ">$i</option>";
	}
	?></select>&nbsp;&nbsp;
	<select name="mjesec" class="default"><?
	for ($i=1; $i<=12; $i++) {
		print "<option value=\"$i\"";
		if ($i==$mjesec) print " selected";
		print ">$i</option>";
	}
	?></select>&nbsp;&nbsp;
	<select name="godina" class="default"><?
	for ($i=2005; $i<=2015; $i++) {
		print "<option value=\"$i\"";
		if ($i==$godina) print " selected";
		print ">$i</option>";
	}
	?></select>

	&nbsp;&nbsp; <input type="text" name="sat" size="1" value="<?=$sat?>"> <b>:</b> <input type="text" name="minuta" size="1" value="<?=$minuta?>"> <b>:</b> <input type="text" name="sekunda" size="1" value="<?=$sekunda?>">
	<br/><br/>

      Krajnji rok za prijavu ispita:
	<br/>
	<select name="dan1" class="default"><?
	for ($i=1; $i<=31; $i++) {
		print "<option value=\"$i\"";
		if ($i==$dan1) print " selected";
		print ">$i</option>";
	}
	?></select>&nbsp;&nbsp;
	<select name="mjesec1" class="default"><?
	for ($i=1; $i<=12; $i++) {
		print "<option value=\"$i\"";
		if ($i==$mjesec1) print " selected";
		print ">$i</option>";
	}
	?></select>&nbsp;&nbsp;
	<select name="godina1" class="default"><?
	for ($i=2005; $i<=2015; $i++) {
		print "<option value=\"$i\"";
		if ($i==$godina1) print " selected";
		print ">$i</option>";
	}
	?></select>

	&nbsp;&nbsp; <input type="text" name="sat1" size="1" value="<?=$sat1?>"> <b>:</b> <input type="text" name="minuta1" size="1" value="<?=$minuta1?>"> <b>:</b> <input type="text" name="sekunda1" size="1" value="<?=$sekunda1?>">
	<br/><br/>
	Maksimalan broj studenata: <input type="text" size="1" name="limit" value="<?=$limit?>"  class="default">
	<br/><br/>
	Tip ispita:
	</select>&nbsp;&nbsp;
	<select name="tipispita" class="default">
		<option value="1"<?php if($tip==1) print " selected" ?>>1. parcijalni</option>
		<option value="2"<?php if($tip==2) print " selected" ?>>2. parcijalni</option>
		<option value="3"<?php if($tip==3) print " selected" ?>>integralni</option>
		<option value="4"<?php if($tip==4) print " selected" ?>>usmeni</option>
	</select>
	<br/><br/><br/>

	<? if($terminid<=0) print '<input type="submit" value="Dodaj"  class="default"><br/><br/>';
         else {
		 print '<input type="submit" value="Izmijeni"  class="default"><br/><br/>';
		 print '<a href="?sta=nastavnik/prijava_ispita&predmet='. $predmet .'">Novi termin</a><br/>';
	   }
	?>
</form>

<?php



//dodavanje novog ispitnog termina

if ($_POST['akcija']=='dodajtermin') {




	$limit = intval($_POST['limit']);
	$tip = intval($_POST['tipispita']);
	$dan = intval($_POST['dan']);
	$mjesec = intval($_POST['mjesec']);
	$godina = intval($_POST['godina']);
	$sat = intval($_POST['sat']);
	$minuta = intval($_POST['minuta']);
	$sekunda = intval($_POST['sekunda']);
	$dan1 = intval($_POST['dan1']);
	$mjesec1 = intval($_POST['mjesec1']);
	$godina1 = intval($_POST['godina1']);
	$sat1 = intval($_POST['sat1']);
	$minuta1 = intval($_POST['minuta1']);
	$sekunda1 = intval($_POST['sekunda1']);

	$t1 = time2mysql(mktime($sat,$minuta,$sekunda,$mjesec,$dan,$godina));
	$t2 = time2mysql(mktime($sat1,$minuta1,$sekunda1,$mjesec1,$dan1,$godina1));


//Provjera ispravnosti

	if (!checkdate($mjesec,$dan,$godina)) {
		niceerror("Odabrani datum je nemoguc");
		zamgerlog("los datum", 3);
		return 0;
	}
	if ($sat<0 || $sat>24 || $minuta<0 || $minuta>60 || $sekunda<0 || $sekunda>60) {
		niceerror("Vrijeme nije dobro");
		zamgerlog("lose vrijeme", 3);
		return 0;
	}
	if (!checkdate($mjesec1,$dan1,$godina1)) {
		niceerror("Odabrani datum je nemoguc");
		zamgerlog("los datum", 3);
		return 0;
	}
	if ($sat1<0 || $sat1>24 || $minuta1<0 || $minuta1>60 || $sekunda1<0 || $sekunda1>60) {
		niceerror("Vrijeme nije dobro");
		zamgerlog("lose vrijeme", 3);
		return 0;
	}
	if ($limit<=0){
		niceerror("Maksimalni broj studenata na ispitu mora biti veci od nule");
		zamgerlog("los max broj studenata po ispitu",3);
		return 0;
	}
	$s1="SELECT id FROM ispit WHERE predmet=$predmet AND komponenta=$tip";
	$q1=myquery($s1);

	if (mysql_num_rows($q1)>0) {
		$sqlInsert="INSERT INTO ispit_termin SET datumvrijeme='$t1' , komponenta=$tip , maxstudenata=$limit , ispit=$predmet , deadline='$t2'";
		$q=myquery($sqlInsert);
		nicemessage("Kreiran novi ispitni termin uspjesno.");
		zamgerlog("Kreiran novi ispitni termin", 2);
	}
	else {
		niceerror("Ispit nije kreiran, morate prvo da kreirate ispit u sekciji 'unos rezultata ispita'");
		zamgerlog("nepostojeci ispit", 3);

	}


}


//izmjena postojeceg ispitnog termina

if ($_POST['akcija']=='izmijenitermin') {

	$terminid = intval($_REQUEST['termin_id']);


	$limit = intval($_POST['limit']);
	$tip = intval($_POST['tipispita']);
	$dan = intval($_POST['dan']);
	$mjesec = intval($_POST['mjesec']);
	$godina = intval($_POST['godina']);
	$sat = intval($_POST['sat']);
	$minuta = intval($_POST['minuta']);
	$sekunda = intval($_POST['sekunda']);
	$dan1 = intval($_POST['dan1']);
	$mjesec1 = intval($_POST['mjesec1']);
	$godina1 = intval($_POST['godina1']);
	$sat1 = intval($_POST['sat1']);
	$minuta1 = intval($_POST['minuta1']);
	$sekunda1 = intval($_POST['sekunda1']);

	$t1 = time2mysql(mktime($sat,$minuta,$sekunda,$mjesec,$dan,$godina));
	$t2 = time2mysql(mktime($sat1,$minuta1,$sekunda1,$mjesec1,$dan1,$godina1));


//Provjera ispravnosti

	if (!checkdate($mjesec,$dan,$godina)) {
		niceerror("Odabrani datum je nemoguc");
		zamgerlog("los datum", 3);
		return 0;
	}
	if ($sat<0 || $sat>24 || $minuta<0 || $minuta>60 || $sekunda<0 || $sekunda>60) {
		niceerror("Vrijeme nije dobro");
		zamgerlog("lose vrijeme", 3);
		return 0;
	}
	if (!checkdate($mjesec1,$dan1,$godina1)) {
		niceerror("Odabrani datum je nemoguc");
		zamgerlog("los datum", 3);
		return 0;
	}
	if ($sat1<0 || $sat1>24 || $minuta1<0 || $minuta1>60 || $sekunda1<0 || $sekunda1>60) {
		niceerror("Vrijeme nije dobro");
		zamgerlog("lose vrijeme", 3);
		return 0;
	}
	if ($limit<=0){
		niceerror("Maksimalni broj studenata na ispitu mora biti veci od nule");
		zamgerlog("los max broj studenata po ispitu",3);
		return 0;
	}
	$s111="SELECT id FROM ispit WHERE predmet=$predmet AND komponenta=$tip";
	$q111=myquery($s111);

	if (mysql_num_rows($q111)>0) {
		$sqlUpdate="UPDATE ispit_termin SET datumvrijeme='$t1' , komponenta=$tip , maxstudenata=$limit , ispit=$predmet , deadline='$t2' WHERE id=$terminid";
		$q5=myquery($sqlUpdate);
		nicemessage("Izmijenjen ispitni termin uspjesno.");
		zamgerlog("Izmijenjen ispitni termin", 2);
	}
	else {
		niceerror("Ispit nije izmijenjen, morate prvo da kreirate ispit u sekciji 'unos rezultata ispita'");
		zamgerlog("nepostojeci ispit", 3);

	}


}



echo "<p><hr/></p>";





//tabela objavljenih termina za predmet

$s1="SELECT DISTINCT it.id, UNIX_TIMESTAMP(it.datumvrijeme), UNIX_TIMESTAMP(it.deadline), k.gui_naziv, it.maxstudenata
             FROM ispit_termin as it, komponenta as k,ispit as i WHERE it.ispit=i.id AND it.komponenta=k.id AND i.predmet=$predmet ";

$q1 = myquery($s1);
?>

<b>Objavljeni ispiti:</b>
<br><br>
<table width="600" border="1" cellpadding="1" cellspacing="1" bordercolor="#000000">
	<tr>
	<td width=20><b>R.br.</b></td>
     	<td align="center" width=110><b>Vrijeme ispita</b></td>
      <td align="center" width=110><b>Rok za prijavu</b></td>
	<td align="center" width=80><b>Tip ispita</b></td>
	<td align="center" width=60><b>Max studenata</b></td>
	<td align="center"><b>Opcije</b></td>
	</tr>

<?php
$brojac=1;

while ($ispit=mysql_fetch_row($q1)) {


?>
	<tr>
	<td><?=$brojac ?></td>
	<td align="center"><?=date("d.m.Y. H:i",date($ispit[1]));?></td>
	<td align="center"><font color="#FF0000"><?=date("d.m.Y. H:i",date($ispit[2]));?></font></td>

	<td align="center"><?=$ispit[3]?></td>

	<td align="center"><?=$ispit[4]?></td>
	<td align="center"><a href="?sta=nastavnik/prijava_ispita&akcija=izmijeni&termin=<?php echo $ispit[0];?>&predmet=<?php echo $predmet ?> ">Izmijeni</a>&nbsp;&nbsp;
				 <a href="?sta=nastavnik/prijava_ispita&akcija=obrisi&termin=<?php echo $ispit[0];?>&predmet=<?php echo $predmet ?> ">Obrisi</a>&nbsp;&nbsp;
				 <a href="?sta=nastavnik/prijava_ispita&akcija=studenti&termin=<?php echo $ispit[0];?>&predmet=<?php echo $predmet ?> ">Studenti</a></td>
	

	</tr>

<?php
$brojac++;
}

echo "</table>";
if ($brojac==1) echo "<br><font color=\"red\">Nema objavljenih termina trenutno.</font>";





//tabela studenata koji su se prijavili za odredjeni ispitni termin

if ($_REQUEST["akcija"]=="studenti")
{
   if ($_GET["termin"]) {
	
	print '<br>';
	print '<p><hr/></p>';
	print '<br>';
	$s2="SELECT o.ime, o.prezime, o.brindexa FROM osoba as o, student_ispit_termin as si, ispit_termin as it WHERE o.id=si.student AND it.id=si.ispit_termin AND it.id=" . $_GET["termin"];
	$q2=myquery($s2);

	print '<table width="360" border="1" cellpadding="1" cellspacing="1" bordercolor="#000000">';
	print '<tr>';
	print '<td width=20><b>R.br.</b></td>';
     	print '<td width=250><b>Ime i Prezime</b></td>';
      print '<td align="center" width=90><b>Broj indexa</b></td>';
	print '</tr>';
	
	$brojac=1;
	while ($r=mysql_fetch_row($q2)) {
	print '<tr>';
	 print '<td>';
	   print $brojac; 
	 print '</td>';
	 print '<td>'; 
         print $r[0]." ".$r[1];
       print'</td>';
	 print '<td align="center">';
         print $r[2]; 
       print '</td>';
	print '</tr>';
	$brojac++;	
	}
	
	print '</table>';
	if($brojac==1) print '<br><font color="red">Do sada se niko nije prijavio za ovaj termin.</font>';

	
    }
	

}


}

?>