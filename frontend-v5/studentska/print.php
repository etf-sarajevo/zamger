<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
<head>
	<title>ETF Zamger print document</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="../css/raspored.css" rel="stylesheet" type="text/css" />
</head>

<?
require("../lib/libvedran.php");
//require("../lib/zamger.php");
require("../lib/config.php");
dbconnect2($conf_dbhost,$conf_dbuser,$conf_dbpass,$conf_dbdb);
##################################################################################
#
#										PRINTANJE STRANICE
#
##################################################################################
/**
*	Printanje predmeta po semestrima, grupa, te profesora.
*/

//Printanje stranice (izabir printera i ostala konfiguracija)
function printPage() {
?>
<script language="Javascript">
	<!--
	setTimeout("window.print()", 1 * 1000);
	//-->
</script>
<?
}

/**
*	Funkcija za ispis vremena na rasporedu (sidebar)
*/
function vrijemeIspis($vrijemePoc, $vrijemeKraj){
	$beg = array("1" => "09:00", "2" => "10:00", "3" => "11:00", "4" => "12:00", "5" => "13:00",
				"6" => "14:00", "7" => "15:00", "8" => "16:00", "9" => "17:00");
	$end = array("1" => "09:45", "2" => "10:45", "3" => "11:45", "4" => "12:45", "5" => "13:45",
				"6" => "14:45", "7" => "15:45", "8" => "16:45", "9" => "17:45");
			
	$ispis = $beg[$vrijemePoc]." - ".$end[$vrijemeKraj];
	return $ispis;
}

/**
*	Funkcija za ispis kompletnog rasporeda
*/
function printRaspored($id, $tip) {	

	// Selektuje podatke iz baze
	if ($rasSel = myquery("SELECT id, id, naziv, kratki_naziv, dan_u_sedmici, smijerR, godinaR, tip, vrijeme_pocetak, vrijeme_kraj, labgrupa, sala FROM raspored_stavka LEFT JOIN predmet ON raspored_stavka.predmet = predmet.id WHERE raspored = '".$id."' ORDER BY dan_u_sedmici ASC, vrijeme_pocetak ASC, id ASC")){
		
		// Printa dane
		echo '<div class="dan_header" style="width:50px">Dan/Sat</div>
				<div class="dan_header">Ponedjeljak</div>
				<div class="dan_header">Utorak</div>
				<div class="dan_header">Srijeda</div>
				<div class="dan_header">Cetvrtak</div>
				<div class="dan_header">Petak</div>
				<div class="razmak"></div>';

		// Printa satnicu
		echo '<div>';
		echo '<div style="float:left">';
		for ($i=9; $i<=17; $i++){
			echo '<div class="satnica">'.$i.':00</div>';
		}
		echo '</div>';		

		for($r=0; $r<9; $r++) {
			for($r2=0; $r2<4; $r2++) {
				echo '<div style = "float: left; border-right: 1px solid #E0E4F3; width: 129px; height: 35px; padding: 4px 0px 0px 1px;"></div>
				';
			}
			echo '<div style = "border-bottom: 1px solid #E0E4F3; margin-left: 54px; width: 650px; height: 30px; padding: 10px 0px 0px 2px;"></div>
			';
		}
						
		echo '<div style = "position:absolute; margin: -369px 0px 0px 53px">';

		// Printa glavni dio
		echo '<div class="kolona">'; // Pocetak kolone	
		$lastDay = 1; // Promjena dana
		$lastCas = 0; // Prazna polja
		while ($row = mysql_fetch_array($rasSel)){
			$cssFontSize = "";
			$cssFontSize2 = "";
			// Provjerava da li je presao na novi dan
			if ($row['dan_u_sedmici'] != $lastDay){
				echo '<div class="razmak"></div></div>'; // Kraj one kolone
				$dayDif = $row['dan_u_sedmici']-$lastDay-1; //Provjerava ako ima prazan dan izmedu										
				for ($i=0; $i<$dayDif; $i++){
					echo '<div class="kolona">
						<div class="prazna_celija" style="height:28px"></div>
						<div class="razmak"></div>
						</div>';
				}
				echo '<div class="kolona">'; // Prelazak u novu kolonu
				$lastDay = $row['dan_u_sedmici'];
				$lastCas = 0;
			}
									
			/* Provjerava da li postoji jos neki cas paralelno */
			if ($parSel = myquery("SELECT id, vrijeme_pocetak, vrijeme_kraj FROM raspored_stavka WHERE ((vrijeme_pocetak<='".$row['vrijeme_pocetak']."' AND vrijeme_kraj>='".$row['vrijeme_pocetak']."') OR (vrijeme_pocetak<='".$row['vrijeme_kraj']."' AND vrijeme_kraj>='".$row['vrijeme_kraj']."')OR (vrijeme_pocetak>='".$row['vrijeme_pocetak']."' AND vrijeme_kraj<='".$row['vrijeme_kraj']."')) AND godinaR = '".$row['godinaR']."' AND smijerR = '".$row['smijerR']."' AND dan_u_sedmici='".$row['dan_u_sedmici']."' AND id!='".$row['id']."' ORDER BY vrijeme_pocetak ASC")){
				$css = 'celija'; // Ovo ti je css za normalni siroki box i on je default
				$cssMarLeft = 0; // Default je na lijevoj strani
				if (mysql_num_rows($parSel)) { // Broji prethodni query, ako ima makar 1 red u rezultatu znaci da se nesto odvija paralelno
					$css .= '_pola'; // Ovo dodaje na css stil da bi bila manja celija
					$cssFontSize = 'style = "font-size: 9px"';
					$cssFontSize2 = "font-size: 9px;";
					// Ovaj dio sluzi da celije idu u cik-cak, valjda kontas sta hocu da kazem
					if ($polaDone){
						// Ovo sve se desava ako je ovo drugi po redu mali box
						$cssMarLeft = 65; // margin left stavlja 60 da ga odmakne na desnu stranu
						$polaDone = false; // Ovo oznacava da slijedeci box treba da bude na lijevoj strani
					}
					else{
						$cssMarLeft = 0; // isto kao i gore samo ostaje na lijevoj strani
						$polaDone = true; // oznacava da slijedeci box ide desno
					}
				}
				else{
					$polaDone = false; // next box ide desno
				}						
			}
			
			if($row['labgrupa'] != 0) {
				$grupeSel = mysql_fetch_array(myquery("SELECT * FROM labgrupa WHERE id = '".$row['labgrupa']."' "));
				$grupaP = " - ".$grupeSel['naziv'];
			} else
				$grupaP = "";
				
			$sala = mysql_fetch_array(myquery("SELECT naziv FROM raspored_sala WHERE id = '".$row['sala']."' "));
			
			if($tip == "full")
				echo '<div class="'.$css.'" style="'.$cssFontSize2.' height:'.(28+($row['vrijeme_kraj']-$row['vrijeme_pocetak'])*41).'px; margin-top:'.(($row['vrijeme_pocetak']-1)*41).'px; margin-left:'.$cssMarLeft.'px">
					<div class = "naslov" '.$cssFontSize.'><div style = "float:left">'.vrijemeIspis($row['vrijeme_pocetak'], $row['vrijeme_kraj']).'</div> <div class = "razmak"></div></div> '.$row['skracenoP'].' ('.$row['tip'].') <b>'.$row['kratki_naziv'].'</b> '.$grupaP.' - '.$sala['nameS'].' </div>';
			else
				echo '
				<div class="'.$css.'" style="'.$cssFontSize2.' height:'.(28+($row['vrijeme_kraj']-$row['vrijeme_pocetak'])*41).'px; margin-top:'.(($row['vrijeme_pocetak']-1)*41).'px; margin-left:'.$cssMarLeft.'px">
					<div class = "naslov" '.$cssFontSize.'><div style = "float:left">'.vrijemeIspis($row['vrijeme_pocetak'], $row['vrijeme_kraj']).'</div> <div class = "razmak"></div></div> <b>'.$sala['nameS'].'</b> </div>';

			// Ispisuje box sa predmetom									
			
			$lastCas = $row['vrijeme_kraj'];
		}
		
		echo '</div>';
		echo '<div class = "razmak"></div>';
		echo '</div>';
	}
}

//Var. za printanje predmeta prema semestrima i odsjecima
$odsjek = $_POST['studij'];
$semestar = $_POST['ponudakursa'];
$imeOdsjek = $_POST['studijNameH'];

//Var. za printanje grupa prema predmetima
$imePredmeta = $_POST['predmetNameH'];
$predmet = $_POST['predmet'];

//Var za printanje nastavnika po predmetima
$imeNastavnika = $_POST['imeNastavnika'];
$nastavnik = $_POST['nastavnik'];
	
	//Printanje predmeta
	if($_GET['act'] == "SiO") {
		echo "<div style = 'font-size: 20px; padding-bottom: 20px;'>Spisak predmeta za odsjek: <b>".$imeOdsjek."</b> na ".$semestar." semestru.</div>";
	
		echo '
		<div>
			<div style = "width: 60%; float: left;">Naziv predmeta</div>
			<div style = "width: 20%; float: left;">Akademska godina</div>
			<div style = "width: 20%; float: left;">Obavezan/Izborni</div>
			<div style = "clear: both;"></div>
			<hr>
			
		';
		//SQL ispis 
		$selectSQLP1 = myquery("SELECT b.naziv AS 'n_predmet', d.naziv AS 'n_ag', a.obavezan FROM ponudakursa a, predmet b, studij c, akademska_godina d WHERE b.id = a.predmet AND c.id = a.studij AND d.id = a.akademska_godina AND a.studij = '".$odsjek."' AND a.semestar = '".$semestar."' ORDER BY a.studij ASC");
	
		if(mysql_num_rows($selectSQLP1) < 1)
			echo "Nema elemenata u bazi";
		else {
			while($sP1 = mysql_fetch_array($selectSQLP1)) {
				if($sP1['obavezan'] == 1)
					$ob = "Obavezan";
				else
					$ob = "Izborni";
			
				echo '
				<div style = "width: 60%; float: left;">'.$sP1['n_predmet'].'</div>
				<div style = "width: 20%; float: left;">'.$sP1['n_ag'].'</div>
				<div style = "width: 20%; float: left;">'.$ob.'</div>
				<div style = "clear: both;"></div>
				';
			}
			
			//Ako ima rezultata ponudi print page-a
			printPage();
		}
	//Printanje grupa
	} else if ($_GET['act'] == "PG") {
		echo "<div style = 'font-size: 20px; padding-bottom: 20px;'>Spisak grupa za predmet: <b>".$imePredmeta."</b></div>";
		
		$selectSQLG = myquery("SELECT a.naziv, COUNT(b.student) AS countSt FROM labgrupa a LEFT JOIN student_labgrupa b ON b.labgrupa = a.id WHERE a.predmet = '".$predmet."' GROUP BY a.id");
		if(mysql_num_rows($selectSQLG) < 1)
			echo "Nema definisanih grupa za izabrani predmet";
		else {
			while($sSG = mysql_fetch_array($selectSQLG)) {
				
				echo "- <b>".$sSG['naziv']."</b> - <i>".$sSG['countSt']." studenata u grupi</i><br/>";
			}
			
			//Ako ima rezultata ponudi print page-a
			printPage();
		}
	//Printanje profesora
	} else if ($_GET['act'] == "PP") {
		echo "<div style = 'font-size: 20px; padding-bottom: 20px;'>Spisak predmeta nastavnika: <b>".$imeNastavnika."</b></div>";
		
		$selectSQLProf = myquery("SELECT b.naziv FROM nastavnik_predmet a, predmet b WHERE b.id = a.predmet AND a.nastavnik = '".$nastavnik."' ");
		if(mysql_num_rows($selectSQLProf) < 1)
			echo "Nastavnik nije aktivan ni najednom predmetu";
		else {
			while($sSG = mysql_fetch_array($selectSQLProf)) {
				
				if($sSG['email'] == "" || $sSG['email'] == NULL)
					$email = "-/-";
				else
					$email = $sSG['email'];
				
				echo "- ".$sSG['naziv']." <br/>";
			}
			
			//Ako ima rezultata ponudi print page-a
			printPage();
		}
	} else if ($_GET['act'] == "rasporedFull") {
		$id = $_GET['id'];
		$ime = $_GET['nazivS'];
		
		echo '<h2>Raspored casova za odsjek <b>'.$ime.'</b></h2>';
		
		printRaspored($id, "full");
		
		//Ako ima rezultata ponudi print page-a
			printPage();
		
	} else if ($_GET['act'] == "sale") {
		$id = $_GET['id'];
		$ime = $_GET['nazivS'];
		
		echo '<h2>Raspored sala za odsjek <b>'.$ime.'</b></h2>';
		
		printRaspored($id, "sale");
		
		//Ako ima rezultata ponudi print page-a
			printPage();
	}
?>