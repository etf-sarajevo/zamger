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

global $userid,$user_siteadmin,$user_studentska;


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
	$q100 = myquery("select pp.osoba, pp.ime, pp.prezime, pp.email, pp.brindexa, pp.datum_rodjenja, pp.mjesto_rodjenja, pp.drzavljanstvo, pp.jmbg, pp.adresa, pp.adresa_mjesto, pp.telefon, pp.kanton, UNIX_TIMESTAMP(pp.vrijeme_zahtjeva) from promjena_podataka as pp where pp.id=$id order by pp.vrijeme_zahtjeva");
	while ($r100 = mysql_fetch_row($q100)) {
		$q110 = myquery("update osoba set ime='$r100[1]', prezime='$r100[2]', email='$r100[3]', brindexa='$r100[4]', datum_rodjenja='$r100[5]', mjesto_rodjenja=$r100[6], drzavljanstvo='$r100[7]', jmbg='$r100[8]', adresa='$r100[9]', adresa_mjesto=$r100[10], telefon='$r100[11]', kanton=".intval($r100[12])." where id=".intval($r100[0]));
		$vrijeme_zahtjeva=$r100[13];
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

	$q195 = myquery("select UNIX_TIMESTAMP(vrijeme_zahtjeva) from promjena_podataka where id=$id");
	if (mysql_num_rows($q195)<1) {
		niceerror("Nepostojeci zahtjev sa IDom $id.");
		zamgerlog("Nepostojeci zahtjev sa IDom $id.", 3);
		return;
	}
	$vrijeme_zahtjeva=mysql_result($q195,0,0);

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
	$q100 = myquery("select pp.osoba, pp.ime, pp.prezime, pp.email, pp.brindexa, UNIX_TIMESTAMP(pp.datum_rodjenja), pp.mjesto_rodjenja, pp.drzavljanstvo, pp.jmbg, pp.adresa, pp.adresa_mjesto, pp.telefon, pp.kanton, o.ime, o.prezime, o.email, o.brindexa, UNIX_TIMESTAMP(o.datum_rodjenja), o.mjesto_rodjenja, o.drzavljanstvo, o.jmbg, o.adresa, o.adresa_mjesto, o.telefon, o.kanton from promjena_podataka as pp, osoba as o where o.id=pp.osoba and pp.id=$id");
	if (mysql_num_rows($q100)<1) {
		niceerror("Nepoznat ID zahtjeva $id.");
		zamgerlog("nepoznat id zahtjeva za promjenu podataka $id", 3);
		return;
	}


	?>
	<p>Korisnik <b><?=mysql_result($q100,0,13)?> <?=mysql_result($q100,0,14)?></b> zatražio je sljedeće izmjene svojih ličnih podataka:
	<ul>
	<?
	promjena("ime", mysql_result($q100,0,1), mysql_result($q100,0,13));
	promjena("prezime", mysql_result($q100,0,2), mysql_result($q100,0,14));
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

	promjena("državljanstvo", mysql_result($q100,0,7), mysql_result($q100,0,19));
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
	<?=genform("POST")?>
	<input type="hidden" name="osoba" value="<?=mysql_result($q100,0,0)?>">
	<input type="submit" name="akcija" value="Prihvati zahtjev">
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<input type="submit" name="akcija" value="Odbij zahtjev"><br><br>
	Eventualno upišite komentar koji želite poslati studentu:<br>
	<input type="text" size="50" name="komentar">
	</form>
	<?
	
	return;
}



// Dobrodošlica

$q1 = myquery("select ime from osoba where id=$userid");
$ime = mysql_result($q1,0,0);
if (spol($ime)=="Z") 
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



}

?>
