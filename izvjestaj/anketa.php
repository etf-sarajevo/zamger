<?

// IZVJESTAJ/ANKETA - stranica koja generise izvjestaje za predmete koje mogu pregledati profesori ili clanovi studentske sluzbe

function izvjestaj_anketa(){

	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);
	global $userid,$user_siteadmin,$user_studentska;

	// naziv predmeta
	$result233=myquery("select p.naziv,pk.akademska_godina,p.id from predmet as p, ponudakursa as pk where pk.predmet=p.id and p.id=$predmet and pk.akademska_godina=$ag; ");
	$naziv_predmeta = mysql_result($result233,0,0);
	
	// provjera da li je dati profesor zadužen na predmetu za koji želi pogledat izvještaj
	if (!$user_siteadmin && !$user_studentska){
		$q001 = myquery("select admin from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
		if (mysql_num_rows($q001)==0) {
			zamgerlog("nastavnik/izvjestaj_anketa privilegije ",3);
			biguglyerror("Nemate pravo pregledati ovaj izvještaj!");
			return;
		}
	}
	
	// naziv akdemske godine
	$q0111=myquery("select naziv from akademska_godina where id = $ag");
	$naziv_ak_god = mysql_result($q0111,0,0);
	
	// da li postoji anketa
	$q011 = myquery("select id from anketa where akademska_godina= $ag");	
	if (mysql_num_rows($q011)==0){ // da li postoji anketa uopce
		biguglyerror("Za datu akademsku godinu nije bila kreirana anketa!");
		return;
	}

	$rank = intval($_REQUEST['rank']);

	//aktuelna anketa
	$q12 = myquery("select id from anketa where akademska_godina=$ag");
	$anketa = mysql_result($q12,0,0);

	if ($_REQUEST['komentar'] == "da") {  
		// ---------------------------------------------   IZVJESTAJ ZA KOMENTARE ---------------------------------------------
		
		$limit = 5; // broj kometara prikazanih po stranici
		$offset = intval($_REQUEST["offset"]);
	
		print "<center>";
		print "<h2>Prikaz svih komentara za predmet $naziv_predmeta za akademsku godinu $naziv_ak_god</h2>\n";
	
	 	$q30 = myquery("select count(*) from rezultat where predmet=$predmet and anketa = $anketa AND zavrsena='Y'");
		$broj_anketa = mysql_result($q30,0,0);
		
		print "<h3> Broj studenata koji su pristupili anketi je : $broj_anketa </h3>";
		
		
		// pokupimo sve komentare za dati predmet
		$q60 = myquery("SELECT count(*) FROM odgovor_text WHERE rezultat IN (SELECT id FROM rezultat WHERE predmet =$predmet and anketa=$anketa AND zavrsena='Y')");
		$broj_odgovora = mysql_result($q60,0,0);
		$q61 = myquery(" SELECT odgovor FROM odgovor_text WHERE rezultat IN (SELECT id FROM rezultat WHERE predmet =$predmet and anketa=$anketa) limit $offset, $limit");
		
		if ($broj_odgovora == 0)
				print "Nema rezultata!";
		else if ($broj_odgovora > $limit) {
				
			print "Prikazujem rezultate ".($offset+1)."-".($offset+5)." od $broj_odgovora. Stranica: ";
			for ($i=0; $i < $broj_odgovora; $i+=$limit) {
				$br = intval($i/$limit)+1;
				
				if ($i == $offset)
					print "<b>$br</b> ";
				else
					print "<a href=\"?sta=izvjestaj/anketa&predmet=$predmet&komentar=da&offset=$i\">$br</a> ";
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
			print  "<td>    $r61[0] </td>"; 
			print  "</tr>";
			
			$i++;
		}	
		?>
		
		</table> 
		</center>
		<?
		
	}
	// -------------------------------------------------   KRAH IZVJESTAJ ZA KOMENTARE  ------------------------------------------------------------------------
	
	// -------------------------------------------------   IZVJESTAJ ZA RANK PITANJA  ------------------------------------------------------------------------
	else if ($_REQUEST['rank'] == "da") {

		print "<center>";
		print "<h2>Statistika za predmet $naziv_predmeta za akademsku godinu $naziv_ak_god</h2>\n";
 		// Opste statistike (sumarno za predmet)


		$q30 = myquery("select count(*) from rezultat where predmet=$predmet and anketa = $anketa AND zavrsena='Y'" );
		$broj_anketa = mysql_result($q30,0,0);
		print "<h3> Broj studenata koji su pristupili anketi je : $broj_anketa </h3>";
		
		// broj rank pitanja
		$result203=myquery("SELECT id FROM pitanje WHERE anketa =$anketa and tip_pitanja =1");
		
		$i = 0;
		while ($r01 = mysql_fetch_row($result203)){
			$j=$i+1;
			$q60 = myquery(" SELECT avg( izbor_id )FROM odgovor_rank WHERE rezultat IN (SELECT id FROM rezultat WHERE predmet =$predmet and anketa = $anketa AND zavrsena='Y')
						AND pitanje = $r01[0]");
			$prosjek[$i]=mysql_result($q60,0,0);
			$i++;
		}
		
		//kupimo pitanja
		$result202=myquery("SELECT p.id, p.tekst,t.tip FROM pitanje p,tip_pitanja t WHERE p.tip_pitanja = t.id and p.anketa =$anketa and p.tip_pitanja=1");
   
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
			print "<tr height='35'>";
			print  "<td>".($i+1) .". $r202[1] </td> <td>    
				<table border='0' width='350px'>
    				<tr> 
        				<td height='30' width='$procenat%'  bgcolor='#CCCCFF'> &nbsp;". round($prosjek[$i],2) ." </td> <td width='".(100-$procenat)."%'> </td>
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
		</center>
		<?
	}
}
?>