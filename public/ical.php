<?

// PUBLIC/ICAL - raspored u iCal a.k.a iCalendar formatu

// Specifikacija: http://tools.ietf.org/html/rfc5545



function public_ical() {

	global $user_nastavnik;

	// Nizovi sa imenima termina
	$vrijeme_pocetak = array("0" => "0800", "1" => "0900", "2" => "1000", "3" => "1100", "4" => "1200", "5" => "1300",
				"6" => "1400", "7" => "1500", "8" => "1600", "9" => "1700", "10" => "1800", "11" => "1900", "12" => "2000");
	$vrijeme_kraj = array("0" => "0845", "1" => "0945", "2" => "1045", "3" => "1145", "4" => "1245", "5" => "1345",
				"6" => "1445", "7" => "1545", "8" => "1645", "9" => "1745", "10" => "1845", "11" => "1945", "12" => "2045");

	// Pretvaramo rss id u userid
	$id = db_escape($_REQUEST['id']);
	$q1 = db_query("select auth from rss where id='$id'");
	if (db_num_rows($q1)<1) {
		print "Greska! Nepoznat RSS ID $id";
		return 0;
	}
	$userid = db_result($q1,0,0);

	// Da li je korisnik nastavnik?
	$q2 = db_query("SELECT np.predmet, pk.akademska_godina, pk.semestar FROM nastavnik_predmet as np, ponudakursa as pk, akademska_godina as ag WHERE np.nastavnik = $userid AND pk.predmet = np.predmet AND pk.akademska_godina = ag.id and np.akademska_godina=ag.id and ag.aktuelna=1");
	if (db_num_rows($q2)>0) $user_nastavnik=true; else $user_nastavnik=false;

	// Da li je semestar parni ili neparni?
	$q10 = db_query("SELECT CURDATE()<pocetak_ljetnjeg_semestra FROM akademska_godina WHERE aktuelna=1");
	$neparni = db_result($q10,0,0);

	if ($user_nastavnik) {
		// Spisak predmeta na kojima je nastavnik angažovan
		$whereCounter = 0;
		$predmet_bio = array();
		while($sUD = db_fetch_assoc($q2)) {
			if (in_array($sUD['predmet'], $predmet_bio)) continue;
			array_push($predmet_bio, $sUD['predmet']);
			$adId = $sUD['akademska_godina'];
			$semId = $sUD['semestar'];
			if ($semId%2 != $neparni) continue;
			
			if($whereCounter > 0)
				$sqlPredmet .= " OR rs.predmet = ".$sUD['predmet'];
			else
				$sqlPredmet = " rs.predmet = ".$sUD['predmet'];
			
			$whereCounter++;
		}
			
		//$sqlWhere = "godinaR = '".$adId."' AND semestarR = '".$semId."' AND tip = 'P' AND (".$sqlPredmet.")"; // WTF!?!?
		if (strlen($sqlPredmet)>0) $sqlWhere = "(".$sqlPredmet.")";
		else $sqlWhere="1=0"; // Nije angazovan nigdje, prikaži prazan raspored
		
		$sqlUpit = "SELECT rs.id, p.naziv as naz, p.kratki_naziv, rs.dan_u_sedmici, rs.tip, rs.vrijeme_pocetak, rs.vrijeme_kraj, rs.labgrupa, rsala.naziv, rs.fini_pocetak, rs.fini_kraj, UNIX_TIMESTAMP(r.vrijeme_kreiranja)
		FROM raspored_stavka as rs, raspored_sala as rsala, predmet as p, raspored as r, akademska_godina as ag
		WHERE ".$sqlWhere." AND rsala.id=rs.sala AND p.id=rs.predmet AND rs.raspored=r.id AND (r.privatno=0 OR r.privatno=$userid) AND r.akademska_godina=ag.id AND ag.aktuelna=1
		ORDER BY rs.dan_u_sedmici ASC, rs.vrijeme_pocetak ASC, rs.id ASC";

	}
	else {
		$sqlUpit = "SELECT rs.id, p.naziv, p.kratki_naziv, rs.dan_u_sedmici, rs.tip, rs.vrijeme_pocetak, rs.vrijeme_kraj, rs.labgrupa, rsala.naziv, rs.fini_pocetak, rs.fini_kraj, UNIX_TIMESTAMP(r.vrijeme_kreiranja)
		FROM raspored_stavka as rs, raspored as r, predmet as p, ponudakursa as pk, student_predmet as sp, student_labgrupa as sl, raspored_sala as rsala, akademska_godina as ag
		WHERE sp.student=$userid AND sp.predmet=pk.id AND pk.predmet=p.id AND pk.akademska_godina=ag.id and pk.semestar mod 2=$neparni and ag.aktuelna=1 AND p.id=rs.predmet AND rs.raspored=r.id AND r.aktivan=1 AND sl.student=$userid AND (rs.labgrupa=0 or rs.labgrupa=sl.labgrupa) AND rs.sala=rsala.id
		GROUP BY rs.labgrupa, rs.dan_u_sedmici, rs.vrijeme_pocetak, p.naziv
		ORDER BY rs.dan_u_sedmici ASC, rs.vrijeme_pocetak ASC, rs.id ASC";

	}

	// Treba nam i aktuelna akademska godina
	$q20 = db_query("select naziv from akademska_godina where aktuelna=1");
	list($zimska_godina, $ljetnja_godina) = explode("/", db_result($q20,0,0));

	header("Content-Type: text/calendar");
	$output = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//ETF/Zamger//NONSGML v1.0//EN\r\n";

	// Selektuj podatke iz baze
	$q10 = db_query($sqlUpit);
	while ($row = db_fetch_row($q10)) {
		// polja
		$rsid = $row[0];
		$predmet_naziv = $row[1];
		$predmet_kratki_naziv = $row[2];
		$dan_u_sedmici = $row[3];
		$tip_stavke = $row[4];
		$vpocetak = $row[5];
		$vkraj = $row[6];
		$labgrupa = $row[7];
		$naziv_sale = $row[8];
		$fini_pocetak = substr($row[9],0,5); // Odsjecamo sekunde
		$fini_kraj = substr($row[10],0,5);
		$vrijeme_kreiranja_rasporeda = date("Ymd", $row[11])."T".date("His", $row[11]);

		if ($neparni_semestar == 0) { // Parni semestar
			// Određujemo datum početka semestra 
			$dan = 18; $mjesec = 2; $godina=$ljetnja_godina;

			do {
				$dan++;
				$dus = date("w", mktime(0,0,0, $mjesec, $dan, $godina));
			} while ($dus != 1); // 1 = ponedjeljak
			// $dan+$mjesec+$godina je datum prvog ponedjeljka u semestru, sada tražimo $dan_u_sedmici
			while ($dus != $dan_u_sedmici) {
				$dan++;
				$dus = date("w", mktime(0,0,0, $mjesec, $dan, $godina));
			}
			if ($dan<10) $dan = "0$dan";
			if ($mjesec<10) $mjesec = "0$mjesec";

		} else { // Neparni semestar
			// Određujemo datum početka semestra 
			$dan = 18; $mjesec = 9; $godina=$zimska_godina;

			do {
				$dan++;
				$dus = date("w", mktime(0,0,0, $mjesec, $dan, $godina));
			} while ($dus != 1);
			// $dan+$mjesec+$godina je datum prvog ponedjeljka u semestru, sada tražimo $dan_u_sedmici
			while ($dus != $dan_u_sedmici) {
				$dan++;
				$dus = date("w", mktime(0,0,0, $mjesec, $dan, $godina));
			}
			if ($dan<10) $dan = "0$dan";
			if ($mjesec<10) $mjesec = "0$mjesec";
		}

		// Vrijeme početka i kraja
		if ($fini_pocetak == "00:00") {
			// Nije zadano fino vrijeme časa, koristimo grube blokove od 45 minuta + 15 minuta pauze
			$ical_start = $vrijeme_pocetak[$vpocetak]."00";
			$ical_end = $vrijeme_kraj[$vkraj]."00";
		} else {
			$pocetak_sati = intval(substr($fini_pocetak, 0, 2));
			$pocetak_minute = intval(substr($fini_pocetak, 3, 2));
			$kraj_sati = intval(substr($fini_kraj, 0, 2));
			$kraj_minute = intval(substr($fini_kraj, 3, 2));
			if ($pocetak_sati<10) $pocetak_sati = "0$pocetak_sati";
			if ($pocetak_minute<10) $pocetak_minute = "0$pocetak_minute";
			if ($kraj_sati<10) $kraj_sati = "0$kraj_sati";
			if ($kraj_minute<10) $kraj_minute = "0$kraj_minute";
			$ical_start = $pocetak_sati.$pocetak_minute."00";
			$ical_end = $kraj_sati.$kraj_minute."00";
		}

		// Opis događaja u kalendaru
		$summary = $predmet_naziv;
		if($tip_stavke == "P") {
			$summary .= " (Predavanje)";
		} else if($tip_stavke == "T") {
			$summary .= " (Tutorijal)";
		} else {
			$summary .= " (Laboratorijska vježba)";
		}
		if ($user_nastavnik && $labgrupa != 0) {
			$qmomoc = db_query("select naziv from labgrupa where id=$labgrupa");
			$summary .= " ".db_result($qmomoc,0,0);
		}
		
		// Ispis
		$output .= "BEGIN:VEVENT\r\nUID:$rsid"."Z$userid@zamger.etf.unsa.ba\r\nDTSTAMP:$vrijeme_kreiranja_rasporeda\r\nDTSTART:$godina$mjesec$dan"."T$ical_start\r\nDTEND:$godina$mjesec$dan"."T$ical_end\r\nSUMMARY:$summary\r\nLOCATION:$naziv_sale\r\nTRANSP:TRANSPARENT\r\nCLASS:PUBLIC\r\nCATEGORIES:APPOINTMENT,EDUCATION\r\nRRULE:FREQ=WEEKLY;COUNT=15\r\nEND:VEVENT\r\n";

	}

	$output .= "END:VCALENDAR\r\n";
	print $output;
}

?>
