<?
global $osoba;
$osoba_URL = $_GET['osoba'];
$pristup = $osoba == $osoba_URL;
if(!$pristup)
	//myerror("Nemate privilegiju pristupa plate tom korisniku. Molimo vas da za pregled plate kliknete na meni \"Plata\" u vasem profilu.");
	echo "<h2>Nemate privilegiju pristupa plate tom korisniku.<br> Molimo vas da za pregled plate kliknete na meni \"Plata\" u vasem profilu.</h2><br>";
else{
$subakcija = $_GET['subakcija'];
//$subakcija = $_POST['subakcija'];
if($subakcija == null or $subakcija == ""){

$ime_prezime = myquery("SELECT ime, prezime FROM osoba WHERE id=$osoba");
$ime = mysql_result($ime_prezime,0,0);
$prezime = mysql_result($ime_prezime,0,1);
echo "<h2>Detalji o plati korisnika $ime $prezime</h2><br>";


//stari metod(ne valja) $fk_naucnonastavno_zvanje = intval(mysql_result(myquery("SELECT fk_naucnonastavno_zvanje FROM izbor WHERE fk_osoba = $osoba"), 0, 0));
$fk_naucnonastavno_zvanje = intval(mysql_result(myquery("SELECT zvanje FROM izbor WHERE osoba = $osoba and CONCAT(year(datum_izbora),'/',year(datum_isteka)) = (SELECT naziv FROM akademska_godina WHERE aktuelna = 1)"), 0, 0));
$fk_naucnonastavno_zvanje2 = intval(mysql_result(myquery("SELECT zvanje FROM izbor WHERE osoba = $osoba ORDER BY year(datum_isteka) DESC"), 0, 0));
	if($fk_naucnonastavno_zvanje == 1 || $fk_naucnonastavno_zvanje == 2 || $fk_naucnonastavno_zvanje == 6){ //u slucaju redovnog profesora, vandrednog profesora ili profesora emeritusa
		/*
		stari nacin, ne koristi se vise
		$broj_vjezbi_tutorijala = intval(mysql_result(myquery("SELECT  SUM( k.sati_vjezbi ) + SUM( k.sati_tutorijala ) AS ukupno_casova
			FROM predmet AS p
			JOIN labgrupa AS l ON p.id = l.predmet
			JOIN angazman AS a ON p.id = a.predmet
			JOIN osoba AS o ON o.id = a.osoba
			JOIN kolicina_predavanja as k ON k.osoba_id = o.id
			JOIN akademska_godina AS ag ON ag.id = a.akademska_godina
			WHERE ag.aktuelna = 1 AND l.id = k.labgrupa_id AND o.id = $osoba
			GROUP BY ime"), 0 , 0));
		$broj_predmeta = intval(mysql_result(myquery("SELECT COUNT( p.id ) 
			FROM osoba AS o
			JOIN predmet AS p
			JOIN angazman AS a ON a.osoba = o.id AND a.predmet = p.id
			WHERE o.id = $osoba
			GROUP BY a.osoba"), 0 ,0));
		*/
		$broj_predavanja = myquery("SELECT  p.id, l.naziv, tip AS tip_grupe
			FROM predmet AS p
			JOIN labgrupa AS l ON p.id = l.predmet
			JOIN angazman AS a ON p.id = a.predmet
			JOIN osoba AS o ON o.id = a.osoba
			JOIN nastavnik_labgrupa as nl ON nl.osoba_id = o.id
			JOIN akademska_godina AS ag ON ag.id = a.akademska_godina
			WHERE ag.aktuelna = 1 AND l.id = nl.labgrupa_id AND o.id = $osoba_URL");//, 0 , 0));
		$broj_predmeta = 0;
		$broj_vjezbi_tutorijala = 0;
		while($row = mysql_fetch_row($broj_predavanja)){
			$pred_id = $row[0];
			$lab_ime = $row[1];
			$tip = $row[2];
			switch($tip){
				case "predavanja":
					$broj_predmeta = $broj_predmeta + 1;//intval(mysql_result(myquery("SELECT sati_predavanja FROM predmet WHERE id = $pred_id"),0,0));
					break;
				case "vjezbe":
					$broj_vjezbi_tutorijala = $broj_vjezbi_tutorijala + intval(mysql_result(myquery("SELECT sati_vjezbi FROM predmet WHERE id = $pred_id"),0,0));
					break;
				case "tutorijali":
					$broj_vjezbi_tutorijala = $broj_vjezbi_tutorijala + intval(mysql_result(myquery("SELECT sati_tutorijala FROM predmet WHERE id = $pred_id"),0,0));
					break;
				case "vjezbe+tutorijali":
					$broj_vjezbi_tutorijala = $broj_vjezbi_tutorijala + 2 * intval(mysql_result(myquery("SELECT sati_tutorijala FROM predmet WHERE id = $pred_id"),0,0));
					break;
			}
		}
		$predmet_norma = $broj_predmeta / 3;
		$vjezbe_tutorijali_norma = $broj_vjezbi_tutorijala / 12;
		$norma = $predmet_norma + $vjezbe_tutorijali_norma;
	}
	else if($fk_naucnonastavno_zvanje == 4 || $fk_naucnonastavno_zvanje == 5){ //u slucaju asistenta ili viseg asistenta
		/*
		stari nacin, ne koristi se vise
		$broj_vjezbi_tutorijala = intval(mysql_result(myquery("SELECT  SUM( k.sati_vjezbi ) + SUM( k.sati_tutorijala ) AS ukupno_casova
			FROM predmet AS p
			JOIN labgrupa AS l ON p.id = l.predmet
			JOIN angazman AS a ON p.id = a.predmet
			JOIN osoba AS o ON o.id = a.osoba
			JOIN kolicina_predavanja as k ON k.osoba_id = o.id
			JOIN akademska_godina AS ag ON ag.id = a.akademska_godina
			WHERE ag.aktuelna = 1 AND l.id = k.labgrupa_id AND o.id = $osoba
			GROUP BY ime"), 0 , 0));
		$broj_predmeta = intval(mysql_result(myquery("SELECT COUNT( p.id ) 
			FROM osoba AS o
			JOIN predmet AS p
			JOIN angazman AS a ON a.osoba = o.id AND a.predmet = p.id
			WHERE o.id = $osoba
			GROUP BY a.osoba"), 0 ,0));
		*/
		$broj_predavanja = myquery("SELECT  p.id, l.naziv, tip AS tip_grupe
			FROM predmet AS p
			JOIN labgrupa AS l ON p.id = l.predmet
			JOIN angazman AS a ON p.id = a.predmet
			JOIN osoba AS o ON o.id = a.osoba
			JOIN nastavnik_labgrupa as nl ON nl.osoba_id = o.id
			JOIN akademska_godina AS ag ON ag.id = a.akademska_godina
			WHERE ag.aktuelna = 1 AND l.id = nl.labgrupa_id AND o.id = $osoba_URL");//, 0 , 0));
		$broj_predmeta = 0;
		$broj_vjezbi_tutorijala = 0;
		while($row = mysql_fetch_row($broj_predavanja)){
			$pred_id = $row[0];
			$lab_ime = $row[1];
			$tip = $row[2];
			switch($tip){
				case "predavanja":
					$broj_predmeta = $broj_predmeta + intval(mysql_result(myquery("SELECT sati_predavanja FROM predmet WHERE id = $pred_id"),0,0));
					break;
				case "vjezbe":
					$broj_vjezbi_tutorijala = $broj_vjezbi_tutorijala + intval(mysql_result(myquery("SELECT sati_vjezbi FROM predmet WHERE id = $pred_id"),0,0));
					break;
				case "tutorijali":
					$broj_vjezbi_tutorijala = $broj_vjezbi_tutorijala + intval(mysql_result(myquery("SELECT sati_tutorijala FROM predmet WHERE id = $pred_id"),0,0));
					break;
				case "vjezbe+tutorijali":
					$broj_vjezbi_tutorijala = $broj_vjezbi_tutorijala + 2 * intval(mysql_result(myquery("SELECT sati_tutorijala FROM predmet WHERE id = $pred_id"),0,0));
					break;
			}
		}
		$predavanja_norma = $broj_predmeta / 12;
		$vjezbe_tutorijali_norma = $broj_vjezbi_tutorijala / 12;
		$norma = $predavanja_norma + $vjezbe_tutorijali_norma;
	}

switch ($fk_naucnonastavno_zvanje){
	case 1:
		$koeficijent_slozenosti = 16;
		break;
	case 2:
		$koeficijent_slozenosti = 14;
		break;
	case 3:
		$koeficijent_slozenosti = 12;
		break;
	case 4:
		$koeficijent_slozenosti = 10;
		break;
	case 5:
		$koeficijent_slozenosti = 8.5;
		break;
	case 6:
		$koeficijent_slozenosti = 16;
		break;
}

$godine_staza = intval(mysql_result(myquery("SELECT count(akademska_godina) FROM angazman WHERE osoba=$osoba  GROUP BY osoba"), 0, 0));
//echo "godine_staza: $godine_staza";
	
$default_vrijednosti=myquery("SELECT * FROM defaultne_vrijednosti_plate"); //defaultne vrijednosti varijabli potrebnih za racunanje plate, kao sto su porezi, broj radnih dana, itd.

	$koeficijent_opterecenja = mysql_result($default_vrijednosti, 0, 0);//0.9;       //unosi se
	$koeficijent_broja_studenata = mysql_result($default_vrijednosti, 0, 1);//0.1;       //unosi se
	$koeficijent_minulog_rada = 1 + 0.006 * $godine_staza;
	$vrijednost_boda = mysql_result($default_vrijednosti, 0, 2);//150;       //unosi se
	$koeficijent_place = $koeficijent_slozenosti * $norma * ($koeficijent_opterecenja + koeficijent_broja_studenata) * $koeficijent_minulog_rada;
	$minuli_rad = ($godine_staza/2) * 0.01; //izracunati (godine_staza/2) * 0.01
	$bruto_placa = (1 + $minuli_rad) * $koeficijent_place * $vrijednost_boda;
	$penziono_i_invalidno_osiguranje = mysql_result($default_vrijednosti, 0, 3) * 100;//0.17;       //unosi se, ostaju default vrijednosti
	$zdravstveno_osiguranje = mysql_result($default_vrijednosti, 0, 4) * 100;//0.12;       //unosi se, ostaju default vrijednosti
	$zaposljavanje_na_teret_osiguranja = mysql_result($default_vrijednosti, 0, 5) * 100;//0.015;       //unosi se, ostaju default vrijednosti
	$neto_placa_sa_porezom = (1 - $penziono_i_invalidno_osiguranje - $zdravstveno_osiguranje - $zaposljavanje_na_teret_osiguranja) * $bruto_placa;
	$porezna_olaksica = 0;       //unosi se
	$porez_na_placu = mysql_result($default_vrijednosti, 0, 6);//0.1;
	$POR = ($neto_placa_sa_porezom - $porezna_olaksica) * $porez_na_placu;
	$NBP = $neto_placa_sa_porezom - $POR;
	$broj_radnih_dana_u_mjesecu = mysql_result($default_vrijednosti, 0, 7);//22;       //unosi se
	$dnevni_topli_obrok = mysql_result($default_vrijednosti, 0, 8);//16;       //unosi se
	$topli_obrok = $broj_radnih_dana_u_mjesecu * $dnevni_topli_obrok;
	$ukupno_za_isplatu = $NBP + $topli_obrok;
	
?>
	<form action="?sta=common/profil&akcija=plata&osoba=<? echo $osoba; ?>&subakcija=izracunaj" method="POST">
		<table>
			<tr>
				<td><font size="3">Koeficijent opterecenja:</font></td>
				<td><font size="3"><input type="text" name="koeficijent_opterecenja" value="<? echo $koeficijent_opterecenja; ?>"></font></td>
			</tr>
			<tr>
				<td><font size="3">Koeficijent broja studenata:</font></td>
				<td><font size="3"><input type="text" name="koeficijent_broja_studenata" value="<? echo $koeficijent_broja_studenata; ?>"></font></td>
			</tr>
			<tr>
				<td><font size="3">Vrijednost boda</font></td>
				<td><font size="3"><input type="text" name="vrijednost_boda" value="<? echo $vrijednost_boda; ?>"></font></td>
			</tr>
			<tr>
				<td><font size="3">Penziono i invalidno osiguranje (unijeti u procentima)</font></td>
				<td><font size="3"><input type="text" name="penziono_i_invalidno_osiguranje" value="<? echo $penziono_i_invalidno_osiguranje; ?>"></font></td>
			</tr>
			<tr>
				<td><font size="3">Zdravstveno osiguranje (unijeti u procentima)</font></td>
				<td><font size="3"><input type="text" name="zdravstveno_osiguranje" value="<? echo $zdravstveno_osiguranje; ?>"></font></td>
			</tr>
			<tr>
				<td><font size="3">Zaposljavanje na teret osiguranja (unijeti u procentima)</font></td>
				<td><font size="3"><input type="text" name="zaposljavanje_na_teret_osiguranja" value="<? echo $zaposljavanje_na_teret_osiguranja; ?>"></font></td>
			</tr>
			<tr>
				<td><font size="3">Porezna olaksica (u KM)</font></td>
				<td><font size="3"><input type="text" name="porezna_olaksica"></font></td>
			</tr>
			<tr>
				<td><font size="3">Broj radnih dana u mjesecu</font></td>
				<td><font size="3"><input type="text" name="broj_radnih_dana_u_mjesecu" value="<? echo $broj_radnih_dana_u_mjesecu; ?>"></font></td>
			</tr>
			<tr>
				<td><font size="3">Dnevni topli obrok (u KM)</font></td>
				<td><font size="3"><input type="text" name="dnevni_topli_obrok" value="<? echo $dnevni_topli_obrok; ?>"></font></td>
			</tr>
		</table>
		<input type="hidden" name="norma" value="<? echo $norma; ?>">
		<input type="hidden" name="koeficijent_slozenosti" value="<? echo $koeficijent_slozenosti; ?>">
		<input type="hidden" name="koeficijent_minulog_rada" value="<? echo $koeficijent_minulog_rada; ?>">
		<input type="hidden" name="godine_staza" value="<? echo $godine_staza; ?>">
		<input type="hidden" name="minuli_rad" value="<? echo $minuli_rad; ?>">
		<input type="submit" name="subakcija" value="Izracunaj">
	</form>
<?
	}
	else if($subakcija == "izracunaj"){
	$default_vrijednosti=myquery("SELECT * FROM defaultne_vrijednosti_plate"); //defaultne vrijednosti varijabli potrebnih za racunanje plate, kao sto su porezi, broj radnih dana, itd.
		$ime_prezime = myquery("SELECT ime, prezime FROM osoba WHERE id=$osoba");
		$ime = mysql_result($ime_prezime,0,0);
		$prezime = mysql_result($ime_prezime,0,1);
		echo "<h2>Detalji o plati korisnika $ime $prezime</h2><br>";
		
		$norma =  round($_POST['norma'], 2);
		$koeficijent_slozenosti = $_POST['koeficijent_slozenosti'];
		$koeficijent_opterecenja = $_POST['koeficijent_opterecenja'];
		$koeficijent_broja_studenata = $_POST['koeficijent_broja_studenata'];
		$koeficijent_minulog_rada = $_POST['koeficijent_minulog_rada'];
		$vrijednost_boda = $_POST['vrijednost_boda'];
		if($vrijednost_boda == null or $vrijednost_boda == "")
			$vrijednost_boda = 0;
		$koeficijent_place = round($koeficijent_slozenosti * $norma * ($koeficijent_opterecenja + koeficijent_broja_studenata) * $koeficijent_minulog_rada, 2);
		$minuli_rad = $_POST['minuli_rad'];
		if((($minuli_rad / 0.005) % 2) == 1)
			$minuli_rad = $minuli_rad + 0.005;
		$bruto_placa = round((1 + $minuli_rad) * $koeficijent_place * $vrijednost_boda, 2);
		$penziono_i_invalidno_osiguranje = $_POST['penziono_i_invalidno_osiguranje']/100;
		$penziono_i_invalidno_osiguranje_num = round($bruto_placa * $penziono_i_invalidno_osiguranje, 2);
		$zdravstveno_osiguranje = $_POST['zdravstveno_osiguranje']/100;
		$zdravstveno_osiguranje_num = round($bruto_placa * $zdravstveno_osiguranje, 2);
		$zaposljavanje_na_teret_osiguranja = $_POST['zaposljavanje_na_teret_osiguranja']/100;
		$zaposljavanje_na_teret_osiguranja_num = round($bruto_placa * $zaposljavanje_na_teret_osiguranja, 2);
		$neto_placa_sa_porezom = round((1 - $penziono_i_invalidno_osiguranje - $zdravstveno_osiguranje - $zaposljavanje_na_teret_osiguranja) * $bruto_placa, 3);
		$porezna_olaksica = $_POST['porezna_olaksica'];
		if($porezna_olaksica == null or $porezna_olaksica == "")
			$porezna_olaksica = 0;
		$porez_na_placu = mysql_result($default_vrijednosti, 0, 6);
		$POR = round(($neto_placa_sa_porezom - $porezna_olaksica) * $porez_na_placu, 3);
		$NBP = round($neto_placa_sa_porezom - $POR, 3);
		$broj_radnih_dana_u_mjesecu = $_POST['broj_radnih_dana_u_mjesecu'];
		$dnevni_topli_obrok = $_POST['dnevni_topli_obrok'];
		$topli_obrok = $broj_radnih_dana_u_mjesecu * $dnevni_topli_obrok;
		$ukupno_za_isplatu = $NBP + $topli_obrok;
		//echo "<table cellpadding=\"0\" cellspacing=\"10\" border=\"0\">\n\t\t";
		/*
		echo "\n\t<table cellspacing=\"2\" border=\"1\"\>\n\t\t";
		echo "<tr><td align=\"center\"><font size=\"3\">Koeficijent kvaliteta:</td><td>$norma</td></font></tr><br>\n\t\t";
		echo "<tr><td align=\"center\"><font size=\"3\">Koeficijent slozenosti:</td><td>$koeficijent_slozenosti</td></font></tr><br>\n\t\t";
		echo "<tr><td align=\"center\"><font size=\"3\">Koeficijent opterecenja:</td><td>$koeficijent_opterecenja</td></font></tr><br>\n\t\t";
		echo "<tr><td align=\"center\"><font size=\"3\">Koeficijent broja studenata:</td><td>$koeficijent_broja_studenata</td></font></tr><br>\n\t\t";
		echo "<tr><td align=\"center\"><font size=\"3\">Koeficijent minulog rada:</td><td>$koeficijent_minulog_rada</td></font></tr><br>\n\t\t";
		echo "<tr><td align=\"center\"><font size=\"3\">Vrijednost boda:</td><td>$vrijednost_boda KM</td></font></tr><br>\n\t\t";
		echo "<tr><td align=\"center\"><font size=\"3\">Koeficijent place:</td><td>$koeficijent_place</td></font></tr><br>\n\t\t";
		echo "<tr><td align=\"center\"><font size=\"3\">Minuli rad:</td><td>$minuli_rad</td></font></tr><br>\n\t\t";
		echo "<tr><td align=\"center\"><font size=\"3\">Bruto placa:</td><td>$bruto_placa KM</td></font></tr><br>\n\t\t";
		echo "<tr><td align=\"center\"><font size=\"3\">Penziono i invalidno osiguranje:</td><td>$penziono_i_invalidno_osiguranje_num KM</td></font></tr><br>\n\t\t";
		echo "<tr><td align=\"center\"><font size=\"3\">Zdravstveno osiguranje:</td><td>$zdravstveno_osiguranje_num KM</td></font></tr><br>\n\t\t";
		echo "<tr><td align=\"center\"><font size=\"3\">Zaposljavanje na teret osiguranja:</td><td>$zaposljavanje_na_teret_osiguranja_num KM</td></font></tr><br>\n\t\t";
		echo "<tr><td align=\"center\"><font size=\"3\">Neto placa sa porezom:</td><td>$neto_placa_sa_porezom KM</td></font></tr><br>\n\t\t";
		echo "<tr><td align=\"center\"><font size=\"3\">Porezna olaksica:</td><td>$porezna_olaksica KM</td></font></tr><br>\n\t\t";
		echo "<tr><td align=\"center\"><font size=\"3\">Porez na placu:</td><td>$porez_na_placu KM</td></font></tr><br>\n\t\t";
		echo "<tr><td align=\"center\"><font size=\"3\">POR:</td><td>$POR KM</td></font></tr><br>\n\t\t";
		echo "<tr><td align=\"center\"><font size=\"3\">Broj radnih dana u mjesecu:</td><td>$broj_radnih_dana_u_mjesecu</td></font></tr><br>\n\t\t";
		echo "<tr><td align=\"center\"><font size=\"3\">Dnevni topli obrok:</td><td>$dnevni_topli_obrok KM</td></font></tr><br>\n\t\t";
		echo "<tr><td align=\"center\"><font size=\"5\">Topli obrok:</td><td>$topli_obrok KM</td></font></tr><br>\n\t\t";
		echo "<tr><td align=\"center\"><font size=\"5\">NBP:</td><td>$NBP KM</td></font></tr><br>\n\t\t";
		echo "<tr><td align=\"center\"><font size=\"5\">Ukupno za isplatu:</td><td>$ukupno_za_isplatu KM</td></font></tr><br>\n\t";
		echo "</table>\n";
		*/
		?>
		
		
		<form action="?sta=common/profil&akcija=plata&osoba=<? echo $osoba; ?>" method="POST">
			<table>
				<tr>
					<td><font size="3">Koeficijent kvaliteta:</font></td>
					<td><font size="3"><input type="text" name="koeficijent_opterecenja" value="<? echo $norma;?>" disabled="disabled"></font></td>
				</tr>
				<tr>
					<td><font size="3">Koeficijent slozenosti:</font></td>
					<td><font size="3"><input type="text" name="koeficijent_opterecenja" value="<? echo $koeficijent_slozenosti;?>" disabled="disabled"></font></td>
				</tr>
				<tr>
					<td><font size="3">Koeficijent opterecenja:</font></td>
					<td><font size="3"><input type="text" name="koeficijent_opterecenja" value="<? echo $koeficijent_opterecenja;?>" disabled="disabled"></font></td>
				</tr>
				<tr>
					<td><font size="3">Koeficijent broja studenata:</font></td>
					<td><font size="3"><input type="text" name="koeficijent_broja_studenata" value="<? echo $koeficijent_broja_studenata;?>" disabled="disabled"></font></td>
				</tr>
				<tr>
					<td><font size="3">Koeficijent minulog rada:</font></td>
					<td><font size="3"><input type="text" name="koeficijent_minulog_rada" value="<? echo $koeficijent_minulog_rada;?>" disabled="disabled"></font></td>
				</tr>
				<tr>
					<td><font size="3">Vrijednost boda</font></td>
					<td><font size="3"><input type="text" name="vrijednost_boda" value="<? echo "$vrijednost_boda KM";?>" disabled="disabled"></font></td>
				</tr>
				<tr>
					<td><font size="3">Koeficijent place:</font></td>
					<td><font size="3"><input type="text" name="koeficijent_place" value="<? echo $koeficijent_place;?>" disabled="disabled"></font></td>
				</tr>
				<tr>
					<td><font size="3">Minuli rad:</font></td>
					<td><font size="3"><input type="text" name="minuli_rad" value="<? echo $minuli_rad;?>" disabled="disabled"></font></td>
				</tr>
				<tr>
					<td><font size="3">Bruto placa:</font></td>
					<td><font size="3"><input type="text" name="bruto_placa" value="<? echo "$bruto_placa KM";?>" disabled="disabled"></font></td>
				</tr>
				<tr>
					<td><font size="3">Penziono i invalidno osiguranje:</font></td>
					<td><font size="3"><input type="text" name="penziono_i_invalidno_osiguranje_num" value="<? echo "$penziono_i_invalidno_osiguranje_num KM";?>" disabled="disabled"></font></td>
				</tr>
				<tr>
					<td><font size="3">Zdravstveno osiguranje:</font></td>
					<td><font size="3"><input type="text" name="zdravstveno_osiguranje_num" value="<? echo "$zdravstveno_osiguranje_num KM";?>" disabled="disabled"></font></td>
				</tr>
				<tr>
					<td><font size="3">Zaposljavanje na teret osiguranja:</font></td>
					<td><font size="3"><input type="text" name="zaposljavanje_na_teret_osiguranja_num" value="<? echo "$zaposljavanje_na_teret_osiguranja_num KM";?>" disabled="disabled"></font></td>
				</tr>
				<tr>
					<td><font size="3">Neto placa sa porezom:</font></td>
					<td><font size="3"><input type="text" name="neto_placa_sa_porezom" value="<? echo "$neto_placa_sa_porezom KM";?>" disabled="disabled"></font></td>
				</tr>
				<tr>
					<td><font size="3">Porezna olaksica (u KM):</font></td>
					<td><font size="3"><input type="text" name="porezna_olaksica" value="<? echo "$porezna_olaksica KM";?>" disabled="disabled"></font></td>
				</tr>
				<tr>
					<td><font size="3">Porez na placu:</font></td>
					<td><font size="3"><input type="text" name="porez_na_placu" value="<? $temp=$porez_na_placu*100; echo "$temp %";?>" disabled="disabled"></font></td>
				</tr>
				<tr>
					<td><font size="3">POR:</font></td>
					<td><font size="3"><input type="text" name="POR" value="<? echo "$POR KM";?>" disabled="disabled"></font></td>
				</tr>
				<tr>
					<td><font size="3">NBP:</font></td>
					<td><font size="3"><input type="text" name="NBP" value="<? echo "$NBP KM";?>" disabled="disabled"></font></td>
				</tr>
				<tr>
					<td><font size="3">Broj radnih dana u mjesecu:</font></td>
					<td><font size="3"><input type="text" name="broj_radnih_dana_u_mjesecu" value="<? echo $broj_radnih_dana_u_mjesecu;?>" disabled="disabled"></font></td>
				</tr>
				<tr>
					<td><font size="3">Dnevni topli obrok (u KM):</font></td>
					<td><font size="3"><input type="text" name="dnevni_topli_obrok" value="<? echo "$dnevni_topli_obrok KM";?>" disabled="disabled"></font></td>
				</tr>
				<tr>
					<td><font size="4"><b>Topli obrok:</b></font></td>
					<td><font size="4"><input type="text" name="topli_obrok" value="<? echo "$topli_obrok KM";?>" disabled="disabled"></font></td>
				</tr>
				<tr>
					<td><font size="4"><b>NBP:</b></font></td>
					<td><font size="4"><input type="text" name="NBP" value="<? echo "$NBP KM";?>" disabled="disabled"></font></td>
				</tr>
				<tr>
					<td><font size="4" color="red"><b>Ukupno za isplatu:</b></font></td>
					<td><font size="4"><input type="text" name="ukupno_za_isplatu" value="<? echo "$ukupno_za_isplatu KM";?>" disabled="disabled"></font></td>
				</tr>
			</table>
			<input type="hidden" name="norma" value="<? echo $norma; ?>">
			<input type="hidden" name="koeficijent_slozenosti" value="<? echo $koeficijent_slozenosti; ?>">
			<input type="hidden" name="koeficijent_minulog_rada" value="<? echo $koeficijent_minulog_rada; ?>">
			<input type="hidden" name="godine_staza" value="<? echo $godine_staza; ?>">
			<input type="hidden" name="minuli_rad" value="<? echo $minuli_rad; ?>">
			<input type="submit" value="Nazad">
		</form>
		
		<?
	}
}
?>
