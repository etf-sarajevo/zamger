<?

// COMMON/PROFIL + opcije korisnika

// v3.9.1.0 (2008/05/09) + Novi modul common/profil


function common_profil() {

global $userid,$conf_system_auth;

?>
<h1>Ime i prezime se ne može promijeniti</h1>
<h1>E-mail se ne može promijeniti</h1>
<?

if ($conf_system_auth == "ldap") {
?>
<h1>Šifru mijenjate kroz <a href="http://webmail.etf.unsa.ba" target="_blank">webmail</a></h1>
<?

} else {
	// TODO: napraviti promjenu sifre

}

}


?>