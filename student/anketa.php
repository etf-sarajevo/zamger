<?

// STUDENT/ANKETA - stranica za dobijanje anketnog koda



function student_anketa() {

	global $userid;
	
	
	$predmet = intval($_REQUEST['predmet']);
	
	$q10 = myquery("select id,naziv from akademska_godina where aktuelna=1");
	$ag = mysql_result($q10,0,0);
	
	$q09= myquery("select id,naziv from anketa where aktivna=1");
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
	
	$q20 = myquery("select studij,semestar from student_studij where student=$userid and akademska_godina=$ag order by semestar desc limit 1");
			if (mysql_num_rows($q20)<1) {
				$sem_ispis = "Niste upisani na studij!";
				
			} else {
				$studij = mysql_result($q20,0,0);
				$semestar = mysql_result($q20,0,1);
	
	}
	
	$ponudakursa = mysql_result($q17,0,0);

?>
<br/>
<p style="font-size: small;">Predmet: <b><?=mysql_result($q10,0,0)?> (<?=mysql_result($q15,0,0)?>)</b><br/>
<?
// kreiramo novi slog u tabeli rezultat

$result700=myquery("SELECT id FROM rezultat ORDER BY id desc limit 1");
//$result700 = mysql_query($q700);
if (mysql_num_rows($result700)==0) 
	$id_rezultata=1;
else
	$id_rezultata =mysql_result($result700,0,0)+1;
// prepraviti da tako da je i akademska godina u hashu
$unique_hash_code = md5($userid.$predmet);
// da li je vec taj slog u tabeli 
$q589 = myquery("select count(*) from rezultat where unique_id='$unique_hash_code'");

$postoji_slog= mysql_result($q589,0,0);

if(!$postoji_slog)
	$q590 = myquery("INSERT INTO rezultat (id ,anketa_id ,vrijeme ,zavrsena ,predmet_id,unique_id,studij,semestar)
   		VALUES ($id_rezultata, $anketa, curdate(), 'N', $predmet, '$unique_hash_code',$studij,$semestar)");



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
                <td align="center" bgcolor="#CCFFCC"> <?=$unique_hash_code?></td>
            </tr>
         </table>
        
       </center>
        

<!-- end progress bar -->
<?


}

?>