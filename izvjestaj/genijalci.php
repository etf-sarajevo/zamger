<?

// IZVJESTAJ/GENIJALCI - Pregled studenata po prosjeku

// v3.9.1.0 (2009/02/04) + Prepravljam jednu stariju standalone skriptu
// v4.0.0.0 (2009/02/19) + Release
// v4.0.0.1 (2009/08/26) + Omogucujem izbor studija -1 (Svi studiji), dodajem parametar "godina studija", reaktiviram broj indexa, ne prikazujem studente koji imaju nepolozene ispite


function izvjestaj_genijalci() {


?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>

<p>Datum i vrijeme izvještaja: <?=date("d. m. Y. H:i");?></p>
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
$wherestudij=$whereprosliciklus="";
if ($studij>0) {
	$q20 = myquery("select naziv from studij where id=$studij");
	?><h3><?=mysql_result($q20,0,0)?></h3><?
	$wherestudij="and ss.studij=$studij";

} else if ($studij == -3) {
	$ciklus = 2;
	?><h3>Svi studiji (MSc bez BSca)</h3><?
	$q25 = myquery("select s.id from studij as s, tipstudija as ts where s.tipstudija=ts.id and ts.ciklus=2");
	$wherestudij="and (";
	while ($r25 = mysql_fetch_row($q25)) {
		if (strlen($wherestudij)>5) $wherestudij .= " or ";
		$wherestudij .= "ss.studij=$r25[0]";
	}
	$wherestudij .= ")";
	$whereprosliciklus = "and ts.ciklus=2";

} else {
	$ciklus = -$studij;

	?><h3>Svi studiji (<?=$ciklus?>. ciklus)</h3><?

	$q25 = myquery("select s.id from studij as s, tipstudija as ts where s.tipstudija=ts.id and ts.ciklus=$ciklus");
	$wherestudij="and (";
	while ($r25 = mysql_fetch_row($q25)) {
		if (strlen($wherestudij)>5) $wherestudij .= " or ";
		$wherestudij .= "ss.studij=$r25[0]";
	}
	$wherestudij .= ")";
}


// Parametar: godina studija
$godinastudija = intval($_REQUEST['godina_studija']);
if ($studij == -2)
	$minsumaects = $godinastudija*60-floatval($_REQUEST['limit_ects']) + 180;
else
	$minsumaects = $godinastudija*60-floatval($_REQUEST['limit_ects']);
$wheresemestar="";;
if ($godinastudija>0) {
	?><h3><?=$godinastudija?>. godina studija</h3><?
	$wheresemestar="and ss.semestar=".($godinastudija*2); 
	// za upit šta trenutno sluša gledamo samo ljetnji semestar, jer ako ima ljetnji onda je sigurno završio i zimski, dok obrnuto ne mora biti
}

$limit_predmet = intval($_REQUEST['limit_predmet']);

if ($_REQUEST['samo_tekuca_gs'] == "da") $samo_tekuca_gs = true; else $samo_tekuca_gs = false;


$q1 = myquery("SELECT a.id, a.prezime, a.ime, a.brindexa, ns.naziv FROM `osoba` as 
a, student_studij as ss, nacin_studiranja as ns WHERE a.id=ss.student and ss.akademska_godina=$ak_god and ss.nacin_studiranja=ns.id $wherestudij $wheresemestar");

while ($r1 = mysql_fetch_row($q1)) {
	$q2 = myquery("select distinct ko.ocjena, p.ects, pk.semestar, p.naziv from konacna_ocjena as ko, predmet as p, ponudakursa as pk, student_predmet as sp, studij as st, tipstudija as ts where ko.student=$r1[0] and ko.predmet=p.id and ko.ocjena>5 and sp.student=$r1[0] and sp.predmet=pk.id and pk.predmet=p.id and pk.akademska_godina=ko.akademska_godina and ko.akademska_godina<=$ak_god and pk.studij=st.id and st.tipstudija=ts.id $whereprosliciklus order by pk.semestar");
	$suma=0; $broj=0; $sumaects=0;
	while ($r2 = mysql_fetch_row($q2)) {
		$sumaects += $r2[1]; 
		if ($samo_tekuca_gs) 
			if ($r2[2] < $godinastudija*2-1) continue;
		$suma += $r2[0]; $broj++; 
	}

	// preskacemo studente sa premalo polozenih predmeta
	if ($limit_predmet>0) {
		$q3 = myquery("select count(*) from student_predmet as sp, ponudakursa as pk, studij as st, tipstudija as ts where sp.student=$r1[0] and sp.predmet=pk.id and pk.akademska_godina=$ak_god and pk.studij=st.id and st.tipstudija=ts.id $whereprosliciklus and (select count(*) from konacna_ocjena as ko where ko.student=$r1[0] and ko.predmet=pk.predmet and ko.ocjena>5)=0");
		if (mysql_result($q3,0,0)>$limit_predmet) continue;
	} else if ($sumaects<$minsumaects) continue; 


	$prosjek = $suma/$broj;
	$prosjeci[$r1[0]]=$prosjek;
	$imeprezime[$r1[0]]="$r1[1] $r1[2]";
	$brindexa[$r1[0]]=$r1[3];
	$nacinstudiranja[$r1[0]]=$r1[4];
	$ects[$r1[0]]=$sumaects;
}

arsort($prosjeci);

?>
<table border="1" cellspacing="0" cellpadding="2">
<tr><th>R.br</th><th>Prezime i ime</th><th>Broj 
indexa</th><th>Način studiranja</th><th>Prosjek</th></tr>
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
