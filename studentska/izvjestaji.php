<?

// STUDENTSKA/IZVJESTAJI - izvjestaji koji se ticu prolaznosti

// v3.9.1.0 (2008/02/19) + Preimenovan bivsi admin_nihada
// v3.9.1.1 (2008/09/08) + Polje aktuelna u tabeli akademska_godina
// v3.9.1.2 (2008/09/09) + Dodan izvjestaj "studenti kojima nedostaje..."
// v3.9.1.3 (2008/09/23) + Dodana opcija "Svi studiji" i sortiranje po broju indeksa
// v3.9.1.4 (2009/01/26) + Dodan overlay za prikaz izvjestaja
// v3.9.1.5 (2009/02/07) + Dodan link za izvjestaj "genijalci"
// v4.0.0.0 (2009/02/19) + Release
// v4.0.9.1 (2009/05/20) + Apsolutni linkovi na slike promijenjeni u relativne
// v4.0.9.2 (2009/08/28) + Razjasnjeni linkovi na rang-listu po prosjeku



function studentska_izvjestaji() {

global $userid,$user_siteadmin,$user_studentska;


// Provjera privilegija

if (!$user_studentska && !$user_siteadmin) {
	zamgerlog("nije studentska",3); // 3: error
	biguglyerror("Pristup nije dozvoljen.");
	return;
}



// Kada se klikne na generisanje izvještaja, biće zasivljen ekran i prikazan prozor koji
// obavjestava da je u toku generisanje izvještaja.
// Razlog: Nihada (among else) ima običaj da klika na link sve dok se stranica ne otvori,
// što ne samo da nema efekta nego i opterećuje server


?>
<p><h3>Studentska služba - Izvještaj o prolaznosti</h3></p>


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
	document.getElementById('prekrivac').style.display="inline";
	document.getElementById('obavijest').style.display="inline";
	document.getElementById('obavijest').style.top=myheight/2-25;
	document.getElementById('obavijest').style.left=mywidth/2-150;

	//alert("Hello");
	return true;
}
</script>

<img src="images/blur.gif" width="1" height="1" border="0"> <!-- preloading -->

<div id="prekrivac" name="prekrivac" style="display:none; position: absolute; left: 0px; top: 55px">
<table width="1024" height="900" border="0" cellspacing="0" cellpadding="0"><tr><td background="images/blur.gif" align="center" valign="center">
&nbsp;
</td></tr></table>
</div>

<div id="obavijest" name="obavijest" style="display:none; position: absolute; left: 0px; top: 55px">
<table width="300" height="50" border="1" cellspacing="0" cellpadding="0"><tr><td align="center" valign="center" width="50"  bgcolor="#DDDDDD"><img src="images/Animated-Hourglass.gif" width="38" height="38"></td><td align="center" valign="center" bgcolor="#DDDDDD">U toku je kreiranje izvještaja<br>Molimo sačekajte</td></tr></table>
</div>


<p>Najčešći izvještaji:
<ul>
<li><a href="?sta=izvjestaj/granicni&polozili=1" onclick="izvjestaj();">Spisak studenata koji su dali uslove za upis u sljedeći semestar</a></li>
<li><a href="?sta=izvjestaj/granicni&parcijalni=0&predmet=1&akademska_godina=4" onclick="izvjestaj();">Granični slučajevi - po studiju</a></li>
<li><a href="?sta=izvjestaj/granicni&parcijalni=0&predmet=1&sort=predmet&akademska_godina=4" onclick="izvjestaj();">Granični slučajevi - po predmetu</a></li>
<li><a href="?sta=izvjestaj/granicni&predmet=1&akademska_godina=4" onclick="izvjestaj();">Studenti kojima fali 1 predmet - 2008/2009</a></li>
<li><a href="?sta=izvjestaj/granicni&predmet=2&akademska_godina=4" onclick="izvjestaj();">Studenti kojima fale 2 predmeta - 2008/2009</a></li>
<li><a href="?sta=izvjestaj/prolaznosttab" onclick="izvjestaj();">Tabelarni pregled prolaznosti</a></li>
<li><a href="?sta=izvjestaj/pregled" onclick="izvjestaj();">Pregled upisanih studenata u školsku 2009/10 godinu</a></li>
<li>Spiskovi studenata po prosječnoj ocjeni (svi studiji, prosjek 8,0 i više, dat uslov):<br />
- <a href="?sta=izvjestaj/genijalci&akademska_godina=4&limit_prosjek=8&studij=0&godina_studija=1&limit_ects=22" onclick="izvjestaj()";>prva godina</a><br />
- <a href="?sta=izvjestaj/genijalci&akademska_godina=4&limit_prosjek=8&studij=0&godina_studija=2&limit_ects=22" onclick="izvjestaj();">druga godina</a><br />
- <a href="?sta=izvjestaj/genijalci&akademska_godina=4&limit_prosjek=8&studij=0&godina_studija=3&limit_ects=22" onclick="izvjestaj();">treća godina</a></li>
</ul></p>

<hr/>

<p><h3>Kreirajte vlastiti izvještaj:</h3></p>


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
	<table width="500" border="0"><tr><td align="left">
		<form action="index.php" method="GET" name="studijForm" onsubmit="return izvjestaj();">
		<input type="hidden" name="sta" value="izvjestaj/prolaznost">
		Akademska godina: <select name="_lv_column_akademska_godina">
		<?
			$q500 = mysql_query("select id,naziv,aktuelna from akademska_godina order by naziv desc");
			while ($r500 = mysql_fetch_row($q500)) {
				print "<option value=\"$r500[0]\"";
				if ($r500[2]==1) print " selected";
				print ">$r500[1]</option>\n";
			}
		?>
		</select><br/><br/>
		Studij: <?=db_dropdown("studij",0,"Svi studiji")?><br/><br/>
		<input type="radio" name="period" value="0" CHECKED> Semestar: <input type="text" name="semestar" size="5" onclick="setCheckedValue(document.forms['studijForm'].elements['period'], '0');">&nbsp;
		<input type="radio" name="period" value="1"> Cijela godina: <input type="text" name="godina" size="5" onclick="setCheckedValue(document.forms['studijForm'].elements['period'], '1');"><br/><br/>

		Statistika za:<br/>
		<input type="radio" name="ispit" value="1" CHECKED> I parcijalni&nbsp;
		<input type="radio" name="ispit" value="2"> II parcijalni&nbsp;
		<input type="radio" name="ispit" value="3"> Ukupan broj bodova&nbsp;
		<input type="radio" name="ispit" value="4"> Konačna ocjena<br/><br/>

		Studenti:<br/>
		<input type="radio" name="cista_gen" value="0" CHECKED> Svi studenti (uključujući ponovce i one koji su prenijeli predmete)<br/>
		<input type="radio" name="cista_gen" value="1"> Svi koji slušaju godinu (uključujući ponovce, ali bez prenijetih predmeta)<br/>
		<input type="radio" name="cista_gen" value="2"> Bez ponovaca<br/>
		<input type="radio" name="cista_gen" value="3"> Čista generacija (studenti koji nemaju ponovljenih godina ni prenesenih predmeta)<br/><br/>

		<input type="checkbox" name="studenti" value="1"> Prikaži podatke za svakog pojedinačnog studenta<br/>
		NAPOMENA: Zbog kompleksnosti izvještaja, izračunavanje podataka za pojedinačne studente može trajati do par minuta.<br/><br/>
		Sortiraj spisak po: <ul>
		<input type="radio" name="sortiranje" value="0" CHECKED> Prezimenu<br/>
		<input type="radio" name="sortiranje" value="1"> Broju položenih ispita i bodova<br/>
		<input type="radio" name="sortiranje" value="2"> Broju indeksa</ul>
		
		<input type="submit" value=" Prikaži "></form>

	<?



}

?>
