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

// Prebaciti u lib/manip?



function common_ajah() {

global $userid,$user_nastavnik,$user_siteadmin;

require("lib/manip.php");


?>
<body onload="javascript:parent.ajah_stop()">
<?

switch ($_REQUEST['akcija']) {

case "prisustvo":
	if (!$user_nastavnik && !$user_siteadmin) {
		zamgerlog("AJAH prisustvo - korisnik nije nastavnik",3); // nivo 3 - greska
		print "niste nastavnik"; break; 
	}

	$student=intval($_GET['student']);
	$cas=intval($_GET['cas']);
	$prisutan=intval($_GET['prisutan']);


	// Provjera prava pristupa

	if (!$user_siteadmin) {
		$q10 = myquery("select predmet, labgrupa from cas where id=$cas");
		if (mysql_num_rows($q10)<1) {
			zamgerlog("AJAH prisustvo - nepostojeci cas $cas",3);
			print "nepostojeci cas"; break;
		}
		$predmet = mysql_result($q10,0,0);
		$labgrupa = mysql_result($q10,0,1);

		if ($labgrupa==0) 
			$q15 = myquery("select count(*) from nastavnik_predmet as np, ponudakursa as pk where np.nastavnik=$userid and np.predmet=pk.predmet and np.akademska_godina=pk.akademska_godina and pk.id=$predmet");
		else
			$q15 = myquery("select count(*) from nastavnik_predmet as np,labgrupa as l, ponudakursa as pk where np.nastavnik=$userid and np.predmet=pk.predmet and np.akademska_godina=pk.akademska_godina and pk.id=l.predmet and l.id=$labgrupa");
		if (mysql_num_rows($q15)<1) {
			zamgerlog("AJAH prisustvo - korisnik nije nastavnik (cas c$cas)",3);
			print "niste nastavnik A"; break;
		}

		// Provjeravamo ogranicenja
		$q20 = myquery("select o.labgrupa from ogranicenje as o, labgrupa as l where o.nastavnik=$userid and o.labgrupa=l.id and l.predmet=$predmet");
		if (mysql_num_rows($q20)>0) {
			$nasao=0;
			while ($r20 = mysql_fetch_row($q20)) {
				// Ako je labgrupa 0 nece ga nikada nac
				if ($r20[0] == $labgrupa) { $nasao=1; break; }
			}
			if ($nasao == 0) {
				zamgerlog("AJAH prisustvo - korisnik ima ogranicenje za grupu (cas c$cas)",3);
				print "imate ograničenje na ovu grupu"; break;
			}
		}
	} else {
		// Treba nam predmet
		$q25 = myquery("select predmet from cas where id=$cas");
		$predmet = mysql_result($q25,0,0);
	}


	// Akcija

	if ($student>0 && $cas>0 && $prisutan>0) {
		$prisutan--;
		$q1 = myquery("select prisutan from prisustvo where student=$student and cas=$cas");
		if (mysql_num_rows($q1)<1) 
			$q2 = myquery("insert into prisustvo set prisutan=$prisutan, student=$student, cas=$cas");
		else
			$q3 = myquery("update prisustvo set prisutan=$prisutan where student=$student and cas=$cas");
	} else {
		zamgerlog("AJAH prisustvo - losa akcija, student: $student cas: $cas prisutan: $prisutan",3);
		print "akcija je generalno loša"; 
		break;
	}

	// Ažuriranje komponenti
	$q4 = myquery("select k.id from tippredmeta_komponenta as tpk,komponenta as k, ponudakursa as pk, predmet as p where pk.id=$predmet and pk.predmet=p.id and p.tippredmeta=tpk.tippredmeta and tpk.komponenta=k.id and k.tipkomponente=3");
	while ($r4 = mysql_fetch_row($q4))
		update_komponente($student,$predmet,$r4[0]);
	zamgerlog("AJAH prisustvo - student: u$student cas: c$cas prisutan: $prisutan",2); // nivo 2 - edit

	print "OK";
	break;


case "izmjena_ispita":

	// TODO: treci tip vrijenosti, fiksna komponenta

	if (!$user_nastavnik) {
		zamgerlog("AJAH prisustvo - korisnik nije nastavnik",3); // nivo 3 - greska
		print "niste nastavnik"; break; 
	}

	// Provjera validnosti primljenih podataka
	$idpolja = $_REQUEST['idpolja'];
	$vrijednost = $_REQUEST['vrijednost'];
	if (!preg_match("/\d/", $vrijednost)) {
		if ($vrijednost != "/") {
			zamgerlog("AJAH ispit - vrijednost $vrijednost nije ni broj ni /",3);
			print "ne valja vrijednost"; break;
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

	// Provjera prava pristupa i dodatna validacija parametara
	if ($ime == "ispit") {
		$stud_id = intval($parametri[1]);
		$ispit = intval($parametri[2]);
		if ($user_siteadmin)
			$q40 = myquery("select 1,pk.id,k.maxbodova,k.id,k.tipkomponente,k.opcija from ispit as i, komponenta as k, ponudakursa as pk where i.id=$ispit and i.komponenta=k.id and i.predmet=pk.predmet and i.akademska_godina=pk.akademska_godina");
		else
			$q40 = myquery("select np.admin,pk.id,k.maxbodova,k.id,k.tipkomponente,k.opcija from nastavnik_predmet as np, ispit as i, komponenta as k, ponudakursa as pk where np.nastavnik=$userid and np.predmet=i.predmet and np.akademska_godina=i.akademska_godina and pk.predmet=i.predmet and pk.akademska_godina=i.akademska_godina and i.id=$ispit and i.komponenta=k.id");
		if (mysql_num_rows($q40)<1) {
			zamgerlog("AJAH ispit - nepoznat ispit $ispit ili niste saradnik",3);
			print "nepoznat ispit $ispit ili niste saradnik na predmetu"; break;
		}
		$padmin = mysql_result($q40,0,0);
		$predmet = mysql_result($q40,0,1);
		$max = mysql_result($q40,0,2);
		// Potrebno za update komponenti:
		$komponenta = mysql_result($q40,0,3);
		$tipkomponente = mysql_result($q40,0,4);
		$kopcija = mysql_result($q40,0,5);

	} else if ($ime == "fiksna") {
		$stud_id = intval($parametri[1]);
		$predmet = intval($parametri[2]);
		$komponenta = intval($parametri[3]);

		// TODO: provjeriti da li komponenta postoji na predmetu
		$q40a = myquery("select maxbodova from komponenta where id=$komponenta and tipkomponente=5");
		if (mysql_num_rows($q40a)!=1) {
			zamgerlog("AJAH fiksna - nepoznata fiksna komponenta $komponenta",3);
			print "nepoznata fiksna komponenta $komponenta"; break;
		}
		$max = mysql_result($q40a,0,0);

		if (!$user_siteadmin) {
			$q40b = myquery("select count(*) from nastavnik_predmet as np, ponudakursa as pk where np.nastavnik=$userid and np.predmet=pk.predmet and np.akademska_godina=pk.akademska_godina and pk.id=$predmet");
			if (mysql_num_rows($q40b)<1) {
				zamgerlog("AJAH fiksna - nije na predmetu p$predmet",3);
				print "niste saradnik na predmetu"; break;
			}
		}
		$padmin=1; // Dozvoljavamo saradnicima da unose fiksne komponente

	} else if ($ime == "ko") {
		// konacna ocjena
		$stud_id = intval($parametri[1]);
		if ($vrijednost!="/") $vrijednost=intval($vrijednost); // zaokruzujemo
		$predmet=intval($parametri[2]);
		$max=10;
		if (!$user_siteadmin) {
			$q41 = myquery("select np.admin from nastavnik_predmet as np, ponudakursa as pk where np.nastavnik=$userid and np.predmet=pk.predmet and np.akademska_godina=pk.akademska_godina and pk.id=$predmet");
			if (mysql_num_rows($q41)<1) {
				zamgerlog("AJAH ispit/ko - niste saradnik (ispit i$ispit)",3);
				print "niste saradnik na predmetu $predmet";
				break;
			}
			$padmin=mysql_result($q41,0,0);
		}
	}
	if ($padmin==0 && !$user_siteadmin) {
		zamgerlog("AJAH ispit - pogresne privilegije (ispit i$ispit)",3);
		print "niste nastavnik na predmetu $predmet niti admin!"; break;
	}

	// Da li je student na predmetu?
	$q45 = myquery ("select count(*) from student_predmet where student=$stud_id and predmet=$predmet");
	if (mysql_result($q45,0,0)<1) {
		zamgerlog("AJAH ispit - student u$stud_id ne slusa predmet p$predmet (ispit i$ispit)",3);
		print "student $stud_id ne sluša predmet $predmet"; break;
	}

	// Maksimalan i minimalan broj bodova
	if ($vrijednost>$max) {
		zamgerlog("AJAH ispit - vrijednost $vrijednost > max $max",3);
		if ($ime=="ko")
			print "stavili ste ocjenu veću od 10";
		else
			print "maksimalan broj bodova je $max, a unijeli ste $vrijednost";
		break;
	}
	if ($ime=="ko" && $vrijednost<6 && $vrijednost!=="/") {
		zamgerlog("AJAH ispit - konacna ocjena manja od 6 ($vrijednost)",3);
		print "stavili ste ocjenu manju od 6";
		break;
	}

	// Ažuriranje podataka u bazi
	if ($ime=="ispit") {
		$q50 = myquery("select ocjena from ispitocjene where ispit=$ispit and student=$stud_id");
		$c = mysql_num_rows($q50);
		if ($c==0 && $vrijednost!=="/") {
			$q60 = myquery("insert into ispitocjene set ispit=$ispit, student=$stud_id, ocjena=$vrijednost");
			zamgerlog("AJAH ispit - upisan novi rezultat $vrijednost (ispit i$ispit, student u$stud_id)",4); // nivo 4: audit
		} else if ($c>0 && $vrijednost==="/") {
			$staraocjena = mysql_result($q50,0,0);
			$q60 = myquery("delete from ispitocjene where ispit=$ispit and student=$stud_id");
			zamgerlog("AJAH ispit - izbrisan rezultat $staraocjena (ispit i$ispit, student u$stud_id)",4); // nivo 4: audit
		} else if ($c>0) {
			$staraocjena = mysql_result($q50,0,0);
			$q60 = myquery("update ispitocjene set ocjena=$vrijednost where ispit=$ispit and student=$stud_id");
			zamgerlog("AJAH ispit - izmjena rezultata $staraocjena u $vrijednost (ispit i$ispit, student u$stud_id)",4); // nivo 4: audit
		}

		update_komponente($stud_id,$predmet,$komponenta);

	} else if ($ime == "fiksna") {
//		update_komponente($stud_id,$predmet,$komponenta);
		$q63 = myquery("delete from komponentebodovi where student=$stud_id and predmet=$predmet and komponenta=$komponenta");
		if ($vrijednost != "/") $q66 = myquery("insert into komponentebodovi set student=$stud_id, predmet=$predmet, komponenta=$komponenta, bodovi=$vrijednost");


	} else if ($ime == "ko") {
		// Odredjujemo metapredmet i akademsku godinu
		$q68 = myquery("select predmet, akademska_godina from ponudakursa where id=$predmet");
		$metapredmet = mysql_result($q68,0,0);
		$ag = mysql_result($q68,0,1);

		// Konacna ocjena
		// TODO: koristiti REPLACE
		$q70 = myquery("select ocjena from konacna_ocjena where predmet=$metapredmet and student=$stud_id");
		$c = mysql_num_rows($q70);
		if ($c==0 && $vrijednost!="/") {
			$q80 = myquery("insert into konacna_ocjena set predmet=$metapredmet, akademska_godina=$ag, student=$stud_id, ocjena=$vrijednost");
			zamgerlog("AJAH ko - dodana ocjena $vrijednost (predmet p$predmet, student u$stud_id)",4); // nivo 4: audit
		} else if ($c>0 && $vrijednost=="/") {
			$staraocjena = mysql_result($q70,0,0);
			$q80 = myquery("delete from konacna_ocjena where predmet=$metapredmet and student=$stud_id");
			zamgerlog("AJAH ko - obrisana ocjena $staraocjena (predmet p$predmet, student u$stud_id)",4); // nivo 4: audit
		} else if ($c>0) {
			$staraocjena = mysql_result($q70,0,0);
			$q80 = myquery("update konacna_ocjena set ocjena=$vrijednost where predmet=$metapredmet and student=$stud_id");
			zamgerlog("AJAH ko - izmjena ocjene $staraocjena u $vrijednost (predmet p$predmet, student u$stud_id)",4); // nivo 4: audit
		}
	}


	print "OK";
	break;


case "pretraga":
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
	$id = intval($_REQUEST['idpolja']);
	$vrijednost = floatval(str_replace(",",".",$_REQUEST['vrijednost']));
	$q100 = myquery("select count(*) from prijemni where id=$id");
	if (mysql_result($q100,0,0)==0)  {
		print "Nepoznat id $id";
		break;
	}
	// Dodati provjeru rezultata prijemnog...
	if ($_REQUEST['vrijednost'] == "/")
		$q110 = myquery("update prijemni set prijemni_ispit_dva=0, izasao_na_prijemni=0 where id=$id");
	else
		$q110 = myquery("update prijemni set prijemni_ispit_dva=$vrijednost, izasao_na_prijemni=1 where id=$id");
	print "OK";

	break;


// Unos ocjena tokom srednje skole za prijemni
case "prijemni_ocjene":
	$prijemni = intval($_REQUEST['prijemni']);

	$nova = intval($_REQUEST['nova']);
	$stara = intval($_REQUEST['stara']);
	$razred = intval($_REQUEST['razred']);
	$tipocjene = intval($_REQUEST['tipocjene']);

// Pretpostavljamo da je id tačan
// Glupost :( ali šta se može kad se ocjene moraju unositi prije nego što se registruje prijemni
/*	$q100 = myquery("select count(*) from prijemni where id=$prijemni");
	if (mysql_result($q100,0,0)==0)  {
		print "Nepoznat id $prijemni";
		break;
	}*/

	if ($_REQUEST['subakcija']!="obrisi" && $_REQUEST['subakcija']!="izmijeni" && $_REQUEST['subakcija']!="dodaj") {
		print "Nepoznata akcija: ".my_escape($_REQUEST['akcija']);
		break;
	}

	if ($_REQUEST['subakcija']=="obrisi" || $_REQUEST['subakcija']=="izmijeni")
		$q200 = myquery("delete from prijemniocjene where prijemni=$prijemni and razred=$razred and ocjena=$stara and tipocjene=$tipocjene limit 1");
	if ($_REQUEST['subakcija']=="dodaj" || $_REQUEST['subakcija']=="izmijeni")
		$q200 = myquery("insert into prijemniocjene set prijemni=$prijemni, razred=$razred, ocjena=$nova, tipocjene=$tipocjene");

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
