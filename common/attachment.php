<?



// COMMON/ATTACHMENT - download zadaće poslane u formi attachmenta

// v3.9.1.0 (2008/02/12) + Preimenovan bivsi stud_download, uz merge dijela koda iz admin_pregled
// v3.9.1.1 (2008/10/22) + Ovaj kod se obajatio :) prepravljeno $uloga na $user_* varijable; omoguceno nastavniku da otvara attachmente studenata cak i ako je istovremeno i student; tabela student_predmet umjesto relacije preko labgrupe; conf_files_path
// v4.0.0.0 (2009/02/19) + Release
// v4.0.9.1 (2009/03/25) + nastavnik_predmet preusmjeren sa tabele ponudakursa na tabelu predmet
// v4.0.9.2 (2009/04/01) + Tabela zadaca preusmjerena sa ponudakursa na tabelu predmet


function common_attachment() {

global $userid,$conf_files_path,$user_student,$user_nastavnik,$user_siteadmin;


// Poslani parametar

$zadaca=intval($_REQUEST['zadaca']);
$zadatak=intval($_REQUEST['zadatak']);

if ($zadaca == 0 || $zadatak == 0) {
	zamgerlog("los poziv (zadaca $zadaca zadatak $zadatak)",3); // nivo 3: greska
	niceerror("Neispravan zadatak.");
	return;
}



$stud_id=intval($_REQUEST['student']);

// Određujemo ID ponudekursa

$q5 = myquery("select pk.id from ponudakursa as pk, zadaca as z where pk.predmet=z.predmet and pk.akademska_godina=z.akademska_godina and z.id=$zadaca");
if (mysql_num_rows($q5)<1) {
	zamgerlog("nepostojeca zadaca $zadaca",3);
	niceerror("Nepostojeća zadaća");
	return;
}
$ponudakursa = mysql_result($q5,0,0);


// Prava pristupa

if ($stud_id==0) { // student otvara vlastitu zadacu
	if ($user_student)
		$stud_id=$userid;
	else {
		zamgerlog("pokusao otvoriti attachment bez ID studenta, a sam nije student",3);
		niceerror("Čiju zadaću pokušavate otvoriti?");
		return;
	}

} else { // student je odredjen kao parametar
	if (!$user_nastavnik && !$user_siteadmin) {
		zamgerlog("attachment: nije nastavnik (student u$stud_id zadaca z$zadaca)",3);
		niceerror("Nemate pravo pregleda ove zadaće");
		return;
	}

	if (!$user_siteadmin) {
		$q10 = myquery("select pk.id from nastavnik_predmet as np, zadaca as z, ponudakursa as pk where z.id=$zadaca and z.predmet=pk.predmet and z.akademska_godina=pk.akademska_godina and pk.predmet=np.predmet and np.nastavnik=$userid and pk.akademska_godina=np.akademska_godina"); // POJEDNOSTAVITI!
		if (mysql_num_rows($q10)<1) {
			zamgerlog("attachment: nije nastavnik na predmetu (student u$stud_id zadaca z$zadaca)",3);
			niceerror("Nemate pravo pregleda ove zadaće");
			return;
		}
		
		// Provjera ogranicenja
		$q20 = myquery("select o.labgrupa from ogranicenje as o, labgrupa as l where o.nastavnik=$userid and o.labgrupa=l.id and l.predmet=$ponudakursa");
		if (mysql_num_rows($q20)>0) {
			$nasao=0;
			while ($r20 = mysql_fetch_row($q20)) {
				$q25 = myquery("select count(*) from student_labgrupa where student=$stud_id and labgrupa=$r20[0]");
				if (mysql_result($q25,0,0)>0) { $nasao=1; break; }
			}
			if ($nasao == 0) {
				zamgerlog("ogranicenje na predmet (student u$stud_id predmet p$ponudakursa)",3);
				niceerror("Nemate pravo pregleda ove zadaće");
				return;
			}
		}
	}
}


// Da li neko pokušava da spoofa zadaću?

$q30 = myquery("SELECT z.predmet FROM zadaca as z, student_predmet as sp, ponudakursa as pk WHERE sp.student=$stud_id and sp.predmet=pk.id and pk.predmet=z.predmet and pk.akademska_godina=z.akademska_godina and z.id=$zadaca");
if (mysql_num_rows($q30)<1) {
	zamgerlog("student nije upisan na predmet (student u$stud_id zadaca z$zadaca)",3);
	niceerror("Student nije upisan na predmet");
	return;
}


// Preuzimanje zadaće

$lokacijazadaca="$conf_files_path/zadace/$ponudakursa/$stud_id/$zadaca/";

$q40 = myquery("select filename from zadatak where zadaca=$zadaca and redni_broj=$zadatak and student=$stud_id and status=1 order by id desc limit 1");
if (mysql_num_rows($q40) < 1) {
	zamgerlog("ne postoji attachment (zadaca $zadaca zadatak $zadatak student $stud_id)",3);
	niceerror("Ne postoji attachment");
	return;
}

$filename = mysql_result($q40,0,0);
$filepath = $lokacijazadaca.$filename;

$type = `file -bi '$filepath'`;
header("Content-Type: $type");
header('Content-Disposition: attachment; filename=' . $filename, false);

$k = readfile($filepath,false);
if ($k == false) print "FALSE";
exit;

}

?>