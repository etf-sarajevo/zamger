<?

//Validacijja podataka-bodovi za prolaz ne smiju biti veci od maksimalnog broja bodova.

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
		$TabelaPrisustva=$_SESSION['TabelaPrisustva'];
	}
else{

//Popunjavamo $TabelaPrisustva, ili sa forme ili iz sesije

if(isset($_POST['maxBodovaPrisustvo'])){
	
		 $TabelaPrisustva=array(array(),array(),array(),array(),array());

if(isset($_POST['maxBodovaPrisustvo']) && isset($_POST['BrojIzostanaka'])){
array_push($TabelaPrisustva[0],"Prisustvo");
array_push($TabelaPrisustva[1],intval($_POST['maxBodovaPrisustvo']));
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
                array_push($TabelaPrisustva[0],$_POST['PrisustvoNazivKomponente'.$i]);
				array_push($TabelaPrisustva[1],$_POST['PrisustvoMaxBodova'.$i]);
				array_push($TabelaPrisustva[2],$_POST['PrisustvoBrojIzostanaka'.$i]);
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