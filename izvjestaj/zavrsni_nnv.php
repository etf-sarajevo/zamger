<?

// IZVJESTAJ/ZAVRSNI_NNV - Spisak tema sa kandidatima i komisijama



function izvjestaj_zavrsni_nnv() {

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
<p>Vijeće <?=$odsjek?>, na sastanku održanom ____________, predlaže Nastavnonaučnom vijeću teme, kandidate i komisije za završne radove <?=$ciklus?>. ciklusa za <?=$ag_naziv?> studijsku godinu.</p>

<style>
.float {
	width: 100px;
	font-weight: bold;
	float: left;
}
</style>

<p>&nbsp;
<?

$q100 = db_query("SELECT z.naslov, o.ime, o.prezime, z.predsjednik_komisije, z.mentor, z.clan_komisije, z.predmet, z.student FROM zavrsni as z, osoba as o WHERE z.predmet=$predmet AND z.akademska_godina=$ag AND z.student=o.id ORDER BY o.prezime, o.ime, z.naslov");

while ($r100 = db_fetch_row($q100)) {
	$naslov = $r100[0];
	$kandidat_ime = $r100[1];
	$kandidat_prezime = $r100[2];
	$predsjednik = $r100[3];
	$mentor_id = $r100[4];
	$clan_komisije = $r100[5];
	$predmet = $r100[6];
	$id_studenta = $r100[7];

	if (!isset($_REQUEST['svi'])) {
		$q110 = db_query("SELECT COUNT(*) FROM konacna_ocjena WHERE student=$id_studenta AND predmet=$predmet AND akademska_godina=$ag AND ocjena>5");
		if (db_result($q110,0,0) == 0) continue;
	}
	
	?>
	<p><span class="float">Tema:</span> <?=$naslov?><br>
	<span class="float">Kandidat:</span> <?=$r100[1]?> <?=$r100[2]?><br>
	<span class="float">Komisija:</span> <?=tituliraj($predsjednik)?> - Predsjednik<br>
	<span class="float">&nbsp;</span> <?=tituliraj($mentor_id)?> - Mentor/Član<br>
	<span class="float">&nbsp;</span> <?=tituliraj($clan_komisije)?> - Član</p>
	<?
}

if (db_num_rows($q100) == 0) {
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
