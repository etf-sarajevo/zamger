<?

// PUBLIC/INTRO - uvodna stranica za javni dio sajta

// Editujte funkciju public_intro() da biste prikazali nešto drugo na početnoj stranici zamgera



function public_intro() {
	global $conf_javni_dnevnik;
	if ($conf_javni_dnevnik)
		public_intro_dnevnik();
	else
		public_intro_bez_dnevnika();
}

function public_intro_dnevnik() {
	?>
		<table width="100%" border="0" cellspacing="4" cellpadding="0">
			<tr><td valign="top" width="300" align="left">
			<br/><br/><b>Dnevnik:</b><br/>
			<?

				require("public/predmeti.php");
				public_predmeti("izvjestaj/predmet&skrati=da");
			?>
			</td><td valign="top">
			<p>&nbsp;</p>
			<?
				login_forma();
			?>
			</td></tr>
		</table>
	<?
}

function public_intro_bez_dnevnika() {
	?>
		<table width="100%" border="0" cellspacing="4" cellpadding="0">
			<tr>
			<td valign="top">
			<p>&nbsp;</p>
			<?
				login_forma();
			?>
			</td></tr>
		</table>
	<?
}


function login_forma() {
	global $greska, $registry, $conf_naziv_institucije_genitiv;

	// Redirekciju na isti URI vršimo samo ako je greška = istek sesije
	$uri=$_SERVER['PHP_SELF'];

	if ($greska == "Vaša sesija je istekla. Molimo prijavite se ponovo." && !(strstr($_SERVER['REQUEST_URI'], "logout"))) {
		$uri = $_SERVER['REQUEST_URI'];
	}

	$anketa_aktivna = false;
	foreach ($registry as $r) {
		if (count($r) == 0) continue;
		if ($r[0]=="public/anketa" && $r[5]==0) {
			$br_aktivnih_anketa = db_get("select count(id) from anketa_anketa where aktivna = 1");
			if ($br_aktivnih_anketa > 0) $anketa_aktivna = true;
		}
	}

	?>
	<center><table border="0" cellpadding="5" bgcolor="#FFFFFF">
	<tr><td align="center">
		<big><b>Dobro došli na bolognaware <?=$conf_naziv_institucije_genitiv?></b></big>
	</td></tr>
	<tr><td align="center">
		<!-- Ikone za javne servise -->
		<table  border="0" cellpadding="10" bgcolor="#FFFFFF"><tr>
			<td align="center" valign="top">
				<p><a href="static/doc/zamger-uputstva-42-nastavnik.pdf"><img src="static/images/32x32/docs.png" width="32" height="32" border="0" alt="Dokumentacija"><br>Uputstva za<br>nastavnike</a></p>
			</td>
	<? 

	if ($anketa_aktivna) {
		?>
			<td align="center" valign="top">
				<p><a href="?sta=public/anketa"><img src="static/images/32x32/info.png" width="32" height="32" border="0" alt="Anketa"><br/>Anketa</a></p>
			</td>
		<?
	}
	?>
		</tr></table>
	</td></tr>
	<tr><td align="center">
	<?

	?>
	<!-- Login forma -->
	<form action="<?=$uri?>" method="POST">
	<input type="hidden" name="loginforma" value="1">
	<table border="0"><tr><td>Korisničko ime:</td><td><input type="text" name="login" size="15" autofocus></td></tr>
	<tr><td>Šifra:</td><td><input type="password" name="pass" size="15"></td></tr>
	<tr><td colspan="2" align="center"><input type="submit" value="Kreni"></td></tr></table>
	</form>
	</td></tr></table></center>
	<?
}

?>
