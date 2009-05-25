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
</script>
<?

function izvjestaj_anketa_semestralni(){

if ($_REQUEST['akcija']=="izvrsi"){
	
	$semestar = intval($_REQUEST['semestar']);
	$ak_god = intval($_REQUEST['akademska_godina']);
	$studij=intval($_REQUEST['studij']);
	
	print "$semestar  jj  $ak_god   $studij";
	
	
	print "<h3>Sumarna statistika za rank pitanja</h3>\n";
 	
	
	$q011 = myquery("select id from anketa where aktivna = 1");	
	$id_ankete = mysql_result($q011,0,0);
	
    // Opste statistike (sumarno za predmet)

	// prvo vidjeti koliko je rank pitanja pa zatim toliko puta napraviit petlju 
	// broj rank pitanja
	$q203="SELECT count(*) FROM pitanje WHERE anketa_id =8 and tip_id =1";
	$result203 = mysql_query($q203);
	$broj_rank_pitanja= mysql_result($result203,0,0);

	
	
	//kupimo pitanja
	$predmeti;
	$string_rezultata;
	$result202=myquery("SELECT p.id, p.tekst,t.tip FROM pitanje p,tip_pitanja t WHERE p.tip_id = t.id and p.anketa_id =8 and p.tip_id=1");
	
	$l=0;
	while($pitanje = mysql_fetch_row($result202)){
		$result409=myquery("select pk.id ,p.kratki_naziv from ponudakursa pk,predmet p where p.id=pk.predmet");
		
		while($predmet = mysql_fetch_row($result409)){
		
			$q6730 = myquery("SELECT sum( b.izbor_id ) / count( * ) FROM rezultat a, odgovor_rank b WHERE a.id = b.rezultat_id AND b.pitanje_id =$pitanje[0] AND a.predmet_id =$predmet[0]");
			$prosjek[$l]=mysql_result($q6730,0,0);
			$predmeti[$l] =$predmet[1] ;
			
			$l++;
		
		}
	
		
	}
	
	?>
    
    <table  border="0" align="center">
    	<tr> 
        	<td bgcolor="#6699CC" width='350px'> Prikaz prosjeka odgovora po pitanjima za sve predmete </td>
        </tr>
       
	<tr> 
        	<td > <hr/>  </td>
        </tr>
    
    
	<?
	// biramo pitanja za glavnu petlju
	$result2077=myquery("SELECT p.id, p.tekst,t.tip FROM pitanje p,tip_pitanja t WHERE p.tip_id = t.id and p.anketa_id =8 and p.tip_id=1");
	
    $i=0;
	while ($r202 = mysql_fetch_row($result2077 	)) {
			
			print "<tr >";
			print  "<td> <img src='izvjestaj/chart_semestralni.php?pitanje=$r202[0]'></td> 
			
			</tr>";
			
					
		}	
	?>
    
    </table> 
    <?
}
	else{
// to do dadati mozda i opciju da se bira i anketa a ne da se automatski uzima aktivna
?>
				
				<form method="post" action>
                    
                    
                    <table align="center">
                    <tr>
                    	<td colspan="2">
                        <h3>Sumarna statistika za rank pitanja</h3>
                        </td>
                    </tr>
                    <tr>
                    	<td>
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
                            	<select name="semestar" id="semestar">
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
                    
                    
                   

<?
}
}
?>