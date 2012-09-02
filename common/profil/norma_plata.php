<?
global $osoba;
$osoba_URL = $_GET['osoba'];
$pristup = $osoba == $osoba_URL;
if(!$pristup)
	//myerror("Nemate privilegiju pristupa plate tom korisniku. Molimo vas da za pregled plate kliknete na meni \"Plata\" u vasem profilu.");
	echo "<h2>Nemate privilegiju pristupa plate tom korisniku.<br> Molimo vas da za detaljan pregled norme kliknete na meni \"Norma plate\" u vasem profilu.</h2><br>";
else{
$ime_prezime = myquery("SELECT ime, prezime FROM osoba WHERE id=$osoba");
$ime = mysql_result($ime_prezime,0,0);
$prezime = mysql_result($ime_prezime,0,1);
echo "<h2>Detalji o normi korisnika $ime $prezime</h2><br>\n";

$zvanje = intval(mysql_result(myquery("SELECT zvanje FROM izbor WHERE osoba = $osoba"), 0, 0));
	if($zvanje == 1 || $zvanje == 2 || $zvanje == 6){ //u slucaju redovnog profesora, vandrednog profesora ili profesora emeritusa
		$broj_vjezbi_tutorijala = intval(mysql_result(myquery("SELECT  SUM( k.br_vjezbi ) + SUM( k.br_tutorijala ) AS ukupno_casova
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
		echo "\t<table cellpadding=\"0\" cellspacing=\"10\" border=\"0\">\n\t\t";
		echo "<tr><td><font size=\"3\">Totalni broj predmeta koje drzite:</td><td>$broj_predmeta</td></font></tr><br>\n\t\t";
		echo "<tr><td><font size=\"3\">Totalni broj vjezbi i tutorijala koje drzite:</td><td>$broj_vjezbi_tutorijala</td></font></tr><br>\n\t\t";
		$predmet_norma = round($broj_predmeta / 3, 2);
		echo "<tr><td><font size=\"3\">Dio norme od predmeta:</td><td>$predmet_norma</td></font></tr><br>\n\t\t";
		$vjezbe_tutorijali_norma = round($broj_vjezbi_tutorijala / 12, 2);
		echo "<tr><td><font size=\"3\">Dio norme od vjezbi i tutorijala:</td><td>$vjezbe_tutorijali_norma</td></font></tr><br>\n\t\t";
		$norma = round($predmet_norma + $vjezbe_tutorijali_norma, 2);
		echo "<tr><td><font size=\"3\">Vasa norma iznosi:</td><td>$norma</td></font></tr><br>\n\t\t";
		echo "<br></table>\n";
	}
	else if($zvanje == 4 || $zvanje == 5){ //u slucaju asistenta ili viseg asistenta
		$broj_predavanja = intval(mysql_result(myquery("SELECT  SUM( k.br_predavanja ) AS ukupno_casova
			FROM predmet AS p
			JOIN labgrupa AS l ON p.id = l.predmet
			JOIN angazman AS a ON p.id = a.predmet
			JOIN osoba AS o ON o.id = a.osoba
			JOIN kolicina_predavanja as k ON k.osoba_id = o.id
			JOIN akademska_godina AS ag ON ag.id = a.akademska_godina
			WHERE ag.aktuelna = 1 AND l.id = k.labgrupa_id AND o.id = $osoba
			GROUP BY ime"), 0, 0));
		$broj_vjezbi_tutorijala = intval(mysql_result(myquery("SELECT  SUM( k.br_predavanja ) + SUM( k.br_vjezbi ) + SUM( k.br_tutorijala ) AS ukupno_casova
			FROM predmet AS p
			JOIN labgrupa AS l ON p.id = l.predmet
			JOIN angazman AS a ON p.id = a.predmet
			JOIN osoba AS o ON o.id = a.osoba
			JOIN kolicina_predavanja as k ON k.osoba_id = o.id
			JOIN akademska_godina AS ag ON ag.id = a.akademska_godina
			WHERE ag.aktuelna = 1 AND l.id = k.labgrupa_id AND o.id = $osoba
			GROUP BY ime"), 0 , 0));
		echo "\t<table cellpadding=\"0\" cellspacing=\"10\" border=\"0\">\n\t\t";
		echo "<tr><td><font size=\"3\">Totalni broj predavanja koje drzite:</td><td>$broj_predavanja</td></font></tr><br>\n\t\t";
		echo "<tr><td><font size=\"3\">Totalni broj vjezbi i tutorijala koje drzite:</td><td>$broj_vjezbi_tutorijala</td></font></tr><br>\n\t\t";
		$predavanja_norma = round($broj_predavanja / 12, 2);
		echo "<tr><td><font size=\"3\">Dio norme od predavanja:</td><td>$predavanja_norma</td></font></tr><br>\n\t\t";
		$vjezbe_tutorijali_norma = round($broj_vjezbi_tutorijala / 12, 2);
		echo "<tr><td><font size=\"3\">Dio norme od vjezbi i tutorijala:</td><td>$vjezbe_tutorijali_norma</td></font></tr><br>\n\t\t";
		$norma = round($predavanja_norma + $vjezbe_tutorijali_norma, 2);
		echo "<tr><td><font size=\"3\">Vasa norma iznosi:</td><td>$norma</td></font></tr><br>\n\t\t";	
	}
}
?>
