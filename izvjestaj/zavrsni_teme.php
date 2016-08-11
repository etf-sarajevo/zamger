<?

// IZVJESTAJ/ZAVRSNI_TEME - Prijedlog tema za završne radove po mentoru



function izvjestaj_zavrsni_teme() {

?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>
<p>Broj:<br>
Datum:</p>

<center>NASTAVNONAUČNOM VIJEĆU<br>
ELEKTROTEHNIČKOG FAKULTETA U SARAJEVU</center>
<?


$predmet = intval($_REQUEST['predmet']);
$ag = intval($_REQUEST['ag']);

$q10 = db_query("SELECT naziv FROM akademska_godina WHERE id=$ag");
if (db_num_rows($q10) != 1) {
	biguglyerror("Neispravna akademska godina");
	return;
}
$ag_naziv = db_result($q10,0,0);

$q20 = db_query("SELECT i.naziv FROM predmet as p, institucija as i WHERE p.id=$predmet AND p.institucija=i.id");
if (db_num_rows($q20) != 1) {
	biguglyerror("Neispravan predmet");
	return;
}
$odsjek = db_result($q20,0,0);

$q30 = db_query("SELECT ts.ciklus FROM tipstudija as ts, studij as s, ponudakursa as pk WHERE pk.predmet=$predmet AND pk.akademska_godina=$ag AND pk.studij=s.id AND s.tipstudija=ts.id");
if (db_num_rows($q30) != 1) {
	biguglyerror("Nije definisana ponuda kursa");
	return;
}
$ciklus = db_result($q30,0,0);


?>
<p>Vijeće <?=$odsjek?>, na sastanku održanom ____________, predlaže Nastavnonaučnom vijeću teme za završne radove <?=$ciklus?>. ciklusa za <?=$ag_naziv?> studijsku godinu.</p>

<p>&nbsp;
<?

$q100 = db_query("SELECT z.naslov, z.mentor FROM zavrsni as z, osoba as o WHERE z.predmet=$predmet AND z.akademska_godina=$ag AND z.mentor=o.id ORDER BY o.prezime, o.ime, z.naslov");

$oldmentor = $rbr = 0;
while ($r100 = db_fetch_row($q100)) {
	$naslov = $r100[0];
	$mentor_id = $r100[1];
	
	if ($mentor_id != $oldmentor) {
		?>
		</p>
		<p><b>Predmetni nastavnik:</b> <?=tituliraj($mentor_id)?><br>
		1. <?=$naslov?>
		<?
		$rbr = 2;
		$oldmentor = $mentor_id;
	} else {
		?>
		<br>
		<?=$rbr++?>. <?=$naslov?>
		<?
	}
}

if ($oldmentor == 0) {
	?>
	</p><p>Nije definisana nijedna tema.</p>
	<?
}

?>

<table border="0" width="100%">
<tr>
	<td width="60%">&nbsp;</td>
	<td width="40%" align="center"><p>ŠEF <?=strtoupper($odsjek)?><br /><br /><br />&nbsp;</p></td>
</tr>
</table>

<?


}
