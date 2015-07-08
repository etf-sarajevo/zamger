<?

// STUDENT/UGOVOROUCENJU - generisanje ugovora o ucenju

// v4.0.9.1 (2009/07/16) + Novi modul za ugovor o ucenju
// v4.0.9.2 (2009/09/15) + Pocetak koristenja modula

// TODO: Ovdje se neće moći koristiti tabela plan_studija jer treba omogućiti maskiranje nekih predmeta, kao i odstupanja od plana (grrr)



function student_ugovoroucenju() {

	global $userid;
	
	require("lib/manip.php");

	// Naslov
	?>
	<h3>Ugovor o učenju</h3>

	<?
	// Za koju godinu se prijavljuje?
	$q1 = myquery("select id, naziv from akademska_godina where aktuelna=1");
	$q2 = myquery("select id, naziv from akademska_godina where id>".mysql_result($q1,0,0)." order by id limit 1");
	if (mysql_num_rows($q2)<1) {
//		nicemessage("U ovom trenutku nije aktiviran upis u sljedeću akademsku godinu.");
//		return;
		// Pretpostavljamo da se upisuje u aktuelnu?
		$zagodinu  = mysql_result($q1,0,0);
		$zagodinunaziv  = mysql_result($q1,0,1);
		$q3 = myquery("select id from akademska_godina where id<$zagodinu order by id desc limit 1");
		$proslagodina = mysql_result($q3,0,0);
	} else {
		$proslagodina = mysql_result($q1,0,0);
		$zagodinu = mysql_result($q2,0,0);
		$zagodinunaziv = mysql_result($q2,0,1);
	}
	?>
	<p>Za akademsku <?=$zagodinunaziv?> godinu.</p>
	<?


	// Ulazni podaci

	$studij=intval($_REQUEST['studij']);
	$godina=intval($_REQUEST['godina']);

	// Provjera ispravnosti podataka
	if ($studij!=0) {
		$q5 = myquery("select zavrsni_semestar from studij where id=$studij");
		if (mysql_num_rows($q5)<1) {
			niceerror("Neispravan studij");
			$studij=0;
			unset($_POST['akcija']);
		}
		else if ($godina<1 || $godina>mysql_result($q5,0,0)/2) {
			$godina=1;
		}
	} else {
		unset($_POST['akcija']);
	}


	// Odabir plana studija
	$plan_studija = 0;
	if ($studij>0) {
		$q5a = myquery("SELECT studij, plan_studija FROM student_studij WHERE student=$userid AND akademska_godina<=$zagodinu ORDER BY akademska_godina DESC LIMIT 1");
		if (mysql_num_rows($q5a)>0 && $studij ==  mysql_result($q5a,0,0))
			$plan_studija = mysql_result($q5a,0,1);
		
		if ($plan_studija == 0) {
			// Student nije prethodno studirao na istom studiju ili plan studija nije bio definisan
			// Uzimamo najnoviji plan za odabrani studij
			$q6 = myquery("select godina_vazenja from plan_studija where studij=$studij order by godina_vazenja desc limit 1");
			if (mysql_num_rows($q6)<1) { 
				niceerror("Nepostojeći studij");
				return;
			}
			$plan_studija = mysql_result($q6,0,0);
		}
	}


	// Akcija - kreiranje ugovora

	if ($_POST['akcija']=="kreiraj_ugovor") {
		// Provjera da li student ima minimalno 30 ECTS kredita po semestru
		// Ako je ovaj uslov ispunjen za oba semestra, slijedi i da ima minimalno 
		// 60 ECTS kredita po godini
		$s1predmeti=$s2predmeti=array(); // Ujedno punimo nizove izbornih predmeta

		for ($sem = $godina*2-1; $sem<=$godina*2; $sem++) {
			$semestar_ects=0;
			$q100 = myquery("select p.ects, p.naziv from predmet as p, plan_studija as ps where ps.godina_vazenja=$plan_studija and ps.studij=$studij and ps.semestar=$sem and ps.obavezan=1 and ps.predmet=p.id");
			while ($r100 = mysql_fetch_row($q100)) {
				$semestar_ects += $r100[0];
			}
			$q110 = myquery("select distinct predmet from plan_studija where godina_vazenja=$plan_studija and studij=$studij and semestar=$sem and obavezan=0");
			while ($r110 = mysql_fetch_row($q110)) {
				$izabran = $_REQUEST["is$r110[0]"];
				if ($izabran=="odsjek$sem") { // izborni predmet sa drugog odsjeka
					$izabran = intval($_REQUEST["odsjek-$r110[0]"]);
				} else $izabran=intval($izabran);

				if ($izabran>0) {
					$q120 = myquery("select ects, naziv from predmet where id=$izabran");
					if (mysql_num_rows($q120)<1) {
						niceerror("Ilegalan izborni predmet");
						return;
					}
					
					if (provjeri_kapacitet($izabran, $zagodinu, $plan_studija) == 0) {
						niceerror("Predmet ".mysql_result($q120,0,1)." se ne može izabrati jer su dostupni kapaciteti za taj predmet popunjeni");
						zamgerlog2("popunjen kapacitet za predmet", $izabran);
						return;
					}

					$semestar_ects += mysql_result($q120,0,0);
					if ($sem==$godina*2-1)
						$s1predmeti[]=$izabran;
					else
						$s2predmeti[]=$izabran;

				// Ako spisak izbornih predmeta nije radio buttons
				// nego checkboxes, $izabran ce biti 0
				} else foreach ($_REQUEST as $ime => $vrijednost) {
					$komad = "iz$r110[0]-";
					if (substr($ime,0,strlen($komad))==$komad) {
						// izborni predmet sa drugog odsjeka
						if (substr($ime, strlen($komad))=="odsjek")
							$izabran = intval($_REQUEST["odsjek-$r110[0]"]);
						else
							$izabran = intval($vrijednost);

						$q130 = myquery("select ects, naziv from predmet where id=$izabran");
						if (mysql_num_rows($q130)<1) {
							niceerror("Ilegalan izborni predmet");
							return;
						}
						
						if (provjeri_kapacitet($izabran, $zagodinu, $plan_studija) == 0) {
							niceerror("Predmet ".mysql_result($q130,0,1)." se ne može izabrati jer su dostupni kapaciteti za taj predmet popunjeni");
							zamgerlog2("popunjen kapacitet za predmet", $izabran);
							return;
						}
						
						$semestar_ects += mysql_result($q130,0,0);
						if ($sem==$godina*2-1)
							$s1predmeti[]=$izabran;
						else
							$s2predmeti[]=$izabran;
					}
				}
			}
			if ($semestar_ects<30) {
				niceerror("Niste izabrali dovoljno izbornih predmeta u $sem. semestru (ukupno $semestar_ects ECTS kredita, a potrebno je 30)");
				return;
			}
		}

		// Sve ok, brišemo stari ugovor iz baze
		$q140 = myquery("select id from ugovoroucenju where student=$userid and akademska_godina=$zagodinu");
		while ($r140 = mysql_fetch_row($q140)) {
			$q145 = myquery("delete from ugovoroucenju where id=$r140[0]");
			$q145 = myquery("delete from ugovoroucenju_izborni where ugovoroucenju=$r140[0]");
		}

		// Ubacujemo novi ugovor u bazu
		$q150 = myquery("insert into ugovoroucenju set student=$userid, akademska_godina=$zagodinu, studij=$studij, semestar=".($godina*2-1));
		// Uzimamo ID ugovora
		$q160 = myquery("select id from ugovoroucenju where student=$userid and akademska_godina=$zagodinu and studij=$studij and semestar=".($godina*2-1));
		$id1 = mysql_result($q160,0,0);
		foreach ($s1predmeti as $predmet) {
			$q170 = myquery("insert into ugovoroucenju_izborni set ugovoroucenju=$id1, predmet=$predmet");
		}

		// Isto za parni semestar
		$q180 = myquery("insert into ugovoroucenju set student=$userid, akademska_godina=$zagodinu, studij=$studij, semestar=".($godina*2));
		$q190 = myquery("select id from ugovoroucenju where student=$userid and akademska_godina=$zagodinu and studij=$studij and semestar=".($godina*2));
		$id2 = mysql_result($q190,0,0);
		foreach ($s2predmeti as $predmet) {
			$q200 = myquery("insert into ugovoroucenju_izborni set ugovoroucenju=$id2, predmet=$predmet");
		}

		zamgerlog("student u$userid kreirao ugovor o ucenju (ID: $id1 i $id2)",2); // 2 - edit
		zamgerlog2("kreirao ugovor o ucenju", intval($id1), intval($id2));
		nicemessage("Kreirali ste Ugovor o učenju!");
		?>
		<p><a href="?sta=student/ugovoroucenjupdf">Kliknite ovdje da biste ga isprintali.</a></p>
		<?
		return;
	}


	// Da li student već ima kreiran ugovor o učenju za sljedeću godinu?
	$q9 = myquery("select count(*) from ugovoroucenju where student=$userid and akademska_godina=$zagodinu");
	if (mysql_result($q9,0,0)>0) {
		?>
		<p>Već imate kreiran Ugovor o učenju.<br />Možete ga preuzeti <a href="?sta=student/ugovoroucenjupdf">klikom ovdje</a>, ili možete kreirati novi ugovor ispod (pri čemu će stari biti pobrisan).</p>
		<p>&nbsp;</p>
		<?
	}



	// --- Prikaz formulara za kreiranje ugovora
	// Studij nije odabran, biramo onaj koji student trenutno sluša
	if ($studij==0) {
		$q10 = myquery("select ss.studij, ss.semestar, s.zavrsni_semestar, s.institucija, s.tipstudija, ss.plan_studija from student_studij as ss, studij as s where ss.student=$userid and ss.akademska_godina=$proslagodina and ss.studij=s.id order by semestar desc limit 1");
		if (mysql_num_rows($q10)>0) {
			$studij=mysql_result($q10,0,0);
			$godina=mysql_result($q10,0,1)/2+1;
			$plan_studija = mysql_result($q10,0,5);
			if (mysql_result($q10,0,1)>=mysql_result($q10,0,2)) {
				$q20 = myquery("select id from studij where moguc_upis=1 and institucija=".mysql_result($q10,0,3)." and tipstudija>".mysql_result($q10,0,4)); // FIXME pretpostavka je da su tipovi studija poredani po ciklusima
				if (mysql_num_rows($q20)>0) {
					$studij = mysql_result($q20,0,0);
					$godina = 1;
					$plan_studija = 0; // Uzećemo najnoviji plan za odabrani studij

				} else {
					// Nema gdje dalje... postavljamo sve na nulu
					$studij=0;
					$godina=1;
				}
			}

			if ($plan_studija == 0) {
				// Određujemo najnoviji plan za novi studij
				$q6 = myquery("select godina_vazenja from plan_studija where studij=$studij order by godina_vazenja desc limit 1");
				if (mysql_num_rows($q6)<1) { 
					niceerror("Nepostojeći studij");
					return;
				}
				$plan_studija = mysql_result($q6,0,0);
			}

		} else {
			niceerror("Niste nikada bili naš student!");
			// Radi testiranja dozvolićemo nestudentima da uđu
			$godina=1;
		}
	}

	?>
	<SCRIPT language="JavaScript">
	// Refresh stranice sa novim izbornim predmetima
	function refresh() {
		var studij = parseInt(document.getElementById('studij').value);
		var godina = parseInt(document.getElementById('godina').value);
		location.replace("index.php?sta=student/ugovoroucenju&studij="+studij+"&godina="+godina);
	}

	// Da bismo spriječili da pritisak na Enter submituje formu
	function noenter() {
		if (window.event && window.event.keyCode == 13) {
			refresh();
			return false;
		}
	}

	// Funckija koja ne dozvoljava da se selektuje različit broj polja od navedenog
	var globalna;
	function jedanod(slot, clicked) {
		var template = "iz"+slot;
		var found=false;
		for (var i=0; i<document.mojaforma.length; i++) {
			var el = document.mojaforma.elements[i];

			if (el.type != "checkbox") continue;
			if (el==clicked) continue;
			if (template == el.name.substr(0,template.length)) {
				if (clicked.checked==true && el.checked==true) {
					el.checked=false; found=true; break; 
				}
				if (clicked.checked==false && el.checked==false) {
					el.checked=true; found=true; break;
				}
			}
		}
		if (!found) {
			globalna=clicked;
			setTimeout("revertuj()", 100);
		}
		return found;
	}
	function drugiodsjek(slot, semestar, enable) {
		var template = "iz"+slot;
		for (var i=0; i<document.mojaforma.length; i++) {
			var el = document.mojaforma.elements[i];
			if (el.type != "checkbox" && el.type != "radio") continue;
			if (el.name.substr(0,template.length) == template) continue;
			if (el.name.substr(0,template.length) == "is"+slot) continue;
			if (el.value == "odsjek"+semestar) el.disabled=enable;
		}
	}
	function revertuj() {
			if (globalna.checked) globalna.checked=false;
			else globalna.checked=true;
	}
	</SCRIPT>

	<form action="index.php" method="POST" name="mojaforma">
	<input type="hidden" name="sta" value="student/ugovoroucenju">
	<input type="hidden" name="akcija" value="kreiraj_ugovor">

	<p>Bjanko Ugovor o učenju: <a href="images/content/150dpi/domestic-contract-0.png">stranica 1</a>, <a href="images/content/150dpi/domestic-contract-1.png">stranica 2</a>!</p>

	<p>Studij: <select name="studij" id="studij" onchange="javascript:refresh()"><option></option>
	<?

	// Spisak studija
	$q30 = myquery("select id, naziv from studij where moguc_upis=1 order by tipstudija, naziv");
	while ($r30 = mysql_fetch_row($q30)) {
		print "<option value=\"$r30[0]\"";
		if ($r30[0]==$studij) print " selected";
		print ">$r30[1]</option>\n";
	}


	$semestar = $godina*2-1;
	?>
	</select></p>
	<p>Godina studija: <input type="text" name="godina" id="godina" value="<?=$godina?>" onchange="javascript:refresh()" onkeypress="javascript:return noenter()"></p>
	<p>&nbsp;</p>

	<p><b>Izborni predmeti:</b></p>
	<p><?=$semestar?>. semestar:<br />
	<?


	// Spisak izbornih predmeta
	$ops=$count=0;
	$q40 = myquery("select predmet from plan_studija where godina_vazenja=$plan_studija and studij=$studij and semestar=$semestar and obavezan=0 order by predmet");
	if (mysql_num_rows($q40)<1)
		print "Nema izbornih predmeta.";
	else {
	$ops=$count=0;
	$slotovi=array();
	while ($r40 = mysql_fetch_row($q40)) {
		$slotovi[] = $r40[0];
	}
	$slotovi[]=0;
	foreach ($slotovi as $slot) {
		if ($ops==0) { /* nop */ }
		else if ($slot==$ops) $count++;
		else if ($count==0) {
			$q45 = myquery("select p.id, p.naziv from predmet as p, izborni_slot as iz where iz.id=$ops and iz.predmet=p.id");
			$prvi=1;
			while ($r45=mysql_fetch_row($q45)) {
				print "<input type=\"radio\" name=\"is$ops\" value=\"$r45[0]\"  onchange=\"drugiodsjek('$ops',$semestar,false);\"";
				if ($prvi) { print " CHECKED"; $prvi=0; }
				print ">$r45[1]</input><br />\n";
			}

			// Predmet sa drugog studija
			print "<input type=\"radio\" name=\"is$ops\" value=\"odsjek$semestar\" onchange=\"drugiodsjek('$ops',$semestar,true);\">Predmet sa drugog odsjeka</input><br />\n";
			print "<select name=\"odsjek-$ops\">\n";

			$spisak_predmeta_drugi_studij = array();
			$drugi_studiji = array();

			$q46 = myquery("select tipstudija from studij where id=$studij");
			$studij_ts = intval(mysql_result($q46,0,0));

			$q47 = myquery("select ps.predmet, p.naziv, s.kratkinaziv, s.id from plan_studija as ps, studij as s, predmet as p where ps.godina_vazenja=$plan_studija and ps.studij!=$studij and ps.semestar=$semestar and ps.obavezan=1 and ps.studij=s.id and s.tipstudija=$studij_ts and ps.predmet=p.id order by s.kratkinaziv, ps.semestar, p.naziv");
			while ($r47 = mysql_fetch_row($q47)) {
				$drugi_studiji[$r47[3]]=$r47[2];

				// Ne uzimamo u obzir predmete koji su zajednicki za vise studija, pa ce ih student svakako slusati ili ih je vec slusao
				$q48 = myquery("select count(*) from plan_studija where godina_vazenja=$plan_studija and studij=$studij and obavezan=1 and predmet=$r47[0]");
				if (mysql_result($q48,0,0)>0) continue;

				// Ne nudimo predmete koje je student eventualno vec polozio
//				$q49 = myquery("select count(*) from konacna_ocjena where student=$userid and predmet=$r47[0] and ocjena>5");
//				if (mysql_result($q49,0,0)>0) continue;

				$spisak_predmeta_drugi_studij[$r47[0]] = "$r47[1] ($r47[2])";

				//print "<option value=\"$r47[0]\">$r47[1] ($r47[2])</option>\n";
			}

			// A sada i izborni
			$q51 = myquery("select ps.predmet, s.kratkinaziv, s.id from plan_studija as ps, studij as s where ps.godina_vazenja=$plan_studija and ps.studij!=$studij and ps.semestar=$semestar and ps.obavezan=0 and ps.studij=s.id and s.tipstudija=$studij_ts order by s.kratkinaziv");
			while ($r51 = mysql_fetch_row($q51)) {
				$drugi_studiji[$r51[2]]=$r51[1];

				$q52 = myquery("select izs.predmet, p.naziv from izborni_slot as izs, predmet as p where izs.id=$r51[0] and izs.predmet=p.id order by p.naziv");
				while ($r52 = mysql_fetch_row($q52)) {
					// Ne uzimamo u obzir predmete koji su zajednicki za vise studija, pa ce ih student svakako slusati ili ih je vec slusao
					$q48 = myquery("select count(*) from plan_studija where godina_vazenja=$plan_studija and studij=$studij and obavezan=1 and predmet=$r52[0]");
					if (mysql_result($q48,0,0)>0) continue;

					// Ne nudimo predmete koje je student eventualno vec polozio
//					$q49 = myquery("select count(*) from konacna_ocjena where student=$userid and predmet=$r52[0] and ocjena>5");
//					if (mysql_result($q49,0,0)>0) continue;

					$spisak_predmeta_drugi_studij[$r52[0]] = "$r52[1] ($r51[1])";
				}

//				print "<option value=\"$r47[0]\">$r47[1] ($r47[2])</option>\n";
			}

			sort($drugi_studiji);
//			sort($spisak_predmeta_drugi_studij);
			foreach ($drugi_studiji as $studij_id=>$studij_naziv) {
				foreach ($spisak_predmeta_drugi_studij as $predmet_id=>$predmet_naziv) {
					if (strstr($predmet_naziv, "($studij_naziv)")) {
						print "<option value=\"$predmet_id\">$predmet_naziv</option>\n";
					}
				}
			}
			print "</select><br>\n";
		} else {
			print "(izaberite ".($count+1)." predmeta)<br />\n";
			$q45 = myquery("select p.id, p.naziv from predmet as p, izborni_slot as iz where iz.id=$ops and iz.predmet=p.id and p.moguc_upis=1");
			$prvi=$count+1;
			while ($r45=mysql_fetch_row($q45)) {
				print "<input type=\"checkbox\" name=\"iz$ops-$r45[0]\" value=\"$r45[0]\"";
				if ($prvi) { print " CHECKED"; $prvi--; }
				print " onchange=\"javascript:jedanod('$ops',this)\">$r45[1]</input><br />\n";
			}

			// Predmet sa drugog studija
			print "<input type=\"checkbox\" name=\"iz$ops-odsjek\" value=\"odsjek$semestar\" onchange=\"javascript:jedanod('$ops',this); drugiodsjek('$ops',$semestar,this.checked);\">Predmet sa drugog odsjeka</input><br />\n";
			print "<select name=\"odsjek-$ops\">\n";

			$spisak_predmeta_drugi_studij = array();
			$drugi_studiji = array();

			$q46 = myquery("select tipstudija from studij where id=$studij");
			$studij_ts = intval(mysql_result($q46,0,0));

			$q47 = myquery("select ps.predmet, p.naziv, s.kratkinaziv, s.id from plan_studija as ps, studij as s, predmet as p where ps.godina_vazenja=$plan_studija and ps.studij!=$studij and ps.semestar=$semestar and ps.obavezan=1 and ps.studij=s.id and s.tipstudija=$studij_ts and ps.predmet=p.id order by s.kratkinaziv, ps.semestar, p.naziv");
			while ($r47 = mysql_fetch_row($q47)) {
				$drugi_studiji[$r47[3]]=$r47[2];

				// Ne uzimamo u obzir predmete koji su zajednicki za vise studija, pa ce ih student svakako slusati ili ih je vec slusao
				$q48 = myquery("select count(*) from plan_studija where godina_vazenja=$plan_studija and studij=$studij and obavezan=1 and predmet=$r47[0]");
				if (mysql_result($q48,0,0)>0) continue;

				// Ne nudimo predmete koje je student eventualno vec polozio
//				$q49 = myquery("select count(*) from konacna_ocjena where student=$userid and predmet=$r47[0] and ocjena>5");
//				if (mysql_result($q49,0,0)>0) continue;

				$spisak_predmeta_drugi_studij[$r47[0]] = "$r47[1] ($r47[2])";

				//print "<option value=\"$r47[0]\">$r47[1] ($r47[2])</option>\n";
			}

			// A sada i izborni
			$q51 = myquery("select ps.predmet, s.kratkinaziv, s.id from plan_studija as ps, studij as s where ps.godina_vazenja=$plan_studija and ps.studij!=$studij and ps.semestar=$semestar and ps.obavezan=0 and ps.studij=s.id and s.tipstudija=$studij_ts order by s.kratkinaziv");
			while ($r51 = mysql_fetch_row($q51)) {
				$drugi_studiji[$r51[2]]=$r51[1];

				$q52 = myquery("select izs.predmet, p.naziv from izborni_slot as izs, predmet as p where izs.id=$r51[0] and izs.predmet=p.id order by p.naziv");
				while ($r52 = mysql_fetch_row($q52)) {
					// Ne uzimamo u obzir predmete koji su zajednicki za vise studija, pa ce ih student svakako slusati ili ih je vec slusao
					$q48 = myquery("select count(*) from plan_studija where godina_vazenja=$plan_studija and studij=$studij and obavezan=1 and predmet=$r52[0]");
					if (mysql_result($q48,0,0)>0) continue;

					// Ne nudimo predmete koje je student eventualno vec polozio
//					$q49 = myquery("select count(*) from konacna_ocjena where student=$userid and predmet=$r52[0] and ocjena>5");
//					if (mysql_result($q49,0,0)>0) continue;

					$spisak_predmeta_drugi_studij[$r52[0]] = "$r52[1] ($r51[1])";
				}

//				print "<option value=\"$r47[0]\">$r47[1] ($r47[2])</option>\n";
			}

			sort($drugi_studiji);
//			sort($spisak_predmeta_drugi_studij);
			foreach ($drugi_studiji as $studij_id=>$studij_naziv) {
				foreach ($spisak_predmeta_drugi_studij as $predmet_id=>$predmet_naziv) {
					if (strstr($predmet_naziv, "($studij_naziv)")) {
						print "<option value=\"$predmet_id\">$predmet_naziv</option>\n";
					}
				}
			}
			print "</select><br>\n";
		}
		$ops=$slot;
	}}


	$semestar=$godina*2;
	?></p>

	<p><?=$semestar?>. semestar:<br />
	<?
	$q40 = myquery("select predmet from plan_studija as ps where godina_vazenja=$plan_studija and studij=$studij and semestar=$semestar and obavezan=0 order by predmet");
	if (mysql_num_rows($q40)<1)
		print "Nema izbornih predmeta.";
	else {
	$ops=$count=0;
	$slotovi=array();
	while ($r40 = mysql_fetch_row($q40)) {
		$slotovi[] = $r40[0];
	}
	$slotovi[]=0;
	foreach ($slotovi as $slot) {
		if ($ops==0) { /* nop */ }
		else if ($slot==$ops) $count++;
		else if ($count==0) {
			$q45 = myquery("select p.id, p.naziv from predmet as p, izborni_slot as iz where iz.id=$ops and iz.predmet=p.id");
			$prvi=1;
			while ($r45=mysql_fetch_row($q45)) {
				print "<input type=\"radio\" name=\"is$ops\" value=\"$r45[0]\"  onchange=\"drugiodsjek('$ops',$semestar,false);\""; 
				if ($prvi) { print " CHECKED"; $prvi=0; }
				print ">$r45[1]</input><br />\n";
			}

			// Predmet sa drugog studija
			print "<input type=\"radio\" name=\"is$ops\" value=\"odsjek$semestar\" onchange=\"drugiodsjek('$ops',$semestar,true);\">Predmet sa drugog odsjeka</input><br />\n";
			print "<select name=\"odsjek-$ops\">\n";

			$spisak_predmeta_drugi_studij = array();
			$drugi_studiji = array();

			$q46 = myquery("select tipstudija from studij where id=$studij");
			$studij_ts = intval(mysql_result($q46,0,0));

			$q47 = myquery("select ps.predmet, p.naziv, s.kratkinaziv, s.id from plan_studija as ps, studij as s, predmet as p where ps.godina_vazenja=$plan_studija and ps.studij!=$studij and ps.semestar=$semestar and ps.obavezan=1 and ps.studij=s.id and s.tipstudija=$studij_ts and ps.predmet=p.id order by s.kratkinaziv, ps.semestar, p.naziv");
			while ($r47 = mysql_fetch_row($q47)) {
				$drugi_studiji[$r47[3]]=$r47[2];

				// Ne uzimamo u obzir predmete koji su zajednicki za vise studija, pa ce ih student svakako slusati ili ih je vec slusao
				$q48 = myquery("select count(*) from plan_studija where godina_vazenja=$plan_studija and studij=$studij and obavezan=1 and predmet=$r47[0]");
				if (mysql_result($q48,0,0)>0) continue;

				// Ne nudimo predmete koje je student eventualno vec polozio
				$q49 = myquery("select count(*) from konacna_ocjena where student=$userid and predmet=$r47[0] and ocjena>5");
//				if (mysql_result($q49,0,0)>0) continue;

				$spisak_predmeta_drugi_studij[$r47[0]] = "$r47[1] ($r47[2])";

				//print "<option value=\"$r47[0]\">$r47[1] ($r47[2])</option>\n";
			}

			// A sada i izborni
			$q51 = myquery("select ps.predmet, s.kratkinaziv, s.id from plan_studija as ps, studij as s where ps.godina_vazenja=$plan_studija and ps.studij!=$studij and ps.semestar=$semestar and ps.obavezan=0 and ps.studij=s.id and s.tipstudija=$studij_ts order by s.kratkinaziv");
			while ($r51 = mysql_fetch_row($q51)) {
				$drugi_studiji[$r51[2]]=$r51[1];

				$q52 = myquery("select izs.predmet, p.naziv from izborni_slot as izs, predmet as p where izs.id=$r51[0] and izs.predmet=p.id order by p.naziv");
				while ($r52 = mysql_fetch_row($q52)) {
					// Ne uzimamo u obzir predmete koji su zajednicki za vise studija, pa ce ih student svakako slusati ili ih je vec slusao
					$q48 = myquery("select count(*) from plan_studija where godina_vazenja=$plan_studija and studij=$studij and obavezan=1 and predmet=$r52[0]");
					if (mysql_result($q48,0,0)>0) continue;

					// Ne nudimo predmete koje je student eventualno vec polozio
					$q49 = myquery("select count(*) from konacna_ocjena where student=$userid and predmet=$r52[0] and ocjena>5");
//					if (mysql_result($q49,0,0)>0) continue;

					$spisak_predmeta_drugi_studij[$r52[0]] = "$r52[1] ($r51[1])";
				}

//				print "<option value=\"$r47[0]\">$r47[1] ($r47[2])</option>\n";
			}

			sort($drugi_studiji);
			//sort($spisak_predmeta_drugi_studij);
			foreach ($drugi_studiji as $studij_id=>$studij_naziv) {
				foreach ($spisak_predmeta_drugi_studij as $predmet_id=>$predmet_naziv) {
					if (strstr($predmet_naziv, "($studij_naziv)")) {
						print "<option value=\"$predmet_id\">$predmet_naziv</option>\n";
					}
				}
			}

			print "</select><br>\n";
		} else {
			print "(izaberite ".($count+1)." predmeta)<br />\n";
			$q45 = myquery("select p.id, p.naziv from predmet as p, izborni_slot as iz where iz.id=$ops and iz.predmet=p.id");
			$prvi=$count+1;
			while ($r45=mysql_fetch_row($q45)) {
				print "<input type=\"checkbox\" name=\"iz$ops-$r45[0]\" value=\"$r45[0]\"";
				if ($prvi) { print " CHECKED"; $prvi--; }
				print " onchange=\"javascript:jedanod('$ops',this)\">$r45[1]</input><br />\n";
			}

			// Predmet sa drugog studija
			print "<input type=\"checkbox\" name=\"iz$ops-odsjek\" value=\"odsjek$semestar\" onchange=\"javascript:jedanod('$ops',this); drugiodsjek('$ops',$semestar,this.checked);\">Predmet sa drugog odsjeka</input><br />\n";
			print "<select name=\"odsjek-$ops\">\n";

			$spisak_predmeta_drugi_studij = array();
			$drugi_studiji = array();

			$q46 = myquery("select tipstudija from studij where id=$studij");
			$studij_ts = intval(mysql_result($q46,0,0));

			$q47 = myquery("select ps.predmet, p.naziv, s.kratkinaziv, s.id from plan_studija as ps, studij as s, predmet as p where ps.godina_vazenja=$plan_studija and ps.studij!=$studij and ps.semestar=$semestar and ps.obavezan=1 and ps.studij=s.id and s.tipstudija=$studij_ts and ps.predmet=p.id order by s.kratkinaziv, ps.semestar, p.naziv");
			while ($r47 = mysql_fetch_row($q47)) {
				$drugi_studiji[$r47[3]]=$r47[2];

				// Ne uzimamo u obzir predmete koji su zajednicki za vise studija, pa ce ih student svakako slusati ili ih je vec slusao
				$q48 = myquery("select count(*) from plan_studija where godina_vazenja=$plan_studija and studij=$studij and obavezan=1 and predmet=$r47[0]");
				if (mysql_result($q48,0,0)>0) continue;

				// Ne nudimo predmete koje je student eventualno vec polozio
				$q49 = myquery("select count(*) from konacna_ocjena where student=$userid and predmet=$r47[0] and ocjena>5");
//				if (mysql_result($q49,0,0)>0) continue;

				$spisak_predmeta_drugi_studij[$r47[0]] = "$r47[1] ($r47[2])";

				//print "<option value=\"$r47[0]\">$r47[1] ($r47[2])</option>\n";
			}

			// A sada i izborni
			$q51 = myquery("select ps.predmet, s.kratkinaziv, s.id from plan_studija as ps, studij as s where ps.godina_vazenja=$plan_studija and ps.studij!=$studij and ps.semestar=$semestar and ps.obavezan=0 and ps.studij=s.id and s.tipstudija=$studij_ts order by s.kratkinaziv");
			while ($r51 = mysql_fetch_row($q51)) {
				$drugi_studiji[$r51[2]]=$r51[1];

				$q52 = myquery("select izs.predmet, p.naziv from izborni_slot as izs, predmet as p where izs.id=$r51[0] and izs.predmet=p.id order by p.naziv");
				while ($r52 = mysql_fetch_row($q52)) {
					// Ne uzimamo u obzir predmete koji su zajednicki za vise studija, pa ce ih student svakako slusati ili ih je vec slusao
					$q48 = myquery("select count(*) from plan_studija where godina_vazenja=$plan_studija and studij=$studij and obavezan=1 and predmet=$r52[0]");
					if (mysql_result($q48,0,0)>0) continue;

					// Ne nudimo predmete koje je student eventualno vec polozio
					$q49 = myquery("select count(*) from konacna_ocjena where student=$userid and predmet=$r52[0] and ocjena>5");
//					if (mysql_result($q49,0,0)>0) continue;

					$spisak_predmeta_drugi_studij[$r52[0]] = "$r52[1] ($r51[1])";
				}

//				print "<option value=\"$r47[0]\">$r47[1] ($r47[2])</option>\n";
			}

			sort($drugi_studiji);
			//sort($spisak_predmeta_drugi_studij);
			foreach ($drugi_studiji as $studij_id => $studij_naziv) {
				foreach ($spisak_predmeta_drugi_studij as $predmet_id => $predmet_naziv) {
					if (strstr($predmet_naziv, "($studij_naziv)")) {
						print "<option value=\"$predmet_id\">$predmet_naziv</option>\n";
					}
				}
			}

			print "</select><br>\n";
		}
		$ops=$slot;
	}}

	?>
	</p>
	<input type="button" value="Osvježi spisak predmeta" onclick="javascript:refresh()"><br /><br />

	<input type="submit" value="Kreiraj ugovor"></form>


	<p><b>Napomene:</b><br>
	* Ukoliko obnavljate godinu, trebate ponovo izabrati one predmete koje ste već položili.<br>
	* Možete izabrati najviše jedan predmet s drugog odsjeka po semestru, a u zbiru trebate imati najmanje 30 ECTS kredita po semestru odnosno 60 ECTS kredita po godini.<br>
	* Ako želite slušati izborni predmet sa drugog fakulteta, sada ovdje izaberite neki predmet sa našeg fakulteta a ujedno pokrenite proceduru (koja podrazumijeva odobrenje oba fakulteta).</p>
	<?

}

?>
