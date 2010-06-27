<?

//Validacijja podataka-bodovi za prolaz ne smiju biti veci od maksimalnog broja bodova.

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
		$TabelaZavrsni=$_SESSION['TabelaZavrsni'];
	}
else{

//Uzimamo podatke sa forme ili iz sesije

if(isset($_POST['maxBodovaUsmeni'])){
	
		 $TabelaZavrsni=array(array(),array(),array(),array());

if(isset($_POST['maxBodovaUsmeni']) && isset($_POST['prolazBodovaUsmeni'])){
array_push($TabelaZavrsni[0],"Zavrsni ispit");
array_push($TabelaZavrsni[1],intval($_POST['maxBodovaUsmeni']));
array_push($TabelaZavrsni[2],intval($_POST['prolazBodovaUsmeni']));
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