<?



function stud_download() {

global $userid,$system_path,$predmet_id;



# Poslani parametar:
$zadaca = intval($_GET['zadaca']);

// Da li neko pokušava da spoofa zadaću?
if ($zadaca!=0) {
	$q01 = myquery("SELECT count(*) FROM zadaca, labgrupa, student_labgrupa as sl
	WHERE sl.student=$userid and sl.labgrupa=labgrupa.id and labgrupa.predmet=zadaca.predmet and zadaca.id=$zadaca");
	if (mysql_result($q01,0,0)==0) {
		print niceerror("Ova zadaća nije iz vašeg predmeta!?");
		return;
	}
}

$zadatak = intval($_GET['zadatak']);

if ($zadaca == 0 || $zadatak == 0) {
	print niceerror("Neispravan zadatak.");
}

$lokacijazadaca="$system_path/zadace/$predmet_id/$userid/$zadaca/";

$q02 = myquery("select filename from zadatak where zadaca=$zadaca and redni_broj=$zadatak and student=$userid and status=1 order by id desc limit 1");
if (mysql_num_rows($q02) < 1) {
	print niceerror("Ova zadaća nije iz vašeg predmeta!?");
	return;
}

$filename = mysql_result($q02,0,0);
$filepath = $lokacijazadaca.$filename;

$type = `file -bi $filepath`;
header("Content-Type: $type");
header('Content-Disposition: attachment; filename=' . $filename, false);

$k = readfile($filepath,false);
if ($k == false) print "FALSE";
exit;

}

?>