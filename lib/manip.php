<?

// LIB/MANIP - manipulacije bazom podataka (ispis studenta sa predmeta i sl.)


// NOTE:  Pretpostavka je da su podaci legalni i da je baza konzistentna




// Funkcija koja ispisuje studenta iz labgrupe, brisuci sve relevantne podatke
// (prisustvo, komentari)
// Ne zaboravite updatovati komponente ako treba (prisustvo je promijenjeno)!

function ispis_studenta_sa_labgrupe($student,$labgrupa) {
	// Prisustvo
	$q10 = db_query("select id from cas where labgrupa=$labgrupa");
	while ($r10 = db_fetch_row($q10)) {
		$q20 = db_query("delete from prisustvo where student=$student and cas=$r10[0]");
	}
	// Komentari
	$q20 = db_query("delete from komentar where student=$student and labgrupa=$labgrupa");

	// Ispis iz labgrupe
	if ($labgrupa>0) $q30 = db_query("delete from student_labgrupa where student=$student and labgrupa=$labgrupa");
}


// Funkcija koja ispisuje studenta sa predmeta, brisuci sve relevantne podatke 
// (ispis sa svih labgrupa, ispiti, konacna ocjena, komponente, zadace)

function ispis_studenta_sa_predmeta($student,$predmet,$ag) {
// Ovo bi se dalo optimizovati
	global $conf_files_path;

	// Odredjujem ponudukursa sto je potrebno za naredna dva upita
	$q225 = db_query("select sp.predmet from student_predmet as sp, ponudakursa as pk where sp.student=$student and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
	if (db_num_rows($q225) == 0) {
		biguglyerror("Student nije upisan na odabrani predmet");
		return;
	}
	$ponudakursa = db_result($q225,0,0);

	// Odredjivanje labgrupa ciji je student eventualno clan
	$q40 = db_query("select sl.labgrupa from student_labgrupa as sl,labgrupa as l where sl.student=$student and sl.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag");
	while ($r40 = db_fetch_row($q40)) {
		ispis_studenta_sa_labgrupe($student,$r40[0]);
	}

	// Ocjene na ispitima
	$q50 = db_query("select id from ispit where predmet=$predmet and akademska_godina=$ag");
	while ($r50 = db_fetch_row($q50)) {
		$q60 = db_query("delete from ispitocjene where student=$student and ispit=$r50[0]");
	}

	// Konacne ocjene
	$q70 = db_query("delete from konacna_ocjena where student=$student and predmet=$predmet and akademska_godina=$ag");
	// Ima li smisla brisati konacnu ocjenu kod ispisa sa predmeta!?
	// Ima, zato što bi u suprotnom student imao položen predmet koji nikada nije slušao

	// Zadace
	$lokacijazadaca="$conf_files_path/zadace/$predmet-$ag/$student/";

	$q90 = db_query("select z.id, pj.ekstenzija, z.attachment from zadaca as z, programskijezik as pj where z.predmet=$predmet and z.akademska_godina=$ag and z.programskijezik=pj.id");
	while ($r90 = db_fetch_row($q90)) {
		$q100 = db_query("select id,redni_broj,filename from zadatak where student=$student and zadaca=$r90[0]");
		while ($r100 = db_fetch_row($q100)) {

			// Fizicko brisanje zadace
			if ($r90[2]==1) { //attachment
				$the_file = "$lokacijazadaca$r90[0]/$r100[1]$r90[2]";
			} else {
				$the_file = "$lokacijazadaca$r90[0]/$r100[2]";
			}
			if (file_exists($the_file)) { 
				unlink($the_file);  
			}

			$q110 = db_query("delete from zadatakdiff where zadatak=$r100[0]");
		}
		$q120 = db_query("delete from zadatak where student=$student and zadaca=$r90[0]");
	}

	// Brisanje komponenti
	$q230 = db_query("delete from komponentebodovi where student=$student and predmet=$ponudakursa");

	// Ispis sa predmeta
	$q240 = db_query("delete from student_predmet where student=$student and predmet=$ponudakursa");

//	zamgerlog("studenta u$student ispisan sa predmeta pp$predmet", 4); // nivo 4: audit
// Logging treba raditi tamo gdje se funkcija poziva!

}


// Za upis studenta na labgrupu kucajte:
// $q = db_query("insert into student_labgrupa set student=$student, labgrupa=$labgrupa")
// Ne treba nista osim ovoga


// Upis studenta na predmet
// Parametar funkcije je ustvari ponudakursa

function upis_studenta_na_predmet($student,$ponudakursa) {
	// Da li je student već upisan na predmet?
	$q5 = db_query("SELECT COUNT(*) FROM student_predmet WHERE student=$student AND predmet=$ponudakursa");
	if (db_result($q5,0,0)>0) return;

	// Zapis u tabeli student_predmet
	$q10 = db_query("insert into student_predmet set student=$student, predmet=$ponudakursa");

	// Pronalazimo labgrupu "(Svi studenti)" i upisujemo studenta u nju
	$q20 = db_query("select l.id, pk.predmet, pk.akademska_godina from labgrupa as l, ponudakursa as pk where pk.id=$ponudakursa and pk.predmet=l.predmet and pk.akademska_godina=l.akademska_godina and l.virtualna=1");
	$labgrupa = db_result($q20,0,0); // mora postojati
	$predmet = db_result($q20,0,1); // treba nam za $q40
	$ag = db_result($q20,0,2); // treba nam za $q40
	
	$q30 = db_query("insert into student_labgrupa set student=$student, labgrupa=$labgrupa");

	// Potrebno je upisati max. bodova za sve komponente prisustva!
	$q40 = db_query("select k.id, k.maxbodova from komponenta as k, tippredmeta_komponenta as tpk, akademska_godina_predmet as agp where agp.predmet=$predmet and agp.tippredmeta=tpk.tippredmeta and agp.akademska_godina=$ag and tpk.komponenta=k.id and k.tipkomponente=3"); // tip komponente 3 = klasično prisustvo
	while ($r40 = db_fetch_row($q40)) {
		$q50 = db_query("insert into komponentebodovi set student=$student, predmet=$ponudakursa, komponenta=$r40[0], bodovi=$r40[1]");
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

	$q190 = db_query("select vrijednost from preference where korisnik=$userid and preferenca='mass-input-format'");
	if (db_num_rows($q190)<1) {
		$q191 = db_query("insert into preference set korisnik=$userid, preferenca='mass-input-format', vrijednost='$format'");
	} else if (db_result($q190,0,0)!=$format) {
		$q192 = db_query("update preference set vrijednost='$format' where korisnik=$userid and preferenca='mass-input-format'");
	}

	$q193 = db_query("select vrijednost from preference where korisnik=$userid and preferenca='mass-input-separator'");
	if (db_num_rows($q193)<1) {
		$q194 = db_query("insert into preference set korisnik=$userid, preferenca='mass-input-separator', vrijednost='$separator'");
	} else if (db_result($q193,0,0)!=$separator) {
		$q195 = db_query("update preference set vrijednost='$separator' where korisnik=$userid and preferenca='mass-input-separator'");
	}


	$greska=0;
	$prosli_idovi = array(); // za duplikate

	foreach ($redovi as $red) {
		$red = trim($red);
		if (strlen($red)<2) continue; // prazan red
		// popravljamo nbsp Unicode karakter
		$red = str_replace("¡", " ", $red);
		$red = str_replace(" ", " ", $red);
		$red = db_escape($red);

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
		$q10 = db_query("select id from osoba where ime like '$ime' and prezime like '$prezime'");
		if (db_num_rows($q10)<1) {
			if ($f)  {
				?><tr bgcolor="#FFE3DD"><td><?=$prezime?></td><td><?=$ime?></td><td>nepoznat student - da li ste dobro ukucali ime?</td></tr><?
			}
			$greska=1;
			continue;

		} else if (db_num_rows($q10)>1) {
			if ($ponudakursa>0) {
				// Postoji više studenata sa istim imenom i prezimenom
				// Biramo onog koji je upisan na ovu ponudukursa
				$q10 = db_query("select DISTINCT o.id from osoba as o, student_predmet as sp where o.ime like '$ime' and o.prezime like '$prezime' and o.id=sp.student and sp.predmet=$ponudakursa");
	
				if (db_num_rows($q10)<1) {
					if ($f) {
						?><tr bgcolor="#FFE3DD"><td><?=$prezime?></td><td><?=$ime?></td><td>nije upisan/a na ovaj predmet</td></tr><?
					}
					$greska=1;
					continue;
	
				} else if (db_num_rows($q10)>1) {
					// Na istom su predmetu!? wtf
					if ($f) {
						?><tr bgcolor="#FFE3DD"><td><?=$prezime?></td><td><?=$ime?></td><td>postoji više studenata sa ovim imenom i prezimenom; koristite pogled grupe</td></tr><?
					}
					$greska=1;
					continue;
				}

			} else if ($predmet>0 && $ag>0) {
				// Isto za predmet
				$q10 = db_query("select DISTINCT o.id from osoba as o, student_predmet as sp, ponudakursa as pk where o.ime like '$ime' and o.prezime like '$prezime' and o.id=sp.student and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
	
				if (db_num_rows($q10)<1) {
					if ($f) {
						?><tr bgcolor="#FFE3DD"><td><?=$prezime?></td><td><?=$ime?></td><td>nije upisan/a na ovaj predmet</td></tr><?
					}
					$greska=1;
					continue;
	
				} else if (db_num_rows($q10)>1) {
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
		$student = db_result($q10,0,0);

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
			$q20 = db_query("select count(*) from student_predmet where student=$student and predmet=$ponudakursa");
		} else if ($predmet>0 && $ag>0) {
			$q20 = db_query("select count(*) from student_predmet as sp, ponudakursa as pk where sp.student=$student and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
		}
		if ($q20 != 0) {
			if (db_result($q20,0,0)<1) {
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
		$q5 = db_query("delete from komponentebodovi where student=$student and predmet=$predmet");

	// Glavni upit - spisak komponenti
	$dodaj="";
	if ($komponenta!=0) $dodaj="and k.id=$komponenta";
	$q10 = db_query("select k.id, k.tipkomponente, k.maxbodova, k.prolaz, k.opcija from komponenta as k, tippredmeta_komponenta as tpk, ponudakursa as pk, akademska_godina_predmet as agp where tpk.komponenta=k.id and tpk.tippredmeta=agp.tippredmeta and pk.id=$predmet and pk.predmet=agp.predmet and pk.akademska_godina=agp.akademska_godina $dodaj");

	while ($r10 = db_fetch_row($q10)) {
		$k=$r10[0];
		switch($r10[1]) { // tipkomponente

		case 1: // Ispit
			$prolaz = $r10[3];

			$q15 = db_query("delete from komponentebodovi where student=$student and predmet=$predmet and komponenta=$k");

			$q20 = db_query("select io.ocjena from ispit as i, ispitocjene as io, ponudakursa as pk where i.predmet=pk.predmet and i.akademska_godina=pk.akademska_godina and pk.id=$predmet and i.komponenta=$k and i.id=io.ispit and io.student=$student order by io.ocjena desc limit 1");
			// Ako nema ispita, komponenta ostaje obrisana
			if (db_num_rows($q20)<1) break;
			$bodovi=db_result($q20,0,0);

			$q25 = db_query("insert into komponentebodovi set student=$student, predmet=$predmet, komponenta=$k, bodovi=$bodovi");


			// Provjeravamo integralni
			$q30 = db_query("select k.id, k.opcija, k.prolaz from komponenta as k, tippredmeta_komponenta as tpk, ponudakursa as pk, akademska_godina_predmet as agp where tpk.komponenta=k.id and tpk.tippredmeta=agp.tippredmeta and pk.id=$predmet and pk.predmet=agp.predmet and pk.akademska_godina=agp.akademska_godina and k.tipkomponente=2 and k.opcija like '%$k%'");
			if (db_num_rows($q30)<1) break;
			$intk = db_result($q30,0,0);
			$intdijelovi = db_result($q30,0,1);
			$intprolaz = db_result($q30,0,2);

			// Integralni postoji - brisemo ga iz spiska komponenti
			$q35 = db_query("delete from komponentebodovi where student=$student and predmet=$predmet and komponenta=$intk");

			// Koliko bodova je na integralnom?
			$q40 = db_query("select io.ocjena from ispit as i, ispitocjene as io, ponudakursa as pk where i.predmet=pk.predmet and i.akademska_godina=pk.akademska_godina and pk.id=$predmet and i.komponenta=$intk and i.id=io.ispit and io.student=$student order by io.ocjena desc limit 1");
			if (db_num_rows($q40)<1) break;
			$intbodovi = db_result($q40,0,0);

			// Koliko bodova je osvojio na ostalim ispitima koji čine jedan 
			// integralni (npr. 1+2 znači da se integralni sastoji od 
			// parcijalnih ispita sa IDovima 1 i 2)
			$dijelovi = explode("+",$intdijelovi);
			$suma = $bodovi;
			$polozio=1; // Da li je polozio sve parcijalne ispite?
			if ($bodovi<$prolaz) $polozio=0;
			foreach ($dijelovi as $dio) {
				if ($dio==$k) continue; // ignorišemo aktuelnu komponentu
				$q45 = db_query("select prolaz from komponenta where id=$dio");
				$dioprolaz = db_result($q45,0,0);

				$q50 = db_query("select io.ocjena from ispit as i, ispitocjene as io, ponudakursa as pk where i.predmet=pk.predmet and i.akademska_godina=pk.akademska_godina and pk.id=$predmet and i.komponenta=$dio and i.id=io.ispit and io.student=$student order by io.ocjena desc limit 1");
				if (db_num_rows($q50)>0) {
					$diobodovi = db_result($q50,0,0);
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
					$q55 = db_query("delete from komponentebodovi where student=$student and predmet=$predmet and komponenta=$dio");
				}
				$q60 = db_query("insert into komponentebodovi set student=$student, predmet=$predmet, komponenta=$intk, bodovi=$intbodovi");
			}
			break;


		case 2: // Integralni ispit
			$prolaz = $r10[3];

			$q100 = db_query("delete from komponentebodovi where student=$student and predmet=$predmet and komponenta=$k");

			$q110 = db_query("select io.ocjena from ispit as i, ispitocjene as io, ponudakursa as pk where i.predmet=pk.predmet and i.akademska_godina=pk.akademska_godina and pk.id=$predmet and i.komponenta=$k and i.id=io.ispit and io.student=$student order by io.ocjena desc limit 1");
			if (db_num_rows($q110)<1) break;
			$bodovi=db_result($q110,0,0);

			// Provjeravamo da li dijelovi parcijalnog imaju vise bodova
			$dijelovi = explode("+",$r10[4]); // $r10[4] = opcija
			$suma = 0; $polozio=1;
			foreach ($dijelovi as $dio) {
				$q120 = db_query("select prolaz from komponenta where id=$dio");
				$dioprolaz = db_result($q120,0,0);
				$q130 = db_query("select io.ocjena from ispit as i, ispitocjene as io, ponudakursa as pk where i.predmet=pk.predmet and i.akademska_godina=pk.akademska_godina and pk.id=$predmet and i.komponenta=$dio and i.id=io.ispit and io.student=$student order by io.ocjena desc limit 1");
				if (db_num_rows($q130)>0) {
					$diobodovi = db_result($q130,0,0);
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
					$q140 = db_query("delete from komponentebodovi where student=$student and predmet=$predmet and komponenta=$dio");
				}
				$q150 = db_query("insert into komponentebodovi set student=$student, predmet=$predmet, komponenta=$k, bodovi=$bodovi");
			}
			break;


		case 3: // Prisustvo
			$maxbodova = $r10[2];
			$minbodova = $r10[3];
			$maxodsustva = $r10[4];
			
			$q200 = db_query("select count(*) from cas as c, labgrupa as l, prisustvo as p, ponudakursa as pk where c.labgrupa=l.id and l.predmet=pk.predmet and l.akademska_godina=pk.akademska_godina and pk.id=$predmet and c.komponenta=$k and c.id=p.cas and p.student=$student and p.prisutan=0");
			$odsustva = db_result($q200,0,0);
			if ($maxodsustva == -1) { // Bodovi proporcionalni prisustvu
				$q205 = db_query("select count(*) from cas as c, labgrupa as l, prisustvo as p, ponudakursa as pk where c.labgrupa=l.id and l.predmet=pk.predmet and l.akademska_godina=pk.akademska_godina and pk.id=$predmet and c.komponenta=$k and c.id=p.cas and p.student=$student");
				$casova = db_result($q205,0,0);
				if ($casova == 0)
					$bodovi = $maxbodova;
				else
					$bodovi = $minbodova + round(($maxbodova - $minbodova) * (($casova-$odsustva) / $casova), 2 );
			
			} else if ($maxodsustva == -2) { // Paraproporcionalni sistem TP
			// TODO: svo prisustvo se može generalizovati na ovaj sistem, pa tako treba i uraditi
				if ($odsustva <= 2)
					$bodovi = $maxbodova;
				else if ($odsustva <= 2 + ($maxbodova-$minbodova)/2)
					$bodovi = $maxbodova - ($odsustva-2)*2;
				else
					$bodovi = $minbodova;

			// Uobičajeni princip
			} else if ($odsustva > $maxodsustva)
				$bodovi=$minbodova;
			else
				$bodovi=$maxbodova;

			$q210 = db_query("delete from komponentebodovi where student=$student and predmet=$predmet and komponenta=$k");
			$q220 = db_query("insert into komponentebodovi set student=$student, predmet=$predmet, komponenta=$k, bodovi=$bodovi");
			break;


		case 4: // Zadace
			$bodovi = 0;

			$q70 = db_query("select z.id, z.zadataka from zadaca as z, ponudakursa as pk where pk.id=$predmet and z.komponenta=$k and z.predmet=pk.predmet and z.akademska_godina=pk.akademska_godina");
			while ($r70 = db_fetch_row($q70)) {
				$zadaca=$r70[0];
				$zadataka=$r70[1];

				for ($i=1; $i<=$zadataka; $i++) {
					$q80 = db_query("select bodova, status from zadatak where zadaca=$zadaca and redni_broj=$i and student=$student order by id desc limit 1");
					if (db_num_rows($q80)>0 && db_result($q80,0,1)==5) {
						// status=5 - pregledana
						$bodovi += db_result($q80,0,0);
					}
				}
			}

			if (db_num_rows($q70)>0) {
				$q90 = db_query("lock tables komponentebodovi write");
				$q91 = db_query("delete from komponentebodovi where student=$student and predmet=$predmet and komponenta=$k");
				$q92 = db_query("insert into komponentebodovi set student=$student, predmet=$predmet, komponenta=$k, bodovi=$bodovi");
				$q93 = db_query("unlock tables");
			}
			break;

		case 5: // Fiksne komponente
			// fiksne komponente se upisuju direktno u tabelu komponentebodovi
			break;
		}
	} // while ($r40...


/*
	// Brisanje starih komponenti
	$q5 = db_query("delete from komponentebodovi where student=$student and predmet=$predmet");


	///////////////
	//  ISPITI
	///////////////

	// Maksimalna ocjena na ispitima
	$max = array();
	$bilo = array();
	
	$q10 = db_query("select i.id, i.komponenta, io.ocjena, k.tipkomponente, k.opcija, k.prolaz from ispit as i, ispitocjene as io, komponenta as k where i.predmet=$predmet and i.id=io.ispit and io.student=$student and i.komponenta=k.id");
	while ($r10 = db_fetch_row($q10)) {
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
				$q20 = db_query("insert into komponentebodovi set student=$student, predmet=$predmet, komponenta=$k, bodovi=".$max[$k]);
			}
		}
	}

	// Obrada parcijalnih ispita
	foreach ($tipkomponente as $k=>$tip) {
		// "/" znaci da je ovaj ispit zamijenjen integralnim
		if ($tip==1 && $max[$k]!="/") { // 1 = parcijalni
			$q30 = db_query("insert into komponentebodovi set student=$student, predmet=$predmet, komponenta=$k, bodovi=".$max[$k]);
		}
	}


	//////////////////////
	//  OSTALE KOMPONENTE
	//////////////////////

	$q40 = db_query("select k.id, k.opcija, k.tipkomponente, k.maxbodova, k.prolaz from komponenta as k, tippredmeta_komponenta as tpk, ponudakursa as pk where tpk.komponenta=k.id and tpk.tippredmeta=pk.tippredmeta and pk.id=$predmet and (k.tipkomponente!=1 and k.tipkomponente!=2)");*/

}


// Funkcija koja provjerava da li je student dao uslov za upis na sljedecu godinu studija, odnosno koliko predmeta nije položeno
// Vraća boolean vrijednost
// Globalni niz $zamger_predmeti_pao sadrži id-eve predmeta koji nisu položeni

function uslov($student, $ag=0) {
	global $zamger_predmeti_pao;
	$ima_uslov=false;

	// Odredjujemo studij i semestar
	if ($ag==0) {
		$q10 = db_query("select ss.studij, ss.semestar, ts.trajanje from student_studij as ss, studij as s, tipstudija as ts where ss.student=$student and ss.studij=s.id and s.tipstudija=ts.id order by ss.akademska_godina desc, ss.semestar desc limit 1");
		if (db_num_rows($q10)<1) 
			return true; // Nikad nije bio student, ima uslov za prvu godinu ;)
	} else {
		$q10 = db_query("select ss.studij, ss.semestar, ts.trajanje from student_studij as ss, studij as s, tipstudija as ts where ss.student=$student and ss.studij=s.id and s.tipstudija=ts.id and ss.akademska_godina=$ag order by ss.semestar desc limit 1");
		if (db_num_rows($q10)<1) 
			return false; // Nije bio student u datoj akademskoj godini
	}

	$studij = db_result($q10,0,0);
	$semestar = db_result($q10,0,1);
	if ($semestar%2==1) $semestar++; // zaokružujemo na parni semestar
	$studij_trajanje = db_result($q10,0,2);

	// Od predmeta koje je slušao, koliko je pao?
	$q20 = db_query("select distinct pk.predmet, p.ects, pk.semestar, pk.obavezan from ponudakursa as pk, student_predmet as sp, predmet as p where sp.student=$student and sp.predmet=pk.id and pk.semestar<=$semestar and pk.studij=$studij and pk.predmet=p.id order by pk.semestar");
	$obavezni_pao_ects=$obavezni_pao=$nize_godine=$ects_polozio=0;
	$zamger_predmeti_pao=array();
	while ($r20 = db_fetch_row($q20)) {
		$predmet = $r20[0];

		$ects = $r20[1];
		$predmet_semestar = $r20[2];
		$obavezan = $r20[3];

		$q30 = db_query("select count(*) from konacna_ocjena where student=$student and predmet=$predmet and ocjena>5");
		if (db_result($q30,0,0)<1) {
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
// Ako je parametar $ispis == true, ne radi ništa (vraća id ponudekursa ako ista već postoji,
// u suprotnom vraća false)
function kreiraj_ponudu_kursa($predmet, $studij, $semestar, $ag, $obavezan, $ispis) {
	// Naziv predmeta nam treba za poruke
	if ($obavezan === true || $obavezan === 1) $obavezan=1; else $obavezan=0;
	$q60 = db_query("select naziv from predmet where id=$predmet");
	$naziv_predmeta = db_result($q60,0,0);
	$pkid = false;

	// Da li već postoji slog u tabeli ponudakursa
	$q61 = db_query("select id from ponudakursa where predmet=$predmet and akademska_godina=$ag and studij=$studij and semestar=$semestar");
	if (db_num_rows($q61)>0) {
		if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Već postoji slog u tabeli ponudakursa za $naziv_predmeta<br/>\n";
		$pkid = db_result($q61,0,0);

	} else {
		if ($obavezan==1) $tekst = "obavezan"; else $tekst = "izborni";
		if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Dodajem predmet $naziv_predmeta ($tekst)<br/>\n";
		else {
			$q63 = db_query("insert into ponudakursa set predmet=$predmet, studij=$studij, semestar=$semestar, obavezan=$obavezan, akademska_godina=$ag");
			$pkid = db_insert_id();

			// Kreiranje labgrupe "svi studenti"
			$q65 = db_query("select count(*) from labgrupa where predmet=$predmet and akademska_godina=$ag and virtualna=1");
			if (db_result($q65,0,0)==0)
				$q67 = db_query("insert into labgrupa set naziv='(Svi studenti)', predmet=$predmet, akademska_godina=$ag, virtualna=1");
		}
	}

	// Dodajem slog u akademska_godina_predmet
	// Uzimamo tip predmeta od prethodne godine
	$q80 = db_query("select akademska_godina, tippredmeta from akademska_godina_predmet where predmet=$predmet and akademska_godina<=$ag order by akademska_godina desc limit 1");
	if (db_num_rows($q80)==0) 
		$tippredmeta = 1; // 1 = ETF Bologna Standard - mora postojati
	else if (db_result($q80,0,0) == $ag) { // Već postoji
		if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Već postoji slog u akademska_godina_predmet<br>\n";
	} else {
		$tippredmeta = db_result($q80,0,1);
		if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Dodajem slog u akademska_godina_predmet<br>\n";
		else $q90 = db_query("insert into akademska_godina_predmet set akademska_godina=$ag, predmet=$predmet, tippredmeta=$tippredmeta");
	}

	// Kopiram podatak od prošle godine za moodle predmet id, ako ga ima
	$q100 = db_query("select akademska_godina, moodle_id from moodle_predmet_id where predmet=$predmet and akademska_godina<=$ag order by akademska_godina desc limit 1");
	// Ako ga nema, ne radimo ništa
	if (db_num_rows($q100)>0) {
		if (db_result($q100,0,0) == $ag) { // Već postoji
			if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Već postoji slog u moodle_predmet_id<br>\n";
		} else {
			$moodle_id = db_result($q100,0,1);
			if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Dodajem slog u moodle_predmet_id<br>\n";
			else $q110 = db_query("insert into moodle_predmet_id set akademska_godina=$ag, predmet=$predmet, moodle_id=$moodle_id");
		}
	} else {
		if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- E: Nema podatka od prošle godine za moodle_predmet_id<br>\n";
	}

	// Kopiram podatak od prošle godine za angažman
	$q120 = db_query("select count(*) from angazman where predmet=$predmet and akademska_godina=$ag");
	if (db_result($q120,0,0) > 0) {
		if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Već postoje slogovi u tabeli angazman<br>\n";
	} else {
		if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Kopiram angažman od prošle godine<br>\n";
		else {
			$q130 = db_query("select osoba, angazman_status from angazman where predmet=$predmet and akademska_godina=".($ag-1));
			while ($r130 = db_fetch_row($q130))
				$q140 = db_query("insert into angazman set osoba=$r130[0], angazman_status=$r130[1], predmet=$predmet, akademska_godina=$ag");
		}
	}

	// Kopiram podatak od prošle godine za prava pristupa
	$q150 = db_query("select count(*) from nastavnik_predmet where predmet=$predmet and akademska_godina=$ag");
	if (db_result($q150,0,0) > 0) {
		if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Već postoje slogovi u tabeli nastavnik_predmet<br>\n";
	} else {
		if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- Kopiram prava pristupa od prošle godine<br>\n";
		else {
			$q160 = db_query("select nastavnik, nivo_pristupa from nastavnik_predmet where predmet=$predmet and akademska_godina=".($ag-1));
			while ($r160 = db_fetch_row($q160))
				$q170 = db_query("insert into nastavnik_predmet set nastavnik=$r160[0], nivo_pristupa='$r160[1]', predmet=$predmet, akademska_godina=$ag");
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
	global $userid; // TODO ovo treba biti parametar $student...
//	print "Provjeravam kapacitet $predmet za godinu $zagodinu<br>";
	// Provjera kapaciteta
	$q112 = db_query("SELECT kapacitet, kapacitet_ekstra FROM ugovoroucenju_kapacitet WHERE predmet=$predmet AND akademska_godina=$zagodinu");
	if (db_num_rows($q112)>0) {
		$kapacitet = db_result($q112,0,0);
		$kapacitet_ekstra = db_result($q112,0,0);
		
		// Koliko je studenata izabralo predmet kao izborni?
		$q113 = db_query("SELECT COUNT(*) FROM ugovoroucenju as uou, ugovoroucenju_izborni as uoi WHERE uou.akademska_godina=$zagodinu AND uou.student!=$userid AND uoi.ugovoroucenju=uou.id AND uoi.predmet=$predmet AND (SELECT COUNT(*) FROM konacna_ocjena AS ko WHERE ko.predmet=$predmet AND ko.ocjena>5 AND ko.student=uou.student)=0");
		$popunjeno = db_result($q113,0,0);
		
		// Koliko sluša na koliziju?
		$q114 = db_query("SELECT COUNT(*) FROM kolizija WHERE akademska_godina=$zagodinu AND predmet=$predmet AND student!=$userid");
		$popunjeno += db_result($q114,0,0);
		
		if ($kapacitet_ekstra != 0 && $popunjeno >= $kapacitet_ekstra)
			return 0;
		
		// Koliko studenata slusa predmet kao obavezan na svom studiju?
		$q115 = db_query("SELECT ps.studij, psp.semestar FROM plan_studija ps, plan_studija_predmet psp, pasos_predmeta pp WHERE ps.id=$najnoviji_plan AND psp.plan_studija=$najnoviji_plan AND psp.pasos_predmeta=pp.id AND pp.predmet=$predmet AND psp.obavezan=1");
		if (db_fetch2($q115, $studij, $semestar)) {
			$q116 = db_query("SELECT COUNT(*) FROM ugovoroucenju WHERE akademska_godina=$zagodinu AND studij=$studij AND semestar=$semestar");
			$popunjeno += db_result($q116,0,0);
		}
//		print "popunjeno $popunjeno<br>";
		
		if ($kapacitet != 0 && $popunjeno >= $kapacitet) 
			return 0;
	}
	return 1;
}


// Da li je student ostvario preduvjete za dati predmet?
// Povratna vrijednost: niz IDova predmeta koji su preduvjet a nisu položeni

function provjeri_preduvjete($predmet, $student, $najnoviji_plan) {
	$rezultat = array();

	$q100 = db_query("SELECT preduvjet FROM preduvjeti WHERE predmet=$predmet");
	while (db_fetch1($q100, $preduvjet)) {
		// Da li je preduvjet po najnovijem planu na istoj ili višoj godini kao predmet?
		$semestar = db_get("SELECT psp.semestar FROM plan_studija_predmet psp, pasos_predmeta pp WHERE psp.plan_studija=$najnoviji_plan AND psp.pasos_predmeta=pp.id AND pp.predmet=$predmet AND psp.obavezan=1");
		if ($semestar === false) 
			$semestar = db_get("SELECT psp.semestar FROM plan_studija_predmet psp, plan_izborni_slot pis, pasos_predmeta pp WHERE psp.plan_studija=$najnoviji_plan AND psp.obavezan=0 AND psp.plan_izborni_slot=pis.id AND pis.predmet=$predmet");
		if ($semestar === false) { niceerror("Predmet nije pronađen u planu i programu"); return; }
		$godina_predmeta = ($semestar+1)/2;

		$semestar = db_get("SELECT psp.semestar FROM plan_studija_predmet psp, pasos_predmeta pp WHERE psp.plan_studija=$najnoviji_plan AND psp.pasos_predmeta=pp.id AND pp.predmet=$preduvjet AND psp.obavezan=1");
		if ($semestar === false) 
			$semestar = db_get("SELECT psp.semestar FROM plan_studija_predmet psp, plan_izborni_slot pis, pasos_predmeta pp WHERE psp.plan_studija=$najnoviji_plan AND psp.obavezan=0 AND psp.plan_izborni_slot=pis.id AND pis.predmet=$preduvjet");
		if ($semestar === false) { niceerror("Preduvjet nije pronađen u planu i programu"); return; }
		$godina_preduvjeta = ($semestar+1)/2;

		if ($godina_preduvjeta >= $godina_predmeta) continue;

		// Da li je položio?
		$br_ocjena = db_get("SELECT COUNT(*) FROM konacna_ocjena WHERE student=$student AND predmet=$preduvjet AND ocjena>5");
		if ($br_ocjena == 0) array_push($rezultat, $preduvjet);
	}
	return $rezultat;
}

?>
