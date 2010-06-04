<?

// STUDENT/KOLIZIJA - modul koji olaksava upis godine na koliziju

// v4.0.9.1 (2009/07/16) + Novi modul za koliziju




function student_kolizija() {

	global $userid;

	// Naslov
	?>
	<h3>Kolizija</h3>
	<?
	// Za koju godinu se prijavljuje?
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
		if (mysql_num_rows($q3)<1) {
			// Definisana je samo jedna akademska godina u bazi
			nicemessage("U ovom trenutku nije aktiviran upis u sljedeću akademsku godinu.");
			return;
		}
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
	$prenosi = intval($_REQUEST['prenosi']);


	// Provjera ispravnosti podataka
	if ($studij!=0) {
		$q5 = myquery("select naziv, zavrsni_semestar from studij where id=$studij");
		if (mysql_num_rows($q5)<1) {
			niceerror("Neispravan studij");
			$studij=0;
			unset($_POST['akcija']);
		}
		else if ($godina<1 || $godina>mysql_result($q5,0,1)/2) {
			$godina=1;
		}
		$naziv_studija=mysql_result($q5,0,0);
	} else {
		unset($_POST['akcija']);
	}


	// Šta trenutno sluša student?
	$q10 = myquery("select ss.studij, ss.semestar, s.zavrsni_semestar, s.institucija, s.tipstudija, s.naziv, ss.plan_studija from student_studij as ss, studij as s where ss.student=$userid and ss.akademska_godina=$proslagodina and ss.studij=s.id order by semestar desc limit 1");
	if (mysql_num_rows($q10)>0) {
		$trenutni_studij=mysql_result($q10,0,0);
		$trenutni_semestar=mysql_result($q10,0,1);
		// Podaci koji nam trebaju radi prelaska sa BSc na MSc
		$szavrsni_semestar=mysql_result($q10,0,2);
		$sinstitucija=mysql_result($q10,0,3);
		$stipstudija=mysql_result($q10,0,4);
		$snazivstudija=mysql_result($q10,0,5);
		$najnoviji_plan=mysql_result($q10,0,6);
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
	$q30 = myquery("select semestar, predmet, obavezan from plan_studija where godina_vazenja=$najnoviji_plan and studij=$trenutni_studij and semestar<=$trenutni_semestar order by semestar");
	while ($r30 = mysql_fetch_row($q30)) {
		if ($r30[2]==1) { // obavezan
			$q50 = myquery("select count(*) from konacna_ocjena where student=$userid and predmet=$r30[1] and ocjena>5");
			if (mysql_result($q50,0,0)<1) {
				$q40 = myquery("select naziv,ects from predmet where id=$r30[1]");
				$predmet_naziv[$r30[1]] = mysql_result($q40,0,0);
				$predmet_ects[$r30[1]] = mysql_result($q40,0,1);
				$predmet_semestar[$r30[1]] = $r30[0];
				if ($r30[0]<$trenutni_semestar-1) $predmet_stari[$r30[1]]=1; // predmet sa nizih godina se ne moze prenijeti
				else $predmet_stari[$r30[1]]=0;
			}
		} else { // izborni predmet
			$q60 = myquery("select p.id, p.naziv, p.ects from izborni_slot as iz, predmet as p where iz.id=$r30[1] and iz.predmet=p.id");
			$naziv="Izborni predmet ("; // Kombinovani naziv svih predmeta
			$polozio=0;
			$ects = 100; // treba nam minimalni ects
			while ($r60 = mysql_fetch_row($q60)) {
				if (strlen($naziv)>18) $naziv .= ", ";
				$naziv .= $r60[1];

				if ($r60[2]<$ects) $ects=$r60[2];

				$q70 = myquery("select count(*) from konacna_ocjena where student=$userid and predmet=$r60[0] and ocjena>5");
				if (mysql_result($q70,0,0)>0) $polozio=1;
			}

			if ($polozio==0) { // nije polozio nijedan moguci predmet
				$predmet_naziv[$r30[1]] = $naziv . ")";
				$predmet_ects[$r30[1]] = $ects;
				$predmet_semestar[$r30[1]] = $r30[0];
				if ($r30[0]<$trenutni_semestar-1) $predmet_stari[$r30[1]]=1;
				else $predmet_stari[$r30[1]]=0;
			}
		}
	}

	// Da li je već u koliziji?
	$q15 = myquery("select count(*) from student_predmet as sp, ponudakursa as pk where sp.student=$userid and sp.predmet=pk.id and pk.studij=$trenutni_studij and pk.semestar>$trenutni_semestar and pk.akademska_godina=$zagodinu");
	if (mysql_result($q15,0,0)>0) {
		?><p>Vaš zahtjev za koliziju je prihvaćen. Upisani ste u odabrane predmete (vidjećete ih na spisku predmeta kada zvanično počne sljedeća akademska godina).</p><?
		return;
	}

	// Uslov za upis u sljedeću godinu 
	if (count($predmet_naziv)==0) {
		?><p>Vi ste dali uslov za upis u <?=$snazivstudija?>, <?=($trenutni_semestar+1)?>. semestar bez kolizije! Možete popuniti <a href="?sta=student/ugovoroucenju">Ugovor o učenju</a> kako biste izabrali izborne predmete.</p><?
		return;

	} else if (count($predmet_naziv)<=1) {
		$da_stari_je=0;
		foreach ($predmet_stari as $stari) { if ($stari) $da_stari_je=1; }
		if ($da_stari_je==0) {
			?><p>Vi ste dali uslov za upis u <?=$snazivstudija?>, <?=($trenutni_semestar+1)?>. semestar bez kolizije! Možete popuniti <a href="?sta=student/ugovoroucenju">Ugovor o učenju</a> kako biste izabrali izborne predmete.</p><?
			return;
		} // else ide na koliziju

	} else if (count($predmet_naziv)>3) {
		?><p>Ne ispunjavate uslov za koliziju jer imate <?=count($predmet_naziv)?> nepoložena predmeta, a možete imati maksimalno tri.</p>
		<p>Prema trenutnoj evidenciji, Vaši nepoloženi predmeti su:</p>
		<ul><?
		foreach ($predmet_naziv as $id=>$naziv) {
			print "<li>$naziv";
			if ($predmet_stari[$id]) print " (predmet sa nižih godina, ne može se prenijeti)";
			print "</li>\n";
		}
		?>
		</ul>
		<p>Ukoliko je ovo greška i položili ste neki od ovih predmeta, hitno kontaktirajte predmetnog nastavnika kako bi unio ocjenu!!!</p>
		<?
		return;
	}

	// Nemoguće je da student ima previše starih predmeta (trenutno: više od jedan) jer onda ne bi mogao upisati godinu. Eventualno je mogao koristiti koliziju prošle godine, ali bi onda varijabla $trenutni_semestar bila niža pa ti predmeti ne bi bili "stari"


	// Da li već postoji zahtjev za koliziju?
	$q18 = myquery("select count(*) from kolizija where student=$userid and akademska_godina=$zagodinu");
	if (mysql_result($q18,0,0)>0) {
		?>
		<p>Već imate jedan zahtjev za koliziju. Možete ponovo popuniti zahtjev, pri čemu se stari briše.</p>
		<?
	}



	// UNOS U BAZU

	// Akcija za unos podataka o koliziji u bazu
	if ($_POST['akcija']=="korak2" && $studij>0 && $godina>0 && $prenosi>0) {
		// Provjeravamo da li je odabran ispravan broj ECTSova po semestru
		foreach ($predmet_ects as $id => $ects) {
			if ($id==$_POST['prenosi']) continue;
			if ($predmet_semestar[$id]%2==1) $zimskiects += $ects;
			else $ljetnjiects += $ects;
		}

		$zze=$zle=0;
		$predmeti1=$predmeti2=array();
		foreach($_POST as $key => $value) {
			if (substr($key,0,10)=="obavezni1-" || substr($key,0,10)=="izborni1--") {
				$predmet=intval(substr($key,10));
				$q200 = myquery("select ects from predmet where id=$predmet");
				if (mysql_num_rows($q200)<1) {
					niceerror("Izabran je nepoznat predmet!");
					return;
				}
				$zze+=mysql_result($q200,0,0);
				array_push($predmeti1,$predmet);
			}
			if (substr($key,0,10)=="obavezni2-" || substr($key,0,10)=="izborni2--") {
				$predmet=intval(substr($key,10));
				$q200 = myquery("select ects from predmet where id=$predmet");
				if (mysql_num_rows($q200)<1) {
					niceerror("Izabran je nepoznat predmet!");
					return;
				}
				$zle+=mysql_result($q200,0,0);
				array_push($predmeti2,$predmet);
			}
		}
		if ($zze>(30-$zimskiects) || $zle>(30-$ljetnjiects)) {
			niceerror("Izabrano je previše ECTS kredita u jednom od semestara! $zze>$zimskiects $zle>$ljetnjiects");
			return;
		}

		// Sve ok, ubacujemo
		$q210 = myquery("delete from kolizija where student=$userid and akademska_godina=$zagodinu"); // Brisem prethodnu koliziju
		foreach($predmeti1 as $predmet) {
			$q210 = myquery("insert into kolizija set student=$userid, akademska_godina=$zagodinu, semestar=1, predmet=$predmet");
		}
		foreach($predmeti2 as $predmet) {
			$q210 = myquery("insert into kolizija set student=$userid, akademska_godina=$zagodinu, semestar=2, predmet=$predmet");
		}
		zamgerlog("student u$userid kreirao zahtjev za koliziju", 2); // 2 - edit
		nicemessage("Kreirali ste zahtjev za koliziju.");
		print "Studentska služba će vas upisati na odgovarajuće predmete prilikom upisa na godinu.";
		return;
	}



	// PRVI EKRAN - IZBOR STUDIJA I PREDMETA ZA PRENOS

	// Ako je bilo koji od primljenih parametara nula, dajemo izbor studija, godine i prenesenog predmeta
	if (!$_POST['akcija']=="korak1" || $studij==0 || $godina==0 || $prenosi==0) {

		// Odredjujemo ciljni studij
		$studij=$trenutni_studij;
		$godina=intval(($trenutni_semestar+3)/2);

		
		if ($trenutni_semestar>=$szavrsni_semestar) {
			$q20 = myquery("select id, naziv from studij where moguc_upis=1 and institucija=$sinstitucija and tipstudija>$stipstudija"); // FIXME pretpostavka je da su tipovi studija poredani po ciklusima
			if (mysql_num_rows($q20)>0) {
				$studij = mysql_result($q20,0,0);
				$snazivstudija = mysql_result($q20,0,1);
				$godina=1;
			} else {
				// Nema gdje dalje... postavljamo sve na nulu
				$studij=0;
				$godina=1;
			}
		}

		// Određujemo najnoviji plan, ali ovaj put za ciljni studij
		// FIXME: prebaciti gore unutar if(mys...($q2)>0) {    ???
		$q6 = myquery("select godina_vazenja from plan_studija where studij=$studij order by godina_vazenja desc limit 1");
		if (mysql_num_rows($q6)<1) { 
			niceerror("Nepostojeći studij");
			return;
		}
		$najnoviji_plan = mysql_result($q6,0,0);


		// Ispis izbora predmeta koji će student prenijeti
		?>
		<form action="index.php" method="POST" name="mojaforma">
		<input type="hidden" name="sta" value="student/kolizija">
		<input type="hidden" name="akcija" value="korak1">
		<?

		// Ima li ijedan predmet koji se može prenijeti?
		$ima=0;
		foreach ($predmet_stari as $id => $stari) {
			if ($stari==0) $ima=1;
		}

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
		$q30 = myquery("select id, naziv from studij where moguc_upis=1 order by tipstudija, naziv");
		while ($r30 = mysql_fetch_row($q30)) {
			print "<option value=\"$r30[0]\"";
			if ($r30[0]==$studij) print " selected";
			print ">$r30[1]</option>\n";
		}

// HACK za neispravno unesene godine na ETFu
if (($godina>=1 && $godina<4) && $studij>6) $godina+=3;


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
	$q6 = myquery("select godina_vazenja from plan_studija where studij=$studij order by godina_vazenja desc limit 1");
	if (mysql_num_rows($q6)<1) { 
		niceerror("Nepostojeći studij");
		return;
	}
	$najnoviji_plan = mysql_result($q6,0,0);


	// Ispisujemo izabrani studij, godinu i preneseni predmet
	?>
	<input type="hidden" name="studij" value="<?=$studij?>">
	<input type="hidden" name="godina" value="<?=$godina?>">

	<p>Kolizija za studij <b><?=$naziv_studija?></b> (<b><?=$godina?>.</b> godina)<br />
	<?
	foreach ($predmet_naziv as $id => $naziv) {
		if ($id==$_POST['prenosi']) { 
			?>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Preneseni predmet: <b><?=$naziv?></b> (<?=$predmet_ects[$id]?> ECTS)<br />
			<input type="hidden" name="prenosi" value="<?=$prenosi?>">
			<?
		}
	}
	foreach ($predmet_naziv as $id => $naziv) {
		if ($id!=$_POST['prenosi']) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Kolizioni predmet: <b>$naziv</b> (".$predmet_ects[$id]." ECTS) <br />\n";
	}

	// Računamo broj ECTSova u zimskom i ljetnjem periodu
	foreach ($predmet_ects as $id => $ects) {
		if ($id==$_POST['prenosi']) continue;
		if ($predmet_semestar[$id]%2==1) $zimskiects += $ects;
		else $ljetnjiects += $ects;
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

		if (sta==0 && vrijednost><?=(30-$zimskiects)?>)
			document.getElementById('zimskicrveno').style.color="red";
		else if (sta==0)
			document.getElementById('zimskicrveno').style.color="black";

		if (sta==1 && vrijednost><?=(30-$ljetnjiects)?>)
			document.getElementById('ljetnjicrveno').style.color="red";
		else if (sta==1)
			document.getElementById('ljetnjicrveno').style.color="black";

	}
	function provjeri_submit() {
		var zimski=parseFloat(document.getElementById('zimskiizbor').innerHTML);
		var ljetnji=parseFloat(document.getElementById('ljetnjiizbor').innerHTML);
		if (zimski><?=(30-$zimskiects)?> || ljetnji><?=(30-$ljetnjiects)?>) {
			alert ("Izabrali ste previše ECTS kredita u koliziji!");
			return false;
		}
		return true;
	}
	</SCRIPT>
	<?


	// Ispis

	?>
	<p><a href="?sta=student/kolizija">Nazad na izbor studija, godine i prenesenog predmeta</a></p>

	<p><hr><br />
	Izaberite predmete koje želite slušati. Možete izabrati maksimalno <?=(30-$zimskiects)?> kredita na zimskom semestru i <?=(30-$ljetnjiects)?> kredita na ljetnom.</p>
	<p>NAPOMENA: Morate popuniti i <a href="?sta=student/ugovoroucenju">Ugovor o učenju</a>, ali za prethodnu godinu (za <?=($godina-1)?>. godinu studija).</p>
	<p><span id="zimskicrveno"><b><?=($godina*2-1)?>. semestar (izabrano <span id="zimskiizbor">0</span> kredita, max. <?=(30-$zimskiects)?>):</b></span><br />
	<?


	$q40 = myquery("select predmet, obavezan from plan_studija where godina_vazenja=$najnoviji_plan and studij=$studij and semestar=".($godina*2-1)." order by obavezan desc, predmet");
	$is_bilo=array();
	while ($r40 = mysql_fetch_row($q40)) {
		if ($r40[1]==1) { // obavezan
			// Ako je vec polozen predmet preskacemo
			$q45 = myquery("select count(*) from konacna_ocjena where student=$userid and predmet=$r40[0]");
			if (mysql_result($q45,0,0)>0) continue; // Ako je vec polozen predmet preskacemo

			$q50 = myquery("select naziv, ects from predmet where id=$r40[0]");
			$pnaziv = mysql_result($q50,0,0);
			$pects = mysql_result($q50,0,1);

			// Zavrsni rad se ne moze izabrati u koliziji
			if ($pects == 12) continue;

			?>
			<input type="checkbox" name="obavezni1-<?=$r40[0]?>" onchange="javascript:updateects(0, <?=$pects?>, this)"> <?=$pnaziv?> (<?=$pects?> ECTS)<br />
			<?
		} else { // izborni

			if (count($is_bilo)==0) {
				print "Izborni predmeti:<br />\n";
			}

			// Da li je već bio izborni slot?
			if (in_array($r40[0], $is_bilo)) continue;
			array_push($is_bilo, $r40[0]);

			$q60 = myquery("select p.id, p.naziv, p.ects from predmet as p, izborni_slot as iz where iz.id=$r40[0] and iz.predmet=p.id");
			while ($r60 = mysql_fetch_row($q60)) {
				// Ako je vec polozen predmet preskacemo
				$q65 = myquery("select count(*) from konacna_ocjena where student=$userid and predmet=$r60[0]");
				if (mysql_result($q65,0,0)>0) continue; // Ako je vec polozen predmet preskacemo

				$pnaziv=$r60[1];
				$pects=$r60[2];
				?>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
				<input type="checkbox" name="izborni1--<?=$r60[0]?>" onchange="javascript:updateects(0, <?=$pects?>, this)"> <?=$pnaziv?> (<?=$pects?> ECTS)<br />
				<?
			}
		}
	}


	?>
	<p><span id="ljetnjicrveno"><b><?=($godina*2)?>. semestar (izabrano <span id="ljetnjiizbor">0</span> kredita, max. <?=(30-$ljetnjiects)?>):</b></span><br />
	<?


	$q40 = myquery("select predmet, obavezan from plan_studija where godina_vazenja=$najnoviji_plan and studij=$studij and semestar=".($godina*2)." order by obavezan desc, predmet");
	$is_bilo=array();
	while ($r40 = mysql_fetch_row($q40)) {
		if ($r40[1]==1) { // obavezan
			// Ako je vec polozen predmet preskacemo
			$q45 = myquery("select count(*) from konacna_ocjena where student=$userid and predmet=$r40[0]");
			if (mysql_result($q45,0,0)>0) continue; // Ako je vec polozen predmet preskacemo

			$q50 = myquery("select naziv, ects from predmet where id=$r40[0]");
			$pnaziv = mysql_result($q50,0,0);
			$pects = mysql_result($q50,0,1);

			// Zavrsni rad se ne moze izabrati u koliziji
			if ($pects == 12) continue;

			?>
			<input type="checkbox" name="obavezni2-<?=$r40[0]?>" onchange="javascript:updateects(1, <?=$pects?>, this)"> <?=$pnaziv?> (<?=$pects?> ECTS)<br />
			<?
		} else { // izborni

			if (count($is_bilo)==0) {
				print "Izborni predmeti:<br />\n";
			}

			// Da li je već bio izborni slot?
			if (in_array($r40[0], $is_bilo)) continue;
			array_push($is_bilo, $r40[0]);

			$q60 = myquery("select p.id, p.naziv, p.ects from predmet as p, izborni_slot as iz where iz.id=$r40[0] and iz.predmet=p.id");
			while ($r60 = mysql_fetch_row($q60)) {
				// Ako je vec polozen predmet preskacemo
				$q65 = myquery("select count(*) from konacna_ocjena where student=$userid and predmet=$r60[0]");
				if (mysql_result($q65,0,0)>0) continue; // Ako je vec polozen predmet preskacemo

				$pnaziv=$r60[1];
				$pects=$r60[2];
				?>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
				<input type="checkbox" name="izborni2--<?=$r60[0]?>" onchange="javascript:updateects(1, <?=$pects?>, this)"> <?=$pnaziv?> (<?=$pects?> ECTS)<br />
				<?
			}
		}
	}

	?>
	</p>
	<p>
	<input type="submit" value="Potvrđujem koliziju" onclick="javascript:return provjeri_submit()"></form></p>
	<?
	


}

?>
