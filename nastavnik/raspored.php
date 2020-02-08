<?

// NASTAVNIK/RASPORED - editovanje grupa za predmet



function nastavnik_raspored() {

?>
<link href="static/css/raspored1.css" rel="stylesheet" type="text/css">
<?

function vrijemeZaIspis($vrijeme){
	$vrijemeS=floor(($vrijeme-1)/4+8);
	$vrijemeMin=$vrijeme%4;
	if($vrijemeMin==1) $vrijemeM="00";
	elseif($vrijemeMin==2) $vrijemeM="15";
	elseif($vrijemeMin==3) $vrijemeM="30";
	elseif($vrijemeMin==0) $vrijemeM="45";
	$vrijemeIspis="$vrijemeS:$vrijemeM";
	return  $vrijemeIspis;
}

	global $userid,$user_siteadmin;
	
	// Parametri
	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);
	
	// Naziv predmeta
	$q10 = db_query("select naziv from predmet where id=$predmet");
	if (db_num_rows($q10)<1) {
		biguglyerror("Nepoznat predmet");
		zamgerlog("ilegalan predmet $predmet",3); //nivo 3: greska
		zamgerlog2("nepoznat predmet", $predmet);
		return;
	}
	$predmet_naziv = db_result($q10,0,0);
	
	
	
	// Da li korisnik ima pravo ući u modul?
	
	if (!$user_siteadmin) {
		$q10 = db_query("select nivo_pristupa from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
		if (db_num_rows($q10)<1 || db_result($q10,0,0)=="asistent") {
			zamgerlog("nastavnik/ispiti privilegije (predmet pp$predmet)",3);
			zamgerlog2("nije nastavnik na predmetu", $predmet, $ag);
			biguglyerror("Nemate pravo ulaska u ovu grupu!");
			return;
		} 
	}
	
	if ($_POST['akcija'] == "promjena_grupe" && check_csrf_token()) {
		$novagrupa = intval($_POST['grupa']);
		$id_stavke=intval($_POST['stavka_rasporeda']);
		$q01=db_query("update raspored_stavka set labgrupa=$novagrupa where id=$id_stavke");
		$q02=db_query("update raspored_stavka set labgrupa=$novagrupa where dupla=$id_stavke");
		$uspjesno_promijenjena_grupa=1;		
		zamgerlog("promijenjena grupa za stavku rasporeda na predmetu $predmet_naziv", 2);
		zamgerlog2("promijenjena grupa za stavku rasporeda", $novagrupa, $id_stavke);
	}
	
	if ($_POST['akcija'] == "sjeckanje termina" && check_csrf_token()) {
		$presjek = intval($_POST['presjek']);
		$id_stavke=intval($_POST['stavka_rasp']);
		$q0=db_query("select raspored,dan_u_sedmici,predmet,vrijeme_pocetak,vrijeme_kraj,sala,tip,labgrupa,dupla,isjeckana from raspored_stavka where id=$id_stavke");
		$raspored=db_result($q0,0,0);
		$dan_u_sedmici=db_result($q0,0,1);
		$predmet=db_result($q0,0,2);
		$pocetak=db_result($q0,0,3);
		$kraj=db_result($q0,0,4);
		$sala=db_result($q0,0,5);
		$tip=db_result($q0,0,6);
		$labgrupa=db_result($q0,0,7);
		$dupla=db_result($q0,0,8);
		$isjeckana=db_result($q0,0,9);
		// $isjeckana=0 znaci da stavka nije nikako isjeckana i prikazuje se u rasporedu
		// $isjeckana=1 znači da je stavka izrezana i ne prikazuje se u rasporedu, a cuva sa u bazi radi vracanja na pocetne casove prije nego sto je nastavnik ista mijenjao
		// $isjeckana=2 predstavlja dijelove od isjeckane stavke
		$q1=db_query("update raspored_stavka set isjeckana=1 where id=$id_stavke");
		$q2=db_query("insert into raspored_stavka set id='NULL', raspored=$raspored, dan_u_sedmici=$dan_u_sedmici, predmet=$predmet,
						vrijeme_pocetak=$pocetak,vrijeme_kraj=$presjek,sala=$sala,tip='$tip',labgrupa=$labgrupa,dupla=$dupla,isjeckana=2");
		$q21=db_query("select max(id) from raspored_stavka");
		$id_prve_stavke=db_result($q21,0,0);
		$q3=db_query("insert into raspored_stavka set id='NULL', raspored=$raspored, dan_u_sedmici=$dan_u_sedmici, predmet=$predmet,
						vrijeme_pocetak=$presjek,vrijeme_kraj=$kraj,sala=$sala,tip='$tip',labgrupa=$labgrupa,dupla=$dupla,isjeckana=2");
		$q31=db_query("select max(id) from raspored_stavka");
		$id_druge_stavke=db_result($q31,0,0);

		$q0=db_query("select raspored,dan_u_sedmici,predmet,vrijeme_pocetak,vrijeme_kraj,sala,tip,labgrupa,dupla,isjeckana,id from raspored_stavka where dupla=$id_stavke");
		for($i=0;$i<db_num_rows($q0);$i++){	
			$raspored=db_result($q0,$i,0);
			$dan_u_sedmici=db_result($q0,$i,1);
			$predmet=db_result($q0,$i,2);
			$pocetak=db_result($q0,$i,3);
			$kraj=db_result($q0,$i,4);
			$sala=db_result($q0,$i,5);
			$tip=db_result($q0,$i,6);
			$labgrupa=db_result($q0,$i,7);
			$dupla=db_result($q0,$i,8);
			$isjeckana=db_result($q0,$i,9);
			$id_duple_stavke=db_result($q0,$i,10);
			$q1=db_query("update raspored_stavka set isjeckana=1 where id=$id_duple_stavke");
			$q2=db_query("insert into raspored_stavka set id='NULL', raspored=$raspored, dan_u_sedmici=$dan_u_sedmici, predmet=$predmet,
							vrijeme_pocetak=$pocetak,vrijeme_kraj=$presjek,sala=$sala,tip='$tip',labgrupa=$labgrupa,dupla=$id_prve_stavke,isjeckana=2");
			$q3=db_query("insert into raspored_stavka set id='NULL', raspored=$raspored, dan_u_sedmici=$dan_u_sedmici, predmet=$predmet,
							vrijeme_pocetak=$presjek,vrijeme_kraj=$kraj,sala=$sala,tip='$tip',labgrupa=$labgrupa,dupla=$id_druge_stavke,isjeckana=2");
			
		}
		$uspjesno_razdvojena_stavka=1;
		zamgerlog("Isjeckana stavka rasporeda", 2);			 
		zamgerlog2("isjeckana stavka rasporeda", $id_stavke);
	}

?>

<p>&nbsp;</p>
<p><h3><?=$predmet_naziv?> - Raspored grupa</h3></p>
<h4>Početni spisak časova:</h4>
<table class="nastavnik_raspored" cellspacing="0" border="1">
	<tr>
	  	<th>Dan</th>
	    <th>Početak</th>
	    <th>Kraj</th>
	    <th>Sala</th>
	    <th>Tip</th>
	    <th>Grupa</th>
 	</tr>
	  	<?
	    $q0=db_query("select rs.dan_u_sedmici,rs.vrijeme_pocetak,rs.vrijeme_kraj,rs.sala,rs.tip,rs.labgrupa,rs.id from raspored_stavka rs,raspored r where rs.predmet=$predmet 
	    and rs.dupla=0 and rs.raspored=r.id and r.akademska_godina=$ag and (rs.tip='T' or rs.tip='L') and (rs.isjeckana=0 or rs.isjeckana=1)
	    order by rs.dan_u_sedmici asc,rs.vrijeme_pocetak asc,rs.labgrupa asc");
	    $qgrupe=db_query("select id,naziv from labgrupa where predmet=$predmet and akademska_godina=$ag");
	    for($i=0;$i<db_num_rows($q0);$i++){
	    	$dan=db_result($q0,$i,0);
	    	$pocetak=db_result($q0,$i,1);
	    	$kraj=db_result($q0,$i,2);
	    	$sala=db_result($q0,$i,3);
	    	$tip=db_result($q0,$i,4);
	    	$labgrupa=db_result($q0,$i,5);
	    	$id_stavke=db_result($q0,$i,6);
	    	if($dan==1) $dan_naziv="Ponedjeljak";
	    	elseif($dan==2) $dan_naziv="Utorak";
	    	elseif($dan==3) $dan_naziv="Srijeda";
	    	elseif($dan==4) $dan_naziv="Četvrtak";
	    	elseif($dan==5) $dan_naziv="Petak";
	    	elseif($dan==6) $dan_naziv="Subota";
	    	$q1=db_query("select naziv from raspored_sala where id=$sala");
	  		$sala_naziv=db_result($q1,0,0);
			$vrijemeP=vrijemeZaIspis($pocetak);
			$vrijemeK=vrijemeZaIspis($kraj);
			if($tip=='P') $tip_naziv="Predavanje";
	    	elseif($tip=='T') $tip_naziv="Tutorijal";
	    	elseif($tip=='L') $tip_naziv="Laboratorija";
	    	if($labgrupa!=-1){
	    		$q2=db_query("select naziv from labgrupa where id=$labgrupa");
	    		$labgrupa_naziv=db_result($q2,0,0);
	    	}
	    	else $labgrupa_naziv="prazno";
	    ?>
	<tr>		
	    <td><?=$dan_naziv?></td>
	    <td><?=$vrijemeP?></td>
	    <td><?=$vrijemeK?></td>
	    <td><?=$sala_naziv?></td>
	    <td><?=$tip_naziv?></td>
	    <td><?=$labgrupa_naziv?></td>
	</tr>
    <?
    }	
    ?>
  </tr>
</table>
<br><hr></hr>



<h4>Izmjena grupa i termina časova:</h4><br>
<?if($uspjesno_promijenjena_grupa==1) nicemessage("Grupa je uspješno promijenjena."); ?>
<?if($uspjesno_razdvojena_stavka==1) nicemessage("Stavka je uspješno razdvojena na 2 termina."); ?>
<table class="nastavnik_raspored" cellspacing="0" border="1">
	<tr>
	  	<th>Dan</th>
	    <th>Početak</th>
	    <th>Kraj</th>
	    <th>Sala</th>
	    <th>Tip</th>
	    <th>Grupa</th>
	    <th>Promjena grupe</th>
	    <th>Razdvajanje časa na 2 termina</th>
 	</tr>
	  	<?
	    $q0=db_query("select rs.dan_u_sedmici,rs.vrijeme_pocetak,rs.vrijeme_kraj,rs.sala,rs.tip,rs.labgrupa,rs.id from raspored_stavka rs,raspored r where rs.predmet=$predmet 
	    and rs.dupla=0 and rs.raspored=r.id and r.akademska_godina=$ag and (rs.tip='T' or rs.tip='L') and (rs.isjeckana=0 or rs.isjeckana=2) order by rs.dan_u_sedmici asc,rs.vrijeme_pocetak asc,rs.labgrupa asc");
	    $qgrupe=db_query("select id,naziv from labgrupa where predmet=$predmet and akademska_godina=$ag");
	    for($i=0;$i<db_num_rows($q0);$i++){
	    	$dan=db_result($q0,$i,0);
	    	$pocetak=db_result($q0,$i,1);
	    	$kraj=db_result($q0,$i,2);
	    	$sala=db_result($q0,$i,3);
	    	$tip=db_result($q0,$i,4);
	    	$labgrupa=db_result($q0,$i,5);
	    	$id_stavke=db_result($q0,$i,6);
	    	if($dan==1) $dan_naziv="Ponedjeljak";
	    	elseif($dan==2) $dan_naziv="Utorak";
	    	elseif($dan==3) $dan_naziv="Srijeda";
	    	elseif($dan==4) $dan_naziv="Četvrtak";
	    	elseif($dan==5) $dan_naziv="Petak";
	    	elseif($dan==6) $dan_naziv="Subota";
	    	$q1=db_query("select naziv from raspored_sala where id=$sala");
	  		$sala_naziv=db_result($q1,0,0);
	  		$vrijemeP=vrijemeZaIspis($pocetak);
			$vrijemeK=vrijemeZaIspis($kraj);
			if($tip=='P') $tip_naziv="Predavanje";
	    	elseif($tip=='T') $tip_naziv="Tutorijal";
	    	elseif($tip=='L') $tip_naziv="Laboratorija";
	    	if($labgrupa!=-1) {
	    		$q2=db_query("select naziv from labgrupa where id=$labgrupa");
	    		$labgrupa_naziv=db_result($q2,0,0);
	    	}
	    	else $labgrupa_naziv="prazno";
	    ?>
	<tr>		
	    <td><?=$dan_naziv?></td>
	    <td><?=$vrijemeP?></td>
	    <td><?=$vrijemeK?></td>
	    <td><?=$sala_naziv?></td>
	    <td><?=$tip_naziv?></td>
	    <td><?=$labgrupa_naziv?></td>
	    <td>
		    <?=genform("POST");?>
			<input type="hidden" name="akcija" value="promjena_grupe">
			<input type="hidden" name="stavka_rasporeda" value="<?=$id_stavke?>">
		    <select name="grupa">
		    	<option value="-1" <? if($labgrupa==-1) print " selected=\"selected\"";?>>--prazno--</option>
		    	<?
		    	for($j=0;$j<db_num_rows($qgrupe);$j++){
		    		$id=db_result($qgrupe,$j,0);
		    		$naziv=db_result($qgrupe,$j,1);
		    		print "<option value=\"$id\"";
		    		if($id==$labgrupa) print " selected=\"selected\"";
		    		print ">$naziv</option>";
		    	}
		    	?>
		    </select>
		    <input type="submit" value=" Promijeni ">
			</form>
	    </td>
	    <td>
	    <?
	    if(($pocetak+1)!=$kraj){?>
	    	<table>
		    	<tr>
			    	<td>
					    <?=genform("POST");?>
						<input type="hidden" name="akcija" value="sjeckanje termina">
						<input type="hidden" name="stavka_rasp" value="<?=$id_stavke?>">
					    <select name="presjek">
					    	<?
					    	for($j=$pocetak+1;$j<$kraj;$j++){
					    		$sredina=vrijemeZaIspis($j);
					    		print "<option value=\"$j\">$vrijemeP - $sredina  &nbsp&nbsp&nbsp&nbsp i &nbsp&nbsp&nbsp&nbsp $sredina - $vrijemeK</option>";		    
					    	}
					    	?>
					    </select>
				    </td>
				    <td>
			    		<input type="submit" value=" Razdvoji ">
			    	</td>
				</form>
				</tr>
			</table>
		<?} else print "<p>Ne može se više razdvajati!</p>";?>
	    </td>
	</tr>
    <?
    }	
    ?>
  </tr>
</table>
<?

}
?>