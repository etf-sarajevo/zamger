<?

// IZVJESTAJ/ISPIT - statistika pojedinacnog ispita

// v3.9.1.0 (2008/04/11) + Izvjestaj izdvojen iz bivseg admin_izvjestaj.php




// NAPOMENA: Sumarne statistike rade samo za predmete tipa "ETF Bologna standard", odnosno 
// predmete koji imaju standardni I i II parcijalni i Integralni. IDovi komponenti su 
// ukodirani.


function izvjestaj_ispit() {

global $userid,$user_nastavnik,$user_studentska,$user_siteadmin;



?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>
<?



$predmet = intval($_REQUEST['predmet']);


// Provjera permisija

if (!$user_nastavnik && !$user_studentska && !$user_siteadmin) {
	biguglyerror("Nemate permisije za pristup ovom izvještaju");
	zamgerlog ("Pristup izvjestaju a nije NBA",3); // 3 = error
	return;
}
if (!$user_studentska && !$user_siteadmin) {
	$q2 = myquery("select admin from nastavnik_predmet where nastavnik=$userid and predmet=$predmet");
	if (mysql_num_rows($q2) < 1) {
		biguglyerror("Nemate permisije za pristup ovom izvještaju");
		zamgerlog ("Nije admin predmeta",3); // 3 = error
		return;
	}
}



$ispit = intval($_REQUEST['ispit']);
if ($_REQUEST['ispit'] == "svi") $ispit=-1;


// Naziv predmeta, akademska godina
$q10 = myquery("select p.naziv,ag.naziv from predmet as p, ponudakursa as pk, akademska_godina as ag where pk.id=$predmet and ag.id=pk.akademska_godina and pk.predmet=p.id");
print "<p>&nbsp;</p><h1>".mysql_result($q10,0,0)." ".mysql_result($q10,0,1)."</h1>\n";


// Tip ispita, datum i opis

if ($ispit==-1) {
	print "<h3>Sumarna statistika za sve ispite</h3>\n";
} else {
	$q20 = myquery("select UNIX_TIMESTAMP(i.datum),k.gui_naziv,i.naziv,k.maxbodova,k.prolaz from ispit as i, komponenta as k where i.id=$ispit and i.komponenta=k.id");
	if (mysql_num_rows($q20)<1) {
		biguglyerror("Nepoznat ispit!");
		zamgerlog ("Pristup ispitu kojeg nema u bazi",3);
		return;
	}

	print "<h3>".mysql_result($q20,0,1).", ".date("d. m. Y.", mysql_result($q20,0,0));
	$opis = mysql_result($q20,0,2);
	if (preg_match("/\w/",$opis)) print " ($opis)";
	print "</h3>\n";
	$maxbodova=mysql_result($q20,0,3);
	$prolaz=mysql_result($q20,0,4);
}


// Opste statistike (sumarno za predmet)

if ($ispit==-1) {
	$q30 = myquery("select count(*) from student_predmet where predmet=$predmet");
	$slusa_predmet = mysql_result($q30,0,0);

	$q40 = myquery("select id from ispit where predmet=$predmet");
	$odrzano_ispita = mysql_num_rows($q40);

	if ($odrzano_ispita>0) {
		$ispiti=array();
		while ($r40 = mysql_fetch_row($q40)) array_push($ispiti,$r40[0]);

		$q50 = myquery("select count(*) from konacna_ocjena where predmet=$predmet and ocjena>5");
		$polozilo = mysql_result($q50,0,0);

		$q60 = myquery("select count(*) from student_predmet as sp where sp.predmet=$predmet and (select count(*) from ispit as i, ispitocjene as io where i.predmet=$predmet and io.ispit=i.id and io.student=sp.student)=0");
		$nisu_izlazili = mysql_result($q60,0,0);
		$stvarno_slusa = $slusa_predmet-$nisu_izlazili;

		// Ako predmet nije bologna standard, daljnje statistike nemaju smisla
		$q70 = myquery("select tpk.komponenta from tippredmeta_komponenta as tpk, ponudakursa as pk where pk.id=$predmet and pk.tippredmeta=tpk.tippredmeta");
		$bologna=0;
		while ($r70 = mysql_fetch_row($q70)) {
			if ($r70[0]==1) $bologna++;
			if ($r70[0]==2) $bologna++;
			if ($r70[0]==3) $bologna++;
		}

		if ($bologna>=3) {
			$q80 = myquery("select count(*) from student_predmet as sp where sp.predmet=$predmet and (select count(*) from ispitocjene as io, ispit as i where i.predmet=$predmet and i.komponenta=1 and io.ispit=i.id and io.student=sp.student and io.ocjena>=10)>0");
			$prvaparc = mysql_result($q80,0,0);

			$q90 = myquery("select count(*) from student_predmet as sp where sp.predmet=$predmet and (select count(*) from ispitocjene as io, ispit as i where i.predmet=$predmet and i.komponenta=2 and io.ispit=i.id and io.student=sp.student and io.ocjena>=10)>0");
			$drugaparc = mysql_result($q90,0,0);

			$q100 = myquery("select count(*) from student_predmet as sp where sp.predmet=$predmet and (select count(*) from ispitocjene as io, ispit as i where i.predmet=$predmet and i.komponenta=3 and io.ispit=i.id and io.student=sp.student and io.ocjena>=20)>0");
			$intparc = mysql_result($q100,0,0);

			$q110 = myquery("select count(*) from student_predmet as sp where sp.predmet=$predmet and (select count(*) from ispitocjene as io, ispit as i where i.predmet=$predmet and i.komponenta=1 and io.ispit=i.id and io.student=sp.student and io.ocjena>=10)>0 and (select count(*) from ispitocjene as io, ispit as i where i.predmet=$predmet and i.komponenta=2 and io.ispit=i.id and io.student=sp.student and io.ocjena>=10)>0");
			$objeparc = mysql_result($q110,0,0);

			$zad_uslove = $intparc+$objeparc;
		}
	}

	?>
	<p>Ukupno upisalo predmet: <b><?=$slusa_predmet?></b> studenata.<br/>
	<?

	if ($odrzano_ispita==0) {
		?>Nije održan nijedan ispit.</p><?
		return;
	}
	else if ($bologna<3) {
		?>
		Nije izašlo ni na jedan ispit (pretpostavka je da ne slušaju predmet, biće isključeni iz daljnjih statistika): <b><?=$nisu_izlazili?></b> studenata.<br/>
		Položilo (konačna ocjena 6 ili više): <b><?=$polozilo?></b> studenata (<b><?=procenat($polozilo,$stvarno_slusa)?></b>).<br/>
		Predmet nije Bologna standard tako da daljnje statistike nisu dostupne.</p>
		<?
		return;
	} else {
		?>
		Nije izašlo ni na jedan ispit (pretpostavka je da ne slušaju predmet, biće isključeni iz daljnjih statistika): <b><?=$nisu_izlazili?></b> studenata.<br/>
		Položilo (konačna ocjena 6 ili više): <b><?=$polozilo?></b> studenata (<b><?=procenat($polozilo,$stvarno_slusa)?></b>).<br/>
		Zadovoljilo uslove za usmeni: <b><?=$zad_uslove?></b> studenata (<b><?=procenat($zad_uslove,$stvarno_slusa)?></b>).<br/><br/>
		Položilo I parcijalni ispit: <b><?=$prvaparc?></b> studenata  (<b><?=procenat($prvaparc,$stvarno_slusa)?></b>).<br/>
		Položilo II parcijalni ispit: <b><?=$drugaparc?></b> studenata  (<b><?=procenat($drugaparc,$stvarno_slusa)?></b>).<br/>
		Položilo oba parcijalna ispita: <b><?=$objeparc?></b> studenata  (<b><?=procenat($objeparc,$stvarno_slusa)?></b>).<br/>
		Položilo ispit integralno: <b><?=$intparc?></b> studenata  (<b><?=procenat($intparc,$stvarno_slusa)?></b>).</p>
		<?
		return;
	}
}


// Opste statistike - pojedinacni ispit

$q200 = myquery("select count(*) from ispitocjene where ispit=$ispit");
$ukupno_izaslo = mysql_result($q200,0,0);

$q210 = myquery("select count(*) from ispitocjene where ispit=$ispit and ocjena>=$prolaz");
$polozilo = mysql_result($q210,0,0);

$q220 = myquery("select count(*) from student_predmet where predmet=$predmet");
$slusa_predmet = mysql_result($q220,0,0);

?>
<p>Ukupno izašlo studenata: <b><?=$ukupno_izaslo?></b><br/>
Položilo: <b><?=$polozilo?></b><br/>
Prolaznost: <b><?=procenat($polozilo,$ukupno_izaslo)?></b></p>

<p>Od studenata koji slušaju predmet, nije izašlo: <b><?=($slusa_predmet-$ukupno_izaslo)?></b></p><?


// Po broju bodova

if ($maxbodova==20) { $rezolucija="0.5"; } else { $rezolucija="1"; }
print "<p>Distribucija po broju bodova:<br/>(Svaki stupac predstavlja broj studenata sa određenim brojem bodova. Rezolucija je $rezolucija bodova)</p>";

// Odredjivanje max. broja studenata po koloni radi skaliranja grafa
$max = 0;
for ($i=0; $i<=$maxbodova; $i+=$rezolucija) {
	$q300 = myquery("select COUNT( * ) FROM ispitocjene WHERE ispit=$ispit and ocjena>=$i and ocjena<".($i+$rezolucija));
	$studenata = mysql_result($q300,0,0);
	if ($studenata>$max) $max=$studenata;
}
$koef = 80/$max;

?><table border="0" cellspacing="0" cellpadding="0"><tr><?
for ($i=0; $i<=$maxbodova; $i+=$rezolucija) {
	$q310 = myquery("select COUNT( * ) FROM ispitocjene WHERE ispit=$ispit and ocjena>=$i and ocjena<".($i+$rezolucija));
	$height = intval(mysql_result($q310,0,0) * $koef);
	?><td width="10">
		<table width="10" border="0" cellspacing="0" cellpadding="0">
			<tr><td>
				<img src="images/fnord.gif" width="1" height="<?=(100-$height)?>">
			</td></tr><tr><td bgcolor="#FF0000">
				<img src="images/fnord.gif" width="1" height="<?=$height?>">
			</td></tr>
		</table>
	</td><td>&nbsp;</td><?
}
?>
</tr></table>
<?


// Prolaznost po grupama

$q315 = myquery("select count(*) from labgrupa where predmet=$predmet");
if (mysql_result($q315,0,0)<2) {
	// Nema grupa, preskacemo ostatak izvjestaja
	return;
}

$ukupno = array(); $polozilo = array(); $prosjek = array(); $grupe = array();
$maxprol = 0; $maxprosj = 0;

$q320 = myquery("select l.id,io.ocjena,l.naziv FROM ispitocjene as io, student_labgrupa as sl, labgrupa as l, ispit as i WHERE io.ispit=$ispit and io.student=sl.student and sl.labgrupa=l.id and l.predmet=i.predmet and i.id=io.ispit order by l.id");
while ($r320 = mysql_fetch_row($q320)) {
	$ukupno[$r320[0]]++;
	if ($r320[1]>=$prolaz) $polozilo[$r320[0]]++;
	$prosjek[$r320[0]] = ($prosjek[$r320[0]]*($ukupno[$r320[0]]-1) + $r320[1]) / $ukupno[$r320[0]];
	$grupe[$r320[0]] = $r320[2];
	if ($prosjek[$r320[0]]>$maxprosj) $maxprosj=$prosjek[$r320[0]];
	$prolaznost = $polozilo[$r320[0]]/$ukupno[$r320[0]];
	if ($prolaznost>$maxprol) $maxprol=$prolaznost;
}

print "<p>Prolaznost po grupama:</p>";
$koef = 80/$maxprol;
?><table border="0" cellspacing="0" cellpadding="0"><tr><?
foreach ($grupe as $id => $naziv) {
	$height = intval($polozilo[$id]/$ukupno[$id] * $koef);
	$label = intval($polozilo[$id]/$ukupno[$id] * 100) . "%";
	?><td width="50" valign="top">
		<table width="50" border="0" cellspacing="0" cellpadding="0">
			<tr><td align="center"><?=$label?></td></tr>
			<tr><td>
				<img src="images/fnord.gif" width="1" height="<?=(100-$height)?>">
			</td></tr><tr><td bgcolor="#FF0000">
				<img src="images/fnord.gif" width="1" height="<?=$height?>">
			</td></tr>
			<tr><td align="center"><?=$naziv?></td></tr>
		</table>
	</td><td width="10">&nbsp;</td><?
}
print "</tr></table>\n";


// Broj bodova po grupama

print "<p>Prosječan broj bodova po grupama:</p>";
$koef = 80/$maxprosj;
?><table border="0" cellspacing="0" cellpadding="0"><tr><?
foreach ($grupe as $id => $naziv) {
	$height = intval($prosjek[$id] * $koef);
	$label = intval($prosjek[$id]*10) / 10;
	?><td width="50" valign="top">
		<table width="50" border="0" cellspacing="0" cellpadding="0">
			<tr><td align="center"><?=$label?></td></tr>
			<tr><td>
				<img src="images/fnord.gif" width="1" height="<?=(100-$height)?>">
			</td></tr><tr><td bgcolor="#FF0000">
				<img src="images/fnord.gif" width="1" height="<?=$height?>">
			</td></tr>
			<tr><td align="center"><?=$naziv?></td></tr>
		</table>
	</td><td width="10">&nbsp;</td><?
}
print "</tr></table>\n";




}

?>