<?php 


function student_framework(){
	global $_lv_, $userid; 

	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);
	if ($predmet == 108) $varijanta=1;
	else $varijanta=0;

	
	if ($varijanta==0) {
	
		if ($predmet != 157 || $ag != 6) { niceerror("Neaktivno"); return; }
		
		$q17 = myquery("select sp.predmet from student_predmet as sp, ponudakursa as pk where sp.student=$userid and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
		if (mysql_num_rows($q17)<1 && $userid != 1 && $userid != 1585 && 
	$userid != 306 && $userid != 1759 && $userid != 1247 && $userid != 1004 
	&& $userid != 1079 && $userid != 1573 && $userid != 307 && $userid != 
	615) {
			zamgerlog("student ne slusa predmet pp$predmet (godina ag$ag)", 3);
			biguglyerror("Niste upisani na ovaj predmet");
			return;
		}
		$zadaca = -1;
	
		print "<h2>Teme za projekte - MMS</h2>";
		
		if ($_REQUEST['akcija'] == "preuzmi" && check_csrf_token()) {
			$q100 = myquery("select id, naslov, osoba, grupa from tema_za_zadacu_2 where zadaca=$zadaca order by id");
			while ($r100 = mysql_fetch_row($q100)) {
				if ($_REQUEST["izaberi-$r100[0]"] == "yes") {
					if ($r100[2]==0) {
						$q120 = myquery("update tema_za_zadacu_2 set osoba=$userid where id=$r100[0]");
						nicemessage("Tema '$r100[1]' potvrđena");
					} else {
						niceerror("Ninja!!!");
					}
				}
			}
		}
		
		print genform("POST");
		?>
		<input type="hidden" name="akcija" value="preuzmi">
		<?

		$q100 = myquery("select id, naslov, osoba, grupa from tema_za_zadacu_2 where zadaca=$zadaca order by id");
		while ($r100 = mysql_fetch_row($q100)) {
			if ($r100[3]==1) {
				print "<p><b>$r100[1]</b><br>\n";
			} else if ($r100[2]==0) {
				// Nije zauzeta
				print "<input type=\"checkbox\" name=\"izaberi-$r100[0]\" value=\"yes\"> $r100[1]<br>\n";
			} else {
//				if ($userid == 1) {
					$q110 = myquery("select prezime, ime from osoba where id=$r100[2]");
					print "&nbsp;[x] $r100[1] - ".mysql_result($q110,0,0)." ".mysql_result($q110,0,1)."<br>\n";
//				} else {
//					print "&nbsp;[x] $r100[1] - zauzeta<br>\n";
//				}
			}
		}
		
		?>
		<input type="submit" value=" Preuzmi temu/teme ">
		</form>
		<?
	}
	
	if ($varijanta==1) {
	

	if ($predmet != 108 || $ag != 6) {
		biguglyerror("Za sada samo Softverski inženjering...");
		return;
	}
	$zadaca=1124;

	if ($_POST['akcija']=="probaj_temu") {
		$q105 = myquery("select potvrdjena from tema_za_zadacu where zadaca=$zadaca and osoba=$userid");
		if (mysql_num_rows($q105)>0) {
			if (mysql_result($q105,0,0)==1) {
				niceerror("Vaša tema je potvrđena i ne možete je promijeniti.");
				return;
			}
			nicemessage("Mijenjam temu...");
			$q106 = myquery("delete from tema_za_zadacu where zadaca=$zadaca and osoba=$userid");
		}

		$tema = my_escape($_POST['tema']);
		$tema = trim($tema);
		foreach (explode(" ", $tema) as $rijec) {
			$q109 = myquery("select o.prezime, o.ime, tzz.naslov, tzz.potvrdjena from osoba as o, tema_za_zadacu as tzz where tzz.zadaca=$zadaca and tzz.osoba=o.id and tzz.naslov like '%$rijec%'");
			if (mysql_num_rows($q109)>0) {
				niceerror("Izgleda da je tema već zauzeta od strane: ".mysql_result($q109,0,0)." ".mysql_result($q109,0,1).".");
				break;
			}
		}
		$q110 = myquery("insert into tema_za_zadacu set osoba=$userid, zadaca=$zadaca, naslov='$tema', potvrdjena=0");
		nicemessage("Dodata nova tema");
		zamgerlog("odabrana tema za zadaću", 2);
	}


	?>
	<p>Unesite temu koju želite raditi za zadaću:</p>
	<?=genform("POST")?>
	<input type="hidden" name="akcija" value="probaj_temu">
	<input type="text" size="50" name="tema"><br>
	<input type="submit" value="Potvrda">
	</form>
	<br>
	<hr>
	<p><b>Teme koje su birali drugi studenti:</b></p>
	<?

	$q108 = myquery("select o.prezime, o.ime, tzz.naslov, tzz.potvrdjena, tzz.komentar from osoba as o, tema_za_zadacu as tzz where tzz.zadaca=$zadaca and tzz.osoba=o.id order by tzz.naslov");
	while ($r108 = mysql_fetch_row($q108)) {
		print "$r108[0] $r108[1], &quot;$r108[2]&quot;";
		if ($r108[3]==1) print " - <font color=\"red\"><b><i>potvrđena!</i></b></font>";
		if ($r108[4]!="") print " - <b>$r108[4]</b>";
		print "<br>\n";
	}
	}
}
?>
