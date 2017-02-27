<?

exit; // sprjecavamo slucajno izvrsenje ove vrlo opasne skripte

$olddb = "zamger3";
$newdb = "zamger";

require("config.php");



mysql_connect("localhost", $conf_dbuser, $conf_dbpass);
if (function_exists('mysql_set_charset') === false) {
     /**
      * Sets the client character set.
      *
      * Note: This function requires MySQL 5.0.7 or later.
      *
      * @see http://www.php.net/mysql-set-charset
      * @param string $charset A valid character set name
      * @param resource $link_identifier The MySQL connection
      * @return TRUE on success or FALSE on failure
      */
     function mysql_set_charset($charset, $link_identifier = null)
     {
         if ($link_identifier == null) {
             return mysql_query('SET NAMES "'.$charset.'"');
//             return mysql_query('SET CHARACTER SET "'.$charset.'"');
         } else {
             return mysql_query('SET NAMES "'.$charset.'"', $link_identifier);
//             return mysql_query('SET CHARACTER SET "'.$charset.'"', $link_identifier);
         }
     }
 }
	mysql_set_charset("utf8");


print "akademska_godina...<br/>\n\n";
$q = mysql_query("insert into $newdb.akademska_godina select * from $olddb.akademska_godina") or die(mysql_error());

print "auth...<br/>\n\n";
$q = mysql_query("insert into $newdb.auth select id,login,password,admin,external_id,'','','','',0,0,0,0 from $olddb.auth") or die(mysql_error());

print "student...<br/>\n\n";
$q = mysql_query("select id,ime,prezime,email,brindexa from $olddb.student") or die(mysql_error());
while ($r=db_fetch_row($q)) {
	$q2 = mysql_query("select count(*) from $newdb.auth where id=$r[0]") or die(mysql_error());
	if (db_result($q2,0,0)>0) {
		$q3 = mysql_query("update $newdb.auth set ime='$r[1]', prezime='$r[2]', email='$r[3]', brindexa='$r[4]', student=1 where id=$r[0]") or die(mysql_error());
	} else {
		$q4 = mysql_query("insert into $newdb.auth set id=$r[0], ime='$r[1]', prezime='$r[2]', email='$r[3]', brindexa='$r[4]'") or die(mysql_error());
	}
}

print "nastavnik...<br/>\n\n";
$q = mysql_query("select id,ime,prezime,email,siteadmin from $olddb.nastavnik") or die(mysql_error());
while ($r=db_fetch_row($q)) {
	if ($r[4]==2) { $studentska=1; $siteadmin=1; }
	else if ($r[4]==1) { $studentska=1; $siteadmin=0; }
	else { $studentska=0; $siteadmin=0; }
	$q2 = mysql_query("select count(*) from $newdb.auth where id=$r[0]") or die(mysql_error());
	if (db_result($q2,0,0)>0) {
		$q3 = mysql_query("update $newdb.auth set ime='$r[1]', prezime='$r[2]', email='$r[3]', nastavnik=1, studentska=$studentska, siteadmin=$siteadmin where id=$r[0]") or die(mysql_error());
	} else {
		$q4 = mysql_query("insert into $newdb.auth set id=$r[0], ime='$r[1]', prezime='$r[2]', email='$r[3]', nastavnik=1, studentska=$studentska, siteadmin=$siteadmin") or die(mysql_error());
	}
}

print "cas...<br/>\n\n";
$q = mysql_query("insert into $newdb.cas select c.id,c.datum,c.vrijeme,c.labgrupa,c.nastavnik,l.predmet,5 from $olddb.cas as c, $olddb.labgrupa as l where c.labgrupa=l.id") or die(mysql_error()); // komponenta za prisustvo je 5

print "institucija...<br/>\n\n";
$q = mysql_query("insert into $newdb.institucija select * from $olddb.institucija") or die(mysql_error());

print "ispit...<br/>\n\n";
$q = mysql_query("insert into $newdb.ispit select id,naziv,predmet,datum,tipispita from $olddb.ispit") or die(mysql_error()); // tipispita odgovara komponenti

print "ispitocjene...<br/>\n\n";
$q = mysql_query("insert into $newdb.ispitocjene select * from $olddb.ispitocjene") or die(mysql_error());

print "komentar...<br/>\n\n";
$q = mysql_query("insert into $newdb.komentar select k.id,k.student,k.nastavnik,k.labgrupa,l.predmet,k.datum,k.komentar from $olddb.komentar as k, $olddb.labgrupa as l where k.labgrupa=l.id") or die(mysql_error());

print "komponenta...<br/>\n\n";
$q = mysql_query("INSERT INTO $newdb.komponenta (`id`, `naziv`, `gui_naziv`, `kratki_gui_naziv`, `tipkomponente`, `maxbodova`, `prolaz`, `opcija`) VALUES
(1, 'I parcijalni (ETF BSc)', 'I parcijalni', 'I parc', 1, 20, 10, ''),
(2, 'II parcijalni (ETF BSc)', 'II parcijalni', 'II parc', 1, 20, 10, ''),
(3, 'Integralni (ETF BSc)', 'Integralni', 'Int', 2, 40, 20, '1+2'),
(4, 'Usmeni (ETF BSc)', 'Usmeni', 'Usmeni', 1, 40, 0, ''),
(5, 'Prisustvo (ETF BSc)', 'Prisustvo', 'Prisustvo', 3, 10, 0, '3'),
(6, 'Zadace (ETF BSc)', 'Zadace', 'Zadace', 4, 10, 0, '');") or die(mysql_error()) or die(mysql_error());

print "konacna_ocjena...<br/>\n\n";
$q = mysql_query("insert into $newdb.konacna_ocjena select * from $olddb.konacna_ocjena") or die(mysql_error());

print "labgrupa...<br/>\n\n";
$q = mysql_query("insert into $newdb.labgrupa select * from $olddb.labgrupa") or die(mysql_error());

// log ne kopiramo

print "nastavnik_predmet...<br/>\n\n";
$q = mysql_query("insert into $newdb.nastavnik_predmet select * from $olddb.nastavnik_predmet") or die(mysql_error());

print "ogranicenje...<br/>\n\n";
$q = mysql_query("insert into $newdb.ogranicenje select * from $olddb.ogranicenje") or die(mysql_error());

print "ponudakursa...<br/>\n\n";
$q = mysql_query("insert into $newdb.ponudakursa select id,predmet,studij,semestar,obavezan,akademska_godina,1 from $olddb.ponudakursa") or die(mysql_error()); // svi predmeti su tipa 1

print "predmet...<br/>\n\n";
$q = mysql_query("insert into $newdb.predmet select id,naziv,institucija,'' from $olddb.predmet") or die(mysql_error());
// Generisemo kratke nazive
$q1 = mysql_query("select id,naziv from $newdb.predmet") or die(mysql_error());
while ($r1 = db_fetch_row($q1)) {
	$skraceni="";
	foreach(explode(" ",$r1[1]) as $naziv) {
		$naziv = strtoupper($naziv);
		$slovo = substr($naziv,0,2);
		if ($slovo=="č") $skraceni .= "Č";
		else if ($slovo=="ć") $skraceni .= "Ć";
		else if ($slovo=="đ") $skraceni .= "Đ";
		else if ($slovo=="š") $skraceni .= "Š";
		else if ($slovo=="ž") $skraceni .= "Ž";
		else $skraceni.=substr($naziv,0,1);
	}
	$q2 = mysql_query("update $newdb.predmet set kratki_naziv='$skraceni' where id=$r1[0]") or die(mysql_error());
}

print "prisustvo...<br/>\n\n";
$q = mysql_query("insert into $newdb.prisustvo select * from $olddb.prisustvo") or die(mysql_error());

print "programskijezik...<br/>\n\n";
$q = mysql_query("insert into $newdb.programskijezik select * from $olddb.programskijezik") or die(mysql_error());

print "stdin...<br/>\n\n";
$q = mysql_query("insert into $newdb.stdin select * from $olddb.stdin") or die(mysql_error());

print "student_labgrupa...<br/>\n\n";
$q = mysql_query("insert into $newdb.student_labgrupa select * from $olddb.student_labgrupa") or die(mysql_error());

print "student_predmet...<br/>\n\n";
$q = mysql_query("insert into $newdb.student_predmet select sl.student,l.predmet from $olddb.student_labgrupa as sl, $olddb.labgrupa as l where sl.labgrupa=l.id") or die(mysql_error());

print "student_studij...<br/>\n\n";
$q = mysql_query("insert into $newdb.student_studij select * from $olddb.student_studij") or die(mysql_error());

print "studij...<br/>\n\n";
$q = mysql_query("insert into $newdb.studij select * from $olddb.studij") or die(mysql_error());

print "tipkomponente...<br/>\n\n";
$q = mysql_query("INSERT INTO $newdb.tipkomponente (`id`, `naziv`, `opis_opcija`) VALUES
(1, 'Ispit', ''),
(2, 'Integralni ispit', 'Ispiti koje zamjenjuje (razdvojeni sa +)'),
(3, 'Zadace', ''),
(4, 'Prisustvo', 'Minimalan broj izostanaka (0=linearno)'),
(5, 'Fiksna', '');") or die(mysql_error());

print "tippredmeta...<br/>\n\n";
$q = mysql_query("INSERT INTO $newdb.tippredmeta (`id`, `naziv`) VALUES
(1, 'ETF Bologna standard');") or die(mysql_error());

print "tippredmeta_komponenta...<br/>\n\n";
$q = mysql_query("INSERT INTO $newdb.tippredmeta_komponenta (`tippredmeta`, `komponenta`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6);") or die(mysql_error());

print "zadaca...<br/>\n\n";
$q = mysql_query("insert into $newdb.zadaca select id,predmet,naziv,zadataka,bodova,rok,aktivna,programskijezik, attachment,6 from $olddb.zadaca") or die(mysql_error()); // komponenta je uvijek 6

print "zadatak...<br/>\n\n";
$q = mysql_query("insert into $newdb.zadatak select * from $olddb.zadatak") or die(mysql_error());

print "zadatakdiff...<br/>\n\n";
$q = mysql_query("insert into $newdb.zadatakdiff select * from $olddb.zadatakdiff") or die(mysql_error());




print "<br/><br/><b>Komponente bodovi</b><br/><br/>\n\n";

print "ispiti...<br/>\n\n";
$q1 = mysql_query("select id from $newdb.ponudakursa order by id") or die(mysql_error());
while ($r1 = db_fetch_row($q1)) {
	$predmet=$r1[0];
	$q2 = mysql_query("select distinct io.student from $newdb.ispitocjene as io, $newdb.ispit as i where i.id=io.ispit and i.predmet=$predmet order by io.student") or die(mysql_error());
	while ($r2 = db_fetch_row($q2)) {
		$student=$r2[0];
		$max=array();
		$bilo=array();
		$q3 = mysql_query("select io.ocjena, i.komponenta from $newdb.ispitocjene as io, $newdb.ispit as i where i.id=io.ispit and io.student=$student and i.predmet=$predmet") or die(mysql_error());
		while ($r3 = db_fetch_row($q3)) {
			$ocjena=$r3[0]; $tip=$r3[1];
			if (!in_array($tip,$bilo) || $ocjena>$max[$tip])
				$max[$tip]=$ocjena;
			if (!in_array($tip,$bilo))
				array_push($bilo,$tip);
		}
		if (in_array(3,$bilo) && ($max[3]>$max[1]+$max[2] || ($max[3]>=20 && ($max[1]<10 || $max[2]<10))))
			$q4 = mysql_query("insert into $newdb.komponentebodovi set student=$student, predmet=$predmet, komponenta=3, bodovi=$max[3]") or die(mysql_error());
		else {
			if (in_array(1,$bilo))
				$q5 = mysql_query("insert into $newdb.komponentebodovi set student=$student, predmet=$predmet, komponenta=1, bodovi=$max[1]") or die(mysql_error());
			if (in_array(2,$bilo))
				$q6 = mysql_query("insert into $newdb.komponentebodovi set student=$student, predmet=$predmet, komponenta=2, bodovi=$max[2]") or die(mysql_error());
		}
		if (in_array(4,$bilo))
			$q7=mysql_query("insert into $newdb.komponentebodovi set student=$student, predmet=$predmet, komponenta=4, bodovi=$max[4]") or die(mysql_error());
	}
}

print "prisustvo...<br/>\n\n";
$q1 = mysql_query("select id from $newdb.ponudakursa order by id") or die(mysql_error());
while ($r1 = db_fetch_row($q1)) {
	$predmet=$r1[0];
	$q2 = mysql_query("select student from $newdb.student_predmet where predmet=$predmet") or die(mysql_error());
	while ($r2 = db_fetch_row($q2)) {
		$student=$r2[0];
		$q3 = mysql_query("select count(*) from $newdb.prisustvo as p, $newdb.cas as c where p.student=$student and p.prisutan=0 and p.cas=c.id and c.predmet=$predmet") or die(mysql_error());
		if (db_result($q3,0,0)>3)
			$q7=mysql_query("insert into $newdb.komponentebodovi set student=$student, predmet=$predmet, komponenta=5, bodovi=0") or die(mysql_error());
		else
			$q7=mysql_query("insert into $newdb.komponentebodovi set student=$student, predmet=$predmet, komponenta=5, bodovi=10") or die(mysql_error());
	}
}

print "zadace...<br/>\n\n";
$q1 = mysql_query("select id from $newdb.ponudakursa order by id") or die(mysql_error());
while ($r1 = db_fetch_row($q1)) {
	$predmet=$r1[0];
	$studenti=array();
	$zadace=array();
	$brzad=array();
	$q100 = mysql_query("select zk.student,zk.zadaca,zk.redni_broj,zk.bodova,zk.status from $newdb.zadatak as zk, $newdb.zadaca as z where zk.status=5 and zk.zadaca=z.id and z.predmet=$predmet order by zk.id desc") or die(mysql_error());
	while ($r100 = db_fetch_row($q100)) {
		if (!in_array($r100[0],$studenti)) array_push($studenti,$r100[0]);
		if (!in_array($r100[1],$zadace)) array_push($zadace,$r100[1]);
		if ($r100[2]>$brzad[$r100[1]]) $brzad[$r100[1]]=$r100[2];
		if ($r100[3]>0 && $b[$r100[0]][$r100[1]][$r100[2]]==0) 
			$b[$r100[0]][$r100[1]][$r100[2]]=$r100[3]+1;
	}
	foreach ($studenti as $student) {
		$bodova=0;
		foreach ($zadace as $zadaca) {
			$brz = $brzad[$zadaca];
			for ($i=1; $i<=$brz; $i++)
				if ($b[$student][$zadaca][$i]>0) 
					$bodova += ($b[$student][$zadaca][$i]-1);
		}
		$q5 = mysql_query("insert into $newdb.komponentebodovi set student=$student, predmet=$predmet, komponenta=6, bodovi=$bodova") or die(mysql_error());
	}
}




print "<br/><br/><b>UTF8 fixes</b><br/><br/>\n\n";

$asearch = array("&#269;","&#263;","&#382;","&#353;","&#273;",
"Ä‡",chr(195).chr(133).chr(194).chr(160),"Å¾","Ä","Å¡","Ä","Å½","ÄŒ","Ä†","Ä‘");
$areplace = array("č","ć","ž","š","đ",
"ć","Š","ž","Đ","š","č","Ž","Č","Ć","đ");


print "auth...<br/>\n\n";
$q = mysql_query("select id,ime,prezime,login from $newdb.auth") or die(mysql_error());
while ($r = db_fetch_row($q)) {
	$ime = str_replace($asearch,$areplace,$r[1]);
	$prezime = str_replace($asearch,$areplace,$r[2]);
	if ($ime != $r[1] || $prezime != $r[2]) 
		$q2=mysql_query("update $newdb.auth set ime='$ime',prezime='$prezime' where id=$r[0] and login='$r[3]'") or die(mysql_error());
//		print("update $newdb.student set ime='$ime',prezime='$prezime' where id=$r[0]");
}

print "institucija...<br/>\n\n";
$q = mysql_query("select id,naziv from $newdb.institucija") or die(mysql_error());
while ($r = db_fetch_row($q)) {
	$naziv = str_replace($asearch,$areplace,$r[1]);
	if ($naziv != $r[1]) $q2=mysql_query("update $newdb.institucija set naziv='$naziv' where id=$r[0]") or die(mysql_error());
}
print "komentar...<br/>\n\n";
$q = mysql_query("select id,komentar from $newdb.komentar") or die(mysql_error());
while ($r = db_fetch_row($q)) {
	$komentar = str_replace($asearch,$areplace,$r[1]);
	if ($naziv != $r[1]) $q2=mysql_query("update $newdb.komentar set komentar='$komentar' where id=$r[0]") or die(mysql_error());
}
print "labgrupa...<br/>\n\n";
$q = mysql_query("select id,naziv from $newdb.labgrupa") or die(mysql_error());
while ($r = db_fetch_row($q)) {
	$naziv = str_replace($asearch,$areplace,$r[1]);
	if ($naziv != $r[1]) $q2=mysql_query("update $newdb.labgrupa set naziv='$naziv' where id=$r[0]") or die(mysql_error());
}

print "predmet...<br/>\n\n";
$q = mysql_query("select id,naziv from $newdb.predmet") or die(mysql_error());
while ($r = db_fetch_row($q)) {
	$naziv = str_replace($asearch,$areplace,$r[1]);
	if ($naziv != $r[1]) $q2=mysql_query("update $newdb.predmet set naziv='$naziv' where id=$r[0]") or die(mysql_error());
}


print "studij...<br/>\n\n";
$q = mysql_query("select id,naziv from $newdb.studij") or die(mysql_error());
while ($r = db_fetch_row($q)) {
	$naziv = str_replace($asearch,$areplace,$r[1]);
	if ($naziv != $r[1]) $q2=mysql_query("update $newdb.studij set naziv='$naziv' where id=$r[0]") or die(mysql_error());
}
print "zadaca...<br/>\n\n";
$q = mysql_query("select id,naziv from $newdb.zadaca") or die(mysql_error());
while ($r = db_fetch_row($q)) {
	$naziv = str_replace($asearch,$areplace,$r[1]);
	if ($naziv != $r[1]) $q2=mysql_query("update $newdb.zadaca set naziv='$naziv' where id=$r[0]") or die(mysql_error());
}
print "zadatak...<br/>\n\n";
$q = mysql_query("select id,komentar from $newdb.zadatak") or die(mysql_error());
while ($r = db_fetch_row($q)) {
	$komentar = str_replace($asearch,$areplace,$r[1]);
	$komentar = str_replace('\'','\\\'',$komentar);
	if ($naziv != $r[1]) $q2=mysql_query("update $newdb.zadatak set komentar='$komentar' where id=$r[0]") or die(mysql_error());
}




print "<br/><br/><b>Akademska godina - fix</b><br/>Popravljamo redoslijed akademskih godina...<br/><br/>\n\n";

print "akademska_godina...<br/>\n\n";
$q = mysql_query("update akademska_godina set id=100 where id=1");
$q = mysql_query("update akademska_godina set id=1 where id=2");
$q = mysql_query("update akademska_godina set id=2 where id=100");

print "ponudakursa...<br/>\n\n";
$q = mysql_query("update ponudakursa set akademska_godina=100 where akademska_godina=1");
$q = mysql_query("update ponudakursa set akademska_godina=1 where akademska_godina=2");
$q = mysql_query("update ponudakursa set akademska_godina=2 where akademska_godina=100");

print "student_studij...<br/>\n\n";
$q = mysql_query("update student_studij set akademska_godina=100 where akademska_godina=1");
$q = mysql_query("update student_studij set akademska_godina=1 where akademska_godina=2");
$q = mysql_query("update student_studij set akademska_godina=2 where akademska_godina=100");




?> 
