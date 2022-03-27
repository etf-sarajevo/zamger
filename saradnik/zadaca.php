<?


// SARADNIK/ZADACA - prozor za pregled zadace



function saradnik_zadaca() {
	
	global $user_siteadmin,$conf_code_viewer,$_api_http_code;
	
	require_once("lib/autotest.php");
	require_once("lib/student_predmet.php"); // update_komponente
	require_once("lib/utility.php"); // nicesize
	
	
	// --------------------
	// Standardni ulazni podaci i potrebne varijable
	
	$studentId=intval($_REQUEST['student']);
	$zadaca=intval($_REQUEST['zadaca']);
	$zadatak=intval($_REQUEST['zadatak']);
	
	
	$currentAssignment = api_call("homework/$zadaca/$zadatak/student/$studentId", [ "resolve" => ["ProgrammingLanguage", "Person", "Homework"] ] );
	if ($_api_http_code == "404") {
		zamgerlog("nepostojeca zadaca $zadaca",3);
		zamgerlog2("nepostojeca zadaca", $zadaca);
		niceerror("Neispravna zadaća.");
		return;
	}
	if ($_api_http_code == "403") {
		zamgerlog("privilegije (student u$studentId zadaca z$zadaca)", 3); // nivo 3: greska
		zamgerlog2("nije nastavnik na predmetu za zadacu", $zadaca); // nivo 3: greska
		niceerror("Nemate pravo izmjene ove zadaće");
		return;
	}
	if ($_api_http_code != "200") {
		niceerror("Neuspješan pristup zadaći: " . $currentAssignment['message']);
		return;
	}
	if ($zadatak < 1 || $zadatak > $currentAssignment['Homework']['nrAssignments']) {
		zamgerlog("pokusao pristupiti nepostojecem zadatku $zadatak u zadaci z$zadaca",3);
		zamgerlog2("zadaca nema toliko zadataka", $zadaca, $zadatak);
		niceerror("Neispravan broj zadatka.");
		return;
	}
	
	// Podaci o zadaci
	$jezik = $currentAssignment['Homework']['ProgrammingLanguage']['geshi'];
	$ekst = $currentAssignment['Homework']['ProgrammingLanguage']['extension'];
	$attach = $currentAssignment['Homework']['attachment'];
	$naziv_zadace = $currentAssignment['Homework']['name'];
	$predmet = $currentAssignment['Homework']['CourseUnit']['id'];
	$ag = $currentAssignment['Homework']['AcademicYear']['id'];
	$id_jezika = $currentAssignment['Homework']['ProgrammingLanguage']['id'];
	$ace_mode = $currentAssignment['Homework']['ProgrammingLanguage']['ace'];
	
	
	// Podaci o studentu
	
	$ime_studenta = $currentAssignment['student']['name'];
	$prezime_studenta = $currentAssignment['student']['surname'];
	
	
	
	
	
	// --------------------
	// AKCIJE
	
	
	// Akcija: Ispis diffa
	
	if ($_GET['akcija'] == "diff") {
		$diffId = intval($_GET['diff_id']);
		
		$diff = api_call("homework/$zadaca/$zadatak/student/$studentId/diff", [ "diffId" => $diffId ], "GET", false, false);
		
		// Ovo ispod nema potrebe jer je diff već escapovan prilikom
		// inserta u bazu (stud_zadaca.php)
		// $diff = str_replace("\n\n","\n",$diff);
		// $diff = htmlspecialchars($diff);
		
		print "<pre>$diff</pre>\n\n";
		return;
	}
	
	
	// Akcija: Izvršenje programa
	
	if ($_POST['akcija'] == "izvrsi" && check_csrf_token()) {
		
		function izvrsi($stdin, $jezik, $lokacijazadaca, $zadaca, $zadatak, $ekst) {
			global $conf_files_path;
			
			// priprema fajlova
			$tstdin = str_replace('\\n',"\n",$stdin); // više nije dvostruki escape
			$tstdin = str_replace('\\N',"\n",$tstdin);
			$tstdin .= "\n";
			$result = file_put_contents("$conf_files_path/tmp/zamger-gdb.txt","run\nbt\n");
			if ($result) $result = file_put_contents("$conf_files_path/tmp/zamger-input.txt",$tstdin);
			if (!$result) {
				zamgerlog("nije uspjelo kreiranje datoteka",3);
				zamgerlog2("nije uspjelo kreiranje datoteka");
				niceerror("Ne mogu kreirati potrebne datoteke u direktoriju /tmp");
				return;
			}
			
			// kompajliranje - FIXME: nema podrške za jezike?
			if ($jezik == "C++")
				$kompajler = "g++";
			else
				$kompajler = "gcc";
			$the_file = "$lokacijazadaca$zadaca/$zadatak$ekst";
			$stdout = array();
			exec("$kompajler -lm  -ggdb $the_file -o $conf_files_path/tmp/zamger.out 2>&1", $stdout, $retvar);
			if ($retvar != 0) {
				niceerror("Kompajliranje nije uspjelo! Slijedi ispis");
				print "<pre>".join("\n",$stdout)."</pre>\n\n";
				// čišćenje
				unlink("$conf_files_path/tmp/zamger-gdb.txt");
				unlink("$conf_files_path/tmp/zamger-input.txt");
				unlink("$conf_files_path/tmp/zamger.out");
				return;
			}
			
			// izvršenje
			unset($stdout);
			chmod("$conf_files_path/tmp/zamger.out", 0755);
			exec("gdb --batch --command=$conf_files_path/tmp/zamger-gdb.txt $conf_files_path/tmp/zamger.out <$conf_files_path/tmp/zamger-input.txt 2>&1", $stdout, $retvar);
			
			// Čistimo viškove iz stdout-a
			$ispis = join("\n",$stdout);
			$ispis = preg_replace("/^Using .*? library .*?\n/", "", $ispis);
			$ok = strpos($ispis,"\nProgram exited normally.\n");
			if ($ok)
				$ispis = substr($ispis,0,$ok);
			else {
				$greska = strpos($ispis,"\nProgram received signal SIGABRT, Aborted.\n");
				$backtrace = substr($ispis,$greska+42);
				$ispis = substr($ispis,0,$greska);
			}
			
			?>
			<center><table width="95%" style="border:1px solid silver;" bgcolor="#FFF3F3"><tr><td>
							<pre><?=$ispis?></pre>
						</td></tr></table></center><br/><?
			if ($ok) {
				?><p><img src="static/mages/16x16/ok.png" width="16" height="16">
					Program se izvršio bez problema.</p><?
			} else {
				?><p><img src="static/images/16x16/bug.png" width="16" height="16">
					Program se krahirao. Backtrace (obratiti pažnju na zadnje linije):</p>
				<pre><?=$backtrace?></pre>
				<?
			}
			
			// čišćenje
			unlink("$conf_files_path/tmp/zamger-gdb.txt");
			unlink("$conf_files_path/tmp/zamger-input.txt");
			unlink("$conf_files_path/tmp/zamger.out");
		}
		
		
		?>
		<h1>Rezultat izvršenja:</h1>
		<?
		
		// TODO ovo treba držati u local storage
		
		/*if ($_POST['sve']) {
			$q70 = db_query("select ulaz from stdin where zadaca=$zadaca and redni_broj=$zadatak");
			while ($r70 = db_fetch_row($q70)) {
				print "<h2>Ulaz: '$r70[0]'</h2>";
				izvrsi($r70[0], $jezik, $lokacijazadaca, $zadaca, $zadatak, $ekst);
			}
		} else {*/
		$stdin = $_POST['stdin'];
		$mstdin = db_escape($stdin);
		//$q70 = db_query("select count(*) from stdin where ulaz='$mstdin' and zadaca=$zadaca and redni_broj=$zadatak");
		//if (db_result($q70,0,0)==0)
		//	$q80 = db_query("insert into stdin set ulaz='$mstdin', zadaca=$zadaca, redni_broj=$zadatak");
		izvrsi($stdin, $jezik, $lokacijazadaca, $zadaca, $zadatak, $ekst);
		//}
		
		?>
		<p><a href="javascript:history.go(-1)">Nazad</a></p>
		<?
		
		return;
	}
	
	
	// Akcija: Izmjena statusa
	
	if ($_POST['akcija'] == "slanje" && check_csrf_token()) {
		
		$komentar = $_POST['komentar'];
		$status = intval($_POST['status']);
		$bodova = floatval(str_replace(",",".",$_POST['bodova']));
		
		$newAssignment = array_to_object( [ "id" => 0, "Homework" => [ "id" => $zadaca ], "assignNo" => $zadatak, "student" => [ "id" => $studentId ], "status" => $status, "score" => $bodova, "comment" => $komentar, "compileReport" => "", "filename" => $currentAssignment['filename'] ] );
		
		// Current assignment is now new changed
		$currentAssignment = api_call("homework/$zadaca/$zadatak/student/$studentId", $newAssignment, "PUT");
		
		if ($_api_http_code != "201") {
			niceerror("Neuspješno ažuriranje statusa zadaće: " . $currentAssignment['message']);
		} else {
			zamgerlog("izmjena zadace (student u$studentId zadaca z$zadaca zadatak $zadatak)", 2);
			zamgerlog2("bodovanje zadace", $studentId, $zadaca, $zadatak);
		}
		
		// Nakon izmjene statusa, nastavljamo normalno sa prikazom zadatka
	}
	
	
	if ($_REQUEST["akcija"] == "brisi_testove" && check_csrf_token()) {
		autotest_brisi_rezultate($studentId, $zadaca, $zadatak);
		nicemessage("Rezultati testova obrisani.");
		?>
		<p><a href="?sta=saradnik/zadaca&amp;student=<?=$studentId?>&amp;zadaca=<?=$zadaca?>&amp;zadatak=<?=$zadatak?>">Nazad</a></p>
		<?
		return;
	}
	
	
	
	// --------------------
	// PRIKAZ ZADATKA
	
	
	// Header
	
	?>
	<h1><a href="?sta=saradnik/student&amp;student=<?=$studentId?>&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>" onclick="window.opener.open(this.href); return false;"><?=$ime_studenta." ".$prezime_studenta?></a>, <?=$naziv_zadace.", Zadatak ".$zadatak."."?></h1>
	<?
	
	
	// Da li ispisati zadatak ili dugme za download attachmenta?
	
	if (!$attach) {
		// Nije attachment
		
		$src = api_call("homework/$zadaca/$zadatak/student/$studentId/file", [], "GET", false, false);
		if ($_api_http_code != "200")
			$src = ""; // File doesn't exist
		
		$no_lines = count(explode("\n", $src));
		
		// ACE code editor
		if ($conf_code_viewer == "ace" && $id_jezika > 0) {
			// Ako nije definisan programski jezik geshi je lakši
			?>
			<div id="editor"><?=htmlspecialchars($src)?></div>
			<script src="static/js/ace/ace.js" type="text/javascript" charset="utf-8"></script>
			<script>
                var editor = ace.edit("editor");
                //editor.setTheme("ace/theme/monokai");
                editor.getSession().setMode("ace/mode/<?=$ace_mode?>");

                // Stavljamo visinu ACE editora na dužinu koda
                var newHeight =
                    editor.getSession().getScreenLength()
                    * editor.renderer.lineHeight
                    + editor.renderer.scrollBar.getWidth() + 20; // 20 = jedan prazan red na kraju
                /*$('#editor').height(newHeight.toString() + "px");
				$('#editor-section').height(newHeight.toString() + "px");
				editor.resize();*/
                document.getElementById('editor').style.height = newHeight.toString() + "px";
                document.getElementById('editor-section').style.height = newHeight.toString() + "px";
                editor.resize();

                // Not editable
                editor.setOptions({
                    readOnly: true,
                    highlightActiveLine: false,
                    highlightGutterLine: false
                })
                editor.renderer.$cursorLayer.element.style.opacity=0
                editor.textInput.getElement().tabIndex=-1
                editor.commands.commmandKeyBinding={}
			</script>
			<?
			
			// geshi - biblioteka za syntax highlighting
		} else {
			require("vendor/autoload.php");
			$geshi = new GeSHi($src, $jezik);
			
			?>
			<center><table width="95%" style="border:1px solid silver;"><tr>
						<!-- Brojevi linija -->
						<td bgcolor="#CCCCCC" align="left"><pre><? for ($i=1; $i<=$no_lines; $i++) print "$i\n"; ?></pre></td>
						<td  bgcolor="#F3F3F3" align="left">
							<?
							print $geshi->parse_code();
							?></td></tr></table></center><br/><?
		}
		
		if ($_REQUEST["akcija"] == "test_sa_kodom") return;
		
		// Formular za izvršavanje programa
		if ($id_jezika > 0) {
			?>
			<script type="text/javascript" src="static/js/combo-box.js"></script>
			<center><table style="border:1px solid silver;" cellspacing="0" cellpadding="6"><tr><td>
							Izvrši program sa sljedećim parametrima (kucajte \n za tipku enter):<br/>
							<?=genform("POST")?>
							<input type="hidden" name="akcija" value="izvrsi">
							<select name="stdin" onKeyPress="edit(event)" onBlur="this.editing = false;">
								<?
								
								// Zadnje korišteni stdin se čuva u bazi
								// TODO ovo treba držati u local storage
								/*$q120 = db_query("select ulaz from stdin where zadaca=$zadaca and redni_broj=$zadatak order by id desc");
								if (db_num_rows($q120)<1)
									print "<option></option>"; // bez ovoga nije moguće upisati novi tekst
								while ($r120 = db_fetch_row($q120)) {
									print "<option value=\"$r120[0]\">$r120[0]</option>\n";
								}*/
								?>
							</select><br/>

							<b>Pažnja!</b> Prije pokretanja provjerite da li program sadrži opasne naredbe.<br/>
							<input type="submit" value=" Izvrši program "> <input type="submit" name="sve" value=" Izvrši sve primjere odjednom ">
							</form>
						</td></tr></table></center><br/>&nbsp;<br/>
			<?
		}
		
		
	} else {
		// Attachment
		
		if ($currentAssignment['status'] != 0 && $currentAssignment['filename'] != "") {
			if ($currentAssignment['submittedTime'] > 0)
				$vrijeme = date("d. m. Y. H:i:s", db_timestamp($currentAssignment['submittedTime']));
			else
				$vrijeme = date("d. m. Y. H:i:s", db_timestamp($currentAssignment['time']));
			
			$filename = $currentAssignment['filename'];
			$velicina = $currentAssignment['filesize'];
			$icon = "static/images/mimetypes/" . getmimeicon($currentAssignment['filename'], $currentAssignment['filetype']);
			$dllink = "index.php?sta=common/attachment&student=$studentId&zadaca=$zadaca&zadatak=$zadatak";
			?>
			<center><table width="75%" border="1" cellpadding="6" cellspacing="0" bgcolor="#CCCCCC"><tr><td>
							<a href="<?=$dllink?>"><img src="<?=$icon?>" border="0"></a>
						</td><td>
							<p>Poslani fajl: <b><a href="<?=$dllink?>"><?=$filename?></a></b><br/>
								Datum slanja: <b><?=$vrijeme?></b><br/>
								Veličina: <b><?=$velicina?></b></p>
						</td></tr></table></center>
			<?
		} else {
			?>
			<center><table width="75%" border="1" cellpadding="6" cellspacing="0" bgcolor="#CCCCCC"><tr><td>
							<b><font color="red">Student je zaboravio priložiti datoteku.</font></b>
						</td></tr></table></center><br/>
			<?
		}
	}
	
	
	// Prikaz statusa sa log-om i izmjena
	?>
	<table border="0">
		<?
		
		if ($currentAssignment['id'] > 0) {
			$status = $currentAssignment['status'];
			$bodova = $currentAssignment['score'];
			$izvjestaj_skripte = nl2br($currentAssignment['compileReport']);
			$komentar = str_replace("\"", "&quot;", $currentAssignment['comment']);
			
			if ($currentAssignment['submittedTime'])
				$vrijeme_slanja = date("d. m. Y. H:i:s", db_timestamp($currentAssignment['submittedTime']));
			else
				// Nije nikad slao, koristimo vrijeme zadaće
				$vrijeme_slanja = date("d. m. Y. H:i:s", db_timestamp($currentAssignment['time']));
			
			?>

			<tr>
				<td>Vrijeme slanja:</td>
				<td><b><?=$vrijeme_slanja?></b></td>
			</tr>
			<?
			
			// Autotest nalaz
			if ($nivo_pristupa == "nastavnik" || $nivo_pristupa == "super_asistent" || $nivo_pristupa == "zadace_admin")
				$nalaz_autotesta = autotest_tabela($studentId, $zadaca, $zadatak, /*$nastavnik =*/ true, db_timestamp($currentAssignment['Homework']['deadline']));
			else
				$nalaz_autotesta = autotest_tabela($studentId, $zadaca, $zadatak, /*$nastavnik =*/ false, db_timestamp($currentAssignment['Homework']['deadline']));
			if ($nalaz_autotesta != "") {
				?>
				<tr>
					<td>Rezultati testiranja:</td>
					<td>
						<p><?=genform("POST")?>
							<input type="hidden" name="akcija" value="brisi_testove">
							<input type="submit" value=" Obriši sve rezultate testiranja ">
							</form></p>
						<?
						print $nalaz_autotesta;
						?>
					</td></tr>
				<?
			}
			
			if ($id_jezika > 0) {
				?>
				<tr>
					<td>Izvještaj skripte:</td>
					<td><i><?=$izvjestaj_skripte?></i></td>
				</tr>
				<?
			}
			
			if ($status == 1 && !$user_siteadmin) { // nema mijenjanja ako je status 1 = ceka se automatska provjera
				?>
				<tr><td colspan="2">Izmjena zadaće nije moguća jer se čeka automatsko testiranje</td></tr>
				<?
			}
			else
				print genform("POST");
			
			?>
			<input type="hidden" name="akcija" value="slanje">
			<?
			
		} else {
			
			print genform("POST");
			?>
			<input type="hidden" name="akcija" value="slanje">

			<tr>
				<td>&nbsp;</td>
				<td><b>Unos bodova za zadaću koja nije poslana:</b></td>
			</tr>
			<?
			
		}
		
		// Dio forme koji se prikazuje bez obzira da li je u pitanju kreiranje nove zadaće ili promjena postojeće
		
		?>
		<tr>
			<td>Status</td>
			<td><select id="status" name="status"><?
					
					function myoption($nr,$tx,$sel) {
						print "$sel";
						print "<option value=$nr";
						if ($nr==$sel) print " selected";
						print ">$tx</option>";
					}
					
					// tabela status kodova
					
					$statusi_array = array("nepoznat status","sačekati automatsko testiranje!","prepisana","ne može se kompajlirati","nova zadaća, potrebno pregledati","pregledana","potrebna odbrana");
					$brstatusa = 7;
					
					for ($i=0;$i<$brstatusa;$i++)
						myoption($i,$statusi_array[$i],$status);
					
					
					?></select></td>
		</tr>
		<tr>
			<td>Bodova:</td>
			<td><input type="text" size="20" name="bodova" value="<?=$bodova?>" onchange="javascript:document.getElementById('status').value=5;"></td>
		</tr>
		<tr>
			<td valign="top">Komentar:</td>
			<td><textarea cols="50" rows="5" name="komentar"><?=$komentar?></textarea></td>
		</tr>
		<tr>
			<td colspan="2" align="center"><?
				if ($status!=1 || $user_siteadmin) {
					?><input type="submit" value="Izmijeni vrijednosti"><?
				} ?></td>
		</tr>
	</table>
	</form>
	
	
	
	<?
	
	##### HISTORIJA IZMJENA ######
	
	$log = api_call("homework/$zadaca/$zadatak/student/$studentId/log", [ "resolve" => ["Person"] ])["results"];
	if (count($log) > 0) {
		?>

		<p>Historija izmjena:</p>
		<ul><?
			foreach($log as $asgn) {
				$vrijeme_izmjene = date ("d. m. Y. H:i:s", db_timestamp($asgn['time']));
				$imeprezime = "";
				// It's possible that there is no author
				if ($asgn['author']['id'] > 0)
					$imeprezime = " (" . $asgn['author']['name']." ".$asgn['author']['surname'] . ")";
				
				?>
				<li><b><?=$vrijeme_izmjene?><?=$imeprezime?>:</b>
					<?=$statusi_array[$asgn['status']]?>
					<? if ($asgn['score'] > 0) print " (" . $asgn['score'] . " bodova)"; ?>
					<? if (strlen(trim($asgn['comment'])) > 0) print " - " . $asgn['comment']; ?>
					<? if ($asgn['hasDiff']) {
						?>
						(<a href="index.php?sta=saradnik/zadaca&akcija=diff&zadaca=<?=$zadaca?>&zadatak=<?=$zadatak?>&student=<?=$studentId?>&diff_id=<?=$asgn['id']?>">diff</a>)
						<?
					}
					?>
				</li>
				<?
			}
			?>
		</ul>
		<?
	}
	
	// Kraj historije izmjena
	
	
	
} // function saradnik_zadaca()


?>
