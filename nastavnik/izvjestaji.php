<?

// NASTAVNIK/IZVJESTAJI - izvjestaji za izabrani predmet

// v3.9.1.0 (2008/02/18) + Preimenovan bivsi admin_predmet
// v3.9.1.1 (2008/09/18) + Konsolidovane sve vrste izvjestaj/grupe i izvjestaj/predmet
// v4.0.0.0 (2009/02/19) + Release
// v4.0.9.1 (2009/03/25) + nastavnik_predmet preusmjeren sa tabele ponudakursa na tabelu predmet
// v4.0.9.2 (2009/04/22) + Nastavnicki moduli sada primaju predmet i akademsku godinu (ag) umjesto ponudekursa


function nastavnik_izvjestaji() {

global $userid,$user_siteadmin;



// Parametri
$predmet = intval($_REQUEST['predmet']);
$ag = intval($_REQUEST['ag']);

// Naziv predmeta
$q10 = myquery("select naziv from predmet where id=$predmet");
if (mysql_num_rows($q10)<1) {
	biguglyerror("Nepoznat predmet");
	zamgerlog("ilegalan predmet $predmet",3); //nivo 3: greska
	zamgerlog2("nepoznat predmet", $predmet);
	return;
}
$predmet_naziv = mysql_result($q10,0,0);



// Da li korisnik ima pravo ući u modul?

if (!$user_siteadmin) {
	$q10 = myquery("select nivo_pristupa from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
	if (mysql_num_rows($q10)<1 || mysql_result($q10,0,0)=="asistent") {
		zamgerlog("nastavnik/ispiti privilegije (predmet pp$predmet)",3);
		zamgerlog2("nije nastavnik na predmetu", $predmet, $ag);
		biguglyerror("Nemate pravo pristupa ovoj opciji");
		return;
	} 
}


// Virtualna grupa
$q20 = myquery("select id from labgrupa where predmet=$predmet and akademska_godina=$ag and virtualna=1");
if (mysql_num_rows($q20) > 0)
	$virtualna = mysql_result($q20,0,0);
else
	$virtualna = 0;


?>

<p>&nbsp;</p>

<p><h3><?=$predmet_naziv?> - Izvještaji</h3></p>

<p><a href="?sta=izvjestaj/statistika_predmeta&predmet=<?=$predmet?>&ag=<?=$ag?>"><img src="images/32x32/izvjestaj.png" border="0" width="32" height="32" align="center"> 1. Sumarni izvještaj za predmet</a></p>

<p><img src="images/32x32/izvjestaj.png" border="0" width="32" height="32" align="left"> 2. Spisak studenata
<ul>
<li><a href="?sta=izvjestaj/grupe&predmet=<?=$predmet?>&ag=<?=$ag?>&grupa=<?=$virtualna?>">Bez grupa</a></li>
<li><a href="?sta=izvjestaj/grupe&predmet=<?=$predmet?>&ag=<?=$ag?>">Jedna kolona po grupama</a></li>
<li><a href="?sta=izvjestaj/grupe&predmet=<?=$predmet?>&ag=<?=$ag?>&double=1">Dvije kolone (za lakše printanje)</a></li>
<li><a href="?sta=izvjestaj/grupe&predmet=<?=$predmet?>&ag=<?=$ag?>&komentari=1">Sa komentarima na rad</a></li>
<li><a href="?sta=izvjestaj/grupe&predmet=<?=$predmet?>&ag=<?=$ag?>&prisustvo=1&komentari=1">Sa poljima za prisustvo</a></li></ul>
</p>

<p><img src="images/32x32/izvjestaj.png" border="0" width="32" height="32" align="left"> 3. Pregled grupa, prisustva, bodova:
<ul><li><a href="?sta=izvjestaj/predmet&predmet=<?=$predmet?>&ag=<?=$ag?>">Puni izvještaj</a></li>
<li><a href="?sta=izvjestaj/predmet&predmet=<?=$predmet?>&ag=<?=$ag?>&skrati=da">Sa sumiranim kolonama za prisustvo i zadaće</a></li>
<li><a href="?sta=izvjestaj/predmet&predmet=<?=$predmet?>&ag=<?=$ag?>&skrati=da&razdvoji_ispite=da">Sa razdvojenim popravnim ispitima </a></li></ul>
</p>

<p><img src="images/32x32/izvjestaj.png" border="0" width="32" height="32" align="left"> 4. Pregled anketa:
<ul>
	<li><a href="?sta=izvjestaj/anketa&predmet=<?=$predmet?>&ag=<?=$ag?>&rank=da">Rank pitanja </a></li>
	<li><a href="?sta=izvjestaj/anketa&predmet=<?=$predmet?>&ag=<?=$ag?>&komentar=da">Komentari</a></li>

</ul>
</p>


<?


}

?>