<?

// NASTAVNIK/IZVJESTAJI - izvjestaji za izabrani predmet

// v3.9.1.0 (2008/02/18) + Preimenovan bivsi admin_predmet
// v3.9.1.1 (2008/09/18) + Konsolidovane sve vrste izvjestaj/grupe i izvjestaj/predmet
// v4.0.0.0 (2009/02/19) + Release
// v4.0.9.1 (2009/03/25) + nastavnik_predmet preusmjeren sa tabele ponudakursa na tabelu predmet


function nastavnik_izvjestaji() {

global $userid,$user_siteadmin;



$predmet=intval($_REQUEST['predmet']);
if ($predmet==0) { 
	zamgerlog("ilegalan predmet $predmet",3); //nivo 3: greska
	biguglyerror("Nije izabran predmet."); 
	return; 
}

$q1 = myquery("select p.naziv from predmet as p, ponudakursa as pk where pk.id=$predmet and pk.predmet=p.id");
$predmet_naziv = mysql_result($q1,0,0);

//$tab=$_REQUEST['tab'];
//if ($tab=="") $tab="Opcije";

//logthis("Admin Predmet $predmet - tab $tab");



// Da li korisnik ima pravo ući u modul?

if (!$user_siteadmin) { // 3 = site admin
	$q10 = myquery("select np.admin from nastavnik_predmet as np, ponudakursa as pk where np.nastavnik=$userid and np.predmet=pk.predmet and np.akademska_godina=pk.akademska_godina and pk.id=$predmet");
	if (mysql_num_rows($q10)<1 || mysql_result($q10,0,0)<1) {
		zamgerlog("nastavnik/izvjestaji privilegije (predmet p$predmet)",3);
		biguglyerror("Nemate pravo ulaska u ovu grupu!");
		return;
	} 
}



?>

<p>&nbsp;</p>

<p><h3><?=$predmet_naziv?> - Izvještaji</h3></p>

<p><a href="?sta=izvjestaj/ispit&predmet=<?=$predmet?>&ispit=svi"><img src="images/32x32/izvjestaj.png" border="0" width="32" height="32" align="center"> 1. Sumarni izvještaj za predmet</a></p>

<p><img src="images/32x32/izvjestaj.png" border="0" width="32" height="32" align="left"> 2. Spisak studenata po grupama:
<ul><li><a href="?sta=izvjestaj/grupe&predmet=<?=$predmet?>">Jedna kolona</a></li>
<li><a href="?sta=izvjestaj/grupe&predmet=<?=$predmet?>&double=1">Dvije kolone (za lakše printanje)</a></li>
<li><a href="?sta=izvjestaj/grupe&predmet=<?=$predmet?>&komentari=1">Sa komentarima na rad</a></li>
<li><a href="?sta=izvjestaj/grupe&predmet=<?=$predmet?>&prisustvo=1&komentari=1">Sa poljima za prisustvo</a></li></ul>
</p>

<p><img src="images/32x32/izvjestaj.png" border="0" width="32" height="32" align="left"> 3. Pregled grupa, prisustva, bodova:
<ul><li><a href="?sta=izvjestaj/predmet&predmet=<?=$predmet?>">Puni izvještaj</a></li>
<li><a href="?sta=izvjestaj/predmet&predmet=<?=$predmet?>&skrati=da">Sa sumiranim kolonama za prisustvo i zadaće</a></li>
<li><a href="?sta=izvjestaj/predmet&predmet=<?=$predmet?>&skrati=da&razdvoji_ispite=da">Sa razdvojenim popravnim ispitima</li><ul>
</p>


<?


}

?>