<?php

function studentska_plan_studija(){
	global $userid,$user_siteadmin,$user_studentska;

	// Provjera privilegija
	if (!$user_studentska && !$user_siteadmin) {
		zamgerlog("nije studentska",3); // 3: error
		biguglyerror("Pristup nije dozvoljen.");
		return;
	}


// Konfigurabilna sekcija


define('BROJ_SEMESTARA', 10);


if(isset($_POST['max_izbornih']) and $_POST['max_izbornih'] > 0 ){
	define('MAX_BROJ_IZBORNIH_PREDMETA_PO_SEMESTRU', $_POST['max_izbornih']);
} else {
	define('MAX_BROJ_IZBORNIH_PREDMETA_PO_SEMESTRU', 6);
}
$max_izbornih=intval($_POST['max_izbornih']);

if(isset($_POST['max_predmeta']) and $_POST['max_predmeta'] > 0 ){
	define('MAX_BROJ_PREDMETA_PO_SEMESTRU', $_POST['max_predmeta']);
} else {
	define('MAX_BROJ_PREDMETA_PO_SEMESTRU', 6);
}
$max_predmeta=intval($_POST['max_predmeta']);

if(isset($_POST['max_broj_slotova']) and $_POST['max_broj_slotova'] > 0 ){
	define('MAX_BROJ_SLOTOVA_PO_SEMESTRU', $_POST['max_broj_slotova']);
} else {
	define('MAX_BROJ_SLOTOVA_PO_SEMESTRU', 6);
}
$max_predmeta=intval($_POST['max_broj_slotova']);


// Formiraj niz semestara
$data_semestar = array();
$data_semestar[] = 'I';
$data_semestar[] = 'II';
$data_semestar[] = 'III';
$data_semestar[] = 'IV';
$data_semestar[] = 'V';
$data_semestar[] = 'VI';
$data_semestar[] = 'VII';
$data_semestar[] = 'VIII';
$data_semestar[] = 'IX';
$data_semestar[] = 'X';


// Ucitaj podatke o studijima
$data_studij = array();
$data_zavrsni_semestar = array();
$sql_studij = " SELECT id, naziv, zavrsni_semestar FROM `studij` ";
$query_studij = myquery($sql_studij);
if(mysql_num_rows($query_studij) > 0){

	while($row = mysql_fetch_array($query_studij)){
		$data_studij[$row['id']] = $row['naziv'];
		$data_zavrsni_semestar[$row['id']] = $row['zavrsni_semestar'];
	}

}else{
	niceerror('Nema podataka o studijima');
	exit;
}

//Ucitaj podatke o akademskoj godini<br />

$data_akademska_godina = array();
$sql_akademska_godina = " SELECT id, naziv FROM `akademska_godina` ";
$query_akademska_godina = myquery($sql_akademska_godina);

if(mysql_num_rows($query_akademska_godina) > 0){

	while($row = mysql_fetch_array($query_akademska_godina)){
		$data_akademska_godina[$row['id']] = $row['naziv'];
	}

}else{
	niceerror('Nema podataka o akademskim godinama');
	exit;
}


// Podaci o predmetima
$data_predmet = array();
$sql_predmet = " SELECT id, naziv FROM `predmet` ORDER BY naziv";
$query_predmet = myquery($sql_predmet);
if(mysql_num_rows($query_predmet) > 0){

	while($row = mysql_fetch_array($query_predmet)){
		$data_predmet[$row['id']] = $row['naziv'];
	}

}else{
	niceerror('Nema podataka o predmetima');
	exit;
}


// Varijable, kako bi pokupio POST/GET podatke
$get_post_array = array('posted', 'studij', 'akademska_godina'); 


for($i = 0; $i < BROJ_SEMESTARA; $i++){

    for($j = 0; $j < MAX_BROJ_PREDMETA_PO_SEMESTRU; $j++){
     $get_post_array[] = 'semestar_'.$i.'_predmet_'.$j;
	 
    }
for($p = 0; $p < MAX_BROJ_SLOTOVA_PO_SEMESTRU; $p++){
    for($j = 0; $j < MAX_BROJ_IZBORNIH_PREDMETA_PO_SEMESTRU; $j++){
     $get_post_array[] = 'semestar_'.$i.'_izborni_predmet_'.$j.'_slot_'.$p;
	 
    }
}
}


foreach($get_post_array as $variable){

    $$variable = false;
    if(isset($_POST[$variable])){
        $$variable = addslashes($_POST[$variable]);
    }else if(isset($_GET[$variable])){
        $$variable = addslashes($_GET[$variable]);
    }
}



//Provjeri je li se desilo da u jednom semestru ima da je neki slot postavljen dva puta ili neki predmet, isto tako provjerava da u 2 razlicita semestra nema istih predmeta
$greskaSlotovi = false;

if($studij and $posted){
for($i = 0; $i < BROJ_SEMESTARA; $i++){

	$niz = array();
	for($j = 0; $j < MAX_BROJ_PREDMETA_PO_SEMESTRU; $j++){
		$varijabla = 'semestar_'.$i.'_predmet_'.$j;
		if($$varijabla == 0) continue;
		$niz[] = $$varijabla;
	}
	if(count($niz) > count(array_unique($niz))){ $greskaSlotovi = true; }


	for($p = 0; $p < MAX_BROJ_SLOTOVA_PO_SEMESTRU; $p++){
		$niz = array();
		for($j = 0; $j < MAX_BROJ_IZBORNIH_PREDMETA_PO_SEMESTRU; $j++){
		$varijabla = 'semestar_'.$i.'_izborni_predmet_'.$j.'_slot_'.$p;
		if($$varijabla == 0) continue;
		$niz[] = $$varijabla;
	}
if(count($niz) > count(array_unique($niz))){
		$greskaSlotovi = true;
	}

	}
	
	/*for($k = 0; $k < MAX_BROJ_IZBORNIH_PREDMETA_PO_SEMESTRU; $k++){
		for($p = 0; $p < MAX_BROJ_SLOTOVA_PO_SEMESTRU; $p++){
		$varijabla = 'semestar_'.$i.'_izborni_predmet_'.$k.'_slot_'.$p;
		if($$varijabla == 0) continue;
		$niz[] = $$varijabla;
	}
	}
	
	if(count($niz) > count(array_unique($niz))){
		$greskaSlotovi = true;
	}*/
}

$niz = array();
	for($p = 0; $p < MAX_BROJ_PREDMETA_PO_SEMESTRU; $p++){
		for($i = 0; $i < BROJ_SEMESTARA; $i++){
		$varijabla = 'semestar_'.$i.'_predmet_'.$p;
		if($$varijabla == 0) continue;
		$niz[] = $$varijabla;
	}
	}
	if(count($niz) > count(array_unique($niz))){ $greskaSlotovi = true; }
	
	
	
if($greskaSlotovi){ $posted=false; }	
	

}


//FORMA 1: Odabir godine i studija
if(!$studij){

	// Spremi odgovarajucu formu
?>	
	<p><h3>Izaberi studij</h3></p>
	<table align="center" border="1" width="80%" style="border-style:solid;">
	<tr><td style="border:1px solid #ffffff; background:#ffffff; font-weight: bold;">Izaberi studij</td></tr>
	<tr><td>
 
	 <?=genform("POST")?>
	<table align="center" border="0" width="100%">

<?
	// Odabir naziva studija
	?>
	<tr style="padding-left: 30px; border-bottom: 1px solid #cecfce;"><td>Studij</td>
	<td><select name="studij">
<?
	foreach($data_studij as $id => $naziv){
	?>
		<option value="<?=$id ?>"><?=$naziv ?></option>
	<?
    }
	?>

	</select></td>
	</tr>
	
		
	<?	
	// Odabir naziva akademske godine
	?>
    <tr style="padding-left: 30px; border-bottom: 1px solid #cecfce;"><td>Akademska godina</td>
	<td><select name="akademska_godina">
<?
	foreach($data_akademska_godina as $id => $naziv){
	?>
    	<option value="<?=$id?>"><?=$naziv?></option>
<?
	}
?>
	</select></td>
	</tr>
    <tr><td>Max. broj obaveznih predmeta u semetru:</td><td>
    <input type="text" name="max_predmeta" value="<?=(MAX_BROJ_PREDMETA_PO_SEMESTRU)?>">
	</td></tr>
    
    <tr><td>Max. broj slotova u semetru:</td><td>
    <input type="text" name="max_broj_slotova" value="<?=(MAX_BROJ_SLOTOVA_PO_SEMESTRU)?>">
	</td></tr>
    
	<tr><td>Max. broj izbornih predmeta u slotu:</td><td>
    <input type="text" name="max_izbornih" value="<?=(MAX_BROJ_IZBORNIH_PREDMETA_PO_SEMESTRU)?>">
	</td></tr>
    

<?
	// Submit input
?>	
	<tr><td></td>
	<td>
	<input type="submit" value="Kreiraj plan studija">
	</td>
	</tr>
</table>
</form>
</td>
</tr>
</table>

<?

//FORMA 2: Kreiranje studija (definisanje predmeta)
}else if(!$posted){

?>
	<p><h3>Kreiranje plana studija</h3></p>
<?
	$sql_pretraga_id="SELECT * FROM `plan_studija` WHERE `godina_vazenja` = '$akademska_godina' and `studij` = '$studij'";

	$rez=myquery($sql_pretraga_id);
	
	if(mysql_num_rows($rez) > 0){
			
		//Izbacivanje greske u slučaju da vec plan postoji u bazi
?>		
		<table align="center" border="1" width="60%" style="border-style:solid;">
		<tr><td style="border:1px solid #ffffff; background:#ffffff; font-weight: bold;">
		<font color="#FF0000">Plan već postoji u bazi, ukoliko želite izmjeniti plan to možete uraditi u formi ispod, ukoliko želite vidjeti plan kliknite na ok.</font>
		</td></tr>
		</table>
		<?
}

	// Spremi odgovarajucu formu
	
?>
	
	
	<?=genform("POST")?>
	<input type="hidden" name="posted" value="1">
	<input type="hidden" name="studij" value="<?=$studij?>">
	<input type="hidden" name="akademska_godina" value="<?=$_POST['akademska_godina']?>">
    <input type="hidden" name="max_predmeta" value="<?=(MAX_BROJ_PREDMETA_PO_SEMESTRU)?>" >
    <input type="hidden" name="max_broj_slotova" value="<?=(MAX_BROJ_SLOTOVA_PO_SEMESTRU)?>" >
    <input type="hidden" name="max_izbornih" value="<?=(MAX_BROJ_IZBORNIH_PREDMETA_PO_SEMESTRU)?>" >
    
	<table align="center" border="1" width="60%" style="border-style:solid;">
	<tr><td style="border:1px solid #ffffff; background:#ffffff; font-weight: bold;">Kreiranje plana studija (<?=($data_studij[$studij])?>)</td></tr>
<?
	if($greskaSlotovi){
?>
		<tr><td style="color:red;"><b>Ne mogu postojati 2 ista slota ili 2 ista predmeta!</b></td></tr>
<?
	}
?>	



    <tr><td>Ukoliko semestar ima manje predmeta ostavite polja viška prazna (vrijednost "izaberi").</td></tr>

<?
	// Petlja po semestrima
	$slot_id=0;
		@$slot;
	for($i = 0; $i < BROJ_SEMESTARA; $i++){



		// Nemaju svi studiji isti broj semestara
		$broj_semestra = $i + 1;
		if($data_zavrsni_semestar[$studij] > 2){

			if($broj_semestra < 1){
				continue;
			}else if($broj_semestra > $data_zavrsni_semestar[$studij]){
				break;
			}

		}else{

			if($broj_semestra > $data_zavrsni_semestar[$studij]){
				break;
			}
		}


?>

		<tr><td>
		<table   align="center" border="0" width="100%">

<?
		// Reci o kojem se semestru radi
?>
		<tr style="padding-left: 30px; border-bottom: 1px solid #cecfce;">
		<td width="50%"><b>Semestar: <?=($data_semestar[$i])?></b><hr></td>
		<td width="50%"></td>
		</tr>
		<?

		// Petlja po predmetima
		
		
		$obaveznih_broj = MAX_BROJ_PREDMETA_PO_SEMESTRU;

		$test_broj_obaveznih = myquery("SELECT COUNT(*) FROM plan_studija WHERE godina_vazenja='$akademska_godina' AND studij='$studij' AND semestar='$semestar_i' AND obavezan='1' LIMIT 1");

		if($test_broj_obaveznih != false AND mysql_num_rows($test_broj_obaveznih) == 1){
			
			$test_broj_obaveznih_red = mysql_fetch_row($test_broj_obaveznih);
			
			if($obaveznih_broj < $test_broj_obaveznih_red[0])
			{
				$obaveznih_broj = $test_broj_obaveznih_red[0];
			}			
		}
		
		
		$semestar_i = $i+1;
		$brojac_izborni = 0;
		$snimljeni_izborni = array();
		$staro_izborni = myquery("SELECT * FROM plan_studija WHERE godina_vazenja='$akademska_godina' AND studij='$studij' AND semestar='$semestar_i' AND obavezan='1'");
		while($red = mysql_fetch_assoc($staro_izborni)){
			$snimljeni_izborni[$brojac_izborni] = $red['predmet'];
			$brojac_izborni++;
		}
		
		
		for($j = 0; $j < MAX_BROJ_PREDMETA_PO_SEMESTRU; $j++){



			// Odabir predmeta
			?>
            
			<tr style="padding-left: 30px; border-bottom: 1px solid #cecfce;"><td width="50%">Predmet <?=($j+1)?></td>
			<td width="50%">
			<select name="semestar_<?=$i?>_predmet_<?=$j?>">
<?		
			// Po default-u, nije nista odabrano
	?>
			<option value="0" selected>izaberi</option>
<?			$a=0;
			foreach($data_predmet as $id => $naziv){
				$odabran = '';
				if(isset($snimljeni_izborni[$j]) and $snimljeni_izborni[$j] == $id){
					$odabran = 'selected="selected"';
				}
				?>
				<option value="<?=$id?>" <?=($odabran)?>><?=$naziv?></option>';
		<?	}
		?>

			</select>
			</td>

			</tr>
		<?
		}
//Petlja po slotovima


	$izbornih_broj = MAX_BROJ_IZBORNIH_PREDMETA_PO_SEMESTRU;

//ako vec postoji u bazi da ga ispise u padajucem meniju za izborne predmete		
		for($m=0;$m<sizeof($slot);$m++)
		{
			for($n=0;$n<sizeof($slot[$m]);$n++)
			$slot[$m][$n]=0;
		}
		
		$brojac_izborni = 0;
		$snimljeni_izborni = array();
		$staro_izborni = myquery("SELECT * FROM plan_studija as p, izborni_slot as i WHERE p.godina_vazenja='$akademska_godina' AND p.studij='$studij' AND p.semestar='$semestar_i' AND p.obavezan='0' AND p.predmet=i.predmet");
		while($red = mysql_fetch_assoc($staro_izborni)){
			$snimljeni_izborni[$brojac_izborni] = $red['predmet']; 
			$it=$red['id']; 
			$postoji=0;
			for($k=0;$k<sizeof($slot);$k++)
				{
					if($it==$slot[$k][0]) $postoji++;
					
				}
				if($postoji==0){
					$upit=myquery("SELECT * FROM izborni_slot WHERE id='$it'");
					while($row=mysql_fetch_array($upit)){
						$slot[$slot_id][0]=$it;
						$slot[$slot_id][]=$row['predmet'];
						
					}
					$slot_id++;
				}
			$brojac_izborni++;
		}
		
		
//echo "slot id  ".$slot_id."<br>";

for($p=0;$p<MAX_BROJ_SLOTOVA_PO_SEMESTRU;$p++){
	?>
    <td width="50%"><b>Slot: <?=$p+1?></b><hr></td>
    <?
	// Petlja po predmetima
		
		for($j = 1; $j <= $izbornih_broj; $j++){

 

			// Odabir predmeta
			?>
            
			<tr style="padding-left: 30px; border-bottom: 1px solid #cecfce;"><td width="50%">Izborni predmet <?=($j)?></td>
			<td width="50%">
			<select name="semestar_<?=$i?>_izborni_predmet_<?=$j-1?>_slot_<?=$p?>">
<?		
			// Po default-u, nije nista odabrano
	?>
			<option value="0" selected>izaberi</option>
<?			$a=0;
			foreach($data_predmet as $id => $naziv){
				$odabran = '';
				if(isset($slot[$p][0]) and $slot[$p][$j] == $id){
					$odabran = 'selected="selected"';
				}
				?>
				<option value="<?=$id?>" <?=($odabran)?>><?=$naziv?></option>';
		<?	}
		?>

			</select>
			</td>

			</tr>
		<?
		}

}
    
    

?>
</table></td></tr>
		<?

	}


	// Submit input
?>
	<tr><td align="right" style=" padding-left: 170px; border-bottom: 1px solid #cecfce; background-color: #cccccc;">
	<input type="submit" value="OK"></td></tr>
	</table>
	</form>
	
<?




}else{
	// FORMA 3: Snimanje plana i stampa


	// Obrisi stari plan, ako postoji, prije nego ubacis novi
	
	$sql_brisi_stari_plan = " DELETE FROM plan_studija ";
	$sql_brisi_stari_plan .= " WHERE studij = ".$studij;
	$query = myquery($sql_brisi_stari_plan);
	$sql_pretraga_id="SELECT * FROM `plan_studija` WHERE `godina_vazenja` = '$akademska_godina' and `studij` = '$studij'";
	

	$rez=myquery($sql_pretraga_id);
	

$slot=-1;
$brojac=0;
@$slot_id;
	// Prodji kroz primljene podatke i vidi sta mozes sa njima
	foreach($get_post_array as $field){

		if($$field && $field != 'semestar' && $field != 'posted'){
			//echo "field jee ".$field;
			// Inicijalizacija
			$semestar = 0;
			$predmet = 0;
			$obavezan = 1;

			// Iz imena primljene varijable odredi o kom semestru se radi, vrijednost varijable je predmet
			$field_name_data = explode('_', $field); 
			if($field_name_data[0] == 'semestar' && ($field_name_data[2] == 'predmet' || $field_name_data[2] == 'izborni')){
				$semestar = $field_name_data[1] + 1;
				$predmet = $$field;
			}else{
				continue;
			}

			// Da li se radi o izbornom predmetu
			if($field_name_data[2] == 'izborni'){
				$obavezan = 0;
			}
			@$sl;
			if($field_name_data[5] == 'slot'){
				$sl = $field_name_data[6];
			}
	
			
			// Ubaci red u bazu
			
			$sql_insert = " INSERT INTO plan_studija (godina_vazenja, studij, semestar, predmet, obavezan) VALUES ";
			$sql_insert .= " ($akademska_godina, $studij, $semestar, $predmet, $obavezan) ";
			
			$query = myquery($sql_insert);
			
		
					
			if($obavezan==0){
				$novi_id=0;
			$izborni_slot_max_id="SELECT MAX(id) as id from `izborni_slot`";
			$query = myquery($izborni_slot_max_id);
			while($red = mysql_fetch_assoc($query)){
				if($sl!=$slot){
						$novi_id=$red['id']+1;
						$slot=$sl;
						$slot_id[$brojac]=$novi_id;
						$brojac++;
				}
				else {
					$novi_id=$red['id'];
					$slot=$sl;
				}
		}
		
				$sql_insert_slot="INSERT INTO izborni_slot (id,predmet) VALUES ($novi_id,$predmet) ";
				$query = myquery($sql_insert_slot);
		
				


				}
			
			
		}
	}





	// Citaj iz baze i prikazi
	$data_plan = array();
	$sql_plan = " SELECT studij, semestar, predmet, obavezan FROM `plan_studija` ";
	$sql_plan .= " ORDER BY studij ASC ";
	$slot_id=0;
	
	$query_plan = myquery($sql_plan);
	if(mysql_num_rows($query_plan) > 0){

		while($row = mysql_fetch_array($query_plan)){

			$tmp_array = array();
			$sem=$data_semestar[($row['semestar']-1)];
			$stud=$data_studij[$row['studij']];
			
			$pr=$row['predmet'];
			if($row['obavezan']==0){
				$predmeti = myquery("SELECT id,predmet FROM izborni_slot WHERE predmet='$pr'");
				while($predmet = @mysql_fetch_array($predmeti)){
					$sl_id=$predmet['id'];
					if($sl_id!=$slot_id){
						$slot_id=$sl_id;
						$predmet1=myquery("SELECT predmet FROM izborni_slot WHERE id='$sl_id'");
						$tmp_niz = array();
						while($predmet2 = @mysql_fetch_array($predmet1)){
							$tmp_niz[] = $data_predmet[$predmet2['predmet']];
						}
						$tmp_array['semestar'] = $sem;
						$tmp_array['studij'] = $stud;
						$tmp_array['predmet'] = implode(" / ", $tmp_niz);
						
						
					}
				}
				
			} else {
				$tmp_array['semestar'] = $sem;
						$tmp_array['studij'] = $stud;
				$tmp_array['predmet'] = $data_predmet[$row['predmet']];
				
			}

			$data_plan[] = $tmp_array;
		}

	}else{
		niceerror('Nema podataka o planu studija');
		exit;
	}


	// Prikazi plan studija
	echo listingHtml($data_plan, '80%', 'Plan studija');

}
}

function listingHtml($data, $table_width, $table_name){


	// Boja u tabelama
	$row_color[0] = "#FFFFF0";
	$row_color[1] = "#F2F8FF";
	$on_mouse_over_color = "#C4FFD7";


	// U ovu varijablu punim html sadrzaj za listanje
	
	?>
	<table align="center" border="1" style="border-style:solid;" width="<?=$table_width?>"><tr>
	<td style="border:1px solid #ffffff; background:#ffffff; font-weight: bold;">
	<?= $table_name?>
	</td></tr>
	<tr align="center"><td>
	<table align="center" border="0" width="100%">

<?
	// Header-i
	?>
	<tr align="center">
	<?
    foreach($data as $key => $row){
		foreach($row as $header => $field){
			?>
            <th align="middle">
			<?=$header?>
			</th>
            <?
		}
		break;
	}
	?>
	</tr>


	<?
    // Podaci
	$counter = 0;
	foreach($data as $key => $row){

		// Pisi u novi red
		$counter++;
		?>
        <tr align="center" bgcolor="<?=$row_color[$counter%2]?>" onMouseOver="bgColor=\''.$on_mouse_over_color.'\'" onMouseOut="bgColor=\'<?=$row_color[$counter%2]?>\'">
<?
		foreach($row as $header => $field){

			// Prikazi polje u redu
			?>
            <td align="middle">
			<?= $field?>
			</td>
<?
		}
?>
		</tr>
<?
	}
?>

	</table>
	</td>
    </tr>
    </table>
<?
return;
	

}

?>
