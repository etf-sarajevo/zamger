<html>
<head>
	<title>ETF Bolognaware</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="css/style.css" rel="stylesheet" type="text/css" />
</head>
<body topmargin="0" leftmargin="0" bottommargin="0" rightmargin="0" bgcolor="#FFFFFF">

<!-- The big table - cause we like it!!!!! web 2.0 is teh sux00rz -->
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr bgcolor="#BBBBFF">
		<td><table width="100%" border="0" cellspacing="0" cellpadding="0"><tr>
			<td width="25">&nbsp;</td>
			<td width="110" height="110" align="center" valign="center"><img src="images/etf.gif"></td>
			<td width="100%"><font color="#FFFFFF"><center><h1>ZAMGER v3.0</h1>by <a href="http://people.etf.unsa.ba/~vljubovic/contact.php"><font color="#FFFFFF">Vedran Ljubović</font></a> (c) 2006,2007</center></font></td>
			<td width="135">&nbsp;</td> <!-- Centriranje-->
		</tr></table></td>
	</tr>
	<tr bgcolor="#777777"><td><img src="images/fnord.gif" width="1" height="1"></td></tr>
	<tr>
		<td>



	<center><table border="0" bgcolor="#FFFFFF"><tr><td>
		<big><b>Dobro došli na bolognaware Elektrotehničkog fakulteta Sarajevo</b></big>
	
		<? include("libvedran.php"); 
		if ($_GET['greska']==1) { niceerror("Nepostojeći student"); } 
		elseif ($_GET['greska']==2) { niceerror("Pogrešna šifra"); } ?>
	
		<p align="center"><a href="pregled-public.php" target="_blank"><img src="images/kontact_todo.png" border="0"><br/>Dnevnik</a></p>

		<p>Za pristup drugim sadržajima, morate se prijaviti:<br/>
		<form action="login.php" method="POST">
		<table border="0"><tr><td>Broj indeksa:</td><td><input type="text" name="login" size="15"></td></tr>
		<tr><td>Šifra:</td><td><input type="password" name="pass" size="15"></td></tr>
		<tr><td colspan="2" align="center"><input type="submit" value="Kreni"></td></tr></table>
		</form>
	</td></tr></table></center>


		</td>
	</tr>
</table>

</body>
</html>
