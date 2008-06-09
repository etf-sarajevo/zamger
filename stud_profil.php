<?

// v2.9.3.1 (2007/03/15) + stara baza u izmjeni profila
// v2.9.3.2 (2007/03/22) + logiraj promjenu šifre

function stud_profil() {

global $userid;


if ($_POST['akcija']=="izmjena") izmijeni_profil();


$q1=myquery("select ime,prezime,email,brindexa from student where id=$userid");
if (mysql_num_rows($q1)<1) {
	biguglyerror("Greška!");
	return;
}



?>
<center><h2>Izmjena ličnih podataka</h2></center>

<form action="student.php" method="POST">
<input type="hidden" name="sta" value="profil">
<input type="hidden" name="akcija" value="izmjena">
<table border="0">
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
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr>
		<td>Promijeni šifru:</td>
		<td><input type="password" name="pass1" size="15"></td>
	</tr>
	<tr>
		<td>Ponovi šifru:</td>
		<td><input type="password" name="pass2" size="15"></td>
	</tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td>&nbsp;</td><td>
		<input type="submit" value=" Pošalji "> &nbsp;&nbsp;&nbsp;&nbsp;
		<input type="reset" value=" Poništi ">
	</td></tr>
</table>


<?

}

function izmijeni_profil() {

	global $userid;

	$ime = my_escape($_POST['ime']);
	$prezime = my_escape($_POST['prezime']);
	$email = my_escape($_POST['email']);
	$brind = intval($_POST['brind']);
	if ($brind==0) { niceerror("Broj indexa mora biti BROJ :)"); return; }

logthis("Izmjena profila $stud_id ('$ime' '$prezime' '$email' '$brind')");

	$q101 = myquery("update student set ime='$ime', prezime='$prezime', email='$email', brindexa='$brind' where id=$userid");

	// Promjena sifre
	$pass1 = my_escape($_POST['pass1']);
	$pass2 = my_escape($_POST['pass2']);
	
	if (preg_match("/[\w\d]/",$pass1)) {
		if ($pass1 != $pass2) {
			niceerror("Šifre se ne poklapaju!");
		} else {
logthis("Promjena sifre $userid");
			$q102 = myquery("update auth set password='$pass1' where id=$userid");
			print "<p><font color='red'><b>Šifra promijenjena</b></font></p>\n";
		}
	}

	return;

}


?>
