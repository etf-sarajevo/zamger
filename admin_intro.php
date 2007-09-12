<?

// v2.9.3.1 (2007/03/17) + ne prikazuj EDIT dugme ne-adminima (greška u deklaraciji varijable $admin_predmeta)
// v3.0.0.0 (2007/04/09) + Release
// v3.0.1.0 (2007/06/12) + Release
// v3.0.1.1 (2007/09/10) + Grupiši predmete po akademskim godinama
// v3.0.1.2 (2007/09/11) + Novi modul "Nihada" za unos i pristup podataka o studentima, nastavnicima, loginima itd., reorganizacija admin linkova


function admin_intro() {

global $userid;



// Dobrodošlica

$q1 = myquery("select ime,prezime,siteadmin from nastavnik where id=$userid");
$ime = mysql_result($q1,0,0);
$prezime = mysql_result($q1,0,1);
$siteadmin = mysql_result($q1,0,2);

$stud_spol = substr($ime,strlen($ime)-1);
if ($stud_spol == "a" && $ime != "Vanja" && $ime != "Peđa" && $ime != "Mirza" && $ime != "Feđa" && $ime != "Saša" && $ime != "Alija" && $ime != "Mustafa") {
	print "<h1>Dobro došla, $ime $prezime!<h1>";
} else {
	print "<h1>Dobro došao, $ime $prezime!</h1>";
}


// Administratorski moduli

print "<p>";
if ($siteadmin==2)
	print "<a href=\"qwerty.php?sta=siteadmin\">Site admin</a> * ";
if ($siteadmin==2 || $siteadmin==1)
	print "<a href=\"qwerty.php?sta=nihada\">Studenti, nastavnici</a> * ";
print "<a href=\"qwerty.php?sta=sifra\">Promjena šifre</a></p>\n";



// Spisak grupa po predmetima, predmeti po akademskoj godini
print '<table border="0" cellspacing="5"><tr>';

if ($_REQUEST['sve']) 
	$q1a = myquery("select id,naziv from akademska_godina order by naziv desc");
else
	$q1a = myquery("select id,naziv from akademska_godina order by naziv desc limit 1");


while ($r1a = mysql_fetch_row($q1a)) {
	if ($siteadmin)
		$q2 = myquery("select id,1 from predmet where akademska_godina=$r1a[0] order by id");
	else
		$q2 = myquery("select predmet,admin from nastavnik_predmet where nastavnik=$userid and akademska_godina=$r1a[0]");

	$nr = mysql_num_rows($q2);
	if ($nr==0) continue; // skip to next

	if ($nr>6) $nr=6;
	print '<td colspan="'.($nr*2).'" align="center" bgcolor="#88BB99">Predmeti ('.$r1a[1].')</td></tr><tr>';

	$br=0;
	while ($r2 = mysql_fetch_row($q2)) {
		# Spacer
		if ($br>0) print '<td bgcolor="#666666" width="1"></td>';
	
		print '<td valign="top">';
		$predmet = $r2[0];
		$admin_predmeta = $r2[1];
	
		# Ispis naziva predmeta
		$q3 = myquery("select predmet.naziv,predmet.aktivan from predmet where predmet.id=$predmet");
		if (mysql_num_rows($q3)<0) {
			print "Greška: nepoznat predmet!";
		} else {
			$naziv_predmeta = mysql_result($q3,0,0);
			$predmet_aktivan = mysql_result($q3,0,1);
			
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
				print "<b>$naziv_predmeta</b>";
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
	print '</tr><tr>';
}



print '</tr></table>';

if (!$_REQUEST['sve']) print '<a href="'.genuri().'&sve=1">Prikaži ranije akademske godine</a>';



}

?>
