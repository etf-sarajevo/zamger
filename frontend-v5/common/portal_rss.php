<?

// COMMON/PORTAL_RSS - dio portala koji se bavi prikazom RSS feedova


// Kocka samo sa naslovima, za početnu portal strnaicu

function common_portal_rss_ukratko($portal, $linkNaRss) {
	// Za sada zabranjujemo pristup osim iz ovih modula
	if ($_REQUEST['sta'] != "student/projekti" && $_REQUEST['sta'] != "nastavnik/projekti") {
		niceerror("Pristup za sada zabranjen");
		return;
	}

	require_once("Config.php");
	require_once(Config::$backend_path."core/Util.php");
	require_once(Config::$backend_path."lms/portal/Portal.php");
	require_once(Config::$backend_path."lms/portal/PortalFeedRenderer.php");

	?>
		<div class="blockRow">
			<div class="block" id="latestRSS">
			<a class="blockTitle" href="<?=$linkNaRss ?>" title="RSS feedovi">RSS feedovi</a>
			<div class="items">
        		<?

			// RSS - ovaj dio ispod ništa ne valja jer prikazuje linkove na feedove umjesto sadržaj feedova
			// Sadržaj se prikazuje tek kad se klikne na RSS tab

			$feeds = $portal->getRSSFeeds();

			foreach ($feeds as $feed) {
				$url = $feed->url;
				$scheme = parse_url($url);
				
				if ($scheme['scheme'] == '') //only www part
					$url = 'http://' . $url;
					
				?>
				<div class="item">
				<a href="<?=$url ?>" title="<?=$feed->title?>" target="_blank"><?
					
				print Util::ellipsize($feed->title, 35, 5); // pošto je kocka mala, stavljamo max. 35 znakova, riječ siječemo nakon 5
				?></a>
				<?
				
				/*if ($feed->description != '') {
					?>
					<div class="desc"><?
					print Util::ellipsize($feed->description, 200); // skraćujemo opis na 200 znakova
		
					?></div><!--desc-->
				<?
				}*/
				$pfr = new PortalFeedRenderer;
				$pfr->defaults();
				$pfr->url = $url;
				$pfr->FeedMaxItems = 5;
				$pfr->cacheTimeoutSeconds = 1*60; // svake minute FIXME
				$pfr->template = Config::$backend_path . "lib/rss2html/rss_kratki_sablon.html";
				$pfr->stripTagsFromFeed = true;
				$pfr->limitItemTitleLength = 50;
	
				print $pfr->render();
	
				?>
				</div><!--item-->
        			<?
			} //foreach

			?>
			</div><!--items-->
			</div><!--block-->
		</div><!--blockRow-->
	<?
} //function

function common_portal_rss($portal, $linkNaRss) {
	global $userid;

	require_once("Config.php");
	require_once(Config::$backend_path."core/Util.php");
	require_once(Config::$backend_path."lms/portal/Portal.php");
	require_once(Config::$backend_path."lms/portal/PortalFeedRenderer.php");

	$subaction = $_REQUEST['subaction'];
	$id = $_REQUEST['id']; // ID RSS feeda za edit/delete

	?>
	<h2>RSS feedovi</h2>
	<div class="links clearfix" id="rss">
	<ul>
		<li><a href="<?=$linkNaRss?>">Lista RSS feedova</a></li>
		<li><a href="<?=$linkNaRss . "&subaction=add" ?>">Novi RSS feed</a></li>
	</ul>
	</div>
	
	<?

	if (!isset($subaction)) {
		$feeds = $portal->getRSSFeeds();
		foreach ($feeds as $link) {
			if ($link->authorId == $userid) {
				?>
				<div class="links clearfix" id="rss">
				<ul>
					<li><a href="<?=$linkNaRss . "&subaction=edit&id=".$link->id ?>">Izmijeni</a></li>
					<li><a href="<?=$linkNaRss . "&subaction=del&id=".$link->id ?>">Obriši</a></li>
				</ul>
				</div>
				<?
			} //if user is author of this item

			?>
			<table class="rss" border="0" cellspacing="0" cellpadding="2">
			<tr>
				<th width="200" align="left" valign="top" scope="row">URL</th>
				<td width="490" align="left" valign="top">
				<?

				$url = $link->url;
				$scheme = parse_url($url);
			
				if ($scheme['scheme'] == '') //only www part	
					$url = 'http://' . $url;
				
				?><a href="<?=$url ?>" title="<?=$link->title ?>" target="_blank"><?=$link->title ?></a>
				</td>
			</tr>
			<?

			if ($link->description != '') {
			?>
			<tr>
				<th width="200" align="left" valign="top" scope="row">Opis</th>
				<td width="490" align="left" valign="top"><?=$link->description ?></td>
			</tr>
			<?
			} //opis

			?>
			<tr>
				<td colspan="2">
				<?

				// FIXME neke od ovih stvari treba staviti u parametre samog feeda
				$pfr = new PortalFeedRenderer;
				$pfr->defaults();
				$pfr->url = $url;
				$pfr->FeedMaxItems = 5;
				$pfr->cacheTimeoutSeconds = 1*60; // svake minute FIXME
				$pfr->template = Config::$backend_path . "lib/rss2html/rss_sablon.html";
				$pfr->LongDateFormat = "d. m. Y.";
				$pfr->ShortTimeFormat = "H:i";
				$pfr->stripTagsFromFeed = true;
				$pfr->limitItemDescriptionLength = 200;
	
				print $pfr->render();

				?>
				</td>
			</tr>
			
			</table>
			<?php
		} //foreach link

		$maxPage = 0; //ceil(count($feeds)/$rowsPerPage);
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

	else if ($subaction == 'add') {
		if (isset($_REQUEST['submit']) && check_csrf_token()) {
			// get variables
			$naziv = trim(my_escape($_REQUEST['naziv']));
			$url = trim(my_escape($_REQUEST['url']));
			$opis = trim(my_escape($_REQUEST['opis']));
			
			if (empty($naziv) || empty($url)) {
				niceerror("Unesite sva obavezna polja.");
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');

			} else {
				$pl = new PortalFeed;
				$pl->title = $naziv;
				$pl->url = $url;
				$pl->description = $opis;
				$pl->authorId = $userid;
				$pl->portalId = $portal->id;
				$pl->add();

				nicemessage('Novi RSS feed uspješno dodan.');
				zamgerlog("dodao RSS feed na projektu $projekat (pp$predmet)", 2);
				nicemessage('<a href="'.$linkNaRss.'">Povratak.</a>');
			}

		} else {
			?>
			<h3>Novi RSS feed</h3>
			<?=genform("POST", "addForm"); ?>
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
			<?
		} // not submitted yet
		
	} //subaction == add

	elseif ($subaction == 'edit') {
		$link = PortalFeed::fromId($id);
		if ($link->portalId != $portal->id) return; // da li je link sa projekta
		if ($link->authorId != $userid && $portal->ownerId != $userid) return; // da li korisnik ima prava
		
		//edit item
		if (isset($_REQUEST['submit']) && check_csrf_token()) {
			// get variables
			$naziv = trim(my_escape($_REQUEST['naziv']));
			$url = trim(my_escape($_REQUEST['url']));
			$opis = trim(my_escape($_REQUEST['opis']));

			if (empty($naziv) || empty($url)) {
				niceerror("Unesite sva obavezna polja.");
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');

			} else {
				$link->title = $naziv;
				$link->url = $url;
				$link->description = $opis;
				$link->update();
				nicemessage('Uspješno ste izmijenili RSS feed.');
				zamgerlog("uredio link na projektu $projekat (pp$predmet)", 2);
				nicemessage('<a href="'.$linkNaRss.'">Povratak.</a>');
			} //option == edit

		} else {
			?>
			<h3>Izmijeni RSS feed</h3>
			<?=genform("POST", "editForm"); ?>

			<div id="formDiv">
				Polja sa * su obavezna. <br />
				
				<div class="row">
					<span class="label">Naziv *</span>
					<span class="formw"><input name="naziv" type="text" id="naziv" size="70" value="<?=$link->title ?>" /></span> 
				</div>

				<div class="row">
					<span class="label">URL *</span>
					<span class="formw"><input name="url" type="text" id="url" size="70" value="<?=$link->url ?>" /></span> 
				</div>
				<div class="row">
					<span class="label">Opis</span>
					<span class="formw"><textarea name="opis" cols="60" rows="15" wrap="physical" id="opis"><?=$link->description ?></textarea></span>
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
		$link = PortalFeed::fromId($id);
		if ($link->portalId != $portal->id) return; // da li je link sa projekta
		if ($link->authorId != $userid && $portal->ownerId != $userid) return; // da li korisnik ima prava

		//delete item
		if (isset($_REQUEST['submit']) && check_csrf_token()) {
			$link->delete();
			nicemessage('Uspješno ste obrisali RSS feed.');	
			zamgerlog("obrisao RSS feed na projektu $projekat (pp$predmet)", 2);

			nicemessage('<a href="'. $linkNaRss .'">Povratak.</a>');
		} else {
			?>
			<h3>Brisanje RSS feeda</h3>
			<?=genform("POST", "deleteForm"); ?>
			Da li ste sigurni da želite obrisati ovaj RSS feed?<br />
			<input name="submit" type="submit" id="submit" value=" Obriši RSS feed "/>
			<input type="button" onclick="javascript:window.location.href = '<?=$linkNaRss?>';" value=" Nemoj brisati "/>
			</form>
			<?
		} 
	} //subaction == del

}

?>
