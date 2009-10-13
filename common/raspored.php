<?

// COMMON/RASPORED - modul za ispis rasporeda

// v4.0.0.0 (2009/02/19) + Release
// v4.0.9.1 (2009/03/25) + nastavnik_predmet preusmjeren sa tabele ponudakursa na tabelu predmet, dodano polje aktuelna u tabelu akademska_godina
// v4.0.9.2 (2009/04/19) + Kompletan rewrite modula kako bih shvatio sta radi i stavio u funkciju
// v4.0.9.3 (2009/09/13) + Dodana podrska za nastavu 08-21 i subotu; popravljen spacing (sugestije by Teo); daljnje unaprjedjenje citljivosti koda

	
		
function common_raspored($tip) {

	global $userid;


	// Nizovi sa imenima termina
	$vrijeme_pocetak = array("0" => "08:00", "1" => "09:00", "2" => "10:00", "3" => "11:00", "4" => "12:00", "5" => "13:00",
				"6" => "14:00", "7" => "15:00", "8" => "16:00", "9" => "17:00", "10" => "18:00", "11" => "19:00", "12" => "20:00");
	$vrijeme_kraj = array("0" => "08:45", "1" => "09:45", "2" => "10:45", "3" => "11:45", "4" => "12:45", "5" => "13:45",
				"6" => "14:45", "7" => "15:45", "8" => "16:45", "9" => "17:45", "10" => "18:45", "11" => "19:45", "12" => "20:45");



// Stilovi i javascript za raspored

// Skripta daj_stablo se sada nalazi u js/stablo.js, a ukljucena je u index.php


?>


<!-- RASPORED -->

<LINK href="css/raspored.css" rel="stylesheet" type="text/css">
<script language="JavaScript" type="text/javascript">
	//Tooltip
	function prikaziTT(poruka, eVentER) {
		var x = 0;
		var y = 0;
		
		if (document.all) {
			x = event.clientX;
			y = event.clientY;
		} else {
			x = eVentER.pageX;
			y = eVentER.pageY;
		}

		var element = document.getElementById('divTTRA');

		element.style.display = "block";
		element.style.left = x + 12 + "px";
		element.style.top = y + 10 + "px";
		element.innerHTML = poruka;
	}

	function sakrijTT() {
		document.getElementById('divTTRA').style.display = "none";
	}
</script>
<div id = "divTTRA" style = "position:absolute; display: none; border: dimgray thin solid; padding: 5px; margin: 2px 5px; background: #f8fbe1; z-index: 100;">
</div>

<div>
	<div style="padding-top: 3px; padding-bottom: 3px; background-color: #F5F5F5"><a href = "#" onclick="daj_stablo('raspored')" style="color: #666699"><img id = "img-raspored" src = "images/plus.png" border = "0" align = left hspace = 2 /><b>Pogledaj svoj raspored časova</b></a></div>
	<hr style = "background-color: #ccc; height: 0px; border: 0px; padding-bottom: 1px">
</div>

<div id = "raspored" style = "display: none; padding-bottom: 15px; line-height: 18px;">

<?

	// Upit koji odredjuje za koje predmete se prikazuje raspored
	if($tip=="student") {
		/*$selUserData = myquery("SELECT a.labgrupa, b.studij, b.semestar, b.akademska_godina FROM student_labgrupa a, student_studij b WHERE a.student = '".$korisnik."' AND b.student = '".$korisnik."' ");
		while($sUD = mysql_fetch_array($selUserData)) {
			$grupaId = $sUD['labgrupa'];
			$studijId = $sUD['studij'];
			$semId = $sUD['semestar'];
			$adId = $sUD['akademska_godina'];
		
			$sqlRasG .= " OR labgrupa = ".$grupaId;
			
		}
		
		$sqlWhere = "godinaR = '".$adId."' AND smijerR = '".$studijId."' AND semestarR = '".$semId."' AND labgrupa = '0' ".$sqlRasG;*/

		// Koji je aktuelni semestar?
		$q5 = myquery("select ss.semestar from student_studij as ss, akademska_godina as ag where ss.student=$userid and ss.akademska_godina=ag.id and ag.aktuelna=1 order by semestar desc limit 1");
		if (mysql_num_rows($q5)<1) {
			// Student nije upisan na fakultet.
			print "Nema rasporeda časova za korisnika<br/><br/>";
			return;
		}
		$semestar_paran = mysql_result($q5,0,0) % 2;

		
		$sqlUpit = "SELECT rs.id, p.naziv, p.kratki_naziv, rs.dan_u_sedmici, rs.tip, rs.vrijeme_pocetak, rs.vrijeme_kraj, rs.labgrupa, rsala.naziv
		FROM raspored_stavka as rs, raspored as r, predmet as p, ponudakursa as pk, student_predmet as sp, student_labgrupa as sl, raspored_sala as rsala, akademska_godina as ag
		WHERE sp.student=$userid AND sp.predmet=pk.id AND pk.predmet=p.id AND pk.akademska_godina=ag.id and pk.semestar mod 2=$semestar_paran and ag.aktuelna=1 AND p.id=rs.predmet AND rs.raspored=r.id AND r.aktivan=1 AND sl.student=$userid AND (rs.labgrupa=0 or rs.labgrupa=sl.labgrupa) AND rs.sala=rsala.id
		GROUP BY rs.labgrupa, rs.dan_u_sedmici, rs.vrijeme_pocetak, p.naziv
		ORDER BY rs.dan_u_sedmici ASC, rs.vrijeme_pocetak ASC, rs.id ASC";

/*		Šta je pisac htio da kaže...


		$sqlUpit = "SELECT rs.id, p.naziv, p.kratki_naziv, rs.dan_u_sedmici, rs.tip, rs.vrijeme_pocetak, rs.vrijeme_kraj, rs.labgrupa
		FROM raspored_stavka as rs
		JOIN predmet as p ON rs.predmet = p.id 
		JOIN ponudakursa as pk ON p.id = pk.predmet 
		JOIN student_predmet as sp ON pk.predmet = sp.predmet 
		JOIN student_labgrupa as sl ON sp.student = sl.student
		WHERE sp.student = '$userid' 
		AND rs.labgrupa IN (0, sl.labgrupa) 
		AND rs.predmet = sp.predmet
		GROUP BY rs.id, p.id, p.naziv, p.kratki_naziv, rs.dan_u_sedmici, rs.tip, rs.vrijeme_pocetak, rs.vrijeme_kraj, sl.student, rs.labgrupa
		ORDER BY rs.dan_u_sedmici ASC, rs.vrijeme_pocetak ASC, rs.id ASC";*/
					

		
	} else {
		$whereCounter = 0;
		$selUserData = myquery("SELECT np.predmet, pk.akademska_godina, pk.semestar FROM nastavnik_predmet as np, ponudakursa as pk, akademska_godina as ag WHERE np.nastavnik = $userid AND pk.predmet = np.predmet AND pk.akademska_godina = ag.id and np.akademska_godina=ag.id and ag.aktuelna=1");
		while($sUD = mysql_fetch_array($selUserData)) {
			$adId = $sUD['akademska_godina'];
			$semId = $sUD['semestar'];
			
			if($whereCounter > 0)
				$sqlPredmet .= " OR rs.predmet = ".$sUD['predmet'];
			else
				$sqlPredmet = " rs.predmet = ".$sUD['predmet'];
			
			$whereCounter++;
		}
			
		//$sqlWhere = "godinaR = '".$adId."' AND semestarR = '".$semId."' AND tip = 'P' AND (".$sqlPredmet.")"; // WTF!?!?
		if (strlen($sqlPredmet)>0) $sqlWhere = "(".$sqlPredmet.")";
		else $sqlWhere="1=0"; // Nije angazovan nigdje, prikaži prazan raspored
		
		$sqlUpit = "SELECT rs.id, p.naziv as naz, p.kratki_naziv, rs.dan_u_sedmici, rs.tip, rs.vrijeme_pocetak, rs.vrijeme_kraj, rsala.naziv FROM raspored_stavka as rs, raspored_sala as rsala, predmet as p WHERE ".$sqlWhere." AND rsala.id=rs.sala AND p.id=rs.predmet ORDER BY rs.dan_u_sedmici ASC, rs.vrijeme_pocetak ASC, rs.id ASC";
	}

	// Selektuj podatke iz baze
	$q10 = myquery($sqlUpit);
	if(mysql_num_rows($q10) == 0)
		print "Nema rasporeda časova za korisnika<br/><br/>";
	else {
		// Zaglavlje sa danima
		?><div class="dan_header" style="width:50px"></div>
			<div class="dan_header">Ponedjeljak</div>
			<div class="dan_header">Utorak</div>
			<div class="dan_header">Srijeda</div>
			<div class="dan_header">Cetvrtak</div>
			<div class="dan_header">Petak</div>
			<div class="dan_header">Subota</div>
			<div class="razmak"></div>
			<?

		// Satnica
		print "<div style=\"float:left\">\n";
		for ($i=8; $i<=20; $i++)
			print "<div class=\"satnica\">$i:00</div>\n";
		print "</div>\n";
		
		for($r=0; $r<13; $r++) {
			for($r2=0; $r2<6; $r2++) {
				print '<div style = "float: left; border-right: 1px solid #E0E4F3; width: 129px; height: 35px; padding: 4px 0px 0px 1px;"></div>'."\n";
			}
			print '<div style = "border-bottom: 1px solid #E0E4F3; margin-left: 54px; width: 650px; height: 30px; padding: 10px 0px 0px 2px;"></div>'."\n";
		}
		
		print "\n".'<div style = "position:absolute; margin: -370px 0px 0px 53px">'."\n";

		// Centralni dio

		print '<div class="kolona">'."\n"; // Pocetak kolone	
		$lastDay = 1; // Promjena dana
		$lastCas = 0; // Prazna polja
		while ($row = mysql_fetch_row($q10)) {
			// polja
			$rsid = $row[0];
			$predmet_naziv = $row[1];
			$predmet_kratki_naziv = $row[2];
			$dan_u_sedmici = $row[3];
			$tip_stavke = $row[4];
			$vpocetak = $row[5];
			$vkraj = $row[6];
			$naziv_sale = $row[8];


			$cssFontSize = "";
			$cssFontSize2 = "";

			// Provjera da li ima preklapanja
			if ($dan_u_sedmici == $lastDay && $vpocetak<$lastCas) {
				$transparentno = "background: rgba(245, 226, 188, 0.5); ";
				$pomak=10;
			} else {
				$transparentno = "background: #F5E2BC; ";
				$pomak=0;
			}

			// Boja naslovne trake kocke
			if($tip_stavke == "P") {
				$bojaTrake = "#E95026";
				if ($pomak==10) $bojaTrake="rgba(233,80,38,0.5);";
				$altT = "Predavanje";
			} else if($tip_stavke == "T") {
				$bojaTrake = "#FF8100";
				if ($pomak==10) $bojaTrake="rgba(255,129,0,0.5);";
				$altT = "Tutorijal";
			} else {
				$bojaTrake = "#E9DE26";
				if ($pomak==10) $bojaTrake="rgba(233,222,38,0.5);";
				$altT = "Laboratorijska vježba";
			}

			// Provjerava da li je presao na novi dan
			// U upitu smo definisali da su stavke poredane hronološki
			if ($dan_u_sedmici != $lastDay) {
				print '<div class="razmak"></div></div>'."\n"; // Kraj prethodne kolone
				$dayDif = $dan_u_sedmici-$lastDay-1; //Provjerava ako ima praznih dana između
				for ($i=0; $i<$dayDif; $i++) {
					print  '<div class="kolona">
						<div class="prazna_celija" style="height:28px"></div>
						<div class="razmak"></div>
					</div>'."\n";
				}
				print '<div class="kolona">'."\n"; // Prelazak u novu kolonu
				$lastDay = $dan_u_sedmici;
				$lastCas = 0;
			}

			// Neke default vrijednosti
			$css = 'celija'; // Ovo  je css za normalni siroki box i on je default
			$cssMarLeft = $pomak; // Default je na lijevoj strani

			$polaDone = false; // next box ide desno
			
			$stylePlus = 'onMouseMove = "prikaziTT(\'<b>'.$predmet_naziv.'</b> - '.$altT.'\', event)" onMouseOver = "prikaziTT(\'<b>'.$predmet_naziv.'</b> - '.$altT.'\', event)" onMouseOut = "sakrijTT()"';

			// Ispisuje box sa predmetom
			print "<div $stylePlus class=\"$css\" style=\"$transparentno $cssFontSize2 height:".(28+($vkraj-$vpocetak)*41)."px; margin-top:".(($vpocetak-4)*41+$pomak)."px; margin-left:$cssMarLeft"."px\">\n";
			print "<div class = \"naslov\" style = \"background: $bojaTrake; $cssFontSize2\">".$vrijeme_pocetak[$vpocetak]." - ".$vrijeme_kraj[$vkraj]."</div> <b>$predmet_kratki_naziv</b> - $naziv_sale</div>\n";

			$lastCas = $vkraj;
		}

		?>
				</div>
			<div class = "razmak"></div>
		</div><br/>
		<div style = "float: left; background: #E95026; padding: 2px; margin: 1px">Predavanje</div> <div style = "float: left; background: #FF8100; padding: 2px; margin: 1px">Tutorijal</div> <div style = "float: left; background: #E9DE26; padding: 2px; margin: 1px">Laboratorijska vježba</div><div class = "razmak"></div><?
	}
		
	?>
</div>

<!-- KRAJ RASPOREDA -->

<?

}

?>
