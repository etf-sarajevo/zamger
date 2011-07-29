<?

// STUDENT/MOODLE - modul za redirekciju na moodle


function student_moodle() {


global $conf_moodle, $conf_moodle_url;

require_once("Config.php");
require_once(Config::$backend_path."lms/moodle/MoodleConfig.php");
require_once(Config::$backend_path."lms/moodle/MoodleID.php");

if (!MoodleConfig::$moodle) {
	biguglyerror("Moodle integracija nije uključena.");
	print "Kontaktirajte vašeg administratora.";
	return;
}

$predmet = intval($_REQUEST['predmet']);
$ag = intval($_REQUEST['ag']);

$tip = $_REQUEST['tip'];

$moodle_id = MoodleID::getMoodleId($predmet, $ag);

if ($tip == "forum")
	header("Location: ".MoodleConfig::$url."mod/forum/index.php?id=$moodle_id");
else
	header("Location: ".MoodleConfig::$url."course/view.php?id=$moodle_id");

}

?>
