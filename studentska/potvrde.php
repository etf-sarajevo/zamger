<?

// STUDENTSKA/POTVRDE - zahtjevi za ovjerena uvjerenja



function studentska_potvrde() {
	
	global $user_siteadmin,$user_studentska,$conf_jasper,$conf_jasper_url,$_api_http_code;
	global $conf_broj_besplatnih_potvrda;
	
	require_once("lib/utility.php"); // spol, vokativ
	require_once("lib/formgen.php"); // datectl


	// Provjera privilegija
	
	if (!$user_studentska && !$user_siteadmin) {
		zamgerlog("nije studentska",3); // 3: error
		zamgerlog2("nije studentska"); // 3: error
		biguglyerror("Pristup nije dozvoljen.");
		return;
	}

	// Zahtjevi za dokumenta / potvrde
	
	
	if (param('akcija') == "obradi_potvrdu") {
		$id = int_param('id');
		$status = int_param('status');
		
		$certificate = api_call("certificate/$id");
		$certificate['status'] = $status;
		$certificate = array_to_object( $certificate );
		$result = api_call("certificate/$id", $certificate, "PUT");
		if ($_api_http_code == "201") {
			if ($status == 1)
				nicemessage("Zahtjev označen kao neobrađen");
			else
				nicemessage("Zahtjev obrađen");
			zamgerlog("obradjen zahtjev za potvrdu $id (status: $status)", 2);
			zamgerlog2("obradjen zahtjev za potvrdu", $id, $status);
		} else {
			niceerror("Neuspješna obrada zahtjeva");
			api_report_bug($result, $certificate);
		}
		
		?>
		<p><a href="?sta=studentska/potvrde&amp;akcija=potvrda">Nazad na spisak zahtjeva za potvrdu</a></p>
		<?php
		return;
	}
	
	if (param('akcija') == "obrisi_potvrdu") {
		$id = int_param('id');
		$result = api_call("certificate/$id", [], "DELETE");
		
		if ($_api_http_code == "204") {
			zamgerlog("izbrisan zahtjev za potvrdu $id", 2);
			zamgerlog2("izbrisan zahtjev za potvrdu", $id);
			nicemessage("Zahtjev obrisan");
		} else {
			niceerror("Neuspješno brisanje zahtjeva");
			api_report_bug($result, []);
		}
		
		?>
		<p><a href="?sta=studentska/potvrde&amp;akcija=potvrda">Nazad na spisak zahtjeva za potvrdu</a></p>
		<?php
		return;
	}
	
	if (param('akcija') == "potvrda_jasper") {
		// Request params
		if (int_param('tip') == 1)
			$reportUnit = "Potvrda";
		else if (int_param('tip') == 2)
			$reportUnit = "Prepis";
		
		if (isset($_POST['year'])) {
			$year = int_param('year');
			$month = int_param('month');
			$day = int_param('day');
			$param1 = "$year-$month-$day";
		} else {
			$id = int_param('id');
			$param1 = "$id";
		}
		
		// Generate token
		$reportToken = array_to_object( [ "token" => 0, "report" => $reportUnit, "dateTime" => 0, "param1" => $param1, "param2" => "" ] );
		$token = api_call("zamger/report_token", $reportToken, "POST")['token'];

		// Generate Jasper URI and mark requests as processed
		// Request must be processed before printing so that the date would be correct
		$reportUnit = "%2Freports%2F$reportUnit";
		if (isset($_POST['year'])) {
			$uriParams = "&zahtjev_datum=$param1&token=$token&zahtjev=0";
			api_call("certificate/processAll/$year/$month/$day", [ "type" => int_param('tip') ] );
		}
		else {
			$uriParams = "&zahtjev=$id&token=$token&zahtjev_datum=";
			$certificate = api_call("certificate/$id");
			if ($certificate['status'] != 2) {
				$certificate['status'] = 2;
				$certificate = array_to_object($certificate);
				api_call("certificate/$id", $certificate, "PUT");
			}
		}
		
		?>
		<script>window.location = '<?=$conf_jasper_url?>/flow.html?_flowId=viewReportFlow&_flowId=viewReportFlow&ParentFolderUri=%2Freports&reportUnit=<?=$reportUnit?>&standAlone=true<?=$uriParams?>&decorate=no&output=pdf';</script>
		<?
		
		return;
	}


	if (param('akcija') == "pretraga") {
		$upit = trim(param('upit'));
		?>
		<h2>Rezultati pretrage za upit: &quot;<?=htmlentities($upit)?>&quot;</h2>
		<?
		
		if (empty($upit)) {
			niceerror("Niste unijeli nikakav kriterij sa pretragu");
			print "<p><a href=\"?sta=studentska/potvrde&akcija=arhiva\">Nazad</a></p>";
			return;
		}
		
		$certificates = api_call("certificate/search", [ "query" => $upit, "resolve" => [ "Person" ] ]);
		if ($_api_http_code != 200) {
			niceerror("Neuspješno čitanje podataka o zadaćama");
			api_report_bug($certificates, []);
			return;
		}
		
		print "<ul>";
		foreach($certificates['results'] as $certificate) {
			$nicedate = date ("d. m. Y.", db_timestamp($certificate['requestedDate']));
			?>
			<li><?=$certificate['student']['surname']?> <?=$certificate['student']['name']?> (<?=$certificate['student']['studentIdNr']?>) - <?=$nicedate?> - <?= ($certificate['status'] == 2 ? "obrađen" : "nije obrađen") ?> - <a href="?sta=studentska/potvrde&akcija=potvrda_jasper&id=<?=$certificate['id']?>">printaj</a></li>
			<?
		}
		print "</ul>";
	}
	
	
	if (param('akcija') == "arhiva") {
		
		// Javascript za ajah
		?>
		<script language="JavaScript">
            function zahtjevi_dan(date) {
                console.log(date);
                var dan = date.split("-")[2];
                var mjesec = date.split("-")[1];
                var godina = date.split("-")[0];
                console.log('dan-' + godina + '-' + mjesec + '-' + dan);
                var rp = document.getElementById('dan-' + date);
                if (rp.innerHTML != "prazan") return; // Vec je ucitan
                rp.innerHTML = "<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Molimo sačekajte...";

                ajax_api_start(
                    "certificate/date/" + godina + "/" + mjesec + "/" + dan,
                    "GET",
                    { "resolve[]" : "Person" },
                    function(response) {
                        var rp = document.getElementById('dan-' + date);
                        rp.innerHTML = "";
                        var lastDate = "";
                        var zahtjevi = response.results;
                        console.log(response);
                        console.log(zahtjevi);
                        for (var id in zahtjevi) {
                            if (zahtjevi.hasOwnProperty(id)) {
                                rp.innerHTML += "<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                                rp.innerHTML += zahtjevi[id].student.surname + " " + zahtjevi[id].student.name + " (" + zahtjevi[id].student.studentIdNr + ") - <a href=\"?sta=studentska/potvrde&akcija=potvrda_jasper&id=" + zahtjevi[id].id + "&tip=" + zahtjevi[id].CertificateType + "\">printaj</a>\n";
                            }
                        }
                        if (rp.innerHTML == "") rp.innerHTML += "<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nema rezultata";
                    }
                );
            }
            function zahtjevi_mjesec(mjesec, godina) {
                var rp = document.getElementById('mjesec-' + mjesec + '-' + godina);
                if (rp.innerHTML != "prazan") return; // Vec je ucitan
                rp.innerHTML = "<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Molimo sačekajte...";

                ajax_api_start(
                    "certificate/date/" + godina + "/" + mjesec ,
                    "GET",
                    {  },
                    function(response) {
                        var rp = document.getElementById('mjesec-' + mjesec + '-' + godina);
                        rp.innerHTML="";
                        var lastDate = "", count=0;
                        var zahtjevi = response.results;
                        console.log(response);
                        console.log(zahtjevi);
                        for (var id in zahtjevi) {
                            if (zahtjevi.hasOwnProperty(id)) {
                                //console.log(zahtjevi[id]);
                                var date = zahtjevi[id].requestedDate.split(" ")[0];
                                if (date != lastDate) {
                                    if (lastDate != "") {
                                        var parts = lastDate.split("-");
                                        var nicedate = parts[2] + ". " + parts[1] + ". " + parts[0];
                                        rp.innerHTML += '<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="static/images/plus.png" width="13" height="13" id="img-dan-' + lastDate + '" onclick="daj_stablo(\'dan-' + lastDate + '\'); zahtjevi_dan(\'' + lastDate + '\')"> ' + nicedate + ' (' + count + ' zahtjeva) <div id="dan-' + lastDate + '" style="display:none">prazan</div>';
                                    }
                                    lastDate = date;
                                    count = 1;
                                } else count ++;
                            }
                        }
                        if (count == 0)
                            rp.innerHTML += "<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nema rezultata";
                        else {
                            var parts = lastDate.split("-");
                            var nicedate = parts[2] + ". " + parts[1] + ". " + parts[0];
                            rp.innerHTML += '<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="static/images/plus.png" width="13" height="13" id="img-dan-' + lastDate + '" onclick="daj_stablo(\'dan-' + lastDate + '\'); zahtjevi_dan(\'' + lastDate + '\')"> ' + nicedate + ' (' + count + ' zahtjeva) <div id="dan-' + lastDate + '" style="display:none">prazan</div>';
                        }
                    }
                );
            }
		</script>
		
		<h2>Arhiva zahtjeva za potvrdu</h2>
		
		<p><a href="?sta=studentska/potvrde&akcija=potvrda">Povratak na spisak neobrađenih potvrda</a></p>
		
		<center><?=genform("POST");?>
		<input type="hidden" name="akcija" value="pretraga">
			Pretražite zahtjeve po studentu:<br> <input type="text" name="upit" size="50"> <input type="submit" value="Traži">
		</form>
		</center>
		
		<?
		
		
		$imena_mjeseci = [ "", "Januar", "Februar", "Mart", "April", "Maj", "Juni", "Juli", "Avgust", "Septembar", "Oktobar", "Novembar", "Decembar"];
		for ($godina=date('Y'); $godina>=2017; $godina--) {
			print "<br/>";
			print dajplus("godina-$godina","$godina godina");
			if ($godina == date('Y')) $start = intval(date('m')); else $start = 12;
			for ($mjesec=$start; $mjesec >= 1; $mjesec--) {
				?>
				<br/>&nbsp;&nbsp;&nbsp;&nbsp;
				<img src="static/images/plus.png" width="13" height="13" id="img-mjesec-<?=$mjesec?>-<?=$godina?>" onclick="daj_stablo('mjesec-<?=$mjesec?>-<?=$godina?>'); zahtjevi_mjesec(<?=$mjesec?>, <?=$godina?>);"> <?=$imena_mjeseci[$mjesec]?> <div id="mjesec-<?=$mjesec?>-<?=$godina?>" style="display:none">prazan</div>
				<?
			}
			print "</div>\n";
		}
		
		print ajax_box();
		return;
	}
	
	
	if (param('akcija') == "potvrda") {
		
		if (param('sort') == "prezime") {
			$sortOrder = "surname"; $sortDescending = false;
			$link1 = "prezime_desc";
			$link2 = "brindexa";
			$link3 = "datum";
		} else if (param('sort') == "prezime_desc") {
			$sortOrder = "surname"; $sortDescending = true;
			$link1 = "prezime";
			$link2 = "brindexa";
			$link3 = "datum";
		} else if (param('sort') == "datum")  {
			$sortOrder = "date"; $sortDescending = false;
			$link1 = "prezime";
			$link2 = "brindexa";
			$link3 = "datum_desc";
		} else if (param('sort') == "datum_desc") {
			$sortOrder = "date"; $sortDescending = true;
			$link1 = "prezime";
			$link2 = "brindexa";
			$link3 = "datum";
		} else if (param('sort') == "brindexa")  {
			$sortOrder = "studentid"; $sortDescending = false;
			$link1 = "prezime";
			$link2 = "brindexa_desc";
			$link3 = "datum";
		} else if (param('sort') == "brindexa_desc") {
			$sortOrder = "studentid"; $sortDescending = true;
			$link1 = "prezime";
			$link2 = "brindexa";
			$link3 = "datum";
		} else { // Default
			$sortOrder = "date"; $sortDescending = false;
			$link1 = "prezime";
			$link2 = "brindexa";
			$link3 = "datum_desc";
		}
		
		?>
		<h3>Zahtjevi za potvrde i uvjerenja</h3>
		<p>=&gt; <a href="?sta=studentska/potvrde&akcija=arhiva">Arhiva zathjeva</a></p>
		<?
		
		$cpt = api_call("certificate/purposesTypes");
		
		if ($conf_jasper) {
			?>
			<?=genform("POST");?>
			<input type="hidden" name="akcija" value="potvrda_jasper">
			<p>Obradi sve zahtjeve na datum: <?=datectrl(date('d'), date('m'), date('Y'))?>
				<select name="tip">
					<?
					foreach($cpt['types'] as $id => $name) {
						?><option value="<?=$id?>"><?=$name?></option><?
					}
					?>
				</select>
				<input type="submit" value=" Obradi "></p>
			</form>
			<?php
		}
		
		$certificates = api_call("certificate/unprocessed", [ "resolve" => [ "Person", "ExtendedPerson" ], "sortOrder" => $sortOrder, "sortDescending" => $sortDescending ])['results'];
		
		?>
		<p><b>Neobrađeni zahtjevi</b></p>
		<table border="1" cellspacing="0" cellpadding="2">
			<tr>
				<th>R.br.</th><th><a href="?sta=studentska/potvrde&akcija=potvrda&sort=<?=$link1?>">Prezime i ime studenta</a></th><th><a href="?sta=studentska/potvrde&akcija=potvrda&sort=<?=$link2?>">Broj indeksa</a></th><th>Tip zahtjeva</th><th><a href="?sta=studentska/potvrde&akcija=potvrda&sort=<?=$link3?>">Datum</a></th><th>Plaćanje</th><th>Opcije</th>
			</tr>
			<?
			
			//$q200 = db_query("SELECT zzp.id, o.ime, o.prezime, tp.id, tp.naziv, UNIX_TIMESTAMP(zzp.datum_zahtjeva), o.id, zzp.svrha_potvrde, o.brindexa, zzp.akademska_godina, zzp.besplatna FROM zahtjev_za_potvrdu as zzp, osoba as o, tip_potvrde as tp WHERE zzp.student=o.id AND zzp.tip_potvrde=tp.id AND zzp.status=1 $order_by");
			$rbr = 1;
			//while ($r200 = db_fetch_row($q200)) {
			foreach($certificates as $certificate) {
				if ($conf_jasper)
					$printUrl = "?sta=studentska/potvrde&amp;akcija=potvrda_jasper&amp;tip=" . $certificate['CertificateType'] . "&amp;id=" . $certificate['id'];
				else if ($certificate['CertificateType'] == 1)
					$printUrl = "?sta=izvjestaj/potvrda&amp;student=" . $certificate['student']['id'] . "&amp;svrha=" . $certificate['CertificatePurpose'] . "&amp;ag=" . $certificate['AcademicYear']['id'];
				else
					$printUrl = "?sta=izvjestaj/index2&amp;student=" . $certificate['student']['id'];
				
				$certPayIcon = "&nbsp;";
				// $conf_broj_besplatnih_potvrda==0 means all is free
				if ($conf_broj_besplatnih_potvrda > 0 && !$certificate['free'])
					$certPayIcon = "<img src=\"static/images/32x32/markica.jpg\" width=\"30\" height=\"30\">";
				
				// Perform checks on student
				$errors = [];
				api_call("enrollment/current/" . $certificate['student']['id']);
				if ($_api_http_code == "404") {
					$errors[] = "trenutno nije upisan na studij!";
				} else if ($certificate['CertificateType'] == 1) {
					$thesis = api_call("thesis/forStudent/" . $certificate['student']['id'], [ "year" => $certificate['AcademicYear']['id'] ]);
					if ($_api_http_code != "404") {
						$portfolio = api_call("course/" . $thesis['CourseUnit']['id'] . "/student/" . $certificate['student']['id'], [ "score" => true ] );
						if ($portfolio['grade'] > 5)
							$errors[] = "student odbranio završni rad";
					}
				}
				
				if ($certificate['student']['ExtendedPerson']['placeOfBirth']['id'] == 0) {
					$errors[] = "nedostaje mjesto rođenja";
				}
				if ($certificate['student']['ExtendedPerson']['dateOfBirth'] == '0000-00-00') {
					$errors[] = "nedostaje datum rođenja";
				}
				
				if ($certificate['student']['ExtendedPerson']['jmbg'] == "") {
					$errors[] = "nedostaje JMBG";
				}
				
				?>
				<tr>
					<td><?=$rbr?></td>
					<td><?=$certificate['student']['surname']?> <?=$certificate['student']['name']?></td>
					<td><?=$certificate['student']['studentIdNr']?></td>
					<td><?=$cpt['types'][$certificate['CertificateType']]?></td>
					<td><?=date("d.m.Y. H:i:s", db_timestamp($certificate['requestedDate']))?></td>
					<td><?=$certPayIcon?></td>
					<td><a href="<?=$printUrl?>" target="_blank">printaj</a> * <a href="?sta=studentska/potvrde&akcija=obradi_potvrdu&id=<?=$certificate['id']?>&status=2">obradi</a>
					<?
					foreach($errors as $error) print " - <font color=\"red\">$error</font>";
					if (count($errors) > 0) {
						?> <a href="?sta=studentska/osobe&akcija=edit&osoba=<?=$certificate['student']['id']?>">popravi</a><?
					}
					?></td>
				</tr>
				<?
				$rbr++;
			}
			
			?>
		</table>
		<?
		return;
	}
	
	// Početna stranica
	
	$certificates = api_call("certificate/unprocessed");
	$br_zahtjeva = count($certificates['results']);
	if ($br_zahtjeva > 0)
		print "<p><a href=\"?sta=studentska/potvrde&akcija=potvrda\">Imate $br_zahtjeva neobrađenih zahtjeva za dokumenta.</a></p>";
	else
		print "<p>Nema neobrađenih zahtjeva za dokumenta.</p>";
	
	
}

function dajplus($layerid,$layername) {
	return "<img src=\"static/images/plus.png\" width=\"13\" height=\"13\" id=\"img-$layerid\" onclick=\"daj_stablo('$layerid')\"> $layername <div id=\"$layerid\" style=\"display:none\">";
}

?>
