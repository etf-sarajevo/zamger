<?

// ADMIN/INTRO - pocetna stranica za admina - stablo predmeta

// v3.9.1.0 (2008/02/26) + Novi modul, admin/intro
// v3.9.1.1 (2008/09/01) + Dodan pogled "Svi studenti"



function admin_intro() {
?>
<p>&nbsp;</p>
<h3>Administracija sajta</h3>
<p>Izaberite predmet koji želite administrirati:</p>
<?
	require("public/predmeti.php");

if ($_REQUEST['grupe']) {
	public_predmeti("saradnik/grupa");
	?><p><a href="?sta=admin/intro">Pogled "Administracija predmeta"</a></p><?
} else {
	public_predmeti("nastavnik/predmet");
	?><p><a href="?sta=admin/intro&grupe=1">Pogled "Svi studenti"</a></p><?
}

}

?>