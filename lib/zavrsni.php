<<<<<<< .mine
<?php
// LIB/ZAVRSNI - funkcije za module nastavnik/zavrsni, student/zavrsni, common/zavrsniStrane, studentska/zavrsni

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
		zamgerlog("student u$userid pokušao da se prijavi na temu završnog rada $zavrsni koji je zaključan na predmetu p$predmet", 3);
		return $errorText;	
	}
	
	$LimitTemaReached = isLimitTemaReachedForPredmet($predmet, $ag);
		
	$actualZavrsniForUser = getActualZavrsniForUserInPredmet($userid, $predmet, $ag);
	if (!empty($actualZavrsniForUser))
	{
		//korisnik je već prijavio temu završnog rada
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
		zamgerlog("student u$userid pokušao da se prijavi na temu završnog rada $zavrsni na predmetu p$predmet iako je limit za broj tema dostignut.", 3);
		return $errorText;	
	}
	
	if (isZavrsniFull($zavrsni, $predmet, $ag) == true)
	{
		$errorText = 'Tema završnog rada je zauzeta. Nije moguće prijaviti se.';
		zamgerlog("student u$userid pokušao da se prijavi na temu završnog rada $zavrsni koja je zauzeta na predmetu p$predmet", 3);
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

//odjava sa teme završnog rada
function getOutOfZavrsni($userid, $predmet, $ag)
{
	$errorText = '';
	
	if (areApplicationsLockedForZavrsni($predmet, $ag))
	{
		$errorText = 'Zaključane su liste tema završnih radova. Odustajanja nisu dozvoljena.';
		zamgerlog("student u$userid pokušao da se odjavi sa teme završnog rada na predmetu p$predmet na kojem je zaključano stanje tema", 3);		
		return $errorText;	
	}
	
	$actualZavrsniForUser = getActualZavrsniForUserInPredmet($userid, $predmet, $ag);
	if (empty($actualZavrsniForUser))
	{
		$errorText = 'Došlo je do greške prilikom spašavanja podataka. Molimo kontaktirajte administratora.';
		zamgerlog("student u$userid pokušao da se odjavi sa teme završnog rada iako nije ni bio prijavljen ni na jednu temu na predmetu p$predmet", 3);		
		return $errorText;
	}
	

	//odjavi studenta sa određene teme
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

/**********************
zavrsniStrane
END
**********************/

?>
=======
<?php
// LIB/ZAVRSNI - funkcije za module nastavnik/zavrsni, student/zavrsni, common/zavrsniStrane, studentska/zavrsni

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
		zamgerlog("student u$userid pokušao da se prijavi na temu završnog rada $zavrsni koji je zaključan na predmetu p$predmet", 3);
		return $errorText;	
	}
	
	$LimitTemaReached = isLimitTemaReachedForPredmet($predmet, $ag);
		
	$actualZavrsniForUser = getActualZavrsniForUserInPredmet($userid, $predmet, $ag);
	if (!empty($actualZavrsniForUser))
	{
		//korisnik je već prijavio temu završnog rada
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
		zamgerlog("student u$userid pokušao da se prijavi na temu završnog rada $zavrsni na predmetu p$predmet iako je limit za broj tema dostignut.", 3);
		return $errorText;	
	}
	
	if (isZavrsniFull($zavrsni, $predmet, $ag) == true)
	{
		$errorText = 'Tema završnog rada je zauzeta. Nije moguće prijaviti se.';
		zamgerlog("student u$userid pokušao da se prijavi na temu završnog rada $zavrsni koja je zauzeta na predmetu p$predmet", 3);
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

//odjava sa teme završnog rada
function getOutOfZavrsni($userid, $predmet, $ag)
{
	$errorText = '';
	
	if (areApplicationsLockedForZavrsni($predmet, $ag))
	{
		$errorText = 'Zaključane su liste tema završnih radova. Odustajanja nisu dozvoljena.';
		zamgerlog("student u$userid pokušao da se odjavi sa teme završnog rada na predmetu p$predmet na kojem je zaključano stanje tema", 3);		
		return $errorText;	
	}
	
	$actualZavrsniForUser = getActualZavrsniForUserInPredmet($userid, $predmet, $ag);
	if (empty($actualZavrsniForUser))
	{
		$errorText = 'Došlo je do greške prilikom spašavanja podataka. Molimo kontaktirajte administratora.';
		zamgerlog("student u$userid pokušao da se odjavi sa teme završnog rada iako nije ni bio prijavljen ni na jednu temu na predmetu p$predmet", 3);		
		return $errorText;
	}
	

	//odjavi studenta sa određene teme
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

/**********************
zavrsniStrane
END
**********************/

?>>>>>>>> .r1243
