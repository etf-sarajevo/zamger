#issue 29

Prvi korak je dodavanje novog linka u registry.php (linija 86)

    - array("nastavnik/dodavanje_asistenata", "Dodajte asistenta / demonstratora", "Dodajte asistenta / demonstratora", "N", "1", "0"),
    
Nakon toga kreiran nova skripta, nastavnik/dodavanje_asistenta.php. Napomena za sve ostale: Da bi se skripta pozivala nakon 
MENU-a potrebno je da se funkcija u skripti zove folder_naziv_dokumenta !

### Pretraživanje

Pretraživanje se vrši putem jquery select-2 (pošto ima više osoba - radi jednostavnijeg pregleda).. 
NOTE : Potrebno je provjeriti brzinu rada, ukoliko se bude sporo izvršavalo, ići na opciju direktnog query select-a

### Restrikcije

U slučaju da neko pokuša kroz URL mijenjati ID predmeta ili ID akademske godine (non integer value) - ubit' će čitav page

### Unos

    1. Provjera da li je nastavnik :: Ako nije, unesi u tabelu "privilegije"
    2. Provjera da li ima nivo pristupa na tom predmetu - unos ili ažuriranje
    
U slučaju da "Osoba" ima pravo pristupa na tom predmetu u toj akademskoj godini, on će to pravo ažurirati shodno 
zadanim parametrima (pravo pristupa kao asistent ili superasistent). Ukoliko nema pravo, kreirat će se novi uzorak.

### Ispis svih angažovanih na predmetu

Ispis se vrši u formi "Osoba - Nivo pristupa - Akcije".

U opcijama padajućeg menija imaju 3 ponuđene stavke (defaultna je ona pravo pristupa odabrane osobe). Ukoliko je potrebno 
da se osobi ukine pravo pristupa predmetu, odabire se opcija "Zabranite pristup".

NOTE: U slučaju promjene pristupa na ovaj način (asistent - asistent), vrijednost iz select forme se šalje kao "asistent ili superasistent". 
Ako se žele želi ukinuti pravo pristupa osobe na predmetu, vrijednost koja se prosljeđuje je "obrisi".


    