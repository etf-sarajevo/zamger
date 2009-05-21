<?php
function common_fileDownload()
{
	global $conf_debug, $userid, $user_nastavnik, $user_student, $conf_files_path, $user_siteadmin;
	$predmet 	= intval($_REQUEST['predmet']);
	$projekat 	= intval($_REQUEST['projekat']);
	$id = intval($_REQUEST['id']); //file ID
	
	if ($predmet <=0 || $projekat <=0)
	{
		//hijack attempt?
		zamgerlog("korisnik u$userid pokusao pristupiti modulu common/fileDownload sa ID predmeta  ili ID projekta koji nije integer ili je <=0", 3);		
		return;
	}
	
	//bad userid
	if (!is_numeric($userid) || $userid <=0)
	{
		zamgerlog("korisnik sa losim ID koji nije integer ili je <=0 pokusao pristupiti modulu common/fileDownload na predmetu p$predmet", 3);				
		return;	
	}


	require_once("lib/projekti.php");
	
	if ($user_student && !$user_siteadmin)
	{
		$actualProject = getActualProjectForUserInPredmet($userid, $predmet);
		if ($actualProject[id] != $projekat)
		{
			//user is not in this project in this predmet...hijack attempt?
			zamgerlog("korisnik u$userid pokusao pristupiti modulu common/fileDownload i projektu na kojem nije prijavljen ID=$projekat na predmetu p$predmet", 3);				
			return;	
		}
		
	}
	
	$entry = getFile($id);
	if (empty($entry))
	{
		zamgerlog("fajl sa ID=$id ne postoji u bazi (common/fileDownload, u$userid projekat $projekat predmet p$predmet)");
		return;
	}
	
	$lokacijafajlova ="$conf_files_path/projekti/fajlovi/$projekat/" . $entry[osoba] . "/" . $entry[filename] . '/v' . $entry[revizija] . '/';
	$filepath = $lokacijafajlova . $entry[filename];
	$type = `file -bi '$filepath'`;
	header("Content-Type: $type");
	header('Content-Disposition: attachment; filename=' . $entry[filename], false);
	
	$k = readfile($filepath,false);
	if ($k == false) 
	{
		print "Download fajla nije uspjelo! Kontaktirajte administratora";
		zamgerlog("download fajla nije uspjelo (fajl $id korisnik u#userid projekat $projekat predmet p$predmet)", 3);
	}
	exit();

	
	
	
	



}


?>