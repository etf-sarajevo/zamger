<LINK href="css/raspored.css" rel="stylesheet" type="text/css">
<script language="JavaScript" type="text/javascript">
			function toggleVisibility(ime){
				var me = document.getElementById(ime);
				var img = document.getElementById('img-'+ime);
				
				if (me.style.display=="none"){
					me.style.display="";
					img.src="images/minus.png";
				}
				else {
					me.style.display="none";
					img.src="images/plus.png";
				}
			}

			//Tooltip
			function prikaziTT(poruka, eVentER) {
				var x = 0;
				var y = 0;
				
				if (document.all) {
					x = event.clientX;
					y = event.clientY;
				} else {
					x = eVentER.pageX;
					y = eVentER.pageY;
				}

				var element = document.getElementById('divTTRA');

				element.style.display = "block";
				element.style.left = x + 12 + "px";
				element.style.top = y + 10 + "px";
				element.innerHTML = poruka;
			}

			function sakrijTT() {
				document.getElementById('divTTRA').style.display = "none";
			}
		</script>
		<div id = "divTTRA" style = "position:absolute; display: none; border: dimgray thin solid; padding: 5px; margin: 2px 5px; background: #f8fbe1; z-index: 100;">
		</div>
<?
	
		//ispisivanje vremena u boxovima
		function vrijemeIspis($vrijemePoc, $vrijemeKraj)
			{
				$beg = array("1" => "09:00", "2" => "10:00", "3" => "11:00", "4" => "12:00", "5" => "13:00",
							"6" => "14:00", "7" => "15:00", "8" => "16:00", "9" => "17:00");
				$end = array("1" => "09:45", "2" => "10:45", "3" => "11:45", "4" => "12:45", "5" => "13:45",
							"6" => "14:45", "7" => "15:45", "8" => "16:45", "9" => "17:45");
				
				$ispis = $beg[$vrijemePoc]." - ".$end[$vrijemeKraj];
				return $ispis;
			}
		
		//ispis rasporeda
		function printRaspored($korisnik, $tipK)
			{
				
				echo '
				<div><a href = "#" onclick="toggleVisibility(\'raspored\')"><img id = "img-raspored" src = "images/plus.png" border = "0" align = left hspace = 2/>Pogledaj svoj raspored casova</a><hr style = "background-color: #ccc; height: 0px; border: 0px; padding-bottom: 1px"></div>
				<div id = "raspored" style = "display: none; padding-bottom: 15px; line-height: 18px;">
				';
				
				if($tipK == "student") {
					/*$selUserData = myquery("SELECT a.labgrupa, b.studij, b.semestar, b.akademska_godina FROM student_labgrupa a, student_studij b WHERE a.student = '".$korisnik."' AND b.student = '".$korisnik."' ");
					while($sUD = mysql_fetch_array($selUserData)) {
						$grupaId = $sUD['labgrupa'];
						$studijId = $sUD['studij'];
						$semId = $sUD['semestar'];
						$adId = $sUD['akademska_godina'];
					
						$sqlRasG .= " OR grupaR = ".$grupaId;
						
					}
					
					$sqlWhere = "godinaR = '".$adId."' AND smijerR = '".$studijId."' AND semestarR = '".$semId."' AND grupaR = '0' ".$sqlRasG;*/
					
					$sqlUpit = "SELECT idR, predmet.id, naziv, kratki_naziv, danR, smijerR, godinaR, tipR, vrijemeRP, vrijemeRK, salaR, obavezan, student_labgrupa.student, grupaR 
								FROM ras_raspored 
								JOIN predmet ON ras_raspored.predmetR = predmet.id 
								JOIN ponudakursa ON predmet.id = ponudakursa.predmet 
								JOIN student_predmet ON ponudakursa.predmet = student_predmet.predmet 
								JOIN student_labgrupa ON student_predmet.student = student_labgrupa.student
								WHERE student_predmet.student = '".$korisnik."' 
								AND grupaR IN (0, student_labgrupa.labgrupa) 
								AND predmetR = student_predmet.predmet
								GROUP BY idR, predmet.id, naziv, kratki_naziv, danR, smijerR, godinaR, tipR, vrijemeRP, vrijemeRK, salaR, obavezan, student_labgrupa.student, grupaR
								ORDER BY danR ASC, vrijemeRP ASC, idR ASC";
					
				} else {
					$whereCounter = 0;
					$selUserData = myquery("SELECT a.predmet, b.akademska_godina, b.semestar FROM nastavnik_predmet a, ponudakursa b WHERE a.nastavnik = '".$korisnik."' AND b.predmet = a.predmet AND b.akademska_godina = (SELECT id FROM akademska_godina ORDER BY id DESC)");
					while($sUD = mysql_fetch_array($selUserData)) {
						
						$adId = $sUD['akademska_godina'];
						$semId = $sUD['semestar'];
						
						if($whereCounter > 0)
							$sqlPredmet .= " OR predmetR = ".$sUD['predmet'];
						else
							$sqlPredmet = " predmetR = ".$sUD['predmet'];
						
						$whereCounter++;
					}
						
					$sqlWhere = "godinaR = '".$adId."' AND semestarR = '".$semId."' AND tipR = 'P' AND (".$sqlPredmet.")";
					
					$sqlUpit = "SELECT idR, predmet.id, naziv, kratki_naziv, danR, smijerR, godinaR, tipR, vrijemeRP, vrijemeRK, salaR FROM ras_raspored LEFT JOIN predmet ON ras_raspored.predmetR = predmet.id WHERE ".$sqlWhere." ORDER BY danR ASC, vrijemeRP ASC, idR ASC";
				}
							
				// Selektuje podatke iz baze
				if ($rasSel = myquery($sqlUpit))
					{
						if(mysql_num_rows($rasSel) == 0)
							echo "Nema rasporeda casova za korisnika<br/><br/>";
						else {
						// Printa dane
						echo '<div class="dan_header" style="width:50px"></div>
							<div class="dan_header">Ponedjeljak</div>
							<div class="dan_header">Utorak</div>
							<div class="dan_header">Srijeda</div>
							<div class="dan_header">Cetvrtak</div>
							<div class="dan_header">Petak</div>
							<div class="razmak"></div>
							';

						// Printa satnicu
						
						echo '<div style="float:left">
						';
						for ($i=9; $i<=17; $i++)
							{
								echo '<div class="satnica">'.$i.':00</div>
								';
							}
						echo '</div>
						';
						
						for($r=0; $r<9; $r++) {
							for($r2=0; $r2<4; $r2++) {
								echo '<div style = "float: left; border-right: 1px solid #E0E4F3; width: 129px; height: 35px; padding: 4px 0px 0px 1px;"></div>
								';
							}
							echo '<div style = "border-bottom: 1px solid #E0E4F3; margin-left: 54px; width: 650px; height: 30px; padding: 10px 0px 0px 2px;"></div>
							';
						}
						
						echo '
						<div style = "position:absolute; margin: -370px 0px 0px 53px">
						';
						// Printa glavni dio
						echo '<div class="kolona">
						'; // Pocetak kolone	
						$lastDay = 1; // Promjena dana
						$lastCas = 0; // Prazna polja
						while ($row = mysql_fetch_array($rasSel))
							{
								
								if($row['obavezan'] == 0) {
									//$provjeraSaUserom = myquery("SELECT * FROM ");
									echo "";
								}
							
								$cssFontSize = "";
								$cssFontSize2 = "";
								// Provjerava da li je presao na novi dan
								if ($row['danR'] != $lastDay)
									{
										echo '<div class="razmak"></div></div>
										'; // Kraj one kolone
										$dayDif = $row['danR']-$lastDay-1; //Provjerava ako ima prazan dan izmedu										
										for ($i=0; $i<$dayDif; $i++)
											{
												echo '<div class="kolona">
														<div class="prazna_celija" style="height:28px"></div>
														<div class="razmak"></div>
													</div>
													';
											}
										echo '<div class="kolona">
										'; // Prelazak u novu kolonu
										$lastDay = $row['danR'];
										$lastCas = 0;
									}
									

								$css = 'celija'; // Ovo  je css za normalni siroki box i on je default
								$cssMarLeft = 0; // Default je na lijevoj strani
										
								$polaDone = false; // next box ide desno
										
									
								$sala = mysql_fetch_array(myquery("SELECT nameS FROM ras_sala WHERE idS = '".$row['salaR']."' "));

								if($row['tipR'] == "P") {
									$bojaTrake = "#E95026";
									$altT = "Predavanje";
								} else if($row['tipR'] == "T") {
									$bojaTrake = "#FF8100";
									$altT = "Tutorial";
								} else {
									$bojaTrake = "#E9DE26";
									$altT = "Labaratorjska vjezba";
								}
								
								$stylePlus = 'onMouseMove = "prikaziTT(\'<b>'.$row['naziv'].'</b> - '.$altT.'\', event)" onMouseOver = "prikaziTT(\'<b>'.$row['naziv'].'</b> - '.$altT.'\', event)" onMouseOut = "sakrijTT()"';
								// Ispisuje box sa predmetom									
								echo '<div '.$stylePlus.' class="'.$css.'" style="'.$cssFontSize2.' height:'.(28+($row['vrijemeRK']-$row['vrijemeRP'])*41).'px; margin-top:'.(($row['vrijemeRP']-1)*41).'px; margin-left:'.$cssMarLeft.'px">
										<div class = "naslov" style = "background: '.$bojaTrake.'; '.$cssFontSize2.'">'.vrijemeIspis($row['vrijemeRP'], $row['vrijemeRK']).'</div> <b>'.$row['kratki_naziv'].'</b> - '.$sala['nameS'].'</div>
										';

								$lastCas = $row['vrijemeRK'];
							}

						echo '</div>';
						echo '<div class = "razmak"></div>';
						echo '</div><br/>';
						echo '<div style = "float: left; background: #E95026; padding: 2px; margin: 1px">Predavanje</div> <div style = "float: left; background: #FF8100; padding: 2px; margin: 1px">Tutorial</div> <div style = "float: left; background: #E9DE26; padding: 2px; margin: 1px">Labaratorijska vjezba</div><div class = "razmak"></div>';
						}
					}
				else
					echo "SQL ERROR";
					
				echo '</div>';
			}
?>
