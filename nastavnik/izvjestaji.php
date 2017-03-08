<?

// NASTAVNIK/IZVJESTAJI - izvjestaji za izabrani predmet



function nastavnik_izvjestaji() {

global $userid,$user_siteadmin;



// Parametri
$predmet = intval($_REQUEST['predmet']);
$ag = intval($_REQUEST['ag']);

// Naziv predmeta
$q10 = db_query("select naziv from predmet where id=$predmet");
if (db_num_rows($q10)<1) {
	biguglyerror("Nepoznat predmet");
	zamgerlog("ilegalan predmet $predmet",3); //nivo 3: greska
	zamgerlog2("nepoznat predmet", $predmet);
	return;
}
$predmet_naziv = db_result($q10,0,0);



// Da li korisnik ima pravo ući u modul?

if (!$user_siteadmin) {
	$q10 = db_query("select nivo_pristupa from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
	if (db_num_rows($q10)<1 || db_result($q10,0,0)=="asistent") {
		zamgerlog("nastavnik/ispiti privilegije (predmet pp$predmet)",3);
		zamgerlog2("nije nastavnik na predmetu", $predmet, $ag);
		biguglyerror("Nemate pravo pristupa ovoj opciji");
		return;
	} 
}


// Virtualna grupa
$q20 = db_query("select id from labgrupa where predmet=$predmet and akademska_godina=$ag and virtualna=1");
if (db_num_rows($q20) > 0)
	$virtualna = db_result($q20,0,0);
else
	$virtualna = 0;


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
<li><a href="?sta=izvjestaj/grupe&predmet=<?=$predmet?>&ag=<?=$ag?>&prisustvo=1&komentari=1">Sa poljima za prisustvo</a></li></ul>
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