<?php

// run_from_shell.php 
// Izvršavanje Zamger modula iz root shella bez korisničke sesije
// Izlaz je HTML (predviđeno da se redirektuje negdje)


if (php_sapi_name() != "cli") exit(0);

require_once("lib/config.php");
require_once("lib/dblayer.php");
require_once("lib/zamger.php");
require_once("lib/session.php");
require_once("lib/utility.php");
require_once("lib/zamgerui.php"); // niceerror, user_box itd.

db_connect($conf_dbhost,$conf_dbuser,$conf_dbpass,$conf_dbdb);

if ($argc < 2) {
	print "GREŠKA: Obavezan parametar naziv zamger modula.\n";
	exit(1);
}

$sta = $argv[1];
include("registry.php");
$staf = str_replace("/","_",$sta);
$found = 0;

// Prebacujemo sve ostale varijable u request
for ($i=2; $i<$argc; $i++) {
	list($key, $value) = explode("=", $argv[$i]);
	$_REQUEST[$key] = $value;
}

// Sve permisije
$user_student = $user_nastavnik = $user_studentska = $user_siteadmin = $user_prijemni = true;


	// Pretraga
	foreach ($registry as $r) {
		if (count($r) == 0) continue;
		if ($r[0] == $sta) { //$r[5] == debug
			$naslov=$r[1];
			$template=$r[4];
			$found=1;
			$greska = "";
			break;
		}
	}

if ($found == 0) {
	print "GREŠKA: Nepoznat modul $sta.\n";
	exit(2);
}

if ($naslov=="") $naslov = "ETF Bolognaware"; // default naslov

// $template=2 - bez ikakvog HTML koda
if ($found==1 && $template==2 && $greska=="") {
	include ("$sta.php");
	eval("$staf();");
	db_disconnect();
	exit(0);
}



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title><?=$naslov?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="static/css/zamger.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="static/css/print.css" type="text/css" media="print">
</head>
<?

	// Sada bi trebao ići Zamger template ali mrsko mi je da dupliciram kod
	print "<body bgcolor=\"#FFFFFF\">\n";
	include ("$sta.php");
	eval("$staf();");
	print "</body></html>\n";
	db_disconnect();


?>
