#issue 29

Prvi korak je dodavanje novog linka u registry.php (linija 86)

    - array("nastavnik/dodavanje_asistenata", "Dodajte asistenta / demonstratora", "Dodajte asistenta / demonstratora", "N", "1", "0"),
    
Nakon toga kreiran nova skripta, nastavnik/dodavanje_asistenta.php. Napomena za sve ostale: Da bi se skripta pozivala nakon 
MENU-a potrebno je da se funkcija u skripti zove folder_naziv_dokumenta !

### Pretraživanje

Pretraživanje se vrši putem jquery select-2 (pošto ima više osoba - radi jednostavnijeg pregleda).. 
NOTE : Potrebno je provjeriti brzinu rada, ukoliko se bude sporo izvršavalo, ići na opciju direktnog query select-a

### Unos

    1. Provjera da li je nastavnik :: Ako nije, unesi u tabelu "privilegije"
    2. Provjera da li ima nivo pristupa na tom predmetu i ako ima koje je to pravo
    3. Provjera da li je angažovan na tom predmetu, i ako jeste koji je to angažman
    
Vrši se provjera da li je angažovan ili nije, i da li ima pristupa ili nema. Ukoliko nije angažovan i ukoliko nema pravo 
pristupa, kreiraju se novi uzorci. Ukoliko jeste angažovan i ima pravo pristupa, angažman i pravo pristupa se uređuju 
(uzorak se ažurira).


### Ispis svih angažovanih na predmetu

