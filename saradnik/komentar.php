<?

// SARADNIK/KOMENTAR - stavljanje komentara na rad studenata



function saradnik_komentar() {

require_once("lib/formgen.php"); // datectrl

global $userid, $user_siteadmin;

?>
<body topmargin="0" leftmargin="0" bottommargin="0" rightmargin="0" bgcolor="#FFFFFF">
<?

$stud_id=intval($_REQUEST['student']); 
$labgrupa=intval($_REQUEST['labgrupa']); 


// Da li neko spoofa predmet/studenta?
$q10 = db_query("select sl.labgrupa from student_labgrupa as sl where sl.student=$stud_id and sl.labgrupa=$labgrupa");
if (db_num_rows($q10)<1) {
	zamgerlog("student u$stud_id nije u labgrupi g$labgrupa",3);
	zamgerlog2("id studenta i labgrupe ne odgovaraju", $stud_id, $labgrupa);
	niceerror("Nemate pravo pristupa ovom studentu!");
	return;
}

// Prava pristupa i odredjivanje predmeta
if ($user_siteadmin) {
	$q20 = db_query("select predmet, akademska_godina from labgrupa where id=$labgrupa");
	if (db_num_rows($q20)<1) {
		zamgerlog("nepoznata labgrupa (labgrupa $labgrupa predmet pp$predmet)",3);
		zamgerlog2("nepoznata labgrupa", $labgrupa);
		niceerror("Nepoznata grupa $labgrupa");
		return;
	}
} else {
	$q20 = db_query("select np.predmet, np.akademska_godina from labgrupa as l, nastavnik_predmet as np where l.id=$labgrupa and l.predmet=np.predmet and l.akademska_godina=np.akademska_godina and np.nastavnik=$userid");
	if (db_num_rows($q20)<1) {
		zamgerlog("nastavnik nije na predmetu (labgrupa g$labgrupa)",3);
		zamgerlog2("nije saradnik na predmetu", $predmet, $ag);
		niceerror("Nemate pravo pristupa ovom studentu!");
		return;
	}
}
$predmet = db_result($q20,0,0);
$ag = db_result($q20,0,1);


// Limit...
$q30 = db_query("select o.labgrupa from ogranicenje as o, labgrupa as l where o.nastavnik=$userid and o.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag");
if (db_num_rows($q30)>0) {
	$nasao=0;
	while ($r30 = db_fetch_row($q30)) {
		if ($r30[0] == $labgrupa) { $nasao=1; break; }
	}
	if ($nasao == 0) {
		zamgerlog("ogranicenje (labgrupa g$labgrupa predmet pp$predmet)",3);
		zamgerlog2("ima ogranicenje na labgrupu", $labgrupa);
		niceerror("Nemate pravo pristupa ovom studentu!");
		return;
	}
}

$q40 = db_query("select ime, prezime, brindexa from osoba where id=$stud_id");
if ($r40 = db_fetch_row($q40)) {
	print "<h3>$r40[0] $r40[1] ($r40[2])</h3>\n";
} else {
	zamgerlog("nepostojeci student $stud_id",3);
	zamgerlog2("nepostojeci student", $stud_id);
	niceerror("Nemate pravo pristupa ovom studentu!");
	return;
}

// Odredjujem ponudukursa koju tabela komentar za sada jos uvijek koristi
$q45 = db_query("select pk.id from ponudakursa as pk, student_predmet as sp where sp.student=$stud_id and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
$ponudakursa = db_result($q45,0,0);


// ------------------------
//  Akcije
// ------------------------

if (param('akcija') == "dodaj" && check_csrf_token()) {
	list ($h,$m,$s) = explode(":", $_POST['vrijeme']);
	$datum = date("Y-m-d H:i:s", mktime($h,$m,$s, $_POST['month'], $_POST['day'], $_POST['year']));
	$komentar = db_escape($_POST['komentar']);
	$q50 = db_query("insert into komentar set student=$stud_id, nastavnik=$userid, labgrupa=$labgrupa, predmet=$ponudakursa, datum='$datum', komentar='$komentar'");

	zamgerlog("dodan komentar na studenta u$stud_id labgrupa g$labgrupa",2);
	zamgerlog2("dodan komentar na studenta", $stud_id, $labgrupa);
}
if (param('akcija') == "obrisi") {
	$id = intval($_GET['id']);
	$q60 = db_query("delete from komentar where id=$id");

	zamgerlog("obrisan komentar $id",2);
	zamgerlog2("obrisan komentar", $id);
}


// Spisak komentara

$q70 = db_query("select k.id, a.ime, a.prezime, UNIX_TIMESTAMP(k.datum), k.komentar from komentar as k, osoba as a where k.student=$stud_id and k.labgrupa=$labgrupa and k.nastavnik=a.id");

if (db_num_rows($q70) < 1) {
	print "<ul><li>Nijedan komentar nije unesen.</li></ul>\n";
}
while ($r70 = db_fetch_row($q70)) {
	$datum = date("d. m. Y. H:i:s", $r70[3]);
	print "<p><b>$datum ($r70[1] $r70[2]):</b> (<a href=\"".genuri()."&akcija=obrisi&id=$r70[0]\">Obriši</a>)<br/>$r70[4]<br/></p>\n";
}


// Dodaj komentar
?>
<p><hr></p>
<p><b>Dodajte komentar:</b><br/>
<?=genform("POST");?>
<input type="hidden" name="akcija" value="dodaj">
Trenutni datum i vrijeme:<br/>
<?=datectrl(date("d"),date("m"),date("Y"));?>&nbsp;
<input type="text" size="10" name="vrijeme" value="<?=date("H:i:s");?>" class="default"><br/><br/>
<textarea cols="35" rows="5" name="komentar"></textarea><br/>
<input type="submit" value=" Pošalji " class="default"></form>
</p>
<?


}


?>
