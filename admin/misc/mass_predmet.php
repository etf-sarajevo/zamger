<?php



//----------------------------------------
// Masovni unos broja indexa
//----------------------------------------

function admin_misc_mass_predmet() {
	global $_api_http_code;

	$ag = intval($_REQUEST['ag']);
	if ($ag == 0) $ag = db_get("SELECT id FROM akademska_godina WHERE aktuelna=1");

	if ($_POST['akcija'] == "masspredmet" && strlen($_POST['nazad'])<1) {
		$pk = intval($_REQUEST['predmet']);
		$predmet = db_get("SELECT predmet FROM ponudakursa WHERE id=$pk");
		$predmet_naziv = db_get("SELECT pp.naziv FROM ponudakursa pk, pasos_predmeta pp WHERE pk.id=$pk AND pk.pasos_predmeta=pp.id");

		if ($_POST['fakatradi'] != 1) $ispis=1; else $ispis=0;
		$greska = 0;

		$mass_resultat = [];
		$greske = [];
		foreach(explode("\n", $_REQUEST['massinput']) as $red) {
			$brindexa = db_escape_string(trim($red));
			if (empty($brindexa)) continue;

			$podaci = db_query_assoc("SELECT id, ime, prezime, brindexa FROM osoba WHERE brindexa='$brindexa'");
			if (empty($podaci)) {
				$mass_rezultat[] = [ "id" => "greska", "brindexa" => $brindexa ];
				$greska = 1;
			} else
				$mass_rezultat[] = $podaci;
		}
		
		if (count($mass_rezultat)==0) {
			niceerror("Niste unijeli nijedan koristan podatak.");
			return;
		}
		
		if ($ispis) {
			?>Akcije koje će biti urađene:<br/><br/>
			<?=genform("POST")?>
			<input type="hidden" name="fakatradi" value="1">
			<table>
			<?
		}
		
		// Spisak studenata
		foreach ($mass_rezultat as $podaci) {
			if ($podaci['id'] == "greska") {
				if ($ispis == 1)  {
					?><tr bgcolor="#FFE3DD"><td>&nbsp;</td><td>&nbsp;</td><td><?=$podaci['brindexa']?></td><td>nepoznat student - da li ste dobro ukucali broj indeksa?</td></tr><?
				}
			} else {
				$student = $podaci['id'];
				$ime = $podaci['ime'];
				$prezime = $podaci['prezime'];
				$brindexa = $podaci['brindexa'];
				if ($ispis==1) {
					?><tr><td><?=$ime?></td><td><?=$prezime?></td><td><?=$brindexa?></td><td><?="upis na predmet $predmet_naziv ($predmet PK $pk)"?></td></tr><?
				}
				else {
					$result = api_call("course/$predmet/$ag/enroll/$student", [ "create" => true, "check" => false ], "POST");
					if ($_api_http_code == "201") {
						print "* Student $ime $prezime ($brindexa) upisan na predmet $predmet_naziv<br>";
						if (strstr($result['value'], "not enrolled in year"))
							print "Nije upisan u akademsku godinu: <a href=\"?sta=studentska/osobe&osoba=$student&akcija=edit\">Studentska/osobe</a><br>\n";
					} else if ($_api_http_code == "403") {
						print "* Student $ime $prezime ($brindexa) je već od ranije upisan na predmet $predmet_naziv<br>";
					} else {
						niceerror("Neuspješan upis studenta na predmet");
						api_report_bug($result, []);
					}
				}
			}
		}
		
		// Potvrda i Nazad
		if ($ispis) {
			?>
			</table>
			<input type="hidden" name="ag" value="<?=$ag?>">
			<input type="hidden" name="predmet" value="<?=$pk?>">
			<input type="hidden" name="massinput" value="<?=$_REQUEST['massinput']?>">
			<input type="submit" name="nazad" value=" Nazad ">
			<? if ($greska==0) print '<input type="submit" value=" Potvrda ">'; ?>
			</form>
			<?
			return;
		} else {
			?>
			Studenti upisani na predmet.<br>
			<a href="?sta=admin/misc&module=mass_predmet">Nazad</a>
			<?
			return;
		}
		
		
	}
	
	
	?>
	
	<p><hr/></p><p><b>Masovni upis studenata u predmet</b><br/>
	<?=genform("POST")?>
	<input type="hidden" name="fakatradi" value="0">
	<input type="hidden" name="akcija" value="masspredmet">
	<input type="hidden" name="ag" value="<?=$ag?>">

	<p>Izaberite predmet: <select name="predmet">
	<?
	
	$param_studij = intval($_REQUEST['studij']);
	if ($param_studij > 0) $add_studij = "AND s.id=$param_studij"; else $add_studij = "";
	$parni = intval($_REQUEST['parni']);
	if ($parni > 0) $add_parni = " AND semestar MOD 2 = 0"; else $add_parni = "";
	
	$q10 = db_query("SELECT DISTINCT pk.id, pp.naziv, s.kratkinaziv, pk.semestar
		FROM ponudakursa pk, pasos_predmeta pp, studij s
		WHERE pk.akademska_godina=$ag AND pk.pasos_predmeta=pp.id AND pk.studij=s.id $add_studij $add_parni
		ORDER BY s.id, pk.semestar, pp.naziv");
	while(db_fetch4($q10, $id, $naziv, $studij, $semestar)) {
		if ($param_studij == 0) $print_studij = "($studij $semestar)"; else $print_studij = "";
		print "<option value=\"$id\">$naziv $print_studij</option>\n";
	}
	?>
	</select></p>
	
	<p>Brojevi indexa studenata (Svaki u zasebnom redu):</p>
	<textarea name="massinput" cols="50" rows="10"><?
		if (strlen($_POST['nazad'])>1) print $_POST['massinput'];
		?></textarea><br/>
	
	<input type="submit" value="  Dodaj  ">
	</form></p><?
	
}
