<?

// NASTAVNIK/IZVJESTAJI - izvjestaji za izabrani predmet



function nastavnik_izvjestaji() {
	global $_api_http_code;
	
	// Parametri
	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);
	
	
	$course = api_call("course/$predmet/$ag");
	
	// Naziv predmeta
	$predmet_naziv = $course['courseName'];
	if (!$course['AcademicYear']['isCurrent']) $predmet_naziv .= " (" . $course['AcademicYear']['name'] . ")";
	
	// Da li korisnik ima pravo ući u modul?
	
	if ($_api_http_code == "403") {
		zamgerlog("nastavnik/grupe privilegije (predmet pp$predmet)",3);
		zamgerlog2("nije nastavnik na predmetu", $predmet, $ag);
		biguglyerror("Nemate pravo pristupa ovoj opciji");
		return;
	}

	
	// Virtualna grupa
	$allGroups = api_call("group/course/$predmet", ["year" => $ag, "includeVirtual" => true])["results"];
	$virtualna = 0;
	foreach($allGroups as $group) {
		if ($group['virtual'])
			$virtualna = $group['id'];
	}
	

	?>
	
	<p>&nbsp;</p>
	
	<p><h3><?=$predmet_naziv?> - Izvještaji</h3></p>
	
	<p><a href="?sta=izvjestaj/statistika_predmeta&predmet=<?=$predmet?>&ag=<?=$ag?>"><img src="static/images/32x32/report.png" border="0" width="32" height="32" align="center"> 1. Sumarni izvještaj za predmet</a></p>
	
	<p><img src="static/images/32x32/report.png" border="0" width="32" height="32" align="left"> 2. Spisak studenata
	<ul>
	<li><a href="?sta=izvjestaj/grupe&predmet=<?=$predmet?>&ag=<?=$ag?>&grupa=<?=$virtualna?>">Bez grupa</a></li>
	<li><a href="?sta=izvjestaj/grupe&predmet=<?=$predmet?>&ag=<?=$ag?>">Jedna kolona po grupama</a></li>
	<li><a href="?sta=izvjestaj/grupe&predmet=<?=$predmet?>&ag=<?=$ag?>&double=1">Dvije kolone (za lakše printanje)</a></li>
	<li><a href="?sta=izvjestaj/grupe&predmet=<?=$predmet?>&ag=<?=$ag?>&komentari=1">Sa komentarima na rad</a></li>
	<li><a href="?sta=izvjestaj/grupe&predmet=<?=$predmet?>&ag=<?=$ag?>&prisustvo=1&komentari=1">Sa poljima za prisustvo</a></li>
	<li><a href="?sta=izvjestaj/dodatni_podaci&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>">Dodatni podaci o studentima</a></li></ul>
	</p>
	
	<p><img src="static/images/32x32/report.png" border="0" width="32" height="32" align="left"> 3. Pregled grupa, prisustva, bodova:
	<ul><li><a href="?sta=izvjestaj/predmet&predmet=<?=$predmet?>&ag=<?=$ag?>">Puni izvještaj</a></li>
	<li><a href="?sta=izvjestaj/predmet&predmet=<?=$predmet?>&ag=<?=$ag?>&skrati=da">Sa sumiranim kolonama za prisustvo i zadaće</a></li>
	<li><a href="?sta=izvjestaj/predmet&predmet=<?=$predmet?>&ag=<?=$ag?>&skrati=da&razdvoji_ispite=da">Sa razdvojenim popravnim ispitima </a></li></ul>
	</p>
	
	<p><img src="static/images/32x32/report.png" border="0" width="32" height="32" align="left"> 4. Pregled anketa:
	<ul>
		<li><a href="?sta=izvjestaj/anketa&predmet=<?=$predmet?>&ag=<?=$ag?>&rank=da">Rank pitanja </a></li>
		<li><a href="?sta=izvjestaj/anketa&predmet=<?=$predmet?>&ag=<?=$ag?>&komentar=da">Komentari</a></li>
	
	</ul>
	</p>
	
	
	<?


}

?>
