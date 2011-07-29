<?php 

// PUBLIC/ANKETA - stranica za ispunjavanje ankete

function student_anketa2(){
	global $_lv_, $userid; 
	$q10 = myquery("select id,naziv from akademska_godina where aktuelna=1");
	$ag = mysql_result($q10,0,0);

	// uzimamo id aktivne ankete
	$q01 = myquery("select id from anketa_anketa where  akademska_godina=$ag and aktivna=1");
	if (mysql_num_rows($q01)==0){
		biguglyerror("Ne postoji aktivna anketa!");
		return;
	}
	$id_ankete = mysql_result($q01,0,0);

	$q09= myquery("select id,naziv,UNIX_TIMESTAMP(datum_zatvaranja),predmet,opis from anketa_anketa where aktivna=1 and akademska_godina=$ag order by id desc");
	$id_ankete = mysql_result($q09,0,0);
	$naziv_ankete = mysql_result($q09,0,1);
	$rok=mysql_result($q09,0,2);
	$anketa_predmet=mysql_result($q09,0,3);
	$opis_ankete = nl2br(mysql_result($q09,0,4));
	if (time () > $rok){
		biguglyerror("Isteklo vrijeme za ispunjavanje ankete");
		return;
	}

	$predmet = intval($_REQUEST['predmet']);
	if ($anketa_predmet>0 && $anketa_predmet!=$predmet) {
		biguglyerror("Anketa nije aktivna za ovaj predmet.");
		return;
	}

	// Da li student slusa predmet?
	//print "select sp.predmet from student_predmet as sp, ponudakursa as pk where sp.student=$userid and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag";
	$q17 = myquery("select sp.predmet from student_predmet as sp, ponudakursa as pk where sp.student=$userid and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
	if (mysql_num_rows($q17)<1) {
		zamgerlog("student ne slusa predmet pp$predmet", 3);
		biguglyerror("Niste upisani na ovaj predmet");
		return;
	}
	
	$q20 = myquery("select studij,semestar from student_studij where student=$userid and akademska_godina=$ag order by semestar desc limit 1");
	if (mysql_num_rows($q20)<1) {
		$sem_ispis = "Niste upisani na studij!";		
	} 
	else {
		$studij = mysql_result($q20,0,0);
		$semestar = mysql_result($q20,0,1);

	}	
	$ponudakursa = mysql_result($q17,0,0);



//	if ($_POST['akcija'] == "finish" || $_POST['akcija'] == "prikazi") {
		// kupimo kod koji je student unio
//		$unique_hash_code = my_escape($_POST['kod']);
		
		// provjeravamo da li je dati student zatrazio kod te da li je vec ispunjavao datu anketu sa poljem zavrsena
//		$q590 = myquery("SELECT id,predmet,zavrsena FROM anketa_rezultat WHERE unique_id = '$unique_hash_code'");
		$q590 = myquery("SELECT id,predmet,zavrsena FROM anketa_rezultat WHERE student=$userid and anketa=$id_ankete");
		
/*		if ( !mysql_num_rows($q590) )	{
			// dio koji ide ako dati hesh ne postoji u bazi tj ako student pokusava da izmisli hesh :P
			?>
			<center>
				<p>Uneseni kod ne postoji u bazi</p>
				<a href="index.php"> Nazad na početnu </a>
			</center>
			<?
			return;
		}*/

		if (mysql_result($q590,0,2) == 'Y') {
			?>
			<center>
				<p>Vaša anketa je već popunjena! Ne možete ponovo popuniti anketu.</p>
				<a href="index.php"> Nazad na početnu </a>
			</center>
			<?
			return;
		}
		if (mysql_num_rows($q590)<1) {
			$q591 = myquery("insert into anketa_rezultat set anketa=$id_ankete, vrijeme=NOW(), zavrsena='N', predmet=$predmet, unique_id='', akademska_godina=$ag, studij=$studij, semestar=$semestar, student=$userid");
			$q590 = myquery("SELECT id FROM anketa_rezultat WHERE student=$userid and anketa=$id_ankete");
		}
		$id_rezultata =mysql_result($q590,0,0);
		//$predmet = mysql_result($q590,0,1);
//	}
	
	// da li je student zavrsio anketu 
	if ($_POST['akcija'] == "finish" && check_csrf_token()) {
		// broj rank pitanja
		$q203=myquery("SELECT count(*) FROM anketa_pitanje WHERE anketa=$id_ankete and tip_pitanja=1");
		$broj_rank_pitanja= mysql_result($q203,0,0);

		$j=1;
		for ($i=0; $j<=$broj_rank_pitanja ; $i++){
			if ($_POST['izbor'.$j]){ // provjeravamo prvo da li je pitanje uopce odgovoreno
				$rankovi[$i] = $_POST['izbor'.$j];
				$id_pitanja[$i] = $_POST['id_pitanja'.$j];
			}
			else
				$i--;
			$j++;
		}
		
		// ubaciti sve odgovore u tabelu odgovori_rank
		for ($i=0; $i< count($rankovi) ; $i++){
			$q590 = myquery("insert into anketa_odgovor_rank set rezultat=$id_rezultata, pitanje=$id_pitanja[$i], izbor_id=$rankovi[$i]");
		}
		
		// broj esejskih pitanja
		$result204=myquery ("SELECT count(*) FROM anketa_pitanje WHERE anketa=$id_ankete and tip_pitanja=2");
		$broj_esej_pitanja= mysql_result($result204,0,0);
		 
		for ($i=0; $i<$broj_esej_pitanja ; $i++){
			$komentar[$i] = my_escape($_POST['komentar'.$j]);
			$id_pitanja[$i] = $_POST['id_pitanja'.$j];
			$j++;
		}
		// ubaciti sve odgoovre u tabelu odgovori_text
		for ($i=0; $i<$broj_esej_pitanja ; $i++){
			$q590 = myquery("insert into anketa_odgovor_text set rezultat=$id_rezultata, pitanje=$id_pitanja[$i], odgovor='$komentar[$i]'");
		}

		// pitanja tipa izbor jedinstveni
		$q204=myquery ("SELECT id FROM anketa_pitanje WHERE anketa=$id_ankete and tip_pitanja=3");
		while ($r204 = mysql_fetch_row($q204)) {
			$pitanje = $r204[0];
			$odgovor = intval($_POST["izbor$pitanje"]);
			$q205 = myquery("select dopisani_odgovor from anketa_izbori_pitanja where pitanje=$pitanje and id=$odgovor");
			if (mysql_result($q205,0,0)==1) {
				$dopisani_odgovor = my_escape($_POST["dopis$pitanje-$odgovor"]);
				$q590 = myquery("insert into anketa_odgovor_dopisani set rezultat=$id_rezultata, pitanje=$pitanje, odgovor='$dopisani_odgovor'");
			}
			$q590 = myquery("insert into anketa_odgovor_izbori set rezultat=$id_rezultata, pitanje=$pitanje, izbor_id=$odgovor");
		}

		// pitanja tipa izbor visestruki
		$q204=myquery ("SELECT id FROM anketa_pitanje WHERE anketa=$id_ankete and tip_pitanja=4");
		while ($r204 = mysql_fetch_row($q204)) {
			$pitanje = $r204[0];
			$q205 = myquery("select id, dopisani_odgovor from anketa_izbori_pitanja where pitanje=$pitanje");
			while ($r205=mysql_fetch_row($q205)) {
				$odgovor = $r205[0];
				if ($_POST["izbor$pitanje-$odgovor"]) {
					if ($r205[1]==1) {
						$dopisani_odgovor = my_escape($_POST["dopis$pitanje-$odgovor"]);
						$q590 = myquery("insert into anketa_odgovor_dopisani set rezultat=$id_rezultata, pitanje=$pitanje, odgovor='$dopisani_odgovor'");
					}
					$q590 = myquery("insert into anketa_odgovor_izbori set rezultat=$id_rezultata, pitanje=$pitanje, izbor_id=$odgovor");
				}
			}
		}
		
		// nakon uspjesnog ispunjenja ankete postaviti i polje zavrsena na true u tabeli razultati
		$q600 = myquery("update anketa_rezultat set zavrsena='Y' where id=$id_rezultata");
		zamgerlog("popunjena anketa $unique_hash_code (pp$predmet)", 2);

		?>	
		<center>
			<p> Hvala na ispunjavanju ankete. </p>
			<a href="index.php"> Nazad na početnu </a>
		</center>
		<?
		return;
	}

	//  ----------------  AKCIJA PRIKAZI ----------------------
	//  dio koji ide nakon sto je student unio kod za anketu te stistnuo dugme 

	//if($_POST['akcija'] == "prikazi") {
		$q011= myquery("select naziv from predmet where id=$predmet");
		$naziv_predmeta = mysql_result($q011,0,0);

		?>
		<center>
			<h2> <?= $naziv_ankete?> (<?=$naziv_predmeta?>)</h2>
		</center>
		<?=genform("POST")?>
		<input type="hidden" name="akcija" value="finish">
		<!--input type="hidden" name="kod" value="<?=$unique_hash_code?>"-->
		
		<table align="center" cellpadding="4" border="0" >
			<tr>
				<td colspan = '6'>
					<?=$opis_ankete?>
				</td>
			</tr>
			<!--tr>
				<td colspan = '6'>
					<hr/> 
					<strong>U sljedećoj tabeli izaberite samo jednu od ocjena za iskazanu tvrdnju na skali ocjena od 1 (apsolutno se ne slažem) do 5 (apsolutno se slažem). </strong>
				</td>
			</tr-->
		<?

		//echo "<tr>  <td colspan = '6'><hr/> </td></tr>";
		$broj_pitanja = Ubaci_pitanje(1,$id_ankete,1);
		//echo "<tr>  <td colspan = '6'><hr/> </td></tr>";
		$broj_pitanja = Ubaci_pitanje(2,$id_ankete,$broj_pitanja); 
		//echo "<tr>  <td colspan = '6'><hr/> </td></tr>";
		$broj_pitanja = Ubaci_pitanje(3,$id_ankete,$broj_pitanja); 
		?>
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
		return;
	//} // ---------------  KRAJ AKCIJA PRIKAZI  --------------------------


/*	// Unos koda
	else {
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
					<input type="text" id="kod" name="kod"  size="60">	
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
	}*/
}//  ----------------- KRAJ FUNKCIJE PUBLIC_ANKETA -------------

function Ubaci_pitanje($tip_pitanja,$id_ankete,$j){
	// kupitmo pitanja u zavisnosti od argumenta koji je poslan
	$results1= myquery("select id,tekst from anketa_pitanje p where tip_pitanja = $tip_pitanja and anketa=$id_ankete");
	
	$par =1;
	// ako je pitanje rank
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

			// input hiden kako bismo znali id pitanja za kasnije cuvanje rezultata
			print "<td> $tekst  <input type='hidden' name='id_pitanja$j' value=$id>  </td>";
			
			for ($i=1; $i<=5;$i++) {
				echo "<td> <input type='radio' name='izbor"."$j'"." value="."$i /> ".$i."  </td>";
			}
			echo "</tr>";
			$j++;
		}	
	}
	else if ($tip_pitanja == 2){	
		while ($pitanje = mysql_fetch_assoc($results1)) {
			$id = $pitanje['id'];
			$tekst = nl2br($pitanje['tekst']);
			
			echo "<tr>  <td colspan = '6'>".$tekst." <input type='hidden' name='id_pitanja$j' value=$id></td></tr>";
			echo "<tr>";
			echo "<td colspan ='6' align = 'center'> <textarea  name='komentar$j' rows='7' cols='40'></textarea> </td>";
			echo "</tr>";
			$j++;
		}		
	}
	else if ($tip_pitanja == 3){	
		// kupitmo pitanja u zavisnosti od argumenta koji je poslan
		$results1= myquery("select id,tekst, tip_pitanja from anketa_pitanje p where (tip_pitanja = 3 or tip_pitanja = 4) and anketa=$id_ankete");
		while ($pitanje = mysql_fetch_assoc($results1)) {
			$id = $pitanje['id'];
			$tekst = nl2br($pitanje['tekst']);

			print "<tr><td colspan='6'><large><b>$j.</b></large> $tekst<br>\n";

			// Spisak izbora
			$q300 = myquery("select id, izbor, dopisani_odgovor from anketa_izbori_pitanja where pitanje=$id");
			while ($r300 = mysql_fetch_row($q300)) {
				if ($r300[2]==1)
					$dopis = " (dopisati): <input type=\"text\" size=\"30\" name=\"dopis$id-$r300[0]\">";
				else $dopis="";

				if ($pitanje['tip_pitanja']==3) { // radio button
					print "<input type='radio' name='izbor$id' value='$r300[0]'> $r300[1]$dopis<br>\n";
				} else {
					print "<input type='checkbox' name='izbor$id-$r300[0]'> $r300[1]$dopis<br>\n";
				}
			}
			print "</td></tr>";
			$j++;
		}
	}
	return $j;
}
?>
