<?

// STUDENT/MOODLE - modul za redirekciju na odgovarajuću moodle stranicu



function student_moodle() {

global $conf_moodle, $conf_moodle_url;

if (!$conf_moodle) {
	biguglyerror("Moodle integracija nije uključena.");
	print "Kontaktirajte vašeg administratora.";
	return;
}

$predmet = intval($_REQUEST['predmet']);
$ag = intval($_REQUEST['ag']);

$tip = $_REQUEST['tip'];

$q = db_query("select moodle_id from moodle_predmet_id where predmet=$predmet and akademska_godina=$ag");
if (db_num_rows($q)<1) {
	zamgerlog("ne postoji moodle ID za predmet pp$predmet, ag$ag", 3);
	zamgerlog2("ne postoji moodle ID za predmet", $predmet, $ag);
	niceerror("Pogrešan predmet/akademska_godina ili za ovaj predmet nije definisan moodle ID.");
	return;
}
$moodle_id = db_result($q,0,0);

if ($tip == "forum")
	header("Location: $conf_moodle_url"."mod/forum/index.php?id=$moodle_id");
else
	header("Location: $conf_moodle_url"."course/view.php?id=$moodle_id");

}

?>
