<?


// IZVJESTAJ/SPISAK_PREDMETA - Spisak predmeta na kojima se nastava izvodi u tekućoj akademskoj godini

function izvjestaj_spisak_predmeta() {

$ag = int_param('ag');
if ($ag == 0)
	$ag = db_get("SELECT id FROM akademska_godina WHERE aktuelna=1");

if (param('format') == "csv")
	print "ID,naziv,sifra\n";
else {
	?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>
<p>Datum i vrijeme izvještaja: <?=date("d. m. Y. H:i");?></p>

<h1>Spisak predmeta</h1>

	<table>
	<thead>
	<tr><th>ID predmeta</th><th>Naziv</th><th>Šifra</th></tr>
	</thead>
	<tbody>
	<? 
}

$q10 = db_query("SELECT COUNT(sp.student), p.id, p.naziv, p.sifra, agp.pasos_predmeta FROM predmet p, ponudakursa pk, student_predmet sp, akademska_godina_predmet agp WHERE sp.predmet=pk.id AND pk.akademska_godina=$ag AND pk.predmet=p.id AND agp.predmet=p.id AND agp.akademska_godina=$ag GROUP BY p.id, p.naziv ORDER BY p.naziv");
while (db_fetch5($q10, $broj_studenata, $predmet_id, $naziv, $sifra, $pasos)) {
	if ($pasos) {
		$q20 = db_query("SELECT naziv, sifra FROM pasos_predmeta WHERE id=$pasos");
		db_fetch2($q20, $naziv, $sifra);
	}
	if (param('format') == "csv")
		print "$predmet_id,$naziv,$sifra\n";
	else {
		?>
		<tr><td><?=$predmet_id?></td><td><?=$naziv?></td><td><?=$sifra?></td></tr>
		<?
	}
}

if (param('format') != "csv") {
	?>
	</tbody>
	</table>
	<?
}

}
