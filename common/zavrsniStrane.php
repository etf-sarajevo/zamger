<?
// COMMON/ZAVRSNISTRANE - stranice zavrsnih radova

function common_zavrsniStrane()
{
	//debug mod aktivan
	global $userid, $user_nastavnik, $user_student, $conf_files_path, $user_siteadmin;
	$predmet 	= intval($_REQUEST['predmet']);
	$ag 		= intval($_REQUEST['ag']);
	$zavrsni 	= intval($_REQUEST['zavrsni']);
	$action 	= $_REQUEST['action'];
	
	//stranica za završne radove
	$section 	= $_REQUEST['section'];
	$subaction  = $_REQUEST['subaction'];
	$id			= intval($_REQUEST['id']);  
	if ($user_student && !$user_siteadmin) 
	{
		$actualZavrsni = getActualZavrsniForUserInPredmet($userid, $predmet, $ag);
		if ($actualZavrsni[id] != $zavrsni)
		{
			//korisnik nije prijavljen na temu završnog rada
			zamgerlog("strane završnih radova: korisnik nije na završnom radu $zavrsni (pp$predmet, ag$ag)", 3);
			return;	
		}
	}
	
	$paramsZavrsni = getPredmetParamsForZavrsni($predmet, $ag);
	$zavrsni1 = getZavrsni($zavrsni);	
	$membersZavrsni = fetchZavrsniMembers($zavrsni1[id]);
	
	
	if ($user_student && !$user_siteadmin)
		$linkPrefix = "?sta=student/zavrsni&akcija=zavrsnistranica&zavrsni=$zavrsni&predmet=$predmet&ag=$ag";
	elseif ($user_nastavnik)
		$linkPrefix = "?sta=nastavnik/zavrsni&akcija=zavrsni_stranica&zavrsni=$zavrsni&predmet=$predmet&ag=$ag";
	else
		return;
	?>  
    
    <h2><?=filtered_output_string($zavrsni1[naziv]) ?></h2>
     <div class="links">
            <ul class="clearfix">
            	<li><a href="<?php echo $linkPrefix?>">Početna strana</a></li>
            	<li><a href="<?php echo $linkPrefix . "&section=info"?>">Informacije o temi završnog rada</a></li>
                <li><a href="<?php echo $linkPrefix . "&section=file"?>">Fajlovi</a></li>
            </ul>   
     </div>	
    <?
		if ($section == 'info')
		{
			// display završni info
	?>
    		<h2>Informacije o temi završnog rada</h2>

			<table class="zavrsni" border="0" cellspacing="0" cellpadding="2">
  				<tr>
    				<th width="200" align="left" valign="top" scope="row">Naziv</th>
    				<td width="490" align="left" valign="top"><?=filtered_output_string($zavrsni1['naziv'])?></td>
  				</tr>
 				<tr>
   					<th width="200" align="left" valign="top" scope="row">Prijavljeni student</th>
   		 			<td width="490" align="left" valign="top">
    				<?
					if (empty($membersZavrsni))
						echo 'Nema prijavljenih studenata.';
					else
					{
					?>
        				<ul>
       			 		<?
						foreach ($membersZavrsni as $member)
						{
						?>
        					<li><?=filtered_output_string($member[prezime] . ' ' . $member[ime] . ', ' . $member[brindexa]); ?></li>
							<?		
						}
							?>
        				</ul>	
					<?	
					}
					?>
    				</td>
  				</tr>
  				<tr>
    				<th width="200" align="left" valign="top" scope="row">Opis</th>
    				<td width="490" align="left" valign="top"><?=filtered_output_string($zavrsni1['opis'])?></td>
  				</tr>
			</table>
    		<?
			} //section -- info
			else($section == 'file')
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

    			<?	
				if (!isset($subaction))
				{
					$rowsPerPage = 20;
					$pageNum = 1;
					if(isset($_REQUEST['page']))
					{
						$pageNum = $_REQUEST['page'];
					}
					
					// counting the offset
					$offset = ($pageNum - 1) * $rowsPerPage;			
				
					//display files 
					$filesZavrsni = fetchFilesForZavrsniAllRevisions($zavrsni1[id], $offset, $rowsPerPage);
				?>
					<table class="files_table" border="0" cellspacing="0" cellpadding="0">
  						<tr>
    						<th scope="col" class="creation_date">Datum kreiranja</th>
    						<th scope="col" class="author">Autor</th>
   		 					<th scope="col" class="revision">Revizija</th>
    						<th scope="col" class="name">Naziv</th>
    						<th scope="col" class="filesize">Veličina</th>
    						<th scope="col" class="options">Opcije</th>
  						</tr>
						<?
						foreach ($filesZavrsni as $file)
						{
							$lastRevisionId = 0;
							$firstRevisionId = count($file) > 0 ? count($file) - 1 : 0;
							$authorZavrsni = getAuthorOfFileForZavrsni($file[$lastRevisionId][id]);
						?>				
    					<tr>
    						<td class="creation_date"><?=date('d.m.Y H:i:s', mysql2time($file[$lastRevisionId][vrijeme]))?></td><!--vrijeme-->
    						<td class="author"><?=filtered_output_string($authorZavrsni['ime'] . ' ' . $authorZavrsni['prezime']) ?></td><!--author-->
        					<td class="revision">v<?=$file[$lastRevisionId][revizija] ?></td><!--revizija-->
        					<td class="filename">
							<? 
							if (count($file) > 1)
							{
							?>
								<a href="#" onClick="toggleFileRevisions('file_<?=$file[$lastRevisionId][id] ?>_revisions')"><?=filtered_output_string($file[$lastRevisionId][filename]) ?></a>		
   							<?
    						}
							else
							{
							?>
    							<?=filtered_output_string($file[$lastRevisionId][filename]) ?>
    						<?						
							}
    						?>        
                            </td><!--filename-->
        					<td class="filesize">
								<?
        						$lokacijafajlova ="$conf_files_path/zavrsni/fajlovi/$zavrsni/" . $file[$lastRevisionId][osoba] . "/" . 
								$file[$lastRevisionId][filename] . '/v' . $file[$lastRevisionId][revizija] . '/';
								$filepath = $lokacijafajlova . $file[$lastRevisionId][filename];
								$filesize = filesize($filepath);
								echo nicesize($filesize);
								?>        
                         	</td><!--filesize-->
        					<td class="options">
								<a href="<?='index.php?sta=common/attachment' . "&tip=zavrsni&zavrsni=$zavrsni&id=" . $file[$lastRevisionId][id] ?>">Snimi</a>        
								<?
								if (isUserAuthorOfFileForZavrsni($file[$lastRevisionId][id], $userid))
								{
								?>
           							<a href="<? echo $linkPrefix . "&subaction=edit&id=" . $file[$firstRevisionId]['id'] ?>">Uredi</a>
           							<a href="<? echo $linkPrefix . "&subaction=del&id=" . $file[$firstRevisionId]['id']?>">Briši</a>
								<?
								} //if user is author of this item
								?>        
                      		</td><!--options-->
    					</tr><!--file_leading-->
    					<?
						if (count($file) > 1)
						{
							for ($i = 1; $i < count($file); $i++)
							{	
								$revision = $file[$i];
								$authorZavrsni = getAuthorOfFileForZavrsni($revision[id]);
						?>
            			<tr class="file_<?=$file[$lastRevisionId][id] ?>_revisions" style="display: none;" id="file_revisions">
                			<td class="creation_date"><?=date('d.m.Y H:i:s', mysql2time($revision[vrijeme]))?></td><!--vrijeme-->
                			<td class="author"><?=filtered_output_string($author['ime'] . ' ' . $author['prezime']) ?></td><!--author-->
                			<td class="revision">v<?=$revision[revizija] ?></td><!--revizija-->
                			<td class="filename"><?=filtered_output_string($revision[filename]) ?></td><!--filename-->
                			<td class="filesize">
								<?
                    			$lokacijafajlova ="$conf_files_path/zavrsni/fajlovi/$zavrsni/" . $revision[osoba] . "/" . 
                    			$revision[filename] . '/v' . $revision[revizija] . '/';
                    			$filepath = $lokacijafajlova . $revision[filename];
                    			$filesize = filesize($filepath);
                    			echo nicesize($filesize);
                    			?>
                			</td><!--filesize-->
                			<td class="options">
                    			<a href="<?='index.php?sta=common/attachment' . "&tip=zavrsni&zavrsni=$zavrsni&id=" . $revision[id] ?>">Snimi</a>        
                			</td><!--options-->
            			</tr><!--file_revision-->	
    					<?					
							} //foreach revision
						} //if count files > 1

						} //foreach file
						?>
    			</table>
				<!--files_table-->

				<?
				$numrowsZavrsni = getCountFilesForZavrsniWithoutRevisions($zavrsni1[id]);		
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
		function getElementsByClassName( strClassName, obj ) 
		{
			var ar = arguments[2] || new Array();
			var re = new RegExp("\\b" + strClassName + "\\b", "g");
		
			if ( re.test(obj.className) ) 
			{
				ar.push( obj );
			}
			for ( var i = 0; i < obj.childNodes.length; i++ )
				getElementsByClassName( strClassName, obj.childNodes[i], ar );
			
			return ar;
		}
		
		function toggleFileRevisions(divID)
		{
			 var aryClassElements = getElementsByClassName( divID, document.body );
			for ( var i = 0; i < aryClassElements.length; i++ ) 
			{
				if (aryClassElements[i].style.display == '')
					aryClassElements[i].style.display = 'none';
				else
					aryClassElements[i].style.display = '';	
			}
		}
	</script>
    
    			<?		
				} //subaction not set
				else
				{
					if ($subaction == 'add')
					{
					if (!isset($_REQUEST['submit']))
					{
				?>
						<h3>Novi fajl</h3>
						<?
						print genform("POST", "addForm\" enctype=\"multipart/form-data\" ");
						?>
						
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
					<?	
					} //not submitted yet
					else
					{
						$errorText = formProcess_file('add');
						if($errorText == '')
						{
							nicemessage('Novi fajl uspješno dodan.');
							zamgerlog("dodao novi fajl na temu završnog rada $zavrsni (pp$predmet)", 2);
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
						if (!isUserAuthorOfFileForZavrsni($id, $userid))
							return;

						if (!isThisFileFirstRevision($id))
							return;
					
						//edit item
						if (!isset($_REQUEST['submit']))
						{
							$entryZavrsni = getFileFirstRevisionZavrsni($id);
							$lastRevisionEntryZavrsni = getFileLastRevisionZavrsni($id);
					?>
					 	<h3>Uredi fajl</h3>
						<?
						print genform("POST", "editForm\" enctype=\"multipart/form-data\" ");
						?>
					
						<div id="formDiv">
							Polja sa * su obavezna. <br />
							<b>Limit za upload je 20MB.</b> <br />							
					   		<div class="row">
								<span class="label">Trenutni fajl</span>
								<span class="formw"><a href="<?='index.php?sta=common/attachment' . "&tip=zavrsni&zavrsni=$zavrsni&id=" . $lastRevisionEntryZavrsni[id]?>" >
									<?=filtered_output_string($lastRevisionEntryZavrsni[filename]) ?>
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
	
					<?								
					}
					else
					{
						$errorText = formProcess_file('edit');
						if($errorText == '')
						{
							nicemessage('Uspješno ste uredili fajl.');
							zamgerlog("uredio fajl na temi završnog rada $zavrsni (pp$predmet)", 2);
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
					
				} //subaction == edit
				elseif ($subaction == 'del')
				{
					if (!isUserAuthorOfFileForZavrsni($id, $userid))
						return;
						
					if (!isThisFileFirstRevisionZavrsni($id))
						return;

					//delete item
					if (isset($id) && is_int($id) && $id > 0)
					{
						if (!isset($_REQUEST['c']))
						{
							echo "Da li ste sigurni da želite obrisati ovaj fajl? Obrisat ćete sve revizije fajla sa servera.<br />";	
							echo '<a href="' . $linkPrefix . '&subaction=del&id=' . $id . '&c=true">Da</a> | <a href="' . $linkPrefix . '">Odustani</a>';			
						}
						else
						{
							if ($_REQUEST['c'] == 'true')
							{
								//delete the record
								if (deleteFileZavrsni($id))
								{
									nicemessage('Uspješno ste obrisali fajl.');	
									zamgerlog("obrisao fajl na temi završnog rada $zavrsni (pp$predmet)", 2);
									$link = $linkPrefix;
								}
								else
								{
									niceerror('Došlo je do greške prilikom brisanja fajla. Molimo kontaktirajte administratora.');
									$link = "javascript:history.back();";	
								}
								nicemessage('<a href="'. $link .'">Povratak.</a>');
							}
							
						} //else isset c get parameter
								
						
					} //id is okay		
				
				} //subaction == del
	
			} //subaction set
			
		} //section == file	

} //function

function formProcess_file($option)
{
	$errorText = '';
	if (!check_csrf_token()) 
	{
		zamgerlog("csrf token nije dobar",3);
		return "Poslani podaci nisu ispravni. Vratite se nazad, ponovo popunite formu i kliknite na dugme Pošalji";
	}
	
	set_time_limit(0);
	
	if (!in_array($option, array('add', 'edit') ) )
	{
		$errorText = 'Došlo je do greške prilikom spašavanja podataka. Molimo kontaktirajte administratora.';
		return $errorText;		
	}
	
	$id = intval($_REQUEST['id']);
	
	if ($option == 'edit' && $id <=0)
	{
		$errorText = 'Došlo je do greške prilikom spašavanja podataka. Molimo kontaktirajte administratora.';
		zamgerlog("pokušao urediti nepostojeći fajl $id, završni rad $zavrsni (pp$predmet)", 3);
		return $errorText;
	}
	if ($option == 'edit' && isThisFileFirstRevision($id) == false)
	{
		//cannot get access to revisions other than the first one	
		$errorText = 'Došlo je do greške prilikom spašavanja podataka. Molimo kontaktirajte administratora.';
		zamgerlog("pokušao urediti staru reviziju fajla $id, završni rad $zavrsni (pp$predmet)", 3);
		return $errorText;
	}
	
	//process file
	if ($option == 'edit')
	{
		$entryZavrsni = getFileFirstRevisionZavrsni($id);
		$lastRevisionEntryZavrsni = getFileLastRevisionZavrsni($id);
	}
	
	//get variables
	$filename	= $_FILES['filename'];
	
	if ($option == 'edit')
	{
		$revizija = $lastRevisionEntryZavrsni[revizija] + 1;
		$file = $entry['id'];
	}
	else
	{
		$revizija = 1;
		$file = '';	
	}

	$zavrsni = intval($_REQUEST['zavrsni']);
	$predmet = intval($_REQUEST['predmet']);
	global $userid;

	
	if ($filename['error'] == 4)
	{
		$errorText = 'Unesite sva obavezna polja.';
		return $errorText;
	}
		

	global $conf_files_path;
	$lokacijafajlova ="$conf_files_path/zavrsni/fajlovi/$zavrsni/$userid/";
	
	if (!file_exists("$conf_files_path/zavrsni/fajlovi/$zavrsni")) 
	{
		mkdir ("$conf_files_path/zavrsni/fajlovi/$zavrsni",0777, true);
	}
	if (!file_exists($lokacijafajlova)) 
	{
		mkdir ($lokacijafajlova,0777, true);
	}
	
	//adding or replacing file - depends on the $option parameter(add, edit)

	if ($filename['error'] > 0)
	{
		if ($filename['error'] == 1 || $filename['error'] == 2)
			$errorText .= 'Pokušavate poslati fajl koji je veći od dozvoljene veličine. Probajte sa manjim fajlom.<br />';
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
		$lastRevisionFile = $lokacijafajlova . $lastRevisionEntry['filename'] . '/v' . $lastRevisionEntry['revizija'] . '/' . $lastRevisionEntry['filename'];
		$newFile          = $uploadDir . $uploadFile;
		
		$extension = preg_replace('/.+(\..*)$/', '$1', $lastRevisionEntry['filename']);
		$textExtensions = array(
								'.txt'
								);  

		if (in_array($extension, $textExtensions)) 
			$diffing = 1;
		
		if ($diffing == 1)
		{
			$diff = `/usr/bin/diff -u $lastRevisionFile $newFile`;
		}	
		 
	} //option == edit

	
	$data = array(
				'filename' => $uploadFile,
				'revizija' => $revizija, 
				'file' => $file, 
				'osoba' => $userid, 
				'zavrsni' => $zavrsni, 
				'diffing' => $diffing, 
				'diff' => $diff
	);
	
	if (!insertFileZavrsni($data))
	{
		$errorText = 'Došlo je do greške prilikom spašavanja podataka. Molimo kontaktirajte administratora.';
		return $errorText;		
	}
	
	return $errorText;
	
}

function insertFileZavrsni($data)
{

	//generate unique id value
	$id = generateIdFromTable('projekat_file');
	
	$query = sprintf("INSERT INTO zavrsni_file (id, filename, revizija, osoba, zavrsni, file) VALUES ('%d', '%s', '%d', '%d', '%d', '%d')", 
											$id, 
											my_escape($data['filename']), 
											intval($data['revizija']), 
											intval($data['osoba']), 
											intval($data['zavrsni']), 
											intval($data['file'])  						
					);
	$result = myquery($query);	
	
	if ($result == false || mysql_affected_rows() == 0)
		return false;
	
	//insert diff
	if ($data['diffing'] == 1)
	{
		$query = sprintf("INSERT INTO zavrsni_file_diff (file, diff) VALUES ('%d', '%s')", 
											$id, 
											my_escape($data['diff'])
		);
		$result = myquery($query);
		
		if ($result == false || mysql_affected_rows() == 0)
			return false;			
	}
	
	return true;	
}

function deleteFileZavrsni($id)
{
	global $conf_files_path;
	
	$listZavrsni = fetchAllRevisionsForFileZavrsni($id);
	
	foreach ($listZavrsni as $item)
	{
		$query = sprintf("DELETE FROM zavrsni_file WHERE id='%d' LIMIT 1", 
					intval($item[id])
					);
	
		$result = myquery($query);
		if (mysql_affected_rows() == 0)
			return false;
			
		$lokacijarevizije = "$conf_files_path/zavrsni/fajlovi/" . $item['zavrsni'] . '/' . $item['osoba'] . '/' . $item['filename'] . '/v' . $item['revizija'];
		
		if (!unlink($lokacijarevizije . '/' . $item[filename]))
			return false;	
		if (!rmdir($lokacijarevizije))
			return false;
			
		//remove any diffs for this file
		myquery("DELETE FROM zavrsni_file_diff WHERE file='" . $item[id] . "' LIMIT 1");
	}
	
	$lokacijafajlova = "$conf_files_path/zavrsni/fajlovi/" . $list[0]['zavrsni'] . '/' . $list[0]['osoba'] . '/' . $list[0]['filename'];
	if (!rmdir($lokacijafajlova))
		return false;
	
	return true;
}

function fetchZavrsniMembers($id)
{
	$result = myquery("SELECT * FROM osoba o INNER JOIN student_zavrsni oz ON o.id=oz.student WHERE oz.zavrsni='$id' ORDER BY prezime ASC, ime ASC");
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	return $list;	
}

function getZavrsni($id)
{
	$result = myquery("SELECT * FROM zavrsni WHERE id='$id' LIMIT 1");
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	return $list[0];	
}

function generateIdFromTable($table)
{
	$result = myquery("select id from $table order by id desc limit 1");
	
	if (mysql_num_rows($result) == 0)
	{
		$id = 0;
	}
	else
	{	
		$id = mysql_fetch_row($result);
		$id = $id[0];
	}
	
	return intval($id+1);
}

function getActualZavrsniForUserInPredmet($userid, $predmet, $ag)
{
	$result = myquery("SELECT z.* FROM zavrsni z, student_zavrsni oz WHERE z.id=oz.zavrsni AND oz.student='$userid' AND z.predmet='$predmet' AND z.akademska_godina='$ag' LIMIT 1");
	
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	return $list[0];	
}

function filtered_output_string($string)
{
	//performing nl2br function to display text from the database
	return nl2br($string);
}

function fetchFilesForZavrsniAllRevisions($id, $offset = 0, $rowsPerPage = 0)
{
	$query = "SELECT * FROM zavrsni_file WHERE zavrsni='$id' AND file=0 ORDER BY vrijeme DESC ";
	if ($offset == 0 && $rowsPerPage == 0)
	{}
	else
		$query .="LIMIT $offset, $rowsPerPage";
	
	$result = myquery($query);
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	$files = array();

	foreach ($list as $item)
	{
		$files[] = fetchAllRevisionsForFileZavrsni($item[id]);	
	}
	return $files;	
}

function fetchFilesForZavrsniLatestRevisions($id, $offset = 0, $rowsPerPage = 0)
{
	$query = "SELECT * FROM zavrsni_file WHERE zavrsni='$id' AND file=0 ORDER BY vrijeme DESC ";
	if ($offset == 0 && $rowsPerPage == 0)
	{}
	else
		$query .="LIMIT $offset, $rowsPerPage";
	
	$result = myquery($query);
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	return $list;
}

function getAuthorOfFileForZavrsni($id)
{
	$result = myquery("SELECT o.* FROM osoba o WHERE o.id=(SELECT f.osoba FROM zavrsni_file f WHERE f.id='$id' LIMIT 1) LIMIT 1");
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	return $list[0];
}

function isUserAuthorOfFileForZavrsni($file, $user)
{
	$result = myquery("SELECT id FROM zavrsni_file WHERE osoba='$user' AND id='$file' LIMIT 1");
	if (mysql_num_rows($result) > 0)
		return true;
	return false;
}

function getFileFirstRevisionZavrsni($id)
{
	$result = myquery("SELECT * FROM zavrsni_file WHERE id='$id' AND revizija=1 LIMIT 1");
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	return $list[0];
}

function getFileZavrsni($id)
{
	$result = myquery("SELECT * FROM zavrsni_file WHERE id='$id' LIMIT 1");
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	return $list[0];
}

function isThisFileFirstRevisionZavrsni($id)
{
	$result = myquery("SELECT id FROM zavrsni_file WHERE id='$id' AND revizija=1 LIMIT 1");
	if (mysql_num_rows($result) > 0)
		return true;
	return false;
}

function getFileLastRevisionZavrsni($id)
{
	$result = myquery("SELECT * FROM zavrsni_file WHERE file='$id' ORDER BY revizija DESC LIMIT 1");
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	if (empty($list))
	{
		//samo jedna revizija
		$list[0] = getFileFirstRevisionZavrsni($id);
	}
	
	return $list[0];
}

function fetchAllRevisionsForFileZavrsni($id)
{
	$list = array();	
	$result = myquery("SELECT * FROM zavrsni_file WHERE file='$id' ORDER BY revizija DESC");
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;
	
	$list[] = getFileFirstRevisionZavrsni($id);
	return $list;	
}

function getCountFilesForZavrsniWithoutRevisions($id)
{
	$result = myquery("SELECT COUNT(id) FROM zavrsni_file WHERE zavrsni='$id' AND revizija=1 LIMIT 1");
	$row = mysql_fetch_row($result);
	$row = $row[0];
	
	return $row;
}
?>
