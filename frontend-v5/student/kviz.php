<?

// STUDENT/KVIZ - spisak kvizova ponuđenih studentu

function student_kviz() {

global $userid;


require_once("Config.php");

// Backend stuff
require_once(Config::$backend_path."core/CourseUnit.php");
require_once(Config::$backend_path."core/AcademicYear.php");
require_once(Config::$backend_path."core/Portfolio.php");
require_once(Config::$backend_path."core/Util.php");

// Ova skripta je dio modula lms/quiz tako da ovo ispod ne mora biti opcionalno
require_once(Config::$backend_path."lms/quiz/Quiz.php");
require_once(Config::$backend_path."lms/quiz/QuizResult.php");

// Akcije
if ($_REQUEST['akcija'] == "slanje") {
	akcijaslanje();
}


// Poslani parametri
$predmet = intval($_REQUEST['predmet']);
$ag = intval($_REQUEST['ag']);

$cu = CourseUnit::fromId($predmet);
$ay = AcademicYear::fromId($ag);
$pf = Portfolio::fromCourseUnit($userid, $predmet, $ag);


print "<h2>Kvizovi</h2>\n";


$kvizovi = Quiz::getLatestForStudent($userid);
if (count ($kvizovi) == 0) {
	print "Trenutno nema aktivnih kvizova za ovaj predmet.";
	return;
}

// Spisak kvizova
?>
<script language="JavaScript">
function otvoriKviz(k) {
	if (/*@cc_on!@*/false) { // check for Internet Explorer
		window.open('index.php?sta=student/popuni_kviz&kviz='+k, 'Kviz', 'fullscreen,scrollbars'); 
	} else {
		var sir = screen.width;
		var vis = screen.height;
		mywindow = window.open('index.php?sta=student/popuni_kviz&kviz='+k, 'Kviz', 'status=0,toolbar=0,location=0,menubar=0,directories=0,resizable=0,scrollbars=1,width='+sir+',height='+vis); 
		mywindow.moveTo(0,0); 
		setTimeout('window.location.reload();', 5000);
	}
}
</script>

<div id="spisak_kvizova">
<p>Trenutno su aktivni kvizovi:</p>
<ul>
<?

foreach ($kvizovi as $kviz) {
	if ($kviz->ipAddressRanges != "") {
		if ( ! Quiz::isIpInRange( Util::getip(), $kviz->ipAddressRanges ) ) {
			print "<li>".$kviz->name." - kviz je nedostupan sa vaše adrese</li>\n";
			continue;
		}
	}

	// Da li je student već popunjavao ovaj kviz
	try {
		$res = QuizResult::fromStudentAndQuiz($userid, $kviz->id);
		print "<li>".$kviz->name." - ";
		if ($res->finished) {
			print "završen, osvojili ste ".$res->score." bodova.";
			if ($res->score >= $kviz->passPoints) // prolaz
				print " Čestitamo!";
			print "</li>\n";
		} else {
			print "nedovršen</li>\n";
		}
	} catch(Exception $e) {
		// Student nije popunjavao kviz
		print "<li><a href=\"#\" onclick=\"otvoriKviz(".$kviz->id.");\">".$kviz->name."</a></li>\n";
	}
}
	
print "</ul>\n";


?>
<p>Kliknite na naziv kviza da pristupite popunjavanju kviza.</p>
<br>
<p><b><font color="red">VAŽNA NAPOMENA</font></b>: Kada započnete popunjavanje kviza ne smijete se prebaciti na drugi prozor! Svaki pokušaj da računar koristite za bilo šta osim popunjavanje kviza može izazvati prekid kviza bez mogućnosti kasnijeg ponovnog popunjavanja.</p>
<p><a href="#" onclick="window.close();">Zatvorite ovaj prozor</a></p>
</div>


<!--div id="nema_js">
Za pristup kvizovima potrebno je da aktivirate JavaScript u vašem web pregledniku.
</div-->
<?


}



?>
