<?php
// COMMON/FILEDOWNLOAD - download fajlova na projektima
function common_fileDownload()
{
	require_once("lib/projekti.php");

	global $userid, $user_nastavnik, $user_student, $conf_files_path, $user_siteadmin;
	$predmet 	= intval($_REQUEST['predmet']);
	$ag		 	= intval($_REQUEST['ag']);
	$projekat 	= intval($_REQUEST['projekat']);
	$id = intval($_REQUEST['id']); //file ID
	
	$q10 = myquery("select admin from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
	$actualProject = getActualProjectForUserInPredmet($userid, $predmet, $ag);
	if ((mysql_num_rows($q10)<1 || mysql_result($q10,0,0)<1) && ($actualProject[id] != $projekat)) {
		zamgerlog("nije ni student ni nastavnik (projekat $projekat, predmet pp$predmet)",3);
		biguglyerror("Nemate pravo ulaska u ovu grupu!");
		return;
	}
	
	$entry = getFile($id);
	if (empty($entry))
	{
		zamgerlog("fajl sa ID=$id ne postoji u bazi (common/fileDownload, u$userid projekat $projekat predmet p$predmet)",3);
		return;
	}
	
	$lokacijafajlova ="$conf_files_path/projekti/fajlovi/$projekat/" . $entry[osoba] . "/" . $entry[filename] . '/v' . $entry[revizija] . '/';
	$filepath = $lokacijafajlova . $entry[filename];
	$type = `file -bi '$filepath'`;
	header("Content-Type: $type");
	header("Content-Disposition: attachment; filename=\"" . $entry[filename] . "\"", false);

	// workaround za http://support.microsoft.com/kb/316431 (zamger bug 94)
	header("Pragma: dummy=bogus"); 
	header("Cache-Control: private");

	$k = readfile($filepath,false);
	if ($k == false) 
	{
		print "Download fajla nije uspjelo! Kontaktirajte administratora";
		zamgerlog("download fajla nije uspjelo (fajl $id korisnik u$userid projekat $projekat predmet p$predmet)", 3);
	}
	exit();

}


?>