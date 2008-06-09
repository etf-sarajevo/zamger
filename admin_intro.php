<?

// v2.9.3.1 (2007/03/17) + ne prikazuj EDIT dugme ne-adminima (greška u deklaraciji varijable $admin_predmeta)


function admin_intro() {

global $userid;



# Dobrodošlica

$q1 = myquery("select ime,prezime,siteadmin from nastavnik where id=$userid");
$ime = mysql_result($q1,0,0);
$prezime = mysql_result($q1,0,1);
$siteadmin = mysql_result($q1,0,2);

$stud_spol = substr($ime,strlen($ime)-1);
if ($stud_spol == "a" && $ime != "Vanja" && $ime != "Peđa" && $ime != "Mirza" && $ime != "Feđa") {
	print "<h1>Dobro došla, $ime $prezime!<h1>";
} else {
	print "<h1>Dobro došao, $ime $prezime!</h1>";
}

if ($siteadmin)
	print "<h3>Ti si <a href=\"qwerty.php?sta=siteadmin\">site admin</a>!</h3>";

# Promjena šifre
print "<p><a href=\"qwerty.php?sta=sifra\">Promjena šifre</a></p>\n";

# Gruppe

print '<table border="0" cellspacing="5"><tr>';

if ($siteadmin)
	$q2 = myquery("select id,1 from predmet order by id");
else
	$q2 = myquery("select predmet,admin from nastavnik_predmet where nastavnik=$userid");


# Table header
$nr = mysql_num_rows($q2);
if ($nr>6) $nr=6;
if ($nr>0)
	print '<td colspan="'.($nr*2).'" align="center" bgcolor="#88BB99">Predmeti</td></tr><tr>';

$br=0;
while ($r2 = mysql_fetch_row($q2)) {
	# Spacer
	if ($br>0) print '<td bgcolor="#666666" width="1"></td>';

	print '<td valign="top">';
	$predmet = $r2[0];
	$admin_predmeta = $r2[1];

	# Ispis naziva predmeta
	$q3 = myquery("select predmet.naziv,predmet.aktivan,akademska_godina.naziv from predmet,akademska_godina where predmet.id=$predmet and predmet.akademska_godina=akademska_godina.id");
	if (mysql_num_rows($q3)<0) {
		print "Greška: nepoznat predmet!";
	} else {
		$naziv_predmeta = mysql_result($q3,0,0);
		$predmet_aktivan = mysql_result($q3,0,1);
		$god = mysql_result($q3,0,2);
		
		// Da li je predmet moj?
		$moj=1;
		if ($siteadmin) {
			$q3a = myquery("select count(*) from nastavnik_predmet where nastavnik=$userid and predmet=$predmet");
			if (mysql_result($q3a,0,0)<1) $moj=0;
		}
		if($predmet_aktivan==0 && $moj==0) {
			print "<b><font color=\"#CC8888\">$naziv_predmeta $god</font></b>";
		} else if ($predmet_aktivan==0) {
			print "<b><font color=\"#888888\">$naziv_predmeta $god</font></b>";
		} else if ($moj==0) {
			print "<b><font color=\"#664444\">$naziv_predmeta $god</font></b>";
		} else {
			print "<b>$naziv_predmeta $god</b>";
		}
	}

	# Edit link
	if ($siteadmin || $admin_predmeta) {
		print ' [<b><a href="qwerty.php?sta=predmet&predmet='.$predmet.'"><font color="red">EDIT</font></a></b>]';
	}

	# Provjeri limit na prikazivanje labgrupa
	$limit = array();
	$q3a = myquery("select ogranicenje.labgrupa from ogranicenje, labgrupa where ogranicenje.nastavnik=$userid and ogranicenje.labgrupa=labgrupa.id and labgrupa.predmet=$predmet");
	if (mysql_num_rows($q3a)>0) {
		while ($r3a = mysql_fetch_row($q3a))
			array_push($limit, $r3a[0]);
	}

	# Lab grupe
	print "<ul>";
	$q4 = myquery("select id,naziv from labgrupa where predmet=$predmet");
	if (mysql_num_rows($q4)<1)
		print "<li>Nisu definisane grupe</li>";
	$result = array();
	while ($r4 = mysql_fetch_row($q4)) {
		$result[$r4[0]]=$r4[1];
	}
	natsort($result);
	foreach($result as $gid=>$gname) {
		if (count($limit)==0 || in_array($gid,$limit))
			print "<li><a href=\"qwerty.php?sta=grupa&id=$gid\">$gname</a></li>";
	}
	print "</ul>";

	# Kraj
	print "</td>";

	$br++;
	if ($br==6) {
		$br=0;
		print "</tr><tr>";
	}
}




print '</tr></table>';




}

?>
