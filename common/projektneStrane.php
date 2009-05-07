<?php
function common_projektneStrane()
{
	//debug mod aktivan
	global $conf_debug, $userid, $user_nastavnik, $user_student, $conf_files_path;
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
		return;
	}
	
	//bad userid
	if (!is_numeric($userid) || $userid <=0)
	{
		return;	
	}
	
	if ($user_student)
	{
		$actualProject = getActualProjectForUserInPredmet($userid, $predmet);
		if ($actualProject[id] != $projekat)
		{
			//user is not in this project in this predmet...hijack attempt?
			return;	
		}
		
	}
	
	$params = getPredmetParams($predmet);
	$project = getProject($projekat);	
	$members = fetchProjectMembers($project[id]);
	
	if ($params[zakljucani_projekti] == 0)
		return;
	
	
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
				//display links for this project, with links to edit and delete
				$links = fetchLinksForProject($project[id]);
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
				//display links for this project, with links to edit and delete
				$feeds = fetchRSSForProject($project[id]);
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
    		<img src="<?=$conf_files_path . "/projekti/clanci/$predmet/$article[osoba]/$article[slika]" ?>" />
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
						return;				
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
			<div class="imgCont">
				<img src="<?=$conf_files_path . "/projekti/clanci/$predmet/$article[osoba]/$article[slika]" ?>" />
			</div>
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
                        <span class="label">Tekst *</span>
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
                            <span class="label">Tekst *</span>
                            <span class="formw"><textarea name="tekst" cols="60" rows="15" wrap="physical" id="tekst"><?php echo stripslashes(htmlentities($entry['tekst'], ENT_QUOTES)) ?></textarea></span>
                        </div> 

	<?php 					if ($entry['slika'] != '')
		  					{
							//if the image exists, display it
				  ?>
                       <div class="row">
                            <span class="label">Trenutna slika</span>
                            <span class="formw"><img src="<?php 
							$lokacijaclanaka ="$conf_files_path/projekti/clanci/$projekat/$userid/";
							echo $lokacijaclanaka . $entry[slika]; ?>" />
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
		return $errorText;		
	}
	
	//get variables
	$naslov 	= $_POST['naslov'];
	$tekst 		= $_POST['tekst'];
	$slika 		= $_FILES['image'];
	
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
				$uploadFile =  $uniqueID . $extension;	
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
	$query = sprintf("DELETE FROM bl_clanak WHERE id='%d' LIMIT 1", 
					intval($id)
					);
	
	$result = myquery($query);
	
	return ( $result == false ) ? false : true;
}





?>