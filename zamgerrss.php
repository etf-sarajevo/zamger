<?
//zamgerrss.php sluzi za prikaz rss feed-a direktno na zamger aplikaciji
//ukoliko neko zeli da rss feed cita u nekoj drugoj aplikaciji, dovoljno ce biti da klikne na ikonu 
//za rss feed i da kopira url koji je dodijeljen toj stranici

require("lib/libvedran.php");
require("lib/zamger.php");
require("lib/config.php");
require("rssreader.php") ;
require("rsscreator.php") ;

dbconnect2($conf_dbhost,$conf_dbuser,$conf_dbpass,$conf_dbdb);


$id = my_escape($_REQUEST['id']);

$q1 = myquery("select auth from rss where id='$id'");
if (mysql_num_rows($q1)<1) {
	print "Greska! Nepoznat RSS ID $id";
	return 0;
}
$userid = mysql_result($q1,0,0);


?>
<html>
<head>
	<title><?=$naslov?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="css/zamger.css" rel="stylesheet" type="text/css" />
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://zamger.etf.unsa.ba/zamgerrss.php" />
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
			<a href="doc/zamger-uputstva-40-nastavnik.pdf" target="_new">
			<img src="images/16x16/dokumentacija.png" width="16" height="16" border="0" align="center">&nbsp;
			Uputstva</a>&nbsp;&nbsp;&nbsp;
			<a href="http://195.130.59.135/bugzilla" target="_new">
			<img src="images/16x16/zad_bug.png" width="16" height="16" border="0" align="center">&nbsp;
			Prijavite bug</a>&nbsp;&nbsp;&nbsp;</font>
			</td>
		<!--/tr></table></td-->
	</tr>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="4">
	<tr>
    <tr>
        <td  style="border-bottom:solid black 1px">
			<?if ($sta != "student/intro") { ?>
				<a href="http://zamger.etf.unsa.ba">&lt;-- Nazad na početnu</a>
			<? } ?>
            <h3>RSS Feed - automatsko obavještenje o novostima!</h3></a>
        </td>
    </tr>
    <td valign="top" align="left" style="border-bottom:solid black 1px">
<?

// Standardne greske
if ($greska != "") {
	niceerror($greska);
	if ($sta=="") 
		zamgerlog("index.php greska: $greska $login ".my_escape($_REQUEST['sta']),3);
	else
		zamgerlog("index.php greska: $greska $login $sta",3);
}

//Pokretanje rss readera


    $rssContent=createRssForUser($userid);
    $rss = new rssFeed();
    $rss->xml=$rssContent;
    $rss->parse();
    $rss->showStories();

?>

	</td></tr>
    <tr>
        <td>
 
    <a href="http://zamger.etf.unsa.ba/rss.php?id=<?=$id?>">
    <img src="images/32x32/rss.png" width="32" height="32" border="0" align="center"> <big>RSS feed</big></a>

        </td>

    </tr>
</table>

<p>&nbsp;</p>
<p>&nbsp;</p>
<p align="center">Copyright (c) 2006-2010 Vedran Ljubović i drugi<br/>Elektrotehnički fakultet Sarajevo</p>

</body>
</html>
<?
	dbdisconnect();
?>
