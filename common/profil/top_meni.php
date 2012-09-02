<?
$boja_licni = $boja_opcije = $boja_izbori = $boja_ljudski = "#BBBBBB";
if ($akcija=="opcije") $boja_opcije="#DDDDDD";
else if ($akcija=="izbori") $boja_izbori="#DDDDDD";
else if ($akcija=="ljudskiresursi") $boja_ljudski="#DDDDDD";
else $boja_licni = "#DDDDDD";


if (intval($_REQUEST['osoba']) > 0) {
	$q10 = myquery("select ime, prezime from osoba where id=$osoba");
	?>
	<p>&nbsp;</p>
	<h2>Promjena podataka korisnika <?=mysql_result($q10,0,0)?> <?=mysql_result($q10,0,1)?></h2>
	<p><a href="?sta=studentska/osobe&amp;akcija=edit&amp;osoba=<?=$osoba?>">Nazad na ostale podatke o osobi</a></p>
	<?
}

// Za sada ne postoje dodatne mogućnosti ponuđene studentima

if ($user_nastavnik || $user_siteadmin || $user_studentska) {
	?>
	<br>
	
	<table border="0" cellspacing="0" cellpadding="0">
	<tr height="25">
		<td bgcolor="#FFFFFF" width="50">&nbsp;</td>
		<td bgcolor="<?=$boja_licni?>" width="150" align="center" onmouseover="this.style.backgroundColor='#FFFFFF';" onmouseout="this.style.backgroundColor='<?=$boja_licni?>';"><a href="?sta=common/profil&akcija=licni&osoba=<?=$osoba?>">Lični podaci</a></td>
		<td bgcolor="#FFFFFF" width="50">&nbsp;</td>
		<td bgcolor="<?=$boja_opcije?>" width="150" align="center" onmouseover="this.style.backgroundColor='#FFFFFF';" onmouseout="this.style.backgroundColor='<?=$boja_opcije?>';"><a href="?sta=common/profil&akcija=opcije&osoba=<?=$osoba?>">Zamger opcije</a></td>
		<td bgcolor="#FFFFFF" width="50">&nbsp;</td>
		<td bgcolor="<?=$boja_izbori?>" width="150" align="center" onmouseover="this.style.backgroundColor='#FFFFFF';" onmouseout="this.style.backgroundColor='<?=$boja_izbori?>';"><a href="?sta=common/profil&akcija=izbori&osoba=<?=$osoba?>">Izbori i nastavni ansambl</a></td>
		<td bgcolor="#FFFFFF" width="50">&nbsp;</td>
		<td bgcolor="<?=$boja_ljudski?>" width="150" align="center" onmouseover="this.style.backgroundColor='#FFFFFF';" onmouseout="this.style.backgroundColor='<?=$boja_cv?>';"><a href="?sta=common/profil&akcija=ljudskiresursi&osoba=<?=$osoba?>">Curriculum Vitae</a></td>
		<td bgcolor="#FFFFFF" width="50">&nbsp;</td>
		<td bgcolor="<?=$boja_ljudski?>" width="150" align="center" onmouseover="this.style.backgroundColor='#FFFFFF';" onmouseout="this.style.backgroundColor='<?=$boja_cv?>';"><a href="?sta=common/profil&akcija=norma_plata&osoba=<?=$osoba?>">Norma plate</a></td>
		<td bgcolor="#FFFFFF" width="50">&nbsp;</td>
		<td bgcolor="<?=$boja_ljudski?>" width="150" align="center" onmouseover="this.style.backgroundColor='#FFFFFF';" onmouseout="this.style.backgroundColor='<?=$boja_cv?>';"><a href="?sta=common/profil&akcija=plata&osoba=<?=$osoba?>">Plata</a></td>
		<td bgcolor="#FFFFFF" width="50">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="9" height="1" bgcolor="#000000" bgimage="images/fnord.gif">
	</tr>
	</table>
	<?
}

?>