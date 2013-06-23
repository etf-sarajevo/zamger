<?

// IZVJESTAJ/SVI_STUDENTI - spisak svih studenata

function izvjestaj_svi_studenti(){


?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>

<p>Datum i vrijeme izvještaja: <?=date("d. m. Y. H:i");?></p>
<?

$ime_oca = request('ime_oca');
$jmbg = request('jmbg');
$vanredni = request('vanredni');
$ag = intval(request('ag'));
$studij = intval(request('studij'));

if ($ag==0) {
	$q10 = myquery("select id, naziv from akademska_godina where aktuelna=1");
	$ag = mysql_result($q10,0,0);
	$ak_god_naziv = mysql_result($q10,0,1);
} else {
	$q20 = myquery("select naziv from akademska_godina where id=$ag");
	$ak_god_naziv = mysql_result($q20,0,0);
}

?>
<h2>Spisak svih studenata abecedno</h2>
<h3>Za akademsku <?=$ak_god_naziv?>. godinu</h3>

<p>
<?

if ($ime_oca) $jos .= ", o.imeoca";
if ($jmbg) $jos .= ", o.jmbg";
if (!$vanredni) $jvanredni = " and ss.nacin_studiranja != 4";

$dodaj_studij = "";
if ($studij != -1) $dodaj_studij = "and ss.studij=$studij";

$q30 = myquery("SELECT o.id, o.ime, o.prezime $jos , o.kanton FROM osoba as o, student_studij as ss WHERE ss.student=o.id and ss.akademska_godina=$ag and ss.semestar mod 2 = 1 $jvanredni $dodaj_studij order by o.prezime, o.ime");
$rbr = 1; $oldid = 0;
$niz = array();

while ($r30 = mysql_fetch_row($q30)) {
	print "$rbr. $r30[2] ";
	if ($ime_oca) print "($r30[3]) ";
	print "$r30[1] ";
	if ($jmbg && $ime_oca) print " ($r30[4])";
	else if ($jmbg) print " ($r30[3])";
	if ($ime_oca && $r30[3] == "") print " <font color=\"red\">- nepoznato ime oca!</font>";

	if ($r30[0] == $oldid) print " <font color=\"red\">- ponavlja se!</font>";
	$oldid = $r30[0];

	print "<br>";
	$rbr++;
}

print "</p>";

}

function request($var) {
	if (isset($_REQUEST[$var]))
		return $_REQUEST[$var];
	return false;
}
?>
