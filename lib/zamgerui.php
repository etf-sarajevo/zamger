<?

// LIB/ZAMGERUI - generisanje različitih elemenata Zamger korisničkog interfejsa

require_once("lib/utility.php"); // malaslova, mimetype


// Standardne poruke (koristiti CSS!)
function niceerror($error) {
	print "<p><font color='red'><b>GREŠKA: $error</b></font></p>";
}

function biguglyerror($error) {
	print "<center><h2><font color='red'><b>GREŠKA: $error</b></font></h2></center>";
}

function nicemessage($error) {
	print "<p><font color='green'><b>$error</b></font></p>";
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
	<img src="static/images/fnord.gif" width="200" height="1" alt="fnord"><br>
	<img src="static/images/16x16/<?=$slika?>" border="0" alt="fnord"> <?=$user['ime']?> <?=$user['prezime']?><br>
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


// Sada implementiramo kao pravi AJAX
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


// Vrati odgovarajuću ikonu za fajl
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

	$tip = mimetype($file);
	$ekst = $tip . strrchr($file, ".");

	if ($mtekst[$ekst]) return $mtekst[$ekst];
	if ($mimetypes[$tip]) return $mimetypes[$tip];

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
	<img src="static/images/combobox_up.png" width="19" height="18" onClick="comboBoxShowHide('<?=$name?>')" id="comboBoxImg_<?=$name?>" valign="bottom"> <img src="static/images/combobox_down.png" style="visibility:hidden">
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
//		<!--td><img src="static/images/fnord.gif" width="10" height="1"></td>
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
		</td><!--td width="1" bgcolor="#000000"><img src="static/images/fnord.gif" width="1" height="1">
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
				$ispis .= "<tr><td colspan=\"2\"><br/><img src=\"static/images/fnord.gif\" width=\"1\" height=\"2\"><br/><b>Zimski semestar ";
			else
				$ispis .= "<tr><td colspan=\"2\"><br/><img src=\"static/images/fnord.gif\" width=\"1\" height=\"2\"><br/><b>Ljetnji semestar ";
			$ispis .= $r30[3].":</b><br/><br/></td></tr>\n";
			$oldsem=$zimskiljetnji; $oldag=$r30[3];
		}

		// Ako je modul trenutno aktivan, boldiraj i prikaži meni
		if (int_param('predmet')==$predmet && int_param('ag')==$pag) {
			$ispis .= '<tr><td valign="top" style="padding-top:2px;"><img src="static/images/down_red.png" align="bottom" border="0"></td>'."\n<td>";
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
				$ispis .= '<tr><td valign="top" style="padding-top:2px;"><img src="static/images/left_red.png" align="bottom" border="0"></td>'."\n<td><a href=\"?sta=student/zavrsni&predmet=$predmet&ag=$pag&sm_arhiva=$arhiva\">$predmet_naziv</a></td></tr>\n";
			else
				$ispis .= '<tr><td valign="top" style="padding-top:2px;"><img src="static/images/left_red.png" align="bottom" border="0"></td>'."\n<td><a href=\"?sta=student/predmet&predmet=$predmet&ag=$pag&sm_arhiva=$arhiva\">$predmet_naziv</a></td></tr>\n";
		}
	}
	$ispis .= "</table>\n";


?>
	<table width="100%" border="0" cellspacing="4" cellpadding="0">
		<tr><td valign="top">
			<img src="static/images/fnord.gif" width="197" height="1"><br/><br/>
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
			<img src="static/images/plus.png" width="13" height="13" id="img-dokumenti" onclick="daj_stablo('dokumenti')">
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
		<td width="1" bgcolor="#888888"><img src="static/images/fnord.gif" width="1" height="1"></td>
		<td width="5" bgcolor="#FFFFFF"><img src="static/images/fnord.gif" width="5" height="1"></td>
		<td width="100%" valign="top">
		<? eval ($fj); ?>
			</td></tr>
		</table>
	<?

}



// Funkcija mass_input omogućuje funkcionalnost "masovnog unosa podataka" tj. copy&paste iz Excela
// Pretpostavka je da je korišten manje-više standardan textarea sa standardnim imenima parametara
// Polje $ispis omogućuje da se uradi jedan "testni prolaz" kojim se vidi šta će biti urađeno
// Funkcija vraća 1 u slucaju greške, 0 za ispravno
// Globalni niz $mass_rezultat sadrži parsirane podatke
function mass_input($ispis) {
	global $mass_rezultat,$userid;
	$mass_rezultat = array(); // brišemo niz
	$mass_rezultat['ime'] = array(); // sprječavamo upozorenja


	// Da li treba ispisivati akcije na ekranu ili ne?
	$f = $ispis;

	// Parametri
	$ponudakursa = intval($_REQUEST['ponudakursa']);
	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']); // akademska godina

	$redovi = explode("\n",$_POST['massinput']);

	// Format imena i prezimena:
	//   0 - Prezime[SEPARATOR]Ime
	//   1 - Ime[SEPARATOR]Prezime
	//   2 - Prezime Ime
	//   3 - Ime Prezime
	$format = intval($_REQUEST['format']);

	// Broj dodatnih kolona podataka (osim imena i prezimena)
	$brpodataka = intval($_REQUEST['brpodataka']);
	if ($_REQUEST['brpodataka']=='on') $brpodataka=1; //checkbox
	$kolona = $brpodataka+1;
	if ($format<2) $kolona++;

	// Separator: 0 = TAB, 1 = zarez, ...
	$separator = intval($_REQUEST['separator']);
	if ($separator==1) $sepchar=','; else $sepchar="\t";

	// Da li je dozvoljeno ponavljanje istog studenta? 1=da, sve ostalo=ne
	$duplikati = intval($_REQUEST['duplikati']);
	if ($duplikati!=1) $duplikati=0;

	// U slucaju duplikati=1, sta se desava sa ponovnim unosom?
	// 0=pise se preko starog, 1=rezultati su nizovi
	$visestruki = intval($_REQUEST['visestruki']);
	if ($visestruki!=1) $visestruki=0;


	// Update korisničkih preferenci kod masovnog unosa

	$q190 = db_query("select vrijednost from preference where korisnik=$userid and preferenca='mass-input-format'");
	if (db_num_rows($q190)<1) {
		$q191 = db_query("insert into preference set korisnik=$userid, preferenca='mass-input-format', vrijednost='$format'");
	} else if (db_result($q190,0,0)!=$format) {
		$q192 = db_query("update preference set vrijednost='$format' where korisnik=$userid and preferenca='mass-input-format'");
	}

	$q193 = db_query("select vrijednost from preference where korisnik=$userid and preferenca='mass-input-separator'");
	if (db_num_rows($q193)<1) {
		$q194 = db_query("insert into preference set korisnik=$userid, preferenca='mass-input-separator', vrijednost='$separator'");
	} else if (db_result($q193,0,0)!=$separator) {
		$q195 = db_query("update preference set vrijednost='$separator' where korisnik=$userid and preferenca='mass-input-separator'");
	}


	$greska=0;
	$prosli_idovi = array(); // za duplikate

	foreach ($redovi as $red) {
		$red = trim($red);
		if (strlen($red)<2) continue; // prazan red
		// popravljamo nbsp Unicode karakter
		$red = str_replace("¡", " ", $red);
		$red = str_replace(" ", " ", $red);
		$red = db_escape($red);

		$nred = explode($sepchar, $red, $kolona);

		// Parsiranje formata
		if ($format==0) {
			$prezime=$nred[0];
			$ime=$nred[1];
		} else if ($format==1) {
			$ime=$nred[0];
			$prezime=$nred[1];
		} else if ($format==2) {
			list($prezime,$ime) = explode(" ",$nred[0],2);
		} else if ($format==3) {
			list($ime,$prezime) = explode(" ",$nred[0],2);
		}
		else {
			niceerror("Nedozvoljen format"); // ovo je fatalna greska
			return 1;
		}

		// Fixevi za naša slova i trim
		$prezime = trim(malaslova($prezime));
		$ime = trim(malaslova($ime));


		// Provjera ispravnosti podataka

		// Da li korisnik postoji u bazi?
		$q10 = db_query("select id from osoba where ime like '$ime' and prezime like '$prezime'");
		if (db_num_rows($q10)<1) {
			if ($f)  {
				?><tr bgcolor="#FFE3DD"><td><?=$prezime?></td><td><?=$ime?></td><td>nepoznat student - da li ste dobro ukucali ime?</td></tr><?
			}
			$greska=1;
			continue;

		} else if (db_num_rows($q10)>1) {
			if ($ponudakursa>0) {
				// Postoji više studenata sa istim imenom i prezimenom
				// Biramo onog koji je upisan na ovu ponudukursa
				$q10 = db_query("select DISTINCT o.id from osoba as o, student_predmet as sp where o.ime like '$ime' and o.prezime like '$prezime' and o.id=sp.student and sp.predmet=$ponudakursa");
	
				if (db_num_rows($q10)<1) {
					if ($f) {
						?><tr bgcolor="#FFE3DD"><td><?=$prezime?></td><td><?=$ime?></td><td>nije upisan/a na ovaj predmet</td></tr><?
					}
					$greska=1;
					continue;
	
				} else if (db_num_rows($q10)>1) {
					// Na istom su predmetu!? wtf
					if ($f) {
						?><tr bgcolor="#FFE3DD"><td><?=$prezime?></td><td><?=$ime?></td><td>postoji više studenata sa ovim imenom i prezimenom; koristite pogled grupe</td></tr><?
					}
					$greska=1;
					continue;
				}

			} else if ($predmet>0 && $ag>0) {
				// Isto za predmet
				$q10 = db_query("select DISTINCT o.id from osoba as o, student_predmet as sp, ponudakursa as pk where o.ime like '$ime' and o.prezime like '$prezime' and o.id=sp.student and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
	
				if (db_num_rows($q10)<1) {
					if ($f) {
						?><tr bgcolor="#FFE3DD"><td><?=$prezime?></td><td><?=$ime?></td><td>nije upisan/a na ovaj predmet</td></tr><?
					}
					$greska=1;
					continue;
	
				} else if (db_num_rows($q10)>1) {
					// Na istom su predmetu!? wtf
					if ($f) {
						?><tr bgcolor="#FFE3DD"><td><?=$prezime?></td><td><?=$ime?></td><td>postoji više studenata sa ovim imenom i prezimenom; koristite pogled grupe</td></tr><?
					}
					$greska=1;
					continue;
				}

			} else {
				if ($f) {
					?><tr bgcolor="#FFE3DD"><td><?=$prezime?></td><td><?=$ime?></td><td>postoji više studenata sa ovim imenom i prezimenom; koristite pogled grupe</td></tr><?
				}
				$greska=1;
				continue;
			}
		}
		$student = db_result($q10,0,0);

		// Da li se ponavlja isti student?
		if ($duplikati==0) {
			// FIXME: zašto ne radi array_search?
			if (in_array($student,$prosli_idovi)) {
				if ($f) {
					?><tr bgcolor="#FFE3DD"><td><?=$prezime?></td><td><?=$ime?></td><td>ponavlja se</td></tr><?
				}
				$greska=1;
				continue;
			}
			array_push($prosli_idovi,$student);
		}

		// Da li je upisan na predmet?
		$q20=0;
		if ($ponudakursa>0) {
			$q20 = db_query("select count(*) from student_predmet where student=$student and predmet=$ponudakursa");
		} else if ($predmet>0 && $ag>0) {
			$q20 = db_query("select count(*) from student_predmet as sp, ponudakursa as pk where sp.student=$student and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
		}
		if ($q20 != 0) {
			if (db_result($q20,0,0)<1) {
				// Pokusacemo preskociti studente koji nemaju ocjenu
				if ($format==0 || $format==1) 
					$bodovi=$nred[2];
				else
					$bodovi=$nred[1];
				if (!preg_match("/\w/",$bodovi)) {
					if ($f)  {
						?><tr bgcolor="#EEEEEE"><td><?=$prezime?></td><td><?=$ime?></td><td>nepoznat student, nema ocjene - preskačem</td></tr><?
					}
				} else {
					if ($f) {
						?><tr bgcolor="#FFE3DD"><td><?=$prezime?></td><td><?=$ime?></td><td>nije upisan/a na ovaj predmet</td></tr><?
					}
					$greska=1;
				}
				continue;
			}
		}

		// Podaci su OK, punimo niz...
		$mass_rezultat['ime'][$student]=$ime;
		$mass_rezultat['prezime'][$student]=$prezime;
		for ($i=1; $i<=$brpodataka; $i++) {
			if ($duplikati==1 && $visestruki==1) {
				if (count($mass_rezultat["podatak$i"][$student])==0) $mass_rezultat["podatak$i"][$student]=array();
				array_push($mass_rezultat["podatak$i"][$student],$nred[$kolona-$brpodataka-1+$i]);
			} else
				$mass_rezultat["podatak$i"][$student]=$nred[$kolona-$brpodataka-1+$i];
		}
	}
	if ($f) {
		print "<br/>\n";
	}
	return $greska;
}

?>
