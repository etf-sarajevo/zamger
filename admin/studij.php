<?

// ADMIN/STUDIJ - parametri studija



function admin_studij() {


require_once("lib/formgen.php"); // db_form, db_grid

?>
<h2>Parametri studija</h2>

<p><a href="?sta=admin/studij&amp;akcija=ag">Akademska godina</a> * <a href="?sta=admin/studij&amp;akcija=inst">Institucija</a> * <a href="?sta=admin/studij&amp;akcija=kanton">Kanton</a> * <a href="?sta=admin/studij&amp;akcija=komponenta">Komponenta ocjene</a> * <a href="?sta=admin/studij&amp;akcija=naucni_stepen">Naučni stepen</a> * <a href="?sta=admin/studij&amp;akcija=strucni_stepen">Stručni stepen</a> * <a href="?sta=admin/studij&amp;akcija=studij">Studij</a> * <a href="?sta=admin/studij&amp;akcija=tipstudija">Tip studija</a> * <a href="?sta=admin/studij&amp;akcija=tippr">Tipovi predmeta</a> * <a href="?sta=admin/studij&amp;akcija=sifarnici">Šifarnici</a></p>

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

if (param('akcija')=="tippr") {
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

if(param('akcija') == 'sifarnici'){
    // Možda ovaj dio izmijeniti, ili iskoristiti neki od već ponuđenih kodova, ali opet možemo i ovako
    if(isset($_POST['type'])){
        db_query("INSERT INTO sifarnici (type, name, value) VALUES ('{$_POST["type"]}', '{$_POST["name"]}', '{$_POST["value"]}')");
    }
    $q10 = db_query("select * from sifarnici order by id");
    ?>
        <table border="0" cellspacing="1" cellpadding="2">
            <thead>
            <tr bgcolor="#999999">
                <td style="padding:3px 20px;"><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">#</font></td>
                <td style="padding:3px 20px;"><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Vrijednost</font></td>
                <td style="padding:3px 20px;"><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Naziv</font></td>
                <td style="padding:3px 20px;"><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Tip</font></td>
            </tr>
            </thead>
            <tbody>
            <?php
                $counter = 1;
                while ($val = db_fetch_row($q10)){
                    ?>
                    <tr>
                        <td style="border: 1px solid rgba(0,0,0,0.1); padding:3px 20px;"><?= $counter++; ?>.</td>
                        <td style="border: 1px solid rgba(0,0,0,0.1); padding:3px 20px; text-align: center;"><?= $val[2]; ?></td>
                        <td style="border: 1px solid rgba(0,0,0,0.1); padding:3px 20px;"><?= $val[1]; ?></td>
                        <td style="border: 1px solid rgba(0,0,0,0.1); padding:3px 20px;"><?= $val[3]; ?></td>
                    </tr>
                    <?php
                }
            ?>
            </tbody>
        </table>

    <form method="post">
        <p>
            <select name="type" id="">
                <option value="0">Odaberite tip</option>
                <option value="status_studenta">Status studenta</option>
            </select>
            Vrijednost :
            <input type="number" name="value" placeholder="">
            Naziv :
            <input type="text" name="name" placeholder="Naziv ..">
            <input type="submit" value="Pošalji">
        </p>
    </form>
    <?php
}




}

?>
