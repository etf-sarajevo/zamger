<?

// SARADNIK/KOMENTAR - stavljanje komentara na rad studenata

// v3.9.1.0 (2008/02/14) + Preimenovan bivsi admin_komentar
// v3.9.1.1 (2008/02/28) + Dodana nulta labgrupa
// v3.9.1.2 (2008/08/28) + Tabela osoba umjesto auth
// v4.0.0.0 (2009/02/19) + Release
// v4.0.9.1 (2009/03/25) + nastavnik_predmet preusmjeren sa tabele ponudakursa na tabelu predmet



function saradnik_komentar() {

global $userid;

?>
<body topmargin="0" leftmargin="0" bottommargin="0" rightmargin="0" bgcolor="#FFFFFF">
<?

$stud_id=intval($_REQUEST['student']); 
$labgrupa=intval($_REQUEST['labgrupa']); 
$predmet=intval($_REQUEST['predmet']);


// Da li neko spoofa predmet/studenta?
if ($labgrupa>0) {
	$q10 = myquery("select sl.labgrupa from student_labgrupa as sl where sl.student=$stud_id and sl.labgrupa=$labgrupa");
	if (mysql_num_rows($q10)<1) {
		zamgerlog("student $stud_id nije u labgrupi $labgrupa",3);
		niceerror("Nemate pravo pristupa ovom studentu!");
		return;
	}
}

if ($admin==3) {
	if ($labgrupa>0) {
		$q20 = myquery("select predmet from labgrupa where id=$labgrupa");
		if (mysql_num_rows($q20)<1) {
			zamgerlog("nepoznata labgrupa (labgrupa $labgrupa predmet $predmet)",3);
			niceerror("Nepoznata grupa $labgrupa");
			return;
		}
	}
} else {
	if ($labgrupa>0) {
		$q20 = myquery("select np.predmet from labgrupa as l, nastavnik_predmet as np, ponudakursa as pk where l.id=$labgrupa and l.predmet=pk.id and pk.predmet=np.predmet and pk.akademska_godina=np.akademska_godina and np.nastavnik=$userid");
		if (mysql_num_rows($q20)<1) {
			zamgerlog("nastavnik nije na predmetu (labgrupa $labgrupa)",3);
			niceerror("Nemate pravo pristupa ovom studentu!");
			return;
		}
	} else {
		$q25 = myquery("select count(*) from nastavnik_predmet as np, ponudakursa as pk where np.nastavnik=$userid and np.predmet=pk.predmet and np.akademska_godina=pk.akademska_godina and pk.id=$predmet");
		if (mysql_result($q25,0,0)<1) {
			zamgerlog("nastavnik nije na predmetu $predmet",3);
			niceerror("Nemate pravo pristupa ovom studentu!");
			return;
		}
	}
}
// $predmet = mysql_result($q20,0,0);


// Limit...
if ($labgrupa>0) {
	$q30 = myquery("select o.labgrupa from ogranicenje as o, labgrupa as l where o.nastavnik=$userid and o.labgrupa=l.id and l.predmet=$predmet");
	if (mysql_num_rows($q30)>0) {
		$nasao=0;
		while ($r30 = mysql_fetch_row($q30)) {
			if ($r30[0] == $labgrupa) { $nasao=1; break; }
		}
		if ($nasao == 0) {
			zamgerlog("ogranicenje (labgrupa $labgrupa predmet $predmet)",3);
			niceerror("Nemate pravo pristupa ovom studentu!");
			return;
		}
	}
}

$q40 = myquery("select ime, prezime, brindexa from osoba where id=$stud_id");
if ($r40 = mysql_fetch_row($q40)) {
	print "<h3>$r40[0] $r40[1] ($r40[2])</h3>\n";
} else {
	zamgerlog("nepostojeci student $stud_id",3);
	niceerror("Nemate pravo pristupa ovom studentu!");
	return;
}


// ------------------------
//  Akcije
// ------------------------

if ($_POST['akcija'] == "dodaj" && check_csrf_token()) {
	list ($h,$m,$s) = explode(":", $_POST['vrijeme']);
	$datum = date("Y-m-d H:i:s", mktime($h,$m,$s, $_POST['month'], $_POST['day'], $_POST['year']));
	$komentar = my_escape($_POST['komentar']);
	$q50 = myquery("insert into komentar set student=$stud_id, nastavnik=$userid, labgrupa=$labgrupa, predmet=$predmet, datum='$datum', komentar='$komentar'");

	zamgerlog("dodan komentar na studenta $stud_id labgrupa $labgrupa",2);
}
if ($_GET['akcija'] == "obrisi") {
	$id = intval($_GET['id']);
	$q60 = myquery("delete from komentar where id=$id");

	logthis("obrisan komentar $id",2);
}


// Spisak komentara

$q70 = myquery("select k.id, a.ime, a.prezime, UNIX_TIMESTAMP(k.datum), k.komentar from komentar as k, osoba as a where k.student=$stud_id and k.labgrupa=$labgrupa and k.predmet=$predmet and k.nastavnik=a.id");

if (mysql_num_rows($q70) < 1) {
	print "<ul><li>Nijedan komentar nije unesen.</li></ul>\n";
}
while ($r70 = mysql_fetch_row($q70)) {
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
