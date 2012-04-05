<?php
	if (ini_get("short_open_tag") != 1) {
		?>
		<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>
		<body>
		<p><font color='red'><b>GREŠKA: Potrebno aktivirati opciju short_open_tag</b></font></p>
		<p>Molimo vas da još jednom pročitate uputstva za instalaciju.</p>
		</body></html>
		<?php
		exit;
	}
?>
<?


// INDEX - master skripta za ZAMGER

// v3.9.1.0 (2008/02/09) + Pocetak rada
// v3.9.1.1 (2008/03/08) + Ukinute uloge
// v3.9.1.2 (2008/03/21) + Popravljen pristup public dijelovima bez prijave i logging
// v3.9.1.3 (2008/04/17) + Popravljen XSS sa varijablom sta
// v3.9.1.4 (2008/05/07) + Dodan logging za razne greske kod anonimnog pristupa, popravljen forwarding kada čovjeku istekne sesija
// v3.9.1.5 (2008/08/27) + Koristimo horizontalni_meni() za studentska/*; koristimo tabelu privilegija umjesto auth
// v3.9.1.6 (2008/10/02) + Popravljen logging
// v4.0.0.0 (2009/02/19) + Release
// v4.0.0.1 (2009/04/01) + Dodan link na RSS u header
// v4.0.9.1.(2009/06/16) + Dodan link na dokumentaciju na sve stranice

$uspjeh=0;


function greska_u_modulima() {
	global $uspjeh, $sta;
	if ($uspjeh==0) {
		?>
		<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>
		<body>
		<p><font color='red'><b>GREŠKA: U toku su radovi na Zamgeru</b></font></p>
		<p>Molimo Vas da pokušate ponovo za par minuta koristeći dugme <a href="javascript:location.reload(true)">Refresh</a>.</p>
		</body></html>
		<?
	}
	if ($uspjeh==1) {
                if (function_exists('error_get_last')) {
			$err = error_get_last();
			$file = $err['file'];
			$line = $err['line'];
			$msg = $err['message'];
			$file = substr($file, strlen($file)-20);

			zamgerlog("sintaksna greska u $sta, $line: '$msg'",2);
		} else {
			$file = $sta;
			zamgerlog("sintaksna greska u $sta",2);
			$msg = "";
		}

	
		niceerror("U toku su radovi na modulu $sta");
		print "<p>Molimo Vas da pokušate ponovo za par minuta koristeći dugme <a href=\"javascript:location.reload(true)\">Refresh</a>.</p>";
	}
}

register_shutdown_function("greska_u_modulima");


require("lib/libvedran.php");
require("lib/zamger.php");
require("lib/config.php");

dbconnect2($conf_dbhost,$conf_dbuser,$conf_dbpass,$conf_dbdb);


// Login forma i provjera sesije

$greska="";
$sta = my_escape($_REQUEST['sta']);

// Ovaj kod smo ukinuli da bi se moglo sa login stranice redirektovati tamo gdje je korisnik već bio
//if ($_REQUEST['greska']==1) {
	// Da li ce se ovo ikada desiti?
//	$greska="Vaša sesija je istekla. Molimo prijavite se ponovo.";
//}

if (isset($_POST['loginforma']) && $_POST['loginforma'] == "1") {
	$login = my_escape($_POST['login']);
	$pass = $_POST['pass'];
	
	if (!preg_match("/[\w\d]/",$login)) {
		$greska="Nepoznat korisnik";
	} else {
		$status = login($pass);
		if ($status == 1) { 
			$greska="Nepoznat korisnik";
		} else if ($status == 2) {
			$greska="Pogrešna šifra";
		} 
	}
	if ($greska=="") zamgerlog("login",1); // nivo 1 = posjeta stranici

} else {
	check_cookie();
//	$userid=0;
	if ($userid==0 && $sta!="" && $sta!="public/intro") {
		$greska = "Vaša sesija je istekla. Molimo prijavite se ponovo.";
//		$sta = ""; // -- Ne brisati sta! treba za provjeru public pristupa
	}
}

// nakon dijela iznad, $userid drzi numericki ID prijavljenog korisnika


// SU = switch user

if ($userid>0) {
	$su = $unsu = 0;
	if (isset($_REQUEST['su'])) $su = intval($_REQUEST['su']);
	if ($su==0) $su = intval($_SESSION['su']);
	if (isset($_REQUEST['unsu'])) $unsu = intval($_REQUEST['unsu']);
	if ($unsu==1 && $su!=0) $su=0;
	if ($su>0) {
		// Provjeravamo da li je korisnik admin
		$q5 = myquery("select count(*) from privilegije where osoba=$userid and privilegija='siteadmin'");
		if (mysql_result($q5,0,0)>0) {
			$userid=$su;
			$_SESSION['su']=$su;
		} 
	} else {
		$_SESSION['su']="";
	}
}




// Određivanje privilegija korisnika

$user_student=$user_nastavnik=$user_studentska=$user_siteadmin=false;
if ($userid>0) {
	$q10 = myquery("select privilegija from privilegije where osoba=$userid");
	while ($r10=mysql_fetch_row($q10)) {
		if ($r10[0]=="student") $user_student=true; 
		if ($r10[0]=="nastavnik") $user_nastavnik=true;
		if ($r10[0]=="studentska") $user_studentska=true;
		if ($r10[0]=="siteadmin") $user_siteadmin=true;
		//if ($r10[0]=="prijemni")  -- ovi nemaju pristup zamgeru
		// ovdje dodati ostale vrste korisnika koje imaju pristup
	}


	// Korisnik nije ništa!?
	if (!$user_student && !$user_nastavnik && !$user_studentska && !$user_siteadmin) {
		$greska = "Vaše korisničko ime je ispravno, ali nemate nikakve privilegije na sistemu! Kontaktirajte administratora.";
		$sta = "";
	}
}




// Pronalazenje trazenog modula u registryju

include("registry.php");
$staf = str_replace("/","_",$sta);
$found=0;
$naslov="";
if ($sta!="") { // Ne kontrolisemo gresku, zbog public pristupa
	// Logout
	if ($sta == "logout") {
		logout();
		zamgerlog("logout",1);
		$userid=0;
		$sta="public/intro";
		$staf="public_intro";
		$found=1;
	}

	// Pretraga
	foreach ($registry as $r) {

		if ($r[0] == $sta) { //$r[5] == debug
			if (strstr($r[3],"P") || (strstr($r[3],"S") && $user_student) || (strstr($r[3],"N") && $user_nastavnik) || (strstr($r[3],"B") && $user_studentska) || (strstr($r[3],"A") && $user_siteadmin)) {
				$naslov=$r[1];
				$template=$r[4];
				$found=1;
				$greska = "";
			} else if ($greska=="") {
				$greska = "Pristup nije dozvoljen";
				$permstr=""; // opis korisnika, za lakši debugging
				if ($user_student) $permstr.="S";
				if ($user_nastavnik) $permstr.="N";
				if ($user_studentska) $permstr.="B";
				if ($user_siteadmin) $permstr.="A";
				if ($userid>0) zamgerlog("Korisnik $userid (tip $permstr) pokusao pristupiti $sta sto zahtijeva $r[3]",3); // nivo 3 = greska
				$sta = ""; // prikaži default modul
//print "Korisnik $userid (tip $permstr) pokusao pristupiti $sta sto zahtijeva $r[3]";
			} else {
				$sta=""; // kako se ne bi prikazivale ostale greske, navigacija itd.
			}
			break;
		}
	}
}

if ($naslov=="") $naslov = "ETF Bolognaware"; // default naslov

// template==2 - ne prikazujemo ni header (npr. PDF ispis)
if ($found==1 && $template==2 && $greska=="") {
	if ($userid>0) zamgerlog(urldecode(genuri()),1); // nivo 1 = posjet stranici
	$uspjeh=1;
	include ("$sta.php");
	$uspjeh=2;
	eval("$staf();");
	dbdisconnect();
	exit;
}


?>
<html>
<head>
	<title><?=$naslov?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href="css/zamger.css" rel="stylesheet" type="text/css" />
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://zamger.etf.unsa.ba/rss.php" />
</head>
<?


// Prikaz modula bez template-a

if ($found==1 && $template==0 && $greska=="") {
	// Greske uvijek prikazujemo u template-u
	print "<body bgcolor=\"#FFFFFF\">\n";
	if (strstr($sta, "izvjestaj/")) {
		
		$k="";
		foreach ($_REQUEST as $kljuc => $vrijednost) {
			if ($kljuc != "sta")
				$k .= "$kljuc=$vrijednost&";
		}
		
		if ($userid>0) {
			?>
			<a href="?sta=izvjestaj/pdf_converter&koji_izvjestaj=<?=$sta?>&<?=$k?>" target="_new"><img src="images/32x32/pdf.png" align=right width="32" height="32" border="0"></a>
			<a href="?sta=izvjestaj/csv_converter&koji_izvjestaj=<?=$sta?>&<?=$k?>" target="_new"><img src="images/32x32/excel.png" align=right width="32" height="32" border="0"></a>
			
			<?
		}
	}
	if ($userid>0) zamgerlog(urldecode(genuri()),1); // nivo 1 = posjet stranici
	$uspjeh=1;
	include ("$sta.php");
	$uspjeh=2;
	eval("$staf();");
	print "</body></html>\n";
	dbdisconnect();
	exit;
}


// Savjet dana
if (isset($_POST['loginforma']) && $_POST['loginforma'] == "1" && $userid>0) {
	// Savjet dana
	$nasao=0;
	foreach ($registry as $r) {
		if ($r[0]=="common/savjet_dana") { $nasao=1; break; }
	}
	if ($nasao==1) {
		$q2 = myquery("select vrijednost from preference where korisnik=$userid and preferenca='savjet_dana'");
		// Ako nema rezultata, pretpostavljamo 1
		if (mysql_num_rows($q2)==0 || mysql_result($q2,0,0) != 0) {
			// Provjeravamo ima li savjeta za ovu vrstu korisnika?
			$upit="";
			if ($user_nastavnik) $upit .= "vrsta_korisnika='nastavnik' or ";
			if ($user_student) $upit .= "vrsta_korisnika='student' or ";
			if ($user_siteadmin) $upit .= "vrsta_korisnika='siteadmin' or ";
			if ($user_studentska) $upit .= "vrsta_korisnika='studentska' or ";

			$q3 = myquery("select count(*) from savjet_dana where $upit 0"); // 0 zbog zadnjeg or
			if (mysql_result($q3,0,0)>0) {
				?>
				<script language="JavaScript">
				function savjet_dana() {
					var url='index.php?sta=common/savjet_dana';
					window.open(url,'savjet_dana','width=600,height=600,scrollbars=yes');
				}
				</script>
				<?
				$onload_funkcija = " onload=\"savjet_dana()\"";
			}
		}
	}
}


// Slijedi template

?>
<body topmargin="0" leftmargin="0" bottommargin="0" rightmargin="0" bgcolor="#FFFFFF"<?=$onload_funkcija?>>

<script type="text/javascript" src="js/stablo.js"></script> <!-- Cesto koristena skripta -->

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr bgcolor="#BBBBFF">
		<!--td><table width="100%" border="0" cellspacing="0" cellpadding="0"><tr-->
			<td width="50%">&nbsp;&nbsp;&nbsp;&nbsp;
			<a href="index.php"><img src="images/etf-50x50.png" width="50" height="50" border="0"></a>
			</td><td width="50%" align="right">
			<font color="#FFFFFF" size="5">
			<b><a href="index.php"><font color="#FFFFFF"><?=$conf_appname?> <?=$conf_appversion?></font></a>&nbsp;</b></font><br/>
			<font color="#FFFFFF" size="1">
			<a href="doc/zamger-uputstva-42-nastavnik.pdf" target="_new">
			<img src="images/16x16/dokumentacija.png" width="16" height="16" border="0" align="center">&nbsp;
			Uputstva</a>&nbsp;&nbsp;&nbsp;
			<a href="http://f.etf.unsa.ba/redmine/projects/zamger/issues/new" target="_new">
			<img src="images/16x16/zad_bug.png" width="16" height="16" border="0" align="center">&nbsp;
			Prijavite bug</a>&nbsp;&nbsp;&nbsp;</font>
			</td>
		<!--/tr></table></td-->
	</tr>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="4">
	<tr><td valign="top" align="left">

<?

// Provjera maila
if ($userid>0) {
	$q20 = myquery("select UNIX_TIMESTAMP(vrijeme) from log where userid=$userid order by id desc limit 2");
	if (mysql_num_rows($q20)>1)
		$vrijeme=intval(mysql_result($q20,1,0));
	else 
		$vrijeme=0;
	$q30 = myquery("select count(*) from poruka where tip=2 and opseg=7 and primalac=$userid and UNIX_TIMESTAMP(vrijeme)>$vrijeme");
	if (mysql_result($q30,0,0)>0) {
		?>
		<img src="images/newmail.gif" id="newmail" width="450" height="188" style="position:absolute;visibility:hidden" onload="newmail_show();">
		<script language="javascript">
		var mywidth,myheight;
		if (window.innerWidth && window.innerHeight) {
			mywidth=window.innerWidth;
			myheight=window.innerHeight;
		} else if (document.body.clientWidth && document.body.clientHeight) {
			mywidth=document.body.clientWidth;
			myheight=document.body.clientHeight;
		}

		var flashes=0;
		function newmail_show() {
			var newmail = document.getElementById('newmail');
			newmail.style.visibility='visible';
			newmail.style.top=-188;
			newmail.style.left=mywidth/2-225;
			setTimeout("newmail_scroll()",10);
		}
		function newmail_scroll() {
			var newmail = document.getElementById('newmail');
			if (parseInt(newmail.style.top) < myheight/2-94) {
				newmail.style.top=parseInt(newmail.style.top)+1;
				setTimeout("newmail_scroll()",10);
			} else {
				setTimeout("newmail_flash()",500);
			}
		}
		function newmail_flash() {
			var newmail = document.getElementById('newmail');
			if (newmail.style.visibility=='visible')
				newmail.style.visibility='hidden';
			else
				newmail.style.visibility='visible';
			flashes++;
			if (flashes<=10) setTimeout("newmail_flash()",500);
		}
		</script>
		<?
	}
}

if($userid>0)
{
	?>

	<div id="notifikacija" style="position:fixed;visibility:hidden;right:40px;bottom:10px;"> 
	<img src="images/notifikacija.png" onClick="Klik1()" onload="prikaziNotifikaciju();"/> </div>
	<div id="notifikacija2" style="position:fixed;visibility:hidden;right:40px;bottom:10px;"> 
	<img src="images/notifikacija2.png" onClick="Klik4()"/> </div>
	<div id="poruka" style="position:fixed;visibility:hidden;right:120px;bottom:10px;">
	<img src="images/poruka.png" onClick="Klik2()" onload="prikaziPoruku();"/> </div>
	<div id="poruka2" style="position:fixed;visibility:hidden;right:120px;bottom:10px;">
	<img src="images/poruka2.png" onClick="Klik3()"/> </div>

	<div id="tabela1" style="font-family:Arial;font-size:10px;position:absolute;visibility:hidden;right:20px;bottom:50px;"></div>
	<div id="tabela2" style="font-family:Arial;font-size:10px;position:absolute;visibility:hidden;right:100px;bottom:50px;"></div>

	<script language="javascript" type="text/javascript">
	var int1=self.setInterval("prikaziNotifikaciju()",20000);
    var int2=self.setInterval("prikaziPoruku()",20000);
	
    var tabela2 = document.getElementById('tabela2');
	var poruka = document.getElementById('poruka');
	var tabela1 = document.getElementById('tabela2');
	var notifikacija = document.getElementById('notifikacija');
	var poruka2 = document.getElementById('poruka2');
	
	function prikaziPoruku()
	{
	
		if (window.XMLHttpRequest){
  			xmlhttp=new XMLHttpRequest();
  		}
		else{
  			xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  		}
		xmlhttp.onreadystatechange=function()
  		{
  			if (xmlhttp.readyState==4 && xmlhttp.status==200){
  			    tabela2.innerHTML=xmlhttp.responseText;
				poruka2.style.visibility='hidden';
				var redovi = document.getElementById('tabela2').getElementsByTagName('tr');
    			var redova = redovi.length;
				if (redova>0){
					poruka.style.visibility='visible';

				}
    		}
  		}
		xmlhttp.open("GET","?sta=common/notifikacija&tip=2",true);
		xmlhttp.send();
	}

	function prikaziNotifikaciju()
	{
		if (window.XMLHttpRequest){
			xmlhttp=new XMLHttpRequest();
  		}
		else{
  			xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  		}
		xmlhttp.onreadystatechange=function()
  		{
  			if (xmlhttp.readyState==4 && xmlhttp.status==200){
    			tabela2.innerHTML=xmlhttp.responseText;
				var redovi = document.getElementById('tabela1').getElementsByTagName('tr');
    			var redova = redovi.length;
				notifikacija2.style.visibility='hidden';
				if (redova>0){
					notifikacija.style.visibility='visible';
				
				}
    		}
  		}
		xmlhttp.open("GET","?sta=common/notifikacija&tip=1",true);
		xmlhttp.send();
	}

	function Klik1()
	{
		if (notifikacija.style.visibility=='visible'){
			
			if (window.XMLHttpRequest){
			xmlhttp=new XMLHttpRequest();
  		}
		else{
  			xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  		}
	
		xmlhttp.open("GET","?sta=common/notifikacija&tip=3",true);
		xmlhttp.send();
			
			tabela2.style.visibility='hidden';
			tabela1.style.visibility='visible';
			notifikacija.style.visibility='hidden';
			notifikacija2.style.visibility='visible';
		}
	}
		
	function Klik2()
	{
		
		if (poruka.style.visibility=='visible'){
				if (window.XMLHttpRequest){
			xmlhttp=new XMLHttpRequest();
  		}
		else{
  			xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  		}
	
		xmlhttp.open("GET","?sta=common/notifikacija&tip=4",true);
		xmlhttp.send();
			tabela1.style.visibility='hidden';
			tabela2.style.visibility='visible';
			poruka.style.visibility='hidden';
			poruka2.style.visibility='visible';
		}
	}

		
	function Klik3()
	{
		if (poruka2.style.visibility=='visible'){
			if (tabela2.style.visibility=='visible')
			tabela2.style.visibility='hidden';
			else{
				tabela1.style.visibility='hidden';
				tabela2.style.visibility='visible';
			}
		}
	}

	function Klik4()
	{
		if (notifikacija2.style.visibility=='visible'){
			if (tabela1.style.visibility=='visible')
				tabela1.style.visibility='hidden';
			else{
				tabela2.style.visibility='hidden';
				tabela1.style.visibility='visible';
			}
		}
	}
	</script>

	<?
}
// Standardne greske
if ($greska != "") {
	niceerror($greska);
	if ($sta=="") 
		zamgerlog("index.php greska: $greska $login ".my_escape($_REQUEST['sta']),3);
	else
		zamgerlog("index.php greska: $greska $login $sta",3);
}

// Poruka greške za modul
if ($found != 1 && $sta != "") {
	niceerror("Modul $sta još uvijek nije napravljen.");
	zamgerlog("pristup nepostojecom modulu $sta",3);
}

// Default moduli za uloge, u slučaju da modul nije pronađen
if ($found != 1) {
	if ($user_siteadmin) {
		$sta = "admin/intro";
	} else if ($user_studentska) {
		$sta = "studentska/intro";
	} else if ($user_nastavnik) {
		$sta = "saradnik/intro";
	} else if ($user_student) {
		$sta = "student/intro";
	} else {
		$sta = "public/intro";
	}
	$staf = str_replace("/","_",$sta);
}


// Promjena uloge korisnika
if ($userid>0) {
	if ($user_student && !strstr($sta,"student/"))
		print "<a href=\"?sta=student/intro\">Studentska stranica</a><br/>\n";
	if ($user_nastavnik && !strstr($sta,"saradnik/") && !strstr($sta,"nastavnik/"))
		print "<a href=\"?sta=saradnik/intro\">Spisak predmeta i grupa</a><br/>\n";
	if ($user_studentska && !strstr($sta,"studentska/"))
		print "<a href=\"?sta=studentska/intro\">Studentska služba</a><br/>\n";
	if ($user_siteadmin && !strstr($sta,"admin/"))
		print "<a href=\"?sta=admin/intro\">Site admin</a><br/>\n";
}


// Polje sa imenom i linkovima na inbox, profil i odjavu
if ($userid>0) {
	user_box();
	zamgerlog(urldecode(genuri()),1); // nivo 1 = posjet stranici
}

// Prikaz modula uglavljenog u template
$uspjeh=1;
include ("$sta.php");
$uspjeh=2;


// Prikaz menija specificnih za odredjene grupe modula
if (strstr($sta,"nastavnik/") || strstr($sta,"admin/"))
	malimeni("$staf();");
else if (strstr($sta,"studentska/"))
	horizontalni_meni("$staf();");
else if (strstr($sta,"student/"))
	studentski_meni("$staf();");
else
	eval("$staf();");


?>

	</td></tr>
</table>

<p>&nbsp;</p>
<p>&nbsp;</p>
<p align="center">Copyright (c) 2006-2012 Vedran Ljubović i drugi<br/>Elektrotehnički fakultet Sarajevo</p>

</body>
</html>
<?
	dbdisconnect();
?>
