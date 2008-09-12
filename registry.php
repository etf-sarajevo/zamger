<?

// REGISTRY - baza komponenti ZAMGERa

// v3.9.1.0 (2008/02/14) - beta1


$registry = array(
# "path", "puni naziv", "UI naziv", "Uloga", "U sablonu", "Nevidljiv (debug)"
# Legenda polja Uloga:
#      A - admin, B - studentska, N - saradnik, S - student, P - public
# Sablon == 2 znaci da se ne prikazuje ni header

array("public/intro", "Login", "Login", "P", "1", "0"),


array("student/intro", "Studentski dashboard", "Dashboard", "S", "1", "0"),
array("student/predmet", "Status predmeta", "Status predmeta", "S", "1", "0"),
array("student/zadaca", "Slanje zadaće", "Slanje zadaće", "S", "1", "0"),
array("student/pdf", "Prikaz zadaće u PDF formatu", "PDF", "S", "2", "0"),


array("saradnik/intro", "Predmeti i grupe za saradnike", "Predmeti", "N", "1", "0"),
array("saradnik/grupa", "Pregled grupe", "Pregled grupe", "N", "1", "0"),
array("saradnik/zadaca", "Pregled zadaće", "Pregled zadaće", "N", "0", "0"),
array("saradnik/izmjena_studenta", "Izmjena podataka o studentu", "Izmjena studenta", "N", "0", "0"),
array("saradnik/komentar", "Komentari na rad studenta", "Komentar", "N", "0", "0"),


array("nastavnik/predmet", "Opcije predmeta", "Opcije predmeta", "N", "1", "0"),
array("nastavnik/obavjestenja", "Obavještenja za studente", "Obavještenja", "N", "1", "0"),
array("nastavnik/grupe", "Grupe za predavanja i vježbe", "Grupe", "N", "1", "0"),
array("nastavnik/ispiti", "Unos rezultata ispita", "Ispiti", "N", "1", "0"),
array("nastavnik/zadace", "Kreiranje i unos zadaća", "Zadaće", "N", "1", "0"),
array("nastavnik/ocjena", "Konačna ocjena", "Konačna ocjena", "N", "1", "0"),
array("nastavnik/izvjestaji", "Izvještaji", "Izvještaji", "N", "1", "0"),


array("studentska/intro", "Studentska služba", "Početna", "B", "1", "0"),
array("studentska/osobe", "Studenti i nastavnici", "Osobe", "B", "1", "0"),
array("studentska/predmeti", "Predmeti", "Predmeti", "B", "1", "0"),
array("studentska/prijemni", "Prijemni ispit", "Prijemni", "B", "1", "0"),
array("studentska/raspored", "Definisanje studentskih rasporeda", "Raspored", "B", "1", "0"),
array("studentska/izvjestaji", "Prolaznost", "Prolaznost", "B", "1", "0"),
array("studentska/obavijest", "Slanje obavještenja", "Obavijesti", "B", "1", "0"),
array("studentska/prodsjeka", "Promjena odsjeka", "Promjena odsjeka", "B", "1", "0"),


array("admin/intro", "Administracija predmeta", "Site admin", "A", "1", "0"),
array("admin/kompakt", "Kompaktovanje baze", "Kompaktovanje baze", "A", "1", "0"),
array("admin/log", "Pregled logova", "Log", "A", "1", "0"),
array("admin/inbox", "Monster messenger", "Poruke", "A", "1", "0"),
array("admin/konzistentnost", "Provjera konzistentnosti", "Konzistentnost", "A", "1", "0"),
array("admin/studij", "Parametri studija", "Studij", "A", "1", "0"),


array("izvjestaj/predmet", "Izvještaj o predmetu", "Dnevnik", "PSNBA", "0", "0"),
array("izvjestaj/grupe", "Spisak studenata po grupama", "Grupe", "NBA", "0", "0"),
array("izvjestaj/ispit", "Izvještaj za ispit", "Ispit", "NBA", "0", "0"),
array("izvjestaj/index", "Spisak ocjena studenta", "Indeks", "BA", "0", "0"),
array("izvjestaj/prolaznost", "Prolaznost", "Prolaznost", "BA", "0", "0"),
array("izvjestaj/progress", "Pregled ostvarenog rezultata na predmetima", "Bodovi", "BA", "0", "0"),
array("izvjestaj/prijemni", "Rang liste kandidata za upis", "Prijemni", "BA", "0", "0"),
array("izvjestaj/granicni", "Granični slučajevi", "Granični", "BA", "0", "0"),


array("common/ajah", "Asynchronous JavaScript And HTML", "AJAH", "PSNBA", "0", "0"),
array("common/attachment", "Download zadaće u formi attachmenta", "Attachment", "SN", "0", "0"),
array("common/inbox", "Lične poruke", "Poruke", "SNBA", "1", "0"),
array("common/profil", "Profil", "Profil", "SNBA", "1", "0"),

array());

?>
