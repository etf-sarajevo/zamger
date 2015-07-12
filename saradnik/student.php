<?

// SARADNIK/STUDENT - prikaz svih detalja o studentu na predmetu, sa mogucnoscu editovanja

// v4.0.9.1 (2009/07/19) + Novi modul saradnik/student prema sugestiji doc. dr Dzenane Djonko
// v4.0.9.2 (2009/09/03) + Dodajem AJAHe za unos ispita i konačne ocjene
// v4.0.9.3 (2009/10/07) + Koristim gen_ldap_uid za email adresu (bilo gresaka); dodana promjena grupe; dodana fiksna komponenta; konacna ocjena na isti nacin kao ostalo; tacna informacija sta slusa i koji put
// v4.0.9.4 (2009/10/21) + Popravljen bug gdje je student ispisivan iz grupe "Svi studenti"; nesto pametniji logging kod promjene grupe; dodao linkove na dosjee za ranije godine


// TODO: dodati:
// - fiksne komponente, sa AJAHom



function saradnik_student() {

global $userid, $user_siteadmin, $conf_ldap_domain;


require("lib/manip.php"); // radi ispisa studenta sa predmeta


print '<p><a href="index.php?sta=saradnik/intro">Spisak predmeta i grupa</a></p>'."\n";

// Ulazni parametri
$student = intval($_REQUEST['student']);
$predmet = intval($_REQUEST['predmet']);
$ag = intval($_REQUEST['ag']);



// Provjera ulaznih podataka i podaci za naslov

// Student
$q40 = myquery("select ime, prezime, brindexa, slika from osoba where id=$student");
if (mysql_num_rows($q40)<1) {
	biguglyerror("Nepoznat student");
	zamgerlog("nepoznat student $student", 3);
	zamgerlog2("nepoznat student", $student);
	return;
}
$ime = mysql_result($q40,0,0);
$prezime = mysql_result($q40,0,1);
$brindexa = mysql_result($q40,0,2);
$slika = mysql_result($q40,0,3);

$mailprint = "";
$q45 = myquery("SELECT adresa FROM email WHERE osoba=$student ORDER BY sistemska DESC, id");
while ($r45 = mysql_fetch_row($q45)) {
	if ($mailprint) $mailprint .= ", ";
	$mailprint .= "<a href=\"mailto:$r45[0]\">$r45[0]</a>";
}

// Predmet
$q5 = myquery("select naziv from predmet where id=$predmet");
if (mysql_num_rows($q5)<1) {
	biguglyerror("Nepoznat predmet");
	zamgerlog("nepoznat predmet $predmet", 3);
	return;
}
$nazivpredmeta = mysql_result($q5,0,0);

$q15 = myquery("SELECT tippredmeta FROM akademska_godina_predmet WHERE akademska_godina=$ag AND predmet=$predmet");
$tippredmeta = mysql_result($q15,0,0);
if ($tippredmeta == 1000) {
	$q4 = myquery("SELECT id FROM zavrsni WHERE student=$student AND predmet=$predmet AND akademska_godina=$ag");
	if (mysql_num_rows($q4)>0) {
		$zavrsni = mysql_result($q4,0,0);
		?>
		<script language="JavaScript">
		location.href='?sta=nastavnik/zavrsni&akcija=zavrsni_stranica&zavrsni=<?=$zavrsni?>&predmet=<?=$predmet?>&ag=<?=$ag?>';
		</script>
		<?
		return;
	}
}


// Akademska godina
$q6 = myquery("select naziv from akademska_godina where id=$ag");
if (mysql_num_rows($q6)<1) {
	biguglyerror("Nepoznata akademska godina");
	zamgerlog("nepoznata ag $ag", 3);
	zamgerlog2("nepoznata ag", $ag);
	return;
}
$nazivag = mysql_result($q6,0,0);


// Da li student sluša predmet
$q7 = myquery("select pk.id, pk.semestar from student_predmet as sp, ponudakursa as pk, studij as s where sp.student=$student and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag and pk.studij=s.id");
if (mysql_num_rows($q7)<1) {
	biguglyerror("Student nije upisan na ovaj predmet");
	zamgerlog("student u$student ne slusa predmet pp$predmet ag$ag", 3);
	zamgerlog2("id studenta i predmeta ne odgovaraju", $student, $predmet, $ag);
	return;
}
$ponudakursa = mysql_result($q7,0,0);
$semestar = mysql_result($q7,0,1);


// Koji studij student sluša, koji put
$q8 = myquery("select s.naziv, ss.semestar, ns.naziv, ss.ponovac from student_studij as ss, studij as s, nacin_studiranja as ns where ss.student=$student and ss.akademska_godina=$ag and ss.semestar mod 2 = ".($semestar%2)." and ss.studij=s.id and ss.nacin_studiranja=ns.id");
if (mysql_num_rows($q8)<1) {
	$q8 = myquery("select s.naziv, ss.semestar, ns.naziv, ss.ponovac from student_studij as ss, studij as s, nacin_studiranja as ns where ss.student=$student and ss.akademska_godina=$ag and ss.semestar mod 2 = 1 and ss.studij=s.id and ss.nacin_studiranja=ns.id");
}
if (mysql_num_rows($q8)<1) {
	$nazivstudija = "Nije upisan na studij!";
	$kolpren=$ponovac=$nacin_studiranja="";
} else {
	$nazivstudija = mysql_result($q8,0,0);
	if (mysql_result($q8,0,1)<$semestar)
		$kolpren = ", kolizija";
	else if (mysql_result($q8,0,1)>$semestar)
		$kolpren = ", prenio predmet";
	else
		$kolpren = "";
	$semestar = mysql_result($q8,0,1);
	$nacin_studiranja = mysql_result($q8,0,2);
	if (mysql_result($q8,0,3)==1) $ponovac=", ponovac"; else $ponovac = "";
}

$q9 = myquery("select ag.id, ag.naziv from student_predmet as sp, ponudakursa as pk, akademska_godina as ag where sp.student=$student and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina<$ag and pk.akademska_godina=ag.id order by ag.id");
if (mysql_num_rows($q9)>0) {
	$kojiput = "(".(mysql_num_rows($q9)+1).". put sluša predmet)";
	$dosjei = "&nbsp;&nbsp;&nbsp;&nbsp;Pogledajte dosje za: ";
	$zarez=0;
	while ($r9 = mysql_fetch_row($q9)) {
		if ($zarez==0) $zarez=1; else $dosjei.= ", ";
		$dosjei .= "<a href=\"?sta=saradnik/student&student=$student&predmet=$predmet&ag=$r9[0]\">$r9[1]</a>";
	}
	$dosjei .= "<br />\n";
} else {
	$kojiput="";
	$dosjei="";
}


// U kojoj je grupi student
$q20 = myquery("select l.id, l.naziv from student_labgrupa as sl, labgrupa as l where sl.student=$student and sl.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag order by l.virtualna");
if (mysql_num_rows($q20)>0) {
	$labgrupa = mysql_result($q20,0,0);
	$lgnaziv = mysql_result($q20,0,1);
} else {
	$labgrupa=0; // Nema labgrupa ili nije ni u jednoj
}



// Provjera prava pristupa

if (!$user_siteadmin) {
	$q10 = myquery("select nivo_pristupa from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
	if (mysql_num_rows($q10)<1) {
		biguglyerror("Nemate pravo pristupa ovom studentu");
		zamgerlog ("nastavnik nije na predmetu (pp$predmet ag$ag)", 3);
		zamgerlog2("nije saradnik na predmetu", $predmet, $ag);
		return;
	}
	$privilegija = mysql_result($q10,0,0);

	// Provjera ogranicenja
	$q30 = myquery("select o.labgrupa from ogranicenje as o, labgrupa as l, student_labgrupa as sl where o.nastavnik=$userid and o.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag");
	if (mysql_num_rows($q30)>0) {
		$nasao=0;
		while ($r30 = mysql_fetch_row($q30)) {
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
if ($_GET['akcija'] == "ispis" && $user_siteadmin) {
	ispis_studenta_sa_predmeta($student,$predmet, $ag);
	zamgerlog("student ispisan sa predmeta (student u$student predmet pp$predmet)",4); // nivo 4: audit
	zamgerlog2("student ispisan sa predmeta", $student, $predmet, $ag);
	nicemessage("Student ispisan sa predmeta.");
	return;
}

if ($_POST['akcija'] == "promjena_grupe" && check_csrf_token()) {
	$novagrupa = intval($_POST['grupa']);
	$staragrupa=0;

	// Da li je student u nekoj grupi i u kojoj?
	//   (Ne smijemo se osloniti na vrijednost varijable $labgrupa jer 
	//   to može biti virtualna grupa iz koje ga ne smijemo ispisati)
	$q53 = myquery("select l.id, l.naziv from student_labgrupa as sl, labgrupa as l where sl.student=$student and sl.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag and l.virtualna=0");
	if (mysql_num_rows($q53)>0) {
		$staragrupa = mysql_result($q53,0,0);
		$naziv_stare_grupe = mysql_result($q53,0,1);
		if ($novagrupa==$staragrupa) {
			nicemessage("Student se već nalazi u grupi $naziv_stare_grupe!");
			print '<a href="?sta=saradnik/student&student='.$student.'&predmet='.$predmet.'&ag='.$ag.'">Nazad</a>'."\n";
			return;
		}
		ispis_studenta_sa_labgrupe($student, $staragrupa);
		nicemessage("Student ispisan iz grupe $naziv_stare_grupe. Podaci o prisustvu su izgubljeni.");
	}

	if ($novagrupa>0) {
		$q55 = myquery("insert into student_labgrupa set student=$student, labgrupa=$novagrupa");
		$q57 = myquery("select naziv from labgrupa where id=$novagrupa");
		nicemessage("Student upisan u grupu ".mysql_result($q57,0,0).". Kreirani su default podaci o prisustvu.");
	}
	
	// Potrebno je updatovati komponentu za prisustvo jer su podaci sada promijenjeni
	$q4 = myquery("select k.id from tippredmeta_komponenta as tpk,komponenta as k, akademska_godina_predmet as agp where agp.predmet=$predmet and agp.tippredmeta=tpk.tippredmeta and agp.akademska_godina=$ag and tpk.komponenta=k.id and k.tipkomponente=3");
	while ($r4 = mysql_fetch_row($q4))
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
		print '- <a href="?sta=saradnik/grupa&id='.$staragrupa.'">Spisak studenata u grupi '.mysql_result($q53,0,1).'</a><br />'."\n";
	else
		print '- <a href="?sta=saradnik/grupa&predmet='.$predmet.'&ag='.$ag.'">Spisak svih studenata na predmetu</a><br />'."\n"; // Ovo je jedini slučaj kad $staragrupa može biti nula
	if ($novagrupa>0)
		print '- <a href="?sta=saradnik/grupa&id='.$novagrupa.'">Spisak studenata u grupi '.mysql_result($q57,0,0).'</a><br />'."\n";
	print '- <a href="?sta=saradnik/student&student='.$student.'&predmet='.$predmet.'&ag='.$ag.'">Nazad na detalje studenta '.$ime.' '.$prezime.'</a>'."\n";
	return;
}

if ($_GET['akcija'] == "ponisti_kviz") {
	$kviz = intval($_REQUEST['kviz']);
	$q2000 = myquery("DELETE FROM kviz_student WHERE student=$student AND kviz=$kviz");
}




// --- ISPIS 

if ($slika != "") { print "<img src=\"?sta=common/slika&osoba=$student\" align=\"left\" style=\"margin: 10px\">\n"; }


// Određujemo username radi slanja poruke
$poruka_link = "";
$q59 = myquery("select login from auth where id=$student");
if (mysql_num_rows($q59)>0)
	$poruka_link = "<br><a href=\"?sta=common/inbox&akcija=compose&primalac=" . mysql_result($q59,0,0) . "\">Pošaljite Zamger poruku</a>";


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

$q60=myquery("select id,naziv from labgrupa where predmet=$predmet and akademska_godina=$ag and virtualna=0 order by naziv");
if (mysql_num_rows($q60)>0) {
	?>
	<?=genform("POST");?>
	<input type="hidden" name="akcija" value="promjena_grupe">
	<p>Promijenite grupu: 
	<select name="grupa" class="default"><option value="0"<?=$nijedna?>>-- Nije ni u jednoj grupi --</option>
	<?
	while ($r60 = mysql_fetch_row($q60)) {
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

$q30 = myquery("select kb.bodovi, k.maxbodova, k.tipkomponente, k.id from komponentebodovi as kb, komponenta as k where kb.student=$student and kb.predmet=$ponudakursa and kb.komponenta=k.id");

$bodova=$mogucih=0;
while ($r30 = mysql_fetch_row($q30)) {
	$bodova += $r30[0];
	if ($r30[2] == 4) { // Tip komponente: zadaće
		$q35 = myquery("select sum(bodova) from zadaca where predmet=$predmet and akademska_godina=$ag and komponenta=$r30[3]");
		$do_sada_zadace = round(mysql_result($q35,0,0), 2);
		// Zbir bodova za zadaće ne može preći ono koliko nosi komponenta
		if ($do_sada_zadace > $r30[1])
			$mogucih += $r30[1];
		else
			$mogucih += $do_sada_zadace;
	} else
		$mogucih += $r30[1];
}
if ($bodova>$mogucih) $bodova=$mogucih; //ne bi se trebalo desiti


// boja označava napredak studenta
if ($mogucih==0) $procent=0;
else $procent = intval(($bodova/$mogucih)*100);
if ($procent>=75) 
	$color="#00FF00";
else if ($procent>=50)
	$color="#FFFF00";
else
	$color="#FF0000";


$tabela1=$procent*2;
$tabela2=200-$tabela1;

$ispis1 = "<img src=\"images/fnord.gif\" width=\"$tabela1\" height=\"10\">";
$ispis2 = "<img src=\"images/fnord.gif\" width=\"$tabela2\" height=\"1\"><br/> $bodova bodova";

if ($tabela1>$tabela2) { 
	$ispis1="<img src=\"images/fnord.gif\" width=\"$tabela1\" height=\"1\"><br/> $bodova bodova";
	$ispis2="<img src=\"images/fnord.gif\" width=\"$tabela2\" height=\"10\">";
}

?>


<!-- progress bar -->

<table border="0"><tr><td align="left">
<p>
<table style="border:1px;border-style:solid" width="206" cellpadding="0" cellspacing="2"><tr>
<td width="<?=$tabela1?>" bgcolor="<?=$color?>"><?=$ispis1?></td>
<td width="<?=$tabela2?>" bgcolor="#FFFFFF"><?=$ispis2?></td></tr></table>

<table width="208" border="0" cellspacing="0" cellpadding="0"><tr>
<td width="68">0</td>
<td align="center" width="68">50</td>
<td align="right" width="69">100</td></tr></table>
što je <?=$procent?>% od trenutno mogućih <?=round($mogucih,2) /* Rješavamo nepreciznost floata */ ?> bodova.</p>
</td></tr></table>


<!-- end progress bar -->
<?


// Nekoliko korisnih operacija za site admina

if ($user_siteadmin) {
	?>
	<p><a href="index.php?sta=saradnik/student&student=<?=$student?>&predmet=<?=$predmet?>&ag=<?=$ag?>&akcija=ispis">Ispiši studenta sa predmeta</a> * <a href="index.php?sta=studentska/osobe&akcija=edit&osoba=<?=$student?>">Detaljnije o studentu</a> * <a href="index.php?su=<?=$student?>">Prijavi se kao student</a></p>
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
	$q70 = myquery("select id,UNIX_TIMESTAMP(datum), vrijeme from cas where labgrupa=$idgrupe and komponenta=$komponenta");
	if (mysql_num_rows($q70)<1) return; // Ne ispisuj grupe u kojima nema registrovanih časova

	$datumi = $vremena = $statusi = "";
	while ($r70 = mysql_fetch_row($q70)) {
		$datumi .= "<td>".date("d.m",$r70[1])."</td>\n";
		list($sati,$minute,$sekunde) = explode(":", $r70[2]);
		$vremena .= "<td>$sati<sup>$minute</sup></td>\n";
		$q80 = myquery("select prisutan from prisustvo where student=$student and cas=$r70[0]");
		if (mysql_num_rows($q80)<1) {
			$statusi .= "<td bgcolor=\"#FFFFCC\" align=\"center\" id=\"dane-".$student."-".$r70[0]."\" onclick=\"javascript:prisustvo(".$student.",".$r70[0].")\"><div id=\"danetekst-".$student."-".$r70[0]."\"> / </div></td>\n";
		} else if (mysql_result($q80,0,0)==1) {
			$statusi .= "<td bgcolor=\"#CCFFCC\" align=\"center\" id=\"dane-".$student."-".$r70[0]."\" onclick=\"javascript:prisustvo(".$student.",".$r70[0].")\"><div id=\"danetekst-".$student."-".$r70[0]."\">DA</div></td>\n";
		} else {
			$statusi .= "<td bgcolor=\"#FFCCCC\" align=\"center\" id=\"dane-".$student."-".$r70[0]."\" onclick=\"javascript:prisustvo(".$student.",".$r70[0].")\"><div id=\"danetekst-".$student."-".$r70[0]."\">NE</div></td>\n";
			$odsustva++;
		}
	}

	
	?>

	<b>Prisustvo (<?=$imegrupe?>):</b><br/>
	<table cellspacing="0" cellpadding="2" border="0" id="prisustvo">
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

$q40 = myquery("select k.id,k.maxbodova,k.prolaz,k.opcija from komponenta as k, tippredmeta_komponenta as tpk, akademska_godina_predmet as agp
where agp.predmet=$predmet and agp.tippredmeta=tpk.tippredmeta and agp.akademska_godina=$ag and tpk.komponenta=k.id and k.tipkomponente=3"); // 3 = prisustvo

while ($r40 = mysql_fetch_row($q40)) {
	$id_komponente = $r40[0];
	$max_bodova = $r40[1];
	$min_bodova = $r40[2];
	$max_izostanaka = $r40[3];

	$odsustva = $casova = 0;
	$q60 = myquery("select l.id,l.naziv from labgrupa as l, student_labgrupa as sl where l.predmet=$predmet and l.akademska_godina=$ag and l.id=sl.labgrupa and sl.student=$student");
	
	while ($r60 = mysql_fetch_row($q60)) {
		$odsustva += prisustvo_ispis($r60[0],$r60[1],$id_komponente, $student);
		$q71 = myquery("select count(*) from cas where labgrupa=$r60[0] and komponenta=$id_komponente");
		$casova += mysql_result($q71,0,0);;
	}
	
	if ($max_izostanaka == -1) {
		if ($casova == 0) 
			$bodovi = 10;
		else
			$bodovi = $min_bodova + round(($max_bodova - $min_bodova) * (($casova - $odsustva) / $casova), 2 ); 
	} else if ($max_izostanaka == -2) { // Paraproporcionalni sistem TP
		if ($odsustva <= 2)
			$bodovi = $max_bodova;
		else if ($odsustva <= 2 + ($max_bodova - $min_bodova)/2)
			$bodovi = $max_bodova - ($odsustva-2)*2;
		else
			$bodovi = $min_bodova;
	} else if ($odsustva<=$max_izostanaka) {
		$bodovi = $max_bodova;
	} else {
		$bodovi = $min_bodova;
	}
	?><p>Ukupno na prisustvo: <b><?=$bodovi?></b> bodova.</p>
	<?
}



// KVIZOVI

$q200 = myquery("SELECT id, naziv, prolaz_bodova FROM kviz WHERE predmet=$predmet AND akademska_godina=$ag");
if (mysql_num_rows($q200) > 0) {
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

	while ($r200 = mysql_fetch_row($q200)) {
		$q210 = myquery("SELECT dovrsen, bodova FROM kviz_student WHERE student=$student AND kviz=$r200[0]");
		$tekst = "";

		if (mysql_num_rows($q210) > 0) {
			$bodova = mysql_result($q210,0,1);
			if (mysql_result($q210,0,0) == 0) {
				$tekst = "<img src=\"images/16x16/zad_cekaj.png\" width=\"8\" height=\"8\"> Nije završio/la";
			} else if ($bodova < $r200[2]) {
				$tekst = "<img src=\"images/16x16/brisanje.png\" width=\"8\" height=\"8\"> $bodova bodova";
			} else {
				$tekst = "<img src=\"images/16x16/zad_ok.png\" width=\"8\" height=\"8\"> $bodova bodova";
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
$stat_icon = array("zad_bug", "zad_preg", "zad_copy", "zad_bug", "zad_preg", "zad_ok");
$stat_tekst = array("Bug u programu", "Pregled u toku", "Zadaća prepisana", "Bug u programu", "Pregled u toku", "Zadaća OK");


?>


<!-- zadace -->

<b>Zadaće:</b><br/>
<table cellspacing="0" cellpadding="2" border="0" id="zadace">
	<thead>
		<tr>
	<td>&nbsp;</td>
<?

// Zaglavlje tabele - potreban nam je max. broj zadataka u zadaci

$q20 = myquery("select zadataka from zadaca where predmet=$predmet and akademska_godina=$ag order by zadataka desc limit 1");
if (mysql_num_rows($q20)>0) {
	$broj_zadataka = mysql_result($q20,0,0);
	for ($i=1;$i<=$broj_zadataka;$i++) {
		?><td>Zadatak <?=$i?>.</td><?
	}
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

$q21 = myquery("select id,naziv,bodova,zadataka from zadaca where predmet=$predmet and akademska_godina=$ag order by komponenta,id");
while ($r21 = mysql_fetch_row($q21)) {
	$zadaca = $r21[0];
	$mogucih += $r21[2];
	$zzadataka = $r21[3];
	?><tr>
	<th><?=$r21[1]?></th>
	<?
	$bodova_zadaca = 0;

	for ($zadatak=1;$zadatak<=$broj_zadataka;$zadatak++) {
		// Ako tekuća zadaća nema toliko zadataka, ispisujemo blank polje
		if ($zadatak>$zzadataka) {
			?><td>&nbsp;</td><?
			continue;
		}

		// Uzmi samo rjesenje sa zadnjim IDom
		$q22 = myquery("select status,bodova,komentar from zadatak where student=$student and zadaca=$zadaca and redni_broj=$zadatak order by id desc limit 1");
		if (mysql_num_rows($q22)<1) {
			?><td>&nbsp;</td><?
		} else {
			$status = mysql_result($q22,0,0);
			$bodova_zadatak = mysql_result($q22,0,1);
			$bodova_zadaca += $bodova_zadatak;

			if (strlen(mysql_result($q22,0,2))>2)
				$imakomentar = "<img src=\"images/16x16/komentar.png\"  width=\"15\" height=\"14\" border=\"0\" title=\"Ima komentar\" alt=\"Ima komentar\" align=\"center\">";
			else
				$imakomentar = "";

			?><td><a href="javascript:openzadaca('<?=$student?>', '<?=$zadaca?>', '<?=$zadatak?>')"><img src="images/16x16/<?=$stat_icon[$status]?>.png" width="16" height="16" border="0" align="center" title="<?=$stat_tekst[$status]?>" alt="<?=$stat_tekst[$status]?>"> <?=$bodova_zadatak?> <?=$imakomentar?></a></td>
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
	<tr><td colspan="<?=$broj_zadataka+1?>" align="right">UKUPNO: </td>
	<td><?=$bodova_sve_zadace?></td></tr>
</tbody>
</table>

<p>Za historiju izmjena kliknite na željeni zadatak. <a href="#" onclick="javascript:window.open('legenda-zadace.html','blah6','width=320,height=130'); return false;">Legenda simbola</a></p>
<br/>

<!-- end zadace -->

<?




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


$q25 = myquery("select k.id, k.gui_naziv from komponenta as k, tippredmeta_komponenta as tpk, akademska_godina_predmet as agp where agp.predmet=$predmet and agp.tippredmeta=tpk.tippredmeta and agp.akademska_godina=$ag and tpk.komponenta=k.id and k.tipkomponente=5"); // 5 = fiksna komponenta
if (mysql_num_rows($q25)>0) {
?>

<!-- fiksne komponente -->

<table cellspacing="0" cellpadding="2" border="0" id="zadace">
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

while ($r25 = mysql_fetch_row($q25)) {
	$komponenta = $r25[0];
	$q27 = myquery("select bodovi from komponentebodovi where student=$student and predmet=$ponudakursa and komponenta=$komponenta");
	if (mysql_num_rows($q27)<1) $ocjenaedit="/";
	else $ocjenaedit=mysql_result($q27,0,0);
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
if (mysql_num_rows($q25)>0) {
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
	

$q30 = myquery("select i.id, UNIX_TIMESTAMP(i.datum), k.gui_naziv, k.id, k.prolaz from ispit as i, komponenta as k where i.predmet=$predmet and i.akademska_godina=$ag and i.komponenta=k.id order by i.datum,i.komponenta");
if (mysql_num_rows($q30) == 0) {
	print "<p>Nije bilo parcijalnih ispita.</p>";
} else {
?>
<table cellspacing="0" cellpadding="2" border="0" id="zadace">
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

while ($r30 = mysql_fetch_row($q30)) {
	$ispit = $r30[0];
	$q40 = myquery("select ocjena from ispitocjene where ispit=$ispit and student=$student");
	if (mysql_num_rows($q40)<1) {
		$ocjenaedit="/";
		$ispitocjena="Nije izašao/la";
	} else {
		$ocjenaedit=mysql_result($q40,0,0);
		$ispitocjena="<b>".mysql_result($q40,0,0)." bodova</b>";
	}
/*	?>
	<p><?=$r30[2]?> (<?=date("d. m. Y",$r30[1])?>): <?=$ispitocjena?>
	<?*/
	?>
	<tr>
		<td><?=$r30[2]?></td>
		<td><?=date("d. m. Y",$r30[1])?></td>
		<td><?
		if ($ocjenaedit=="/") print "&nbsp;";
		else if ($ocjenaedit>=$r30[4]) print "<img src=\"images/16x16/zad_ok.png\" width=\"16\" height=\"16\">"; 
		else print "<img src=\"images/16x16/brisanje.png\" width=\"16\" height=\"16\">"; // najljepše slike
		?></td>
		<td id="ispit-<?=$student?>-<?=$ispit?>" ondblclick="coolboxopen(this)"><?=$ocjenaedit?></td>
		<td><? 
		if ($privilegija=="nastavnik" || $privilegija=="super_asistent" || $user_siteadmin) { 
			?><div id="ispitlog<?=$ispit?>"></div><?
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

if (mysql_num_rows($q30) > 0) {
?>
</tbody></table>
<p>Dvokliknite na bodove da promijenite podatak ili upišete novi. Za brisanje rezultata, pobrišite postojeći podatak i pritisnite Enter.</p>
<?
}




// KONAČNA OCJENA

$vrijeme_konacne_ocjene=0;
$q50 = myquery("select ocjena, UNIX_TIMESTAMP(datum), UNIX_TIMESTAMP(datum_u_indeksu) from konacna_ocjena where student=$student and predmet=$predmet and akademska_godina=$ag");
if (mysql_num_rows($q50)>0) {
	$konacnaocjena = mysql_result($q50,0,0);
	$vrijeme_konacne_ocjene = mysql_result($q50,0,1);
	$datum_u_indeksu = mysql_result($q50,0,2);
} else {
	$konacnaocjena = "/";
}

?>
<p>&nbsp;</p>
<table cellspacing="0" cellpadding="2" border="0" id="zadace">
<tr>
	<td>&nbsp;</td>
	<td>Ocjena:</td>
	<td>Datum u indeksu:</td>
	<td>Dnevnik izmjena:</td>
</tr>
<tr>
	<td><b>Konačna ocjena:</b></td>
<?

if ($privilegija=="nastavnik" || $user_siteadmin) {
	?>
	<td id="ko-<?=$student?>-<?=$predmet?>-<?=$ag?>" ondblclick="coolboxopen(this)"><?=$konacnaocjena?></td>
	<td id="kodatum-<?=$student?>-<?=$predmet?>-<?=$ag?>" ondblclick="coolboxopen(this)"><?=date("d. m. Y", $datum_u_indeksu)?></td>
	<td><div id="kolog"></div></td>
	<?
} else {
	?>
	<td><?=$konacnaocjena?></td>
	<?
}

print "</tr></table>\n";



// **************************************
// POPUNA LOGOVA
// **************************************

// Ne radimo ništa ako korisnik nema privilegije

if ($privilegija!="nastavnik" && $privilegija!="super_asistent" && !$user_siteadmin) return;

?>


<SCRIPT language="JavaScript">
<?


// Spisak ponuda kursa, za slucaj da nema rezultata
$q90 = myquery("select id from ponudakursa where predmet=$predmet and akademska_godina=$ag");
$pkovi = array();
while ($r90 = mysql_fetch_row($q90)) array_push($pkovi, $r90[0]);


// Log za ispite

$q100 = myquery("select i.id, UNIX_TIMESTAMP(i.vrijemeobjave) from ispit as i, komponenta as k where i.predmet=$predmet and i.akademska_godina=$ag and i.komponenta=k.id order by i.datum,i.komponenta");

while ($r100 = mysql_fetch_row($q100)) {
	$vrijeme_ispita = $r100[1];

	// Utvrdjujemo ocjenu da bismo lakse mogli rekonstruisati izmjene kroz log
	$q105 = myquery("select ocjena from ispitocjene where ispit=$r100[0] and student=$student");
	if (mysql_num_rows($q105)<1)
		$ispitocjena="/";
	else
		$ispitocjena=mysql_result($q105,0,0);

	// Spisak izmjena ocjene
	$q110 = myquery("select dogadjaj, UNIX_TIMESTAMP(vrijeme), userid from log where 
	dogadjaj like 'AJAH ispit - upisan novi rezultat % (ispit i$r100[0], student u$student)' or 
	dogadjaj like 'AJAH ispit - izbrisan rezultat % (ispit i$r100[0], student u$student)' or 
	dogadjaj like 'AJAH ispit - izmjena rezultata % (ispit i$r100[0], student u$student)' order by id desc");
	while ($r110 = mysql_fetch_row($q110)) {
		$datum = date("d.m.Y. H:i:s", $r110[1]);
		$q120 = myquery("select ime,prezime from osoba where id=".$r110[2]);
		if (mysql_num_rows($q120)>0) {
			$korisnik = mysql_result($q120,0,0)." ".mysql_result($q120,0,1);
		} else {
			$korisnik = "/nepoznat korisnik $r110[2]/";
		}

		if (strstr($r110[0], "upisan novi rezultat")) {
			$rezultat = floatval(substr($r110[0], 34));
			if ($rezultat != $ispitocjena) $rezultat .= " ?";
			$ispitocjena = "/";
			?>
			document.getElementById('ispitlog<?=$r100[0]?>').innerHTML = '<img src="images/16x16/log_edit.png" width="16" height="16" align="center"> upisan rezultat <b><?=$rezultat?></b> (<?=$korisnik?>, <?=$datum?>)<br />' + document.getElementById('ispitlog<?=$r100[0]?>').innerHTML;
			<?
		} else if (strstr($r110[0], "izbrisan rezultat")) {
			$rezultat = floatval(substr($r110[0], 31));
			if ($ispitocjena != "/") $rezultat .= " ?"; else $ispitocjena=$rezultat;
			?>
			document.getElementById('ispitlog<?=$r100[0]?>').innerHTML = '<img src="images/16x16/log_edit.png" width="16" height="16" align="center"> izbrisan rezultat (<?=$korisnik?>, <?=$datum?>)<br />' + document.getElementById('ispitlog<?=$r100[0]?>').innerHTML;
			<?
		} else if (preg_match("/izmjena rezultata ([\d\.]+) u ([\d\.]+)/", $r110[0], $matches)) {
			$starirezultat = floatval($matches[1]);
			$rezultat = floatval($matches[2]);
			if ($ispitocjena != $rezultat) $rezultat .= " ?";
			$ispitocjena = $starirezultat;
			?>
			document.getElementById('ispitlog<?=$r100[0]?>').innerHTML = '<img src="images/16x16/log_edit.png" width="16" height="16" align="center"> promijenjen rezultat u <b><?=$rezultat?></b> (<?=$korisnik?>, <?=$datum?>)<br />' + document.getElementById('ispitlog<?=$r100[0]?>').innerHTML;
			<?
		}
	}

	// Necemo traziti masovne unose ako student nije ni izlazio na ispit
	if ($ispitocjena == "/") continue; 


	// Masovni unosi

	$q110 = myquery("select UNIX_TIMESTAMP(vrijeme), userid from log where dogadjaj='masovni rezultati ispita za predmet pp$predmet' AND UNIX_TIMESTAMP(vrijeme)>$r100[1]-10 ORDER BY vrijeme"); // uzimamo razliku 10 sekundi, jer moze doci do malog kasnjenja prilikom unosa u log
	if (mysql_num_rows($q110)>0) {
		$datum = date("d.m.Y. H:i:s", mysql_result($q110,0,0));
		$q120 = myquery("select ime,prezime from osoba where id=".mysql_result($q110,0,1));
		if (mysql_num_rows($q120)>0) {
			$korisnik = mysql_result($q120,0,0)." ".mysql_result($q120,0,1);
		} else {
			$korisnik = "/nepoznat korisnik ".mysql_result($q110,0,1)."/";
		}
		?>
		document.getElementById('ispitlog<?=$r100[0]?>').innerHTML = '<img src="images/16x16/log_edit.png" width="16" height="16" align="center"> masovni rezultati ispita - <b><?=$ispitocjena?></b> (<?=$korisnik?>, <?=$datum?>)<br />' + document.getElementById('ispitlog<?=$r100[0]?>').innerHTML;
		<?

	// Nema pod oznakom predmeta, pokusacemo ponudu kursa
	} else foreach ($pkovi as $ponudakursa) {
		$q110 = myquery("select UNIX_TIMESTAMP(vrijeme), userid from log where dogadjaj='masovni rezultati ispita za predmet p$ponudakursa' AND UNIX_TIMESTAMP(vrijeme)>$r100[1]-10");
		if (mysql_num_rows($q110)>0) {
			$datum = date("d.m.Y. H:i:s", mysql_result($q110,0,0));
			$q120 = myquery("select ime,prezime from osoba where id=".mysql_result($q110,0,1));
			if (mysql_num_rows($q120)>0) {
				$korisnik = mysql_result($q120,0,0)." ".mysql_result($q120,0,1);
			} else {
				$korisnik = "/nepoznat korisnik ".mysql_result($q110,0,1)."/";
			}
			?>
			document.getElementById('ispitlog<?=$r100[0]?>').innerHTML = '<img src="images/16x16/log_edit.png" width="16" height="16" align="center"> masovni rezultati ispita - <b><?=$ispitocjena?></b> (<?=$korisnik?>, <?=$datum?>)<br />' + document.getElementById('ispitlog<?=$r100[0]?>').innerHTML;
			<?
			break;
		}
	}
	
}


// Log za konacnu ocjenu
$bilo=0;
$upit = "
dogadjaj like 'AJAH ko - dodana ocjena % (predmet pp$predmet, student u$student)' or 
dogadjaj like 'AJAH ko - obrisana ocjena % (predmet pp$predmet, student u$student)' or 
dogadjaj like 'AJAH ko - izmjena ocjene % (predmet pp$predmet, student u$student)' or 
dogadjaj like 'dopisana ocjena % prilikom upisa na studij (predmet pp$predmet, student u$student)' or 
dogadjaj like 'masovno dodana ocjena % (predmet pp$predmet, student u$student)'";

$q150 = myquery("select dogadjaj, UNIX_TIMESTAMP(vrijeme), userid from log where $upit order by id desc");
while ($r150 = mysql_fetch_row($q150)) {
	$bilo=1;
	$datum = date("d.m.Y. H:i:s", $r150[1]);
	$q160 = myquery("select ime,prezime from osoba where id=$r150[2]");
	if (mysql_num_rows($q160)>0) {
		$korisnik = mysql_result($q160,0,0)." ".mysql_result($q160,0,1);
	} else {
		$korisnik = "/nepoznat korisnik $r150[2]/";
	}

	if (strstr($r150[0], " - dodana ocjena")) {
		$rezultat = intval(substr($r150[0], 24));
		if ($rezultat != $konacnaocjena) $rezultat .= " ?";
		$konacnaocjena = "/";
		?>
		document.getElementById('kolog').innerHTML = '<img src="images/16x16/log_edit.png" width="16" height="16" align="center"> dodana ocjena <b><?=$rezultat?></b> (<?=$korisnik?>, <?=$datum?>)<br />' + document.getElementById('kolog').innerHTML;
		<?
	} else if (strstr($r150[0], "obrisana ocjena")) {
		$rezultat = intval(substr($r150[0], 26));
		if ($konacnaocjena != "/") $rezultat .= " ?"; else $konacnaocjena=$rezultat;
		?>
		document.getElementById('kolog').innerHTML = '<img src="images/16x16/log_edit.png" width="16" height="16" align="center"> obrisana ocjena (<?=$korisnik?>, <?=$datum?>)<br />' + document.getElementById('kolog').innerHTML;
		<?
	} else if (preg_match("/izmjena ocjene (\d+) u (\d+)/", $r150[0], $matches)) {
		$starirezultat = intval($matches[1]);
		$rezultat = intval($matches[2]);
		if ($konacnaocjena != $rezultat) $rezultat .= " ?";
		$konacnaocjena = $starirezultat;
		?>
		document.getElementById('kolog').innerHTML = '<img src="images/16x16/log_edit.png" width="16" height="16" align="center"> promijenjena ocjena u <b><?=$rezultat?></b> (<?=$korisnik?>, <?=$datum?>)<br />' + document.getElementById('kolog').innerHTML;
		<?
	} else if (strstr($r150[0], "dopisana ocjena")) {
		$rezultat = intval(substr($r150[0], 16));
		if ($konacnaocjena != $rezultat) $rezultat .= " ?";
		$konacnaocjena = "/";
		?>
		document.getElementById('kolog').innerHTML = '<img src="images/16x16/log_edit.png" width="16" height="16" align="center"> studentska služba dopisala ocjenu <b><?=$rezultat?></b> prilikom upisa u sljedeći semestar (<?=$korisnik?>, <?=$datum?>)<br />' + document.getElementById('kolog').innerHTML;
		<?
	} else if (strstr($r150[0], "masovno dodana ocjena")) {
		$rezultat = intval(substr($r150[0], 22));
		if ($konacnaocjena != $rezultat) $rezultat .= " ?";
		$konacnaocjena = "/";
		?>
		document.getElementById('kolog').innerHTML = '<img src="images/16x16/log_edit.png" width="16" height="16" align="center"> masovno upisana ocjena <b><?=$rezultat?></b> (<?=$korisnik?>, <?=$datum?>)<br />' + document.getElementById('kolog').innerHTML;
		<?
	}
}

/*if (mysql_num_rows($q150)<1) foreach ($pkovi as $ponudakursa) {
	$q150 = myquery("select dogadjaj, UNIX_TIMESTAMP(vrijeme), userid from log where dogadjaj like 'AJAH ko - dodana ocjena % (predmet p$ponudakursa, student u$student)' or dogadjaj like 'AJAH ko - obrisana ocjena % (predmet p$ponudakursa, student u$student)' or dogadjaj like 'AJAH ko - izmjena ocjene % (predmet p$ponudakursa, student u$student)' order by id desc");

	while ($r150 = mysql_fetch_row($q150)) {
		$bilo=1;
		$datum = date("d.m.Y. H:i:s", $r150[1]);
		$q160 = myquery("select ime,prezime from osoba where id=$r150[2]");
		if (mysql_num_rows($q160)>0) {
			$korisnik = mysql_result($q160,0,0)." ".mysql_result($q160,0,1);
		} else {
			$korisnik = "/nepoznat korisnik $r150[2]/";
		}
	
		if (strstr($r150[0], "dodana ocjena")) {
			$rezultat = intval(substr($r150[0], 24));
			if ($rezultat != $konacnaocjena) $rezultat .= " ?";
			$konacnaocjena = "/";
			$vrijeme_konacne_ocjene=$r150[1];
			?>
			document.getElementById('kolog').innerHTML = '<img src="images/16x16/log_edit.png" width="16" height="16" align="center"> dodana ocjena <b><?=$rezultat?></b> (<?=$korisnik?>, <?=$datum?>)<br />' + document.getElementById('kolog').innerHTML;
			<?
		} else if (strstr($r150[0], "obrisana ocjena")) {
			$rezultat = intval(substr($r150[0], 26));
			if ($konacnaocjena != "/") $rezultat .= " ?"; else $konacnaocjena=$rezultat;
			$vrijeme_konacne_ocjene=0;
			?>
			document.getElementById('kolog').innerHTML = '<img src="images/16x16/log_edit.png" width="16" height="16" align="center"> obrisana ocjena (<?=$korisnik?>, <?=$datum?>)<br />' + document.getElementById('kolog').innerHTML;
			<?
		} else if (preg_match("/izmjena ocjene (\d+) u (\d+)/", $r150[0], $matches)) {
			$starirezultat = intval($matches[1]);
			$rezultat = intval($matches[2]);
			if ($konacnaocjena != $rezultat) $rezultat .= " ?";
			$konacnaocjena = $starirezultat;
			$vrijeme_konacne_ocjene=$r150[1];
			?>
			document.getElementById('kolog').innerHTML = '<img src="images/16x16/log_edit.png" width="16" height="16" align="center"> promijenjena ocjena u <b><?=$rezultat?></b> (<?=$korisnik?>, <?=$datum?>)<br />' + document.getElementById('kolog').innerHTML;
			<?
		}
//print "$r150[0] $r150[1] $r150[2]\n";
	}
}*/


if (mysql_num_rows($q150)==0 && $vrijeme_konacne_ocjene > 0) {
	$bilo=1;
	$q170 = myquery("select UNIX_TIMESTAMP(vrijeme), userid from log where dogadjaj='masovno upisane ocjene na predmet pp$predmet' AND ABS(UNIX_TIMESTAMP(vrijeme)-$vrijeme_konacne_ocjene)<10"); // uzimamo apsolutnu razliku 10 sekundi, jer moze doci do malog kasnjenja prilikom unosa u log
	if (mysql_num_rows($q170)>0) {
		$datum = date("d.m.Y. H:i:s", mysql_result($q170,0,0));
		$q180 = myquery("select ime,prezime from osoba where id=".mysql_result($q170,0,1));
		if (mysql_num_rows($q180)>0) {
			$korisnik = mysql_result($q180,0,0)." ".mysql_result($q180,0,1);
		} else {
			$korisnik = "/nepoznat korisnik ".mysql_result($q170,0,1)."/";
		}
		?>
		document.getElementById('kolog').innerHTML = '<img src="images/16x16/log_edit.png" width="16" height="16" align="center"> masovno unesene ocjene - <b><?=$konacnaocjena?></b> (<?=$korisnik?>, <?=$datum?>)<br />' + document.getElementById('kolog').innerHTML;
		<?

	// Nema pod oznakom predmeta, pokusacemo ponudu kursa
	} else foreach ($pkovi as $ponudakursa) {
		$q170 = myquery("select UNIX_TIMESTAMP(vrijeme), userid from log where dogadjaj='masovno upisane ocjene na predmet p$ponudakursa' AND ABS(UNIX_TIMESTAMP(vrijeme)-".mysql_result($q50,0,1).")<10"); // uzimamo apsolutnu razliku 10 sekundi, jer moze doci do malog kasnjenja prilikom unosa u log
		if (mysql_num_rows($q170)>0) {
			$datum = date("d.m.Y. H:i:s", mysql_result($q170,0,0));
			$q180 = myquery("select ime,prezime from osoba where id=".mysql_result($q170,0,1));
			if (mysql_num_rows($q180)>0) {
				$korisnik = mysql_result($q180,0,0)." ".mysql_result($q180,0,1);
			} else {
				$korisnik = "/nepoznat korisnik ".mysql_result($q170,0,1)."/";
			}
			?>
			document.getElementById('kolog').innerHTML = '<img src="images/16x16/log_edit.png" width="16" height="16" align="center"> masovno unesene ocjene - <b><?=$konacnaocjena?></b> (<?=$korisnik?>, <?=$datum?>)<br />' + document.getElementById('kolog').innerHTML;
			<?
		}
	}
	
}

// Ako nema nista u logu, sakrivamo ga
if ($bilo==0) {
	?>
	toggleVisibilityObj('kolink');
	<?
}



// Log za fiksne komponente
// Radimo samo ako ima fiksnih komponenti

$q200 = myquery("select k.id from komponenta as k, tippredmeta_komponenta as tpk, akademska_godina_predmet as agp where agp.predmet=$predmet and agp.tippredmeta=tpk.tippredmeta and agp.akademska_godina=$ag and tpk.komponenta=k.id and k.tipkomponente=5"); // 5 = fiksna komponenta
while ($r200 = mysql_fetch_row($q200)) {
	$komponenta = $r200[0];

	// Utvrdjujemo ocjenu da bismo lakse mogli rekonstruisati izmjene kroz log
	$q205 = myquery("select bodovi from komponentebodovi where student=$student and predmet=$ponudakursa and komponenta=$komponenta");
	if (mysql_num_rows($q205)<1) $ispitocjena="/";
	else $ispitocjena=mysql_result($q205,0,0);

	// Izmjene fiksne komponente putem AJAHa
	$q210 = myquery("select dogadjaj, UNIX_TIMESTAMP(vrijeme), userid from log where dogadjaj like 'AJAH fiksna - upisani bodovi % za fiksnu komponentu $komponenta (predmet pp$predmet, student u$student)' order by id desc");
	while ($r210 = mysql_fetch_row($q210)) {
		$datum = date("d.m.Y. H:i:s", $r210[1]);
		$q220 = myquery("select ime,prezime from osoba where id=".$r210[2]);
		if (mysql_num_rows($q220)>0) {
			$korisnik = mysql_result($q220,0,0)." ".mysql_result($q220,0,1);
		} else {
			$korisnik = "/nepoznat korisnik $r210[2]/";
		}
		
		$rezultat = floatval(substr($r210[0], 29));
		?>
		document.getElementById('fiksnalog<?=$komponenta?>').innerHTML = '<img src="images/16x16/log_edit.png" width="16" height="16" align="center"> upisan rezultat <b><?=$rezultat?></b> (<?=$korisnik?>, <?=$datum?>)<br />' + document.getElementById('fiksnalog<?=$komponenta?>').innerHTML;
		<?
	}

	// Masovni unosi - jednog dana kad bude

/*	$q110 = myquery("select UNIX_TIMESTAMP(vrijeme), userid from log where dogadjaj='masovni rezultati ispita za predmet pp$predmet' AND ABS(UNIX_TIMESTAMP(vrijeme)-$r100[1])<10"); // uzimamo apsolutnu razliku 10 sekundi, jer moze doci do malog kasnjenja prilikom unosa u log
	if (mysql_num_rows($q110)>0) {
		$datum = date("d.m.Y. H:i:s", mysql_result($q110,0,0));
		$q120 = myquery("select ime,prezime from osoba where id=".mysql_result($q110,0,1));
		if (mysql_num_rows($q120)>0) {
			$korisnik = mysql_result($q120,0,0)." ".mysql_result($q120,0,1);
		} else {
			$korisnik = "/nepoznat korisnik/";
		}
		?>
		document.getElementById('ispitlog<?=$r100[0]?>').innerHTML = '<img src="images/16x16/log_edit.png" width="16" height="16" align="center"> masovni rezultati ispita - <b><?=$ispitocjena?></b> (<?=$korisnik?>, <?=$datum?>)<br />' + document.getElementById('ispitlog<?=$r100[0]?>').innerHTML;
		<?

	// Nema pod oznakom predmeta, pokusacemo ponudu kursa
	} else foreach ($pkovi as $ponudakursa) {
		$q110 = myquery("select UNIX_TIMESTAMP(vrijeme), userid from log where dogadjaj='masovni rezultati ispita za predmet p$ponudakursa' AND ABS(UNIX_TIMESTAMP(vrijeme)-$r100[1])<10");
		if (mysql_num_rows($q110)>0) {
			$datum = date("d.m.Y. H:i:s", mysql_result($q110,0,0));
			$q120 = myquery("select ime,prezime from osoba where id=".mysql_result($q110,0,1));
			if (mysql_num_rows($q120)>0) {
				$korisnik = mysql_result($q120,0,0)." ".mysql_result($q120,0,1);
			} else {
				$korisnik = "/nepoznat korisnik/";
			}
			?>
			document.getElementById('ispitlog<?=$r100[0]?>').innerHTML = '<img src="images/16x16/log_edit.png" width="16" height="16" align="center"> masovni rezultati ispita - <b><?=$ispitocjena?></b> (<?=$korisnik?>, <?=$datum?>)<br />' + document.getElementById('ispitlog<?=$r100[0]?>').innerHTML;
			<?
		}
	}*/

}

/*$q100 = myquery("select i.id, UNIX_TIMESTAMP(i.vrijemeobjave) from ispit as i, komponenta as k where i.predmet=$predmet and i.akademska_godina=$ag and i.komponenta=k.id order by i.datum,i.komponenta");

while ($r100 = mysql_fetch_row($q100)) {
	// Utvrdjujemo ocjenu da bismo lakse mogli rekonstruisati izmjene kroz log
	$q105 = myquery("select ocjena from ispitocjene where ispit=$r100[0] and student=$student");
	if (mysql_num_rows($q105)<1)
		$ispitocjena="/";
	else
		$ispitocjena=mysql_result($q105,0,0);

	// Spisak izmjena ocjene
	$q110 = myquery("select dogadjaj, UNIX_TIMESTAMP(vrijeme), userid from log where dogadjaj like 'AJAH ispit - upisan novi rezultat % (ispit i$r100[0], student u$student)' or dogadjaj like 'AJAH ispit - izbrisan rezultat % (ispit i$r100[0], student u$student)' or dogadjaj like 'AJAH ispit - izmjena rezultata % (ispit i$r100[0], student u$student)' order by id desc");
	while ($r110 = mysql_fetch_row($q110)) {
		$datum = date("d.m.Y. H:i:s", $r110[1]);
		$q120 = myquery("select ime,prezime from osoba where id=".$r110[2]);
		if (mysql_num_rows($q120)>0) {
			$korisnik = mysql_result($q120,0,0)." ".mysql_result($q120,0,1);
		} else {
			$korisnik = "/nepoznat korisnik $r110[2]/";
		}

		if (strstr($r110[0], "upisan novi rezultat")) {
			$rezultat = floatval(substr($r110[0], 34));
			if ($rezultat != $ispitocjena) $rezultat .= " ?";
			$ispitocjena = "/";
			?>
			document.getElementById('ispitlog<?=$r100[0]?>').innerHTML = '<img src="images/16x16/log_edit.png" width="16" height="16" align="center"> upisan rezultat <b><?=$rezultat?></b> (<?=$korisnik?>, <?=$datum?>)<br />' + document.getElementById('ispitlog<?=$r100[0]?>').innerHTML;
			<?
		} else if (strstr($r110[0], "izbrisan rezultat")) {
			$rezultat = floatval(substr($r110[0], 31));
			if ($ispitocjena != "/") $rezultat .= " ?"; else $ispitocjena=$rezultat;
			?>
			document.getElementById('ispitlog<?=$r100[0]?>').innerHTML = '<img src="images/16x16/log_edit.png" width="16" height="16" align="center"> izbrisan rezultat (<?=$korisnik?>, <?=$datum?>)<br />' + document.getElementById('ispitlog<?=$r100[0]?>').innerHTML;
			<?
		} else if (preg_match("/izmjena rezultata ([\d\.]+) u ([\d\.]+)/", $r110[0], $matches)) {
			$starirezultat = floatval($matches[1]);
			$rezultat = floatval($matches[2]);
			if ($ispitocjena != $rezultat) $rezultat .= " ?";
			$ispitocjena = $starirezultat;
			?>
			document.getElementById('ispitlog<?=$r100[0]?>').innerHTML = '<img src="images/16x16/log_edit.png" width="16" height="16" align="center"> promijenjen rezultat u <b><?=$rezultat?></b> (<?=$korisnik?>, <?=$datum?>)<br />' + document.getElementById('ispitlog<?=$r100[0]?>').innerHTML;
			<?
		}
	}

	// Sakrivamo link na log ako nema ništa u njemu
	if ($ispitocjena == "/" && mysql_num_rows($q110)<1) {
/*		?>
		toggleVisibilityObj('ispitlink<?=$r100[0]?>');
		<?*/
/*	}

	// Necemo traziti masovne unose ako student nije ni izlazio na ispit
	if ($ispitocjena == "/") continue; 


	// Masovni unosi

	$q110 = myquery("select UNIX_TIMESTAMP(vrijeme), userid from log where dogadjaj='masovni rezultati ispita za predmet pp$predmet' AND ABS(UNIX_TIMESTAMP(vrijeme)-$r100[1])<10"); // uzimamo apsolutnu razliku 10 sekundi, jer moze doci do malog kasnjenja prilikom unosa u log
	if (mysql_num_rows($q110)>0) {
		$datum = date("d.m.Y. H:i:s", mysql_result($q110,0,0));
		$q120 = myquery("select ime,prezime from osoba where id=".mysql_result($q110,0,1));
		if (mysql_num_rows($q120)>0) {
			$korisnik = mysql_result($q120,0,0)." ".mysql_result($q120,0,1);
		} else {
			$korisnik = "/nepoznat korisnik ".mysql_result($q110,0,1)."/";
		}
		?>
		document.getElementById('ispitlog<?=$r100[0]?>').innerHTML = '<img src="images/16x16/log_edit.png" width="16" height="16" align="center"> masovni rezultati ispita - <b><?=$ispitocjena?></b> (<?=$korisnik?>, <?=$datum?>)<br />' + document.getElementById('ispitlog<?=$r100[0]?>').innerHTML;
		<?

	// Nema pod oznakom predmeta, pokusacemo ponudu kursa
	} else foreach ($pkovi as $ponudakursa) {
		$q110 = myquery("select UNIX_TIMESTAMP(vrijeme), userid from log where dogadjaj='masovni rezultati ispita za predmet p$ponudakursa' AND ABS(UNIX_TIMESTAMP(vrijeme)-$r100[1])<10");
		if (mysql_num_rows($q110)>0) {
			$datum = date("d.m.Y. H:i:s", mysql_result($q110,0,0));
			$q120 = myquery("select ime,prezime from osoba where id=".mysql_result($q110,0,1));
			if (mysql_num_rows($q120)>0) {
				$korisnik = mysql_result($q120,0,0)." ".mysql_result($q120,0,1);
			} else {
				$korisnik = "/nepoznat korisnik ".mysql_result($q110,0,1)."/";
			}
			?>
			document.getElementById('ispitlog<?=$r100[0]?>').innerHTML = '<img src="images/16x16/log_edit.png" width="16" height="16" align="center"> masovni rezultati ispita - <b><?=$ispitocjena?></b> (<?=$korisnik?>, <?=$datum?>)<br />' + document.getElementById('ispitlog<?=$r100[0]?>').innerHTML;
			<?
		}
	}
	
}*/




?>
</SCRIPT>
<?







}