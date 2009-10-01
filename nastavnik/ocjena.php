<?

// NASTAVNIK/OCJENA - masovni unos konacnih ocjena

// v3.9.1.0 (2008/02/19) + Preimenovan bivsi admin_predmet
// v3.9.1.1 (2008/02/28) + Koristim lib/manip
// v3.9.1.2 (2008/05/20) + Podignut logging nivo sa 2 na 4
// v3.9.1.3 (2008/08/27) + Dodana zastita od visestrukog slanja kod masovnog unosa
// v4.0.0.0 (2009/02/19) + Release
// v4.0.9.1 (2009/03/25) + nastavnik_predmet preusmjeren sa tabele ponudakursa na tabelu predmet
// v4.0.9.2 (2009/03/31) + Tabela konacna_ocjena preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.3 (2009/04/23) + Nastavnicki moduli sada primaju predmet i akademsku godinu (ag) umjesto ponudekursa
// v4.0.9.4 (2009/09/13) + Redizajniran ispis kod masovnog unosa, sugerisao: Zajko


function nastavnik_ocjena() {

global $userid,$user_siteadmin;

require("lib/manip.php");
global $mass_rezultat; // za masovni unos studenata u grupe



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

<p><h3><?=$predmet_naziv?> - Konačna ocjena</h3></p>

<?


# Masovni unos konačnih ocjena

if ($_POST['akcija'] == "massinput" && strlen($_POST['nazad'])<1 && check_csrf_token()) {

	if ($_POST['fakatradi'] != 1) $ispis=1; else $ispis=0; // fakatradi=0 --> ispis=1

	if ($ispis) {
		?>Akcije koje će biti urađene:<br/><br/>
		<?=genform("POST")?>
		<input type="hidden" name="fakatradi" value="1">
		<table border="0" cellspacing="1" cellpadding="2">
		<!-- FIXME: prebaciti stilove u CSS? -->
		<thead>
		<tr bgcolor="#999999">
			<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;color:white;">Prezime</font></td>
			<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;color:white;">Ime</font></td>
			<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;color:white;">Ocjena / Komentar</font></td>
		</tr>
		</thead>
		<tbody>
		<?
	}

	$greska=mass_input($ispis); // Funkcija koja parsira podatke

	if (count($mass_rezultat)==0) {
//		niceerror("Niste unijeli ništa.");
//		return;
		print "Niste unijeli nijedan upotrebljiv podatak<br/><br/>\n";
		$greska=1;
	}


	// Obrada rezultata

	$boja1 = "#EEEEEE";
	$boja2 = "#DDDDDD";
	$boja=$boja1;
	$bojae = "#FFE3DD";

	foreach ($mass_rezultat['ime'] as $student=>$ime) {
		$prezime = $mass_rezultat['prezime'][$student];
		$ocjena = $mass_rezultat['podatak1'][$student];

		// Student neocijenjen (prazno mjesto za ocjenu)
		if (intval($ocjena)==0 && strpos($ocjena,"0")===FALSE) {
			if ($ispis) {
				?>
				<tr bgcolor="<?=$boja?>">
					<td><?=$prezime?></td><td><?=$ime?></td>
					<td>nije ocijenjen/a (unesena je ocjena: <?=$ocjena?>)</td>
				</tr>
				<?
				if ($boja==$boja1) $boja=$boja2; else $boja=$boja1;
			}
			continue;
		}

		// Ocjena mora biti u opsegu 6-10
		$ocjena = intval($ocjena);
		if ($ocjena<6 || $ocjena>10) {
			if ($ispis) {
				?>
				<tr bgcolor="<?=$bojae?>">
					<td><?=$prezime?></td><td><?=$ime?></td>
					<td>ocjena nije u opsegu 6-10 (ocjena: <?=$ocjena?>)</td>
				</tr>
				<?
				$greska=1;
				continue;
			}
		}

		// Da li vec ima ocjena u bazi?
		$q100 = myquery("select ocjena from konacna_ocjena where student=$student and predmet=$predmet");
		if (mysql_num_rows($q100)>0) {
			if ($ispis) {
				$oc2 = mysql_result($q100,0,0);
				?>
				<tr bgcolor="<?=$bojae?>">
					<td><?=$prezime?></td><td><?=$ime?></td>
					<td>već ima ocjenu <?=$oc2?>; koristite pogled grupe za izmjenu</td>
				</tr>
				<?
				$greska=1;
				continue;
			}
		}

		if ($ispis) {
			?>
			<tr bgcolor="<?=$boja?>">
				<td><?=$prezime?></td><td><?=$ime?></td>
				<td>ocjena: <?=$ocjena?></td>
			</tr>
			<?
			if ($boja==$boja1) $boja=$boja2; else $boja=$boja1;
		} else {
			$q110 = myquery("insert into konacna_ocjena set student=$student, predmet=$predmet, akademska_godina=$ag, ocjena=$ocjena");
		}
	}

	if ($ispis) {
		if ($greska == 0) {
			?>
			</tbody></table>
			<p>Potvrdite upis ocjena ili se vratite na prethodni ekran.</p>
			<p><input type="submit" name="nazad" value=" Nazad "> <input type="submit" value=" Potvrda"></p>
			</form>
			<? 
		} else {
			?>
			</tbody></table>
			<p>U unesenim podacima ima grešaka. Da li ste izabrali ispravan format ("Prezime[TAB]Ime" vs. "Prezime Ime")? Vratite se nazad kako biste ovo popravili.</p>
			<p><input type="submit" name="nazad" value=" Nazad "></p>
			</form>
			<? 
		}
		return;
	} else {
		zamgerlog("masovno upisane ocjene na predmet pp$predmet",4);
		
		?>
		Ocjene su upisane.
		<script language="JavaScript">
		location.href='?sta=nastavnik/ocjena&predmet=<?=$predmet?>&ag=<?=$ag?>';
		</script>
		<?
	}
}




// Masovni unos konačnih ocjena

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


?><p><b>Masovni unos konačnih ocjena</b><br/>
<?=genform("POST")?>
<input type="hidden" name="fakatradi" value="0">
<input type="hidden" name="akcija" value="massinput">
<input type="hidden" name="nazad" value="">
<input type="hidden" name="brpodataka" value="1">
<input type="hidden" name="duplikati" value="0">

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
<?



}

?>