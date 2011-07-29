<?

// PUBLIC/PREDMETI - spisak predmeta u finom stablu

// v3.9.1.0 (2008/02/09) + Novi modul: public/predmeti
// v3.9.1.1 (2008/09/01) + Funkcija dajplus() izbacena u globalni opseg jer se u suprotnom funkcija public_predmet() nije mogla pozivati vise puta (sto je svakako nemoguce zbog CSS IDova ali eto...)
// v4.0.0.0 (2009/02/19) + Release
// v4.0.9.1 (2009/04/30) + Modul izvjestaj/predmet sada umjesto ponudekursa prima predmet i ag



function public_predmeti($modul) {
	// $modul - gdje vodi link, ostaviti prazno ako ne zelite link,
	// ako modul trazi dodatne parametre - navedite ih npr.
	// "saradnik/grupa&id=0"

	$link = "<a href=\"?sta=$modul&predmet=--PK--\" target=\"_blank\">";
	$linka = "</a>";
	if ($modul == "") $link=$linka="";

	// Javascript za ajah
	?>
	<script language="JavaScript">
	function ucitavaj(semestar, studij, ag) {
		var rp=document.getElementById('sem-'+semestar+'-'+studij+'-'+ag);
		if (rp.innerHTML != "prazan") return; // Vec je ucitan
		rp.innerHTML = "<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Molimo sačekajte...";
		ajah_start("?sta=common/ajah&akcija=spisak_predmeta&ag="+ag+"&studij="+studij+"&semestar="+semestar, "", "napuni_rezultate("+semestar+","+studij+","+ag+")");
	}
	function napuni_rezultate(semestar,studij,ag) {
		var rp=document.getElementById('sem-'+semestar+'-'+studij+'-'+ag);
		var tekst = frames['zamger_ajah'].document.body.innerHTML;
		rp.innerHTML="";
		var oldpozicija=0, pozicija=0;
		do {
			// Uzimam jedan red
			var pozicija = tekst.indexOf('|',oldpozicija);
			var tmptekst = tekst.substr(oldpozicija,pozicija-oldpozicija);
			if (tmptekst.length<2) { oldpozicija=pozicija+1; continue; }
			if (tmptekst == "OK") break;
			rp.innerHTML += "<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			oldpozicija=pozicija+1;

			// Razdvajam id predmeta od naziva predmeta
			var pidpos = tmptekst.indexOf(' ');
			var predmetid = tmptekst.substr(0,pidpos);
			var predmetnaziv = tmptekst.substr(pidpos+1);
			var linkp = '<?=$link?>';
			linkp=linkp.replace("--PK--", predmetid+"&ag="+ag);
			rp.innerHTML += linkp+predmetnaziv+"</a>";
		} while (pozicija>=0);
	}
	</script>

	<?

	// Skripta daj_stablo se sada nalazi u js/stablo.js, a ukljucena je u index.php

	$q1 = myquery("select ag.id,ag.naziv from akademska_godina as ag where (select count(*) from ponudakursa as pk where pk.akademska_godina=ag.id)>0 order by ag.id");

	while ($r1 = mysql_fetch_row($q1)) {
		print "<br/>".dajplus("ag-$r1[0]","$r1[1] akademska godina");
		$q2 = myquery("select s.id, s.naziv from studij as s where (select count(*) from ponudakursa as pk where pk.akademska_godina=$r1[0] and pk.studij=s.id)>0 order by s.id");
		while ($r2 = mysql_fetch_row($q2)) {
			print "<br/>&nbsp;&nbsp;&nbsp;&nbsp;";
			print dajplus("studij-$r2[0]-$r1[0]",$r2[1]);
			$q3 = myquery("select semestar from ponudakursa where studij=$r2[0] and akademska_godina=$r1[0] group by semestar order by semestar");
			while ($r3 = mysql_fetch_row($q3)) {
				print "<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				print "<img src=\"images/plus.png\" width=\"13\" height=\"13\" id=\"img-sem-$r3[0]-$r2[0]-$r1[0]\" onclick=\"daj_stablo('sem-$r3[0]-$r2[0]-$r1[0]'); ucitavaj('$r3[0]', '$r2[0]', '$r1[0]');\"> $r3[0]. semestar <div id=\"sem-$r3[0]-$r2[0]-$r1[0]\" style=\"display:none\">prazan</div>";
			}
			print "</div>\n";
		}
		print "</div>\n";
	}

	print ajah_box();
}

function dajplus($layerid,$layername) {
	return "<img src=\"images/plus.png\" width=\"13\" height=\"13\" id=\"img-$layerid\" onclick=\"daj_stablo('$layerid')\"> $layername <div id=\"$layerid\" style=\"display:none\">";
}

?>
