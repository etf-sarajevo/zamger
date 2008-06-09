<?

// ADMIN/INTRO - pocetna stranica za admina - stablo predmeta

// v3.9.1.0 (2008/02/26) + Novi modul, admin/intro



function admin_intro() {
?>
<p>&nbsp;</p>
<h3>Administracija sajta</h3>
<p>Izaberite predmet koji želite administrirati:</p>
<?
	require("public/predmeti.php");
	public_predmeti("nastavnik/predmet&c=N");
}

?>