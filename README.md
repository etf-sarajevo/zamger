# Zamger
Zamger https://zamger.etf.unsa.ba

# Kako se trenutno koristi API

Potrebno je prvo da se autenticiraš na web servis, što se radi preko endpointa:

https://zamger.etf.unsa.ba/api_v5/auth

Obrati pažnju da je kompletan API usklađen sa REST+JSON. Drugim riječima, svi poslani podaci (pa i username i password) moraju biti JSON kodirani i rezultati su uvijek JSON kodirani. Kada se autenticiraš, dobiješ ID sesije koji se u svakom sljedećem pristupu mora proslijediti kao parametar url-a SESSION_ID.

Primjer kako se koristi autentikacija imaš ovdje:
https://zamger.etf.unsa.ba/wslogin.php
U pitanju je čisti JavaScript tako da predlažem da proanaliziraš kod.

Specifikacija svih ostalih endpointa se može vidjeti iz fajla:
https://github.com/etf-sarajevo/zamger/blob/rezamger/api-v5/wiring.php

Konkretno spisak predmeta za nastavnika se dobija sa /course/teacher/{id}
Recimo, pošto je moj userid na Zamgeru 1 ja se prvo logiram preko stranice wslogin.php. Dobio sam neki SID, recimo 12345. Sada pristupam URLu:

https://zamger.etf.unsa.ba/api_v5/course/teacher/1?SESSION_ID=12345

Ovo je JSON kod koji odgovara nizu objekata tipa CourseUnitYear. Objekat tipa CourseUnitYear se sastoji od elemenata tipa: CourseUnit, AcademicYear i Scoring. To možeš vidjeti ovdje:

https://github.com/etf-sarajevo/zamger/blob/rezamger/backend-v5/core/CourseUnitYear.php

Neki od ovih objekata su nerezolvirani, što možeš vidjeti po tome što JSON sadrži atribut className npr:

"AcademicYear":{"className":"AcademicYear","id":13}

Da bi se rezolvirali svi objekti tipa AcademicYear, samo u URL dodaš parmetar resolve[]=AcademicYear, ovako:

https://zamger.etf.unsa.ba/api_v5/course/teacher/1?SESSION_ID=12345&resolve[]=AcademicYear

Sada vidiš da su prikazani detaljniji podaci o akademskoj godini. resolve parametri se samo dodaju, recimo ovako možemo rezolvirati sada i Institution:

https://zamger.etf.unsa.ba/api_v5/course/teacher/1?SESSION_ID=12345&resolve[]=AcademicYear&resolve[]=Institution

Naravno ovo nije dovoljno da se dobiju baš svi podaci o kursevima, pa predlažem da pogledaš u wiring.php kojim još servisima trebaš pristupiti da saznaš nešto što ti je potrebno za rad.

# Instalacija na Linux host

Zamger v5 trenutno pretpostavlja da je na istom sistemu instaliran Zamger v4. Slijedite uputstva za instalaciju Zamger v4 data ovdje:

https://github.com/etf-sarajevo/zamger/blob/master/static/doc/INSTALL.txt

Kada ste sve ovo završili i uvjerili se da Zamger v4 radi (jako bitno!) pređite na sljedeće korake:

* Recimo da je vaš web root /var/www - napravite git clone u folder zamger-v5 i prebacite se na branch:
  cd /var/www
  mkdir zamger-v5
  git clone https://github.com/etf-sarajevo/zamger.git zamger-v5
  cd zamger-v5
  git checkout rezamger
* U folderu api-v5 kopirajte Config.php.default u Config.php i editujte slično kao konfiguracija za zamger-v4 (koristi se ista baza podataka)
* Da biste imali fine API URLove u Apache konfiguraciju virtualnog hosta dodajte sljedeće linije:
    RewriteEngine on
    RewriteRule   /api/(.+)    /var/www/zamger-v5/api-v5/index.php?route=$1  [QSA,L]
