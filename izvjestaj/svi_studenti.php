<?

// IZVJESTAJ/SVI_STUDENTI - spisak svih studenata po nekim kriterijima i sa određenim kolonama



function izvjestaj_svi_studenti() {


?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>

<p>Datum i vrijeme izvještaja: <?=date("d. m. Y. H:i");?></p>
<?

$ime_oca = param('ime_oca');
$spol = param('spol');
$jmbg = param('jmbg');
$vanredni = param('vanredni');
$nacin_studiranja = param('nacin_studiranja');
$login = param('login');
$brindexa = param('brindexa');
$ag = int_param('ag');
$tipstudija = int_param('tipstudija');
$studij = int_param('studij');
$godina = int_param('godina');
$tabelarno = param('tabelarno');
$ponovci = int_param('ponovci');
$mjesto_rodjenja = param('mjesto_rodjenja');
$adresa_mjesto = param('adresa_mjesto');
$drzavljanstvo = param('drzavljanstvo');
$boracke = int_param('boracke');
$zaduzenje = int_param('zaduzenje');

if ($ag==0) {
	$q10 = db_query("select id, naziv from akademska_godina where aktuelna=1");
	db_fetch2($q10, $ag, $ak_god_naziv);
} else {
	$ak_god_naziv = db_get("select naziv from akademska_godina where id=$ag");
}

if ($studij == 0) {
	if ($tipstudija == 0)
		$naziv_studija = "Svi studiji";
	else
		$naziv_studija = db_get("SELECT naziv FROM tipstudija WHERE id=$tipstudija");
} else {
	$naziv_studija = db_get("SELECT naziv FROM studij WHERE id=$studij");
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
if ($spol) $kolone .= ", o.spol";
if ($jmbg) $kolone .= ", o.jmbg";
if ($nacin_studiranja) $kolone .= ", ns.naziv as nacin";
if ($login) $kolone .= ", a.login";
if ($brindexa) $kolone .= ", o.brindexa";
if ($mjesto_rodjenja) $kolone .= ", m.naziv as mjestorodj";
if ($adresa_mjesto) $kolone .= ", am.naziv as adresamjesto";
if ($drzavljanstvo) $kolone .= ", d.naziv as drzavljanstvo";
if ($zaduzenje) $kolone .= ", ss.zaduzenje";

$tabele = "";
if ($nacin_studiranja) $tabele .= ", nacin_studiranja as ns";
if ($studij == 0 && $tipstudija > 0) $tabele .= ", studij as s";
if ($login) $tabele .= ", auth as a";
if ($mjesto_rodjenja) $tabele .= ", mjesto as m";
if ($adresa_mjesto) $tabele .= ", mjesto as am";
if ($drzavljanstvo) $tabele .= ", drzava as d";
if ($boracke) $tabele .= ", osoba_posebne_kategorije opk";

$uslovi = "";
if (!$vanredni) $uslovi .= " and ss.nacin_studiranja != 4";
if ($nacin_studiranja) $uslovi .= " and ss.nacin_studiranja=ns.id";
if ($studij > 0) 
	$uslovi .= " and ss.studij=$studij";
else if ($studij == 0 && $tipstudija > 0) 
	$uslovi .= " and ss.studij=s.id and s.tipstudija=$tipstudija";
if ($ponovci == 1) $uslovi .= " and ss.ponovac=0";
if ($ponovci == 2) $uslovi .= " and ss.ponovac=1";
if ($ponovci == 3) $uslovi .= " and ss.status_studenta=1";
if ($login) $uslovi .= " and o.id=a.id";
if ($mjesto_rodjenja) $uslovi .= " and o.mjesto_rodjenja=m.id";
if ($adresa_mjesto) {
	$uslovi .= " and o.adresa_mjesto=am.id";
	if ($adresa_mjesto != "on") $uslovi .= " and am.naziv='$adresa_mjesto'";
}
if ($drzavljanstvo) $uslovi .= " and o.drzavljanstvo=d.id";
if ($boracke) $uslovi .= " and o.id=opk.osoba AND opk.posebne_kategorije != 3"; // studenti ne ostvaruju nikakva prava po osnovu pripadnosti kategoriji 3 "djeca demobilisanih boraca"

$redoslijed = "";
if ($nacin_studiranja) $redoslijed .= "ss.nacin_studiranja, ";

$uslov_semestar = " and ss.semestar mod 2 = 1"; // Bilo koji neparan semestar
if ($ponovci == 3) $uslov_semestar = " and ss.semestar mod 2 = 0"; // HACK u 2019/2020 godini apsolventi su samo u parni semestar upisani
if ($godina > 0)
	$uslov_semestar = " and ss.semestar=".($godina*2-1);

$q30 = db_query("SELECT o.id, o.ime, o.prezime $kolone , o.kanton FROM osoba as o, student_studij as ss $tabele WHERE ss.student=o.id and ss.akademska_godina=$ag $uslov_semestar $uslovi order by $redoslijed o.prezime, o.ime");
$rbr = 1; $oldid = 0;
$niz = array();

if ($tabelarno) {
	print "<table border=\"1\" cellspacing=\"0\" cellpadding=\"3\" style=\"border: 1px; border-collapse: collapse\"><tr><th>R. br.</th><th>Prezime</th>";
	if ($ime_oca) print "<th>Ime roditelja</th>";
	print "<th>Ime</th>";
	if ($spol) print "<th>Spol</th>";
	if ($jmbg) print "<th>JMBG</th>";
	if ($nacin_studiranja) print "<th>Način studiranja</th>";
	if ($login) print "<th>Login</th>";
	if ($brindexa) print "<th>Broj indeksa</th>";
	if ($mjesto_rodjenja) print "<th>Mjesto rođenja</th>";
	if ($adresa_mjesto) print "<th>Adresa mjesto</th>";
	if ($drzavljanstvo) print "<th>Državljanstvo</th>";
	print "<th>&nbsp;</th>";
	print "</tr>\n";
}

while ($osoba = db_fetch_assoc($q30)) {
	if ($tabelarno) {
		print "<tr><td>$rbr</td><td>".$osoba['prezime']."</td>";
		if ($ime_oca) print "<td>".$osoba['imeoca']."</td>";
		print "<td>".$osoba['ime']."</td>";
		if ($brindexa) print "<td>".$osoba['brindexa']."</td>";
		if ($spol) { 
			if ($osoba['spol'] == "Z") $osoba['spol'] = "Ž"; 
			print "<td>".$osoba['spol']."</td>";
		}
		if ($jmbg) print "<td>".$osoba['jmbg']."</td>";
		if ($nacin_studiranja) print "<td>".$osoba['nacin']."</td>";
		if ($login) print "<td>".$osoba['login']."</td>";
		if ($mjesto_rodjenja) print "<td>".$osoba['mjestorodj']."</td>";
		if ($adresa_mjesto) print "<td>".$osoba['adresamjesto']."</td>";
		if ($drzavljanstvo) print "<td>".$osoba['drzavljanstvo']."</td>";
		if ($zaduzenje) print "<td>".$osoba['zaduzenje']."</td>";

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
		if ($brindexa) print " (".$osoba['brindexa'].") ";
		if ($spol) { 
			if ($osoba['spol'] == "Z") $osoba['spol'] = "Ž"; 
			print " (".$osoba['spol'].") ";
		}
		if ($jmbg) print " (".$osoba['jmbg'].") ";
		if ($nacin_studiranja) print " - ".$osoba['nacin']." ";
		if ($login) print " - ".$osoba['login']." ";
		if ($mjesto_rodjenja) print "(".$osoba['mjestorodj'].")";
		if ($adresa_mjesto) print "(".$osoba['adresamjesto'].")";
		if ($drzavljanstvo) print "(".$osoba['drzavljanstvo'].")";
		if ($zaduzenje && $osoba['zaduzenje'] > 0) print " - dug: ".$osoba['zaduzenje']." KM";

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

?>
