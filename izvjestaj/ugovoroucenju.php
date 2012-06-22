<?

// IZVJESTAJ/UGOVOROUCENJU - procjena broja studenata na predmetu za datu akademsku godinu na osnovu popunjenih ugovora o ucenju



function izvjestaj_ugovoroucenju() {


function dajstudenta($stud) {
	$q = myquery("select prezime, ime from osoba where id=$stud");
	return mysql_result($q,0,0)." ".mysql_result($q,0,1);
}


require("lib/manip.php");

$debug_student = 0;
$debug_predmet = 0;



$novaag = intval($_REQUEST['akademska_godina']);
if ($novaag==0) $novaag = intval($_REQUEST['ag']);
if ($novaag==0) {
	$q3 = myquery("select id, naziv, aktuelna from akademska_godina order by id desc limit 1");
	$novaag = mysql_result($q3,0,0);
} else {
	$q3 = myquery("select 0, naziv, aktuelna from akademska_godina where id=$novaag");
	if (mysql_num_rows($q3)<1) {
		niceerror("Nepoznata akademska godina");
		zamgerlog("nepoznata godina $ag", 3);
		return;
	}
}
$novaag_naziv = mysql_result($q3,0,1);
$novaag_aktuelna = mysql_result($q3,0,2);

$q5 = myquery("select id, naziv from akademska_godina where id<$novaag order by id desc limit 1");
if (mysql_num_rows($q5)<1) {
	niceerror("Nije definisana akademska godina prije godine $novaag_naziv");
	print "Nemam na osnovu čega da vršim procjenu.";
	return;
}
$ag = mysql_result($q5,0,0);
$ag_naziv = mysql_result($q5,0,1);



?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>
<p>Datum i vrijeme izvještaja: <?=date("d. m. Y. H:i");?></p>
<?


// Podvrsta izvještaja: IMENA
// Daje spisak studenata koji su odabrali određeni predmet kroz Ugovor
$imena = intval($_REQUEST['imena']);
if ($imena>0) {
	$q1 = myquery("select naziv from predmet where id=$imena");
	print "<h2>Spisak studenata koji su odabrali predmet: ".mysql_result($q1,0,0)."</h2>\n";
}

// Podvrsta izvještaja: NISU IZABRALI
// Daje spisak imena studenata koji nisu izabrali izborne predmete
$nisu_izabrali = $_REQUEST['nisu_izabrali'];


// Odredićemo vrstu izvještaja kasnije
$fuzzy = $pola_godine = false;

// Spisak predmeta koji su imali ispit u septembru
$ispit_septembar=array();
$q5 = myquery("select i.id, i.predmet, i.komponenta from ispit as i where i.akademska_godina=$ag and (MONTH(i.datum)=8 or MONTH(i.datum)=9) and (select count(*) from ispitocjene as io where io.ispit=i.id)>0 order by i.predmet");
$staripredmet=0;
while ($r5 = mysql_fetch_row($q5)) {
	$predmet=$r5[1];
	if ($predmet!=$staripredmet) {
		$staripredmet=$predmet;
		$ispit_septembar[$predmet]=array();
	}
	array_push($ispit_septembar[$predmet], "$r5[0]-$r5[2]");
}

// Spisak svih studenata iz zadnje godine
$q10 = myquery("select student, studij, semestar, plan_studija from student_studij where akademska_godina=$ag and semestar MOD 2=0");
while ($r10 = mysql_fetch_row($q10)) {
	$student = $r10[0];
	$studij = $r10[1];
	$semestar = $r10[2];
	$ps = $r10[3];

	// Da li je student već upisan u novoj akademskoj godini?
	$q200 = myquery("select studij, semestar, ponovac from student_studij where student=$student and akademska_godina=$novaag order by semestar");
	if (mysql_num_rows($q200)>0) {
		$novistudij = mysql_result($q200,0,0);
		$novisemestar = mysql_result($q200,0,1);
		$ponovac = mysql_result($q200,0,2);

		// Jeste, samo selektujemo predmete na koje je upisan
		$imakoliziju = false;
		$q210 = myquery("select pk.predmet, pk.semestar from ponudakursa as pk, student_predmet as sp where sp.student=$student and sp.predmet=pk.id and pk.akademska_godina=$novaag");
		while ($r210 = mysql_fetch_row($q210)) {
			if ($r210[1] < $novisemestar)  {
				$slusa_prenio_sigurno[$r210[0]]++;
				if ($imena == $r210[0]) {
					$q268 = myquery("select prezime, ime, brindexa from osoba where id=$student");
					print "- ".mysql_result($q268,0,0)." ".mysql_result($q268,0,1)." (".mysql_result($q268,0,2).") - već upisan (prenio)<br>";
				}
			}

			else if ($r210[1] > $novisemestar+1) {
				$slusa_kolizija_sigurno[$r210[0]]++;
				$imakoliziju = true;
				if ($imena == $r210[0]) {
					$q268 = myquery("select prezime, ime, brindexa from osoba where id=$student");
					print "- ".mysql_result($q268,0,0)." ".mysql_result($q268,0,1)." (".mysql_result($q268,0,2).") - kolizija<br>";
				}
			}
			else if ($ponovac) {
				$slusa_ponovac_sigurno[$r210[0]]++;
				if ($novisemestar>1) $slusa_odsjek_ponovac[$r210[0]][$novistudij]++;
				if ($imena == $r210[0]) {
					$q268 = myquery("select prezime, ime, brindexa from osoba where id=$student");
					print "- ".mysql_result($q268,0,0)." ".mysql_result($q268,0,1)." (".mysql_result($q268,0,2).") - već upisan (ponovac) studij $novistudij<br>";
				}
			}
			else {
				$slusa_redovno_sigurno[$r210[0]]++;
				$slusa_odsjek_sigurno[$r210[0]][$novistudij]++;
				if ($imena == $r210[0]) {
					$q268 = myquery("select prezime, ime, brindexa from osoba where id=$student");
					print "- ".mysql_result($q268,0,0)." ".mysql_result($q268,0,1)." (".mysql_result($q268,0,2).") - već upisan (redovno) studij $novistudij<br>";
				}
			}
		}

		// Opšta statistika
		
		if ($ponovac) $br_ponovac[$novistudij][$novisemestar+1]++;
		else {
			if ($novisemestar==1)
				$br_ima_uslov[$novistudij][6]++;
			else
				$br_ima_uslov[$novistudij][$novisemestar-1]++;
		}
		if ($imakoliziju) $br_kolizija_nema_uslov[$novistudij][$novisemestar+1]++;

		// Ako nije upisan u parni semestar, uzmimamo još ugovor o učenju i 
		// plan studija, pošto je u prenesene predmete i kolizije već upisan
		if ($novaag_aktuelna && mysql_num_rows($q200)==1) {
			$pola_godine = true; // podaci za drugo pola su procjena
			if ($novistudij != $studij) {
				$q220 = myquery("select godina_vazenja from plan_studija where studij=$novistudij order by godina_vazenja desc limit 1");
				$ps = mysql_result($q220,0,0);
			}

			// Obavezni predmeti sa sljedećeg semestra
			$novisemestar++;
			$q230 = myquery("select predmet from plan_studija where godina_vazenja=$ps and studij=$novistudij and semestar=$novisemestar and obavezan=1");
			while ($r230 = mysql_fetch_row($q230)) {
				$predmet = $r230[0];
				$q240 = myquery("select count(*) from konacna_ocjena where student=$student and predmet=$predmet and ocjena>5");
				if (mysql_result($q240,0,0)==0)
					if ($ponovac) {
						$slusa_ponovac_sigurno[$predmet]++;
						if ($imena == $predmet) {
							$q268 = myquery("select prezime, ime, brindexa from osoba where id=$student");
							print "- ".mysql_result($q268,0,0)." ".mysql_result($q268,0,1)." (".mysql_result($q268,0,2).") - ponovac $novistudij<br>";
						}
					} else {
						$slusa_redovno_sigurno[$predmet]++;
						if ($imena == $predmet) {
							$q268 = myquery("select prezime, ime, brindexa from osoba where id=$student");
							print "- ".mysql_result($q268,0,0)." ".mysql_result($q268,0,1)." (".mysql_result($q268,0,2).") $novistudij<br>";
						}
					}
			}

			// Ugovor o učenju - studij se mora poklapati sa izabranim
			$q250 = myquery("select id from ugovoroucenju where student=$student and akademska_godina=$novaag and studij=$novistudij and semestar=$novisemestar");
			if (mysql_num_rows($q250)<1) {
				if ($novisemestar==2) $novisemestar=8;
				$bezizbornih_redovno_sigurno[$novistudij][$novisemestar-2]++;
				if ($nisu_izabrali == "da") {
					$q251 = myquery("select prezime, ime, brindexa from osoba where id=$student");
					$q252 = myquery("select naziv from studij where id=$novistudij");
				}
			} else {
				$uou = mysql_result($q250,0,0);
				$q260 = myquery("select predmet from ugovoroucenju_izborni where ugovoroucenju=$uou");
				while ($r260 = mysql_fetch_row($q260)) {
					$predmet = $r260[0];

					// Da li je već položio
					$q265 = myquery("select count(*) from konacna_ocjena where student=$student and predmet=$predmet and ocjena>5");
					if (mysql_result($q265,0,0)>0) continue;

					if ($ponovac) {
						$slusa_ponovac_sigurno[$r260[0]]++;
						$slusa_odsjek_ponovac[$r260[0]][$novistudij]++;
					} else {
						$slusa_redovno_sigurno[$r260[0]]++;
						$slusa_odsjek_sigurno[$r260[0]][$novistudij]++;
					}

					if ($imena == $predmet) {
						$q268 = myquery("select prezime, ime, brindexa from osoba where id=$student");
						print "- ".mysql_result($q268,0,0)." ".mysql_result($q268,0,1)." (".mysql_result($q268,0,2).") ";
						if ($ponovac) print "- ponovac";
						print "$studij <br>";
					}
				}
			}
		}

		continue; // Sljedeći student
	}

	// 15.10. je krajnji rok za upis na fakultet
	if (date("m")>10 || date("m")==10 && date("d")>15 || date("Y")>substr($novaag_naziv,0,4)) continue;

	$fuzzy = true; // Izvještaj sadrži nepreciznosti...

	// Student nije upisan u sljedećoj akademskoj godini
	// koristimo Ugovor o učenju i podatke o koliziji da procijenimo njegov status
	global $zamger_predmeti_pao;
	$zamger_predmeti_pao=array();
	$uslov = uslov($student, $ag);

if ($student==$debug_student) { print "predmeti pao "; print_r ($zamger_predmeti_pao); if ($uslov) print " ima uslov<br>"; else print " nema uslov<br>"; }

	// Ima li ugovor o ucenju?
	$izborni_ugovor = array();
	$q20 = myquery("select id, studij, semestar from ugovoroucenju where student=$student and akademska_godina=$novaag order by semestar");
	if (mysql_num_rows($q20)<1) $imaugovor=false; else $imaugovor=true;

	$ugovor_ponovac=false;
	$novistudij=$studij;
	$novisemestar=$semestar+1;

	$q21 = myquery("select s.institucija, ts.ciklus, ts.trajanje from studij as s, tipstudija as ts where s.id=$studij and s.tipstudija=ts.id");
	$s_institucija = mysql_result($q21,0,0);
	$s_ciklus = mysql_result($q21,0,1);
	$s_trajanje = mysql_result($q21,0,2);

	if ($imaugovor) {
		$novistudij = mysql_result($q20,0,1);
		$novisemestar = mysql_result($q20,0,2);
if ($student==$debug_student) print "ima ugovor $novistudij $novisemestar<br>";

		// Promjena studija
		if ($novistudij != $studij) {
			// Završio ciklus, upisuje novi studij?
			if ($semestar==$s_trajanje && $novisemestar==1) {
				// Uslov je OK!
				// Za završni semestar zanemarujemo predmet "završni rad"
				if (!$uslov && count($zamger_predmeti_pao)==1) $uslov=true;

			} else if ($semestar==$s_trajanje) {
				// Pretpostavljamo da se prebacuje na raniji semestar drugog studija
				$ugovor_ponovac=true;

			// Sljedeći semestar drugog studija
			} else if ($novisemestar==$semestar+1) {
				// FIXME ponovo provjeriti uslov?
				$ugovor_ponovac=false;

			// Raniji semestar drugog studija
			} else if ($novisemestar<=$semestar) {
				$ugovor_ponovac=true;

			} // else nešto se čudno dešava

			$q23 = myquery("select godina_vazenja from plan_studija where studij=$novistudij order by godina_vazenja desc limit 1");
			$ps = mysql_result($q23,0,0);

		} else if ($novisemestar <= $semestar) {
			if ($uslov) {
				// Student je mislio da će ponavljati
				$novisemestar=$semestar+1;
				$imaugovor=false;
			} else {
				// Ponovac
				$ugovor_ponovac=true;
			}
		}

	} else { // Nema ugovor
if ($student==$debug_student) print "nema ugovor $semestar<br>";
		// Ako je na kraju studija, pretpostavljamo da će upisati novi
		if ($semestar == $s_trajanje) {
			$q22 = myquery("select s.id from studij as s, tipstudija as ts where s.institucija=$s_institucija and s.tipstudija=ts.id and ts.ciklus=".($s_ciklus+1));
			if (mysql_num_rows($q22)>0) {
				$novistudij=mysql_result($q22,0,0);
				$novisemestar=1;
				$q23 = myquery("select godina_vazenja from plan_studija where studij=$novistudij order by godina_vazenja desc limit 1");
				$ps = mysql_result($q23,0,0);

			} else { //nema više studija poslije ovog
				$novistudij=0;
				$novisemestar=0;
			}
		}
	}

	// Ima li zahtjev za koliziju?
	$kolizija = array();
	$q30 = myquery("select semestar, predmet from kolizija where student=$student and akademska_godina=$novaag");
	if (mysql_num_rows($q30)<1) $imakoliziju=false; else $imakoliziju=true;
if ($student==$debug_student) print "kolizija ".($imakoliziju?1:0)."<br>";

	// Student koji preko kolizije mijenja studij
	if ($imakoliziju && !$imaugovor) {
		while ($r30 = mysql_fetch_row($q30)) {
			$q31 = myquery("select studij, semestar from plan_studija where predmet=$r30[1] and obavezan=1 order by godina_vazenja desc limit 1");
			if (mysql_num_rows($q31)>0) {
				if ($novistudij != mysql_result($q31,0,0)) {
					$novistudij= mysql_result($q31,0,0);
					$novisemestar=mysql_result($q31,0,1);
					if ($novisemestar%2==0) $novisemestar--;
if ($student==$debug_student) print "mijenja smjer preko kolizije $novistudij $novisemestar<br>";
				}
				break;
			}
		}
		$q30 = myquery("select semestar, predmet from kolizija where student=$student and akademska_godina=$novaag");
	}

	// Spisak predmeta sa više godine koje je student položio koliziono
	$polozio_koliziono = array();
	$q35 = myquery("select ko.predmet from konacna_ocjena as ko, student_predmet as sp, ponudakursa as pk where ko.ocjena>5 and ko.student=$student and ko.predmet=pk.predmet and ko.akademska_godina=pk.akademska_godina and sp.student=$student and sp.predmet=pk.id and pk.semestar>$semestar");
	while ($r35 = mysql_fetch_row($q35))
		array_push($polozio_koliziono, $r35[0]);
if ($student==$debug_student) { print "polozio koliziono"; print_r ($polozio_koliziono); print "<br>\n"; }


	// Šta je položio u septembru?
	$pao_septembar=$polozio_septembar=array();
	foreach ($zamger_predmeti_pao as $predmet) {
		$polozio=$pao=false;
		if ($ispit_septembar[$predmet])
		foreach ($ispit_septembar[$predmet] as $ispitkomponenta) {
			list($ispit,$komponenta) = explode("-", $ispitkomponenta);

			// Da li je student ranije položio ispit?
			$q60 = myquery("select count(*) from ispit as i, ispitocjene as io, komponenta as k where i.predmet=$predmet and i.akademska_godina=$ag and MONTH(i.datum)!=8 and MONTH(i.datum)!=9 and i.komponenta=k.id and io.ispit=i.id and io.student=$student and io.ocjena>=k.prolaz and k.id=$komponenta");
			if (mysql_result($q60,0,0)>0) continue; // Jeste

			// A da li je položio u septembru?
			$q70 = myquery("select count(*) from ispit as i, ispitocjene as io, komponenta as k where i.id=$ispit and i.komponenta=k.id and io.ispit=i.id and io.student=$student and io.ocjena>=k.prolaz");
			if (mysql_result($q70,0,0)>0)
				$polozio=true;
			else
				$pao=true;
		}


		// Ako je student ranije položio sve ispite, biće $polozio=$pao=false, znači da mu je ostao još usmeni i ne znamo situaciju

		// Ako je bilo više termina u septembru od kojih je jedan položio a neke nije, biće $polozio=$pao=true, zato prvo gledamo $polozio pa onda $pao
		if ($polozio)
			array_push($polozio_septembar,$predmet);
		else if ($pao)
			array_push($pao_septembar,$predmet);
	}
if ($student==$debug_student) { print "polozio septembar"; print_r($polozio_septembar); print " pao septembar "; print_r($pao_septembar); print "<br>"; }

	// Opšte statistike
	if ($imaugovor) $brugovora++;
	if ($imakoliziju) $brkolizija++;
	if ($imaugovor && $imakoliziju) $broboje++;
	$brstudenata++;




	// SPISAK PO PREDMETIMA
	// Kategorije studenata 1-6

	// 1. Ima uslov
	if ($uslov) {
if ($student==$debug_student) print "Ima uslov<br>";
		$fali_u_koliziji=0;

		$q40 = myquery("select predmet from plan_studija where godina_vazenja=$ps and studij=$novistudij and (semestar=$novisemestar or semestar=".($novisemestar+1).") and obavezan=1");
		while ($r40 = mysql_fetch_row($q40)) {
			if (!in_array($r40[0], $polozio_koliziono)) {
				$slusa_redovno_sigurno[$r40[0]]++;
				$fali_u_koliziji++;
				$slusa_odsjek_sigurno[$r40[0]][$novistudij]++;
//if ($student==$debug_student) { print "ima uslov slusa redovno sigurno $r40[0] po planu studija $ps novistudij $novistudij semestar $novisemestar<br>"; }
			}
		}

		foreach ($zamger_predmeti_pao as $predmet)
			if (in_array($predmet, $pao_septembar))
				$slusa_prenio_sigurno[$predmet]++;
			else if (!in_array($predmet, $polozio_septembar))
				$slusa_prenio_mozda[$predmet]++;

		// 1A. Ima ugovor o učenju
		if ($imaugovor && !$ugovor_ponovac) {
			$q50 = myquery("select predmet from ugovoroucenju_izborni where ugovoroucenju=".mysql_result($q20,0,0)." or ugovoroucenju=".mysql_result($q20,1,0));
			while ($r50 = mysql_fetch_row($q50)) {
				if (!in_array($r50[0], $polozio_koliziono)) {
					$slusa_redovno_sigurno[$r50[0]]++;
					$fali_u_koliziji++;
					$slusa_odsjek_sigurno[$r50[0]][$novistudij]++;
				}
			}
		
		// 1B. Nema ugovor o ucenju ili je popunio za ponavljanje
		} else {
			$bezizbornih_redovno_sigurno[$novistudij][$semestar]++;
			// Odredjujemo koliko fali u koliziji
			$q55 = myquery("select count(*) from plan_studija where godina_vazenja=$ps and studij=$novistudij and (semestar=$novisemestar or semestar=".($novisemestar+1).") and obavezan=0");
			$fali_u_koliziji += mysql_result($q55,0,0);
			if ($nisu_izabrali == "da") {
				$q251 = myquery("select prezime, ime, brindexa from osoba where id=$student");
				$q252 = myquery("select naziv from studij where id=$novistudij");
			}
		}

/*		// 1X. Da li ima uslove da koliziono odmah sluša sljedeću godinu?
		if ($imakoliziju && mysql_result($q30,0,0)>$semestar+2 && $fali_u_koliziji<=3) {
			while ($r30 = mysql_fetch_row($q30))
				$slusa_kolizija_sigurno[$r30[1]]++;
			$br_kolizija_nema_uslov[$novistudij][$semestar+2]++;
		}*/

		$br_ima_uslov[$novistudij][$semestar]++;
		if (!$imaugovor && $novistudij != 0) $ima_uslov_nema_ugovor[] = $student;

		continue;
	}


	// 2. Ašćare dao uslov u septembru samo ocjene još nisu upisane
	if (count($polozio_septembar)>0 && count($zamger_predmeti_pao)-count($polozio_septembar)<=1) {
if ($student==$debug_student) print "Ašćare ima uslov<br>";
		$fali_u_koliziji=0;

		$q40 = myquery("select predmet from plan_studija where godina_vazenja=$ps and studij=$novistudij and (semestar=".($novisemestar+1)." or semestar=$novisemestar) and obavezan=1");
		while ($r40 = mysql_fetch_row($q40)) {
			if (!in_array($r40[0], $polozio_koliziono)) {
				$slusa_redovno_sigurno[$r40[0]]++;
				$fali_u_koliziji++;
				$slusa_odsjek_sigurno[$r40[0]][$novistudij]++;
			}
		}

		foreach ($zamger_predmeti_pao as $predmet) {
			if (in_array($predmet, $pao_septembar))
				$slusa_prenio_sigurno[$predmet]++;
			else if (!in_array($predmet, $polozio_septembar))
				$slusa_prenio_mozda[$predmet]++;
		}

		// 2A. Ima ugovor o učenju
		if ($imaugovor && !$ugovor_ponovac) {
			$q50 = myquery("select predmet from ugovoroucenju_izborni where ugovoroucenju=".mysql_result($q20,0,0)." or ugovoroucenju=".mysql_result($q20,1,0));
			while ($r50 = mysql_fetch_row($q50)) {
				if (!in_array($r50[0], $polozio_koliziono)) {
					$slusa_redovno_sigurno[$r50[0]]++;
					$fali_u_koliziji++;
					$slusa_odsjek_sigurno[$r50[0]][$novistudij]++;
				}
			}
		
		// 2B. Nema ugovor o ucenju ili je popunio za ponavljanje
		} else {
			$bezizbornih_redovno_sigurno[$studij][$semestar]++;
			// Odredjujemo koliko fali u koliziji
			$q55 = myquery("select count(*) from plan_studija where godina_vazenja=$ps and studij=$novistudij and (semestar=".($novisemestar+1)." or semestar=$novisemestar) and obavezan=0");
			$fali_u_koliziji += mysql_result($q55,0,0);
			if ($nisu_izabrali == "da") {
				$q251 = myquery("select prezime, ime, brindexa from osoba where id=$student");
				$q252 = myquery("select naziv from studij where id=$studij");
			}
		}

		// 2X. Da li ima uslove da koliziono odmah sluša sljedeću godinu?
/*		if ($imakoliziju && mysql_result($q30,0,0)>$semestar+2 && $fali_u_koliziji<=3) {
			while ($r30 = mysql_fetch_row($q30))
				$slusa_kolizija_sigurno[$r30[1]]++;
			$br_kolizija_nema_uslov[$novistudij][$semestar+2]++;
		}*/

		$br_ima_uslov_sept[$novistudij][$semestar]++;

		continue;
	}


	// Šta je student izjavio da će položiti u septembru kako bi stekao pravo na koliziju?
	$kolizioni_septembar = array();
	$ostvario_septembar = true;
	$q80 = myquery("select predmet from septembar where student=$student and akademska_godina=$ag");
	while ($r80 = mysql_fetch_row($q80)) {
		if (in_array($r80[0], $pao_septembar))
			$ostvario_septembar = false;
		else if (!in_array($r80[0], $polozio_septembar))
			array_push($kolizioni_septembar, $r80[0]);
	}


	// Ostale kategorije studenata su, prema sadašnjem stanju, ponovci
	foreach ($zamger_predmeti_pao as $predmet) {
		if (in_array($predmet, $pao_septembar))
			$slusa_ponovac_sigurno[$predmet]++;
		else if (!in_array($predmet, $polozio_septembar))
			$slusa_ponovac_mozda[$predmet]++;
	}


	// 3. Ima uslove za koliziju, popunio zahtjev
if ($student==$debug_student) print "uslov 1 ".(count($zamger_predmeti_pao)-count($polozio_septembar))." uslov2 ".($imakoliziju?1:0)." uslov3 ".($ostvario_septembar?1:0)."<br>";
	if (count($zamger_predmeti_pao)-count($polozio_septembar) <= 3 && $imakoliziju && $ostvario_septembar) {
if ($student==$debug_student) print "Kolizija<br>";
		// Predmeti koje sluša koliziono
		$ovaj_student_kolizija=array();
		while ($r30 = mysql_fetch_row($q30)) {
			$predmet = $r30[1];
			if (!in_array($predmet, $polozio_koliziono)) {
				// Ako je student ostavio neki predmet za septembar, umjesto njega je izabrao neki drugi na višoj godini, a ne znamo koji - moraće se opredijeliti ako ne položi
				if (count($kolizioni_septembar)>0)
					$slusa_kolizija_mozda[$predmet]++;
				else 
					$slusa_kolizija_sigurno[$predmet]++;

				array_push($ovaj_student_kolizija, $predmet);
			}
		}

		// 3A. Ima šansi da upiše redovno
		if (!$ugovor_ponovac && count($pao_septembar)<=1) {
			$q40 = myquery("select predmet from plan_studija where godina_vazenja=$ps and studij=$novistudij and (semestar=".($novisemestar+1)." or semestar=$novisemestar) and obavezan=1");
			while ($r40 = mysql_fetch_row($q40))
				if (!in_array($r40[0], $ovaj_student_kolizija) && !in_array($r40[0], $polozio_koliziono))
					$slusa_redovno_mozda[$r40[0]]++;
			
			// 3A1. Ima ugovor o učenju za višu godinu
			if ($imaugovor) {
				$q50 = myquery("select predmet from ugovoroucenju_izborni where ugovoroucenju=".mysql_result($q20,0,0)." or ugovoroucenju=".mysql_result($q20,1,0));
				while ($r50 = mysql_fetch_row($q50))
					if (!in_array($r50[0], $ovaj_student_kolizija) && !in_array($r50[0], $polozio_koliziono))
						$slusa_redovno_mozda[$r50[0]]++;

			// 3A2. Nije popunio ugovor o učenju
			} else {
				$bezizbornih_redovno_mozda[$novistudij][$semestar]++;
			}
			$br_kolizija_mozda_uslov[$novistudij][$semestar]++;
		} else {
			$br_kolizija_nema_uslov[$novistudij][$semestar]++;
		}

		continue;
	}

	// 4. Nema uslove za koliziju ali smatra da će imati (i ima šansi)
	// Ako nije ostvario septembar, tj. pao je predmet koji je rekao da će položiti, mora ponovo popuniti koliziju
	// Uz svo dužno poštovanje, smatramo da studenti sa više od 5 nepoloženih predmeta nemaju šansi za koliziju
	if ($imakoliziju && $ostvario_septembar && count($zamger_predmeti_pao)-count($polozio_septembar)<=5 && count($pao_septembar)<=3) {
if ($student==$debug_student) print "Možda kolizija<br>";
		// Predmeti koje sluša koliziono
		while ($r30 = mysql_fetch_row($q30)) {
			$predmet = $r30[1];
			if (!in_array($predmet, $polozio_koliziono)) {
				$slusa_kolizija_mozda[$predmet]++;
			}
		}
		
		// Smatramo da nije realno da će dati uslov, bez obzira na ugovor

		$br_mozda_kolizija[$novistudij][$semestar]++;

		continue;
	}

	// 5. Nije tražio koliziju, ima šansi za uslov
	if (!$ugovor_ponovac && count($pao_septembar)<=1 && count($zamger_predmeti_pao)-count($polozio_septembar) <= 3) {
if ($student==$debug_student) print "Šanse za uslov bez kolizije<br>";
		$q40 = myquery("select predmet from plan_studija where godina_vazenja=$ps and studij=$novistudij and (semestar=".($novisemestar+1)." or semestar=$novisemestar) and obavezan=1");
		while ($r40 = mysql_fetch_row($q40))
			if (!in_array($r40[0], $polozio_koliziono))
				$slusa_redovno_mozda[$r40[0]]++;
		
		// 5A1. Ima ugovor o učenju za višu godinu
		if ($imaugovor) {
			$q50 = myquery("select predmet from ugovoroucenju_izborni where ugovoroucenju=".mysql_result($q20,0,0)." or ugovoroucenju=".mysql_result($q20,1,0));
			while ($r50 = mysql_fetch_row($q50))
				if (!in_array($r50[0], $polozio_koliziono))
					$slusa_redovno_mozda[$r50[0]]++;

		// 5A2. Nije popunio ugovor o učenju
		} else {
			$bezizbornih_redovno_mozda[$novistudij][$semestar]++;
		}

		$br_mozda_uslov[$novistudij][$semestar]++;

		continue;
	}
if ($student==$debug_student) print "Ponovac<br>";

	// 6. Ostali su pretpostavljamo sigurni ponovci	
	$br_ponovac[$studij][$semestar]++;

} // while ($r10...)

// Kod podizvještaja IMENA ovdje završavamo
// FIXME dodati i za neparne semestre
if ($imena>0) return;




// Disclaimer

if ($fuzzy) {
	?>
	<h2>Procjena broja studenata po predmetu za <?=$novaag_naziv?></h2>
	<p><b>Napomena:</b> Procjena broja studenata po predmetu je data na osnovu popunjenih Ugovora o učenju i Zahtjeva za koliziju, pod sljedećim pretpostavkama:
	<ul><li>da se nijedan student neće ispisati sa fakulteta</li>
	<li>da će svi zahtjevi za promjenu odsjeka biti odobreni</li>
	<li>da će student koji u septembru položi pismeni ispit koji ranije nije položio vjerovatno položiti i završni ispit</li></ul></p>
	<?

	// Opšte statistike o Ugovoru o učenju koje su interesantne samo za procjenu
	
	?>
	<script language="JavaScript">
	function daj(){
		var me = document.getElementById('ima_uslov_nema_ugovor');
		if (me.style.display=="none"){
			me.style.display="inline";
		}
		else {
			me.style.display="none";
		}
	}
	</script>
	<p>U akademskoj <?=$ag_naziv?> godini bilo je <?=$brstudenata?> studenata.<br>
	Od toga <?=$brugovora?> je popunilo ugovor o učenju, a <?=$brkolizija?> je popunilo zahtjev za koliziju (<?=$broboje?> je popunilo oboje).<br>
	<?=count($ima_uslov_nema_ugovor)?> studenata ima uslov, a nisu popunili Ugovor o učenju! <img src="images/plus.png" width="13" height="13" id="img-ag-1" onclick="daj()">.</p>
	<div id="ima_uslov_nema_ugovor" style="display:none">
	<?
	foreach ($ima_uslov_nema_ugovor as $student) {
		$q99 = myquery("select ime, prezime, brindexa from osoba where id=$student");
		$r99 = mysql_fetch_row($q99);
		print "$r99[1] $r99[0] ($r99[2])<br>\n";
	}
	?>
	</div>
	<?

} else if ($pola_godine) {
	?>
	<h2>Procjena broja studenata po predmetu za <?=$novaag_naziv?></h2>
	<p><b>Napomena:</b> Podaci za parni semestar su procijenjeni na osnovu popunjenih Ugovora o učenju pod sljedećim pretpostavkama:
	<ul><li>da se nijedan student neće ispisati nakon završenog neparnog semestra</li>
	<li>da se studenti neće predomisliti za izborne predmete u parnom semestru (na što imaju pravo)</li></ul></p>
	<?

} else {
	?>
	<h2>Broj studenata po predmetu za <?=$novaag_naziv?></h2>
	<?
}






// --------- ISPIS ----------



// Po studijima i semestrima

$q100 = myquery("select ps.predmet, s.id, s.naziv, ps.semestar, ps.obavezan, ts.ciklus, s.institucija from plan_studija as ps, studij as s, tipstudija as ts where ps.studij=s.id and (ps.godina_vazenja=1 or ps.godina_vazenja=4) and s.tipstudija=ts.id order by ts.ciklus, s.naziv, ps.semestar, ps.obavezan DESC"); // FIXME ukodirani planovi studija
$oldstudij=$oldsemestar=$oldobavezan="";

$predmeti_ispis=array();

// Ispisujemo podatke "od-do" ili samo jedan broj ako se ne razlikuju
function od_do ($br1, $br2) {
	$br1 = intval($br1); // ako je blank dobićemo nulu
	$br2 = $br1+$br2;
	if ($br1==$br2) return $br1;
	return "$br1 - $br2";
}

$qblesavo = myquery("select id, kratkinaziv from studij order by id");
while ($rblesavo = mysql_fetch_row($qblesavo)) {
	$naziv_studijaa[$rblesavo[0]]=$rblesavo[1];
}


while ($r100 = mysql_fetch_row($q100)) {
	$studij=$r100[1];
	$naziv_studija = $r100[2];
	$semestar=$r100[3];
	$obavezan=$r100[4];
	$ciklus=$r100[5];
	$institucija=$r100[6];

	if ($semestar!=$oldsemestar || $obavezan==1) {
		$x=0;
		foreach ($predmeti_ispis as $predmet => $naziv_predmeta) {
			if ($izborni_print==1 && $x==1) $naziv_predmeta .= " *";
			$x=1;

			if ($ciklus==1 && $oldsemestar<=2) $slusa_redovno_sigurno[$predmet]=$upisano_na_studij;

			$redovno = od_do($slusa_redovno_sigurno[$predmet], $slusa_redovno_mozda[$predmet]);
			$kolizija = od_do($slusa_kolizija_sigurno[$predmet], $slusa_kolizija_mozda[$predmet]);
			$uk1 = od_do($slusa_redovno_sigurno[$predmet]+$slusa_kolizija_sigurno[$predmet], $slusa_redovno_mozda[$predmet]+$slusa_kolizija_mozda[$predmet]);


			$ponovac = od_do($slusa_ponovac_sigurno[$predmet], $slusa_ponovac_mozda[$predmet]);
			$prenio = od_do($slusa_prenio_sigurno[$predmet], $slusa_prenio_mozda[$predmet]);

			$uk2 = od_do($slusa_redovno_sigurno[$predmet]+$slusa_kolizija_sigurno[$predmet]+$slusa_ponovac_sigurno[$predmet]+$slusa_prenio_sigurno[$predmet], $slusa_redovno_mozda[$predmet]+$slusa_kolizija_mozda[$predmet]+$slusa_ponovac_mozda[$predmet]+$slusa_prenio_mozda[$predmet]);
			$dodaj = $dodajpon = "";
			for ($i=1; $i<15; $i++) {
				if ($i==$studij) continue;
				if ($slusa_odsjek_sigurno[$predmet][$i]>0 && $slusa_odsjek_sigurno[$predmet][$i]<$slusa_redovno_sigurno[$predmet]) 
					$dodaj .= " (".$naziv_studijaa[$i]." ".$slusa_odsjek_sigurno[$predmet][$i].")";
				if ($slusa_odsjek_ponovac[$predmet][$i]>0 && $slusa_odsjek_ponovac[$predmet][$i]<$slusa_ponovac_sigurno[$predmet]) 
					$dodajpon .= " (".$naziv_studijaa[$i]." ".$slusa_odsjek_ponovac[$predmet][$i].")";
			}
	
			print "<tr><td>$rbr</td><td>$naziv_predmeta</td><td>$redovno $dodaj</td><td>$kolizija</td><td bgcolor=\"#CCCCCC\">$uk1</td><td>$ponovac $dodajpon</td><td>$prenio</td><td bgcolor=\"#CCCCCC\">$uk2</td>\n</tr>\n";
			$rbr++;
		}
		$predmeti_ispis=array();
	}

	if ($studij!=$oldstudij) {
		if ($oldstudij!="") {
			if ($izborni_print==1) {
				$ss=$oldsemestar-2;
				$nijeod = od_do($bezizbornih_redovno_sigurno[$studij][$ss], $bezizbornih_redovno_mozda[$studij][$ss]);
				if ($fuzzy || ($pola_godine && $semestar%2==1)) {
					print "<tr><td>&nbsp;</td><td>Nije odabralo izborne predmete</td>\n";
					print "<td colspan=\"6\" align=\"left\">$nijeod</td>\n</tr>\n";
				}
			}
			print "</table></p>\n\n";
		}
		print "<h2>$naziv_studija</h2>\n";
	}
	if ($semestar!=$oldsemestar) {
		if ($studij==$oldstudij) {
			if ($izborni_print==1) {
				if ($semestar%2==1) $ss=$semestar-3; else $ss=$semestar-2;
				if ($ss==0) $ss=6; // FIXME etf specifično
				$nijeod = od_do($bezizbornih_redovno_sigurno[$studij][$ss], $bezizbornih_redovno_mozda[$studij][$ss]);
				if ($fuzzy || ($pola_godine && $semestar%2==1)) {
					print "<tr><td>&nbsp;</td><td>Nije odabralo izborne predmete</td>\n";
					print "<td colspan=\"6\" align=\"left\">$nijeod</td>\n</tr>\n";
				}
			}
			print "</table></p>\n\n";
		}
		print "<p><b>$semestar. semestar:</b></p>\n";

		// Ispis statistika
		if ($ciklus==2 && $semestar==1) {
			$statsem=6;
//			$q105 = myquery("select s.id from studij as s, tipstudija as ts where s.tipstudija=ts.id and ts.ciklus=1 and s.institucija=$institucija");
//			$statstudij=mysql_result($q105,0,0);
$statstudij=$studij;
		} else {
			$statsem=$semestar-1;
			$statstudij=$studij;
		}

		if ($statsem==0) { // Prvi semestar prvog ciklusa
			// Ako još nije krenula sljedeća godina, ovaj broj će se popunjavati brucošima kako traje upis
			$q107 = myquery("select count(*) from student_studij where studij=$studij and akademska_godina=$novaag and semestar=1 and ponovac=0");
			$upisano_na_studij = mysql_result($q107,0,0);
			print "<p>Na ovom odsjeku upisano $upisano_na_studij redovnih i ".intval($br_ponovac[$studij][2])." ponovaca</p>";

			$q107 = myquery("select count(*) from student_studij as ss, studij as s, tipstudija as ts where ss.akademska_godina=$novaag and ss.semestar=1 and ss.studij=s.id and s.tipstudija=ts.id and ts.ciklus=1 and ss.ponovac=0");
			$upisano_na_studij = mysql_result($q107,0,0);
		} else if ($semestar%2==1) {
			?>
			<p>Statistike:<br>
			* <? if ($fuzzy) print "Dalo uslov"; else print "Redovno upisalo"; ?>: <?=intval($br_ima_uslov[$statstudij][$statsem])?><br>
			<?
			if ($br_ima_uslov_sept[$statstudij][$statsem]>0) { ?>
			* Vjerovatno dalo uslov u septembru: <?=intval($br_ima_uslov_sept[$statstudij][$statsem])?><br>
			<? } ?>
			* Kolizija: <?=intval($br_kolizija_mozda_uslov[$statstudij][$statsem]+$br_kolizija_nema_uslov[$statstudij][$statsem])?> <?
			if ($br_kolizija_mozda_uslov[$statstudij][$statsem]>0) {
			?> (od toga <?=intval($br_kolizija_mozda_uslov[$statstudij][$statsem])?> ima šansi da da uslov)<?
			} 
			?><br><?
			if ($br_mozda_uslov[$statstudij][$statsem]>0) { ?>
			* Imaju šanse za uslov, a nisu tražili koliziju: <?=intval($br_mozda_uslov[$statstudij][$statsem])?><br>
			<? }
			if ($br_mozda_kolizija[$statstudij][$statsem]>0) { ?>
			* Imaju šanse za koliziju: <?=intval($br_mozda_kolizija[$statstudij][$statsem])?><br>
			<? }
			if ($br_kolizija_mozda_uslov[$studij][$semestar+1]+$br_mozda_uslov[$studij][$semestar+1]+$br_mozda_kolizija[$studij][$semestar+1] > 0) { ?>
			* Sigurnih ponovaca: <?=intval($br_ponovac[$studij][$semestar+1]+$br_kolizija_nema_uslov[$studij][$semestar+1])?><br>
			* Možda ponovaca: <?=intval($br_kolizija_mozda_uslov[$studij][$semestar+1]+$br_mozda_uslov[$studij][$semestar+1]+$br_mozda_kolizija[$studij][$semestar+1])?><br>
			<?
			} else { ?>
			* Ponovaca: <?=intval($br_ponovac[$studij][$semestar+1]+$br_kolizija_nema_uslov[$studij][$semestar+1])?><br>
			<?
			}
		}
		?>
		<table border="1" cellspacing="0" cellpadding="2">
		<tr><td>R.br.</td><td>Predmet</td><td>1.<br>Redovno</td><td>2.<br>Kolizija</td><td bgcolor="#CCCCCC">3.<br>(1+2)</td><td>4.<br>Ponovaca</td><td>5.<br>Prenesenih</td><td bgcolor="#CCCCCC">6.<br>UKUPNO (3+4+5)</td></tr>
		<?
		$oldstudij=$studij;
		$oldsemestar=$semestar;
		$rbr=1;
	}

	if ($obavezan==1) {
		$q110 = myquery("select naziv from predmet where id=$r100[0]");
		$predmeti_ispis[$r100[0]] = mysql_result($q110,0,0);
		$izborni_print=0;
	} else {
		$q120 = myquery("select p.id, p.naziv from izborni_slot as izs, predmet as p where izs.id=$r100[0] and izs.predmet=p.id");
		while($r120 = mysql_fetch_row($q120))
			$predmeti_ispis[$r120[0]]=$r120[1];
		$izborni_print=1;
	}
}


// Ispis zadnjih redova...
$x=0;
foreach ($predmeti_ispis as $predmet => $naziv_predmeta) {
	if ($izborni_print==1 && $x==1) $naziv_predmeta .= " *";
	$x=1;

	if ($ciklus==1 && $oldsemestar<=2) $slusa_redovno_sigurno[$predmet]=$upisano_na_studij;

	$redovno = od_do($slusa_redovno_sigurno[$predmet], $slusa_redovno_mozda[$predmet]);
	$kolizija = od_do($slusa_kolizija_sigurno[$predmet], $slusa_kolizija_mozda[$predmet]);
	$uk1 = od_do($slusa_redovno_sigurno[$predmet]+$slusa_kolizija_sigurno[$predmet], $slusa_redovno_mozda[$predmet]+$slusa_kolizija_mozda[$predmet]);


	$ponovac = od_do($slusa_ponovac_sigurno[$predmet], $slusa_ponovac_mozda[$predmet]);
	$prenio = od_do($slusa_prenio_sigurno[$predmet], $slusa_prenio_mozda[$predmet]);

	$uk2 = od_do($slusa_redovno_sigurno[$predmet]+$slusa_kolizija_sigurno[$predmet]+$slusa_ponovac_sigurno[$predmet]+$slusa_prenio_sigurno[$predmet], $slusa_redovno_mozda[$predmet]+$slusa_kolizija_mozda[$predmet]+$slusa_ponovac_mozda[$predmet]+$slusa_prenio_mozda[$predmet]);

	print "<tr><td>$rbr $k</td><td>$naziv_predmeta</td><td>$redovno</td><td>$kolizija</td><td bgcolor=\"#CCCCCC\">$uk1</td><td>$ponovac</td><td>$prenio</td><td bgcolor=\"#CCCCCC\">$uk2</td>\n</tr>\n";
	$rbr++;
}

$ss=$oldsemestar-2;
$nijeod = od_do($bezizbornih_redovno_sigurno[$studij][$ss], $bezizbornih_redovno_mozda[$studij][$ss]);
if ($fuzzy || ($pola_godine && $semestar%2==0)) {
	print "<tr><td>&nbsp;</td><td>Nije odabralo izborne predmete</td>\n";
	print "<td colspan=\"6\" align=\"left\">$nijeod</td>\n</tr>\n";
}
print "</table></p>\n\n";



}

?>
