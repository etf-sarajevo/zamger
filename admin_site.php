<?

// v3.0.1.1 (2007/09/12) + Novi modul: site admin (za sada samo kompaktovanje)
// v3.0.1.2 (2007/10/02) + Dodan logging
// v3.0.1.3 (2007/10/13) + Nova struktura baze za predmete; novi tab Log za analizu loga
// v3.0.1.4 (2007/11/06) + Robusnije generisanje loga; nove logging opcije

function admin_site() {

global $userid;

global $_lv_; // We use form generators


# Vrijednosti

$tab=$_REQUEST['tab'];
if ($tab=="") $tab="Kompaktovanje";


###############
# Akcije
###############


if ($_POST['akcija'] == "kompaktuj") {
	$predmet = intval($_POST['predmet']);
	$q10 = myquery("select p.naziv, ag.naziv from ponudakursa as pk, predmet as p, akademska_godina as ag where pk.akademska_godina=ag.id and pk.id=$predmet and pk.predmet=p.id");
	if (!($r10 = mysql_fetch_row($q10))) {
		niceerror("Predmet nije pronađen u bazi");
		return;
	}
	nicemessage("Kompaktujem predmet $r10[0] ($r10[1])");
	
	// Zadaće
	$q11 = myquery("select id,zadataka from zadaca where predmet=$predmet");
	$totcount=0;
	$diffcount=0;
	$stdincount=0;
	while ($r11 = mysql_fetch_row($q11)) {
		$zadaca = $r11[0];
		$brzad = $r11[1];
		
		// Historija statusa zadaće
		for ($i=1; $i<=$brzad; $i++) {
			$q12 = myquery("select id,student from zadatak where zadaca=$zadaca and redni_broj=$i order by student,id desc");
			$student=0;
			$count=0;
			while ($r12 = mysql_fetch_row($q12)) {
				if ($student != $r12[1]) {
					if ($count>0) {
//						print("$count statusa za ($student, $zadaca, $i)... ");
						$totcount += $count;
						$count=0;
					}
					$student=$r12[1];
				} else {
					$q13 = myquery("delete from zadatak where id=$r12[0]");
					$count++;
				}

				$q14 = myquery("delete from zadatakdiff where zadatak=$r12[0]");
				$diffcount++;
			}

			$q15 = myquery("select count(*) from stdin where zadaca=$zadaca and redni_broj=$i");
			$stdincount += mysql_result($q15,0,0);
			$q16 = myquery("delete from stdin where zadaca=$zadaca and redni_broj=$i");
		}
	}
	nicemessage("Obrisano: $totcount starih statusa zadaće, $diffcount diffova, $stdincount unosa.");

	logthis("Kompaktovana baza za predmet $predmet");
}



###############
# Ispis tabova
###############


function printtab($ime,$tab) {
	if ($ime==$tab) 
		print '<td bgcolor="#DDDDDD" width="50">'.$ime.'</td>'."\n";
	else
		print '<td bgcolor="#BBBBBB" width="50"><a href="qwerty.php?sta=siteadmin&&tab='.$ime.'">'.$ime.'</a></td>'."\n";
}

?>
<p><h3>Site Admin</h3></p>

<table border="0" cellspacing="1" cellpadding="5" width="750">
<tr>
<td width="50">&nbsp;</td>
<? 
printtab("Kompaktovanje",$tab); 
printtab("Log",$tab); 
?>
<td bgcolor="#BBBBBB" width="50"><a href="qwerty.php">Nazad</a></td>
<td width="550">&nbsp;</td>
</tr>
<tr>
<td width="50">&nbsp;</td>
<td colspan="8" bgcolor="#DDDDDD" width="700">
<?


if ($tab == "Kompaktovanje") {
	?>
	<p><b>Kompaktovanje baze</b><br/>
	Ovo je operacija kojim se iz baze brišu svi podaci koji nisu potrebni za ispravno izračunavanje ocjene. To uključuje: historiju starih statusa zadaće, razlike (diffove) zadaća, komentare i pomoćne ocjene za grupe/studente, unose za izvršavanje zadaće na serveru.</p>
	<p>Izaberite koji predmet želite kompaktovati:<br/>
	<?=genform() ?>
	<input type="hidden" name="akcija" value="kompaktuj">
	<select name="predmet">
	<?
		$q100 = myquery("select pk.id, p.naziv, ag.naziv from ponudakursa as pk, predmet as p, akademska_godina as ag where pk.akademska_godina=ag.id and pk.predmet=p.id order by ag.naziv,p.naziv");
		while ($r100 = mysql_fetch_row($q100)) {
			print "<option value=\"$r100[0]\">$r100[1] ($r100[2])</option>\n";
		}
	?>
	</select>
	<input type="submit" value=" Kompaktuj "></form>
	<?
}


if ($tab == "Log") {
	$maxlogins = 20;
	$stardate = intval($_GET['stardate']);
	if ($stardate == 0) {
		$q199 = myquery("select id from log order by id desc limit 1");
		$stardate = mysql_result($q199,0,0)+1;
	}

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

	$q200 = myquery ("select id, UNIX_TIMESTAMP(vrijeme), userid, dogadjaj from log where id<$stardate order by id desc");
	$lastlogin = array();
	$eventshtml = array();
	$logins=0;
	while ($r200 = mysql_fetch_row($q200)) {
		$nicedate = " (".date("d.m.Y. H:i:s", $r200[1]).")";
		$usr=$r200[2];
		if ($lastlogin[$usr]==0) {
			$lastlogin[$usr]=$r200[0];
			$logins++;
			if ($logins > $maxlogins) {
				$stardate=$r200[0]+1;
				break; // izlaz iz while
			}
		}

		if (substr($r200[3],0,5) == "Login") {
			$eventshtml[$lastlogin[$usr]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\">Login $r200[2] $nicedate\n".$eventshtml[$lastlogin[$usr]];
			$lastlogin[$usr]=0;
		}
		else if (preg_match("/Admin grupa (\d+)/", $r200[3], $matches)) {
			$q203 = myquery("select p.naziv, l.naziv from labgrupa as l, predmet as p, ponudakursa as pk where l.id=$matches[1] and l.predmet=pk.id and pk.predmet=p.id");
			if (mysql_num_rows($q203)>0) {
				$eventshtml[$lastlogin[$r200[2]]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\">Predmet ".mysql_result($q203,0,0).", labgrupa ".mysql_result($q203,0,1).$nicedate."\n".$eventshtml[$lastlogin[$r200[2]]];
			} else {
				$eventshtml[$lastlogin[$r200[2]]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\">".$r200[3].$nicedate."\n".$eventshtml[$lastlogin[$r200[2]]];
			}
		} 
		else if (preg_match("/Poslana zadaca (\d+)-(\d+)/", $r200[3], $matches)) {
			$q204 = myquery("select p.naziv, z.naziv from zadaca as z, predmet as p, ponudakursa as pk where z.id=$matches[1] and z.predmet=pk.id and pk.predmet=p.id");
			if (mysql_num_rows($q204)>0) {
				$eventshtml[$lastlogin[$r200[2]]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\">Poslana zadaća ".mysql_result($q204,0,1)." / $matches[2] (predmet ".mysql_result($q204,0,0).")".$nicedate."\n".$eventshtml[$lastlogin[$r200[2]]];
			} else {
				$eventshtml[$lastlogin[$r200[2]]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\">".$r200[3].$nicedate."\n".$eventshtml[$lastlogin[$r200[2]]];
			}
		}
		else if (preg_match("/Admin Predmet (\d+) - (.+)/", $r200[3], $matches)) {
			$q205 = myquery("select p.naziv from predmet as p, ponudakursa as pk where pk.id=$matches[1] and pk.predmet=p.id");
			if (mysql_num_rows($q205)>0) {
				$eventshtml[$lastlogin[$r200[2]]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\">Admin Predmet ".mysql_result($q205,0,0)." - $matches[2]".$nicedate."\n".$eventshtml[$lastlogin[$r200[2]]];
			} else {
				$eventshtml[$lastlogin[$r200[2]]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\">".$r200[3].$nicedate."\n".$eventshtml[$lastlogin[$r200[2]]];
			}
		}
		else if (preg_match("/Registrovan cas \d+ za labgrupu (\d+)/", $r200[3], $matches)) {
			$q203 = myquery("select p.naziv, l.naziv from labgrupa as l, predmet as p, ponudakursa as pk where l.id=$matches[1] and l.predmet=pk.id and pk.predmet=p.id");
			if (mysql_num_rows($q203)>0) {
				$eventshtml[$lastlogin[$r200[2]]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\">Registrovan čas za labgrupu ".mysql_result($q203,0,1)." (predmet ".mysql_result($q203,0,0).")".$nicedate."\n".$eventshtml[$lastlogin[$r200[2]]];
			} else {
				$eventshtml[$lastlogin[$r200[2]]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\">".$r200[3].$nicedate."\n".$eventshtml[$lastlogin[$r200[2]]];
			}
		}
		else if (preg_match("/Izmjena ličnih podataka studenta (\d+)/", $r200[3], $matches)) {
			$q206 = myquery("select ime, prezime from student where id=$matches[1]");
			if (mysql_num_rows($q206)>0) {
				$eventshtml[$lastlogin[$r200[2]]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\">Izmjena ličnih podataka studenta ".mysql_result($q206,0,0)." ".mysql_result($q206,0,1).$nicedate."\n".$eventshtml[$lastlogin[$r200[2]]];
			} else {
				$eventshtml[$lastlogin[$r200[2]]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\">".$r200[3].$nicedate."\n".$eventshtml[$lastlogin[$r200[2]]];
			}
		}
		else if (preg_match("/Izvrsena zadaca \((\d+),(\d+),(\d+)\)/", $r200[3], $matches)) {
			$q204 = myquery("select z.naziv from zadaca as z, predmet as p, ponudakursa as pk where z.id=$matches[1] and z.predmet=pk.id and pk.predmet=p.id");
			$q206 = myquery("select ime, prezime from student where id=$matches[3]");
			if (mysql_num_rows($q204)>0 && mysql_num_rows($q206)>0) {
				$eventshtml[$lastlogin[$r200[2]]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\">Izvršena zadaća ".mysql_result($q204,0,0)." / $matches[2] (student ".mysql_result($q206,0,0)." ".mysql_result($q206,0,1).")".$nicedate."\n".$eventshtml[$lastlogin[$r200[2]]];
			} else {
				$eventshtml[$lastlogin[$r200[2]]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\">".$r200[3].$nicedate."\n".$eventshtml[$lastlogin[$r200[2]]];
			}
		}
		else if (preg_match("/Izmjena statusa zadace \((\d+),(\d+),(\d+)\)/", $r200[3], $matches)) {
			$q204 = myquery("select z.naziv from zadaca as z, predmet as p, ponudakursa as pk where z.id=$matches[1] and z.predmet=pk.id and pk.predmet=p.id");
			$q206 = myquery("select ime, prezime from student where id=$matches[3]");
			if (mysql_num_rows($q204)>0 && mysql_num_rows($q206)>0) {
				$eventshtml[$lastlogin[$r200[2]]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\">Izmjena statusa zadaće ".mysql_result($q204,0,0)." / $matches[2] (student ".mysql_result($q206,0,0)." ".mysql_result($q206,0,1).")".$nicedate."\n".$eventshtml[$lastlogin[$r200[2]]];
			} else {
				$eventshtml[$lastlogin[$r200[2]]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\">".$r200[3].$nicedate."\n".$eventshtml[$lastlogin[$r200[2]]];
			}
		}
		else if (preg_match("/Nastavnik (\d+) dodan na predmet (\d+)/", $r200[3], $matches)) {
			$q205 = myquery("select p.naziv from predmet as p, ponudakursa as pk where pk.id=$matches[2] and pk.predmet=p.id");
			$q207 = myquery("select ime,prezime from nastavnik where id=$matches[1]");
			if (mysql_num_rows($q205)>0 && mysql_num_rows($q207)>0) {
				$eventshtml[$lastlogin[$r200[2]]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\">Nastavnik '".mysql_result($q207,0,0)." ".mysql_result($q207,0,1)."' dodan na predmet '".mysql_result($q205,0,0)."' ".$nicedate."\n".$eventshtml[$lastlogin[$r200[2]]];
			} else {
				$eventshtml[$lastlogin[$r200[2]]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\">".$r200[3].$nicedate."\n".$eventshtml[$lastlogin[$r200[2]]];
			}

		}
		else if (preg_match("/Nastavnik (\d+) proglasen za admina predmeta (\d+)/", $r200[3], $matches)) {
			$q205 = myquery("select p.naziv from predmet as p, ponudakursa as pk where pk.id=$matches[2] and pk.predmet=p.id");
			$q207 = myquery("select ime,prezime from nastavnik where id=$matches[1]");
			if (mysql_num_rows($q205)>0 && mysql_num_rows($q207)>0) {
				$eventshtml[$lastlogin[$r200[2]]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\">Nastavnik '".mysql_result($q207,0,0)." ".mysql_result($q207,0,1)."' proglašen za admina predmeta '".mysql_result($q205,0,0)."' ".$nicedate."\n".$eventshtml[$lastlogin[$r200[2]]];
			} else {
				$eventshtml[$lastlogin[$r200[2]]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\">".$r200[3].$nicedate."\n".$eventshtml[$lastlogin[$r200[2]]];
			}

		}
		else if (preg_match("/Student labgrupa (\d+)/", $r200[3], $matches)) {
			$q203 = myquery("select p.naziv, l.naziv from labgrupa as l, predmet as p, ponudakursa as pk where l.id=$matches[1] and l.predmet=pk.id and pk.predmet=p.id");
			if (mysql_num_rows($q203)>0) {
				$eventshtml[$lastlogin[$r200[2]]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\">Statusna stranica, predmet ".mysql_result($q203,0,0).", labgrupa ".mysql_result($q203,0,1)." ".$nicedate."\n".$eventshtml[$lastlogin[$r200[2]]];
			} else {
				$eventshtml[$lastlogin[$r200[2]]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\">".$r200[3].$nicedate."\n".$eventshtml[$lastlogin[$r200[2]]];
			}
		}
		else {
			$eventshtml[$lastlogin[$r200[2]]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\">".$r200[3].$nicedate."\n".$eventshtml[$lastlogin[$r200[2]]];
		}
	}

	foreach ($eventshtml as $logid => $event) {
		$q201 = myquery("select auth.id,auth.admin,UNIX_TIMESTAMP(log.vrijeme) from auth,log where auth.id=log.userid and log.id=$logid");
		$userid = mysql_result($q201,0,0);
		$nicedate = " (".date("d.m.Y. H:i:s", mysql_result($q201,0,2)).")";

		if (mysql_result($q201,0,1)==1) {
			$usrimg="tutor"; 
			$q202 = myquery("select ime,prezime from nastavnik where id=$userid");
			$link = "qwerty.php?sta=nihada&tab=Nastavnici&akcija=edit&nastavnik=$userid";
		} else {
			$usrimg="user";
			$q202 = myquery("select ime,prezime from student where id=$userid");
			$link = "qwerty.php?sta=nihada&tab=Studenti&akcija=edit&student=$userid";
		}
		$imeprezime = mysql_result($q202,0,0)." ".mysql_result($q202,0,1);

		if (substr($event,0,4)!="<img")
			print "<img src=\"images/plus.png\" width=\"13\" height=\"13\" id=\"img-$logid\" onclick=\"toggleVisibility('$logid')\">
<img src=\"images/$usrimg.png\" width=\"16\" height=\"16\" align=\"center\">
<a href=\"$link\" target=\"_blank\">$imeprezime</a> $nicedate
<div id=\"$logid\" style=\"display:none\">\n";
		print "$event</div><br/>\n";
	}
	print "<p>&nbsp;</p><p><a href=\"".genuri()."&stardate=$stardate\">Sljedećih $maxlogins</a></p>";
}

?>
</td>
</tr>
</table>
<?

}

?>