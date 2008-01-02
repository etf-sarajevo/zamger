<?

// v3.0.1.0 (2007/06/12) + Release
// v3.0.1.1 (2007/12/04) + Upit za spisak labgrupa je koristio staru strukturu baze

function stud_labgrupa() {

global $userid, $labgrupa;


$q1 = myquery("select p.naziv,l.id,ag.naziv from predmet as p,student_labgrupa as sl,labgrupa as l, ponudakursa as pk, akademska_godina as ag where sl.student=$userid and sl.labgrupa=l.id and l.predmet=pk.id and pk.aktivan=1 and pk.predmet=p.id and pk.akademska_godina=ag.id");

if (mysql_num_rows($q1)==1) {
	$labgrupa=mysql_result($q1,0,1);
	return;
}

elseif (mysql_num_rows($q1)>1) {
	print "Izaberite predmet:\n<ul>";
	while ($r1 = mysql_fetch_row($q1)) {
		print "<li><a href=\"".genuri()."&labgrupa=$r1[1]\">$r1[0] ($r1[2])</a></li>";
	}
	print "</ul>\n";
}

else {
	print "Niste trenutno upisani ni u jedan aktivan predmet. Ako je ovo greška, kontaktirajte tutora!";
}


}

?>