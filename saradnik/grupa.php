<?

// SARADNIK/GRUPA - administracija jedne grupe na predmetu



function saradnik_grupa() {

global $userid,$user_siteadmin;

require_once("lib/student_predmet.php"); // update_komponente
require_once("lib/utility.php"); // procenat, bssort


print '<p><a href="index.php?sta=saradnik/intro">Spisak predmeta i grupa</a></p>'."\n";


// ------- ULAZNI PARAMETRI

$labgrupa = int_param('id');
$kreiranje = int_param('kreiranje');


if ($labgrupa>0) {
	// Određujemo predmet i ag za labgrupu
	$q30 = db_query("select naziv, predmet, akademska_godina, virtualna from labgrupa where id=$labgrupa");
	if (db_num_rows($q30)<1) {
		biguglyerror("Nemate pravo ulaska u ovu grupu!");
		zamgerlog("nepostojeca labgrupa $labgrupa",3); // 3 = greska
		zamgerlog2("nepostojeca labgrupa", $labgrupa);
		return;
	}
	$naziv = db_result($q30,0,0);
	$predmet = db_result($q30,0,1);
	$ag = db_result($q30,0,2);
	$grupa_virtualna = db_result($q30,0,3);

} else {
	// Ako nije definisana grupa, probacemo preko predmeta i ag uci u virtuelnu grupu
	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);
	$q35 = db_query("select id, naziv from labgrupa where predmet=$predmet and akademska_godina=$ag and virtualna=1");
	if (db_num_rows($q35)<1) {
		biguglyerror("Nemate pravo ulaska u ovu grupu!");
		zamgerlog("nepostojeca virtualna labgrupa za predmet pp$predmet ag$ag",3); // 3 = greska
		zamgerlog2("nepostojeca virtualna labgrupa", $predmet, $ag);
		return;
	}
	$labgrupa = db_result($q35,0,0);
	$naziv = db_result($q35,0,1);
	$grupa_virtualna = 1;
}



// Da li korisnik ima pravo ući u grupu?
$privilegija = "";
if (!$user_siteadmin) {
	$q40 = db_query("select nivo_pristupa from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
	if (db_num_rows($q40)<1) {
		biguglyerror("Nemate pravo ulaska u ovu grupu!");
		zamgerlog ("nastavnik nije na predmetu (labgrupa g$labgrupa)", 3);
		zamgerlog2("nije saradnik na predmetu", $predmet, $ag);
		return;
	}
	$privilegija = db_result($q40,0,0);

	$q50 = db_query("select o.labgrupa from ogranicenje as o, labgrupa as l where o.nastavnik=$userid and o.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag");
	if (db_num_rows($q50)>0) {
		$nasao=0;
		while ($r50 = db_fetch_row($q50)) {
			if ($r50[0] == $labgrupa) { $nasao=1; break; }
		}
		if ($nasao == 0) {
			biguglyerror("Nemate pravo ulaska u ovu grupu!");
			zamgerlog("ogranicenje na labgrupu g$labgrupa", 3); // 3 - greska
			zamgerlog2("ima ogranicenje na labgrupu", $labgrupa);
			return;
		}
	}
}


// Spisak komponenti koje su zastupljene na predmetu

$tipovi_komponenti=array();
$q52 = db_query("select k.id, k.tipkomponente from akademska_godina_predmet as agp, tippredmeta_komponenta as tpk, komponenta as k where agp.akademska_godina=$ag and agp.predmet=$predmet and agp.tippredmeta=tpk.tippredmeta and tpk.komponenta=k.id");
while ($r52 = db_fetch_row($q52))
	$tipovi_komponenti[$r52[0]] = $r52[1];


// ------- AKCIJE

// Dodavanje casa

if (param('akcija') == 'dodajcas' && check_csrf_token()) {
	// KOMPONENTA
	// Ovaj kod radi samo sa jednom komponentom prisustva. U budućnosti to bi moglo biti popravljeno, ali realno nema prevelike potrebe

	$datum = intval($_POST['godina'])."-". intval($_POST['mjesec'])."-". intval($_POST['dan']);
	$vrijeme = $_POST['vrijeme'];
	if (!preg_match("/^\d?\d\:\d\d$/", $vrijeme)) {
		niceerror("Vrijeme nije u ispravnom formatu!");
		print "<p>Vrijeme mora biti oblika HH:MM, a vi ste unijeli '$vrijeme'.</p>";
		print "<p><a href=\"?sta=saradnik/grupa&id=$labgrupa\">Nazad</a></p>";
		return;
	}
	$predavanje = intval($_POST['predavanje']);

	// Ako se klikne na refresh, datum moze biti 0-0-0...
	if ($datum != "0-0-0") {
		// Tražimo komponentu prisustva i uzimamo prvu
		// FIXME: praktično je nemoguće registrovati čas za drugu komponentu
		$komponenta=0;
		foreach ($tipovi_komponenti as $k_id => $tip) {
			if ($tip==3) { // 3 = prisustvo
				$komponenta = $k_id;
				break;
			}
		}
		if ($komponenta==0) {
			niceerror("Nije definisana komponenta za prisustvo na ovom predmetu.");
			zamgerlog("nije definisana komponenta za prisustvo na pp$predmet", 3);
			zamgerlog2("nije definisana komponenta za prisustvo", $predmet, $ag);
			return;
		}

		$kviz = intval($_REQUEST['kviz']);

		$q60 = db_query("insert into cas set datum='$datum', vrijeme='$vrijeme', labgrupa=$labgrupa, nastavnik=$userid, komponenta=$komponenta, kviz=$kviz");
		$cas_id = db_insert_id();
	
		// Max bodova za komponentu
		$q75 = db_query("select maxbodova, opcija from komponenta where id=$komponenta");
		$maxbodova = db_result($q75,0,0);
		$opcija = db_result($q75,0,1);
	
		// dodajemo u bazu default podatke za prisustvo i ocjene
	
		$q80 = db_query("select student from student_labgrupa where labgrupa=$labgrupa");
		while ($r80 = db_fetch_row($q80)) {
			$stud_id = $r80[0];
			$prisustvo = intval($_POST['prisustvo']);

			// Potrebna nam je ponudakursa za update_komponente
			$q53 = db_query("select sp.predmet from student_predmet as sp, ponudakursa as pk where sp.student=$stud_id and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
			$ponudakursa = db_result($q53,0,0);

			$q90 = db_query("insert into prisustvo set student=$stud_id, cas=$cas_id, prisutan=$prisustvo");
			
			// Update radimo samo ako se registruje odsustvo ili ako je opcija=-1 (proporcionalni bodovi)
			if ($prisustvo==0 || $opcija==-1 || $opcija==-3)
				update_komponente($stud_id,$ponudakursa,$komponenta);
			else {
				// Ako nema uopšte bodova za komponentu, ubacićemo broj bodova
				$q95 = db_query("select count(*) from komponentebodovi where student=$stud_id and predmet=$ponudakursa and komponenta=$komponenta");
				if (db_result($q95,0,0)==0) {
					$q97 = db_query("insert into komponentebodovi set student=$stud_id, predmet=$ponudakursa, komponenta=$komponenta, bodovi=$maxbodova");
				}
			}
		}
		
		// Ako je odabrano "sa kvizom" kreiramo kviz
		if ($kviz > 0) {
			$q98 = db_query("select trajanje_kviza from kviz where id=$kviz");
			$trajanje = db_result($q98,0,0)*2;
			$q99 = db_query("update kviz set vrijeme_pocetak=NOW(), vrijeme_kraj=NOW() + INTERVAL $trajanje SECOND, labgrupa=$labgrupa where id=$kviz");
		}
	
		zamgerlog("registrovan cas c$cas_id",2); // nivo 2: edit
		zamgerlog2("registrovan cas", $cas_id);
	}
}


// Brisanje casa

if (param('akcija') == 'brisi_cas' && check_csrf_token()) {
	$cas_id = intval($_POST['_lv_casid']);

	// Updatujemo komponentu svima koji su bili prisutni
	$q103 = db_query("select komponenta from cas where id=$cas_id");
	if (db_num_rows($q103)>0) {
		$komponenta = db_result($q103,0,0);
		
		$q105 = db_query("select sp.student, sp.predmet from prisustvo as pr, student_predmet as sp, ponudakursa as pk where pr.cas=$cas_id and pr.student=sp.student and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
		$studenti = $ponudekursa = array();
		while ($r105 = db_fetch_row($q105)) {
			array_push($studenti, $r105[0]);
			$ponudekursa[$r105[0]] = $r105[1];
		}

		$q100 = db_query("delete from prisustvo where cas=$cas_id");
		$q110 = db_query("delete from cas where id=$cas_id");
		
		foreach($studenti as $student)
			update_komponente($student, $ponudekursa[$student], $komponenta);
		
		zamgerlog("obrisan cas $cas_id",2);
		zamgerlog2("obrisan cas", $cas_id);
	}
}




// ------- ZAGLAVLJE STRANICE (naslov i sl.)


$q130 = db_query("select naziv from predmet where id=$predmet");
$pime = db_result($q130,0,0); // Ne bi se smjelo desiti da je nepostojeci predmet, posto se to odredjuje iz labgrupe

?>
<br />
<center><h1><?=$pime?> - <?=$naziv?></h1></center>
<?



// -------- UPIT: SPISAK STUDENATA U GRUPI

$q310 = db_query("select a.id,a.ime,a.prezime,a.brindexa from osoba as a,student_labgrupa as sl where a.id=sl.student and sl.labgrupa=$labgrupa");

$imeprezime = array();
$brind = array();
while ($r310 = db_fetch_row($q310)) {
	$stud_id = $r310[0];
	$stud_ime = $r310[1];
	$stud_prezime = $r310[2];
	$stud_brind = $r310[3];
	
	// Dodajemo ime grupe pored imena studenta ako je grupa virtualna
	if ($grupa_virtualna == 1) {
		$q315 = db_query("select lg.naziv from labgrupa as lg, student_labgrupa as sl where sl.student=$stud_id and sl.labgrupa=lg.id and lg.virtualna=0 and lg.predmet=$predmet and lg.akademska_godina=$ag");
		if (db_num_rows($q315)>0) $stud_ime .= " (".db_result($q315,0,0).")";
	}
	
	$imeprezime[$stud_id] = "$stud_prezime&nbsp;$stud_ime";
	$brind[$stud_id] = $stud_brind;
}
uasort($imeprezime,"bssort"); // bssort - bosanski jezik


// Ako nema nikoga u grupi, prekidamo rad odmah

if (count($imeprezime) == 0) {
	print "<p>Nijedan student nije u grupi</p>\n";
	return;
}



// JavaScript za prikaz popup prozora (trenutno se koristi samo za komentare)
//  * FF ne podržava direktan poziv window.open() iz eventa 

?>
<script language="JavaScript">
function firefoxopen(p1,p2,p3) { 
	window.open(p1,p2,p3);
}
</script>

<?


// Cool editing box
if ($privilegija=="nastavnik" || $privilegija=="super_asistent" || $user_siteadmin) {
	cool_box('ajah_start("index.php?c=N&sta=common/ajah&akcija=izmjena_ispita&idpolja="+zamger_coolbox_origcaller.id+"&vrijednost="+coolboxedit.value, "undo_coolbox()", "zamger_coolbox_origcaller=false");');
	?>
	<script language="JavaScript">
	function undo_coolbox() {
		var greska = document.getElementById("zamger_ajah-info").innerText || document.getElementById("zamger_ajah-info").textContent;
		if (!greska.match(/\S/)) greska = "Došlo je do greške. Molimo kontaktirajte administratora.";
		alert(greska);
		zamger_coolbox_origcaller.innerHTML = zamger_coolbox_origvalue;
		zamger_coolbox_origcaller=false;
	}
	</script>
	<?
}



// ------- PODACI O ZADAĆAMA

if (in_array(4, $tipovi_komponenti)) { // 4 = zadaće

	// SPISAK ZADAĆA I PRIPREMA ZAGLAVLJA TABELE

	$zad_id_array = array();
	$zadace_zaglavlje1 = "";
	$zadace_zaglavlje2 = "";
	$q205 = db_query("SELECT k.id, k.gui_naziv FROM akademska_godina_predmet as agp, tippredmeta_komponenta as tpk, komponenta as k
	WHERE agp.predmet=$predmet and agp.akademska_godina=$ag and agp.tippredmeta=tpk.tippredmeta and tpk.komponenta=k.id and k.tipkomponente=4 ORDER BY k.id");
	while ($r205 = db_fetch_row($q205)) {
		$brzadaca = 0;
		$zad_komponenta_zaglavlje = "";
		$komponenta = $r205[0];
		
		// Razvrstavamo zadaće po komponentama
		$q210 = db_query("select id,naziv,zadataka,bodova from zadaca where predmet=$predmet and akademska_godina=$ag and komponenta=$komponenta order by id");
		while ($r210 = db_fetch_row($q210)) {
			$zad_komponenta_zaglavlje .= "<td width=\"60\" align=\"center\">$r210[1]<br /><a href=\"?sta=saradnik/svezadace&grupa=$labgrupa&zadaca=$r210[0]\">Download</a></td>\n";
			$zad_id_array[] = $r210[0];
			$zad_brz_array[$r210[0]] = $r210[2];
			$zad_nazivi[$r210[0]] = $r210[1];
			$mogucih_bodova += $r210[3];
			$brzadaca++;
			$minw += 60;
		}

		if ($brzadaca>0) {
			$zadace_zaglavlje1 .= "<td align=\"center\" colspan=\"$brzadaca\">$r205[1]</td>\n";
			$zadace_zaglavlje2 .= $zad_komponenta_zaglavlje;
		}
	}

	// CACHE REZULTATA ZADAĆA
	
	$zadace_statusi = array();
	$zadace_bodovi = array();
	foreach ($zad_id_array as $zadaca_id) {
		for ($zadatak=1; $zadatak<=$zad_brz_array[$zadaca_id]; $zadatak++) {
			foreach ($imeprezime as $stud_id => $stud_imepr) {
				$rezultat = db_query_assoc("SELECT status, bodova FROM zadatak WHERE zadaca=$zadaca_id AND redni_broj=$zadatak AND student=$stud_id ORDER BY id DESC LIMIT 1");
				if (!$rezultat) 
					$zadace_statusi[$zadaca_id][$zadatak][$stud_id]=0;
				else {
					$zadace_bodovi[$zadaca_id][$zadatak][$stud_id] = $rezultat['bodova'];
					$zadace_statusi[$zadaca_id][$zadatak][$stud_id] = $rezultat['status']+1;
				}
			}
		}
	}
	/*$q300 = db_query("SELECT z.zadaca,z.redni_broj,z.student,z.status,z.bodova
	FROM zadatak as z,student_labgrupa as sl 
	WHERE z.student=sl.student and sl.labgrupa=$labgrupa
	ORDER BY z.id");
	while ($r300 = db_fetch_row($q300)) {
		// Slog sa najnovijim IDom se smatra mjerodavnim
		// Ostali su u bazi radi historije
		$zadace_bodovi[$r300[0]][$r300[1]][$r300[2]]=$r300[4];
		$zadace_statusi[$r300[0]][$r300[1]][$r300[2]]=$r300[3]+1;
		// Dodajemo 1 na status kako bismo kasnije mogli znati da li 
		// je vrijednost niza definisana ili ne.
		// undef ne radi :(
	}*/

	
	// SPISAK NEPREGLEDANIH ZADAĆA

	// JavaScript za prikaz popup prozora sa zadaćom
	//  * Kod IE naslov prozora ('zadaca') ne smije sadržavati razmak i
	// ne smije biti prazan, a inače je nebitan

	?>
	<script language="JavaScript">
	function openzadaca(e, student,zadaca,zadatak) {
		var evt = e || window.event;
		var url='index.php?sta=saradnik/zadaca&student='+student+'&zadaca='+zadaca+'&zadatak='+zadatak;
		if (evt.shiftKey)
			window.open(url,'_blank','width=600,height=600,scrollbars=yes');
		else
			window.open(url,'zadaca','width=600,height=600,scrollbars=yes');
	}
	</script>
	
	<?
	
	
	$print="";
	foreach ($zadace_statusi as $zadaca => $data1) {
		foreach ($data1 as $zadatak => $data2) {
			foreach($data2 as $student => $status) {
				if ($status==5)
				$print .= '<li><a href="#" onclick="javascript:openzadaca(event, \''.$student.'\',\''.$zadaca.'\',\''.$zadatak.'\')">'.$imeprezime[$student]." - ".$zad_nazivi[$zadaca].", zadatak ".$zadatak."</a></li>";
			}
		}
	}

	/*$q150 = db_query(
	"SELECT zk.zadaca, zk.redni_broj, zk.student, a.ime, a.prezime, zk.status, z.naziv
	FROM zadatak as zk, osoba as a, student_labgrupa as sl, zadaca as z
	WHERE zk.student=a.id AND zk.student=sl.student 
	AND sl.labgrupa=$labgrupa AND zk.zadaca=z.id AND z.predmet=$predmet AND z.akademska_godina=$ag
	ORDER BY zk.zadaca, zk.redni_broj, a.prezime, a.ime, zk.id DESC");
	
	
	$mzadaca=0; $mzadatak=0; $mstudent=0; $print="";
	while ($r150 = db_fetch_row($q150)) {
		if ($r150[0]==$mzadaca && $r150[1]==$mzadatak && $r150[2]==$mstudent) continue;
		$mzadaca=$r150[0]; $mzadatak=$r150[1]; $mstudent=$r150[2];
		if ($r150[5]!=4) continue;
		$print .= '<li><a href="#" onclick="javascript:openzadaca(event, \''.$r150[2].'\',\''.$r150[0].'\',\''.$r150[1].'\')">'.$r150[3]." ".$r150[4]." - ".$r150[6].", zadatak ".$r150[1]."</a></li>";
	}*/
	if ($print != "") print "<h2>Nove zadaće za pregled:</h2>\n<ul>$print</ul>";
}





// ------- FORMA ZA NOVI ČAS

if (in_array(3, $tipovi_komponenti)) { // 3 = prisustvo
	$dan=date("d"); $mjesec=date("m"); $godina=date("Y"); 
	$vrijeme=date("H:i");

	// Ujedno ćemo definisati i neke JavaScripte za prisustvo

	?>
	<table border="0" width="100%"><tr><td valign="top" width="50%">&nbsp;</td>
	<td valign="top" width="50%">
		Registrujte novi čas:<br/>
		<?=genform("POST")?>
		<input type="hidden" name="akcija" value="dodajcas">
	
		Datum:
		<select name="dan" class="default"><?
		for ($i=1; $i<=31; $i++) {
			print "<option value=\"$i\"";
			if ($i==$dan) print " selected";
			print ">$i</option>";
		}
		?></select>&nbsp;&nbsp;
		<select name="mjesec" class="default"><?
		for ($i=1; $i<=12; $i++) {
			print "<option value=\"$i\"";
			if ($i==$mjesec) print " selected";
			print ">$i</option>";
		}
		?></select>&nbsp;&nbsp;
		<select name="godina" class="default"><?
		for ($i=2005; $i<=2020; $i++) {
			print "<option value=\"$i\"";
			if ($i==$godina) print " selected";
			print ">$i</option>";
		}
		?></select><br/>
		Vrijeme: <input type="text" size="10" name="vrijeme" value="<?=$vrijeme?>"  class="default">
		<input type="submit" value="Registruj"  class="default"><br/><br/>
	
		<input type="radio" name="prisustvo" value="1" CHECKED>Svi prisutni
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="radio" name="prisustvo" value="0">Svi odsutni
		<?
		
		// Kreiraj čas sa kvizom
		$q160 = db_query("select smp.aktivan from studentski_modul_predmet as smp, studentski_modul as sm where smp.predmet=$predmet and smp.akademska_godina=$ag and smp.studentski_modul=sm.id and sm.modul='student/kviz'");
		if (db_num_rows($q160)>0 && db_result($q160,0,0)==1) {
			?>
			<br>
			Sa kvizom: <select name="kviz"><option value="0">/</option>
			<?
			
			$q170 = db_query("select id,naziv from kviz where predmet=$predmet and akademska_godina=$ag and aktivan=1");
			while ($r170 = db_fetch_row($q170)) {
				print "<option value=\"$r170[0]\">$r170[1]</option>\n";
			}
			
			?>
			</select>
			<?
		}
		
		
		?>
	
	</form>
	</td></tr></table>
	
	<script language="JavaScript">
	var prisutan;
	var oldState;
	var boje = [ "", "#FFCCCC", "#CCFFCC", "#FFFFCC" ];
	var tekstovi = [ "", "NE", "DA", "/" ];
	
	function azuriraj_polje(status, student, cas) {
		var celija = document.getElementById("dane-"+student+"-"+cas);
		var tekst = document.getElementById("danetekst-"+student+"-"+cas);
		if (status == -1) {
			celija.style.background = "#FFFFFF";
			celija.style.backgroundImage = "url(static/images/Animated-Hourglass.gif)";
			celija.style.backgroundRepeat = "no-repeat";
			celija.style.backgroundPosition = "center";
			tekst.innerHTML = "";
		} else {
			celija.style.backgroundImage = "";
			celija.style.background = boje[status];
			tekst.innerHTML = tekstovi[status];
		}
	}

	// Funkcija koja se poziva klikom na polje u tabeli
	function prisustvo(e, student, cas) {
		var val = document.getElementById("danetekst-"+student+"-"+cas).innerHTML;
		azuriraj_polje(-1, student, cas);
		for (i=1; i<=3; i++)
			if (val == tekstovi[i]) oldState = i;
		
		prisutan = 1;
		var evt = e || window.event;
		if (evt.shiftKey) {
			prisutan = 3;
		} else if (oldState == 2) {
			prisutan = 1;
		} else if (oldState == 1) {
			prisutan = 2;
		}
		
		ajax_start(
			"ws/prisustvo",
			"POST",
			{ "student" : student, "cas" : cas, "prisutan" : prisutan },
			function(foo) {
				azuriraj_polje(prisutan, student, cas);
			},
			function(responseText, status, url) {
				azuriraj_polje(oldState, student, cas);
				var greska = "";
				if (status != 200)
					greska = "Došlo je do greške (status: "+status+"). Molimo kontaktirajte administratora";
				else try {
					var object = JSON.parse(responseText);
					greska = object['message'];
				} catch(e) {
					greska = "Došlo je do greške (nevalidan odgovor). Molimo kontaktirajte administratora";
					console.log("Web servis "+url+" nije vratio validan JSON: "+xhttp.responseText);
					console.log(e);
				}
				alert(greska);
			}
		);
	}
	
	function upozorenje(cas) {
		if (confirm("Da li ste sigurni da želite obrisati čas?")) {
			// _lv_casid osigurava da genform() neće dodati još jedno hidden polje
			document.brisanjecasa._lv_casid.value=cas;
			document.brisanjecasa.submit();
		}
		return false;
	}
	
	</script>

	<!-- Pomocna forma za POST brisanje casa -->
	
	<?=genform("POST", "brisanjecasa")?>
	<input type="hidden" name="akcija" value="brisi_cas">
	<input type="hidden" name="_lv_casid" value="">
	</form>

	<?


} // if (in_array(3, $tipovi_komponenti))


// Ispis AJAH box-a neposredno iznad tablice grupe

print ajah_box();
ajax_box();



// ------- TABLICA GRUPE - ZAGLAVLJE


$minw = 0; // minimalna sirina tabele
$mogucih_bodova = 0; // koliko bodova su studenti mogli osvojiti, radi procenta
$zaglavlje1 = "";
$zaglavlje2 = "";
$prisustvo_id_array = array();


// Zaglavlje prisustvo

$q195 = db_query("SELECT k.id, k.gui_naziv, k.maxbodova FROM akademska_godina_predmet as agp, tippredmeta_komponenta as tpk, komponenta as k
WHERE agp.predmet=$predmet and agp.akademska_godina=$ag and agp.tippredmeta=tpk.tippredmeta and tpk.komponenta=k.id and k.tipkomponente=3 ORDER BY k.id");
// Ako nema nijedne komponente prisustva, upit neće vratiti ništa
while ($r195 = db_fetch_row($q195)) {
	$casova = 0;
	$prisustvo_zaglavlje = "";
	$prisustvo_id_array[] = $r195[0];
	$cas_id_array = array();
	$cas_kviz = $cas_kviz_prolaz = array();

	$q200 = db_query("SELECT id,datum,vrijeme,kviz FROM cas where labgrupa=$labgrupa and komponenta=$r195[0] ORDER BY datum, vrijeme");
	while ($r200 = db_fetch_row($q200)) {
		$cas_id = $r200[0];
		list ($cas_godina,$cas_mjesec,$cas_dan) = explode("-",$r200[1]);
		list ($cas_sat,$cas_minuta,$cas_sekunda) = explode(":",$r200[2]);
		$prisustvo_zaglavlje .= "<td align=\"center\">$cas_dan.$cas_mjesec<br/>$cas_sat:$cas_minuta";
		$prisustvo_zaglavlje .= '<br/><a href="javascript:onclick=upozorenje('."'$cas_id'".');"><img src="static/images/16x16/not_ok.png" border="0"></a>';
		$prisustvo_zaglavlje .= "</td>\n";
		$cas_id_array[] = $cas_id;
		$casova++;
		$minw += 40;

		// Kviz
		if ($r200[3] > 0) {
			$cas_kviz[$cas_id] = $r200[3];

			// Odredjujemo bodove za prolaz
			$q202 = db_query("select prolaz_bodova from kviz where id=$r200[3]");
			$cas_kviz_prolaz[$cas_id] = db_result($q202,0,0);
			
			// Ako sam nekom drugom casu ranije dao ovaj id, moram ga obrisati
			foreach ($cas_id_array as $cid) {
				if ($cid == $cas_id) continue;
				if ($cas_kviz[$cid] == $r200[3]) $cas_kviz[$cid]=0;
			}
			
		} else {
			// Ako kviz nije unesen u bazu, tražimo najbliži
			$q203 = db_query("select id, prolaz_bodova from kviz where predmet=$predmet and akademska_godina=$ag and vrijeme_pocetak>='$r200[1]' and vrijeme_pocetak<'$r200[1]' + interval 5 day order by vrijeme_pocetak desc");
			while ($r203 = db_fetch_row($q203)) {
				// Da li je već bio?
				$bio = false;
				foreach ($cas_id_array as $cid) {
					if ($cas_kviz[$cid] == $r203[0]) $bio=true;
				}
				if ($bio) continue;
				$cas_kviz[$cas_id] = $r203[0];
				$cas_kviz_prolaz[$cas_id] = $r203[1];
				break;
			}
		}
	}
	$prisustvo_casovi[$r195[0]] = $cas_id_array;
//	$prisustvo_maxbodova[$r195[0]] = $r195[2];
//	$prisustvo_maxizostanaka[$r195[0]] = $r195[3];
//	$prisustvo_minbodova[$r195[0]] = $r195[4];
	$mogucih_bodova += $r195[2];

	if ($prisustvo_zaglavlje == "") { 
		$prisustvo_zaglavlje = "<td>&nbsp;</td>"; 
		$minw += 40; 
		$casova=1;
	}

	$zaglavlje1 .= "<td align=\"center\" colspan=\"".($casova+1)."\">$r195[1]</td>\n";
	$zaglavlje2 .= $prisustvo_zaglavlje;
	$zaglavlje2 .= "<td>BOD.</td>\n";
}


if (in_array(4, $tipovi_komponenti)) { // 4 = zadaće
	$zaglavlje1 .= $zadace_zaglavlje1;
	$zaglavlje2 .= $zadace_zaglavlje2;
}


// Zaglavlje fiksne komponente

$fiksna_prolaz = array();
$fiksna_id_array = array();
$q215 = db_query("SELECT k.id, k.gui_naziv, k.maxbodova, k.prolaz FROM akademska_godina_predmet as agp, tippredmeta_komponenta as tpk, komponenta as k
WHERE agp.predmet=$predmet and agp.akademska_godina=$ag and agp.tippredmeta=tpk.tippredmeta and tpk.komponenta=k.id and k.tipkomponente=5 ORDER BY k.id");
// Ako nema nijedne fiksne komponente, upit neće vratiti ništa
while ($r215 = db_fetch_row($q215)) {
	$zaglavlje1 .= "<td align=\"center\" rowspan=\"2\">$r215[1]";
	$mogucih_bodova += $r215[2];
	$minw += 60;
	$fiksna_id_array[]=$r215[0];
	$fiksna_prolaz[$r215[0]]=$r215[3];
}


// Zaglavlje ispiti

$broj_ispita=0;
$ispit_zaglavlje="";
$ispit_id_array=array();
$q220 = db_query("select i.id, UNIX_TIMESTAMP(i.datum), k.id, k.kratki_gui_naziv, k.tipkomponente, k.maxbodova, k.prolaz, k.opcija from ispit as i, komponenta as k where i.predmet=$predmet and i.akademska_godina=$ag and i.komponenta=k.id order by i.datum,i.komponenta");
// Ako nema komponenti sa ispitima, neće biti registrovan nijedan ispit
while ($r220 = db_fetch_row($q220)) {
	if ($r220[4]==5)
		$ispit_zaglavlje .= "<td align=\"center\">$r220[3]</td>\n";
	else
		$ispit_zaglavlje .= "<td align=\"center\">$r220[3]<br/> ".date("d.m.",$r220[1])."</td>\n";

	$ispit_id_array[] = $r220[0];
	$ispit_komponenta[$r220[0]] = $r220[2];

	// Pripremamo podatke o komponentama
	$komponenta_tip[$r220[2]] = $r220[4];
	$komponenta_maxb[$r220[2]] = $r220[5];
	$komponenta_prolaz[$r220[2]] = $r220[6];
	$komponenta_opcija[$r220[2]] = "$r220[7]";
	if ($r220[4]!=2) $mogucih_bodova += $r220[5];

	$minw += 40;
	$broj_ispita++;
}

if ($broj_ispita>0) {
	$zaglavlje1 .= "<td align=\"center\" colspan=\"$broj_ispita\">Ispiti</td>\n";
	$zaglavlje2 .= $ispit_zaglavlje;
}


// Zaglavlje konacna ocjena

//$ispis_konacna=0;
//$q230 = db_query("select count(*) from konacna_ocjena where predmet=$predmet_id");
//if (db_result($q230,0,0)>0) {
//	$minw += 40;
	$ispis_konacna=1;
//}

//if ($casova==0) $casova=1;


// ISPIS ZAGLAVLJA

$minw += 70; // ukupno
$minw += 45; // broj indexa
$minw += 100; // ime i prezime
$minw += 40; // komentar
$minw += 40; // bodovi prisustvo


?>
<table cellspacing="0" cellpadding="2" border="1" <? if ($minw>800) print "width=\"$minw\""; ?>>
<tr>
	<td rowspan="2" align="center" valign="center">Ime i prezime</td>
	<td rowspan="2" align="center" valign="center">Broj indexa</td>
	<td rowspan="2" align="center" valign="center">Ko-<br/>men-<br/>tar</td>
	<?=$zaglavlje1?>
	<td align="center" valign="center" rowspan="2">&nbsp;&nbsp;<b>UKUPNO</b>&nbsp;&nbsp;</td>
	<? if ($ispis_konacna==1) { ?><td rowspan="2" align="center">Konačna<br/>ocjena</td><? } ?>
</tr>
<tr>
	<?=$zaglavlje2?>
</tr>
<?


// Ikone i statusi za zadaće
$stat_icon = array("bug", "wait_icon", "copy", "bug", "view", "ok");
$stat_tekst = array("Bug u programu", "Automatsko testiranje u toku", "Zadaća prepisana", "Bug u programu", "Potrebno pregledati", "Zadaća OK");



// Glavna petlja za ispis tabele studenata

$redni_broj=0;
foreach ($imeprezime as $stud_id => $stud_imepr) {
	$redni_broj++;
?>
<tr>
	<td id="student_<?=$stud_id?>"><?=$redni_broj?>.&nbsp;<a href="index.php?sta=saradnik/student&student=<?=$stud_id?>&predmet=<?=$predmet?>&ag=<?=$ag?>"><?=$stud_imepr?></a></td>
	<td><?=$brind[$stud_id]?></td>
	<td align="center"><a href="javascript:firefoxopen('index.php?sta=saradnik/komentar&student=<?=$stud_id?>&labgrupa=<?=$labgrupa?>','blah3','width=350,height=320,status=0,toolbar=0,resizable=1,location=0,menubar=0,scrollbars=1');"><img src="static/images/16x16/comment_blue.png" border="0" width="16" height="16" alt="Komentar na rad studenta" title="Komentar na rad studenta"></a></td>
<?

	$prisustvo_ispis=$zadace_ispis=$ispiti_ispis="";
	$bodova=0;


	// PRISUSTVO - ISPIS

	foreach($prisustvo_id_array as $pid) {

	$cas_id_array = $prisustvo_casovi[$pid];

	if (count($cas_id_array)==0) $prisustvo_ispis .= "<td>&nbsp;</td>";
	$odsustvo=0;
	foreach ($cas_id_array as $cid) {
		// Postoji li kviz za ovaj čas?
		$uspjeh_na_kvizu = "";
		if (array_key_exists($cid, $cas_kviz)) {
			$q317 = db_query("select dovrsen, bodova from kviz_student where student=$stud_id and kviz=".$cas_kviz[$cid]);
			if (db_num_rows($q317)>0) {
				if (db_result($q317,0,0)==1 && db_result($q317,0,1)>=$cas_kviz_prolaz[$cid])
					$uspjeh_na_kvizu='<img src="static/images/16x16/ok.png" width="8" height="8">';
				else
					$uspjeh_na_kvizu='<img src="static/images/16x16/not_ok.png" width="8" height="8">';
			}
		}

		$q320 = db_query("select prisutan from prisustvo where student=$stud_id and cas=$cid");
		if (db_num_rows($q320)>0) {
			if (db_result($q320,0,0) == 1) { 
				$prisustvo_ispis .= "<td bgcolor=\"#CCFFCC\" align=\"center\" id=\"dane-".$stud_id."-".$cid."\" onclick=\"javascript:prisustvo(event,".$stud_id.",".$cid.")\"><span id=\"danetekst-".$stud_id."-".$cid."\">DA</span> $uspjeh_na_kvizu</td>";
			} else { 
				$prisustvo_ispis .= "<td bgcolor=\"#FFCCCC\" align=\"center\" id=\"dane-".$stud_id."-".$cid."\" onclick=\"javascript:prisustvo(event,".$stud_id.",".$cid.")\"><span id=\"danetekst-".$stud_id."-".$cid."\">NE</span> $uspjeh_na_kvizu</td>";
				$odsustvo++;
			}
			//$ocj = db_result($q14,0,1);
		} else {
			$prisustvo_ispis .= "<td bgcolor=\"#FFFFCC\" align=\"center\" id=\"dane-".$stud_id."-".$cid."\" onclick=\"javascript:prisustvo(event,".$stud_id.",".$cid.")\"><div id=\"danetekst-".$stud_id."-".$cid."\"> / </div> $uspjeh_na_kvizu</td>";
		}
	}

/*	if ($odsustvo<=$prisustvo_maxizostanaka[$pid]) {
		$prisustvo_ispis .= "<td>".$prisustvo_maxbodova[$pid]."</td>";
		$bodova+=10;
	} else {
		$prisustvo_ispis .= "<td>".$prisustvo_minbodova[$pid]."</td>";
	}*/
	$q325 = db_query("select kb.bodovi from komponentebodovi as kb, ponudakursa as pk where kb.student=$stud_id and kb.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag and kb.komponenta=$pid");
	if (db_num_rows($q325)==0) 
		$pbodovi=0;
	else
		$pbodovi=db_result($q325,0,0);
	$prisustvo_ispis .= "<td>$pbodovi</td>\n";
	$bodova += $pbodovi;

	} // foreach ($prisustvo... as $pid)


	// ZADACE - ISPIS

	if (!empty($zad_id_array)) foreach ($zad_id_array as $zid) {
		$zadace_ispis .= "<td>\n";
		// FIXME: subqueries
		//$q15a = db_query ("select redni_broj from zadatak where zadaca=$zid and student=$stud_id order by redni_broj group by redni_broj");

		for ($i=1; $i<=$zad_brz_array[$zid]; $i++) {
			$status = $zadace_statusi[$zid][$i][$stud_id];
			if ($status == 0) { // Zadatak nije poslan
				if ($kreiranje>0) {
					$zadace_ispis .= "<a href=\"#\" onclick=\"javascript:openzadaca(event, '".$stud_id."', '".$zid."', '".$i."'); return false;\"><img src=\"static/images/16x16/create_new.png\" width=\"16\" height=\"16\" border=\"0\" align=\"center\" title=\"".$stud_id.",".$zid.",".$i."\" alt=\"".$stud_id.",".$zid.",".$i."\"></a>&nbsp;";
					//if ($i<$zad_brz_array[$zid]) $zadace_ispis .= "<br/>";
				}
			} else {
				$status--; // Bio uvećan za 1 
				$icon = $stat_icon[$status];
				$title = $stat_tekst[$status];
				$zb = $zadace_bodovi[$zid][$i][$stud_id];
				$zadace_ispis .= "<a href=\"#\" onclick=\"javascript:openzadaca(event, '".$stud_id."', '".$zid."', '".$i."'); return false;\"><img src=\"static/images/16x16/".$icon.".png\" width=\"16\" height=\"16\" border=\"0\" align=\"center\" title=\"".$stud_id.",".$zid.",".$i."\" alt=\"".$stud_id.",".$zid.",".$i."\">&nbsp;".$zb."</a>";
//				if ($i<$zad_brz_array[$zid]) $zadace_ispis .= "<br/>";
				$bodova += $zb;
			}
		}
		$zadace_ispis .= "&nbsp;</td>\n";
	}


	// FIKSNE KOMPONENTE - ISPIS

	$fiksne_ispis="";
	foreach ($fiksna_id_array as $fiksna) {
		$q328 = db_query("select kb.bodovi from komponentebodovi as kb, ponudakursa as pk where kb.student=$stud_id and kb.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag and kb.komponenta=$fiksna");
		if (db_num_rows($q328)>0) {
			$fbodova = db_result($q328,0,0);
			$fiksne_ispis .= "<td id=\"fiksna-$stud_id-$predmet-$fiksna-$ag\" ondblclick=\"coolboxopen(this)\">$fbodova</td>\n";
			$bodova += $fbodova;
		} else {
			$fiksne_ispis .= "<td id=\"fiksna-$stud_id-$predmet-$fiksna-$ag\" ondblclick=\"coolboxopen(this)\">/</td>\n";
		}
	}


	// ISPITI - ISPIS

	$ispiti_ispis="";
	$komponente=$kmax=array();
	foreach ($ispit_id_array as $ispit) {
		$k = $ispit_komponenta[$ispit];

		$q330 = db_query("select ocjena from ispitocjene where ispit=$ispit and student=$stud_id");
		if (db_num_rows($q330)>0) {
			$ocjena = db_result($q330,0,0);
			$ispiti_ispis .= "<td id=\"ispit-$stud_id-$ispit\" ondblclick=\"coolboxopen(this)\">$ocjena</td>\n";
			if (!in_array($k,$komponente) || $ocjena>$kmax[$k])
				$kmax[$k]=$ocjena;
		} else {
			$ispiti_ispis .= "<td id=\"ispit-$stud_id-$ispit\" ondblclick=\"coolboxopen(this)\">/</td>\n";
			if (!array_key_exists($k, $kmax)) $kmax[$k]=0;
		}
		if (!in_array($k,$komponente)) $komponente[]=$k;
	}

	// Prvo trazimo integralne ispite
	foreach ($komponente as $k) {
		if ($komponenta_tip[$k] == 2) {
			// Koje parcijalne ispite obuhvata integralni
			$dijelovi = explode("+", $komponenta_opcija[$k]);

			// Racunamo zbir
			$zbir=0;
			$pao=0;
			foreach ($dijelovi as $dio) {
				$zbir += $kmax[$dio];
				if ($kmax[$dio]<$komponenta_prolaz[$dio]) $pao=1;
			}

			// Eliminisemo parcijalne obuhvacene integralnim
			if ($kmax[$k]>$zbir || $pao==1 && $kmax[$k]>=$komponenta_prolaz[$k]) {
				$bodova += $kmax[$k];
				foreach ($dijelovi as $dio) $kmax[$dio]=0;
			}
		}
	}

	// Sabiremo preostale parcijalne ispite na sumu bodova
	foreach ($komponente as $k) {
		if ($komponenta_tip[$k] != 2) {
			$bodova += $kmax[$k];
		}
	}


	// KONACNA OCJENA - ISPIS

	$q350 = db_query("select ocjena from konacna_ocjena where student=$stud_id and predmet=$predmet and akademska_godina=$ag");
	if ($privilegija == "super_asistent") {
		if (db_num_rows($q350)>0) {
			$ko_ispis = "<td align=\"center\" id=\"ko-$stud_id-$predmet-$ag\">".db_result($q350,0,0)."</td>\n";
		} else {
			$ko_ispis = "<td align=\"center\" id=\"ko-$stud_id-$predmet-$ag\">/</td>\n";
		}
	} else {
		if (db_num_rows($q350)>0) {
			$ko_ispis = "<td align=\"center\" id=\"ko-$stud_id-$predmet-$ag\" ondblclick=\"coolboxopen(this)\">".db_result($q350,0,0)."</td>\n";
		} else {
			$ko_ispis = "<td align=\"center\" id=\"ko-$stud_id-$predmet-$ag\" ondblclick=\"coolboxopen(this)\">/</td>\n";
		}
	}


?>
	<?=$prisustvo_ispis?>
	<?=$zadace_ispis?>
	<?=$fiksne_ispis?>
	<?=$ispiti_ispis?>
	<td align="center"><? print $bodova;
/*	Procenat zauzima previše prostora po horizontali, a nije toliko interesantan
	if ($mogucih_bodova!=0 && $mogucih_bodova!=100) {
//		?> (<?=procenat($bodova,$mogucih_bodova)?>)<?
	} */
	?></td>
	<?=$ko_ispis?>
</tr><?

}

?>
</table>

<p><?
	if ($kreiranje>0) {
		$k=str_replace("&amp;kreiranje=1","",genuri());
?><a href="<?=$k?>">Sakrij dugmad za kreiranje zadataka</a><?
	} else {
?><a href="<?=genuri()?>&amp;kreiranje=1">Prikaži dugmad za kreiranje zadataka</a><?
	}
?> * <a href="?sta=saradnik/grupa&amp;id=<?=$labgrupa?>">Refresh</a></p>

<?
if ($privilegija=="nastavnik") { 
	?><p>Vi ste administrator ovog predmeta.</p><? 
} else if ($privilegija=="super_asistent") {
	?><p>Vi ste super-asistent ovog predmeta.</p><? 
}
?>
<p>&nbsp;</p>
<?




}

?>
