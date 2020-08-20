<?

// NASTAVNIK/PRIJAVA_ISPITA - upravljanje terminima za prijavljivanje studenata na ispit



function nastavnik_prijava_ispita() {
	
	require_once("lib/formgen.php"); // datectrl
	require_once("lib/utility.php"); // nuliraj_broj
	
	
	global $_api_http_code;
	
	//parametri
	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);
	$ispit = intval($_REQUEST['ispit']);
	$termin = intval($_REQUEST['termin']);
	
	$course = api_call("course/$predmet/$ag");
	
	// Naziv predmeta
	$predmet_naziv = $course['courseName'];
	
	// Da li korisnik ima pravo ući u modul?
	
	if ($_api_http_code == "403") {
		zamgerlog("nastavnik/grupe privilegije (predmet pp$predmet)",3);
		zamgerlog2("nije nastavnik na predmetu", $predmet, $ag);
		biguglyerror("Nemate pravo pristupa ovoj opciji");
		return;
	}
	
	$exam = api_call("exam/$ispit", [ "resolve" => ["CourseActivity"], "withResults" => true ] );
	if ($_api_http_code != "200") {
		niceerror("Nepostojeći ispit");
		zamgerlog("nepostojeci ispit $ispit ili nije sa predmeta (pp$predmet, ag$ag)", 3);
		return;
	}
	
	
	
	// Podaci za ispis
	$finidatum = date("d. m. Y", db_timestamp($exam['date']));
	$tipispita = $exam['CourseActivity']['id'];
	$fini_naziv_ispita = $exam['CourseActivity']['name'];
	
	
	
	?>
	
	<br/>
	<h3><?=$predmet_naziv?> - Termini ispita</h3>
	
	<h4><?=$fini_naziv_ispita?>, <?=$finidatum?></h4>
	
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
		$event = api_call("event/$termin", [ "resolve" => [ "Person" ] ] );
		if ($_api_http_code != "200") {
			zamgerlog("termin ne pripada ispitu",3);
			zamgerlog2("id termina i ispita se ne poklapaju", $termin, $ispit);
			biguglyerror("Ispitni termin ne pripada datom ispitu");
			return;
		}
	}
	
	
	
	// AKCIJE
	
	// Akcija koja briše ispitni termin
	
	if ($_REQUEST['akcija']=="obrisi") {
		$datumvrijeme = date("d. m. Y. h:i:s", db_timestamp($event['dateTime']));
	
		?>
		<h4>Brisanje ispitnog termina <?=$datumvrijeme?></h4>
		<p>Za ovaj termin se do sada prijavilo <b><?=$event['registered']?></b> studenata.<br />
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
		api_call("event/$termin", [], "DELETE");
		if ($_api_http_code == "204") {
			zamgerlog("izbrisan ispitni termin $termin (pp$predmet, ag$ag)", 2);
			zamgerlog2("izbrisan ispitni termin", $termin, $predmet, $ag);
			nicemessage("Termin uspješno obrisan ");
		} else {
			niceerror("Neuspješno brisanje termina: kod $_api_http_code");
		}
	}
	
	
	
	
	// Tabela studenata koji su se prijavili za ovaj ispitni termin
	
	if ($_REQUEST["akcija"]=="studenti") {
		if ($_REQUEST['subakcija']=="dodaj_studenta" && check_csrf_token()) {
			$student = intval($_REQUEST['student']);
			$result = api_call("event/$termin/register/$student", [], "POST");
			if ($_api_http_code == "201") {
				zamgerlog2("nastavnik dodao studenta na termin", $student, $termin);
				$event = api_call("event/$termin", [ "resolve" => [ "Person" ] ] );
			} else {
				niceerror("Neuspješno dodavanje studenta na termin ($_api_http_code): " . $result['message']);
			}
			// Ponovo preuzimamo spisak studenata jer se promijenio
			$event = api_call("event/$termin", [ "resolve" => [ "Person" ] ] );
		}
		if ($_REQUEST['subakcija']=="izbaci_studenta" && check_csrf_token()) {
			$student = intval($_REQUEST['student']);
			api_call("event/$termin/register/$student", [], "DELETE");
			if ($_api_http_code == "204") {
				zamgerlog2("nastavnik uklonio studenta sa termina", $student, $termin);
			} else {
				niceerror("Neuspješno uklanjanje studenta sa termin: kod $_api_http_code");
			}
			// Ponovo preuzimamo spisak studenata jer se promijenio
			$event = api_call("event/$termin", [ "resolve" => [ "Person" ] ] );
		}
	
		$datumvrijeme = date("d. m. Y. H:i:s", db_timestamp($event['dateTime']));
	
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
	
		$bili = array();
		$brojac = 1;
		usort($event['students'], function ($s1, $s2) {
			if ($s1['surname'] == $s2['surname']) return bssort($s1['name'], $s2['name']);
			return bssort($s1['surname'], $s2['surname']);
		});
		foreach($event['students'] as $student) {
			array_push($bili, $student['id']);
			?>
			<tr>
				<td><?=$brojac?></td>
				<td><?=$student['surname']?> <?=$student['name']?></td>
				<td><?=$student['studentIdNr']?></td>
				<td><?=genform("POST")?>
					<input type="hidden" name="akcija" value="studenti">
					<input type="hidden" name="subakcija" value="izbaci_studenta">
					<input type="hidden" name="student" value="<?=$student['id']?>">
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
		
		$virtualGroup = api_call("group/course/$predmet/allStudents", [ "year" => $ag, "names" => true ] );
		usort($virtualGroup['members'], function ($s1, $s2) {
			if ($s1['student']['surname'] == $s2['student']['surname']) return bssort($s1['student']['name'], $s2['student']['name']);
			return bssort($s1['student']['surname'], $s2['student']['surname']);
		});
		foreach($virtualGroup['members'] as $member) {
			if (in_array($member['student']['id'], $bili)) continue;
			?>
			<option value="<?=$member['student']['id']?>"><?=$member['student']['surname'] . " " . $member['student']['name']?></option>
			<?
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
	
		$t1 = db_timestamp($event['dateTime']);
		$dan = date('d',$t1); $mjesec = date('m',$t1); $godina = date('Y',$t1); $sat = date('H',$t1); $minuta = date('i',$t1); $sekunda = date('s',$t1);
		
		$t2 = db_timestamp($event['deadline']);
		$dan1 = date('d',$t2); $mjesec1 = date('m',$t2); $godina1 = date('Y',$t2); $sat1 = date('H',$t2); $minuta1 = date('i',$t2); $sekunda1 = date('s',$t2);
	
		$limit = $event['maxStudents'];
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
		
		$dan=nuliraj_broj($dan); $mjesec=nuliraj_broj($mjesec); $sat=nuliraj_broj($sat); $minuta=nuliraj_broj($minuta); $sekunda=nuliraj_broj($sekunda);
		$dan1=nuliraj_broj($dan1); $mjesec1=nuliraj_broj($mjesec1); $sat1=nuliraj_broj($sat1); $minuta1=nuliraj_broj($minuta1); $sekunda1=nuliraj_broj($sekunda1);
		
		$db_date = "$godina-$mjesec-$dan $sat:$minuta:$sekunda";
		$db_date1 = "$godina1-$mjesec1-$dan1 $sat1:$minuta1:$sekunda1";
	
	
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
			$eventObj = array_to_object( [ "id" => $termin, "CourseUnit" => [ "id" => $predmet ], "AcademicYear" => [ "id" => $ag ], "dateTime" => $db_date, "deadline" => $db_date1,  "maxStudents" => $limit, "CourseActivity" => [ "id" => $tipispita ], "options" => $ispit ] );
			$result = api_call("event/$termin", $eventObj, "PUT");
			if ($_api_http_code == "201") {
				nicemessage("Uspješno izmijenjen termin.");
				zamgerlog("izmijenjen ispitni termin", 2);
				zamgerlog2("izmijenjen ispitni termin", $termin);
			} else {
				niceerror("Neuspješna izmjena termina ($_api_http_code): " . $result['message']);
			}
		}
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
		
		$dan=nuliraj_broj($dan); $mjesec=nuliraj_broj($mjesec); $sat=nuliraj_broj($sat); $minuta=nuliraj_broj($minuta); $sekunda=nuliraj_broj($sekunda);
		$dan1=nuliraj_broj($dan1); $mjesec1=nuliraj_broj($mjesec1); $sat1=nuliraj_broj($sat1); $minuta1=nuliraj_broj($minuta1); $sekunda1=nuliraj_broj($sekunda1);
		
		$db_date = "$godina-$mjesec-$dan $sat:$minuta:$sekunda";
		$db_date1 = "$godina1-$mjesec1-$dan1 $sat1:$minuta1:$sekunda1";
	
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
			$eventObj = array_to_object( [ "id" => 0, "CourseUnit" => [ "id" => $predmet ], "AcademicYear" => [ "id" => $ag ], "dateTime" => $db_date, "deadline" => $db_date1,  "maxStudents" => $limit, "CourseActivity" => [ "id" => $tipispita ], "options" => $ispit ] );
			$result = api_call("event/course/$predmet/$ag", $eventObj, "POST");
			if ($_api_http_code == "201") {
				nicemessage("Uspješno kreiran novi termin.");
				zamgerlog2("kreiran novi ispitni termin", db_insert_id(), $predmet, $ag);
				zamgerlog("kreiran novi ispitni termin pp$predmet, ag$ag", 2);
			} else {
				niceerror("Neuspješno kreiranje termina ($_api_http_code): " . $result['message']);
			}
		}
	}
	
	
	
	
	// GLAVNI EKRAN
	
	// Tabela objavljenih termina za predmet
	
	$allEvents = api_call("event/exam/$ispit")["results"];
	
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
	
	$uk_prijavljeno = 0; $brojac = 1;
	foreach($allEvents as $event) {
		$id_termina = $event['id'];
		$vrijeme_termina = date("d.m.Y. H:i", db_timestamp($event['dateTime']));
		$rok_prijave = date("d.m.Y. H:i", db_timestamp($event['deadline']));
		$max_studenata = $event['maxStudents'];
	
		$prijavljeno = $event['registered'];
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
	
	// Ako unosimo novi termni za ispit - daj datum za koji je ispit postavljen
	if($_REQUEST["akcija"] != "izmijeni" and $_REQUEST["akcija"] != "studenti"){
		$datum = $exam['date'];
		$dan = explode("-", $datum)[2];
		$mjesec = explode("-", $datum)[1];
		$godina = explode("-", $datum)[0];
	
		$dan1 = $dan; $mjesec1 = $mjesec; $godina1 = $godina;
		
		$sat = "09";  $minuta = "00";  $sekunda = "00";
		$sat1 = "09"; $minuta1 = "00"; $sekunda1 = "00";
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
			<a href="?sta=nastavnik/ispiti&predmet=<?=$predmet?>&ag=<?=$ag?>">&lt;&lt;&lt; Nazad</a></p>
	</form>
	
	<?
}

?>
