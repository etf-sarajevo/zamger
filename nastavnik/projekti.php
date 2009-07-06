<?php
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
	$action 	= $_GET['action'];
	$id			= intval($_GET['id']);
		
	?>
<LINK href="css/projekti.css" rel="stylesheet" type="text/css">
<h2>Projekti</h2>
<?php	
	
	$params = getPredmetParams($predmet);
	if ($action!= 'page')
	{
	?>
     <div class="links">
            <ul class="clearfix">
            	<li><a href="<?php echo $linkPrefix . "&action=param"?>">Parametri projekata</a></li>
                <li><a href="<?php echo $linkPrefix ?>">Lista projekata</a></li>
                <li class="last"><a href="<?php echo $linkPrefix . "&action=addProject" ?>">Novi projekat</a></li>
            </ul>   
    </div>	
    <?php	
	}
	
	if (!isset($action))
	{
		
?>
<h2>Lista projekata</h2>
<?php
	$projects = fetchProjects($predmet);
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
		$countTeamsForPredmet = getCountNONEmptyProjectsForPredmet($predmet);
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
		
			if (!isset($_POST['submit']))
			{
?>
				 <h2>Parametri projekata</h2>
			
				<form action="<?=$linkPrefix . "&action=param" ?>" method="post" enctype="multipart/form-data" name="editForm" id="editForm">
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
					zamgerlog("korisnik u$userid uredio parametre projekata na predmetu p$_GET[predmet]", 2);		
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
		
			if (empty($params) && !isset($_POST['submit']))
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
			
			if (!isset($_POST['submit']))
			{
		
	?>	
    		
				 <h2>Novi projekat</h2>
				
                <form action="<?=$linkPrefix . "&action=addProject" ?>" method="post" enctype="multipart/form-data" name="addForm" id="addForm">
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
					zamgerlog("korisnik u$userid dodao novi projekat na predmetu p$_GET[predmet]", 2);		

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
			if (isset($id) && is_int($id) && $id > 0)
			{
				if (!isset($_POST['submit']))
				{
					$entry = getProject($id);
				
	?>
					 <h1>Uredi projekat</h1>
				
                    <form action="<?=$linkPrefix . "&action=editProject&amp;id=$id" ?>" method="post" enctype="multipart/form-data" name="editForm" id="editForm">
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
						zamgerlog("korisnik u$userid uspješno uredio projekat na predmetu p$_GET[predmet]", 2);		

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
						
				
			} //id is okay	
		
		} //action == editProject
		elseif ($action == 'delProject')
		{
			//delete item
			if (isset($id) && is_int($id) && $id > 0)
			{
				if (!isset($_GET['c']))
				{
					echo "Da li ste sigurni da zelite obrisati ovaj projekat? Svi podaci vezani za aktivnosti na ovom projektu ce biti obrisane.<br />";	
					echo '<a href="' . $linkPrefix .'&amp;action=delProject&amp;id=' . $id . '&amp;c=true">Da</a> | <a href="' . $linkPrefix . '">Odustani</a>';			
				}
				else
				{
					if ($_GET['c'] == 'true')
					{
						//delete the record
						if (deleteProject($id))
						{
							nicemessage('Uspjesno ste obrisali projekat.');	
							zamgerlog("korisnik u$userid izbrisao projekat ID=$id na predmetu p$_GET[predmet]", 4);		

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

	
	} //else - action is set




} //function



function formProcess($option)
{
	if (!in_array($option, array('add', 'edit') ) )
	{
		$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';		
		return $errorText;		
	}
	
	//get variables
	$naziv = $_POST['naziv'];
	$opis  = $_POST['opis'];
	
	$predmet = intval($_GET['predmet']);
	$id = intval($_GET['id']);
	
	$errorText = '';
	
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
				'predmet' => $predmet	
	);
	
	
	if ($option == 'add')
	{
		if (!insertProject($data))
		{
			$errorText = 'Došlo je do greške prilikom spašavanja podataka. Molimo kontaktirajte administratora.';
			zamgerlog("greška prilikom unosa novog projekta u bazu(predmet p$predmet, korisnik u$userid)", 3);
			return $errorText;		
		}
	
	} //option == add
	else
	{
		if (!updateProject($data, $id))
		{
			$errorText = 'Došlo je do greške prilikom spašavanja podataka. Molimo kontaktirajte administratora.';
			zamgerlog("greška prilikom update projekta $id u bazi(predmet p$predmet, korisnik u$userid)", 3);
			return $errorText;		
		}
	
	} //option == edit
	
	return $errorText;
	
}


function insertProject($data)
{
	//generate unique id value
	$id = generateIdFromTable('projekat');
	
	$query = sprintf("INSERT INTO projekat (id, naziv, opis, predmet) VALUES ('%d', '%s', '%s', '%d')", 
											$id, 
											my_escape($data['naziv']), 
											my_escape($data['opis']), 
											intval($data['predmet']) 
											
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
	//get variables
	$min_timova = intval($_POST['min_timova']);
	$max_timova  = intval($_POST['max_timova']);
	
	$min_clanova_tima = intval($_POST['min_clanova_tima']);
	$max_clanova_tima = intval($_POST['max_clanova_tima']);
	
	$zakljucani_projekti = 0;
	if (isset($_POST['lock']))
		$zakljucani_projekti = 1;
	
	$predmet = intval($_GET['predmet']);
	
	$errorText = '';
	
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
	
	if (!replacePredmetParams($data, $predmet))
	{
		$errorText = 'Došlo je do greške prilikom spašavanja podataka. Molimo kontaktirajte administratora.';
		zamgerlog("greska prilikom spasavanja parametara na projektu $projekat(predmet p$predmet, korisnik u$userid)", 3);
		return $errorText;		
	}


	return $errorText;
	
}
function replacePredmetParams($data, $predmet)
{
	$query = sprintf("REPLACE predmet_parametri SET predmet='%d', min_timova='%d', max_timova='%d', min_clanova_tima='%d', max_clanova_tima='%d', zakljucani_projekti='%d'", 
											$predmet, 
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