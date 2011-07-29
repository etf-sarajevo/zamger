<?

// IZVJESTAJ/PROLAZNOST - Pregled prolaznosti i ocjena po godini, odsjeku...

// v3.9.1.0 (2008/04/21) + Kopiran admin_izvjestaj, dodana tabela student_predmet, komponente, izvjestaj "ukupan broj bodova" prebacen na komponente
// v3.9.1.1 (2008/04/24) + Dodano bojenje po odsjeku
// v3.9.1.2 (2008/08/28) + Tabela osoba umjesto auth
// v3.9.1.3 (2008/09/23) + Dodana opcija "Svi studiji" i sortiranje po broju indeksa
// v3.9.1.4 (2008/09/24) + Popravljen bug 26 - netacan broj studenata koji su upisali predmet (kod izvjestaja konacna ocjena)
// v3.9.1.5 (2008/10/24) + Popravljena ukupna statistika kod upita "Redovni + ponovci + preneseni"
// v4.0.0.0 (2009/02/19) + Release
// v4.0.9.1 (2009/03/31) + Tabela ispit preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.2 (2009/03/31) + Tabela konacna_ocjena preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.3 (2009/04/01) + Tabela zadaca preusmjerena sa ponudakursa na tabelu predmet


// TODO: Zašto ovo nije prebačeno na komponente?


function izvjestaj_test2() {


?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>
<?



$oldstudij=$oldsemestar=0;

// ($q30) Spisak predmeta na studij-semestru
$q30 = myquery("select pk.id, p.naziv, pk.obavezan, pk.semestar, p.id from predmet as p, ponudakursa as pk where pk.predmet=p.id and pk.akademska_godina=4 and (pk.semestar=1 or pk.semestar=2) order by pk.semestar, p.naziv");

// Ispisujemo samo sumarne podatke
while ($r30 = mysql_fetch_row($q30)) {
	$pk=$r30[0];	$semestar=$r30[3];
	$metapredmet = $r30[4];

	if ($metapredmet == $oldmetapredmet) continue;
	$oldmetapredmet=$metapredmet;

	// Zaglavlje tabele
	if ($semestar!=$oldsemestar) {
	/*	$q35 = myquery("select naziv from studij where id=$studij");

		if ($oldstudij!=0 && $oldsemestar!=0) print "</table>";*/

		?>
		<!--p><b>semestar</b></p-->
		<table border="1" cellspacing="0" cellpadding="2">
			<tr>
				<td rowspan="2"><b>Predmet</b></td>
				<td colspan="5" align="center"><b>2005/2006</b></td>
				<td colspan="5" align="center"><b>2006/2007</b></td>
				<td colspan="5" align="center"><b>2007/2008</b></td>
				<td colspan="5" align="center"><b>2008/2009</b></td>
			</tr>
			<tr>
				<!-- Predmet -->
				<!-- 2005/2006 -->
				<td><b>Kanton</b></td>
				<td><b>Samof</b></td>
				<td><b>REDOVNI</b></td>
				<td><b>Ponovci</b></td>
				<td bgcolor="#EEEEEE"><b>UKUPNO</b></td>
				<!-- 2006/2007 -->
				<td><b>Kanton</b></td>
				<td><b>Samof</b></td>
				<td><b>REDOVNI</b></td>
				<td><b>Ponovci</b></td>
				<td bgcolor="#EEEEEE"><b>UKUPNO</b></td>
				<!-- 2007/2008 -->
				<td><b>Kanton</b></td>
				<td><b>Samof</b></td>
				<td><b>REDOVNI</b></td>
				<td><b>Ponovci</b></td>
				<td bgcolor="#EEEEEE"><b>UKUPNO</b></td>
				<!-- 2008/2009 -->
				<td><b>Kanton</b></td>
				<td><b>Samof</b></td>
				<td><b>REDOVNI</b></td>
				<td><b>Ponovci</b></td>
				<td bgcolor="#EEEEEE"><b>UKUPNO</b></td>
			</tr><?
	}
	/*$oldstudij=$studij; */$oldsemestar=$semestar;


	$naziv = $r30[1];
	if ($r30[2]==0) $naziv .= " *";
	?><tr><td><?=$naziv?></td>
	<?



	global $polozio;
	$polozio = array(); // ne znam kako bez global :(
	global $suma_bodova;
	$suma_bodova = array();
	global $brindexa;
	$brindexa = array();

	// Akademska godina
	for ($akgod=1; $akgod<=4; $akgod++) {

		// Preskacemo zadnji semestar..
		if ($akgod==4 && $semestar%2==0) {
			?><td align="center" valign="center">/</td><td align="center" valign="center">/</td><td align="center" valign="center">/</td><td align="center" valign="center">/</td><td align="center" valign="center">/</td><?
			continue;
		}


		// ($q40) Upit za spisak studenata
		$q40 = myquery("select ss.student, ss.nacin_studiranja, ss.ponovac from student_predmet as sp, student_studij as ss, ponudakursa as pk where sp.predmet=pk.id and pk.predmet=$metapredmet and pk.akademska_godina=$akgod and sp.student=ss.student and ss.akademska_godina=$akgod and ss.semestar=$semestar");
		$brojuk = mysql_num_rows($q40);

		if ($brojuk==0) {
			?><td align="center" valign="center">/</td><td align="center" valign="center">/</td><td align="center" valign="center">/</td><td align="center" valign="center">/</td><td align="center" valign="center">/</td><?
			continue;
		}

		$poluk=$brojred=$polred=$brojsamof=$polsamof=$brojpon=$polpon=0;

		while ($r40=mysql_fetch_row($q40)) {
			$q50 = myquery("select count(*) from konacna_ocjena where student=$r40[0] and predmet=$metapredmet and akademska_godina=$akgod");
			$polozio = mysql_result($q50,0,0);
			if ($polozio) $poluk++;
			if ($r40[2]==1) {
				$brojpon++; if ($polozio) $polpon++;
			} else if ($r40[1]==1) {
				$brojred++; if ($polozio) $polred++;
			} else {
				$brojsamof++; if ($polozio) $polsamof++;
			}
		}

		$brojreduk = $brojred+$brojsamof;
		$polreduk=$polred+$polsamof;

		if ($brojred==0) print "<td align=\"center\" valign=\"center\">/</td>";
		else print "<td>$polred / $brojred<br>".procenat($polred,$brojred)."</td>";
		if ($brojsamof==0) print "<td align=\"center\" valign=\"center\">/</td>";
		else print "<td>$polsamof / $brojsamof<br>".procenat($polsamof,$brojsamof)."</td>";
		if ($brojreduk==0) print "<td align=\"center\" valign=\"center\">/</td>";
		else print "<td>$polreduk / $brojreduk<br>".procenat($polreduk,$brojreduk)."</td>";
		if ($brojpon==0) print "<td align=\"center\" valign=\"center\">/</td>";
		else print "<td>$polpon / $brojpon<br>".procenat($polpon,$brojpon)."</td>";
		print "<td bgcolor=\"#EEEEEE\">$poluk / $brojuk<br>".procenat($poluk,$brojuk)."</td>\n";
	}

	print "</tr>\n";

	
}


print "</table>\n* Predmet je izborni\n\n";


}
