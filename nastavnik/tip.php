<?
//NASTAVNIK/TIP-modul koji ce omogućiti definisanja sistema bodovanja na predmetu

function nastavnik_tip() {

global $userid,$user_siteadmin;

?>

<SCRIPT LANGUAGE="JavaScript">
<!-- Beginning of JavaScript -


function MsgBox (textstring) {
alert (textstring) }


// - End of JavaScript - -->
</SCRIPT>

<?
//pokupimo parametre

$predmet = intval($_REQUEST['predmet']);
$ag = intval($_REQUEST['ag']);
$brojK=intval($_REQUEST['komp']);
if(!isset($_REQUEST['komp'])) $brojK=1;
$brojIspita=intval($_REQUEST['brojIspita']);
if(!isset($_REQUEST['brojIspita'])){ $brojIspita=0;}
$brojPrisustva=intval($_REQUEST['brojPrisustva']);
if(!isset($_REQUEST['brojPrisustva'])) {$brojPrisustva=0;}
$brojZadaca=intval($_REQUEST['brojZadaca']);
if(!isset($_REQUEST['brojZadaca'])) {$brojZadaca=0;}
   
// Naziv predmeta
$q10 = myquery("select naziv from predmet where id=$predmet");
if (mysql_num_rows($q10)<1) {
	biguglyerror("Nepoznat predmet");
	zamgerlog("ilegalan predmet $predmet",3); //nivo 3: greška
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
	//spašavanje izmjena za novodefinisani tip predmeta
	
	$q10 = myquery("select naziv from predmet where id=".$predmet."");

	while($r10 = mysql_fetch_row($q10)){
						$naziv=$r10[0];
						$q11 = myquery("select id from tippredmeta where  naziv ='".$naziv.$ag."'");
						$id_predmeta=$r11[0];
						if($r11 = mysql_fetch_row($q11)){
							$id_predmeta=$r11[0];
							//brišemo sve komponente definisane na ovom predmetu
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
	
	//spašavamo naše novodefinisane komponente

		$prvi=0;
		$drugi=0;
		$q14=myquery("INSERT INTO tippredmeta(naziv) VALUES('".$naziv.$ag."')");
		
		$q11 = myquery("select id from tippredmeta where naziv='".$naziv.$ag."'");
		if($r11=mysql_fetch_row($q11)){
		$id_predmeta=$r11[0];
		}
									
									
		$TabelaPismenihIspita=$_SESSION['TabelaPismenihIspita'];
		$pomocna=count($TabelaPismenihIspita[0]);
					for($i=0;$i<$pomocna;$i++){
					if($TabelaPismenihIspita[3][$i]==1){
						if($i==0){
							$q14=myquery("INSERT INTO
										 komponenta(naziv,gui_naziv,kratki_gui_naziv,tipkomponente,maxbodova,prolaz,uslov)
										 VALUES('".$TabelaPismenihIspita[0][0]."".$naziv."','".$TabelaPismenihIspita[0][0]."','".$TabelaPismenihIspita[0][0]."',1,".$TabelaPismenihIspita[1][0].",".$TabelaPismenihIspita[2][0].",".$TabelaPismenihIspita[4][0].");");
									$q14 = myquery("select id from komponenta where naziv='".$TabelaPismenihIspita[0][$i].$naziv."'");
									if($r14=mysql_fetch_row($q14)){
									$prvi=$r14[0];
									}
							$q14=myquery("INSERT INTO
										 tippredmeta_komponenta 
										 VALUES($id_predmeta,$prvi);");
						}
						elseif($i==1){
							$q14=myquery("INSERT INTO
										 komponenta(naziv,gui_naziv,kratki_gui_naziv,tipkomponente,maxbodova,prolaz,uslov)
										 VALUES('".$TabelaPismenihIspita[0][$i].$naziv."','".$TabelaPismenihIspita[0][$i]."','".$TabelaPismenihIspita[0][$i]."'
										 ,1,".$TabelaPismenihIspita[1][$i].",".$TabelaPismenihIspita[2][$i].",".$TabelaPismenihIspita[4][$i].");");
										 $q14 = myquery("select id from komponenta where naziv='".$TabelaPismenihIspita[0][$i].$naziv."'");
									if($r14=mysql_fetch_row($q14)){
									$drugi=$r14[0];
									}
										$q14=myquery("INSERT INTO
										 tippredmeta_komponenta 
										 VALUES($id_predmeta,$drugi);");
				
						}
						elseif($i==2){
							if($prvi!=0 && $drugi!=0){
							$q14=myquery("INSERT INTO
										 komponenta(naziv,gui_naziv,kratki_gui_naziv,tipkomponente,maxbodova,prolaz,opcija,uslov)
										 VALUES('".$TabelaPismenihIspita[0][$i].$naziv."','".$TabelaPismenihIspita[0][$i]."','".$TabelaPismenihIspita[0][$i]."'
										 ,2,".$TabelaPismenihIspita[1][$i].",".$TabelaPismenihIspita[2][$i].",'".$prvi."+".$drugi."',".$TabelaPismenihIspita[4][$i].");");
										 $q14 = myquery("select id from komponenta where naziv='".$TabelaPismenihIspita[0][$i].$naziv."'");
									if($r14=mysql_fetch_row($q14)){
									$prvi=$r14[0];
									}
							$q14=myquery("INSERT INTO
										 tippredmeta_komponenta 
										 VALUES($id_predmeta,$prvi);");
							}
				
						}
						else{
										$q14=myquery("INSERT INTO
										 komponenta(naziv,gui_naziv,kratki_gui_naziv,tipkomponente,maxbodova,prolaz,uslov)
										 VALUES('".$TabelaPismenihIspita[0][$i].$naziv."','".$TabelaPismenihIspita[0][$i]."','".$TabelaPismenihIspita[0][$i]."'
										 ,1,".$TabelaPismenihIspita[1][$i].",".$TabelaPismenihIspita[2][$i].",".$TabelaPismenihIspita[4][$i].");");
										 $q14 = myquery("select id from komponenta where naziv='".$TabelaPismenihIspita[0][$i].$naziv."'");
									if($r14=mysql_fetch_row($q14)){
									$prvi=$r14[0];
									}
							$q14=myquery("INSERT INTO
										 tippredmeta_komponenta 
										 VALUES($id_predmeta,$prvi);");
							
						}
					}
					}
					
								$TabelaZadaca=$_SESSION['TabelaZadaca'];
					$pomocna=count($TabelaZadaca[0]);
					for($i=0;$i<$pomocna;$i++){
						
					if($TabelaZadaca[2][$i]==1){
						
							$q14=myquery("INSERT INTO
										 komponenta(naziv,gui_naziv,kratki_gui_naziv,tipkomponente,maxbodova,uslov)
										 VALUES('".$TabelaZadaca[0][$i].$naziv."','".$TabelaZadaca[0][$i]."','".$TabelaZadaca[0][$i]."'
										 ,4,".$TabelaZadaca[1][$i].",".$TabelaZadaca[3][$i].");");
									$q14 = myquery("select id from komponenta where naziv='".$TabelaZadaca[0][$i].$naziv."'");
									if($r14=mysql_fetch_row($q14)){
									$prvi=$r14[0];
									}
							$q14=myquery("INSERT INTO
										 tippredmeta_komponenta 
										 VALUES($id_predmeta,$prvi);");						
						}
					}
					$TabelaPrisustva=$_SESSION['TabelaPrisustva'];
					$pomocna=count($TabelaPrisustva[0]);
					for($i=0;$i<$pomocna;$i++){
						
					if($TabelaPrisustva[3][$i]==1){
											$q14=myquery("INSERT INTO
										 komponenta(naziv,gui_naziv,kratki_gui_naziv,tipkomponente,maxbodova,opcija,uslov)
										 VALUES('".$TabelaPrisustva[0][$i].$naziv."','".$TabelaPrisustva[0][$i]."','".$TabelaPrisustva[0][$i]."'
										 ,3,".$TabelaPrisustva[1][$i].",'".$TabelaPrisustva[2][$i]."',".$TabelaPrisustva[4][$i].");");
									$q14 = myquery("select id from komponenta where naziv='".$TabelaPrisustva[0][$i].$naziv."'");
									if($r14=mysql_fetch_row($q14)){
									$prvi=$r14[0];
									}
							$q14=myquery("INSERT INTO
										 tippredmeta_komponenta 
										 VALUES($id_predmeta,$prvi);");
						}
					}
					
					$TabelaZavrsni=$_SESSION['TabelaZavrsni'];
					if($TabelaZavrsni[3][0]==1){
											$q14=myquery("INSERT INTO
										 komponenta(naziv,gui_naziv,kratki_gui_naziv,tipkomponente,maxbodova,prolaz,uslov)
										 VALUES('".$TabelaZavrsni[0][0].$naziv."','".$TabelaZavrsni[0][0]."','".$TabelaZavrsni[0][0]."'
										 ,1,".$TabelaZavrsni[1][0].",".$TabelaZavrsni[2][0].",0);");
									$q14 = myquery("select id from komponenta where naziv='".$TabelaZavrsni[0][0].$naziv."'");
									if($r14=mysql_fetch_row($q14)){
									$prvi=$r14[0];
									}
							$q14=myquery("INSERT INTO
										 tippredmeta_komponenta 
										 VALUES($id_predmeta,$prvi);");
					}
					
					$TabelaFiksnih=$_SESSION['TabelaFiksnih'];
					$pomocna=count($TabelaFiksnih[0]);
					for($i=0;$i<$pomocna;$i++){
					if($TabelaFiksnih[3][$i]==1){
													$q14=myquery("INSERT INTO
										 komponenta(naziv,gui_naziv,kratki_gui_naziv,tipkomponente,maxbodova,prolaz,uslov)
										 VALUES('".$TabelaFiksnih[0][$i].$naziv."','".$TabelaFiksnih[0][$i]."','".$TabelaFiksnih[0][$i]."'
										 ,1,".$TabelaFiksnih[1][$i].",".$TabelaFiksnih[2][$i].",".$TabelaFiksnih[2][$i].");");
									$q14 = myquery("select id from komponenta where naziv='".$TabelaFiksnih[0][$i].$naziv."'");
									if($r14=mysql_fetch_row($q14)){
									$prvi=$r14[0];
									}
							$q14=myquery("INSERT INTO
										 tippredmeta_komponenta 
										 VALUES($id_predmeta,$prvi);");
						}
					}
		
		$q15=myquery("INSERT INTO akademska_godina_predmet(akademska_godina, predmet, tippredmeta)
					 VALUES($ag,$predmet,$id_predmeta)");
		

	zamgerlog("Osoba u".$userid." kreirala tip predmeta $naziv"."$ag",4);
	
}
elseif(isset($_POST['submit2'])){
 	//spašavanje izmjena za novodefinisani tip predmeta
	$pregled=my_escape($_SESSION['spasi']);
	$predmet=my_escape($predmet);
	$ag=my_escape($ag);
	$q14=myquery("UPDATE akademska_godina_predmet
			 SET tippredmeta=$pregled
			 WHERE predmet=$predmet AND akademska_godina=$ag;");
	
	zamgerlog("Osoba u".$userid." promijenila tip predmeta p".$predmet." u $pregled",4);
	
	
}

?>
<link href="../css/zamger.css" rel="stylesheet" type="text/css" />


<p>&nbsp;</p>

<p><h3>Definišite tip predmeta - <?=$predmet_naziv?></h3></p>

<?


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
        	 <font size="2">Naziv Vašeg novokreiranog tipa predmeta će biti <? print $naziv.$ag; ?> .</font>
            </br></br></br>
            <table>
            <tr>
            <td>
            <a href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>'><font size="2" color="#000066">Nazad</font></a></td>
            <td align="right" width="450 px">
            <a href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&obiljezeno=ispiti&komp=<? print $brojK; ?>&brojIspita=<? print $brojIspita; ?>&brojZadaca=<? print $brojZadaca; ?>&brojPrisustva=<? print $brojPrisustva; ?>'><font size="2" color="#000066">Dalje</font></a>
            </td>
            </tr>
            </table>
        <?
	}
	else if($_REQUEST['obiljezeno']=="dodaj"){
		//definisanje fiksnih komponenti
			//uzimanje podataka sa forme i smiještanje u varijablu $TabelaFiksnih
				//Validacija podataka-bodovi za prolaz ne smiju biti veći od maksimalnog broja bodova.

				$kontrola1=true;
				$kontrola2=true;
				$kontrola3=true;

					for($i=0;$i<$brojK;$i++){
						if($_POST['maxBodova'.$i]<$_POST['prolazBodova'.$i]){
							$kontrola1=false;
							
						}
						if(  (($_POST['maxBodova'.$i]==false) && isset($_POST['Fiksne'.$i])) || (($_POST['nazivKomponente'.$i]==false) && isset($_POST['Fiksne'.$i])))
							{
								$kontrola2=false;
							}
							if(($_POST['maxBodova'.$i]==0) && isset($_POST['Fiksne'.$i])){  $kontrola3=false;}
					}
					
					
				if(($kontrola1==false) || ($kontrola2==false) || ($kontrola3==false)){
					if($kontrola1==false){
							?>
						<SCRIPT language=JavaScript>
								MsgBox("Maksimalan broj bodova za komponentu mora biti veći od broja bodova potrebnih za prolaz!");
						</SCRIPT>
						
						<?
					}
					elseif($kontrola2==false){
							?>
						<SCRIPT language=JavaScript>
								MsgBox("Potrebno je definisati sve parametre kako biste dodali komponentu!");
						</SCRIPT>
						
						<?		
					}
					else{
							?>
						<SCRIPT language=JavaScript>
								MsgBox("Provjerite ispravnost podataka koje želite registrovati!");
						</SCRIPT>
						
						<?	
					}
						$TabelaFiksnih=$_SESSION['TabelaFiksnih'];
					}
				else{
				if(isset($_POST['maxBodova0'])){
						 $TabelaFiksnih=array(array(),array(),array(),array(),array());
				
				for($i=0;$i<$brojK;$i++){
					if(isset($_POST['nazivKomponente'.$i]) && isset($_POST['maxBodova'.$i]) && isset($_POST['prolazBodova'.$i])){
			    				if (!check_csrf_token()) {
     								biguglyerror("Greska prilikom uzimanja podataka sa forme za fiksne komponente.");
        							zamgerlog("Uzimanje podataka sa forme za fiksne komponente",3);
                  				    return;
    							}
								array_push($TabelaFiksnih[0],my_escape($_POST['nazivKomponente'.$i]));
								array_push($TabelaFiksnih[1],floatval($_POST['maxBodova'.$i]));							
								array_push($TabelaFiksnih[2],floatval($_POST['prolazBodova'.$i]));
								if(isset($_POST['Fiksne'.$i]))
								array_push($TabelaFiksnih[3],1);
								else
								array_push($TabelaFiksnih[3],0);
								if(isset($_POST['UslovFiksne'.$i]))
								array_push($TabelaFiksnih[4],1);
								else
								array_push($TabelaFiksnih[4],0);
					}
				}
				
				$_SESSION['TabelaFiksnih']=$TabelaFiksnih;
								}
								
								
						 else{
							 $TabelaFiksnih=$_SESSION['TabelaFiksnih'];
						 }
				}
		 		
		?>
        	<div id="fiksne">
            Ovdje možete definisati vlastite komponente predmeta, koje se boduju, npr. seminarski rad i sl.
            </div>
            </br></br>
            
            
            
<table>
            <tr>
            <td>
            
        	<!-- <form method="post" action=""> -->   
            <? print genform_hani(); ?>
           	  <table border="0"><tr bgcolor="#bbbbbb">
				<td>Naziv komponente</td><td>Max. bodova</td><td>Prolaz</td><td>Dodaj</td><td>Uslov</td>
				</tr>
                <? 
				for($i=0;$i<$brojK;$i++){
				if ($bgcolor=="") $bgcolor="bgcolor=\"#efefef\""; else $bgcolor=""; ?>
                <tr <? $bgcolor ?>>
                <td width="30"><input style="text" name="nazivKomponente<? print $i; ?>" width="29" align="middle" value="<? print $TabelaFiksnih[0][$i]?>"/></td>
                <td width="30"><input style="text" name="maxBodova<? print $i; ?>" width="29" align="middle" value="<? print $TabelaFiksnih[1][$i]?>"/></td>
                <td width="30"><input style="text" name="prolazBodova<? print $i; ?>" width="29" align="middle" value="<? print $TabelaFiksnih[2][$i]?>"/></td>
                <td width="30"><input type="checkbox" name="Fiksne<? print $i; ?>" value="Dodaj" onclick="this.form.submit();" <? if($TabelaFiksnih[3][$i]==1) print "checked=\"yes\""; ?> /></td>
                <td width="30"><input type="checkbox" name="UslovFiksne<? print $i; ?>" value="Uslov" onclick="this.form.submit();" <? if($TabelaFiksnih[4][$i]==1) print "checked=\"yes\""; ?>/></td>
                </tr>
                <? } ?>
                </table>
                </form>
                </br>
                 <a href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&obiljezeno=dodaj&komp=<? print $brojK+1; ?>&brojIspita=<? print $brojIspita; ?>&brojZadaca=<? print $brojZadaca; ?>&brojPrisustva=<? print $brojPrisustva; ?>'><font size="1" color="#000066">Dodaj Komponentu</font></a>
                  </br></br>
            </td>
            <td width="20 px">
            </td>
            <td><? pregled_predmeta_bez_naziva($predmet); ?>
            </td>
            </tr>
                        <tr>
            <td>
            <a href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&obiljezeno=usmeni&komp=<? print $brojK; ?>&brojIspita=<? print $brojIspita; ?>&brojZadaca=<? print $brojZadaca; ?>&brojPrisustva=<? print $brojPrisustva; ?>'><font size="2" color="#000066">Nazad</font></a></td><td></td><td align="right" width="450 px">
            <a href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&obiljezeno=pregled&komp=<? print $brojK; ?>&brojIspita=<? print $brojIspita; ?>&brojZadaca=<? print $brojZadaca; ?>&brojPrisustva=<? print $brojPrisustva; ?>'><font size="2" color="#000066">Dalje</font></a>        
            </td>
            </tr>
            </table>
        <?
	}
	
	

	else if($_REQUEST['obiljezeno']=="ispiti"){
		//definisanje ispita
		    //uzimanje podataka sa forme i smiještanje u varijablu $TabelaPismenihIspita
			//Validacija podataka-bodovi za prolaz ne smiju biti veći od maksimalnog broja bodova.					
					$kontrola1=true;
					$kontrola2=true;
					$kontrola3=true;
					
						if(($_POST['maxBodovaPrvi']<$_POST['prolazBodovaPrvi']) || ($_POST['maxBodovaDrugi']<$_POST['prolazBodovaDrugi'])){
							
							$kontrola1=false;
						}
						if(  (($_POST['maxBodovaPrvi']==false) && isset($_POST['Prvi'])) || (($_POST['prolazBodovaDrugi']==false) && isset($_POST['Drugi'])) || (($_POST['maxBodovaDrugi']==false) && isset($_POST['Drugi'])) || (($_POST['prolazBodovaPrvi']==false) && isset($_POST['Prvi'])))
						{
							$kontrola2=false;
						}
						
						
						if( (($_POST['maxBodovaPrvi']==0) && isset($_POST['Prvi'])) || ($_POST['maxBodovaDrugi']==0 && isset($_POST['Drugi']))){ 
						$kontrola3=false;
						}
						
						for($i=0;$i<$brojIspita;$i++){
							if($_POST['IspitmaxBodova'.$i]<$_POST['IspitprolazBodova'.$i]){
								$kontrola1=false;
								
							}
							if(  (($_POST['IspitmaxBodova'.$i]==false) && isset($_POST['IspitDodaj'.$i])) || (($_POST['IspitprolazBodova'.$i]==false) && isset($_POST['IspitDodaj'.$i])) || (($_POST['nazivKomponente'.$i]==false) && isset($_POST['IspitDodaj'.$i])))
								{
									$kontrola2=false;
								}
							if(($_POST['IspitmaxBodova'.$i]==0) && isset($_POST['IspitDodaj'.$i])){  $kontrola3=false;}
						}
						
						
					if(($kontrola1==false) || ($kontrola2==false) || ($kontrola3==false)){
						if($kontrola1==false){
								?>
							<SCRIPT language=JavaScript>
									MsgBox("Maksimalan broj bodova za komponentu mora biti veći od broja bodova potrebnih za prolaz!");
							</SCRIPT>
							
							<?
						}
						elseif($kontrola2==false){
								?>
							<SCRIPT language=JavaScript>
									MsgBox("Potrebno je definisati sve parametre kako biste dodali komponentu!");
							</SCRIPT>
							
							<?		
						}
						else{
								?>
							<SCRIPT language=JavaScript>
									MsgBox("Provjerite ispravnost podataka koje želite registrovati!");
							</SCRIPT>
							
			<?	
						}
							$TabelaPismenihIspita=$_SESSION['TabelaPismenihIspita'];
						}
					else{
							
					
					if(isset($_POST['maxBodovaDrugi'])){
							 $TabelaPismenihIspita=array(array(),array(),array(),array(),array());
									 
					if(isset($_POST['maxBodovaPrvi']) && isset($_POST['prolazBodovaPrvi'])){
				            if (!check_csrf_token()) {
        						biguglyerror("Greska prilikom uzimanja podataka sa forme za pismene ispite.");
        						zamgerlog("Uzimanje podataka sa forme za pismene ispite",3);
        						return;
    						}
							array_push($TabelaPismenihIspita[0],"Prvi parcijalni ispit");
							array_push($TabelaPismenihIspita[1],floatval($_POST['maxBodovaPrvi']));
							array_push($TabelaPismenihIspita[2],floatval($_POST['prolazBodovaPrvi']));
							if(isset($_POST['Prvi']))
							array_push($TabelaPismenihIspita[3],1);
							else
							array_push($TabelaPismenihIspita[3],0);
							if(isset($_POST['UslovPrvi']))
							array_push($TabelaPismenihIspita[4],1);
							else
							array_push($TabelaPismenihIspita[4],0);
					}
					
					if(isset($_POST['maxBodovaDrugi']) && isset($_POST['prolazBodovaDrugi'])){
						
			        if (!check_csrf_token()) {
        				biguglyerror("Greska prilikom uzimanja podataka sa forme za pismene ispite.");
        				zamgerlog("Uzimanje podataka sa forme za pismene ispite",3);
        				return;
    				}
					array_push($TabelaPismenihIspita[0],"Drugi parcijalni ispit");
					array_push($TabelaPismenihIspita[1],floatval($_POST['maxBodovaDrugi']));
					array_push($TabelaPismenihIspita[2],floatval($_POST['prolazBodovaDrugi']));	
					if(isset($_POST['Drugi']))
					array_push($TabelaPismenihIspita[3],1);
					else
					array_push($TabelaPismenihIspita[3],0);
					if(isset($_POST['UslovDrugi']))
					array_push($TabelaPismenihIspita[4],1);
					else
					array_push($TabelaPismenihIspita[4],0);
					
					}
					
					if(isset($_POST['maxBodovaPrvi']) && isset($_POST['prolazBodovaPrvi']) && isset($_POST['maxBodovaDrugi']) && isset($_POST['prolazBodovaDrugi'])){
						
			        if (!check_csrf_token()) {
        				biguglyerror("Greska prilikom uzimanja podataka sa forme za pismene ispite.");
        				zamgerlog("Uzimanje podataka sa forme za pismene ispite",3);
        				return;
    				}
					array_push($TabelaPismenihIspita[0],"Integralni ispit");
					array_push($TabelaPismenihIspita[1],(floatval($_POST['maxBodovaPrvi'])+floatval($_POST['maxBodovaDrugi'])));	
					array_push($TabelaPismenihIspita[2],(floatval($_POST['prolazBodovaPrvi'])+floatval($_POST['prolazBodovaDrugi'])));					
					if(isset($_POST['Integralni']))
					array_push($TabelaPismenihIspita[3],1);
					else
					array_push($TabelaPismenihIspita[3],0);
					if(isset($_POST['UslovIntegralni']))
					array_push($TabelaPismenihIspita[4],1);
					else
					array_push($TabelaPismenihIspita[4],0);
					
					}
					
					for($i=0;$i<$brojIspita;$i++){
						if(isset($_POST['nazivKomponente'.$i]) && isset($_POST['IspitmaxBodova'.$i]) && isset($_POST['IspitprolazBodova'.$i])){
									array_push($TabelaPismenihIspita[0],my_escape($_POST['nazivKomponente'.$i]));
									array_push($TabelaPismenihIspita[1],floatval($_POST['IspitmaxBodova'.$i]));								
									array_push($TabelaPismenihIspita[2],floatval($_POST['IspitprolazBodova'.$i]));								
									if(isset($_POST['IspitDodaj'.$i]))
									array_push($TabelaPismenihIspita[3],1);
									else
									array_push($TabelaPismenihIspita[3],0);
									if(isset($_POST['UslovIspit'.$i]))
									array_push($TabelaPismenihIspita[4],1);
									else
									array_push($TabelaPismenihIspita[4],0);
						}
					}
					
					$_SESSION['TabelaPismenihIspita']=$TabelaPismenihIspita;
									}
							 else{
								 $TabelaPismenihIspita=$_SESSION['TabelaPismenihIspita'];
								 
							 }
							 
					}	
		?>
        <table>
        <tr>
  <td>
             <? print genform_hani(); ?>
            	<table border="0"><tr bgcolor="#bbbbbb">
				<td>Naziv</td><td>Max. bodova</td><td>Prolaz</td><td>Dodaj</td><td>Uslov</td>
				</tr>
                <tr <? $bgcolor ?>>
                <td width="150">Prvi parcijalni ispit </td>
                <td width="30"><input style="text" name="maxBodovaPrvi" width="29" align="middle" value="<? print $TabelaPismenihIspita[1][0]?>" /></td>
                <td width="30"><input style="text" name="prolazBodovaPrvi" width="29" align="middle" value="<? print $TabelaPismenihIspita[2][0]?>" /></td>
                <td width="30"><input type="checkbox" name="Prvi" value="PrviDodaj" onclick="this.form.submit();" <? if($TabelaPismenihIspita[3][0]==1) print "checked=\"yes\""; ?>/></td>
                <td width="30"><input type="checkbox" name="UslovPrvi" value="UslovPrviDodaj" onclick="this.form.submit();" <? if($TabelaPismenihIspita[4][0]==1) print "checked=\"yes\""; ?>/></td>
                </tr>
                <? 
				
				$bgcolor="bgcolor=\"#efefef\"";  ?>
                
                <tr <? $bgcolor ?>>
                <td width="150">Drugi parcijalni ispit </td>
                <td width="30"><input style="text" name="maxBodovaDrugi" width="29" align="middle" value="<? print $TabelaPismenihIspita[1][1]?>"/></td>
                <td width="30"><input style="text" name="prolazBodovaDrugi" width="29" align="middle" value="<? print $TabelaPismenihIspita[2][1]?>"/></td>
                <td width="30"><input type="checkbox" name="Drugi" value="DrugiDodaj" onclick="this.form.submit();" <? if($TabelaPismenihIspita[3][1]==1) print "checked=\"yes\""; ?>/></td>
                <td width="30"><input type="checkbox" name="UslovDrugi" value="UslovDrugiDodaj" onclick="this.form.submit();" <? if($TabelaPismenihIspita[4][1]==1) print "checked=\"yes\""; ?>/></td>
                </tr>
                
                <? 
				
				$bgcolor="";  ?>
                
                <tr <? $bgcolor ?>>
                <td width="150">Integralni ispit </td>
                <td width="30"></td>
                <td width="30"></td>
                <td width="30"><input type="checkbox" name="Integralni" value="IntegralniDodaj" onclick="this.form.submit();" <? if($TabelaPismenihIspita[3][2]==1) print "checked=\"yes\""; ?>/></td>
                <td width="30"><input type="checkbox" name="UslovIntegralni" value="UslovIntegralniDodaj" onclick="this.form.submit();" <? if($TabelaPismenihIspita[4][2]==1) print "checked=\"yes\""; ?>/></td>
                </tr>
                <tr>
                                <? 
				for($i=0;$i<$brojIspita;$i++){
				if ($bgcolor=="") $bgcolor="bgcolor=\"#efefef\""; else $bgcolor=""; ?>
                <tr <? $bgcolor ?>>
                <td width="30"><input style="text" name="nazivKomponente<? echo $i; ?>" width="29" align="middle" value="<? print $TabelaPismenihIspita[0][3+$i]?>"/></td>
                <td width="30"><input style="text" name="IspitmaxBodova<? echo $i; ?>" width="29" align="middle" value="<? print $TabelaPismenihIspita[1][3+$i]?>"/></td>
                <td width="30"><input style="text" name="IspitprolazBodova<? echo $i; ?>" width="29" align="middle" value="<? print $TabelaPismenihIspita[2][3+$i]?>"/></td>
                <td width="30"><input type="checkbox" name="IspitDodaj<? echo $i; ?>" value="IspitDodaj"  onclick="this.form.submit();" <? if($TabelaPismenihIspita[3][3+$i]==1) print "checked=\"yes\""; ?> /></td>
                <td width="30"><input type="checkbox" name="UslovIspit<? echo $i; ?>" value="UslovIspit" onclick="this.form.submit();" <? if($TabelaPismenihIspita[4][3+$i]==1) print "checked=\"yes\""; ?> /></td>
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
                 <a href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&obiljezeno=ispiti&komp=<? print $brojK; 
				 ?>&brojIspita=<? print $brojIspita+1; ?>&brojZadaca=<? print $brojZadaca; ?>&brojPrisustva=<? print $brojPrisustva; ?>'><font size="1" color="#000066">Dodaj Ispit</font></a>
                  </br></br>
                        </td>
                        </tr>
           
          <tr>
         
          <td>
            <a href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&obiljezeno=naziv&komp=<? print $brojK; ?>&brojIspita=<? print $brojIspita; ?>&brojZadaca=<? print $brojZadaca; ?>&brojPrisustva=<? print $brojPrisustva; ?>'><font size="2" color="#000066">Nazad</font></a></td><td></td><td align="right" width="450 px">
            <a href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&obiljezeno=zadace&komp=<? print $brojK; ?>&brojIspita=<? print $brojIspita; ?>&brojZadaca=<? print $brojZadaca; ?>&brojPrisustva=<? print $brojPrisustva; ?>'><font size="2" color="#000066">Dalje</font></a>        
            </td>
            </tr>
            </table>
        <?
	}
	
	
	else if($_REQUEST['obiljezeno']=="zadace"){
		//definisanje zadace
		//uzima podatke sa forme i stavlja u varijablu $TabelaZadaca
		//Validacija podataka-bodovi za prolaz ne smiju biti veći od maksimalnog broja bodova.		
				$kontrola1=true;
				$kontrola2=true;
				$kontrola3=true;
				
					if( (($_POST['maxBodovaZadace']==false) && isset($_POST['Zadace'])))
					{
						$kontrola2=false;
					}
					if( (($_POST['maxBodovaZadace']==0) && isset($_POST['Zadace']))){ 
					$kontrola3=false;
					}
					
					for($i=0;$i<$brojZadaca;$i++){
				
						if(  (($_POST['ZadacamaxBodova'.$i]==false) && isset($_POST['ZadacaDodaj'.$i])) || (($_POST['nazivKomponenteZadaca'.$i]==false) && isset($_POST['ZadacaDodaj'.$i])))
							{
								$kontrola2=false;
							}
						
						if(($_POST['ZadacamaxBodova'.$i]==0) && isset($_POST['ZadacaDodaj'.$i])){  $kontrola3=false;}
					}
					
					
				if(($kontrola1==false) || ($kontrola2==false) || ($kontrola3==false)){
					if($kontrola1==false){
							?>
						<SCRIPT language=JavaScript>
								MsgBox("Maksimalan broj bodova za komponentu mora biti veći od broja bodova potrebnih za prolaz!");
						</SCRIPT>
						
						<?
					}
					elseif($kontrola2==false){
							?>
						<SCRIPT language=JavaScript>
								MsgBox("Potrebno je definisati sve parametre kako biste dodali komponentu!");
						</SCRIPT>
						
						<?		
					}
					else{
							?>
						<SCRIPT language=JavaScript>
								MsgBox("Provjerite ispravnost podataka koje želite registrovati!");
						</SCRIPT>
						
		<?	
					}
						$TabelaZadaca=$_SESSION['TabelaZadaca'];
					}
				else{
					
				//sve podatke sa forme kupimo u varijablu $TabelaZadaca ili, ako nije submitano, uzimamo podatke iz sesije
				if(isset($_POST['maxBodovaZadace'])){
					
					
					$TabelaZadaca=array(array(),array(),array(),array());
					
				if(isset($_POST['maxBodovaZadace'])){
					if (!check_csrf_token()) {
        				biguglyerror("Greska prilikom uzimanja podataka sa forme za zadace.");
    			   		zamgerlog("Uzimanje podataka sa forme za zadace",3);
        				return;
    				}	
				array_push($TabelaZadaca[0],"Zadace");
				array_push($TabelaZadaca[1],floatval($_POST['maxBodovaZadace']));
				if(isset($_POST['Zadace']))
				array_push($TabelaZadaca[2],1);
				else
				array_push($TabelaZadaca[2],0);
				if(isset($_POST['UslovZadace']))
				array_push($TabelaZadaca[3],1);
				else
				array_push($TabelaZadaca[3],0);
				
				}
				
				for($i=0;$i<$brojZadaca;$i++){
					if(isset($_POST['nazivKomponenteZadaca'.$i]) && isset($_POST['ZadacamaxBodova'.$i])){
								if (!check_csrf_token()) {
        							biguglyerror("Greska prilikom uzimanja podataka sa forme za zadace.");
    			   					zamgerlog("Uzimanje podataka sa forme za zadace",3);
        							return;
    							}	
								array_push($TabelaZadaca[0],my_escape($_POST['nazivKomponenteZadaca'.$i]));
								array_push($TabelaZadaca[1],floatval($_POST['ZadacamaxBodova'.$i]));
								if(isset($_POST['ZadacaDodaj'.$i]))
								array_push($TabelaZadaca[2],1);
								else
								array_push($TabelaZadaca[2],0);
								if(isset($_POST['ZadacaIspit'.$i]))
								array_push($TabelaZadaca[3],1);
								else
								array_push($TabelaZadaca[3],0);
					}
				}
				
				$_SESSION['TabelaZadaca']=$TabelaZadaca;
								}
						 else{
							 $TabelaZadaca=$_SESSION['TabelaZadaca'];
						 }
				}
	
		
		?>
        <table>
        <tr>
        <td>
             <? print genform_hani(); ?>
            	<table border="0"><tr bgcolor="#bbbbbb">
				<td>Naziv</td><td>Max. bodova</td><td>Dodaj</td><td>Uslov</td>
				</tr>
               
                <tr <? $bgcolor ?>>
                <td width="150">Zadaće </td>
                <td width="30"><input style="text" name="maxBodovaZadace" width="29" align="middle" value="<? print $TabelaZadaca[1][0]?>"/></td>
                <td width="30"><input type="checkbox" name="Zadace" value="ZadaceDodaj" onclick="this.form.submit();" <? if($TabelaZadaca[2][0]==1) print "checked=\"yes\""; ?>/></td>
                <td width="30"><input type="checkbox" name="UslovZadace" value="UslovZadaceDodaj" onclick="this.form.submit();" <? if($TabelaZadaca[3][0]==1) print "checked=\"yes\""; ?>/></td>
                </tr>
                                <tr>
                                <? 
				for($i=0;$i<$brojZadaca;$i++){
				if ($bgcolor=="") $bgcolor="bgcolor=\"#efefef\""; else $bgcolor=""; ?>
                <tr <? $bgcolor ?>>
                <td width="30"><input style="text" name="nazivKomponenteZadaca<? echo $i; ?>" width="29" align="middle" value="<? print $TabelaZadaca[0][1+$i]?>"/></td>
                <td width="30"><input style="text" name="ZadacamaxBodova<? echo $i; ?>" width="29" align="middle" value="<? print $TabelaZadaca[1][1+$i]?>"/></td>
                <td width="30"><input type="checkbox" name="ZadacaDodaj<? echo $i; ?>" value="ZadacaDodaj" onclick="this.form.submit();" <? if($TabelaZadaca[2][1+$i]==1) print "checked=\"yes\""; ?> /></td>
                <td width="30"><input type="checkbox" name="ZadacaIspit<? echo $i; ?>" value="ZadacaUslov" onclick="this.form.submit();" <? if($TabelaZadaca[3][1+$i]==1) print "checked=\"yes\""; ?> /></td>
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
            <? if($user_siteadmin){ ?>
                 <a href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&obiljezeno=zadace&komp=<? print $brojK; 
				 ?>&brojIspita=<? print $brojIspita; ?>&brojZadaca=<? print $brojZadaca+1; ?>&brojPrisustva=<? print $brojPrisustva; ?>'><font size="1" color="#000066">Dodaj zadacu</font></a>
                  </br></br>
                  <? } ?>
                        </td>
                        </tr>
            <tr>
            <td>
            <a href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&obiljezeno=ispiti&komp=<? print $brojK; ?>&brojIspita=<? print $brojIspita; ?>&brojZadaca=<? print $brojZadaca; ?>&brojPrisustva=<? print $brojPrisustva; ?>'><font size="2" color="#000066">Nazad</font></a></td><td></td><td align="right" width="450 px">
            <a href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&obiljezeno=prisustvo&komp=<? print $brojK; ?>&brojIspita=<? print $brojIspita; ?>&brojZadaca=<? print $brojZadaca; ?>&brojPrisustva=<? print $brojPrisustva; ?>'><font size="2" color="#000066">Dalje</font></a>        
            </td>
            </tr>
</table>
        <?
	}
	
	else if($_REQUEST['obiljezeno']=="prisustvo"){
		//definisanje ispita
        //uzimamo podatke sa forme za prisustvo i smještamo u varijablu $TabelaPrisustva
		//Validacija podataka-bodovi za prolaz ne smiju biti veći od maksimalnog broja bodova.		
				$kontrola1=true;
				$kontrola2=true;
				$kontrola3=true;
				
					if(  (($_POST['maxBodovaPrisustvo']==false) && isset($_POST['Prisustvo'])) || (($_POST['BrojIzostanaka']==false) && isset($_POST['Prisustvo'])))
					{
						$kontrola2=false;
					}
						if( (($_POST['maxBodovaPrisustvo']==0) && isset($_POST['Prisustvo'])) || ($_POST['BrojIzostanaka']==0 && isset($_POST['Prisustvo']))){ 
					$kontrola3=false;
					}
					
					for($i=0;$i<$brojPrisustva;$i++){
				
						if(  (($_POST['PrisustvoMaxBodova'.$i]==false) && isset($_POST['PrisustvoDodaj'.$i])) || (($_POST['PrisustvoBrojIzostanaka'.$i]==false) && isset($_POST['PrisustvoDodaj'.$i])) || (($_POST['PrisustvoNazivKomponente'.$i]==false) && isset($_POST['PrisustvoDodaj'.$i])))
							{
								$kontrola2=false;
							}
							if((($_POST['PrisustvoMaxBodova'.$i]==0) && isset($_POST['PrisustvoDodaj'.$i])) || (($_POST['PrisustvoBrojIzostanaka'.$i]==0) && isset($_POST['PrisustvoDodaj'.$i]))){  $kontrola3=false;}
					}
					
					
				if(($kontrola1==false) || ($kontrola2==false) || ($kontrola3==false)){
					if($kontrola1==false){
							?>
						<SCRIPT language=JavaScript>
								MsgBox("Maksimalan broj bodova za komponentu mora biti veći od broja bodova potrebnih za prolaz!");
						</SCRIPT>
						
						<?
					}
					elseif($kontrola2==false){
							?>
						<SCRIPT language=JavaScript>
								MsgBox("Potrebno je definisati sve parametre kako biste dodali komponentu!");
						</SCRIPT>
						
						<?		
					}
					else{
							?>
						<SCRIPT language=JavaScript>
								MsgBox("Provjerite ispravnost podataka koje želite registrovati!");
						</SCRIPT>
						
		<?	
					}
						$TabelaPrisustva=$_SESSION['TabelaPrisustva'];
					}
				else{
				
				//Popunjavamo $TabelaPrisustva, ili sa forme ili iz sesije
				
				if(isset($_POST['maxBodovaPrisustvo'])){
					
						 $TabelaPrisustva=array(array(),array(),array(),array(),array());
				
				if(isset($_POST['maxBodovaPrisustvo']) && isset($_POST['BrojIzostanaka'])){
					
					if (!check_csrf_token()) {
        				biguglyerror("Greska prilikom uzimanja podataka sa forme za prisustvo.");
        				zamgerlog("Uzimanje podataka sa forme za prisustvo",3);
        				return;
    				}
				array_push($TabelaPrisustva[0],"Prisustvo");
				array_push($TabelaPrisustva[1],floatval($_POST['maxBodovaPrisustvo']));
				array_push($TabelaPrisustva[2],intval($_POST['BrojIzostanaka']));
				if(isset($_POST['Prisustvo']))
				array_push($TabelaPrisustva[3],1);
				else
				array_push($TabelaPrisustva[3],0);
				if(isset($_POST['UslovPrisustvo']))
				array_push($TabelaPrisustva[4],1);
				else
				array_push($TabelaPrisustva[4],0);
				
				}
				
				for($i=0;$i<$brojPrisustva;$i++){
				
					if(isset($_POST['PrisustvoNazivKomponente'.$i]) && isset($_POST['PrisustvoMaxBodova'.$i]) && isset($_POST['PrisustvoBrojIzostanaka'.$i])){
								if (!check_csrf_token()) {
        							biguglyerror("Greska prilikom uzimanja podataka sa forme za prisustvo.");
        							zamgerlog("Uzimanje podataka sa forme za prisustvo",3);
        							return;
    							}
								array_push($TabelaPrisustva[0],my_escape($_POST['PrisustvoNazivKomponente'.$i]));
								array_push($TabelaPrisustva[1],floatval($_POST['PrisustvoMaxBodova'.$i]));
								array_push($TabelaPrisustva[2],intval($_POST['PrisustvoBrojIzostanaka'.$i]));
								if(isset($_POST['PrisustvoDodaj'.$i]))
								array_push($TabelaPrisustva[3],1);
								else
								array_push($TabelaPrisustva[3],0);
								if(isset($_POST['PrisustvoIspit'.$i]))
								array_push($TabelaPrisustva[4],1);
								else
								array_push($TabelaPrisustva[4],0);
					}
				}
				
				$_SESSION['TabelaPrisustva']=$TabelaPrisustva;
								}
						 else{
							 $TabelaPrisustva=$_SESSION['TabelaPrisustva'];
						 }
				}

		
		?>
        <table>
        <tr>
  <td>
             <? print genform_hani(); ?>
            	<table border="0"><tr bgcolor="#bbbbbb">
				<td>Naziv</td><td>Max. bodova</td><td>Dozvoljen broj izostanaka</td><td>Dodaj</td><td>Uslov</td>
				</tr>
               
                <tr <? $bgcolor ?>>
                <td width="100">Prisustvo</td>
                <td width="30"><input style="text" name="maxBodovaPrisustvo" width="29" align="middle" value="<? print $TabelaPrisustva[1][0]?>" /></td>
                <td width="100"><input style="text" name="BrojIzostanaka" width="29" align="middle" value="<? print $TabelaPrisustva[2][0]?>" /></td>
                <td width="30"><input type="checkbox" name="Prisustvo" value="PrisustvoDodaj" onclick="this.form.submit();" <? if($TabelaPrisustva[3][0]==1) print "checked=\"yes\""; ?> /></td>
                <td width="30"><input type="checkbox" name="UslovPrisustvo" value="UslovPrisustvoDodaj" onclick="this.form.submit();" <? if($TabelaPrisustva[4][0]==1) print "checked=\"yes\""; ?>/></td>
                </tr>
                 <tr>
                  <? 
				for($i=0;$i<$brojPrisustva;$i++){
				if ($bgcolor=="") $bgcolor="bgcolor=\"#efefef\""; else $bgcolor=""; ?>
                <tr <? $bgcolor ?>>
                <td width="30"><input style="text" name="PrisustvoNazivKomponente<? echo $i; ?>" width="29" align="middle" value="<? print $TabelaPrisustva[0][1+$i]?>" /></td>
                <td width="30"><input style="text" name="PrisustvoMaxBodova<? echo $i; ?>" width="29" align="middle" value="<? print $TabelaPrisustva[1][1+$i]?>"/></td>
                <td width="30"><input style="text" name="PrisustvoBrojIzostanaka<? echo $i; ?>" width="29" align="middle" value="<? print $TabelaPrisustva[2][1+$i]?>"/></td>
                <td width="30"><input type="checkbox" name="PrisustvoDodaj<? echo $i; ?>" value="PrisustvoDodaj" onclick="this.form.submit();" <? if($TabelaPrisustva[3][1+$i]==1) print "checked=\"yes\""; ?> /></td>
                <td width="30"><input type="checkbox" name="PrisustvoIspit<? echo $i; ?>" value="PrisustvoUslov" onclick="this.form.submit();" <? if($TabelaPrisustva[4][1+$i]==1) print "checked=\"yes\""; ?>/></td>
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
                 <a href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&obiljezeno=prisustvo&komp=<? print $brojK; 
				 ?>&brojIspita=<? print $brojIspita; ?>&brojZadaca=<? print $brojZadaca; ?>&brojPrisustva=<? print $brojPrisustva+1; ?>'><font size="1" color="#000066" >Dodaj prisustvo</font></a>
                  </br></br>
                        </td>
                        </tr>
                        <tr>
            <td>
            <a href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&obiljezeno=zadace&komp=<? print $brojK; ?>&brojIspita=<? print $brojIspita; ?>&brojZadaca=<? print $brojZadaca; ?>&brojPrisustva=<? print $brojPrisustva; ?>'><font size="2" color="#000066">Nazad</font></a></td> <td></td><td align="right" width="450 px">
            <a href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&obiljezeno=usmeni&komp=<? print $brojK; ?>&brojIspita=<? print $brojIspita; ?>&brojZadaca=<? print $brojZadaca; ?>&brojPrisustva=<? print $brojPrisustva; ?>'><font size="2" color="#000066">Dalje</font></a>        
            </td>
            </tr>
</table>
        <?
	}
	
	else if($_REQUEST['obiljezeno']=="usmeni"){
		//definisanje usmenog ispita
		//uzimamo podatke sa forme i smiještamo u varijablu $TabelaZavrsni
		//Validacija podataka-bodovi za prolaz ne smiju biti veći od maksimalnog broja bodova.			
				$kontrola1=true;
				$kontrola2=true;
				$kontrola3=true;
				
					if(($_POST['maxBodovaUsmeni']<$_POST['prolazBodovaUsmeni'])){
						
						$kontrola1=false;
					}
					if(  (($_POST['maxBodovaUsmeni']==false) && isset($_POST['Usmeni'])) || (($_POST['prolazBodovaUsmeni']==false) && isset($_POST['Usmeni'])))
					{
						$kontrola2=false;
					}
						if( (($_POST['maxBodovaUsmeni']==0) && isset($_POST['Usmeni'])) || ($_POST['prolazBodovaUsmeni']==0 && isset($_POST['Usmeni']))){ 
					$kontrola3=false;
					}
					
					
					
				if(($kontrola1==false) || ($kontrola2==false) || ($kontrola3==false)){
					if($kontrola1==false){
							?>
						<SCRIPT language=JavaScript>
								MsgBox("Maksimalan broj bodova za komponentu mora biti veći od broja bodova potrebnih za prolaz!");
						</SCRIPT>
						
						<?
					}
					elseif($kontrola2==false){
							?>
						<SCRIPT language=JavaScript>
								MsgBox("Potrebno je definisati sve parametre kako biste dodali komponentu!");
						</SCRIPT>
						
						<?		
					}
					else{
							?>
						<SCRIPT language=JavaScript>
								MsgBox("Provjerite ispravnost podataka koje želite registrovati!");
						</SCRIPT>
						
		<?	
					}
						$TabelaZavrsni=$_SESSION['TabelaZavrsni'];
					}
				else{
				
				//Uzimamo podatke sa forme ili iz sesije
				
				if(isset($_POST['maxBodovaUsmeni'])){
					
						 $TabelaZavrsni=array(array(),array(),array(),array());
				
				if(isset($_POST['maxBodovaUsmeni']) && isset($_POST['prolazBodovaUsmeni'])){
					
					if (!check_csrf_token()) {
		     			biguglyerror("Greska prilikom uzimanja podataka sa forme za zavrsni ispit.");
       		 			zamgerlog("Uzimanje podataka sa forme za zavrsni ispit",3);
        				return;
   	 				}
				array_push($TabelaZavrsni[0],"Zavrsni ispit");
				array_push($TabelaZavrsni[1],floatval($_POST['maxBodovaUsmeni']));
				array_push($TabelaZavrsni[2],floatval($_POST['prolazBodovaUsmeni']));
				if(isset($_POST['Usmeni']))
				array_push($TabelaZavrsni[3],1);
				else
				array_push($TabelaZavrsni[3],0);		
				}
				$_SESSION['TabelaZavrsni']=$TabelaZavrsni;
				}
						 else{
							 $TabelaZavrsni=$_SESSION['TabelaZavrsni'];
						 }
				}


		?>
        <table>
        <tr>
  <td>
             <? print genform_hani(); ?>
            	<table border="0"><tr bgcolor="#bbbbbb">
				<td></td><td>Max. bodova</td><td>Prolaz</td><td>Dodaj</td>
				</tr>
               
                <tr <? $bgcolor ?>>
                <td width="150">Završni ispit </td>
                <td width="30"><input style="text" name="maxBodovaUsmeni" width="29" align="middle" value="<? print $TabelaZavrsni[1][0]?>" /></td>
                <td width="30"><input style="text" name="prolazBodovaUsmeni" width="29" align="middle" value="<? print $TabelaZavrsni[2][0]?>" /></td>
                <td width="30"><input type="checkbox" name="Usmeni" value="UsmeniDodaj" onclick="this.form.submit();" <? if($TabelaZavrsni[3][0]==1) print "checked=\"yes\""; ?> /></td>
                
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
            <a href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&obiljezeno=prisustvo&komp=<? print $brojK; ?>&brojIspita=<? print $brojIspita; ?>&brojZadaca=<? print $brojZadaca; ?>&brojPrisustva=<? print $brojPrisustva; ?>'><font size="2" color="#000066">Nazad</font></a></td><td></td><td align="right" width="450 px">
            <a href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&obiljezeno=dodaj&komp=<? print $brojK; ?>&brojIspita=<? print $brojIspita; ?>&brojZadaca=<? print $brojZadaca; ?>&brojPrisustva=<? print $brojPrisustva; ?>'><font size="2" color="#000066">Dalje</font></a>        
            </td>
            </tr>
</table>
        <?
	}
	
	else if($_REQUEST['obiljezeno']=="pregled"){
		//pregled definisanog tipa predmeta
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
           <a href='?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&obiljezeno=dodaj&komp=<? print $brojK; ?>&brojIspita=<? print $brojIspita; ?>&brojZadaca=<? print $brojZadaca; ?>&brojPrisustva=<? print $brojPrisustva; ?>'><font size="2" color="#000066">Nazad</font></a>   
            <td align="right" width="700 px">
          <form action="index.php?sta=nastavnik/tip&predmet=<? echo $predmet; ?>&ag=<? print $ag;?>&komp=<? print $brojK;?>&brojIspita=<? print $brojIspita; ?>$brojZadaca=<? print $brojZadaca; ?>$brojPrisustva=<? print $brojPrisustva; ?>" method="post" name="ZaSpasavanje">
           <input type="submit" name="submit" value="Spasi" />     </form>       
            </td>
            </tr>
            </table>
        <?
	
	}
}
else if($_REQUEST['postojeci']!=false && $_REQUEST['postojeci']>0){
	//ponuđeni tipovi predmeta u obliku array(ID,naziv)
	//$ponudeni_tipovi=array(array(1,'ETF Bologna standard'));
	$pregled=my_escape($_REQUEST['pregled']);
	if(!$pregled)
	$pregled=1;
	$_SESSION['spasi']=$pregled;
	
	$q10 = myquery("select id,naziv from tippredmeta");
     ?>

  </form>
      <? print genform_hani("POST", "zaPregled"); ?>
     <font size=2 >Izaberite tip predmeta:</font>
     <select name="pregled" onchange="submit()">
     <?
	 while ($r10 = mysql_fetch_row($q10)) {
		 if($r10[0]!=$pregled)
     			print "<option value='$r10[0]'>$r10[1]</option>";
	 	  else
		 		 print "<option selected value='$r10[0]'>$r10[1]</option>";
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
           <form action="index.php?sta=nastavnik/tip&predmet=<? print $predmet; ?>&ag=<? print $ag;?>" method="post" name="ZaSpasavanje">
           <input type="submit" name="submit2" value="Spasi" />     </form>   
            </td>
</tr>
            </table>
            
			<?
}
else{
	//odabir jednog od unaprijed definisanih predmeta
	?>
     <span id="opomena">
    	Promjenom tipa predmeta gube se svi pohranjeni podaci o ispitima, zadaćama i sl.
    </span>
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

function meni_za_tip_predmeta(){

		$predmet=intval($_REQUEST['predmet']);
		$ag=intval($_REQUEST['ag']);
		$obiljezeno=$_REQUEST['obiljezeno'];
		$brojK=intval($_REQUEST['komp']);
		if(!isset($_REQUEST['komp'])) $brojK=1;
		$brojIspita=intval($_REQUEST['brojIspita']);
		if(!isset($_REQUEST['brojIspita'])){ $brojIspita=0;}
		$brojPrisustva=intval($_REQUEST['brojPrisustva']);
		if(!isset($_REQUEST['brojPrisustva'])) {$brojPrisustva=0;}
		$brojZadaca=intval($_REQUEST['brojZadaca']);
		if(!isset($_REQUEST['brojZadaca'])) {$brojZadaca=0;}
	

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
	$registry=array(array("Naziv tipa-->","naziv"),array("Pismeni ispiti-->","ispiti"),array("Zadaće-->","zadace"),array("Prisustvo-->","prisustvo"),array("Završni ispit-->","usmeni"),array("Fiksne komponente-->","dodaj"), array("Pregled","pregled"));
	foreach ($registry as $r) { 
			if ($r[1]==$obiljezeno ) $bgcolor="#eeeeee"; else $bgcolor="#cccccc";
			?><td height="20"  bgcolor="<?=$bgcolor?>" onmouseover="this.bgColor='#ffffff'" onmouseout="this.bgColor='<?=$bgcolor?>'">
				<a href="?sta=nastavnik/tip&predmet=<?=$predmet?>&ag=<?=$ag?>&obiljezeno=<?=$r[1]?>&komp=<?=$brojK?>&brojIspita=<?=$brojIspita; ?>&brojZadaca=<?=$brojZadaca; ?>&brojPrisustva=<?=$brojPrisustva; ?>" class="malimeni"><?=$r[0]?></a>
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
			$q20 = myquery("select k.id, k.gui_naziv, k.maxbodova, k.prolaz, k.uslov from komponenta as k, tippredmeta_komponenta as tpk where k.id=tpk.komponenta and tpk.tippredmeta=$pregled");
			while ($r20 = mysql_fetch_row($q20)){
		     print "<td>";
			print $r20[1];
			print "</td><td>";
			print $r20[2];
			print "</td><td>";
			if($r20[3]!=0)
			print $r20[3];
			print "</td><td>";
			if($r20[4]==1)
			print "Da";
			else
			print "Ne";
			if ($bgcolor=="") $bgcolor="bgcolor=\"#efefef\""; else $bgcolor="";
			print "</td></tr><tr $bgcolor><td></td>";
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
			$suma=0;
			$suma_p=0;
			$TabelaPismenihIspita=$_SESSION['TabelaPismenihIspita'];
			$pomocna=count($TabelaPismenihIspita[0]);
			for($i=0;$i<$pomocna;$i++){
			if($TabelaPismenihIspita[3][$i]==1){
				if($i!=2){
				$suma=$suma+$TabelaPismenihIspita[1][$i];
				$suma_p=$suma_p+$TabelaPismenihIspita[2][$i];
				}
		     print "<td>";
			print my_escape($TabelaPismenihIspita[0][$i]);
			print "</td><td>";
			print my_escape($TabelaPismenihIspita[1][$i]);
			print "</td><td>";
			if($TabelaPismenihIspita[2][$i]!=0)
			print my_escape($TabelaPismenihIspita[2][$i]);
			print "</td><td>";
			if($TabelaPismenihIspita[4][$i]==1)
			print "Da";
			else
			print "Ne";
			if ($bgcolor=="") $bgcolor="bgcolor=\"#efefef\""; else $bgcolor="";
			print "</td></tr><tr $bgcolor>";
				}
			}
						$TabelaZadaca=$_SESSION['TabelaZadaca'];
			$pomocna=count($TabelaZadaca[0]);
			for($i=0;$i<$pomocna;$i++){
			if($TabelaZadaca[2][$i]==1){
				$suma=$suma+$TabelaZadaca[1][$i];
				
		     print "<td>";
			print my_escape($TabelaZadaca[0][$i]);
			print "</td><td>";
			print my_escape($TabelaZadaca[1][$i]);
			print "</td><td>";
			print "</td><td>";
			if($TabelaZadaca[3][$i]==1)
			print "Da";
			else
			print "Ne";
			if ($bgcolor=="") $bgcolor="bgcolor=\"#efefef\""; else $bgcolor="";
			print "</td></tr><tr $bgcolor>";
				}
			}
			$TabelaPrisustva=$_SESSION['TabelaPrisustva'];
			$pomocna=count($TabelaPrisustva[0]);
			for($i=0;$i<$pomocna;$i++){
			if($TabelaPrisustva[3][$i]==1){
				$suma=$suma+$TabelaPrisustva[1][$i];
		     print "<td>";
			print my_escape($TabelaPrisustva[0][$i]);
			print "</td><td>";
			print my_escape($TabelaPrisustva[1][$i]);
			print "</td><td>";
			print "</td><td>";
			if($TabelaPrisustva[4][$i]==1)
			print "Da";
			else
			print "Ne";
			if ($bgcolor=="") $bgcolor="bgcolor=\"#efefef\""; else $bgcolor="";
			print "</td></tr><tr $bgcolor>";
				}
			}
			
			$TabelaZavrsni=$_SESSION['TabelaZavrsni'];
			if($TabelaZavrsni[3][0]==1){
				$suma=$suma+$TabelaZavrsni[1][0];
				$suma_p=$suma_p+$TabelaZavrsni[2][0];
		     print "<td>";
			print my_escape($TabelaZavrsni[0][0]);
			print "</td><td>";
			print my_escape($TabelaZavrsni[1][0]);
			print "</td><td>";
			if($TabelaZavrsni[2][0]!=0)
			print my_escape($TabelaZavrsni[2][0]);
			print "</td><td>";
			if ($bgcolor=="") $bgcolor="bgcolor=\"#efefef\""; else $bgcolor="";
			print "</td></tr><tr $bgcolor>";
			}
			
			$TabelaFiksnih=$_SESSION['TabelaFiksnih'];
			$pomocna=count($TabelaFiksnih[0]);
			for($i=0;$i<$pomocna;$i++){
			if($TabelaFiksnih[3][$i]==1){
				$suma=$suma+$TabelaFiksnih[1][$i];
				$suma_p=$suma_p+$TabelaFiksnih[2][$i];
		     print "<td>";
			print my_escape($TabelaFiksnih[0][$i]);
			print "</td><td>";
			print my_escape($TabelaFiksnih[1][$i]);
			print "</td><td>";
			if($TabelaFiksnih[2][$i]!=0)
			print my_escape($TabelaFiksnih[2][$i]);
			print "</td><td>";
			if($TabelaFiksnih[4][$i]==1)
			print "Da";
			else
			print "Ne";
			if ($bgcolor=="") $bgcolor="bgcolor=\"#efefef\""; else $bgcolor="";
			print "</td></tr><tr $bgcolor>";
				}
			}
			?>
			</tr>
            <tr><td> Ukupno</td><td><? print $suma; ?></td><td><? print $suma_p; ?></td></tr>
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
				$suma=0;
				$suma_p=0;
			$TabelaPismenihIspita=$_SESSION['TabelaPismenihIspita'];
			$pomocna=count($TabelaPismenihIspita[0]);
			for($i=0;$i<$pomocna;$i++){
			if($TabelaPismenihIspita[3][$i]==1){
				if($i!=2){
				$suma=$suma+$TabelaPismenihIspita[1][$i];
				$suma_p=$suma_p+$TabelaPismenihIspita[2][$i];
				}
				print "<td></td>";
		     print "<td>";
			print my_escape($TabelaPismenihIspita[0][$i]);
			print "</td><td>";
			print my_escape($TabelaPismenihIspita[1][$i]);
			print "</td><td>";
			if($TabelaPismenihIspita[2][$i]!=0)
			print my_escape($TabelaPismenihIspita[2][$i]);
			print "</td><td>";
			if($TabelaPismenihIspita[4][$i]==1)
			print "Da";
			else
			print "Ne";
			if ($bgcolor=="") $bgcolor="bgcolor=\"#efefef\""; else $bgcolor="";
			print "</td></tr><tr $bgcolor>";
				}
			}
						$TabelaZadaca=$_SESSION['TabelaZadaca'];
			$pomocna=count($TabelaZadaca[0]);
			for($i=0;$i<$pomocna;$i++){
				
			if($TabelaZadaca[2][$i]==1){
				$suma=$suma+$TabelaZadaca[1][$i];
				print "<td></td>";
		     print "<td>";
			print my_escape($TabelaZadaca[0][$i]);
			print "</td><td>";
			print my_escape($TabelaZadaca[1][$i]);
			print "</td><td>";
			print "</td><td>";
			if($TabelaZadaca[3][$i]==1)
			print "Da";
			else
			print "Ne";
			if ($bgcolor=="") $bgcolor="bgcolor=\"#efefef\""; else $bgcolor="";
			print "</td></tr><tr $bgcolor>";
				}
			}
			$TabelaPrisustva=$_SESSION['TabelaPrisustva'];
			$pomocna=count($TabelaPrisustva[0]);
			for($i=0;$i<$pomocna;$i++){
				
			if($TabelaPrisustva[3][$i]==1){
				$suma=$suma+$TabelaPrisustva[1][$i];
				print "<td></td>";
		     print "<td>";
			print my_escape($TabelaPrisustva[0][$i]);
			print "</td><td>";
			print my_escape($TabelaPrisustva[1][$i]);
			print "</td><td>";
			print "</td><td>";
			if($TabelaPrisustva[4][$i]==1)
			print "Da";
			else
			print "Ne";
			if ($bgcolor=="") $bgcolor="bgcolor=\"#efefef\""; else $bgcolor="";
			print "</td></tr><tr $bgcolor>";
				}
			}
			
			$TabelaZavrsni=$_SESSION['TabelaZavrsni'];
			if($TabelaZavrsni[3][0]==1){
				$suma=$suma+$TabelaZavrsni[1][0];
				$suma_p=$suma_p+$Tabelazavrsni[2][0];
				print "<td></td>";
		     print "<td>";
			print my_escape($TabelaZavrsni[0][0]);
			print "</td><td>";
			print my_escape($TabelaZavrsni[1][0]);
			print "</td><td>";
			if($TabelaZavrsni[2][0]!=0)
			print my_escape($TabelaZavrsni[2][0]);
			print "</td><td>";
			if ($bgcolor=="") $bgcolor="bgcolor=\"#efefef\""; else $bgcolor="";
			print "</td></tr><tr $bgcolor>";
			}
			
			$TabelaFiksnih=$_SESSION['TabelaFiksnih'];
			$pomocna=count($TabelaFiksnih[0]);
			for($i=0;$i<$pomocna;$i++){
			if($TabelaFiksnih[3][$i]==1){
				$suma=$suma+$TabelaFiksnih[1][$i];
				$suma_p=$suma_p+$TabelaFiksnih[2][$i];
				print "<td></td>";
		     print "<td>";
			print my_escape($TabelaFiksnih[0][$i]);
			print "</td><td>";
			print my_escape($TabelaFiksnih[1][$i]);
			print "</td><td>";
			if($TabelaFiksnih[2][$i]!=0)
			print $TabelaFiksnih[2][$i];
			print "</td><td>";
			if($TabelaFiksnih[4][$i]==1)
			print "Da";
			else
			print "Ne";
			if ($bgcolor=="") $bgcolor="bgcolor=\"#efefef\""; else $bgcolor="";
			print "</td></tr><tr $bgcolor>";
				}
			}
			?>
			</tr>
                <tr><td> Ukupno</td><td><? print $suma; ?></td><td><? print $suma_p; ?></td></tr>
            </table>
            <?
	
}
function genform_hani($method="POST", $name="") {
	global $login;

	if ($method != "GET" && $method != "POST") $method="POST";
	$result = '<form name="'.$name.'" action="" method="'.$method.'">'."\n";

	//   CSRF protection
	//   The generated token is a SHA1 sum of session ID, time()/1000 and userid (in the
	// highly unlikely case that two users get the same SID in a short timespan). The
	// second function checks this token and the second token which uses time()/1000+1.
	// This leaves a 1000-2000 second (cca. 16-33 minutes) window during which an 
	// attacker could potentially discover a users SID and then craft an attack targeting
	// that specific user.

	$result .= '<input type="hidden" name="_lv_csrf_protection_token1" value="'.sha1(session_id().(intval(time()/1000)).$login).'"><input type="hidden" name="_lv_csrf_protection_token2" value="'.sha1(session_id().(intval(time()/1000)+1).$login).'">';

	return $result;
}
	?>