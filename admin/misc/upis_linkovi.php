<?php

// Spisak sa linkovima na studentska/osobe radi lakšeg upisa

function admin_misc_upis_linkovi() {
	$nova_ag = intval($_REQUEST['ag']);
	$godina = intval($_REQUEST['godina']);
	$parni = intval($_REQUEST['parni']);
	$neparni = 1-$parni;
	if (isset($_REQUEST['direktno'])) $direktno = true; else $direktno = false;
	if (isset($_REQUEST['kolizija'])) $kolizija = true; else $kolizija = false;
	
	$stari_semestar = $godina*2 - $parni;
	$novi_semestar = $stari_semestar+1;
	$stara_ag = $nova_ag - $neparni;
	
	$q10 = db_query("select o.id, o.ime, o.prezime, o.brindexa, ss.studij from osoba as o, student_studij as ss where ss.akademska_godina=$stara_ag and ss.semestar=$stari_semestar and
ss.student=o.id order by o.prezime, o.ime");
	while ($r10 = db_fetch_row($q10)) {
		if ($kolizija) {
			$q20 = db_query("SELECT count(*) FROM kolizija WHERE student=$r10[0] AND akademska_godina=$nova_ag");
			if (db_result($q20,0,0)==0) continue;
		} else {
			$q20 = db_query("SELECT count(*) FROM student_studij WHERE student=$r10[0] AND akademska_godina=$nova_ag AND semestar mod 2=$neparni");
			if (db_result($q20,0,0)>0) continue;
		}
		if ($direktno)
			print "<a href=\"?sta=studentska/osobe&osoba=$r10[0]&akcija=upis&studij=$r10[4]&semestar=$novi_semestar&godina=$nova_ag\">$r10[2] $r10[1] ($r10[3])</a><br>\n";
		else
			print "<a href=\"?sta=studentska/osobe&osoba=$r10[0]&akcija=edit\">$r10[2] $r10[1] ($r10[3])</a><br>\n";
	}
	
}
