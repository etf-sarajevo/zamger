<?php

//novi modul student/prijava_ispita


function student_prijava_ispita() {

global $userid;


?>
<h3>Prijava ispita/događaja</h3>
<?

// Trebaće nam aktuelna godina

$q5 = myquery("select id from akademska_godina where aktuelna=1");
$ag = mysql_result($q5,0,0);



// Odjavljivanje sa prijavljenog ispita

if ($_GET["akcija"]=="odjavi") {
	$termin = intval($_GET['termin']);
	$q200 = myquery("select i.predmet, i.akademska_godina, UNIX_TIMESTAMP(ist.deadline) from student_ispit_termin as sit, ispit_termin as ist, ispit as i where sit.student=$userid and sit.ispit_termin=$termin and ist.id=$termin and ist.ispit=i.id");
	if (mysql_num_rows($q200)<1) {
		niceerror("Već ste ispisani sa termina.");
		?>
		<script language="JavaScript">
		location.href='?sta=student/prijava_ispita';
		</script>
		<?
		return;
	}
	
	if (mysql_result($q200,0,2) < time() && $_GET['potvrda_odjave'] != "da") {
		niceerror("Rok za prijavljivanje na ovaj ispit je istekao!");
		?>
		<p>Ako se sada odjavite, više se nećete moći ponovo prijaviti za ovaj isti termin! Da li ste sigurni da želite da se odjavite?</p>
		<?=genform("GET");?>
		<input type="hidden" name="potvrda_odjave" value="da">
		<input type="submit" value="Da, odjavi me!">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="button" value="Nazad" onclick="javascript:location.href='?sta=student/prijava_ispita'">
		</form>
		<?
		return;
	}
	
	$predmet = mysql_result($q200,0,0);
	$q210 = myquery("DELETE FROM student_ispit_termin WHERE student=$userid AND ispit_termin=$termin");
	nicemessage("Uspješno ste odjavljeni sa ispita/događaja.");
	zamgerlog("odjavljen sa ispita (pp$predmet)", 2);
}


// Prijava na ispit

if ($_GET["akcija"]=="prijavi") {
	$termin = intval($_REQUEST['termin']);
	if (!$termin) {
		niceerror("Neispravan termin.");
		return;
	}

	// Da li je student upisan na predmet?
	$q100 = myquery ("SELECT i.predmet FROM ispit_termin as it, ispit as i, ponudakursa as pk, student_predmet as sp WHERE it.id=$termin AND it.ispit=i.id AND i.predmet=pk.predmet AND pk.akademska_godina=i.akademska_godina and pk.id=sp.predmet AND sp.student=$userid");
	if (mysql_num_rows($q100)<1) {
		niceerror("Niste upisani na taj predmet!");
		return;
	}
	$predmet = mysql_result($q100,0,0);

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
			niceerror("Već ste prijavljeni na neki termin za ovaj ispit/događaj.");
		} else {
			$q140 = myquery("INSERT INTO student_ispit_termin (student,ispit_termin) VALUES ($userid, $termin)");
			nicemessage("Uspješno ste prijavljeni na termin");
			zamgerlog("prijavljen na termin za ispit/događaj (pp$predmet)", 2);
		}
	}
}



// GLAVNI EKRAN

// Spisak ispita koji se mogu prijaviti

$q10=myquery("(SELECT it.id, p.id, k.id, i.id, p.naziv, UNIX_TIMESTAMP(it.datumvrijeme), UNIX_TIMESTAMP(it.deadline), k.gui_naziv, it.maxstudenata 
	FROM ispit_termin as it, ispit as i, predmet as p, komponenta as k, osoba as o, student_predmet as sp, ponudakursa as pk 
	WHERE it.ispit=i.id AND i.komponenta=k.id AND i.predmet=p.id AND pk.predmet=p.id and pk.akademska_godina=i.akademska_godina 
	AND o.id=$userid AND o.id=sp.student AND sp.predmet=pk.id AND it.datumvrijeme>=NOW() ORDER BY it.datumvrijeme) union (SELECT it.id, p.id, d.id, i.id, p.naziv, UNIX_TIMESTAMP(it.datumvrijeme), UNIX_TIMESTAMP(it.deadline), d.naziv, it.maxstudenata 
	FROM ispit_termin as it, ispit as i, predmet as p, dogadjaj as d, osoba as o, student_predmet as sp, ponudakursa as pk 
	WHERE it.ispit=i.id AND i.komponenta=d.id AND i.predmet=p.id AND pk.predmet=p.id and pk.akademska_godina=i.akademska_godina 
	AND o.id=$userid AND o.id=sp.student AND sp.predmet=pk.id AND it.datumvrijeme>=NOW() ORDER BY it.datumvrijeme);");


?>
<br><br>
<b>Ispiti/Događaji otvoreni za prijavu:</b>
<br><br>
<table border="0" cellspacing="1" cellpadding="5">
<thead>
<tr bgcolor="#999999">
	<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">R.br.</font></td>
	<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Predmet</font></td>
	<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Rok za prijavu</font></td>
	<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Vrijeme ispita/događaja</font></td>
	<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Tip ispita/događaja</font></td>
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
//	$q20 = myquery("select count(*) from ispitocjene as io, ispit as i, komponenta as k where io.student=$userid and io.ispit=i.id and i.predmet=$id_predmeta and i.akademska_godina=$ag and i.komponenta=k.id and k.id=$id_komponente and io.ocjena>=k.prolaz");
//	if (mysql_result($q20,0,0)>0) continue;

	// Da li je položio predmet?
	$q30 = myquery("select count(*) from konacna_ocjena where student=$userid and predmet=$id_predmeta and ocjena>=6");
	if (mysql_result($q30,0,0)>0) continue;

	$greska = $greska_long = "";

	// Da li je termin popunjen?
	$q50 = myquery("SELECT count(*) FROM student_ispit_termin WHERE ispit_termin=$id_termina");
	if (mysql_result($q50,0,0)>=$max_studenata) { $greska .= "P"; $greska_long = "Termin popunjen. "; }

	// Da li je već prijavio ovaj ispit u nekom od termina?
	$q40 = myquery("select count(*) from student_ispit_termin as sit, ispit_termin as it where sit.student=$userid and sit.ispit_termin=it.id and it.ispit=$id_ispita");
	if (mysql_result($q40,0,0)>0) {
		$q55 = myquery("SELECT COUNT(*) FROM student_ispit_termin WHERE student=$userid AND ispit_termin=$id_termina");
		if (mysql_result($q55,0,0) > 0) {
			$greska .= "O";
			$greska_long .= "Već ste prijavljeni za ovaj termin. ";
		} else {
			$greska .= "D";
			$greska_long .= "Prijavljeni ste za drugi termin ovog ispita. ";
		}
	}

	// Da li je istekao rok za prijavu?
	$color = "";
	if ($r10[6]<time()) {
		$color = " style=\"color: #999\"";
	}

	?>
	<tr<?=$color?>>
		<td<?=$color?>><?=$brojac?></td>
		<td<?=$color?>><?=$naziv_predmeta?></td>
		<td align="center"<?=$color?>><?=$rok_za_prijavu?></td>
		<td align="center"<?=$color?>><?=$vrijeme_ispita?></td>
		<td align="center"<?=$color?>><?=$tip_ispita?></td>
		<td align="center"<?=$color?> title="<?=$greska_long?>"><?

	if ($r10[6]<time()) {
		?>Rok za prijavu je istekao<?
	} else if ($greska === "") {
		?><a href="?sta=student/prijava_ispita&akcija=prijavi&termin=<?=$id_termina?>">Prijavi</a><?
	} else {
		?><font color="#FF0000">Prijava nije moguća (<?=$greska?>)</font><?
	} ?></td>
	</tr>
	<?
	$brojac++;
}

?>
</table>
<? if($brojac==1) { 
	?><p>Trenutno nema termina na koje se možete prijaviti.</p><? 
} else {
	?><p><b>LEGENDA GREŠAKA:</b><br>
	<b>P</b> - termin je popunjen (ako nema ove oznake, postoji još slobodnih mjesta na ovom terminu)<br>
	<b>O</b> - već ste prijavljeni za ovaj termin<br>
	<b>D</b> - prijavljeni ste za drugi termin istog ispita; potrebno je da se odjavite sa tog termina da biste se mogli prijaviti za ovaj termin</p>
	<?
}

?>
<br><br><br>

<b>Prijavljeni ispiti/događaji:</b>

<?


//slijedeci dio koda sluzi za tabelarni prikaz prijavljenih predmeta

$q60 = myquery("(SELECT p.naziv, UNIX_TIMESTAMP(it.datumvrijeme), k.gui_naziv, it.id, p.id
             FROM ispit_termin as it, ispit as i, predmet as p, komponenta as k, student_ispit_termin as sit
             WHERE it.ispit=i.id AND p.id=i.predmet AND i.akademska_godina=$ag AND i.komponenta=k.id AND sit.student=$userid AND sit.ispit_termin=it.id
             ORDER BY it.datumvrijeme) union (SELECT p.naziv, UNIX_TIMESTAMP(it.datumvrijeme), d.naziv, it.id, p.id
             FROM ispit_termin as it, ispit as i, predmet as p, dogadjaj as d, student_ispit_termin as sit
             WHERE it.ispit=i.id AND p.id=i.predmet AND i.akademska_godina=$ag AND i.komponenta=d.id AND sit.student=$userid AND sit.ispit_termin=it.id
             ORDER BY it.datumvrijeme);");

?>
<br><br>
<table border="0" cellspacing="1" cellpadding="5">
<thead>
<tr bgcolor="#999999">
	<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">R.br.</font></td>
	<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Predmet</font></td>
	<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Vrijeme ispita/događaja</font></td>
	<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Tip ispita/događaja</font></td>
	<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Opcije</font></td>
</tr>
</thead>
<tbody>
<?
$brojac=1;

while ($r60=mysql_fetch_row($q60)) {

	// Ako je ispit u prošlosti, provjeravamo da li ima još termina da bi se student mogao odjaviti sa prošlog termina
	if ($r60[1] < time()) {
		$q70=myquery("SELECT count(*)
		FROM ispit_termin as it, ispit as i
		WHERE it.ispit=i.id AND i.predmet=$r60[4] AND i.akademska_godina=$ag AND it.deadline>=NOW()");
		if (mysql_result($q70,0,0)==0) continue;
	}
	
	// Takođe ne dozvoljavamo da se student odjavi sa ispita za koje ima ocjenu jer bi to moglo pobrkati izvoz ocjena
	$q80 = myquery("select count(*) from konacna_ocjena where student=$userid and predmet=$r60[4] and ocjena>=6");
	if (mysql_result($q80,0,0)>0) continue;

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

if($brojac==1) print "<p>Niste prijavljeni niti na jedan ispit/događaj</p>";




}
?>
