<?

// ADMIN/NOVAGODINA - kreiranje nove akademske godine





function admin_novagodina() {


require("lib/manip.php");


if ($_POST['akcija'] == "novagodina") {
	if ($_POST['fakatradi'] != 1) $ispis=1; else $ispis=0;

	$naziv = my_escape($_POST['godina']);
	$q10 = myquery("select id from akademska_godina where naziv like '$naziv'");
	if (mysql_num_rows($q10)<1) {
		$q20 = myquery("select id from akademska_godina order by id desc limit 1");
		$noviid = mysql_result($q20,0,0)+1;
		$q30 = myquery("insert into akademska_godina set id=$noviid, naziv='$naziv', aktuelna=0");
		$q10 = myquery("select id from akademska_godina where naziv like '$naziv'");
		$ag = mysql_result($q10,0,0);
		print "-- Kreirana nova akademska godina '$naziv' (ID: $ag). Koristite modul 'Parametri studija' da je proglasite za aktuelnu.<br/>\n";
	} else {
		$ag = mysql_result($q10,0,0);
		print "-- Pronađena postojeća akademska godina (ID: $ag) - neće biti kreirana nova godina.<br/>\n";
	}
	
	$q40 = myquery("select s.id, s.naziv, ts.trajanje, ts.moguc_upis from studij as s, tipstudija as ts where s.tipstudija=ts.id");
	while ($r40 = mysql_fetch_row($q40)) {
		$studij = $r40[0];
		if ($ispis) print "-- Studij $r40[1]<br/>\n";

		if ($r40[3]==0) {
			if ($ispis) print "&nbsp;&nbsp;&nbsp;!! Nije moguć upis na ovaj studij.<br/>";
			continue;
		}

		$bio=array();
		for ($sem=1; $sem<=$r40[2]; $sem++) {
			if ($ispis) print "&nbsp;&nbsp;&nbsp;-- Semestar $sem<br/>\n";
			$min_god_vazenja = $ag-intval(($sem-1)/2);
			$q50 = myquery("select predmet, godina_vazenja, obavezan from plan_studija where studij=$studij and semestar=$sem and godina_vazenja<=$min_god_vazenja");
			if (mysql_num_rows($q50)<1) {
				if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;!! Nije pronađen plan studija mlađi od godine sa IDom $min_god_vazenja<br/>\n";
			}
			while ($r50 = mysql_fetch_row($q50)) {
				if ($r50[2]==1) { // obavezan
					kreiraj_ponudu_kursa ($r50[0], $studij, $sem, $ag, 1, $ispis);


				} else { // izborni
					$iz = $r50[0];
					// $iz je slot, uzimamo sve predmete u tom slotu
					$q70 = myquery("select p.id, p.naziv from predmet as p, izborni_slot as iz where iz.id=$iz and iz.predmet=p.id");
					while ($r70 = mysql_fetch_row($q70)) {
						$predmet = $r70[0];
						if (in_array($predmet, $bio)) continue;
						array_push($bio, $predmet);
						kreiraj_ponudu_kursa ($predmet, $studij, $sem, $ag, 0, $ispis);
					}
				}
			}
		}
	}

	if ($ispis) {
		?><?=genform("POST")?>
		<input type="submit" value="Potvrdi">
		<input type="hidden" name="fakatradi" value="1">
		</form>
		<?
	} else {
		print "Podaci su ubačeni.";
	}


} else {
	


	$q = myquery("select naziv from akademska_godina order by id desc limit 1");
	
	?>
	<h2>Nova akademska godina</h2>
	<p>Ovaj modul kreira novu akademsku godinu u bazi, a zatim za datu godinu kreira sve predmete koji su predviđeni aktuelnim planovima svih kreiranih studija.</p>
	<p>Klikom na dugme "Kreiraj" biće najprije ispisano šta će se sve uraditi, te ponuđeno dugme "Potvrda" nakon kojeg će akcije biti izvršene i baza izmijenjena.</p>
	<p><?=genform("POST")?>
	<input type="hidden" name="akcija" value="novagodina">
	<input type="text" name="godina" size="20" value="<?=mysql_result($q,0,0)?>">
	<input type="submit" value=" Kreiraj novu akademsku godinu ">
	</form>
	<hr>
	<?
}





}

?>