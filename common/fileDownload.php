<?php
function common_fileDownload()
{
	global $userid, $user_nastavnik, $user_student, $conf_files_path, $user_siteadmin;
	$predmet 	= intval($_REQUEST['predmet']);
	$ag		 	= intval($_REQUEST['ag']);
	$projekat 	= intval($_REQUEST['projekat']);
	$id = intval($_REQUEST['id']); //file ID
	
	if ($predmet <=0 || $projekat <=0 || $ag <=0)
	{
		//hijack attempt?
		zamgerlog("korisnik u$userid pokusao pristupiti modulu common/fileDownload sa ID predmeta  ili ID projekta ili ag koji nije integer ili je <=0", 3);		
		return;
	}
	
	
	if ($user_nastavnik && !$user_siteadmin)
	{
		$q10 = myquery("select admin from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
		if (mysql_num_rows($q10)<1 || mysql_result($q10,0,0)<1) {
			zamgerlog("common/projektneStrane privilegije (predmet pp$predmet)",3);
			biguglyerror("Nemate pravo ulaska u ovu grupu!");
			return;
		} 	
	}
	
	require_once("lib/projekti.php");


	if ($user_student && !$user_siteadmin)
	{
		$actualProject = getActualProjectForUserInPredmet($userid, $predmet);
		if ($actualProject[id] != $projekat)
		{
			//user is not in this project in this predmet...hijack attempt?
			zamgerlog("korisnik u$userid pokusao pristupiti modulu common/fileDownload i projektu na kojem nije prijavljen ID=$projekat na predmetu p$predmet", 3);				
			biguglyerror("Nemate pravo ulaska u ovu grupu!");
			return;	
		}
		
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
	
	$k = readfile($filepath,false);
	if ($k == false) 
	{
		print "Download fajla nije uspjelo! Kontaktirajte administratora";
		zamgerlog("download fajla nije uspjelo (fajl $id korisnik u$userid projekat $projekat predmet p$predmet)", 3);
	}
	exit();

}


?>