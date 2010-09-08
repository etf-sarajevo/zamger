<link href="css/raspored1.css" rel="stylesheet" type="text/css">
<?

// NASTAVNIK/RASPORED - editovanje grupa za predmet

// v5.0.0.0 (2010/09/07) + Dodat Super asistent kao korisnik koji moze pristupiti modulu



function nastavnik_raspored() {

	global $userid,$user_siteadmin;
	
	// Parametri
	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);
	
	// Naziv predmeta
	$q10 = myquery("select naziv from predmet where id=$predmet");
	if (mysql_num_rows($q10)<1) {
		biguglyerror("Nepoznat predmet");
		zamgerlog("ilegalan predmet $predmet",3); //nivo 3: greska
		return;
	}
	$predmet_naziv = mysql_result($q10,0,0);
	
	
	
	// Da li korisnik ima pravo ući u modul?
	
	if (!$user_siteadmin) { // 3 = site admin
		$q10 = myquery("select nivo_pristupa from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
	
		if ((mysql_num_rows($q10)<1 || mysql_result($q10,0,0)=="asistent") {
			zamgerlog("nastavnik/ispiti privilegije (predmet pp$predmet)",3);
			biguglyerror("Nemate pravo ulaska u ovu grupu!");
			return;
		} 
	}
	
	if ($_POST['akcija'] == "promjena_grupe" && check_csrf_token()) {
	$novagrupa = intval($_POST['grupa']);
	$id_stavke=intval($_POST['stavka_rasporeda']);
	$q01=myquery("update raspored_stavka set labgrupa=$novagrupa where id=$id_stavke");
	$q02=myquery("update raspored_stavka set labgrupa=$novagrupa where dupla=$id_stavke");
	$uspjesno_promijenjena_grupa=1;		
	zamgerlog("Promijenjena grupa na predmetu $predmet_naziv", 2);
	}

?>

<p>&nbsp;</p>
<p><h3><?=$predmet_naziv?> - Raspored grupa</h3></p>
Spisak časova:<br></br>
<?if($uspjesno_promijenjena_grupa==1) nicemessage("Grupa je uspješno promijenjena."); ?>
<table class="nastavnik_raspored" cellspacing="0" border="1">
	<tr>
	  	<th>Dan</th>
	    <th>Početak</th>
	    <th>Kraj</th>
	    <th>Sala</th>
	    <th>Tip</th>
	    <th>Grupa</th>
	    <th>Promjena grupe</th>
 	</tr>
	  	<?
	    $q0=myquery("select rs.dan_u_sedmici,rs.vrijeme_pocetak,rs.vrijeme_kraj,rs.sala,rs.tip,rs.labgrupa,rs.id from raspored_stavka rs,raspored r where rs.predmet=$predmet 
	    and rs.dupla=0 and rs.raspored=r.id and r.akademska_godina=$ag and (rs.tip='T' or rs.tip='L') order by rs.dan_u_sedmici asc,rs.vrijeme_pocetak asc,rs.labgrupa asc");
	    $qgrupe=myquery("select id,naziv from labgrupa where predmet=$predmet and akademska_godina=$ag");
	    for($i=0;$i<mysql_num_rows($q0);$i++){
	    	$dan=mysql_result($q0,$i,0);
	    	$pocetak=mysql_result($q0,$i,1);
	    	$kraj=mysql_result($q0,$i,2);
	    	$sala=mysql_result($q0,$i,3);
	    	$tip=mysql_result($q0,$i,4);
	    	$labgrupa=mysql_result($q0,$i,5);
	    	$id_stavke=mysql_result($q0,$i,6);
	    	if($dan==1) $dan_naziv="Ponedjeljak";
	    	elseif($dan==2) $dan_naziv="Utorak";
	    	if($dan==3) $dan_naziv="Srijeda";
	    	if($dan==4) $dan_naziv="Četvrtak";
	    	if($dan==5) $dan_naziv="Petak";
	    	$q1=myquery("select naziv from raspored_sala where id=$sala");
	  		$sala_naziv=mysql_result($q1,0,0);
	  		$vrijemePocS=floor(($pocetak-1)/4+9);
			$vrijemePocMin=$pocetak%4;
			if($vrijemePocMin==1) $vrijemePocM="00";
			elseif($vrijemePocMin==2) $vrijemePocM="15";
			elseif($vrijemePocMin==3) $vrijemePocM="30";
			elseif($vrijemePocMin==0) $vrijemePocM="45";
			$vrijemeP="$vrijemePocS:$vrijemePocM";
			$vrijemeKrajS=floor(($kraj-1)/4+9);
			$vrijemeKrajMin=$kraj%4;
			if($vrijemeKrajMin==1) $vrijemeKrajM="00";
			elseif($vrijemeKrajMin==2) $vrijemeKrajM="15";
			elseif($vrijemeKrajMin==3) $vrijemeKrajM="30";
			elseif($vrijemeKrajMin==0) $vrijemeKrajM="45";
			$vrijemeK="$vrijemeKrajS:$vrijemeKrajM";
			if($tip=='P') $tip_naziv="Predavanje";
	    	elseif($tip=='T') $tip_naziv="Tutorijal";
	    	elseif($tip=='L') $tip_naziv="Laboratorija";
	    	$q2=myquery("select naziv from labgrupa where id=$labgrupa");
	    	$labgrupa_naziv=mysql_result($q2,0,0);
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
		    	<?
		    	for($j=0;$j<mysql_num_rows($qgrupe);$j++){
		    		$id=mysql_result($qgrupe,$j,0);
		    		$naziv=mysql_result($qgrupe,$j,1);
		    		print "<option value=\"$id\"";
		    		if($id==$labgrupa) print " selected=\"selected\"";
		    		print ">$naziv</option>";
		    	}
		    	?>
		    </select>
		    <input type="submit" value=" Promijeni ">
			</form>
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