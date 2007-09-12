<?

// v3.0.1.1 (2007/09/11) + Novi modul "Nihada" za unos i pristup podataka o studentima, nastavnicima, loginima itd.



function admin_nihada() {

global $userid;

global $_lv_; // We use form generators


// Provjera privilegija
$q1 = myquery("select siteadmin from nastavnik where id=$userid");

if (mysql_num_rows($q1) < 1 || mysql_result($q1,0,0) < 1) {
	niceerror("Pristup nije dozvoljen.");
	return;
}

$tab=$_REQUEST['tab'];
if ($tab=="") $tab="Studenti";

$akcija=$_REQUEST['akcija'];


// Izvjestaji

if ($akcija == "report") {
?>
<html>
<head>
	<title>Izvještaji</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="css/style.css" rel="stylesheet" type="text/css" />
</head>
<body bgcolor="#FFFFFF">
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>
<?
}


if ($tab == "Studenti" && $akcija == "report") {
	$imena_ocjena = array("Šest","Sedam","Osam","Devet","Deset");

	$student = intval($_REQUEST['student']);
	$q300 = myquery("select ime,prezime,brindexa from student where id=$student");
	if (!($r300 = mysql_fetch_row($q300))) {
		niceerror("Student se ne nalazi u bazi podataka.");
		return;
	}
	print "<p>&nbsp;</br>Student:</br><h1>$r300[0] $r300[1]</h1><br/>\nBroj indeksa: $r300[2]<br/><br/><br/>\n";

	$tip = $_REQUEST['tip'];


	if ($tip == "index") {
		?><p><b>Pregled položenih predmeta sa ocjenama</b></p>
		<table width="700" border="1" cellspacing="0" cellpadding="3"><tr bgcolor="#AAAAAA">
			<td width="20">&nbsp;</td>
			<td width="330">Naziv predmeta</td>
			<td width="200">Akademska godina</td>
			<td width="150">Konačna ocjena</td>
		</tr>
		<?
		$i=1;
		$q301 = myquery("select id,naziv from akademska_godina order by naziv");
		while ($r301 = mysql_fetch_row($q301)) {
			$q302 = myquery("select p.naziv,k.ocjena from konacna_ocjena as k,predmet as p where k.student=$student and k.predmet=p.id and p.akademska_godina=$r301[0] order by p.naziv");
			while ($r302 = mysql_fetch_row($q302)) {
				print "<tr><td>".($i++)."</td><td>".$r302[0]."</td><td>".$r301[1]."</td><td>".$r302[1]." (".$imena_ocjena[$r302[1]-6].")</td></tr>";
			}
		}
		print "</table>";
	}


	if ($tip == "progress" || $tip == "progress2") {
		?><p><b>Pregled ostvarenog rezultata na predmetima</b></p>
		<table width="700" border="1" cellspacing="0" cellpadding="3"><tr bgcolor="#AAAAAA">
			<td width="20">&nbsp;</td>
			<td width="155">Predmet</td>
			<td width="75">Ak. godina</td>
			<td width="75">Prisustvo</td>
			<td width="75">Zadaće</td>
			<td width="75">I parcijalni</td>
			<td width="75">II parcijalni</td>
			<td width="75">UKUPNO</td>
			<td width="75">Ocjena</td>
		</tr>
		<?
		$i=1;
		$q310 = myquery("select id,naziv from akademska_godina order by naziv");
		while ($r310 = mysql_fetch_row($q310)) {
			$q311 = myquery("select p.id, p.naziv, l.id from predmet as p, labgrupa as l, student_labgrupa as sl where sl.student=$student and sl.labgrupa=l.id and l.predmet=p.id and p.akademska_godina=$r310[0] order by p.naziv");
			while ($r311 = mysql_fetch_row($q311)) {
				print "<tr><td>".($i++)."</td><td>".$r311[1]."</td><td>".$r310[1]."</td>";
				$ukupno=0;

				$q312 = myquery("select count(*) from prisustvo as p,cas as c where p.student=$student and p.cas=c.id and c.labgrupa=$r311[2] and p.prisutan=0");
				if (mysql_result($q312,0,0)<=3) {
					print "<td>10</td>";
					$ukupno += 10;
				} else
					print "<td>0</td>";

				$q313 = myquery("select id, zadataka from zadaca where predmet=$r311[0]");
				$zadaca=0;
				while ($r313 = mysql_fetch_row($q313)) {
					for ($i=1; $i<=$r313[1]; $i++) {
						$q314 = myquery("select status,bodova from zadatak where zadaca=$r313[0] and redni_broj=$i and student=$student order by id desc limit 1");
						if ($r314 = mysql_fetch_row($q314))
							if ($r314[0] == 5)
								$zadaca += $r314[1];
					}
				}
				print "<td>$zadaca</td>";
				$ukupno += $zadaca;

				$q315 = myquery("select io.ocjena,i.datum from ispitocjene as io, ispit as i where io.student=$student and io.ispit=i.id and io.ocjena>=0 and i.predmet=$r311[0] order by i.datum");
				$max=0;

				print "<td>";
				if (mysql_num_rows($q315)>0) {
					while ($r315 = mysql_fetch_row($q315)) {
						if ($tip == "progress2") {
							list ($g,$m,$d) = explode("-",$r315[1]);
							print "$r315[0] ($d.$m.)<br/>";
						}
						if ($r315[0]>$max) $max=$r315[0];
					}
					$ukupno += $max;
					if ($tip == "progress") print $max;
				} else
					print "&nbsp;";
				print "</td>";

				$q316 = myquery("select io.ocjena2,i.datum from ispitocjene as io, ispit as i where io.student=$student and io.ispit=i.id and io.ocjena2>=0 and i.predmet=$r311[0] order by i.datum");
				$max=0;

				print "<td>";
				if (mysql_num_rows($q316)>0) {
					while ($r316 = mysql_fetch_row($q316)) {
						if ($tip == "progress2") {
							list ($g,$m,$d) = explode("-",$r316[1]);
							print "$r316[0] ($d.$m.)<br/>";
						}
						if ($r316[0]>$max) $max=$r316[0];
					}
					$ukupno += $max;
					if ($tip == "progress") print $max;
				} else
					print "&nbsp;";
				print "</td>";

				print "<td>$ukupno</td>";

				$q317 = myquery("select ocjena from konacna_ocjena where student=$student and predmet=$r311[0]");
				if ($r317 = mysql_fetch_row($q317))
					if ($r317[0] > 5)
						print "<td>$r317[0]</td>";
					else
						print "<td>Nije položio/la</td>";
				else
					print "<td>Nije položio/la</td>";


				print "</tr>";
			}
		}
		print "</table>";
	}

	return;
}





function printtab($ime,$tab) {
	if ($ime==$tab) 
		print '<td bgcolor="#EEEEEE" width="50">'.$ime.'</td>'."\n";
	else
		print '<td bgcolor="#BBBBBB" width="50"><a href="qwerty.php?sta=nihada&tab='.$ime.'">'.$ime.'</a></td>'."\n";
}



?>
<p><h3>Administracija</h3></p>

<table border="0" cellspacing="1" cellpadding="5" width="800">
<tr>
<td width="50">&nbsp;</td>
<?
printtab("Studenti", $tab);
printtab("Nastavnici", $tab);
printtab("Korisnici", $tab);
printtab("Predmeti", $tab);
?>
<td bgcolor="#BBBBBB" width="50"><a href="qwerty.php">Nazad</a></td>
<td width="350">&nbsp;</td>
</tr>
<tr>
<td width="50">&nbsp;</td>
<td colspan="7" bgcolor="#EEEEEE" width="750">
<?


if ($tab == "Studenti" && $akcija == "edit") {
	$student = intval($_REQUEST['student']);

	print "<a href=\"".genuri()."&akcija=\">Nazad na rezultate pretrage</a><br/><br/>";
	

	// Izvjestaji

	?>
	<center>
	<table width="700" border="0" cellspacing="0" cellpadding="0"><tr><td width="100" valign="top">
		<table width="100%" border="1" cellspacing="0" cellpadding="0">
			<tr><td bgcolor="#777777" align="center">
				<font color="white"><b>IZVJEŠTAJI:</b></font>
			</td></tr>
			<tr><td align="center"><a href="<?=genuri()?>&akcija=report&tip=index">
			<img src="images/kontact_journal.png" border="0"><br/>Indeks</a></td></tr>
			<tr><td align="center"><a href="<?=genuri()?>&akcija=report&tip=progress">
			<img src="images/kontact_journal.png" border="0"><br/>Bodovi</a></td></tr>
			<tr><td align="center"><a href="<?=genuri()?>&akcija=report&tip=progress2">
			<img src="images/kontact_journal.png" border="0"><br/>Bodovi + nepoloženi ispiti</a></td></tr>
		</table>
	</td><td width="10" valign="top">&nbsp;
	</td><td width="590" valign="top">
	<?


	// Osnovni podaci

	$q200 = myquery("select ime,prezime,email,brindexa from student where id=$student");
	if (!($r200 = mysql_fetch_row($q200))) {
		niceerror("Nepostojeći student!");
		return;
	}
	?>
	<?=genform("POST")?>
	<input type="hidden" name="subakcija" value="podaci">
	<table width="100%" border="0"><tr>
		<td>Ime:<br/> <input type="text" size="10" name="ime" value="<?=$r200[0]?>"></td>
		<td>Prezime:<br/> <input type="text" size="10" name="prezime" value="<?=$r200[1]?>"></td>
		<td>E-mail:<br/> <input type="text" size="10" name="email" value="<?=$r200[2]?>"></td>
		<td>Broj indexa:<br/> <input type="text" size="10" name="brindexa" value="<?=$r200[3]?>"></td>
		<td><input type="Submit" value=" Izmijeni "></td>
	</tr></form>
	<tr><td colspan="5"><br/></td></tr>
	<?

	$q201 = myquery("select login,password from auth where id=$student");
	if (!($r201 = mysql_fetch_row($q201))) $auth=0; else $auth=1;


	// Login&password

	?>
	<?=genform("POST")?>
	<input type="hidden" name="subakcija" value="auth">
	<tr>
		<td colspan="2">Korisnički pristup: <? if(!$auth) print '<font color="red">NEMA</font>'; ?></td>
		<td>Korisničko ime:<br/> <input type="text" size="10" name="login" value="<?=$r201[0]?>"></td>
		<td>Šifra:<br/> <input type="password" size="10" name="password" value="<?=$r201[1]?>"></td>
		<td><input type="Submit" value="<? if($auth) print ' Izmijeni '; else print ' Dodaj '?>"></td>
	</tr></table></form>
	<?


	// Trenutno sluša

	$q202 = myquery("select id,naziv from akademska_godina order by naziv desc");
	$r202 = mysql_fetch_row($q202);
	
	$q203 = myquery("select p.id,p.naziv from predmet as p, labgrupa as l, student_labgrupa as sl where sl.student=$student and sl.labgrupa=l.id and l.predmet=p.id and p.akademska_godina=$r202[0]");
	if (mysql_num_rows($q203)>0)
		print "<b>Trenutno sluša ($r202[1]):</b>\n<ul>\n";
	while ($r203 = mysql_fetch_row($q203))
		print "<li><a href=\"".genuri()."&tab=Predmeti&akcija=edit&predmet=$r203[0]\">$r203[1]</a></li>\n";
	print "</ul>\n";


	// Upis na predmet

	print "<p>Upis studenta na predmet:\n";
	print genform("POST");
	print '<input type="hidden" name="subakcija" value="upisi">';
	$_lv_["where:akademska_godina"] = $r202[0];
	print db_dropdown("predmet");
	print '<input type="submit" value=" Upiši "></form></p>';


	// Ranije slušao

	print "<b>Odslušao/la:</b>\n<ul>\n";
	while ($r202 = mysql_fetch_row($q202)) {
		$q204 = myquery("select p.id,p.naziv from predmet as p, labgrupa as l, student_labgrupa as sl where sl.student=$student and sl.labgrupa=l.id and l.predmet=p.id and p.akademska_godina=$r202[0]");
		while ($r204 = mysql_fetch_row($q204)) {
			print "<li><a href=\"".genuri()."&tab=Predmeti&akcija=edit&predmet=$r204[0]\">$r204[1] ($r202[1])</a> ";
			$q205 = myquery("select ocjena from konacna_ocjena where student=$student and predmet=$r204[0]");
			if ($r205 = mysql_fetch_row($q205)) 
				if ($r205[0]>5)
					print "(Ocjena: $r205[0])";
				else
					print "(Nije položio/la)";
			else
				print "(Nije položio/la)";
		}
	}


	?></td></tr></table></center><? // Vanjska tabela


}

else if ($tab == "Studenti") {
	$src = my_escape($_REQUEST["search"]);
	$limit = 20;
	$offset = intval($_REQUEST["offset"]);

	?>
	<center>
	<table width="500" border="0"><tr><td align="left">
		<p><b>Pretraži studente</b><br/>
		Unesite dio imena i prezimena ili broj indeksa:<br/>
		<?=genform("POST")?>
		<input type="hidden" name="offset" value="0"> <?/*resetujem offset*/?>
		<input type="text" size="50" name="search" value="<? if ($src!="sve") print $src?>"> <input type="Submit" value=" Pretraži "></form>
		<a href="<?=genuri()?>&search=sve">Prikaži sve studente</a><br/><br/>
	<?
	if ($src) {
		if ($src == "sve") {
			$q100 = myquery("select count(*) from student");
			$q101 = myquery("select id,ime,prezime,brindexa from student order by prezime limit $offset,$limit");
		} else {
			$q100 = myquery("select count(*) from student where ime like '%$src%' or prezime like '%$src%' or brindexa like '%$src%'");
			$q101 = myquery("select id,ime,prezime,brindexa from student where ime like '%$src%' or prezime like '%$src%' order by prezime limit $offset,$limit");
		}
		$rezultata = mysql_result($q100,0,0);
		if ($rezultata == 0)
			print "Nema rezultata!";
		else if ($rezultata>$limit) {
			print "Prikazujem rezultate ".($offset+1)."-".($offset+20)." od $rezultata. Stranica: ";

			for ($i=0; $i<$rezultata; $i+=$limit) {
				$br = intval($i/$limit)+1;
				if ($i==$offset)
					print "<b>$br</b>&nbsp;";
				else
					print "<a href=\"".genuri()."&offset=$i\">$br</a>&nbsp;";
			}
			print "<br/>";
		}
//		else
//			print "$rezultata rezultata:";

		print "<br/>";

		print '<table width="100%" border="0">';
		$i=$offset+1;
		while ($r101 = mysql_fetch_row($q101)) {
			print "<tr><td>$i. $r101[2] $r101[1] ($r101[3])</td><td><a href=\"".genuri()."&akcija=edit&student=$r101[0]\">Detalji</a></td></tr>";
			$i++;
		}
		print "</table>";
	}
	print "</table></center>\n";
}




?>
</td>
</tr>
</table>
<?




}

?>
