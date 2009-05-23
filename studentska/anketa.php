
<script type="text/javascript">
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

</script>


<?php 



function studentska_anketa()
{
$akcija = $_REQUEST['akcija'];
$anketa = intval($_REQUEST['anketa']);
$id = intval($_REQUEST['anketa']);

// ako korinik želi da mijenja podatke vezane za anketu -- ime -- info -- datum pocetka i kraja
// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
if ($akcija =="podaci")
	{
	
	if ($_POST['subakcija']=="potvrda" ) {

		$naziv = $_REQUEST['naziv'];
		$info = $_REQUEST['info'];
		
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
		
		$q395 = myquery("update anketa set title='$naziv', open_date='$mysqlvrijeme1', close_date='$mysqlvrijeme2',
						  info='$info' where id=$anketa");
		//$nesto= mysql_query($q395);
		 // nivo 4 - audit
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
		
		// prvo sve anket postavimo na neaktivne 
		$result401=myquery("update anketa set aktivna = 0");
		//a zatim datu postavimo kao aktivnu jer u datom trenu samo jedna ankete moze biti aktivna
		$result401=myquery("update anketa set aktivna = 1 where id=$id");
		print "<center><span style='color:#009900'> Anekta je postavljena kao aktivna!</span></center>";
	
	}

	$result401=myquery("select id,open_date,close_date,title,info from anketa where id=$id");
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
				 Info : &nbsp; 
            </td>
            <td valign="top">
            <b><b><textarea name="info" cols="30"  rows="15" class="default"><?=mysql_result($result401,0,4)?> </textarea></b><br/>
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
else if ($_POST['akcija'] == "novi"){
// TODO dodati provjeru naziva
	$naziv = substr($_POST['naziv'], 0, 100);
	print $naziv;
		print "Nova anketa.<br/><br/>";
	
		
	$q393 = myquery("insert into anketa set title='$naziv'");
	$q391 = myquery("select id from anketa where title='$naziv'");
	$anketa = mysql_result($q391,0,0);

	?>
	<script language="JavaScript">
	location.href='<?=genuri()?>&akcija=edit&anketa=<?=$anketa?>';
	</script>
	<?
	
	



}	
//  ******************* dio koji se prikazuje ako se klikne DETALJI ******************************

else if ($_GET['akcija'] == "edit" ) {

	//TODO dodati akciju AKTIVIRAJ ANKETU koja ce svim administratorima predmeta omoguciti da aktiviraju studentima modul anketa 
	// 
	//ili to ili napraviti da se putem obavjestenja studenti obavjeste za anketu te tu staviti link na istu
	// napravio da kada profesor aktivira anketu  na stranici student/intro se pojavi obavjestenje u dijelu aktuelno 

	
	
	// subakcija koja se izvrsava kada se edituje neko od pitanja 
	if($_POST['subakcija']=="edit_pitanje"){
		$sta_je = $_REQUEST['obrisi'];
		$pitanje = $_REQUEST['column_id'];
		if ($sta_je){
			// TO DO provjerit ima li odgovora na ti pitanje .. ako ima onemoguciti brisanje
			print "Pitanje obrisano";
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
		print "Anketa : $anketa";
		print "Tekst pitanja: $tekst_pitanja" ;
		print "Tip pitanja : $tip_pitanja";	
		$q891=myquery("select id from pitanje ORDER BY id desc limit 1");
$id_pitanja=mysql_result($q891,0,0)+1;


		print $id_pitanja;
		//mozda treba prepraviti posto koristi autoincrement	
		$q800=myquery("insert into pitanje (anketa_id,tip_id,tekst) values ($anketa,$tip_pitanja,'$tekst_pitanja')");
		
	}
	
	
	
	
	$id=$_GET['anketa'];
		
		// Osnovni podaci
	
	$result201=myquery("select id,open_date,close_date,title,info,editable from anketa where id=$id");
    //$result201 = mysql_query($q201);
	
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

?>
	 <a href="?sta=studentska/anketa">Povratak nazad</a>

<center>
	<table border="0" width="600" >
    	<tr>
            <td valign="top" colspan="2" align="center">
				<h2><?=$naziv?> </h2>	
        	</td>
		</tr>
        
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
				 Info : &nbsp; 
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
		print '<tr> <td  > Tekst pitanja </td> <td> Tip pitanja</td> <td> </td></tr>';
			
		// da li se mogu dodavati nova pitanja ili mijenjati postojeca
		if($editable == 0){
				$i=1;
				while ($r202 = mysql_fetch_row($result202)) {			
					
					print  "<tr> <td colspan='3'> <hr/> </td> </tr>";
					print "<tr "; if ($i%2==0) print "bgcolor=\"#EEEEEE\""; print ">";
					print "<td colspan='2'>$i. $r202[1]  </td><td>$r202[2]  </td> </tr>";				
					$i++;
			}	
		
		}		
		else{	
				
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
    <tr>
    
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
// DIO koji se pojavljuje na pocetku 
?>


<center>

<table width="600" border="0">
		<tr>
        	<td align="left">
				
				<p><h3>Studentska sluzba - Anketa</h3></p>
                <div class="anketa_naslov">
                <p><h4>Postojece ankete</h4></p>
                
                </div>
                <?
				$q200=myquery("select id,open_date,close_date,title,info,aktivna from anketa");
				print '<table width="100%" border="0">';
				//$naziv = mysql_result($result200,0,4);
				while ($r200 = mysql_fetch_row($q200)){
					print "<tr><td> $r200[3] ";
					if ($r200[5] == 1 ) print "&nbsp;(<span style='color:#FF0000'> aktivna </span>)";
					print "</td><td align='right'><a href='".genuri()."&akcija=edit&anketa=$r200[0]'>
							Detalji</a></td>";
					
					print "</td><td align='right'>";
					if ($r200[5] == 0 )	 print "<a href='".genuri()."&akcija=podaci&anketa=$r200[0]&subakcija=aktivacija'>Aktiviraj</a>";
				    print "</td></tr>";
				}
				print "</table>";
					
				?>
                <hr>
				<?=genform("POST")?>
                <input type="hidden" name="akcija" value="novi">
                <b>Nova anketa :</b><br/>
                <input type="text" name="naziv" size="50"> <input type="submit" value=" Dodaj ">
                </form>
                <hr>
                
                <div class="anketa_naslov">
                <p><h4>Rezultati ankete </h4></p>
             	</div>
             	
                <?php 
				
				
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
					print "<tr><td colspan='3'> <a href='?sta=izvjestaj/anketa_semestralni'>Semestralni</a> </td>\n";
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