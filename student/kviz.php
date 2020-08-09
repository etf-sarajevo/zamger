<?

// STUDENT/KVIZ - spisak kvizova ponuđenih studentu



function student_kviz() {

	global $userid, $_api_http_code;
	
	
	// Poslani parametri
	$predmet = int_param('predmet');
	$ag = int_param('ag');
	
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		// Zbog automatskog reloadanja ovog prozora dok se popunjava kviz, dolazilo je do otvaranja
		// dijaloga za "resubmit" što je znalo dovesti do prekida popunjavanja kviza
		?>
		<script language="JavaScript">
		location.href='?sta=student/kviz&predmet=<?=$predmet?>&ag=<?=$ag?>';
		</script>
		<?
		return 0;
	}
	
	
	?>
	<h2>Kvizovi</h2>
	<?php
	
	
	// List of all currently open quizzes for student
	$allQuizzes = api_call("quiz/latest/$userid", ["checkIp" => "true"])['results'];
	$quizzes = [];
	// Find quizzes on selected course
	foreach($allQuizzes as $quiz) {
		if ($quiz['CourseUnit']['id'] == $predmet && $quiz['AcademicYear']['id'] == $ag)
			$quizzes[] = $quiz;
	}
	if (empty($quizzes)) {
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
	foreach($quizzes as $quiz) {
		// Da li je ip adresa u datom rasponu
		if ($quiz['ipAddressRanges'] != "") {
			if (!$quiz['available']) {
				print "<li>" . $quiz['name']  . " - kviz je nedostupan sa vaše adrese</li>\n";
				continue;
			}
		}
		
		// Da li je student već popunjavao ovaj kviz
		$quizResult = api_call("quiz/" . $quiz['id'] . "/student/$userid");
		if ($_api_http_code != "404") {
			if ($quizResult['finished']) {
				$bodova = $quizResult['score'];
				if ($bodova >= $quiz['passPoints']) $cestitka = " Čestitamo!"; else $cestitka = "";
				print "<li>" . $quiz['name']  . " - završen, osvojili ste $bodova bodova. $cestitka</li>\n";
			}
			else {
				print "<li>" . $quiz['name']  . " - nedovršen</li>\n";
			}
			continue;
		}
	
		print "<li><a href=\"#\" onclick=\"otvoriKviz(" . $quiz['id'] . ");\">" . $quiz['name'] . "</a></li>\n";
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
