<?

// LIB/STUDENT_PREDMET - funkcije za status studenta na predmetu



// Provjerava da li student sluša predmet i vraća ponudu kursa
function daj_ponudu_kursa($student, $predmet, $ag) {
	$q2 = db_query("select sp.predmet from student_predmet as sp, ponudakursa as pk where sp.student=$student and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
	if (db_num_rows($q2)<1)
		return false;
	
	return db_result($q2,0,0);
}


// Spisak labgrupa na predmetu i akademskoj godini kojih je student član
// Ako je $ukljuci_virtualne=false, neće biti vraćene virtualne labgrupe
function student_labgrupe($student, $predmet, $ag, $ukljuci_virtualne = true) {
	global $userid;
	
	$rezultat = array();
	$upit = "SELECT l.id FROM student_labgrupa as sl, labgrupa as l WHERE sl.labgrupa=l.id AND sl.student=$student AND l.predmet=$predmet AND l.akademska_godina=$ag";
	if (!$ukljuci_virtualne) $upit .= " AND l.virtualna=0";
	$q10 = db_query($upit);
	while ($r10 = db_fetch_row($q10)) $rezultat[] = $r10[0];
	return $rezultat;
}


// Funkcija koja ispisuje studenta iz labgrupe, brisuci sve relevantne podatke
// (prisustvo, komentari)
// Ne zaboravite updatovati komponente ako treba (prisustvo je promijenjeno)!
function ispis_studenta_sa_labgrupe($student, $labgrupa) {
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
function ispis_studenta_sa_predmeta($student, $predmet, $ag) {
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


// Upis studenta na labgrupu
function upis_studenta_na_labgrupu($student, $labgrupa) {
	$q = db_query("insert into student_labgrupa set student=$student, labgrupa=$labgrupa");
}


// Upis studenta na predmet
// Parametar funkcije je ustvari ponudakursa
function upis_studenta_na_predmet($student, $ponudakursa) {
	// Da li je student već upisan na predmet?
	$q5 = db_query("SELECT COUNT(*) FROM student_predmet WHERE student=$student AND predmet=$ponudakursa");
	if (db_result($q5,0,0)>0) return;

	// Pronalazimo labgrupu "(Svi studenti)" i upisujemo studenta u nju (mora postojati)
	$q20 = db_query("select l.id, pk.predmet, pk.akademska_godina from labgrupa as l, ponudakursa as pk where pk.id=$ponudakursa and pk.predmet=l.predmet and pk.akademska_godina=l.akademska_godina and l.virtualna=1");
	if (db_num_rows($q20) == 0) {
		niceerror("Ne postoji grupa (Svi studenti) za ovaj predmet");
		zamgerlog2("nepostojeca virtualna labgrupa", 0, 0, 0, $ponudakursa); // FIXME negdje moramo imati predmet/ag ?
		return;
	}
	$labgrupa = db_result($q20,0,0); // mora postojati
	$predmet = db_result($q20,0,1); // treba nam za $q40
	$ag = db_result($q20,0,2); // treba nam za $q40

	// Zapis u tabeli student_predmet
	$q10 = db_query("insert into student_predmet set student=$student, predmet=$ponudakursa");
	
	$q30 = db_query("insert into student_labgrupa set student=$student, labgrupa=$labgrupa");

	// Potrebno je upisati max. bodova za sve komponente prisustva!
	$q40 = db_query("select k.id, k.maxbodova from komponenta as k, tippredmeta_komponenta as tpk, akademska_godina_predmet as agp where agp.predmet=$predmet and agp.tippredmeta=tpk.tippredmeta and agp.akademska_godina=$ag and tpk.komponenta=k.id and k.tipkomponente=3"); // tip komponente 3 = klasično prisustvo
	while ($r40 = db_fetch_row($q40)) {
		$q50 = db_query("insert into komponentebodovi set student=$student, predmet=$ponudakursa, komponenta=$r40[0], bodovi=$r40[1]");
	}
	
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

			} else if ($maxodsustva == -3) { // Još jedan sistem TP
				$q205 = db_query("select count(*) from cas as c, labgrupa as l, prisustvo as p, ponudakursa as pk where c.labgrupa=l.id and l.predmet=pk.predmet and l.akademska_godina=pk.akademska_godina and pk.id=$predmet and c.komponenta=$k and c.id=p.cas and p.student=$student");
				$casova = db_result($q205,0,0);
				
				$bodovi = ($maxbodova / 13) * ($casova - $odsustva);

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


?>
