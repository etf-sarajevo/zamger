<?

// STUDENT/PROSJECI - skripta za racunanje prosjeka

// v3.9.1.0 (2008/10/20) + Novi modul student/prosjeci
// v3.9.1.1 (2009/02/07) + Dodan prikaz prosjeka po semestrima, na zahtjev studenata
// v4.0.0.0 (2009/02/19) + Release
// v4.0.9.1 (2009/03/31) + Tabela konacna_ocjena preusmjerena sa ponudakursa na tabelu predmet


function student_prosjeci() {

global $userid;

$maxgod=0;
$q10 = myquery("select ko.ocjena, pk.semestar from konacna_ocjena as ko, student_predmet as sp, ponudakursa as pk where sp.student=$userid and pk.predmet=ko.predmet and pk.akademska_godina=ko.akademska_godina and ko.student=$userid and sp.predmet=pk.id");
while ($r10 = mysql_fetch_row($q10)) {
	$sumoc += $r10[0]; $broc++;
	$sumasem[$r10[1]] += $r10[0];
	$brocsem[$r10[1]]++;
	if ($r10[1]/2>$maxgod) $maxgod=$r10[1]/2;
//print "Ocjena: $r10[0] ($r10[1])<br/>";
}
//$maxgod=intval($maxgod);

?>
<h2>Ukupan prosjek: <?=round($sumoc/$broc, 2)?></h2>

<h2>Sume po godinama:</h2>
<?
for ($i=1; $i<=$maxgod; $i++) {
	$prosjek = ($sumasem[$i*2-1]+$sumasem[$i*2]) / ($brocsem[$i*2-1]+$brocsem[$i*2]);
	$prosjek1 = $sumasem[$i*2-1]/$brocsem[$i*2-1];
	$prosjek2 = $sumasem[$i*2]/$brocsem[$i*2];

	?>
	<h3><?=$i?>. godina: <?=round($prosjek, 2)?></h3>
	<?=($i*2-1)?>. semestar: <?=round($prosjek1, 2)?><br>
	<?=($i*2)?>. semestar: <?=round($prosjek2, 2)?><br>
	<?
	
}

}

?>