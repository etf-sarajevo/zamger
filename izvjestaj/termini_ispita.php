<?

// IZVJESTAJ/TERMINI_ISPITA - spiskovi studenata za termine ispita sa bodovima i ostalim podacima



function izvjestaj_termini_ispita() {

global $userid,$user_nastavnik,$user_studentska,$user_siteadmin;

require_once("lib/utility.php"); // procenat, bssort


?>

<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>
<p>Datum i vrijeme izvještaja: <?=date("d. m. Y. H:i");?></p>
<?

// Parametar
if(isset($_REQUEST['termin'])){
	$termin_id_url = intval($_REQUEST['termin']);
}
if(isset($_REQUEST['ispit'])) $ispit = intval($_REQUEST['ispit']);
else{
	$q8 = db_query("select opcije from dogadjaj where id=$termin_id_url");
	if (db_num_rows($q8)<1) {
		niceerror("Nepostojeći termin.");
		return;
	}
	$ispit = db_result($q8,0,0);
}


$q9 = db_query("select komponenta from ispit where id=$ispit");
if (db_num_rows($q9)<1) {
	niceerror("Nepostojeći ispit.");
	return;
}
$komp = db_result($q9,0,0);
// Upit za ispit

$q10 = db_query("select UNIX_TIMESTAMP(i.datum), ap.naziv, i.predmet, i.akademska_godina from ispit as i, aktivnost_predmet as ap where i.id=$ispit and i.komponenta=ap.id");

$predmet = db_result($q10,0,2);
$ag = db_result($q10,0,3);
$finidatum = date("d. m. Y.", db_result($q10,0,0));
$naziv = db_result($q10,0,1);


// Dodatna provjera privilegija
if (!$user_studentska && !$user_siteadmin) {
	$q20 = db_query("select nivo_pristupa from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
	if (db_num_rows($q20) < 1) {
		biguglyerror("Nemate permisije za pristup ovom izvještaju");
		zamgerlog ("nije admin predmeta pp$predmet godina ag$ag",3); // 3 = error
		return;
	}
	$privilegija = db_result($q20,0,0);
}

// Cool editing box
if ($privilegija=="nastavnik" || $privilegija=="super_asistent" || $user_siteadmin) {
	cool_box('ajah_start("index.php?c=N&sta=common/ajah&akcija=izmjena_ispita&idpolja="+zamger_coolbox_origcaller.id+"&vrijednost="+coolboxedit.value, "undo_coolbox()", "zamger_coolbox_origcaller=false");');
	?>
	<script language="JavaScript">
	function undo_coolbox() {
		var greska = document.getElementById("zamger_ajah-info").innerText || document.getElementById("zamger_ajah-info").textContent;
		if (greska.includes("Exam result too large")) {
		    alert ("Unijeli ste rezultat ispita izvan dozvoljenog opsega");
            document.getElementById("zamger_ajah-info").innerText = "";
            document.getElementById("zamger_ajah-info").textContent = "";
		} else
			alert(greska);
		zamger_coolbox_origcaller.innerHTML = zamger_coolbox_origvalue;
		zamger_coolbox_origcaller=false;
	}
	</script>
	<?
}

// Naziv predmeta, akademska godina
$q21 = db_query("select naziv from predmet where id=$predmet");
$q22 = db_query("select naziv from akademska_godina where id=$ag");

?>
	<p>&nbsp;</p>
	<h3><?=$naziv?>, <?=$finidatum?></h3>
	<p><?=db_result($q21,0,0)?> <?=db_result($q22,0,0)?></p>

<?

print ajah_box();

$imeprezime = $brindexa = array();

$qtermini = db_query("SELECT d.id,UNIX_TIMESTAMP(d.datum_vrijeme)
				     FROM dogadjaj d
					 INNER JOIN ispit i ON i.id = d.opcije
					 WHERE i.id=$ispit
					 ORDER BY d.datum_vrijeme
					");

$broj_termina =0;
if(isset($_REQUEST['termin'])){
		$termin_id_from_url = intval($_REQUEST['termin']);
	?>
		<p><a href="?sta=izvjestaj/termini_ispita&termin=<?=$termin_id_from_url?>">Refresh</a></p>
	<?
	}
	else{
	?>
		<p><a href="?sta=izvjestaj/termini_ispita&ispit=<?=$ispit;?>">Refresh</a></p>
	<?
	}
while ($rtermini = db_fetch_row($qtermini)) {
	
	$broj_termina ++;
	$id_termina = $rtermini[0];
	
	if(isset($_REQUEST['termin'])){
		$termin_id_from_url = intval($_REQUEST['termin']);
		if($termin_id_from_url!=$id_termina)  continue;
	}
	
	$datum_termina= date("d. m. Y. ( H:i )",$rtermini[1]);
	if(isset($_REQUEST['ispit'])) $ispit = intval($_REQUEST['ispit']);
	else{
		$q8 = db_query("select opcije from dogadjaj where id=$termin_id_url");
		$ispit = db_result($q8,0,0);
	}
	print "Termin $broj_termina : <h4 style=\"display:inline\"> $datum_termina</h4><br></br>";
	$q10 = db_query("select o.id, o.prezime, o.ime, o.brindexa, a.login 
					from osoba as o, student_predmet as sp, ponudakursa as pk, dogadjaj_osoba dos, dogadjaj d, ispit i, auth a
					where 
						sp.predmet=pk.id 
						and sp.student=o.id
						and dos.osoba=o.id
						and dos.dogadjaj=d.id
						and d.opcije = i.id
						and pk.predmet=$predmet 
						and pk.akademska_godina=$ag
						and i.id=$ispit
						and d.id = $id_termina
						and a.id=o.id
						");
	if (db_num_rows($q10)<1) {
		print "<p>------------------------------------------------------</p>";
		print "<p>Nijedan student nije prijavljen na ovaj termin.</p>";
		print "<p>------------------------------------------------------</p>";
		continue;
	}
	
	while ($r10 = db_fetch_row($q10)) {
		$imeprezime[$r10[0]] = "$r10[1] $r10[2]"; 
		if (param('logini')) $logini[$r10[0]] = $r10[4];
		$brindexa[$r10[0]] = "$r10[3]";
	}
	uasort($imeprezime,"bssort"); // bssort - bosanski jezik
	
	// Ima li grupa na predmetu?
	$q27 = db_query("SELECT count(*) FROM labgrupa WHERE predmet=$predmet AND akademska_godina=$ag AND virtualna=0");
	if (db_result($q27,0,0)>0)
		$treba_grupe = true;
	else
		$treba_grupe = false;
	
	$broj_ispita=0;
	$ispit_zaglavlje="";
	$oldkomponenta=0;

	$ispit_id_array = array();
	
	$q30 = db_query("select i.id, UNIX_TIMESTAMP(i.datum), ap.id, ap.kratki_naziv, ap.aktivnost, ap.bodova, ap.prolaz, ap.opcije from ispit as i, aktivnost_predmet as ap where i.predmet=$predmet and i.akademska_godina=$ag and i.komponenta=ap.id order by i.datum, i.komponenta");
	$imaintegralni=0;
	while ($r30 = db_fetch_row($q30)) {
		$komponenta = $r30[2];
		$imeispita = $r30[3];
		$tipkomponente = $r30[4];	

		$ispit_zaglavlje .= "<td align=\"center\">$imeispita<br/> ".date("d.m.",$r30[1])."</td>\n";
		$broj_ispita++;
	
		$ispit_id_array[] = $r30[0];
		$ispit_komponenta[$r30[0]] = $r30[2];
	
		// Pripremamo podatke o komponentama
		$komponenta_tip[$r30[2]] = $r30[4];
		$komponenta_maxb[$r30[2]] = $r30[5];
		$komponenta_prolaz[$r30[2]] = $r30[6];
		$komponenta_opcija[$r30[2]] = "$r30[7]";
	}
	
	// Racunamo koliko je bilo moguce ostvariti bodova na predmetu (radi racunanja procenta)
	$mogucih_bodova=0; 
	foreach($komponenta_maxb as $kid => $kmb) 
		if ($komponenta_tip[$kid] != 2 || // 2 = integralni ne racunamo
			($imaintegralni == 1 && $broj_ispita < 2)) // osim ako je to jedini ispit
			$mogucih_bodova += $kmb;
	// Ostale komponente cemo sabrati nesto kasnije...
	
	// Za slucaj da prof odrzi integralni bez parcijalnih
	if ($imaintegralni==1 && $broj_ispita < 2) {
		// $razvdoji_ispite=1; goto // Zaglavlje tabele ispita
		// no php ne podržava goto :(
		$broj_ispita=2;
		// Ovo ce i dalje biti deformisano, ali nesto manje deformisano nego ranije
	}
	
	
	
	// SPISAK KOMPONENTI KOJE NISU ISPITI
	
	$ostale_komponente = array();
	
	// 8 = ispit
	$q40 = db_query("select ap.id, ap.kratki_naziv, ap.aktivnost, ap.bodova from aktivnost_predmet ap, aktivnost_agp as aagp where aagp.predmet=$predmet and aagp.aktivnost_predmet=ap.id and (ap.aktivnost!=8 or ap.aktivnost is null) and aagp.akademska_godina=$ag and ap.bodova>0");
	while ($r40 = db_fetch_row($q40)) {
		$mogucih_bodova += $r40[3];
	
		$ostale_komponente[$r40[0]]=$r40[1];
	}
	
	
	$zaglavlje1=$zaglavlje2=""; // Dva reda zaglavlja tabele


	// Ostale komponente
	foreach ($ostale_komponente as $kid => $knaziv)
		$zaglavlje1 .= "<td rowspan=\"2\" align=\"center\">$knaziv</td>\n";
	
	
	
		?>
	<table border="1" cellspacing="0" cellpadding="2">
		<tr><td rowspan="2" align="center">R.br.</td>
			<td rowspan="2" align="center">Prezime i ime</td>
			<td rowspan="2" align="center">Br. indexa</td>
			<? if ($treba_grupe) { ?><td rowspan="2" align="center">Grupa</td><? } ?>
			<?=$zaglavlje1?>
			<td align="center" <? if ($broj_ispita==0) { ?> rowspan="2" <? } else { ?> colspan="<?=$broj_ispita?>" <? } ?>>Ispiti</td>
			<td rowspan="2" align="center"><b>UKUPNO</b></td>
			<td rowspan="2" align="center">Konačna<br/>ocjena</td>
		</tr>
		<tr>
			<?=$zaglavlje2?>
			<?=$ispit_zaglavlje?>
		</tr>
		<?
	
	
	// ------ SPISAK STUDENATA ------

	$idovi = array_keys($imeprezime);
	
	// Petlja za ispis studenata
	$redni_broj=0;
	$zebra_bg = $zebra_siva = "#f0f0f0";
	$zebra_bijela = "#ffffff";
	foreach ($imeprezime as $stud_id => $stud_imepr) {
		if (!in_array($stud_id, $idovi)) continue;
		unset ($imeprezime[$stud_id]); // Vise se nece javljati

		if ($zebra_bg == $zebra_siva) $zebra_bg=$zebra_bijela; else $zebra_bg=$zebra_siva;
		if (param('logini')) $stud_imepr = $logini[$stud_id];
		
		$redni_broj++;
			?>
		<tr bgcolor="<?=$zebra_bg?>">
			<td><?=$redni_broj?>.</td>
			<td><?=$stud_imepr?></td>
			<td><?=$brindexa[$stud_id]?></td>
			<?

		if ($treba_grupe) {
			$q220 = db_query("SELECT l.naziv FROM labgrupa as l, student_labgrupa as sl WHERE l.predmet=$predmet AND l.akademska_godina=$ag AND l.virtualna=0 AND l.id=sl.labgrupa AND sl.student=$stud_id");
			if (db_num_rows($q220)==0) 
				$grupa = "&nbsp;";
			else
				$grupa = db_result($q220,0,0);
			?>
			<td><?=$grupa?></td>
			<?
		}
	
		$ispis="";
		$bodova=0; // Zbir bodova koje je student ostvario

		// OSTALE KOMPONENTE

		foreach ($ostale_komponente as $kid => $knaziv) {
			$q230 = db_query("select kb.bodovi from komponentebodovi as kb, ponudakursa as pk where kb.student=$stud_id and kb.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag and kb.komponenta=$kid");
			$obodova=0; 
			if (db_num_rows($q230)>0) {
				$obodova = db_result($q230,0,0);
			}
			$ispis .= "<td>$obodova</td>";
			$bodova += $obodova;
		}
	
	
	
		// ISPITI

		if ($broj_ispita==0) {
			$ispis .= "<td>&nbsp;</td>";
		}
		$komponente=$kmax=$kispis=array();
		foreach ($ispit_id_array as $ispit) {
			$k = $ispit_komponenta[$ispit];
	
			$q230 = db_query("select ocjena from ispitocjene where ispit=$ispit and student=$stud_id");
			if (db_num_rows($q230)>0) {
				$ocjena = db_result($q230,0,0);
				$ispis .= "<td align=\"center\" id=\"ispit-$stud_id-$ispit\" ondblclick=\"coolboxopen(this)\">$ocjena</td>\n";
				if (!in_array($k,$komponente) || $ocjena>$kmax[$k]) {
					$kmax[$k]=$ocjena;
					$kispis[$k] = "<td align=\"center\" id=\"ispit-$stud_id-$ispit\" ondblclick=\"coolboxopen(this)\">$ocjena</td>\n";
				}
			} else {
				$ispis .= "<td align=\"center\" id=\"ispit-$stud_id-$ispit\" ondblclick=\"coolboxopen(this)\">/</td>\n";
				if ($kispis[$k] == "") $kispis[$k] = "<td align=\"center\" id=\"ispit-$stud_id-$ispit\" ondblclick=\"coolboxopen(this)\">/</td>\n";
			}
			if (!in_array($k,$komponente)) $komponente[]=$k;
		}
		
		// Prvo trazimo integralne ispite
		foreach ($komponente as $k) {
			if ($komponenta_tip[$k] == 2) {
				// Koje parcijalne ispite obuhvata integralni
				$dijelovi = explode("+", $komponenta_opcija[$k]);
	
				// Racunamo zbir
				$zbir=0;
				$pao=0;
				foreach ($dijelovi as $dio) {
					$zbir += $kmax[$dio];
					if ($kmax[$dio]<$komponenta_prolaz[$dio]) $pao=1;
				}
	
				// Eliminisemo parcijalne obuhvacene integralnim
				if ($kmax[$k]>$zbir || $pao==1 && $kmax[$k]>=$komponenta_prolaz[$k]) {
					$bodova += $kmax[$k];
					foreach ($dijelovi as $dio) {
						$kmax[$dio]=0;
						$kispis[$dio]="";
					}
					$kispis[$k] = "<td align=\"center\" colspan=\"".count($dijelovi)."\">".$kmax[$k]."</td>\n";
				}
				else $kispis[$k]="";
			}
		}
	
		// Sabiremo preostale parcijalne ispite na sumu bodova
		foreach ($komponente as $k) {
			if ($komponenta_tip[$k] != 2) {
				$bodova += $kmax[$k];
			}
		}


		// STATISTIKE
		$topscore[$stud_id]=$bodova;

		print $ispis;

		print "<td align=\"center\">$bodova (".procenat($bodova,$mogucih_bodova).")</td>\n";


		// Konacna ocjena
		$q508 = db_query("select ocjena from konacna_ocjena where student=$stud_id and predmet=$predmet and akademska_godina=$ag");
		if (db_num_rows($q508)>0) {
			print "<td id=\"ko-$stud_id-$predmet-$ag\" ondblclick=\"coolboxopen(this)\">".db_result($q508,0,0)."</td>\n";
		} else {
			print "<td id=\"ko-$stud_id-$predmet-$ag\" ondblclick=\"coolboxopen(this)\">/</td>\n";
		}

		print "</tr>\n";
	}
	print "</table><p>&nbsp;</p>";
}
?>

<?}?>
