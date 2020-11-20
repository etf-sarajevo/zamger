<?

// SARADNIK/KOMENTAR - stavljanje komentara na rad studenata



function saradnik_komentar() {

	require_once("lib/formgen.php"); // datectrl
	
	global $userid, $_api_http_code;
	
	?>
	<body topmargin="0" leftmargin="0" bottommargin="0" rightmargin="0" bgcolor="#FFFFFF">
	<?
	
	$studentId=intval($_REQUEST['student']);
	$labgrupa=intval($_REQUEST['labgrupa']);


	// ------------------------
	//  Akcije
	// ------------------------
	
	if (param('akcija') == "dodaj" && check_csrf_token()) {
		list ($h,$m,$s) = explode(":", $_POST['vrijeme']);
		$datum = date("Y-m-d H:i:s", mktime($h,$m,$s, $_POST['month'], $_POST['day'], $_POST['year']));
		$komentar = $_POST['komentar'];
		
		$comment = [ "id" => 0, "student" => [ "id" => $studentId ], "teacher" => [ "id" => $userid ], "Group" => [ "id" => $labgrupa ], "dateTime" => $datum, "text" => $komentar ];
		$comment = array_to_object($comment);
		
		$result = api_call("comment/group/$labgrupa/student/$studentId", $comment, "POST");
		if ($_api_http_code == "201") {
			zamgerlog("dodan komentar na studenta u$studentId labgrupa g$labgrupa", 2);
			zamgerlog2("dodan komentar na studenta", $studentId, $labgrupa);
		} else {
			niceerror("Dodavanje komentara nije uspjelo");
			api_report_bug($result, $comment);
		}
	}
	
	
	if (param('akcija') == "obrisi") {
		$id = intval($_GET['id']);
		api_call("comment/$id", [], "DELETE");
		
		if ($_api_http_code == "204") {
			zamgerlog("obrisan komentar $id",2);
			zamgerlog2("obrisan komentar", $id);
		} else {
			niceerror("Brisanje komentara nije uspjelo");
			api_report_bug($result, []);
		}
	}


	// Spisak komentara
	$comments = api_call("comment/group/$labgrupa/student/$studentId", [ "resolve" => ["Person"] ], "GET")["results"];
	
	if (count($comments) < 1) {
		?>
		<ul><li>Nijedan komentar nije unesen.</li></ul>
		<?
	}
	foreach($comments as $comment) {
		$datum = date("d. m. Y. H:i:s", db_timestamp($comment['dateTime']));
		$teacherName = $comment['teacher']['name'] . " " . $comment['teacher']['surname'];
		?>
		<p><b><?=$datum?> (<?=$teacherName?>):</b>
			(<a href="<?=genuri()?>&akcija=obrisi&id=<?=$comment['id']?>">Obriši</a>)<br/>
			<?=$comment['text']?><br/>
		</p>
		<?
	}

	
	// Dodaj komentar
	?>
	<p><hr></p>
	<p><b>Dodajte komentar:</b><br/>
	<?=genform("POST");?>
	<input type="hidden" name="akcija" value="dodaj">
	Trenutni datum i vrijeme:<br/>
	<?=datectrl(date("d"),date("m"),date("Y"));?>&nbsp;
	<input type="text" size="10" name="vrijeme" value="<?=date("H:i:s");?>" class="default"><br/><br/>
	<textarea cols="35" rows="5" name="komentar"></textarea><br/>
	<input type="submit" value=" Pošalji " class="default"></form>
	</p>
	</body>
	<?


}


?>
