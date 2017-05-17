<?

// ADMIN/STUDIJ - parametri studija



function admin_studij() {


require_once("lib/formgen.php"); // db_form, db_grid

?>
<h2>Parametri studija</h2>

<p><a href="?sta=admin/studij&amp;akcija=ag">Akademska godina</a> * <a href="?sta=admin/studij&amp;akcija=inst">Institucija</a> * <a href="?sta=admin/studij&amp;akcija=kanton">Kanton</a> * <a href="?sta=admin/studij&amp;akcija=komponenta">Komponenta ocjene</a> * <a href="?sta=admin/studij&amp;akcija=naucni_stepen">Naučni stepen</a> * <a href="?sta=admin/studij&amp;akcija=strucni_stepen">Stručni stepen</a> * <a href="?sta=admin/studij&amp;akcija=studij">Studij</a> * <a href="?sta=admin/studij&amp;akcija=tipstudija">Tip studija</a> * <a href="?sta=admin/studij&amp;akcija=tippr">Tipovi predmeta</a></p>

<?

if (param('akcija')=="ag") {
	print db_grid("akademska_godina");
	print "<br /><hr><br />Dodaj:<br />".db_form("akademska_godina");

}

if (param('akcija')=="inst") {
	print db_grid("institucija");
	print "<br /><hr><br />Dodaj:<br />".db_form("institucija");
}

if (param('akcija')=="kanton") {
	print db_grid("kanton");
	print "<br /><hr><br />Dodaj:<br />".db_form("kanton");
}

if (param('akcija')=="komponenta") {
	print db_grid("komponenta");
	print "<br /><hr><br />Dodaj:<br />".db_form("komponenta");
}

if (param('akcija')=="naucni_stepen") {
	print db_grid("naucni_stepen");
	print "<br /><hr><br />Dodaj:<br />".db_form("naucni_stepen");
}

if (param('akcija')=="strucni_stepen") {
	print db_grid("strucni_stepen");
	print "<br /><hr><br />Dodaj:<br />".db_form("strucni_stepen");
}

if (param('akcija')=="studij") {
	print db_grid("studij");
	print "<br /><hr><br />Dodaj:<br />".db_form("studij");
}

if (param('akcija')=="tipstudija") {
	print db_grid("tipstudija");
	print "<br /><hr><br />Dodaj:<br />".db_form("tipstudija");
}

if (param('akcija')=="tippr") {
	//print db_grid("tippredmeta");
	// Ovo trebamo manuelno dok se u libvedran ne doda podrška za many-to-many
	// relacije

	// FIXME!! Ne radi!!
	?>
	<table border="0"><tr bgcolor="#bbbbbb">
		<td>Naziv</td><td>Komponente</td><td>&nbsp;</td>
	</tr>
	<?

	$q10 = db_query("select id,naziv from tippredmeta order by id");
	$bgcolor="";
	while ($r10 = db_fetch_row($q10)) {
		?>
		<tr <?=$bgcolor?>><input type="hidden" name="id" value="<?=$r10[0]?>">
			<td><input type="text" name="naziv" value="<?=$r10[1]?>"></td>
			<td><?
		if ($bgcolor=="") $bgcolor="bgcolor=\"#efefef\""; else $bgcolor="";
		$q20 = db_query("select k.id, k.naziv from komponenta as k, tippredmeta_komponenta as tpk where k.id=tpk.komponenta and tpk.tippredmeta=$r10[0]");
		while ($r20 = db_fetch_row($q20))
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
