<?

// IZVJESTAJ/PRIJEMNI_TOP10POSTO - deset posto najbolje plasiranih studenata na prijemnom ispitu

function izvjestaj_prijemni_top10posto() {


?>
<p>Univerzitet u Sarajevu<br />
Elektrotehnički fakultet Sarajevo</p>

<h3>Najboljih 10% kandidata na prijemnom ispitu po srednjoj školi</h3>
<?


$pt = intval($_REQUEST['termin']);

$skole = array();

$q10 = myquery("select count(*) from prijemni_prijava where prijemni_termin=$pt");

$desetposto = intval(mysql_result($q10,0,0)/10);

$q10 = myquery("select pp.rezultat, o.ime, o.prezime, uus.srednja_skola from prijemni_prijava as pp, osoba as o, uspjeh_u_srednjoj as uus where pp.prijemni_termin=$pt and pp.osoba=o.id and uus.osoba=o.id order by pp.rezultat desc limit $desetposto");

while ($r10 = mysql_fetch_row($q10)) {
	if (!$skole[$r10[3]]) $skole[$r10[3]]=array();
	array_push($skole[$r10[3]], "$r10[1] $r10[2] ($r10[0] bodova)");
}

foreach ($skole as $idskole => $skola) {
	$q20 = myquery("select naziv from srednja_skola where id=$idskole");
	print "<p><b>".mysql_result($q20,0,0)."</b><br>\n<ul>\n";
	foreach ($skola as $kandidat)
		print "<li>$kandidat</li>\n";
	print "</ul></p>\n";
}

}

?>
