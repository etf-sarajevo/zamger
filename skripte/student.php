<?

$uslovag=" and ag.id=5";

if ($argc != 3) { print "Greska.\n"; exit; }

require("../www/lib/libvedran.php");
require("../www/lib/zamger.php");

dbconnect();

$id = intval($argv[2]);

$q1 = myquery("select ime,prezime from osoba where id=$id");
if (mysql_num_rows($q1)<1) {
	print "Nema takvog studenta.\n";
	exit;
}

print mysql_result($q1,0,0)." ".mysql_result($q1,0,1).": \n";

$q2 = myquery("select pk.id,p.naziv,ag.naziv,p.id,ag.id from predmet as p, 
ponudakursa as pk, student_predmet as sp, akademska_godina as ag where 
sp.student=$id and sp.predmet=pk.id and pk.predmet=p.id and 
pk.akademska_godina=ag.id $uslovag");
while ($r2 = mysql_fetch_row($q2)) {
	print " - \"$r2[1] ($r2[2])\": ";
	$q3 = myquery("select l.naziv from labgrupa as l, 
student_labgrupa as sl where sl.student=$id and sl.labgrupa=l.id and 
l.predmet=$r2[3] and l.virtualna=0 and l.akademska_godina=$r2[4]");
	while ($r3 = mysql_fetch_row($q3)) {
		print "$r3[0], ";
	}
	print "\n";
}

print "\n\n";


dbdisconnect();

?>
