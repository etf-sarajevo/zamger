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
array("student/zadaca", "Slanje zadaće", "Slanje zadaće", "S", "1", "0"),
array("student/zadacapdf", "Prikaz zadaće u PDF formatu", "PDF", "S", "2", "0"),
array("student/prosjeci", "Kalkulator prosjeka ocjena", "Kalkulator prosjeka", "S", "1", "0"),
array("student/prijava_ispita", "Prijava ispita", "Prijava ispita", "S", "1", "0"),
array("student/moodle", "Materijali (Moodle)", "Materijali (Moodle)", "S", "2", "0"),
array("student/anketa", "Anketa", "Anketa", "S", "1", "0"),
array("student/ugovoroucenju", "Ugovor o učenju", "Ugovor o učenju", "S", "1", "0"),
array("student/ugovoroucenjupdf", "Ugovor o učenju (PDF)", "Ugovor o učenju (PDF)", "S", "2", "0"),
array("student/kolizija", "Kolizija", "Kolizija", "S", "1", "0"),
array("student/notifikacija", "Notifikacija", "Notifikacija", "S", "1", "0"),


array("saradnik/intro", "Predmeti i grupe za saradnike", "Predmeti", "N", "1", "0"),
array("saradnik/grupa", "Pregled grupe", "Pregled grupe", "N", "1", "0"),
array("saradnik/zadaca", "Pregled zadaće", "Pregled zadaće", "N", "0", "0"),
array("saradnik/izmjena_studenta", "Izmjena podataka o studentu", "Izmjena studenta", "N", "0", "0"),
array("saradnik/komentar", "Komentari na rad studenta", "Komentar", "N", "0", "0"),
array("saradnik/student", "Detalji studenta na predmetu", "Detalji studenta", "N", "1", "0"),
array("saradnik/svezadace", "Download svih zadaća u grupi", "Sve zadaće", "N", "2", "0"),


array("nastavnik/predmet", "Opcije predmeta", "Opcije predmeta", "N", "1", "0"),
array("nastavnik/obavjestenja", "Obavještenja za studente", "Obavještenja", "N", "1", "0"),
array("nastavnik/grupe", "Grupe za predavanja i vježbe", "Grupe", "N", "1", "0"),
array("nastavnik/ispiti", "Ispiti", "Ispiti", "N", "1", "0"),
array("nastavnik/prijava_ispita", "Prijava ispita", "Prijava ispita", "N", "1", "1"),
array("nastavnik/zadace", "Kreiranje i unos zadaća", "Zadaće", "N", "1", "0"),
array("nastavnik/ocjena", "Konačna ocjena", "Konačna ocjena", "N", "1", "0"),
array("nastavnik/izvjestaji", "Izvještaji", "Izvještaji", "N", "1", "0"),
array("nastavnik/raspored", "Raspored", "Raspored", "N", "1", "0"),
// Hani Zahirovic- modul za definisanje tipa predmeta

array("nastavnik/tip", "Sistem bodovanja", "Sistem bodovanja predmeta", "N", "1", "0"),


array("studentska/intro", "Studentska služba", "Početna", "B", "1", "0"),
array("studentska/osobe", "Studenti i nastavnici", "Osobe", "B", "1", "0"),
array("studentska/predmeti", "Predmeti", "Predmeti", "B", "1", "0"),
array("studentska/prijemni", "Prijemni ispit", "Prijemni", "B", "1", "0"),
//array("studentska/raspored", "Definisanje studentskih rasporeda", "Raspored", "B", "1", "0"),
array("studentska/raspored1", "Definisanje studentskih rasporeda", "Raspored", "B", "1", "0"),
array("studentska/izvjestaji", "Izvještaji", "Izvještaji", "B", "1", "0"),
array("studentska/obavijest", "Slanje obavještenja", "Obavijesti", "B", "1", "0"),
array("studentska/prodsjeka", "Promjena odsjeka", "Promjena odsjeka", "B", "1", "0"),
array("studentska/anketa", "Anketa", "Anketa", "B", "1", "0"),
array("studentska/plan", "Nastavni plan studija", "Plan studija", "B", "1", "0"),
array("studentska/kreiranje_plana", "Kreiranje plana studija", "Kreiranje plana studija", "B", "1", "0"),
array("studentska/prijave", "Štampanje prijava", "Štampanje prijava", "B", "1", "1"),


array("admin/intro", "Administracija predmeta", "Site admin", "A", "1", "0"),
array("admin/kompakt", "Kompaktovanje baze", "Kompaktovanje baze", "A", "1", "0"),
array("admin/log", "Pregled logova", "Log", "A", "1", "0"),
//array("admin/konzistentnost", "Provjera konzistentnosti", "Konzistentnost", "A", "1", "0"),
array("admin/studij", "Parametri studija", "Studij", "A", "1", "0"),
array("admin/novagodina", "Nova akademska godina", "Nova akademska godina", "A", "1", "0"),


array("izvjestaj/predmet", "Izvještaj o predmetu", "Dnevnik", "PSNBA", "0", "0"),
array("izvjestaj/grupe", "Spisak studenata po grupama", "Grupe", "NBA", "0", "0"),
array("izvjestaj/ispit", "Izvještaj za ispit", "Ispit", "NBA", "0", "0"),
array("izvjestaj/index", "Uvjerenje o položenim predmetima", "Indeks", "SBA", "0", "0"),
array("izvjestaj/prolaznost", "Prolaznost", "Prolaznost", "BA", "0", "0"),
array("izvjestaj/progress", "Pregled ostvarenog rezultata na predmetima", "Bodovi", "SBA", "0", "0"),
array("izvjestaj/prijemni", "Rang liste kandidata za upis", "Prijemni", "BA", "0", "0"),
array("izvjestaj/granicni", "Granični slučajevi", "Granični", "BA", "0", "0"),
array("izvjestaj/genijalci", "Pregled studenata po prosjeku", "Prosjek", "BA", "0", "0"),
array("izvjestaj/statistika_predmeta", "Sumarna statistika predmeta", "Statistika predmeta", "NBA", "0", "0"),
array("izvjestaj/historija", "Historija studenta", "Historija studenta", "BA", "0", "0"),
array("izvjestaj/anketa", "Rezultati ankete", "Anketa", "NBA", "0", "0"),
array("izvjestaj/anketa_semestralni", "Rezultati ankete", "Anketa", "NBA", "0", "0"),
array("izvjestaj/anketa_komparacija", "Poredjenje rezultata ankete", "Anketa", "NBA", "0", "0"),
array("izvjestaj/pregled", "Pregled upisanih studenata", "Pregled upisanih studenata", "BA", "0", "0"),
array("izvjestaj/prijave", "Štampanje prijava", "Štampanje prijava", "B", "2", "0"),
array("izvjestaj/pdf_converter", "Za generisanje PDF izvjestaja", "Dnevnik", "PSNBA", "2", "0"),
array("izvjestaj/csv_converter", "Za generisanje excel izvjestaja", "Dnevnik", "PSNBA", "2", "0"),

array("common/ajah", "Asynchronous JavaScript And HTML", "AJAH", "PSNBA", "0", "0"),
array("common/attachment", "Download zadaće u formi attachmenta", "Attachment", "SN", "2", "0"),
array("common/inbox", "Lične poruke", "Poruke", "SNBA", "1", "0"),
array("common/profil", "Profil", "Profil", "SNBA", "1", "0"),
//array("common/raspored", "Raspored", "Raspored", "SNBA", "1", "0"),
array("common/raspored1", "Raspored", "Raspored", "SNBA", "1", "0"),
array("common/slika", "Slika", "Slika", "SNBA", "2", "0"),
array("common/pdfraspored", "Prikaz rasporeda u pdf formatu", "PDF_RASPORED", "SNBA", "1", "0"),
array("common/savjet_dana", "Da li ste znali...", "Da li ste znali...", "SNBA", "0", "0"),

/************************************
 * Haris Agic
*************************************/
array("nastavnik/projekti", "Projekti", "Projekti", "N", "1", "0"),
array("student/projekti", "Projekti", "Projekti", "S", "1", "0"),
array("common/projektneStrane", "Projektne strane", "Projektne strane", "SN", "1", "0"),
array("common/articleImageDownload", "Slike projektnih članaka", "Slike članaka", "SNA", "2", "0"),


array ("nastavnik/zavrsni", "Zavrsni radovi", "Zavrsni", "N", "1", "0"),
array ("student/zavrsni", "Zavrsni radovi", "Zavrsni", "S", "1", "0"),
array ("studentska/zavrsni", "Zavrsni radovi", "Zavrsni", "B", "1", "0"),
array()
);
?>