<?

// SARADNIK/IZMJENA_STUDENTA - pregled i izmjena podataka o korisniku

// v3.9.1.0 (2008/02/12) + Preimenovan bivsi admin_student_izmjena
// v3.9.1.1 (2008/02/25) + Student upisan u 0,1,2... grupa
// v3.9.1.2 (2008/03/08) + Nova tabela auth
// v3.9.1.3 (2008/03/21) + Student ne mora biti ni u jednoj labgrupi, auth polja
// v3.9.1.4 (2008/04/14) + Popravljen link za ispis studenta sa predmeta
// v3.9.1.5 (2008/06/16) + Situacija kad student nije ni u jednoj grupi je sada malo jasnija, brisi prisustvo prilikom promjene grupe
// v3.9.1.6 (2008/08/28) + Tabela osoba umjesto auth
// v3.9.1.7 (2008/09/17) + Omogucena promjena grupe ako student nije niti u jednoj grupi (bug 24)
// v3.9.1.8 (2008/09/23) + Popravljen link na studentska/osobe
// v3.9.1.9 (2008/10/03) + Akcija izmjena prebacena na genform() radi sigurnosnih aspekata istog
// v3.9.1.10 (2008/10/05) + Broj indexa ne mora biti broj
// v3.9.1.11 (2008/11/17) + Samo site admin moze ispisati studenta sa predmeta (privremeno rjesenje)
// v4.0.0.0 (2009/02/19) + Release
// v4.0.9.1 (2009/03/24) + Prebacena polja ects i tippredmeta iz tabele ponudakursa u tabelu predmet



function saradnik_izmjena_studenta() {

global $userid,$user_siteadmin,$user_studentska;


require("lib/manip.php"); // radi ispisa studenta sa predmeta


?>
<body topmargin="0" leftmargin="0" bottommargin="0" rightmargin="0" bgcolor="#FFFFFF">
<?

$stud_id=intval($_REQUEST['student']); 
$predmet_id=intval($_REQUEST['predmet']); 


// Da li neko spoofa predmet/studenta?
$q10 = myquery("select sl.labgrupa from student_labgrupa as sl,labgrupa where sl.student=$stud_id and sl.labgrupa=labgrupa.id and labgrupa.predmet=$predmet_id");
if (mysql_num_rows($q10)>0) {
	$labgrupa = mysql_result($q10,0,0);
} else {
	$labgrupa=0;
}


// Limit...
$q20 = myquery("select ogranicenje.labgrupa from ogranicenje, labgrupa where ogranicenje.nastavnik=$userid and ogranicenje.labgrupa=labgrupa.id and labgrupa.predmet=$predmet_id");
if (mysql_num_rows($q20)>0) {
	$nasao=0;
	while ($r20 = mysql_fetch_row($q20)) {
		if ($r20[0] == $labgrupa) { $nasao=1; break; }
	}
	if ($nasao == 0) {
		zamgerlog("ogranicenje (student u$stud_id predmet p$predmet_id)",3);
		niceerror("Nemate pravo pristupa labgrupi u kojoj se nalazi ovaj student");
		return;
	}
}


// Onemogući izmjenu ako prijavljeni korisnik nije nastavnik na predmetu ili siteadmin
$q30=myquery("select count(*) from nastavnik_predmet where nastavnik=$userid and predmet=$predmet_id");
$izmjena_moguca = 0;
if ((mysql_result($q30,0,0)>0) || $user_siteadmin || $user_studentska) { 
	$izmjena_moguca = 1;
}

// TODO: prikaži read-only podatke o studentu
if ($izmjena_moguca ==0) {
	zamgerlog("nije moguca izmjena (student u$stud_id predmet p$predmet_id)",3);
	niceerror("Nemate pravo pristupa ovom studentu!");
	return;
}


// Poziv funkcije za izmjenu

if ($_POST['akcija']=="izmjena" && $izmjena_moguca==1 && check_csrf_token()) {
	$labgrupa = _izmijeni_profil($stud_id,$predmet_id);
}


// Ispis studenta sa predmeta

if ($_GET['akcija'] == "ispis" && $user_siteadmin) {
	ispis_studenta_sa_predmeta($stud_id,$predmet_id);
	zamgerlog("student ispisan sa predmeta (student u$stud_id predmet p$predmet_id)",4); // nivo 4: audit
	nicemessage("Studen ispisan sa predmeta.");
	return;
}


// Podaci o studentu...

$q140=myquery("select ime,prezime,email,brindexa from osoba where id=$stud_id");
if (mysql_num_rows($q140)<1) {
	zamgerlog("nepostojeci student (student $stud_id)",3);
	biguglyerror("Nema studenta $stud_id");
	return;
}

$q160=myquery("select p.naziv from predmet as p, ponudakursa as pk where pk.id=$predmet_id and pk.predmet=p.id");
if (mysql_num_rows($q160)<1) {
	zamgerlog("nepostojeci predmet (predmet $predmet_id)",3);
	biguglyerror("Nema predmeta $predmet_id");
	return;
}
$predmet = mysql_result($q160,0,0);


// Studij koji student trenutno sluša

$q170=myquery("select id,naziv from akademska_godina order by id desc limit 1");
$ag=mysql_result($q170,0,0);
$agnaziv=mysql_result($q170,0,1);

$q180=myquery("select s.naziv from student_studij as ss, studij as s where s.id=ss.studij and ss.akademska_godina=$ag and ss.student=$stud_id");
if (mysql_num_rows($q180)<1)
	$studij="Nije upisan niti na jedan studij! ($agnaziv)";
else
	$studij=mysql_result($q180,0,0)." ($agnaziv)";


?>
<center><h2>Izmjena ličnih podataka</h2></center>

<?=genform("POST")?>
<input type="hidden" name="akcija" value="izmjena">
<table border="0" width="100%">
	<tr>
		<td>DB ID:</td>
		<td><b><?=$stud_id?></b></td>
	</tr>
	<tr>
		<td>Ime:</td>
		<td><input type="text" name="ime" size="20" value="<?=mysql_result($q140,0,0)?>"></td>
	</tr>
	<tr>
		<td>Prezime:</td>
		<td><input type="text" name="prezime" size="20" value="<?=mysql_result($q140,0,1)?>"></td>
	</tr>
	<tr>
		<td>Kontakt e-mail:</td>
		<td><input type="text" name="email" size="20" value="<?=mysql_result($q140,0,2)?>"></td>
	</tr>
	<tr>
		<td>Broj indexa:</td>
		<td><input type="text" name="brind" size="10" value="<?=mysql_result($q140,0,3)?>"></td>
	</tr>
	<tr>
		<td>Upisan na:</td>
		<td><b><?=$studij?></b></td>
	</tr>
<?


// Labgrupe

$q150=myquery("select id,naziv from labgrupa where predmet=$predmet_id order by naziv");

if (mysql_num_rows($q150)>0) {

	$q155 = myquery("select l.id, l.naziv from labgrupa as l, student_labgrupa as sl where l.predmet=$predmet_id and sl.labgrupa=l.id and sl.student=$stud_id");
	if (mysql_num_rows($q155)<=1) {
		if (mysql_num_rows($q155)==0) $nijedna=" SELECTED"; else $nijedna="";
?>
	<tr>
		<td>Upiši u grupu:</td>
		<td><select name="grupa"><option value="0"<?=$nijedna?>>-- Nije ni u jednoj grupi --</option>
			<?
			while ($r150 = mysql_fetch_row($q150)) {
				if ($r150[0]==mysql_result($q155,0,0)) 
					$value="SELECTED"; else $value="";
				?><option value="<?=$r150[0]?>" <?=$value?>><?=$r150[1]?></option><?
			}
		?></select></td>
	</tr>
<?
//	} else if (mysql_num_rows($q155)==1) {
/*?>
	<tr>
		<td>Prebaci u grupu:</td>
		<td><select name="grupa"><option value="0">-- Nije ni u jednoj grupi --</option>
			<?
			while ($r150 = mysql_fetch_row($q150)) {
				if ($r150[0]==mysql_result($q155,0,0)) 
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
			while ($r155 = mysql_fetch_row($q155)) {
				print $r155[1];
				print " <a href=\"?sta=izmjena_studenta&akcija=ispis_iz_grupe&grupa=$r155[0]\">(ispiši)</a><br/>\n";
			}
		?></td>
	</tr>
<?
	}
}

if ($user_siteadmin) {
	?>
	<tr><td colspan="2"><a href="index.php?sta=saradnik/izmjena_studenta&student=<?=$stud_id?>&predmet=<?=$predmet_id?>&akcija=ispis">Ispiši studenta sa predmeta:<br/><b><?=$predmet?></b></a></td></tr>
	<?
}

if ($user_siteadmin) {
	// Linkovi za site admina
	?>
	<tr><td colspan="2"><a href="index.php?c=B&sta=studentska/osobe&akcija=edit&osoba=<?=$stud_id?>" target="openerwindow" onClick="if (document.images) opener.name='openerwindow'">Detaljnije o studentu</a></td></tr>
	<tr><td colspan="2"><a href="index.php?c=S&su=<?=$stud_id?>" target="openerwindow" onClick="if (document.images) opener.name='openerwindow'">Prijavi se kao student</a></td></tr>
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



function _izmijeni_profil($stud_id,$predmet_id) {
	$ime = my_escape($_POST['ime']);
	$prezime = my_escape($_POST['prezime']);
	$email = my_escape($_POST['email']);
	$brind = my_escape($_POST['brind']);
	if ($brind==0) { 
		zamgerlog("broj indexa nije broj ($brind)",3);
		niceerror("Broj indexa mora biti BROJ :)"); 
		return; 
	}

	$grupa = intval($_POST['grupa']);
	$q200 = myquery("select count(*) from labgrupa where id=$grupa");
	if (mysql_result($q200,0,0)<1) { 
		zamgerlog("nepoznata grupa ($grupa)",3);
		niceerror("Nepoznata grupa."); 
		return; 
	}

	$q210 = myquery("update osoba set ime='$ime', prezime='$prezime', email='$email', brindexa='$brind' where id=$stud_id");

	// Update grupe - prvo obrisati staru pa ubaciti novu
	$q220 = myquery("select sl.labgrupa from student_labgrupa as sl,labgrupa where sl.student=$stud_id and sl.labgrupa=labgrupa.id and labgrupa.predmet=$predmet_id");
	$vec_upisan_u_grupu = 0;
	while ($r220 = mysql_fetch_row($q220)) {
		if ($r220[0]==$grupa) {
			$vec_upisan_u_grupu = 1;
		} else {
			$q230 = myquery("delete from student_labgrupa where student=$stud_id and labgrupa=$r220[0]");

			// Brisanje prisustva za staru grupu
			$q235 = myquery("delete from prisustvo where student=$stud_id and cas=ANY(select id from cas where labgrupa=$r220[0])");
		}
	}
	
	if ($vec_upisan_u_grupu==0) {
		$q240 = myquery("insert into student_labgrupa set student=$stud_id, labgrupa=$grupa");

		// Update komponente za prisustvo
		$q250 = myquery("select tpk.komponenta from tippredmeta_komponenta as tpk, ponudakursa as pk, komponenta as k, predmet as p where pk.id=$predmet_id and pk.predmet=p.id and p.tippredmeta=tpk.tippredmeta and tpk.komponenta=k.id and k.tipkomponente=3"); // tipkomponente 3 = prisustvo
		while ($r250 = mysql_fetch_row($q250))
			update_komponente($stud_id, $predmet_id, $r250[0]);
	}
	zamgerlog("update profila i labgrupe za studenta u$stud_id",2); // nivo 2: edit

	return $grupa;
}


?>
