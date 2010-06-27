<?

$suma=0;
$TabelaPismenihIspita=$_SESSION['TabelaPismenihIspita'];
$pomocna=count($TabelaPismenihIspita[0]);
			for($i=0;$i<$pomocna;$i++){
				if($TabelaPismenihIspita[3][$i]==1 && $i!=2){
				$suma=$suma+$TabelaPismenihIspita[1][$i];
				}
			}
			
$TabelaZadaca=$_SESSION['TabelaZadaca'];
$pomocna=count($TabelaZadaca[0]);
			for($i=0;$i<$pomocna;$i++){
				
				if($TabelaZadaca[2][$i]==1){
					$suma=$suma+$TabelaZadaca[1][$i];
				}
			}
			
$TabelaPrisustva=$_SESSION['TabelaPrisustva'];
$pomocna=count($TabelaPrisustva[0]);
			for($i=0;$i<$pomocna;$i++){
				
				if($TabelaPrisustva[3][$i]==1){
				 	$suma=$suma+$TabelaPrisustva[1][$i];
				}
			}
			

$TabelaZavrsni=$_SESSION['TabelaZavrsni'];
			if($TabelaZavrsni[3][0]==1){
				$suma=$suma+$TabelaZavrsni[1][0];
			}
			
$TabelaFiksnih=$_SESSION['TabelaFiksnih'];
$pomocna=count($TabelaFiksnih[0]);
			for($i=0;$i<$pomocna;$i++){
				if($TabelaFiksnih[3][$i]==1){
				 	$suma=$suma+$TabelaFiksnih[1][$i];
				}
			}
	



?>