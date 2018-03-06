<?

// STUDENT/INTRO - uvodna stranica za studente



function student_intro() {

global $userid, $registry;

require_once("lib/utility.php"); // spol, vokativ


// Dobrodošlica

$q1 = db_query("select ime, spol from osoba where id=$userid");
$ime = db_result($q1,0,0);
$spol = db_result($q1,0,1);
if ($spol == 'Z' || ($spol == '' && spol($ime)=="Z"))
	print "<h1>Dobro došla, ".vokativ($ime,"Z")."</h1>";
else
	print "<h1>Dobro došao, ".vokativ($ime,"M")."</h1>";


// Sakrij module ako ih nema u registry-ju
$modul_raspored=$modul_anketa=0;
foreach ($registry as $r) {
	if (count($r) == 0) continue;
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
		<h2><img src="static/images/32x32/latest.png" align="absmiddle"> <font color="#666699">AKTUELNO</font></h2>
<?

$vrijeme_poruke = array();
$code_poruke = array();


// Rokovi za slanje zadaća

$q10 = db_query("select z.id, z.naziv, UNIX_TIMESTAMP(z.rok), p.naziv, pk.id, UNIX_TIMESTAMP(z.vrijemeobjave), p.id, pk.akademska_godina from zadaca as z, student_predmet as sp, ponudakursa as pk, predmet as p where z.predmet=pk.predmet and z.akademska_godina=pk.akademska_godina and z.rok>curdate() and sp.predmet=pk.id and sp.student=$userid and pk.predmet=p.id and z.aktivna=1 order by rok limit 5");
while ($r10 = db_fetch_row($q10)) {
	// Da li je aktivan modul za zadaće?
	$q12 = db_query("select count(*) from studentski_modul as sm, studentski_modul_predmet as smp where sm.modul='student/zadaca' and sm.id=smp.studentski_modul and smp.predmet=$r10[6] and smp.akademska_godina=$r10[7]");
	if (db_result($q12,0,0)==0) continue;

	$code_poruke["z".$r10[0]] = "<b>$r10[3]:</b> Rok za slanje <a href=\"?sta=student/zadaca&zadaca=$r10[0]&predmet=$r10[6]&ag=$r10[7]\">zadaće ".$r10[1]."</a> je ".date("d. m. Y. \u H:i",$r10[2]).".<br/><br/>\n";
	$vrijeme_poruke["z".$r10[0]] = $r10[5];
}


// Objavljeni rezultati ispita

$q15 = db_query("select i.id, pk.id, k.gui_naziv, UNIX_TIMESTAMP(i.vrijemeobjave), p.naziv, UNIX_TIMESTAMP(i.datum), true, k.prolaz, p.id, pk.akademska_godina from ispit as i, komponenta as k, ponudakursa as pk, predmet as p, student_predmet as sp where i.komponenta=k.id and i.predmet=pk.predmet and i.akademska_godina=pk.akademska_godina and pk.predmet=p.id and sp.student=$userid and sp.predmet=pk.id");
while ($r15 = db_fetch_row($q15)) {
	if ($r15[3] < time()-60*60*24*30) continue; // preskačemo starije od mjesec dana

	// Da li je student položio predmet? Preskačemo ako jeste
	$q15a = db_query("select count(*) from konacna_ocjena where predmet=$r15[8] and ocjena>=6 and student=$userid");
	if (db_result($q15a,0,0)>0) continue;

	// Da li je ovaj student izlazio na ispit?
	$q16 = db_query("select ocjena from ispitocjene where ispit=$r15[0] and student=$userid");
	if (db_num_rows($q16)==0) { // Ne
		// Ima li termina na koje se može prijaviti?
		$q17 = db_query("select count(*) from ispit_termin where ispit=$r15[0] and datumvrijeme>=NOW()");
		if (db_result($q17,0,0)>0) {
			$code_poruke["i".$r15[0]] = "<b>$r15[4]:</b> Objavljeni termini za ispit $r15[2]. <a href=\"?sta=student/prijava_ispita&predmet=$r15[8]&ag=$r15[9]\">Prijavite se!</a><br /><br />\n";
			$vrijeme_poruke["i".$r15[0]] = $r15[3];
		}
	}
	else { // Student je dobio $bodova
		$bodova = db_result($q16,0,0);
		if ($bodova >= $r15[7] && $r15[7] > 0) $cestitka=" Čestitamo!"; else $cestitka="";
		$code_poruke["i".$r15[0]] = "<b>$r15[4]:</b> Objavljeni rezultati ispita: <a href=\"?sta=student/predmet&predmet=$r15[8]&ag=$r15[9]\">$r15[2] (".date("d. m. Y",$r15[5]).")</a>. Dobili ste $bodova bodova.$cestitka<br /><br />\n";
		$vrijeme_poruke["i".$r15[0]] = $r15[3];
	}
}

// Konačne ocjene

$q17 = db_query("select pk.id, ko.ocjena, UNIX_TIMESTAMP(ko.datum), p.naziv, p.id, pk.akademska_godina from konacna_ocjena as ko, student_predmet as sp, ponudakursa as pk, predmet as p where ko.student=$userid and sp.student=$userid and ko.predmet=p.id and ko.akademska_godina=pk.akademska_godina and sp.predmet=pk.id and pk.predmet=p.id and ko.ocjena>5");
while ($r17 = db_fetch_row($q17)) {
	if ($r17[2] < time()-60*60*24*30) continue; // preskacemo starije od mjesec dana
	$code_poruke["k".$r17[0]] = "<b>$r17[3]:</b> Čestitamo! <a href=\"?sta=student/predmet&predmet=$r17[4]&ag=$r17[5]\">Dobili ste $r17[1]</a><br /><br />\n";
	$vrijeme_poruke["k".$r17[0]] = $r17[2];
}

// Anketa
// Ima li ovo smisla? Ako natrpamo 5 poruka u obavjestenja, nece se nista drugo prikazati :(
/*if ($modul_anketa) {
	$q19a = db_query("select pk.id, p.naziv, p.id, pk.akademska_godina from student_predmet as sp, ponudakursa as pk, predmet as p where  sp.student=$userid and  sp.predmet=pk.id and pk.predmet=p.id");
	$q19b = db_query("select UNIX_TIMESTAMP(datum_otvaranja) from anketa_anketa where aktivna = 1");

	// provjeravamo da li postoji aktivna anketa
	if (db_num_rows($q19b)!= 0) {
		$q19b_vrijeme=db_result($q19b,0,0);
		
		while ($r19 = db_fetch_row($q19a)) {
			if ($q19b_vrijeme < time()-60*60*24*30) continue; // preskacemo starije od mjesec dana
			$code_poruke["l".$r19[0]] = "<b>$r19[1]:</b><a href=\"?sta=student/anketa&predmet=$r19[2]\"> Molimo ispunite anketu. </a> <br/><br/>\n";
			$vrijeme_poruke["l".$r19[0]] = $q19b_vrijeme;
		}
	}
}*/


// Kvizovi
$q18 = db_query("select k.id, k.naziv, UNIX_TIMESTAMP(k.vrijeme_pocetak), k.labgrupa, k.predmet, k.akademska_godina, p.naziv from kviz as k, student_predmet as sp, ponudakursa as pk, predmet as p where sp.student=$userid and sp.predmet=pk.id and pk.predmet=k.predmet and pk.predmet=p.id and pk.akademska_godina=k.akademska_godina and k.vrijeme_pocetak<NOW() and k.vrijeme_kraj>NOW() and k.aktivan=1");
while ($r18 = db_fetch_row($q18)) {
	$labgrupa = $r18[3];
	$predmet = $r18[4];
	$ag = $r18[5];

	if ($labgrupa > 0) { // definisana je labgrupa
		$nasao = false;
		$q19 = db_query("select sl.labgrupa from student_labgrupa as sl, labgrupa as l where sl.student=$userid and sl.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag and l.virtualna=0");
		while ($r19 = db_fetch_row($q19)) {
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





// OBAVJEŠTENJA

?>
</td>

<td width="30%" valign="top" style="padding: 10px; padding-right:30px;" bgcolor="#f2f2f2">
				<h2><img src="static/images/32x32/info.png" align="absmiddle"> <font color="#666699">OBAVJEŠTENJA</font></h2>
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
$q20 = db_query("select id,naziv from akademska_godina where aktuelna=1 order by id desc limit 1");
$ag = db_result($q20,0,0);
$ag_naziv = db_result($q20,0,1);

// Studij koji student trenutno sluša
$studij=0;
$q30 = db_query("select ss.studij,ss.semestar,ts.ciklus from student_studij as ss, studij as s, tipstudija as ts where ss.student=$userid and ss.akademska_godina=$ag and ss.studij=s.id and s.tipstudija=ts.id order by ss.semestar desc limit 1");
if (db_num_rows($q30)>0) {
	$studij   = db_result($q30,0,0);
	$semestar = db_result($q30,0,1);
	$ciklus   = db_result($q30,0,2);
	$godina_studija = intval(($semestar+1)/2);
}

$q40 = db_query("select id, UNIX_TIMESTAMP(vrijeme), opseg, primalac, naslov, tekst, posiljalac from poruka where tip=1 order by vrijeme desc");
$printed=0;
while ($r40 = db_fetch_row($q40)) {
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
		$q50 = db_query("select p.naziv from student_predmet as sp, ponudakursa as pk, predmet as p where sp.student=$userid and sp.predmet=pk.id and pk.predmet=$primalac and pk.akademska_godina=$ag and pk.predmet=p.id");
		if (db_num_rows($q50)<1) continue;
		$posiljalac = db_result($q50,0,0);
	} else if ($opseg==6) {
		// odredjujemo naziv predmeta za labgrupu i da li je student u grupi
		$q55 = db_query("select p.naziv from student_labgrupa as sl, labgrupa as l, predmet as p where sl.student=$userid and sl.labgrupa=l.id and l.id=$primalac and l.predmet=p.id");
		if (db_num_rows($q55)<1) continue;
		$posiljalac = db_result($q55,0,0);

	} else {
		// Obavještenja u drugim opsezima može slati samo site admin ili studentska služba
		$q56 = db_query("select count(*) from privilegije where osoba=$posiljalac and privilegija='siteadmin'");
		if (db_result($q56,0,0)>0) 
			$posiljalac = "Administrator";
		else {
			$q57 = db_query("select count(*) from privilegije where osoba=$posiljalac and privilegija='studentska'");
			if (db_result($q57,0,0)>0) 
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
<h2><img src="static/images/32x32/messages.png" align="absmiddle"> <font color="#666699">PORUKE</font></h2><?

$vrijeme_poruke = array();
$code_poruke = array();

$q100 = db_query("select id, UNIX_TIMESTAMP(vrijeme), opseg, primalac, naslov from poruka where tip=2 order by vrijeme desc limit 1000");
while ($r100 = db_fetch_row($q100)) {
	$id = $r100[0];
	$opseg = $r100[2];
	$primalac = $r100[3];
	if ($opseg == 2 || $opseg==3 && $primalac!=$studij || $opseg==4 && $primalac!=$ag ||  $opseg==7 && $primalac!=$userid)
		continue;
	if ($opseg==5) {
		// odredjujemo da li je student ikada slusao predmet (FIXME?)
		$q110 = db_query("select count(*) from student_predmet as sp, ponudakursa as pk where sp.student=$userid and sp.predmet=pk.id and pk.predmet=$primalac");
		if (db_result($q110,0,0)<1) continue;
	}
	if ($opseg==6) {
		// da li je student u labgrupi?
		$q115 = db_query("select count(*) from student_labgrupa where student=$userid and labgrupa=$primalac");
		if (db_result($q115,0,0)<1) continue;
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

$q120 = db_query("select z.id,z.naziv,z.zadataka,z.predmet,p.kratki_naziv from zadaca as z,student_predmet as sp, ponudakursa as pk, predmet as p where sp.student=$userid and sp.predmet=z.predmet and sp.predmet=pk.id and pk.predmet=p.id");
while ($r120 = db_fetch_row($q120)) {
	for ($i=1; $i<=$r120[2]; $i++) {
		$q130 = db_query("select id,UNIX_TIMESTAMP(vrijeme),komentar from zadatak where student=$userid and zadaca=$r120[0] and redni_broj=$i order by id desc limit 1");
		if (db_num_rows($q130)<1 || strlen(db_result($q130,0,2))<1)
			continue;
		$id = db_result($q130,0,0);
		$vrijeme_poruke[$id]=db_result($q130,0,1);
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
$q200 = db_query("select id from rss where auth=$userid");
if (db_num_rows($q200)<1) {
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
		$q210 = db_query("select count(*) from rss where id='$rssid'");
	} while (db_result($q210,0,0)>0);
	$q220 = db_query("insert into rss set id='$rssid', auth=$userid");
} else {
	$rssid = db_result($q200,0,0);
}


global $conf_site_url;

?>
<a href="<?=$conf_site_url?>/rss.php?id=<?=$rssid?>"><img src="static/images/32x32/rss.png" width="32" height="32" border="0" align="center"> <big>RSS Feed - automatsko obavještenje o novostima!</big></a>
<a href="http://feedvalidator.org/check.cgi?url=http%3A//zamger.etf.unsa.ba/rss.php%3Fid%3D<?=$rssid?>"><img src="static/images/valid-rss-rogers.png" width="88" height="31" border="0" align="center"></a>

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
