<?

// COMMON/AJAH - biblioteka za razmjenu podataka a la AJAX

// VAZNO: za svaku akciju je potrebno implementirati striktnu kontrolu prava pristupa,
// jer se to ne podrazumijeva


// Prebaciti u lib/manip?



function common_ajah() {

global $userid,$user_nastavnik,$user_siteadmin,$user_studentska, $_api_http_code;

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
	$idpolja = param('idpolja');
	$vrijednost = param('vrijednost');
	$staravrijednost = param('staravrijednost');

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
		
	} else if ($ime == "fiksna") {
		$stud_id = intval($parametri[1]);
		$predmet = intval($parametri[2]);
		$komponenta = intval($parametri[3]);
		$ag = intval($parametri[4]);

	} else if ($ime == "ko" || $ime == "kodatum") {
		// konacna ocjena
		$stud_id = intval($parametri[1]);
		if ($ime == "ko" && $vrijednost!="/") $vrijednost=intval($vrijednost); // zaokruzujemo
		$predmet=intval($parametri[2]);
		$ag = intval($parametri[3]);
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
		$novidatum = "$godina-$mjesec-$dan";
	}

	// Ažuriranje podataka u bazi
	if ($ime=="ispit") {
		if ($vrijednost!=="/") {
			$examResult = array_to_object( ["result" => $vrijednost] );
			$result = api_call("exam/$ispit/student/$stud_id", $examResult, "PUT");
			if ($_api_http_code == "201") {
				if ($staravrijednost !== "/") {
					zamgerlog("AJAH ko - izmjena rezultata $staravrijednost u $vrijednost (ispit i$ispit, student u$stud_id)", 4); // nivo 4: audit
					zamgerlog2("izmjenjen rezultat ispita", $stud_id, $ispit, 0, "$staravrijednost -> $vrijednost");
				} else {
					zamgerlog("AJAH ispit - upisan novi rezultat $vrijednost (ispit i$ispit, student u$stud_id)", 4); // nivo 4: audit
					zamgerlog2("upisan rezultat ispita", $stud_id, $ispit, 0, $vrijednost); // nivo 4: audit
				}
			} else {
				print "greška ($_api_http_code): " . $result['message'];
				break;
			}
		} else {
			$result = api_call("exam/$ispit/student/$stud_id", [], "DELETE");
			if ($_api_http_code == "204") {
				zamgerlog("AJAH ispit - izbrisan rezultat $staravrijednost (ispit i$ispit, student u$stud_id)",4); // nivo 4: audit
				zamgerlog2("izbrisan rezultat ispita", $stud_id, $ispit, 0, $staravrijednost); // nivo 4: audit
			} else {
				print "greška ($_api_http_code): " . $result['message'];
				break;
			}
		}

		// Generisem statičku verziju izvještaja predmet
		generisi_izvjestaj_predmet( $predmet, $ag, array('skrati' => 'da', 'sakrij_imena' => 'da', 'razdvoji_ispite' => 'da') );

	} else if ($ime == "fiksna") {
		// Treba nam ponuda kursa
		$portfolio = api_call("course/$predmet/student/$stud_id", [ "year" => $ag, "score" => true ]);
		$ponudakursa = $portfolio['CourseOffering']['id'];
		
		$studentScore = array_to_object( [ "student" => [ "id" => $stud_id ], "CourseActivity" => [ "id" => $komponenta ], "CourseOffering" => [ "id" => $ponudakursa ], "score" => $vrijednost ] );
		if ($vrijednost !== "/") {
			$result = api_call("course/$predmet/$ag/student/$stud_id/score", $studentScore, "PUT");
			if ($_api_http_code == "201") {
				zamgerlog("AJAH fiksna - upisani bodovi $vrijednost za fiksnu komponentu $komponenta (predmet pp$predmet, student u$stud_id)",4);
				zamgerlog2("izmjena bodova za fiksnu komponentu", intval($stud_id), intval($ponudakursa), intval($komponenta), $vrijednost);
			} else {
				print "greška ($_api_http_code): " . $result['message'];
				break;
			}
		} else {
			$result = api_call("course/$predmet/$ag/student/$stud_id/score", $studentScore, "DELETE");
			if ($_api_http_code == "204") {
				zamgerlog("AJAH fiksna - upisani bodovi $vrijednost za fiksnu komponentu $komponenta (predmet pp$predmet, student u$stud_id)",4);
				zamgerlog2("izmjena bodova za fiksnu komponentu", intval($stud_id), intval($ponudakursa), intval($komponenta), $vrijednost);
			} else {
				print "greška ($_api_http_code): " . $result['message'];
				break;
			}
		}

		// Generisem statičku verziju izvještaja predmet
		generisi_izvjestaj_predmet( $predmet, $ag, array('skrati' => 'da', 'sakrij_imena' => 'da', 'razdvoji_ispite' => 'da') );

	} else if ($ime == "ko") {
		// Konacna ocjena
		if ($vrijednost !== "/") {
			$portfolio = array_to_object( [ "grade" => $vrijednost, "gradeDate" => null ] );
			$result = api_call("course/$predmet/$ag/student/$stud_id/grade", $portfolio, "PUT");
			if ($_api_http_code == "201") {
				if ($staravrijednost !== "/") {
					zamgerlog("AJAH ko - izmjena ocjene $staravrijednost u $vrijednost (predmet pp$predmet, student u$stud_id)", 4); // nivo 4: audit
					zamgerlog2("izmjena ocjene", $stud_id, $predmet, $ag, "$staravrijednost -> $vrijednost");
				} else {
					zamgerlog("AJAH ko - dodana ocjena $vrijednost (predmet pp$predmet, student u$stud_id)", 4); // nivo 4: audit
					zamgerlog2("dodana ocjena", $stud_id, $predmet, $ag, $vrijednost);
				}
			} else {
				print "greška ($_api_http_code): " . $result['message'];
				break;
			}
		} else {
			$result = api_call("course/$predmet/$ag/student/$stud_id/grade", [], "DELETE");
			if ($_api_http_code == "204") {
				zamgerlog("AJAH ko - obrisana ocjena $staravrijednost (predmet pp$predmet, student u$stud_id)",4); // nivo 4: audit
				zamgerlog2("obrisana ocjena", $stud_id, $predmet, $ag, $staravrijednost);
			} else {
				print "greška ($_api_http_code): ";
				print_r($result);
				break;
			}
		}

		// Generisem statičku verziju izvještaja predmet
		generisi_izvjestaj_predmet( $predmet, $ag, array('skrati' => 'da', 'sakrij_imena' => 'da', 'razdvoji_ispite' => 'da') );

	} else if ($ime == "kodatum") {
		$portfolio = api_call("course/$predmet/student/$stud_id", [ "year" => $ag, "score" => true ], "GET", true, true, false);
		$portfolio->gradeDate = $novidatum;
		$result = api_call("course/$predmet/$ag/student/$stud_id/grade", $portfolio, "PUT");
		if ($_api_http_code == "201") {
			zamgerlog("AJAH kodatum - promijenjen datum u indeksu (predmet pp$predmet, student u$stud_id)", 4);
			zamgerlog2("promijenjen datum ocjene", $stud_id, $predmet, $ag, date("d.m.Y",$novidatum));
		} else {
			print "greška ($_api_http_code): " . $result['message'];
			break;
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
