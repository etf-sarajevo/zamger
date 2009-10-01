<?

// STUDENTSKA/PRIJEMNI - modul za administraciju prijemnog ispita

// v3.9.1.0 (2008/06/05) + Import koda by eldin.starcevic@hotmail.com
// v3.9.1.1 (2008/06/09) + Dodan post-guard, ispravljen bug sa ispisom datuma u pregledu, dodana default vrijednost za opći uspjeh
// v3.9.1.2 (2008/07/11) + Finalna verzija korištena za prijemni na ETFu
// v3.9.1.3 (2008/08/28) + Uhakovan drugi termin za prijemni (popraviti), centriran i reorganizovan prikaz
// v3.9.1.4 (2008/10/03) + Akcije unospotvrda i unoskriterij (subakcija spremi) prebacene na genform() radi sigurnosnih aspekata istog
// v3.9.1.5 (2009/02/12) + Cleanup
// v4.0.0.0 (2009/02/19) + Release
// v4.0.9.1 (2009/06/19) + Restruktuiranje i ciscenje baze: uvedeni sifrarnici mjesto i srednja_skola, za unos se koristi combo box; tabela prijemni_termin omogucuje definisanje termina prijemnog ispita, sto omogucuje i prijemni ispit za drugi ciklus; pa su dodate i odgovarajuce akcije za kreiranje i izbor termina; licni podaci se sada unose direktno u tabelu osoba, dodaje se privilegija "prijemni" u tabelu privilegija; razdvojene tabele: uspjeh_u_srednjoj (koja se vezuje na osoba i srednja_skola) i prijemni_prijava (koja se vezuje na osoba i prijemni_termin); polja za studij su FK umjesto tekstualnog polja; dodano polje prijemni_termin u upis_kriterij; tabela prijemniocjene preimenovana u srednja_ocjene; ostalo: dodan logging; jmbg proglasen obaveznim; vezujem ocjene iz srednje skole za redni broj, posto se do sada redoslijed ocjena oslanjao na ponasanje baze; nova combobox kontrola
// v4.0.9.2 (2009/07/15) + Dodajem kod za upis na drugi ciklus
// v4.0.9.3 (2009/09/02) + U akciji za unos kriterija za upis: popravljen upit kada ne postoji nista u bazi, prikaz odabranog studija, varijabla Spremi nije bila unsetovana


function studentska_prijemni() {

global $_lv_;


// Default akcija je unos novog studenta
if ($_REQUEST['akcija']=="") $_REQUEST['akcija']="unos";


?>
<center>
<table border="0" width="100%">


<?


// ODREDJIVANJE TERMINA I NASLOVA

$termin=intval($_REQUEST['termin']);
if ($termin==0) {
	// Daj najskoriji ispit
	$q10 = myquery("select pt.id, ag.naziv, UNIX_TIMESTAMP(pt.datum), pt.ciklus_studija from prijemni_termin as pt, akademska_godina as ag where pt.akademska_godina=ag.id order by pt.datum desc limit 1");

	if (mysql_num_rows($q10)<1 && $_REQUEST['akcija'] != "novi_ispit") {
		$_REQUEST['akcija'] = "novi_ispit";
		$termin=0;
	} else {
		$termin=mysql_result($q10,0,0);
	}
} else {
	$q10 = myquery("select pt.id, ag.naziv, UNIX_TIMESTAMP(pt.datum), pt.ciklus_studija from prijemni_termin as pt, akademska_godina as ag where pt.id=$termin and pt.akademska_godina=ag.id");
}

$datum = date("d. m. Y.",mysql_result($q10,0,2));
$ciklus_studija = mysql_result($q10,0,3);

$naziv = " za ".mysql_result($q10,0,1)." akademsku godinu (".mysql_result($q10,0,3)." ciklus studija), $datum";





// MENI S LIJEVE STRANE

// (ne prikazuje se ako je akcija "pregled")

if ($_REQUEST['akcija'] != "pregled") {

?>
<tr><td valign="top" width="220">

<!-- Termini prijemnog ispita -->
* <a href="?sta=studentska/prijemni&akcija=novi_ispit">Novi prijemni ispit</a><br />
* <a href="?sta=studentska/prijemni&akcija=arhiva_ispita">Arhiva prijemnih ispita</a><br /><br /><br />


<!-- Tabela za linkove koji otvaraju ostale stranice vezane za modul -->
<?=$datum?>:<br />
<table bgcolor="" style="border:1px;border-style:solid;border-color:black">
	<tr>
		<td align="left"><a href="?sta=studentska/prijemni&akcija=unos&termin=<?=$termin?>">Unos kandidata</a></td>
	</tr>
	<tr>&nbsp;</tr>
	<tr>
		<td align="left"><a href="?sta=studentska/prijemni&akcija=pregled&termin=<?=$termin?>">Tabelarni pregled kandidata</a></td>
	</tr>
	<tr>&nbsp;</tr>
	<tr>
		<td><a href="?sta=studentska/prijemni&akcija=prijemni&termin=<?=$termin?>">Unos bodova sa prijemnog ispita</a></td>
	</tr>
	<tr>&nbsp;</tr>
	<tr>
		<td><a href="?sta=studentska/prijemni&akcija=upis_kriterij&termin=<?=$termin?>">Kriteriji za upis</a></td>
	</tr>
	</tr>
	<tr>&nbsp;</tr>
	<tr>
		<td><a href="?sta=studentska/prijemni&akcija=spisak&termin=<?=$termin?>">Spisak kandidata</a></td>
        <tr>&nbsp;</tr> 	 
	<tr> 	 
		<td>Rang liste kandidata:<br /><?

	// Spisak studija
	$q20 = myquery("select s.id, s.naziv from studij as s, tipstudija as ts where s.moguc_upis=1 and s.tipstudija=ts.id and ts.ciklus=$ciklus_studija order by s.naziv");
	while ($r20 = mysql_fetch_row($q20)) {
		?>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="?sta=izvjestaj/prijemni&_lv_column_studij=<?=$r20[0]?>&termin=<?=$termin?>"><?=$r20[1]?></a><br />
		<?
	}
	?>
		</td> 	 
	</tr> 	 
</table>




</td><td width="10">&nbsp;</td>


<?

} // if ($_REQUEST['akcija'] != "pregled" )




// NASLOV

?>
<td valign="top">

<h1>Prijemni ispit</h1>
<?




// NOVI PRIJEMNI ISPIT

if ($_POST['akcija']=="novi_ispit_potvrda" && check_csrf_token()) {
	$ciklus = intval($_REQUEST['ciklus']);
	if ($ciklus!=1 && $ciklus !=2) {
		biguglyerror("Neispravan ciklus studija");
		zamgerlog("neispravan ciklus za novi termin prijemnog", 3); // 3 = greska
		return;
	}
	$ag = intval($_REQUEST['_lv_column_akademska_godina']);

	if (preg_match("/(\d+).*?(\d+).*?(\d+)/",$_REQUEST['datum'],$matches)) {
		$dan=$matches[1]; $mjesec=$matches[2]; $godina=$matches[3];
		if ($godina<100)
			if ($godina<50) $godina+=2000; else $godina+=1900;
		if ($godina<1000)
			if ($godina<900) $godina+=2000; else $godina+=1000;
	} else {
		biguglyerror("Neispravan datum");
		zamgerlog("neispravan datum za novi termin prijemnog", 3); // 3 = greska
		return;
	}

	$q20 = myquery("insert into prijemni_termin set akademska_godina=$ag, datum='$godina-$mjesec-$dan', ciklus_studija=$ciklus");

	zamgerlog("kreiran novi termin za prijemni ispit", 4); // 4 = audit

	?>
	<p>Novi termin kreiran. <a href="?sta=studentska/prijemni">Kliknite ovdje za nastavak</a></p>

</td></tr></table></center>
	<?
	
	return; // Necemo da se ispise naziv
}

if ($_REQUEST['akcija']=="novi_ispit") {

	unset($_REQUEST['akcija']);

	?><h2>Novi termin prijemnog ispita:</h2>

	<?=genform("POST")?>
	<input type="hidden" name="akcija" value="novi_ispit_potvrda">
	<table border="0"><tr><td>
	Ciklus studija:</td><td><select name="ciklus"><option value="1">Prvi</option><option value="2">Drugi</option></select>
	</td></tr><tr><td>
	Akademska godina:</td><td><?=db_dropdown("akademska_godina")?>
	</td></tr><tr><td>
	Datum održavanja ispita:</td><td><input type="text" name="datum" size="20">
	</td></tr><tr><td>&nbsp;</td><td>
	<input type="submit" value="  Kreiraj  ">
	</td></tr></table>
	</form>
	
	<p>Za povratak, kliknite na link "Unos kandidata" sa lijeve strane.</p>

</td></tr></table></center>
	<?
	
	return; // Necemo da se ispise naziv
}



// ARHIVA PRIJEMNIH ISPITA

if ($_REQUEST['akcija'] == "arhiva_ispita") {
	?>
	<p>Do sada održani prijemni ispiti (po datumu ispita):</p>
	<ul>
	<?

	$q30 = myquery("select pt.id, ag.naziv, UNIX_TIMESTAMP(pt.datum), pt.ciklus_studija from prijemni_termin as pt, akademska_godina as ag where pt.akademska_godina=ag.id order by pt.datum");
	while ($r30 = mysql_fetch_row($q30)) {
		$datum = date("d. m. Y.", $r30[2]);
		?>
		<li><a href="?sta=studentska/prijemni&termin=<?=$r30[0]?>">(<?=$datum?>) Akademska <?=$r30[1]?> godina, <?=$r30[3]?>. ciklus studija</a></li>
		<?
	}
	?>
	</ul>

</td></tr></table></center>
	<?
	
	return; // Necemo da se ispise naziv
}

// ISPIS NAZIVA ODABRANOG TERMINA ISPITA

?>
<p><?=$naziv?></p>
<?




// PREDLINK ZA IZVJEŠTAJ


if ($_REQUEST['akcija']=="spisak") {
	?>
	<form action="index.php" method="GET">
	<input type="hidden" name="sta" value="izvjestaj/prijemni">
	<input type="hidden" name="akcija" value="kandidati">
	<input type="hidden" name="termin" value="<?=$termin?>">
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



// UNOS KRITERIJA ZA UPIS

if ($_REQUEST['akcija'] == "upis_kriterij") {

	if ($_POST['spremi'] && check_csrf_token()) {
		$rdonja = intval($_REQUEST['donja_granica']);
		$rgornja = intval($_REQUEST['gornja_granica']);
		$rkandidatisd = intval($_REQUEST['kandidati_sd']);
		$rkandidatisp = intval($_REQUEST['kandidati_sp']);
		$rkandidatikp = intval($_REQUEST['kandidati_kp']);
		$rprijemnimax = floatval($_REQUEST['prijemni_max']);
		$rstudij = intval($_REQUEST['rstudij']);

		$qInsert = myquery("REPLACE upis_kriterij SET donja_granica=$rdonja, gornja_granica=$rgornja, kandidati_strani=$rkandidatisd, kandidati_sami_placaju=$rkandidatisp, kandidati_kanton_placa=$rkandidatikp, prijemni_max=$rprijemnimax, studij=$rstudij, prijemni_termin=$termin");

		$_REQUEST['prikazi'] = true; // prikazi upravo unesene podatke

		zamgerlog("promijenjeni kriteriji za prijemni ispit termin $termin, studij $rstudij", 4);
	}

	if ($_REQUEST['prikazi']) {
		$rstudij = intval($_REQUEST['rstudij']);
		$q120 = myquery("select donja_granica, gornja_granica, kandidati_strani, kandidati_sami_placaju, kandidati_kanton_placa, prijemni_max from upis_kriterij where studij=$rstudij and prijemni_termin=$termin");
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


	// Spisak dostupnih studija
	$q130 = myquery("select s.id, s.naziv from studij as s, tipstudija as ts where s.moguc_upis=1 and s.tipstudija=ts.id and ts.ciklus=$ciklus_studija");
	$spisak_studija="";
	while ($r130 = mysql_fetch_row($q130)) {
		$spisak_studija .= "<option value=\"$r130[0]\"";
		if ($r130[0]==$rstudij) $spisak_studija .= " selected";
		$spisak_studija .= ">$r130[1]</option>\n";
	}

	unset($_REQUEST['spremi']);

?>

<SCRIPT language="JavaScript">
function odzuti(nesto) {
	nesto.style.backgroundColor = '#FFFFFF';
}
</SCRIPT>

<h3>Unos kriterija za upis</h3>
<br/>

<?=genform("POST")?>
<table align="left" border="0" width="70%" bgcolor="">
	<tr>
		<td colspan="2" align="left">Odsjek:</td>
	</tr>
	<tr>
		<td><select name="rstudij"><?=$spisak_studija?></select></td>
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




// TABELARNI UNOS BODOVA SA PRIJEMNOG ISPITA

if ($_REQUEST['akcija']=="prijemni") {

	?>
	<h3>Unos bodova sa prijemnog ispita</h3>
	<br />
	<hr color="black" width="100%">
	<a href="index.php?sta=studentska/prijemni&akcija=prijemni&sort=prezime&termin=<?=$termin?>">Sortirano po prezimenu</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="index.php?sta=studentska/prijemni&akcija=prijemni&sort=unos&termin=<?=$termin?>">Sortirano po broju dosjea</a>
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
			ajah_start("index.php?c=N&sta=common/ajah&akcija=prijemni_unos&osoba="+id+"&vrijednost="+vrijednost+"&termin=<?=$termin?>","document.getElementById('prijemni'+"+id+").focus()");
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

	$upit = "SELECT o.id, o.ime, o.prezime, pp.rezultat, pp.izasao, us.opci_uspjeh+us.kljucni_predmeti+us.dodatni_bodovi, pp.broj_dosjea from osoba as o, prijemni_prijava as pp, uspjeh_u_srednjoj as us where o.id=pp.osoba and pp.prijemni_termin=$termin and us.osoba=o.id";

	if ($_REQUEST['sort'] == "prezime") $upit .= " ORDER BY o.prezime, o.ime";
	else $upit .= " ORDER BY pp.broj_dosjea";

	$q = myquery($upit);
	$id=0;
	while ($r = mysql_fetch_row($q)) {
		if ($id!=0)
			print "$r[0])\"></tr>\n";
		$id=$r[0];
		if ($r[4]==0) $bodova="/"; else $bodova=$r[3]; // izasao na prijemni?

		?>
		<SCRIPT language="JavaScript"> origval[<?=$id?>]=<?=$bodova?></SCRIPT>
		<tr><td><?=$r[6]?></td>
		<td><?=$r[2]?> <?=$r[1]?></td>
		<td align="center"><?=(round($r[5]*10)/10)?></td>
		<td align="center"><input type="text" id="prijemni<?=$id?>" size="2" value="<?=$bodova?>" style="border:1px black solid" onblur="izgubio_focus(this)" onfocus="dobio_focus(this)" onkeydown="enterhack(this,event,<?
	}
	if ($id != 0) { ?>0)"><? }
	?></tr>
	<?
	?>
	</table>
	<?
}




// BRISANJE KANDIDATA (poziva se sa vise mjesta)

if ($_REQUEST["akcija"]=="obrisi") {
	$osoba = intval($_GET['osoba']);
	if ($osoba>0) {
		myquery("DELETE FROM prijemni_prijava WHERE osoba=$osoba AND prijemni_termin=$termin LIMIT 1");

		// Necemo brisati osobu i ostale podatke
		zamgerlog("brisem osobu u$osoba sa prijemnog - termin $termin", 4);
		nicemessage("Kandidat ispisan sa prijemnog ispita");
	}
	
	$_REQUEST['akcija']="pregled";
}



// SPISAK KANDIDATA - TABELA

if ($_REQUEST['akcija'] == "pregled") {
	
	if ($ciklus_studija==1) $sirina="2500"; else $sirina="2000";

	?>
	<h3>Pregled kandidata</h3>
	<br />
	
	<hr color="black" width="<?=$sirina?>">
	<a href="index.php?sta=studentska/prijemni&akcija=pregled&sort=prezime&termin=<?=$termin?>">Sortirano po prezimenu</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="index.php?sta=studentska/prijemni&akcija=pregled&sort=bd&termin=<?=$termin?>">Sortirano po broju dosjea</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="index.php?sta=studentska/prijemni&akcija=unos&termin=<?=$termin?>">Dodaj kandidata</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="index.php?sta=studentska/prijemni&akcija=prijemni&termin=<?=$termin?>">Unos bodova sa prijemnog ispita</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="index.php?sta=studentska/prijemni&akcija=kandidati&iz=bih&termin=<?=$termin?>">Kandidati BiH</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="index.php?sta=studentska/prijemni&akcija=kandidati&iz=strani&termin=<?=$termin?>">Kandidati (strani državljani)</a>
	<hr color="black" width="<?=$sirina?>">
	
	<?
	
	$imena_studija=array();
	$q = myquery("select id,kratkinaziv from studij");
	while ($r = mysql_fetch_row($q)) {
		$imena_studija[$r[0]]=$r[1];
	}

	$imena_skola=array();
	$q = myquery("select id, naziv from srednja_skola");
	while ($r = mysql_fetch_row($q)) {
		$imena_skola[$r[0]]=$r[1];
	}

	$imena_mjesta=array();
	$q = myquery("select id, naziv from mjesto");
	while ($r = mysql_fetch_row($q)) {
		$imena_mjesta[$r[0]]=$r[1];
	}
	
	if ($ciklus_studija==1) {
		$sqlSelect="SELECT o.id, o.ime, o.prezime, UNIX_TIMESTAMP(o.datum_rodjenja), o.mjesto_rodjenja, o.drzavljanstvo, us.srednja_skola, o.jmbg, o.adresa, o.adresa_mjesto, o.telefon, k.naziv, us.opci_uspjeh, us.kljucni_predmeti, us.dodatni_bodovi, pp.broj_dosjea, pp.redovan, pp.rezultat, pp.izasao, pp.studij_prvi, pp.studij_drugi, pp.studij_treci, pp.studij_cetvrti, us.ucenik_generacije, pp.broj_dosjea
		FROM osoba as o, kanton as k, uspjeh_u_srednjoj as us, prijemni_prijava as pp
		WHERE pp.osoba=o.id and pp.prijemni_termin=$termin and us.osoba=o.id and o.kanton=k.id ";
	} else {
		$sqlSelect="SELECT o.id, o.ime, o.prezime, UNIX_TIMESTAMP(o.datum_rodjenja), o.mjesto_rodjenja, o.drzavljanstvo, 0, o.jmbg, o.adresa, o.adresa_mjesto, o.telefon, k.naziv, 0, 0, 0, pp.broj_dosjea, pp.redovan, pp.rezultat, pp.izasao, pp.studij_prvi, pp.studij_drugi, pp.studij_treci, pp.studij_cetvrti, 0, pp.broj_dosjea
		FROM osoba as o, kanton as k, prijemni_prijava as pp
		WHERE pp.osoba=o.id and pp.prijemni_termin=$termin and o.kanton=k.id ";
	} 

	if ($_REQUEST['sort'] == "bd") $sqlSelect .= "ORDER BY pp.broj_dosjea";
	else $sqlSelect .= "ORDER BY o.prezime, o.ime";
				
	$q=myquery($sqlSelect);
	
	
	?>
	
	<table width="<?=$sirina?>" border="1" cellpadding="1" cellspacing="1" bordercolor="#000000">
	<tr>
	<td width="10"><b>R.br.</b></td>
	<td align="center"><b>Opcije</b></td>
	<td><b>Prezime</b></td>
	<td><b>Ime</b></td>
	<td width="100"><b>Datum rođenja</b></td>
	<td><b>Mjesto rođenja</b></td>
	<td><b>Državljanstvo</b></td>
	<? if ($ciklus_studija==1) { ?>
	<td><b>Završena škola</b></td>
	<td><b>Uč. gen.</b></td><? } ?>
	<td width="115"><b>Jmbg</b></td>
	<td><b>Adresa</b></td>
	<td><b>Telefon</b></td>
	<td width="200"><b>Kanton</b></td>
	<? if ($ciklus_studija==1) { ?>
	<td width="90"><b>Opći uspjeh</b></td>
	<td width="90"><b>Ključni pred.</b></td>
	<td width="90"><b>Dodatni bod.</b></td><? } ?>
	<td width="90"><b>Prijemni ispit</b></td>
	<td width="70"><b>Tip studija</b></td>
	<? if ($ciklus_studija==1) { ?>
	<td width="80"><b>Odsjek prvi</b></td>
	<td width="80"><b>Odsjek drugi</b></td>
	<td width="80"><b>Odsjek treći</b></td>
	<td width="80"><b>Odsjek četvrti</b></td>
	<? } else { ?>
	<td width="80"><b>Odsjek</b></td><? } ?>
	<td width="10"><b>R.br.</b></td>
	<td align="center"><b>Opcije</b></td>
	</tr>
	
	<?
	$brojac = 1;
	while ($kandidat=mysql_fetch_row($q)) {
		?>
		
		<tr>
		<td align="center"><?=$kandidat[24];?></td>
		<td align="center">
		<a href="?sta=studentska/prijemni&akcija=obrisi&osoba=<?=$kandidat[0]?>&termin=<?=$termin?>">Obriši&nbsp;&nbsp;</a>
		<a href="?sta=studentska/prijemni&akcija=unos&izmjena=<?=$kandidat[0]?>&termin=<?=$termin?>">Izmijeni</a>
		</td>

		<td><?=$kandidat[2];?></td>
		<td><?=$kandidat[1];?></td>
		<td><?=date("d. m. Y",$kandidat[3]);?></td>
		<td><?=$imena_mjesta[$kandidat[4]];?></td>
		<td><?=$kandidat[5];?></td>
		<? if ($ciklus_studija==1) { ?>
		<td><?=$imena_skola[$kandidat[6]];?></td>
		<td><? if ($kandidat[23]>0) print "DA"; else print "&nbsp;"?></td><? } ?>
		<td><?=$kandidat[7];?></td>
		<td><?=$kandidat[8];?>, <?=$imena_mjesta[$kandidat[9]]?></td>
		<td><?=$kandidat[10];?></td>
		<td><?=$kandidat[11];?></td>
		<? if ($ciklus_studija==1) { ?>
		<td align="center"><?=$kandidat[12];?></td>
		<td align="center"><?=$kandidat[13];?></td>
		<td align="center"><?=$kandidat[14];?></td><? } ?>
		<td align="center"><?=$kandidat[17];?></td>
		<td align="center"><? if ($kandidat[16]) echo "redovni"; else echo "paralelni";?></td>
		<td align="center"><?=$imena_studija[$kandidat[19]];?></td>
		<? if ($ciklus_studija==1) { ?>
		<td align="center"><?=$imena_studija[$kandidat[20]];?></td>
		<td align="center"><?=$imena_studija[$kandidat[21]];?></td>
		<td align="center"><?=$imena_studija[$kandidat[22]];?></td><? } ?>
		<td align="center"><?=$kandidat[24];?></td>
		
		<td align="center">
		<a href="?sta=studentska/prijemni&akcija=obrisi&osoba=<?=$kandidat[0]?>&termin=<?=$termin?>">Obriši&nbsp;&nbsp;</a>
		<a href="?sta=studentska/prijemni&akcija=unos&izmjena=<?=$kandidat[0]?>&termin=<?=$termin?>">Izmijeni</a>
		</td>
		</tr>
		
		<?

		
		$brojac++;
	}

	?>
	
	</table>
	
	<hr color="black" width="<?=$sirina?>">
	<a href="index.php?sta=studentska/prijemni&akcija=pregled&sort=prezime&termin=<?=$termin?>">Sortirano po prezimenu</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="index.php?sta=studentska/prijemni&akcija=pregled&sort=bd&termin=<?=$termin?>">Sortirano po broju dosjea</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="index.php?sta=studentska/prijemni&akcija=unos&termin=<?=$termin?>">Dodaj kandidata&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a>
	<a href="index.php?sta=studentska/prijemni&akcija=prijemni&termin=<?=$termin?>">Unos bodova sa prijemnog ispita&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</font></a>
	<a href="index.php?sta=studentska/prijemni&akcija=kandidati&iz=bih&termin=<?=$termin?>">Kandidati BiH&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</font></a>
	<a href="index.php?sta=studentska/prijemni&akcija=kandidati&iz=strani&termin=<?=$termin?>">Kandidati (strani državljani)</font></a>
	<hr color="black" width="<?=$sirina?>">
	<?

}





// POTVRDA UNOSA KANDIDATA

// Polje vrstaunosa moze biti:
// - novi - kreiraju se novi zapisi u tabelama
// - novigreska - edituju se postojeci zapisi, ali korisnik nema mogucnost unosa ocjena
// - editovanje - ima mogucnost unosa ocjena
// - sljedeci - prelazak na unos sljedeceg kandidata


if ($_POST['akcija'] == 'unospotvrda' && check_csrf_token()) {

	$rosoba=intval($_REQUEST['osoba']);
	$rbrojdosjea=intval($_REQUEST['broj_dosjea']);
	$rime=my_escape(trim($_REQUEST['ime']));
	$rprezime=my_escape(trim($_REQUEST['prezime']));
	$rmjestorod=my_escape(trim($_REQUEST['mjesto_rodjenja']));
	$rdrzavljanstvo=my_escape(trim($_REQUEST['drzavljanstvo']));
	$rzavrskola=my_escape(trim($_REQUEST['zavrsena_skola']));
	$rjmbg=$_REQUEST['jmbg'];
	$radresa=my_escape(trim($_REQUEST['adresa']));
	$radresamjesto=my_escape(trim($_REQUEST['adresa_mjesto']));
	$rtelefon=my_escape(trim($_REQUEST['telefon_roditelja']));
	$rkanton=intval($_REQUEST['_lv_column_kanton']);
	if ($_REQUEST['tip_studija']) $rredovni=1; else $rredovni=0;
	if ($_REQUEST['ucenik_generacije']) $rgener=1; else $rgener=0;
	$opi=intval($_REQUEST['studij_prvi_izbor']);
	$odi=intval($_REQUEST['studij_drugi_izbor']);
	$oti=intval($_REQUEST['studij_treci_izbor']);
	$oci=intval($_REQUEST['studij_cetvrti_izbor']);
	$ropci=floatval(str_replace(",",".",$_REQUEST['opci_uspjeh']));
	$rkljucni=floatval(str_replace(",",".",$_REQUEST['kljucni_predmeti']));
	$rdodatni=floatval(str_replace(",",".",$_REQUEST['dodatni_bodovi']));
	$rprijemni=floatval(str_replace(",",".",$_REQUEST['prijemni']));

	// Obrada datuma
	if (preg_match("/(\d+).*?(\d+).*?(\d+)/",$_REQUEST['datum_rodjenja'],$matches)) {
		$dan=$matches[1]; $mjesec=$matches[2]; $godina=$matches[3];
		if ($godina<100)
			if ($godina<50) $godina+=2000; else $godina+=1900;
		if ($godina<1000)
			if ($godina<900) $godina+=2000; else $godina+=1000;
	}

	// Pronalazak mjesta i srednje skole i dodavanje u bazu
	$rmjrid=0;
	if ($rmjestorod != "") {
		$q300 = myquery("select id from mjesto where naziv like '$rmjestorod'");
		if (mysql_num_rows($q300)<1) {
			$q301 = myquery("insert into mjesto set naziv='$rmjestorod'");
			$q300 = myquery("select id from mjesto where naziv='$rmjestorod'");
			zamgerlog("upisano novo mjesto rodjenja $rmjestorod", 2);
		}
		$rmjrid = mysql_result($q300,0,0);
	}

	$radmid=0;
	if ($radresamjesto != "") {
		$q302 = myquery("select id from mjesto where naziv like '$radresamjesto'");
		if (mysql_num_rows($q302)<1) {
			$q303 = myquery("insert into mjesto set naziv='$radresamjesto'");
			$q302 = myquery("select id from mjesto where naziv='$radresamjesto'");
			zamgerlog("upisano novo mjesto (adresa) $radresamjesto", 2);
		}
		$radmid = mysql_result($q302,0,0);
	}

	$rskolaid=0;
	if ($rzavrskola != "") {
//		$rzavrskola = str_replace("&quot;", "\\\"", $rzavrskola);
		$rzavrskola = str_replace("\\\'", "\'", $rzavrskola);
		$q304 = myquery("select id from srednja_skola where naziv like '$rzavrskola'");
		if (mysql_num_rows($q304)<1) {
			$q305 = myquery("insert into srednja_skola set naziv='$rzavrskola'");
			$q304 = myquery("select id from srednja_skola where naziv='$rzavrskola'");
			zamgerlog("upisana nova srednja skola $rzavrskola", 2);
		}
		$rskolaid = mysql_result($q304,0,0);
	}


	// Dodatne provjere integriteta koje je lakše uraditi u PHPu nego u JavaScriptu
	$greska=0;
	if (!preg_match("/\w/",$rime)) {
		niceerror("Ime nije ispravno"); 
		$greska=1; $greskaime=1;
	}
	if (!preg_match("/\w/",$rprezime)) {
		niceerror("Prezime nije ispravno"); 
		$greska=1; $greskaprezme=1;
	}
	if ($rmjrid==0) {
		niceerror("Mjesto rođenja nije ispravno"); 
		$greska=1; $greskamjestorod=1;
	}
	if (!preg_match("/\w/",$rdrzavljanstvo)) {
		niceerror("Državljanstvo nije ispravno"); 
		$greska=1; $greskadrzavljanstvo=1;
	}
	if ($rdrzavljanstvo != "BiH" && $rkanton!=13) {
		niceerror("Državljanstvo je različito od 'BiH' (".$rdrzavljanstvo."), a kanton nije stavljen na 'Strani državljanin'"); 
		$greska=1; $greskadrzavljanstvo=1;
	}
	if (testjmbg($rjmbg) != "") {
		niceerror("JMBG neispravan: ".testjmbg($rjmbg)); 
		$greska=1; $greskajmbg=1;
	}
	if (preg_match("/(\d+).*?(\d+).*?(\d+)/",$_REQUEST['datum_rodjenja'],$matches)) {
		// Ovo je već urađeno:
		// $dan=$matches[1]; $mjesec=$matches[2]; $godina=$matches[3];
		if (!checkdate($mjesec,$dan,$godina)) {
			niceerror("Datum rođenja je kalendarski nemoguć ($dan. $mjesec. $godina)");
			$greskadatumrod=1;
			$greska=1;
		}
		$jdan=intval(substr($_REQUEST['jmbg'],0,2));
		$jmjesec=intval(substr($_REQUEST['jmbg'],2,2));
		$jgodina=intval(substr($_REQUEST['jmbg'],4,3));
		if ($jgodina>900) $jgodina+=1000; else $jgodina+=2000;
		if ($dan!=$jdan || $mjesec!=$jmjesec || $godina!=$jgodina) {
			niceerror("Uneseni datum rođenja se ne poklapa s onim u JMBGu"); $greska=1;
			$greskadatumrod=1;
		}
	} else {
		niceerror("Datum rođenja nije ispravan - ne sadrži dovoljan broj cifara.");
		$greska=1; $greskadatumrod=1;
	}


	// Transakcija!
	$q305 = myquery("lock tables osoba write, prijemni_prijava write, uspjeh_u_srednjoj write, log write");

	// Da li se broj dosjea ponavlja??
	if ($_REQUEST['vrstaunosa']=="novi" || $rosoba==0) {
		$q308 = myquery("select count(*) from prijemni_prijava where broj_dosjea=$rbrojdosjea and prijemni_termin=$termin");
	} else {
		$q308 = myquery("select count(*) from prijemni_prijava where broj_dosjea=$rbrojdosjea and prijemni_termin=$termin and osoba!=$rosoba");
	}
	if (mysql_result($q308,0,0)>0) {
		niceerror("Broj dosjea $rbrojdosjea je već unesen! Izaberite neki drugi redni broj.");
		$greska=1;
		$greskabrojdos=1;
	}


	// Dodajemo novog kandidata u tabele osoba, prijemni_prijava i uspjeh_u_srednjoj

	if ($_REQUEST['vrstaunosa']=="novi" || $rosoba==0) {

		// Nova osoba
		//$min_id=1;
		$min_id = 2500; // radi lakseg grupisanja brojeva

		$q310 = myquery("select id+1 from osoba where id>=$min_id order by id desc limit 1");
		if (mysql_num_rows($q310)<1)
			$rosoba=$min_id;
		else
			$rosoba=mysql_result($q310,0,0);

		$q320 = myquery("insert into osoba set id=$rosoba, ime='$rime', prezime='$rprezime', email='', brindexa='', datum_rodjenja='$godina-$mjesec-$dan', mjesto_rodjenja=$rmjrid, drzavljanstvo='$rdrzavljanstvo', jmbg='$rjmbg', adresa='$radresa', adresa_mjesto=$radmid, telefon='$rtelefon', kanton=$rkanton, treba_brisati=0");

		// Nova prijava prijemni
		$q330 = myquery("insert into prijemni_prijava set prijemni_termin=$termin, osoba=$rosoba, broj_dosjea=$rbrojdosjea, redovan=$rredovni, studij_prvi=$opi, studij_drugi=$odi, studij_treci=$oti, studij_cetvrti=$oci, izasao=0, rezultat=0");

		// Novi uspjeh u srednjoj -- samo za prvi ciklus
		if ($ciklus_studija==1) {
			$q340 = myquery("insert into uspjeh_u_srednjoj set osoba=$rosoba, srednja_skola=$rskolaid, opci_uspjeh=0, kljucni_predmeti=0, dodatni_bodovi=0, ucenik_generacije=$rgener");
		}

		zamgerlog("novi kandidat za prijemni u$rosoba broj dosjea $rbrojdosjea", 2);

		// Nastavljamo sa unosom ocjena:
		$_REQUEST['akcija']="unos";
		$vrstaunosa="editovanje";
		$osoba=$rosoba;

		// Ako dodje do greske, mi cemo ubaciti podatke u bazu ali cemo jednostavno traziti da se ponovo unesu
		if ($greska==1) $vrstaunosa="novigreska";
	}

	else { // Editovanje postojeceg kandidata

		// Updatujem osobu
		$q350 = myquery("update osoba set ime='$rime', prezime='$rprezime', datum_rodjenja='$godina-$mjesec-$dan', mjesto_rodjenja=$rmjrid, drzavljanstvo='$rdrzavljanstvo', jmbg='$rjmbg', adresa='$radresa', adresa_mjesto=$radmid, telefon='$rtelefon', kanton=$rkanton, treba_brisati=0 where id=$rosoba");

		// Updatujem prijavu prijemnog
		$q360 = myquery("update prijemni_prijava set broj_dosjea=$rbrojdosjea, redovan=$rredovni, studij_prvi=$opi, studij_drugi=$odi, studij_treci=$oti, studij_cetvrti=$oci, rezultat=$rprijemni where osoba=$rosoba and prijemni_termin=$termin");

		// Updatujem uspjeh u srednjoj -- samo za prvi ciklus
		if ($ciklus_studija==1) {
			$q370 = myquery("update uspjeh_u_srednjoj set srednja_skola=$rskolaid, opci_uspjeh=$ropci, kljucni_predmeti=$rkljucni, dodatni_bodovi=$rdodatni, ucenik_generacije=$rgener where osoba=$rosoba");
		}

		zamgerlog("izmjena kandidata za prijemni u$rosoba broj dosjea $rbrojdosjea", 2);

		$_REQUEST['akcija']="unos";

		// Ako je prethodni unos bio posljedica greske...
		if ($_REQUEST['vrstaunosa']=="novigreska") {
			// ...prelazimo na unos ocjena
			$vrstaunosa="editovanje";
			$osoba=$rosoba; // zaboravi osobu

			if ($greska==1) $vrstaunosa="novigreska"; // osim ako nije opet greska

		// Kod editovanja...
		} else {
			// ...unosimo sljedeceg kandidata
			$vrstaunosa="novi";
			$osoba=0; // zaboravi osobu
			if ($greska==1) { // u slucaju greske ponovo editujemo
				$vrstaunosa="editovanje";
				$osoba=$rosoba;
			}
		}
	}

	// Kraj transakcije
	$q380 = myquery("unlock tables");
}




// AKCIJA=UNOS NOVOG STUDENTA

if ($_REQUEST['akcija']=="unos") {

// Polje vrstaunosa moze biti:
// - novi - kreiraju se novi zapisi u tabelama
// - novigreska - edituju se postojeci zapisi, ali korisnik nema mogucnost unosa ocjena
// - editovanje - ima mogucnost unosa ocjena
// - sljedeci - prelazak na unos sljedeceg kandidata
if (!$vrstaunosa) {
	// ako je prosli put unosen novi kandidat, varijabla $osoba je definisana u akciji unospotvrda
	if (intval($_REQUEST['izmjena'])>0) {
		$vrstaunosa="editovanje";
		$osoba = intval($_REQUEST['izmjena']);
	} else {
		$vrstaunosa="novi";
	}
}


// Traženje postojeće osobe po JMBGu
if (intval($_REQUEST['trazijmbg'])>0) {
	$jmbg = my_escape($_REQUEST['trazijmbg']); // u biti ne znamo format JMBGa
	$q1 = myquery("select id from osoba where jmbg='$jmbg'");
	if (mysql_num_rows($q1)<1) {
		niceerror("Traženi JMBG nije pronađen ($jmbg).");
		$vrstaunosa="novi";
		$ejmbg=$jmbg;
	} else {
		$osoba = mysql_result($q1,0,0);
		$vrstaunosa="editovanje";
		// Da li je osoba vec na prijemnom?
		$q2 = myquery("select count(*) from prijemni_prijava where prijemni_termin=$termin and osoba=$osoba");
		if (mysql_result($q2,0,0)>0) {
			nicemessage("Osoba sa JMBGom već prijavljena na prijemni");
		} else {
			// Broj dosjea postavljamo na prvi slobodan
			$q3 = myquery("select broj_dosjea+1 from prijemni_prijava where prijemni_termin=$termin order by broj_dosjea desc limit 1");
			if (mysql_num_rows($q3)<1)
				$nbrojdosjea=1;
			else
				$nbrojdosjea=mysql_result($q3,0,0);

			// Kod upisa na više cikluse, pretpostavljamo da će upisati isti studij
			// što određujemo na osnovu institucije
			$sp=0;
			if ($ciklus_studija>1) {
				$q4 = myquery("select s.institucija from studij as s, student_studij as ss where ss.student=$osoba and ss.studij=s.id order by ss.akademska_godina desc, ss.semestar desc limit 1");
				if (mysql_num_rows($q4)>0) { // Da li je ikada studirao išta ovdje?
					$q5 = myquery("select s.id from studij as s, tipstudija as ts where s.institucija=".mysql_result($q4,0,0)." and s.tipstudija=ts.id and ts.ciklus=$ciklus_studija");
					$sp = mysql_result($q5,0,0);
				}

				// Brišemo ranije podatke o uspjehu kako ne bismo stvorili konflikt sa podacima sa prošlog prijemnog (MSc -> PhD)
				$q5a = myquery("delete from prosliciklus_ocjene where osoba=$osoba");
				$q5b = myquery("delete from prosliciklus_uspjeh where osoba=$osoba");
			}

			$q6 = myquery("insert into prijemni_prijava set prijemni_termin=$termin, osoba=$osoba, broj_dosjea=$nbrojdosjea, studij_prvi=$sp");

			nicemessage("Prijavljujem osobu na prijemni ispit");
		}

		// Da li je potrebno kreirati zapis u tabeli "uspjeh u srednjoj"? samo za prvi ciklus
		if ($ciklus_studija==1) {
			$q7 = myquery("select count(*) from uspjeh_u_srednjoj where osoba=$osoba");
			if (mysql_result ($q7,0,0)<1) {
				$q8 = myquery("insert into uspjeh_u_srednjoj set osoba=$osoba");
				// Ostale stvari ce biti popunjene kroz formular
			}

		} else {
			// Za više cikluse, popunićemo tabelu podacima o prethodnom ciklusu iz Zamgera
			$q9 = myquery("select ko.ocjena, p.ects, pk.semestar from konacna_ocjena as ko, ponudakursa as pk, predmet as p, student_predmet as sp, studij as s, tipstudija as ts where ko.student=$osoba and ko.predmet=pk.predmet and ko.akademska_godina=pk.akademska_godina and pk.predmet=p.id and pk.id=sp.predmet and sp.student=$osoba and pk.studij=s.id and s.tipstudija=ts.id and ts.ciklus=".($ciklus_studija-1));
			$bodovi=0; // Odmah izracunavamo i bodove
			$rednibroj=1;
			$maxsemestar=0;
			$sumaects=0;
			while ($r9 = mysql_fetch_row($q9)) {
				$q10 = myquery("insert into prosliciklus_ocjene set osoba=$osoba, redni_broj=$rednibroj, ocjena=$r9[0], ects=$r9[1]");
				$rednibroj++;
				$bodovi += ($r9[0]*$r9[1]);
				$sumaects += $r9[1];
				if ($r9[2]>$maxsemestar) $maxsemestar=$r9[2];
			}
			// bodovi = suma od (ocjena*ects) / suma ects / brojsemestara
			$bodovi = $bodovi / $sumaects / $maxsemestar;
			$q11 = myquery("insert into prosliciklus_uspjeh set osoba=$osoba, opci_uspjeh=$bodovi, broj_semestara=$maxsemestar");
		}
	}
}


$eredovni=1;
$ekanton=$eopci=$ekljucni=$edodatni=$eprijemni=0;

if ($osoba>0) {
	$q = myquery("select o.ime, o.prezime, UNIX_TIMESTAMP(o.datum_rodjenja), o.mjesto_rodjenja, o.drzavljanstvo, o.jmbg, o.adresa, o.adresa_mjesto, o.telefon, o.kanton, pp.redovan, pp.studij_prvi, pp.studij_drugi, pp.studij_treci, pp.studij_cetvrti, pp.rezultat, pp.broj_dosjea, pp.izasao from osoba as o, prijemni_prijava as pp where o.id=$osoba and o.id=pp.osoba and pp.prijemni_termin=$termin");
	$eime = mysql_result($q,0,0);
	$eprezime = mysql_result($q,0,1);
	$edatum = date("d. m. Y.",mysql_result($q,0,2));
	$emjesto = mysql_result($q,0,3);
	$edrz = mysql_result($q,0,4);
	$ejmbg = mysql_result($q,0,5);
	$eadresa = mysql_result($q,0,6);
	$eadresamjesto = mysql_result($q,0,7);
	$etelefon = mysql_result($q,0,8);
	$ekanton = mysql_result($q,0,9);
	$eredovni = mysql_result($q,0,10);
	$eopi = mysql_result($q,0,11);
	$eodi = mysql_result($q,0,12);
	$eoti = mysql_result($q,0,13);
	$eoci = mysql_result($q,0,14);
	$eprijemni = mysql_result($q,0,15);
	$ebrojdosjea = mysql_result($q,0,16);
	$eizasao = mysql_result($q,0,17);

	if ($ciklus_studija==1) { // Uzimamo podatke za srednju skolu - samo ako se upisuje na prvi ciklus
		$q300 = myquery("select srednja_skola, opci_uspjeh, kljucni_predmeti, dodatni_bodovi, ucenik_generacije from uspjeh_u_srednjoj where osoba=$osoba");
		$eskola = mysql_result($q300,0,0);
		$eopci = mysql_result($q300,0,1);
		$ekljucni = mysql_result($q300,0,2);
		$edodatni = mysql_result($q300,0,3);
		$egener = mysql_result($q300,0,4);
	} else { // podaci za prosli ciklus
		$q310 = myquery("select fakultet, opci_uspjeh, dodatni_bodovi, broj_semestara from prosliciklus_uspjeh where osoba=$osoba");
		$efakultet = mysql_result($q310,0,0);
		$eopci = mysql_result($q310,0,1);
		$edodatni = mysql_result($q310,0,2);
		$ebrojsem = mysql_result($q310,0,3);
	}
}

else { // Nova osoba
	// Odredjujemo broj dosjea
	$q220 = myquery("select broj_dosjea+1 from prijemni_prijava where prijemni_termin=$termin order by broj_dosjea desc limit 1");
	if (mysql_num_rows($q220)<1)
		$ebrojdosjea=1;
	else
		$ebrojdosjea=mysql_result($q220,0,0);
}


// Spisak dostupnih studija ovisno o ciklusu studija

$q230 = myquery("select s.id, s.kratkinaziv from studij as s, tipstudija as ts where s.moguc_upis=1 and s.tipstudija=ts.id and ts.ciklus=$ciklus_studija order by s.kratkinaziv");
$studiji = array();
$sstudimena="";
$sstudbrojevi="";
while ($r230 = mysql_fetch_row($q230)) {
	$studiji[$r230[0]] = $r230[1];
	if ($sstudimena != "") $sstudimena .= ",";
	$sstudimena .= "'".$r230[1]."'";
	if ($sstudbrojevi != "") $sstudbrojevi .= ",";
	$sstudbrojevi .= "'".$r230[0]."'";
}


// Spisak gradova za mjesto rodjenja i adresu

$q240 = myquery("select id, naziv from mjesto order by naziv");
$gradovir="<option></option>";
$gradovia="<option></option>";
while ($r240 = mysql_fetch_row($q240)) {
	$gradovir .= "<option"; $gradovia .= "<option";
 	if ($r240[0]==$emjesto) { $gradovir  .= " SELECTED"; $mjestorvalue = $r240[1]; }
 	if ($r240[0]==$eadresamjesto) { $gradovia  .= " SELECTED"; $adresarvalue = $r240[1]; }
	$gradovir .= ">$r240[1]</option>\n";
	$gradovia .= ">$r240[1]</option>\n";
}



// Spisak srednjih skola

$q250 = myquery("select id, naziv from srednja_skola order by naziv");
$srednjer="<option></option>";
while ($r250 = mysql_fetch_row($q250)) {
	$srednjer .= "<option";
 	if ($r250[0]==$eskola) { $srednjer  .= " SELECTED"; $skolarvalue = $r250[1]; }
	$srednjer .= ">$r250[1]</option>\n";
}



// Tabela za unos podataka - design
?>
<h3>Unos kandidata</h3>
<br />

<SCRIPT language="JavaScript">

// Funkcija update_izobre() kod izbora studija kao "prvi izbor" izbacuje taj studij iz liste za drugi, treci itd. 
// Slicno radi i za drugi i treci izbor.

function update_izbore() {
	var studijiimena = new Array(<?=$sstudimena?>);
	var studijibrojevi = new Array(<?=$sstudbrojevi?>);
	var prvi = document.getElementById('studij_prvi_izbor');
	odzuti(prvi);
	var drugi = document.getElementById('studij_drugi_izbor');
	var treci = document.getElementById('studij_treci_izbor');
	var cetvrti = document.getElementById('studij_cetvrti_izbor');
	var drugval = drugi.value;
	while (drugi.length>1)
		drugi.options[1]=null;
	for (i=0; i<4; i++) {
		if (studijibrojevi[i] != prvi.value) { 
			drugi.options[drugi.length]=new Option(studijiimena[i],studijibrojevi[i]);
			if (drugval==studijibrojevi[i]) drugi.selectedIndex=drugi.length-1;
		}
	}
	var trecval = treci.value;
	while (treci.length>1)
		treci.options[1]=null;
	for (i=0; i<4; i++) {
		if (studijibrojevi[i] != prvi.value && studijibrojevi[i] != drugi.value) { 
			treci.options[treci.length]=new Option(studijiimena[i],studijibrojevi[i]);
			if (trecval==studijibrojevi[i]) treci.selectedIndex=treci.length-1;
		}
	}
	var cetval = cetvrti.value;
	while (cetvrti.length>1)
		cetvrti.options[1]=null;
	for (i=0; i<4; i++) {
		if (studijibrojevi[i] != prvi.value && studijibrojevi[i] != drugi.value && studijibrojevi[i] != treci.value) { 
			cetvrti.options[cetvrti.length]=new Option(studijiimena[i],studijibrojevi[i]);
			if (cetval==studijibrojevi[i]) cetvrti.selectedIndex=cetvrti.length-1;
		}
	}
}

// Kada korisnik ukuca nesto u obavezno polje, ono prestaje biti zuto (postaje bijelo)
function odzuti(nesto) {
	nesto.style.backgroundColor = '#FFFFFF';
}

// Predji na sljedece polje pritiskom na dugme enter
function enterhack(e,gdje) {
	if(e.keyCode==13) {
		document.getElementById(gdje).focus();
		return false;
	}
}

// Trazimo osobu sa datim JMBGom u bazi
function jmbg_trazi() {
	var jmbg = document.getElementById('jmbg').value;
	document.location.replace('index.php?sta=studentska/prijemni&akcija=unos&trazijmbg='+jmbg+'&termin=<?=$termin?>');
}

</SCRIPT>

<script type="text/javascript" src="js/combo-box.js"></script>

	<script type="text/javascript">
	function comboBoxEdit(evt, elname) {
		var ib = document.getElementById(elname);
		var list = document.getElementById("comboBoxDiv_"+elname);
		var listsel = document.getElementById("comboBoxMenu_"+elname);

		var key, keycode;
		if (evt) {
			key = evt.which;
			keycode = evt.keyCode;
		} else if (window.event) {
			key = window.event.keyCode;
			keycode = key; // wtf?
		} else return true;

		if (keycode==40) { // arrow down
			if (list.style.visibility == 'visible') {
				if (listsel.selectedIndex<listsel.length)
					listsel.selectedIndex = listsel.selectedIndex+1;
			} else {
				comboBoxShowHide(elname);
			}
			return false;

		} else if (keycode==38) { // arrow up
			if (list.style.visibility == 'visible' && listsel.selectedIndex>0) {
				listsel.selectedIndex = listsel.selectedIndex-1;
			}
			return false;

		} else if (keycode==13 && list.style.visibility == 'visible') { // Enter key - select option and hide
			comboBoxOptionSelected(elname);
			return false;

		} else if (key>31 && key<127) {
			// This executes before the letter is added to text
			// so we have to add it manually
			var ibtxt = ib.value.toLowerCase() + String.fromCharCode(key).toLowerCase();

			for (i=0; i<listsel.length; i++) {
				var listtxt = listsel.options[i].value.toLowerCase();
				if (ibtxt == listtxt.substr(0,ibtxt.length)) {
					listsel.selectedIndex=i;
					if (list.style.visibility == 'hidden') comboBoxShowHide(elname);
					return true;
				}
			}
			return true;
		}
		return true;
	}

	function comboBoxShowHide(elname) {
		var ib = document.getElementById(elname);
		var list = document.getElementById("comboBoxDiv_"+elname);
		var image = document.getElementById("comboBoxImg_"+elname);

		if (list.style.visibility == 'hidden') {
			// Nadji poziciju objekta
			var curleft = curtop = 0;
			var obj=ib;
			if (obj.offsetParent) {
				do {
					curleft += obj.offsetLeft;
					curtop += obj.offsetTop;
				} while (obj = obj.offsetParent);
			}
	
			list.style.visibility = 'visible';
			list.style.left=curleft;
			list.style.top=curtop+ib.offsetHeight;
			image.src = "images/cb_down.png";
		} else {
			list.style.visibility = 'hidden';
			image.src = "images/cb_up.png";
		}
	}
	function comboBoxOptionSelected(elname) {
		var ib = document.getElementById(elname);
		var listsel = document.getElementById("comboBoxMenu_"+elname);
		
		ib.value = listsel.options[listsel.selectedIndex].value;
		comboBoxShowHide(elname);
	}
	</script>

<?

// Nećemo da se ove varijable pojavljuju u genform
unset($_REQUEST['broj_dosjea']); unset($_REQUEST['ime']); unset($_REQUEST['prezime']); unset($_REQUEST['datum_rodjenja']); unset($_REQUEST['osoba']); unset($_REQUEST['vrstaunosa']); unset($_REQUEST['mjesto_rodjenja']); unset($_REQUEST['drzavljanstvo']); unset($_REQUEST['zavrsena_skola']); unset($_REQUEST['jmbg']); unset($_REQUEST['adresa']); unset($_REQUEST['adresa_mjesto']); unset($_REQUEST['telefon_roditelja']); unset($_REQUEST['tip_studija']); unset($_REQUEST['ucenik_generacije']); unset($_REQUEST['studij_prvi_izbor']); unset($_REQUEST['studij_drugi_izbor']); unset($_REQUEST['studij_treci_izbor']); unset($_REQUEST['studij_cetvrti_izbor']); unset($_REQUEST['prijemni']); unset($_REQUEST['opci_uspjeh']); unset($_REQUEST['kljucni_predmeti']); unset($_REQUEST['dodatni_bodovi']);
unset($_REQUEST['trazijmbg']);

// Sljedeci i prethodni broj dosjea
$q260 = myquery("select osoba from prijemni_prijava where broj_dosjea<".intval($ebrojdosjea)." and prijemni_termin=$termin order by broj_dosjea desc limit 1");
if (mysql_num_rows($q260)>0) {
	$lijevodugme = '<input type="button" value="  <<  " onclick="javascript:document.location.replace(\'index.php?sta=studentska/prijemni&akcija=unos&izmjena='.mysql_result($q260,0,0).'&termin='.$termin.'\')"> ';
} else {
	$lijevodugme = '<input type="button" value="  <<  " disabled> ';
}

$q270 = myquery("select osoba from prijemni_prijava where broj_dosjea>".intval($ebrojdosjea)." and prijemni_termin=$termin order by broj_dosjea limit 1");
if (mysql_num_rows($q270)>0) {
	$desnodugme = '<input type="button" value="  >>  " onclick="javascript:document.location.replace(\'index.php?sta=studentska/prijemni&akcija=unos&izmjena='.mysql_result($q270,0,0).'&termin='.$termin.'\')"> ';
} else {
	$desnodugme = '<input type="button" value="  >>  " disabled> ';
}


print genform("POST", "glavnaforma");?>
<input type="hidden" name="akcija" value="unospotvrda">
<input type="hidden" name="osoba" value="<?=$osoba?>">
<input type="hidden" name="vrstaunosa" value="<?=$vrstaunosa?>">

<table border="0" cellpadding="3" cellspacing="0">
	<tr>
		<td width="130" align="left">Broj dosjea:</td>
		<td><?=$lijevodugme?><input maxlength="50" size="5" name="broj_dosjea" id="broj_dosjea" type="text" value="<?=$ebrojdosjea?>" autocomplete="off" onkeypress="enterhack(event,'ime')" class="default"
		<? if ($greskabrojdos) {
			?> style="background-color:#FF0000" oninput="odzuti(this)" <? 
		} 
		?>><font color="#FF0000">*</font> <?=$desnodugme?></td>
	</tr>
	<tr>
		<td width="130" align="left">Ime kandidata:</td>
		<td><input maxlength="50" size="17" name="ime" id="ime" type="text" class="default" 
		<? if ($greskaime) {
			?> value="<?=$eime?>" style="background-color:#FF0000" oninput="odzuti(this)" <? 
		} else if ($eime) {
			?> value="<?=$eime?>"<? 
		} else {
			?> style="background-color:#FFFF00" oninput="odzuti(this)" <? 
		} 
		?> autocomplete="off" onkeypress="return enterhack(event,'prezime')"><font color="#FF0000">*</font></td>
	</tr>
	<tr>
		<td width="125" align="left">Prezime kandidata:</td>
		<td><input maxlength="50" size="17" name="prezime" id="prezime" type="text" class="default" 
		<? if ($greskaprezime) {
			?> value="<?=$eprezime?>" style="background-color:#FF0000" oninput="odzuti(this)" <? 
		} else if ($eprezime) {
			?> value="<?=$eprezime?>"<? 
		} else {
			?> style="background-color:#FFFF00" oninput="odzuti(this)" <? 
		} 
		?> autocomplete="off" onkeypress="return enterhack(event,'datum_rodjenja')"><font color="#FF0000">*</font></td>
	</tr>
	<tr>
		<td width="125" align="left">Datum rođenja:</td>
		<td><input maxlength="20" size="17" name="datum_rodjenja" id="datum_rodjenja" type="text" class="default" 
		<? if ($greskadatumrod) {
			?> value="<?=$edatum?>" style="background-color:#FF0000" oninput="odzuti(this)" <? 
		} else if ($edatum) {
			?> value="<?=$edatum?>"<? 
		} else { 
			?> style="background-color:#FFFF00" oninput="odzuti(this)" <? 
		} 
		?> autocomplete="off" onkeypress="return enterhack(event,'mjesto_rodjenja')"><font color="#FF0000">*</font></td>
	</tr>
	<tr>
		<td width="125" align="left">Mjesto rođenja:</td>

		<td><input type="text" name="mjesto_rodjenja" id="mjesto_rodjenja" value="<?=$mjestorvalue?>" class="default" onKeyPress="return comboBoxEdit(event, 'mjesto_rodjenja'); this.style.backgroundColor = '#FFFFFF';" autocomplete="off" size="17" <?
		if ($greskamjestorod) {
			?> style="background-color:#FF0000" onChange="this.style.backgroundColor = '#FFFFFF'"<?
		} else if ($emjesto==0) {
			?> style="background-color:#FFFF00" onChange="this.style.backgroundColor = '#FFFFFF'"<? 
		} else {
			?> style="background-color:#FFFFFF"<? 
		} 
		?>><img src="images/cb_up.png" width="19" height="18" onClick="comboBoxShowHide('mjesto_rodjenja')" id="comboBoxImg_mjesto_rodjenja" valign="bottom">
		<!-- Rezultati pretrage primaoca -->
		<div id="comboBoxDiv_mjesto_rodjenja" style="position:absolute;visibility:hidden">
			<select name="comboBoxMenu_mjesto_rodjenja" id="comboBoxMenu_mjesto_rodjenja" size="10" onClick="comboBoxOptionSelected('mjesto_rodjenja')"><?=$gradovir?></select>
		</div><font color="#FF0000">*</font></td>

	</tr>
	<tr>
		<td width="125" align="left">Državljanstvo:</td>
		<td><input maxlength="40" size="17" name="drzavljanstvo" id="drzavljanstvo" type="text" class="default"  
		<? if ($greskadrzavljanstvo) {
			?> value="<?=$edrz?>" style="background-color:#FF0000"<? 
		} else if ($edrz) {
			?> value="<?=$edrz?>"<? 
		} else {
			?> value="BiH" <? 
		} 
		?> onkeypress="return enterhack(event,'zavrsena_skola')"><font color="#FF0000">*</font></td>
	</tr>
<?
	// Srednju školu prikazujemo samo za prvi ciklus
	if ($ciklus_studija==1) {
?>
	<tr>
		<td width="125" align="left">Završena škola:</td>
		<td><input type="text" name="zavrsena_skola" id="zavrsena_skola" value="<?=$skolarvalue?>" class="default" onKeyPress="return comboBoxEdit(event, 'zavrsena_skola'); this.style.backgroundColor = '#FFFFFF';" autocomplete="off" size="35" class="default" 
		<? if ($eskola==0) {
			?> style="background-color:#FFFF00" onChange="this.style.backgroundColor = '#FFFFFF'"<? 
		} else {
			?> style="background-color:#FFFFFF"<? 
		} 
		?>><img src="images/cb_up.png" width="19" height="18" onClick="comboBoxShowHide('zavrsena_skola')" id="comboBoxImg_zavrsena_skola" valign="bottom">
		<!-- Rezultati pretrage primaoca -->
		<div id="comboBoxDiv_zavrsena_skola" style="position:absolute;visibility:hidden">
			<select name="comboBoxMenu_zavrsena_skola" id="comboBoxMenu_zavrsena_skola" size="10" onClick="comboBoxOptionSelected('zavrsena_skola')"><?=$srednjer?></select>
		</div></td>

	</tr>
<?
	}
?>
	<tr>
		<td width="125" align="left">JMBG:</td>
		<td><input maxlength="13" size="17" name="jmbg" id="jmbg" type="text" class="default" 
		<? if ($greskajmbg) {
			?> value="<?=$ejmbg?>" style="background-color:#FF0000" oninput="odzuti(this)" <? 
		} else if ($ejmbg) {
			?> value="<?=$ejmbg?>"<? 
		} else {
			?> style="background-color:#FFFF00" oninput="odzuti(this)" <? 
		} 
		?> autocomplete="off" onkeypress="return enterhack(event,'adresa')"><font color="#FF0000">*</font>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" value=" Traži " onclick="javascript:jmbg_trazi();"></td>
	</tr>
	<tr>
		<td width="125" align="left">Adresa:</td>
		<td><input maxlength="50" size="17" name="adresa" id="adresa" type="text" class="default" <? 
		if ($eadresa) { 
			?> value="<?=$eadresa?>"<? 
		} else { 
			?> style="background-color:#FFFF00" oninput="odzuti(this)" <? 
		} 
		?> autocomplete="off" onkeypress="return enterhack(event,'telefon_roditelja')"></td>
	</tr>
	<tr>
		<td width="125" align="left">Adresa (mjesto):</td>
		<td><input type="text" name="adresa_mjesto" id="adresa_mjesto" value="<?=$adresarvalue?>" class="default" onKeyPress="return comboBoxEdit(event, 'adresa_mjesto'); this.style.backgroundColor = '#FFFFFF';" autocomplete="off" size="17" <?
		if ($greskamjestorod) {
			?> style="background-color:#FF0000" onChange="this.style.backgroundColor = '#FFFFFF'"<?
		} else if ($emjesto==0) {
			?> style="background-color:#FFFF00" onChange="this.style.backgroundColor = '#FFFFFF'"<? 
		} else {
			?> style="background-color:#FFFFFF"<? 
		} 
		?>><img src="images/cb_up.png" width="19" height="18" onClick="comboBoxShowHide('adresa_mjesto')" id="comboBoxImg_adresa_mjesto" valign="bottom">
		<!-- Rezultati pretrage primaoca -->
		<div id="comboBoxDiv_adresa_mjesto" style="position:absolute;visibility:hidden">
			<select name="comboBoxMenu_adresa_mjesto" id="comboBoxMenu_adresa_mjesto" size="10" onClick="comboBoxOptionSelected('adresa_mjesto')"><?=$gradovia?></select>
		</div><font color="#FF0000">*</font></td>

	</tr>
	<tr>
		<td width="125" align="left">Telefon roditelja:</td>
		<td><input maxlength="30" size="17" name="telefon_roditelja" id="telefon_roditelja" type="text" class="default"
		<? if ($etelefon) { 
			?> value="<?=$etelefon?>"<? 
		} else { 
			?> style="background-color:#FFFF00" oninput="odzuti(this)" <? 
		} 
		?> autocomplete="off" onkeypress="return enterhack(event,'kanton')"></td>
	</tr>
	<tr>
		<td width="125" align="left">Kanton:</td>
		<td><?=db_dropdown("kanton",$ekanton,"-- Izaberite kanton --")?></td>
	</tr>
	<tr>
		<td width="125" align="left">Redovni studij?</td>
		<td><input type="checkbox" name="tip_studija"  <? if ($eredovni) { ?> checked="checked" <? } ?> value="1"></td>
	</tr>
<?
	// Polje "učenik generacije" prikazujemo samo kod upisa u prvi ciklus
	if ($ciklus_studija==1) {
?>
	<tr>
		<td width="125" align="left">Učenik generacije?</td>
		<td><input type="checkbox" name="ucenik_generacije" <? if ($egener) { ?> checked="checked" <? } ?> value="1"></td>
	</tr>
<? } ?>
<?
	// Više izbora nudimo samo za prvi ciklus studija (svakako je to hack)
	if ($ciklus_studija==1) {
?>
	<tr>
		<td width="125" align="left">Studij:</td>
		<td>
		<table width="100%" border="0" align="center">
			<tr><td>Prvi izbor</td><td>Drugi izbor</td><td>Treći izbor</td><td>Četvrti izbor</td></tr>
			<tr>
				<td><select name="studij_prvi_izbor" id="studij_prvi_izbor" onchange="update_izbore()" <? if (!$eopi) { ?> style="background-color:#FFFF00"<? } ?>><option></option><? 
				foreach($studiji as $id => $naziv) {
					print "<option value=\"$id\"";
					if ($id==$eopi) print " selected";
					print ">$naziv</option>";
				}?></select></td>
				<td><select name="studij_drugi_izbor" id="studij_drugi_izbor" onchange="update_izbore()"><option></option><?
				foreach($studiji as $id => $naziv) {
					print "<option value=\"$id\"";
					if ($id==$eodi) print " selected";
					print ">$naziv</option>";
				}?></select></td>
				<td><select name="studij_treci_izbor" id="studij_treci_izbor" onchange="update_izbore()"><option></option><? 
				foreach($studiji as $id => $naziv) {
					print "<option value=\"$id\"";
					if ($id==$eoti) print " selected";
					print ">$naziv</option>";
				}?></select></td>
				<td><select name="studij_cetvrti_izbor" id="studij_cetvrti_izbor" onchange="update_izbore()"><option></option><? 
				foreach($studiji as $id => $naziv) {
					print "<option value=\"$id\"";
					if ($id==$eoci) print " selected";
					print ">$naziv</option>";
				}?></select></td>
			</tr>
		</table>
		</td>
	</tr>
<?
	} else { // Samo jedan izbor -- TODO (mozda) omogućiti konfigurisanje da li je izbor samo jedan ili višestruki na nivou termina
?>
	<tr>
		<td width="125" align="left">Studij:</td>
		<td><select name="studij_prvi_izbor" id="studij_prvi_izbor" <? if (!$eopi) { ?> style="background-color:#FFFF00"<? } ?>><option></option><? 
				foreach($studiji as $id => $naziv) {
					print "<option value=\"$id\"";
					if ($id==$eopi) print " selected";
					print ">$naziv</option>";
				}?></select>
		</td>
	</tr>
<? } ?>
<? if ($eizasao>0) { ?>
	<tr>
		<td width="125" align="left">Bodovi na prijemnom:</td>
		<td><input maxlength="50" size="17" name="prijemni" id="prijemni" type="text" value="<?=$eprijemni?>" autocomplete="off"></td>
	</tr>
<? } else { ?>
	<input type="hidden" name="prijemni" value="0">
<? } ?>
</table>
<br />


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
	if (!provjeri('broj_dosjea')) return false;

	// Da li je broj dosjea pozitivan broj?
	var nesto = document.getElementById('broj_dosjea');
	if (parseInt(nesto.value) < 1) {
		alert ("Broj dosjea mora biti veći od nule.");
		nesto.focus();
		self.scrollTo(nesto.offsetLeft, nesto.offsetTop);
		return false;
	}

	var nesto = document.getElementsByName('_lv_column_kanton');
	if (nesto[0].value=='-1') {
		alert("Niste izabrali kanton");
		nesto[0].focus();
		self.scrollTo(nesto[0].offsetLeft,nesto[0].offsetTop);
		return false;
	}

	var nesto = document.getElementById('studij_prvi_izbor');
	if (nesto.value=='') {
		alert("Niste izabrali odsjek");
		nesto.focus();
		self.scrollTo(nesto.offsetLeft,nesto.offsetTop);
		return false;
	}

	document.getElementsByName('glavnaforma')[0].submit();
	return true;
}

</script>


<?



// UNOS OCJENA IZ SREDNJE SKOLE

// Unos ocjena ce se prikazati samo prilikom editovanja, posto su ocjene u zasebnoj tabeli koja se vezuje 
// za osobu, na taj nacin se osigurava da osoba postoji prilikom editovanja.
// Ukoliko bismo se oslonili na prvi slobodan ID u tabeli osoba, postojala bi mogucnost da dva korisnika 
// unose ocjene za istu osobu (ta bi osoba mogla biti registrovana pod novim IDom ali ne bi imala ocjene,
// dok bi druga osoba imala pogresne ocjene)


if ($vrstaunosa!="editovanje") {

	?>
	</form>
	<p><font color="#FF0000">*</font> - Sva polja označena zvjezdicom su obavezna.<br/>
	<input type="hidden" name="unosocjena" value="1">
	<input type="button" value="Unos ocjena" onclick="provjeri_sve()"></p>
	
	<p>&nbsp;</p>
	<?


} else if ($ciklus_studija==1) { // Ocjene iz srednje škole

	?>
	<b>Ocjene iz srednje škole:</b><br/>
	
	<?

	// AJAH i prateće funkcije

	print ajah_box();

	?>
	<SCRIPT language="JavaScript">
	// Funkcija koja racuna bodove za opci uspjeh i kljucne predmete
	function izracunaj_bodove() {
		// Opci uspjeh
		var sumaocjena=0, brojocjena=0;
		for (i=1; i<=20; i++) {
			for (j=1; j<=4; j++) {
				var id = <?=$osoba?>*1000 + j*100 + i;
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
				var id = <?=$osoba?>*1000 + j*100 + i+90;
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
		var osoba = Math.floor(id/1000);
		var razred = Math.floor((id-osoba*1000)/100);
		var tipocjene = id-osoba*1000-razred*100;
		var rednibroj = 0;
		if (tipocjene>=90) { tipocjene -= 90; rednibroj=0; }
		else { rednibroj=tipocjene; tipocjene=0; }

		if (vrijednost == "") {
			vrijednost="/";
		}
		if (origval[id]=="") origval[id]="/";
		if (vrijednost != "/" && (!parseInt(vrijednost) || parseInt(vrijednost)<1 || parseInt(vrijednost)>5)) {
			alert("Neispravna ocjena: "+vrijednost+" !\nOcjena mora biti u opsegu 1-5 ili znak / za poništavanje "+id);
			element.value = origval[id];
			if (origval[id]=="/") element.value="";
			element.focus();
			element.select();
			return false;
		}
		if (zamger_ajah_sending) {
			element.focus();
			element.select();
			return false;
		}
		if (origval[id]=="/" && vrijednost!="/")
			ajah_start("index.php?c=N&sta=common/ajah&akcija=prijemni_ocjene&osoba="+osoba+"&nova="+vrijednost+"&subakcija=dodaj&razred="+razred+"&tipocjene="+tipocjene+"&rednibroj="+rednibroj,"document.getElementById('prijemniocjene'+"+id+").focus()");
		else if (origval[id]!="/" && vrijednost=="/")
			ajah_start("index.php?c=N&sta=common/ajah&akcija=prijemni_ocjene&osoba="+osoba+"&stara="+origval[id]+"&subakcija=obrisi&razred="+razred+"&tipocjene="+tipocjene+"&rednibroj="+rednibroj,"document.getElementById('prijemniocjene'+"+id+").focus()");
		else if (origval[id]!=vrijednost)
			ajah_start("index.php?c=N&sta=common/ajah&akcija=prijemni_ocjene&osoba="+osoba+"&nova="+vrijednost+"&stara="+origval[id]+"&subakcija=izmijeni&razred="+razred+"&tipocjene="+tipocjene+"&rednibroj="+rednibroj,"document.getElementById('prijemniocjene'+"+id+").focus()");

		origval[id]=vrijednost;

		izracunaj_bodove();
	}

	function enterhack2(element,e,gdje) {
		if(e.keyCode==13) {
			element.blur();
			document.getElementById('prijemniocjene'+gdje).focus();
			document.getElementById('prijemniocjene'+gdje).select();
			return false;
		}
	}
	var origval=new Array();
	</SCRIPT>

	<table border="0" cellspacing="0" cellpadding="1">
	<tr><td valign="top">

	<table border="0" cellspacing="0" cellpadding="1">
		<tr><td>&nbsp;</td><td align="center"><b> I </b></td><td align="center"><b> II </b></td><td align="center"><b> III </b></td><td align="center"><b> IV </b></td></tr>
	<?

	$q = myquery("SELECT razred, ocjena, tipocjene,redni_broj FROM srednja_ocjene WHERE osoba=$osoba");
	$razred = array();
	$kljucni = array();
	while ($r = mysql_fetch_row($q)) {
		if ($r[2]==0 && $r[3]==0) $razred[$r[0]][]= $r[1];
		else if ($r[2]==0) $razred[$r[0]][$r[3]]= $r[1];
		else $kljucni[$r[0]][$r[2]]=$r[1];
	}

	for ($i=1; $i<=20; $i++) {
		?>
		<tr><td align="right"><?=$i?>.</td>
		<?
		for ($j=1; $j<=4; $j++) {
			$id = $osoba*1000 + $j*100 + $i;
			if ($i<=19) $nextid = $id+1;
			else if ($j<4) $nextid = $osoba*1000 + ($j+1)*100 + $i;
			else $nextid=$osoba*1000 + 100 + 91;
			if (is_array($razred[$j]) && array_key_exists($i, $razred[$j]))
				$vr = $razred[$j][$i];
			else
				$vr = "";
			?>
			<SCRIPT language="JavaScript"> origval[<?=$id?>]='<?=$vr?>'</SCRIPT>
			<td align="center"><input type="text" id="prijemniocjene<?=$id?>" size="4" value="<?=$vr?>" style="border:1px black solid" onblur="izgubio_focus(this)" onfocus="dobio_focus(this)" onkeydown="return enterhack2(this,event,<?=$nextid?>)"></td>
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
			$id = $osoba*1000 + $j*100 + $i+90;
			if (is_array($kljucni[$j]) && array_key_exists($i, $kljucni[$j]))
				$vr=$kljucni[$j][$i];
			else $vr = "";
			if ($j<4) $nextid = $osoba*1000 + ($j+1)*100 + $i+90;
			else if ($i==1) $nextid = $osoba*1000 + 100 + $i+90+1;
			else if ($i==2) $nextid = $osoba*1000 + 3*100 + $i+90+1;
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


<!-- Provjera zajedno sa bodovima -->

<SCRIPT language="JavaScript">
function provjeri_sve_bodovi() {
	var nesto = document.getElementById('opci_uspjeh');
	if (parseInt(nesto.value)==0) {
		alert("Opći uspjeh je nula!");
		nesto.focus();
		return false;
	}
	var nesto = document.getElementById('kljucni_predmeti');
	if (parseInt(nesto.value)==0) {
		alert("Bodovi za ključne predmete su nula!");
		nesto.focus();
		return false;
	}
	return provjeri_sve();
}

</script>


<p><font color="#FF0000">*</font> - Sva polja označena zvjezdicom su obavezna.<br/>
<input type="button" value="Snimi" onclick="provjeri_sve_bodovi()"></p>

<p>&nbsp;</p>


<?


} // else if ($ciklus_studija==1)


else {

// Unos ocjena sa prethodnog ciklusa studija


	?>
	<b>Ocjene iz prethodnog ciklusa studija:</b><br/><br/>
	 
<table border="0" cellpadding="3" cellspacing="0">
	<tr>
		<td width="250" align="left">Broj semestara na prethodnom ciklusu:</td>
		<td><input maxlength="50" size="5" name="broj_semestara" id="broj_semestara" type="text" class="default" <? 
		if ($ebrojsem) {
			?> value="<?=$ebrojsem?>"<? 
		} else {
			?> style="background-color:#FFFF00" oninput="odzuti(this)" <? 
		} 
		?> autocomplete="off"><font color="#FF0000">*</font></td>
	</tr>
	<?

	// AJAH i prateće funkcije

	print ajah_box();

	?>
	<SCRIPT language="JavaScript">
	// Funkcija koja racuna bodove za prethodni ciklus studija
	function izracunaj_bodove() {

		// Bodovi se racunaju po formuli:
		// suma(ocjena*ects) / suma(ects) / brojsemestara
		var sumabodovi=0, sumaects=0;
		var brojsemestara=parseInt(document.getElementById('broj_semestara').value);
		if (brojsemestara==0) {
			document.getElementById('opci_uspjeh').value = "0";
			return; // ne mozemo dijeliti s nulom
		}

		for (i=1; i<=40; i++) {
			var idoc=2*i-1;
			var idec=2*i;
			var ocjena = document.getElementById('prijemniocjene'+idoc).value;
			var ects = document.getElementById('prijemniocjene'+idec).value;
			if (ocjena != "/" && ocjena != "" && ects != "/" && ects != "") {
				sumabodovi += parseInt(ocjena)*parseFloat(ects);
				sumaects += parseFloat(ects);
			}
		}

		if (sumaects==0) {
			document.getElementById('opci_uspjeh').value = "0";
			return; // ne mozemo dijeliti s nulom
		}

		alert("sumabodovi: "+sumabodovi+" sumaects: "+sumaects+" brojsemestara: "+brojsemestara);

		var rezultat = sumabodovi / sumaects / brojsemestara;
		document.getElementById('opci_uspjeh').value = rezultat;
	}

	function dobio_focus(element) {
		element.style.borderColor='red';
	}

	function izgubio_focus(element) {
		element.style.borderColor='black';

		var vrijednost = element.value;
		var id = parseInt(element.id.substr(14));
		var rednibroj = Math.ceil(id/2);
		var osoba = <?=$osoba?>;
		if (vrijednost == "") {
			vrijednost="/";
		}

		if (vrijednost==origval[id]) return true; // ne radi nista ako nije promijenjeno

		// Blokiraj ako je slanje u toku
		if (zamger_ajah_sending) {
			element.focus();
			element.select();
			return false;
		}

		if (id%2==1) { // Ocjena

			// Provjera ispravnosti
			if (vrijednost != "/" && (!parseInt(vrijednost) || parseInt(vrijednost)<6 || parseInt(vrijednost)>10)) {
				alert("Neispravna ocjena: "+vrijednost+" !\nOcjena mora biti u opsegu 6-10 ili znak / za poništavanje "+id);
				element.value = origval[id];
				if (origval[id]=="/") element.value="";
				element.focus();
				element.select();
				return false;
			}
			ajah_start("index.php?c=N&sta=common/ajah&akcija=prosli_ciklus_ocjena&osoba="+osoba+"&nova="+vrijednost+"&rednibroj="+rednibroj,"document.getElementById('prijemniocjene'+"+id+").focus()");

		} else { // ECTS
			if (vrijednost != "/" && (!parseFloat(vrijednost) || parseFloat(vrijednost)<=0 || parseFloat(vrijednost)>10)) {
				alert("Neispravan ECTS: "+vrijednost+" !\nECTS mora biti u opsegu 0-10 ili znak / za poništavanje "+id);
				element.value = origval[id];
				if (origval[id]=="/") element.value="";
				element.focus();
				element.select();
				return false;
			}
			ajah_start("index.php?c=N&sta=common/ajah&akcija=prosli_ciklus_ects&osoba="+osoba+"&nova="+vrijednost+"&rednibroj="+rednibroj,"document.getElementById('prijemniocjene'+"+id+").focus()");
		}

		origval[id]=vrijednost;

		izracunaj_bodove();
	}

	function enterhack2(element,e,gdje) {
		if(e.keyCode==13) {
			element.blur();
			document.getElementById('prijemniocjene'+gdje).focus();
			document.getElementById('prijemniocjene'+gdje).select();
			return false;
		}
	}
	var origval=new Array();
	</SCRIPT>

	<table border="0" cellspacing="0" cellpadding="1">
	<tr><td valign="top">

	<table border="0" cellspacing="0" cellpadding="1">
		<tr><td>Predmet&nbsp;</td><td align="center"><b>Ocjena</b></td><td align="center"><b>ECTS</b></td></td></tr>
	<?


	$q = myquery("SELECT ocjena, ects, redni_broj FROM prosliciklus_ocjene WHERE osoba=$osoba");
	$ocjene = $ects = array();
	while ($r = mysql_fetch_row($q)) {
		if ($r[2]==0) {
			$ocjene[] = $r[0];
			$ects[] = $r[1];
		} else {
			$ocjene[$r[2]] = $r[0];
			$ects[$r[2]] = $r[1];
		}
	}

	for ($i=1; $i<=20; $i++) {
		?>
		<tr><td align="right"><?=$i?>.</td>
		<SCRIPT language="JavaScript"> origval[<?=$i*2-1?>]='<?=$ocjene[$i]?>'</SCRIPT>
		<td align="center"><input type="text" id="prijemniocjene<?=$i*2-1?>" size="4" value="<?=$ocjene[$i]?>" style="border:1px black solid" onblur="izgubio_focus(this)" onfocus="dobio_focus(this)" onkeydown="return enterhack2(this,event,<?=($i*2)?>)"></td>
		<SCRIPT language="JavaScript"> origval[<?=$i*2?>]='<?=$ects[$i]?>'</SCRIPT>
		<td align="center"><input type="text" id="prijemniocjene<?=$i*2?>" size="4" value="<?=$ects[$i]?>" style="border:1px black solid" onblur="izgubio_focus(this)" onfocus="dobio_focus(this)" onkeydown="return enterhack2(this,event,<?=($i*2+1)?>)"></td>
		</tr><?
	}
	?>
	</table>

	</td><td width="30">&nbsp;</td>
	<td valign="top">

	<table border="0" cellspacing="0" cellpadding="1">
		<tr><td>Predmet&nbsp;</td><td align="center"><b>Ocjena</b></td><td align="center"><b>ECTS</b></td></td></tr>
	<?
	for ($i=21; $i<=40; $i++) {
		?>
		<tr><td align="right"><?=$i?>.</td>
		<td align="center"><input type="text" id="prijemniocjene<?=$i*2-1?>" size="4" value="<?=$ocjene[$i]?>" style="border:1px black solid" onblur="izgubio_focus(this)" onfocus="dobio_focus(this)" onkeydown="return enterhack2(this,event,<?=($i*2)?>)"></td>
		<td align="center"><input type="text" id="prijemniocjene<?=$i*2?>" size="4" value="<?=$ects[$i]?>" style="border:1px black solid" onblur="izgubio_focus(this)" onfocus="dobio_focus(this)" onkeydown="return enterhack2(this,event,<?=($i*2+1)?>)"></td>
		</tr><?
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
		<td align="left">Prethodni ciklus: 
		<input maxlength="20" size="10" name="opci_uspjeh" id="opci_uspjeh" type="text" value="<?=$eopci?>"><font color="#FF0000">*</font></td>
		<td align="left">Dodatni bodovi:
		<input maxlength="20" size="10" name="dodatni_bodovi" type="text" value="<?=$edodatni?>"></td>
	</tr>
</table>
</fieldset>
</form>


<!-- Provjera zajedno sa bodovima -->

<SCRIPT language="JavaScript">
function provjeri_sve_bodovi() {
	var nesto = document.getElementById('opci_uspjeh');
	if (parseInt(nesto.value)==0) {
		alert("Opći uspjeh je nula!");
		nesto.focus();
		return false;
	}
	var nesto = document.getElementById('broj_semestara');
	if (parseInt(nesto.value)==0) {
		alert("Broj semestara je nula!");
		nesto.focus();
		return false;
	}
	return provjeri_sve();
}

</script>


<p><font color="#FF0000">*</font> - Sva polja označena zvjezdicom su obavezna.<br/>
<input type="button" value="Snimi" onclick="provjeri_sve_bodovi()"></p>

<p>&nbsp;</p>


<?

} // if ($vrstaunosa) ... else if ... else {




} // ne znam od cega je ovo?



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