<?php


//----------------------------------------
// Generiši izvještaj
//----------------------------------------

function admin_misc_generisi_izvjestaj() {
	
	if ($_POST['akcija']=="generisi_izvjestaj" && check_csrf_token()) {
		// Generisem statičku verziju izvještaja predmet
		generisi_izvjestaj_predmet( $_POST['predmet'], $_POST['ag'], array("skrati" => "da", "sakrij_imena" => "da", "razdvoji_ispite" => "da") );
		
		nicemessage("Izvještaj generisan");
		
	} else {
		
		?>
		<?=genform("POST")?>
		<input type="hidden" name="akcija" value="generisi_izvjestaj">
		Unesite ID predmeta: <input type="text" name="predmet" value=""><br>
		Akademska godina: <input type="text" name="ag" value=""><br>
		<input type="submit" value=" Generiši izvještaj ">
		</form>
		<?
	}
}