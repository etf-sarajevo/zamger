<?
//Validacijja podataka-bodovi za prolaz ne smiju biti veci od maksimalnog broja bodova.

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
		$TabelaFiksnih=$_SESSION['TabelaFiksnih'];
	}
else{
if(isset($_POST['maxBodova0'])){
		 $TabelaFiksnih=array(array(),array(),array(),array(),array());

for($i=0;$i<$brojK;$i++){
	if(isset($_POST['nazivKomponente'.$i]) && isset($_POST['maxBodova'.$i]) && isset($_POST['prolazBodova'.$i])){
                array_push($TabelaFiksnih[0],$_POST['nazivKomponente'.$i]);
				array_push($TabelaFiksnih[1],$_POST['maxBodova'.$i]);
				array_push($TabelaFiksnih[2],$_POST['prolazBodova'.$i]);
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
