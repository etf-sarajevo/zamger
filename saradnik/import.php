<html>
<head>
<title>Import bodova iz moodle-a</title>
</head>
<body>

<?php
//Tabelama sam pristupao preko moodle.ime_tabele i zamger.ime_tabele tako da ne treba mysql_select_db('ime_baze')
$link_moodle = mysql_connect("localhost", "moodle", "moodle") or die ("Greska pri konekciji: " .mysql_error());
$link_zamger = mysql_connect("localhost", "zamger", "zamger") or die ("Greska pri konekciji: " .mysql_error());
/*
if (!$link_moodle) {
	mysql_select_db("moodle");
}
if (!$link_zamger) {
	mysql_select_db("zamger");
}*/

//Prikupljanje podataka iz Moodle tabele
//Prikupljaju se sifra predmeta, ime zadace i JMBG svih studenata
//Posto se pri prikupljanju zadace porede po imenu trebaju imati isti naziv u Moodle-u kao i u Zamgeru
$query1 = mysql_query("SELECT c.idnumber, gi.itemname, u.idnumber
	FROM moodle.mdl_grade_grades gg, moodle.mdl_user u, moodle.mdl_grade_items gi, moodle.mdl_course c
	WHERE gi.itemname IN ('Zadaca 1', 'Zadaca 2', 'Zadaca 3', 'Zadaca 4', 'Zadaca 5') AND
	gg.userid=u.id AND gg.itemid=gi.id AND gi.courseid=c.id", $link_moodle) or die ("Greska u query1: " .mysql_error());
		
//Ubacivanje podataka u zamger tabelu
while ($row1 = mysql_fetch_array($query1)) {
	//$bodovi sadrzi vrijednost zadace za date vrijednosti iz $row1 (trenutni student, trenutna zadaca i trenutni predmet)
	$bodovi = mysql_query("SELECT gg.finalgrade
		FROM moodle.mdl_grade_grades gg, moodle.mdl_user u, moodle.mdl_grade_items gi, moodle.mdl_course c
		WHERE gi.itemname='$row1[1]' AND c.idnumber='$row1[0]' AND u.idnumber='$row1[2]' AND
		gg.userid=u.id AND gg.itemid=gi.id AND gi.courseid=c.id", $link_moodle) or die ("Greska u bodovi: " .mysql_error());
	$bodovi_value = mysql_fetch_array($bodovi);
	
	//zadaca_id sadrzi id zadace trenutne vrijednosti u $row1
	$zadaca_id = mysql_query("SELECT z.id
		FROM zamger.zadaca z, zamger.predmet p
		WHERE z.naziv='$row1[1]' AND p.sifra='$row1[0]' AND p.id=z.predmet", $link_zamger) or die ("Greska u zadaca_id: " .mysql_error());
	$zadaca_id_value = mysql_fetch_array($zadaca_id);
	
	//$student_id vraca id studenta koji si trenutno cita iz $row1
	$student_id = mysql_query("SELECT o.id
		FROM zamger.osoba o
		WHERE o.jmbg='$row1[2]'", $link_zamger) or die ("Greska u student_id: " .mysql_error());
	$student_id_value = mysql_fetch_array($student_id);
	
	//Kao user koji unosi vrijednosti u tabelu stavio sam nastavnika na predmetu, znam da to nije dobro, ali je privremeno dok ne skontam kako izabrati user-a koji je trenunto logovan
	$user_id = mysql_query("SELECT np.nastavnik
		FROM zamger.nastavnik_predmet np, zamger.predmet p
		WHERE p.sifra='$row1[0]' AND p.id=np.predmet", $link_zamger) or die ("Greska u user_id: " .mysql_error());
	$user_id_value = mysql_fetch_array($user_id);
	
	$query2 = "INSERT INTO zamger.zadatak (zadaca, redni_broj, student, status, bodova, vrijeme, userid)
		VALUES ('$zadaca_id_value[0]', '1', '$student_id_value[0]', '5', '$bodovi_value[0]', 'SYSDATE', '$user_id_value[0]')";
	
	mysql_query($query2, $link_zamger) or die ("Greska u query2: " .mysql_error());
}
?>

</body>
</html>
