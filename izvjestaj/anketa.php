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


	print "<h3>Sumarna statistika za rank pitanja</h3>\n";
 
	
    // Opste statistike (sumarno za predmet)


	$q30 = myquery("select count(*) from rezultat where predmet_id=$predmet ");
	$broj_anketa = mysql_result($q30,0,0);
	print "<h2> Broj studenata koji su pristupili anketi je : $broj_anketa </h2>";
	
	
	// prvo vidjeti koliko je rank pitanja pa zatim toliko puta napraviit petlju 
	// broj rank pitanja
	$result203=myquery("SELECT id FROM pitanje WHERE anketa_id =8 and tip_id =1");
	//$broj_rank_pitanja= mysql_result($result203,0,0);
	
	$i = 0;
	while ($r01 = mysql_fetch_row($result203)){
		
		$j=$i+1;
		$q60 = myquery(" SELECT avg( izbor_id )FROM odgovor_rank WHERE rezultat_id IN (SELECT id FROM rezultat WHERE predmet_id =$predmet)
					AND pitanje_id = $r01[0]");
	
		$prosjek[$i]=mysql_result($q60,0,0);
		print " nn: $prosjek[$i] bb $r01[0]<br/>";
		$i++;
	}
	
	
	//kupimo pitanja
	$q202="SELECT p.id, p.tekst,t.tip FROM pitanje p,tip_pitanja t WHERE p.tip_id = t.id and p.anketa_id =8 and p.tip_id=1";
    $result202 = mysql_query($q202);
	
	?>
    
    <table width="800px"  >
    	<tr> 
        	<td bgcolor="#6699CC"> Pitanje </td> <td bgcolor="#6699CC" width='350px'> Prosjek odgovora </td>
        </tr>
       
	<tr> 
        	<td colspan="2"> <hr/>  </td>
        </tr>
    
    
	<?
    $i=0;
	while ($r202 = mysql_fetch_row($result202)) {
			$procenat=($prosjek[$i]/5)*100;
			print "<tr >";
			print  "<td>".($i+1) .". $r202[1] </td> <td>    
				<table border='1' width='350px'>
    				<tr> 
        				<td width='$procenat%'  bgcolor='#CCCCFF'>". round($prosjek[$i],2) ." </td> <td>  </td>
        			</tr>
      			</table> 
			</td> 
			</tr>";
			
			$i++;
		}	
	?>
    </table> 
    <?
}
}
?>