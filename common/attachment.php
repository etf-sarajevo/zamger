<?



// COMMON/ATTACHMENT - download zadaće poslane u formi attachmenta

// v3.9.1.0 (2008/02/12) + Preimenovan bivsi stud_download, uz merge dijela koda iz admin_pregled
// v3.9.1.1 (2008/10/22) + Ovaj kod se obajatio :) prepravljeno $uloga na $user_* varijable; omoguceno nastavniku da otvara attachmente studenata cak i ako je istovremeno i student; tabela student_predmet umjesto relacije preko labgrupe; conf_files_path
// v4.0.0.0 (2009/02/19) + Release
// v4.0.0.1 (2009/04/01) + Logicka greska je onemogucavala admina da otvori attachment


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


// Prava pristupa
$stud_id=intval($_REQUEST['student']);

if ($stud_id==0) { // student otvara vlastitu zadacu
	if ($user_student)
		$stud_id=$userid;
	else {
		zamgerlog("pokusao otvoriti attachment bez ID studenta, a sam nije student");
		niceerror("Čiju zadaću pokušavate otvoriti?");
		return;
	}

} else { // student je odredjen kao parametar
	if (!$user_nastavnik && !$user_siteadmin) {
		zamgerlog("attachment: nije nastavnik (student u$stud_id zadaca z$zadaca)");
		niceerror("Nemate pravo pregleda ove zadaće");
		return;
	}

	if (!$user_siteadmin) {
		$q10 = myquery("select np.predmet from nastavnik_predmet as np, zadaca as z where z.id=$zadaca and z.predmet=np.predmet and np.nastavnik=$userid");
		if (mysql_num_rows($q10)<1) {
			zamgerlog("attachment: nije nastavnik na predmetu (student u$stud_id zadaca z$zadaca)",3);
			niceerror("Nemate pravo pregleda ove zadaće");
			return;
		}
		$predmet_id = mysql_result($q10,0,0);
		
		// Provjera ogranicenja
		$q20 = myquery("select o.labgrupa from ogranicenje as o, labgrupa as l where o.nastavnik=$userid and o.labgrupa=l.id and l.predmet=$predmet_id");
		if (mysql_num_rows($q20)>0) {
			$nasao=0;
			while ($r20 = mysql_fetch_row($q20)) {
				$q25 = myquery("select count(*) from student_labgrupa where student=$stud_id and labgrupa=$r20[0]");
				if (mysql_result($q25,0,0)>0) { $nasao=1; break; }
			}
			if ($nasao == 0) {
				zamgerlog("ogranicenje na predmet (student u$stud_id predmet p$predmet_id)",3);
				niceerror("Nemate pravo pregleda ove zadaće");
				return;
			}
		}
	}
}


// Da li neko pokušava da spoofa zadaću?

$q30 = myquery("SELECT z.predmet FROM zadaca as z, student_predmet as sp WHERE sp.student=$stud_id and sp.predmet=z.predmet and z.id=$zadaca");
if (mysql_num_rows($q30)<1) {
	zamgerlog("student nije upisan na predmet (student u$stud_id zadaca z$zadaca)",3);
	niceerror("Student nije upisan na predmet");
	return;
}
$predmet_id = mysql_result($q30,0,0);


// Preuzimanje zadaće

$lokacijazadaca="$conf_files_path/zadace/$predmet_id/$stud_id/$zadaca/";

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