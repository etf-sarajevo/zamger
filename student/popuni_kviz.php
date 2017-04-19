<?

// STUDENT/POPUNI_KVIZ - popunjavanje kviza



function student_popuni_kviz() {

	global $userid;
	
	$kviz = intval($_REQUEST['kviz']);
	if ($_REQUEST['akcija']=="salji") {
		// Ako je akcija salji, dodajemo vrijeme aktivacije
		$q5 = db_query("select vrijeme_aktivacije from kviz_student where student=$userid and kviz=$kviz");
		if (db_num_rows($q5)<1) {
			niceerror("Molimo ponovite kviz");
			zamgerlog("poslao popunjen kviz $kviz a nema stavke u student_kviz", 3);
			zamgerlog2("poslao popunjen kviz a nema stavke u student_kviz", $kviz);
			return;
		}
		$vrijeme_kraja = "'".db_result($q5,0,0)."' + INTERVAL (trajanje_kviza+60) SECOND";
		// Dodajemo 60 sekundi na trajanje, zbog evt. problema sa konekcijom
	} else
		$vrijeme_kraja = "vrijeme_kraj";

	$q10 = db_query("select naziv, predmet, akademska_godina, aktivan, vrijeme_pocetak<NOW(), $vrijeme_kraja > NOW(), labgrupa, ip_adrese, broj_pitanja, trajanje_kviza, prolaz_bodova FROM kviz where id=$kviz");
	if (db_num_rows($q10)<1) { // Postoji li kviz
		niceerror("Kviz ne postoji");
		zamgerlog("pristup nepostojecem kvizu $kviz", 3);
		zamgerlog2("pristup nepostojecem kvizu", $kviz);
		return;
	}
	
	$naziv_ankete = db_result($q10,0,0);
	$predmet = db_result($q10,0,1);
	$ag = db_result($q10,0,2);
	$broj_pitanja = db_result($q10,0,8);
	$trajanje_kviza = db_result($q10,0,9); // u sekundama
	$prolaz_bodova = db_result($q10,0,10);
	
	// Da li student sluša predmet? Ujedno i naziv predmeta
	$q20 = db_query("select p.naziv from student_predmet as sp, ponudakursa as pk, predmet as p where sp.student=$userid and sp.predmet=pk.id and pk.predmet=p.id and p.id=$predmet and pk.akademska_godina=$ag");
	if (db_num_rows($q20)<1) {
		niceerror("Nemate pristup ovom kvizu");
		zamgerlog("student nije na predmetu za kviz $kviz", 3);
		zamgerlog2("student nije na predmetu", $kviz);
		return;
	}
	$naziv_predmeta = db_result($q20,0,0);
	
	// Da li je aktivan kviz
	if (db_result($q10,0,3) != 1) {
		niceerror("Kviz nije aktivan");
		zamgerlog("kviz nije aktivan $kviz", 3);
		zamgerlog2("kviz nije aktivan", $kviz);
		return;
	}
	
	// Da li je vrijeme za kviz
	if (db_result($q10,0,4) != 1 || db_result($q10,0,5) != 1) {
		niceerror("Vrijeme za ovaj kviz je isteklo ".db_result($q10,0,4));
		zamgerlog("vrijeme isteklo za kviz $kviz", 3);
		zamgerlog2("vrijeme isteklo",$kviz);
		return;
	}

	// Da li je u labgrupi?
	$labgrupa=db_result($q10,0,6);
	if ($labgrupa>0) {
		$q30 = db_query("select count(*) from student_labgrupa where student=$userid and labgrupa=$labgrupa");
		if (db_result($q30,0,0)==0) {
			niceerror("Nemate pristup ovom kvizu");
			zamgerlog("student nije u labgrupi $labgrupa za kviz $kviz", 3);
			zamgerlog2("student nije u odgovarajucoj labgrupi", intval($labgrupa), intval($kviz));
			return;
		}
	}
	
	// Provjera IP adrese
	if (db_result($q10,0,7) != "") {
		$moja_ip = getip();
		$ispravna = false;

		$blokovi = explode(",", db_result($q10,0,7));
		foreach ($blokovi as $blok) {
			if (strstr($blok, "/")) { // adresa u CIDR formatu
				// Npr. 192.168.0.1/24
				// Preuzeto sa: http://pgregg.com/blog/2009/04/php-algorithms-determining-if-an-ip-is-within-a-specific-range.html
				list ($baza, $maska) = explode("/", $blok);
				$moja_f = ip2float($moja_ip);
				$baza_f = ip2float($baza);
				$netmask_dec = bindec( str_pad('', $maska, '1') . str_pad('', 32-$maska, '0') );
				$wildcard_dec = pow(2, (32-$maska)) - 1;
				$netmask_dec = ~ $wildcard_dec; 
				if (($moja_f & $netmask_dec) == ($baza_f & $netmask_dec)) {
					$ispravna = true;
					break;
				}
			}

			else if (strstr($blok, "-")) { // Raspon sa crticom
				// Npr. 10.0.0.1 - 10.0.0.15
				list ($prva, $zadnja) = explode("-", $blok);
				$moja_f = ip2float($moja_ip);
				$prva_f = ip2float($prva);
				$zadnja_f = ip2float($zadnja);
				if (($moja_f >= $prva_f) && ($moja_f <= $zadnja_f)) {
					$ispravna = true;
					break;
				}

			} else { // Pojedinačna adresa
				if ($moja_ip == $blok) {
					$ispravna = true;
					break;
				}
			}
		}

		// 
		if ($ispravna == false) {
			niceerror("Nemate pristup ovom kvizu");
			zamgerlog("losa ip adresa za kviz $kviz", 3);
			zamgerlog2("losa ip adresa", $kviz);
			return;
		}
	}
	
	
	// AKCIJA šalji
	// Sve ove provjere smo iskoristili da ih ne bismo ponovo kucali
	if ($_REQUEST['akcija'] == "salji" && check_csrf_token()) {
		$uk_bodova = 0;
		$rbr=1;
		for ($i=1; $i<=$broj_pitanja; $i++) {
			// MCSA - ako je dato više tačnih odgovora na pitanje, uvažavamo bilo koji
			$id_pitanja = $_REQUEST["rbrpitanje$i"];
			$tacan_odgovor = false;
			$q200 = db_query("select kp.tekst, kp.bodova, ko.id from kviz_pitanje as kp, kviz_odgovor as ko where kp.id=$id_pitanja and ko.kviz_pitanje=kp.id and ko.tacan=1");
			while ($r200 = db_fetch_row($q200)) {
				$tekst_pitanja = $r200[0];
				$bodova_pitanje = $r200[1];
				if ($_REQUEST["odgovor"][$id_pitanja] == $r200[2]) $tacan_odgovor = true;
			}
			
			$ispis_rezultata .= "<tr><td>$rbr.</td><td>".substr($tekst_pitanja,0,20)."...</td><td>";
			$rbr++;
			
			if ($tacan_odgovor) {
				$uk_bodova += $bodova_pitanje;
				$ispis_rezultata .= '<img src="static/images/16x16/ok.png" width="16" height="16">'."</td><td>$bodova_pitanje</td></tr>";
				$q205 = db_query("UPDATE kviz_pitanje SET ukupno=ukupno+1, tacnih=tacnih+1 WHERE id=$id_pitanja");
			} else {
				$ispis_rezultata .= '<img src="static/images/16x16/not_ok.png" width="16" height="16">'."</td><td>0</td></tr>";
				$q208 = db_query("UPDATE kviz_pitanje SET ukupno=ukupno+1 WHERE id=$id_pitanja");
			}
		}
		/*
		$q200 = db_query("select kp.id, kp.bodova, ko.id, ko.tacan, kp.tekst from kviz_pitanje as kp, kviz_odgovor as ko where ko.kviz_pitanje=kp.id and kp.kviz=$kviz");
		$ispis_rezultata = "";
		$rbr=1;
		while ($r200 = db_fetch_row($q200)) {
			$id_pitanja = $r200[0];
			$id_odgovora = $r200[2];
			if ($_REQUEST["odgovor"][$id_pitanja] == $id_odgovora && $r200[3]==1) 
				$uk_bodova += $r200[1];
			$tekst = $r200[4];
			
			if ($r200[3]!=1 || $_REQUEST["odgovor"][$id_pitanja]==0) continue;
			
			$ispis_rezultata .= "<tr><td>$rbr.</td><td>".substr($tekst,0,20)."...</td><td>";
			$rbr++;
			if ($_REQUEST["odgovor"][$id_pitanja] == $id_odgovora && $r200[3]==1) 
				$ispis_rezultata .= '<img src="static/images/16x16/ok.png" width="16" height="16">'."</td><td>$r200[1]</td></tr>";
			else
				$ispis_rezultata .= '<img src="static/images/16x16/not_ok.png" width="16" height="16">'."</td><td>0</td></tr>";
		}
		*/
		$q210 = db_query("update kviz_student set dovrsen=1, bodova=$uk_bodova where student=$userid and kviz=$kviz");
		print "<center><h1>Kviz završen</h1></center>\n";
		nicemessage("Osvojili ste $uk_bodova bodova.");
		if ($uk_bodova>=$prolaz_bodova) nicemessage("Čestitamo");
		?>
		<p><b>Tabela odgovora</b></p>
		<table border="1" cellspacing="0" cellpadding="2">
			<tr><td>R.br.</td><td>Pitanje</td><td>Tačno?</td><td>Bodova</td></tr>
		<?
		print $ispis_rezultata;
		print "</table>\n<br><br>\n";
		
		?><p><a href="#" onclick="window.close();">Zatvorite ovaj prozor</a></p><?
		zamgerlog("uradio kviz $kviz", 2);
		zamgerlog2("uradio kviz", $kviz);
		return;		
	}
	
	
	// Da li je već ranije popunjavao kviz?
	$q40 = db_query("select count(*) from kviz_student where student=$userid and kviz=$kviz");
	if (db_result($q40,0,0)>0) {
		niceerror("Već ste popunjavali ovaj kviz");
		zamgerlog("vec popunjavan kviz $kviz", 3);
		zamgerlog2("vec popunjavan kviz", $kviz);
		return;
	}
	
	// Ubacujemo da je započeo kviz
	$q50 = db_query("insert into kviz_student set student=$userid, kviz=$kviz, dovrsen=0, bodova=0, vrijeme_aktivacije=NOW()");
	
		
	// Student može sudjelovati u kvizu pa šaljemo HTML

	?>
	<html>
	<head>
	<title>Kviz</title>
	<script>
	var Tpocetak=new Date();
	var Tkraj=new Date();
	var active_element;

	function onBlur() {
		if (/*@cc_on!@*/false) { // check for Internet Explorer
			if (active_element != document.activeElement) {
				active_element = document.activeElement;
				return;
			}
		}

		alert('Vaš kviz je obustavljen jer ste pokušali raditi nešto što nije popunjavanje kviza!\nIzgubili ste bodove.');
		var forma=document.getElementsByName('slanje');
		forma[0].submit();
	}

	function ucitavanje() {
		Tkraj.setTime((new Date()).getTime()+<?=$trajanje_kviza?>*1000); // vrijeme je u milisekundama
		var t = setTimeout("provjeriVrijeme()",1000);
		if (/*@cc_on!@*/false) { // check for Internet Explorer
			active_element = document.activeElement;
			document.onfocusout = onBlur;
		} else {
			window.onblur = onBlur;
		}
		setTimeout("clp_clear();",1000);
	}
	
	function clp_clear() {
		var content=window.clipboardData.getData("Text");
		if (content==null) {
			window.clipboardData.clearData();
		}
		setTimeout("clp_clear();",1000);
	}

	function provjeriVrijeme() {
		var diff=new Date();
		diff.setTime(Tkraj-(new Date()));
		var vrijeme=document.getElementById('vrijeme');

		if (Tkraj<=(new Date())) {
			var forma=document.getElementsByName('slanje');
			forma[0].submit();
			return;
		}

		if (diff.getMinutes()==0 && diff.getSeconds()<30) {
			vrijeme.style.color='#FF0000';
		}
		var s = diff.getSeconds();
		if (s<10) s = "0"+s;
		
		vrijeme.innerHTML = diff.getMinutes()+":"+s;
		setTimeout("provjeriVrijeme()", 1000);
	}

	</script>
	</head>
	<body onload="ucitavanje()">
	<center><h2><?=$naziv_predmeta?></h2>
	<h2><?=$naziv_ankete?></h2></center>
	<div id="vrijemeinfo" style="width:150px; position:fixed; right:10px; top:20px; background-color: #303030; color:white;">Preostalo vrijeme: <span id="vrijeme"></span></div>
	<?


	// Ispisujemo pitanja kviza

	?>
	<br>
	<?=genform("POST", "slanje")?>
	<input type="hidden" name="akcija" value="salji">
	<table width=600px align=center>
	<?
		// ISPISI PITANJA
		$i=0;
		$q100 = db_query("select id, tip, tekst from kviz_pitanje where vidljivo=1 and kviz=$kviz order by RAND() limit 0,$broj_pitanja");
		while ($r100 = db_fetch_row($q100)) {
			$i++;
			$pitanje_id = $r100[0];
			$pitanje = $r100[2];
			
			?>
			<tr>
				<td valign=top><font size="5" face="serif"><?=$i ?>.</font></td>
				<td><font size="5" face="serif"><?=$pitanje ?></font>
				<input type="hidden" name="rbrpitanje<?=$i?>" value="<?=$pitanje_id?>">
					<br>
					<table>
					<?
						// ISPISI ODGOVORE ZA PITANJE
						$q110 = db_query("select id, tekst from kviz_odgovor where vidljiv=1 and kviz_pitanje=".$pitanje_id." order by RAND()");
						while ($r110 = db_fetch_row($q110)) {
							$odgovor = $r110[1];
							$odgovor_id = $r110[0];
							// FIXME: moze mapipulirati id odgovora i pitanja kada salje...
							?>
							<tr>
								<td><font size="5" face="serif">
								&nbsp;&nbsp; <input name="odgovor[<?=$pitanje_id ?>]" type="radio" value=<?=$odgovor_id ?>>&nbsp;&nbsp;<?=$odgovor; ?>
								</font></td>
							</tr>
							<?
						} // kraj ispisa odgovora 
					?>
					</table>
				</td>
			</tr>
			<tr><td colspan="2"><font size="5" face="serif">&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;</font></td></tr>
			<?
		}  // Kraj ispisa pitanja ?>
		
	
	</table>	
		
	<br><br>
		<input type="submit" value="Predaj">
	</form>
<!--
	 <div id=navbox style="width:150px; position:fixed; right:10px; top:120px">
		<b><div id=showTime2 style="width:100%; background-color: #303030; color:white;" ></div></b>
		<?PHP for ($j=1; $j<$i+1; $j++) { ?>
			<a href="#pitanje<?PHP echo $j ?>" style="text-decoration: none; color:white;"><div style="width:100%; background-color: darkgray;" ><b>Pitanje <?PHP echo $j ?></b></div>
		<?PHP } ?>
	 </div>
-->

	</body>
	</html>
	<?

}



// Hack za činjenicu da je long tip u PHPu signed
// Preuzeto sa: http://pgregg.com/blog/2009/04/php-algorithms-determining-if-an-ip-is-within-a-specific-range.html
function ip2float($ip) {
	return (float)sprintf("%u",ip2long($ip));
}

// Funkcija za dobivanje IP adrese korisnika iza proxy-ja
// Preuzeto sa: http://www.teachmejoomla.net/code/php/remote-ip-detection-with-php.html
function validip($ip) {
	if (!empty($ip) && ip2long($ip)!=-1) {
		$reserved_ips = array (
			array('0.0.0.0','2.255.255.255'),
			array('10.0.0.0','10.255.255.255'),
			array('127.0.0.0','127.255.255.255'),
			array('169.254.0.0','169.254.255.255'),
			array('172.16.0.0','172.31.255.255'),
			array('192.0.2.0','192.0.2.255'),
			array('192.168.0.0','192.168.255.255'),
			array('255.255.255.0','255.255.255.255')
		);
	
		$num_ip = ip2float($ip);
		foreach ($reserved_ips as $r) {
			$min = ip2float($r[0]); 
			$max = ip2float($r[1]);
			if (($num_ip >= $min) && ($num_ip <= $max)) return false;
		}
		return true;
	} else {
		return false;
	}
}

function getip() {
	if (validip($_SERVER["HTTP_CLIENT_IP"])) {
		return $_SERVER["HTTP_CLIENT_IP"];
	}
	foreach (explode(",",$_SERVER["HTTP_X_FORWARDED_FOR"]) as $ip) {
		if (validip(trim($ip))) {
			return $ip;
		}
	}
	if (validip($_SERVER["HTTP_X_FORWARDED"])) {
		return $_SERVER["HTTP_X_FORWARDED"];
	} elseif (validip($_SERVER["HTTP_FORWARDED_FOR"])) {
		return $_SERVER["HTTP_FORWARDED_FOR"];
	} elseif (validip($_SERVER["HTTP_FORWARDED"])) {
		return $_SERVER["HTTP_FORWARDED"];
	} elseif (validip($_SERVER["HTTP_X_FORWARDED"])) {
		return $_SERVER["HTTP_X_FORWARDED"];
	} else {
		return $_SERVER["REMOTE_ADDR"];
	}
}

?>
