<?

// PUBLIC/PREDMETI - spisak predmeta u finom stablu



function public_predmeti($modul) {
	require_once("lib/zamgerui.php"); // zbog ajax_box

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
		// Prosljeđujemo podatke iz PHPa u JS
		var link = '<?=$link?>';
		var linka = '<?=$linka?>';
		
		var rp=document.getElementById('sem-'+semestar+'-'+studij+'-'+ag);
		if (rp.innerHTML != "prazan") return; // Vec je ucitan
		rp.innerHTML = "<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Molimo sačekajte...";

		ajax_start(
			"ws/predmet", 
			"GET",
			{ "studij" : studij, "semestar" : semestar, "ag" : ag },
			function(predmeti) { 
				var rp=document.getElementById('sem-'+semestar+'-'+studij+'-'+ag);
				rp.innerHTML="";
				for (var id in predmeti) {
					if (predmeti.hasOwnProperty(id)) {
						rp.innerHTML += "<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
						linkp=link.replace("--PK--", id+"&ag="+predmeti[id]['akademska_godina']);
						rp.innerHTML += linkp+predmeti[id]['naziv']+linka;
					}
				}
			}
		);
	}
	</script>

	<?

	// Skripta daj_stablo se sada nalazi u js/stablo.js, a ukljucena je u index.php

	$q1 = db_query("select ag.id,ag.naziv from akademska_godina as ag where (select count(*) from ponudakursa as pk where pk.akademska_godina=ag.id)>0 order by ag.id");

	while ($r1 = db_fetch_row($q1)) {
		print "<br/>".dajplus("ag-$r1[0]","$r1[1] akademska godina");
		$q2 = db_query("select s.id, s.naziv from studij as s where (select count(*) from ponudakursa as pk where pk.akademska_godina=$r1[0] and pk.studij=s.id)>0 order by s.id");
		while ($r2 = db_fetch_row($q2)) {
			print "<br/>&nbsp;&nbsp;&nbsp;&nbsp;";
			print dajplus("studij-$r2[0]-$r1[0]",$r2[1]);
			$q3 = db_query("select semestar from ponudakursa where studij=$r2[0] and akademska_godina=$r1[0] group by semestar order by semestar");
			while ($r3 = db_fetch_row($q3)) {
				print "<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				print "<img src=\"static/images/plus.png\" width=\"13\" height=\"13\" id=\"img-sem-$r3[0]-$r2[0]-$r1[0]\" onclick=\"daj_stablo('sem-$r3[0]-$r2[0]-$r1[0]'); ucitavaj('$r3[0]', '$r2[0]', '$r1[0]');\"> $r3[0]. semestar <div id=\"sem-$r3[0]-$r2[0]-$r1[0]\" style=\"display:none\">prazan</div>";
			}
			print "</div>\n";
		}
		print "</div>\n";
	}

	print ajax_box();
}

function dajplus($layerid,$layername) {
	return "<img src=\"static/images/plus.png\" width=\"13\" height=\"13\" id=\"img-$layerid\" onclick=\"daj_stablo('$layerid')\"> $layername <div id=\"$layerid\" style=\"display:none\">";
}

?>
