<?

function nastavnik_unos_kolicine_pred(){

global $userid,$user_siteadmin,$user_studentska;


//echo "<br><br><br>"; //za podesavanje visine odakle tabela pocinje
$agod = $_REQUEST["ag"];
$predmet = $_REQUEST["predmet"];
$action = $_REQUEST["action"];

// Provjera privilegija

if (!$user_studentska && !$user_siteadmin) {
	zamgerlog("nije studentska",3); // 3: error
	biguglyerror("Pristup nije dozvoljen.");
	return;
}

//ovim se provjerava da li se prvi put otvara ovaj modul, tj. da li je bilo nekih promjena vrsenih pomocu ovog modula
if($action == null or $action == "")
{
// ovo se izvrsava ako se 1. put otvara ovaj modul, ili ako se vraca na njega nakon nekih promjena
prikazi_tabele:{

/*$res = myquery("SELECT k.id, sifra, p.naziv AS predmet, ime, prezime, l.naziv AS labgrupa, k.sati_predavanja, k.sati_vjezbi, k.sati_tutorijala, ag.naziv AS godina
FROM predmet AS p
JOIN labgrupa AS l ON p.id = l.predmet
JOIN angazman AS a ON p.id = a.predmet
JOIN osoba AS o ON o.id = a.osoba
JOIN kolicina_predavanja as k ON k.osoba_id = o.id
JOIN akademska_godina AS ag ON ag.id = a.akademska_godina
WHERE ag.aktuelna = 1 AND l.id = k.labgrupa_id AND p.id = $predmet
ORDER BY ime ASC");*/ // ovo je query za 1. tabelu
$res = myquery("SELECT nl.id, p.naziv AS predmet, ime, prezime, l.naziv AS labgrupa, tip, ag.naziv AS godina
FROM predmet AS p
JOIN labgrupa AS l ON p.id = l.predmet
JOIN angazman AS a ON p.id = a.predmet
JOIN osoba AS o ON o.id = a.osoba
JOIN nastavnik_labgrupa AS nl ON nl.osoba_id = o.id
JOIN akademska_godina AS ag ON ag.id = a.akademska_godina
WHERE ag.aktuelna = 1 AND l.id = nl.labgrupa_id AND p.id = $predmet
ORDER BY ime ASC, l.naziv ASC"); // ovo je updateovan query za 1. tabelu (novi metod)

/*$res3 = myquery("SELECT o.id, p.naziv AS predmet, ime, prezime, l.naziv AS labgrupa 
FROM predmet AS p 
JOIN labgrupa AS l ON p.id = l.predmet
JOIN osoba AS o 
JOIN angazman AS a ON a.predmet = p.id and a.osoba = o.id
JOIN akademska_godina AS ag on l.akademska_godina = ag.id
WHERE p.id = $predmet AND ag.aktuelna = 1
GROUP BY ime");*/ // ovo je query za 2. tabelu

$res3 = myquery("SELECT o.id, p.naziv AS predmet, ime, prezime, l.naziv AS labgrupa 
FROM predmet AS p 
JOIN labgrupa AS l ON p.id = l.predmet
JOIN osoba AS o 
JOIN angazman AS a ON a.predmet = p.id and a.osoba = o.id
JOIN akademska_godina AS ag on l.akademska_godina = ag.id
WHERE p.id = $predmet AND ag.aktuelna = 1
GROUP BY ime"); // ovo je updateovan query za 1. tabelu (novi metod)
?>

<font size="3">Izmjena kolicine predavanja, vjezbi i tutorijala za predmet za pojedinacne nastavnike (koji predaju)</font>
<br><br>
	<table border="1" cellspacing="1" font size="big">
		<tr>
			<td>Naziv predmeta</td>
			<td>Akademska godina</td>
			<td>Ime</td>
			<td>Prezime</td>
			<td>Labgrupa</td>
			<td>Tip grupe</td>
			<!-- <td></td> -->
			<td></td>
		</tr>
	<?
		while($row = mysql_fetch_row($res)){
		$kol_id = $row[0];
		$naziv_pred = $row[1];
		$ak_god = $row[6];
		$ime = $row[2];
		$prezime = $row[3];
		$labgrupa = $row[4];
		$tip = $row[5];
		/*$br_pred = $row[6];
		$br_vj = $row[7];
		$br_tut = $row[8];
		if($br_pred == null or "")
			$br_pred = 0;
		if($br_vj == null or "")
			$br_vj = 0;
		if($br_tut == null or "")
			$br_tut = 0; */
	?>
		<form action="?sta=nastavnik/unos_kolicine_pred&predmet=<? echo $predmet ?>&ag=<? echo $agod ?>&action=edit" method="POST">
		<input type="hidden" name="akcija" value="edit_1">
		<tr>
			<input type="hidden" name="kol_id" value="<? echo $kol_id; ?>">
			<td><? echo $naziv_pred; ?></td>
			<td><? echo $ak_god; ?></td>
			<td><? echo $ime; ?></td>
			<td><? echo $prezime; ?></td>
			<td><? echo $labgrupa; ?></td>
			<td><select name="tip"><option value=\"<? echo $tip; ?>\"><? echo $tip; ?></a></td>
			<!--
			<td><select name="tip"><? /*
				switch($tip){
					case "predavanja":
						print "<option value=\"predavanja\">Predavanja</a><br><option value=\"vjezbe\">Vjezbe</a><br><option value=\"tutorijali\">Tutorijali</a><br><option value=\"vjezbe+tutorijali\">Vjezbe + tutorijali</a><br>";
						break;
					case "vjezbe":
						print "<option value=\"vjezbe\">Vjezbe</a><br><option value=\"predavanja\">Predavanja</a><br><option value=\"tutorijali\">Tutorijali</a><br><option value=\"vjezbe+tutorijali\">Vjezbe + tutorijali</a><br>";
						break;
					case "tutorijali":
						print "<option value=\"tutorijali\">Tutorijali</a><br><option value=\"predavanja\">Predavanja</a><br><option value=\"vjezbe\">Vjezbe</a><br><option value=\"vjezbe+tutorijali\">Vjezbe + tutorijali</a><br>";
						break;
					case "vjezbe+tutorijali":
						print "<option value=\"vjezbe+tutorijali\">Vjezbe + tutorijali</a><br><option value=\"predavanja\">Predavanja</a><br><option value=\"vjezbe\">Vjezbe</a><br><option value=\"tutorijali\">Tutorijali</a><br>";
						break;
				} */
			?>
				</select>
			</td>
			<td><input type="submit" value="Spasi postavke" /></td>
			-->
			<td><input name="delete" type="submit" value="Izbrisi postavke" /></td>
		</tr>
		</form>
		<?
		}	
		?>
	</table>
<br><br><br>
<!-- kraj tabele za editovanje kolicina predavanja pojedinacnih nastavnika -->


<font size="3">Unosenje kolicine predavanja, vjezbi i tutorijala za predmet za pojedinacne nastavnike (koji ne predaju)</font>
	<table border="1" cellspacing="1" font size="big">
		<tr>
			<td>Ime</td>
			<td>Prezime</td>
			<td>Labgrupa</td>
			<!--
			<td>Tip grupe</td>
			<td>Sati predavanja</td>
			<td>Sati vjezbi</td>
			<td>Sati tutorijala</td>
			-->
			<td></td>
		</tr>
		<?
			while($row = mysql_fetch_row($res3)){
				$ime = $row[2];
				$prezime = $row[3];
				$osoba_id = $row[0];
				$predmet_id = $predmet;
				$ak_godina = $agod;
				?>
				<script language="javascript">
					function setSelectValue (id, val) {
						document.getElementById(id).value = val;
					}
					function promjeniTip(obj) {
						//to do
					} 
				</script>
				
				<form action="?sta=nastavnik/unos_kolicine_pred&predmet=<? echo $predmet ?>&ag=<? echo $agod ?>&action=edit" method="POST">
				<input type="hidden" name="akcija" value="edit_2">
				<tr>
					<td><? echo $ime; ?></td>
					<td><? echo $prezime; ?></td>
					<input type="hidden" name="osoba_id" value="<? echo $osoba_id; ?>">
					<input type="hidden" name="predmet_id" value="<? echo $predmet_id; ?>">
					<input type="hidden" name="ak_godina" value="<? echo $ak_godina; ?>">
					<td>
						<select name="labgroup">
							<?
							$grupe = myquery("select l.id, l.naziv as labgrupa, p.naziv as predmet from labgrupa as l join predmet as p on l.predmet = p.id where p.id = $predmet");
							//query za izlistavanje labgrupa za trenutni predmet trenutnog nastavnika
							while($row = mysql_fetch_row($grupe)){
							$lab_id = $row[0];
							$lab_ime = $row[1];
								echo "<option value=\"$lab_id\">$lab_ime</option><br>";
							}
							
							?>
						</select>
					</td>
					<!--
					<td><input type="text" name="br_pred_nastavnik" value="<? echo $br_pred; ?>" /></td>
					<td><input type="text" name="br_vjezbi_nastavnik" value="<? echo $br_vj; ?>" /></td>
					<td><input type="text" name="br_tutorijala_nastavnik" value="<? echo $br_tut; ?>" /></td>
					<td><select name="tip">
							<option value="predavanja">Predavanja</a>
							<option value="vjezbe">Vjezbe</a>
							<option value="tutorijali">Tutorijali</a>
							<option value="vjezbe+tutorijali">Vjezbe + tutorijali</a>
						</select>
					</td>
					-->
					<td><input type="submit" value="Dodaj nastavnika" /></td>
					<!-- <td><? echo "<a href=\"\">Dodaj nastavnika</a>"; ?></td> -->
				</tr>
				</form>
				<br>
				<?
			}
		?>
	</table>
<br><br><br>
<!-- kraj tabele za dodavanje kolicina predavanja pojedinacnih nastavnika koji trenutno ne predaju -->



<font size="3">Mijenjanje kolicine predavanja, vjezbi i tutorijala za predmet</font>
<!-- tabela za editovanje kolicina predavanja za trenutno odabrani predmet -->
<form action="?sta=nastavnik/unos_kolicine_pred&predmet=<? echo $predmet ?>&ag=<? echo $agod ?>&action=edit" method="POST">
	<input type="hidden" name="akcija" value="edit_3">
	<table border="1" cellspacing="1" font size="big">
		<tr>
			<td>Sifra</td>
			<td>Naziv predmeta</td>
			<td>Sati predavanja</td>
			<td>Sati vjezbi</td>
			<td>Sati tutorijala</td>
		</tr>
	<?
		$predmet_query = myquery("SELECT sifra, naziv, sati_predavanja, sati_vjezbi, sati_tutorijala FROM predmet where id = $predmet order by id asc");
		 // ovo je query za 3. tabelu
		while($row = mysql_fetch_row($predmet_query)){
		$sifra = $row[0];
		$naziv = $row[1];
		$br_pred = $row[2];
		$br_vj = $row[3];
		$br_tut = $row[4];
		if($br_pred == null or "")
			$br_pred = 0;
		if($br_vj == null or "")
			$br_vj = 0;
		if($br_tut == null or "")
			$br_tut = 0;
	?>
		<tr>
			<td><? echo $sifra; ?></td>
			<td><? echo $naziv; ?></td>
			<td><input type="text" name="br_pred_predmet" value="<? echo $br_pred; ?>" /></td>
			<td><input type="text" name="br_vj_predmet" value="<? echo $br_vj; ?>" /></td>
			<td><input type="text" name="br_tut_predmet" value="<? echo $br_tut; ?>" /></td>
		<!-- <td><input type="submit" name="dugme" value="<? echo "Spasi"; ?>" /></td> -->
		</tr>
		<br>
	<?
	}
	?>
	</table>
	<input type="submit" value="Spasi postavke" />
</form>
<!--kraj tabele za editovanje kolicina predavanja za trenutno odabrani predmet -->


<?
//}//kraj zagrade za provjeru $error
}
} //kraj ispisa tabela prilikom 1. ulaska u modul ili vracanja nakon nekih izmjena

//sada se provjerava koja promjena treba da se izvrsi
else if($action == "edit"){
$akcija = $_POST['akcija'];

if($akcija == "edit_1"){
	//ovo se izvrsava ako se mijenjaju podaci za Sati predavanja pojedinacnih nastavnika (1. tabela)
	$greska = 0; // 0-nema greske, 1-vrijednost nula za varijablu(e), 2-broj predavanja za nastavnika veci od broja predavanja na predmetu
	$kol_id = $_POST['kol_id'];
	/*
	$br_predavanja_predmet =  mysql_result(myquery("SELECT sati_predavanja FROM predmet WHERE id = $predmet"),0,0);
	$br_pred = $_POST['br_pred'];
	if($br_pred > $br_predavanja_predmet)
		$greska1 = 2;
	else if($br_pred == '0' or NULL)
		$greska1 = 1;
	$br_vjezbi_predmet =  mysql_result(myquery("SELECT sati_vjezbi FROM predmet WHERE id = $predmet"),0,0);
	$br_vj = $_POST['br_vj'];
	if($br_vj > $br_vjezbi_predmet)
		$greska2 = 2;
	else if($br_vj == '0' or NULL)
		$greska2 = 1;
	$br_tutorijala_predmet = mysql_result(myquery("SELECT sati_tutorijala FROM predmet WHERE id = $predmet"),0,0);
	$br_tut = $_POST['br_tut'];
	if($br_tut > $br_tutorijala_predmet)
		$greska3 = 2;
	else if($br_tut == '0' or NULL)
		$greska3 = 1;
	if($greska1 == 1 AND $greska2 == 1 AND $greska3 == 1)
		$greska = 1;
	else if($greska1 == 2 OR $greska2 == 2 OR $greska3 == 2)
		$greska = 2;
	$query = FALSE;
	*/
	if(isset($_POST['delete'])){
		$query = myquery("DELETE FROM nastavnik_labgrupa WHERE id = $kol_id");
		if($query)
			echo "<br>Uspjesno ste izbrisali nastavniku predavanja. Kliknite <a href=\"?sta=nastavnik/unos_kolicine_pred&predmet=$predmet&ag=$agod\">OVDJE</a> za povratak.";
		else
			niceerror("Doslo je do greske prilikom brisanja unosa. Kliknite <a href=\"?sta=nastavnik/unos_kolicine_pred&predmet=$predmet&ag=$agod\">OVDJE</a> za povratak.");
	} else{
		switch($greska){
			case 0:
				$query = myquery("UPDATE kolicina_predavanja set sati_predavanja = $br_pred, sati_vjezbi = $br_vj, sati_tutorijala = $br_tut WHERE id = $kol_id");
				if($query)
					goto prikazi_tabele;
					//echo "<br>Uspjesno ste dodali nastavniku predavanja.Kliknite <a href=\"?sta=nastavnik/unos_kolicine_pred&predmet=$pred&ag=$agod\">OVDJE</a> za povratak.";
				else
					niceerror("Doslo je do greske prilikom izmjene podataka u bazi podataka.");
				break;
			case 1:
				niceerror("Doslo je do greske prilikom unosa podataka. Ne smiju sve 3 varijable imati vrijednost 0.<br>Kliknite <a href=\"?sta=nastavnik/unos_kolicine_pred&predmet=$pred&ag=$agod\">OVDJE</a> za povratak.");
				break;
			case 2:
				niceerror("Doslo je do greske prilikom unosa podataka. Broj predavanja/vjezbi/tutorijala za nastavnika ne smije biti veci od broja predavanja/vjezbi/tutorijala registrovanih za predmet.<br>Kliknite <a href=\"?sta=nastavnik/unos_kolicine_pred&predmet=$pred&ag=$agod\">OVDJE</a> za povratak.");
				break;
		}
	}
	/*if($query){
		goto prikazi_tabele;
	} else {
		myerror("Doslo je do greske prilikom izmjene podataka.");
	}*/
	//echo "<font size=\"10\">$kol_id</font>";
}
else if($akcija == "edit_2"){
	//ovo se izvrsava kada se nastavnicima dodaju kolicine predavanja po 1. put (2. tabela)
	$greska = 0; // 0-nema greske, 1-vrijednost nula za varijablu(e), 2-broj predavanja za nastavnika veci od broja predavanja na predmetu
	$osoba_id = $_POST['osoba_id'];
	$predmet_id = $_POST['predmet_id'];
	$labgrupa_id  = $_POST['labgroup'];
	$ak_godina = $_POST['ak_godina'];
	$tip = $_POST['tip'];
	/*
	$br_predavanja_predmet =  mysql_result(myquery("SELECT sati_predavanja FROM predmet WHERE id = $predmet"),0,0);
	$br_predavanja = $_POST['br_pred_nastavnik'];
	if($br_predavanja > $br_predavanja_predmet)
		$greska1 = 2;
	else if($br_predavanja == '0' or NULL)
		$greska1 = 1;
	$br_vjezbi_predmet =  mysql_result(myquery("SELECT sati_vjezbi FROM predmet WHERE id = $predmet"),0,0);
	$br_vjezbi = $_POST['br_vjezbi_nastavnik'];
	if($br_vjezbi > $br_vjezbi_predmet)
		$greska2 = 2;
	else if($br_vjezbi == '0' or NULL)
		$greska2 = 1;
	$br_tutorijala_predmet = mysql_result(myquery("SELECT sati_tutorijala FROM predmet WHERE id = $predmet"),0,0);
	$br_tutorijala = $_POST['br_tutorijala_nastavnik'];
	if($br_tutorijala > $br_tutorijala_predmet)
		$greska3 = 2;
	else if($br_tutorijala == '0' or NULL)
		$greska3 = 1;
	if($greska1 == 1 AND $greska2 == 1 AND $greska3 == 1)
		$greska = 1;
	else if($greska1 == 2 OR $greska2 == 2 OR $greska3 == 2)
		$greska = 2;
	switch($greska){
		case 0:
			$query = myquery("INSERT INTO kolicina_predavanja VALUES(null, '$osoba_id','$predmet_id','$labgrupa_id','$ak_godina','$br_predavanja','$br_vjezbi','$br_tutorijala')");
			if($query)
				echo "<br>Uspjesno ste dodali nastavniku predavanja.Kliknite <a href=\"?sta=nastavnik/unos_kolicine_pred&predmet=$pred&ag=$agod\">OVDJE</a> za povratak.";
			else
				niceerror("Doslo je do greske prilikom unosa u bazu podataka.");
			break;
		case 1:
			niceerror("Doslo je do greske prilikom unosa podataka. Ne smiju sve 3 varijable imati vrijednost 0.<br>Kliknite <a href=\"?sta=nastavnik/unos_kolicine_pred&predmet=$pred&ag=$agod\">OVDJE</a> za povratak.");
			break;
		case 2:
			niceerror("Doslo je do greske prilikom unosa podataka. Broj predavanja/vjezbi/tutorijala za nastavnika ne smije biti<br> veci od broja predavanja/vjezbi/tutorijala registrovanih za predmet.<br>Kliknite <a href=\"?sta=nastavnik/unos_kolicine_pred&predmet=$pred&ag=$agod\">OVDJE</a> za povratak.");
			break;
	}
	*/
	$uslov = 0;
	$postoji = myquery("SELECT count(id) AS br FROM nastavnik_labgrupa where osoba_id = $osoba_id and ak_godina = $ak_godina and labgrupa_id = $labgrupa_id group by osoba_id"); //query za odredjivanje da li ovaj unos vec postoji
	if(mysql_fetch_row($postoji))
		$uslov = intval(mysql_result($postoji,0,0)); 
	if($uslov > 0)
		niceerror("Doslo je do greske prilikom procesiranja unosa. Trazeni unos vec postoji u bazi podataka.<br>Molimo Vas da izaberete druge postavke. Kliknite <a href=\"?sta=nastavnik/unos_kolicine_pred&predmet=$predmet&ag=$agod\">OVDJE</a> za povratak.");
	else{
		$query = myquery("INSERT INTO nastavnik_labgrupa VALUES(null, '$osoba_id','$labgrupa_id','$ak_godina')");
		if($query)
			echo "<br>Uspjesno ste dodali nastavniku predavanja.Kliknite <a href=\"?sta=nastavnik/unos_kolicine_pred&predmet=$predmet&ag=$agod\">OVDJE</a> za povratak.";
		else
			niceerror("Doslo je do greske prilikom brisanja unosa. Kliknite <a href=\"?sta=nastavnik/unos_kolicine_pred&predmet=$predmet&ag=$agod\">OVDJE</a> za povratak.");
	}
}
else if($akcija == "edit_3"){
	//ovo se izvrsava ako se mijenjaju podaci za Sati predavanja predmeta (3. tabela)
	$br_pred = $_POST['br_pred_predmet'];
	$br_vj = $_POST['br_vj_predmet'];
	$br_tut = $_POST['br_tut_predmet'];
	$query = myquery("update predmet set sati_predavanja = $br_pred, sati_vjezbi = $br_vj, sati_tutorijala = $br_tut where id = $predmet");
	if($query)
		echo "Uspjesno ste promijenili podatke. Kliknite <a href=\"?sta=nastavnik/unos_kolicine_pred&predmet=$predmet&ag=$agod\">OVDJE</a> za povratak";
	else
		niceerror("Doslo je do greske prilikom unosa u bazu podataka.");
	}
}

//kraj skripte
}
?>
