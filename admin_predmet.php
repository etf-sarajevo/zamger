<?

// * v2.9.3.0 (?) Zamger3 RC1
// v2.9.3.1 (2007/03/11) + libvedran 0.0.5, input validation
// v2.9.3.2 (2007/03/13) + editovanje imena grupe
// v2.9.3.3 (2007/03/16) + rucno ubacivanje zadataka u zadacu, nova labela za broj zadataka
// v2.9.3.4 (2007/03/26) + novo ime za genform() kod dodavanja ispita
// v3.0.0.0 (2007/04/09) + Release
// v3.0.0.1 (2007/04/25) + Rezultati ispita: konvertuj decimalni zarez u tačku, 
// ispravka greške u SQL upitu, nova imena varijabli za datum, ispravljeno više 
// semantičkih grešaka, dodana provjera za ponavljanje studenata u rezultatima
// v3.0.0.2 (2007/05/04) + Kompaktovanje baze
// v3.0.0.3 (2007/05/24) + Ispravka greške do koje je došlo zbog prelaska na FROM_UNIXTIME
// v3.0.1.0 (2007/06/12) + Release
// v3.0.1.1 (2007/09/11) + U tabeli ispitocjena sada je razdvojen prvi i drugi parcijalni, naziv se ignoriše; dodan unos konačne ocjene; poništena vrijednost varijable fakatradi kod masovnih unosa; izbačeno kompaktovanje (to će biti u siteadminu)
// v3.0.1.2 (2007/09/20) + Dodano dugme Nazad na sve ekrane za potvrdu (Usability), korištenje rtrim() u masovnom unosu


function admin_predmet() {

global $userid;

global $_lv_; // We use form generators


# Vrijednosti

$predmet=intval($_GET['predmet']);
if ($predmet==0) $predmet=intval($_POST['predmet']);
if ($predmet==0) { niceerror("Nije izabran predmet."); return; }

$q1 = myquery("select naziv from predmet where id=$predmet");
$predmet_naziv = mysql_result($q1,0,0);

$tab=$_GET['tab'];
if ($tab=="") $tab=$_POST['tab'];
if ($tab=="") $tab="Opcije";


###############
# Akcije
###############


# Dodaj grupu

if ($_POST['akcija'] == "nova_grupa") {
	$q2 = myquery("insert into labgrupa set naziv='".my_escape($_POST['ime'])."', predmet=$predmet");
} 


# Obrisi grupu

if ($_GET['akcija'] == "obrisi_grupu") {
	$grupaid = intval($_GET['grupaid']);
	$q10 = myquery("delete from labgrupa where id=$grupaid");
	$q11 = myquery("delete from student_labgrupa where labgrupa=$grupaid");
}


# Promjena imena grupe

if ($_POST['akcija'] == "preimenuj_grupu") {
	$grupaid = intval($_POST['grupaid']);
	$ime = my_escape($_POST['ime']);
	$q10 = myquery("update labgrupa set naziv='$ime' where id=$grupaid");
	// Grupa treba ostati otvorena:
	$_GET['akcija']="studenti_grupa";
	$_GET['grupaid']=$grupaid;
}


# Kopiraj grupe

if ($_POST['akcija'] == "kopiraj_grupe") {
	$kopiraj = intval($_POST['_lv_column_predmet']);
	$q20 = myquery("select count(*) from labgrupa where predmet=$kopiraj");
	if (mysql_result($q20,0,0) == 0) {
		niceerror("Nisu definisane grupe za ovaj predmet.");
	}
	$q21 = myquery("insert into labgrupa select 0,naziv,$predmet from labgrupa where predmet=$kopiraj");
	$q22 = myquery("select id,naziv from labgrupa where predmet=$predmet");
	while ($r22 = mysql_fetch_row($q22)) {
		$q23 = myquery("select id from labgrupa where predmet=$kopiraj and naziv='$r22[1]'");
		if (mysql_num_rows($q23)>0) {
			$origid = mysql_result($q23,0,0);
			$q24 = myquery("insert into student_labgrupa select student,$r22[0] from student_labgrupa where labgrupa=$origid");
		}
	}
}



# Masovni unos studenata u grupe

if ($_POST['akcija'] == "massinput") {
	$redovi = explode("\n",$_POST['massinput']);
	$tempid=1;

	$f = $_POST['fakatradi'];
	if ($f != 1) {
		print "Akcije koje će biti urađene:<br/><br/>\n";
		print genform("POST");
		print '<input type="hidden" name="fakatradi" value="1">';
	}

	foreach ($redovi as $red) {
		$red = rtrim($red);
		$red = my_escape($red);	
		if (strlen($red)>1) {
			# Parsiranje formata
			$format = $_POST['format'];
			if ($format == "A") {
				list($prezime,$ime,$grupa,$email,$brindexa) = explode("\t",$red);
			} else if ($format == "B") {
				list($imepr,$grupa,$email,$brindexa) = explode("\t",$red);
				list($prezime,$ime) = explode(" ",$imepr);
			} else if ($format == "C") {
				list($imepr,$grupa,$brindexa) = explode("\t",$red);
				list($prezime,$ime) = explode(" ",$imepr);
				$email = "";
			} else if ($format == "D") {
				list($imepr,$brindexa) = explode("\t",$red);
				list($prezime,$ime) = explode(" ",$imepr);
				$email = "";
			}

			# Da li student već postoji?
			$q30 = myquery("select id from student where ime='$ime' and prezime='$prezime'");
			if (mysql_num_rows($q30)>0) {
				$student = mysql_result($q30,0,0);
				$q30a = myquery("select l.id,l.naziv from student_labgrupa as sl, labgrupa as l where sl.student=$student and sl.labgrupa=l.id and l.predmet=$predmet");
				if (mysql_num_rows($q30a)>0) {
					$labgrupa = mysql_result($q30a,0,0);
					$lgnaziv = mysql_result($q30a,0,1);
					if ($f != 1) {
						print "Prebacivanje studenta '$prezime $ime' iz grupe '$lgnaziv' u grupu";
					} else {
						$q30b = myquery("delete from student_labgrupa where student=$student and labgrupa=$labgrupa");
					}
				} else {
					if ($f != 1) {
						print "Prijava studenta '$prezime $ime' u predmet '$predmet' grupa";
					}
				}
			} else {
				if ($f != 1) {
					print "Unos novog studenta '$prezime $ime' ($brindexa), prijava u predmet u predmet '$predmet' grupa";
				} else {
					$q31 = myquery("insert into student set ime='$ime', prezime='$prezime', email='$email', brindexa='$brindexa'");
					$q32 = myquery("select id from student where ime='$ime' and prezime='$prezime'");
					$student = mysql_result($q32,0,0);
				}
			}

			# Izbor grupe
			if ($format == "D") {
				# Format D - grupa nije navedena, koristi prvu
				$q33 = myquery("select id,naziv from labgrupa where predmet=$predmet order by id limit 1");
			} else {
				$q33 = myquery("select id,naziv from labgrupa where naziv='$grupa' and predmet=$predmet");
			}

			# Dodaj studenta u grupu ili ispisi, ovisno o $f
			if (mysql_num_rows($q33)==0) {
				if ($f != 1) print " --- Nepoznata grupa!!";
			} else {
				if ($f != 1)
					print " '".mysql_result($q33,0,1)."'";
				else 
					$q34 = myquery("insert into student_labgrupa set student=$student, labgrupa=".mysql_result($q33,0,0));
			}
			if ($f != 1) print "<br/>\n";
		}
	}
	if ($f != 1) {
		print '<input type="button" value=" Nazad " onClick="location.href=\'qwerty.php?sta=predmet&predmet='.$predmet.'&tab=Grupe\'"> <input type="submit" value=" Potvrda ">';
		print "</form>";
		return;
	}
}



# Masovni unos rezultata ispita

if ($_POST['akcija'] == "massexam") {
	$redovi = explode("\n",$_POST['massexam']);
	$tempid=1;

	$f = $_POST['fakatradi'];
	if ($f != 1) {
		print "Akcije koje će biti urađene:<br/><br/>\n";
		print genform("POST");
		print '<input type="hidden" name="fakatradi" value="1">';
	} else {
		# Registrovati ispit u bazi

		$naziv = my_escape($_POST['naziv']);
		$dan = intval($_POST['day']);
		$mjesec = intval($_POST['month']);
		$godina = intval($_POST['year']);
		$mdat = mktime(0,0,0,$mjesec,$dan,$godina);

		$q40 = myquery("insert into ispit set naziv='$naziv', predmet=$predmet, datum=FROM_UNIXTIME('$mdat')");
		$q41 = myquery("select id from ispit where naziv='$naziv' and predmet=$predmet and datum=FROM_UNIXTIME('$mdat')");
		if (mysql_num_rows($q41)<1) {
			niceerror("Unos ispita nije uspio.");
			return;
		} 
		$ispit = mysql_result($q41,0,0);
	}

	$prosli_idovi = array();

	foreach ($redovi as $red) {
		$red = rtrim($red);
		$red = my_escape($red);
		if (strlen($red)>1) {
			# Parsiranje formata
			$format = $_POST['format'];
			if ($format == "A") {
				list($imepr,$bodova) = explode("\t",$red);
				$bodova2 == -1;
				list($prezime,$ime) = explode(" ",$imepr);
			} else if ($format == "B") {
				list($imepr,$bodova2) = explode("\t",$red);
				$bodova == -1;
				list($prezime,$ime) = explode(" ",$imepr);
			} else if ($format == "C") {
				list($imepr,$bodova,$bodova2) = explode("\t",$red);;
				list($prezime,$ime) = explode(" ",$imepr);
			} else if ($format == "D") {
				list($prezime,$ime,$bodova) = explode("\t",$red);
				$bodova2 == -1;
			} else if ($format == "E") {
				list($prezime,$ime,$bodova2) = explode("\t",$red);
				$bodova == -1;
			} else if ($format == "F") {
				list($prezime,$ime,$bodova,$bodova2) = explode("\t",$red);
			}
			# pretvori $bodova u float uz obradu decimalnog zareza
			$bodova = floatval(str_replace(",",".",$bodova));
			$bodova2 = floatval(str_replace(",",".",$bodova2));

			# Da li student postoji?
			$q42 = myquery("select id from student where ime like '$ime' and prezime like '$prezime'");
			if (mysql_num_rows($q42)>0) {
				$student = mysql_result($q42,0,0);

				# Da li se isti student ponavlja dvaput?
				if (array_search($student, $prosli_idovi)) {
					if ($f != 1) {
						print "-- GREŠKA! Student '$prezime $ime' se ponavlja! (bodova: $bodova / $bodova2)<br/>";
					}
				} else {
					if ($f != 1) {
						print "Student '$prezime $ime' (ID: $student) - bodova: $bodova / $bodova2<br/>";
					} else {
						$q43 = myquery("insert into ispitocjene set ispit=$ispit, student=$student, ocjena=$bodova, ocjena2=$bodova2");
					}
				}
			} else {
				if ($f != 1) {
					print "-- GREŠKA! Nepoznat student '$prezime $ime'<br/>";
				}
			}
		}
	}
	if ($f != 1) {
		print '<input type="button" value=" Nazad " onClick="location.href=\'qwerty.php?sta=predmet&predmet='.$predmet.'&tab=Ispiti\'"> <input type="submit" value=" Potvrda">';
		print "</form>";
		return;
	}
}






# Masovni unos konačnih ocjena

if ($_POST['akcija'] == "massocjena") {
	$redovi = explode("\n",$_POST['massocjena']);
	$tempid=1;

	$f = $_POST['fakatradi'];
	if ($f != 1) {
		print "Akcije koje će biti urađene:<br/><br/>\n";
		print genform("POST");
		print '<input type="hidden" name="fakatradi" value="1">';
	} else {

	}

	$prosli_idovi = array();

	foreach ($redovi as $red) {
		$red = rtrim($red);
		$red = my_escape($red);
		if (strlen($red)>1) {
			# Parsiranje formata
			$format = $_POST['format'];
			if ($format == "A") {
				list($imepr,$ocjena) = explode("\t",$red);
				list($prezime,$ime) = explode(" ",$imepr);
			} else if ($format == "B") {
				list($prezime,$ime,$ocjena) = explode("\t",$red);
			}
			# pretvori $ocjenu u int
			$ocjena = intval($ocjena);

			# Da li student postoji?
			$q42 = myquery("select id from student where ime like '$ime' and prezime like '$prezime'");
			if (mysql_num_rows($q42)>0) {
				$student = mysql_result($q42,0,0);

				# Da li se isti student ponavlja dvaput?
				if (array_search($student, $prosli_idovi)) {
					if ($f != 1) {
						print "-- GREŠKA! Student '$prezime $ime' se ponavlja! (ocjena: $ocjena)<br/>";
					}
				} else {
					if ($f != 1) {
						print "Student '$prezime $ime' (ID: $student) - ocjena: $ocjena<br/>";
					} else {
						$q43 = myquery("insert into konacna_ocjena set student=$student, predmet=$predmet, ocjena=$ocjena");
					}
				}
			} else {
				if ($f != 1) {
					print "-- GREŠKA! Nepoznat student '$prezime $ime'<br/>";
				}
			}
		}
	}
	if ($f != 1) {
		print '<input type="button" value=" Nazad " onClick="location.href=\'qwerty.php?sta=predmet&predmet='.$predmet.'&tab=Ocjena\'"> <input type="submit" value=" Potvrda">';
		print "</form>";
		return;
	}
}


# Dodavanje zadataka u zadaću

/*if ($_GET['akcija']=="dodaj_zadatke") {
	$brojzad = 0;

	// _lv_nav_id bi trebao biti ID zadaće
	$zadaca = intval($_GET['zadaca']);
	$q50 = myquery("select zadataka from zadaca where id=$zadaca");
	if (mysql_num_rows($q50)>0) $brojzad = mysql_result($q50,0,0);
	
	$q51 = myquery("select sl.student from student_labgrupa as sl, labgrupa as l where l.predmet=$predmet and l.id=sl.labgrupa");
	while ($r51 = mysql_fetch_row($q51)) {
		for ($i=1; $i<=$brojzad; $i++) {
			$q52 = myquery("select id from zadatak where zadaca=$zadaca and redni_broj=$i and student=$r51[0] limit 1");
			if (mysql_num_rows($q52)==0) {
				$q53 = myquery("insert into zadatak set zadaca=$zadaca, redni_broj=$i, student=$r51[0], status=1, bodova=0, vrijeme=NOW()");
			}
		}
	}
	print "<p><b>Operacija izvršena:</b> Svim studentima su generisani zadaci iz izabrane zadaće sa statusom &quot;Novi zadatak&quot;.</p>\n";
	$_REQUEST['akcija']="";
}*/

###############
# Ispis tabova
###############


function printtab($ime,$predmet,$tab) {
	if ($ime==$tab) 
		print '<td bgcolor="#DDDDDD" width="50">'.$ime.'</td>'."\n";
	else
		print '<td bgcolor="#BBBBBB" width="50"><a href="qwerty.php?sta=predmet&predmet='.$predmet.'&tab='.$ime.'">'.$ime.'</a></td>'."\n";
}

?>
<script language="JavaScript">
function upozorenje(url) {
	var a = confirm("Svi studenti iz ove grupe će biti ispisani sa predmeta.");
	if (a)
		window.location=url;
}
</script>

<p><h3><?=$predmet_naziv?></h3></p>

<table border="0" cellspacing="1" cellpadding="5" width="550">
<tr>
<td width="50">&nbsp;</td>
<? 
printtab("Opcije",$predmet,$tab); 
printtab("Grupe",$predmet,$tab); 
printtab("Ispiti",$predmet,$tab); 
printtab("Zadaće",$predmet,$tab); 
printtab("Kvizovi",$predmet,$tab); 
printtab("Ocjena",$predmet,$tab); 
?>
<td bgcolor="#BBBBBB" width="50"><a href="qwerty.php">Nazad</a></td>
<td width="150">&nbsp;</td>
</tr>
<tr>
<td width="50">&nbsp;</td>
<td colspan="8" bgcolor="#DDDDDD" width="500">
<?



# Opšta konfiguracija

if ($tab == "Opcije") {
	$_lv_["label:naziv"] = "Naziv predmeta";
	$_lv_["label:akademska_godina"] = "Akademska godina";
	$_lv_["label:aktivan"] = "Predmet je aktivan (vidljiv studentima)";
	$_lv_["label:motd"] = "Obavještenja za studente (na vrhu Status stranice)";
	$_lv_["where:id"] = "$predmet";
	$_lv_["forceedit"]=1;

	print db_form("predmet");
}



# Konfiguracija grupa

if ($tab == "Grupe") {
	print "Spisak grupa:<br/>\n";
	$q100 = myquery("select id,naziv from labgrupa where predmet=$predmet order by id");

	# Spisak grupa
	print "<ul>\n";
	if (mysql_num_rows($q100) == 0)
		print "<li>Nema definisanih grupa</li>\n";
	while ($r100 = mysql_fetch_row($q100)) {
		$grupa = $r100[0];
		$naziv = $r100[1];

		print "<li>$naziv - ";

		$q101 = myquery("select count(*) from student_labgrupa where labgrupa=$grupa");
		$brstud = mysql_result($q101,0,0);
		print "(<a href=\"qwerty.php?sta=predmet&predmet=$predmet&tab=Grupe&akcija=studenti_grupa&grupaid=$grupa\">$brstud studenata</a>) - ";

		print "<a href=\"javascript:onclick=upozorenje('qwerty.php?sta=predmet&predmet=$predmet&tab=Grupe&akcija=obrisi_grupu&grupaid=$grupa')\">Obriši grupu</a>";

		print "</li>\n";
		if ($_GET['akcija']=="studenti_grupa" && $_GET['grupaid']==$grupa) {
			print "<ul>\n";
			$q102 = myquery("select student.id,student.prezime,student.ime from student_labgrupa,student where student_labgrupa.student=student.id and student_labgrupa.labgrupa=$grupa order by student.prezime");
			while ($r102 = mysql_fetch_row($q102)) {
				?><li><a href="#" onclick="javascript:window.open('qwerty.php?sta=student-izmjena&student=<?=$r102[0]?>&predmet=<?=$predmet?>','Podaci o studentu','width=300,height=200');"><? print $r102[1]." ".$r102[2]."</a></li>\n";
			}
			print "</ul>";
			$zapamti_grupu=$naziv;
		}
	}
	print "</ul>\n";

	# Editovanje grupe
	if ($_GET['akcija']=="studenti_grupa") {
		$gg = intval($_GET['grupaid']);
		# Dodavanje grupe
		print "<p>\n";
		print genform("POST");
		print '<input type="hidden" name="akcija" value="preimenuj_grupu">'."\n";
		print '<input type="hidden" name="grupaid" value="'.$gg.'">'."\n";
		print 'Promijenite naziv grupe: <input type="text" name="ime" size="20" value="'.$zapamti_grupu.'"> <input type="submit" value="Izmijeni"></form></p>'."\n";
	}

	# Dodavanje grupe
	print "<p>\n";
	print genform("POST");
	print '<input type="hidden" name="akcija" value="nova_grupa">'."\n";
	print 'Dodaj grupu: <input type="text" name="ime" size="20"> <input type="submit" value="Dodaj"></form></p>'."\n";

	# Kopiranje grupa sa predmeta
	$q103 = myquery("select akademska_godina from predmet where id=$predmet");
	$akgod = mysql_result($q103,0,0);
	print "<p>\n";
	print genform("POST");
	print '<input type="hidden" name="akcija" value="kopiraj_grupe">'."\n";
	print 'Prekopiraj grupe sa predmeta: '."\n";
	$_lv_["where:akademska_godina"] = "$akgod";
	print db_dropdown("predmet");
	print '<input type="submit" value="Dodaj">'."\n";
	print '</form></p>'."\n";

	# Masovni unos
	print '<p><hr/></p><p><b>Masovni unos studenata</b><br/>'."\n";
	print genform("POST");
	print '<input type="hidden" name="fakatradi" value="0">'; // poništi fakatradi
	print '<input type="hidden" name="akcija" value="massinput">'."\n";
	print '<br/>Izaberite format podataka:<br/>'."\n";
	print '<input type="radio" name="format" value="A" CHECKED> Prezime[TAB]Ime[TAB]Grupa[TAB]E-mail[TAB]Broj indexa<br/>'."\n";
	print ' <input type="radio" name="format" value="B"> Prezime Ime[TAB]Grupa[TAB]E-mail[TAB]Broj indexa<br/>'."\n";
	print ' <input type="radio" name="format" value="C"> Prezime Ime[TAB]Grupa[TAB]Broj indexa<br/>'."\n";
	print ' <input type="radio" name="format" value="D"> Prezime Ime[TAB]Broj indexa (svi će biti dodati u prvu grupu)<br/><br/>'."\n";
	print '<textarea name="massinput" cols="50" rows="10"></textarea><br/>'."\n";
	print '<input type="submit" value="  Dodaj  ">'."\n";
	print '</form></p>'."\n";

}



# Unos ispita

if ($tab == "Ispiti") {
	print "Uneseni ispiti:<br/>\n";
	$q110 = myquery("select id,naziv,UNIX_TIMESTAMP(datum) from ispit where predmet=$predmet");
	print "<ul>\n";
	if (mysql_num_rows($q110)<1)
		print "<li>Nije unesen nijedan ispit.</li>";
	while ($r110 = mysql_fetch_row($q110)) {
		print '<li><a href="qwerty.php?sta=statistika&ispit='.$r110[0].'">'.$r110[1].' ('.date("d. m. Y.",$r110[2]).')</a></li>'."\n";
	}
	print "</ul>\n";

	# Masovni unos rezultata ispita
	print '<p><hr/></p>'."\n";
	print '<p><b>Masovni unos rezultata ispita</b><br/>'."\n";
	print genform("POST");
	print '<input type="hidden" name="fakatradi" value="0">'; // poništi fakatradi
	print '<input type="hidden" name="akcija" value="massexam">'."\n";

	print '<br/>Naziv ispita: <input type="text" name="naziv" size="20"><br/><br/>'."\n";
	print 'Datum: '.datectrl(date('d'),date('m'),date('Y'))."<br/><br/>\n";

	print 'Izaberite format podataka:<br/>'."\n";
	print '<input type="radio" name="format" value="A"> Prezime Ime[TAB]I parcijalni<br/>'."\n";
	print '<input type="radio" name="format" value="B"> Prezime Ime[TAB]II parcijalni<br/>'."\n";
	print '<input type="radio" name="format" value="C" CHECKED> Prezime Ime[TAB]I parcijalni[TAB]II parcijalni<br/>'."\n";
	print '<input type="radio" name="format" value="D"> Prezime[TAB]Ime[TAB]I parcijalni<br/>'."\n";
	print '<input type="radio" name="format" value="E"> Prezime[TAB]Ime[TAB]II parcijalni<br/>'."\n";
	print '<input type="radio" name="format" value="F"> Prezime[TAB]Ime[TAB]I parcijalni[TAB]II parcijalni<br/>'."\n";
	print "<br/>\n";
	print '<textarea name="massexam" cols="50" rows="10"></textarea><br/>'."\n";
	print '<input type="submit" value="  Dodaj  ">'."\n";
	print '</form></p>'."\n";
}




# Unos i podešavanje zadaća

if ($tab == "Zadaće") {
	$_lv_["where:predmet"] = $predmet;

	# Prikaz unesenih zadaća
	print "Unesene zadaće:<br/>\n";
	print db_list("zadaca");

	$izabrana = intval($_REQUEST['_lv_nav_id']);
	if ($izabrana==0) {
		?><p><hr/></p>
		<p><b>Unos nove zadaće</b><br/>
		<?
	} else {
		?><p><hr/></p>
		<p><b>Izmjena zadaće</b></p>
		<?
	}

	$_lv_["label:programskijezik"] = "Programski jezik";
	$_lv_["label:zadataka"] = "Broj zadataka";
	$_lv_["label:bodova"] = "Max. broj bodova";
	$_lv_["label:attachment"] = "Slanje zadatka u formi attachmenta";
	$_lv_["label:rok"] = "Rok za slanje";
	print db_form("zadaca");
}




// Kvizovi!

if ($tab == "Kvizovi") {
	print "<ul><b>Nije još implementirano... Sačekajte sljedeću verziju :)</b></ul>\n";
}




// Konačna ocjena

if ($tab == "Ocjena") {
	print "<p>Unos konačnih ocjena za predmet.</p>\n";
	print genform("POST");
	print '<input type="hidden" name="fakatradi" value="0">'; // poništi fakatradi
	print '<input type="hidden" name="akcija" value="massocjena">'."\n";
	print 'Izaberite format podataka:<br/>'."\n";
	print '<input type="radio" name="format" value="A" CHECKED> Prezime Ime[TAB]Ocjena<br/>'."\n";
	print '<input type="radio" name="format" value="B"> Prezime[TAB]Ime[TAB]Ocjena<br/>'."\n";
	print "<br/>\n";
	print '<textarea name="massocjena" cols="50" rows="10"></textarea><br/>'."\n";
	print '<input type="submit" value="  Dodaj  ">'."\n";
	print '</form></p>'."\n";
}




?>
</td>
</tr>
</table>
<?

}

?>