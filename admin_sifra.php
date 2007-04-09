<?

function admin_sifra() {

global $userid;

if ($_POST['akcija']=="promjena") {
	if ($_POST['nova'] != $_POST['ponovo']) {
		niceerror("Šifre se razlikuju!");
	} else {
		$q1 = myquery("update auth set password='".$_POST['nova']."' where id=$userid");
		print "<p>Promjena uspješno izvršena.</p>";
	}

} else {

?>
<form action="qwerty.php" method="POST">
<input type="hidden" name="sta" value="sifra">
<input type="hidden" name="akcija" value="promjena">
Unesite šifru: <input type="password" size="20" name="nova"><br/>
Ponovite šifru: <input type="password" size="20" name="ponovo"><br/>
<input type="submit" value=" Pošalji ">
</form>
<?

}

?>
<a href="qwerty.php">Nazad</a>
<?


}


?>