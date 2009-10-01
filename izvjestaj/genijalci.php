<?

// IZVJESTAJ/GENIJALCI - Pregled studenata po prosjeku

// v3.9.1.0 (2009/02/04) + Prepravljam jednu stariju standalone skriptu
// v4.0.0.0 (2009/02/19) + Release
// v4.0.0.1 (2009/08/26) + Omogucujem izbor studija -1 (Svi studiji), dodajem parametar "godina studija", reaktiviram broj indexa, ne prikazujem studente koji imaju nepolozene ispite


function izvjestaj_genijalci() {


?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>
<?

// Akademska godina

$ak_god = intval($_REQUEST['akademska_godina']);
if ($ak_god==0) {
	// Aktuelna godina
	$q10 = myquery("select id, naziv from akademska_godina where aktuelna=1");
	$ak_god = mysql_result($q10,0,0);
	$ak_god_naziv = mysql_result($q10,0,1);
} else {
	$q10 = myquery("select naziv from akademska_godina where id=$ak_god");
	$ak_god_naziv = mysql_result($q10,0,0);
}


$limit_prosjek = intval($_REQUEST['limit_prosjek']);


?>
<h2>Pregled studenata po prosjeku</h2>
<?

// Parametar: studij
$studij = intval($_REQUEST['studij']);
$wherestudij="";
if ($studij>0) {
	$q20 = myquery("select naziv from studij where id=$studij");
	?><h3><?=mysql_result($q20,0,0)?></h3><?
	$wherestudij="and ss.studij=$studij";
} else {
	?><h3>Svi studiji</h3><?
}


// Parametar: godina studija
$godinastudija = intval($_REQUEST['godina_studija']);
$wheresemestar="";;
if ($godinastudija>0) {
	?><h3><?=$godinastudija?>. godina studija</h3><?
	$wheresemestar="and ss.semestar=".($godinastudija*2); // gledamo samo ljetnji semestar, jer ako ima ljetnji onda je sigurno zavrsio i zimski, dok obrnuto ne mora biti
}



$q1 = myquery("SELECT a.id, a.prezime, a.ime, a.brindexa, ns.naziv FROM `osoba` as 
a, student_studij as ss, nacin_studiranja as ns WHERE a.id=ss.student and ss.akademska_godina=$ak_god and ss.nacin_studiranja=ns.id $wherestudij $wheresemestar");

while ($r1 = mysql_fetch_row($q1)) {
	$q2 = myquery("select distinct ko.ocjena, p.ects, pk.semestar, p.naziv from konacna_ocjena as ko, predmet as p, ponudakursa as pk, student_predmet as sp where ko.student=$r1[0] and ko.predmet=p.id and sp.student=$r1[0] and sp.predmet=pk.id and pk.predmet=p.id and pk.akademska_godina=ko.akademska_godina and pk.semestar<".($godinastudija*2+1));
	$suma=0; $broj=0; $sumaects=0;
	while ($r2 = mysql_fetch_row($q2)) { 
		$suma += $r2[0]; $broj++; $sumaects += $r2[1]; 
	}

	// preskacemo studente sa premalo polozenih predmeta
	$minsumaects = $godinastudija*60-floatval($_REQUEST['limit_ects']);
	if ($sumaects<$minsumaects) continue; 

	$prosjek = $suma/$broj;
	$prosjeci[$r1[0]]=$prosjek;
	$imeprezime[$r1[0]]="$r1[1] $r1[2]";
	$brindexa[$r1[0]]=$r1[3];
	$nacinstudiranja[$r1[0]]=$r1[4];
}

arsort($prosjeci);

?>
<table border="1">
<tr><td>R.br</td><td>Prezime i ime</td><td>Broj 
indexa</td><td>Način studiranja</td><td>Prosjek</td></tr>
<?

$k=1;

foreach ($prosjeci as $id=>$prosjek) {
	if ($prosjek<$limit_prosjek) break;
	?> 
	<tr><td><?=$k++?></td><td><?=$imeprezime[$id]?></td><td><?=$brindexa[$id]?></td><td><?=$nacinstudiranja[$id]?></td><td><?=round($prosjek,2)?></td></tr>
	<?
}

?></table><?

}
