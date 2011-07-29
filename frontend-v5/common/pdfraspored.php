<?
  
function common_pdfraspored() {
?>
<LINK href="css/raspored1.css" rel="stylesheet" type="text/css">
<?

function ob_file_callback($buffer)
{
  global $sadrzaj_bafera_za_pdf;
  $sadrzaj_bafera_za_pdf=$buffer;

}

global $string_pdf,$string,$sadrzaj_bafera_za_pdf,$userid;

ob_start('ob_file_callback');
$tip = my_escape($_GET['tip']);
if($tip=="student") {
		// Aktuelna akademska godina
		$q0 = myquery("select id,naziv from akademska_godina where aktuelna=1");
		$ag = mysql_result($q0,0,0);
		
		// Studij koji student trenutno sluša
		$q1 = myquery("select studij,semestar from student_studij where student=$userid and akademska_godina=$ag order by semestar desc limit 1");
		$semestar = mysql_result($q1,0,1);
		$semestar_neparan=$semestar%2;
		$studij = mysql_result($q1,0,0);
		$q0=myquery("select id from raspored where akademska_godina=$ag and studij=$studij and semestar=$semestar");
		$id_rasporeda=mysql_result($q0,0,0);
?>
		<table border="1" cellspacing="0" align="center">
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
						<p><? print "$i";?></p>
						<p><? print "00";?></p>
					</th>
					<th>
						<p></p>
						<p><? print "15";?></p>
					</th>
					<th>
						<p></p>
						<p><? print "30";?></p>
					</th>
					<th>
						<p></p>
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
									<table>
										<tr>
											<td><p>$tip</p></td>
										</tr>
										<tr>
											<td><p>$predmet_naziv</p></td>
										</tr>
										<tr>
											<td><p>$sala_naziv</p></td>
										</tr>";

							if($tip!='P'){
								print "
										<tr>";
											if($labgrupa_naziv=="(Svi studenti)") print "<td><p class=\"plavo\">--</p></td>";
											else print "<td><p>$labgrupa_naziv</p></td>";
										print "</tr>";
							}
							else{
								print "
										<tr>
											<td>--</td>
										</tr>";
							}
								print "
										<tr>
											<td><p>$vrijemeP-</p></td>
										</tr>
										<tr>
											<td><p>$vrijemeK</p></td>
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
		?>
		<table border="1" cellspacing="0">
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
						<p><? print "$i";?></p>
						<p><? print "00";?></p>
					</th>
					<th>
						<p></p>
						<p><? print "15";?></p>
					</th>
					<th>
						<p></p>
						<p><? print "30";?></p>
					</th>
					<th>
						<p></p>
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
									<table align=\"center\">
										<tr>
											<td><p>$tip</p></td>
										</tr>
										<tr>
											<td><p>$predmet_naziv</p></td>
										</tr>
										<tr>
											<td><p>$sala_naziv</p></td>
										</tr>";

							if($tip!='P'){
								print "
										<tr>";
											if($labgrupa_naziv=="(Svi studenti)") print "<td><p>--</p></td>";
											else print "<td><p>$labgrupa_naziv</p></td>";
										print "</tr>";
							}
							else{
								print "
										<tr>
											<td>--</td>
										</tr>";
							}
								print "
										<tr>
											<td><p>$vrijemeP-</p></td>
										</tr>
										<tr>
											<td><p>$vrijemeK</p></td>
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
ob_end_clean();

require_once('lib/tcpdf/tcpdf.php');

class MYPDF extends TCPDF {

	//Page header
	public function Header() {
	
	$this->SetMargins(10,35,25,true);	
	$this->Image("images/etf-100x100.png",10,8,20);
	$this->Image("images/unsa.png",180,8,20);
	
        $this->SetFont("DejaVu Sans",'',10);
	$this->SetY(15);
	$this->SetX(80);
	$this->Cell(50,5,'UNIVERZITET U SARAJEVU',0,0,'C');
	$this->Ln();
	$this->SetX(80);
	
	$this->Cell(50,5,'ELEKTROTEHNIČKI FAKULTET',0,0,'C');
	$this->Ln();
	$this->Cell(190,5,'','B',0,'C');
	$this->Ln();
	$this->Ln();
	}

	// Page footer
	public function Footer() {
		
	if ($this->PageNo() > 1) {
		//Position at 1.5 cm from bottom
		$this->SetY(-15);
		//Arial italic 8
		$this->SetFont('DejaVu Sans ','I',8);
		//Text color in gray
		$this->SetTextColor(128);
		//Page number
		$this->Cell(0,10,'Stranica '.$this->PageNo(),0,0,'C');
	}
	}
}

// Prva stranica
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->AddFont("DejaVuSans","","DejaVuSans.php");
$pdf->AddFont("DejaVuSans","","DejaVuSans-Bold.php");
$pdf->SetFont('DejaVuSans','',4);
$pdf->AddPage();


$sadrzaj_bafera_za_pdf = str_replace("\t","        ",$sadrzaj_bafera_za_pdf);

$pdf->writeHTMLCell($w=0, $h=0, $x='', $y='', $sadrzaj_bafera_za_pdf, $border=0, $ln=1, $fill=0, $reseth=true, $align='center', $autopadding=true);
ob_end_clean();
$pdf->Output('Raspored.pdf', 'I');
}

?>