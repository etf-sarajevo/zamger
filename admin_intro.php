<?

function admin_intro() {

global $login;



# Dobrodošlica

$q1 = myquery("select ime,prezime from admin_login where login='$login'");
$ime = mysql_result($q1,0,0);
$prezime = mysql_result($q1,0,1);
$stud_spol = substr($ime,strlen($ime)-1);
if ($stud_spol == "a" && $ime != "Vanja" && $ime != "Peđa" && $ime != "Mirza" && $ime != "Feđa") {
	print "<h1>Dobro došla, $ime $prezime!<h1>";
} else {
	print "<h1>Dobro došao, $ime $prezime!</h1>";
}

print "<h2>Izaberi grupu:</h2>";

print "<ul>";
$q1 = myquery("select * from grupe order by naziv");
while ($r1 = mysql_fetch_row($q1)) {
	print '<li><a href="qwerty.php?sta=grupa&id='.$r1[0].'">'.$r1[1].'</a></li>';
}
print "</ul>";


}

?>
