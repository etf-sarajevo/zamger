<?

// IZVJESTAJ/ANKETA - stranica koja generise izvjestaje za predmete koje mogu pregledati profesori ili clanovi studentske sluzbe



function izvjestaj_anketa() {

	global $userid,$user_siteadmin,$user_studentska, $user_student, $user_nastavnik;
	global $conf_skr_naziv_institucije_genitiv;

	require_once("lib/utility.php"); // procenat

	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);
	$anketa = intval($_REQUEST['anketa']);

	// naziv predmeta
	$q10 = db_query("select p.naziv,pk.akademska_godina,p.id from predmet as p, ponudakursa as pk where pk.predmet=p.id and p.id=$predmet and pk.akademska_godina=$ag; ");
	$naziv_predmeta = db_result($q10,0,0);

	if (!$user_siteadmin && !$user_studentska) {
		$pristup_nastavnik = $pristup_student = false;

		// provjera da li je dati profesor zadužen na predmetu za koji želi pogledat izvještaj
		if ($user_nastavnik) {
			$q20 = db_query("select nivo_pristupa from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
			if (db_num_rows($q20)>0 && db_result($q20,0,0) != 'asistent') {
				$pristup_nastavnik = true;
			}
		}
		
		// pravo pristupa studentima za ankete 
		if (!$pristup_nastavnik && $user_student) {
			$q20 = db_query("select count(*) from student_predmet as sp, ponudakursa as pk where sp.student=$userid and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
			if (db_result($q20,0,0)>0) {
				$pristup_student = true;
			}
		}
		
		if (!$pristup_nastavnik && !$pristup_student) {
			zamgerlog("nastavnik/izvjestaj_anketa privilegije",3);
			zamgerlog2("privilegije");
			biguglyerror("Nemate pravo pregledati ovaj izvještaj!");
			return;
		}
	}
	
	// naziv akademske godine
	$q30 = db_query("select naziv from akademska_godina where id=$ag");
	$naziv_ak_god = db_result($q30,0,0);
	
	// da li je dat ID ankete kao parametar
	if ($anketa>0) 
		$q40 = db_query("select id, aktivna from anketa_anketa where akademska_godina= $ag and id=$anketa");
	else {
		// ...nije, uzimamo zadnju anketu na predmetu za koju postoje rezultati
		$q40 = db_query("select aa.id, aa.aktivna from anketa_anketa as aa where aa.akademska_godina=$ag and (select count(*) from anketa_rezultat as ar where ar.anketa=aa.id and ar.predmet=$predmet)>0 order by id desc");
		if (db_num_rows($q40)<1)
			$q40 = db_query("select id, aktivna from anketa_anketa where akademska_godina=$ag");
	}

	if (db_num_rows($q40)==0){
		biguglyerror("Za datu akademsku godinu nije kreirana anketa!");
		return;
	}
	$anketa = db_result($q40,0,0);
	$aktivna = db_result($q40,0,1);

	if (!$user_siteadmin && !$user_studentska && $aktivna==1) {
		?>
		<h2>Pristup rezultatima ankete nije moguć</h2>
		<p><?=$pristup_student?> <?=$userid?> Rezultatima ankete se može pristupiti tek nakon isteka određenog roka. Za dodatne informacije predlažemo da kontaktirate službe <?=$conf_skr_naziv_institucije_genitiv?></p>
		<?
		return;
	}

	if ($_REQUEST['komentar'] == "da") {
		// Studenti ne mogu vidjeti komentare
		if ($pristup_student) {
			zamgerlog("nastavnik/izvjestaj_anketa student pristupa komentarima",3);
			zamgerlog2("student pristupa komentarima");
			biguglyerror("Studenti nemaju pravo pristupa komentarima");
			return;
		}
	
		// ---------------------------------------------   IZVJESTAJ ZA KOMENTARE ---------------------------------------------
		
		$limit = intval($_REQUEST["limit"]); // broj kometara prikazanih po stranici
		if ($limit == 0) $limit = 5;
		$offset = intval($_REQUEST["offset"]);

	 	$q50 = db_query("select count(*) from anketa_rezultat where predmet=$predmet and anketa = $anketa AND zavrsena='Y'");
		$broj_anketa = db_result($q50,0,0);

		?>
		<center>
		<h2>Prikaz svih komentara za predmet <?=$naziv_predmeta?> za akademsku godinu <?=$naziv_ak_god?></h2>
		
		<h3>Broj studenata koji su pristupili anketi je: <?=$broj_anketa?></h3>
		<?
		
		
		// pokupimo sve komentare za dati predmet
		$q60 = db_query("SELECT count(*) FROM anketa_odgovor_text WHERE odgovor<>'' and rezultat IN (SELECT id FROM anketa_rezultat WHERE predmet=$predmet and anketa=$anketa AND zavrsena='Y')");
		$broj_odgovora = db_result($q60,0,0);
		$q61 = db_query(" SELECT odgovor FROM anketa_odgovor_text WHERE odgovor<>'' and rezultat IN (SELECT id FROM anketa_rezultat WHERE predmet =$predmet and anketa=$anketa) limit $offset, $limit");
		
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
		while ($r61 = db_fetch_row($q61)) {
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

		$q100 = db_query("select count(*) from anketa_rezultat where predmet=$predmet and anketa = $anketa AND zavrsena='Y'" );
		$broj_anketa = db_result($q100,0,0);
		print "<h3> Broj studenata koji su pristupili anketi je : $broj_anketa </h3>";
		
		// broj rank pitanja
		$q110 = db_query("SELECT id FROM anketa_pitanje WHERE anketa =$anketa and tip_pitanja =1");
		
		$prosjek = array();
		while ($r110 = db_fetch_row($q110)) {
			$q120 = db_query("SELECT avg(izbor_id), count(izbor_id) FROM anketa_odgovor_rank WHERE rezultat IN (SELECT id FROM anketa_rezultat WHERE predmet=$predmet and anketa=$anketa AND zavrsena='Y') AND pitanje = $r110[0]");
			$prosjek[$r110[0]]=db_result($q120,0,0);
			$broj_odgovora[$r110[0]]=db_result($q120,0,1);
		}
		
		// kupimo pitanja
		$q130 = db_query("SELECT p.id, p.tekst,t.tip FROM anketa_pitanje p,anketa_tip_pitanja t WHERE p.tip_pitanja = t.id and p.anketa =$anketa and p.tip_pitanja=1 order by p.id");

		if (db_num_rows($q130) > 0) {
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
		}

		$i=0;
		while ($r130 = db_fetch_row($q130)) {
			$tekst=$r130[1];
			$procenat=($prosjek[$r130[0]]/5)*100;

			?><tr height='35'>
				<td><?=($i+1)?>. <?=$tekst?><br><font color="#999999"><small>(<?=$broj_odgovora[$r130[0]]?> odgovora)</small></font></td>
				<td>
					<table border='0' width='350px'>
					<tr> 
	        				<td height='30' width='<?=$procenat?>%' bgcolor="#CCCCFF"> &nbsp;<?=round($prosjek[$r130[0]],2)?></td>
						<td width='<?=(100-$procenat)?>%'> </td>
        				</tr></table> 
				</td>
			</tr>
			<?
			
			$i++;
		}

		// Prosječan broj bodova na svim pitanjima
		if (count($prosjek) == 0) $prosjek = 0;
		else $prosjek = array_sum($prosjek)/count($prosjek);

		
		// PITANJA TIPA IZBOR
		
		//kupimo pitanja
		$q200 = db_query("SELECT p.id, p.tekst,t.tip FROM anketa_pitanje p,anketa_tip_pitanja t WHERE p.tip_pitanja = t.id and p.anketa =$anketa and (p.tip_pitanja=3 or p.tip_pitanja=4) order by p.id");

		if (db_num_rows($q200)>0) {
		?>
		
		<table width="800px"  >
			<tr> 
				<td bgcolor="#6699CC"> Pitanje </td> <td bgcolor="#6699CC" width='350px'> Odgovori </td>
			</tr>
		   
			<tr> 
				<td colspan="2"> <hr/>  </td>
			</tr>
		<?
		$i=0;
		while ($r200 = db_fetch_row($q200)) {
			$id_pitanja=$r200[0];
			$tekst=$r200[1];
			$ispis_odgovori = "";
			
			$q205 = db_query("SELECT COUNT(oi.rezultat) FROM anketa_odgovor_izbori oi WHERE oi.pitanje=$id_pitanja GROUP BY oi.izbor_id");
			$max_odgovora = 0;
			while(db_fetch1($q205, $broj))
				if ($broj > $max_odgovora) $max_odgovora = $broj;

			$q210 = db_query("select ip.id, ip.izbor, ip.dopisani_odgovor, count(oi.rezultat) from anketa_izbori_pitanja as ip, anketa_odgovor_izbori as oi where ip.pitanje=$id_pitanja and oi.pitanje=$id_pitanja and oi.izbor_id=ip.id group by ip.id");
			while ($r210 = db_fetch_row($q210)) {
	
				$procenat = round($r210[3]/$broj_anketa, 4)*100;
				$procenat_sirina = round($r210[3]/$max_odgovora, 4)*100;
				$ispis_odgovori .= "<table border='0' width='350px'>
				<tr> 
					<td height='30' width='$procenat_sirina%' bgcolor=\"#CCCCFF\"> &nbsp;</td>
					<td width='" . (100-$procenat_sirina) . "%'>&nbsp;</td>
				</tr></table>";
				$ispis_odgovori .= $r210[1]." - ".$r210[3]." ($procenat%)<br>\n";
				
				
				if ($r210[2]==1) {
					$q220 = db_query("select odgovor from anketa_odgovor_dopisani where pitanje=$id_pitanja");
					if (db_num_rows($q220)==0) continue;
					$ispis_odgovori .= "<font color=\"#BBBBBB\">";
					while ($r220 = db_fetch_row($q220)) {
						$ispis_odgovori .= "&quot;".$r220[0]."&quot; ";
					}
					$ispis_odgovori .= "</font><br>\n";
				}
			}

			$q230 = db_query("select count(distinct rezultat) from anketa_odgovor_izbori where pitanje=$id_pitanja");
			$q240 = db_query("select count(*) from anketa_odgovor_izbori where pitanje=$id_pitanja and izbor_id=0");
			$neodg = $broj_anketa - db_result($q230,0,0) + db_result($q240,0,0);
			$ispis_odgovori .= "<i>neodgovoreno: $neodg (".(round($neodg/$broj_anketa, 4)*100)."%)</i>";

			?><tr height='35'>
				<td><?=($i+1)?>. <?=$tekst?></td>
				<td width="100"><?=$ispis_odgovori?></td>
			</tr>
			<tr><td colspan="2"><hr></td></tr>
			<?
			
			$i++;
		}
		} // db_num_rows($result202)


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
