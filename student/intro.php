<?

// STUDENT/INTRO - uvodna stranica za studente

// v3.9.1.0 (2008/02/09) + Novi modul: student/intro, prikazuje informacije, obavjestenja i stablo predmeta
// v3.9.1.1 (2008/03/05) + Tweaks & optimizacije; izbaceni komentari na zadace iz poruka iz dva razloga: prespori su, neki saradnici pisu irelevantne informacije u njih
// v3.9.1.2 (2008/03/06) + Ubacen opseg 6: labgrupa, a korisnik postaje opseg 7
// v3.9.1.3 (2008/03/21) + Popravljen link za slanje zadaće u Aktuelno
// v3.9.1.4 (2008/03/26) + Fini datum u bloku Poruke
// v3.9.1.5 (2008/04/10) + Dodani rezultati ispita i konacna ocjena u Aktuelno, ukinut mysql2time() kod rokova za zadace
// v3.9.1.6 (2008/04/30) + Popravljen naslov poruke "bez naslova"; dodan link na RSS
// v3.9.1.7 (2008/08/28) + Tabela osoba umjesto auth
// v3.9.1.8 (2008/11/21) + Pod Aktuelno objavi samo rezultate ispita na koje je student izasao; prikazi bodove i cestitku ako je polozio/la; prikazi tekst Studentska sluzba za obavjestenja koje posalje studentska


function student_intro() {

global $userid, $registry;


// Dobrodošlica

$q1 = myquery("select ime from osoba where id=$userid");
$ime = mysql_result($q1,0,0);
if (spol($ime)=="Z") 
	print "<h1>Dobro došla, ".genitiv($ime,"Z")."</h1>";
else
	print "<h1>Dobro došao, ".genitiv($ime,"M")."</h1>";


// Sakrij raspored ako ga nema u registry-ju
$nasao=0;
foreach ($registry as $r) {
	if ($r[0]=="studentska/raspored") { $nasao=1; break; }
}
if ($nasao==1) {
	require_once "common/raspored.php";
	printRaspored($userid, "student");
}


// AKTUELNO

// TODO: dodati prijave ispita i druge module...

?>

<table border="0" width="100%"><tr>
	<td width="30%" valign="top" style="padding: 10px; padding-right:30px;">
		<h2><img src="images/32x32/aktuelno.png" align="absmiddle"> <font color="#666699">AKTUELNO</font></h2>
<?

$vrijeme_poruke = array();
$code_poruke = array();


// Rokovi za slanje zadaća

$q10 = myquery("select z.id, z.naziv, UNIX_TIMESTAMP(z.rok), p.naziv, pk.id, UNIX_TIMESTAMP(z.vrijemeobjave) from zadaca as z, student_predmet as sp, ponudakursa as pk, predmet as p where z.predmet=sp.predmet and sp.student=$userid and sp.predmet=pk.id and pk.predmet=p.id and z.rok>curdate() and z.aktivna=1 order by rok limit 5");
while ($r10 = mysql_fetch_row($q10)) {
	$code_poruke["z".$r10[0]] = "<b>$r10[3]:</b> Rok za slanje <a href=\"?sta=student/zadaca&zadaca=$r10[0]&predmet=$r10[4]\">zadaće ".$r10[1]."</a> je ".date("d. m. Y",$r10[2]).".<br/><br/>\n";
	$vrijeme_poruke["z".$r10[0]] = $r10[5];
}


// Objavljeni rezultati ispita

$q15 = myquery("select i.id, i.predmet, k.gui_naziv, UNIX_TIMESTAMP(i.vrijemeobjave), p.naziv, UNIX_TIMESTAMP(i.datum), io.ocjena, k.prolaz from ispitocjene as io, ispit as i, komponenta as k, ponudakursa as pk, predmet as p where io.student=$userid and io.ispit=i.id and i.komponenta=k.id and i.predmet=pk.id and pk.predmet=p.id");
while ($r15 = mysql_fetch_row($q15)) {
	if ($r15[3] < time()-60*60*24*30) continue; // preskacemo starije od mjesec dana
	if ($r15[6]>=$r15[7]) $cestitka=" Čestitamo!"; else $cestitka="";
	$code_poruke["i".$r15[0]] = "<b>$r15[4]:</b> Objavljeni rezultati ispita: <a href=\"?sta=student/predmet&predmet=$r15[1]\">$r15[2] (".date("d. m. Y",$r15[5]).")</a>. Dobili ste $r15[6] bodova.$cestitka<br/><br/>\n";
	$vrijeme_poruke["i".$r15[0]] = $r15[3];
}


$q17 = myquery("select ko.predmet, ko.ocjena, UNIX_TIMESTAMP(ko.datum), p.naziv from konacna_ocjena as ko, student_predmet as sp, ponudakursa as pk, predmet as p where ko.student=$userid and sp.student=$userid and sp.predmet=ko.predmet and sp.predmet=pk.id and pk.predmet=p.id");
while ($r17 = mysql_fetch_row($q17)) {
	if ($r17[2] < time()-60*60*24*30) continue; // preskacemo starije od mjesec dana
	$code_poruke["k".$r17[0]] = "<b>$r17[3]:</b> Čestitamo! <a href=\"?sta=student/predmet&predmet=$r17[0]\">Dobili ste $r17[1]</a><br/><br/>\n";
	$vrijeme_poruke["k".$r17[0]] = $r17[2];
}



// Sortiramo po vremenu
arsort($vrijeme_poruke);
$count=0;
foreach ($vrijeme_poruke as $id=>$vrijeme) {
	print $code_poruke[$id];
	$count++;
	if ($count==5) break; // prikazujemo 5 poruka
}
if ($count==0) {
	print "Nema aktuelnih informacija.";
}

print $vijesti;





// OBAVJEŠTENJA

?>
</td>

<td width="30%" valign="top" style="padding: 10px; padding-right:30px;" bgcolor="#f2f2f2">
				<h2><img src="images/32x32/info.png" align="absmiddle"> <font color="#666699">OBAVJEŠTENJA</font></h2>
<?

// TODO: optimizacija

// LEGENDA tabele poruke
// Tip:
//    1 - obavjestenja
//    2 - lične poruke
// Opseg:
//    0 - svi korisnici Zamgera
//    1 - svi studenti
//    2 - svi nastavnici
//    3 - svi studenti na studiju (primalac - id studija)
//    4 - svi studenti na godini (primalac - id akademske godine)
//    5 - svi studenti na predmetu (primalac - id predmeta)
//    6 - korisnik (primalac - user id)

// Zadnja akademska godina
$q20 = myquery("select id,naziv from akademska_godina order by id desc limit 1");
$ag = mysql_result($q20,0,0);

// Studij koji student trenutno sluša
$studij=0;
$q30 = myquery("select studij,semestar from student_studij where student=$userid and akademska_godina=$ag order by semestar desc limit 1");
if (mysql_num_rows($q30)>0) {
	$studij = mysql_result($q30,0,0);
}

$q40 = myquery("select id, UNIX_TIMESTAMP(vrijeme), opseg, primalac, naslov, tekst, posiljalac from poruka where tip=1 order by vrijeme desc");
$printed=0;
while ($r40 = mysql_fetch_row($q40)) {
	$opseg = $r40[2];
	$primalac = $r40[3];
	$posiljalac = $r40[6];
	if ($opseg == 2 || $opseg==3 && $primalac!=$studij || $opseg==4 && $primalac!=$ag ||  $opseg==7 && $primalac!=$userid)
		continue;
	if ($opseg==5) {
		// odredjujemo naziv predmeta i da li ga student slusa
		$q50 = myquery("select p.naziv from student_predmet as sp, ponudakursa as pk, predmet as p where sp.student=$userid and sp.predmet=$primalac and pk.id=$primalac and pk.predmet=p.id");
		if (mysql_num_rows($q50)<1) continue;
		$posiljalac = mysql_result($q50,0,0);
	} else if ($opseg==6) {
		// odredjujemo naziv predmeta za labgrupu i da li je student u grupi
		$q55 = myquery("select p.naziv from student_labgrupa as sl, labgrupa as l, ponudakursa as pk, predmet as p where sl.student=$userid and sl.labgrupa=l.id and l.id=$primalac and l.predmet=pk.id and pk.predmet=p.id");
		if (mysql_num_rows($q55)<1) continue;
		$posiljalac = mysql_result($q55,0,0);

	} else {
		// Obavještenja u drugim opsezima može slati samo site admin ili studentska služba
		$q56 = myquery("select count(*) from privilegije where osoba=$posiljalac and privilegija='siteadmin'");
		if (mysql_result($q60,0,0)>0) 
			$posiljalac = "Administrator";
		else {
			$q57 = myquery("select count(*) from privilegije where osoba=$posiljalac and privilegija='studentska'");
			if (mysql_result($q60,0,0)>0) 
				$posiljalac = "Studentska služba";
			else
				$posiljalac = "Neko iz mase";
		}

	}
	
	?>
	<b><?=$posiljalac?></b> (<?=date("d.m",$r40[1])?>)<br/>
	<?=$r40[4]?><?
	if (strlen($r40[5])>0) print " (<a href=\"?sta=common/inbox&poruka=$r40[0]\">Dalje...</a>)";
	?><br/><br/>
	<?
	$printed=1;
}
if ($printed==0)
	print "Nema novih obavještenja.";




// PORUKE (izvadak iz inboxa)

?></td>

<td width="30%" valign="top" style="padding: 10px;">
<h2><img src="images/32x32/poruke.png" align="absmiddle"> <font color="#666699">PORUKE</font></h2><?

$vrijeme_poruke = array();
$code_poruke = array();

$q100 = myquery("select id, UNIX_TIMESTAMP(vrijeme), opseg, primalac, naslov from poruka where tip=2 order by vrijeme desc");
while ($r100 = mysql_fetch_row($q100)) {
	$id = $r100[0];
	$opseg = $r100[2];
	$primalac = $r100[3];
	if ($opseg == 2 || $opseg==3 && $primalac!=$studij || $opseg==4 && $primalac!=$ag ||  $opseg==7 && $primalac!=$userid)
		continue;
	if ($opseg==5) {
		// odredjujemo da li student slusa predmet
		$q110 = myquery("select count(*) from student_predmet where student=$userid and predmet=$primalac");
		if (mysql_result($q110,0,0)<1) continue;
	}
	if ($opseg==6) {
		// da li je student u labgrupi?
		$q115 = myquery("select count(*) from student_labgrupa where student=$userid and labgrupa=$primalac");
		if (mysql_result($q115,0,0)<1) continue;
	}
	$vrijeme_poruke[$id]=$r100[1];

	// Fino vrijeme
	$vr = $vrijeme_poruke[$id];
	$vrijeme="";
	if (date("d.m.Y",$vr)==date("d.m.Y")) $vrijeme = "<i>danas</i> ";
	else if (date("d.m.Y",$vr+3600*24)==date("d.m.Y")) $vrijeme = "<i>juče</i> ";
	else $vrijeme .= date("d.m. ",$vr);
	$vrijeme .= date("H:i",$vr);

	$naslov = $r100[4];
	if (strlen($naslov)>30) $naslov = substr($naslov,0,28)."...";
	if (!preg_match("/\S/",$naslov)) $naslov = "[Bez naslova]";

	$code_poruke[$id]="<li><a href=\"?sta=common/inbox&poruka=$id\">$naslov</a><br/>($vrijeme)</li>\n";
}

/* 

PRESPORO :(

// Da pokusamo ubaciti komentare na zadaće u ovo....

$q120 = myquery("select z.id,z.naziv,z.zadataka,z.predmet,p.kratki_naziv from zadaca as z,student_predmet as sp, ponudakursa as pk, predmet as p where sp.student=$userid and sp.predmet=z.predmet and sp.predmet=pk.id and pk.predmet=p.id");
while ($r120 = mysql_fetch_row($q120)) {
	for ($i=1; $i<=$r120[2]; $i++) {
		$q130 = myquery("select id,UNIX_TIMESTAMP(vrijeme),komentar from zadatak where student=$userid and zadaca=$r120[0] and redni_broj=$i order by id desc limit 1");
		if (mysql_num_rows($q130)<1 || strlen(mysql_result($q130,0,2))<1)
			continue;
		$id = mysql_result($q130,0,0);
		$vrijeme_poruke[$id]=mysql_result($q130,0,1);
		$naslov = "Komentar na Zadatak $i, $r120[1] ($r120[4])";
		if (strlen($naslov)>32) $naslov = substr($naslov,0,30)."...";
		$code_poruke[$id]="<li><a href=\"?sta=student/zadaca&predmet=$r120[3]&zadaca=$r120[0]&zadatak=$i\">$naslov</a><br/>(".date("d.m h:i",$vrijeme_poruke[$id]).")</li>\n";
	}
}*/

// Sortiramo po vremenu
arsort($vrijeme_poruke);
$count=0;
foreach ($vrijeme_poruke as $id=>$vrijeme) {
	print $code_poruke[$id];
	$count++;
	if ($count==5) break; // prikazujemo 5 poruka
}
if ($count==0) {
	print "<li>Nemate nijednu poruku.</li>\n";
}


?>
</td>

</tr>
</table>

<br/><br/>



<?

// RSS ID

srand(time());
$q200 = myquery("select id from rss where auth=$userid");
if (mysql_num_rows($q200)<1) {
	// kreiramo novi ID
	do {
		$rssid="";
		for ($i=0; $i<10; $i++) {
			$slovo = rand()%62;
			if ($slovo<10) $sslovo=$slovo;
			else if ($slovo<36) $sslovo=chr(ord('a')+$slovo-10);
			else $sslovo=chr(ord('A')+$slovo-36);
			$rssid .= $sslovo;
		}
		$q210 = myquery("select count(*) from rss where id='$rssid'");
	} while (mysql_result($q210,0,0)>0);
	$q220 = myquery("insert into rss set id='$rssid', auth=$userid");
} else {
	$rssid = mysql_result($q200,0,0);
}


?>
<a href="http://zamger.etf.unsa.ba/rss.php?id=<?=$rssid?>"><img src="images/32x32/rss.png" width="32" height="32" border="0" align="center"> <big>RSS Feed - automatsko obavještenje o novostima!</big></a>

<!--
<table border="0" bgcolor="#DDDDDD" width="100%">
<tr><td colspan="4" bgcolor="#CCCCCC" align="center" valign="center" style="font-size: medium"><b>Sa drugih sajtova...</b></td></tr>
<tr>

<td width="25%" valign="top" style="padding: 10px; padding-right:30px;" bgcolor="#FFFFFF">
<b>ETF.UNSA.BA:</b><br/>
* yadayada<br/>
* blah<br/>
* whocares<br/>
* Excel format
</td>

<td width="25%" valign="top" style="padding: 10px; padding-right:30px;" bgcolor="#FFFFFF">
<b>ra15070@etf.unsa.ba:</b><br/>
* prepisana zadaća<br/>
* još jedna prepisana zadaća<br/>
* pa radil iko išta sam<br/>
* Excel format
</td>


<td width="25%" valign="top" style="padding: 10px; padding-right:30px;" bgcolor="#FFFFFF">
<b>ETF.BA:</b><br/>
* Steleks se ugasio<br/>
* Teo proglašen za doživotnog počasnog studenta<br/>
* šta ja znam
</td>


<td width="25%" valign="top" style="padding: 10px; padding-right:30px;" bgcolor="#FFFFFF">
&nbsp; <!--FILLER--><!--
</td>
</tr></table>
-->
			
	<?
}

?>
