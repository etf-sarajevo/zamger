<?

// LIB/MANIP - manipulacije bazom podataka (ispis studenta sa predmeta i sl.)

// v3.9.1.0 (2008/02/28) + Nova biblioteka, radi centralizovanja operacija koje vrse kompleksne manipulacije sa bazom
// v3.9.1.1 (2008/04/10) + Typo u update_komponente, dio za zadace; ne racunamo prisustvo ni zadace ako nije registrovan nijedan cas / zadaca
// v3.9.1.2 (2008/04/14) + Ponistavam zadnju izmjenu - ako nije odrzan nijedan cas treba dati max bodova za prisustvo 
// v3.9.1.3 (2008/04/24) + mass_input(): (!$f) zamijenjeno sa ($f) (provjeriti sve module!); dodano trimovanje imena i prezimena i ljepse upozorenje kod gresaka; ako student nije na predmetu a nema bodova, to nije greska
// v3.9.1.4 (2008/05/16) + Optimizovan update_komponente() tako da se moze zadati bilo koja komponenta, ukinuto update_komponente_prisustvo
// v3.9.1.5 (2008/08/28) + Tabela osoba umjesto auth; omoguceno koristenje masovnog unosa kada nije definisan predmet
// v3.9.1.6 (2008/11/24) + mass_input(): zamijeni Unicode karakter "non-breakable space" razmakom
// v3.9.1.7 (2009/01/20) + Priblizavam upite za brisanje i unos komponenti kod zadaca jer se desavalo da paralelni proces unese nesto drugo; eksperiment sa lock tables
// v4.0.0.0 (2009/02/19) + Release
// v4.0.9.1 (2009/03/24) + Prebacena polja ects i tippredmeta iz tabele ponudakursa u tabelu predmet
// v4.0.9.2 (2009/03/31) + Tabela ispit preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.3 (2009/04/01) + Tabela zadaca preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.4 (2009/04/23) + Prebacena tabela labgrupa sa ponudekursa na predmet; funkcije ispis_studenta_sa... sada primaju predmet a ne ponudukursa; ukinut zastarjeli logging u ispis_studenta_sa_predmeta; massinput sada moze primiti ponudukursa ili predmet+ag
// v4.0.9.5 (2009/05/06) + Dodajem funkciju upis_studenta_na_predmet koja za sada samo upisuje studenta i u virtuelnu labgrupu
// v4.0.9.6 (2009/05/08) + Popravljam update_komponente za prisustvo, tabela cas vise ne sadrzi predmet
// v4.0.9.7 (2009/05/15) + Direktorij za zadace je sada predmet-ag umjesto ponudekursa
// v4.0.9.8 (2009/05/17) + Ukidamo nultu labgrupu kod ispisa sa predmeta
// v4.0.9.9 (2009/09/13) + Redizajniran ispis kod masovnog unosa, sugerisao: Zajko
// v4.0.9.10 (2009/09/16) + Prilikom upisa na predmet upisujem default bodove za prisustvo u tabelu komponentebodovi


// NOTE:  Pretpostavka je da su podaci legalni i da je baza konzistentna




// Funkcija koja ispisuje studenta iz labgrupe, brisuci sve relevantne podatke
// (prisustvo, komentari)
// Ne zaboravite updatovati komponente ako treba (prisustvo je promijenjeno)!

function ispis_studenta_sa_labgrupe($student,$labgrupa) {
	// Prisustvo
	$q10 = myquery("select id from cas where labgrupa=$labgrupa");
	while ($r10 = mysql_fetch_row($q10)) {
		$q20 = myquery("delete from prisustvo where student=$student and cas=$r10[0]");
	}
	// Komentari
	$q20 = myquery("delete from komentar where student=$student and labgrupa=$labgrupa");

	// Ispis iz labgrupe
	if ($labgrupa>0) $q30 = myquery("delete from student_labgrupa where student=$student and labgrupa=$labgrupa");
}


// Funkcija koja ispisuje studenta sa predmeta, brisuci sve relevantne podatke 
// (ispis sa svih labgrupa, ispiti, konacna ocjena, komponente, zadace)

function ispis_studenta_sa_predmeta($student,$predmet,$ag) {
// Ovo bi se dalo optimizovati
	global $conf_files_path;

	// Odredjujem ponudukursa sto je potrebno za naredna dva upita
	$q225 = myquery("select sp.predmet from student_predmet as sp, ponudakursa as pk where sp.student=$student and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
	if (mysql_num_rows($q225) == 0) {
		biguglyerror("Student nije upisan na odabrani predmet");
		return;
	}
	$ponudakursa = mysql_result($q225,0,0);

	// Odredjivanje labgrupa ciji je student eventualno clan
	$q40 = myquery("select sl.labgrupa from student_labgrupa as sl,labgrupa as l where sl.student=$student and sl.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag");
	while ($r40 = mysql_fetch_row($q40)) {
		ispis_studenta_sa_labgrupe($student,$r40[0]);
	}

	// Ocjene na ispitima
	$q50 = myquery("select id from ispit where predmet=$predmet and akademska_godina=$ag");
	while ($r50 = mysql_fetch_row($q50)) {
		$q60 = myquery("delete from ispitocjene where student=$student and ispit=$r50[0]");
	}

	// Konacne ocjene
	$q70 = myquery("delete from konacna_ocjena where student=$student and predmet=$predmet and akademska_godina=$ag");
	// Ima li smisla brisati konacnu ocjenu kod ispisa sa predmeta!?
	// Ima, zato što bi u suprotnom student imao položen predmet koji nikada nije slušao

	// Zadace
	$lokacijazadaca="$conf_files_path/zadace/$predmet-$ag/$student/";

	$q90 = myquery("select z.id, pj.ekstenzija, z.attachment from zadaca as z, programskijezik as pj where z.predmet=$predmet and z.akademska_godina=$ag and z.programskijezik=pj.id");
	while ($r90 = mysql_fetch_row($q90)) {
		$q100 = myquery("select id,redni_broj,filename from zadatak where student=$student and zadaca=$r90[0]");
		while ($r100 = mysql_fetch_row($q100)) {

			// Fizicko brisanje zadace
			if ($r90[2]==1) { //attachment
				$the_file = "$lokacijazadaca$r90[0]/$r100[1]$r90[2]";
			} else {
				$the_file = "$lokacijazadaca$r90[0]/$r100[2]";
			}
			if (file_exists($the_file)) { 
				unlink($the_file);  
			}

			$q110 = myquery("delete from zadatakdiff where zadatak=$r100[0]");
		}
		$q120 = myquery("delete from zadatak where student=$student and zadaca=$r90[0]");
	}

	// Brisanje komponenti
	$q230 = myquery("delete from komponentebodovi where student=$student and predmet=$ponudakursa");

	// Ispis sa predmeta
	$q240 = myquery("delete from student_predmet where student=$student and predmet=$ponudakursa");

//	zamgerlog("studenta u$student ispisan sa predmeta pp$predmet", 4); // nivo 4: audit
// Logging treba raditi tamo gdje se funkcija poziva!

}


// Za upis studenta na labgrupu kucajte:
// $q = myquery("insert into student_labgrupa set student=$student, labgrupa=$labgrupa")
// Ne treba nista osim ovoga


// Upis studenta na predmet
// Parametar funkcije je ustvari ponudakursa

function upis_studenta_na_predmet($student,$ponudakursa) {
	// Da li je student već upisan na predmet?
	$q5 = myquery("SELECT COUNT(*) FROM student_predmet WHERE student=$student AND predmet=$ponudakursa");
	if (mysql_result($q5,0,0)>0) return;

	// Zapis u tabeli student_predmet
	$q10 = myquery("insert into student_predmet set student=$student, predmet=$ponudakursa");

	// Pronalazimo labgrupu "(Svi studenti)" i upisujemo studenta u nju
	$q20 = myquery("select l.id, pk.predmet, pk.akademska_godina from labgrupa as l, ponudakursa as pk where pk.id=$ponudakursa and pk.predmet=l.predmet and pk.akademska_godina=l.akademska_godina and l.virtualna=1");
	$labgrupa = mysql_result($q20,0,0); // mora postojati
	$predmet = mysql_result($q20,0,1); // treba nam za $q40
	$ag = mysql_result($q20,0,2); // treba nam za $q40
	
	$q30 = myquery("insert into student_labgrupa set student=$student, labgrupa=$labgrupa");

	// Potrebno je upisati max. bodova za sve komponente prisustva!
	$q40 = myquery("select k.id, k.maxbodova from komponenta as k, tippredmeta_komponenta as tpk, akademska_godina_predmet as agp where agp.predmet=$predmet and agp.tippredmeta=tpk.tippredmeta and agp.akademska_godina=$ag and tpk.komponenta=k.id and k.tipkomponente=3"); // tip komponente 3 = klasično prisustvo
	while ($r40 = mysql_fetch_row($q40)) {
		$q50 = myquery("insert into komponentebodovi set student=$student, predmet=$ponudakursa, komponenta=$r40[0], bodovi=$r40[1]");
	}
	
}



// Masovni unos podataka (koristi se u nastavnickim funkcijama)
// Vraca 1 u slucaju greske, 0 za ispravno
// Globalni niz $mass_rezultat sadrzi unesene podatke

function mass_input($ispis) {
	global $mass_rezultat,$userid;
	$mass_rezultat = array(); // brišemo niz
	$mass_rezultat['ime'] = array(); // sprječavamo upozorenja


	// Da li treba ispisivati akcije na ekranu ili ne?
	$f = $ispis;

	// Parametri
	$ponudakursa = intval($_REQUEST['ponudakursa']);
	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']); // akademska godina

	$redovi = explode("\n",$_POST['massinput']);

	// Format imena i prezimena:
	//   0 - Prezime[SEPARATOR]Ime
	//   1 - Ime[SEPARATOR]Prezime
	//   2 - Prezime Ime
	//   3 - Ime Prezime
	$format = intval($_REQUEST['format']);

	// Broj dodatnih kolona podataka (osim imena i prezimena)
	$brpodataka = intval($_REQUEST['brpodataka']);
	if ($_REQUEST['brpodataka']=='on') $brpodataka=1; //checkbox
	$kolona = $brpodataka+1;
	if ($format<2) $kolona++;

	// Separator: 0 = TAB, 1 = zarez, ...
	$separator = intval($_REQUEST['separator']);
	if ($separator==1) $sepchar=','; else $sepchar="\t";

	// Da li je dozvoljeno ponavljanje istog studenta? 1=da, sve ostalo=ne
	$duplikati = intval($_REQUEST['duplikati']);
	if ($duplikati!=1) $duplikati=0;

	// U slucaju duplikati=1, sta se desava sa ponovnim unosom?
	// 0=pise se preko starog, 1=rezultati su nizovi
	$visestruki = intval($_REQUEST['visestruki']);
	if ($visestruki!=1) $visestruki=0;


	// Update korisničkih preferenci kod masovnog unosa

	$q190 = myquery("select vrijednost from preference where korisnik=$userid and preferenca='mass-input-format'");
	if (mysql_num_rows($q190)<1) {
		$q191 = myquery("insert into preference set korisnik=$userid, preferenca='mass-input-format', vrijednost='$format'");
	} else if (mysql_result($q190,0,0)!=$format) {
		$q192 = myquery("update preference set vrijednost='$format' where korisnik=$userid and preferenca='mass-input-format'");
	}

	$q193 = myquery("select vrijednost from preference where korisnik=$userid and preferenca='mass-input-separator'");
	if (mysql_num_rows($q193)<1) {
		$q194 = myquery("insert into preference set korisnik=$userid, preferenca='mass-input-separator', vrijednost='$separator'");
	} else if (mysql_result($q193,0,0)!=$separator) {
		$q195 = myquery("update preference set vrijednost='$separator' where korisnik=$userid and preferenca='mass-input-separator'");
	}


	$greska=0;
	$prosli_idovi = array(); // za duplikate

	foreach ($redovi as $red) {
		$red = trim($red);
		if (strlen($red)<2) continue; // prazan red
		// popravljamo nbsp Unicode karakter
		$red = str_replace("¡", " ", $red);
		$red = str_replace(" ", " ", $red);
		$red = my_escape($red);

		$nred = explode($sepchar, $red, $kolona);

		// Parsiranje formata
		if ($format==0) {
			$prezime=$nred[0];
			$ime=$nred[1];
		} else if ($format==1) {
			$ime=$nred[0];
			$prezime=$nred[1];
		} else if ($format==2) {
			list($prezime,$ime) = explode(" ",$nred[0],2);
		} else if ($format==3) {
			list($ime,$prezime) = explode(" ",$nred[0],2);
		}
		else {
			niceerror("Nedozvoljen format"); // ovo je fatalna greska
			return 1;
		}

		// Fixevi za naša slova i trim
		$prezime = trim(malaslova($prezime));
		$ime = trim(malaslova($ime));


		// Provjera ispravnosti podataka

		// Da li korisnik postoji u bazi?
		$q10 = myquery("select id from osoba where ime like '$ime' and prezime like '$prezime'");
		if (mysql_num_rows($q10)<1) {
			if ($f)  {
				?><tr bgcolor="#FFE3DD"><td><?=$prezime?></td><td><?=$ime?></td><td>nepoznat student - da li ste dobro ukucali ime?</td></tr><?
			}
			$greska=1;
			continue;

		} else if (mysql_num_rows($q10)>1) {
			if ($ponudakursa>0) {
				// Postoji više studenata sa istim imenom i prezimenom
				// Biramo onog koji je upisan na ovu ponudukursa
				$q10 = myquery("select DISTINCT o.id from osoba as o, student_predmet as sp where o.ime like '$ime' and o.prezime like '$prezime' and o.id=sp.student and sp.predmet=$ponudakursa");
	
				if (mysql_num_rows($q10)<1) {
					if ($f) {
						?><tr bgcolor="#FFE3DD"><td><?=$prezime?></td><td><?=$ime?></td><td>nije upisan/a na ovaj predmet</td></tr><?
					}
					$greska=1;
					continue;
	
				} else if (mysql_num_rows($q10)>1) {
					// Na istom su predmetu!? wtf
					if ($f) {
						?><tr bgcolor="#FFE3DD"><td><?=$prezime?></td><td><?=$ime?></td><td>postoji više studenata sa ovim imenom i prezimenom; koristite pogled grupe</td></tr><?
					}
					$greska=1;
					continue;
				}

			} else if ($predmet>0 && $ag>0) {
				// Isto za predmet
				$q10 = myquery("select DISTINCT o.id from osoba as o, student_predmet as sp, ponudakursa as pk where o.ime like '$ime' and o.prezime like '$prezime' and o.id=sp.student and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
	
				if (mysql_num_rows($q10)<1) {
					if ($f) {
						?><tr bgcolor="#FFE3DD"><td><?=$prezime?></td><td><?=$ime?></td><td>nije upisan/a na ovaj predmet</td></tr><?
					}
					$greska=1;
					continue;
	
				} else if (mysql_num_rows($q10)>1) {
					// Na istom su predmetu!? wtf
					if ($f) {
						?><tr bgcolor="#FFE3DD"><td><?=$prezime?></td><td><?=$ime?></td><td>postoji više studenata sa ovim imenom i prezimenom; koristite pogled grupe</td></tr><?
					}
					$greska=1;
					continue;
				}

			} else {
				if ($f) {
					?><tr bgcolor="#FFE3DD"><td><?=$prezime?></td><td><?=$ime?></td><td>postoji više studenata sa ovim imenom i prezimenom; koristite pogled grupe</td></tr><?
				}
				$greska=1;
				continue;
			}
		}
		$student = mysql_result($q10,0,0);

		// Da li se ponavlja isti student?
		if ($duplikati==0) {
			// FIXME: zašto ne radi array_search?
			if (in_array($student,$prosli_idovi)) {
				if ($f) {
					?><tr bgcolor="#FFE3DD"><td><?=$prezime?></td><td><?=$ime?></td><td>ponavlja se</td></tr><?
				}
				$greska=1;
				continue;
			}
			array_push($prosli_idovi,$student);
		}

		// Da li je upisan na predmet?
		$q20=0;
		if ($ponudakursa>0) {
			$q20 = myquery("select count(*) from student_predmet where student=$student and predmet=$ponudakursa");
		} else if ($predmet>0 && $ag>0) {
			$q20 = myquery("select count(*) from student_predmet as sp, ponudakursa as pk where sp.student=$student and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
		}
		if ($q20 != 0) {
			if (mysql_result($q20,0,0)<1) {
				// Pokusacemo preskociti studente koji nemaju ocjenu
				if ($format==0 || $format==1) 
					$bodovi=$nred[2];
				else
					$bodovi=$nred[1];
				if (!preg_match("/\w/",$bodovi)) {
					if ($f)  {
						?><tr bgcolor="#EEEEEE"><td><?=$prezime?></td><td><?=$ime?></td><td>nepoznat student, nema ocjene - preskačem</td></tr><?
					}
				} else {
					if ($f) {
						?><tr bgcolor="#FFE3DD"><td><?=$prezime?></td><td><?=$ime?></td><td>nije upisan/a na ovaj predmet</td></tr><?
					}
					$greska=1;
				}
				continue;
			}
		}

		// Podaci su OK, punimo niz...
		$mass_rezultat['ime'][$student]=$ime;
		$mass_rezultat['prezime'][$student]=$prezime;
		for ($i=1; $i<=$brpodataka; $i++) {
			if ($duplikati==1 && $visestruki==1) {
				if (count($mass_rezultat["podatak$i"][$student])==0) $mass_rezultat["podatak$i"][$student]=array();
				array_push($mass_rezultat["podatak$i"][$student],$nred[$kolona-$brpodataka-1+$i]);
			} else
				$mass_rezultat["podatak$i"][$student]=$nred[$kolona-$brpodataka-1+$i];
		}
	}
	if ($f) {
		print "<br/>\n";
	}
	return $greska;
}



// Azurira "komponente" - sumarne bodove po komponentama ukupnog broja bodova za neki predmet i studenta
// parametar $predmet je ustvari ponudakursa

function update_komponente($student,$predmet,$komponenta=0) {
	// Ako nije navedena komponenta, racunaju se sve komponente

	// Brišemo podatke trenutno u bazi ako je komponenta=0
	if ($komponenta==0)
		$q5 = myquery("delete from komponentebodovi where student=$student and predmet=$predmet");

	// Glavni upit - spisak komponenti
	$dodaj="";
	if ($komponenta!=0) $dodaj="and k.id=$komponenta";
	$q10 = myquery("select k.id, k.tipkomponente, k.maxbodova, k.prolaz, k.opcija from komponenta as k, tippredmeta_komponenta as tpk, ponudakursa as pk, akademska_godina_predmet as agp where tpk.komponenta=k.id and tpk.tippredmeta=agp.tippredmeta and pk.id=$predmet and pk.predmet=agp.predmet and pk.akademska_godina=agp.akademska_godina $dodaj");

	while ($r10 = mysql_fetch_row($q10)) {
		$k=$r10[0];
		switch($r10[1]) { // tipkomponente

		case 1: // Ispit
			$prolaz = $r10[3];

			$q15 = myquery("delete from komponentebodovi where student=$student and predmet=$predmet and komponenta=$k");

			$q20 = myquery("select io.ocjena from ispit as i, ispitocjene as io, ponudakursa as pk where i.predmet=pk.predmet and i.akademska_godina=pk.akademska_godina and pk.id=$predmet and i.komponenta=$k and i.id=io.ispit and io.student=$student order by io.ocjena desc limit 1");
			// Ako nema ispita, komponenta ostaje obrisana
			if (mysql_num_rows($q20)<1) break;
			$bodovi=mysql_result($q20,0,0);

			$q25 = myquery("insert into komponentebodovi set student=$student, predmet=$predmet, komponenta=$k, bodovi=$bodovi");


			// Provjeravamo integralni
			$q30 = myquery("select k.id, k.opcija, k.prolaz from komponenta as k, tippredmeta_komponenta as tpk, ponudakursa as pk, akademska_godina_predmet as agp where tpk.komponenta=k.id and tpk.tippredmeta=agp.tippredmeta and pk.id=$predmet and pk.predmet=agp.predmet and pk.akademska_godina=agp.akademska_godina and k.tipkomponente=2 and k.opcija like '%$k%'");
			if (mysql_num_rows($q30)<1) break;
			$intk = mysql_result($q30,0,0);
			$intdijelovi = mysql_result($q30,0,1);
			$intprolaz = mysql_result($q30,0,2);

			// Integralni postoji - brisemo ga iz spiska komponenti
			$q35 = myquery("delete from komponentebodovi where student=$student and predmet=$predmet and komponenta=$intk");

			// Koliko bodova je na integralnom?
			$q40 = myquery("select io.ocjena from ispit as i, ispitocjene as io, ponudakursa as pk where i.predmet=pk.predmet and i.akademska_godina=pk.akademska_godina and pk.id=$predmet and i.komponenta=$intk and i.id=io.ispit and io.student=$student order by io.ocjena desc limit 1");
			if (mysql_num_rows($q40)<1) break;
			$intbodovi = mysql_result($q40,0,0);

			// Koliko bodova je osvojio na ostalim ispitima koji čine jedan 
			// integralni (npr. 1+2 znači da se integralni sastoji od 
			// parcijalnih ispita sa IDovima 1 i 2)
			$dijelovi = explode("+",$intdijelovi);
			$suma = $bodovi;
			$polozio=1; // Da li je polozio sve parcijalne ispite?
			if ($bodovi<$prolaz) $polozio=0;
			foreach ($dijelovi as $dio) {
				if ($dio==$k) continue; // ignorišemo aktuelnu komponentu
				$q45 = myquery("select prolaz from komponenta where id=$dio");
				$dioprolaz = mysql_result($q45,0,0);

				$q50 = myquery("select io.ocjena from ispit as i, ispitocjene as io, ponudakursa as pk where i.predmet=pk.predmet and i.akademska_godina=pk.akademska_godina and pk.id=$predmet and i.komponenta=$dio and i.id=io.ispit and io.student=$student order by io.ocjena desc limit 1");
				if (mysql_num_rows($q50)>0) {
					$diobodovi = mysql_result($q50,0,0);
					if ($diobodovi<$dioprolaz) $polozio=0;
					$suma += $diobodovi;
				} else $polozio=0;
			}

			// Integralni se uzima u obzir ako je osvojeno više bodova nego
			// suma svih parcijalnih, ili ako je položio integralni a pao
			// bilo koji od parcijalnih
			if ($suma<$intbodovi || ($polozio==0 && $intbodovi>$intprolaz)) {
				foreach ($dijelovi as $dio) {
					// Ovo ce ujedno obrisati upravo ubacenu komponentu
					// ali to vrijedi pojednostavljenja koda
					$q55 = myquery("delete from komponentebodovi where student=$student and predmet=$predmet and komponenta=$dio");
				}
				$q60 = myquery("insert into komponentebodovi set student=$student, predmet=$predmet, komponenta=$intk, bodovi=$intbodovi");
			}
			break;


		case 2: // Integralni ispit
			$prolaz = $r10[3];

			$q100 = myquery("delete from komponentebodovi where student=$student and predmet=$predmet and komponenta=$k");

			$q110 = myquery("select io.ocjena from ispit as i, ispitocjene as io, ponudakursa as pk where i.predmet=pk.predmet and i.akademska_godina=pk.akademska_godina and pk.id=$predmet and i.komponenta=$k and i.id=io.ispit and io.student=$student order by io.ocjena desc limit 1");
			if (mysql_num_rows($q110)<1) break;
			$bodovi=mysql_result($q110,0,0);

			// Provjeravamo da li dijelovi parcijalnog imaju vise bodova
			$dijelovi = explode("+",$r10[4]); // $r10[4] = opcija
			$suma = 0; $polozio=1;
			foreach ($dijelovi as $dio) {
				$q120 = myquery("select prolaz from komponenta where id=$dio");
				$dioprolaz = mysql_result($q120,0,0);
				$q130 = myquery("select io.ocjena from ispit as i, ispitocjene as io, ponudakursa as pk where i.predmet=pk.predmet and i.akademska_godina=pk.akademska_godina and pk.id=$predmet and i.komponenta=$dio and i.id=io.ispit and io.student=$student order by io.ocjena desc limit 1");
				if (mysql_num_rows($q130)>0) {
					$diobodovi = mysql_result($q130,0,0);
					if ($diobodovi<$dioprolaz) $polozio=0;
					$suma += $diobodovi;
				} else $polozio=0;
			}

			// Integralni se uzima u obzir ako je osvojeno više bodova nego
			// suma svih parcijalnih, ili ako je položio integralni a pao
			// bilo koji od parcijalnih
			if ($suma<$bodovi || ($polozio==0 && $bodovi>$prolaz)) {
				foreach ($dijelovi as $dio) {
					// Brisemo sve dijelove integralnog
					$q140 = myquery("delete from komponentebodovi where student=$student and predmet=$predmet and komponenta=$dio");
				}
				$q150 = myquery("insert into komponentebodovi set student=$student, predmet=$predmet, komponenta=$k, bodovi=$bodovi");
			}
			break;


		case 3: // Prisustvo
			$maxbodova = $r10[2];
			$minbodova = $r10[3];
			$maxodsustva = $r10[4];
			
			$q200 = myquery("select count(*) from cas as c, labgrupa as l, prisustvo as p, ponudakursa as pk where c.labgrupa=l.id and l.predmet=pk.predmet and l.akademska_godina=pk.akademska_godina and pk.id=$predmet and c.komponenta=$k and c.id=p.cas and p.student=$student and p.prisutan=0");
			$odsustva = mysql_result($q200,0,0);
			if ($maxodsustva == -1) { // Bodovi proporcionalni prisustvu
				$q205 = myquery("select count(*) from cas as c, labgrupa as l, prisustvo as p, ponudakursa as pk where c.labgrupa=l.id and l.predmet=pk.predmet and l.akademska_godina=pk.akademska_godina and pk.id=$predmet and c.komponenta=$k and c.id=p.cas and p.student=$student");
				$casova = mysql_result($q205,0,0);
				if ($casova == 0)
					$bodovi = $maxbodova;
				else
					$bodovi = $minbodova + round(($maxbodova - $minbodova) * (($casova-$odsustva) / $casova), 2 );
			
			} else if ($maxodsustva == -2) { // Paraproporcionalni sistem TP
				if ($odsustva <= 2)
					$bodovi = $maxbodova;
				else if ($odsustva <= 2 + ($maxbodova-$minbodova)/2)
					$bodovi = $maxbodova - ($odsustva-2)*2;
				else
					$bodovi = $minbodova;
			
			} else if ($odsustva > $maxodsustva)
				$bodovi=$minbodova;
			else
				$bodovi=$maxbodova;

			$q210 = myquery("delete from komponentebodovi where student=$student and predmet=$predmet and komponenta=$k");
			$q220 = myquery("insert into komponentebodovi set student=$student, predmet=$predmet, komponenta=$k, bodovi=$bodovi");
			break;


		case 4: // Zadace
			$bodovi = 0;

			$q70 = myquery("select z.id, z.zadataka from zadaca as z, ponudakursa as pk where pk.id=$predmet and z.komponenta=$k and z.predmet=pk.predmet and z.akademska_godina=pk.akademska_godina");
			while ($r70 = mysql_fetch_row($q70)) {
				$zadaca=$r70[0];
				$zadataka=$r70[1];

				for ($i=1; $i<=$zadataka; $i++) {
					$q80 = myquery("select bodova, status from zadatak where zadaca=$zadaca and redni_broj=$i and student=$student order by id desc limit 1");
					if (mysql_num_rows($q80)>0 && mysql_result($q80,0,1)==5) {
						// status=5 - pregledana
						$bodovi += mysql_result($q80,0,0);
					}
				}
			}

			if (mysql_num_rows($q70)>0) {
				$q90 = myquery("lock tables komponentebodovi write");
				$q91 = myquery("delete from komponentebodovi where student=$student and predmet=$predmet and komponenta=$k");
				$q92 = myquery("insert into komponentebodovi set student=$student, predmet=$predmet, komponenta=$k, bodovi=$bodovi");
				$q93 = myquery("unlock tables");
			}
			break;

		case 5: // Fiksne komponente
			// fiksne komponente se upisuju direktno u tabelu komponentebodovi
			break;
		}
	} // while ($r40...


/*
	// Brisanje starih komponenti
	$q5 = myquery("delete from komponentebodovi where student=$student and predmet=$predmet");


	///////////////
	//  ISPITI
	///////////////

	// Maksimalna ocjena na ispitima
	$max = array();
	$bilo = array();
	
	$q10 = myquery("select i.id, i.komponenta, io.ocjena, k.tipkomponente, k.opcija, k.prolaz from ispit as i, ispitocjene as io, komponenta as k where i.predmet=$predmet and i.id=io.ispit and io.student=$student and i.komponenta=k.id");
	while ($r10 = mysql_fetch_row($q10)) {
		$k=$r10[1];
		if (!in_array($k,$bilo)) {
			array_push($bilo,$k);
			$max[$k]=$r10[2];
			$tipkomponente[$k]=$r10[3];
			$opcija[$k]=$r10[4];
			$prolaz[$k]=$r10[5];
		} else if ($r10[2]>$max[$k]) $max[$k]=$r10[2];
	}

	// Obrada integralnih ispita
	foreach ($tipkomponente as $k=>$tip) {
		if ($tip==2) { // 2 = integralni
			$parcijalni = explode("+", $opcija[$k]);
			$suma=0;
			$pao=0;
			foreach ($parcijalni as $p) {
				$suma += $max[$p];
				if ($max[$p]<$prolaz[$p]) $pao=1;
			}
			// Uslov za koristenje integralnog umjesto parcijalnih
			if ($max[$k]>$suma || ($pao==1 && $max[$k]>$prolaz[$k])) {
				// Brisemo parcijalne
				foreach ($parcijalni as $p) {
					$max[$p]="/";
				}
				// Upisujemo integralni u komponentebodovi
				$q20 = myquery("insert into komponentebodovi set student=$student, predmet=$predmet, komponenta=$k, bodovi=".$max[$k]);
			}
		}
	}

	// Obrada parcijalnih ispita
	foreach ($tipkomponente as $k=>$tip) {
		// "/" znaci da je ovaj ispit zamijenjen integralnim
		if ($tip==1 && $max[$k]!="/") { // 1 = parcijalni
			$q30 = myquery("insert into komponentebodovi set student=$student, predmet=$predmet, komponenta=$k, bodovi=".$max[$k]);
		}
	}


	//////////////////////
	//  OSTALE KOMPONENTE
	//////////////////////

	$q40 = myquery("select k.id, k.opcija, k.tipkomponente, k.maxbodova, k.prolaz from komponenta as k, tippredmeta_komponenta as tpk, ponudakursa as pk where tpk.komponenta=k.id and tpk.tippredmeta=pk.tippredmeta and pk.id=$predmet and (k.tipkomponente!=1 and k.tipkomponente!=2)");*/

}


// Funkcija koja provjerava da li je student dao uslov za upis na sljedecu godinu studija, odnosno koliko predmeta nije položeno
// Vraća boolean vrijednost
// Globalni niz $zamger_predmeti_pao sadrži id-eve predmeta koji nisu položeni

function uslov($student, $ag=0) {
	global $zamger_predmeti_pao;
	$ima_uslov=false;

	// Odredjujemo studij i semestar
	if ($ag==0) {
		$q10 = myquery("select ss.studij, ss.semestar, ts.trajanje from student_studij as ss, studij as s, tipstudija as ts where ss.student=$student and ss.studij=s.id and s.tipstudija=ts.id order by ss.akademska_godina desc, ss.semestar desc limit 1");
		if (mysql_num_rows($q10)<1) 
			return true; // Nikad nije bio student, ima uslov za prvu godinu ;)
	} else {
		$q10 = myquery("select ss.studij, ss.semestar, ts.trajanje from student_studij as ss, studij as s, tipstudija as ts where ss.student=$student and ss.studij=s.id and s.tipstudija=ts.id and ss.akademska_godina=$ag order by ss.semestar desc limit 1");
		if (mysql_num_rows($q10)<1) 
			return false; // Nije bio student u datoj akademskoj godini
	}

	$studij = mysql_result($q10,0,0);
	$semestar = mysql_result($q10,0,1);
	if ($semestar%2==1) $semestar++; // zaokružujemo na parni semestar
	$studij_trajanje = mysql_result($q10,0,2);

	// Od predmeta koje je slušao, koliko je pao?
	$q20 = myquery("select distinct pk.predmet, p.ects, pk.semestar, pk.obavezan from ponudakursa as pk, student_predmet as sp, predmet as p where sp.student=$student and sp.predmet=pk.id and pk.semestar<=$semestar and pk.studij=$studij and pk.predmet=p.id order by pk.semestar");
	$obavezni_pao_ects=$obavezni_pao=$nize_godine=$ects_polozio=0;
	$zamger_predmeti_pao=array();
	while ($r20 = mysql_fetch_row($q20)) {
		$predmet = $r20[0];

		$ects = $r20[1];
		$predmet_semestar = $r20[2];
		$obavezan = $r20[3];

		$q30 = myquery("select count(*) from konacna_ocjena where student=$student and predmet=$predmet and ocjena>5");
		if (mysql_result($q30,0,0)<1) {
			array_push($zamger_predmeti_pao, $predmet);

			// Predmet se ne može prenijeti preko dvije godine
			if ($predmet_semestar<$semestar-1) $nize_godine++;

			// Ako je obavezan, situacija je jasna
			if ($obavezan) { 
				$obavezni_pao_ects+=$ects;
				$obavezni_pao++;

			// Za izborne možemo odrediti uslov samo preko ECTSa
			// pošto je tokom godina student mogao pokušavati razne izborne
			// predmete
			}
		} else
			$ects_polozio += $ects;
	}

	// USLOV ZA UPIS
	// Prema aktuelnom zakonu može se prenijeti tačno jedan predmet, bez obzira na ECTS
	// No, na sljedeći ciklus studija se ne može prenijeti ništa
	$ects_ukupno = $semestar*30;

	// 1. Završni semestar, mora očistiti sve
	if ($semestar==$studij_trajanje && $obavezni_pao==0 && $ects_polozio>=$ects_ukupno) {
		// Jedan semestar nosi 30 ECTSova
		$ima_uslov=true;

	// 2. Nije završni semestar, nedostaje jedan ili nijedan predmet (ali samo sa zadnje odslušane godine studija)
	} else if ($semestar<$studij_trajanje && $obavezni_pao<=1 && $nize_godine==0) {

		// 2A. Položeni svi obavezni predmeti. 
		// Da li nedostaje više od jednog izbornog? Izborni slotovi nose 4-6 ECTS
		if ($obavezni_pao==0 && $ects_polozio>$ects_ukupno-8) {
			$ima_uslov=true;

		// 2B. Nedostaje jedan obavezan predmet. Izbornih treba biti nula
		} else if ($obavezni_pao==1 && $ects_polozio+$obavezni_pao_ects>=$ects_ukupno) {
			$ima_uslov=true;
		}

	}

	return $ima_uslov;
}


// Funkcija koja kreira jednu ponudu kursa i dodaje sve ostalo što treba
function kreiraj_ponudu_kursa($predmet, $studij, $semestar, $ag, $obavezan, $ispis) {
	// Naziv predmeta nam treba za poruke
	if ($obavezan === true || $obavezan === 1) $obavezan=1; else $obavezan=0;
	$q60 = myquery("select naziv from predmet where id=$predmet");
	$naziv_predmeta = mysql_result($q60,0,0);

	// Da li već postoji slog u tabeli ponudakursa
	$q61 = myquery("select id from ponudakursa where predmet=$predmet and akademska_godina=$ag and studij=$studij and semestar=$semestar");
	if (mysql_result($q61,0,0)>0) {
		if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Već postoji slog u tabeli ponudakursa za $naziv_predmeta<br/>\n";
		$pkid = mysql_result($q61,0,0);

	} else {
		if ($obavezan==1) $tekst = "obavezan"; else $tekst = "izborni";
		if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Dodajem predmet $naziv_predmeta ($tekst)<br/>\n";
		else {
			$q63 = myquery("insert into ponudakursa set predmet=$predmet, studij=$studij, semestar=$semestar, obavezan=$obavezan, akademska_godina=$ag");
			$pkid = mysql_insert_id();

			// Kreiranje labgrupe "svi studenti"
			$q65 = myquery("select count(*) from labgrupa where predmet=$predmet and akademska_godina=$ag and virtualna=1");
			if (mysql_result($q65,0,0)==0)
				$q67 = myquery("insert into labgrupa set naziv='(Svi studenti)', predmet=$predmet, akademska_godina=$ag, virtualna=1");
		}
	}

	// Dodajem slog u akademska_godina_predmet
	// Uzimamo tip predmeta od prethodne godine
	$q80 = myquery("select akademska_godina, tippredmeta from akademska_godina_predmet where predmet=$predmet and akademska_godina<=$ag order by akademska_godina desc limit 1");
	if (mysql_num_rows($q80)==0) 
		$tippredmeta = 1; // 1 = ETF Bologna Standard - mora postojati
	else if (mysql_result($q80,0,0) == $ag) { // Već postoji
		if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Već postoji slog u akademska_godina_predmet<br>\n";
	} else {
		$tippredmeta = mysql_result($q80,0,1);
		if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Dodajem slog u akademska_godina_predmet<br>\n";
		else $q90 = myquery("insert into akademska_godina_predmet set akademska_godina=$ag, predmet=$predmet, tippredmeta=$tippredmeta");
	}

	// Kopiram podatak od prošle godine za moodle predmet id, ako ga ima
	$q100 = myquery("select akademska_godina, moodle_id from moodle_predmet_id where predmet=$predmet and akademska_godina<=$ag order by akademska_godina desc limit 1");
	// Ako ga nema, ne radimo ništa
	if (mysql_num_rows($q100)>0) {
		if (mysql_result($q100,0,0) == $ag) { // Već postoji
			if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Već postoji slog u moodle_predmet_id<br>\n";
		} else {
			$moodle_id = mysql_result($q100,0,1);
			if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Dodajem slog u moodle_predmet_id<br>\n";
			else $q110 = myquery("insert into moodle_predmet_id set akademska_godina=$ag, predmet=$predmet, moodle_id=$moodle_id");
		}
	} else {
		if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Nema podatka od prošle godine za moodle_predmet_id<br>\n";
	}


	// Kopiram podatak od prošle godine za angažman
	$q120 = myquery("select count(*) from angazman where predmet=$predmet and akademska_godina=$ag");
	if (mysql_result($q120,0,0) > 0) {
		if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Već postoje slogovi u tabeli angazman<br>\n";
	} else {
		if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Kopiram angažman od prošle godine<br>\n";
		else {
			$q130 = myquery("select osoba, angazman_status from angazman where predmet=$predmet and akademska_godina=".($ag-1));
			while ($r130 = mysql_fetch_row($q130))
				$q140 = myquery("insert into angazman set osoba=$r130[0], angazman_status=$r130[1], predmet=$predmet, akademska_godina=$ag");
		}
	}

	// Kopiram podatak od prošle godine za prava pristupa
	$q150 = myquery("select count(*) from nastavnik_predmet where predmet=$predmet and akademska_godina=$ag");
	if (mysql_result($q150,0,0) > 0) {
		if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Već postoje slogovi u tabeli nastavnik_predmet<br>\n";
	} else {
		if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Kopiram prava pristupa od prošle godine<br>\n";
		else {
			$q160 = myquery("select nastavnik, nivo_pristupa from nastavnik_predmet where predmet=$predmet and akademska_godina=".($ag-1));
			while ($r160 = mysql_fetch_row($q160))
				$q170 = myquery("insert into nastavnik_predmet set nastavnik=$r160[0], nivo_pristupa='$r160[1]', predmet=$predmet, akademska_godina=$ag");
		}
	}

	return $pkid;
}


// Funkcija provjerava da li ima slobodnog mjesta na predmetu kao izbornom ili u koliziji

// Tabela ugovoroucenju_kapacitet sadrzi polja:
//  - kapacitet (ukupan dozvoljeni broj studenata) 
//  - kapacitet_ekstra (dozvoljeni broj preko onih kojima je predmet obavezan)
//   TODO: kapacitet_drugi_odsjek (maksimalan broj studenata sa drugog odsjeka)

// Parametri:
//  - $predmet - ID predmeta koji student zeli izabrati
//  - $zagodinu - ID akademske godine
//  - $najnoviji_plan - ID NPP za koji gledamo da li je predmet obavezan 
//    (to se da zakljuciti iz parametra $zagodinu, ali bi potencijalno usporilo upite?)

// Povratna vrijednost: 0 - nema vise mjesta, 1 - ima jos mjesta

// TODO: studenti sa maticnog odsjeka koji biraju predmet kao izborni trebaju imati prednost u odnosu 
// na koliziju, ali trenutno ne vidim kako to izvesti a da nekome ne postane invalidan odabir predmeta

function provjeri_kapacitet($predmet, $zagodinu, $najnoviji_plan) {
	global $userid;
//	print "Provjeravam kapacitet $predmet za godinu $zagodinu<br>";
	// Provjera kapaciteta
	$q112 = myquery("SELECT kapacitet, kapacitet_ekstra FROM ugovoroucenju_kapacitet WHERE predmet=$predmet AND akademska_godina=$zagodinu");
	if (mysql_num_rows($q112)>0) {
		$kapacitet = mysql_result($q112,0,0);
		$kapacitet_ekstra = mysql_result($q112,0,0);
		
		// Koliko je studenata izabralo predmet kao izborni?
		$q113 = myquery("SELECT COUNT(*) FROM ugovoroucenju as uou, ugovoroucenju_izborni as uoi WHERE uou.akademska_godina=$zagodinu AND uou.student!=$userid AND uoi.ugovoroucenju=uou.id AND uoi.predmet=$predmet AND (SELECT COUNT(*) FROM konacna_ocjena AS ko WHERE ko.predmet=$predmet AND ko.ocjena>5 AND ko.student=uou.student)=0");
		$popunjeno = mysql_result($q113,0,0);
		
		// Koliko sluša na koliziju?
		$q114 = myquery("SELECT COUNT(*) FROM kolizija WHERE akademska_godina=$zagodinu AND predmet=$predmet AND student!=$userid");
		$popunjeno += mysql_result($q114,0,0);
		
		if ($kapacitet_ekstra > 0 && $popunjeno >= $kapacitet_ekstra)
			return 0;
		
		// Koliko studenata slusa predmet kao obavezan na svom studiju?
		$q115 = myquery("SELECT studij, semestar FROM plan_studija WHERE godina_vazenja=$najnoviji_plan AND predmet=$predmet AND obavezan=1");
		if (mysql_num_rows($q115)>0) {
			$q116 = myquery("SELECT COUNT(*) FROM ugovoroucenju WHERE akademska_godina=$zagodinu AND studij=".mysql_result($q115,0,0)." AND semestar=".mysql_result($q115,0,1));
			$popunjeno += mysql_result($q116,0,0);
		}
//		print "popunjeno $popunjeno<br>";
		
		if ($kapacitet > 0 && $popunjeno >= $kapacitet) 
			return 0;
	}
	return 1;
}

?>
