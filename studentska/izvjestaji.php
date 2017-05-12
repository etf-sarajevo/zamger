<?

// STUDENTSKA/IZVJESTAJI - izvjestaji koji se ticu prolaznosti



function studentska_izvjestaji() {

global $userid,$user_siteadmin,$user_studentska;


require_once("lib/formgen.php"); // db_dropdown

// Provjera privilegija

if (!$user_studentska && !$user_siteadmin) {
	zamgerlog("nije studentska",3); // 3: error
	zamgerlog2("nije studentska"); // 3: error
	biguglyerror("Pristup nije dozvoljen.");
	return;
}



// Kada se klikne na generisanje izvještaja, biće zasivljen ekran i prikazan prozor koji
// obavjestava da je u toku generisanje izvještaja.
// Razlog: Nihada (among else) ima običaj da klika na link sve dok se stranica ne otvori,
// što ne samo da nema efekta nego i opterećuje server


?>
<p><h3>Studentska služba - Izvještaji</h3></p>


<script language="JavaScript">

var mywidth,myheight;
if (window.innerWidth && window.innerHeight) {
	mywidth=window.innerWidth;
	myheight=window.innerHeight;
} else if (document.body.clientWidth && document.body.clientHeight) {
	mywidth=document.body.clientWidth;
	myheight=document.body.clientHeight;
}

function izvjestaj() {
	var n = ""; // Stupid hack for stupid specification
	document.getElementById('prekrivac').style.width = n.concat(mywidth, "px");
	document.getElementById('prekrivac').style.height = n.concat(myheight, "px");
	document.getElementById('prekrivac').style.display="inline";

	document.getElementById('obavijest').style.top = n.concat(myheight/2-25, "px");
	document.getElementById('obavijest').style.left = n.concat(mywidth/2-150, "px");
	document.getElementById('obavijest').style.display="inline";

	//alert(n);
	return true;
}
</script>

<img src="static/images/blur.gif" width="1" height="1" border="0"> <!-- preloading -->

<div id="prekrivac" name="prekrivac" style="display:none; position: absolute; left: 0px; top: 55px; background-image:url('static/images/blur.gif'); background-repeat:repeat;">
</div>

<div id="obavijest" name="obavijest" style="display:none; position: absolute; left: 0px; top: 55px">
<table width="300" height="50" border="1" cellspacing="0" cellpadding="0"><tr><td align="center" valign="center" width="50"  bgcolor="#DDDDDD"><img src="static/images/Animated-Hourglass.gif" width="38" height="38"></td><td align="center" valign="center" bgcolor="#DDDDDD">U toku je kreiranje izvještaja<br>Molimo sačekajte</td></tr></table>
</div>

<?


// Razne forme za pojedinačne izvještaje

if (param('akcija') == "po_prosjeku") {
	?>
	<h3>Spiskovi studenata po prosječnoj ocjeni</h3>

	<form action="index.php" method="GET" name="studijForm" onsubmit="return izvjestaj();">
	<input type="hidden" name="sta" value="izvjestaj/genijalci">
	<table border="0">
		<tr><td>Akademska godina:</td><td><select name="akademska_godina">
		<?
			$q500 = db_query("select id,naziv,aktuelna from akademska_godina order by naziv desc");
			while ($r500 = db_fetch_row($q500)) {
				print "<option value=\"$r500[0]\"";
				if ($r500[2]==1) print " selected";
				print ">$r500[1]</option>\n";
			}
		?>
		</select></td></tr>
		<tr><td>Studij:</td><td><select name="studij">
			<option value="-1">Svi studiji (BSc)</option>
			<option value="-2">Svi studiji (MSc)</option>
			<option value="-3">Svi studiji (MSc bez BSca)</option>
			<?
				$q505 = db_query("select id, naziv from studij where moguc_upis=1 order by naziv");
				while ($r505 = db_fetch_row($q505)) {
					print "<option value=\"$r505[0]\">$r505[1]</option>\n";
				}
		?></select></td></tr>
		<tr><td>Godina:</td><td><input type="text" name="godina_studija" size="5" value="1"></td></tr>
		<tr><td>Limit prosjeka:</td><td><input type="text" name="limit_prosjek" value="8.0"></td></tr>
		<tr><td>Maksimalan broj<br>nepoloženih predmeta:</td><td><input type="text" name="limit_predmet" value="1"></td></tr>
		<tr><td colspan="2"><input type="radio" name="samo_tekuca_gs" value="da" checked> Prosjek samo za odabranu godinu studija<br>
		<input type="radio" name="samo_tekuca_gs" value="ne"> Prosjek za odabrani studij</td></tr>
	</table>
	<input type="submit" value=" Prikaži "></form>
	<?
}

if (param('akcija') == "po_nepolozenim") {
	?>
	<h3>Spiskovi studenata po broju nepoloženih predmeta (GRANIČNI SLUČAJEVI)</h3>

	<form action="index.php" method="GET" name="studijForm" onsubmit="return izvjestaj();">
	<input type="hidden" name="sta" value="izvjestaj/granicni">
	<input type="hidden" name="varijanta" value="1">
	<table border="0">
		<tr><td>Akademska godina:</td><td><select name="akademska_godina">
		<?
			$q500 = db_query("select id,naziv,aktuelna from akademska_godina order by naziv desc");
			while ($r500 = db_fetch_row($q500)) {
				print "<option value=\"$r500[0]\"";
				if ($r500[2]==1) print " selected";
				print ">$r500[1]</option>\n";
			}
		?>
		</select></td></tr>
		<tr>
		<td colspan="2">&nbsp;<br><input type="radio" name="studij_godina" value="svi" onclick="javascript:document.getElementById('studij').disabled=true; document.getElementById('godina_studija').disabled=true;" checked> Svi studiji i godine<br>
		<input type="radio" name="studij_godina" value="izbor" onclick="javascript:document.getElementById('studij').disabled=false; document.getElementById('godina_studija').disabled=false;"> Izabrani studij i godina:<br>
		Studij: 
		<select name="studij" id="studij" disabled>
			<option value="-1">Svi studiji (BSc)</option>
			<option value="-2">Svi studiji (MSc)</option>
			<?
				$q505 = db_query("select id, naziv from studij where moguc_upis=1 order by naziv");
				while ($r505 = db_fetch_row($q505)) {
					print "<option value=\"$r505[0]\">$r505[1]</option>\n";
				}
		?></select>
		Godina:
		<input type="text" name="godina_studija" id="godina_studija" size="5" value="1" disabled><br>&nbsp;
		</td></tr>
		<tr><td colspan="2">&nbsp;Broj nepoloženih ispita:<br>&nbsp;<br><input type="radio" name="vrste_granicnih" value="sve" onclick="javascript:document.getElementById('predmeta').disabled=true; document.getElementById('parcijalnih').disabled=true; document.getElementById('douslova').disabled=true;" checked> Sve vrste graničnih slučajeva<br>
		<input type="radio" name="vrste_granicnih" value="izbor" onclick="javascript:document.getElementById('predmeta').disabled=false; document.getElementById('parcijalnih').disabled=false; document.getElementById('douslova').disabled=false;"> Samo studenti koji imaju nepoloženih:<br>
		<input type="text" name="predmeta" id="predmeta" size="5" value="1" disabled> čitavih predmeta i <input type="text" name="parcijalnih" id="parcijalnih" size="5" value="1" disabled> parcijalnih ispita &nbsp;
		<input type="checkbox" name="douslova" id="douslova" disabled> Do uslova<br>&nbsp;
		</td></tr>
		<tr><td>Prikaži podatke:</td><td>
		<input type="radio" name="prikaz" value="sumarno" checked> Sumarno
		<input type="radio" name="prikaz" value="po_predmetu"> Grupisano po predmetu
		<input type="radio" name="prikaz" value="po_studiju"> Grupisano po studiju

		</td></tr>
	</table>
<br>&nbsp;<br>
	<input type="submit" value=" Prikaži "></form>
	<?
}


if (param('akcija') == "prolaznost") {

	?>
	<p><h3>Prolaznost studenata na predmetima</h3></p>


	<script type="text/javascript">
	function setCheckedValue(radioObj, newValue) {
		if(!radioObj)
			return;
		var radioLength = radioObj.length;
		if(radioLength == undefined) {
			radioObj.checked = (radioObj.value == newValue.toString());
			return;
		}
		for(var i = 0; i < radioLength; i++) {
			radioObj[i].checked = false;
			if(radioObj[i].value == newValue.toString()) {
				radioObj[i].checked = true;
			}
		}
	}
	</script>

	<table width="550" border="0"><tr><td align="left">
		<form action="index.php" method="GET" name="studijForm" onsubmit="return izvjestaj();">
		<input type="hidden" name="sta" value="izvjestaj/prolaznost">
		Akademska godina: <select name="_lv_column_akademska_godina">
		<?
			$q500 = db_query("select id,naziv,aktuelna from akademska_godina order by naziv desc");
			while ($r500 = db_fetch_row($q500)) {
				print "<option value=\"$r500[0]\"";
				if ($r500[2]==1) print " selected";
				print ">$r500[1]</option>\n";
			}
		?>
		</select><br/><br/>
		Studij: <select name="_lv_column_studij"><option value="-1">Prva godina studija</option><?
			$q505 = db_query("select id, naziv from studij where moguc_upis=1 order by naziv");
			while ($r505 = db_fetch_row($q505)) {
				print "<option value=\"$r505[0]\">$r505[1]</option>\n";
			}
		?></select><br/><br/>
		<input type="radio" name="period" value="0" CHECKED> Semestar: <input type="text" name="semestar" size="5" onclick="setCheckedValue(document.forms['studijForm'].elements['period'], '0');">&nbsp;
		<input type="radio" name="period" value="1"> Cijela godina: <input type="text" name="godina" size="5" onclick="setCheckedValue(document.forms['studijForm'].elements['period'], '1');"><br/><br/>

		Statistika za:<br/>
		<input type="radio" name="ispit" value="1" CHECKED> I parcijalni&nbsp;
		<input type="radio" name="ispit" value="2"> II parcijalni&nbsp;
		<input type="radio" name="ispit" value="3"> Ukupan broj bodova&nbsp;
		<input type="radio" name="ispit" value="4"> Konačna ocjena&nbsp;
		<input type="radio" name="ispit" value="5"> Uslov
		<br/><br/>

		Studenti:<br/>
		<input type="radio" name="cista_gen" value="0" CHECKED> Svi studenti (uključujući ponovce i one koji su prenijeli predmete)<br/>
		<input type="radio" name="cista_gen" value="1"> Svi koji slušaju godinu (uključujući ponovce, ali bez prenijetih predmeta)<br/>
		<input type="radio" name="cista_gen" value="2"> Bez ponovaca<br/>
		<input type="radio" name="cista_gen" value="3"> Čista generacija (studenti koji nemaju ponovljenih godina ni prenesenih predmeta)<br/>
		<input type="radio" name="cista_gen" value="4"> Samo ponovci<br/><br/>

		<input type="checkbox" name="studenti" value="1"> Prikaži podatke za svakog pojedinačnog studenta<br/>
		NAPOMENA: Zbog kompleksnosti izvještaja, izračunavanje podataka za pojedinačne studente može trajati do par minuta.<br/><br/>
		Sortiraj spisak po: <ul>
		<input type="radio" name="sortiranje" value="0" CHECKED> Prezimenu<br/>
		<input type="radio" name="sortiranje" value="1"> Broju položenih ispita i bodova<br/>
		<input type="radio" name="sortiranje" value="2"> Broju indeksa</ul>
		
		<input type="submit" value=" Prikaži "></form>

	<?
}


if (param('akcija') == "pregled") {

	?>
	<p><h3>Pregled broja upisanih studenata u aktuelnoj akademskoj godini</h3></p>
	<form action="index.php" method="GET" name="studijForm" onsubmit="return izvjestaj();">
	Tip izvještaja: <select name="sta">
	<option value="izvjestaj/pregled">Po tipu studiranja</option>
	<option value="izvjestaj/pregled_nacin">Po tipu i načinu studiranja</option>
	<option value="izvjestaj/po_kantonima">Po kantonima</option>
	</select><br><br>
	Akademska godina: <select name="akademska_godina">
	<?
		$q500 = db_query("select id,naziv,aktuelna from akademska_godina order by naziv desc");
		while ($r500 = db_fetch_row($q500)) {
			print "<option value=\"$r500[0]\"";
			if ($r500[2]==1) print " selected";
			print ">$r500[1]</option>\n";
		}
	?>
	</select><br/><br/>
	<input type="checkbox" name="po_semestrima"> Po semestrima<br><br>
	<input type="submit" value=" Prikaži "></form>
	<?
}


if (param('akcija') == "ugovoroucenju") {

	?>
	<p><h3>Detaljan broj studenata po predmetu u aktuelnoj akademskoj godini</h3></p>
	<p>Ukoliko upis u akademsku godinu nije završen, prikazuje se procjena broja studenata na osnovu popunjenih Ugovora o učenju i Zahtjeva za koliziju. Ova procjena je data pod sljedećim pretpostavkama:
	<ul><li>da se nijedan student neće ispisati sa fakulteta</li>
	<li>da će svi zahtjevi za promjenu odsjeka biti odobreni</li>
	<li>da će student koji u septembru položi pismeni ispit koji ranije nije položio vjerovatno položiti i završni ispit</li></ul>

	<form action="index.php" method="GET" name="studijForm" onsubmit="return izvjestaj();">
	<input type="hidden" name="sta" value="izvjestaj/ugovoroucenju">
	Akademska godina: <select name="akademska_godina">
	<?
		$q500 = db_query("select id,naziv,aktuelna from akademska_godina order by naziv desc");
		while ($r500 = db_fetch_row($q500)) {
			print "<option value=\"$r500[0]\"";
			if ($r500[2]==1) print " selected";
			print ">$r500[1]</option>\n";
		}
	?>
	</select><br/><br/>
	<input type="submit" value=" Prikaži "></form>
	<?
}

if (param('akcija') == "uspjesnost") {
	?>
	<h3>Uspješnost studenata i prosječno trajanje studija</h3>
	<form action="index.php" method="GET" name="studijForm" onsubmit="return izvjestaj();">
	<input type="hidden" name="sta" value="izvjestaj/uspjesnost">
	<p>Izaberite studij:<br>
	<?=db_dropdown("studij")?><br/><br/>
	<input type="submit" value=" Prikaži "></form>
	<?
}

if (param('akcija') == "svi_studenti") {
	?>
	<h3>Spisak svih studenata abecedno</h3>
	<form action="index.php" method="GET" name="studijForm" onsubmit="return izvjestaj();">
	<input type="hidden" name="sta" value="izvjestaj/svi_studenti">
	<p><input type="checkbox" name="ime_oca">Ime oca<br />
	<input type="checkbox" name="jmbg">JMBG<br />
	<input type="checkbox" name="nacin_studiranja">Način studiranja (redovni, samofinansirajući...)<br />
	<input type="checkbox" name="vanredni">Uključi i vanredne studente<br />
	<input type="checkbox" name="adresa_mjesto">Mjesto boravka<br />
	<input type="checkbox" name="login">Korisničko ime<br /><br />
	<input type="checkbox" name="tabelarno">Prikaži u obliku tabele umjesto numerisane liste<br />
	Akademska godina: <select name="ag">
	<?
		$q506 = db_query("select id, naziv, aktuelna from akademska_godina order by naziv");
		while ($r506 = db_fetch_row($q506)) {
			print "<option value=\"$r506[0]\"";
			if ($r506[2] == 1) print " selected";
			print ">$r506[1]</option>\n";
		}
	?></select><br />
	Studij: <select name="studij">
	<option value="0">Svi studiji</option>
	<option value="-1">Prvi ciklus</option>
	<option value="-2">Drugi ciklus</option>
	<option value="-3">Treći ciklus</option>
	<?
		$q505 = db_query("select id, naziv from studij order by naziv"); //TODO neke virtualne studije izostaviti?
		while ($r505 = db_fetch_row($q505)) {
			print "<option value=\"$r505[0]\">$r505[1]</option>\n";
		}
	?></select><br />
	Godina studija: <select name="godina"><option value="0">Sve godine</option><option value="1">1</option><option value="2">2</option><option value="3">3</option></select><br />
	<input type="checkbox" name="prvi_put">Prvi put (bez ponovaca)<br />
	
	<input type="submit" value=" Prikaži "></form>
	<?
}

// SPISAK IZVJEŠTAJA

?>


<hr/>

<p>Najčešći izvještaji:
<ul>
<li><a href="?sta=studentska/izvjestaji&amp;akcija=prolaznost">Prolaznost studenata po predmetima</a></li>
<li><a href="?sta=izvjestaj/prolaznosttab" onclick="return izvjestaj();">Tabelarni pregled prolaznosti (sumarno za sve godine)</a></li>
<li><a href="?sta=studentska/izvjestaji&amp;akcija=pregled">Pregled broja upisanih studenata u aktuelnoj akademskoj godini</a></li>
<li><a href="?sta=izvjestaj/ugovoroucenju" onclick="return izvjestaj();">Detaljan broj studenata po predmetu u aktuelnoj akademskoj godini i/ili Procjena za sljedeću akademsku godinu</a> - <a href="?sta=studentska/izvjestaji&akcija=ugovoroucenju">ranije akademske godine</a></li>
<li><a href="?sta=studentska/izvjestaji&amp;akcija=po_nepolozenim">Spisak studenata po broju nepoloženih predmeta (GRANIČNI SLUČAJEVI)</a></li>
<li><a href="?sta=studentska/izvjestaji&amp;akcija=po_prosjeku">Spisak studenata po prosječnoj ocjeni</a></li>
<li><a href="?sta=studentska/izvjestaji&amp;akcija=svi_studenti">Spisak svih studenata abecedno</a></li>
<li><a href="?sta=studentska/izvjestaji&amp;akcija=uspjesnost">Uspješnost studenata i prosječno trajanje studija</a></li>
</ul></p>

<hr/>
<?


}

?>
