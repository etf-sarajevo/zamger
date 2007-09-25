<?

// v3.0.1.1 (2007/09/11) + Novi modul "Nihada" za unos i pristup podataka o studentima, nastavnicima, loginima itd. Trenutno implementirana samo pretraga studenata i neki izvještaji
// v3.0.1.2 (2007/09/20) + Izdvojeni izvjestaji u modul "izvjestaj"
// v3.0.1.3 (2007/09/25) + Dodana kartica Predmeti, izbacena kartica Korisnici (to je integrisano sa Studenti odnosno Nastavnici), razmaci u broju rezultata na kartici Studenti


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
printtab("Predmeti", $tab);
?>
<td bgcolor="#BBBBBB" width="50"><a href="qwerty.php">Nazad</a></td>
<td width="350">&nbsp;</td>
</tr>
<tr>
<td width="50">&nbsp;</td>
<td colspan="7" bgcolor="#EEEEEE" width="750">
<?


//------------------------------
//   STUDENTI
//------------------------------


if ($tab == "Studenti" && $akcija == "edit") {
	$student = intval($_REQUEST['student']);

	print "<a href=\"".genuri()."&akcija=\">Nazad na rezultate pretrage</a><br/><br/>";
	

	// Submit akcije

	if ($_POST['subakcija'] == "podaci") {
		$ime = my_escape($_POST['ime']);
		$prezime = my_escape($_POST['prezime']);
		$email = my_escape($_POST['email']);
		$brindexa = my_escape($_POST['brindexa']);

		$q190 = myquery("update student set ime='$ime', prezime='$prezime', email='$email', brindexa='$brindexa' where id=$student");
	}
	if ($_POST['subakcija'] == "auth") {
		$login = my_escape($_POST['login']);
		$password = my_escape($_POST['password']);

		$q191 = myquery("select count(*) from auth where id=$student");
		if (mysql_result($q191,0,0) < 1) {
			$q192 = myquery("insert into auth set id=$student, login='$login', password='$password'");
		} else {
			$q193 = myquery("update auth set login='$login', password='$password' where id=$student");
		}
	}
	if ($_POST['subakcija'] == "upisi") {
		$predmet = intval($_POST['_lv_column_predmet']);
		$q193 = myquery("select id from labgrupa where predmet=$predmet order by id limit 1");
		if (mysql_num_rows($q193) < 1) {
			$q194 = myquery("insert into labgrupa set predmet=$predmet, naziv='Default grupa'");
			$q193 = myquery("select id from labgrupa where predmet=$predmet order by id limit 1");
		}
		$labgrupa = mysql_result($q193,0,0);
		$q195 = myquery("insert into student_labgrupa set student=$student, labgrupa=$labgrupa");
	}

	// Izvjestaji

	?>
	<center>
	<table width="700" border="0" cellspacing="0" cellpadding="0"><tr><td width="100" valign="top">
		<table width="100%" border="1" cellspacing="0" cellpadding="0">
			<tr><td bgcolor="#777777" align="center">
				<font color="white"><b>IZVJEŠTAJI:</b></font>
			</td></tr>
			<tr><td align="center"><a href="qwerty.php?sta=izvjestaj&tip=index&student=<?=$student?>">
			<img src="images/kontact_journal.png" border="0"><br/>Indeks</a></td></tr>
			<tr><td align="center"><a href="qwerty.php?sta=izvjestaj&tip=progress&student=<?=$student?>&razdvoji_ispite=0">
			<img src="images/kontact_journal.png" border="0"><br/>Bodovi</a></td></tr>
			<tr><td align="center"><a href="qwerty.php?sta=izvjestaj&tip=progress&student=<?=$student?>&razdvoji_ispite=1">
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


	// Login&password

	$q201 = myquery("select login,password from auth where id=$student");
	if (!($r201 = mysql_fetch_row($q201))) $auth=0; else $auth=1;

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
			$dijelovi = explode(" ", $src);
			$query = "";
			foreach($dijelovi as $dio) {
				if ($query != "") $query .= "or ";
				$query .= "ime like '%$dio%' or prezime like '%$dio%' or brindexa like '%$dio%' ";
			}
			$q100 = myquery("select count(*) from student where $query");
			$q101 = myquery("select id,ime,prezime,brindexa from student where $query order by prezime limit $offset,$limit");
		}
		$rezultata = mysql_result($q100,0,0);
		if ($rezultata == 0)
			print "Nema rezultata!";
		else if ($rezultata>$limit) {
			print "Prikazujem rezultate ".($offset+1)."-".($offset+20)." od $rezultata. Stranica: ";

			for ($i=0; $i<$rezultata; $i+=$limit) {
				$br = intval($i/$limit)+1;
				if ($i==$offset)
					print "<b>$br</b> ";
				else
					print "<a href=\"".genuri()."&offset=$i\">$br</a> ";
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


//------------------------------
//   STUDENTI
//------------------------------

else if ($tab == "Predmeti" && $akcija == "edit") {
	$predmet = intval($_REQUEST['predmet']);

	$oag = intval($_REQUEST['old_akademska_godina']);
	print "<a href=\"".genuri()."&akcija=&_lv_column_akademska_godina=$oag\">Nazad na rezultate pretrage</a><br/><br/>";

	// Izvjestaji

	?>
	<center>
	<table width="700" border="0" cellspacing="0" cellpadding="0"><tr><td width="100" valign="top">
		<table width="100%" border="1" cellspacing="0" cellpadding="0">
			<tr><td bgcolor="#777777" align="center">
				<font color="white"><b>IZVJEŠTAJI:</b></font>
			</td></tr>
			<tr><td align="center"><a href="qwerty.php?sta=izvjestaj&tip=grupe&predmet=<?=$predmet?>">
			<img src="images/kontact_journal.png" border="0"><br/>Spisak grupa</a></td></tr>
			<tr><td align="center"><a href="qwerty.php?sta=izvjestaj&tip=predmet_full&predmet=<?=$predmet?>">
			<img src="images/kontact_journal.png" border="0"><br/>Puni izvještaj</a></td></tr>
		</table>
	</td><td width="10" valign="top">&nbsp;
	</td><td width="590" valign="top">
	<?
	

	// Submit akcije

	if ($_POST['subakcija'] == "dodaj") {
		$nastavnik = intval($_POST['_lv_column_nastavnik']);
		if ($nastavnik>0) {
			$q360 = myquery("select count(*) from nastavnik_predmet where nastavnik=$nastavnik and predmet=$predmet");
			if (mysql_result($q360,0,0) < 1) {
				$q361 = myquery("insert into nastavnik_predmet set nastavnik=$nastavnik, predmet=$predmet");
			}
		}
	}
	else if ($_GET['subakcija'] == "set_admin") {
		$nastavnik = intval($_GET['nastavnik']);
		$yesno = intval($_GET['yesno']);
		$q362 = myquery("update nastavnik_predmet set admin=$yesno where nastavnik=$nastavnik and predmet=$predmet");
	}
	else if ($_GET['subakcija'] == "izbaci") {
		$nastavnik = intval($_GET['nastavnik']);
		$q363 = myquery("delete from nastavnik_predmet where nastavnik=$nastavnik and predmet=$predmet");
	}


	// Osnovni podaci

	$q350 = myquery("select naziv,akademska_godina from predmet where id=$predmet");
	if (!($r350 = mysql_fetch_row($q350))) {
		niceerror("Nepostojeći predmet!");
		return;
	}
	?>
	<?=genform("POST")?>
	<input type="hidden" name="subakcija" value="podaci">
	<table width="100%" border="0"><tr>
		<td>Naziv predmeta<br/> <input type="text" size="40" name="naziv" value="<?=$r350[0]?>"></td>
		<td>Akademska godina:<br/> <?=db_dropdown("akademska_godina", $r350[1])?></td>
		<td><input type="Submit" value=" Izmijeni "></td>
	</tr></table></form>
	<?


	// Nastavnici na predmetu

	print "<p>Nastavnici angažovani na predmetu:</p>\n";
	$q351 = myquery("select np.nastavnik,np.admin,n.ime,n.prezime from nastavnik as n, nastavnik_predmet as np where np.nastavnik=n.id and np.predmet=$predmet");
	if (mysql_num_rows($q351) < 1) {
		print "<ul><li>Nema nastavnika</li></ul>\n";
	} else {
		?>
		<script language="JavaScript">
			function firefox_go(url) {
//alert("Hello world!");
				window.location = url;
			}
		</script>
		<table width="100%" border="1" cellspacing="0"><tr><td>Ime i prezime</td><td>Administrator predmeta</td><td>Ograničenja</td><td>&nbsp;</td></tr><?
	}
	while ($r351 = mysql_fetch_row($q351)) {
		print '<tr><td><a href="qwerty.php?sta=nihada&tab=Nastavnici&akcija=edit&nastavnik='.$r351[0].'">'.$r351[2].' '.$r351[3].'</td>'."\n";

		print '<td><input type="checkbox" onchange="javascript:location.href=\'';
		print genuri()."&subakcija=set_admin&nastavnik=$r351[0]&yesno=";
		if ($r351[1]==1) 
			print "0'\" CHECKED></td>\n"; 
		else 
			print "1'\"></td>\n";

		print '<td><a href="'.genuri().'&subakcija=ogranicenja&nastavnik='.$r351[0].'">';
		$q352 = myquery("select l.naziv from ogranicenje as o, labgrupa as l where o.nastavnik=$r351[0] and o.labgrupa=l.id and l.predmet=$predmet");
		if (mysql_num_rows($q352)<1)
			print "Nema";
		while ($r352 = mysql_fetch_row($q352)) {
			print substr($r352[0],0,15).", ";
		}
		print "</a></td>"."\n";

		print "<td><a href=\"".genuri()."&subakcija=izbaci&nastavnik=$r351[0]\">Izbaci</a></td></tr>"."\n";
	}
	if (mysql_num_rows($q351) > 0) {
		print "</table>\n";
	}


	// Dodaj nove nastavnike

	print "<p>Angažman nastavnika na predmetu:\n";
	print genform("POST");
	print '<input type="hidden" name="subakcija" value="dodaj">';
	print db_dropdown("nastavnik");
	print '<input type="submit" value=" Dodaj "></form></p>';

	?></td></tr></table></center><? // Vanjska tabela

}


else if ($tab == "Predmeti") {
	$src = my_escape($_REQUEST["search"]);
	$limit = 20;
	$offset = intval($_REQUEST["offset"]);
	$ak_god = intval($_REQUEST["_lv_column_akademska_godina"]);
	if ($ak_god == 0) {
		$q299 = myquery("select id from akademska_godina order by naziv desc limit 1");
		$ak_god = mysql_result($q299,0,0);
	}

	?>
	<center>
	<table width="500" border="0"><tr><td align="left">
		<p><b>Pretraga</b><br/>
		Za prikaz svih predmeta na akademskoj godini, ostavite polje za pretragu prazno.</br>
		<?=genform("POST")?>
		<input type="hidden" name="offset" value="0"> <?/*resetujem offset*/?>
		<?=db_dropdown("akademska_godina",$ak_god, "Sve akademske godine");?>
		<input type="text" size="50" name="search" value="<? if ($src!="") print $src?>"> <input type="Submit" value=" Pretraži "></form>
		<br/><br/>
	<?
	if ($ak_god>0 && $src != "") {
		$q300 = myquery("select count(*) from predmet where akademska_godina=$ak_god and naziv like '%$src%'");
	} else if ($ak_god>0) {
		$q300 = myquery("select count(*) from predmet where akademska_godina=$ak_god");
	} else if ($src != "") {
		$q300 = myquery("select count(*) from predmet where naziv like '%$src%'");
	} else {
		$q300 = myquery("select count(*) from predmet");
	}
	$rezultata = mysql_result($q300,0,0);

	if ($rezultata == 0)
		print "Nema rezultata!";
	else {
		if ($rezultata>$limit) {
			print "Prikazujem rezultate ".($offset+1)."-".($offset+20)." od $rezultata. Stranica: ";
	
			for ($i=0; $i<$rezultata; $i+=$limit) {
				$br = intval($i/$limit)+1;
				if ($i==$offset)
					print "<b>$br</b> ";
				else
					print "<a href=\"".genuri()."&offset=$i\">$br</a> ";
			}
			print "<br/>";
		}
		print "<br/>";

		if ($ak_god>0 && $src != "") {
			$q301 = myquery("select p.id, p.naziv, ag.naziv from predmet as p, akademska_godina as ag where p.akademska_godina=ag.id and ag.id=$ak_god and p.naziv like '%$src%' order by ag.naziv desc, p.naziv");
		} else if ($ak_god>0) {
			$q301 = myquery("select p.id, p.naziv, ag.naziv from predmet as p, akademska_godina as ag where p.akademska_godina=ag.id and ag.id=$ak_god order by ag.naziv desc, p.naziv");
		} else if ($src != "") {
			$q301 = myquery("select p.id, p.naziv, ag.naziv from predmet as p, akademska_godina as ag where p.akademska_godina=ag.id and p.naziv like '%$src%' order by ag.naziv desc, p.naziv");
		} else {
			$q301 = myquery("select p.id, p.naziv, ag.naziv from predmet as p, akademska_godina as ag where p.akademska_godina=ag.id order by ag.naziv desc,p.naziv");
		}

		print '<table width="100%" border="0">';
		$i=$offset+1;
		while ($r301 = mysql_fetch_row($q301)) {
			print "<tr><td>$i. $r301[1] ($r301[2])</td>\n";
			print "<td><a href=\"".genuri()."&old_akademska_godina=$ak_god&akcija=edit&predmet=$r301[0]\">Detalji</a></td>\n";
			print "<td><a href=\"qwerty.php?sta=predmet&predmet=$r301[0]\">Uređivanje predmeta</a></td></tr>";
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
