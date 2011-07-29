<?
session_start();
?>
<LINK href="../css/raspored.css" rel="stylesheet" type="text/css">
<script language="JavaScript" type="text/javascript">
			//Funkcija za potrvrdu brisanja
			function izbrisi(poruka, url)  {
				if (confirm(poruka))
					location.href = url;
			}
			
			//Tooltip
			function prikaziTT(poruka, eVentER, elementD) {
				var x = 0;
				var y = 0;
				
				if (document.all) {
					x = event.clientX;
					y = event.clientY;
				} else {
					x = eVentER.pageX;
					y = eVentER.pageY;
				}
				
				document.getElementById(elementD).style.background = "#f4f2ca";

				var element = document.getElementById( 'divTTR' );

				element.style.display = "block";
				element.style.left = x + 12 + "px";
				element.style.top = y + 10 + "px";
				element.innerHTML = poruka;
			}

			function sakrijTT(elementD) {
				document.getElementById( 'divTTR' ).style.display = "none";
				document.getElementById(elementD).style.background = "#FFFFEE";
			}
		</script>
		<div id = "divTTR" style = "position:absolute; display: none; border: dimgray thin solid; padding: 5px; margin: 2px 5px; background: #f8fbe1; z-index: 100;">
		</div>
<?
$tmplDan = array (1=>"Ponedjeljak", 2=>"Utorak", 3=>"Srijeda", 4=>"Cetvrtak", 5=>"Petak");
$tmplVrijeme = array (1 => "9:00<br/>9:45", 2=>"10:00<br/>10:45", 3=>"11:00<br/>11:45", 4=>"12:00<br/>12:45", 5=>"13:00<br/>13:45", 6=>"14:00<br/>14:45", 7=>"15:00<br/>15:45", 8=>"16:00<br/>16:45", 9=>"17:00<br/>17:45", 10=>"18:00<br/>18:45");
	
//Brisanje casova
if($_GET['id'] != NULL) {
	foreach ($_SESSION['tmpRas'] as $key => $val){
		if ($val['key'] == $_GET['id']){
			unset($_SESSION['tmpRas'][$key]);
		} 
	}
}
	
	//FUNKCIJA ZA ISPIS REZULTATA U RASPORED
	function printPredmet($x, $y, $ret = false, $style = false) {

		$styleC = 0;
		$boxCont = 0;
		
		//Izbrisi nulti box (blank)
		if($x == 0 AND $y == 0)
			return false;
			
		foreach ($_SESSION['tmpRas'] AS $val) {
			if ($val['x'] == $x AND $val['y'] == $y) {
				
				if($val['tip'] == "P")
					$stylePlusCont = "";
				else {
					$boxCont++;
					$stylePlusCont .= $val['ispisPopUp'];
				}
				
				if($ret == true)
					$ispisB .= $val['ispisBox']." ";
				else if($style != false)
					$styleC++;
			}
		}
		$stylePlus = 'onMouseMove = "prikaziTT(\''.$stylePlusCont.'\', event, \'cellTmpR['.$y.']['.$x.']\')" onMouseOver = "prikaziTT(\''.$stylePlusCont.'\', event, \'cellTmpR['.$y.']['.$x.']\')" onMouseOut = "sakrijTT(\'cellTmpR['.$y.']['.$x.']\')"';
		
		if($ret == true) 
			return $ispisB;
			
		//Posto se funkcija poziva uvijek, provjeri dali ima upisano nesto u box, ako da onda ga oboji kao oznacen, ako ne samo ga oboji u standardnu boju
		if($styleC > 0 AND $style != false) {
			return "text-align: right; background: #FFFFEE;";
		}
		else if($style != false)
			return "background: #F1F6F6;";
			
		//Ako ima sadrzaj a da nije predmet, ispisi popup
		if($boxCont > 0)
			return $stylePlus;
			
		
	}
	
	//ISPIS TMP TABLICE
	for($i = 0; $i<=5; $i++) {						//Ispis redova rasporeda
		for($j = 0; $j<=10; $j++) {					//Ispis kolona rasporeda
	
			$tmpl[$i][0] = $tmplDan[$i];		//Dodjela tmpl-dana, novi dan iz predefinisanog arraya (drugi element niza je 0, jer je potrebno ispisati samo u prvoj koloni imena dana)
			$tmpl[0][$j] = $tmplVrijeme[$j];	//Dodjena tmpl-vremena, vrijeme upisano iz predefinisanog arraya (prvi element niza 0 jer se vrijeme ispisuje samo u prvom redu rasporeda)
			
			//Ako se ispisuje prvi red, centriraj vrijeme, u suprotnom, sav sadrzaj pozicioniraj lijevo
			if($i == 0)
				$pozicioniranjeTexta = "text-align: center;";
			else
				$pozicioniranjeTexta = "text-align: left;";
			
			/**
			* Ispis polja rasporeda sa pripadajucim vrijednostima.
			* Svako polje ima jedinstveni ID radi brisanja boxova sa predmetima
			*/
			echo "
				<div id = 'cellTmpR[".$i."][".$j."]' ".printPredmet($j, $i)." style = '".printPredmet($j, $i, false, true)."margin: 0px 2px 2px 0px; float:left; padding: 2px; width: 60px; height:50px;'>".$tmpl[$i][$j].printPredmet($j,$i, true)."</div>
			";
		}
		//Nakon svakog ispisanog reda, ocisti float i predji u novi red
		echo "<div style = 'clear: both'></div>";
	}
?>