<?

// IZVJESTAJ/SVI_STUDENTI - spisak svih studenata po nekim kriterijima i sa određenim kolonama



function izvjestaj_svi_studenti() {


?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>

<p>Datum i vrijeme izvještaja: <?=date("d. m. Y. H:i");?></p>
<?

$ime_oca = request('ime_oca');
$jmbg = request('jmbg');
$vanredni = request('vanredni');
$nacin_studiranja = request('nacin_studiranja');
$login = request('login');
$ag = intval(request('ag'));
$studij = intval(request('studij'));
$godina = intval(request('godina'));
$tabelarno = request('tabelarno');
$prvi_put = request('prvi_put');
$mjesto_rodjenja = request('mjesto_rodjenja');
$adresa_mjesto = request('adresa_mjesto');

if ($ag==0) {
	$q10 = db_query("select id, naziv from akademska_godina where aktuelna=1");
	$ag = db_result($q10,0,0);
	$ak_god_naziv = db_result($q10,0,1);
} else {
	$q20 = db_query("select naziv from akademska_godina where id=$ag");
	$ak_god_naziv = db_result($q20,0,0);
}

if ($studij == 0)
	$naziv_studija = "Svi studiji";
else if ($studij == -1)
	$naziv_studija = "Prvi ciklus studija";
else if ($studij == -2)
	$naziv_studija = "Drugi ciklus studija";
else {
	$q30 = db_query("SELECT naziv FROM studij WHERE id=$studij");
	$naziv_studija = db_result($q30,0,0);
}

if ($godina>0)
	$naziv_studija .= ", $godina. godina";

?>
<h2>Spisak svih studenata abecedno</h2>
<h3>Za akademsku <?=$ak_god_naziv?>. godinu, <?=$naziv_studija?></h3>

<p>
<?

$kolone = "";
if ($ime_oca) $kolone .= ", o.imeoca";
if ($jmbg) $kolone .= ", o.jmbg";
if ($nacin_studiranja) $kolone .= ", ns.naziv as nacin";
if ($login) $kolone .= ", a.login";
if ($mjesto_rodjenja) $kolone .= ", m.naziv as mjestorodj";
if ($adresa_mjesto) $kolone .= ", am.naziv as adresamjesto";

$tabele = "";
if ($nacin_studiranja) $tabele .= ", nacin_studiranja as ns";
if ($studij < 0) $tabele .= ", studij as s, tipstudija as ts";
if ($login) $tabele .= ", auth as a";
if ($mjesto_rodjenja) $tabele .= ", mjesto as m";
if ($adresa_mjesto) $tabele .= ", mjesto as am";

$uslovi = "";
if (!$vanredni) $uslovi .= " and ss.nacin_studiranja != 4";
if ($nacin_studiranja) $uslovi .= " and ss.nacin_studiranja=ns.id";
if ($studij > 0) 
	$uslovi .= " and ss.studij=$studij";
else if ($studij < 0) 
	$uslovi .= " and ss.studij=s.id and s.tipstudija=ts.id and ts.ciklus=".(-$studij);
if ($prvi_put) $uslovi .= " and ss.ponovac=0";
if ($login) $uslovi .= " and o.id=a.id";
if ($mjesto_rodjenja) $uslovi .= " and o.mjesto_rodjenja=m.id";
if ($adresa_mjesto) {
	$uslovi .= " and o.adresa_mjesto=am.id";
	if ($adresa_mjesto != "on") $uslovi .= " and am.naziv='$adresa_mjesto'";
}

$redoslijed = "";
if ($nacin_studiranja) $redoslijed .= "ss.nacin_studiranja, ";

$uslov_semestar = " and ss.semestar mod 2 = 1"; // Bilo koji neparan semestar
if ($godina > 0)
	$uslov_semestar = " and ss.semestar=".($godina*2-1);

$q30 = db_query("SELECT o.id, o.ime, o.prezime $kolone , o.kanton FROM osoba as o, student_studij as ss $tabele WHERE ss.student=o.id and ss.akademska_godina=$ag $uslov_semestar $uslovi order by $redoslijed o.prezime, o.ime");
$rbr = 1; $oldid = 0;
$niz = array();

if ($tabelarno) {
	print "<table border=\"1\" cellspacing=\"0\" cellpadding=\"3\" style=\"border: 1px; border-collapse: collapse\"><tr><th>R. br.</th><th>Prezime</th>";
	if ($ime_oca) print "<th>Ime roditelja</th>";
	print "<th>Ime</th>";
	if ($jmbg) print "<th>JMBG</th>";
	if ($nacin_studiranja) print "<th>Način studiranja</th>";
	if ($login) print "<th>Login</th>";
	if ($mjesto_rodjenja) print "<th>Mjesto rođenja</th>";
	if ($adresa_mjesto) print "<th>Adresa mjesto</th>";
	print "<th>&nbsp;</th>";
	print "</tr>\n";
}

while ($osoba = db_fetch_assoc($q30)) {
	if ($tabelarno) {
		print "<tr><td>$rbr</td><td>".$osoba['prezime']."</td>";
		if ($ime_oca) print "<td>".$osoba['imeoca']."</td>";
		print "<td>".$osoba['ime']."</td>";
		if ($jmbg) print "<td>".$osoba['jmbg']."</td>";
		if ($nacin_studiranja) print "<td>".$osoba['nacin']."</td>";
		if ($login) print "<td>".$osoba['login']."</td>";
		if ($mjesto_rodjenja) print "<td>".$osoba['mjestorodj']."</td>";
		if ($adresa_mjesto) print "<td>".$osoba['adresamjesto']."</td>";

		// Greške
		if ($ime_oca && $osoba['imeoca'] == "") print "<td><font color=\"red\">- nepoznato ime oca!</font></td>";
		else if ($osoba['id'] == $oldid) print "<td><font color=\"red\">- ponavlja se!</font></td>";
		else print "<td>&nbsp;</td>";
		$oldid = $osoba['id'];

		print "</tr>\n";
	} else {
		// Kolone
		print "$rbr. ".$osoba['prezime']." ";
		if ($ime_oca) print "(".$osoba['imeoca'].") ";
		print $osoba['ime']." ";
		if ($jmbg) print " (".$osoba['jmbg'].") ";
		if ($nacin_studiranja) print " - ".$osoba['nacin']." ";
		if ($login) print " - ".$osoba['login']." ";
		if ($mjesto_rodjenja) print "(".$osoba['mjestorodj'].")";
		if ($adresa_mjesto) print "(".$osoba['adresamjesto'].")";

		// Greške
		if ($ime_oca && $osoba['imeoca'] == "") print " <font color=\"red\">- nepoznato ime oca!</font>";
		if ($osoba['id'] == $oldid) print " <font color=\"red\">- ponavlja se!</font>";
		$oldid = $osoba['id'];

		print "<br>";
	}
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
