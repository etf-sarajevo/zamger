<?

// COMMON/PORTAL_CLANCI - vijesti (članci) za portal


// Kratke vijesti za prikaz na naslovnici portala

function common_portal_clanci_ukratko($portal, $linkNaClanke) {
	// Za sada zabranjujemo pristup osim iz ovih modula
	if ($_REQUEST['sta'] != "student/projekti" && $_REQUEST['sta'] != "nastavnik/projekti") {
		niceerror("Pristup za sada zabranjen");
		return;
	}

	require_once("Config.php");
	require_once(Config::$backend_path."core/Util.php");
	require_once(Config::$backend_path."lms/portal/Portal.php");

	?>
		<div class="blockRow clearfix">
			<div class="block" id="latestArticles">
			<a class="blockTitle" href="<?=$linkNaClanke ?>" title="Članci">Najnoviji članci</a>
			<div class="items">
			<?
				$latestArticles = $portal->getLatestArticles(4); // 4 najnovija članka
				foreach ($latestArticles as $article) {
					?>
					<div class="item">
					<span class="date"><?=date('d.m H:i  ', $article->time) ?></span>
					<a href="<?=$linkNaClanke . "&subaction=view&id=".$article->id ?>" title="<?=$article->subject?>" target="_blank"><?
					print Util::ellipsize( $article->subject, 100 ); // skrati na 100 znakova
					?></a>
					<span class="author"> - <?=$article->author->name . ' ' . $article->author->surname ?></span>
					<div class="desc"><?
					print Util::ellipsize( $article->text, 200 ); // Skrati na 200 znakova
					?></div><!--desc-->
					</div><!--item-->	
					<?
				}
				
			?>
			
			</div><!--items-->
			</div><!--block-->
		</div><!--blockRow-->
	<?
} //function


function common_portal_clanci($portal, $linkNaClanke) {
	global $userid;

	require_once("Config.php");
	require_once(Config::$backend_path."core/Util.php");
	require_once(Config::$backend_path."lms/portal/Portal.php");

	$subaction = $_REQUEST['subaction'];
	$id = $_REQUEST['id']; // ID članka za edit/delete

	?>
	<h2>Članci</h2>
	<div class="links clearfix" id="bl">
	<ul>
		<li><a href="<?=$linkNaClanke ?>">Lista članaka</a></li>
		<li><a href="<?=$linkNaClanke . "&subaction=add"?>">Novi članak</a></li>
	</ul>   
	</div>	
	<?

	if (!isset($subaction)) {
		$rowsPerPage = 20;
		$pageNum = 1;
		if(isset($_REQUEST['page'])) {
			$pageNum = $_REQUEST['page'];
		}
		// counting the offset
		$offset = ($pageNum - 1) * $rowsPerPage;
		
		//$articles = fetchArticlesForProject($project[id], $offset, $rowsPerPage);
		$articles = $portal->getLatestArticles($rowsPerPage, $offset);
		foreach($articles as $article) {
			?>
			<div class="article_summary clearfix">
			<?

			if (!empty($article->image)) {
				?>
				<div class="imgCont">
				<a href="<?="index.php?sta=common/articleImageDownload&projekat=$projekat&predmet=$predmet&ag=$ag&a=".$article->id."&u=".$article->authorId."&i=".$article->image ?>" target="_blank">
					<img src="<?="index.php?sta=common/articleImageDownload&projekat=$projekat&predmet=$predmet&ag=$ag&a=".$article->id."&u=".$article->authorId."&i=".$article->image ?>" />
				</a>
				</div>
				<?
			}

			?>
			<div class="contentCont" <? if (empty($article->image)) echo 'style="margin-left: 0;"' ?>>
				<h1>
				<a href="<?=$linkNaClanke . "&subaction=view&id=".$article->id ?>" 
				title="<?=$article->subject ?>"><?=$article->subject ?>
				</a>
				</h1>
				<div class="details">
					Autor: <?=$article->author->name . ' ' . $article->author->surname ?><br />
					Datum: <?=date('d.m.Y', $article->time) ?>
				</div><!--details-->
			<?

			if ($article->authorId == $userid || $portal->ownerId == $userid) {
				?>	
				<div class="buttons">
				<a href="<?= $linkNaClanke . "&subaction=edit&id=".$article->id ?>" title="Izmijeni ovaj članak">Izmijeni</a> | 
				<a href="<?= $linkNaClanke . "&subaction=del&id=".$article->id ?>" title="Obriši ovaj članak">Obriši</a>
				</div><!--buttons-->	
				<?
			}

			?>
			<div class="text">
			<?
			if (empty($article->image))
				$txtLen = 800;
			else
				$txtLen = 400;
			print Util::ellipsize($article->text, $txtLen);
			?>
			</div><!--text-->
			</div><!--contentCont-->
			</div><!--article_summary--> 
			<?
		} //foreach article	

		$maxPage = ceil( $portal->getArticlesCount() / $rowsPerPage );
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

	} //subactin not set

	else if ($subaction == 'view') {
		$article = PortalArticle::fromId($id);
		if ($article->portalId != $portal->id) return; // da li je članak sa projekta

		?>
		<div class="article_full clearfix">
			<div class="contentCont clearfix">
				<h1>
					<a href="<?=$linkNaClanke . "?subaction=view&id=".$article->id ?>" 
					title="<?=$article->subject ?>"><?=$article->subject ?>
					</a>
				</h1>
				<div class="details">
					Autor: <?=$article->author->name . ' ' . $article->author->surname ?><br />
					Datum: <?=date('d.m.Y', $article->time) ?>
				</div><!--details-->
		<?

		if ($article->authorId == $userid || $portal->ownerId == $userid) {
			?>	
				<div class="buttons">
					<a href="<?= $linkNaClanke . "&subaction=edit&id=".$article->id ?>" title="Izmijeni ovaj članak">Izmijeni</a> | 
					<a href="<?= $linkNaClanke . "&subaction=del&id=".$article->id ?>" title="Obriši ovaj članak">Obriši</a>
				</div><!--buttons-->	
			<?
		}

		if (!empty($article->image)) {
			?>
				<div class="imgCont">
				<a href="<?="index.php?sta=common/articleImageDownload&projekat=$projekat&predmet=$predmet&ag=$ag&a=".$article->id."&u=".$article->authorId."&i=".$article->image ?>" target="_blank">
					<img src="<?="index.php?sta=common/articleImageDownload&projekat=$projekat&predmet=$predmet&ag=$ag&a=".$article->id."&u=".$article->authorId."&i=".$article->image ?>" />
				</a>
				</div>
			<?
		}

		?>
				<div class="text"><?=$article->text ?></div><!--text-->
			</div><!--contentCont-->
		</div><!--article_full--> 

		<a id="backLink" href="<?=$linkNaClanke ?>">Povratak na listu članaka</a>
		<?
	} //subaction == view

	elseif ($subaction == 'add') {
		if (isset($_REQUEST['submit']) && check_csrf_token()) {
			$naslov = trim(my_escape($_REQUEST['naslov']));
			$tekst = trim(my_escape($_REQUEST['tekst']));
			$slika = $_FILES['image'];

			$errMsg = uploadSlike($slika, $projekat);

			if (empty($naslov)) {
				niceerror("Unesite sva obavezna polja.");
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}

			try {
				$imageURL = uploadSlike($slika, $projekat);
			} catch(Exception $e) {
				niceerror($e->getMessage());
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}
			
			$pa = new PortalArticle;
			$pa->subject = $naslov;
			$pa->text = $tekst;
			$pa->image = $imageUrl;
			$pa->authorId = $userid;
			$pa->portalId = $portal->id;
			$pa->add();

			nicemessage('Novi članak uspješno dodan.');
			zamgerlog("dodao novi clanak na projektu $projekat (pp$predmet)", 2);
			nicemessage('<a href="'. $linkNaClanke .'">Povratak.</a>');

		} else { // Not submitted
			?>	
			<h3>Novi članak</h3>
			<?=genform("POST", "addForm\" enctype=\"multipart/form-data\" "); ?>

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
			<?
		} //not submitted yet

	} //subaction == add

	elseif ($subaction == 'edit') {
		$article = PortalArticle::fromId($id);
		if ($article->portalId != $portal->id) return; // da li je članak sa projekta
		if ($article->authorId != $userid && $portal->ownerId != $userid) return; // da li korisnik ima prava

		if (isset($_REQUEST['submit']) && check_csrf_token()) {
			$naslov = trim(my_escape($_REQUEST['naslov']));
			$tekst = trim(my_escape($_REQUEST['tekst']));
			$slika = $_FILES['image'];
			
			if (empty($naslov)) {
				niceerror("Unesite sva obavezna polja.");
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}

			try {
				$imageURL = uploadSlike($slika, $projekat);
			} catch(Exception $e) {
				niceerror($e->getMessage());
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}

			if (isset($_REQUEST['delete'])) { // Brisanje slike
				$lokacijaclanaka ="$conf_files_path/projekti/clanci/$projekat/$userid/";
				unlink($lokacijaclanaka . $article->image);
				$imageUrl = '';
			}
			else if ($imageUrl != '') { // Zamjena slike
				$lokacijaclanaka ="$conf_files_path/projekti/clanci/$projekat/$userid/";
				unlink($lokacijaclanaka . $article->image);
			}
			else
				$imageUrl = $article->image;

			$article->subject = $naslov;
			$article->text = $tekst;
			$article->image = $imageUrl;
			$article->update();
		}

		else { // not submitted
			?>
			<h3>Uredi članak</h3>
			<?=genform("POST", "editForm\" enctype=\"multipart/form-data\" "); ?>
		
			<div id="formDiv">
				Polja sa * su obavezna. <br />
			
				<div class="row">
					<span class="label">Naslov *</span>
					<span class="formw"><input name="naslov" type="text" id="naslov" size="70" value="<?=$article->subject ?>" /></span> 
				</div>
				<div class="row">
					<span class="label">Tekst</span>
					<span class="formw"><textarea name="tekst" cols="60" rows="15" wrap="physical" id="tekst"><?=$article->text ?></textarea></span>
				</div> 
			<?

			if ($article->image != '') {
				//if the image exists, display it
				?>
				<div class="row">
					<span class="label">Trenutna slika</span>
					<span class="formw"><img src="<?="index.php?sta=common/articleImageDownload&projekat=$projekat&predmet=$predmet&ag=$ag&a=".$article->id."&u=".$article->authorId."&i=".$article->image ?>" />
					</span>
				</div> 
				
				<div class="row">
					<span class="label">Briši sliku</span>
					<span class="formw"><input name="delete" type="checkbox" id="delete" value="delete" /></span>
				</div>
				<?
			} //if image is present

			?>
				<div class="row">
					<span class="label"><?
					if($article->image != '') echo "ILI: Zamijeni sliku"; else echo "Slika";?></span>
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
			<?
		}
		
	} //subaction == edit

	elseif ($subaction == 'del') {
		$article = PortalArticle::fromId($id);
		if ($article->portalId != $portal->id) return; // da li je članak sa projekta
		if ($article->authorId != $userid && $portal->ownerId != $userid) return; // da li korisnik ima prava

		//delete item
		if (isset($_REQUEST['submit']) && check_csrf_token()) {
			$link->delete();
			nicemessage('Uspješno ste obrisali članak.');	
			zamgerlog("obrisao clanak na projektu $projekat (pp$predmet)", 2);

			nicemessage('<a href="'. $linkNaClanke .'">Povratak.</a>');
		} else {
			?>
			<h3>Brisanje članka</h3>
			<?=genform("POST", "deleteForm"); ?>
			Da li ste sigurni da želite obrisati ovaj članak?<br />
			<input name="submit" type="submit" id="submit" value=" Obriši članak "/>
			<input type="button" onclick="javascript:window.location.href = '<?=$linkNaClanke?>';" value=" Nemoj brisati "/>
			</form>
			<?
		} 
	} //subaction == del
}




// Pomoćna funkcija koja handluje razne stvari vezane za upload slike
function uploadSlike($slika, $projekat) {
	global $conf_files_path, $userid;
	$lokacijaclanaka ="$conf_files_path/projekti/clanci/$projekat/$userid/";
	
	if (!file_exists("$conf_files_path/projekti/clanci/$projekat")) {
		mkdir ("$conf_files_path/projekti/clanci/$projekat",0777, true);
	}
	if (!file_exists($lokacijaclanaka)) {
		mkdir ($lokacijaclanaka,0777, true);
	}

	if ($slika['error'] != 4) {
		//cannot delete original image and preplace it with the new image so check this also
		
		if (isset($_REQUEST['delete'])) {
			throw new Exception('Selektujte ili brisanje slike, ili zamjena slike, ne oboje!');
		}
		
		//adding or replacing image - depends on the $option parameter(add, edit)
		
		if ($slika['error'] > 0) {
			if ($slika['error'] == 1 || $slika['error'] == 2)
				throw new Exception('Pokušavate poslati fajl koji je veci od dozvoljene velicine. Probajte sa manjim fajlom.<br />');
			else
				throw new Exception('Vaš fajl nije poslan korektno. Molimo pokušajte ponovo.<br />');
		} else {
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

			if (!in_array($extension, $safeExtensions)) {
				 throw new Exception('Format slike nije dozvoljen. <br />');
			}
			if (getimagesize($slika['tmp_name']) == false) {
				 throw new Exception('Format slike nije dozvoljen. <br />');
			}
			
			//final file name
			$uniqueID = date('YmdHis', time());
			$uploadFile =  $uniqueID . "$userid" . $extension;	
			
			if (move_uploaded_file($slika['tmp_name'], $uploadDir . $uploadFile)) {
				//transfered a file to upload directory from temp dir
				//if edit option REPLACING the old image (overwrite)
				chmod($uploadDir . $uploadFile, 0777);	
			
			} else {
				throw new Exception('Desila se greška prilikom uploada slike. Molimo kontaktirajte administratora.<br />');
			} //else
			
		} //else

		return $uploadFile;
	} //if ($_FILES['slika']['error'] != 4)

	return '';
}

?>
