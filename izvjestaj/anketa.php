<?

// IZVJESTAJ/ANKETA - stranica koja generise izvjestaje za predmete koje mogu pregledati profesori ili clanovi studentske sluzbe

// v5.0.0.0 (2010/09/07) + Dodat Super asistent kao korisnik koji moze pristupiti ovom tipu izvjestaja

function izvjestaj_anketa(){

	global $userid,$user_siteadmin,$user_studentska;

	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);

	// naziv predmeta
	$q10 = myquery("select p.naziv,pk.akademska_godina,p.id from predmet as p, ponudakursa as pk where pk.predmet=p.id and p.id=$predmet and pk.akademska_godina=$ag; ");
	$naziv_predmeta = mysql_result($q10,0,0);

	// provjera da li je dati profesor zadužen na predmetu za koji želi pogledat izvještaj
	if (!$user_siteadmin && !$user_studentska) {
		$q20 = myquery("select admin from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
		$q21 = myquery("select super_asistent from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
		if ((mysql_num_rows($q20)==0) || (mysql_num_rows($q21)==0)) {
			zamgerlog("nastavnik/izvjestaj_anketa privilegije ",3);
			biguglyerror("Nemate pravo pregledati ovaj izvještaj!");
			return;
		}
	}
	
	// naziv akademske godine
	$q30 = myquery("select naziv from akademska_godina where id=$ag");
	$naziv_ak_god = mysql_result($q30,0,0);
	
	// da li postoji anketa?
	$q40 = myquery("select aa.id from anketa_anketa as aa where aa.akademska_godina=$ag and (select count(*) from anketa_rezultat as ar where ar.anketa=aa.id and ar.predmet=$predmet)>0 order by id desc"); // prikaži anketu koju je neko popunjavao
	if (mysql_num_rows($q40)<0)
		$q40 = myquery("select id from anketa_anketa where akademska_godina=$ag");

	if (mysql_num_rows($q40)==0){
		biguglyerror("Za datu akademsku godinu nije kreirana anketa!");
		return;
	}
	$anketa = mysql_result($q40,0,0);


	if ($_REQUEST['komentar'] == "da") {
		// ---------------------------------------------   IZVJESTAJ ZA KOMENTARE ---------------------------------------------
		
		$limit = 5; // broj kometara prikazanih po stranici
		$offset = intval($_REQUEST["offset"]);

	 	$q50 = myquery("select count(*) from anketa_rezultat where predmet=$predmet and anketa = $anketa AND zavrsena='Y'");
		$broj_anketa = mysql_result($q50,0,0);

		?>
		<center>
		<h2>Prikaz svih komentara za predmet <?=$naziv_predmeta?> za akademsku godinu <?=$naziv_ak_god?></h2>
		
		<h3>Broj studenata koji su pristupili anketi je: <?=$broj_anketa?></h3>
		<?
		
		
		// pokupimo sve komentare za dati predmet
		$q60 = myquery("SELECT count(*) FROM anketa_odgovor_text WHERE odgovor<>'' and rezultat IN (SELECT id FROM anketa_rezultat WHERE predmet=$predmet and anketa=$anketa AND zavrsena='Y')");
		$broj_odgovora = mysql_result($q60,0,0);
		$q61 = myquery(" SELECT odgovor FROM anketa_odgovor_text WHERE odgovor<>'' and rezultat IN (SELECT id FROM anketa_rezultat WHERE predmet =$predmet and anketa=$anketa) limit $offset, $limit");
		
		if ($broj_odgovora == 0)
			print "Nema rezultata!";

		else if ($broj_odgovora > $limit) {
			$donja_granica=$offset+1;
			$gornja_granica=$offset+5;
			if ($gornja_granica>$broj_odgovora) $gornja_granica=$broj_odgovora;
				
			print "Prikazujem rezultate $donja_granica-$gornja_granica od $broj_odgovora. Stranica: ";
			for ($i=0; $i < $broj_odgovora; $i+=$limit) {
				$br = intval($i/$limit)+1;
				
				if ($i == $offset)
					print "<b>$br</b> ";
				else
					print "<a href=\"?sta=izvjestaj/anketa&predmet=$predmet&ag=$ag&komentar=da&offset=$i\">$br</a> ";
			}
			print "<br/>";
		}
	
		?>
		<table width="650px"  >
			 <tr>
				<td bgcolor="#6699CC" height="10">   </td>
			</tr>
		<?
		$i=0;
		while ($r61 = mysql_fetch_row($q61)) {
			$komentar = str_replace("\n", "<br/>\n", $r61[0]);
			?><tr>
				<td><hr/></td>
			</tr>
			<tr>
				<td><?=$komentar?></td>
			</tr>
			<?
			$i++;
		}	
		?>
		
		</table> 
		</center>
		<?
		
	}
	// -------------------------------------------------   KRAH IZVJEŠTAJ ZA KOMENTARE  ------------------------------------------------------------------------
	
	// -------------------------------------------------   IZVJEŠTAJ ZA RANK PITANJA  ------------------------------------------------------------------------
	else if ($_REQUEST['rank'] == "da") {

		print "<center>";
		print "<h2>Statistika za predmet $naziv_predmeta za akademsku godinu $naziv_ak_god</h2>\n";

		$q100 = myquery("select count(*) from anketa_rezultat where predmet=$predmet and anketa = $anketa AND zavrsena='Y'" );
		$broj_anketa = mysql_result($q100,0,0);
		print "<h3> Broj studenata koji su pristupili anketi je : $broj_anketa </h3>";
		
		// broj rank pitanja
		$q110 = myquery("SELECT id FROM anketa_pitanje WHERE anketa =$anketa and tip_pitanja =1");
		
		$i = 0;
		while ($r110 = mysql_fetch_row($q110)) {
			$j=$i+1;
			$q120 = myquery("SELECT avg(izbor_id), count(izbor_id) FROM anketa_odgovor_rank WHERE rezultat IN (SELECT id FROM anketa_rezultat WHERE predmet=$predmet and anketa=$anketa AND zavrsena='Y') AND pitanje = $r110[0]");
			$prosjek[$i]=mysql_result($q120,0,0);
			$broj_odgovora[$i]=mysql_result($q120,0,1);
			$i++;
		}
		
		// kupimo pitanja
		$q130 = myquery("SELECT p.id, p.tekst,t.tip FROM anketa_pitanje p,anketa_tip_pitanja t WHERE p.tip_pitanja = t.id and p.anketa =$anketa and p.tip_pitanja=1");

		?>
		
		<table width="800px">
			<tr> 
				<td bgcolor="#6699CC">&nbsp;&nbsp;Pitanje</td><td bgcolor="#6699CC" width='350px'>&nbsp;&nbsp;Prosjek odgovora</td>
			</tr>
		
			<tr>
				<td colspan="2"><hr/></td>
			</tr>
			<tr>
				 <td>&nbsp;</td><td bgcolor="#FF0000" width='350px'>&nbsp;MAX </td>
			</tr>
		<?

		$i=0;
		while ($r130 = mysql_fetch_row($q130)) {
			$tekst=$r130[1];
			$procenat=($prosjek[$i]/5)*100;

			?><tr height='35'>
				<td><?=($i+1)?>. <?=$tekst?><br><font color="#999999"><small>(<?=$broj_odgovora[$i]?> odgovora)</small></font></td>
				<td>
					<table border='0' width='350px'>
					<tr> 
	        				<td height='30' width='<?=$procenat?>%' bgcolor="#CCCCFF"> &nbsp;<?=round($prosjek[$i],2)?></td>
						<td width='<?=(100-$procenat)?>"%'> </td>
        				</tr></table> 
				</td>
			</tr>
			<?
			
			$i++;
		}

		// Prosječan broj bodova na svim pitanjima
		$prosjek = array_sum($prosjek)/count($prosjek);

		?>
		<tr> 
				<td colspan="2"> <hr/>  </td>
			</tr>
			  <!--tr > 
				 <td align="right"> Prosjek predmeta : </td> <td  width='350px'> &nbsp;<strong><?=round($prosjek,2)?> </strong> </td>
			 </tr-->
		</table> 
		</center>
		<?
	}
}
?>