<?

// IZVJESTAJ/ZAVRSNI_PO_CLANU_KOMISIJE - Koliko je koji nastavnik imao članstava u komisijama za završne radove


function izvjestaj_zavrsni_po_clanu_komisije() {

?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>
<?


$predmet = intval($_REQUEST['predmet']);
$ag = intval($_REQUEST['ag']);

$q10 = myquery("SELECT naziv FROM akademska_godina WHERE id=$ag");
if (mysql_num_rows($q10) != 1) {
	biguglyerror("Neispravna akademska godina");
	return;
}
$ag_naziv = mysql_result($q10,0,0);

$q20 = myquery("SELECT i.naziv FROM predmet as p, institucija as i WHERE p.id=$predmet AND p.institucija=i.id");
if (mysql_num_rows($q20) != 1) {
	biguglyerror("Neispravan predmet");
	return;
}
$odsjek = mysql_result($q20,0,0);

$q30 = myquery("SELECT ts.ciklus FROM tipstudija as ts, studij as s, ponudakursa as pk WHERE pk.predmet=$predmet AND pk.akademska_godina=$ag AND pk.studij=s.id AND s.tipstudija=ts.id");
if (mysql_num_rows($q30) != 1) {
	biguglyerror("Nije definisana ponuda kursa");
	return;
}
$ciklus = mysql_result($q30,0,0);


?>
<h2>Učešće nastavnika u komisijama za završne radove</h2>
<p>Akademska <?=$ag_naziv?> godina, studij <?=$odsjek?>, <?=$ciklus?>. ciklus studija</p>

<?

$q100 = myquery("SELECT z.naslov, z.mentor, z.predsjednik_komisije, z.clan_komisije, o.id, z.student
FROM zavrsni as z, osoba as o 
WHERE z.predmet=$predmet AND z.akademska_godina=$ag AND z.predsjednik_komisije>0 AND z.clan_komisije>0 AND (z.mentor=o.id OR z.predsjednik_komisije=o.id OR z.clan_komisije=o.id)
ORDER BY o.prezime, o.ime, z.naslov");

$oldosoba = $rbr = 0;
while ($r100 = mysql_fetch_row($q100)) {
	$naslov = $r100[0];
	$osoba_id = $r100[4];
	$id_studenta = $r100[5];
	
	$q110 = myquery("SELECT COUNT(*) FROM konacna_ocjena WHERE student=$id_studenta AND predmet=$predmet AND ocjena>5");
	if (mysql_result($q110,0,0) == 0) continue;
	
	if ($osoba_id != $oldosoba) {
		?>
		</p>
		<p><b>Nastavnik:</b> <?=tituliraj($osoba_id)?><br>
		1. <?=$naslov?>
		<?
		if ($osoba_id == $r100[1])
			print " - mentor";
		$rbr = 2;
		$oldosoba = $osoba_id;
	} else {
		?>
		<br>
		<?=$rbr++?>. <?=$naslov?>
		<?
	}
}

if ($oldosoba == 0) {
	?>
	</p><p>Nije definisana nijedna tema.</p>
	<?
}


}
