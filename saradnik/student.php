<?

// SARADNIK/STUDENT - prikaz svih detalja o studentu na predmetu, sa mogucnoscu editovanja



// TODO: dodati:
// - fiksne komponente, sa AJAHom



function saradnik_student() {

global $userid, $user_siteadmin, $conf_ldap_domain;


require("lib/student_predmet.php"); // upis_studenta*, ispis_studenta*, update_komponente


print '<p><a href="index.php?sta=saradnik/intro">Spisak predmeta i grupa</a></p>'."\n";

// Ulazni parametri
$student = int_param('student');
$predmet = int_param('predmet');
$ag = int_param('ag');



// Provjera ulaznih podataka i podaci za naslov

// Student
$q40 = db_query("select ime, prezime, brindexa, slika from osoba where id=$student");
if (db_num_rows($q40)<1) {
	biguglyerror("Nepoznat student");
	zamgerlog("nepoznat student $student", 3);
	zamgerlog2("nepoznat student", $student);
	return;
}
$ime = db_result($q40,0,0);
$prezime = db_result($q40,0,1);
$brindexa = db_result($q40,0,2);
$slika = db_result($q40,0,3);

$mailprint = "";
$q45 = db_query("SELECT adresa FROM email WHERE osoba=$student ORDER BY sistemska DESC, id");
while ($r45 = db_fetch_row($q45)) {
	if ($mailprint) $mailprint .= ", ";
	$mailprint .= "<a href=\"mailto:$r45[0]\">$r45[0]</a>";
}

// Predmet
$q5 = db_query("select naziv from predmet where id=$predmet");
if (db_num_rows($q5)<1) {
	biguglyerror("Nepoznat predmet");
	zamgerlog("nepoznat predmet $predmet", 3);
	return;
}
$nazivpredmeta = db_result($q5,0,0);

$q15 = db_query("SELECT tippredmeta FROM akademska_godina_predmet WHERE akademska_godina=$ag AND predmet=$predmet");
$tippredmeta = db_result($q15,0,0);
if ($tippredmeta == 1000) {
	$q4 = db_query("SELECT id FROM zavrsni WHERE student=$student AND predmet=$predmet AND akademska_godina=$ag");
	if (db_num_rows($q4)>0) {
		$zavrsni = db_result($q4,0,0);
		?>
		<script language="JavaScript">
		location.href='?sta=nastavnik/zavrsni&akcija=zavrsni_stranica&zavrsni=<?=$zavrsni?>&predmet=<?=$predmet?>&ag=<?=$ag?>';
		</script>
		<?
		return;
	}
}


// Akademska godina
$q6 = db_query("select naziv from akademska_godina where id=$ag");
if (db_num_rows($q6)<1) {
	biguglyerror("Nepoznata akademska godina");
	zamgerlog("nepoznata ag $ag", 3);
	zamgerlog2("nepoznata ag", $ag);
	return;
}
$nazivag = db_result($q6,0,0);


// Da li student sluša predmet
$q7 = db_query("select pk.id, pk.semestar from student_predmet as sp, ponudakursa as pk, studij as s where sp.student=$student and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag and pk.studij=s.id");
if (db_num_rows($q7)<1) {
	biguglyerror("Student nije upisan na ovaj predmet");
	zamgerlog("student u$student ne slusa predmet pp$predmet ag$ag", 3);
	zamgerlog2("id studenta i predmeta ne odgovaraju", $student, $predmet, $ag);
	return;
}
$ponudakursa = db_result($q7,0,0);
$semestar = db_result($q7,0,1);


// Koji studij student sluša, koji put
$q8 = db_query("select s.naziv, ss.semestar, ns.naziv, ss.ponovac from student_studij as ss, studij as s, nacin_studiranja as ns where ss.student=$student and ss.akademska_godina=$ag and ss.semestar mod 2 = ".($semestar%2)." and ss.studij=s.id and ss.nacin_studiranja=ns.id");
if (db_num_rows($q8)<1) {
	$q8 = db_query("select s.naziv, ss.semestar, ns.naziv, ss.ponovac from student_studij as ss, studij as s, nacin_studiranja as ns where ss.student=$student and ss.akademska_godina=$ag and ss.semestar mod 2 = 1 and ss.studij=s.id and ss.nacin_studiranja=ns.id");
}
if (db_num_rows($q8)<1) {
	$nazivstudija = "Nije upisan na studij!";
	$kolpren=$ponovac=$nacin_studiranja="";
} else {
	$nazivstudija = db_result($q8,0,0);
	if (db_result($q8,0,1)<$semestar)
		$kolpren = ", kolizija";
	else if (db_result($q8,0,1)>$semestar)
		$kolpren = ", prenio predmet";
	else
		$kolpren = "";
	$semestar = db_result($q8,0,1);
	$nacin_studiranja = db_result($q8,0,2);
	if (db_result($q8,0,3)==1) $ponovac=", ponovac"; else $ponovac = "";
}

$q9 = db_query("select ag.id, ag.naziv from student_predmet as sp, ponudakursa as pk, akademska_godina as ag where sp.student=$student and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina<$ag and pk.akademska_godina=ag.id order by ag.id");
if (db_num_rows($q9)>0) {
	$kojiput = "(".(db_num_rows($q9)+1).". put sluša predmet)";
	$dosjei = "&nbsp;&nbsp;&nbsp;&nbsp;Pogledajte dosje za: ";
	$zarez=0;
	while ($r9 = db_fetch_row($q9)) {
		if ($zarez==0) $zarez=1; else $dosjei.= ", ";
		$dosjei .= "<a href=\"?sta=saradnik/student&student=$student&predmet=$predmet&ag=$r9[0]\">$r9[1]</a>";
	}
	$dosjei .= "<br />\n";
} else {
	$kojiput="";
	$dosjei="";
}


// U kojoj je grupi student
$q20 = db_query("select l.id, l.naziv from student_labgrupa as sl, labgrupa as l where sl.student=$student and sl.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag order by l.virtualna");
if (db_num_rows($q20)>0) {
	$labgrupa = db_result($q20,0,0);
	$lgnaziv = db_result($q20,0,1);
} else {
	$labgrupa=0; // Nema labgrupa ili nije ni u jednoj
}



// Provjera prava pristupa

$privilegija = "";
if (!$user_siteadmin) {
	$q10 = db_query("select nivo_pristupa from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
	if (db_num_rows($q10)<1) {
		biguglyerror("Nemate pravo pristupa ovom studentu");
		zamgerlog ("nastavnik nije na predmetu (pp$predmet ag$ag)", 3);
		zamgerlog2("nije saradnik na predmetu", $predmet, $ag);
		return;
	}
	$privilegija = db_result($q10,0,0);

	// Provjera ogranicenja
	$q30 = db_query("select o.labgrupa from ogranicenje as o, labgrupa as l, student_labgrupa as sl where o.nastavnik=$userid and o.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag");
	if (db_num_rows($q30)>0) {
		$nasao=0;
		while ($r30 = db_fetch_row($q30)) {
			if ($r30[0] == $labgrupa) { $nasao=1; break; }
		}
		if ($nasao == 0) {
			biguglyerror("Student je u grupi za koju vam je ograničen pristup");
			zamgerlog("ogranicenje na labgrupu g$labgrupa", 3);
			zamgerlog2("ima ogranicenje na labgrupu", intval($labgrupa));
			return;
		}
	}
}



// ----  AKCIJE

// Akcija: ispis studenta sa predmeta
if (param('akcija') == "ispis" && $user_siteadmin) {
	ispis_studenta_sa_predmeta($student,$predmet, $ag);
	zamgerlog("student ispisan sa predmeta (student u$student predmet pp$predmet)",4); // nivo 4: audit
	zamgerlog2("student ispisan sa predmeta", $student, $predmet, $ag);
	nicemessage("Student ispisan sa predmeta.");
	return;
}

if (param('akcija') == "promjena_grupe" && check_csrf_token()) {
	$novagrupa = intval($_POST['grupa']);
	$staragrupa=0;

	// Da li je student u nekoj grupi i u kojoj?
	//   (Ne smijemo se osloniti na vrijednost varijable $labgrupa jer 
	//   to može biti virtualna grupa iz koje ga ne smijemo ispisati)
	$q53 = db_query("select l.id, l.naziv from student_labgrupa as sl, labgrupa as l where sl.student=$student and sl.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag and l.virtualna=0");
	if (db_num_rows($q53)>0) {
		$staragrupa = db_result($q53,0,0);
		$naziv_stare_grupe = db_result($q53,0,1);
		if ($novagrupa==$staragrupa) {
			nicemessage("Student se već nalazi u grupi $naziv_stare_grupe!");
			print '<a href="?sta=saradnik/student&student='.$student.'&predmet='.$predmet.'&ag='.$ag.'">Nazad</a>'."\n";
			return;
		}
		ispis_studenta_sa_labgrupe($student, $staragrupa);
		nicemessage("Student ispisan iz grupe $naziv_stare_grupe. Podaci o prisustvu su izgubljeni.");
	}

	if ($novagrupa>0) {
		upis_studenta_na_labgrupu($student, $novagrupa);
		$q57 = db_query("select naziv from labgrupa where id=$novagrupa");
		nicemessage("Student upisan u grupu ".db_result($q57,0,0).". Kreirani su default podaci o prisustvu.");
	}
	
	// Potrebno je updatovati komponentu za prisustvo jer su podaci sada promijenjeni
	$q4 = db_query("select k.id from tippredmeta_komponenta as tpk,komponenta as k, akademska_godina_predmet as agp where agp.predmet=$predmet and agp.tippredmeta=tpk.tippredmeta and agp.akademska_godina=$ag and tpk.komponenta=k.id and k.tipkomponente=3");
	while ($r4 = db_fetch_row($q4))
		update_komponente($student,$ponudakursa,$r4[0]);

	// Pametni logging
	if ($staragrupa>0 && $novagrupa>0) {
		zamgerlog("student u$student prebacen iz grupe g$staragrupa u g$novagrupa", 2); // 2 = edit
		zamgerlog2("promijenjena grupa studenta", $student, $novagrupa, 0, $staragrupa);
	} else if ($staragrupa>0) {
		zamgerlog("student u$student ispisan iz grupe g$staragrupa", 2);
		zamgerlog2("student ispisan sa grupe", $student, intval($staragrupa));
	} else {
		zamgerlog("student u$student upisan u grupu g$novagrupa", 2);
		zamgerlog2("student upisan u grupu", $student, $novagrupa);
	}

	// Linkovi za dalje
	print "<p>Gdje želite sada ići?:<br />\n";
	if ($staragrupa>0)
		print '- <a href="?sta=saradnik/grupa&id='.$staragrupa.'">Spisak studenata u grupi '.db_result($q53,0,1).'</a><br />'."\n";
	else
		print '- <a href="?sta=saradnik/grupa&predmet='.$predmet.'&ag='.$ag.'">Spisak svih studenata na predmetu</a><br />'."\n"; // Ovo je jedini slučaj kad $staragrupa može biti nula
	if ($novagrupa>0)
		print '- <a href="?sta=saradnik/grupa&id='.$novagrupa.'">Spisak studenata u grupi '.db_result($q57,0,0).'</a><br />'."\n";
	print '- <a href="?sta=saradnik/student&student='.$student.'&predmet='.$predmet.'&ag='.$ag.'">Nazad na detalje studenta '.$ime.' '.$prezime.'</a>'."\n";
	return;
}

if (param('akcija') == "ponisti_kviz") {
	$kviz = intval($_REQUEST['kviz']);
	$q2000 = db_query("DELETE FROM kviz_student WHERE student=$student AND kviz=$kviz");
	zamgerlog("ponisten kviz u$student $kviz", 2);
	zamgerlog2("ponisten kviz", $student, $kviz);

	nicemessage("Poništen kviz");

	?>
	<script language="JavaScript">
	setTimeout(function() { 
		location.href='?sta=saradnik/student&student=<?=$student?>&predmet=<?=$predmet?>&ag=<?=$ag?>'; 
	}, 500);
	</script>
	<?
	return;
}




// --- ISPIS 

if ($slika != "") { print "<img src=\"?sta=common/slika&osoba=$student\" align=\"left\" style=\"margin: 10px\">\n"; }


// Određujemo username radi slanja poruke
$poruka_link = "";
$q59 = db_query("select login from auth where id=$student");
if (db_num_rows($q59)>0)
	$poruka_link = "<br><a href=\"?sta=common/inbox&akcija=compose&primalac=" . db_result($q59,0,0) . "\">Pošaljite Zamger poruku</a>";


// Naslov
?>
<h1><?=$ime?> <?=$prezime?> (<?=$brindexa?>)</h1>
<p>Upisan na (<?=$nazivag?>): <b><?=$nazivstudija?>, <?=$semestar?>. semestar <?=$ponovac?> <?=$kolpren?> <?=$kojiput?></b>
<br />
<?=$dosjei?>
<b>Email: <?=$mailprint?><?=$poruka_link?></b></p>
<h3>Predmet: <?=$nazivpredmeta?> <br />
<?
if ($labgrupa>0) print "Grupa: <a href=\"?sta=saradnik/grupa&id=$labgrupa\">$lgnaziv</a>";
else print "(nije ni u jednoj grupi)";
?>
</h3>
<?



// Promjena grupe

$q60=db_query("select id,naziv from labgrupa where predmet=$predmet and akademska_godina=$ag and virtualna=0 order by naziv");
if (db_num_rows($q60)>0) {
	?>
	<?=genform("POST");?>
	<input type="hidden" name="akcija" value="promjena_grupe">
	<p>Promijenite grupu: 
	<select name="grupa" class="default"><option value="0"<?=$nijedna?>>-- Nije ni u jednoj grupi --</option>
	<?
	while ($r60 = db_fetch_row($q60)) {
		if ($r60[0]==$labgrupa) $value="SELECTED"; else $value="";
		?>
		<option value="<?=$r60[0]?>" <?=$value?>><?=$r60[1]?></option>
		<?
	}
	?>
	</select>
	<input type="submit" value=" Promijeni grupu " class="default">
	</form>
	<?
}



// PROGRESS BAR
// Kod kopiran iz student/predmet - trebalo bi izdvojiti u lib

// Sumiramo bodove po komponentama i računamo koliko je bilo moguće ostvariti
$ukupno_bodova = $ukupno_mogucih = 0;

$q30 = db_query("select k.id, k.tipkomponente, k.opcija, kb.bodovi, k.maxbodova from komponentebodovi as kb, komponenta as k where kb.student=$student and kb.predmet=$ponudakursa and kb.komponenta=k.id");
while(db_fetch5($q30, $id_komponente, $tip_komponente, $parametar_komponente, $komponenta_bodova, $komponenta_mogucih)) {
	$ukupno_bodova += $komponenta_bodova;
	
	// Za neke komponente imamo poseban kod koliko je bilo moguće ostvariti
	if ($tip_komponente == 4) { // Tip komponente: zadaće
		$do_sada_zadace = db_get("select sum(bodova) from zadaca where predmet=$predmet and akademska_godina=$ag and komponenta=$id_komponente");
		$do_sada_zadace = round($do_sada_zadace, 2);
		
		// Zbir bodova za zadaće ne može preći ono koliko nosi komponenta
		if ($do_sada_zadace > $komponenta_mogucih)
			$ukupno_mogucih += $komponenta_mogucih;
		else
			$ukupno_mogucih += $do_sada_zadace;
	
	} else if ($tip_komponente == 3 && $parametar_komponente == -3) { // Prisustvo sa linearnim porastom
		$casova = db_get("select count(*) from cas as c, labgrupa as l, prisustvo as p, ponudakursa as pk where c.labgrupa=l.id and l.predmet=pk.predmet and l.akademska_godina=pk.akademska_godina and pk.id=$ponudakursa and c.komponenta=$id_komponente and c.id=p.cas and p.student=$student");
		$ukupno_mogucih += $casova * $komponenta_mogucih / 13;
		
	} else
		$ukupno_mogucih += $komponenta_mogucih;
}

// Procenat nikada ne smije biti veći od 100%
if ($ukupno_mogucih==0) 
	$procenat = 0;
else if ($ukupno_bodova > $ukupno_mogucih) 
	$procenat = 100;
else 
	$procenat = intval(($ukupno_bodova/$ukupno_mogucih)*100);

// boja označava napredak studenta
if ($procenat>=75) 
	$boja = "#00FF00";
else if ($procenat>=50)
	$boja = "#FFFF00";
else
	$boja = "#FF0000";

// Crtamo tabelu koristeći dvije preskalirane slike
$ukupna_sirina = 200;

$tabela1 = $procenat * 2;
$tabela2 = $ukupna_sirina - $tabela1;

// Tekst "X bodova" ćemo upisati u onu stranu tabele koja je manja
if ($tabela1 <= $tabela2) {
	$ispis1 = "<img src=\"static/images/fnord.gif\" width=\"$tabela1\" height=\"10\">";
	$ispis2 = "<img src=\"static/images/fnord.gif\" width=\"$tabela2\" height=\"1\"><br> $ukupno_bodova bodova";
} else {
	$ispis1="<img src=\"static/images/fnord.gif\" width=\"$tabela1\" height=\"1\"><br> $ukupno_bodova bodova";
	$ispis2="<img src=\"static/images/fnord.gif\" width=\"$tabela2\" height=\"10\">";
}

?>


<!-- progress bar -->

<table border="0"><tr><td align="left">
<p>
<table style="border:1px;border-style:solid" width="206" cellpadding="0" cellspacing="2"><tr>
<td width="<?=$tabela1?>" bgcolor="<?=$boja?>"><?=$ispis1?></td>
<td width="<?=$tabela2?>" bgcolor="#FFFFFF"><?=$ispis2?></td></tr></table>

<table width="208" border="0" cellspacing="0" cellpadding="0"><tr>
<td width="68">0</td>
<td align="center" width="68">50</td>
<td align="right" width="69">100</td></tr></table>
što je <?=$procenat?>% od trenutno mogućih <?=round($ukupno_mogucih,2) ?> bodova.</p>
</td></tr></table>


<!-- end progress bar -->
<?


// Nekoliko korisnih operacija za site admina

if ($user_siteadmin) {
	?>
	<p><a href="index.php?sta=saradnik/student&amp;student=<?=$student?>&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>&amp;akcija=ispis">Ispiši studenta sa predmeta</a> * <a href="index.php?sta=studentska/osobe&amp;akcija=edit&amp;osoba=<?=$student?>">Detaljnije o studentu</a> * <a href="index.php?su=<?=$student?>">Prijavi se kao student</a></p>
	<?
}




// PRISUSTVO:

print ajah_box();

?>
<script language="JavaScript">
// Funkcija koja se poziva klikom na polje u tabeli
function prisustvo(student,cas) {
	if (zamger_ajah_sending) {
		alert("Slanje u toku. Sačekajte malo.");
		return false;
	}
	var prisutan = invert(student,cas);
	ajah_start("index.php?c=N&sta=common/ajah&akcija=prisustvo&student="+student+"&cas="+cas+"&prisutan="+prisutan, "invert("+student+","+cas+")");
	// U slucaju da ajah ne uspije, ponovo se poziva funkcija invert
}
// Switchuje DA i NE
function invert(student,cas) {
	var val = document.getElementById("danetekst-"+student+"-"+cas).innerHTML;
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
function openzadaca(student,zadaca,zadatak) {
	var url='index.php?sta=saradnik/zadaca&student='+student+'&zadaca='+zadaca+'&zadatak='+zadatak;
	window.open(url,'blah','width=600,height=600,scrollbars=yes');
}
function toggleVisibilityObj(ime){
	var me = document.getElementById(ime);
	if (me.style.display=="none"){
		me.style.display="inline";
	}
	else {
		me.style.display="none";
	}
	return false; // da ne bi radio link
}
</script>
	<?


// Ispis tablice prisustva za jednu od grupa u kojima je student

function prisustvo_ispis($idgrupe,$imegrupe,$komponenta,$student) {
	if (!preg_match("/\w/",$imegrupe)) $imegrupe = "[Bez naziva]";

	$odsustva=0;
	$q70 = db_query("select id,UNIX_TIMESTAMP(datum), vrijeme from cas where labgrupa=$idgrupe and komponenta=$komponenta");
	if (db_num_rows($q70)<1) return; // Ne ispisuj grupe u kojima nema registrovanih časova

	$datumi = $vremena = $statusi = "";
	while ($r70 = db_fetch_row($q70)) {
		$datumi .= "<td>".date("d.m",$r70[1])."</td>\n";
		list($sati,$minute,$sekunde) = explode(":", $r70[2]);
		$vremena .= "<td>$sati<sup>$minute</sup></td>\n";
		$q80 = db_query("select prisutan from prisustvo where student=$student and cas=$r70[0]");
		if (db_num_rows($q80)<1) {
			$statusi .= "<td bgcolor=\"#FFFFCC\" align=\"center\" id=\"dane-".$student."-".$r70[0]."\" onclick=\"javascript:prisustvo(".$student.",".$r70[0].")\"><div id=\"danetekst-".$student."-".$r70[0]."\"> / </div></td>\n";
		} else if (db_result($q80,0,0)==1) {
			$statusi .= "<td bgcolor=\"#CCFFCC\" align=\"center\" id=\"dane-".$student."-".$r70[0]."\" onclick=\"javascript:prisustvo(".$student.",".$r70[0].")\"><div id=\"danetekst-".$student."-".$r70[0]."\">DA</div></td>\n";
		} else {
			$statusi .= "<td bgcolor=\"#FFCCCC\" align=\"center\" id=\"dane-".$student."-".$r70[0]."\" onclick=\"javascript:prisustvo(".$student.",".$r70[0].")\"><div id=\"danetekst-".$student."-".$r70[0]."\">NE</div></td>\n";
			$odsustva++;
		}
	}

	
	?>

	<b>Prisustvo (<?=$imegrupe?>):</b><br/>
	<table cellspacing="0" cellpadding="2" border="0" id="prisustvo" class="prisustvo">
	<tr>
		<th>Datum</th>
	<?=$datumi?>
	</tr>
	<tr>
		<th>Vrijeme</th>
	<?=$vremena?>
	</tr>
	<tr>
		<th>Prisutan</th>
	<?=$statusi?>
	</tr>
	</table>
	</p>
	
	<?
	return $odsustva;
}


// Izračunavamo broj bodova za svaku komponentu prisustva
$q40 = db_query("select k.id, k.maxbodova, k.prolaz, k.opcija from komponenta as k, tippredmeta_komponenta as tpk, akademska_godina_predmet as agp
where agp.predmet=$predmet and agp.tippredmeta=tpk.tippredmeta and agp.akademska_godina=$ag and tpk.komponenta=k.id and k.tipkomponente=3"); // 3 = prisustvo

while (db_fetch4($q40, $id_komponente, $max_bodova, $min_bodova, $parametar_komponente)) {
	$odsustva = $casova = 0;
	$labgrupe = db_query_vassoc("select l.id,l.naziv from labgrupa as l, student_labgrupa as sl where l.predmet=$predmet and l.akademska_godina=$ag and l.id=sl.labgrupa and sl.student=$student");
	
	foreach($labgrupe as $id_grupe => $naziv_grupe) {
		$odsustva += prisustvo_ispis($id_grupe, $naziv_grupe, $id_komponente, $student);
		$casova += db_get("select count(*) from cas where labgrupa=$id_grupe and komponenta=$id_komponente");
	}
	
	if ($parametar_komponente == -1) {
		if ($casova == 0) 
			$bodovi = 10;
		else
			$bodovi = $min_bodova + round(($max_bodova - $min_bodova) * (($casova - $odsustva) / $casova), 2 ); 
			
	} else if ($parametar_komponente == -2) { // Paraproporcionalni sistem TP
		if ($odsustva <= 2)
			$bodovi = $max_bodova;
		else if ($odsustva <= 2 + ($max_bodova - $min_bodova)/2)
			$bodovi = $max_bodova - ($odsustva-2)*2;
		else
			$bodovi = $min_bodova;

	} else if ($parametar_komponente == -3) { // Još jedan sistem TP
		$bodovi = ($max_bodova / 13) * ($casova - $odsustva);
	
	// Pozitivan parametar komponente je najveći dozvoljeni broj izostanaka za cut-off model
	} else if ($odsustva <= $parametar_komponente) {
		$bodovi = $max_bodova;
	} else {
		$bodovi = $min_bodova;
	}
	?><p>Ukupno na prisustvo: <b><?=$bodovi?></b> bodova.</p>
	<?
}



// KVIZOVI

$q200 = db_query("SELECT id, naziv, prolaz_bodova FROM kviz WHERE predmet=$predmet AND akademska_godina=$ag");
if (db_num_rows($q200) > 0) {
	?>

	<b>Kvizovi:</b><br/>
	<table cellspacing="0" cellpadding="2" border="0" id="kvizovi">
	<thead>
	<tr>
		<th>Naziv kviza</th>
		<th>Rezultat</th>
		<th>Akcije</th>
	</tr>
	</thead>
	<?

	while ($r200 = db_fetch_row($q200)) {
		$q210 = db_query("SELECT dovrsen, bodova FROM kviz_student WHERE student=$student AND kviz=$r200[0]");
		$tekst = "";

		if (db_num_rows($q210) > 0) {
			$bodova = db_result($q210,0,1);
			if (db_result($q210,0,0) == 0) {
				$tekst = "<img src=\"static/images/16x16/wait_icon.png\" width=\"8\" height=\"8\"> Nije završio/la";
			} else if ($bodova < $r200[2]) {
				$tekst = "<img src=\"static/images/16x16/not_ok.png\" width=\"8\" height=\"8\"> $bodova bodova";
			} else {
				$tekst = "<img src=\"static/images/16x16/ok.png\" width=\"8\" height=\"8\"> $bodova bodova";
			}
		}

		?>
		<tr>
			<td><?=$r200[1]?></td>
			<td><?=$tekst?></td>
			<td><? if ($tekst !== "") { ?><a href="?sta=saradnik/student&student=<?=$student?>&predmet=<?=$predmet?>&ag=<?=$ag?>&akcija=ponisti_kviz&kviz=<?=$r200[0]?>">Poništi kviz</a><? } ?></td>
		</tr>
		<?
	}

	?>
	</table>
	</p>
	<?
}




//  ZADAĆE


// Statusne ikone:
$stat_icon = array("bug", "view", "copy", "bug", "view", "ok");
$stat_tekst = array("Bug u programu", "Pregled u toku", "Zadaća prepisana", "Bug u programu", "Pregled u toku", "Zadaća OK");




$q95 = db_query("select k.id,k.gui_naziv from komponenta as k, tippredmeta_komponenta as tpk, akademska_godina_predmet as agp
where agp.predmet=$predmet and agp.akademska_godina=$ag and agp.tippredmeta=tpk.tippredmeta and tpk.komponenta=k.id and k.tipkomponente=4"); // 4 = zadaće


while(db_fetch2($q95, $id_komponente, $naziv_komponente)) {


?>


<!-- zadace -->

<b><?=$naziv_komponente?>:</b><br/>
<table cellspacing="0" cellpadding="2" border="0" id="zadace<?=$id_komponente?>" class="zadace">
	<thead>
		<tr>
	<td>&nbsp;</td>
<?

// Zaglavlje tabele - potreban nam je max. broj zadataka u zadaci

$max_broj_zadataka = db_get("select zadataka from zadaca where predmet=$predmet and akademska_godina=$ag and komponenta=$id_komponente order by zadataka desc limit 1");
for ($i=1;$i<=$max_broj_zadataka;$i++) {
	?><td>Zadatak <?=$i?>.</td><?
}

?>
		<td><b>Ukupno bodova</b></td>
		</tr>
	</thead>
<tbody>
<?


// Tijelo tabele

// LEGENDA STATUS POLJA:
// 0 - nepoznat status
// 1 - nova zadaća
// 2 - prepisana
// 3 - ne može se kompajlirati
// 4 - prošla test, predstoji kontrola
// 5 - pregledana


/* Ovo se sve moglo kroz SQL rijesiti, ali necu iz razloga:
1. PHP je citljiviji
2. MySQL <4.1 ne podrzava subqueries */


$bodova_sve_zadace=0;

$q21 = db_query("select id,naziv,bodova,zadataka from zadaca where predmet=$predmet and akademska_godina=$ag and komponenta=$id_komponente order by id");
while ($r21 = db_fetch_row($q21)) {
	$zadaca = $r21[0];
	$mogucih += $r21[2];
	$broj_zadataka = $r21[3];
	?><tr>
	<th><?=$r21[1]?></th>
	<?
	$bodova_zadaca = 0;

	for ($zadatak=1; $zadatak<=$max_broj_zadataka; $zadatak++) {
		// Ako tekuća zadaća nema toliko zadataka, ispisujemo blank polje
		if ($zadatak > $broj_zadataka) {
			?><td>&nbsp;</td><?
			continue;
		}

		// Uzmi samo rjesenje sa zadnjim IDom
		$q22 = db_query("select status,bodova,komentar from zadatak where student=$student and zadaca=$zadaca and redni_broj=$zadatak order by id desc limit 1");
		if (db_num_rows($q22)<1) {
			?><td>&nbsp;</td><?
		} else {
			$status = db_result($q22,0,0);
			$bodova_zadatak = db_result($q22,0,1);
			$bodova_zadaca += $bodova_zadatak;

			if (strlen(db_result($q22,0,2))>2)
				$imakomentar = "<img src=\"static/images/16x16/comment_yellow.png\"  width=\"15\" height=\"14\" border=\"0\" title=\"Ima komentar\" alt=\"Ima komentar\" align=\"center\">";
			else
				$imakomentar = "";

			?><td><a href="javascript:openzadaca('<?=$student?>', '<?=$zadaca?>', '<?=$zadatak?>')"><img src="static/images/16x16/<?=$stat_icon[$status]?>.png" width="16" height="16" border="0" align="center" title="<?=$stat_tekst[$status]?>" alt="<?=$stat_tekst[$status]?>"> <?=$bodova_zadatak?> <?=$imakomentar?></a></td>
	<?
		}
	}
	?>
	<td><?=$bodova_zadaca?></td>
	</tr>
	<?
	$bodova_sve_zadace += $bodova_zadaca;
}


// Ukupno bodova za studenta
 
$bodova += $bodova_sve_zadace;

?>
	<tr><td colspan="<?=$max_broj_zadataka+1?>" align="right">UKUPNO: </td>
	<td><?=$bodova_sve_zadace?></td></tr>
</tbody>
</table>

<p>Za historiju izmjena kliknite na željeni zadatak. <a href="#" onclick="javascript:window.open('legenda-zadace.html','blah6','width=320,height=130'); return false;">Legenda simbola</a></p>
<br/>

<!-- end zadace -->

<?


} // while(db_fetch2($q95...



// Importujemo kod za coolbox
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




// FIKSNE KOMPONENTE


$q25 = db_query("select k.id, k.gui_naziv from komponenta as k, tippredmeta_komponenta as tpk, akademska_godina_predmet as agp where agp.predmet=$predmet and agp.tippredmeta=tpk.tippredmeta and agp.akademska_godina=$ag and tpk.komponenta=k.id and k.tipkomponente=5"); // 5 = fiksna komponenta
if (db_num_rows($q25)>0) {
?>

<!-- fiksne komponente -->

<table cellspacing="0" cellpadding="2" border="0" id="fiksne" class="zadace">
	<thead>
	<tr>
		<td><b>Komponenta ocjene</b></td>
		<td><b>Bodovi</b></td>
		<td><b>Dnevnik izmjena</b></td>
	</tr>
	</thead>
	<tbody>
<?
}

while ($r25 = db_fetch_row($q25)) {
	$komponenta = $r25[0];
	$q27 = db_query("select bodovi from komponentebodovi where student=$student and predmet=$ponudakursa and komponenta=$komponenta");
	if (db_num_rows($q27)<1) $ocjenaedit="/";
	else $ocjenaedit=db_result($q27,0,0);
	?>
	<tr>
		<td><?=$r25[1]?></td>
		<td id="fiksna-<?=$student?>-<?=$predmet?>-<?=$komponenta?>-<?=$ag?>" ondblclick="coolboxopen(this)"><?=$ocjenaedit?></td>
		<td><? 
		if ($privilegija=="nastavnik" || $privilegija=="super_asistent" || $user_siteadmin) { 
			?><div id="fiksnalog<?=$komponenta?>"></div><?
		} else print "/";
		?></td>
	</tr>
	<?
}
if (db_num_rows($q25)>0) {
?>
	</tbody>
</table>
<p>&nbsp;</p>
<?
}



//  ISPITI


?>

<!-- ispiti -->

<b>Ispiti:</b><br/>

<?
	

$q30 = db_query("select i.id, UNIX_TIMESTAMP(i.datum), k.gui_naziv, k.id, k.prolaz from ispit as i, komponenta as k where i.predmet=$predmet and i.akademska_godina=$ag and i.komponenta=k.id order by i.datum,i.komponenta");
if (db_num_rows($q30) == 0) {
	print "<p>Nije bilo parcijalnih ispita.</p>";
} else {
?>
<table cellspacing="0" cellpadding="2" border="0" id="ispiti" class="zadace">
	<thead>
	<tr>
		<td><b>Tip ispita</b></td>
		<td><b>Datum ispita</b></td>
		<td><b>Položio/la?</b></td>
		<td><b>Bodovi</b></td>
		<td><b>Dnevnik izmjena</b></td>
	</tr>
	</thead>
	<tbody>
<?
}

$rezultati_ispita = array();
while ($r30 = db_fetch_row($q30)) {
	$ispit = $r30[0];
	$rezultati_ispita[$ispit] = db_get("select ocjena from ispitocjene where ispit=$ispit and student=$student");
	if ($rezultati_ispita[$ispit]) {
		$ispitocjena = "<b>" . $rezultati_ispita[$ispit] . " bodova</b>";
	} else {
		$rezultati_ispita[$ispit] = "/";
		$ispitocjena = "Nije izašao/la";
	}
/*	?>
	<p><?=$r30[2]?> (<?=date("d. m. Y",$r30[1])?>): <?=$ispitocjena?>
	<?*/
	?>
	<tr>
		<td><?=$r30[2]?></td>
		<td><?=date("d. m. Y",$r30[1])?></td>
		<td><?
		if ($rezultati_ispita[$ispit]=="/") 
			print "&nbsp;";
		else if ($rezultati_ispita[$ispit] >= $r30[4]) 
			print "<img src=\"static/images/16x16/ok.png\" width=\"16\" height=\"16\">"; 
		else 
			print "<img src=\"static/images/16x16/not_ok.png\" width=\"16\" height=\"16\">"; // najljepše slike
		?></td>
		<td id="ispit-<?=$student?>-<?=$ispit?>" ondblclick="coolboxopen(this)"><?=$rezultati_ispita[$ispit]?></td>
		<td><? 
		if ($privilegija=="nastavnik" || $privilegija=="super_asistent" || $user_siteadmin) { 
			?><div id="ispitlog<?=$ispit?>"><img src="static/images/busy-light-25x25.gif" width="16" height="16"></div><?
		} else print "/";
		?></td>
	</tr>
	<?

/*	if ($predmet_admin || $user_siteadmin) {
		?> <span id="ispitlink<?=$ispit?>">(<a href="#" onclick="return toggleVisibilityObj('ispitlog<?=$ispit?>');">dnevnik izmjena</a>)</span> (<a href="#" onclick="return toggleVisibilityObj('ispitunos<?=$ispit?>');">upišite rezultat</a>)</p>
		<p><div id="ispitlog<?=$ispit?>" style="display:none"></div></p>
		<p><div id="ispitunos<?=$ispit?>" style="display:none">
		<table border="0" cellspacing="0" cellpadding="2" height="16"><tr height="16">
			<td width="37">&nbsp;</td>
			<td align="left">Unesite bodove ili znak "kosa crta" (/):</td>
			<td width="5">&nbsp;<br/>&nbsp;</td>
			<td id="ispit-<?=$student?>-<?=$ispit?>" ondblclick="coolboxopen(this)" width="32" height="32" style="font-size:11px; border:1px solid black"><?=$ocjenaedit?></td>
		</tr></table>
		</div></p>
		<?
	} else {
		print "</p>\n";
	}*/
}

if (db_num_rows($q30) > 0) {
?>
</tbody></table>
<p>Dvokliknite na bodove da promijenite podatak ili upišete novi. Za brisanje rezultata, pobrišite postojeći podatak i pritisnite Enter.</p>
<?
}




// KONAČNA OCJENA

$vrijeme_konacne_ocjene=0;
$q50 = db_query("select ocjena, UNIX_TIMESTAMP(datum), UNIX_TIMESTAMP(datum_u_indeksu) from konacna_ocjena where student=$student and predmet=$predmet and akademska_godina=$ag");
if (db_num_rows($q50)>0) {
	$konacnaocjena = db_result($q50,0,0);
	$vrijeme_konacne_ocjene = db_result($q50,0,1);
	$datum_u_indeksu = date("d. m. Y", db_result($q50,0,2));
} else {
	$konacnaocjena = "/";
	$datum_u_indeksu = "";
}

?>
<p>&nbsp;</p>
<table cellspacing="0" cellpadding="2" border="0" id="konacna_ocjena" class="zadace">
<tr>
	<td>&nbsp;</td>
	<td>Ocjena:</td>
	<td>Datum u indeksu:</td>
	<? if ($privilegija=="nastavnik" || $user_siteadmin) { ?>
	<td>Dnevnik izmjena:</td>
	<? } ?>
</tr>
<tr>
	<td><b>Konačna ocjena:</b></td>
<?

if ($privilegija=="nastavnik" || $user_siteadmin) {
	?>
	<td id="ko-<?=$student?>-<?=$predmet?>-<?=$ag?>" ondblclick="coolboxopen(this)"><?=$konacnaocjena?></td>
	<td id="kodatum-<?=$student?>-<?=$predmet?>-<?=$ag?>" ondblclick="coolboxopen(this)"><?=$datum_u_indeksu?></td>
	<td><div id="kolog"><img src="static/images/busy-light-25x25.gif" width="16" height="16"></div></td>
	<?
} else {
	?>
	<td><?=$konacnaocjena?></td>
	<td><?=$datum_u_indeksu?></td>
	<?
}

?>
</tr></table>
<?



// **************************************
// POPUNA LOGOVA
// **************************************

// Ne radimo ništa ako korisnik nema privilegije

if ($privilegija != "nastavnik" && $privilegija != "super_asistent" && !$user_siteadmin) return;

?>


<SCRIPT language="JavaScript">

setTimeout(function() { ucitajLogove(<?=$student?>, <?=$predmet?>, <?=$ag?>); }, 100);
var konacnaocjena = '<?=$konacnaocjena?>';
var rezultati_ispita = {};
<?
foreach($rezultati_ispita as $ispit => $bodovi)
	print "rezultati_ispita['".$ispit."'] = '$bodovi';\n";
	
?>

function ucitajLogove(student, predmet, ag) {
	var xmlhttp = new XMLHttpRequest();
	var url = "index.php?sta=ws/log&tip_loga=student&student=" + student + "&predmet=" + predmet + "&ag=" + ag;
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
			result = JSON.parse(xmlhttp.responseText);
			if (result.success == "true") {
				parsirajLogove(result.data);
			} else {
				console.log("Web servis za logove vratio success=false");
				console.log(result);
			}
			return false;
		}
		if (xmlhttp.readyState == 4 && xmlhttp.status == 500) {
			console.log("Serverska greška kod pozivanja web servisa za logove.");
			console.log("readyState "+xmlhttp.readyState+" status "+xmlhttp.status);
		}
	}
	xmlhttp.open("GET", url, true);
	xmlhttp.send();
	
}

function parsirajLogove(log) {
	if (document.getElementById('kolog')) document.getElementById('kolog').innerHTML = "";
	for (var ispit in rezultati_ispita) {
		if (rezultati_ispita.hasOwnProperty(ispit)) {
			document.getElementById('ispitlog' + ispit).innerHTML = "";
		}
	}
	for (i=0; i<log.length; i++) {
		var stavka = log[i];
		
		if (stavka.opis_dogadjaja == "dodana ocjena" && document.getElementById('kolog')) {
			if (stavka.ocjena != konacnaocjena) stavka.ocjena += " ?";
			konacnaocjena = "/";
			
			document.getElementById('kolog').innerHTML = '<img src="static/images/16x16/edit_red.png" width="16" height="16" align="center"> dodana ocjena <b>' + stavka.ocjena + '</b> (' + stavka.korisnik + ', ' + stavka.vrijeme + ')<br />' + document.getElementById('kolog').innerHTML;
			
		} else if (stavka.opis_dogadjaja == "obrisana ocjena" && document.getElementById('kolog')) {
			if (konacnaocjena != "/") 
				stavka.ocjena += " ?"; 
			else 
				konacnaocjena=stavka.ocjena;
			
			document.getElementById('kolog').innerHTML = '<img src="static/images/16x16/edit_red.png" width="16" height="16" align="center"> obrisana ocjena (' + stavka.korisnik + ', ' + stavka.vrijeme + ')<br />' + document.getElementById('kolog').innerHTML;
			
		} else if (stavka.opis_dogadjaja == "izmjena ocjene" && document.getElementById('kolog')) {
			if (stavka.ocjena != konacnaocjena) stavka.ocjena += " ?";
			konacnaocjena = stavka.stara_ocjena;
			
			document.getElementById('kolog').innerHTML = '<img src="static/images/16x16/edit_red.png" width="16" height="16" align="center"> promijenjena ocjena u <b>' + stavka.ocjena + '</b> (' + stavka.korisnik + ', ' + stavka.vrijeme + ')<br />' + document.getElementById('kolog').innerHTML;
			
		} else if (stavka.opis_dogadjaja == "promijenjen datum ocjene" && document.getElementById('kolog')) {
			document.getElementById('kolog').innerHTML = '<img src="static/images/16x16/edit_red.png" width="16" height="16" align="center"> promijenjena datum ocjene u <b>' + stavka.datum_ocjene + '</b> (' + stavka.korisnik + ', ' + stavka.vrijeme + ')<br />' + document.getElementById('kolog').innerHTML;
			
		} else if (stavka.opis_dogadjaja == "upisan rezultat ispita") {
			if (stavka.bodovi != rezultati_ispita[stavka.ispit]) 
				stavka.bodovi += " ?";
			rezultati_ispita[stavka.ispit] = "/";
			
			document.getElementById('ispitlog' + stavka.ispit).innerHTML = '<img src="static/images/16x16/edit_red.png" width="16" height="16" align="center"> upisan rezultat <b>' + stavka.bodovi + '</b> (' + stavka.korisnik + ', ' + stavka.vrijeme + ')<br />' + document.getElementById('ispitlog' + stavka.ispit).innerHTML;
			
		} else if (stavka.opis_dogadjaja == "izbrisan rezultat ispita") {
			if (rezultati_ispita[stavka.ispit] != "/") 
				stavka.stari_bodovi += " ?";
			else 
				rezultati_ispita[stavka.ispit] = stavka.stari_bodovi;
			
			document.getElementById('ispitlog' + stavka.ispit).innerHTML = '<img src="static/images/16x16/edit_red.png" width="16" height="16" align="center"> izbrisan rezultat (' + stavka.korisnik + ', ' + stavka.vrijeme + ')<br />' + document.getElementById('ispitlog' + stavka.ispit).innerHTML;
			
		} else if (stavka.opis_dogadjaja == "izmjenjen rezultat ispita") {
			if (stavka.bodovi != rezultati_ispita[stavka.ispit]) 
				stavka.bodovi += " ?";
			rezultati_ispita[stavka.ispit] = stavka.stari_bodovi;
			
			document.getElementById('ispitlog' + stavka.ispit).innerHTML = '<img src="static/images/16x16/edit_red.png" width="16" height="16" align="center"> promijenjen rezultat u <b>' + stavka.bodovi + '</b> (' + stavka.korisnik + ', ' + stavka.vrijeme + ')<br />' + document.getElementById('ispitlog' + stavka.ispit).innerHTML;
			
		} else if (stavka.opis_dogadjaja == "izmjena bodova za fiksnu komponentu") {
			document.getElementById('fiksnalog' + stavka.komponenta).innerHTML = '<img src="static/images/16x16/edit_red.png" width="16" height="16" align="center"> promijenjeni bodovi u <b>' + stavka.bodovi + '</b> (' + stavka.korisnik + ', ' + stavka.vrijeme + ')<br />' + document.getElementById('fiksnalog' + stavka.komponenta).innerHTML;
		}
	}
}

</SCRIPT>
<?







}


?>
