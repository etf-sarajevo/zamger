<?

// IZVJESTAJ/STATISTIKA_PREDMETA - neke sumarne statistike za dati predmet

// v4.0.9.1 (2009/04/22) + Novi izvjestaj napravljen na osnovu koda iz izvjestaj/ispit



// NAPOMENA: Sumarne statistike rade samo za predmete tipa "ETF Bologna standard", odnosno 
// predmete koji imaju standardni I i II parcijalni i Integralni. IDovi komponenti su 
// ukodirani. FIXME

// Ovo je vrlo sporo :(


function izvjestaj_statistika_predmeta() {

global $userid,$user_nastavnik,$user_studentska,$user_siteadmin;



?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>
<?



$predmet = intval($_REQUEST['predmet']);
$ag = intval($_REQUEST['ag']); // akademska godina


// Provjera permisija

if (!$user_nastavnik && !$user_studentska && !$user_siteadmin) {
	biguglyerror("Nemate permisije za pristup ovom izvještaju");
	zamgerlog ("pristup izvjestaju a nije NBA",3); // 3 = error
	return;
}
if (!$user_studentska && !$user_siteadmin) {
	$q2 = myquery("select admin from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
	if (mysql_num_rows($q2) < 1) {
		biguglyerror("Nemate permisije za pristup ovom izvještaju");
		zamgerlog ("nije admin predmeta pp$predmet, godina ag$ag",3); // 3 = error
		return;
	}
}



// Naziv predmeta, akademske godine

$q10 = myquery("select naziv from predmet where id=$predmet");
if (mysql_num_rows($q10)<1) {
	biguglyerror("Nepoznat predmet");
	zamgerlog ("nepoznat predmet $predmet", 3);
	return;
}

$q15 = myquery("select naziv from akademska_godina where id=$ag");
if (mysql_num_rows($q15)<1) {
	biguglyerror("Nepoznata akademska godina");
	zamgerlog ("nepoznat akademska godina $ag", 3);
	return;
}
?>
	<p>&nbsp;</p>
	<h1><?=mysql_result($q10,0,0)?> <?=mysql_result($q15,0,0)?></h1>
	<h3>Sumarna statistika za sve ispite</h3>
<?



// Tijelo izvjestaja

$q30 = myquery("select count(*) from student_predmet as sp, ponudakursa as pk where sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
$slusa_predmet = mysql_result($q30,0,0);

$q40 = myquery("select id from ispit where predmet=$predmet and akademska_godina=$ag");
$odrzano_ispita = mysql_num_rows($q40);

if ($odrzano_ispita>0) {
	$ispiti=array();
	while ($r40 = mysql_fetch_row($q40)) array_push($ispiti,$r40[0]);

	$q50 = myquery("select count(*) from konacna_ocjena where predmet=$predmet and akademska_godina=$ag and ocjena>5");
	$polozilo = mysql_result($q50,0,0);

	$q60 = myquery("select count(*) from student_predmet as sp, ponudakursa as pk where sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag and (select count(*) from ispit as i, ispitocjene as io where i.predmet=$predmet and i.akademska_godina=$ag and io.ispit=i.id and io.student=sp.student)=0");
	$nisu_izlazili = mysql_result($q60,0,0);
	$stvarno_slusa = $slusa_predmet-$nisu_izlazili;

	// Ako predmet nije bologna standard, daljnje statistike nemaju smisla - FIXME
	$q70 = myquery("select tpk.komponenta from tippredmeta_komponenta as tpk, predmet as p where p.id=$predmet and p.tippredmeta=tpk.tippredmeta");
	$bologna=0;
	while ($r70 = mysql_fetch_row($q70)) {
		if ($r70[0]==1) $bologna++;
		if ($r70[0]==2) $bologna++;
		if ($r70[0]==3) $bologna++;
	}

	if ($bologna>=3) {
		$q80 = myquery("select count(*) from student_predmet as sp, ponudakursa as pk where sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag and (select count(*) from ispitocjene as io, ispit as i where i.predmet=$predmet and i.akademska_godina=$ag and i.komponenta=1 and io.ispit=i.id and io.student=sp.student and io.ocjena>=10)>0");
		$prvaparc = mysql_result($q80,0,0);

		$q90 = myquery("select count(*) from student_predmet as sp, ponudakursa as pk where sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag and (select count(*) from ispitocjene as io, ispit as i where i.predmet=$predmet and i.akademska_godina=$ag and i.komponenta=2 and io.ispit=i.id and io.student=sp.student and io.ocjena>=10)>0");
		$drugaparc = mysql_result($q90,0,0);

		$q100 = myquery("select count(*) from student_predmet as sp, ponudakursa as pk where sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag and (select count(*) from ispitocjene as io, ispit as i where i.predmet=$predmet and i.akademska_godina=$ag and i.komponenta=3 and io.ispit=i.id and io.student=sp.student and io.ocjena>=20)>0");
		$intparc = mysql_result($q100,0,0);

		$q110 = myquery("select count(*) from student_predmet as sp, ponudakursa as pk where sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag and (select count(*) from ispitocjene as io, ispit as i where i.predmet=$predmet and i.akademska_godina=$ag and i.komponenta=1 and io.ispit=i.id and io.student=sp.student and io.ocjena>=10)>0 and (select count(*) from ispitocjene as io, ispit as i where i.predmet=$predmet and i.akademska_godina=$ag and i.komponenta=2 and io.ispit=i.id and io.student=sp.student and io.ocjena>=10)>0");
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

?>