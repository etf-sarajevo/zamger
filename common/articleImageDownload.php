<?php
// COMMON/ARTICLEIMAGEDOWNLOAD - download slika vezanih za clanke na projektima
function common_articleImageDownload()
{
	global $userid, $user_nastavnik, $user_student, $conf_files_path, $user_siteadmin;	
	$predmet 	= intval($_REQUEST['predmet']);
	$ag		 	= intval($_REQUEST['ag']);
	$projekat 	= intval($_REQUEST['projekat']);
	$articleID   = intval($_REQUEST['a']);
	$authorID   = intval($_REQUEST['u']);
	$imageName = $_GET['i'];
		
	if ($predmet <=0 || $projekat <=0 || $authorID <=0 || $ag <=0 || $articleID <= 0)
	{
		//hijack attempt?
		zamgerlog("korisnik u$userid pokusao pristupiti modulu common/articleImageDownload sa ID predmeta  ili ID projekta ili ID autora slike ili ag ili clanak koji nije integer ili je <=0", 3);
		zamgerlog2("neispravni parametri", $predmet, $ag, $projekat, "$authorID, $articleID");
		return;
	}
	
	if ($user_nastavnik && !$user_siteadmin)
	{
		$q10 = myquery("select nivo_pristupa from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
		if (mysql_num_rows($q10)<1 || mysql_result($q10,0,0)<1) {
			zamgerlog("common/projektneStrane privilegije (predmet pp$predmet)",3);
			zamgerlog2("nije saradnik na predmetu", $predmet, $ag);
			biguglyerror("Nemate pravo ulaska u ovu grupu!");
			return;
		}
	}
	require_once("lib/projekti.php");
	if ($user_student && !$user_siteadmin)
	{
		$actualProject = getActualProjectForUserInPredmet($userid, $predmet, $ag);
		if ($actualProject[id] != $projekat)
		{
			//user is not in this project in this predmet...hijack attempt?
			zamgerlog("korisnik u$userid pokusao pristupiti modulu common/articleImageDownload i projektu na kojem nije prijavljen ID=$projekat na predmetu p$predmet", 3);
			zamgerlog2("nije na projektu", $projekat);
			biguglyerror("Nemate pravo ulaska u ovu grupu!");
			return;	
		}
		
	}
	
	$imageName = strip_tags($imageName);
	$imageName = trim($imageName);
	
	$article = getArticle($articleID);
	if (empty($article) || ( $article['osoba'] != $authorID || $article['slika'] != $imageName || $article['projekat'] != $projekat ))
	{
		zamgerlog("korisnik u$userid pokusao pristupiti modulu common/articleImageDownload sa losim authorID, imageName, projekat ili ID clanka", 3);
		zamgerlog2("clanak se ne poklapa sa projektom", $articleID, $projekat);
		return;
	}

	$lokacijaclanaka ="$conf_files_path/projekti/clanci/$projekat/" . $article['osoba'] . "/";
	$filepath = $lokacijaclanaka  . $article['slika'];

	$type = `file -bi '$filepath'`;

	header("Content-Type: $type");
	header('Content-Length: ' . filesize($filepath));
	
	echo file_get_contents($filepath);	
	
}


?>
