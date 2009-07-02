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
	
	$ak_god = intval($_REQUEST['akademska_godina']);
	$studij=intval($_REQUEST['studij']);
	
	if ($studij == 1)
		$semestar = intval($_REQUEST['semestar']);
	else 
		$semestar = intval($_REQUEST['semestar2']);
	$q0111=myquery("select naziv from akademska_godina where id = $ak_god");
	$naziv_ak_god = mysql_result($q0111,0,0);
	
	$q0112=myquery("select naziv from studij where id = $studij");
	$naziv_studija = mysql_result($q0112,0,0);
	
	//prepraviti na :
	$q011 = myquery("select id from anketa where ak_god= $ak_god");	
	
	if (mysql_num_rows($q011)!=0){ // da li postoji anketa uopce
	
		$id_ankete = mysql_result($q011,0,0);	
		
		
	
		$result203=myquery("SELECT count(*) FROM pitanje WHERE anketa_id =$id_ankete and tip_id =1");
		$broj_rank_pitanja= mysql_result($result203,0,0);
	/*
		//kupimo pitanja
		$predmeti;
		$string_rezultata;
		$result202=myquery("SELECT p.id, p.tekst,t.tip FROM pitanje p,tip_pitanja t WHERE p.tip_id = t.id and p.anketa_id =$id_ankete and p.tip_id=1");
		
		$l=0;
		while($pitanje = mysql_fetch_row($result202)){
			$result409=myquery("select pk.id ,p.kratki_naziv from ponudakursa pk,predmet p where p.id=pk.predmet and studij = $studij and semestar = $semestar");
			
			while($predmet = mysql_fetch_row($result409)){
			
				$q6730 = myquery("SELECT sum( b.izbor_id ) / count( * ) FROM rezultat a, odgovor_rank b WHERE a.id = b.rezultat_id AND b.pitanje_id =$pitanje[0] AND a.predmet_id =$predmet[0]");
				$prosjek[$l]=mysql_result($q6730,0,0);
				$predmeti[$l] =$predmet[1] ;
				
				$l++;
			
			}
		
			
		}*/
		
		?>
        <center>
       		<h3>Sumarna statistika za rank pitanja za akademsku godinu <?=$naziv_ak_god?></h3>
             <h3><?=$naziv_studija?></h3>
              <h3><? if ($semestar%2 ==1 ) print  "Zimski semestar"; else print "Ljetni semestar";?></h3>
        </center>
		<table  border="0" align="center">
			<tr> 
				<td bgcolor="#6699CC"  width='350px'> Prikaz prosjeka odgovora po pitanjima za sve predmete </td>
			</tr>
		   
		<tr> 
				<td > <hr/>  </td>
			</tr>
		
		
		<?
		// biramo pitanja za glavnu petlju
		$result2077=myquery("SELECT p.id, p.tekst,t.tip FROM pitanje p,tip_pitanja t WHERE p.tip_id = t.id and p.anketa_id =$id_ankete and p.tip_id=1");
		
		$i=0;
		while ($r202 = mysql_fetch_row($result2077 	)) {
				
				print "<tr ><td align='center'> $r202[1] </td> <tr>";
				print  "<td> <img src='izvjestaj/chart_semestralni.php?pitanje=$r202[0]&semestar=$semestar&studij=$studij'>
				  <hr />
				</td> 
				
				</tr>";
				
						
			}	
		?>
		
		</table> 
		<?
	}// kraj uslova da li postoji anketa
	else
	print "Jos nije kreirana niti jedna anketa!!";
}
	else if ($_REQUEST['akcija']=="po_smjerovima"){
		
		$ak_god = intval($_REQUEST['akademska_godina']);
		$semestar = intval($_REQUEST['semestar']);
		
		$q0111=myquery("select naziv from akademska_godina where id = $ak_god");
		$naziv_ak_god = mysql_result($q0111,0,0);
		
		//prepraviti na :
		$q011 = myquery("select id from anketa where ak_god= $ak_god");	
		$anketa = mysql_result($q011,0,0);
		
	
		?>
			 <center>
				<h3>Sumarna statistika za rank pitanja za akademsku godinu <?=$naziv_ak_god?>  po smjerovima</h3>
				  <h3><? if ($semestar ==1 ) print  "Zimski semestar"; if ($semestar ==2 ) print "Ljetni semestar"; if ($semestar ==0 ) print "Cijela godina";?> </h3>
			</center>
		
			<table align="center" >
				<tr>
			   
					<td align="center" bgcolor='#00FF00' height="20" width="150"> PGS  </td>
					<td align="center" bgcolor='#FF0000' width="150"> RI  </td>
					<td align="center" bgcolor='#0000FF' width="150"> AE  </td>
					<td align="center" bgcolor='#00FFFF' width="150"> EE  </td>
					<td align="center" bgcolor='#FFFF00' width="150"> TK  </td>
						
				</tr>
				
				<tr>
					<td colspan="5">
						<img src='izvjestaj/po_smjerovima_linijski.php?anketa=<?=$anketa?>&semestar=<?=$semestar?>'>
					</td>            
				</tr>
						
			</table>
		
		
		<?
		
		
		}
}
?>