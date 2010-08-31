<?

// NASTAVNIK/ZADACE - kreiranje zadaća i masovni unos

// v3.9.1.0 (2008/02/19) + Preimenovan bivsi admin_predmet
// v3.9.1.1 (2008/04/03) + Koristim lib/manip
// v3.9.1.2 (2008/05/09) + Forma za masovni unos koristila za hidden polje "akcija" vrijednost "masszadaca" umjesto "massinput", nije ispravno preuzeta vrijednost zadace iz db_dropdown (falilo _lv_column), dugme nazad nije bilo ispravno obradjeno
// v3.9.1.3 (2008/05/12) + Kod masovnog unosa u upitu stajalo SET... redni_broj=$bodova :( Popravljen logging
// v3.9.1.4 (2008/05/16) + Dodan update_komponente()
// v3.9.1.5 (2008/08/18) + Informativnija greska kod pokusaja masovnog unosa zadaca ako ne postoji nijedna zadaca, promijenjen naslov "Unos zadace" u "Kreiranje zadace", dodana zastita od visestrukog slanja kod masovnog unosa
// v3.9.1.6 (2009/01/23) + Ukinut db_form() radi niza bugova (metabug #48)
// v4.0.0.0 (2009/02/19) + Release
// v4.0.0.1 (2009/03/12) + Nije se mogao zadati programski jezik (uvijek vracao na nedefinisan); dvostruka stavka "--Nije odredjen--"; poboljsan feedback nakon kreiranja / editovanja zadace
// v4.0.9.1 (2009/03/25) + nastavnik_predmet preusmjeren sa tabele ponudakursa na tabelu predmet
// v4.0.9.2 (2009/04/01) + Tabela zadaca preusmjerena sa ponudakursa na tabelu predmet; dodana provjera spoofinga zadace kod masovnog unosa
// v4.0.9.3 (2009/04/23) + Nastavnicki moduli sada primaju predmet i akademsku godinu (ag) umjesto ponudekursa
// v4.0.9.4 (2009/05/08) + Izdvajam brisanje zadace da se ne bi izvrsilo i azuriranje i sve ostalo
// v4.0.9.5 (2009/09/13) + Dozvoljavam da naziv zadace bude jedno slovo
// v4.0.9.6 (2009/10/26) + Popravljeno brisanje zadace, dodana zastita od visestrukog submitanja


function nastavnik_zadace() {

global $userid,$user_siteadmin, $dozvoljene_ekstenzije,$conf_files_path;

require("lib/manip.php");
global $mass_rezultat; // za masovni unos studenata u grupe
global $_lv_; // radi autogenerisanih formi


// Parametri
$predmet = intval($_REQUEST['predmet']);
$ag = intval($_REQUEST['ag']);

// Naziv predmeta
$q10 = myquery("select naziv from predmet where id=$predmet");
if (mysql_num_rows($q10)<1) {
	biguglyerror("Nepoznat predmet");
	zamgerlog("ilegalan predmet $predmet",3); //nivo 3: greska
	return;
}
$predmet_naziv = mysql_result($q10,0,0);

//Dozvoljene ekstenzije
$q99 = myquery("select naziv from ekstenzije");
$dozvoljene_ekstenzije =array();
 
  for($i=0 ; $i<mysql_num_rows( $q99 ) ; $i++ ) 
  {
      $dozvoljene_ekstenzije[$i]=mysql_result($q99,$i,0);
  }

// Da li korisnik ima pravo ući u modul?

if (!$user_siteadmin) { // 3 = site admin
	$q10 = myquery("select admin from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
	if (mysql_num_rows($q10)<1 || mysql_result($q10,0,0)<1) {
		zamgerlog("nastavnik/ispiti privilegije (predmet pp$predmet)",3);
		biguglyerror("Nemate pravo ulaska u ovu grupu!");
		return;
	} 
}



?>

<p>&nbsp;</p>

<p><h3><?=$predmet_naziv?> - Zadaće</h3></p>

<?

# Masovni unos zadaća

if ($_POST['akcija'] == "massinput" && strlen($_POST['nazad'])<1 && check_csrf_token()) {

	if ($_POST['fakatradi'] != 1) $ispis=1; else $ispis=0;

	// Provjera ostalih parametara
	$zadaca = intval($_REQUEST['_lv_column_zadaca']);
	$zadatak = intval($_REQUEST['zadatak']);

	$q20 = myquery("select naziv,zadataka,bodova,komponenta,predmet,akademska_godina from zadaca where id=$zadaca");
	if (mysql_num_rows($q20)<1) {
		zamgerlog("nepostojeca zadaca $zadaca",3); // 3 = greška
		niceerror("Morate najprije kreirati zadaću");
		print "\n<p>Koristite formular &quot;Kreiranje zadaće&quot; koji se nalazi na prethodnoj stranici. Ukoliko ne vidite nijednu zadaću na spisku &quot;Postojeće zadaće&quot;, koristite dugme Refresh vašeg web preglednika.</p>\n";
		return;
	}
	if (mysql_result($q20,0,1)<$zadatak) {
		zamgerlog("zadaca $zadaca nema $zadatak zadataka",3);
		niceerror("Zadaća \"".mysql_result($q20,0,0)."\" nema $zadatak zadataka.");
		return;
	}
	$maxbodova=mysql_result($q20,0,2);
	$komponenta=mysql_result($q20,0,3);

	// Provjera spoofanja zadaće
	if ($predmet != mysql_result($q20,0,4) || $ag != mysql_result($q20,0,5)) {
		zamgerlog("zadaca z$zadaca nije u predmetu pp$predme",3);
		niceerror("Pogresan ID zadace!");
		return;
	}

	$greska=mass_input($ispis); // Funkcija koja parsira podatke

	if (count($mass_rezultat)==0) {
		niceerror("Niste unijeli ništa.");
		return;
	}

	if ($ispis) {
		?>Akcije koje će biti urađene:<br/><br/>
		<?=genform("POST")?>
		<input type="hidden" name="fakatradi" value="1">
		<input type="hidden" name="_lv_column_zadaca" value="<?=$zadaca?>">
		<?
	}


	foreach ($mass_rezultat['ime'] as $student=>$ime) {
		$prezime = $mass_rezultat['prezime'][$student];
		$bodova = $mass_rezultat['podatak1'][$student];
		$bodova = str_replace(",",".",$bodova);

		// Student neocijenjen (prazno mjesto za ocjenu)
		if (floatval($bodova)==0 && strpos($bodova,"0")===FALSE) {
			if ($ispis)
				print "Student '$prezime $ime' - nema zadaću (nije unesen broj bodova $bodova)<br/>";
			continue;
		}

		// Bodovi moraju biti manji od maximalnih borova
		$bodova = floatval($bodova);
		if ($bodova>$maxbodova) {
			if ($ispis) {
				print "-- Studenta '$prezime $ime' ima $bodova bodova što je više od maksimalnih $maxbodova<br/>";
				//$greska=1;
				continue;
			}
		}

		// Zaključak
		if ($ispis) {
			print "Student '$prezime $ime' - zadaća $zadaca, bodova $bodova<br/>";
		} else {
			// Odredjujemo zadnji filename
			$q25 = myquery("select filename from zadatak where zadaca=$zadaca and redni_broj=$zadatak and student=$student order by id desc limit 1");
			if (mysql_num_rows($q25)>0) {
				$filename=mysql_result($q25,0,0);
			} else $filename='';

			$status_pregledana = 5; // status 5: pregledana
			$q30 = myquery("insert into zadatak set zadaca=$zadaca, redni_broj=$zadatak, student=$student, status=$status_pregledana, bodova=$bodova, vrijeme=NOW(), filename='$filename', userid=$userid"); 

			// Treba nam ponudakursa za update komponente
			$q35 = myquery("select sp.predmet from student_predmet as sp, ponudakursa as pk where sp.student=$student and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
			$ponudakursa = mysql_result($q35,0,0);

			update_komponente($student,$ponudakursa,$komponenta); // update statistike
		}
	}

	if ($ispis) {
		print '<input type="submit" name="nazad" value=" Nazad "> ';
		if ($greska==0) print ' <input type="submit" value=" Potvrda">';
		print "</form>";
		return;
	} else {
		zamgerlog("masovno upisane zadaće na predmet pp$predmet, zadaća z$zadaca, zadatak $zadatak",2); // 2 = edit
		?>
		Bodovi iz zadaća su upisani.
		<script language="JavaScript">
		location.href='?sta=nastavnik/zadace&predmet=<?=$predmet?>&ag=<?=$ag?>';
		</script>
		<?
	}
}



// Akcija za kreiranje novek, promjenu postojeće ili brisanje zadaće

if ($_POST['akcija']=="edit" && $_POST['potvrdabrisanja'] != " Nazad ") {
	$edit_zadaca = intval($_POST['zadaca']);
	
	// Prava pristupa
	if ($edit_zadaca>0) {
		$q86 = myquery("select predmet, akademska_godina from zadaca where id=$edit_zadaca");
		if (mysql_num_rows($q86)<1) {
			niceerror("Nepostojeća zadaća sa IDom $edit_zadaca");
			zamgerlog("promjena nepostojece zadace $edit_zadaca", 3);
			return 0;
		}
		if (mysql_result($q86,0,0)!=$predmet || mysql_result($q86,0,1)!=$ag) {
			niceerror("Zadaća nije sa izabranog predmeta");
			zamgerlog("promjena zadace: zadaca $edit_zadaca nije sa predmeta pp$predmet", 3);
			return 0;
		}
	}


	// Brisanje zadaće
	
	if ($_POST['brisanje'] == " Obriši ") {
		if ($edit_zadaca <= 0) return; // Ne bi se smjelo desiti
		$q86 = myquery("select predmet, akademska_godina from zadaca where id=$edit_zadaca");
		if (mysql_num_rows($q86)<1) {
			niceerror("Nepostojeća zadaća sa IDom $edit_zadaca");
			zamgerlog("brisanje nepostojece zadace $edit_zadaca", 3);
			return 0;
		}
		if (mysql_result($q86,0,0)!=$predmet || mysql_result($q86,0,1)!=$ag) {
			niceerror("Zadaća nije sa izabranog predmeta");
			zamgerlog("brisanje zadace: zadaca $edit_zadaca nije sa predmeta pp$predmet", 3);
			return 0;
		}
	
		if ($_POST['potvrdabrisanja']==" Briši ") {
			$q88 = myquery("delete from zadaca where id=$edit_zadaca");
			$q89 = myquery("delete from zadatak where zadaca=$edit_zadaca");
			zamgerlog("obrisana zadaca $edit_zadaca sa predmeta pp$predmet", 4);
			nicemessage ("Zadaća uspješno obrisana");
			?>
			<script language="JavaScript">
			location.href='?sta=nastavnik/zadace&predmet=<?=$predmet?>&ag=<?=$ag?>';
			</script>
			<?
			return;
		} else {
			$q96 = myquery("select count(*) from zadatak where zadaca=$edit_zadaca");
			$brojzadataka=mysql_result($q96,0,0);
			print genform("POST");
			?>
			Brisanjem zadaće obrisaćete i sve do sada unesene ocjene i poslane zadatke! Da li ste sigurni da to želite?<br>
			U pitanju je <b><?=$brojzadataka?></b> jedinstvenih slogova u bazi!<br><br>
			<input type="submit" name="potvrdabrisanja" value=" Briši ">
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input type="submit" name="potvrdabrisanja" value=" Nazad ">
			<?
			return;
		}
	}


	// Kreiranje ili izmjena zadaće

	$naziv = trim(my_escape($_POST['naziv']));
	$zadataka = intval($_POST['zadataka']);
	$bodova = floatval(str_replace(",",".",$_POST['bodova']));
	$dan = intval($_POST['day']);
	$mjesec = intval($_POST['month']);
	$godina = intval($_POST['year']);
	$sat = intval($_POST['sat']);
	$minuta = intval($_POST['minuta']);
	$sekunda = intval($_POST['sekunda']);
	if ($_POST['aktivna']) $aktivna=1; else $aktivna=0;
	if ($_POST['attachment']) $attachment=1; else $attachment=0;
	$programskijezik = intval($_POST['_lv_column_programskijezik']);
	
	//-----------------------------------------------
	if (intval($_POST['attachment']) == 1)
	{
		//$dozvoljene_ekstenzije_selected = $_POST['dozvoljene_eks'];
		$dozvoljene_ekstenzije_selected = implode(',',$_POST['dozvoljene_eks']);

	}
	else
	{
		$dozvoljene_ekstenzije_selected = null;
	}
    //-------------------------------------------------
	// Provjera ispravnosti
	if (!preg_match("/\w/",$naziv)) {
		niceerror("Naziv zadaće nije dobar.");
		zamgerlog("los naziv zadace", 3);
		return 0;
	}
	if ($zadataka<=0 || $bodova<=0 || $zadataka>100 || $bodova>100) {
		niceerror("Broj zadataka ili broj bodova nije dobar");
		zamgerlog("los broj zadataka ili bodova", 3);
		return 0;
	}
	if (!checkdate($mjesec,$dan,$godina)) {
		niceerror("Odabrani datum je nemoguć");
		zamgerlog("los datum", 3);
		return 0;
	}
	if ($sat<0 || $sat>24 || $minuta<0 || $minuta>60 || $sekunda<0 || $sekunda>60) {
		niceerror("Vrijeme nije dobro");
		zamgerlog("lose vrijeme", 3);
		return 0;
	}
	$mysqlvrijeme = time2mysql(mktime($sat,$minuta,$sekunda,$mjesec,$dan,$godina));

	// Provjera duplog imena zadace
	$q90 = myquery("select count(*) from zadaca where naziv like '$naziv' and predmet=$predmet and akademska_godina=$ag and id!=$edit_zadaca");
	if (mysql_result($q90,0,0)>0) {
		niceerror("Zadaća pod imenom '$naziv' već postoji! Izaberite neko drugo ime.");
		zamgerlog("zadaca sa nazivom '$naziv' vec postoji", 3);
		return 0;
	}
       
	// Kreiranje nove
	if ($edit_zadaca==0) {
		
		
		$postavka_file = $_FILES['postavka_zadace_file']['name'];

		// Komponentu postavljamo na 6, defaultna komponenta za zadace - FIXME
		$q92 = myquery("insert into zadaca set postavka_zadace = '$postavka_file', dozvoljene_ekstenzije = '$dozvoljene_ekstenzije_selected', predmet=$predmet, akademska_godina=$ag, naziv='$naziv', zadataka=$zadataka, bodova=$bodova, rok='$mysqlvrijeme', aktivna=$aktivna, attachment=$attachment, programskijezik=$programskijezik, komponenta=6");
		$q93 = myquery("select id from zadaca where predmet=$predmet and akademska_godina=$ag and naziv='$naziv' and zadataka=$zadataka and bodova=$bodova and aktivna=$aktivna and attachment=$attachment and programskijezik=$programskijezik and komponenta=6");
		$edit_zadaca = mysql_result($q93,0,0);
		nicemessage("Kreirana nova zadaća '$naziv'");
		zamgerlog("kreirana nova zadaca z$edit_zadaca", 2);
		
		if(!file_exists("$conf_files_path/zadace/$predmet-$ag/postavke"))
		{
			mkdir("$conf_files_path/zadace/$predmet-$ag/postavke");
		}
		 
		 if( $_FILES['postavka_zadace_file']['name'] != "" )
		{
		  copy ( $_FILES['postavka_zadace_file']['tmp_name'], "$conf_files_path/zadace/$predmet-$ag/postavke/".$_FILES['postavka_zadace_file']['name']) ;
		}

	// Izmjena postojece zadace
	} else {
		
		$postavka_file = $_FILES['postavka_zadace_file']['name'];
		// Ako se smanjuje broj zadataka, moraju se obrisati bodovi
		$q94 = myquery("select zadataka, komponenta from zadaca where id=$edit_zadaca");
		$oldzadataka = mysql_result($q94,0,0);
		if ($zadataka<$oldzadataka) {
			// Prilikom brisanja svakog zadatka updatujemo komponentu studenta
			$komponenta = mysql_result($q94,0,1);
			$q96 = myquery("select id,student from zadatak where zadaca=$edit_zadaca and redni_broj>$zadataka and redni_broj<=$oldzadataka order by student");
			$oldstudent=0;
			while ($r96 = mysql_fetch_row($q96)) {
				$q97 = myquery("delete from zadatak where id=$r96[0]");
				if ($oldstudent!=0 && $oldstudent!=$r96[1])
					update_komponente($oldstudent,$predmet,$komponenta);
				$oldstudent=$r96[1];
			}
			if ($oldstudent!=0) // log samo ako je bilo nesto
				zamgerlog("Smanjen broj zadataka u zadaci z$edit_zadaca", 4);
		}
                if(!file_exists("$conf_files_path/zadace/$predmet-$ag/postavke"))
		{
			mkdir("$conf_files_path/zadace/$predmet-$ag/postavke");
		}
		 
		 if( $_FILES['postavka_zadace_file']['name'] != "" )
		{
		 copy ( $_FILES['postavka_zadace_file']['tmp_name'], "$conf_files_path/zadace/$predmet-$ag/postavke/".$_FILES['postavka_zadace_file']['name']) ;
		
		}

		$q94 = myquery("update zadaca set postavka_zadace = '$postavka_file', naziv='$naziv', zadataka=$zadataka, bodova=$bodova, rok='$mysqlvrijeme', aktivna=$aktivna, attachment=$attachment, programskijezik=$programskijezik, dozvoljene_ekstenzije='$dozvoljene_ekstenzije_selected' where id=$edit_zadaca");
		nicemessage("Ažurirana zadaća '$naziv'");
		zamgerlog("azurirana zadaca z$edit_zadaca", 2);
	}
}




// Spisak postojećih zadaća

$_lv_["where:predmet"] = $predmet;
$_lv_["where:akademska_godina"] = $ag;
$_lv_["where:komponenta"] = 6; // namećemo standardnu komponentu za zadaće... FIXME

print "Postojeće zadaće:<br/>\n";
print db_list("zadaca");


// Kreiranje nove zadace ili izmjena postojeće

$izabrana = intval($_REQUEST['_lv_nav_id']);
if ($izabrana==0) $izabrana=intval($edit_zadaca);
if ($izabrana==0) {
	?><p><hr/></p>
	<p><b>Kreiranje zadaće</b><br/>
	<?
	$znaziv=$zaktivna=$zattachment=$zjezik="";
	$zzadataka=0; $zbodova=0;
	$tmpvrijeme=time();
} else {
	?><p><hr/></p>
	<p><b>Izmjena zadaće</b></p>
	<?
	$q100 = myquery("select predmet, akademska_godina, naziv, zadataka, bodova, rok, aktivna, programskijezik, attachment,dozvoljene_ekstenzije from zadaca where id=$izabrana");
	if ($predmet != mysql_result($q100,0,0) || $ag != mysql_result($q100,0,1)) {
		niceerror("Zadaća ne pripada vašem predmetu");
		zamgerlog("zadaca $izabrana ne pripada predmetu pp$predmet",3);
		return;
	}

	$znaziv = mysql_result($q100,0,2);
	$zzadataka = intval(mysql_result($q100,0,3));
	$zbodova = floatval(mysql_result($q100,0,4));
	$tmpvrijeme = mysql2time(mysql_result($q100,0,5));
	if (mysql_result($q100,0,6)==1) $zaktivna="CHECKED"; else $zaktivna="";
	$zjezik = mysql_result($q100,0,7);
	if (mysql_result($q100,0,8)==1) $zattachment="CHECKED"; else $zattachment="";
	$dozvoljene_ekstenzije_selected=mysql_result($q100,0,9);
	
}

$zdan = date('d',$tmpvrijeme);
$zmjesec = date('m',$tmpvrijeme);
$zgodina = date('Y',$tmpvrijeme);
$zsat = date('H',$tmpvrijeme);
$zminuta = date('i',$tmpvrijeme);
$zsekunda = date('s',$tmpvrijeme);



// JavaScript za provjeru validnosti forme
?>
<script language="JavaScript">
function IsNumeric(sText) {
   var ValidChars = "0123456789.";
   var IsNumber=true;
   var Char;

 
   for (i = 0; i < sText.length && IsNumber == true; i++) 
      { 
      Char = sText.charAt(i); 
      if (ValidChars.indexOf(Char) == -1) 
         {
         IsNumber = false;
         }
      }
   return IsNumber;0
   
}

function provjera() {
//	var forma=document.getElementById("kreiranje_zadace");
	var naziv=document.getElementById("naziv");
	if (parseInt(naziv.value.length)<1) {
		alert("Niste unijeli naziv");
		naziv.style.border=1;
		naziv.style.backgroundColor="#FF9999";
		naziv.focus();
		return false;
	}
	var zadataka=document.getElementById("zadataka");
	if (zadataka.value=="0") {
		alert("Broj zadataka u zadaći mora biti veći od nule, npr. 1");
		zadataka.style.border=1;
		zadataka.style.backgroundColor="#FF9999";
		zadataka.focus();
		return false;
	}
	if (!IsNumeric(zadataka.value)) {
		alert("Neispravan broj zadataka!");
		zadataka.style.border=1;
		zadataka.style.backgroundColor="#FF9999";
		zadataka.focus();
		return false;
	}
	var bodova=document.getElementById("bodova");
	if (bodova.value=="0") {
		alert("Broj bodova koje nosi zadaća mora biti veći od nule, npr. 2 boda");
		bodova.style.border=1;
		bodova.style.backgroundColor="#FF9999";
		bodova.focus();
		return false;
	}
	if (!IsNumeric(bodova.value)) {
		alert("Neispravan broj bodova!");
		bodova.style.border=1;
		bodova.style.backgroundColor="#FF9999";
		bodova.focus();
		return false;
	}
	
	return true;
}

function onemoguci_ekstenzije(chk)
{
	var attachment=document.getElementById("attachment");
	var dozvoljene_ekstenzije = document.getElementById("dozvoljene_ekstenzije");
	var jezik = document.getElementById("_lv_column_programskijezik");

	if (attachment.checked)
	{
		dozvoljene_ekstenzije.style.display = '';
		
		jezik.setAttribute("disabled","disabled");
		
	}
	else
	{
		dozvoljene_ekstenzije.style.display = 'none';
		jezik.removeAttribute("disabled");

		for (i = 0; i < chk.length; i++)
		chk[i].checked = false;
	}

	}

</script>
<?



// Forma za kreiranje zadaće

print genform("POST", "kreiranje_zadace\" enctype=\"multipart/form-data\" onsubmit=\"return provjera();");

?>
<input type="hidden" name="akcija" value="edit">
<input type="hidden" name="zadaca" value="<?=$izabrana?>">
Naziv: <input type="text" name="naziv" id="naziv" size="30" value="<?=$znaziv?>"><br><br>

Broj zadataka: <input type="text" name="zadataka" id="zadataka" size="4" value="<?=$zzadataka?>">
&nbsp;&nbsp;&nbsp;&nbsp; Max. broj bodova: <input type="text" name="bodova" id="bodova" size="3" value="<?=$zbodova?>"><br><br>

Rok za slanje: <?=datectrl($zdan,$zmjesec,$zgodina)?>
&nbsp;&nbsp; <input type="text" name="sat" size="1" value="<?=$zsat?>"> <b>:</b> <input type="text" name="minuta" size="1" value="<?=$zminuta?>"> <b>:</b> <input type="text" name="sekunda" size="1" value="<?=$zsekunda?>"> <br><br>

<input type="checkbox" name="aktivna" <?=$zaktivna?>> Aktivna
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input type="checkbox" value="1" id="attachment" onclick="onemoguci_ekstenzije(this.form.dozvoljene_eks)" name="attachment" <?=$zattachment?>> Slanje zadatka u formi attachmenta<br><br>
<span id="dozvoljene_ekstenzije" style="display:none" title="Oznacite željene ekstenzije">
Dozvoljene ekstenzije (Napomena:Ukoliko ne odaberete ni jednu ekstenziju sve ekstenzije postaju dozvoljene): 
<? $dozvoljene_ekstenzije_selected=explode(',',$dozvoljene_ekstenzije_selected);
foreach($dozvoljene_ekstenzije as $doz_ext) { ?>
<input type="checkbox" name="dozvoljene_eks[]" <? if(in_array($doz_ext,$dozvoljene_ekstenzije_selected)) echo 'checked="checked"'?> value="<? echo $doz_ext; ?>" /> <? echo $doz_ext; ?>
<? } ?>
<br><br>
</span>
Programski jezik: <?=db_dropdown("programskijezik", $zjezik)?><br><br>
Postavka zadaće: <br>
<input type="file" name="postavka_zadace_file" size="45">
  <br><br>
<input type="submit" value=" Pošalji "> <input type="reset" value=" Poništi ">
<?
if ($izabrana>0) {
	?><input type="submit" name="brisanje" value=" Obriši ">
         	
<?

}
echo "<script> onemoguci_ekstenzije('');</script>";
?>
</form>
<?



/*
$_lv_["label:programskijezik"] = "Programski jezik";
$_lv_["label:zadataka"] = "Broj zadataka";
$_lv_["label:bodova"] = "Max. broj bodova";
$_lv_["label:attachment"] = "Slanje zadatka u formi attachmenta";
$_lv_["label:rok"] = "Rok za slanje";
$_lv_["hidden:vrijemeobjave"] = 1;
print db_form("zadaca");*/



// Formular za masovni unos zadaća

$format = intval($_POST['format']);
if (!$_POST['format']) {
	$q110 = myquery("select vrijednost from preference where korisnik=$userid and preferenca='mass-input-format'");
	if (mysql_num_rows($q110)>0) $format = mysql_result($q110,0,0);
	else //default vrijednost
		$format=0;
}

$separator = intval($_POST['separator']);
if (!$_POST['separator']) {
	$q120 = myquery("select vrijednost from preference where korisnik=$userid and preferenca='mass-input-separator'");
	if (mysql_num_rows($q120)>0) $separator = mysql_result($q120,0,0);
	else //default vrijednost
		$separator=0;
}

$q130 = myquery("select count(*) from zadaca where predmet=$predmet and akademska_godina=$ag");
if (mysql_result($q130,0,0)>0) {

?><p><hr/></p>
<p><b>Masovni unos zadaća</b><br/>
<?

print genform("POST");
?><input type="hidden" name="fakatradi" value="0">
<input type="hidden" name="akcija" value="massinput">
<input type="hidden" name="nazad" value="">
<input type="hidden" name="brpodataka" value="1">
<input type="hidden" name="duplikati" value="0">

Izaberite zadaću: <?=db_dropdown("zadaca");?>
Izaberite zadatak: <select name="zadatak"><?
$q112 = myquery("select zadataka from zadaca where predmet=$predmet and akademska_godina=$ag order by zadataka desc limit 1");
for ($i=1; $i<=mysql_result($q112,0,0); $i++) {
	print "<option value=\"$i\">$i</option>\n";
}
?>
</select><br/><br/>

<textarea name="massinput" cols="50" rows="10"><?
if (strlen($_POST['nazad'])>1) print $_POST['massinput'];
?></textarea><br/>
<br/>Format imena i prezimena: <select name="format" class="default">
<option value="0" <? if($format==0) print "SELECTED";?>>Prezime[TAB]Ime</option>
<option value="1" <? if($format==1) print "SELECTED";?>>Ime[TAB]Prezime</option>
<option value="2" <? if($format==2) print "SELECTED";?>>Prezime Ime</option>
<option value="3" <? if($format==3) print "SELECTED";?>>Ime Prezime</option></select>&nbsp;
Separator: <select name="separator" class="default">
<option value="0" <? if($separator==0) print "SELECTED";?>>Tab</option>
<option value="1" <? if($separator==1) print "SELECTED";?>>Zarez</option></select><br/><br/>
<input type="submit" value="  Dodaj  ">
</form></p>


<?=genform("POST")?>
<p><hr/></p>
<h4>Import zadaca iz Moodle-a</h4>
<p><br/><b>Napomena: </b>Sve zadace moraju imati ista imena kao u Moodle-u!</p>
<input type="hidden" name="akcija" value="import_svih">

<?
//Import svih zadaca
if ($_POST['akcija'] == "import_svih" && check_csrf_token()) {
	//Tabelama sam pristupao preko moodle.ime_tabele i zamger.ime_tabele tako da ne treba mysql_select_db('ime_baze')
	$link_moodle = mysql_connect("localhost", "moodle", "moodle") or die ("Greska pri konekciji: " .mysql_error());
	$link_zamger = mysql_connect("localhost", "zamger", "zamger") or die ("Greska pri konekciji: " .mysql_error());
	/*
	if (!$link_moodle) {
		mysql_select_db("moodle");
	}
	if (!$link_zamger) {
		mysql_select_db("zamger");
	}*/

	//Prikupljanje sifre predmeta iz zamger baze radi poredjenja
	$sifra = mysql_query("SELECT sifra FROM zamger.predmet WHERE id='$predmet'", $link_zamger) or die ("Greska u sifra: " .mysql_error());
	$sifra_value = mysql_fetch_array($sifra);
	
	//Prikupljanje imena zadaca iz Zamger baze
	$zadaca_ime = mysql_query("SELECT naziv FROM zamger.zadaca", $link_zamger) or die ("Greska u zadaca_id: " .mysql_error());
	while ($zi = mysql_fetch_array($zadaca_ime)) {
		//Prikupljanje podataka iz Moodle tabele
		//Prikupljaju se sifra predmeta, ime zadace i JMBG svih studenata
		//Posto se pri prikupljanju zadace porede po imenu trebaju imati isti naziv u Moodle-u kao i u Zamgeru
		$query1 = mysql_query("SELECT c.idnumber, gi.itemname, u.idnumber
			FROM moodle.mdl_grade_grades gg, moodle.mdl_user u, moodle.mdl_grade_items gi, moodle.mdl_course c
			WHERE gi.itemname = '$zi[0]' AND c.idnumber = '$sifra_value[0]' AND
			gg.userid=u.id AND gg.itemid=gi.id AND gi.courseid=c.id", $link_moodle) or die ("Greska u query1: " .mysql_error());
		
		//Ubacivanje podataka u zamger tabelu
		while ($row1 = mysql_fetch_array($query1)) {
			//$bodovi sadrzi vrijednost zadace iz $row1 za date vrijednosti (trenutni student, trenutna zadaca i trenutni predmet)
			$bodovi = mysql_query("SELECT gg.finalgrade
				FROM moodle.mdl_grade_grades gg, moodle.mdl_user u, moodle.mdl_grade_items gi, moodle.mdl_course c
				WHERE gi.itemname='$row1[1]' AND c.idnumber='$row1[0]' AND u.idnumber='$row1[2]' AND
				gg.userid=u.id AND gg.itemid=gi.id AND gi.courseid=c.id", $link_moodle) or die ("Greska u bodovi: " .mysql_error());
			$bodovi_value = mysql_fetch_array($bodovi);
		
			//zadaca_id sadrzi id zadace trenutne vrijednosti u $row1
			$zadaca_id = mysql_query("SELECT z.id
				FROM zamger.zadaca z, zamger.predmet p
				WHERE z.naziv='$row1[1]' AND p.sifra='$row1[0]' AND p.id=z.predmet", $link_zamger) or die ("Greska u zadaca_id: " .mysql_error());
			$zadaca_id_value = mysql_fetch_array($zadaca_id);
		
			//$student_id vraca id studenta koji si trenutno cita iz $row1
			$student_id = mysql_query("SELECT o.id
				FROM zamger.osoba o
				WHERE o.jmbg='$row1[2]'", $link_zamger) or die ("Greska u student_id: " .mysql_error());
			$student_id_value = mysql_fetch_array($student_id);
		
			$query2 = "INSERT INTO zamger.zadatak (zadaca, redni_broj, student, status, bodova, vrijeme, userid)
				VALUES ('$zadaca_id_value[0]', '1', '$student_id_value[0]', '5', '$bodovi_value[0]', 'SYSDATE()', '$userid')";
		
			mysql_query($query2, $link_zamger) or die ("Greska u query2: " .mysql_error());
		}
	}
}
?>
<table>
<tr>
	<td><input type="submit" name="sve_zadace" value="Import"><br/></td>
</tr>
<tr>
	<td><a href="index.php?sta=saradnik/grupa&id=<?=$predmet?>">Link na tabelu sa zadacama</a></td>
</tr>
</table>
</form>


<?

} else {

	?><p><hr/></p>
	<p><b>Masovni unos zadaća NIJE MOGUĆ</b><br/>
	Najprije kreirajte zadaću koristeći formular iznad</p>
	<?
}



}

?>
