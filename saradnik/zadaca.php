<?


// SARADNIK/ZADACA - prozor za pregled zadace



function saradnik_zadaca() {

global $conf_files_path,$userid,$user_siteadmin,$conf_code_viewer;

require_once("lib/autotest.php"); 
require_once("lib/student_predmet.php"); // update_komponente
require_once("lib/utility.php"); // nicesize


// --------------------
// Standardni ulazni podaci i potrebne varijable

$stud_id=intval($_REQUEST['student']);
$zadaca=intval($_REQUEST['zadaca']);
$zadatak=intval($_REQUEST['zadatak']);



// Prava pristupa

if (!$user_siteadmin) {
	// Da li je nastavnik na predmetu?
	$q10 = db_query("select np.nivo_pristupa from nastavnik_predmet as np, zadaca as z where z.id=$zadaca and z.predmet=np.predmet and z.akademska_godina=np.akademska_godina and np.nastavnik=$userid");
	if (db_num_rows($q10)<1) {
		zamgerlog("privilegije (student u$stud_id zadaca z$zadaca)",3); // nivo 3: greska
		zamgerlog2("nije nastavnik na predmetu za zadacu", $zadaca); // nivo 3: greska
		niceerror("Nemate pravo izmjene ove zadaće");
		return;
	}
	$nivo_pristupa = db_result($q10,0,0);

	// Ogranicenja (tabela: ogranicenje) ne provjeravamo jer bi to bilo prekomplikovano,
	// a pitanje je da li ima smisla

} else $nivo_pristupa = "nastavnik";


// Podaci o zadaci

$q20 = db_query("select p.geshi, p.ekstenzija, z.attachment, z.naziv, z.zadataka, z.komponenta, z.predmet, z.akademska_godina, z.programskijezik, p.ace from zadaca as z, programskijezik as p where z.id=$zadaca and z.programskijezik=p.id");
if (db_num_rows($q20)<1) {
	zamgerlog("nepostojeca zadaca $zadaca",3);
	zamgerlog2("nepostojeca zadaca", $zadaca);
	niceerror("Neispravna zadaća.");
	exit;
}

$jezik = db_result($q20,0,0);
$ekst = db_result($q20,0,1);
$attach = db_result($q20,0,2);
$naziv_zadace = db_result($q20,0,3);
$komponenta = db_result($q20,0,5);
$predmet = db_result($q20,0,6);
$ag = db_result($q20,0,7);
$id_jezika = db_result($q20,0,8);
$ace_mode = db_result($q20,0,9);


if (db_result($q20,0,4)<$zadatak || $zadatak<1) {
	zamgerlog("pokusao pristupiti nepostojecem zadatku $zadatak u zadaci z$zadaca",3);
	zamgerlog2("zadaca nema toliko zadataka", $zadaca, $zadatak);
	niceerror("Neispravan broj zadatka.");
	exit;
}



// Podaci o studentu

$q50 = db_query("select ime, prezime from osoba where id=$stud_id");
if (db_num_rows($q50)<1) {
	zamgerlog("nepostojeci student $stud_id",3);
	zamgerlog2("nepostojeci student", $stud_id);
	niceerror("Neispravan student.");
	exit;
}

$ime_studenta = db_result($q50,0,0);
$prezime_studenta = db_result($q50,0,1);

$lokacijazadaca="$conf_files_path/zadace/$predmet-$ag/$stud_id/";





// --------------------
// AKCIJE


// Akcija: Ispis diffa

if ($_GET['akcija'] == "diff") {
	$diff_id=intval($_GET['diff_id']);
	$q60 = db_query("select diff from zadatakdiff where zadatak=$diff_id");
	$diff = db_result($q60,0,0);

	// Ovo ispod nema potrebe jer je diff već escapovan prilikom 
	// inserta u bazu (stud_zadaca.php)
	// $diff = str_replace("\n\n","\n",$diff);
	// $diff = htmlspecialchars($diff);

	print "<pre>$diff</pre>\n\n";
	return;
}


// Akcija: Izvršenje programa

if ($_POST['akcija'] == "izvrsi" && check_csrf_token()) {

	// čuvamo poslane podatke u bazi (ako ih nema)
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

	if ($_POST['sve']) {
		$q70 = db_query("select ulaz from stdin where zadaca=$zadaca and redni_broj=$zadatak");
		while ($r70 = db_fetch_row($q70)) {
			print "<h2>Ulaz: '$r70[0]'</h2>";
			izvrsi($r70[0], $jezik, $lokacijazadaca, $zadaca, $zadatak, $ekst);
		}
	} else {
		$stdin = $_POST['stdin'];
		$mstdin = db_escape($stdin);
		$q70 = db_query("select count(*) from stdin where ulaz='$mstdin' and zadaca=$zadaca and redni_broj=$zadatak");
		if (db_result($q70,0,0)==0) 
			$q80 = db_query("insert into stdin set ulaz='$mstdin', zadaca=$zadaca, redni_broj=$zadatak");
		izvrsi($stdin, $jezik, $lokacijazadaca, $zadaca, $zadatak, $ekst);
	}
	
	?>
	<p><a href="javascript:history.go(-1)">Nazad</a></p>
	<?
	
	return;
}


// Akcija: Izmjena statusa

if ($_POST['akcija'] == "slanje" && check_csrf_token()) {

	$komentar = db_escape($_POST['komentar']);
	$status = intval($_POST['status']);
	$bodova = floatval(str_replace(",",".",$_POST['bodova']));

	// Osiguravamo da se filename prenese u svaku sljedeću instancu zadatka
	$filename = $izvjestaj_skripte = '';
	$q90 = db_query("select filename, izvjestaj_skripte from zadatak where zadaca=$zadaca and redni_broj=$zadatak and student=$stud_id order by id desc limit 1");
	if (db_num_rows($q90) > 0) {
		$filename = db_escape_string(db_result($q90,0,0));
		$izvjestaj_skripte = db_escape_string(db_result($q90,0,1)); // Već je sanitiziran HTML
	}

	$q100 = db_query("insert into zadatak set zadaca=$zadaca, redni_broj=$zadatak, student=$stud_id, status=$status, bodova=$bodova, vrijeme=now(), komentar='$komentar', filename='$filename', izvjestaj_skripte='$izvjestaj_skripte', userid=$userid");

	// Odredjujemo ponudu kursa (za update komponente)
	$q110 = db_query("select pk.id from student_predmet as sp, ponudakursa as pk where sp.student=$stud_id and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");

	update_komponente($stud_id, db_result($q110,0,0), $komponenta);

	zamgerlog("izmjena zadace (student u$stud_id zadaca z$zadaca zadatak $zadatak)",2);
	zamgerlog2("bodovanje zadace", $stud_id, $zadaca, $zadatak);

	// Nakon izmjene statusa, nastavljamo normalno sa prikazom zadatka
}


if ($_REQUEST["akcija"] == "test_detalji") {
	$test = intval($_REQUEST['test']);

	// Provjera spoofinga testa
	$q10 = db_query("SELECT COUNT(*) FROM autotest WHERE id=$test AND zadaca=$zadaca AND zadatak=$zadatak");
	if (db_result($q10,0,0) == 0) {
		niceerror("Odabrani test nije sa odabrane zadaće.");
		return;
	}

	if ($nivo_pristupa == "nastavnik" || $nivo_pristupa == "super_asistent" || $nivo_pristupa == "zadace_admin")
		autotest_detalji($test, $stud_id, /* $param_nastavnik = */ true); 
	else
		autotest_detalji($test, $stud_id, /* $param_nastavnik = */ false); 
	return;
}



if ($_REQUEST["akcija"] == "brisi_testove" && check_csrf_token()) {
	autotest_brisi_rezultate($stud_id, $zadaca, $zadatak);
	nicemessage("Rezultati testova obrisani.");
	?>
	<p><a href="?sta=saradnik/zadaca&amp;student=<?=$stud_id?>&amp;zadaca=<?=$zadaca?>&amp;zadatak=<?=$zadatak?>">Nazad</a></p>
	<?
	return;
}



// --------------------
// PRIKAZ ZADATKA


// Header

?>
<h1><a href="?sta=saradnik/student&amp;student=<?=$stud_id?>&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>" onclick="window.opener.open(this.href); return false;"><?=$ime_studenta." ".$prezime_studenta?></a>, <?=$naziv_zadace.", Zadatak ".$zadatak."."?></h1>
<?


// Da li ispisati zadatak ili dugme za download attachmenta?

if ($attach == 0) {
	// Nije attachment

	$src = "";
	$the_file = "$lokacijazadaca$zadaca/$zadatak$ekst";
	$no_lines = 0;
	if (file_exists($the_file)) { 
		if ($_REQUEST["akcija"] == "test_sa_kodom") {
			$test = intval($_REQUEST['test']);

			// Provjera spoofinga testa
			$q10 = db_query("SELECT COUNT(*) FROM autotest WHERE id=$test AND zadaca=$zadaca AND zadatak=$zadatak");
			if (db_result($q10,0,0) == 0) {
				niceerror("Odabrani test nije sa odabrane zadaće.");
				return;
			}

			$src = autotest_sa_kodom($test, $stud_id, /* $param_nastavnik = */ true); 
		} else
			$src = file_get_contents($the_file);

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
			include_once('lib/geshi/geshi.php');
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
			$q120 = db_query("select ulaz from stdin where zadaca=$zadaca and redni_broj=$zadatak order by id desc");
			if (db_num_rows($q120)<1)
				print "<option></option>"; // bez ovoga nije moguće upisati novi tekst
			while ($r120 = db_fetch_row($q120)) {
				print "<option value=\"$r120[0]\">$r120[0]</option>\n";
			}
			?>
			</select><br/>
		
			<b>Pažnja!</b> Prije pokretanja provjerite da li program sadrži opasne naredbe.<br/>
			<input type="submit" value=" Izvrši program "> <input type="submit" name="sve" value=" Izvrši sve primjere odjednom ">
			</form> </table></center><br/>&nbsp;<br/>
			<?
		}
	}


} else {
	// Attachment

	$q130 = db_query("select filename,UNIX_TIMESTAMP(vrijeme) from zadatak where zadaca=$zadaca and redni_broj=$zadatak and student=$stud_id order by id desc limit 1");
	if (db_num_rows($q130) > 0) {
		$filename = db_result($q130,0,0);
		$the_file = "$lokacijazadaca$zadaca/$filename";

		if ($filename && file_exists($the_file)) {
			$vrijeme = date("d. m. Y. h:i:s", db_result($q130,0,1));
			$velicina = nicesize(filesize($the_file));
			$icon = "static/images/mimetypes/" . getmimeicon($the_file);
			$dllink = "index.php?sta=common/attachment&student=$stud_id&zadaca=$zadaca&zadatak=$zadatak";
			?>
			<center><table width="75%" border="1" cellpadding="6" cellspacing="0" bgcolor="#CCCCCC"><tr><td>
			<a href="<?=$dllink?>"><img src="<?=$icon?>" border="0"></a>
			</td><td>
			<p>Poslani fajl: <b><a href="<?=$dllink?>"><?=$filename?></a></b><br/>
			Veličina: <b><?=$velicina?></b></p>
			</td></tr></table></center><br/>
			<?

		} else {
			?>
			<center><table width="75%" border="1" cellpadding="6" cellspacing="0" bgcolor="#CCCCCC"><tr><td>
			<b><font color="red">Student je zaboravio priložiti datoteku.</font></b>
			</td></tr></table></center><br/>
			<?
		}
	}
}


// Prikaz statusa sa log-om i izmjena

$q140 = db_query("select status,bodova,izvjestaj_skripte,komentar from zadatak where zadaca=$zadaca and redni_broj=$zadatak and student=$stud_id order by id desc limit 1");
if (db_num_rows($q140) > 0) {
	$status = db_result($q140,0,0);
	$bodova = db_result($q140,0,1);
	$izvjestaj_skripte = str_replace("\n","<br/>",db_result($q140,0,2));
	$komentar = db_result($q140,0,3);
	$komentar = str_replace("\"","&quot;",$komentar);

	// Koristimo poseban upit da bismo odredili vrijeme slanja prve verzije
	$q150 = db_query("select UNIX_TIMESTAMP(vrijeme) from zadatak where zadaca=$zadaca and redni_broj=$zadatak and student=$stud_id order by id limit 1");
	$vrijeme_slanja = date("d. m. Y. H:i:s",db_result($q150,0,0));

	?>

	<table border="0">
	<tr>
		<td>Vrijeme slanja:</td>
		<td><b><?=$vrijeme_slanja?></b></td>
	</tr>
	<?

	// Autotest nalaz
	if ($nivo_pristupa == "nastavnik" || $nivo_pristupa == "super_asistent" || $nivo_pristupa == "zadace_admin")
		$nalaz_autotesta = autotest_tabela($stud_id, $zadaca, $zadatak, /*$nastavnik =*/ true);
	else
		$nalaz_autotesta = autotest_tabela($stud_id, $zadaca, $zadatak, /*$nastavnik =*/ false);	
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
	}

	if ($id_jezika > 0) {
		?>
	<tr>
		<td>Izvještaj skripte:</td>
		<td><i><?=$izvjestaj_skripte?></i></td>
	</tr>
		<?
	}

	if ($status == 1 && !$user_siteadmin) // nema mijenjanja ako je status 1 = ceka se automatska provjera
		print "Izmjena zadaće nije moguća jer se čeka automatsko testiranje";
	else
		print genform("POST");

	?>
	<input type="hidden" name="akcija" value="slanje">
	<?

} else {
	
	print genform("POST");
	?>
	<input type="hidden" name="akcija" value="slanje">

	<table border="0">
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

$statusi_array = array("nepoznat status","sačekati automatsko testiranje!","prepisana","ne može se kompajlirati","nova zadaća, potrebno pregledati","pregledana");
$brstatusa = 6;

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


$q160 = db_query("select id,UNIX_TIMESTAMP(vrijeme),status,bodova,komentar,userid from zadatak where zadaca=$zadaca and redni_broj=$zadatak and student=$stud_id order by vrijeme");
if (db_num_rows($q160)>1) {

?>

<p>Historija izmjena:</p>
<ul><?
	while ($r160 = db_fetch_row($q160)) {
		$imeprezime="";
		if ($r160[5]>0) {
			$q165 = db_query("select ime, prezime from osoba where id=$r160[5]");
			if (db_num_rows($q165)>0) {
				$imeprezime = db_result($q165,0,0)." ".db_result($q165,0,1);
			}
		}

		$vrijeme_slanja = date("d. m. Y. H:i:s",$r160[1]);
		print "<li><b>$vrijeme_slanja";
		if ($imeprezime != "") print " ($imeprezime)";
		print ":</b> ".$statusi_array[$r160[2]];
		if ($r160[3]>0) print " (".$r160[3]." bodova)";
		if (strlen($r160[4])>0) print " - &quot;".$r160[4]."&quot;";
		$q170 = db_query("select count(zadatak) from zadatakdiff where zadatak=$r160[0]");
		if (db_result($q170,0,0)>0)
			print " (<a href=\"index.php?sta=saradnik/zadaca&akcija=diff&zadaca=$zadaca&zadatak=$zadatak&student=$stud_id&diff_id=$r160[0]\">diff</a>)";
		print "</li>\n\n"; 
	}

?>
</ul>

<?


} 

// Kraj historije izmjena



} // function saradnik_zadaca()


?>
