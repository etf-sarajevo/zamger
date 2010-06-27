<?

//Validacijja podataka-bodovi za prolaz ne smiju biti veci od maksimalnog broja bodova.

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
		$TabelaZadaca=$_SESSION['TabelaZadaca'];
	}
else{
	
//sve podatke sa forme kupimo u varijablu $TabelaZadaca ili, ako nije submitano, uzimamo podatke iz sesije
if(isset($_POST['maxBodovaZadace'])){
	
	
	$TabelaZadaca=array(array(),array(),array(),array());
	
if(isset($_POST['maxBodovaZadace'])){
array_push($TabelaZadaca[0],"Zadace");
array_push($TabelaZadaca[1],intval($_POST['maxBodovaZadace']));
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
                array_push($TabelaZadaca[0],$_POST['nazivKomponenteZadaca'.$i]);
				array_push($TabelaZadaca[1],$_POST['ZadacamaxBodova'.$i]);
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