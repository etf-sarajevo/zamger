<?
function studentska_kreiranje_plana(){

	global $userid,$user_siteadmin,$user_studentska;
	
	
	if (!$user_studentska && !$user_siteadmin) {
		zamgerlog("nije studentska",3); // 3: error
		zamgerlog2("nije studentska"); // 3: error
		biguglyerror("Pristup nije dozvoljen.");
		return;
	}
	
	if (!isset($_REQUEST['create'])){
		//crtaj formu
?>
		<h3 style="margin-left: 30px;">Dodavanje stavke nastavnog plana</h3>
		<br/>
		<div style="padding-left: 30px;">
		<div style="background-color: #EFEFEF;
					border-top-width: 1px;
					border-top-color: coral;
					border-top-style: solid;
					border-bottom-width: 1px;
					border-bottom-color: coral;
					border-bottom-style: solid;
					display: table-cell;
					padding-left: 5px;
					padding-top: 5px;">
					
<?
		
		print genform("POST", "createPlan"); //generisi prvu liniju forme
		?>
			<label style="float: left;width: 120px;font-weight: bold;">Godina važenja:</label>
				<select name="godina_vazenja" class="formElement" id="godina_vazenja" style="width: 250px;margin-bottom: 5px;">
					<? 
						$cnt = 0;
						$akademske = fetch_ag();
						foreach($akademske as $akg)
						{
							$cnt = $cnt+1;
					?>		
							<option value="<?= $akg['id']?>"><?= nl2br($cnt . '.' . $akg['naziv'])?></option>
					<?	} 
					?>
				</select>
				<br />
			<label style="float: left;width: 120px;font-weight: bold;">Studij:</label>
				<select name="studij" class="formElement" id="studij" style="width: 250px;margin-bottom: 5px;">
					<? 
						$cnt = 0;
						$studiji = fetch_studiji();
						foreach($studiji as $std)
						{
							$cnt = $cnt+1;
					?>		
							<option value="<?= $std['id']?>"><?= nl2br($cnt . '.' . $std['naziv'])?></option>
					<?	} 
					?>
				</select>
				<br />
			<label style="float: left;width: 120px;font-weight: bold;">Semestar: </label>
				<input type="text" name="semestar" value="" style="width: 30px;">
				<br />
			<label style="float: left;width: 120px;font-weight: bold;">Predmet:</label>
				<select name="predmet" id="predmet" style="width: 250px;margin-bottom: 5px;">
					<? 
						$cnt = 0;
						$predmeti = fetch_predmeti();
						foreach($predmeti as $prm)
						{
							$cnt = $cnt+1;
					?>		
							<option value="<?= $prm['id']?>"><?= nl2br($cnt . '.' . $prm['naziv'])?></option>
					<?	} 
					?>	
				</select>

			<input type="checkbox" name="obavezan" id="obavezan">
				<span class="smallText"> Obavezan </span>
			</input>
			<br />
			<input type="submit" name="create" id="submitbutton" value="Potvrdi" style="width:100px;font-weight: bold;"/>
		</form>
		</div>
		</div>
<?
	}else{ //procesiraj formu 
		$errorText = processInsertPlanForm();
		
		if($errorText == ''){
?>
			<div style="padding-left: 30px">
			<div>
<?
			nicemessage('Uspješno ste kreirali stavku plana studija.');
?>		
			<a href="?sta=studentska/plan">PRIKAŽI PLAN</a>
			<br/><br/>
			<a href="?sta=studentska/kreiranje_plana">Nazad na kreiranje nove stavke plana</a>
			</div>
			</div>
<?
		}else{
			niceerror($errorText);
			$link = "javascript:history.back();";
			nicemessage('<a href="'. $link .'">Povratak.</a>');
		}
		
	}//END of if-else 
}//end of function 

//Funckcija dohvaca sve akademske godine iz baze
function fetch_ag() {
	
	$result = myquery("select id,naziv from akademska_godina");
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;
	mysql_free_result($result);
	
	return $list;
}

function fetch_studiji() {
	
	$result = myquery("select id,naziv from studij");
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;
	mysql_free_result($result);
	
	return $list;
}

function fetch_predmeti(){
	$result = myquery("select id,naziv from predmet");
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;
	mysql_free_result($result);
	
	return $list;
}

function processInsertPlanForm(){

	$errorText = '';
	if(!check_csrf_token())
	{
		biguglyerror("Mrš odavle");
		zamgerlog("1337 h4x0r detected",3);
		zamgerlog2("csrf token ne odgovara");
		return "ERROR";
	}
	
	//get variables
	$godina_id = intval($_REQUEST['godina_vazenja']);
	$studij_id = intval($_REQUEST['studij']);
	$semestar = intval($_REQUEST['semestar']);
	//provjeri da je semestar cifra
	if(!is_int($semestar)){
		$errorText = "Broj semestra nije validan!";
		return $errorText;
	}
	$predmet_id = intval($_REQUEST['predmet']);
	$obavezan = 0;
	if(isset($_REQUEST['obavezan'])){
		$obavezan = 1;
	}
	$errorText = createPlan($godina_id, $studij_id, $semestar, $predmet_id, $obavezan);
		
	return $errorText;
}

function createPlan ($godina_id, $studij_id, $semestar, $predmet_id, $obavezan){
	
	//TODO: dohvati studij->zavrsni_semestar, uporedi sa unesenim semestrom.
	$res_std = myquery("select zavrsni_semestar from studij s where s.id=$studij_id");
	$row = mysql_fetch_assoc($res_std);
	if($row['zavrsni_semestar'] < $semestar ){
		$errorText = "Uneseni semestar je veci od ukupnog broja semestara izabranog studija!";
		return $errorText;
	}
	
	$query = sprintf("INSERT INTO plan_studija (godina_vazenja,studij,semestar,predmet,obavezan) VALUES ('%d', '%d', '%d', '%d', '%d')", 
					$godina_id, $studij_id, $semestar, $predmet_id, $obavezan);
	
	$result = myquery($query);
	
	if ($result == false)
	{
		$errorText = 'Došlo je do greške prilikom spašavanja podataka. Molimo kontaktirajte administratora.';
		return $errorText;	
	}	
} 	
	



?>