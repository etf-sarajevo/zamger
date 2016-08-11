<?

// ADMIN/KOMPAKT - kompaktovanje baze za predmete koji su zavrseni



function admin_kompakt() {

global $userid, $conf_files_path;



###############
# Akcije
###############

if (param('akcija') == "kompaktuj") {
	$ponudakursa = intval($_POST['predmet']);
	$q10 = db_query("select p.naziv, ag.naziv, p.id, ag.id from ponudakursa as pk, predmet as p, akademska_godina as ag where pk.akademska_godina=ag.id and pk.id=$predmet and pk.predmet=p.id");
	if (!($r10 = db_fetch_row($q10))) {
		zamgerlog("nepoznat predmet $predmet",3); // nivo 3: greska
		niceerror("Predmet nije pronađen u bazi");
		return;
	}
	nicemessage("Kompaktujem predmet $r10[0] ($r10[1])");
	$predmet = $r10[2];
	$ag = $r10[3];
	
	// Zadaće
	$q11 = db_query("select id,zadataka, programskijezik from zadaca where predmet=$predmet and akademska_godina=$ag");
	$totcount=0;
	$diffcount=0;
	$stdincount=0;
	$filecount=0;
	$lokacijazadaca="$conf_files_path/zadace/$predmet-$ag/";
	while ($r11 = db_fetch_row($q11)) {
		$zadaca = $r11[0];
		$brzad = $r11[1];
		$pj = $r11[2];

		// Ekstenzija
		if ($pj>0) {
			$q11a = db_query("select ekstenzija from programskijezik where id=$pj");
			$ekstenzija = db_result($q11a,0,0);
		}
		
		// Historija statusa zadaće
		for ($i=1; $i<=$brzad; $i++) {
			$q12 = db_query("select id,student, filename, redni_broj from zadatak where zadaca=$zadaca and redni_broj=$i order by student,id desc");
			$student=0;
			$count=0;
			while ($r12 = db_fetch_row($q12)) {
				if ($student != $r12[1]) {
					if ($count>0) {
//						print("$count statusa za ($student, $zadaca, $i)... ");
						$totcount += $count;
						$count=0;
					}
					$student=$r12[1];
				} else {
					$q13 = db_query("delete from zadatak where id=$r12[0]");
					$count++;
				}

				$q13a = db_query("select count(*) from zadatakdiff where zadatak=$r12[0]");
				$q14 = db_query("delete from zadatakdiff where zadatak=$r12[0]");
				$diffcount+=db_result($q13a,0,0);

				// Brisanje fajla / attachment
				$filename = $r12[2];
				if (preg_match("/\w/", $filename)) {
					$path = $lokacijazadaca."$student/$zadaca/$filename";
					if (file_exists($path)) {
						unlink($path);
						$filecount++;
					}
				}
				$path = $lokacijazadaca."$student/$zadaca/$r12[3]$ekstenzija";
				if (file_exists($path)) {
					unlink($path);
					$filecount++;
				}
			}

			$q15 = db_query("select count(*) from stdin where zadaca=$zadaca and redni_broj=$i");
			$stdincount += db_result($q15,0,0);
			$q16 = db_query("delete from stdin where zadaca=$zadaca and redni_broj=$i");
		}
	}
	nicemessage("Obrisano: $totcount starih statusa zadaće, $diffcount diffova, $stdincount unosa stdin, $filecount datoteka.");

	zamgerlog("kompaktovana baza za predmet p$ponudakursa",4); // nivo 4: audit
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
	$q100 = db_query("select pk.id, p.naziv, ag.naziv from ponudakursa as pk, predmet as p, akademska_godina as ag where pk.akademska_godina=ag.id and pk.predmet=p.id order by ag.naziv,p.naziv");
	while ($r100 = db_fetch_row($q100)) {
		print "<option value=\"$r100[0]\">$r100[1] ($r100[2])</option>\n";
	}
?>
</select>
<input type="submit" value=" Kompaktuj "></form>
<?


}

?>