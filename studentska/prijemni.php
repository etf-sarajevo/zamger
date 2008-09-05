<?

// STUDENTSKA/PRIJEMNI - modul za administraciju prijemnog ispita

// v3.9.1.0 (2008/06/05) + Import koda by eldin.starcevic@hotmail.com
// v3.9.1.1 (2008/06/09) + Dodan post-guard, ispravljen bug sa ispisom datuma u pregledu, dodana default vrijednost za opći uspjeh
// v3.9.1.2 (2008/07/11) + Finalna verzija korištena za prijemni na ETFu
// v3.9.1.3 (2008/08/28) + Uhakovan drugi termin za prijemni (popraviti), centriran i reorganizovan prikaz


// TODO: koristiti tabelu osoba



function studentska_prijemni() {

global $_lv_;


?>
<center>
<table border="0" width="100%">


<?

if ($_REQUEST['akcija'] != "pregled") {

?>
<tr><td valign="top" width="200">
<!--Tabela za linkove koji otvaraju ostale stranice vezane za aplikciju-->
Prijemni ispit 7. jula 2008:<br/>


<table bgcolor="" style="border:1px;border-style:solid;border-color:black">
	<tr>
		<td align="left"><a href="?sta=studentska/prijemni&akcija=pregled&termin=1">Pregled kandidata</a></td>
	</tr>
	<tr>&nbsp;</tr>
	<tr>
		<td><a href="?sta=studentska/prijemni&akcija=prijemni&termin=1">Unos bodova sa prijemnog ispita</a></td>
	</tr>
	<tr>&nbsp;</tr>
	<tr>
		<td><a href="?sta=studentska/prijemni&akcija=unos_kriterij&termin=1">Unos kriterija za upis</a></td>
	</tr>
	</tr>
	<tr>&nbsp;</tr>
	<tr>
		<td><a href="?sta=studentska/prijemni&akcija=spisak&termin=1">Spisak kandidata za prijemni ispit</a></td>
		<!--&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="?sta=izvjestaj/prijemni&akcija=kandidati&iz=bih&termin=1">BiH</a><br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="?sta=izvjestaj/prijemni&akcija=kandidati&iz=strani&termin=1">Strani državljani</a><br />
		</td>-->
        <tr>&nbsp;</tr> 	 
	<tr> 	 
		<td>Rang liste kandidata:<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="?sta=izvjestaj/prijemni&_lv_column_studij=3&termin=1">Automatika i elektronika</a><br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="?sta=izvjestaj/prijemni&_lv_column_studij=4&termin=1">Elektroenergetika</a><br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="?sta=izvjestaj/prijemni&_lv_column_studij=2&termin=1">Računarstvo i informatika</a><br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="?sta=izvjestaj/prijemni&_lv_column_studij=5&termin=1">Telekomunikacije</a><br />

		</td> 	 
	</tr> 	 
</table>

<br/><br/>
Prijemni ispit 1. septembra 2008:<br/>


<table bgcolor="" style="border:1px;border-style:solid;border-color:black">
	<tr>
		<td align="left"><a href="?sta=studentska/prijemni&akcija=pregled&termin=2">Pregled kandidata</a></td>
	</tr>
	<tr>&nbsp;</tr>
	<tr>
		<td><a href="?sta=studentska/prijemni&akcija=prijemni&termin=2">Unos bodova sa prijemnog ispita</a></td>
	</tr>
	<tr>&nbsp;</tr>
	<tr>
		<td><a href="?sta=studentska/prijemni&akcija=unos_kriterij&termin=2">Unos kriterija za upis</a></td>
	</tr>
	</tr>
	<tr>&nbsp;</tr>
	<tr>
		<td><a href="?sta=studentska/prijemni&akcija=spisak&termin=2">Spisak kandidata za prijemni ispit</a></td>
		<!--&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="?sta=izvjestaj/prijemni&akcija=kandidati&iz=bih&termin=2">BiH</a><br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="?sta=izvjestaj/prijemni&akcija=kandidati&iz=strani&termin=2">Strani državljani</a><br />
		</td>-->
        <tr>&nbsp;</tr> 	 
	<tr> 	 
		<td>Rang liste kandidata:<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="?sta=izvjestaj/prijemni&_lv_column_studij=3&termin=2">Automatika i elektronika</a><br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="?sta=izvjestaj/prijemni&_lv_column_studij=4&termin=2">Elektroenergetika</a><br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="?sta=izvjestaj/prijemni&_lv_column_studij=2&termin=2">Računarstvo i informatika</a><br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="?sta=izvjestaj/prijemni&_lv_column_studij=5&termin=2">Telekomunikacije</a><br />

		</td> 	 
	</tr> 	 
</table>




</td><td width="10">&nbsp;</td>


<?

} // if ($_REQUEST['akcija'] != "pregled" )

?>
<td valign="top">

<h1>Prijemni ispit</h1>
<br />
<?




// Default akcija je unos novog studenta
if ($_REQUEST['akcija']=="") $_REQUEST['akcija']="unos";


// Predlink za izvještaj
if ($_REQUEST['akcija']=="spisak") {
	?>
	<form action="index.php" method="GET">
	<input type="hidden" name="sta" value="izvjestaj/prijemni">
	<input type="hidden" name="akcija" value="kandidati">
	<input type="hidden" name="termin" value="<?=$_REQUEST['termin']?>">
	<h2>Spisak kandidata za prijemni ispit</h2>

	<p>Državljanstvo: <select name="iz"><option value="bih">BiH</option>
	<option value="strani">Strani državljani</option>
	<option>Svi zajedno</option>
	</select></p>
	<p>Sortirano po: <select name="sort"><option value="abecedno">imenu i prezimenu</option>
	<option>ukupnom broju bodova</option>
	</select></p>
	<input type="submit" value=" Kreni ">
	</form>
	<?
}

// Unos bodova sa prijemnog ispita

if ($_REQUEST['akcija']=="prijemni") {


	?>
	<h3>Unos bodova sa prijemnog ispita</h3>
	<br />
	<hr color="black" width="100%">
	<a href="index.php?sta=studentska/prijemni&akcija=prijemni&sort=prezime">Sortirano po prezimenu</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="index.php?sta=studentska/prijemni&akcija=prijemni&sort=unos">Sortirano po redoslijedu unosa</a>
	<hr color="black" width="100%">

	<?

	// AJAH i prateće funkcije

	print ajah_box();

	?>
	<SCRIPT language="JavaScript">
	function dobio_focus(element) {
		element.style.borderColor='red';
	}
	function izgubio_focus(element) {
		element.style.borderColor='black';
		var id = parseInt(element.id.substr(8));
		var vrijednost = element.value;
		if (vrijednost!=origval[id])
			ajah_start("index.php?c=N&sta=common/ajah&akcija=prijemni_unos&idpolja="+id+"&vrijednost="+vrijednost,"document.getElementById('prijemni'+"+id+").focus()");
		origval[id]=vrijednost;
	}
	function enterhack(element,e,gdje) {
		if(e.keyCode==13) {
			element.blur();
			document.getElementById('prijemni'+gdje).focus();
			document.getElementById('prijemni'+gdje).select();
		}
	}
	var origval=new Array();
	</SCRIPT>


	<table border="1" bordercolordark="grey" cellspacing="0">
		<tr><td><b>R. br.</b></td><td width="300"><b>Prezime i ime</b></td>
		<td><b>Bodovi (srednja šk.)</b></td>
		<td><b>Bodovi (prijemni)</b></td></tr>
	<?

	$upit = "";

	if ($_REQUEST['termin']==2) $upit = "SELECT id, ime, prezime, prijemni_ispit_dva, izasao_na_prijemni, opci_uspjeh+kljucni_predmeti+dodatni_bodovi FROM prijemni WHERE prijavio_drugi=1 OR prijavio_drugi=2";
	else $upit .= "SELECT id, ime, prezime, prijemni_ispit, izasao_na_prijemni, opci_uspjeh+kljucni_predmeti+dodatni_bodovi FROM prijemni WHERE prijavio_drugi=0 OR prijavio_drugi=2";


	if ($_REQUEST['sort'] == "prezime") $upit .= " ORDER BY prezime,ime";
	else $upit .= " ORDER BY id";

	$q = myquery($upit);
	$id=0;
	while ($r = mysql_fetch_row($q)) {
		if ($id!=0)
			print "$r[0])\"></tr>\n";
		$id=$r[0];
		if ($r[4]==0) $bodova="/"; else $bodova=$r[3]; // izasao na prijemni?

		?>
		<SCRIPT language="JavaScript"> origval[<?=$id?>]=<?=$bodova?></SCRIPT>
		<tr><td><?=$id?></td>
		<td><?=$r[2]?> <?=$r[1]?></td>
		<td align="center"><?=(round($r[5]*10)/10)?></td>
		<td align="center"><input type="text" id="prijemni<?=$id?>" size="2" value="<?=$bodova?>" style="border:1px black solid" onblur="izgubio_focus(this)" onfocus="dobio_focus(this)" onkeydown="enterhack(this,event,<?
	}
	?>0)"></tr>
	<?
	?>
	</table>
	<?

}

/*

STARA VERZIJA UNOSA BODOVA
Nihada rekla da će praviti puno grešaka


// Obrada podataka poslanih iz formulara za prijemni ispit

if ($_REQUEST['akcija']=="prijemni_bodovi") {
	$nizBodoviPrijemni = explode("\n", $_POST['prijemni_ispit']);
	$success = 0;
	$failed = 0;
	$pom = count($nizBodoviPrijemni);
	for ($i = 0 ; $i < $pom ; $i++){
		$nizPrezimeImeBodovi = explode($_POST['separator'], $nizBodoviPrijemni[$i]);
		$nizPrezimeIme = explode(" ", $nizPrezimeImeBodovi[0]);
		$bodovi = doubleval($nizPrezimeImeBodovi[1]);
		$prezime = my_escape($nizPrezimeIme[0]);
		$ime = my_escape($nizPrezimeIme[1]);
		if ($bodovi < 0 || $bodovi > 40){
			niceerror("Bodovi moraju biti u opsegu od 0 do 40!");
			exit;
		}
		
		$sqlUpdate = myquery("UPDATE prijemni SET prijemni_ispit = $bodovi WHERE ime = '$ime' AND prezime = '$prezime'");
		if($sqlUpdate)
			$success++;
		else
			$failed++;
	}
	
	if($success >= 1 && $failed == 0)
		nicemessage("Baza je ažurirana.");
	else
		niceerror("Nastala je greška pri ažuriranju!");
}


// Formular za prijemni ispit

if ($_REQUEST['akcija']=="prijemni") {


	?>
	<h3>Unos bodova sa prijemnog ispita</h3>
	<br />

	<table align="left" border="0" width="320" bgcolor="lightgray">
	<form action="index.php" method="POST">
	<input type="hidden" name="sta" value="studentska/prijemni">
	<input type="hidden" name="akcija" value="prijemni_bodovi">
		<tr>
			<td><br></td>
		</tr>
		<tr>
			<td colspan="2"><textarea cols="50" rows="10" name="prijemni_ispit"></textarea></td>
		</tr>
		<tr>
			<td><br></td>
		</tr>
		<tr>
			<td align="left">Separator:<select name="separator">
				<option value=",">Zarez</option>
				<option value=", ">Zarez i razmak</option>
				</select>
			</td>
			<td align="right" width="150"><input type="submit" name="spremi_bodove" value="Spremi"></td>
		</tr>
	</form>
	</table>
	
	<table align="right" border="0" width="320" bgcolor="">
		<tr>
			<td align="left"><a href="?sta=studentska/prijemni&akcija=pregled">Pregled kandidata</a></td>
		</tr>
		<tr>&nbsp;</tr>
		<tr>
			<td><a href="?sta=studentska/prijemni&akcija=unos">Dodaj kandidata</a></td>
		</tr>
		<tr>&nbsp;</tr>
		<tr>
			<td><a href="?sta=studentska/prijemni&akcija=kandidati&iz=bih">Kandidati BiH</a></td>
		</tr>
		<tr>&nbsp;</tr>
		<tr>
			<td><a href="?sta=studentska/prijemni&akcija=kandidati&iz=strani">Kandidati (strani državljani)</a></td>
		</tr>
	</table>
	
	<?

}

*/


// brisanje kandidata

if ($_REQUEST["akcija"]=="obrisi") {
	$rbr = intval($_GET['redni_broj']);
	if ($rbr>0) {
		$sqlDelete="DELETE FROM prijemni WHERE id=$rbr";
		myquery($sqlDelete);
		myquery("delete from prijemniocjene where prijemni=$rbr");
	}
	
	$_REQUEST['akcija']="pregled";
}


// Obrada podataka sa forme za unos kandidata i ekran za potvrdu

if ($_REQUEST['akcija'] == 'unospotvrda') {

	$rime=my_escape($_REQUEST['ime']);
	$rprezime=my_escape($_REQUEST['prezime']);
	$rmjestorod=my_escape($_REQUEST['mjesto_rodjenja']);
	$rdrzavljanstvo=my_escape($_REQUEST['drzavljanstvo']);
	$rzavrskola=my_escape($_REQUEST['zavrsena_skola']);
	$rjmbg=$_REQUEST['jmbg'];
	$radresa=my_escape($_REQUEST['adresa']);
	$rtelefon=my_escape($_REQUEST['telefon_roditelja']);
	$rkanton=intval($_REQUEST['_lv_column_kanton']);
	if ($_REQUEST['tip_studija']) $rredovni=1; else $rredovni=0;
	if ($_REQUEST['student_generacije']) $rgener=1; else $rgener=0;
	$opi=$_REQUEST['odsjek_prvi_izbor'];
	$odi=$_REQUEST['odsjek_drugi_izbor'];
	$oti=$_REQUEST['odsjek_treci_izbor'];
	$oci=$_REQUEST['odsjek_cetvrti_izbor'];
	$ropci=floatval($_REQUEST['opci_uspjeh']);
	$rkljucni=floatval($_REQUEST['kljucni_predmeti']);
	$rdodatni=floatval($_REQUEST['dodatni_bodovi']);
	$rprijemni=floatval($_REQUEST['prijemni']);
	$editovanje = intval($_REQUEST['editovanje']);
	$rprijemni_dva=floatval($_REQUEST['prijemni_dva']);

	if ($_REQUEST['termin1']=="on" && $_REQUEST['termin2']=="on") {
		$rtermin=2;
	} else if ($_REQUEST['termin2']=="on") {
		$rtermin=1;
	} else /* if ($_REQUEST['termin1']=="on") */ {
		$rtermin=0;
	}

	if (preg_match("/(\d+).*?(\d+).*?(\d+)/",$_REQUEST['datum_rodjenja'],$matches)) {
		$dan=$matches[1]; $mjesec=$matches[2]; $godina=$matches[3];
		if ($godina<100)
			if ($godina<50) $godina+=2000; else $godina+=1900;
		if ($godina<1000)
			if ($godina<900) $godina+=2000; else $godina+=1000;
	}

	// Podaci su ok, ubaci u bazu
	if ($_REQUEST['ok']==1) {
		$novi_id = intval($_REQUEST['novi_id']);
		$stari_id = intval($_REQUEST['stari_id']);

		// Da li se redni broj ponavlja??
		if ($editovanje<=0) {
			$q = myquery("select count(*) from prijemni where id=$novi_id");
			if (mysql_result($q,0,0)>0) $novi_id=$stari_id; // Stari ID se ne bi trebao ponavljati...
		}

		$dioupita = "set ime='$rime', prezime='$rprezime', datum_rodjenja='$godina-$mjesec-$dan', mjesto_rodjenja='$rmjestorod', drzavljanstvo='$rdrzavljanstvo', zavrsena_skola='$rzavrskola', jmbg='$rjmbg', adresa='$radresa', telefon='$rtelefon', kanton='$rkanton', redovni=$rredovni, odsjek_prvi='$opi', odsjek_drugi='$odi', odsjek_treci='$oti', odsjek_cetvrti='$oci', opci_uspjeh=$ropci, kljucni_predmeti=$rkljucni, dodatni_bodovi=$rdodatni, student_generacije=$rgener, prijemni_ispit=$rprijemni, prijemni_ispit_dva=$rprijemni_dva, prijavio_drugi=$rtermin, id=$novi_id";

		if ($editovanje>0)
			$q = myquery("update prijemni $dioupita where id=$stari_id");
		else
			$q = myquery("insert into prijemni $dioupita");
		if ($novi_id!=$stari_id) 
			$q = myquery("update prijemniocjene set prijemni=$novi_id where prijemni=$stari_id");

		$_REQUEST['akcija']="unos";

		// Korisnik kliknuo na dugme NAZAD
		if ($_REQUEST['nazad']) {
			$q2 = myquery("select id from prijemni where ime='$rime' and prezime='$rprezime'");
			$_REQUEST['akcija']="unos";
			$_REQUEST['edit']=mysql_result($q2,0,0);
		} else if (intval($_REQUEST['edit'])>0 && !$_REQUEST['nazad']) {
			$_REQUEST['akcija']="pregled";
			$_REQUEST['edit']=0;
		} else {
			// POST-guard
			?>
			<script language="JavaScript">
			window.location="?sta=studentska/prijemni";
			</script>
			<?
		}

	} else {

		// Dodatne provjere integriteta koje je lakše uraditi u PHPu nego u JavaScriptu
		if (!preg_match("/\w/",$rime)) {
			niceerror("Ime nije ispravno");
		}
		if (!preg_match("/\w/",$rprezime)) {
			niceerror("Prezime nije ispravno");
		}
		if (!preg_match("/\w/",$rmjestorod)) {
			niceerror("Mjesto rođenja nije ispravno");
		}
		if (!preg_match("/\w/",$rdrzavljanstvo)) {
			niceerror("Državljanstvo nije ispravno");
		}
		if ($rdrzavljanstvo != "BiH" && $rkanton!=13) {
			niceerror("Državljanstvo je različito od 'BiH' (".$rdrzavljanstvo."), a kanton nije stavljen na 'Strani državljanin'");
		}
		if (testjmbg($rjmbg) != "") {
			niceerror("JMBG neispravan: ".testjmbg($rjmbg));
		}
		if (preg_match("/(\d+).*?(\d+).*?(\d+)/",$_REQUEST['datum_rodjenja'],$matches)) {
			// Ovo je već urađeno:
			// $dan=$matches[1]; $mjesec=$matches[2]; $godina=$matches[3];
			if (!checkdate($mjesec,$dan,$godina)) niceerror("Datum rođenja je kalendarski nemoguć ($dan. $mjesec. $godina)");
			$jdan=intval(substr($_REQUEST['jmbg'],0,2));
			$jmjesec=intval(substr($_REQUEST['jmbg'],2,2));
			$jgodina=intval(substr($_REQUEST['jmbg'],4,3));
			if ($jgodina>900) $jgodina+=1000; else $jgodina+=2000;
			if ($dan!=$jdan || $mjesec!=$jmjesec || $godina!=$jgodina)
				niceerror("Uneseni datum rođenja se ne poklapa s onim u JMBGu");
		} else {
			niceerror("Datum rođenja nije ispravan - ne sadrži dovoljan broj cifara.");
		}

		// Da li se redni broj ponavlja??
		$novi_id = intval($_REQUEST['novi_id']);
		if ($editovanje==0) {
			$q = myquery("select count(*) from prijemni where id=$novi_id");
			if (mysql_result($q,0,0)>0) {
				niceerror("Redni broj $novi_id je već unesen! Izaberite neki drugi redni broj. $editovanje");
			}
		}

		// Prikaz forme za provjeru
		$q = myquery("select naziv from kanton where id=$rkanton");
		$naziv_kantona = mysql_result($q,0,0);
?>
<h3>Provjera podataka</h3>
<br />

<table width="70%" cellspacing="3" style="border:1px;border-style:solid;border-color:black">
	<tr>
		<td align="left" width="30%">Redni broj:</td><td align="left"><b><?=$novi_id?></b></td>
	</tr>
	<tr>
		<td align="left" width="30%">Ime:</td><td align="left"><b><?=$rime?></b></td>
	</tr>
	<tr bgcolor="#DDDDDD">
		<td align="left">Prezime:</td><td align="left"><b><?=$rprezime?></b></td>
	</tr>
	<tr>
		<td align="left">Datum rođenja:</td><td align="left"><b><?=$dan?>. <?=$mjesec?>. <?=$godina?></b></td>
	</tr>
	<tr bgcolor="#DDDDDD">
		<td align="left">Mjesto rođenja:</td><td align="left"><b><?=$rmjestorod?></b></td>
	</tr>
	<tr>
		<td align="left">Državljanstvo:</td><td align="left"><b><?=$rdrzavljanstvo?></b></td>
	</tr>
	<tr bgcolor="#DDDDDD">
		<td align="left">Završena škola:</td><td align="left"><b><?=$rzavrskola?></b></td>
	</tr>
	<tr>
		<td align="left">JMBG:</td><td align="left"><b><?=$rjmbg?></b></td>
	</tr>
	<tr bgcolor="#DDDDDD">
		<td align="left">Adresa:</td><td align="left"><b><?=$radresa?></b></td>
	</tr>
	<tr>
		<td align="left">Telefon roditelja:</td><td align="left"><b><?=$rtelefon?></b></td>
	</tr>
	<tr bgcolor="#DDDDDD">
		<td align="left">Kanton:</td><td align="left"><b><?=$naziv_kantona?></b></td>
	</tr>
	<tr>
		<td align="left">Tip studija:</td><td align="left"><b><? if ($rredovni==1) print "redovni"; else print "nije redovni";?></b></td>
	</tr>
	<tr>
		<td align="left">Učenik generacije:</td><td align="left"><b><? if ($rgener==1) print "DA"; else print "NE";?></b></td>
	</tr>
	<tr bgcolor="#DDDDDD">
		<td align="left">Izbor odsjeka:</td><td align="left"><b><?=$opi." ".$odi." ".$oti." ".$oci?></b></td>
	</tr>
	<tr>
		<td align="left">Bodovi (opći uspjeh):</td><td align="left"><b><?=$ropci?></b></td>
	</tr>
	<tr bgcolor="#DDDDDD">
		<td align="left">Bodovi (ključni predmeti):</td><td align="left"><b><?=$rkljucni?></b></td>
	</tr>
	<tr>
		<td align="left">Bodovi (dodatni bodovi):</td><td align="left"><b><?=$rdodatni?></b></td>
	</tr>
<?
if ($rtermin==0) $termin="7. jula 2008";
else if ($rtermin==1) $termin="1. septembra 2008";
else if ($rtermin==2) $termin="7. jula i 1. septembra 2008";
?>
	<tr bgcolor="#DDDDDD">
		<td align="left">Termini prijemnog:</td><td align="left"><b><?=$termin?></b></td>
	</tr>
<? 
if ($rprijemni>0) { ?>
	<tr bgcolor="#DDDDDD">
		<td align="left">Bodovi (prijemni 7.7):</td><td align="left"><b><?=$rprijemni?></b></td>
	</tr>
<?
}
if ($rprijemni_dva>0) { ?>
	<tr bgcolor="#DDDDDD">
		<td align="left">Bodovi (prijemni 1.9):</td><td align="left"><b><?=$rprijemni_dva?></b></td>
	</tr>
<?
}
?>
</table>
<br />

<?=genform("POST")?><input type="hidden" name="_lv_column_kanton" value="<?=$rkanton?>"><input type="hidden" name="ok" value="1"><input type="submit" value=" Potvrda "> <input type="submit" name="nazad" value=" Nazad "></form><?
	}
}




// Spisak kandidata

if ($_REQUEST['akcija'] == "pregled") {
	
	$termin = $_REQUEST['termin'];

	?>
	<h3>Pregled kandidata</h3>
	<br />
	
	<hr color="black" width="2500">
	<a href="index.php?sta=studentska/prijemni&akcija=pregled&sort=prezime&termin=<?=$termin?>">Sortirano po prezimenu</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="index.php?sta=studentska/prijemni&akcija=pregled&sort=id&termin=<?=$termin?>">Sortirano po rednom broju</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="index.php?sta=studentska/prijemni&akcija=unos&termin=<?=$termin?>">Dodaj kandidata</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="index.php?sta=studentska/prijemni&akcija=prijemni&termin=<?=$termin?>">Unos bodova sa prijemnog ispita</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="index.php?sta=studentska/prijemni&akcija=kandidati&iz=bih&termin=<?=$termin?>">Kandidati BiH</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="index.php?sta=studentska/prijemni&akcija=kandidati&iz=strani&termin=<?=$termin?>">Kandidati (strani državljani)</a>
	<hr color="black" width="2500">
	
	<?
	
	$imena_kantona=array();
	$q = myquery("select id,naziv from kanton");
	while ($r = mysql_fetch_row($q)) {
		$imena_kantona[$r[0]]=$r[1];
	}
	
	
	$sqlSelect="SELECT id, ime, prezime, UNIX_TIMESTAMP(datum_rodjenja), mjesto_rodjenja,
				drzavljanstvo, zavrsena_skola, jmbg, adresa, telefon, kanton, opci_uspjeh,
				kljucni_predmeti, dodatni_bodovi, redovni, ";
	if ($_REQUEST['termin']=="2") $sqlSelect .= "prijemni_ispit_dva AS prijemni_bodovi, ";
	else $sqlSelect .= "prijemni_ispit AS prijemni_bodovi, ";

	$sqlSelect .= "odsjek_prvi, odsjek_drugi, odsjek_treci, odsjek_cetvrti, student_generacije FROM prijemni ";

	if ($_REQUEST['termin']=="1")
		$sqlSelect .= "WHERE prijavio_drugi=0 OR prijavio_drugi=2 ";
	else if ($_REQUEST['termin']=="2")
		$sqlSelect .= "WHERE prijavio_drugi=1 OR prijavio_drugi=2 ";


	if ($_REQUEST['sort'] == "id") $sqlSelect .= "ORDER BY id";
	else $sqlSelect .= "ORDER BY prezime";
				
	$q=myquery($sqlSelect);
	
	
	?>
	
	<table width="2500" border="1" cellpadding="1" cellspacing="1" bordercolor="#000000">
	<tr>
	<td width="10"><b>R.br.</b></td>
	<td><b>Prezime</b></td>
	<td><b>Ime</b></td>
	<td width="100"><b>Datum rođenja</b></td>
	<td><b>Mjesto rođenja</b></td>
	<td><b>Državljanstvo</b></td>
	<td><b>Završena škola</b></td>
	<td><b>Uč. gen.</b></td>
	<td width="115"><b>Jmbg</b></td>
	<td><b>Adresa</b></td>
	<td><b>Telefon</b></td>
	<td width="200"><b>Kanton</b></td>
	<td width="90"><b>Opći uspjeh</b></td>
	<td width="90"><b>Ključni pred.</b></td>
	<td width="90"><b>Dodatni bod.</b></td>
	<td width="90"><b>Prijemni ispit</b></td>
	<td width="70"><b>Tip studija</b></td>
	<td width="80"><b>Odsjek prvi</b></td>
	<td width="80"><b>Odsjek drugi</b></td>
	<td width="80"><b>Odsjek treći</b></td>
	<td width="80"><b>Odsjek četvrti</b></td>
	<td width="10"><b>R.br.</b></td>
	<td align="center"><b>Opcije</b></td>
	</tr>
	
	<?
	$brojac = 1;
	while ($kandidat=mysql_fetch_array($q)) {
		?>
		
		<tr>
		<td align="center"><?php //echo "$brojac";
		echo $kandidat["id"];?></td>
		<td><?php echo $kandidat["prezime"];?></td>
		<td><?php echo $kandidat["ime"];?></td>
		<td><?php echo date("d. m. Y",$kandidat["UNIX_TIMESTAMP(datum_rodjenja)"]);?></td>
		<td><?php echo $kandidat["mjesto_rodjenja"];?></td>
		<td><?php echo $kandidat["drzavljanstvo"];?></td>
		<td><?php echo $kandidat["zavrsena_skola"];?></td>
		<td><?php if ($kandidat["student_generacije"]>0) print "DA"; else print "&nbsp;"?></td>
		<td><?php echo $kandidat["jmbg"];?></td>
		<td><?php echo $kandidat["adresa"];?></td>
		<td><?php echo $kandidat["telefon"];?></td>
		<td><?php echo $imena_kantona[$kandidat["kanton"]];?></td>
		<td align="center"><?php echo $kandidat["opci_uspjeh"];?></td>
		<td align="center"><?php echo $kandidat["kljucni_predmeti"];?></td>
		<td align="center"><?php echo $kandidat["dodatni_bodovi"];?></td>
		<td align="center"><?php echo $kandidat["prijemni_bodovi"];?></td>
		<td align="center"><?php if ($kandidat["redovni"]) echo "redovni"; else echo "paralelni";?></td>
		<td align="center"><?php echo $kandidat["odsjek_prvi"];?></td>
		<td align="center"><?php echo $kandidat["odsjek_drugi"];?></td>
		<td align="center"><?php echo $kandidat["odsjek_treci"];?></td>
		<td align="center"><?php echo $kandidat["odsjek_cetvrti"];?></td>
		<td align="center"><?php //echo "$brojac";
		echo $kandidat["id"];?></td>
		
		<td align="center">
		<a href="?sta=studentska/prijemni&akcija=obrisi&redni_broj=<?php echo $kandidat["id"];?> ">Obriši&nbsp;&nbsp;</a>
		<a href="?sta=studentska/prijemni&akcija=unos&edit=<?php echo $kandidat["id"];?>">Izmijeni</a>
		</td>
		</tr>
		
		<?
		
		$brojac++;
	}

	?>
	
	</table>
	
	<hr color="black" width="2500">
	<a href="index.php?sta=studentska/prijemni&akcija=pregled&sort=prezime&termin=<?=$termin?>">Sortirano po prezimenu</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="index.php?sta=studentska/prijemni&akcija=pregled&sort=id&termin=<?=$termin?>">Sortirano po rednom broju</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="index.php?sta=studentska/prijemni&akcija=unos&termin=<?=$termin?>">Dodaj kandidata&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a>
	<a href="index.php?sta=studentska/prijemni&akcija=prijemni&termin=<?=$termin?>">Unos bodova sa prijemnog ispita&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</font></a>
	<a href="index.php?sta=studentska/prijemni&akcija=kandidati&iz=bih&termin=<?=$termin?>">Kandidati BiH&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</font></a>
	<a href="index.php?sta=studentska/prijemni&akcija=kandidati&iz=strani&termin=<?=$termin?>">Kandidati (strani državljani)</font></a>
	<hr color="black" width="2500">
	<?

}



// Unos novog kandidata u tabelu za prijemni

if ($_REQUEST['akcija']=="unos") {


// Editovanje starog kandidata
$editid=intval($_REQUEST['edit']);
$eredovni=1;
$ekanton=$eopci=$ekljucni=$edodatni=$eprijemni=$eprijemni_dva=0;
$eprijavio_drugi=-1;
if ($editid>0) {
	$q = myquery("select ime, prezime, UNIX_TIMESTAMP(datum_rodjenja), mjesto_rodjenja, drzavljanstvo, zavrsena_skola, jmbg, adresa, telefon, kanton, redovni, odsjek_prvi, odsjek_drugi, odsjek_treci, odsjek_cetvrti, opci_uspjeh, kljucni_predmeti, dodatni_bodovi, prijemni_ispit, student_generacije, prijemni_ispit_dva, prijavio_drugi from prijemni where id=$editid");
	$eime = mysql_result($q,0,0);
	$eprezime = mysql_result($q,0,1);
	$edatum = date("d. m. Y.",mysql_result($q,0,2));
	$emjesto = mysql_result($q,0,3);
	$edrz = mysql_result($q,0,4);
	$eskola = mysql_result($q,0,5);
	$ejmbg = mysql_result($q,0,6);
	$eadresa = mysql_result($q,0,7);
	$etelefon = mysql_result($q,0,8);
	$ekanton = mysql_result($q,0,9);
	$eredovni = mysql_result($q,0,10);
	$eopi = mysql_result($q,0,11);
	$eodi = mysql_result($q,0,12);
	$eoti = mysql_result($q,0,13);
	$eoci = mysql_result($q,0,14);
	$eopci = mysql_result($q,0,15);
	$ekljucni = mysql_result($q,0,16);
	$edodatni = mysql_result($q,0,17);
	$eprijemni = mysql_result($q,0,18);
	$egener = mysql_result($q,0,19);
	$eprijemni_dva = mysql_result($q,0,20);
	$eprijavio_drugi = mysql_result($q,0,21);
}


$theid = $editid;
if ($theid<=0) {
	$q = myquery("select id from prijemni order by id desc limit 1");
	if (mysql_num_rows($q)<1) $theid=1;
	else $theid = mysql_result($q,0,0)+1;
	// Zauzmi mjesto u bazi
	$q = myquery("select prijemni from prijemni_trenutno_edituje where vrijeme>NOW()-INTERVAL 4 HOUR order by prijemni");
	while ($r=mysql_fetch_row($q))
		if ($theid==$r[0]) $theid++;
	$q = myquery("insert into prijemni_trenutno_edituje set prijemni=$theid");
}

$odsjeci = array("AE", "EE", "RI", "TK");

// Tabela za unos podataka - design
?>
<h3>Unos kandidata</h3>
<br />

<SCRIPT language="JavaScript">
function update_izbore() {
	var odsjeci = new Array("AE","EE","TK","RI");
	var prvi = document.getElementById('odsjek_prvi_izbor');
	odzuti(prvi);
	var drugi = document.getElementById('odsjek_drugi_izbor');
	var treci = document.getElementById('odsjek_treci_izbor');
	var cetvrti = document.getElementById('odsjek_cetvrti_izbor');
	var drugval = drugi.value;
	while (drugi.length>1)
		drugi.options[1]=null;
	for (i=0; i<4; i++) {
		if (odsjeci[i] != prvi.value) { 
			drugi.options[drugi.length]=new Option(odsjeci[i],odsjeci[i]);
			if (drugval==odsjeci[i]) drugi.selectedIndex=drugi.length-1;
		}
	}
	var trecval = treci.value;
	while (treci.length>1)
		treci.options[1]=null;
	for (i=0; i<4; i++) {
		if (odsjeci[i] != prvi.value && odsjeci[i] != drugi.value) { 
			treci.options[treci.length]=new Option(odsjeci[i],odsjeci[i]);
			if (trecval==odsjeci[i]) treci.selectedIndex=treci.length-1;
		}
	}
	var cetval = cetvrti.value;
	while (cetvrti.length>1)
		cetvrti.options[1]=null;
	for (i=0; i<4; i++) {
		if (odsjeci[i] != prvi.value && odsjeci[i] != drugi.value && odsjeci[i] != treci.value) { 
			cetvrti.options[cetvrti.length]=new Option(odsjeci[i],odsjeci[i]);
			if (cetval==odsjeci[i]) cetvrti.selectedIndex=cetvrti.length-1;
		}
	}
}

function odzuti(nesto) {
	nesto.style.backgroundColor = '#FFFFFF';
}

// Predji na sljedece polje
function enterhack(e,gdje) {
	if(e.keyCode==13) document.getElementById(gdje).focus();
}

</SCRIPT>

<form action="index.php" method="POST" id="glavnaforma">
<input type="hidden" name="sta" value="studentska/prijemni">
<input type="hidden" name="akcija" value="unospotvrda">
<input type="hidden" name="stari_id" value="<?=$theid?>">
<? if ($editid>0) { // Editovanje
?><input type="hidden" name="editovanje" value="1">
<? } ?>

<table border="0" cellpadding="3" cellspacing="0">
	<tr>
		<td width="130" align="left">Redni broj:</td>
		<td><input maxlength="50" size="5" name="novi_id" id="novi_id" type="text" value="<?=$theid?>" autocomplete="off" onkeypress="enterhack(event,'ime')"><font color="#FF0000">*</font></td>
	</tr>
	<tr>
		<td width="130" align="left">Ime kandidata:</td>
		<td><input maxlength="50" size="17" name="ime" id="ime" type="text" <? if ($eime) { ?> value="<?=$eime?>"<? } else { ?> style="background-color:#FFFF00" oninput="odzuti(this)" <? } ?> autocomplete="off" onkeypress="enterhack(event,'prezime')"><font color="#FF0000">*</font></td>
	</tr>
	<tr>
		<td width="125" align="left">Prezime kandidata:</td>
		<td><input maxlength="50" size="17" name="prezime" id="prezime" type="text" <? if ($eprezime) { ?> value="<?=$eprezime?>"<? } else { ?> style="background-color:#FFFF00" oninput="odzuti(this)" <? } ?> autocomplete="off" onkeypress="enterhack(event,'datum_rodjenja')"><font color="#FF0000">*</font></td>
	</tr>
	<tr>
		<td width="125" align="left">Datum rođenja:</td>
		<td><input maxlength="20" size="17" name="datum_rodjenja" id="datum_rodjenja" type="text" <? if ($edatum) { ?> value="<?=$edatum?>"<? } else { ?> style="background-color:#FFFF00" oninput="odzuti(this)" <? } ?> autocomplete="off" onkeypress="enterhack(event,'mjesto_rodjenja')"><font color="#FF0000">*</font></td>
	</tr>
	<tr>
		<td width="125" align="left">Mjesto rođenja:</td>
		<td><input maxlength="50" size="17" name="mjesto_rodjenja" id="mjesto_rodjenja" type="text" <? if ($emjesto) { ?> value="<?=$emjesto?>"<? } else { ?> style="background-color:#FFFF00" oninput="odzuti(this)" <? } ?> onkeypress="enterhack(event,'drzavljanstvo')"><font color="#FF0000">*</font></td>
	</tr>
	<tr>
		<td width="125" align="left">Državljanstvo:</td>
		<td><input maxlength="40" size="17" name="drzavljanstvo" id="drzavljanstvo" type="text"  <? if ($edrz) { ?> value="<?=$edrz?>"<? } else { ?> value="BiH" <? } ?> onkeypress="enterhack(event,'zavrsena_skola')"><font color="#FF0000">*</font></td>
	</tr>
	<tr>
		<td width="125" align="left">Završena škola:</td>
		<td><input maxlength="50" size="17" name="zavrsena_skola" id="zavrsena_skola" type="text" <? if ($eskola) { ?> value="<?=$eskola?>"<? } else { ?> style="background-color:#FFFF00" oninput="odzuti(this)" <? } ?> onkeypress="enterhack(event,'jmbg')"></td>
	</tr>
	<tr>
		<td width="125" align="left">JMBG:</td>
		<td><input maxlength="13" size="17" name="jmbg" id="jmbg" type="text" <? if ($ejmbg) { ?> value="<?=$ejmbg?>"<? } else { ?> style="background-color:#FFFF00" oninput="odzuti(this)" <? } ?> autocomplete="off" onkeypress="enterhack(event,'adresa')"></td>
	</tr>
	<tr>
		<td width="125" align="left">Adresa:</td>
		<td><input maxlength="50" size="17" name="adresa" id="adresa" type="text" <? if ($eadresa) { ?> value="<?=$eadresa?>"<? } else { ?> style="background-color:#FFFF00" oninput="odzuti(this)" <? } ?> autocomplete="off" onkeypress="enterhack(event,'telefon_roditelja')"></td>
	</tr>
	<tr>
		<td width="125" align="left">Telefon roditelja:</td>
		<td><input maxlength="30" size="17" name="telefon_roditelja" id="telefon_roditelja" type="text"  <? if ($etelefon) { ?> value="<?=$etelefon?>"<? } else { ?> style="background-color:#FFFF00" oninput="odzuti(this)" <? } ?> autocomplete="off" onkeypress="enterhack(event,'kanton')"></td>
	</tr>
	<tr>
		<td width="125" align="left">Kanton:</td>
		<td><?=db_dropdown("kanton",$ekanton,"-- Izaberite kanton --")?></td>
	</tr>
	<tr>
		<td width="125" align="left">Redovni studij?</td>
		<td><input type="checkbox" name="tip_studija"  <? if ($eredovni) { ?> checked="checked" <? } ?> value="1"></td>
	</tr>
	<tr>
		<td width="125" align="left">Učenik generacije?</td>
		<td><input type="checkbox" name="student_generacije" <? if ($egener) { ?> checked="checked" <? } ?> value="1"></td>
	</tr>
	<tr>
		<td width="125" align="left">Odsjek:</td>
		<td>
		<table width="100%" border="0" align="center">
			<tr><td>Prvi izbor</td><td>Drugi izbor</td><td>Treći izbor</td><td>Četvrti izbor</td></tr>
			<tr>
				<td><select name="odsjek_prvi_izbor" id="odsjek_prvi_izbor" onchange="update_izbore()" <? if (!$eopi) { ?> style="background-color:#FFFF00"<? } ?>><option></option><? foreach($odsjeci as $odsjek) {
					print "<option value=\"$odsjek\"";
					if ($odsjek==$eopi) print " selected";
					print ">$odsjek</option>";
				}?></select></td>
				<td><select name="odsjek_drugi_izbor" id="odsjek_drugi_izbor" onchange="update_izbore()"><option></option><? foreach($odsjeci as $odsjek) {
					print "<option value=\"$odsjek\"";
					if ($odsjek==$eodi) print " selected";
					print ">$odsjek</option>";
				}?></select></td>
				<td><select name="odsjek_treci_izbor" id="odsjek_treci_izbor" onchange="update_izbore()"><option></option><? foreach($odsjeci as $odsjek) {
					print "<option value=\"$odsjek\"";
					if ($odsjek==$eoti) print " selected";
					print ">$odsjek</option>";
				}?></select></td>
				<td><select name="odsjek_cetvrti_izbor" id="odsjek_cetvrti_izbor" onchange="update_izbore()"><option></option><? foreach($odsjeci as $odsjek) {
					print "<option value=\"$odsjek\"";
					if ($odsjek==$eoci) print " selected";
					print ">$odsjek</option>";
				}?></select></td>
			</tr>
		</table>
		</td>
	</tr>
	<tr>
		<td width="125" align="left">Termin prijemnog:</td>
		<td><input type="checkbox" name="termin1" id="termin1" <?if ($eprijavio_drugi==0 || $eprijavio_drugi==2) print "checked"?>>07. 07. 2008 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="termin2" id="termin2" <?if ($eprijavio_drugi==1 || $eprijavio_drugi==2) print "checked"?>>01. 09. 2008</td>
	</tr>
<? if ($eprijemni>0) { ?>
	<tr>
		<td width="125" align="left">Prijemni (7.7):</td>
		<td><input maxlength="50" size="17" name="prijemni" id="prijemni" type="text" value="<?=$eprijemni?>" autocomplete="off"></td>
	</tr>
<? } else { ?>
	<input type="hidden" name="prijemni" value="0">
<? } ?>
<? if ($eprijemni_dva>0) { ?>
	<tr>
		<td width="125" align="left">Prijemni (1.9)</td>
		<td><input maxlength="50" size="17" name="prijemni_dva" id="prijemni_dva" type="text" value="<?=$eprijemni_dva?>" autocomplete="off"></td>
	</tr>
<? } else { ?>
	<input type="hidden" name="prijemni_dva" value="0">
<? } ?>
</table>
<br />

<b>Unos ocjena.</b><br/>

<?

/*

UNOS OCJENA TOKOM SREDNJE ŠKOLE
Prva varijanta - ne valja, potrebno čuvati podatke o ocjenama, regenerisati prosjeke itd...


?>

<!-- Unos ocjena tokom srednje skole -->



<script language="JavaScript">
var sumaocjena=0;
var brojocjena=0;
var sumaocjena1=0;
var brojocjena1=0;
var sumaocjena2=0;
var brojocjena2=0;
var sumaocjena3=0;
var brojocjena3=0;
var sumaocjena4=0;
var brojocjena4=0;
var unique=0;
var sumakljucnih=0;
var brojkljucnih=0;
function obrisi_ocjenu(broc,ocjena,kljucval,rz) {
	var razred = document.getElementById('razred').value;

/*        if (rz=="I razred") {
	  	sumaocjena1 = parseInt(sumaocjena1)-parseInt(ocjena);
		brojocjena1--;
	}
        if (rz=="II razred") {
	  	sumaocjena2 = parseInt(sumaocjena2)-parseInt(ocjena);
		brojocjena2--;
	}
        if (rz=="III razred") {
	  	sumaocjena3 = parseInt(sumaocjena3)-parseInt(ocjena);
		brojocjena3--;
	}
        if (rz=="IV razred") {
	  	sumaocjena4 = parseInt(sumaocjena4)-parseInt(ocjena);
		brojocjena4--;
	}* /
  	sumaocjena = parseInt(sumaocjena)-parseInt(ocjena);
	brojocjena--;
	if (kljucval>0) {
		sumakljucnih=parseInt(sumakljucnih)-parseInt(ocjena);
		brojkljucnih--;
	}

/*        var prosjek1;
	if (brojocjena1>0) prosjek1 = Math.round((sumaocjena1/brojocjena1)*10)/10; else prosjek1=0;
        var prosjek2;
	if (brojocjena2>0) prosjek2 = Math.round((sumaocjena2/brojocjena2)*10)/10; else prosjek2=0;
        var prosjek3;
	if (brojocjena3>0) prosjek3 = Math.round((sumaocjena3/brojocjena3)*10)/10; else prosjek3=0;
        var prosjek4;
	if (brojocjena4>0) prosjek4 = Math.round((sumaocjena4/brojocjena4)*10)/10; else prosjek4=0;
        var prosjeku = (prosjek1+prosjek2+prosjek3+prosjek4)*2;* /
	var prosjeku;
	if (brojocjena>0) prosjeku = Math.round((sumaocjena/brojocjena)*10)/10; else prosjeku=0;

	document.getElementById('opci_uspjeh').value = prosjeku*8;

	if (brojkljucnih>0) 
		document.getElementById('kljucni_predmeti').value = Math.round((sumakljucnih/brojkljucnih)*40)/10;
	else
		document.getElementById('kljucni_predmeti').value = '0';


	// Pokusacemo obrisati ovo kino iz html-a
	var stari = document.getElementById('dalje').innerHTML;
	var pos1 = stari.indexOf('<!-- brojocjena ' + broc + '-->');
	var pos2 = stari.indexOf('<!-- kraj brojocjena -->',pos1+1) + 24;
	document.getElementById('dalje').innerHTML = stari.substr(0,pos1) + stari.substr(pos2);
	document.getElementById('razred').value=razred;
}

function addnew() {
	var ocjena=document.getElementById('ocjena').value;
	if (ocjena<2 || ocjena>5) {
		alert("Ocjena nije u rasponu 2-5!");
		return false;
	}
	var kljucni=document.getElementById('kljucni').checked;
	var razred=document.getElementById('razred').value;

	// Update polja sa bodovima
/*        if (razred=="I razred") {
		sumaocjena1=parseInt(sumaocjena1)+parseInt(ocjena);
		brojocjena1++;
	}
        if (razred=="II razred") {
		sumaocjena2=parseInt(sumaocjena2)+parseInt(ocjena);
		brojocjena2++;
	}
        if (razred=="III razred") {
		sumaocjena3=parseInt(sumaocjena3)+parseInt(ocjena);
		brojocjena3++;
	}
        if (razred=="IV razred") {
		sumaocjena4=parseInt(sumaocjena4)+parseInt(ocjena);
		brojocjena4++;
	}* /
	sumaocjena=parseInt(sumaocjena)+parseInt(ocjena);
	brojocjena++;
	var kljuctext='';
	var kljucval=0;
	if (kljucni==true) {
		sumakljucnih=parseInt(sumakljucnih)+parseInt(ocjena);
		brojkljucnih++;
		kljuctext='DA';
		kljucval=1;
	}
/*        var prosjek1;
	if (brojocjena1>0) prosjek1 = Math.round((sumaocjena1/brojocjena1)*10)/10; else prosjek1=0;
        var prosjek2;
	if (brojocjena2>0) prosjek2 = Math.round((sumaocjena2/brojocjena2)*10)/10; else prosjek2=0;
        var prosjek3;
	if (brojocjena3>0) prosjek3 = Math.round((sumaocjena3/brojocjena3)*10)/10; else prosjek3=0;
        var prosjek4;
	if (brojocjena4>0) prosjek4 = Math.round((sumaocjena4/brojocjena4)*10)/10; else prosjek4=0;
        var prosjeku = (prosjek1+prosjek2+prosjek3+prosjek4)*2;* /
	var prosjeku;
	if (brojocjena>0) prosjeku=Math.round((sumaocjena/brojocjena)*10)/10; 
	else prosjeku=0;
	document.getElementById('opci_uspjeh').value = prosjeku*8;
	if (brojkljucnih>0) document.getElementById('kljucni_predmeti').value = Math.round((sumakljucnih/brojkljucnih)*40)/10;
//alert('Suma '+sumakljucnih+' broj '+brojkljucnih);
//alert(kljucni);
//
	// Formiranje HTMLa
	unique++;
	var html = '<!-- brojocjena ' + unique + '--><tr><td>' + document.getElementById('razred').value + '</td><td><b>' + ocjena + '</b></td><td><b>' + kljuctext + ' (<a onclick=' + "\"" + 'obrisi_ocjenu(' + unique + ',' + ocjena + ',' + kljucval + ',\''+razred+'\')'+"\""+'>obriši</a>)</b></td></tr><!-- kraj brojocjena --><!-- komentar -->';
	var stari = document.getElementById('dalje').innerHTML;
	document.getElementById('dalje').innerHTML = stari.replace('<!-- komentar -->',html);

	// Reset forme
	document.getElementById('kljucni').checked=false;
	document.getElementById('ocjena').value='';
	document.getElementById('razred').value=razred;
	document.getElementById('ocjena').focus();

	return true;
}

/*function provjeri(varijablu) {
	if (document.getElementById(varijablu).value=='') {
		alert("Niste unijeli "+varijablu);
		return false;
	}
	return true;
}


<div id="dalje" name="dalje">
<table cellspacing="0" cellpadding="4">
	<tr>
		<td>Razred</td>
		<td>Ocjena</td>
		<td>Ključni predmet</td>
		<td>&nbsp;</td>
	</tr>
	<!-- komentar -->
	<tr>
		<td><select name="razred" id="razred">
			<option value="I razred">I razred</option>
			<option value="II razred">II razred</option>
			<option value="III razred">III razred</option>
			<option value="IV razred">IV razred</option>
		</select></td>
		<td><input type="text" id="ocjena" name="ocjena" size="5" autocomplete="off" onkeypress="enterhack(event,'kljucni')"></td>
		<td align="center"><input type="radio" id="kljucni" name="kljucni" value="1" onkeypress="enterhack(event,'posalji_ocjenu')"> Da <input type="radio" id="kljucni" name="kljucni" value="0" onkeypress="enterhack(event,'posalji_ocjenu')"> Ne</td>
		<td><input type="button" id="posalji_ocjenu" name="posalji_ocjenu" value=" Ok " onclick="addnew();"></td>
	</tr>
</table>
</div>

<?
*/


// NOVA VARIJANTA OCJENA IZ SREDNJE
// Korištenje AJAHa...



	// AJAH i prateće funkcije

	print ajah_box();

	?>
	<SCRIPT language="JavaScript">
	// Funkcija koja racuna bodove za opci uspjeh i kljucne predmete
	function izracunaj_bodove() {
		// Opci uspjeh
		var sumaocjena=0, brojocjena=0;
		for (i=0; i<20; i++) {
			for (j=1; j<=4; j++) {
				var id = <?=$theid?>*1000 + j*100 + i;
				var val = document.getElementById('prijemniocjene'+id).value;
				if (val != "/" && val != "") {
					sumaocjena += parseInt(val);
					brojocjena++;
				}
			}
		}
		var prosjeku;
		if (brojocjena>0) prosjeku=Math.round((sumaocjena/brojocjena)*10)/10; 
		else prosjeku=0;
		document.getElementById('opci_uspjeh').value = prosjeku*8;

		var sumekljucni=new Array(), brojkljucni=new Array(), prosjecikljucni=new Array();
		for (var i=1; i<=3; i++) {
			sumekljucni[i]=0; brojkljucni[i]=0;
			var pocni_od=1;
			if (i==3) pocni_od=3;
			for (var j=pocni_od; j<=4; j++) {
				var id = <?=$theid?>*1000 + j*100 + i+90;
				var val = document.getElementById('prijemniocjene'+id).value;
				if (val != "/" && val != "") {
					sumekljucni[i] += parseInt(val);
					brojkljucni[i]++;
				}
			}
			if (brojkljucni[i]>0)
				prosjecikljucni[i] = sumekljucni[i]/brojkljucni[i];
			else prosjecikljucni[i]=0;
		}
		var bodovi_kljucni = (prosjecikljucni[1]+prosjecikljucni[2]+prosjecikljucni[3])/3 * 4;
		bodovi_kljucni = Math.round(bodovi_kljucni*10)/10;
		document.getElementById('kljucni_predmeti').value=bodovi_kljucni;
	}

	function dobio_focus(element) {
		element.style.borderColor='red';
	}

	function izgubio_focus(element) {
		element.style.borderColor='black';

		var vrijednost = element.value;
		var id = parseInt(element.id.substr(14));
		var prijemni = Math.floor(id/1000);
		var razred = Math.floor((id-prijemni*1000)/100);
		var tipocjene = id-prijemni*1000-razred*100;
		if (tipocjene>=90) tipocjene -= 90;
		else tipocjene=0;

		if (vrijednost == "") {
			vrijednost="/";
			element.value="/";
		}
		if (origval[id]=="") origval[id]="/";
		if (vrijednost != "/" && (!parseInt(vrijednost) || parseInt(vrijednost)<1 || parseInt(vrijednost)>5)) {
			alert("Neispravna ocjena: "+vrijednost+" !\nOcjena mora biti u opsegu 1-5 ili znak / za poništavanje "+id);
			element.value = origval[id];
			element.focus();
			element.select();
			return false;
		}
		if (origval[id]=="/" && vrijednost!="/")
			ajah_start("index.php?c=N&sta=common/ajah&akcija=prijemni_ocjene&prijemni="+prijemni+"&nova="+vrijednost+"&subakcija=dodaj&razred="+razred+"&tipocjene="+tipocjene,"document.getElementById('prijemni'+"+id+").focus()");
		else if (origval[id]!="/" && vrijednost=="/")
			ajah_start("index.php?c=N&sta=common/ajah&akcija=prijemni_ocjene&prijemni="+prijemni+"&stara="+origval[id]+"&subakcija=obrisi&razred="+razred+"&tipocjene="+tipocjene,"document.getElementById('prijemni'+"+id+").focus()");
		else if (origval[id]!=vrijednost)
			ajah_start("index.php?c=N&sta=common/ajah&akcija=prijemni_ocjene&prijemni="+prijemni+"&nova="+vrijednost+"&stara="+origval[id]+"&subakcija=izmijeni&razred="+razred+"&tipocjene="+tipocjene,"document.getElementById('prijemni'+"+id+").focus()");

		origval[id]=vrijednost;

		izracunaj_bodove();
	}

	function enterhack2(element,e,gdje) {
		if(e.keyCode==13) {
			element.blur();
			document.getElementById('prijemniocjene'+gdje).focus();
			document.getElementById('prijemniocjene'+gdje).select();
		}
	}
	var origval=new Array();
	</SCRIPT>

	<table border="0" cellspacing="0" cellpadding="1">
	<tr><td valign="top">

	<table border="0" cellspacing="0" cellpadding="1">
		<tr><td>&nbsp;</td><td align="center"><b> I </b></td><td align="center"><b> II </b></td><td align="center"><b> III </b></td><td align="center"><b> IV </b></td></tr>
	<?

	$q = myquery("SELECT razred, ocjena, tipocjene FROM prijemniocjene WHERE prijemni=$theid");
	$razred = array();
	$kljucni = array();
	while ($r = mysql_fetch_row($q)) {
		if ($r[2]==0) $razred[$r[0]][]= $r[1];
		else $kljucni[$r[0]][$r[2]]=$r[1];
	}

	for ($i=0; $i<20; $i++) {
		?>
		<tr><td align="right"><?=($i+1)?>.</td>
		<?
		for ($j=1; $j<=4; $j++) {
			$id = $theid*1000 + $j*100 + $i;
			if ($i<19) $nextid = $id+1;
			else if ($j<4) $nextid = $theid*1000 + ($j+1)*100 + $i;
			else $nextid=$theid*1000 + 100 + 91;
			if (array_key_exists($i, $razred[$j]))
				$vr = $razred[$j][$i];
			else
				$vr = "";
			?>
			<SCRIPT language="JavaScript"> origval[<?=$id?>]='<?=$vr?>'</SCRIPT>
			<td align="center"><input type="text" id="prijemniocjene<?=$id?>" size="4" value="<?=$vr?>" style="border:1px black solid" onblur="izgubio_focus(this)" onfocus="dobio_focus(this)" onkeydown="enterhack2(this,event,<?=$nextid?>)"></td>
			<?
		}
		?></tr><?
	}
	?>
	</table>

	</td><td width="30">&nbsp;</td>
	<td valign="top">

	<table border="0" cellspacing="0" cellpadding="1">
		<tr><td>&nbsp;</td><td align="center"><b> I </b></td><td align="center"><b> II </b></td><td align="center"><b> III </b></td><td align="center"><b> IV </b></td></tr>
		<?
	for ($i=1; $i<=3; $i++) {
		if ($i==1) print "<tr><td><b>Jezik</b></td>\n";
		else if ($i==2) print "<tr><td><b>Matematika</b></td>\n";
		else if ($i==3) print "<tr><td><b>Fizika</b></td>\n";

		$pocni_od = 1;
		if ($i==3) $pocni_od=3;

		for ($j=1; $j<$pocni_od; $j++) print "<td>&nbsp;</td>\n";
		for ($j=$pocni_od; $j<=4; $j++) {
			$id = $theid*1000 + $j*100 + $i+90;
			if (array_key_exists($i, $kljucni[$j])) $vr=$kljucni[$j][$i];
			else $vr = "";
			if ($j<4) $nextid = $theid*1000 + ($j+1)*100 + $i+90;
			else if ($i==1) $nextid = $theid*1000 + 100 + $i+90+1;
			else if ($i==2) $nextid = $theid*1000 + 3*100 + $i+90+1;
 			else $nextid=0;
			?>
			<SCRIPT language="JavaScript"> origval[<?=$id?>]='<?=$vr?>'</SCRIPT>
			<td align="center"><input type="text" id="prijemniocjene<?=$id?>" size="4" value="<?=$vr?>" style="border:1px black solid" onblur="izgubio_focus(this)" onfocus="dobio_focus(this)" onkeydown="enterhack2(this,event,<?=$nextid?>)"></td>
			<?
		}
	}
	?>
	</table>

	</td></tr></table>
	<?


?>

<br /><br />
<!-- Tablica bodova -->

<fieldset style="width:200px" style="background-color:#0099FF">
<legend>Bodovi</legend>
<table align="center" width="600" border="0">
	<tr>
		<td align="left">Opći uspjeh: 
		<input maxlength="10" size="5" name="opci_uspjeh" id="opci_uspjeh" type="text" value="<?=$eopci?>"><font color="#FF0000">*</font></td>
		<td align="left">Ključni predmeti:
		<input maxlength="10" size="5" name="kljucni_predmeti" id="kljucni_predmeti" type="text" value="<?=$ekljucni?>"><font color="#FF0000">*</font></td>
		<td align="left">Dodatni bodovi:
		<input maxlength="10" size="5" name="dodatni_bodovi" type="text" value="<?=$edodatni?>"></td>
	</tr>
</table>
</fieldset>
</form>



<!-- Provjera ispravnosti svih polja na formularu prije slanja -->

<SCRIPT language="JavaScript">
function provjeri(varijablu) {
	var nesto = document.getElementById(varijablu);
	if(nesto.value=="") {
		alert("Niste unijeli "+varijablu);
		nesto.focus();
		self.scrollTo(nesto.offsetLeft,nesto.offsetTop);
		return false;
	}
	return true;
}
function provjeri_sve() {
	if (!provjeri('ime')) return false;
	if (!provjeri('prezime')) return false;
	if (!provjeri('datum_rodjenja')) return false;
	if (!provjeri('mjesto_rodjenja')) return false;
	if (!provjeri('drzavljanstvo')) return false;

	var nesto = document.getElementsByName('_lv_column_kanton');
	if (nesto[0].value=='-1') {
		alert("Niste izabrali kanton");
		nesto[0].focus();
		self.scrollTo(nesto[0].offsetLeft,nesto[0].offsetTop);
		return false;
	}

	var nesto = document.getElementById('odsjek_prvi_izbor');
	if (nesto.value=='') {
		alert("Niste izabrali odsjek");
		nesto.focus();
		self.scrollTo(nesto.offsetLeft,nesto.offsetTop);
		return false;
	}

	var nesto = document.getElementById('opci_uspjeh');
	if (parseInt(nesto.value)==0) {
		alert("Opći uspjeh je nula!");
		return false;
	}
	var nesto = document.getElementById('kljucni_predmeti');
	if (parseInt(nesto.value)==0) {
		alert("Bodovi za ključne predmete su nula!");
		return false;
	}

	var nesto = document.getElementById('termin1');
	var nesto2 = document.getElementById('termin2');
	if (!nesto.checked && !nesto2.checked) {
		alert("Niste izabrali nijedan termin za prijemni!");
		return false;
	}

	document.getElementById('glavnaforma').submit();
	return true;
}

</script>



<p><font color="#FF0000">*</font> - Sva polja označena zvjezdicom su obavezna.<br/>
<input type="button" value="Snimi" onclick="provjeri_sve()"></p>

<p>&nbsp;</p>


<!--Tabela za linkove koji otvaraju ostale stranice vezane za aplikciju-->
<!--
<table border="0" bgcolor="">
<tr>
	<td>Prijemni ispit 7. jula 2008:<br/>


<table border="0" bgcolor="">
	<tr>
		<td align="left"><a href="?sta=studentska/prijemni&akcija=pregled&termin=1">Pregled kandidata</a></td>
	</tr>
	<tr>&nbsp;</tr>
	<tr>
		<td><a href="?sta=studentska/prijemni&akcija=prijemni&termin=1">Unos bodova sa prijemnog ispita</a></td>
	</tr>
	<tr>&nbsp;</tr>
	<tr>
		<td><a href="?sta=studentska/prijemni&akcija=unos_kriterij&termin=1">Unos kriterija za upis</a></td>
	</tr>
	</tr>
	<tr>&nbsp;</tr>
	<tr>
		<td>Spisak kandidata za prijemni ispit:<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="?sta=izvjestaj/prijemni&akcija=kandidati&iz=bih&termin=1">BiH</a><br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="?sta=izvjestaj/prijemni&akcija=kandidati&iz=strani&termin=1">Strani državljani</a><br />
		</td>
        <tr>&nbsp;</tr> 	 
	<tr> 	 
		<td>Rang liste kandidata:<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="?sta=izvjestaj/prijemni&_lv_column_studij=3&termin=1">Automatika i elektronika</a><br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="?sta=izvjestaj/prijemni&_lv_column_studij=4&termin=1">Elektroenergetika</a><br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="?sta=izvjestaj/prijemni&_lv_column_studij=2&termin=1">Računarstvo i informatika</a><br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="?sta=izvjestaj/prijemni&_lv_column_studij=5&termin=1">Telekomunikacije</a><br />

		</td> 	 
	</tr> 	 
</table>


	</td><td>Prijemni ispit 1. septembra 2008:<br/>


<table border="0" bgcolor="">
	<tr>
		<td align="left"><a href="?sta=studentska/prijemni&akcija=pregled&termin=2">Pregled kandidata</a></td>
	</tr>
	<tr>&nbsp;</tr>
	<tr>
		<td><a href="?sta=studentska/prijemni&akcija=prijemni&termin=2">Unos bodova sa prijemnog ispita</a></td>
	</tr>
	<tr>&nbsp;</tr>
	<tr>
		<td><a href="?sta=studentska/prijemni&akcija=unos_kriterij&termin=2">Unos kriterija za upis</a></td>
	</tr>
	</tr>
	<tr>&nbsp;</tr>
	<tr>
		<td>Spisak kandidata za prijemni ispit:<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="?sta=izvjestaj/prijemni&akcija=kandidati&iz=bih&termin=2">BiH</a><br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="?sta=izvjestaj/prijemni&akcija=kandidati&iz=strani&termin=2">Strani državljani</a><br />
		</td>
        <tr>&nbsp;</tr> 	 
	<tr> 	 
		<td>Rang liste kandidata:<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="?sta=izvjestaj/prijemni&_lv_column_studij=3&termin=2">Automatika i elektronika</a><br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="?sta=izvjestaj/prijemni&_lv_column_studij=4&termin=2">Elektroenergetika</a><br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="?sta=izvjestaj/prijemni&_lv_column_studij=2&termin=2">Računarstvo i informatika</a><br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="?sta=izvjestaj/prijemni&_lv_column_studij=5&termin=2">Telekomunikacije</a><br />

		</td> 	 
	</tr> 	 
</table>


</td></tr></table>

<br />
-->

<?

} // if ($akcija_unos==1)



// Forma za izbor vrste rang liste
if ($_REQUEST['akcija'] == "rang_liste") {
	?>
	<form action="index.php" METHOD="GET">
	<input type="hidden" name="sta" value="izvjestaj/prijemni">
	Odsjek: <? 
		$_lv_['where:moguc_upis']="1"; 
		echo db_dropdown("studij")
	?><br /><br />
	<input type="submit" value=" Prikaži ">
	</form>
	<?
}



//Unos kriterija za upis
if ($_REQUEST['akcija'] == "unos_kriterij") {

	if ($_REQUEST['spremi']) {
		$rdonja = intval($_REQUEST['donja_granica']);
		$rgornja = intval($_REQUEST['gornja_granica']);
		$rkandidatisd = intval($_REQUEST['kandidati_sd']);
		$rkandidatisp = intval($_REQUEST['kandidati_sp']);
		$rkandidatikp = intval($_REQUEST['kandidati_kp']);
		$rprijemnimax = floatval($_REQUEST['prijemni_max']);
		$rstudij = intval($_REQUEST['_lv_column_studij']);

		$qInsert = myquery("UPDATE upis_kriterij SET donja_granica=$rdonja, gornja_granica=$rgornja, kandidati_strani=$rkandidatisd, kandidati_sami_placaju=$rkandidatisp, kandidati_kanton_placa=$rkandidatikp, prijemni_max=$rprijemnimax WHERE studij=$rstudij");

		$_REQUEST['prikazi'] = true; // prikazi upravo unesene podatke
	}

	if ($_REQUEST['prikazi']) {
		$rstudij = intval($_REQUEST['_lv_column_studij']);
		$q120 = myquery("select donja_granica, gornja_granica, kandidati_strani, kandidati_sami_placaju, kandidati_kanton_placa, prijemni_max from upis_kriterij where studij=$rstudij");
		if (mysql_num_rows($q120)<1) {
			$pdonja=$pgornja=$pksd=$pksp=$pkkp=$ppmax=0;
		} else {
			$pdonja=mysql_result($q120,0,0);
			$pgornja=mysql_result($q120,0,1);
			$pksd=mysql_result($q120,0,2);
			$pksp=mysql_result($q120,0,3);
			$pkkp=mysql_result($q120,0,4);
			$ppmax=mysql_result($q120,0,5);
		}
	}

?>

<SCRIPT language="JavaScript">
function odzuti(nesto) {
	nesto.style.backgroundColor = '#FFFFFF';
}
</SCRIPT>

<h3>Unos kriterija za upis</h3>
<br/>

<form action="" method="POST">
<input type="hidden" name="sta" value="studentska/prijemni">
<input type="hidden" name="akcija" value="unos_kriterij">
<table align="left" border="0" width="70%" bgcolor="">
	<tr>
		<td colspan="2" align="left">Odsjek:</td>
	</tr>
	<tr>
		<td><?php $_lv_['where:moguc_upis']="1"; echo db_dropdown("studij",$rstudij,"-- Izaberite odsjek --")?></td>
		<td><input type="submit" name="prikazi" value=" Prikaži "></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td width="70%" align="left">Maksimalni broj bodova na prijemnom ispitu:</td>
		<td><input type="text" size="12" name="prijemni_max" style="background-color:#FFFF00" oninput="odzuti(this)" autocomplete="off" value="<?=$ppmax?>"></td>
	</tr>
	<tr>
		<td width="70%" align="left">Hard limit:</td>
		<td><input type="text" size="12" name="donja_granica" style="background-color:#FFFF00" oninput="odzuti(this)" autocomplete="off" value="<?=$pdonja?>"></td>
	</tr>
	<tr>
		<td width="70%" align="left">Soft limit:</td>
		<td><input type="text" size="12" name="gornja_granica" style="background-color:#FFFF00" oninput="odzuti(this)" autocomplete="off" value="<?=$pgornja?>"></td>
	</tr>
	<tr>
		<td width="70%" align="left">Broj kandidata (strani državljani):</td>
		<td><input type="text" size="12" name="kandidati_sd" style="background-color:#FFFF00" oninput="odzuti(this)" autocomplete="off" value="<?=$pksd?>"></td>
	</tr>
	<tr>
		<td width="70%" align="left">Broj kandidata (sami plaćaju školovanje):</td>
		<td><input type="text" size="12" name="kandidati_sp" style="background-color:#FFFF00" oninput="odzuti(this)" autocomplete="off" value="<?=$pksp?>"></td>
	</tr>
	<tr>
		<td width="70%" align="left">Broj kandidata (kanton plaća školovanje):</td>
		<td><input type="text" size="12" name="kandidati_kp" style="background-color:#FFFF00" oninput="odzuti(this)" autocomplete="off" value="<?=$pkkp?>"></td>
	</tr>
	<tr>
		<td>&nbsp;<td>
	</tr>
	<tr>
		<td><input type="submit" name="spremi" value="Spremi"></td>
	</tr>
	
	</table>
	</form>
	
<?

}


?>
</td></tr></table></center>
<?


} // function studentska_prijemni



// Funkcija za testiranje ispravnosti JMBG

function testjmbg($jmbg) {
	if (strlen($jmbg)!=13) return "JMBG nema tačno 13 cifara";
	for ($i=0; $i<13; $i++) {
		$slovo = substr($jmbg,$i,1);
		if ($slovo<'0' || $slovo>'9') return "Neki od znakova nisu cifre";
		$cifre[$i] = $slovo-'0';
	}
	// Datum
	if (!checkdate($cifre[2]*10+$cifre[3], $cifre[0]*10+$cifre[1], $cifre[4]*10+$cifre[5]))
		return "Datum rođenja je kalendarski nemoguć";
	// Checksum
	$k = 11 - (( 7*($cifre[0]+$cifre[6]) + 6*($cifre[1]+$cifre[7]) + 5*($cifre[2]+$cifre[8]) + 4*($cifre[3]+$cifre[9]) + 3*($cifre[4]+$cifre[10]) + 2*($cifre[5]+$cifre[11]) ) % 11);
	if ($k==11) $k=0;
	if ($k!=$cifre[12]) return "Checksum ne valja ($cifre[12] a trebao bi biti $k)";
	return "";
}

?>
