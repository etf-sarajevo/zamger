<?

// COMMON/RASPORED1 - modul za ispis rasporeda
	
function common_raspored1($tip) {

	global $userid;
?>


<!-- RASPORED -->

<LINK href="css/raspored1.css" rel="stylesheet" type="text/css">


<div>
	<div style="padding-top: 3px; padding-bottom: 3px; background-color: #F5F5F5"><a href = "#" onclick="daj_stablo('raspored')" style="color: #666699"><img id = "img-raspored" src = "images/plus.png" border = "0" align = left hspace = 2 /><b>Pogledaj svoj raspored časova</b></a></div>
	<hr style = "background-color: #ccc; height: 0px; border: 0px; padding-bottom: 1px">
</div>

<div id = "raspored" style = "display: none; padding-bottom: 15px; line-height: 18px;">
<?
	if($tip=="student") {
		// Aktuelna akademska godina
		$q0 = myquery("select id,naziv from akademska_godina where aktuelna=1");
		$ag = mysql_result($q0,0,0);
		
		// Studij koji student trenutno sluša
		$q1 = myquery("select studij,semestar from student_studij where student=$userid and akademska_godina=$ag order by semestar desc limit 1");
		if (mysql_num_rows($q1)<1) {
			print "Nema rasporeda časova za korisnika<br/><br/></div>";
			return;
		} 
		$semestar = mysql_result($q1,0,1);
		$semestar_neparan=$semestar%2;
		$studij = mysql_result($q1,0,0);
		$q0=myquery("select id from raspored where akademska_godina=$ag and studij=$studij and semestar=$semestar");
		if(mysql_num_rows($q0)<1){
			print "Nema kreiranih rasporeda!</div>";
			return;
		}
		else{ // ako postoji raspored za korisnika
		?>
		<a href="?sta=common/pdfraspored&tip=<?=$tip?>" target="_new"><img src="images/16x16/pdf.png" width="16" height="16" border="0"></a>
		<?
		$id_rasporeda=mysql_result($q0,0,0);
?>
		<table class="raspored" border="1" cellspacing="0">
			<tr>
				<th>
					<p>Sat/</p>
					<p>Dan</p>
				</th>
				<?
				for($i=8;$i<=20;$i++){
					$j=$i+1;
				?>
					<th width="35">
						<p class="bold"><? print "$i";?></p>
						<p><? print "00";?></p>
					</th>
					<th width="35">
						<p><br></p>
						<p><? print "15";?></p>
					</th>
					<th width="35">
						<p><br></p>
						<p><? print "30";?></p>
					</th>
					<th width="35">
						<p><br></p>
						<p><? print "45";?></p>
					</th>
				<?
				}
				?>
			</tr>
			<?
			// petlja za 6 dana u sedmici
			for($i=1;$i<=6;$i++){
				print "<tr>";
				$q0=myquery("select rs.vrijeme_pocetak,rs.vrijeme_kraj from raspored_stavka rs,student_predmet sp,ponudakursa pk,predmet p,raspored r 
				where rs.dan_u_sedmici=$i and sp.predmet=pk.id and pk.predmet=p.id and rs.predmet=p.id and rs.raspored=r.id  and r.akademska_godina=$ag 
				and sp.student=$userid and pk.akademska_godina=$ag and pk.semestar mod 2=$semestar_neparan and rs.dupla=0 
				and (rs.isjeckana=0 or rs.isjeckana=2) and rs.labgrupa != -1");
				// sada je potrebno naći maksimalni broj preklapanja termina da bi znali koliki je rowspan potreban za dan $i
				// poredimo svaki interval casa sa svakim
				$broj_preklapanja=array();
				for($j=0;$j<53;$j++){
					$broj_preklapanja[]=0;
				}
				for($j=0;$j<mysql_num_rows($q0);$j++){
					$pocetak=mysql_result($q0,$j,0);
					$kraj=mysql_result($q0,$j,1);
					for($k=$pocetak;$k<$kraj;$k++) $broj_preklapanja[$k]++;
				}
				$max_broj_preklapanja=max($broj_preklapanja);
				if($i==1) $dan_tekst="PON";
				elseif ($i==2) $dan_tekst="UTO";
				elseif ($i==3) $dan_tekst="SRI";
				elseif ($i==4) $dan_tekst="ČET";
				elseif ($i==5) $dan_tekst="PET";
				elseif ($i==6) $dan_tekst="SUB";
				// sada pravimo dvodimenzionalni niz, koji predstavlja zauzetost termina u određenom redu
				$zauzet=array();
				for($j=0;$j<$max_broj_preklapanja;$j++){
					$zauzet=array();
				}
				for($j=0;$j<$max_broj_preklapanja;$j++){
					for($k=0;$k<=52;$k++){
						$zauzet[$j][]=0;
					}
				}
				// zauzet[1][0]=1 znaci da je termin 1 zauzet u drugom redu  
				
				$q1=myquery("select rs.id,rs.raspored,rs.predmet,rs.vrijeme_pocetak,rs.vrijeme_kraj,rs.sala,rs.tip,rs.labgrupa,sp.predmet 
					from raspored_stavka rs,student_predmet sp,ponudakursa pk,predmet p,raspored r where rs.dan_u_sedmici=$i and sp.predmet=pk.id and pk.predmet=p.id 
					and rs.raspored=r.id and rs.predmet=p.id and sp.student=$userid and r.akademska_godina=$ag and pk.akademska_godina=$ag and pk.semestar mod 2=$semestar_neparan 
					and rs.dupla=0 and (rs.isjeckana=0 or rs.isjeckana=2) and rs.labgrupa != -1 order by rs.id");
				$gdje=array();
				$gdje["id_stavke"]=array(); 
				$gdje["red_stavke"]=array(); // red u kojem stavka ide
				// primjer 
				// gdje["id_stavke"][0]=5 znaci da je id prve stavke 5
				// gdje["red_stavke"][0]=3 znaci da stavka 1 ide u 4. red
				// [0] pretstavlja prvu stavku jer indeksi kreću od nule i druga kolona treba biti ista-- u ovom slucaju [0]
				for($j=0;$j<mysql_num_rows($q1);$j++){
					$id_stavke=mysql_result($q1,$j,0);
					$gdje["id_stavke"][$j]=$id_stavke;// i ovo vise ne diramo jer znamo koji je id stavke na osnovu nepoznate $j
					$gdje["red_stavke"][$j]=0; // postavljamo na nulu jer još ne znamo gdje ide određena stavka
				}
				for($j=0;$j<mysql_num_rows($q1);$j++){
					$id_stavke=mysql_result($q1,$j,0);
					$pocetak=mysql_result($q1,$j,3);
					$kraj=mysql_result($q1,$j,4);
					for($k=0;$k<$max_broj_preklapanja;$k++){
						$zauzet_red=0;
						while($pocetak!=$kraj){
							if($zauzet[$k][$pocetak-1]==1){
								$zauzet_red=1;// ako je uslov ispunjen nađen je barem jedan zauzet red
								break;
							}
							$pocetak++;
						}
						if($zauzet_red==0){
							// ako nije zauzet termin u tom redu dodajemo termin u taj red i prekidamo petlju
							$gdje["red_stavke"][$j]=$k; // $stavka $j ide u red $k
							//sada proglasavamo termin zauzetim u tom redu $k+1
							$pocetak=mysql_result($q1,$j,3);
							while($pocetak!=$kraj){
								$zauzet[$k][$pocetak-1]=1;// termin $pocetak se zauzima u redu $k+1
								$pocetak++;
							}
						}
						if($zauzet_red==0) break;
					}
				}
				print "<td rowspan=\"$max_broj_preklapanja\">$dan_tekst</td>";
				for($j=0;$j<$max_broj_preklapanja;$j++){
					if($j>0) print "</tr><tr>";
					$zadnji=1;
					$zadnji_m=0;
					for($m=1;$m<=52;$m++){
						if($viska_cas==1) { $viska_cas=0; $m=$zadnji_m-1; continue; }
						for($k=0;$k<mysql_num_rows($q1);$k++){
							$id_stavke=mysql_result($q1,$k,0);
							$predmet=mysql_result($q1,$k,2);
							$q2=myquery("select kratki_naziv from predmet where id=$predmet");
							$predmet_naziv=mysql_result($q2,0,0);
							$pocetak=mysql_result($q1,$k,3);
							$kraj=mysql_result($q1,$k,4);
							$sala=mysql_result($q1,$k,5);
							$q3=myquery("select naziv from raspored_sala where id=$sala");
							$sala_naziv=mysql_result($q3,0,0);
							$tip=mysql_result($q1,$k,6);
							$labgrupa=mysql_result($q1,$k,7);
							$studentov_predmet=mysql_result($q1,$k,8);
							if($labgrupa!=0){
								$q4=myquery("select naziv from labgrupa where id=$labgrupa");
								$labgrupa_naziv=mysql_result($q4,0,0);
							}
							$interval=$kraj-$pocetak;
							if($gdje["red_stavke"][$k]==$j && $pocetak==$m){
							$vrijemePocS=floor(($pocetak-1)/4+8);
							$vrijemePocMin=$pocetak%4;
							if($vrijemePocMin==1) $vrijemePocM="00";
							elseif($vrijemePocMin==2) $vrijemePocM="15";
							elseif($vrijemePocMin==3) $vrijemePocM="30";
							elseif($vrijemePocMin==0) $vrijemePocM="45";
							$vrijemeP="$vrijemePocS:$vrijemePocM";
							$vrijemeKrajS=floor(($kraj-1)/4+8);
							$vrijemeKrajMin=$kraj%4;
							if($vrijemeKrajMin==1) $vrijemeKrajM="00";
							elseif($vrijemeKrajMin==2) $vrijemeKrajM="15";
							elseif($vrijemeKrajMin==3) $vrijemeKrajM="30";
							elseif($vrijemeKrajMin==0) $vrijemeKrajM="45";
							$vrijemeK="$vrijemeKrajS:$vrijemeKrajM";
							$q3=myquery("select obavezan from ponudakursa where id=$studentov_predmet");
							if(mysql_num_rows($q3)>0) $obavezan=mysql_result($q3,0,0);
							if($tip!='P' && $labgrupa_naziv!="(Svi studenti)"){
								$q5=myquery("select l.naziv from student_labgrupa sl,labgrupa l where sl.labgrupa=l.id and sl.student=$userid and l.predmet=$predmet");
								$brojac=0;
								for($s=0;$s<mysql_num_rows($q5);$s++){
									$naziv_studentove_labgrupe=mysql_result($q5,$s,0);
									if($naziv_studentove_labgrupe=="(Svi studenti)") continue;
									else { $brojac=1;break;}
								}
								if($brojac==1 && $naziv_studentove_labgrupe!=$labgrupa_naziv) { $zadnji_m=$kraj; $viska_cas=1; break;}
								//if($naziv_studentove_labgrupe=="(Svi studenti)") { $zadnji_m=$kraj; $viska_cas=1; break;}	
								// ako je iskljucena opcija iznad studentu koji nije ni u jednoj grupi se prikazuju termini ostalih grupa
							}
							for($n=$zadnji;$n<$pocetak;$n++) print "<td></td>";
							$zadnji=$kraj;		
							print "
								<td colspan=\"$interval\">
									<table class=\"cas\" align=\"center\">
										<tr>
											<td><p class=\"bold\">$tip</p></td>
										</tr>
										<tr>
											<td><p class=\"plavo\">$predmet_naziv</p></td>
										</tr>
										<tr>
											<td><p class=\"bold\">$sala_naziv</p></td>
										</tr>";

							if($tip!='P'){
								print "
										<tr>";
											if($labgrupa_naziv=="(Svi studenti)") print "<td><p class=\"plavo\">--</p></td>";
											else print "<td><p class=\"plavo\">$labgrupa_naziv</p></td>";
										print "</tr>";
							}
							else{
								print "
										<tr>
											<td class=\"plavo\">--</td>
										</tr>";
							}
								print "
										<tr>
											<td><p class=\"mala_slova\">$vrijemeP-$vrijemeK</p></td>
										</tr>
									</table>
								</td>";
							}
						}
					}							
				}
				print "</tr>";
			}
			?>
			
		</table>
<?
		}
	}
	// ako tip nije student
	else{
		// Da li je aktuelan neparni ili parni semestar?
		$q0 = myquery("select count(*) from student_studij as ss, akademska_godina as ag where ss.akademska_godina=ag.id and ag.aktuelna=1 and ss.semestar mod 2=0");
		if (mysql_result($q0,0,0)>0) $parni=1; else $parni=0;
		$brojac=0;
		$q1 = myquery("SELECT np.predmet, pk.akademska_godina, pk.semestar FROM nastavnik_predmet np, ponudakursa pk, akademska_godina ag where np.nastavnik = $userid AND pk.predmet = np.predmet AND pk.akademska_godina = ag.id and np.akademska_godina=ag.id and ag.aktuelna=1");
		while($r1 = mysql_fetch_array($q1)) {
			$ak_god = $r1['akademska_godina'];
			$semestar = $r1['semestar'];
			if ($semestar%2 == $parni) continue;
			if($brojac > 0)
				$sqlPredmet .= " or rs.predmet = ".$r1['predmet'];
			else
				$sqlPredmet = " rs.predmet = ".$r1['predmet'];
				
			$brojac++;
		}
		if (strlen($sqlPredmet)>0) $sqlWhere="(".$sqlPredmet.")"; 
		else
		{
			print "Korisnik nije angažovan ni na jednom predmetu u ovom semestru.<br/><br/></div>";
			return;
		}
		?>
		<a href="?sta=common/pdfraspored&tip=<?=$tip?>" target="_new"><img src="images/16x16/pdf.png" width="16" height="16" border="0"></a>
		<table class="raspored" border="1" cellspacing="0">
			<tr>
				<th>
					<p>Sat/</p>
					<p>Dan</p>
				</th>
				<?
				for($i=8;$i<=20;$i++){
					$j=$i+1;
				?>
					<th>
						<p class="bold"><? print "$i";?></p>
						<p><? print "00";?></p>
					</th>
					<th>
						<p><br></p>
						<p><? print "15";?></p>
					</th>
					<th>
						<p><br></p>
						<p><? print "30";?></p>
					</th>
					<th>
						<p><br></p>
						<p><? print "45";?></p>
					</th>
				<?
				}
				?>
			</tr>
			<?
			// petlja za 6 dana u sedmici
			for($i=1;$i<=6;$i++){
				print "<tr>";
				$q0=myquery("select rs.vrijeme_pocetak,rs.vrijeme_kraj from raspored_stavka rs,predmet p,raspored r where ". $sqlWhere. " and rs.predmet=p.id 
				and rs.raspored=r.id and r.akademska_godina=$ak_god and rs.dupla=0 and (rs.isjeckana=0 or rs.isjeckana=2) and rs.labgrupa != -1");
				// sada je potrebno naći maksimalni broj preklapanja termina da bi znali koliki je rowspan potreban za dan $i
				// poredimo svaki interval casa sa svakim
				$broj_preklapanja=array();
				for($j=0;$j<=52;$j++){
					$broj_preklapanja[]=0;
				}
				for($j=0;$j<mysql_num_rows($q0);$j++){
					$pocetak=mysql_result($q0,$j,0);
					$kraj=mysql_result($q0,$j,1);
					for($k=$pocetak;$k<$kraj;$k++) $broj_preklapanja[$k]++;
				}
				$max_broj_preklapanja=max($broj_preklapanja);
				if($i==1) $dan_tekst="PON";
				elseif ($i==2) $dan_tekst="UTO";
				elseif ($i==3) $dan_tekst="SRI";
				elseif ($i==4) $dan_tekst="ČET";
				elseif ($i==5) $dan_tekst="PET";
				elseif ($i==6) $dan_tekst="SUB";
				// sada pravimo dvodimenzionalni niz, koji predstavlja zauzetost termina u određenom redu
				$zauzet=array();
				for($j=0;$j<$max_broj_preklapanja;$j++){
					$zauzet=array();
				}
				for($j=0;$j<$max_broj_preklapanja;$j++){
					for($k=0;$k<=52;$k++){
						$zauzet[$j][]=0;
					}
				}
				// zauzet[1][0]=1 znaci da je termin 1 zauzet u drugom redu  
				
				$q1=myquery("select rs.id,rs.raspored,rs.predmet,rs.vrijeme_pocetak,rs.vrijeme_kraj,rs.sala,rs.tip,rs.labgrupa from raspored_stavka rs,
				predmet p,raspored r where " .$sqlWhere. " and rs.dan_u_sedmici=$i and rs.predmet=p.id and rs.raspored=r.id and r.akademska_godina=$ak_god 
				and rs.dupla=0 and (rs.isjeckana=0 or rs.isjeckana=2) and rs.labgrupa != -1 order by rs.id");
				$gdje=array();
				$gdje["id_stavke"]=array(); 
				$gdje["red_stavke"]=array(); // red u kojem stavka ide
				// primjer 
				// gdje["id_stavke"][0]=5 znaci da je id prve stavke 5
				// gdje["red_stavke"][0]=3 znaci da stavka 1 ide u 4. red
				// [0] pretstavlja prvu stavku jer indeksi kreću od nule i druga kolona treba biti ista-- u ovom slucaju [0]
				for($j=0;$j<mysql_num_rows($q1);$j++){
					$id_stavke=mysql_result($q1,$j,0);
					$gdje["id_stavke"][$j]=$id_stavke;// i ovo vise ne diramo jer znamo koji je id stavke na osnovu nepoznate $j
					$gdje["red_stavke"][$j]=0; // postavljamo na nulu jer još ne znamo gdje ide određena stavka
				}
				for($j=0;$j<mysql_num_rows($q1);$j++){
					$id_stavke=mysql_result($q1,$j,0);
					$pocetak=mysql_result($q1,$j,3);
					$kraj=mysql_result($q1,$j,4);
					for($k=0;$k<$max_broj_preklapanja;$k++){
						$zauzet_red=0;
						while($pocetak!=$kraj){
							if($zauzet[$k][$pocetak-1]==1){
								$zauzet_red=1;// ako je uslov ispunjen nađen je barem jedan zauzet red
								break;
							}
							$pocetak++;
						}
						if($zauzet_red==0){
							// ako nije zauzet termin u tom redu dodajemo termin u taj red i prekidamo petlju
							$gdje["red_stavke"][$j]=$k; // $stavka $j ide u red $k
							//sada proglasavamo termin zauzetim u tom redu $k+1
							$pocetak=mysql_result($q1,$j,3);
							while($pocetak!=$kraj){
								$zauzet[$k][$pocetak-1]=1;// termin $pocetak se zauzima u redu $k+1
								$pocetak++;
							}
						}
						if($zauzet_red==0) break;
					}
				}
				print "<td rowspan=\"$max_broj_preklapanja\">$dan_tekst</td>";
				for($j=0;$j<$max_broj_preklapanja;$j++){
					if($j>0) print "</tr><tr>";
					$zadnji=1;
					$zadnji_m=0;
					for($m=1;$m<=52;$m++){
						if($viska_cas==1) { $viska_cas=0; $m=$zadnji_m-1; continue; }
						for($k=0;$k<mysql_num_rows($q1);$k++){
							$id_stavke=mysql_result($q1,$k,0);
							$predmet=mysql_result($q1,$k,2);
							$q2=myquery("select kratki_naziv from predmet where id=$predmet");
							$predmet_naziv=mysql_result($q2,0,0);
							$pocetak=mysql_result($q1,$k,3);
							$kraj=mysql_result($q1,$k,4);
							$sala=mysql_result($q1,$k,5);
							$q3=myquery("select naziv from raspored_sala where id=$sala");
							$sala_naziv=mysql_result($q3,0,0);
							$tip=mysql_result($q1,$k,6);
							$labgrupa=mysql_result($q1,$k,7);
							if($labgrupa!=0){
								$q4=myquery("select naziv from labgrupa where id=$labgrupa");
								$labgrupa_naziv=mysql_result($q4,0,0);
							}
							$interval=$kraj-$pocetak;
							if($gdje["red_stavke"][$k]==$j && $pocetak==$m){
							$vrijemePocS=floor(($pocetak-1)/4+8);
							$vrijemePocMin=$pocetak%4;
							if($vrijemePocMin==1) $vrijemePocM="00";
							elseif($vrijemePocMin==2) $vrijemePocM="15";
							elseif($vrijemePocMin==3) $vrijemePocM="30";
							elseif($vrijemePocMin==0) $vrijemePocM="45";
							$vrijemeP="$vrijemePocS:$vrijemePocM";
							$vrijemeKrajS=floor(($kraj-1)/4+8);
							$vrijemeKrajMin=$kraj%4;
							if($vrijemeKrajMin==1) $vrijemeKrajM="00";
							elseif($vrijemeKrajMin==2) $vrijemeKrajM="15";
							elseif($vrijemeKrajMin==3) $vrijemeKrajM="30";
							elseif($vrijemeKrajMin==0) $vrijemeKrajM="45";
							$vrijemeK="$vrijemeKrajS:$vrijemeKrajM";
							$q3=myquery("select obavezan from ponudakursa where id=$predmet");
							if(mysql_num_rows($q3)>0) $obavezan=mysql_result($q3,0,0);
							/*
							if($tip!='P' && $labgrupa_naziv!="(Svi studenti)"){
								$q5=myquery("select l.naziv from student_labgrupa sl,labgrupa l where sl.labgrupa=l.id and sl.student=$userid and l.predmet=$predmet");
								$brojac=0;
								for($s=0;$s<mysql_num_rows($q5);$s++){
									$naziv_studentove_labgrupe=mysql_result($q5,$s,0);
									if($naziv_studentove_labgrupe=="(Svi studenti)") continue;
									else { $brojac=1;break;}
								}
								if($brojac==1 && $naziv_studentove_labgrupe!=$labgrupa_naziv) { $zadnji_m=$kraj; $viska_cas=1; break;}
								//if($naziv_studentove_labgrupe=="(Svi studenti)") { $zadnji_m=$kraj; $viska_cas=1; break;}	
								// ako je iskljucena opcija iznad studentu koji nije ni u jednoj grupi se prikazuju termini ostalih grupa
							}
							*/
							$q4=myquery("select o.labgrupa,l.naziv from ogranicenje o, labgrupa l where o.nastavnik=$userid and o.labgrupa=l.id and l.predmet=$predmet");
							if(mysql_num_rows($q4)>0){
								$postoji_labgrupa=0;
								for($s=0;$s<mysql_num_rows($q4);$s++){
									$ogr_labgrupa=mysql_result($q4,$s,0);
									$ogr_labgrupa_naziv=mysql_result($q4,$s,1);
									if($ogr_labgrupa==$labgrupa){ $postoji_labgrupa=1;break; }
									if($ogr_labgrupa_naziv=="(Svi studenti)" && $tip=='P') { $postoji_labgrupa=1;break; }
								}
								if($postoji_labgrupa==0) { $zadnji_m=$kraj; $viska_cas=1; break;}			
							}
							for($n=$zadnji;$n<$pocetak;$n++) print "<td></td>";
							$zadnji=$kraj;		
							print "
								<td colspan=\"$interval\">
									<table class=\"cas\" align=\"center\">
										<tr>
											<td><p class=\"bold\">$tip</p></td>
										</tr>
										<tr>
											<td><p class=\"plavo\">$predmet_naziv</p></td>
										</tr>
										<tr>
											<td><p class=\"bold\">$sala_naziv</p></td>
										</tr>";

							if($tip!='P'){
								print "
										<tr>";
											if($labgrupa_naziv=="(Svi studenti)") print "<td><p class=\"plavo\">--</p></td>";
											else print "<td><p class=\"plavo\">$labgrupa_naziv</p></td>";
										print "</tr>";
							}
							else{
								print "
										<tr>
											<td class=\"plavo\">--</td>
										</tr>";
							}
								print "
										<tr>
											<td><p class=\"mala_slova\">$vrijemeP-$vrijemeK</p></td>
										</tr>
									</table>
								</td>";
							}
						}
					}							
				}
				print "</tr>";
			}
			?>
			
		</table>
<?
	}
?>
</div>

<?
}?>