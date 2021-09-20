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

function nicemessage($message) {
	print "<p><font color='green'><b>$message</b></font></p>";
}


// Floating layer sa podacima o korisniku i loginom
function user_box() {
	global $user_nastavnik,$user_studentska,$user_siteadmin,$userid,$su,$person;

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
	<img src="static/images/16x16/<?=$slika?>" border="0" alt="fnord"> <?=$person['name']?> <?=$person['surname']?><br>
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
	global $conf_backend_url_client, $conf_backend_has_rewrite, $conf_keycloak, $conf_site_url;
	if ($conf_keycloak) {
		?>
		<script>
			var zamger_oauth_token = '<?=get_keycloak_token()?>';
		</script>
		<?php
	}
	
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
		function ajax_api_start(route, method, params, cb_success, cb_fail) {
			cb_fail = cb_fail || ajax_log_error;
			var xhttp = new XMLHttpRequest();
			xhttp.onreadystatechange = function() {
				if (xhttp.readyState == 4 && (xhttp.status == 200 || xhttp.status == 201 || xhttp.status == 204)) {
					var object = {};
					try {
						object = JSON.parse(xhttp.responseText);
					} catch(e) {
						cb_fail(xhttp.responseText, xhttp.status, url);
						return;
					}
					cb_success(object);
				} else if (xhttp.readyState == 4 && xhttp.status == 401) {
					// Access denied, check if token expired
					var xhttp_token = new XMLHttpRequest();
					xhttp_token.onreadystatechange = function() {
						if (xhttp_token.readyState == 4 && xhttp_token.status == 200 && xhttp_token.responseText.substring(0,7) == "Token: ") {
							zamger_oauth_token = xhttp_token.responseText.substring(7);
							ajax_api_start(route, method, params, cb_success, cb_fail);
						} else if (xhttp_token.readyState == 4) {
							cb_fail(xhttp.responseText, xhttp.status, url);
						}
					};
					var url = '<?=$conf_site_url?>/get_token.php';
					xhttp_token.open("GET", url, true);
					xhttp_token.send();
				} else if (xhttp.readyState == 4) {
					cb_fail(xhttp.responseText, xhttp.status, url);
				}
			};

			var url='<?=$conf_backend_url_client?>';
			<?
			if ($conf_backend_has_rewrite) {
			?>
			url += route;
			<?
			} else {
			?>
			url += "?route=" + route;
			<?
			}
			// These must be in URL even if method is not GET
			if (!$conf_keycloak) {
			?>
			if (url.includes("?")) url += "&"; else url += "?";
			url += "SESSION_ID=<?=$_SESSION['api_session']?>";
			<?
			}
			if ($_SESSION['su']) {
			?>
			if (url.includes("?")) url += "&"; else url += "?";
			url += "impersonate=<?=$_SESSION['su']?>";
			<?
			}
			?>
			if (method == "GET") {
				for(param in params) {
					if (url.includes("?")) url += "&"; else url += "?";
					url += param + "=" + encodeURI(params[param]);
				}
			}

			xhttp.open(method, url, true);
			xhttp.setRequestHeader("Content-type", "application/json");
			<?
			if ($conf_keycloak) {
			?>
			xhttp.setRequestHeader("Authorization", "Bearer "+zamger_oauth_token);
			<?
			}
			?>
			xhttp.send(JSON.stringify(params));
		}
		// Default funkcija za neuspjeh, logira greške
		function ajax_log_error(responseText, status, url) {
			try {
				var object = JSON.parse(responseText);
				console.log("Neuspio upit na web servis "+url+": ["+object['code']+"] "+object['message']);
			} catch(e) {
				console.log("Web servis "+url+" nije vratio validan JSON (status "+status+"): "+xhttp.responseText);
			}
		}
	</script>
	<?php
}


// Vrati odgovarajuću ikonu za fajl
function getmimeicon($file, $tip) {
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
		// FIXME prebaciti na aktivnost "završni rad"
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
	if ($predmet==0 || ($tippredmeta != 1000 && $tippredmeta != 1001)) {
		foreach ($registry as $r) {
			if(count($r) < 5 || $r[5] != 0) continue; // nevidljiv
			if (strstr($r[0],$sekcija)) {
				if ($r[0]==$sta) $bgcolor="#eeeeee"; else $bgcolor="#ffffff";
				if ($r[0]=="nastavnik/zavrsni") continue; // Ovo se prikazuje samo ako je tippredmeta == 1000 ili 1001 - završni rad
				?><tr><td height="20" align="right" bgcolor="<?=$bgcolor?>" onmouseover="this.bgColor='#CCCCCC'" onmouseout="this.bgColor='<?=$bgcolor?>'">
					<a href="?sta=<?=$r[0]?><?=$dodaj?>" class="malimeni"><?=$r[1]?></a>
				</tr></tr>
				<tr><td>&nbsp;</td></tr>
				<?
			}
		}
	} else {
		?><tr><td height="20" align="right" bgcolor="#eeeeee" onmouseover="this.bgColor='#CCCCCC'" onmouseout="this.bgColor='#eeeeee'">
			<a href="?sta=nastavnik/zavrsni<?=$dodaj?>" class="malimeni">Završni rad</a>
		</tr></tr>
		<tr><td>&nbsp;</td></tr>
		<?
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
	global $userid, $sta, $registry, $courseDetails, $_api_http_code;

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
	
	// Upit course/student/$userid vraca predmete koje je student ikada slusao (all=true) ili koje trenutno slusa (all=false)
	$arhiva = int_param('sm_arhiva');
	if ($arhiva==1)
		$courseDetails = api_call("course/student/$userid", ["all" => "true", "courseInformation" => "true", "activities" => "true"])['results'];
	else
		$courseDetails = api_call("course/student/$userid", ["courseInformation" => "true", "activities" => "true"])['results'];
	
	if ($_api_http_code == "404") {
		// Student not currently enrolled
		$courseDetails = [];
	}
	
	$output = '<table border="0" cellspacing="2" cellpadding="1">';
	$oldsem=$oldyear=0;
	
	// Glavna petlja za generisanje ispisa
	foreach($courseDetails as $courseDetail) {
		$semester = $courseDetail['CourseOffering']['semester'];
		$yearId = $courseDetail['CourseOffering']['AcademicYear']['id'];
		$year = $courseDetail['CourseOffering']['AcademicYear']['name'];
		$courseId = $courseDetail['CourseOffering']['CourseUnit']['id'];
		$course = $courseDetail['courseName'];
		$tippredmeta = "FIXME"; // FIXME

		// Zaglavlje sa imenom akademske godine i semestrom
		if ($semester % 2 != $oldsem || $year != $oldyear) {
			if ($semester%2==1)
				$output .= "<tr><td colspan=\"2\"><br/><img src=\"static/images/fnord.gif\" width=\"1\" height=\"2\"><br/><b>Zimski semestar ";
			else
				$output .= "<tr><td colspan=\"2\"><br/><img src=\"static/images/fnord.gif\" width=\"1\" height=\"2\"><br/><b>Ljetnji semestar ";
			$output .= $year . ":</b><br/><br/></td></tr>\n";
			$oldsem = $semester % 2;
			$oldyear = $year;
		}

		// Ako je modul trenutno aktivan, boldiraj i prikaži meni
		if (int_param('predmet') == $courseId && int_param('ag') == $yearId) {
			$output .= '<tr><td valign="top" style="padding-top:2px;"><img src="static/images/down_red.png" align="bottom" border="0"></td>'."\n<td>";
			if ($course == "Završni rad" || $course == "Završni rad (Master)")
				$output .= "<a href=\"?sta=student/zavrsni&predmet=$courseId&ag=$yearId&sm_arhiva=$arhiva\">";
			else if ($_REQUEST['sta'] != "student/predmet")
				$output .= "<a href=\"?sta=student/predmet&predmet=$courseId&ag=$yearId&sm_arhiva=$arhiva\">";
			$output .= "<b>$course</b>";
			if ($_REQUEST['sta'] != "student/predmet")
				$output .= "</a>";
			$output .= "<br/>\n";
			
			// Studentski moduli aktivirani za ovaj predmet
			$translation = [1 => "student/moodle", 2 => "student/zadaca", 4 => "student/projekti", 5 => "student/kviz", 6 => "student/anketa", 7 => "student/gg" ];
			foreach($courseDetail['activities'] as $activity) {
				if (!array_key_exists($activity['Activity']['id'], $translation))
					continue;
				$sta = $translation[$activity['Activity']['id']];
				if ($activity['Activity']['id'] == 2) {
					if (array_key_exists("StudentSubmit", $activity['options']) && $activity['options']['StudentSubmit'])
						$activity['name'] = "Slanje zadaće";
					else
						continue;
				}
				if ($sta == $_REQUEST['sta'])
					$output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . $activity['name'] . "<br/>\n";
				else if ($activity['display'] == 1)
					$output .= "&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"?sta=$sta&predmet=$courseId&ag=$yearId\" target=\"_blank\">" . $activity['name'] . "</a><br/>\n";
				else
					$output .= "&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"?sta=$sta&predmet=$courseId&ag=$yearId&sm_arhiva=$arhiva\">" . $activity['name'] . "</a><br/>\n";
			}
			
			$output .= "</td></tr>\n";
		} else {
			if ($course == "Završni rad" || $course == "Završni rad (Master)")
				$output .= '<tr><td valign="top" style="padding-top:2px;"><img src="static/images/left_red.png" align="bottom" border="0"></td>'."\n<td><a href=\"?sta=student/zavrsni&predmet=$courseId&ag=$yearId&sm_arhiva=$arhiva\">$course</a></td></tr>\n";
			else
				$output .= '<tr><td valign="top" style="padding-top:2px;"><img src="static/images/left_red.png" align="bottom" border="0"></td>'."\n<td><a href=\"?sta=student/predmet&predmet=$courseId&ag=$yearId&sm_arhiva=$arhiva\">$course</a></td></tr>\n";
		}
	}
	$output .= "</table>\n";


?>
	<table width="100%" border="0" cellspacing="4" cellpadding="0">
		<tr><td valign="top">
			<img src="static/images/fnord.gif" width="197" height="1"><br/><br/>
			<? if ($sta != "student/intro") { ?>
			<a href="?sta=student/intro">&lt;-- Nazad na početnu</a>
			<? } else { ?>&nbsp;<? } ?><?=$output?>
			
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
				&nbsp;&nbsp;&nbsp; <a href="?sta=izvjestaj/sv20">ŠV-20 obrazac</a> <i><font color="red">NOVO!</font></i><br />
				&nbsp;&nbsp;&nbsp; <a href="?sta=student/potvrda">Zahtjev za ovjereno uvjerenje</a><br />
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
function mass_input($ispis, $virtualGroup = []) {
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
	//   4 - Broj indeksa
	$format = intval($_REQUEST['format']);
	if ($format == 4) return _mass_input_brindexa($ispis, $virtualGroup);
	
	if (empty($virtualGroup))
		$virtualGroup = api_call("group/course/$predmet/allStudents", [ "year" => $ag, "names" => true]);
	$names = $studentIds = [];
	foreach($virtualGroup['members'] as $member) {
		if ($ponudakursa > 0 && $member['CourseOffering']['id'] != $ponudakursa)
			continue;
		$names[$member['student']['id']] = $member['student']['name'] . " " . $member['student']['surname'];
		$studentIds[$member['student']['id']] = $member['student']['studentIdNr'];
	}

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
	api_call("person/preferences", ["preference" => 'mass-input-format', "value" => $format], "PUT");
	api_call("person/preferences", ["preference" => 'mass-input-separator', "value" => $separator], "PUT");


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
		} else if ($format==4) {
			$brindexa = $nred[0];
			$ime = $prezime = "";
		}
		else {
			niceerror("Nedozvoljen format"); // ovo je fatalna greska
			return 1;
		}

		// Fixevi za naša slova i trim
		$prezime = trim(malaslova($prezime));
		$ime = trim(malaslova($ime));
		$join = $ime . " " . $prezime;


		// Provjera ispravnosti podataka

		// Da li korisnik postoji u bazi?
		$found = []; $student = 0;
		foreach($names as $id => $name) {
			if ($name == $join) {
				$found[$id] = $name;
				$student = $id;
			}
		}
		
		if (count($found) == 0) {
			// Pokusacemo preskociti studente koji nemaju ocjenu
			if ($format==0 || $format==1)
				$bodovi=$nred[2];
			else
				$bodovi=$nred[1];
			if (!preg_match("/\w/",$bodovi)) {
				if ($f)  {
					?><tr bgcolor="#EEEEEE"><td><?=$prezime?></td><td><?=$ime?></td><td><?=$brindexa?></td><td>nepoznat student, nema ocjene - preskačem</td></tr><?
				}
			}
			else {
				if ($f)  {
					?><tr bgcolor="#FFE3DD"><td><?=$prezime?></td><td><?=$ime?></td><td>&nbsp;</td><td>nepoznat student - da li ste dobro ukucali ime?</td></tr><?
				}
				$greska=1;
				continue;
			}

		} else if (count($found) > 1) {
			// Na istom su predmetu!? wtf
			if ($f) {
				?><tr bgcolor="#FFE3DD"><td><?=$prezime?></td><td><?=$ime?></td><td>&nbsp;</td><td>postoji više studenata sa ovim imenom i prezimenom; koristite pogled grupe</td></tr><?
			}
			$greska=1;
			continue;
		}
		$brindexa = $studentIds[$student];

		// Da li se ponavlja isti student?
		if ($duplikati==0) {
			// FIXME: zašto ne radi array_search?
			if (in_array($student,$prosli_idovi)) {
				if ($f) {
					?><tr bgcolor="#FFE3DD"><td><?=$prezime?></td><td><?=$ime?></td><td><?=$brindexa?></td><td>ponavlja se</td></tr><?
				}
				$greska=1;
				continue;
			}
			array_push($prosli_idovi,$student);
		}

		// Podaci su OK, punimo niz...
		$mass_rezultat['ime'][$student]=$ime;
		$mass_rezultat['prezime'][$student]=$prezime;
		$mass_rezultat['brindexa'][$student]=$brindexa;
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


function _mass_input_brindexa($ispis, $virtualGroup = []) {
	global $mass_rezultat,$userid;
	
	// Da li treba ispisivati akcije na ekranu ili ne?
	$f = $ispis;
	
	// Parametri
	$ponudakursa = intval($_REQUEST['ponudakursa']);
	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']); // akademska godina
	
	$redovi = explode("\n",$_POST['massinput']);
	
	$format = intval($_REQUEST['format']);
	
	if (empty($virtualGroup))
		$virtualGroup = api_call("group/course/$predmet/allStudents", [ "year" => $ag, "names" => true]);
	$names = $surnames = $studentIds = [];
	foreach($virtualGroup['members'] as $member) {
		if ($ponudakursa > 0 && $member['CourseOffering']['id'] != $ponudakursa)
			continue;
		$names[$member['student']['id']] = $member['student']['name'];
		$surnames[$member['student']['id']] = $member['student']['surname'];
		$studentIds[$member['student']['id']] = $member['student']['studentIdNr'];
	}
	
	// Broj dodatnih kolona podataka (osim imena i prezimena)
	$brpodataka = intval($_REQUEST['brpodataka']);
	if ($_REQUEST['brpodataka']=='on') $brpodataka=1; //checkbox
	$kolona = $brpodataka+1;
	
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
	api_call("person/preferences", ["preference" => 'mass-input-format', "value" => $format], "PUT");
	api_call("person/preferences", ["preference" => 'mass-input-separator', "value" => $separator], "PUT");
	
	
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
		$brindexa = trim($nred[0]);
	
		// Provjera ispravnosti podataka
		
		// Da li korisnik postoji u bazi?
		$found = []; $student = 0;
		foreach($studentIds as $id => $studentId) {
			if ($studentId == $brindexa) {
				$found[$id] = $studentId;
				$student = $id;
			}
		}
		
		if (count($found) == 0) {
			if ($f)  {
				?><tr bgcolor="#FFE3DD"><td>&nbsp;</td><td>&nbsp;</td><td><?=$brindexa?></td><td>nepoznat student - da li ste dobro ukucali broj indeksa?</td></tr><?
			}
			$greska=1;
			continue;
			
		} else if (count($found) > 1) {
			if ($f) {
				?><tr bgcolor="#FFE3DD"><td>&nbsp;</td><td>&nbsp;</td><td><?=$brindexa?></td><td>postoji više studenata sa ovim brojem indeksa; kontaktirajte studentsku službu!</td></tr><?
			}
			$greska=1;
			continue;
		}
		$ime = $names[$student];
		$prezime = $surnames[$student];
		
		// Da li se ponavlja isti student?
		if ($duplikati==0) {
			// FIXME: zašto ne radi array_search?
			if (in_array($student,$prosli_idovi)) {
				if ($f) {
					?><tr bgcolor="#FFE3DD"><td><?=$prezime?></td><td><?=$ime?></td><td><?=$brindexa?></td><td>ponavlja se</td></tr><?
				}
				$greska=1;
				continue;
			}
			array_push($prosli_idovi,$student);
		}
		
		// Podaci su OK, punimo niz...
		$mass_rezultat['ime'][$student]=$ime;
		$mass_rezultat['prezime'][$student]=$prezime;
		$mass_rezultat['brindexa'][$student]=$brindexa;
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


function getCourseDetails($courseId, $courseYear = 0) {
	global $courseDetails, $userid;
	if (isset($courseDetails)) foreach($courseDetails as $course)
		if ($course['CourseOffering']['CourseUnit']['id'] == $courseId)
			return $course;
	
	// Course not found in courseDetails, fetch from API
	$params = ["courseInformation" => true];
	if ($courseYear != 0) $params['year'] = $courseYear;
	$course = api_call("course/$courseId/student/$userid", $params);
	if ($course['code'] == "404") {
		// Maybe its a teacher?
		$course = api_call("course/$courseId");
	}
	if ($course['code'] != "200") return [];
	$courseDetails[] = $course;
	return $course;
}

function getCourseName($courseId, $courseYear = 0) {
	return getCourseDetails($courseId, $courseYear)['courseName'];
}


// Helper function for displaying a standard API error message
function api_report_bug($apiResponse, $apiRequest) {
	global $_api_http_code, $http_result, $sta;
	
	$bt = debug_backtrace();
	$caller = array_shift($bt);
	$msg = "";
	if (is_array($apiResponse) && array_key_exists('message', $apiResponse))
		$msg = $apiResponse['message'];
	
	?>
	<form action="index.php" method="POST">
		<input type="hidden" name="sta" value="common/inbox">
		<input type="hidden" name="akcija" value="bugreport">
		<input type="hidden" name="original_sta" value="<?=$sta?>">
		<input type="hidden" name="file" value="<?=$caller['file']?>">
		<input type="hidden" name="line" value="<?=$caller['line']?>">
		<input type="hidden" name="request_data" value="<?=htmlentities(json_encode($apiRequest))?>">
		<input type="hidden" name="code" value="<?=$_api_http_code?>">
		<input type="hidden" name="message" value="<?=$msg?>">
		<input type="hidden" name="server_json" value="<?=htmlentities($http_result)?>">
		<input type="submit" value="     Prijavite bug" style="-webkit-appearance: none;
    -moz-appearance: none;
    appearance: none; background: #eeeeee url('static/images/16x16/bug.png') no-repeat; background-position: 10px 5px; padding: 5px 10px; border: 1px solid #223445; border-radius: 4px;">
		
	</form>
	<?
}


?>
