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
	$q100 = myquery("select pp.osoba, pp.ime, pp.prezime, pp.brindexa, pp.datum_rodjenja, pp.mjesto_rodjenja, pp.drzavljanstvo, pp.jmbg, pp.adresa, pp.adresa_mjesto, pp.telefon, pp.kanton, pp.imeoca, pp.prezimeoca, pp.imemajke, pp.prezimemajke, pp.spol, pp.nacionalnost, pp.slika, UNIX_TIMESTAMP(pp.vrijeme_zahtjeva), djevojacko_prezime, maternji_jezik, vozacka_dozvola, nacin_stanovanja from promjena_podataka as pp where pp.id=$id order by pp.vrijeme_zahtjeva");
	while ($r100 = mysql_fetch_row($q100)) {
		// Sve parametre treba ponovo escape-ati
		// Npr: korisnik je ukucao Meho'
		// - prilikom inserta u tabelu promjena podataka ovo se pretvara u Meho\'
		// - u tabeli se ustvari nalazi Meho'
		// - vrijednost varijable $r100[1] je Meho'
		$ime = mysql_real_escape_string($r100[1]);
		$prezime = mysql_real_escape_string($r100[2]);
		$brindexa = mysql_real_escape_string($r100[3]);
		$datum_rodjenja = mysql_real_escape_string($r100[4]);
		// mjesto rodjenja je tipa int
		// drzavljanstvo je tipa int
		$jmbg = mysql_real_escape_string($r100[7]);
		$adresa = mysql_real_escape_string($r100[8]);
		// adresa_mjesto je tipa int
		$telefon = mysql_real_escape_string($r100[10]);
		// kanton je tipa int
		$imeoca = mysql_real_escape_string($r100[12]);
		$prezimeoca = mysql_real_escape_string($r100[13]);
		$imemajke = mysql_real_escape_string($r100[14]);
		$prezimemajke = mysql_real_escape_string($r100[15]);
		// spol je tipa enum
		// nacionalnost je tipa int
		$slikapromjena = $r100[18];

		$djevojacko_prezime = mysql_real_escape_string($r100[20]);
		// maternji_jezik je tipa integer
		// vozacka_dozvola je tipa int
		// nacin_stanovanja je tipa integer

		$q110 = myquery("update osoba set ime='$ime', prezime='$prezime', brindexa='$brindexa', datum_rodjenja='$datum_rodjenja', mjesto_rodjenja=$r100[5], drzavljanstvo=$r100[6], jmbg='$jmbg', adresa='$adresa', adresa_mjesto=$r100[9], telefon='$telefon', kanton=$r100[11], imeoca='$imeoca', prezimeoca='$prezimeoca', imemajke='$imemajke', prezimemajke='$prezimemajke', spol='$r100[16]', nacionalnost=$r100[17], djevojacko_prezime='$djevojacko_prezime', maternji_jezik=$r100[21], vozacka_dozvola=$r100[22], nacin_stanovanja=$r100[23] where id=$r100[0]");
		$vrijeme_zahtjeva = $r100[19];

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
		zamgerlog("nepostojeci zahtjev sa IDom $id", 3);
		return;
	}
	$vrijeme_zahtjeva=mysql_result($q195,0,0);
	$slikapromjena=mysql_result($q195,0,1);

	// Treba li obrisati viška sliku?
	$q197 = myquery("select slika from osoba where id=$osoba");
	if ($slikapromjena != "" && mysql_result($q197,0,0) != $slikapromjena)
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
	$q100 = myquery("select pp.osoba, pp.ime, pp.prezime, pp.brindexa, UNIX_TIMESTAMP(pp.datum_rodjenja), pp.mjesto_rodjenja, pp.drzavljanstvo, pp.jmbg, pp.adresa, pp.adresa_mjesto, pp.telefon, pp.kanton, o.ime, o.prezime, o.brindexa, UNIX_TIMESTAMP(o.datum_rodjenja), o.mjesto_rodjenja, o.drzavljanstvo, o.jmbg, o.adresa, o.adresa_mjesto, o.telefon, o.kanton, pp.imeoca, o.imeoca, pp.prezimeoca, o.prezimeoca, pp.imemajke, o.imemajke, pp.prezimemajke, o.prezimemajke, pp.spol, o.spol, pp.nacionalnost, o.nacionalnost, pp.slika, o.slika, pp.djevojacko_prezime, o.djevojacko_prezime, pp.maternji_jezik, o.maternji_jezik, pp.vozacka_dozvola, o.vozacka_dozvola, pp.nacin_stanovanja, o.nacin_stanovanja from promjena_podataka as pp, osoba as o where o.id=pp.osoba and pp.id=$id");
	if (mysql_num_rows($q100)<1) {
		niceerror("Nepoznat ID zahtjeva $id.");
		zamgerlog("nepoznat id zahtjeva za promjenu podataka $id", 3);
		return;
	}
	$osoba=mysql_result($q100,0,0);

	?>
	<p>Korisnik <b><?=mysql_result($q100,0,12)?> <?=mysql_result($q100,0,13)?></b> zatražio je sljedeće izmjene svojih ličnih podataka:
	<ul>
	<?
	promjena("ime", mysql_result($q100,0,1), mysql_result($q100,0,12));
	promjena("prezime", mysql_result($q100,0,2), mysql_result($q100,0,13));
	promjena("djevojačko prezime", mysql_result($q100,0,37), mysql_result($q100,0,38));
	promjena("ime oca", mysql_result($q100,0,23), mysql_result($q100,0,24));
	promjena("prezime oca", mysql_result($q100,0,25), mysql_result($q100,0,26));
	promjena("ime majke", mysql_result($q100,0,27), mysql_result($q100,0,28));
	promjena("prezime majke", mysql_result($q100,0,29), mysql_result($q100,0,30));

	$starispol = mysql_result($q100,0,31); $novispol = mysql_result($q100,0,32);
	if ($starispol != $novispol) {
		if ($starispol=="M") $starispol="muški";
		if ($starispol=="Z") $starispol="ženski";
		if ($novispol=="M") $novispol="muški";
		if ($novispol=="Z") $novispol="ženski";
		promjena ("spol", $starispol, $novispol);
	}

	promjena("broj indexa", mysql_result($q100,0,3), mysql_result($q100,0,14));
	promjena("datum rođenja", date("d. m. Y.", mysql_result($q100,0,4)), date("d. m. Y.", mysql_result($q100,0,15)));

	// Mjesto rodjenja
	$staromj=mysql_result($q100,0,5); $novomj=mysql_result($q100,0,16);
	if ($staromj!=$novomj) {
		if ($staromj != 0) {
			$q101 = myquery("select naziv from mjesto where id=$staromj");
			$staromjn = mysql_result($q101,0,0);
		}
		if ($novomj != 0) {
			$q102 = myquery("select naziv from mjesto where id=$novomj");
			$novomjn = mysql_result($q102,0,0);
		}
		if ($staromjn == $novomjn) {
			$q101 = myquery("select o.naziv from mjesto as m, opcina as o where m.id=$staromj and m.opcina=o.id");
			$staromjn .= " (".mysql_result($q101,0,0).")";
			$q102 = myquery("select o.naziv from mjesto as m, opcina as o where m.id=$novomj and m.opcina=o.id");
			$novomjn .= " (".mysql_result($q102,0,0).")";
		}

		promjena("mjesto rođenja", $staromjn, $novomjn);
	}

	// Drzavljanstvo
	$starodrz=mysql_result($q100,0,6); $novodrz=mysql_result($q100,0,17);
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
	$staronac=mysql_result($q100,0,33); $novonac=mysql_result($q100,0,34);
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

	promjena("JMBG", mysql_result($q100,0,7), mysql_result($q100,0,18));

	// Adresa
	$staraadr = mysql_result($q100,0,8); $novaadr = mysql_result($q100,0,19);
	$said = mysql_result($q100,0,9); $naid = mysql_result($q100,0,20);
	if ($said != 0) {
		$q103 = myquery("select naziv from mjesto where id=$said");
		$staraadr .= ", ".mysql_result($q103,0,0);
	}
	if ($naid != 0) {
		$q103 = myquery("select naziv from mjesto where id=$naid");
		$novaadr .= ", ".mysql_result($q103,0,0);
	}
	promjena("adresa", $staraadr, $novaadr);

	promjena("telefon", mysql_result($q100,0,10), mysql_result($q100,0,21));

	$starikanton = mysql_result($q100,0,11); $novikanton = mysql_result($q100,0,22);
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

	$starimaternji = mysql_result($q100,0,39); $novimaternji = mysql_result($q100,0,40);
	if ($starimaternji != $novimaternji) {
		if ($starimaternji != 0) {
			$q110 = myquery("select naziv from maternji_jezik where id=$starimaternji");
			$starimaternji = mysql_result($q110,0,0);
		}
		if ($novimaternji != 0) {
			$q112 = myquery("select naziv from maternji_jezik where id=$novimaternji");
			$novimaternji = mysql_result($q112,0,0);
		}
		promjena("maternji jezik", $starimaternji, $novimaternji);
	}

	$staravozacka = mysql_result($q100,0,41); $novavozacka = mysql_result($q100,0,42);
	if ($staravozacka != $novavozacka) {
		if ($staravozacka != 0) {
			$q110 = myquery("select naziv from vozacki_kategorija where id=$staravozacka");
			$staravozacka = mysql_result($q110,0,0);
		}
		if ($novavozacka != 0) {
			$q112 = myquery("select naziv from vozacki_kategorija where id=$novavozacka");
			$novavozacka = mysql_result($q112,0,0);
		}
		promjena("vozačka dozvola", $staravozacka, $novimaternji);
	}

	$starinacinst = mysql_result($q100,0,43); $novinacinst = mysql_result($q100,0,44);
	if ($starinacinst != $novinacinst) {
		if ($starinacinst != 0) {
			$q110 = myquery("select naziv from nacin_stanovanja where id=$starinacinst");
			$starinacinst = mysql_result($q110,0,0);
		}
		if ($novinacinst != 0) {
			$q112 = myquery("select naziv from nacin_stanovanja where id=$novinacinst");
			$novinacinst = mysql_result($q112,0,0);
		}
		promjena("način stanovanja", $starinacinst, $novinacinst);
	}

	?>
	</ul><p>&nbsp;</p>
	<?

	$staraslika = mysql_result($q100,0,36); $novaslika = mysql_result($q100,0,35);
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



// Zahtjevi za dokumenta / potvrde


if ($_GET['akcija'] == "obradi_potvrdu") {
	$id = intval($_GET['id']);
	$status = intval($_GET['status']);
	$q210 = myquery("UPDATE zahtjev_za_potvrdu SET status=$status WHERE id=$id");
	zamgerlog("obradjen zahtjev za potvrdu $id (status: $status)", 2);
	zamgerlog2("obradjen zahtjev za potvrdu", $id, $status);;

	nicemessage("Zahtjev obrađen");


	// Poruka korisniku
	$q215 = myquery("SELECT UNIX_TIMESTAMP(datum_zahtjeva), student FROM zahtjev_za_potvrdu WHERE id=$id");
	$vrijeme_zahtjeva = mysql_result($q215,0,0);
	$student = mysql_result($q215,0,1);
	$tekst_poruke = "Na dan ".date("d. m. Y.", $vrijeme_zahtjeva).", u ".date("H:i:s", $vrijeme_zahtjeva)." poslali ste zahtjev za ovjereno uvjerenje ili potvrdu o redovnom studiju. Vaše uvjerenje je spremno i možete ga preuzeti u studentskoj službi.";
	$q310 = myquery("insert into poruka set tip=2, opseg=7, primalac=$student, posiljalac=$userid, vrijeme=NOW(), ref=0, naslov='Vaša potvrda/uvjerenje je spremno', tekst='$tekst_poruke'");

	// Slanje GCM poruke
	require("gcm/push_message.php");
	push_message(array($student), "Potvrde", "Vaša potvrda/uvjerenje je spremno");
	$_GET['akcija'] = "potvrda";
}

if ($_GET['akcija'] == "obrisi_potvrdu") {
	$id = intval($_GET['id']);
	$q210 = myquery("DELETE FROM zahtjev_za_potvrdu WHERE id=$id");
	zamgerlog("obrisan zahtjev za potvrdu $id", 2);
	zamgerlog2("obrisan zahtjev za potvrdu", $id, $status);;

	nicemessage("Zahtjev obrisan");

	$_GET['akcija'] = "potvrda";
}


if ($_GET['akcija'] == "potvrda") {

	if ($_REQUEST['sort'] == "prezime") {
		$order_by = "ORDER BY o.prezime, o.ime";
		$link1 = "prezime_desc";
		$link2 = "brindexa";
		$link3 = "datum";
	} else if ($_REQUEST['sort'] == "prezime_desc") {
		$order_by = "ORDER BY o.prezime DESC, o.ime DESC";
		$link1 = "prezime";
		$link2 = "brindexa";
		$link3 = "datum";
	} else if ($_REQUEST['sort'] == "datum")  {
		$order_by = "ORDER BY zzp.datum_zahtjeva";
		$link1 = "prezime";
		$link2 = "brindexa";
		$link3 = "datum_desc";
	} else if ($_REQUEST['sort'] == "datum_desc") {
		$order_by = "ORDER BY zzp.datum_zahtjeva DESC";
		$link1 = "prezime";
		$link2 = "brindexa";
		$link3 = "datum";
	} else if ($_REQUEST['sort'] == "brindexa")  {
		$order_by = "ORDER BY o.brindexa";
		$link1 = "prezime";
		$link2 = "brindexa_desc";
		$link3 = "datum";
	} else if ($_REQUEST['sort'] == "brindexa_desc") {
		$order_by = "ORDER BY o.brindexa DESC";
		$link1 = "prezime";
		$link2 = "brindexa";
		$link3 = "datum";
	} else { // Default
		$order_by = "ORDER BY zzp.datum_zahtjeva";
		$link1 = "prezime";
		$link2 = "brindexa";
		$link3 = "datum_desc";
	}

	?>
	<p><b>Neobrađeni zahtjevi</b></p>
	<table border="1" cellspacing="0" cellpadding="2">
		<tr>
			<th>R.br.</th><th><a href="?sta=studentska/intro&akcija=potvrda&sort=<?=$link1?>">Prezime i ime studenta</a></th><th><a href="?sta=studentska/intro&akcija=potvrda&sort=<?=$link2?>">Broj indeksa</a></th><th>Tip zahtjeva</th><th><a href="?sta=studentska/intro&akcija=potvrda&sort=<?=$link3?>">Datum</a></th><th>Opcije</th>
		</tr>
	<?

	$q200 = myquery("SELECT zzp.id, o.ime, o.prezime, tp.id, tp.naziv, UNIX_TIMESTAMP(zzp.datum_zahtjeva), o.id, zzp.svrha_potvrde, o.brindexa FROM zahtjev_za_potvrdu as zzp, osoba as o, tip_potvrde as tp WHERE zzp.student=o.id AND zzp.tip_potvrde=tp.id AND zzp.status=1 $order_by");
	$rbr = 1;
	while ($r200 = mysql_fetch_row($q200)) {
		if ($r200[3] == 1)
			$link_printanje = "?sta=izvjestaj/potvrda&student=$r200[6]&svrha=$r200[7]";
		else
			$link_printanje = "?sta=izvjestaj/index2&student=$r200[6]";

		print "<tr><td>$rbr</td><td>$r200[2] $r200[1]</td><td>$r200[8]</td><td>$r200[4]</td><td>".date("d.m.Y. H:i:s", $r200[5])."</td><td><a href=\"$link_printanje\">printaj</a> * <a href=\"?sta=studentska/intro&akcija=obradi_potvrdu&id=$r200[0]&status=2\">obradi</a>";

		// Dodatne kontrole
		$error = 0;
		$q210 = myquery("SELECT count(*) FROM student_studij AS ss, akademska_godina AS ag WHERE ss.student=$r200[6] AND ss.akademska_godina=ag.id AND ag.aktuelna=1");
		if (mysql_result($q210,0,0) == 0) {
			print " - <font color=\"red\">trenutno nije upisan na studij!</font>"; $error=1;
		}
		
		$q220 = myquery("SELECT mjesto_rodjenja, datum_rodjenja, jmbg FROM osoba WHERE id=$r200[6]");
		if (mysql_result($q220,0,0) == 0) {
			print " - <font color=\"red\">nedostaje mjesto rođenja</font>"; $error=1;
		}
		if (mysql_result($q220,0,1) == '0000-00-00') {
			print " - <font color=\"red\">nedostaje datum rođenja</font>"; $error=1;
		}

		if (mysql_result($q220,0,2) == "") {
			print " - <font color=\"red\">nedostaje JMBG</font>"; $error=1;
		}
		if ($error == 1)
			print " <a href=\"?sta=studentska/osobe&akcija=edit&osoba=$r200[6]\">popravi</a>";
		print "</td></tr>\n";
		$rbr++;
	}

	?>
	</table>
	<p><b>Obrađeni zahtjevi</b></p>
	<?
	if ($_REQUEST['subakcija'] == "arhiva") {
		?>
		<p><a href="?sta=studentska/intro&akcija=potvrda">Sakrij zahtjeve starije od mjesec dana</a></p>
		<?
	} else {
		?>
		<p><a href="?sta=studentska/intro&akcija=potvrda&subakcija=arhiva">Prikaži zahtjeve starije od mjesec dana</a></p>
		<?
	}
	?>
	<table border="1" cellspacing="0" cellpadding="2">
		<tr>
			<th>R.br.</th><th><a href="?sta=studentska/intro&akcija=potvrda&sort=<?=$link1?>">Prezime i ime studenta</a></th><th><a href="?sta=studentska/intro&akcija=potvrda&sort=<?=$link2?>">Broj indeksa</a></th><th>Tip zahtjeva</th><th><a href="?sta=studentska/intro&akcija=potvrda&sort=<?=$link3?>">Datum</a></th><th>Opcije</th>
		</tr>
	<?

	if ($_REQUEST['subakcija'] == "arhiva") $arhiva = "";
	else $arhiva = "AND zzp.datum_zahtjeva > DATE_SUB(NOW(), INTERVAL 1 MONTH)";

	$q200 = myquery("SELECT zzp.id, o.ime, o.prezime, tp.id, tp.naziv, UNIX_TIMESTAMP(zzp.datum_zahtjeva), o.id, zzp.svrha_potvrde, o.brindexa FROM zahtjev_za_potvrdu as zzp, osoba as o, tip_potvrde as tp WHERE zzp.student=o.id AND zzp.tip_potvrde=tp.id AND zzp.status=2 $arhiva $order_by");
	$rbr = 1;
	while ($r200 = mysql_fetch_row($q200)) {
		if ($r200[3] == 1)
			$link_printanje = "?sta=izvjestaj/potvrda&student=$r200[6]&svrha=$r200[7]";
		else
			$link_printanje = "?sta=izvjestaj/index2&student=$r200[6]";

		print "<tr><td>$rbr</td><td>$r200[2] $r200[1]</td><td>$r200[8]</td><td>$r200[4]</td><td>".date("d.m.Y. H:i:s", $r200[5])."</td><td><a href=\"$link_printanje\">printaj</a> * <a href=\"?sta=studentska/intro&akcija=obradi_potvrdu&id=$r200[0]&status=1\">postavi kao neobrađen</a> * <a href=\"?sta=studentska/intro&akcija=obrisi_potvrdu&id=$r200[0]\">obriši</a></td></tr>\n";
		$rbr++;
	}

	print "</table>\n";
	return;
}



// -----------------------------------------
//
// POCETNA STRANICA
//
// -----------------------------------------




// Dobrodošlica

$q1 = myquery("select ime, spol from osoba where id=$userid");
$ime = mysql_result($q1,0,0);
$spol = mysql_result($q1,0,1);
if ($spol == 'Z' || ($spol == '' && spol($ime)=="Z"))
	print "<h1>Dobro došla, ".vokativ($ime,"Z")."</h1>";
else
	print "<h1>Dobro došao, ".vokativ($ime,"M")."</h1>";


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


// Zathjevi za dokumenta

$q40 = myquery("SELECT count(*) FROM zahtjev_za_potvrdu WHERE status=1");
$br_zahtjeva = mysql_result($q40, 0, 0);
if ($br_zahtjeva > 0)
	print "<p><a href=\"?sta=studentska/intro&akcija=potvrda\">Imate $br_zahtjeva neobrađenih zahtjeva za dokumenta.</a></p>";
else
	print "<p>Nema neobrađenih zahtjeva za dokumenta.</p>";



}

?>
