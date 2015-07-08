<?

// STUDENT/PREDMET - statusna stranica predmeta

// v3.9.1.0 (2008/02/19) + Kopiran raniji stud_status, uz novi dizajn
// v3.9.1.1 (2008/03/28) + Dodana ikona za slanje novog zadatka (zad_novi.png)
// v3.9.1.2 (2008/04/09) + Dodan prikaz akademske godine uz ime predmeta; zadace bez imena; navigacija za zadace je prikazivala visak zadataka; otvori PDF u novom prozoru
// v3.9.1.3 (2008/10/02) + Dodana provjera da li student slusa predmet
// v4.0.0.0 (2009/02/19) + Release
// v4.0.9.1 (2009/03/24) + Prebacena polja ects i tippredmeta iz tabele ponudakursa u tabelu predmet
// v4.0.9.2 (2009/03/31) + Tabela ispit preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.3 (2009/03/31) + Tabela konacna_ocjena preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.4 (2009/04/01) + Tabela zadaca preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.5 (2009/04/02) + Tabela studentski_moduli preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.6 (2009/04/29) + Preusmjeravam tabelu labgrupa sa tabele ponudakursa na tabelu predmet
// v4.0.9.7 (2009/05/01) + Parametri su sada predmet i ag
// v4.0.9.8 (2009/05/06) + Kod ispisa naziva grupe u kojoj je student, necemo uzimati u obzir virtualne grupe; ispis prisustva pojednostavljen ukidanjem labgrupe 0
// v4.0.9.9 (2009/10/20) + Ne prikazuj link na student/pdf ako je zadaca tipa attachment


function student_predmet() {

global $userid;


$predmet = intval($_REQUEST['predmet']);
$ag = intval($_REQUEST['ag']); // akademska godina


// Podaci za zaglavlje
$q10 = myquery("select naziv from predmet where id=$predmet");
if (mysql_num_rows($q10)<1) {
	zamgerlog("nepoznat predmet $predmet",3); // nivo 3: greska
	zamgerlog2("nepoznat predmet", $predmet);
	biguglyerror("Nepoznat predmet");
	return;
}

$q15 = myquery("select naziv from akademska_godina where id=$ag");
if (mysql_num_rows($q15)<1) {
	zamgerlog("nepoznata akademska godina $ag",3); // nivo 3: greska
	zamgerlog2("nepoznata akademska godina", $ag); // nivo 3: greska
	biguglyerror("Nepoznata akademska godina");
	return;
}

// Da li student slusa predmet?
$q17 = myquery("select sp.predmet from student_predmet as sp, ponudakursa as pk where sp.student=$userid and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
if (mysql_num_rows($q17)<1) {
	zamgerlog("student ne slusa predmet pp$predmet (ag$ag)", 3);
	zamgerlog2("student ne slusa predmet", $predmet, $ag);
	biguglyerror("Niste upisani na ovaj predmet");
	return;
}
$ponudakursa = mysql_result($q17,0,0);


?>
<br/>
<p style="font-size: small;">Predmet: <b><?=mysql_result($q10,0,0)?> (<?=mysql_result($q15,0,0)?>)</b><br/>
<?

// Određivanje labgrupe
$q20 = myquery("select l.naziv from labgrupa as l, student_labgrupa as sl where l.predmet=$predmet and l.akademska_godina=$ag and l.virtualna=0 and l.id=sl.labgrupa and sl.student=$userid limit 1");
// Ispisujemo naziv prve nevirtualne grupe koju upit vrati
if (mysql_num_rows($q20)>0) {
	?>Grupa: <b><?=mysql_result($q20,0,0)?></b></p><?
}

print "<br/>\n";



// Nastavni ansambl
$q25 = myquery("select o.id, ast.naziv from angazman as a, angazman_status as ast, osoba as o where a.predmet=$predmet and a.akademska_godina=$ag and a.angazman_status=ast.id and a.osoba=o.id order by ast.id");
while ($r25 = mysql_fetch_row($q25)) {
	print "<b>".ucfirst($r25[1])."</b>: ".tituliraj($r25[0])."<br/>";
}
print "<br/>\n";


// PROGRESS BAR

$q30 = myquery("select kb.bodovi, k.maxbodova, k.tipkomponente, k.id from komponentebodovi as kb, komponenta as k where kb.student=$userid and kb.predmet=$ponudakursa and kb.komponenta=k.id");

$bodova=$mogucih=0;
while ($r30 = mysql_fetch_row($q30)) {
	$bodova += $r30[0];
	if ($r30[2] == 4) { // Tip komponente: zadaće
		$q35 = myquery("select sum(bodova) from zadaca where predmet=$predmet and akademska_godina=$ag and komponenta=$r30[3]");
		$do_sada_zadace = round(mysql_result($q35,0,0), 2);
		// Zbir bodova za zadaće ne može preći ono koliko nosi komponenta
		if ($do_sada_zadace > $r30[1])
			$mogucih += $r30[1];
		else
			$mogucih += $do_sada_zadace;
	} else
		$mogucih += $r30[1];
}
if ($bodova>$mogucih) $bodova=$mogucih; //ne bi se trebalo desiti


// boja označava napredak studenta
if ($mogucih==0) $procent=0;
else $procent = intval(($bodova/$mogucih)*100);
if ($procent>=75) 
	$color="#00FF00";
else if ($procent>=50)
	$color="#FFFF00";
else
	$color="#FF0000";


$tabela1=$procent*2;
$tabela2=200-$tabela1;

$ispis1 = "<img src=\"images/fnord.gif\" width=\"$tabela1\" height=\"10\">";
$ispis2 = "<img src=\"images/fnord.gif\" width=\"$tabela2\" height=\"1\"><br/> $bodova bodova";

if ($tabela1>$tabela2) { 
	$ispis1="<img src=\"images/fnord.gif\" width=\"$tabela1\" height=\"1\"><br/> $bodova bodova";
	$ispis2="<img src=\"images/fnord.gif\" width=\"$tabela2\" height=\"10\">";
}

?>


<!-- progress bar -->

<center><table border="0"><tr><td align="left">
<p>Osvojili ste....<br/>
<table style="border:1px;border-style:solid" width="206" cellpadding="0" cellspacing="2"><tr>
<td width="<?=$tabela1?>" bgcolor="<?=$color?>"><?=$ispis1?></td>
<td width="<?=$tabela2?>" bgcolor="#FFFFFF"><?=$ispis2?></td></tr></table>

<table width="208" border="0" cellspacing="0" cellpadding="0"><tr>
<td width="68">0</td>
<td align="center" width="68">50</td>
<td align="right" width="69">100</td></tr></table>
što je <?=$procent?>% od trenutno mogućih <?=round($mogucih,2) /* Rješavamo nepreciznost floata */ ?> bodova.</p>
</td></tr></table></center>


<!-- end progress bar -->
<?




// PRIKAZ NOVOSTI SA MOODLE-a (by fzilic)

function moodle_novosti($predmet, $ag) {
	// Parametri potrebni za Moodle integraciju
	global $conf_moodle, $conf_moodle_url, $conf_moodle_db, $conf_moodle_prefix, $conf_moodle_reuse_connection, $conf_moodle_dbhost, $conf_moodle_dbuser, $conf_moodle_dbpass;
	global $__lv_connection, $conf_use_mysql_utf8;
	global $userid;
	
	if (!$conf_moodle) return;

	// Potrebno je pronaci u tabeli moodle_predmet_id koji je id kursa koristen na Moodle stranici za odredjeni predmet sa Zamger-a..tacno jedan id kursa iz moodle baze odgovara jednom predmetu u zamger bazi
	$q60 = myquery("select moodle_id from moodle_predmet_id where predmet=$predmet and akademska_godina=$ag");
	if (mysql_num_rows($q60)<1) return;
	
	$course_id = mysql_result($q60,0,0);


	// Prikazujemo vijesti od posljednjeg logina minus dvije sedmice
	// TODO ovo se sada može napraviti jer imamo posljednji_pristup?
//	$q59 = myquery("select unix_timestamp(vrijeme) from log where userid=$userid and dogadjaj='login' order by vrijeme desc limit 2");

	//$vrijeme_logina = array();

	//while($r59 = mysql_fetch_array($q59))
	//	array_push($vrijeme_logina,$r59[0]);

	//$vrijeme_posljednjeg_logina = $vrijeme_logina[1];
	//$vrijeme_za_novosti = $vrijeme_logina[0]-(14*24*60*60);
	$vrijeme_za_novosti = time()-(14*24*60*60);
	$vrijeme_posljednjeg_logina = time();


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

	$q61 = mysql_query("select module, instance, visible, id, added from ".$conf_moodle_db.".".$conf_moodle_prefix."course_modules where course=$course_id",$moodle_con);
	
	while ($r61 = mysql_fetch_array($q61)) {
		// Modul 9 je zaduzen za cuvanje informacija o obavijesti koje se postavljaju u labelu na moodle stranici
		// Ako visible != 1 instanca je sakrivena i ne treba je prikazati u Zamgeru
		if ($r61[0] == 9 && $r61[2] == 1) {
			$q62 = mysql_query("select name, timemodified from ".$conf_moodle_db.".".$conf_moodle_prefix."label where course=$course_id and id=$r61[1] and timemodified>$vrijeme_za_novosti order by timemodified desc",$moodle_con);
			
			while ($r62 = mysql_fetch_array($q62)) {
				$code_poruke["o".$r61[3]] = $r62[0];
				$vrijeme_poruke_obavijest["o".$r61[3]] = ($r61[4]>$r62[1])?$r61[4]:$r62[1];
			}
		}
		
		// Modul 13 je zaduzen za cuvanje informacija o dodatom resursu na moodle stranici
		if ($r61[0] == 13 && $r61[2] == 1) {
			$q64 = mysql_query("select name, timemodified, id from ".$conf_moodle_db.".".$conf_moodle_prefix."resource where course=$course_id and id=$r61[1] and timemodified>$vrijeme_za_novosti order by timemodified desc",$moodle_con);
			
			while ($r64 = mysql_fetch_array($q64)) {
				$code_poruke["r".$r61[3]] = "<a href=\"$conf_moodle_url"."mod/resource/view.php?id=$r61[3]\">$r64[0]</a>";
				$vrijeme_poruke_resurs["r".$r61[3]] = ($r61[4]>$r64[1])?$r61[4]:$r64[1];
			}
		}
	}
	
	// Diskonektujemo moodle
	if (!$conf_moodle_reuse_connection) {
		mysql_close($moodle_con);
	}

	if (count($vrijeme_poruke_obavijest)>0) {
		?><h3>Obavještenja</h3>
		<ul><?
		arsort($vrijeme_poruke_obavijest);
		$count=0;
		foreach ($vrijeme_poruke_obavijest as $id=>$vrijeme) {
			$code = $code_poruke[$id];
			if ($vrijeme>$vrijeme_posljednjeg_logina) $code = "<b>$code</b>";
			print "<li>(".date("d.m. H:i:s", $vrijeme).") $code</li>\n";
			$count++;
			if ($count==5) break; // prikazujemo 5 poruka
		}
		print "<li><a href=\"$conf_moodle_url"."course/view.php?id=$course_id\">Opširnije...</a></li></ul>\n";
	}

	if (count($vrijeme_poruke_resurs)>0) {
		?><h3>Resursi</h3>
		<ul><?
		arsort($vrijeme_poruke_resurs);
		$count=0;
		foreach ($vrijeme_poruke_resurs as $id=>$vrijeme) {
			$code = $code_poruke[$id];
			if ($vrijeme>$vrijeme_posljednjeg_logina) $code = "<b>$code</b>";
			print "<li>(".date("d.m. H:i:s", $vrijeme).") $code</li>\n";
			$count++;
			if ($count==5) break; // prikazujemo 5 poruka
		}
		print "</ul>\n<br>\n";
	}

} // function moodle_novosti()


moodle_novosti($predmet, $ag);



//  PRISUSTVO NA VJEŽBAMA


function prisustvo_ispis($idgrupe,$imegrupe,$komponenta) {
	global $userid;

	if (!preg_match("/\w/",$imegrupe)) $imegrupe = "[Bez naziva]";

	$odsustva=0;
	$q70 = myquery("select id,UNIX_TIMESTAMP(datum), vrijeme from cas where labgrupa=$idgrupe and komponenta=$komponenta order by datum, vrijeme");
	if (mysql_num_rows($q70)<1) return; // Ne ispisuj grupe u kojima nema registrovanih časova

	$datumi = $vremena = $statusi = "";
	while ($r70 = mysql_fetch_row($q70)) {
		$datumi .= "<td>".date("d.m",$r70[1])."</td>\n";
		list($sati,$minute,$sekunde) = explode(":", $r70[2]);
		$vremena .= "<td>$sati<sup>$minute</sup></td>\n";
		$q80 = myquery("select prisutan from prisustvo where student=$userid and cas=$r70[0]");
		if (mysql_num_rows($q80)<1) {
			$statusi .= "<td bgcolor=\"#FFFFCC\" align=\"center\">/</td>\n";
		} else if (mysql_result($q80,0,0)==1) {
			$statusi .= "<td bgcolor=\"#CCFFCC\" align=\"center\">DA</td>\n";
		} else {
			$statusi .= "<td bgcolor=\"#FFCCCC\" align=\"center\">NE</td>\n";
			$odsustva++;
		}
	}

	
	?>

	<b>Prisustvo (<?=$imegrupe?>):</b><br/>
	<table cellspacing="0" cellpadding="2" border="0" id="prisustvo">
	<tr>
		<th>Datum</th>
	<?=$datumi?>
	</tr>
	<tr>
		<th>Vrijeme</th>
	<?=$vremena?>
	</tr>
	<tr>
		<th>Prisutan</th>
	<?=$statusi?>
	</tr>
	</table>
	</p>
	
	<?
	return $odsustva;
}

$q40 = myquery("select k.id,k.maxbodova,k.prolaz,k.opcija from komponenta as k, tippredmeta_komponenta as tpk, akademska_godina_predmet as agp
where agp.predmet=$predmet and agp.akademska_godina=$ag and agp.tippredmeta=tpk.tippredmeta and tpk.komponenta=k.id and k.tipkomponente=3"); // 3 = prisustvo

while ($r40 = mysql_fetch_row($q40)) {
	$id_komponente = $r40[0];
	$max_bodova = $r40[1];
	$min_bodova = $r40[2];
	$max_izostanaka = $r40[3];

	$odsustva = $casova = 0;
	$q60 = myquery("select l.id,l.naziv from labgrupa as l, student_labgrupa as sl where l.predmet=$predmet and l.akademska_godina=$ag and l.id=sl.labgrupa and sl.student=$userid");
	
	while ($r60 = mysql_fetch_row($q60)) {
		$odsustva += prisustvo_ispis($r60[0],$r60[1],$id_komponente);
		$q71 = myquery("select count(*) from cas where labgrupa=$r60[0] and komponenta=$id_komponente");
		$casova += mysql_result($q71,0,0);;
	}
	
	if ($max_izostanaka == -1) {
		if ($casova == 0) 
			$bodovi = 10;
		else
			$bodovi = $min_bodova + round(($max_bodova - $min_bodova) * (($casova - $odsustva) / $casova), 2 ); 
	} else if ($max_izostanaka == -2) { // Paraproporcionalni sistem TP
		if ($odsustva <= 2)
			$bodovi = $max_bodova;
		else if ($odsustva <= 2 + ($max_bodova - $min_bodova)/2)
			$bodovi = $max_bodova - ($odsustva-2)*2;
		else
			$bodovi = $min_bodova;
	} else if ($odsustva<=$max_izostanaka) {
		$bodovi = $max_bodova;
	} else {
		$bodovi = $min_bodova;
	}
	?><p>Ukupno na prisustvo imate <b><?=$bodovi?></b> bodova.</p>
	<?
}






//  ZADAĆE


// Statusne ikone:
$stat_icon = array("zad_bug", "zad_preg", "zad_copy", "zad_bug", "zad_preg", "zad_ok");
$stat_tekst = array("Bug u programu", "Pregled u toku", "Zadaća prepisana", "Bug u programu", "Pregled u toku", "Zadaća OK");


?>


<!-- zadace -->

<b>Zadaće:</b><br/>
<table cellspacing="0" cellpadding="2" border="0" id="zadace">
	<thead>
		<tr>
<?


$q100 = myquery("select count(*) from studentski_modul_predmet as smp, studentski_modul as sm where smp.predmet=$predmet and smp.akademska_godina=$ag and smp.aktivan=1 and smp.studentski_modul=sm.id and sm.modul='student/zadaca'");

$total_max_bodova = 0;

// Prikaz sa predmete kod kojih nije aktivno slanje zadaća
if (mysql_result($q100,0,0)==0) {
	// U pravilu ovdje ima samo jedan zadatak, pa ćemo sumirati
	$idovi_zadaca = array();
	$max_bodova_zadaca = array();
	$q110 = myquery("select id, naziv, zadataka, bodova from zadaca where predmet=$predmet and akademska_godina=$ag order by komponenta, naziv");
	while ($r110 = mysql_fetch_row($q110)) {
		$idovi_zadaca[] = $r110[0];
		$brzad[$r110[0]] = $r110[2];
		$naziv_zadace = $r110[1];
		$max_bodova_zadaca[$r110[0]] = $r110[3];
		if (!preg_match("/\w/",$naziv_zadace)) $naziv_zadace = "[Bez naziva]";
		?><td><?=$naziv_zadace?></td><?
	}
?>
		<td><b>Ukupno bodova</b></td>
		</tr>
	</thead>
<tbody>
<?
	$uk_bodova=0;
	foreach ($idovi_zadaca as $zadaca) {
		$bodova=0;
		$status=-1;
		for ($zadatak=1; $zadatak<=$brzad[$zadaca]; $zadatak++) {
			$q120 = myquery("select status,bodova from zadatak where student=$userid and zadaca=$zadaca and redni_broj=$zadatak order by id desc limit 1");
			if (mysql_num_rows($q120)>0) {
				$status = mysql_result($q120,0,0);
				$bodova += mysql_result($q120,0,1);
			}
		}
		if ($status==-1) {
		?>
		<td>&nbsp;</td>
		<?
		} else {
		?>
		<td><img src="images/16x16/<?=$stat_icon[$status]?>.png" width="16" height="16" border="0" align="center" title="<?=$stat_tekst[$status]?>" alt="<?=$stat_tekst[$status]?>"> <?=$bodova?> / <?=$max_bodova_zadaca[$zadaca]?></td>
		<?
		}
		$uk_bodova+=$bodova;
		$total_max_bodova += $max_bodova_zadaca[$zadaca];
	}
	?>
	<td><?=$uk_bodova?> / <?=$total_max_bodova?></td></tr>
</tbody>
</table>

&nbsp;<br/>

	<?


// Prikaz sa aktivnim slanjem
} else { // if (mysql_result($q100...

?>
	<td>&nbsp;</td>
<?

// Zaglavlje tabele - potreban nam je max. broj zadataka u zadaći

$q20 = myquery("select zadataka, postavka_zadace from zadaca where predmet=$predmet and akademska_godina=$ag");
$ima_postavka = false;
$broj_zadataka = 0;
while ($r20 = mysql_fetch_row($q20)) {
	if ($r20[0]>$broj_zadataka) $broj_zadataka=$r20[0];
	if (preg_match("/\w/", $r20[1])) $ima_postavka=true;
}

for ($i=1;$i<=$broj_zadataka;$i++) {
	?><td>Zadatak <?=$i?>.</td><?
}

?>
		<td><b>Ukupno bodova</b></td>
		<td><b>Mogućih</b></td>
		<? if ($ima_postavka) { ?><td><b>Postavka zadaća</b></td><? } ?>
		<td><b>PDF</b></td>
		</tr>
	</thead>
<tbody>
<?


// Tijelo tabele

// LEGENDA STATUS POLJA:
// 0 - nepoznat status
// 1 - nova zadaća
// 2 - prepisana
// 3 - ne može se kompajlirati
// 4 - prošla test, predstoji kontrola
// 5 - pregledana


/* Ovo se sve moglo kroz SQL rijesiti, ali necu iz razloga:
1. PHP je citljiviji
2. MySQL <4.1 ne podrzava subqueries */


$bodova_sve_zadace=$total_max_bodova=0;

$q21 = myquery("select id, naziv, bodova, zadataka, programskijezik, attachment, postavka_zadace from zadaca where predmet=$predmet and akademska_godina=$ag order by komponenta,id");
while ($r21 = mysql_fetch_row($q21)) {
	$zadaca = $r21[0];
	$max_bodova_zadaca = $r21[2];
	$total_max_bodova += $max_bodova_zadaca;
	$zzadataka = $r21[3];
	$postavka_zadace = $r21[6];
	?><tr>
	<th><?=$r21[1]?></th>
	<?
	$bodova_zadaca = 0;
	$slao_zadacu = false;

	for ($zadatak=1;$zadatak<=$broj_zadataka;$zadatak++) {
		// Ako tekuća zadaća nema toliko zadataka, ispisujemo blank polje
		if ($zadatak>$zzadataka) {
			?><td>&nbsp;</td><?
			continue;
		}

		// Uzmi samo rjesenje sa zadnjim IDom
		$q22 = myquery("select status,bodova,komentar from zadatak where student=$userid and zadaca=$zadaca and redni_broj=$zadatak order by id desc limit 1");
		if (mysql_num_rows($q22)<1) {
			?><td><a href="?sta=student/zadaca&predmet=<?=$predmet?>&ag=<?=$ag?>&zadaca=<?=$zadaca?>&zadatak=<?=$zadatak?>"><img src="images/16x16/zad_novi.png" width="16" height="16" border="0" align="center" title="Novi zadatak" alt="Novi zadatak"></a></td><?
		} else {
			$slao_zadacu = true;
			$status = mysql_result($q22,0,0);
			$bodova_zadatak = mysql_result($q22,0,1);
			$bodova_zadaca += $bodova_zadatak;
			if (strlen(mysql_result($q22,0,2))>2)
				$imakomentar = "<img src=\"images/16x16/komentar.png\"  width=\"15\" height=\"14\" border=\"0\" title=\"Ima komentar\" alt=\"Ima komentar\" align=\"center\">";
			else
				$imakomentar = "";
			?><td><a href="?sta=student/zadaca&predmet=<?=$predmet?>&ag=<?=$ag?>&zadaca=<?=$zadaca?>&zadatak=<?=$zadatak?>"><img src="images/16x16/<?=$stat_icon[$status]?>.png" width="16" height="16" border="0" align="center" title="<?=$stat_tekst[$status]?>" alt="<?=$stat_tekst[$status]?>"> <?=$bodova_zadatak?> <?=$imakomentar?></a></td>
	<?
		}
	}
	
	?>
	<td><?=$bodova_zadaca?></td><td><?=$max_bodova_zadaca?></td><td>
	<?
	
	// Link za download postavke zadaće
	if ($ima_postavka) {
		if ($postavka_zadace != "") {
			?><a href="?sta=common/attachment&zadaca=<?=$zadaca?>&tip=postavka"><img src="images/16x16/preuzmi.png" width="16" height="16" border="0"></a><?
		} else { print "&nbsp;"; }
		print "</td><td>\n";
	}

	// Download zadaće u PDF formatu - sada je moguć i za attachmente
	if ($slao_zadacu) {
		?><a href="?sta=student/zadacapdf&zadaca=<?=$zadaca?>" target="_new"><img src="images/16x16/pdf.png" width="16" height="16" border="0"></a><?
	} else { print "&nbsp;"; }
	?>
	</td></tr>
	<?
	
	$bodova_sve_zadace += $bodova_zadaca;
}


// Ukupno bodova za studenta
 
$bodova += $bodova_sve_zadace;
$mogucih += $total_max_bodova;

?>
	<tr><td colspan="<?=$broj_zadataka+1?>" align="right">UKUPNO: </td>
	<td><?=$bodova_sve_zadace?></td><td><?=$total_max_bodova?></td><td>&nbsp;</td>
	<? if ($ima_postavka) { ?><td>&nbsp;</td><? } ?></tr>
</tbody>
</table>

<p>Za ponovno slanje zadatka, kliknite na sličicu u tabeli iznad. <a href="#" onclick="javascript:window.open('legenda-zadace.html','blah6','width=320,height=130');">Legenda simbola</a></p>
<br/>

<!-- end zadace -->

<?

} // else




//  ISPITI

?>

<!-- ispiti -->

<b>Ispiti:</b><br/>

<?
	

$q30 = myquery("select i.id,UNIX_TIMESTAMP(i.datum),k.gui_naziv,k.id from ispit as i, komponenta as k where i.predmet=$predmet and i.akademska_godina=$ag and i.komponenta=k.id order by i.datum,i.komponenta");
if (mysql_num_rows($q30) == 0) {
	print "<p>Nije bilo parcijalnih ispita.</p>";
}

while ($r30 = mysql_fetch_row($q30)) {
	$q40 = myquery("select ocjena from ispitocjene where ispit=$r30[0] and student=$userid");
	if (mysql_num_rows($q40)<1) {
//		print "Nije izašao/izašla";
	} else {
		?><p><?=$r30[2]?> (<?=date("d. m. Y",$r30[1])?>): <b><?=mysql_result($q40,0,0)?> bodova</b></p><?
	}
}


//  FIKSNE KOMPONENTE

$q400 = myquery("SELECT k.gui_naziv, kb.bodovi FROM komponenta as k, komponentebodovi as kb WHERE k.tipkomponente=5 AND k.id=kb.komponenta AND kb.student=$userid AND kb.predmet=$ponudakursa");
if (mysql_num_rows($q400)>0) {
	?>

	<!-- ostalo -->

	<b>Ostalo:</b><br/>

	<?
}
while ($r400 = mysql_fetch_row($q400)) {
	?><p><?=$r400[0]?>: <b><?=$r400[1]?> bodova</b></p><?
}


// KONAČNA OCJENA

$q50 = myquery("select ocjena from konacna_ocjena where student=$userid and predmet=$predmet and akademska_godina=$ag");
if (mysql_num_rows($q50)>0) {
	?>
	<center>
		<table width="100px" style="border-width: 3px; border-style: solid; border-color: silver">
			<tr><td align="center">
				KONAČNA OCJENA<br/>
				<font size="6"><b><?=mysql_result($q50,0,0)?></b></font>
			</td></tr>
		</table>
	</center>
	<?
}


}

?>
