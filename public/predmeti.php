<?

// PUBLIC/PREDMETI - spisak predmeta u finom stablu

// v3.9.1.0 (2008/02/09) + Novi modul: public/predmeti



function public_predmeti($modul) {
	// $modul - gdje vodi link, ostaviti prazno ako ne zelite link,
	// ako modul trazi dodatne parametre - navedite ih npr.
	// "saradnik/grupa&id=0"
	$link = "<a href=\"?sta=$modul&predmet=--PK--\" target=\"_blank\">";
	$linka = "</a>";
	if ($modul == "") $link=$linka="";

	?>
	<script language="JavaScript">
		function toggleVisibility(ime){
			var me = document.getElementById(ime);
			var img = document.getElementById('img-'+ime);
			if (me.style.display=="none"){
				me.style.display="inline";
				img.src="images/minus.png";
			}
			else {
				me.style.display="none";
				img.src="images/plus.png";
			}
		}
	</script>
	<?

	function dajplus($layerid,$layername) {
		return "<img src=\"images/plus.png\" width=\"13\" height=\"13\" id=\"img-$layerid\" onclick=\"toggleVisibility('$layerid')\"> $layername <div id=\"$layerid\" style=\"display:none\">";
	}

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
				print dajplus("sem-$r3[0]-$r2[0]-$r1[0]","$r3[0]. semestar");
				$q4 = myquery("select pk.id,p.naziv from predmet as p, ponudakursa as pk where pk.predmet=p.id and pk.akademska_godina=$r1[0] and pk.studij=$r2[0] and pk.semestar=$r3[0] order by p.naziv");
				while ($r4 = mysql_fetch_row($q4)) {
					print  "<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
					$linkp = str_replace("--PK--",$r4[0],$link);
					print $linkp.$r4[1].$linka;
				}
				print "</div>\n";
			}
			print "</div>\n";
		}
		print "</div>\n";
	}
	
//	$q1 = myquery("select pk.id,p.naziv,ag.naziv from predmet as p, ponudakursa as pk, akademska_godina as ag where ag.id=pk.akademska_godina and pk.predmet=p.id order by ag.naziv,p.naziv");
//	print "<p>Izaberite predmet:</p>\n<ul>";
//	while ($r1 = mysql_fetch_row($q1)) {
//		print "<li><a href=\"pregled-public.php?predmet=$r1[0]\">$r1[1] ($r1[2])</a></li>";
//	}
}

?>