<?

// ADMIN/MISC - sta god mi padne na pamet da kodiram





function admin_misc() {

require("lib/manip.php");
global $mass_rezultat; // za masovni unos studenata u grupe


?>
<p>&nbsp;</p>
<h3>Ostalo</h3>
<p>Ovdje možete dodati svoj kod:</p>
<?




//----------------------------------------
// Masovni unos jmbg
//----------------------------------------

function testjmbg($jmbg) {
	if (strlen($jmbg)!=13) return "JMBG nema tačno 13 cifara";
	for ($i=0; $i<13; $i++) {
		$slovo = substr($jmbg,$i,1);
		if ($slovo<'0' || $slovo>'9') return "Neki od znakova nisu cifre";
		$cifre[$i] = $slovo-'0';
	}
	// Datum
	if (!checkdate($cifre[2]*10+$cifre[3], $cifre[0]*10+$cifre[1], $cifre[4]*10+$cifre[5]))
		return "Datum rođenja je kalendarski nemoguć";
	// Checksum
	$k = 11 - (( 7*($cifre[0]+$cifre[6]) + 6*($cifre[1]+$cifre[7]) + 5*($cifre[2]+$cifre[8]) + 4*($cifre[3]+$cifre[9]) + 3*($cifre[4]+$cifre[10]) + 2*($cifre[5]+$cifre[11]) ) % 11);
	if ($k==11) $k=0;
	if ($k!=$cifre[12]) return "Checksum ne valja ($cifre[12] a trebao bi biti $k)";
	return "";
}

if ($_REQUEST['akcija'] == "massjmbg" && strlen($_POST['nazad'])<1) {

	if ($_POST['fakatradi'] != 1) $ispis=1; else $ispis=0;

	$greska=mass_input($ispis); // Funkcija koja parsira podatke
	if (count($mass_rezultat)==0) {
		niceerror("Niste unijeli nijedan koristan podatak.");
		return;
	}

	if ($ispis) {
		?>Akcije koje će biti urađene:<br/><br/>
		<?=genform("POST")?>
		<input type="hidden" name="fakatradi" value="1">
		<?
	}

	// Spisak studenata
	foreach ($mass_rezultat['ime'] as $student=>$ime) {
		$prezime = $mass_rezultat['prezime'][$student];

		$jmbg = trim($mass_rezultat['podatak1'][$student]);
		$t = testjmbg($jmbg);
		if ($t != "")
			if ($ispis==1) print "++ Student '$ime $prezime' JMBG '$jmbg' -- $t<br/>\n";
			else print "$ime $prezime - $jmbg<br/>\n";
		else if ($ispis==1)
			print "Student '$ime $prezime' ispravan JMBG $jmbg<br/>\n";
		
		if ($ispis==0)
			$q10 = myquery("update osoba set jmbg='$jmbg' where id=$student");
	}

	// Potvrda i Nazad
	if ($ispis) {//DELIĆ	ADNAN		2907987110034

		print '<input type="submit" name="nazad" value=" Nazad "> ';
		if ($greska==0) print '<input type="submit" value=" Potvrda ">';
		print "</form>";
		return;
	} else {
		?>
		Upisani JMBGovi.
		<?
	}


}



?>

<p><hr/></p><p><b>Masovni unos JMBGova</b><br/>
<?=genform("POST")?>
<input type="hidden" name="fakatradi" value="0">
<input type="hidden" name="akcija" value="massjmbg">
<input type="hidden" name="nazad" value="">
<input type="hidden" name="visestruki" value="1">
<input type="hidden" name="duplikati" value="0">
<input type="hidden" name="brpodataka" value="1">

<textarea name="massinput" cols="50" rows="10"><?
if (strlen($_POST['nazad'])>1) print $_POST['massinput'];
?></textarea><br/>
<br/>Format imena i prezimena: <select name="format" class="default">
<option value="0" <? if($format==0) print "SELECTED";?>>Prezime[TAB]Ime</option>
<option value="1" <? if($format==1) print "SELECTED";?>>Ime[TAB]Prezime</option>
<option value="2" <? if($format==2) print "SELECTED";?>>Prezime Ime</option>
<option value="3" <? if($format==3) print "SELECTED";?>>Ime Prezime</option></select>&nbsp;
Separator: <select name="separator" class="default">
<option value="0" <? if($separator==0) print "SELECTED";?>>Tab</option>
<option value="1" <? if($separator==1) print "SELECTED";?>>Zarez</option></select><br/><br/>
<br/><br/>

<input type="submit" value="  Dodaj  ">
</form></p><?

}

?>