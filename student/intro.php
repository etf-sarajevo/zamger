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
// v3.9.1.9 (2008/12/24) + Popravljen bug sa prikazom posiljaoca obavjestenja Administrator i Studentska sluzba
// v4.0.0.0 (2009/02/19) + Release
// v4.0.9.1 (2009/03/31) + Tabela ispit preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.2 (2009/03/31) + Tabela konacna_ocjena preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.3 (2009/04/01) + Tabela zadaca preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.4 (2009/04/06) + Skracujem prikaz obavjestenja ako je "naslov" duzi od 200 znakova
// v4.0.9.5 (2009/04/19) + Popravljen upit za obavjestenja o polozenim ispitima - posto je moguce da isti predmet ima vise ponudakursa, desavalo se da link na stranicu predmeta bude za pogresnu ponudukursa na koju student nije upisan
// v4.0.9.6 (2009/04/29) + Prebacujem tabelu poruka (opseg 5) sa ponudekursa na predmet (neki studenti ce mozda dobiti dvije identicne poruke)
// v4.0.9.7 (2009/04/29) + Preusmjeravam tabelu labgrupa sa tabele ponudakursa na tabelu predmet
// v4.0.9.8 (2009/05/01) + Parametri modula student/predmet i student/zadaca su sada predmet i ag


function student_intro() {

global $userid, $registry;


// Dobrodošlica

$q1 = myquery("select ime, spol from osoba where id=$userid");
$ime = mysql_result($q1,0,0);
$spol = mysql_result($q1,0,1);
if ($spol == 'Z' || ($spol == '' && spol($ime)=="Z"))
	print "<h1>Dobro došla, ".vokativ($ime,"Z")."</h1>";
else
	print "<h1>Dobro došao, ".vokativ($ime,"M")."</h1>";


// Sakrij module ako ih nema u registry-ju
$modul_raspored=$modul_anketa=0;
foreach ($registry as $r) {
	if ($r[0]=="common/raspored1") $modul_raspored=1;
	if ($r[0]=="student/anketa") $modul_anketa=1;
}

// Prikazujem raspored
if ($modul_raspored==1) {
	require "common/raspored1.php";
	common_raspored1("student");
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

$q10 = myquery("select z.id, z.naziv, UNIX_TIMESTAMP(z.rok), p.naziv, pk.id, UNIX_TIMESTAMP(z.vrijemeobjave), p.id, pk.akademska_godina from zadaca as z, student_predmet as sp, ponudakursa as pk, predmet as p where z.predmet=pk.predmet and z.akademska_godina=pk.akademska_godina and z.rok>curdate() and sp.predmet=pk.id and sp.student=$userid and pk.predmet=p.id and z.aktivna=1 order by rok limit 5");
while ($r10 = mysql_fetch_row($q10)) {
	// Da li je aktivan modul za zadaće?
	$q12 = myquery("select count(*) from studentski_modul as sm, studentski_modul_predmet as smp where sm.modul='student/zadaca' and sm.id=smp.studentski_modul and smp.predmet=$r10[6] and smp.akademska_godina=$r10[7]");
	if (mysql_result($q12,0,0)==0) continue;

	$code_poruke["z".$r10[0]] = "<b>$r10[3]:</b> Rok za slanje <a href=\"?sta=student/zadaca&zadaca=$r10[0]&predmet=$r10[6]&ag=$r10[7]\">zadaće ".$r10[1]."</a> je ".date("d. m. Y. \u H:i",$r10[2]).".<br/><br/>\n";
	$vrijeme_poruke["z".$r10[0]] = $r10[5];
}


// Objavljeni rezultati ispita

$q15 = myquery("select i.id, pk.id, k.gui_naziv, UNIX_TIMESTAMP(i.vrijemeobjave), p.naziv, UNIX_TIMESTAMP(i.datum), true, k.prolaz, p.id, pk.akademska_godina from ispit as i, komponenta as k, ponudakursa as pk, predmet as p, student_predmet as sp where i.komponenta=k.id and i.predmet=pk.predmet and i.akademska_godina=pk.akademska_godina and pk.predmet=p.id and sp.student=$userid and sp.predmet=pk.id");
while ($r15 = mysql_fetch_row($q15)) {
	if ($r15[3] < time()-60*60*24*30) continue; // preskačemo starije od mjesec dana

	// Da li je student položio predmet? Preskačemo ako jeste
	$q15a = myquery("select count(*) from konacna_ocjena where predmet=$r15[8] and ocjena>=6 and student=$userid");
	if (mysql_result($q15a,0,0)>0) continue;

	// Da li je ovaj student izlazio na ispit?
	$q16 = myquery("select ocjena from ispitocjene where ispit=$r15[0] and student=$userid");
	if (mysql_num_rows($q16)==0) { // Ne
		// Ima li termina na koje se može prijaviti?
		$q17 = myquery("select count(*) from ispit_termin where ispit=$r15[0] and datumvrijeme>=NOW()");
		if (mysql_result($q17,0,0)>0) {
			$code_poruke["i".$r15[0]] = "<b>$r15[4]:</b> Objavljeni termini za ispit $r15[2]. <a href=\"?sta=student/prijava_ispita&predmet=$r15[8]&ag=$r15[9]\">Prijavite se!</a><br /><br />\n";
			$vrijeme_poruke["i".$r15[0]] = $r15[3];
		}
	}
	else { // Student je dobio $bodova
		$bodova = mysql_result($q16,0,0);
		if ($bodova>=$r15[7]) $cestitka=" Čestitamo!"; else $cestitka="";
		$code_poruke["i".$r15[0]] = "<b>$r15[4]:</b> Objavljeni rezultati ispita: <a href=\"?sta=student/predmet&predmet=$r15[8]&ag=$r15[9]\">$r15[2] (".date("d. m. Y",$r15[5]).")</a>. Dobili ste $bodova bodova.$cestitka<br /><br />\n";
		$vrijeme_poruke["i".$r15[0]] = $r15[3];
	}
}

// Konačne ocjene

$q17 = myquery("select pk.id, ko.ocjena, UNIX_TIMESTAMP(ko.datum), p.naziv, p.id, pk.akademska_godina from konacna_ocjena as ko, student_predmet as sp, ponudakursa as pk, predmet as p where ko.student=$userid and sp.student=$userid and ko.predmet=p.id and ko.akademska_godina=pk.akademska_godina and sp.predmet=pk.id and pk.predmet=p.id and ko.ocjena>5");
while ($r17 = mysql_fetch_row($q17)) {
	if ($r17[2] < time()-60*60*24*30) continue; // preskacemo starije od mjesec dana
	$code_poruke["k".$r17[0]] = "<b>$r17[3]:</b> Čestitamo! <a href=\"?sta=student/predmet&predmet=$r17[4]&ag=$r17[5]\">Dobili ste $r17[1]</a><br /><br />\n";
	$vrijeme_poruke["k".$r17[0]] = $r17[2];
}

// Anketa
// Ima li ovo smisla? Ako natrpamo 5 poruka u obavjestenja, nece se nista drugo prikazati :(
/*if ($modul_anketa) {
	$q19a = myquery("select pk.id, p.naziv, p.id, pk.akademska_godina from student_predmet as sp, ponudakursa as pk, predmet as p where  sp.student=$userid and  sp.predmet=pk.id and pk.predmet=p.id");
	$q19b = myquery("select UNIX_TIMESTAMP(datum_otvaranja) from anketa_anketa where aktivna = 1");

	// provjeravamo da li postoji aktivna anketa
	if (mysql_num_rows($q19b)!= 0) {
		$q19b_vrijeme=mysql_result($q19b,0,0);
		
		while ($r19 = mysql_fetch_row($q19a)) {
			if ($q19b_vrijeme < time()-60*60*24*30) continue; // preskacemo starije od mjesec dana
			$code_poruke["l".$r19[0]] = "<b>$r19[1]:</b><a href=\"?sta=student/anketa&predmet=$r19[2]\"> Molimo ispunite anketu. </a> <br/><br/>\n";
			$vrijeme_poruke["l".$r19[0]] = $q19b_vrijeme;
		}
	}
}*/


// Kvizovi
$q18 = myquery("select k.id, k.naziv, UNIX_TIMESTAMP(k.vrijeme_pocetak), k.labgrupa, k.predmet, k.akademska_godina, p.naziv from kviz as k, student_predmet as sp, ponudakursa as pk, predmet as p where sp.student=$userid and sp.predmet=pk.id and pk.predmet=k.predmet and pk.predmet=p.id and pk.akademska_godina=k.akademska_godina and k.vrijeme_pocetak<NOW() and k.vrijeme_kraj>NOW() and k.aktivan=1");
while ($r18 = mysql_fetch_row($q18)) {
	$labgrupa = $r18[3];
	$predmet = $r18[4];
	$ag = $r18[5];

	if ($labgrupa > 0) { // definisana je labgrupa
		$nasao = false;
		$q19 = myquery("select sl.labgrupa from student_labgrupa as sl, labgrupa as l where sl.student=$userid and sl.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag and l.virtualna=0");
		while ($r19 = mysql_fetch_row($q19)) {
			if ($r19[0] == $labgrupa) $nasao = true;
		}
		if (!$nasao) continue; // nije ta labgrupa
	}
	
	$code_poruke["kv".$r18[0]] = "<b>$r18[6]:</b> Otvoren je kviz <a href=\"?sta=student/kviz&predmet=$predmet&ag=$ag\">$r18[1]</a><br/><br/>\n";
	$vrijeme_poruke["kv".$r18[0]] = $r18[2];
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
//    6 - svi studenti na labgrupi (primalac - id labgrupe)
//    7 - korisnik (primalac - user id)
//    8 - svi studenti na godini studija (primalac - idstudija*10+godina_studija)

// Zadnja akademska godina
$q20 = myquery("select id,naziv from akademska_godina where aktuelna=1 order by id desc limit 1");
$ag = mysql_result($q20,0,0);
$ag_naziv = mysql_result($q20,0,1);

// Studij koji student trenutno sluša
$studij=0;
$q30 = myquery("select ss.studij,ss.semestar,ts.ciklus from student_studij as ss, studij as s, tipstudija as ts where ss.student=$userid and ss.akademska_godina=$ag and ss.studij=s.id and s.tipstudija=ts.id order by ss.semestar desc limit 1");
if (mysql_num_rows($q30)>0) {
	$studij   = mysql_result($q30,0,0);
	$semestar = mysql_result($q30,0,1);
	$ciklus   = mysql_result($q30,0,2);
	$godina_studija = intval(($semestar+1)/2);
}

$q40 = myquery("select id, UNIX_TIMESTAMP(vrijeme), opseg, primalac, naslov, tekst, posiljalac from poruka where tip=1 order by vrijeme desc");
$printed=0;
while ($r40 = mysql_fetch_row($q40)) {
	if (time() - $r40[1] > 60*60*24*365) continue;
	$opseg = $r40[2];
	$primalac = $r40[3];
	$posiljalac = $r40[6];
	if ($opseg == 2 || $opseg==3 && $primalac!=$studij && $primalac!=-$ciklus || $opseg==4 && $primalac!=$ag ||  $opseg==7 && $primalac!=$userid || $opseg==8 && $primalac!= ($studij*10+$godina_studija) && $primalac!= (-$ciklus*10-$godina_studija))
		continue;
	if ($opseg==5) {
		// Poruke od starih akademskih godina nisu relevantne
		if ($r40[1]<mktime(0,0,0,9,1,intval($ag_naziv))) continue;

		// odredjujemo naziv predmeta i da li ga student slusa
		$q50 = myquery("select p.naziv from student_predmet as sp, ponudakursa as pk, predmet as p where sp.student=$userid and sp.predmet=pk.id and pk.predmet=$primalac and pk.akademska_godina=$ag and pk.predmet=p.id");
		if (mysql_num_rows($q50)<1) continue;
		$posiljalac = mysql_result($q50,0,0);
	} else if ($opseg==6) {
		// odredjujemo naziv predmeta za labgrupu i da li je student u grupi
		$q55 = myquery("select p.naziv from student_labgrupa as sl, labgrupa as l, predmet as p where sl.student=$userid and sl.labgrupa=l.id and l.id=$primalac and l.predmet=p.id");
		if (mysql_num_rows($q55)<1) continue;
		$posiljalac = mysql_result($q55,0,0);

	} else {
		// Obavještenja u drugim opsezima može slati samo site admin ili studentska služba
		$q56 = myquery("select count(*) from privilegije where osoba=$posiljalac and privilegija='siteadmin'");
		if (mysql_result($q56,0,0)>0) 
			$posiljalac = "Administrator";
		else {
			$q57 = myquery("select count(*) from privilegije where osoba=$posiljalac and privilegija='studentska'");
			if (mysql_result($q57,0,0)>0) 
				$posiljalac = "Studentska služba";
			else
				$posiljalac = "Neko iz mase";
		}

	}
	
	// Ako je tekst obavještenja prevelik, skraćujemo
	$tekst = $r40[4];
	$skracen=false;
	if (strlen($tekst)>200) {
		$pos = strpos($tekst," ",200);
		if ($pos>220) $pos=220;
		$tekst = substr($tekst,0,$pos)."...";
		$skracen=true;
	}

	?>
	<b><?=$posiljalac?></b> (<?=date("d.m",$r40[1])?>)<br/>
	<?=$tekst?><?
	if (strlen($r40[5])>0 || $skracen) print " (<a href=\"?sta=common/inbox&poruka=$r40[0]\">Dalje...</a>)";
	?><br/><br/>
	<?
	$printed++;
	// Maksimalno 5 obavjestenja
	if ($printed>=5) break;
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
		// odredjujemo da li je student ikada slusao predmet (FIXME?)
		$q110 = myquery("select count(*) from student_predmet as sp, ponudakursa as pk where sp.student=$userid and sp.predmet=pk.id and pk.predmet=$primalac");
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
<a href="http://feedvalidator.org/check.cgi?url=http%3A//zamger.etf.unsa.ba/rss.php%3Fid%3D<?=$rssid?>"><img src="images/valid-rss-rogers.png" width="88" height="31" border="0" align="center"></a>

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
