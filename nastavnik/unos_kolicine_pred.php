<?

function nastavnik_unos_kolicine_pred(){

global $userid,$user_siteadmin,$user_studentska;


//echo "<br><br><br>"; //za podesavanje visine odakle tabela pocinje
$agod = $_REQUEST["ag"];
$pred = $_REQUEST["predmet"];
$action = $_REQUEST["action"];

// Provjera privilegija

if (!$user_studentska && !$user_siteadmin) {
	zamgerlog("nije studentska",3); // 3: error
	biguglyerror("Pristup nije dozvoljen.");
	return;
}


/*
SELECT p.naziv AS predmet, ag.naziv AS akademska_godina, ime, prezime, l.naziv AS labgrupa, k.br_predavanja, k.br_vjezbi, k.br_tutorijala
FROM kolicina_predavanja AS k
JOIN osoba AS o ON k.osoba_id = o.id
JOIN labgrupa AS l ON l.id = k.labgrupa_id
JOIN akademska_godina AS ag ON ag.id = l.akademska_godina
JOIN predmet AS p ON p.id = l.predmet
WHERE ag.aktuelna = 1
ORDER BY ime ASC
*/

/*$res2 = myquery("SELECT k.id, sifra, p.naziv AS predmet, ime, prezime, l.naziv AS labgrupa, k.br_predavanja, k.br_vjezbi, k.br_tutorijala, ag.naziv AS godina
FROM predmet AS p
JOIN labgrupa AS l ON p.id = l.predmet
JOIN angazman AS a ON p.id = a.predmet
JOIN osoba AS o ON o.id = a.osoba
JOIN kolicina_predavanja as k ON k.osoba_id = o.id
JOIN akademska_godina AS ag ON ag.id = a.akademska_godina
ORDER BY ime ASC"); */


?>
<!-- echo "<table border=\"1\">"; -->
<?
//ovim se provjerava da li se prvi put otvara ovaj modul, tj. da li je bilo nekih promjena vrsenih pomocu ovog modula
if($action == null or $action == "")
{
// ovo se izvrsava ako se 1. put otvara ovaj modul, ili ako se vraca na njega nakon nekih promjena
prikazi_tabele:{

$res = myquery("SELECT k.id, sifra, p.naziv AS predmet, ime, prezime, l.naziv AS labgrupa, k.br_predavanja, k.br_vjezbi, k.br_tutorijala, ag.naziv AS godina
FROM predmet AS p
JOIN labgrupa AS l ON p.id = l.predmet
JOIN angazman AS a ON p.id = a.predmet
JOIN osoba AS o ON o.id = a.osoba
JOIN kolicina_predavanja as k ON k.osoba_id = o.id
JOIN akademska_godina AS ag ON ag.id = a.akademska_godina
WHERE ag.aktuelna = 1 AND l.id = k.labgrupa_id
ORDER BY ime ASC"); // ovo je query za 1. tabelu

$res3 = myquery("SELECT o.id, p.naziv AS predmet, ime, prezime, l.naziv AS labgrupa 
FROM predmet AS p 
JOIN labgrupa AS l ON p.id = l.predmet
JOIN osoba AS o 
JOIN angazman AS a ON a.predmet = p.id and a.osoba = o.id
JOIN akademska_godina AS ag on l.akademska_godina = ag.id
WHERE p.id = $pred AND ag.aktuelna = 1
GROUP BY ime");
?>

<font size="3">Izmjena kolicine predavanja, vjezbi i tutorijala za predmet za pojedinacne nastavnike (koji predaju)</font>
<br><br>
<!-- tabela za editovanje kolicina predavanja pojedinacnih nastavnika -->
<!--
<form action="?sta=nastavnik/unos_kolicine_pred&predmet=<? echo $pred ?>&ag=<? echo $agod ?>&action=edit" method="POST">
	<input type="hidden" name="akcija" value="edit_1">
-->
	<table border="1" cellspacing="1" font size="big">
		<tr>
			<td>Naziv predmeta</td>
			<td>Akademska godina</td>
			<td>Ime</td>
			<td>Prezime</td>
			<td>Labgrupa</td>
			<td>Broj predavanja</td>
			<td>Broj vjezbi</td>
			<td>Broj tutorijala</td>
			<td></td>
			<td></td>
			<!-- <td></td> -->
		</tr>
	<?
		while($row = mysql_fetch_row($res)){
		$kol_id = $row[0];
		$naziv_pred = $row[2];
		$ak_god = $row[9];
		$ime = $row[3];
		$prezime = $row[4];
		$labgrupa = $row[5];
		$br_pred = $row[6];
		$br_vj = $row[7];
		$br_tut = $row[8];
		if($br_pred == null or "")
			$br_pred = 0;
		if($br_vj == null or "")
			$br_vj = 0;
		if($br_tut == null or "")
			$br_tut = 0;
	?>
		<form action="?sta=nastavnik/unos_kolicine_pred&predmet=<? echo $pred ?>&ag=<? echo $agod ?>&action=edit" method="POST">
		<input type="hidden" name="akcija" value="edit_1">
		<tr>
			<input type="hidden" name="kol_id" value="<? echo $kol_id; ?>">
			<td><? echo $naziv_pred; ?></td>
			<td><? echo $ak_god; ?></td>
			<td><? echo $ime; ?></td>
			<td><? echo $prezime; ?></td>
			<td><? echo $labgrupa; ?></td>
			<td><input type="text" name="br_pred" value="<? echo $br_pred; ?>" /></td>
			<td><input type="text" name="br_vj" value="<? echo $br_vj; ?>" /></td>
			<td><input type="text" name="br_tut" value="<? echo $br_tut; ?>" /></td>
			<td><input type="submit" value="Spasi postavke" /></td>
			<td><input name="delete" type="submit" value="Izbrisi postavke" /></td>
			<!-- <td><a href="?sta=nastavnik/unos_kolicine_pred&predmet=<? echo $pred ?>&ag=<? echo $agod ?>&kol_id=<? echo $kol_id; ?>">SPASI</td> -->
		</tr>
		</form>
		<?
		}	
		?>
	</table>
	<input type="submit" value="Spasi postavke" />
<!--
</form>
-->
<br><br><br>
<!-- kraj tabele za editovanje kolicina predavanja pojedinacnih nastavnika -->


<font size="3">Unosenje kolicine predavanja, vjezbi i tutorijala za predmet za pojedinacne nastavnike (koji ne predaju)</font>
<!-- tabela za dodavanje kolicina predavanja pojedinacnih nastavnika koji trenutno ne predaju -->
<form action="?sta=nastavnik/unos_kolicine_pred&predmet=<? echo $pred ?>&ag=<? echo $agod ?>&action=edit" method="POST">
<input type="hidden" name="akcija" value="edit_2">
	<table border="1" cellspacing="1" font size="big">
		<tr>
			<td>Ime</td>
			<td>Prezime</td>
			<td>Labgrupa</td>
			<td>Broj predavanja</td>
			<td>Broj vjezbi</td>
			<td>Broj tutorijala</td>
			<td></td>
		</tr>
		<?
			while($row = mysql_fetch_row($res3)){
				$ime = $row[2];
				$prezime = $row[3];
				$osoba_id = $row[0];
				$predmet_id = $pred;
				$ak_godina = $agod;
				?>
				<tr>
					<td><? echo $ime; ?></td>
					<td><? echo $prezime; ?></td>
					<input type="hidden" name="osoba_id" value="<? echo $osoba_id; ?>">
					<input type="hidden" name="predmet_id" value="<? echo $predmet_id; ?>">
					<input type="hidden" name="ak_godina" value="<? echo $ak_godina; ?>">
					<td>
						<select name="labgroup">
							<?
							$grupe = myquery("select l.id, l.naziv as labgrupa, p.naziv as predmet from labgrupa as l join predmet as p on l.predmet = p.id where p.id = $pred");
							//query za izlistavanje labgrupa za trenutni predmet trenutnog nastavnika
							while($row = mysql_fetch_row($grupe)){
							$lab_id = $row[0];
							$lab_ime = $row[1];
								echo "<option value=\"$lab_id\">$lab_ime</option>";
							}
							
							?>
						</select>
					</td>
					<td><input type="text" name="br_pred_nastavnik" value="<? echo $br_pred; ?>" /></td>
					<td><input type="text" name="br_vjezbi_nastavnik" value="<? echo $br_vj; ?>" /></td>
					<td><input type="text" name="br_tutorijala_nastavnik" value="<? echo $br_tut; ?>" /></td>
					<td><input type="submit" value="Dodaj nastavnika" /></td>
					<!-- <td><? echo "<a href=\"\">Dodaj nastavnika</a>"; ?></td> -->
				</tr>
				<br>
				<?
			}
		?>
	</table>
</form>
<br><br><br>
<!-- kraj tabele za dodavanje kolicina predavanja pojedinacnih nastavnika koji trenutno ne predaju -->



<font size="3">Mijenjanje kolicine predavanja, vjezbi i tutorijala za predmet</font>
<!-- tabela za editovanje kolicina predavanja za trenutno odabrani predmet -->
<form action="?sta=nastavnik/unos_kolicine_pred&predmet=<? echo $pred ?>&ag=<? echo $agod ?>&action=edit" method="POST">
	<input type="hidden" name="akcija" value="edit_3">
	<table border="1" cellspacing="1" font size="big">
		<tr>
			<td>Sifra</td>
			<td>Naziv predmeta</td>
			<td>Broj predavanja</td>
			<td>Broj vjezbi</td>
			<td>Broj tutorijala</td>
		</tr>
	<?
		$predmet_query = myquery("SELECT sifra, naziv, br_predavanja, br_vjezbi, br_tutorijala FROM predmet where id = $pred order by id asc");
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
	//ovo se izvrsava ako se mijenjaju podaci za broj predavanja pojedinacnih nastavnika (1. tabela)
	$kol_id = $_POST['kol_id'];
	$br_pred = $_POST['br_pred'];
	$br_vj = $_POST['br_vj'];
	$br_tut = $_POST['br_tut'];
	$query = FALSE;
	if(isset($_POST['delete'])){
		$query = myquery("DELETE FROM kolicina_predavanja WHERE id = $kol_id");
	} else
		$query = myquery("UPDATE kolicina_predavanja set br_predavanja = $br_pred, br_vjezbi = $br_vj, br_tutorijala = $br_tut WHERE id = $kol_id");
	if($query){
		goto prikazi_tabele;
	} else {
		myerror("Doslo je do greske prilikom izmjene podataka.");
	}
	//echo "<font size=\"10\">$kol_id</font>";
}
else if($akcija == "edit_2"){
	//ovo se izvrsava kada se nastavnicima dodaju kolicine predavanja po 1. put (2. tabela)
	$osoba_id = $_POST['osoba_id'];
	$predmet_id = $_POST['predmet_id'];
	$labgrupa_id  = $_POST['labgroup'];
	$ak_godina = $_POST['ak_godina'];
	$br_predavanja = $_POST['br_pred_nastavnik'];
	$br_vjezbi = $_POST['br_vjezbi_nastavnik'];
	$br_tutorijala = $_POST['br_tutorijala_nastavnik'];
	$query = myquery("INSERT INTO kolicina_predavanja VALUES(null, '$osoba_id','$predmet_id','$labgrupa_id','$ak_godina','$br_predavanja','$br_vjezbi','$br_tutorijala')");
	//echo $query; za provjeravanje ispravnosti querija
	if($query)
		echo "<br>Uspjesno ste dodali nastavniku predavanja.Kliknite <a href=\"?sta=nastavnik/unos_kolicine_pred&predmet=$pred&ag=$agod\">OVDJE</a> za povratak";
	else
		myerror("Doslo je do greske prilikom unosa u bazu podataka.");
}
else if($akcija == "edit_3"){
	//ovo se izvrsava ako se mijenjaju podaci za broj predavanja predmeta (3. tabela)
	$br_pred = $_POST['br_pred_predmet'];
	$br_vj = $_POST['br_vj_predmet'];
	$br_tut = $_POST['br_tut_predmet'];
	$query = myquery("update predmet set br_predavanja = $br_pred, br_vjezbi = $br_vj, br_tutorijala = $br_tut where id = $pred");
	if($query)
		echo "Uspjesno ste promijenili podatke. Kliknite <a href=\"?sta=nastavnik/unos_kolicine_pred&predmet=$pred&ag=$agod\">OVDJE</a> za povratak";
	else
		myerror("Doslo je do greske prilikom unosa u bazu podataka.");
	}
}

//kraj skripte
}
?>
