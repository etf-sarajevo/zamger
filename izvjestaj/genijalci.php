<?

// IZVJESTAJ/GENIJALCI - Pregled studenata po prosjeku



function izvjestaj_genijalci() {

require_once("lib/utility.php"); // procenat

?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>

<p>Datum i vrijeme izvještaja: <?=date("d. m. Y. H:i");?></p>
<?

// Akademska godina

$ak_god = intval($_REQUEST['akademska_godina']);
if ($ak_god==0) {
	// Aktuelna godina
	$q10 = db_query("select id, naziv from akademska_godina where aktuelna=1");
	$ak_god = db_result($q10,0,0);
	$ak_god_naziv = db_result($q10,0,1);
} else {
	$q10 = db_query("select naziv from akademska_godina where id=$ak_god");
	$ak_god_naziv = db_result($q10,0,0);
}


$limit_prosjek = intval($_REQUEST['limit_prosjek']);


?>
<h2>Pregled studenata po prosjeku</h2>
<?

// Parametar: studij
$studij = intval($_REQUEST['studij']);
$wherestudij=$whereprosliciklus="";
if ($studij>0) {
	$q20 = db_query("select naziv from studij where id=$studij");
	?><h3><?=db_result($q20,0,0)?></h3><?
	$wherestudij="and ss.studij=$studij";

} else if ($studij == -3) {
	$ciklus = 2;
	?><h3>Svi studiji (MSc bez BSca)</h3><?
	$q25 = db_query("select s.id from studij as s, tipstudija as ts where s.tipstudija=ts.id and ts.ciklus=2");
	$wherestudij="and (";
	while ($r25 = db_fetch_row($q25)) {
		if (strlen($wherestudij)>5) $wherestudij .= " or ";
		$wherestudij .= "ss.studij=$r25[0]";
	}
	$wherestudij .= ")";
	$whereprosliciklus = "and ts.ciklus=2";

} else {
	$ciklus = -$studij;

	?><h3>Svi studiji (<?=$ciklus?>. ciklus)</h3><?

	$q25 = db_query("select s.id from studij as s, tipstudija as ts where s.tipstudija=ts.id and ts.ciklus=$ciklus");
	$wherestudij="and (";
	while ($r25 = db_fetch_row($q25)) {
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


$q1 = db_query("SELECT a.id, a.prezime, a.ime, a.brindexa, ns.naziv, ss.studij 
FROM osoba a, student_studij as ss, nacin_studiranja as ns 
WHERE a.id=ss.student and ss.akademska_godina=$ak_god and ss.nacin_studiranja=ns.id $wherestudij $wheresemestar");

while ($r1 = db_fetch_row($q1)) {
	$id_studenta = $r1[0];
	$student_id_studija = $r1[5];
	
	$imeprezime[$id_studenta]="$r1[1] $r1[2]";
	$brindexa[$id_studenta]=$r1[3];
	$nacinstudiranja[$id_studenta]=$r1[4];
	
	$q2 = db_query("select distinct ko.ocjena, p.ects, pk.semestar, p.naziv from konacna_ocjena as ko, predmet as p, ponudakursa as pk, student_predmet as sp, studij as st, tipstudija as ts where ko.student=$r1[0] and ko.predmet=p.id and ko.ocjena>5 and sp.student=$r1[0] and sp.predmet=pk.id and pk.predmet=p.id and pk.akademska_godina=ko.akademska_godina and ko.akademska_godina<=$ak_god and pk.studij=st.id and st.tipstudija=ts.id $whereprosliciklus order by pk.semestar");
	$suma=0; $broj=0; $sumaects=0;
	while ($r2 = db_fetch_row($q2)) {
		$sumaects += $r2[1];
		if ($samo_tekuca_gs) 
			if ($r2[2] < $godinastudija*2-1) continue;
		$suma += $r2[0]; $broj++; 
		$sumasemestar[$r2[2]][$r1[0]] += $r2[0];
		$brojsemestar[$r2[2]][$r1[0]]++;
	}
	
	// Dodajemo ocjene sa priznavanja
	$q4 = db_query("SELECT pr.ocjena, pr.ects, ss.semestar, pr.naziv_predmeta
	FROM priznavanje pr, student_studij ss, studij st, tipstudija ts
	WHERE pr.student=$id_studenta AND ss.student=$id_studenta AND ss.akademska_godina=pr.akademska_godina AND ss.semestar MOD 2 = 1 AND ss.studij=st.id and st.tipstudija=ts.id $whereprosliciklus order by ss.semestar");
	while ($r4 = db_fetch_row($q4)) {
		$sumaects += $r4[1];
		if ($samo_tekuca_gs) 
			if ($r4[2] < $godinastudija*2-1) continue;
		$suma += $r4[0]; $broj++; 
		$sumasemestar[$r4[2]][$r1[0]] += $r4[0];
		$brojsemestar[$r4[2]][$r1[0]]++;
	}

	// preskacemo studente sa premalo polozenih predmeta
	if ($limit_predmet>0) {
		$q3 = db_query("select count(*) from student_predmet as sp, ponudakursa as pk, studij as st, tipstudija as ts where sp.student=$id_studenta and sp.predmet=pk.id and pk.akademska_godina=$ak_god and pk.studij=st.id and st.tipstudija=ts.id $whereprosliciklus and (select count(*) from konacna_ocjena as ko where ko.student=$id_studenta and ko.predmet=pk.predmet and ko.ocjena>5)=0");
		if (db_result($q3,0,0)>$limit_predmet) continue;
	} else if ($sumaects<$minsumaects) continue; 

	$prosjek = $suma/$broj;
	$prosjeci[$id_studenta]=$prosjek;
	$ects[$id_studenta]=$sumaects;
}

arsort($prosjeci);

?>
<table border="1" cellspacing="0" cellpadding="2">
<tr><th>R.br</th><th>Prezime i ime</th><th>Broj 
indexa</th><th>Način studiranja</th><th>Prosjek</th></tr>
<?

$k=1;

$statistika=array();

foreach ($prosjeci as $id=>$prosjek) {
	$ocjena = intval(round($prosjek,0));
	$statistika[$ocjena]++;
	$statistika[0]++;
	if ($prosjek<$limit_prosjek) break;
	?> 
	<tr><td><?=$k++?></td><td><?=$imeprezime[$id]?></td><td><?=$brindexa[$id]?></td><td><?=$nacinstudiranja[$id]?></td><td><?=round($prosjek,2) /*. " " . round($sumasemestar[1][$id]/$brojsemestar[1][$id],2) . " " . round($sumasemestar[2][$id]/$brojsemestar[2][$id],2) . " " . round($sumasemestar[3][$id]/$brojsemestar[3][$id],2) . " " . round($sumasemestar[4][$id]/$brojsemestar[4][$id],2) . " " . round($sumasemestar[5][$id]/$brojsemestar[5][$id],2) . " " . round($sumasemestar[6][$id]/$brojsemestar[6][$id],2)*/ ?></td></tr>
	<?
}

?></table>

<p>Po ocjenama:</p>

<table><tr><th>Ocjena</th><th>Studenata</th><th>Procenat</th></tr>
<?

for ($i=10; $i>5; $i--)
	print "<tr><td>$i</td><td>".$statistika[$i]."</td><td>".
	procenat($statistika[$i], $statistika[0])."</td></tr>";

?></table>
<?

}
