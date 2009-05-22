<?php
require_once("lib/projekti.php");
function student_projekti()
{
	//debug mod aktivan
	global $conf_debug, $userid, $user_student;
	$predmet = intval($_REQUEST['predmet']);
	
	if ($predmet <=0)
	{
		//hijack attempt?
		zamgerlog("korisnik u$userid pokusao pristupiti modulu student/projekti sa ID predmeta koji nije integer ili je <=0", 3);		
		return;
	}
	if ($user_student == false)
	{
		//hijack attempt?
		zamgerlog("korisnik u$userid pokusao pristupiti modulu student/projekti iako nije student", 3);		
		return;
	}

	
	$linkPrefix = "?sta=student/projekti&predmet=$predmet";
	$action 	= $_GET['action'];
	$id			= intval($_GET['id']);
	
	$conf_debug = 1;

	//bad userid
	if (!is_numeric($userid) || $userid <=0)
	{
		zamgerlog("korisnik sa losim ID koji nije integer ili je <=0 pokusao pristupiti modulu student/projekti na predmetu p$predmet", 3);				
		return;	
	}
	
	
	if (!isset($action))
	{
	
		$predmetParams = getPredmetParams($predmet);
	
?>

<h2>Projekti</h2>
<span class="notice">
Nastavnik je definisao sljedece parametre svih projekata:
    <ul>
        <li>Broj timova: <?php
        	if ($predmetParams[min_timova] == $predmetParams[max_timova])
				echo 'tačno ' . $predmetParams[max_timova];
			else
				echo 'od ' . $predmetParams[min_timova] . ' do ' . $predmetParams[max_timova];
			?></li>
        <li>Broj članova tima: <?php
        	if ($predmetParams[min_clanova_tima] == $predmetParams[max_clanova_tima])
				echo 'tačno ' . $predmetParams[max_clanova_tima];
			else
				echo 'od ' . $predmetParams[min_clanova_tima] . ' do ' . $predmetParams[max_clanova_tima];
			?></li>
    </ul>
    Prijavite se na projekat i automatski se učlanjujete u projektni tim ili kreirate novi tim. Da biste promijenili tim, prijavite se u drugi tim.
</span>	<br />
<?php
  	if ($predmetParams[zakljucani_projekti] == 1)
		{
?>
	<span class="notice">Onemogučene su prijave u projektne timove. Otvorene su projektne stranice.</span>	
<?php
		} //locked projects
		else
		{
?>
	<span class="noticeGreen">Moguće su prijave u projetne timove. Nastavnik još uvijek nije kompletirao prijave.</span>	

<?php
		}
?>
<?php
	
	
	$teamLimitReached = isTeamLimitReachedForPredmet($predmet);
		
	$actualProjectForUser = getActualProjectForUserInPredmet($userid, $predmet);
	
	if (!empty($actualProjectForUser))
	{
		//user already in a project on this predmet
		$nMembers = getCountMembersForProject($actualProjectForUser[id]);
		//after I leave actual team, I will be able to create a new project team because this one will be empty...
		if ($nMembers -1 == 0)
		{
			//reset the team limit
			$teamLimitReached = false;
		}	
	}
	$projects = array();
	if ($predmetParams[zakljucani_projekti] == 1)
	{
		if (!empty($actualProjectForUser))
			$projects[] = $actualProjectForUser;
	}
	else
		$projects = fetchProjects($predmet);
	
	foreach ($projects as $project)
	{

		$members = fetchProjectMembers($project[id]);
		
		$newTeamDeny = isProjectEmpty($project[id]) == true && $teamLimitReached == true;

		
?>
<h3><?=filtered_output_string($project['naziv'])?></h3>
<div class="links">
            <ul class="clearfix">
        <?php  
		if ($predmetParams['zakljucani_projekti'] == 0) //open for applications
		{
		   if (!empty($actualProjectForUser) && $actualProjectForUser[id] == $project[id])
		   {
		?>
				<li class="last"><a href="<?php echo $linkPrefix . "&projekat=$project[id]&action=getout"?>">Odustani od prijave na ovom projektu</a></li>	
		<?php 
		   }
		   	elseif(isProjectFull($project[id], $predmet) == true)
			{
		?>
        		<li style="color:red" class="last">Projekat je popunjen i ne prima prijave.</li>
        <?php
			}
			else
			{
				if ($newTeamDeny == false)
				{
					if (empty($actualProjectForUser))
					{
		?>
        		<li class="last"><a href="<?php echo $linkPrefix . "&projekat=$project[id]&action=apply"?>">Prijavi se na ovaj projekat</a></li>       	
		<?php
					}
					else
					{
		?>	
        		<li class="last"><a href="<?php echo $linkPrefix . "&projekat=$project[id]&action=apply"?>">Prijavi se na ovaj projekat / Promijeni članstvo</a></li>   	
		<?php
					}
				}
				else
				{
?>
        	<div style="color:red; margin-top: 10px;">Limit za broj timova dostignut. Ne možete kreirati novi tim. Prijavite se na projekte u kojima ima mjesta.</div>	
<?php
				}	
			}
		} //predmetparams locked == 0
		?>
        <?php
			if ($predmetParams['zakljucani_projekti'] == 1)
			{
		?>
        	<li class="last"><a href="<?= $linkPrefix . "&action=page&projekat=$project[id]" ?>">Projektna stranica</a></li>
		<?php
			}
		?>
            </ul>   
    </div>	
<table class="projekti" border="0" cellspacing="0" cellpadding="2">
  <tr>
    <th width="200" align="left" valign="top" scope="row">Naziv</th>
    <td width="700" align="left" valign="top"><?=filtered_output_string($project['naziv'])?></td>
  </tr>
  <tr>
    <th width="200" align="left" valign="top" scope="row">Prijavljeni tim / student</th>
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
	
	} //if action is not set
	else
	{
		if ($action == 'apply')
		{
			$projekat = intval($_GET['projekat']);
			if ($projekat <= 0)
			{
				zamgerlog("korisnik u$userid pokusao da se prijavi na projekat sa losim ID koji nije integer ili je <=0 na predmetu p$predmet", 3);				
				return;
			}
			$errorText = applyForProject($userid, $projekat, $predmet);
			if($errorText == '')
			{
		?>
        	<script type="text/javascript">window.location = '<?=$linkPrefix ?>'; </script>
        <?php						
			}
			else
			{	
				//an error occured trying to process the form
				niceerror($errorText);				
			}
			nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');
	
		
		
		} //action == apply
		elseif ($action == 'getout')
		{
			$projekat = intval($_GET['projekat']);
			if ($projekat <= 0)
			{
				zamgerlog("korisnik u$userid pokusao da se odjavi sa projekta sa losim ID koji nije integer ili je <=0 na predmetu p$predmet", 3);				
				return;
			}
			$errorText = getOutOfproject($userid, $predmet);
			if($errorText == '')
			{
		?>
        	<script type="text/javascript">window.location = '<?=$linkPrefix ?>'; </script>
        <?php						
			}
			else
			{	
				//an error occured trying to process the form
				niceerror($errorText);				
			}
			nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');
	
		
		
		} //action == getout
		elseif ($action == 'page')
		{
			require_once('common/projektneStrane.php');
			common_projektneStrane();
				
		} //action == page
		
	} //else - action is set
	
} //function


?>
