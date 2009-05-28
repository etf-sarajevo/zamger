<?php
require_once("lib/projekti.php");

function nastavnik_projekti()
{
	//debug mod aktivan
	global $conf_debug, $userid, $user_nastavnik;
	$predmet = intval($_REQUEST['predmet']);
	
	if ($predmet <=0)
	{
		//hijack attempt?
		zamgerlog("korisnik u$userid pokusao pristupiti modulu nastavnik/projekti sa ID predmeta koji nije integer ili je <=0", 3);		
		return;
	}
	
	if ($user_nastavnik == false)
	{
		//hijack attempt?
		zamgerlog("korisnik u$userid pokusao pristupiti modulu student/projekti iako nije student", 3);		
		return;
	}

	$linkPrefix = "?sta=nastavnik/projekti&predmet=$predmet";
	$action 	= $_GET['action'];
	$id			= intval($_GET['id']);
	
	$conf_debug = 1;
	

	//bad userid
	if (!is_numeric($userid) || $userid <=0)
	{
		zamgerlog("korisnik sa losim ID koji nije integer ili je <=0 pokusao pristupiti modulu nastavnik/projekti na predmetu p$predmet", 3);				
		return;	
	}
	
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
    <td width="700" align="left" valign="top"><?=filtered_output_string($project['naziv'])?></td>
  </tr>
  <tr>
    <th width="200" align="left" valign="top" scope="row">Prijavljeni studenti</th>
    <td width="700" align="left" valign="top">
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
    <td width="700" align="left" valign="top"><?=filtered_output_string($project['opis'])?></td>
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
						<span class="label">Zakljucaj stanje projekata i timova</span>
						<span class="formw"><input name="lock" type="checkbox" id="lock" <?php 
							if ($params['zakljucani_projekti'] == 1)
								echo 'checked';
						?> /></span> <br /><br />
                        Ova opcija ce onemoguciti dalje prijavljivanje za projekte i pokrenuti projektne stranice.
					</div>
                    
					<div class="row">
						<span class="label">MIN timova *</span>
						<span class="formw"><input name="min_timova" type="text" id="min_timova" size="10" value="<?php echo stripslashes(htmlentities($params['min_timova'], ENT_QUOTES))?>" /></span> 
					</div>
                    <div class="row">
						<span class="label">MAX timova *</span>
						<span class="formw"><input name="max_timova" type="text" id="max_timova" size="10" value="<?php echo stripslashes(htmlentities($params['max_timova'], ENT_QUOTES))?>" /></span> 
					</div>
                    
                    <div class="row">
						<span class="label">MIN clanova tima *</span>
						<span class="formw"><input name="min_clanova_tima" type="text" id="min_clanova_tima" size="10" value="<?php echo stripslashes(htmlentities($params['min_clanova_tima'], ENT_QUOTES))?>" /></span> 
					</div>
                    <div class="row">
						<span class="label">MAX clanova tima *</span>
						<span class="formw"><input name="max_clanova_tima" type="text" id="max_clanova_tima" size="10" value="<?php echo stripslashes(htmlentities($params['max_clanova_tima'], ENT_QUOTES))?>" /></span> 
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
					nicemessage('Uspjesno ste uredili parametre projekata.');
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
				niceerror("Zaključali ste stanje sa projektima na ovom predmetu. Nije moguće napraviti novi projekat.");
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
					nicemessage('Novi projekat uspjesno dodan.');
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
                            <span class="formw"><input name="naziv" type="text" id="naziv" size="70" value="<?php echo stripslashes(htmlentities($entry['naziv'], ENT_QUOTES))?>" /></span> 
                        </div>
                        <div class="row">
                            <span class="label">Opis *</span>
                            <span class="formw"><textarea name="opis" cols="60" rows="15" wrap="physical" id="opis"><?php echo stripslashes(htmlentities($entry['opis'], ENT_QUOTES)) ?></textarea></span>
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
						nicemessage('Uspjesno ste uredili projekat.');
						zamgerlog("korisnik u$userid uspjesno uredio projekat na predmetu p$_GET[predmet]", 2);		

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
	
	$opis = strip_tags($opis);
	$naziv = strip_tags($naziv);
	
	
	$data = array(
				'naziv' => $naziv, 
				'opis'  => $opis, 
				'predmet' => $predmet	
	);
	
	
	if ($option == 'add')
	{
		if (!insertProject($data))
		{
			$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';
			return $errorText;		
		}
	
	} //option == add
	else
	{
		if (!updateProject($data, $id))
		{
			$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';
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
	$query = sprintf("DELETE FROM projekat WHERE id='%d' LIMIT 1", 
					intval($id)
					);
	
	$result = myquery($query);
	
	//delete all data bound to this project
	deleteArticlesForProject($id);
	deleteFilesForProject($id);
	deleteLinksForProject($id);
	deleteRssForProject($id);
	deleteForumForProject($id);
	deleteStudentsForProject($id);
	
	return ( $result == false ) ? false : true;
}

function deleteArticlesForProject($id)
{
	//bl_clanak	
	
	global $conf_files_path;
	
	$list = fetchArticlesForProject($id);
	
	foreach ($list as $item)
	{
		if ($item['slika'] != '')
		{
			$lokacijaclanaka ="$conf_files_path/projekti/clanci/" . $item['projekat'] . '/' . $item['osoba'] . '/';
			unlink($lokacijaclanaka . $item['slika']);
		}
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
	
	$allFiles = fetchFilesForProjectAllRevisions($id);
	foreach ($allFiles as $list)
	{
		foreach ($list as $item)
		{
			
			$query = sprintf("DELETE FROM projekat_file WHERE id='%d' LIMIT 1", 
						intval($item[id])
						);
		
			$result = myquery($query);
			if (mysql_affected_rows() == 0)
				return false;
				
			$lokacijarevizije = "$conf_files_path/projekti/fajlovi/" . $item['projekat'] . '/' . $item['osoba'] . '/' . $item['filename'] . '/v' . $item['revizija'];
			
			if (!unlink($lokacijarevizije . '/' . $item[filename]))
				return false;	
			if (!rmdir($lokacijarevizije))
				return false;
				
			//remove any diffs for this file
			myquery("DELETE FROM projekat_file_diff WHERE file='" . $item[id] . "' LIMIT 1");
		}
		
		$lokacijafajlova = "$conf_files_path/projekti/fajlovi/" . $list[0]['projekat'] . '/' . $list[0]['osoba'] . '/' . $list[0]['filename'];
		if (!rmdir($lokacijafajlova))
			return false;
		
		return true;
	
	} //foreach allFiles
		
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
	$query = sprintf("DELETE FROM osoba_projekat WHERE projekat='%d' ", 
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
		$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';
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