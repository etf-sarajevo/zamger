<?

function stud_labgrupa() {

global $userid, $labgrupa;


$q1 = myquery("select predmet.naziv,labgrupa.id from predmet,student_labgrupa as sl,labgrupa where sl.student=$userid and sl.labgrupa=labgrupa.id and labgrupa.predmet=predmet.id and predmet.aktivan=1");

if (mysql_num_rows($q1)==1) {
	$labgrupa=mysql_result($q1,0,1);
	return;
}

elseif (mysql_num_rows($q1)>1) {
	print "Izaberite predmet:\n<ul>";
	while ($r1 = mysql_fetch_row($q1)) {
		print "<li><a href=\"".genuri()."&labgrupa=$r1[1]\">$r1[0]</a></li>";
	}
	print "</ul>\n";
}

else {
	print "Niste trenutno upisani ni u jedan aktivan predmet. Ako je ovo greška, kontaktirajte tutora!";
}


}

?>