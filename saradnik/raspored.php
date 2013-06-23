<?

// SARADNIK/RASPORED - podešavanje rasporeda za nastavnika

		
function saradnik_raspored($tip) {

	global $userid, $user_nastavnik, $user_studentska;

	// Nizovi sa imenima termina
	$vrijeme_pocetak = array("0" => "08:00", "1" => "09:00", "2" => "10:00", "3" => "11:00", "4" => "12:00", "5" => "13:00",
				"6" => "14:00", "7" => "15:00", "8" => "16:00", "9" => "17:00", "10" => "18:00", "11" => "19:00", "12" => "20:00");
	$vrijeme_kraj = array("0" => "08:45", "1" => "09:45", "2" => "10:45", "3" => "11:45", "4" => "12:45", "5" => "13:45",
				"6" => "14:45", "7" => "15:45", "8" => "16:45", "9" => "17:45", "10" => "18:45", "11" => "19:45", "12" => "20:45");

	$dani_u_sedmici = array("", "Ponedjeljak", "Utorak", "Srijeda", "Četvrtak", "Petak", "Subota");


	?>
	<h2> Podešavanje rasporeda</h2>
	<a href="index.php?sta=saradnik/intro">Povratak na početnu stranicu.</a><br /><br />
	
	<?
	
	
	// AKCIJA - DODAVANJE STAVKE U RASPORED
	
	if ($_REQUEST['akcija'] == "dodaj" && check_csrf_token()) {
		$dan = intval($_REQUEST['dan']);
		$predmet = intval($_REQUEST['predmet']);
		$tip = $_REQUEST['tip'];
		$vvrijeme_pocetak = intval($_REQUEST['vrijeme_pocetak']);
		$vvrijeme_kraj = intval($_REQUEST['vrijeme_kraj']);
		$fini_pocetak = $_REQUEST['fini_pocetak'];
		$fini_kraj = $_REQUEST['fini_kraj'];
		$sala = intval($_REQUEST['sala']);
		if ($_REQUEST['javan']) $privatno = 0; else $privatno = $userid;;
		$labgrupa = intval($_REQUEST['labgrupa']);
		
		$greska = "";
		if ($dan == 0) $greska .= "Niste izabrali dan u sedmici. ";
		if ($tip != "P" && $tip != "T" && $tip != "L") $greska .= "Pogrešan tip časa. ";
		if ($vvrijeme_kraj < $vvrijeme_pocetak) $greska .= "Čas se ne može završiti prije nego što počne! ";
		
		$matches = array();
		if (!preg_match("/^(\d\d)\:(\d\d)\:(\d\d)$/", $fini_pocetak, $matches)) $greska .= "Precizno vrijeme početka nije u ispravnom formatu. ";
		$pocstamp = mktime($matches[1], $matches[2], $matches[3]);
		if (!preg_match("/^(\d\d)\:(\d\d)\:(\d\d)$/", $fini_kraj, $matches)) $greska .= "Precizno vrijeme kraja nije u ispravnom formatu. ";
		$krajstamp = mktime($matches[1], $matches[2], $matches[3]);
		if ($greska == "" && $krajstamp < $pocstamp) $greska .= "Čas se ne može završiti prije nego što počne! (precizno vrijeme) ";
		
		if ($greska != "") {
			niceerror($greska);
			print "<a href=\"javascript:history.back();\">Nazad</a>";
			return;
		}

		$q200 = myquery("select id from akademska_godina where aktuelna=1");
		$ag = mysql_result($q200,0,0);

		if ($labgrupa == 0) {
		
			?>
			<b>Upravo u raspored dodajete čas sa sljedećim podacima:</b><br />
			Dan u sedmici: <?=$dani_u_sedmici[$dan] ?><br />
			Predmet: <?
				$q300 = myquery("select naziv from predmet where id=$predmet");
				print mysql_result($q300,0,0);
			?><br />
			Tip časa: <?=$tip ?><br />
			Trajanje časa: <?=$vrijeme_pocetak[$vvrijeme_pocetak] ?> - <?=$vrijeme_kraj[$vvrijeme_kraj] ?><br />
			<?
				if ($fini_pocetak != "00:00:00") {
					?>
					Preciznije trajanje: <?=$fini_pocetak." - ".$fini_kraj ?><br />
					<?
				}
			?>
			Sala: <?
				$q310 = myquery("select naziv from raspored_sala where id=$sala");
				print mysql_result($q310,0,0);
			?><br />
			<?
			if ($privatno == 0) print "Vidljivo studentima<br />"; else print "Nije vidljivo studentima<br />";
			?>
			Ako neki od ovih podataka nije tačan, <a href="javascript:history.back();">vratite se nazad</a>.<br /><br />
			
			<?=genform("POST");?>
			Da li je ovaj čas specifičan za jednu od grupa na predmetu ili je zajednički za sve?<br />
			Izaberite grupu: <select name="labgrupa">
				<option value="-1">Zajednički za sve</option>
				<?
				$q199 = myquery("select id, naziv from labgrupa where predmet=$predmet and akademska_godina=$ag and virtualna=0 order by naziv");
				while ($r199 = mysql_fetch_row($q199)) {
					print "<option value=\"$r199[0]\">$r199[1]</option>";
				}
				?>
			</select><br />
			<input type="submit" value=" Potvrda ">
			</form>
			<?
			return;
		}
		if ($labgrupa == -1) $labgrupa=0;

		// Dodati studij, semestar
		
		$q210 = myquery("select id from raspored where akademska_godina=$ag and privatno=$privatno");
		if (mysql_num_rows($q210) < 1) {
			$q220 = myquery("insert into raspored set studij=0, semestar=0, akademska_godina=$ag, privatno=$privatno, aktivan=1");
			$id_rasporeda = mysql_insert_id();
			zamgerlog("kreiran raspored $id_rasporeda", 2);
		} else 
			$id_rasporeda = mysql_result($q210,0,0);
			
		$q230 = myquery("insert into raspored_stavka set raspored=$id_rasporeda, dan_u_sedmici=$dan, predmet=$predmet, labgrupa=$labgrupa, vrijeme_pocetak=$vvrijeme_pocetak, vrijeme_kraj=$vvrijeme_kraj, sala=$sala, tip='$tip', dupla=0, isjeckana=0, fini_pocetak='$fini_pocetak', fini_kraj='$fini_kraj'");

		zamgerlog("dodana stavka ".mysql_insert_id()." u raspored $id_rasporeda", 2);
		nicemessage ("Dodavanje časa u raspored uspjelo!");
		print "<a href=\"?sta=saradnik/raspored\">Nastavak</a>";
		return;
	}


	// AKCIJA - potvrda izmjene

	if ($_REQUEST['akcija'] == "potvrda_izmjene" && check_csrf_token()) {
		$id_stavke = intval($_REQUEST['id_stavke']);
		$dan = intval($_REQUEST['dan']);
		$predmet = intval($_REQUEST['predmet']);
		$tip = $_REQUEST['tip'];
		$vvrijeme_pocetak = intval($_REQUEST['vrijeme_pocetak']);
		$vvrijeme_kraj = intval($_REQUEST['vrijeme_kraj']);
		$fini_pocetak = $_REQUEST['fini_pocetak'];
		$fini_kraj = $_REQUEST['fini_kraj'];
		$sala = intval($_REQUEST['sala']);
		if ($_REQUEST['javan']) $privatno = 0; else $privatno = $userid;;
		$labgrupa = intval($_REQUEST['labgrupa']);
		
		$greska = "";
		if ($dan == 0) $greska .= "Niste izabrali dan u sedmici. ";
		if ($tip != "P" && $tip != "T" && $tip != "L") $greska .= "Pogrešan tip časa. ";
		if ($vvrijeme_kraj < $vvrijeme_pocetak) $greska .= "Čas se ne može završiti prije nego što počne! ";
		
		$matches = array();
		if (!preg_match("/^(\d\d)\:(\d\d)\:(\d\d)$/", $fini_pocetak, $matches)) $greska .= "Precizno vrijeme početka nije u ispravnom formatu. ";
		$pocstamp = mktime($matches[1], $matches[2], $matches[3]);
		if (!preg_match("/^(\d\d)\:(\d\d)\:(\d\d)$/", $fini_kraj, $matches)) $greska .= "Precizno vrijeme kraja nije u ispravnom formatu. ";
		$krajstamp = mktime($matches[1], $matches[2], $matches[3]);
		if ($greska == "" && $krajstamp < $pocstamp) $greska .= "Čas se ne može završiti prije nego što počne! (precizno vrijeme) ";
		
		if ($greska != "") {
			niceerror($greska);
			print "<a href=\"javascript:history.back();\">Nazad</a>";
			return;
		}

		$q200 = myquery("select id from akademska_godina where aktuelna=1");
		$ag = mysql_result($q200,0,0);

		// Dodati studij, semestar
		
		$q210 = myquery("select id from raspored where akademska_godina=$ag and privatno=$privatno");
		if (mysql_num_rows($q210) < 1) {
			$q220 = myquery("insert into raspored set studij=0, semestar=0, akademska_godina=$ag, privatno=$privatno, aktivan=1");
			$id_rasporeda = mysql_insert_id();
			zamgerlog("kreiran raspored $id_rasporeda", 2);
		} else 
			$id_rasporeda = mysql_result($q210,0,0);
			
		$q230 = myquery("update raspored_stavka set raspored=$id_rasporeda, dan_u_sedmici=$dan, predmet=$predmet, labgrupa=$labgrupa, vrijeme_pocetak=$vvrijeme_pocetak, vrijeme_kraj=$vvrijeme_kraj, sala=$sala, tip='$tip', dupla=0, isjeckana=0, fini_pocetak='$fini_pocetak', fini_kraj='$fini_kraj' where id=$id_stavke");
		
		zamgerlog("ažurirana stavka $id_stavke u rasporedu $id_rasporeda", 2);
		nicemessage ("Ažuriranje časa u rasporedu uspjelo!");
		print "<a href=\"?sta=saradnik/raspored\">Nastavak</a>";
		return;
	}

	
	// SPISAK PREDMETA NA KOJIMA JE ANGAŽOVAN NASTAVNIK
	
	$q10 = myquery("select count(*) from student_studij as ss, akademska_godina as ag where ss.akademska_godina=ag.id and ag.aktuelna=1 and ss.semestar mod 2=0");
	if (mysql_num_rows($q10)>0) $neparni=0; else $neparni=1;

	$whereCounter = 0;
	$spisak_predmeta = "";
	
	if ($user_studentska && $_REQUEST['dajsve']==1) {
		$q20 = myquery("SELECT pk.predmet, pk.akademska_godina, pk.semestar, p.id, p.naziv FROM 
ponudakursa as pk, akademska_godina as ag, predmet as p WHERE pk.akademska_godina = ag.id and 
ag.aktuelna=1 and pk.predmet=p.id");
	} else if ($user_nastavnik) {
		$q20 = myquery("SELECT np.predmet, pk.akademska_godina, pk.semestar, p.id, p.naziv FROM nastavnik_predmet as np, ponudakursa as pk, akademska_godina as ag, predmet as p WHERE np.nastavnik = $userid AND pk.predmet = np.predmet AND np.predmet=p.id and pk.akademska_godina = ag.id and np.akademska_godina=ag.id and ag.aktuelna=1");
	} else {
		$q20 = myquery("SELECT pk.predmet, pk.akademska_godina, pk.semestar, p.id, p.naziv FROM student_predmet as sp, ponudakursa as pk, akademska_godina as ag, predmet as p WHERE sp.student = $userid AND pk.id = sp.predmet AND pk.akademska_godina = ag.id and ag.aktuelna=1 and pk.predmet=p.id");
	}
	
	while($r20 = mysql_fetch_row($q20)) {
		$ag = $r20[1];
		$semestar = $r20[2];
		if ($semestar%2 != $neparni) continue;
		
		if($whereCounter > 0)
			$sqlPredmet .= " OR rs.predmet = ".$r20[0];
		else
			$sqlPredmet = " rs.predmet = ".$r20[0];
		
		$whereCounter++;
		
		$spisak_predmeta .= "<option value=\"$r20[3]\">$r20[4]</option>\n";
	}
		
	if (strlen($sqlPredmet)>0) $sqlWhere = "(".$sqlPredmet.")";
	else $sqlWhere="1=0"; // Nije angazovan nigdje, prikaži prazan raspored

	
	
	// AKCIJA - IZMJENA STAVKE
	
	if ($_REQUEST['akcija'] == "izmjena") {
		$rid = intval($_REQUEST['id']);
		$q400 = myquery("select rs.dan_u_sedmici, rs.predmet, rs.labgrupa, rs.vrijeme_pocetak, rs.vrijeme_kraj, rs.sala, rs.tip, rs.fini_pocetak, rs.fini_kraj, r.privatno from raspored_stavka as rs, raspored as r where rs.id=$rid and rs.raspored=r.id");
		if (mysql_num_rows($q400)<1) {
			niceerror("Nepoznata stavka u rasporedu");
			return ;
		}
		
		$mdan = mysql_result($q400,0,0);
		$mpredmet = mysql_result($q400,0,1);
		$mgrupa = mysql_result($q400,0,2);
		$mpoc = mysql_result($q400,0,3);
		$mkraj = mysql_result($q400,0,4);
		$msala = mysql_result($q400,0,5);
		$mtip = mysql_result($q400,0,6);
		$mfinipoc = mysql_result($q400,0,7);
		$mfinikraj = mysql_result($q400,0,8);
		
		if (mysql_result($q400,0,9) == 0) $javno=" checked"; else $javno = "";
		
		$spisak_predmeta = str_replace("option value=\"$mpredmet\"", "option value=\"$mpredmet\" selected", $spisak_predmeta);
	
		?>
		<b>Izmijenite čas u rasporedu:</b><br /><br />
		<?=genform("POST"); ?>
		<input type="hidden" name="akcija" value="potvrda_izmjene">
		<input type="hidden" name="id_stavke" value="<?=$rid ?>">
		Dan u sedmici: <select name="dan"><? 
			foreach ($dani_u_sedmici as $id=>$ime) {
				if ($id == $mdan) $sel=" selected"; else $sel = "";
				print "<option value=\"$id\" $sel>$ime</option>\n"; 
			}
		?></select><br />
		Predmet: <select name="predmet"><?=$spisak_predmeta?></select><br />
		Tip časa: <select name="tip">
			<option value="P" <? if ($mtip=="P") print "selected"; ?>>Predavanja</option>
			<option value="T" <? if ($mtip=="T") print "selected"; ?>>Tutorijali</option>
			<option value="L" <? if ($mtip=="L") print "selected"; ?>>Laboratorijske vježbe</option>
		</select><br />
		<br />
		Početak časa: <select name="vrijeme_pocetak"><? 
			foreach ($vrijeme_pocetak as $id=>$ime) {
				if ($id == $mpoc) $sel=" selected"; else $sel = "";
				print "<option value=\"$id\" $sel>$ime</option>\n"; 
			}
		?></select><br />
		Završetak časa: <select name="vrijeme_kraj"><? 
			foreach ($vrijeme_kraj as $id=>$ime) {
				if ($id == $mkraj) $sel=" selected"; else $sel = "";
				print "<option value=\"$id\" $sel>$ime</option>\n"; 
			}
		?></select><br /><br />
		Ako vrijednosti izabrane iznad nisu dovoljno precizne, unesite precizniju vrijednost vremena u polje ispod (u formatu hh:mm:ss)<br />
		Preciznije vrijeme početka: <input type="text" name="fini_pocetak" size="10" value="<?=$mfinipoc ?>" /><br />
		Preciznije vrijeme završetka: <input type="text" name="fini_kraj" size="10" value="<?=$mfinikraj ?>" /><br /><br />
		Sala: <select name="sala"><?
			$q100 = myquery("select id,naziv, kapacitet, tip from raspored_sala order by naziv");
			while ($r100 = mysql_fetch_row($q100)) {
				if ($r100[3] != "") $r100[3] = ", ".$r100[3];
				if ($r100[0] == $msala) $sel=" selected"; else $sel = "";
				print "<option value=\"$r100[0]\" $sel>$r100[1] ($r100[2] mjesta$r100[3])</option>\n";
			}
		?></select><br />
		Nastavna grupa: <select name="labgrupa"><?
			if ($mgrupa == 0) $sel = " selected"; else $sel = "";
			print "<option value=\"0\" $sel>Zajednički za sve</option>\n";
		
			$q410 = myquery("select id from akademska_godina where aktuelna=1");
			$ag = mysql_result($q410,0,0);

			$q420 = myquery("select id, naziv from labgrupa where predmet=$mpredmet and akademska_godina=$ag order by naziv");
			while ($r420 = mysql_fetch_row($q420)) {
				if ($r420[0] == $mgrupa) $sel=" selected"; else $sel = "";
				print "<option value=\"$r420[0]\" $sel>$r420[1]</option>\n";
			}
		?></select><br />
		Da li želite da čas bude vidljiv svima? <input type="checkbox" name="javan" <?=$javno?>> DA<br />
		<br />
		<input type="submit" value=" Potvrdite izmjene časa ">
		</form>
		<?
		
		return;
	}
	
	
	// DODAVANJE ČASA U RASPORED
	
	?>
	<b>Dodajte čas u raspored:</b><br /><br />
	<?=genform("POST"); ?>
	<input type="hidden" name="akcija" value="dodaj">
	Dan u sedmici: <select name="dan"><? foreach ($dani_u_sedmici as $id=>$ime) print "<option value=\"$id\">$ime</option>\n"; ?></select><br />
	Predmet: <select name="predmet"><?=$spisak_predmeta?></select><br />
	Tip časa: <select name="tip">
		<option value="P">Predavanja</option>
		<option value="T">Tutorijali</option>
		<option value="L">Laboratorijske vježbe</option>
	</select><br />
	<br />
	Početak časa: <select name="vrijeme_pocetak"><? foreach ($vrijeme_pocetak as $id=>$ime) print "<option value=\"$id\">$ime</option>\n"; ?></select><br />
	Završetak časa: <select name="vrijeme_kraj"><? foreach ($vrijeme_kraj as $id=>$ime) print "<option value=\"$id\">$ime</option>\n"; ?></select><br /><br />
	Ako vrijednosti izabrane iznad nisu dovoljno precizne, unesite precizniju vrijednost vremena u polje ispod (u formatu hh:mm:ss)<br />
	Preciznije vrijeme početka: <input type="text" name="fini_pocetak" size="10" value="00:00:00" /><br />
	Preciznije vrijeme završetka: <input type="text" name="fini_kraj" size="10" value="00:00:00" /><br /><br />
	Sala: <select name="sala"><?
		$q100 = myquery("select id,naziv, kapacitet, tip from raspored_sala order by naziv");
		while ($r100 = mysql_fetch_row($q100)) {
			if ($r100[3] != "") $r100[3] = ", ".$r100[3];
			print "<option value=\"$r100[0]\">$r100[1] ($r100[2] mjesta$r100[3])</option>\n";
		}
	?></select><br />
	Da li želite da čas bude vidljiv svima? <input type="checkbox" name="javan"> DA<br />
	<br />
	<input type="submit" value=" Dodaj čas u raspored ">
	</form>
	<p>&nbsp;</p>
	
	<?
	
	
	
	// TRENUTNI RASPORED
	
	?>

	<b>Vaš trenutni raspored:</b>
	<br /><br />
	<?
	
	
	$q30 = myquery("SELECT rs.id, p.naziv as naz, p.kratki_naziv, rs.dan_u_sedmici, rs.tip, rs.vrijeme_pocetak, rs.vrijeme_kraj, rs.labgrupa, rsala.naziv, rs.fini_pocetak, rs.fini_kraj, r.privatno
	FROM raspored_stavka as rs, raspored_sala as rsala, predmet as p, raspored as r, akademska_godina as ag
	WHERE ".$sqlWhere." AND rsala.id=rs.sala AND p.id=rs.predmet AND rs.raspored=r.id and r.akademska_godina=ag.id and ag.aktuelna=1 and (r.privatno=0 or r.privatno=$userid)
	ORDER BY rs.dan_u_sedmici ASC, rs.vrijeme_pocetak ASC, rs.id ASC");
	if (mysql_num_rows($q30) == 0)
		print "<br />Nijedan čas nije definisan u vašem rasporedu.";

	$old_dan = -1;	
	
	while ($r30 = mysql_fetch_row($q30)) {
		if ($r30[3] != $old_dan) { print "</ul><br />".$dani_u_sedmici[$r30[3]]."<ul>"; $old_dan = $r30[3]; }
		
		if ($r30[4] == "P") $tip = "Predavanja"; else if ($r30[4] == "T") $tip = "Tutorijali"; else $tip = "Laboratorijske vježbe";
		
		?>
		<li><?=$vrijeme_pocetak[$r30[5]]." - ".$vrijeme_kraj[$r30[6]] ?>, <b><?=$r30[1] ?></b> ( <a href="?sta=saradnik/raspored&akcija=izmjena&id=<?=$r30[0]?>">izmijeni</a> )<br />
		<? if ($r30[9] != "00:00:00") {
			?>
			Preciznije vrijeme: <?=substr($r30[9], 0, 5) ?> - <?=substr($r30[10], 0, 5) ?><br />
			<?
		} ?>
		Sala: <?=$r30[8]?><br />
		Tip časa: <?=$tip?><br />
		<? if ($r30[7] != 0) {
			$q40 = myquery("select naziv from labgrupa where id=".$r30[7]);
			?>
			Grupa: <?=mysql_result($q40,0,0); ?><br />
			<?
		} ?>
		<? if ($r30[11] == 0) print "Javno"; else print "Privatno";
		print "<br /></li>\n";
	}

}

?>
