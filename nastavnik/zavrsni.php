<?php
// NASTAVNIK/ZAVRSNI 

function nastavnik_zavrsni() {

	global $userid, $user_nastavnik, $user_siteadmin;
	global $conf_files_path;

	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);

	// Da li korisnik ima pravo ući u modul?
	if (!$user_siteadmin) 
	{
		$q10 = myquery("select nivo_pristupa from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
		if (mysql_num_rows($q10)<1 || mysql_result($q10,0,0)=="asistent") {
			zamgerlog("nastavnik/završni privilegije (predmet pp$predmet)",3);
			biguglyerror("Nemate pravo pristupa ovoj opciji");
			return;
		}
	}

	$linkPrefix = "?sta=nastavnik/zavrsni&predmet=$predmet&ag=$ag";
	$akcija = $_REQUEST['akcija'];
	$id = intval($_REQUEST['id']);
	
	?>
	<LINK href="css/zavrsni.css" rel="stylesheet" type="text/css">
	<h2>Završni rad</h2>
	<?
	
	if ($akcija == 'zavrsni_stranica') {
		require_once ('common/zavrsniStrane.php');
		common_projektneStrane();
	} //akcija == projektna_stranica


} // function
?>
