<?


// IZVJESTAJ/LOGINI - Nastavnici i studenti na predmetima - sa loginima i IDovima

function izvjestaj_logini() {

$ag = int_param('ag');
if ($ag == 0)
	$ag = db_get("SELECT id FROM akademska_godina WHERE aktuelna=1");

if (param('tip') == "nastavnici")
	$q10 = db_query("SELECT nastavnik, predmet FROM nastavnik_predmet WHERE akademska_godina=$ag AND nivo_pristupa='nastavnik'");
else
	$q10 = db_query("SELECT sp.student, pk.predmet FROM student_predmet sp, ponudakursa pk WHERE sp.predmet=pk.id AND pk.akademska_godina=$ag");

if (param('format') == "csv")
	print "login,predmet\n";
else {
	?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>
<p>Datum i vrijeme izvještaja: <?=date("d. m. Y. H:i");?></p>

<h1>Spisak <? if (param('tip') == "nastavnici") print "nastavnika"; else print "studenata"; ?> na predmetima</h1>

	<table>
	<thead>
	<tr><th>Login</th><th>ID predmeta</th></tr>
	</thead>
	<tbody>
	<? 
}


while (db_fetch2($q10, $osoba, $predmet)) {
	if ($predmet >= 225 && $predmet <= 232) continue;
	$login = db_get("SELECT login FROM auth WHERE id=$osoba LIMIT 1");
	if ($login == "vedran") $login = "vljubovic";
	if (!$login) $login = $osoba;
	if (param('format') == "csv")
		print "$login,$predmet\n";
	else {
		?>
		<tr><td><?=$login?></td><td><?=$predmet?></td></tr>
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
