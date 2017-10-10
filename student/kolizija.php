<?

// STUDENT/KOLIZIJA - modul koji olaksava upis godine na koliziju



function student_kolizija() {

	global $userid, $conf_uslov_predmeta;
	
	require("lib/student_studij.php");
	
	// Definicija kolizije
	$limit_ects_zima = 20;
	$limit_ects_ljeto = 20;
	$uslov_ukupan_broj_nepolozenih = 0;
	$uslov_ects_zima = 15;
	$uslov_ects_ljeto = 15;

	// Naslov
	?>
	<h3>Kolizija</h3>
	<?
	// Za koju godinu se prijavljuje?
	$q1 = db_query("select id, naziv from akademska_godina where aktuelna=1");
	$q2 = db_query("select id, naziv from akademska_godina where id>".db_result($q1,0,0)." order by id limit 1");
	if (db_num_rows($q2)<1) {
//		nicemessage("U ovom trenutku nije aktiviran upis u sljedeću akademsku godinu.");
//		return;
		// Pretpostavljamo da se upisuje u aktuelnu?
		$zagodinu  = db_result($q1,0,0);
		$zagodinunaziv  = db_result($q1,0,1);
		$q3 = db_query("select id from akademska_godina where id<$zagodinu order by id desc limit 1");
		$proslagodina = db_result($q3,0,0);
		if (db_num_rows($q3)<1) {
			// Definisana je samo jedna akademska godina u bazi
			nicemessage("U ovom trenutku nije aktiviran upis u sljedeću akademsku godinu.");
			return;
		}
	} else {
		$proslagodina = db_result($q1,0,0);
		$zagodinu = db_result($q2,0,0);
		$zagodinunaziv = db_result($q2,0,1);
	}
	?>
	<p>Za akademsku <?=$zagodinunaziv?> godinu.</p>
	<?


	// Ulazni podaci

	$studij=intval($_REQUEST['studij']);
	$godina=intval($_REQUEST['godina']);
	$prenosi = intval($_REQUEST['prenosi']);


	// Provjera ispravnosti podataka
	if ($studij!=0) {
		$trajanje_studija = db_get("select ts.trajanje from studij s, tipstudija ts where s.id=$studij and s.tipstudija=ts.id");
		if ($trajanje_studija === false) {
			niceerror("Neispravan studij");
			$studij=0;
			unset($_POST['akcija']);
		}
		else if ($godina<1 || $godina>$trajanje_studija/2) {
			$godina=1;
		}
		$naziv_studija = db_get("select naziv from studij where id=$studij");
	} else {
		unset($_POST['akcija']);
	}


	// Šta trenutno sluša student?
	$q10 = db_query("SELECT ss.studij, ss.semestar, ts.trajanje, s.institucija, s.tipstudija, s.naziv, ss.plan_studija 
		FROM student_studij ss, studij s, tipstudija ts 
		WHERE ss.student=$userid AND ss.akademska_godina=$proslagodina AND ss.studij=s.id AND s.tipstudija=ts.id
		ORDER BY semestar DESC LIMIT 1");
	if (db_num_rows($q10)>0) {
		$trenutni_studij=db_result($q10,0,0);
		$trenutni_semestar=db_result($q10,0,1);
		// Podaci koji nam trebaju radi prelaska sa BSc na MSc
		$strajanje=db_result($q10,0,2);
		$sinstitucija=db_result($q10,0,3);
		$stipstudija=db_result($q10,0,4);
		$snazivstudija=db_result($q10,0,5);
		$najnoviji_plan=db_result($q10,0,6);
	} else {
		niceerror("Niste nikada bili naš student!");
		return; // dozvoliti testiranje nestudentima bi bilo prilično komplikovano, obzirom na dio "provjera uslova za koliziju"
	}

	// Modul kolizija zahtijeva plan studija
	if ($najnoviji_plan==0) {
		niceerror("Modul za koliziju ne može biti aktiviran ako nije definisan plan studija");
		print "Kontaktirajte vašeg administratora.";
		return;
	}


	// PROVJERA USLOVA ZA KOLIZIJU
	// Od predmeta koje je slušao, koliko je pao?
	$predmet_naziv=$predmet_ects=$predmet_semestar=$predmet_stari=array();
	$broj_izbornih = array();
	$q30 = db_query("select semestar, pasos_predmeta, obavezan, plan_izborni_slot from plan_studija_predmet where plan_studija=$najnoviji_plan and semestar<=$trenutni_semestar order by semestar");
	while (db_fetch4($q30, $semestar, $pasos_predmeta, $obavezan, $plan_izborni_slot)) {
		if ($obavezan == 1) {
			$q40 = db_query("SELECT predmet, naziv, ects FROM pasos_predmeta WHERE id=$pasos_predmeta");
			db_fetch3($q40, $id, $naziv, $ects);
			
			$ima_ocjenu = db_get("select count(*) from konacna_ocjena where student=$userid and predmet=$id and ocjena>5");
			if ($ima_ocjenu < 1) {
				$predmet_naziv[$id] = $naziv;
				$predmet_ects[$id] = $ects;
				$predmet_semestar[$id] = $semestar;
				if ($semestar < $trenutni_semestar-1) $predmet_stari[$id]=1; // predmet sa nizih godina se ne moze prenijeti
				else $predmet_stari[$id]=0;
			}

		} else { // izborni predmet
			$broj_izbornih[$plan_izborni_slot]++;
			$q60 = db_query("select pp.predmet, pp.naziv, pp.ects from plan_izborni_slot as pis, pasos_predmeta as pp, ponudakursa as pk, student_predmet as sp where pis.id=$plan_izborni_slot and pis.pasos_predmeta=pp.id and pp.predmet=pk.predmet and pk.akademska_godina=$proslagodina and pk.id=sp.predmet and sp.student=$userid");

			// Upit vraća više redova ako postoji više slotova za isti skup predmeta
			while (db_fetch3($q60, $id, $naziv, $ects)) {
				$ima_ocjenu = db_get("select count(*) from konacna_ocjena where student=$userid and predmet=$id and ocjena>5");
				if ($ima_ocjenu < 1) {
					$predmet_naziv[$id] = $naziv;
					$predmet_ects[$id] = $ects;
					$predmet_semestar[$id] = $semestar;
					if ($semestar < $trenutni_semestar-1) $predmet_stari[$id]=1; // predmet sa nizih godina se ne moze prenijeti
					else $predmet_stari[$id]=0;
				}
			}

			// Student nije slušao dovoljan broj ponuđenih izbornih predmeta
			// Dešava se u slučaju da je izabran predmet sa drugog odsjeka
			$broj_sa_maticnog = db_num_rows($q60);
			if ($broj_sa_maticnog < $broj_izbornih[$plan_izborni_slot]) {
				$q60 = db_query("select pp.predmet, pp.naziv, pp.ects from plan_izborni_slot as pis, pasos_predmeta as pp where pis.id=$plan_izborni_slot and pis.pasos_predmeta=pp.id");
				$naziv="Izborni predmet ("; // Kombinovani naziv svih predmeta
				$polozio=0;
				$min_ects = 100; // treba nam predmet sa najmanjim brojem ects kredita
				while (db_fetch3($q60, $id, $pnaziv, $ects)) {
					if (strlen($naziv)>18) $naziv .= ", ";
					$naziv .= $pnaziv;
	
					if ($ects < $min_ects) $min_ects=$ects;
	
					$ima_ocjenu = db_get("select count(*) from konacna_ocjena where student=$userid and predmet=$id and ocjena>5");
					if ($ima_ocjenu > 0) $polozio++;
				}
	
				if ($polozio < $broj_izbornih[$plan_izborni_slot]) { // nije polozio dovoljno izbornih predmeta
					// Spisak izbornih
					$validni_izborni = db_query_varray("select pp.predmet from plan_izborni_slot as pis, plan_studija_predmet as psp, pasos_predmeta as pp where psp.plan_studija=$najnoviji_plan and psp.semestar<=$trenutni_semestar and psp.obavezan=0 and psp.plan_izborni_slot=pis.id and psp.pasos_predmeta=pp.id");

					// Određujemo predmet sa drugog odsjeka koji je slušao 
					// koristeći tabelu ponudakursa da saznamo sve predmete koji su se izvodili u tom semestru
					$pronadjen = false;
					$q72 = db_query("select pk.predmet, p.naziv, p.ects from ponudakursa as pk, student_predmet as sp, predmet as p where sp.student=$userid and sp.predmet=pk.id and pk.akademska_godina=$proslagodina and pk.semestar=$semestar and pk.obavezan=0 and pk.predmet=p.id");
					while (db_fetch3($q72, $id, $naziv, $ects)) {
						if (!in_array($id, $validni_izborni)) {
							$pronadjen = true;

							// Da li ga je položio?
							$ima_ocjenu = db_get("select count(*) from konacna_ocjena where student=$userid and predmet=$id and ocjena>5");
							if ($ima_ocjenu == 0) {
								$predmet_naziv[$id] = $naziv;
								$predmet_ects[$id] = $ects;
								$predmet_semestar[$id] = $semestar;
								if ($semestar < $trenutni_semestar-1) $predmet_stari[$id]=1;
								else $predmet_stari[$id]=0;
							}
						}
					}

					// Nismo pronašli predmet sa drugog odsjeka koji je student slušao
					// Znači da student nije ni upisao sve potrebne izborne predmete
					if (!$pronadjen) {
						$predmet_naziv[$plan_izborni_slot] = $naziv . ")";
						$predmet_ects[$plan_izborni_slot] = $ects;
						$predmet_semestar[$plan_izborni_slot] = $semestar;
						if ($semestar < $trenutni_semestar-1) $predmet_stari[$semestar]=1;
						else $predmet_stari[$semestar]=0;
					}
				}
			}
		}
	}

	// Da li je već u koliziji? Provjeravamo da li je upisan na predmete iz veće godine studija nego što je trenutno
	// FIXME ako ukinemo unaprijed upisivanje predmeta na koliziji za čitavu godinu, ovo će prestati raditi!
	$u_koliziji = db_get("select count(*) from student_predmet as sp, ponudakursa as pk where sp.student=$userid and sp.predmet=pk.id and pk.studij=$trenutni_studij and pk.semestar>$trenutni_semestar and pk.akademska_godina=$zagodinu");
	if ($u_koliziji > 0) {
		?><p>Vaš zahtjev za koliziju je prihvaćen. Upisani ste u odabrane predmete (vidjećete ih na spisku predmeta kada zvanično počne sljedeća akademska godina).</p><?
		return;
	}

	// Uslov za upis u sljedeću godinu 
	if (count($predmet_naziv)==0) {
		?><p>Vi ste dali uslov za upis u <?=$snazivstudija?>, <?=($trenutni_semestar+1)?>. semestar bez kolizije! Možete popuniti <a href="?sta=student/ugovoroucenju">Ugovor o učenju</a> kako biste izabrali izborne predmete.</p>
		<?
		return;

	} else if (count($predmet_naziv) <= $conf_uslov_predmeta) {
		$da_stari_je=0;
		foreach ($predmet_stari as $stari) { if ($stari) $da_stari_je=1; }
		if ($da_stari_je==0) {
			?><p>Vi ste dali uslov za upis u <?=$snazivstudija?>, <?=($trenutni_semestar+1)?>. semestar bez kolizije! Možete popuniti <a href="?sta=student/ugovoroucenju">Ugovor o učenju</a> kako biste izabrali izborne predmete.</p>
			<?
			return;
		} // else ide na koliziju

	} else if ($uslov_ukupan_broj_nepolozenih> 0 && count($predmet_naziv)>$uslov_ukupan_broj_nepolozenih) {
		?><p>Trenutno ne ispunjavate uslov za koliziju jer imate <?=count($predmet_naziv)?> nepoložena predmeta, a možete imati maksimalno <?=$uslov_ukupan_broj_nepolozenih?>.</p>
		<?
/*		<p>Prema trenutnoj evidenciji, Vaši nepoloženi predmeti su:</p>
		<ul><?
		foreach ($predmet_naziv as $id=>$naziv) {
			print "<li>$naziv";
			if ($predmet_stari[$id]) print " (predmet sa nižih godina, ne može se prenijeti)";
			print "</li>\n";
		}
		?>
		</ul>*/
		?>
		<p>Ukoliko je ovo greška i položili ste neki od ovih predmeta, hitno kontaktirajte predmetnog nastavnika kako bi unio ocjenu!!!</p>
		<?
//		return;
	}

	// Nemoguće je da student ima previše starih predmeta (trenutno: više od jedan) jer onda ne bi mogao upisati godinu. Eventualno je mogao koristiti koliziju prošle godine, ali bi onda varijabla $trenutni_semestar bila niža pa ti predmeti ne bi bili "stari"


	// Da li već postoji zahtjev za koliziju?
	$postoji_zahtjev = db_get("select count(*) from kolizija where student=$userid and akademska_godina=$zagodinu");
	if ($postoji_zahtjev > 0) {
		?>
		<p>Već imate jedan zahtjev za koliziju. Možete ponovo popuniti zahtjev, pri čemu se stari briše.</p>
		<p>Kliknite na jedan od linkova ispod za printanje zahtjeva za:<br>
		- <a href="?sta=student/kolizijapdf&amp;semestar=1">zimski semestar</a><br>
		- <a href="?sta=student/kolizijapdf&amp;semestar=2">ljetnji semestar</a></p>
		<?
	}



	// UNOS U BAZU

	// Akcija za unos podataka o koliziji u bazu
	if ($_POST['akcija']=="korak2" && $studij>0 && $godina>0) {
		// Provjeravamo da li je odabran ispravan broj ECTSova po semestru
		foreach ($predmet_ects as $id => $ects) {
			if ($id==$_POST['prenosi'] || $_POST["polaze-$id"]) continue;
			if ($predmet_semestar[$id]%2==1) $zimskiects += $ects;
			else $ljetnjiects += $ects;
		}
		$polozeno_ects_zima = 30-$zimskiects;
		$polozeno_ects_ljeto = 30-$ljetnjiects;

		$zze=$zle=0;
		$predmeti1=$predmeti2=array();
		foreach($_POST as $key => $value) {
			if (substr($key,0,10)=="obavezni1-" || substr($key,0,10)=="iizborni1-") {
				$predmet=intval(substr($key,10));
				$q200 = db_query("select pp.ects from pasos_predmeta pp, plan_studija_predmet psp where psp.pasos_predmeta=pp.id and pp.predmet=$predmet and psp.plan_studija=$najnoviji_plan");
				if (db_num_rows($q200)<1) {
					$q200 = db_query("select pp.ects from pasos_predmeta pp, plan_studija_predmet psp, plan_izborni_slot pis where psp.plan_izborni_slot=pis.id and pis.pasos_predmeta=pp.id and pp.predmet=$predmet and psp.plan_studija=$najnoviji_plan");
					if (db_num_rows($q200)<1) {
						niceerror("Izabran je nepoznat predmet!");
						return;
					}
				}
				$zze+=db_result($q200,0,0);
				array_push($predmeti1,$predmet);
			}
			if (substr($key,0,10)=="obavezni2-" || substr($key,0,10)=="iizborni2-") {
				$predmet=intval(substr($key,10));
				$q200 = db_query("select pp.ects from pasos_predmeta pp, plan_studija_predmet psp where psp.pasos_predmeta=pp.id and pp.predmet=$predmet and psp.plan_studija=$najnoviji_plan");
				if (db_num_rows($q200)<1) {
					$q200 = db_query("select pp.ects from pasos_predmeta pp, plan_studija_predmet psp, plan_izborni_slot pis where psp.plan_izborni_slot=pis.id and pis.pasos_predmeta=pp.id and pp.predmet=$predmet and psp.plan_studija=$najnoviji_plan");
					if (db_num_rows($q200)<1) {
						niceerror("Izabran je nepoznat predmet!");
						return;
					}
				}
				$zle+=db_result($q200,0,0);
				array_push($predmeti2,$predmet);
			}
		}
		if ($zze > ($limit_ects_zima - $zimskiects) && $zze > 0 || $zle > ($limit_ects_ljeto - $ljetnjiects) && $zle > 0) {
			niceerror("Izabrano je previše ECTS kredita u jednom od semestara! $zze>".($limit_ects_zima-$zimskiects)." $zle>".($limit_ects_ljeto-$ljetnjiects));
			return;
		}
		if ($uslov_ects_zima>0 && $polozeno_ects_zima<$uslov_ects_zima && $zze>0) {
			niceerror("Nemate pravo na koliziju u zimskom semestru jer imate $polozen_ects_zima kredita što je manje od $uslov_ects_zima");
			return;
		}
		if ($uslov_ects_ljeto>0 && $polozeno_ects_ljeto<$uslov_ects_ljeto && $zle>0) {
			niceerror("Nemate pravo na koliziju u ljetnjem semestru jer imate $polozen_ects_ljeto kredita što je manje od $uslov_ects_ljeto");
			return;
		}
		
		// Provjera kapaciteta
		foreach(array_merge($predmeti1, $predmeti2) as $predmet) {
			if (provjeri_kapacitet($userid, $predmet, $zagodinu, $trenutni_studij, true) == 0) {
	 			$q117 = db_query("SELECT naziv FROM predmet WHERE id=$predmet");
				niceerror("Predmet ".db_result($q117,0,0)." se ne može izabrati jer su dostupni kapaciteti za taj predmet popunjeni");
				zamgerlog2("popunjen kapacitet za predmet", $predmet);
				return;
			}

		}

		// Sve ok, ubacujemo
		$q210 = db_query("delete from kolizija where student=$userid and akademska_godina=$zagodinu"); // Brisem prethodnu koliziju
		$q212 = db_query("delete from septembar where student=$userid and akademska_godina=$zagodinu"); // Brisem prethodnu koliziju
		foreach($predmeti1 as $predmet) {
			$q210 = db_query("insert into kolizija set student=$userid, akademska_godina=$zagodinu, semestar=1, predmet=$predmet");
		}
		foreach($predmeti2 as $predmet) {
			$q210 = db_query("insert into kolizija set student=$userid, akademska_godina=$zagodinu, semestar=2, predmet=$predmet");
		}
		foreach ($predmet_ects as $id => $ects) {
			if (isset($_POST["polaze-$id"])) {
				$q210 = db_query("insert into septembar set student=$userid, akademska_godina=$zagodinu, predmet=$id");
			}
		}
		zamgerlog("student u$userid kreirao zahtjev za koliziju", 2); // 2 - edit
		zamgerlog2("kreirao zahtjev za koliziju"); // 2 - edit
		nicemessage("Kreirali ste zahtjev za koliziju.");
		?>
		<p>Studentska služba će vas upisati na odgovarajuće predmete prilikom upisa na godinu.</p>
		<p>Kliknite na jedan od linkova ispod za printanje zahtjeva za:<br>
		- <a href="?sta=student/kolizijapdf&amp;semestar=1">zimski semestar</a><br>
		- <a href="?sta=student/kolizijapdf&amp;semestar=2">ljetnji semestar</a></p>
		<?
		return;
	}



	// PRVI EKRAN - IZBOR STUDIJA I PREDMETA ZA PRENOS

	// Ako je bilo koji od primljenih parametara nula, dajemo izbor studija, godine i prenesenog predmeta
	if (!$_POST['akcija']=="korak1" || $studij==0 || $godina==0 || $prenosi==0) {

		// Odredjujemo ciljni studij
		$studij=$trenutni_studij;
		$godina=intval(($trenutni_semestar+3)/2);

		
		if ($trenutni_semestar>=$strajanje) {
			$q20 = db_query("select id, naziv from studij where moguc_upis=1 and institucija=$sinstitucija and tipstudija>$stipstudija"); // FIXME pretpostavka je da su tipovi studija poredani po ciklusima
			if (db_num_rows($q20)>0) {
				$studij = db_result($q20,0,0);
				$snazivstudija = db_result($q20,0,1);
				$godina=1;
			} else {
				// Nema gdje dalje... postavljamo sve na nulu
				$studij=0;
				$godina=1;
			}
		}

		// Određujemo najnoviji plan, ali ovaj put za ciljni studij
		// FIXME: prebaciti gore unutar if(mys...($q2)>0) {    ???
		$q6 = db_query("select id from plan_studija where studij=$studij order by godina_vazenja desc limit 1");
		if (db_num_rows($q6)<1) { 
			niceerror("Nepostojeći studij");
			return;
		}
		$najnoviji_plan = db_result($q6,0,0);

		?>
		<form action="index.php" method="POST" name="mojaforma">
		<input type="hidden" name="sta" value="student/kolizija">
		<input type="hidden" name="akcija" value="korak1">
		<?

		// Ispis ponude za polaganje
		if (count($predmet_naziv)>3) {
			?>
			<p><b>Izaberite predmete koje ste položili ili ih planirate položiti do kraja septembarskog roka:</b><br />
			<?

			foreach ($predmet_naziv as $id=>$naziv) {
				?>
				<input type="checkbox" name="polaze-<?=$id?>" <?
				?>><?=$naziv?> (<?=$predmet_semestar[$id]?>. semestar, <?=$predmet_ects[$id]?> ECTS)<br />
				<?
			}
	
			?>
			<p>VAŽNO: Ako ne položite neki od ovih predmeta, Vaš zahtjev za koliziju se poništava!</p>
			<?
		}

		// Ispis izbora predmeta koji će student prenijeti

		// Ima li ijedan predmet koji se može prenijeti?
		$ima=0;
		foreach ($predmet_stari as $id => $stari) {
			if ($stari==0) $ima=1;
		}
		$ima=0; // Onemogućen "prenos redovno"

		if ($ima) {
			?>
			<p><b>Izaberite predmet koji prenosite redovno:</b><br />
			<?

			foreach ($predmet_naziv as $id=>$naziv) {
				?>
				<input type="radio" name="prenosi" value="<?=$id?>"<?
					if ($predmet_stari[$id]) print " DISABLED";
				?>><?=$naziv?> (<?=$predmet_semestar[$id]?>. semestar, <?=$predmet_ects[$id]?> ECTS)<br />
				<?
			}
	
			?>
			<p>Ostale predmete prenosite "koliziono" što znači da u odgovarajućem semestru (ljetnom ili zimskom) ne možete slušati odgovarajući broj ECTS kredita.</p>
			<?
		} else {
			print '<input type="hidden" name="prenosi" value="-1">';
		}


		// Izbor studija
		?>
		<p><hr><br />
		Studij: <select name="studij" id="studij" onchange="javascript:refresh()"><option></option>
		<?
	
		// Spisak studija
		$q30 = db_query("select id, naziv from studij where moguc_upis=1 order by tipstudija, naziv");
		while ($r30 = db_fetch_row($q30)) {
			print "<option value=\"$r30[0]\"";
			if ($r30[0]==$studij) print " selected";
			print ">$r30[1]</option>\n";
		}

		?>
		</select></p>
		<p>Godina studija: <input type="text" name="godina" id="godina" value="<?=$godina?>"></p>
		<p><input type="submit" value="Dalje >>"></p>
		</form>
	
	
		<?
	
		return;

	} // if ($studij==0 || $godina==0 || $prenosi==0)





	// ISPIS IZBORA PREDMETA
	// (odnosno korak 2 wizarda)

	?>
	<form action="index.php" method="POST" name="mojaforma">
	<input type="hidden" name="sta" value="student/kolizija">
	<input type="hidden" name="akcija" value="korak2">
	<?


	// Odredjujemo najnoviji plan studija za odabrani studij
	$q6 = db_query("select id from plan_studija where studij=$studij order by godina_vazenja desc limit 1");
	if (db_num_rows($q6)<1) { 
		niceerror("Nepostojeći studij");
		return;
	}
	$najnoviji_plan = db_result($q6,0,0);

	foreach ($predmet_naziv as $id => $naziv) {
		if ($_POST["polaze-$id"] && $id==$_POST['prenosi']) {
			niceerror("Pogrešni podaci!");
			?>
			Ne možete istovremeno izabrati da ćete predmet položiti i da ga prenosite.
			<?
			return; 
		}
	}
	$br_polozice=0;
	foreach ($predmet_naziv as $id => $naziv) {
		if ($_POST["polaze-$id"])
			$br_polozice++;
	}
	if ($br_polozice > count($predmet_naziv)-2) {
			niceerror("Pa nećete sve položiti :)");
			?>
			<p>Vratite se nazad i ostavite barem dva predmeta koja nećete položiti kako biste bili kolizija. Ako sve položite onda niste kolizija.</p>
			<?
			return; 
	}


	// Ispisujemo izabrani studij, godinu i preneseni predmet
	?>
	<input type="hidden" name="studij" value="<?=$studij?>">
	<input type="hidden" name="godina" value="<?=$godina?>">

	<p>Kolizija za studij <b><?=$naziv_studija?></b> (<b><?=$godina?>.</b> godina)<br />
	<?
	foreach ($predmet_naziv as $id => $naziv) {
		if ($_POST["polaze-$id"]) {
			?>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Položiće: <b><?=$naziv?></b> (<?=$predmet_ects[$id]?> ECTS)<br />
			<input type="hidden" name="polaze-<?=$id?>" value="on">
			<?
		}
	}
	foreach ($predmet_naziv as $id => $naziv) {
		if ($id==$_POST['prenosi']) { 
			?>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Preneseni predmet: <b><?=$naziv?></b> (<?=$predmet_ects[$id]?> ECTS)<br />
			<input type="hidden" name="prenosi" value="<?=$prenosi?>">
			<?
		}
	}
	$broj_kolizionih_predmeta = 0;
	foreach ($predmet_naziv as $id => $naziv) {
		if ($id != $_POST['prenosi'] && !$_POST["polaze-$id"]) {
			?>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Kolizioni predmet: <b><?=$naziv?></b> (<?=$predmet_ects[$id]?> ECTS)<br />
			<?
			$broj_kolizionih_predmeta++;
		}
	}

	if ($uslov_ukupan_broj_nepolozenih > 0 && $broj_kolizionih_predmeta>$uslov_ukupan_broj_nepolozenih) {
		?>
		<? nicemessage("Izabrali ste više od $uslov_ukupan_broj_nepolozenih koliziona predmeta"); ?>
		<p>Možete imati maksimalno <?=$uslov_ukupan_broj_nepolozenih?> predmeta u koliziji, ostale predmete trebate položiti.</p>
		<p><a href="?sta=student/kolizija">Nazad na izbor studija, godine i prenesenog predmeta</a></p>
		<?
		return;
	}

	// Računamo broj ECTSova u zimskom i ljetnjem periodu
	foreach ($predmet_ects as $id => $ects) {
		if ($id==$_POST['prenosi'] || $_POST["polaze-$id"]) continue;
		if ($predmet_semestar[$id]%2==1) $zimskiects += $ects;
		else $ljetnjiects += $ects;
	}
	$polozeno_ects_zima = 30-$zimskiects;
	$polozeno_ects_ljeto = 30-$ljetnjiects;
	
	if ($uslov_ects_zima>0 && $polozeno_ects_zima<$uslov_ects_zima && $uslov_ects_ljeto>0 && $polozeno_ects_ljeto<$uslov_ects_ljeto) {
		?><p>Trenutno ne ispunjavate uslov za koliziju.</p>
		<p>Potrebno je da ste položili minimalno <?=$uslov_ects_zima?> ECTS kredita u zimskom semestru (vi imate <?=$polozeno_ects_zima?>) te minimalno <?=$uslov_ects_ljeto?> ECTS kredita u ljetnjem semestru (vi imate <?=$polozeno_ects_ljeto?>).</p>
		<?
		return;
	}



	// JavaScript

	?>

	<SCRIPT language="JavaScript">
	// Funckija koja ne dozvoljava da se selektuje različit broj polja od navedenog
	var globalna;
	function jedanod(slot, clicked) {
		var template = "izborni-"+slot;
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
	function revertuj() {
		if (globalna.checked) globalna.checked=false;
		else globalna.checked=true;
	}
	function updateects(sta, zakoliko, radio) {
		var layer;
		if (sta==0) layer=document.getElementById('zimskiizbor');
		else layer=document.getElementById('ljetnjiizbor');

		var vrijednost=parseFloat(layer.innerHTML);

		if (radio.checked) vrijednost+=zakoliko;
		else vrijednost-=zakoliko;

		layer.innerHTML=vrijednost;

		if (sta==0 && vrijednost><?=($limit_ects_zima-$zimskiects)?>)
			document.getElementById('zimskicrveno').style.color="red";
		else if (sta==0)
			document.getElementById('zimskicrveno').style.color="black";

		if (sta==1 && vrijednost><?=($limit_ects_ljeto-$ljetnjiects)?>)
			document.getElementById('ljetnjicrveno').style.color="red";
		else if (sta==1)
			document.getElementById('ljetnjicrveno').style.color="black";

	}
	function provjeri_submit() {
		var zimski=parseFloat(document.getElementById('zimskiizbor').innerHTML);
		var ljetnji=parseFloat(document.getElementById('ljetnjiizbor').innerHTML);
		if (zimski><?=($limit_ects_zima-$zimskiects)?> && zimski > 0 || ljetnji><?=($limit_ects_ljeto-$ljetnjiects)?> && ljetnji > 0) {
			alert ("Izabrali ste previše ECTS kredita u koliziji! "+zimski);
			return false;
		}
		return true;
	}
	</SCRIPT>
	<?



	// Pomoćna PHP funkcija koja ispisuje jedan predmet
	function dajpredmet($pasos_predmeta, $studij, $najnoviji_plan, $zagodinu, $izborni, $zimski) {
		global $userid;

		$q50 = db_query("select naziv, ects, predmet from pasos_predmeta where id=$pasos_predmeta");
		if (db_num_rows($q50)<1) return;
		$pnaziv = db_result($q50,0,0);
		$pects = db_result($q50,0,1);
		$predmet = db_result($q50,0,2);
		
		// Ako se ne nudi u koliziji, preskačemo
		$kap = db_query_assoc("SELECT kapacitet, kapacitet_izborni, kapacitet_kolizija FROM ugovoroucenju_kapacitet WHERE predmet=$predmet AND akademska_godina=$zagodinu");
		if ($kap && ($kap['kapacitet'] == 0 || $kap['kapacitet_izborni'] == 0 || $kap['kapacitet_kolizija'] == 0)) return;

		// Ako je vec položen predmet preskačemo
		$q45 = db_query("select count(*) from konacna_ocjena where student=$userid and predmet=$predmet");
		if (db_result($q45,0,0)>0) return;

		// Završni rad se ne može izabrati u koliziji
		if ($pects >= 12) return;

		if ($izborni) {
			$dodaj = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			$cbid = "iizborni";
		} else {
			$dodaj = "";
			$cbid = "obavezni";
		}
		if ($zimski) {
			$jsid = "0";
			$cbid .= "1"; 
		} else { $jsid = "1"; $cbid .= "2"; } // bezveze :( popraviti
		

		// Provjera preduvjeta -- za sada disabled
		$preduvjeti = provjeri_preduvjete($predmet, $userid, $najnoviji_plan);
		for ($i=0; $i<count($preduvjeti); $i++) {
			if (isset($_POST["polaze-" . $preduvjeti[$i]])) array_splice($preduvjeti, $i, 1);
		}
		if (!empty($preduvjeti)) {
			?>
			<?=$dodaj?><?=$pnaziv?> (<?=$pects?> ECTS) - ne možete izabrati ovaj predmet jer niste položili sljedeće predmete:
			<?
			foreach ($preduvjeti as $preduvjet) {
				$naziv_preduvjeta = db_get("SELECT pp.naziv FROM plan_studija_predmet psp, pasos_predmeta pp WHERE psp.plan_studija=$najnoviji_plan AND psp.pasos_predmeta=pp.id AND pp.predmet=$preduvjet AND psp.obavezan=1");
				if (!$naziv_preduvjeta)
					$naziv_preduvjeta = db_get("SELECT pp.naziv from pasos_predmeta pp, plan_studija_predmet psp, plan_izborni_slot pis where pp.predmet=$preduvjet and pp.id=pis.pasos_predmeta and pis.id=psp.plan_izborni_slot and psp.plan_studija=$najnoviji_plan");
				print $naziv_preduvjeta.", ";
			}
			print "<br>\n";
		} else {
			?>
			<?=$dodaj?><input type="checkbox" name="<?=$cbid?>-<?=$predmet?>" onchange="javascript:updateects(<?=$jsid?>, <?=$pects?>, this)"> <?=$pnaziv?> (<?=$pects?> ECTS)<br>
			<?
		}

	}


	// Ispis

	?>
	<p><a href="?sta=student/kolizija">Nazad na izbor studija, godine i prenesenog predmeta</a></p>

	<p><hr><br />
	Izaberite predmete koje želite slušati. Možete izabrati maksimalno <?
		if ($uslov_ects_zima==0 || $polozeno_ects_zima>=$uslov_ects_zima) {
			print ($limit_ects_zima-$zimskiects) . " kredita na zimskom semestru ";
			if ($uslov_ects_ljeto==0 || $polozeno_ects_ljeto>=$uslov_ects_ljeto) print "i ";
		}
		if ($uslov_ects_ljeto==0 || $polozeno_ects_ljeto>=$uslov_ects_ljeto)
			print ($limit_ects_ljeto-$ljetnjiects) . " kredita na ljetnom semestru";
	?>.</p>
	<p>NAPOMENA: Morate popuniti i <a href="?sta=student/ugovoroucenju">Ugovor o učenju</a>, ali za prethodnu godinu (za <?=($godina-1)?>. godinu studija).</p>
	<?
	
	if ($uslov_ects_zima>0 && $polozeno_ects_zima<$uslov_ects_zima) {
		?><p><b>Ne ispunjavate uslov za koliziju u zimskom semestru (potrebno minimalno <?=$uslov_ects_zima?> ECTS kredita a vi imate <?=$polozeno_ects_zima?>)</b></p>
		<span style="display:none">
		<?
	}
	
	?>
	<p><span id="zimskicrveno"><b><?=($godina*2-1)?>. semestar (izabrano <span id="zimskiizbor">0</span> kredita, max. <?=($limit_ects_zima-$zimskiects)?>):</b></span><br />
	<?

	// ZIMSKI SEMESTAR

	$q40 = db_query("select pasos_predmeta, obavezan, plan_izborni_slot from plan_studija_predmet where plan_studija=$najnoviji_plan and semestar=".($godina*2-1)." order by obavezan desc, pasos_predmeta");
	$is_bilo=array();
	while ($r40 = db_fetch_row($q40)) {
		if ($r40[1]==1) { // obavezan
			dajpredmet($r40[0], $studij, $najnoviji_plan, $zagodinu, false, true);
		} else { // izborni

			if (count($is_bilo)==0) {
				print "Izborni predmeti:<br />\n";
			}

			// Da li je već bio izborni slot?
			if (in_array($r40[2], $is_bilo)) continue;
			array_push($is_bilo, $r40[2]);

			$q60 = db_query("select pasos_predmeta from plan_izborni_slot where id=$r40[2]");
			while ($r60 = db_fetch_row($q60)) {
				dajpredmet($r60[0], $studij, $najnoviji_plan, $zagodinu, true, true);
			}
		}
	}
	print "</p>\n";
	
	if ($uslov_ects_zima>0 && $polozeno_ects_zima<$uslov_ects_zima) {
		?></span><?
	}


	// LJETNJI SEMESTAR

	if ($uslov_ects_ljeto>0 && $polozeno_ects_ljeto<$uslov_ects_ljeto) {
		?><p><b>Ne ispunjavate uslov za koliziju u ljetnjem semestru (potrebno minimalno <?=$uslov_ects_ljeto?> ECTS kredita a vi imate <?=$polozeno_ects_ljeto?>)</b></p>
		<span style="display:none">
		<?
	}
	
	?>
	<p><span id="ljetnjicrveno"><b><?=($godina*2)?>. semestar (izabrano <span id="ljetnjiizbor">0</span> kredita, max. <?=($limit_ects_ljeto-$ljetnjiects)?>):</b></span><br />
	<?

	$q40 = db_query("select pasos_predmeta, obavezan, plan_izborni_slot from plan_studija_predmet where plan_studija=$najnoviji_plan and semestar=".($godina*2)." order by obavezan desc, pasos_predmeta");
	$is_bilo=array();
	while ($r40 = db_fetch_row($q40)) {
		if ($r40[1]==1) { // obavezan
			dajpredmet($r40[0], $studij, $najnoviji_plan, $zagodinu, false, false);
		} else { // izborni

			if (count($is_bilo)==0) {
				print "Izborni predmeti:<br />\n";
			}

			// Da li je već bio izborni slot?
			if (in_array($r40[2], $is_bilo)) continue;
			array_push($is_bilo, $r40[2]);

			$q60 = db_query("select pasos_predmeta from plan_izborni_slot where id=$r40[2]");
			while ($r60 = db_fetch_row($q60)) {
				dajpredmet($r60[0], $studij, $najnoviji_plan, $zagodinu, true, false);
			}
		}
	}

	print "</p>\n";

	if ($uslov_ects_ljeto>0 && $polozeno_ects_ljeto<$uslov_ects_ljeto) {
		?></span><?
	}

	?>
	<p>
	<input type="submit" value="Potvrđujem koliziju" onclick="javascript:return provjeri_submit()"></form></p>
	<?
	

}

?>
