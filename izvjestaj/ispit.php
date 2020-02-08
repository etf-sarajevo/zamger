<?

// IZVJESTAJ/ISPIT - statistika pojedinacnog ispita


// Provjeriti ispravnost dijela sa grupama



function izvjestaj_ispit() {

global $userid,$user_nastavnik,$user_studentska,$user_siteadmin;

require_once("lib/utility.php"); // procenat


?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>
<p>Datum i vrijeme izvještaja: <?=date("d. m. Y. H:i");?></p>
<?



// Parametar

$ispit = intval($_REQUEST['ispit']);
if ($_REQUEST['ispit'] == "svi") {
	// Privremeno vrsim redirekciju na izvjestaj/statistika_predmeta
	$ponudakursa = intval($_REQUEST['predmet']);
	$qtmp = db_query("select predmet, akademska_godina from ponudakursa where id=$ponudakursa");
	$predmet = db_result($qtmp,0,0);
	$ag = db_result($qtmp,0,1);
	?>
	<script language="JavaScript">
	location.href='?sta=izvjestaj/statistika_predmeta&predmet=<?=$predmet?>&ag=<?=$ag?>';
	</script>
	<?
	return;
}


// Elementarna provjera privilegija

/*if (!$user_nastavnik && !$user_studentska && !$user_siteadmin) {
	biguglyerror("Nemate permisije za pristup ovom izvještaju");
	zamgerlog ("pristup izvjestaju a nije NBA",3); // 3 = error
	return;
}*/


// Upit za ispit

$q10 = db_query("select UNIX_TIMESTAMP(i.datum), k.gui_naziv, k.maxbodova, k.prolaz, i.predmet, i.akademska_godina from ispit as i, komponenta as k where i.id=$ispit and i.komponenta=k.id");
if (db_num_rows($q10)<1) {
	biguglyerror("Nepoznat ispit!");
	zamgerlog ("nepoznat ispit $ispit",3);
	zamgerlog2 ("nepoznat ispit", $ispit);
	return;
}

$finidatum = date("d. m. Y.", db_result($q10,0,0));
$naziv = db_result($q10,0,1);
$maxbodova = db_result($q10,0,2);
$prolaz = db_result($q10,0,3);
$predmet = db_result($q10,0,4);
$ag = db_result($q10,0,5);


// Dodatna provjera privilegija
if (!$user_studentska && !$user_siteadmin) {
	$q20 = db_query("select nivo_pristupa from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
	if (db_num_rows($q20) < 1) {
		biguglyerror("Nemate permisije za pristup ovom izvještaju");
		zamgerlog ("nije admin predmeta pp$predmet godina ag$ag",3); // 3 = error
		zamgerlog2 ("nije saradnik na predmetu", intval($predmet), intval($ag)); // 3 = error
		return;
	}
}



// Naziv predmeta, akademska godina
$q21 = db_query("select naziv from predmet where id=$predmet");
$q22 = db_query("select naziv from akademska_godina where id=$ag");

?>
	<p>&nbsp;</p>
	<h1><?=db_result($q21,0,0)?> <?=db_result($q22,0,0)?></h1>
	<h3><?=$naziv?>, <?=$finidatum?></h3>
<?


// Opste statistike - pojedinacni ispit

$q200 = db_query("select count(*) from ispitocjene where ispit=$ispit");
$ukupno_izaslo = db_result($q200,0,0);

$q210 = db_query("select count(*) from ispitocjene where ispit=$ispit and ocjena>=$prolaz");
$polozilo = db_result($q210,0,0);

$q220 = db_query("select count(*) from student_predmet as sp, ponudakursa as pk where sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
$slusa_predmet = db_result($q220,0,0);

?>
<p>Ukupno izašlo studenata: <b><?=$ukupno_izaslo?></b><br/>
Položilo: <b><?=$polozilo?></b><br/>
Prolaznost: <b><?=procenat($polozilo,$ukupno_izaslo)?></b></p>

<p>Od studenata koji slušaju predmet, nije izašlo: <b><?=($slusa_predmet-$ukupno_izaslo)?></b></p><?


// Po broju bodova

if ($maxbodova==20) { $rezolucija="0.5"; } else { $rezolucija="1"; }
print "\n<p>Distribucija po broju bodova:<br/>(Svaki stupac predstavlja broj studenata sa određenim brojem bodova. Rezolucija je $rezolucija bodova)</p>\n\n";

print "<SCRIPT>var studenataSaBodovima=[ ";

// Odredjivanje max. broja studenata po koloni radi skaliranja grafa
$max = 0;
for ($i=0; $i<=$maxbodova; $i+=$rezolucija) {
	$q300 = db_query("select COUNT( * ) FROM ispitocjene WHERE ispit=$ispit and ocjena>=$i and ocjena<".($i+$rezolucija));
	$studenata = db_result($q300,0,0);
	print "$studenata, ";
	if ($studenata>$max) $max=$studenata;
}
if ($max>0) $koef = 80/$max; else $koef=80;
print " ];</SCRIPT>\n\n";

?><table border="0" cellspacing="0" cellpadding="0"><tr><?
for ($i=0; $i<=$maxbodova; $i+=$rezolucija) {
	$q310 = db_query("select COUNT( * ) FROM ispitocjene WHERE ispit=$ispit and ocjena>=$i and ocjena<".($i+$rezolucija));
	$height = intval(db_result($q310,0,0) * $koef);
	?><td width="10">
		<table width="10" border="0" cellspacing="0" cellpadding="0">
			<tr><td>
				<img src="static/images/fnord.gif" width="1" height="<?=(100-$height)?>">
			</td></tr><tr><td bgcolor="#FF0000">
				<img src="static/images/fnord.gif" width="1" height="<?=$height?>">
			</td></tr>
		</table>
	</td><td>&nbsp;</td><?
}
?>
</tr></table>
<?


// Prolaznost po grupama

$q315 = db_query("select count(*) from labgrupa where predmet=$predmet and akademska_godina=$ag and virtualna=0");
if (db_result($q315,0,0)<2) {
	// Nema grupa, preskacemo ostatak izvjestaja
	return;
}

$ukupno = array(); $polozilo = array(); $prosjek = array(); $grupe = array();
$maxprol = 0; $maxprosj = 0;

$q320 = db_query("select l.id,io.ocjena,l.naziv FROM ispitocjene as io, student_labgrupa as sl, labgrupa as l, ispit as i WHERE io.ispit=$ispit and io.student=sl.student and sl.labgrupa=l.id and i.id=io.ispit and l.predmet=i.predmet and l.akademska_godina=i.akademska_godina and l.virtualna=0 order by l.id");
while ($r320 = db_fetch_row($q320)) {
	$grupe[$r320[0]] = $r320[2]; // Nazivi grupa

	$ukupno[$r320[0]]++;
	if ($r320[1]>=$prolaz) $polozilo[$r320[0]]++;

	$prosjek[$r320[0]] = ($prosjek[$r320[0]]*($ukupno[$r320[0]]-1) + $r320[1]) / $ukupno[$r320[0]];
	if ($prosjek[$r320[0]]>$maxprosj) $maxprosj=$prosjek[$r320[0]];

	$prolaznost = $polozilo[$r320[0]]/$ukupno[$r320[0]];
	if ($prolaznost>$maxprol) $maxprol=$prolaznost;
}

print "<p>Prolaznost po grupama:</p>";
if ($maxprol > 0) $koef = 80/$maxprol; else $koef = 0;
?><table border="0" cellspacing="0" cellpadding="0"><tr><?
foreach ($grupe as $id => $naziv) {
	$height = intval($polozilo[$id]/$ukupno[$id] * $koef);
	$label = intval($polozilo[$id]/$ukupno[$id] * 100) . "%";
	?><td width="50" valign="top">
		<table width="50" border="0" cellspacing="0" cellpadding="0">
			<tr><td align="center"><?=$label?></td></tr>
			<tr><td>
				<img src="static/images/fnord.gif" width="1" height="<?=(100-$height)?>">
			</td></tr><tr><td bgcolor="#FF0000">
				<img src="static/images/fnord.gif" width="1" height="<?=$height?>">
			</td></tr>
			<tr><td align="center"><?=$naziv?></td></tr>
		</table>
	</td><td width="10">&nbsp;</td><?
}
print "</tr></table>\n";


// Broj bodova po grupama

print "<p>Prosječan broj bodova po grupama:</p>";
if ($maxprosj > 0) $koef = 80/$maxprosj; else $koef = 0;
?><table border="0" cellspacing="0" cellpadding="0"><tr><?
foreach ($grupe as $id => $naziv) {
	$height = intval($prosjek[$id] * $koef);
	$label = intval($prosjek[$id]*10) / 10;
	?><td width="50" valign="top">
		<table width="50" border="0" cellspacing="0" cellpadding="0">
			<tr><td align="center"><?=$label?></td></tr>
			<tr><td>
				<img src="static/images/fnord.gif" width="1" height="<?=(100-$height)?>">
			</td></tr><tr><td bgcolor="#FF0000">
				<img src="static/images/fnord.gif" width="1" height="<?=$height?>">
			</td></tr>
			<tr><td align="center"><?=$naziv?></td></tr>
		</table>
	</td><td width="10">&nbsp;</td><?
}
print "</tr></table>\n";




}

?>
