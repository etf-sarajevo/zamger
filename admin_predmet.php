<?

// * v2.9.3.0 (?) Zamger3 RC1
// v2.9.3.1 (2007/03/11) + libvedran 0.0.5, input validation
// v2.9.3.2 (2007/03/13) + editovanje imena grupe
// v2.9.3.3 (2007/03/16) + rucno ubacivanje zadataka u zadacu, nova labela za broj zadataka
// v2.9.3.4 (2007/03/26) + novo ime za genform() kod dodavanja ispita
// v3.0.0.0 (2007/04/09) + Release
// v3.0.0.1 (2007/04/25) + Rezultati ispita: konvertuj decimalni zarez u tačku, 
// ispravka greške u SQL upitu, nova imena varijabli za datum, ispravljeno više 
// semantičkih grešaka, dodana provjera za ponavljanje studenata u rezultatima
// v3.0.0.2 (2007/05/04) + Kompaktovanje baze
// v3.0.0.3 (2007/05/24) + Ispravka greške do koje je došlo zbog prelaska na FROM_UNIXTIME
// v3.0.1.0 (2007/06/12) + Release
// v3.0.1.1 (2007/09/11) + U tabeli ispitocjena sada je razdvojen prvi i drugi parcijalni, naziv se ignoriše; dodan unos konačne ocjene; poništena vrijednost varijable fakatradi kod masovnih unosa; izbačeno kompaktovanje (to će biti u siteadminu)
// v3.0.1.2 (2007/09/20) + Dodano dugme Nazad na sve ekrane za potvrdu (usability), korištenje rtrim() u masovnom unosu, dodan link na izvještaj "spisak studenata po grupama"
// v3.0.1.3 (2007/09/24) + Popravljen bug sa većim brojem razmaka kod masovnog unosa
// v3.0.1.4 (2007/10/02) + Dodan logging
// v3.0.1.5 (2007/10/05) + Ispravke bugova kod kopiranja grupa: zabranjeno kopiranje sa istog predmeta, prebacivanje studenta (ako je vec upisan na predmet), spoji grupe ako se isto zovu
// v3.0.1.6 (2007/10/08) + Nova struktura baze za predmete; izbacen jedan broj opcija iz taba "Opcije" (sad je to u modulu Nihada)
// v3.0.1.7 (2007/10/09) + Popravljen bug sa kopiranjem predmeta, dodana provjera prava pristupa
// v3.0.1.8 (2007/10/16) + SQL bug u provjeri permisija
// v3.0.1.9 (2007/10/19) + Nova shema tabele ispita
// v3.0.1.10 (2007/10/20) + Odvojen tab "Izvjestaji", dodan izvjestaj sa komentarima
// v3.0.1.11 (2007/10/24) + massinput: Brisanje starih podataka o prisustvu; Ne radi nista ako student ostaje u istoj grupi
// v3.0.1.12 (2007/10/25) + Ukinut "Naziv ispita" posto sada "Tip ispita" igra tu ulogu; dodan format "Ime Prezime[tab]ocjena" u konačne ocjene
// v3.0.1.13 (2007/11/02) + Numerisani izvjestaji u kartici "Izvjestaji"
// v3.0.1.14 (2007/11/08) + Zabrani registrovanje novih studenata kroz massinput; novi formati za massinput (ukinute opcije koje ne interesuju nastavnike, dodano Ime-Prezime); nova opcija "upisi sve studente sa semestra"; popravljen log za massexam
// v3.0.1.15 (2007/11/19) + U massexam dodana provjera da li student slusa predmet; ispravljen bug gdje su bodovi upisani drugom studentu sa istim imenom i prezimenom (ostaje problem sa dva studenta sa istim imenom i prezimenom koji slusaju isti predmet!?); zabrana unosa ispita sa greskama; massinput: popravljen bug kod prebacivanja studenta u drugu grupu
// v3.0.1.16 (2007/12/06) + Popravljeno otvaranje popup-a u IE6
// v3.0.1.17 (2007/12/10) + Zabraniti registrovanje vise ispita istog tipa na isti datum; popravljen bug u SQLu zbog kojeg nije prepoznato da student nije upisan na predmet
// v3.0.1.18 (2007/12/13) + Sitna ispravka u gore spomenutoj zabrani; bezimene grupe
// v3.0.1.19 (2007/12/15) + Umjesto zabrane, sada dodajemo rezultate na isti ispit!; samo 0 se priznaje kao nula bodova
// v3.0.1.20 (2008/01/19) + Dodan link na skracenu verziju izvjestaja "predmet_full"; masovni unos zadaca
// v3.0.1.21 (2008/01/28) + Dodano trimovanje imena i prezimena u massocjena i masszadaca (do sada samo u massexam)


function admin_predmet() {

global $userid;

global $_lv_; // We use form generators


# Vrijednosti

$predmet=intval($_GET['predmet']);
if ($predmet==0) $predmet=intval($_POST['predmet']);
if ($predmet==0) { niceerror("Nije izabran predmet."); return; }

$q1 = myquery("select p.naziv from predmet as p, ponudakursa as pk where pk.id=$predmet and pk.predmet=p.id");
$predmet_naziv = mysql_result($q1,0,0);

$tab=$_GET['tab'];
if ($tab=="") $tab=$_POST['tab'];
if ($tab=="") $tab="Opcije";

logthis("Admin Predmet $predmet - tab $tab");



// Da li korisnik ima pravo ući u modul?

$q501 = myquery("select siteadmin from nastavnik where id=$userid");
if (mysql_num_rows($q501)<1) {
	niceerror("Nemate pravo ulaska u ovu grupu!");
	return;
}
if (mysql_result($q501,0,0) < 1) {
	$q502 = myquery("select np.admin from nastavnik_predmet as np where np.nastavnik=$userid and np.predmet=$predmet");
	if (mysql_num_rows($q502)<1 || mysql_result($q502,0,0)<1) {
		niceerror("Nemate pravo ulaska u ovu grupu!");
		return;
	} 
}


###############
# Akcije
###############


# Dodaj grupu

if ($_POST['akcija'] == "nova_grupa") {
	$ime = my_escape($_POST['ime']);
	$q2 = myquery("insert into labgrupa set naziv='$ime', predmet=$predmet");
	logthis("Dodana nova labgrupa '$ime'");
}


# Obrisi grupu

if ($_GET['akcija'] == "obrisi_grupu") {
	$grupaid = intval($_GET['grupaid']);
	$q10 = myquery("delete from labgrupa where id=$grupaid");
	$q11 = myquery("delete from student_labgrupa where labgrupa=$grupaid");
	// Dodati brisanje svih podataka
	logthis("Obrisana labgrupa $grupaid");
}


# Promjena imena grupe

if ($_POST['akcija'] == "preimenuj_grupu") {
	$grupaid = intval($_POST['grupaid']);
	$ime = my_escape($_POST['ime']);
	$q10 = myquery("update labgrupa set naziv='$ime' where id=$grupaid");
	// Grupa treba ostati otvorena:
	$_GET['akcija']="studenti_grupa";
	$_GET['grupaid']=$grupaid;
	logthis("Preimenovana labgrupa $grupaid u '$ime'");
}


# Kopiraj grupe

if ($_POST['akcija'] == "kopiraj_grupe") {
	$kopiraj = intval($_POST['kopiraj']);
	if ($kopiraj == $predmet) {
		niceerror("Ne možete kopirati grupe sa istog predmeta.");
		return;
	}
	$q20 = myquery("select id,naziv from labgrupa where predmet=$kopiraj");
	if (mysql_num_rows($q20) == 0) 
		niceerror("Nisu definisane grupe za ovaj predmet.");
	while ($r20 = mysql_fetch_row($q20)) {
		$q21 = myquery("select id from labgrupa where predmet=$predmet and naziv='$r20[1]'");
		if (mysql_num_rows($q21) == 0) {
			$q22 = myquery("insert into labgrupa set naziv='$r20[1]', predmet=$predmet");
			$q21 = myquery("select id from labgrupa where predmet=$predmet and naziv='$r20[1]'");
		}
		$novagrupa = mysql_result($q21,0,0);
		$q23 = myquery("select student from student_labgrupa as sl where labgrupa=$r20[0]");
		while ($r23 = mysql_fetch_row($q23)) {
			$q24 = myquery("select sl.labgrupa from student_labgrupa as sl, labgrupa as l where sl.student=$r23[0] and sl.labgrupa=l.id and l.predmet=$predmet");
			if (mysql_num_rows($q24) > 0) {
				$staragrupa=mysql_result($q24,0,0);
				$q25 = myquery("update student_labgrupa set labgrupa=$novagrupa where student=$r23[0] and labgrupa=$staragrupa");
				print "Prebacujem studenta $r23[0] iz grupe $staragrupa u grupu $novagrupa<br/>";
			} else {
				$q25 = myquery("insert into student_labgrupa set labgrupa=$novagrupa, student=$r23[0]");
				print "Upisujem studenta $r23[0] u grupu $novagrupa<br/>";
			}
		}
	}

	logthis("Prekopirane labgrupe sa predmeta $kopiraj u $predmet");
}



# Upis u prvu grupu svih koji slušaju tekući semestar/odsjek

if ($_REQUEST['akcija'] == "svisasemestra") {
//	$f = $_POST['fakatradi'];
	// IMHO fakatradi nije potreban jer je operacija foolproof

	$q50 = myquery("select id from labgrupa where predmet=$predmet order by id limit 1");
	if (mysql_num_rows($q50) < 1) {
		$q51 = myquery("insert into labgrupa set naziv='Grupa 1', predmet=$predmet");
		$q50 = myquery("select id from labgrupa where predmet=$predmet order by id limit 1");
	}
	$labgrupa = mysql_result($q50,0,0);

	$q52 = myquery("select ss.student from student_studij as ss, ponudakursa as pk where pk.id=$predmet and pk.studij=ss.studij and pk.semestar=ss.semestar and pk.akademska_godina=ss.akademska_godina");

	while ($r52 = mysql_fetch_row($q52)) {
		$q53 = myquery("select count(*) from student_labgrupa as sl, labgrupa as l where sl.student=$r52[0] and sl.labgrupa=l.id and l.predmet=$predmet");
		if (mysql_result($q53,0,0)==0) {
			// Ne vrsimo premjestanje, nego samo upis ako nije vec upisan
			$q54 = myquery("insert into student_labgrupa set student=$r52[0], labgrupa=$labgrupa");
		}
	}
}



# Masovni unos studenata u grupe

if ($_POST['akcija'] == "massinput") {
	$redovi = explode("\n",$_POST['massinput']);
	$tempid=1;

	$f = $_POST['fakatradi'];
	if ($f != 1) {
		print "Akcije koje će biti urađene:<br/><br/>\n";
		print genform("POST");
		print '<input type="hidden" name="fakatradi" value="1">';
	}
	$greska=0;

	foreach ($redovi as $red) {
		$red = rtrim($red);
		$red = my_escape($red);	
		if (strlen($red)>1) {
			# Parsiranje formata
			$format = $_POST['format'];
/*			if ($format == "A") {
				list($prezime,$ime,$grupa,$email,$brindexa) = explode("\t",$red,5);
			} else if ($format == "B") {
				list($imepr,$grupa,$email,$brindexa) = explode("\t",$red,4);
				list($prezime,$ime) = explode(" ",$imepr,2);
			} else if ($format == "C") {
				list($imepr,$grupa,$brindexa) = explode("\t",$red,3);
				list($prezime,$ime) = explode(" ",$imepr,2);
				$email = "";
			} else if ($format == "D") {
				list($imepr,$brindexa) = explode("\t",$red,2);
				list($prezime,$ime) = explode(" ",$imepr,2);
				$email = "";
			}*/
			if ($format == "E") {
				list($prezime,$ime,$grupa) = explode("\t",$red,5);
			} else if ($format == "F") {
				list($imepr,$grupa) = explode("\t",$red,4);
				list($prezime,$ime) = explode(" ",$imepr,2);
			} else if ($format == "G") {
				list($imepr,$grupa) = explode("\t",$red,4);
				list($ime,$prezime) = explode(" ",$imepr,2);
			} else if ($format == "H") {
				list($prezime,$ime) = explode("\t",$red,5);
			}
			else {
				niceerror("Nije izabran format!");
				return;
			}
			$email = "";
			$brindexa = "";

			# Da li student već postoji?
			$q30 = myquery("select id from student where ime='$ime' and prezime='$prezime'");
			$stara_grupa = -1;
			if (mysql_num_rows($q30)>0) {
				$student = mysql_result($q30,0,0);
				$q30a = myquery("select l.id,l.naziv from student_labgrupa as sl, labgrupa as l where sl.student=$student and sl.labgrupa=l.id and l.predmet=$predmet");
				if (mysql_num_rows($q30a)>0) {
					$stara_grupa = mysql_result($q30a,0,0);
					$lgnaziv = mysql_result($q30a,0,1);
					if ($f != 1) {
						print "Prebacivanje studenta '$prezime $ime' iz grupe '$lgnaziv' u grupu";
					} else {
						$q30b = myquery("delete from student_labgrupa where student=$student and labgrupa=$stara_grupa");
					}
				} else {
					if ($f != 1) {
						print "Prijava studenta '$prezime $ime' u predmet '$predmet_naziv' grupa";
					}
				}
			} else {
				if ($f != 1) {
					print " -- GREŠKA! Nepoznat student '$prezime $ime' ($brindexa) - da li ste koristili ispravan format (prezime-ime vs. ime-prezime)?";
					$greska=1;
				} else {
					$q31 = myquery("insert into student set ime='$ime', prezime='$prezime', email='$email', brindexa='$brindexa'");
					$q32 = myquery("select id from student where ime='$ime' and prezime='$prezime'");
					$student = mysql_result($q32,0,0);
				}
			}

			# Izbor grupe
			if ($format == "D" || $format == "H") {
				# Format D - grupa nije navedena, koristi prvu
				$q33 = myquery("select id,naziv from labgrupa where predmet=$predmet order by id limit 1");
			} else {
				$q33 = myquery("select id,naziv from labgrupa where naziv='$grupa' and predmet=$predmet");
			}
			$nova_grupa = mysql_result($q33,0,0);

			# Dodaj studenta u grupu ili ispisi, ovisno o $f
			if (mysql_num_rows($q33)==0) {
				if ($f != 1) print " --- Nepoznata grupa!!";
				$greska = 1;
			} else {
				if ($nova_grupa == $stara_grupa) {
					if ($f != 1) print " ISTU GRUPU! Ništa neće biti urađeno.";
				} else {
					if ($f != 1)
						print " '".mysql_result($q33,0,1)."'";
					else if ($greska==0) {
						if ($stara_grupa>0) {
							$q33a = myquery("select id from cas where labgrupa=$stara_grupa");
							while ($r33a = mysql_fetch_row($q33a)) {
								$q33b = myquery("delete from prisustvo where student=$student and cas=$r33a[0]");
							}
						}
						$q34 = myquery("insert into student_labgrupa set student=$student, labgrupa=$nova_grupa");
					}
				}
			}
			if ($f != 1) print "<br/>\n";
		}
	}
	if ($f != 1) {
		print '<input type="button" value=" Nazad " onClick="location.href=\'qwerty.php?sta=predmet&predmet='.$predmet.'&tab=Grupe\'"> ';
		if ($greska==0) print '<input type="submit" value=" Potvrda ">';
		print "</form>";
		return;
	} else {
		logthis("Masovno upisani studenti na predmet $predmet");
	}
}



# Masovni unos rezultata ispita

if ($_POST['akcija'] == "massexam") {
	$redovi = explode("\n",$_POST['massexam']);
	$tempid=1;
	$greska=0;

	$f = $_POST['fakatradi'];
	if ($f != 1) {
		print "Akcije koje će biti urađene:<br/><br/>\n";
		print genform("POST");
		print '<input type="hidden" name="fakatradi" value="1">';
	} 


	// Registrovati ispit u bazi

	$naziv = my_escape($_POST['naziv']);
	$dan = intval($_POST['day']);
	$mjesec = intval($_POST['month']);
	$godina = intval($_POST['year']);
	$mdat = mktime(0,0,0,$mjesec,$dan,$godina);

	$tipispita = intval($_POST['tipispita']);

	// Da li je ispit vec registrovan?
	$q39 = myquery("select id from ispit where predmet=$predmet and datum=FROM_UNIXTIME('$mdat') and tipispita=$tipispita");
	if (mysql_num_rows($q39)>0) {
		$ispit = mysql_result($q39,0,0);
		if ($f != 1) {
			print "Dodati rezultate na postojeći ispit (ID: $ispit):<br/>";
		}
	} else if ($f == 1) {
		$q40 = myquery("insert into ispit set naziv='$naziv', predmet=$predmet, datum=FROM_UNIXTIME('$mdat'), tipispita=$tipispita");
		$q41 = myquery("select id from ispit where naziv='$naziv' and predmet=$predmet and datum=FROM_UNIXTIME('$mdat') and tipispita=$tipispita");

		if (mysql_num_rows($q41)<1) {
			niceerror("Unos ispita nije uspio.");
			return;
		} 
		$ispit = mysql_result($q41,0,0);
	}


	// Obrada rezultata

	$prosli_idovi = array();

	foreach ($redovi as $red) {
		$red = rtrim($red);
		$red = my_escape($red);
		if (strlen($red)>1) {
			# Parsiranje formata
			$format = $_POST['format'];
			if ($format == "A") {
				$red = trim($red);
				list($imepr,$bodova) = explode("\t",$red,2);
				list($prezime,$ime) = explode(" ",$imepr,2);
				$prezime=trim($prezime); 
				$ime=trim($ime);
			} else if ($format == "B") {
				$red = trim($red);
				list($prezime,$ime,$bodova) = explode("\t",$red,3);
				$prezime=trim($prezime); 
				$ime=trim($ime);
			} else if ($format == "C") {
				$red = trim($red);
				list($imepr,$bodova) = explode("\t",$red,2);
				list($ime,$prezime) = explode(" ",$imepr,2);
				$prezime=trim($prezime); 
				$ime=trim($ime);
			}
			else {
				niceerror("Nije izabran format!");
				return;
			}

			// pretvori $bodova u float uz obradu decimalnog zareza
			$fbodova = floatval(str_replace(",",".",$bodova));
			// samo 0 priznajemo za nula bodova, inace student nije izasao na ispit
			if ($fbodova==0 && strpos($bodova,"0")===FALSE) {
				if ($f != 1)
					print "Student '$prezime $ime' - nije izašao na ispit (nije unesen broj bodova $bodova)<br/>";
				continue;
			}
			$bodova = $fbodova;

			# Da li student postoji?
			$q42 = myquery("select * from student as s, student_labgrupa as sl, labgrupa as l where s.ime like '$ime' and s.prezime like '$prezime' and sl.student=s.id and sl.labgrupa=l.id and l.predmet=$predmet");
			if (mysql_num_rows($q42)>0) {
				$student = mysql_result($q42,0,0);

				# Da li se isti student ponavlja dvaput?
				if (array_search($student, $prosli_idovi)) {
					if ($f != 1) {
						print "-- GREŠKA! Student '$prezime' '$ime' se ponavlja! (bodova: $bodova)<br/>";
						$greska=1;
					}
				} else {
					if ($f == 1) {
						$q43 = myquery("insert into ispitocjene set ispit=$ispit, student=$student, ocjena=$bodova");
						$greska=1;
					} else {
						print "Student '$prezime $ime' (ID: $student) - bodova: $bodova<br/>";
					}
				}
			} else {
				$q42a = myquery("select count(*) from student where ime like '$ime' and prezime like '$prezime'");
				if (mysql_result($q42a,0,0) >= 1) {
					if ($f != 1) {
						print "-- GREŠKA! Student '$prezime' '$ime' nije upisan na ovaj predmet.<br/>";
						$greska=1;
					}
				} else {
					if ($f != 1) {
						print "-- GREŠKA! Nepoznat student '$prezime' '$ime'<br/>";
						$greska=1;
					}
				}
			}
		}
	}
	if ($f != 1) {
		print '<input type="button" value=" Nazad " onClick="location.href=\'qwerty.php?sta=predmet&predmet='.$predmet.'&tab=Ispiti\'">';
		if ($greska==0) print ' <input type="submit" value=" Potvrda">';
		print "</form>";
		return;
	} else {
		logthis("Masovni rezultati ispita za predmet $predmet");
	}
}






# Masovni unos konačnih ocjena

if ($_POST['akcija'] == "massocjena") {
	$redovi = explode("\n",$_POST['massocjena']);
	$tempid=1;
	$greska=0;

	$f = $_POST['fakatradi'];
	if ($f != 1) {
		print "Akcije koje će biti urađene:<br/><br/>\n";
		print genform("POST");
		print '<input type="hidden" name="fakatradi" value="1">';
	} else {

	}

	$prosli_idovi = array();

	foreach ($redovi as $red) {
		$red = rtrim($red);
		$red = my_escape($red);
		if (strlen($red)>1) {
			# Parsiranje formata
			$format = $_POST['format'];
			if ($format == "A") {
				list($imepr,$ocjena) = explode("\t",$red,2);
				list($prezime,$ime) = explode(" ",$imepr,2);
				$prezime=trim($prezime); 
				$ime=trim($ime);
			} else if ($format == "B") {
				list($imepr,$ocjena) = explode("\t",$red,2);
				list($ime,$prezime) = explode(" ",$imepr,2);
				$prezime=trim($prezime); 
				$ime=trim($ime);
			} else if ($format == "C") {
				list($prezime,$ime,$ocjena) = explode("\t",$red,3);
				$prezime=trim($prezime); 
				$ime=trim($ime);
			}
			# pretvori $ocjenu u int
			if (intval($ocjena)==0 && strpos($ocjena,"0")===FALSE) {
				if ($f != 1)
					print "Student '$prezime $ime' - nije ocijenjen (nije unesena ocjena $ocjena)<br/>";
				continue;
			}
			$ocjena = intval($ocjena);

			# Da li student postoji?
			$q42 = myquery("select * from student as s, student_labgrupa as sl, labgrupa as l where s.ime like '$ime' and s.prezime like '$prezime' and sl.student=s.id and sl.labgrupa=l.id and l.predmet=$predmet");
			if (mysql_num_rows($q42)>0) {
				$student = mysql_result($q42,0,0);

				# Da li se isti student ponavlja dvaput?
				if (array_search($student, $prosli_idovi)) {
					if ($f != 1) {
						print "-- GREŠKA! Student '$prezime $ime' se ponavlja! (ocjena: $ocjena)<br/>";
						$greska=1;
					}
				} else {
					if ($f != 1) {
						print "Student '$prezime $ime' (ID: $student) - ocjena: $ocjena<br/>";
					} else {
						$q43 = myquery("insert into konacna_ocjena set student=$student, predmet=$predmet, ocjena=$ocjena");
					}
				}
			} else {
				$q42a = myquery("select count(*) from student where ime like '$ime' and prezime like '$prezime'");
				if (mysql_result($q42a,0,0) >= 1) {
					if ($f != 1) {
						print "-- GREŠKA! Student '$prezime' '$ime' nije upisan na ovaj predmet.<br/>";
						$greska=1;
					}
				} else {
					if ($f != 1) {
						print "-- GREŠKA! Nepoznat student '$prezime' '$ime'<br/>";
						$greska=1;
					}
				}
			}
		}
	}
	if ($f != 1) {
		print '<input type="button" value=" Nazad " onClick="location.href=\'qwerty.php?sta=predmet&predmet='.$predmet.'&tab=Ocjena\'"> ';
		if ($greska==0) print ' <input type="submit" value=" Potvrda">';
		print "</form>";
		return;
	} else {
		logthis("Masovno upisane ocjene na predmet $predmet");
	}
}









# Masovni unos konačnih ocjena

if ($_POST['akcija'] == "masszadaca") {
	$redovi = explode("\n",$_REQUEST['masszadaca']);
	$tempid=1;
	$zadaca = intval($_REQUEST['_lv_column_zadaca']);
	$zadatak = intval($_REQUEST['zadatak']);
	$q44 = myquery("select naziv,zadataka,bodova from zadaca where id=$zadaca");
	if (mysql_result($q44,0,1)<$zadatak) {
		niceerror("Zadaća \"".mysql_result($q44,0,0)."\" nema $zadatak zadataka.");
		return;
	}
	$maxbodova=mysql_result($q44,0,2);

	$f = $_POST['fakatradi'];
	if ($f != 1) {
		print "Akcije koje će biti urađene:<br/><br/>\n";
		print genform("POST");
		print '<input type="hidden" name="fakatradi" value="1">';
		print '<input type="hidden" name="_lv_column_zadaca" value="'.$zadaca.'">';
		print '<input type="hidden" name="zadatak" value="'.$zadatak.'">';
	} else {

	}

	$prosli_idovi = array();

	foreach ($redovi as $red) {
		$red = rtrim($red);
		$red = my_escape($red);
		if (strlen($red)>1) {
			# Parsiranje formata
			$format = $_POST['format'];
			if ($format == "A") {
				list($imepr,$bodova) = explode("\t",$red,2);
				list($prezime,$ime) = explode(" ",$imepr,2);
				$prezime=trim($prezime); 
				$ime=trim($ime);
			} else if ($format == "B") {
				list($imepr,$bodova) = explode("\t",$red,2);
				list($ime,$prezime) = explode(" ",$imepr,2);
				$prezime=trim($prezime); 
				$ime=trim($ime);
			} else if ($format == "C") {
				list($prezime,$ime,$bodova) = explode("\t",$red,3);
				$prezime=trim($prezime); 
				$ime=trim($ime);
			}
			// pretvori $bodova u float uz obradu decimalnog zareza
			$fbodova = floatval(str_replace(",",".",$bodova));
			// samo 0 priznajemo za nula bodova, inace student nije izasao na ispit
			if ($fbodova==0 && strpos($bodova,"0")===FALSE) {
				if ($f != 1)
					print "Student '$prezime $ime' - nije uradio zadaću (nije unesen broj bodova $bodova)<br/>";
				continue;
			}
			$bodova = $fbodova;

			if ($bodova>$maxbodova) {
				if ($f != 1) {
					print "-- GREŠKA! Student '$prezime $ime' uneseno je $bodova što je više od maksimalnih $maxbodova<br/>";
					$greska=1;
				}
			}

			# Da li student postoji?
			$q42 = myquery("select * from student as s, student_labgrupa as sl, labgrupa as l where s.ime like '$ime' and s.prezime like '$prezime' and sl.student=s.id and sl.labgrupa=l.id and l.predmet=$predmet");
			if (mysql_num_rows($q42)>0) {
				$student = mysql_result($q42,0,0);

				# Da li se isti student ponavlja dvaput?
				if (array_search($student, $prosli_idovi)) {
					if ($f != 1) {
						print "-- GREŠKA! Student '$prezime $ime' se ponavlja! (bodova $bodova)<br/>";
						$greska=1;
					}
				} else {
					if ($f != 1) {
						print "Student '$prezime $ime' (ID: $student) - bodova: $bodova<br/>";
					} else {
						$q46 = myquery("insert into zadatak set zadaca=$zadaca, redni_broj=$zadatak, student=$student, status=5, bodova=$bodova, vrijeme=NOW()");
					}
				}
			} else {
				$q42a = myquery("select count(*) from student where ime like '$ime' and prezime like '$prezime'");
				if (mysql_result($q42a,0,0) >= 1) {
					if ($f != 1) {
						print "-- GREŠKA! Student '$prezime' '$ime' nije upisan na ovaj predmet.<br/>";
						$greska=1;
					}
				} else {
					if ($f != 1) {
						print "-- GREŠKA! Nepoznat student '$prezime' '$ime'<br/>";
						$greska=1;
					}
				}
			}
		}
	}
	if ($f != 1) {
		print '<input type="button" value=" Nazad " onClick="location.href=\'qwerty.php?sta=predmet&predmet='.$predmet.'&tab=Zadaće\'"> ';
		if ($greska==0) print ' <input type="submit" value=" Potvrda">';
		print "</form>";
		return;
	} else {
		logthis("Masovno upisane zadaće na predmet $predmet, zadaca $zadaca, zadatak $zadatak");
	}
}


# Dodavanje zadataka u zadaću

/*if ($_GET['akcija']=="dodaj_zadatke") {
	$brojzad = 0;

	// _lv_nav_id bi trebao biti ID zadaće
	$zadaca = intval($_GET['zadaca']);
	$q50 = myquery("select zadataka from zadaca where id=$zadaca");
	if (mysql_num_rows($q50)>0) $brojzad = mysql_result($q50,0,0);
	
	$q51 = myquery("select sl.student from student_labgrupa as sl, labgrupa as l where l.predmet=$predmet and l.id=sl.labgrupa");
	while ($r51 = mysql_fetch_row($q51)) {
		for ($i=1; $i<=$brojzad; $i++) {
			$q52 = myquery("select id from zadatak where zadaca=$zadaca and redni_broj=$i and student=$r51[0] limit 1");
			if (mysql_num_rows($q52)==0) {
				$q53 = myquery("insert into zadatak set zadaca=$zadaca, redni_broj=$i, student=$r51[0], status=1, bodova=0, vrijeme=NOW()");
			}
		}
	}
	print "<p><b>Operacija izvršena:</b> Svim studentima su generisani zadaci iz izabrane zadaće sa statusom &quot;Novi zadatak&quot;.</p>\n";
	$_REQUEST['akcija']="";
}*/

###############
# Ispis tabova
###############


function printtab($ime,$predmet,$tab) {
	if ($ime==$tab) 
		print '<td bgcolor="#DDDDDD" width="50">'.$ime.'</td>'."\n";
	else
		print '<td bgcolor="#BBBBBB" width="50"><a href="qwerty.php?sta=predmet&predmet='.$predmet.'&tab='.$ime.'">'.$ime.'</a></td>'."\n";
}

?>
<script language="JavaScript">
function upozorenje(url) {
	var a = confirm("Svi studenti iz ove grupe će biti ispisani sa predmeta.");
	if (a)
		window.location=url;
}
</script>

<p><h3><?=$predmet_naziv?></h3></p>

<table border="0" cellspacing="1" cellpadding="5" width="650">
<tr>
<td width="50">&nbsp;</td>
<? 
printtab("Opcije",$predmet,$tab); 
printtab("Izvještaji",$predmet,$tab); 
printtab("Grupe",$predmet,$tab); 
printtab("Ispiti",$predmet,$tab); 
printtab("Zadaće",$predmet,$tab); 
printtab("Kvizovi",$predmet,$tab); 
printtab("Ocjena",$predmet,$tab); 
?>
<td bgcolor="#BBBBBB" width="50"><a href="qwerty.php">Nazad</a></td>
<td width="200">&nbsp;</td>
</tr>
<tr>
<td width="50">&nbsp;</td>
<td colspan="9" bgcolor="#DDDDDD" width="600">
<?



# Opšta konfiguracija

// Ukinuti?
if ($tab == "Opcije") {
	$_lv_["hidden:predmet"] = 1;
	$_lv_["hidden:studij"] = 1;
	$_lv_["hidden:semestar"] = 1;
	$_lv_["hidden:obavezan"] = 1;
	$_lv_["hidden:akademska_godina"] = 1;
	$_lv_["label:aktivan"] = "Predmet je aktivan (vidljiv studentima)";
	$_lv_["label:motd"] = "Obavještenja za studente (na vrhu Status stranice)";
	$_lv_["where:id"] = "$predmet";
	$_lv_["forceedit"]=1;

	print db_form("ponudakursa");
}


if ($tab == "Izvještaji") {

	// Izvjestaj "GRUPE"
//	print '<p><hr/></p>';
	print '<p><a href="qwerty.php?sta=izvjestaj&tip=grupedouble&predmet='.$predmet.'"><img src="images/kontact_journal.png" border="0" align="center"> 1. Spisak studenata po grupama</a></p>';

	// Izvjestaj "PREDMET_FULL"
	print '<p><a href="qwerty.php?sta=izvjestaj&tip=predmet_full&predmet='.$predmet.'"><img src="images/kontact_journal.png" border="0" align="center"> 2. Pregled grupa, prisustva, bodova</a><br/>';
	print '<a href="qwerty.php?sta=izvjestaj&tip=predmet_full&predmet='.$predmet.'&skrati=1">(skraćena verzija)</a></p>';

	// Izvjestaj "KOMENTARI"
	print '<p><a href="qwerty.php?sta=izvjestaj&tip=grupe&komentari=1&predmet='.$predmet.'"><img src="images/kontact_journal.png" border="0" align="center"> 3. Spisak studenata sa komentarima</a>';

}


# Konfiguracija grupa

if ($tab == "Grupe") {
	print "Spisak grupa:<br/>\n";
	$q100 = myquery("select id,naziv from labgrupa where predmet=$predmet order by id");

	# Spisak grupa
	print "<ul>\n";
	if (mysql_num_rows($q100) == 0)
		print "<li>Nema definisanih grupa</li>\n";
	while ($r100 = mysql_fetch_row($q100)) {
		$grupa = $r100[0];
		$naziv = $r100[1];
		if (!preg_match("/\w/",$naziv)) 
			print "<li>[Nema imena] - ";
		else
			print "<li>$naziv - ";

		$q101 = myquery("select count(*) from student_labgrupa where labgrupa=$grupa");
		$brstud = mysql_result($q101,0,0);
		print "(<a href=\"qwerty.php?sta=predmet&predmet=$predmet&tab=Grupe&akcija=studenti_grupa&grupaid=$grupa\">$brstud studenata</a>) - ";

		print "<a href=\"javascript:onclick=upozorenje('qwerty.php?sta=predmet&predmet=$predmet&tab=Grupe&akcija=obrisi_grupu&grupaid=$grupa')\">Obriši grupu</a>";

		print "</li>\n";
		if ($_GET['akcija']=="studenti_grupa" && $_GET['grupaid']==$grupa) {
			print "<ul>\n";
			$q102 = myquery("select student.id,student.prezime,student.ime from student_labgrupa,student where student_labgrupa.student=student.id and student_labgrupa.labgrupa=$grupa order by student.prezime");
			while ($r102 = mysql_fetch_row($q102)) {
				?><li><a href="#" onclick="javascript:window.open('qwerty.php?sta=student-izmjena&student=<?=$r102[0]?>&predmet=<?=$predmet?>','blah6','width=320,height=320');"><? print $r102[1]." ".$r102[2]."</a></li>\n";
			}
			print "</ul>";
			$zapamti_grupu=$naziv;
		}
	}
	print "</ul>\n";

	# Editovanje grupe
	if ($_GET['akcija']=="studenti_grupa") {
		$gg = intval($_GET['grupaid']);
		# Dodavanje grupe
		print "<p>\n";
		print genform("POST");
		print '<input type="hidden" name="akcija" value="preimenuj_grupu">'."\n";
		print '<input type="hidden" name="grupaid" value="'.$gg.'">'."\n";
		print 'Promijenite naziv grupe: <input type="text" name="ime" size="20" value="'.$zapamti_grupu.'"> <input type="submit" value="Izmijeni"></form></p>'."\n";
	}

	# Dodavanje grupe
	print "<p>\n";
	print genform("POST");
	print '<input type="hidden" name="akcija" value="nova_grupa">'."\n";
	print 'Dodaj grupu: <input type="text" name="ime" size="20"> <input type="submit" value="Dodaj"></form></p>'."\n";

	# Kopiranje grupa sa predmeta
	$q103 = myquery("select akademska_godina from ponudakursa where id=$predmet");
	$akgod = mysql_result($q103,0,0);
	print "<p>\n";
	print genform("POST");
	print '<input type="hidden" name="akcija" value="kopiraj_grupe">'."\n";
	print 'Prekopiraj grupe sa predmeta: '."\n";
	print '<select name="kopiraj">';
	$q103a = myquery("select pk.id, p.naziv from predmet as p, ponudakursa as pk where pk.predmet=p.id and pk.akademska_godina=$akgod order by p.naziv");
	while ($r103a = mysql_fetch_row($q103a)) {
		print "<option value=\"$r103a[0]\">$r103a[1]</a>\n";
	}
	print '</select><input type="submit" value="Dodaj">'."\n";
	print '</form></p>'."\n";

	# Upis svih studenata na semestru
	$q104 = myquery("select s.naziv, pk.semestar from ponudakursa as pk, studij as s where pk.id=$predmet and pk.studij=s.id");
	print '<p><hr/></p><p><a href="qwerty.php?sta=predmet&predmet='.$predmet.'&tab=Grupe&akcija=svisasemestra">Upiši u prvu grupu sve studente koji trenutno slušaju '.mysql_result($q104,0,0).', '.mysql_result($q104,0,1).'. semestar</a></p>';

	# Masovni unos
	print '<p><hr/></p><p><b>Masovni unos studenata</b><br/>'."\n";
	print genform("POST");
	print '<input type="hidden" name="fakatradi" value="0">'; // poništi fakatradi
	print '<input type="hidden" name="akcija" value="massinput">'."\n";
	print '<br/>Izaberite format podataka:<br/>'."\n";
	print '<input type="radio" name="format" value="E" CHECKED> Prezime[TAB]Ime[TAB]Grupa<br/>'."\n";
	print ' <input type="radio" name="format" value="F"> Prezime Ime[TAB]Grupa<br/>'."\n";
	print ' <input type="radio" name="format" value="G"> Ime Prezime[TAB]Grupa<br/>'."\n";
	print ' <input type="radio" name="format" value="H"> Prezime[TAB]Ime (svi će biti dodati u prvu grupu)<br/><br/>'."\n";
	print '<textarea name="massinput" cols="50" rows="10"></textarea><br/>'."\n";
	print '<input type="submit" value="  Dodaj  ">'."\n";
	print '</form></p>'."\n";

}



# Unos ispita

if ($tab == "Ispiti") {
	print "Uneseni ispiti:<br/>\n";
	$q110 = myquery("select i.id,i.naziv,UNIX_TIMESTAMP(i.datum),t.naziv from ispit as i, tipispita as t where i.predmet=$predmet and i.tipispita=t.id order by i.datum,i.tipispita");
	print "<ul>\n";
	if (mysql_num_rows($q110)<1)
		print "<li>Nije unesen nijedan ispit.</li>";
	while ($r110 = mysql_fetch_row($q110)) {
		print '<li><a href="qwerty.php?sta=izvjestaj&tip=ispit&predmet='.$predmet.'&ispit='.$r110[0].'">'.$r110[3].' ('.date("d. m. Y.",$r110[2]).')</a></li>'."\n";
	}
	print "</ul>\n";

	# Masovni unos rezultata ispita
	print '<p><hr/></p>'."\n";
	print '<p><b>Masovni unos rezultata ispita</b><br/>'."\n";
	print genform("POST");
	print '<input type="hidden" name="fakatradi" value="0">'; // poništi fakatradi
	print '<input type="hidden" name="akcija" value="massexam">'."\n";

//	print '<br/>Naziv ispita: <input type="text" name="naziv" size="20">&nbsp;'."\n";
	print '<br/>Tip ispita: <select name="tipispita">';
	$q111 = myquery("select id,naziv from tipispita order by id");
	while ($r111 = mysql_fetch_row($q111)) {
		print '<option value="'.$r111[0].'">'.$r111[1].'</option>'."\n";
	}
	print '</select><br/><br/>'."\n";
	print 'Datum: '.datectrl(date('d'),date('m'),date('Y'))."<br/><br/>\n";

	print 'Izaberite format podataka:<br/>'."\n";
	print '<input type="radio" name="format" value="A"> Prezime Ime[TAB]Ocjena<br/>'."\n";
	print '<input type="radio" name="format" value="B"> Prezime[TAB]Ime[TAB]Ocjena<br/>'."\n";
	print '<input type="radio" name="format" value="C"> Ime Prezime[TAB]Ocjena<br/>'."\n";
	print "<br/>\n";
	print '<textarea name="massexam" cols="50" rows="10"></textarea><br/>'."\n";
	print '<input type="submit" value="  Dodaj  ">'."\n";
	print '</form></p>'."\n";
}




# Unos i podešavanje zadaća

if ($tab == "Zadaće") {
	$_lv_["where:predmet"] = $predmet;

	# Prikaz unesenih zadaća
	print "Unesene zadaće:<br/>\n";
	print db_list("zadaca");

	$izabrana = intval($_REQUEST['_lv_nav_id']);
	if ($izabrana==0) {
		?><p><hr/></p>
		<p><b>Unos nove zadaće</b><br/>
		<?
	} else {
		?><p><hr/></p>
		<p><b>Izmjena zadaće</b></p>
		<?
	}

	$_lv_["label:programskijezik"] = "Programski jezik";
	$_lv_["label:zadataka"] = "Broj zadataka";
	$_lv_["label:bodova"] = "Max. broj bodova";
	$_lv_["label:attachment"] = "Slanje zadatka u formi attachmenta";
	$_lv_["label:rok"] = "Rok za slanje";
	print db_form("zadaca");

	?><p><hr/></p>
	<p><b>Masovni unos zadaća</b><br/>
	<?

	print genform("POST");
	?><input type="hidden" name="fakatradi" value="0">
	<input type="hidden" name="akcija" value="masszadaca">

	Izaberite zadaću: <?=db_dropdown("zadaca");?>
	Izaberite zadatak: <select name="zadatak"><?
	$q112 = myquery("select zadataka from zadaca where predmet=$predmet order by zadataka desc limit 1");
	for ($i=1; $i<=mysql_result($q112,0,0); $i++) {
		print "<option value=\"$i\">$i</option>\n";
	}
	?>
	</select><br/><br/>

	Izaberite format podataka:<br/>
	<input type="radio" name="format" value="A"> Prezime Ime[TAB]Bodova<br/>
	<input type="radio" name="format" value="B"> Ime Prezime[TAB]Bodova<br/>
	<input type="radio" name="format" value="C"> Prezime[TAB]Ime[TAB]Bodova<br/>
	<br/>
	<textarea name="masszadaca" cols="50" rows="10"></textarea><br/>
	<input type="submit" value="  Dodaj  ">
	</form></p><?
}




// Kvizovi!

if ($tab == "Kvizovi") {
	print "<ul><b>Nije još implementirano... Sačekajte sljedeću verziju :)</b></ul>\n";
}




// Konačna ocjena

if ($tab == "Ocjena") {
	# Pojedinačna ocjena
	


	# Masovni unos konačnih ocjena
//	print '<p><hr/></p>'."\n";
	print '<p><b>Masovni unos konačnih ocjena</b><br/>'."\n";
	print genform("POST");
	print '<input type="hidden" name="fakatradi" value="0">'; // poništi fakatradi
	print '<input type="hidden" name="akcija" value="massocjena">'."\n";
	print 'Izaberite format podataka:<br/>'."\n";
	print '<input type="radio" name="format" value="A" CHECKED> Prezime Ime[TAB]Ocjena<br/>'."\n";
	print '<input type="radio" name="format" value="B"> Ime Prezime[TAB]Ocjena<br/>'."\n";
	print '<input type="radio" name="format" value="C"> Prezime[TAB]Ime[TAB]Ocjena<br/>'."\n";
	print "<br/>\n";
	print '<textarea name="massocjena" cols="50" rows="10"></textarea><br/>'."\n";
	print '<input type="submit" value="  Dodaj  ">'."\n";
	print '</form></p>'."\n";
}




?>
</td>
</tr>
</table>
<?

}

?>