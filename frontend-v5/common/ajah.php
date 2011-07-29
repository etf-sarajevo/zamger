<?

// COMMON/AJAH - asinhroni javascript i html - ukinuti!!!


function common_ajah() {

global $userid,$user_nastavnik,$user_siteadmin,$user_studentska;

require("lib/manip.php");

require_once("Config.php"); // pomjeriti u index.php?



?>
<body onLoad="javascript:parent.ajah_stop()">
<?

switch ($_REQUEST['akcija']) {

// Postavljanje prisustva
case "prisustvo":

	require_once(Config::$backend_path."core/CourseUnitYear.php");

	require_once(Config::$backend_path."lms/attendance/Attendance.php");
	require_once(Config::$backend_path."lms/attendance/ZClass.php");
	require_once(Config::$backend_path."lms/attendance/Group.php");

	$student = intval($_GET['student']);
	$cas = intval($_GET['cas']);
	$prisutan = intval($_GET['prisutan']);

	// Provjera prava pristupa
	if ($userid == 0) {
		zamgerlog("AJAH prisustvo - istekla sesija",3); // nivo 3 - greska
		print "Vasa sesija je istekla. Pritisnite dugme Refresh da se ponovo prijavite.";
		break;
	}

	if (!$user_nastavnik && !$user_siteadmin) {
		zamgerlog("AJAH prisustvo - korisnik nije nastavnik",3); // nivo 3 - greska
		print "niste nastavnik"; break; 
	}

	try {
		if (!($student>0 && $cas>0 && $prisutan>0)) throw new Exception("negativne vrijednosti");

		$a = Attendance::fromStudentAndClass($student, $cas);
	
		// Da li je nastavnik na predmetu?
		if (!$user_siteadmin) {
			$cuy = new CourseUnitYear;
			$cuy->courseUnitId = $a->zclass->group->courseUnitId;
			$cuy->academicYearId = $a->zclass->group->academicYearId;
	
			if ($cuy->teacherAccessLevel($userid) == "nema") {
				zamgerlog("AJAH prisustvo - korisnik nema prava (cas c$cas)",3);
				print "nemate prava pristupa"; break;
			}
	
			// Da li nastavnik ima ograničenja na grupe?
			if (!$a->zclass->group->isTeacher($userid)) {
				zamgerlog("AJAH prisustvo - korisnik ima ogranicenje za grupu (cas c$cas)",3);
				print "imate ograničenje na ovu grupu"; break;
			}
		}
	
		// Postavi prisustvo
		if ($prisutan==1) $a->setPresence(false); else $a->setPresence(true);

	} catch (Exception $e) {
		zamgerlog("AJAH prisustvo - ".$e->getMessage().", student: $student cas: $cas prisutan: $prisutan",3);
		print $e->getMessage(); break;
	}

	print "OK";
	break;


// Postavljanje ispita, konačne ocjene i fiksnih komponenti
case "izmjena_ispita":

	require_once(Config::$backend_path."core/Portfolio.php");
	require_once(Config::$backend_path."core/CourseUnitYear.php");

	require_once(Config::$backend_path."lms/exam/ExamResult.php");

	if ($userid == 0) {
		zamgerlog("AJAH ispit - istekla sesija",3); // nivo 3 - greska
		print "Vasa sesija je istekla. Pritisnite dugme Refresh da se ponovo prijavite.";
		break;
	}

	if (!$user_nastavnik && !$user_studentska && !$user_siteadmin) {
		zamgerlog("AJAH ispit - korisnik nije nastavnik",3); // nivo 3 - greska
		print "niste nastavnik"; break; 
	}

	// Provjera validnosti primljenih podataka
	$idpolja = $_REQUEST['idpolja'];
	$vrijednost = $_REQUEST['vrijednost'];
	if (!preg_match("/\d/", $vrijednost)) {
		if ($vrijednost != "/") {
			zamgerlog("AJAH ispit - vrijednost $vrijednost nije ni broj ni /",3);
			print "Vrijednost $vrijednost nije ni broj ni /"; break;
		}
	} else {
		$vrijednost = floatval(str_replace(",",".",$vrijednost));
	}

	$parametri = array();
	$parametri = explode("-",$idpolja);
	$ime = $parametri[0];
	if ($ime != "ispit" && $ime!="ko" && $ime!="fiksna") {
		// ko = konacna ocjena
		zamgerlog("AJAH ispit - ne valja id polja ($idpolja)",3);
		print "ne valja ID polja $idpolja"; break;
	}

	// ----- AJAH ZA ISPITE
	if ($ime == "ispit") {
		try {
			$stud_id = intval($parametri[1]);
			$ispit = intval($parametri[2]);
	
			$er = ExamResult::fromStudentAndExam($stud_id, $ispit);
	
			// Da li je nastavnik na predmetu?
			if (!$user_siteadmin && !$user_studentska) {
				$cuy = new CourseUnitYear;
				$cuy->courseUnitId = $er->exam->courseUnitId;
				$cuy->academicYearId = $er->exam->academicYearId;
				$nivo = $cuy->teacherAccessLevel($userid);
		
				if ($nivo != "nastavnik" && $nivo != "super_asistent") {
					zamgerlog("AJAH ispit - pogresne privilegije (ispit i$ispit)",3);
					print "niste nastavnik na predmetu $predmet niti admin!"; break;
				}
			}
	
			if ($vrijednost==="/") 
				$er->deleteExamResult();
			else
				$er->setExamResult($vrijednost);

		} catch(Exception $e) {
			zamgerlog("AJAH ispit - ".$e->getMessage()." (ispit $ispit)", 3);
			print "e: ".$e->getMessage()."<br>\n"; 
			break;
		}
	}


	// ----- AJAH ZA FIKSNE KOMPONENTE
	if ($ime == "fiksna") {
		try {
			$stud_id = intval($parametri[1]);
			$predmet = intval($parametri[2]);
			$komponenta = intval($parametri[3]);
			$ag = intval($parametri[4]);

			// Da li je nastavnik na predmetu?
			if (!$user_siteadmin) {
				$cuy = new CourseUnitYear;
				$cuy->courseUnitId = $predmet;
				$cuy->academicYearId = $ag;
				$nivo = $cuy->teacherAccessLevel($userid);
		
				if ($nivo != "nastavnik" && $nivo != "super_asistent" && $nivo != "asistent") {
					zamgerlog("AJAH fiksna - pogresne privilegije (ispit i$ispit)",3);
					print "niste nastavnik na predmetu $predmet niti admin!"; break;
				}
			}
	
			$p = Portfolio::fromCourseUnit($stud_id, $predmet, $ag);
			if ($vrijednost==="/") 
				$p->deleteScore($komponenta);
			else
				$p->setScore($komponenta, $vrijednost);

		} catch(Exception $e) {
			zamgerlog("AJAH fiksna - ".$e->getMessage()." (u$stud_id pp$predmet komponenta $komponenta ag$ag)", 3);
			print "e: ".$e->getMessage()."<br>\n"; 
			break;
		}
	}


	// ----- AJAH ZA KONAČNU OCJENU
	if ($ime == "ko") {
		try {
			// konacna ocjena
			$stud_id = intval($parametri[1]);
			$predmet=intval($parametri[2]);
			$ag = intval($parametri[3]);

			// Da li je nastavnik na predmetu?
			if (!$user_siteadmin && !$user_studentska) {
				$cuy = new CourseUnitYear;
				$cuy->courseUnitId = $predmet;
				$cuy->academicYearId = $ag;
				$nivo = $cuy->teacherAccessLevel($userid);
		
				if ($nivo != "nastavnik") {
					zamgerlog("AJAH ko - pogresne privilegije (ispit i$ispit)",3);
					print "niste nastavnik na predmetu $predmet niti admin!"; break;
				}
			}

			$p = Portfolio::fromCourseUnit($stud_id, $predmet, $ag);
			if ($vrijednost==="/") 
				$p->deleteGrade();
			else
				$p->setGrade(intval($vrijednost)); // zaokružujemo

		} catch(Exception $e) {
			zamgerlog("AJAH fiksna - ".$e->getMessage()." (u$stud_id pp$predmet komponenta $komponenta ag$ag)", 3);
			print "e: ".$e->getMessage()."<br>\n"; 
			break;
		}
	}

	print "OK";
	break;


case "pretraga":
	if ($userid == 0) {
		zamgerlog("AJAH pretraga - istekla sesija",3); // nivo 3 - greska
		print "Vasa sesija je istekla. Pritisnite dugme Refresh da se ponovo prijavite.";
		break;
	}

	require_once(Config::$backend_path."core/Person.php");

	$ime = my_escape($_REQUEST['ime']);
	
	$persons = Person::search($ime);
	foreach ($persons as $p) {
		$primalac = "$r10[0] ($r10[1] $r10[2])";
		print $p->login." (".$p->name." ".$p->surname.")\n";
		$redova++;
		if ($redova>10) break;
	}
	if ($redova==0 && count($persons)>0) {
		print "Nema rezultata\n";
	}
	print "OK";

	break;


// Unos bodova sa prijemnog
case "prijemni_unos":

	if ($userid == 0) {
		zamgerlog("AJAH prijemni - istekla sesija",3); // nivo 3 - greska
		print "Vasa sesija je istekla. Pritisnite dugme Refresh da se ponovo prijavite.";
		break;
	}

	if (!$user_studentska && !$user_siteadmin) {
		zamgerlog("AJAH prijemni - korisnik nije studentska sluzba ",3); // nivo 3 - greska
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

	break;


// Unos ocjena tokom srednje skole za prijemni
case "prijemni_ocjene":

	if ($userid == 0) {
		zamgerlog("AJAH prijemni - istekla sesija",3); // nivo 3 - greska
		print "Vasa sesija je istekla. Pritisnite dugme Refresh da se ponovo prijavite.";
		break;
	}

	if (!$user_studentska && !$user_siteadmin) {
		zamgerlog("AJAH prijemni - korisnik nije studentska sluzba ",3); // nivo 3 - greska
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
		print "Vasa sesija je istekla. Pritisnite dugme Refresh da se ponovo prijavite.";
		break;
	}

	if (!$user_studentska && !$user_siteadmin) {
		zamgerlog("AJAH prijemni - korisnik nije studentska sluzba ",3); // nivo 3 - greska
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
		print "Vasa sesija je istekla. Pritisnite dugme Refresh da se ponovo prijavite.";
		break;
	}

	if (!$user_studentska && !$user_siteadmin) {
		zamgerlog("AJAH prijemni - korisnik nije studentska sluzba ",3); // nivo 3 - greska
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

	require_once(Config::$backend_path."core/CourseOffering.php");
	$predmeti = CourseOffering::getCoursesOffered($ag, $studij, $semestar);
	foreach ($predmeti as $p)
		print $p->courseUnit->id." ".$p->courseUnit->name."|";

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
