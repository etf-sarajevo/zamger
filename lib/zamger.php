<?

// LIB/ZAMGER - funkcije koje se koriste u ZAMGER kodu

// v3.9.1.0 (2008/02/12) + Pocetak
// v3.9.1.1 (2008/03/07) + Dodana sekcija "arhiva" u studentski_meni
// v3.9.1.2 (2008/03/08) + userbox() prebacen na auth tabelu, nove slicice ;)
// v3.9.1.3 (2008/03/21) + gen_ldap_uid()
// v3.9.1.4 (2008/04/14) + Imena mjeseci malim
// v3.9.1.5 (2008/05/06) + zamgerlog(): popravljen logging za dogadjaje kada korisnik nije logiran
// v3.9.1.6 (2008/05/09) + studentski_meni(): arhivirani predmeti sortirani po godinama i semestrima, popravljen link za arhivu (cuva trenutno otvoreni modul)
// v3.9.1.7 (2008/08/27) + novi meni: horizontalni_meni(), koristimo tabelu osoba u gen_ldap_uid() i user_box()
// v3.9.1.8 (2008/09/03) + Dodano slovo 'a' u genitiv()
// v3.9.1.9 (2008/09/13) + Polje aktuelna u tabeli akademska_godina (studentski_meni()); sprjeceno otvaranje coolboxa ako slanje nije uspjelo
// v3.9.1.10 (2009/02/10) + Funkcija myquery prebacena ovdje radi logginga
// v4.0.0.0 (2009/02/19) + Release
// v4.0.9.1 (2009/04/02) + Tabela studentski_moduli preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.2 (2009/04/29) + studentski_meni(): sortiranje po semestrima je dovodilo da se vise puta ponavlja zaglavlje za svaki preneseni predmet (sa drugog semestra); dodajem akademsku godinu u linkove za malimeni
// v4.0.9.3 (2009/05/01) + studentski_meni(): Parametri modula student/predmet su sada predmet i ag; restruktuiran kod; nova struktura baze za studentske module
// v4.0.9.4 (2009/07/23) + Dodajem linkove na dokumente - merge
// v4.0.9.5 (2009/08/15) + Implementiram podrsku za parametar "nevidljiv" u registry-ju
// v4.0.9.6 (2009/09/03) + Sprijeceno slanje podataka iz coolboxa ako je prethodno slanje u toku
// v4.0.9.7 (2009/09/13) + Linkovi na koliziju i ugovor o ucenju; detalji dizajna studentskog menija (sugestije by Teo)
// v4.0.9.8 (2009/10/02) + Ispravke u coolboxu pruzaju bolju podrsku za razne browsere
// v4.0.9.9 (2009/10/07) + Ne insistiraj na kosoj crti kao znaku za brisanje u coolboxu



// Funkcija koja pretvara naša slova iz velikih u mala

function malaslova($string) {
	$slovo = substr($string,0,1);
	if ($slovo>='A' && $slovo<='Z') {
		$string = strtr($string, "ČĆŽŠĐ", "čćžšđ");
	} else {
		$string = substr($string,0,2).strtr(substr($string,2),"ČĆŽŠĐ","čćžšđ");
	}
	return $string;
}



// Vraća procentni ispis

function procenat($dio,$total) {
	if ($total==0) return "0.00%";
	return (intval($dio/$total*10000)/100)."%";
}


// Pokušava pogoditi spol na osnovu imena
//   Z = ženski, M = muški

function spol($ime) {
	if ($ime == "Ines" || $ime == "Iris") return "Z";
	if (substr($ime,strlen($ime)-1) == "a" && $ime != "Vanja" && $ime != "Peđa" && $ime != "Mirza" && $ime != "Feđa" && $ime != "Saša" && $ime != "Alija" && $ime != "Mustafa" && $ime != "Novica" && $ime != "Avdija" && $ime != "Zikrija")
		return "Z";
	else
		return "M";
}


// Vraća vokativ riječi (primitivno)

function vokativ($rijec,$spol) {
	if ($spol=="Z") return $rijec;
	$slovo = substr($rijec,strlen($rijec)-1);
	if ($slovo == "a" || $slovo == "e" || $slovo == "i" || $slovo == "o" || $slovo == "u" || $slovo == "k")
		return $rijec;
	else if ($slovo == "h")
		return substr($rijec,0,strlen($rijec)-1)."še";
	else if ($slovo == "g")
		return substr($rijec,0,strlen($rijec)-1)."že";
	else
		return $rijec."e";
}

// Vraća genitiv riječi (primitivno)

function genitiv($rijec,$spol) {
	$slovo = substr($rijec,strlen($rijec)-1);
	if ($slovo == "a")
		return substr($rijec,0,strlen($rijec)-1)."e";
	else
		return $rijec."a";
}


// Floating layer sa podacima o korisniku i loginom

function user_box() {
	global $user_nastavnik,$user_studentska,$user_siteadmin,$userid,$su;

	$q1 = myquery("select ime,prezime from osoba where id=$userid");

	if ($user_siteadmin) {
		$slika="admin.png";
	} else if ($user_studentska) {
		$slika="teta.png";
	} else if ($user_nastavnik) {
		$slika="tutor.png";
	} else {
		$slika="user.png";
	}
	
	$ime = mysql_result($q1,0,0);
	$prezime = mysql_result($q1,0,1);

	$unsu = "";
	if ($su>0) {
		$unsu = "<a href=\"?unsu=1\">UnSU</a> * ";
	}

?>

<div id="kocka" style="position:absolute;right:10px;top:55px">
	<table style="border:1px;border-style:solid"><tr><td>
	<img src="images/fnord.gif" width="200" height="1" alt="fnord"><br>
	<img src="images/16x16/<?=$slika?>" border="0" alt="fnord"> <?=$ime?> <?=$prezime?><br>
	<?=$unsu?><a href="?sta=common/inbox">Poruke</a> * <a href="?sta=common/profil">Profil</a> * <a href="?sta=logout">Odjava</a>
	</td></tr></table>
</div>

<?
}


// Prikazi skriveni IFRAME za AJAH i layer u kojem se ispisuju poruke 
// name i id IFRAMEa je $naziv, a layera je $naziv-info

function ajah_box() {
	global $zamger_ajah_inited;

	$naziv="zamger_ajah";
	if ($zamger_ajah_inited==1) return;
	$zamger_ajah_inited=1;

	?>

	<!--AJAH BOX -->
	<font color="#FF0000"><b><div id="<?=$naziv?>-info">&nbsp;</div></b></font>
	<iframe height="0" width="0" frameborder="0" name="<?=$naziv?>" id="<?=$naziv?>"></iframe>
	<script language="JavaScript">
	var zamger_ajah_sending=false;
	var zamger_ajah_success="";
	var zamger_ajah_fail="";
	
	function ajah_stop() {
		zamger_ajah_sending=false;
		var pozicija=frames['<?=$naziv?>'].document.body.innerHTML.indexOf("OK");
		if (pozicija!=-1) {
			document.getElementById("<?=$naziv?>-info").innerHTML="&nbsp;";
			eval(zamger_ajah_success);
		} else {
//			document.getElementById("<?=$naziv?>-info").innerHTML="Greška!";
document.getElementById("<?=$naziv?>-info").innerHTML=frames['<?=$naziv?>'].document.body.innerHTML;
			eval(zamger_ajah_fail);
		}
	}

	function ajah_start(url, fail, success) {
		if (zamger_ajah_sending) return; // semawhore
		zamger_ajah_sending=true;
		zamger_ajah_fail=fail;
		zamger_ajah_success=success;
		document.getElementById("<?=$naziv?>-info").innerHTML="Šaljem podatke na server...";
		frames['<?=$naziv?>'].location.replace (url);
	}
	</script>

<?
}


// Reimplementacija file_put_contents, za staru verziju PHPa

if (!function_exists('file_put_contents')) {
function file_put_contents($file,$tekst) {
	if (!($file = fopen($file,"w"))) return false;
	$bytes = fwrite($file,$tekst);
	fclose($file);
	return $bytes;
}
}


// Vrati odgovarajuću ikonu za fajl
// (Kandidat za prebacivanje u libvedran)

function getmimeicon($file) {
	$mimetypes = array(
		"text/x-c" => "source_c.png",
		"audio/mpeg" => "sound.png",
		"application/msword" => "document.png",
		"application/msword application/msword" => "document.png",
		"application/x-rar" => "zip.png",
		"application/x-tar" => "tar.png",
		"application/x-gzip" => "tar.png",
		"application/x-rpm" => "rpm.png",
		"text/plain" => "txt.png",
		"image/png" => "image.png",
		"image/gif" => "image.png",
		"image/jpeg" => "image.png",
		"text/plain" => "txt.png",
		"text/html" => "html.png",
		"application/pdf" => "pdf.png",
		"application/postscript" => "postscript.png",
		"video/quicktime" => "quicktime.png",
		"video/mp2p" => "video.png",
		"video/mpv" => "video.png",
		"application/x-zip" => "zip.png"
	);

	$mtekst = array(
		"text/x-c.cpp" => "source_cpp.png",
		"application/x-zip.odt" => "document.png",
		"application/x-zip.ods" => "spreadsheet.png",
		"application/x-zip.odg" => "vectorgfx.png",
		"application/x-zip.docx" => "document.png",
		"application/x-zip.xslx" => "spreadsheet.png",
		".svg" => "vectorgfx.png",
		".xls" => "spreadsheet.png",
		".html" => "html.png"
	);


	$file_output = `file -bi '$file'`;
	$file_output = str_replace("\n", "", $file_output);
	if (strstr($file_output, ";"))
		$file_output = substr($file_output, 0, strpos($file_output, ";"));
	if (strstr($file_output, ","))
		$file_output = substr($file_output, 0, strpos($file_output, ","));
	$ekst = $file_output . strrchr($file, ".");

	if ($mtekst[$ekst]) return $mtekst[$ekst];
	if ($mimetypes[$file_output]) return $mimetypes[$file_output];

	return "misc.png";
}


// Funkcija koja omogucuje dinamicko editovanje polja tabele

function cool_box($izvrsi) {

// Opis situacije:
// Zelja nam je da se promjena prihvati kada korisnik pritisne ENTER
// a ako samo klikne pored prozora da se promjena ponisti.

// U svim browserima pritisak na tipku ENTER ce pozvati metodu onchange,
// osim na IE7/IE8 gdje pritisak na ENTER ne proizvodi nikakav event ako
// nije definisana propisna forma. Iz tog razloga moramo koristiti 
// onkeypress da uhvatimo ENTER. Sto se tice klika pored, browseri ce
// pozvati onchange i/ili onblur redoslijedom koji je nemoguce 
// predvidjeti :( zato moramo izvrsiti istu akciju u oba.

// U svim testiranim browserima onkeypress ce se izvrsiti prije onchange
// i onblur. Ako nadjete izuzetak, prijavite bug.


?>

<!-- COOL BOX -->
<div id="coolbox" style="position:absolute;visibility:hidden"><input style="font-size:11px; border:1px solid red" type="text" size="3" onblur="coolboxclose()" onchange="coolboxclose()" onkeypress="coolboxkey(event)" id="coolboxedit"></div>

<script language="JavaScript">
var zamger_coolbox_origcaller=false;
var zamger_coolbox_origvalue=false;
var zamger_coolbox_submitted=false;

function coolboxopen(callobj) {
	if (zamger_coolbox_origcaller) return; // Box je već otvoren
	zamger_coolbox_submitted=false;
	zamger_coolbox_origcaller = callobj;
	zamger_coolbox_origvalue = callobj.innerHTML;

	// Nadji poziciju objekta
	var curleft = curtop = 0;
	var obj=callobj;
	if (obj.offsetParent) {
		do {
			curleft += obj.offsetLeft;
			curtop += obj.offsetTop;
		} while (obj = obj.offsetParent);
	}

	// postavi coolbox
	var coolbox = document.getElementById("coolbox");
	var coolboxedit = document.getElementById("coolboxedit");
	coolbox.style.visibility = 'visible';
	coolbox.style.left = curleft+2 + "px";
	coolbox.style.top = curtop+2 + "px";
	coolboxedit.style.width = (callobj.offsetWidth - 6) + "px"; // 6=padding
	coolboxedit.style.height = (callobj.offsetHeight - 6) + "px";
	coolboxedit.value = callobj.innerHTML;
	if (coolboxedit.value == "/") coolboxedit.value="";
	coolboxedit.focus();
}

function coolboxclose() {
	if (zamger_coolbox_submitted) return; // U toku je slanje na server
	if (!zamger_coolbox_origcaller) return; // Box nije otvoren
	var coolbox = document.getElementById("coolbox");
	var coolboxedit = document.getElementById("coolboxedit");
	coolbox.style.visibility = 'hidden';
	coolboxedit.blur();
	
	// Pošto se onblur/onchange izvršava poslije onkeypress, sada je
	// sigurno da poništimo vrijednost ove varijable, jer je u slučaju
	// klika pored kocke ništa neće poništiti!
	zamger_coolbox_origcaller = false; 
}

function coolboxsubmit() {
	if (zamger_ajah_sending) { // Detektujemo ispad mreže
		alert("Slanje u toku. Molimo sačekajte.");
		return;
	}
	if (!zamger_coolbox_origcaller) return;
	if (zamger_coolbox_submitted) return;
	zamger_coolbox_submitted=true;
	var coolbox = document.getElementById("coolbox");
	var coolboxedit = document.getElementById("coolboxedit");
	if (coolbox.style.visibility == 'hidden') return;
	if (coolboxedit.value == "") coolboxedit.value="/";
	coolbox.style.visibility = 'hidden';
	coolboxedit.blur();
	if (coolboxedit.value != zamger_coolbox_origvalue) {
		zamger_coolbox_origcaller.innerHTML = coolboxedit.value;
		<?=$izvrsi?>
	}
}

// Svrha ove funkcije je da uhvati ENTER tipku u IE7/IE8
function coolboxkey(e) {
	var coolboxedit = document.getElementById("coolboxedit");
	if (e.keyCode==13 && coolboxedit.value!=zamger_coolbox_origvalue) { 
		// Ne saljemo podatke ako nije doslo do promjene
		coolboxsubmit(); 
	}
}
</script>

<?
}


// DHTML combo box kontrola
// Upotreba: na početku dokumenta uključiti js/mycombobox.js
// Zatim po potrebi mycombobox($name, $value, $valueslist)
// - $name - string, jedinstveni DOM ID za combobox
// - $value - default vrijednost comboboxa
// - $valueslist - niz vrijednosti koje treba popuniti u combobox

function mycombobox($name, $value, $valueslist) {
	?>
	<input type="text" name="<?=$name?>" id="<?=$name?>" value="<?=$value?>" class="default" onKeyDown="return comboBoxEdit(event, '<?=$name?>')" autocomplete="off" onBlur="comboBoxHide('<?=$name?>')">
	<img src="images/cb_up.png" width="19" height="18" onClick="comboBoxShowHide('<?=$name?>')" id="comboBoxImg_<?=$name?>" valign="bottom"> <img src="images/cb_down.png" style="visibility:hidden">
	<!-- Rezultati pretrage primaoca -->
	<div id="comboBoxDiv_<?=$name?>" style="position:absolute;visibility:hidden">
		<select name="comboBoxMenu_<?=$name?>" id="comboBoxMenu_<?=$name?>" size="10" onClick="comboBoxOptionSelected('<?=$name?>')" onFocus="this.focused=true;" onBlur="this.focused=false;"><option></option><?
	foreach ($valueslist as $listitem) {
		print "<option";
		if ($value == $listitem) print " SELECTED";
		print ">$listitem</option>\n";
	}
	?></select>
	</div>
	<?
}


// "Mali meni" - koji se pokazuje u modulima za nastavnika, studentsku i site admin

function malimeni($fj) {

	global $sta, $registry;

	$sekcija = substr($sta, 0,strlen($sta)-strlen(strstr($sta,"/"))+1);

	if ($sekcija=="nastavnik/") {
		$predmet=intval($_REQUEST['predmet']);
		$ag=intval($_REQUEST['ag']);
		$dodaj="&predmet=$predmet&ag=$ag";
	}
	
	if ($predmet>0) {
		$q15 = myquery("SELECT tippredmeta FROM akademska_godina_predmet WHERE akademska_godina=$ag AND predmet=$predmet");
		$tippredmeta = mysql_result($q15,0,0);
	}

	?>
	<style>
		a.malimeni {color:#333399;text-decoration:none;}
		a:hover.malimeni {color:#333399;text-decoration:underline;}
	</style>
	<table width="100%" border="0" cellspacing="4" cellpadding="0">
		<tr><?
//		<!--td><img src="images/fnord.gif" width="10" height="1"></td>
//		</td-->
		?><td valign="top" width="200" align="left">
		<p>&nbsp;</p>
			<table border="0" cellspacing="0" cellpadding="0" width="100%">
		<?

	$k=0;
	if ($predmet==0 || $tippredmeta != 1000) 
	foreach ($registry as $r) {
		if($r[5] != 0) continue; // nevidljiv
		if (strstr($r[0],$sekcija)) { 
			if ($r[0]==$sta) $bgcolor="#eeeeee"; else $bgcolor="#ffffff";
			if ($r[0]=="nastavnik/zavrsni") continue; // Ovo se prikazuje samo ako je tippredmeta == 1000 - završni rad
			?><tr><td height="20" align="right" bgcolor="<?=$bgcolor?>" onmouseover="this.bgColor='#CCCCCC'" onmouseout="this.bgColor='<?=$bgcolor?>'">
				<a href="?sta=<?=$r[0]?><?=$dodaj?>" class="malimeni"><?=$r[1]?></a>
			</tr></tr>
			<tr><td>&nbsp;</td></tr>
			<?
		}
	}

	if ($sekcija=="nastavnik/") {
			?><tr><td height="20" align="right" bgcolor="<?=$bgcolor?>" onmouseover="this.bgColor='#CCCCCC'" onmouseout="this.bgColor='#ffffff'">
				<a href="?sta=saradnik/intro" class="malimeni">Nazad na spisak predmeta i grupa</a>
			</tr></tr>
			<?
	}


			?>
			</table>
		</td><!--td width="1" bgcolor="#000000"><img src="images/fnord.gif" width="1" height="1">
		</td-->
		<td width="50">&nbsp;</td>
		<td valign="top">
		<?
		eval($fj);
		?>
		</td>
	</tr></table><?

}


// Varijanta "horizontalni meni" za studentsku službu

function horizontalni_meni($fj) {

	global $sta, $registry;

	$sekcija = substr($sta, 0,strlen($sta)-strlen(strstr($sta,"/"))+1);

	if ($sekcija=="nastavnik/") {
		$predmet=intval($_REQUEST['predmet']);
		$dodaj="&predmet=$predmet";
	}

	?>
	&nbsp;</br>
	<style>
		a.malimeni {color:#333399;text-decoration:none;}
		a:hover.malimeni {color:#333399;text-decoration:underline;}
	</style>

	<table cellspacing="0" cellpadding="4" style="border:1px; border-style:solid; border-color:black; margin-left: 30px">
		<tr>
		<?

	$k=0;
	foreach ($registry as $r) {
		if($r[5] != 0) continue;
		if (strstr($r[0],$sekcija)) { 
			if ($r[0]==$sta) $bgcolor="#eeeeee"; else $bgcolor="#cccccc";
			?><td height="20" width="100" bgcolor="<?=$bgcolor?>" onmouseover="this.bgColor='#ffffff'" onmouseout="this.bgColor='<?=$bgcolor?>'">
				<a href="?sta=<?=$r[0]?><?=$dodaj?>" class="malimeni"><?=$r[2]?></a>
			</td>
			<?
		}
	}

	?>
	</tr></table>

	<div style="margin: 30px">
	<?
	eval($fj);
	?>
	</div>
	<?
}


// "Studentski meni" - prikazuje se u prozoru studenta

function studentski_meni($fj) {
	global $userid, $sta, $registry;

	// Parametri potrebni za Moodle integraciju
	global $conf_moodle, $conf_moodle_url, $conf_moodle_db, $conf_moodle_prefix, $conf_moodle_reuse_connection, $conf_moodle_dbhost, $conf_moodle_dbuser, $conf_moodle_dbpass;
	global $conf_dbhost, $conf_dbuser, $conf_dbpass, $conf_dbdb;

	// Koji od interesantnih registry modula su aktivni
	$modul_uou=$modul_kolizija=$modul_prijava=$modul_prosjek=$modul_anketa=0;
	foreach ($registry as $r) {
		if($r[5] != 0) continue; // nevidljiv
		if ($r[0]=="student/ugovoroucenju") $modul_uou=1;
		if ($r[0]=="student/kolizija") $modul_kolizija=1;
		if ($r[0]=="student/prijava_ispita") $modul_prijava=1;
		if ($r[0]=="student/prosjeci") $modul_prosjek=1;
		if ($r[0]=="student/anketa") $modul_anketa=1;
	}

	// Upit $q30 vraca predmete koje je student ikada slusao (arhiva=1) ili koje trenutno slusa (arhiva=0)
	$arhiva = intval($_REQUEST['sm_arhiva']);
	if ($arhiva==1) {
		$sem_ispis = "Arhivirani predmeti";
		$q30 = myquery("SELECT pk.id, p.naziv, pk.semestar, ag.naziv, p.id, ag.id, agp.tippredmeta
		FROM student_predmet as sp, ponudakursa as pk, predmet as p, akademska_godina as ag, akademska_godina_predmet as agp
		WHERE sp.student=$userid AND sp.predmet=pk.id AND pk.predmet=p.id AND pk.akademska_godina=ag.id AND ag.id=agp.akademska_godina AND p.id=agp.predmet
		ORDER BY ag.id, pk.semestar MOD 2 DESC, p.naziv");

	} else {
		// Aktuelna akademska godina
		$q10 = myquery("select id,naziv from akademska_godina where aktuelna=1");
		$ag = mysql_result($q10,0,0);

		// Studij koji student trenutno sluša
		$q20 = myquery("select studij,semestar from student_studij where student=$userid and akademska_godina=$ag order by semestar desc limit 1");
		if (mysql_num_rows($q20)<1) {
			$sem_ispis = "Niste upisani na studij!";
			$q30 = myquery("SELECT * from student_studij where 1=0"); // dummy upit koji ne vraca ništa
			// Može li ovo bolje!?
		} else {
			$studij = mysql_result($q20,0,0);
			$semestar = mysql_result($q20,0,1);

			// Određujemo da li je aktuelni semestar parni ili neparni
			$semestar=$semestar%2;
			if ($semestar==1)
				$sem_ispis = "Zimski semestar ";
			else
				$sem_ispis = "Ljetnji semestar ";
			$sem_ispis .= mysql_result($q10,0,1).":";

			$q30 = myquery("SELECT pk.id, p.naziv, pk.semestar, ag.naziv, p.id, ag.id, agp.tippredmeta
			FROM student_predmet as sp, ponudakursa as pk, predmet as p, akademska_godina as ag, akademska_godina_predmet as agp
			WHERE sp.student=$userid AND sp.predmet=pk.id AND pk.predmet=p.id AND pk.akademska_godina=$ag AND pk.semestar%2=$semestar AND pk.akademska_godina=ag.id AND agp.akademska_godina=$ag AND agp.predmet=p.id
			ORDER BY p.naziv");
		}
	}

	$ispis = '<table border="0" cellspacing="2" cellpadding="1">';
	$oldsem=$oldag=0; 

	// Glavna petlja za generisanje ispisa
	while ($r30 = mysql_fetch_row($q30)) {
		$ponudakursa = $r30[0];
		$predmet_naziv = $r30[1];
		$predmet = $r30[4];
		$pag = $r30[5];
		$zimskiljetnji = $r30[2]%2;
		$tippredmeta = $r30[6];

		// Zaglavlje sa imenom akademske godine i semestrom
		if ($zimskiljetnji!=$oldsem || $r30[3]!=$oldag) {
			if ($r30[2]%2==1)
				$ispis .= "<tr><td colspan=\"2\"><br/><img src=\"images/fnord.gif\" width=\"1\" height=\"2\"><br/><b>Zimski semestar ";
			else
				$ispis .= "<tr><td colspan=\"2\"><br/><img src=\"images/fnord.gif\" width=\"1\" height=\"2\"><br/><b>Ljetnji semestar ";
			$ispis .= $r30[3].":</b><br/><br/></td></tr>\n";
			$oldsem=$zimskiljetnji; $oldag=$r30[3];
		}

		// Ako je modul trenutno aktivan, boldiraj i prikaži meni
		if (intval($_REQUEST['predmet'])==$predmet && intval($_REQUEST['ag'])==$pag) {
			$ispis .= '<tr><td valign="top" style="padding-top:2px;"><img src="images/dole.png" align="bottom" border="0"></td>'."\n<td>";
			if ($tippredmeta == 1000)
				$ispis .= "<a href=\"?sta=student/zavrsni&predmet=$predmet&ag=$pag&sm_arhiva=$arhiva\">";
			else if ($_REQUEST['sta'] != "student/predmet")
				$ispis .= "<a href=\"?sta=student/predmet&predmet=$predmet&ag=$pag&sm_arhiva=$arhiva\">";
			$ispis .= "<b>$predmet_naziv</b>";
			if ($_REQUEST['sta'] != "student/predmet")
				$ispis .= "</a>";
			$ispis .= "<br/>\n";
			
			// Studentski moduli aktivirani za ovaj predmet
			$q40 = myquery("select sm.gui_naziv, sm.modul, sm.novi_prozor from studentski_modul as sm, studentski_modul_predmet as smp where smp.predmet=$predmet and smp.akademska_godina=$pag and smp.aktivan=1 and smp.studentski_modul=sm.id order by sm.id");
			while ($r40 = mysql_fetch_row($q40)) {
			$tip_forum="";
			if ($r40[0]=="Forum Komentari") $tip_forum="&tip=forum";
				if ($r40[1]==$_REQUEST['sta'])
					$ispis .= "&nbsp;&nbsp;&nbsp;&nbsp;$r40[0]<br/>\n";
				else if ($r40[2]==1)
					$ispis .= "&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"?sta=$r40[1]&predmet=$predmet&ag=$pag$tip_forum\" target=\"_blank\">$r40[0]</a><br/>\n";
				else
					$ispis .= "&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"?sta=$r40[1]&predmet=$predmet&ag=$pag&sm_arhiva=$arhiva\">$r40[0]</a><br/>\n";
			}

			// Da li ima aktivna anketa i da li je istekao rok?
			if ($modul_anketa) {
				$q42 = myquery("select UNIX_TIMESTAMP(datum_zatvaranja) from anketa_anketa where aktivna=1");
				if (mysql_num_rows($q42)!=0) { // da li uopce ima kreirana anketa ako ne , ne radi nista
					$rok=mysql_result($q42,0,0);
					if (time () < $rok) {
						$q42b =  myquery("select id from anketa_anketa a where a.aktivna=1");
						if(mysql_num_rows($q42b)>0)
							$ispis .= "&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"?sta=student/anketa&predmet=$predmet\">Anketa</a><br/>\n";
					}
				}
			}

			$ispis .= "</td></tr>\n";
		} else {
			if ($tippredmeta == 1000)
				$ispis .= '<tr><td valign="top" style="padding-top:2px;"><img src="images/lijevo.png" align="bottom" border="0"></td>'."\n<td><a href=\"?sta=student/zavrsni&predmet=$predmet&ag=$pag&sm_arhiva=$arhiva\">$predmet_naziv</a></td></tr>\n";
			else
				$ispis .= '<tr><td valign="top" style="padding-top:2px;"><img src="images/lijevo.png" align="bottom" border="0"></td>'."\n<td><a href=\"?sta=student/predmet&predmet=$predmet&ag=$pag&sm_arhiva=$arhiva\">$predmet_naziv</a></td></tr>\n";
		}
	}
	$ispis .= "</table>\n";


?>
	<table width="100%" border="0" cellspacing="4" cellpadding="0">
		<tr><td valign="top">
			<img src="images/fnord.gif" width="197" height="1"><br/><br/>
			<? if ($sta != "student/intro") { ?>
			<a href="?sta=student/intro">&lt;-- Nazad na početnu</a>
			<? } else { ?>&nbsp;<? } ?><?=$ispis?>
			
			<br />
			<? if ($arhiva==0) { ?>
			<a href="<?=genuri()?>&sm_arhiva=1">Prikaži arhivirane predmete</a>
			<? } else { ?>
			<a href="<?=genuri()?>&sm_arhiva=0">Sakrij arhivirane predmete</a>
			<? } ?>
			<br /><br />
			<img src="images/plus.png" width="13" height="13" id="img-dokumenti" onclick="daj_stablo('dokumenti')">
			<a href="#" onclick="daj_stablo('dokumenti'); return false;">Dokumenti</a><br />
			<div id="dokumenti" style="display:none">
				&nbsp;&nbsp;&nbsp; <a href="?sta=student/potvrda">Zahtjev za ovjereno uvjerenje</a> <i><font color="red">NOVO!</font></i><br />
				&nbsp;&nbsp;&nbsp; <a href="?sta=izvjestaj/index&student=<?=$userid?>">Uvjerenje o položenim predmetima</a><br />
				&nbsp;&nbsp;&nbsp; <a href="?sta=izvjestaj/progress&student=<?=$userid?>&razdvoji_ispite=1">Pregled ostvarenog rezultata</a><br />
				<? if ($modul_uou) { ?>
				&nbsp;&nbsp;&nbsp; <a href="?sta=student/ugovoroucenju">Ugovor o učenju</a><br />
				<? } ?>
				<? if ($modul_prijava) { ?>
				&nbsp;&nbsp;&nbsp; <a href="?sta=student/prijava_ispita">Prijava ispita</a><br />
				<? } ?>
				&nbsp;&nbsp;&nbsp; Promjena odsjeka <i><font color="red">USKORO!</font></i><br />
				<? if ($modul_kolizija) { ?>
				&nbsp;&nbsp;&nbsp; <a href="?sta=student/kolizija">Zahtjev za koliziju</a><br />
				<? } ?>
				<? if ($modul_prosjek) { ?>
				&nbsp;&nbsp;&nbsp; <a href="?sta=student/prosjeci">Prosjeci po godinama</a><br />
				<? } ?>
			</div>
			<br /><br />
			<?


	// Prikaz poruka sa Moodle foruma

	if (isset($_REQUEST['predmet']) && isset($_REQUEST['ag']) && $conf_moodle) {
		// Varijabla komentariforum postaje ID predmeta koji je izabran
		$komentariforum = $_REQUEST['predmet'];
		$predmet = intval($_REQUEST['predmet']);
		$ag = intval($_REQUEST['ag']);

		$qsm = myquery("select aktivan from studentski_modul_predmet where predmet=$predmet and akademska_godina=$ag");
		if (mysql_num_rows($qsm)>0) {
		$aktivan_provjera = mysql_result($qsm,0,0);
 
			if ($aktivan_provjera==1) {
				$q = myquery("select moodle_id from moodle_predmet_id where predmet=$predmet and akademska_godina=$ag");

				// Uzimanje Moodle_ID ako je predmet povezan sa moodle
				if (mysql_num_rows($q)>0) {
					$moodle_id = mysql_result($q,0,0);

					// Konekcija na bazu?
					if (!$conf_moodle_reuse_connection) {
						dbdisconnect();
						dbconnect2($conf_moodle_dbhost, $conf_moodle_dbuser, $conf_moodle_dbpass, $conf_moodle_db);
					}

					// Citanje komentara iz Moodle Baze
					$query3 = "SELECT * FROM $conf_moodle_db.$conf_moodle_prefix"."forum_discussions WHERE course=$moodle_id order by timemodified desc LIMIT 0,4";
					$rs3 = myquery($query3);
					?>
						<table border="0" cellspacing="2" cellpadding="1">
							<tr>
								<td colspan="2">
									<br/><img src="images/16x16/komentar-plavi.png"> <b>Predmet komentari:</b><br/>
								</td>
							</tr> 
							<tr>
								<td>
									<?
									$provjerakomentara=0;
									while ($numrows3=mysql_fetch_array($rs3))
									{
										$brojac=$brojac+1;
										$idkom=$numrows3['id'];
										$kurs=$numrows3['course'];
										$vrijeme=$numrows3['timemodified'];
										$naziv=$numrows3['name'];
										$forum=$numrows3['forum'];
										$query4 = "SELECT * FROM $conf_moodle_db.$conf_moodle_prefix"."forum WHERE id=$forum";
										$rs4 = myquery($query4);
										$numrows4=mysql_fetch_array($rs4);
										$naziv_foruma=$numrows4['name'];
										//Ako postoji komentar ispisi ga
										if(!empty($naziv)){
											$provjerakomentara++;									
											print '<div style="padding:5px"><img src="images/16x16/komentar.png"/> <a target="_blank" href="'.$conf_moodle_url.'mod/forum/discuss.php?d='.$idkom.'">'.$naziv.'</a><br> ['.$naziv_foruma.']<br></div>';
										}
									}
									if($provjerakomentara==0){
										print '<div style="padding:5px"><center>NEMA KOMENTARA!</a></center><br></div>';}
									?>
								</td>
							</tr> 
						</table> 
					<?

					// Vraćamo Zamger konekciju
					if (!$conf_moodle_reuse_connection) {
						dbdisconnect();
						dbconnect2($conf_dbhost, $conf_dbuser, $conf_dbpass, $conf_dbdb);
					}
				}
			}
		}
	}


	// Prikaz današnjeg datuma

	$dani = array("","Ponedjeljak", "Utorak", "Srijeda", "Četvrtak", "Petak", "Subota", "Nedjelja");
	$mjeseci = array("", "januar", "februar", "mart", "april", "maj", "juni", "juli", "avgust", "septembar", "oktobar", "novembar", "decembar");

	print $dani[date("N",time())];
	print ", ".date("j",time()).". ".$mjeseci[date("n",time())]." ".date("Y",time()).".";

	?>
		</td>
		<td width="1" bgcolor="#888888"><img src="images/fnord.gif" width="1" height="1"></td>
		<td width="5" bgcolor="#FFFFFF"><img src="images/fnord.gif" width="5" height="1"></td>
		<td width="100%" valign="top">
		<? eval ($fj); ?>
			</td></tr>
		</table>
	<?

}


// Logging

function zamgerlog($event,$nivo) {
	global $userid;

	// Brisemo gluposti iz eventa
	if (($k=strpos($event,"sta="))>0) $event=substr($event,$k+4);
	if (strstr($event,"MOODLEID_=")) $event=preg_replace("/MOODLEID_=([^&]*)/","",$event);
	$event = str_replace("&amp;"," ",$event);
	$event = str_replace("&"," ",$event);
	// sakrij sifru!
	$event=preg_replace("/pass=([^&]*)/","",$event);
	// brisemo PHPSESSID
	$event=preg_replace("/PHPSESSID=([^&]*)/","",$event);
	// brisemo tekstove poruka i sl.
	$event=preg_replace("/tekst=([^&]*)/","",$event);
	// brisemo suvisan tekst koji ubacuje mysql
	$event=str_replace("You have an error in your SQL syntax;","",$event);
	$event=str_replace("check the manual that corresponds to your MySQL server version for the right syntax to use","",$event);

	if (intval($userid)==0) $userid=0;

	myquery("insert into log set dogadjaj='".my_escape($event)."', userid=$userid, nivo=$nivo");
}


// Bilježenje poruke u log2 je nešto složenije
function zamgerlog2($tekst, $objekat1 = 0, $objekat2 = 0, $objekat3 = 0, $blob = "") {
	global $userid, $sta;

	$tekst = my_escape($tekst);
	$blob = my_escape($blob);
	if ($sta=="logout") $sta="";

	// Parametri objekat* moraju biti tipa int, pratimo sve drugačije pozive kako bismo ih mogli popraviti
	if ($objekat1 !== intval($objekat1) || $objekat2 !== intval($objekat2) || $objekat3 !== intval($objekat3)) {
		$q5 = myquery("INSERT INTO log2 SELECT 0,NOW(), ".intval($userid).", m.id, d.id, 0, 0, 0, '".my_escape($_SERVER['REMOTE_ADDR'])."' FROM log2_modul AS m, log2_dogadjaj AS d WHERE m.naziv='$sta' AND d.opis='poziv zamgerlog2 funkcije nije ispravan'");
		// Dodajemo blob
		$id = mysql_insert_id();
		$tekst_bloba = "";
		if ($objekat1 !== intval($objekat1)) $tekst_bloba .= "objekat1: $objekat1 ";
		if ($objekat2 !== intval($objekat2)) $tekst_bloba .= "objekat2: $objekat2 ";
		if ($objekat3 !== intval($objekat3)) $tekst_bloba .= "objekat3: $objekat3 ";

		$q7 = myquery("INSERT INTO log2_blob SET log2=$id, tekst='$tekst_bloba'");
		$objekat1 = intval($objekat1); $objekat2 = intval($objekat2); $objekat3 = intval($objekat3);
	}
	
	// $userid izgleda nekada može biti i prazan string?
	$q5 = myquery("INSERT INTO log2 SELECT 0,NOW(), ".intval($userid).", m.id, d.id, $objekat1, $objekat2, $objekat3, '".my_escape($_SERVER['REMOTE_ADDR'])."' FROM log2_modul AS m, log2_dogadjaj AS d WHERE m.naziv='$sta' AND d.opis='$tekst'");
	if (mysql_affected_rows() == 0) {
		// Nije ništa ubačeno, vjerovatno fale polja u tabelama
		$q10 = myquery("SELECT COUNT(*) FROM log2_modul WHERE naziv='$sta'");
		if (mysql_result($q10,0,0) == 0)
			// U ovim slučajevima će se pozvati zamgerlog2 sa invalidnim modulom
			if ($tekst == "login" || $tekst == "sesija istekla" || $tekst == "nepoznat korisnik")
				$sta == "";
			else
				$q20 = myquery("INSERT INTO log2_modul SET naziv='$sta'");

		$q30 = myquery("SELECT COUNT(*) FROM log2_dogadjaj WHERE opis='$tekst'");
		if (mysql_result($q30,0,0) == 0)
			// Neka admin manuelno u bazi definiše ako je događaj različitog nivoa od 2
			$q40 = myquery("INSERT INTO log2_dogadjaj SET opis='$tekst', nivo=2"); 

		$q50 = myquery("INSERT INTO log2 SELECT 0,NOW(), ".intval($userid).", m.id, d.id, $objekat1, $objekat2, $objekat3, '".my_escape($_SERVER['REMOTE_ADDR'])."' FROM log2_modul AS m, log2_dogadjaj AS d WHERE m.naziv='$sta' AND d.opis='$tekst'");
		// Ako sada nije uspjelo ubacivanje, nije nas briga :)
	}

	if ($blob !== "") {
		// Dodajemo blob
		$id = mysql_insert_id();
		$q60 = myquery("INSERT INTO log2_blob SET log2=$id, tekst='$blob'");
	}
}


// Ova funkcija definiše pravila za kreiranje UIDa za LDAP.
// Ispod je dato pravilo: prvo slovo imena + prvo slovo prezimena + broj indexa

function gen_ldap_uid($userid) {
	$q10 = myquery("select ime, prezime, brindexa from osoba where id=$userid");
	$ime = mysql_result($q10,0,0);
	$prezime = mysql_result($q10,0,1);
	$brindexa = mysql_result($q10,0,2);

	// Pretvorba naših slova Unicode -> ASCII (proširiti?)
	$debosn = array( 'Č'=>'c', 'č'=>'c', 'Ć'=>'c', 'ć'=>'c', 'Đ'=>'d', 'đ'=>'d', 'Š'=>'s', 'š'=>'s', 'Ž'=>'z', 'ž'=>'z');

	$sime = strtolower(substr($ime,0,1));
	if ($debosn[substr($ime,0,2)]) $sime=$debosn[substr($ime,0,2)];
	$sprezime = strtolower(substr($prezime,0,1));
	if ($debosn[substr($prezime,0,2)]) $sprezime=$debosn[substr($prezime,0,2)];

	if (strstr($brindexa,"/")) $brindexa = substr($brindexa,strpos($brindexa,"/")+1);

	return $sime.$sprezime.$brindexa;
}


function myquery($query) {
	global $_lv_, $conf_script_path;

	if ($r = @mysql_query($query)) {
		return $r;
	}
	
	# Error handling
	if ($_lv_["debug"])
		print "<br/><hr/><br/>MYSQL query:<br/><pre>".$query."</pre><br/>MYSQL error:<br/><pre>".mysql_error()."</pre>";
	$backtrace = debug_backtrace();
	$file = $backtrace[0]['file'];
	$file = str_replace($conf_script_path."/", "", $file);
	$line = intval($backtrace[0]['line']);

	$error = mysql_error();
	$error = str_replace("You have an error in your SQL syntax;", "", $error); 
	$error = str_replace("check the manual that corresponds to your MySQL server version for the right syntax to use", "", $error);
	zamgerlog("SQL greska ($file : $line): $error", 3);
	zamgerlog2("SQL greska", 0, 0, 0, "$file:$line: $error");
	exit;
}

// Vraca puni naziv osobe sa svim titulama
function tituliraj($osoba, $sa_akademskim_zvanjem = true, $sa_naucnonastavnim_zvanjem = true, $prezime_prvo = false) {
	$q10 = myquery("select ime, prezime, naucni_stepen, strucni_stepen from osoba where id=$osoba");
	if (!($r10 = mysql_fetch_row($q10))) {
		return "";
	}
	if ($prezime_prvo)
		$ime = $r10[1]." ".$r10[0];
	else
		$ime = $r10[0]." ".$r10[1];

	if ($r10[2]) {
		$q20 = myquery("select titula from naucni_stepen where id=$r10[2]");
		if ($r20 = mysql_fetch_row($q20))
			if ($prezime_prvo)
				$ime = $r10[1]." ".$r20[0]." ".$r10[0];
			else
				$ime = $r20[0]." ".$ime;
	}
	
	if ($sa_akademskim_zvanjem) {
		$q30 = myquery("select titula from strucni_stepen where id=$r10[3]");
		if ($r30 = mysql_fetch_row($q30))
			$ime = $ime.", ".$r30[0];
	}
	
	if ($sa_naucnonastavnim_zvanjem) {
		$q40 = myquery("select z.titula from izbor as i, zvanje as z where i.osoba=$osoba and i.zvanje=z.id and (i.datum_isteka>=NOW() or i.datum_isteka='0000-00-00')");
		if ($r40 = mysql_fetch_row($q40))
			$ime = $r40[0]." ".$ime;
	}

	return $ime;
}


?>
