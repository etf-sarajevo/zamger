<?

function izvjestaj_srednje() {




/**
 * linear regression function
 * @param $x array x-coords
 * @param $y array y-coords
 * @returns array() m=>slope, b=>intercept
 */
function linear_regression($x, $y) {

  // calculate number points
  $n = count($x);
  
  // ensure both arrays of points are the same size
  if ($n != count($y)) {

    trigger_error("linear_regression(): Number of elements in coordinate arrays do not match.", E_USER_ERROR);
  
  }

  // calculate sums
  $x_sum = array_sum($x);
  $y_sum = array_sum($y);

  $xx_sum = 0;
  $xy_sum = 0;
  
  for($i = 0; $i < $n; $i++) {
  
    $xy_sum+=($x[$i]*$y[$i]);
    $xx_sum+=($x[$i]*$x[$i]);
    
  }
  
  // calculate slope
  $m = (($n * $xy_sum) - ($x_sum * $y_sum)) / (($n * $xx_sum) - ($x_sum * $x_sum));
  
  // calculate intercept
  $b = ($y_sum - ($m * $x_sum)) / $n;
    
  // return result
  return array("m"=>$m, "b"=>$b);

}

function pearsonova_korelacija($ar1, $ar2) {
	$n = count($ar1);
	if ($n != count($ar2)) {
		trigger_error("Dimenzije nizova se ne poklapaju.", E_USER_ERROR);
	}

	$ar1_avg = array_sum($ar1) / $n;
	$ar2_avg = array_sum($ar2) / $n;

	// Racunamo standardnu devijaciju
	$tmp = array();
	foreach ($ar1 as $m)
		$tmp[] = ($m-$ar1_avg)*($m-$ar1_avg);
	$ar1_stddev = sqrt(array_sum($tmp) / $n);

	$tmp = array();
	foreach ($ar2 as $m)
		$tmp[] = ($m-$ar2_avg)*($m-$ar2_avg);
	$ar2_stddev = sqrt(array_sum($tmp) / $n);

	// Racunamo kovarijansu
	$cov = 0;
	for ($i=0; $i<$n; $i++)
		$cov += ($ar1[$i] - $ar1_avg) * ($ar2[$i] - $ar2_avg);
	$cov /= $n;

	$corr = $cov / ($ar1_stddev * $ar2_stddev);
	return $corr;
}




function experiment($ar1, $ar2, $ar1part, $ar2part) {
	$n = count($ar1);
	if ($n != count($ar2)) {
		trigger_error("Dimenzije nizova se ne poklapaju.", E_USER_ERROR);
	}

	$ar1_avg = array_sum($ar1) / $n;
	$ar2_avg = array_sum($ar2) / $n;

	// Racunamo standardnu devijaciju
	$tmp = array();
	foreach ($ar1 as $m)
		$tmp[] = ($m-$ar1_avg)*($m-$ar1_avg);
	$ar1_stddev = sqrt(array_sum($tmp) / $n);

	$tmp = array();
	foreach ($ar2 as $m)
		$tmp[] = ($m-$ar2_avg)*($m-$ar2_avg);
	$ar2_stddev = sqrt(array_sum($tmp) / $n);

	// Racunamo kovarijansu
	$cov = 0;
	for ($i=0; $i<count($ar1part); $i++)
		$cov += ($ar1part[$i] - $ar1_avg) * ($ar2part[$i] - $ar2_avg);
	$cov /= count($ar1part);

	$corr = $cov / ($ar1_stddev * $ar2_stddev);
	return $corr;
}


?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>
<p>Datum i vrijeme izvještaja: <?=date("d. m. Y. H:i");?></p>
<?


?>
<h2>Korelacija: srednja ocjena iz matematike i prijemni ispit</h2>

	<script type="text/javascript"
		src="https://www.google.com/jsapi?autoload={
		'modules':[{
		'name':'visualization',
		'version':'1',
		'packages':['corechart']
		}]
		}"></script>

<?

$q10 = db_query("SELECT so.osoba, AVG(so.ocjena), ss.id, ss.naziv, pp.prijemni_termin, pp.rezultat FROM srednja_ocjene AS so, uspjeh_u_srednjoj as uus, srednja_skola AS ss, prijemni_prijava AS pp WHERE so.tipocjene=2 AND so.osoba!=0 AND so.osoba=uus.osoba AND uus.srednja_skola=ss.id AND so.osoba=pp.osoba AND pp.izasao=1 AND (pp.prijemni_termin=14 or pp.prijemni_termin=23 or pp.prijemni_termin=30 or pp.prijemni_termin=33 or pp.prijemni_termin=39 or pp.prijemni_termin=43 or pp.prijemni_termin=47 or pp.prijemni_termin=49) GROUP BY so.osoba");

$suma = $broj = array();
for ($i=1; $i<23; $i++)
	$suma[$i] = $broj[$i] = array();

while ($r10 = db_fetch_row($q10)) {
	if ($r10[2] == 472) $r10[2] = 36;
	if ($r10[2] == 148) $r10[2] = 149;
	if ($r10[2] == 477) $r10[2] = 149;
	if ($r10[2] == 418) $r10[2] = 149;
	if ($r10[2] == 149) $r10[3] = "Srednja škola Konjic";
	if ($r10[2] == 388) $r10[2] = 17;
	if ($r10[2] == 381) $r10[2] = 246;
	if ($r10[2] == 104) $r10[2] = 26;
	
	$suma[$r10[4]][$r10[1]] += $r10[5];
	$broj[$r10[4]][$r10[1]] ++;
	
	$ocjene_termin[$r10[4]][] = $r10[1];
	$rezultati_termin[$r10[4]][] = $r10[5];
	$ocjene_termin_skola[$r10[4]][$r10[2]][] = $r10[1];
	$rezultati_termin_skola[$r10[4]][$r10[2]][] = $r10[5];
	$naziv_skole[$r10[2]] = $r10[3];
}

$skole_sumarno = $skole_broj_ocjena = array();


foreach ($suma as $pt => $suma_oc) {
	if (count($ocjene_termin[$pt]) == 0) continue;
	
	$godina = db_get("SELECT ag.naziv FROM akademska_godina ag, prijemni_termin pt WHERE pt.id=$pt AND pt.akademska_godina=ag.id");
	print "Termin: $pt ($godina)<br>";
	print "Izašlo ljudi: ".count($ocjene_termin[$pt])."<br><br>";

	print "Korelacija ocjene i rezultata na prijemnom: " . pearsonova_korelacija($ocjene_termin[$pt], $rezultati_termin[$pt]) . "<br><br>";

	//print "Linearna regresija ocjene vs. rezultata na prijemnom<br><br>";
	$regresija2[$pt] = linear_regression($ocjene_termin[$pt], $rezultati_termin[$pt]);

	//var_dump($regresija2[$pt]);
	//print "<br><br>";

	ksort($suma_oc);
	
	if ($pt == 39) {
	?>
	<script type="text/javascript">
	google.setOnLoadCallback(drawChart);

	function drawChart() {
		var data = new google.visualization.DataTable();
		data.addColumn('number', 'Prosječna ocjena iz matematike');
		data.addColumn('number', 'Uspjeh na prijemnom');
		data.addColumn('number', 'Procjena (regresija)');
		
		data.addRows([
		<?
		foreach ($suma_oc as $ocjena=>$bodova) {
			$bodova = $bodova / $broj[$pt][$ocjena];
			print "[$ocjena, $bodova, ".($ocjena*$regresija2[$pt]["m"] + $regresija2[$pt]["b"])."],";
		}
		?>
		]);

		var options = {
		title: 'Prosječna ocjena iz matematike vs. rezultat na prijemnom',
		legend: { position: 'bottom' }
		};

		var chart = new google.visualization.LineChart(document.getElementById('chart_linreg'));

		chart.draw(data, options);
	}
	</script>
	<div id="chart_linreg" style="width: 900px; height: 500px"></div>
	<?
	}
	
	
// 	foreach ($suma_oc as $ocjena=>$bodova) {
// 		$bodova = $bodova / $broj[$pt][$ocjena];
// 		print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Ocjena: $ocjena Prosjek: $bodova Procjena: ".($ocjena*$regresija2[$pt]["m"] + $regresija2[$pt]["b"])."<br>";
// 	}
	
	print "<br><br>";

	foreach ($ocjene_termin_skola[$pt] as $skola => $ocjene_skola) {
		if (count($ocjene_skola) < 5) continue;
		$k=0;
		for ($i=0; $i<count($ocjene_skola); $i++)
			$k += ($rezultati_termin_skola[$pt][$skola][$i] - ($ocjene_skola[$i]*$regresija2[$pt]["m"]+$regresija2[$pt]["b"])) / 40;
		$skole_sumarno[$skola] += $k;
		$skole_broj_ocjena[$skola] += count($ocjene_skola);
		$k /= count($ocjene_skola);
		print "Škola ".$naziv_skole[$skola].": Učenika: ".count($ocjene_skola)." K: ". round(experiment($ocjene_termin[$pt], $rezultati_termin[$pt], $ocjene_skola, $rezultati_termin_skola[$pt][$skola]),2) . " Poklapanje sa lin.reg: $k<br><br>";
	}

}

foreach($skole_sumarno as $skola => &$prosjek) {
	$prosjek /= $skole_broj_ocjena[$skola];
}

arsort($skole_sumarno);


?>
<h2>Sumarno škole</h2>
<?
$i = 1;
foreach($skole_sumarno as $skola => &$prosjek) {
	if ($skole_broj_ocjena[$skola] < 5) continue;
	print "$i. <b>" . $naziv_skole[$skola] . "</b>: " . sprintf("%.2f%%", $prosjek*100) . " (uzorak: ".$skole_broj_ocjena[$skola].")<br>";
	$i++;
}

}
