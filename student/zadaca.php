<?

// STUDENT/ZADACA - slanje zadace za studente

// v3.9.1.0 (2008/02/21) + Kopiran raniji stud_zadaca
// v3.9.1.1 (2008/03/21) + Popravljeni stari linkovi, $conf_files_path, typo u akcijaslanje(), popravljen logging
// v3.9.1.2 (2008/03/26) + Staza za diff je bila loša
// v3.9.1.3 (2008/03/28) + Navigacija v3.0 kopirana sa predmet.php, fixevi za widescreen
// v3.9.1.4 (2008/04/10) + Navigacija je prikazivala visak zadataka; dodan update komponente nakon slanja
// v3.9.1.5 (2008/04/27) + Zamijenjen obican zadatak i attachment u log zapisu
// v3.9.1.6 (2008/05/16) + Dodan link za odgovor na komentar tutora; dodano polje $komponenta u poziv update_komponente() radi brzeg izvrsenja
// v3.9.1.7 (2008/08/28) + Tabela osoba umjesto auth
// v3.9.1.8 (2008/10/03) + Slanje zadace prebaceno na genform() radi sigurnosnih aspekata istog (kod slanja attachmenta ne moze?)
// v3.9.1.9 (2008/10/22) + Popravljen bug u slanju zadace - genform() nije ubacivao hidden polje zadatak jer se ono ponekad izracunava kao "zadnji neuradjeni" i slicno
// v3.9.1.9 (2008/10/27) + Isto i sa poljem zadaca
// v3.9.1.10 (2008/11/10) + Popravljen status nove zadace sa 2 (prepisana) na 4 (potrebno pregledati)
// v3.9.1.10 (2009/02/10) + Onemogucen spoofing predmeta i pogresna kombinacija predmet/zadaca; csrf zastita je sprjecavala slanje attachmenta
// v4.0.0.0 (2009/02/19) + Release
// v4.0.0.1 (2009/04/01) + Kod slanja zadace kao attachment status je bio postavljen na 1 (potrebna automatska kontrola) cak i ako nije odabran programski jezik



function student_zadaca() {

global $userid,$conf_files_path;


// Akcije
if ($_POST['akcija'] == "slanje" && ($_FILES['attachment']['tmp_name'] || check_csrf_token())) {
	akcijaslanje();
}


// Poslani parametri
$zadaca = intval($_REQUEST['zadaca']);
$predmet_id = intval($_REQUEST['predmet']);



//  IMA LI AKTIVNIH?
// TODO: provjeriti da li je aktivan modul...

$q10 = myquery("select count(*) from zadaca where predmet=$predmet_id and aktivna=1");
if (mysql_result($q10,0,0) == 0) {
	niceerror("Nijedna zadaća nije aktivna");
	// Ovo ujedno utvrđuje da li je $predmet_id nelegalan
	return;
}



//  ODREĐIVANJE ID ZADAĆE

// Da li neko pokušava da spoofa zadaću?
if ($zadaca!=0) {
	$q20 = myquery("SELECT count(*) FROM zadaca, student_predmet as sp
	WHERE sp.student=$userid and sp.predmet=zadaca.predmet and zadaca.id=$zadaca");
	if (mysql_result($q20,0,0)==0) {
		zamgerlog("student nije upisan na predmet (zadaca z$zadaca)",3);
		biguglyerror("Ova zadaća nije iz vašeg predmeta");
		return;
	}
}

// Ili predmet
if ($predmet_id != 0) {
	$q25 = myquery("select count(*) from student_predmet where student=$userid and predmet=$predmet_id");
	if (mysql_result($q25,0,0)==0) {
		zamgerlog("student nije upisan na predmet (predmet p$predmet_id)",3);
		biguglyerror("Niste upisani na ovaj predmet");
		return;
	}
	// Odgovarajuci predmet i zadaca
	if ($zadaca != 0) {
		$q27 = myquery("select count(*) from zadaca where id=$zadaca and predmet=$predmet_id");
		if (mysql_result($q27,0,0)==0) {
			zamgerlog("zadaca i predmet ne odgovaraju (predmet p$predmet_id, zadaca z$zadaca)",3);
			biguglyerror("Ova zadaća nije iz vašeg predmeta");
			return;
		}
	}
}

// Nije izabrana konkretna zadaca
if ($zadaca==0) {
	// Zadnja zadaca na kojoj je radio/la
	$q30 = myquery("SELECT z.id FROM zadatak as zk, zadaca as z
	WHERE z.id=zk.zadaca and z.aktivna=1 and z.rok>curdate() and z.predmet=$predmet_id and zk.student=$userid
	ORDER BY z.id DESC LIMIT 1");

	if (mysql_num_rows($q30)>0)
		$zadaca = mysql_result($q30,0,0);
	else {
		// Nije radio ni na jednoj od aktivnih zadaca
		// Daj najstariju aktivnu zadacu
		$q40 = myquery("select id from zadaca where predmet=$predmet_id and rok>curdate() and aktivna=1 order by id limit 1");

		if (mysql_num_rows($q40)>0)
			$zadaca = mysql_result($q40,0,0);
		else {
			// Ako ni ovdje nema rezultata, znači da je svim 
			// zadaćama istekao rok. Daćemo zadnju zadaću.
			// Da li ima aktivnih provjerili smo u $q10
			$q50 = myquery("select id from zadaca where predmet=$predmet_id and aktivna=1 order by id desc limit 1");
			$zadaca = mysql_result($q50,0,0);
		}
	}
}



# Standardna lokacija zadaca:

$lokacijazadaca="$conf_files_path/zadace/$predmet_id/$userid/";
# Create db dir
if (!file_exists("$conf_files_path/zadace/$predmet_id")) {
	mkdir ("$conf_files_path/zadace/$predmet_id",0777, true);
}





// Ove vrijednosti će nam trebati kasnije
$q60 = myquery("select naziv,zadataka,UNIX_TIMESTAMP(rok),programskijezik,attachment from zadaca where id=$zadaca");
$naziv = mysql_result($q60,0,0);
$brojzad = mysql_result($q60,0,1);
$rok = mysql_result($q60,0,2);
$jezik = mysql_result($q60,0,3);
$attachment = mysql_result($q60,0,4);



//  ODREĐIVANJE ZADATKA

// Poslani parametar:
$zadatak = intval($_REQUEST['zadatak']);

if ($zadatak==0) { 
	// Prvi neurađeni zadatak u datoj zadaći
	// NOTE: subquery
	$q70 = myquery("select zk.redni_broj from zadatak as zk where zk.student=$userid and zk.zadaca=$zadaca and (select count(*) from zadatak as zk2 where zk2.student=$userid and zk2.zadaca=$zadaca and zk2.redni_broj=zk.redni_broj)=0 order by zk.redni_broj limit 1");
	
	if (mysql_num_rows($q70)>0) 
		$zadatak=mysql_result($q70,0,0);
	// Sve je uradio, daj zadnji
	else 
		$zadatak=$brojzad;
}



//  NAVIGACIJA

print "<br/><br/><center><h1>$naziv, Zadatak: $zadatak</h1></center>\n";


// Još novija navigacija


// Statusne ikone:
$stat_icon = array("zad_bug", "zad_preg", "zad_copy", "zad_bug", "zad_preg", "zad_ok");
$stat_tekst = array("Bug u programu", "Pregled u toku", "Zadaća prepisana", "Bug u programu", "Pregled u toku", "Zadaća OK");


?>


<!-- zadace -->
<center>
<table cellspacing="0" cellpadding="2" border="0" id="zadace">
	<thead>
		<tr>
<?



?>
	<td>&nbsp;</td>
<?

// Zaglavlje tabele - potreban nam je max. broj zadataka u zadaci

$q20 = myquery("select zadataka from zadaca where predmet=$predmet_id order by zadataka desc limit 1");
$broj_zadataka = mysql_result($q20,0,0);
for ($i=1;$i<=$broj_zadataka;$i++) {
	?><td>Zadatak <?=$i?>.</td><?
}

?>
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


$bodova_sve_zadace=0;

$q21 = myquery("select id,naziv,bodova,zadataka from zadaca where predmet=$predmet_id order by komponenta,id");
while ($r21 = mysql_fetch_row($q21)) {
	$m_zadaca = $r21[0];
	$m_mogucih += $r21[2];
	$m_maxzadataka = $r21[3];
	?><tr>
	<th><?=$r21[1]?></th>
	<?

	for ($m_zadatak=1;$m_zadatak<=$broj_zadataka;$m_zadatak++) {
		// Ako tekuća zadaća nema toliko zadataka, ispisujemo blank polje
		if ($m_zadatak>$m_maxzadataka) {
			?><td>&nbsp;</td><?
			continue;
		}

		// Uzmi samo rjesenje sa zadnjim IDom
		$q22 = myquery("select status,bodova,komentar from zadatak where student=$userid and zadaca=$m_zadaca and redni_broj=$m_zadatak order by id desc limit 1");
		if ($m_zadaca==$zadaca && $m_zadatak==$zadatak)
			$bgcolor = ' bgcolor="#DDDDFF"'; 
		else 	$bgcolor = "";
		if (mysql_num_rows($q22)<1) {
			?><td <?=$bgcolor?>><a href="?sta=student/zadaca&predmet=<?=$predmet_id?>&zadaca=<?=$m_zadaca?>&zadatak=<?=$m_zadatak?>"><img src="images/16x16/zad_novi.png" width="16" height="16" border="0" align="center" title="Novi zadatak" alt="Novi zadatak"></a></td><?
		} else {
			$status = mysql_result($q22,0,0);
			$bodova_zadatak = mysql_result($q22,0,1);
			if (strlen(mysql_result($q22,0,2))>2)
				$imakomentar = "<img src=\"images/16x16/komentar.png\"  width=\"15\" height=\"14\" border=\"0\" title=\"Ima komentar\" alt=\"Ima komentar\" align=\"center\">";
			else
				$imakomentar = "";
			?><td <?=$bgcolor?>><a href="?sta=student/zadaca&predmet=<?=$predmet_id?>&zadaca=<?=$m_zadaca?>&zadatak=<?=$m_zadatak?>"><img src="images/16x16/<?=$stat_icon[$status]?>.png" width="16" height="16" border="0" align="center" title="<?=$stat_tekst[$status]?>" alt="<?=$stat_tekst[$status]?>"> <?=$bodova_zadatak?> <?=$imakomentar?></a></td>
	<?
		}
	}
	?>
	</tr>
	<?
}



?>
</tbody>
</table>
</center>
<?




// Nova navigacija - kod kopiran iz stud_status



/*
?>
<center><table cellspacing="0" cellpadding="2" border="1">
<tr><td>&nbsp;</td>

<?


// Zaglavlje tabele - potreban nam je max. broj zadataka u svim zadaćama
$q80 = myquery("select zadataka from zadaca where predmet=$predmet_id order by zadataka desc limit 1");
if (mysql_num_rows($q80)<1) 
	$max_brzad=0;
else
	$max_brzad=mysql_result($q80,0,0);

for ($i=1;$i<=$max_brzad;$i++) {
	?><td>Zadatak <?=$i?>.</td><?
}

?>
</tr>
<?


// (nastavak navigacije...)

/* Ovo se sve moglo kroz SQL rijesiti, ali necu iz razloga:
1. PHP je citljiviji
2. MySQL <4.1 ne podrzava subqueries */


/*
// Status ikone:
$stat_icon = array("zad_bug", "zad_preg", "zad_copy", "zad_bug", "zad_preg", "zad_ok", "zad_novi");
$stat_tekst = array("Bug u programu", "Pregled u toku", "Zadaća prepisana", "Bug u programu", "Pregled u toku", "Zadaća OK", "Novi zadatak");


$bodova_sve_zadace=0;

$q90 = myquery("select id,naziv,bodova,zadataka from zadaca where predmet=$predmet_id");
while ($r90 = mysql_fetch_row($q90)) {
	$m_zadaca = $r90[0];
	$m_mogucih += $r90[2];
	$m_brzad = $r90[3];
	?><tr>
	<td><?=$r90[1]?></td><?
	$m_bodova_zadaca = 0;

	for ($m_zadatak=1;$m_zadatak<=$m_brzad;$m_zadatak++) {
		// Uzmi samo rjesenje sa zadnjim IDom
		$q100 = myquery("select status,bodova from zadatak where student=$userid and zadaca=$m_zadaca and redni_broj=$m_zadatak order by id desc limit 1");
		if (mysql_num_rows($q100)>0) {
			$status = mysql_result($q100,0,0);
			$m_bodova_zadatak = mysql_result($q100,0,1);
			$m_bodova_zadaca += $m_bodova_zadatak;
		} else {
			$status = 6;
			$m_bodova_zadatak="";
		} 
		if ($m_zadaca==$zadaca && $m_zadatak==$zadatak)
			$bgcolor = ' bgcolor="#EEEEFF"'; 
		else 	$bgcolor = "";
		?><td <?=$bgcolor?>><a href="?sta=student/zadaca&zadaca=<?=$m_zadaca?>&zadatak=<?=$m_zadatak?>&predmet=<?=$predmet_id?>"><img src="images/16x16/<?=$stat_icon[$status]?>.png" width="16" height="16" border="0" align="center" title="<?=$stat_tekst[$status]?>" alt="<?=$stat_tekst[$status]?>"> <?=$m_bodova_zadatak?></a></td><?
	}
	
	// Ispis praznih ćelija tabele za zadaće koje imaju manje od max. zadataka
	for ($i=$m_zadatak; $i<=$max_brzad; $i++) {
		print "<td>&nbsp;</td>\n";
	}

	?></tr><?
	$bodova_sve_zadace += $m_bodova_zadaca;
}


// Ukupno bodova za studenta
 
$bodova += $bodova_sve_zadace;

?>
</table></center>
<?

*/


// STARA NAVIGACIJA - Naslov i strelice lijevo-desno...

/*$onclick = 'onclick="self.location = \''.genuri();

# dugme "<<"
$q30 = myquery("select id from zadaca where id<$zadaca and predmet=$predmet_id order by id desc limit 1");
if (mysql_num_rows($q30)==0)
	$d1="disabled";
else
	$d1=$onclick.'&zadaca='.mysql_result($q30,0,0).'&zadatak=1\'"';

# dugme "<"
if ($zadatak==1)
	$d2="disabled";
else
	$d2=$onclick.'&zadaca='.$zadaca.'&zadatak='.($zadatak-1).'\'"';

# dugme "<"
if ($zadatak>=$brojzad) // $brzad određen kod Određivanja zadaće
	$d3="disabled";
else
	$d3=$onclick.'&zadaca='.$zadaca.'&zadatak='.($zadatak+1).'\'"';

# dugme ">>"
$q31 = myquery("select id from zadaca where id>$zadaca and predmet=$predmet_id order by id limit 1");
if (mysql_num_rows($q31)==0)
	$d4="disabled";
else
	$d4=$onclick.'&zadaca='.mysql_result($q31,0,0).'&zadatak=1\'"';



# Ispis zaglavlja

?>
<table width="100%" border="0">
<tr>
<td width="10%" align="center" valign="center"><input type="submit" value=" &lt;&lt; " <?=$d1?>></td>
<td width="10%" align="center" valign="center"><input type="submit" value=" &lt; " <?=$d2?>></td>
<td align="center" valign="center">
<h1>Zadaća: <?=$naziv?>, Zadatak: <?=$zadatak?></h1>
</td>
<td width="10%" align="center" valign="center"><input type="submit" value=" &gt; " <?=$d3?>></td>
<td width="10%" align="center" valign="center"><input type="submit" value=" &gt;&gt; " <?=$d4?>></td>
</tr></table>

<? 
*/



//  PORUKE I KOMENTARI


// Upit za izvjestaj skripte i komentar tutora

?>
<br/><br/>
<center>
<table width="600" border="0"><tr><td>
<?

$q110 = myquery("select izvjestaj_skripte,komentar,userid from zadatak where student=$userid and zadaca=$zadaca and redni_broj=$zadatak order by id desc limit 1");
if (mysql_num_rows($q110)>0) {
	$poruka = mysql_result($q110,0,0);
	$komentar = mysql_result($q110,0,1);
	$tutor = mysql_result($q110,0,2);

	if (preg_match("/\w/",$poruka)) {
		$poruka = str_replace("\n","<br/>\n",$poruka);
		?><p>Poruka kod kompajliranja:<br/><b><?=$poruka?></b></p><?
	}
	if (preg_match("/\w/",$komentar)) {
		// Link za odgovor na komentar
		$link="";
		if ($tutor>0) {
			$q115 = myquery("select a.login,o.ime,o.prezime from auth as a, osoba as o where o.id=$tutor and a.id=o.id");

			$naslov = urlencode("Odgovor na komentar ($naziv, Zadatak $zadatak)");
			$tekst = urlencode("> $komentar");
			$primalac = urlencode(mysql_result($q115,0,0)." (".mysql_result($q115,0,1)." ".mysql_result($q115,0,2).")");

			$link = " (<a href=\"?sta=common/inbox&akcija=compose&naslov=$naslov&tekst=$tekst&primalac=$primalac\">odgovor</a>)";
		}
		?><p>Komentar tutora: <b><?=$komentar?></b><?=$link?><?
	}
}


// Istek roka za slanje zadace

if ($rok <= time()) {
	print "<p><b>Vrijeme za slanje ove zadaće je isteklo.</b></p>";
	// Ovo je onemogućavalo copy&paste u Firefoxu :(
	//$readonly = "DISABLED";
} else {
	$readonly = "";
}




//  FORMA ZA SLANJE


if ($attachment) {
	print "</td></tr></table>\n";

	// Attachment
	$q120 = myquery("select filename,UNIX_TIMESTAMP(vrijeme) from zadatak where zadaca=$zadaca and redni_broj=$zadatak and student=$userid and status=1 order by id desc limit 1");
	if (mysql_num_rows($q120)>0) {
		$filename = mysql_result($q120,0,0);
		$the_file = "$lokacijazadaca/$zadaca/$filename";
		if (file_exists($the_file)) {
			$vrijeme = mysql_result($q120,0,1);
			$vrijeme = date("d. m. Y. h:i:s",$vrijeme);
			$velicina = nicesize(filesize($the_file));
			$icon = "images/mimetypes/" . getmimeicon($the_file);
			$dllink = "index.php?sta=common/attachment&zadaca=$zadaca&zadatak=$zadatak";
			?>
			<center><table width="75%" border="1" cellpadding="6" cellspacing="0" bgcolor="#CCCCCC"><tr><td>
			<a href="<?=$dllink?>"><img src="<?=$icon?>" border="0"></a>
			</td><td>
			<p>Poslani fajl: <b><a href="<?=$dllink?>"><?=$filename?></a></b><br/>
			Datum slanja: <b><?=$vrijeme?></b><br/>
			Veličina: <b><?=$velicina?></b></p>
			</td></tr></table></center>
			<?
		}
		print "<p>Ako želite promijeniti datoteku iznad, izaberite novu i kliknite na dugme za slanje:</p>";
	} else {
		print "<p>Izaberite datoteku koju želite poslati i kliknite na dugme za slanje:</p>";
	}

	?>

	<form action="index.php" method="POST" enctype="multipart/form-data">
	<input type="hidden" name="sta" value="student/zadaca">
	<input type="hidden" name="akcija" value="slanje">
	<input type="hidden" name="predmet" value="<?=$predmet_id?>">
	<input type="hidden" name="zadaca" value="<?=$zadaca?>">
	<input type="hidden" name="zadatak" value="<?=$zadatak?>">
	<input type="hidden" name="labgrupa" value="<?=$labgrupa?>">
	<input type="file" name="attachment" size="50">
	</center>
	<p>&nbsp;</p>
	<?

} else {

	// Forma
	$q130 = myquery("select ekstenzija from programskijezik where id=$jezik");
	$ekst = mysql_result($q130,0,0);

	if ($rok > time()) {
 		?><p>Kopirajte vaš zadatak u tekstualno polje ispod:</p>
		</td></tr></table>

		<?
	}


	// Moze li se izbaciti labgrupa ispod?

	?>
	
	<center>
	<?=genform("POST")?>
	<input type="hidden" name="zadaca" value="<?=$zadaca?>">
	<input type="hidden" name="zadatak" value="<?=$zadatak?>">
	<input type="hidden" name="akcija" value="slanje">
	<input type="hidden" name="labgrupa" value="<?=$labgrupa?>">
	
	<textarea rows="20" cols="80" name="program" <?=$readonly?> wrap="off"><? 
	$the_file = "$lokacijazadaca$zadaca/$zadatak$ekst";
	if (file_exists($the_file)) print join("",file($the_file)); 
	?></textarea>
	</center>	

	<?
}

?>

<center>
<table width="600" border="0">
<tr><td align="center"><input type="reset" value=" Poništi izmjene "></td>
<td align="center"><input type="submit" value=" Pošalji zadatak! "></td></tr>
</table>
</center>
</form>
<?


} // function student_zadaca()



function akcijaslanje() {

	global $userid,$conf_files_path;
	require ("lib/manip.php"); // update komponente nakon slanja

	// Parametri
	$predmet_id = intval($_POST['predmet']);
	$zadaca = intval($_POST['zadaca']); 
	$zadatak = intval($_POST['zadatak']);
	$program = $_POST['program'];
	if ($predmet_id==0) {
		return; // student_zadaca() će ispisati grešku
	}


	// Standardna lokacija zadaca
	$lokacijazadaca="$conf_files_path/zadace/$predmet_id/$userid/";
	if (!file_exists("$conf_files_path/zadace/$predmet_id")) {
		mkdir ("$conf_files_path/zadace/$predmet_id",0777, true);
	}


	// Da li neko pokušava da spoofa zadaću?
	$q200 = myquery("SELECT count(*) FROM zadaca, student_predmet as sp
	WHERE sp.student=$userid and sp.predmet=zadaca.predmet and zadaca.id=$zadaca");
	if (mysql_result($q200,0,0)==0) {
//		biguglyeerror("Ova zadaća nije iz vašeg predmeta");
		// student_zadaca() ce ispisati grešku
		return;
	}

	// Ovo je potrebno radi pravljenja diff-a
	if (get_magic_quotes_gpc()) {
		$program = stripslashes($program);
	}

	// Podaci o zadaći
	$q210 = myquery("select programskijezik, UNIX_TIMESTAMP(rok), attachment, naziv, komponenta from zadaca where id=$zadaca");
	$jezik = mysql_result($q210,0,0);
	$rok = mysql_result($q210,0,1);
	$attach = mysql_result($q210,0,2);
	$naziv_zadace = mysql_result($q210,0,3);
	$komponenta = mysql_result($q210,0,4);

	// Ako nije zadat jezik, postavi status na 4 (ceka pregled), inace na 1 (automatska kontrola)
	if ($jezik==0) $prvi_status=4; else $prvi_status=1;

	// Provjera roka
	if ($rok <= time()) {
		niceerror("Vrijeme za slanje zadaće je isteklo!");
		zamgerlog("isteklo vrijeme za slanje zadaće z$zadaca",3); // nivo 3 - greska
		return; 
	}

	// Pravimo potrebne puteve
	if (!file_exists($lokacijazadaca)) mkdir ($lokacijazadaca,0777);
	if ($zadaca>0 && !file_exists("$lokacijazadaca$zadaca")) 
		mkdir ("$lokacijazadaca$zadaca",0777);

	// Vrsta zadaće: textarea ili attachment
	if ($attach == 0) {
		// Određivanje ekstenzije iz jezika
		$q220 = myquery("select ekstenzija from programskijezik where id=$jezik");
		$ekst = mysql_result($q220,0,0);

		$filename = "$lokacijazadaca$zadaca/$zadatak$ekst";

		// Temp fajl radi određivanja diff-a 
		$diffing=0;
		if (file_exists($filename)) {
			if (file_exists("$lokacijazadaca$zadaca/difftemp")) 
				unlink ("$lokacijazadaca$zadaca/difftemp");
			rename ($filename, "$lokacijazadaca$zadaca/difftemp"); 
			$diffing=1;
		}

		// Kreiranje datoteke
		if (strlen($program)<=10) {
			niceerror("Niste kopirali zadaću!");
			zamgerlog("poslao praznu zadacu z$zadaca zadatak $zadatak",3); // nivo 3 - greska
		} else if ($zadaca>0 && $zadatak>0 && ($f = fopen($filename,'w'))) {
			fwrite($f,$program);
			fclose($f);

			// Tabela "zadatak" funkcioniše kao log događaja u
			// koji se stvari samo dodaju
			$q230 = myquery("insert into zadatak set zadaca=$zadaca, redni_broj=$zadatak, student=$userid, status=$prvi_status, vrijeme=now(), filename='$zadatak$ekst', userid=$userid");

			// Pravljenje diffa
			if ($diffing==1) {
				$diff = `/usr/bin/diff -u $lokacijazadaca$zadaca/difftemp $filename`;
				$diff = my_escape($diff);
				if (strlen($diff)>1) {
					$q240 = myquery("select id from zadatak where zadaca=$zadaca and redni_broj=$zadatak and student=$userid and status=1 order by id desc limit 1");
					if (mysql_num_rows($q240) > 0) {
						$id = mysql_result($q240,0,0);
						$q250 = myquery("insert into zadatakdiff set zadatak=$id, diff='$diff'");
					}
				}
				unlink ("$lokacijazadaca$zadaca/difftemp");
			}

			nicemessage($naziv_zadace."/Zadatak ".$zadatak." uspješno poslan!");
			update_komponente($userid,$predmet_id);
			zamgerlog("poslana zadaca z$zadaca zadatak $zadatak",2); // nivo 2 - edit
		} else {
			zamgerlog("greska pri slanju zadace (zadaca z$zadaca zadatak $zadatak filename $filename)",3);
			niceerror("Greška pri slanju zadaće. Kontaktirajte tutora.");
		}

	} else { // if ($attach==0)...
		$program = $_FILES['attachment']['tmp_name'];
		if ($program && (file_exists($program))) {
			// Nećemo pokušavati praviti diff
			$filename = "$lokacijazadaca$zadaca/".$_FILES['attachment']['name'];
			unlink ($filename);
			rename($program, $filename);

			$q260 = myquery("insert into zadatak set zadaca=$zadaca, redni_broj=$zadatak, student=$userid, status=$prvi_status, vrijeme=now(), filename='".$_FILES['attachment']['name']."', userid=$userid");

			nicemessage("Z".$naziv_zadace."/".$zadatak." uspješno poslan!");
			update_komponente($userid,$predmet_id,$komponenta);
			zamgerlog("poslana zadaca z$zadaca zadatak $zadatak (attachment)",2); // nivo 2 - edit
		} else {
			zamgerlog("greska kod attachmenta (zadaca z$zadaca, varijabla program je: $program)",3);
			niceerror("Greška pri slanju zadaće. Kontaktirajte tutora.");
		}
	}
}

?>
