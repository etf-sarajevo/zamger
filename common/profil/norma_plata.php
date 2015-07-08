<?
global $osoba;
$osoba_URL = $_GET['osoba'];
$pristup = $osoba == $osoba_URL;
if(!$pristup)
	//myerror("Nemate privilegiju pristupa plate tom korisniku. Molimo vas da za pregled plate kliknete na meni \"Plata\" u vasem profilu.");
	//echo "<h2>Nemate privilegiju pristupa plate tom korisniku.<br> Molimo vas da za detaljan pregled norme kliknete na meni \"Norma plate\" u vasem profilu.</h2><br>";
	niceerror("Nemate privilegiju pristupa plate tom korisniku.<br> Molimo vas da za detaljan pregled norme kliknete na meni \"Norma plate\" u vasem profilu.");
else{
$ime_prezime = myquery("SELECT ime, prezime FROM osoba WHERE id=$osoba");
$ime = mysql_result($ime_prezime,0,0);
$prezime = mysql_result($ime_prezime,0,1);
echo "<h2>Detalji o normi korisnika $ime $prezime</h2><br>\n";

//stari metod(ne valja) $fk_naucnonastavno_zvanje = intval(mysql_result(myquery("SELECT fk_naucnonastavno_zvanje FROM izbor WHERE fk_osoba = $osoba"), 0, 0));
$fk_naucnonastavno_zvanje = intval(mysql_result(myquery("SELECT zvanje FROM izbor WHERE osoba = $osoba and CONCAT(year(datum_izbora),'/',year(datum_isteka)) = (SELECT naziv FROM akademska_godina WHERE aktuelna = 1)"), 0, 0));
$fk_naucnonastavno_zvanje2 = intval(mysql_result(myquery("SELECT zvanje FROM izbor WHERE osoba = $osoba ORDER BY year(datum_isteka) DESC"), 0, 0));
	if($fk_naucnonastavno_zvanje == 1 || $fk_naucnonastavno_zvanje == 2 || $fk_naucnonastavno_zvanje == 6){ //u slucaju redovnog profesora, vandrednog profesora ili profesora emeritusa
		/*
		stari nacin, ne koristi se vise
		$broj_vjezbi_tutorijala = intval(mysql_result(myquery("SELECT  SUM( k.sati_vjezbi ) + SUM( k.sati_tutorijala ) AS ukupno_casova
			FROM predmet AS p
			JOIN labgrupa AS l ON p.id = l.predmet
			JOIN angazman AS a ON p.id = a.predmet
			JOIN osoba AS o ON o.id = a.osoba
			JOIN kolicina_predavanja as k ON k.osoba_id = o.id
			JOIN akademska_godina AS ag ON ag.id = a.akademska_godina
			WHERE ag.aktuelna = 1 AND l.id = k.labgrupa_id AND o.id = $osoba
			GROUP BY ime"), 0 , 0));
		$broj_predmeta = intval(mysql_result(myquery("SELECT COUNT( p.id ) 
			FROM osoba AS o
			JOIN predmet AS p
			JOIN angazman AS a ON a.osoba = o.id AND a.predmet = p.id
			WHERE o.id = $osoba
			GROUP BY a.osoba"), 0 ,0));
		*/
		$broj_predavanja = myquery("SELECT  p.id, l.naziv, tip AS tip_grupe
			FROM predmet AS p
			JOIN labgrupa AS l ON p.id = l.predmet
			JOIN angazman AS a ON p.id = a.predmet
			JOIN osoba AS o ON o.id = a.osoba
			JOIN nastavnik_labgrupa as nl ON nl.osoba_id = o.id
			JOIN akademska_godina AS ag ON ag.id = a.akademska_godina
			WHERE ag.aktuelna = 1 AND l.id = nl.labgrupa_id AND o.id = $osoba");//, 0 , 0));
		$broj_predmeta = 0;
		$broj_vjezbi_tutorijala = 0;
		while($row = mysql_fetch_row($broj_predavanja)){
			$pred_id = $row[0];
			$lab_ime = $row[1];
			$tip = $row[2];
			switch($tip){
				case "predavanja":
					$broj_predmeta = $broj_predmeta + 1;//intval(mysql_result(myquery("SELECT sati_predavanja FROM predmet WHERE id = $pred_id"),0,0));
					break;
				case "vjezbe":
					$broj_vjezbi_tutorijala = $broj_vjezbi_tutorijala + intval(mysql_result(myquery("SELECT sati_vjezbi FROM predmet WHERE id = $pred_id"),0,0));
					break;
				case "tutorijali":
					$broj_vjezbi_tutorijala = $broj_vjezbi_tutorijala + intval(mysql_result(myquery("SELECT sati_tutorijala FROM predmet WHERE id = $pred_id"),0,0));
					break;
				case "vjezbe+tutorijali":
					$broj_vjezbi_tutorijala = $broj_vjezbi_tutorijala + 2 * intval(mysql_result(myquery("SELECT sati_tutorijala FROM predmet WHERE id = $pred_id"),0,0));
					break;
			}
		}
		echo "\t<table cellpadding=\"0\" cellspacing=\"10\" border=\"0\">\n\t\t";
		echo "<tr><td><font size=\"3\">Totalni broj predmeta koje drzite:</td><td>$broj_predmeta</td></font></tr><br>\n\t\t";
		echo "<tr><td><font size=\"3\">Totalni broj vjezbi i tutorijala koje drzite:</td><td>$broj_vjezbi_tutorijala</td></font></tr><br>\n\t\t";
		$predmet_norma = round($broj_predmeta / 3, 2);
		echo "<tr><td><font size=\"3\">Dio norme od predavanja:</td><td>$predmet_norma</td></font></tr><br>\n\t\t";
		$vjezbe_tutorijali_norma = round($broj_vjezbi_tutorijala / 12, 2);
		echo "<tr><td><font size=\"3\">Dio norme od vjezbi i tutorijala:</td><td>$vjezbe_tutorijali_norma</td></font></tr><br>\n\t\t";
		$norma = round($predmet_norma + $vjezbe_tutorijali_norma, 2);
		echo "<tr><td><font size=\"3\">Vasa norma iznosi:</td><td>$norma</td></font></tr><br>\n\t\t";
		echo "<br></table>\n";
	}
	else if($fk_naucnonastavno_zvanje == 4 || $fk_naucnonastavno_zvanje == 5){ //u slucaju asistenta ili viseg asistenta
		/*
		stari nacin, ne koristi se vise
		$broj_predavanja = intval(mysql_result(myquery("SELECT  SUM( k.sati_predavanja ) AS ukupno_casova
			FROM predmet AS p
			JOIN labgrupa AS l ON p.id = l.predmet
			JOIN angazman AS a ON p.id = a.predmet
			JOIN osoba AS o ON o.id = a.osoba
			JOIN kolicina_predavanja as k ON k.osoba_id = o.id
			JOIN akademska_godina AS ag ON ag.id = a.akademska_godina
			WHERE ag.aktuelna = 1 AND l.id = k.labgrupa_id AND o.id = $osoba
			GROUP BY ime"), 0, 0));
		$broj_vjezbi_tutorijala = intval(mysql_result(myquery("SELECT  SUM( k.sati_predavanja ) + SUM( k.sati_vjezbi ) + SUM( k.sati_tutorijala ) AS ukupno_casova
			FROM predmet AS p
			JOIN labgrupa AS l ON p.id = l.predmet
			JOIN angazman AS a ON p.id = a.predmet
			JOIN osoba AS o ON o.id = a.osoba
			JOIN kolicina_predavanja as k ON k.osoba_id = o.id
			JOIN akademska_godina AS ag ON ag.id = a.akademska_godina
			WHERE ag.aktuelna = 1 AND l.id = k.labgrupa_id AND o.id = $osoba
			GROUP BY ime"), 0 , 0));
		*/
		$broj_predavanja = myquery("SELECT  p.id, l.naziv, tip AS tip_grupe
			FROM predmet AS p
			JOIN labgrupa AS l ON p.id = l.predmet
			JOIN angazman AS a ON p.id = a.predmet
			JOIN osoba AS o ON o.id = a.osoba
			JOIN nastavnik_labgrupa as nl ON nl.osoba_id = o.id
			JOIN akademska_godina AS ag ON ag.id = a.akademska_godina
			WHERE ag.aktuelna = 1 AND l.id = nl.labgrupa_id AND o.id = $osoba");//, 0 , 0));	
		$broj_predmeta = 0;
		$broj_vjezbi_tutorijala = 0;
		while($row = mysql_fetch_row($broj_predavanja)){
			$pred_id = $row[0];
			$lab_ime = $row[1];
			$tip = $row[2];
			switch($tip){
				case "predavanja":
					$broj_predmeta = $broj_predmeta + intval(mysql_result(myquery("SELECT sati_predavanja FROM predmet WHERE id = $pred_id"),0,0));
					break;
				case "vjezbe":
					$broj_vjezbi_tutorijala = $broj_vjezbi_tutorijala + intval(mysql_result(myquery("SELECT sati_vjezbi FROM predmet WHERE id = $pred_id"),0,0));
					break;
				case "tutorijali":
					$broj_vjezbi_tutorijala = $broj_vjezbi_tutorijala + intval(mysql_result(myquery("SELECT sati_tutorijala FROM predmet WHERE id = $pred_id"),0,0));
					break;
				case "vjezbe+tutorijali":
					$broj_vjezbi_tutorijala = $broj_vjezbi_tutorijala + 2 * intval(mysql_result(myquery("SELECT sati_tutorijala FROM predmet WHERE id = $pred_id"),0,0));
					break;
			}
		}
		echo "\t<table cellpadding=\"0\" cellspacing=\"10\" border=\"0\">\n\t\t";
		echo "<tr><td><font size=\"3\">Totalni broj predavanja koje drzite:</td><td>$broj_predmeta</td></font></tr><br>\n\t\t";
		echo "<tr><td><font size=\"3\">Totalni broj vjezbi i tutorijala koje drzite:</td><td>$broj_vjezbi_tutorijala</td></font></tr><br>\n\t\t";
		$predavanja_norma = round($broj_predmeta / 12, 2);
		echo "<tr><td><font size=\"3\">Dio norme od predavanja:</td><td>$predavanja_norma</td></font></tr><br>\n\t\t";
		$vjezbe_tutorijali_norma = round($broj_vjezbi_tutorijala / 12, 2);
		echo "<tr><td><font size=\"3\">Dio norme od vjezbi i tutorijala:</td><td>$vjezbe_tutorijali_norma</td></font></tr><br>\n\t\t";
		$norma = round($predavanja_norma + $vjezbe_tutorijali_norma, 2);
		echo "<tr><td><font size=\"3\">Vasa norma iznosi:</td><td>$norma</td></font></tr><br>\n\t\t";	
	}
}
?>
