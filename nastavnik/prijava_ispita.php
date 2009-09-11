<?php

//novi modul nastavnik/prijava_ispita


function nastavnik_prijava_ispita() {

require("lib/manip.php");

global $userid,$user_siteadmin;
	
//parametri	
$predmet = intval($_REQUEST['predmet']);
$ag = intval($_REQUEST['ag']);
$ispit = intval($_REQUEST['ispit']);
$termin = intval($_REQUEST['termin']);


if ($predmet==0) { 
	zamgerlog("ilegalan predmet $predmet",3); //nivo 3: greska
	biguglyerror("Nije izabran predmet."); 
	return; 
}

//provjera da li ispitni termin pripada ispitu
if($termin){
	$q0 = myquery("SELECT it.id FROM ispit_termin as it, ispit as i WHERE it.id=$termin AND it.ispit=i.id AND i.id=$ispit ");
	$result = mysql_result($q0,0,0);
	if (!$result) {
	zamgerlog("termin ne pripada ispitu",3);
	biguglyerror("Ispitni termin ne pripada datom ispitu"); 
	return;
	}
}

// Da li korisnik ima pravo uci u modul?

if (!$user_siteadmin) { // 3 = site admin
	$q10 = myquery("select admin from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
	if (mysql_num_rows($q10)<1 || mysql_result($q10,0,0)<1) {
		zamgerlog("nastavnik/prijava_ispita privilegije (predmet pp$predmet)",3);
		biguglyerror("Nemate pravo ulaska u ovu grupu!");
		return;
	} 
}



$q1 = myquery("select naziv from predmet where id=$predmet");
$predmet_naziv = mysql_result($q1,0,0);

$dan=date("d"); $mjesec=date("m"); $godina=date("Y"); 
$sat=date("H"); $minuta=date("i"); $sekunda=date("s");
$dan1=date("d"); $mjesec1=date("m"); $godina1=date("Y"); 
$sat1=date("H"); $minuta1=date("i"); $sekunda1=date("s");
$limit=0;
?>

<br/>
<p><h3><?=$predmet_naziv?> - Prijava ispita</h3></p>

<?



//akcija koja brise ispitni termin

if ($_REQUEST["akcija"]=="obrisi")
{
	$s555 = "SELECT it.id FROM ispit_termin as it, ispit as i WHERE it.id=$termin AND it.ispit=i.id AND i.predmet=$predmet";
	$q555 = myquery($s555);
	$r555 = mysql_result($q555,0,0);
	
	if (($termin) && ($r555)) {
	$delete1="DELETE FROM ispit_termin WHERE id=" . $termin;
	$delete2="DELETE FROM student_ispit_termin WHERE ispit_termin=" . $termin;
	myquery($delete1);
	myquery($delete2);
	zamgerlog("Izbrisan ispitni termin id=$termin", 2);
	}
	
?>
	<script language="JavaScript">
		window.location="?sta=nastavnik/prijava_ispita&predmet=<? print $predmet; ?>&ag=<? print $ag; ?>";
	</script>
<?
}



//ispisivanje tabele studenata koji su se vec prijavili za neki ispitni termin
if ($_REQUEST["akcija"]=="studenti")
{
   print '<br>';
   if ($termin) {
	
	
	$s2="SELECT o.ime, o.prezime, o.brindexa FROM osoba as o, student_ispit_termin as si, ispit_termin as it WHERE o.id=si.student AND it.id=si.ispit_termin AND it.id=" . $termin;
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

	print '<br>';
	print '<p><hr/></p>';
	print '<br>';
    }
	
	$s2="SELECT UNIX_TIMESTAMP(datumvrijeme), UNIX_TIMESTAMP(deadline) , maxstudenata
             FROM ispit_termin WHERE id=$termin";

	$q2 = myquery($s2);
	
	$limit = mysql_result($q2,0,2);
	$t1 = mysql_result($q2,0,0);
	$t2 = mysql_result($q2,0,1);

	if($t1){
	$dan = date('d',$t1);
	$mjesec = date('m',$t1);
	$godina = date('Y',$t1);
	$sat = date('H',$t1);
	$minuta = date('i',$t1);
	$sekunda = date('s',$t1);
	}
	if($t2){
	$dan1 = date('d',$t2);
	$mjesec1 = date('m',$t2);
	$godina1 = date('Y',$t2);
	$sat1 = date('H',$t2);
	$minuta1 = date('i',$t2);
	$sekunda1 = date('s',$t2);
	}

}


//u ovoj akciji se samo iz baze podataka uzimaju vrijednosti, konkretna promjena se vrsi u akciji "izmijenitermin"
if ($_REQUEST["akcija"]=="izmijeni")
{
	if ($termin) {
	
	$s2="SELECT UNIX_TIMESTAMP(datumvrijeme), UNIX_TIMESTAMP(deadline) , maxstudenata
             FROM ispit_termin WHERE id=$termin";

	$q2 = myquery($s2);
	
	$limit = mysql_result($q2,0,2);
	$t1 = mysql_result($q2,0,0);
	$t2 = mysql_result($q2,0,1);
	
	}

	if($t1){
	$dan = date('d',$t1);
	$mjesec = date('m',$t1);
	$godina = date('Y',$t1);
	$sat = date('H',$t1);
	$minuta = date('i',$t1);
	$sekunda = date('s',$t1);
	}
	if($t2){
	$dan1 = date('d',$t2);
	$mjesec1 = date('m',$t2);
	$godina1 = date('Y',$t2);
	$sat1 = date('H',$t2);
	$minuta1 = date('i',$t2);
	$sekunda1 = date('s',$t2);
	}
	

}


//dodavanje novog ispitnog termina

if ($_POST['akcija'] == 'dodajtermin' && check_csrf_token()) {

	$s4 = "SELECT komponenta FROM ispit WHERE id=$ispit";
	$q4 = myquery($s4);
		
	$limit = intval($_POST['limit']);
	$tip = mysql_result($q4,0,0);
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

	$t1 = mktime($sat,$minuta,$sekunda,$mjesec,$dan,$godina);
	$t2 = mktime($sat1,$minuta1,$sekunda1,$mjesec1,$dan1,$godina1);


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
	
	$sqlInsert="INSERT INTO ispit_termin SET datumvrijeme=FROM_UNIXTIME('$t1') , komponenta=$tip , maxstudenata=$limit , ispit=$ispit , deadline=FROM_UNIXTIME('$t2')";
	$q=myquery($sqlInsert);
	zamgerlog("Kreiran novi ispitni termin", 2);
?>
	<script language="JavaScript">
		window.location="?sta=nastavnik/ispiti&predmet=<? print $predmet; ?>&ag=<? print $ag; ?>";
	</script>
<?

}


//izmjena postojeceg ispitnog termina

if ($_POST['akcija'] == 'izmijenitermin' && check_csrf_token()) {

	$limit = intval($_POST['limit']);
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

	$t1 = mktime($sat,$minuta,$sekunda,$mjesec,$dan,$godina);
	$t2 = mktime($sat1,$minuta1,$sekunda1,$mjesec1,$dan1,$godina1);


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
	
		$sqlUpdate="UPDATE ispit_termin SET datumvrijeme=FROM_UNIXTIME('$t1') , maxstudenata=$limit , deadline=FROM_UNIXTIME('$t2') WHERE id=$termin";
		$q5=myquery($sqlUpdate);
		zamgerlog("Izmijenjen ispitni termin", 2);
?>
		<script language="JavaScript">
		window.location="?sta=nastavnik/ispiti&predmet=<? print $predmet; ?>&ag=<? print $ag; ?>";
		</script>
<?
}



//forma za unos novog ispitnog termina

?>

	<? if ($_REQUEST["akcija"]=="izmijeni" || $_REQUEST["akcija"]=="studenti") print 'Izmijeni termin:';
	   else print 'Registruj novi ispitni termin za prijavu:';
        ?>
	<?=genform("POST")?>
	<input type="hidden" name="termin" value="<?=$termin?>">
	<? if($termin<=0) print '<input type="hidden" name="akcija" value="dodajtermin">';
	   else print '<input type="hidden" name="akcija" value="izmijenitermin">';
	?>
	<? 
	$q1 = myquery("select k.gui_naziv from ispit as i, komponenta as k where i.komponenta=k.id and i.id=$ispit");
	$komponenta_naziv = mysql_result($q1,0,0); 
	?>
	Tip ispita: <b><? print $komponenta_naziv ?></b>
	<br/><br/>
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

	&nbsp;&nbsp; <input type="text" name="sat" size="2" value="<?=$sat?>"> <b>:</b> <input type="text" name="minuta" size="2" value="<?=$minuta?>"> <b>:</b> <input type="text" name="sekunda" size="2" value="<?=$sekunda?>">
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

	&nbsp;&nbsp; <input type="text" name="sat1" size="2" value="<?=$sat1?>"> <b>:</b> <input type="text" name="minuta1" size="2" value="<?=$minuta1?>"> <b>:</b> <input type="text" name="sekunda1" size="2" value="<?=$sekunda1?>">
	<br/><br/>
	Maksimalan broj studenata: <input type="text" size="2" name="limit" value="<?=$limit?>"  class="default">
	<br/><br/>

	<? 
       if ($_REQUEST["akcija"]=="izmijeni" || $_REQUEST["akcija"]=="studenti") print '<input type="submit" value="Izmijeni"  class="default"><br/><br/>';
	   else print '<input type="submit" value="Dodaj"  class="default"><br/><br/>';
	   
     	 print '<a href="?sta=nastavnik/ispiti&predmet='. $predmet . '&ag=' . $ag . '"><<< Nazad</a><br/>';
	?>
</form>

<?

echo "<p><hr/></p>";




//tabela objavljenih termina za predmet

$s1="SELECT DISTINCT it.id, UNIX_TIMESTAMP(it.datumvrijeme), UNIX_TIMESTAMP(it.deadline), k.gui_naziv, it.maxstudenata
             FROM ispit_termin as it, komponenta as k,ispit as i, akademska_godina as ag WHERE it.ispit=i.id AND it.komponenta=k.id AND i.predmet=$predmet AND ag.id=$ag";

$q1 = myquery($s1);
?>
<b>Objavljeni termini:</b>
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

<?
$brojac=1;

while ($r1=mysql_fetch_row($q1)) {

	$temp=$r1[0];
	$s5="SELECT i.id FROM ispit_termin as it, ispit as i WHERE it.id=$temp AND it.ispit=i.id";
	$q5=myquery($s5);
	$i=mysql_result($q5,0,0);

?>
	<tr>
	<td><?=$brojac ?></td>
	<td align="center"><?=date("d.m.Y. H:i",date($r1[1]));?></td>
	<td align="center"><font color="#FF0000"><?=date("d.m.Y. H:i",date($r1[2]));?></font></td>

	<td align="center"><?=$r1[3]?></td>

	<td align="center"><?=$r1[4]?></td>
	<td align="center"><a href="?sta=nastavnik/prijava_ispita&akcija=izmijeni&ispit=<? print $i;?>&termin=<? print $r1[0];?>&predmet=<? print $predmet ?>&ag=<? print $ag ?> ">Izmijeni</a>&nbsp;&nbsp;
				 <a href="?sta=nastavnik/prijava_ispita&akcija=obrisi&ispit=<? print $i;?>&termin=<? print $r1[0];?>&predmet=<? print $predmet ?>&ag=<? print $ag ?> ">Obrisi</a>&nbsp;&nbsp;
				 <a href="?sta=nastavnik/prijava_ispita&akcija=studenti&ispit=<? print $i;?>&termin=<? print $r1[0];?>&predmet=<? print $predmet ?>&ag=<? print $ag ?> ">Studenti</a></td>
	

	</tr>

<?
$brojac++;
}

print "</table>";
if ($brojac==1) echo "<br><font color=\"red\">Nema objavljenih termina trenutno.</font><br><br>
					      <font color=\"red\">* Napomena: Da bi objavili termine za prijavu ispita morate prvo kreirati ispit</font>";
print "<br>";






}

?>