<?

// STUDENT/KVIZ - spisak kvizova ponuđenih studentu



function student_kviz() {

global $userid;


// Akcije
if ($_REQUEST['akcija'] == "slanje") {
	akcijaslanje();
}


// Poslani parametri
$predmet = int_param('predmet');
$ag = int_param('ag');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Zbog automatskog reloadanja ovog prozora dok se popunjava kviz, dolazilo je do otvaranja
	// dijaloga za "resubmit" što je znalo dovesti do prekida popunjavanja kviza
	?>
	<script language="JavaScript">
	location.href='?sta=student/kviz&predmet=<?=$predmet?>&ag=<?=$ag?>';
	</script>
	<?
	return 0;
}


$q10 = db_query("select naziv from predmet where id=$predmet");
if (db_num_rows($q10)<1) {
	zamgerlog("nepoznat predmet $predmet",3); // nivo 3: greska
	zamgerlog2("nepoznat predmet", $predmet); // nivo 3: greska
	biguglyerror("Nepoznat predmet");
	return;
}

$q15 = db_query("select naziv from akademska_godina where id=$ag");
if (db_num_rows($q10)<1) {
	zamgerlog("nepoznata akademska godina $ag",3); // nivo 3: greska
	zamgerlog2("nepoznata akademska godina", $ag); // nivo 3: greska
	biguglyerror("Nepoznata akademska godina");
	return;
}

// Da li student slusa predmet?
$q17 = db_query("select sp.predmet from student_predmet as sp, ponudakursa as pk where sp.student=$userid and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
if (db_num_rows($q17)<1) {
	zamgerlog("student ne slusa predmet pp$predmet", 3);
	zamgerlog2("student ne slusa predmet", $predmet, $ag);
	biguglyerror("Niste upisani na ovaj predmet");
	return;
}
$ponudakursa = db_result($q17,0,0);


print "<h2>Kvizovi</h2>\n";

// Spisak grupa u kojima je student
$upit_labgrupa = "";
$q20 = db_query("select sl.labgrupa from labgrupa as l, student_labgrupa as sl where sl.student=$userid and sl.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag and l.virtualna=0");
while ($r20 = db_fetch_row($q20)) {
	$upit_labgrupa .= "or labgrupa=$r20[0] ";
}


// Ima li aktivnih kvizova
$q30 = db_query("select id, naziv, ip_adrese, prolaz_bodova from kviz where predmet=$predmet and akademska_godina=$ag and vrijeme_pocetak<=NOW() and vrijeme_kraj>=NOW() and aktivan=1 and (labgrupa=0 $upit_labgrupa)");
if (db_num_rows($q30)<1) {
	print "Trenutno nema aktivnih kvizova za ovaj predmet.";
	return;
}

// Spisak kvizova
?>
<script language="JavaScript">
function otvoriKviz(k) {
	if (/*@cc_on!@*/false) { // check for Internet Explorer
		window.open('index.php?sta=student/popuni_kviz&kviz='+k, 'Kviz', 'fullscreen,scrollbars'); 
	} else {
		var sir = screen.width;
		var vis = screen.height;
		mywindow = window.open('index.php?sta=student/popuni_kviz&kviz='+k, 'Kviz', 'status=0,toolbar=0,location=0,menubar=0,directories=0,resizable=0,scrollbars=1,width='+sir+',height='+vis); 
		mywindow.moveTo(0,0); 
		setTimeout('window.location.reload();', 5000);
	}
}
</script>

<div id="spisak_kvizova">
<p>Trenutno su aktivni kvizovi:</p>
<ul>
<?
while ($r30 = db_fetch_row($q30)) {
	// Da li je ip adresa u datom rasponu
	if ($r30[2] != "") {
		$moja_ip = getip();
		$ispravna = false;

		$blokovi = explode(",", $r30[2]);
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
			print "<li>$r30[1] - kviz je nedostupan sa vaše adrese ($moja_ip)</li>\n";
			continue;
		}
	}

	// Da li je student već popunjavao ovaj kviz
	$q40 = db_query("select dovrsen, bodova from kviz_student where student=$userid and kviz=$r30[0]");
	if (db_num_rows($q40)>0) {
		print "<li>$r30[1] - ";
		if (db_result($q40,0,0)==0) {
			print "nedovršen</li>\n";
		} else {
			$bodova = db_result($q40,0,1);
			print "završen, osvojili ste $bodova bodova.";
			if ($bodova >= $r30[3]) // prolaz
				print " Čestitamo!";
			print "</li>\n";
		}
		continue;
	}

	print "<li><a href=\"#\" onclick=\"otvoriKviz($r30[0]);\">$r30[1]</a></li>\n";
}
print "</ul>\n";


?>
<p>Kliknite na naziv kviza da pristupite popunjavanju kviza.</p>
<br>
<p><b><font color="red">VAŽNA NAPOMENA</font></b>: Kada započnete popunjavanje kviza ne smijete se prebaciti na drugi prozor! Svaki pokušaj da računar koristite za bilo šta osim popunjavanje kviza može izazvati prekid kviza bez mogućnosti kasnijeg ponovnog popunjavanja.</p>
<p><a href="#" onclick="window.close();">Zatvorite ovaj prozor</a></p>
</div>


<!--div id="nema_js">
Za pristup kvizovima potrebno je da aktivirate JavaScript u vašem web pregledniku.
</div-->
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
