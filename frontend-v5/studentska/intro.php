<?

// STUDENTSKA/INTRO - uvodna stranica za studentsku

// v3.9.1.0 (2008/02/19) + Preimenovan bivsi admin_nihada
// v3.9.1.1 (2008/03/26) + Nova auth tabela
// v3.9.1.2 (2008/08/28) + Tabela osoba umjesto auth; dodana potvrda izmjene licnih podataka
// v3.9.1.3 (2008/10/03) + Destruktivni zahtjevi prebaceni na POST radi sukladnosti sa RFCom
// v4.0.0.0 (2009/02/19) + Release
// v4.0.0.1 (2009/03/05) + Slanje poruke studentu da je zahtjev prihvacen / odbijen i komentar
// v4.0.0.2 (2009/04/20) + Broj indexa ne mora biti integer :(
// v4.0.9.1 (2009/06/19) + Tabela osoba: ukinuto polje srednja_skola (to ce biti rijeseno na drugi nacin); polje mjesto_rodjenja prebaceno na sifrarnik; dodano polje adresa_mjesto kao FK na isti sifrarnik


function studentska_intro() {

global $userid,$user_siteadmin,$user_studentska,$conf_files_path;


// Provjera privilegija

if (!$user_studentska && !$user_siteadmin) {
	zamgerlog("nije studentska",3); // 3: error
	biguglyerror("Pristup nije dozvoljen.");
	return;
}


// Akcije

function promjena($nominativ, $u, $iz) {
	if ($iz==$u) return;
	if ($iz=="" || $iz=="0" || $iz=="01. 01. 1970." || !preg_match("/\w/", $iz)) {
		print "<li>Upis novog podatka <b>$nominativ</b> (vrijednost: <b>$u</b>)</li>\n";
	} else if ($u=="" || $u=="0" || !preg_match("/\w/", $u)) {
		print "<li>Brisanje podatka <b>$nominativ</b> (stara vrijednost: <b>$iz</b>)</li>\n";
	} else {
		print "<li>Promjena podatka <b>$nominativ</b> iz vrijednosti <b>$iz</b> u vrijednost <b>$u</b></li>\n";
	}
}

if ($_POST['akcija'] == "Prihvati zahtjev" && check_csrf_token()) {
	$id = intval($_REQUEST['id']);
	$osoba = intval($_REQUEST['osoba']);
	$q100 = myquery("select pp.osoba, pp.ime, pp.prezime, pp.email, pp.brindexa, pp.datum_rodjenja, pp.mjesto_rodjenja, pp.drzavljanstvo, pp.jmbg, pp.adresa, pp.adresa_mjesto, pp.telefon, pp.kanton, pp.imeoca, pp.prezimeoca, pp.imemajke, pp.prezimemajke, pp.spol, pp.nacionalnost, pp.slika, UNIX_TIMESTAMP(pp.vrijeme_zahtjeva) from promjena_podataka as pp where pp.id=$id order by pp.vrijeme_zahtjeva");
	while ($r100 = mysql_fetch_row($q100)) {
		// Sve parametre treba ponovo escape-ati
		// Npr: korisnik je ukucao Meho'
		// - prilikom inserta u tabelu promjena podataka ovo se pretvara u Meho\'
		// - u tabeli se ustvari nalazi Meho'
		// - vrijednost varijable $r100[1] je Meho'
		$ime = mysql_real_escape_string($r100[1]);
		$prezime = mysql_real_escape_string($r100[2]);
		$email = mysql_real_escape_string($r100[3]);
		$brindexa = mysql_real_escape_string($r100[4]);
		$datum_rodjenja = mysql_real_escape_string($r100[5]);
		// mjesto rodjenja je tipa int
		// drzavljanstvo je tipa int
		$jmbg = mysql_real_escape_string($r100[8]);
		$adresa = mysql_real_escape_string($r100[9]);
		// adresa_mjesto je tipa int
		$telefon = mysql_real_escape_string($r100[11]);
		// kanton je tipa int
		$imeoca = mysql_real_escape_string($r100[13]);
		$prezimeoca = mysql_real_escape_string($r100[14]);
		$imemajke = mysql_real_escape_string($r100[15]);
		$prezimemajke = mysql_real_escape_string($r100[16]);
		// spol je tipa enum
		// nacionalnost je tipa int
		$slikapromjena = $r100[19];

		$q110 = myquery("update osoba set ime='$ime', prezime='$prezime', email='$email', brindexa='$brindexa', datum_rodjenja='$datum_rodjenja', mjesto_rodjenja=$r100[6], drzavljanstvo=$r100[7], jmbg='$jmbg', adresa='$adresa', adresa_mjesto=$r100[10], telefon='$telefon', kanton=$r100[12], imeoca='$imeoca', prezimeoca='$prezimeoca', imemajke='$imemajke', prezimemajke='$prezimemajke', spol='$r100[17]', nacionalnost=$r100[18] where id=$r100[0]");
		$vrijeme_zahtjeva = $r100[20];

		// Provjera izmjene slike
		$q115 = myquery("select slika from osoba where id=$r100[0]");
		$staraslika = mysql_result($q115,0,0);
		if ($staraslika != $slikapromjena) {
			$novaslika = $slikapromjena;
			$novaslika = str_replace("-promjena", "", $novaslika);
			$prefiks = "$conf_files_path/slike/";
			if (file_exists($prefiks.$staraslika))
				unlink($prefiks.$staraslika);
			if ($slikapromjena != "")
				rename($prefiks.$slikapromjena, $prefiks.$novaslika);
			$q117 = myquery("update osoba set slika='$novaslika' where id=$r100[0]");
		}
	}
	$q120 = myquery("delete from promjena_podataka where id=$id");
	zamgerlog("prihvacen zahtjev za promjenu podataka korisnika u$osoba", 4);
	print "Zahtjev je prihvaćen";

	// Poruka korisniku
	$tekst_poruke = "Na dan ".date("d. m. Y.", $vrijeme_zahtjeva).", u ".date("H:i:s", $vrijeme_zahtjeva)." poslali ste zahtjev za promjenu ličnih podataka. Vaš zahtjev je prihvaćen. Klikom na link Profil možete vidjeti vaše nove podatke.";
	if (strlen($_REQUEST['komentar'])>2)
		$tekst_poruke .= "\n\nPovodom Vašeg zahtjeva, Studentska služba vam je uputila sljedeći komentar:\n\t".$_REQUEST['komentar'];
	$q310 = myquery("insert into poruka set tip=2, opseg=7, primalac=$osoba, posiljalac=$userid, vrijeme=NOW(), ref=0, naslov='Vaš zahtjev za promjenu podataka je prihvaćen', tekst='$tekst_poruke'");

	return;
}

if ($_POST['akcija'] == "Odbij zahtjev" && check_csrf_token()) {
	$id = intval($_REQUEST['id']);
	$osoba = intval($_REQUEST['osoba']);

	$q195 = myquery("select UNIX_TIMESTAMP(vrijeme_zahtjeva), slika from promjena_podataka where id=$id");
	if (mysql_num_rows($q195)<1) {
		niceerror("Nepostojeci zahtjev sa IDom $id.");
		zamgerlog("Nepostojeci zahtjev sa IDom $id.", 3);
		return;
	}
	$vrijeme_zahtjeva=mysql_result($q195,0,0);
	$slikapromjena=mysql_result($q195,0,1);

	// Treba li obrisati viška sliku?
	$q197 = myquery("select slika from osoba where id=$osoba");
	if (mysql_result($q197,0,0) != $slikapromjena)
		unlink ("$conf_files_path/slike/$slikapromjena");

	$q200 = myquery("delete from promjena_podataka where id=$id");
	zamgerlog("odbijen zahtjev za promjenu podataka korisnika u$osoba", 2);
	print "Zahtjev je odbijen";

	// Poruka korisniku
	$tekst_poruke = "Na dan ".date("d. m. Y.", $vrijeme_zahtjeva).", u ".date("H:i:s", $vrijeme_zahtjeva)." poslali ste zahtjev za promjenu ličnih podataka. Vaš zahtjev je odbijen.";
	if (strlen($_REQUEST['komentar'])>2)
		$tekst_poruke .= "\n\nRazlog odbijanja zahtjeva je:\n\t".$_REQUEST['komentar'];
	$q310 = myquery("insert into poruka set tip=2, opseg=7, primalac=$osoba, posiljalac=$userid, vrijeme=NOW(), ref=0, naslov='Vaš zahtjev za promjenu podataka je odbijen!', tekst='$tekst_poruke'");

	return;
}


if ($_GET['akcija'] == "zahtjev") {

	$id = intval($_REQUEST['id']);
	$q100 = myquery("select pp.osoba, pp.ime, pp.prezime, pp.email, pp.brindexa, UNIX_TIMESTAMP(pp.datum_rodjenja), pp.mjesto_rodjenja, pp.drzavljanstvo, pp.jmbg, pp.adresa, pp.adresa_mjesto, pp.telefon, pp.kanton, o.ime, o.prezime, o.email, o.brindexa, UNIX_TIMESTAMP(o.datum_rodjenja), o.mjesto_rodjenja, o.drzavljanstvo, o.jmbg, o.adresa, o.adresa_mjesto, o.telefon, o.kanton, pp.imeoca, o.imeoca, pp.prezimeoca, o.prezimeoca, pp.imemajke, o.imemajke, pp.prezimemajke, o.prezimemajke, pp.spol, o.spol, pp.nacionalnost, o.nacionalnost, pp.slika, o.slika from promjena_podataka as pp, osoba as o where o.id=pp.osoba and pp.id=$id");
	if (mysql_num_rows($q100)<1) {
		niceerror("Nepoznat ID zahtjeva $id.");
		zamgerlog("nepoznat id zahtjeva za promjenu podataka $id", 3);
		return;
	}
	$osoba=mysql_result($q100,0,0);

	?>
	<p>Korisnik <b><?=mysql_result($q100,0,13)?> <?=mysql_result($q100,0,14)?></b> zatražio je sljedeće izmjene svojih ličnih podataka:
	<ul>
	<?
	promjena("ime", mysql_result($q100,0,1), mysql_result($q100,0,13));
	promjena("prezime", mysql_result($q100,0,2), mysql_result($q100,0,14));
	promjena("ime oca", mysql_result($q100,0,25), mysql_result($q100,0,26));
	promjena("prezime oca", mysql_result($q100,0,27), mysql_result($q100,0,28));
	promjena("ime majke", mysql_result($q100,0,29), mysql_result($q100,0,30));
	promjena("prezime majke", mysql_result($q100,0,31), mysql_result($q100,0,32));

	$starispol = mysql_result($q100,0,33); $novispol = mysql_result($q100,0,34);
	if ($starispol != $novispol) {
		if ($starispol=="M") $starispol="muški";
		if ($starispol=="Z") $starispol="ženski";
		if ($novispol=="M") $novispol="muški";
		if ($novispol=="Z") $novispol="ženski";
		promjena ("spol", $starispol, $novispol);
	}

	promjena("kontakt e-mail adresa", mysql_result($q100,0,3), mysql_result($q100,0,15));
	promjena("broj indexa", mysql_result($q100,0,4), mysql_result($q100,0,16));
	promjena("datum rođenja", date("d. m. Y.", mysql_result($q100,0,5)), date("d. m. Y.", mysql_result($q100,0,17)));

	// Mjesto rodjenja
	$staromj=mysql_result($q100,0,6); $novomj=mysql_result($q100,0,18);
	if ($staromj!=$novomj) {
		if ($staromj != 0) {
			$q101 = myquery("select naziv from mjesto where id=$staromj");
			$staromj = mysql_result($q101,0,0);
		}
		if ($novomj != 0) {
			$q102 = myquery("select naziv from mjesto where id=$novomj");
			$novomj = mysql_result($q102,0,0);
		}
		promjena("mjesto rođenja", $staromj, $novomj);
	}

	// Drzavljanstvo
	$starodrz=mysql_result($q100,0,7); $novodrz=mysql_result($q100,0,19);
	if ($starodrz!=$novodrz) {
		if ($starodrz != 0) {
			$q101 = myquery("select naziv from drzava where id=$starodrz");
			$starodrz = mysql_result($q101,0,0);
		}
		if ($novodrz != 0) {
			$q102 = myquery("select naziv from drzava where id=$novodrz");
			$novodrz = mysql_result($q102,0,0);
		}
		promjena("državljanstvo", $starodrz, $novodrz);
	}

	// Nacionalnost
	$staronac=mysql_result($q100,0,35); $novonac=mysql_result($q100,0,36);
	if ($staronac!=$novonac) {
		if ($staronac != 0) {
			$q101 = myquery("select naziv from nacionalnost where id=$staronac");
			$staronac = mysql_result($q101,0,0);
		}
		if ($novonac != 0) {
			$q102 = myquery("select naziv from nacionalnost where id=$novonac");
			$novonac = mysql_result($q102,0,0);
		}
		promjena("nacionalnost", $staronac, $novonac);
	}

	promjena("JMBG", mysql_result($q100,0,8), mysql_result($q100,0,20));

	// Adresa
	$staraadr = mysql_result($q100,0,9); $novaadr = mysql_result($q100,0,21);
	$said = mysql_result($q100,0,10); $naid = mysql_result($q100,0,22);
	if ($said != 0) {
		$q103 = myquery("select naziv from mjesto where id=$said");
		$staraadr .= ", ".mysql_result($q103,0,0);
	}
	if ($naid != 0) {
		$q103 = myquery("select naziv from mjesto where id=$naid");
		$novaadr .= ", ".mysql_result($q103,0,0);
	}
	promjena("adresa", $staraadr, $novaadr);

	promjena("telefon", mysql_result($q100,0,11), mysql_result($q100,0,23));

	$starikanton = mysql_result($q100,0,12); $novikanton = mysql_result($q100,0,24);
	if ($starikanton != $novikanton) {
		if ($starikanton != 0) {
			$q110 = myquery("select naziv from kanton where id=$starikanton");
			$starikanton = mysql_result($q110,0,0);
		}
		if ($novikanton != 0) {
			$q112 = myquery("select naziv from kanton where id=$novikanton");
			$novikanton = mysql_result($q112,0,0);
		}
		promjena("kanton", $starikanton, $novikanton);
	}

	?>
	</ul><p>&nbsp;</p>
	<?

	$staraslika = mysql_result($q100,0,38); $novaslika = mysql_result($q100,0,37);
	if ($staraslika != $novaslika) {
		if ($staraslika=="") {
			?>
			<p>Dodavanje slike:<br><img src="?sta=common/slika&osoba=<?=$osoba?>&promjena=1"></p>
			<?
		}
		else if ($novaslika=="") {
			?>
			<p>Brisanje slike (stara slika):<br><img src="?sta=common/slika&osoba=<?=$osoba?>"></p>
			<?
		}
		else {
			?>
			<p>Promjena slike</p>
			<table border="0"><tr><td valign="top">Iz:<br><img src="?sta=common/slika&osoba=<?=$osoba?>"></td>
			<td valign="top">U<br><img src="?sta=common/slika&osoba=<?=$osoba?>&promjena=1"></td></tr></table>
			<?
		}
		print "<p>&nbsp;</p>\n";
	}

	?>
	<?=genform("POST")?>
	<input type="hidden" name="osoba" value="<?=$osoba?>">
	<input type="submit" name="akcija" value="Prihvati zahtjev">
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<input type="submit" name="akcija" value="Odbij zahtjev"><br><br>
	Eventualno upišite komentar koji želite poslati studentu:<br>
	<input type="text" size="50" name="komentar">
	</form>
	<?
	
	return;
}


// AKCIJA=IZVOZ

if ($_GET['akcija'] == "izvoz") {
	$predmet = intval($_GET['predmet']);
	if ($predmet==0) {
		$q20 = myquery("select p.id, p.naziv, i.kratki_naziv, count(*) from konacna_ocjena as ko, predmet as p, institucija as i where (ko.izvezena=0 or ko.izvezena=2) and ko.predmet=p.id and p.institucija=i.id group by ko.predmet order by i.kratki_naziv, p.naziv");
		if (mysql_num_rows($q20)<1) {
			nicemessage("Nema novih podataka za izvoz"); // Ne bi se smjelo desiti, osim korištenjem istorije?
			return;
		}
		print "<p>Podaci za izvoz:<ul>\n";
		while ($r20 = mysql_fetch_row($q20)) {
			print "<li><a href=\"?sta=studentska/intro&akcija=izvoz&predmet=$r20[0]\">$r20[3] ocjena iz: $r20[1] ($r20[2])</a></li>\n";
		}
		print "</ul></p>\n";
	} else {
		$q25 = myquery("select p.naziv, i.kratki_naziv from predmet as p, institucija as i where p.id=$predmet and p.institucija=i.id");
		print "<h2>".mysql_result($q25,0,0)." (".mysql_result($q25,0,1).")</h2>\n";


		$q30 = myquery("select o.ime, o.prezime, o.brindexa, p.naziv, ko.izvezena, ko.ocjena, UNIX_TIMESTAMP(ko.datum), ko.student from konacna_ocjena as ko, predmet as p, osoba as o where ko.student=o.id and ko.predmet=$predmet and p.id=$predmet and (ko.izvezena=0 || ko.izvezena=2 || ko.izvezena=3)");
		if (mysql_num_rows($q30)==0) {
			print "<p>Nema novih podataka za izvoz.</p>";
			// Ne bi se smjelo desiti, osim korištenjem istorije?
			print "<p><a href=\"?sta=studentska/intro&akcija=izvoz\">Nazad</a></p>\n";
			return;
		}
		$ispis=$ispis_greska=$ispis_greska_datum="";
		$zadnji_datum=0;
		while ($r30 = mysql_fetch_row($q30)) {
			$q35 = myquery("select UNIX_TIMESTAMP(ist.datumvrijeme) from ispit_termin as ist, ispit as i, komponenta as k, student_ispit_termin as sit where sit.student=$r30[7] and sit.ispit_termin=ist.id and ist.ispit=i.id and i.predmet=$predmet and i.komponenta=k.id and k.gui_naziv='Usmeni' order by ist.datumvrijeme desc limit 1");
			if (mysql_num_rows($q35)==0) {
				//$nesta = date("d/m/Y", $r30[6]);
				$ispis_greska .= "$r30[1] $r30[0], ";
				continue;
			}
			if (mysql_result($q35,0,0) >= time()) {
				$ispis_greska_datum .= "$r30[1] $r30[0], ";
				continue;
			}

			$datum_ocjene = date("d/m/Y", mysql_result($q35,0,0)); // Datum

			if ($r30[4]==0) {
				$ispis .= "DODAJ_OCJENU,$r30[0] $r30[1],$r30[2],$r30[3],$r30[5],$datum_ocjene\n";
			} else if ($r30[4]==2) {
				$ispis .= "OBRISI_OCJENU,$r30[0] $r30[1],$r30[2],$r30[3]\n";
				$ispis .= "DODAJ_OCJENU,$r30[0] $r30[1],$r30[2],$r30[3],$r30[5],$datum_ocjene\n";
			} else if ($r30[4]==3) {
				$ispis .= "OBRISI_OCJENU,$r30[0] $r30[1],$r30[2],$r30[3]\n";
			}
			if ($r30[6]>$zadnji_datum) $zadnji_datum=$r30[6];
		}
		?>
		<textarea rows="10" cols="80"><?=$ispis?></textarea>
		<p><br></p>
		<? if ($ispis_greska != "") {
			?>
			<p><font color="red">Pored navedenih, sljedeći studenti imaju upisanu ocjenu a nisu prijavljeni na ispit: <?=$ispis_greska?></font></p>
		<? } else if ($ispis_greska_datum != "") {
			?>
			<p><font color="red">Pored navedenih, sljedeći studenti su prijavljeni na ispit sa datumom u budućnosti: <?=$ispis_greska_datum?></font></p>
		<? } else { ?>
		<p>
		<a href="?sta=studentska/intro&akcija=izvoz_ponisti&predmet=<?=$predmet?>&zadnji_datum=<?=$zadnji_datum?>">Kada uspješno ubacite podatke u ISSS, kliknite ovdje da označite podatke kao ubačene.</a></p>
		<?
		}
	}
/*	$q20 = myquery("select vrijednost from preference where korisnik=0 and preferenca='datum-zadnjeg-izvoza'");
	if (mysql_num_rows($q20)<1) {
		$q25 = myquery("insert into preference set korisnik=0, preferenca='datum-zadnjeg-izvoza', vrijednost=UNIX_TIMESTAMP(NOW())");
		print "<p>Nema novih podataka za izvoz.</p>";
	} else {
		$q30 = myquery("select dogadjaj, UNIX_TIMESTAMP(vrijeme) from log where vrijeme>=FROM_UNIXTIME(".mysql_result($q20,0,0).") and (dogadjaj like 'AJAH ko - dodana ocjena %' or dogadjaj like 'AJAH ko - obrisana ocjena %' or dogadjaj like 'AJAH ko - izmjena ocjene % ' or dogadjaj like 'dopisana ocjena %' or dogadjaj like 'masovno dodana ocjena %') order by vrijeme");
		if (mysql_num_rows($q30)==0) {
			print "<p>Nema novih podataka za izvoz.</p>";
		} else {
			$ispis="";
			while ($r30 = mysql_fetch_row($q30)) {
				if (preg_match("/AJAH ko - dodana ocjena (\d+) \(predmet pp(\d+), student u(\d+)\)/", $r30[0], $matches)) {
					$q40 = myquery("select ime, prezime, brindexa from osoba where id=".$matches[3]);
					$q50 = myquery("select naziv from predmet where id=".$matches[2]);
					$ispis .= "DODAJ_OCJENU,".mysql_result($q40,0,0)." ".mysql_result($q40,0,1).",".mysql_result($q40,0,2).",".mysql_result($q50,0,0).",$matches[1],".date("d/m/Y", $r30[1])."\n";
				}
				if (preg_match("/masovno dodana ocjena (\d+) \(predmet pp(\d+), student u(\d+)\)/", $r30[0], $matches)) {
					$q40 = myquery("select ime, prezime, brindexa from osoba where id=".$matches[3]);
					$q50 = myquery("select naziv from predmet where id=".$matches[2]);
					$ispis .="DODAJ_OCJENU,".mysql_result($q40,0,0)." ".mysql_result($q40,0,1).",".mysql_result($q40,0,2).",".mysql_result($q50,0,0).",$matches[1],".date("d/m/Y", $r30[1])."\n";
				}
				if (preg_match("/AJAH ko - obrisana ocjena (\d+) \(predmet pp(\d+), student u(\d+)\)/", $r30[0], $matches)) {
					$q40 = myquery("select ime, prezime, brindexa from osoba where id=$matches[3]");
					$q50 = myquery("select naziv from predmet where id=$matches[2]");
					$ispis .= "OBRISI_OCJENU,".mysql_result($q40,0,0)." ".mysql_result($q40,0,1).",".mysql_result($q40,0,2).",".mysql_result($q50,0,0)."\n";
				}
				if (preg_match("/AJAH ko - izmjena ocjene \d+ u (\d+) \(predmet pp(\d+), student u(\d+)\)/", $r30[0], $matches)) {
					$q40 = myquery("select ime, prezime, brindexa from osoba where id=$matches[3]");
					$q50 = myquery("select naziv from predmet where id=$matches[2]");
					$ispis .= "OBRISI_OCJENU,".mysql_result($q40,0,0)." ".mysql_result($q40,0,1).",".mysql_result($q40,0,2).",".mysql_result($q50,0,0)."\nDODAJ_OCJENU,".mysql_result($q40,0,0)." ".mysql_result($q40,0,1).",".mysql_result($q40,0,2).",".mysql_result($q50,0,0).",$matches[1],".date("d/m/Y", $r30[1])."\n";
				}
			}
			?>
			<textarea rows="10" cols="80"><?=$ispis?></textarea>
			<p><br></p>
			<p>
			<a href="?sta=studentska/intro&akcija=izvoz_ponisti">Kada uspješno ubacite podatke u ISSS, kliknite ovdje da označite podatke kao ubačene.</a></p>
			<?
		}
	}*/


	return;
}

if ($_GET['akcija'] == "izvoz_ponisti") {
/*	$q60 = myquery("update preference set vrijednost=UNIX_TIMESTAMP(NOW()) where korisnik=0 and preferenca='datum-zadnjeg-izvoza'");
	zamgerlog("ponisten datum za izvoz", 2);*/
	$predmet = intval($_GET['predmet']);
	$zadnji_datum = intval($_GET['zadnji_datum']);
	$q65 = myquery("delete from konacna_ocjena where predmet=$predmet and datum<=FROM_UNIXTIME($zadnji_datum) and izvezena=3");
	$q60 = myquery("update konacna_ocjena set izvezena=1 where predmet=$predmet and datum<=FROM_UNIXTIME($zadnji_datum)"); 
	zamgerlog("ponisten datum za izvoz (pp$predmet)", 2);
}



// Dobrodošlica

$q1 = myquery("select ime, spol from osoba where id=$userid");
$ime = mysql_result($q1,0,0);
$spol = mysql_result($q1,0,1);
if ($spol == 'Z' || ($spol == '' && spol($ime)=="Z"))
	print "<h1>Dobro došla, ".genitiv($ime,"Z")."</h1>";
else
	print "<h1>Dobro došao, ".genitiv($ime,"M")."</h1>";


// Zahtjevi za promjenu ličnih podataka


$q10 = myquery("select pp.id, pp.osoba, UNIX_TIMESTAMP(pp.vrijeme_zahtjeva), o.ime, o.prezime from promjena_podataka as pp, osoba as o where o.id=pp.osoba order by pp.vrijeme_zahtjeva");
if (mysql_num_rows($q10)<1) {
?>
<p>Nema novih zahtjeva za promjenu ličnih podataka.</p>
<?
} else {
?>
<p><b>Zahtjevi za promjenu ličnih podataka:</b>
<ul>
<?
}

while ($r10 = mysql_fetch_row($q10)) {
	?>
	<li><a href="?sta=studentska/intro&akcija=zahtjev&id=<?=$r10[0]?>"><?=$r10[3]?> <?=$r10[4]?></a> (<?=date("d. m. Y. H:i", $r10[2])?>)</li>
	<?
}

if (mysql_num_rows($q10)>0) {
?>
</ul>
Kliknite na zahtjev da biste ga prihvatili ili odbili.
</p>

<?

}


// Podaci za eksport

$q20 = myquery("select vrijednost from preference where korisnik=0 and preferenca='datum-zadnjeg-izvoza'");
if (mysql_num_rows($q20)<1) {
	$q25 = myquery("insert into preference set korisnik=0, preferenca='datum-zadnjeg-izvoza', vrijednost=UNIX_TIMESTAMP(NOW())");
	print "<p>Nema novih podataka za izvoz.</p>";
} else {
//	$q30 = myquery("select count(*) from log where vrijeme>=FROM_UNIXTIME(".mysql_result($q20,0,0).") and (dogadjaj like 'AJAH ko - dodana ocjena %' or dogadjaj like 'AJAH ko - obrisana ocjena %' or dogadjaj like 'AJAH ko - izmjena ocjene % ' or dogadjaj like 'dopisana ocjena %' or dogadjaj like 'masovno dodana ocjena %')");
	$q30 = myquery("select count(*) from konacna_ocjena where izvezena=0 or izvezena=2");
	if (mysql_result($q30,0,0)==0) {
		print "<p>Nema novih podataka za izvoz.</p>";
	} else {
		?>
		<p><a href="?sta=studentska/intro&akcija=izvoz">Imate <?=mysql_result($q30,0,0)?> novih ocjena koje treba izvesti u ISSS! Kliknite ovdje za fajl</a></p>
		<?
	}
}



}

?>
