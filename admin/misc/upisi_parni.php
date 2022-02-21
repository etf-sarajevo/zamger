<?php



//----------------------------------------
// Upiši sve studente u parni semestar
//----------------------------------------

// Ovaj modul se koristi kada studentska služba objavi da ne mogu stići obraditi sva dokumenta na vrijeme do početka
// parnog semestra pa da ja kroz sistem sve studente koji su bili upisani u neparni semestar automatski
// upišem u parni

function admin_misc_upisi_parni() {
	global $_api_http_code;
	
	if (param('akcija') == "upisi_parni" && check_csrf_token()) {
		$ispis = (int_param('fakatradi') != 1);
		
		$greska = 0;
		
		// Ovaj spisak ne postoji na apiju a nije ga smisleno dodati
		$q10 = db_query("SELECT student, studij, semestar, ss.akademska_godina, nacin_studiranja, plan_studija FROM student_studij ss, akademska_godina ag WHERE ss.akademska_godina=ag.id AND ag.aktuelna=1 AND ss.semestar MOD 2 = 1 AND status_studenta=1");
		print "<ul>";
		while (db_fetch6($q10, $student, $studij, $semestar, $godina, $nacin_studiranja, $plan_studija)) {
			$ponovac = true;
			$status = 1;
			$semestar++;
			$enrollment = array_to_object([ "student" => [ "id" => $student], "Programme" => [ "id" => $studij], "semester" => $semestar, "AcademicYear" => [ "id" => $godina], "repeat" => $ponovac, "status" => $status ]);
			$enrollment->EnrollmentType = [ "id" => $nacin_studiranja ];
			$enrollment->Curriculum = [ "id" => $plan_studija ];
			$enrollment->dryRun = $ispis;
			
			$newEnrollment = api_call("enrollment/$student", $enrollment, "POST");
			if ($_api_http_code == "201") {
				$studentFullname = $newEnrollment['student']['surname'] . " " . $newEnrollment['student']['name'] . " (" . $newEnrollment['student']['studentIdNr'] . ")";
				
				nicemessage("Student $studentFullname ($student) je upisan na studij " . $newEnrollment['Programme']['name'] . ", $semestar. semestar u akademskoj " . $newEnrollment['AcademicYear']['name'] . " godini");
				
				// Enroll into courses
				foreach($newEnrollment['enrollCourses'] as $cuy) {
					if ($ispis) { print "* Student upisan na predmet " . $cuy['courseName'] . "<br>"; continue; }
					$result = api_call("course/" . $cuy['CourseUnit']['id'] . "/" . $cuy['AcademicYear']['id'] . "/enroll/$student", [], "POST");
					if ($_api_http_code == "201") {
						print "* Student upisan na predmet " . $cuy['courseName'] . "<br>";
					} else if ($_api_http_code == "403") {
						print "* Student je već od ranije upisan na predmet " . $cuy['courseName'] . "<br>";
					} else {
						niceerror("Neuspješan upis studenta na predmet: " . $result['message']);
					}
				}
				foreach($newEnrollment['failedCourses'] as $cuy) {
					if ($ispis) { print "* Student upisan na preneseni predmet " . $cuy['courseName'] . "<br>"; continue; }
					$result = api_call("course/" . $cuy['CourseUnit']['id'] . "/" . $cuy['AcademicYear']['id'] . "/enroll/$student", [], "POST");
					if ($_api_http_code == "201") {
						print "* Student upisan na preneseni predmet " . $cuy['courseName'] . "<br>";
					} else if ($_api_http_code == "403") {
						print "* Student je već od ranije upisan na preneseni predmet " . $cuy['courseName'] . "<br>";
					} else {
						niceerror("Neuspješan upis studenta na predmet: " . $result['message']);
					}
				}
				
				
			} else if($_api_http_code == "400" && starts_with($newEnrollment['message'], "Already enrolled")) {
				if ($semestar % 2 == 0) $rijec = "ljetnji"; else $rijec = "zimski";
				niceerror("Student $student je već upisan u $rijec semestar tekuće akademske godine!");
			} else {
				niceerror("Neuspješan upis studenta na studij: " . $newEnrollment['message']);
			}
		}
		print "</ul>";
		
		// Potvrda i Nazad
		if ($ispis) {
			?>
			<?=genform("POST")?>
			<input type="hidden" name="fakatradi" value="1">
			<?
			print '<input type="submit" name="nazad" value=" Nazad "> ';
			if ($greska==0) print '<input type="submit" value=" Potvrda ">';
			print "</form>";
			return;
		} else {
			?>
			Svi studenti upisani u parni semestar.
			<?
		}
		return;
	}
	
	?>
		<p><hr/></p>
		
		<p><?=genform("POST");?>
			<input type="hidden" name="akcija" value="upisi_parni">
			<input type="hidden" name="fakatradi" value="0">
			<input type="submit" value="Upiši sve studente u parni semestar">
			</form></p>
	
	<?
}