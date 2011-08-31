<?php
// COMMON/ARTICLEIMAGEDOWNLOADZVRSNI - download slika vezanih za clanke na zavrsnim radovima
function common_articleImageDownloadZavrsni()
{
	global $userid, $user_nastavnik, $user_student, $conf_files_path, $user_siteadmin;	
	$predmet 	= intval($_REQUEST['predmet']);
	$ag		 	= intval($_REQUEST['ag']);
	$zavrsni 	= intval($_REQUEST['zavrsni']);
	$articleID   = intval($_REQUEST['a']);
	$authorID   = intval($_REQUEST['u']);
	$imageName = $_GET['i'];
		
	if ($predmet <=0 || $zavrsni <=0 || $authorID <=0 || $ag <=0 || $articleID <= 0)
	{
		//hijack attempt?
		zamgerlog("korisnik u$userid pokusao pristupiti modulu common/articleImageDownloadZavrsni sa ID predmeta  ili ID zavrsnog rada ili ID autora slike ili ag ili clanak koji nije integer ili je <=0", 3);		
		return;
	}
	
	if ($user_nastavnik && !$user_siteadmin)
	{
		$q10 = myquery("select nivo_pristupa from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
		if (mysql_num_rows($q10)<1 || mysql_result($q10,0,0)<1) {
			zamgerlog("common/zavrsniStrane privilegije (predmet pp$predmet)",3);
			biguglyerror("Nemate pravo ulaska u ovu grupu!");
			return;
		} 	
	}
	require_once("lib/zavrsni.php");
	if ($user_student && !$user_siteadmin)
	{
		$actualZavrsni = getActualZavrsniForUserInPredmet($userid, $predmet, $ag);
		if ($actualZavrsni[id] != $zavrsni)
		{
			//korisnik nije prijavljen na temu zavrsnog rada
			zamgerlog("korisnik u$userid pokusao pristupiti modulu common/articleImageDownloadZavrsni i zavrsnom radu na kojem nije prijavljen ID=$zavrsni na predmetu p$predmet", 3);				
			biguglyerror("Nemate pravo ulaska u ovu grupu!");
			return;	
		}
		
	}
	
	$imageName = strip_tags($imageName);
	$imageName = trim($imageName);
	
	$article = getArticle($articleID);
	if (empty($article) || ( $article['osoba'] != $authorID || $article['slika'] != $imageName || $article['zavrsni'] != $zavrsni ))
	{
		zamgerlog("korisnik u$userid pokusao pristupiti modulu common/articleImageDownloadZavrsni sa losim authorID, imageName, zavrsni rad ili ID clanka", 3);				
		return;
	}

	$lokacijaclanaka ="$conf_files_path/zavrsni/clanci/$zavrsni/" . $article['osoba'] . "/";
	$filepath = $lokacijaclanaka  . $article['slika'];

	$type = `file -bi '$filepath'`;

	header("Content-Type: $type");
	header('Content-Length: ' . filesize($filepath));
	
	echo file_get_contents($filepath);	
	
}


?>