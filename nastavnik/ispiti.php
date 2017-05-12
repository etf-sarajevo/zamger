<?

// NASTAVNIK/ISPITI - kreiranje i unos rezultata ispita



function nastavnik_ispiti() {

global $userid,$user_siteadmin,$user_studentska;

require_once("lib/formgen.php"); // datectrl, genform
require_once("lib/zamgerui.php"); // za masovni unos studenata u grupe (mass_input)
require_once("lib/student_predmet.php"); // update_komponente

global $mass_rezultat; 

// Parametri
$predmet = int_param('predmet');
$ag = int_param('ag');
$termin = int_param('termin');


// Naziv predmeta
$predmet_naziv = db_get("select naziv from predmet where id=$predmet");
if ($predmet_naziv === false) {
	biguglyerror("Nepoznat predmet");
	zamgerlog("ilegalan predmet $predmet",3); //nivo 3: greska
	zamgerlog2("nepoznat predmet", $predmet);
	return;
}



// Da li korisnik ima pravo ući u modul?

if (!$user_siteadmin && !$user_studentska) {
	$nivo_pristupa = db_get("select nivo_pristupa from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
	if ($nivo_pristupa === false || $nivo_pristupa == "asistent") {
		zamgerlog("nastavnik/ispiti privilegije (predmet pp$predmet)",3);
		zamgerlog2("nije nastavnik na predmetu", $predmet, $ag);
		biguglyerror("Nemate pravo pristupa ovoj opciji");
		return;
	} 
}


// Neki korisni podaci o ispitu

$ispit = int_param('ispit');
if ($ispit>0) {
	$q30 = db_query("select UNIX_TIMESTAMP(i.datum), k.id, k.gui_naziv, k.maxbodova from ispit as i, komponenta as k where i.id=$ispit and i.predmet=$predmet and i.akademska_godina=$ag and i.komponenta=k.id");
	if (db_num_rows($q30)<1) {
		niceerror("Nepostojeći ispit");
		print "Moguće je da ste ga već obrisali? Ako ste koristili dugme Back vašeg browsera da biste došli na ovu stranicu, predlažemo da kliknete na link Ispiti sa lijeve strane kako biste dobili ažurnu informaciju.";
		zamgerlog("nepostojeci ispit $ispit ili nije sa predmeta (pp$predmet, ag$ag)", 3);
		zamgerlog2("nepostojeci ispit ili nije sa predmeta", $predmet, $ag, $ispit);
		return;
	}
}


?>

<p>&nbsp;</p>

<p><h3><?=$predmet_naziv?> - Ispiti</h3></p>

<?



// Masovni unos rezultata ispita

if ($_POST['akcija'] == "massinput" && strlen($_POST['nazad'])<1 && check_csrf_token()) {

	if ($ispit>0) {
		$finidatum = date("d. m. Y", db_result($q30,0,0));
		$tipispita = db_result($q30,0,1);
		$fini_naziv_ispita = db_result($q30,0,2);
		print "<p><b>Masovni unos ocjena za ispit $fini_naziv_ispita, održan $finidatum</b></p>";
		$maxbodova = db_result($q30,0,3);
	}


	if ($_POST['fakatradi'] != 1) $ispis=1; else $ispis=0;

	if ($ispis) {
		?>Akcije koje će biti urađene:<br/><br/>
		<?=genform("POST")?>
		<input type="hidden" name="fakatradi" value="1">
		<table border="0" cellspacing="1" cellpadding="2">
		<!-- FIXME: prebaciti stilove u CSS? -->
		<thead>
		<tr bgcolor="#999999">
			<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;color:white;">Prezime</font></td>
			<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;color:white;">Ime</font></td>
			<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;color:white;">Bodovi / Komentar</font></td>
		</tr>
		</thead>
		<tbody>
		<?
	}

	$greska=mass_input($ispis); // Funkcija koja parsira podatke

	// Dozvoljavamo kreiranje blank ispita
	// if (count($mass_rezultat)==0) { ...


	// Registrovati ispit u bazi - ovaj kod se trenutno ne koristi, ali ga neću još izbacivati
	if ($ispit==0) {
		$naziv = db_escape($_POST['naziv']);
		$dan = intval($_POST['day']);
		$mjesec = intval($_POST['month']);
		$godina = intval($_POST['year']);
		$mdat = mktime(0,0,0,$mjesec,$dan,$godina);
	
		$tipispita = intval($_POST['tipispita']);
	
		// Da li je ispit vec registrovan?
		$ispit = db_get("select id from ispit where predmet=$predmet and datum=FROM_UNIXTIME('$mdat') and komponenta=$tipispita and akademska_godina=$ag");
		if ($ispit !== false) {
			if ($ispis) {
				print "Dodati rezultate na postojeći ispit (ID: $ispit):<br/>";
			}
			$dodavanje=1;
		} else if (!$ispis) {
			db_query("insert into ispit set predmet=$predmet, akademska_godina=$ag, datum=FROM_UNIXTIME('$mdat'), komponenta=$tipispita");
			$ispit = db_insert_id();
			$dodavanje=0;
		}
	} else $dodavanje=1; // Uvijek je dodavanje


	// Obrada rezultata

	$boja1 = "#EEEEEE";
	$boja2 = "#DDDDDD";
	$boja=$boja1;
	$bojae = "#FFE3DD";

	foreach ($mass_rezultat['ime'] as $student=>$ime) {
		$prezime = $mass_rezultat['prezime'][$student];
		$bodova = $mass_rezultat['podatak1'][$student];

		// pretvori bodove u float uz obradu decimalnog zareza
		$fbodova = floatval(str_replace(",",".",$bodova));
		// samo 0 priznajemo za nula bodova, inace student nije izasao na ispit
		if ($fbodova==0 && strpos($bodova,"0")===FALSE) {
			if ($ispis) {
				?>
				<tr bgcolor="<?=$boja?>">
					<td><?=$prezime?></td><td><?=$ime?></td>
					<td>nije izašao/la na ispit (unesena je ocjena: <?=$bodova?>)</td>
				</tr>
				<?
				if ($boja==$boja1) $boja=$boja2; else $boja=$boja1;
			}
			continue;
		}
		$bodova = $fbodova;

		// Da li je broj bodova veći od maksimalno dozvoljenog?
		if ($bodova > $maxbodova) {
			if ($ispis) {
				?>
				<tr bgcolor="<?=$bojae?>">
					<td><?=$prezime?></td><td><?=$ime?></td>
					<td>broj bodova <?=$bodova?> je veći od maksimalnih <?=$maxbodova?></td>
				</tr>
				<?
			}
			$greska=1;
			continue;
		}

		// Određujem ponudu kursa (provjeru je već trebao uraditi massinput ali neka je i ovdje)
		// Ponudakursa nam treba za update_komponente()
		$ponudakursa = db_get("select sp.predmet from student_predmet as sp, ponudakursa as pk where sp.student=$student and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
		if ($ponudakursa === false) {
			if ($ispis) {
				?>
				<tr bgcolor="<?=$bojae?>">
					<td><?=$prezime?></td><td><?=$ime?></td>
					<td>nije upisan/a na predmet</td>
				</tr>
				<?
			}
			$greska=1;
			continue; // Ne smijemo dozvoliti da se ovakav podatak unese u bazu
		}
		
		// Da li je ocjena za studenta vec ranije unesena?
		if ($dodavanje == 1) {
			$oc2 = db_get("select ocjena from ispitocjene where ispit=$ispit and student=$student");
			if ($oc2 !== false) {
				if ($ispis) {
					?>
					<tr bgcolor="<?=$bojae?>">
						<td><?=$prezime?></td><td><?=$ime?></td>
						<td>već ima rezultat <?=$oc2?>; koristite pogled grupe za izmjenu</td>
					</tr>
					<?
				}
				$greska=1;
				continue; // Ne smijemo dozvoliti dvostruke ocjene u bazi
			}
		}

		// Zakljucak
		if ($ispis) {
			?>
			<tr bgcolor="<?=$boja?>">
				<td><?=$prezime?></td><td><?=$ime?></td>
				<td><?=$bodova?> bodova</td>
			</tr>
			<?
			if ($boja==$boja1) $boja=$boja2; else $boja=$boja1;
		} else {
			db_query("insert into ispitocjene set ispit=$ispit, student=$student, ocjena=$bodova");
			zamgerlog2("upisan rezultat ispita", $student, $ispit, 0, $bodova);

			// Update komponenti
			update_komponente($student, $ponudakursa, $tipispita);
		}
	}

	if ($ispis) {
		if ($greska == 0) {
			?>
			</tbody></table>
			<p>Potvrdite upis ispita i bodova ili se vratite na prethodni ekran.</p>
			<p><input type="submit" name="nazad" value=" Nazad "> <input type="submit" value=" Potvrda"></p>
			</form>
			<? 
		} else {
			?>
			</tbody></table>
			<p>U unesenim podacima ima grešaka. Da li ste izabrali ispravan format ("Prezime[TAB]Ime" vs. "Prezime Ime")? Vratite se nazad kako biste ovo popravili.</p>
			<p><input type="submit" name="nazad" value=" Nazad "></p>
			</form>
			<? 
		}
		return;
	} else {
		// Generisem statičku verziju izvještaja predmet
		generisi_izvjestaj_predmet( $predmet, $ag, array("skrati" => "da", "sakrij_imena" => "da") );

		zamgerlog("masovni rezultati ispita za predmet pp$predmet",4);
		?>
		Rezultati ispita su upisani.
		<script language="JavaScript">
		location.href='?sta=nastavnik/ispiti&predmet=<?=$predmet?>&ag=<?=$ag?>';
		</script>
		<?
	}
}

if ($_POST['akcija'] == "massinput" && $_POST['nazad']==" Nazad ") {
	// Redirektujemo na akciju masovni_unos
	$_REQUEST['akcija']='masovni_unos';
}



// Brisanje ispita

if ($_REQUEST['akcija']=="brisanje" && $ispit > 0 && $_REQUEST['potvrdabrisanja'] != " Nazad ") {

	$brojstudenata = db_get("select count(*) from ispitocjene where ispit=$ispit");

	if ($_REQUEST['potvrdabrisanja'] == " Briši " && check_csrf_token()) {
		$komponenta = db_result($q30,0,1);
		zamgerlog ("obrisan ispit $ispit (pp$predmet, ag$ag)", 4); // 4 - audit

		print "<p>Brisanje u toku. Molimo budite strpljivi, ova akcija može potrajati nekoliko minuta.</p>\n\n\n\n";
		$q210 = db_query("select io.student, pk.id from ispitocjene as io, student_predmet as sp, ponudakursa as pk where io.ispit=$ispit and io.student=sp.student and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
		$brojac=1;
		while (db_fetch2($q210, $student, $ponudakursa)) {
			print "Ažuriram bodove za studenta $brojac od $brojstudenata<br />\n\n";

			db_query("delete from ispitocjene where ispit=$ispit and student=$student");
			update_komponente($student,$ponudakursa,$komponenta);
			zamgerlog2 ("izbrisan rezultat ispita", intval($student), $ispit);

			$brojac++;
		}

		print "Brišem termine za prijavu ispita i prijave<br />\n\n";
		$q230 = db_query("select id from ispit_termin where ispit=$ispit");
		while (db_fetch1($q230, $termin)) {
			db_query("delete from student_ispit_termin where ispit_termin=$termin");
			db_query("delete from ispit_termin where id=$termin");
			zamgerlog2 ("izbrisan termin ispita", intval($termin));
		}

		db_query("delete from ispit where id=$ispit");
		zamgerlog2 ("obrisan ispit", $predmet, $ag, $ispit);
		nicemessage("Svi podaci su ažurirani.");
		print "<a href=\"?sta=nastavnik/ispiti&predmet=$predmet&ag=$ag\">Nazad</a>\n";
		return;

	} else {
		$finidatum = date("d. m. Y", db_result($q30,0,0));
		$tipispita = db_result($q30,0,2);

		$brojprijava = db_get("select count(*) from student_ispit_termin as sit, ispit_termin as it where it.ispit=$ispit and sit.ispit_termin=it.id");

		print genform("POST");
		?>
		<h2>Zatražili ste brisanje ispita &quot;<?=$tipispita?>&quot; održanog <?=$finidatum?></h2>
		<p><font color="red"><b>Brisanje ispita je vrlo destruktivna akcija!</b></font></p>
		<p>Brisanjem ispita potpuno ćete promijeniti bodovanje svih studenata na predmetu. Ova operacija se ne može vratiti! Da li ste sigurni da to želite?<br /><br />
		Na odabranom ispitu su registrovani rezultati za <b><?=$brojstudenata?> studenata</b>.<br /><br />
		<? if ($brojprijava>0) { ?>Za polaganje ovog ispita je prijavljeno <b><?=$brojprijava?> studenata</b>.<br /><br /><? } ?>
		<input type="submit" name="potvrdabrisanja" value=" Briši ">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input type="submit" name="potvrdabrisanja" value=" Nazad ">
		<?
		return;
	}
}



// Promjena ispita

if ($_REQUEST['akcija']=="promjena" && $ispit > 0 && $_REQUEST['potvrdapromjene'] != " Nazad ") {
	$komponenta = db_result($q30,0,1);

	$brojstudenata = db_get("select count(*) from ispitocjene where ispit=$ispit");

	if ($_REQUEST['potvrdapromjene'] == " Promijeni " && check_csrf_token()) {

		$dan = int_param('day');
		$mjesec = int_param('month');
		$godina = int_param('year');
		$mdat = mktime(0,0,0,$mjesec,$dan,$godina);

		if ($komponenta != $_POST['tipispita']) {
			zamgerlog ("promijenjen tip ispita $ispit (pp$predmet, ag$ag)", 4); // 4 - audit
			zamgerlog2 ("promijenjen tip ispita", $ispit);
			$nova_komponenta = intval($_POST['tipispita']);
			print "<p>Ažuriranje u toku. Molimo budite strpljivi, ova akcija može potrajati nekoliko minuta.</p>\n\n\n\n";
			db_query("update ispit set komponenta=$nova_komponenta where id=$ispit");

			$q320 = db_query("select io.student, pk.id from ispitocjene as io, student_predmet as sp, ponudakursa as pk where io.ispit=$ispit and io.student=sp.student and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
			$brojac=1;
			while (db_fetch2($q320, $student, $ponudakursa)) {
				print "Ažuriram bodove za studenta $brojac od $brojstudenata<br />\n\n";

				update_komponente($student,$ponudakursa,$komponenta);
				update_komponente($student,$ponudakursa,$nova_komponenta);

				$brojac++;
			}
		}

		if ($mdat != db_result($q30,0,0)) {
			zamgerlog ("promijenjen datum ispita $ispit (pp$predmet, ag$ag)", 4); // 4 - audit
			zamgerlog2("promijenjen datum ispita", $ispit);
			db_query("update ispit set datum=FROM_UNIXTIME('$mdat') where id=$ispit");
			print "<p>Ažuriram datum ispita.</p>\n";
		}

		nicemessage("Svi podaci su ažurirani.");
		print "<a href=\"?sta=nastavnik/ispiti&predmet=$predmet&ag=$ag\">Nazad</a>\n";
		return;

	} else {
		$finidatum = date("d. m. Y", db_result($q30,0,0));
		$dan = date("d", db_result($q30,0,0));
		$mjesec = date("m", db_result($q30,0,0));
		$godina = date("Y", db_result($q30,0,0));
		$tipispita = db_result($q30,0,2);

		print genform("POST");
		?>
		<h2>Zatražili ste promjenu podataka ispita &quot;<?=$tipispita?>&quot; održanog <?=$finidatum?></h2>
		<p>Na odabranom ispitu su registrovani rezultati za <b><?=$brojstudenata?> studenata</b>.<br><br>
		<p>Datum ispita: <?=datectrl($dan, $mjesec, $godina)?></p>
		<p>Tip ispita: <select name="tipispita" class="default"><?
		$q340 = db_query("select k.id, k.gui_naziv from tippredmeta_komponenta as tpk, komponenta as k, akademska_godina_predmet as agp where agp.predmet=$predmet and agp.tippredmeta=tpk.tippredmeta and agp.akademska_godina=$ag and tpk.komponenta=k.id and (k.tipkomponente=1 or k.tipkomponente=2) order by k.id");
		while (db_fetch2($q340, $id, $naziv)) {
			print '<option value="'.$id.'"';
			if ($komponenta==$id) print ' SELECTED';
			print '>'.$naziv.'</option>'."\n";
		}
		?></select><br />
		<font color="red">Promjenom tipa ispita mijenjate bodovanje za sve studente! Ova operacija može potrajati malo duže.</font></p>
		<input type="submit" name="potvrdapromjene" value=" Promijeni ">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input type="submit" name="potvrdapromjene" value=" Nazad ">
		<?
		return;
	}
}



// Masovni unos rezultata ispita

if ($_REQUEST['akcija']=="masovni_unos") {
	$finidatum = date("d. m. Y", db_result($q30,0,0));
	$tipispita = db_result($q30,0,2);

	// Korisničke preference za masovni unos
	$format = int_param('format');
	if ($format == 0) {
		$format = db_get("select vrijednost from preference where korisnik=$userid and preferenca='mass-input-format'");
		if ($format === false) //default vrijednost
			$format=0;
	}
	
	$separator = int_param('separator');
	if ($separator == 0) {
		$separator = db_get("select vrijednost from preference where korisnik=$userid and preferenca='mass-input-separator'");
		if ($separator === false) //default vrijednost
			$separator=0;
	}

	?>
	<h4>Masovni unos ocjena za ispit <?=$tipispita?>, održan <?=$finidatum?></h4>

	<?=genform("POST");?>
	<input type="hidden" name="fakatradi" value="0">
	<input type="hidden" name="akcija" value="massinput">
	<input type="hidden" name="nazad" value="">
	<input type="hidden" name="brpodataka" value="1">
	<input type="hidden" name="duplikati" value="0">

	<textarea name="massinput" cols="50" rows="10"><?
	if (strlen($_POST['nazad'])>1) print $_POST['massinput'];
	?></textarea><br/>
	<br/>Format imena i prezimena: <select name="format" class="default">
	<option value="0" <? if($format==0) print "SELECTED";?>>Prezime[TAB]Ime</option>
	<option value="1" <? if($format==1) print "SELECTED";?>>Ime[TAB]Prezime</option>
	<option value="2" <? if($format==2) print "SELECTED";?>>Prezime Ime</option>
	<option value="3" <? if($format==3) print "SELECTED";?>>Ime Prezime</option></select>&nbsp;
	Separator: <select name="separator" class="default">
	<option value="0" <? if($separator==0) print "SELECTED";?>>Tab</option>
	<option value="1" <? if($separator==1) print "SELECTED";?>>Zarez</option></select><br/><br>
	<input type="submit" value="  Dodaj  ">
	</form>
	<p><a href="?sta=nastavnik/ispiti&predmet=<?=$predmet?>&ag=<?=$ag?>">Nazad na tabelu ispita</a></p>
	<?
	return;
}


// Kreiranje novog ispita

if ($_REQUEST['akcija']=="novi_ispit") {
	$naziv = db_escape($_POST['naziv']);
	$dan = intval($_POST['day']);
	$mjesec = intval($_POST['month']);
	$godina = intval($_POST['year']);
	$mdat = mktime(0,0,0,$mjesec,$dan,$godina);


	$tipispita = intval($_POST['tipispita']);

	// Da li je ispit vec registrovan?
	$ispit = db_get("select id from ispit where predmet=$predmet and datum=FROM_UNIXTIME('$mdat') and komponenta=$tipispita and akademska_godina=$ag");
	if ($ispit !== false) {
		nicemessage("Ispit već postoji.");
	} else {
		db_query("insert into ispit set predmet=$predmet, akademska_godina=$ag, datum=FROM_UNIXTIME('$mdat'), komponenta=$tipispita");
		$ispit = db_insert_id();
		nicemessage("Ispit uspješno kreiran.");
		zamgerlog("kreiran novi ispit (predmet pp$predmet, ag$ag)", 4); // 4 - audit
		zamgerlog2("kreiran novi ispit", $ispit, $predmet, $ag);
	}
}


// Izvještaj rezultati ispita

if ($_REQUEST['akcija']=="rezultati_ispita") {
	$ispit = intval($_REQUEST['ispit']);
	?>
	<form action="index.php" method="POST">
	<input type="hidden" name="sta" value="izvjestaj/rezultati_ispita">
	<input type="hidden" name="ispit" value="<?=$ispit;?>">
	<input type="hidden" name="predmet" value="<?=$predmet ?>">
	<input type="hidden" name="ag" value="<?=$ag ?>">
	<h3>Rezultati ispita</h3>
	<p>Molimo da u polje ispod unesete obaviještenje o terminu uvida u radove koje će biti dodato na dno izvještaja.</p>
	<textarea name="obavijest_uvid" rows="10" cols="60"></textarea>
	<p>U koliko kolona želite ispis: <select name="kolone"><option value="1">Jedna kolona</option><option value="2">Dvije kolone</option></select></p>
	<input type="submit" value=" Kreiraj izvještaj "></form>
	<?

	return;
}




// GLAVNI EKRAN

// Tabela unesenih ispita

$q500 = db_query("select i.id,UNIX_TIMESTAMP(i.datum),k.gui_naziv from ispit as i, komponenta as k where i.predmet=$predmet and i.akademska_godina=$ag and i.komponenta=k.id order by i.datum, k.gui_naziv");

?>
<br>
<table border="0" cellspacing="1" cellpadding="2">
<thead>
<tr bgcolor="#999999">
	<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Tip ispita</font></td>
	<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Datum ispita</font></td>
	<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Opcije</font></td>
</tr>
</thead>
<tbody>
<?

$brojac=1;

if (db_num_rows($q500)<1)
	print "Nije unesen nijedan ispit.";

while (db_fetch3($q500, $id, $vrijeme, $naziv)) {
	?>
	<tr>
		<td align="left"><?=$naziv?></td>
		<td align="left"><?=date("d.m.Y.",date($vrijeme));?></td>
		<td align="left">
			<a href="?sta=nastavnik/ispiti&amp;akcija=masovni_unos&ispit=<?=$id;?>&amp;predmet=<?=$predmet ?>&amp;ag=<?=$ag ?>">Masovni unos rezultata</a>
			*
			<a href="?sta=nastavnik/ispiti&amp;akcija=promjena&ispit=<?=$id;?>&amp;predmet=<?=$predmet ?>&amp;ag=<?=$ag ?>">Promjena</a>
			*
			<a href="?sta=nastavnik/ispiti&amp;akcija=brisanje&ispit=<?=$id;?>&amp;predmet=<?=$predmet ?>&amp;ag=<?=$ag ?>">Brisanje</a>
			*
			<a href="?sta=nastavnik/prijava_ispita&amp;ispit=<?=$id;?>&amp;predmet=<?=$predmet ?>&amp;ag=<?=$ag ?>">Termini</a>
			*
			<a href="?sta=izvjestaj/ispit&amp;ispit=<?=$id;?>&amp;predmet=<?=$predmet ?>">Statistika</a>
			*
			<a href="?sta=nastavnik/ispiti&amp;akcija=rezultati_ispita&amp;ispit=<?=$id;?>&amp;predmet=<?=$predmet ?>&amp;ag=<?=$ag ?>">Rezultati ispita</a>
		</td>
	</tr>
	<?

	$brojac++;
}
?>
</tbody></table><br>

<p>Ako želite da unosite rezultate ispita jedan po jedan u tabelu studenata, koristite <a href="?sta=saradnik/intro">Spisak predmeta i grupa</a></p>
<?




// Forma za kreiranje ispita

?>
<p>&nbsp;</p>
<?=genform("POST")?>
<input type="hidden" name="akcija" value="novi_ispit">

<p><b>Dodaj novi ispit</b></p>

<!--br/>Naziv ispita: <input type="text" name="naziv" size="20">&nbsp;-->
<p>Tip ispita: <select name="tipispita" class="default"><?
	$tipispita = intval($_POST['tipispita']);
	$q510 = db_query("select k.id,k.gui_naziv from tippredmeta_komponenta as tpk,komponenta as k, akademska_godina_predmet as agp where agp.predmet=$predmet and agp.tippredmeta=tpk.tippredmeta and agp.akademska_godina=$ag and tpk.komponenta=k.id and (k.tipkomponente=1 or k.tipkomponente=2) order by k.id");
	while (db_fetch2($q510, $id, $naziv)) {
		print '<option value="'.$id.'"';
		if ($tipispita==$id) print ' SELECTED';
		print '>'.$naziv.'</option>'."\n";
	}
?></select>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Datum: <?
$day=intval($_POST['day']); $month=intval($_POST['month']); $year=intval($_POST['year']); 
if ($day>0) print datectrl($day,$month,$year);
else print datectrl(date('d'),date('m'),date('Y'));
?><br/><br/>

<input type="submit" value="  Dodaj  ">
<br/><br/><br/>

</form></p>
<?


}

?>
