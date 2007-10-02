<?

// v3.0.1.1 (2007/09/12) + Novi modul: site admin (za sada samo kompaktovanje)
// v3.0.1.2 (2007/10/02) + Dodan logging


function admin_site() {

global $userid;

global $_lv_; // We use form generators


# Vrijednosti

$tab=$_REQUEST['tab'];
if ($tab=="") $tab="Kompaktovanje";


###############
# Akcije
###############


if ($_POST['akcija'] == "kompaktuj") {
	$predmet = intval($_POST['predmet']);
	$q10 = myquery("select p.naziv, ag.naziv from predmet as p, akademska_godina as ag where p.akademska_godina=ag.id and p.id=$predmet");
	if (!($r10 = mysql_fetch_row($q10))) {
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

	logthis("Kompaktovana baza za predmet $predmet");
}



###############
# Ispis tabova
###############


function printtab($ime,$tab) {
	if ($ime==$tab) 
		print '<td bgcolor="#DDDDDD" width="50">'.$ime.'</td>'."\n";
	else
		print '<td bgcolor="#BBBBBB" width="50"><a href="qwerty.php?sta=siteadmin&&tab='.$ime.'">'.$ime.'</a></td>'."\n";
}

?>
<p><h3>Site Admin</h3></p>

<table border="0" cellspacing="1" cellpadding="5" width="550">
<tr>
<td width="50">&nbsp;</td>
<? 
printtab("Kompaktovanje",$tab); 
?>
<td bgcolor="#BBBBBB" width="50"><a href="qwerty.php">Nazad</a></td>
<td width="450">&nbsp;</td>
</tr>
<tr>
<td width="50">&nbsp;</td>
<td colspan="8" bgcolor="#DDDDDD" width="500">
<?


if ($tab == "Kompaktovanje") {
	?>
	<p><b>Kompaktovanje baze</b><br/>
	Ovo je operacija kojim se iz baze brišu svi podaci koji nisu potrebni za ispravno izračunavanje ocjene. To uključuje: historiju starih statusa zadaće, razlike (diffove) zadaća, komentare i pomoćne ocjene za grupe/studente, unose za izvršavanje zadaće na serveru.</p>
	<p>Izaberite koji predmet želite kompaktovati:<br/>
	<?=genform() ?>
	<input type="hidden" name="akcija" value="kompaktuj">
	<select name="predmet">
	<?
		$q100 = myquery("select p.id, p.naziv, ag.naziv from predmet as p, akademska_godina as ag where p.akademska_godina=ag.id order by ag.naziv,p.naziv");
		while ($r100 = mysql_fetch_row($q100)) {
			print "<option value=\"$r100[0]\">$r100[1] ($r100[2])</option>\n";
		}
	?>
	</select>
	<input type="submit" value=" Kompaktuj "></form>
	<?
}

?>
</td>
</tr>
</table>
<?

}

?>