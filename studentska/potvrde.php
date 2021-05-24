<?

// STUDENTSKA/POTVRDE - zahtjevi za ovjerena uvjerenja



function studentska_potvrde() {
	
	global $userid,$user_siteadmin,$user_studentska,$conf_files_path,$conf_jasper,$conf_jasper_url,$_api_http_code;
	
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
		$id = intval($_GET['id']);
		$status = intval($_GET['status']);
		$q210 = db_query("UPDATE zahtjev_za_potvrdu SET status=$status WHERE id=$id");
		zamgerlog("obradjen zahtjev za potvrdu $id (status: $status)", 2);
		zamgerlog2("obradjen zahtjev za potvrdu", $id, $status);
		
		if ($status == 1) {
			nicemessage("Zahtjev označen kao neobrađen");
			?>
			<p><a href="?sta=studentska/potvrde&amp;akcija=potvrda">Nazad na spisak zahtjeva za potvrdu</a></p>
			<?php
			return;
		}
		
		nicemessage("Zahtjev obrađen");
		
		// Poruka korisniku
		$q215 = db_query("SELECT UNIX_TIMESTAMP(datum_zahtjeva), student FROM zahtjev_za_potvrdu WHERE id=$id");
		$vrijeme_zahtjeva = db_result($q215,0,0);
		$student = db_result($q215,0,1);
		$tekst_poruke = "Na dan ".date("d. m. Y.", $vrijeme_zahtjeva).", u ".date("H:i:s", $vrijeme_zahtjeva)." poslali ste zahtjev za ovjereno uvjerenje ili potvrdu o redovnom studiju. Vaše uvjerenje je spremno i možete ga preuzeti u studentskoj službi.";
		$q310 = db_query("insert into poruka set tip=2, opseg=7, primalac=$student, posiljalac=$userid, vrijeme=NOW(), ref=0, naslov='Vaša potvrda/uvjerenje je spremno', tekst='$tekst_poruke'");
		
		// Slanje GCM poruke
		require("gcm/push_message.php");
		push_message(array($student), "Potvrde", "Vaša potvrda/uvjerenje je spremno");
		$_GET['akcija'] = "potvrda";
	}
	
	if (param('akcija') == "obrisi_potvrdu") {
		$id = intval($_GET['id']);
		$q210 = db_query("DELETE FROM zahtjev_za_potvrdu WHERE id=$id");
		zamgerlog("obrisan zahtjev za potvrdu $id", 2);
		zamgerlog2("obrisan zahtjev za potvrdu", $id);
		
		nicemessage("Zahtjev obrisan");
		
		$_GET['akcija'] = "potvrda";
	}
	
	if (param('akcija') == "potvrda_jasper") {
		$id = 0;
		if (isset($_POST['year'])) {
			$year = int_param('year');
			$month = int_param('month');
			$day = int_param('day');
			$date = "$year-$month-$day";
		}
		else
			$id = int_param('id');
		$param2 = "''";
		$token = rand(100000, 999999);
		
		$reportUnit = "%2Freports%2FPotvrda";
		
		// Utvrđujemo koji put ponavlja
		if ($id > 0) {
			$q220 = db_query("SELECT ss.status_studenta, ss.semestar, ts.ciklus, zzp.student, zzp.svrha_potvrde FROM student_studij ss, zahtjev_za_potvrdu zzp, studij s, tipstudija ts WHERE zzp.id=$id AND zzp.student=ss.student AND zzp.akademska_godina=ss.akademska_godina AND ss.studij=s.id AND s.tipstudija=ts.id ORDER BY ss.semestar DESC LIMIT 1");
			if (db_num_rows($q220) == 0) {
				$naziv_ag = db_get("SELECT ag.naziv FROM akademska_godina ag, zahtjev_za_potvrdu zzp WHERE zzp.id=$id AND zzp.akademska_godina=ag.id");
				niceerror("Student nije upisan na studij u akademskoj $naziv_ag godini");
				?>
				<p>Student je popunio zahtjev za akademsku <b><?=$naziv_ag?></b>, ali ne postoji evidencija da je bio upisan na studij u toj godini.</p>
				<p>Ako je ovo greška, <a href="?sta=studentska/osobe&amp;akcija=edit&amp;osoba=<?=db_get("SELECT student FROM zahtjev_za_potvrdu where id=$id");?>">kliknite ovdje da otvorite profil studenta</a>.</p>
				<p><a href="?sta=studentska/potvrde&amp;akcija=potvrda">Nazad na spisak zahtjeva za potvrdu</a></p>
				<?
				return;
			}
			db_fetch5($q220, $status_studenta, $semestar, $ciklus, $student, $svrha);
			if ($status_studenta == 1)
				$put = 1; // Ako je apsolvent, sigurno je prvi put
			else
				$put = db_get("SELECT COUNT(*)+1 FROM student_studij ss, zahtjev_za_potvrdu zzp, studij s, tipstudija ts WHERE zzp.id=$id AND zzp.student=ss.student AND zzp.akademska_godina>ss.akademska_godina AND ss.semestar=$semestar AND ss.status_studenta!=1 AND ss.studij=s.id AND s.tipstudija=ts.id AND ts.ciklus=$ciklus");
			
			$uriParams = "&zahtjev=$id&token=$token&put=$put";
		} else
			$uriParams = "&zahtjev_datum=$date&token=$token&put=$put";
		
		db_query("DELETE FROM jasper_token WHERE NOW()-vrijeme>1500");
		db_query("INSERT INTO jasper_token SET token=$token, report='Potvrda', vrijeme=NOW(), param1=$id, param2=$param2");
		
		?>
		<script>window.location = '<?=$conf_jasper_url?>/flow.html?_flowId=viewReportFlow&_flowId=viewReportFlow&ParentFolderUri=%2Freports&reportUnit=<?=$reportUnit?>&standAlone=true<?=$uriParams?>&decorate=no&output=pdf';</script>
		<?
		
		// Označi zahtjev kao obrađen
		if ($id > 0)
			$q200 = db_query("SELECT id, status FROM zahtjev_za_potvrdu WHERE student=$student AND svrha_potvrde=$svrha");
		else
			$q200 = db_query("SELECT id, status FROM zahtjev_za_potvrdu WHERE datum_zahtjeva>='$date 00:00:00' AND datum_zahtjeva<='$date 23:59:59'");
		while ($r200 = db_fetch_row($q200)) {
			if ($r200[1] == 1)
				db_query("UPDATE zahtjev_za_potvrdu SET status=2 WHERE id=$r200[0]");
		}
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
			$nicedate = date ("d. m. Y.", db_timestamp($certificate['datetime']));
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
                                rp.innerHTML += zahtjevi[id].student.surname + " " + zahtjevi[id].student.name + " (" + zahtjevi[id].student.studentIdNr + ") - <a href=\"?sta=studentska/potvrde&akcija=potvrda_jasper&id=" + zahtjevi[id].id + "\">printaj</a>\n";
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
                                var date = zahtjevi[id].datetime.split(" ")[0];
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
				print "<br/>&nbsp;&nbsp;&nbsp;&nbsp;";
				print "<img src=\"static/images/plus.png\" width=\"13\" height=\"13\" id=\"img-mjesec-$mjesec-$godina\" onclick=\"daj_stablo('mjesec-$mjesec-$godina'); zahtjevi_mjesec($mjesec, $godina);\"> " . $imena_mjeseci[$mjesec] . " <div id=\"mjesec-$mjesec-$godina\" style=\"display:none\">prazan</div>";
			}
			print "</div>\n";
		}
		
		print ajax_box();
		return;
	}
	
	
	if (param('akcija') == "potvrda") {
		
		if (param('sort') == "prezime") {
			$order_by = "ORDER BY o.prezime, o.ime";
			$link1 = "prezime_desc";
			$link2 = "brindexa";
			$link3 = "datum";
		} else if (param('sort') == "prezime_desc") {
			$order_by = "ORDER BY o.prezime DESC, o.ime DESC";
			$link1 = "prezime";
			$link2 = "brindexa";
			$link3 = "datum";
		} else if (param('sort') == "datum")  {
			$order_by = "ORDER BY zzp.datum_zahtjeva";
			$link1 = "prezime";
			$link2 = "brindexa";
			$link3 = "datum_desc";
		} else if (param('sort') == "datum_desc") {
			$order_by = "ORDER BY zzp.datum_zahtjeva DESC";
			$link1 = "prezime";
			$link2 = "brindexa";
			$link3 = "datum";
		} else if (param('sort') == "brindexa")  {
			$order_by = "ORDER BY o.brindexa";
			$link1 = "prezime";
			$link2 = "brindexa_desc";
			$link3 = "datum";
		} else if (param('sort') == "brindexa_desc") {
			$order_by = "ORDER BY o.brindexa DESC";
			$link1 = "prezime";
			$link2 = "brindexa";
			$link3 = "datum";
		} else { // Default
			$order_by = "ORDER BY zzp.datum_zahtjeva";
			$link1 = "prezime";
			$link2 = "brindexa";
			$link3 = "datum_desc";
		}
		
		if ($conf_jasper) {
			?>
			<?=genform("POST");?>
			<input type="hidden" name="akcija" value="potvrda_jasper">
			<p>Obradi sve potvrde na datum: <?=datectrl(date('d'), date('m'), date('Y'))?> <input type="submit" value=" Obradi "></p>
			</form>
			<?php
		}
		
		?>
		<p><b>Neobrađeni zahtjevi</b></p>
		<table border="1" cellspacing="0" cellpadding="2">
			<tr>
				<th>R.br.</th><th><a href="?sta=studentska/potvrde&akcija=potvrda&sort=<?=$link1?>">Prezime i ime studenta</a></th><th><a href="?sta=studentska/potvrde&akcija=potvrda&sort=<?=$link2?>">Broj indeksa</a></th><th>Tip zahtjeva</th><th><a href="?sta=studentska/potvrde&akcija=potvrda&sort=<?=$link3?>">Datum</a></th><th>Plaćanje</th><th>Opcije</th>
			</tr>
			<?
			
			$q200 = db_query("SELECT zzp.id, o.ime, o.prezime, tp.id, tp.naziv, UNIX_TIMESTAMP(zzp.datum_zahtjeva), o.id, zzp.svrha_potvrde, o.brindexa, zzp.akademska_godina, zzp.besplatna FROM zahtjev_za_potvrdu as zzp, osoba as o, tip_potvrde as tp WHERE zzp.student=o.id AND zzp.tip_potvrde=tp.id AND zzp.status=1 $order_by");
			$rbr = 1;
			while ($r200 = db_fetch_row($q200)) {
				$ag = $r200[9];
				
				if ($r200[3] == 1 && $conf_jasper)
					$link_printanje = "?sta=studentska/potvrde&amp;akcija=potvrda_jasper&amp;id=$r200[0]";
				else if ($r200[3] == 1)
					$link_printanje = "?sta=izvjestaj/potvrda&amp;student=$r200[6]&amp;svrha=$r200[7]&amp;ag=$ag";
				else
					$link_printanje = "?sta=izvjestaj/index2&amp;student=$r200[6]";
				
				print "<tr><td>$rbr</td><td>$r200[2] $r200[1]</td><td>$r200[8]</td><td>$r200[4]</td><td>".date("d.m.Y. H:i:s", $r200[5])."</td>";
				
				if ($r200[10] == 1 || $conf_broj_besplatnih_potvrda == 0) print "<td>&nbsp;</td>"; else print "<td><img src=\"static/images/32x32/markica.jpg\" width=\"30\" height=\"30\"></td>";
				print "<td><a href=\"$link_printanje\" target=\"_blank\">printaj</a> * <a href=\"?sta=studentska/potvrde&akcija=obradi_potvrdu&id=$r200[0]&status=2\">obradi</a>";
				
				// Dodatne kontrole
				$error = 0;
				$q210 = db_query("SELECT count(*) FROM student_studij AS ss WHERE ss.student=$r200[6] AND ss.akademska_godina=$ag");
				if (db_result($q210,0,0) == 0) {
					print " - <font color=\"red\">trenutno nije upisan na studij!</font>"; $error=1;
				} else {
					$zavrsni = db_get("SELECT COUNT(*) FROM konacna_ocjena ko, akademska_godina_predmet agp WHERE ko.student=$r200[6] AND ko.akademska_godina=$ag AND ko.ocjena>5 AND ko.predmet=agp.predmet AND agp.akademska_godina=$ag AND (agp.tippredmeta=1000 OR agp.tippredmeta=1001)");
					if ($zavrsni > 0 && $r200[3] == 1) {
						print " - <font color=\"red\">student odbranio završni rad</font>"; $error=1;
					}
				}
				
				$q220 = db_query("SELECT mjesto_rodjenja, datum_rodjenja, jmbg FROM osoba WHERE id=$r200[6]");
				if (db_result($q220,0,0) == 0) {
					print " - <font color=\"red\">nedostaje mjesto rođenja</font>"; $error=1;
				}
				if (db_result($q220,0,1) == '0000-00-00') {
					print " - <font color=\"red\">nedostaje datum rođenja</font>"; $error=1;
				}
				
				if (db_result($q220,0,2) == "") {
					print " - <font color=\"red\">nedostaje JMBG</font>"; $error=1;
				}
				if ($error == 1)
					print " <a href=\"?sta=studentska/osobe&akcija=edit&osoba=$r200[6]\">popravi</a>";
				print "</td></tr>\n";
				$rbr++;
			}
			
			?>
		</table>
		<p><b>Obrađeni zahtjevi</b></p>
		<?
		if (param('subakcija') == "arhiva") {
			?>
			<p><a href="?sta=studentska/potvrde&akcija=potvrda">Sakrij zahtjeve starije od mjesec dana</a></p>
			<?
		} else {
			?>
			<p><a href="?sta=studentska/potvrde&akcija=arhiva">Prikaži zahtjeve starije od mjesec dana</a></p>
			<?
		}
		?>
		<table border="1" cellspacing="0" cellpadding="2">
			<tr>
				<th>R.br.</th><th><a href="?sta=studentska/potvrde&akcija=potvrda&sort=<?=$link1?>">Prezime i ime studenta</a></th><th><a href="?sta=studentska/potvrde&akcija=potvrda&sort=<?=$link2?>">Broj indeksa</a></th><th>Tip zahtjeva</th><th><a href="?sta=studentska/potvrde&akcija=potvrda&sort=<?=$link3?>">Datum</a></th><th>Opcije</th>
			</tr>
			<?
			
			/*if (param('subakcija') == "arhiva") $arhiva = "";
			else*/ $arhiva = "AND zzp.datum_zahtjeva > DATE_SUB(NOW(), INTERVAL 1 MONTH)";
			
			$q200 = db_query("SELECT zzp.id, o.ime, o.prezime, tp.id, tp.naziv, UNIX_TIMESTAMP(zzp.datum_zahtjeva), o.id, zzp.svrha_potvrde, o.brindexa, zzp.akademska_godina FROM zahtjev_za_potvrdu as zzp, osoba as o, tip_potvrde as tp WHERE zzp.student=o.id AND zzp.tip_potvrde=tp.id AND zzp.status=2 $arhiva $order_by");
			$rbr = 1;
			while ($r200 = db_fetch_row($q200)) {
				$ag = $r200[9];
				
				if ($r200[3] == 1 && $conf_jasper)
					$link_printanje = "?sta=studentska/potvrde&amp;akcija=potvrda_jasper&amp;id=$r200[0]";
				else if ($r200[3] == 1)
					$link_printanje = "?sta=izvjestaj/potvrda&amp;student=$r200[6]&amp;svrha=$r200[7]&amp;ag=$ag";
				else
					$link_printanje = "?sta=izvjestaj/index2&amp;student=$r200[6]";
				
				print "<tr><td>$rbr</td><td>$r200[2] $r200[1]</td><td>$r200[8]</td><td>$r200[4]</td><td>".date("d.m.Y. H:i:s", $r200[5])."</td><td><a href=\"$link_printanje\" target=\"_blank\">printaj</a> * <a href=\"?sta=studentska/potvrde&akcija=obradi_potvrdu&id=$r200[0]&status=1\">postavi kao neobrađen</a> * <a href=\"?sta=studentska/potvrde&akcija=obrisi_potvrdu&id=$r200[0]\">obriši</a></td></tr>\n";
				$rbr++;
			}
			
			?>
		</table>
		<?
		return;
	}
	
	// Početna stranica
	
	$q40 = db_query("SELECT count(*) FROM zahtjev_za_potvrdu WHERE status=1");
	$br_zahtjeva = db_result($q40, 0, 0);
	if ($br_zahtjeva > 0)
		print "<p><a href=\"?sta=studentska/potvrde&akcija=potvrda\">Imate $br_zahtjeva neobrađenih zahtjeva za dokumenta.</a></p>";
	else
		print "<p>Nema neobrađenih zahtjeva za dokumenta.</p>";
	
	
}

function dajplus($layerid,$layername) {
	return "<img src=\"static/images/plus.png\" width=\"13\" height=\"13\" id=\"img-$layerid\" onclick=\"daj_stablo('$layerid')\"> $layername <div id=\"$layerid\" style=\"display:none\">";
}

?>
