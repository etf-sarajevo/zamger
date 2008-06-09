<html>
<head>
<title>Prisustvo na vježbama</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="css/style.css" rel="stylesheet" type="text/css" />
</head>
<body>

<p>Elektrotehnički fakultet Sarajevo<br>
Odsjek za računarstvo i informatiku<br>
Predmet: Osnove računarstva i Tehnike programiranja</p>

<h1>Pregled prisustva na vježbama</h1>

<!--p>Legenda:<br/>
<table border="1" cellspacing="0" cellpadding="2">
<tr><td bgcolor="#CCFFCC" width="50">U</td><td>Usmeni</td></tr>
<tr><td bgcolor="#FFFFCC">P1</td><td>Popravni - 1. parcijala</td></tr>
<tr><td bgcolor="#FFFFCC">P2</td><td>Popravni - 2. parcijala</td></tr>
<tr><td bgcolor="#FFEECC">PO</td><td>Popravni - integralno</td></tr>
<tr><td bgcolor="#FFCCFF">??</td><td>Nije skupio/la 40 bodova - nedefinisano (bicete obavijesteni)</td></tr>
<tr><td bgcolor="#FFCCCC">/</td><td>Pao/Pala</td></tr>

</table-->

<?
	$imena=array();
	$topscore=array();

require("libvedran.php");
dbconnect();

$db = my_escape($_GET['predmet']);
$imenaopt = intval($_GET['imena']);
mysql_select_db("vedran_".str_replace("'","",$db));


$result = mysql_query("select id,naziv from grupe order by id");

//print "AAAAAAAAA ".vsortcmp("Čamdžić","Cokić");

while ($jedanred = mysql_fetch_row($result)) {
	$grupa_id = $jedanred[0];
	$grupa_naziv = $jedanred[1];
	print "<center><h2>Grupa: $grupa_naziv</h2></center>\n";

	// ZAGLAVLJE PRISUSTVO
	$r2 = mysql_query("select id,datum,vrijeme from casovi where grupa=$grupa_id order by datum");
	$casova = 0;
	$casovi_zaglavlje = $ocjene_zaglavlje = $parc_zaglavlje = "";
	$cas_id_array = $vj_id_array = $par_id_array = array();
	while ($dr = mysql_fetch_row($r2)) {
		$cas_id = $dr[0];
		list ($cas_godina,$cas_mjesec,$cas_dan) = explode("-",$dr[1]);
		list ($cas_sat,$cas_minuta,$cas_sekunda) = explode(":",$dr[2]);
		$casovi_zaglavlje .= "<td>$cas_dan.$cas_mjesec<br>$cas_sat:$cas_minuta</td>\n";
		array_push($cas_id_array,$cas_id);
		$casova++;
	}
	if ($casovi_zaglavlje == "") $casovi_zaglavlje = "<td>&nbsp;</td>";
	
	// ZAGLAVLJE ZADACE
	//$r2a = mysql_query("select ocjene.vjezba from ocjene,studenti where ocjene.student=studenti.id and studenti.grupa=$grupa_id group by ocjene.vjezba order by ocjene.vjezba") or die(mysql_error());
	$q2a = myquery("select id from zadace_objavljene order by id");
	$brzadaca = mysql_num_rows($q2a);
	if ($brzadaca == 0) { $brzadaca=1; $ocjene_zaglavlje = "<td>&nbsp;</td>"; }
	else {
		for ($i=0;$i<$brzadaca;$i++) {
			$vjezba = mysql_result($q2a,$i,0);
			$ocjene_zaglavlje .= "<td>$vjezba.</td>\n";
			array_push($vj_id_array,$vjezba);
		}
	}

	// ZAGLAVLJE PARCIJALE
	$r2b = mysql_query("select parcijale.id from parcijale,studenti where parcijale.student=studenti.id and studenti.grupa=$grupa_id group by parcijale.id order by parcijale.id");
	$brparc = mysql_num_rows($r2b);
	if ($brparc == 0) {
		$parc_zaglavlje = "<td>&nbsp;</td>";
		$brparc=1;
	} else {
		while ($drb = mysql_fetch_row($r2b)) {
			$parc = $drb[0];
			$parc_zaglavlje .= "<td>$parc.</td>\n";
			array_push($par_id_array,$parc);
		}
	}
	if ($casova==0) $casova=1;

	?>
<table cellspacing="0" cellpadding="2" border="1">
<tr>
	<? if ($imenaopt) { ?><td rowspan="2" align="center" valign="center">Ime i prezime</td><? } ?>
	<td rowspan="2" align="center" valign="center">Broj indexa</td>
	<td align="center" colspan="<?=($casova+1)?>">Prisustvo</td>
	<td align="center" colspan="<?=$brzadaca?>">Ocjene iz zadaća</td>
	<td align="center" colspan="<?=$brparc?>">Parcijalni ispiti</td>
	<td align="center" valign="center" rowspan="2" colspan="2">&nbsp;&nbsp;<b>UKUPNO</b>&nbsp;&nbsp;</td>
</tr>
<tr>
	<?=$casovi_zaglavlje?><td>BOD.</td>
	<?=$ocjene_zaglavlje?>
	<?=$parc_zaglavlje?>
</tr>
<?
	$r3 = mysql_query("select id,ime,prezime,brindexa from studenti where grupa=$grupa_id");
	$imeprezime = array();
	$brind = array();
	while ($tr = mysql_fetch_row($r3)) {
		$stud_id = $tr[0];
		$stud_ime = $tr[1];
		$stud_prezime = $tr[2];
		$stud_brind = $tr[3];
		$imeprezime[$stud_id] = "$stud_prezime $stud_ime";
		$brind[$stud_id] = $stud_brind;
	}
	uasort($imeprezime,"vsortcmp");
	foreach ($imeprezime as $stud_id => $stud_imepr) {
	
?>
<tr>
	<? if ($imenaopt) print "<td>$stud_imepr</td>"; ?>
	<td><?=$brind[$stud_id]?></td>
<?
		$prisustvo_ispis=$ocjene_ispis=$parc_ispis="";
		$bodova=0;
		$mogucih=0;

		// PRISUSTVO
		if (count($cas_id_array)==0) $prisustvo_ispis = "<td>&nbsp;</td>";
		$odsustvo=0;
		foreach ($cas_id_array as $cid) {
			$r4 = mysql_query("select prisutan,ocjena from prisustvo where student=$stud_id and cas=$cid");
			if (mysql_num_rows($r4)>0) {
				if (mysql_result($r4,0,0) == 1) { 
					$prisustvo_ispis .= "<td bgcolor=\"#CCFFCC\" align=\"center\">DA</td>";
				} else { 
					$prisustvo_ispis .= "<td bgcolor=\"#FFCCCC\" align=\"center\">NE</td>";
					$odsustvo++;
				}
				$ocj = mysql_result($r4,0,1);
			} else {
				$prisustvo_ispis .= "<td bgcolor=\"#FFFFCC\"> / </td>";
			}
		}
		if ($odsustvo<=3) {
			$prisustvo_ispis .= "<td>10</td>";
			$bodova+=10;
		} else {
			$prisustvo_ispis .= "<td>0</td>";
		}
		$mogucih+=10;

		// ZADACE
		if (count($vj_id_array)==0) $ocjene_ispis = "<td>&nbsp;</td>";
		foreach ($vj_id_array as $vid) {
			//$r5 = mysql_query("select ocjena from ocjene where student=$stud_id and vjezba=$vid");
			$q5 = myquery("select status,bodova,zadatak from zadace where zadaca=$vid and student=$stud_id order by zadatak,id desc");
			$ok = 1;
			$ocjena = 0;
			$zadatak = 0;
			if (mysql_num_rows($q5) == 0)
				$ok = 0;
			else while ($r5 = mysql_fetch_row($q5)) {
				if ($r5[2] == $zadatak) continue;
				$zadatak = $r5[2];
				$status = $r5[0]; 
				if ($status == 0 || $status == 1 || $status == 4) {
					$ok = 0;
					break;
				}
				$ocjena += $r5[1];
			}
			if ($ok == 0) {
				$ocjene_ispis .= "<td> / </td>";
			} else {
				$ocjene_ispis .= "<td> $ocjena </td>";
				$bodova = $bodova + $ocjena;
			}
			$mogucih+=2;
		}

		// PARCIJALE
		if (count($par_id_array)==0) $parc_ispis = "<td>&nbsp;</td>";
		$i=$pao1=$pao2=0;
		foreach ($par_id_array as $pid) {
			$i++;
			$r6 = mysql_query("select ocjena from parcijale where student=$stud_id and id=$pid");
			if (mysql_num_rows($r6)>0) {
				if (($ocjena = mysql_result($r6,0,0)) == -1) {
					$parc_ispis .= "<td> / </td>";
					if ($i==1) { $pao1 = 1; } else { $pao2 = 1; }
				} else {
					$parc_ispis .= "<td> $ocjena </td>";
					if ($ocjena<10) {
						if ($i==1) { $pao1 = 1; } else { $pao2 = 1; }
					}	
					$bodova = $bodova + $ocjena;
				}
			} else {
				$parc_ispis .= "<td> / </td>";
				if ($i==1) { $pao1 = 1; } else { $pao2 = 1; }
			}
			$mogucih+=20;
		}

		if ($mogucih>0) $procent = round(($bodova/$mogucih)*100); else $procent=0;
		
		$imena[$stud_id]=$stud_imepr;
		$topscore[$stud_id]=$bodova;
		if ($pao1 == 0 && $pao2 == 0) {
			if ($bodova>=40) {
				$thecolor="#CCFFCC";
				$theletter="U";
			} else {
				$thecolor="#FFCCFF";
				$theletter="??";
			}
		} elseif ($bodova<20) {
			$thecolor="#FFCCCC";
			$theletter="/";
		} elseif ($pao1 == 0 && $pao2 == 1) {
			$thecolor="#FFFFCC";
			$theletter="P2";
		} elseif ($pao2 == 0 && $pao1 == 1) {
			$thecolor="#FFFFCC";
			$theletter="P1";
		} else {
			$thecolor="#FFEECC";
			$theletter="P0";
		}

?>
	<?=$prisustvo_ispis?>
	<?=$ocjene_ispis?>
	<?=$parc_ispis?>
	<td align="center"><?=$bodova?> (<?=$procent?>%)</td>
	<td bgcolor="<?=$thecolor?>"><?=$theletter?></td>
<?
	}
?>
</tr>
</table>
<p>&nbsp;</p>
<?
} 

mysql_close();
?>


<!-- TOP LISTA -->

<?
asort($topscore);
$i=0;
$ispis_tl = $ispis_bl = "";
foreach ($topscore as $id => $bodova) {
	if ($i<=15) {
		$ispis_bl .= "<tr><td>".(count($topscore)-$i)."</td><td>".$imena[$id]."</td><td>".$bodova."</td></tr>";
	}
	if ($i>=count($topscore)-15) {
		$ispis_tl .= "<tr><td>".(count($topscore)-$i)."</td><td>".$imena[$id]."</td><td>".$bodova."</td></tr>";
	}
	$i++;
}

# select id,naziv from grupe
?>

<table border="0" cellspacing="0" cellpadding="0">
<tr><td>
	<table border="2" cellspacing="0" cellpadding="3">
	<tr><td colspan="2"><center><h2>Top-Lista</h2></center></td></tr>
	<tr><td>&nbsp;</td><td>IME</td><td>BODOVA</td></tr>
	<?=$ispis_tl;?>
	</table>
</td>
<!--td width="150">&nbsp;</td>
<td>
	<table border="2" cellspacing="0" cellpadding="3">
	<tr><td colspan="2"><center><h2>Bottom-Lista</h2></center></td></tr>
	<tr><td>&nbsp;</td><td>IME</td><td>BODOVA</td></tr>
	<!--?=$ispis_bl;?>
	</table>

</td></tr--></table>
<!-- KRAJ TOP LISTE -->


</body>
</html>
<?

function vsortcmp($a, $b) {
	$a=strtolower($a); $b=strtolower($b);
	$abeceda = array("a","A","b","B","c","C","č","Č","ć","Ć","d","đ","Đ","e","f","g","h","i","j","k","l","m","n","o","p", "q","r","s","š","Š","t","u","v", "w","x","y","z","ž","Ž");
	$min = (strlen($a)<strlen($b)) ? strlen($a) : strlen($b);
	for ($i=0; $i<$min; $i++) {
		$ca = substr($a,$i,1); if (ord($ca)>128) $ca = substr($a,$i,2);
		$cb = substr($b,$i,1); if (ord($cb)>128) $cb = substr($b,$i,2);
		$k=array_search($ca,$abeceda); $l=array_search($cb,$abeceda);
//		print "K: $k L: $l ZLJ: ".$ca. "       ";
		if ($k<$l) return -1; if ($k>$l) return 1;
	}
	if (strlen($a)<strlen($b)) return -1;
	return 1;
}

?>
