<?

// ADMIN/INTRO - pocetna stranica za admina - stablo predmeta



function admin_intro() {
?>
<p>&nbsp;</p>
<h3>Administracija sajta</h3>
<p>Izaberite predmet koji želite administrirati:</p>
<?
	require("public/predmeti.php");

if (param('grupe')) {
	public_predmeti("saradnik/grupa");
	?><p><a href="?sta=admin/intro">Pogled "Administracija predmeta"</a></p><?
} else {
	public_predmeti("nastavnik/predmet");
	?><p><a href="?sta=admin/intro&grupe=1">Pogled "Svi studenti"</a></p><?
}

}

?>