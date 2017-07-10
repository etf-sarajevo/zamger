<?

// COMMON/AJAH - biblioteka za razmjenu podataka a la AJAX

// VAZNO: za svaku akciju je potrebno implementirati striktnu kontrolu prava pristupa,
// jer se to ne podrazumijeva


// Prebaciti u lib/manip?



function common_ajah() {

global $userid,$user_nastavnik,$user_siteadmin,$user_studentska;

require_once("lib/student_predmet.php"); // update_komponente


?>
<body onLoad="javascript:parent.ajah_stop()">
<?

switch ($_REQUEST['akcija']) {

case "prisustvo": // prebaceno na ws/prisustvo (POST metoda)
	
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

	$q10 = db_query("select c.labgrupa, l.predmet, l.akademska_godina from cas as c, labgrupa as l where c.id=$cas and c.labgrupa=l.id");
	if (db_num_rows($q10)<1) {
		zamgerlog("AJAH prisustvo - nepostojeci cas $cas",3);
		zamgerlog2("prisustvo - nepostojeci cas", $cas);
		print "nepostojeci cas"; break;
	}
	$labgrupa = db_result($q10,0,0);
	$predmet = db_result($q10,0,1);
	$ag = db_result($q10,0,2);


	// Provjera prava pristupa
	if (!$user_siteadmin) {
		$q15 = db_query("select count(*) from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
		if (db_num_rows($q15)<1) {
			zamgerlog("AJAH prisustvo - korisnik nije nastavnik (cas c$cas)",3);
			zamgerlog2("nije saradnik na predmetu (prisustvo)", $cas);
			print "niste nastavnik A"; break;
		}

		// Provjeravamo ogranicenja
		$q20 = db_query("select o.labgrupa from ogranicenje as o, labgrupa as l where o.nastavnik=$userid and o.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag");
		if (db_num_rows($q20)>0) {
			$nasao=0;
			while ($r20 = db_fetch_row($q20)) {
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
			$q0 = db_query("delete from prisustvo where student=$student and cas=$cas");
		} else {
			$prisutan--;
			$q1 = db_query("select prisutan from prisustvo where student=$student and cas=$cas");
			if (db_num_rows($q1)<1) 
				$q2 = db_query("insert into prisustvo set prisutan=$prisutan, student=$student, cas=$cas");
			else
				$q3 = db_query("update prisustvo set prisutan=$prisutan where student=$student and cas=$cas");
		}
	} else {
		zamgerlog("AJAH prisustvo - losa akcija, student: $student cas: $cas prisutan: $prisutan",3);
		zamgerlog2("prisustvo - losa akcija", $student, $cas, $prisutan);
		print "akcija je generalno loša"; 
		break;
	}

	// Ažuriranje komponenti
	// potrebna nam je ponudakursa za update_komponente
	$q3 = db_query("select pk.id from ponudakursa as pk, student_predmet as sp where sp.student=$student and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
	$ponudakursa = db_result($q3,0,0);

	$q4 = db_query("select k.id from tippredmeta_komponenta as tpk,komponenta as k, akademska_godina_predmet as agp where agp.predmet=$predmet and agp.tippredmeta=tpk.tippredmeta and agp.akademska_godina=$ag and tpk.komponenta=k.id and k.tipkomponente=3");
	while ($r4 = db_fetch_row($q4))
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
			$q40 = db_query("select 'nastavnik',pk.id,k.maxbodova,k.id,k.tipkomponente,k.opcija, pk.predmet from ispit as i, komponenta as k, ponudakursa as pk, student_predmet as sp where i.id=$ispit and i.komponenta=k.id and i.predmet=pk.predmet and i.akademska_godina=pk.akademska_godina and sp.predmet=pk.id and sp.student=$stud_id");
		else
			$q40 = db_query("select np.nivo_pristupa,pk.id,k.maxbodova,k.id,k.tipkomponente,k.opcija, pk.predmet from nastavnik_predmet as np, ispit as i, komponenta as k, ponudakursa as pk, student_predmet as sp where np.nastavnik=$userid and np.predmet=i.predmet and np.akademska_godina=i.akademska_godina and pk.predmet=i.predmet and pk.akademska_godina=i.akademska_godina and i.id=$ispit and i.komponenta=k.id and sp.predmet=pk.id and sp.student=$stud_id");
		if (db_num_rows($q40)<1) {
			zamgerlog("AJAH ispit - nepoznat ispit $ispit ili niste saradnik",3);
			zamgerlog2("ispit - nepoznat ispit ili nije saradnik",$ispit);
			print "nepoznat ispit $ispit ili niste saradnik na predmetu"; break;
		}
		if (db_result($q40,0,0) != "asistent") $padmin = 1;
		$ponudakursa = db_result($q40,0,1);
		$max = db_result($q40,0,2);
		// Potrebno za update komponenti:
		$komponenta = db_result($q40,0,3);
		$tipkomponente = db_result($q40,0,4);
		$kopcija = db_result($q40,0,5);
		$predmet = db_result($q40,0,6);

	} else if ($ime == "fiksna") {
		$stud_id = intval($parametri[1]);
		$predmet = intval($parametri[2]);
		$komponenta = intval($parametri[3]);
		$ag = intval($parametri[4]);

		// TODO: provjeriti da li komponenta postoji na predmetu
		$q40a = db_query("select maxbodova from komponenta where id=$komponenta and tipkomponente=5");
		if (db_num_rows($q40a)!=1) {
			zamgerlog("AJAH fiksna - nepoznata fiksna komponenta $komponenta",3);
			zamgerlog2("fiksna - nepoznata fiksna komponenta", $komponenta);
			print "nepoznata fiksna komponenta $komponenta"; break;
		}
		$max = db_result($q40a,0,0);

		if (!$user_siteadmin) {
			$q40b = db_query("select count(*) from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
			if (db_num_rows($q40b)<1) {
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

		$max=11;
		if (!$user_siteadmin && !$user_studentska) {
			$q41 = db_query("select nivo_pristupa from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
			if (db_num_rows($q41)<1) {
				zamgerlog("AJAH ispit/ko - niste saradnik (ispit pp$predmet, ag$ag)",3);
				zamgerlog2("nije saradnik na predmetu (ispit/ko)", $predmet, $ag);
				print "niste saradnik na predmetu $predmet";
				break;
			}
			if (db_result($q41,0,0)=="nastavnik") $padmin = 1;
		}
	}
	if ($padmin==0 && !$user_siteadmin && !$user_studentska) {
		zamgerlog("AJAH ispit - pogresne privilegije (ispit i$ispit)",3);
		zamgerlog2("ispit - pogresne privilegije", $ispit);
		print "niste nastavnik na predmetu $predmet niti admin!"; break;
	}

	// Da li je student na predmetu?
	$q45 = db_query ("select count(*) from student_predmet as sp, ponudakursa as pk where sp.student=$stud_id and sp.predmet=pk.id and pk.predmet=$predmet");
	if (db_result($q45,0,0)<1) {
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
		$q50 = db_query("select ocjena from ispitocjene where ispit=$ispit and student=$stud_id");
		$c = db_num_rows($q50);
		if ($c==0 && $vrijednost!=="/") {
			$q60 = db_query("insert into ispitocjene set ispit=$ispit, student=$stud_id, ocjena=$vrijednost");
			zamgerlog("AJAH ispit - upisan novi rezultat $vrijednost (ispit i$ispit, student u$stud_id)",4); // nivo 4: audit
			zamgerlog2("upisan rezultat ispita", $stud_id, $ispit, 0, $vrijednost); // nivo 4: audit
		} else if ($c>0 && $vrijednost==="/") {
			$staraocjena = db_result($q50,0,0);
			$q60 = db_query("delete from ispitocjene where ispit=$ispit and student=$stud_id");
			zamgerlog("AJAH ispit - izbrisan rezultat $staraocjena (ispit i$ispit, student u$stud_id)",4); // nivo 4: audit
			zamgerlog2("izbrisan rezultat ispita", $stud_id, $ispit, 0, $staraocjena); // nivo 4: audit
		} else if ($c>0) {
			$staraocjena = db_result($q50,0,0);
			$q60 = db_query("update ispitocjene set ocjena=$vrijednost where ispit=$ispit and student=$stud_id");
			zamgerlog("AJAH ispit - izmjena rezultata $staraocjena u $vrijednost (ispit i$ispit, student u$stud_id)",4); // nivo 4: audit
			zamgerlog2("izmjenjen rezultat ispita", $stud_id, $ispit, 0, "$staraocjena -> $vrijednost"); // nivo 4: audit
		}

		update_komponente($stud_id,$ponudakursa,$komponenta);

		// Generisem statičku verziju izvještaja predmet
		generisi_izvjestaj_predmet( $predmet, $ag, array('skrati' => 'da', 'sakrij_imena' => 'da') );

	} else if ($ime == "fiksna") {
		// Odredjujemo ponudukursa zbog tabele komponentebodovi
		$q62 = db_query("select pk.id from student_predmet as sp, ponudakursa as pk where sp.student=$stud_id and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
		$ponudakursa = db_result($q62,0,0);

		$q63 = db_query("delete from komponentebodovi where student=$stud_id and predmet=$ponudakursa and komponenta=$komponenta");
		if ($vrijednost != "/") $q66 = db_query("insert into komponentebodovi set student=$stud_id, predmet=$ponudakursa, komponenta=$komponenta, bodovi=$vrijednost");
		zamgerlog("AJAH fiksna - upisani bodovi $vrijednost za fiksnu komponentu $komponenta (predmet pp$predmet, student u$stud_id)",4);
		zamgerlog2("izmjena bodova za fiksnu komponentu", intval($stud_id), intval($ponudakursa), intval($komponenta), $vrijednost);

		// Generisem statičku verziju izvještaja predmet
		generisi_izvjestaj_predmet( $predmet, $ag, array('skrati' => 'da', 'sakrij_imena' => 'da') );

	} else if ($ime == "ko") {
		// Konacna ocjena
		
		// Određivanje trenutno važećeg pasoša predmeta 
		// FIXME pasoš predmeta treba biti dio ponudekursa - sada sam definitivno shvatio da je tako
		$pasos_predmeta = db_get("SELECT psp.pasos_predmeta FROM plan_studija_predmet psp, pasos_predmeta pp, plan_studija ps
		WHERE psp.pasos_predmeta=pp.id AND pp.predmet=$predmet AND psp.plan_studija=ps.id AND ps.godina_vazenja<=$ag ORDER BY psp.pasos_predmeta DESC LIMIT 1");
		if ($pasos_predmeta === false) {
			$pasos_predmeta = db_get("SELECT pis.pasos_predmeta FROM plan_studija_predmet psp, pasos_predmeta pp, plan_studija ps, plan_izborni_slot pis
			WHERE pis.pasos_predmeta=pp.id AND pp.predmet=$predmet AND psp.plan_izborni_slot=pis.id AND psp.plan_studija=ps.id AND ps.godina_vazenja<=$ag ORDER BY pis.pasos_predmeta DESC LIMIT 1");
		}
		if ($pasos_predmeta === false) $pasos_predmeta="NULL";
		
		/*// Pasoš predmeta za koji upisujemo ocjenu je onaj koji je u planu studija po kojem je student studirao date godine.
		// Za slučaj da je student mijenjao plan studija na prelazu iz zimskog u ljetnji semestar, a po jednom planu je predmet 
		// predviđen u zimskom a po drugom u ljetnjem semestru, uzimamo parnost semestra u kojem je ponudakursa koju je student
		// upisao.
		// Upit je dosta kompleksan ali nema drugog načina da se osiguramo od ovog slučaja.
		$pasos_predmeta = db_get("SELECT psp.pasos_predmeta FROM plan_studija_predmet psp, pasos_predmeta pp, student_studij ss, ponudakursa pk, student_predmet sp
				WHERE psp.pasos_predmeta=pp.id AND pp.predmet=$predmet AND psp.plan_studija=ss.plan_studija AND ss.student=$stud_id AND 
				ss.akademska_godina=$ag AND ss.semestar MOD 2=pk.semestar MOD 2 AND pk.id=sp.predmet AND pk.predmet=$predmet AND pk.akademska_godina=$ag
				AND sp.student=$stud_id
				ORDER BY psp.pasos_predmeta DESC LIMIT 1");*/

		// Ne koristimo REPLACE i slicno zbog logginga
		$q70 = db_query("select ocjena from konacna_ocjena where predmet=$predmet and student=$stud_id");
		$c = db_num_rows($q70);
		if ($c==0 && $vrijednost!="/") {
			// Određivanje datuma za indeks
			$q105 = db_query("SELECT UNIX_TIMESTAMP(it.datumvrijeme) 
			FROM ispit as i, ispit_termin as it, student_ispit_termin as sit 
			WHERE sit.student=$stud_id and sit.ispit_termin=it.id and it.ispit=i.id and i.predmet=$predmet and i.akademska_godina=$ag
			ORDER BY i.datum DESC LIMIT 1");
			if (db_num_rows($q105) > 0) {
				$datum_u_indeksu = db_result($q105,0,0);
				if ($datum_u_indeksu > time())
					$datum_provjeren = 0;
				else
					$datum_provjeren = 1;
			} else {
				$datum_u_indeksu = time();
				$datum_provjeren = 0;
			}

			$q80 = db_query("insert into konacna_ocjena set predmet=$predmet, akademska_godina=$ag, student=$stud_id, ocjena=$vrijednost, datum=NOW(), datum_u_indeksu=FROM_UNIXTIME($datum_u_indeksu), datum_provjeren=$datum_provjeren, pasos_predmeta=$pasos_predmeta");
			zamgerlog("AJAH ko - dodana ocjena $vrijednost (predmet pp$predmet, student u$stud_id)",4); // nivo 4: audit
			zamgerlog2("dodana ocjena", $stud_id, $predmet, $ag, $vrijednost);
		} else if ($c>0 && $vrijednost=="/") {
			$staraocjena = db_result($q70,0,0);
			$q80 = db_query("delete from konacna_ocjena where predmet=$predmet and student=$stud_id");
			zamgerlog("AJAH ko - obrisana ocjena $staraocjena (predmet pp$predmet, student u$stud_id)",4); // nivo 4: audit
			zamgerlog2("obrisana ocjena", $stud_id, $predmet, $ag, $staraocjena);
		} else if ($c>0) {
			$staraocjena = db_result($q70,0,0);
			$q80 = db_query("update konacna_ocjena set ocjena=$vrijednost, datum=NOW() where predmet=$predmet and student=$stud_id");
			zamgerlog("AJAH ko - izmjena ocjene $staraocjena u $vrijednost (predmet pp$predmet, student u$stud_id)",4); // nivo 4: audit
			zamgerlog2("izmjena ocjene", $stud_id, $predmet, $ag, "$staraocjena -> $vrijednost");
		}
		
		// Izvoz unesene ocjene
		if (db_get("SELECT COUNT(*) FROM izvoz_ocjena WHERE student=$stud_id AND predmet=$predmet") == 0)
			db_query("INSERT INTO izvoz_ocjena VALUES($stud_id,$predmet)");

		// Generisem statičku verziju izvještaja predmet
		generisi_izvjestaj_predmet( $predmet, $ag, array('skrati' => 'da', 'sakrij_imena' => 'da') );

	} else if ($ime == "kodatum") {
		// AJAH "kodatum" je uvijek promjena
		$q85 = db_query("select UNIX_TIMESTAMP(datum_u_indeksu), datum_provjeren from konacna_ocjena where predmet=$predmet and student=$stud_id");
		if (db_num_rows($q85) == 0) {
			print "ne moze se mijenjati datum dok se ne unese ocjena";
			break;
		}
		$staridatum = db_result($q85,0,0);
		$datum_provjeren = db_result($q85,0,1);

		if ($staridatum != $novidatum || $datum_provjeren == 0) {
			$q87 = db_query("update konacna_ocjena set datum_u_indeksu=FROM_UNIXTIME($novidatum), datum_provjeren=1 where predmet=$predmet and student=$stud_id");
			zamgerlog("AJAH kodatum - promijenjen datum u indeksu (predmet pp$predmet, student u$stud_id)", 4);
			zamgerlog2("promijenjen datum ocjene", $stud_id, $predmet, $ag, date("d.m.Y",$novidatum));
			
			if (db_get("SELECT COUNT(*) FROM izvoz_ocjena WHERE student=$stud_id AND predmet=$predmet") == 0)
				db_query("INSERT INTO izvoz_ocjena VALUES($stud_id,$predmet)");
		}
	}


	print "OK";
	break;


case "pretraga": // prebaceno na ws/osoba akcija=pretraga
	if ($userid == 0) {
		zamgerlog("AJAH pretraga - istekla sesija",3); // nivo 3 - greska
		zamgerlog2("pretraga - istekla sesija"); // nivo 3 - greska
		print "Vasa sesija je istekla. Pritisnite dugme Refresh da se ponovo prijavite.";
		break;
	}

	$ime = db_escape($_REQUEST['ime']);
	if (!preg_match("/\w/",$ime)) { print "OK"; return; }
	$ime = str_replace("(","",$ime);
	$ime = str_replace(")","",$ime);
	$imena = explode(" ",$ime);
	$upit = "";
	foreach($imena as $dio) {
		if ($upit != "") $upit .= " and ";
		$upit .= "(o.ime like '%$dio%' or o.prezime like '%$dio%' or a.login like '%$dio%' or o.brindexa like '%$dio%')";
	}
	$q10 = db_query("select a.login, o.ime, o.prezime from auth as a, osoba as o where a.id=o.id and $upit order by o.prezime, o.ime");
	$redova=0;
	while ($r10 = db_fetch_row($q10)) {
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

	$q100 = db_query("select count(*) from prijemni_prijava where osoba=$osoba and prijemni_termin=$termin");
	if (db_result($q100,0,0)==0)  {
		print "Nepoznat id $id";
		break;
	}
	// Dodati provjeru rezultata prijemnog...
	if ($_REQUEST['vrijednost'] == "/")
		$q110 = db_query("update prijemni_prijava set rezultat=0, izasao=0 where osoba=$osoba and prijemni_termin=$termin");
	else
		$q110 = db_query("update prijemni_prijava set rezultat=$vrijednost, izasao=1 where osoba=$osoba and prijemni_termin=$termin");

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
/*	$q100 = db_query("select count(*) from osoba where id=$osoba");
	if (db_result($q100,0,0)==0)  {
		print "Nepoznat id $prijemni";
		break;
	}*/

	if ($_REQUEST['subakcija']!="obrisi" && $_REQUEST['subakcija']!="izmijeni" && $_REQUEST['subakcija']!="dodaj") {
		print "Nepoznata akcija: ".db_escape($_REQUEST['akcija']);
		break;
	}

	if ($_REQUEST['subakcija']=="obrisi" || $_REQUEST['subakcija']=="izmijeni")
		$q200 = db_query("delete from srednja_ocjene where osoba=$osoba and razred=$razred and ocjena=$stara and tipocjene=$tipocjene and redni_broj=$rednibroj limit 1");
	if ($_REQUEST['subakcija']=="dodaj" || $_REQUEST['subakcija']=="izmijeni")
		$q200 = db_query("insert into srednja_ocjene set osoba=$osoba, razred=$razred, ocjena=$nova, tipocjene=$tipocjene, redni_broj=$rednibroj");

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

	$q100 = db_query("select count(*) from osoba where id=$osoba");
	if (db_result($q100,0,0)==0)  {
		print "Nepoznata osoba $osoba";
		break;
	}

	if ($nova==0) {
		$q140 = db_query("delete from prosliciklus_ocjene where osoba=$osoba and redni_broj=$rednibroj");
	} else if ($nova<6 || $nova>10) {
		print "Ocjena nije u opsegu 6-10";
		break;
	} else {
	
		$q110 = db_query("select count(*) from prosliciklus_ocjene where osoba=$osoba and redni_broj=$rednibroj");
		if (db_result($q110,0,0)==0)
			$q120 = db_query("insert into prosliciklus_ocjene set osoba=$osoba, redni_broj=$rednibroj, ocjena=$nova");
		else
			$q130 = db_query("update prosliciklus_ocjene set ocjena=$nova where osoba=$osoba and redni_broj=$rednibroj");
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

	$q100 = db_query("select count(*) from osoba where id=$osoba");
	if (db_result($q100,0,0)==0)  {
		print "Nepoznata osoba $osoba";
		break;
	}

	if ($nova==0) {
		$q140 = db_query("delete from prosliciklus_ocjene where osoba=$osoba and redni_broj=$rednibroj");
	} else {
		$q110 = db_query("select count(*) from prosliciklus_ocjene where osoba=$osoba and redni_broj=$rednibroj");
		if (db_result($q110,0,0)==0)
			$q120 = db_query("insert into prosliciklus_ocjene set osoba=$osoba, redni_broj=$rednibroj, ects=$nova");
		else
			$q130 = db_query("update prosliciklus_ocjene set ects=$nova where osoba=$osoba and redni_broj=$rednibroj");
	}

	print "OK";

	break;


case "spisak_predmeta": // prebaceno na ws/predmet
	$ag = intval($_REQUEST['ag']);
	$studij = intval($_REQUEST['studij']);
	$semestar = intval($_REQUEST['semestar']);

	$q4 = db_query("select p.id,p.naziv,pk.akademska_godina from predmet as p, ponudakursa as pk where pk.predmet=p.id and pk.akademska_godina=$ag and pk.studij=$studij and pk.semestar=$semestar order by p.naziv");
	while ($r4 = db_fetch_row($q4)) {
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
