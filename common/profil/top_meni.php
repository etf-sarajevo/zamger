<?
$boja_licni = $boja_opcije = $boja_izbori = "#BBBBBB";
if ($akcija=="opcije") $boja_opcije="#DDDDDD";
else if ($akcija=="izbori") $boja_izbori="#DDDDDD";
else $boja_licni = "#DDDDDD";


// Za sada ne postoje dodatne mogućnosti ponuđene studentima

if ($user_nastavnik) {
	?>
	<br>
	
	<table border="0" cellspacing="0" cellpadding="0">
	<tr height="25">
		<td bgcolor="#FFFFFF" width="50">&nbsp;</td>
		<td bgcolor="<?=$boja_licni?>" width="200" align="center" onmouseover="this.style.backgroundColor='#FFFFFF';" onmouseout="this.style.backgroundColor='<?=$boja_licni?>';"><a href="?sta=common/profil&akcija=licni">Lični podaci</a></td>
		<td bgcolor="#FFFFFF" width="50">&nbsp;</td>
		<td bgcolor="<?=$boja_opcije?>" width="200" align="center" onmouseover="this.style.backgroundColor='#FFFFFF';" onmouseout="this.style.backgroundColor='<?=$boja_opcije?>';"><a href="?sta=common/profil&akcija=opcije">Zamger opcije</a></td>
		<td bgcolor="#FFFFFF" width="50">&nbsp;</td>
		<td bgcolor="<?=$boja_izbori?>" width="200" align="center" onmouseover="this.style.backgroundColor='#FFFFFF';" onmouseout="this.style.backgroundColor='<?=$boja_izbori?>';"><a href="?sta=common/profil&akcija=izbori">Izbori i nastavni ansambl</a></td>
		<td bgcolor="#FFFFFF" width="50">&nbsp;</td>
		<td bgcolor="<?=$boja_izbori?>" width="200" align="center" onmouseover="this.style.backgroundColor='#FFFFFF';" onmouseout="this.style.backgroundColor='<?=$boja_izbori?>';"><a href="?sta=common/profil&akcija=ljudskiresursi">UNSA HR</a></td>
		<td bgcolor="#FFFFFF" width="100">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="9" height="1" bgcolor="#000000" bgimage="images/fnord.gif">
	</tr>
	</table>
	<?
}
?>