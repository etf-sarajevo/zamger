<?

// ADMIN/STUDIJ - parametri studija

// v3.9.1.0 (2008/06/13) + Novi modul, admin/studij



function admin_studij() {


?>
<h2>Parametri studija</h2>

<p><a href="?sta=admin/studij&akcija=ag">Akademska godina</a> * <a href="?sta=admin/studij&akcija=inst">Institucija</a> * <a href="?sta=admin/studij&akcija=kanton">Kanton</a> * <a href="?sta=admin/studij&akcija=komponenta">Komponenta ocjene</a> * <a href="?sta=admin/studij&akcija=studij">Studij</a> * <a href="?sta=admin/studij&akcija=tippr">Tipovi predmeta</a></p>

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

if ($_REQUEST['akcija']=="komponenta") {
	print db_grid("komponenta");
	print "<br /><hr><br />Dodaj:<br />".db_form("komponenta");
}

if ($_REQUEST['akcija']=="studij") {
	print db_grid("studij");
	print "<br /><hr><br />Dodaj:<br />".db_form("studij");
}

if ($_REQUEST['akcija']=="tippr") {
	//print db_grid("tippredmeta");
	// Ovo trebamo manuelno dok se u libvedran ne doda podrška za many-to-many
	// relacije

	// FIXME!! Ne radi!!
	?>
	<table border="0"><tr bgcolor="#bbbbbb">
		<td>Naziv</td><td>Komponente</td><td>&nbsp;</td>
	</tr>
	<?

	$q10 = myquery("select id,naziv from tippredmeta order by id");
	$bgcolor="";
	while ($r10 = mysql_fetch_row($q10)) {
		?>
		<tr <?=$bgcolor?>><input type="hidden" name="id" value="<?=$r10[0]?>">
			<td><input type="text" name="naziv" value="<?=$r10[1]?>"></td>
			<td><?
		if ($bgcolor=="") $bgcolor="bgcolor=\"#efefef\""; else $bgcolor="";
		$q20 = myquery("select k.id, k.naziv from komponenta as k, tippredmeta_komponenta as tpk where k.id=tpk.komponenta and tpk.tippredmeta=$r10[0]");
		while ($r20 = mysql_fetch_row($q20))
			print $r20[1]." (<a href=\"\">izbaci</a>)<br />";
		?>(<a href="">dodaj</a>)</td>
			<td><input type="submit" name="izmijeni" value=" Izmijeni ">&nbsp;
			<input type="submit" name="obrisi" value=" Obriši "></td>
		</tr>
		<?
	}
	print "</table>\n";

	print "<br /><hr><br />Dodaj:<br />".db_form("tippredmeta");
}





}

?>
