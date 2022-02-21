<?php

//----------------------------------------
// Masovni unos jmbg
//----------------------------------------

function admin_misc_mass_jmbg() {
	require_once("lib/utility.php"); // testjmbg
	require_once("lib/zamgerui.php"); // mass_input
	
	global $mass_rezultat;
	
	if ($_POST['akcija'] == "massjmbg" && strlen($_POST['nazad'])<1) {
		
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
		} else {
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
				$q10 = db_query("update osoba set jmbg='$jmbg' where id=$student");
		}
		
		// Potvrda i Nazad
		if ($ispis) {
			
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