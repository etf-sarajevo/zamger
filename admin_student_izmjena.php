<?


// v2.9.3.1 (2007/03/17) + onemogući izmjenu ako nije admin predmeta
// v2.9.3.2 (2007/03/19) + ali omogući ako je siteadmin; dodatna zaštita od spoofanja
// v3.0.0.0 (2007/04/09) + Release
// v3.0.1.0 (2007/06/12) + Release
// v3.0.1.1 (2007/09/11) + Pristup kao siteadmin nije radio


function admin_student_izmjena() {

global $userid;

?>
<html>
<head>
	<title>Podaci o studentu</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="css/style.css" rel="stylesheet" type="text/css" />
</head>
<body topmargin="0" leftmargin="0" bottommargin="0" rightmargin="0" bgcolor="#FFFFFF">
<?

$stud_id=intval($_GET['student']); 
if ($stud_id<1) $stud_id=intval($_POST['student']); 

$predmet_id=intval($_GET['predmet']); 
if ($predmet_id<1) $predmet_id=intval($_POST['predmet']);


// Da li neko spoofa predmet/studenta?
$q100 = myquery("select sl.labgrupa from student_labgrupa as sl,labgrupa where sl.student=$stud_id and sl.labgrupa=labgrupa.id and labgrupa.predmet=$predmet_id");
if (mysql_num_rows($q100)<1) {
	niceerror("Nemate pravo pristupa ovom studentu!");
	return;
}
$labgrupa = mysql_result($q100,0,0);


// Limit...
$q101 = myquery("select ogranicenje.labgrupa from ogranicenje, labgrupa where ogranicenje.nastavnik=$userid and ogranicenje.labgrupa=labgrupa.id and labgrupa.predmet=$predmet_id");
if (mysql_num_rows($q101)>0) {
	$nasao=0;
	while ($r101 = mysql_fetch_row($q101)) {
		if ($r101[0] == $labgrupa) { $nasao=1; break; }
	}
	if ($nasao == 0) {
		niceerror("Nemate pravo pristupa ovom studentu!");
		return;
	}
}


// Onemogući izmjenu ako prijavljeni korisnik nije admin predmeta
$q102=myquery("select admin from nastavnik_predmet where nastavnik=$userid and predmet=$predmet_id");
$izmjena_moguca = 0;
if (mysql_num_rows($q102)>0 && mysql_result($q102,0,0)==1) {
	$izmjena_moguca = 1;
} else {
	$q103=myquery("select siteadmin from nastavnik where id=$userid");
	if (mysql_num_rows($q103)>0 && mysql_result($q103,0,0)==2)
		$izmjena_moguca = 1;
}

if ($izmjena_moguca ==0) {
	niceerror("Nemate pravo pristupa ovom studentu!");
	return;
}

// Poziv funkcije za izmjenu
if ($_POST['akcija']=="izmjena" && $izmjena_moguca==1) izmijeni_profil($stud_id,$predmet_id);


// Podaci o studentu...
$q1=myquery("select ime,prezime,email,brindexa from student where id=$stud_id");
if (mysql_num_rows($q1)<1) {
	biguglyerror("Nema studenta $stud_id");
	return;
}

$q2=myquery("select id,naziv from labgrupa where predmet=$predmet_id order by naziv");

$q3=myquery("select student_labgrupa.labgrupa from student_labgrupa,labgrupa where student_labgrupa.student=$stud_id and student_labgrupa.labgrupa=labgrupa.id and labgrupa.predmet=$predmet_id");

$q4=myquery("select naziv from predmet where id=$predmet_id");
if (mysql_num_rows($q4)<1) {
	biguglyerror("Nema predmeta $predmet_id");
	return;
}
$predmet = mysql_result($q4,0,0);


?>
<center><h2>Izmjena ličnih podataka</h2></center>

<form action="qwerty.php" method="POST">
<input type="hidden" name="sta" value="student-izmjena">
<input type="hidden" name="akcija" value="izmjena">
<input type="hidden" name="student" value="<?=$stud_id?>">
<input type="hidden" name="predmet" value="<?=$predmet_id?>">
<table border="0">
	<tr>
		<td>DB ID:</td>
		<td><b><?=$stud_id?></b></td>
	</tr>
	<tr>
		<td>Ime:</td>
		<td><input type="text" name="ime" size="20" value="<?=mysql_result($q1,0,0)?>"></td>
	</tr>
	<tr>
		<td>Prezime:</td>
		<td><input type="text" name="prezime" size="20" value="<?=mysql_result($q1,0,1)?>"></td>
	</tr>
	<tr>
		<td>E-mail:</td>
		<td><input type="text" name="email" size="30" value="<?=mysql_result($q1,0,2)?>"></td>
	</tr>
	<tr>
		<td>Broj indexa:</td>
		<td><input type="text" name="brind" size="10" value="<?=mysql_result($q1,0,3)?>"></td>
	</tr>
	<tr>
		<td>Predmet:</td>
		<td><b><?=$predmet?></b></td>
	</tr>
	<tr>
		<td>Grupa:</td>
		<td><select name="grupa"><?
			$gr = $labgrupa;
			while ($r2 = mysql_fetch_row($q2)) {
				print '<option value="'.$r2[0].'"';
				if ($r2[0]==$gr) print ' SELECTED';
				print '>'.$r2[1].'</option>';
			}
		?></select></td>
	</tr>
<?

if (mysql_result($q103,0,0)==2) {
	?>
	<tr><td colspan="2"><a href="qwerty.php?sta=nihada&akcija=edit&student=<?=$stud_id?>">Detaljnije o studentu</a></td></tr>
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

function izmijeni_profil($stud_id,$predmet_id) {


	$ime = my_escape($_POST['ime']);
	$prezime = my_escape($_POST['prezime']);
	$email = my_escape($_POST['email']);
	$brind = intval($_POST['brind']);
	if ($brind==0) { niceerror("Broj indexa mora biti BROJ :)"); return; }

	$grupa = intval($_POST['grupa']);
	$q100 = myquery("select count(*) from labgrupa where id=$grupa");
	if (mysql_result($q100,0,0)<1) { niceerror("Nepoznata grupa."); return; }

	$q101 = myquery("update student set ime='$ime', prezime='$prezime', email='$email', brindexa='$brind' where id=$stud_id");

	// Update grupe - prvo obrisati staru pa ubaciti novu
	$q102 = myquery("select sl.labgrupa from student_labgrupa as sl,labgrupa where sl.student=$stud_id and sl.labgrupa=labgrupa.id and labgrupa.predmet=$predmet_id");
	while ($r102 = mysql_fetch_row($q102)) {
		$q103 = myquery("delete from student_labgrupa where student=$stud_id and labgrupa=$r102[0]");
	}
	
	$q104 = myquery("insert into student_labgrupa set student=$stud_id, labgrupa=$grupa");
	return;
}


?>
