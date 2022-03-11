<?

// ADMIN/MISC - sta god mi padne na pamet da kodiram


function admin_misc() {
	
	require_once("lib/formgen.php"); // db_dropdown
	require_once("lib/student_predmet.php"); // update_komponente, upisi_na_predmet, ispisi_sa_predmeta
	require_once("lib/student_studij.php"); // ima_li_uslov
	
	
	?>
	<p>&nbsp;</p>
	<h3>Ostalo</h3>
	<p>Opšti administrativni i maintenance moduli koji se dodaju van registry-ja (ali su dostupni samo adminu):</p>
	<?
	
	$misc_modules = [
		"broken_integral" => "Neispravan integralni ispit",
		"upis_linkovi" => "Spisak sa linkovima na studentska/osobe radi lakšeg upisa",
		"upis_prva" => "Upis brucoša u predmete na prvoj godini",
		"upis_vise" => "Retroaktivni upis u predmete na višim godinama",
		"mass_jmbg" => "Masovni unos jmbg",
		"mass_index" => "Masovni unos broja indexa",
		"logini" => "Kreiraj logine svim studentima prve godine",
		"grupe" => "Masovno kreiranje grupa na prvoj godini",
		"grupe_vise" => "Kreiranje grupa na višim godinama",
		"zamjena_grupa" => "Zamijeni grupe dva studenta na svim predmetima",
		"anketa_tokeni" => "Masovno kreiranje tokena za ankete",
		"update_komponenti" => "Masovni update komponenti",
		"brisanje_osobe" => "Sigurno brisanje osobe",
		"spajanje_osoba" => "Spajanje dvije osobe",
		"zamijeni_pk" => "Zamijeni ponudu kursa",
		"import_raspored" => "Import rasporeda iz Ribićeve aplikacije",
		"mass_zavrsni" => "Upiši sve studente 5. semestra BSc / 3. semestra MSc na predmet Završni rad",
		"upisi_parni" => "Upiši sve studente u parni semestar",
		"promijeni_kodove" => "Ažuriraj kodove za izvještaje",
		"import_eunsa" => "Import studenata iz eUNSA aplikacije",
	];
	
	if (param('module') && array_key_exists(param('module'), $misc_modules)) {
		print "<h3>" . $misc_modules[param('module')] . "</h3>\n";
		require_once("admin/misc/" . param('module') . ".php");
		eval("admin_misc_" . param('module') . "();");
		return;
	}
	
	print "<ul>\n";
	foreach($misc_modules as $module => $name) {
		print "<li><a href='?sta=admin/misc&module=".$module."'>$name</a></li>\n";
	}
	

}

?>
