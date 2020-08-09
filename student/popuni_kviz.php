<?

// STUDENT/POPUNI_KVIZ - popunjavanje kviza

// TODO prebaciti u JavaScript

function student_popuni_kviz() {

	global $_api_http_code;
	
	$kviz = intval($_REQUEST['kviz']);
	
	// AKCIJA šalji
	if ($_REQUEST['akcija'] == "salji" && check_csrf_token()) {
		$quiz = json_decode($_REQUEST['quizObject']);
		// Markiramo odgovore kao tačne
		foreach($quiz->questions as &$question) {
			foreach($question->answers as &$answer) {
				$answer->correct = ($_REQUEST['odgovor' . $question['id']] == $answer['id']);
			}
		}
		
		$quiz = api_call("quiz/$kviz/submit", $quiz);
		if ($_api_http_code == "404") {
			niceerror("Molimo ponovite kviz");
			zamgerlog("poslao popunjen kviz $kviz a nema stavke u student_kviz", 3);
			zamgerlog2("poslao popunjen kviz a nema stavke u student_kviz", $kviz);
			return;
		}
		else if ($_api_http_code == "403") {
			niceerror("Nemate pristup ovom kvizu");
			zamgerlog("student nije na predmetu za kviz $kviz", 3);
			zamgerlog2("student nije na predmetu", $kviz);
			return;
		}
		
		// Tabela sa rezultatima
		print "<center><h1>Kviz završen</h1></center>\n";
		nicemessage("Osvojili ste " . $quiz->result->score . " bodova.");
		if ($quiz->result->score >= $quiz->passPoints) nicemessage("Čestitamo");
		?>
		<p><b>Tabela odgovora</b></p>
		<table border="1" cellspacing="0" cellpadding="2">
		<tr><td>R.br.</td><td>Pitanje</td><td>Tačno?</td><td>Bodova</td></tr>
		<?
		
		$rbr = 1;
		foreach($quiz->questions as $question) {
			$points = $question->score;
			if (!$question->answered) $points = 0;
			?><tr>
				<td><?=$rbr++?>.</td>
				<td><?=substr($question['text'],0,20) ?>...</td>
				<td><img src="static/images/16x16/<? if ($question->answered) print "ok"; else print "not_ok"; ?>.png" width="16" height="16"></td>
				<td><?=$points?></td>
			</tr><?
		}
		
		?>
		</table>
		<br><br>
		<p><a href="#" onclick="window.close();">Zatvorite ovaj prozor</a></p><?
		zamgerlog("uradio kviz $kviz", 2);
		zamgerlog2("uradio kviz", $kviz);
		return;
	}
	
	// Take the quiz (returns Quiz object)
	$quiz = api_call("quiz/$kviz/take", [], "GET", false, true, false);
	if ($_api_http_code == "404") {
		niceerror("Kviz ne postoji");
		zamgerlog("pristup nepostojecem kvizu $kviz", 3);
		zamgerlog2("pristup nepostojecem kvizu", $kviz);
		return;
	}
	if ($_api_http_code == "403") {
		niceerror("Nemate pristup ovom kvizu");
		zamgerlog("student nije na predmetu za kviz $kviz", 3);
		zamgerlog2("student nije na predmetu", $kviz);
		return;
	}
	
	$naziv_kviza = $quiz->name;
	$trajanje_kviza = $quiz->duration;
	$naziv_predmeta = getCourseName($quiz->CourseUnit->id);
	
	// Student može sudjelovati u kvizu pa šaljemo HTML

	?>
	<html>
	<head>
	<title>Kviz</title>
	<script>
	var Tpocetak=new Date();
	var Tkraj=new Date();
	var active_element;

	function onBlur() {
		if (/*@cc_on!@*/false) { // check for Internet Explorer
			if (active_element != document.activeElement) {
				active_element = document.activeElement;
				return;
			}
		}

		alert('Vaš kviz je obustavljen jer ste pokušali raditi nešto što nije popunjavanje kviza!\nIzgubili ste bodove.');
		var forma=document.getElementsByName('slanje');
		forma[0].submit();
	}

	function ucitavanje() {
		Tkraj.setTime((new Date()).getTime() + <?=$trajanje_kviza?> * 1000); // vrijeme je u milisekundama
		var t = setTimeout("provjeriVrijeme()",1000);
		if (/*@cc_on!@*/false) { // check for Internet Explorer
			active_element = document.activeElement;
			document.onfocusout = onBlur;
		} else {
			window.onblur = onBlur;
		}
		setTimeout("clp_clear();",1000);
	}
	
	function clp_clear() {
		var content=window.clipboardData.getData("Text");
		if (content==null) {
			window.clipboardData.clearData();
		}
		setTimeout("clp_clear();",1000);
	}

	function provjeriVrijeme() {
		var diff=new Date();
		diff.setTime(Tkraj-(new Date()));
		var vrijeme=document.getElementById('vrijeme');

		if (Tkraj<=(new Date())) {
			var forma=document.getElementsByName('slanje');
			forma[0].submit();
			return;
		}

		if (diff.getMinutes()==0 && diff.getSeconds()<30) {
			vrijeme.style.color='#FF0000';
		}
		var s = diff.getSeconds();
		if (s<10) s = "0"+s;
		
		vrijeme.innerHTML = diff.getMinutes()+":"+s;
		setTimeout("provjeriVrijeme()", 1000);
	}

	</script>
	</head>
	<body onload="ucitavanje()">
	<center><h2><?=$naziv_predmeta?></h2>
	<h2><?=$naziv_kviza?></h2></center>
	<div id="vrijemeinfo" style="width:150px; position:fixed; right:10px; top:20px; background-color: #303030; color:white;">Preostalo vrijeme: <span id="vrijeme"></span></div>
	<?


	// Ispisujemo pitanja kviza

	?>
	<br>
	<?=genform("POST", "slanje")?>
	<input type="hidden" name="quizObject" value="<?=json_encode($quiz)?>">
	<input type="hidden" name="akcija" value="salji">
	<table width=600px align=center>
	<?
		// ISPISI PITANJA
		$i=0;
		foreach($quiz->questions as $question) {
			$i++;
			
			?>
			<tr>
				<td valign=top><font size="5" face="serif"><?=$i ?>.</font></td>
				<td><font size="5" face="serif"><?=$question['text'] ?></font>
				<input type="hidden" name="rbrpitanje<?=$i?>" value="<?=$question['id']?>">
					<br>
					<table>
					<?
						// ISPISI ODGOVORE ZA PITANJE
						foreach($question->asnwers as $answer) {
							// FIXME: moze mapipulirati id odgovora i pitanja kada salje...
							?>
							<tr>
								<td><font size="5" face="serif">
								&nbsp;&nbsp; <input name="odgovor[<?=$question['id'] ?>]" type="radio" value=<?=$answer['id'] ?>>&nbsp;&nbsp;<?=$answer['text'] ?>
								</font></td>
							</tr>
							<?
						} // kraj ispisa odgovora 
					?>
					</table>
				</td>
			</tr>
			<tr><td colspan="2"><font size="5" face="serif">&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;</font></td></tr>
			<?
		}  // Kraj ispisa pitanja ?>
		
	
	</table>	
		
	<br><br>
		<input type="submit" value="Predaj">
	</form>
	<!--
			Navigation bar (removed)
		 <div id=navbox style="width:150px; position:fixed; right:10px; top:120px">
			<b><div id=showTime2 style="width:100%; background-color: #303030; color:white;" ></div></b>
			<?PHP for ($j=1; $j<$i+1; $j++) { ?>
				<a href="#pitanje<?PHP echo $j ?>" style="text-decoration: none; color:white;"><div style="width:100%; background-color: darkgray;" ><b>Pitanje <?PHP echo $j ?></b></div>
			<?PHP } ?>
		 </div>
	-->

	</body>
	</html>
	<?

}
