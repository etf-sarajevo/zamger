<SCRIPT LANGUAGE="JavaScript">
<!-- Beginning of JavaScript -


function MsgBox (textstring) {
alert (textstring) }


// - End of JavaScript - -->
</SCRIPT>


<?
//NASTAVNIK/TIP-modul koji ce omoguciti definisanja sistema bodovanja na predmetu

function nastavnik_tip() {

global $userid,$user_siteadmin;

//pokupimo parametre

$predmet = intval($_REQUEST['predmet']);
$ag = intval($_REQUEST['ag']);
$brojK=intval($_REQUEST['komp']);
if(!isset($_REQUEST['komp'])) $brojK=1;
$brojIspita=intval($_SESSION['brojIspita']);
if(!isset($_SESSION['brojIspita'])){ $_SESSION['brojIspita']=$brojIspita=0;}
$brojPrisustva=intval($_SESSION['brojPrisustva']);
if(!isset($_SESSION['brojPrisustva'])) {$_SESSION['brojPrisustva']=$brojPrisustva=0;}
$brojZadaca=intval($_SESSION['brojZadaca']);
if(!isset($_SESSION['brojZadaca'])) {$_SESSION['brojZadaca']=$brojZadaca=0;}

//dijelovi koda koji vode racuna o broju definisanih komponenti

include("nastavnik/dodaj_ispit.php");

// Naziv predmeta
$q10 = myquery("select naziv from predmet where id=$predmet");
if (mysql_num_rows($q10)<1) {
	biguglyerror("Nepoznat predmet");
	zamgerlog("ilegalan predmet $predmet",3); //nivo 3: greska
	return;
}
$predmet_naziv = mysql_result($q10,0,0);



// Da li korisnik ima pravo ući u modul?

if (!$user_siteadmin) { // 3 = site admin
	$q10 = myquery("select admin from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
	if (mysql_num_rows($q10)<1 || mysql_result($q10,0,0)<1) {
		zamgerlog("nastavnik/tip privilegije (predmet pp$predmet)",3);
		biguglyerror("Nemate pravo ulaska u ovu grupu!");
		return;
	} 
}

?>

<?
if(isset($_POST['submit'])){
	//spasavanje izmjena za novodefinisani tip predmeta
	
	//sabira broj poena na pojedinim komponentama i smijesta u $suma
	include("suma.php");

if($suma>100){
	
	header("location:../zamger/index.php?sta=nastavnik/tip&predmet=".$predmet."&ag=".$ag."&obiljezeno=pregled&komp=".$brojK."&greska=1");
}
else{
	
	
	$q10 = myquery("select naziv from predmet where id=".$predmet."");

	while($r10 = mysql_fetch_row($q10)){
		$naziv=$r10[0];
			//$q11 = myquery("select tippredmeta from akademska_godina_predmet where predmet=".$predmet." AND akademska_godina=".$ag."");
				//		$r11 = mysql_fetch_row($q11);
					//	$id_predmeta=$r11[0];
						$q11 = myquery("select id from tippredmeta where  naziv ='".$naziv.$ag."'");
						$id_predmeta=$r11[0];
						//$q11 = myquery("select naziv from tippredmeta where id=".$id_predmeta." AND naziv LIKE '".$naziv."%'");
						if($r11 = mysql_fetch_row($q11)){
							$id_predmeta=$r11[0];
						//$naziv2=$r11[0];
						//if(($naziv.$ag)==$naziv2){
							
		
							//brisemo sve komponente definisane na ovom predmetu
							$q12 = myquery("select tippredmeta, komponenta from tippredmeta_komponenta where tippredmeta=".$id_predmeta."");
							while($r12=mysql_fetch_row($q12)){
								$id_komponente=$r12[1];
								$q13 = myquery("DELETE FROM komponenta WHERE id=".$id_komponente."");
								$q13 = myquery("DELETE FROM tippredmeta_komponenta WHERE komponenta=".$id_komponente." AND 		
											   tippredmeta=".$id_predmeta."");
							}
							
						$q13 = myquery("DELETE FROM tippredmeta WHERE id=".$id_predmeta."");
						
						}
						$q13 = myquery("DELETE FROM akademska_godina_predmet where predmet=".$predmet." AND akademska_godina=".$ag."");
						
	}
	
	//spasavamo nase novodefinisane komponente
	
	include("nastavnik/spasi.php");
	
	
	
	header("location:../zamger/index.php?sta=nastavnik/tip&predmet=".$predmet."&ag=".$ag);  
}
}
elseif(isset($_POST['submit2'])){
 	//spasavanje izmjena za novodefinisani tip predmeta
	$pregled=$_SESSION['spasi'];
	
	//$q14=myquery("UPDATE predmet
		//	 SET tippredmeta=$pregled
			// WHERE id=$predmet;");
		
	$q14=myquery("UPDATE akademska_godina_predmet
			 SET tippredmeta=$pregled
			 WHERE predmet=$predmet AND akademska_godina=$ag;");
	
	
	header("location:../zamger/index.php?sta=nastavnik/tip&predmet=".$predmet."&ag=".$ag);
}

else{

?>

<p>&nbsp;</p>

<p><h3>Definišite tip predmeta - <?=$predmet_naziv?></h3></p>

<?

if(isset($_REQUEST['greska'])){
				   //ukoliko je broj bodova na novodefinisanom predmetu preko 100
				   
				   		?>
	    
		<SCRIPT language=JavaScript>
        		MsgBox("Maksimalan broj bodova za Vas tip predmeta ne smije biti veci od 100!");
		</SCRIPT>
        
        <?
				   }
if($_REQUEST['obiljezeno']!=false){
	//Definisanje vlastitog tipa predmeta
	meni_za_tip_predmeta();
	
	if($_REQUEST['obiljezeno']=="naziv"){
		//definisanje naziva novog tipa predmeta
		?>
        <? $q10 = myquery("select naziv from predmet where id=".$predmet."");
				    while($r10 = mysql_fetch_row($q10)){
						$naziv=$r10[0];
					}
	     ?>
     		</br>
        	 <font size="2">Naziv Vaseg novokreiranog tipa predmeta ce biti <? echo $naziv; ?> .</font>
            </br></br></br>
            <table>
            <tr>
            <td>
            <a href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>'><font size="2" color="#000066">Nazad</font></a></td>
            <td align="right" width="450 px">
            <a href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&obiljezeno=ispiti'><font size="2" color="#000066">Dalje</font></a>
            </td>
            </tr>
            </table>
        <?
	}
	else if($_REQUEST['obiljezeno']=="dodaj"){
		//definisanje fiksnih komponenti
			//uzimanje podataka sa forme i smijestanje u varijablu $TabelaFiksnih
			include("nastavnik/uzmi_fiksne.php");
		
		?>
        	<font size="3" style="font-family:'Times New Roman', Times, serif">
            Ovdje možete definisati vlastite komponente predmeta, koje se boduju, npr. seminarski rad i sl.
            </br></br>
            </font>
            
<table>
            <tr>
            <td>
            
        	<form method="post" action="">
            	<table border="0"><tr bgcolor="#bbbbbb">
				<td>Naziv komponente</td><td>Max. bodova</td><td>Prolaz</td><td>Dodaj</td><td>Uslov</td>
				</tr>
                <? 
				for($i=0;$i<$brojK;$i++){
				if ($bgcolor=="") $bgcolor="bgcolor=\"#efefef\""; else $bgcolor=""; ?>
                <tr <? $bgcolor ?>>
                <td width="30"><input style="text" name="nazivKomponente<? echo $i; ?>" width="29" align="middle" value="<? echo $TabelaFiksnih[0][$i]?>"/></td>
                <td width="30"><input style="text" name="maxBodova<? echo $i; ?>" width="29" align="middle" value="<? echo $TabelaFiksnih[1][$i]?>"/></td>
                <td width="30"><input style="text" name="prolazBodova<? echo $i; ?>" width="29" align="middle" value="<? echo $TabelaFiksnih[2][$i]?>"/></td>
                <td width="30"><input type="checkbox" name="Fiksne<? echo $i; ?>" value="Dodaj" onclick="this.form.submit();" <? if($TabelaFiksnih[3][$i]==1) echo "checked=\"yes\""; ?> /></td>
                <td width="30"><input type="checkbox" name="UslovFiksne<? echo $i; ?>" value="Uslov" onclick="this.form.submit();" <? if($TabelaFiksnih[4][$i]==1) echo "checked=\"yes\""; ?>/></td>
                </tr>
                <? } ?>
                </table>
                </form>
                </br>
                 <a href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&obiljezeno=dodaj&komp=<? echo $brojK+1; ?>'><font size="1" color="#000066">Dodaj Komponentu</font></a>
                  </br></br>
            </td>
            <td width="20 px">
            </td>
            <td><? pregled_predmeta_bez_naziva($predmet); ?>
            </td>
            </tr>
                        <tr>
            <td>
            <a href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&obiljezeno=usmeni&komp=<? echo $brojK; ?>'><font size="2" color="#000066">Nazad</font></a></td><td></td><td align="right" width="450 px">
            <a href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&obiljezeno=pregled&komp=<? echo $brojK; ?>'><font size="2" color="#000066">Dalje</font></a>        
            </td>
            </tr>
            </table>
        <?
	}
	
	

	else if($_REQUEST['obiljezeno']=="ispiti"){
		//definisanje ispita
		    //uzimanje podataka sa forme i smijestanje u varijablu $TabelaPismenihIspita
			include("nastavnik/uzmi_pismene.php");
	
		?>
        <table>
        <tr>
  <td>
        	<form method="post" action="<? $PHP_SELF ?>">
            	<table border="0"><tr bgcolor="#bbbbbb">
				<td>Naziv</td><td>Max. bodova</td><td>Prolaz</td><td>Dodaj</td><td>Uslov</td>
				</tr>
                <tr <? $bgcolor ?>>
                <td width="150">Prvi parcijalni ispit </td>
                <td width="30"><input style="text" name="maxBodovaPrvi" width="29" align="middle" value="<? echo $TabelaPismenihIspita[1][0]?>" /></td>
                <td width="30"><input style="text" name="prolazBodovaPrvi" width="29" align="middle" value="<? echo $TabelaPismenihIspita[2][0]?>" /></td>
                <td width="30"><input type="checkbox" name="Prvi" value="PrviDodaj" onclick="this.form.submit();" <? if($TabelaPismenihIspita[3][0]==1) echo "checked=\"yes\""; ?>/></td>
                <td width="30"><input type="checkbox" name="UslovPrvi" value="UslovPrviDodaj" onclick="this.form.submit();" <? if($TabelaPismenihIspita[4][0]==1) echo "checked=\"yes\""; ?>/></td>
                </tr>
                <? 
				
				$bgcolor="bgcolor=\"#efefef\"";  ?>
                
                <tr <? $bgcolor ?>>
                <td width="150">Drugi parcijalni ispit </td>
                <td width="30"><input style="text" name="maxBodovaDrugi" width="29" align="middle" value="<? echo $TabelaPismenihIspita[1][1]?>"/></td>
                <td width="30"><input style="text" name="prolazBodovaDrugi" width="29" align="middle" value="<? echo $TabelaPismenihIspita[2][1]?>"/></td>
                <td width="30"><input type="checkbox" name="Drugi" value="DrugiDodaj" onclick="this.form.submit();" <? if($TabelaPismenihIspita[3][1]==1) echo "checked=\"yes\""; ?>/></td>
                <td width="30"><input type="checkbox" name="UslovDrugi" value="UslovDrugiDodaj" onclick="this.form.submit();" <? if($TabelaPismenihIspita[4][1]==1) echo "checked=\"yes\""; ?>/></td>
                </tr>
                
                <? 
				
				$bgcolor="";  ?>
                
                <tr <? $bgcolor ?>>
                <td width="150">Integralni ispit </td>
                <td width="30"></td>
                <td width="30"></td>
                <td width="30"><input type="checkbox" name="Integralni" value="IntegralniDodaj" onclick="this.form.submit();" <? if($TabelaPismenihIspita[3][2]==1) echo "checked=\"yes\""; ?>/></td>
                <td width="30"><input type="checkbox" name="UslovIntegralni" value="UslovIntegralniDodaj" onclick="this.form.submit();" <? if($TabelaPismenihIspita[4][2]==1) echo "checked=\"yes\""; ?>/></td>
                </tr>
                <tr>
                                <? 
				for($i=0;$i<$brojIspita;$i++){
				if ($bgcolor=="") $bgcolor="bgcolor=\"#efefef\""; else $bgcolor=""; ?>
                <tr <? $bgcolor ?>>
                <td width="30"><input style="text" name="nazivKomponente<? echo $i; ?>" width="29" align="middle" value="<? echo $TabelaPismenihIspita[0][3+$i]?>"/></td>
                <td width="30"><input style="text" name="IspitmaxBodova<? echo $i; ?>" width="29" align="middle" value="<? echo $TabelaPismenihIspita[1][3+$i]?>"/></td>
                <td width="30"><input style="text" name="IspitprolazBodova<? echo $i; ?>" width="29" align="middle" value="<? echo $TabelaPismenihIspita[2][3+$i]?>"/></td>
                <td width="30"><input type="checkbox" name="IspitDodaj<? echo $i; ?>" value="IspitDodaj"  onclick="this.form.submit();" <? if($TabelaPismenihIspita[3][3+$i]==1) echo "checked=\"yes\""; ?> /></td>
                <td width="30"><input type="checkbox" name="UslovIspit<? echo $i; ?>" value="UslovIspit" onclick="this.form.submit();" <? if($TabelaPismenihIspita[4][3+$i]==1) echo "checked=\"yes\""; ?> /></td>
                </tr>
                <? } ?>
                 </tr>
                
                </table>
                </form>
          </td>
                        <td width="10 px">
            </td>
            <td><? pregled_predmeta_bez_naziva($predmet); ?></td>
          </tr>
                    <tr><td>
                            </br>
                 <a href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&obiljezeno=ispiti&komp=<? echo $brojK; 
				 ?>&funkcija=dodajIspit'><font size="1" color="#000066">Dodaj Ispit</font></a>
                  </br></br>
                        </td>
                        </tr>
           
          <tr>
         
          <td>
            <a href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&obiljezeno=naziv&komp=<? echo $brojK; ?>'><font size="2" color="#000066">Nazad</font></a></td><td></td><td align="right" width="450 px">
            <a href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&obiljezeno=zadace&komp=<? echo $brojK; ?>'><font size="2" color="#000066">Dalje</font></a>        
            </td>
            </tr>
            </table>
        <?
	}
	
	
	else if($_REQUEST['obiljezeno']=="zadace"){
		//definisanje zadace
		//uzima podatke sa forme i stavlja u varijablu $TabelaZadaca
		include("nastavnik/uzmi_zadace.php");
		
		?>
        <table>
        <tr>
        <td>
        	<form method="post" action="">
            	<table border="0"><tr bgcolor="#bbbbbb">
				<td>Naziv</td><td>Max. bodova</td><td>Dodaj</td><td>Uslov</td>
				</tr>
               
                <tr <? $bgcolor ?>>
                <td width="150">Zadace </td>
                <td width="30"><input style="text" name="maxBodovaZadace" width="29" align="middle" value="<? echo $TabelaZadaca[1][0]?>"/></td>
                <td width="30"><input type="checkbox" name="Zadace" value="ZadaceDodaj" onclick="this.form.submit();" <? if($TabelaZadaca[2][0]==1) echo "checked=\"yes\""; ?>/></td>
                <td width="30"><input type="checkbox" name="UslovZadace" value="UslovZadaceDodaj" onclick="this.form.submit();" <? if($TabelaZadaca[3][0]==1) echo "checked=\"yes\""; ?>/></td>
                </tr>
                                <tr>
                                <? 
				for($i=0;$i<$brojZadaca;$i++){
				if ($bgcolor=="") $bgcolor="bgcolor=\"#efefef\""; else $bgcolor=""; ?>
                <tr <? $bgcolor ?>>
                <td width="30"><input style="text" name="nazivKomponenteZadaca<? echo $i; ?>" width="29" align="middle" value="<? echo $TabelaZadaca[0][1+$i]?>"/></td>
                <td width="30"><input style="text" name="ZadacamaxBodova<? echo $i; ?>" width="29" align="middle" value="<? echo $TabelaZadaca[1][1+$i]?>"/></td>
                <td width="30"><input type="checkbox" name="ZadacaDodaj<? echo $i; ?>" value="ZadacaDodaj" onclick="this.form.submit();" <? if($TabelaZadaca[2][1+$i]==1) echo "checked=\"yes\""; ?> /></td>
                <td width="30"><input type="checkbox" name="ZadacaIspit<? echo $i; ?>" value="ZadacaUslov" onclick="this.form.submit();" <? if($TabelaZadaca[3][1+$i]==1) echo "checked=\"yes\""; ?> /></td>
                </tr>
                <? } ?>
                 </tr>
                
                </table>
          </form>
                  </br></br>
            <table>

            </table>
          </td>
                              <td width="20 px">
            </td>
            <td>
            	<? pregled_predmeta_bez_naziva($predmet); ?>
            </td>
          </tr>
           <tr><td>
                            </br>
                 <a href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&obiljezeno=zadace&komp=<? echo $brojK; 
				 ?>&funkcija=dodajZadacu'><font size="1" color="#000066">Dodaj zadacu</font></a>
                  </br></br>
                        </td>
                        </tr>
            <tr>
            <td>
            <a href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&obiljezeno=ispiti&komp=<? echo $brojK; ?>'><font size="2" color="#000066">Nazad</font></a></td><td></td><td align="right" width="450 px">
            <a href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&obiljezeno=prisustvo&komp=<? echo $brojK; ?>'><font size="2" color="#000066">Dalje</font></a>        
            </td>
            </tr>
</table>
        <?
	}
	
	else if($_REQUEST['obiljezeno']=="prisustvo"){
		//definisanje ispita
        //uzimamo podatke sa forme za prisustvo i smjestamo u varijablu $TabelaPrisustva
		include("nastavnik/uzmi_prisustvo.php");
		
		?>
        <table>
        <tr>
  <td>
        	<form method="post" action="">
            	<table border="0"><tr bgcolor="#bbbbbb">
				<td>Naziv</td><td>Max. bodova</td><td>Dozvoljen broj izostanaka</td><td>Dodaj</td><td>Uslov</td>
				</tr>
               
                <tr <? $bgcolor ?>>
                <td width="100">Prisustvo</td>
                <td width="30"><input style="text" name="maxBodovaPrisustvo" width="29" align="middle" value="<? echo $TabelaPrisustva[1][0]?>" /></td>
                <td width="100"><input style="text" name="BrojIzostanaka" width="29" align="middle" value="<? echo $TabelaPrisustva[2][0]?>" /></td>
                <td width="30"><input type="checkbox" name="Prisustvo" value="PrisustvoDodaj" onclick="this.form.submit();" <? if($TabelaPrisustva[3][0]==1) echo "checked=\"yes\""; ?> /></td>
                <td width="30"><input type="checkbox" name="UslovPrisustvo" value="UslovPrisustvoDodaj" onclick="this.form.submit();" <? if($TabelaPrisustva[4][0]==1) echo "checked=\"yes\""; ?>/></td>
                </tr>
                 <tr>
                  <? 
				for($i=0;$i<$brojPrisustva;$i++){
				if ($bgcolor=="") $bgcolor="bgcolor=\"#efefef\""; else $bgcolor=""; ?>
                <tr <? $bgcolor ?>>
                <td width="30"><input style="text" name="PrisustvoNazivKomponente<? echo $i; ?>" width="29" align="middle" value="<? echo $TabelaPrisustva[0][1+$i]?>" /></td>
                <td width="30"><input style="text" name="PrisustvoMaxBodova<? echo $i; ?>" width="29" align="middle" value="<? echo $TabelaPrisustva[1][1+$i]?>"/></td>
                <td width="30"><input style="text" name="PrisustvoBrojIzostanaka<? echo $i; ?>" width="29" align="middle" value="<? echo $TabelaPrisustva[2][1+$i]?>"/></td>
                <td width="30"><input type="checkbox" name="PrisustvoDodaj<? echo $i; ?>" value="PrisustvoDodaj" onclick="this.form.submit();" <? if($TabelaPrisustva[3][1+$i]==1) echo "checked=\"yes\""; ?> /></td>
                <td width="30"><input type="checkbox" name="PrisustvoIspit<? echo $i; ?>" value="PrisustvoUslov" onclick="this.form.submit();" <? if($TabelaPrisustva[4][1+$i]==1) echo "checked=\"yes\""; ?>/></td>
                </tr>
                <? } ?>
                 </tr>
                </table>
                </form>
                  </br></br>
            <table>

      </table>
          </td>
                    <td width="20 px">
            </td>
            <td>
            	<? pregled_predmeta_bez_naziva($predmet); ?>
            </td>
          </tr>
                     <tr><td>
                            </br>
                 <a href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&obiljezeno=prisustvo&komp=<? echo $brojK; 
				 ?>&funkcija=dodajPrisustvo'><font size="1" color="#000066">Dodaj prisustvo</font></a>
                  </br></br>
                        </td>
                        </tr>
                        <tr>
            <td>
            <a href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&obiljezeno=zadace&komp=<? echo $brojK; ?>'><font size="2" color="#000066">Nazad</font></a></td> <td></td><td align="right" width="450 px">
            <a href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&obiljezeno=usmeni&komp=<? echo $brojK; ?>'><font size="2" color="#000066">Dalje</font></a>        
            </td>
            </tr>
</table>
        <?
	}
	
	else if($_REQUEST['obiljezeno']=="usmeni"){
		//definisanje usmenog ispita
		//uzimamo podatke sa forme i smijestamo u varijablu $TabelaZavrsni
		include("nastavnik/uzmi_zavrsni.php");
		?>
        <table>
        <tr>
  <td>
        	<form method="post" action="">
            	<table border="0"><tr bgcolor="#bbbbbb">
				<td></td><td>Max. bodova</td><td>Prolaz</td><td>Dodaj</td>
				</tr>
               
                <tr <? $bgcolor ?>>
                <td width="150">Zavrsni ispit </td>
                <td width="30"><input style="text" name="maxBodovaUsmeni" width="29" align="middle" value="<? echo $TabelaZavrsni[1][0]?>" /></td>
                <td width="30"><input style="text" name="prolazBodovaUsmeni" width="29" align="middle" value="<? echo $TabelaZavrsni[2][0]?>" /></td>
                <td width="30"><input type="checkbox" name="Usmeni" value="UsmeniDodaj" onclick="this.form.submit();" <? if($TabelaZavrsni[3][0]==1) echo "checked=\"yes\""; ?> /></td>
                
                </tr>
                
              </table>
      </form>
                  </br></br>
            <table>
            </table>
          </td>
                                <td width="10 px">
            </td>
            <td>
            	<? pregled_predmeta_bez_naziva($predmet); ?>
            </td>
          </tr>
                        <tr>
            <td>
            <a href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&obiljezeno=prisustvo&komp=<? echo $brojK; ?>'><font size="2" color="#000066">Nazad</font></a></td><td></td><td align="right" width="450 px">
            <a href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&obiljezeno=dodaj&komp=<? echo $brojK; ?>'><font size="2" color="#000066">Dalje</font></a>        
            </td>
            </tr>
</table>
        <?
	}
	
	else if($_REQUEST['obiljezeno']=="pregled"){
		//pregled definisanog tipa predmeta
		/* Treba promijeniti */
			 ?>
      </br>
   <font size=2 >Pregled definisanog tipa predmeta:</font>
   </br></br>
   <div style="margin-left: 250px">
     <?
	 pregled_predmeta_sa_nazivom($predmet);
	 ?>
     		</div>
            </br></br>
            <table>
            <tr>
            <td>
           <a href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&obiljezeno=dodaj&komp=<? echo $brojK; ?>'><font size="2" color="#000066">Nazad</font></a>   
            <td align="right" width="700 px">
          <form action="../zamger/index.php?sta=nastavnik/tip&predmet=<? echo $predmet; ?>&ag=<? echo $ag;?>&komp=<? echo $brojK;?>" method="post" name="ZaSpasavanje">
           <input type="submit" name="submit" value="Spasi" />     </form>       
            </td>
            </tr>
            </table>
        <?
	
	}
}
else if($_REQUEST['postojeci']!=false && $_REQUEST['postojeci']>0){
	//ponudeni tipovi predmeta u obliku array(ID,naziv)
	//$ponudeni_tipovi=array(array(1,'ETF Bologna standard'));
	$pregled=$_REQUEST['pregled'];
	if(!$pregled)
	$pregled=1;
	$_SESSION['spasi']=$pregled;
	
	$q10 = myquery("select id,naziv from tippredmeta");
     ?>

  </form>
     <form name="zsPregled" method="post" action="<?php echo $PHP_SELF;?>">
     <font size=2 >Izaberite tip predmeta:</font>
     <select name="pregled" onchange="submit()">
     <?
	 //for($i=0;$i<count($ponudeni_tipovi);$i++){
		//  if($ponudeni_tipovi[$i][0]!=$pregled)
     		//	echo "<option value='".$ponudeni_tipovi[$i][0]."'>".$ponudeni_tipovi[$i][1]."</option>";
	 	  //else
		 	//	echo "<option selected value='".$ponudeni_tipovi[$i][0]."'>".$ponudeni_tipovi[$i][1]."</option>";
	 //}
	 while ($r10 = mysql_fetch_row($q10)) {
		 if($r10[0]!=$pregled)
     			echo "<option value='$r10[0]'>$r10[1]</option>";
	 	  else
		 		 echo "<option selected value='$r10[0]'>$r10[1]</option>";
	 }
	 ?>
     </select>
     </form>
      </br></br>
   <font size=2 >Pregled odabranog tipa predmeta:</font>
   </br></br>
   <div style="margin-left: 250px">
     <?
	 pregled_predmeta($pregled);
	
	?>
    </div>
    </br></br>
     <table>
            <tr>
            <td>
           <a href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>'><font size="2" color="#000066">Nazad</font></a>   
            <td align="right" width="700 px">
           <form action="../zamger/index.php?sta=nastavnik/tip&predmet=<? echo $predmet; ?>&ag=<? echo $ag;?>" method="post" name="ZaSpasavanje">
           <input type="submit" name="submit2" value="Spasi" />     </form>   
            </td>
</tr>
            </table>
            
			<?
}
else{
	//odabir jednog od unaprijed definisanih predmeta
	?>
	<table>
    <tr>
    <td ><a href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&postojeci=1'><font size=2 color="#000066">->Odaberite postojeći tip predmeta</font></a></td>
	</tr>
        <tr>
    <td font-size: 10pt><a href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&obiljezeno=naziv'><font size=2 color="#000066">->Definišite vlastiti tip predmeta</font></a></td>
 </tr>
 </br></br></br>
    </table>
   
<font size=2 >Trenutno definisane komponente na predmetu:</font>
   </br></br>
   <div style="margin-left: 320px">
   
<? 

$q10 = myquery("select tippredmeta from akademska_godina_predmet where predmet=$predmet AND akademska_godina=$ag");
while($r10 = mysql_fetch_row($q10)){
$pregled=$r10[0];	
}
pregled_predmeta($pregled);

//resetovanje svih varijabli koje se koriste prilikom definicije predmeta
	$_SESSION['brojIspita']=0;
	$_SESSION['brojPrisustva']=0;
	$_SESSION['brojZadaca']=0;
	unset($_SESSION['TabelaPismenihIspita']);
	unset($_SESSION['TabelaFiksnih']);
	unset($_SESSION['TabelaZadaca']);
	unset($_SESSION['TabelaPrisustva']);
	unset($_SESSION['TabelaZavrsni']);
	$_SESSION['spasi']=0;?>
            </div>
            <?
}
}
}
function meni_za_tip_predmeta(){
		
        
	

		$predmet=intval($_REQUEST['predmet']);
		$ag=intval($_REQUEST['ag']);
		$obiljezeno=$_REQUEST['obiljezeno'];
		$brojK=intval($_REQUEST['komp']);
		if(!isset($_REQUEST['komp'])) $brojK=1;
	

	?>
	&nbsp;</br>
	<style>
		a.malimeni {color:#333399;text-decoration:none;}
		a:hover.malimeni {color:#333399;text-decoration:underline;}
	</style>

	<table cellspacing="0" cellpadding="2" style="border:0px; border-style:solid; border-color:black; margin-left: 0px">
		<tr>
		<?

if($obiljezeno==false) $obiljezeno="naziv";
	$registry=array(array("Naziv tipa-->","naziv"),array("Pismeni ispiti-->","ispiti"),array("Zadace-->","zadace"),array("Prisustvo-->","prisustvo"),array("Zavrsni ispit-->","usmeni"),array("Fiksne komponente-->","dodaj"), array("Pregled","pregled"));
	foreach ($registry as $r) { 
			if ($r[1]==$obiljezeno ) $bgcolor="#eeeeee"; else $bgcolor="#cccccc";
			?><td height="20"  bgcolor="<?=$bgcolor?>" onmouseover="this.bgColor='#ffffff'" onmouseout="this.bgColor='<?=$bgcolor?>'">
				<a href="?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&obiljezeno=<?=$r[1]?>&komp=<?=$brojK?>" class="malimeni"><?=$r[0]?></a>
			</td>
			<?
		}


	?>
	</tr></table>
	<p>&nbsp;</p>
<?
		}

function pregled_predmeta($pregled){
	//funkcija koja prikazuje tip predmeta u tabelarnoj formi
	
	?>
    <table border="0"><tr bgcolor="#bbbbbb">
		<td>Naziv</td><td>Komponente</td><td>Max. bodova</td><td>Prolaz</td><td>Uslov</td>
	</tr>
    <?
	$q10 = myquery("select naziv from tippredmeta where id={$pregled}");
	$bgcolor="";
	$r10 = mysql_fetch_row($q10)
	?>
		<tr <?=$bgcolor?>><td><input type="text" name="naziv" value="<?=$r10[0]?>" readonly="readonly"></td>
			<?
			$q20 = myquery("select k.id, k.gui_naziv, k.maxbodova, k.prolaz from komponenta as k, tippredmeta_komponenta as tpk where k.id=tpk.komponenta and tpk.tippredmeta=$pregled");
			while ($r20 = mysql_fetch_row($q20)){
		     echo "<td>";
			print $r20[1];
			echo "</td><td>";
			print $r20[2];
			echo "</td><td>";
			if($r20[3]!=0)
			print $r20[3];
			if ($bgcolor=="") $bgcolor="bgcolor=\"#efefef\""; else $bgcolor="";
			echo "</td></tr><tr $bgcolor><td></td>";
			}
			?>
			</tr>
            </table>
            <?
	
}
function pregled_predmeta_bez_naziva($pregled){
	//funkcija koja prikazuje tip predmeta u tabelarnoj formi
	
	?>
    <table border="0"><tr bgcolor="#bbbbbb">
		<td>Komponente</td><td>Max. bodova</td><td>Prolaz</td><td>Uslov</td>
	</tr>
		<tr <?=$bgcolor?>>
			<?
			$TabelaPismenihIspita=$_SESSION['TabelaPismenihIspita'];
			$pomocna=count($TabelaPismenihIspita[0]);
			for($i=0;$i<$pomocna;$i++){
			if($TabelaPismenihIspita[3][$i]==1){
		     echo "<td>";
			print $TabelaPismenihIspita[0][$i];
			echo "</td><td>";
			print $TabelaPismenihIspita[1][$i];
			echo "</td><td>";
			if($TabelaPismenihIspita[2][$i]!=0)
			print $TabelaPismenihIspita[2][$i];
			echo "</td><td>";
			if($TabelaPismenihIspita[4][$i]==1)
			print "Da";
			else
			print "Ne";
			if ($bgcolor=="") $bgcolor="bgcolor=\"#efefef\""; else $bgcolor="";
			echo "</td></tr><tr $bgcolor>";
				}
			}
						$TabelaZadaca=$_SESSION['TabelaZadaca'];
			$pomocna=count($TabelaZadaca[0]);
			for($i=0;$i<$pomocna;$i++){
			if($TabelaZadaca[2][$i]==1){
		     echo "<td>";
			print $TabelaZadaca[0][$i];
			echo "</td><td>";
			print $TabelaZadaca[1][$i];
			echo "</td><td>";
			echo "</td><td>";
			if($TabelaZadaca[3][$i]==1)
			print "Da";
			else
			print "Ne";
			if ($bgcolor=="") $bgcolor="bgcolor=\"#efefef\""; else $bgcolor="";
			echo "</td></tr><tr $bgcolor>";
				}
			}
			$TabelaPrisustva=$_SESSION['TabelaPrisustva'];
			$pomocna=count($TabelaPrisustva[0]);
			for($i=0;$i<$pomocna;$i++){
			if($TabelaPrisustva[3][$i]==1){
		     echo "<td>";
			print $TabelaPrisustva[0][$i];
			echo "</td><td>";
			print $TabelaPrisustva[1][$i];
			echo "</td><td>";
			echo "</td><td>";
			if($TabelaPrisustva[4][$i]==1)
			print "Da";
			else
			print "Ne";
			if ($bgcolor=="") $bgcolor="bgcolor=\"#efefef\""; else $bgcolor="";
			echo "</td></tr><tr $bgcolor>";
				}
			}
			
			$TabelaZavrsni=$_SESSION['TabelaZavrsni'];
			if($TabelaZavrsni[3][0]==1){
		     echo "<td>";
			print $TabelaZavrsni[0][0];
			echo "</td><td>";
			print $TabelaZavrsni[1][0];
			echo "</td><td>";
			if($TabelaZavrsni[2][0]!=0)
			print $TabelaZavrsni[2][0];
			echo "</td><td>";
			if ($bgcolor=="") $bgcolor="bgcolor=\"#efefef\""; else $bgcolor="";
			echo "</td></tr><tr $bgcolor>";
			}
			
			$TabelaFiksnih=$_SESSION['TabelaFiksnih'];
			$pomocna=count($TabelaFiksnih[0]);
			for($i=0;$i<$pomocna;$i++){
			if($TabelaFiksnih[3][$i]==1){
		     echo "<td>";
			print $TabelaFiksnih[0][$i];
			echo "</td><td>";
			print $TabelaFiksnih[1][$i];
			echo "</td><td>";
			if($TabelaFiksnih[2][$i]!=0)
			print $TabelaFiksnih[2][$i];
			echo "</td><td>";
			if($TabelaFiksnih[4][$i]==1)
			print "Da";
			else
			print "Ne";
			if ($bgcolor=="") $bgcolor="bgcolor=\"#efefef\""; else $bgcolor="";
			echo "</td></tr><tr $bgcolor>";
				}
			}
			?>
			</tr>
            </table>
            <?
	
}
function pregled_predmeta_sa_nazivom($pregled){
	//funkcija koja prikazuje tip predmeta u tabelarnoj formi
	
	?>
    <table border="0"><tr bgcolor="#bbbbbb">
		<td>Naziv</td><td>Komponente</td><td>Max. bodova</td><td>Prolaz</td><td>Uslov</td>
	</tr>
		<tr <?=$bgcolor?>>
			<?
			$q10 = myquery("select naziv from predmet where id=".$pregled."");
				    while($r10 = mysql_fetch_row($q10)){
						$naziv=$r10[0];
					}
			?>
            <td><input type="text" name="naziv" value="<?=$naziv?>" readonly="readonly"></td>
            </tr>
            <tr>
            <?
					
			$TabelaPismenihIspita=$_SESSION['TabelaPismenihIspita'];
			$pomocna=count($TabelaPismenihIspita[0]);
			for($i=0;$i<$pomocna;$i++){
			if($TabelaPismenihIspita[3][$i]==1){
				echo "<td></td>";
		     echo "<td>";
			print $TabelaPismenihIspita[0][$i];
			echo "</td><td>";
			print $TabelaPismenihIspita[1][$i];
			echo "</td><td>";
			if($TabelaPismenihIspita[2][$i]!=0)
			print $TabelaPismenihIspita[2][$i];
			echo "</td><td>";
			if($TabelaPismenihIspita[4][$i]==1)
			print "Da";
			else
			print "Ne";
			if ($bgcolor=="") $bgcolor="bgcolor=\"#efefef\""; else $bgcolor="";
			echo "</td></tr><tr $bgcolor>";
				}
			}
						$TabelaZadaca=$_SESSION['TabelaZadaca'];
			$pomocna=count($TabelaZadaca[0]);
			for($i=0;$i<$pomocna;$i++){
				
			if($TabelaZadaca[2][$i]==1){
				echo "<td></td>";
		     echo "<td>";
			print $TabelaZadaca[0][$i];
			echo "</td><td>";
			print $TabelaZadaca[1][$i];
			echo "</td><td>";
			echo "</td><td>";
			if($TabelaZadaca[3][$i]==1)
			print "Da";
			else
			print "Ne";
			if ($bgcolor=="") $bgcolor="bgcolor=\"#efefef\""; else $bgcolor="";
			echo "</td></tr><tr $bgcolor>";
				}
			}
			$TabelaPrisustva=$_SESSION['TabelaPrisustva'];
			$pomocna=count($TabelaPrisustva[0]);
			for($i=0;$i<$pomocna;$i++){
				
			if($TabelaPrisustva[3][$i]==1){
				echo "<td></td>";
		     echo "<td>";
			print $TabelaPrisustva[0][$i];
			echo "</td><td>";
			print $TabelaPrisustva[1][$i];
			echo "</td><td>";
			echo "</td><td>";
			if($TabelaPrisustva[4][$i]==1)
			print "Da";
			else
			print "Ne";
			if ($bgcolor=="") $bgcolor="bgcolor=\"#efefef\""; else $bgcolor="";
			echo "</td></tr><tr $bgcolor>";
				}
			}
			
			$TabelaZavrsni=$_SESSION['TabelaZavrsni'];
			if($TabelaZavrsni[3][0]==1){
				echo "<td></td>";
		     echo "<td>";
			print $TabelaZavrsni[0][0];
			echo "</td><td>";
			print $TabelaZavrsni[1][0];
			echo "</td><td>";
			if($TabelaZavrsni[2][0]!=0)
			print $TabelaZavrsni[2][0];
			echo "</td><td>";
			if ($bgcolor=="") $bgcolor="bgcolor=\"#efefef\""; else $bgcolor="";
			echo "</td></tr><tr $bgcolor>";
			}
			
			$TabelaFiksnih=$_SESSION['TabelaFiksnih'];
			$pomocna=count($TabelaFiksnih[0]);
			for($i=0;$i<$pomocna;$i++){
			if($TabelaFiksnih[3][$i]==1){
				echo "<td></td>";
		     echo "<td>";
			print $TabelaFiksnih[0][$i];
			echo "</td><td>";
			print $TabelaFiksnih[1][$i];
			echo "</td><td>";
			if($TabelaFiksnih[2][$i]!=0)
			print $TabelaFiksnih[2][$i];
			echo "</td><td>";
			if($TabelaFiksnih[4][$i]==1)
			print "Da";
			else
			print "Ne";
			if ($bgcolor=="") $bgcolor="bgcolor=\"#efefef\""; else $bgcolor="";
			echo "</td></tr><tr $bgcolor>";
				}
			}
			?>
			</tr>
            </table>
            <?
	
}




	?>