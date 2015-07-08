<?

// SARADNIK/GRUPA - administracija jedne grupe na predmetu

// v3.9.1.0 (2008/02/11) + Preimenovan bivsi admin_grupa
// v3.9.1.1 (2008/03/08) + Nova tabela auth
// v3.9.1.2 (2008/03/15) + Popravljen log nivo za brisanje casa
// v3.9.1.3 (2008/05/16) + update_komponente_prisustvo() zamijenjen sa update_komponente()
// v3.9.1.4 (2008/06/10) + Dodan ispis fiksnih komponenti + AJAH
// v3.9.1.5 (2008/08/18) + Provjera da li postoji predmet
// v3.9.1.6 (2008/08/28) + Tabela osoba umjesto auth
// v3.9.1.7 (2008/09/08) + Forma za registrovanje casa nije prosljedjivala ID predmeta
// v3.9.1.8 (2008/09/13) + Sprijeceno otvaranje coolboxa ako slanje nije uspjelo
// v3.9.1.9 (2008/09/17) + Akcija dodaj_cas ce ubaciti 10 bodova u tabelu komponentebodovi ako prije toga nije bilo sloga u toj tabeli za datog studenta, predmet i komponentu
// v3.9.1.10 (2008/10/03) + Akcija dodaj_cas prebacena na genform() radi sigurnosnih aspekata istog; onemoguceno dodavanje casa sa GET
// v3.9.1.11 (2008/11/18) + Akcija brisi_cas nije prosljedjivala predmet_id, sto je dovodilo do greske "nepostojeci predmet" (ali je cas ipak bio obrisan)
// v3.9.1.12 (2008/12/23) + Akcija brisi_cas prebacena na POST radi zastite od CSRF (bug 54); dodan refresh link
// v3.9.1.13 (2008/01/21) + Dodan predmet na Refresh link
// v4.0.0.0 (2009/02/19) + Release
// v4.0.9.1 (2009/03/24) + Prebacena polja ects i tippredmeta iz tabele ponudakursa u tabelu predmet
// v4.0.9.2 (2009/03/25) + nastavnik_predmet preusmjeren sa tabele ponudakursa na tabelu predmet
// v4.0.9.3 (2009/03/31) + Tabela ispit preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.4 (2009/03/31) + Tabela konacna_ocjena preusmjerena sa ponudakursa na tabelu predmet
// v4.0.0.1 (2009/04/01) + Kod brisanja casa, ID nekada nije bio ispravno prosljedjivan (sto je za rezultat imalo da se cas nikako ne moze obrisati)
// v4.0.9.2 (2009/04/01) + Tabela zadaca preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.3 (2009/04/14) + Zaboravio popraviti ID predmeta u pozivu AJAHa za konacnu ocjenu
// v4.0.9.4 (2009/04/22) + Preusmjeravam tabelu labgrupa sa tabele ponudakursa na tabelu predmet; saradnik/izmjena_studenta sada prima predmet umjesto ponudekursa; u slucaju grupe 0 prima se predmet i ag umjesto ponudekursa; preusmjeravam tabelu cas sa ponudekursa na predmet
// v4.0.9.5 (2009/05/06) + Ukidam "virtualnu grupu" 0, predmet i akademska godina vise nisu neophodni parametri; dodano malo logginga
// v4.0.9.6 (2009/05/15) + U Refresh linku predmet i ag više nisu potrebni
// v4.0.9.7 (2009/05/17) + Dodana ag u link na izmjenu_studenta
// v4.0.9.8 (2009/05/18) + AJAH komponente za fiksnu komponentu i konacnu ocjenu sada primaju predmet i ag
// v4.0.9.9 (2009/09/03) + Stavljam ime studenta kao link na saradnik/student, da vidim hoce li iko primijetiti
// v4.0.9.10 (2009/10/02) + Sprijecena promjena prisustva ako je slanje u toku
// v4.0.9.11 (2009/10/07) + Omogucujem ulazak u grupu "svi studenti" preko predmeta i akademske godine
// v4.0.9.12 (2009/10/14) + Ne prikazuj formu za kreiranje casa ako ne postoji nijedna klasicna komponenta za prisustvo
// v4.0.9.13 (2009/10/24) + Novi modul "sve zadace"


function saradnik_grupa() {

global $userid,$user_siteadmin;

require ("lib/manip.php");



print '<p><a href="index.php?sta=saradnik/intro">Spisak predmeta i grupa</a></p>'."\n";


// ------- ULAZNI PARAMETRI

$labgrupa = intval($_REQUEST['id']);
$kreiranje = intval($_GET['kreiranje']);


if ($labgrupa>0) {
	// Određujemo predmet i ag za labgrupu
	$q30 = myquery("select naziv, predmet, akademska_godina, virtualna from labgrupa where id=$labgrupa");
	if (mysql_num_rows($q30)<1) {
		biguglyerror("Nemate pravo ulaska u ovu grupu!");
		zamgerlog("nepostojeca labgrupa $labgrupa",3); // 3 = greska
		zamgerlog2("nepostojeca labgrupa", $labgrupa);
		return;
	}
	$naziv = mysql_result($q30,0,0);
	$predmet = mysql_result($q30,0,1);
	$ag = mysql_result($q30,0,2);
	$virtualna = mysql_result($q30,0,3);

} else {
	// Ako nije definisana grupa, probacemo preko predmeta i ag uci u virtuelnu grupu
	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);
	$q35 = myquery("select id, naziv from labgrupa where predmet=$predmet and akademska_godina=$ag and virtualna=1");
	if (mysql_num_rows($q35)<1) {
		biguglyerror("Nemate pravo ulaska u ovu grupu!");
		zamgerlog("nepostojeca virtualna labgrupa za predmet pp$predmet ag$ag",3); // 3 = greska
		zamgerlog2("nepostojeca virtualna labgrupa", $predmet, $ag);
		return;
	}
	$labgrupa = mysql_result($q35,0,0);
	$naziv = mysql_result($q35,0,1);
	$virtualna = 1;
}



// Da li korisnik ima pravo ući u grupu?
if (!$user_siteadmin) {
	$q40 = myquery("select nivo_pristupa from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
	if (mysql_num_rows($q40)<1) {
		biguglyerror("Nemate pravo ulaska u ovu grupu!");
		zamgerlog ("nastavnik nije na predmetu (labgrupa g$labgrupa)", 3);
		zamgerlog2("nije saradnik na predmetu", $predmet, $ag);
		return;
	}
	$privilegija = mysql_result($q40,0,0);

	$q50 = myquery("select o.labgrupa from ogranicenje as o, labgrupa as l where o.nastavnik=$userid and o.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag");
	if (mysql_num_rows($q50)>0) {
		$nasao=0;
		while ($r50 = mysql_fetch_row($q50)) {
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
$q52 = myquery("select k.id, k.tipkomponente from akademska_godina_predmet as agp, tippredmeta_komponenta as tpk, komponenta as k where agp.akademska_godina=$ag and agp.predmet=$predmet and agp.tippredmeta=tpk.tippredmeta and tpk.komponenta=k.id");
while ($r52 = mysql_fetch_row($q52))
	$tipovi_komponenti[$r52[0]] = $r52[1];



// ------- AKCIJE

// Dodavanje casa

if ($_POST['akcija'] == 'dodajcas' && check_csrf_token()) {
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

		$q60 = myquery("insert into cas set datum='$datum', vrijeme='$vrijeme', labgrupa=$labgrupa, nastavnik=$userid, komponenta=$komponenta, kviz=$kviz");
		$cas_id = mysql_insert_id();
	
		// Max bodova za komponentu
		$q75 = myquery("select maxbodova, opcija from komponenta where id=$komponenta");
		$maxbodova = mysql_result($q75,0,0);
		$opcija = mysql_result($q75,0,1);
	
		// dodajemo u bazu default podatke za prisustvo i ocjene
	
		$q80 = myquery("select student from student_labgrupa where labgrupa=$labgrupa");
		while ($r80 = mysql_fetch_row($q80)) {
			$stud_id = $r80[0];
			$prisustvo = intval($_POST['prisustvo']);

			// Potrebna nam je ponudakursa za update_komponente
			$q53 = myquery("select sp.predmet from student_predmet as sp, ponudakursa as pk where sp.student=$stud_id and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
			$ponudakursa = mysql_result($q53,0,0);

			$q90 = mysql_query("insert into prisustvo set student=$stud_id, cas=$cas_id, prisutan=$prisustvo");
			
			// Update radimo samo ako se registruje odsustvo ili ako je opcija=-1 (proporcionalni bodovi)
			if ($prisustvo==0 || $opcija==-1)
				update_komponente($stud_id,$ponudakursa,$komponenta);
			else {
				// Ako nema uopšte bodova za komponentu, ubacićemo broj bodova
				$q95 = myquery("select count(*) from komponentebodovi where student=$stud_id and predmet=$ponudakursa and komponenta=$komponenta");
				if (mysql_result($q95,0,0)==0) {
					$q97 = myquery("insert into komponentebodovi set student=$stud_id, predmet=$ponudakursa, komponenta=$komponenta, bodovi=$maxbodova");
				}
			}
		}
		
		// Ako je odabrano "sa kvizom" kreiramo kviz
		if ($kviz > 0) {
			$q98 = myquery("select trajanje_kviza from kviz where id=$kviz");
			$trajanje = mysql_result($q98,0,0)*2;
			$q99 = myquery("update kviz set vrijeme_pocetak=NOW(), vrijeme_kraj=NOW() + INTERVAL $trajanje SECOND, labgrupa=$labgrupa where id=$kviz");
		}
	
		zamgerlog("registrovan cas c$cas_id",2); // nivo 2: edit
		zamgerlog2("registrovan cas", $cas_id);
	}
}


// Brisanje casa

if ($_POST['akcija'] == 'brisi_cas' && check_csrf_token()) {
	$cas_id = intval($_POST['_lv_casid']);

	// Updatujemo komponentu svima koji su bili prisutni
	$q103 = myquery("select komponenta from cas where id=$cas_id");
	if (mysql_num_rows($q103)>0) {
		$komponenta = mysql_result($q103,0,0);
		
		$q105 = myquery("select sp.student, sp.predmet from prisustvo as pr, student_predmet as sp, ponudakursa as pk where pr.cas=$cas_id and pr.student=sp.student and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
		$studenti = $ponudekursa = array();
		while ($r105 = mysql_fetch_row($q105)) {
			array_push($studenti, $r105[0]);
			$ponudekursa[$r105[0]] = $r105[1];
		}

		$q100 = myquery("delete from prisustvo where cas=$cas_id");
		$q110 = myquery("delete from cas where id=$cas_id");
		
		foreach($studenti as $student)
			update_komponente($student, $ponudekursa[$student], $komponenta);
		
		zamgerlog("obrisan cas $cas_id",2);
		zamgerlog2("obrisan cas", $cas_id);
	}
}




// ------- ZAGLAVLJE STRANICE (naslov i sl.)


$q130 = myquery("select naziv from predmet where id=$predmet");
$pime = mysql_result($q130,0,0); // Ne bi se smjelo desiti da je nepostojeci predmet, posto se to odredjuje iz labgrupe

?>
<br />
<center><h1><?=$pime?> - <?=$naziv?></h1></center>
<?



// Ima li ikoga u grupi?

$q140 = myquery("select count(student) from student_labgrupa where labgrupa=$labgrupa");

if (mysql_result($q140,0,0)<1) {
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



// ------- SPISAK NEPREGLEDANIH ZADAĆA

if (in_array(4, $tipovi_komponenti)) { // 4 = zadaće
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

	$q150 = myquery(
	"SELECT zk.zadaca, zk.redni_broj, zk.student, a.ime, a.prezime, zk.status, z.naziv
	FROM zadatak as zk, osoba as a, student_labgrupa as sl, zadaca as z
	WHERE zk.student=a.id AND zk.student=sl.student 
	AND sl.labgrupa=$labgrupa AND zk.zadaca=z.id AND z.predmet=$predmet AND z.akademska_godina=$ag
	ORDER BY zk.zadaca, zk.redni_broj, a.prezime, a.ime, zk.id DESC");
	
	
	$mzadaca=0; $mzadatak=0; $mstudent=0; $print="";
	while ($r150 = mysql_fetch_row($q150)) {
		if ($r150[0]==$mzadaca && $r150[1]==$mzadatak && $r150[2]==$mstudent) continue;
		$mzadaca=$r150[0]; $mzadatak=$r150[1]; $mstudent=$r150[2];
		if ($r150[5]!=4) continue;
		$print .= '<li><a href="#" onclick="javascript:openzadaca(event, \''.$r150[2].'\',\''.$r150[0].'\',\''.$r150[1].'\')">'.$r150[3]." ".$r150[4]." - ".$r150[6].", zadatak ".$r150[1]."</a></li>";
	}
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
		$q160 = myquery("select smp.aktivan from studentski_modul_predmet as smp, studentski_modul as sm where smp.predmet=$predmet and smp.akademska_godina=$ag and smp.studentski_modul=sm.id and sm.modul='student/kviz'");
		if (mysql_num_rows($q160)>0 && mysql_result($q160,0,0)==1) {
			?>
			<br>
			Sa kvizom: <select name="kviz"><option value="0">/</option>
			<?
			
			$q170 = myquery("select id,naziv from kviz where predmet=$predmet and akademska_godina=$ag and aktivan=1");
			while ($r170 = mysql_fetch_row($q170)) {
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
	var oldState = 0;
	var oldEvent;

	// Funkcija koja se poziva klikom na polje u tabeli
	function prisustvo(e,student,cas) {
		if (zamger_ajah_sending) {
			alert("Slanje u toku. Sačekajte malo.");
			return false;
		}
		var prisutan = invert(e,student,cas);
		ajah_start("index.php?c=N&sta=common/ajah&akcija=prisustvo&student="+student+"&cas="+cas+"&prisutan="+prisutan, "undo_prisustvo("+student+","+cas+","+prisutan+")");
	}

	// Funkcija koja se poziva u slučaju greške sa prisustvom
	function undo_prisustvo(student,cas,prisutan) {
		var greska = document.getElementById("zamger_ajah-info").innerText || document.getElementById("zamger_ajah-info").textContent;
		if (!greska.match(/\S/)) greska = "Došlo je do greške. Molimo kontaktirajte administratora.";
		alert(greska);
		invert(oldEvent, student, cas);
	}

	// Switchuje DA i NE
	function invert(e,student,cas) {
		var val = document.getElementById("danetekst-"+student+"-"+cas).innerHTML;
		var evt = e || window.event;
		oldEvent = e;

		// Shift služi za pristup neutralnom stanju /
		if (evt.shiftKey) {
			if (oldState == 0) {
				if (val == "DA") oldState = 1; else oldState = 2;
				document.getElementById("dane-"+student+"-"+cas).style.background = "#FFFFCC";
				document.getElementById("danetekst-"+student+"-"+cas).innerHTML = "/";
				return 3;
			} else {
				if (oldState==1) val="NE"; else val="DA"; // Invertujemo sa NE na DA i obrnuto
				oldState=0;
			}
		}

		if (val == "DA") {
			document.getElementById("dane-"+student+"-"+cas).style.background = "#FFCCCC";
			document.getElementById("danetekst-"+student+"-"+cas).innerHTML = "NE";
			return 1;
		} else {
			document.getElementById("dane-"+student+"-"+cas).style.background="#CCFFCC";
			document.getElementById("danetekst-"+student+"-"+cas).innerHTML = "DA";
			return 2;
		}
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



// ------- TABLICA GRUPE - ZAGLAVLJE


$minw = 0; // minimalna sirina tabele
$mogucih_bodova = 0; // koliko bodova su studenti mogli osvojiti, radi procenta
$zaglavlje1 = "";
$zaglavlje2 = "";
$prisustvo_id_array = array();


// Zaglavlje prisustvo

$q195 = myquery("SELECT k.id, k.gui_naziv, k.maxbodova FROM akademska_godina_predmet as agp, tippredmeta_komponenta as tpk, komponenta as k
WHERE agp.predmet=$predmet and agp.akademska_godina=$ag and agp.tippredmeta=tpk.tippredmeta and tpk.komponenta=k.id and k.tipkomponente=3 ORDER BY k.id");
// Ako nema nijedne komponente prisustva, upit neće vratiti ništa
while ($r195 = mysql_fetch_row($q195)) {
	$casova = 0;
	$prisustvo_zaglavlje = "";
	$prisustvo_id_array[] = $r195[0];
	$cas_id_array = array();
	$cas_kviz_array = array();

	$q200 = myquery("SELECT id,datum,vrijeme,kviz FROM cas where labgrupa=$labgrupa and komponenta=$r195[0] ORDER BY datum, vrijeme");
	while ($r200 = mysql_fetch_row($q200)) {
		$cas_id = $r200[0];
		list ($cas_godina,$cas_mjesec,$cas_dan) = explode("-",$r200[1]);
		list ($cas_sat,$cas_minuta,$cas_sekunda) = explode(":",$r200[2]);
		$prisustvo_zaglavlje .= "<td align=\"center\">$cas_dan.$cas_mjesec<br/>$cas_sat:$cas_minuta";
		$prisustvo_zaglavlje .= '<br/><a href="javascript:onclick=upozorenje('."'$cas_id'".');"><img src="images/16x16/brisanje.png" border="0"></a>';
		$prisustvo_zaglavlje .= "</td>\n";
		$cas_id_array[] = $cas_id;
		$casova++;
		$minw += 40;

		// Kviz
		if ($r200[3] > 0) {
			$cas_kviz[$cas_id] = $r200[3];

			// Odredjujemo bodove za prolaz
			$q202 = myquery("select prolaz_bodova from kviz where id=$r200[3]");
			$cas_kviz_prolaz[$cas_id] = mysql_result($q202,0,0);
			
			// Ako sam nekom drugom casu ranije dao ovaj id, moram ga obrisati
			foreach ($cas_id_array as $cid) {
				if ($cid == $cas_id) continue;
				if ($cas_kviz[$cid] == $r200[3]) $cas_kviz[$cid]=0;
			}
			
		} else {
			// Ako kviz nije unesen u bazu, tražimo najbliži
			$q203 = myquery("select id, prolaz_bodova from kviz where predmet=$predmet and akademska_godina=$ag and vrijeme_pocetak>='$r200[1]' and vrijeme_pocetak<'$r200[1]' + interval 5 day order by vrijeme_pocetak desc");
			while ($r203 = mysql_fetch_row($q203)) {
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



// Zaglavlje zadaće

$zad_id_array = array();
$q205 = myquery("SELECT k.id, k.gui_naziv FROM akademska_godina_predmet as agp, tippredmeta_komponenta as tpk, komponenta as k
WHERE agp.predmet=$predmet and agp.akademska_godina=$ag and agp.tippredmeta=tpk.tippredmeta and tpk.komponenta=k.id and k.tipkomponente=4 ORDER BY k.id");
// Ako nema nijedne komponente zadaća, upit neće vratiti ništa
while ($r205 = mysql_fetch_row($q205)) {
	$brzadaca = 0;
	$zadace_zaglavlje = "";
	$komponenta = $r205[0];
	
	// Razvrstavamo zadaće po komponentama
	$q210 = myquery("select id,naziv,zadataka,bodova from zadaca where predmet=$predmet and akademska_godina=$ag and komponenta=$komponenta order by id");
	while ($r210 = mysql_fetch_row($q210)) {
		$zadace_zaglavlje .= "<td width=\"60\" align=\"center\">$r210[1]<br /><a href=\"?sta=saradnik/svezadace&grupa=$labgrupa&zadaca=$r210[0]\">Download</a></td>\n";
		$zad_id_array[] = $r210[0];
		$zad_brz_array[$r210[0]] = $r210[2];
		$mogucih_bodova += $r210[3];
		$brzadaca++;
		$minw += 60;
	}

	if ($brzadaca>0) {
		$zaglavlje1 .= "<td align=\"center\" colspan=\"$brzadaca\">$r205[1]</td>\n";
		$zaglavlje2 .= $zadace_zaglavlje;
	}
}


// Zaglavlje fiksne komponente

$fiksna_prolaz = array();
$fiksna_id_array = array();
$q215 = myquery("SELECT k.id, k.gui_naziv, k.maxbodova, k.prolaz FROM akademska_godina_predmet as agp, tippredmeta_komponenta as tpk, komponenta as k
WHERE agp.predmet=$predmet and agp.akademska_godina=$ag and agp.tippredmeta=tpk.tippredmeta and tpk.komponenta=k.id and k.tipkomponente=5 ORDER BY k.id");
// Ako nema nijedne fiksne komponente, upit neće vratiti ništa
while ($r215 = mysql_fetch_row($q215)) {
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
$q220 = myquery("select i.id, UNIX_TIMESTAMP(i.datum), k.id, k.kratki_gui_naziv, k.tipkomponente, k.maxbodova, k.prolaz, k.opcija from ispit as i, komponenta as k where i.predmet=$predmet and i.akademska_godina=$ag and i.komponenta=k.id order by i.datum,i.komponenta");
// Ako nema komponenti sa ispitima, neće biti registrovan nijedan ispit
while ($r220 = mysql_fetch_row($q220)) {
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
//$q230 = myquery("select count(*) from konacna_ocjena where predmet=$predmet_id");
//if (mysql_result($q230,0,0)>0) {
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
	<? if ($ispis_usmeni==1) { ?><td>Usmeni</td><? } ?>
</tr>
<?


// CACHE REZULTATA ZADAĆA
$zadace_statusi=array();
$zadace_bodovi=array();
$q300 = myquery("SELECT z.zadaca,z.redni_broj,z.student,z.status,z.bodova
FROM zadatak as z,student_labgrupa as sl 
WHERE z.student=sl.student and sl.labgrupa=$labgrupa
ORDER BY z.id");
while ($r300 = mysql_fetch_row($q300)) {
	// Slog sa najnovijim IDom se smatra mjerodavnim
	// Ostali su u bazi radi historije
	$zadace_bodovi[$r300[0]][$r300[1]][$r300[2]]=$r300[4];
	$zadace_statusi[$r300[0]][$r300[1]][$r300[2]]=$r300[3]+1;
	// Dodajemo 1 na status kako bismo kasnije mogli znati da li 
	// je vrijednost niza definisana ili ne.
	// undef ne radi :(
}



// Ikone i statusi za zadaće
$stat_icon = array("zad_bug", "zad_cekaj", "zad_copy", "zad_bug", "zad_preg", "zad_ok");
$stat_tekst = array("Bug u programu", "Automatsko testiranje u toku", "Zadaća prepisana", "Bug u programu", "Potrebno pregledati", "Zadaća OK");



// Glavna petlja - studenti

$q310 = myquery("select a.id,a.ime,a.prezime,a.brindexa from osoba as a,student_labgrupa as sl where a.id=sl.student and sl.labgrupa=$labgrupa");

$imeprezime = array();
$brind = array();
while ($r310 = mysql_fetch_row($q310)) {
	$stud_id = $r310[0];
	$stud_ime = $r310[1];
	$stud_prezime = $r310[2];
	$stud_brind = $r310[3];
	$imeprezime[$stud_id] = "$stud_prezime&nbsp;$stud_ime";
	$brind[$stud_id] = $stud_brind;
	
	// Dodajemo ime grupe pored imena studenta ako je grupa virtualna
	if ($grupa_virtualna == 1) {
		$q315 = myquery("select lg.naziv from labgrupa as lg, student_labgrupa as sl where sl.student=$stud_id and sl.labgrupa=lg.id and lg.virtualna=0 and lg.predmet=$predmet and lg.akademska_godina=$ag");
		if (mysql_num_rows($q315)>0) $stud_ime .= " (".mysql_result($q315,0,0).")";
	}
}
uasort($imeprezime,"bssort"); // bssort - bosanski jezik
$redni_broj=0;


foreach ($imeprezime as $stud_id => $stud_imepr) {
	$rednibroj++;
?>
<tr>
	<td id="student_<?=$stud_id?>"><?=$rednibroj?>.&nbsp;<a href="index.php?sta=saradnik/student&student=<?=$stud_id?>&predmet=<?=$predmet?>&ag=<?=$ag?>"><?=$stud_imepr?></a></td>
	<td><?=$brind[$stud_id]?></td>
	<td align="center"><a href="javascript:firefoxopen('index.php?sta=saradnik/komentar&student=<?=$stud_id?>&labgrupa=<?=$labgrupa?>','blah3','width=350,height=320,status=0,toolbar=0,resizable=1,location=0,menubar=0,scrollbars=1');"><img src="images/16x16/komentar-plavi.png" border="0" width="16" height="16" alt="Komentar na rad studenta" title="Komentar na rad studenta"></a></td>
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
		if ($cas_kviz[$cid] > 0) {
			$q317 = myquery("select dovrsen, bodova from kviz_student where student=$stud_id and kviz=".$cas_kviz[$cid]);
			if (mysql_num_rows($q317)>0) {
				if (mysql_result($q317,0,0)==1 && mysql_result($q317,0,1)>=$cas_kviz_prolaz[$cid])
					$uspjeh_na_kvizu='<img src="images/16x16/zad_ok.png" width="8" height="8">';
				else
					$uspjeh_na_kvizu='<img src="images/16x16/brisanje.png" width="8" height="8">';
			}
		}

		$q320 = myquery("select prisutan from prisustvo where student=$stud_id and cas=$cid");
		if (mysql_num_rows($q320)>0) {
			if (mysql_result($q320,0,0) == 1) { 
				$prisustvo_ispis .= "<td bgcolor=\"#CCFFCC\" align=\"center\" id=\"dane-".$stud_id."-".$cid."\" onclick=\"javascript:prisustvo(event,".$stud_id.",".$cid.")\"><span id=\"danetekst-".$stud_id."-".$cid."\">DA</span> $uspjeh_na_kvizu</td>";
			} else { 
				$prisustvo_ispis .= "<td bgcolor=\"#FFCCCC\" align=\"center\" id=\"dane-".$stud_id."-".$cid."\" onclick=\"javascript:prisustvo(event,".$stud_id.",".$cid.")\"><span id=\"danetekst-".$stud_id."-".$cid."\">NE</span> $uspjeh_na_kvizu</td>";
				$odsustvo++;
			}
			//$ocj = mysql_result($q14,0,1);
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
	$q325 = myquery("select kb.bodovi from komponentebodovi as kb, ponudakursa as pk where kb.student=$stud_id and kb.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag and kb.komponenta=$pid");
	if (mysql_num_rows($q325)==0) 
		$pbodovi=0;
	else
		$pbodovi=mysql_result($q325,0,0);
	$prisustvo_ispis .= "<td>$pbodovi</td>\n";
	$bodova += $pbodovi;

	} // foreach ($prisustvo... as $pid)


	// ZADACE - ISPIS

	foreach ($zad_id_array as $zid) {
		$zadace_ispis .= "<td>\n";
		// FIXME: subqueries
		//$q15a = myquery ("select redni_broj from zadatak where zadaca=$zid and student=$stud_id order by redni_broj group by redni_broj");

		for ($i=1; $i<=$zad_brz_array[$zid]; $i++) {
			$status = $zadace_statusi[$zid][$i][$stud_id];
			if ($status == 0) { // Zadatak nije poslan
				if ($kreiranje>0) {
					$zadace_ispis .= "<a href=\"#\" onclick=\"javascript:openzadaca(event, '".$stud_id."', '".$zid."', '".$i."'); return false;\"><img src=\"images/16x16/zad_novi.png\" width=\"16\" height=\"16\" border=\"0\" align=\"center\" title=\"".$stud_id.",".$zid.",".$i."\" alt=\"".$stud_id.",".$zid.",".$i."\"></a>&nbsp;";
					//if ($i<$zad_brz_array[$zid]) $zadace_ispis .= "<br/>";
				}
			} else {
				$status--; // Bio uvećan za 1 
				$icon = $stat_icon[$status];
				$title = $stat_tekst[$status];
				$zb = $zadace_bodovi[$zid][$i][$stud_id];
				$zadace_ispis .= "<a href=\"#\" onclick=\"javascript:openzadaca(event, '".$stud_id."', '".$zid."', '".$i."'); return false;\"><img src=\"images/16x16/".$icon.".png\" width=\"16\" height=\"16\" border=\"0\" align=\"center\" title=\"".$stud_id.",".$zid.",".$i."\" alt=\"".$stud_id.",".$zid.",".$i."\">&nbsp;".$zb."</a>";
//				if ($i<$zad_brz_array[$zid]) $zadace_ispis .= "<br/>";
				$bodova += $zb;
			}
		}
		$zadace_ispis .= "&nbsp;</td>\n";
	}


	// FIKSNE KOMPONENTE - ISPIS

	$fiksne_ispis="";
	foreach ($fiksna_id_array as $fiksna) {
		$q328 = myquery("select kb.bodovi from komponentebodovi as kb, ponudakursa as pk where kb.student=$stud_id and kb.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag and kb.komponenta=$fiksna");
		if (mysql_num_rows($q328)>0) {
			$fbodova = mysql_result($q328,0,0);
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

		$q330 = myquery("select ocjena from ispitocjene where ispit=$ispit and student=$stud_id");
		if (mysql_num_rows($q330)>0) {
			$ocjena = mysql_result($q330,0,0);
			$ispiti_ispis .= "<td id=\"ispit-$stud_id-$ispit\" ondblclick=\"coolboxopen(this)\">$ocjena</td>\n";
			if (!in_array($k,$komponente) || $ocjena>$kmax[$k])
				$kmax[$k]=$ocjena;
		} else {
			$ispiti_ispis .= "<td id=\"ispit-$stud_id-$ispit\" ondblclick=\"coolboxopen(this)\">/</td>\n";
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

	$q350 = myquery("select ocjena from konacna_ocjena where student=$stud_id and predmet=$predmet and akademska_godina=$ag");
	if ($privilegija == "super_asistent") {
		if (mysql_num_rows($q350)>0) {
			$ko_ispis = "<td align=\"center\" id=\"ko-$stud_id-$predmet-$ag\">".mysql_result($q350,0,0)."</td>\n";
		} else {
			$ko_ispis = "<td align=\"center\" id=\"ko-$stud_id-$predmet-$ag\">/</td>\n";
		}
	} else {
		if (mysql_num_rows($q350)>0) {
			$ko_ispis = "<td align=\"center\" id=\"ko-$stud_id-$predmet-$ag\" ondblclick=\"coolboxopen(this)\">".mysql_result($q350,0,0)."</td>\n";
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
