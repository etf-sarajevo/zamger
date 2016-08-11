<?php

// LIB/PROJEKTI - funkcije za module nastavnik/projekti, student/projekti, common/projektneStrane



function fetchProjects($predmet, $ag)
{
	$result = db_query("SELECT * FROM projekat WHERE predmet='$predmet' AND akademska_godina='$ag' ORDER BY naziv");
	$list = array();
	while ($row = db_fetch_assoc($result))
		$list[] = $row;	
	db_free_result($result);
	
	return $list;	
}
function getProject($id)
{
	$result = db_query("SELECT * FROM projekat WHERE id='$id' LIMIT 1");
	$list = array();
	while ($row = db_fetch_assoc($result))
		$list[] = $row;	
	db_free_result($result);
	
	return $list[0];	
}

function fetchProjectMembers($id)
{
	$result = db_query("SELECT * FROM osoba o INNER JOIN student_projekat op ON o.id=op.student WHERE op.projekat='$id' ORDER BY prezime ASC, ime ASC");
	$list = array();
	while ($row = db_fetch_assoc($result))
		$list[] = $row;	
	db_free_result($result);
	
	return $list;	
}

function applyForProject($userid, $project, $predmet, $ag)
{
	$errorText = '';
	
	if (areApplicationsLockedForPredmet($predmet, $ag))
	{
		$errorText = 'Zaključane su prijave na projekte. Prijave nisu dozvoljene.';
		zamgerlog("student u$userid pokusao da se prijavi na projekat $project koji je zaključan na predmetu p$predmet", 3);
		zamgerlog2("projekat zakljucan (pokusao da se prijavi)", $project);
		return $errorText;	
	}
	
	$teamLimitReached = isTeamLimitReachedForPredmet($predmet, $ag);
		
	$actualProjectForUser = getActualProjectForUserInPredmet($userid, $predmet, $ag);
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
	$newTeamDeny = isProjectEmpty($project) == true && $teamLimitReached == true;
	
	
	if ( $newTeamDeny == true )
	{
		$errorText = 'Limit timova dostignut. Nije moguće kreirati projektni tim. Prijavite se na drugi projekat.';
		zamgerlog("student u$userid pokusao da se prijavi na projekat $project na predmetu p$predmet iako je limit za broj timova dostignut.", 3);
		zamgerlog2("dosegnut limit za broj projekata (pokusao da se prijavi)", $predmet, $ag);
		return $errorText;	
	}
	
	if (isProjectFull($project, $predmet, $ag) == true)
	{
		$errorText = 'Projekat je popunjen. Nije moguće prijaviti se.';
		zamgerlog("student u$userid pokusao da se prijavi na projekat $project koji je popunjen na predmetu p$predmet", 3);
		zamgerlog2("projekat popunjen (pokusao da se prijavi)", $project);
		return $errorText;	
	}
	
	
	//clear person from all projects on this predmet
	$result = db_query("DELETE FROM student_projekat WHERE student='$userid' AND projekat IN (SELECT id FROM projekat WHERE predmet='$predmet' AND akademska_godina='$ag')");
	$query = sprintf("INSERT INTO student_projekat (student, projekat) VALUES ('%d', '%d')", 
					$userid,
					$project
	);
	
	$result = db_query($query);
	
	if ($result == false)
	{
		$errorText = 'Došlo je do greške prilikom spašavanja podataka. Molimo kontaktirajte administratora.';
		return $errorText;	
	}
	
	return $errorText;
}
function getOutOfProject($userid, $predmet, $ag)
{
	$errorText = '';
	
	if (areApplicationsLockedForPredmet($predmet, $ag))
	{
		$errorText = 'Zaključane su liste timova za projekte. Odustajanja nisu dozvoljena.';
		zamgerlog("student u$userid pokusao da se odjavi sa projekat predmetu p$predmet na kojem je zakljucano stanje timova i projekata", 3);		
		zamgerlog2("projekti zakljucani (pokusao da se odjavi)", $predmet, $ag);		
		return $errorText;	
	}
	
	$actualProjectForUser = getActualProjectForUserInPredmet($userid, $predmet, $ag);
	if (empty($actualProjectForUser))
	{
		$errorText = 'Došlo je do greške prilikom spašavanja podataka. Molimo kontaktirajte administratora.';
		zamgerlog("student u$userid pokusao da se odjavi sa projekta iako nije ni bio prijavljen ni na jedan projekat na predmetu p$predmet", 3);		
		zamgerlog2("nije ni na jednom projektu (odjava)", $predmet, $ag);		
		return $errorText;
	}
	

	//clear person from all projects on this predmet - actually only one project
	$result = db_query("DELETE FROM student_projekat WHERE student='$userid' AND projekat IN (SELECT id FROM projekat WHERE predmet='$predmet' AND akademska_godina='$ag')");
	
	if ($result == false)
	{
		$errorText = 'Došlo je do greške prilikom spašavanja podataka. Molimo kontaktirajte administratora.';
		return $errorText;	
	}
	
	return $errorText;
}

function generateIdFromTable($table)
{
	$result = db_query("select id from $table order by id desc limit 1");
	
	if (db_num_rows($result) == 0)
	{
		$id = 0;
	}
	else
	{	
		$id = db_fetch_row($result);
		$id = $id[0];
	}
		
	
	return intval($id+1);
}

function getPredmetParams($predmet, $ag)
{
	$result = db_query("SELECT * FROM predmet_projektni_parametri WHERE predmet='$predmet' AND akademska_godina='$ag' LIMIT 1");
	$list = array();
	while ($row = db_fetch_assoc($result))
		$list[] = $row;	
	db_free_result($result);
	
	return $list[0];	
}
function areApplicationsLockedForPredmet($predmet, $ag)
{
	
	$params = getPredmetParams($predmet, $ag);
	return $params[zakljucani_projekti] == 1;
}
function getCountProjectsForPredmet($predmet, $ag)
{
	$result = db_query("SELECT COUNT(id) FROM projekat WHERE predmet='$predmet' AND akademska_godina='$ag'");
	$row = db_fetch_row($result);
	$row = $row[0];
	
	return $row;
}	

function getCountMembersForProject($id)
{
	$result = db_query("SELECT COUNT(id) FROM osoba o INNER JOIN student_projekat op ON o.id=op.student WHERE op.projekat='$id'");
	$row = db_fetch_row($result);
	$row = $row[0];
	
	return $row;
}

function getCountEmptyProjectsForPredmet($predmet, $ag)
{
	$count = 0;
	$projects = fetchProjects($predmet, $ag);
	foreach ($projects as $project)
	{	
		$nMembers = getCountMembersForProject($project[id]);
		if ($nMembers == 0)
			$count++;	
	}
	
	return $count;
}
function getCountNONEmptyProjectsForPredmet($predmet, $ag)
{
	$count = 0;
	$projects = fetchProjects($predmet, $ag);
	foreach ($projects as $project)
	{	
		$nMembers = getCountMembersForProject($project[id]);
		if ($nMembers > 0)
			$count++;	
	}
	
	return $count;
}

function isTeamLimitReachedForPredmet($predmet, $ag)
{

	$nTeams = getCountNONEmptyProjectsForPredmet($predmet, $ag);
	$predmetParams = getPredmetParams($predmet, $ag);
	
	if ($nTeams < $predmetParams['max_timova'])
		return false;
	
	return true;
}

function isProjectEmpty($project)
{
	$nMembers = getCountMembersForProject($project);
	
	if ($nMembers == 0)
		return true;
		
	return false;
} 

function isProjectFull($project, $predmet, $ag)
{
	$predmetParams = getPredmetParams($predmet, $ag);
	$nMembers = getCountMembersForProject($project);
	
	if ($nMembers < $predmetParams[max_clanova_tima])
		return false;
		
	return true;
}

function getActualProjectForUserInPredmet($userid, $predmet, $ag)
{
	$result = db_query("SELECT p.* FROM projekat p, student_projekat op WHERE p.id=op.projekat AND op.student='$userid' AND p.predmet='$predmet' AND p.akademska_godina='$ag' LIMIT 1");
	
	$list = array();
	while ($row = db_fetch_assoc($result))
		$list[] = $row;	
	db_free_result($result);
	
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
projektneStrane
START
*********************/
function fetchLinksForProject($id, $offset, $rowsPerPage)
{
	$result = db_query("SELECT * FROM projekat_link WHERE projekat='$id' ORDER BY vrijeme DESC, naziv ASC ");
	if ($offset == 0 && $rowsPerPage == 0)
	{}
	else
		$query .="LIMIT $offset, $rowsPerPage";

	$list = array();
	while ($row = db_fetch_assoc($result))
		$list[] = $row;	
	db_free_result($result);
	
	return $list;	
}

function isUserAuthorOfLink($link, $user)
{
	$result = db_query("SELECT id FROM projekat_link WHERE osoba='$user' AND id='$link' LIMIT 1");
	if (db_num_rows($result) > 0)
		return true;
	return false;
}

function getAuthorOfLink($id)
{
	$result = db_query("SELECT o.* FROM osoba o WHERE o.id=(SELECT l.osoba FROM projekat_link l WHERE l.id='$id' LIMIT 1) LIMIT 1");
	$list = array();
	while ($row = db_fetch_assoc($result))
		$list[] = $row;	
	db_free_result($result);
	
	return $list[0];
}
function getLink($id)
{
	$result = db_query("SELECT * FROM projekat_link WHERE id='$id' LIMIT 1");
	$list = array();
	while ($row = db_fetch_assoc($result))
		$list[] = $row;	
	db_free_result($result);
	
	return $list[0];
}

function getCountLinksForProject($id)
{
	$result = db_query("SELECT COUNT(id) FROM projekat_link WHERE projekat='$id' LIMIT 1");
	$row = db_fetch_row($result);
	$row = $row[0];
	
	return $row;
}


function fetchRSSForProject($id, $offset, $rowsPerPage)
{
	$result = db_query("SELECT * FROM projekat_rss WHERE projekat='$id' ORDER BY vrijeme DESC, naziv ASC");
	if ($offset == 0 && $rowsPerPage == 0)
	{}
	else
		$query .="LIMIT $offset, $rowsPerPage";

	$list = array();
	while ($row = db_fetch_assoc($result))
		$list[] = $row;	
	db_free_result($result);
	
	return $list;	
}

function isUserAuthorOfRSS($rss, $user)
{
	$result = db_query("SELECT id FROM projekat_rss WHERE osoba='$user' AND id='$rss' LIMIT 1");
	if (db_num_rows($result) > 0)
		return true;
	return false;
}
function getAuthorOfRSS($id)
{
	$result = db_query("SELECT o.* FROM osoba o WHERE o.id=(SELECT r.osoba FROM projekat_rss r WHERE r.id='$id' LIMIT 1) LIMIT 1");
	$list = array();
	while ($row = db_fetch_assoc($result))
		$list[] = $row;	
	db_free_result($result);
	
	return $list[0];
}


function getRSS($id)
{
	$result = db_query("SELECT * FROM projekat_rss WHERE id='$id' LIMIT 1");
	$list = array();
	while ($row = db_fetch_assoc($result))
		$list[] = $row;	
	db_free_result($result);
	
	return $list[0];
}

function getCountRSSForProject($id)
{
	$result = db_query("SELECT COUNT(id) FROM projekat_rss WHERE projekat='$id' LIMIT 1");
	$row = db_fetch_row($result);
	$row = $row[0];
	
	return $row;
}

function fetchArticlesForProject($id, $offset = 0, $rowsPerPage = 0)
{
	$query = "SELECT * FROM bl_clanak WHERE projekat='$id' ORDER BY vrijeme DESC ";
	if ($offset == 0 && $rowsPerPage == 0)
	{}
	else
		$query .="LIMIT $offset, $rowsPerPage";
	
	$result = db_query($query);
	$list = array();
	while ($row = db_fetch_assoc($result))
		$list[] = $row;	
	db_free_result($result);
	
	return $list;	
}

function isUserAuthorOfArticle($article, $user)
{
	$result = db_query("SELECT id FROM bl_clanak WHERE osoba='$user' AND id='$article' LIMIT 1");
	if (db_num_rows($result) > 0)
		return true;
	return false;
}

function getArticle($id)
{
	$result = db_query("SELECT * FROM bl_clanak WHERE id='$id' LIMIT 1");
	$list = array();
	while ($row = db_fetch_assoc($result))
		$list[] = $row;	
	db_free_result($result);
	
	return $list[0];
}

function getCountArticlesForProject($id)
{
	$result = db_query("SELECT COUNT(id) FROM bl_clanak WHERE projekat='$id' LIMIT 1");
	$row = db_fetch_row($result);
	$row = $row[0];
	
	return $row;
}


function getAuthorOfArticle($id)
{
	$result = db_query("SELECT o.* FROM osoba o WHERE o.id=(SELECT b.osoba FROM bl_clanak b WHERE b.id='$id' LIMIT 1) LIMIT 1");
	$list = array();
	while ($row = db_fetch_assoc($result))
		$list[] = $row;	
	db_free_result($result);
	
	return $list[0];
}

function fetchFilesForProjectAllRevisions($id, $offset = 0, $rowsPerPage = 0)
{
	$query = "SELECT * FROM projekat_file WHERE projekat='$id' AND file=0 ORDER BY vrijeme DESC ";
	if ($offset == 0 && $rowsPerPage == 0)
	{}
	else
		$query .="LIMIT $offset, $rowsPerPage";
	
	$result = db_query($query);
	$list = array();
	while ($row = db_fetch_assoc($result))
		$list[] = $row;	
	db_free_result($result);
	
	$files = array();

	foreach ($list as $item)
	{
		$files[] = fetchAllRevisionsForFile($item[id]);	
	}
	return $files;	
}
function fetchFilesForProjectLatestRevisions($id, $offset = 0, $rowsPerPage = 0)
{
	$query = "SELECT * FROM projekat_file WHERE projekat='$id' AND file=0 ORDER BY vrijeme DESC ";
	if ($offset == 0 && $rowsPerPage == 0)
	{}
	else
		$query .="LIMIT $offset, $rowsPerPage";
	
	$result = db_query($query);
	$list = array();
	while ($row = db_fetch_assoc($result))
		$list[] = $row;	
	db_free_result($result);
	
	return $list;
}
function getAuthorOfFile($id)
{
	$result = db_query("SELECT o.* FROM osoba o WHERE o.id=(SELECT f.osoba FROM projekat_file f WHERE f.id='$id' LIMIT 1) LIMIT 1");
	$list = array();
	while ($row = db_fetch_assoc($result))
		$list[] = $row;	
	db_free_result($result);
	
	return $list[0];
}


function isUserAuthorOfFile($file, $user)
{
	$result = db_query("SELECT id FROM projekat_file WHERE osoba='$user' AND id='$file' LIMIT 1");
	if (db_num_rows($result) > 0)
		return true;
	return false;
}

function getFileFirstRevision($id)
{
	$result = db_query("SELECT * FROM projekat_file WHERE id='$id' AND revizija=1 LIMIT 1");
	$list = array();
	while ($row = db_fetch_assoc($result))
		$list[] = $row;	
	db_free_result($result);
	
	return $list[0];
}

function getFile($id)
{
	$result = db_query("SELECT * FROM projekat_file WHERE id='$id' LIMIT 1");
	$list = array();
	while ($row = db_fetch_assoc($result))
		$list[] = $row;	
	db_free_result($result);
	
	return $list[0];
}

function isThisFileFirstRevision($id)
{
	$result = db_query("SELECT id FROM projekat_file WHERE id='$id' AND revizija=1 LIMIT 1");
	if (db_num_rows($result) > 0)
		return true;
	return false;
}

function getFileLastRevision($id)
{
	$result = db_query("SELECT * FROM projekat_file WHERE file='$id' ORDER BY revizija DESC LIMIT 1");
	$list = array();
	while ($row = db_fetch_assoc($result))
		$list[] = $row;	
	db_free_result($result);
	
	if (empty($list))
	{
		//only one revision
		$list[0] = getFileFirstRevision($id);
	}
	
	return $list[0];
}
function fetchAllRevisionsForFile($id)
{
	$list = array();	
	$result = db_query("SELECT * FROM projekat_file WHERE file='$id' ORDER BY revizija DESC");
	while ($row = db_fetch_assoc($result))
		$list[] = $row;
	
	$list[] = getFileFirstRevision($id);
	return $list;	
}
function getCountFilesForProjectWithoutRevisions($id)
{
	$result = db_query("SELECT COUNT(id) FROM projekat_file WHERE projekat='$id' AND revizija=1 LIMIT 1");
	$row = db_fetch_row($result);
	$row = $row[0];
	
	return $row;
}


function fetchThreadsForProject($id, $offset = 0, $rowsPerPage = 0)
{
	$query = "SELECT t.* FROM bb_tema t WHERE t.projekat='$id' ORDER BY (SELECT p.vrijeme FROM bb_post p WHERE p.id=t.zadnji_post LIMIT 1) DESC ";
	if ($offset == 0 && $rowsPerPage == 0)
	{}
	else
		$query .="LIMIT $offset, $rowsPerPage";
	
	$result = db_query($query);
	$list = array();
	while ($row = db_fetch_assoc($result))
		$list[] = $row;	
	db_free_result($result);
	
	foreach ($list as $key => $item)
	{
		getExtendedInfoForThread($item[id], $list[$key]);
	}
	
	return $list;	
}
function getExtendedInfoForThread($id, &$list)
{
	$result = db_query("SELECT p.naslov, t.prvi_post, t.zadnji_post FROM bb_post p, bb_tema t WHERE t.id='$id' AND p.tema='$id' AND p.id=t.prvi_post LIMIT 1");
	$row = db_fetch_assoc($result);
	$list['naslov'] = $row[naslov];
	
	
	$list['broj_odgovora'] = getCountRepliesToFirstPostInThread($id);
	$list['prvi_post'] = getPostInfoForThread($id, $row[prvi_post]);
	$list['zadnji_post'] = getPostInfoForThread($id, $row[zadnji_post]);
}
function getThreadAndPosts($thread)
{
	$result = db_query("SELECT * FROM bb_tema WHERE id='$thread' LIMIT 1");
	$row = db_fetch_assoc($result);
	
	if ($row == false || db_num_rows($result) == 0)
		return array();
	
	$item = $row;
	
	getExtendedInfoForThread($thread, $item);
	
	$item[posts] = getPostsInThread($thread);
	
	return $item;
}

function getPostsInThread($id)
{
	$result = db_query("SELECT * FROM bb_post WHERE tema='$id' ORDER BY vrijeme ASC");
	$list = array();
	while ($row = db_fetch_assoc($result))
		$list[] = $row;

	foreach ($list as $key => $item)
	{		
		$result = db_query("SELECT tekst FROM bb_post_text WHERE post='$item[id]' LIMIT 1");
		$row = db_fetch_assoc($result);
		
		$list[$key]['tekst'] = $row['tekst'];
		$list[$key]['osoba'] = getOsobaInfoForPost($item[id]);		
	}
	
	return $list;
}


function getCountThreadsForProject($id)
{
	$result = db_query("SELECT COUNT(id) FROM bb_tema WHERE projekat='$id' LIMIT 1");
	$row = db_fetch_row($result);
	$row = $row[0];
	
	return $row;
}

function incrementThreadViewCount($thread)
{
	$result = db_query("UPDATE bb_tema SET pregleda=pregleda+1 WHERE id='$thread' LIMIT 1");
}


function getCountPostsInThread($id)
{
	$result = db_query("SELECT COUNT(id) FROM bb_post WHERE tema='$id' LIMIT 1");
	$row = db_fetch_row($result);
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
	$result = db_query("SELECT * FROM bb_post WHERE tema='$thread' AND id='$post' LIMIT 1");
	$list = array();
	while ($row = db_fetch_assoc($result))
		$list[] = $row;	
	db_free_result($result);
	
	$list[0][osoba] = getOsobaInfoForPost($post);
	
	return $list[0];
	
}

function getPost($id)
{
	$result = db_query("SELECT p.*, t.tekst FROM bb_post p, bb_post_text t WHERE p.id='$id' AND p.id=t.post LIMIT 1");
	$list = array();
	while ($row = db_fetch_assoc($result))
		$list[] = $row;	
	db_free_result($result);
	
	return $list[0];
}

function getOsobaInfoForPost($post)
{
	$result = db_query("SELECT o.ime, o.prezime FROM osoba o, bb_post p WHERE p.osoba=o.id  AND p.id='$post' LIMIT 1");
	$list = array();
	while ($row = db_fetch_assoc($result))
		$list[] = $row;	
	db_free_result($result);
	
	return $list[0];
}

function isUserAuthorOfPost($post, $user)
{
	$result = db_query("SELECT id FROM bb_post WHERE osoba='$user' AND id='$post' LIMIT 1");
	if (db_num_rows($result) > 0)
		return true;
	return false;
}

function fetchLatestPostsForProject($project, $limit)
{
	$result = db_query("SELECT p.*, pt.tekst FROM bb_post p, bb_post_text pt WHERE pt.post=p.id AND p.tema IN (SELECT t.id FROM bb_tema t WHERE t.projekat=$project) ORDER BY p.vrijeme DESC LIMIT 0, $limit ");
	$list = array();
	$i = 0;
	while ($row = db_fetch_assoc($result))
	{
		$list[$i] = $row;
		$list[$i][osoba] = getOsobaInfoForPost($list[$i][id]);
		$i++;
	}
	
	
	
	db_free_result($result);
	
	return $list;

}


/**********************
projektneStrane
END
**********************/


?>
