<?

// COMMON/PORTAL_FAJLOVI - dio portala koji se bavi uploadom i downloadom fajlova


// Spisak fajlova (zadnja verzija) za naslovnicu portala

function common_portal_fajlovi_ukratko($folder_id, $linkNaFajlove) {
	// Za sada zabranjujemo pristup osim iz ovih modula
	if ($_REQUEST['sta'] != "student/projekti" && $_REQUEST['sta'] != "nastavnik/projekti") {
		niceerror("Pristup za sada zabranjen");
		return;
	}

	require_once("Config.php");
	require_once(Config::$backend_path."core/Util.php");
	require_once(Config::$backend_path."lms/file/Folder.php");

	$folder = new Folder;
	$folder->id = $folder_id; // FIXME!

	?>
		<div class="blockRow">
			<div class="block" id="latestFiles">
			<a class="blockTitle" href="<?=$linkPrefix . "&section=file" ?>" title="Fajlovi">Fajlovi</a>
			<div class="items">
			<?
			// Fajlovi
			
			//$files = fetchFilesForProjectLatestRevisions($project[id], 0, 4);;
			$files = $folder->getAllFiles();
			
			foreach ($files as $file) {
				?>
				<div class="item">
					<span class="date"><?=date('d.m H:i  ', $file->lastRevision->time) ?></span>
					<a href="<?="index.php?sta=common/attachment&tip=projekat&projekat=$projekat&id=".$file->id ?>" title="<?=$file->filename?>" ><?
					print Util::ellipsize($file->filename, 100, 0); // pošto nema riječi postavljamo dužinu riječi na 0
					?></a>
					<span class="author"> - <?= $file->author->name . ' ' . $file->author->surname ?></span>
				
				</div><!--item-->	
				<?
			} //foreach

			?>
			</div><!--items-->
			</div><!--block-->
		</div><!--blockRow-->

	</div><!--rightBlocks-->
	</div><!--mainWrapper-->
	<?
} //function


function common_portal_fajlovi($folder_id, $linkNaFajlove) {
	global $userid;

	require_once("Config.php");
	require_once(Config::$backend_path."core/Util.php");
	require_once(Config::$backend_path."lms/file/Folder.php");

	$subaction = $_REQUEST['subaction'];
	$id = $_REQUEST['id']; // ID linka za edit/delete

	$folder = new Folder;
	$folder->id = $folder_id; // FIXME!

	?>
	<h2>Fajlovi</h2>
	<div class="links clearfix" id="rss">
	<ul>
		<li><a href="<?=$linkNaFajlove ?>">Lista fajlova</a></li>
		<li><a href="<?=$linkNaFajlove . "&subaction=add" ?>">Novi fajl</a></li>
	</ul>   
	</div>	
	
	<?

	if (!isset($subaction)) {
		$rowsPerPage = 20;
		$pageNum = 1;
		if(isset($_REQUEST['page']))
		{
			$pageNum = $_REQUEST['page'];
		}
		// counting the offset
		$offset = ($pageNum - 1) * $rowsPerPage;			
		
		//display files for this project, with links to edit and delete
		$files = $folder->getAllFiles($rowsPerPage, $offset);

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

		foreach ($files as $file) {
			?>
			<tr>
			<td class="creation_date"><?=date('d.m.Y H:i:s', $file->lastRevision->time) ?></td><!--vrijeme-->
			<td class="author"><?=$file->author->name . ' ' . $file->author->surname ?></td><!--author-->
			<td class="revision">v<?=$file->nrRevisions ?></td><!--revizija-->
			<td class="filename"><?

			if ($file->nrRevisions > 1) {
				?>
				<a href="#" onclick="toggleFileRevisions('file_<?=$file->id ?>_revisions')"><?=$file->filename ?></a>
				<?
			} else {
				print $file->filename;
			}
			
			?>
			</td><!--filename-->
			<td class="filesize"><?
				$path = $file->lastRevision->getPath();
				print nicesize(filesize($path));
			?>
			</td><!--filesize-->
			<td class="options">
				<a href="<?='index.php?sta=common/attachment' . "&tip=projekat&projekat=$projekat&id=" . $file->lastRevision->id ?>">Snimi</a>
			<?
			if ($file->lastRevision->authorId == $userid) {
				?>
				<a href="<?=$linkNaFajlove . "&subaction=edit&id=" . $file->id ?>">Izmijeni</a>
				<a href="<?=$linkNaFajlove . "&subaction=del&id=" . $file->id ?>">Obriši</a>
				<?
			} //if user is author of this item

			?>
			</td><!--options-->
			</tr><!--file_leading-->
			<?

			if ($file->nrRevisions > 1) {
				for ($i = 1; $i < $file->nrRevisions; $i++) {	
					$revision = $file->getRevision($i);
					?>
						<tr class="file_<?=$file->id ?>_revisions" style="display: none;" id="file_revisions">
						<td class="creation_date"><?=date('d.m.Y H:i:s', $revision->time) ?></td><!--vrijeme-->
						<td class="author"><?=$revision->author->name . ' ' . $revision->author->surname ?></td><!--author-->
						<td class="revision">v<?=$revision->revisionNumber ?></td><!--revizija-->
						<td class="filename"><?=$file->filename ?></td><!--filename-->
						<td class="filesize"><?
							print Util::nicesize(filesize($revision->getPath()));
							?>
						</td><!--filesize-->
						<td class="options">
							<a href="<?='index.php?sta=common/attachment' . "&tip=projekat&projekat=$projekat&id=" . $revision->id ?>">Snimi</a>
						</td><!--options-->
						</tr><!--file_revision-->	
					<?
				} //foreach revision

			} //if count revisions > 1

		} //foreach file

		?>
		</table>
		<!--files_table-->
		<?

		$numrows = $folder->getFileCount();
					
		$maxPage = ceil($numrows/$rowsPerPage);
		$self = $linkPrefix;
	
		if ($maxPage > 0) {
			echo "<span class=\"newsPages\">";
			if ($pageNum > 1) {
				$page = $pageNum - 1;
				$prev = " <a href=\"$self&page=$page\">[Prethodna]</a> ";
				
				$first = " <a href=\"$self&page=1\">[Prva]</a> ";
			} 
			
			if ($pageNum < $maxPage) {
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

	else if ($subaction == 'add') {
		if (isset($_REQUEST['submit']) && check_csrf_token()) {
			set_time_limit(0);

			//get variables
			$uploadedFile = $_FILES['uploadedFile'];

			// Handlujemo neke uobičajene greške
			if ($uploadedFile['error'] == 4) {
				niceerror('Unesite sva obavezna polja.');
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}
			else if ($uploadedFile['error'] == 1 || $uploadedFile['error'] == 2) {
				niceerror('Pokušavate poslati fajl koji je veci od dozvoljene veličine. Probajte sa manjim fajlom.<br />');
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}
			else if ($uploadedFile['error'] > 0) {
				niceerror('Vaš fajl nije poslan korektno. Molimo pokušajte ponovo.<br />');
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}

			$name = trim($uploadedFile['name']);

			// Pošto su backend i frontend na istom serveru, iskoristićemo move_uploaded_file
			try {
				$file = $folder->addFileWithoutContent($name, $userid);
			} catch(Exception $e) {
				niceerror('Fajl sa ovim imenom već postoji.<br />');
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}

			if ( move_uploaded_file( $uploadedFile['tmp_name'], $file->lastRevision->getPath() ) ) {
				chmod($file->lastRevision->getPath(), 0777);	
			} else {
				$file->delete();
				niceerror('Zapisivanje fajla na serveru nije uspjelo. Kontaktirajte administratora.<br />');
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}
			
			nicemessage('Novi fajl uspješno dodan.');
			zamgerlog("dodao novi fajl na projektu $projekat (pp$predmet)", 2);
			nicemessage('<a href="'. $linkNaFajlove .'">Povratak.</a>');

		} else {
			?>
			<h3>Novi fajl</h3>
			<?=genform("POST", "addForm\" enctype=\"multipart/form-data\" "); ?>
				
			<div id="formDiv">
				Polja sa * su obavezna. <br />
				<b>Limit za upload je 20MB.</b> <br />
				<div class="row">
				<span class="label">Fajl *</span>
				<span class="formw">
					<input name="uploadedFile" type="file" id="uploadedFile" size="60" />
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
		
	} //subaction == add
	elseif ($subaction == 'edit') {
		$file = ZFile::fromId($id);
		if ($file->folderId != $folder->id) return; // nije iz ovog projekta
		// if ($file->authorId != $userid) return; // Dozvolićemo svima da uploaduju fajlove!

		if (isset($_REQUEST['submit']) && check_csrf_token()) {
			set_time_limit(0);

			//get variables
			$uploadedFile = $_FILES['uploadedFile'];

			// Handlujemo neke uobičajene greške
			if ($uploadedFile['error'] == 4) {
				niceerror('Unesite sva obavezna polja.');
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}
			else if ($uploadedFile['error'] == 1 || $uploadedFile['error'] == 2) {
				niceerror('Pokušavate poslati fajl koji je veci od dozvoljene veličine. Probajte sa manjim fajlom.<br />');
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}
			else if ($uploadedFile['error'] > 0) {
				niceerror('Vaš fajl nije poslan korektno. Molimo pokušajte ponovo.<br />');
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}

			// Pošto su backend i frontend na istom serveru, iskoristićemo move_uploaded_file
			$rev = $file->addRevisionWithoutContent($userid);

			if ( move_uploaded_file( $uploadedFile['tmp_name'], $rev->getPath() ) ) {
				chmod($rev->getPath(), 0777);	
			} else {
				$file->dropRevision();
				niceerror('Zapisivanje fajla na serveru nije uspjelo. Kontaktirajte administratora. '.$rev->getPath().'<br />');
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}

			// Generišemo diff
			$rev->updateDiff();
			
			nicemessage('Uspješno ste izmijenili fajl.');
			zamgerlog("izmijenio novi fajl na projektu $projekat (pp$predmet)", 2);
			nicemessage('<a href="'. $linkNaFajlove .'">Povratak.</a>');

		} else {
			?>
			<h3>Izmijeni fajl</h3>
			<?=genform("POST", "editForm\" enctype=\"multipart/form-data\" "); ?>
			
			<div id="formDiv">
				Polja sa * su obavezna. <br />
				<b>Limit za upload je 20MB.</b> <br />
				<div class="row">
					<span class="label">Trenutni fajl</span>
					<span class="formw"><a href="<?='index.php?sta=common/attachment' . "&tip=projekat&projekat=$projekat&id=" . $file->lastRevision->id ?>" >
						<?=$file->filename ?>
					</a>
					</span>
				</div> 

				<div class="row">
					<span class="label">Zamijeni fajl</span>
					<span class="formw">
						<input name="uploadedFile" type="file" id="uploadedFile" size="50" />
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
	} //subaction == edit

	elseif ($subaction == 'del') {
		$file = ZFile::fromId($id);
		if ($file->folderId != $folder->id) return; // nije iz ovog projekta
		// if ($file->authorId != $userid) return; // Dozvolićemo svima da brišu fajlove!

		//edit item
		if (isset($_REQUEST['submit']) && check_csrf_token()) {
			$file->delete();
			nicemessage('Uspješno ste obrisali fajl.');	
			zamgerlog("obrisao fajl na projektu $projekat (pp$predmet)", 2);

			nicemessage('<a href="'. $linkNaFajlove .'">Povratak.</a>');
		} else {
			?>
			<h3>Brisanje fajla</h3>
			<?=genform("POST", "deleteForm"); ?>
			Da li ste sigurni da zelite obrisati ovaj fajl?<br />
			<input name="submit" type="submit" id="submit" value=" Obriši fajl "/>
			<input type="button" onclick="javascript:window.location.href = '<?=$linkNaFajlove ?>';" value=" Nemoj brisati "/>
			</form>
			<?				
		} 
	} //subaction == del

}

?>
