<?

// STUDENTSKA/IZVJESTAJI - izvjestaji koji se ticu prolaznosti

// v3.9.1.0 (2008/02/19) + Preimenovan bivsi admin_nihada
// v3.9.1.0 (2008/09/08) + Polje aktuelna u tabeli akademska_godina
// v3.9.1.0 (2008/09/09) + Dodan izvjestaj "studenti kojima nedostaje..."



function studentska_izvjestaji() {

global $userid,$user_siteadmin,$user_studentska;


// Provjera privilegija

if (!$user_studentska && !$user_siteadmin) {
	zamgerlog("nije studentska",3); // 3: error
	biguglyerror("Pristup nije dozvoljen.");
	return;
}



?>
<p><h3>Studentska služba - Izvještaj o prolaznosti</h3></p>

<?




	?>

<p>Najčešći izvještaji:
<ul>
<li><a href="?sta=izvjestaj/granicni&polozili=1">Spisak studenata koji su dali uslove za upis u sljedeći semestar</a></li>
<li><a href="?sta=izvjestaj/granicni&parcijalnih=1&predmeta=1">Granični slučajevi - po studiju</a></li>
<li><a href="?sta=izvjestaj/granicni&parcijalnih=1&predmeta=1&sort=predmet">Granični slučajevi - po predmetu</a></li>
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
		<form action="index.php" method="GET" name="studijForm">
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
		Studij: <?=db_dropdown("studij")?><br/><br/>
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
		NAPOMENA: Zbog kompleksnosti izvještaja, izračunavanje podataka za pojedinačne studente može trajati do par minuta.<br/>
		Sortiraj spisak po: 
		<input type="radio" name="sortiranje" value="0" CHECKED> Prezimenu
		<input type="radio" name="sortiranje" value="1"> Broju položenih ispita i bodova<br/><br/>
		
		<input type="submit" value=" Prikaži "></form>

	<?



}

?>
