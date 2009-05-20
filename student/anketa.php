<?

// STUDENT/PREDMET - statusna stranica predmeta

// v3.9.1.0 (2008/02/19) + Kopiran raniji stud_status, uz novi dizajn
// v3.9.1.1 (2008/03/28) + Dodana ikona za slanje novog zadatka (zad_novi.png)
// v3.9.1.2 (2008/04/09) + Dodan prikaz akademske godine uz ime predmeta; zadace bez imena; navigacija za zadace je prikazivala visak zadataka; otvori PDF u novom prozoru
// v3.9.1.3 (2008/10/02) + Dodana provjera da li student slusa predmet
// v4.0.0.0 (2009/02/19) + Release
// v4.0.9.1 (2009/03/24) + Prebacena polja ects i tippredmeta iz tabele ponudakursa u tabelu predmet
// v4.0.9.2 (2009/03/31) + Tabela ispit preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.3 (2009/03/31) + Tabela konacna_ocjena preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.4 (2009/04/01) + Tabela zadaca preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.5 (2009/04/02) + Tabela studentski_moduli preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.6 (2009/04/29) + Preusmjeravam tabelu labgrupa sa tabele ponudakursa na tabelu predmet
// v4.0.9.7 (2009/05/01) + Parametri su sada predmet i ag
// v4.0.9.8 (2009/05/06) + Kod ispisa naziva grupe u kojoj je student, necemo uzimati u obzir virtualne grupe; ispis prisustva pojednostavljen ukidanjem labgrupe 0


function student_anketa() {

global $userid;


$predmet = intval($_REQUEST['predmet']);
//$ag = intval($_REQUEST['ag']); // akademska godina
$ag =1;
$q09= myquery("select id,title from anketa where aktivna=1");
$anketa = mysql_result($q09,0,0);
$naziv= mysql_result($q09,0,1);
// Podaci za zaglavlje
$q10 = myquery("select naziv from predmet where id=$predmet");
if (mysql_num_rows($q10)<1) {
	zamgerlog("nepoznat predmet $predmet",3); // nivo 3: greska
	biguglyerror("Nepoznat predmet");
	return;
}

$q15 = myquery("select naziv from akademska_godina where id=$ag");
if (mysql_num_rows($q10)<1) {
	zamgerlog("nepoznata akademska godina $ag",3); // nivo 3: greska
	biguglyerror("Nepoznata akademska godina");
	return;
}

// Da li student slusa predmet?
//print "select sp.predmet from student_predmet as sp, ponudakursa as pk where sp.student=$userid and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag";
$q17 = myquery("select sp.predmet from student_predmet as sp, ponudakursa as pk where sp.student=$userid and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
if (mysql_num_rows($q17)<1) {
	zamgerlog("student ne slusa predmet pp$predmet", 3);
	biguglyerror("Niste upisani na ovaj predmet");
	return;
}
$ponudakursa = mysql_result($q17,0,0);

?>
<br/>
<p style="font-size: small;">Predmet: <b><?=mysql_result($q10,0,0)?> (<?=mysql_result($q15,0,0)?>)</b><br/>
<?
// kreiramo novi slog u tabeli rezultat

$q700="SELECT id FROM rezultat ORDER BY id desc limit 1";
$result700 = mysql_query($q700);
$id_rezultata =mysql_result($result700,0,0)+1;

$unique_hash_code = md5($userid.$predmet);
// da li je vec taj slog u tabeli 
$q589 = myquery("select count(*) from rezultat where osoba_id='$unique_hash_code'");

$postoji_slog= mysql_result($q589,0,0);

if(!$postoji_slog)
	$q590 = myquery("INSERT INTO rezultat (id ,anketa_id ,submitted ,complete ,predmet_id,osoba_id)
   		VALUES ($id_rezultata, $anketa, curdate(), 'N', $predmet, '$unique_hash_code')");



?>


<!-- progress bar -->

        <center>
      
        <p>Ovdje cete dobiti kod koji cete iskoristiti za ispunjavanje ankete za ovaj predmet: &nbsp;<br/>
        <br/>
        <table width="300" cellpadding="0" cellspacing="2" >
            <tr height="30">
                <td width="300">Vas kod za ovaj predmet je: <br /></td>
            </tr>
            <tr>
                <td bgcolor="#CCFFCC"> <?=$unique_hash_code?></td>
            </tr>
         </table>
        
       </center>
        

<!-- end progress bar -->
<?


}

?>