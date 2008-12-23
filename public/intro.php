<?

// PUBLIC/INTRO - uvodna stranica za javni dio sajta
// Editujte funkciju public_intro() da biste prikazali nešto drugo na početnoj stranici zamgera

// v3.9.1.0 (2008/02/09) + Novi modul: public/intro, prikazuje stablo predmeta i login formu
// v3.9.1.1 (2008/03/08) + Popravljena redirekcija
// v3.9.1.2 (2008/11/21) + Dodajem link na dokumentaciju



function public_intro() {

	?>
		<table width="100%" border="0" cellspacing="4" cellpadding="0">
			<tr>
			<!--td><img src="images/fnord.gif" width="10" height="1"></td>
			</td--><td valign="top" width="300" align="left">
			<br/><br/><b>Dnevnik:</b><br/>
			<?

				require("public/predmeti.php");
				public_predmeti("izvjestaj/predmet");
			?>
			</td><!--td width="1" bgcolor="#000000"><img src="images/fnord.gif" width="1" height="1">
			</td--><td valign="top">
			<p>&nbsp;</p>
			<?
				login_forma();
			?>
			</td></tr>
		</table>
	<?
}


function login_forma() {
	global $greska;

	// Redirekciju na isti URI vršimo samo ako je greška = istek sesije
	$uri=$_SERVER['PHP_SELF'];

	if ($greska == "Vaša sesija je istekla. Molimo prijavite se ponovo." && !(strstr($_SERVER['REQUEST_URI'], "logout"))) {
		$uri = $_SERVER['REQUEST_URI'];
	}

	?>
	<center><table border="0" bgcolor="#FFFFFF"><tr><td>
		<big><b>Dobro došli na bolognaware Elektrotehničkog fakulteta Sarajevo</b></big>
	
			<p align="center">
			<a href="doc/zamger-uputstva-40-nastavnik.pdf"><img src="images/32x32/dokumentacija.png" width="32" height="32" border="0"><br/>Uputstva za<br/>nastavnike</a></p>
		<form action="<?=$uri?>" method="POST">
		<input type="hidden" name="loginforma" value="1">
		<table border="0"><tr><td>Korisničko ime (UID):</td><td><input type="text" name="login" size="15"></td></tr>
		<tr><td>Šifra:</td><td><input type="password" name="pass" size="15"></td></tr>
		<tr><td colspan="2" align="center"><input type="submit" value="Kreni"></td></tr></table>
		</form>
	</td></tr></table></center>
	<?
}

?>