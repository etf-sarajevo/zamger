<?

// ADMIN/KOMPAKT - kompaktovanje baze za predmete koji su zavrseni

// v3.9.1.0 (2008/02/26) + Preimenovan bivsi admin_site



function admin_kompakt() {

global $userid;



###############
# Akcije
###############


if ($_POST['akcija'] == "kompaktuj") {
	$predmet = intval($_POST['predmet']);
	$q10 = myquery("select p.naziv, ag.naziv from ponudakursa as pk, predmet as p, akademska_godina as ag where pk.akademska_godina=ag.id and pk.id=$predmet and pk.predmet=p.id");
	if (!($r10 = mysql_fetch_row($q10))) {
		zamgerlog("nepoznat predmet $predmet",3); // nivo 3: greska
		niceerror("Predmet nije pronađen u bazi");
		return;
	}
	nicemessage("Kompaktujem predmet $r10[0] ($r10[1])");
	
	// Zadaće
	$q11 = myquery("select id,zadataka from zadaca where predmet=$predmet");
	$totcount=0;
	$diffcount=0;
	$stdincount=0;
	while ($r11 = mysql_fetch_row($q11)) {
		$zadaca = $r11[0];
		$brzad = $r11[1];
		
		// Historija statusa zadaće
		for ($i=1; $i<=$brzad; $i++) {
			$q12 = myquery("select id,student from zadatak where zadaca=$zadaca and redni_broj=$i order by student,id desc");
			$student=0;
			$count=0;
			while ($r12 = mysql_fetch_row($q12)) {
				if ($student != $r12[1]) {
					if ($count>0) {
//						print("$count statusa za ($student, $zadaca, $i)... ");
						$totcount += $count;
						$count=0;
					}
					$student=$r12[1];
				} else {
					$q13 = myquery("delete from zadatak where id=$r12[0]");
					$count++;
				}

				$q14 = myquery("delete from zadatakdiff where zadatak=$r12[0]");
				$diffcount++;
			}

			$q15 = myquery("select count(*) from stdin where zadaca=$zadaca and redni_broj=$i");
			$stdincount += mysql_result($q15,0,0);
			$q16 = myquery("delete from stdin where zadaca=$zadaca and redni_broj=$i");
		}
	}
	nicemessage("Obrisano: $totcount starih statusa zadaće, $diffcount diffova, $stdincount unosa.");

	zamgerlog("kompaktovana baza za predmet $predmet",4); // nivo 4: audit
}



?>
<p>&nbsp;</p>
<h3>Kompaktovanje baze</h3>
<p>Ovo je operacija kojim se iz baze brišu svi podaci koji nisu potrebni za ispravno izračunavanje ocjene. To uključuje: historiju starih statusa zadaće, razlike (diffove) zadaća, komentare i pomoćne ocjene za grupe/studente, unose za izvršavanje zadaće na serveru.</p>
<p>Izaberite koji predmet želite kompaktovati:<br/>
<?=genform() ?>
<input type="hidden" name="akcija" value="kompaktuj">
<select name="predmet">
<?
	$q100 = myquery("select pk.id, p.naziv, ag.naziv from ponudakursa as pk, predmet as p, akademska_godina as ag where pk.akademska_godina=ag.id and pk.predmet=p.id order by ag.naziv,p.naziv");
	while ($r100 = mysql_fetch_row($q100)) {
		print "<option value=\"$r100[0]\">$r100[1] ($r100[2])</option>\n";
	}
?>
</select>
<input type="submit" value=" Kompaktuj "></form>
<?


}

?>