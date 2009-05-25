<?
function izvjestaj_anketa(){

$predmet = intval($_REQUEST['predmet']);

$rank = intval($_REQUEST['rank']);

if ($_REQUEST['komentar'] == "da") 
{  

	$limit = 5; // broj kometara prikazanih po stranici
	$offset = intval($_REQUEST["offset"]);
	
	
	// ako je izvjestaj za komentare


	print "<h3>Prikaz svih komentara</h3>\n";
 
	
	// Opste statistike (sumarno za predmet)


	$q30 = myquery("select count(*) from rezultat where predmet_id=$predmet ");
	$broj_anketa = mysql_result($q30,0,0);
	
	print "<h2> Broj studenata koji su pristupili anketi je : $broj_anketa </h2>";
	
	// pokupimo sve komentare za dati predmet
	
	
	$q60 = myquery("SELECT count(*) FROM odgovor_text WHERE rezultat_id IN (SELECT id FROM rezultat WHERE predmet_id =$predmet)");
	
	$broj_odgovora = mysql_result($q60,0,0);
	
	$q61 = myquery(" SELECT response FROM odgovor_text WHERE rezultat_id IN (SELECT id FROM rezultat WHERE predmet_id =$predmet) limit $offset, $limit");
	
	
	if ($broj_odgovora == 0)
			print "Nema rezultata!";
	else if ($broj_odgovora > $limit) {
			
			print "Prikazujem rezultate ".($offset+1)."-".($offset+5)." od $broj_odgovora. Stranica: ";

			for ($i=0; $i < $broj_odgovora; $i+=$limit) {
				$br = intval($i/$limit)+1;
				
				if ($i == $offset)
					print "<b>$br</b> ";
				else
					print "<a href=\"?sta=izvjestaj/anketa&predmet=2&komentar=da&offset=$i\">$br</a> ";
			}
			print "<br/>";
		}

	
	?>
    
    <table width="650px"  >
    	 <tr>
        	<td bgcolor="#6699CC" height="10">   </td>
        </tr>
       
	    
	<?
    $i=0;
	while ($r61 = mysql_fetch_row($q61)) {
			
			print  "<tr >"; 
			print  "<td>  <hr/>  </td>"; 
			print  "</tr>";
			print  "<tr >";
			//print  "<td>".($i+1) .". </td>"; 
			print  "<td>    $r61[0] </td>"; 
			print  "</tr>";
			
			$i++;
		}	
	?>
    
    </table> 
    <?
	
		

	}// kraj komentara

	else if ($_REQUEST['rank'] == "da") 
	{
	
	// ako je izvjestaj za rank pitanja

	$result233=myquery("select p.naziv from predmet as p, ponudakursa as pk where pk.predmet=p.id and pk.id=$predmet ");
	$naziv_predmeta = mysql_result($result233,0,0);


	print "<h2>Statistika za predmet $naziv_predmeta</h2>\n";
 
	
    // Opste statistike (sumarno za predmet)


	$q30 = myquery("select count(*) from rezultat where predmet_id=$predmet ");
	$broj_anketa = mysql_result($q30,0,0);
	print "<h3> Broj studenata koji su pristupili anketi je : $broj_anketa </h3>";
	
	
	 
	// broj rank pitanja
	$result203=myquery("SELECT id FROM pitanje WHERE anketa_id =8 and tip_id =1");
	
	
	$i = 0;
	while ($r01 = mysql_fetch_row($result203)){
		
		$j=$i+1;
		$q60 = myquery(" SELECT avg( izbor_id )FROM odgovor_rank WHERE rezultat_id IN (SELECT id FROM rezultat WHERE predmet_id =$predmet)
					AND pitanje_id = $r01[0]");
	
		$prosjek[$i]=mysql_result($q60,0,0);
		
		$i++;
	}
	
	
	//kupimo pitanja
	$result202=myquery("SELECT p.id, p.tekst,t.tip FROM pitanje p,tip_pitanja t WHERE p.tip_id = t.id and p.anketa_id =8 and p.tip_id=1");
   
	
	?>
    
    <table width="800px"  >
    	<tr> 
        	<td bgcolor="#6699CC"> Pitanje </td> <td bgcolor="#6699CC" width='350px'> Prosjek odgovora </td>
        </tr>
       
	<tr> 
        	<td colspan="2"> <hr/>  </td>
        </tr>
          <tr > 
             <td  > </td> <td bgcolor="#FF0000" width='350px'> &nbsp;MAX </td>
         </tr>
    
    
	<?
    $i=0;
	while ($r202 = mysql_fetch_row($result202)) {
			$procenat=($prosjek[$i]/5)*100;
			print "<tr >";
			print  "<td>".($i+1) .". $r202[1] </td> <td>    
				<table border='0' width='350px'>
    				<tr> 
        				<td width='$procenat%'  bgcolor='#CCCCFF'>". round($prosjek[$i],2) ." </td> <td width='".(100-$procenat)."%'> </td>
        			</tr>
      			</table> 
			</td> 
			</tr>";
			
			$i++;
		}	
		$prosjek = array_sum($prosjek)/sizeof($prosjek);

	?>
    <tr> 
        	<td colspan="2"> <hr/>  </td>
        </tr>
          <tr > 
             <td align="right"> Prosjek predmeta : </td> <td  width='350px'> &nbsp;<strong><?=round($prosjek,2)?> </strong> </td>
         </tr>
    </table> 
    <?
}
}
?>