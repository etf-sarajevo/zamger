<?

// SARADNIK/IZMJENA_STUDENTA - pregled i izmjena podataka o korisniku



// TODO: Posto se prakticno sve akcije ovdje sada rade kroz studentsku sluzbu (osim promjene grupe), ovaj modul ce biti zamijenjen jednim readonly prozorom, a promjena grupe ce biti usavrsena


function saradnik_izmjena_studenta() {

print "Ne radi";
return;

global $userid,$user_siteadmin,$user_studentska;


require_once("lib/student_predmet.php"); // update_komponente


?>
<body topmargin="0" leftmargin="0" bottommargin="0" rightmargin="0" bgcolor="#FFFFFF">
<?

$student=intval($_REQUEST['student']); 
$predmet=intval($_REQUEST['predmet']); 
$ag=intval($_REQUEST['ag']); 


// Necemo provjeravati prava pristupa jer je osnovna provjera vec napravljena kroz registry, a prikaz readonly podataka nastavniku koji nije angazovan na predmetu je IMHO ok


// Podaci o studentu...

$q140=db_query("select ime,prezime,brindexa from osoba where id=$student");
if (db_num_rows($q140)<1) {
	zamgerlog("nepostojeci student (student $student)",3);
	biguglyerror("Nepoznat student");
	return;
}

// Podaci o predmetu

$q160=db_query("select naziv from predmet where id=$predmet");
if (db_num_rows($q160)<1) {
	zamgerlog("nepostojeci predmet (predmet $predmet)",3);
	biguglyerror("Nepoznat predmet");
	return;
}
$naziv_predmeta = db_result($q160,0,0);


// Aktuelna akademska godina

$q170=db_query("select naziv from akademska_godina where id=$ag");
if (db_num_rows($q170)<1) {
	zamgerlog("nepostojeca ag $ag",3);
	biguglyerror("Nepoznat predmet");
	return;
}
$agnaziv=db_result($q170,0,0);


// Studij koji student trenutno sluša

$q180=db_query("select s.naziv from student_studij as ss, studij as s where s.id=ss.studij and ss.akademska_godina=$ag and ss.student=$student");
if (db_num_rows($q180)<1)
	$studij="Nije upisan niti na jedan studij! ($agnaziv)";
else
	$studij=db_result($q180,0,0)." ($agnaziv)";


// Provjera ogranicenja

/*$q10 = db_query("select sl.labgrupa from student_labgrupa as sl,labgrupa where sl.student=$student and sl.labgrupa=labgrupa.id and labgrupa.predmet=$predmet");
if (db_num_rows($q10)>0) {
	$labgrupa = db_result($q10,0,0);
} else {
	$labgrupa=0;
}


// Limit...
$q20 = db_query("select ogranicenje.labgrupa from ogranicenje, labgrupa where ogranicenje.nastavnik=$userid and ogranicenje.labgrupa=labgrupa.id and labgrupa.predmet=$predmet_id");
if (db_num_rows($q20)>0) {
	$nasao=0;
	while ($r20 = db_fetch_row($q20)) {
		if ($r20[0] == $labgrupa) { $nasao=1; break; }
	}
	if ($nasao == 0) {
		zamgerlog("ogranicenje (student u$stud_id predmet pp$predmet_id)",3);
		niceerror("Nemate pravo pristupa labgrupi u kojoj se nalazi ovaj student");
		return;
	}
}*/


// Onemogući izmjenu ako prijavljeni korisnik nije nastavnik na predmetu ili siteadmin
$izmjena_moguca = 0;
if ($user_siteadmin || $user_studentska) { 
	$izmjena_moguca = 1;
} else {
	$q30=db_query("select count(*) from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
	if (db_result($q30,0,0)>0) { 
		$izmjena_moguca = 1;
	}
}

// TODO: prikaži read-only podatke o studentu
if ($izmjena_moguca ==0) {
	zamgerlog("saradnik/izmjena_studenta: nije moguca izmjena (student u$student predmet pp$predmet)",3);
	niceerror("Nemate pravo pristupa ovom studentu!");
	return;
}


// Poziv funkcije za izmjenu

if ($_POST['akcija']=="izmjena" && $izmjena_moguca==1 && check_csrf_token()) {
	$labgrupa = _izmijeni_profil($student,$predmet);
}


// Ispis studenta sa predmeta

if ($_GET['akcija'] == "ispis" && $user_siteadmin) {
	ispis_studenta_sa_predmeta($student,$predmet, $ag);
	zamgerlog("student ispisan sa predmeta (student u$student predmet pp$predmet)",4); // nivo 4: audit
	nicemessage("Studen ispisan sa predmeta.");
	return;
}




?>
<center><h2>Izmjena ličnih podataka</h2></center>

<?=genform("POST")?>
<input type="hidden" name="akcija" value="izmjena">
<table border="0" width="100%">
	<tr>
		<td>DB ID:</td>
		<td><b><?=$student?></b></td>
	</tr>
	<tr>
		<td>Ime:</td>
		<td><input type="text" name="ime" size="20" value="<?=db_result($q140,0,0)?>"></td>
	</tr>
	<tr>
		<td>Prezime:</td>
		<td><input type="text" name="prezime" size="20" value="<?=db_result($q140,0,1)?>"></td>
	</tr>
	<tr>
		<td>Broj indexa:</td>
		<td><input type="text" name="brind" size="10" value="<?=db_result($q140,0,2)?>"></td>
	</tr>
	<tr>
		<td>Upisan na:</td>
		<td><b><?=$studij?></b></td>
	</tr>
<?


// Labgrupe

$q150=db_query("select id,naziv from labgrupa where predmet=$predmet and akademska_godina=$ag and virtualna=0 order by naziv");

if (db_num_rows($q150)>0) {

	$q155 = db_query("select l.id, l.naziv from labgrupa as l, student_labgrupa as sl where l.predmet=$predmet and l.akademska_godina=$ag and sl.labgrupa=l.id and sl.student=$student and l.virtualna=0");
	if (db_num_rows($q155)<=1) {
		if (db_num_rows($q155)==0) $nijedna=" SELECTED"; else $nijedna="";
?>
	<tr>
		<td>Upiši u grupu:</td>
		<td><select name="grupa"><option value="0"<?=$nijedna?>>-- Nije ni u jednoj grupi --</option>
			<?
			while ($r150 = db_fetch_row($q150)) {
				if ($r150[0]==db_result($q155,0,0)) 
					$value="SELECTED"; else $value="";
				?><option value="<?=$r150[0]?>" <?=$value?>><?=$r150[1]?></option><?
			}
		?></select></td>
	</tr>
<?
//	} else if (db_num_rows($q155)==1) {
/*?>
	<tr>
		<td>Prebaci u grupu:</td>
		<td><select name="grupa"><option value="0">-- Nije ni u jednoj grupi --</option>
			<?
			while ($r150 = db_fetch_row($q150)) {
				if ($r150[0]==db_result($q155,0,0)) 
					$value="SELECTED"; else $value="";
				?><option value="<?=$r150[0]?>" <?=$value?>><?=$r150[1]?></option><?
			}
		?></select></td>
	</tr>
<?*/
	} else {
?>
	<tr>
		<td>Grupe:</td>
		<td><?
			while ($r155 = db_fetch_row($q155)) {
				print $r155[1];
				// Ovo ispod nije implementirano!?!
				print " <a href=\"?sta=saradnik/izmjena_studenta&akcija=ispis_iz_grupe&grupa=$r155[0]&student=$student&predmet=$predmet\">(ispiši)</a><br/>\n";
			}
		?></td>
	</tr>
<?
	}
}

if ($user_siteadmin) {
	?>
	<tr><td colspan="2"><a href="index.php?sta=saradnik/izmjena_studenta&student=<?=$student?>&predmet=<?=$predmet?>&ag=<?=$ag?>&akcija=ispis">Ispiši studenta sa predmeta:<br/><b><?=$naziv_predmeta?></b></a></td></tr>
	<?
}

if ($user_siteadmin) {
	// Linkovi za site admina
	?>
	<tr><td colspan="2"><a href="index.php?c=B&sta=studentska/osobe&akcija=edit&osoba=<?=$student?>" target="openerwindow" onClick="if (document.images) opener.name='openerwindow'">Detaljnije o studentu</a></td></tr>
	<tr><td colspan="2"><a href="index.php?c=S&su=<?=$student?>" target="openerwindow" onClick="if (document.images) opener.name='openerwindow'">Prijavi se kao student</a></td></tr>
	<?
}

?>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td>&nbsp;</td><td>
		<input type="submit" value=" Pošalji " <? if ($izmjena_moguca != 1) print "disabled"; ?>> &nbsp;&nbsp;&nbsp;&nbsp;
		<input type="reset" value=" Poništi ">
	</td></tr>
</table>


<?

}



function _izmijeni_profil($student,$predmet) {
	$ime = db_escape($_POST['ime']);
	$prezime = db_escape($_POST['prezime']);
	$brind = db_escape($_POST['brind']);
	if ($brind==0) {
		// Obsolete?
		zamgerlog("broj indexa nije broj ($brind)",3);
		niceerror("Broj indexa mora biti BROJ :)"); 
		return; 
	}

	$grupa = intval($_POST['grupa']);
	$q200 = db_query("select count(*) from labgrupa where id=$grupa and predmet=$predmet");
	if (db_result($q200,0,0)<1) { 
		zamgerlog("nepoznata grupa ($grupa) ili nije na predmetu pp$predmet",3);
		niceerror("Nepoznata grupa."); 
		return; 
	}

	$q210 = db_query("update osoba set ime='$ime', prezime='$prezime', brindexa='$brind' where id=$student");

	// Update grupe - prvo obrisati staru pa ubaciti novu
	$q220 = db_query("select sl.labgrupa from student_labgrupa as sl,labgrupa where sl.student=$student and sl.labgrupa=labgrupa.id and labgrupa.predmet=$predmet and labgrupa.virtualna=0");
	$vec_upisan_u_grupu = 0;
	while ($r220 = db_fetch_row($q220)) {
		if ($r220[0]==$grupa) {
			$vec_upisan_u_grupu = 1;
		} else {
			$q230 = db_query("delete from student_labgrupa where student=$student and labgrupa=$r220[0]");

			// Brisanje prisustva za staru grupu
			$q235 = db_query("delete from prisustvo where student=$student and cas=ANY(select id from cas where labgrupa=$r220[0])");
		}
	}
	
	if ($vec_upisan_u_grupu==0) {
		$q240 = db_query("insert into student_labgrupa set student=$student, labgrupa=$grupa");

		// Update komponente za prisustvo
		$q250 = db_query("select tpk.komponenta from tippredmeta_komponenta as tpk, ponudakursa as pk, komponenta as k, akademska_godina_predmet as agp where pk.id=$predmet and pk.predmet=agp.predmet and agp.akademska_godina=$ag and agp.tippredmeta=tpk.tippredmeta and tpk.komponenta=k.id and k.tipkomponente=3"); // tipkomponente 3 = prisustvo
		// Ovo za sada ne radi jer update_komponente trazi ponudukursa sto mi ovdje ne mozemo znati
/*		while ($r250 = db_fetch_row($q250))
			update_komponente($student, $predmet, $r250[0]);*/
	}
	zamgerlog("update profila i labgrupe za studenta u$student",2); // nivo 2: edit

	return $grupa;
}


?>
