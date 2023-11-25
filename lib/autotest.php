<?

// LIB/AUTOTEST - Funkcije vezane za autotestove



// autotest_tabela: Tabelarni pregled rezultata svih testova za korisnika
//   $student - ID studenta
//   $zadaca  - ID zadace
//   $zadatak - redni broj zadatka
//   $nastavnik - ako je true, moguć je pristup neaktivnim i sakrivenim testovima
// Vraća HTML kod tabele

function autotest_tabela($student, $zadaca, $zadatak, $nastavnik, $rok_za_slanje) {
	global $_api_http_code;
	$result = api_call("homework/$zadaca/$zadatak/student/$student/file", [ "filename" => "$zadatak-result.json" ]);
	if ($_api_http_code != "200") return ""; // No test result

	// Get task specification
	$homeworkFiles = api_call("homework/$zadaca/files")["results"];
	$taskFileId = false;
	foreach ($homeworkFiles as $homeworkFile) {
		if ($homeworkFile['type'] == "autotest" && $homeworkFile['assignNo'] == $zadatak)
			$taskFileId = $homeworkFile['id'];
	}
	if (!$taskFileId) return "";
	
	$task = api_call("homework/files/$taskFileId");
	if ($_api_http_code != "200") return ""; // No task specification
	
	
	
	// Kod adaptiran iz render.php, zbog implementacije za $nastavnik i skrivene testove
	
	function tr($k) { return $k; }
	
	$statuses = array(
	array( "id" => "ok", "code" => 1, "label" => tr("OK"), "description" => tr("Test successful") ),
	array( "id" => "symbol", "code" => 2, "label" => "Nije pronađeno", "description" => tr("Required string/symbol not found in code") ),
	array( "id" => "error", "code" => 3, "label" => "Ne može se kompajlirati", "description" => tr("Test code couldn't be compiled") ),
	array( "id" => "too_long", "code" => 4, "label" => "Predugo izvršavanje", "description" => tr("Test took too long to finish") ),
	array( "id" => "crash", "code" => 5, "label" => "Testni program se krahira", "description" => tr("The program crashed") ),
	array( "id" => "wrong", "code" => 6, "label" => "Pogrešan rezultat", "description" => tr("Program output doesn't match expected output") ),
	array( "id" => "profiler", "code" => 7, "label" => "Memorijska greška", "description" => tr("A run-time error was reported by profiler") ),
	array( "id" => "find_fail", "code" => 8, "label" => "Nije pronađen rezultat", "description" => tr("Program output was not found") ),
	array( "id" => "exception", "code" => 9, "label" => tr("Unexpected exception"), "description" => tr("Program throws an exception") ),
	array( "id" => "internal", "code" => 10, "label" => tr("Internal error"), "description" => tr("Internal error with autotester system") ),
	array( "id" => "unzip", "code" => 11, "label" => tr("Not a ZIP file"), "description" => tr("Unzip command failed") ),
	array( "id" => "tool", "code" => 12, "label" => tr("Internal error"), "description" => tr("Internal error - a tool failed to run") ),
	array( "id" => "profiler_ok", "code" => 701, "label" => tr("OK"), "description" => tr("Profiler reported no known errors") ),
	array( "id" => "oob", "code" => 702, "label" => "Memorijska greška", "description" => tr("Memory error (exceeded array/vector size or illegal pointer operation)") ),
	array( "id" => "uninit", "code" => 703, "label" => "Neinicijalizovana promjenljiva", "description" => tr("Program is accessing a variable that wasn't initialized") ),
	array( "id" => "memleak", "code" => 704, "label" => "Curenje memorije", "description" => tr("Allocated memory was not freed") ),
	array( "id" => "invalid_free", "code" => 705, "label" => "Loša dealokacija", "description" => tr("Attempting to free memory that wasn't allocated") ),
	array( "id" => "mismatched_free", "code" => 705, "label" => "Pogrešan dealokator", "description" => tr("Wrong type of deallocation used (delete vs. delete[] ...)") ),
	);
	
	
	$task_enc = htmlspecialchars(json_encode($task));
	$result_enc = htmlspecialchars(json_encode($result));
	
	$rezultat = '<form action="lib/autotester/tools/render/render.php" method="POST" id="details_form">
	<input type="hidden" name="language" value="bs">
	<input type="hidden" name="task" value="' . $task_enc . '">
	<input type="hidden" name="result" value="' . $result_enc . '">
	<input type="hidden" name="test" id="form_test_id" value="0">
	</form>';
	
		$rezultat .= <<<HTML
	<script>
	function showDetail(id) {
		document.getElementById('form_test_id').value = "" + id;
		document.getElementById('details_form').submit();
		return false;
	}
	</script>
	
	<table border="1" cellspacing="0" cellpadding="2">
		<thead><tr>
			<th>Test</th>
			<th>Rezultat</th>
			<th>Vrijeme testiranja</th>
			<th>&nbsp;</th>
		</tr></thead>
HTML;
	
	$no = 0;
	foreach($task['tests'] as $test) {
		if (array_key_exists('options', $test) && in_array("silent", $test['options'])) continue;
		if (!array_key_exists($test['id'], $result['test_results'])) continue;
		
		// Da li prikazati skrivene testove?
		if (array_key_exists('options', $test) && in_array("hidden", $test['options']) && !$nastavnik && $rok_za_slanje > time())
			continue;
		
		$tr = $result['test_results'][$test['id']];
		if ($tr['status'] == 1)
			$icon = '<img src="static/images/16x16/ok.png" width="8" height="8">';
		else
			$icon = '<img src="static/images/16x16/not_ok.png" width="8" height="8">';
			
		// Get detailed status text for profiler errors
		if ($tr['status'] == 7) {
			foreach($tr['tools'] as $key => $value)
				if (substr($key, 0, 7) == "profile" && $value['status'] != 1)
					$tr['status'] = 700 + $value['status'];
		}

		// Get status text
		$status_text = "Ok";
		if (array_key_exists('options', $test) && in_array("nodetail", $test['options']) && $tr['status'] != 1)
			$status_text = "Not ok";
		else foreach($statuses as $st)
			if ($tr['status'] == $st['code'])
				$status_text = $st['label'];
		
		// Gray color for hidden tests
		if (array_key_exists('options', $test) && (in_array("nodetail", $test['options']) || in_array("hidden", $test['options'])))
			$class = "style=\"color: #777\"";
		else
			$class = "";
		$no++;
		
		$nicetime = date("d. m. Y H:i:s", $result['time']);
		
		$rezultat .= "<tr>
			<td $class>$no</td>
			<td $class>$icon $status_text</td>
			<td $class>$nicetime</td>
			<td>
				<a href=\"#\" onclick=\"return showDetail(".$test['id'].");\">Detalji</a>
			</td>
		</tr>";
	}
	
	$rezultat .= "\n</table>\n";
	return $rezultat;
}


// autotest_brisi_rezultate: Briše rezultate svih testova za datog studenta, zadaću i zadatak
//   $student - ID studenta
//   $zadaca  - ID zadace
//   $zadatak - redni broj zadatka

function autotest_brisi_rezultate($student, $zadaca, $zadatak) {
	global $_api_http_code;
	$result = api_call("homework/$zadaca/$zadatak/student/$student/file", [ "filename" => "$zadatak-result.json" ], "DELETE" );
	if ($_api_http_code != "204") {
		niceerror("Neuspješno brisanje rezultata testiranja");
		api_report_bug($result, [ "filename" => "$zadatak-result.json" ]);
	}
}


// autotest_status_display: Na ekranu ispisuje plutajući layer sa statusom na zadaći
//   $student - ID studenta
//   $zadaca  - ID zadace
//   $zadatak - redni broj zadatka
//   $assignment - Assignment object (from API)
//   $nastavnik - ako je true, moguć je pristup neaktivnim i sakrivenim testovima

function autotest_status_display($student, $zadaca, $zadatak, $assignment, $nastavnik) {
	global $_api_http_code;
	$stat_tekst = array("Bug u programu", "Pregled u toku", "Prepisana", "Bug u programu", "Pregled u toku", "Zadaća OK", "Potrebna odbrana");
	$status_zadace = $assignment['status'];
	$bodova = $assignment['score'];

//if (($zadaca == 5460 || $zadaca == 5461) && $status_zadace == 2) { $status_zadace=4; $imakomentar = ""; }
	if ($status_zadace == 3) {
		$bgcolor = "#fcc";
		$status_duzi_tekst = "<b>Ne može se kompajlirati</b>";
		$status_ikona = "bug";
	}
	else if ($status_zadace == 2 || $status_zadace == 6) {
		$bgcolor = "#fcc";
		if ($status_zadace == 2)
			$status_duzi_tekst = "<b>Zadaća je prepisana</b>";
		else
			$status_duzi_tekst = "<b>Potrebna je odbrana zadaće</b>";
		$status_ikona = "copy";
	}
	else if ($status_zadace == 1 || $status_zadace == 4) {
		$bgcolor = "#ffc";
		$status_duzi_tekst = "<b>Pregled u toku</b>";
		$status_ikona = "view";
	}
	else if ($status_zadace == 5) {
		$status_duzi_tekst = "<b>Zadaća pregledana: $bodova bodova</b>";
		$status_ikona = "ok";
	}

	// Status testova
	$resultsExist = false;
	$ukupno_testova = $proslo_testova = 0;
	if ($assignment['Homework']['automatedTesting']) {
		$result = api_call("homework/$zadaca/$zadatak/student/$student/file", ["filename" => "$zadatak-result.json"]);
		if ($_api_http_code == "200" && !empty($result)) {
			$homeworkFiles = api_call("homework/$zadaca/files")["results"];
			$taskFileId = false;
			foreach ($homeworkFiles as $homeworkFile) {
				if ($homeworkFile['type'] == "autotest" && $homeworkFile['assignNo'] == $zadatak)
					$taskFileId = $homeworkFile['id'];
			}
			if ($taskFileId) {
				$task = api_call("homework/files/$taskFileId");
				if ($_api_http_code == "200")
					$resultsExist = true;
			}
		} else $status_duzi_tekst .= "<br>r1: $_api_http_code";
		
		if ($resultsExist) {
			foreach ($task['tests'] as $test) {
				if (array_key_exists('options', $test) && in_array("silent", $test['options'])) continue;
				$ukupno_testova++;
				if (!array_key_exists($test['id'], $result['test_results'])) continue;
				
				$tr = $result['test_results'][$test['id']];
				if ($tr['status'] == 1) $proslo_testova++;
			}
		}
	}

	if ($status_zadace == 1 || $status_zadace == 3) {
		if ($ukupno_testova > 0)
			$status_duzi_tekst .= "<br>Ispod su stari rezultati testova za prošlu verziju zadaće";
	}
	else if ($status_zadace == 4 || $status_zadace == 5) {
		if ($ukupno_testova > 0 && $ukupno_testova > $proslo_testova) {
			$bgcolor = "#ffc";
			$status_duzi_tekst .= ". <b>".($ukupno_testova-$proslo_testova)." od $ukupno_testova testova nije prošlo</b>";
		}
		else if ($ukupno_testova > 0) {
			$bgcolor = "#cfc";
			$status_duzi_tekst .= ". <b>Prošli svi testovi</b>";
		} else if ($status_zadace == 5) {
			$bgcolor = "#cfc";
		} else {
			$bgcolor = "#ffc";
		}
	}

	?>
	<table width="95%" style="border: 1px solid silver; background-color: <?=$bgcolor?>" cellpadding="5">
	<tr><td align="center">
		<p>Status zadaće:<br>
		<img src="static/images/16x16/<?=$status_ikona?>.png" width="16" height="16" border="0" align="center" title="<?=$stat_tekst[$status_zadace]?>" alt="<?=$stat_tekst[$status_zadace]?>"> <?=$status_duzi_tekst?></p>
	</td></tr>
	</table>
	<?
}

?>
