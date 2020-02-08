<?

		//Pozicija gdje se trenutno admin nalazi (Radi selektovanja u navigaciji i izabira elementa u sadrzaju)
		function pozicija($lokacija = false, $sel = false)
			{	
				//za selektovanje
				if($sel != false && $lokacija == false)
					{
						if($sel)
							$lok = "selected";
						else
							$lok = "nja";
							
						echo $lok;
					}
				else //za sadrzaj
					{
						if($lokacija == 'pocetak' || $lokacija == "")
							{
								$sadrzaj = "laa";
							}
						else if($lokacija == 'sale')
							{
								$sadrzaj = napraviSale();
							}
						else if($lokacija == 'novi')
							{
								$sadrzaj = napraviRaspored();
							}
						else if($lokacija == 'modifikuj')
							{
								$sadrzaj = urediRaspored();
							}
						else if($lokacija == 'pogledaj')
							{
								$sadrzaj;
							}
						
						echo $sadrzaj;
					}
			}
			
		//Select option u formi
		function selectOption($tablica, $elementi, $ajax = false)
			{
				global $db;
				
				$ret1 .= '
					<select name = "'.$tablica.'" id = "'.$tablica.'" '.$ajax.'>
						<option value="0">- - - -</option>
				';
				
				$selectO = $db->Query("SELECT * FROM ".$tablica." ");
				while($sO = $db->fetchArray($selectO))
					{
						$ret1 .= '
							<option value = "'.$db->stripS($sO[$elementi[0]], true).'" >'.$db->stripS($sO[$elementi[1]]).'</option>
						';
					}
				$ret1 .= '
					</select> 
					<br/>
				';
				//111
				return $ret1;
			}
		
		//Kreiranje sala
		function napraviSale()
			{
				global $db, $main;
				
				if($_POST)
					{
						$salaS = $db->addSlashes($_POST['salaS']);
						$kapacitetS = $db->addSlashes($_POST['kapacitetS']);
						
						$salaModif = $db->addSlashes($_POST['modify']);
						
						//Ako je paramatar != 0, uradi update baze, u suprotnom ubaci novi red u bazu
						if($salaModif != 0)
							{
								$updateDBS = $db->Query("UPDATE raspored_sala SET naziv = '".$salaS."', kapacitet = '".$kapacitetS."' WHERE id = '".$salaModif."' ");
								if($updateDBS)
									{
										$main->printInfo("Sala uspjesno modifikovana", true);
									}
								else
									{
										$main->printInfo("Greska pri modifikaciji sale", true);
									}
							}
						else
							{
								$insertIntoDBS = $db->Query("INSERT INTO raspored_sala (naziv, kapacitet) VALUES ('".$salaS."', '".$kapacitetS."') ");
								if($insertIntoDBS)
									{
										$main->printInfo("Sala uspjesno dodana", true);
									}
								else
									{
										$main->printInfo("Greska pri dodavanju sale", true);
									}
							}
					}
				else
					{
						echo '
							<script type = "text/javascript">
								//Funkcija koja pri reloadu prozora brise sve iz formi
								window.onload=function()
									{
										document.saleF.reset();
										fokusPrviElement();
									} 
									
								//Funkcija koja stavlja fokus na prvi element
								function fokusPrviElement()
									{
									document.saleF.salaS.focus();
									}

								//Funkcija za modifikaciju
								function popuniSalaPolja(sala, kapacitet, idS, idM)
									{
										document.saleF.salaS.value=sala;
										document.saleF.salaS.style.background = "#FF7578";
										document.saleF.salaS.style.border = "1px solid #AB070C";
										document.saleF.salaS.style.color = "#fff";
										
										document.saleF.kapacitetS.value=kapacitet;
										document.saleF.kapacitetS.style.background = "#FF7578";
										document.saleF.kapacitetS.style.border = "1px solid #AB070C";
										document.saleF.kapacitetS.style.color = "#fff";
										
										document.saleF.submit_sala.value="Modifikuj salu";
										
										document.getElementById(idM).innerHTML = "(Modifikacija sele: "+ sala +") <span style = \"font-size:10px; font-weight:normal\">| <a href = \"\">Ponisti modifikaciju</a></span>";
										
										document.saleF.modify.value = idS;
										
									}
									
								//Funkcija za potrvrdu brisanja
								function izbrisi(poruka, url) 
									{
										if (confirm(poruka))
											location.href = url;
									}
									
								//Provjera dali su popunjena polja
								function provjeriPolja()
									{
										var imeSale = document.saleF.salaS;
										var capSale = document.saleF.kapacitetS;
										
										if(imeSale.value == null || imeSale.value == "")
											{
												alert(\'Polje sala prazno!\');
												imeSale.focus();
											}
										else if(capSale.value == null || capSale.value == "" || capSale.value == 0)
											{
												alert(\'Polje kapacitet sale prazno (ili je kapacitet sale = 0)\');
												capSale.focus();
											}
										else
											document.saleF.submit();
									}
									
								//Dopusti samo unos brojeva
								function brojevi(polje, e, dec)
									{
										var key;
										var keychar;

										if (window.event)
											key = window.event.keyCode;
										else if (e)
											key = e.which;
										else
											return true;

										keychar = String.fromCharCode(key);

										if ((key==null) || (key==0) || (key==8) || (key==9) || (key==13) || (key==27) )
											return true;
										else if ((("0123456789").indexOf(keychar) > -1))
											return true;
										else if (dec && (keychar == "."))
											{
												polje.form.elements[dec].focus();
												return false;
											}
										else
											return false;
									}
									
								function doAction(){}

							</script>
							<div class = "velikiNaslov">
								Definisnje sala: <span id = "salaModify" style = "color: #ff0000"></span>
							</div>
							<hr style = "border-top: 1px dotted #ccc; color: #FFF">
							<form name = "saleF" id = "saleF" action="./?sta=studentska/raspored&uradi=sale" method = "post">
								<div>
									<div class = "formLS">Unesi oznaku sale: (primjer: S01)</div>
									<div class = "formRS"><input type = "textbox" value = "" name = "salaS" size = "30" MAXLENGTH="10"></div>
									<div class = "razmak"></div>
									
									<div class = "formLS">Kapacitet sale: (primjer: 30)</div>
									<div class = "formRS"><input type = "textbox" value = "" name = "kapacitetS" size = "30" MAXLENGTH="4" onKeyPress="return brojevi(this, event)"></div>
									<div class = "razmak"></div>
									<input type = "textbox" value = "0" name = "modify" size = "30" style = "display: none">
									<input type = "submit" name = "submit_sala" value = "Spremi" onClick = "provjeriPolja(); return false;">
								</div>
							</form>
							<br/>
							<b>SALE</b>
							<hr class = "hrStyle">
							
							<div class = "sectionP0">
								<div id = "sectionP1" style = "font-weight: bold">No.</div>
								<div id = "sectionP2" style = "font-weight: bold">Ime sale</div>
								<div id = "sectionP3" style = "font-weight: bold">Kapacitet</div>
								<div id = "sectionP4" style = "font-weight: bold">Akcije</div>
								<div class = "razmak"></div>
							</div>
						';
						
						//Ispis sala za modifikaciju
						$selectSaleDB = $db->Query("SELECT id, naziv, kapacitet FROM raspored_sala ORDER BY id DESC ");
						$ifExistSDB = $db->Count($selectSaleDB);
						
						if($ifExistSDB >= 1)
							{
								?>
								
								<?
								$nmbrCounter = 1;
								while($pSDB = $db->fetchArray($selectSaleDB))
									{
										$idSale = $db->stripS($pSDB['id'], true);
										$imeSale = $db->stripS($pSDB['naziv']);
										$kapacSale = $db->stripS($pSDB['kapacitet'], true);
										
										echo '
											<div class = "sectionP0">
												<div id = "sectionP1">'.$nmbrCounter.'.</div>
												<div id = "sectionP2">'.$imeSale.'</div>
												<div id = "sectionP3">'.$kapacSale.'</div>
												<div id = "sectionP4">
													<a href = "javascript: doAction()" onClick="javascript:popuniSalaPolja(\''.$imeSale.'\',\''.$kapacSale.'\', \''.$idSale.'\', \'salaModify\')" ><img  src = "images/16x16/log_edit.gif" alt = "Uredi salu" title = "Uredi salu" border = "0" /></a> |
													<a href = "javascript: doAction()" onClick="javascript:izbrisi(\'Zelim izbrisati salu: '.$imeSale.' ?\', \'?sta=studentska/raspored&uradi=sale&do=brisi&idS='.$idSale.'\')"><img src = "images/16x16/brisanje.gif" alt = "Brisi salu" title = "Brisi salu" border = "0" /></a>
												</div>
												<div class = "razmak"></div>
											</div>
										';
										
										$nmbrCounter++;
									}
							}
						else
							echo "SALE NISU JOS DEFINISANE";
					}
					
					if($_GET['do'] == "brisi")
						{
							brisiSalu($db->addSlashes($_GET['id']));
						}
					
			}//Kraj kreiranja sala
			
		//Brisanje sala
		function brisiSalu($idSale)
			{
				global $db, $main;
				
				$deleteSDB = $db->Query("DELETE FROM raspored_sala WHERE id = '".$idSale."' ");
				if($deleteSDB)
					{
						//Osvjezi prozor da bi se izbrisala iz liste sala
						echo '
						<script language="JavaScript" type="text/javascript">
							var reloaded = false;
							var loc=""+document.location;
							loc = loc.indexOf("?reloaded=")!=-1?loc.substring(loc.indexOf("?reloaded=")+10,loc.length):"";
							loc = loc.indexOf("&")!=-1?loc.substring(0,loc.indexOf("&")):loc;
							reloaded = loc!=""?(loc=="true"):reloaded;

							function reloadOnceOnly() {
								if (!reloaded) 
									window.location.replace(window.location+"?reloaded=true");
							}
							reloadOnceOnly(); //You can call this via the body tag if desired
						</script>
						';
						
						$main->printInfo("Sala uspjesno obrisana", false);
					}
				else
					{
						$main->printInfo("GRESKA, prilikom brisanja sale");
					}
			}
			
		//Ispis <option> predmeta vezanih za neki smijer i godinu
		function ispisiPredmeteVezaneZaSmijer($smijer, $godina = false)
			{
				
			}
			
		//Kreiraj raspored
		function napraviRaspored()
			{
				global $db;
				
				$ajax = '
					onChange = "javascript:promjenaGodine(\'godinaSCSS\')";
				';
				
				if($_POST)
					{
					
					}
				else
					{
						echo'
							<script type = "text/javascript">
								//Funkcija za dinamicko mijenjanje godine studija u zavisnosti od smijera
								function promjenaGodine(ob)
									{
										sel = getSelected(ob);
										//selSem = getSelected(document.getElementById(\'semestar\'));
										
										god = document.getElementById(\'godina\');
										//gr = document.getElementById(\'grupa\');
										
										// Godina
										god.options.length=0;
										god.options[0] = new Option("- - - -", 0);
										if (sel == 0)	
											{												
												god.disabled=true;
											}
										else if (sel == 1)
											{												
												god.options[1] = new Option("1. Semestar", 1);
												god.options[2] = new Option("2. Semestar", 2);
												god.disabled=false;
											}										
										else
											{												
												for (i=3; i<=9; i++)
													{
														god.options[i-2] = new Option(i+". Semestar", i);
													}
												god.disabled=false;
											}
											
										ucitajGrupe(document.getElementById(\'studij\'));													
									}	
									
								function ucitajGrupe(ob)
									{
										selGod = getSelected(ob);
										selStu = getSelected(document.getElementById(\'studij\'));
										
										if (selStu && selGod)
											{
												// Grupe
												gr = document.getElementById(\'grupa\');
												gr.options.length=0;
												gr.options[0] = new Option("- - - -", 0);
												if (labgrupe[selStu+"-"+selGod]) 
													{
														for (i=1; i<=labgrupe[selStu+"-"+selGod].length; i++)
															{
																gr.options[i] = new Option(labgrupe[selStu+"-"+selGod][i-1].naziv, labgrupe[selStu+"-"+selGod][i-1].id);
															}
														gr.disabled=false;	
													}
												else
													{
														gr.disabled=true;
													}
											}
									}							
								function getSelected(ob)
									{
										for (i = 0; i < ob.options.length; i++)
											{
												if (ob.options[i].selected)
													{
														return ob.options[i].value;
														break;
													}
											}
									}';
						if ($lgS = $db->Query("SELECT lg.naziv, lg.id, pk.studij, pk.semestar FROM ponudakursa pk, labgrupa lg WHERE lg.predmet = pk.id ORDER BY lg.naziv ASC"))
							{
								while ($row = $db->fetchArray($lgS)) {
									$labG[$row['studij'].'-'.$row['semestar']][] = array("id" => $row['id'], "naziv" => $row['naziv']);
								}
								echo "var labgrupe = ".json_encode($labG).";";
							}
						echo '
							</script>
							<div class = "velikiNaslov">
								Definisi novi raspored:
							</div>
							<hr style = "border-top: 1px dotted #ccc; color: #FFF">
					
							<form name = "rasP" id = "rasP" action="./?sta=admin&uradi=sale" method = "post">
								<div>
									<div class = "formLS">Akademska godina:</div>
									<div class = "formRS">'.selectOption("akademska_godina", array("id", "naziv")).'</div>
									<div class = "razmak"></div>								
									
									<div class = "formLS">Smijer:</div>
									<div class = "formRS">'.selectOption("studij", array("id", "naziv"), ' onchange="promjenaGodine(this)"').'</div>
									<div class = "razmak"></div>
									
									<div class = "formLS">Godina studija:</div>
									<div class = "formRS" id = "godinaSCSS"><select name="godina" id="godina" onchange="ucitajGrupe(this);" disabled="disabled"><option value="0">- - - -</option></select></div>
									<div class = "razmak"></div>
									
									<div class = "formLS">Grupa:</div>
									<div class = "formRS"><select name="grupa" id="grupa" disabled="disabled"><option value="0">- - - -</option></select></div>
									<div class = "razmak"></div>
									<br/>
									
									
									<script type="text/javascript">
										
										function addElement(inEl, elTag, inHtml, otherD) 
											{
												parentEl = document.getElementById(inEl);
												newEl = document.createElement(elTag);
												/*if (otherD.id)
													newEl.setAttribute(\'id\', otherD.id);
												if (otherD.class)
													newEl.setAttribute(\'class\', otherD.id);
												if (otherD.style)
													newEl.setAttribute(\'style\', otherD.style);*/
												for (index in otherD)
													newEl.setAttribute(index, otherD[index]);
												newEl.innerHTML = inHtml;
												parentEl.appendChild(newEl);
											}
	
										var dani = {1:"ponedeljak",2:"utorak",3:"srijeda",4:"cetvrtak",5:"petak"};
										var nums = {1:1, 2:1, 3:1, 4:1, 5:1};

										function addPredmet(id)
											{
												nums[id] += 1;
												addHtml = \'<a style = "font-size: 13px; text-decoration: none; text-align: right" href = "javascript:void(0)" onclick="this.parentNode.parentNode.removeChild(this.parentNode);">[-]</a><div class = "cRDanNoBox">\'+nums[id]+\'<br/></div><div class = "cRDanContBox">Predmet:<br/><select name="predmet[\'+id+\'][\'+nums[id]+\']"><option></option></select><br/>Vrijeme:<br/><span><select name="h[\'+id+\'][\'+nums[id]+\']"><option></option></select></span>-<span><select name="min[\'+id+\'][\'+nums[id]+\']"><option></option></select></span></div><div class = "razmak"></div>\';

												addElement(dani[id],\'div\',addHtml,{id:\'mkdiv\'});
											}
											
									</script>									
									
									<div class = "cRDanBox" id="ponedeljak">
										<span style = "float: left"><b>Ponedjeljak</b></span> <span style = "float: right"><a href="javascript:addPredmet(1);">[+]</a></span><br/><br/>
										
										<!-- ################################################################## -->
										<div class = "cRDanNoBox">1</div>
										<div class = "cRDanContBox">
											Predmet:<br/>
											<select name="predmet[1][1]">
												<option></option>
											</select>
											<br/>
											Vrijeme:<br/>
											<span>
												<select name="h[1][1]">
													<option></option>
												</select>
											</span>
											-
											<span>
												<select name="min[1][1]">
													<option></option>
												</select>
											</span>
										</div>
										<div class = "razmak"></div>				
										
									</div>
									
									<div class = "cRDanBox" id="utorak">
										<span style = "float: left"><b>Utorak</b></span> <span style = "float: right"><a href="javascript:addPredmet(2);">[+]</a></span><br/><br/>
										
										<div class = "cRDanNoBox">1</div>
										<div class = "cRDanContBox">
											Predmet:<br/>
											<select name="predmet[2][1]">
												<option></option>
											</select>
											<br/>
											Vrijeme:<br/>
											<span>
												<select name="h[2][1]">
													<option></option>
												</select>
											</span>
											-
											<span>
												<select name="min[2][1]">
													<option></option>
												</select>
											</span>
										</div>
										
									</div>
									
									<div class = "cRDanBox" id="srijeda">
										<span style = "float: left"><b>Srijeda</b></span> <span style = "float: right"><a href="javascript:addPredmet(3);">[+]</a></span><br/><br/>
										
										<div class = "cRDanNoBox">1</div>
										<div class = "cRDanContBox">
											Predmet:<br/>
											<select name="predmet[3][1]">
												<option></option>
											</select>
											<br/>
											Vrijeme:<br/>
											<span>
												<select name="h[3][1]">
													<option></option>
												</select>
											</span>
											-
											<span>
												<select name="min[3][1]">
													<option></option>
												</select>
											</span>
										</div>
										
									</div>
									
									<div class = "cRDanBox" id="cetvrtak">
										<span style = "float: left"><b>Cetvrtak</b></span> <span style = "float: right"><a href="javascript:addPredmet(4);">[+]</a></span><br/><br/>
										
										<div class = "cRDanNoBox">1</div>
										<div class = "cRDanContBox">
											Predmet:<br/>
											<select name="predmet[4][1]">
												<option></option>
											</select>
											<br/>
											Vrijeme:<br/>
											<span>
												<select name="h[4][1]">
													<option></option>
												</select>
											</span>
											-
											<span>
												<select name="min[4][1]">
													<option></option>
												</select>
											</span>
										</div>
										
									</div>
									
									<div class = "cRDanBox" id="petak">
										<span style = "float: left"><b>Petak</b></span> <span style = "float: right"><a href="javascript:addPredmet(5);">[+]</a></span><br/><br/>
										
										<div class = "cRDanNoBox">1</div>
										<div class = "cRDanContBox">
											Predmet:<br/>
											<select name="predmet[5][1]">
												<option></option>
											</select>
											<br/>
											Vrijeme:<br/>
											<span>
												<select name="h[5][1]">
													<option></option>
												</select>
											</span>
											-
											<span>
												<select name="min[5][1]">
													<option></option>
												</select>
											</span>
										</div>
										
										
										
									</div>
									<div class = "razmak"></div>
									<input type = "submit" name = "submit_sala" value = "Spremi" onClick = "provjeriPolja(); return false;">
								</div>
							</form>
						';
					}
					
			}//Kraj kreiranja rasporeda
			
		//Uredi raspored
		function urediRaspored()
			{
				return "Modifikujemo raspored";
			}//Kraj uredjivanja rasporeda
			
		//Navigacija administracije
		function navigacija ()
			{
				$gdje = $_GET['uradi'];
					
				?>
					<div id = "navigacija">
							<ul id = "menu">
								<li><a id="<?pozicija(false, $gdje == "pocetak")?>" href = "?sta=studentska/raspored&uradi=pocetak">Raspored administracija</a></li>
								<li><a id="<?pozicija(false, $gdje == "sale")?>" href = "?sta=studentska/raspored&uradi=sale">Definisi sale</a></li>
								<li><a id="<?pozicija(false, $gdje == "novi")?>" href = "?sta=studentska/raspored&uradi=novi">Napravi novi raspored</a></li>
								<li><a id="<?pozicija(false, $gdje == "modifikuj")?>" href = "?sta=studentska/raspored&uradi=modifikuj">Modifikuj postojeci raspored</a></li>
								<li><a id="<?pozicija(false, $gdje == "pogledaj")?>'" href = "?sta=studentska/raspored&uradi=pogledaj">Pogledaj sve rasporede</a></li>
							</ul>
					</div>
				<?
			}
			
		//Sadrzaj (uradi)
		function sadrzaj()
			{
				$gdje = $_GET['uradi'];
				
				?>
					<div id = "sadrzajA">
						<?pozicija($gdje)?>
					</div>
					<div class = "razmak"></div>
				<?
			}
			
		//Printanje cijele administracije
		function printajAdministracijuRasporeda()
			{
				?>
					<div id = "adminRas">
						Administracija rasporeda:
						<div class = "razmak"></div><br/>
						<?navigacija()?>
						<?sadrzaj()?>
					</div>
					
				<?
			}

?>