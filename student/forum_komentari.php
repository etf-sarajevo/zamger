<?

// STUDENT/MOODLE - modul za redirekciju na moodle

// v4.0.9.1 (2009/05/01) + Kreiran modul kako bi se ukinuo stari kljakavi sistem studentskih modula


function student_forum_komentari() {

$predmet = intval($_REQUEST['predmet']);
$ag = intval($_REQUEST['ag']);

$q = myquery("select moodle_id from etf_moodle where predmet=$predmet and akademska_godina=$ag");
if (mysql_num_rows($q)<1) {
	zamgerlog("ne postoji moodle ID za predmet pp$predmet, ag$ag", 3);
	niceerror("Pogrešan predmet/akademska_godina ili za ovaj predmet nije definisan moodle ID.");
	return;
}
$moodle_id = mysql_result($q,0,0);

header("Location: http://c2.etf.unsa.ba/mod/forum/index.php?id=$moodle_id");
}

?>
