<?

// STUDENT/PROSJECI - skripta za racunanje prosjeka



function student_prosjeci() {

global $userid, $conf_naziv_institucije;


?>
<h2>Prosjeci</h2>
<?


// Ako se ne koriste planovi studija, dajemo prosjek svega što je student slušao
//$q5 = db_query("select count(*) from plan_studija");
//if (db_num_rows($q5)==0) {
if (true) {
	// Ovo će dati neprecizne rezultate u slučaju da je student mijenjao studij u toku studiranja
	// (objašnjenje u komentaru drugog dijela)

	$maxgod=0;
	$q10 = db_query("select ts.ciklus, pk.semestar, ko.ocjena from student_predmet as sp, ponudakursa as pk, konacna_ocjena as ko, studij as s, tipstudija as ts where sp.student=$userid and sp.predmet=pk.id and ko.predmet=pk.predmet and ko.akademska_godina=pk.akademska_godina and ko.student=$userid and pk.studij=s.id and s.tipstudija=ts.id");
	$ciklusi=array();
	while ($r10 = db_fetch_row($q10)) {
		$ciklus=$r10[0]; $semestar=$r10[1]; $ocjena=$r10[2];

		if (!in_array($ciklus,$ciklusi)) $ciklusi[]=$ciklus;
		$suma_ciklus[$ciklus] += $ocjena; $broj_ciklus[$ciklus]++;
		$suma_ciklus_semestar["$ciklus-$semestar"] += $ocjena; $broj_ciklus_semestar["$ciklus-$semestar"]++;
	
		if ($r10[1]/2>$maxgod) $maxgod=$r10[1]/2;
	//print "Ocjena: $r10[0] ($r10[1])<br/>";
	}
	//$maxgod=intval($maxgod);


	sort($ciklusi);
	foreach ($ciklusi as $ciklus) {
		?>
		
		<h3><?=$ciklus?>. ciklus studija</h3>
		<?

		if ($broj_ciklus_semestar["$ciklus-1"]==0) {
			?>
			<h4>Niste položili nijedan ispit u ovom ciklusu. Prosjek iznosi: 0</h4>
			<?
			continue;
		}

		?>
		<p>Ukupan prosjek ciklusa: <?=round($suma_ciklus[$ciklus]/$broj_ciklus[$ciklus], 2)?>
		<p>
		<?
		$i=1;
		while ($broj_ciklus_semestar["$ciklus-$i"]>0) {
			if ($i%2==1) {
				$god=intval($i/2)+1;
				$j=$i+1;
				$prosjek = ($suma_ciklus_semestar["$ciklus-$i"] + $suma_ciklus_semestar["$ciklus-$j"]) / ($broj_ciklus_semestar["$ciklus-$i"] + $broj_ciklus_semestar["$ciklus-$j"]);
				?>
				<h4><?=$god?>. godina: <?=round($prosjek, 2)?></h4>
				<?
			}

			$prosjek = $suma_ciklus_semestar["$ciklus-$i"] / $broj_ciklus_semestar["$ciklus-$i"];
			?>
			<?=($i)?>. semestar: <?=round($prosjek, 2)?><br>
			<?

			$i++;
		}
	}

	

?>
<p>&nbsp;</p>
<p><b>Tumačenje prikazanih brojeva:</b>
<ul>
<li>Prosjek godine <i>nije jednak</i> srednjoj vrijednosti prosjeka semestara, jer broj predmeta po semestrima ne mora biti isti.
	<ul>
		<li>Primjer: U prvom semestru ste imali pet predmeta i iz svih pet ste dobili ocjenu 10. Prosjek 1. semestra je 10.0</li>
		<li>U drugom semestru ste imali šest predmeta i iz svih šest ste dobili ocjenu 6. Prosjek 2. semestra je 6.0</li>
		<li>No prosjek godine <i>nije</i> 8.0 nego je nešto manji. Prosjek godine se računa kao prosjek svih predmeta na godini, kojih ima jedanaest, pa je to (5*10 + 6*6) / 11 = 86 / 11 = 7,82.</li>
	</ul>
</li>
<li>Prosjek ciklusa <i>nije jednak</i> srednjoj vrijednosti svih godina jer su u prosjek ciklusa uračunate i godine koje nisu završene tj. godine koje ponavljate.
	<ul>
		<li>Primjer: Na prvoj godini ste imali deset predmeta i sve predmete ste položili sa ocjenom 10. Prosjek godine je 10.0.</li>
		<li>Na drugoj godini ste imali također deset predmeta ali položili ste samo dva sa ocjenom 6. Prosjek druge godine neće biti prikazan jer godina nije završena.</li>
		<li>Prosjek ciklusa se računa kao prosjek svih položenih predmeta, a to je (10*10 + 2*6) / 12 = 112 / 12 = 9,33.</li>
	</ul>
</ul>
<?

	
	return;
}



// RAD SA PLANOM STUDIJA

// Npr. student 1 je slušao 2. godinu na studiju AE, položio 2-3 predmeta, prebacio se na 2. godinu RI
//  - Položeni predmeti sa 2. godine AE koji ne postoje na studiju RI se NE računaju. Predmet "Diskretna 
// matematika" koji postoji na oba studija bi se trebao računati, ali to formalno nije isti predmet tako 
// da se ostavlja profesoru da prizna ocjenu. S druge strane položeni predmeti sa 1. godine se računaju!
//
// Dakle mora se proći kroz plan studija i vidjeti koje predmete iz plana je student položio. Da bi ovo 
// radilo, student mora odabrati za koji studij računa prosjek i po kojem planu


$studij = intval($_REQUEST['studij']);
$plan_studija = intval($_REQUEST['plan_studija']);

if ($studij==0 || $plan_studija==0) {
	$q10 = db_query("select distinct ss.studij, ss.plan_studija, s.naziv, ag.naziv from student_studij as ss, studij as s, plan_studija as ps, akademska_godina as ag where ss.studij=s.id and ss.plan_studija=ps.id AND ps.godina_vazenja=ag.id and ss.student=$userid order by ss.akademska_godina");
	if (db_num_rows($q10)==0) {
		print "<p>Nikada niste bili upisani na $conf_naziv_institucije. Ne možemo odrediti prosjek.</p>\n";
		return;
	}

	if (db_num_rows($q10)==1) {
		// Ako je student slušao samo jedan studij, olakšavamo slučaj
		$studij=db_result($q10,0,0);
		$plan_studija=db_result($q10,0,1);

	} else {
		?>
		<p>Za koji studij želite odrediti prosjeke:<br />
		<?
		while (db_fetch4($q10, $studij, $plan_studija, $naziv_studija, $akademska_godina)) {
			print "* <a href=\"?sta=student/prosjeci&amp;studij=$studij&amp;plan_studija=$plan_studija\">$naziv_studija (plan i program usvojen $akademska_godina)</a><br />\n";
		}
		print "</p>\n";
		return;
	}
}



// Naslov

$q15 = db_query("select naziv from studij where id=$studij");
?>
<h2><?=db_result($q15,0,0);?></h2>
<?


// Prolazimo kroz plan studija

$q20 = db_query("select pasos_predmeta, plan_izborni_slot, semestar, obavezan from plan_studija_predmet where plan_studija=$plan_studija order by semestar");
while (db_fetch4($q20, $pasos_predmeta, $plan_izborni_slot, $semestar, $obavezan)) {
	if ($obavezan == 1) { // Obavezan
		$q30 = db_query("select ko.ocjena from konacna_ocjena ko, pasos_predmeta pp where ko.student=$userid and ko.predmet=pp.predmet AND pp.id=$pasos_predmeta");
	} else { // Izborni
		$q30 = db_query("select ko.ocjena, ko.predmet from konacna_ocjena as ko, pasos_predmeta pp, plan_izborni_slot as pis where pis.id=$plan_izborni_slot and pis.pasos_predmeta=pp.id AND pp.predmet=ko.predmet and ko.student=$userid ".$bio_izborni_sql[$plan_izborni_slot]);
		if (db_num_rows($q30)>0)
			$bio_izborni_sql[$plan_izborni_slot] .= "and ko.predmet!=".db_result($q30,0,1);
	}

	if (db_num_rows($q30)>0) {
		$ocjena = db_result($q30,0,0);
		$suma_studij += $ocjena; $broj_studij++;
		$suma_semestar[$semestar] += $ocjena; $broj_semestar[$semestar]++;
	} else {
		$nije_ocistio_semestar[$semestar]=1;
	}
}


// Ispis

if ($broj_semestar[1]==0) {
	?>
	<h4>Niste položili nijedan ispit. Prosjek iznosi: 0</h4>
	<?
	return;
}

?>
<p>Ukupan prosjek: <?=round($suma_studij/$broj_studij, 2)?>
<p>
<?
$i=1;
while ($broj_semestar[$i]>0) {
	if ($i%2==1) {
		$god=intval($i/2)+1;
		$j=$i+1;
		$prosjek = ($suma_semestar[$i] + $suma_semestar[$j]) / ($broj_semestar[$i] + $broj_semestar[$j]);
		?>
		<h4><?=$god?>. godina: <?=round($prosjek, 2)?></h4>
		<?
	}

	$prosjek = $suma_semestar[$i] / $broj_semestar[$i];
	?>
	<?=($i)?>. semestar: <?=round($prosjek, 2)?><br>
	<?

	$i++;
}



} // function student_prosjeci

?>
