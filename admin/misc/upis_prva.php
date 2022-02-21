<?php

// Upis brucoša u predmete na prvoj godini, ako to nije obavila studentska služba

function admin_misc_upis_prva() {
	// TODO prebaciti na api
	
	$ag = intval($_REQUEST['ag']);
	
	if ($_REQUEST['fakatradi'] != 1) $ispis=1; else $ispis=0;
	$q10 = db_query("select ss.student, ss.studij, s.kratkinaziv from student_studij as ss, studij as s, tipstudija as ts where ss.akademska_godina=$ag and ss.studij=s.id and s.tipstudija=ts.id and ts.ciklus=1");
	while ($r10 = db_fetch_row($q10)) {
		$q5 = db_query("select pk.id, p.naziv from ponudakursa as pk, predmet as p where pk.semestar=1 and pk.akademska_godina=$ag and pk.studij=$r10[1] and pk.predmet=p.id");
		while ($r5 = db_fetch_row($q5)) {
			$q15 = db_query("select count(*) from student_predmet where student=$r10[0] and predmet=$r5[0]");
			if (db_result($q15,0,0)>0) {
				if ($ispis) {
					$q20 = db_query("select ime, prezime, brindexa from osoba where id=$r10[0]");
					print "Student ".db_result($q20,0,0)." ".db_result($q20,0,1)." ".db_result($q20,0,2)." već upisan na ponudukursa $r5[1]<br>";
				}
			} else {
				if ($ispis) {
					$q20 = db_query("select ime, prezime, brindexa from osoba where id=$r10[0]");
					print "Upisujem studenta ".db_result($q20,0,0)." ".db_result($q20,0,1)." ".db_result($q20,0,2)." na ponudukursa $r5[1] ($r10[2] - $r5[0])<br>";
				} else
					upis_studenta_na_predmet($r10[0], $r5[0]);
			}
		}
	}
}