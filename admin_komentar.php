<?


// v3.0.1.1 (2007/09/26) + Novi modul: Komentar


function admin_komentar() {

global $userid;

?>
<html>
<head>
	<title>Komentari na rad studenta</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="css/style.css" rel="stylesheet" type="text/css" />
</head>
<body topmargin="0" leftmargin="0" bottommargin="0" rightmargin="0" bgcolor="#FFFFFF">
<p>Komentari na rad studenta</p>
<?

$stud_id=intval($_GET['student']); 
if ($stud_id<1) $stud_id=intval($_POST['student']); 

$labgrupa=intval($_GET['labgrupa']); 
if ($labgrupa<1) $labgrupa=intval($_POST['labgrupa']);


// Da li neko spoofa predmet/studenta?
$q100 = myquery("select sl.labgrupa from student_labgrupa as sl where sl.student=$stud_id and sl.labgrupa=$labgrupa");
if (mysql_num_rows($q100)<1) {
	niceerror("Nemate pravo pristupa ovom studentu!");
	return;
}

$q100a = myquery("select predmet from labgrupa where id=$labgrupa");
if (mysql_num_rows($q100a)<1) {
	niceerror("Nemate pravo pristupa ovom studentu!");
	return;
}
$predmet = mysql_result($q100a,0,0);


// Limit...
$q101 = myquery("select o.labgrupa from ogranicenje as o, labgrupa as l where o.nastavnik=$userid and o.labgrupa=l.id and l.predmet=$predmet");
if (mysql_num_rows($q101)>0) {
	$nasao=0;
	while ($r101 = mysql_fetch_row($q101)) {
		if ($r101[0] == $labgrupa) { $nasao=1; break; }
	}
	if ($nasao == 0) {
		niceerror("Nemate pravo pristupa ovom studentu!");
		return;
	}
}

$q102 = myquery("select ime, prezime, brindexa from student where id=$stud_id");
if ($r102 = mysql_fetch_row($q102)) {
	print "<h3>$r102[0] $r102[1] ($r102[2])</h3>\n";
} else {
	niceerror("Nemate pravo pristupa ovom studentu!");
	return;
}


// ------------------------
//  Akcije
// ------------------------

if ($_POST['akcija'] == "dodaj") {
	list ($h,$m,$s) = explode(":", $_POST['vrijeme']);
	$datum = date("Y-m-d H:i:s", mktime($h,$m,$s, $_POST['month'], $_POST['day'], $_POST['year']));
	$komentar = my_escape($_POST['komentar']);
	$q120 = myquery("insert into komentar set student=$stud_id, nastavnik=$userid, labgrupa=$labgrupa, datum='$datum', komentar='$komentar'");
}
if ($_GET['akcija'] == "obrisi") {
	$id = intval($_GET['id']);
	$q121 = myquery("delete from komentar where id=$id");
}


// Spisak komentara

$q110 = myquery("select k.id, n.ime, n.prezime, UNIX_TIMESTAMP(k.datum), k.komentar from komentar as k, nastavnik as n where k.student=$stud_id and k.labgrupa=$labgrupa and k.nastavnik=n.id");

if (mysql_num_rows($q110) < 1) {
	print "<ul><li>Nijedan komentar nije unesen.</li></ul>\n";
}
while ($r110 = mysql_fetch_row($q110)) {
	$datum = date("d. m. Y. H:i:s", $r110[3]);
	print "<p><b>$datum ($r110[1] $r110[2]):</b> (<a href=\"".genuri()."&akcija=obrisi&id=$r110[0]\">Obriši</a>)<br/>$r110[4]<br/></p>\n";
}


// Dodaj komentar
?>
<p><hr></p>
<p><b>Dodajte komentar:</b><br/>
<?=genform();?>
<input type="hidden" name="akcija" value="dodaj">
Trenutni datum i vrijeme:<br/>
<?=datectrl(date("d"),date("m"),date("Y"));?>&nbsp;
<input type="text" size="10" name="vrijeme" value="<?=date("H:i:s");?>"><br/><br/>
<textarea cols="35" rows="5" name="komentar"></textarea><br/>
<input type="submit" value=" Pošalji "></form>
</p>
<?


}


?>
