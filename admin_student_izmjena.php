<?

function admin_student_izmjena() {

?>
<html>
<head>
	<title>Podaci o studentu</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="css/style.css" rel="stylesheet" type="text/css" />
</head>
<body topmargin="0" leftmargin="0" bottommargin="0" rightmargin="0" bgcolor="#FFFFFF">
<?

$stud_id=$_GET['student']; if ($stud_id<1) $stud_id=$_POST['student']; 

if ($_POST['akcija']=="izmjena") izmijeni_profil($stud_id);


$q1=myquery("select ime,prezime,email,brindexa,grupa from studenti where id=$stud_id");
if (mysql_num_rows($q1)<1) {
	biguglyerror("Greška!");
	return;
}

$q2=myquery("select id,naziv from grupe order by naziv");


?>
<center><h2>Izmjena ličnih podataka</h2></center>

<form action="qwerty.php" method="POST">
<input type="hidden" name="sta" value="student-izmjena">
<input type="hidden" name="akcija" value="izmjena">
<input type="hidden" name="student" value="<?=$stud_id?>">
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
		<td>Grupa:</td>
		<td><select name="grupa"><?
			$gr = mysql_result($q1,0,4);
			while ($r2 = mysql_fetch_row($q2)) {
				print '<option value="'.$r2[0].'"';
				if ($r2[0]==$gr) print ' SELECTED';
				print '>'.$r2[1].'</option>';
			}
		?></select></td>
	</tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td>&nbsp;</td><td>
		<input type="submit" value=" Pošalji "> &nbsp;&nbsp;&nbsp;&nbsp;
		<input type="reset" value=" Poništi ">
	</td></tr>
</table>


<?

}

function izmijeni_profil($stud_id) {


	$ime = my_escape($_POST['ime']);
	$prezime = my_escape($_POST['prezime']);
	$email = my_escape($_POST['email']);
	$brind = intval($_POST['brind']);
	if ($brind==0) { niceerror("Broj indexa mora biti BROJ :)"); return; }

	$grupa = intval($_POST['grupa']);
	$q100 = myquery("select count(*) from grupe where id=$grupa");
	if (mysql_result($q100,0,0)<1) { niceerror("Nepoznata grupa."); return; }

	$q101 = myquery("update studenti set ime='$ime', prezime='$prezime', email='$email', brindexa='$brind', grupa=$grupa where id=$stud_id");
	return;

}


?>
