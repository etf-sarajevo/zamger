<?

//Validacijja podataka-bodovi za prolaz ne smiju biti veci od maksimalnog broja bodova.

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
        		MsgBox("Maksimalan broj bodova za komponentu mora biti veci od broja bodova potrebnih za prolaz!");
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
        		MsgBox("Provjerite ispravnost podataka koje zelite registrovati!");
		</SCRIPT>
        
        <?	
	}
		$TabelaPismenihIspita=$_SESSION['TabelaPismenihIspita'];
	}
else{
		

if(isset($_POST['maxBodovaDrugi'])){
		 $TabelaPismenihIspita=array(array(),array(),array(),array(),array());
		 		 
if(isset($_POST['maxBodovaPrvi']) && isset($_POST['prolazBodovaPrvi'])){

	
		array_push($TabelaPismenihIspita[0],"Prvi parcijalni ispit");
		array_push($TabelaPismenihIspita[1],intval($_POST['maxBodovaPrvi']));
		array_push($TabelaPismenihIspita[2],intval($_POST['prolazBodovaPrvi']));
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
array_push($TabelaPismenihIspita[0],"Drugi parcijalni ispit");
array_push($TabelaPismenihIspita[1],intval($_POST['maxBodovaDrugi']));
array_push($TabelaPismenihIspita[2],intval($_POST['prolazBodovaDrugi']));
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
array_push($TabelaPismenihIspita[0],"Integralni ispit");
array_push($TabelaPismenihIspita[1],(intval($_POST['maxBodovaPrvi'])+intval($_POST['maxBodovaDrugi'])));
array_push($TabelaPismenihIspita[2],(intval($_POST['prolazBodovaPrvi'])+intval($_POST['prolazBodovaDrugi'])));
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
                array_push($TabelaPismenihIspita[0],$_POST['nazivKomponente'.$i]);
				array_push($TabelaPismenihIspita[1],$_POST['IspitmaxBodova'.$i]);
				array_push($TabelaPismenihIspita[2],$_POST['IspitprolazBodova'.$i]);
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