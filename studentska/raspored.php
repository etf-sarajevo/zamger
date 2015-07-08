<LINK href="css/raspored.css" rel="stylesheet" type="text/css">
<script language="JavaScript" type="text/javascript">
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
			
			//Funkcija koja prebacuje korisnika na slijedeci korak u wizzardu
			function nextStep() {
				var x = document.getElementById("rasP")
				x.action = "./?sta=studentska/raspored&uradi=novi&step=kraj"
				x.submit()
			}
			
			//Funkcija za validaciju popunjenih formi
			function validacija() {
				var predmet = document.getElementById("predmet").value;
				var sala = document.getElementById("sala").value;
				var poruka;
				var error = 0;
				
				if(predmet == 0) {
					poruka = "Predmet nije izabran!\n";
					error++;
				}
				
				if(sala == 0) {
					poruka = "Sala nije izabrana!\n";
					error++;
				}
				
				if(error > 0)
					alert(poruka);
				else
					document.getElementById("rasP").submit();
			}
			
			//Radi prenosa imena predmeta u tmp raspored (uzima text iz select drop downa)
			function popuniPolje(polje, sakriveno) {
				var x = document.getElementById(polje);
				
				document.getElementById(sakriveno).value = x.options[x.selectedIndex].text;
			}
			
			
			function getSelected(ob) {
				for (i = 0; i < ob.options.length; i++) {
					if (ob.options[i].selected) {
						return ob.options[i].value;
					}
				}
			}
		</script>
<?
//Poziva json klasu
require_once "classes/class.json.php";
$json = new Services_JSON; //Nova instanca klase

function studentska_raspored () {
	global $userid,$user_siteadmin,$user_studentska, $db, $main;

	// Provjera privilegija
	if (!$user_studentska && !$user_siteadmin) {
		zamgerlog("nije studentska",3); // 3: error
		zamgerlog2("nije studentska");
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
		$lok = "selected";
			
		echo $lok;
	}
	else { //za sadrzaj
		if($lokacija == 'pocetak') {
			$sadrzaj = ispisPocetne();
		}
		else if($lokacija == 'sale') {
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
			function popuniSalaPolja(sala, kapacitet, tip, idS, idM) {
				document.saleF.salaS.value=sala;
				document.saleF.salaS.style.background = "#FF7578";
				document.saleF.salaS.style.border = "1px solid #AB070C";
				document.saleF.salaS.style.color = "#fff";
				
				document.saleF.tipSale.value=tip;
				document.saleF.tipSale.style.background = "#FF7578";
				document.saleF.tipSale.style.border = "1px solid #AB070C";
				document.saleF.tipSale.style.color = "#fff";
				
				document.saleF.kapacitetS.value=kapacitet;
				document.saleF.kapacitetS.style.background = "#FF7578";
				document.saleF.kapacitetS.style.border = "1px solid #AB070C";
				document.saleF.kapacitetS.style.color = "#fff";
				
				document.saleF.submit_sala.value="Modifikuj salu";
				
				document.getElementById(idM).innerHTML = "(Modifikacija sale: "+ sala +") <span style = \"font-size:10px; font-weight:normal\">| <a href = \"?sta=studentska/raspored&uradi=sale\">Ponisti modifikaciju</a></span>";
				
				document.saleF.modify.value = idS;
			}
			</script>
			<?
			$sadrzaj = napraviSale();
		}
		else if($lokacija == 'novi') {
			$sadrzaj = napraviRaspored();
		}
		else if($lokacija == 'pogledaj') {
			$sadrzaj = pogledajRasporede();
		} else if($lokacija == 'brisiRaspored') {
			$sadrzaj = brisiRaspored();
		} else 
			echo "Dobrodosli u administraciju rasporeda";
		
		echo $sadrzaj;
	}
}
	
//Select option u formi
function selectOption($tablica, $elementi, $values, $name = false, $distinct = false) {
	/*
	* Mogucnost prikaza defaultnog prvog elementa. Ako nije preciziran, u tom slucaju postavi default na "- - -" sa vrijednoscu optiona = 0.
	* U suprotnom, iskoristi zadanu vrijednust, prenesenu funkcijom. (Primijer kod koristenja grupe (Sve grupe je defaultni select sa vrijednoscu "all").
	*/
	if($values['optionV'] == NULL) {
		$values['optionV'] = 0;
		$values['optionVv'] = "- - -";
	}
	
	if($distinct == false)
		$selectS = "*";
	else
		$selectS = $distinct;
	
	/*
	* Ako nije zadato ime selecta, automatski se dodjeljuje ime kao ime tablice u bazi, medjutim, dolazi nekada do potrebe 
	* reimenovanja selecta, kao sto je slucaj ako imamo SQL select iz vise tablea, onda je nemoguce da name ima takvo ime.
	* Drugi slucaj je koristenje javascripte (ovdje naglaseno ajaxa), jer se JS-om definise tacno zadato ime, koje odredjuje name 
	* objekta.
	*/
	if($name != false) {
		$ret1 .= '<select name = "'.$name.'" id = "'.$name.'" '.$values['ajax'].' '.$values['disable'].'><option value="'.$values['optionV'].'">'.$values['optionVv'].'</option>';
	} else {
		$ret1 .= '<select name = "'.$tablica.'" id = "'.$tablica.'" '.$values['ajax'].' '.$values['disable'].'><option value="'.$values['optionV'].'">'.$values['optionVv'].'</option>';
	}
	
	$selectO = myquery("SELECT ".$selectS." FROM ".$tablica." ".$values['sql_uslov']." ");
	while($sO = mysql_fetch_array($selectO)) {
		$ret1 .= '<option value = "'.my_escape($sO[$elementi[0]], true).'" >'.my_escape($sO[$elementi[1]])." ".my_escape($sO[$elementi[2]]).'</option>';
	}
	
	$ret1 .= '</select>';
	
	return $ret1;
}

##################################################################################
#
#									KREIRANJE POCETNE STRANICE
#
##################################################################################
/**
* Ispis pocetne stranice administracije
* U ovom modulu se ispisuju: 
*
* - spiska predmeta po semestrima i odsjecima
* - spisak grupa studenata za svaki predmet
* - pregled anga�mana nastavnika na pojedinim predmetima
* 
*/

//Funkcija za ispis html-a
function ispisPocetne() {
	echo '<br/>';
	
	########################################
	echo '
		<div><a href = "#" onclick="daj_stablo(\'sp\'); document.getElementById(\'formP0\').reset();"><img id = "img-sp" src = "images/plus.png" border = "0" align = left hspace = 2/>Spiska predmeta po semestrima i odsjecima</a><hr style = "background-color: #ccc; height: 0px; border: 0px; padding-bottom: 1px"></div>
		<div id = "sp" style = "display: none; padding-bottom: 15px; line-height: 18px;">
			<div style = "height: 150px;">
				<div style = "width: 35%; float: left;">Naziv predmeta</div>
				<div style = "width: 29%; float: left;">Odsjek</div>
				<div style = "width: 10%; float: left;">Semestar</div>
				<div style = "width: 12%; float: left;">Akademska godina</div>
				<div style = "width: 12%; float: left;">Obavezan/Izborni</div>
				<div class = "razmak"></div>
				<hr>
				<div style="height:80%;overflow:auto; padding: 5px;">
			
	';
	//SQL ispis 
	$selectSQLP1 = myquery("SELECT b.naziv AS 'n_predmet', c.naziv AS 'n_studij', a.semestar, d.naziv AS 'n_ag', a.obavezan FROM ponudakursa a, predmet b, studij c, akademska_godina d WHERE b.id = a.predmet AND c.id = a.studij AND d.id = a.akademska_godina ORDER BY a.studij ASC");
	while($sP1 = mysql_fetch_array($selectSQLP1)) {
		if($sP1['obavezan'] == 1)
			$ob = "Obavezan";
		else
			$ob = "Izborni";
		echo '
			<div style = "width: 36%; float: left; border-bottom: 1px dotted #ccc; ">'.$sP1['n_predmet'].'</div>
			<div style = "width: 30%; float: left; border-bottom: 1px dotted #ccc;">'.$sP1['n_studij'].'</div>
			<div style = "width: 10%; float: left; border-bottom: 1px dotted #ccc;">'.$sP1['semestar'].'</div>
			<div style = "width: 12%; float: left; border-bottom: 1px dotted #ccc;">'.$sP1['n_ag'].'</div>
			<div style = "width: 12%; float: left; border-bottom: 1px dotted #ccc;">'.$ob.'</div>
			<div class = "razmak"></div>
		 ';
	}
	
	/**
	*	Polje hidden (studijNameH) koristi se za ispis imena studija (odsjeka) selektovanog u drop boxu.
	*	Koristi se zbog toga da bi se u printu mogao ispisati smijer koji se printa.
	*/
	echo '
				</div>
			</div>
		<br/><font color = "#000">
			<form name = "formP0" id = "formP0" action = "studentska/print.php?act=SiO" target = "_blank" method = "post">
				Printanje spiskova: <b>Studij:</b> '.selectOption("studij", array("id", "naziv"), array("ajax"=>"onChange = \"javascript:popuniPolje('studij', 'studijNameH')\"")).' &nbsp;&nbsp;
				<input type = "hidden" name = "studijNameH" id = "studijNameH" />
				<b>Semestar:</b> '.selectOption("ponudakursa", array("semestar", "semestar"), array("sql_uslov"=>"ORDER BY semestar ASC"), false, "DISTINCT(semestar)").'</font> 
				&nbsp;&nbsp;<button><img src = "images/16x16/Icon_Print.png" border = "0"></button>
			</form>
		</div>
	';
	
	#######################################
	echo '
		<div><a href = "#" onclick="daj_stablo(\'sg\'); document.getElementById(\'formP1\').reset();"><img id = "img-sg" src = "images/plus.png" border = "0" align = left hspace = 2/>Spisak grupa studenata za svaki predmet</a><hr style = "background-color: #ccc; height: 0px; border: 0px; padding-bottom: 1px"></div>
		<div id = "sg" style = "display: none; padding-bottom: 15px;">
			<form name = "formP1" id = "formP1" action = "studentska/print.php?act=PG" target = "_blank" method = "post">
				Printanje grupa za pedmet: '.selectOption("predmet", array("id", "naziv"), array("ajax"=>"onChange = \"javascript:popuniPolje('predmet', 'predmetNameH')\"")).' &nbsp;&nbsp;
				<input type = "hidden" name = "predmetNameH" id = "predmetNameH" />
				<button><img src = "images/16x16/Icon_Print.png" border = "0"></button>
			</form>
		</div>
	';
	
	#######################################
	echo '
		<div><a href = "#" onclick="daj_stablo(\'pp\'); document.getElementById(\'formP2\').reset();"><img id = "img-pp" src = "images/plus.png" border = "0" align = left hspace = 2/>Pregled angazmana nastavnika na pojedinim predmetima</a><hr style = "background-color: #ccc; height: 0px; border: 0px; padding-bottom: 1px"></div>
		<div id = "pp" style = "display: none; padding-bottom: 15px;">
			<form name = "formP2" id = "formP2" action = "studentska/print.php?act=PP" target = "_blank" method = "post">
				Printanje profesora za pedmet: '.selectOption("osoba", array("id", "ime", "prezime"), array("ajax"=>"onChange = \"javascript:popuniPolje('nastavnik', 'imeNastavnika')\"", "sql_uslov"=>"WHERE nastavnik = 1"), "nastavnik").' &nbsp;&nbsp;
				<input type = "hidden" name = "imeNastavnika" id = "imeNastavnika" />
				<button><img src = "images/16x16/Icon_Print.png" border = "0"></button>
			</form>
		</div>
	';
	
	
}


##################################################################################
#
#										KREIRANJE SALA
#
##################################################################################
function napraviSale() {
	if($_POST) {
		$salaS = my_escape($_POST['salaS']);
		$kapacitetS = my_escape($_POST['kapacitetS']);
		$tipS = my_escape($_POST['tipSale']);
		
		$salaModif = my_escape($_POST['modify']);
		
		//Ako je parametar != 0, uradi update baze, u suprotnom ubaci novi red u bazu
		if($salaModif != 0) {
			$updateDBS = myquery("UPDATE raspored_sala SET naziv = '".$salaS."', kapacitet = '".$kapacitetS."', tip = '".$tipS."' WHERE id = '".$salaModif."' ");
			if($updateDBS) {
				printInfo("Sala uspjesno modifikovana", true);
			} else {
				printInfo("Greska pri modifikaciji sale", true);
			}
		} else {
			$insertIntoDBS = myquery("INSERT INTO raspored_sala (naziv, kapacitet, tip) VALUES ('".$salaS."', '".$kapacitetS."', '".$tipS."') ");
			if($insertIntoDBS) {
				printInfo("Sala uspjesno dodana", true);
			} else {
				printInfo("Greska pri dodavanju sale", true);
			}
		}
	} else {
		?>
		<div class = "velikiNaslov">
			Definisanje sala: <span id = "salaModify" style = "color: #ff0000"></span>
		</div>
		<hr style = "border-top: 1px dotted #ccc; color: #FFF">
		<form name = "saleF" id = "saleF" action="./?sta=studentska/raspored&uradi=sale" method = "post">
			<div>
				<div class = "formLS">Unesi oznaku sale: (primjer: S01)</div>
				<div class = "formRS"><input type = "textbox" value = "" name = "salaS" size = "30" MAXLENGTH="10"></div>
				<div class = "razmak"></div>
				
				<div class = "formLS">Unesi tipa sale:</div>
				<div class = "formRS">
					<select name = "tipSale">
						<option value = "Amfiteatar">Amfiteatar</option>
						<option value = "Labaratorija">Labaratorija</option>
						<option value = "Kabinet">Kabinet</option>
					</select>
				</div>
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
			<div id = "sectionP2a" style = "font-weight: bold">Tip sale</div>
			<div id = "sectionP3" style = "font-weight: bold">Kapacitet</div>
			<div id = "sectionP4" style = "font-weight: bold">Akcije</div>
			<div class = "razmak"></div>
		</div>
		<?
			
		//Ispis sala za modifikaciju
		$selectSaleDB = myquery("SELECT id, naziv, kapacitet, tip FROM raspored_sala ORDER BY id DESC ");
		$ifExistSDB = mysql_num_rows($selectSaleDB);
			
		if($ifExistSDB >= 1) {
			$nmbrCounter = 1;
			while($pSDB = mysql_fetch_array($selectSaleDB)) {
				$idSale = $pSDB['id'];
				$imeSale = $pSDB['naziv'];
				$tipSale = $pSDB['tip'];
				$kapacSale = $pSDB['kapacitet'];
				
				?>
				<div class = "sectionP0">
					<div id = "sectionP1"><?=$nmbrCounter?></div>
					<div id = "sectionP2"><?=$imeSale?></div>
					<div id = "sectionP2a"><?=$tipSale?></div>
					<div id = "sectionP3"><?=$kapacSale?></div>
					<div id = "sectionP4">
						<a href = "javascript: void(0)" onClick="javascript:popuniSalaPolja('<?=$imeSale?>','<?=$kapacSale?>', '<?=$tipSale?>', '<?=$idSale?>', 'salaModify'); scroll(0,95)" ><img  src = "images/16x16/log_edit.png" alt = "Uredi salu" title = "Uredi salu" border = "0" /></a> |
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

##################################################################################
#
#										BRISANJE SALA
#
##################################################################################
function brisiSalu($idSale) {
	$deleteSDB = myquery("DELETE FROM raspored_sala WHERE id = '".$idSale."' ");
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

//Samo vraca predmete
function ispisiPredmeteBox($studij, $semestar, $akademska) {
	$selectPredmeteDB = myquery("SELECT a.predmet, b.kratki_naziv FROM ponudakursa a, predmet b WHERE a.studij = '".my_escape($studij)."' AND a.semestar = '".my_escape($semestar)."' AND a.akademska_godina = '".$akademska."' AND b.id=a.predmet");
		while($sPDB = mysql_fetch_array($selectPredmeteDB)) {
			$ispis .= '<option value = "'.$sPDB['predmet'].'">'.$sPDB['kratki_naziv'].'</option>';
		}
	
	return $ispis;
}

				
function predmetLista($grupa) {
	$prS = myquery("SELECT b.id, b.kratki_naziv FROM labgrupa a, predmet b WHERE b.id = a.predmet AND a.id = '".$grupa."' ");
	while($rW = mysql_fetch_array($prS)) {
		$predmetLista .= '<option value = "'.$rW['id'].'">'.$rW['kratki_naziv'].'</option>';
	}
					
	return $predmetLista;
}

			
##################################################################################
#
#										KREIRANJE RASPOREDA
#
##################################################################################
function napraviRaspored() {

	global $json, $ispis, $val;
	
	$unlock_div = "none";
	$ajax = 'onChange = "javascript:promjenaGodine(\'godinaSCSS\')";';
	
	?>
	<div class = "velikiNaslov">
			Kreiranje novog rasporeda:
	</div>
	<hr style = "border-top: 1px dotted #ccc; color: #FFF">
	<div  class = "wizardEl1" <?if($_GET['step']=='unosP' || $_GET['step']=='tut_lab' || $_GET['step']=='kraj') echo 'style = "color: #8cc689; font-weight:bold"'; else if($_GET['uradi']=='novi') echo 'style = "color: #000; font-weight:bold"';?>>Osnovne postavke rasporeda</div>
	<div  class = "wizardEl1" <?if($_GET['step']=='unosP') echo 'style = "color: #000; font-weight:bold"'; else if($_GET['step']=='tut_lab' || $_GET['step']=='kraj') echo 'style = "color: #8cc689; font-weight:bold"';?>>Unos predmeta</div>
	<div  class = "wizardEl1" <?if($_GET['step']=='kraj') echo 'style = "color: #000; font-weight:bold"';?>>Generisanje rasporeda</div>
	<div class = "razmak"></div>
	<div id = "wizardBox">
		<div class = "wizardBoxInner" <?if($_GET['step']=='unosP' || $_GET['step']=='tut_lab' || $_GET['step']=='kraj') echo 'style = "background: #a6ffa2"'; else if($_GET['uradi']=='novi') echo 'style = "background: #fcf300"';?>></div>
		<div class = "wizardBoxInner" <?if($_GET['step']=='unosP') echo 'style = "background: #fcf300"'; else if($_GET['step']=='tut_lab' || $_GET['step']=='kraj') echo 'style = "background: #a6ffa2"';?>></div>
		<div class = "wizardBoxInner" <?if($_GET['step']=='kraj') echo 'style = "background: #fcf300"';?>></div>
		<div class = "razmak"></div>
	</div>
	<?
	
	if($_GET['step'] == "kraj") { //Stranica na kojoj potvrdjujemo kreiranje rasporeda
	
		
		foreach ($_SESSION['tmpRas'] as $vale) {
			$tmp[] = array("x" => $vale['x'], "y" => $vale['y'], "predmet" => $vale['predmet'], "sala" => $vale['sala'], "tip" => $vale['tip'], "grupa" => $vale['grupa']);
		}
		$arr = $tmp;
		
		function ispis($arrP) {
			//$insertRas = myquery("INSERT INTO ras_ras VALUES ('', '".$_POST['godina']."', '".$_POST['studij']."', '".$_POST['akademska_godina']."')");
			$selR = mysql_fetch_assoc(myquery("SELECT MAX(raspored) AS idas, smijerR, godinaR, semestarR FROM raspored_stavka GROUP BY raspored"));
			$idN = $selR['idas'] + 1;
			
			if($_POST['studij'] == $selR['smijerR'] AND $_POST['akademska_godina'] == $selR['godinaR'] AND $_POST['godina'] == $selR['semestarR'])
				echo "Raspored za izabrani smijer na ".$_POST['godina']." semestru vec postoji.";
			else {
			
				$kreiran = 0;
				foreach ($arrP as $val) {
					//echo join(", ", $val)."<br/>";
					if($val['x'] == "")
						$none;
					else {
						if(myquery("INSERT INTO raspored_stavka (raspored, dan_u_sedmici, predmet, vrijeme_pocetak, vrijeme_kraj, smijerR, godinaR, semestarR, sala, tip, labgrupa) VALUES ('".$idN."', '".$val['y']."', '".$val['predmet']."', '".$val['xp']."', '".$val['xe']."', '".$_POST['studij']."', '".$_POST['akademska_godina']."', '".$_POST['godina']."', '".$val['sala']."', '".$val['tip']."', '".$val['grupa']."')"))
							$kreiran++;
					}
				}
				if($kreiran > 0)
					echo "<font size = 3><b>Raspored kreiran.</b></font>";
				else	
					echo "<b>GRESKA!!!</b>";
			}
		}
		
		function grupisi($niz) {
			//$tmpSes = $_SESSION['tmpRas'];
			global $arr; $tmpSes = $niz;
			$size = sizeof($tmpSes);
			for ($i=0; $i<$size; $i++) {
				if ($tmpSes[$i]) {
					$tmpSes[$i]['xp'] = $tmpSes[$i]['x']; $tmpSes[$i]['xe'] = $tmpSes[$i]['x'];
					for ($j=0; $j<$size; $j++) {
						if ($tmpSes[$j] AND $tmpSes[$i]['y'] == $tmpSes[$j]['y'] AND $tmpSes[$i]['predmet'] == $tmpSes[$j]['predmet'] AND $tmpSes[$i]['sala'] == $tmpSes[$j]['sala'] AND $tmpSes[$i]['tip'] == $tmpSes[$j]['tip'] AND $tmpSes[$i]['grupa'] == $tmpSes[$j]['grupa'] AND $i!=$j) {
							if ($tmpSes[$i]['xp']>$tmpSes[$j]['x']) {
								$tmpSes[$i]['xp']=$tmpSes[$j]['x'];
							}
							if ($tmpSes[$i]['xe']<$tmpSes[$j]['x']) {
								$tmpSes[$i]['xe']=$tmpSes[$j]['x'];
							}
							unset($tmpSes[$j]);
						}
					}
				}
			}
			return $tmpSes;
		}
		
		//ispis($arr);
		echo "<hr/>";
		$done = grupisi($arr);
		ispis($done);
		
	} else if($_GET['step'] == "unosP") { //Stranica za popunjavanje rasporeda predmetima
	
		//Funkcija za provjeru vec upisanih predmeta u neki termin
		function imaLiPredmet($x, $y, $tip, $grupa, $sala) {
			foreach ($_SESSION['tmpRas'] AS $val) {
				if ($val['x'] == $x AND $val['y'] == $y) {
					if ($val['tip'] == 'P' OR $tip == 'P') // Za predavanje
						return "P";
					else if ($val['grupa'] == $grupa OR $val['grupa'] == "0" OR $grupa == "0") // Ako je ista grupa ili sve grupe
						return "G";
					else if ($val['sala'] == $sala) // Za salu
						return "S";
				}
			}
			return false;
		}
		
		echo '<b>Izgled rasporeda (preview):</b><br/><br/>';
		
		
		if($_SESSION['tmpRas'] == NULL) {
			$_SESSION['tmpRas'][] = "";
		} else {
			if(imaLiPredmet($_POST['odT'], $_POST['dan'], $_POST['tip'], $_POST['grupa'], $_POST['sala']) == "P")
				echo "<font style = 'color: #ff0000; font-weight: bold'>GRESKA! Vec postoji predmet u rasporedu koji se odrzava u prethodno odabranom terminu.</font><br/><br/>";
			else if(imaLiPredmet($_POST['odT'], $_POST['dan'], $_POST['tip'], $_POST['grupa'], $_POST['sala']) == "G")
				echo "<font style = 'color: #ff0000; font-weight: bold'>GRESKA! Grupa koju ste odabrali vec ima cas u datom terminu.</font><br/><br/>";
			else if(imaLiPredmet($_POST['odT'], $_POST['dan'], $_POST['tip'], $_POST['grupa'], $_POST['sala']) == "S")
				echo "<font style = 'color: #ff0000; font-weight: bold'>GRESKA! Vec postoji predmet u rasporedu koji se odrzava u prethodno odabranoj SALI.</font><br/><br/>";
			else {
				$key = substr(md5(uniqid(rand(), true)), 0, 10);
				
				if($_POST['grupa'] == "0")
					$grupaIspis = "Svi";
				else
					$grupaIspis = $_POST['sakrivenoPoljeG'];
				
				if($_POST['tip'] == "P")
					$boxIspis = '<div style = "float: left"><a style = "color: #ff0000" href = "javascript:izbrisi(\'Izbrisati cas?\', \'showTmpRas.php?id='.$key.'\')" >x</a></div><div style = "float: right"><b>'.$_POST['sakrivenoPoljeP'].'</b></div><div class = "razmak"></div>Predavanje<br/>('.$grupaIspis.')<br/>Sala: '.$_POST['sakrivenoPoljeS']; 
				else {
					$boxIspis = '<a style = "color: #ff0000" href = "javascript:izbrisi(\'Izbrisati cas?\', \'showTmpRas.php?id='.$key.'\')" >x-'.$_POST['sakrivenoPoljeP'].'</a>';
					$popupIspis = $_POST['tip'].'&nbsp;&nbsp;&nbsp;<b>'.$_POST['sakrivenoPoljeP'].'</b><br/>'.$grupaIspis.'<br/>Sala: '.$_POST['sakrivenoPoljeS'].'<hr>';
				}
				$_SESSION['tmpRas'][] = array("key"=>$key, "x"=>$_POST['odT'], "y"=>$_POST['dan'], "predmet"=>$_POST['predmet'], "sala"=>$_POST['sala'], "tip"=>$_POST['tip'], "grupa"=>$_POST['grupa'], "ispisBox"=>$boxIspis, "ispisPopUp"=>$popupIspis);
			}
		}
	
?>
		<script>
			function izaberiGrupu(idG) {
				//selGod = getSelected(ob);
				//selStu = getSelected(document.getElementById('studij'));
				
				sel = getSelected(idG);
				
				if (sel) {
					// Grupe
					gr = document.getElementById('grupa');
					gr.options.length=0;
					gr.options[0] = new Option("- - -", 0);
					if (labgrupe[sel]) {
						for (i=1; i<=labgrupe[sel].length; i++) {
							gr.options[i] = new Option(labgrupe[sel][i-1].naziv, labgrupe[sel][i-1].id);
						}
						gr.disabled=false;
					} else {
						gr.disabled=true;
					}
				}
			}
			
			<?
			if ($lgS = myquery("SELECT lg.naziv, lg.id, pr.id AS predmetId, pr.kratki_naziv FROM ponudakursa pk, labgrupa lg, predmet pr WHERE lg.predmet = pk.id AND pk.studij = '".$_POST['studij']."' AND pk.semestar = '".$_POST['godina']."' AND pr.id = lg.predmet ORDER BY lg.naziv ASC")) {
				while ($row = mysql_fetch_array($lgS)) {
					$labG[$row['predmetId']][] = array("id" => $row['id'], "naziv" => $row['naziv']);
				}
				echo "var labgrupe = ".$json->encode($labG).";";
			}
			?>
		</script>
		<iframe src = "studentska/showTmpRas.php" style = "width: 750px; height: 380px; border: 0px" frameborder = "0" scrolling = "auto"></iframe>
		
		<form name = "rasP" id = "rasP" action="./?sta=studentska/raspored&uradi=novi&step=unosP"  method = "post">
			<input type="hidden" name="akademska_godina" value="<?=$_POST['akademska_godina']?>" />
			<input type="hidden" name="studij" value="<?=$_POST['studij']?>"/>
			<input type="hidden" name="godina" value="<?=$_POST['godina']?>"/>
			
			<b>Unos predmeta: </b><br/><br/>
			<div class = "formLS">Dan:</div>
			<div class = "formRS">
				<select name="dan">
					<option value="1">Ponedjeljak</option>
					<option value="2">Utorak</option>
					<option value="3">Srijeda</option>
					<option value="4">Cetvrtak</option>
					<option value="5">Petak</option>
				</select>
			</div>
			<div class = "razmak"></div>
			
			<div class = "formLS">Tip:</div>
			<div class = "formRS">
				<select id = "tip" name = "tip">
					<option value = "P">Predavanje</option>
					<option value = "T">Tutorial</option>
					<option value = "L">Labaratorijska vjezba</option>
				</select>
			</div>
			<div class = "razmak"></div>
			
			<div class = "formLS">Predmet:</div>
			<div class = "formRS">
				<select id = "predmet" name = "predmet" onChange = "popuniPolje('predmet', 'sakrivenoPoljeP'); izaberiGrupu(this)">
					<option value = "0">- - -</option>
					<?=ispisiPredmeteBox($_POST['studij'], $_POST['godina'], $_POST['akademska_godina'])?>
				</select>
				<input type="hidden" id="sakrivenoPoljeP" name="sakrivenoPoljeP" />
			</div>
			<div class = "razmak"></div>
			
			<div class = "formLS">Grupa:</div>
			<div class = "formRS">
				<select id = "grupa" name = "grupa" onChange = "popuniPolje('grupa', 'sakrivenoPoljeG')" disabled="disabled">
					<option value="0">- - -</option>
				</select>
				<input type="hidden" id="sakrivenoPoljeG" name="sakrivenoPoljeG" />
			</div>
			<div class = "razmak"></div>
				
			<div class = "formLS">Sala:</div>
			<div class = "formRS">
					<?=selectOption("raspored_sala", array("id", "naziv"), array("ajax"=>'onChange = "popuniPolje(\'sala\', \'sakrivenoPoljeS\')"'), "sala")?>
				<input type="hidden" id="sakrivenoPoljeS" name="sakrivenoPoljeS" />
			</div>
			<div class = "razmak"></div>
				
			<div class = "formLS">Vrijeme:</div>
			<div class = "formRS">
				<select name="odT">
					<option value="1">09:00 - 09:45</option>
					<option value="2">10:00 - 10:45</option>
					<option value="3">11:00 - 11:45</option>
					<option value="4">12:00 - 12:45</option>
					<option value="5">13:00 - 13:45</option>
					<option value="6">14:00 - 14:45</option>
					<option value="7">15:00 - 15:45</option>
					<option value="8">16:00 - 16:45</option>
					<option value="9">17:00 - 17:45</option>
					<option value="10">18:00 - 18:45</option>
				</select>
			</div>
			<div class = "razmak"></div>
			<input type = "submit" id = "submit" name = "submit" value = "Spremi" onClick = "validacija(); return false;">
			<input type = "submit" name = "submit2" onClick = "nextStep()" value = "Slijedeci korak">
		</form>
<?		
			
	} else if($_GET['uradi'] == "novi") { //Stranica za osnovni odabir godine, smijera i semestra
		if($_GET['step'] != "unosP" || $_GET['step'] == "kraj") 
			unset($_SESSION['tmpRas']);
		
?>
	<form name = "rasP" id = "rasP" action="./?sta=studentska/raspored&uradi=novi&step=unosP" method = "post">
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
					for (i=3; i<=6; i++) {
						god.options[i-2] = new Option(i+". Semestar", i);
					}
					god.disabled=false;
				}
			}

			//Ako su selektovana polja studij i semestar (godina), prikazi dugme za ispis, u suprotnom, sakrij ga
			function prikaziDugme() {
				if(document.getElementById('godina').value != 0 && document.getElementById('studij').value != 0 && document.getElementById('akademska_godina').value != 0) {
					document.getElementById('popuniPredmetDiv').style.display = "";
				} else {
					document.getElementById('popuniPredmetDiv').style.display = "none";
				}
			}
		</script>
		<div class = "formLS">Akademska godina:</div>
		<div class = "formRS"><?=selectOption("akademska_godina", array("id", "naziv"), array("ajax"=>'onChange="prikaziDugme()"'))?>
		</div>
		<div class = "razmak"></div>
		<div class = "formLS">Smijer:</div>
		<div class = "formRS"><?=selectOption("studij", array("id", "naziv"), array("ajax"=>'onChange="promjenaGodine(this); prikaziDugme()"'))?>
		</div>
		<div class = "razmak"></div>
		<div class = "formLS">Semestar studija:</div>
		<div class = "formRS" id = "godinaSCSS">
			<select name="godina" id="godina" onChange = "prikaziDugme();" disabled="disabled">
				<option value="0">- - -</option>';
			</select>
		</div>
		<div class = "razmak"></div>
		<br/>
		<div id = "popuniPredmetDiv" style = "display: none">
			<input type = "submit" name = "submit" value = "Korak 2: Popuni obavezne predmete"/>
		</div>
<?
	}

} //Kraj kreiranja rasporeda

##################################################################################
#
#										PREGLED RASPOREDA
#
##################################################################################
function pogledajRasporede() {
	?>
	<div class = "velikiNaslov">
		Kreirani rasporedi:
	</div>
	<hr style = "border-top: 1px dotted #ccc; color: #FFF">
	<?
	
	$brojacRK = 1;
	$sqlRasporediK = myquery("SELECT DISTINCT(a.raspored), pk.semestar, b.naziv AS nStudij, c.naziv AS nAkademska FROM raspored_stavka a, studij b, akademska_godina c, ponudakursa pk WHERE b.id = pk.studij AND c.id = pk.akademska_godina and pk.id=a.predmet ORDER BY a.id DESC");
	if(mysql_num_rows($sqlRasporediK) < 1)
		echo "Nema kreiranih rasporeda";
	else {
		while($sRK = mysql_fetch_array($sqlRasporediK)) {
		
			echo "<div style = 'line-height: 18px'>Raspored no.".$brojacRK." - <b>Odsjek:</b> <font color = '#000'>".$sRK['nStudij']."</font> | <b>Semestar:</b> <font color = '#000'>".$sRK['semestar']."</font> | <b>Akademska godina:</b> <font color = '#000'>".$sRK['nAkademska']."</font> | <a target = '_blank' href = 'studentska/print.php?act=rasporedFull&id=".$sRK['raspored']."&nazivS=".$sRK['nStudij']."'><img src = 'images/16x16/raspored.png' border = '0' alt = 'Printaj cijeli raspored' title = 'Printaj cijeli raspored'></a> | <a target = '_blank' href = 'studentska/print.php?act=sale&id=".$sRK['raspored']."&nazivS=".$sRK['nStudij']."'><img src = 'images/16x16/sale.png' border = '0' alt = 'Printaj sale' title = 'Printaj sale'></a> | <a href = '?sta=studentska/raspored&uradi=brisiRaspored&id=".$sRK['raspored']."'><img src = 'images/16x16/brisanje.png' border = '0' alt = 'Brisi raspored' title = 'Brisi raspored'></a></div>";
		
			$brojacRK++;
		}
	}
	
} //Kraj 

##################################################################################
#
#										BRISANJE RASPOREDA
#
##################################################################################
function brisiRaspored() {
	$id = $_GET['id'];
	
	$sqlBrisi = myquery("DELETE FROM raspored_stavka WHERE raspored = '".$id."' ");
	if($sqlBrisi)
		echo "Raspored izbrisan.";
	else
		echo "GRESKA prilikom brisanja rasporeda.";
}


##################################################################################
#
#										NAVIGACIJA
#
##################################################################################
function navigacija () {
	$gdje = $_GET['uradi'];

	?>
	<div id = "navigacija">
		<ul id = "menu">
			<li><a id="<?pozicija(false, $gdje == "pocetak")?>" href = "?sta=studentska/raspored&uradi=pocetak">Raspored administracija</a></li>
			<li><a id="<?pozicija(false, $gdje == "sale")?>" href = "?sta=studentska/raspored&uradi=sale">Definisi sale</a></li>
			<li><a id="<?pozicija(false, $gdje == "novi")?>" href = "?sta=studentska/raspored&uradi=novi">Napravi novi raspored</a></li>
			<li><a id="<?pozicija(false, $gdje == "pogledaj")?>" href = "?sta=studentska/raspored&uradi=pogledaj">Pogledaj sve rasporede</a></li>
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