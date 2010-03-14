<?php

//novi modul student/prijava_ispita


function student_prijava_ispita() {

global $userid;


?>
<h3>Prijava ispita</h3>
<?

// Trebaće nam aktuelna godina

$q5 = myquery("select id from akademska_godina where aktuelna=1");
$ag = mysql_result($q5,0,0);



// Odjavljivanje sa prijavljenog ispita

if ($_GET["akcija"]=="odjavi") {
	$termin = intval($_GET['termin']);
	if ($termin) {
		$q200 = myquery("select count(*) from student_ispit_termin where student=$userid and ispit_termin=$termin");
		if (mysql_result($q200,0,0)<1) {
			niceerror("Već ste ispisani sa termina.");
			?>
			<script language="JavaScript">
			location.href='?sta=student/prijava_ispita';
			</script>
			<?
			return;
		}
		$q210 = myquery("DELETE FROM student_ispit_termin WHERE student=$userid AND ispit_termin=$termin");
		nicemessage("Uspješno ste odjavljeni sa ispita");
		zamgerlog("odjavljen sa ispita", 2);
	}
}


// Prijava na ispit

if ($_GET["akcija"]=="prijavi") {
	$termin = intval($_REQUEST['termin']);
	if (!$termin) {
		niceerror("Neispravan termin.");
		return;
	}

	// Da li je student upisan na predmet?
	$q100 = myquery ("SELECT count(*) FROM ispit_termin as it, ispit as i, ponudakursa as pk, student_predmet as sp WHERE it.id=$termin AND it.ispit=i.id AND i.predmet=pk.predmet AND i.akademska_godina=$ag and pk.akademska_godina=$ag and pk.id=sp.predmet AND sp.student=$userid");
	if (mysql_result($q100,0,0)<1) {
		niceerror("Niste upisani na taj predmet!");
		return;
	}

	// Da li je popunjen termin?
	$q110 = myquery("SELECT count(*) FROM student_ispit_termin WHERE ispit_termin=$termin");
	$q120 = myquery("SELECT maxstudenata FROM ispit_termin WHERE id=$termin");
	if (mysql_result($q110,0,0) >= mysql_result($q120,0,0)) {
		niceerror("Ispitni termin je popunjen.");
	} else {
		$q130 = myquery("select ispit from ispit_termin where id=$termin");
		$ispit = mysql_result($q130,0,0);

		// Da li je već prijavio termin na istom ispitu?
		$q135 = myquery("select count(*) from student_ispit_termin as sit, ispit_termin as it where sit.student=$userid and sit.ispit_termin=it.id and it.ispit=$ispit");
		if (mysql_result($q135,0,0)>0) {
			niceerror("Već ste prijavljeni na neki termin za ovaj ispit.");
		} else {
			$q140 = myquery("INSERT INTO student_ispit_termin (student,ispit_termin) VALUES ($userid, $termin)");
			nicemessage("Uspješno ste prijavljeni na termin");
			zamgerlog("prijavljen na termin za ispit", 2);
		}
	}
}



// GLAVNI EKRAN

// Spisak ispita koji se mogu prijaviti

$q10=myquery("SELECT it.id, p.id, k.id, i.id, p.naziv, UNIX_TIMESTAMP(it.datumvrijeme), UNIX_TIMESTAMP(it.deadline), k.gui_naziv, it.maxstudenata FROM ispit_termin as it, ispit as i, predmet as p, komponenta as k, osoba as o, student_predmet as sp, ponudakursa as pk WHERE it.ispit=i.id AND i.komponenta=k.id AND i.predmet=p.id AND i.akademska_godina=$ag AND pk.predmet=p.id and pk.akademska_godina=$ag AND o.id=$userid AND o.id=sp.student AND sp.predmet=pk.id AND it.datumvrijeme>=NOW() ORDER BY it.datumvrijeme");


?>
<br><br>
<b>Ispiti otvoreni za prijavu:</b>
<br><br>
<table border="0" cellspacing="1" cellpadding="5">
<thead>
<tr bgcolor="#999999">
	<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">R.br.</font></td>
	<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Predmet</font></td>
	<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Vrijeme ispita</font></td>
	<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Rok za prijavu</font></td>
	<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Tip ispita</font></td>
	<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Opcije</font></td>
</tr>
</thead>
<tbody>

<?

$brojac=1;

while ($r10=mysql_fetch_row($q10)) {
	$id_termina = $r10[0];
	$id_predmeta = $r10[1];
	$id_komponente = $r10[2];
	$id_ispita = $r10[3];

	$naziv_predmeta = $r10[4];
	$vrijeme_ispita = date("d.m.Y. H:i",date($r10[5]));
	$rok_za_prijavu = date("d.m.Y. H:i",date($r10[6]));
	$tip_ispita = $r10[7];
	$max_studenata =$r10[8];

	// Da li je student već položio ovu vrstu ispita?
	$q20 = myquery("select count(*) from ispitocjene as io, ispit as i, komponenta as k where io.student=$userid and io.ispit=i.id and i.predmet=$id_predmeta and i.akademska_godina=$ag and i.komponenta=k.id and k.id=$id_komponente and io.ocjena>=k.prolaz");
	if (mysql_result($q20,0,0)>0) continue;

	// Da li je položio predmet?
	$q30 = myquery("select count(*) from konacna_ocjena where student=$userid and predmet=$id_predmeta and ocjena>=6");
	if (mysql_result($q30,0,0)>0) continue;

	// Da li je već prijavio ovaj ispit u nekom od termina?
	$q40 = myquery("select count(*) from student_ispit_termin as sit, ispit_termin as it where sit.student=$userid and sit.ispit_termin=it.id and it.ispit=$id_ispita");
	if (mysql_result($q40,0,0)>0) continue;


	?>
	<tr>
		<td><?=$brojac?></td>
		<td><?=$naziv_predmeta?></td>
		<td align="center"><?=$vrijeme_ispita?></td>
		<td align="center"><?=$rok_za_prijavu?></td>
		<td align="center"><?=$tip_ispita?></td>
		<td align="center"><?

	// Da li je termin popunjen?
	$q50 = myquery("SELECT count(*) FROM student_ispit_termin WHERE ispit_termin=$id_termina");
	if(mysql_result($q50,0,0)>=$max_studenata) {
		?><font color="#FF0000">Termin popunjen</font><?

	// Da li je istekao rok za prijavu?
	} else if ($r10[6]<time()) {
		?><font color="#FF0000">Rok je istekao</font><?

	} else {
		?><a href="?sta=student/prijava_ispita&akcija=prijavi&termin=<?=$id_termina?>">Prijavi</a><?
	}?></td>
	</tr>
	<?
	$brojac++;
}

?>
</table>
<? if($brojac==1) { ?><p>Trenutno nema termina na koje se možete prijaviti.</p><? } ?>
<br><br><br>

<b>Prijavljeni ispiti:</b>

<?


//slijedeci dio koda sluzi za tabelarni prikaz prijavljenih predmeta

$q60 = myquery("SELECT p.naziv, UNIX_TIMESTAMP(it.datumvrijeme), k.gui_naziv, it.id
             FROM ispit_termin as it, ispit as i, predmet as p, komponenta as k, student_ispit_termin as sit
             WHERE it.ispit=i.id AND p.id=i.predmet AND i.akademska_godina=$ag AND i.komponenta=k.id AND sit.student=$userid AND sit.ispit_termin=it.id AND it.datumvrijeme>=NOW()");

?>
<br><br>
<table border="0" cellspacing="1" cellpadding="5">
<thead>
<tr bgcolor="#999999">
	<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">R.br.</font></td>
	<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Predmet</font></td>
	<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Vrijeme ispita</font></td>
	<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Tip ispita</font></td>
	<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Opcije</font></td>
</tr>
</thead>
<tbody>
<?
$brojac=1;

while ($r60=mysql_fetch_row($q60)) {
	?>
	<tr>
		<td><?=$brojac?></td>
		<td><?=$r60[0]?></td>
		<td align="center"><?=date("d.m.Y. H:i",date($r60[1]));?></td>
		<td align="center"><?=$r60[2];?></td>
		<td align="center"><a href="?sta=student/prijava_ispita&akcija=odjavi&termin=<?=$r60[3];?> ">Odjavi</a></td>
	</tr>
	<?
	$brojac++;
}

?>
</table>
<?

if($brojac==1) print "<p>Niste prijavljeni niti na jedan ispit</p>";




}
?>