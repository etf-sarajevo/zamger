<?

// STUDENT/PREDMET - statusna stranica predmeta



function student_predmet() {

	global $userid, $courseDetails;
	
	
	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']); // akademska godina
	
	$course = [];
	foreach($courseDetails as $courseDetail) {
		if ($courseDetail['CourseOffering']['CourseUnit']['id'] == $predmet && $courseDetail['CourseOffering']['AcademicYear']['id'] == $ag)
			$course = $courseDetail;
	}
	if (empty($course)) {
		zamgerlog("nepoznat predmet $predmet",3); // nivo 3: greska
		zamgerlog2("nepoznat predmet", $predmet);
		biguglyerror("Nepoznat predmet");
		return;
	}

	$ponudakursa = $course['CourseOffering']['id'];
	
	if (array_key_exists('name', $course['CourseOffering']['CourseDescription']))
		$courseName = $course['CourseOffering']['CourseDescription']['name'];
	else
		$courseName = $course['CourseOffering']['CourseUnit']['name'];
	$year = $course['CourseOffering']['AcademicYear']['name'];


	?>
	<br/>
	<p style="font-size: small;">Predmet: <b><?=$courseName?> (<?=$year?>)</b><br/>
	<?

	// Određivanje labgrupe
	$groups = api_call("group/course/$predmet/student/$userid", ["year" => $ag])['results'];
	foreach($groups as $group) {
		if (!$group['virtual']) {
			?>Grupa: <b><?=$group['name']?></b></p><?
		}
	}
	
	print "<br/>\n";

	foreach($course['CourseUnitYear']['staff'] as $staff) {
		$name = $staff['Person']['name'] . " " . $staff['Person']['surname'];
		if ($staff['Person']['titlesPre']) $name = $staff['Person']['titlesPre'] . " $name";
		if ($staff['Person']['titlesPost']) $name .= " " . $staff['Person']['titlesPost'];
		$email = $staff['Person']['email'];
		
		print "<b>".ucfirst($staff['status'])."</b>: <a href=\"mailto:$email\">$name</a><br/>";
	}

	
	// PROGRESS BAR
	
	// Sumiramo bodove po komponentama i računamo koliko je bilo moguće ostvariti
	$ukupno_bodova = $ukupno_mogucih = 0;

	foreach($course['score'] as $komponenta) {
		$ukupno_bodova += $komponenta['score'];
		
		foreach($course['CourseUnitYear']['activities'] as $act) {
			if ($act['id'] == $komponenta['CourseActivity']['id']) {
				$tip_komponente = $act['Activity']['id'];
				$opcije_komponente = $act['options'];
				$kmax = $act['points'];
			}
		}
		
		// Za neke komponente imamo poseban kod koliko je bilo moguće ostvariti
		if ($tip_komponente == 2) { // Tip komponente: zadaće
			/*$do_sada_zadace = db_get("select sum(bodova) from zadaca where predmet=$predmet and akademska_godina=$ag and komponenta=$id_komponente");
			$do_sada_zadace = round($do_sada_zadace, 2);
			
			// Zbir bodova za zadaće ne može preći ono koliko nosi komponenta
			if ($do_sada_zadace > $komponenta_mogucih)
				$ukupno_mogucih += $komponenta_mogucih;
			else
				$ukupno_mogucih += $do_sada_zadace;*/
			$ukupno_mogucih += $kmax;
			
		} else if ($tip_komponente == 5 && in_array("Proportional", $opcije_komponente) && array_key_exists("TotalClasses", $opcije_komponente)) { // Prisustvo sa linearnim porastom
			//$casova = db_get("select count(*) from cas as c, labgrupa as l, prisustvo as p, ponudakursa as pk where c.labgrupa=l.id and l.predmet=pk.predmet and l.akademska_godina=pk.akademska_godina and pk.id=$ponudakursa and c.komponenta=$id_komponente and c.id=p.cas and p.student=$userid");
			//$ukupno_mogucih += $casova * $komponenta_mogucih / 13;
			$ukupno_mogucih += $kmax;
			
		} else
			$ukupno_mogucih += $kmax;
	}
	

	// Procenat nikada ne smije biti veći od 100%
	if ($ukupno_mogucih==0)
		$procenat = 0;
	else if ($ukupno_bodova > $ukupno_mogucih)
		$procenat = 100;
	else
		$procenat = intval(($ukupno_bodova/$ukupno_mogucih)*100);
	
	// boja označava napredak studenta
	if ($procenat>=75)
		$boja = "#00FF00";
	else if ($procenat>=50)
		$boja = "#FFFF00";
	else
		$boja = "#FF0000";
	
	// Crtamo tabelu koristeći dvije preskalirane slike
	$ukupna_sirina = 200;
	
	$tabela1 = $procenat * 2;
	$tabela2 = $ukupna_sirina - $tabela1;
	
	// Tekst "X bodova" ćemo upisati u onu stranu tabele koja je manja
	if ($tabela1 <= $tabela2) {
		$ispis1 = "<img src=\"static/images/fnord.gif\" width=\"$tabela1\" height=\"10\">";
		$ispis2 = "<img src=\"static/images/fnord.gif\" width=\"$tabela2\" height=\"1\"><br> $ukupno_bodova bodova";
	} else {
		$ispis1="<img src=\"static/images/fnord.gif\" width=\"$tabela1\" height=\"1\"><br> $ukupno_bodova bodova";
		$ispis2="<img src=\"static/images/fnord.gif\" width=\"$tabela2\" height=\"10\">";
	}


	?>
	
	
	<!-- progress bar -->
	
	<center><table border="0"><tr><td align="left">
	<p>Osvojili ste....<br/>
	<table style="border:1px;border-style:solid" width="206" cellpadding="0" cellspacing="2"><tr>
	<td width="<?=$tabela1?>" bgcolor="<?=$boja?>"><?=$ispis1?></td>
	<td width="<?=$tabela2?>" bgcolor="#FFFFFF"><?=$ispis2?></td></tr></table>
	
	<table width="208" border="0" cellspacing="0" cellpadding="0"><tr>
	<td width="68">0</td>
	<td align="center" width="68">50</td>
	<td align="right" width="69">100</td></tr></table>
	što je <?=$procenat?>% od trenutno mogućih <?=round($ukupno_mogucih,2) ?> bodova.</p>
	</td></tr></table></center>

	
	<!-- end progress bar -->
	<?





//  PRISUSTVO NA VJEŽBAMA


function prisustvo_ispis($idgrupe,$imegrupe,$komponenta) {
	global $userid;

	if (!preg_match("/\w/",$imegrupe)) $imegrupe = "[Bez naziva]";

	$odsustva=0;
	$q70 = db_query("select id,UNIX_TIMESTAMP(datum), vrijeme from cas where labgrupa=$idgrupe and komponenta=$komponenta order by datum, vrijeme");
	if (db_num_rows($q70)<1) return; // Ne ispisuj grupe u kojima nema registrovanih časova

	$datumi = $vremena = $statusi = "";
	while ($r70 = db_fetch_row($q70)) {
		$datumi .= "<td>".date("d.m",$r70[1])."</td>\n";
		list($sati,$minute,$sekunde) = explode(":", $r70[2]);
		$vremena .= "<td>$sati<sup>$minute</sup></td>\n";
		$q80 = db_query("select prisutan from prisustvo where student=$userid and cas=$r70[0]");
		if (db_num_rows($q80)<1) {
			$statusi .= "<td bgcolor=\"#FFFFCC\" align=\"center\">/</td>\n";
		} else if (db_result($q80,0,0)==1) {
			$statusi .= "<td bgcolor=\"#CCFFCC\" align=\"center\">DA</td>\n";
		} else {
			$statusi .= "<td bgcolor=\"#FFCCCC\" align=\"center\">NE</td>\n";
			$odsustva++;
		}
	}

	
	?>

	<b>Prisustvo (<?=$imegrupe?>):</b><br/>
	<table cellspacing="0" cellpadding="2" border="0" id="prisustvo" class="prisustvo">
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


// Izračunavamo broj bodova za svaku komponentu prisustva
$q40 = db_query("select k.id, k.maxbodova, k.prolaz, k.opcija from komponenta as k, tippredmeta_komponenta as tpk, akademska_godina_predmet as agp
where agp.predmet=$predmet and agp.tippredmeta=tpk.tippredmeta and agp.akademska_godina=$ag and tpk.komponenta=k.id and k.tipkomponente=3"); // 3 = prisustvo

while (db_fetch4($q40, $id_komponente, $max_bodova, $min_bodova, $parametar_komponente)) {
	$odsustva = $casova = 0;
	$labgrupe = db_query_vassoc("select l.id,l.naziv from labgrupa as l, student_labgrupa as sl where l.predmet=$predmet and l.akademska_godina=$ag and l.id=sl.labgrupa and sl.student=$userid");
	
	foreach($labgrupe as $id_grupe => $naziv_grupe) {
		$odsustva += prisustvo_ispis($id_grupe, $naziv_grupe, $id_komponente);
		$casova += db_get("select count(*) from cas where labgrupa=$id_grupe and komponenta=$id_komponente");
	}
	
	if ($parametar_komponente == -1) {
		if ($casova == 0) 
			$bodovi = 10;
		else
			$bodovi = $min_bodova + round(($max_bodova - $min_bodova) * (($casova - $odsustva) / $casova), 2 ); 
			
	} else if ($parametar_komponente == -2) { // Paraproporcionalni sistem TP
		if ($odsustva <= 2)
			$bodovi = $max_bodova;
		else if ($odsustva <= 2 + ($max_bodova - $min_bodova)/2)
			$bodovi = $max_bodova - ($odsustva-2)*2;
		else
			$bodovi = $min_bodova;

	} else if ($parametar_komponente == -3) { // Još jedan sistem TP
		$bodovi = ($max_bodova / 13) * ($casova - $odsustva);
	
	// Pozitivan parametar komponente je najveći dozvoljeni broj izostanaka za cut-off model
	} else if ($odsustva <= $parametar_komponente) {
		$bodovi = $max_bodova;
	} else {
		$bodovi = $min_bodova;
	}
	?><p>Ukupno na prisustvo imate <b><?=$bodovi?></b> bodova.</p>
	<?
}






//  ZADAĆE


// Statusne ikone:
$stat_icon = array("bug", "view", "copy", "bug", "view", "ok");
$stat_tekst = array("Bug u programu", "Pregled u toku", "Zadaća prepisana", "Bug u programu", "Pregled u toku", "Zadaća OK");

$slanje_zadaca_aktivno = db_get("select count(*) from studentski_modul_predmet as smp, studentski_modul as sm where smp.predmet=$predmet and smp.akademska_godina=$ag and smp.aktivan=1 and smp.studentski_modul=sm.id and sm.modul='student/zadaca'");

// Spisak komponenti zadaća
$q95 = db_query("select k.id,k.gui_naziv from komponenta as k, tippredmeta_komponenta as tpk, akademska_godina_predmet as agp
where agp.predmet=$predmet and agp.akademska_godina=$ag and agp.tippredmeta=tpk.tippredmeta and tpk.komponenta=k.id and k.tipkomponente=4"); // 4 = zadaće

while(db_fetch2($q95, $id_komponente, $naziv_komponente)) {

$ima_aktivnih_zadaca = db_get("SELECT COUNT(*) FROM zadaca WHERE predmet=$predmet and akademska_godina=$ag and komponenta=$id_komponente AND aktivna=1");


?>

<!-- zadace -->

<b><?=$naziv_komponente?>:</b><br/>
<table cellspacing="0" cellpadding="2" border="0" id="zadace<?=$id_komponente?>" class="zadace">
	<thead>
		<tr>
<?


$total_max_bodova = 0;

// Prikaz sa predmete kod kojih nije aktivno slanje zadaća
if ($slanje_zadaca_aktivno == 0 || $ima_aktivnih_zadaca == 0) {
	// U pravilu ovdje ima samo jedan zadatak, pa ćemo sumirati
	$idovi_zadaca = array();
	$max_bodova_zadaca = array();
	$q110 = db_query("select id, naziv, zadataka, bodova from zadaca where predmet=$predmet and akademska_godina=$ag and komponenta=$id_komponente order by naziv");
	while ($r110 = db_fetch_row($q110)) {
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
			$q120 = db_query("select status,bodova from zadatak where student=$userid and zadaca=$zadaca and redni_broj=$zadatak order by id desc limit 1");
			if (db_num_rows($q120)>0) {
				$status = db_result($q120,0,0);
				$bodova += db_result($q120,0,1);
			}
		}
		if ($status==-1) {
		?>
		<td>&nbsp;</td>
		<?
		} else {
		?>
		<td><img src="static/images/16x16/<?=$stat_icon[$status]?>.png" width="16" height="16" border="0" align="center" title="<?=$stat_tekst[$status]?>" alt="<?=$stat_tekst[$status]?>"> <?=$bodova?> / <?=$max_bodova_zadaca[$zadaca]?></td>
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
} else { // if ($slanje_zadaca_aktivno...

?>
	<td>&nbsp;</td>
<?

// Zaglavlje tabele - potreban nam je max. broj zadataka u zadaći

$q20 = db_query("select zadataka, postavka_zadace from zadaca where predmet=$predmet and akademska_godina=$ag and komponenta=$id_komponente");
$ima_postavka = false;
$broj_zadataka = 0;
while ($r20 = db_fetch_row($q20)) {
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

$q21 = db_query("select id, naziv, bodova, zadataka, programskijezik, attachment, postavka_zadace from zadaca where predmet=$predmet and akademska_godina=$ag and komponenta=$id_komponente order by id");
while ($r21 = db_fetch_row($q21)) {
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
		$q22 = db_query("select status,bodova,komentar from zadatak where student=$userid and zadaca=$zadaca and redni_broj=$zadatak order by id desc limit 1");
		if (db_num_rows($q22)<1) {
			?><td><a href="?sta=student/zadaca&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>&amp;zadaca=<?=$zadaca?>&amp;zadatak=<?=$zadatak?>"><img src="static/images/16x16/create_new.png" width="16" height="16" border="0" align="center" title="Novi zadatak" alt="Novi zadatak"></a></td><?
		} else {
			$slao_zadacu = true;
			$status = db_result($q22,0,0);
			$bodova_zadatak = db_result($q22,0,1);
			$bodova_zadaca += $bodova_zadatak;
			if (strlen(db_result($q22,0,2))>2)
				$imakomentar = "<img src=\"static/images/16x16/comment_yellow.png\"  width=\"15\" height=\"14\" border=\"0\" title=\"Ima komentar\" alt=\"Ima komentar\" align=\"center\">";
			else
				$imakomentar = "";
			?><td><a href="?sta=student/zadaca&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>&amp;zadaca=<?=$zadaca?>&amp;zadatak=<?=$zadatak?>"><img src="static/images/16x16/<?=$stat_icon[$status]?>.png" width="16" height="16" border="0" align="center" title="<?=$stat_tekst[$status]?>" alt="<?=$stat_tekst[$status]?>"> <?=$bodova_zadatak?> <?=$imakomentar?></a></td>
	<?
		}
	}
	
	?>
	<td><?=$bodova_zadaca?></td><td><?=$max_bodova_zadaca?></td><td>
	<?
	
	// Link za download postavke zadaće
	if ($ima_postavka) {
		if ($postavka_zadace != "") {
			?><a href="?sta=common/attachment&amp;zadaca=<?=$zadaca?>&amp;tip=postavka"><img src="static/images/16x16/download.png" width="16" height="16" border="0"></a><?
		} else { print "&nbsp;"; }
		print "</td><td>\n";
	}

	// Download zadaće u PDF formatu - sada je moguć i za attachmente
	if ($slao_zadacu) {
		?><a href="?sta=student/zadacapdf&amp;zadaca=<?=$zadaca?>" target="_new"><img src="static/images/16x16/pdf.png" width="16" height="16" border="0"></a><?
	} else { print "&nbsp;"; }
	?>
	</td></tr>
	<?
	
	$bodova_sve_zadace += $bodova_zadaca;
}


// Ukupno bodova za studenta

//$bodova += $bodova_sve_zadace;
//$mogucih += $total_max_bodova;

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


} // $q95 - petlja komponenti



//  ISPITI

?>

<!-- ispiti -->

<b>Ispiti:</b><br/>

<?
	

$q30 = db_query("select i.id,UNIX_TIMESTAMP(i.datum),k.gui_naziv,k.id from ispit as i, komponenta as k where i.predmet=$predmet and i.akademska_godina=$ag and i.komponenta=k.id order by i.datum,i.komponenta");
if (db_num_rows($q30) == 0) {
	print "<p>Nije bilo parcijalnih ispita.</p>";
}

while ($r30 = db_fetch_row($q30)) {
	$q40 = db_query("select ocjena from ispitocjene where ispit=$r30[0] and student=$userid");
	if (db_num_rows($q40)<1) {
//		print "Nije izašao/izašla";
	} else {
		?><p><?=$r30[2]?> (<?=date("d. m. Y",$r30[1])?>): <b><?=db_result($q40,0,0)?> bodova</b></p><?
	}
}


//  FIKSNE KOMPONENTE

$q400 = db_query("SELECT k.gui_naziv, kb.bodovi FROM komponenta as k, komponentebodovi as kb WHERE k.tipkomponente=5 AND k.id=kb.komponenta AND kb.student=$userid AND kb.predmet=$ponudakursa");
if (db_num_rows($q400)>0) {
	?>

	<!-- ostalo -->

	<b>Ostalo:</b><br/>

	<?
}
while ($r400 = db_fetch_row($q400)) {
	?><p><?=$r400[0]?>: <b><?=$r400[1]?> bodova</b></p><?
}


// KONAČNA OCJENA

$ocjena = db_get("select ocjena from konacna_ocjena where student=$userid and predmet=$predmet and akademska_godina=$ag");
if ($ocjena > 5) {
	if ($ocjena == 11) $ocjena = "Ispunio/la obaveze";
	if ($ocjena == 12) $ocjena = "Uspješno odbranio/la";
	?>
	<center>
		<table width="100px" style="border-width: 3px; border-style: solid; border-color: silver">
			<tr><td align="center">
				KONAČNA OCJENA<br/>
				<font size="6"><b><?=$ocjena?></b></font>
			</td></tr>
		</table>
	</center>
	<?
}


}

?>
