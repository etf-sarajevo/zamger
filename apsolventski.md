# Apsolventski staž

## Kreiranje ispita

### Tabela
Samo dodavanje apsolventskog staža zahtijevalo je dodatne modifikacije na već postojećim modulima i tabelama. Proces 
dodavanja apsolventskog staža sastoji se iz:

    - Dodavanje kolone apsolventski_rok u tabeli ispiti
    
Na ovaj način, traženjem uslove if(student->apsolvent && ispit->apsolventski_rok) prikazivat će se termini samo za 
studente koji imaju status apsolventa.

### Kreiranje ispita
Keiranje ispita za apsolvente je omogućeno kroz dropdown select menu, gdje je defaultno odabrano kao "NE"  za apsolventski 
rok, sve dok drukčije se ne naznači.
Vrijednosti select menu-a :

    0 - NE
    1 - DA


### Ispis ispita
Za prikazivanje ispita (i da li je apsolventski rok ili ne) bilo je potrebno promijeniti funkciju na ispisu iz

    - db_fetch3() u db_fetch4()
    
pošto je dodan novi parametar $apsolventski_rok. Da li je u pitanju apsolventski rok ili ne, prikazano je u četvrtoj koloni 
na generalnom prikazu ispita od svakog pojedinačnog predmeta.

## Status studenta

Da bi naznačili status studenta da li je apsolvent ili ne, potrebno je bilo:

    - U tabeli student_studij dodati novu kolonu "status" koja defaultnu vrijednost ima null

Konvenciono je uzeto:

    0 ili NULL - Student ima status regularnog studenta
    1. Student ima status apsolventa

## Ispis termina za prijavu studenata

#### Home - student/intro
Ispis termina sevrši u student/intro. Logika je sljedeća: Ako je ispit za apsolvente, on će biti prikazan samo za studente 
koji su apsolventi. Ako je regularni ispit, on će se ispisati za sve studente.

Dodano je :

    - nova vrijednost i.apsolventski_rok u query-u $q15, gdje se apsolventski rok dobija kao $q15[10]
    - kreiran novi query koji provjerava status studenta u tabeli student_studij
    
Napomena:: Dodan string koji naznačava da li je Apsolvenstki rok ili ne!
    
Potrebno je dodatno modifikovati ovaj query u odnosu na godinu i slično (dodatni where uslovi)

    - $s_s = (int)db_fetch_row(db_query("select status from student_studij where student = $userid"))[0];
    
#### Prijava ispita - student/prijava_ispita

I u ovom slučaju je potrebno studentima koji su apsolventi dozvoliti da se prijavi na apsolventski rok, dok za normalni
rok je potrebno omogućiti svima da se prijave.
Uslovi su identični kao i u prošlom dijelu


## Postavljanje statusa apsolventa

Status apsolventa će dobiti student koji je tehnički ponovac, odnosno tretira se tako ako ponovo upisuje treću odnosno 
petu godinu, ali ako je prvi put ponovac u trećoj ili petoj godini, onda je apsolvent!


```php

// Postavljanje uslova da li je student apsolvent ili nije !
// Prvi od uslova je da li se student upisuje u 5-6 ili 9-10 semestar
if($semestar == 5 or $semestar == 6 or $semestar == 9 or $semestar == 10){
    // Sad ćemo provjeriti da li je student ponovac / odnosno da li se treba upisati kao ponovac
    if($ponovac){
        // Sad ćemo provjeriti da li je ponovac prvi put ili je više puta (Za ostale uslove treba provjeriti)
        $val = db_get("SELECT COUNT(*) FROM student_studij WHERE student = $student AND studij = $studij AND semestar = $semestar AND ponovac = 1");
        if(!$val){
            // Ako nikad nije bio ponovac, a sad treba biti, onda ćemo ga staviti kao apsolventa
            $apsolvent = 1;
            $ponovac = 0;
        }

        // Sada provjeravamo da li je student ikad bio apsolvent, ako jeste onda je ponovac!
        $val = db_get("SELECT COUNT(*) FROM student_studij WHERE student = $student AND studij = $studij AND semestar = $semestar AND status = 1");
        if($val){
            $apsolvent = 0;
            $ponovac = 1;
        }
    }
}

```

## Prikaz statusa

U pregledu osobe (studentska / osobe), dodan je i prikaz statusa studenta u odnosu na šifarnik (Ovo može poslužiti za uređivanje)
statusa službenika.

Dodano je nekoliko query-a : 

```php
    // 2342 linija
    // Vrijednost šifarnika
    $sifarnici = db_query("select * from sifarnici order by id");
    $aktuelnaGodina = db_fetch_row(db_query("select * from akademska_godina where aktuelna = 1"))[0];
    $status = db_fetch_row(db_query("SELECT s.naziv, ss.semestar, ss.akademska_godina, ag.naziv, s.id, ts.trajanje, ns.naziv, ts.ciklus, ss.status, sif.name
    FROM student_studij as ss, studij as s, akademska_godina as ag, tipstudija as ts, nacin_studiranja as ns, sifarnici as sif
    WHERE ss.student=$osoba and ss.studij=s.id and ag.id=ss.akademska_godina and s.tipstudija=ts.id and ss.nacin_studiranja=ns.id and ss.status=sif.value and sif.type='status_studenta'
    ORDER BY ag.naziv DESC"));

    // 2361
    <p>
        Status studenta : <?= $status[9]; ?>
    </p>

```