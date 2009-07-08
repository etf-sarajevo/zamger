<?php 
// STUDENTSKA/ANKETA - administracija ankete, studentska služba


function studentska_anketa()
{
global $userid,$user_siteadmin,$user_studentska;
global $_lv_; // Potrebno za genform() iz libvedran

// Provjera privilegija

if (!$user_studentska && !$user_siteadmin) {
	zamgerlog("nije studentska",3); // 3: error
	biguglyerror("Pristup nije dozvoljen.");
	return;
}

?>
<script type="text/javascript">
function promjeniListu()
{
	studij = document.getElementById('studij').value;
	if (studij !=1){
		document.getElementById('pgs').style.display = 'none';
		document.getElementById('ostalo').style.display = '';
	}
	else{
		document.getElementById('pgs').style.display = '';
		document.getElementById('ostalo').style.display = 'none';
	}

}
function setVal()
{
document.getElementById('tekst_novo_pitanje').value = pitanje_array[document.getElementById('pitanja').selectedIndex];
document.getElementById('tip_novo_pitanja').selectedIndex = tip_array[document.getElementById('pitanja').selectedIndex];

}
var pitanje_array = new Array();
var tip_array = new Array();

var par=1;

function switch_poredjenje()
{
	
	if (par==1){
	
			document.getElementById('poredjenje_1').style.display = '';
			par=0;
	}
	else { 
	
			document.getElementById('poredjenje_1').style.display = 'none';
			par=1;

	}
}
var help=1;

function switch_izvjestaj()
{
	
	if (help==1){
	
			document.getElementById('semestralni').style.display = '';
			help=0;
	}
	else { 
	
			document.getElementById('semestralni').style.display = 'none';
			help=1;

	}
}

var help2=1;

function switch_izvjestaj2()
{
	
	if (help2==1){
	
			document.getElementById('po_smjerovima').style.display = '';
			help2=0;
	}
	else { 
	
			document.getElementById('po_smjerovima').style.display = 'none';
			help2=1;

	}
}
</script>

<?

$akcija = $_REQUEST['akcija'];
$anketa = intval($_REQUEST['anketa']);
$id = intval($_REQUEST['anketa']);


// deaktivizacija ankete
if ($_REQUEST['akcija']=="deaktivacija" ){
		
		$result401=myquery("update anketa set aktivna = 0 where id=$id");
	
	}

// ako korinik želi da mijenja podatke vezane za anketu -- ime -- opis -- datum pocetka i kraja
// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
if ($akcija =="podaci")
	{
	
	if ($_POST['subakcija']=="potvrda" && check_csrf_token() ) {

		$naziv = $_REQUEST['naziv'];
		$opis = $_REQUEST['opis'];
		
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
			niceerror("Odabrani datum je nemoguc");
			zamgerlog("los datum", 3);
			return 0;
		}
		if (!checkdate($mjesec2,$dan2,$godina2)) {
			niceerror("Odabrani datum je nemoguc");
			zamgerlog("los datum", 3);
			return 0;
		}
		if ($sat<0 || $sat>24 || $minuta<0 || $minuta>60 || $sekunda<0 || $sekunda>60) {
			niceerror("Vrijeme nije dobro");
			zamgerlog("lose vrijeme", 3);
			return 0;
		}
			
			
		
		$mysqlvrijeme1 = time2mysql(mktime($sat,$minuta,$sekunda,$mjesec,$dan,$godina));
		$mysqlvrijeme2 = time2mysql(mktime($sat2,$minuta2,$sekunda2,$mjesec2,$dan2,$godina2));
		
		$q395 = myquery("update anketa set naziv='$naziv', datum_otvaranja='$mysqlvrijeme1', datum_zatvaranja='$mysqlvrijeme2',
						  opis='$opis' where id=$anketa");
		
		?>
		<script language="JavaScript">
		location.href='?sta=studentska/anketa&anketa=<?=$anketa?>&akcija=edit';
		</script>
		<? 
		return;
	}
	
	 print "<a href='?sta=studentska/anketa&akcija=edit&anketa=$anketa'>Povratak nazad</a>";
	
		// subakcija kojom se data anketa postavlja kao aktivna
	if ($_REQUEST['subakcija']=="aktivacija" ){
		
		// automatski postavljamo i to da vise nije moguce editovati pitanja date ankete posto je postala aktivna
		
		$result401a=myquery("update anketa set editable = 0 where id=$id");
		
		// prvo sve anket postavimo na neaktivne 
		$result401=myquery("update anketa set aktivna = 0");
		//a zatim datu postavimo kao aktivnu jer u datom trenu samo jedna ankete moze biti aktivna
		$result402=myquery("update anketa set aktivna = 1 where id=$id");
		print "<center><span style='color:#009900'> Anekta je postavljena kao aktivna!</span></center>";
	
	}

	$result401=myquery("select id,datum_otvaranja,datum_zatvaranja,naziv,opis from anketa where id=$id");
    //$result401 = mysql_query($q401);
	$naziv = mysql_result($result401,0,3);

	?>
    
   

<center>
	<h2><?=$naziv?>  - izmjena podataka</h2>
    <?
    $tmpvrijeme=time();
	
	$zdan = date('d',$tmpvrijeme);
	$zmjesec = date('m',$tmpvrijeme);
	$zgodina = date('Y',$tmpvrijeme);
	$zsat = date('H',$tmpvrijeme);
	$zminuta = date('i',$tmpvrijeme);
	$zsekunda = date('s',$tmpvrijeme);
	
	
	
	
	?>
    
    
    
	<?=genform("POST")?>
	<input type="hidden" name="subakcija" value="potvrda">
	<table border="0" width="600">
    	    <tr>
        	<td valign="top" align="right" >
				Naziv : &nbsp; 
            </td>
            <td valign="top">
            <b><input type="text" name="naziv" value="<?=$naziv?>" class="default"></b><br/>
			</td>
        </tr>
        
        <tr>
        	<td valign="top" align="right">
				 Datum otvaranja : &nbsp;  

            </td>
            <td valign="top">
            <b> <?=datectrl($zdan,$zmjesec,$zgodina,"1")?>   <input type="text" name="sat1" size="1" value="<?=$zsat?>"> <b>:</b> <input type="text" name="minuta1" size="1" value="<?=$zminuta?>"> <b>:</b> <input type="text" name="sekunda1" size="1" value="<?=$zsekunda?>"> <br></b><br/>
			</td>
        </tr>
        
        <tr>
        	<td valign="top" align="right">
				 Datum zatvaranja : &nbsp; 
            </td>
            <td valign="top">
            <b><b><?=datectrl($zdan,$zmjesec,$zgodina,"2")?>  <input type="text" name="sat2" size="1" value="<?=$zsat?>"> <b>:</b> <input type="text" name="minuta2" size="1" value="<?=$zminuta?>"> <b>:</b> <input type="text" name="sekunda2" size="1" value="<?=$zsekunda?>"> <br></b><br/>
			</td>
        </tr>
           
        <tr>
        	<td valign="top" align="right">
				 opis : &nbsp; 
            </td>
            <td valign="top">
            <b><b><textarea name="opis" cols="30"  rows="15" class="default"><?=mysql_result($result401,0,4)?> </textarea></b><br/>
			</td>
        </tr>
        
    </table>

	<p>
	<input type="Submit" value=" Izmijeni "></form>
	
	</p>
    </center>
	<?
	
	}
	
	
//  ******************* dio koji se prikazuje kada se kreira nova anketa ******************************	
else if ($_POST['akcija'] == "novi" && check_csrf_token()){
// TODO dodati provjeru naziva
	$ak_godina = $_POST['ak_godina'];
	$naziv = substr($_POST['naziv'], 0, 100);
	$prethodna_anketa = $_POST['prethodna_anketa'];

	print "Nova anketa.<br/><br/>";
			
	$q393 = myquery("insert into anketa (naziv,ak_god) values ('$naziv',$ak_godina)");
	$q391 = myquery("select id from anketa where naziv='$naziv'");
	$anketa = mysql_result($q391,0,0);
	
	// da li cemo prekopirati pitanja od proslogodisnje ankete ?
	if ($prethodna_anketa != 0)
		{
		// ubaci pitanja od izabrane ankete za ponavljanje
		$q377=myquery("insert into pitanje (anketa_id,tip_id,tekst) select $anketa,tip_id,tekst from pitanje where anketa_id=$prethodna_anketa");
		
	}
	?>
	<script language="JavaScript">
	location.href='<?=genuri()?>&akcija=edit&anketa=<?=$anketa?>';
	</script>
	<?
	
	



}	
//  ******************* dio koji se prikazuje ako se klikne DETALJI ******************************

else if ($_GET['akcija'] == "edit" ) {

	// subakcija koja se izvrsava kada se edituje neko od pitanja 
	if($_POST['subakcija']=="edit_pitanje"  ){
		$obrisi = $_REQUEST['obrisi'];
		$pitanje = $_REQUEST['column_id'];
		if ($obrisi){
			
			$q800=myquery("delete from pitanje where id = $pitanje");
		}
		else{
			
			$tekst_pitanja = $_REQUEST['tekst_pitanja'];
			$tip_pitanja= $_REQUEST['tip_pitanja'];
			
			$q800=myquery("update pitanje set tip_id=$tip_pitanja,tekst= '$tekst_pitanja' where id = $pitanje");
			
		}
	
	}
	// subakcija koja se izvrsava kada se dodaje novo pitanje
	if($_POST['subakcija']=="novo_pitanje"){
		$tekst_pitanja = $_REQUEST['tekst_novo_pitanje'];
		$tip_pitanja= $_REQUEST['tip_novo_pitanja'];
	
		$q891=myquery("select id from pitanje ORDER BY id desc limit 1");
		$id_pitanja=mysql_result($q891,0,0)+1;

		//mozda treba prepraviti posto koristi autoincrement	
		$q800=myquery("insert into pitanje (anketa_id,tip_id,tekst) values ($anketa,$tip_pitanja,'$tekst_pitanja')");
		
	}

	$id=$_GET['anketa'];
		
		// Osnovni podaci
	
	$result201=myquery("select id,datum_otvaranja,datum_zatvaranja,naziv,opis,editable,ak_god from anketa where id=$id");
    $ak_godina_ankete=mysql_result($result201,0,6);
	
	
	// broj pitanja
	$result203=myquery("SELECT count(*) FROM pitanje WHERE anketa_id =$id");
	//$result203 = mysql_query($q203);
	$broj_pitanja= mysql_result($result203,0,0);
	
	//kupimo pitanja
	$result202=myquery("SELECT p.id, p.tekst,t.tip FROM pitanje p,tip_pitanja t WHERE p.tip_id = t.id and p.anketa_id =$id");
    //$result202 = mysql_query($q202);
	
	
	$naziv = mysql_result($result201,0,3);
	$editable = mysql_result($result201,0,5);
	//  opci podaci
	
	// id aktelne akademske godine
	$q010 = myquery("select id,naziv from akademska_godina where aktuelna=1");
	$aktuelna_ak_god = mysql_result($q010,0,0);
	
	$q125 = myquery("select naziv from akademska_godina where id=$ak_godina_ankete");
	$naziv_ak_godina_ankete = mysql_result($q125,0,0);
	
	
?>
	 <a href="?sta=studentska/anketa">Povratak nazad</a>

<center>


	<table border="0" width="600" >
    	<tr>
            <td valign="top" colspan="2" align="center">
				<h2><?=$naziv?> za godinu <?=$naziv_ak_godina_ankete?> </h2>	
        	</td>
		</tr>
           <? if ($ak_godina_ankete==$aktuelna_ak_god){ ?>
        <tr>
        	<td valign="top" align="right" >
				Naziv : &nbsp; 
            </td>
            <td valign="top">
            <b><?=$naziv?></b><br/>
			</td>
        </tr>
        
     
        <tr>
        	<td valign="top" align="right">
				 Datum otvaranja : &nbsp; 
            </td>
            <td valign="top">
            <b><?=mysql_result($result201,0,1)?></b><br/>
			</td>
        </tr>
        
        <tr>
        	<td valign="top" align="right">
				 Datum zatvranja : &nbsp; 
            </td>
            <td valign="top">
            <b><b><?=mysql_result($result201,0,2)?></b><br/>
			</td>
        </tr>
           
        <tr>
        	<td valign="top" align="right">
				 opis : &nbsp; 
            </td>
            <td valign="top">
            <b><b><?=mysql_result($result201,0,4)?></b><br/>
			</td>
        </tr> 
        <tr>
            <td valign="top" colspan="2" align="center">
		
				<hr/>
        	</td>
		</tr>
        <tr>
            <td valign="top" colspan="2" align="center">
		
				<?=genform("GET")?>
                <input type="hidden" name="akcija" value="podaci">
                <input type="Submit" value=" Izmijeni "></form>
        	</td>
		</tr>
    </table>
	
	<?php 
	}
	else print "</table>";
	// podaci o pitanjima koja pripadaju toj anketi
		function dropdown_anketa($tip){
			$q283=myquery("SELECT id, tip from tip_pitanja");
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
		
		print "<br/>";
		print '<table width="800" border="0">';
		print "<tr bgcolor='#00AAFF'> <td  > <strong> Tekst pitanja </strong></td> <td> <strong> Tip pitanja </strong></td> ";
			
		// da li se mogu dodavati nova pitanja ili mijenjati postojeca
		if($editable == 0){
				print "</tr>";
				$i=1;
				while ($r202 = mysql_fetch_row($result202)) {			
					
					print  "<tr> <td colspan='2'> <hr/> </td> </tr>";
					print "<tr "; if ($i%2==0) print "bgcolor=\"#EEEEEE\""; print ">";
					print "<td >$i. $r202[1]  </td><td>$r202[2]  </td> </tr>";				
					$i++;
			}	
		
		}		
		else{	
				print "<td>  </td></tr>";
				$i=1;
				while ($r202 = mysql_fetch_row($result202)) {
					print "<form name='' action='".genuri()."&akcija=edit&anketa=$anketa' method='POST'>
						<tr> <td colspan='3'> <hr/> </td> </tr>";
					print "<input type='hidden' name='subakcija' value='edit_pitanje'>";
					print "<tr "; if ($i%2==0) print "bgcolor=\"#EEEEEE\""; print ">";
					print "<input type='hidden' name='column_id' value='$r202[0]'>";
					print  "<td> <input name ='tekst_pitanja' size='100' value='$r202[1]'/> </td> <td> ". dropdown_anketa($r202[2]); 
					print "</td><td><input type='submit' value='Posalji '><input type='submit' name='obrisi'  value='Obrisi '></td></tr> ";
					print "</form>";
					
					$i++;
				}	
					$q284=myquery("SELECT id, tekst,tip_id FROM pitanje");
					$lista_pitanja="<select id = 'pitanja' name='pitanja' onChange=\"javascript:setVal();\">";
					$Counter=0;
					while ($r283=mysql_fetch_row($q284)) {
							
						$lista_pitanja.="<option value='$r283[0]'>$r283[1]</option>"; 
						
						$lista_pitanja.="<script>pitanje_array[$Counter]='$r283[1]'; tip_array[$Counter]=$r283[2]-1; </script>";
						$Counter++;	
							
					}
					$lista_pitanja.= "</select>";
					
					
					print "<tr> <td colspan='3'> <hr/><br> </td> </tr>";
					print "<tr > <td colspan='3'> Dodajte novo pitanje: </td> </tr>";
					print "<tr > <td colspan='3'> Odaberite postojece pitanja: </td> </tr>";
					print "<tr > <td colspan='3'> $lista_pitanja </td> </tr>";
					print "<tr > <td colspan='3'> Novo pitanje: </td> </tr>";
					print "<form name='' action='".genuri()."&akcija=edit&anketa=$anketa' method='POST'>";
					print "<input type='hidden' name='subakcija' value='novo_pitanje'>";
					print "<tr >";  	
					print  "<td>Tekst: <input name='tekst_novo_pitanje' id = 'tekst_novo_pitanje' size='100' /> </td> <td> Tip:". dropdown_anketa(1); 
					print "</td><td><input type='submit' value='Dodaj '><input type='reset'  value='Reset '></td></tr> 
					</form>";
				
		}
		
		
		
		print "</table>";
	
		
	?>
	<table>
    <tr >
    
    <td>
    
    </td>
    </tr></table>
    
</center>
<?

} // ************************* kraj dijela koji se prikazuje ako je korisnik kliknuo na detalje ******************


// dio koji se desava ako korisnik obrise anketu

else if ($_GET['akcija'] == "brisi" ){
print "brisanje uspjesno";

}



else {
// ----------------------------          DIO koji se pojavljuje na pocetku  -----------------------------------------
//-------------------------------------------------------------------------------------------------------------------
		$q10 = myquery("select id,naziv from akademska_godina where aktuelna=1");
		$ag = mysql_result($q10,0,0);

?>


<center>

<table width="600" border="0">
		<tr>
        	<td align="left">
				
				<p><h3>Studentska sluzba - Anketa</h3></p>
                <div class="anketa_naslov">
                	<p><h4>Aktuelna akademska godina</h4></p>
                </div>
                
				<?php 
				// gledamo da li je za ovu akademsku godinu kreirana anketa
				$q199=myquery("select id,naziv,opis,aktivna from anketa where ak_god=$ag");
				// kupimo ako postoje ankete od proslih godina
				$q199b=myquery("select id,naziv from anketa where ak_god!=$ag");
				if (mysql_num_rows($q199) ==0)
				{
					print "Za ovu akademsku godinu nije kreirana ankete!";
				?>	  
                    <hr>
					<!--                    Forma za kreiranje ankete:              -->   
                 <?=genform("POST")?>
                    <input type="hidden" name="akcija" value="novi">
                    <input type="hidden" name="ak_godina" value="<?=$ag?>">
                    <b>Nova anketa :</b><br/>
                    <input type="text" name="naziv" size="50"> <input type="submit" value=" Dodaj ">
                    <br />Ponovi pitanja od: 
                    <select title="Ponovi pitanja od" name="prethodna_anketa" id="prethodna_anketa">
                    	<option value='0'> Bez ponavljanja </option>
					<?php 
					while ($r199b = mysql_fetch_row($q199b)){
						print "<option value='$r199b[0]'> $r199b[1]</option>";
					}
					?>
                    </select>
                    </form>
                    <hr>
                        
				<?	
					
					
				}
				else { // ako je vec kreirana anketa
				
					$anekta_row = mysql_fetch_row($q199);
					print '<table width="100%" border="0">';
					print "<tr><td width='50%'>  ";
					print " $anekta_row[1]  </td>";
					
					if ($anekta_row[3] == 0 ) 
						print "<td> <a href='".genuri()."&akcija=podaci&anketa=$anekta_row[0]&subakcija=aktivacija'>Aktiviraj</a>";
					else
						print "<td> <a href='".genuri()."&akcija=deaktivacija&anketa=$anekta_row[0]'>Deaktiviraj</a>";
					
					print "</td><td ><a href='".genuri()."&akcija=edit&anketa=$anekta_row[0]'>Detalji</a>";
					print "</td></tr>";				
					print "</table>";
				}
								
				?>
                
                <hr />
                <div class="anketa_naslov">
                <p><h4>Prosle akademske godine</h4></p>
                
                </div>
				
				<?
				$q200=myquery("select id,datum_otvaranja,datum_zatvaranja,naziv,opis,aktivna from anketa where ak_god!=$ag");
				print '<table width="100%" border="0">';
				
				if (mysql_num_rows($q200)==0) print " <tr > <td>Ne postoji anekta za prethodne akademske godine!</td></tr>";
				else
					while ($r200 = mysql_fetch_row($q200)){
						print "<tr><td width='50%' > $r200[3] ";
						//if ($r200[5] == 1 ) print "&nbsp;(<span style='color:#FF0000'> aktivna </span>)";
						print "</td><td ><a href='".genuri()."&akcija=edit&anketa=$r200[0]'>
								Detalji</a></td>";
						
						print "</tr>";
					}
				print "</table>";
					
				?>
             
             
            <!-- -------------------------------       REZULTATI -------------------------------------------------->
                <hr />
                <div class="anketa_naslov">
                <p><h4>Rezultati ankete </h4></p>
             	</div>
             	
                <a onclick="switch_poredjenje()" > Sumarni izvjestaji </a>
                
                <div id="poredjenje_1" style="display:none" class="izvjestaji">
                	<ul>
                    <li> <a onclick="switch_izvjestaj()">  &nbsp;Semestralni izvjestaj </a> </li>
                    	
                        <div id="semestralni" style="display:none">
                        <form method="post" action="?sta=izvjestaj/anketa_semestralni">
                    
                          <table width="450" align="center">
                            <tr>
                                <td width="200">
                                Odaberite  akademsku godinu  : 
                                </td>
                                <td>
                                        <select name="akademska_godina">
                                        <?
                                        $q295 = myquery("select id,naziv, aktuelna from akademska_godina order by naziv");
                                        while ($r295=mysql_fetch_row($q295)) {
                                        ?>
                                            <option value="<?=$r295[0]?>"<? if($r295[0]==$ak_god) print " selected"; ?>><?=$r295[1]?></option>
                                        <?
                                        }
                                        ?></select><br/> 
                                </td>
                              </tr>
                              <tr>
                                <td>
                                        Odaberite  studij  :
                                </td>
                                <td>
                            
                                        <select onchange="javascript:promjeniListu()" name="studij" id="studij">
                                        <?
                                        $q295 = myquery("select id,naziv from studij order by id");
                                        while ($r295=mysql_fetch_row($q295)) {
                                        ?>
                                            <option value="<?=$r295[0]?>"><?=$r295[1]?></option>
                                        <?
                                        }
                                        ?></select><br/>
                                    </td>
                                </tr>    
                                 <tr>
                                    <td>       
                                    Odaberite semestar :
                                    </td>
                                    <td>
                                        <div id="pgs">
                                        <select name="semestar" id="semestar">
                                            <option value="1"> 1</option>
                                            <option value="2"> 2</option>
                                        </select>
                                        </div>
                                        <div id="ostalo" style="display:none">
                                        <select name="semestar2" id="semestar2">
                                            <option value="3"> 3</option>
                                            <option value="4"> 4</option>
                                            <option value="5"> 5</option>
                                            <option value="6"> 6</option>
                                        </select>
                                        </div>
                                        
                                     </td>
                                   </tr>
                                   
                                   <tr>
                                    <td colspan="2">
                                            <input type="hidden" name="akcija" value="izvrsi">                                
                                            <input size="100px" type="submit" value="Kreiraj izvjestaj">
                                            
                                    </td>
                                    </tr>
                               </table>

               		</form>
                    </div>
                    
                    <li> <a onclick="switch_izvjestaj2()">  &nbsp;Izvjestaj po smjerovima</a> </li>
                    
                    	<div id="po_smjerovima" style="display:none">
                        	  <form method="post" action="?sta=izvjestaj/anketa_semestralni">
                             <table width="450" align="center" >
                              <tr>
                                <td width="200">
                                Odaberite  akademsku godinu  : 
                                </td>
                                <td align="left">
                                        <select name="akademska_godina">
                                        <?
                                        $q295 = myquery("select id,naziv, aktuelna from akademska_godina order by naziv");
                                        while ($r295=mysql_fetch_row($q295)) {
                                        ?>
                                            <option value="<?=$r295[0]?>"<? if($r295[0]==$ak_god) print " selected"; ?>><?=$r295[1]?> &nbsp;&nbsp;&nbsp;&nbsp;</option>
                                        <?
                                        }
                                        ?></select><br/> 
                                </td>
                              </tr>
                              <tr>
                                    <td>       
                                    Odaberite semestar :
                                    </td>
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
                
                <?php 
			
				
				print " <hr />";
				
				
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
					?></select><br/>
					<input type="text" size="50" name="search" value="<? if ($src!="") print $src?>"> 
                    <input type="Submit" value=" Pretrazi ">
                     </form>
					<br/>
				<?
				if ($ak_god>0 && $src != "") {
					$q300 = myquery("select count(*) from ponudakursa as pk, predmet as p where pk.akademska_godina=$ak_god and 
					p.naziv like '%$src%' and pk.predmet=p.id");
				} else if ($ak_god>0) {
					$q300 = myquery("select count(*) from ponudakursa where akademska_godina=$ak_god");
				} else if ($src != "") {
					$q300 = myquery("select count(*) from ponudakursa as pk, predmet as p where pk.predmet=p.id and p.naziv like 
					'%$src%'");
				} else {
					$q300 = myquery("select count(*) from ponudakursa");
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
								print "<a href=\"&offset=$i&_lv_column_akademska_godina=$ak_god\">$br</a> ";
						}
						print "<br/>";
					}
					
			
					if ($ak_god>0 && $src != "") {
						$q301 = myquery("select pk.id, p.naziv, ag.naziv, s.kratkinaziv from predmet as p, ponudakursa as pk, akademska_godina as ag, studij as s where pk.akademska_godina=ag.id and ag.id=$ak_god and p.naziv like '%$src%' and pk.predmet=p.id and pk.studij=s.id order by ag.naziv desc, p.naziv limit $offset,$limit");
					} else if ($ak_god>0) {
						$q301 = myquery("select pk.id, p.naziv, ag.naziv, s.kratkinaziv from predmet as p, ponudakursa as pk, akademska_godina as ag, studij as s where pk.akademska_godina=ag.id and ag.id=$ak_god and pk.predmet=p.id and pk.studij=s.id order by ag.naziv desc, p.naziv limit $offset,$limit");
					} else if ($src != "") {
						$q301 = myquery("select pk.id, p.naziv, ag.naziv, s.kratkinaziv from predmet as p, ponudakursa as pk, akademska_godina as ag, studij as s where pk.akademska_godina=ag.id and p.naziv like '%$src%' and pk.predmet=p.id and pk.studij=s.id order by ag.naziv desc, p.naziv limit $offset,$limit");
					} else {
						$q301 = myquery("select pk.id, p.naziv, ag.naziv, s.kratkinaziv from predmet as p, ponudakursa as pk, akademska_godina as ag, studij as s where pk.akademska_godina=ag.id and pk.predmet=p.id and pk.studij=s.id order by ag.naziv desc,p.naziv limit $offset,$limit");
					}
							
													
							
					print '<table width="100%" border="0">';
					
					$i=$offset+1;
					while ($r301 = mysql_fetch_row($q301)) {
						if ($ak_god>0)
							print "<tr><td>$i. $r301[1] ($r301[3])</td>\n";
						else
							print "<tr><td>$i. $r301[1] ($r301[3]) - $r301[2]</td>\n";
						print "<td align='right'><a href= '?sta=izvjestaj/anketa&predmet=$r301[0]&rank=da'>Izvjestaj rank</a></td>\n";
						print "<td align='right' ><a href='?sta=izvjestaj/anketa&predmet=$r301[0]&komentar=da'>Izvjestaj komentari</a></td>\n";
				
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