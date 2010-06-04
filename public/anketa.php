<?php 

// PUBLIC/ANKETA - stranica za ispunjavanje ankete

function public_anketa() {

	global $_lv_; 

	$q10 = myquery("select id,naziv from akademska_godina where aktuelna=1");
	$ag = mysql_result($q10,0,0);

	// Uzimamo id aktivne ankete
	$q09 = myquery("select id,naziv,UNIX_TIMESTAMP(datum_zatvaranja) from anketa_anketa where aktivna=1 and akademska_godina=$ag order by id desc");
	if (mysql_num_rows($q09)==0) {
		biguglyerror("Ne postoji aktivna anketa!");
		return;
	}
	$anketa = mysql_result($q09,0,0);
	$naziv_ankete = mysql_result($q09,0,1);
	$rok = mysql_result($q09,0,2);
	if (time () > $rok){
		biguglyerror("Isteklo vrijeme za ispunjavanje ankete");
		return;
	}

	// Kupimo kod koji je student unio
	$hash_code = my_escape($_POST['hash_code']);

	if ($_POST['akcija'] == "finish" || $_POST['akcija'] == "prikazi") {
		// Provjeravamo da li kod postoji i da li je već iskorišten (polje zavrsena)
		$q590 = myquery("SELECT id, predmet, zavrsena FROM anketa_rezultat WHERE unique_id='$hash_code'");
		if (mysql_num_rows($q590)==0) {
			// dio koji ide ako dati hash ne postoji u bazi tj. ako student pokušava da izmisli hash :P
			?>
			<center>
				<p>Greška: neispravan kod '<?=$hash_code?>'.</p>
				<a href="index.php">Nazad na početnu stranicu</a>
			</center>
			<?	
		}
	
		$rezultat = mysql_result($q590,0,0);
		$predmet = mysql_result($q590,0,1);
		$zavrsena = mysql_result($q590,0,2);
	
		if ($zavrsena != 'N') {
			?>
			<center>
				<p>Već ste jednom popunili anketu. Nema mogućnosti izmjene jednom popunjene ankete.</p>
				<a href="index.php">Nazad na početnu stranicu</a>
			</center>
			<?	
		}
	}


	// da li je student završio anketu
	if ($_POST['akcija'] == "finish" && check_csrf_token()) {
		
		// broj rank pitanja
		$q203 = myquery("SELECT count(*) FROM anketa_pitanje WHERE anketa=$anketa and tip_pitanja=1");
		$broj_rank_pitanja = mysql_result($q203,0,0);
		
		$j=1;
		for ($i=0; $j<=$broj_rank_pitanja ; $i++){
			if ($_POST['izbor'.$j]){ // provjeravamo prvo da li pitanje uopce odgovoreno
				$izbori[$i] = $_POST['izbor'.$j];
				$id_pitanja[$i] = $_POST['id_pitanja'.$j];
			}
			else
				$i--;
			$j++;
		}
		
		// ubaciti sve odgovore u tabelu odgovori_rank
		for ($i=0; $i<count($izbori); $i++) {
			$q590 = myquery("insert into anketa_odgovor_rank set rezultat=$rezultat, pitanje=$id_pitanja[$i], izbor_id=$izbori[$i]");
		}
		
		// broj esejskih pitanja
		$result204 = myquery("SELECT count(*) FROM anketa_pitanje WHERE anketa=$anketa and tip_pitanja =2");
		$broj_esej_pitanja = mysql_result($result204,0,0);
		
		for ($i=0; $i<$broj_esej_pitanja; $i++) {
			$komentar[$i] = my_escape($_POST['komentar'.$j]);
			$id_pitanja[$i] = $_POST['id_pitanja'.$j];
			$j++;
		}
		// ubaciti sve odgoovre u tabelu odgovori_text
		for ($i=0; $i<$broj_esej_pitanja; $i++) {
			$q590 = myquery("insert into anketa_odgovor_text set rezultat=$rezultat, pitanje=$id_pitanja[$i], odgovor='$komentar[$i]'");
		}
		
		// nakon uspjesnog ispunjenja ankete postaviti i polje zavrsena na true u tabeli razultati
		$q600 = myquery("update anketa_rezultat set zavrsena='Y' where id=$rezultat");
		
		?>
		<center>
			<p> Hvala na ispunjavanju ankete. </p>
			<a href="index.php">Nazad na početnu stranicu</a>
		</center>
		<?
	}

	//  ----------------  AKCIJA PRIKAZI dio koji ide nakon sto je student unio kod za anketu te stistnuo dugme ----------------------
	else if ($_POST['akcija'] == "prikazi" && check_csrf_token()) {
		$q011= myquery("select naziv from predmet where id = $predmet");
		$naziv_predmeta = mysql_result($q011,0,0);
	
		?>
		<center>
			<h2>Anketa za predmet <?= $naziv_predmeta?></h2>
		</center>
		<?=genform("POST")?>
		<input type="hidden" name="akcija" value="finish">
		<input type="hidden" name="hash_code" value="<?=$hash_code?>">

		<table align="center" cellpadding="4" border="0" >
			<tr>
				<td colspan = '6'>
				<hr/> 
				<strong> U sljedećoj tabeli izaberite samo jednu od ocjena za iskazanu tvrdnju na skali ocjena od 1 (apsolutno se ne slažem) do 5 (apsolutno se slažem). </strong>
				</td>
			</tr>
			<tr><td colspan='6'><hr/></td></tr>
			<? $broj_pitanja = Ubaci_pitanje(1,$anketa,1);?>
			<tr><td colspan='6'><hr/></td></tr>
			<? $broj_pitanja = Ubaci_pitanje(2,$anketa,$broj_pitanja);?>
			<tr><td colspan='6'><hr/></td></tr>

		</table>
		<br />
		<table align="center">
			<tr>
				<td>
					<input align="middle"  type="submit" value="Pošalji" />
				</td>
			</tr>
		</table>
		</form>

		<?
	} // ---------------  KRAJ AKCIJA PRIKAZI  --------------------------

	else	{
		?>
		<?=genform("POST")?>
		<table align="center" cellpadding="0">
			<tr>
				<td>
					<br/>
					Unesite kod koji ste dobili za ispunjavanje ankete: &nbsp;
				</td>
				<td>
					<br/>
					<input type="hidden" id="akcija" name="akcija" value="prikazi">
					<input type="text" id="kod" name="hash_code"  size="60">	
				</td>
			</tr>
			<tr>
				<td colspan="2" align="center">
					<br/>
					 <input type="submit" value="Pošalji">
				</td>
			</tr>
		</table>
		</form>

		<?
	}
}//  ----------------- KRAJ FUNKCIJE PUBLIC_ANKETA -------------


// Pomoćna funkcija ubacuje sva pitanja datog tipa u anketi
// Vraća zadnji redni broj

function Ubaci_pitanje($tip_pitanja,$id_ankete,$pocetni_broj) {

	// kupitmo pitanja u zavisnosti od argumenta koji je poslan
	$results1 = myquery("select id,tekst from anketa_pitanje p where tip_pitanja=$tip_pitanja and anketa=$id_ankete");
	
	$par = 1;
	$j = $pocetni_broj;

	// rank pitanja
	if ($tip_pitanja == 1) {
		while ($pitanje = mysql_fetch_assoc($results1)) {
			$id = $pitanje['id'];
			$tekst = nl2br($pitanje['tekst']); // omogućujemo nove redove
			if ($par==1) {
				print "<tr> "; 
				$par = 2;
			} else {
				print "<tr bgcolor='#CCCCFF'> "; 
				$par = 1;
			}

			// input hiden kako bismo znali id pitanja za kasnije cuvanje rezultata :)
			print "<td>$tekst  <input type='hidden' name='id_pitanja$j' value=$id></td>";
			
			for ($i=1; $i<=5; $i++) {
				echo "<td><input type='radio' name='izbor"."$j'"." value="."$i /> ".$i."  </td>";
			}
			echo "</tr>";
			$j++;
		}	
	}

	// esejska pitanja
	else if ($tip_pitanja == 2) {
		while ($pitanje = mysql_fetch_assoc($results1)) {
			$id = $pitanje['id'];
			$tekst = nl2br($pitanje['tekst']);
			
			echo "<tr><td colspan='6'>".$tekst." <input type='hidden' name='id_pitanja$j' value=$id></td></tr>";
			echo "<tr>";
			echo "<td colspan='6' align='center'><textarea name='komentar$j' rows='7' cols='40'></textarea></td>";
			echo "</tr>";
			$j++;
		}
	}
	return $j;
}

?>
