<?

// COMMON/AJAH - biblioteka za razmjenu podataka a la AJAX

// VAZNO: za svaku akciju je potrebno implementirati striktnu kontrolu prava pristupa,
// jer se to ne podrazumijeva

// v3.9.1.0 (2008/02/12) + Preimenovano iz admin_ajah.php, dodan error handling
// v3.9.1.1 (2008/03/15) + Popravljen bug u provjeri ogranicenja za prisustvo
// v3.9.1.2 (2008/03/25) + Dodana pretraga imena za slanje poruke
// v3.9.1.3 (2008/04/04) + Optimizovan ajah za prisustvo koristenjem update_komponenta_prisustvo() iz libmanip
// v3.9.1.4 (2008/04/09) + Popravljeno koristenje varijable $user_siteadmin
// v3.9.1.5 (2008/05/16) + Optimizovan update_komponente() tako da se moze zadati bilo koja komponenta, ukinuto update_komponente_prisustvo
// v3.9.1.6 (2008/06/10) + Dodana podrska za fiksne komponente
// v3.9.1.7 (2008/06/16) + Popravljena provjera za site_admin kod prisustva, postrozen uslov za brisanje/dodavanje ocjene na ispitu
// v3.9.1.7 (2008/06/22) + Dodan unos bodova sa prijemnog
// v3.9.1.7a (2008/07/01) + Dodan unos ocjena tokom srednje skole za prijemni
// v3.9.1.8 (2008/08/28) + Tabela osoba umjesto auth u akciji "pretraga" (kod pisanja poruke)
// v3.9.1.8a (2008/09/01) + Bio iskomentiran OK kod prisustva !?
// v3.9.1.9 (2008/09/17) + Prisustvo nije radilo sa casovima u grupi "Svi studenti"; konacna ocjena: kod poredjenja integera 0 i stringa mora se koristiti !==; popravljena poruka za konacnu ocjenu vecu od $max
// v3.9.1.10 (2008/10/14) + Popravljen upit u akciji "pretraga"
// v4.0.0.0 (2009/02/19) + Release
// v4.0.9.1 (2009/03/24) + Prebacena polja ects i tippredmeta iz tabele ponudakursa u tabelu predmet
// v4.0.9.2 (2009/03/25) + nastavnik_predmet preusmjeren sa tabele ponudakursa na tabelu predmet - FIXME: prekontrolisati upite, mozda je moguca optimizacija?
// v4.0.9.3 (2009/03/31) + Tabela ispit preusmjerena sa ponudakursa na tabelu predmet - FIXME: isto
// v4.0.9.4 (2009/03/31) + Tabela konacna_ocjena preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.5 (2009/04/20) + Typo u upitu za prava nastavnika u modulu izmjena_ispita; ekvivalentan upit za admine, i.predmet vise nije id ponudekursa nego predmeta
// v4.0.9.6 (2009/04/24) + Greska uvedena u v4.0.9.4 (r372), ako $q70 ne vrati nista kako cemo onda znati metapredmet?
// v4.0.9.7 (2009/04/29) + Preusmjeravam tabelu labgrupa sa tabele ponudakursa na tabelu predmet
// v4.0.9.8 (2009/05/05) + Prisustvo: Labgrupa 0 se ukida, kao i polja predmet i akademska godina iz tabele cas
// v4.0.9.9 (2009/05/18) + Nemamo vise ponudukursa kod izmjene fiksnih bodova i konacne ocjene, pa cemo tu koristiti predmet i akademsku godinu
// v4.0.9.10 (2009/06/19) + Restruktuiranje i ciscenje baze: uvedeni sifrarnici mjesto i srednja_skola, za unos se koristi combo box; tabela prijemni_termin omogucuje definisanje termina prijemnog ispita, sto omogucuje i prijemni ispit za drugi ciklus; pa su dodate i odgovarajuce akcije za kreiranje i izbor termina; licni podaci se sada unose direktno u tabelu osoba, dodaje se privilegija "prijemni" u tabelu privilegija; razdvojene tabele: uspjeh_u_srednjoj (koja se vezuje na osoba i srednja_skola) i prijemni_prijava (koja se vezuje na osoba i prijemni_termin); polja za studij su FK umjesto tekstualnog polja; dodano polje prijemni_termin u upis_kriterij; tabela prijemniocjene preimenovana u srednja_ocjene; ostalo: dodan logging; jmbg proglasen obaveznim; vezujem ocjene iz srednje skole za redni broj, posto se do sada redoslijed ocjena oslanjao na ponasanje baze; nova combobox kontrola
// v4.0.9.11 (2009/06/22) + Provjera prava pristupa kod fiksne komponente i konacne ocjene nije prebacena sa ponudekursa na predmet+ag
// v4.0.9.12 (2009/07/15) + Dodajem kod za upis na drugi ciklus
// v4.0.9.13 (2009/10/02) + Ljepsa poruka za sesiju koja je istekla; jos zastite za module studentske sluzbe


// Prebaciti u lib/manip?



function common_ajah() {

global $userid,$user_nastavnik,$user_siteadmin,$user_studentska;

require("lib/manip.php");


?>
<body onLoad="javascript:parent.ajah_stop()">
<?

switch ($_REQUEST['akcija']) {

case "prisustvo":
	
	if ($userid == 0) {
		zamgerlog("AJAH prisustvo - istekla sesija",3); // nivo 3 - greska
		zamgerlog2("prisustvo - istekla sesija"); // nivo 3 - greska
		print "Vasa sesija je istekla. Pritisnite dugme Refresh da se ponovo prijavite.";
		break;
	}

	if (!$user_nastavnik && !$user_siteadmin) {
		zamgerlog("AJAH prisustvo - korisnik nije nastavnik",3); // nivo 3 - greska
		zamgerlog2("prisustvo - korisnik nije nastavnik"); // nivo 3 - greska
		print "niste nastavnik"; break; 
	}

	$student=intval($_GET['student']);
	$cas=intval($_GET['cas']);
	$prisutan=intval($_GET['prisutan']);

	// Provjera parametra i odredjivanje predmeta i ag

	$q10 = myquery("select c.labgrupa, l.predmet, l.akademska_godina from cas as c, labgrupa as l where c.id=$cas and c.labgrupa=l.id");
	if (mysql_num_rows($q10)<1) {
		zamgerlog("AJAH prisustvo - nepostojeci cas $cas",3);
		zamgerlog2("prisustvo - nepostojeci cas", $cas);
		print "nepostojeci cas"; break;
	}
	$labgrupa = mysql_result($q10,0,0);
	$predmet = mysql_result($q10,0,1);
	$ag = mysql_result($q10,0,2);


	// Provjera prava pristupa
	if (!$user_siteadmin) {
		$q15 = myquery("select count(*) from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
		if (mysql_num_rows($q15)<1) {
			zamgerlog("AJAH prisustvo - korisnik nije nastavnik (cas c$cas)",3);
			zamgerlog2("nije saradnik na predmetu (prisustvo)", $cas);
			print "niste nastavnik A"; break;
		}

		// Provjeravamo ogranicenja
		$q20 = myquery("select o.labgrupa from ogranicenje as o, labgrupa as l where o.nastavnik=$userid and o.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag");
		if (mysql_num_rows($q20)>0) {
			$nasao=0;
			while ($r20 = mysql_fetch_row($q20)) {
				// Ako je labgrupa 0 nece ga nikada nac
				if ($r20[0] == $labgrupa) { $nasao=1; break; }
			}
			if ($nasao == 0) {
				zamgerlog("AJAH prisustvo - korisnik ima ogranicenje za grupu (cas c$cas)",3);
				zamgerlog2("prisustvo - ima ogranicenje za grupu", $cas);
				print "imate ograničenje na ovu grupu"; break;
			}
		}

		// ponudakursa
	}



	// Akcija

	if ($student>0 && $cas>0) {
		if ($prisutan == 3) { // Postavljanje u neutralno stanje
			$q0 = myquery("delete from prisustvo where student=$student and cas=$cas");
		} else {
			$prisutan--;
			$q1 = myquery("select prisutan from prisustvo where student=$student and cas=$cas");
			if (mysql_num_rows($q1)<1) 
				$q2 = myquery("insert into prisustvo set prisutan=$prisutan, student=$student, cas=$cas");
			else
				$q3 = myquery("update prisustvo set prisutan=$prisutan where student=$student and cas=$cas");
		}
	} else {
		zamgerlog("AJAH prisustvo - losa akcija, student: $student cas: $cas prisutan: $prisutan",3);
		zamgerlog2("prisustvo - losa akcija", $student, $cas, $prisutan);
		print "akcija je generalno loša"; 
		break;
	}

	// Ažuriranje komponenti
	// potrebna nam je ponudakursa za update_komponente
	$q3 = myquery("select pk.id from ponudakursa as pk, student_predmet as sp where sp.student=$student and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
	$ponudakursa = mysql_result($q3,0,0);

	$q4 = myquery("select k.id from tippredmeta_komponenta as tpk,komponenta as k, akademska_godina_predmet as agp where agp.predmet=$predmet and agp.tippredmeta=tpk.tippredmeta and agp.akademska_godina=$ag and tpk.komponenta=k.id and k.tipkomponente=3");
	while ($r4 = mysql_fetch_row($q4))
		update_komponente($student,$ponudakursa,$r4[0]);
	zamgerlog("AJAH prisustvo - student: u$student cas: c$cas prisutan: $prisutan",2); // nivo 2 - edit
	zamgerlog2("prisustvo azurirano", $student, $cas, $prisutan); // nivo 2 - edit

	print "OK";
	break;


case "izmjena_ispita":

	// TODO: treci tip vrijenosti, fiksna komponenta

	if ($userid == 0) {
		zamgerlog("AJAH ispit - istekla sesija",3); // nivo 3 - greska
		zamgerlog2("ispit - istekla sesija");
		print "Vasa sesija je istekla. Pritisnite dugme Refresh da se ponovo prijavite.";
		break;
	}

	if (!$user_nastavnik && !$user_studentska && !$user_siteadmin) {
		zamgerlog("AJAH ispit - korisnik nije nastavnik",3); // nivo 3 - greska
		zamgerlog2("ispit - korisnik nije nastavnik"); // nivo 3 - greska
		print "niste nastavnik"; break; 
	}

	// Provjera validnosti primljenih podataka
	$idpolja = $_REQUEST['idpolja'];
	$vrijednost = $_REQUEST['vrijednost'];

	$parametri = array();
	$parametri = explode("-",$idpolja);
	$ime = $parametri[0];
	if ($ime != "ispit" && $ime!="ko" && $ime!="fiksna" && $ime!="kodatum") {
		// ko = konacna ocjena
		zamgerlog("AJAH ispit - ne valja id polja ($idpolja)",3);
		zamgerlog2("ispit - ne valja id polja", $idpolja);
		print "ne valja ID polja $idpolja"; break;
	}

	if ($ime != "kodatum") {
		if (!preg_match("/\d/", $vrijednost)) {
			if ($vrijednost != "/") {
				zamgerlog("AJAH ispit - vrijednost $vrijednost nije ni broj ni /",3);
				zamgerlog2("ispit - vrijednost nije ni broj ni /",0,0,0,$vrijednost);
				print "Vrijednost $vrijednost nije ni broj ni /"; break;
			}
		} else {
			$vrijednost = floatval(str_replace(",",".",$vrijednost));
		}
	}

	// Provjera prava pristupa i dodatna validacija parametara
	if ($ime == "ispit") {
		$stud_id = intval($parametri[1]);
		$ispit = intval($parametri[2]);
		if ($user_siteadmin)
			$q40 = myquery("select 'nastavnik',pk.id,k.maxbodova,k.id,k.tipkomponente,k.opcija, pk.predmet from ispit as i, komponenta as k, ponudakursa as pk, student_predmet as sp where i.id=$ispit and i.komponenta=k.id and i.predmet=pk.predmet and i.akademska_godina=pk.akademska_godina and sp.predmet=pk.id and sp.student=$stud_id");
		else
			$q40 = myquery("select np.nivo_pristupa,pk.id,k.maxbodova,k.id,k.tipkomponente,k.opcija, pk.predmet from nastavnik_predmet as np, ispit as i, komponenta as k, ponudakursa as pk, student_predmet as sp where np.nastavnik=$userid and np.predmet=i.predmet and np.akademska_godina=i.akademska_godina and pk.predmet=i.predmet and pk.akademska_godina=i.akademska_godina and i.id=$ispit and i.komponenta=k.id and sp.predmet=pk.id and sp.student=$stud_id");
		if (mysql_num_rows($q40)<1) {
			zamgerlog("AJAH ispit - nepoznat ispit $ispit ili niste saradnik",3);
			zamgerlog2("ispit - nepoznat ispit ili nije saradnik",$ispit);
			print "nepoznat ispit $ispit ili niste saradnik na predmetu"; break;
		}
		if (mysql_result($q40,0,0) != "asistent") $padmin = 1;
		$ponudakursa = mysql_result($q40,0,1);
		$max = mysql_result($q40,0,2);
		// Potrebno za update komponenti:
		$komponenta = mysql_result($q40,0,3);
		$tipkomponente = mysql_result($q40,0,4);
		$kopcija = mysql_result($q40,0,5);
		$predmet = mysql_result($q40,0,6);

	} else if ($ime == "fiksna") {
		$stud_id = intval($parametri[1]);
		$predmet = intval($parametri[2]);
		$komponenta = intval($parametri[3]);
		$ag = intval($parametri[4]);

		// TODO: provjeriti da li komponenta postoji na predmetu
		$q40a = myquery("select maxbodova from komponenta where id=$komponenta and tipkomponente=5");
		if (mysql_num_rows($q40a)!=1) {
			zamgerlog("AJAH fiksna - nepoznata fiksna komponenta $komponenta",3);
			zamgerlog2("fiksna - nepoznata fiksna komponenta", $komponenta);
			print "nepoznata fiksna komponenta $komponenta"; break;
		}
		$max = mysql_result($q40a,0,0);

		if (!$user_siteadmin) {
			$q40b = myquery("select count(*) from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
			if (mysql_num_rows($q40b)<1) {
				zamgerlog("AJAH fiksna - nije na predmetu pp$predmet, ag$ag",3);
				zamgerlog2("nije saradnik na predmetu (fiksna)", $predmet, $ag);
				print "niste saradnik na predmetu"; break;
			}
		}
		$padmin=1; // Dozvoljavamo saradnicima da unose fiksne komponente

	} else if ($ime == "ko" || $ime == "kodatum") {
		// konacna ocjena
		$stud_id = intval($parametri[1]);
		if ($ime == "ko" && $vrijednost!="/") $vrijednost=intval($vrijednost); // zaokruzujemo
		$predmet=intval($parametri[2]);
		$ag = intval($parametri[3]);

		$max=10;
		if (!$user_siteadmin && !$user_studentska) {
			$q41 = myquery("select nivo_pristupa from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
			if (mysql_num_rows($q41)<1) {
				zamgerlog("AJAH ispit/ko - niste saradnik (ispit pp$predmet, ag$ag)",3);
				zamgerlog2("nije saradnik na predmetu (ispit/ko)", $predmet, $ag);
				print "niste saradnik na predmetu $predmet";
				break;
			}
			if (mysql_result($q41,0,0)=="nastavnik") $padmin = 1;
		}
	}
	if ($padmin==0 && !$user_siteadmin && !$user_studentska) {
		zamgerlog("AJAH ispit - pogresne privilegije (ispit i$ispit)",3);
		zamgerlog2("ispit - pogresne privilegije", $ispit);
		print "niste nastavnik na predmetu $predmet niti admin!"; break;
	}

	// Da li je student na predmetu?
	$q45 = myquery ("select count(*) from student_predmet as sp, ponudakursa as pk where sp.student=$stud_id and sp.predmet=pk.id and pk.predmet=$predmet");
	if (mysql_result($q45,0,0)<1) {
		zamgerlog("AJAH ispit - student u$stud_id ne slusa predmet pp$predmet (ispit i$ispit)",3);
		zamgerlog2("ispit - student ne slusa predmet", $stud_id, $ispit);
		print "student $stud_id ne sluša predmet $predmet"; break;
	}

	// Maksimalan i minimalan broj bodova
	if ($ime != "kodatum" && $vrijednost>$max) {
		zamgerlog("AJAH ispit - vrijednost $vrijednost > max $max",3);
		zamgerlog2("ispit - vrijednost > max", $stud_id, intval($ispit), 0, "$vrijednost > $max");
		if ($ime=="ko")
			print "stavili ste ocjenu veću od 10";
		else
			print "maksimalan broj bodova je $max, a unijeli ste $vrijednost";
		break;
	}
	if ($ime=="ko" && $vrijednost<6 && $vrijednost!=="/") {
		zamgerlog("AJAH ispit - konacna ocjena manja od 6 ($vrijednost)",3);
		zamgerlog2("ispit - konacna ocjena manja od 6", 0,0,0, $vrijednost);
		print "stavili ste ocjenu manju od 6";
		break;
	}

	if ($ime=="kodatum") { // Parsiranje datuma
		if (!preg_match("/(\d+).*?(\d+).*?(\d+)/", $vrijednost, $matches)) {
			zamgerlog("AJAH ispit - datum konacne ocjene nije u trazenom formatu ($vrijednost)", 3);
			zamgerlog2("ispit - datum konacne ocjene nije u trazenom formatu", 0,0,0, $vrijednost);
			print "los format datuma";
			break;
		}
		$dan=$matches[1]; $mjesec=$matches[2]; $godina=$matches[3];
		if ($godina<100)
			if ($godina<50) $godina+=2000; else $godina+=1900;
		if ($godina<1000)
			if ($godina<900) $godina+=2000; else $godina+=1000;
		if (!checkdate($mjesec,$dan,$godina)) {
			zamgerlog("AJAH ispit - datum konacne ocjene je nemoguc ($vrijednost)", 3);
			zamgerlog2("ispit - datum konacne ocjene je nemoguc", 0,0,0, $vrijednost);
			print "uneseni datum $dan. $mjesec. $godina je kalendarski nemoguc";
			break;
		}
		$novidatum = mktime(0, 0, 0, $mjesec, $dan, $godina);
		if ($novidatum === false) {
			zamgerlog("AJAH ispit - datum konacne ocjene je neispravan ($vrijednost)", 3);
			zamgerlog2("ispit - datum konacne ocjene je nemoguc", 0,0,0, $vrijednost);
			print "uneseni datum $dan. $mjesec. $godina nije ispravan";
			break;
		}
	}

	// Ažuriranje podataka u bazi
	if ($ime=="ispit") {
		$q50 = myquery("select ocjena from ispitocjene where ispit=$ispit and student=$stud_id");
		$c = mysql_num_rows($q50);
		if ($c==0 && $vrijednost!=="/") {
			$q60 = myquery("insert into ispitocjene set ispit=$ispit, student=$stud_id, ocjena=$vrijednost");
			zamgerlog("AJAH ispit - upisan novi rezultat $vrijednost (ispit i$ispit, student u$stud_id)",4); // nivo 4: audit
			zamgerlog2("upisan rezultat ispita", $stud_id, $ispit, 0, $vrijednost); // nivo 4: audit
		} else if ($c>0 && $vrijednost==="/") {
			$staraocjena = mysql_result($q50,0,0);
			$q60 = myquery("delete from ispitocjene where ispit=$ispit and student=$stud_id");
			zamgerlog("AJAH ispit - izbrisan rezultat $staraocjena (ispit i$ispit, student u$stud_id)",4); // nivo 4: audit
			zamgerlog2("izbrisan rezultat ispita", $stud_id, $ispit, 0, $staraocjena); // nivo 4: audit
		} else if ($c>0) {
			$staraocjena = mysql_result($q50,0,0);
			$q60 = myquery("update ispitocjene set ocjena=$vrijednost where ispit=$ispit and student=$stud_id");
			zamgerlog("AJAH ispit - izmjena rezultata $staraocjena u $vrijednost (ispit i$ispit, student u$stud_id)",4); // nivo 4: audit
			zamgerlog2("izmjenjen rezultat ispita", $stud_id, $ispit, 0, "$staraocjena -> $vrijednost"); // nivo 4: audit
		}

		update_komponente($stud_id,$ponudakursa,$komponenta);

	} else if ($ime == "fiksna") {
		// Odredjujemo ponudukursa zbog tabele komponentebodovi
		$q62 = myquery("select pk.id from student_predmet as sp, ponudakursa as pk where sp.student=$stud_id and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
		$ponudakursa = mysql_result($q62,0,0);

		$q63 = myquery("delete from komponentebodovi where student=$stud_id and predmet=$ponudakursa and komponenta=$komponenta");
		if ($vrijednost != "/") $q66 = myquery("insert into komponentebodovi set student=$stud_id, predmet=$ponudakursa, komponenta=$komponenta, bodovi=$vrijednost");
		zamgerlog("AJAH fiksna - upisani bodovi $vrijednost za fiksnu komponentu $komponenta (predmet pp$predmet, student u$stud_id)",4);
		zamgerlog2("izmjena bodova za fiksnu komponentu", intval($stud_id), intval($ponudakursa), intval($komponenta), $vrijednost);

	} else if ($ime == "ko") {
		// Konacna ocjena

		// Ne koristimo REPLACE i slicno zbog logginga
		$q70 = myquery("select ocjena from konacna_ocjena where predmet=$predmet and student=$stud_id");
		$c = mysql_num_rows($q70);
		if ($c==0 && $vrijednost!="/") {
			// Određivanje datuma za indeks
			$q105 = myquery("SELECT UNIX_TIMESTAMP(it.datumvrijeme) 
			FROM ispit as i, ispit_termin as it, student_ispit_termin as sit 
			WHERE sit.student=$stud_id and sit.ispit_termin=it.id and it.ispit=i.id and i.predmet=$predmet and i.akademska_godina=$ag
			ORDER BY i.datum DESC LIMIT 1");
			if (mysql_num_rows($q105) > 0) {
				$datum_u_indeksu = mysql_result($q105,0,0);
				if ($datum_u_indeksu > time())
					$datum_provjeren = 0;
				else
					$datum_provjeren = 1;
			} else {
				$datum_u_indeksu = time();
				$datum_provjeren = 0;
			}

			$q80 = myquery("insert into konacna_ocjena set predmet=$predmet, akademska_godina=$ag, student=$stud_id, ocjena=$vrijednost, datum=NOW(), datum_u_indeksu=FROM_UNIXTIME($datum_u_indeksu), datum_provjeren=$datum_provjeren");
			zamgerlog("AJAH ko - dodana ocjena $vrijednost (predmet pp$predmet, student u$stud_id)",4); // nivo 4: audit
			zamgerlog2("dodana ocjena", $stud_id, $predmet, $ag, $vrijednost);
		} else if ($c>0 && $vrijednost=="/") {
			$staraocjena = mysql_result($q70,0,0);
			$q80 = myquery("delete from konacna_ocjena where predmet=$predmet and student=$stud_id");
			zamgerlog("AJAH ko - obrisana ocjena $staraocjena (predmet pp$predmet, student u$stud_id)",4); // nivo 4: audit
			zamgerlog2("obrisana ocjena", $stud_id, $predmet, $ag, $staraocjena);
		} else if ($c>0) {
			$staraocjena = mysql_result($q70,0,0);
			$q80 = myquery("update konacna_ocjena set ocjena=$vrijednost, datum=NOW() where predmet=$predmet and student=$stud_id");
			zamgerlog("AJAH ko - izmjena ocjene $staraocjena u $vrijednost (predmet pp$predmet, student u$stud_id)",4); // nivo 4: audit
			zamgerlog2("izmjena ocjene", $stud_id, $predmet, $ag, "$staraocjena -> $vrijednost");
		}

	} else if ($ime == "kodatum") {
		// AJAH "kodatum" je uvijek promjena
		$q85 = myquery("select UNIX_TIMESTAMP(datum_u_indeksu), datum_provjeren from konacna_ocjena where predmet=$predmet and student=$stud_id");
		if (mysql_num_rows($q85) == 0) {
			print "ne moze se mijenjati datum dok se ne unese ocjena";
			break;
		}
		$staridatum = mysql_result($q85,0,0);
		$datum_provjeren = mysql_result($q85,0,1);

		if ($staridatum != $novidatum || $datum_provjeren == 0) {
			$q87 = myquery("update konacna_ocjena set datum_u_indeksu=FROM_UNIXTIME($novidatum), datum_provjeren=1 where predmet=$predmet and student=$stud_id");
			zamgerlog("AJAH kodatum - promijenjen datum u indeksu (predmet pp$predmet, student u$stud_id)", 4);
			zamgerlog2("promijenjen datum ocjene", $stud_id, $predmet, $ag, date("d.m.Y",$novidatum));
		}
	}


	print "OK";
	break;


case "pretraga":
	if ($userid == 0) {
		zamgerlog("AJAH pretraga - istekla sesija",3); // nivo 3 - greska
		zamgerlog2("pretraga - istekla sesija"); // nivo 3 - greska
		print "Vasa sesija je istekla. Pritisnite dugme Refresh da se ponovo prijavite.";
		break;
	}

	$ime = my_escape($_REQUEST['ime']);
	if (!preg_match("/\w/",$ime)) { print "OK"; return; }
	$ime = str_replace("(","",$ime);
	$ime = str_replace(")","",$ime);
	$imena = explode(" ",$ime);
	$upit = "";
	foreach($imena as $dio) {
		if ($upit != "") $upit .= " and ";
		$upit .= "(o.ime like '%$dio%' or o.prezime like '%$dio%' or a.login like '%$dio%' or o.brindexa like '%$dio%')";
	}
	$q10 = myquery("select a.login, o.ime, o.prezime from auth as a, osoba as o where a.id=o.id and $upit order by o.prezime, o.ime");
	$redova=0;
	while ($r10 = mysql_fetch_row($q10)) {
		if (strlen($r10[0])<2) continue;
		$primalac = "$r10[0] ($r10[1] $r10[2])";
		print "$primalac\n";
		$redova++;
		if ($redova>10) break;
	}
	if ($redova==0) {
		print "Nema rezultata\n";
	}
	print "OK";

	break;


// Unos bodova sa prijemnog
case "prijemni_unos":

	if ($userid == 0) {
		zamgerlog("AJAH prijemni - istekla sesija",3); // nivo 3 - greska
		zamgerlog2("prijemni - istekla sesija"); // nivo 3 - greska
		print "Vasa sesija je istekla. Pritisnite dugme Refresh da se ponovo prijavite.";
		break;
	}

	if (!$user_studentska && !$user_siteadmin) {
		zamgerlog("AJAH prijemni - korisnik nije studentska sluzba ",3); // nivo 3 - greska
		zamgerlog2("prijemni - korisnik nije studentska sluzba"); // nivo 3 - greska
		print "niste studentska sluzba"; break; 
	}

	$osoba = intval($_REQUEST['osoba']);
	$termin = intval($_REQUEST['termin']);
	$vrijednost = floatval(str_replace(",",".",$_REQUEST['vrijednost']));

	$q100 = myquery("select count(*) from prijemni_prijava where osoba=$osoba and prijemni_termin=$termin");
	if (mysql_result($q100,0,0)==0)  {
		print "Nepoznat id $id";
		break;
	}
	// Dodati provjeru rezultata prijemnog...
	if ($_REQUEST['vrijednost'] == "/")
		$q110 = myquery("update prijemni_prijava set rezultat=0, izasao=0 where osoba=$osoba and prijemni_termin=$termin");
	else
		$q110 = myquery("update prijemni_prijava set rezultat=$vrijednost, izasao=1 where osoba=$osoba and prijemni_termin=$termin");

	print "OK";

	zamgerlog("upisan rezultat na prijemnom za u$osoba, termin $termin ($vrijednost)",2);
	zamgerlog2("upisan rezultat na prijemnom", $osoba, $termin, 0, $vrijednost);

	break;


// Unos ocjena tokom srednje skole za prijemni
case "prijemni_ocjene":

	if ($userid == 0) {
		zamgerlog("AJAH prijemni - istekla sesija",3); // nivo 3 - greska
		zamgerlog2("prijemni - istekla sesija"); // nivo 3 - greska
		print "Vasa sesija je istekla. Pritisnite dugme Refresh da se ponovo prijavite.";
		break;
	}

	if (!$user_studentska && !$user_siteadmin) {
		zamgerlog("AJAH prijemni - korisnik nije studentska sluzba ",3); // nivo 3 - greska
		zamgerlog2("prijemni - korisnik nije studentska sluzba"); // nivo 3 - greska
		print "niste studentska sluzba"; break; 
	}

	$osoba = intval($_REQUEST['osoba']);

	$nova = intval($_REQUEST['nova']);
	$stara = intval($_REQUEST['stara']);
	$razred = intval($_REQUEST['razred']);
	$tipocjene = intval($_REQUEST['tipocjene']);
	$rednibroj = intval($_REQUEST['rednibroj']);

// Pretpostavljamo da je id osobe tačan
// Glupost :( ali šta se može kad se ocjene moraju unositi prije nego što se registruje osoba
/*	$q100 = myquery("select count(*) from osoba where id=$osoba");
	if (mysql_result($q100,0,0)==0)  {
		print "Nepoznat id $prijemni";
		break;
	}*/

	if ($_REQUEST['subakcija']!="obrisi" && $_REQUEST['subakcija']!="izmijeni" && $_REQUEST['subakcija']!="dodaj") {
		print "Nepoznata akcija: ".my_escape($_REQUEST['akcija']);
		break;
	}

	if ($_REQUEST['subakcija']=="obrisi" || $_REQUEST['subakcija']=="izmijeni")
		$q200 = myquery("delete from srednja_ocjene where osoba=$osoba and razred=$razred and ocjena=$stara and tipocjene=$tipocjene and redni_broj=$rednibroj limit 1");
	if ($_REQUEST['subakcija']=="dodaj" || $_REQUEST['subakcija']=="izmijeni")
		$q200 = myquery("insert into srednja_ocjene set osoba=$osoba, razred=$razred, ocjena=$nova, tipocjene=$tipocjene, redni_broj=$rednibroj");

	print "OK";

	break;



// Unos ocjena u prošlom ciklusu studija za prijemni
case "prosli_ciklus_ocjena":

	if ($userid == 0) {
		zamgerlog("AJAH prijemni - istekla sesija",3); // nivo 3 - greska
		zamgerlog2("prijemni - istekla sesija"); // nivo 3 - greska
		print "Vasa sesija je istekla. Pritisnite dugme Refresh da se ponovo prijavite.";
		break;
	}

	if (!$user_studentska && !$user_siteadmin) {
		zamgerlog("AJAH prijemni - korisnik nije studentska sluzba ",3); // nivo 3 - greska
		zamgerlog2("prijemni - korisnik nije studentska sluzba"); // nivo 3 - greska
		print "niste studentska sluzba"; break; 
	}

	$osoba = intval($_REQUEST['osoba']);
	$nova = intval($_REQUEST['nova']);
	$rednibroj = intval($_REQUEST['rednibroj']); // nece biti nula

	$q100 = myquery("select count(*) from osoba where id=$osoba");
	if (mysql_result($q100,0,0)==0)  {
		print "Nepoznata osoba $osoba";
		break;
	}

	if ($nova==0) {
		$q140 = myquery("delete from prosliciklus_ocjene where osoba=$osoba and redni_broj=$rednibroj");
	} else if ($nova<6 || $nova>10) {
		print "Ocjena nije u opsegu 6-10";
		break;
	} else {
	
		$q110 = myquery("select count(*) from prosliciklus_ocjene where osoba=$osoba and redni_broj=$rednibroj");
		if (mysql_result($q110,0,0)==0)
			$q120 = myquery("insert into prosliciklus_ocjene set osoba=$osoba, redni_broj=$rednibroj, ocjena=$nova");
		else
			$q130 = myquery("update prosliciklus_ocjene set ocjena=$nova where osoba=$osoba and redni_broj=$rednibroj");
	}

	print "OK";

	break;



// Unos ECTS bodova u prošlom ciklusu studija za prijemni
case "prosli_ciklus_ects": // 1500,5 / 157,5 = 9,52698413 / 6 = 1,58783069

	if ($userid == 0) {
		zamgerlog("AJAH prijemni - istekla sesija",3); // nivo 3 - greska
		zamgerlog2("prijemni - istekla sesija"); // nivo 3 - greska
		print "Vasa sesija je istekla. Pritisnite dugme Refresh da se ponovo prijavite.";
		break;
	}

	if (!$user_studentska && !$user_siteadmin) {
		zamgerlog("AJAH prijemni - korisnik nije studentska sluzba ",3); // nivo 3 - greska
		zamgerlog2("prijemni - korisnik nije studentska sluzba"); // nivo 3 - greska
		print "niste studentska sluzba"; break; 
	}

	$osoba = intval($_REQUEST['osoba']);
	$nova = floatval($_REQUEST['nova']);
	$rednibroj = intval($_REQUEST['rednibroj']); // nece biti nula

	$q100 = myquery("select count(*) from osoba where id=$osoba");
	if (mysql_result($q100,0,0)==0)  {
		print "Nepoznata osoba $osoba";
		break;
	}

	if ($nova==0) {
		$q140 = myquery("delete from prosliciklus_ocjene where osoba=$osoba and redni_broj=$rednibroj");
	} else {
		$q110 = myquery("select count(*) from prosliciklus_ocjene where osoba=$osoba and redni_broj=$rednibroj");
		if (mysql_result($q110,0,0)==0)
			$q120 = myquery("insert into prosliciklus_ocjene set osoba=$osoba, redni_broj=$rednibroj, ects=$nova");
		else
			$q130 = myquery("update prosliciklus_ocjene set ects=$nova where osoba=$osoba and redni_broj=$rednibroj");
	}

	print "OK";

	break;


case "spisak_predmeta":
	$ag = intval($_REQUEST['ag']);
	$studij = intval($_REQUEST['studij']);
	$semestar = intval($_REQUEST['semestar']);

	$q4 = myquery("select p.id,p.naziv,pk.akademska_godina from predmet as p, ponudakursa as pk where pk.predmet=p.id and pk.akademska_godina=$ag and pk.studij=$studij and pk.semestar=$semestar order by p.naziv");
	while ($r4 = mysql_fetch_row($q4)) {
		print "$r4[0] $r4[1]|";
	}

	print "OK";

	break;

default:

# Testna poruka

?>

Wellcome to ajah :)

<?

}

}

?>
