<?

// STUDENTSKA/INTRO - uvodna stranica za studentsku



function studentska_intro() {

global $userid,$user_siteadmin,$user_studentska,$conf_files_path,$conf_jasper,$conf_jasper_url;

require_once("lib/utility.php"); // spol, vokativ
	require_once("lib/formgen.php"); // datectl


// Provjera privilegija

if (!$user_studentska && !$user_siteadmin) {
	zamgerlog("nije studentska",3); // 3: error
	zamgerlog2("nije studentska"); // 3: error
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

if (param('akcija') === "Prihvati zahtjev" && check_csrf_token()) {
	$id = intval($_REQUEST['id']);
	$osoba = intval($_REQUEST['osoba']);
	$q100 = db_query("select pp.osoba, pp.ime, pp.prezime, pp.brindexa, pp.datum_rodjenja, pp.mjesto_rodjenja, pp.drzavljanstvo, pp.jmbg, pp.adresa, pp.adresa_mjesto, pp.telefon, pp.kanton, pp.imeoca, pp.prezimeoca, pp.imemajke, pp.prezimemajke, pp.spol, pp.nacionalnost, pp.boracke_kategorije, pp.slika, UNIX_TIMESTAMP(pp.vrijeme_zahtjeva) from promjena_podataka as pp where pp.id=$id order by pp.vrijeme_zahtjeva");
	while ($r100 = db_fetch_row($q100)) {
		// Sve parametre treba ponovo escape-ati
		// Npr: korisnik je ukucao Meho'
		// - prilikom inserta u tabelu promjena_podataka ovo se pretvara u Meho\'
		// - u tabeli se ustvari nalazi Meho'
		// - vrijednost varijable $r100[1] je Meho'
		$ime = db_escape_string($r100[1]);
		$prezime = db_escape_string($r100[2]);
		$brindexa = db_escape_string($r100[3]);
		$datum_rodjenja = db_escape_string($r100[4]);
		// mjesto rodjenja je tipa int
		// drzavljanstvo je tipa int
		$jmbg = db_escape_string($r100[7]);
		$adresa = db_escape_string($r100[8]);
		// adresa_mjesto je tipa int
		$telefon = db_escape_string($r100[10]);
		// kanton je tipa int
		$imeoca = db_escape_string($r100[12]);
		$prezimeoca = db_escape_string($r100[13]);
		$imemajke = db_escape_string($r100[14]);
		$prezimemajke = db_escape_string($r100[15]);
		// spol je tipa enum
		// nacionalnost je tipa int
		// boracke_kategorije je boolean
		$slikapromjena = $r100[19];

		$q110 = db_query("update osoba set ime='$ime', prezime='$prezime', brindexa='$brindexa', datum_rodjenja='$datum_rodjenja', mjesto_rodjenja=$r100[5], drzavljanstvo=$r100[6], jmbg='$jmbg', adresa='$adresa', adresa_mjesto=$r100[9], telefon='$telefon', kanton=$r100[11], imeoca='$imeoca', prezimeoca='$prezimeoca', imemajke='$imemajke', prezimemajke='$prezimemajke', spol='$r100[16]', nacionalnost=$r100[17], boracke_kategorije=$r100[18] where id=$r100[0]");
		$vrijeme_zahtjeva = $r100[20];

		// Provjera izmjene slike
		$q115 = db_query("select slika from osoba where id=$r100[0]");
		$staraslika = db_result($q115,0,0);
		if ($staraslika != $slikapromjena) {
			$novaslika = $slikapromjena;
			$novaslika = str_replace("-promjena", "", $novaslika);
			$prefiks = "$conf_files_path/slike/";
			if (file_exists($prefiks.$staraslika))
				unlink($prefiks.$staraslika);
			if ($slikapromjena != "")
				rename($prefiks.$slikapromjena, $prefiks.$novaslika);
			$q117 = db_query("update osoba set slika='$novaslika' where id=$r100[0]");
		}
	}
	$q120 = db_query("delete from promjena_podataka where id=$id");
	zamgerlog("prihvacen zahtjev za promjenu podataka korisnika u$osoba", 4);
	zamgerlog2("prihvacen zahtjev za promjenu podataka", $osoba);
	print "Zahtjev je prihvaćen";
	
	if (db_get("SELECT COUNT(*) FROM izvoz_promjena_podataka WHERE student=$osoba") == 0)
		db_query("INSERT INTO izvoz_promjena_podataka VALUES($osoba)");

	// Poruka korisniku
	$tekst_poruke = "Na dan ".date("d. m. Y.", $vrijeme_zahtjeva).", u ".date("H:i:s", $vrijeme_zahtjeva)." poslali ste zahtjev za promjenu ličnih podataka. Vaš zahtjev je prihvaćen. Klikom na link Profil možete vidjeti vaše nove podatke.";
	if (strlen($_REQUEST['komentar'])>2)
		$tekst_poruke .= "\n\nPovodom Vašeg zahtjeva, Studentska služba vam je uputila sljedeći komentar:\n\t".$_REQUEST['komentar'];
	$q310 = db_query("insert into poruka set tip=2, opseg=7, primalac=$osoba, posiljalac=$userid, vrijeme=NOW(), ref=0, naslov='Vaš zahtjev za promjenu podataka je prihvaćen', tekst='$tekst_poruke'");

	return;
}

if (param('akcija') == "Odbij zahtjev" && check_csrf_token()) {
	$id = intval($_REQUEST['id']);
	$osoba = intval($_REQUEST['osoba']);

	$q195 = db_query("select UNIX_TIMESTAMP(vrijeme_zahtjeva), slika from promjena_podataka where id=$id");
	if (db_num_rows($q195)<1) {
		niceerror("Nepostojeci zahtjev sa IDom $id.");
		zamgerlog("nepostojeci zahtjev sa IDom $id.", 3);
		zamgerlog2("nepostojeci zahtjev", $id);
		return;
	}
	$vrijeme_zahtjeva=db_result($q195,0,0);
	$slikapromjena=db_result($q195,0,1);

	// Treba li obrisati viška sliku?
	$q197 = db_query("select slika from osoba where id=$osoba");
	if ($slikapromjena != "" && db_result($q197,0,0) != $slikapromjena)
		unlink ("$conf_files_path/slike/$slikapromjena");

	$q200 = db_query("delete from promjena_podataka where id=$id");
	zamgerlog("odbijen zahtjev za promjenu podataka korisnika u$osoba", 2);
	zamgerlog2("odbijen zahtjev za promjenu podataka", $osoba);
	print "Zahtjev je odbijen";

	// Poruka korisniku
	$tekst_poruke = "Na dan ".date("d. m. Y.", $vrijeme_zahtjeva).", u ".date("H:i:s", $vrijeme_zahtjeva)." poslali ste zahtjev za promjenu ličnih podataka. Vaš zahtjev je odbijen.";
	if (strlen($_REQUEST['komentar'])>2)
		$tekst_poruke .= "\n\nRazlog odbijanja zahtjeva je:\n\t".$_REQUEST['komentar'];
	$q310 = db_query("insert into poruka set tip=2, opseg=7, primalac=$osoba, posiljalac=$userid, vrijeme=NOW(), ref=0, naslov='Vaš zahtjev za promjenu podataka je odbijen!', tekst='$tekst_poruke'");

	return;
}


if (param('akcija') == "zahtjev") {

	$id = intval($_REQUEST['id']);
	$q100 = db_query("select pp.osoba, pp.ime, pp.prezime, pp.brindexa, UNIX_TIMESTAMP(pp.datum_rodjenja), pp.mjesto_rodjenja, pp.drzavljanstvo, pp.jmbg, pp.adresa, pp.adresa_mjesto, pp.telefon, pp.kanton, o.ime, o.prezime, o.brindexa, UNIX_TIMESTAMP(o.datum_rodjenja), o.mjesto_rodjenja, o.drzavljanstvo, o.jmbg, o.adresa, o.adresa_mjesto, o.telefon, o.kanton, pp.imeoca, o.imeoca, pp.prezimeoca, o.prezimeoca, pp.imemajke, o.imemajke, pp.prezimemajke, o.prezimemajke, pp.spol, o.spol, pp.nacionalnost, o.nacionalnost, pp.slika, o.slika, pp.boracke_kategorije, o.boracke_kategorije from promjena_podataka as pp, osoba as o where o.id=pp.osoba and pp.id=$id");
	if (db_num_rows($q100)<1) {
		niceerror("Nepoznat ID zahtjeva $id.");
		zamgerlog("nepoznat id zahtjeva za promjenu podataka $id", 3);
		zamgerlog2("nepoznat id zahtjeva za promjenu podataka", $id);
		return;
	}
	$osoba=db_result($q100,0,0);

	?>
	<p>Korisnik <b><?=db_result($q100,0,12)?> <?=db_result($q100,0,13)?></b> zatražio je sljedeće izmjene svojih ličnih podataka:
	<ul>
	<?
	promjena("ime", db_result($q100,0,1), db_result($q100,0,12));
	promjena("prezime", db_result($q100,0,2), db_result($q100,0,13));
	promjena("ime oca", db_result($q100,0,23), db_result($q100,0,24));
	promjena("prezime oca", db_result($q100,0,25), db_result($q100,0,26));
	promjena("ime majke", db_result($q100,0,27), db_result($q100,0,28));
	promjena("prezime majke", db_result($q100,0,29), db_result($q100,0,30));

	$starispol = db_result($q100,0,31); $novispol = db_result($q100,0,32);
	if ($starispol != $novispol) {
		if ($starispol=="M") $starispol="muški";
		if ($starispol=="Z") $starispol="ženski";
		if ($novispol=="M") $novispol="muški";
		if ($novispol=="Z") $novispol="ženski";
		promjena ("spol", $starispol, $novispol);
	}

	promjena("broj indexa", db_result($q100,0,3), db_result($q100,0,14));
	promjena("datum rođenja", date("d. m. Y.", db_result($q100,0,4)), date("d. m. Y.", db_result($q100,0,15)));

	// Mjesto rodjenja
	$staromj=db_result($q100,0,5); $novomj=db_result($q100,0,16);
	if ($staromj!=$novomj) {
		if ($staromj != 0) {
			$q101 = db_query("select naziv from mjesto where id=$staromj");
			$staromjn = db_result($q101,0,0);
		}
		if ($novomj != 0) {
			$q102 = db_query("select naziv from mjesto where id=$novomj");
			$novomjn = db_result($q102,0,0);
		}
		if ($staromjn == $novomjn) {
			$q101 = db_query("select o.naziv from mjesto as m, opcina as o where m.id=$staromj and m.opcina=o.id");
			$staromjn .= " (".db_result($q101,0,0).")";
			$q102 = db_query("select o.naziv from mjesto as m, opcina as o where m.id=$novomj and m.opcina=o.id");
			$novomjn .= " (".db_result($q102,0,0).")";
		}

		promjena("mjesto rođenja", $staromjn, $novomjn);
	}

	// Drzavljanstvo
	$starodrz=db_result($q100,0,6); $novodrz=db_result($q100,0,17);
	if ($starodrz!=$novodrz) {
		if ($starodrz != 0) {
			$q101 = db_query("select naziv from drzava where id=$starodrz");
			$starodrz = db_result($q101,0,0);
		}
		if ($novodrz != 0) {
			$q102 = db_query("select naziv from drzava where id=$novodrz");
			$novodrz = db_result($q102,0,0);
		}
		promjena("državljanstvo", $starodrz, $novodrz);
	}

	// Nacionalnost
	$staronac=db_result($q100,0,33); $novonac=db_result($q100,0,34);
	if ($staronac!=$novonac) {
		if ($staronac != 0) {
			$q101 = db_query("select naziv from nacionalnost where id=$staronac");
			$staronac = db_result($q101,0,0);
		}
		if ($novonac != 0) {
			$q102 = db_query("select naziv from nacionalnost where id=$novonac");
			$novonac = db_result($q102,0,0);
		}
		promjena("nacionalnost", $staronac, $novonac);
	}

	promjena("JMBG", db_result($q100,0,7), db_result($q100,0,18));

	// Adresa
	$staraadr = db_result($q100,0,8); $novaadr = db_result($q100,0,19);
	$said = db_result($q100,0,9); $naid = db_result($q100,0,20);
	if ($said != 0) {
		$q103 = db_query("select naziv from mjesto where id=$said");
		$staraadr .= ", ".db_result($q103,0,0);
	}
	if ($naid != 0) {
		$q103 = db_query("select naziv from mjesto where id=$naid");
		$novaadr .= ", ".db_result($q103,0,0);
	}
	promjena("adresa", $staraadr, $novaadr);

	promjena("telefon", db_result($q100,0,10), db_result($q100,0,21));

	$starikanton = db_result($q100,0,11); $novikanton = db_result($q100,0,22);
	if ($starikanton != $novikanton) {
		if ($starikanton != 0) {
			$q110 = db_query("select naziv from kanton where id=$starikanton");
			$starikanton = db_result($q110,0,0);
		}
		if ($novikanton != 0) {
			$q112 = db_query("select naziv from kanton where id=$novikanton");
			$novikanton = db_result($q112,0,0);
		}
		promjena("kanton", $starikanton, $novikanton);
	}

	promjena("boračke kategorije", db_result($q100,0,37), db_result($q100,0,38));

	?>
	</ul><p>&nbsp;</p>
	<?

	$staraslika = db_result($q100,0,36); $novaslika = db_result($q100,0,35);
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
	
	
	
	// -----------------------------------------
	//
	// POCETNA STRANICA
	//
	// -----------------------------------------
	
	
	
	
	// Dobrodošlica
	
	$q1 = db_query("select ime, spol from osoba where id=$userid");
	$ime = db_result($q1,0,0);
	$spol = db_result($q1,0,1);
	if ($spol == 'Z' || ($spol == '' && spol($ime)=="Z"))
		print "<h1>Dobro došla, ".vokativ($ime,"Z")."</h1>";
	else
		print "<h1>Dobro došao, ".vokativ($ime,"M")."</h1>";
	
	
	// Zahtjevi za promjenu ličnih podataka
	
	
	$q10 = db_query("select pp.id, pp.osoba, UNIX_TIMESTAMP(pp.vrijeme_zahtjeva), o.ime, o.prezime from promjena_podataka as pp, osoba as o where o.id=pp.osoba order by pp.vrijeme_zahtjeva");
	if (db_num_rows($q10)<1) {
	?>
		<p>Nema novih zahtjeva za promjenu ličnih podataka.</p>
	<?
	} else {
		?>
		<p><b>Zahtjevi za promjenu ličnih podataka:</b>
		<ul>
	<?
	}
	
	while ($r10 = db_fetch_row($q10)) {
		?>
		<li><a href="?sta=studentska/intro&akcija=zahtjev&id=<?=$r10[0]?>"><?=$r10[3]?> <?=$r10[4]?></a> (<?=date("d. m. Y. H:i", $r10[2])?>)</li>
		<?
	}
	
	if (db_num_rows($q10)>0) {
	?>
	</ul>
	Kliknite na zahtjev da biste ga prihvatili ili odbili.
	</p>
	
	<?
	
	}
	
	
	// Zahtjevi za dokumenta
	
	$q40 = db_query("SELECT count(*) FROM zahtjev_za_potvrdu WHERE status=1");
	$br_zahtjeva = db_result($q40, 0, 0);
	if ($br_zahtjeva > 0)
		print "<p><a href=\"?sta=studentska/potvrde&akcija=potvrda\">Imate $br_zahtjeva neobrađenih zahtjeva za dokumenta.</a></p>";
	else
		print "<p>Nema neobrađenih zahtjeva za dokumenta.</p>";
	

}

function dajplus($layerid,$layername) {
	return "<img src=\"static/images/plus.png\" width=\"13\" height=\"13\" id=\"img-$layerid\" onclick=\"daj_stablo('$layerid')\"> $layername <div id=\"$layerid\" style=\"display:none\">";
}

?>
