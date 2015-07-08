<?

// STUDENTSKA/ANKETA - administracija ankete, studentska služba

function studentska_anketa(){
	global $userid, $user_siteadmin, $user_studentska, $conf_site_url;
	global $_lv_; // Potrebno za genform() iz libvedran
	
	// Provjera privilegija
	
	if (!$user_studentska && !$user_siteadmin) {
		zamgerlog("nije studentska",3); // 3: error
		zamgerlog2("nije studentska"); // 3: error
		biguglyerror("Pristup nije dozvoljen.");
		return;
	}

	// JavaScript
	?>
	<script type="text/javascript">
		function setVal(){
			document.getElementById('tekst_novo_pitanje').value = pitanje_array[document.getElementById('pitanja').selectedIndex];
			document.getElementById('tip_novo_pitanja').selectedIndex = tip_array[document.getElementById('pitanja').selectedIndex];
		}
		var pitanje_array = new Array();
		var tip_array = new Array();
		
		var par=1;
		
		function switch_poredjenje(){
			if (par==1){
				document.getElementById('poredjenje_1').style.display = '';
				par=0;
			} else {
				document.getElementById('poredjenje_1').style.display = 'none';
				par=1;
			}
		}
		var help=1;
		
		function switch_izvjestaj(){
			if (help==1) {
				document.getElementById('semestralni').style.display = '';
				help=0;
			} else {
				document.getElementById('semestralni').style.display = 'none';
				help=1;
			}
		}
		
		var help2=1;
		
		function switch_izvjestaj2() {
			if (help2==1) {
				document.getElementById('po_smjerovima').style.display = '';
				help2=0;
			} else {
				document.getElementById('po_smjerovima').style.display = 'none';
				help2=1;
			}
		}
	</script>
	<?

	// Određujemo akciju
	$akcija = $_REQUEST['akcija'];
	$anketa = intval($_REQUEST['anketa']);
	$id = intval($_REQUEST['anketa']);
	
	// Deaktivacija ankete
	if ($_REQUEST['akcija']=="deaktivacija") {
		$q500 = myquery("update anketa_anketa set aktivna=0 where id=$id");
		$q510 = myquery("update anketa_predmet set aktivna=0 where anketa=$id");
		zamgerlog("deaktivirana anketa $id", 4); // nivo 4 = audit
		zamgerlog2("deaktivirana anketa", $id);
	}
	
	// Aktivacija ankete
	if ($_REQUEST['akcija']=="aktivacija") {
		// Prvo sve ankete postavimo na neaktivne
		$q520 = myquery("update anketa_anketa set aktivna=0");
		$q530 = myquery("update anketa_predmet set aktivna=0 where anketa=$id");

		// ...a zatim datu postavimo kao aktivnu jer u datom trenutku samo jedna anketa može biti aktivna.
		// Automatski postavljamo i to da vise nije moguće editovati pitanja date ankete pošto je postala aktivna
		$q540 = myquery("update anketa_anketa set aktivna=1, editable=0 where id=$id");
		$q550 = myquery("update anketa_predmet set aktivna=1 where anketa=$id");

		print "<center><span style='color:#009900'>Anketa je postavljena kao aktivna!</span></center>";
		zamgerlog("aktivirana anketa $id", 4);
		zamgerlog2("aktivirana anketa", $id);
	}
	
	// Promjena podataka o anketi
	if ($akcija =="podaci") {
		if ($_POST['subakcija']=="potvrda" && check_csrf_token()) {
			$naziv = my_escape($_REQUEST['naziv']);
			$opis = my_escape($_REQUEST['opis']);
			
			$dan = intval($_POST['1day']);
			$mjesec = intval($_POST['1month']);
			$godina = intval($_POST['1year']);
			$sat = intval($_POST['sat1']);
			$minuta = intval($_POST['minuta1']);
			$sekunda = intval($_POST['sekunda1']);
			
			$dan2 = intval($_POST['2day']);
			$mjesec2 = intval($_POST['2month']);
			$godina2 = intval($_POST['2year']);
			$sat2 = intval($_POST['sat2']);
			$minuta2 = intval($_POST['minuta2']);
			$sekunda2 = intval($_POST['sekunda2']);
			
			if (!checkdate($mjesec,$dan,$godina)) {
				niceerror("Odabrani datum je nemoguć");
				return 0;
			}
			if (!checkdate($mjesec2,$dan2,$godina2)) {
				niceerror("Odabrani datum je nemoguć");
				return 0;
			}
			if ($sat<0 || $sat>24 || $minuta<0 || $minuta>60 || $sekunda<0 || $sekunda>60) {
				niceerror("Vrijeme nije dobro");
				return 0;
			}


			$mysqlvrijeme1 = time2mysql(mktime($sat,$minuta,$sekunda,$mjesec,$dan,$godina));
			$mysqlvrijeme2 = time2mysql(mktime($sat2,$minuta2,$sekunda2,$mjesec2,$dan2,$godina2));
			
			$q560 = myquery("update anketa_anketa set naziv='$naziv', datum_otvaranja='$mysqlvrijeme1', datum_zatvaranja='$mysqlvrijeme2', opis='$opis' where id=$anketa");
			zamgerlog("promijenjeni podaci za anketu $anketa", 2);
			zamgerlog2("promijenjeni podaci za anketu", $anketa);
			
			?>
			<script language="JavaScript">
			location.href='?sta=studentska/anketa&anketa=<?=$anketa?>&akcija=edit';
			</script>
			<? 
			return;
		}
		print "<a href='?sta=studentska/anketa&akcija=edit&anketa=$anketa'>Povratak nazad</a>";
		
		$q580 = myquery("select id,UNIX_TIMESTAMP(datum_otvaranja),UNIX_TIMESTAMP(datum_zatvaranja),naziv,opis from anketa_anketa where id=$id");
		$datum_otvaranja = mysql_result($q580,0,1);
		$datum_zatvaranja = mysql_result($q580,0,2);
		$naziv = mysql_result($q580,0,3);
	
		?>
		<center>
		<h2> <?=$naziv?>  - izmjena podataka </h2>
		<?

		$odan = date('d',$datum_otvaranja);
		$omjesec = date('m',$datum_otvaranja);
		$ogodina = date('Y',$datum_otvaranja);
		$osat = date('H',$datum_otvaranja);
		$ominuta = date('i',$datum_otvaranja);
		$osekunda = date('s',$datum_otvaranja);

		$zdan = date('d',$datum_zatvaranja);
		$zmjesec = date('m',$datum_zatvaranja);
		$zgodina = date('Y',$datum_zatvaranja);
		$zsat = date('H',$datum_zatvaranja);
		$zminuta = date('i',$datum_zatvaranja);
		$zsekunda = date('s',$datum_zatvaranja);

		?>
		<?=genform("POST")?>
		<input type="hidden" name="subakcija" value="potvrda">
		<table border="0" width="600">
		<tr>
			<td valign="top" align="right" >
				Naziv: &nbsp; 
			</td>
			<td valign="top">
			<b><input type="text" name="naziv" value="<?=$naziv?>" class="default"></b><br/>
			</td>
		</tr>
		<tr>
			<td valign="top" align="right">
				Datum otvaranja: &nbsp;
			</td>
			<td valign="top">
				<b> <?=datectrl($odan,$omjesec,$ogodina,"1")?>
				<input type="text" name="sat1" size="1" value="<?=$osat?>"> <b>:</b> 
				<input type="text" name="minuta1" size="1" value="<?=$ominuta?>"> <b>:</b> 
				<input type="text" name="sekunda1" size="1" value="<?=$osekunda?>"> <br></b><br/>
			</td>
		</tr>
		<tr>
			<td valign="top" align="right">
				Datum zatvaranja: &nbsp; 
			</td>
			<td valign="top">
				<b><b><?=datectrl($zdan,$zmjesec,$zgodina,"2")?>  
				<input type="text" name="sat2" size="1" value="<?=$zsat?>"> <b>:</b> 
				<input type="text" name="minuta2" size="1" value="<?=$zminuta?>"> <b>:</b> 
				<input type="text" name="sekunda2" size="1" value="<?=$zsekunda?>"> <br></b><br/>
			</td>
		</tr>
		<tr>
			<td valign="top" align="right">
				Opis: &nbsp; 
			</td>
			<td valign="top">
				<b><b><textarea name="opis" cols="50"  rows="15" class="default"><?=mysql_result($q580,0,4)?> </textarea></b><br/>
			</td>
		</tr>
		</table>

		<p>
			<input type="Submit" value=" Izmijeni "></form>
		</p>
		</center>
		<?
	}
	// ++++++++++++++++++++++++++++++ KRAJ AKCIJA PODACI  +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++


	//  *******************   AKCIJA NOVI - dio koji se prikazuje kada se kreira nova anketa ****************************	
	else if ($_POST['akcija'] == "novi" && check_csrf_token()) {
		// TODO dodati provjeru naziva
		$ak_godina = intval($_POST['akademska_godina']);
		$naziv = my_escape(substr($_POST['naziv'], 0, 100));
		$prethodna_anketa = intval($_POST['prethodna_anketa']);
	
		print "Nova anketa je kreirana. Molimo sačekajte.<br/><br/>";
				
		$q393 = myquery("insert into anketa_anketa set naziv='$naziv', datum_otvaranja=NOW(), datum_zatvaranja=NOW(), opis='', aktivna=0, editable=1, akademska_godina=$ak_godina");
		$anketa = mysql_insert_id();
		$r394 = myquery("insert into anketa_predmet set anketa=$anketa, predmet=NULL, akademska_godina=$ak_godina, aktivna=0"); // FIXME Ovim je kreirana anketa za sve predmete... 
		zamgerlog("kreirana nova anketa '$naziv' sa id-om $anketa", 4);
		zamgerlog2("kreirana nova anketa", $anketa);
		
		// Da li ćemo prekopirati pitanja od prošlogodišnje ankete ?
		if ($prethodna_anketa != 0) {
			// Ubaci pitanja od izabrane ankete za ponavljanje
			$q377=myquery("insert into anketa_pitanje (anketa,tip_pitanja,tekst) select $anketa,tip_pitanja,tekst from anketa_pitanje where anketa=$prethodna_anketa");
		}
		?>
		<script language="JavaScript">
			location.href='<?=genuri()?>&akcija=edit&anketa=<?=$anketa?>';
		</script>
		<?
	} 
	//  *****************************  KRAJ AKCIJA NOVI   ************************************************************		


	//  ******************* AKCIJA EDIT - dio koji se prikazuje ako se klikne DETALJI ******************************
	else if ($_REQUEST['akcija'] == "edit" ) {
		
		print "<a href='?sta=studentska/anketa'>Povratak nazad</a>";
		
		// Subakcija koja se izvrsava kada se edituje neko od pitanja 
		if($_POST['subakcija']=="edit_pitanje" && check_csrf_token()) {
			$pitanje = intval($_REQUEST['column_id']);
			if (isset($_REQUEST['obrisi'])) {
				$q800 = myquery("delete from anketa_pitanje where id=$pitanje");
				print " <center> <span style='color:#009900'> Pitanje uspješno obrisano! </span> </center>";
				zamgerlog("obrisano pitanje na anketi $anketa", 2);
				zamgerlog2("obrisano pitanje na anketi", $anketa);
			} else {
				$tekst_pitanja = $_REQUEST['tekst_pitanja'];
				$tip_pitanja = $_REQUEST['tip_pitanja'];
				$q810 = myquery("update anketa_pitanje set tip_pitanja=$tip_pitanja, tekst='$tekst_pitanja' where id=$pitanje");
				print " <center> <span style='color:#009900'> Pitanje uspješno izmjenjeno! </span> </center>";
				zamgerlog("izmijenjeno pitanje na anketi $anketa", 2);
				zamgerlog2("izmijenjeno pitanje na anketi", $anketa);
			}
		}
		
		// subakcija koja se izvrsava kada se dodaje novo pitanje
		if($_POST['subakcija']=="novo_pitanje" && check_csrf_token()) {
			$tekst_pitanja = my_escape($_REQUEST['tekst_novo_pitanje']);
			$tip_pitanja = intval($_REQUEST['tip_novo_pitanja']);

			$q891 = myquery("select id from anketa_pitanje ORDER BY id desc limit 1");
			$id_pitanja = mysql_result($q891,0,0)+1;
			$q800 = myquery("insert into anketa_pitanje (anketa,tip_pitanja,tekst) values ($anketa,$tip_pitanja,'$tekst_pitanja')");
			print " <center> <span style='color:#009900'> Pitanje uspješno dodano! </span> </center>";
			zamgerlog("dodano pitanje na anketi $anketa", 2);
			zamgerlog2("dodano pitanje na anketi", $anketa);
		}
		
		// Osnovni podaci
		
		$id_ankete = intval($_REQUEST['anketa']);
		$q201 = myquery("select datum_otvaranja,datum_zatvaranja,naziv,opis,editable,akademska_godina from anketa_anketa where id=$id_ankete");
		$datum_otvaranja = mysql_result($q201,0,0);
		$datum_zatvaranja = mysql_result($q201,0,1);
		$naziv = mysql_result($q201,0,2);
		$opis = mysql_result($q201,0,3);
		$editable = mysql_result($q201,0,4);
		$ak_godina_ankete = mysql_result($q201,0,5);
		
		// broj pitanja
		$q203 = myquery("SELECT count(*) FROM anketa_pitanje WHERE anketa=$id_ankete");
		$broj_pitanja = mysql_result($q203,0,0);
		
		//kupimo pitanja
		$q202=myquery("SELECT p.id, p.tekst,t.tip FROM anketa_pitanje p,anketa_tip_pitanja t WHERE p.tip_pitanja = t.id and p.anketa = $id_ankete order by p.id");
		
		// id aktelne akademske godine
		$q010 = myquery("select id,naziv from akademska_godina where aktuelna=1");
		$aktuelna_ak_god = mysql_result($q010,0,0);
		
		$q125 = myquery("select naziv from akademska_godina where id=$ak_godina_ankete");
		$naziv_ak_godina_ankete = mysql_result($q125,0,0);
		
		?>
	
		<center>
		<table border="0" width="600" >
			<tr>
				<td valign="top" colspan="2" align="center">
					<h2><?=$naziv?> za godinu <?=$naziv_ak_godina_ankete?> </h2>	
				</td>
			</tr>
			<?

		if ($ak_godina_ankete == $aktuelna_ak_god) {
			?>
			<tr>
				<td valign="top" align="right" > Naziv: &nbsp; </td>
				<td valign="top"> <b><?=$naziv?></b><br/> 	</td>
			</tr>
			<tr>
				<td valign="top" align="right">  Datum otvaranja: &nbsp; 	</td>
				<td valign="top">  <b><?=$datum_otvaranja?></b><br/> </td>
			</tr>
			<tr>
				<td valign="top" align="right">  Datum zatvaranja: &nbsp; 	</td>
				<td valign="top"> <b><?=$datum_zatvaranja?></b><br/> 	</td>
			</tr>
			<tr>
				<td valign="top" align="right">	 Opis: &nbsp; 	</td>
				<td valign="top"> <b><?=$opis?></b><br/></td>
			</tr> 
			<tr>
				<td valign="top" align="right">&nbsp;</td>
				<td valign="top">&nbsp;<br /> <a href="?sta=izvjestaj/anketa_sumarno&anketa=<?=$id_ankete?>">Sumarni izvještaj za anketu</a><br/></td>
			</tr> 
			<tr>
				<td valign="top" colspan="2" align="center"> <hr/></td>
			</tr>
			<tr>
				<td valign="top" colspan="2" align="center">
					<?=genform("POST")?>
					<input type="hidden" name="akcija" value="podaci">
					<input type="button" value=" Pregled " onclick="javascript:window.open('<?=$conf_site_url?>/index.php?sta=public/anketa&akcija=preview&anketa=<?=$id_ankete?>');">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="Submit" value=" Izmijeni "></form>
				</td>
			</tr>
		</table>
		
		<? 
		}
		else print "</table>";

		// podaci o pitanjima koja pripadaju toj anketi
		function dropdown_anketa($tip) {
			$q283=myquery("SELECT id, tip from anketa_tip_pitanja");
			if ($tip == 1)
				$lista="<select id='tip_novo_pitanja' name='tip_novo_pitanja'>";
			else
				$lista="<select name='tip_pitanja'>";

			while ($r283=mysql_fetch_row($q283)) {
				$lista.="<option value='$r283[0]'"; 
				if($r283[1]==$tip) 
					$lista.=" selected"; 
				$lista.=">$r283[1]</option>";
			}
			$lista.= "</select>";
			return $lista;
		}
		
		?>
		<br/>
		<table width="800" border="0">
			<tr bgcolor='#00AAFF'>
				<td><strong>Tekst pitanja</strong></td>
				<td><strong>Tip pitanja</strong></td>
		<?
				
		// Da li se mogu dodavati nova pitanja ili mijenjati postojeća
		if($editable == 0){
			print "</tr>";
			$i=1;
			while ($r202 = mysql_fetch_row($q202)) {
				?>
			<tr>
				<td colspan='2'><hr/></td>
			</tr>
			<tr <? if ($i%2==0) print "bgcolor=\"#EEEEEE\""; ?>>
				<td><?=$i?>. <?=$r202[1]?></td>
				<td width='150'><?=$r202[2]?></td>
			</tr>
				<?
				$i++;
			}
		} else {
			print "<td>  </td></tr>";
			$i=1;
			while ($r202 = mysql_fetch_row($q202)) {
				print genform("POST");
				?>
				<tr>
					<td colspan='3'> <hr/> </td> 
				</tr>
				<input type='hidden' name='subakcija' value='edit_pitanje'>
				<tr <? if ($i%2==0) print "bgcolor=\"#EEEEEE\""?>>
				<input type='hidden' name='column_id' value='<?=$r202[0]?>'>
					<td><input name ='tekst_pitanja' size='100' value='<?=$r202[1]?>'/> </td> 
					<td><?=dropdown_anketa($r202[2])?></td>
					<td><input type='submit' value='Pošalji '><input type='submit' name='obrisi'  value=' Obriši '></td>
				</tr>
				</form>
				<?
				$i++;
			}	
			$q284 = myquery("SELECT id, tekst, tip_pitanja FROM anketa_pitanje");
			$lista_pitanja = "<select id = 'pitanja' name='pitanja' onChange=\"javascript:setVal();\">";
			$Counter=0;
/*			while ($r283=mysql_fetch_row($q284)) {					
				$lista_pitanja.="<option value='$r283[0]'>$r283[1]</option>"; 
				$lista_pitanja.="<script>pitanje_array[$Counter]='$r283[1]'; tip_array[$Counter]=$r283[2]-1; </script>";
				$Counter++;						
			}*/
			$lista_pitanja.= "</select>";
			
			?>
			<tr><td colspan='3'><hr/><br> </td> </tr>
			<tr><td colspan='3'>Dodajte novo pitanje: </td> </tr>
			<tr><td colspan='3'>Odaberite postojeće pitanje za izmjenu: </td> </tr>
			<tr><td colspan='3'><?=$lista_pitanja?></td> </tr>
			<tr><td colspan='3'>Novo pitanje: </td> </tr>
			<form name='' action="<?=genuri()?>&akcija=edit&anketa=<?=$anketa?>" method='POST'>
			<input type='hidden' name='subakcija' value='novo_pitanje'>
			<tr >
				<td>Tekst: <input name='tekst_novo_pitanje' id = 'tekst_novo_pitanje' size='100' /> </td> <td> Tip: <?=dropdown_anketa(1)?></td>
				<td><input type='submit' value=' Dodaj '><input type='reset'  value='Poništi'></td>
			</tr>
			</form>
			<?
		}
		print "</table>";
		?>
	</center>
	<?
	} 


	// ************************************* KRAJ AKCIJA EDIT  *************************************************
	
	// ----------------------------          Početna stranica  ----------------------------------------------------------
	//-------------------------------------------------------------------------------------------------------------------
	else {

		$q10 = myquery("select id,naziv from akademska_godina where aktuelna=1");
		$ag = mysql_result($q10,0,0);
		?>
		<center>
	
		<table width="600" border="0">
			<tr><td align="left">
				<p><h3>Studentska služba - Anketa</h3></p>
				<div class="anketa_naslov">
					<p><h4>Aktuelna akademska godina</h4></p>
				</div>
				<?

				// Gledamo da li je za ovu akademsku godinu kreirana ijedna anketa
				$q199=myquery("select id,naziv,opis,aktivna from anketa_anketa where akademska_godina=$ag");
				if (mysql_num_rows($q199)==0) {
					print "Za ovu akademsku godinu nije kreirana nijedna anketa";
				} else { 
					?><table width="100%" border="0"><?
					while ($anketa_row = mysql_fetch_row($q199)) {
						?>
						<tr><td width='50%'><?=$anketa_row[1]?></td>
						<td><?					
						if ($anketa_row[3] == 0 ) 
							print "<a href='".genuri()."&akcija=aktivacija&anketa=$anketa_row[0]'>Aktiviraj</a>";
						else
							print "<a href='".genuri()."&akcija=deaktivacija&anketa=$anketa_row[0]'>Deaktiviraj</a>";
						?>
						</td>
						<td><a href="<?=genuri()?>&akcija=edit&anketa=<?=$anketa_row[0]?>">Detalji</a></td></tr>
						<?
					}
					print "</table>";
				}

				// Forma za kreiranje ankete
				// Spisak anketa od prošlih godina, radi ponavljanja pitanja
				$q199b=myquery("select a.id, a.naziv, ak.naziv from anketa_anketa a, akademska_godina ak where a.akademska_godina = ak.id order by a.akademska_godina desc");
				?>

				<hr>
				<?=genform("POST")?>
				<input type="hidden" name="akcija" value="novi">
				<input type="hidden" name="akademska_godina" value="<?=$ag?>">
				<p><br><b>Nova anketa</b><br/><br/>
				Naziv ankete:<br/>
				<input type="text" name="naziv" size="50"> <input type="submit" value=" Dodaj ">
				<br />Ponovi pitanja od: 
				<select title="Ponovi pitanja od" name="prethodna_anketa" id="prethodna_anketa">
					<option value='0'> Bez ponavljanja </option>
					<? 
					while ($r199b = mysql_fetch_row($q199b)){
						print "<option value='$r199b[0]'> $r199b[1] ($r199b[2])</option>";
					}
					?>
				</select>
				</form>
				<?


				// Spisak anketa u prethodnim akademskim godinama

				?>
				<hr />
				<div class="anketa_naslov">
					<p><h4>Prethodne akademske godine</h4></p>
				</div>
				<?

				$q200=myquery("select a.id, a.datum_otvaranja, a.datum_zatvaranja, a.naziv, a.opis, a.aktivna, ak.naziv from anketa_anketa a, akademska_godina ak where a.akademska_godina = ak.id and akademska_godina!=$ag");
				print '<table width="100%" border="0">';
				if (mysql_num_rows($q200)==0) {
					?><tr><td>Prethodnih akademskih godina nije bila definisana nijedna anketa</td></tr><?
				} else {
					while ($r200 = mysql_fetch_row($q200)){
						?>
						<tr>
							<td width="50%"><?=$r200[3]?> (<?=$r200[6]?>)</td>
							<td><a href="<?=genuri()?>&akcija=edit&anketa=<?=$r200[0]?>">Detalji</a></td>
						</tr>
						<?
					}
				}
				?>
				</table>

				<!-- -------------------------------       REZULTATI -------------------------------------------------->
				<hr />
				<div class="anketa_naslov">
					<p><h4>Rezultati ankete </h4></p>
				</div>
				<a onclick="switch_poredjenje()" style="cursor:pointer">Sumarni izvještaji</a>
				<div id="poredjenje_1" style="display:none" class="izvjestaji">
				<ul>
					<li> <a onclick="switch_izvjestaj()" style="cursor:pointer">  &nbsp;Semestralni izvještaj </a> </li>
					<div id="semestralni" style="display:none">
					<form method="GET" action="index.php">
					<input type="hidden" name="sta" value="izvjestaj/anketa_semestralni">
					<table width="450" align="center">
						<tr>
							<td width="200">Odaberite akademsku godinu:</td>
							<td>
							<select name="akademska_godina">
				<?
					$q295 = myquery("select id,naziv, aktuelna from akademska_godina order by naziv");
					while ($r295=mysql_fetch_row($q295)) {
					?>
					<option value="<?=$r295[0]?>"<? if($r295[0]==$ag) print " selected"; ?>><?=$r295[1]?></option>
					<? } 
				?>
							</select><br/>
							</td>
						</tr>
						<tr>
							<td>Odaberite studij:</td>
							<td>
								<select name="studij" id="studij">
								<option value="-1">--- Prva godina studija ---</option>
				<?
					$q295 = myquery("select s.id, s.naziv, ts.trajanje from studij as s, tipstudija as ts where s.tipstudija=ts.id and s.moguc_upis=1 order by s.tipstudija, s.naziv");
					$maxsemestara=0;
					while ($r295=mysql_fetch_row($q295)) {
						?>
						<option value="<?=$r295[0]?>"><?=$r295[1]?></option>
						<?
						if ($r295[2]>$maxsemestara) $maxsemestara=$r295[2];
					}
				?>
								</select><br/>
							</td>
						</tr>
						<tr>
							<td> Odaberite semestar:</td>
							<td>
							<div id="pgs">
								<select name="semestar" id="semestar">
								<?
								for ($sem=1; $sem<=$maxsemestara; $sem++) {
									?>
									<option value="<?=$sem?>"> <?=$sem?></option>
									<?
								}
								?>
								</select>
							</div>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<input type="hidden" name="akcija" value="semestralni">
								<input size="100px" type="submit" value="Kreiraj izvještaj">
							</td>
						</tr>
					</table>	
					</form>
					</div>

					<li> <a onclick="switch_izvjestaj2()" style="cursor:pointer">  &nbsp;Izvještaj po smjerovima</a> </li>
					<div id="po_smjerovima" style="display:none">
					<form method="post" action="?sta=izvjestaj/anketa_semestralni">
					<table width="450" align="center" >
						<tr>
							<td width="200"> Odaberite akademsku godinu: </td>
							<td align="left">
							<select name="akademska_godina">
					<?
						$q295 = myquery("select id,naziv, aktuelna from akademska_godina order by naziv");
						while ($r295=mysql_fetch_row($q295)) {
						?>
						<option value="<?=$r295[0]?>"<? if($r295[0]==$ag) print " selected"; ?>><?=$r295[1]?> &nbsp;&nbsp;&nbsp;&nbsp;</option>
						<?
					}
					?></select><br/> 
							</td>
						</tr>
						<tr>
							<td>Odaberite semestar:</td>
							<td>
							<div id="semestar">
								<select name="semestar" id="semestar">
								<option value="1"> Zimski</option>
								<option value="2"> Ljetni</option>
								<option value="3"> Cijela godina &nbsp;</option>
								</select>
							</div>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<input type="hidden" name="akcija" value="po_smjerovima">
								<input size="100px" type="submit" value="Kreiraj izvjestaj">
							</td>
						</tr>
					</table>
					</form>
					</div>
				</ul>
				</div>


				<hr />
				<?
					$src = my_escape($_REQUEST["search"]);
					$limit = 20;
					$offset = intval($_REQUEST["offset"]);
					$ak_god = intval($_REQUEST["akademska_godina"]);
					if ($ak_god == 0) {
						$q299 = myquery("select id from akademska_godina where aktuelna=1 order by naziv desc limit 1");
						$ak_god = mysql_result($q299,0,0);
					}
					?>
					<table width="100%" border="0">
						<tr>
							<td align="left">
								<p>Pregled izvjestaja po predmetu :<br/>
								<small>Za prikaz svih predmeta na akademskoj godini, ostavite polje za pretragu prazno.</small> </br>
								<?=genform("GET")?>
                                    <input type="hidden" name="offset" value="0"> <?/*resetujem offset*/?>
                                    <select name="akademska_godina">
                                        <option value="-1">Sve akademske godine</option>
                                    <?
                                    $q295 = myquery("select id,naziv, aktuelna from akademska_godina order by naziv");
                                    while ($r295=mysql_fetch_row($q295)) {
                                    	?>
                                        <option value="<?=$r295[0]?>"<? if($r295[0]==$ak_god) print " selected"; ?>><?=$r295[1]?></option>
                                   		<?
                                    }
                                    ?>
                                    </select><br/>
                                    <input type="text" size="50" name="search" value="<? if ($src!="") print $src?>"> 
                                    <input type="Submit" value=" Pretrazi ">
                                </form>
								<br/>
					<?
					if ($ak_god>0 && $src != "") {
						$q300 = myquery("select count( distinct predmet) from ponudakursa as pk, predmet as p where pk.akademska_godina=$ak_god and 
						p.naziv like '%$src%' and pk.predmet=p.id ");
					} else if ($ak_god>0) {
						$q300 = myquery("select count(distinct predmet) from ponudakursa where akademska_godina=$ak_god");
					} else if ($src != "") {
						$q300 = myquery("select sum(br) from (select count(distinct predmet) as br from ponudakursa as pk, predmet as p where pk.predmet=p.id and p.naziv like 
						'%$src%' GROUP BY pk.akademska_godina) as tb1");
					} else {
						$q300 = myquery("select sum(br) from (select count(distinct predmet) as br from ponudakursa GROUP BY akademska_godina ) as tb1");
					}
					$rezultata = mysql_result($q300,0,0);
				
					if ($rezultata == 0)
						print "Nema rezultata!";
					else {
						if ($rezultata>$limit) {
							print "Prikazujem rezultate ".($offset+1)."-".($offset+20)." od $rezultata. Stranica: ";
					
							for ($i=0; $i<$rezultata; $i+=$limit) {
								$br = intval($i/$limit)+1;
								if ($i==$offset)
									print "<b>$br</b> ";
								else
									print "<a href=\"?sta=studentska/anketa&offset=$i&_lv_column_akademska_godina=$ak_god\">$br</a> ";
							}
							print "<br/>";
						}
						
				
						if ($ak_god>0 && $src != "") {
							$q301 = myquery("select p.id, p.naziv, ag.naziv, i.kratki_naziv, ag.id from predmet as p, ponudakursa as pk, akademska_godina as ag, institucija as i where pk.akademska_godina=ag.id and ag.id=$ak_god and p.naziv like '%$src%' and pk.predmet=p.id and p.institucija=i.id GROUP BY p.id, ag.id order by ag.naziv desc, p.naziv limit $offset,$limit");
						} else if ($ak_god>0) {
							$q301 = myquery("select p.id, p.naziv, ag.naziv, i.kratki_naziv, ag.id from predmet as p, ponudakursa as pk, akademska_godina as ag, institucija as i where pk.akademska_godina=ag.id and ag.id=$ak_god and pk.predmet=p.id and p.institucija=i.id GROUP BY p.id, ag.id order by ag.naziv desc, p.naziv limit $offset,$limit");
						} else if ($src != "") {
							$q301 = myquery("select p.id, p.naziv, ag.naziv, i.kratki_naziv, ag.id from predmet as p, ponudakursa as pk, akademska_godina as ag, institucija as i where pk.akademska_godina=ag.id and p.naziv like '%$src%' and pk.predmet=p.id and p.institucija=i.id GROUP BY p.id, ag.id order by ag.naziv desc, p.naziv limit $offset,$limit");
						} else {
							$q301 = myquery("select p.id, p.naziv, ag.naziv, i.kratki_naziv, ag.id from predmet as p, ponudakursa as pk, akademska_godina as ag, institucija as i where pk.akademska_godina=ag.id and pk.predmet=p.id and p.institucija=i.id GROUP BY p.id, ag.id order by ag.naziv desc,p.naziv limit $offset,$limit");
						}
										
						print '<table width="100%" border="0">';
						
						$i=$offset+1;
						while ($r301 = mysql_fetch_row($q301)) {
							if ($ak_god>0){
								if ($r301[5] == 1 || $r301[5]==2)
									print "<tr><td>$i. $r301[1] (PGS)</td>\n";
								else
									print "<tr><td>$i. $r301[1] ($r301[3])</td>\n";
								}
							else
								if ($r301[5] == 1 || $r301[5]==2)
									print "<tr><td>$i. $r301[1] (PGS) - $r301[2]</td>\n";
								else
									print "<tr><td>$i. $r301[1] ($r301[3]) - $r301[2]</td>\n";
								print "<td align='right'><a href= '?sta=izvjestaj/anketa&predmet=$r301[0]&ag=$r301[4]&rank=da'>Izvjestaj rank</a></td>\n";
								print "<td align='right' ><a href='?sta=izvjestaj/anketa&predmet=$r301[0]&ag=$r301[4]&komentar=da'>Izvjestaj komentari</a></td>\n";
					
							$i++;
						}
						print "</table>";
					}
					?>
						<br/>
					
					</table>				
				</td>
			</tr>
	 </table>
	</center>
	<?
	}
}
?>