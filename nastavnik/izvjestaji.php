<?

// NASTAVNIK/IZVJESTAJI - izvjestaji za izabrani predmet

// v3.9.1.0 (2008/02/18) + Preimenovan bivsi admin_predmet



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
	$q10 = myquery("select np.admin from nastavnik_predmet as np where np.nastavnik=$userid and np.predmet=$predmet");
	if (mysql_num_rows($q10)<1 || mysql_result($q10,0,0)<1) {
		zamgerlog("privilegije (predmet $predmet)",3);
		biguglyerror("Nemate pravo ulaska u ovu grupu!");
		return;
	} 
}



?>

<p>&nbsp;</p>

<p><h3><?=$predmet_naziv?> - Izvještaji</h3></p>

<p><a href="?sta=izvjestaj/ispit&predmet=<?=$predmet?>&ispit=svi"><img src="images/32x32/izvjestaj.png" border="0" width="32" height="32" align="center"> 1. Sumarni izvještaj za predmet</a></p>

<p><a href="?sta=izvjestaj/grupe&double=1&predmet=<?=$predmet?>"><img src="images/32x32/izvjestaj.png" border="0" width="32" height="32" align="center"> 2. Spisak studenata po grupama</a></p>

<p><a href="?sta=izvjestaj/predmet&predmet=<?=$predmet?>&skrati=da"><img src="images/32x32/izvjestaj.png" border="0" width="32" height="32" align="center"> 3. Pregled grupa, prisustva, bodova</a></p>

<p><a href="?sta=izvjestaj/predmet&predmet=<?=$predmet?>&skrati=da&razdvoji_ispite=da"><img src="images/32x32/izvjestaj.png" border="0" width="32" height="32" align="center"> 4. Pregled grupa, prisustva, bodova sa razdvojenim popravnim ispitima</a></p>

<p><a href="?sta=izvjestaj/grupe&komentari=1&predmet=<?=$predmet?>"><img src="images/32x32/izvjestaj.png" border="0" width="32" height="32" align="center"> 5. Spisak studenata sa komentarima</a></p>


<?


}

?>