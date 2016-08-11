<?

// LIB/ZAMGER - funkcije koje se koriste u ZAMGER kodu



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

function genitiv($rijec,$spol='?') {
	if ($spol == '?') $spol = spol($rijec); // Ovo se ustvari ne koristi? FIXME
	$slovo = substr($rijec,strlen($rijec)-1);
	if ($slovo == "a")
		return substr($rijec,0,strlen($rijec)-1)."e";
	else
		return $rijec."a";
}


// Floating layer sa podacima o korisniku i loginom

function user_box() {
	global $user_nastavnik,$user_studentska,$user_siteadmin,$userid,$su;

	$user = db_query_assoc("select ime,prezime from osoba where id=$userid");

	if ($user_siteadmin) {
		$slika="admin.png";
	} else if ($user_studentska) {
		$slika="teta.png";
	} else if ($user_nastavnik) {
		$slika="tutor.png";
	} else {
		$slika="user.png";
	}
	
	$unsu = "";
	if ($su>0) {
		$unsu = "<a href=\"?unsu=1\">UnSU</a> * ";
	}

?>

<div id="kocka" style="position:absolute;right:10px;top:55px">
	<table style="border:1px;border-style:solid"><tr><td>
	<img src="images/fnord.gif" width="200" height="1" alt="fnord"><br>
	<img src="images/16x16/<?=$slika?>" border="0" alt="fnord"> <?=$user['ime']?> <?=$user['prezime']?><br>
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


function ajax_box() {
	?>
	<script language="JavaScript">
	function ajax_start(url, method, params, cb_success, cb_fail) {
		cb_fail = cb_fail || ajax_log_error;
		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {
			if (xhttp.readyState == 4 && xhttp.status == 200) {
				try {
					var object = JSON.parse(xhttp.responseText);
					if (object['success'] === 'true')
						cb_success(object['data']);
					else
						cb_fail(xhttp.responseText, xhttp.status, url);
				} catch(e) {
					cb_fail(xhttp.responseText, xhttp.status, url);
				}
			} else if (xhttp.readyState == 4) {
				cb_fail(xhttp.responseText, xhttp.status, url);
			}
		};
		
		// Zamger URL
		if (url.indexOf("://") == -1) {
			params['sta'] = url;
			url = "index.php";
		}
		
		var encode_params = "";
		for (var key in params) {
			if (params.hasOwnProperty(key)) {
				if (encode_params != "") encode_params += "&";
				encode_params += encodeURIComponent(key) + "=" + encodeURIComponent(params[key]);
			}
		}
		
		if (method == "POST") {
			xhttp.open("POST", url, true);
			xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhttp.send(encode_params);
		} else {
			if (encode_params != "") url = url + "?" + encode_params;
			xhttp.open(method, url, true);
			xhttp.send();
		}
	}
	// Default funkcija za neuspjeh, logira greške
	function ajax_log_error(responseText, status, url) {
		if (status != 200) {
			console.log("Web servis "+url+" vratio status "+status);
			return;
		}
		try {
			var object = JSON.parse(responseText);
			console.log("Neuspio upit na web servis "+url+": ["+object['code']+"] "+object['message']);
		} catch(e) {
			console.log("Web servis "+url+" nije vratio validan JSON: "+xhttp.responseText);
			console.log(e);
		}
	}
	</script>
	<?php
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

	$predmet = 0; $dodaj = "";
	if ($sekcija=="nastavnik/") {
		$predmet = int_param('predmet');
		$ag = int_param('ag');
		$dodaj="&predmet=$predmet&ag=$ag";
	}
	
	if ($predmet>0) {
		$q15 = db_query("SELECT tippredmeta FROM akademska_godina_predmet WHERE akademska_godina=$ag AND predmet=$predmet");
		$tippredmeta = db_result($q15,0,0);
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
		if(count($r) < 5 || $r[5] != 0) continue; // nevidljiv
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

	$dodaj = "";
	if ($sekcija=="nastavnik/") {
		$predmet = int_param('predmet');
		$dodaj = "&predmet=$predmet";
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
		if(count($r) < 5 || $r[5] != 0) continue;
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

	// Koji od interesantnih registry modula su aktivni
	$modul_uou=$modul_kolizija=$modul_prijava=$modul_prosjek=$modul_anketa=0;
	foreach ($registry as $r) {
		if(count($r)<5 || $r[5] != 0) continue; // nevidljiv
		if ($r[0]=="student/ugovoroucenju") $modul_uou=1;
		if ($r[0]=="student/kolizija") $modul_kolizija=1;
		if ($r[0]=="student/prijava_ispita") $modul_prijava=1;
		if ($r[0]=="student/prosjeci") $modul_prosjek=1;
		if ($r[0]=="student/anketa") $modul_anketa=1;
	}

	// Aktuelna akademska godina
	$q10 = db_query("select id,naziv from akademska_godina where aktuelna=1");
	$ag = db_result($q10,0,0);

	// Upit $q30 vraca predmete koje je student ikada slusao (arhiva=1) ili koje trenutno slusa (arhiva=0)
	$arhiva = int_param('sm_arhiva');
	if ($arhiva==1) {
		$sem_ispis = "Arhivirani predmeti";
		$q30 = db_query("SELECT pk.id, p.naziv, pk.semestar, ag.naziv, p.id, ag.id, agp.tippredmeta
		FROM student_predmet as sp, ponudakursa as pk, predmet as p, akademska_godina as ag, akademska_godina_predmet as agp
		WHERE sp.student=$userid AND sp.predmet=pk.id AND pk.predmet=p.id AND pk.akademska_godina=ag.id AND ag.id=agp.akademska_godina AND p.id=agp.predmet
		ORDER BY ag.id, pk.semestar MOD 2 DESC, p.naziv");

	} else {
		// Studij koji student trenutno sluša
		$q20 = db_query("select studij,semestar from student_studij where student=$userid and akademska_godina=$ag order by semestar desc limit 1");
		if (db_num_rows($q20)<1) {
			$sem_ispis = "Niste upisani na studij!";
			$q30 = db_query("SELECT * from student_studij where 1=0"); // dummy upit koji ne vraca ništa
			// Može li ovo bolje!?
		} else {
			$studij = db_result($q20,0,0);
			$semestar = db_result($q20,0,1);

			// Određujemo da li je aktuelni semestar parni ili neparni
			$semestar=$semestar%2;
			if ($semestar==1)
				$sem_ispis = "Zimski semestar ";
			else
				$sem_ispis = "Ljetnji semestar ";
			$sem_ispis .= db_result($q10,0,1).":";

			$q30 = db_query("SELECT pk.id, p.naziv, pk.semestar, ag.naziv, p.id, ag.id, agp.tippredmeta
			FROM student_predmet as sp, ponudakursa as pk, predmet as p, akademska_godina as ag, akademska_godina_predmet as agp
			WHERE sp.student=$userid AND sp.predmet=pk.id AND pk.predmet=p.id AND pk.akademska_godina=$ag AND pk.semestar%2=$semestar AND pk.akademska_godina=ag.id AND agp.akademska_godina=$ag AND agp.predmet=p.id
			ORDER BY p.naziv");
		}
	}

	$ispis = '<table border="0" cellspacing="2" cellpadding="1">';
	$oldsem=$oldag=0; 

	// Glavna petlja za generisanje ispisa
	while ($r30 = db_fetch_row($q30)) {
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
		if (int_param('predmet')==$predmet && int_param('ag')==$pag) {
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
			$q40 = db_query("select sm.gui_naziv, sm.modul, sm.novi_prozor from studentski_modul as sm, studentski_modul_predmet as smp where smp.predmet=$predmet and smp.akademska_godina=$pag and smp.aktivan=1 and smp.studentski_modul=sm.id order by sm.id");
			while ($r40 = db_fetch_row($q40)) {
				if ($r40[1]==$_REQUEST['sta'])
					$ispis .= "&nbsp;&nbsp;&nbsp;&nbsp;$r40[0]<br/>\n";
				else if ($r40[2]==1)
					$ispis .= "&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"?sta=$r40[1]&predmet=$predmet&ag=$pag\" target=\"_blank\">$r40[0]</a><br/>\n";
				else
					$ispis .= "&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"?sta=$r40[1]&predmet=$predmet&ag=$pag&sm_arhiva=$arhiva\">$r40[0]</a><br/>\n";
			}

			// Da li postoji anketa za dati predmet ili sve predmete u trenutnom semestru?
			if ($modul_anketa) {
				$q42 = db_query("select a.id, a.naziv, ap.aktivna from anketa_anketa as a, anketa_predmet as ap where ap.anketa=a.id and a.akademska_godina=$pag and (ap.predmet=$predmet or ap.predmet IS NULL) and ap.semestar=$zimskiljetnji");
				if (db_num_rows($q42) == 1) { // Samo jedna anketa, dajemo link pod nazivom "Rezultati ankete"
					$ispis .= "&nbsp;&nbsp;&nbsp;&nbsp;";
					if ($_REQUEST['sta'] != "student/anketa")
						$ispis .= "<a href=\"?sta=student/anketa&anketa=".db_result($q42,0,0)."&predmet=$predmet&ag=$pag&sm_arhiva=$arhiva\">";
					$ispis .= "Rezultati ankete";
					if ($_REQUEST['sta'] != "student/anketa") $ispis .= "</a>";
					$ispis .= "<br/>\n";
				} else {
					while ($r42 = db_fetch_row($q42)) {
						$ispis .= "&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"?sta=student/anketa&anketa=$r42[0]&predmet=$predmet&ag=$pag&sm_arhiva=$arhiva\">$r42[1]</a><br/>\n";
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
			<? if ($arhiva == 1) { ?>
			<a href="<?=genuri()?>&sm_arhiva=0">Sakrij arhivirane predmete</a>
			<? } else { ?>
			<a href="<?=genuri()?>&sm_arhiva=1">Prikaži arhivirane predmete</a>
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

	db_query("insert into log set dogadjaj='".db_escape($event)."', userid=$userid, nivo=$nivo");
}


// Bilježenje poruke u log2 je nešto složenije
function zamgerlog2($tekst, $objekat1 = 0, $objekat2 = 0, $objekat3 = 0, $blob = "") {
	global $userid, $sta;

	$tekst = db_escape($tekst);
	$blob = db_escape($blob);
	if ($sta=="logout") $sta="";

	// Parametri objekat* moraju biti tipa int, pratimo sve drugačije pozive kako bismo ih mogli popraviti
	if ($objekat1 !== intval($objekat1) || $objekat2 !== intval($objekat2) || $objekat3 !== intval($objekat3)) {
		$q5 = db_query("INSERT INTO log2 SELECT 0,NOW(), ".intval($userid).", m.id, d.id, 0, 0, 0, '".db_escape($_SERVER['REMOTE_ADDR'])."' FROM log2_modul AS m, log2_dogadjaj AS d WHERE m.naziv='$sta' AND d.opis='poziv zamgerlog2 funkcije nije ispravan'");
		// Dodajemo blob
		$id = db_insert_id(); // Zašto se dešava da $id bude nula???
		$tekst_bloba = "";
		if ($objekat1 !== intval($objekat1)) $tekst_bloba .= "objekat1: $objekat1 ";
		if ($objekat2 !== intval($objekat2)) $tekst_bloba .= "objekat2: $objekat2 ";
		if ($objekat3 !== intval($objekat3)) $tekst_bloba .= "objekat3: $objekat3 ";

		$q7 = db_query("INSERT INTO log2_blob SET log2=$id, tekst='$tekst_bloba'");
		$objekat1 = intval($objekat1); $objekat2 = intval($objekat2); $objekat3 = intval($objekat3);
	}
	
	// $userid izgleda nekada može biti i prazan string?
	$q5 = db_query("INSERT INTO log2 SELECT 0,NOW(), ".intval($userid).", m.id, d.id, $objekat1, $objekat2, $objekat3, '".db_escape($_SERVER['REMOTE_ADDR'])."' FROM log2_modul AS m, log2_dogadjaj AS d WHERE m.naziv='$sta' AND d.opis='$tekst'");
	if (db_affected_rows() == 0) {
		// Nije ništa ubačeno, vjerovatno fale polja u tabelama
		$ubaceno = db_get("SELECT COUNT(*) FROM log2_modul WHERE naziv='$sta'");
		if ($ubaceno == 0)
			// U ovim slučajevima će se pozvati zamgerlog2 sa invalidnim modulom
			if ($tekst == "login" || $tekst == "sesija istekla" || $tekst == "nepoznat korisnik")
				$sta == "";
			else
				$q20 = db_query("INSERT INTO log2_modul SET naziv='$sta'");

		$ubaceno = db_get("SELECT COUNT(*) FROM log2_dogadjaj WHERE opis='$tekst'");
		if ($ubaceno == 0)
			// Neka admin manuelno u bazi definiše ako je događaj različitog nivoa od 2
			$q40 = db_query("INSERT INTO log2_dogadjaj SET opis='$tekst', nivo=2"); 

		$q50 = db_query("INSERT INTO log2 SELECT 0,NOW(), ".intval($userid).", m.id, d.id, $objekat1, $objekat2, $objekat3, '".db_escape($_SERVER['REMOTE_ADDR'])."' FROM log2_modul AS m, log2_dogadjaj AS d WHERE m.naziv='$sta' AND d.opis='$tekst'");
		// Ako sada nije uspjelo ubacivanje, nije nas briga :)
	}

	if ($blob !== "") {
		// Dodajemo blob
		$id = db_insert_id();
		$q60 = db_query("INSERT INTO log2_blob SET log2=$id, tekst='$blob'");
	}
}


// Ova funkcija definiše pravila za kreiranje UIDa za LDAP.
// Ispod je dato pravilo: prvo slovo imena + prvo slovo prezimena + broj indexa

function gen_ldap_uid($userid) {
	$q10 = db_query("select ime, prezime, brindexa from osoba where id=$userid");
	$ime = db_result($q10,0,0);
	$prezime = db_result($q10,0,1);
	$brindexa = db_result($q10,0,2);

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
	$q10 = db_query("select ime, prezime, naucni_stepen, strucni_stepen from osoba where id=$osoba");
	if (!($r10 = db_fetch_row($q10))) {
		return "";
	}
	if ($prezime_prvo)
		$ime = $r10[1]." ".$r10[0];
	else
		$ime = $r10[0]." ".$r10[1];

	if ($r10[2]) {
		$q20 = db_query("select titula from naucni_stepen where id=$r10[2]");
		if ($r20 = db_fetch_row($q20))
			if ($prezime_prvo)
				$ime = $r10[1]." ".$r20[0]." ".$r10[0];
			else
				$ime = $r20[0]." ".$ime;
	}
	
	if ($sa_akademskim_zvanjem) {
		$q30 = db_query("select titula from strucni_stepen where id=$r10[3]");
		if ($r30 = db_fetch_row($q30))
			$ime = $ime.", ".$r30[0];
	}
	
	if ($sa_naucnonastavnim_zvanjem) {
		$q40 = db_query("select z.titula from izbor as i, zvanje as z where i.osoba=$osoba and i.zvanje=z.id and (i.datum_isteka>=NOW() or i.datum_isteka='0000-00-00')");
		if ($r40 = db_fetch_row($q40))
			$ime = $r40[0]." ".$ime;
	}

	return $ime;
}


// Funkcija za konverziju arapskih brojeva u rimske, bazirana na nečemu što sam našao na php.net
function rimski_broj($arapski_broj = '') { 
	if ($arapski_broj == '') { $arapski_broj = date("Y"); } // Po defaultu vraća trenutnu godinu
	$arapski_broj          = intval($arapski_broj); 
	$arapski_broj_text     = "$arapski_broj"; 
	$arapski_broj_duzina   = strlen($arapski_broj_text); 

	// Ne postoje rimski brojevi van opsega [1,4999]
	if ($arapski_broj > 4999 || $arapski_broj < 1) { return false; } 

	// Ne postoji rimska cifra za nulu
	$rimske_cifre_jedinice = array('', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX');
	$rimske_cifre_desetice = array('', 'X', 'XX', 'XXX', 'XL', 'L', 'LX', 'LXX', 'LXXX', 'XC');
	$rimske_cifre_stotice  = array('', 'C', 'CC', 'CCC', 'CD', 'D', 'DC', 'DCC', 'DCC', 'CM');
	$rimske_cifre_hiljade  = array('', 'M', 'MM', 'MMM', 'MMMM');
	
	while (strlen($arapski_broj_text) < 4) $arapski_broj_text = "0" . $arapski_broj_text;

	$anu = intval(substr($arapski_broj_text, 3, 1)); 
	$anx = intval(substr($arapski_broj_text, 2, 1)); 
	$anc = intval(substr($arapski_broj_text, 1, 1)); 
	$anm = intval(substr($arapski_broj_text, 0, 1)); 

	$rimski_broj = $rimske_cifre_hiljade[$anm] . $rimske_cifre_stotice[$anc] . $rimske_cifre_desetice[$anx] . $rimske_cifre_jedinice[$anu]; 
	return $rimski_broj; 
} 


// Funkcija koja zamjenjuje stringove koji liče na URL sa HTML kodom koji linkuje na njih
function linkuj_urlove($tekst) {
	$i=0;
	while (strpos($tekst,"http://",$i)!==false || strpos($tekst,"https://",$i)!==false) {
		$j = strpos($tekst,"http://",$i);
		if ($j==false) $j = strpos($tekst,"https://",$i);
		
		// Prvi sljedeći razmak ili kraj stringa
		$k = strpos($tekst," ",$j);
		$k2 = strpos($tekst,"\n",$j);
		if ($k2<$k && $k2!=0) $k=$k2;
		if ($k==0) $k=$k2;
		if ($k==0) { $k=strlen($tekst);}

		// Interpunkcijski znakovi kojim se obično završava rečenica nisu dio URLa
		do {
			$k--;
			$a = substr($tekst,$k,1);
		} while ($a=="."||$a=="," || $a==")" || $a=="!" || $a=="?"); 
		
		// Stringove kraće od 9 znakova ne smatramo URLom
		$k++;
		if ($k-$j<9) { $i=$j+1; continue; }
		
		// Zamjenjujemo URL sa linkom na URL
		$url = substr($tekst,$j,$k-$j);
		$tekst = substr($tekst,0,$j). "<a href=\"$url\" target=\"_blank\">$url</a>". substr($tekst,$k);
		$i = $j+strlen($url)+28;
	}
	return $tekst;
}


// Često korištena funkcija kada treba redirektovati izlaz u datoteku
function zamger_file_callback($buffer) {
	global $zamger_filecb_sadrzaj_buffera;
	$zamger_filecb_sadrzaj_buffera = $buffer;
}


// Generiše cachiranu verziju izvještaja izvjestaj/predmet
// Prije poziva treba u superglobalni niz $_REQUEST napuniti eventualne parametre izvještaja
function generisi_izvjestaj_predmet($predmet, $ag, $params = array()) {
	global $zamger_filecb_sadrzaj_buffera, $conf_files_path;

	// Punimo parametre u superglobalni niz $_REQUEST kako bi se proslijedili izvještaju
	foreach($params as $key => $value)
		$_REQUEST[$key] = $value;
	$_REQUEST['predmet'] = $predmet;
	$_REQUEST['ag'] = $ag;
	
	ob_start('zamger_file_callback');
	include("izvjestaj/predmet.php");
	eval("izvjestaj_predmet();");
	ob_end_clean();
	
	if (!file_exists("$conf_files_path/izvjestaj_predmet")) {
		mkdir ("$conf_files_path/izvjestaj_predmet",0777, true);
	}
	$filename = $conf_files_path."/izvjestaj_predmet/$predmet-$ag-".date("dmY").".html";
	file_put_contents($filename, $zamger_filecb_sadrzaj_buffera);
}


// Da li nastavnik ima pravo pristupa podacima studenta na predmetu i akademskoj godini
// Ako je $student=0, odnosi se na sve studente
function nastavnik_pravo_pristupa($predmet, $ag, $student=0) {
	global $userid;

	$q20 = db_query("select nivo_pristupa from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
	if (db_num_rows($q20)>0) {
		$ok = true;
		// Postoji li ograničenje na tom predmetu
		if (db_result($q20,0,0) == "asistent") {
			$labgrupe = student_labgrupe($student, $predmet, $ag);
			$ok = nastavnik_ogranicenje($predmet, $ag, $student);
		}
	}
	return $ok;
}


// Spisak labgrupa na predmetu i akademskoj godini kojih je student član
// Ako je $ukljuci_virtualne=false, neće biti vraćene virtualne labgrupe
function student_labgrupe($student, $predmet, $ag, $ukljuci_virtualne = true) {
	global $userid;
	
	$rezultat = array();
	$upit = "SELECT l.id FROM student_labgrupa as sl, labgrupa as l WHERE sl.labgrupa=l.id AND sl.student=$student AND l.predmet=$predmet AND l.akademska_godina=$ag";
	if (!$ukljuci_virtualne) $upit .= " AND l.virtualna=0";
	$q10 = db_query($upit);
	while ($r10 = db_fetch_row($q10)) $rezultat[] = $r10[0];
	return $rezultat;
}


// Da li nastavnik ima ograničenje na labgrupu u kojoj je student
function nastavnik_ogranicenje($predmet, $ag, $student=0) {
	global $userid;

	$q50 = db_query("select o.labgrupa from ogranicenje as o, labgrupa as l where o.nastavnik=$userid and o.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag");
	if (db_num_rows($q50) < 1) return true;
	if ($student == 0) return false;
	
	$labgrupe = student_labgrupe($student, $predmet, $ag, false);
	if (count($labgrupe) == 0) return false;
	
	while ($r50 = db_fetch_row($q50))
		foreach($labgrupe as $lg)
			if ($r50[0] == $lg) return true;
	
	return false;
}


// Provjerava da li student sluša predmet i vraća ponudu kursa
function daj_ponudu_kursa($student, $predmet, $ag) {
	$q2 = db_query("select sp.predmet from student_predmet as sp, ponudakursa as pk where sp.student=$student and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
	if (db_num_rows($q2)<1)
		return false;
	
	return db_result($q2,0,0);
}


// Vraća vrijednost request parametra ili nulu
function param($name) {
	if (isset($_REQUEST[$name])) return $_REQUEST[$name];
	return false;
}

// Vraća integer vrijednost request parametra ili nulu
function int_param($name) {
	if (isset($_REQUEST[$name])) return intval($_REQUEST[$name]);
	return 0;
}

// Poredi parametar sa stringom
function param_equals($name, $value) {
	if (!isset($_REQUEST[$name])) return false;
	return $_REQUEST[$name] === $value;
}


?>
