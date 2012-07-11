<?

// ADMIN/STUDIJ - parametri studija

// v3.9.1.0 (2008/06/13) + Novi modul, admin/studij



function admin_studij() {


?>
<h2>Parametri studija</h2>

<p><a href="?sta=admin/studij&akcija=ag">Akademska godina</a> * <a href="?sta=admin/studij&akcija=inst">Institucija</a> * <a href="?sta=admin/studij&akcija=kanton">Kanton</a> * <a href="?sta=admin/studij&akcija=studij">Studij</a></p>

<?

if ($_REQUEST['akcija']=="ag") {
	print db_grid("akademska_godina");
	print "<br /><hr><br />Dodaj:<br />".db_form("akademska_godina");

}

if ($_REQUEST['akcija']=="inst") {
	print db_grid("institucija");
	print "<br /><hr><br />Dodaj:<br />".db_form("institucija");
}

if ($_REQUEST['akcija']=="kanton") {
	print db_grid("kanton");
	print "<br /><hr><br />Dodaj:<br />".db_form("kanton");
}

if ($_REQUEST['akcija']=="studij") {
	print db_grid("studij");
	print "<br /><hr><br />Dodaj:<br />".db_form("studij");
}



}

?>
