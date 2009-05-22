<?php
function common_articleImageDownload()
{
	global $conf_debug, $userid, $user_nastavnik, $user_student, $conf_files_path, $user_siteadmin;	
	$predmet 	= intval($_REQUEST['predmet']);
	$projekat 	= intval($_REQUEST['projekat']);
	$authorID   = intval($_REQUEST['u']);
	$imageName = $_GET['i'];
		
	if ($predmet <=0 || $projekat <=0 || $authorID <=0)
	{
		//hijack attempt?
		zamgerlog("korisnik u$userid pokusao pristupiti modulu common/articleImageDownload sa ID predmeta  ili ID projekta ili ID autora slike koji nije integer ili je <=0", 3);		
		return;
	}
	
	//bad userid
	if (!is_numeric($userid) || $userid <=0)
	{
		zamgerlog("korisnik sa losim ID koji nije integer ili je <=0 pokusao pristupiti modulu common/articleImageDownload na predmetu p$predmet", 3);				
		return;	
	}
	
	
	$imageName = strip_tags($imageName);
	$imageName = trim($imageName);
	
	if (empty($imageName))
	{
		zamgerlog("korisnik u$userid pokusao pristupiti modulu common/articleImageDownload sa praznim nazivom slike", 3);				
		return;
	}
	
	
	$lokacijaclanaka ="$conf_files_path/projekti/clanci/$projekat/" . $authorID . "/";
	$filepath = $lokacijaclanaka  . $imageName;
	
	//$type = `file -bi '$filepath'`;
	
	$type = 'application/octet-stream';
	header("Content-Type: $type");
	header('Content-Length: ' . filesize($filepath));
	
	echo file_get_contents($filepath);	
}


?>