<?


// SARADNIK/ZADACA - prozor za pregled zadace

// v3.9.1.0 (2008/02/12) + Preimenovan bivsi admin_pregled, dodatna kontrola pristupa
// v3.9.1.1 (2008/03/08) + Nova tabela auth, ukidamo labgrupe totalno
// v3.9.1.2 (2008/03/22) + Prebaceno sve na $conf_files_path i drugi sitni bugovi
// v3.9.1.3 (2008/03/26) + Popravljen javascript
// v3.9.1.4 (2008/05/16) + Dodano polje userid u tabeli zadatak koje odredjuje ko je zadnji izmjenio podatak (da li ima potrebe prikazati?); dodano polje $komponenta u poziv update_komponente() radi brzeg izvrsenja
// v3.9.1.5 (2008/08/28) + Tabela osoba umjesto auth
// v3.9.1.6 (2008/10/03) + Izmjena statusa i izvrsenje zadace prebaceni na genform() (radi sigurnosnih aspekata istog) i POST metod (radi sukladnosti sa RFCom koji nalaze da se sve potencijalno destruktivne akcije rade kroz POST)
// v3.9.1.7 (2008/10/19) + Popravljeno jos bugova izazvanih prelaskom na POST
// v3.9.1.8 (2009/01/22) + Dozvoliti unos bodova iz zadace sa zarezom
// v4.0.0.0 (2009/02/19) + Release
// v4.0.9.1 (2009/03/25) + nastavnik_predmet preusmjeren sa tabele ponudakursa na tabelu predmet
// v4.0.9.2 (2009/04/01) + Tabela zadaca preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.3 (2009/04/05) + Zadatak tipa attachment nije prikazivan osim ako je status 1
// v4.0.9.4 (2009/05/15) + Direktorij za zadace je sada predmet-ag umjesto ponudekursa
// v4.0.9.5 (2009/05/25) + Upiti u prvom dijelu skripte su postali prekomplikovani, pa se potkrala greska da je odredjivana pogresna ponudakursa (ne ona koju student slusa nego neka random)
// v4.0.9.6 (2009/08/11) + Dodajem osobu koja je napravila izmjenu u log na sugestiju prof. Nosovica



function saradnik_zadaca() {

global $conf_files_path,$userid,$user_siteadmin;

require("lib/autotest.php"); 
require("lib/manip.php"); // radi update_komponente


// --------------------
// Standardni ulazni podaci i potrebne varijable

$stud_id=intval($_REQUEST['student']);
$zadaca=intval($_REQUEST['zadaca']);
$zadatak=intval($_REQUEST['zadatak']);



// Prava pristupa

if (!$user_siteadmin) {
	// Da li je nastavnik na predmetu?
	$q10 = myquery("select count(*) from nastavnik_predmet as np, zadaca as z where z.id=$zadaca and z.predmet=np.predmet and z.akademska_godina=np.akademska_godina and np.nastavnik=$userid");
	if (mysql_result($q10,0,0)<1) {
		zamgerlog("privilegije (student u$stud_id zadaca z$zadaca)",3); // nivo 3: greska
		niceerror("Nemate pravo izmjene ove zadaće");
		return;
	}

	// Ogranicenja (tabela: ogranicenje) ne provjeravamo jer bi to bilo prekomplikovano,
	// a pitanje je da li ima smisla

}


// Podaci o zadaci

$q20 = myquery("select p.geshi, p.ekstenzija, z.attachment, z.naziv, z.zadataka, z.komponenta, z.predmet, z.akademska_godina, z.programskijezik from zadaca as z, programskijezik as p where z.id=$zadaca and z.programskijezik=p.id");
if (mysql_num_rows($q20)<1) {
	zamgerlog("nepostojeca zadaca $zadaca",3);
	niceerror("Neispravna zadaća.");
	exit;
}

$jezik = mysql_result($q20,0,0);
$ekst = mysql_result($q20,0,1);
$attach = mysql_result($q20,0,2);
$naziv_zadace = mysql_result($q20,0,3);
$komponenta = mysql_result($q20,0,5);
$predmet = mysql_result($q20,0,6);
$ag = mysql_result($q20,0,7);
$id_jezika = mysql_result($q20,0,8);


if (mysql_result($q20,0,4)<$zadatak || $zadatak<1) {
	zamgerlog("pokusao pristupiti nepostojecem zadatku $zadatak u zadaci z$zadaca",3);
	niceerror("Neispravan broj zadatka.");
	exit;
}



// Podaci o studentu

$q50 = myquery("select ime, prezime from osoba where id=$stud_id");
if (mysql_num_rows($q50)<1) {
	zamgerlog("nepostojeci student $stud_id",3);
	niceerror("Neispravan student.");
	exit;
}

$ime_studenta = mysql_result($q50,0,0);
$prezime_studenta = mysql_result($q50,0,1);

$lokacijazadaca="$conf_files_path/zadace/$predmet-$ag/$stud_id/";





// --------------------
// AKCIJE


// Akcija: Ispis diffa

if ($_GET['akcija'] == "diff") {
	$diff_id=intval($_GET['diff_id']);
	$q60 = myquery("select diff from zadatakdiff where zadatak=$diff_id");
	$diff = mysql_result($q60,0,0);

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
			?><p><img src="images/16x16/zad_ok.png" width="16" height="16"> 
			Program se izvršio bez problema.</p><?
		} else {
			?><p><img src="images/16x16/zad_bug.png" width="16" height="16">
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
		$q70 = myquery("select ulaz from stdin where zadaca=$zadaca and redni_broj=$zadatak");
		while ($r70 = mysql_fetch_row($q70)) {
			print "<h2>Ulaz: '$r70[0]'</h2>";
			izvrsi($r70[0], $jezik, $lokacijazadaca, $zadaca, $zadatak, $ekst);
		}
	} else {
		$stdin = $_POST['stdin'];
		$mstdin = my_escape($stdin);
		$q70 = myquery("select count(*) from stdin where ulaz='$mstdin' and zadaca=$zadaca and redni_broj=$zadatak");
		if (mysql_result($q70,0,0)==0) 
			$q80 = myquery("insert into stdin set ulaz='$mstdin', zadaca=$zadaca, redni_broj=$zadatak");
		izvrsi($stdin, $jezik, $lokacijazadaca, $zadaca, $zadatak, $ekst);
	}
	
	?>
	<p><a href="javascript:history.go(-1)">Nazad</a></p>
	<?
	
	return;
}


// Akcija: Izmjena statusa

if ($_POST['akcija'] == "slanje" && check_csrf_token()) {

	$komentar = my_escape($_POST['komentar']);
	$status = intval($_POST['status']);
	$bodova = floatval(str_replace(",",".",$_POST['bodova']));

	// Osiguravamo da se filename prenese u svaku sljedeću instancu zadatka
	$filename = $izvjestaj_skripte = '';
	$q90 = myquery("select filename, izvjestaj_skripte from zadatak where zadaca=$zadaca and redni_broj=$zadatak and student=$stud_id  order by id desc limit 1");
	if (mysql_num_rows($q90) > 0) {
		$filename = mysql_real_escape_string(mysql_result($q90,0,0));
		$izvjestaj_skripte = mysql_real_escape_string(mysql_result($q90,0,1)); // Već je sanitiziran HTML
	}

	$q100 = myquery("insert into zadatak set zadaca=$zadaca, redni_broj=$zadatak, student=$stud_id, status=$status, bodova=$bodova, vrijeme=now(), komentar='$komentar', filename='$filename', izvjestaj_skripte='$izvjestaj_skripte', userid=$userid");

	// Odredjujemo ponudu kursa (za update komponente)
	$q110 = myquery("select pk.id from student_predmet as sp, ponudakursa as pk where sp.student=$stud_id and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");

	update_komponente($stud_id, mysql_result($q110,0,0), $komponenta);

	zamgerlog("izmjena zadace (student u$stud_id zadaca z$zadaca zadatak $zadatak)",2);

	// Nakon izmjene statusa, nastavljamo normalno sa prikazom zadatka
}


if ($_REQUEST["akcija"] == "test_detalji") {
	$test = intval($_REQUEST['test']);

	// Provjera spoofinga testa
	$q10 = myquery("SELECT COUNT(*) FROM autotest WHERE id=$test AND zadaca=$zadaca AND zadatak=$zadatak");
	if (mysql_result($q10,0,0) == 0) {
		niceerror("Odabrani test nije sa odabrane zadaće.");
		return;
	}

	autotest_detalji($test, $stud_id, /* $param_nastavnik = */ true); 
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
			$q10 = myquery("SELECT COUNT(*) FROM autotest WHERE id=$test AND zadaca=$zadaca AND zadatak=$zadatak");
			if (mysql_result($q10,0,0) == 0) {
				niceerror("Odabrani test nije sa odabrane zadaće.");
				return;
			}

			$src = autotest_sa_kodom($test, $stud_id, /* $param_nastavnik = */ true); 
		} else
			$src = file_get_contents($the_file);

		$no_lines = count(explode("\n", $src));
	
		// geshi - biblioteka za syntax highlighting
		
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

		if ($_REQUEST["akcija"] == "test_sa_kodom") return;

		// Formular za izvršavanje programa
		if ($id_jezika > 0) {
			?>
			<script type="text/javascript" src="js/combo-box.js"></script>
			<center><table style="border:1px solid silver;" cellspacing="0" cellpadding="6"><tr><td>
			Izvrši program sa sljedećim parametrima (kucajte \n za tipku enter):<br/>
			<?=genform("POST")?>
			<input type="hidden" name="akcija" value="izvrsi">
			<select name="stdin" onKeyPress="edit(event)" onBlur="this.editing = false;">
			<?

			// Zadnje korišteni stdin se čuva u bazi
			$q120 = myquery("select ulaz from stdin where zadaca=$zadaca and redni_broj=$zadatak order by id desc");
			if (mysql_num_rows($q120)<1)
				print "<option></option>"; // bez ovoga nije moguće upisati novi tekst
			while ($r120 = mysql_fetch_row($q120)) {
				print "<option value=\"$r120[0]\">$r120[0]</option>\n";
			}
			?>
			</select><br/>
		
			<b>Pažnja!</b> Prije pokretanja provjerite da li program sadrži opasne naredbe.<br/>
			<input type="submit" value=" Izvrši program "> <input type="submit" name="sve" value=" Izvrši sve primjere odjednom ">
			</form></table></center><br/>&nbsp;<br/>
			<?
		}
	}


} else {
	// Attachment

	$q130 = myquery("select filename,UNIX_TIMESTAMP(vrijeme) from zadatak where zadaca=$zadaca and redni_broj=$zadatak and student=$stud_id order by id desc limit 1");
	if (mysql_num_rows($q130) > 0) {
		$filename = mysql_result($q130,0,0);
		$the_file = "$lokacijazadaca$zadaca/$filename";

		if ($filename && file_exists($the_file)) {
			$vrijeme = date("d. m. Y. h:i:s", mysql_result($q130,0,1));
			$velicina = nicesize(filesize($the_file));
			$icon = "images/mimetypes/" . getmimeicon($the_file);
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

$q140 = myquery("select status,bodova,izvjestaj_skripte,komentar from zadatak where zadaca=$zadaca and redni_broj=$zadatak and student=$stud_id order by id desc limit 1");
if (mysql_num_rows($q140) > 0) {
	$status = mysql_result($q140,0,0);
	$bodova = mysql_result($q140,0,1);
	$izvjestaj_skripte = str_replace("\n","<br/>",mysql_result($q140,0,2));
	$komentar = mysql_result($q140,0,3);
	$komentar = str_replace("\"","&quot;",$komentar);

	// Koristimo poseban upit da bismo odredili vrijeme slanja prve verzije
	$q150 = myquery("select UNIX_TIMESTAMP(vrijeme) from zadatak where zadaca=$zadaca and redni_broj=$zadatak and student=$stud_id order by id limit 1");
	$vrijeme_slanja = date("d. m. Y. H:i:s",mysql_result($q150,0,0));

	?>
	
	<table border="0">
	<tr>
		<td>Vrijeme slanja:</td>
		<td><b><?=$vrijeme_slanja?></b></td>
	</tr>
	<?

	// Autotest nalaz
	$nalaz_autotesta = autotest_tabela($stud_id, $zadaca, $zadatak, /*$nastavnik =*/ true);
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


$q160 = myquery("select id,UNIX_TIMESTAMP(vrijeme),status,bodova,komentar,userid from zadatak where zadaca=$zadaca and redni_broj=$zadatak and student=$stud_id order by vrijeme");
if (mysql_num_rows($q160)>1) {

?>

<p>Historija izmjena:</p>
<ul><?
	while ($r160 = mysql_fetch_row($q160)) {
		$imeprezime="";
		if ($r160[5]>0) {
			$q165 = myquery("select ime, prezime from osoba where id=$r160[5]");
			if (mysql_num_rows($q165)>0) {
				$imeprezime = mysql_result($q165,0,0)." ".mysql_result($q165,0,1);
			}
		}

		$vrijeme_slanja = date("d. m. Y. H:i:s",$r160[1]);
		print "<li><b>$vrijeme_slanja";
		if ($imeprezime != "") print " ($imeprezime)";
		print ":</b> ".$statusi_array[$r160[2]];
		if ($r160[3]>0) print " (".$r160[3]." bodova)";
		if (strlen($r160[4])>0) print " - &quot;".$r160[4]."&quot;";
		$q170 = myquery("select count(zadatak) from zadatakdiff where zadatak=$r160[0]");
		if (mysql_result($q170,0,0)>0)
			print " (<a href=\"index.php?sta=saradnik/zadaca&akcija=diff&zadaca=$zadaca&zadatak=$zadatak&student=$stud_id&diff_id=$r160[0]\">diff</a>)";
		print "</li>"; 
	}

?></ul><?


} 

// Kraj historije izmjena



} // function saradnik_zadaca()






?>
