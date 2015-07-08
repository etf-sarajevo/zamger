<?
	// AKCIJE
	// FIXME prebaciti na stringove

	if (intval($_REQUEST['save']) == 1) { // lični podaci
		$djevojacko= my_escape($_REQUEST['djevojacko']);
		$vozacka= intval($_REQUEST['vozacka']);
		$mjezik= intval($_REQUEST['mjezik']);
		$nacin_stanovanja= intval($_REQUEST['nacin_stanovanja']);		
		myquery("update osoba set djevojacko_prezime='$djevojacko', maternji_jezik=$mjezik , vozacka_dozvola=$vozacka , nacin_stanovanja=$nacin_stanovanja where id=$osoba");
	}

	if (intval($_REQUEST['save']) == 2) { // usavršavanje
		$naziv= my_escape($_REQUEST['naziv_usavrsavanja']);
		$datum= strtotime(my_escape($_REQUEST['datum_usavrsavanja']));
		$institucija= my_escape($_REQUEST['naziv_institucije']);
		$kvalifikacija= my_escape($_REQUEST['kvalifikacija']);
		myquery("INSERT INTO hr_usavrsavanje (osoba ,datum ,naziv_usavrsavanja ,obrazovna_institucija ,kvalifikacija)VALUES ('$osoba',  FROM_UNIXTIME('$datum'),  '$naziv',  '$institucija',  '$kvalifikacija')");
	}

	if (intval($_REQUEST['save']) == 3) { // Radovi
		$naziv= my_escape($_REQUEST['naziv_rada']);
		$datum= strtotime(my_escape($_REQUEST['datum_rada']));
		$naziv_casopisa= my_escape($_REQUEST['naziv_casopisa']);
		$naziv_izdavaca= my_escape($_REQUEST['naziv_izdavaca']);
		myquery("INSERT INTO hr_naucni_radovi (osoba ,datum ,naziv_rada ,naziv_casopisa ,naziv_izdavaca)VALUES ('$osoba',  FROM_UNIXTIME('$datum'),  '$naziv',  '$naziv_casopisa',  '$naziv_izdavaca')");
	}

	if (intval($_REQUEST['save']) == 4) { // Mentorstva
		$datum= strtotime(my_escape($_REQUEST['datum_mentorstva']));
		$ime_kandidata= my_escape($_REQUEST['ime_kandidata']);
		$naziv_teme= my_escape($_REQUEST['naziv_teme']);
		$mfakultet= intval($_REQUEST['mfakultet']);
		$mmentorstvo= intval($_REQUEST['mmentorstvo']);
		myquery("INSERT INTO hr_mentorstvo (osoba ,datum ,ime_kandidata ,naziv_teme ,fakultet,vrsta_mentora)VALUES ('$osoba',  FROM_UNIXTIME('$datum'),  '$ime_kandidata',  '$naziv_teme',  $mfakultet,$mmentorstvo)");
	}

	if (intval($_REQUEST['save']) == 5) { // Publikacije
		$datum= strtotime(my_escape($_REQUEST['datum_publikacije']));
		$naziv= my_escape($_REQUEST['naziv_publikacije']);
		$casopis= my_escape($_REQUEST['naziv_ci']);
		$fk_tip_publikacije= intval($_REQUEST['vrsta_publikacije']);
		myquery("INSERT INTO  hr_publikacija (osoba,datum ,naziv ,casopis ,tip_publikacije) VALUES ('$osoba',  FROM_UNIXTIME('$datum'),  '$naziv',  '$casopis',  $fk_tip_publikacije)");
	}


	if (intval($_REQUEST['save']) == 6) { // Nagrade
		$datum= strtotime(my_escape($_REQUEST['datum_nagrade']));
		$naziv= my_escape($_REQUEST['naziv_nagrade']);
		$opis= my_escape($_REQUEST['opis_nagrade']);
		myquery("INSERT INTO `hr_nagrade_priznanja` (`osoba`, `datum`, `naziv`, `opis`) VALUES ('$osoba',  FROM_UNIXTIME('$datum'),  '$naziv',  '$opis')");
	}

	if (intval($_REQUEST['save']) == 7) { // Jezik
		$jezik= intval($_REQUEST['jezik']);
		$razumjevanje= intval($_REQUEST['razumjevanje']);
		$govor= intval($_REQUEST['govor']);
		$pisanje= intval($_REQUEST['pisanje']);
		myquery("INSERT INTO `hr_kompetencije` (`osoba`, `jezik`, `razumjevanje`, `govor`, pisanje) VALUES ('$osoba', $jezik, $razumjevanje,$govor, $pisanje )");
	}

?>

<link rel="stylesheet" href="css/libs/hr.css" type="text/css" media="screen" />
<link rel="stylesheet" href="css/libs/ui.all.css" type="text/css" media="screen" />


<br><br>

<ul id="tabs">
    <li class="tab1"><a href="?sta=common/profil&akcija=ljudskiresursi&subakcija=radnoiskustvo&osoba=<?=$osoba?>" title="Radno iskustvo">1. Radno iskustvo</a></li>
    <li class="tab2"><a href="?sta=common/profil&akcija=ljudskiresursi&subakcija=obrazovanje&osoba=<?=$osoba?>" title="Obrazovanje">2. Obrazovanje</a></li>
    <li class="tab4"><a href="?sta=common/profil&akcija=ljudskiresursi&subakcija=publikacije&osoba=<?=$osoba?>" title="Publikacije">3. Publikacije</a></li>    
    <li class="tab5"><a href="?sta=common/profil&akcija=ljudskiresursi&subakcija=mentorstva&osoba=<?=$osoba?>" title="Mentorstva">4. Mentorstva</a></li> 
    <li class="tab7"><a href="?sta=common/profil&akcija=ljudskiresursi&subakcija=nagrade&osoba=<?=$osoba?>" title="Nagrade/Priznanja">5. Nagrade/Priznanja</a></li> 
    <li class="tab8"><a href="?sta=common/profil&akcija=ljudskiresursi&subakcija=kompetencije&osoba=<?=$osoba?>" title="Lične vjestine/kompetencije">6. Lične vještine/kompetencije</a></li> 
</ul>
<!-- 
<div style="float:right; padding-right:30px;padding-top:10px;">
	<button type="submit"><IMG SRC="images/32x32/spasi.png" ALIGN="absmiddle">&nbsp;&nbsp;Spasi sve unose</button>
</div>
 -->
<form id="hrforma" class="formular" method="post" action="">
<div id="content"> 
  <?
   // funkcija za umotavanje sifrarnika u <option> - treba za includove
  	function getSifrarnikData($tabela, $selectid =0) {
		$q1 = myquery("select id,naziv from $tabela");
		$zavratiti="<option>n/a</option>";
		while ($r1 = mysql_fetch_row($q1)) { 
			$zavratiti .= "<option";
		 	if ($r1[0]==$selectid && $selectid!=0) { $zavratiti  .= " SELECTED"; }
			$zavratiti .= " value=".$r1[0].">".$r1[1]."</option>\n";
		}
		return $zavratiti;
  	}
  
// Pojedini tabovi odvojeni radi preglednosti
$subakcija = $_REQUEST['subakcija'];
if ($subakcija == "radnoiskustvo")
	include ("common/profil/hr_moduli/hr_radnoiskustvo.php");
else if ($subakcija == "obrazovanje")
//	include ("common/profil/hr_moduli/hr_usavrsavanje.php");
  	include ("common/profil/hr_moduli/hr_obrazovanje.php");
//  	include ("common/profil/hr_moduli/hr_naucniradovi.php");
else if ($subakcija == "publikacije")
	include ("common/profil/hr_moduli/hr_publikacije.php");
else if ($subakcija == "mentorstva")
	include ("common/profil/hr_moduli/hr_mentorstva.php");
else if ($subakcija == "nagrade")
	include ("common/profil/hr_moduli/hr_nagrade.php");
else if ($subakcija == "kompetencije")
	include ("common/profil/hr_moduli/hr_kompetencije.php");
else
	include ("common/profil/hr_moduli/hr_radnoiskustvo.php");

  ?>
  <br><br>
</div>
</form>
<br>
<b>VAŽNO: Ukoliko nedostaje neka opcija (npr. vaš maternji jezik) kontaktirajte administratora!</b>


