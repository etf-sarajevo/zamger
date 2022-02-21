<?php

//----------------------------------------
// Retroaktivni upis u predmete svih studenata viših godina
//----------------------------------------

// Koristi se samo ako sam opet bio kreten i nisam na vrijeme koristio admin/novagodina

function admin_misc_upis_vise() {
	// TODO prebaciti na api
	
	$ag = intval($_REQUEST['ag']);
	
	if ($_REQUEST['fakatradi'] != 1) $ispis=1; else $ispis=0;
	$q10 = db_query("select ss.student, ss.studij, s.kratkinaziv, ss.ponovac, ss.semestar from student_studij as ss, studij as s, tipstudija as ts where ss.akademska_godina=$ag and ss.studij=s.id and s.tipstudija=ts.id and ts.ciklus=1");
	while (db_fetch5($q10, $student, $studij, $studij_kratki, $ponovac, $semestar)) {
		$godina = ($semestar + 1) / 2;
		if ($ispis) {
			$pstudent = db_query_assoc("SELECT ime, prezime, brindexa FROM osoba WHERE id=$student");
			print "Student " . $pstudent['ime'] . " " . $pstudent['prezime'] . " (" . $pstudent['brindexa'] . ") - $studij_kratki $godina";
		}
		if ($ponovac > 0) {
			if ($ispis)
				print " - ponovac<br>\n";
			global $zamger_predmeti_pao, $zamger_pao_ects;
			$uslov = ima_li_uslov($student, $ag);
			foreach ($zamger_predmeti_pao as $predmet => $naziv_predmeta) {
				if ($ispis)
					print "-- Pao predmet $naziv_predmeta<br>\n";
				
				$q15 = db_query("SELECT id, semestar FROM ponudakursa WHERE akademska_godina=$ag AND predmet=$predmet AND studij=$studij");
				if (db_num_rows($q15) == 0) {
					if ($ispis)
						print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; -- Nepoznata ponudakursa (studij $studij semestar $semestar)<br>\n";
					else
						print""; // Kreiraj ponudu kursa?
				} else {
					db_fetch2($q15, $pk, $semestar);
					if ($semestar % 2 == 0) {
						if ($ispis)
							print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; -- Predmet u parnom semestru, preskačem<br>\n";
						continue;
					}
				}
				$vecupisan = db_get("SELECT COUNT(*) FROM student_predmet WHERE student=$student AND predmet=$pk");
				if ($vecupisan) {
					if ($ispis)
						print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; -- Već upisan na predmet<br>\n";
				}
				else if (!$ispis)
					upis_studenta_na_predmet($student, $pk);
			}
			
		} else {
			if ($ispis) print "<br>\n";
			$q20 = db_query("select pk.id, p.naziv from ponudakursa as pk, predmet as p where pk.semestar=$semestar and pk.akademska_godina=$ag and pk.studij=$studij and pk.predmet=p.id AND pk.obavezan=1");
			while (db_fetch2($q20, $pk, $naziv_predmeta)) {
				$vecupisan = db_get("SELECT COUNT(*) FROM student_predmet WHERE student=$student AND predmet=$pk");
				if ($vecupisan) {
					if ($ispis)
						print "-- Već upisan na obavezan predmet $naziv_predmeta<br>\n";
				} else if ($ispis)
					print "-- Upisujem na obavezan predmet $naziv_predmeta<br>\n";
				else
					upis_studenta_na_predmet($student, $pk);
			}
			$uou_id = db_get("SELECT id FROM ugovoroucenju WHERE student=$student AND akademska_godina=$ag AND semestar=$semestar");
			if (!$uou_id) {
				if ($ispis)
					print "-- Student nije popunio ugovor o učenju<br>\n";
			} else {
				$predmeti_uou = db_query_varray("SELECT predmet FROM ugovoroucenju_izborni WHERE ugovoroucenju=$uou_id");
				foreach($predmeti_uou as $predmet) {
					$q30 = db_query("select pk.id, p.naziv from ponudakursa as pk, predmet as p where pk.semestar=$semestar and pk.akademska_godina=$ag and pk.studij=$studij and pk.predmet=p.id AND pk.obavezan=0 AND p.id=$predmet");
					if (db_num_rows($q30) == 0) {
						if ($ispis) {
							$naziv_predmeta = db_get("SELECT naziv FROM predmet WHERE id=$predmet");
							print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; -- Ne postoji ponuda kursa za predmet $naziv_predmeta iz ugovora<br>\n";
						} else
							print ""; // Kreirati ponudu za izborni sa drugog odsjeka
					} else {
						db_fetch2($q30, $pk, $naziv_predmeta);
						$vecupisan = db_get("SELECT COUNT(*) FROM student_predmet WHERE student=$student AND predmet=$pk");
						if ($vecupisan) {
							if ($ispis)
								print "-- Već upisan na izborni predmet $naziv_predmeta (uou)<br>\n";
						}
						else if ($ispis)
							print "-- Upisujem na izborni predmet $naziv_predmeta (uou)<br>\n";
						else
							upis_studenta_na_predmet($student, $pk);
					}
				}
			}
		}

// 		$q5 = db_query("select pk.id, p.naziv from ponudakursa as pk, predmet as p where pk.semestar=1 and pk.akademska_godina=$ag and pk.studij=$r10[1] and pk.predmet=p.id");
// 		while ($r5 = db_fetch_row($q5)) {
// 			$q15 = db_query("select count(*) from student_predmet where student=$r10[0] and predmet=$r5[0]");
// 			if (db_result($q15,0,0)>0) {
// 				if ($ispis) {
// 					$q20 = db_query("select ime, prezime, brindexa from osoba where id=$r10[0]");
// 					print "Student ".db_result($q20,0,0)." ".db_result($q20,0,1)." ".db_result($q20,0,2)." već upisan na ponudukursa $r5[1]<br>";
// 				}
// 			} else {
// 				if ($ispis) {
// 					$q20 = db_query("select ime, prezime, brindexa from osoba where id=$r10[0]");
// 					print "Upisujem studenta ".db_result($q20,0,0)." ".db_result($q20,0,1)." ".db_result($q20,0,2)." na ponudukursa $r5[1] ($r10[2] - $r5[0])<br>";
// 				} else
// 					upis_studenta_na_predmet($r10[0], $r5[0]);
// 			}
// 		}
	}
}
