<?php 

// PUBLIC/ANKETA - stranica za ispunjavanje ankete


global $_lv_; 

$k=1;
	// uzimamo id aktivne ankete
	$q01 = myquery("select id from anketa where aktivna = 1");
	$id_ankete = mysql_result($q01,0,0);

function Ubaci_pitanje($tip_pitanja){
	global $id_ankete;
	

	// kupitmo pitanja u zavisnosti od argumenta koji je poslan
	$results1= myquery("select id,tekst from pitanje p where tip_id = '".$tip_pitanja."' and anketa_id=$id_ankete");
//	$results1 = mysql_query($query1);
	global $k;
	$j=$k;
	
	// ako je pitanje rank
	if ($tip_pitanja == 1){
			
			while ($pitanje = mysql_fetch_assoc($results1)) {
			
				$id = $pitanje['id'];
				$tekst = $pitanje['tekst'];
				
				$result2= myquery("select izbor from izbori_pitanja where pitanje_id = '$id'");
				$br_rez=mysql_num_rows($result2);
			
			
				// dodati input hiden kako bismo znali id pitanja za kasnije cuvanje rezultata :)
				
				print "<tr>  <td> $tekst  <input type='hidden' name='id_pitanja$j' value=$id>  </td>";
				
				for ($i=1; $i<=5;$i++){
					$u=$i-1;
					if ($br_rez){
						$izbor = mysql_result($result2,$u,0);
						//print "ovo je ispis izbora : $izbor";
						echo "<td> <input type='radio' name='izbor"."$j'"." value="."$i /> ".$izbor."  </td>";
						}
					else
						{
						echo "<td> <input type='radio' name='izbor"."$j'"." value="."$i /> ".$i."  </td>";
						}
					
				
				}
				echo "</tr>";
				$j++;
				}	
		}
	else if ($tip_pitanja == 2)
		{	
			while ($pitanje = mysql_fetch_assoc($results1)) {
				$id = $pitanje['id'];
				$tekst = $pitanje['tekst'];
				
				echo "<tr>  <td colspan = '6'>".$tekst." <input type='hidden' name='id_pitanja$j' value=$id></td></tr>";
				echo "<tr>";
				
				echo "<td colspan ='6' align = 'center'> <textarea  name='coment$j' rows='7' cols='40'>  </textarea> </td>";
				echo "</tr>";
				$j++;
				}	
			
		}
		$k=$j;
}


?>

<script type="text/javascript" >

function Validate()
{
    brojac=0;
	for (i= 0; i<5; i++)
	if (document.forma.izbor1[i].checked)
        brojac++;
	for (i= 0; i<5; i++)
	if (document.forma.izbor2[i].checked)
        brojac++;
	for (i= 0; i<5; i++)
	if (document.forma.izbor3[i].checked)
        brojac++;
	for (i= 0; i<5; i++)
	if (document.forma.izbor4[i].checked)
        brojac++;
	
	if(brojac==4)
		return true;
		
		
    alert('Niste odgovorili na sva pitanja');
	
    return false;
}

</script>

<?php 
function public_anketa(){

	$q10 = myquery("select id,naziv from akademska_godina where aktuelna=1");
	$ag = mysql_result($q10,0,0);
	
	$q09= myquery("select id,naziv,UNIX_TIMESTAMP(datum_zatvaranja) from anketa where aktivna=1 and ak_god=$ag");
	$anketa = mysql_result($q09,0,0);
	$naziv= mysql_result($q09,0,1);
	$rok=mysql_result($q09,0,2);
	if (time () > $rok){
	
		biguglyerror("Isteklo vrijeme za ispunjavanje ankete");
		return;
	}
	
	
	// da li je student zavrsio anketu 
	if ($_POST['akcija'] == "finish" ) {
		
		global $id_ankete;
		
		$id_rezultata = $_POST['id_rezultata'];
		//mogao bi se prvo napraviti query na tabelu tip pitanja pa zatim za svaki slog vidjeti da li je tih pitanja bilo u anketi
	
		
		// broj rank pitanja
		$q203=myquery("SELECT count(*) FROM pitanje WHERE anketa_id =$id_ankete and tip_id =1");
		$broj_rank_pitanja= mysql_result($q203,0,0);
		 
		 $j=1;
		 for ($i=0; $i<$broj_rank_pitanja ; $i++)
			{
				
				$izbori[$i] = $_POST['izbor'.$j];
				$id_pitanja[$i] = $_POST['id_pitanja'.$j];
				$j++;
		}
		
		// ubaciti sve odgovore u tabelu odgovori_rank
		for ($i=0; $i<$broj_rank_pitanja ; $i++){
			
			$q590 = myquery("insert into odgovor_rank set rezultat_id=$id_rezultata, pitanje_id=$id_pitanja[$i], izbor_id=$izbori[$i]");
		
		}
		
		// broj esejskih pitanja
		$q204="SELECT count(*) FROM pitanje WHERE anketa_id =$id_ankete and tip_id =2";
		$result204 = mysql_query($q204);
		$broj_esej_pitanja= mysql_result($result204,0,0);
		 
		 for ($i=0; $i<$broj_esej_pitanja ; $i++)
			{
				$coment[$i] = my_escape($_POST['coment'.$j]);
				$id_pitanja[$i] = $_POST['id_pitanja'.$j];
				$j++;
			}
		// ubaciti sve odgoovre u tabelu odgovori_text
		for ($i=0; $i<$broj_esej_pitanja ; $i++){
			
			$q590 = myquery("insert into odgovor_text set rezultat_id=$id_rezultata, pitanje_id=$id_pitanja[$i], response='$coment[$i]'");
		
		}
		
		?>	
    	<center>
    	<p> Hvala na ispunjavanju ankete. </p>
    	<a href="http://www.zamger.dev/trunk/index.php"> Nazad na pocetnu </a>
    	</center>
	<?
	// kako znam koji je id pitanja mozemo znati i u koju tabelu treba ubaciti odgovor :D
		//$q590 = myquery("insert into odgovor_rank set rezultat_id=1, pitanje_id=$id_pitanja1, izbor_id=$izbor1");
	
	
	// nakon uspjesnog ispunjenja ankete postaviti i polje zavrsena na true u tabeli razultati
	$q600 = myquery("update rezultat set zavrsena='Y' where id=$id_rezultata");
	
}

//  dio koji ide nakon sto je student unio kod za anketu te stistnuo dugme
else if($_POST['akcija'] == "prikazi") {

		// kupimo kod koji je student unio
		$unique_hash_code = $_POST['kod'];
		
		// provjeravamo da li je dati student zatrazio kod te da li je vec ispunjavao datu anketu sa poljem zavrsena
		
		$q590 = myquery("SELECT count( * ),id,predmet_id FROM rezultat WHERE unique_id = '$unique_hash_code' AND zavrsena = 'N'");
		$broj_rezultata =mysql_result($q590,0,0);
		if($broj_rezultata==0){
		
			// dio koji ide ako dati hesh ne postoji u bazi tj ako student pokusava da izmisli hesh :P
		?>
			<center>
    	<p> Zao nam je ali ili ste vec ispunili anketu ili dati kod ne postoji u bazi!! </p>
    	<a href="http://www.zamger.dev/trunk/index.php"> Nazad na pocetnu </a>
    	</center>
		<?	
			
		
		}
		else  { // else 15   uspjesno 
		
		$id_rezultata =mysql_result($q590,0,1);
		$ponudakursa = mysql_result($q590,0,2);
		$q011= myquery("select naziv from ponudakursa pk,predmet p where pk.predmet = p.id and pk.id = $ponudakursa");
		$naziv_predmeta = mysql_result($q011,0,0);
		
		?>
		<center>
            <h2> Anketa za predmet <?= $naziv_predmeta?> </h2>
            


        </center>
		<form id="forma" name="forma" method="post" action="<?php echo $PHP_SELF?>" onSubmit="return Validate()">
            <input type="hidden" name="akcija" value="finish">
            <input type="hidden" name="id_rezultata" value="<?=$id_rezultata?>">
            
            <table align="center" cellpadding="4" border="0" >
             	<tr>  <td colspan = '6'><hr/> <strong> U sljedecoj tabeli  izaberite samo jednu od ocjena za iskazanu tvrdnju na skali ocjena od 1 (najlosija)  do 5 (najbolja). </strong></td></tr>;
                <?php 
            
                    echo "<tr>  <td colspan = '6'><hr/> </td></tr>";
                    Ubaci_pitanje(1);
                    echo "<tr>  <td colspan = '6'><hr/> </td></tr>";
                    Ubaci_pitanje(2); 
                    echo "<tr>  <td colspan = '6'><hr/> </td></tr>";
                ?>
     
            </table>
            
            <br />
            <table align="center">
                <tr> 
                    <td>
                 	   <input align="middle"  type="submit" value="Posalji" ></input></input>
                    </td>
                </tr>
            </table>
		</form>


<?
		} // kraj od else 15
}

else{


	?>
     <table align="center" cellpadding="0">
     	<form method="post" >
        <tr>
        	<td>
            	<br/>
            	Unesite kod koji ste dobili za ispunjavanje anekte: &nbsp;
            		
            </td>
			<td>
            	<br/>
            	<input type="hidden" id="akcija" name="akcija" value="prikazi">
                <input type="text" id="kod" name="kod"  size="60">
            		
            </td>
            

        </tr>
     	<tr>
        	<td colspan="2" align="center">
            	<br/>
            	 <input type="submit" value="Posalji">
            </td>
        </tr>
 		</form>
     </table>
    
    
    <?	

}
}// kraj dijela za anketu
?>
