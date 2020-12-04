<?php

if (isset($_REQUEST['language'])) {
	$language_id = basename($_REQUEST['language']);
	if (!empty($language_id) && !preg_match("/\W/", $language_id))
		require("l10n/$language_id.php");
}

function tr($txt) { 
	global $language;
	if (array_key_exists($txt, $language)) return $language[$txt];
	return $txt; 
}

?>
<html>
<head>
	<title><?=tr("Test results")?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" href="render.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<script src="render.js"></script>
	<script src="/static/js/jsdiff/diff.js"></script>
</head>


<body bgcolor="#ffffff">
<?php

if (isset($_REQUEST['title']))
	print "<h2>" . $_REQUEST['title'] . "</h2>\n";

// List of test status code labels
$statuses = array(
	array( "id" => "ok", "code" => 1, "label" => tr("OK"), "description" => tr("Test successful") ),
	array( "id" => "symbol", "code" => 2, "label" => tr("Not found"), "description" => tr("Required string/symbol not found in code") ),
	array( "id" => "error", "code" => 3, "label" => tr("Can't compile"), "description" => tr("Test code couldn't be compiled") ),
	array( "id" => "too_long", "code" => 4, "label" => tr("Timeout"), "description" => tr("Test took too long to finish") ),
	array( "id" => "crash", "code" => 5, "label" => tr("Crashed"), "description" => tr("The program crashed") ),
	array( "id" => "wrong", "code" => 6, "label" => tr("Wrong output"), "description" => tr("Program output doesn't match expected output") ),
	array( "id" => "profiler", "code" => 7, "label" => tr("Run-time error"), "description" => tr("A run-time error was reported by profiler") ),
	array( "id" => "find_fail", "code" => 8, "label" => tr("No output"), "description" => tr("Program output was not found") ),
	array( "id" => "exception", "code" => 9, "label" => tr("Unexpected exception"), "description" => tr("Program throws an exception") ),
	array( "id" => "internal", "code" => 10, "label" => tr("Internal error"), "description" => tr("Internal error with autotester system") ),
	array( "id" => "unzip", "code" => 11, "label" => tr("Not a ZIP file"), "description" => tr("Unzip command failed") ),
	array( "id" => "tool", "code" => 12, "label" => tr("Internal error"), "description" => tr("Internal error - a tool failed to run") ),
	array( "id" => "profiler_ok", "code" => 701, "label" => tr("OK"), "description" => tr("Profiler reported no known errors") ),
	array( "id" => "oob", "code" => 702, "label" => tr("Memory error"), "description" => tr("Memory error (exceeded array/vector size or illegal pointer operation)") ),
	array( "id" => "uninit", "code" => 703, "label" => tr("Uninitialized"), "description" => tr("Program is accessing a variable that wasn't initialized") ),
	array( "id" => "memleak", "code" => 704, "label" => tr("Memory leak"), "description" => tr("Allocated memory was not freed") ),
	array( "id" => "invalid_free", "code" => 705, "label" => tr("Bad deallocation"), "description" => tr("Attempting to free memory that wasn't allocated") ),
	array( "id" => "mismatched_free", "code" => 705, "label" => tr("Wrong deallocator"), "description" => tr("Wrong type of deallocation used (delete vs. delete[] ...)") ),
);

$newlines = array( "\r\n", "\n", "\\n" );



function fatal_error() {
	?>
	<p style="color: red; font-weight: bold"><?=tr("Illegal request")?></p>
	<p><?=tr("If this problem persists, please contact your system administrator.")?></p>
	<?php
	exit(0);
}


function show_table($task, $result) {
	global $statuses, $language_id;
	
	$task_enc = htmlspecialchars(json_encode($task));
	$result_enc = htmlspecialchars(json_encode($result));
	
	?>
	<form action="render.php" method="POST" id="details_form">
	<input type="hidden" name="language" value="<?=$language_id?>">
	<input type="hidden" name="task" value="<?=$task_enc?>">
	<input type="hidden" name="result" value="<?=$result_enc?>">
	<input type="hidden" name="test" id="form_test_id" value="0">
	</form>
	
	<script>
	function showDetail(id) {
		document.getElementById('form_test_id').value = "" + id;
		document.getElementById('details_form').submit();
		return false;
	}
	</script>
	
	<table border="1" cellspacing="0" cellpadding="2">
		<thead><tr>
			<th><?=tr("Test")?></th>
			<th><?=tr("Result")?></th>
			<th><?=tr("Time of testing")?></th>
			<th>&nbsp;</th>
		</tr></thead>
	<?php
	
	$no = 0;
	foreach($task['tests'] as $test) {
		if (array_key_exists('options', $test) && in_array("silent", $test['options'])) continue;
		if (!array_key_exists($test['id'], $result['test_results'])) continue;
		$tr = $result['test_results'][$test['id']];
		if ($tr['status'] == 1) 
			$icon = "<i class=\"fa fa-check\" style=\"color: green\"></i>"; 
		else 
			$icon = "<i class=\"fa fa-times\" style=\"color: red\"></i>"; 
			
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
		if (array_key_exists('options', $test) && in_array("nodetail", $test['options']))
			$class = "gray";
		else
			$class = "";
		$no++;
		
		$nicetime = date(tr("F j, Y, g:i a"), $result['time']);
		
		?>
		<tr>
			<td class="<?=$class?>"><?=$no?></td>
			<td class="<?=$class?>"><?=$icon?> <?=$status_text?></td>
			<td class="<?=$class?>"><?=$nicetime?></td>
			<td>
				<a href="#" onclick="return showDetail(<?=$test['id']?>);"><?=tr("Details")?></a>
			</td>
		</tr>
		<?php
	}
	
	?>
	</table>
	<?php
}

function show_form() {
	global $language_id;
	
	?>
	<form action="render.php" method="POST">
	<input type="hidden" name="language" value="<?=$language_id?>">
	<textarea name="task" rows="10" cols="50"></textarea><br>
	<textarea name="result" rows="10" cols="50"></textarea><br>
	
	<input type="text" name="test" value="0"><br>
	<input type="submit" value=" Go ">
	</form>
	
	<?php
	
}

function escape_output($s) {
	global $newlines;
	$s = htmlspecialchars($s);
	$s = str_replace($newlines, "<br>", $s);
	$s = str_replace(" ", "&nbsp;", $s);
	return $s;
}

function escape_javascript($s) {
	global $newlines;
	$s = str_replace($newlines, "\\n", trim($s));
	$s = str_replace("'", "\'", $s);
	$s = preg_replace("/\s+\\\\n/", "\\n", $s);
	return $s;
}


function message_position($msg) {
	if (array_key_exists('line', $msg)) $result = $msg['line'];
	else $result = "??";
	if (array_key_exists('file', $msg))
		if ($msg['file'] == "TEST_CODE")
			$result .= ", " . tr("test code");
		else
			$result .= ", " . tr("file") . " ".$msg['file'];
	return $result;
}


function generate_report($the_test, $test_result) {
	global $newlines, $statuses;
	
	foreach($statuses as $st)
		if ($st['code'] == $test_result['status']) 
			$status_text = $st['description'];
			
	$report = tr("TEST STATUS: ") . $status_text . "\n\n";
	$raw = "";
	
	if (array_key_exists('debug', $test_result['tools'])) {
		$dr = $test_result['tools']['debug'];
		if (array_key_exists('parsed_output', $dr)) {
			$report .= tr("DEBUGGER MESSAGES:") . "\n";
			foreach($dr['parsed_output'] as $msg)
				$report .= tr("Program crashes in line ") . message_position($msg) . "\n\n";
		}
		if (array_key_exists('output', $dr) && !empty($dr['output']))
			$raw .= tr("DEBUGGER OUTPUT:") . "\n". $dr['output'] . "\n\n";
	}

	if (array_key_exists('profile[memcheck]', $test_result['tools']) && $test_result['tools']['profile[memcheck]']['status'] > 1)
		$pr = $test_result['tools']['profile[memcheck]'];
	else if (array_key_exists('profile[sgcheck]', $test_result['tools']))
		$pr = $test_result['tools']['profile[sgcheck]'];
	else
		$pr = [];

	if (array_key_exists('parsed_output', $pr)) {
		$messages = "";
		foreach($pr['parsed_output'] as $msg) {
			$messages .= tr("Error in line ") . message_position($msg);
			foreach($statuses as $st)
				if ($st['code'] == 700 + $msg['type'])
					$messages .= ":\n" . $st['description'] . "\n\n";
		}
		if ($messages != "") 
			$report .= tr("PROFILER MESSAGES:")."\n$messages";
	}
	if (array_key_exists('output', $pr) && !empty($pr['output']) && $pr['status'] > 1)
		$raw .= tr("PROFILER OUTPUT:") . "\n". $pr['output'] . "\n\n";

	$cr = [];
	if (array_key_exists('compile', $test_result['tools']))
		$cr = $test_result['tools']['compile'];
	else if (array_key_exists('compile[debug]', $test_result['tools']))
		$cr = $test_result['tools']['compile[debug]'];
	if (array_key_exists('parsed_output', $cr)) {
		$messages = "";
		foreach($cr['parsed_output'] as $msg)
			$messages .= tr("Error in line ") . message_position($msg) . ":\n" . $msg['message'] . "\n\n";
		if ($messages != "") 
			$report .= tr("COMPILER MESSAGES:") . "\n$messages";
	}
	if (array_key_exists('output', $cr) && !empty($cr['output']))
		$raw .= tr("COMPILER OUTPUT:") . "\n" . $cr['output'];

	$report .= "\n\n$raw";
	$report = str_replace($newlines, "<br>", $report);
	
	return $report;
}


function show_test($task, $result, $test) {
	global $statuses;

	// Colors
	$input_color  = "#fcc";
	$output_color = "#cfc";


	
	$the_test = array();
	$test_no = 0;
	foreach ($task['tests'] as $t) {
		if (array_key_exists('options', $t) && in_array("silent", $t['options'])) continue;
		if (!array_key_exists($t['id'], $result['test_results'])) continue;
		$test_no++;
		if ($t['id'] == $test) { $the_test = $t; break; }
	}
	
	$test_result = $result['test_results'][$test];

	if ($test_result['status'] == 7) {
		foreach($test_result['tools'] as $key => $value)
			if (substr($key, 0, 7) == "profile" && $value['status'] != 1)
				$test_result['status'] = 700 + $value['status'];
	}

	$status_text = "";
	foreach($statuses as $st)
		if ($st['code'] == $test_result['status']) 
			$status_text = $st['label'];
	
	// Status background
	if ($test_result['status'] == 1) $style = "success"; else $style = "fail";

	if (!array_key_exists('compiler_options', $result)) $result['compiler_options'] = "";
	if (!array_key_exists('debug', $result['tools'])) $result['tools']['debug'] = "";
	if (!array_key_exists('profile[memcheck]', $result['tools'])) $result['tools']['profile[memcheck]'] = "";

?>
	<script>
	var expected = [];
	var diffLabel = '<?=tr('Diff')?>';
	var hideDiffLabel = '<?=tr('Hide diff')?>';
	</script>
	
	<h2><?=tr("Detailed information")?> - Test <?=$test_no?></h2>
	<p><a href="#" onclick="return showhide('buildhost_data');"><?=tr("Show information on test platform")?></a></p>
	<div id="buildhost_data" style="display:none">
		<b><?=tr("Testing system:")?></b><br><?=$result['buildhost_description']['id']?><br><br>
		<b>OS:</b><br><?=str_replace("\n", "<br>", $result['buildhost_description']['os'])?><br><br>
		<b><?=tr("Compiler version:")?></b><br><?=$result['tools']['compile']?><br><br>
		<!-- <b><?=tr("Compiler options:")?></b><br><?=$result['compiler_options']?><br><br> -->
		<b><?=tr("Debugger version:")?></b><br><?=$result['tools']['debug']?><br><br>
		<b><?=tr("Profiler version:")?></b><br><?=$result['tools']['profile[memcheck]']?>
	</div>
	
	<h3><?=tr("Result")?>: <span class="<?=$style?>"><?=$status_text?></span></h3>
	<?php
	
	// Patch tool
	if (array_key_exists('patch', $the_test))
	foreach($the_test['patch'] as $patch) {
		if (!array_key_exists('position', $patch)) continue;
		if ($patch['position'] == "main") {
			?>
			<h3><?=tr("Test code:")?></h3>
			<pre><?=htmlspecialchars($patch['code']);?></pre>
			<?php
		}
		if ($patch['position'] == "top_of_file") {
			?>
			<h3><?=tr("Global scope (top of file):")?></h3>
			<pre><?=htmlspecialchars($patch['code']);?></pre>
			<?php
		}
		if ($patch['position'] == "above_main") {
			?>
			<h3><?=tr("Global scope (above main):")?></h3>
			<pre><?=htmlspecialchars($patch['code']);?></pre>
			<?php
		}
	}
	
	
/*if ($status != "no_func") {
	?>
	<p><a href="<?=genuri()?>&amp;akcija=test_sa_kodom">Prikaži kod testa unutar zadaće</a></p>
	<?
}*/

//if ($nastavnik)
//	if ($sakriven == 1) print "<p>Test je sakriven (nije vidljiv studentima)</p>\n"; else print "<p>Test je javan (vidljiv studentima)</p>\n";

	?>
	<hr>
	<h3><?=tr("Program input/output")?></h3>
	<table border="0" cellspacing="5">
	<?php
	
	if (array_key_exists('environment', $the_test['execute']) && array_key_exists('stdin', $the_test['execute']['environment'])) {
		?>
		<tr><td><?=tr("Standard input:")?></td>
		<td><code><?=escape_output($the_test['execute']['environment']['stdin'])?></code></td></tr>
		<?php
	}
	
	if (array_key_exists('expect', $the_test['execute'])) {
		$label_printed = false;
		$exno = 0;
		foreach ($the_test['execute']['expect'] as $expect) {
			if (!$label_printed) {
				print "<tr><td>" . tr("Expected output(s):") . "</td>";
				$label_printed = true;
			} else {
				print "<tr><td>&nbsp;</td>";
			}
			
			?>
			<script>
				expected[<?=$exno?>] = '<?=escape_javascript($expect)?>';
			</script>
			<td><span class="fail"><code><?=escape_output($expect)?></code></span></td></tr>
			<tr><td>&nbsp;</td>
			<td><a href="#" onclick="return showDiff(<?=$exno?>)" id="showDiffLink"><?=tr("Diff")?></a></td></tr>
			<?php
			
			$exno++;
		}
	}

	if (array_key_exists('fail', $the_test['execute'])) {
		$label_printed = false;
		foreach ($the_test['execute']['fail'] as $expect) {
			if (!$label_printed) {
				?>
				<tr><td><?=tr("Fail on output(s):")?></td>
				<td><code><?=escape_output($expect)?></code></td></tr>
				<?php
			} else {
				?>
				<tr><td>&nbsp;</td>
				<td><code><?=escape_output($expect)?></code></td></tr>
				<?php
			}
		}
	}
	
	if (array_key_exists('matching', $the_test['execute'])) {
		?>
		<tr><td><?=tr("Matching type")?></td>
		<td><?=$the_test['execute']['matching']?></td></tr>
		<?php
	}
	
	if (array_key_exists('output', $test_result['tools']['execute'])) {
		?>
		<script>
		var programOutput = "<?=escape_javascript($test_result['tools']['execute']['output'])?>";
		</script>
		<tr><td><?=tr("Your program output:")?></td>
		<td id="programOutput"><span class="success"><code><?=escape_output($test_result['tools']['execute']['output'])?></code></span></td></tr>
		<?php 
	}
	
	if(array_key_exists('duration', $test_result['tools']['execute'])) {
		?>
		<tr><td><?=tr("Execution time (rounded):")?></td>
		<td><?=$test_result['tools']['execute']['duration']?> <?=tr("seconds")?></td></tr>
		<?php
	}
	
	?>
	</table>
<?php

	?>
	<hr>
	<h3><?=tr("Test report:")?></h3>
	<code><?=generate_report($the_test, $test_result)?></code>
	<?php
}



if(!isset($_REQUEST['task'])) {
	show_form();
	fatal_error();
}

$task = json_decode($_REQUEST['task'], true);
$result = json_decode($_REQUEST['result'], true);

if (!isset($_REQUEST['test']) || intval($_REQUEST['test']) == 0)
	show_table($task, $result);
else
	show_test($task, $result, intval($_REQUEST['test']));
exit(0);
	

