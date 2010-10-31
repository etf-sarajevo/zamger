<?

// RSS - feed za studente

// v3.9.1.0 (2008/04/30) + pocetak
// v3.9.1.1 (2008/10/24) + Popravljen entity u linku za common/inbox
// v4.0.0.0 (2009/02/19) + Release
// v4.0.9.1 (2009/03/31) + Tabela ispit preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.2 (2009/03/31) + Tabela konacna_ocjena preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.3 (2009/04/01) + Tabela zadaca preusmjerena sa ponudakursa na tabelu predmet; popravljen link na stranicu za konacnu ocjenu (greska unesena sa r372)
// v4.0.9.4 (2009/04/06) + Dodano polje pubDate na sve kanale
// v4.0.9.5 (2009/04/19) + Popravljen link na rezultate ispita
// v4.0.9.6 (2009/04/29) + Prebacujem tabelu poruka (opseg 5) sa ponudekursa na predmet (neki studenti ce mozda dobiti dvije identicne poruke); jos uvijek koristena auth tabela za ime i prezime, sto spada u davnu historiju zamgera
// v4.0.9.7 (2009/05/01) + Parametri modula student/predmet i student/zadaca su sada predmet i ag


$broj_poruka = 10;


require("lib/libvedran.php");
require("lib/zamger.php");
require("lib/config.php");

dbconnect2($conf_dbhost,$conf_dbuser,$conf_dbpass,$conf_dbdb);

// Parametri potrebni za Moodle integraciju
global $conf_moodle, $conf_moodle_url, $conf_moodle_db, $conf_moodle_prefix, $conf_moodle_reuse_connection, $conf_moodle_dbhost, $conf_moodle_dbuser, $conf_moodle_dbpass;
global $__lv_connection, $conf_use_mysql_utf8;

// Pretvaramo rss id u userid
$id = my_escape($_REQUEST['id']);
$q1 = myquery("select auth from rss where id='$id'");
if (mysql_num_rows($q1)<1) {
	print "Greska! Nepoznat RSS ID $id";
	return 0;
}
$userid = mysql_result($q1,0,0);
// Update timestamp
$q2 = myquery("update rss set access=NOW() where id='$id'");


// Ime studenta
$q5 = myquery("select ime,prezime from osoba where id=$userid");
if (mysql_num_rows($q5)<1) {
	print "Greska! Nepoznat userid $userid";
	return 0;
}
$ime = mysql_result($q5,0,0); $prezime = mysql_result($q5,0,1);


header("Content-type: application/rss+xml");

?>
<<?='?'?>xml version="1.0" encoding="utf-8"?>
<!DOCTYPE rss PUBLIC "-//Netscape Communications//DTD RSS 0.91//EN" "http://www.rssboard.org/rss-0.91.dtd">
<rss version="0.91">
<channel>
        <title>Zamger RSS</title>
        <link><?=$conf_site_url?></link>
        <description>Aktuelne informacije za studenta <?=$ime?> <?=$prezime?></description>
        <language>bs-ba</language>
<?



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

$q10 = myquery("select z.id, z.naziv, UNIX_TIMESTAMP(z.rok), p.naziv, pk.id, UNIX_TIMESTAMP(z.vrijemeobjave), p.id, pk.akademska_godina from zadaca as z, student_predmet as sp, ponudakursa as pk, predmet as p where z.predmet=pk.predmet and z.akademska_godina=pk.akademska_godina and sp.student=$userid and sp.predmet=pk.id and pk.predmet=p.id and z.rok>curdate() and z.aktivna=1 order by rok desc limit $broj_poruka");
while ($r10 = mysql_fetch_row($q10)) {
	// Da li je aktivan modul za zadaće?
	$q12 = myquery("select count(*) from studentski_modul as sm, studentski_modul_predmet as smp where sm.modul='student/zadaca' and sm.id=smp.studentski_modul and smp.predmet=$r10[6] and smp.akademska_godina=$r10[7]");
	if (mysql_result($q12,0,0)==0) continue;

	$code_poruke["z".$r10[0]] = "
		<item>
		<title>Objavljena zadaća $r10[1], predmet $r10[3]</title>
		<link>$conf_site_url/index.php?sta=student/zadaca&amp;zadaca=$r10[0]&amp;predmet=$r10[6]&amp;ag=$r10[7]</link>
		<description><![CDATA[Rok za slanje je ".date("d. m. Y h:i ",$r10[2]).".]]></description>
		</item>\n";
	$vrijeme_poruke["z".$r10[0]] = $r10[5];
}


// Objavljeni rezultati ispita

$q15 = myquery("select i.id, i.predmet, k.gui_naziv, UNIX_TIMESTAMP(i.vrijemeobjave), p.naziv, UNIX_TIMESTAMP(i.datum), pk.id, p.id, pk.akademska_godina from ispit as i, komponenta as k, student_predmet as sp, ponudakursa as pk, predmet as p where sp.student=$userid and sp.predmet=pk.id and i.predmet=pk.predmet and i.akademska_godina=pk.akademska_godina and i.komponenta=k.id and pk.predmet=p.id order by i.vrijemeobjave desc limit $broj_poruka");
while ($r15 = mysql_fetch_row($q15)) {
	if ($r15[3] < time()-60*60*24*30) continue; // preskacemo starije od mjesec dana
	$code_poruke["i".$r15[0]] = "
		<item>
		<title>Objavljeni rezultati ispita $r15[2] (".date("d. m. Y",$r15[5]).") - predmet $r15[4]</title>
		<link>$conf_site_url/index.php?sta=student/predmet&amp;predmet=$r15[7]&amp;ag=$r15[8]</link>
		<description></description>
		</item>\n";
	$vrijeme_poruke["i".$r15[0]] = $r15[3];
}

// konacna ocjena

$q17 = myquery("select pk.id, ko.ocjena, UNIX_TIMESTAMP(ko.datum), p.naziv, p.id, pk.akademska_godina from konacna_ocjena as ko, student_predmet as sp, ponudakursa as pk, predmet as p where ko.student=$userid and sp.student=$userid and sp.predmet=pk.id and ko.predmet=pk.predmet and ko.akademska_godina=pk.akademska_godina and pk.predmet=p.id order by ko.datum desc limit $broj_poruka");
while ($r17 = mysql_fetch_row($q17)) {
	if ($r17[2] < time()-60*60*24*30) continue; // preskacemo starije od mjesec dana
	$code_poruke["k".$r17[0]] = "
		<item>
		<title>Čestitamo! Dobili ste $r17[1] -- predmet $r17[3]</title>
		<link>$conf_site_url/index.php?sta=student/predmet&amp;predmet=$r17[4]&amp;ag=$r17[5]</link>
		<description></description>
		</item>\n";
	$vrijeme_poruke["k".$r17[0]] = $r17[2];
}



// pregledane zadace
// (ok, ovo moze biti JAAAKO sporo ali dacemo sve od sebe da ne bude ;) )

$q18 = myquery("select zk.id, zk.redni_broj, UNIX_TIMESTAMP(zk.vrijeme), p.naziv, z.naziv, pk.id, z.id, p.id, pk.akademska_godina from zadatak as zk, zadaca as z, ponudakursa as pk, predmet as p where zk.student=$userid and zk.status!=1 and zk.status!=4 and zk.zadaca=z.id and z.predmet=p.id and pk.predmet=p.id and pk.akademska_godina=z.akademska_godina order by zk.id desc limit 10");
$zadaca_bila = array();
while ($r18 = mysql_fetch_row($q18)) {
	if (in_array($r18[6],$zadaca_bila)) continue; // ne prijavljujemo vise puta istu zadacu
	if ($r18[2] < time()-60*60*24*30) break; // IDovi bi trebali biti hronoloskim redom, tako da ovdje mozemo prekinuti petlju
	$code_poruke["zp".$r18[0]] = "
		<item>
		<title>Pregledana zadaća $r18[4], predmet $r18[3]</title>
		<link>$conf_site_url/index.php?sta=student/predmet&amp;predmet=$r18[7]&amp;ag=$r18[8]</link>
		<description><![CDATA[Posljednja izmjena: ".date("d. m. Y. h:i:s",$r18[2])."]]></description>
		</item>\n";
	array_push($zadaca_bila,$r18[6]);
	$vrijeme_poruke["zp".$r18[0]] = $r18[2];
}



// PORUKE (izvadak iz inboxa)


// Zadnja akademska godina
$q20 = myquery("select id,naziv from akademska_godina where aktuelna=1 order by id desc limit 1");
$ag = mysql_result($q20,0,0);
$ag_naziv = mysql_result($q20,0,1);

// Studij koji student trenutno sluša
$studij=0;
$q30 = myquery("select studij,semestar from student_studij where student=$userid and akademska_godina=$ag order by semestar desc limit 1");
if (mysql_num_rows($q30)>0) {
	$studij = mysql_result($q30,0,0);
}


$br = 0;
$q100 = myquery("select id, UNIX_TIMESTAMP(vrijeme), opseg, primalac, naslov, tip, posiljalac from poruka order by vrijeme desc");
while ($r100 = mysql_fetch_row($q100)) {
	$id = $r100[0];
	$opseg = $r100[2];
	$primalac = $r100[3];
	if ($opseg == 2 || $opseg==3 && $primalac!=$studij || $opseg==4 && $primalac!=$ag ||  $opseg==7 && $primalac!=$userid)
		continue;
	if ($opseg==5) {
		// Poruke od starih akademskih godina nisu relevantne
		if ($r100[1]<mktime(0,0,0,9,1,intval($ag_naziv))) continue;

		// odredjujemo da li student slusa predmet
		$q110 = myquery("select count(*) from student_predmet as sp, ponudakursa as pk where sp.student=$userid and sp.predmet=pk.id and pk.predmet=$primalac and pk.akademska_godina=$ag");
		if (mysql_result($q110,0,0)<1) continue;
	}
	if ($opseg==6) {
		// da li je student u labgrupi?
		$q115 = myquery("select count(*) from student_labgrupa where student=$userid and labgrupa=$primalac");
		if (mysql_result($q115,0,0)<1) continue;
	}

	// Poruka je ok
	if (++$br > $broj_poruka) break; // Nema smisla da gledamo dalje
	$vrijeme_poruke[$id]=$r100[1];

	// Fino vrijeme
	$vr = $vrijeme_poruke[$id];
	$vrijeme="";
	if (date("d.m.Y",$vr)==date("d.m.Y")) $vrijeme = "danas ";
	else if (date("d.m.Y",$vr+3600*24)==date("d.m.Y")) $vrijeme = "juče ";
	else $vrijeme .= date("d.m. ",$vr);
	$vrijeme .= date("H:i",$vr);

	$naslov = $r100[4];
	// Ukidam nove redove u potpunosti
	$naslov = str_replace("\n", " ", $naslov);
	// RSS ne podržava &quot; entitet!?
	$naslov = str_replace("&quot;", '"', $naslov);
	if (strlen($naslov)>30) $naslov = substr($naslov,0,28)."...";
	if (!preg_match("/\S/",$naslov)) $naslov = "[Bez naslova]";

	// Posiljalac
	if ($r100[6]==0) {
		$posiljalac="Administrator";
	} else {
		$q120 = myquery("select ime,prezime from osoba where id=$r100[6]");
		if (mysql_num_rows($q120)>0) {
			$posiljalac=mysql_result($q120,0,0)." ".mysql_result($q120,0,1);
		} else {
			$posiljalac="Nepoznat";
		}
	}

	if ($r100[5]==1)
		$title="Obavijest";
	else
		$title="Poruka";

	$code_poruke[$id]="
		<item>
		<title>$title: $naslov ($vrijeme)</title>
		<link>$conf_site_url/index.php?sta=common%2Finbox&amp;poruka=$id</link>
		<description>Poslao: $posiljalac</description>
		</item>\n";
}


//Novosti sa Courseware-a

// Cache nećemo puniti jer je to sporo, a student/predmet se često otvara

/*//prikupljanje podatak o novostima na Moodle stranicama
$q40 = myquery("Select predmet from student_predmet where student=$userid");
if(mysql_num_rows($q40)>0){
	while($r40 = mysql_fetch_array($q40)){
		$predmet = $r40[0];
		
		$q41 = myquery("Select moodle_id from moodle_predmet_id where predmet=$predmet and akademska_godina=$ag");
		
		if(mysql_num_rows($q41)==1){
		
		$moodle_id = mysql_result($q41,0);
		$id_modula = array();
		
		//provjera konekcije na moodle bazu
		$moodle_con = $__lv_connection;
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
		$q42 = mysql_query("Select module from ".$conf_moodle_db.".".$conf_moodle_prefix."course_modules where course=$moodle_id and module=9 or module=13 order by added desc limit 10",$moodle_con);
		
			while($r42 = mysql_fetch_array($q42)){
				if($r42[0]==9){
					$q43 = mysql_query("Select name, timemodified from ".$conf_moodle_db.".".$conf_moodle_prefix."label where timemodified>$vrijeme_loga order by timemodified desc limit 5",$moodle_con);
					if(mysql_num_rows($q43)>0){
					while($r43 = mysql_fetch_array($q43)){
						$q44 = myquery("Select id from $conf_dbdb.moodle_predmet_rss where vrstanovosti=1 and moodle_id=$moodle_id and sadrzaj='$r43[0]' and vrijeme_promjene=$r43[1]");
						
						if(mysql_num_rows($q44)<1){
						myquery("Insert into moodle_predmet_rss(vrstanovosti, moodle_id, sadrzaj, vrijeme_promjene) values ('1', '$moodle_id', '$r43[0]','$r43[1]')");
						}
						}
					}
				}
				if($r42[0]==13){
					$q45 = mysql_query("Select name, timemodified from ".$conf_moodle_db.".".$conf_moodle_prefix."resource where timemodified>$vrijeme_loga order by timemodified desc limit 5",$moodle_con);
					if(mysql_num_rows($q45)>0){
					while($r45 = mysql_fetch_array($q45)){
						$q46 = myquery("Select id from $conf_dbdb.moodle_predmet_rss where vrstanovosti=2 and moodle_id=$moodle_id and sadrzaj='$r45[0]' and vrijeme_promjene=$r45[1]");
						
						if(mysql_num_rows($q46)<1){
						myquery("Insert into moodle_predmet_rss(vrstanovosti, moodle_id, sadrzaj, vrijeme_promjene) values ('2', '$moodle_id', '$r45[0]','$r45[1]')");
						}
						}
					}
				}
			}
		}
		
	}
}
// Diskonektujemo moodle o
	if (!$conf_moodle_reuse_connection) {
		mysql_close($moodle_con);
	}
*/




// Vijesti iz cache-a stavljamo u niz

$q200 = myquery("select mpi.moodle_id, mpr.id, mpr.vrstanovosti, mpr.sadrzaj, mpr.vrijeme_promjene, p.kratki_naziv, p.naziv from moodle_predmet_id as mpi, moodle_predmet_rss as mpr, student_predmet as sp, ponudakursa as pk, predmet as p where sp.student=$userid and sp.predmet=pk.id and pk.predmet=mpi.predmet and pk.akademska_godina=$ag and mpi.akademska_godina=$ag and mpi.moodle_id=mpr.moodle_id and pk.predmet=p.id order by mpr.vrijeme_promjene desc limit $broj_poruka");
while ($r200 = mysql_fetch_row($q200)) {
	$moodle_id = $r200[0];

	// Skraćeni naslov
	$naslov = $r200[3];
	if (strlen($naslov)>30) 
		$naslov = substr($naslov,0,28)."...";

	// Fino vrijeme
	$vrijeme="";
	if (date("d.m.Y",$r200[4])==date("d.m.Y")) $vrijeme = "danas ";
	else if (date("d.m.Y",$r200[4]+3600*24)==date("d.m.Y")) $vrijeme = "juče ";
	else $vrijeme .= date("d.m. ",$r200[4]);
	$vrijeme .= date("H:i",$r200[4]);

	if ($r200[2]==1) { // 1 = labela
		$code_poruke["mo".$r200[1]]= "<item>
		<title>Obavijest ($r200[5]): $naslov ($vrijeme)</title>
		<link>".$conf_moodle_url."course/view.php?id=$moodle_id</link>
		<description>Detaljnije na Moodle stranici predmeta $r200[6]</description>
		</item>\n";
		$vrijeme_poruke["mo".$r200[1]] = $r200[4];
	}

	if ($r200[2]==2) { // 2 = resurs
		$code_poruke["mr".$r200[1]]= "<item>
		<title>Resurs ($r200[5]): $naslov ($vrijeme)</title>
		<link>".$conf_moodle_url."course/view.php?id=$moodle_id</link>
		<description>Detaljnije na Moodle stranici predmeta $r200[6]</description>
		</item>\n";

		$vrijeme_poruke["mr".$r200[1]] = $r200[4];
	}
}



// Sortiramo po vremenu
arsort($vrijeme_poruke);
$count=0;


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
	if ($count==$broj_poruka) break; // prikazujemo 5 poruka
}




?>
</channel>
</rss>
