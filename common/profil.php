<?

// COMMON/PROFIL + opcije korisnika

// v3.9.1.0 (2008/05/09) + Novi modul common/profil
// v3.9.1.1 (2008/08/28) + $conf_promjena_sifre, zahtjev za promjenu ostalih podataka
// v3.9.1.2 (2008/10/03) + Poostren zahtjev na POST
// v3.9.1.3 (2008/10/15) + Dodan format datuma
// v4.0.0.0 (2009/02/19) + Release
// v4.0.0.1 (2009/03/05) + Dodan logging; sakrij broj indexa korisnicima koji nisu studenti; prikazi informaciju ako je vec poslan zahtjev; ne radi nista ako korisnik nije napravio promjenu
// v4.0.9.1 (2009/06/19) + Tabela osoba: ukinuto polje srednja_skola (to ce biti rijeseno na drugi nacin); polje mjesto_rodjenja prebaceno na sifrarnik; dodano polje adresa_mjesto kao FK na isti sifrarnik
// v4.0.9.2 (2009/06/23) + Nova combobox kontrola koja se sasvim dobro pokazala kod studentska/prijemni
// v4.		(2012/01/19) + Refactoring i ljudski resursi

function common_profil() {

	global $userid, $conf_system_auth, $conf_files_path, $conf_promjena_sifre, $conf_skr_naziv_institucije, $conf_skr_naziv_institucije_genitiv;
	global $user_student, $user_nastavnik, $user_studentska, $user_siteadmin;
	
	
	$akcija = $_REQUEST['akcija'];
	
	// Ispis menija
	include("common/profil/top_meni.php");
	
	// Izbor opcije iz menia
	switch($akcija) {
		case "opcije": 
			include("common/profil/opcije.php");
			break;
		case "izbori": 
			include("common/profil/izbor_imenovanja.php");
			break;
		case "ljudskiresursi": 
			include("common/profil/ljudski_resursi.php");
			break;
		default: 
			include("common/profil/licni_podaci.php");
			break;	
	}
}
?>