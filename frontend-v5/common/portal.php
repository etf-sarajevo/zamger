<?

// COMMON/PORTAL - portal koji se može prikazati po potrebi na željenoj lokaciji


function common_portal() {
	require_once("Config.php");
	
	// Backend stuff
	require_once(Config::$backend_path."core/Portfolio.php");
	require_once(Config::$backend_path."core/Util.php");
	
	require_once(Config::$backend_path."lms/portal/Portal.php");

	// Međutim ovo bi trebalo biti opcionalno

	
	//debug mod aktivan
	global $userid;
	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);
	$portalId = intval($_REQUEST['portalId']);
	$sekcija = $_REQUEST['sekcija'];
	if (!isset($sekcija)) $sekcija="portal";

	// Za sada pristup portalu samo iz ovih modula
	// Dok ne implementiramo prava pristupa na nivou portala
	if ($_REQUEST['sta'] != "student/projekti" && $_REQUEST['sta'] != "nastavnik/projekti") {
		niceerror("Pristup za sada zabranjen");
		return;
	}
	
	// Konfiguracija portala - prebaciti u tabelu portal i koristiti fromId metodu
	$portal = new Portal;
	$portal->id = $portalId;
	$portal->layout = array(array("forum","articles"), array("links", "rss", "files"));
	$portal->menu = array("portal", "projectinfo", "links", "rss", "articles", "files", "forum");

	// FIXME! ovaj kod koristim čisto da bih dobio ime projekta
	require_once(Config::$backend_path."lms/projects/Project.php");
	$projekat = intval($_REQUEST['projekat']); // FIXME Kupimo varijablu projekat koja je data modulima */projekti
	$project = Project::fromId($projekat);
	$portal->name = $project->name;
	
	// linkPrefix treba da vodi na početnu stranicu - FIXME i ovo :)
	$linkPrefix = "?sta=" . $_REQUEST['sta'] . "&akcija=" . $_REQUEST['akcija'] . "&predmet=$predmet&ag=$ag&projekat=$projekat";



	// Prikaz menija

	?>
	<h2><?=$portal->name?></h2>
	<div class="links">
		<ul class="clearfix">
	<?
	foreach ($portal->menu as $menuOption) {
		if ($menuOption == $portal->menu[count($portal->menu)-1]) $last='class="last"';
		?>
		<li <?=$last?>><a href="<?=$linkPrefix . "&sekcija=" . $menuOption ?>"><?=Portal::$menuTitles[$menuOption] ?></a></li>
		<?
	}
	?>
		</ul>
	</div>
	<?	



	// Prikaz sekcija

	// Portal
	if ($sekcija == "portal") {
		?>
		<div id="mainWrapper" class="clearfix">
		<?
		$lijevo=true;

		foreach ($portal->layout as $lijevo_desno) {
			// Zaglavlje lijevo odnosno desno
			if ($lijevo) {
				?>
				<div id="leftBlocks">
				<?
				$lijevo = false;
			} else {
				?>
				</div><!--leftBlocks-->
				<div id="rightBlocks" class="clearfix">
				<?
			}

			// Portal moduli
			foreach ($lijevo_desno as $modul) {
				if ($modul == "forum") {
					require("common/forum.php");
					common_forum_ukratko($portalId, $linkPrefix . "&sekcija=forum");
				}
				if ($modul == "articles") {
					require("common/portal_clanci.php");
					common_portal_clanci_ukratko($portal, $linkPrefix . "&sekcija=articles");
				}
				if ($modul == "links") {
					require("common/portal_linkovi.php");
					common_portal_linkovi_ukratko($portal, $linkPrefix . "&sekcija=links");
				}
				if ($modul == "rss") {
					require("common/portal_rss.php");
					common_portal_rss_ukratko($portal, $linkPrefix . "&sekcija=rss");
				}
				if ($modul == "files") {
					require("common/portal_fajlovi.php");
					common_portal_fajlovi_ukratko($portalId, $linkPrefix . "&sekcija=files");
				}
			}
		}
	} // kraj sekcije portal


	// Informacije o projektu
	// Nadam se da ćemo ovu sekciju ukinuti u potpunosti jer je redundantna 
	// (studenti i nastavnici svakako vide informacije o projektu na prethodnom linku)
	// Umjesto toga staviti wiki!
	else if ($sekcija == "projectinfo") {
		$members = $project->getMembers();

		// display project info
		?>
		<h2>Informacije o projektu</h2>

		<table class="projekti" border="0" cellspacing="0" cellpadding="2">
		<tr>
			<th width="200" align="left" valign="top" scope="row">Naziv</th>
			<td width="490" align="left" valign="top"><?=$project->name?></td>
		</tr>
		<tr>
			<th width="200" align="left" valign="top" scope="row">Prijavljeni studenti</th>
			<td width="490" align="left" valign="top">
		<?
		if (empty($members))
			echo 'Nema prijavljenih studenata.';
		else {
			?>
			<ul>
			<?
			foreach ($members as $member) {
				?>
				<li><?=$member->surname . ' ' . $member->name . ', ' . $member->studentIdNr; ?></li>
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
			<td width="490" align="left" valign="top"><?=$project->description ?></td>
		</tr>
		</table>
		<?
	} //section -- projectinfo


	// Sekcija: linkovi
	elseif ($sekcija == 'links') {
		require("common/portal_linkovi.php");
		common_portal_linkovi($portal, $linkPrefix . "&sekcija=links");
	} //section == links

	elseif ($sekcija == 'rss') {
		require("common/portal_rss.php");
		common_portal_rss($portal, $linkPrefix . "&sekcija=rss");
	} //section == rss

	elseif ($sekcija == 'articles') {
		require("common/portal_clanci.php");
		common_portal_clanci($portal, $linkPrefix . "&sekcija=articles");
	} //section == bl (blackboard)


	elseif ($sekcija == 'files') {
		require("common/portal_fajlovi.php");
		common_portal_fajlovi($portalId, $linkPrefix . "&sekcija=files");
	} //section == file

	elseif ($sekcija == 'forum') {
		require("common/forum.php");
		common_forum($portalId, $linkPrefix . "&sekcija=forum");
	} //section == bb (forum)

} //function




?>