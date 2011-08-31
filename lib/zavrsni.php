<?php
// LIB/ZAVRSNI - funkcije za module nastavnik/zavrsni, student/zavrsni, common/zavrsniStrane

function fetchZavrsni($predmet, $ag)
{
	$result = myquery("SELECT * FROM zavrsni WHERE predmet='$predmet' AND akademska_godina='$ag' ORDER BY naziv");
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

function fetchZavrsniMembers($id)
{
	$result = myquery("SELECT * FROM osoba o INNER JOIN student_zavrsni oz ON o.id=oz.student WHERE oz.zavrsni='$id' ORDER BY prezime ASC, ime ASC");
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	return $list;	
}

function applyForZavrsni($userid, $zavrsni, $predmet, $ag)
{
	$errorText = '';
	
	if (areApplicationsLockedForZavrsni($predmet, $ag))
	{
		$errorText = 'Zaključane su prijave na završne. Prijave nisu dozvoljene.';
		zamgerlog("student u$userid pokusao da se prijavi na temu završnog rada $zavrsni koji je zaključan na predmetu p$predmet", 3);
		return $errorText;	
	}
	
	$LimitTemaReached = isLimitTemaReachedForPredmet($predmet, $ag);
		
	$actualZavrsniForUser = getActualZavrsniForUserInPredmet($userid, $predmet, $ag);
	if (!empty($actualZavrsniForUser))
	{
		//korisnik je vec prijavio temu na ovom predmetu
		$zMembers = getCountMembersForZavrsni($actualZavrsniForUser[id]);
		
		if ($zMembers -1 == 0)
		{
			//resetovanje broja tema
			$LimitTemaReached = false;
		}	
	}
	$newTemaDeny = isZavrsniEmpty($zavrsni) == true && $LimitTemaReached == true;
	
	
	if ( $newTemaDeny == true )
	{
		$errorText = 'Limit tema završnih radova je dostignut. Nije moguće kreirati novu temu. Prijavite se na drugu temu.';
		zamgerlog("student u$userid pokusao da se prijavi na temu završnog rada $zavrsni na predmetu p$predmet iako je limit za broj tema dostignut.", 3);
		return $errorText;	
	}
	
	if (isZavrsniFull($zavrsni, $predmet, $ag) == true)
	{
		$errorText = 'Tema završnog rada je zauzeta. Nije moguće prijaviti se.';
		zamgerlog("student u$userid pokusao da se prijavi na temu završnog rada $zavrsni koja je zauzeta na predmetu p$predmet", 3);
		return $errorText;	
	}
	
	
	//odjavljivanje/brisanje studenta sa teme zavrsnog rada
	$result = myquery("DELETE FROM student_zavrsni WHERE student='$userid' AND zavrsni IN (SELECT id FROM zavrsni WHERE predmet='$predmet' AND akademska_godina='$ag')");
	$query = sprintf("INSERT INTO student_zavrsni (student, zavrsni) VALUES ('%d', '%d')", 
					$userid,
					$zavrsni
	);
	
	$result = myquery($query);
	
	if ($result == false)
	{
		$errorText = 'Došlo je do greške prilikom spašavanja podataka. Molimo kontaktirajte administratora.';
		return $errorText;	
	}
	
	return $errorText;
}

function getOutOfZavrsni($userid, $predmet, $ag)
{
	$errorText = '';
	
	if (areApplicationsLockedForZavrsni($predmet, $ag))
	{
		$errorText = 'Zaključane su liste tema završnih radova. Odustajanja nisu dozvoljena.';
		zamgerlog("student u$userid pokusao da se odjavi sa teme završnog rada na predmetu p$predmet na kojem je zakljucano stanje tema", 3);		
		return $errorText;	
	}
	
	$actualZavrsniForUser = getActualZavrsniForUserInPredmet($userid, $predmet, $ag);
	if (empty($actualZavrsniForUser))
	{
		$errorText = 'Došlo je do greške prilikom spašavanja podataka. Molimo kontaktirajte administratora.';
		zamgerlog("student u$userid pokusao da se odjavi sa teme zavrsnog rada iako nije ni bio prijavljen ni na jednu temu na predmetu p$predmet", 3);		
		return $errorText;
	}
	

	//odjavi studenta sa odredjene teme
	$result = myquery(" FROM student_zavrsni WHERE student='$userid' AND zavrsni IN (SELECT id FROM zavrsni WHERE predmet='$predmet' AND akademska_godina='$ag')");
	
	if ($result == false)
	{
		$errorText = 'Došlo je do greške prilikom spašavanja podataka. Molimo kontaktirajte administratora.';
		return $errorText;	
	}
	
	return $errorText;
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

function getPredmetParamsForZavrsni($predmet, $ag)
{
	$result = myquery("SELECT * FROM predmet_parametri_zavrsni WHERE predmet='$predmet' AND akademska_godina='$ag' LIMIT 1");
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	return $list[0];	
}

function areApplicationsLockedForZavrsni($predmet, $ag)
{
	
	$paramsZavrsni = getPredmetParamsZavrsni($predmet, $ag);
	return $paramsZavrsni[zakljucani_zavrsni] == 1;
}

function getCountZavrsniForPredmet($predmet, $ag)
{
	$result = myquery("SELECT COUNT(id) FROM zavrsni WHERE predmet='$predmet' AND akademska_godina='$ag'");
	$row = mysql_fetch_row($result);
	$row = $row[0];
	
	return $row;
}	

function getCountMembersForZavrsni($id)
{
	$result = myquery("SELECT COUNT(id) FROM osoba o INNER JOIN student_zavrsni oz ON o.id=oz.student WHERE oz.zavrsni='$id'");
	$row = mysql_fetch_row($result);
	$row = $row[0];
	
	return $row;
}

function getCountEmptyZavrsniForPredmet($predmet, $ag)
{
	$count = 0;
	$zavrsni1 = fetchZavrsni($predmet, $ag);
	foreach ($zavrsni1 as $zavrsni)
	{	
		$zMembers = getCountMembersForZavrsni($zavrsni[id]);
		if ($zMembers == 0)
			$count++;	
	}
	
	return $count;
}

function getCountNONEmptyZavrsniForPredmet($predmet, $ag)
{
	$count = 0;
	$zavrsni1 = fetchZavrsni($predmet, $ag);
	foreach ($zavrsni1 as $zavrsni)
	{	
		$zMembers = getCountMembersForZavrsni($zavrsni[id]);
		if ($zMembers > 0)
			$count++;	
	}
	
	return $count;
}

function isLimitTemaReachedForPredmet($predmet, $ag)
{

	$zTeme = getCountNONEmptyZavrsniForPredmet($predmet, $ag);
	$predmetParamsZavrsni = getPredmetParamsZavrsni($predmet, $ag);
	
	if ($zTeme < $predmetParamsZavrsni['max_tema'])
		return false;
	
	return true;
}

function isZavrsniEmpty($zavrsni)
{
	$zMembers = getCountMembersForZavrsni($zavrsni);
	
	if ($zMembers == 0)
		return true;
		
	return false;
} 

function isZavrsniFull($zavrsni, $predmet, $ag)
{
	$predmetParamsZavrsni = getPredmetParamsZavrsni($predmet, $ag);
	$zMembers = getCountMembersForZavrsni($zavrsni);
	
	if ($zMembers < $predmetParamsZavrsni[max_clanova])
		return false;
		
	return true;
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


function rmdir_recursive($dirname)
{
    // Sanity check
    if (!file_exists($dirname)) 
	{
        return false;
    }

    // Simple delete for a file
    if (is_file($dirname) || is_link($dirname))
	{
        return unlink($dirname);
    }

    // Loop through the folder
    $dir = dir($dirname);
    while (false !== $entry = $dir->read()) 
	{
        // Skip pointers
        if ($entry == '.' || $entry == '..') 
		{
            continue;
        }

        // Recurse
        rmdir_recursive($dirname . DIRECTORY_SEPARATOR . $entry);
    }

    // Clean up
    $dir->close();
    return rmdir($dirname);
}


/**********************
zavrsniStrane
START
*********************/

function fetchLinksForZavrsni($id, $offset, $rowsPerPage)
{
	$result = myquery("SELECT * FROM zavrsni_link WHERE zavrsni='$id' ORDER BY vrijeme DESC, naziv ASC ");
	if ($offset == 0 && $rowsPerPage == 0)
	{}
	else
		$query .="LIMIT $offset, $rowsPerPage";

	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	return $list;	
}

function isUserAuthorOfLinkForZavrsni($link, $user)
{
	$result = myquery("SELECT id FROM zavrsni_link WHERE osoba='$user' AND id='$link' LIMIT 1");
	if (mysql_num_rows($result) > 0)
		return true;
	return false;
}

function getAuthorOfLinkForZavrsni($id)
{
	$result = myquery("SELECT o.* FROM osoba o WHERE o.id=(SELECT l.osoba FROM zavrsni_link l WHERE l.id='$id' LIMIT 1) LIMIT 1");
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	return $list[0];
}

function getLinkZavrsni($id)
{
	$result = myquery("SELECT * FROM zavrsni_link WHERE id='$id' LIMIT 1");
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	return $list[0];
}

function getCountLinksForZavrsni($id)
{
	$result = myquery("SELECT COUNT(id) FROM zavrsni_link WHERE zavrsni='$id' LIMIT 1");
	$row = mysql_fetch_row($result);
	$row = $row[0];
	
	return $row;
}

function fetchRSSForZavrsni($id, $offset, $rowsPerPage)
{
	$result = myquery("SELECT * FROM zavrsni_rss WHERE zavrsni='$id' ORDER BY vrijeme DESC, naziv ASC");
	if ($offset == 0 && $rowsPerPage == 0)
	{}
	else
		$query .="LIMIT $offset, $rowsPerPage";

	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	return $list;	
}

function isUserAuthorOfRSSForZavrsni($rss, $user)
{
	$result = myquery("SELECT id FROM zavrsni_rss WHERE osoba='$user' AND id='$rss' LIMIT 1");
	if (mysql_num_rows($result) > 0)
		return true;
	return false;
}

function getAuthorOfRSSForZavrsni($id)
{
	$result = myquery("SELECT o.* FROM osoba o WHERE o.id=(SELECT r.osoba FROM zavrsni_rss r WHERE r.id='$id' LIMIT 1) LIMIT 1");
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	return $list[0];
}

function getRSSZavrsni($id)
{
	$result = myquery("SELECT * FROM zavrsni_rss WHERE id='$id' LIMIT 1");
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	return $list[0];
}

function getCountRSSForZavrsni($id)
{
	$result = myquery("SELECT COUNT(id) FROM zavrsni_rss WHERE zavrsni='$id' LIMIT 1");
	$row = mysql_fetch_row($result);
	$row = $row[0];
	
	return $row;
}

function fetchArticlesForZavrsni($id, $offset = 0, $rowsPerPage = 0)
{
	$query = "SELECT * FROM bl_clanak WHERE zavrsni='$id' ORDER BY vrijeme DESC ";
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

function isUserAuthorOfArticle($article, $user)
{
	$result = myquery("SELECT id FROM bl_clanak WHERE osoba='$user' AND id='$article' LIMIT 1");
	if (mysql_num_rows($result) > 0)
		return true;
	return false;
}

function getArticle($id)
{
	$result = myquery("SELECT * FROM bl_clanak WHERE id='$id' LIMIT 1");
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	return $list[0];
}

function getCountArticlesForZavrsni($id)
{
	$result = myquery("SELECT COUNT(id) FROM bl_clanak WHERE zavrsni='$id' LIMIT 1");
	$row = mysql_fetch_row($result);
	$row = $row[0];
	
	return $row;
}


function getAuthorOfArticle($id)
{
	$result = myquery("SELECT o.* FROM osoba o WHERE o.id=(SELECT b.osoba FROM bl_clanak b WHERE b.id='$id' LIMIT 1) LIMIT 1");
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	return $list[0];
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


function fetchThreadsForZavrsni($id, $offset = 0, $rowsPerPage = 0)
{
	$query = "SELECT t.* FROM bb_tema t WHERE t.zavrsni='$id' ORDER BY (SELECT p.vrijeme FROM bb_post p WHERE p.id=t.zadnji_post LIMIT 1) DESC ";
	if ($offset == 0 && $rowsPerPage == 0)
	{}
	else
		$query .="LIMIT $offset, $rowsPerPage";
	
	$result = myquery($query);
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	foreach ($list as $key => $item)
	{
		getExtendedInfoForThread($item[id], $list[$key]);
	}
	
	return $list;	
}

function getExtendedInfoForThread($id, &$list)
{
	$result = myquery("SELECT p.naslov, t.prvi_post, t.zadnji_post FROM bb_post p, bb_tema t WHERE t.id='$id' AND p.tema='$id' AND p.id=t.prvi_post LIMIT 1");
	$row = mysql_fetch_assoc($result);
	$list['naslov'] = $row[naslov];
	
	
	$list['broj_odgovora'] = getCountRepliesToFirstPostInThread($id);
	$list['prvi_post'] = getPostInfoForThread($id, $row[prvi_post]);
	$list['zadnji_post'] = getPostInfoForThread($id, $row[zadnji_post]);
}

function getThreadAndPosts($thread)
{
	$result = myquery("SELECT * FROM bb_tema WHERE id='$thread' LIMIT 1");
	$row = mysql_fetch_assoc($result);
	
	if ($row == false || mysql_num_rows($result) == 0)
		return array();
	
	$item = $row;
	
	getExtendedInfoForThread($thread, $item);
	
	$item[posts] = getPostsInThread($thread);
	
	return $item;
}

function getPostsInThread($id)
{
	$result = myquery("SELECT * FROM bb_post WHERE tema='$id' ORDER BY vrijeme ASC");
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;

	foreach ($list as $key => $item)
	{		
		$result = myquery("SELECT tekst FROM bb_post_text WHERE post='$item[id]' LIMIT 1");
		$row = mysql_fetch_assoc($result);
		
		$list[$key]['tekst'] = $row['tekst'];
		$list[$key]['osoba'] = getOsobaInfoForPost($item[id]);		
	}
	
	return $list;
}


function getCountThreadsForZavrsni($id)
{
	$result = myquery("SELECT COUNT(id) FROM bb_tema WHERE zavrsni='$id' LIMIT 1");
	$row = mysql_fetch_row($result);
	$row = $row[0];
	
	return $row;
}

function incrementThreadViewCount($thread)
{
	$result = myquery("UPDATE bb_tema SET pregleda=pregleda+1 WHERE id='$thread' LIMIT 1");
}


function getCountPostsInThread($id)
{
	$result = myquery("SELECT COUNT(id) FROM bb_post WHERE tema='$id' LIMIT 1");
	$row = mysql_fetch_row($result);
	$row = $row[0];
	
	return $row;
}

function getCountRepliesToFirstPostInThread($id)
{
	$nPosts = getCountPostsInThread($id);
	
	if ($nPosts == 0)
		return 0;
	else
		return $nPosts - 1;
}

function getPostInfoForThread($thread, $post)
{
	$result = myquery("SELECT * FROM bb_post WHERE tema='$thread' AND id='$post' LIMIT 1");
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	$list[0][osoba] = getOsobaInfoForPost($post);
	
	return $list[0];
	
}

function getPost($id)
{
	$result = myquery("SELECT p.*, t.tekst FROM bb_post p, bb_post_text t WHERE p.id='$id' AND p.id=t.post LIMIT 1");
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	return $list[0];
}

function getOsobaInfoForPost($post)
{
	$result = myquery("SELECT o.ime, o.prezime FROM osoba o, bb_post p WHERE p.osoba=o.id  AND p.id='$post' LIMIT 1");
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	return $list[0];
}

function isUserAuthorOfPost($post, $user)
{
	$result = myquery("SELECT id FROM bb_post WHERE osoba='$user' AND id='$post' LIMIT 1");
	if (mysql_num_rows($result) > 0)
		return true;
	return false;
}

function fetchLatestPostsForZavrsni($zavrsni, $limit)
{
	$result = myquery("SELECT p.*, pt.tekst FROM bb_post p, bb_post_text pt WHERE pt.post=p.id AND p.tema IN (SELECT t.id FROM bb_tema t WHERE t.zavrsni=$zavrsni) ORDER BY p.vrijeme DESC LIMIT 0, $limit ");
	$list = array();
	$i = 0;
	while ($row = mysql_fetch_assoc($result))
	{
		$list[$i] = $row;
		$list[$i][osoba] = getOsobaInfoForPost($list[$i][id]);
		$i++;
	}
	
	mysql_free_result($result);
	
	return $list;
}


/**********************
zavrsniStrane
END
**********************/

?>