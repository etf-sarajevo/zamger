<?

// COMMON/PROFIL + opcije korisnika


function common_profil() {

global $userid, $conf_system_auth, $conf_files_path, $conf_promjena_sifre, $conf_skr_naziv_institucije, $conf_skr_naziv_institucije_genitiv;
global $user_student, $user_nastavnik, $user_studentska, $user_siteadmin;


require_once("lib/formgen.php"); // db_dropdown

$akcija = $_REQUEST['akcija'];

// Ispis menija

$boja_licni = $boja_opcije = $boja_izbori = $boja_log = "#BBBBBB";
if ($akcija=="opcije") $boja_opcije="#DDDDDD";
else if ($akcija=="izbori") $boja_izbori="#DDDDDD";
else if ($akcija=="log") $boja_log="#DDDDDD";
else $boja_licni = "#DDDDDD";


// Za sada ne postoje dodatne mogućnosti ponuđene studentima

if ($user_nastavnik) {
	?>
	<br>
	
	<table border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td bgcolor="#FFFFFF" width="50" height="25">&nbsp;</td>
		<td bgcolor="<?=$boja_licni?>" width="200" align="center" onmouseover="this.style.backgroundColor='#FFFFFF';" onmouseout="this.style.backgroundColor='<?=$boja_licni?>';"><a href="?sta=common/profil&amp;akcija=licni">Lični podaci</a></td>
		<td bgcolor="#FFFFFF" width="50">&nbsp;</td>
		<td bgcolor="<?=$boja_opcije?>" width="200" align="center" onmouseover="this.style.backgroundColor='#FFFFFF';" onmouseout="this.style.backgroundColor='<?=$boja_opcije?>';"><a href="?sta=common/profil&amp;akcija=opcije">Zamger opcije</a></td>
		<td bgcolor="#FFFFFF" width="50">&nbsp;</td>
		<td bgcolor="<?=$boja_izbori?>" width="200" align="center" onmouseover="this.style.backgroundColor='#FFFFFF';" onmouseout="this.style.backgroundColor='<?=$boja_izbori?>';"><a href="?sta=common/profil&amp;akcija=izbori">Izbori i nastavni ansambl</a></td>
		<td bgcolor="#FFFFFF" width="100">&nbsp;</td>
		<td bgcolor="<?=$boja_log?>" width="200" align="center" onmouseover="this.style.backgroundColor='#FFFFFF';" onmouseout="this.style.backgroundColor='<?=$boja_log?>';"><a href="?sta=common/profil&amp;akcija=log">Log</a></td>
		<td bgcolor="#FFFFFF" width="100">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="9" height="1" bgcolor="#000000">
	</tr>
	</table>
	<?
}



// Zamger opcije

if ($akcija=="opcije") {
	if ($_REQUEST['subakcija'] == "promjena" && check_csrf_token()) {
		$csv_separator = $_REQUEST['csv-separator'];
		if ($csv_separator != ";" && $csv_separator != ",") $csv_separator = db_escape($csv_separator);

		$q500 = db_query("delete from preference where korisnik=$userid and preferenca='csv-separator'");
		$q510 = db_query("insert into preference set korisnik=$userid, preferenca='csv-separator', vrijednost='$csv_separator'");
		
		$savjet_dana = intval($_REQUEST['savjet_dana']);

		$q520 = db_query("delete from preference where korisnik=$userid and preferenca='savjet_dana'");
		$q530 = db_query("insert into preference set korisnik=$userid, preferenca='savjet_dana', vrijednost=$savjet_dana");

		nicemessage("Zamger opcije uspješno promijenjene");
		zamgerlog("promijenjene zamger opcije", 2);
		zamgerlog2("promijenjene zamger opcije");
	}

	?>
	<h2>Opcije Zamgera</h2>
	<p>U ovom trenutku možete prilagoditi sljedeće opcije koje se odnose samo na vaš korisnički nalog:</p>

	<?=genform("POST")?>
	<input type="hidden" name="subakcija" value="promjena">
	<table border="0" cellspacing="0" cellpadding="0">

	<?

	// mass-input-format
	// mass-input-separator
	// - Pošto se ova dva jednostavno zapamte od zadnje primjene, ne vidim svrhu da ih dodajem ovdje

	// csv-separator

	$csv_separatori = array(";", ",");
	$csv_vrijednosti = array("SELECTED", ""); // default je tačka-zarez

	$q100 = db_query("select vrijednost from preference where korisnik=$userid and preferenca='csv-separator'");
	if (db_num_rows($q100)>0) {
		if (db_result($q100,0,0) == ",") {
			$csv_vrijednosti[0] = "";
			$csv_vrijednosti[1] = "SELECTED";
		} else if (db_result($q100,0,0) != ";") {
			$csv_vrijednosti[0] = "";
			array_push($csv_separatori, db_result($q100,0,0));
			array_push($csv_vrijednosti, "SELECTED");
		}
	}

	?>
	<tr>
		<td>Separator za izvoz u CSV format (Excel):</td>
		<td><select name="csv-separator">
		<?
		for ($i=0; $i<count($csv_separatori); $i++) 
			print "<option value=\"$csv_separatori[$i]\" $csv_vrijednosti[$i]\">$csv_separatori[$i]</option>\n";
		?>
		</select></td>
	</tr>
	<?

	// csv-encoding
	// - Treba uvijek biti Windows-1250

	// savjet_dana

	$savjet_dana = "CHECKED";
	$q110 = db_query("select vrijednost from preference where korisnik=$userid and preferenca='savjet_dana'");
	if (db_num_rows($q110)>0 && db_result($q110,0,0)==0)
		$savjet_dana = "";

	?>
	<tr>
		<td>Prikaži "Savjet dana":</td>
		<td><input type="checkbox" name="savjet_dana" value="1" <?=$savjet_dana?>></td>
	</tr>
	<?

	// Kraj tabele

	?>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr>
		<td>&nbsp;</td>
		<td><input type="submit" value="Promijeni"></td>
	</tr>

	</table>
	</form>
	<?
	
	if ($user_nastavnik) {
		?>
		<hr>
		<h2>Izvještaj o održanoj nastavi</h2>
		
		<h3>Za demonstratore:</h3>
		
		<FORM action="index.php" method="GET">
		<input type="hidden" name="sta" value="izvjestaj/odrzana_nastava">
		<input type="hidden" name="demonstratorski" value="true">
		Akademska godina:
		<?=db_dropdown("akademska_godina");?><br>
		Predmet: 
		<SELECT name="predmet">
		<?
		$q1010 = db_query("SELECT DISTINCT p.id, p.naziv FROM predmet p, nastavnik_predmet np WHERE np.nastavnik=$userid AND np.predmet=p.id");
		while ($r1010 = db_fetch_row($q1010)) {
			print "<OPTION VALUE=\"$r1010[0]\">$r1010[1]</OPTION>\n";
		}
		?>
		</SELECT><br>
		Semestar: <SELECT NAME="semestar"><OPTION VALUE="zimski">Zimski</OPTION><OPTION VALUE="ljetnji">Ljetnji</OPTION></SELECT>
		<input type="submit" value=" Kreni ">
		</form>
		
		<h3>Za nastavnike (mjesečni izvještaj o održanoj nastavi):</h3>
		
		<FORM action="index.php" method="GET">
		<input type="hidden" name="sta" value="izvjestaj/odrzana_nastava">
		Akademska godina:
		<?=db_dropdown("akademska_godina");?><br>
		Mjesec: <select name="mjesec">
			<option value="1">Januar</option>
			<option value="2">Februar</option>
			<option value="3">Mart</option>
			<option value="4">April</option>
			<option value="5">Maj</option>
			<option value="6">Juni</option>
			<option value="7">Juli</option>
			<option value="8">Avgust</option>
			<option value="9">Septembar</option>
			<option value="10">Oktobar</option>
			<option value="11">Novembar</option>
			<option value="12">Decembar</option>
		</select><br>
		Odsjek: <select name="odsjek">
			<option value="3">Odsjek za automatiku i elektroniku</option>
			<option value="4">Odsjek za elektroenergetiku</option>
			<option value="2">Odsjek za računarstvo i informatiku</option>
			<option value="5">Odsjek za telekomunikacije</option>
		</select><br>
		<input type="submit" value=" Kreni ">
		</form>
		<?
	}

	return;
}



// Akcija: izbori i imenovanja

if ($akcija=="izbori") {

	?>
	<h2>Izbori, imenovanja, nastavni ansambl</h2>
	<p>Podaci u tabelama ispod za sada se ne mogu mijenjati! Molimo da sve greške i dopune prijavite službama <?=$conf_skr_naziv_institucije_genitiv?>.</p>

	<?


	if ($_REQUEST['subakcija'] == "arhiva_izbora") {
		?>
		<h3>Historijski pregled izbora u zvanja</h3>
		<table border="1" cellspacing="0" cellpadding="2">
		<tr>
			<th>Zvanje</th><th>Datum izbora</th><th>Datum isteka</th><th>Oblast</th><th>Podoblast</th><th>Radni odnos</th><th>Druga VŠO?</th>
		</tr>
		<?

		$q500 = db_query("select zvanje, UNIX_TIMESTAMP(datum_izbora), UNIX_TIMESTAMP(datum_isteka), oblast, podoblast, dopunski, druga_institucija from izbor WHERE osoba=$userid order by datum_isteka, datum_izbora");
		if (db_num_rows($q500) < 1) {
			?>
			<tr><td colspan="7">Nemamo nikakvih podataka o vašim izborima.</td></tr>
			<?
		}
		while ($r500 = db_fetch_row($q500)) {
			$q510 = db_query("select naziv from zvanje where id=$r500[0]");
			$nzvanje = db_result($q510,0,0);

			$datum_izbora = date("d. m. Y", $r500[1]);
			if ($r500[1] == 0)
				$datum_izbora = "<font color=\"red\">(nepoznato)</font>";
			$datum_isteka = date("d. m. Y", $r500[2]);
			if ($r500[2] == 0)
				$datum_isteka = "Neodređeno";
			$oblast = $r500[3];
			if ($oblast<1)
				$oblast = "<font color=\"red\">(nepoznato)</font>";
			else {
				$q520 = db_query("select naziv from oblast where id=$oblast");
				if (db_num_rows($q520)<1)
					$oblast = "<font color=\"red\">GREŠKA</font>";
				else
					$oblast = db_result($q520,0,0);
			}
			$podoblast = $r500[4];
			if ($podoblast<1)
				$podoblast = "<font color=\"red\">(nepoznato)</font>";
			else {
				$q530 = db_query("select naziv from podoblast where id=$podoblast");
				if (db_num_rows($q530)<1)
					$podoblast = "<font color=\"red\">GREŠKA</font>";
				else
					$podoblast = db_result($q530,0,0);
			}
			if ($r500[5]==0) $radniodnos = "Stalni";
			else $radniodnos = "Dopunski";

			if ($r500[6]==1) $druga_vso = "DA";

			?>
			<tr><td><?=$nzvanje?></td><td><?=$datum_izbora?></td><td><?=$datum_isteka?></td><td><?=$oblast?></td><td><?=$podoblast?></td><td><?=$radniodnos?></td><td><?=$druga_vso?></td></tr>
			<?
		}

		?>
		</table>
		<br>
		<a href="?sta=common/profil&akcija=izbori">&lt; &lt; Nazad</a>
		<?


		return;
	}



	if ($_REQUEST['subakcija'] == "arhiva_angazman") {
		?>
		<h3>Historijski pregled angažmana u nastavnom ansamblu</h3>
		<table border="1" cellspacing="0" cellpadding="2">
		<tr>
			<th>Akademska godina</th><th>Predmet</th><th>Status</th>
		</tr>
		<?

		$q540 = db_query("select p.id, p.naziv, angs.naziv, i.kratki_naziv, ag.naziv from angazman as a, angazman_status as angs, predmet as p, institucija as i, akademska_godina as ag where a.osoba=$userid and a.akademska_godina=ag.id and a.predmet=p.id and a.angazman_status=angs.id and p.institucija=i.id order by ag.naziv desc, angs.id, p.naziv");
		if (db_num_rows($q540) < 1) {
			?>
			<tr><td colspan="7">Nemamo nikakvih podataka o vašem angažmanu u nastavi.</td></tr>
			<?
		}
		while ($r540 = db_fetch_row($q540)) {
			?>
			<tr><td><?=$r540[4]?></td><td><?="$r540[1] ($r540[3])"?></td><td><?=$r540[2]?></td></tr>
			<?
		}

		?>
		</table>
		<br>
		<a href="?sta=common/profil&akcija=izbori">&lt; &lt; Nazad</a>
		<?


		return;
	}


	// Izbori u zvanja

	?>
	<table border="0" width="600">
	<tr><td colspan="2" bgcolor="#999999"><font color="#FFFFFF">IZBORI U ZVANJA:</font></td></tr>
	<tr>
	<?

	$q400 = db_query("select z.naziv, UNIX_TIMESTAMP(i.datum_izbora), UNIX_TIMESTAMP(i.datum_isteka), i.oblast, i.podoblast, i.dopunski, i.druga_institucija from izbor as i, zvanje as z WHERE i.osoba=$userid and i.zvanje=z.id order by i.datum_isteka DESC, i.datum_izbora DESC");
	if (db_num_rows($q400)==0) {
		?>
		<tr><td colspan="2">Nema podataka o izboru ili nikada niste bili izabrani u zvanje.</td></tr>
		<?
	} else {
		$datum_izbora = date("d. m. Y", db_result($q400,0,1));
		if (db_result($q400,0,1)==0)
			$datum_izbora = "<font color=\"red\">(nepoznato)</font>";
		$datum_isteka = date("d. m. Y", db_result($q400,0,2));
		if (db_result($q400,0,2)==0)
			$datum_isteka = "Neodređeno";
		$oblast = db_result($q400,0,3);
		if ($oblast<1)
			$oblast = "<font color=\"red\">(nepoznato)</font>";
		else {
			$q410 = db_query("select naziv from oblast where id=$oblast");
			if (db_num_rows($q410)<1)
				$oblast = "<font color=\"red\">GREŠKA</font>";
			else
				$oblast = db_result($q410,0,0);
		}
		$podoblast = db_result($q400,0,4);
		if ($podoblast<1)
			$podoblast = "<font color=\"red\">(nepoznato)</font>";
		else {
			$q420 = db_query("select naziv from podoblast where id=$podoblast");
			if (db_num_rows($q420)<1)
				$podoblast = "<font color=\"red\">GREŠKA</font>";
			else
				$podoblast = db_result($q420,0,0);
		}
		if (db_result($q400,0,5)==0) $radniodnos = "Stalni";
		else $radniodnos = "Dopunski";
		
		?>
		<tr><td>Zvanje:</td><td><b><?=db_result($q400,0,0)?></b></td></tr>
		<tr><td>Datum izbora:</td><td><b><?=$datum_izbora?></b></td></tr>
		<tr><td>Datum isteka:</td><td><b><?=$datum_isteka?></b></td></tr>
		<tr><td>Oblast:</td><td><b><?=$oblast?></b></td></tr>
		<tr><td>Podoblast:</td><td><b><?=$podoblast?></b></td></tr>
		<tr><td>Radni odnos:</td><td><b><?=$radniodnos?></b></td></tr>
		<?
		if (db_result($q400,0,6)==1) print "<tr><td colspan=\"2\"><b>Biran/a na drugoj VŠO</b></td></tr>\n";

		?>
		<tr><td colspan="2">&nbsp;</td></tr>
		<tr><td>&nbsp;</td><td><a href="?sta=common/profil&akcija=izbori&subakcija=arhiva_izbora">Historijski pregled izbora u zvanja</a></td></tr>
		<?
	}


	// Stručni i naučni stepen

	?>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td colspan="2" bgcolor="#999999"><font color="#FFFFFF">STRUČNI I NAUČNI STEPEN:</font></td></tr>
	<?

	$q430 = db_query("select strucni_stepen, naucni_stepen from osoba where id=$userid");
	$strucni_stepen = "Nepoznato / Bez stručnog stepena";
	$naucni_stepen = "Nepoznato / Bez naučnog stepena";
	if (db_result($q430,0,0)!=0) {
		$q440 = db_query("select naziv from strucni_stepen where id=".db_result($q430,0,0));
		$strucni_stepen = db_result($q440,0,0);
	}
	if (db_result($q430,0,1)!=0) {
		$q450 = db_query("select naziv from naucni_stepen where id=".db_result($q430,0,1));
		$naucni_stepen = db_result($q450,0,0);
	}

	?>
	<tr><td>Stručni stepen:</td><td><b><?=$strucni_stepen?></b></td></tr>
	<tr><td>Naučni stepen:</td><td><b><?=$naucni_stepen?></b></td></tr>
	<?


	// Nastavni ansambl

	?>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td colspan="2" bgcolor="#999999"><font color="#FFFFFF">UČEŠĆE U NASTAVNOM ANSAMBLU:</font></td></tr>
	<?


	$q460 = db_query("select p.id, p.naziv, angs.naziv, i.kratki_naziv from angazman as a, angazman_status as angs, predmet as p, institucija as i, akademska_godina as ag where a.osoba=$userid and a.akademska_godina=ag.id and ag.aktuelna=1 and a.predmet=p.id and a.angazman_status=angs.id and p.institucija=i.id order by angs.id, p.naziv");
	if (db_num_rows($q460) == 0) {
		?>
		<tr><td colspan="2">Niste angažovani niti na jednom predmetu u ovoj godini.</td></tr>
		<?
	}
	else {
		?>
		<tr><td valign="top">Predmeti:</td><td>
		<?
		while ($r460 = db_fetch_row($q460)) {
			print "$r460[1] ($r460[3]) - <b>$r460[2]</b><br>\n";
		}
		?>
		</td></tr>
		<?
	}
	?>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td>&nbsp;</td><td><a href="?sta=common/profil&akcija=izbori&subakcija=arhiva_angazman">Historijski pregled angažmana u nastavi</a></td></tr>
	<?

	return;
}



// Funkcije za log koje cachiraju imena korisnika i predmeta 

function get_user_link($id) {
	static $users = array();
	if (!$users[$id]) {
		$q20 = db_query("select ime, prezime from osoba where id=$id");
		if (db_num_rows($q20)>0) {
			$imeprezime = urlencode(db_result($q20,0,0)." ".db_result($q20,0,1));
			$link="?sta=common/inbox&amp;akcija=compose&amp;primalac=$imeprezime";
			$users[$id] = "<a href=\"$link\" target=\"_new\">".db_result($q20,0,0)." ".db_result($q20,0,1)."</a>";
		} else return $id;
	}
	return $users[$id];
}

function get_pk_link($id) {
	static $predmeti = array();
	if (!$predmeti[$id]) {
		$q30 = db_query("select p.id, p.naziv, pk.akademska_godina from ponudakursa as pk, predmet as p where pk.id=$id and pk.predmet=p.id");
		if (db_num_rows($q30)>0) {
			$predmeti[$id] = "<a href=\"?sta=nastavnik/predmet&amp;predmet=".db_result($q30,0,0)."&ag=".db_result($q30,0,2)."\" target=\"_new\">".db_result($q30,0,1)."</a>";
		} else return $id;
	}
	return $predmeti[$id];
}

function get_predmet_link($id) {
	static $aktuelna_ag = 0; // Aktuelna akademska godina
	if ($aktuelna_ag==0) {
		$q35 = db_query("select id from akademska_godina where aktuelna=1 order by id desc");
		$aktuelna_ag = db_result($q35,0,0);
	}

	static $predmeti = array();
	if (!$predmeti[$id]) {
		$q40 = db_query("select naziv from predmet where id=$id");
		if (db_num_rows($q40)>0) {
			$predmeti[$id] = "<a href=\"?sta=nastavnik/predmet&amp;predmet=$id&amp;ag=$aktuelna_ag\" target=\"_new\">".db_result($q40,0,0)."</a>";
		} else return $id;
	}
	return $predmeti[$id];
}

function get_predmet_ag_link($predmet, $ag) {
	static $godine = array();
	if (!$godine[$ag]) {
		$q50 = db_query("select naziv from akademska_godina where id=$ag");
		if (db_num_rows($q50)>0) {
			$godine[$ag] = db_result($q50,0,0);
		} else return "$predmet, $ag";
	}

	static $predmeti = array();
	if (!$predmeti[$predmet]) {
		$q40 = db_query("select naziv from predmet where id=$predmet");
		if (db_num_rows($q40)>0) {
			$predmeti[$predmet] = db_result($q40,0,0);
		} else return "$predmet, $ag";
	}
	return "<a href=\"?sta=nastavnik/predmet&amp;predmet=$predmet&amp;ag=$ag\" target=\"_new\">".$predmeti[$predmet]." ".$godine[$ag]."</a>";
}

function get_zadaca_link($id, $usr) {
	$q50 = db_query("select z.naziv,z.predmet,z.akademska_godina, p.naziv from zadaca as z, predmet as p where z.id=$id and z.predmet=p.id");
	if (db_num_rows($q50)>0) {
		$naziv=db_result($q50,0,0);
		if (!preg_match("/\w/",$naziv)) $naziv="[Bez imena]";
		$predmet=db_result($q50,0,1);
		$ag=db_result($q50,0,2);
		$pnaziv=db_result($q50,0,3);
		if (intval($usr)>0) {
			$q55 = db_query("select l.id from student_labgrupa as sl, labgrupa as l where sl.student=$usr and sl.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag order by virtualna");
			if (db_num_rows($q55)>0)
				$link="?sta=saradnik/grupa&amp;id=".db_result($q55,0,0);
			else
				$link="?sta=nastavnik/zadace&amp;predmet=$predmet&ag=$ag";
			return "<a href=\"$link\" target=\"_blank\">$naziv ($pnaziv)</a>";
		}
	}
	return "$id";
}

function get_cas_link($id) {
	$q70 = db_query("select l.id, p.naziv, l.naziv from cas as c, labgrupa as l, predmet as p where c.id=$id and c.labgrupa=l.id and l.predmet=p.id");
	if (db_num_rows($q70)>0) {
		$link = "?sta=saradnik/grupa&amp;id=".db_result($q70,0,0);
		$tekst = db_result($q70,0,2)." (".db_result($q70,0,1).")";
		return "<a href=\"$link\" target=\"_blank\">$tekst</a>";
	}
	return "$id";
}

function get_ispit_link($id) {
	static $ispiti = array();
	if (!$ispiti[$id]) {
		$q60 = db_query("select p.naziv, k.gui_naziv, i.predmet, i.akademska_godina from ispit as i, predmet as p, komponenta as k where i.id=$id and i.predmet=p.id and i.komponenta=k.id");
		if (db_num_rows($q60)>0) {
			$link = "?sta=nastavnik/ispiti&amp;predmet=".db_result($q60,0,2)."&amp;ag=".db_result($q60,0,3);
			$tekst = db_result($q60,0,1)." (".db_result($q60,0,0).")";
			$ispiti[$id] = "<a href=\"$link\" target=\"_blank\">$tekst</a>";
		}
		else return "$id";
	}
	return $ispiti[$id];
}

function get_grupa_link($id) {
	static $grupe = array();
	if (!$grupe[$id]) {
		$q80 = db_query("select p.naziv, l.naziv from labgrupa as l, predmet as p where l.id=$id and l.predmet=p.id");
		if (db_num_rows($q80)>0) {
			$link = "?sta=saradnik/grupa&amp;id=$id";
			$tekst = db_result($q80,0,1)." (".db_result($q80,0,0).")";
			$grupe[$id] = "<a href=\"$link\" target=\"_blank\">$tekst</a>";
		}
		else return "$id";
	}
	return $grupe[$id];
}

function get_komp_link($id) {
	static $komponente = array();
	if (!$komponente[$id]) {
		$q70 = db_query("select gui_naziv from komponenta where id=$id");
		if (db_num_rows($q70)>0) {
			$komponente[$id] = db_result($q70,0,0);
		}
		else return "$id";
	}
	return $komponente[$id];
}

function get_projekat_link($id) {
	static $projekti = array();
	if (!$projekti[$id]) {
		$q90 = db_query("select p.naziv, p2.naziv, p2.id, p.akademska_godina from projekat as p, predmet as p2 where p.id=$id and p.predmet=p2.id");
		if (db_num_rows($q90)>0) {
			$link = "?sta=nastavnik/projekti&amp;predmet=".db_result($q90,0,2)."&amp;ag=".db_result($q90,0,3)."&amp;akcija=projektna_stranica&amp;projekat=$id";
			$tekst = db_result($q90,0,0)." (".db_result($q90,0,1).")";
			$projekti[$id] = "<a href=\"$link\" target=\"_blank\">$tekst</a>";
		}
		else return "$id";
	}
	return $projekti[$id];
}

function get_studij($id) {
	static $studiji = array();
	if (!$studiji[$id]) {
		$q100 = db_query("select naziv from studij where id=$id");
		$studiji[$id] = db_result($q100,0,0);
	}
	return $studiji[$id];
}

function get_ag($id) {
	static $ags = array();
	if (!$ags[$id]) {
		$q110 = db_query("select naziv from akademska_godina where id=$id");
		$ags[$id] = db_result($q110,0,0);
	}
	return $ags[$id];
}

function add_string($s1, $s2, $s3) {
	if ($s1=="") return $s3;
	return $s1.$s2.$s3;
}


if ($akcija == "log") {
	// Uvodne deklaracije
	$maxlogins = 20;
	$stardate = intval($_GET['stardate']);
	if ($stardate == 0) {
		$q199 = db_query("select id from log2 order by id desc limit 1");
		$stardate = db_result($q199,0,0)+1;
	}

	// Za iole prihvatljive performanse upita na log bazu mora se imati limit,
	// ali kod pretrage postoji mogućnost da upit sa tim limitom ne vrati dovoljan broj rezultata.
	// Broj ispod je nekakav kompromis između ova dva problema
	$query_limit = 10000; 
	$query_max_limit = 1000000;

	?>
	<div style="margin-left: 20px">
	<?

	// Upit za log
	$q10 = db_query ("SELECT l.id, UNIX_TIMESTAMP(l.vrijeme), l.userid, lm.naziv, l.dogadjaj, ld.opis, ld.nivo, l.objekat1, l.objekat2, l.objekat3 
	FROM log2 AS l, log2_dogadjaj AS ld, log2_modul AS lm 
	WHERE l.modul=lm.id AND l.dogadjaj=ld.id AND l.id<$stardate AND l.id>".($stardate-$query_limit)." AND l.userid=$userid and (ld.nivo>=2 or ld.opis='login') 
	ORDER BY l.id DESC");
	$lastlogin = array();
	$eventshtml = array();
	$logins = 0;
	$prvidatum = $zadnjidatum = 0;
	$last_id = $stardate - $query_limit;
	while ($logins < $maxlogins) {
		$r10 = db_fetch_row($q10);
		if (!$r10) {
			$stardate = $last_id+1;
			$last_id = $stardate-$query_limit;
			// Da ubrzamo stvari, povećaćemo limit na upitu
			$query_limit *= 2;
			if ($query_limit > $query_max_limit || $stardate < 2) {
				// Nema više smisla nastaviti, rezultata više nema
				//$stardate=1;
				break;
			}
			
			$q10 = db_query ("SELECT l.id, UNIX_TIMESTAMP(l.vrijeme), l.userid, lm.naziv, l.dogadjaj, ld.opis, ld.nivo, l.objekat1, l.objekat2, l.objekat3 
			FROM log2 AS l, log2_dogadjaj AS ld, log2_modul AS lm 
			WHERE l.modul=lm.id AND l.dogadjaj=ld.id AND l.id<$stardate AND l.id>".($stardate-$query_limit)." AND l.userid=$userid and (ld.nivo>=2 or ld.opis='login') 
			ORDER BY l.id DESC");
			continue; // Povratak na početak petlje
		}
		
		$last_id = $r10[0]; // $lastlogin koristimo da provjerimo da li je korisnik išta radio nakon logina
		if ($prvidatum==0) $prvidatum = $r10[1];
		$zadnjidatum = $r10[1];
		$nicedate = " (".date("d.m.Y. H:i:s", $r10[1]).")";
		$usr = $r10[2]; // ID korisnika
		$modul = $r10[3];
		$evt_id = $r10[4];
		$opis = $r10[5]; // string koji opisuje dogadjaj

		// ne prikazuj login ako je to jedina stavka, ako je nivo veci od 1 ili ako nema pretrage
		if ($lastlogin[$usr]==0 && (($nivo==1 && $pretraga=="") || $opis != "login")) { 
			$lastlogin[$usr]=$r10[0];
			$logins++;
			if ($logins > $maxlogins) {
				$stardate=$r10[0]+1;
				break; // izlaz iz while
			}
		}

		if ($r10[6]==1) $nivoimg="info";
		else if ($r10[6]==2) $nivoimg="edit_red";
		else if ($r10[6]==3) $nivoimg="warning";
		else if ($r10[6]==4) $nivoimg="audit";


		$evt = "";
		if ($modul != "") $evt .= "$modul: ";
		$evt .= "$opis";
		$objekti = "";


		// Log transformacije opisa
		if (substr($opis,0,14) == "poslana zadaca" || substr($opis,0,24) == "greska pri slanju zadace" || $opis == "poslao praznu zadacu" || $opis == "ne postoji fajl za zadacu" || $opis == "zadaca nema toliko zadataka") {
			$objekti = get_zadaca_link($r10[7], $usr).", zadatak $r10[8]";

		} else if ($opis == "isteklo vrijeme za slanje zadace" || $opis == "pogresan tip datoteke" || $opis == "student ne slusa predmet za zadacu" || $opis == "nije nastavnik na predmetu za zadacu" || $opis == "ogranicenje na predmet za zadacu" || $opis == "postavka ne postoji" || $opis == "obrisana postavka zadace" || $opis == "smanjen broj zadataka u zadaci" || $opis == "azurirana zadaca" || $opis == "niko nije poslao zadacu" || $opis == "kreiranje arhive zadaca nije uspjelo") {
			$objekti = get_zadaca_link($r10[7], $usr);

		} else if ($opis == "ne postoji attachment" || $opis == "bodovanje zadace") {
			$objekti = get_user_link($r10[7]).", ".get_zadaca_link($r10[8], $r10[7]).", zadatak $r10[9]";

		} else if ($opis == "prisustvo azurirano") {
			$objekti = get_user_link($r10[7]).", ".get_cas_link($r10[8]).", prisustvo: $r10[9]";

		} else if ($opis == "prisustvo - nije nastavnik na predmetu" || $opis == "prisustvo - ima ogranicenje za grupu" || $opis == "registrovan cas") {
			$objekti = get_cas_link($r10[7]);

		} else if ($opis == "student ne slusa predmet" || $opis == "ne postoji moodle ID za predmet" || substr($opis,0,25) == "nije saradnik na predmetu" || $opis == "svi projekti su jos otkljucani" || $opis == "nije nastavnik na predmetu" || $opis == "predmet nema virtuelnu grupu" || $opis == "nije definisan tip predmeta" || substr($opis,0,22) == "dosegnut limit za broj projekata" || substr($opis,0,19) == "projekti zakljucani" || $opis == "nije ni na jednom projektu (odjava)" || $opis == "prekopirane labgrupe" || $opis == "izmijenjeni parametri projekata na predmetu" || $opis == "kreiran tip predmeta") {
			$objekti = get_predmet_ag_link($r10[7],$r10[8]);

		} else if ($opis == "ne postoji komponenta za zadace" || $opis == "promijenjen tip predmeta" || $opis == "nije definisana komponenta za prisustvo" || $opis == "nepostojeca virtualna labgrupa" || $opis == "nije ponudjen predmet") {
			$objekti = get_predmet_ag_link($r10[7],$r10[8]);

		} else if ($opis == "dodana ocjena" || $opis == "obrisana ocjena" || $opis == "izmjena ocjene" || $opis == "promijenjen datum ocjene" || substr($opis,0,27) ==  "student ispisan sa predmeta" || $opis == "nastavniku data prava na predmetu" || $opis == "nastavnik angazovan na predmetu") {
			$objekti = get_user_link($r10[7]).", ".get_predmet_ag_link($r10[8], $r10[9]);

		} else if ($opis == "nijedna zadaca nije aktivna" || $opis == "popunjena anketa" || $opis == "odabrana tema za zadacu" || $opis == "ponisten datum za izvoz") {
			$objekti = get_predmet_link($r10[7]);

		} else if ($opis == "student ne slusa ponudukursa") {
			$objekti = get_pk_link($r10[7]);

		} else if ($opis == "kreirao ponudu kursa zbog studenta" || substr($opis,0,25) == "student upisan na predmet") {
			$objekti = get_user_link($r10[7]).", ".get_pk_link($r10[8]);

		} else if ($opis == "upisan rezultat ispita" || $opis == "izbrisan rezultat ispita" || $opis == "izmjenjen rezultat ispita") {
			$objekti = get_user_link($r10[7]).", ".get_ispit_link($r10[8]);

		} else if ($opis == "promijenjen tip ispita" || $opis == "promijenjen datum ispita" || $opis == "kreiran novi ispit") {
			$objekti = get_ispit_link($r10[7]);

		} else if ($opis == "izmjena bodova za fiksnu komponentu") {
			$objekti = get_user_link($r10[7]).", ".get_pk_link($r10[8]).", ".get_komp_link($r10[9]);

		} else if ($opis == "nije na projektu" || $opis == "dodao link na projektu" || $opis == "uredio link na projektu" || $opis == "obrisao link na projektu" || $opis == "dodao rss feed na projektu" || $opis == "uredio rss feed na projektu" || $opis == "obrisao rss feed na projektu" || $opis == "dodao clanak na projektu" || $opis == "uredio clanak na projektu" || $opis == "obrisao clanak na projektu" || $opis == "dodao fajl na projektu" || $opis == "uredio fajl na projektu" || $opis == "obrisao fajl na projektu" || $opis == "dodao temu na projektu" || $opis == "obrisao post na projektu" || substr($opis, 0, 18) == "projekat zakljucan" || substr($opis, 0, 17) == "projekat popunjen" || $opis == "dodao projekat na predmetu" || $opis == "izmijenio projekat" || $opis == "dodao biljesku na projekat") {
			$objekti = get_projekat_link($r10[7]);
	//		$objekti = $r10[7];

		} else if ($opis == "student prijavljen na projekat" || $opis == "student prebacen na projekat" || $opis == "student odjavljen sa projekta") {
			$objekti = get_user_link($r10[7]).", ".get_projekat_link($r10[8]);

		} else if ($opis == "poslana poruka" || $opis == "osoba nema sliku" || $opis == "nema datoteke za sliku" || $opis == "nepoznat tip slike" || $opis == "citanje fajla za sliku nije uspjelo" || $opis == "nije studentska, a pristupa tudjem izvjestaju" || $opis == "korisnik nikada nije studirao" || $opis == "prihvacen zahtjev za promjenu podataka" || $opis == "odbijen zahtjev za promjenu podataka" || $opis == "korisnik vec postoji u bazi" || $opis == "dodan novi korisnik" || $opis == "promijenjeni licni podaci korisnika" || $opis == "postavljena slika za korisnika" || $opis == "obrisana slika za korisnika" || $opis == "proglasen za studenta") {
			$objekti = get_user_link($r10[7]);

		} else if ($opis == "postavljen broj indeksa" || $opis == "prihvacen zahtjev za koliziju" || $opis == "dodani podaci o izboru" || $opis == "azurirani podaci o izboru" || $opis == "promijenjen email za korisnika") {
			$objekti = get_user_link($r10[7]);

		} else if ($opis == "greska prilikom slanja fajla na zavrsni" || $opis == "dodao fajl na zavrsni" || $opis == "azuriran sazetak zavrsnog rada" || $opis == "izmijenio temu zavrsnog rada" || $opis == "dodao biljesku na zavrsni rad" || $opis == "dodana tema zavrsnog rada") {
	//		$objekti = get_zavrsni_link($r10[7]);
			$objekti = $r10[7];

		} else if (substr($opis,0,24) == "student ispisan sa grupe" || substr($opis,0,22) == "student upisan u grupu" || $opis == "dodan komentar na studenta" || $opis == "promijenjena grupa studenta") {
			$objekti = get_user_link($r10[7]).", ".get_grupa_link($r10[8]);

		} else if ($opis == "preimenovana labgrupa" || substr($opis, 0, 17) == "kreirana labgrupa" || $opis == "ima ogranicenje na labgrupu") {
			$objekti = get_grupa_link($r10[7]);

		} else if ($opis == "student upisan na studij" || $opis == "pokusao ispisati studenta sa studija koji ne slusa") {
			$objekti = get_user_link($r10[7]).", ".get_studij($r10[8])." ".get_ag($r10[9]);

		} else {
			// Kreiranje log zapisa
			if ($r10[7]>0) $objekti = add_string($objekti, ", ", $r10[7]);
			if ($r10[8]>0) $objekti = add_string($objekti, ", ", $r10[8]);
			if ($r10[9]>0) $objekti = add_string($objekti, ", ", $r10[9]);
		}

		$q20 = db_query("SELECT tekst FROM log2_blob WHERE log2=$r10[0]");
		if (db_num_rows($q20)>0) 
			$objekti = add_string($objekti, ", ", db_result($q20,0,0));
		if ($objekti !== "") $evt .= " ($objekti)";


		// Pošto idemo unazad, login predstavlja kraj zapisa za korisnika

		if ($opis == "login") {
			if ($lastlogin[$usr] && $lastlogin[$usr]!=0) {
				$eventshtml[$lastlogin[$usr]] = "<br/><img src=\"static/images/fnord.gif\" width=\"37\" height=\"1\"> <img src=\"static/images/16x16/$nivoimg.png\" width=\"16\" height=\"16\" align=\"center\" alt=\"$nivoimg\"> login (ID: $usr) $nicedate\n".$eventshtml[$lastlogin[$usr]];
				$lastlogin[$usr]=0;
			}
		}
		else if (strstr($evt," su=")) {
			$eventshtml[$lastlogin[$usr]] = "<br/><img src=\"static/images/fnord.gif\" width=\"37\" height=\"1\"> <img src=\"static/images/16x16/$nivoimg.png\" width=\"16\" height=\"16\" align=\"center\" alt=\"$nivoimg\"> SU to ID: $usr $nicedate\n".$eventshtml[$lastlogin[$usr]];
			$lastlogin[$usr]=0;
		}


		else {
			$eventshtml[$lastlogin[$usr]] = "<br/><img src=\"static/images/fnord.gif\" width=\"37\" height=\"1\"> <img src=\"static/images/16x16/$nivoimg.png\" width=\"16\" height=\"16\" align=\"center\" alt=\"$nivoimg\"> ".$evt.$nicedate."\n".$eventshtml[$lastlogin[$usr]];
		}
	}
	if ($stardate==1) $zadnjidatum=1; // Došlo je do breaka...
	else $stardate = $last_id;


	// Dodajemo zaglavlja sa [+] poljem (prebaciti iznad)

	foreach ($eventshtml as $logid => $event) {
		if (substr($event,0,4)!="<img") {
			// Login počinje sa <br/>

			// TODO: optimizovati upite!

			$q201 = db_query("select userid, UNIX_TIMESTAMP(vrijeme) from log2 where id=".intval($logid));
			$userid = intval(db_result($q201,0,0));
			$nicedate = " (".date("d.m.Y. H:i:s", db_result($q201,0,1)).")";

			if ($userid==0) {
				$imeprezime = "ANONIMNI PRISTUPI";
				$usrimg="zad_bug";

			} else {
				$q202 = db_query("select ime, prezime from osoba where id=$userid");
				$imeprezime = db_result($q202,0,0)." ".db_result($q202,0,1);

				$q203 = db_query("select count(*) from privilegije where osoba=$userid and privilegija='nastavnik'");
				$q204 = db_query("select count(*) from privilegije where osoba=$userid and privilegija='studentska'");
				$q205 = db_query("select count(*) from privilegije where osoba=$userid and privilegija='siteadmin'");

				if (db_result($q205,0,0)>0) {
					$usrimg="admin"; 
				} else if (db_result($q204,0,0)>0) {
					$usrimg="teta"; 
				} else if (db_result($q203,0,0)>0) {
					$usrimg="tutor"; 
				} else {
					$usrimg="user";
				}
			}
	
			$link = "?sta=studentska/osobe&amp;akcija=edit&amp;osoba=$userid";

			print "<img src=\"static/images/plus.png\" width=\"13\" height=\"13\" id=\"img-l$logid\" onclick=\"daj_stablo('l$logid')\" alt=\"plus\">
	Pristup $nicedate
	<div id=\"l$logid\" style=\"display:none\">\n";
		}

		print "$event</div><br/>\n";
	}
	?>
	<p>&nbsp;</p>
	<p><a href="<?=genuri()?>&amp;stardate=<?=$stardate?>">Sljedećih <?=$maxlogins?></a></p>
	</div>
	<?

	return;
}




// Akcija za lične podatke

?>
<h2>Zahtjev za promjenu ličnih podataka u Informacionom sistemu <?=$conf_skr_naziv_institucije_genitiv?></h2>
<?


if ($_POST['subakcija'] == "potvrda" && check_csrf_token()) {
	// Da li je u pitanju izmjena ili brisanje maila
	$q1000 = db_query("select id, adresa, sistemska from email where osoba=$userid");
	while ($r1000 = db_fetch_row($q1000)) {
		if ($_POST['obrisi_email'.$r1000[0]]) {
			$q1010 = db_query("DELETE FROM email WHERE id=$r1000[0]");
			nicemessage("E-mail adresa obrisana.");
			print "<a href=\"javascript:history.go(-1);\">Nazad</a>";
			zamgerlog("obrisana email adresa ".$r1000[1],2);
			zamgerlog2("email adresa obrisana", 0, 0, 0, $r1000[1]);
			return 0;
		}
		if ($_POST['izmijeni_email'.$r1000[0]]) {
			// Validacija maila
			$email = db_escape($_POST['email'.$r1000[0]]);
			if (!preg_match("/\w/", $email)) {
				niceerror("Promjena adrese nije uspjela. Unijeli ste praznu e-mail adresu.");
				print "<p>Ako želite da obrišete adresu, koristite dugme \"Obriši\".</p>";
				print "<a href=\"javascript:history.go(-1);\">Nazad</a>";
				return 0;
			}

			if (function_exists('filter_var')) {
				if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
					niceerror("Nova e-mail adresa nije ispravna.");
					print "<a href=\"javascript:history.go(-1);\">Nazad</a>";
					return 0;
				}
			} else {
				if (!strstr($email, "@")) {
					niceerror("Nova e-mail adresa nije ispravna.");
					print "<a href=\"javascript:history.go(-1);\">Nazad</a>";
					return 0;
				}
			}

			$q1020 = db_query("update email set adresa='$email' where id=".$r1000[0]);
			nicemessage("E-mail adresa promijenjena.");
			print "<a href=\"javascript:history.go(-1);\">Nazad</a>";
			zamgerlog("email adresa promijenjena iz ".$r1000[1]." u ".$email,2);
			zamgerlog2("email adresa promijenjena", 0, 0, 0, "$r1000[1] -> $email");
			return 0;
		}
	}

	if ($_POST['dodaj_email']) {
		// Validacija maila
		$email = db_escape($_REQUEST['email_novi']);

		if (!preg_match("/\w/", $email)) {
			niceerror("Dodavanje adrese nije uspjelo. Unijeli ste praznu e-mail adresu.");
			print "<a href=\"javascript:history.go(-1);\">Nazad</a>";
			return 0;
		}

		if (function_exists('filter_var')) {
			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				niceerror("Nova e-mail adresa nije ispravna.");
				print "<a href=\"javascript:history.go(-1);\">Nazad</a>";
				return 0;
			}
		} else {
			if (!strstr($email, "@")) {
				niceerror("Nova e-mail adresa nije ispravna.");
				print "<a href=\"javascript:history.go(-1);\">Nazad</a>";
				return 0;
			}
		}

		$q1030 = db_query("INSERT INTO email SET osoba=$userid, adresa='$email', sistemska=0");
		nicemessage("E-mail adresa dodana.");
		print "<a href=\"javascript:history.go(-1);\">Nazad</a>";
		zamgerlog("dodana nova email adresa ".$email,2);
		zamgerlog2("email adresa dodana", 0, 0, 0, $email);
		return 0;
		
	}


	$ime = db_escape($_REQUEST['ime']);
	$prezime = db_escape($_REQUEST['prezime']);
	$spol = $_REQUEST['spol'];
	if ($spol != "M" && $spol != "Z") $spol="";
	$brindexa = db_escape($_REQUEST['brindexa']);
	$jmbg = db_escape($_REQUEST['jmbg']);

	$adresa = db_escape($_REQUEST['adresa']);
	$adresa_mjesto = db_escape($_REQUEST['adresa_mjesto']);
	$telefon = db_escape($_REQUEST['telefon']);

	$imeoca = db_escape($_REQUEST['imeoca']);
	$prezimeoca = db_escape($_REQUEST['prezimeoca']);
	$imemajke = db_escape($_REQUEST['imemajke']);
	$prezimemajke = db_escape($_REQUEST['prezimemajke']);
	$mjesto_rodjenja = db_escape($_REQUEST['mjesto_rodjenja']);
	$opcina_rodjenja = intval($_REQUEST['opcina_rodjenja']);
	$drzava_rodjenja = intval($_REQUEST['drzava_rodjenja']);
	$nacionalnost = intval($_REQUEST['nacionalnost']);
	$drzavljanstvo = intval($_REQUEST['drzavljanstvo']);
	$kanton = intval($_REQUEST['_lv_column_kanton']);
	if ($_REQUEST['borac']) $borac=1; else $borac=0;

	if (preg_match("/(\d+).*?(\d+).*?(\d+)/", $_REQUEST['datum_rodjenja'], $matches)) {
		$dan=$matches[1]; $mjesec=$matches[2]; $godina=$matches[3];
		if ($godina<100)
			if ($godina<50) $godina+=2000; else $godina+=1900;
		if ($godina<1000)
			if ($godina<900) $godina+=2000; else $godina+=1000;
	} else {
		$dan="00"; $mjesec="00"; $godina="0000";
	}

	// Mjesto
	$mjrid=0;
	if ($mjesto_rodjenja != "") {
		$q1 = db_query("select id from mjesto where naziv='$mjesto_rodjenja' and opcina=$opcina_rodjenja and drzava=$drzava_rodjenja");
		if (db_num_rows($q1)<1) {
			$q2 = db_query("insert into mjesto set naziv='$mjesto_rodjenja', opcina=$opcina_rodjenja, drzava=$drzava_rodjenja");
			$q1 = db_query("select id from mjesto where naziv='$mjesto_rodjenja' and opcina=$opcina_rodjenja and drzava=$drzava_rodjenja");
			zamgerlog("upisano novo mjesto rodjenja $mjesto_rodjenja", 2);
			zamgerlog2("upisano novo mjesto rodjenja", 0, 0, 0, $mjesto_rodjenja);
		}
		$mjrid = db_result($q1,0,0);
	}

	$admid=0;
	if ($adresa_mjesto != "") {
		$q3 = db_query("select id from mjesto where naziv='$adresa_mjesto'");
		if (db_num_rows($q3)<1) {
			$q4 = db_query("insert into mjesto set naziv='$adresa_mjesto', opcina=$opcina_rodjenja, drzava=1");
			$q3 = db_query("select id from mjesto where naziv='$adresa_mjesto'");
			zamgerlog("upisano novo mjesto (adresa) $adresa_mjesto", 2);
			zamgerlog2("upisano novo mjesto (adresa)", 0, 0, 0, $adresa_mjesto);
		}
		$admid = db_result($q3,0,0);
	}


	// Da li je uopste bilo promjene?
	$q05 = db_query("select ime, prezime, imeoca, prezimeoca, imemajke, prezimemajke, spol, brindexa, datum_rodjenja, mjesto_rodjenja, nacionalnost, drzavljanstvo, jmbg, adresa, adresa_mjesto, telefon, kanton, boracke_kategorije from osoba where id=$userid");
	if (db_result($q05,0,0)==$ime && db_result($q05,0,1)==$prezime && db_result($q05,0,2)==$imeoca && db_result($q05,0,3)==$prezimeoca && db_result($q05,0,4)==$imemajke && db_result($q05,0,5)==$prezimemajke && db_result($q05,0,6)==$spol && db_result($q05,0,7)==$brindexa && db_result($q05,0,8)=="$godina-$mjesec-$dan" && db_result($q05,0,9)==$mjrid && db_result($q05,0,10)==$nacionalnost && db_result($q05,0,11)==$drzavljanstvo && db_result($q05,0,12)==$jmbg && db_result($q05,0,13)==$adresa && db_result($q05,0,14)==$admid && db_result($q05,0,15)==$telefon && db_result($q05,0,16)==$kanton && db_result($q05,0,17)==$borac) {
		?><p><b>Ništa nije promijenjeno?</b><br>
		Podaci koje ste unijeli ne razlikuju se od podataka koje već imamo u bazi. Zahtjev za promjenu neće biti poslan.</p><?
		return;
	}

	$q10 = db_query("select id from promjena_podataka where osoba=$userid");
	if (db_num_rows($q10)>0) {
		$id = db_result($q10,0,0);
		$upit = "osoba=$userid";
		if ($ime != "") $upit .= ", ime='$ime'";
		if ($prezime != "") $upit .= ", prezime='$prezime'";
		if ($imeoca != "") $upit .= ", imeoca='$imeoca'";
		if ($prezimeoca != "") $upit .= ", prezimeoca='$prezimeoca'";
		if ($imemajke != "") $upit .= ", imemajke='$imemajke'";
		if ($prezimemajke != "") $upit .= ", prezimemajke='$prezimemajke'";
		if ($spol != "") $upit .= ", spol='$spol'";
		if ($brindexa != "") $upit .= ", brindexa='$brindexa'";
		if ($jmbg != "") $upit .= ", jmbg='$jmbg'";
		if ($mjrid != 0) $upit .= ", mjesto_rodjenja=$mjrid";
		if ($nacionalnost != 0) $upit .= ", nacionalnost=$nacionalnost";
		if ($drzavljanstvo != 0) $upit .= ", drzavljanstvo=$drzavljanstvo";
		if ($adresa != "") $upit .= ", adresa='$adresa'";
		if ($admid != 0) $upit .= ", adresa_mjesto='$admid'";
		if ($telefon != "") $upit .= ", telefon='$telefon'";
		if ($kanton != 0) $upit .= ", kanton=$kanton";
		if ($godina!=1970) $upit .= ", datum_rodjenja='$godina-$mjesec-$dan'";
		if ($borac != 0) $upit .= ", boracke_kategorije=$borac";
		$q20 = db_query("update promjena_podataka set $upit where id=$id");
	} else {
		$q25 = db_query("select slika from osoba where id=$userid");
		$slika = db_result($q25,0,0);
		$q30 = db_query("insert into promjena_podataka set osoba=$userid, ime='$ime', prezime='$prezime', imeoca='$imeoca', prezimeoca='$prezimeoca', imemajke='$imemajke', prezimemajke='$prezimemajke', spol='$spol', brindexa='$brindexa', jmbg='$jmbg', mjesto_rodjenja=$mjrid, nacionalnost=$nacionalnost, drzavljanstvo=$drzavljanstvo, adresa='$adresa', adresa_mjesto=$admid, telefon='$telefon', kanton=$kanton, datum_rodjenja='$godina-$mjesec-$dan', boracke_kategorije=$borac, slika='".db_escape($slika)."'");
	}
	zamgerlog("zatrazena promjena ličnih podataka",2); // 2 = edit
	zamgerlog2("zatrazena promjena licnih podataka"); // 2 = edit

	?>
	<h2>Zahvaljujemo!</h2>

	<p>Zahtjev je poslan!</p>
	<p>Nakon što Studentska služba provjeri ispravnost podataka, oni će biti uneseni u Informacioni sistem. Molimo da budete dostupni za slučaj da je potrebna dokumentacija za neku od izmjena koje ste zatražili.</p>
	<?
	return;
}


// Postavljanje ili promjena slike

if ($_POST['subakcija']=="postavisliku" && check_csrf_token()) {
	$slika = $_FILES['slika']['tmp_name'];
	if ($slika && (file_exists($slika))) {
		// Kopiramo novu sliku na privremenu lokaciju
		$podaci = getimagesize($slika);
		$koef = $podaci[0]/$podaci[1];
		if ($koef < 0.5 || $koef > 2) {
			niceerror("Omjer širine i visine slike nije povoljan.");
			print "<p>Slika bi trebala biti uobičajenog formata slike za lične dokumente. Ova je formata $podaci[0]x$podaci[1].</p>\n";
			return;
		}

		$novavisina = 150;
		$novasirina = $novavisina * $koef;
		$filename = "$conf_files_path/slike/$userid-promjena";
		if (!file_exists("$conf_files_path/slike"))
			mkdir ("$conf_files_path/slike", 0777, true);

		$dest = imagecreatetruecolor($novasirina, $novavisina);
		switch ($podaci[2]) {
			case IMAGETYPE_GIF:
				$source = imagecreatefromgif($slika);
				imagecopyresampled($dest, $source, 0, 0, 0, 0, $novasirina, $novavisina, $podaci[0], $podaci[1]);
				imagegif($dest, $filename.".gif");
				$slikabaza = "$userid-promjena.gif";
				break;
			case IMAGETYPE_JPEG:
				$source = imagecreatefromjpeg($slika);
				imagecopyresampled($dest, $source, 0, 0, 0, 0, $novasirina, $novavisina, $podaci[0], $podaci[1]);
				imagejpeg($dest, $filename.".jpg");
				$slikabaza = "$userid-promjena.jpg";
				break;
			case IMAGETYPE_PNG:
				$source = imagecreatefrompng($slika);
				imagecopyresampled($dest, $source, 0, 0, 0, 0, $novasirina, $novavisina, $podaci[0], $podaci[1]);
				imagepng($dest, $filename.".png");
				$slikabaza = "$userid-promjena.png";
				break;
			case IMAGETYPE_TIFF_II:
				nicemessage("Nije moguća promjena dimenzija slike tipa TIFF... Ostavljam zadate dimenzije.");
				rename ($slika, $filename.".tiff");
				$slikabaza = "$userid-promjena.tiff";
				break;
			default:
				niceerror("Nepoznat tip slike.");
				print "<p>Za vašu profil sliku možete koristiti samo slike tipa GIF, JPEG ili PNG.</p>";
				return;
		}
	
		$q300 = db_query("select id from promjena_podataka where osoba=$userid");
		if (db_num_rows($q300)>0) {
			$q310 = db_query("update promjena_podataka set slika='$slikabaza' where osoba=$userid");
		} else {
			$q320 = db_query("insert into promjena_podataka select 0, $userid, ime, prezime, imeoca, prezimeoca, imemajke, prezimemajke, spol, brindexa, datum_rodjenja, mjesto_rodjenja, nacionalnost, drzavljanstvo, boracke_kategorije, jmbg, adresa, adresa_mjesto, telefon, kanton, '$slikabaza', NOW() from osoba where id=$userid");
		}
	
		zamgerlog("zatrazeno postavljanje/promjena slike", 2);
		zamgerlog2("zatrazeno postavljanje/promjena slike");
		?>
		<h2>Zahvaljujemo!</h2>
	
		<p>Zahtjev je poslan!</p>
		<p>Nakon što Studentska služba provjeri ispravnost podataka, oni će biti uneseni u Informacioni sistem. Molimo da budete dostupni za slučaj da je potrebna dokumentacija za neku od izmjena koje ste zatražili.</p>
		<?
		return;
	} else {
		nicemessage("Greška pri slanju slike");
	}
}


// Brisanje slike

if ($_POST['subakcija']=="obrisisliku" && check_csrf_token()) {
	$q300 = db_query("select id from promjena_podataka where osoba=$userid");
	if (db_num_rows($q300)>0) {
		$q310 = db_query("update promjena_podataka set slika='' where osoba=$userid");
	} else {
		$q320 = db_query("insert into promjena_podataka select 0, $userid, ime, prezime, imeoca, prezimeoca, imemajke, prezimemajke, spol, brindexa, datum_rodjenja, mjesto_rodjenja, nacionalnost, drzavljanstvo, boracke_kategorije, jmbg, adresa, adresa_mjesto, telefon, kanton, '', NOW() from osoba where id=$userid");
	}

	zamgerlog("zatrazeno brisanje slike", 2);
	zamgerlog2("zatrazeno brisanje slike");
	?>
	<h2>Zahvaljujemo!</h2>

	<p>Zahtjev je poslan!</p>
	<p>Nakon što Službe <?=$conf_skr_naziv_institucije_genitiv?> provjere ispravnost podataka, oni će biti uneseni u Informacioni sistem. Molimo da budete dostupni za slučaj da je potrebna dokumentacija za neku od izmjena koje ste zatražili.</p>
	<?
	return;
}



if ($conf_system_auth == "ldap") {
?>
<h3><font color="red">NAPOMENA:</font> Pristupnu šifru možete promijeniti isključivo koristeći <?=$conf_promjena_sifre?></h3>
<?

} else {
	// TODO: napraviti promjenu sifre

}

$q390 = db_query("select UNIX_TIMESTAMP(vrijeme_zahtjeva) from promjena_podataka where osoba=$userid order by vrijeme_zahtjeva desc limit 1");

if (db_num_rows($q390)>0) {
	?><p><b>Već ste uputili zahtjev za promjenu ličnih podataka</b> (na dan <?=date("d. m. Y. \u H:i:s", db_result($q390,0,0))?>). Vaš zahtjev se trenutno razmatra. U međuvremenu, ispod možete vidjeti stare podatke i eventualno ponovo poslati zahtjev (stari zahtjev će u tom slučaju biti zanemaren.</p><?
} else {
?>
	<p>Pozivamo Vas da podržite rad Studentske službe <?=$conf_skr_naziv_institucije_genitiv?> tako što ćete prijaviti sve eventualne greške u vašim ličnim podacima (datim ispod).</p><?
}

$q400 = db_query("select ime, prezime, brindexa, UNIX_TIMESTAMP(datum_rodjenja), mjesto_rodjenja, jmbg, drzavljanstvo, adresa, adresa_mjesto, telefon, kanton, spol, imeoca, prezimeoca, imemajke, prezimemajke, drzavljanstvo, nacionalnost, boracke_kategorije, slika from osoba where id=$userid");

// Spisak gradova
$q410 = db_query("select id,naziv, opcina, drzava from mjesto order by naziv");
$gradovir="<option></option>";
$gradovia="<option></option>";
while ($r410 = db_fetch_row($q410)) { 
	$gradovir .= "<option"; $gradovia .= "<option";
 	if ($r410[0]==db_result($q400,0,4)) { 
		$gradovir  .= " SELECTED"; 
		$mjestorvalue = $r410[1]; 
		$opcinar = $r410[2];
		$drzavar = $r410[3];
	}
 	if ($r410[0]==db_result($q400,0,8)) { $gradovia  .= " SELECTED"; $adresarvalue = $r410[1]; }
	$gradovir .= ">$r410[1]</option>\n";
	$gradovia .= ">$r410[1]</option>\n";
}


// Spisak opcina
$q420 = db_query("select id,naziv from opcina order by naziv");
$opciner="<option></option>";
while ($r420 = db_fetch_row($q420)) {
	$opciner .= "<option value=\"$r420[0]\"";
 	if ($r420[0]==$opcinar) { $opciner  .= " SELECTED";  }
	$opciner .= ">$r420[1]</option>\n";
}


// Spisak drzava
$q430 = db_query("select id,naziv from drzava order by naziv");
$drzaver="<option></option>";
$drzavlj="<option></option>";
while ($r430 = db_fetch_row($q430)) {
	$drzaver .= "<option value=\"$r430[0]\"";
 	if ($r430[0]==$drzavar) { $drzaver  .= " SELECTED";  }
	$drzaver .= ">$r430[1]</option>\n";
	$drzavlj .= "<option value=\"$r430[0]\"";
 	if ($r430[0]==db_result($q400,0,16)) { $drzavlj  .= " SELECTED";  }
	$drzavlj .= ">$r430[1]</option>\n";
}


// Spisak nacionalnosti
$q440 = db_query("select id,naziv from nacionalnost order by naziv");
$nacion="<option></option>";
while ($r440 = db_fetch_row($q440)) {
	$nacion .= "<option value=\"$r440[0]\"";
 	if ($r440[0]==db_result($q400,0,17)) { $nacion  .= " SELECTED";  }
	$nacion .= ">$r440[1]</option>\n";
}

// Spol
if (db_result($q400,0,11)=="M") $muskir = "CHECKED"; else $muskir="";
if (db_result($q400,0,11)=="Z") $zenskir = "CHECKED"; else $zenskir="";

// Pripadnik borackih kategorija
if (db_result($q400,0,18)==1) $boracke = "CHECKED"; else $boracke="";


?>
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
			image.src = "static/images/combobox_down.png";
		} else {
			list.style.visibility = 'hidden';
			image.src = "static/images/combobox_up.png";
		}
	}
	function comboBoxHide(elname) {
		var list = document.getElementById("comboBoxDiv_"+elname);
		var listsel = document.getElementById("comboBoxMenu_"+elname);
		if (list.style.visibility == 'visible' && listsel.focused==false) {
			list.style.visibility = 'hidden';
			image.src = "static/images/combobox_up.png";
		}
	}
	function comboBoxOptionSelected(elname) {
		var ib = document.getElementById(elname);
		var listsel = document.getElementById("comboBoxMenu_"+elname);
		
		ib.value = listsel.options[listsel.selectedIndex].value;
		comboBoxShowHide(elname);
	}
	</script>

	<!--script type="text/javascript" src="js/combo-box.js"></script-->

	<table border="0" width="600">
	<tr><td colspan="2" bgcolor="#999999"><font color="#FFFFFF">SLIKA:</font></td></tr>
	<tr><td colspan="2">Slika koju ovdje odaberete nije vaš "avatar" nego zvanična fotografija u formatu lične karte / pasoša koja ide u dokumentaciju fakulteta i vezuje se za vaše zvanične dokumente. Slika mora imati bijelu/svijetlu pozadinu. Molimo vas da pošaljete sliku zadovoljavajuće kvalitete radi lakšeg štampanja dokumenata.</td></tr>
	<?
	if (db_result($q400,0,19)=="") {
		print genform("POST", "a\"  enctype=\"multipart/form-data");
		?>
		<input type="hidden" name="subakcija" value="postavisliku">
		<tr><td colspan="2"><p><input type="file" name="slika"> <input type="submit" value="Dodaj sliku"></p></td></tr>
		</form>
		<?
	} else {
		?>
		<?=genform("POST")?>
		<input type="hidden" name="subakcija" value="obrisisliku">
		<tr><td colspan="2"><p>
		<img src="?sta=common/slika&osoba=<?=$userid?>"><br/>
		<input type="submit" value="Obriši sliku"><br></form>
		<?
		print genform("POST", "b\"  enctype=\"multipart/form-data");
		?>
		<input type="hidden" name="subakcija" value="postavisliku">
		<input type="file" name="slika"> <input type="submit" value="Promijeni sliku"></p></td></tr>
		</form>
		<?
	}
	?>
	<?=genform("POST")?>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td colspan="2" bgcolor="#999999"><font color="#FFFFFF">OSNOVNI PODACI:</font></td></tr>
	<tr><td>
		Ime:</td><td><input type="text" name="ime" value="<?=db_result($q400,0,0)?>" class="default">
	</td></tr>
	<tr><td>
		Prezime:</td><td><input type="text" name="prezime" value="<?=db_result($q400,0,1)?>" class="default">
	</td></tr>
	<tr><td>
		Spol:</td><td><input type="radio" name="spol" value="M" <?=$muskir?>> Muški &nbsp;&nbsp; <input type="radio" name="spol" value="Z" <?=$zenskir?>> Ženski
	</td></tr>

	<? if ($user_student) { ?>
	<tr><td>
		Broj indexa:</td><td><input type="text" name="brindexa" value="<?=db_result($q400,0,2)?>" class="default">
	</td></tr>
	<? } ?>
	<tr><td>
		JMBG:</td><td><input type="text" name="jmbg" value="<?=db_result($q400,0,5)?>" class="default">
	</td></tr>

	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td colspan="2" bgcolor="#999999"><font color="#FFFFFF">KONTAKT PODACI:</font></td></tr>
	<tr><td colspan="2">Ovi podaci će se koristiti kako bi Službe <?=$conf_skr_naziv_institucije_genitiv?> mogle lakše stupiti u kontakt s vama.</td></tr>

	</td></tr>
	<tr><td>
		Adresa (ulica i broj):</td><td><input type="text" name="adresa" value="<?=db_result($q400,0,7)?>" class="default">
	</td></tr>
	<tr><td>
		Adresa (mjesto):</td><td>
		<input type="text" name="adresa_mjesto" id="adresa_mjesto" value="<?=$adresarvalue?>" class="default" onKeyPress="comboBoxEdit(event, 'adresa_mjesto')" autocomplete="off" onBlur="comboBoxHide('adresa_mjesto')"><img src="static/images/combobox_up.png" width="19" height="18" onClick="comboBoxShowHide('adresa_mjesto')" id="comboBoxImg_adresa_mjesto" valign="bottom"> <img src="static/images/combobox_down.png" style="visibility:hidden">
		<!-- Rezultati pretrage primaoca -->
		<div id="comboBoxDiv_adresa_mjesto" style="position:absolute;visibility:hidden">
			<select name="comboBoxMenu_adresa_mjesto" id="comboBoxMenu_adresa_mjesto" size="10" onClick="comboBoxOptionSelected('adresa_mjesto')"><?=$gradovir?></select>
		</div>
	</td></tr>
	<tr><td>
		Kontakt telefon:</td><td><input type="text" name="telefon" value="<?=db_result($q400,0,9)?>" class="default">
	</td></tr>
	<?
	$q450 = db_query("select id, adresa, sistemska from email where osoba=$userid");
	?>
	<tr><td valign="top">
		Kontakt e-mail:</td><td>
		<?

		while($r450 = db_fetch_row($q450)) {
			?>
			<?
			if ($r450[2] == 0) {
				?>
				<input type="text" name="email<?=$r450[0]?>" value="<?=$r450[1]?>" class="default">
				<input type="submit" name="izmijeni_email<?=$r450[0]?>" value=" Izmijeni "> <input type="submit" name="obrisi_email<?=$r450[0]?>" value=" Obriši ">
				<?
			} else {
				print "<b>".$r450[1]."</b>";
			}
			print "<br>\n";
		}
		?>

		<input type="text" name="email_novi" class="default"> <input type="submit" name="dodaj_email" value=" Dodaj e-mail ">
	</td></tr>
	<tr><td colspan="2">Ovim putem ne možete promijeniti vašu <?=$conf_skr_naziv_institucije?> e-mail adresu! Možete postaviti neku drugu adresu (Gmail, Hotmail...) na koju želite da primate obavještenja pored vaše <?=$conf_skr_naziv_institucije?> adrese.</td></tr>


	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td colspan="2" bgcolor="#999999"><font color="#FFFFFF">LIČNI PODACI:</font></td></tr>
	<tr><td colspan="2">Ovi podaci će se koristiti za automatsko popunjavanje formulara i obrazaca. Podaci su preuzeti iz formulara koje ste popunili prilikom upisa na fakultet. Ovim putem preuzimate punu odgovornost za ispravnost podataka koje navedete u formularu ispod.</td></tr>
	<tr><td>
		Ime oca:</td><td><input type="text" name="imeoca" value="<?=db_result($q400,0,12)?>" class="default">
	</td></tr>
	<tr><td>
		Prezime oca:</td><td><input type="text" name="prezimeoca" value="<?=db_result($q400,0,13)?>" class="default">
	</td></tr>
	<tr><td>
		Ime majke:</td><td><input type="text" name="imemajke" value="<?=db_result($q400,0,14)?>" class="default">
	</td></tr>
	<tr><td>
		Prezime majke:</td><td><input type="text" name="prezimemajke" value="<?=db_result($q400,0,15)?>" class="default">
	</td></tr>
	<tr><td>
		Datum rođenja:<br/>
		(D.M.G)</td><td><input type="text" name="datum_rodjenja" value="<?
		if (db_result($q400,0,4)) print date("d. m. Y.", db_result($q400,0,3))?>" class="default">
	</td></tr>
	<tr><td>
		Mjesto rođenja:</td><td>
		<input type="text" name="mjesto_rodjenja" id="mjesto_rodjenja" value="<?=$mjestorvalue?>" class="default" onKeyPress="return comboBoxEdit(event, 'mjesto_rodjenja')" autocomplete="off" onBlur="comboBoxHide('mjesto_rodjenja')"><img src="static/images/combobox_up.png" width="19" height="18" onClick="comboBoxShowHide('mjesto_rodjenja')" id="comboBoxImg_mjesto_rodjenja" valign="bottom"> <img src="static/images/combobox_down.png" style="visibility:hidden">
		<!-- Rezultati pretrage primaoca -->
		<div id="comboBoxDiv_mjesto_rodjenja" style="position:absolute;visibility:hidden">
			<select name="comboBoxMenu_mjesto_rodjenja" id="comboBoxMenu_mjesto_rodjenja" size="10" onClick="comboBoxOptionSelected('mjesto_rodjenja')" onFocus="this.focused=true;" onBlur="this.focused=false;"><?=$gradovir?></select>
		</div>
	</td></tr>
	<tr><td>
		Općina rođenja:</td><td><select name="opcina_rodjenja" class="default"><?=$opciner?></select>
	</td></tr>
	<tr><td>
		Država rođenja:</td><td><select name="drzava_rodjenja" class="default"><?=$drzaver?></select>
	</td></tr>
	<tr><td>
		Nacionalnost:</td><td><select name="nacionalnost" class="default"><?=$nacion?></select>
	</td></tr>
	<tr><td>
		Kanton / regija:</td><td><?=db_dropdown("kanton",db_result($q400,0,10), "--Izaberite kanton--") ?> <br/>
	</td></tr>
	<tr><td>
		Državljanstvo:</td><td><select name="drzavljanstvo" class="default"><?=$drzavlj?></select>
	</td></tr>
	<!--tr><td>
		<input type="checkbox" name="borac" <?=$boracke?>> Dijete šehida / borca / pripadnik RVI
	</td></tr--> <!-- Privremeno zakomentarisano zbog boljeg rješavanja -->


	<? if ($user_student && false) { ?>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td colspan="2" bgcolor="#999999"><font color="#FFFFFF">PODACI O PRETHODNOM CIKLUSU STUDIJA:</font></td></tr>
	<tr><td>
		Završena srednja škola:</td><td><input type="text" name="srednja_skola" value="<?=db_result($q400,0,12)?>" class="default">
	</td></tr>
	<? } ?>


	</table>

	<input type="hidden" name="subakcija" value="potvrda">
	<input type="Submit" value=" Pošalji zahtjev "></form>

	<p>&nbsp;</p>
	<p>Klikom na dugme iznad biće poslan zahtjev koji službe <?=$conf_skr_naziv_institucije_genitiv?> trebaju da provjere i potvrde. Ovo može potrajati nekoliko dana. Molimo da budete strpljivi.</p>

	<?



}


?>
