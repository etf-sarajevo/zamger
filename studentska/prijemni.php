<?

// STUDENTSKA/PRIJEMNI - modul za administraciju prijemnog ispita

// v3.9.1.0 (2008/06/05) + Import koda by eldin.starcevic@hotmail.com
// v3.9.1.1 (2008/06/09) + Dodan post-guard, ispravljen bug sa ispisom datuma u pregledu, dodana default vrijednost za opći uspjeh


function studentska_prijemni() {

?>
<h1>Prijemni ispit</h1>
<br />
<?




// Default akcija je unos novog studenta
if ($_REQUEST['akcija']=="") $_REQUEST['akcija']="unos";



// Spisak kandidata za prijemni
// Parametar IZ definiše da li su strani, domaci ili svi

if ($_REQUEST['akcija']=="kandidati") {

	if ($_REQUEST['iz']=='bih') {
		$uslov="WHERE kanton!=13"; $naslov="(BiH)";
	}
	else if ($_REQUEST['iz']=='strani') {
		$uslov="WHERE kanton=13"; $naslov="(Strani državljani)";
	}
	else {
		$uslov=""; $naslov="";
	}

	?>
	<h3>Spisak kandidata za kvalifikacioni ispit <?=$naslov?></h3>
	<br /><?

	$sqlSelect="SELECT id, ime, prezime, kanton, opci_uspjeh, kljucni_predmeti, dodatni_bodovi FROM prijemni $uslov ORDER BY prezime";
	
	$q = myquery($sqlSelect);
	
	?>
	<table width="" align="center" border="1" cellpadding="1" cellspacing="1" bordercolor="#000000">
	<tr>
	<td width="10"><b>R.br.</b></td>
	<td><b>Prezime i ime</b></td>
	<td width="100"><b>Opći uspjeh</b></td>
	<td width="110"><b>Ključni predmeti</b></td>
	<td width="105"><b>Dodatni bodovi</b></td>
	<td width="105"><b>Ukupno bodova</b></td>
	</tr>
	<?php
	$brojac = 1;
	while ($kandidat=mysql_fetch_array($q))
	{
		$array = array ($kandidat["prezime"], $kandidat["ime"]);
		$prezimeIme = join (" ", $array);
		?>
		<tr>
		<td align="center"><?php echo "$brojac";?></td>
		<td><?php echo $prezimeIme;?></td>
		<td align="center"><?php echo $kandidat["opci_uspjeh"];?></td>
		<td align="center"><?php echo $kandidat["kljucni_predmeti"];?></td>
		<td align="center"><?php echo $kandidat["dodatni_bodovi"];?></td>
		<td align="center"><?php echo $kandidat["opci_uspjeh"]+$kandidat["kljucni_predmeti"]+$kandidat["dodatni_bodovi"];?></font></td>
		</tr>
		<?php
		$brojac++;
	}
	?>
	</table>
	<?
}

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


// brisanje kandidata

if ($_REQUEST["akcija"]=="obrisi")
{
	if ($_GET["redni_broj"]) {
		$sqlDelete="DELETE FROM prijemni WHERE id=" . $_GET["redni_broj"];
		myquery($sqlDelete);
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
	$opi=$_REQUEST['odsjek_prvi_izbor'];
	$odi=$_REQUEST['odsjek_drugi_izbor'];
	$oti=$_REQUEST['odsjek_treci_izbor'];
	$oci=$_REQUEST['odsjek_cetvrti_izbor'];
	$ropci=floatval($_REQUEST['opci_uspjeh']);
	$rkljucni=floatval($_REQUEST['kljucni_predmeti']);
	$rdodatni=floatval($_REQUEST['dodatni_bodovi']);
	$rprijemni=floatval($_REQUEST['prijemni']);

	if (preg_match("/(\d+).*?(\d+).*?(\d+)/",$_REQUEST['datum_rodjenja'],$matches)) {
		$dan=$matches[1]; $mjesec=$matches[2]; $godina=$matches[3];
		if ($godina<100)
			if ($godina<50) $godina+=2000; else $godina+=1000;
		if ($godina<1000)
			if ($godina<900) $godina+=2000; else $godina+=1000;
	}

	// Podaci su ok, ubaci u bazu
	if ($_REQUEST['ok']==1) {
		$dioupita = "set ime='$rime', prezime='$rprezime', datum_rodjenja='$godina-$mjesec-$dan', mjesto_rodjenja='$rmjestorod', drzavljanstvo='$rdrzavljanstvo', zavrsena_skola='$rzavrskola', jmbg='$rjmbg', adresa='$radresa', telefon='$rtelefon', kanton='$rkanton', redovni=$rredovni, odsjek_prvi='$opi', odsjek_drugi='$odi', odsjek_treci='$oti', odsjek_cetvrti='$oci', opci_uspjeh=$ropci, kljucni_predmeti=$rkljucni, dodatni_bodovi=$rdodatni, prijemni_ispit=0";
		$editid = intval($_REQUEST['edit']);
		if ($editid>0)
			$q = myquery("update prijemni $dioupita where id=$editid");
		else
			$q = myquery("insert into prijemni $dioupita");

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
			$dan=$matches[1]; $mjesec=$matches[2]; $godina=$matches[3];
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

		// Prikaz forme za provjeru
		$q = myquery("select naziv from kanton where id=$rkanton");
		$naziv_kantona = mysql_result($q,0,0);
?>
<h3>Provjera podataka</h3>
<br />

<table width="70%" cellspacing="3" style="border:1px;border-style:solid;border-color:black">
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
		<td align="left">Tip studija:</td><td align="left"><b><? if ($rredovni==1) print "redovni"; else print "";?></b></td>
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
	<tr bgcolor="#DDDDDD">
		<td align="left">Bodovi (prijemni ispit):</td><td align="left"><b><?=$rprijemni?></b></td>
	</tr>
</table>
<br />

<?=genform("POST")?><input type="hidden" name="_lv_column_kanton" value="<?=$rkanton?>"><input type="hidden" name="ok" value="1"><input type="submit" value=" Potvrda "> <input type="submit" name="nazad" value=" Nazad "></form><?
	}
}




// Spisak kandidata

if ($_REQUEST['akcija'] == "pregled") {
	
	?>
	<h3>Pregled kandidata</h3>
	<br />
	
	<hr color="black" width="2500">
	<a href="index.php?sta=studentska/prijemni&akcija=unos">Dodaj kandidata&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a>
	<a href="index.php?sta=studentska/prijemni&akcija=prijemni">Unos bodova sa prijemnog ispita&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</font></a>
	<a href="index.php?sta=studentska/prijemni&akcija=kandidati&iz=bih">Kandidati BiH&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</font></a>
	<a href="index.php?sta=studentska/prijemni&akcija=kandidati&iz=strani">Kandidati (strani državljani)</font></a>
	<hr color="black" width="2500">
	
	<?
	
	$imena_kantona=array();
	$q = myquery("select id,naziv from kanton");
	while ($r = mysql_fetch_row($q)) {
		$imena_kantona[$r[0]]=$r[1];
	}
	
	
	$sqlSelect="SELECT id, ime, prezime, UNIX_TIMESTAMP(datum_rodjenja), mjesto_rodjenja,
				drzavljanstvo, zavrsena_skola, jmbg, adresa, telefon, kanton, opci_uspjeh,
				kljucni_predmeti, dodatni_bodovi, redovni, prijemni_ispit, odsjek_prvi, odsjek_drugi, odsjek_treci, odsjek_cetvrti FROM prijemni ORDER BY prezime";
				
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
		<td align="center"><?php echo "$brojac";?></td>
		<td><?php echo $kandidat["prezime"];?></td>
		<td><?php echo $kandidat["ime"];?></td>
		<td><?php echo date("d. m. Y",$kandidat["UNIX_TIMESTAMP(datum_rodjenja)"]);?></td>
		<td><?php echo $kandidat["mjesto_rodjenja"];?></td>
		<td><?php echo $kandidat["drzavljanstvo"];?></td>
		<td><?php echo $kandidat["zavrsena_skola"];?></td>
		<td><?php echo $kandidat["jmbg"];?></td>
		<td><?php echo $kandidat["adresa"];?></td>
		<td><?php echo $kandidat["telefon"];?></td>
		<td><?php echo $imena_kantona[$kandidat["kanton"]];?></td>
		<td align="center"><?php echo $kandidat["opci_uspjeh"];?></td>
		<td align="center"><?php echo $kandidat["kljucni_predmeti"];?></td>
		<td align="center"><?php echo $kandidat["dodatni_bodovi"];?></td>
		<td align="center"><?php echo $kandidat["prijemni_ispit"];?></td>
		<td align="center"><?php if ($kandidat["redovni"]) echo "redovni"; else echo "paralelni";?></td>
		<td align="center"><?php echo $kandidat["odsjek_prvi"];?></td>
		<td align="center"><?php echo $kandidat["odsjek_drugi"];?></td>
		<td align="center"><?php echo $kandidat["odsjek_treci"];?></td>
		<td align="center"><?php echo $kandidat["odsjek_cetvrti"];?></td>
		<td align="center"><?php echo "$brojac";?></td>
		
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
	<a href="index.php?sta=studentska/prijemni&akcija=unos">Dodaj kandidata&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a>
	<a href="index.php?sta=studentska/prijemni&akcija=prijemni">Unos bodova sa prijemnog ispita&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</font></a>
	<a href="index.php?sta=studentska/prijemni&akcija=kandidati&iz=bih">Kandidati BiH&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</font></a>
	<a href="index.php?sta=studentska/prijemni&akcija=kandidati&iz=strani">Kandidati (strani državljani)</font></a>
	<hr color="black" width="2500">
	<?

}



// Unos novog kandidata u tabelu za prijemni

if ($_REQUEST['akcija']=="unos") {


// Editovanje starog kandidata
$editid=intval($_REQUEST['edit']);
$eredovni=1;
$ekanton=$eopci=$ekljucni=$edodatni=$eprijemni=0;
if ($editid>0) {
	$q = myquery("select ime, prezime, UNIX_TIMESTAMP(datum_rodjenja), mjesto_rodjenja, drzavljanstvo, zavrsena_skola, jmbg, adresa, telefon, kanton, redovni, odsjek_prvi, odsjek_drugi, odsjek_treci, odsjek_cetvrti, opci_uspjeh, kljucni_predmeti, dodatni_bodovi, prijemni_ispit from prijemni where id=$editid");
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
}

$odsjeci = array("AE", "EE", "RI", "TK");

// Tabela za unos podataka - design
?>
<h3>Unos studenata</h3>
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

</SCRIPT>

<form action="index.php" method="POST" id="glavnaforma">
<input type="hidden" name="sta" value="studentska/prijemni">
<input type="hidden" name="akcija" value="unospotvrda">
<? if ($editid>0) { ?><input type="hidden" name="edit" value="<?=$editid?>"><? } ?>

<table border="0" cellpadding="3" cellspacing="0">
	<tr>
		<td width="130" align="left">Ime kandidata:</td>
		<td><input maxlength="50" size="17" name="ime" id="ime" type="text" <? if ($eime) { ?> value="<?=$eime?>"<? } else { ?> style="background-color:#FFFF00" oninput="odzuti(this)" <? } ?> autocomplete="off"><font color="#FF0000">*</font></td>
	</tr>
	<tr>
		<td width="125" align="left">Prezime kandidata:</td>
		<td><input maxlength="50" size="17" name="prezime" id="prezime" type="text" <? if ($eprezime) { ?> value="<?=$eprezime?>"<? } else { ?> style="background-color:#FFFF00" oninput="odzuti(this)" <? } ?> autocomplete="off"><font color="#FF0000">*</font></td>
	</tr>
	<tr>
		<td width="125" align="left">Datum rođenja:</td>
		<td><input maxlength="20" size="17" name="datum_rodjenja" id="datum_rodjenja" type="text" <? if ($edatum) { ?> value="<?=$edatum?>"<? } else { ?> style="background-color:#FFFF00" oninput="odzuti(this)" <? } ?> autocomplete="off"><font color="#FF0000">*</font></td>
	</tr>
	<tr>
		<td width="125" align="left">Mjesto rođenja:</td>
		<td><input maxlength="50" size="17" name="mjesto_rodjenja" id="mjesto_rodjenja" type="text" <? if ($emjesto) { ?> value="<?=$emjesto?>"<? } else { ?> style="background-color:#FFFF00" oninput="odzuti(this)" <? } ?> ><font color="#FF0000">*</font></td>
	</tr>
	<tr>
		<td width="125" align="left">Državljanstvo:</td>
		<td><input maxlength="40" size="17" name="drzavljanstvo" id="drzavljanstvo" type="text"  <? if ($edrz) { ?> value="<?=$edrz?>"<? } else { ?> value="BiH" <? } ?> ><font color="#FF0000">*</font></td>
	</tr>
	<tr>
		<td width="125" align="left">Završena škola:</td>
		<td><input maxlength="50" size="17" name="zavrsena_skola" id="zavrsena_skola" type="text" <? if ($eskola) { ?> value="<?=$eskola?>"<? } else { ?> style="background-color:#FFFF00" oninput="odzuti(this)" <? } ?>></td>
	</tr>
	<tr>
		<td width="125" align="left">JMBG:</td>
		<td><input maxlength="13" size="17" name="jmbg" id="jmbg" type="text" <? if ($ejmbg) { ?> value="<?=$ejmbg?>"<? } else { ?> style="background-color:#FFFF00" oninput="odzuti(this)" <? } ?> autocomplete="off"></td>
	</tr>
	<tr>
		<td width="125" align="left">Adresa:</td>
		<td><input maxlength="50" size="17" name="adresa" id="adresa" type="text" <? if ($eadresa) { ?> value="<?=$eadresa?>"<? } else { ?> style="background-color:#FFFF00" oninput="odzuti(this)" <? } ?> autocomplete="off"></td>
	</tr>
	<tr>
		<td width="125" align="left">Telefon roditelja:</td>
		<td><input maxlength="30" size="17" name="telefon_roditelja" id="telefon_roditelja" type="text"  <? if ($etelefon) { ?> value="<?=$etelefon?>"<? } else { ?> style="background-color:#FFFF00" oninput="odzuti(this)" <? } ?> autocomplete="off"></td>
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
<? if ($eprijemni>0) { ?>
	<tr>
		<td width="125" align="left">Prijemni</td>
		<td><input maxlength="50" size="17" name="prijemni" id="prijemni" type="text" value="<?=$eprijemni?>" autocomplete="off"></td>
	</tr>
<? } else { ?>
	<input type="hidden" name="prijemni" value="0">
<? } ?>
</table>
<br />

<b>Unos ocjena.</b><br/>

<!-- Unos ocjena tokom srednje skole -->
<script language="JavaScript">
var sumaocjena=0;
var brojocjena=0;
var sumakljucnih=0;
var brojkljucnih=0;
function obrisi_ocjenu(broc,ocjena,kljucval) {
	sumaocjena = parseInt(sumaocjena)-parseInt(ocjena);
	brojocjena--;
	if (kljucval>0) {
		sumakljucnih=parseInt(sumakljucnih)-parseInt(ocjena);
		brojkljucnih--;
	}

	if (brojocjena>0)
		document.getElementById('opci_uspjeh').value = (sumaocjena/brojocjena)*8;
	else
		document.getElementById('opci_uspjeh').value = '0';

	if (brojkljucnih>0) 
		document.getElementById('kljucni_predmeti').value = sumakljucnih/brojkljucnih*4;
	else
		document.getElementById('kljucni_predmeti').value = '0';


	// Pokusacemo obrisati ovo kino iz html-a
	var stari = document.getElementById('dalje').innerHTML;
	var pos1 = stari.indexOf('<!-- brojocjena ' + brojocjena + '-->');
	var pos2 = stari.indexOf('<!-- kraj brojocjena -->',pos1+1) + 24;
	document.getElementById('dalje').innerHTML = stari.substr(0,pos1) + stari.substr(pos2);
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
	document.getElementById('opci_uspjeh').value = (sumaocjena/brojocjena)*8;
	if (brojkljucnih>0) document.getElementById('kljucni_predmeti').value = sumakljucnih/brojkljucnih*4;
//alert('Suma '+sumakljucnih+' broj '+brojkljucnih);
//alert(kljucni);
//
	// Formiranje HTMLa
	var html = '<!-- brojocjena ' + brojocjena + '--><tr><td>' + document.getElementById('razred').value + '</td><td><b>' + ocjena + '</b></td><td><b>' + kljuctext + ' (<a onclick="obrisi_ocjenu(' + brojocjena + ',' + ocjena + ',' + kljucval + ')">obriši</a>)</b></td></tr><!-- kraj brojocjena --><!-- komentar -->';
	var stari = document.getElementById('dalje').innerHTML;
	document.getElementById('dalje').innerHTML = stari.replace('<!-- komentar -->',html);

	// Reset forme
	document.getElementById('kljucni').checked=false;
	document.getElementById('ocjena').value='';
	document.getElementById('razred').value=razred;

	return true;
}

/*function provjeri(varijablu) {
	if (document.getElementById(varijablu).value=='') {
		alert("Niste unijeli "+varijablu);
		return false;
	}
	return true;
}*/
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

	document.getElementById('glavnaforma').submit();
	return true;
}

</script>

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
		<td><input type="text" id="ocjena" name="ocjena" size="5" autocomplete="off"></td>
		<td align="center"><input type="radio" id="kljucni" name="kljucni" value="1"> Da <input type="radio" id="kljucni" name="kljucni" value="0"> Ne</td>
		<td><input type="button" value=" Ok " onclick="addnew();"></td>
	</tr>
</table>
</div>

<br /><br />
<!-- Tablica bodova -->

<fieldset style="width:200px" style="background-color:#0099FF">
<legend>Bodovi</legend>
<table align="center" width="600" border="0">
	<tr>
		<td align="left">Opći uspjeh:</td>
		<td><input maxlength="10" size="3" name="opci_uspjeh" id="opci_uspjeh" type="text" value="<?=$eopci?>"><font color="#FF0000">*</font></td>
		<td align="left">Ključni predmeti:</td>
		<td><input maxlength="10" size="3" name="kljucni_predmeti" id="kljucni_predmeti" type="text" value="<?=$ekljucni?>"><font color="#FF0000">*</font></td>
		<td align="left">Dodatni bodovi:</td>
		<td><input maxlength="10" size="3" name="dodatni_bodovi" type="text" value="<?=$edodatni?>"></td>
	</tr>
</table>
</fieldset>
</form>


<p><font color="#FF0000">*</font> - Sva polja označena zvjezdicom su obavezna.<br/>
<input type="button" value="Snimi" onclick="provjeri_sve()"></p>

<p>&nbsp;</p>


<!--Tabela za linkove koji otvaraju ostale stranice vezane za aplikciju-->
<table border="0" bgcolor="">
	<tr>
		<td align="left"><a href="?sta=studentska/prijemni&akcija=pregled">Pregled kandidata</a></td>
	</tr>
	<tr>&nbsp;</tr>
	<tr>
		<td><a href="?sta=studentska/prijemni&akcija=prijemni">Unos bodova sa prijemnog ispita</a></td>
	</tr>
	<tr>&nbsp;</tr>
	<tr>
		<td><a href="?sta=studentska/prijemni&akcija=kandidati&iz=bih">Kandidati BiH</a></td>
	</tr>
	<tr>&nbsp;</tr>
	<tr>
		<td><a href="?sta=studentska/prijemni&akcija=kandidati&iz=strani">Kandidati (strani državljani)</a></td>
	</tr>
	<tr>&nbsp;</tr>
	<tr>
		<td><a href="?sta=studentska/prijemni&akcija=unos_kriterij">Unos kriterija za upis</a></td>
	</tr>
</table>

<br />

<?

} // if ($akcija_unos==1)

//Unos kriterija za upis

if ($_REQUEST['akcija'] == "kriterij_potvrda"){

	$rdonja = intval($_REQUEST['donja_granica']);
	$rgornja = intval($_REQUEST['gornja_granica']);
	$rkandidatisd = intval($_REQUEST['kandidati_sd']);
	$rkandidatisp = intval($_REQUEST['kandidati_sp']);
	$rkandidatikp = intval($_REQUEST['kandidati_kp']);
	$rprijemnimax = floatval($_REQUEST['prijemni_max']);
	$rodsjek = intval($_REQUEST['_lv_column_institucija']);
	$rakademska_godina = intval($_REQUEST['_lv_column_akademska_godina']);
	

	if ($_REQUEST['potvrda']){
	$qInsert = myquery("INSERT INTO upis_kriterij	(donja_granica, gornja_granica, kandidati_strani, kandidati_sami_placaju, kandidati_kanton_placa, odsjek, akademska_godina,  prijemni_max)
										VALUES  	($rdonja, $rgornja, $rkandidatisd, $rkandidatisp, $rkandidatikp, $rodsjek, $rakademska_godina, $rprijemnimax)");
	$_REQUEST['akcija'] = "unos_kriterij";
	}
	else if ($_REQUEST['nazad']){
	$_REQUEST['akcija']="unos_kriterij";
	}
	
	else{
	
		$q_odsjek = myquery("select naziv from institucija where id=$rodsjek");
		$naziv_odsjeka = mysql_result($q_odsjek,0,0);
		$q_akademska_godina = myquery("select naziv from akademska_godina where id=$rakademska_godina");
		$akademska_godina = mysql_result($q_akademska_godina,0,0);
		
?>

<h3>Provjera podataka</h3>
<br />

<table width="70%" cellspacing="3" style="border:1px;border-style:solid;border-color:black">
	<tr bgcolor="#DDDDDD">
		<td align="left">Akademska godina:</td><td align="left"><b><?php print $akademska_godina?></b></td>
	</tr>
	<tr>
		<td align="left">Odsjek:</td><td align="left"><b><?php print $naziv_odsjeka?></b></td>
	</tr>
	<tr bgcolor="#DDDDDD">
		<td align="left">Maksimalni broj bodova na prijemnom ispitu:</td><td align="left"><b><?php print $rprijemnimax?></b></td>
	</tr>
	<tr>
		<td align="left" width="65%">Donja granica:</td><td align="left"><b><?php echo $rdonja?></b></td>
	</tr>
	<tr bgcolor="#DDDDDD">
		<td align="left">Gornja granica:</td><td align="left"><b><?php echo $rgornja?></b></td>
	</tr>
	<tr>
		<td align="left" width="65%">Broj kandidata(strani državljani):</td><td align="left"><b><?php echo $rkandidatisd?></b></td>
	</tr>
	<tr bgcolor="#DDDDDD">
		<td align="left">Broj kandidata(sami plaćaju troškove školovanja):</td><td align="left"><b><?php print $rkandidatisp?></b></td>
	</tr>
	<tr>
		<td align="left">Broj kandidata(kanton plaća troškove školovanja):</td><td align="left"><b><?php print $rkandidatikp?></b></td>
	</tr>
</table>
<br />
<?php echo genform("POST")?><input type="hidden" name="_lv_column_institucija" value="<?php echo $rodsjek?>"><input type="hidden" name="_lv_column_akademska_godina" value="<?php echo $rakademska_godina?>"><input type="submit" name="potvrda" value=" Potvrda "> <input type="submit" name="nazad" value=" Nazad "></form><?php
}
}
?>

<?php
	
if ($_REQUEST['akcija'] == "unos_kriterij"){

?>
<h3>Unos kriterija za upis</h3>
<br/>

<form action="" method="POST">
<input type="hidden" name="sta" value="studentska/prijemni">
<input type="hidden" name="akcija" value="kriterij_potvrda">
<table align="left" border="0" width="70%" bgcolor="">
	<tr>
		<td width="70%" align="left">Odsjek:</td>
		<td width="" align="left">Akademska godina:</td>
	</tr>
	<tr>
		<td><?php echo db_dropdown("institucija")?></td>
		<td><?php echo db_dropdown("akademska_godina")?></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td width="70%" align="left">Maksimalni broj bodova na prijemnom ispitu:</td>
		<td><input type="text" size="12" name="prijemni_max" style="background-color:#FFFF00" oninput="odzuti(this)" autocomplete="off"></td>
	</tr>
	<tr>
		<td width="70%" align="left">Donja granica(postotak):</td>
		<td><input type="text" size="12" name="donja_granica" style="background-color:#FFFF00" oninput="odzuti(this)" autocomplete="off"></td>
	</tr>
	<tr>
		<td width="70%" align="left">Gornja granica(postotak):</td>
		<td><input type="text" size="12" name="gornja_granica" style="background-color:#FFFF00" oninput="odzuti(this)" autocomplete="off"></td>
	</tr>
	<tr>
		<td width="70%" align="left">Broj kandidata(strani državljani):</td>
		<td><input type="text" size="12" name="kandidati_sd" style="background-color:#FFFF00" oninput="odzuti(this)" autocomplete="off"></td>
	</tr>
	<tr>
		<td width="70%" align="left">Broj kandidata(sami plaćaju školovanje):</td>
		<td><input type="text" size="12" name="kandidati_sp" style="background-color:#FFFF00" oninput="odzuti(this)" autocomplete="off"></td>
	</tr>
	<tr>
		<td width="70%" align="left">Broj kandidata(kanton plaća školovanje):</td>
		<td><input type="text" size="12" name="kandidati_kp" style="background-color:#FFFF00" oninput="odzuti(this)" autocomplete="off"></td>
	</tr>
	<tr>
		<td>&nbsp;<td>
	</tr>
	<tr>
		<td><input type="submit" value="Spremi"></td>
	</tr>
	
	</table>
	</form>
	
<?php
}

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
