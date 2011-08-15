<?

// COMMON/PORTAL_LINKOVI - omogućuje korisnicima upravljanje listom korisnih linkova


// Mala kocka sa spiskom linkova, za naslovnu portal stranicu

function common_portal_linkovi_ukratko($portal, $linkNaLinkove) {
	// Za sada zabranjujemo pristup osim iz ovih modula
	if ($_REQUEST['sta'] != "student/projekti" && $_REQUEST['sta'] != "nastavnik/projekti") {
		niceerror("Pristup za sada zabranjen");
		return;
	}

	require_once("Config.php");
	require_once(Config::$backend_path."core/Util.php");
	require_once(Config::$backend_path."lms/portal/Portal.php");

	?>
		<div class="blockRow">
			<div class="block" id="latestLinks">
			<a class="blockTitle" href="<?=$linkNaLinkove ?>" title="Korisni linkovi">Korisni linkovi</a>
			<div class="items">

			<?
			// Linkovi

			$links = $portal->getLinks(4); // skidamo najviše 4 linka - FIXME?
			// Treba omogućiti autoru portala da postavi proizvoljan broj i redoslijed linkova

			foreach ($links as $link) {
				$url = $link->url;
				$scheme = parse_url($url);
				
				if ($scheme['scheme'] == '') //only www part
					$url = 'http://' . $url;
					
				?>
				<div class="item">
				<a href="<?=$url ?>" title="<?=$link->title?>" target="_blank"><?
					
				print Util::ellipsize($link->title, 35, 5); // pošto je kocka mala, stavljamo max. 35 znakova, riječ siječemo nakon 5
				?></a>
				<span class="author"> - <?= $link->author->name . ' ' . $link->author->surname ?></span>
				<?
				if ($link->description != '') {
					?>
					<div class="desc"><?
					print Util::ellipsize($link->description, 200); // skraćujemo opis na 200 znakova
		
					?></div><!--desc-->
				<?
				}
	
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



function common_portal_linkovi($portal, $linkNaLinkove) {
	global $userid;

	require_once("Config.php");
	require_once(Config::$backend_path."core/Util.php");
	require_once(Config::$backend_path."lms/portal/Portal.php");

	$subaction = $_REQUEST['subaction'];
	$id = $_REQUEST['id']; // ID linka za edit/delete

	?>
	<h2>Korisni linkovi</h2>
	<div class="links" id="link">
	<ul class="clearfix">
		<li><a href="<?=$linkNaLinkove?>">Lista linkova</a></li>
		<li><a href="<?=$linkNaLinkove . "&subaction=add" ?>">Novi link</a></li>
	</ul>   
	</div>	
	
	<?

	if (!isset($subaction)) {
		$rowsPerPage = 20;
		$pageNum = 1;
		if (isset($_REQUEST['page'])) {
			$pageNum = $_REQUEST['page'];
		}
		// counting the offset
		$offset = ($pageNum - 1) * $rowsPerPage;
		
		//display links for this project, with links to edit and delete
		$links = $portal->getLinks($rowsPerPage, $offset);
		foreach ($links as $link) {
			if ($link->authorId == $userid || $portal->ownerId == $userid) {
				?>
				<div class="links" id="link">
				<ul class="clearfix">
					<li><a href="<?=$linkNaLinkove . "&subaction=edit&id=". $link->id ?>">Izmijeni</a></li>
					<li><a href="<?=$linkNaLinkove . "&subaction=del&id=". $link->id ?>">Obriši</a></li>
				</ul>
				</div>
				<?
			} //if user is author of this item
			?>

			<table class="linkovi" border="0" cellspacing="0" cellpadding="2">
			<tr>
				<th width="200" align="left" valign="top" scope="row">URL</th>
				<td width="490" align="left" valign="top">
				<?
					$url = $link->url;
					$scheme = parse_url($url);
					if ($scheme['scheme'] == '') //only www part	
						$url = 'http://' . $url;
				?><a href="<?=$url ?>" title="<?=$link->title ?>" target="_blank"><?=$link->title?></a>
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
			</table>
			<?

		} //foreach link

		$maxPage = ceil( $portal->getLinksCount() / $rowsPerPage );
		$self = $linkNaLinkove;
		
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
		
	} //subaction not set

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
				$pl = new PortalLink;
				$pl->title = $naziv;
				$pl->url = $url;
				$pl->description = $opis;
				$pl->authorId = $userid;
				$pl->portalId = $portal->id;
				$pl->add();

				nicemessage('Novi link uspješno dodan.');
				zamgerlog("dodao link na projektu $projekat (pp$predmet)", 2);
				nicemessage('<a href="'.$linkNaLinkove.'">Povratak.</a>');
			}

		} else {
			?>
			<h3>Novi link</h3>
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
		$link = PortalLink::fromId($id);
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
				nicemessage('Uspješno ste izmijenili link.');
				zamgerlog("uredio link na projektu $projekat (pp$predmet)", 2);
				nicemessage('<a href="'.$linkNaLinkove.'">Povratak.</a>');
			} //option == edit

		} else {
			?>
			<h3>Izmijeni link</h3>
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
		$link = PortalLink::fromId($id);
		if ($link->portalId != $portal->id) return; // da li je link sa projekta
		if ($link->authorId != $userid && $portal->ownerId != $userid) return; // da li korisnik ima prava

		//edit item
		if (isset($_REQUEST['submit']) && check_csrf_token()) {
			$link->delete();
			nicemessage('Uspješno ste obrisali link.');	
			zamgerlog("obrisao link na projektu $projekat (pp$predmet)", 2);

			nicemessage('<a href="'. $linkNaLinkove .'">Povratak.</a>');
		} else {
			?>
			<h3>Brisanje linka</h3>
			<?=genform("POST", "deleteForm"); ?>
			Da li ste sigurni da zelite obrisati ovaj link?<br />
			<input name="submit" type="submit" id="submit" value=" Obriši link "/>
			<input type="button" onclick="javascript:window.location.href = '<?=$linkNaLinkove?>';" value=" Nemoj brisati "/>
			</form>
			<?
		}
	} //subaction == del
}

?>
