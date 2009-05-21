<?php
function common_projektneStrane()
{
	//debug mod aktivan
	global $conf_debug, $userid, $user_nastavnik, $user_student, $conf_files_path, $user_siteadmin;
	$predmet 	= intval($_REQUEST['predmet']);
	$projekat 	= intval($_REQUEST['projekat']);
	$action 	= $_GET['action'];
	
	//for project page only:
	$section 	= $_GET['section'];
	$subaction  = $_GET['subaction'];
	$id			= intval($_GET['id']);  //editing links, rss....
	if ($predmet <=0 || $projekat <=0)
	{
		//hijack attempt?
		zamgerlog("korisnik u$userid pokusao pristupiti modulu common/projektneStrane sa ID predmeta  ili ID projekta koji nije integer ili je <=0", 3);		
		return;
	}
	
	//bad userid
	if (!is_numeric($userid) || $userid <=0)
	{
		zamgerlog("korisnik sa losim ID koji nije integer ili je <=0 pokusao pristupiti modulu common/projektneStrane na predmetu p$predmet", 3);				
		return;	
	}

	if ($user_student && !$user_siteadmin) //ordinary student
	{
		$actualProject = getActualProjectForUserInPredmet($userid, $predmet);
		if ($actualProject[id] != $projekat)
		{
			//user is not in this project in this predmet...hijack attempt?
			zamgerlog("korisnik u$userid pokusao pristupiti modulu common/projektneStrane i projektu na kojem nije prijavljen ID=$projekat na predmetu p$predmet", 3);				
			return;	
		}
		
	}
	
	$params = getPredmetParams($predmet);
	$project = getProject($projekat);	
	$members = fetchProjectMembers($project[id]);
	
	if ($params[zakljucani_projekti] == 0)
	{
		zamgerlog("korisnik u$userid pokusao pristupiti modulu common/projektneStrane i projektu iako projektne strane jos nisu otvorene na predmetu p$predmet", 3);				
		return;
	}
	
	
	if ($user_student)
		$linkPrefix = "?sta=student/projekti&action=page&projekat=$projekat&predmet=$predmet";
	elseif ($user_nastavnik)
		$linkPrefix = "?sta=nastavnik/projekti&action=page&projekat=$projekat&predmet=$predmet";
	else
		return;
	
	$conf_debug = 1;
	

	?>   
     <div class="links">
            <ul>
            	<li><a href="<?php echo $linkPrefix?>">Početna strana</a></li>
            	<li><a href="<?php echo $linkPrefix . "&section=info"?>">Informacije o projektu</a></li>
                <li><a href="<?php echo $linkPrefix . "&section=links"?>">Korisni linkovi</a></li>
                <li><a href="<?php echo $linkPrefix . "&section=rss"?>">RSS feedovi</a></li>
                <li><a href="<?php echo $linkPrefix . "&section=bl"?>">Članci</a></li>
                <li><a href="<?php echo $linkPrefix . "&section=file"?>">Fajlovi</a></li>
                <li><a href="<?php echo $linkPrefix . "&section=bb"?>">Grupa za diskusiju</a></li>
            </ul>   
     </div>	
    <?php	
	
	
	
	if (!isset($section))
	{
		//display project start page
	?>
   
    <?php
	
	} //section not set
	else
	{
		if ($section == 'info')
		{
			// display project info
	?>
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
		
		} //section -- info
		elseif ($section == 'links')
		{
			//links management
			$linkPrefix .='&section=links';
	?>
<h2>Korisni linkovi</h2>
 <div class="links clearfix" id="link">
    <ul>
        <li><a href="<?php echo $linkPrefix?>">Lista linkova</a></li>
        <li><a href="<?php echo $linkPrefix . "&subaction=add"?>">Novi link</a></li>
    </ul>   
</div>	

    <?php	
			if (!isset($subaction))
			{
				$rowsPerPage = 20;
				$pageNum = 1;
				if(isset($_GET['page']))
				{
					$pageNum = $_GET['page'];
				}
				// counting the offset
				$offset = ($pageNum - 1) * $rowsPerPage;
				
				//display links for this project, with links to edit and delete
				$links = fetchLinksForProject($project[id], $offset, $rowsPerPage);
				foreach ($links as $link)
				{
					if (isUserAuthorOfLink($link[id], $userid))
					{
	?>
<div class="links clearfix" id="link">
    <ul>
        <li><a href="<?php echo $linkPrefix . "&subaction=edit&id=$link[id]"?>">Uredi</a></li>
        <li><a href="<?php echo $linkPrefix . "&subaction=del&id=$link[id]"?>">Briši</a></li>
    </ul>   
</div>	
	<?php
					} //if user is author of this item
	?>

<table class="linkovi" border="0" cellspacing="0" cellpadding="2">
  <tr>
    <th width="200" align="left" valign="top" scope="row">URL</th>
    <td width="700" align="left" valign="top">
    <?php
						$url = stripslashes(htmlentities($link[url], ENT_QUOTES));
						$scheme = parse_url($url);
						$scheme  = $scheme['scheme'];
					
						if ($scheme == '') //only www part	
							$url = 'http://' . $url;
						
						
	?><a href="<?=$url ?>" title="<?=stripslashes(htmlentities($link['naziv'], ENT_QUOTES))?>" target="_blank"><?=filtered_output_string($link[naziv]); ?></a>   
    </td>
  </tr>
  <tr>
    <th width="200" align="left" valign="top" scope="row">Opis</th>
    <td width="700" align="left" valign="top"><?=filtered_output_string($link['opis'])?></td>
  </tr>
</table>
    <?php
				} //foreach link
				$numrows = getCountLinksForProject($project[id]);
							
				$maxPage = ceil($numrows/$rowsPerPage);
				$self = $linkPrefix;
				
				if ($maxPage > 0)
				{
					echo "<span class=\"newsPages\">";
					if ($pageNum > 1)
					{
						$page = $pageNum - 1;
						$prev = " <a href=\"$self&page=$page\">[Prethodna]</a> ";
						
						$first = " <a href=\"$self&page=1\">[Prva]</a> ";
					} 
					
					if ($pageNum < $maxPage)
					{
						$page = $pageNum + 1;
						$next = " <a href=\"$self&page=$page\">[Sljedeća]</a> ";
						
						$last = " <a href=\"$self&page=$maxPage\">[Zadnja]</a> ";
					} 
					
					echo $first . $prev . " Strana <strong>$pageNum</strong> od ukupno <strong>$maxPage</strong> " . $next . $last;
					echo "</span>"; //newsPages span	
				}
				
			} //subactin not set
			else
			{
				if ($subaction == 'add')
				{
					
					if (!isset($_POST['submit']))
					{
				
	?>
						 <h3>Novi link</h3>
						
						<form action="<?=$linkPrefix . "&subaction=add" ?>" method="post" enctype="application/x-www-form-urlencoded" name="addForm" id="addForm">
						<div id="formDiv">
							Polja sa * su obavezna. <br />
							
                            <div class="row">
								<span class="label">Naziv *</span>
								<span class="formw"><input name="naziv" type="text" id="naziv" size="70" /></span> 
							</div>
	
							<div class="row">
								<span class="label">URL *</span>
								<span class="formw"><input name="url" type="text" id="url" size="70" /></span> 
							</div>
							<div class="row">
								<span class="label">Opis</span>
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
						$errorText = formProcess_links('add');
						if($errorText == '')
						{
							nicemessage('Novi link uspjesno dodan.');
							zamgerlog("korisnik u$userid uspjesno dodao novi link na projektu ID=$projekat na predmetu p$predmet", 2);				
							$link = $linkPrefix;			
						}
						else
						{	
							niceerror($errorText);
							$link = "javascript:history.back();";		
						}
						nicemessage('<a href="'. $link .'">Povratak.</a>');
					
					
					} //submitted the form
				
				} //subaction == add
				elseif ($subaction == 'edit')
				{
					if (!isUserAuthorOfLink($id, $userid))
						return;
					
					
					//edit item
					if (isset($id) && is_int($id) && $id > 0)
					{
						
						if (!isset($_POST['submit']))
						{
							$entry = getLink($id);
					
			?>
						 <h3>Uredi link</h3>
						
						<form action="<?=$linkPrefix . "&subaction=edit&id=$id" ?>" method="post" enctype="application/x-www-form-urlencoded" name="editForm" id="editForm">
						<div id="formDiv">
							Polja sa * su obavezna. <br />
							
                            <div class="row">
								<span class="label">Naziv *</span>
								<span class="formw"><input name="naziv" type="text" id="naziv" size="70" value="<?php echo stripslashes(htmlentities($entry['naziv'], ENT_QUOTES))?>" /></span> 
							</div>
	
							<div class="row">
								<span class="label">URL *</span>
								<span class="formw"><input name="url" type="text" id="url" size="70" value="<?php echo stripslashes(htmlentities($entry['url'], ENT_QUOTES))?>" /></span> 
							</div>
							<div class="row">
								<span class="label">Opis</span>
								<span class="formw"><textarea name="opis" cols="60" rows="15" wrap="physical" id="opis"><?php echo stripslashes(htmlentities($entry['opis'], ENT_QUOTES))?></textarea></span>
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
							$errorText = formProcess_links('edit');
							if($errorText == '')
							{
								nicemessage('Uspjesno ste uredili link.');
								zamgerlog("korisnik u$userid uspjesno uredio link na projektu ID=$projekat na predmetu p$predmet", 2);				
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
					
				} //subaction == edit
				elseif ($subaction == 'del')
				{
					if (!isUserAuthorOfLink($id, $userid))
						return;

					//delete item
					if (isset($id) && is_int($id) && $id > 0)
					{
						if (!isset($_GET['c']))
						{
							echo "Da li ste sigurni da zelite obrisati ovaj link?<br />";	
							echo '<a href="' . $linkPrefix . '&subaction=del&id=' . $id . '&c=true">Da</a> | <a href="' . $linkPrefix . '">Odustani</a>';			
						}
						else
						{
							if ($_GET['c'] == 'true')
							{
								//delete the record
								if (deleteLink($id))
								{
									nicemessage('Uspjesno ste obrisali link.');	
									zamgerlog("korisnik u$userid uspjesno obrisao link na projektu ID=$projekat na predmetu p$predmet", 2);				
									$link = $linkPrefix;		
								}
								else
								{
									niceerror('Doslo je do greske prilikom brisanja linka. Molimo kontaktirajte administratora.');
									$link = "javascript:history.back();";	
								}
								nicemessage('<a href="'. $link .'">Povratak.</a>');
							}
							
						} //else isset c get parameter
								
						
					} //id is okay		
				
				} //subaction == del
	
			} //subaction set
			
			
		} //section == links
		elseif ($section == 'rss')
		{
			//links management
			$linkPrefix .='&section=rss';
	?>
<h2>RSS feedovi</h2>
 <div class="links clearfix" id="rss">
    <ul>
        <li><a href="<?php echo $linkPrefix?>">Lista RSS feedova</a></li>
        <li><a href="<?php echo $linkPrefix . "&subaction=add"?>">Novi RSS feed</a></li>
    </ul>   
</div>	

    <?php	
			if (!isset($subaction))
			{
				$rowsPerPage = 20;
				$pageNum = 1;
				if(isset($_GET['page']))
				{
					$pageNum = $_GET['page'];
				}
				// counting the offset
				$offset = ($pageNum - 1) * $rowsPerPage;
				
				//display links for this project, with links to edit and delete
				$feeds = fetchRSSForProject($project[id], $offset, $rowsPerPage);
				foreach ($feeds as $link)
				{
					if (isUserAuthorOfRSS($link[id], $userid))
					{
	?>
<div class="links clearfix" id="rss">
    <ul>
        <li><a href="<?php echo $linkPrefix . "&subaction=edit&id=$link[id]"?>">Uredi</a></li>
        <li><a href="<?php echo $linkPrefix . "&subaction=del&id=$link[id]"?>">Briši</a></li>
    </ul>   
</div>	
	<?php
					} //if user is author of this item
	?>

<table class="rss" border="0" cellspacing="0" cellpadding="2">
  <tr>
    <th width="200" align="left" valign="top" scope="row">URL</th>
    <td width="700" align="left" valign="top">
    <?php
						$url = stripslashes(htmlentities($link[url], ENT_QUOTES));
						$scheme = parse_url($url);
						$scheme  = $scheme['scheme'];
					
						if ($scheme == '') //only www part	
							$url = 'http://' . $url;
						
						
	?><a href="<?=$url ?>" title="<?=stripslashes(htmlentities($link['naziv'], ENT_QUOTES))?>" target="_blank"><?=filtered_output_string($link[naziv]); ?></a>   
    </td>
  </tr>
  <tr>
    <th width="200" align="left" valign="top" scope="row">Opis</th>
    <td width="700" align="left" valign="top"><?=filtered_output_string($link['opis'])?></td>
  </tr>
</table>
    <?php
				} //foreach link
				$numrows = getCountRSSForProject($project[id]);
							
				$maxPage = ceil($numrows/$rowsPerPage);
				$self = $linkPrefix;
				
				if ($maxPage > 0)
				{
					echo "<span class=\"newsPages\">";
					if ($pageNum > 1)
					{
						$page = $pageNum - 1;
						$prev = " <a href=\"$self&page=$page\">[Prethodna]</a> ";
						
						$first = " <a href=\"$self&page=1\">[Prva]</a> ";
					} 
					
					if ($pageNum < $maxPage)
					{
						$page = $pageNum + 1;
						$next = " <a href=\"$self&page=$page\">[Sljedeća]</a> ";
						
						$last = " <a href=\"$self&page=$maxPage\">[Zadnja]</a> ";
					} 
					
					echo $first . $prev . " Strana <strong>$pageNum</strong> od ukupno <strong>$maxPage</strong> " . $next . $last;
					echo "</span>"; //newsPages span	
				}
				
			} //subactin not set
			else
			{
				if ($subaction == 'add')
				{
					
					if (!isset($_POST['submit']))
					{
				
	?>
						 <h3>Novi RSS feed</h3>
						
						<form action="<?=$linkPrefix . "&subaction=add" ?>" method="post" enctype="application/x-www-form-urlencoded" name="addForm" id="addForm">
						<div id="formDiv">
							Polja sa * su obavezna. <br />
							
                            <div class="row">
								<span class="label">Naziv *</span>
								<span class="formw"><input name="naziv" type="text" id="naziv" size="70" /></span> 
							</div>
	
							<div class="row">
								<span class="label">URL *</span>
								<span class="formw"><input name="url" type="text" id="url" size="70" /></span> 
							</div>
							<div class="row">
								<span class="label">Opis</span>
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
						$errorText = formProcess_rss('add');
						if($errorText == '')
						{
							nicemessage('Novi RSS feed uspjesno dodan.');
							zamgerlog("korisnik u$userid uspjesno dodao novi rss feed na projektu ID=$projekat na predmetu p$predmet", 2);				
							$link = $linkPrefix;			
						}
						else
						{	
							niceerror($errorText);
							$link = "javascript:history.back();";		
						}
						nicemessage('<a href="'. $link .'">Povratak.</a>');
					
					
					} //submitted the form
				
				} //subaction == add
				elseif ($subaction == 'edit')
				{
					if (!isUserAuthorOfRSS($id, $userid))
						return;
					
					//edit item
					if (isset($id) && is_int($id) && $id > 0)
					{
						
						if (!isset($_POST['submit']))
						{
							$entry = getRSS($id);
					
			?>
						 <h3>Uredi RSS feed</h3>
						
						<form action="<?=$linkPrefix . "&subaction=edit&id=$id" ?>" method="post" enctype="application/x-www-form-urlencoded" name="editForm" id="editForm">
						<div id="formDiv">
							Polja sa * su obavezna. <br />
							
                            <div class="row">
								<span class="label">Naziv *</span>
								<span class="formw"><input name="naziv" type="text" id="naziv" size="70" value="<?php echo stripslashes(htmlentities($entry['naziv'], ENT_QUOTES))?>" /></span> 
							</div>
	
							<div class="row">
								<span class="label">URL *</span>
								<span class="formw"><input name="url" type="text" id="url" size="70" value="<?php echo stripslashes(htmlentities($entry['url'], ENT_QUOTES))?>" /></span> 
							</div>
							<div class="row">
								<span class="label">Opis</span>
								<span class="formw"><textarea name="opis" cols="60" rows="15" wrap="physical" id="opis"><?php echo stripslashes(htmlentities($entry['opis'], ENT_QUOTES))?></textarea></span>
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
							$errorText = formProcess_rss('edit');
							if($errorText == '')
							{
								nicemessage('Uspjesno ste uredili RSS feed.');
								zamgerlog("korisnik u$userid uspjesno uredio rss feed na projektu ID=$projekat na predmetu p$predmet", 2);				
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
					
				} //subaction == edit
				elseif ($subaction == 'del')
				{
					if (!isUserAuthorOfRSS($id, $userid))
						return;

					//delete item
					if (isset($id) && is_int($id) && $id > 0)
					{
						if (!isset($_GET['c']))
						{
							echo "Da li ste sigurni da zelite obrisati ovaj RSS feed?<br />";	
							echo '<a href="' . $linkPrefix . '&subaction=del&id=' . $id . '&c=true">Da</a> | <a href="' . $linkPrefix . '">Odustani</a>';			
						}
						else
						{
							if ($_GET['c'] == 'true')
							{
								//delete the record
								if (deleteRSS($id))
								{
									nicemessage('Uspjesno ste obrisali RSS feed.');	
									zamgerlog("korisnik u$userid uspjesno obrisao rss feed na projektu ID=$projekat na predmetu p$predmet", 2);				
									$link = $linkPrefix;		
								}
								else
								{
									niceerror('Doslo je do greske prilikom brisanja RSS feeda. Molimo kontaktirajte administratora.');
									$link = "javascript:history.back();";	
								}
								nicemessage('<a href="'. $link .'">Povratak.</a>');
							}
							
						} //else isset c get parameter
								
						
					} //id is okay		
				
				} //subaction == del
	
			} //subaction set
			
			
		} //section == rss
		elseif ($section == 'bl')
		{
			//links management
			$linkPrefix .='&section=bl';
    ?>
<h2>Članci</h2>
 <div class="links clearfix" id="bl">
    <ul>
        <li><a href="<?php echo $linkPrefix?>">Lista članaka</a></li>
        <li><a href="<?php echo $linkPrefix . "&subaction=add"?>">Novi članak</a></li>
    </ul>   
</div>	
    <?php
			if (!isset($subaction))
			{
				
				$rowsPerPage = 20;
				$pageNum = 1;
				if(isset($_GET['page']))
				{
					$pageNum = $_GET['page'];
				}
				// counting the offset
				$offset = ($pageNum - 1) * $rowsPerPage;
				
				$articles = fetchArticlesForProject($project[id], $offset, $rowsPerPage);
				foreach($articles as $article)
				{
	?>
    
   <div class="article_summary clearfix">
   	<?php
		if (!empty($article[slika]))
		{
	?>
    	<div class="imgCont">
    		<img src="<?="index.php?sta=common/articleImageDownload&projekat=$projekat&predmet=$predmet&u=$article[osoba]&i=$article[slika]"?>" />
        </div>
	<?php
		}
	?>
    	<div class="contentCont" <?php if (empty($article[slika])) echo 'style="margin-left: 0;"' ?>>
            <h1>
                <a href="<?=$linkPrefix . "&subaction=view&id=$article[id]" ?>" 
                title="<?=stripslashes(htmlentities($article['naslov'], ENT_QUOTES, 'UTF-8')) ?>"><?=filtered_output_string($article['naslov']) ?>
                </a>
            </h1>
            <div class="details">
        <?php
			$author = getAuthorOfArticle($article[id]);
		?>
                Autor: <?=filtered_output_string($author[ime] . ' ' . $author[prezime]) ?><br />
                Datum: <?=date('d.m.Y', strtotime($article[vrijeme])) ?>
            </div><!--details-->
   <?php
   		if (isUserAuthorOfArticle($article[id], $userid) == true)
		{
	?>	
            <div class="buttons">
                <a href="<?= $linkPrefix . "&subaction=edit&id=$article[id]" ?>" title="Uredi ovaj članak">Uredi</a> | 
                <a href="<?= $linkPrefix . "&subaction=del&id=$article[id]" ?>" title="Briši ovaj članak">Briši</a>
            </div><!--buttons-->	
	<?php	
		}
   ?>

<div class="text">
                                <?php
                                $len = strlen($article[tekst]);
                        
                                if (!empty($article[slika]))
                                    $maxLen = 400;	
                                else
                                    $maxLen = 800;	
                                echo filtered_output_string(substr($article['tekst'], 0, $maxLen-1));
                                if ($len>$maxLen) 
                                    echo '...';
                                ?>
            </div><!--text-->
        </div><!--contentCont-->
   </div><!--article_summary--> 
    
    <?php
				} //foreach article	
				$numrows = getCountArticlesForProject($project[id]);
							
				$maxPage = ceil($numrows/$rowsPerPage);
				$self = $linkPrefix;
				
				if ($maxPage > 0)
				{
					echo "<span class=\"newsPages\">";
					if ($pageNum > 1)
					{
						$page = $pageNum - 1;
						$prev = " <a href=\"$self&page=$page\">[Prethodna]</a> ";
						
						$first = " <a href=\"$self&page=1\">[Prva]</a> ";
					} 
					
					if ($pageNum < $maxPage)
					{
						$page = $pageNum + 1;
						$next = " <a href=\"$self&page=$page\">[Sljedeća]</a> ";
						
						$last = " <a href=\"$self&page=$maxPage\">[Zadnja]</a> ";
					} 
					
					echo $first . $prev . " Strana <strong>$pageNum</strong> od ukupno <strong>$maxPage</strong> " . $next . $last;
					echo "</span>"; //newsPages span	
				}
							
			} //subactin not set
			else
			{
				if ($subaction == 'view')
				{
					$article = getArticle($id);
					if (empty($article))
					{
						zamgerlog("korisnik u$userid pokusava pregledati clanak sa losim ID=$id koji nije pronadjen u bazi podataka na projektu ID=$projekat na predmetu p$predmet", 3);				
						return;	
					}			
		?>
		
	   <div class="article_full clearfix">
			<div class="contentCont clearfix">
				<h1>
					<a href="<?=$linkPrefix . "?subaction=view&id=$article[id]" ?>" 
					title="<?=stripslashes(htmlentities($article['naslov'], ENT_QUOTES, 'UTF-8')) ?>"><?=filtered_output_string($article['naslov']) ?>
					</a>
				</h1>
				<div class="details">
			<?php
				$author = getAuthorOfArticle($article[id]);
			?>
					Autor: <?=filtered_output_string($author[ime] . ' ' . $author[prezime]) ?><br />
					Datum: <?=date('d.m.Y', strtotime($article[vrijeme])) ?>
				</div><!--details-->
	   <?php
					if (isUserAuthorOfArticle($article[id], $userid) == true)
					{
		?>	
				<div class="buttons">
					<a href="<?= $linkPrefix . "&subaction=edit&id=$article[id]" ?>" title="Uredi ovaj članak">Uredi</a> | 
					<a href="<?= $linkPrefix . "&subaction=del&id=$article[id]" ?>" title="Briši ovaj članak">Briši</a>
				</div><!--buttons-->	
		<?php	
					}
	   ?>
		<?php
					if (!empty($article[slika]))
					{
		?>
			<div class="imgCont"><img src="<?="index.php?sta=common/articleImageDownload&projekat=$projekat&predmet=$predmet&u=$article[osoba]&i=$article[slika]"?>" /></div>
	  <?php
					}
		?>
				<div class="text"><?=filtered_output_string($article['tekst']) ?></div><!--text-->
			</div><!--contentCont-->
	   </div><!--article_full--> 
       <a id="backLink" href="<?=$linkPrefix?>">Povratak na listu članaka</a>

		
		<?php
					
				
				} //subaction == view
				elseif ($subaction == 'add')
				{
		
					if (!isset($_POST['submit']))
					{
		
	?>	
    		
				 <h3>Novi članak</h3>
				
                <form action="<?=$linkPrefix . "&subaction=add" ?>" method="post" enctype="multipart/form-data" name="addForm" id="addForm">
                <div id="formDiv">
                	Polja sa * su obavezna. <br />
                
                	<div class="row">
                        <span class="label">Naslov *</span>
                        <span class="formw"><input name="naslov" type="text" id="naslov" size="70" /></span> 
                  	</div>
                    <div class="row">
                        <span class="label">Tekst</span>
                        <span class="formw"><textarea name="tekst" cols="60" rows="15" wrap="physical" id="tekst"></textarea></span>
                   	</div> 
                   
                    <div class="row">
                        <span class="label">Slika</span>
                        <span class="formw">
                            <input name="image" type="file" id="image" size="60" />
                        </span><br /><br />
                        Dozvoljeni tipovi slike: jpg, jpeg, gif, png <br />
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
						$errorText = formProcess_bl('add');
						if($errorText == '')
						{
							nicemessage('Novi članak uspješno dodan.');
							zamgerlog("korisnik u$userid uspjesno dodao novi clanak na projektu ID=$projekat na predmetu p$predmet", 2);				
							$link = $linkPrefix;			
						}
						else
						{	
							niceerror($errorText);
							$link = "javascript:history.back();";		
						}
						nicemessage('<a href="'. $link .'">Povratak.</a>');
					
					
					} //submitted the form
	
				} //subaction == add
				elseif ($subaction == 'edit')
				{
					if (!isUserAuthorOfArticle($id, $userid))
						return;

					
					//edit item
					if (isset($id) && is_int($id) && $id > 0)
					{
						if (!isset($_POST['submit']))
						{
							$entry = getArticle($id);
				
	?>
					 <h3>Uredi članak</h3>
				
                    <form action="<?=$linkPrefix . "&subaction=edit&id=$id" ?>" method="post" enctype="multipart/form-data" name="editForm" id="editForm">
                    <div id="formDiv">
                        Polja sa * su obavezna. <br />
                    
                        <div class="row">
                            <span class="label">Naslov *</span>
                            <span class="formw"><input name="naslov" type="text" id="naslov" size="70" value="<?php echo stripslashes(htmlentities($entry['naslov'], ENT_QUOTES))?>" /></span> 
                        </div>
                        <div class="row">
                            <span class="label">Tekst</span>
                            <span class="formw"><textarea name="tekst" cols="60" rows="15" wrap="physical" id="tekst"><?php echo stripslashes(htmlentities($entry['tekst'], ENT_QUOTES)) ?></textarea></span>
                        </div> 

	<?php 					if ($entry['slika'] != '')
		  					{
							//if the image exists, display it
				  ?>
                       <div class="row">
                            <span class="label">Trenutna slika</span>
                            <span class="formw"><img src="<?="index.php?sta=common/articleImageDownload&projekat=$projekat&predmet=$predmet&u=$entry[osoba]&i=$entry[slika]"?>" />
                            </span>
                       </div> 
                       
                       <div class="row">
                            <span class="label">Briši sliku</span>
                            <span class="formw"><input name="delete" type="checkbox" id="delete" value="delete" /></span>
                       </div> 
                   
	<?php
		  					} //if image is present
				  
	?>
                        <div class="row">
                          <span class="label"><?php 
                          if($entry['slika'] != '') echo "ILI: Zamijeni sliku"; else echo "Slika";?></span>
                            <span class="formw">
                                <input name="image" type="file" id="image" size="50" />
                            </span><br /><br />
                        	Dozvoljeni tipovi slike: jpg, jpeg, gif, png <br />
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
							$errorText = formProcess_bl('edit');
							if($errorText == '')
							{
								nicemessage('Uspješno ste uredili članak.');
								zamgerlog("korisnik u$userid uspjesno uredio clanak na projektu ID=$projekat na predmetu p$predmet", 2);				
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
				
				} //subaction == edit
				elseif ($subaction == 'del')
				{
					//delete item
					if (isset($id) && is_int($id) && $id > 0)
					{
						if (isUserAuthorOfArticle($id, $userid) == false)
							return;
						
						if (!isset($_GET['c']))
						{
							echo "Da li ste sigurni da zelite obrisati ovaj članak? <br />";	
							echo '<a href="' . $linkPrefix .'&amp;subaction=del&amp;id=' . $id . '&amp;c=true">Da</a> | <a href="' . $linkPrefix . '">Odustani</a>';			
						}
						else
						{
							if ($_GET['c'] == 'true')
							{
								//delete the record
								if (deleteArticle($id))
								{
									nicemessage('Uspjesno ste obrisali članak.');	
									zamgerlog("korisnik u$userid uspjesno obrisao clanak na projektu ID=$projekat na predmetu p$predmet", 2);				
									$link = $linkPrefix;		
								}
								else
								{
									niceerror('Došlo je do greske prilikom brisanja članka. Molimo kontaktirajte administratora.');
									$link = "javascript:history.back();";	
								}
								nicemessage('<a href="'. $link .'">Povratak.</a>');
							}
							
						 } //else isset c get parameter
								
						
					  } //id is okay		
				
				 } //subaction == del
		
			} //subaction set
				
		} //section == bl (blackboard)
		elseif ($section == 'file')
		{
			//files management
			$linkPrefix .='&section=file';

	?>
<h2>Fajlovi</h2>
 <div class="links clearfix" id="rss">
    <ul>
        <li><a href="<?php echo $linkPrefix?>">Lista fajlova</a></li>
        <li><a href="<?php echo $linkPrefix . "&subaction=add"?>">Novi fajl</a></li>
    </ul>   
</div>	

    <?php	
			if (!isset($subaction))
			{
				$rowsPerPage = 20;
				$pageNum = 1;
				if(isset($_GET['page']))
				{
					$pageNum = $_GET['page'];
				}
				// counting the offset
				$offset = ($pageNum - 1) * $rowsPerPage;			
				
				//display files for this project, with links to edit and delete
				$files = fetchFilesForProjectAllRevisions($project[id], $offset, $rowsPerPage);

				foreach ($files as $file)
				{
					$lastRevisionId = count($file)-1;
					
					if (isUserAuthorOfFile($file[$lastRevisionId][id], $userid))
					{
	?>
<div class="links clearfix" id="file">
    <ul>
        <li><a href="<?php echo $linkPrefix . "&subaction=edit&id=" . $file[$lastRevisionId]['id'] ?>">Uredi</a></li>
        <li><a href="<?php echo $linkPrefix . "&subaction=del&id=" . $file[$lastRevisionId]['id']?>">Briši</a></li>
    </ul>   
</div>	
	<?php
					} //if user is author of this item
	?>
    <div class="file_leading<?php if (count($file) > 1) echo ' link' ?>">
    <?php if (count($file) > 1)
	{
	?>
		<a href="#" onclick="toggleFileRevisions('file_<?=$file[0][id] ?>_revisions')"> + </a>
   	<?php
    }
    ?>    
    	<a href="<?='index.php?sta=common/fileDownload' . "&predmet=$predmet&projekat=$projekat&id=" . $file[0][id] ?>" target="_blank"><?=filtered_output_string($file[0][filename]) ?></a>
    </div><!--file_leading-->
    <?php
		if (count($file) > 1)
		{
	?>
    <div class="file_revisions" id="file_<?=$file[0]['id'] ?>_revisions" style="display: none;">
	<?php
					for ($i = 1; $i < count($file); $i++)
					{	
						$revision = $file[$i];
	?>
        <div class="file_revision">
        	<a href="<?='index.php?sta=common/fileDownload' . "&predmet=$predmet&projekat=$projekat&id=" . $revision[id] ?>" target="_blank">
            	<?='v' . $revision[revizija] ?>&nbsp;&nbsp;
				<?=filtered_output_string($revision[filename]) ?>
            </a>
        </div><!--file_revision-->	
    <?php					
					} //foreach revision
	?>
    </div><!--file_revisions-->
    <?php
		} //if count files > 1
	?>
		
	<?php
				} //foreach file
				$numrows = getCountFilesForProjectWithoutRevisions($project[id]);
							
				$maxPage = ceil($numrows/$rowsPerPage);
				$self = $linkPrefix;
			
				if ($maxPage > 0)
				{
					echo "<span class=\"newsPages\">";
					if ($pageNum > 1)
					{
						$page = $pageNum - 1;
						$prev = " <a href=\"$self&page=$page\">[Prethodna]</a> ";
						
						$first = " <a href=\"$self&page=1\">[Prva]</a> ";
					} 
					
					if ($pageNum < $maxPage)
					{
						$page = $pageNum + 1;
						$next = " <a href=\"$self&page=$page\">[Sljedeća]</a> ";
						
						$last = " <a href=\"$self&page=$maxPage\">[Zadnja]</a> ";
					} 
					
					echo $first . $prev . " Strana <strong>$pageNum</strong> od ukupno <strong>$maxPage</strong> " . $next . $last;
					echo "</span>"; //newsPages span
				}	
				
	?>
    <script type="text/javascript">
		function toggleFileRevisions(divID)
		{
			wrap = document.getElementById(divID);
			if (wrap.style.display == 'block')
				wrap.style.display = 'none';
			else
				wrap.style.display = 'block';		
		}
	
	</script>
    <?php		
			} //subaction not set
			else
			{
				if ($subaction == 'add')
				{
					
					if (!isset($_POST['submit']))
					{
				
	?>
						 <h3>Novi fajl</h3>
						
						<form action="<?=$linkPrefix . "&subaction=add" ?>" method="post" enctype="multipart/form-data" name="addForm" id="addForm">
						<div id="formDiv">
							Polja sa * su obavezna. <br />
							<b>Limit za upload je 20MB.</b> <br />
                            <div class="row">
                                <span class="label">Fajl *</span>
                                <span class="formw">
                                    <input name="filename" type="file" id="filename" size="60" />
                                    <input type="hidden" name="MAX_FILE_SIZE" value="20971520">
                                </span>
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
						$errorText = formProcess_file('add');
						if($errorText == '')
						{
							nicemessage('Novi fajl uspjesno dodan.');
							zamgerlog("korisnik u$userid uspjesno dodao novi fajl na projektu ID=$projekat na predmetu p$predmet", 2);				
							$link = $linkPrefix;			
						}
						else
						{	
							niceerror($errorText);
							$link = "javascript:history.back();";		
						}
						nicemessage('<a href="'. $link .'">Povratak.</a>');
					
					
					} //submitted the form
				
				} //subaction == add
				elseif ($subaction == 'edit')
				{
					if (!isUserAuthorOfFile($id, $userid))
						return;
					if (!isThisFileFirstRevision($id))
						return;
					
					//edit item
					if (isset($id) && is_int($id) && $id > 0)
					{
						
						if (!isset($_POST['submit']))
						{
							$entry = getFileFirstRevision($id);
							$lastRevisionEntry = getFileLastRevision($id);
			?>
						 <h3>Uredi fajl</h3>
						
						<form action="<?=$linkPrefix . "&subaction=edit&id=$id" ?>" method="post" enctype="multipart/form-data" name="editForm" id="editForm">
						<div id="formDiv">
							Polja sa * su obavezna. <br />
                            <b>Limit za upload je 20MB.</b> <br />							
                           <div class="row">
                                <span class="label">Trenutni fajl</span>
                                <span class="formw"><a href="<?='index.php?sta=common/fileDownload' . "&predmet=$predmet&projekat=$projekat&id=" . $lastRevisionEntry[id]?>" target="_blank">
									<?=filtered_output_string($lastRevisionEntry[filename]) ?>
                                </a>
                                </span>
                           </div> 

                            <div class="row">
                              <span class="label">Zamijeni fajl</span>
                                <span class="formw">
                                    <input name="filename" type="file" id="filename" size="50" />
                                    <input type="hidden" name="MAX_FILE_SIZE" value="20971520">
                                </span>
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
							$errorText = formProcess_file('edit');
							if($errorText == '')
							{
								nicemessage('Uspjesno ste uredili fajl.');
								zamgerlog("korisnik u$userid uspjesno uredio fajl na projektu ID=$projekat na predmetu p$predmet", 2);				
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
					
				} //subaction == edit
				elseif ($subaction == 'del')
				{
					if (!isUserAuthorOfFile($id, $userid))
						return;
						
					if (!isThisFileFirstRevision($id))
						return;

					//delete item
					if (isset($id) && is_int($id) && $id > 0)
					{
						if (!isset($_GET['c']))
						{
							echo "Da li ste sigurni da zelite obrisati ovaj fajl? Obrisacete sve revizije fajla sa servera.<br />";	
							echo '<a href="' . $linkPrefix . '&subaction=del&id=' . $id . '&c=true">Da</a> | <a href="' . $linkPrefix . '">Odustani</a>';			
						}
						else
						{
							if ($_GET['c'] == 'true')
							{
								//delete the record
								if (deleteFile($id))
								{
									nicemessage('Uspjesno ste obrisali fajl.');	
									zamgerlog("korisnik u$userid uspjesno obrisao fajl na projektu ID=$projekat na predmetu p$predmet", 2);				
									$link = $linkPrefix;		
								}
								else
								{
									niceerror('Doslo je do greske prilikom brisanja fajla. Molimo kontaktirajte administratora.');
									$link = "javascript:history.back();";	
								}
								nicemessage('<a href="'. $link .'">Povratak.</a>');
							}
							
						} //else isset c get parameter
								
						
					} //id is okay		
				
				} //subaction == del
	
			} //subaction set
			
		} //section == file
		elseif ($section == 'bb')
		{
			//links management
			$linkPrefix .='&section=bb';
    ?>
<h2>Grupa za diskusiju</h2>
 <div class="links clearfix" id="bl">
    <ul>
        <li><a href="<?php echo $linkPrefix?>">Lista tema</a></li>
        <li><a href="<?php echo $linkPrefix . "&subaction=add"?>">Nova tema</a></li>
    </ul>   
</div>	
    <?php
			if (!isset($subaction))
			{
				
				$rowsPerPage = 20;
				$pageNum = 1;
				if(isset($_GET['page']))
				{
					$pageNum = $_GET['page'];
				}
				// counting the offset
				$offset = ($pageNum - 1) * $rowsPerPage;
				
				$threads = fetchThreadsForProject($project[id], $offset, $rowsPerPage);
				$numrows = getCountThreadsForProject($project[id]);

	?>
<div id="threadList">
	<div class="threadRow caption clearfix">
        <div class="threadInfo">
        	<div class="views">Pregleda</div><!--views-->
        	<div class="lastReply">Zadnji odgovor</div><!--lastReply-->
            <div class="replies">Odgovora</div><!--replies-->
        </div><!--threadInfo-->
    	<div class="title">Teme (<?=$numrows ?>)</div><!--title-->		
    </div><!--threadRow caption-->
    <?php
				foreach($threads as $key => $thread)
				{
	?>
	<div class="threadRow clearfix<?php if  ($key % 2) echo ' pattern'?>">
        <div class="threadInfo">
        	<div class="views"><?=intval($thread[pregleda]) ?></div><!--views-->
        	<div class="lastReply"><?=date('d.m.Y H:i:s', mysql2time($thread[zadnji_post][vrijeme])) ?><br /><?=filtered_output_string($thread[zadnji_post][osoba][prezime] . ' ' . $thread[zadnji_post][osoba][ime]) ?></div><!--lastReply-->
            <div class="replies"><?=intval($thread[broj_odgovora]) ?></div><!--replies-->
        </div><!--threadInfo-->
    	<div class="title"><a href="<?=$linkPrefix . "&subaction=view&tid=$thread[id]" ?>" title="<?php echo stripslashes(htmlentities($thread['naslov'], ENT_QUOTES)) ?>"><?=filtered_output_string($thread[naslov]) ?></a></div><!--title-->
        <div class="author"><?=filtered_output_string($thread[prvi_post][osoba][prezime] . ' ' . $thread[prvi_post][osoba][ime]) ?></div><!--author-->		
    </div><!--threadRow caption-->
    <?php
				} //foreach thread
	?>
</div><!--threadList-->
    <?php
							
				$maxPage = ceil($numrows/$rowsPerPage);
				$self = $linkPrefix;
				
				if ($maxPage > 0)
				{
					echo "<span class=\"newsPages\">";
					if ($pageNum > 1)
					{
						$page = $pageNum - 1;
						$prev = " <a href=\"$self&page=$page\">[Prethodna]</a> ";
						
						$first = " <a href=\"$self&page=1\">[Prva]</a> ";
					} 
					
					if ($pageNum < $maxPage)
					{
						$page = $pageNum + 1;
						$next = " <a href=\"$self&page=$page\">[Sljedeća]</a> ";
						
						$last = " <a href=\"$self&page=$maxPage\">[Zadnja]</a> ";
					} 
					
					echo $first . $prev . " Strana <strong>$pageNum</strong> od ukupno <strong>$maxPage</strong> " . $next . $last;
					echo "</span>"; //newsPages span	
				}
							
			} //subactin not set
			else
			{
				if ($subaction == 'view')
				{
					$tid = intval($_GET[tid]);
					$thread = getThreadAndPosts($tid);
					if (empty($thread))
					{
						zamgerlog("korisnik u$userid pokusava pregledati BB thread sa losim ID=$id koji nije pronadjen u bazi podataka na projektu ID=$projekat na predmetu p$predmet", 3);				
						return;	
					}	
					incrementThreadViewCount($thread[id]);		
					
	?>
    <div id="fullThread">
    <?php
					foreach ($thread[posts] as $post)
					{
	?>				
		<div class="post">
        	<div id="post_<?=$post[id]?>_header" class="header clearfix" onclick="toggleShowPost('post_<?=$post[id] ?>')">
                <div class="buttons">
                	<a href="<?=$linkPrefix . "&subaction=add&tid=$post[tema]&id=$post[id]"?>" title="Odgovori na ovaj post">Odgovori</a>
    <?php
		if (isUserAuthorOfPost($post[id], $userid) == true)
		{
	?>
    				| <a href="<?=$linkPrefix . "&subaction=edit&tid=$post[tema]&id=$post[id]"?>" title="Uredi vlastiti post">Uredi</a>
    				| <a href="<?=$linkPrefix . "&subaction=del&tid=$post[tema]&id=$post[id]"?>" title="Obriši vlastiti post">Obriši</a>		
    <?php
		}
	
	?>
                </div>
                <div class="maininfo">
                	<div class="date"><?=date('d.m.Y H:i:s', mysql2time($post[vrijeme])) ?></div>
                    <div class="author"><?=filtered_output_string($post[osoba][prezime] . ' ' . $post[osoba][ime]) ?></div> - 
                    <div class="title"><?=filtered_output_string($post[naslov]) ?></div>
                </div>
            </div><!--header-->
            <div class="text" id="post_<?=$post[id] ?>_text"><?=filtered_output_string($post[tekst]) ?></div><!--text-->

        </div><!--post-->				
					
	<?php			
					} //foreach post
	?>
    
    
    </div><!--fullThread-->
        <script type="text/javascript">
		function toggleShowPost(divID)
		{
			header = document.getElementById(divID + '_header');
			text = document.getElementById(divID + '_text');
			if (text.style.display == 'block' || text.style.display == '')
			{
				text.style.display = 'none';
				header.style.backgroundColor = '#F5F5F5';
				header.style.color = 'black';
			}
			else
			{
				text.style.display = 'block';
				header.style.backgroundColor = '#EEEEEE';
			}	
				
		}
	
		</script>
	
    <?php
				
				
				
				} //subaction == view (thread)
				elseif ($subaction == 'add')
				{
		
					$threadID = intval($_GET['tid']);
					
					if ($threadID <=0)
						$thread = false;
					else
						$thread = true;
					
					if ($thread == true)
					{
						$postInfo = getPostInfoForThread($threadID, $id);
						if (empty($postInfo))
						{
							zamgerlog("korisnik u$userid pokusao napisati odgovor na post sa losim IDem koji nije int ili je <=0 na projektu ID=$projekat na predmetu p$predmet", 3);
							return;	
						}	
					}
					if (!isset($_POST['submit']))
					{
		
	?>	
    		
				 <h3><?php if ($thread == true) echo 'Novi odgovor'; else echo 'Nova tema'; ?></h3>
				
                <form action="<?php if ($thread == true) echo $linkPrefix . "&subaction=add&tid=$threadID"; else echo $linkPrefix . "&subaction=add" ?>" method="post" enctype="multipart/form-data" name="addForm" id="addForm">
                <div id="formDiv">
                	Polja sa * su obavezna. <br />
                
                	<div class="row">
                        <span class="label">Naslov *</span>
                        <span class="formw"><input name="naslov" type="text" id="naslov" size="70" <?php if ($thread == true) {?> value="RE: <?=stripslashes(htmlentities($postInfo['naslov'], ENT_QUOTES, 'UTF-8'))?>"<?php } ?>/></span> 
                  	</div>
                    <div class="row">
                        <span class="label">Tekst *</span>
                        <span class="formw"><textarea name="tekst" cols="60" rows="15" wrap="physical" id="tekst"></textarea></span>
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
						$errorText = formProcess_bb('add', $thread, $threadID);
						if($errorText == '')
						{
							if ($thread == true)
							{
								nicemessage('Novi odgovor uspješno dodan.');
								zamgerlog("korisnik u$userid uspjesno dodao novi ogvoro na diskusiju (ID=$threadID) na projektu ID=$projekat na predmetu p$predmet", 2);			
							}
							else
							{
								nicemessage('Nova tema uspješno dodana.');
								zamgerlog("korisnik u$userid uspjesno dodao novu temu diskusije na projektu ID=$projekat na predmetu p$predmet", 2);
							}
								
							if (!empty($_GET[tid]))				
								$link = $linkPrefix . "&subaction=view&tid=$_GET[tid]";	
							else
								$link = $linkPrefix;
						}
						else
						{	
							niceerror($errorText);
							$link = "javascript:history.back();";		
						}
						nicemessage('<a href="'. $link .'">Povratak.</a>');
					
					
					} //submitted the form
	
				} //subaction == addThread
				elseif ($subaction == 'edit')
				{
					//edit item
					if (isset($id) && is_int($id) && $id > 0)
					{
						if (isUserAuthorOfPost($id, $userid) == false)
						{
							zamgerlog("korisnik u$userid pokusava urediti vlastiti post  ID=$id kojem nije autor na projektu ID=$projekat na predmetu p$predmet", 3);
							return;
						}
						$threadID = intval($_GET[tid]);
						if ($threadID <=0)
						{
							zamgerlog("korisnik u$userid pokusava urediti post ID=$id iako je ID teme los jer nije int ili je <=0 na projektu ID=$projekat na predmetu p$predmet", 3);
							return;
						}
						
						
						if (!isset($_POST['submit']))
						{
							$entry = getPost($id);
							if (empty($entry))
							{
								zamgerlog("korisnik u$userid pokusava urediti vlastiti post koji ima los ID koji nije int ili je <=0 na projektu ID=$projekat na predmetu p$predmet", 3);
								return;
							}
	?>
					 <h3>Uredi post</h3>
				
                    <form action="<?=$linkPrefix . "&subaction=edit&tid=$threadID&id=$id" ?>" method="post" enctype="multipart/form-data" name="editForm" id="editForm">
                    <div id="formDiv">
                        Polja sa * su obavezna. <br />
                    
                        <div class="row">
                            <span class="label">Naslov *</span>
                            <span class="formw"><input name="naslov" type="text" id="naslov" size="70" value="<?php echo stripslashes(htmlentities($entry['naslov'], ENT_QUOTES))?>" /></span> 
                        </div>
                        <div class="row">
                            <span class="label">Tekst *</span>
                            <span class="formw"><textarea name="tekst" cols="60" rows="15" wrap="physical" id="tekst"><?php echo stripslashes(htmlentities($entry['tekst'], ENT_QUOTES)) ?></textarea></span>
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
							$errorText = formProcess_bb('edit', $thread, $threadID);
							if($errorText == '')
							{
								nicemessage('Uspješno ste uredili post.');
								zamgerlog("korisnik u$userid uspjesno uredio vlastiti BB post ID=$id na projektu ID=$projekat na predmetu p$predmet", 2);				
								$link = $linkPrefix . "&subaction=view&tid=$_GET[tid]";									
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
				
				} //subaction == edit
				elseif ($subaction == 'del')
				{
					//delete item
					if (isset($id) && is_int($id) && $id > 0)
					{
						if (isUserAuthorOfPost($id, $userid) == false)
						{
							zamgerlog("korisnik u$userid pokusava izbrisati post ID=$id iako nije autor na projektu ID=$projekat na predmetu p$predmet", 3);
							return;
						}
						$threadID = intval($_GET[tid]);
						if ($threadID<=0)
						{
							zamgerlog("korisnik u$userid pokusava izbrisati post ID=$id iako nije dobar ID teme koji nije int ili je <=0 na projektu ID=$projekat na predmetu p$predmet", 3);
							return;
						}
						
						if (!isset($_GET['c']))
						{
							echo "Da li ste sigurni da zelite obrisati ovaj post? <br />";	
							echo '<a href="' . $linkPrefix .'&amp;subaction=del&tid=' . $threadID .'&id=' . $id . '&amp;c=true">Da</a> | <a href="' . $linkPrefix . '">Odustani</a>';			
						}
						else
						{
							if ($_GET['c'] == 'true')
							{
								//delete the record
								if (deletePost($id))
								{
									nicemessage('Uspjesno ste obrisali post.');	
									zamgerlog("korisnik u$userid uspjesno obrisao post na projektu ID=$projekat na predmetu p$predmet", 2);				
									if (getCountPostsInThread($threadID) > 0)
										$link = $linkPrefix . "&subaction=view&tid=$_GET[tid]";	
									else
										$link = $linkPrefix;
								}
								else
								{
									niceerror('Došlo je do greske prilikom brisanja posta. Molimo kontaktirajte administratora.');
									$link = "javascript:history.back();";	
								}
								nicemessage('<a href="'. $link .'">Povratak.</a>');
							}
							
						 } //else isset c get parameter
								
						
					  } //id is okay		
				
				 } //subaction == del
		
			} //subaction set
				
		} //section == bb (forum)		
	
	} //else - section is set

} //function


function formProcess_links($option)
{
	$errorText = '';
	
	if (!in_array($option, array('add', 'edit') ) )
	{
		$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';
		return $errorText;		
	}
	
	$id = intval($_GET['id']);
	
	if ($option == 'edit' && $id <=0)
	{
		$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';
		zamgerlog("korisnik u$userid pokusao urediti link sa losim ID koji nije integer ili je <=0 na projektu ID=$projekat na predmetu p$predmet", 3);				

		return $errorText;		
	}
	
	//get variables
	$naziv 	= $_POST['naziv'];
	$url 	= $_POST['url'];
	$opis 	= $_POST['opis'];
	
	$projekat = intval($_GET['projekat']);
	$predmet = intval($_GET['predmet']);
	global $userid;
	
	
	
	
	if (empty($naziv) || empty($url))
	{
		$errorText = 'Unesite sva obavezna polja.';
		return $errorText;
	}
	
	$naziv = trim($naziv);
	$url = trim($url);
	$opis = trim($opis);
	
	$naziv = strip_tags($naziv);
	$url = strip_tags($url);
	$opis = strip_tags($opis);
	
	$data = array(
				'naziv' => $naziv, 
				'url' => $url, 
				'opis' => $opis, 
				'osoba' => $userid, 
				'projekat' => $projekat 
	);
	
	if ($option == 'add')
	{
		if (!insertLink($data))
		{
			$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';
			return $errorText;		
		}
	
	} //option == add
	else
	{
		if (!updateLink($data, $id))
		{
			$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';
			return $errorText;		
		}
	
	} //option == edit


	return $errorText;
	
}

function insertLink($data)
{

	//generate unique id value
	$id = generateIdFromTable('projekat_link');
	
	$query = sprintf("INSERT INTO projekat_link (id, naziv, url, opis, projekat, osoba) VALUES ('%d', '%s', '%s', '%s', '%d', '%d')", 
											$id, 
											my_escape($data['naziv']), 
											my_escape($data['url']), 
											my_escape($data['opis']), 
											intval($data['projekat']), 
											intval($data['osoba'])  
											
					);
	$result = myquery($query);	
	
	return ( $result == false ) ? false : true;
}

function updateLink($data, $id)
{
	$query = sprintf("UPDATE projekat_link SET naziv='%s', url='%s', opis='%s' WHERE id='%d' LIMIT 1", 
											my_escape($data['naziv']), 
											my_escape($data['url']), 
											my_escape($data['opis']),
											intval($id) 
											
					);
	$result = myquery($query);	

	return ( $result == false ) ? false : true;
}
function deleteLink($id)
{
	$query = sprintf("DELETE FROM projekat_link WHERE id='%d' LIMIT 1", 
					intval($id)
					);
	
	$result = myquery($query);
	
	return ( $result == false ) ? false : true;
}

function formProcess_rss($option)
{
	$errorText = '';
	
	if (!in_array($option, array('add', 'edit') ) )
	{
		$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';
		return $errorText;		
	}
	
	$id = intval($_GET['id']);
	
	if ($option == 'edit' && $id <=0)
	{
		$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';
		zamgerlog("korisnik u$userid pokusao urediti rss feed sa losim ID koji nije integer ili je <=0 na projektu ID=$projekat na predmetu p$predmet", 3);				
		return $errorText;		
	}
	
	//get variables
	$naziv 	= $_POST['naziv'];
	$url 	= $_POST['url'];
	$opis 	= $_POST['opis'];
	
	$projekat = intval($_GET['projekat']);
	$predmet = intval($_GET['predmet']);
	global $userid;
	
	
	
	
	if (empty($naziv) || empty($url))
	{
		$errorText = 'Unesite sva obavezna polja.';
		return $errorText;
	}
	
	$naziv = trim($naziv);
	$url = trim($url);
	$opis = trim($opis);
	
	$naziv = strip_tags($naziv);
	$url = strip_tags($url);
	$opis = strip_tags($opis);
	
	$data = array(
				'naziv' => $naziv, 
				'url' => $url, 
				'opis' => $opis, 
				'osoba' => $userid, 
				'projekat' => $projekat 
	);
	
	if ($option == 'add')
	{
		if (!insertRSS($data))
		{
			$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';
			return $errorText;		
		}
	
	} //option == add
	else
	{
		if (!updateRSS($data, $id))
		{
			$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';
			return $errorText;		
		}
	
	} //option == edit


	return $errorText;
	
}

function insertRSS($data)
{

	//generate unique id value
	$id = generateIdFromTable('projekat_rss');
	
	$query = sprintf("INSERT INTO projekat_rss (id, naziv, url, opis, projekat, osoba) VALUES ('%d', '%s', '%s', '%s', '%d', '%d')", 
											$id, 
											my_escape($data['naziv']), 
											my_escape($data['url']), 
											my_escape($data['opis']), 
											intval($data['projekat']), 
											intval($data['osoba'])  
											
					);
	$result = myquery($query);	
	
	return ( $result == false ) ? false : true;
}

function updateRSS($data, $id)
{
	$query = sprintf("UPDATE projekat_RSS SET naziv='%s', url='%s', opis='%s' WHERE id='%d' LIMIT 1", 
											my_escape($data['naziv']), 
											my_escape($data['url']), 
											my_escape($data['opis']),
											intval($id) 
											
					);
	$result = myquery($query);	

	return ( $result == false ) ? false : true;
}
function deleteRSS($id)
{
	$query = sprintf("DELETE FROM projekat_rss WHERE id='%d' LIMIT 1", 
					intval($id)
					);
	
	$result = myquery($query);
	
	return ( $result == false ) ? false : true;
}


function formProcess_bl($option)
{
	$errorText = '';
	
	if (!in_array($option, array('add', 'edit') ) )
	{
		$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';
		return $errorText;		
	}
	
	$id = intval($_GET['id']);
	
	if ($option == 'edit' && $id <=0)
	{
		$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';
		zamgerlog("korisnik u$userid pokusao urediti clanak sa losim ID koji nije integer ili je <=0 na projektu ID=$projekat na predmetu p$predmet", 3);				
		return $errorText;		
	}
	
	//get variables
	$naslov 	= $_POST['naslov'];
	$tekst 		= $_POST['tekst'];
	$slika 		= $_FILES['image'];
	
	$projekat = intval($_GET['projekat']);
	$predmet = intval($_GET['predmet']);
	global $userid;

	
	if (empty($naslov))
	{
		$errorText = 'Unesite sva obavezna polja.';
		return $errorText;
	}
	
	$naslov = trim($naslov);
	$tekst = trim($tekst);
	
	$naslov = strip_tags($naslov);
	$tekst = strip_tags($tekst);
	
	
	//process image
	if ($option == 'edit')
	{
		$entry = getArticle($id);
	}
	
	global $conf_files_path;
	$lokacijaclanaka ="$conf_files_path/projekti/clanci/$projekat/$userid/";
	
	if (!file_exists("$conf_files_path/projekti/clanci/$projekat")) 
	{
		mkdir ("$conf_files_path/projekti/clanci/$projekat",0777, true);
	}
	if (!file_exists($lokacijaclanaka)) 
	{
		mkdir ($lokacijaclanaka,0777, true);
	}


	if ($slika['error'] != 4)
	{

		//cannot delete original image and preplace it with the new image so check this also
		
		if (isset($_POST['delete']))
		{
			$errorText .= 'Selektujte ili brisanje slike, ili zamjena slike, ne oboje!';
			return $errorText;
		}
		
		//adding or replacing image - depends on the $option parameter(add, edit)
		
		if ($slika['error'] > 0)
		{
			if ($slika['error'] == 1 || $slika['error'] == 2)
				$errorText .= 'Pokušavate poslati fajl koji je veci od dozvoljene velicine. Probajte sa manjim fajlom.<br />';
			else
				$errorText .= 'Vaš fajl nije poslan korektno. Molimo pokušajte ponovo.<br />';
			return $errorText;		
		}
		else
		{
			//No error occured so far
			
			$uploadDir = $lokacijaclanaka;
			
			# Go to all lower case for consistency
			$imageName = strtolower($slika["name"]);
						
			$extension = preg_replace('/.+(\..*)$/', '$1', $imageName); 
			
			$safeExtensions = array(
									'.jpg',
									'.jpeg', 
									'.gif', 
									'.png'								
							  
			);  

			if (!in_array($extension, $safeExtensions)) 
			{
				 $errorText .= 'Format slike nije dozvoljen. <br />';
				 return $errorText;	
			}
			if (getimagesize($slika['tmp_name']) == false)
			{
				 $errorText .= 'Format slike nije dozvoljen. <br />';
				 return $errorText;		
			}
			
			//final file name
			if ($option == 'add')
			{
				$uniqueID = date('YmdHis', time());
				$uploadFile =  $uniqueID . "$userid" . $extension;	
			}
			else
			{
				if ($entry['slika'] == '')
				{
					$uniqueID = date('YmdHis', time());
					$uploadFile =  $uniqueID . "$userid" . $extension;	
				}	
				else
					$uploadFile = $entry['slika'];
				
			}
			
			
			if (move_uploaded_file($slika['tmp_name'], $uploadDir . $uploadFile))
			{
				//transfered a file to upload directory from temp dir
				//if edit option REPLACING the old image (overwrite)
				chmod($uploadDir . $uploadFile, 0777);	
			
			} 
			else
			{
				$errorText .= 'Desila se greška prilikom uploada slike. Molimo kontaktirajte administratora.<br />';
				return $errorText;			
			} //else
			
		} //else
	
	
	} //if ($_FILES['slika']['error'] != 4)
	
	if ($option == 'add')
	{		
		if ($slika['error'] != 4)
			$imageURL  = $uploadFile;
		else	
			$imageURL  = '';
			
	} //add option
	else	
	//edit option
	{			
		if ($entry['slika'] == '')
		{
			$imageURL = $uploadFile;				
		}	
		else
		{
			if (isset($_POST['delete']))
			{
				//delete image from server
				
				unlink($lokacijaclanaka . $entry['slika']);
				//reset image in the database
				$imageURL = '';
				
			}
			else
				$imageURL = $entry['slika'];
		}	
	}
	
	
	
	
	$data = array(
				'naslov' => $naslov, 
				'tekst' => $tekst, 
				'slika' => $imageURL, 
				'osoba' => $userid, 
				'projekat' => $projekat 
	);
	
	if ($option == 'add')
	{
		if (!insertArticle($data))
		{
			$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';
			return $errorText;		
		}
	
	} //option == add
	else
	{
		if (!updateArticle($data, $id))
		{
			$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';
			return $errorText;		
		}
	
	} //option == edit


	return $errorText;
	
}

function insertArticle($data)
{

	//generate unique id value
	$id = generateIdFromTable('bl_clanak');
	
	$query = sprintf("INSERT INTO bl_clanak (id, naslov, tekst, slika, projekat, osoba) VALUES ('%d', '%s', '%s', '%s', '%d', '%d')", 
											$id, 
											my_escape($data['naslov']), 
											my_escape($data['tekst']), 
											my_escape($data['slika']), 
											intval($data['projekat']), 
											intval($data['osoba'])  
											
					);
	$result = myquery($query);	
	
	return ( $result == false ) ? false : true;
}

function updateArticle($data, $id)
{
	$query = sprintf("UPDATE bl_clanak SET naslov='%s', tekst='%s', slika='%s' WHERE id='%d' LIMIT 1", 
											my_escape($data['naslov']), 
											my_escape($data['tekst']), 
											my_escape($data['slika']),
											intval($id) 
											
					);
	$result = myquery($query);	

	return ( $result == false ) ? false : true;
}
function deleteArticle($id)
{	
	global $conf_files_path;
	
	$entry = getArticle($id);
	$query = sprintf("DELETE FROM bl_clanak WHERE id='%d' LIMIT 1", 
					intval($id)
					);
	
	$result = myquery($query);
	
	if (mysql_affected_rows() == 0)
		return false;
	
	if ($entry[slika] != '')
	{
		$lokacijaclanaka ="$conf_files_path/projekti/clanci/" . $entry['projekat'] . '/' . $entry['osoba'] . '/';
		if (!unlink($lokacijaclanaka . $entry['slika']))
			return false;	
	}
	
	return true;
}

function formProcess_file($option)
{
	$errorText = '';
	
	//ini_set('post_max_size', '20M');
	//ini_set('upload_max_filesize', '20M');
	set_time_limit(0);
	
	if (!in_array($option, array('add', 'edit') ) )
	{
		$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';
		return $errorText;		
	}
	
	$id = intval($_GET['id']);
	
	if ($option == 'edit' && $id <=0)
	{
		$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';
		zamgerlog("korisnik u$userid pokusao urediti fajl sa losim ID koji nije integer ili je <=0 na projektu ID=$projekat na predmetu p$predmet", 3);				
		return $errorText;		
	}
	if ($option == 'edit' && isThisFileFirstRevision($id) == false)
	{
		//cannot get access to revisions other than the first one	
		$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';
		zamgerlog("korisnik u$userid pokusao urediti specificnu reviziju nekog fajla (ID=$id) iako nije dozvololjeno mijenjanje pojedinacnih revizija fajlova na projektu ID=$projekat na predmetu p$predmet", 3);				
		return $errorText;		
	}
	
	//process file
	if ($option == 'edit')
	{
		$entry = getFileFirstRevision($id);
		$lastRevisionEntry = getFileLastRevision($id);
	}
	
	//get variables
	$filename	= $_FILES['filename'];
	
	if ($option == 'edit')
	{
		$revizija = $lastRevisionEntry[revizija] + 1;
		$file = $entry['id'];
	}
	else
	{
		$revizija = 1;
		$file = '';	
	}

	$projekat = intval($_GET['projekat']);
	$predmet = intval($_GET['predmet']);
	global $userid;

	
	if ($filename['error'] == 4)
	{
		$errorText = 'Unesite sva obavezna polja.';
		return $errorText;
	}
		

	global $conf_files_path;
	$lokacijafajlova ="$conf_files_path/projekti/fajlovi/$projekat/$userid/";
	
	if (!file_exists("$conf_files_path/projekti/fajlovi/$projekat")) 
	{
		mkdir ("$conf_files_path/projekti/fajlovi/$projekat",0777, true);
	}
	if (!file_exists($lokacijafajlova)) 
	{
		mkdir ($lokacijafajlova,0777, true);
	}
	



	//adding or replacing file - depends on the $option parameter(add, edit)

	if ($filename['error'] > 0)
	{
		if ($filename['error'] == 1 || $filename['error'] == 2)
			$errorText .= 'Pokušavate poslati fajl koji je veci od dozvoljene velicine. Probajte sa manjim fajlom.<br />';
		else
			$errorText .= 'Vaš fajl nije poslan korektno. Molimo pokušajte ponovo.<br />';
		return $errorText;		
	}
	else
	{
		//No error occured so far
		//escape file name before upload
		if ($option == 'add')
			$Name = $filename['name'];
		else
			$Name = $entry[filename];

		$Name = trim($Name);					
		
		//make directory structure for this file
		//$uploadDir = $lokacijafajlova . "$Name" . "_revizije/v$revizija/";
		$uploadDir = $lokacijafajlova . "$Name/";
		
		if (!file_exists($uploadDir)) 
		{
			mkdir ($uploadDir,0777, true);
		}
		$uploadDir = $uploadDir . "v$revizija/";
		
		if (!file_exists($uploadDir)) 
		{
			mkdir ($uploadDir,0777, true);
		}


		//final file name
		if ($option == 'add')
			$uploadFile =  $Name;
		else
			$uploadFile = $entry['filename'];

		
		if (move_uploaded_file($filename['tmp_name'], $uploadDir . $uploadFile))
		{
			//transfered a file to upload directory from temp dir
			//if edit option REPLACING the old image (overwrite)
			chmod($uploadDir . $uploadFile, 0777);	
		} 
		else
		{
			
			$errorText .= 'Desila se greška prilikom uploada fajla. Molimo kontaktirajte administratora.<br />AA';
			return $errorText;			
		} //else
		
	} //else
	
	//diff
	$diff = '';
	$diffing = 0;

	if ($option == 'edit')
	{
		//diffing with textual files only
		$lastRevisionFile = $lokacijafajlova .'/' . $lastRevisionEntry['filename'] . '/v' . $lastRevisionEntry['revizija'] . '/' . $lastRevisionEntry['filename'];
		$newFile          = $uploadDir . $uploadFile;
		
		$extension = preg_replace('/.+(\..*)$/', '$1', $lastRevisionEntry['filename']);
		$textExtensions = array(
								'.txt'
								);  

		if (in_array($extension, $textExtensions)) 
			$diffing = 1;
		
		if ($diffing == 1)
		{
			//hope this will finish nicely...
			$diff = `/usr/bin/diff -u $lastRevisionFile $newFile`;
		}	
	
		 
	} //option == edit

	
	$data = array(
				'filename' => $uploadFile,
				'revizija' => $revizija, 
				'file' => $file, 
				'osoba' => $userid, 
				'projekat' => $projekat, 
				'diffing' => $diffing, 
				'diff' => $diff
	);
	
	if (!insertFile($data))
	{
		$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';
		return $errorText;		
	}

	return $errorText;
	
}

function insertFile($data)
{

	//generate unique id value
	$id = generateIdFromTable('projekat_file');
	
	$query = sprintf("INSERT INTO projekat_file (id, filename, revizija, osoba, projekat, file) VALUES ('%d', '%s', '%d', '%d', '%d', '%d')", 
											$id, 
											my_escape($data['filename']), 
											intval($data['revizija']), 
											intval($data['osoba']), 
											intval($data['projekat']), 
											intval($data['file'])  						
					);
	$result = myquery($query);	
	
	if ($result == false || mysql_affected_rows() == 0)
		return false;
	
	//insert diff
	if ($data['diffing'] == 1)
	{
		$query = sprintf("INSERT INTO projekat_file_diff (file, diff) VALUES ('%d', '%s')", 
											$id, 
											my_escape($data['diff'])
		);
		$result = myquery($query);
		
		if ($result == false || mysql_affected_rows() == 0)
			return false;			
	}
	
	return true;	
}

function deleteFile($id)
{
	global $conf_files_path;
	
	$list = fetchAllRevisionsForFile($id);
	
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
}


function formProcess_bb($option, $thread, $threadID)
{
	$errorText = '';
	
	if (!in_array($option, array('add', 'edit') ) )
	{
		$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';
		return $errorText;		
	}
	
	$id = intval($_GET['id']);
	
	if ($option == 'edit' && $id <=0)
	{
		$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';
		zamgerlog("korisnik u$userid pokusao urediti BB post sa losim ID koji nije integer ili je <=0 na projektu ID=$projekat na predmetu p$predmet", 3);				
		return $errorText;		
	}

	if ($thread == true && $threadID <=0)
	{
		$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';
		zamgerlog("korisnik u$userid pokusao urediti BB post sa losim ID teme koji nije integer ili je <=0 na projektu ID=$projekat na predmetu p$predmet", 3);				
		return $errorText;			
	}
	
	
	//get variables
	$naslov 	= $_POST['naslov'];
	$tekst 		= $_POST['tekst'];
	
	$projekat = intval($_GET['projekat']);
	$predmet = intval($_GET['predmet']);
	global $userid;

	
	if (empty($naslov) || empty($tekst))
	{
		$errorText = 'Unesite sva obavezna polja.';
		return $errorText;
	}
	
	$naslov = trim($naslov);
	$tekst = trim($tekst);
	
	$naslov = strip_tags($naslov);
	$tekst = strip_tags($tekst);
	

	if ($option == 'edit')
	{
		$entry = getPost($id);
	}
	
	
	
	$data = array(
				'naslov' => $naslov, 
				'tekst' => $tekst, 
				'osoba' => $userid, 
				'projekat' => $projekat, 
				'threadID' => $threadID //only used in insertReply if thread == true		
	);
	
	if ($option == 'add')
	{
		if ($thread == false)
		{
			//new thread inserting
			if (!insertThread($data))
			{
				$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';
				return $errorText;		
			}
		} //thread false
		else
		{
			//inserting post in thread
			if (!insertReplyForThread($threadID, $data))
			{
				$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';
				return $errorText;		
			}
		
		}
	
	} //option == add
	else
	{
		if (!updatePost($data, $id))
		{
			$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';
			return $errorText;		
		}
	
	} //option == edit


	return $errorText;
	
}

function insertThread($data)
{
	//generate unique id value
	$thread_id = generateIdFromTable('bb_tema');
	
	$query = sprintf("INSERT INTO bb_tema (id, osoba, projekat) VALUES('%d', '%d', '%d')", 
											$thread_id,
											intval($data['osoba']), 
											intval($data['projekat'])											
	
	);
	$result = myquery($query);	
	
	if ($result == false || mysql_affected_rows() == 0)
		return false;
	$post_id = insertReplyForThread($thread_id, $data);
	if ($post_id == false)
		return false;
	

	//update some data in newly created thread
	$query = sprintf("UPDATE bb_tema SET prvi_post='%d', zadnji_post='%d' WHERE id='%d' LIMIT 1", 
											$post_id, 
											$post_id, 
											$thread_id
	);
	
	$result = myquery($query);
	
	if ($result == false || mysql_affected_rows() == 0)
		return false;
		
	return true;
}

function insertReplyForThread($thread, $data)
{
	
	//insert post for this thread, this is the first post
	$post_id = generateIdFromTable('bb_post');
	$query = sprintf("INSERT INTO bb_post (id, naslov, osoba, tema) VALUES('%d', '%s', '%d', '%d')", 
											$post_id, 
											my_escape($data['naslov']), 
											intval($data['osoba']), 
											$thread	
	);
	$result = myquery($query);
	if ($result == false || mysql_affected_rows() == 0)
		return false;
	
	//insert text for this post
	$query = sprintf("REPLACE INTO bb_post_text SET post='%d', tekst='%s'", 
											$post_id, 
											my_escape($data['tekst'])	
	);
	
	$result = myquery($query);
	if ($result == false || mysql_affected_rows() == 0)
		return false;
	
	//update some data in thread
	$query = sprintf("UPDATE bb_tema SET zadnji_post='%d' WHERE id='%d' LIMIT 1", 
											$post_id, 
											$thread
	);
	
	$result = myquery($query);
	
	if ($result == false || mysql_affected_rows() == 0)
		return false;
	
	return $post_id;	
}	

function updatePost($data, $id)
{
	$query = sprintf("UPDATE bb_post SET naslov='%s' WHERE id='%d' LIMIT 1", 
											my_escape($data['naslov']), 
											intval($id) 
											
					);
	$result = myquery($query);
	
	if ($result == false)
		return false;	
	
	$query = sprintf("UPDATE bb_post_text SET tekst='%s' WHERE post='%d' LIMIT 1", 
											my_escape($data['tekst']), 
											intval($id) 
											
					);
	$result = myquery($query);

	return ( $result == false ) ? false : true;
}

function deletePost($id)
{	
	$query = sprintf("DELETE FROM bb_post WHERE id='%d' LIMIT 1", 
					intval($id)
					);
	
	$result = myquery($query);
	
	if (mysql_affected_rows() == 0)
		return false;
		
	$query = sprintf("DELETE FROM bb_post_text WHERE post='%d' LIMIT 1", 
					intval($id)
					);
	
	$result = myquery($query);
	
	if (mysql_affected_rows() == 0)
		return false;
	
	//if first post, delete thread
	
	$result = myquery("SELECT prvi_post, id FROM bb_tema WHERE prvi_post='$id' LIMIT 1");
	
	if (mysql_num_rows($result) > 0)
	{
		//delete evetyhing
		$row = mysql_fetch_assoc($result);
		$thread = $row[id];
		
		$result = myquery("DELETE FROM bb_tema WHERE id='$thread' LIMIT 1");
		if ($result == false || mysql_affected_rows() == 0)
			return false;
			
		return true;
	}
	
	$result = myquery("SELECT zadnji_post, id FROM bb_tema WHERE zadnji_post='$id' LIMIT 1");
	if (mysql_num_rows($result) > 0)
	{
		//assign this value to the new last post
		$row = mysql_fetch_assoc($result);
		$thread = $row[id];
		
		$result = myquery("SELECT id FROM bb_post WHERE tema='$thread' ORDER BY vrijeme DESC LIMIT 1");
		$row = mysql_fetch_assoc($result);
		$post = $row[id];
		
		$result = myquery("UPDATE bb_tema SET zadnji_post='$post' WHERE id='$thread' LIMIT 1");
		if ($result == false || mysql_affected_rows() == 0)
			return false;
		
		return true;		
	}	

	return true;
}

?>