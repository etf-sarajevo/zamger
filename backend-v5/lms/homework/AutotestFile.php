<?php

// Modul: lms/homework
// Klasa: AutotestFile
// Opis: .autotest fajl za zadatak


require_once(Config::$backend_path."core/CourseUnitYear.php");
require_once(Config::$backend_path."lms/homework/Homework.php");

class AutotestFile {
	public $id;
	public $name, $language, $required_compiler, $preferred_compiler, $compiler_features, $compiler_options, $compiler_options_debug;
	public $compile, $run, $test, $test_specifications;
	
	public static function fromHomeworkNumber($homeworkId, $assignmentNumber) {
		$hw = Homework::fromId($homeworkId);
		if ($assignmentNumber < 1 || $assignmentNumber > $hw->nrAssignments)
			throw new Exception("Unknown assignment number $assignmentNumber", "404");
		if (!$hw->automatedTesting)
			throw new Exception("Homework $homeworkId isn't specified for testing", "403");
			
		$hw->CourseUnit->resolve();
		$hw->AcademicYear->resolve();
		$hw->ProgrammingLanguage->resolve();
			
		$af = new Autotestfile;
		$af->id = $homeworkId * 100 + $assignmentNumber;
		$af->name = $hw->CourseUnit->name . " (" . $hw->AcademicYear->name . "), " . $hw->name;
		if ($hw->nrAssignments > 1) $af->name .= ", zadatak $assignmentNumber";
		
		$af->language = $hw->ProgrammingLanguage->name;
		// FIXME
		if ($af->language == "C++11") $af->language = "C++";
		
		// FIXME (add features?)
		$af->required_compiler = $hw->ProgrammingLanguage->compilerName;
		$af->preferred_compiler = $hw->ProgrammingLanguage->compilerName;
		$af->compiler_features = array();
		$af->compiler_options = $hw->ProgrammingLanguage->compilerOptions;
		$af->compiler_options_debug = $hw->ProgrammingLanguage->compilerDebugOptions;
		
		$af->compile = "true";
		$af->run = "false";
		$af->test = "true";
		$af->debug = "true";
		$af->profile = "true";
		if ($af->language == "Python") {
			$af->debug = "false";
			$af->profile = "false";
		}
		if ($af->language == "Matlab .m") {
			$af->compile = "false";
			$af->profile = "false";
		}
		
		$af->test_specifications = array();
		
		// Tests
		$q3 = DB::query("SELECT tip, specifikacija, zamijeni FROM autotest_replace WHERE zadaca=$homeworkId AND zadatak=$assignmentNumber");
		$replace_symbols = array();
		$require_symbols = array();
		while ($r3 = DB::fetch_row($q3)) {
			if ($r3[2] === "")
				array_push($require_symbols, $r3[1]);
			else {
				$replace = array();
				$replace['type'] = $r3[0];
				$replace['match'] = $r3[1];
				$replace['replace'] = $r3[2];
				array_push($replace_symbols, $replace);
			}
		}
		
		$q2 = DB::query("SELECT id, kod, rezultat, alt_rezultat, fuzzy, global_scope, pozicija_globala, stdin, partial_match, sakriven FROM autotest WHERE zadaca=$homeworkId AND zadatak=$assignmentNumber AND aktivan=1");
		while ($r2 = DB::fetch_row($q2)) {
			// Studentima ne prikazujemo sakrivene testove?
			if ($r2[9]==1 && !AccessControl::privilege('siteadmin') && !teacherLevel($hw->CourseUnit->id, $hw->AcademicYear->id))
				continue;
			
			$test = array();
			$test['id'] = $r2[0];
			$test['require_symbols'] = $require_symbols;
			$test['replace_symbols'] = $replace_symbols;
			$test['code'] = $r2[1];
			
			if ($r2[6] === 'prije_maina') {
				$test['global_above_main'] = $r2[5];
				$test['global_top'] = "";
			} else {
				$test['global_top'] = $r2[5];
				$test['global_above_main'] = "";
			}
			
			$test['running_params'] = array();
			$test['running_params']['timeout'] = 10; // TODO hardcodirano 10 sekundi
			$test['running_params']['vmem'] = 1000000; // TODO hardcodirano ~200 MB
			$test['running_params']['stdin'] = $r2[7];
			
			$test['expected'] = array();
			if ($r2[2] === "===IZUZETAK===") // TODO dodati switch u GUI, tip izuzetka
				$test['expected_exception'] = "true";
			else {
				$test['expected_exception'] = "false";
				array_push($test['expected'], $r2[2]);
				if ($r2[3] !== "") array_push($test['expected'], $r2[3]);
			}

			$test['expected_crash'] = "false"; // TODO implementirati
			
			// TODO Napraviti sve kao jedan dropdown
			$test['ignore_whitespace'] = "false";
			$test['regex'] = "false";
			if ($r2[4] === "1")
				$test['ignore_whitespace'] = "true";
			else if ($r2[4] === "2")
				$test['regex'] = "true";
			
			if ($r2[8] === "1")
				$test['substring'] = "true";
			else
				$test['substring'] = "false";
				
			array_push($af->test_specifications, $test);
		}
		
		return $af;
	}
	
	public function update($homeworkId, $assignmentNumber) {
		$hw = Homework::fromId($homeworkId);
		if ($assignmentNumber < 1 || $assignmentNumber > $hw->nrAssignments)
			throw new Exception("Unknown assignment number $assignmentNumber", "404");
		if (!$hw->automatedTesting)
			throw new Exception("Homework $homeworkId isn't specified for testing", "403");
		
		$this->id = $homeworkId * 100 + $assignmentNumber;
		
		DB::query("DELETE FROM autotest WHERE zadaca=$homeworkId AND zadatak=$assignmentNumber");
		
		// FIXME require_symbols, replace_symbols
		
		$this->test_specifications = (array) $this->test_specifications;
		foreach($this->test_specifications as $test) {
			$test = (array) $test;
			$test['expected'] = (array) $test['expected'];
			$test['running_params'] = (array) $test['running_params'];
			if (count($test['expected'])>1) $alt_rezultat=$test['expected'][1]; else $alt_rezultat="";
			if (!empty($test['global_top'])) {
				$global = $test['global_top'];
				$pozicija_globala = 'prije_svega';
			} else if (!empty($test['global_above_main'])) {
				$global = $test['global_above_main'];
				$pozicija_globala = 'prije_maina';
			} else {
				$global = "";
				$pozicija_globala = 'prije_svega';
			}
			if ($test['substring'] == 'true')
				$partial = 1;
			else
				$partial = 0;
			if ($test['regex'] == 'true')
				$fuzzy = 2;
			else if ($test['ignore_whitespace'] == 'true')
				$fuzzy = 1;
			else
				$fuzzy = 0;
			$q195 = DB::query("INSERT INTO autotest SET zadaca=$homeworkId, zadatak=$assignmentNumber, kod='".DB::escape_string($test['code'])."', rezultat='".DB::escape_string($test['expected'][0])."', alt_rezultat='".DB::escape_string($alt_rezultat)."', fuzzy=$fuzzy, global_scope='".DB::escape_string($global)."', pozicija_globala='$pozicija_globala', stdin='".DB::escape_string($test['running_params']['stdin'])."', partial_match=$partial");
		}
	}

}

?>
