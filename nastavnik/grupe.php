<?

// NASTAVNIK/GRUPE - administracija grupa



// FIXME: moguce kreirati vise grupa sa istim imenom


function nastavnik_grupe() {
	
	global $userid, $_api_http_code;
	
	global $mass_rezultat; // za masovni unos studenata u grupe
	
	// Parametri
	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);
	
	
	$course = api_call("course/$predmet/$ag");
	
	// Naziv predmeta
	$predmet_naziv = $course['courseName'];
	
	// Da li korisnik ima pravo ući u modul?
	
	if ($_api_http_code == "403") {
		zamgerlog("nastavnik/grupe privilegije (predmet pp$predmet)",3);
		zamgerlog2("nije nastavnik na predmetu", $predmet, $ag);
		biguglyerror("Nemate pravo pristupa ovoj opciji");
		return;
	}
	
	
	
	?>
	
	<p>&nbsp;</p>
	
	<h3><?=$predmet_naziv?> - Grupe</h3>
	
	<?
	
	
	###############
	# Akcije
	###############
	
	
	// Dodaj grupu
	
	if ($_POST['akcija'] == "nova_grupa" && check_csrf_token()) {
		$group = array_to_object( ["id" => 0, "name" => $_POST['ime'], "type" => $_POST['tip'], "CourseUnit" => ["id" => $predmet], "AcademicYear" => ["id" => $ag], "virtual" => false] );
		$result = api_call("group/course/$predmet/$ag", $group, "POST");
		if ($_api_http_code == "201") {
			zamgerlog2("kreirana labgrupa", db_insert_id(), $predmet, $ag, $_POST['ime']);
			zamgerlog("dodana nova labgrupa '".$_POST['ime']."' (predmet pp$predmet godina ag$ag)", 4); // nivo 4: audit
		} else {
			niceerror("Neuspješno dodavanje grupe: " . $result['message']);
		}
	}
	
	
	// Obrisi grupu
	
	if ($_POST['akcija'] == "obrisi_grupu" && check_csrf_token()) {
		$groupId = intval($_POST['grupaid']);
		api_call("group/$groupId", [], "DELETE");
		if ($_api_http_code == "204") {
			zamgerlog("obrisana labgrupa $groupId (predmet pp$predmet)",4); // nivo 4: audit
			zamgerlog2("obrisana labgrupa", intval($predmet), $ag, $groupId);
		} else {
			niceerror("Neuspješno brisanje grupe: kod $_api_http_code");
		}
	}
	
	
	// Promjena imena grupe
	
	if ($_POST['akcija'] == "preimenuj_grupu" && check_csrf_token()) {
		$groupId = intval($_POST['grupaid']);
		$group = array_to_object( ["id" => $groupId, "name" => $_POST['ime'], "type" => $_POST['tip'], "CourseUnit" => ["id" => $predmet], "AcademicYear" => ["id" => $ag], "virtual" => false] );
		
		$result = api_call("group/$groupId", $group, "PUT");
		if ($_api_http_code == "201") {
			zamgerlog("preimenovana labgrupa $groupId u '".$_POST['ime']."' (predmet pp$predmet godina ag$ag)",2); // nivo 2: edit
			zamgerlog2("preimenovana labgrupa", $groupId, 0, 0, $_POST['ime']);
		} else {
			niceerror("Neuspješna promjena grupe: " . $result['message']);
		}
	
		// Grupa treba ostati otvorena:
		$_GET['akcija']="studenti_grupa";
		$_GET['grupaid']=$groupId;
	}
	
	
	// Kopiraj grupe
	
	if ($_POST['akcija'] == "kopiraj_grupe" && check_csrf_token()) {
		$kopiraj = intval($_POST['kopiraj']);
		if ($kopiraj == $predmet) {
			zamgerlog("kopiranje sa istog predmeta pp$predmet",3);
			zamgerlog2("kopiranje grupa sa istog predmeta", $predmet, $ag);
			niceerror("Ne možete kopirati grupe sa istog predmeta.");
			return;
		}
	
		$result = api_call("group/course/$predmet/$ag/copy", [ "fromCourse" => $kopiraj ], "POST");
		if ($_api_http_code == "201") {
			zamgerlog("prekopirane labgrupe sa predmeta pp$kopiraj u pp$predmet",4);
			zamgerlog2("prekopirane labgrupe", $kopiraj, $ag);
		} else {
			niceerror("Neuspješno kopiranje grupa: " . $result['message']);
		}
	}
	
	
	$groups = api_call("group/course/$predmet", [ "year" => $ag ] )["results"];
	
	// Masovni unos studenata u grupe
	if ($_POST['akcija'] == "massinput" && strlen($_POST['nazad'])<1 && check_csrf_token()) {
	
		if ($_POST['fakatradi'] != 1) $ispis=1; else $ispis=0;
	
		if ($ispis) {
			?>Akcije koje će biti urađene:<br/><br/>
			<?=genform("POST")?>
			<input type="hidden" name="fakatradi" value="1">
			<table border="0" cellspacing="1" cellpadding="2">
			<!-- FIXME: prebaciti stilove u CSS? -->
			<thead>
			<tr bgcolor="#999999">
				<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;color:white;">Prezime</font></td>
				<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;color:white;">Ime</font></td>
				<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;color:white;">Broj indeksa</font></td>
				<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;color:white;">Akcije</font></td>
			</tr>
			</thead>
			<tbody>
			<?
		}
	
		$greska=mass_input($ispis); // Funkcija koja parsira podatke
	
		// Cache IDova grupa prema imenu
		$idovi_grupa=array();
	
	
		// Spisak studenata
	
		$boja1 = "#EEEEEE";
		$boja2 = "#DDDDDD";
		$boja=$boja1;
		$bojae = "#FFE3DD";
	
		foreach ($mass_rezultat['ime'] as $student=>$ime) {
			$prezime = $mass_rezultat['prezime'][$student];
			$brindexa = $mass_rezultat['brindexa'][$student];
	
			// Ispis studenta iz svih grupa
			$ispisispis = "";
			$ispis_grupe=$upis_grupe=array();
			$studentGroups = api_call("group/course/$predmet/student/$student", ["year" => $ag])["results"];
			$found=1;
			foreach($studentGroups as $group) {
				if ($group['virtual']) continue;
				$ispis_grupe[$group['id']] = $group['name'];
				if (!in_array($group['name'], $mass_rezultat['podatak1'][$student])) $found=0;
			}
	
			if ($found==1 && count($groups)>0) {
				if ($ispis) {
					?>
					<tr bgcolor="<?=$boja?>">
						<td><?=$prezime?></td><td><?=$ime?></td><td><?=$brindexa?></td>
						<td>Već upisan u grupu <?
						foreach ($ispis_grupe as $gid => $gime) print "'$gime' ";
						?> - preskačem</td>
					</tr>
					<?
					if ($boja==$boja1) $boja=$boja2; else $boja=$boja1;
				}
				continue;
			}
	
			// spisak grupa u koje treba upisati studenta
			foreach ($mass_rezultat['podatak1'][$student] as $imegrupe) {
				$imegrupe = trim($imegrupe);
				if ($imegrupe == "") continue;
	
				// Da li grupa postoji u cache-u ?
				if (array_key_exists($imegrupe,$idovi_grupa)) {
					$labgrupa=$idovi_grupa[$imegrupe];
	
				// Ne postoji, tražimo u bazi
				} else {
					// Da li je ime ispravno?
					if (!preg_match("/\w/", $imegrupe)) {
						?>
						<tr bgcolor="<?=$bojae?>">
							<td><?=$prezime?></td><td><?=$ime?></td><td><?=$brindexa?></td>
							<td>neispravno ime grupe '<?=$imegrupe?>'</td>
						</tr>
						<?
						$greska=1;
						continue;
					}
	
					// Određujemo ID grupe
					$foundGroup = false;
					foreach ($groups as $group) {
						if ($group['name'] == $imegrupe)
							$foundGroup = $group;
					}
					if (!$foundGroup) {
						// Grupa ne postoji - kreiramo je
						if ($ispis) {
							?>
							<tr bgcolor="<?=$boja?>">
								<td colspan="4">Kreiranje nove grupe '<?=$imegrupe?>'</td>
							</tr>
							<?
							if ($boja==$boja1) $boja=$boja2; else $boja=$boja1;
						} else {
							$group = array_to_object( ["id" => 0, "name" => $imegrupe, "type" => 'vjezbe+tutorijali', "CourseUnit" => ["id" => $predmet], "AcademicYear" => ["id" => $ag], "virtual" => false] );
							$foundGroup = api_call("group/course/$predmet/$ag", $group, "POST");
							if ($_api_http_code != "201") {
								niceerror("Neuspješno kreiranje grupe: " . $foundGroup['message']);
								return;
							}
							$labgrupa = $foundGroup['id'];
							zamgerlog2("kreirana labgrupa (masovni unos)", intval($labgrupa), 0, 0, $imegrupe);
						}
					} else {
						$labgrupa = $foundGroup['id'];
					}
	
					$idovi_grupa[$imegrupe] = $foundGroup['id'];
				}
	
				// Da li je grupa već jednom spomenuta?
				foreach ($upis_grupe as $gid => $gime) {
					if ($gid==$labgrupa) {
						if ($ispis) {
							?>
							<tr bgcolor="<?=$bojae?>">
								<td><?=$prezime?></td><td><?=$ime?></td><td><?=$brindexa?></td>
								<td>Grupa '<?=$gime?>' je navedena dvaput - greška?</td>
							</tr>
							<?
						}
						continue;
					}
				}
	
				$upis_grupe[$labgrupa]=$imegrupe;
			}
	
			// Obavljam ispisivanje i upisivanje u grupe
			if ($ispis) { // na ekran
				?>
				<tr bgcolor="<?=$boja?>">
					<td><?=$prezime?></td><td><?=$ime?></td><td><?=$brindexa?></td>
					<td>
				<?
				foreach ($ispis_grupe as $gid => $gime) {
					print "Ispis iz grupe '$gime'<br />\n";
				}
				foreach ($upis_grupe as $gid => $gime) {
					print "Upis u grupu '$gime'<br />\n";
				}
				print "</td></tr>\n";
				if ($boja==$boja1) $boja=$boja2; else $boja=$boja1;
			} else {
				foreach ($ispis_grupe as $gid => $gime) {
					api_call("group/$gid/student/$student", [], "DELETE");
					if ($_api_http_code != "204") {
						niceerror("Neuspješan ispis studenta iz grupe: kod $_api_http_code");
						return;
					}
					zamgerlog2("student ispisan sa grupe (masovni unos)", $student, $gid);
				}
				foreach ($upis_grupe as $gid => $gime) {
					$result = api_call("group/$gid/student/$student", [], "POST");
					if ($_api_http_code != "201") {
						niceerror("Neuspješan upis studenta u grupu: " . $result['message']);
						return;
					}
					zamgerlog2("student upisan u grupu (masovni unos)", $student, $gid);
				}
			}
		}
	
		// Potvrda i Nazad
		if ($ispis) {
			if ($greska != 0) {
				?>
				</tbody></table>
				<p>U unesenim podacima ima grešaka. Da li ste izabrali ispravan format ("Prezime[TAB]Ime" vs. "Prezime Ime")?Vratite se nazad kako biste ovo popravili.</p>
				<p>NAPOMENA: Upis studenata na predmet može vršiti samo studentska služba. Ukoliko na spisku nedostaje neki student koji sluša vaš predmet, kontaktirajte službu radi razjašnjenja nesporazuma.</p>
				<p><input type="submit" name="nazad" value=" Nazad "></p>
				</form>
				<?
	
			} else if (count($mass_rezultat)==0) {
				?>
				</tbody></table>
				<p>Niste unijeli nijedan koristan podatak.</p>
				<p><input type="submit" name="nazad" value=" Nazad "></p>
				</form>
				<?
	
			} else {
				?>
				</tbody></table>
				<p>Potvrdite kreiranje grupa i upis studenata u grupe ili se vratite na prethodni ekran.</p>
				<p><input type="submit" name="nazad" value=" Nazad "> <input type="submit" value=" Potvrda"></p>
				</form>
				<?
			}
			return;
	
		} else {
			zamgerlog("masovan upis grupa za predmet pp$predmet",4);
			?>
			Masovan upis studenata u grupe je uspješno obavljen.
			<script language="JavaScript">
			location.href='?sta=nastavnik/grupe&predmet=<?=$predmet?>&ag=<?=$ag?>';
			</script>
			<?
		}
	}
	
	
	
	###############
	# Prikaz grupa
	###############
	
	?>
	<script language="JavaScript">
	function upozorenje(grupa) {
		var a = confirm("Svi studenti će biti ispisani iz ove grupe.");
		if (a) {
			document.getElementById('grupaid').value=grupa;
			document.brisanjegrupe.submit();
		}
	}
	</script>
	<?=genform("POST", "brisanjegrupe")?>
	<input type="hidden" name="akcija" value="obrisi_grupu">
	<input type="hidden" name="grupaid" id="grupaid" value=""></form>
	
	Spisak grupa:<br/>

	<ul>
	<?
	
	usort($groups, function($g1, $g2) { return strnatcasecmp($g1['name'], $g2['name']); });
	
	if (count($groups) == 0) {
		?>
		<li>Nema definisanih grupa</li>
		<?
	}
	
	$tip_selektovan = array();
	foreach ($groups as $group) {
		if (!preg_match("/\w/", $group['name']))
			$group['name'] = "[Nema imena]";
		
		
		?>
		<li><?=$group['name']?> -
			(<a href="?sta=nastavnik/grupe&predmet=<?=$predmet?>&ag=<?=$ag?>&akcija=studenti_grupa&grupaid=<?=$group['id']?>"><?=$group['countMembers']?> studenata</a>) -
			<a href="javascript:onclick=upozorenje('<?=$group['id']?>')">Obriši grupu</a>
		</li>
		<?
	
		if ($_GET['akcija']=="studenti_grupa" && $_GET['grupaid']==$group['id']) {
			// It's faster to get just this group members separately, than to get all group members at the top
			$group = api_call("group/" . $group['id'] . "/students")["results"];
			
			?>
			<ul>
			<?
			
			usort($group, function ($s1, $s2) {
				if ($s1['surname'] == $s2['surname']) return bssort($s1['name'], $s2['name']);
				return bssort($s1['surname'], $s2['surname']);
			});
			
			foreach($group as $s) {
				?><li><a href="#" onclick="javascript:window.open('?sta=saradnik/izmjena_studenta&student=<?=$s['id']?>&predmet=<?=$predmet?>&ag=<?=$ag?>','blah6','width=320,height=320');"><?=$s['surname']." ".$s['name']?></a></li><?
			}
			
			?>
			</ul>
			<?
			$zapamti_grupu = $group['name'];
			$tip_selektovan[$group['type']] = " SELECTED";
		}
	}
	
	?>
	</ul>
	<?
	
	# Editovanje grupe
	if ($_GET['akcija']=="studenti_grupa") {
		$gg = intval($_GET['grupaid']);
		?><p>
		<?=genform("POST")?>
		<input type="hidden" name="akcija" value="preimenuj_grupu">
		<input type="hidden" name="grupaid" value="<?=$gg?>">
		Promijenite naziv grupe: <input type="text" name="ime" size="20" value="<?=$zapamti_grupu?>">
		Promijenite tip grupe: <select name="tip">
		<option value="predavanja" <?=$tip_selektovan['predavanja']?>>Grupa za predavanja</option>
		<option value="vjezbe" <?=$tip_selektovan['vjezbe']?>>Grupa za vježbe</option>
		<option value="tutorijali" <?=$tip_selektovan['tutorijali']?>>Grupa za tutorijale</option>
		<option value="vjezbe+tutorijali" <?=$tip_selektovan['vjezbe+tutorijali']?>>Grupa za vježbe i tutorijale</option>
		</select>
		<input type="submit" value="Izmijeni"></form></p>
		<?
	}
	
	
	// Dodavanje grupe
	
	?>
	
	<p>
	<?=genform("POST")?>
	<input type="hidden" name="akcija" value="nova_grupa">
	Dodaj grupu: <input type="text" name="ime" size="20">
	Tip grupe:<select name="tip">
		<option value="predavanja">Grupa za predavanja</option>
		<option value="vjezbe">Grupa za vježbe</option>
		<option value="tutorijali">Grupa za tutorijale</option>
		<option value="vjezbe+tutorijali" SELECTED>Grupa za vježbe i tutorijale</option>
	</select>
	<input type="submit" value="Dodaj"></form></p>
	<?
	
	
	// Kopiranje grupa sa predmeta
	?>
	
	<p>
	<?=genform("POST")?>
	<input type="hidden" name="akcija" value="kopiraj_grupe">
	Prekopiraj grupe sa predmeta: <select name="kopiraj">
	<?
	$courses = api_call("course/programme/0/0", [ "resolve" => ["CourseDescription"] ])["results"];
	// Remove duplicates
	$coursesCleared = [];
	foreach ($courses as $course)
		$coursesCleared[$course['CourseUnit']['id']] = $course['CourseDescription']['name'];
	
	foreach ($coursesCleared as $id => $name) {
		?>
			<option value="<?=$id?>"><?=$name?></option>
		<?
	}
	?></select>
	<input type="submit" value="Dodaj">
	</form></p><?
	
	
	// Masovni unos
	
	// TODO preference
	
	$format = intval($_POST['format']);
	/*if (!$_POST['format']) {
		$q110 = db_query("select vrijednost from preference where korisnik=$userid and preferenca='mass-input-format'");
		if (db_num_rows($q110)>0) $format = db_result($q110,0,0);
		else //default vrijednost
			$format=0;
	}*/
	
	$separator = intval($_POST['separator']);
	/*if (!$_POST['separator']) {
		$q120 = db_query("select vrijednost from preference where korisnik=$userid and preferenca='mass-input-separator'");
		if (db_num_rows($q120)>0) $separator = db_result($q120,0,0);
		else //default vrijednost
			$separator=0;
	}*/
	
	?>
	
	<p><hr/></p><p><b>Masovni upis studenata u grupe</b><br/>
	U prozoru ispod navedite ime i prezime studenta, znak za separator i naziv grupe u koju želite da ga/je upišete.<br/>
	<?=genform("POST")?>
	<input type="hidden" name="fakatradi" value="0">
	<input type="hidden" name="akcija" value="massinput">
	<input type="hidden" name="nazad" value="">
	<input type="hidden" name="visestruki" value="1">
	<input type="hidden" name="duplikati" value="1">
	<input type="hidden" name="brpodataka" value="1">
	
	<textarea name="massinput" cols="50" rows="10"><?
	if (strlen($_POST['nazad'])>1) print $_POST['massinput'];
	?></textarea><br/>
	<br/>Format imena i prezimena: <select name="format" class="default">
	<option value="0" <? if($format==0) print "SELECTED";?>>Prezime[TAB]Ime</option>
	<option value="1" <? if($format==1) print "SELECTED";?>>Ime[TAB]Prezime</option>
	<option value="2" <? if($format==2) print "SELECTED";?>>Prezime Ime</option>
	<option value="3" <? if($format==3) print "SELECTED";?>>Ime Prezime</option>
	<option value="4" <? if($format==4) print "SELECTED";?>>Broj indeksa</option></select>&nbsp;
	Separator: <select name="separator" class="default">
	<option value="0" <? if($separator==0) print "SELECTED";?>>Tab</option>
	<option value="1" <? if($separator==1) print "SELECTED";?>>Zarez</option></select><br/><br/>
	
	<input type="submit" value="  Dodaj  ">
	</form></p><?
	

} // function nastavnik_grupa()

?>
