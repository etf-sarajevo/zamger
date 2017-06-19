<?

// RSS - feed za studente


$broj_poruka = 10;


require("lib/config.php");
require("lib/dblayer.php");
require("lib/zamger.php");
require("lib/legacy.php");

db_connect($conf_dbhost,$conf_dbuser,$conf_dbpass,$conf_dbdb);

// Parametri potrebni za Moodle integraciju
global $conf_moodle, $conf_moodle_url, $conf_moodle_db, $conf_moodle_prefix, $conf_moodle_reuse_connection, $conf_moodle_dbhost, $conf_moodle_dbuser, $conf_moodle_dbpass;
global $__db_connection, $conf_use_mysql_utf8;


if ($_REQUEST['id'] == "svi") {
	$id = "svi";
	$userid = 0;
	$ime = "Svi"; $prezime = "Korisnici";
	$q1_8 = db_query("SELECT id FROM akademska_godina WHERE aktuelna=1");
	$ag = db_result($q1_8,0,0);
}

else {

	// Pretvaramo rss id u userid
	$id = db_escape($_REQUEST['id']);
	$q1 = db_query("select auth from rss where id='$id'");
	if (db_num_rows($q1)<1) {
		print "Greska! Nepoznat RSS ID $id";
		return 0;
	}
	$userid = db_result($q1,0,0);
	// Update timestamp
	$q2 = db_query("update rss set access=NOW() where id='$id'");


	// Ime studenta
	$q5 = db_query("select ime,prezime from osoba where id=$userid");
	if (db_num_rows($q5)<1) {
		print "Greska! Nepoznat userid $userid";
		return 0;
	}
	$ime = db_result($q5,0,0); $prezime = db_result($q5,0,1);
}



$vrijeme_poruke = array();
$code_poruke = array();

/*$vrijeme_poruke[1]=1;
$code_poruke[1]="<item>
		<title>hello</title>
		<link>$conf_site_url/index.php?sta=student/zadaca&amp;zadaca=$r10[0]&amp;predmet=$r10[4]</link>
		<description><![CDATA[hello hello]]>
	</item>";

print $code_poruke[1];*/

// Rokovi za slanje zadaća

if ($id == "svi")
	$q10 = db_query("select z.id, z.naziv, UNIX_TIMESTAMP(z.rok), p.naziv, pk.id, UNIX_TIMESTAMP(z.vrijemeobjave), p.id, pk.akademska_godina from zadaca as z, ponudakursa as pk, predmet as p where z.predmet=pk.predmet and z.akademska_godina=pk.akademska_godina and pk.akademska_godina=$ag and pk.predmet=p.id and z.rok>curdate() and z.aktivna=1 order by rok desc limit $broj_poruka");
else 
	$q10 = db_query("select z.id, z.naziv, UNIX_TIMESTAMP(z.rok), p.naziv, pk.id, UNIX_TIMESTAMP(z.vrijemeobjave), p.id, pk.akademska_godina from zadaca as z, student_predmet as sp, ponudakursa as pk, predmet as p where z.predmet=pk.predmet and z.akademska_godina=pk.akademska_godina and sp.student=$userid and sp.predmet=pk.id and pk.predmet=p.id and z.rok>curdate() and z.aktivna=1 order by rok desc limit $broj_poruka");

while ($r10 = db_fetch_row($q10)) {
	// Da li je aktivan modul za zadaće?
	$q12 = db_query("select count(*) from studentski_modul as sm, studentski_modul_predmet as smp where sm.modul='student/zadaca' and sm.id=smp.studentski_modul and smp.predmet=$r10[6] and smp.akademska_godina=$r10[7]");
	if (db_result($q12,0,0)==0) continue;

	$vrijeme_poruke["z".$r10[0]] = $r10[5];
	$code_poruke["z".$r10[0]] = "<item>
		<guid isPermaLink=\"false\">z".$r10[0]."</guid>
		<title>Objavljena zadaća $r10[1], predmet $r10[3]</title>
		<link>$conf_site_url/index.php?sta=student/zadaca&amp;zadaca=$r10[0]&amp;predmet=$r10[6]&amp;ag=$r10[7]</link>
		<description><![CDATA[Rok za slanje je ".date("d. m. Y  h:i",$r10[2]).".]]></description>
		<pubDate>".date("D, j M Y H:i:s O", $vrijeme_poruke["z".$r10[0]])."</pubDate>
	</item>\n";
}


// Objavljeni rezultati ispita

if ($id == "svi")
	$q15 = db_query("select i.id, i.predmet, k.gui_naziv, UNIX_TIMESTAMP(i.vrijemeobjave), p.naziv, UNIX_TIMESTAMP(i.datum), pk.id, p.id, pk.akademska_godina from ispit as i, komponenta as k, ponudakursa as pk, predmet as p where i.akademska_godina=$ag and i.predmet=pk.predmet and i.akademska_godina=pk.akademska_godina and i.komponenta=k.id and pk.predmet=p.id order by i.vrijemeobjave desc limit $broj_poruka");
else 
	$q15 = db_query("select i.id, i.predmet, k.gui_naziv, UNIX_TIMESTAMP(i.vrijemeobjave), p.naziv, UNIX_TIMESTAMP(i.datum), pk.id, p.id, pk.akademska_godina from ispit as i, komponenta as k, student_predmet as sp, ponudakursa as pk, predmet as p where sp.student=$userid and sp.predmet=pk.id and i.predmet=pk.predmet and i.akademska_godina=pk.akademska_godina and i.komponenta=k.id and pk.predmet=p.id order by i.vrijemeobjave desc limit $broj_poruka");
while ($r15 = db_fetch_row($q15)) {
	if ($r15[3] < time()-60*60*24*30) continue; // preskacemo starije od mjesec dana

	// Da li je student položio predmet? Preskačemo ako jeste
	$q15a = db_query("select count(*) from konacna_ocjena where predmet=$r15[7] and ocjena>=6 and student=$userid");
	if (db_result($q15a,0,0)>0) continue;

	// Ima li kakvih rezultata?
	$q16 = db_query("select count(*) from ispitocjene where ispit=$r15[0]");
	if (db_result($q16,0,0)==0) {
		$q17 = db_query("select count(*) from ispit_termin where ispit=$r15[0]");
		if (db_result($q17,0,0)>0) {
			$vrijeme_poruke["i".$r15[0]] = $r15[3];
			$code_poruke["i".$r15[0]] = "<item>
		<guid isPermaLink=\"false\">i".$r15[0]."</guid>
		<title>Objavljeni termini za ispit $r15[2] (".date("d. m. Y",$r15[5]).") - predmet $r15[4]</title>
		<link>$conf_site_url/index.php?sta=student/predmet&amp;predmet=$r15[7]&amp;ag=$r15[8]</link>
		<description><![CDATA[Datum objave ".date("d. m. Y  h:i",$r15[3]).".]]></description>
		<pubDate>".date("D, j M Y H:i:s O", $vrijeme_poruke["i".$r15[0]])."</pubDate>
	</item>\n";
		}
	}
	else {
		if ($id == "svi") $modul = "izvjestaj/predmet"; else $modul = "student/predmet";
		$vrijeme_poruke["i".$r15[0]] = $r15[3];
		$code_poruke["i".$r15[0]] = "<item>
		<guid isPermaLink=\"false\">i".$r15[0]."</guid>
		<title>Objavljeni rezultati ispita $r15[2] (".date("d. m. Y",$r15[5]).") - predmet $r15[4]</title>
		<link>$conf_site_url/index.php?sta=$modul&amp;predmet=$r15[7]&amp;ag=$r15[8]</link>
		<description><![CDATA[Datum objave ".date("d. m. Y  h:i",$r15[3]).".]]></description>
		<pubDate>".date("D, j M Y H:i:s O", $vrijeme_poruke["i".$r15[0]])."</pubDate>
	</item>\n";
	}
}



// konacna ocjena

if ($id != "svi") {
$q17 = db_query("select pk.id, ko.ocjena, UNIX_TIMESTAMP(ko.datum), p.naziv, p.id, pk.akademska_godina from konacna_ocjena as ko, student_predmet as sp, ponudakursa as pk, predmet as p where ko.student=$userid and sp.student=$userid and sp.predmet=pk.id and ko.predmet=pk.predmet and ko.akademska_godina=pk.akademska_godina and pk.predmet=p.id order by ko.datum desc limit $broj_poruka");
while ($r17 = db_fetch_row($q17)) {
	if ($r17[2] < time()-60*60*24*30) continue; // preskacemo starije od mjesec dana
	$vrijeme_poruke["k".$r17[0]] = $r17[2];
	$code_poruke["k".$r17[0]] = "<item>
		<guid isPermaLink=\"false\">k".$r17[0]."</guid>
		<title>Čestitamo! Dobili ste $r17[1] -- predmet $r17[3]</title>
		<link>$conf_site_url/index.php?sta=student/predmet&amp;predmet=$r17[4]&amp;ag=$r17[5]</link>
		<description></description>
		<pubDate>".date("D, j M Y H:i:s O", $vrijeme_poruke["k".$r17[0]])."</pubDate>
	</item>\n";
}
}



/*// pregledane zadace
// (ok, ovo moze biti JAAAKO sporo ali dacemo sve od sebe da ne bude ;) )

$q18 = db_query("select zk.id, zk.redni_broj, UNIX_TIMESTAMP(zk.vrijeme), p.naziv, z.naziv, pk.id, z.id, p.id, pk.akademska_godina from zadatak as zk, zadaca as z, ponudakursa as pk, predmet as p where zk.student=$userid and zk.status!=1 and zk.status!=4 and zk.zadaca=z.id and z.predmet=p.id and pk.predmet=p.id and pk.akademska_godina=z.akademska_godina order by zk.id desc limit $broj_poruka");
$zadaca_bila = array();
while ($r18 = db_fetch_row($q18)) {
	if (in_array($r18[6],$zadaca_bila)) continue; // ne prijavljujemo vise puta istu zadacu
	if ($r18[2] < time()-60*60*24*30) break; // IDovi bi trebali biti hronoloskim redom, tako da ovdje mozemo prekinuti petlju
	$vrijeme_poruke["zp".$r18[0]] = $r18[2];
	$code_poruke["zp".$r18[0]] = "<item>
		<guid isPermaLink=\"false\">zp".$r18[0]."</guid>
		<title>Pregledana zadaća $r18[4], predmet $r18[3]</title>
		<link>$conf_site_url/index.php?sta=student/predmet&amp;predmet=$r18[7]&amp;ag=$r18[8]</link>
		<description><![CDATA[Posljednja izmjena: ".date("d. m. Y. h:i:s",$r18[2])."]]></description>
		<pubDate>".date("D, j M Y H:i:s O", $vrijeme_poruke["zp".$r18[0]])."</pubDate>
	</item>\n";
	array_push($zadaca_bila,$r18[6]);
}
*/


// PORUKE (izvadak iz inboxa)


if ($id == "svi") {
	$q100 = db_query("select id, UNIX_TIMESTAMP(vrijeme), opseg, primalac, naslov, tip, posiljalac from poruka where opseg=3 or opseg=4 or opseg=5 order by vrijeme desc limit $broj_poruka");
	while ($r100 = db_fetch_row($q100)) {
		$id = $r100[0];
		$opseg = $r100[2];
		$primalac = $r100[3];
		
		// Poruka je ok
		if (++$br > $broj_poruka) break; // Nema smisla da gledamo dalje
		$vrijeme_poruke[$id]=$r100[1];

		// Fino vrijeme
		$vr = $vrijeme_poruke[$id];
		$vrijeme="";
	//	if (date("d.m.Y",$vr)==date("d.m.Y")) $vrijeme = "danas ";
	//	else if (date("d.m.Y",$vr+3600*24)==date("d.m.Y")) $vrijeme = "juče ";
	//	else 
		$vrijeme .= date("d.m. ",$vr);
		$vrijeme .= date("H:i",$vr);

		$naslov = $r100[4];
		// Ukidam nove redove u potpunosti
		$naslov = str_replace("\n", " ", $naslov);
		// RSS ne podržava &quot; entitet!?
		$naslov = str_replace("&quot;", '"', $naslov);
		if (strlen($naslov)>30) $naslov = mb_substr($naslov,0,28)."...";
		if (!preg_match("/\S/",$naslov)) $naslov = "[Bez naslova]";

		// Posiljalac
		if ($r100[6]==0) {
			$posiljalac="Administrator";
		} else {
			$q120 = db_query("select ime,prezime from osoba where id=$r100[6]");
			if (db_num_rows($q120)>0) {
				$posiljalac=db_result($q120,0,0)." ".db_result($q120,0,1);
			} else {
				$posiljalac="Nepoznat";
			}
		}

		if ($r100[5]==1)
			$title="Obavijest";
		else
			$title="Poruka";

		$code_poruke[$id]="<item>
			<guid isPermaLink=\"false\">".$id."</guid>
			<title>$title: $naslov ($vrijeme)</title>
			<link>$conf_site_url/index.php?sta=common%2Finbox&amp;poruka=$id</link>
			<description>Poslao: $posiljalac</description>
			<pubDate>".date("D, j M Y H:i:s O", $vrijeme_poruke[$id])."</pubDate>
		</item>\n";
	}

} else {
	// Zadnja akademska godina
	$q20 = db_query("select id,naziv from akademska_godina where aktuelna=1 order by id desc limit 1");
	$ag = db_result($q20,0,0);
	$ag_naziv = db_result($q20,0,1);

	// Studij koji student trenutno sluša
	$studij=0;
	$q30 = db_query("select studij,semestar from student_studij where student=$userid and akademska_godina=$ag order by semestar desc limit 1");
	if (db_num_rows($q30)>0) {
		$studij = db_result($q30,0,0);
	}


	$br = 0;
	$q100 = db_query("select id, UNIX_TIMESTAMP(vrijeme), opseg, primalac, naslov, tip, posiljalac from poruka order by vrijeme desc limit $broj_poruka");
while ($r100 = db_fetch_row($q100)) {
	$id = $r100[0];
	$opseg = $r100[2];
	$primalac = $r100[3];
	if ($opseg == 2 || $opseg==3 && $primalac!=$studij || $opseg==4 && $primalac!=$ag ||  $opseg==7 && $primalac!=$userid)
		continue;
	if ($opseg==5) {
		// Poruke od starih akademskih godina nisu relevantne
		if ($r100[1]<mktime(0,0,0,9,1,intval($ag_naziv))) continue;

		// odredjujemo da li student slusa predmet
		$q110 = db_query("select count(*) from student_predmet as sp, ponudakursa as pk where sp.student=$userid and sp.predmet=pk.id and pk.predmet=$primalac and pk.akademska_godina=$ag");
		if (db_result($q110,0,0)<1) continue;
	}
	if ($opseg==6) {
		// da li je student u labgrupi?
		$q115 = db_query("select count(*) from student_labgrupa where student=$userid and labgrupa=$primalac");
		if (db_result($q115,0,0)<1) continue;
	}

	// Poruka je ok
	if (++$br > $broj_poruka) break; // Nema smisla da gledamo dalje
	$vrijeme_poruke[$id]=$r100[1];

	// Fino vrijeme
	$vr = $vrijeme_poruke[$id];
	$vrijeme="";
//	if (date("d.m.Y",$vr)==date("d.m.Y")) $vrijeme = "danas ";
//	else if (date("d.m.Y",$vr+3600*24)==date("d.m.Y")) $vrijeme = "juče ";
//	else 
	$vrijeme .= date("d.m. ",$vr);
	$vrijeme .= date("H:i",$vr);

	$naslov = $r100[4];
	// Ukidam nove redove u potpunosti
	$naslov = str_replace("\n", " ", $naslov);
	// RSS ne podržava &quot; entitet!?
	$naslov = str_replace("&quot;", '"', $naslov);
	if (strlen($naslov)>30) $naslov = mb_substr($naslov,0,28)."...";
	if (!preg_match("/\S/",$naslov)) $naslov = "[Bez naslova]";

	// Posiljalac
	if ($r100[6]==0) {
		$posiljalac="Administrator";
	} else {
		$q120 = db_query("select ime,prezime from osoba where id=$r100[6]");
		if (db_num_rows($q120)>0) {
			$posiljalac=db_result($q120,0,0)." ".db_result($q120,0,1);
		} else {
			$posiljalac="Nepoznat";
		}
	}

	if ($r100[5]==1)
		$title="Obavijest";
	else
		$title="Poruka";

	$code_poruke[$id]="<item>
		<guid isPermaLink=\"false\">".$id."</guid>
		<title>$title: $naslov ($vrijeme)</title>
		<link>$conf_site_url/index.php?sta=common%2Finbox&amp;poruka=$id</link>
		<description>Poslao: $posiljalac</description>
		<pubDate>".date("D, j M Y H:i:s O", $vrijeme_poruke[$id])."</pubDate>
	</item>\n";
}
}


// Novosti sa Courseware-a


if ($conf_moodle) {

// Prikazujemo vijesti od posljednjeg logina minus dvije sedmice
$vrijeme_za_novosti = time()-(14*24*60*60);
$vrijeme_posljednjeg_logina = time();


$moodle_con = $__db_connection;
if (!$conf_moodle_reuse_connection) {
	// Pravimo novu konekciju za moodle, kod iz dbconnect2() u libvedran
	if (!($moodle_con = mysql_connect($conf_moodle_dbhost, $conf_moodle_dbuser, $conf_moodle_dbpass))) {
		biguglyerror(mysql_error());
		exit;
	}
	if (!mysql_select_db($conf_moodle_db, $moodle_con)) {
		biguglyerror(mysql_error());
		exit;
	}
	if ($conf_use_mysql_utf8) {
		mysql_set_charset("utf8",$moodle_con);
	}
}


// Potrebno je pronaci u tabeli moodle_predmet_id koji je id kursa koristen na Moodle stranici za odredjeni predmet sa Zamger-a..tacno jedan id kursa iz moodle baze odgovara jednom predmetu u zamger bazi

if ($id == "svi")
	$q200 = db_query("select mpi.moodle_id, p.kratki_naziv, p.naziv from ponudakursa as pk, predmet as p, moodle_predmet_id as mpi where pk.predmet=p.id and pk.predmet=mpi.predmet and pk.akademska_godina=$ag and mpi.akademska_godina=$ag");

else 
	$q200 = db_query("select mpi.moodle_id, p.kratki_naziv, p.naziv from student_predmet as sp, ponudakursa as pk, predmet as p, moodle_predmet_id as mpi where sp.student=$userid and sp.predmet=pk.id and pk.predmet=p.id and pk.predmet=mpi.predmet and pk.akademska_godina=$ag and mpi.akademska_godina=$ag");
while ($r200 = db_fetch_row($q200)) {
	$course_id = $r200[0];

	$q210 = mysql_query("select module, instance, visible, id, added from ".$conf_moodle_db.".".$conf_moodle_prefix."course_modules where course=$course_id",$moodle_con);
	
	while ($r210 = mysql_fetch_array($q210)) {

		// Modul 9 je zaduzen za cuvanje informacija o obavijesti koje se postavljaju u labelu na moodle stranici
		// Ako visible != 1 instanca je sakrivena i ne treba je prikazati u Zamgeru
		if ($r210[0] == 9 && $r210[2] == 1) {
			$q220 = mysql_query("select name, timemodified from ".$conf_moodle_db.".".$conf_moodle_prefix."label where course=$course_id and id=$r210[1] and timemodified>$vrijeme_za_novosti order by timemodified desc",$moodle_con);
			
			while ($r220 = mysql_fetch_array($q220)) {
				$vrijeme = date("d.m. H:i",($r210[4]>$r220[1])?$r210[4]:$r220[1]);

				// Skraćeni naslov
				$naslov = strip_tags($r220[0]);
				$naslov = str_replace("&nbsp;", " ", $naslov); // HTML entiteti u polju
				$naslov = str_replace("&", "&#x26;", $naslov); // Kodiranje za ampersand
				if (strlen($naslov)>30) 
					$naslov = mb_substr($naslov,0,28)."...";

				$vrijeme_poruke["mo".$r210[3]] = ($r210[4]>$r220[1])?$r210[4]:$r220[1];
				$code_poruke["mo".$r210[3]] = "<item>
		<guid isPermaLink=\"false\">mo".$r210[3]."</guid>
		<title>Obavijest ($r200[1]): $naslov ($vrijeme)</title>
		<link>".$conf_moodle_url."course/view.php?id=$course_id</link>
		<description>Detaljnije na Moodle stranici predmeta $r200[2]</description>
		<pubDate>".date("D, j M Y H:i:s O", $vrijeme_poruke["mo".$r210[3]])."</pubDate>
		</item>\n";
			}
		}
		
		// Modul 13 je zaduzen za cuvanje informacija o dodatom resursu na moodle stranici
		if ($r210[0] == 13 && $r210[2] == 1) {
			$q230 = mysql_query("select name, timemodified, id from ".$conf_moodle_db.".".$conf_moodle_prefix."resource where course=$course_id and id=$r210[1] and timemodified>$vrijeme_za_novosti order by timemodified desc",$moodle_con);
			
			while ($r230 = mysql_fetch_array($q230)) {
				$vrijeme = date("d.m. H:i",($r210[4]>$r230[1])?$r210[4]:$r230[1]);

				// Skraćeni naslov
				$naslov = strip_tags($r230[0]);
				$naslov = str_replace("&nbsp;", " ", $naslov); // HTML entiteti u polju
				$naslov = str_replace("&", "&#x26;", $naslov); // Kodiranje za ampersand
				if (strlen($naslov)>30) 
					$naslov = mb_substr($naslov,0,28)."...";

				$vrijeme_poruke["mr".$r210[3]] = ($r210[4]>$r230[1])?$r210[4]:$r230[1];
				$code_poruke["mr".$r210[3]] = "<item>
		<guid isPermaLink=\"false\">mr".$r210[3]."</guid>
		<title>Resurs ($r200[1]): $naslov ($vrijeme)</title>
		<link>".$conf_moodle_url."mod/resource/view.php?id=$r210[3]</link>
		<description>Detaljnije na Moodle stranici predmeta $r200[2]</description>
		<pubDate>".date("D, j M Y H:i:s O", $vrijeme_poruke["mr".$r210[3]])."</pubDate>
		</item>\n";
			}
		}
	}
}
	
// Diskonektujemo moodle
if (!$conf_moodle_reuse_connection) {
	mysql_close($moodle_con);
}
}


// KRAJ I ISPIS
// Sortiramo po vremenu

arsort($vrijeme_poruke);
$count=0;

header("Content-type: application/rss+xml");
header("Last-Modified: " . gmdate("D, j M Y H:i:s", array_shift(array_values($vrijeme_poruke))) . " GMT");

?>
<<?='?'?>xml version="1.0" encoding="utf-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
        <title>Zamger RSS</title>
        <link><?=$conf_site_url?></link>
        <description>Aktuelne informacije za studenta <?=$ime?> <?=$prezime?></description>
        <language>bs-ba</language>
        <atom:link href="<?=$conf_site_url?>/rss.php?id=<?=$id?>" rel="self" type="application/rss+xml" />
<?


foreach ($vrijeme_poruke as $id=>$vrijeme) {
	if ($count==0) {
		// Polje pubDate u zaglavlju sadrži vrijeme zadnje izmjene tj. najnovije poruke

		//print "        <pubDate>".date(DATE_RSS, $vrijeme)."</pubDate>\n";
		// U verziji PHP 5.1.6 (i vjerovatno starijim) DATE_RSS je nekorektno 
		// izjednačeno sa "D, j M Y H:i:s T"
		print "        <pubDate>".date("D, j M Y H:i:s O", $vrijeme)."</pubDate>\n";
	}

	print $code_poruke[$id];
	$count++;
	if ($count==$broj_poruka) break; // prikazujemo samo prvih $broj_poruka poruka
}




?>
</channel>
</rss>
