<?php


// Import studenata iz eUpis aplikacije

function fix_ime($string) {
	$result = mb_substr($string,0,1);
	$string = strtolower($string);
	$string = strtr($string, ["Š" => "š"]);
	$result .= mb_substr($string, 1);
	return $result;
}

function upitnik_like($s1, $s2) {
	$s1 = strtr($s1, ["Č" => "?", "Ć" => "?", "č" => "?", "ć" => "?", "đ" => "?"]);
	$s2 = strtr($s2, ["Č" => "?", "Ć" => "?", "č" => "?", "ć" => "?", "đ" => "?"]);
	return strtolower($s1) == strtolower($s2);
}


function admin_misc_import_eunsa() {
	global $_api_http_code;
	
	global $conf_debug;
	$conf_debug = true;
	
	if ($_POST['akcija']=="import_eunsa" && check_csrf_token()) {
		if ($_POST['fakatradi'] == 1) $f=true; else $f=false;
		
		$id = db_get("SELECT MAX(id) FROM osoba") + 1;
		$prethodna_godina = db_get("SELECT id FROM akademska_godina WHERE aktuelna=1");
		$godina = $prethodna_godina + 1;
		$i = 0;
		
		$pocni_od = int_param('pocni_od');
		
		$svi_logini = db_query_varray("SELECT login FROM auth");
		
		print "<table><tr><td><b>Zamger ID</b></td><td><b>Ime</b></td><td><b>Prezime</b></td><td><b>Ime oca</b></td><td><b>Novi login</b></td><td><b>Broj indexa</b></td><td><b>Stari login</b></td><td><b>JMBG</b></td><td><b>OID</b></td></tr>\n";
		
		foreach(explode("\n", $_POST['csv']) as $line) {
			if (empty(trim($line))) continue;
			$i++;
			$parts = explode(",", trim($line));
			if ($parts[0] == "Prezime") continue;
			
			if ($i < $pocni_od) {
				continue;
			}
			
			$prezime = trim($parts[0]);
			$ime = trim($parts[2]);
			
			$jmbg = $parts[4];
			if ($parts[5] == "Muški") $spol='M'; else $spol='Z';
			$oid = $parts[6];
			$imeoca = trim($parts[7]);
			$datum_rodjenja = substr($parts[9],0,10);
			if ($parts[11] == "Sarajevo" && $parts[12] == "Centar" || $parts[10] == "Centar Sarajevo")
				$mjesto = 139;
			else {
				$mjesto = db_get("SELECT id FROM mjesto WHERE naziv='" . trim($parts[11]) . "'");
				if (!$mjesto)
					print "GRESKA: Nepoznato mjesto " . $parts[11] . " (oid $oid)<br>\n";
			}
			$adresa = str_replace("\\\\", ",", $parts[17]);
			if ($parts[18] == "Sarajevo" && $parts[20] == "Centar" || $parts[18] == "Sarajevo" && $parts[20] == "Centar Sarajevo" ||  $parts[18] == "Centar Sarajevo")
				$adresa_mjesto = 139;
			else if ($parts[18] == "Sarajevo" && $parts[20] == "Novo Sarajevo" || $parts[18] == "Novo Sarajevo")
				$adresa_mjesto = 199;
			else if ($parts[18] == "Sarajevo" && $parts[20] == "Novi Grad" || $parts[18] == "Sarajevo" && $parts[20] == "Novi Grad Sarajevo" || $parts[18] == "Novi Grad")
				$adresa_mjesto = 206;
			else if ($parts[18] == "Sarajevo" && $parts[20] == "Stari Grad" || $parts[18] == "Sarajevo" && $parts[20] == "Stari Grad Sarajevo" || $parts[18] == "Stari Grad")
				$adresa_mjesto = 215;
			else if ($parts[18] == "Sarajevo" && $parts[20] == "Vogošća")
				$adresa_mjesto = 32;
			else if ($parts[18] == "Sarajevo" && $parts[20] == "Ilidža")
				$adresa_mjesto = 13;
			else if ($parts[18] == "Sarajevo") {
				print "GRESKA: Nepoznato Sarajevo " . trim($parts[20]) . " (oid $oid)<br>\n";
			}
			else {
				$adresa_mjesto = db_get("SELECT id FROM mjesto WHERE naziv='" . trim($parts[18]) . "'");
				if (!$adresa_mjesto)
					print "GRESKA: Nepoznato adresa mjesto " . $parts[18] . " (oid $oid)<br>\n";
			}
			$drzavljanstvo = db_get("SELECT id FROM drzava WHERE naziv LIKE '" . trim($parts[23]) . "'");
			if (!$drzavljanstvo)
				print "GRESKA: Nepoznato drzavljanstvo " . $parts[23] . " (oid $oid)<br>\n";
			$telefon = $parts[25];
			
			$brindexa = $parts[30];
			
			
			if (count($parts) > 33) {
				$zanimanje_oca = $parts[33];
				
				if ($parts[34] == "bez zaposlenja") {
					$status_aktivnosti_roditelja = 2;
					$status_zaposlenosti_roditelja = 0;
				} else if ($parts[34] == "Penzioner") {
					$status_aktivnosti_roditelja = 3;
					$status_zaposlenosti_roditelja = 0;
				} else if (!empty(trim($parts[34]))) {
					$status_aktivnosti_roditelja = 1;
					$status_zaposlenosti_roditelja = 2;
				} else {
					$status_aktivnosti_roditelja = 0;
					$status_zaposlenosti_roditelja = 0;
				}
				$ime_majke = fix_ime($parts[35]);
				if (upitnik_like($parts[0], $parts[36]))
					$prezime_majke = $prezime;
				else
					$prezime_majke = fix_ime($parts[36]);
				if ($parts[48] == "Bošnjaci")
					$nacionalnost = 1;
				else if ($parts[48] == "Srbi")
					$nacionalnost = 2;
				else if ($parts[48] == "Hrvat")
					$nacionalnost = 3;
				else if ($parts[48] == "Ostali - Bosanci")
					$nacionalnost = 9;
				else
					$nacionalnost = 6;
				$sql = "INSERT INTO osoba SET id=$id, prezime='$prezime', ime='$ime', jmbg='$jmbg', spol='$spol', imeoca='$imeoca', prezimeoca='$prezime', imemajke='$ime_majke', prezimemajke='$prezime_majke', datum_rodjenja='$datum_rodjenja', mjesto_rodjenja='$mjesto', adresa='$adresa', adresa_mjesto='$adresa_mjesto', drzavljanstvo=$drzavljanstvo, telefon='$telefon', brindexa='$brindexa', zanimanje_roditelja='$zanimanje_oca', status_aktivnosti_roditelja=$status_aktivnosti_roditelja, status_zaposlenosti_roditelja=$status_zaposlenosti_roditelja, nacionalnost=$nacionalnost, oid='$oid'";
			} else
			
			$sql = "INSERT INTO osoba SET id=$id, prezime='$prezime', ime='$ime', jmbg='$jmbg', spol='$spol', imeoca='$imeoca', prezimeoca='$prezime', datum_rodjenja='$datum_rodjenja', mjesto_rodjenja='$mjesto', adresa='$adresa', adresa_mjesto='$adresa_mjesto', drzavljanstvo=$drzavljanstvo, telefon='$telefon', brindexa='$brindexa', oid='$oid'";
			
			if ($f) db_query($sql);
			
			// Škola
			if (count($parts) > 50) {
				$ime_skole = str_replace("?", "%", $parts[51]);
				$ime_skole = str_replace("\"", "", $ime_skole);
				$skola = false;
				if ($ime_skole == "Srednja medicinska škola - Jezero Sarajevo")
					$skola = 920;
				else if ($ime_skole == "Franjevačka klasična gimnazija Visoko")
					$skola = 579;
				else
					$skola = db_get("SELECT id FROM srednja_skola WHERE naziv LIKE '$ime_skole';");
				if (!$skola)
					print "Greška: Nepoznata škola $ime_skole<br>\n";
				
				if ($skola) {
					$sql = "INSERT INTO uspjeh_u_srednjoj SET osoba=$id, srednja_skola=$skola, godina=$prethodna_godina;";
					
					if ($f) db_query($sql);
				}
			}
			
			// Login
			
			$trans = array("č"=>"c", "ć"=>"c", "đ"=>"d", "š"=>"s", "ž"=>"z", "Č"=>"C", "Ć"=>"C", "Đ"=>"D", "Š"=>"S", "Ž"=>"Z");
			$ime_login = preg_replace("/\W/", "", strtolower(strtr($ime, $trans)));
			$prezime_login = preg_replace("/\W/", "", strtolower(strtr($prezime, $trans)));
			$br = 1;
			do {
				$login = substr($ime_login,0,1).substr($prezime_login,0,9) . $br;
				$br++;
			} while(in_array($login, $svi_logini));
			
			$sql = "INSERT INTO auth SET id=$id, login='$login', aktivan=1;";
			
			$stari_login = substr($ime_login,0,1).substr($prezime_login,0,1) . $brindexa;
			
			if ($f) db_query($sql);
			if ($f) db_query("INSERT INTO privilegije SET osoba=$id, privilegija='student'");
			
			
			// Email
			
			$email = $login . "@etf.unsa.ba";
			$sql = "INSERT INTO email SET osoba=$id, adresa='$email', sistemska=1;";
			if ($f) db_query($sql);
			$email2 = $parts[26];
			if (!empty(trim($email2))) {
				$sql = "INSERT INTO email SET osoba=$id, adresa='$email2', sistemska=0;";
				if ($f) db_query($sql);
			}
			
			// Upis u prvu
			
			if ($parts[27] == "Redovni samofinansirajući studij")
				$nacin_studiranja = 3;
			else if ($parts[27] == "Redovni studij")
				$nacin_studiranja = 1;
			else {
				$nacin_studiranja = 4;
				print "GRESKA: Nepoznat način studiranja " . $parts[27] . "<br>\n";
			}
			
			if ($parts[28] == "Računarstvo i informatika")
				$studij = 2;
			else if ($parts[28] == "Elektroenergetika")
				$studij = 4;
			else if ($parts[28] == "Automatika i elektronika")
				$studij = 3;
			else if ($parts[28] == "Telekomunikacije")
				$studij = 5;
			else if ($parts[28] == "Razvoj softvera")
				$studij = 22;
			else {
				$studij = 22;
				print "GRESKA: Nepoznat studij " . $parts[28] . "<br>\n";
			}
			
			print "<tr><td>$id</td><td>$ime</td><td>$prezime</td><td>$imeoca</td><td>$login</td><td>$brindexa</td><td>$stari_login</td><td>$jmbg</td><td>$oid</td></tr>\n";
			
			if ($f) {
				$enrollment = array_to_object(["student" => ["id" => $id], "Programme" => ["id" => $studij], "semester" => 1, "AcademicYear" => ["id" => $godina ], "EnrollmentType" => ["id" => $nacin_studiranja], "repeat" => false, "status" => 0 /* Normal student */, "whichTime" => 1, "dryRun" => false]);
				$newEnrollment = api_call("enrollment/$id", $enrollment, "POST");
				if ($_api_http_code == "201") {
					foreach ($newEnrollment['enrollCourses'] as $cuy) {
						$result = api_call("course/" . $cuy['CourseUnit']['id'] . "/" . $cuy['AcademicYear']['id'] . "/enroll/$id", [], "POST");
						if ($_api_http_code != "201") {
							print "GRESKA: Neuspješan upis studenta $ime $prezime na predmet " . $cuy['courseName'] . "<br>\n";
							print_r($result);
						}
					}
				} else {
					print "GRESKA: Neuspješan upis studenta $ime $prezime na studij $studij<br>\n";
					print_r($newEnrollment);
				}
			}
			
			$id++;
			
			if ($f && $i % 50 == 0) {
				?>
					</table>
				<p>Obrađeno <?=$i?> studenata od <?=count(explode("\n", $_POST['csv'])) ?>...</p>
				<?=genform("POST")?>
				<input type="hidden" name="pocni_od" value="<?=($i+1)?>">
				<input type="hidden" name="fakatradi" value="1">
				<input type="submit" value=" Nastavak ">
				</form>
				<?
				break;
			}
		}
		
		if (!$f) {
			?>
			</table>
			<?=genform("POST")?>
			<input type="hidden" name="fakatradi" value="1">
			<input type="submit" value=" Fakat radi ">
			</form>
			<?
		} else {
			?>
			</table>
			<p>Obrađeno <?=$i?> studenata od <?=count(explode("\n", $_POST['csv'])) ?>. Kraj</p>
			<?
		}
	} else {
		?>
		<?=genform("POST")?>
		<input type="hidden" name="akcija" value="import_eunsa">
		<input type="hidden" name="fakatradi" value="0">
		CSV:<br>
		<textarea name="csv"></textarea>
		<input type="submit" value=" Uvezi podatke iz eUpis aplikacije ">
		</form>
		<?
	}
}