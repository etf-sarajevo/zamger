<?

// IZVJESTAJ/UGOVOROUCENJU - procjena broja studenata na predmetu za datu akademsku godinu na osnovu popunjenih ugovora o ucenju



function izvjestaj_ugovoroucenju() {

global $conf_uslov_predmeta, $conf_uslov_kolizija, $conf_uslov_ects_kredita;

$realno_poloziti_u_septembru = 3;


function dajstudenta($stud) {
	$q = db_query("select prezime, ime from osoba where id=$stud");
	return db_result($q,0,0)." ".db_result($q,0,1);
}


global $userid,$user_studentska,$user_siteadmin;

require("lib/student_studij.php");

$debug_student = 0;
$debug_predmet = 0;


if (!$user_studentska && !$user_siteadmin) {
	biguglyerror("Pristup nije dozvoljen.");
	return;
}


$novaag = intval($_REQUEST['akademska_godina']);
if ($novaag==0) $novaag = intval($_REQUEST['ag']);
if ($novaag==0) {
	$q3 = db_query("select id, naziv, aktuelna from akademska_godina order by id desc limit 1");
	$novaag = db_result($q3,0,0);
} else {
	$q3 = db_query("select 0, naziv, aktuelna from akademska_godina where id=$novaag");
	if (db_num_rows($q3)<1) {
		niceerror("Nepoznata akademska godina");
		zamgerlog("nepoznata godina $ag", 3);
		zamgerlog2("nepoznata godina", $ag);
		return;
	}
}
$novaag_naziv = db_result($q3,0,1);
$novaag_aktuelna = db_result($q3,0,2);

$q5 = db_query("select id, naziv from akademska_godina where id<$novaag order by id desc limit 1");
if (db_num_rows($q5)<1) {
	niceerror("Nije definisana akademska godina prije godine $novaag_naziv");
	print "Nemam na osnovu čega da vršim procjenu.";
	return;
}
$ag = db_result($q5,0,0);
$ag_naziv = db_result($q5,0,1);



?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>
<p>Datum i vrijeme izvještaja: <?=date("d. m. Y. H:i");?></p>
<?


// Podvrsta izvještaja: IMENA
// Daje spisak studenata koji su odabrali određeni predmet kroz Ugovor
$imena = intval($_REQUEST['imena']);
if ($imena>0) {
	$q1 = db_query("select naziv from predmet where id=$imena");
	print "<h2>Spisak studenata koji su odabrali predmet: ".db_result($q1,0,0)."</h2>\n";
}

// Ako je imena==-1 daje imena studenata za sve predmete
// Cache imena predmeta
$imena_predmeta = array();
if ($imena <= -1) {
	$q2 = db_query("select id, naziv from predmet");
	while ($r2 = db_fetch_row($q2)) {
		$imena_predmeta[$r2[0]] = $r2[1];
	}
}

// Podvrsta izvještaja: NISU IZABRALI
// Daje spisak imena studenata koji nisu izabrali izborne predmete
$nisu_izabrali = $_REQUEST['nisu_izabrali'];


// Odredićemo vrstu izvještaja kasnije
$fuzzy = $pola_godine = false;

// Spisak predmeta koji su imali ispit u septembru
$ispit_septembar=array();
$q5 = db_query("select i.id, i.predmet, i.komponenta from ispit as i where i.akademska_godina=$ag and (MONTH(i.datum)=8 or MONTH(i.datum)=9) and (select count(*) from ispitocjene as io where io.ispit=i.id)>0 order by i.predmet");
$staripredmet=0;
while ($r5 = db_fetch_row($q5)) {
	$predmet=$r5[1];
	if ($predmet!=$staripredmet) {
		$staripredmet=$predmet;
		$ispit_septembar[$predmet]=array();
	}
	array_push($ispit_septembar[$predmet], "$r5[0]-$r5[2]");
}

// Cache imena studija
$imena_studija = array();
$q7 = db_query("select id,kratkinaziv from studij");
while ($r7 = db_fetch_row($q7))
	$imena_studija[$r7[0]] = $r7[1];

// Spisak svih studenata iz zadnje godine
$q10 = db_query("select student, studij, semestar, plan_studija from student_studij where akademska_godina=$ag and semestar MOD 2=0");
while ($r10 = db_fetch_row($q10)) {
	$student = $r10[0];
	$studij = $r10[1];
	$semestar = $r10[2];
	$ps = $r10[3];

	// Da li je student već upisan u novoj akademskoj godini?
	$q200 = db_query("select studij, semestar, ponovac from student_studij where student=$student and akademska_godina=$novaag order by semestar");
	if (db_num_rows($q200)>0) {
		$novistudij = db_result($q200,0,0);
		$novisemestar = db_result($q200,0,1);
		$ponovac = db_result($q200,0,2);

		// Jeste, samo selektujemo predmete na koje je upisan
		$imakoliziju = false;
		$q210 = db_query("select pk.predmet, pk.semestar from ponudakursa as pk, student_predmet as sp where sp.student=$student and sp.predmet=pk.id and pk.akademska_godina=$novaag");
		while ($r210 = db_fetch_row($q210)) {
			if ($r210[1] < $novisemestar)  {
				$slusa_prenio_sigurno[$r210[0]]++;
/*if ($novistudij == 2 && $r210[1] == 3)
	print dajstudenta($student)." prenio sigurno (upisan) ".$r210[0]."<br />";
if ($novistudij == 2 && $r210[1] == 1)
	print dajstudenta($student)." prenio sigurno (upisan) ".$r210[0]."<br />";*/
				if ($imena == $r210[0]) {
					$q268 = db_query("select prezime, ime, brindexa from osoba where id=$student");
					print "- ".db_result($q268,0,0)." ".db_result($q268,0,1)." (".db_result($q268,0,2).") - već upisan (prenio)<br>";
				}
				if ($imena == -1) {
					$q268 = db_query("select prezime, ime, brindexa from osoba where id=$student");
					print "- ".db_result($q268,0,0)." ".db_result($q268,0,1)." (".db_result($q268,0,2).") - već upisan (prenio) - ".$imena_predmeta[$r210[0]]."<br>";
				}
			}

			else if ($r210[1] > $novisemestar+1) {
				$slusa_kolizija_sigurno[$r210[0]]++;
				$imakoliziju = true;
if ($r210[0]==$debug_predmet) print $k++."kolizija sigurno (trenutno sluša) $student<br>";
/*if ($novistudij == 2 && $r210[1] == 3)
	print dajstudenta($student)." kolizija sigurno (upisan) ".$r210[0]."<br />";*/
				if ($imena == $r210[0]) {
					$q268 = db_query("select prezime, ime, brindexa from osoba where id=$student");
					print "- ".db_result($q268,0,0)." ".db_result($q268,0,1)." (".db_result($q268,0,2).") - kolizija<br>";
				}
				if ($imena == -1) {
					$q268 = db_query("select prezime, ime, brindexa from osoba where id=$student");
					print "- ".db_result($q268,0,0)." ".db_result($q268,0,1)." (".db_result($q268,0,2).") - kolizija - ".$imena_predmeta[$r210[0]]."<br>";
				}
			}
			else {
				$q269 = db_query("select count(*) from student_predmet as sp, ponudakursa as pk where sp.student=$student and sp.predmet=pk.id and pk.predmet=$r210[0] and pk.akademska_godina<$novaag");
				if (db_result($q269, 0, 0) > 0) {
					$slusa_ponovac_sigurno[$r210[0]]++;
					if ($novisemestar>1) $slusa_odsjek_ponovac[$r210[0]][$novistudij]++;
if ($r210[0]==$debug_predmet) print $k++."ponovac sigurno (trenutno sluša) $student<br>";
/*if ($novistudij == 2 && $r210[1] == 3)
	print dajstudenta($student)." ponovac sigurno (upisan) ".$r210[0]."<br />";*/
					if ($imena == $r210[0]) {
						$q268 = db_query("select prezime, ime, brindexa from osoba where id=$student");
						print "- ".db_result($q268,0,0)." ".db_result($q268,0,1)." (".db_result($q268,0,2).") - već upisan (ponovac) - ".$imena_studija[$novistudij]."<br>";
					}
				}
				else {
					$slusa_redovno_sigurno[$r210[0]]++;
					$slusa_odsjek_sigurno[$r210[0]][$novistudij]++;
/*if ($novistudij == 2 && $r210[1] == 3)
	print dajstudenta($student)." redovno sigurno (upisan) ".$r210[0]."<br />";*/
if ($r210[0]==$debug_predmet) print $k++." redovno sigurno (trenutno sluša) $student<br>";
					if ($imena == $r210[0]) {
						$q268 = db_query("select prezime, ime, brindexa from osoba where id=$student");
						print "- ".db_result($q268,0,0)." ".db_result($q268,0,1)." (".db_result($q268,0,2).") - već upisan (redovno) - ".$imena_studija[$novistudij]."<br>";
					}
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
		if ($novaag_aktuelna && db_num_rows($q200)==1) {
			$pola_godine = true; // podaci za drugo pola su procjena
			if ($novistudij != $studij) {
				$q220 = db_query("select id from plan_studija where studij=$novistudij order by godina_vazenja desc limit 1");
				$ps = db_result($q220,0,0);
			}
			if ($ps==0)  continue; // Nema plana, preskačemo

			// Obavezni predmeti sa sljedećeg semestra
			$novisemestar++;
			$q230 = db_query("select pp.predmet from plan_studija_predmet psp, pasos_predmeta pp where psp.plan_studija=$novistudij and psp.semestar=$novisemestar and psp.obavezan=1 and psp.pasos_predmeta=pp.id");
			while ($r230 = db_fetch_row($q230)) {
				$predmet = $r230[0];
				$q240 = db_query("select count(*) from konacna_ocjena where student=$student and predmet=$predmet and ocjena>5");
				if (db_result($q240,0,0)==0)
					if ($ponovac) {
						$slusa_ponovac_sigurno[$predmet]++;
if ($predmet==$debug_predmet) print $k++."ponovac sigurno (non-fuzzy) $student $predmet $debug_predmet<br>";
						if ($imena == $predmet) {
							$q268 = db_query("select prezime, ime, brindexa from osoba where id=$student");
							print "- ".db_result($q268,0,0)." ".db_result($q268,0,1)." (".db_result($q268,0,2).") - ponovac $novistudij<br>";
						}
					} else {
						$slusa_redovno_sigurno[$predmet]++;
if ($predmet==$debug_predmet) print $k++."redovno sigurno (non-fuzzy) $student<br>";
						if ($imena == $predmet) {
							$q268 = db_query("select prezime, ime, brindexa from osoba where id=$student");
							print "- ".db_result($q268,0,0)." ".db_result($q268,0,1)." (".db_result($q268,0,2).") $novistudij<br>";
						}
					}
			}

			// Ugovor o učenju - studij se mora poklapati sa izabranim
			$q250 = db_query("select id from ugovoroucenju where student=$student and akademska_godina=$novaag and studij=$novistudij and semestar=$novisemestar");
			if (db_num_rows($q250)<1) {
				if ($novisemestar==2) $novisemestar=8;
				$bezizbornih_redovno_sigurno[$novistudij][$novisemestar-2]++;
				if ($nisu_izabrali == "da") {
					$q251 = db_query("select prezime, ime, brindexa from osoba where id=$student");
					$q252 = db_query("select naziv from studij where id=$novistudij");
					//print "- ".db_result($q252,0,0)." - ".($semestar)." - ".db_result($q251,0,0)." ".db_result($q251,0,1)." (".db_result($q251,0,2).")<br>";
				}
			} else {
				$uou = db_result($q250,0,0);
				$q260 = db_query("select predmet from ugovoroucenju_izborni where ugovoroucenju=$uou");
				while ($r260 = db_fetch_row($q260)) {
					$predmet = $r260[0];

					// Da li je već položio
					$q265 = db_query("select count(*) from konacna_ocjena where student=$student and predmet=$predmet and ocjena>5");
					if (db_result($q265,0,0)>0) continue;

					if ($ponovac) {
						$slusa_ponovac_sigurno[$r260[0]]++;
						$slusa_odsjek_ponovac[$r260[0]][$novistudij]++;
if ($r260[0]==$debug_predmet) print $k++."ponovac sigurno (non-fuzzy uou) $student<br>";
					} else {
						$slusa_redovno_sigurno[$r260[0]]++;
						$slusa_odsjek_sigurno[$r260[0]][$novistudij]++;
if ($r260[0]==$debug_predmet) print $k++."redovno sigurno (non-fuzzy uou) $student<br>";
					}

					if ($imena == $predmet) {
						$q268 = db_query("select prezime, ime, brindexa from osoba where id=$student");
						print "- ".db_result($q268,0,0)." ".db_result($q268,0,1)." (".db_result($q268,0,2).") ";
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
	// TODO: Implementacija podrške za izborne predmete sa drugog odsjeka usporila je generisanje izvještaja sa 2 na 8 minuta!
	// Izvesti optimiziranu varijantu
	$uslov = ima_li_uslov($student, $ag);

if ($student==$debug_student) { print "predmeti pao "; print_r ($zamger_predmeti_pao); if ($uslov) print " ima uslov<br>"; else print " nema uslov $uslov<br>"; }

	// Ima li ugovor o ucenju?
	$izborni_ugovor = array();
	$q20 = db_query("select id, studij, semestar from ugovoroucenju where student=$student and akademska_godina=$novaag order by semestar");
	if (db_num_rows($q20)<1) $imaugovor=false; else $imaugovor=true;
//if ($studij==2 && $semestar==6 && !$imaugovor) print "Nema ugovor: $student<br>";


	$ugovor_ponovac=false;
	$novistudij=$studij;
	$novisemestar=$semestar+1;

	$q21 = db_query("select s.institucija, ts.ciklus, ts.trajanje from studij as s, tipstudija as ts where s.id=$studij and s.tipstudija=ts.id");
	$s_institucija = db_result($q21,0,0);
	$s_ciklus = db_result($q21,0,1);
	$s_trajanje = db_result($q21,0,2);
	
	// Ako je završni semestar izbacićemo završni rad iz spiska nepoloženih predmeta
	if ($semestar == $s_trajanje) {
		// Prilagođavamo globalne varijable za uslov
		$uslov_predmeta = 0;
		$uslov_ects_kredita = 0;
		// Nema kolizije sa ciklusa na ciklus
		$uslov_kolizija = 0;		
		
		if (count($zamger_predmeti_pao) == 1) {
			$zamger_predmeti_pao = array();
			$uslov = true;
		} else {
			// Nađi završni rad u predmetima... mrsko mi je sad to
		}
if ($student==$debug_student) { print "zadnji semestar, sada je: "; print_r ($zamger_predmeti_pao); if ($uslov) print " ima uslov<br>"; else print " nema uslov $uslov<br>"; }
	} else {
		$uslov_predmeta = $conf_uslov_predmeta;
		$uslov_ects_kredita = $conf_uslov_ects_kredita;
		$uslov_kolizija = $conf_uslov_kolizija;
	}

	if ($imaugovor) {
		$novistudij = db_result($q20,0,1);
		$novisemestar = db_result($q20,0,2);
if ($student==$debug_student) print "ima ugovor $novistudij $novisemestar<br>";
//if ($novistudij != $studij) print "mijenja studij $studij -> $novistudij<br>";

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

			$q23 = db_query("select id from plan_studija where studij=$novistudij order by godina_vazenja desc limit 1");
			$ps = db_result($q23,0,0);

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
			$q22 = db_query("select s.id from studij as s, tipstudija as ts where s.institucija=$s_institucija and s.tipstudija=ts.id and ts.ciklus=".($s_ciklus+1));
			if (db_num_rows($q22)>0) {
				$novistudij=db_result($q22,0,0);
				$novisemestar=1;
				$q23 = db_query("select id from plan_studija where studij=$novistudij order by godina_vazenja desc limit 1");
				if (db_num_rows($q23)>0) {
					$ps = db_result($q23,0,0);
//		print "Student: $student Novistudij: $novistudij Stariciklus: $s_ciklus PS4: $ps<br>";
				} else {
					// Ne postoji plan studija za odgovarajući studij sljedećeg ciklusa
					$novistudij=0;
					$novisemestar=0;
				}

			} else { //nema više studija poslije ovog
				$novistudij=0;
				$novisemestar=0;
			}
		}
	}

	// Ima li zahtjev za koliziju?
	$kolizija = array();
	$q30 = db_query("select semestar, predmet from kolizija where student=$student and akademska_godina=$novaag");
	if (db_num_rows($q30)<1) $imakoliziju=false; 
	else {
		//if (db_result($q30,0,0)<=$semestar) $imakoliziju=false; // student ne zna kako da popuni zahtjev za koliziju
		//else 
			$imakoliziju=true;
	}
if ($student==$debug_student) print "kolizija ".($imakoliziju?1:0)."<br>";

	// Student koji preko kolizije mijenja studij
	if ($imakoliziju && !$imaugovor) {
		while ($r30 = db_fetch_row($q30)) {
			$q31 = db_query("SELECT ps.studij, psp.semestar FROM plan_studija ps, plan_studija_predmet psp, pasos_predmeta pp WHERE pp.predmet=$r30[1] AND psp.pasos_predmeta=pp.id AND psp.obavezan=1 AND psp.plan_studija=ps.id ORDER BY ps.godina_vazenja DESC, ps.id DESC LIMIT 1"); // Sumnjivo?
			if (db_num_rows($q31)>0) {
				if ($novistudij != db_result($q31,0,0)) {
					$novistudij= db_result($q31,0,0);
					$novisemestar=db_result($q31,0,1);
					if ($novisemestar%2==0) $novisemestar--;
if ($student==$debug_student) print "mijenja smjer preko kolizije $novistudij $novisemestar<br>";
				}
				break;
			}
		}
		$q30 = db_query("select semestar, predmet from kolizija where student=$student and akademska_godina=$novaag");
	}

	// Spisak predmeta sa više godine koje je student položio koliziono
	$pao_koliziono = $polozio_koliziono = array();
	$q35 = db_query("select distinct pk.predmet from student_predmet as sp, ponudakursa as pk where sp.student=$student and sp.predmet=pk.id and pk.semestar>$semestar");
	while ($r35 = db_fetch_row($q35)) {
		$q37 = db_query("select count(*) from konacna_ocjena where student=$student and predmet=$r35[0] and ocjena>5");
		if (db_result($q37,0,0)>0)
			array_push($polozio_koliziono, $r35[0]);
		else
			array_push($pao_koliziono, $r35[0]);

	}
if ($student==$debug_student) { print "polozio koliziono  "; print_r ($polozio_koliziono); print "<br>\n"; print "pao koliziono "; print_r ($pao_koliziono); print "<br>\n"; }


	// Šta je položio u septembru?
	$pao_septembar=$polozio_septembar=array();
	foreach ($zamger_predmeti_pao as $predmet => $naziv) {
		$polozio=$pao=false;
		if ($ispit_septembar[$predmet])
		foreach ($ispit_septembar[$predmet] as $ispitkomponenta) {
			list($ispit,$komponenta) = explode("-", $ispitkomponenta);

			// Da li je student ranije položio ispit?
			$q60 = db_query("select count(*) from ispit as i, ispitocjene as io, komponenta as k where i.predmet=$predmet and i.akademska_godina=$ag and MONTH(i.datum)!=8 and MONTH(i.datum)!=9 and i.komponenta=k.id and io.ispit=i.id and io.student=$student and io.ocjena>=k.prolaz and k.id=$komponenta");
			if (db_result($q60,0,0)>0) continue; // Jeste

			// A da li je položio u septembru?
			$q70 = db_query("select count(*) from ispit as i, ispitocjene as io, komponenta as k where i.id=$ispit and i.komponenta=k.id and io.ispit=i.id and io.student=$student and io.ocjena>=k.prolaz");
			if (db_result($q70,0,0)>0)
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

		$q40 = db_query("SELECT pp.predmet FROM plan_studija_predmet psp, pasos_predmeta pp WHERE psp.plan_studija=$ps and (psp.semestar=$novisemestar or psp.semestar=".($novisemestar+1).") and psp.obavezan=1 AND psp.pasos_predmeta=pp.id");
		while ($r40 = db_fetch_row($q40)) {
			if (in_array($r40[0], $pao_koliziono)) {
				$slusa_ponovac_sigurno[$r40[0]]++;
				$slusa_odsjek_ponovac[$r40[0]][$novistudij]++;
				if ($imena == $r40[0]) {
					$q268 = db_query("select prezime, ime, brindexa from osoba where id=$student");
					print "- ".db_result($q268,0,0)." ".db_result($q268,0,1)." (".db_result($q268,0,2).") - ponovac sigurno (pao koliziono, ima uslov)<br>";
				}
			} else
			if (!in_array($r40[0], $polozio_koliziono)) {
				$slusa_redovno_sigurno[$r40[0]]++;
				$fali_u_koliziji++;
				$slusa_odsjek_sigurno[$r40[0]][$novistudij]++;
if ($r40[0]==$debug_predmet) print $k++." redovno sigurno (obavezan) $student<br>";
/*if ($novistudij == 2 && $novisemestar == 3)
	print dajstudenta($student)." redovno sigurno (obavezan) ".$r40[0]."<br />";*/

//if ($student==$debug_student) { print "ima uslov slusa redovno sigurno $r40[0] po planu studija $ps novistudij $novistudij semestar $novisemestar<br>"; }
				if ($imena == $r40[0]) {
					$q268 = db_query("select prezime, ime, brindexa from osoba where id=$student");
					print "- ".db_result($q268,0,0)." ".db_result($q268,0,1)." (".db_result($q268,0,2).") - redovno sigurno (obavezan, ima uslov)<br>";
				}
			}
		}

		foreach ($zamger_predmeti_pao as $predmet => $naziv)
			if (in_array($predmet, $pao_septembar)) {
				$slusa_prenio_sigurno[$predmet]++;
if ($predmet==$debug_predmet) print "prenio sigurno (pao, dao uslov) $student<br>";
/*if ($novistudij == 2 && $novisemestar == 5)
	print dajstudenta($student)." prenio sigurno ".$predmet."<br />";
if ($novistudij == 2 && $novisemestar == 3)
	print dajstudenta($student)." prenio sigurno ".$predmet."<br />";*/
				if ($imena == $predmet) {
					$q268 = db_query("select prezime, ime, brindexa from osoba where id=$student");
					print "- ".db_result($q268,0,0)." ".db_result($q268,0,1)." (".db_result($q268,0,2).") - prenio sigurno (ima uslov)<br>";
				}
			}
			else if (!in_array($predmet, $polozio_septembar)) {
				$slusa_prenio_mozda[$predmet]++;
if ($predmet==$debug_predmet) print "prenio možda (pao, dao uslov) $student<br>";
/*if ($novistudij == 2 && $novisemestar == 5)
	print dajstudenta($student)." prenio možda ".$predmet."<br />";
if ($novistudij == 2 && $novisemestar == 3)
	print dajstudenta($student)." prenio možda ".$predmet."<br />";*/
				if ($imena == $predmet) {
					$q268 = db_query("select prezime, ime, brindexa from osoba where id=$student");
					print "- ".db_result($q268,0,0)." ".db_result($q268,0,1)." (".db_result($q268,0,2).") - prenio možda (ima uslov)<br>";
				}
			}

		// 1A. Ima ugovor o učenju
		if ($imaugovor && !$ugovor_ponovac) {
			$q50 = db_query("select predmet from ugovoroucenju_izborni where ugovoroucenju=".db_result($q20,0,0)." or ugovoroucenju=".db_result($q20,1,0));
			while ($r50 = db_fetch_row($q50)) {
				if (!in_array($r50[0], $polozio_koliziono)) {
					$slusa_redovno_sigurno[$r50[0]]++;
					$fali_u_koliziji++;
					$slusa_odsjek_sigurno[$r50[0]][$novistudij]++;
if ($r50[0]==$debug_predmet) print "redovno sigurno (uou) $student<br>";
/*if ($novistudij == 2 && $novisemestar == 3)
	print dajstudenta($student)." redovno sigurno (uou) ".$r50[0]."<br />";*/
					if ($imena == $r50[0]) {
						$q268 = db_query("select o.prezime, o.ime, o.brindexa, s.kratkinaziv from osoba as o, student_studij as ss, studij as s where o.id=$student and o.id=ss.student and ss.studij=s.id and ss.akademska_godina=$ag");
						print "- ".db_result($q268,0,0)." ".db_result($q268,0,1)." (".db_result($q268,0,2).") (".db_result($q268,0,3).") - redovno sigurno (popunio ugovor, ima uslov)<br>";
					}
				}
			}
		
		// 1B. Nema ugovor o ucenju ili je popunio za ponavljanje
		} else {
			$bezizbornih_redovno_sigurno[$novistudij][$semestar]++;
/*if ($novistudij == 2 && $novisemestar == 3)
	print dajstudenta($student)." nije odabrao izborne<br />";*/
			// Odredjujemo koliko fali u koliziji
			$q55 = db_query("select count(*) from plan_studija_predmet where plan_studija=$ps and (semestar=$novisemestar or semestar=".($novisemestar+1).") and obavezan=0");
			$fali_u_koliziji += db_result($q55,0,0);
			if ($nisu_izabrali == "da") {
				$q251 = db_query("select prezime, ime, brindexa from osoba where id=$student");
				$q252 = db_query("select naziv from studij where id=$novistudij");
				//print "- ".db_result($q252,0,0)." ".($semestar)." - ".db_result($q251,0,0)." ".db_result($q251,0,1)." (".db_result($q251,0,2).")<br>";
			}
		}

/*		// 1X. Da li ima uslove da koliziono odmah sluša sljedeću godinu?
		if ($imakoliziju && db_result($q30,0,0)>$semestar+2 && $fali_u_koliziji<=3) {
			while ($r30 = db_fetch_row($q30))
				$slusa_kolizija_sigurno[$r30[1]]++;
			$br_kolizija_nema_uslov[$novistudij][$semestar+2]++;
		}*/

		$br_ima_uslov[$novistudij][$semestar]++;
		if (!$imaugovor && $novistudij != 0) $ima_uslov_nema_ugovor[] = $student;

		continue;
	}


	// 2. Ašćare dao uslov u septembru samo ocjene još nisu upisane
	if (count($polozio_septembar)>0 && count($zamger_predmeti_pao)-count($polozio_septembar) <= $uslov_predmeta) {
if ($student==$debug_student) print "Ašćare ima uslov<br>";
		$fali_u_koliziji=0;

		$q40 = db_query("SELECT pp.predmet FROM plan_studija_predmet psp, pasos_predmeta pp WHERE psp.plan_studija=$ps and (psp.semestar=$novisemestar or psp.semestar=".($novisemestar+1).") and psp.obavezan=1 AND psp.pasos_predmeta=pp.id");
		while ($r40 = db_fetch_row($q40)) {
			if (!in_array($r40[0], $polozio_koliziono)) {
				$slusa_redovno_sigurno[$r40[0]]++;
				$fali_u_koliziji++;
				$slusa_odsjek_sigurno[$r40[0]][$novistudij]++;
if ($r40[0]==$debug_predmet) print "redovno sigurno (obavezan ašćare) $student<br>";
/*if ($novistudij == 2 && $novisemestar == 3)
	print dajstudenta($student)." redovno sigurno (obavezan sept) ".$r40[0]."<br />";*/
				if ($imena == $r40[0]) {
					$q268 = db_query("select prezime, ime, brindexa from osoba where id=$student");
					print "- ".db_result($q268,0,0)." ".db_result($q268,0,1)." (".db_result($q268,0,2).") - redovno sigurno (obavezan, dao uslov u septembru)<br>";
				}
			}
		}

		foreach ($zamger_predmeti_pao as $predmet => $naziv) {
			if (in_array($predmet, $pao_septembar)) {
				$slusa_prenio_sigurno[$predmet]++;
if ($predmet==$debug_predmet) print "prenio sigurno (pao, uslov ašćare) $student<br>";
/*if ($novistudij == 2 && $novisemestar == 5)
	print dajstudenta($student)." prenio sigurno (sept) ".$predmet."<br />";
if ($novistudij == 2 && $novisemestar == 3)
	print dajstudenta($student)." prenio sigurno (sept) ".$predmet."<br />";*/
				if ($imena == $predmet) {
					$q268 = db_query("select prezime, ime, brindexa from osoba where id=$student");
					print "- ".db_result($q268,0,0)." ".db_result($q268,0,1)." (".db_result($q268,0,2).") - prenio sigurno (dao uslov u septembru)<br>";
				}
			} else if (!in_array($predmet, $polozio_septembar)) {
				$slusa_prenio_mozda[$predmet]++;
if ($predmet==$debug_predmet) print "prenio sigurno (pao, uslov ašćare) $student<br>";
/*if ($novistudij == 2 && $novisemestar == 5)
	print dajstudenta($student)." prenio možda (sept) ".$predmet."<br />";*/
				if ($imena == $predmet) {
					$q268 = db_query("select prezime, ime, brindexa from osoba where id=$student");
					print "- ".db_result($q268,0,0)." ".db_result($q268,0,1)." (".db_result($q268,0,2).") - prenio možda (dao uslov u septembru)<br>";
				}
			}
		}

		// 2A. Ima ugovor o učenju
		if ($imaugovor && !$ugovor_ponovac) {
			$q50 = db_query("select predmet from ugovoroucenju_izborni where ugovoroucenju=".db_result($q20,0,0)." or ugovoroucenju=".db_result($q20,1,0));
			while ($r50 = db_fetch_row($q50)) {
				if (!in_array($r50[0], $polozio_koliziono)) {
					$slusa_redovno_sigurno[$r50[0]]++;
					$fali_u_koliziji++;
					$slusa_odsjek_sigurno[$r50[0]][$novistudij]++;
if ($r50[0]==$debug_predmet) print "redovno sigurno (uou ašćare) $student<br>";
/*if ($novistudij == 2 && $novisemestar == 3)
	print dajstudenta($student)." redovno sigurno (uou sept) ".$r50[0]."<br />";*/
					if ($imena == $r50[0]) {
						$q268 = db_query("select o.prezime, o.ime, o.brindexa, s.kratkinaziv from osoba as o, student_studij as ss, studij as s where o.id=$student and o.id=ss.student and ss.studij=s.id and ss.akademska_godina=$ag");
						print "- ".db_result($q268,0,0)." ".db_result($q268,0,1)." (".db_result($q268,0,2).") (".db_result($q268,0,3).") - redovno sigurno (popunio ugovor, dao uslov u septembru)<br>";
					}
				}
			}
		
		// 2B. Nema ugovor o ucenju ili je popunio za ponavljanje
		} else {
			$bezizbornih_redovno_sigurno[$novistudij][$semestar]++;
/*if ($novistudij == 2 && $novisemestar == 3)
	print dajstudenta($student)." nije odabrao izborne (sept)<br />";*/
			// Odredjujemo koliko fali u koliziji
			$q55 = db_query("select count(*) from plan_studija_predmet where plan_studija=$ps and (semestar=".($novisemestar+1)." or semestar=$novisemestar) and obavezan=0");
			$fali_u_koliziji += db_result($q55,0,0);
			if ($nisu_izabrali == "da") {
				$q251 = db_query("select prezime, ime, brindexa from osoba where id=$student");
				$q252 = db_query("select naziv from studij where id=$studij");
				//print "- ".db_result($q252,0,0)." ".($semestar)." - ".db_result($q251,0,0)." ".db_result($q251,0,1)." (".db_result($q251,0,2).")<br>";
			}
		}

		// 2X. Da li ima uslove da koliziono odmah sluša sljedeću godinu?
/*		if ($imakoliziju && db_result($q30,0,0)>$semestar+2 && $fali_u_koliziji<=3) {
			while ($r30 = db_fetch_row($q30))
				$slusa_kolizija_sigurno[$r30[1]]++;
			$br_kolizija_nema_uslov[$novistudij][$semestar+2]++;
		}*/

		$br_ima_uslov_sept[$novistudij][$semestar]++;

		continue;
	}


	// Šta je student izjavio da će položiti u septembru kako bi stekao pravo na koliziju?
	$kolizioni_septembar = array();
	$ostvario_septembar = true;
	$q80 = db_query("select predmet from septembar where student=$student and akademska_godina=$ag");
	while ($r80 = db_fetch_row($q80)) {
		if (in_array($r80[0], $pao_septembar))
			$ostvario_septembar = false;
		else if (!in_array($r80[0], $polozio_septembar))
			array_push($kolizioni_septembar, $r80[0]);
	}


	// Ostale kategorije studenata su, prema sadašnjem stanju, ponovci
	foreach ($zamger_predmeti_pao as $predmet => $naziv) {
		if (in_array($predmet, $pao_septembar)) {
			$slusa_ponovac_sigurno[$predmet]++;
if ($predmet==$debug_predmet) print "ponovac sigurno $student<br>";
/*if ($novistudij == 2 && $novisemestar == 3)
	print dajstudenta($student)." ponovac sigurno ".$r50[0]."<br />";*/
			if ($imena == $predmet) {
				$q268 = db_query("select prezime, ime, brindexa from osoba where id=$student");
				print "- ".db_result($q268,0,0)." ".db_result($q268,0,1)." (".db_result($q268,0,2).") - ponovac sigurno<br>";
			}
		}
		else if (!in_array($predmet, $polozio_septembar)) {
			$slusa_ponovac_mozda[$predmet]++;
if ($predmet==$debug_predmet) print "ponovac možda $student<br>";
/*if ($novistudij == 2 && $novisemestar == 3)
	print dajstudenta($student)." ponovac možda ".$r50[0]."<br />";*/
			if ($imena == $predmet) {
				$q268 = db_query("select prezime, ime, brindexa from osoba where id=$student");
				print "- ".db_result($q268,0,0)." ".db_result($q268,0,1)." (".db_result($q268,0,2).") - ponovac možda (nisu objavljeni rez. za septembar)<br>";
			}
		}
	}


	// 3. Ima uslove za koliziju, popunio zahtjev
if ($student==$debug_student) print "uslov1 ".(count($zamger_predmeti_pao)-count($polozio_septembar))." uslov2 ".($imakoliziju?1:0)." uslov3 ".($ostvario_septembar?1:0)."<br>";
	if (count($zamger_predmeti_pao)-count($polozio_septembar) <= $uslov_kolizija && $imakoliziju && $ostvario_septembar) {
if ($student==$debug_student) print "Kolizija<br>";
		// Predmeti koje sluša koliziono
		$ovaj_student_kolizija=array();
		while ($r30 = db_fetch_row($q30)) {
			$predmet = $r30[1];
			if (in_array($predmet, $pao_koliziono)) {
				$slusa_ponovac_sigurno[$predmet]++; // Brojimo ga kao ponovca
if ($predmet==$debug_predmet) print "ponovac, kolizija (ima uslov) $student<br>";
				array_push($ovaj_student_kolizija, $predmet);
				if ($imena == $predmet) {
					$q268 = db_query("select prezime, ime, brindexa from osoba where id=$student");
					print "- ".db_result($q268,0,0)." ".db_result($q268,0,1)." (".db_result($q268,0,2).") - ponovac sigurno (popunio zahtjev za koliziju)<br>";
				}
			}
			else if (!in_array($predmet, $polozio_koliziono)) {
				// Ako je student ostavio neki predmet za septembar, umjesto njega je izabrao neki drugi na višoj godini, a ne znamo koji - moraće se opredijeliti ako ne položi
				if (count($kolizioni_septembar)>0)
					$slusa_kolizija_mozda[$predmet]++;
				else 
					$slusa_kolizija_sigurno[$predmet]++;

if ($predmet==$debug_predmet) print "kolizija (ima uslov) $student<br>";
/*if ($novistudij == 2 && $novisemestar == 3)
	print dajstudenta($student)." kolizija sigurno (možda uslov) ".$predmet."<br />";*/
				array_push($ovaj_student_kolizija, $predmet);
				if ($imena == $predmet) {
					$q268 = db_query("select prezime, ime, brindexa from osoba where id=$student");
					print "- ".db_result($q268,0,0)." ".db_result($q268,0,1)." (".db_result($q268,0,2).") - kolizija sigurno (popunio zahtjev)<br>";
				}
			}
		}

		// 3A. Ima šansi da upiše redovno
		if (!$ugovor_ponovac && count($pao_septembar) <= $uslov_predmeta) {
			$q40 = db_query("SELECT pp.predmet FROM plan_studija_predmet psp, pasos_predmeta pp WHERE psp.plan_studija=$ps and (psp.semestar=$novisemestar or psp.semestar=".($novisemestar+1).") and psp.obavezan=1 AND psp.pasos_predmeta=pp.id");
			while ($r40 = db_fetch_row($q40))
				if (!in_array($r40[0], $ovaj_student_kolizija) && !in_array($r40[0], $polozio_koliziono)) {
					$slusa_redovno_mozda[$r40[0]]++;
if ($r40[0]==$debug_predmet) print "redovno možda (obavezan) $student<br>";
/*if ($novistudij == 2 && $novisemestar == 3)
	print dajstudenta($student)." redovno možda (obavezan) ".$r40[0]."<br />";*/
					if ($imena == $r40[0]) {
						$q268 = db_query("select prezime, ime, brindexa from osoba where id=$student");
						print "- ".db_result($q268,0,0)." ".db_result($q268,0,1)." (".db_result($q268,0,2).") - redovno možda (popunio zahtjev za koliziju)<br>";
					}
				}
			
			// 3A1. Ima ugovor o učenju za višu godinu
			if ($imaugovor) {
				$q50 = db_query("select predmet from ugovoroucenju_izborni where ugovoroucenju=".db_result($q20,0,0)." or ugovoroucenju=".db_result($q20,1,0));
				while ($r50 = db_fetch_row($q50))
					if (!in_array($r50[0], $ovaj_student_kolizija) && !in_array($r50[0], $polozio_koliziono)) {
						$slusa_redovno_mozda[$r50[0]]++;
if ($r50[0]==$debug_predmet) print "redovno možda (uou) $student<br>";
/*if ($novistudij == 2 && $novisemestar == 3)
	print dajstudenta($student)." redovno možda (uou) ".$r50[0]."<br />";*/
						if ($imena == $r50[0]) {
							$q268 = db_query("select prezime, ime, brindexa from osoba where id=$student");
							print "- ".db_result($q268,0,0)." ".db_result($q268,0,1)." (".db_result($q268,0,2).") - redovno možda (popunio ugovor o učenju)<br>";
						}
					}


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
	if ($imakoliziju && $ostvario_septembar && count($zamger_predmeti_pao)-count($polozio_septembar) <= $uslov_predmeta+2 && count($pao_septembar) <= $uslov_kolizija) {
if ($student==$debug_student) print "Možda kolizija<br>";
		// Predmeti koje sluša koliziono
		while ($r30 = db_fetch_row($q30)) {
			$predmet = $r30[1];
			if (!in_array($predmet, $polozio_koliziono)) {
				$slusa_kolizija_mozda[$predmet]++;
if ($predmet==$debug_predmet) print "kolizija možda $student<br>";
/*if ($novistudij == 2 && $novisemestar == 3)
	print dajstudenta($student)." kolizija možda ".$predmet."<br />";*/
				if ($imena == $predmet) {
					$q268 = db_query("select prezime, ime, brindexa from osoba where id=$student");
					print "- ".db_result($q268,0,0)." ".db_result($q268,0,1)." (".db_result($q268,0,2).") - kolizija možda (popunio zahtjev za koliziju)<br>";
				}
			}
		}
		
		// Smatramo da nije realno da će dati uslov, bez obzira na ugovor

		$br_mozda_kolizija[$novistudij][$semestar]++;

		continue;
	}

	// 5. Nije tražio koliziju, ima šansi za uslov
	if (!$ugovor_ponovac && count($pao_septembar) <= $uslov_predmeta && count($zamger_predmeti_pao)-count($polozio_septembar) <= $realno_poloziti_u_septembru) {
if ($student==$debug_student) print "Šanse za uslov bez kolizije<br>";
		$q40 = db_query("SELECT pp.predmet FROM plan_studija_predmet psp, pasos_predmeta pp WHERE psp.plan_studija=$ps and (psp.semestar=$novisemestar or psp.semestar=".($novisemestar+1).") and psp.obavezan=1 AND psp.pasos_predmeta=pp.id");
		while ($r40 = db_fetch_row($q40))
			if (!in_array($r40[0], $polozio_koliziono)) {
				$slusa_redovno_mozda[$r40[0]]++;
if ($r40[0]==$debug_predmet) print "redovno možda (možda uslov bez kolizije) $student<br>";
/*if ($novistudij == 2 && $novisemestar == 3)
	print dajstudenta($student)." redovno možda (neće koliziju) ".$r40[0]."<br />";*/
				if ($imena == $r40[0]) {
					$q268 = db_query("select prezime, ime, brindexa from osoba where id=$student");
					print "- ".db_result($q268,0,0)." ".db_result($q268,0,1)." (".db_result($q268,0,2).") - redovno možda (nije tražio koliziju)<br>";
				}
			}
		
		// 5A1. Ima ugovor o učenju za višu godinu
		if ($imaugovor) {
			$q50 = db_query("select predmet from ugovoroucenju_izborni where ugovoroucenju=".db_result($q20,0,0)." or ugovoroucenju=".db_result($q20,1,0));
			while ($r50 = db_fetch_row($q50))
				if (!in_array($r50[0], $polozio_koliziono)) {
					$slusa_redovno_mozda[$r50[0]]++;
if ($r50[0]==$debug_predmet) print "redovno možda (uou bez kolizije) $student<br>";
/*if ($novistudij == 2 && $novisemestar == 3)
	print dajstudenta($student)." redovno možda (uou, neće koliziju) ".$r50[0]."<br />";*/
					if ($imena == $r50[0]) {
						$q268 = db_query("select prezime, ime, brindexa from osoba where id=$student");
						print "- ".db_result($q268,0,0)." ".db_result($q268,0,1)." (".db_result($q268,0,2).") - redovno možda (nije tražio koliziju, ima ugovor)<br>";
					}
				}

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
	<?=count($ima_uslov_nema_ugovor)?> studenata ima uslov, a nisu popunili Ugovor o učenju! <img src="static/images/plus.png" width="13" height="13" id="img-ag-1" onclick="daj()">.</p>
	<div id="ima_uslov_nema_ugovor" style="display:none">
	<?
	foreach ($ima_uslov_nema_ugovor as $student) {
		$q99 = db_query("select ime, prezime, brindexa from osoba where id=$student");
		$r99 = db_fetch_row($q99);
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

$q100 = db_query("select psp.pasos_predmeta, psp.plan_izborni_slot, s.id, s.naziv, psp.semestar, psp.obavezan, ts.ciklus, s.institucija from plan_studija_predmet as psp, plan_studija as ps, studij as s, tipstudija as ts where ps.studij=s.id and (ps.godina_vazenja=10 or ps.godina_vazenja=4) and psp.plan_studija=ps.id AND s.tipstudija=ts.id order by ts.ciklus, s.naziv, psp.semestar, psp.obavezan DESC"); // FIXME ukodirani planovi studija - ovo sada neće raditi!
$oldstudij=$oldsemestar=$oldobavezan="";

$predmeti_ispis=array();

// Ispisujemo podatke "od-do" ili samo jedan broj ako se ne razlikuju
function od_do ($br1, $br2) {
	$br1 = intval($br1); // ako je blank dobićemo nulu
	$br2 = $br1+$br2;
	if ($br1==$br2) return $br1;
	return "$br1 - $br2";
}

$qblesavo = db_query("select id, kratkinaziv from studij order by id");
while ($rblesavo = db_fetch_row($qblesavo)) {
	$naziv_studijaa[$rblesavo[0]]=$rblesavo[1];
}

$institucija=1;

while ($r100 = db_fetch_row($q100)) {
	$studij=$r100[2];
	$naziv_studija = $r100[3];
	$semestar=$r100[4];
	$obavezan=$r100[5];
	$ciklus=$r100[6];
	$oldinstitucija=$institucija;
	$institucija=$r100[7];

	if ($semestar!=$oldsemestar || $obavezan==1) {
		$x=0;
		foreach ($predmeti_ispis as $predmet => $naziv_predmeta) {
			if ($predmeti_institucija[$predmet] != $oldinstitucija) continue;
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
	
			print "<tr><td>$rbr</td><td><a href=\"?sta=izvjestaj/ugovoroucenju&imena=$predmet\">$naziv_predmeta</a></td><td>$redovno $dodaj</td><td>$kolizija</td><td bgcolor=\"#CCCCCC\">$uk1</td><td>$ponovac $dodajpon</td><td>$prenio</td><td bgcolor=\"#CCCCCC\">$uk2</td>\n</tr>\n";
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
//			$q105 = db_query("select s.id from studij as s, tipstudija as ts where s.tipstudija=ts.id and ts.ciklus=1 and s.institucija=$institucija");
//			$statstudij=db_result($q105,0,0);
$statstudij=$studij;
		} else {
			$statsem=$semestar-1;
			$statstudij=$studij;
		}

		if ($statsem==0) { // Prvi semestar prvog ciklusa
			// Ako još nije krenula sljedeća godina, ovaj broj će se popunjavati brucošima kako traje upis
			$q107 = db_query("select count(*) from student_studij where studij=$studij and akademska_godina=$novaag and semestar=1 and ponovac=0");
			$upisano_na_studij = db_result($q107,0,0);
			print "<p>Na ovom odsjeku upisano $upisano_na_studij redovnih i ".intval($br_ponovac[$studij][2])." ponovaca</p>";

			$q107 = db_query("select count(*) from student_studij as ss, studij as s, tipstudija as ts where ss.akademska_godina=$novaag and ss.semestar=1 and ss.studij=s.id and s.tipstudija=ts.id and ts.ciklus=1 and ss.ponovac=0");
			$upisano_na_studij = db_result($q107,0,0);
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
		$q110 = db_query("select p.id, pp.naziv, p.institucija from predmet p, pasos_predmeta pp where pp.id=$r100[0] AND pp.predmet=p.id");
		db_fetch3($q110, $predmet, $naziv_predmeta, $pinstitucija);
		// FIXME ne koristiti instituciju ovdje!
		$predmeti_ispis[$predmet] = $naziv_predmeta;
		$predmeti_institucija[$predmet] = $pinstitucija;
		$izborni_print=0;
	} else {
		$q120 = db_query("select p.id, pp.naziv, p.institucija from plan_izborni_slot as pis, predmet as p, pasos_predmeta as pp where pis.id=$r100[1] and pis.pasos_predmeta=pp.id AND pp.predmet=p.id");
		while(db_fetch3($q120, $predmet, $naziv_predmeta, $pinstitucija)) {
			$predmeti_ispis[$predmet] = $naziv_predmeta;
			$predmeti_institucija[$predmet] = $pinstitucija;
		}
		$izborni_print=1;
	}
}


// Ispis zadnjih redova...
$x=0;
foreach ($predmeti_ispis as $predmet => $naziv_predmeta) {
	if ($predmeti_institucija[$predmet] != $institucija) continue;
	if ($izborni_print==1 && $x==1) $naziv_predmeta .= " *";
	$x=1;

	if ($ciklus==1 && $oldsemestar<=2) $slusa_redovno_sigurno[$predmet]=$upisano_na_studij;

	$redovno = od_do($slusa_redovno_sigurno[$predmet], $slusa_redovno_mozda[$predmet]);
	$kolizija = od_do($slusa_kolizija_sigurno[$predmet], $slusa_kolizija_mozda[$predmet]);
	$uk1 = od_do($slusa_redovno_sigurno[$predmet]+$slusa_kolizija_sigurno[$predmet], $slusa_redovno_mozda[$predmet]+$slusa_kolizija_mozda[$predmet]);


	$ponovac = od_do($slusa_ponovac_sigurno[$predmet], $slusa_ponovac_mozda[$predmet]);
	$prenio = od_do($slusa_prenio_sigurno[$predmet], $slusa_prenio_mozda[$predmet]);

	$uk2 = od_do($slusa_redovno_sigurno[$predmet]+$slusa_kolizija_sigurno[$predmet]+$slusa_ponovac_sigurno[$predmet]+$slusa_prenio_sigurno[$predmet], $slusa_redovno_mozda[$predmet]+$slusa_kolizija_mozda[$predmet]+$slusa_ponovac_mozda[$predmet]+$slusa_prenio_mozda[$predmet]);

	print "<tr><td>$rbr $k</td><td><a href=\"?sta=izvjestaj/ugovoroucenju&imena=$predmet\">$naziv_predmeta</a></td><td>$redovno</td><td>$kolizija</td><td bgcolor=\"#CCCCCC\">$uk1</td><td>$ponovac</td><td>$prenio</td><td bgcolor=\"#CCCCCC\">$uk2</td>\n</tr>\n";
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
