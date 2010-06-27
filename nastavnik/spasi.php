<?

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
		
//$q14=myquery("UPDATE predmet
	//		 SET tippredmeta=$id_predmeta
		//	 WHERE id=$predmet;");

$q15=myquery("INSERT INTO akademska_godina_predmet(akademska_godina, predmet, tippredmeta)
			 VALUES($ag,$predmet,$id_predmeta)");

?>