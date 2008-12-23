<?

// LIB/MANIP - manipulacije bazom podataka (ispis studenta sa predmeta i sl.)

// v3.9.1.0 (2008/02/28) + Nova biblioteka, radi centralizovanja operacija koje vrse kompleksne manipulacije sa bazom
// v3.9.1.1 (2008/04/10) + Typo u update_komponente, dio za zadace; ne racunamo prisustvo ni zadace ako nije registrovan nijedan cas / zadaca
// v3.9.1.2 (2008/04/14) + Ponistavam zadnju izmjenu - ako nije odrzan nijedan cas treba dati max bodova za prisustvo 
// v3.9.1.3 (2008/04/24) + mass_input(): (!$f) zamijenjeno sa ($f) (provjeriti sve module!); dodano trimovanje imena i prezimena i ljepse upozorenje kod gresaka; ako student nije na predmetu a nema bodova, to nije greska
// v3.9.1.4 (2008/05/16) + Optimizovan update_komponente() tako da se moze zadati bilo koja komponenta, ukinuto update_komponente_prisustvo
// v3.9.1.5 (2008/08/28) + Tabela osoba umjesto auth; omoguceno koristenje masovnog unosa kada nije definisan predmet
// v3.9.1.6 (2008/11/24) + mass_input(): zamijeni Unicode karakter "non-breakable space" razmakom


// NOTE:  Pretpostavka je da su podaci legalni i da je baza konzistentna




// Funkcija koja ispisuje studenta iz labgrupe, brisuci sve relevantne podatke
// (prisustvo, komentari)
// Dozvoljena je labgrupa 0 (brisu se podaci u grupi "Svi studenti")
// Ne zaboravite updatovati komponente ako treba (prisustvo je promijenjeno)!

function ispis_studenta_sa_labgrupe($student,$predmet,$labgrupa) {
	// Prisustvo
	$q10 = myquery("select id from cas where predmet=$predmet and labgrupa=$labgrupa");
	while ($r10 = mysql_fetch_row($q10)) {
		$q20 = myquery("delete from prisustvo where student=$student and cas=$r10[0]");
	}
	// Komentari
	$q20 = myquery("delete from komentar where student=$student and predmet=$predmet and labgrupa=$labgrupa");

	// Ispis iz labgrupe
	if ($labgrupa>0) $q30 = myquery("delete from student_labgrupa where student=$student and labgrupa=$labgrupa");
}


// Funkcija koja ispisuje studenta sa predmeta, brisuci sve relevantne podatke 
// (ispis sa svih labgrupa, ispiti, konacna ocjena, komponente, zadace)

function ispis_studenta_sa_predmeta($student,$predmet) {
	logthis("Ispis studenta $stud_id sa predmeta $predmet_id (labgrupa $labgrupa)");

	global $conf_files_path;

	// Odredjivanje labgrupa ciji je student eventualno clan
	$q40 = myquery("select sl.labgrupa from student_labgrupa as sl,labgrupa where sl.student=$student and sl.labgrupa=labgrupa.id and labgrupa.predmet=$predmet");
	while ($r40 = mysql_fetch_row($q40)) {
		ispis_studenta_sa_labgrupe($student,$predmet,$r40[0]);
	}
	// Brisemo i nultu labgrupu ("svi studenti")
	ispis_studenta_sa_labgrupe($student,$predmet,0);

	// Ocjene na ispitima
	$q50 = myquery("select id from ispit where predmet=$predmet");
	while ($r50 = mysql_fetch_row($q50)) {
		$q60 = myquery("delete from ispitocjene where student=$student and ispit=$r50[0]");
	}

	// Konacne ocjene
	$q70 = myquery("delete from konacna_ocjena where student=$student and predmet=$predmet");

	// Zadace
	$lokacijazadaca="$conf_files_path/zadace/$predmet/$student/";

	$q90 = myquery("select z.id, pj.ekstenzija, z.attachment from zadaca as z, programskijezik as pj where z.predmet=$predmet and z.programskijezik=pj.id");
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
	$q230 = myquery("delete from komponentebodovi where student=$student and predmet=$predmet");

	// Ispis sa predmeta
	$q240 = myquery("delete from student_predmet where student=$student and predmet=$predmet");

}




// Masovni unos podataka (koristi se u nastavnickim funkcijama)
// Vraca 1 u slucaju greske, 0 za ispravno
// Globalni niz $mass_rezultat sadrzi unesene podatke

function mass_input($ispis) {
	global $mass_rezultat,$userid;
	$mass_rezultat=array(); // brisemo niz


	// Da li treba ispisivati akcije na ekranu ili ne?
	$f = $ispis;

	$predmet = intval($_REQUEST['predmet']);
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
			if ($f) print "-- GREŠKA! Prezime: '$prezime'. Ime: '$ime'. Nepoznat student! Da li ste dobro ukucali ime?<br/>";
			$greska=1;
			continue;

		} else if (mysql_num_rows($q10)>1) {
			if ($predmet>0) {
				// Postoji više studenata sa istim imenom i prezimenom
				// Biramo onog koji je upisan na ovaj predmet
				$q10 = myquery("select DISTINCT o.id from osoba as o, student_predmet as sp where o.ime like '$ime' and o.prezime like '$prezime' and o.id=sp.student and sp.predmet=$predmet");
	
				if (mysql_num_rows($q10)<1) {
					if ($f) print "-- GREŠKA! Student '$prezime $ime' nije upisan na ovaj predmet<br/>";
					$greska=1;
					continue;
	
				} else if (mysql_num_rows($q10)>1) {
					// Na istom su predmetu!? wtf
					if ($f) print "-- GREŠKA! Postoji više studenata koji se zovu '$prezime $ime' na ovom predmetu. Kontaktirajte administratora.<br/>";
					$greska=1;
					continue;
				}
			} else {
				if ($f) print "-- GREŠKA! Postoji više studenata koji se zovu '$prezime $ime'. Kontaktirajte administratora.<br/>";
				$greska=1;
				continue;
			}
		}
		$student = mysql_result($q10,0,0);

		// Da li se ponavlja isti student?
		if ($duplikati==0) {
			// FIXME: zašto ne radi array_search?
			if (in_array($student,$prosli_idovi)) {
				if ($f) print "-- GREŠKA! Student '$prezime $ime' se ponavlja!<br/>";
				$greska=1;
				continue;
			}
			array_push($prosli_idovi,$student);
		}

		// Da li je upisan na predmet?
		if ($predmet>0) {
			$q20 = myquery("select count(*) from student_predmet where student=$student and predmet=$predmet");
			if (mysql_result($q20,0,0)<1) {
				// Pokusacemo preskociti studente koji nemaju ocjenu
				if ($format==0 || $format==1) 
					$bodovi=$nred[2];
				else
					$bodovi=$nred[1];
				if (!preg_match("/\w/",$bodovi)) {
					if ($f) print "Student '$prezime $ime' nije upisan na ovaj predmet, ali nema ni broj bodova ($bodova) - preskačem.<br/>\n";
				} else {
					if ($f) print "-- GREŠKA! Student '$prezime $ime' ($student) nije upisan na ovaj predmet<br/>\n";
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
				array_push($mass_rezultat["podatak$i"][$student],$nred[$kolona-$i]);
			} else
				$mass_rezultat["podatak$i"][$student]=$nred[$kolona-$i];
		}
	}
	if ($f) {
		print "<br/>\n";
		if ($greska==1) print "Da li ste izabrali ispravan format? \"Prezime[TAB]Ime\" vs. \"Prezime Ime\".<br/><br/>";
	}
	return $greska;
}



// Azurira "komponente" - sumarne bodove po komponentama ukupnog broja bodova za neki predmet i studenta

function update_komponente($student,$predmet,$komponenta=0) {
	// Ako nije navedena komponenta, racunaju se sve komponente

	// Glavni upit - spisak komponenti
	$dodaj="";
	if ($komponenta!=0) $dodaj="and k.id=$komponenta";
	$q10 = myquery("select k.id, k.tipkomponente, k.maxbodova, k.prolaz, k.opcija from komponenta as k, tippredmeta_komponenta as tpk, ponudakursa as pk where tpk.komponenta=k.id and tpk.tippredmeta=pk.tippredmeta and pk.id=$predmet $dodaj");

	while ($r10 = mysql_fetch_row($q10)) {
		$k=$r10[0];
		switch($r10[1]) { // tipkomponente

		case 1: // Ispit
			$prolaz = $r10[3];

			$q15 = myquery("delete from komponentebodovi where student=$student and predmet=$predmet and komponenta=$k");

			$q20 = myquery("select io.ocjena from ispit as i, ispitocjene as io where i.predmet=$predmet and i.komponenta=$k and i.id=io.ispit and io.student=$student order by io.ocjena desc limit 1");
			// Ako nema ispita, komponenta ostaje obrisana
			if (mysql_num_rows($q20)<1) break; 
			$bodovi=mysql_result($q20,0,0);

			$q25 = myquery("insert into komponentebodovi set student=$student, predmet=$predmet, komponenta=$k, bodovi=$bodovi");


			// Provjeravamo integralni
			$q30 = myquery("select k.id, k.opcija, k.prolaz from komponenta as k, tippredmeta_komponenta as tpk, ponudakursa as pk where tpk.komponenta=k.id and tpk.tippredmeta=pk.tippredmeta and pk.id=$predmet and k.tipkomponente=2 and k.opcija like '%$k%'");
			if (mysql_num_rows($q30)<1) break;
			$intk = mysql_result($q30,0,0);
			$intdijelovi = mysql_result($q30,0,1);
			$intprolaz = mysql_result($q30,0,2);

			// Integralni postoji - brisemo ga iz spiska komponenti
			$q35 = myquery("delete from komponentebodovi where student=$student and predmet=$predmet and komponenta=$intk");

			// Koliko bodova je na integralnom?
			$q40 = myquery("select io.ocjena from ispit as i, ispitocjene as io where i.predmet=$predmet and i.komponenta=$intk and i.id=io.ispit and io.student=$student order by io.ocjena desc limit 1");
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

				$q50 = myquery("select io.ocjena from ispit as i, ispitocjene as io where i.predmet=$predmet and i.komponenta=$dio and i.id=io.ispit and io.student=$student order by io.ocjena desc limit 1");
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

			$q110 = myquery("select io.ocjena from ispit as i, ispitocjene as io where i.predmet=$predmet and i.komponenta=$k and i.id=io.ispit and io.student=$student order by io.ocjena desc limit 1");
			if (mysql_num_rows($q110)<1) break;
			$bodovi=mysql_result($q110,0,0);

			// Provjeravamo da li dijelovi parcijalnog imaju vise bodova
			$dijelovi = explode("+",$r10[4]); // $r10[4] = opcija
			$suma = 0; $polozio=1;
			foreach ($dijelovi as $dio) {
				$q120 = myquery("select prolaz from komponenta where id=$dio");
				$dioprolaz = mysql_result($q120,0,0);
				$q130 = myquery("select io.ocjena from ispit as i, ispitocjene as io where i.predmet=$predmet and i.komponenta=$dio and i.id=io.ispit and io.student=$student order by io.ocjena desc limit 1");
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
			
			$q200 = myquery("select count(*) from cas, prisustvo where cas.predmet=$predmet and cas.komponenta=$k and cas.id=prisustvo.cas and prisustvo.student=$student and prisustvo.prisutan=0");
			if (mysql_result($q200,0,0)>$maxodsustva)
				$bodovi=$minbodova;
			else
				$bodovi=$maxbodova;

			$q210 = myquery("delete from komponentebodovi where student=$student and predmet=$predmet and komponenta=$k");
			$q220 = myquery("insert into komponentebodovi set student=$student, predmet=$predmet, komponenta=$k, bodovi=$bodovi");
			break;


		case 4: // Zadace
			$bodovi = 0;
			$q230 = myquery("delete from komponentebodovi where student=$student and predmet=$predmet and komponenta=$k");

			$q70 = myquery("select id, zadataka from zadaca where predmet=$predmet and komponenta=$k");
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

			if (mysql_num_rows($q70)>0)
				$q90 = myquery("insert into komponentebodovi set student=$student, predmet=$predmet, komponenta=$k, bodovi=$bodovi");
			break;

		case 5: // Fiksne komponente
			// TODO!
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


?>