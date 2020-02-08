<?

// ADMIN/CRON - administracija zadataka koji se izvršavaju periodično


function admin_cron() {

	?>
	<p>&nbsp;</p>
	<h3>Cron zadaci:</h3>
	<p>&nbsp;</p>
	<?
	

	if (param('akcija') == "log") {
		$cron = intval($_REQUEST['cron']);
		$id = intval($_REQUEST['id']);
		if ($id>0) $sqladd = "AND id=$id";
		$q20 = db_query("SELECT id, izlaz, return_value, UNIX_TIMESTAMP(vrijeme) FROM cron_rezultat WHERE cron=$cron $sqladd ORDER BY vrijeme DESC");
		
		?>
		<p>Vrijeme izvršenja: <b><?=date("d.m.Y. H:i:s", db_result($q20,0,3)) ?></b></p>
		<p>Povratna vrijednost: <b><?=db_result($q20,0,2)?></b></p>
		<p>Izlaz:</p>
		<pre><?=db_result($q20,0,1)?></pre>
		<hr>
		<p>Ranija izvršenja</p>
		<ul>
		<?
		while ($r20 = db_fetch_row($q20))
			print "<li><a href=\"?sta=admin/cron&akcija=log&cron=$cron&id=$r20[0]\">".date("d.m.Y H:i:s", $r20[3])."</a></li>\n";
		
		print "</ul>\n";
		
		return;
	}
	
	
	?>
	<table border="1" cellspacing="0" cellpadding="3">
	<tr><th>Skripta</th><th>Aktivan</th><th>Cron string</th><th>Zadnje izvršenje</th><th>Sljedeće izvršenje</th><th>Akcije</th></tr>
	<?

	$q10 = db_query("select id, path, aktivan, godina, mjesec, dan, sat, minuta, sekunda, UNIX_TIMESTAMP(zadnje_izvrsenje), UNIX_TIMESTAMP(sljedece_izvrsenje) FROM cron");
	while ($r10 = db_fetch_row($q10)) {
		if ($r10[2] == 1) $aktivan="X"; else $aktivan="";
		$cronstring = $r10[3]." ".$r10[4]." ".$r10[5]." ".$r10[6]." ".$r10[7]." ".$r10[8];
		$zadnje_izvrsenje = date("d.m.Y. H:i:s", $r10[9]);
		$sljedece_izvrsenje = date("d.m.Y. H:i:s", $r10[10]);
		
		?>
		<tr>
			<td><?=$r10[1]?></td>
			<td><?=$aktivan?></td>
			<td><?=$cronstring?></td>
			<td><?=$zadnje_izvrsenje?></td>
			<td><?=$sljedece_izvrsenje?></td>
			<td><a href="?sta=admin/cron&akcija=log&cron=<?=$r10[0]?>">Log</a> * <a href="?sta=common/cron&force=<?=$r10[0]?>">Izvrši odmah</a></td>
		</tr>
		<?
	}

	print "</table>\n";
}

?>
