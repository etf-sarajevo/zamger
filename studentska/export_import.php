<?

function studentska_export_import() {

global $conf_site_url;

$export_ws = "$conf_site_url/api/export";

?>
<h2>Uvoz/Izvoz podataka</h2>

<SCRIPT>
// JavaScript za provjeru validnosti podataka putem web servisa
var export_url = '<?=$export_ws?>';
var za_slanje = [];
var za_ciscenje = [];
var dugmad;
var razlike_poruke = [];

//window.onload = function () { pozicioniraj_prozor(); }


// Iteriranje kroz niz elemenata za provjeru
function servis_provjera(tip) {
	if (za_provjeru.length > 0) {
		var stavka = za_provjeru.shift();
		servis_single(stavka, tip, true);
	} else {
		show_hide_buttons();
	}
}


// Iteriranje kroz niz elemenata za slanje
function servis_slanje(tip) {
	if (za_slanje.length > 0) {
		var stavka = za_slanje.shift();
		servis_single(stavka, tip, false);
	} else {
		show_hide_buttons();
	}
}


// Iteriranje kroz niz elemenata za slanje
function servis_ciscenje(tip) {
	if (za_ciscenje.length > 0) {
		var stavka = za_ciscenje.shift();
		servis_single(stavka, "ciscenje_"+tip, false);
	} else {
		show_hide_buttons();
	}
}

function show_hide_buttons() {
	var dugmad = document.getElementsByClassName('dugmeProvjera');
	for (var i=0; i<dugmad.length; i++) {
		if (za_provjeru.length > 0) 
			dugmad[i].style.display = "inline";
		else
			dugmad[i].style.display = "none";
	}

	dugmad = document.getElementsByClassName('dugmeSlanje');
	for (var i=0; i<dugmad.length; i++) {
		if (za_slanje.length > 0) 
			dugmad[i].style.display = "inline";
		else
			dugmad[i].style.display = "none";
	}

	dugmad = document.getElementsByClassName('dugmeCiscenje');
	for (var i=0; i<dugmad.length; i++) {
		if (za_ciscenje.length > 0) 
			dugmad[i].style.display = "inline";
		else
			dugmad[i].style.display = "none";
	}
}


// Prikaži grešku
function student_status(student, status, poruka) {
	var celija = document.getElementById('status'+student);
	if (status == "ok")
		celija.innerHTML = '<img src="static/images/16x16/ok.png" width="16" height="16"> ';
	if (status == "greska")
		celija.innerHTML = '<img src="static/images/16x16/not_ok.png" width="16" height="16"> ';
	if (status == "nastaviti")
		celija.innerHTML = '<img src="static/images/16x16/edit_yellow.png" width="16" height="16"> ';
	if (status == "bug")
		celija.innerHTML = '<img src="static/images/16x16/bug.png" width="16" height="16"> ';
	if (status == "ociscen")
		celija.parentElement.style.display = "none";
	celija.innerHTML = celija.innerHTML + poruka;
}

// Provjera/slanje jedne stavke
function servis_single(stavka, tip, provjera) {
	var xmlhttp = new XMLHttpRequest();
	var url = export_url + "?tip=" + tip;
	if (provjera) url = url + "&akcija=provjera";
	
	if (tip == "ocjene" || tip == "ciscenje_ocjene") 
		url += "&student=" + stavka.student + "&predmet=" + stavka.predmet + "&ocjena=" + stavka.ocjena + "&datum=" + stavka.datum;
		
	else if (tip == "upis_vise" || tip == "ciscenje_upis_vise") 
		url += "&student=" + stavka.student + "&studij=" + stavka.studij + "&godina=" + stavka.godina + "&semestar=" + stavka.semestar;
		
	else if (tip == "promjena_podataka") {
		if (provjera) url = export_url + "?tip=daj_razlike&student=" + stavka.student
		else {
			url = export_url + "?tip=popravi_isss&student=" + stavka.student + "&razlike=";
			for (i=0; i<stavka.razlike.length; i++)
				url += stavka.razlike[i].podatak + "%20";
		}
	}
	
	else
		url += "&student=" + stavka.student + "&studij=" + stavka.studij + "&godina=" + stavka.godina;
		
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
			try {
				result = JSON.parse(xmlhttp.responseText);
				if (result.success == "true") {
					if (result.data.tekst == 'Student u ISSSu se razlikuje') {
						var poruka = {};
						poruka.student = stavka.student;
						poruka.akademska_godina = stavka.akademska_godina;
						poruka.razlike = result.data.razlike;
						poruka.isss_id_studenta = result.data.isss_id_studenta;
						razlike_poruke.push(poruka);
						result.data.tekst = "<a href='#' onclick=\"prikazi_razlike('"+(razlike_poruke.length-1)+"', 'student'); return false;\">" +
							result.data.tekst + "</a>";
					}
					if (result.data.tekst.substring(0,24) == 'Unesen je različit datum') {
						var poruka = {};
						poruka.student = stavka.student;
						poruka.akademska_godina = stavka.akademska_godina;
						poruka.predmet = stavka.predmet;
						var datum_isss = result.data.tekst.substring(27,37);
						var datum_zamger = result.data.tekst.substring(40);
						poruka.razlike = [ { "podatak" : "datum", "zamger" : datum_zamger, "isss" : datum_isss } ];
						razlike_poruke.push(poruka);
						result.data.tekst = "<a href='#' onclick=\"prikazi_razlike('"+(razlike_poruke.length-1)+"', 'datum'); return false;\">" +
							result.data.tekst + "</a>";
					}
					var student = stavka.student;
					if (tip == "upis_vise" || tip == "ciscenje_upis_vise") student = student + "-" + stavka.semestar;
					student_status(student, result.data.status, result.data.tekst);
					
					if (result.data.status == "nastaviti") za_slanje.push(stavka);
					else if (result.data.status == "ok") za_ciscenje.push(stavka);
					
					// Prelazimo na sljedeći zadatak
					if (provjera) servis_provjera(tip); 
					else if (tip.indexOf("ciscenje_") > -1)
						servis_ciscenje(tip.substring(9));
					else servis_slanje(tip);
				} else {
					console.log("Web servis vratio success=false");
					console.log(result);
					
					if (stavka.greska) {
						if (provjera) student_status(stavka.student, "bug", "Provjera nije uspjela");
						else student_status(stavka.student, "bug", "Upis nije uspio");
					} else {
						stavka.greska = true;
						if (provjera) za_provjeru.push(stavka);
						else za_slanje.push(stavka);
					}
					
					if (provjera) servis_provjera(tip); 
					else if (tip.indexOf("ciscenje_") > -1)
						servis_ciscenje(tip.substring(9));
					else servis_slanje(tip);
				}
			} catch(e) {
				console.log("Parsiranje JSONa nije uspjelo");
				console.log(xmlhttp.responseText);
				
				// Ponavljamo provjeru ako prethodno nije bila greška
				if (stavka.greska) {
					if (provjera) student_status(stavka.student, "bug", "Provjera nije uspjela");
					else student_status(stavka.student, "bug", "Upis nije uspio");
				} else {
					stavka.greska = true;
					if (provjera) za_provjeru.push(stavka);
					else za_slanje.push(stavka);
				}
				if (provjera) servis_provjera(tip); else servis_slanje(tip);
			}
			return false;
		}
		if (xmlhttp.readyState == 4) {
			console.log("Serverska greška kod pozivanja web servisa za provjeru.");
			console.log("readyState "+xmlhttp.readyState+" status "+xmlhttp.status);
			student_status(stavka.student, "bug", "Servis nedostupan");
			servis_provjera(tip);
		}
	}
	xmlhttp.open("GET", url, true);
	xmlhttp.send();
}

// Želimo da pozicioniramo prozor za prikaz podataka desno od tabele
function pozicioniraj_prozor() {
	var tabela=document.getElementById('tabelaPodataka');
	var prozor=document.getElementById('displayWindow');
	var rect = tabela.getBoundingClientRect();
	prozor.style.visibility="visible";
	
	var toppos = rect.top;
	if (window && window.pageYOffset)
		toppos += window.pageYOffset*2;
	else if (document && document.scrollTop)
		toppos += document.scrollTop*2;

	var screenwidth = document.documentElement.clientWidth || document.body.clientWidth || window.innerWidth;
	var leftpos = rect.right+100;
	while (leftpos+prozor.getBoundingClientRect().width >= screenwidth) leftpos--;
	
	prozor.style.top = "" + toppos + "px";
	prozor.style.left = "" + leftpos + "px";
}

function prikazi_razlike(code, tip) {
	pozicioniraj_prozor();
	var obj = razlike_poruke[code];
	var tbl = document.getElementById('tabelaRazlika');
	
	for (var x=tbl.rows.length-1; x>0; x--) { // Red 0 je zaglavlje
		tbl.deleteRow(x);
	}
	
	for (i=0; i<obj.razlike.length; i++) {
		var row = tbl.insertRow(tbl.rows.length);
		var cell, div, txt;
		cell = row.insertCell(0);
		div = document.createElement('div');
		txt = document.createTextNode(obj.razlike[i].podatak);
		div.appendChild(txt);
		cell.appendChild(div);
		
		cell = row.insertCell(1);
		div = document.createElement('div');
		txt = document.createTextNode(obj.razlike[i].zamger);
		div.appendChild(txt);
		cell.appendChild(div);
		
		cell = row.insertCell(2);
		div = document.createElement('div');
		txt = document.createTextNode(obj.razlike[i].isss);
		div.appendChild(txt);
		cell.appendChild(div);
	}
	
	var b1 = document.getElementById('popraviZamger');
	var b2 = document.getElementById('popraviIsss');
	var b3 = document.getElementById('ocistiRazlike');
	b1.disabled = false; b2.disabled = false; b3.disabled = false;

	b1.onclick = function() { b1.disabled = true; b2.disabled = true; b3.disabled = true; popravi_zamger(code, tip); }
	b2.onclick = function() { b1.disabled = true; b2.disabled = true; b3.disabled = true; popravi_isss(code, tip); }
	b3.onclick = function() { 
		b1.disabled = true; b2.disabled = true; b3.disabled = true;
		var za_ciscenje_tmp = za_ciscenje.slice();
		za_ciscenje = [];
		za_ciscenje.push(obj.student);
		servis_ciscenje('upis_prva');
		za_ciscenje = za_ciscenje_tmp.slice();
	};
	
	var prozor=document.getElementById('displayWindow');
	var unutrasnji=document.getElementById('innerElement');
	prozor.style.height = (unutrasnji.offsetHeight + 30) + "px";
}

function popravi_isss(code, tip) {
	var xmlhttp = new XMLHttpRequest();
	var obj = razlike_poruke[code];
	
	var url = export_url;
	if (tip == 'datum')
		url += "&tip=popravi_datum_isss&student=" + obj.student + "&predmet=" + obj.predmet;
	else {
		url += "&tip=popravi_studenta_isss&student=" + obj.student + "&isss_id_studenta=" + obj.isss_id_studenta  + "&razlike=";
		for (i=0; i<obj.razlike.length; i++)
			url += obj.razlike[i].podatak + "%20";
	}
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
			try {
				result = JSON.parse(xmlhttp.responseText);
				if (result.success == "true") {
					student_status(obj.student, result.data.status, result.data.tekst);
					
					var stavka = {};
					stavka.student = obj.student;
					stavka.predmet = obj.predmet;
					if (result.data.status == "nastaviti") za_slanje.push(stavka);
					else if (result.data.status == "ok") za_ciscenje.push(stavka);
					show_hide_buttons();
				} else {
					console.log("Web servis vratio success=false");
					console.log(result);
					student_status(obj.student, "bug", "Promjena podataka nije uspjela");
				}
				var prozor=document.getElementById('displayWindow');
				prozor.style.visibility="hidden";
			} catch(e) {
				console.log("Parsiranje JSONa nije uspjelo");
				console.log(xmlhttp.responseText);
				student_status(obj.student, "bug", "Promjena podataka nije uspjela");
			}
			return false;
		}
		if (xmlhttp.readyState == 4) {
			console.log("Serverska greška kod pozivanja web servisa za provjeru.");
			console.log("readyState "+xmlhttp.readyState+" status "+xmlhttp.status);
			student_status(obj.student, "bug", "Servis nedostupan");
		}
	}
	xmlhttp.open("GET", url, true);
	xmlhttp.send();
}

function popravi_zamger(code, tip) {
	var xmlhttp = new XMLHttpRequest();
	var obj = razlike_poruke[code];
	
	var url;
	if (tip == 'datum') {
		url = "?sta=common/ajah&akcija=izmjena_ispita&idpolja=kodatum-" + obj.student + "-" + obj.predmet + "-" + obj.akademska_godina;
		datum = obj.razlike[0].isss.split("-");
		url += "&vrijednost=" + datum[2] + "." + datum[1] + "." + datum[0];
	} else {
		url = ""; // Nije još implementirano
		alert("Još uvijek nije implementirano");
		/*url += "&tip=popravi_studenta_isss&student=" + obj.student + "&isss_id_studenta=" + obj.isss_id_studenta  + "&razlike=";
		for (i=0; i<obj.razlike.length; i++)
			url += obj.razlike[i].podatak + "%20";*/
	}
	console.log("Popravljam "+url);
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
			if (xmlhttp.responseText.indexOf("OK") >= 0) {
				student_status(obj.student, "ok", "Datum popravljen");
				
				var stavka = {};
				stavka.student = obj.student;
				stavka.predmet = obj.predmet;
				if (result.data.status == "nastaviti") za_slanje.push(stavka);
				else if (result.data.status == "ok") za_ciscenje.push(stavka);
				show_hide_buttons();
			} else {
				console.log("Web servis vratio " + xmlhttp.responseText);
				console.log(result);
				student_status(obj.student, "bug", "Promjena podataka nije uspjela");
			}
			var prozor=document.getElementById('displayWindow');
			prozor.style.visibility="hidden";
			return false;
		}
		if (xmlhttp.readyState == 4) {
			console.log("Serverska greška kod pozivanja common/ajah.");
			console.log("readyState "+xmlhttp.readyState+" status "+xmlhttp.status);
			student_status(obj.student, "bug", "Servis nedostupan");
		}
	}
	xmlhttp.open("GET", url, true);
	xmlhttp.send();
}

</SCRIPT>
<?


if (param('akcija') == "novi_studenti") {
	?>
	<h3>Novi studenti za upis na 1. godinu studija</h3>
	<p><? daj_dugmice('upis_prva') ?></p>
	<table border="0" id="tabelaPodataka">
	<tr><th>Student</th><th>Studij</th><th>&nbsp;</th></tr>
	<?
	
	$javascript_niz = "";
	$q10 = db_query("SELECT o.id id_studenta, o.ime, o.prezime, o.brindexa, s.id id_studija, s.naziv naziv_studija, 
				ts.ciklus, ag.id id_godine, ag.naziv naziv_godine
			FROM izvoz_upis_prva iup, student_studij ss, osoba o, studij s, akademska_godina ag, tipstudija ts
			WHERE iup.student=ss.student AND iup.akademska_godina=ss.akademska_godina AND ss.semestar mod 2 = 1 AND
				iup.student=o.id AND iup.akademska_godina=ag.id AND ss.studij=s.id AND s.tipstudija=ts.id
			ORDER BY ag.id, s.naziv, o.prezime, o.ime");
	while($r10 = db_fetch_assoc($q10)) {
		$ispis_ime = "<a href=\"?sta=studentska/osobe&amp;akcija=edit&amp;osoba=" . $r10['id_studenta']. "\" target=\"_blank\">" . $r10['prezime'] . " " . $r10['ime'] . "</a>" . " (" . $r10['brindexa'] . ")";
		$ispis_studij = $r10['naziv_studija'] . ", " . $r10['ciklus'] . ". ciklus, " .$r10['naziv_godine'];
		$id_celije = "status" . $r10['id_studenta'];
		$javascript_niz .= "{ student: " . $r10['id_studenta'] . ", studij: " . $r10['id_studija'] . ", godina: " . $r10['id_godine'] . "},\n";
		
		?>
		<tr>
			<td><?=$ispis_ime?></td>
			<td><?=$ispis_studij?></td>
			<td id="<?=$id_celije?>">&nbsp;</td>
		</tr>
		<?
	}
	
	?>
	</table>
	<script>
	var za_provjeru = [ <?=$javascript_niz?> ];
	</script>
	
	<div id="displayWindow" style="position:absolute; visibility:hidden; border:1px solid #333; background-color: #f8f8f8; width: 600px; height: 200px;">
		<div id="innerElement">
		<h2 style="align:center">Razlike</h2> 
		<table border="0" id="tabelaRazlika">
			<thead><tr><th>Polje</th><th>Zamger</th><th>Drugi sistem</th></tr></thead>
			<tbody><tbody>
		</table>
		<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button id="popraviZamger">Popravi u Zamgeru</button> 
			<button id="popraviIsss">Popravi u drugom sistemu</button> 
			<button id="ocistiRazlike">Očisti studenta</button>
			<button onclick="document.getElementById('displayWindow').style.visibility = 'hidden';">Zatvori</button>
		</p>
		</div>
	</div>
	
	<p><? daj_dugmice('upis_prva') ?></p>
	<p><a href="?sta=studentska/export_import">Nazad</a></p>
	<?
	
	
	return;
}




if (param('akcija') == "upis_vise") {
	?>
	<h3>Upis studenata na više semestre/godine studije</h3>
	<p><? daj_dugmice('upis_vise') ?></p>
	<table border="0">
	<tr><th>Student</th><th>Studij</th><th>&nbsp;</th></tr>
	<?
	
	$javascript_niz = "";
	$q10 = db_query("SELECT o.id id_studenta, o.ime, o.prezime, o.brindexa, s.id id_studija, s.naziv naziv_studija, 
				ts.ciklus, ss.semestar, ag.id id_godine, ag.naziv naziv_godine
			FROM izvoz_upis_semestar ius, student_studij ss, osoba o, studij s, akademska_godina ag, tipstudija ts
			WHERE ius.student=ss.student AND ius.akademska_godina=ss.akademska_godina AND ius.semestar=ss.semestar AND
				ius.student=o.id AND ius.akademska_godina=ag.id AND ss.studij=s.id AND s.tipstudija=ts.id
			ORDER BY ag.id, s.naziv, ss.semestar, o.prezime, o.ime");
	while($r10 = db_fetch_assoc($q10)) {
		$ispis_ime = "<a href=\"?sta=studentska/osobe&amp;akcija=edit&amp;osoba=" . $r10['id_studenta']. "\" target=\"_blank\">" . $r10['prezime'] . " " . $r10['ime'] . "</a>" . " (" . $r10['brindexa'] . ")";
		$ispis_studij = $r10['naziv_studija'] . ", " . $r10['semestar'] . ". semestar, " .$r10['naziv_godine'];
		$id_celije = "status" . $r10['id_studenta'] . "-" . $r10['semestar'];
		$javascript_niz .= "{ student: " . $r10['id_studenta'] . ", studij: " . $r10['id_studija'] . ", godina: " . $r10['id_godine'] . ", semestar: " . $r10['semestar'] . "},\n";
		
		?>
		<tr>
			<td><?=$ispis_ime?></td>
			<td><?=$ispis_studij?></td>
			<td id="<?=$id_celije?>">&nbsp;</td>
		</tr>
		<?
	}
	
	?>
	</table>
	<script>
	var za_provjeru = [ <?=$javascript_niz?> ];
	</script>
	<p><? daj_dugmice('upis_vise') ?></p>
	<p><a href="?sta=studentska/export_import">Nazad</a></p>
	<?
	
	
	return;
}




if (param('akcija') == "ocjene") {
	?>
	<h3>Izvoz unesenih ocjena</h3>
	<p>Izaberite predmet:</p>
	<ul>
	<?
	
	$q20 = db_query("SELECT pp.id, pp.naziv FROM izvoz_ocjena io, konacna_ocjena ko, pasos_predmeta pp
			WHERE io.student=ko.student AND io.predmet=ko.predmet AND ko.pasos_predmeta=pp.id
			GROUP BY ko.predmet ORDER BY pp.naziv");
	while(db_fetch2($q20, $id_pasosa, $naziv_predmeta)) {
		?>
		<li><a href="?sta=studentska/export_import&amp;akcija=ocjene_predmet&amp;id_pasosa=<?=$id_pasosa?>">
		<?=$naziv_predmeta?></a></li>
		<?
	}
	?>
	</ul>
	<?
	return;
}

if (param('akcija') == "ocjene_predmet") {
	$id_pasosa = int_param('id_pasosa');
	$neprovjerene = int_param("preskoci_neprovjerene");
	$naziv_predmeta = db_get("SELECT naziv FROM pasos_predmeta WHERE id=$id_pasosa");

	?>
	<h3>Izvoz unesenih ocjena za predmet <?=$naziv_predmeta?></h3>
	<p><? daj_dugmice('ocjene') ?></p>
	<table border="0" id="tabelaPodataka">
	<tr><th>Student</th><th>Predmet</th><th>Ocjena</th><th>&nbsp;</th></tr>
	<?
	
	$javascript_niz = "";
	$dodaj_upit = "";
	if ($neprovjerene == 0) $dodaj_upit = "AND ko.datum_provjeren=1";
	$q10 = db_query("SELECT o.id id_studenta, o.ime, o.prezime, o.brindexa, ko.predmet, pp.naziv naziv_predmeta, 
				ag.id id_godine, ag.naziv naziv_godine, ko.ocjena, UNIX_TIMESTAMP(ko.datum_u_indeksu) datum, ko.datum_provjeren
			FROM izvoz_ocjena io, osoba o, konacna_ocjena ko, pasos_predmeta pp, akademska_godina ag
			WHERE io.student=ko.student AND io.predmet=ko.predmet AND io.student=o.id AND
				ko.akademska_godina=ag.id AND ko.pasos_predmeta=pp.id AND pp.id=$id_pasosa $dodaj_upit
			ORDER BY ag.id, pp.naziv, o.prezime, o.ime");
	while($r10 = db_fetch_assoc($q10)) {
		$ispis_ime = "<a href=\"?sta=studentska/osobe&amp;akcija=edit&amp;osoba=" . $r10['id_studenta']. "\" target=\"_blank\">" . $r10['prezime'] . " " . $r10['ime'] . "</a>" . " (" . $r10['brindexa'] . ")";
		$ispis_predmet = $r10['naziv_predmeta'] . " (" . $r10['naziv_godine'] . ")";
		$ispis_ocjena = $r10['ocjena'] . " (" . date("d. m. Y.", $r10['datum']) . ")";
		if ($r10['datum_provjeren'] != 1) $ispis_ocjena .= " (?)";
		$id_celije = "status" . $r10['id_studenta'];
		$javascript_niz .= "{ student: " . $r10['id_studenta'] . ", predmet: " . $r10['predmet'] . ", ocjena: " . $r10['ocjena'] . ", datum: " . $r10['datum'] . ", akademska_godina: " . $r10['id_godine'] . "},\n";
		
		?>
		<tr>
			<td><?=$ispis_ime?></td>
			<td><?=$ispis_predmet?></td>
			<td><?=$ispis_ocjena?></td>
			<td id="<?=$id_celije?>">&nbsp;</td>
		</tr>
		<?
	}
	
	?>
	</table>
	<?
	if ($neprovjerene == 0) {
		$q20 = db_query("SELECT o.id id_studenta, o.ime, o.prezime, o.brindexa, ko.predmet, pp.naziv naziv_predmeta, 
				ag.id id_godine, ag.naziv naziv_godine, ko.ocjena, UNIX_TIMESTAMP(ko.datum_u_indeksu) datum, ko.datum_provjeren
			FROM izvoz_ocjena io, osoba o, konacna_ocjena ko, pasos_predmeta pp, akademska_godina ag
			WHERE io.student=ko.student AND io.predmet=ko.predmet AND io.student=o.id AND
				ko.akademska_godina=ag.id AND ko.pasos_predmeta=pp.id AND pp.id=$id_pasosa AND ko.datum_provjeren=0
			ORDER BY ag.id, pp.naziv, o.prezime, o.ime");
		if (db_num_rows($q20)>0) {
			?>
			<h3>Ocjene čiji datum nije provjeren</h3>
			<table border="0">
			<tr><th>Student</th><th>Predmet</th><th>Ocjena</th><th>&nbsp;</th></tr>
			<?
		}
		while($r10 = db_fetch_assoc($q20)) {
			$ispis_ime = "<a href=\"?sta=studentska/osobe&amp;akcija=edit&amp;osoba=" . $r10['id_studenta']. "\" target=\"_blank\">" . $r10['prezime'] . " " . $r10['ime'] . "</a>" . " (" . $r10['brindexa'] . ")";
			$ispis_predmet = $r10['naziv_predmeta'] . " (" . $r10['naziv_godine'] . ")";
			$ispis_ocjena = $r10['ocjena'] . " (" . date("d. m. Y.", $r10['datum']) . ")";
			$id_celije = "status" . $r10['id_studenta'];
		
			?>
			<tr>
				<td><?=$ispis_ime?></td>
				<td><?=$ispis_predmet?></td>
				<td><?=$ispis_ocjena?></td>
				<td id="<?=$id_celije?>"><a href='#' onclick="potvrdi_datum('<?=$id_celije?>'); return false;">Popravi datum</a></td>
			</tr>
			<?
		}
		if (db_num_rows($q20)>0) {
			?>
			</table>
			<?
		}
	} 
	?>
	<script>
	var za_provjeru = [ <?=$javascript_niz?> ];
	</script>
	
	<div id="displayWindow" style="position:absolute; visibility:hidden; border:1px solid #333; background-color: #f8f8f8; width: 600px; height: 200px;">
		<div id="innerElement">
		<h2 style="align:center">Popravi datum</h2> 
		<table border="0" id="tabelaRazlika">
			<thead><tr><th>Polje</th><th>Zamger</th><th>Drugi sistem</th></tr></thead>
			<tbody><tbody>
		</table>
		<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button id="popraviZamger">Popravi u Zamgeru</button> 
			<button id="popraviIsss">Popravi u drugom sistemu</button> 
			<button id="ocistiRazlike">Očisti studenta</button>
			<button onclick="document.getElementById('displayWindow').style.visibility = 'hidden';">Zatvori</button>
		</p>
		</div>
	</div>
	
	<p><? daj_dugmice('ocjene') ?></p>
	<p><a href="?sta=studentska/export_import&amp;akcija=ocjene">Nazad</a></p>
	<?
	
	
	return;
}

if (param('akcija') == "promjena_podataka") {
	?>
	<h3>Promjena podataka studenta</h3>
	<p><? daj_dugmice('promjena_podataka') ?></p>
	<table border="0" id="tabelaPodataka">
	<tr><th>Student</th><th>&nbsp;</th></tr>
	<?
	
	$javascript_niz = "";
	$q10 = db_query("SELECT o.id id_studenta, o.ime, o.prezime, o.brindexa
			FROM izvoz_promjena_podataka ipp, osoba o
			WHERE ipp.student=o.id
			ORDER BY o.prezime, o.ime");
	while($r10 = db_fetch_assoc($q10)) {
		$ispis_ime = "<a href=\"?sta=studentska/osobe&amp;akcija=edit&amp;osoba=" . $r10['id_studenta']. "\" target=\"_blank\">" . $r10['prezime'] . " " . $r10['ime'] . "</a>" . " (" . $r10['brindexa'] . ")";
		$id_celije = "status" . $r10['id_studenta'];
		$javascript_niz .= "{ student: " . $r10['id_studenta'] . " },\n";
		
		?>
		<tr>
			<td><?=$ispis_ime?></td>
			<td id="<?=$id_celije?>">&nbsp;</td>
		</tr>
		<?
	}
	
	?>
	</table>
	
	<div id="displayWindow" style="position:absolute; visibility:hidden; border:1px solid #333; background-color: #f8f8f8; width: 600px; height: 200px;">
		<div id="innerElement">
		<h2 style="align:center">Razlike</h2> 
		<table border="0" id="tabelaRazlika">
			<thead><tr><th>Polje</th><th>Zamger</th><th>Drugi sistem</th></tr></thead>
			<tbody><tbody>
		</table>
		<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button id="popraviZamger">Popravi u Zamgeru</button> 
			<button id="popraviIsss">Popravi u drugom sistemu</button> 
			<button id="ocistiRazlike">Očisti studenta</button>
			<button onclick="document.getElementById('displayWindow').style.visibility = 'hidden';">Zatvori</button>
		</p>
		</div>
	</div>
	<script>
	var za_provjeru = [ <?=$javascript_niz?> ];
	</script>
	<p><? daj_dugmice('promjena_podataka') ?></p>
	<p><a href="?sta=studentska/export_import">Nazad</a></p>
	<?
	
	
	return;
}


// GLAVNI MENI

$ocjena_za_izvoz = db_get("SELECT COUNT(*) FROM izvoz_ocjena");
$studenata_za_izvoz = db_get("SELECT COUNT(*) FROM izvoz_upis_prva");
$upisa_za_izvoz = db_get("SELECT COUNT(*) FROM izvoz_upis_semestar");
$promjena_podataka_za_izvoz = db_get("SELECT COUNT(*) FROM izvoz_promjena_podataka");

?>

<p>Podaci za izvoz:</p>
<ul>
	<li><a href="?sta=studentska/export_import&amp;akcija=novi_studenti"><?=$studenata_za_izvoz?> novih studenata za upis na 1. godinu</a></li>
	<li><a href="?sta=studentska/export_import&amp;akcija=upis_vise"><?=$upisa_za_izvoz?> upis studenata na više semestre</a></li>
	<li><a href="?sta=studentska/export_import&amp;akcija=ocjene"><?=$ocjena_za_izvoz?> ocjena</a></li>
	<li><a href="?sta=studentska/export_import&amp;akcija=promjena_podataka"><?=$promjena_podataka_za_izvoz?> promjena podataka studenata</a></li>
</ul>

<?


}

function daj_dugmice($tip) {
	?>
	<button onclick="servis_provjera('<?=$tip?>');" class="dugmeProvjera">Provjeri podatke</button> 
	<button onclick="servis_slanje('<?=$tip?>');" class="dugmeSlanje" style="display:none">Pošalji podatke</button> 
	<button onclick="servis_ciscenje('<?=$tip?>');" class="dugmeCiscenje" style="display:none">Očisti provjerene</button>
	<button onclick="location.reload();">Osvježi spisak</button>
	<?
}

?>

