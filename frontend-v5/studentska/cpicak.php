<?

function studentska_cpicak() {

$q1 = myquery("select id, naziv from nacionalnost");
while ($r1 = mysql_fetch_row($q1)) {
	$nacionalnost[$r1[0]] = $r1[1];
}

$q2 = myquery("select id, naziv from nacin_studiranja");
while ($r2 = mysql_fetch_row($q2)) {
	$nacin_studiranja[$r2[0]] = $r2[1];
}

$q3 = myquery("select id, naziv from opcina");
while ($r3 = mysql_fetch_row($q3)) {
	$opc = $r3[1];
	$opc = str_replace(",","",$opc);
	$opcina[$r3[0]] = $opc;
}

$q4 = myquery("select id, naziv from drzava");
while ($r4 = mysql_fetch_row($q4)) {
	$drzava[$r4[0]] = $r4[1];
}

$q5 = myquery("select id, naziv, opcina, drzava from mjesto");
while ($r5 = mysql_fetch_row($q5)) {
	$mjesto[$r5[0]] = $r5[1];
	$mj_opcina[$r5[0]] = $opcina[$r5[2]];
if ($r5[1]=="Sarajevo" && $opcina[$r5[2]]=="") $mj_opcina[$r5[0]]="Sarajevo";
	$mj_drzava[$r5[0]] = $drzava[$r5[3]];
}

$q6 = myquery("select id, naziv, opcina, domaca from srednja_skola");
while ($r6 = mysql_fetch_row($q6)) {
$ss = str_replace(",","",$r6[1]);
$ss = str_replace("'","",$ss);
	$srednja_skola[$r6[0]] = $ss;
	$s_s_opcina[$r6[0]] = $opcina[$r6[2]];
	$s_s_domaca[$r6[0]] = $r6[3];
}

$q7 = myquery("select id, naziv from kanton");
while ($r7 = mysql_fetch_row($q7)) {
	$kanton[$r7[0]] = $r7[1];
}


$q8 = myquery("select id, naziv from akademska_godina");
while ($r8 = mysql_fetch_row($q8)) {
	$godina[$r8[0]] = substr($r8[1],0,4);
}






$q10 = myquery("select o.brindexa, o.ime, o.prezime, o.jmbg, o.imeoca, 
o.prezimeoca, o.imemajke, o.prezimemajke, o.adresa, o.telefon, o.email, 
o.spol, o.nacionalnost, UNIX_TIMESTAMP(o.datum_rodjenja), 
ss.nacin_studiranja, o.mjesto_rodjenja, uus.srednja_skola, 
uus.godina, o.boracke_kategorije, s.naziv, o.drzavljanstvo, 
o.adresa_mjesto, o.kanton, o.slika
from osoba as o, student_studij as ss, uspjeh_u_srednjoj as uus, studij 
as s
where ss.student=o.id and ss.semestar=1 and ss.ponovac=0 and 
ss.akademska_godina=6 and o.id=uus.osoba and ss.studij=s.id
");

print "<textarea rows=10 cols=80>\n";

while ($r10 = mysql_fetch_row($q10)) {
if ($r10[0]<15805) continue;
	$ispis = 
"NOVI_STUDENT,$r10[0],$r10[1],$r10[2],$r10[3],$r10[4],$r10[5],$r10[6],$r10[7],";
$adr = str_replace(",","",$r10[8]);
	$ispis .= "$adr ".$mjesto[$r10[21]].","; 
$email = preg_replace("/,.*?$/", "", $r10[10]);
	$ispis .= "$r10[9],$email,$r10[11],";
	$ispis .= $nacionalnost[$r10[12]].",";
	$ispis .= date("d/m/Y", $r10[13]).",";
	$ispis .= $nacin_studiranja[$r10[14]].",";
	$ispis .= $mjesto[$r10[15]].",".$mj_opcina[$r10[15]].",".$mj_drzava[$r10[15]].",";
	$ispis .= $srednja_skola[$r10[16]].",";
	$ispis .= $godina[$r10[17]].",";
	$ispis .= $s_s_opcina[$r10[16]].",".$s_s_domaca[$r10[16]].",";
	$ispis .= $r10[18].",".$r10[19].",".$drzava[$r10[20]].",";
	$ispis .= $mj_drzava[$r10[21]].",";
	$kanta = $kanton[$r10[22]];
	if ($kanta=="Unsko-Sanski kanton") $kanta = "UNSKO-SANSKI KANTON";
	if ($kanta=="Posavski kanton") $kanta = "POSAVSKI KANTON";
	if ($kanta=="Tuzlanski kanton") $kanta = "TUZLANSKI KANTON";
	if ($kanta=="Zeničko-Dobojski kanton") $kanta = "ZENIČKO DOBOJSKI KANTON";
	if ($kanta=="Bosansko-Podrinjski kanton") $kanta = "BOSANSKO PODRINJSKI KANTON";
	if ($kanta=="Srednjobosanski kanton") $kanta = "SREDNJEBOSANSKI KANTON";
	if ($kanta=="Hercegovačko-Neretvanski kanton") $kanta = "HERCEGOVAČKO NERETVANSKI KANTON";
	if ($kanta=="Zapadno-Hercegovački kanton") $kanta = "ZAPADNO HERCEGOVAČKI KANTON";
	if ($kanta=="Sarajevski kanton") $kanta = "KANTON SARAJEVO";
	if ($kanta=="Livanjski kanton") $kanta = "HERCEGBOSANSKI KANTON";
	if ($kanta=="Distrikt Brčko") $kanta = "DISTRIKT BRČKO";
	if ($kanta=="Republika Srpska") $kanta = "RS";
	
	$ispis .= $kanta.",";
	$ispis .= $mj_opcina[$r10[21]].",".$mjesto[$r10[21]].",";
	$ispis .= $r10[23]."\n";
	print $ispis;
}

print "</textarea>";

}

?>

