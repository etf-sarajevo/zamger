<?

// IZVJESTAJ/GENIJALCI - Pregled studenata po prosjeku

// v3.9.1.0 (2009/02/04) + Prepravljam jednu stariju standalone skriptu



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


$studij = intval($_REQUEST['studij']);
if ($studij>0) {
	$q20 = myquery("select naziv from studij where id=$studij");
	?><h3><?=mysql_result($q20,0,0)?></h3><?
}




$q1 = myquery("SELECT a.id, a.prezime, a.ime, a.brindexa FROM `osoba` as 
a, student_studij as ss WHERE a.id=ss.student and ss.akademska_godina=$ak_god and ss.studij=$studij");

while ($r1 = mysql_fetch_row($q1)) {
	$q2 = myquery("select ocjena from konacna_ocjena where student=$r1[0]");
	$suma=0; $broj=0;
	while ($r2 = mysql_fetch_row($q2)) { $suma += $r2[0]; $broj++; }
	$prosjek = $suma/$broj;
	$prosjeci[$r1[0]]=$prosjek;
	$imeprezime[$r1[0]]="$r1[1] $r1[2]";
	$brindexa[$r1[0]]=$r1[3];
}

arsort($prosjeci);

?>
<table border="1">
<tr><td>R.br</td><td>Prezime i ime</td><!--td>Broj 
indexa</td--><td>Prosjek</td></tr>
<?

$k=1;

foreach ($prosjeci as $id=>$prosjek) {
	if ($prosjek<$limit_prosjek) break;
	?> 
	<tr><td><?=$k++?></td><td><?=$imeprezime[$id]?></td><!--td><?=$brindexa[$id]?></td--><td><?=round($prosjek,2)?></td></tr>
	<?
}

?></table><?

}
