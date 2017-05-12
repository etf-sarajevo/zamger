<?

// NASTAVNIK/PRIJAVA_ISPITA - upravljanje terminima za prijavljivanje studenata na ispit



function nastavnik_prijava_ispita() {

require_once("lib/formgen.php"); // datectrl
require_once("lib/utility.php"); // nuliraj_broj


global $userid,$user_siteadmin,$user_studentska;
	
//parametri	
$predmet = intval($_REQUEST['predmet']);
$ag = intval($_REQUEST['ag']);
$ispit = intval($_REQUEST['ispit']);
$termin = intval($_REQUEST['termin']);



// Da li korisnik ima pravo uci u modul?

if (!$user_siteadmin && !$user_studentska) {
	$q10 = db_query("select nivo_pristupa from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
	if (db_num_rows($q10)<1 || db_result($q10,0,0)=="asistent") {
		zamgerlog("nastavnik/prijava_ispita privilegije (predmet pp$predmet, ag$ag)",3);
		zamgerlog2("nije nastavnik na predmetu", $predmet, $ag);
		biguglyerror("Nemate pravo pristupa ovoj opciji");
		return;
	} 
}


// Provjera ispita

$q20 = db_query("SELECT UNIX_TIMESTAMP(i.datum), k.gui_naziv FROM ispit as i, komponenta as k WHERE i.id=$ispit and i.komponenta=k.id");
if (db_num_rows($q20)<1) {
	niceerror("Nepostojeći ispit");
	zamgerlog("nepostojeci ispit $ispit ili nije sa predmeta (pp$predmet, ag$ag)", 3);
	return;
}



// Podaci za ispis

$finidatum = date("d. m. Y", db_result($q20,0,0));
$tip_ispita = db_result($q20,0,1);

$q30 = db_query("select naziv from predmet where id=$predmet");
$predmet_naziv = db_result($q30,0,0);



?>

<br/>
<h3><?=$predmet_naziv?> - Termini ispita</h3>

<h4><?=$tip_ispita?>, <?=$finidatum?></h4>

<a href="?sta=izvjestaj/termini_ispita&ispit=<?=$ispit;?>">Izvještaj o terminima</a> 

<?


// Informativna poruka 

if (!$_REQUEST['akcija']) {
	?>
	<p>Definisanjem jednog ili više termina ispita omogućujete studentima da se prijavljuju za ispit kroz Zamger koristeći modul "Prijava ispita".<br />
	Korištenje ove mogućnosti nije obavezno - ukoliko samo želite unijeti rezultate, nemojte kreirati termine.</p>
	
	<p><a href="?sta=nastavnik/ispiti&predmet=<?=$predmet?>&ag=<?=$ag?>"><<< Nazad</a></p>
	
	<?
}

$dan=0; // Ovo će biti promijenjeno u slučaju izmjene



// Provjera da li ispitni termin pripada ispitu
if ($termin) {
	$q40 = db_query("SELECT count(*) FROM ispit_termin as it, ispit as i WHERE it.id=$termin AND it.ispit=i.id AND i.id=$ispit ");
	if (db_result($q40,0,0)<1) {
		zamgerlog("termin ne pripada ispitu",3);
		zamgerlog2("id termina i ispita se ne poklapaju", $termin, $ispit);
		biguglyerror("Ispitni termin ne pripada datom ispitu"); 
		return;
	}
}



// AKCIJE

// Akcija koja briše ispitni termin

if ($_REQUEST['akcija']=="obrisi") {
	$q70 = db_query("select count(*) from student_ispit_termin where ispit_termin=$termin");
	$broj_studenata = db_result($q70,0,0);

	$q80 = db_query("select UNIX_TIMESTAMP(datumvrijeme) from ispit_termin where id=$termin");
	$datumvrijeme = date("d. m. Y. h:i:s", db_result($q80,0,0));

	?>
	<h4>Brisanje ispitnog termina <?=$datumvrijeme?></h4>
	<p>Za ovaj termin se do sada prijavilo <b><?=$broj_studenata?></b> studenata.<br />
	Da li ste sigurni da ga želite obrisati?</p>

	<?=genform("POST")?>
	<input type="hidden" name="akcija" value="obrisi_potvrda">
	<input type="submit" value=" Briši ">
	<input type="submit" name="povratak" value=" Nazad ">
	</form>
	<?
	return;
}


// Potvrda brisanja

if ($_REQUEST["akcija"]=="obrisi_potvrda" && $_REQUEST['povratak'] != " Nazad " && check_csrf_token()) {
	$q90 = db_query("DELETE FROM student_ispit_termin WHERE ispit_termin=$termin");
	$q95 = db_query("DELETE FROM ispit_termin WHERE id=$termin");
	zamgerlog("izbrisan ispitni termin $termin (pp$predmet, ag$ag)", 2);
	zamgerlog2("izbrisan ispitni termin", $termin, $predmet, $ag);
	nicemessage("Termin uspješno obrisan ");
}




// Tabela studenata koji su se prijavili za ovaj ispitni termin

if ($_REQUEST["akcija"]=="studenti") {
	if ($_REQUEST['subakcija']=="dodaj_studenta" && check_csrf_token()) {
		$student = intval($_REQUEST['student']);
		$q215 = db_query("select count(*) from student_ispit_termin where student=$student and ispit_termin=$termin");
		if (db_result($q215,0,0)>0)
			nicemessage("Student je već prijavljen na ovaj termin!");
		else {
			$q220 = db_query("insert into student_ispit_termin set student=$student, ispit_termin=$termin");
			zamgerlog2("nastavnik dodao studenta na termin", $student, $termin);
		}
	}
	if ($_REQUEST['subakcija']=="izbaci_studenta" && check_csrf_token()) {
		$student = intval($_REQUEST['student']);
		$q225 = db_query("delete from student_ispit_termin where student=$student and ispit_termin=$termin");
		zamgerlog2("nastavnik uklonio studenta sa termina", $student, $termin);
	}

	$q200 = db_query("select UNIX_TIMESTAMP(datumvrijeme) from ispit_termin where id=$termin");
	$datumvrijeme = date("d. m. Y. H:i:s", db_result($q200,0,0));

	$q200=db_query("SELECT o.ime, o.prezime, o.brindexa, o.id FROM osoba as o, student_ispit_termin as si WHERE o.id=si.student AND si.ispit_termin=$termin order by o.prezime, o.ime");


	?>
	<p><b>Tabela prijavljenih za: <?=$datumvrijeme?></b></p>

	<table border="0" cellspacing="1" cellpadding="2">
	<thead>
	<tr bgcolor="#999999">
		<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">R.br.</font></td>
		<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Prezime i ime</font></td>
		<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Broj indexa</font></td>
		<td>&nbsp;</td>
	</tr>
	</thead>
	<tbody>
	<?

	$brojac=1;
	$bili = array();
	while ($r200=db_fetch_row($q200)) {
		array_push($bili, $r200[3]);
		?>
		<tr>
			<td><?=$brojac?></td>
			<td><?=$r200[1]?> <?=$r200[0]?></td>
			<td><?=$r200[2]?></td>
			<td><?=genform("POST")?>
				<input type="hidden" name="akcija" value="studenti">
				<input type="hidden" name="subakcija" value="izbaci_studenta">
				<input type="hidden" name="student" value="<?=$r200[3]?>">
				<input type="submit" value="Izbaci" class="default">
				</form>
			</td>
		</tr>
		<?
		$brojac++;	
	}


	?>
	</table>
	<? if($brojac==1) print '<br>Do sada se niko nije prijavio za ovaj termin.'; ?>
	<?

	// Dodavanje studenta na termin
	print genform("POST");
	?>
	<br>
	<input type="hidden" name="subakcija" value="dodaj_studenta">
	Dodajte studenta na termin:<br>
	<select name="student">
	<?
	$q210 = db_query("select o.id, o.prezime, o.ime from osoba as o, student_predmet as sp, ponudakursa as pk where sp.student=o.id and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag order by o.prezime, o.ime");
	while ($r210 = db_fetch_row($q210)) {
		if (in_array($r210[0], $bili)) continue;
		print "<option value=\"$r210[0]\">$r210[1] $r210[2]</option>\n";
	}
	?>
	</select> <input type="submit" value=" Dodaj ">
	</form>
	<br><hr/><br>
	<?

	// Omogućićemo izmjenu ovog termina
	$_REQUEST['akcija']="izmijeni";
}


// U ovoj akciji se samo iz baze podataka uzimaju vrijednosti, konkretna promjena se vrsi u akciji "izmijeni_potvrda"

if ($_REQUEST["akcija"]=="izmijeni") {
	if (!$termin) {
		niceerror("Nije izabran termin.");
		return 0;
	}

	$q100 = db_query("SELECT UNIX_TIMESTAMP(datumvrijeme), UNIX_TIMESTAMP(deadline) , maxstudenata FROM ispit_termin WHERE id=$termin");

	$t1 = db_result($q100,0,0);
	$dan = date('d',$t1); $mjesec = date('m',$t1); $godina = date('Y',$t1); $sat = date('H',$t1); $minuta = date('i',$t1); $sekunda = date('s',$t1);

	$t2 = db_result($q100,0,1);
	$dan1 = date('d',$t2); $mjesec1 = date('m',$t2); $godina1 = date('Y',$t2); $sat1 = date('H',$t2); $minuta1 = date('i',$t2); $sekunda1 = date('s',$t2);

	$limit = db_result($q100,0,2);
}


// Potvrda izmjene postojeceg ispitnog termina

if ($_POST['akcija'] == 'izmijeni_potvrda' && check_csrf_token()) {
	$limit = intval($_POST['limit']);

	$dan = intval($_POST['day']);
	$mjesec = intval($_POST['month']);
	$godina = intval($_POST['year']);
	$sat = intval($_POST['sat']);
	$minuta = intval($_POST['minuta']);
	$sekunda = intval($_POST['sekunda']);
	$dan1 = intval($_POST['1day']);
	$mjesec1 = intval($_POST['1month']);
	$godina1 = intval($_POST['1year']);
	$sat1 = intval($_POST['sat1']);
	$minuta1 = intval($_POST['minuta1']);
	$sekunda1 = intval($_POST['sekunda1']);

	$t1 = mktime($sat,$minuta,$sekunda,$mjesec,$dan,$godina);
	$t2 = mktime($sat1,$minuta1,$sekunda1,$mjesec1,$dan1,$godina1);


	//Provjera ispravnosti

	if (!checkdate($mjesec,$dan,$godina)) {
		niceerror("Odabrani datum je nemoguć");
	}
	else if ($sat<0 || $sat>24 || $minuta<0 || $minuta>60 || $sekunda<0 || $sekunda>60) {
		niceerror("Odabrano vrijeme je nemoguće");
	}
	else if (!checkdate($mjesec1,$dan1,$godina1)) {
		niceerror("Odabrani datum za rok prijave je nemoguć");
	}
	else if ($sat1<0 || $sat1>24 || $minuta1<0 || $minuta1>60 || $sekunda1<0 || $sekunda1>60) {
		niceerror("Odabrano vrijeme za rok prijave je nemoguće");
	}
	else if ($limit<=0){
		niceerror("Maksimalni broj studenata na ispitu mora biti veći od nule");
	}
	else if ($t1<$t2){
		niceerror("Krajnji rok za prijavu ispita mora raniji od tačnog vremena održavanja ispita");
	}
	else {
		nicemessage("Uspješno izmijenjen termin.");
		$q110=db_query("UPDATE ispit_termin SET datumvrijeme=FROM_UNIXTIME('$t1') , maxstudenata=$limit , deadline=FROM_UNIXTIME('$t2'), ispit=$ispit WHERE id=$termin");
		zamgerlog("izmijenjen ispitni termin", 2);
		zamgerlog2("izmijenjen ispitni termin", $termin);
	}

	// Radi ljepšeg ispisa, dodajemo nule
	$dan=nuliraj_broj($dan); $mjesec=nuliraj_broj($mjesec); $sat=nuliraj_broj($sat); $minuta=nuliraj_broj($minuta); $sekunda=nuliraj_broj($sekunda);
	$dan1=nuliraj_broj($dan1); $mjesec1=nuliraj_broj($mjesec1); $sat1=nuliraj_broj($sat1); $minuta1=nuliraj_broj($minuta1); $sekunda1=nuliraj_broj($sekunda1);
}


// Dodavanje novog ispitnog termina

if ($_POST['akcija'] == 'dodaj_potvrda' && check_csrf_token()) {
	$limit = intval($_POST['limit']);

	$dan = intval($_POST['day']);
	$mjesec = intval($_POST['month']);
	$godina = intval($_POST['year']);
	$sat = intval($_POST['sat']);
	$minuta = intval($_POST['minuta']);
	$sekunda = intval($_POST['sekunda']);
	$dan1 = intval($_POST['1day']);
	$mjesec1 = intval($_POST['1month']);
	$godina1 = intval($_POST['1year']);
	$sat1 = intval($_POST['sat1']);
	$minuta1 = intval($_POST['minuta1']);
	$sekunda1 = intval($_POST['sekunda1']);

	$t1 = mktime($sat,$minuta,$sekunda,$mjesec,$dan,$godina);
	$t2 = mktime($sat1,$minuta1,$sekunda1,$mjesec1,$dan1,$godina1);


	//Provjera ispravnosti

	if (!checkdate($mjesec,$dan,$godina)) {
		niceerror("Odabrani datum je nemoguć");
	}
	else if ($sat<0 || $sat>24 || $minuta<0 || $minuta>60 || $sekunda<0 || $sekunda>60) {
		niceerror("Odabrano vrijeme je nemoguće");
	}
	else if (!checkdate($mjesec1,$dan1,$godina1)) {
		niceerror("Odabrani datum za rok prijave je nemoguć");
	}
	else if ($sat1<0 || $sat1>24 || $minuta1<0 || $minuta1>60 || $sekunda1<0 || $sekunda1>60) {
		niceerror("Odabrano vrijeme za rok prijave je nemoguće");
	}
	else if ($limit<=0){
		niceerror("Maksimalni broj studenata na ispitu mora biti veći od nule");
	}
	else if ($t1<$t2){
		niceerror("Krajnji rok za prijavu ispita mora raniji od tačnog vremena održavanja ispita");
	}
	else {
		nicemessage("Uspješno kreiran novi termin.");
		$q=db_query("INSERT INTO ispit_termin SET datumvrijeme=FROM_UNIXTIME('$t1'), maxstudenata=$limit , ispit=$ispit , deadline=FROM_UNIXTIME('$t2')");
		zamgerlog2("kreiran novi ispitni termin", db_insert_id(), $predmet, $ag);
		zamgerlog("kreiran novi ispitni termin pp$predmet, ag$ag", 2);
	}

	// Radi ljepšeg ispisa, dodajemo nule
	$dan=nuliraj_broj($dan); $mjesec=nuliraj_broj($mjesec); $sat=nuliraj_broj($sat); $minuta=nuliraj_broj($minuta); $sekunda=nuliraj_broj($sekunda);
	$dan1=nuliraj_broj($dan1); $mjesec1=nuliraj_broj($mjesec1); $sat1=nuliraj_broj($sat1); $minuta1=nuliraj_broj($minuta1); $sekunda1=nuliraj_broj($sekunda1);
}




// GLAVNI EKRAN

// Tabela objavljenih termina za predmet

$q10=db_query("SELECT id, UNIX_TIMESTAMP(datumvrijeme), UNIX_TIMESTAMP(deadline), maxstudenata FROM ispit_termin WHERE ispit=$ispit order by datumvrijeme");

?>
<b>Objavljeni termini:</b>
<br><br>
<table border="0" cellspacing="1" cellpadding="2">
<thead>
<tr bgcolor="#999999">
	<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">R.br.</font></td>
	<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Vrijeme termina</font></td>
	<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Rok za prijavu</font></td>
	<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Prijavljeno</font></td>
	<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Max.</font></td>
	<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Opcije</font></td>
</tr>
</thead>
<tbody>

<?
$brojac=1;
$uk_prijavljeno=0;

while ($r10=db_fetch_row($q10)) {
	$id_termina = $r10[0];
	$vrijeme_termina = date("d.m.Y. H:i",date($r10[1]));
	$rok_prijave = date("d.m.Y. H:i",date($r10[2]));
	$max_studenata = $r10[3];

	$q20 = db_query("select count(*) from student_ispit_termin where ispit_termin=$id_termina");
	$prijavljeno = db_result($q20,0,0);
	$uk_prijavljeno += $prijavljeno;

	?>
	<tr>
		<td><?=$brojac ?></td>
		<td align="center"><?=$vrijeme_termina?></td>
		<td align="center"><font color="#FF0000"><?=$rok_prijave?></font></td>
		<td align="center"><?=$prijavljeno?></td>
		<td align="center"><?=$max_studenata?></td>
		<td align="center">
			<a href="?sta=nastavnik/prijava_ispita&akcija=izmijeni&termin=<?=$id_termina?>&ispit=<?=$ispit?>&predmet=<?=$predmet?>&ag=<?=$ag?>">Izmijeni</a>&nbsp;&nbsp;
			<a href="?sta=nastavnik/prijava_ispita&akcija=obrisi&termin=<?=$id_termina?>&ispit=<?=$ispit?>&predmet=<?=$predmet?>&ag=<?=$ag?>">Obriši</a>&nbsp;&nbsp;
			<a href="?sta=nastavnik/prijava_ispita&akcija=studenti&termin=<?=$id_termina?>&ispit=<?=$ispit?>&predmet=<?=$predmet?>&ag=<?=$ag?>">Studenti</a>&nbsp;&nbsp;
			<a href="?sta=izvjestaj/termini_ispita&termin=<?=$id_termina?>">Izvještaj</a>
		</td>
	</tr>
	<?
	$brojac++;
}

?>
	<tr>
		<td colspan="3" align="right">UKUPNO: &nbsp;</td>
		<td align="center"><?=$uk_prijavljeno?></td>
		<td colspan="2">&nbsp;</td>
	</tr>
</tbody></table>
<? if ($brojac==1) { ?><br>Nije registrovan nijedan termin za ovaj ispit<br><br><? } ?>
<br><hr />
<?




// Forma za unos novog ispitnog termina ili editovanje postojećeg

if ($dan==0) {
	$dan=$dan1=date('d'); $mjesec=$mjesec1=date('m'); $godina=$godina1=date('Y');
	$sat=$sat1=date('H'); $minuta=$minuta1=date('i'); $sekunda=$sekunda1=date('s');
	$limit=0;
	// Ako akcija nije izmjena, brišemo vrijednost varijable termin
	$termin=0;
}


?>
	<?=genform("POST")?>
	<input type="hidden" name="termin" value="<?=$termin?>">
	<input type="hidden" name="akcija" value="<? 
		if($termin<=0) print 'dodaj_potvrda';
		else print 'izmijeni_potvrda';
	?>">

	<p><b><? if ($_REQUEST["akcija"]=="izmijeni" || $_REQUEST["akcija"]=="studenti") print 'Izmjena termina';
	   else print 'Registrovanje novog termina';
        ?></b>

	<br/><br/>
	Datum i vrijeme ispita:<br/>
	<?=datectrl($dan, $mjesec, $godina); ?>

	&nbsp;&nbsp; <input type="text" name="sat" size="2" value="<?=$sat?>"> <b>:</b> <input type="text" name="minuta" size="2" value="<?=$minuta?>"> <b>:</b> <input type="text" name="sekunda" size="2" value="<?=$sekunda?>">
	<br/><br/>

	Krajnji rok za prijavu ispita:
	<br/>
	<?=datectrl($dan1, $mjesec1, $godina1, "1"); ?>

	&nbsp;&nbsp; <input type="text" name="sat1" size="2" value="<?=$sat1?>"> <b>:</b> <input type="text" name="minuta1" size="2" value="<?=$minuta1?>"> <b>:</b> <input type="text" name="sekunda1" size="2" value="<?=$sekunda1?>">
	<br/><br/>
	Maksimalan broj studenata: <input type="text" size="2" name="limit" value="<?=$limit?>"  class="default">
	<br/><br/>

	<input type="submit" value="<? 
	if ($_REQUEST["akcija"]=="izmijeni" || $_REQUEST["akcija"]=="studenti") print 'Izmijeni'; else print 'Dodaj';
	?>"  class="default"><br/><br/>
	<a href="?sta=nastavnik/ispiti&predmet=<?=$predmet?>&ag=<?=$ag?>">&lt;&lt;&lt; Nazad</a><br/>
</form>

<?






}

?>
