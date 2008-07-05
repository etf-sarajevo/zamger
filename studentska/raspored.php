<LINK href="css/raspored.css" rel="stylesheet" type="text/css">
<?
//Poziva json klasu
require_once "classes/class.json.php";
$json = new Services_JSON; //Nova instanca klase

function studentska_raspored () {
	global $userid,$user_siteadmin,$user_studentska, $db, $main;

	// Provjera privilegija
	if (!$user_studentska && !$user_siteadmin) {
		zamgerlog("nije studentska",3); // 3: error
		biguglyerror("Pristup nije dozvoljen.");
		return;
	}
	
	?>
	<div id = "adminRas">
		Administracija rasporeda:
		<div class = "razmak"></div><br/>
		<?navigacija()?>
		<?sadrzaj()?>
	</div>
		
	<?
}




//Pozicija gdje se trenutno admin nalazi (Radi selektovanja u navigaciji i izabira elementa u sadrzaju)
function pozicija($lokacija = false, $sel = false) {
	//za selektovanje
	if($sel != false && $lokacija == false) {
		if($sel)
			$lok = "selected";
		else
			$lok = "nja";
			
		echo $lok;
	}
	else { //za sadrzaj
		if($lokacija == 'pocetak' || $lokacija == "") {
				$sadrzaj = "laa";
		}
		else if($lokacija == 'sale') {
				$sadrzaj = napraviSale();
		}
		else if($lokacija == 'novi') {
				$sadrzaj = napraviRaspored();
		}
		else if($lokacija == 'modifikuj') {
				$sadrzaj = urediRaspored();
		}
		else if($lokacija == 'pogledaj') {
				$sadrzaj;
		}
		
		echo $sadrzaj;
	}
}
	
//Select option u formi
function selectOption($tablica, $elementi, $ajax = false, $disabled) {
	$ret1 .= '
		<select name = "'.$tablica.'" id = "'.$tablica.'" '.$ajax.' '.$disabled.'>
			<option value="0">- - - -</option>
	';
	
	$selectO = myquery("SELECT * FROM ".$tablica." ");
	while($sO = mysql_fetch_array($selectO)) {
		$ret1 .= '
			<option value = "'.my_escape($sO[$elementi[0]], true).'" >'.my_escape($sO[$elementi[1]]).'</option>
		';
	}
	$ret1 .= '
		</select> 
		<br/>
	';
	return $ret1;
}

//Kreiranje sala
function napraviSale() {
	if($_POST) {
		$salaS = my_escape($_POST['salaS']);
		$kapacitetS = my_escape($_POST['kapacitetS']);
		
		$salaModif = my_escape($_POST['modify']);
		
		//Ako je paramatar != 0, uradi update baze, u suprotnom ubaci novi red u bazu
		if($salaModif != 0) {
			$updateDBS = myquery("UPDATE ras_sala SET nameS = '".$salaS."', capacS = '".$kapacitetS."' WHERE idS = '".$salaModif."' ");
			if($updateDBS) {
				printInfo("Sala uspjesno modifikovana", true);
			} else {
				printInfo("Greska pri modifikaciji sale", true);
			}
		} else {
			$insertIntoDBS = myquery("INSERT INTO ras_sala (nameS, capacS) VALUES ('".$salaS."', '".$kapacitetS."') ");
			if($insertIntoDBS) {
				printInfo("Sala uspjesno dodana", true);
			} else {
				printInfo("Greska pri dodavanju sale", true);
			}
		}
	} else {
		?>
		<script language="JavaScript" type="text/javascript">
			//Funkcija koja pri reloadu prozora brise sve iz formi
			window.onload=function() {
				document.saleF.reset();
				fokusPrviElement();
			} 
				
			//Funkcija koja stavlja fokus na prvi element
			function fokusPrviElement() {
				document.saleF.salaS.focus();
			}

			//Funkcija za modifikaciju
			function popuniSalaPolja(sala, kapacitet, idS, idM) {
				document.saleF.salaS.value=sala;
				document.saleF.salaS.style.background = "#FF7578";
				document.saleF.salaS.style.border = "1px solid #AB070C";
				document.saleF.salaS.style.color = "#fff";
				
				document.saleF.kapacitetS.value=kapacitet;
				document.saleF.kapacitetS.style.background = "#FF7578";
				document.saleF.kapacitetS.style.border = "1px solid #AB070C";
				document.saleF.kapacitetS.style.color = "#fff";
				
				document.saleF.submit_sala.value="Modifikuj salu";
				
				document.getElementById(idM).innerHTML = "(Modifikacija sale: "+ sala +") <span style = \"font-size:10px; font-weight:normal\">| <a href = \"?sta=studentska/raspored&uradi=sale\">Ponisti modifikaciju</a></span>";
				
				document.saleF.modify.value = idS;
			}
				
			//Funkcija za potrvrdu brisanja
			function izbrisi(poruka, url)  {
				if (confirm(poruka))
					location.href = url;
			}
			
			//Provjera dali su popunjena polja
			function provjeriPolja() {
				var imeSale = document.saleF.salaS;
				var capSale = document.saleF.kapacitetS;
				
				if(imeSale.value == null || imeSale.value == "") {
					alert('Polje sala prazno!');
					imeSale.focus();
				} else if(capSale.value == null || capSale.value == "" || capSale.value == 0) {
					alert('Polje kapacitet sale prazno (ili je kapacitet sale = 0)');
					capSale.focus();
				} else
					document.saleF.submit();
			}
				
			//Dopusti samo unos brojeva
			function brojevi(polje, e, dec) {
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
				else if (dec && (keychar == ".")) {
					polje.form.elements[dec].focus();
					return false;
				} else
					return false;
			}
		</script>
		<div class = "velikiNaslov">
			Definisanje sala: <span id = "salaModify" style = "color: #ff0000"></span>
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
		<?
			
		//Ispis sala za modifikaciju
		$selectSaleDB = myquery("SELECT idS, nameS, capacS FROM ras_sala ORDER BY idS DESC ");
		$ifExistSDB = mysql_num_rows($selectSaleDB);
			
		if($ifExistSDB >= 1) {
			$nmbrCounter = 1;
			while($pSDB = mysql_fetch_array($selectSaleDB)) {
				$idSale = $pSDB['idS'];
				$imeSale = $pSDB['nameS'];
				$kapacSale = $pSDB['capacS'];
				
				?>
				<div class = "sectionP0">
					<div id = "sectionP1"><?=$nmbrCounter?></div>
					<div id = "sectionP2"><?=$imeSale?></div>
					<div id = "sectionP3"><?=$kapacSale?></div>
					<div id = "sectionP4">
						<a href = "javascript: void(0)" onClick="javascript:popuniSalaPolja('<?=$imeSale?>','<?=$kapacSale?>', '<?=$idSale?>', 'salaModify'); scroll(0,95)" ><img  src = "images/16x16/log_edit.png" alt = "Uredi salu" title = "Uredi salu" border = "0" /></a> |
						<a href = "javascript: void(0)" onClick="javascript:izbrisi('Zelim izbrisati salu: <?=$imeSale?> ?', '?sta=studentska/raspored&uradi=sale&do=brisi&idS=<?=$idSale?>')"><img src = "images/16x16/brisanje.png" alt = "Brisi salu" title = "Brisi salu" border = "0" /></a>
					</div>
					<div class = "razmak"></div>
				</div>
				<?
				$nmbrCounter++;
			}
		} else
			echo "SALE NISU JOS DEFINISANE";
	}
		
	if($_GET['do'] == "brisi") {
		brisiSalu(my_escape($_GET['idS']));
	}
			
} //Kraj kreiranja sala

//Brisanje sala
function brisiSalu($idSale) {
	$deleteSDB = myquery("DELETE FROM ras_sala WHERE idS = '".$idSale."' ");
	if($deleteSDB) {
		//Osvjezi prozor da bi se izbrisala iz liste sala
		?>
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
		<?
				
		printInfo("Sala uspjesno obrisana", false);
	} else {
		printInfo("GRESKA, prilikom brisanja sale");
	}
}

//Ispis <option> predmeta vezanih za neki smijer i godinu
function ispisiElementDana($dan, $int, $grupa, $semestar) {
	$danDivId = strtolower($dan);
	
	echo '
		<div class = "cRDanBox" id="'.$danDivId.'">
			<span style = "float: left"><b>'.$dan.'</b></span> 
			<span style = "float: right"><a href="javascript:addPredmet('.$int.');">[+]</a></span>
			<br/><br/>
			<!-- ################################################################## -->
			<!--<div class = "cRDanNoBox">1</div>-->
			<div class = "cRDanContBoxPredmet">
				<input type="checkbox" name = "izborniPredmet" onClick = "javascript:izaberiGrupu('.$grupa.', '.$semestar.');"/> Izborni Predmet<br/>
				<select name="grupa" id="grupa" disabled="disabled"><option value="0">- - - -</option></select>
				<p>
				<select name="predmet['.$int.'][1]">
					<option></option>
				</select>
				</p>
				<input type="radio" name="tipP['.$int.'][1]" VALUE="P">Predavanje<BR>
				<input type="radio" name="tipP['.$int.'][1]" VALUE="T">Tutorial<BR>
				<input type="radio" name="tipP['.$int.'][1]" VALUE="L">Labaratorijska vjezba
			</div>
			<span class = "cRDanContBoxV">
				Vrijeme:<br/>
				<span>
					<select name="h['.$int.'][1]">
						<option></option>
					</select>
				</span>
				<span>
					<select name="min['.$int.'][1]">
						<option></option>
					</select>
				</span>
			</span>
			<span class = "cRDanContBoxS">
				Sala:<br/>
				<select name="s['.$int.'][1]">
					<option></option>
				</select>
			</span>
		</div>
	';
}

//Kreiraj raspored
function napraviRaspored() {
	global $json;
	
	$unlock_div = "none";
	$ajax = 'onChange = "javascript:promjenaGodine(\'godinaSCSS\')";';
	
	if($_POST) {
		//printInfo("Sala uspjesno dodana", true);
		$disable_all = "DISABLED";
		$unlock_div = "";
		
		echo "ppp: ".$_POST['akademska_godina'];
	}

	if($_POST['submit']) {
		echo "laaa";
	} else {
	?>
		<script language="JavaScript" type="text/javascript">
			//Funkcija za dinamicko mijenjanje godine studija u zavisnosti od smijera
			function promjenaGodine(ob) {
				sel = getSelected(ob);
				//selSem = getSelected(document.getElementById(\'semestar\'));
		
				god = document.getElementById('godina');
				//gr = document.getElementById(\'grupa\');
		
				// Godina
				god.options.length=0;
				god.options[0] = new Option("- - - -", 0);
				if (sel == 0) {
					god.disabled=true;
				} else if (sel == 1) {
					god.options[1] = new Option("1. Semestar", 1);
					god.options[2] = new Option("2. Semestar", 2);
					god.disabled=false;
				} else {
					for (i=3; i<=9; i++) {
						god.options[i-2] = new Option(i+". Semestar", i);
					}
					god.disabled=false;
				}

				
			}
			
			function izaberiGrupu(stud, god) {
				ucitajGrupe(stu,god);
			}
		
			function izaberiGrupu(selStu,selGod) {
				//selGod = getSelected(ob);
				//selStu = getSelected(document.getElementById('studij'));
		
				if (selStu && selGod) {
					// Grupe
					gr = document.getElementById('grupa');
					gr.options.length=0;
					gr.options[0] = new Option("- - - -", 0);
					if (labgrupe[selStu+"-"+selGod]) {
						for (i=1; i<=labgrupe[selStu+"-"+selGod].length; i++) {
							gr.options[i] = new Option(labgrupe[selStu+"-"+selGod][i-1].naziv, labgrupe[selStu+"-"+selGod][i-1].id);
						}
						gr.disabled=false;
					} else {
						gr.disabled=true;
					}
				}
			}

			function getSelected(ob) {
				for (i = 0; i < ob.options.length; i++) {
					if (ob.options[i].selected) {
						return ob.options[i].value;
					}
				}
			}
			<?
			if ($lgS = myquery("SELECT lg.naziv, lg.id, pk.studij, pk.semestar FROM ponudakursa pk, labgrupa lg WHERE lg.predmet = pk.id ORDER BY lg.naziv ASC")) {
				while ($row = mysql_fetch_array($lgS)) {
					$labG[$row['studij'].'-'.$row['semestar']][] = array("id" => $row['id'], "naziv" => $row['naziv']);
				}
				echo "var labgrupe = ".$json->encode($labG).";";
			}
			?>
		</script>
		<div class = "velikiNaslov">
			Definisi novi raspored:
		</div>
		<hr style = "border-top: 1px dotted #ccc; color: #FFF">
			
		<form name = "rasP" id = "rasP" action="" method = "post">
			<div class = "formLS">Akademska godina:</div>
			<div class = "formRS"><?=selectOption("akademska_godina", array("id", "naziv"), false, $disable_all)?></div>
			<div class = "razmak"></div>
			<div class = "formLS">Smijer:</div>
			<div class = "formRS"><?=selectOption("studij", array("id", "naziv"), 'onchange="promjenaGodine(this)"', $disable_all)?></div>
			<div class = "razmak"></div>
			<div class = "formLS">Godina studija:</div>
			<div class = "formRS" id = "godinaSCSS"><select name="godina" id="godina" disabled="disabled"><option value="0">- - - -</option></select></div>
			<div class = "razmak"></div>
			<br/>
			<script language="JavaScript" type="text/javascript">
				function addElement(inEl, elTag, inHtml, otherD) {
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

				var dani = {1:"ponedjeljak",2:"utorak",3:"srijeda",4:"cetvrtak",5:"petak"};
				var nums = {1:1, 2:1, 3:1, 4:1, 5:1};

				function addPredmet(id) {
					nums[id] += 1;
					addHtml = '<div class="razmak"></div><hr><div class = "cRDanContBoxPredmet">'+
						'<input type="checkbox" name = "izborniPredmet"/> Izborni Predmet'+
						'<p><select name="predmet['+id+']['+nums[id]+']"><option></option></select></p>'+
						'<input type="radio" name="tipP['+id+']['+nums[id]+']" VALUE="P">Predavanje<BR>'+
						'<input type="radio" name="tipP['+id+']['+nums[id]+']" VALUE="T">Tutorial<BR>'+
						'<input type="radio" name="tipP['+id+']['+nums[id]+']" VALUE="L">Labaratorijska vjezba'+
						'</div>'+
						'<span class = "cRDanContBoxV">Vrijeme:<br/>'+
						'<span><select name="h['+id+']['+nums[id]+']"><option></option></select></span>'+
						'<span><select name="min['+id+']['+nums[id]+']"><option></option></select></span>'+
						'</span>'+
						'<span class = "cRDanContBoxS">Sala:<br/>'+
						'<select name="salaP['+id+']['+nums[id]+']"><option></option></select>'+
						'</span>';

					addElement(dani[id],'div',addHtml,{id:'mkdiv'});
				}
			</script>

			<input type = "submit" name = "kl" value = "Ucitaj predmete"/>
			<br/><br/>

			<div style = "display: <?=$unlock_div?>">
				<?
				ispisiElementDana("Ponedjeljak", 1, $_POST['studij'], $_POST['godina']);
				ispisiElementDana("Utorak", 2, $_POST['studij'], $_POST['godina']);
				ispisiElementDana("Srijeda", 3, $_POST['studij'], $_POST['godina']);
				ispisiElementDana("Cetvrtak", 4, $_POST['studij'], $_POST['godina']);
				ispisiElementDana("Petak", 5, $_POST['studij'], $_POST['godina']);
				?>
				<div class = "razmak"></div>
				<input type = "submit" name = "submit" value = "Spremi">
			</div>
		</form>
		<?
	} // if ($POST) ... else ...

} //Kraj kreiranja rasporeda

//Uredi raspored
function urediRaspored() {
	if($_POST) {
		echo "dddd";
	} else {
		?>
		<form action = "" method = "post">
			<input type = "submit" name = "a" value = "PPP"/>
			<input type = "submit" name = "a2" value = "PPP2"/>
		</form>
		<?
	}
} //Kraj uredjivanja rasporeda


//Navigacija administracije
function navigacija () {
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
function sadrzaj() {
	$gdje = $_GET['uradi'];
	
	?>
	<div id = "sadrzajA">
		<?pozicija($gdje)?>
	</div>
	<div class = "razmak"></div>
	<?
}

// Izbacuje sve znakove iz stringa osim slova, brojeva, - i _ i mjenja space sa -
function stringToText($string, $toLower = false) {
	if (!$string){
		return false;
	} else {
		$string = preg_replace ('/[^a-zA-Z0-9\-_\ ]/', '', $string);
		$string = preg_replace("/ /", "-", $string);
		
		if ($toLower == true) {
			return strtolower($string);
		} else {
			return $string;
		}
	}
}
	
//
function dan($dan) {
	$d = array (1 => "Ponedjeljak", 2=>"Utorak", 3=>"Srijeda", 4=>"Cetvrtak", 5=>"Petak", 6=>"Subota", 7=>"Nedjelja");

	return $d[$dan];
}
	
//Ispisuje prozor (popup) sa obavjestenjem
function printInfo($text, $reloadBack = false) {
	if($reloadBack == true) {
		$reload = 'setTimeout("window.location=\'\'", 0);';
		echo "<img src = 'img/load.gif' alt = 'Ucitavanje'/> Ucitavam...";
	}
		
	echo '
		<script language="JavaScript" type="text/javascript">
			alert(\''.$text.'\');
			
			'.$reload.'
		</script>
	';
}



?>