<?



// COMMON/ATTACHMENT - download zadaće poslane u formi attachmenta

// v3.9.1.0 (2008/02/12) + Preimenovan bivsi stud_download, uz merge dijela koda iz admin_pregled


function common_attachment() {

global $userid,$conf_system_path,$uloga;


// Poslani parametar

$zadaca=intval($_REQUEST['zadaca']);
$zadatak=intval($_REQUEST['zadatak']);

if ($zadaca == 0 || $zadatak == 0) {
	zamgerlog("los poziv (zadaca $zadaca zadatak $zadatak)",3); // nivo 3: greska
	niceerror("Neispravan zadatak.");
	return;
}


// Prava pristupa

if ($uloga=="S") { // student
	$stud_id=$userid;
} else if ($uloga=="N") {
	$stud_id=intval($_REQUEST['student']);

	if ($admin!=3) { // 3 = site admin
		$q10 = myquery("select np.predmet,l.id from nastavnik_predmet as np, labgrupa as l, student_labgrupa as sl where np.nastavnik=$userid and np.predmet=l.predmet and l.id=sl.labgrupa and sl.student=$stud_id and z.id=$zadaca and z.predmet=np.predmet");
		if (mysql_num_rows($q10)<1) {
			zamgerlog("privilegije (student $stud_id zadaca $zadaca)",3);
			niceerror("Nemate pravo pregleda ove zadaće");
			return;
		}
		$predmet_id = mysql_result($q10,0,0);
		$labgrupa = mysql_result($q10,0,1);
		
		$q20 = myquery("select o.labgrupa from ogranicenje as o, labgrupa as l where o.nastavnik=$userid and o.labgrupa=l.id and l.predmet=$predmet_id");
		if (mysql_num_rows($q20)>0) {
			$nasao=0;
			while ($r20 = mysql_fetch_row($q20)) {
				if ($r20[0] == $grupa_id) { $nasao=1; break; }
			}
			if ($nasao == 0) {
				zamgerlog("ogranicenje na predmet (student $stud_id predmet $predmet_id)",3);
				niceerror("Nemate pravo pregleda ove zadaće");
				return;
			}
		}
	}
}


// Da li neko pokušava da spoofa zadaću?

$q30 = myquery("SELECT count(*) FROM zadaca as z, labgrupa as l, student_labgrupa as sl WHERE sl.student=$stud_id and sl.labgrupa=l.id and l.predmet=z.predmet and z.id=$zadaca");
if (mysql_result($q30,0,0)==0) {
	zamgerlog("student nije upisan na predmet (student $stud_id zadaca $zadaca)",3);
	niceerror("Student nije upisan na predmet");
	return;
}


// Slanje zadaće

$lokacijazadaca="$conf_system_path/zadace/$predmet_id/$stud_id/$zadaca/";

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