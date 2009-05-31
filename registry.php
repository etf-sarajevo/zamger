<?

// REGISTRY - baza komponenti ZAMGERa

// v3.9.1.0 (2008/02/14) - beta1


$registry = array(
# "path", "puni naziv", "UI naziv", "Uloga", "U sablonu", "Nevidljiv (debug)"
# Legenda polja Uloga:
#      A - admin, B - studentska, N - saradnik, S - student, P - public
# Sablon == 2 znaci da se ne prikazuje ni header

array("public/intro", "Login", "Login", "P", "1", "0"),
array("public/anketa", "Anketa", "Anketa", "P", "1", "0"),


array("student/intro", "Studentski dashboard", "Dashboard", "S", "1", "0"),
array("student/predmet", "Status predmeta", "Status predmeta", "S", "1", "0"),
array("student/zadaca", "Slanje zada?e", "Slanje zada?e", "S", "1", "0"),
array("student/pdf", "Prikaz zada?e u PDF formatu", "PDF", "S", "2", "0"),
array("student/prosjeci", "Kalkulator prosjeka ocjena", "Kalkulator prosjeka", "S", "1", "0"),
array("student/prijava_isptita", "Prijava ispita", "Prijava ispita", "S", "1", "0"),
array("student/moodle", "Materijali (Moodle)", "Materijali (Moodle)", "S", "2", "0"),
array("student/anketa", "Anketa", "Anketa", "S", "1", "0"),


array("saradnik/intro", "Predmeti i grupe za saradnike", "Predmeti", "N", "1", "0"),
array("saradnik/grupa", "Pregled grupe", "Pregled grupe", "N", "1", "0"),
array("saradnik/zadaca", "Pregled zada?e", "Pregled zada?e", "N", "0", "0"),
array("saradnik/izmjena_studenta", "Izmjena podataka o studentu", "Izmjena studenta", "N", "0", "0"),
array("saradnik/komentar", "Komentari na rad studenta", "Komentar", "N", "0", "0"),


array("nastavnik/predmet", "Opcije predmeta", "Opcije predmeta", "N", "1", "0"),
array("nastavnik/obavjestenja", "Obavještenja za studente", "Obavještenja", "N", "1", "0"),
array("nastavnik/grupe", "Grupe za predavanja i vježbe", "Grupe", "N", "1", "0"),
array("nastavnik/ispiti", "Unos rezultata ispita", "Ispiti", "N", "1", "0"),
array("nastavnik/prijava_ispita", "Prijava ispita", "Prijava ispita", "N", "1", "0"),
array("nastavnik/zadace", "Kreiranje i unos zada?a", "Zada?e", "N", "1", "0"),
array("nastavnik/ocjena", "Kona?na ocjena", "Kona?na ocjena", "N", "1", "0"),
array("nastavnik/izvjestaji", "Izvještaji", "Izvještaji", "N", "1", "0"),


array("studentska/intro", "Studentska služba", "Po?etna", "B", "1", "0"),
array("studentska/osobe", "Studenti i nastavnici", "Osobe", "B", "1", "0"),
array("studentska/predmeti", "Predmeti", "Predmeti", "B", "1", "0"),
array("studentska/prijemni", "Prijemni ispit", "Prijemni", "B", "1", "0"),
array("studentska/raspored", "Definisanje studentskih rasporeda", "Raspored", "B", "1", "0"),
array("studentska/izvjestaji", "Izvještaji o prolaznosti", "Izvještaji", "B", "1", "0"),
array("studentska/obavijest", "Slanje obavještenja", "Obavijesti", "B", "1", "0"),
array("studentska/prodsjeka", "Promjena odsjeka", "Promjena odsjeka", "B", "1", "0"),
array("studentska/anketa", "Anketa", "Anketa", "B", "1", "0"),


array("admin/intro", "Administracija predmeta", "Site admin", "A", "1", "0"),
array("admin/kompakt", "Kompaktovanje baze", "Kompaktovanje baze", "A", "1", "0"),
array("admin/log", "Pregled logova", "Log", "A", "1", "0"),
array("admin/konzistentnost", "Provjera konzistentnosti", "Konzistentnost", "A", "1", "0"),
array("admin/studij", "Parametri studija", "Studij", "A", "1", "0"),


array("izvjestaj/predmet", "Izvještaj o predmetu", "Dnevnik", "PSNBA", "0", "0"),
array("izvjestaj/grupe", "Spisak studenata po grupama", "Grupe", "NBA", "0", "0"),
array("izvjestaj/ispit", "Izvještaj za ispit", "Ispit", "NBA", "0", "0"),
array("izvjestaj/index", "Spisak ocjena studenta", "Indeks", "BA", "0", "0"),
array("izvjestaj/prolaznost", "Prolaznost", "Prolaznost", "BA", "0", "0"),
array("izvjestaj/progress", "Pregled ostvarenog rezultata na predmetima", "Bodovi", "BA", "0", "0"),
array("izvjestaj/prijemni", "Rang liste kandidata za upis", "Prijemni", "BA", "0", "0"),
array("izvjestaj/granicni", "Grani?ni slu?ajevi", "Grani?ni", "BA", "0", "0"),
array("izvjestaj/genijalci", "Pregled studenata po prosjeku", "Prosjek", "BA", "0", "0"),
array("izvjestaj/statistika_predmeta", "Sumarna statistika predmeta", "Statistika predmeta", "NBA", "0", "0"),
array("izvjestaj/historija", "Historija studenta", "Historija studenta", "BA", "0", "0"),
array("izvjestaj/anketa", "Rezultati ankete", "Anketa", "NBA", "0", "0"),
array("izvjestaj/anketa_semestralni", "Rezultati ankete", "Anketa", "NBA", "0", "0"),
array("izvjestaj/anketa_komparacija", "Poredjenje rezultata ankete", "Anketa", "NBA", "0", "0"),


array("common/ajah", "Asynchronous JavaScript And HTML", "AJAH", "PSNBA", "0", "0"),
array("common/attachment", "Download zada?e u formi attachmenta", "Attachment", "SN", "0", "0"),
array("common/inbox", "Li?ne poruke", "Poruke", "SNBA", "1", "0"),
array("common/profil", "Profil", "Profil", "SNBA", "1", "0"),
array("common/raspored", "Raspored", "Raspored", "SNBA", "1", "0"),
/************************************
 * Haris Agic
*************************************/
array("nastavnik/projekti", "Projekti", "Projekti", "N", "1", "0"),
array("student/projekti", "Projekti", "Projekti", "S", "1", "0"),
array("common/projektneStrane", "Projektne strane", "Projektne strane", "SN", "1", "0"),
array("common/fileDownload", "Download projektnih fajlova", "Projektni fajlovi", "SNA", "2", "0"),
array("common/articleImageDownload", "Slike projektnih clanaka", "Slike clanaka", "SNA", "2", "0"),
array()

);




?>
