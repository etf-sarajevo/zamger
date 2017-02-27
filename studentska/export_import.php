<?

function studentska_export_import() {

$export_ws = "index.php?sta=ws/export";

?>
<h2>Uvoz/Izvoz podataka</h2>

<SCRIPT>
// JavaScript za provjeru validnosti podataka putem web servisa
var export_url = '<?=$export_ws?>';
var za_slanje = [];
var dugmad;

// Iteriranje kroz niz elemenata za provjeru
function servis_provjera(tip) {
	if (za_provjeru.length > 0) {
		var stavka = za_provjeru.shift();
		servis_provjera_single(stavka, tip);
	} else if (za_slanje.length > 0) {
		dugmad = document.getElementsByClassName('dodajDugme');
		for (var i=0; i<dugmad.length; i++) {
			dugmad[i].innerHTML += ' <button onclick="servis_slanje(\'' + tip + '\');">Pošalji podatke</button>';
		}
	}
}


// Iteriranje kroz niz elemenata za slanje
function servis_slanje(tip) {
	if (za_slanje.length > 0) {
		var stavka = za_slanje.shift();
		servis_slanje_single(stavka, tip);
	}
}

// Prikaži grešku
function provjera_greska(student, poruka) {
	var celija = document.getElementById('status'+student);
	celija.innerHTML = '<img src="images/16x16/brisanje.png" width="16" height="16"> '+poruka;
}

// Provjera jedne stavke
function servis_provjera_single(stavka, tip) {
	var xmlhttp = new XMLHttpRequest();
	var url = export_url + "&tip=" + tip + "&akcija=provjera";
	if (tip == "ocjene") 
		url += "&student=" + stavka.student + "&predmet=" + stavka.predmet + "&ocjena=" + stavka.ocjena + "&datum=" + stavka.datum;
	else
		url += "&student=" + stavka.student + "&studij=" + stavka.studij + "&godina=" + stavka.godina;
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
			result = JSON.parse(xmlhttp.responseText);
			if (result.success == "true") {
				// Sada nešto
				za_slanje.push(stavka);
				servis_provjera(tip);
			} else {
				console.log("Web servis za provjeru vratio success=false");
				console.log(result);
				provjera_greska(stavka.student, "Provjera nije uspjela");
				servis_provjera(tip);
			}
			return false;
		}
		if (xmlhttp.readyState == 4) {
			console.log("Serverska greška kod pozivanja web servisa za provjeru.");
			console.log("readyState "+xmlhttp.readyState+" status "+xmlhttp.status);
			provjera_greska(stavka.student, "Servis nedostupan");
			servis_provjera(tip);
		}
		//console.log("readyState "+xmlhttp.readyState+" status "+xmlhttp.status);
	}
	xmlhttp.open("GET", url, true);
	xmlhttp.send();
}

</SCRIPT>
<?


if (param('akcija') == "novi_studenti") {
	?>
	<h3>Novi studenti za upis na 1. godinu studija</h3>
	<p class="dodajDugme"><button onclick="servis_provjera('upis_prva');">Provjeri podatke</button></p>
	<table border="0">
	<tr><th>Student</th><th>Studij</th><th>&nbsp;</th></tr>
	<?
	
	$javascript_niz = "";
	$q10 = db_query("SELECT o.id id_studenta, o.ime, o.prezime, o.brindexa, s.id id_studija, s.naziv naziv_studija, 
				ts.ciklus, ag.id id_godine, ag.naziv naziv_godine
			FROM izvoz_upis_prva iup, student_studij ss, osoba o, studij s, akademska_godina ag, tipstudija ts
			WHERE iup.student=ss.student AND iup.akademska_godina=ss.akademska_godina AND ss.semestar=1 AND
				iup.student=o.id AND iup.akademska_godina=ag.id AND ss.studij=s.id AND s.tipstudija=ts.id
			ORDER BY ag.id, s.naziv, o.prezime, o.ime");
	while($r10 = db_fetch_assoc($q10)) {
		$ispis_ime = $r10['prezime'] . " " . $r10['ime'] . " (" . $r10['brindexa'] . ")";
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
	<p class="dodajDugme"><button onclick="servis_provjera();">Provjeri podatke</button></p>
	<p><a href="?sta=studentska/export_import">Nazad</a></p>
	<?
	
	
	return;
}




if (param('akcija') == "upis_vise") {
	?>
	<h3>Upis studenata na više semestre/godine studije</h3>
	<p class="dodajDugme"><button onclick="servis_provjera('upis_vise');">Provjeri podatke</button></p>
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
		$ispis_ime = $r10['prezime'] . " " . $r10['ime'] . " (" . $r10['brindexa'] . ")";
		$ispis_studij = $r10['naziv_studija'] . ", " . $r10['semestar'] . ". semestar, " .$r10['naziv_godine'];
		$id_celije = "status" . $r10['id_studenta'];
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
	<p class="dodajDugme"><button onclick="servis_provjera();">Provjeri podatke</button></p>
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
	$naziv_predmeta = db_get("SELECT naziv FROM pasos_predmeta WHERE id=$id_pasosa");

	?>
	<h3>Izvoz unesenih ocjena za predmet <?=$naziv_predmeta?></h3>
	<p class="dodajDugme"><button onclick="servis_provjera('ocjene');">Provjeri podatke</button></p>
	<table border="0">
	<tr><th>Student</th><th>Predmet</th><th>Ocjena</th><th>&nbsp;</th></tr>
	<?
	
	$javascript_niz = "";
	$q10 = db_query("SELECT o.id id_studenta, o.ime, o.prezime, o.brindexa, ko.predmet, pp.naziv naziv_predmeta, 
				ag.id id_godine, ag.naziv naziv_godine, ko.ocjena, UNIX_TIMESTAMP(ko.datum_u_indeksu) datum
			FROM izvoz_ocjena io, osoba o, konacna_ocjena ko, pasos_predmeta pp, akademska_godina ag
			WHERE io.student=ko.student AND io.predmet=ko.predmet AND io.student=o.id AND
				ko.akademska_godina=ag.id AND ko.pasos_predmeta=pp.id AND pp.id=$id_pasosa
			ORDER BY ag.id, pp.naziv, o.prezime, o.ime");
	while($r10 = db_fetch_assoc($q10)) {
		$ispis_ime = $r10['prezime'] . " " . $r10['ime'] . " (" . $r10['brindexa'] . ")";
		$ispis_predmet = $r10['naziv_predmeta'] . " (" . $r10['naziv_godine'] . ")";
		$ispis_ocjena = $r10['ocjena'] . " (" . date("d. m. Y.", $r10['datum']) . ")";
		$id_celije = "status" . $r10['id_studenta'];
		$javascript_niz .= "{ student: " . $r10['id_studenta'] . ", predmet: " . $r10['predmet'] . ", ocjena: " . $r10['ocjena'] . ", datum: " . $r10['datum'] . "},\n";
		
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
	<script>
	var za_provjeru = [ <?=$javascript_niz?> ];
	</script>
	<p class="dodajDugme"><button onclick="servis_provjera();">Provjeri podatke</button></p>
	<p><a href="?sta=studentska/export_import">Nazad</a></p>
	<?
	
	
	return;
}


// GLAVNI MENI

$ocjena_za_izvoz = db_get("SELECT COUNT(*) FROM izvoz_ocjena");
$studenata_za_izvoz = db_get("SELECT COUNT(*) FROM izvoz_upis_prva");
$upisa_za_izvoz = db_get("SELECT COUNT(*) FROM izvoz_upis_semestar");

?>

<p>Podaci za izvoz:</p>
<ul>
	<li><a href="?sta=studentska/export_import&amp;akcija=novi_studenti"><?=$studenata_za_izvoz?> novih studenata za upis na 1. godinu</a></li>
	<li><a href="?sta=studentska/export_import&amp;akcija=upis_vise"><?=$upisa_za_izvoz?> upis studenata na više semestre</a></li>
	<li><a href="?sta=studentska/export_import&amp;akcija=ocjene"><?=$ocjena_za_izvoz?> ocjena</a></li>
</ul>

<?


}

?>

