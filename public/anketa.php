<?php 

// PUBLIC/ANKETA - stranica za ispunjavanje ankete

function public_anketa(){
	global $_lv_; 
	$q10 = myquery("select id,naziv from akademska_godina where aktuelna=1");
	$ag = mysql_result($q10,0,0);

	// uzimamo id aktivne ankete
	$q01 = myquery("select id from anketa where  akademska_godina=$ag and aktivna = 1");
	if (mysql_num_rows($q01)==0){
		biguglyerror("Ne postoji aktivna anketa!");
		return;
	}
	$id_ankete = mysql_result($q01,0,0);

	$q09= myquery("select id,naziv,UNIX_TIMESTAMP(datum_zatvaranja) from anketa where aktivna=1 and akademska_godina=$ag");
	$anketa = mysql_result($q09,0,0);
	$naziv= mysql_result($q09,0,1);
	$rok=mysql_result($q09,0,2);
	if (time () > $rok){
		biguglyerror("Isteklo vrijeme za ispunjavanje ankete");
		return;
	}
	
	// da li je student zavrsio anketu 
	if ($_POST['akcija'] == "finish" && check_csrf_token()) {
			
		$id_rezultata = $_POST['id_rezultata'];
		$id_ankete = $_POST['id_ankete'];
				
		// broj rank pitanja
		$q203=myquery("SELECT count(*) FROM pitanje WHERE anketa =$id_ankete and tip_pitanja =1");
		$broj_rank_pitanja= mysql_result($q203,0,0);
		 
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
		for ($i=0; $i< sizeof($izbori) ; $i++){
			$q590 = myquery("insert into odgovor_rank set rezultat=$id_rezultata, pitanje=$id_pitanja[$i], izbor_id=$izbori[$i]");
		}
		
		// broj esejskih pitanja
		$result204=myquery ("SELECT count(*) FROM pitanje WHERE anketa =$id_ankete and tip_pitanja =2");
		$broj_esej_pitanja= mysql_result($result204,0,0);
		 
		for ($i=0; $i<$broj_esej_pitanja ; $i++){
				$komentar[$i] = my_escape($_POST['komentar'.$j]);
				$id_pitanja[$i] = $_POST['id_pitanja'.$j];
				$j++;
		}
		// ubaciti sve odgoovre u tabelu odgovori_text
		for ($i=0; $i<$broj_esej_pitanja ; $i++){
			$q590 = myquery("insert into odgovor_text set rezultat=$id_rezultata, pitanje=$id_pitanja[$i], odgovor='$komentar[$i]'");
		}
		
		?>	
    	<center>
    		<p> Hvala na ispunjavanju ankete. </p>
    		<a href="index.php"> Nazad na pocetnu </a>
    	</center>
		<?
        // nakon uspjesnog ispunjenja ankete postaviti i polje zavrsena na true u tabeli razultati
        $q600 = myquery("update rezultat set zavrsena='Y' where id=$id_rezultata");
	}

	//  ----------------  AKCIJA PRIKAZI dio koji ide nakon sto je student unio kod za anketu te stistnuo dugme ----------------------
	else if($_POST['akcija'] == "prikazi") {
		// kupimo kod koji je student unio
		$unique_hash_code = my_escape($_POST['kod']);
		
		// provjeravamo da li je dati student zatrazio kod te da li je vec ispunjavao datu anketu sa poljem zavrsena
		$q590 = myquery("SELECT count( * ),id,predmet FROM rezultat WHERE unique_id = '$unique_hash_code' AND zavrsena = 'N' GROUP BY id, predmet");
		
		if ( !mysql_num_rows($q590) )	{
			// dio koji ide ako dati hesh ne postoji u bazi tj ako student pokusava da izmisli hesh :P
			?>
			<center>
				<p> Zao nam je ali ili ste vec ispunili anketu ili dati kod ne postoji u bazi!! </p>
				<a href="index.php"> Nazad na pocetnu </a>
			</center>
			<?	
		}
		else  { // else 15   uspjesno 
		
			$id_rezultata =mysql_result($q590,0,1);
			$predmet = mysql_result($q590,0,2);
			$q011= myquery("select naziv from predmet where id = $predmet");
			$naziv_predmeta = mysql_result($q011,0,0);
		
			?>
			<center>
				<h2> Anketa za predmet <?= $naziv_predmeta?> </h2>
			</center>
			<?=genform("POST")?>
				<input type="hidden" name="akcija" value="finish">
				<input type="hidden" name="id_rezultata" value="<?=$id_rezultata?>">
				<input type="hidden" name="id_ankete" value="<?=$id_ankete?>">
				
				<table align="center" cellpadding="4" border="0" >
					<tr>  
                    	<td colspan = '6'>
                        	<hr/> 
                            <strong> U sljedecoj tabeli  izaberite samo jednu od ocjena za iskazanu tvrdnju na skali ocjena od 1 (najlosija)  do 5 (najbolja). </strong>
                        </td>
                    </tr>;
					<?php 
                        echo "<tr>  <td colspan = '6'><hr/> </td></tr>";
                        $broj_pitanja = Ubaci_pitanje(1,$id_ankete,1);
                        echo "<tr>  <td colspan = '6'><hr/> </td></tr>";
                        $broj_pitanja = Ubaci_pitanje(2,$id_ankete,$broj_pitanja); 
                        echo "<tr>  <td colspan = '6'><hr/> </td></tr>";
                    ?>
				</table>
				<br />
				<table align="center">
					<tr> 
						<td>
						   <input align="middle"  type="submit" value="Posalji" />
						</td>
					</tr>
				</table>
			</form>
	
	
		<?
		} // kraj od else 15
	} // ---------------  KRAJ AKCIJA PRIKAZI  --------------------------
	else	{
		?>
		<?=genform("POST")?>
		<table align="center" cellpadding="0">
			<tr>
				<td>
					<br/>
					Unesite kod koji ste dobili za ispunjavanje anekte: &nbsp;	
				</td>
				<td>
					<br/>
					<input type="hidden" id="akcija" name="akcija" value="prikazi">
					<input type="text" id="kod" name="kod"  size="60">	
				</td>
			</tr>
			<tr>
				<td colspan="2" align="center">
					<br/>
					 <input type="submit" value="Posalji">
				</td>
			</tr>
		</table>
		</form>
		<?	
	}
}//  ----------------- KRAJ FUNKCIJE PUBLIC_ANKETA -------------

function Ubaci_pitanje($tip_pitanja,$id_ankete,$j){
	// kupitmo pitanja u zavisnosti od argumenta koji je poslan
	$results1= myquery("select id,tekst from pitanje p where tip_pitanja = $tip_pitanja and anketa=$id_ankete");
	
	$par =1;
	// ako je pitanje rank
	if ($tip_pitanja == 1){
		while ($pitanje = mysql_fetch_assoc($results1)) {
			$id = $pitanje['id'];
			$tekst = $pitanje['tekst'];
			// input hiden kako bismo znali id pitanja za kasnije cuvanje rezultata :)
			if ($par==1){
				print "<tr> "; 
				$par = 2;
			}
			else{
				print "<tr bgcolor='#CCCCFF'> "; 
				$par = 1;
			}
			print "<td> $tekst  <input type='hidden' name='id_pitanja$j' value=$id>  </td>";
			
			for ($i=1; $i<=5;$i++){
				echo "<td> <input type='radio' name='izbor"."$j'"." value="."$i /> ".$i."  </td>";
			}
			echo "</tr>";
			$j++;
		}	
	}
	else if ($tip_pitanja == 2){	
		while ($pitanje = mysql_fetch_assoc($results1)) {
			$id = $pitanje['id'];
			$tekst = $pitanje['tekst'];
			
			echo "<tr>  <td colspan = '6'>".$tekst." <input type='hidden' name='id_pitanja$j' value=$id></td></tr>";
			echo "<tr>";
			echo "<td colspan ='6' align = 'center'> <textarea  name='komentar$j' rows='7' cols='40'>  </textarea> </td>";
			echo "</tr>";
			$j++;
		}		
	}
	return $j;
}
?>
