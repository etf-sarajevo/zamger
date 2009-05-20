<?
function izvjestaj_anketa_semestralni(){

$predmet = intval($_REQUEST['predmet']);


	
	// ako je izvjestaj za rank pitanja


	print "<h3>Sumarna statistika za rank pitanja</h3>\n";
 
	
    // Opste statistike (sumarno za predmet)


	
	
	
	// prvo vidjeti koliko je rank pitanja pa zatim toliko puta napraviit petlju 
	// broj rank pitanja
	$q203="SELECT count(*) FROM pitanje WHERE anketa_id =8 and tip_id =1";
	$result203 = mysql_query($q203);
	$broj_rank_pitanja= mysql_result($result203,0,0);

	
	
	//kupimo pitanja
	
	$result202=myquery("SELECT p.id, p.tekst,t.tip FROM pitanje p,tip_pitanja t WHERE p.tip_id = t.id and p.anketa_id =8 and p.tip_id=1");
	
	$l=0;
	while($pitanje = mysql_fetch_row($result202)){
		$result409=myquery("select pk.id ,p.kratki_naziv from ponudakursa pk,predmet p where p.id=pk.predmet");
		
		while($predmet = mysql_fetch_row($result409)){
		
			$q6730 = myquery("SELECT sum( b.izbor_id ) / count( * ) FROM rezultat a, odgovor_rank b WHERE a.id = b.rezultat_id AND b.pitanje_id =$pitanje[0] AND a.predmet_id =$predmet[0]");
			$prosjek[$l]=mysql_result($q6730,0,0);
			
			$l++;
		
		}
	
	}
	
	
	
	
	
	
    
	
	?>
    
    <table  border="0" >
    	<tr> 
        	<td bgcolor="#6699CC" width='350px'> Pitanje </td> <td  bgcolor="#6699CC" > Prosjek odgovora </td>
        </tr>
       
	<tr> 
        	<td colspan="2"> <hr/>  </td>
        </tr>
    
    
	<?
	// biramo pitanja za glavnu petlju
	$result2077=myquery("SELECT p.id, p.tekst,t.tip FROM pitanje p,tip_pitanja t WHERE p.tip_id = t.id and p.anketa_id =8 and p.tip_id=1");
	
    $i=0;
	while ($r202 = mysql_fetch_row($result2077 	)) {
			
			print "<tr >";
			print  "<td>".($i+1) .". $r202[1] </td> 
			<td height='100'>    
				<table border='0'  width='350'>
    				<tr height='100'> ";
					
					$result2016=myquery("select pk.id, p.kratki_naziv from ponudakursa pk,predmet p where p.id=pk.predmet");
					while ($r2016 = mysql_fetch_row($result2016)) {
					$procenat=($prosjek[$i]/5)*100;
					?>
                    <td align="center" valign="bottom">
					<table width="40" bgcolor='#CC445F' height="<?=$procenat?>%"  border="0"> 
                    	
                        <tr  >
                         <td align="center" valign="bottom">
                         	<?=$procenat?>%
                         </td>
                         </tr>
                    	<tr  >
                         <td align="center" valign="bottom">
                         	<?=$r2016[1]?> 
                         </td>
                         </tr>
                     </table>
        				
                        </td>
        			<?
					$i++;
                    //print "<td >". round($prosjek[$i],2) ." </td> ";
					}
			print "</tr>
      			</table> 
			</td> 
			</tr>";
			
		}	
	?>
    </table> 
    <?

}
?>