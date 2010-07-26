<?php
// NASTAVNIK/PROJEKTI - nastavnicki modul za definisanje projekata, parametara
require_once("lib/projekti.php");

function nastavnik_projekti()
{
	//debug mod aktivan
	global $userid, $user_nastavnik, $user_siteadmin;
	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);
		
	// Da li korisnik ima pravo ući u modul?
	
	if (!$user_siteadmin) 
	{ // 3 = site admin
		$q10 = myquery("select admin from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
		if (mysql_num_rows($q10)<1 || mysql_result($q10,0,0)<1) {
			zamgerlog("nastavnik/projekti privilegije (predmet pp$predmet)",3);
			biguglyerror("Nemate pravo ulaska u ovu grupu!");
			return;
		} 
	}
	
	$linkPrefix = "?sta=nastavnik/projekti&predmet=$predmet&ag=$ag";
	$action 	= $_REQUEST['action'];
	$id			= intval($_REQUEST['id']);
		
	?>
<LINK href="css/projekti.css" rel="stylesheet" type="text/css">
<script type="text/javascript">
			window.onload = function() {
				var table = document.getElementById('expandable_table');
				if (table) {
					var trs = table.getElementsByTagName('tr');
					for(var i = 0; i < trs.length; i++) {
						var a = trs[i].getElementsByTagName('td')[1].getElementsByTagName('a')[0];
						if(a){
							a.onclick = function() {
								var span = this.parentNode.getElementsByTagName('span')[0];
								span.style.display = span.style.display == 'none' ? 'block' : 'none';
								this.firstChild.nodeValue = span.style.display == 'none' ? '|+|' : '|-|';
							
								var studSpan = this.parentNode.parentNode.getElementsByTagName('td')[0].getElementsByTagName('span')[0];
								studSpan.style.display = studSpan.style.display == 'none' ? 'block' : 'none';
							};
						}	
					}
				}
			};

			function removeTable(divId, tableId) {
				  var d = document.getElementById(divId);
				  var child = document.getElementById(tableId);
				  d.removeChild(child);
			}
	
</script>
<h2>Projekti</h2>
<?php	
	
	$params = getPredmetParams($predmet, $ag);
	if ($action != 'page')
	{
	?>
     <div class="links">
            <ul class="clearfix">
            	<li><a href="<?php echo $linkPrefix . "&action=param"?>">Parametri projekata</a></li>
                <li><a href="<?php echo $linkPrefix ?>">Lista projekata</a></li>
                <li><a href="<?php echo $linkPrefix . "&action=addProject" ?>">Novi projekat</a></li>
                <li class="last"><a href="<?php echo $linkPrefix . "&action=addStudent"?>">Dodjela projekata studentima</a></li>
            </ul>   
    </div>	
    <?php	
	}
	
	if (!isset($action))
	{
		
?>
<h2>Lista projekata</h2>
<?php
	$projects = fetchProjects($predmet, $ag);
	if (!empty($projects))
	{
		if ($params[zakljucani_projekti] == 1)
		{
?>
	<span class="notice">Onemogučene su prijave u projektne timove. Otvorene su projektne stranice.</span>	
<?php
		} //locked projects
		else
		{
?>
	<span class="noticeGreen">Studenti se još uvijek mogu prijaviti u projektne timove. Niste zaključali spiskove u parametrima prijekata.</span>	

<?php
		}
		$countTeamsForPredmet = getCountNONEmptyProjectsForPredmet($predmet, $ag);
		if ($countTeamsForPredmet < $params[min_timova])
		{
?>
	<span class="notice">Trenutni broj timova (<?=$countTeamsForPredmet?>) je ispod minimalnog broj timova koji ste definisali za ovaj predmet (<?=$params[min_timova]?>).</span>	
<?php
		
		} //if min teams is not achieved yet
		
		
	} //if there are projects
	else
	{
?>
	<span class="notice">Nema kreiranih projekata na ovom predmetu.</span>	
<?php	
	}

?>
	
<?php
	
	foreach ($projects as $project)
	{
		$members = fetchProjectMembers($project[id]);
?>
<h3><?=filtered_output_string($project['naziv'])?></h3>
<div class="links">
            <ul class="clearfix" style="margin-bottom: 10px;">
            <li><a href="<?php echo $linkPrefix . "&action=editProject&id=$project[id]" ?>">Uredi projekat</a></li>
            <li><a href="<?php echo $linkPrefix . "&action=addNoteOnProject&id=$project[id]" ?>">Dodaj biljesku</a></li>
            <li <?php if ($params[zakljucani_projekti] == 0) echo 'class="last"' ?>><a href="<?php echo $linkPrefix . "&action=delProject&id=$project[id]" ?>">Obriši projekat</a></li>
     	<?php
			if ($params[zakljucani_projekti] == 1)
			{
		?>
                <li class="last"><a href="<?= $linkPrefix . "&action=page&projekat=$project[id]" ?>">Projektna stranica</a></li>
        <?php
			} //locked projects
		?>
            </ul> 
        <?php
			$countMembersForProject = getCountMembersForProject($project[id]);
			if ($countMembersForProject < $params[min_clanova_tima])
			{
		?>
			<span class="notice">Broj prijavljenih studenata (<?=$countMembersForProject?>) je ispod minimuma koji ste definisali za ovaj predmet (<?=$params[min_clanova_tima]?>).</span>	
		<?php
			} //if count members < minimal defined
		?>
        
    </div>	
<table class="projekti" border="0" cellspacing="0" cellpadding="2">
  <tr>
    <th width="200" align="left" valign="top" scope="row">Naziv</th>
    <td width="490" align="left" valign="top"><?=filtered_output_string($project['naziv'])?></td>
  </tr>
  <tr>
    <th width="200" align="left" valign="top" scope="row">Prijavljeni studenti</th>
    <td width="490" align="left" valign="top">
    	<?php
			if (empty($members))
				echo 'Nema prijavljenih studenata.';
			else
			{
		?>
        <ul>
        <?php
				foreach ($members as $member)
				{
		?>
        	<li><?=filtered_output_string($member[prezime] . ' ' . $member[ime] . ', ' . $member[brindexa]); ?></li>
		<?php		
				}
		?>
        </ul>	
		<?php	
			}
		
		?>
    
    </td>
  </tr>
  <tr>
    <th width="200" align="left" valign="top" scope="row">Opis</th>
    <td width="490" align="left" valign="top"><?=filtered_output_string($project['opis'])?></td>
  </tr>
</table>


<?php
	} //foreach project
	
	} //action not set
	else
	{
		if ($action == 'param')
		{
		
			if (!isset($_REQUEST['submit']))
			{
?>
				 <h2>Parametri projekata</h2>
				<?php
					print genform("POST", "editForm");
				?>
				<!--<action="<?=$linkPrefix . "&action=param" ?>" method="post" enctype="multipart/form-data" name="editForm" id="editForm">-->
				<div id="formDiv">
					Polja sa * su obavezna. <br />
                	<div class="row">
						<span class="label">Zaključaj stanje projekata i timova</span>
						<span class="formw"><input name="lock" type="checkbox" id="lock" <?php 
							if ($params['zakljucani_projekti'] == 1)
								echo 'checked';
						?> /></span> <br /><br />
                        Ova opcija će onemogućiti prijavljivanje na projekte i pokrenuti projektne stranice.
					</div>
                    
					<div class="row">
						<span class="label">MIN timova *</span>
						<span class="formw"><input name="min_timova" type="text" id="min_timova" size="10" value="<?php echo $params['min_timova'];?>" /></span> 
					</div>
                    <div class="row">
						<span class="label">MAX timova *</span>
						<span class="formw"><input name="max_timova" type="text" id="max_timova" size="10" value="<?php echo $params['max_timova']?>" /></span> 
					</div>
                    
                    <div class="row">
						<span class="label">MIN članova tima *</span>
						<span class="formw"><input name="min_clanova_tima" type="text" id="min_clanova_tima" size="10" value="<?php echo $params['min_clanova_tima']?>" /></span> 
					</div>
                    <div class="row">
						<span class="label">MAX članova tima *</span>
						<span class="formw"><input name="max_clanova_tima" type="text" id="max_clanova_tima" size="10" value="<?php echo $params['max_clanova_tima']?>" /></span> 
					</div>

					<div class="row">	
						<span class="formw" style="margin-left:150px;"><input name="submit" type="submit" id="submit" value="Potvrdi"/></span>
					</div>
				
				</div><!--formDiv-->
				
				
				</form>
				
				
<?php				
						
			}
			else
			{
				$errorText = formProcess_param();
				if($errorText == '')
				{
					nicemessage('Uspješno ste uredili parametre projekata.');
					zamgerlog("korisnik u$userid uredio parametre projekata na predmetu pp$_REQUEST[predmet]", 2);		
					$link = $linkPrefix;		
				}
				else
				{	
					//an error occured trying to process the form
					niceerror($errorText);
					$link = "javascript:history.back();";					
				}
				nicemessage('<a href="'. $link .'">Povratak.</a>');
				
			} //submitted the form
		
		} //action == param		
		elseif ($action == 'addProject')
		{
		
			if (empty($params) && !isset($_REQUEST['submit']))
			{
				nicemessage("Prvo podesite parametre projekata.");
				nicemessage('<a href="'. $linkPrefix .'&action=param">Parametri projekata</a>');
				return;
			}
			if ($params[zakljucani_projekti] == 1)
			{
				niceerror("Zaključali ste stanje projekata na ovom predmetu. Nije moguće napraviti novi projekat.");
				nicemessage('<a href="'. $linkPrefix .'&action=param">Parametri projekata</a>');
				return;
			}
			
			if (!isset($_REQUEST['submit']))
			{
		
	?>	
				 <h2>Novi projekat</h2>
	<?php
   				 print genform("POST", "addForm");
    ?>
                
                <div id="formDiv">
                	Polja sa * su obavezna. <br />
                
                	<div class="row">
                        <span class="label">Naziv *</span>
                        <span class="formw"><input name="naziv" type="text" id="naziv" size="70" /></span> 
                  	</div>
                    <div class="row">
                        <span class="label">Opis *</span>
                        <span class="formw"><textarea name="opis" cols="60" rows="15" wrap="physical" id="opis"></textarea></span>
                   	</div> 
                    
                    <div class="row">	
                      	<span class="formw" style="margin-left:150px;"><input name="submit" type="submit" id="submit" value="Potvrdi"/></span>
                    </div>
                
                </div><!--formDiv-->
                
                
                </form>
                
			
	<?php	
			} //not submitted yet
			else
			{
				$errorText = formProcess('add');
				if($errorText == '')
				{
					nicemessage('Novi projekat uspješno dodan.');
					zamgerlog("korisnik u$userid dodao novi projekat na predmetu pp$_REQUEST[predmet]", 2);		

					$link = $linkPrefix;			
				}
				else
				{	
					niceerror($errorText);
					$link = "javascript:history.back();";		
				}
				nicemessage('<a href="'. $link .'">Povratak.</a>');
			
			
			} //submitted the form
	
		} //action == addProject
		elseif ($action == 'editProject')
		{
			//edit item
			if (!isset($_REQUEST['submit']))
			{
				$entry = getProject($id);
			
?>
				 <h1>Uredi projekat</h1>
	<?php
   				 print genform("POST", "editForm");
    ?>
				
				<div id="formDiv">
					Polja sa * su obavezna. <br />
				
					<div class="row">
						<span class="label">Naziv *</span>
						<span class="formw"><input name="naziv" type="text" id="naziv" size="70" value="<?php echo $entry['naziv']?>" /></span> 
					</div>
					<div class="row">
						<span class="label">Opis *</span>
						<span class="formw"><textarea name="opis" cols="60" rows="15" wrap="physical" id="opis"><?php echo $entry['opis'] ?></textarea></span>
					</div> 
					
					<div class="row">	
						<span class="formw" style="margin-left:150px;"><input name="submit" type="submit" id="submit" value="Potvrdi"/></span>
					</div>
				
				</div><!--formDiv-->
				
				
				</form>
				
				
<?php				
						
			}
			else
			{
				$errorText = formProcess('edit');
				if($errorText == '')
				{
					nicemessage('Uspješno ste uredili projekat.');
					zamgerlog("korisnik u$userid uspješno uredio projekat na predmetu pp$_REQUEST[predmet]", 2);		

					$link = $linkPrefix;									
				}
				else
				{	
					//an error occured trying to process the form
					niceerror($errorText);
					$link = "javascript:history.back();";	
					
				}
				nicemessage('<a href="'. $link .'">Povratak.</a>');
				
			} //submitted the form
			
		
		} //action == editProject
		elseif ($action == 'addNoteOnProject'){
			
			$entry = getProject($id);
			if(!isset($_REQUEST['addNote'])){
				//plot form
				
?>
				<h3>Dodaj bilješku za projekat</h3>	
<?php 
				print genform('POST','addNote');					
?>			
				<div class="row">
					<span class="label">Bilješka:</span>
					<span class="formw"><textarea name="note" cols="60" rows="15" wrap="physical" id="opis"><?php echo $entry['biljeska'] ?></textarea></span>
				</div> 
					
				<div class="row">	
					<span class="formw" style="margin-left:150px;"><input name="addNote" type="submit" id="submit" value="Potvrdi"/></span>
				</div>
				</form>
<?php 
			}
			else{
				//process form
				$errorText = processAddNoteOnProjectForm($predmet, $ag);
				if($errorText == '')
				{
					nicemessage('Uspješno ste dodali biljesku.');
					zamgerlog("korisnik u$userid dodao biljesku na projekat $id na predmetu pp$_REQUEST[predmet]", 2);		
					$link = $linkPrefix;									
				}
				else
				{	
					//an error occured trying to process the form
					niceerror($errorText);
					$link = "javascript:history.back();";	
					
				}
				nicemessage('<a href="'. $link .'">Povratak.</a>');
				
				
			}			
		}
		elseif ($action == 'delProject')
		{
			//delete item
			if (isset($id) && is_int($id) && $id > 0)
			{
				if (!isset($_REQUEST['c']))
				{
					echo "Da li ste sigurni da zelite obrisati ovaj projekat? Svi podaci vezani za aktivnosti na ovom projektu ce biti obrisane.<br />";	
					echo '<a href="' . $linkPrefix .'&amp;action=delProject&amp;id=' . $id . '&amp;c=true">Da</a> | <a href="' . $linkPrefix . '">Odustani</a>';			
				}
				else
				{
					if ($_REQUEST['c'] == 'true')
					{
						//delete the record
						if (deleteProject($id))
						{
							nicemessage('Uspjesno ste obrisali projekat.');	
							zamgerlog("korisnik u$userid izbrisao projekat ID=$id na predmetu pp$_REQUEST[predmet]", 4);		

							$link = $linkPrefix;		
						}
						else
						{
							niceerror('Doslo je do greske prilikom brisanja projekta. Molimo kontaktirajte administratora.');		

							$link = "javascript:history.back();";	
						}
						nicemessage('<a href="'. $link .'">Povratak.</a>');
					}
					
				} //else isset c get parameter
						
				
			} //id is okay		
		
		} //action == delProject
		elseif ($action == 'page')
		{
			require_once('common/projektneStrane.php');
			common_projektneStrane();
				
		} //action == page
		elseif ( $action == 'addStudent' or $action=='addStudent#'  )
		{
						
?>
			<div id="parentDiv">
			<!-- Ako je prvi put ucitano, dohvati predmete i dohvati sve studente na predmetu, prikazi formu. -->
			<?php 
			if (!isset($_REQUEST['dodaj']) && !isset($_REQUEST['brisi']))
			{
				drawStudentAndProjectTable($predmet, $ag); //iscrtaj tabelu
				
			?>	
				</br>
				<div class="obradaStudentaDiv">
					<div>
					<b>LISTA STUDENATA BEZ PROJEKTA:</b>
					
						<? $lijencine=fetchStudentiBezProjekta($predmet,$ag);
							if(empty($lijencine)){
								nicemessage('Svim studentima je dodijeljen projekat!');
							}else{
								$cnt = 0;
								
								foreach($lijencine as $lijen){
									$cnt = $cnt+1;
									print "</br>";
									print  "<span id=\"noProjectStudent\">" . $cnt . ".  " . $lijen['ime'] . " " . $lijen['prezime'] . "</span>";
								}
								
							}
						
						?>
						
					</div>
				</div>
				</br>				
				<div class="obradaStudentaDiv">
				<div>
				<div><b>DODAVANJE STUDENTA NA PROJEKAT</b></div>
				<div><span class="napomena">*Uputa:</span> Izaberite studenta, a zatim projekat i konačno kliknite Upiši!</div>
			<?php 
				print genform("POST", "moveStudent"); //generisi prvu liniju forme
			?>
					
					Student : <select name="student">
								<?php 
									$cnt = 0;
									$studentsOnPredmet = fetchStudentsOnPredmet($predmet,$ag);
									foreach($studentsOnPredmet as $stud)
									{
										$cnt = $cnt+1;
									
								?>	
									<option value="<?= $stud['id']?>"><?= filtered_output_string($cnt . '.' . $stud['ime'] . ' ' . $stud['prezime'])?></option>
								<?	} ?>
						  	</select><br/>
					
					
					Projekat :<select name="projekat">
								<?php 
									$cnt2 = 0;
									$projects = fetchProjects($predmet, $ag);
				  					$rowcounter = 0;
									foreach($projects as $proj)
									{
										$cnt2 = $cnt2 +1;
								?>
									<option value="<?= $proj['id']?>"><?= filtered_output_string($cnt2 . '.' . $proj['naziv'])?></option>
								<?  }?>
		   				  	</select>
		   					<br />
		   			
							<input name="dodaj" type="submit" value="Upiši" onclick="removeTable('parentDiv','expandable_table')"/>
				</form>
				</div>
				</div>
				
				</br>
				<!-- Forma za izbacivanje studenta sa projekta -->
				<div class="obradaStudentaDiv">
				<div>
				<div><b>BRISANJE STUDENTA SA PROJEKTA</b></div>
				<div><span class="napomena">*Uputa:</span> Izaberite studenta, a zatim projekat i konacno kliknite Ispiši!</div>
			<?php 
				print genform("POST", "unregStudent"); //generisi prvu liniju forme
			?>
					
					Student : <select name="student">
								<?php 
									$cnt = 0;
									$studentsOnPredmet = fetchStudentsOnPredmet($predmet,$ag);
									foreach($studentsOnPredmet as $stud)
									{
										$cnt = $cnt+1;
									
								?>	
									<option value="<?= $stud['id']?>"><?= filtered_output_string($cnt . '.' . $stud['ime'] . ' ' . $stud['prezime'])?></option>
								<?	} ?>
						  	</select><br/>
					
					
					Projekat :<select name="projekat">
								<?php 
									$cnt2 = 0;
									$projects = fetchProjects($predmet, $ag);
				  					$rowcounter = 0;
									foreach($projects as $proj)
									{
										$cnt2 = $cnt2 +1;
								?>
									<option value="<?= $proj['id']?>"><?= filtered_output_string($cnt2 . '.' . $proj['naziv'])?></option>
								<?  }?>
		   				  	</select>
		   					<br />
		   			
							<input name="brisi" type="submit" value="Ispiši" onclick="removeTable('parentDiv','expandable_table')"/>
				</form>
				</div>
				</div>	
				
				<!-- Kraj forme za izbacivanje studenta sa projekta -->
				
			<? 
			}
			else //on submit
			{
				if(isset($_REQUEST['dodaj']))
				{
					
					$errorText = processChangeProjectForm($predmet, $ag);
					drawStudentAndProjectTable($predmet, $ag);
					if($errorText == '')
					{
						nicemessage('Student je uspiješno prijavljen na projekat!');
						zamgerlog("korisnik $userid uspješno promijenio članstvo studenta $_REQUEST[student] na projektu $_REQUEST[projekat]", 2);		
						$link = $linkPrefix . "&action=addStudent";
					}
					else
					{	
						//an error occured trying to process the form
						niceerror($errorText);
						$link = "javascript:history.back();";	
					}
					nicemessage('<a href="'. $link .'">Povratak.</a>');
				
				} //forma za dodavanje studenta submitana
				else if(isset($_REQUEST['brisi']))
				{
					$errorText = processDeleteFromProjectForm($predmet, $ag);
					drawStudentAndProjectTable($predmet, $ag);
					if($errorText == '')
					{
						nicemessage('Student je uspiješno obrisan sa projekta!');
						zamgerlog("korisnik $userid uspješno odbacio članstvo studenta $_REQUEST[student] na projektu $_REQUEST[projekat]", 2);		
						$link = $linkPrefix . "&action=addStudent";
					}
					else
					{	
						//an error occured trying to process the form
						niceerror($errorText);
						$link = "javascript:history.back();";	
					}
					nicemessage('<a href="'. $link .'">Povratak.</a>');
				
				}
			}
			?>
			</div>
			
<?php 		
			}//action -addStudent
	} //else - action is set
} //function

function drawStudentAndProjectTable($predmet, $ag){
	print "<table id=\"expandable_table\">
			<thead id=\"project_list_header\">
				<tr><td><b>ČLANOVI PROJEKTA</b></td>
		            <td><b>NAZIV PROJEKTA</b></td>
		       </tr>
		    </thead>
            <tbody>";
			
				  $projects = fetchProjects($predmet, $ag);
				  $rowcounter = 0;
				  foreach ($projects as $project){
					$membs = fetchProjectMembers($project['id']);

					if ($rowcounter % 2 == 0){
						echo "<tr class=\"marked_row\">";
					}else{
						echo "<tr>";
					}
						 
					print "<td id=\"member_list\"><ul style=\"list-style-type:decimal\">";
							 foreach($membs as $memb){
									print "<li>" . filtered_output_string($memb[prezime] . ' ' . $memb[ime] . ' ' . $memb[brindexa]);
									print "</li>";
						     }
						    print "</ul>";
						    print "<span style=\"display:none\"></span></td>";
	                	print "<td id=\"project_name\">" . filtered_output_string($project['naziv']) . "  "; 
	                	print "<a href=\"#\">|+|</a>";
	                	print "<span class=\"detail_text\" style=\"display:none;\">" . filtered_output_string($project['biljeska']) . "</span></td>";
	                print "</tr>";
                
				  	$rowcounter = $rowcounter +1;
				  }
			
            print"</tbody></table>";
	
}


function processDeleteFromProjectForm($predmet, $ag){
	$errorText = '';
	if(!check_csrf_token())
	{
		biguglyerror("Mrš odavle");
		zamgerlog("1337 h4x0r detected",3);
		return "ERROR";
	}
	//get variables
	$stud_id = intval($_REQUEST['student']);
	$proj_id = intval($_REQUEST['projekat']);
	
	$errorText = unregisterFromProject($stud_id, $proj_id, $predmet, $ag);
	return $errorText;
	
}

function processAddNoteOnProjectForm($predmet, $ag){
	
	$errorText = '';
	if (!check_csrf_token()) 
	{
		biguglyerror("Mrš odavle");
		zamgerlog("1337 h4x0r detected",3);
		return "ERROR";
   	}
   	
   	$biljeska = $_REQUEST['note'];
   	$project_id = $_REQUEST['id'];
   	
   	if(!updateProjectNote($project_id,$biljeska)){
   		$errorText = "Doslo je do greske prilikom dodavanja biljeske, kontaktirajte administratora!";
   		zamgerlog("greška prilikom unosa biljeske za projekat $project_id u bazu(predmet pp$predmet, korisnik u$userid)", 3);
   	}
   	
   	return $errorText;
	
}


function processChangeProjectForm($predmet, $ag){

	$errorText = '';
	if(!check_csrf_token())
	{
		biguglyerror("Mrš odavle");
		zamgerlog("1337 h4x0r detected",3);
		return "ERROR";
	}
	
	//get variables
	$stud_id = intval($_REQUEST['student']);
	$proj_id = intval($_REQUEST['projekat']);
		
	$errorText = applyForProject($stud_id, $proj_id, $predmet, $ag);
	return $errorText;
}

function formProcess($option)
{
	$errorText = '';
	if (!check_csrf_token()) 
	{
		biguglyerror("Mrš odavle");
		zamgerlog("1337 h4x0r detected",3);
		return "ERROR";
   	}
	
	if (!in_array($option, array('add', 'edit') ) )
	{
		$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';		
		return $errorText;		
	}
	
	//get variables
	$naziv = $_REQUEST['naziv'];
	$opis  = $_REQUEST['opis'];
	
	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);
	$id = intval($_REQUEST['id']);
	
	if (empty($naziv) || empty($opis))
	{
		$errorText = 'Unesite sva obavezna polja.';
		return $errorText;
	}

	$naziv = trim($naziv);
	$opis = trim($opis);
		
	$data = array(
				'naziv' => $naziv, 
				'opis'  => $opis, 
				'predmet' => $predmet,
				'ag' => $ag	
	);
	
	
	if ($option == 'add')
	{
		if (!insertProject($data))
		{
			$errorText = 'Došlo je do greške prilikom spašavanja podataka. Molimo kontaktirajte administratora.';
			zamgerlog("greška prilikom unosa novog projekta u bazu(predmet pp$predmet, korisnik u$userid)", 3);
			return $errorText;		
		}
	
	} //option == add
	else
	{
		if (!updateProject($data, $id))
		{
			$errorText = 'Došlo je do greške prilikom spašavanja podataka. Molimo kontaktirajte administratora.';
			zamgerlog("greška prilikom update projekta $id u bazi(predmet pp$predmet, korisnik u$userid)", 3);
			return $errorText;		
		}
	
	} //option == edit
	
	return $errorText;
	
}


function insertProject($data)
{
	//generate unique id value
	$id = generateIdFromTable('projekat');
	
	$query = sprintf("INSERT INTO projekat (id, naziv, opis, predmet, akademska_godina) VALUES ('%d', '%s', '%s', '%d', '%d')", 
											$id, 
											my_escape($data['naziv']), 
											my_escape($data['opis']), 
											intval($data['predmet']),
											intval($data['ag']) 
											
					);
	$result = myquery($query);	
	
	return ( $result == false ) ? false : true;
}
function updateProject($data, $id)
{
	$query = sprintf("UPDATE projekat SET naziv='%s', opis='%s' WHERE id='%d' LIMIT 1", 
											my_escape($data['naziv']), 
											my_escape($data['opis']), 
											intval($id) 
											
					);
	$result = myquery($query);	

	return ( $result == false ) ? false : true;
}


function deleteProject($id)
{
	//delete all data bound to this project
	deleteArticlesForProject($id);
	deleteFilesForProject($id);
	deleteLinksForProject($id);
	deleteRssForProject($id);
	deleteForumForProject($id);
	deleteStudentsForProject($id);
	
	$query = sprintf("DELETE FROM projekat WHERE id='%d' LIMIT 1", 
					intval($id)
					);
	
	$result = myquery($query);
	
	return ( $result == false ) ? false : true;
}

function deleteArticlesForProject($id)
{
	//bl_clanak	
	
	global $conf_files_path;
	$lokacijaclanaka ="$conf_files_path/projekti/clanci/";
	
	if (!rmdir_recursive($lokacijaclanaka . $id)) //delete all article files - images for this project
	{
		zamgerlog("greška prilikom brisanja direktorija clanci za projekat ID=$id iz fajl sistema", 3);
		return false;	
	}
	
	$query = sprintf("DELETE FROM bl_clanak WHERE projekat='%d'", 
					intval($id)
					);
	
	$result = myquery($query);
}

function deleteFilesForProject($id)
{
	//projekat_file
	
	global $conf_files_path;
	
	$lokacijafajlova ="$conf_files_path/projekti/fajlovi/";
	
	if (!rmdir_recursive($lokacijafajlova . $id))
	{
		zamgerlog("greška prilikom brisanja direktorija fajlovi za projekat ID=$id iz fajl sistema", 3);
		return false;	
	}
		
}

function deleteLinksForProject($id)
{
	$query = sprintf("DELETE FROM projekat_link WHERE projekat='%d' ", 
					intval($id)
					);
	
	$result = myquery($query);

}

function deleteRssForProject($id)
{
	$query = sprintf("DELETE FROM projekat_rss WHERE projekat='%d' ", 
					intval($id)
					);
	
	$result = myquery($query);
}

function deleteForumForProject($id)
{
	//bb_tema...	
	$query = sprintf("DELETE FROM bb_post_text WHERE post IN (SELECT id FROM bb_post WHERE tema IN (SELECT id FROM bb_tema WHERE projekat='%d') ) ", 
					intval($id)
					);
	
	$result = myquery($query);
	
	$query = sprintf("DELETE FROM bb_post WHERE tema IN (SELECT id FROM bb_tema WHERE projekat='%d') ", 
					intval($id)
					);
	
	$result = myquery($query);
	
	$query = sprintf("DELETE FROM bb_tema WHERE projekat='%d' ", 
					intval($id)
					);
	
	$result = myquery($query);
}

function deleteStudentsForProject($id)
{
	$query = sprintf("DELETE FROM student_projekat WHERE projekat='%d' ", 
					intval($id)
					);
	
	$result = myquery($query);
}

function formProcess_param()
{
	$errorText = '';
	if (!check_csrf_token()) 
	{
		biguglyerror("Mrš odavle");
		zamgerlog("1337 h4x0r detected",3);
		return "ERROR";
   	}
	
	//get variables
	$min_timova = intval($_REQUEST['min_timova']);
	$max_timova  = intval($_REQUEST['max_timova']);
	
	$min_clanova_tima = intval($_REQUEST['min_clanova_tima']);
	$max_clanova_tima = intval($_REQUEST['max_clanova_tima']);
	
	$zakljucani_projekti = 0;
	if (isset($_REQUEST['lock']))
		$zakljucani_projekti = 1;
	
	$predmet = intval($_REQUEST['predmet']);
	$ag		 = intval($_REQUEST['ag']);
	
	
	if (empty($min_timova) || empty($max_timova)
	 || empty($min_clanova_tima) || empty($max_clanova_tima))
	{
		$errorText = 'Unesite sva obavezna polja.';
		return $errorText;
	}
	if ($min_timova <= 0 || $max_timova <= 0 || $min_clanova_tima <= 0 || $max_clanova_tima <= 0)
	{
		$errorText = 'Unesite pozitivne vrijednosti u formu.';
		return $errorText;
	}

	$data = array(
				'min_timova' => $min_timova, 
				'max_timova' => $max_timova, 
				'min_clanova_tima' => $min_clanova_tima, 
				'max_clanova_tima' => $max_clanova_tima, 
				'zakljucani_projekti' => $zakljucani_projekti 
	);
	
	if (!replacePredmetParams($data, $predmet, $ag))
	{
		$errorText = 'Došlo je do greške prilikom spašavanja podataka. Molimo kontaktirajte administratora.';
		zamgerlog("greska prilikom spasavanja parametara na projektu $projekat(predmet pp$predmet, korisnik u$userid)", 3);
		return $errorText;		
	}


	return $errorText;
	
}
function replacePredmetParams($data, $predmet, $ag)
{
	$query = sprintf("REPLACE predmet_projektni_parametri SET predmet='%d', akademska_godina='%d', min_timova='%d', max_timova='%d', min_clanova_tima='%d', max_clanova_tima='%d', zakljucani_projekti='%d'", 
											$predmet,
											$ag, 
											$data['min_timova'], 
											$data['max_timova'],
											$data['min_clanova_tima'],
											$data['max_clanova_tima'], 
											$data['zakljucani_projekti']
											
					);
	$result = myquery($query);	

	return ( $result == false ) ? false : true;


}


?>