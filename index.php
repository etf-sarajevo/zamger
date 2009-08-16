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

if ($_POST['loginforma'] == "1") {
	$login = my_escape($_POST['login']);
	$pass = my_escape($_POST['pass']);
	
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
	$su = intval($_REQUEST['su']);
	if ($su==0) $su = intval($_SESSION['su']);
	$unsu = intval($_REQUEST['unsu']);
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
		if ($r[0] == $sta) { //$r[5] == nevidljiv
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
	include ("$sta.php");
	eval("$staf();");
	exit;
}


?>
<html>
<head>
	<title><?=$naslov?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="css/zamger.css" rel="stylesheet" type="text/css" />
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://zamger.etf.unsa.ba/rss.php" />
</head><?


// Prikaz modula bez template-a

if ($found==1 && $template==0 && $greska=="") {
	// Greske uvijek prikazujemo u template-u
	print "<body bgcolor=\"#FFFFFF\">\n";
	if ($userid>0) zamgerlog(urldecode(genuri()),1); // nivo 1 = posjet stranici
	include ("$sta.php");
	eval("$staf();");
	print "</body></html>\n";
	exit;
}


// Slijedi template

?>
<body topmargin="0" leftmargin="0" bottommargin="0" rightmargin="0" bgcolor="#FFFFFF">

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr bgcolor="#BBBBFF">
		<!--td><table width="100%" border="0" cellspacing="0" cellpadding="0"><tr-->
			<td width="50%">&nbsp;&nbsp;&nbsp;&nbsp;
			<a href="index.php"><img src="images/etf-50x50.png" width="50" height="50" border="0"></a>
			</td><td width="50%" align="right">
			<font color="#FFFFFF" size="5">
			<b><a href="index.php"><font color="#FFFFFF"><?=$conf_appname?> <?=$conf_appversion?></font></a>&nbsp;</b></font><br/>
			<font color="#FFFFFF" size="1">
			<a href="http://195.130.59.135/bugzilla" target="_new">
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
	if (mysql_num_rows($q20)>0)
		$vrijeme=intval(mysql_result($q20,1,0));
	else 
		$vrijeme=0;
	$q30 = myquery("select count(*) from poruka where tip=2 and opseg=7 and primalac=$userid and UNIX_TIMESTAMP(vrijeme)>$vrijeme");
	if (mysql_result($q30,0,0)>0) {
		?>
		<img src="images/newmail.gif" id="newmail" width="450" height="188" style="position:absolute;visibility:hidden" onload="newmail_show();">
		<script language="javascript">
		var flashes=0;
		function newmail_show() {
			var newmail = document.getElementById('newmail');
			newmail.style.visibility='visible';
			newmail.style.top=-188;
			newmail.style.left=document.width/2-225;
			setTimeout("newmail_scroll()",10);
		}
		function newmail_scroll() {
			var newmail = document.getElementById('newmail');
			if (parseInt(newmail.style.top) < document.height/2-94) {
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
include ("$sta.php");


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
<p align="center">Copyright (c) 2006-2008 Vedran Ljubović<br/>Elektrotehnički fakultet Sarajevo</p>

</body>
</html>
<?
	dbdisconnect();
?>
