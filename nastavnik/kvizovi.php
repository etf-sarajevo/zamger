<?

// NASTAVNIK/KVIZOVI - kreiranje i administracija kvizova


function nastavnik_kvizovi() {

global $userid,$user_siteadmin;
global $_lv_;



// Parametri
$predmet = intval($_REQUEST['predmet']);
$ag = intval($_REQUEST['ag']);

// Naziv predmeta
$q5 = myquery("select naziv from predmet where id=$predmet");
if (mysql_num_rows($q5)<1) {
	biguglyerror("Nepoznat predmet");
	zamgerlog("ilegalan predmet $predmet",3); //nivo 3: greska
	zamgerlog2("nepoznat predmet", $predmet);
	return;
}
$predmet_naziv = mysql_result($q5,0,0);

// Da li korisnik ima pravo ući u modul?

if (!$user_siteadmin) {
	$q10 = myquery("select nivo_pristupa from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
	if (mysql_num_rows($q10)<1 || mysql_result($q10,0,0)=="asistent") {
		zamgerlog("nastavnik/ispiti privilegije (predmet pp$predmet)",3);
		zamgerlog2("nije nastavnik na predmetu", $predmet, $ag);
		biguglyerror("Nemate pravo pristupa ovoj opciji");
		return;
	} 
}



?>

<p>&nbsp;</p>

<p><h3><?=$predmet_naziv?> - Kvizovi</h3></p>

<p>Napomena: Ovaj modul je još uvijek u fazi razvoja i nije dovoljno testiran. Ne preporučujemo njegovo korištenje.</p>

<?



// Akcija - editovanje pitanja

if ($_REQUEST['akcija'] == "pitanja") {

	$kviz = intval($_REQUEST['kviz']);
	$q200 = myquery("select naziv, predmet, akademska_godina from kviz where id=$kviz");
	if (mysql_num_rows($q200)<1) {
		niceerror("Nepostojeći kviz $kviz");
		zamgerlog("editovanje pitanja: nepostojeci kviz $kviz", 3);
		zamgerlog2("nepostojeci kviz (editovanje pitanja)", $kviz);
		return;
	}
	if ((mysql_result($q200,0,1) != $predmet) || (mysql_result($q200,0,2) != $ag)) {
		niceerror("Kviz nije sa ovog predmeta");
		zamgerlog("editovanje pitanja: kviz $kviz nije sa predmeta pp$predmet ag$ag", 3);
		zamgerlog2("id kviza i predmeta se ne poklapaju (editovanje pitanja)", $predmet, $ag, $kviz);
		return;
	}
	$naziv_kviza = mysql_result($q200, 0, 0);

	// Subakcije
	if ($_REQUEST['subakcija'] == "potvrda_novo" && check_csrf_token()) {
		$tekst = my_escape($_REQUEST['tekst']);
		$bodova = floatval(str_replace(',', '.', $_REQUEST['bodova']));
		if ($_REQUEST['vidljivo']) $vidljivo=1; else $vidljivo=0;
		$tip = my_escape($_REQUEST['tip']);

		$q300 = myquery("insert into kviz_pitanje set kviz=$kviz, tip='$tip', tekst='$tekst', bodova=$bodova, vidljivo=$vidljivo");
		$pitanje = mysql_insert_id();

		// Ako je korisnik unosio odgovore prije kreiranja pitanja, njihov id pitanja je 0
		$q315 = myquery("update kviz_odgovor set kviz_pitanje=$pitanje where kviz_pitanje=0");

		nicemessage("Pitanje uspješno dodano");
		zamgerlog2("dodano pitanje na kviz", $pitanje);
		?>
		<script language="JavaScript">
		location.href='?sta=nastavnik/kvizovi&predmet=<?=$predmet?>&ag=<?=$ag?>&kviz=<?=$kviz?>&akcija=pitanja&subakcija=izmijeni&pitanje=<?=$pitanje?>';
		</script>
		<?
		return;
	}

	if ($_REQUEST['subakcija'] == "potvrda_izmjene" && check_csrf_token()) {
		$pitanje = intval($_REQUEST['pitanje']);
		$tekst = my_escape($_REQUEST['tekst']);
		$bodova = floatval(str_replace(',', '.', $_REQUEST['bodova']));
		if ($_REQUEST['vidljivo']) $vidljivo=1; else $vidljivo=0;
		$tip = my_escape($_REQUEST['tip']);

		$q320 = myquery("select kviz from kviz_pitanje where id=$pitanje");
		if (mysql_num_rows($q320)==0) {
			niceerror("Pitanje je obrisano!");
			zamgerlog("potvrda editovanja pitanja: pitanje $pitanje ne postoji", 3);
			zamgerlog2("pitanje na kvizu ne postoji (potvrda editovanja)", $pitanje);
			return;
		}
		if (mysql_result($q320,0,0) != $kviz) {
			niceerror("Pitanje nije sa ovog kviza");
			zamgerlog("potvrda editovanja pitanja: pitanje $pitanje nije sa kviza $kviz (pp$predmet ag$ag)", 3);
			zamgerlog2("id pitanja i kviza se ne poklapaju (potvrda editovanja)", $pitanje, $kviz);
			return;
		}

		$q330 = myquery("update kviz_pitanje set tekst='$tekst', tip='$tip', bodova=$bodova, vidljivo=$vidljivo where id=$pitanje");

		nicemessage("Pitanje uspješno izmijenjeno");
		zamgerlog2("izmijenjeno pitanje na kvizu", $pitanje);
		?>
		<script language="JavaScript">
		location.href='?sta=nastavnik/kvizovi&predmet=<?=$predmet?>&ag=<?=$ag?>&kviz=<?=$kviz?>&akcija=pitanja&subakcija=izmijeni&pitanje=<?=$pitanje?>';
		</script>
		<?
		return;
	}
	
	if ($_REQUEST['subakcija'] == "obrisi") { // brisanje pitanja - ovdje ce nam trebati potvrda!
		$pitanje = intval($_REQUEST['pitanje']);
		$q320 = myquery("select kviz from kviz_pitanje where id=$pitanje");
		if (mysql_num_rows($q320)==0) {
			niceerror("Pitanje je već obrisano!");
			zamgerlog("potvrda brisanja pitanja: pitanje $pitanje ne postoji", 3);
			zamgerlog2("pitanje ne postoji (potvrda brisanja)", $pitanje);
			return;
		}
		if (mysql_result($q320,0,0) != $kviz) {
			niceerror("Pitanje nije sa ovog kviza");
			zamgerlog("potvrda brisanja pitanja: pitanje $pitanje nije sa kviza $kviz (pp$predmet ag$ag)", 3);
			zamgerlog2("id pitanja i kviza se ne poklapaju (potvrda brisanja)", $pitanje, $kviz);
			return;
		}
		
		$q335 = myquery("delete from kviz_odgovor where kviz_pitanje=$pitanje");
		$q336 = myquery("delete from kviz_pitanje where id=$pitanje");

		nicemessage("Pitanje uspješno obrisano");
		zamgerlog2("obrisano pitanje sa kviza", $kviz, $pitanje);
		?>
		<script language="JavaScript">
		location.href='?sta=nastavnik/kvizovi&predmet=<?=$predmet?>&ag=<?=$ag?>&kviz=<?=$kviz?>&akcija=pitanja';
		</script>
		<?
		return;
	}

	if ($_REQUEST['subakcija'] == "dodaj_odgovor" && check_csrf_token()) {
		$pitanje = intval($_REQUEST['pitanje']);
		$tekst = my_escape($_REQUEST['tekst']);
		if ($_REQUEST['tacan']) $tacan=1; else $tacan=0;

		if ($pitanje>0) {
			$q320 = myquery("select kviz from kviz_pitanje where id=$pitanje");
			if (mysql_num_rows($q320)==0 || mysql_result($q320,0,0) != $kviz) {
				niceerror("Pitanje nije sa ovog kviza");
				zamgerlog("dodavanje odgovora: pitanje $pitanje nije sa kviza $kviz (pp$predmet ag$ag)", 3);
				zamgerlog2("id pitanja i kviza se ne poklapaju (dodavanje odgovora)", $pitanje, $kviz);
				return;
			}
		}

		$q340 = myquery("insert into kviz_odgovor set kviz_pitanje=$pitanje, tekst='$tekst', tacan=$tacan");

		nicemessage("Odgovor uspješno dodan");
		zamgerlog2("dodan odgovor na pitanje", mysql_insert_id());
		if ($pitanje>0) {
			?>
			<script language="JavaScript">
			location.href='?sta=nastavnik/kvizovi&predmet=<?=$predmet?>&ag=<?=$ag?>&kviz=<?=$kviz?>&akcija=pitanja&subakcija=izmijeni&pitanje=<?=$pitanje?>';
			</script>
			<?
		}
		else {
			?>
			<script language="JavaScript">
			location.href='?sta=nastavnik/kvizovi&predmet=<?=$predmet?>&ag=<?=$ag?>&kviz=<?=$kviz?>&akcija=pitanja';
			</script>
			<?
		}
		return;
	}

	if ($_REQUEST['subakcija'] == "obrisi_odgovor") { // && check_csrf_token()) {
		$odgovor = intval($_REQUEST['odgovor']);
		$q350 = myquery("select kp.kviz, kp.id from kviz_pitanje as kp, kviz_odgovor as ko where ko.id=$odgovor and ko.kviz_pitanje=kp.id");
		if (mysql_num_rows($q350)==0) {
			// Moguće da je odgovor dat prije pitanja
			$q355 = myquery("select kviz_pitanje from kviz_odgovor where id=$odgovor");
			if (mysql_num_rows($q355)==0) {
				niceerror("Odgovor je već obrisan!");
				zamgerlog("brisanje odgovora: odgovor $odgovor ne postoji", 3);
				zamgerlog2("odgovor ne postoji (brisanje odgovora)", $odgovor);
				return;
			} 
		}
		else if (mysql_result($q350,0,0) != $kviz) {
			niceerror("Odgovor ne postoji ili pitanje nije sa ovog kviza");
			zamgerlog("brisanje odgovora: odgovor $odgovor nije sa kviza $kviz (pp$predmet ag$ag)", 3);
			zamgerlog2("id odgovora i kviza se ne poklapaju (brisanje odgovora)", $odgovor, $kviz);
			return;
		}

		$q360 = myquery("delete from kviz_odgovor where id=$odgovor");

		nicemessage("Odgovor uspješno obrisan");
		$dodaj = "";
		if (mysql_num_rows($q350)!=0) { $dodaj = "&subakcija=izmijeni&pitanje=".mysql_result($q350,0,1); }
		zamgerlog2("obrisan odgovor sa kviza", $odgovor, $kviz);
		
		?>
		<script language="JavaScript">
		location.href='?sta=nastavnik/kvizovi&predmet=<?=$predmet?>&ag=<?=$ag?>&kviz=<?=$kviz?>&akcija=pitanja<?=$dodaj?>';
		</script>
		<?
		return;
	}
	
	if ($_REQUEST['subakcija'] == "toggle_tacnost") { // && check_csrf_token()) {
		$odgovor = intval($_REQUEST['odgovor']);
		$q370 = myquery("select kp.kviz, kp.id, ko.tacan from kviz_pitanje as kp, kviz_odgovor as ko where ko.id=$odgovor and ko.kviz_pitanje=kp.id");
		if (mysql_num_rows($q370)==0 || mysql_result($q370,0,0) != $kviz) {
			niceerror("Odgovor ne postoji ili pitanje nije sa ovog kviza");
			zamgerlog("toggle tacnost: odgovor $odgovor nije sa kviza $kviz (pp$predmet ag$ag)", 3);
			zamgerlog2("id odgovora i kviza se ne poklapaju (toggle tacnosti)", $odgovor, $kviz);
			return;
		}

		if (mysql_result($q370,0,2) == 1) $tacan=0; else $tacan=1;
		$q380 = myquery("update kviz_odgovor set tacan=$tacan where id=$odgovor");

		nicemessage("Odgovor proglašen za (ne)tačan");
		zamgerlog2("odgovor proglasen za (ne)tacan", $odgovor, $tacan);
		?>
		<script language="JavaScript">
		location.href='?sta=nastavnik/kvizovi&predmet=<?=$predmet?>&ag=<?=$ag?>&kviz=<?=$kviz?>&akcija=pitanja&subakcija=izmijeni&pitanje=<?=mysql_result($q370,0,1)?>';
		</script>
		<?
		return;
	}

	if ($_REQUEST['subakcija'] == "kopiraj_pitanja" && check_csrf_token()) {
		$drugi_kviz = intval($_REQUEST['_lv_column_kviz']);
		$q740 = myquery("SELECT naziv FROM kviz WHERE id=$drugi_kviz"); // Dozvoljavamo kopiranje sa kviza sa drugog predmeta!?
		if (mysql_num_rows($q740) == 0) {
			niceerror("Nepoznat kviz");
			zamgerlog2("nepoznat ID kviza", $drugi_kviz);
			return;
		}
		$q700 = myquery("SELECT id, tip, tekst, bodova, vidljivo FROM kviz_pitanje WHERE kviz=$drugi_kviz");
		while ($r700 = mysql_fetch_row($q700)) {
			$staro_pitanje = $r700[0];
			$tekst = mysql_real_escape_string($r700[2]);
			
			$q710 = myquery("INSERT INTO kviz_pitanje SET kviz=$kviz, tip='$r700[1]', tekst='$tekst', bodova=$r700[3], vidljivo=$r700[4]");
			$novo_pitanje = mysql_insert_id();
			
			// Kreiranje odgovora na pitanje
			$q720 = myquery("SELECT tekst, tacan, vidljiv FROM kviz_odgovor WHERE kviz_pitanje=$staro_pitanje");
			while ($r720 = mysql_fetch_row($q720)) {
				$tekst = mysql_real_escape_string($r720[0]);
				$q730 = myquery("INSERT INTO kviz_odgovor SET kviz_pitanje=$novo_pitanje, tekst='$tekst', tacan=$r720[1], vidljiv=$r720[2]");
			}
		}

		nicemessage("Prekopirana pitanja sa kviza");
		zamgerlog2("prekopirana pitanja sa kviza", $kviz, $drugi_kviz);
		?>
		<script language="JavaScript">
		location.href='?sta=nastavnik/kvizovi&predmet=<?=$predmet?>&ag=<?=$ag?>&kviz=<?=$kviz?>&akcija=pitanja';
		</script>
		<?
		return;
	}

	?>
	<h3>Izmjena pitanja za kviz "<?=$naziv_kviza?>"</h3>
	<a href="?sta=nastavnik/kvizovi&predmet=<?=$predmet?>&ag=<?=$ag?>&_lv_nav_id=<?=$kviz?>">Nazad na podešavanje parametara kviza</a><br><br>
	<table border="0" cellspacing="1" cellpadding="2">
	<tr bgcolor="#999999">
		<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;color:white;">R.br.</font></td>
		<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;color:white;">Tekst pitanja</font></td>
		<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;color:white;">Odgovori</font></td>
		<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;color:white;">Bodova</font></td>
		<td><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;color:white;">Vidljivo?</font></td>
		<td>&nbsp;</td>
	</tr>
	<?

	$rbr=0;
	$q210 = myquery("select id, tip, tekst, bodova, vidljivo from kviz_pitanje where kviz=$kviz");
	while ($r210 = mysql_fetch_row($q210)) {
		// Pribavljamo odgovore
		$odgovori = "";
		$q220 = myquery("select tekst, tacan from kviz_odgovor where kviz_pitanje=$r210[0] order by tacan desc");
		if (mysql_num_rows($q220)<1)
			$odgovori = "<font color=\"red\">Nema ponuđenih odgovora</font>";
		$broj_tacnih = 0;
		while ($r220 = mysql_fetch_row($q220)) {
			$odgovori .= "'$r220[0]'";
			if ($r220[1]==1) { $odgovori .= " (*)"; $broj_tacnih++; }
			$odgovori .= ", ";
		}
		if (mysql_num_rows($q220)>0 && $broj_tacnih==0) {
			$odgovori = "<font color=\"red\">Nije ponuđen tačan odgovor</font><br>\n".$odgovori;
		}
		else if (mysql_num_rows($q220)>0 && $r210[1]=='mcma' && $broj_tacnih==1) {
			$odgovori = "<font color=\"red\">Ponuđen je samo jedan tačan odgovor</font><br>\n".$odgovori;
		}

		$vidljivo="NE";
		if ($r210[4]==1) $vidljivo="DA";

		$rbr++;
		?>
		<tr>
			<td><?=$rbr?></td>
			<td><?=$r210[2]?></td>
			<td><?=$odgovori?></td>
			<td><?=$r210[3]?></td>
			<td><?=$vidljivo?></td>
			<td><a href="?sta=nastavnik/kvizovi&predmet=<?=$predmet?>&ag=<?=$ag?>&kviz=<?=$kviz?>&akcija=pitanja&subakcija=obrisi&pitanje=<?=$r210[0]?>">Obriši</a> *
			<a href="?sta=nastavnik/kvizovi&predmet=<?=$predmet?>&ag=<?=$ag?>&kviz=<?=$kviz?>&akcija=pitanja&subakcija=izmijeni&pitanje=<?=$r210[0]?>">Izmijeni</a></td>
		</tr>
		<?
	}

	print "</table>\n<br><br>\n";
	if (mysql_num_rows($q210)==0) {
		print genform("POST");
		?>
		<input type="hidden" name="subakcija" value="kopiraj_pitanja">
		<p>Kopiraj pitanja sa kviza:<?
		$_lv_["where:predmet"] = $predmet;
		$_lv_["where:akademska_godina"] = $ag;
		print db_dropdown("kviz");
		?>
		<input type="submit" value=" Kreni ">
		</p></form><?
	}

	if ($_REQUEST['subakcija']=="izmijeni") {
		?>
		<a href="?sta=nastavnik/kvizovi&predmet=<?=$predmet?>&ag=<?=$ag?>&kviz=<?=$kviz?>&akcija=pitanja">Dodaj novo pitanje</a><br><br>
		
		<a name="izmjena"></a>
		<b>Izmjena pitanja</b><br>
		<?

		$pitanje = intval($_REQUEST['pitanje']);
		$q230 = myquery("select kviz, tip, tekst, bodova, vidljivo from kviz_pitanje where id=$pitanje");
		if (mysql_num_rows($q230)<1) {
			niceerror("Nepostojeće pitanje $pitanje");
			zamgerlog("editovanje pitanja: nepostojece pitanje $pitanje", 3);
			zamgerlog2("nepostojece pitanje (editovanje pitanja)", $pitanje);
			return;
		}
		if (mysql_result($q230,0,0) != $kviz) {
			niceerror("Pitanje nije sa ovog kviza");
			zamgerlog("editovanje pitanja: pitanje $pitanje nije sa kviza $kviz (pp$predmet ag$ag)", 3);
			zamgerlog2("id pitanja i kviza se ne poklapaju (editovanje pitanja)", $pitanje, $kviz);
			return;
		}
		$tip = mysql_result($q230,0,1);
		$tekst = mysql_result($q230,0,2);
		$bodova = mysql_result($q230,0,3);
		if (mysql_result($q230,0,4)==1) $vidljivo = "CHECKED"; else $vidljivo = "";
		$subakcija="potvrda_izmjene";
	} else {
		print "<b>Dodajte novo pitanje</b><br>\n";
		$tekst = $vidljiv = "";
		$bodova = $pitanje = 0;
		$tip = "mcsa";
		$subakcija="potvrda_novo";
	}
	unset($_REQUEST['subakcija']);
	unset($_GET['subakcija']);
	
	?>
	<?=genform("POST");?>
	<input type="hidden" name="subakcija" value="<?=$subakcija?>">
	<input type="hidden" name="pitanje" value="<?=$pitanje?>">
	<table border="0">
		<tr><td>Tekst pitanja:</td><td><input type="text" size="50" name="tekst" value="<?=$tekst?>"></td></tr>
		<tr><td>Bodova:</td><td><input type="text" size="5" name="bodova" value="<?=$bodova?>"></td></tr>
		<tr><td>Tip pitanja:</td><td>
			<select name="tip">
				<option value="mcsa" <? if ($tip=="mcsa") print "SELECTED" ?>>MCSA</option>
				<option value="mcma" <? if ($tip=="mcma") print "SELECTED" ?>>MCMA</option>
				<option value="tekstualno" <? if ($tip=="tekstualno") print "SELECTED" ?>>Tekstualno</option>
			</select>
			<a href="#" onclick="javascript:window.open('legenda-pitanja.html','blah6','width=320,height=300');">Legenda tipova pitanja</a>
		</td></tr>
		<tr><td align="right"><input type="checkbox" name="vidljivo" value="1" <?=$vidljivo?>></td><td>Pitanje vidljivo</td></tr>
	</table>
	<br>Ponuđeni odgovori:<br>
	<ul>
	<?
	$q240 = myquery("select id, tekst, tacan, vidljiv from kviz_odgovor where kviz_pitanje=$pitanje");
	if (mysql_num_rows($q240)==0)
		print "<li>Do sada nije unesen nijedan odgovor</li>\n";
	while ($r240 = mysql_fetch_row($q240)) {
		print "<li>";
		if ($r240[3]==0) print "<font color=\"#AAAAAA\">";
		print $r240[1];
		if ($r240[2] == 1) { print " (TAČAN)"; $toggle_link = "Proglasi za netačan"; }
		else { $toggle_link = "Proglasi za tačan"; }
		if ($r240[3]==0) print "</font> - nevidljiv";
		?> - <a href="?sta=nastavnik/kvizovi&predmet=<?=$predmet?>&ag=<?=$ag?>&kviz=<?=$kviz?>&akcija=pitanja&subakcija=obrisi_odgovor&odgovor=<?=$r240[0]?>">Obriši</a>
		- <a href="?sta=nastavnik/kvizovi&predmet=<?=$predmet?>&ag=<?=$ag?>&kviz=<?=$kviz?>&akcija=pitanja&subakcija=toggle_tacnost&odgovor=<?=$r240[0]?>"><?=$toggle_link?></a></li>
		<?
	}

	?>
	</ul>
	<input type="submit" value="Promjena pitanja"><br>
	</form>
	<br>
	Dodajte odgovor na ovo pitanje:<br>
	<?=genform("POST");?>
	<input type="hidden" name="subakcija" value="dodaj_odgovor">
	<input type="hidden" name="pitanje" value="<?=$pitanje?>">
	Tekst odgovora: <input type="text" name="tekst" size="50"><br>
	<input type="checkbox" name="tacan" value="1"> Tačan<br>
	<input type="submit" value="Dodaj"><br>
	</form>
	<?

	return;
}



// Akcija - statistički pregled rezultata kviza

if ($_REQUEST['akcija'] == "rezultati") {
	$kviz = intval($_REQUEST['kviz']);
	$q600 = myquery("select naziv, predmet, akademska_godina, broj_pitanja, prolaz_bodova from kviz where id=$kviz");
	if (mysql_num_rows($q600)<1) {
		niceerror("Nepostojeći kviz $kviz");
		zamgerlog("editovanje pitanja: nepostojeci kviz $kviz", 3);
		zamgerlog2("nepostojeci kviz (editovanje pitanja)", $kviz);
		return;
	}
	if ((mysql_result($q600,0,1) != $predmet) || (mysql_result($q600,0,2) != $ag)) {
		niceerror("Kviz nije sa ovog predmeta");
		zamgerlog("editovanje pitanja: kviz $kviz nije sa predmeta pp$predmet ag$ag", 3);
		zamgerlog2("id kviza i predmeta se ne poklapaju (editovanje pitanja)", $predmet, $ag, $kviz);
		return;
	}
	$naziv_kviza = mysql_result($q600, 0, 0);
	$max_bodova = mysql_result($q600, 0, 3);
	$prolaz_bodova = mysql_result($q600, 0, 4);
	
	$broj_bodova = array();
	$ukupno = $max_broj = $ukupno_prolaz = 0;
	for ($i=0; $i<=$max_bodova; $i++) {
		$q620 = myquery("SELECT COUNT(*) FROM kviz_student WHERE kviz=$kviz AND dovrsen=1 AND bodova>=$i AND bodova<".($i+1));
		$broj_bodova[$i] = mysql_result($q620,0,0);
		$ukupno += $broj_bodova[$i];
		if ($broj_bodova[$i] > $max_broj) $max_broj = $broj_bodova[$i];
		if ($i>=$prolaz_bodova) $ukupno_prolaz += $broj_bodova[$i];
	}
	
	$q630 = myquery("SELECT COUNT(*) FROM kviz_student WHERE kviz=$kviz AND dovrsen=0");
	$nedovrsenih = mysql_result($q630,0,0);

	?>
	<p>Popunilo kviz: <b><?=$ukupno?></b> studenata<br />
	Nisu dovršili popunjavanje kviza: <b><?=$nedovrsenih?></b> studenata<br />
	Ostvarilo prolazne bodove: <b><?=$ukupno_prolaz?></b> studenata (<?=procenat($ukupno_prolaz, $ukupno)?>)</p>
	
	<h3><?=$naziv_kviza?></h3>
	<h4>Distribucija bodova</h4>
	<div id="grafik">
		<div style="width:300px;height:200px;margin:5px;">
			<?
			foreach ($broj_bodova as $bod => $broj) {
				if($broj==0) $broj_pixela_print =170;
				else {
					$broj_pixela = ($broj/$max_broj)*200;
					$broj_pixela_print = intval(200-$broj_pixela);
				}
				if ($bod < $prolaz_bodova) $boja="red"; else $boja="green";
				?>
				<div style="width:45px; height:200px; background:<?=$boja?>;margin-left:5px;float:left;">
					<div style="width:45px;height:<?=$broj_pixela_print?>px;background:white;">&nbsp;</div>
					<span style="color:white;font-size: 25px; text-align: center; ">&nbsp;<?=$bod?></span>
				</div>	
				<?
			}
		?>
		</div>
		<div style="width:300px;height:50px;margin:5px;">
			<?
			foreach ($broj_bodova as $bod => $broj) {
				?>
				<div style="width:45px; margin-left:5px; text-align: center; float:left; ">
					<?=$broj?> (<?=procenat($broj, $ukupno)?>)
				</div>
				<?
			}
			?>
		</div>
	</div>
	<?
	
	// Statistika pitanja
	
	?>
	<h3>Statistika pitanja</h3>
	<table border="1" style="border-collapse:collapse">
	<tr><th>Pitanje</th><th>Uk. odgovora</th><th>Tačnih</th></tr>
	<?
	
		
	$q640 = myquery("SELECT id, tekst, ukupno, tacnih FROM kviz_pitanje WHERE kviz=$kviz ORDER BY tacnih/ukupno");
	while ($r640 = mysql_fetch_row($q640)) {
		$id_pitanja = $r640[0];
		$pitanje = $r640[1];
		if (strlen($pitanje) > 60)
			$skr_pitanje = mb_substr($pitanje,0,50)."...";
		else
			$skr_pitanje = $pitanje;
		$odgovora = $r640[2];
		$tacnih = $r640[3];
		?>
		<tr>
			<td title="<?=$pitanje?>">
			<a href="?sta=nastavnik/kvizovi&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>&amp;kviz=<?=$kviz?>&amp;akcija=pitanja&amp;subakcija=izmijeni&amp;pitanje=<?=$id_pitanja?>#izmjena"><?=$skr_pitanje?></a></td>
			<td><?=$odgovora?></td>
			<td><?=$tacnih?> (<?=procenat($tacnih, $odgovora)?>)</td>
		</tr>
		<?
	}

	?>
	</table>
	<?

	return;
}


// Kopiranje kvizova sa prošlogodišnjeg predmeta

if ($_REQUEST['akcija'] === "prosla_godina" && strlen($_POST['nazad'])<1) {
	$old_ag = $ag-1; // Ovo je po definiciji prošla godina
	$greska = false;
	
	$q499 = myquery("SELECT naziv FROM akademska_godina WHERE id=$old_ag");
	if (mysql_num_rows($q499) == 0) {
		niceerror("Nije pronađena prošla akademska godina.");
		zamgerlog("nije pronadjena akademska godina $old_ag");
		zamgerlog2("nije pronadjena akademska godina", $old_ag);
		$greska = true;
	}
	
	if (!$greska) {
		$q500 = myquery("SELECT naziv FROM kviz WHERE predmet=$predmet AND akademska_godina=$old_ag");
		if (mysql_num_rows($q500) == 0) {
			niceerror("Prošle godine nije bio definisan nijedan kviz");
			zamgerlog("prosle godine nije bio definisan nijedan kviz $predmet $old_ag");
			zamgerlog2("prosle godine nije bio definisan nijedan kviz", $predmet, $old_ag);
			$greska = true;
		}
	}
	
	if (!$greska && $_REQUEST['potvrda'] === "potvrdjeno" && check_csrf_token()) {
		$q510 = myquery("SELECT id, naziv, vrijeme_pocetak, vrijeme_kraj, ip_adrese, prolaz_bodova, broj_pitanja, trajanje_kviza, aktivan FROM kviz WHERE predmet=$predmet AND akademska_godina=$old_ag");
		while ($r510 = mysql_fetch_row($q510)) {
			// Kreiranje novog kviza
			$stari_kviz = $r510[0];
			print "<p>Kopiram kviz $r510[1]...</p>";
			$naziv = mysql_real_escape_string($r510[1]);
			
			$q520 = myquery("INSERT INTO kviz SET naziv='$naziv', predmet=$predmet, akademska_godina=$ag, vrijeme_pocetak='$r510[2]', vrijeme_kraj='$r510[3]', ip_adrese='$r510[4]', prolaz_bodova=$r510[5], broj_pitanja=$r510[6], trajanje_kviza=$r510[7], aktivan=$r510[8]");
			$novi_kviz = mysql_insert_id();
			
			// Kreiranje pitanja
			$q530 = myquery("SELECT id, tip, tekst, bodova, vidljivo FROM kviz_pitanje WHERE kviz=$stari_kviz");
			while ($r530 = mysql_fetch_row($q530)) {
				$staro_pitanje = $r530[0];
				$tekst = mysql_real_escape_string($r530[2]);
				
				$q540 = myquery("INSERT INTO kviz_pitanje SET kviz=$novi_kviz, tip='$r530[1]', tekst='$tekst', bodova=$r530[3], vidljivo=$r530[4]");
				$novo_pitanje = mysql_insert_id();
				
				// Kreiranje odgovora na pitanje
				$q550 = myquery("SELECT tekst, tacan, vidljiv FROM kviz_odgovor WHERE kviz_pitanje=$staro_pitanje");
				while ($r550 = mysql_fetch_row($q550)) {
					$tekst = mysql_real_escape_string($r550[0]);
					$q560 = myquery("INSERT INTO kviz_odgovor SET kviz_pitanje=$novo_pitanje, tekst='$tekst', tacan=$r550[1], vidljiv=$r550[2]");
				}
			}
		}
		nicemessage("Kopiranje završeno!");
		print "<a href=\"?sta=nastavnik/kvizovi&predmet=$predmet&ag=$ag\">Povratak na stranicu kvizova</a>\n";
		return;
	}
	
	else if (!$greska) {
		nicemessage("Kopiram sljedeće kvizove iz akademske ".mysql_result($q499,0,0).". godine.");
		print "\n<ul>\n";
		while ($r500 = mysql_fetch_row($q500)) {
			print "<li>$r500[0]</li>\n";
		}
		print "</ul>\n";
		print genform("POST");
		?>
		<input type="hidden" name="potvrda" value="potvrdjeno">
		<p>Da li ste sigurni?</p>
		<p><input type="submit" name="nazad" value=" Nazad "> <input type="submit" value=" Potvrda"></p>
		</form>
		<?
	}
	return;
}


// Korektno brisanje kviza

if ($_REQUEST['_lv_action_delete']) {
	$kviz = intval($_REQUEST['_lv_column_id']);
	$q200 = myquery("select naziv, predmet, akademska_godina from kviz where id=$kviz");
	if (mysql_num_rows($q200)<1) {
		niceerror("Nepostojeći kviz $kviz");
		zamgerlog("brisanje kviza: nepostojeci kviz $kviz", 3);
		zamgerlog2("nepostojeci kviz (brisanje kviza)", $kviz);
		return;
	}
	if ((mysql_result($q200,0,1) != $predmet) || (mysql_result($q200,0,2) != $ag)) {
		niceerror("Kviz nije sa ovog predmeta");
		zamgerlog("brisanje kviza: kviz $kviz nije sa predmeta pp$predmet ag$ag", 3);
		zamgerlog2("id kviza i predmeta se ne poklapaju (brisanje kviza)", $predmet, $ag, $kviz);
		return;
	}
	
	$q400 = myquery("select id from kviz_pitanje where kviz=$kviz");
	// Brisemo odgovore
	while ($r400 = mysql_fetch_row($q400)) {
		$q410 = myquery("delete from kviz_odgovor where kviz_pitanje=$r400[0]");
	}
	$q420 = myquery("delete from kviz_pitanje where kviz=$kviz");
	$q430 = myquery("delete from kviz_student where kviz=$kviz");
	// db_form() će pobrisati stavku iz tabele kviz
	zamgerlog2("obrisan kviz", $predmet, $ag, $kviz);
}


// Provjeravamo da li je raspon dobro unesen

if (($_REQUEST['_lv_action'] == "edit" || $_REQUEST['_lv_action'] == "add") && !$_REQUEST['_lv_action_delete']) {
	$ip_adresa_losa = false;

	if ($_REQUEST['_lv_action'] == "edit") {
		$id_kviza = intval($_REQUEST['_lv_column_id']);
		// Dodajemo logging
		zamgerlog("izmijenjen kviz $id_kviza (pp$predmet)", 2);
		zamgerlog2("izmijenjen kviz", $id_kviza);
	} else {
		$labgrupa = intval($_REQUEST['_lv_column_labgrupa']);
		$naziv = my_escape($_REQUEST['_lv_column_naziv']);
		$pb = floatval($_REQUEST['_lv_column_prolaz_bodova']);
		$q100 = myquery("select id from kviz where predmet=$predmet and akademska_godina=$ag and naziv='$naziv' and labgrupa=$labgrupa and prolaz_bodova=$pb");
		$id_kviza = mysql_result($q100,0,0);
		zamgerlog("dodan novi kviz $id_kviza (pp$predmet)", 2);
		zamgerlog2("dodan kviz", $id_kviza);
	}

	$ip_adrese = $_REQUEST['_lv_column_ip_adrese'];

	foreach (explode(",", $ip_adrese) as $blok) {
		if (strstr($blok, "/")) { // blok adresa u CIDR formatu
			list ($baza, $maska) = explode("/", $blok);
			if ($baza != long2ip(ip2long($baza))) { $ip_adresa_losa = true; break; }
			if ($maska != intval($maska)) { $ip_adresa_losa = true; break; }
			if ($maska<1 || $maska>32) { $ip_adresa_losa = true; break; }
		}
		else if (strstr($blok, "-")) { // raspon adresa sa crticom
			list ($pocetak, $kraj) = explode("-", $blok);
			if ($pocetak != long2ip(ip2long($pocetak))) { $ip_adresa_losa = true; break; }
			if ($kraj != long2ip(ip2long($kraj))) { $ip_adresa_losa = true; break; }
		}
		else { // pojedinačna adresa
			if ($blok != long2ip(ip2long($blok))) { $ip_adresa_losa = true; break; }
		}
	}

	// Vraćamo se na editovanje lošeg kviza
	if ($ip_adresa_losa) {
		$_REQUEST['_lv_nav_id'] = $id_kviza;
		$_GET['_lv_nav_id'] = $id_kviza;
		$_POST['_lv_nav_id'] = $id_kviza;

		niceerror("Neispravan format IP adrese");
		?>
		<p>Raspon IP adresa treba biti u jednom od formata:<br>
		- CIDR format (npr. 123.45.67.89/24)<br>
		- raspon početak-kraj sa crticom (npr. 123.45.67.89-123.45.67.98)<br>
		- pojedinačna adresa<br>
		Takođe možete navesti više raspona ili pojedinačnih adresa razdvojenih zarezom.</p>
		<?
	}
}


// Spisak postojećih kvizova

$_lv_["where:predmet"] = $predmet;
$_lv_["where:akademska_godina"] = $ag;
$_lv_["new_link"] = "Unos novog kviza";

print "Odaberite neki od postojećih kvizova koji želite administrirati:<br/>\n";
print db_list("kviz");

$q1000 = myquery("SELECT COUNT(*) FROM kviz WHERE predmet=$predmet AND akademska_godina=$ag");
if (mysql_result($q1000,0,0) == 0)
	print "<p><a href=\"?sta=nastavnik/kvizovi&predmet=$predmet&ag=$ag&akcija=prosla_godina\">Prekopiraj kvizove sa prošle akademske godine</a></p>\n";

print "<hr>\n";
$kviz = intval($_REQUEST['_lv_nav_id']);
if ($kviz > 0) {
	?>
	<h3>Izmjena kviza</h3>
	<ul>
		<li><a href="?sta=nastavnik/kvizovi&predmet=<?=$predmet?>&ag=<?=$ag?>&kviz=<?=$kviz?>&akcija=pitanja">Izmijenite pitanja na kvizu</a></li>
		<li><a href="?sta=nastavnik/kvizovi&predmet=<?=$predmet?>&ag=<?=$ag?>&kviz=<?=$kviz?>&akcija=rezultati">Rezultati kviza (do sada poslani odgovori)</a></li>
	</ul>
	<?
} else {
	?>
	<h3>Kreiranje novog kviza</h3>
	<p>Unesite podatke o novom kvizu koji želite kreirati:</p><br>
	<?
}


$_lv_["label:vrijeme_pocetak"] = "Početak";
$_lv_["label:vrijeme_kraj"] = "Kraj";
$_lv_["label:labgrupa"] = "Samo za studente iz grupe";
$_lv_["label:ip_adrese"] = "Ograniči na IP adrese";
$_lv_["label:prolaz_bodova"] = "Minimum bodova za prolaz";
$_lv_["label:trajanje_kviza"] = "Trajanje kviza (u sekundama)";
$_lv_["hidden:predmet"] = 1;
$_lv_["hidden:akademska_godina"] = 1;
print db_form("kviz", "kvizform");


// Markiramo loše polje
if ($ip_adresa_losa) {
	?>
	<script>
	var element = document.getElementsByName('_lv_column_ip_adrese');
	element[0].style.backgroundColor = "#FF9999";
	element[0].focus();
	element[0].select();
	</script>
	<?
}

}

?>