<?

// STUDENT/PROSJECI - skripta za racunanje prosjeka

// v3.9.1.0 (2008/10/20) + Novi modul student/prosjeci
// v3.9.1.1 (2009/02/07) + Dodan prikaz prosjeka po semestrima, na zahtjev studenata
// v4.0.0.0 (2009/02/19) + Release
// v4.0.9.1 (2009/03/31) + Tabela konacna_ocjena preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.2 (2009/10/21) + Rewrite modula tako da uzima u obzir plan studija i cikluse studija


function student_prosjeci() {

global $userid, $conf_naziv_institucije;


?>
<h2>Prosjeci</h2>
<?


// Ako se ne koriste planovi studija, dajemo prosjek svega što je student slušao
//$q5 = myquery("select count(*) from plan_studija");
//if (mysql_num_rows($q5)==0) {
if (true) {
	// Ovo će dati neprecizne rezultate u slučaju da je student mijenjao studij u toku studiranja
	// (objašnjenje u komentaru drugog dijela)

	$maxgod=0;
	$q10 = myquery("select ts.ciklus, pk.semestar, ko.ocjena from student_predmet as sp, ponudakursa as pk, konacna_ocjena as ko, studij as s, tipstudija as ts where sp.student=$userid and sp.predmet=pk.id and ko.predmet=pk.predmet and ko.akademska_godina=pk.akademska_godina and ko.student=$userid and pk.studij=s.id and s.tipstudija=ts.id");
	$ciklusi=array();
	while ($r10 = mysql_fetch_row($q10)) {
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
	$q10 = myquery("select distinct ss.studij, ss.plan_studija, s.naziv, ag.naziv from student_studij as ss, studij as s, akademska_godina as ag where ss.studij=s.id and ss.plan_studija=ag.id and ss.student=$userid order by ss.akademska_godina");
	if (mysql_num_rows($q10)==0) {
		print "<p>Nikada niste bili upisani na $conf_naziv_institucije. Ne možemo odrediti prosjek.</p>\n";
		return;
	}

	if (mysql_num_rows($q10)==1) {
		// Ako je student slušao samo jedan studij, olakšavamo slučaj
		$studij=mysql_result($q10,0,0);
		$plan_studija=mysql_result($q10,0,1);

	} else {
		?>
		<p>Za koji studij želite odrediti prosjeke:<br />
		<?
		while ($r10 = mysql_fetch_row($q10)) {
			print "* <a href=\"?sta=student/prosjeci&studij=$r10[0]&plan_studija=$r10[1]\">$r10[2] (plan i program usvojen $r10[3])</a><br />\n";
		}
		print "</p>\n";
		return;
	}
}



// Naslov

$q15 = myquery("select naziv from studij where id=$studij");
?>
<h2><?=mysql_result($q15,0,0);?></h2>
<?


// Prolazimo kroz plan studija

$q20 = myquery("select predmet, semestar, obavezan from plan_studija where godina_vazenja=$plan_studija and studij=$studij order by semestar");
while ($r20 = mysql_fetch_row($q20)) {
	$semestar = $r20[1];
	if ($r20[2]==1) { // Obavezan
		$predmet=$r20[0];
		$q30 = myquery("select ocjena from konacna_ocjena where student=$userid and predmet=$predmet");
	} else { // Izborni
		$izborni_slot=$r20[0];
		$q30 = myquery("select ko.ocjena, ko.predmet from konacna_ocjena as ko, izborni_slot as iz where iz.id=$izborni_slot and iz.predmet=ko.predmet and ko.student=$userid ".$bio_izborni_sql[$izborni_slot]);
		if (mysql_num_rows($q30)>0)
			$bio_izborni_sql[$izborni_slot] .= "and ko.predmet!=".mysql_result($q30,0,1);
	}

	if (mysql_num_rows($q30)>0) {
		$ocjena = mysql_result($q30,0,0);
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