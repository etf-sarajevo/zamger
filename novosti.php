<?

//funkcija koja je dodata za pronalazak novosti na c2.etf.unsa.ba, ukoliko nijedna novost ne postoji u tabeli rss_cache u 'zamger' bazi
//ukoliko se baza za tu stranicu ne zove 'moodle', potrebno je taj naziv zamijeniti sa odgovarajucim
//potrebno je takodjer definisati konekcije koje ce se koristiti sto za Zamger, sto za c2.etf.unsa.ba
//ukoliko se razlikuju, naravno..
//nakon definisanja potrebno je promijeniti u mysql upitima zamijeniti $con sa varijablom kojom je definisana konekcija za odredjenu bazu..prepoznat cete po upitima koja je konekcija potrebna
//tamo gdje se poziva tabela sa predznakom 'moodle.', zamijenite $con sa odgovarajucom
//ukoliko se nalazi 'zamger.', opet je potrebno zamijeniti '$con' u upitu sa odgovarajucom konekcijom


function novosti($sifra,$vrijeme){

	$con = mysql_connect("localhost", "root","");
	mysql_select_db("moodle");
	mysql_select_db("zamger");
	
	$id_modula = array();
	$provjera = 0;
	
	//upit vraca id kursa na osnovu proslijedjenje sifre kursa koja mora biti ista kao i u tabeli 
	//zamger.predmet
	$q0 = mysql_query("Select id from moodle.mdl_course where idnumber='$sifra'",$con);
	$kurs = mysql_result($q0,0);
	
	$q1 = mysql_query("Select cm.module from moodle.mdl_course_modules as cm where
	cm.course=".$kurs." order by added desc limit 10",$con);
	
	while($r1 = mysql_fetch_array($q1)){
		array_push($id_modula,$r1['0']);
	}
	
	for($i=0;$i<count($id_modula);$i++){
		//u tabeli za momdul 9 se nalaze podaci o obavijestima koje su postavljene u labeli na 
		//stranici c2.etf.unsa.ba
		if($id_modula[$i]==9){
			$q2= mysql_query("Select name, content, timemodified from moodle.mdl_label where
			timemodified > ".$vrijeme." order by timemodified desc limit 5",$con);
			
			if(mysql_num_rows($q2)>=1){
				while($r2 = mysql_fetch_array($q2)){
				
					//ovaj dio sluzi za insertu u zamger bazu, ali samo pod uslovom da je moodle 
					//instaliran na localhost-u...zbog linka koji se prosljedjuje
					mysql_query("Insert into zamger.rss_cache(sifra_kursa, kurs, naslov,sadrzaj,
					link,vrijeme_promjene) VALUES('$sifra','".$kurs."','".$r2['0']."','".$r2['1'].
					"','http://localhost/moodle/course/view.php?id=$kurs','".$r2['2']."')");
					$provjera = 1;
					
					/*--ovaj dio sluzi ukoliko se zeli povezati sa c2.etf.unsa.ba
					
					mysql_query("Insert into zamger.rss_cache(sifra_kursa, kurs, naslov,sadrzaj,
					link,vrijeme_promjene) VALUES('$sifra','".$kurs."','".$r2['0']."','".$r2['1'].
					"','http://c2.etf.unsa.ba/course/view.php?id=$kurs','".$r2['2']."')");
					$provjera = 1;*/
				}
			}
		}
		
		//u tabeli za momdul 13 se nalaze podaci o predavanjima/linkovima koji su postavljeni na
		//stranicu c2.etf.unsa.ba
		if($id_modula[$i]==13){
			$q3= mysql_query("Select  name, summary, timemodified from moodle.mdl_label where 
			timemodified > ".$vrijeme." order by timemodified desc limit 5",$con);
			
			if(mysql_num_rows($q3)>=1){
				while($r3 = mysql_fetch_array($q3)){
					//ovaj dio sluzi za insertu u zamger bazu, ali samo pod uslovom da je moodle 
					//instaliran na localhost-u...zbog linka koji se prosljedjuje
					mysql_query("Insert into zamger.rss_cache(sifra_kursa, kurs, naslov,sadrzaj,
					link,vrijeme_promjene) VALUES('$sifra','".$kurs."','".$r2['0']."','".$r2['1'].
					"','http://localhost/moodle/course/view.php?id=$kurs','".$r2['2']."')");
					$provjera = 1;
					
					/*--ovaj dio sluzi ukoliko se zeli povezati sa c2.etf.unsa.ba
					
					mysql_query("Insert into zamger.rss_cache(sifra_kursa, kurs, naslov,sadrzaj,
					link,vrijeme_promjene) VALUES('$sifra','".$kurs."','".$r2['0']."','".$r2['1'].
					"','http://c2.etf.unsa.ba/course/view.php?id=$kurs','".$r2['2']."')");
					$provjera = 1;*/
				}
			}
		}
	}
	
	mysql_close($con);
	
	return $provjera;

}
?>